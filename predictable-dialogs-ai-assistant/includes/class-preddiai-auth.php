<?php

if (!defined('ABSPATH')) {
    exit;
}

class PREDDIAI_Auth {
    const STATE_TRANSIENT = 'preddiai_auth_state';
    const CODE_VERIFIER_TRANSIENT = 'preddiai_code_verifier';
    const AUTH_NONCE_ACTION = 'preddiai_auth_action';
    const AUTH_NONCE_NAME = 'preddiai_auth_nonce';
    const TRANSIENT_TTL = 600;

    public function register_hooks() {
        add_action('admin_post_preddiai_connect', array($this, 'handle_connect'));
        add_action('admin_post_preddiai_disconnect', array($this, 'handle_disconnect'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_filter('allowed_redirect_hosts', array($this, 'allow_predictable_dialogs_redirect_host'));
    }

    public function register_rest_routes() {
        register_rest_route(
            'predictable-dialogs-ai-assistant/v1',
            '/auth/callback',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'handle_callback'),
                'permission_callback' => '__return_true',
            )
        );
    }

    public function handle_connect() {
        $this->assert_admin_request();

        try {
            $state = $this->generate_token();
            $code_verifier = $this->generate_token(64);
            $code_challenge = $this->base64url_encode(hash('sha256', $code_verifier, true));

            set_transient(self::STATE_TRANSIENT, $state, self::TRANSIENT_TTL);
            set_transient(self::CODE_VERIFIER_TRANSIENT, $code_verifier, self::TRANSIENT_TTL);

            error_log('[Predictable Dialogs] PREDDIAI_APP_URL during WordPress plugin connect: ' . PREDDIAI_APP_URL);

            $settings = PREDDIAI_Settings::get_settings();
            $current_agent_name = isset($settings['agent_name']) ? trim((string) $settings['agent_name']) : '';
            $authorize_args = array(
                'site_url' => $this->site_origin(),
                'redirect_uri' => rest_url('predictable-dialogs-ai-assistant/v1/auth/callback'),
                'state' => $state,
                'code_challenge' => $code_challenge,
            );
            if ($current_agent_name !== '') {
                $authorize_args['current_agent_name'] = $current_agent_name;
            }

            $authorize_url = add_query_arg(
                $authorize_args,
                trailingslashit(PREDDIAI_APP_URL) . 'integrations/wordpress-plugin/authorize'
            );

            error_log('[Predictable Dialogs] WordPress plugin authorize URL: ' . $authorize_url);

            wp_safe_redirect($authorize_url);
            exit;
        } catch (Exception $error) {
            $this->redirect_to_settings('error', 'connect_failed');
        }
    }

    public function handle_callback(WP_REST_Request $request) {
        $state = sanitize_text_field((string) $request->get_param('state'));
        $code = sanitize_text_field((string) $request->get_param('code'));
        $stored_state = get_transient(self::STATE_TRANSIENT);

        if (!$state || !$code || !$stored_state || !hash_equals((string) $stored_state, $state)) {
            error_log(
                '[Predictable Dialogs] WordPress plugin callback state verification failed: has_state=' . ($state ? 'yes' : 'no')
                . ', has_code=' . ($code ? 'yes' : 'no')
                . ', has_stored_state=' . ($stored_state ? 'yes' : 'no')
                . ', state_matches=' . ($stored_state && $state && hash_equals((string) $stored_state, $state) ? 'yes' : 'no')
            );
            $this->redirect_to_settings('error', 'state_failed');
        }

        delete_transient(self::STATE_TRANSIENT);

        $code_verifier = get_transient(self::CODE_VERIFIER_TRANSIENT);
        if (!$code_verifier) {
            delete_transient(self::CODE_VERIFIER_TRANSIENT);
            $this->redirect_to_settings('error', 'expired');
        }

        $exchange = $this->exchange_authorization_code($code, (string) $code_verifier);
        delete_transient(self::CODE_VERIFIER_TRANSIENT);

        if (is_wp_error($exchange)) {
            $this->redirect_to_settings('error', $exchange->get_error_code());
        }

        $this->save_connection($exchange);
        $this->redirect_to_settings('success', 'connected');
    }

    public function handle_disconnect() {
        $this->assert_admin_request();

        $settings = PREDDIAI_Settings::get_settings();
        $settings['agent_name'] = '';
        $settings['agent_display_name'] = '';
        $settings['initialization_snippet'] = '';

        update_option(PREDDIAI_Settings::OPTION_NAME, $settings);
        delete_transient(self::STATE_TRANSIENT);
        delete_transient(self::CODE_VERIFIER_TRANSIENT);

        $this->redirect_to_settings('success', 'disconnected');
    }

    public function allow_predictable_dialogs_redirect_host($hosts) {
        $app_host = wp_parse_url(PREDDIAI_APP_URL, PHP_URL_HOST);
        if ($app_host && !in_array($app_host, $hosts, true)) {
            $hosts[] = $app_host;
        }

        return $hosts;
    }

    private function exchange_authorization_code($code, $code_verifier) {
        $response = wp_remote_post(
            trailingslashit(PREDDIAI_API_URL) . 'v1/integrations/wordpress-plugin/exchange',
            array(
                'timeout' => 15,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ),
                'body' => wp_json_encode(
                    array(
                        'code' => $code,
                        'codeVerifier' => $code_verifier,
                    )
                ),
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error('exchange_failed', $response->get_error_message());
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($status_code < 200 || $status_code >= 300 || !is_array($body)) {
            return new WP_Error('exchange_failed', 'Predictable Dialogs rejected the authorization code.');
        }

        if (empty($body['agentName']) || empty($body['initializationSnippet'])) {
            return new WP_Error('exchange_failed', 'Predictable Dialogs returned an incomplete connection response.');
        }

        return $body;
    }

    private function save_connection($exchange) {
        $settings = PREDDIAI_Settings::get_settings();
        $was_connected = !empty($settings['agent_name']);
        $current_widget_type = isset($settings['widget_type']) ? (string) $settings['widget_type'] : '';
        $current_standard_placement_mode = isset($settings['standard_placement_mode']) ? (string) $settings['standard_placement_mode'] : '';
        $valid_widget_types = array('Bubble', 'Popup', 'Standard');
        $valid_standard_placement_modes = array('manual', 'selected_page', 'all_pages');

        $settings['agent_name'] = sanitize_text_field((string) $exchange['agentName']);
        $settings['agent_display_name'] = sanitize_text_field((string) ($exchange['agentDisplayName'] ?? $exchange['agentName']));
        if (!$was_connected || !in_array($current_widget_type, $valid_widget_types, true)) {
            $settings['widget_type'] = 'Standard';
        }
        if (!$was_connected || !in_array($current_standard_placement_mode, $valid_standard_placement_modes, true)) {
            $settings['standard_placement_mode'] = 'all_pages';
        }
        $settings['initialization_snippet'] = $this->prepare_connection_initialization_snippet((string) $exchange['initializationSnippet'], $settings);
        $settings = PREDDIAI_Settings::apply_snippet_derived_settings($settings);

        update_option(PREDDIAI_Settings::OPTION_NAME, $settings);
    }

    private function prepare_connection_initialization_snippet($snippet, $settings) {
        $snippet = PREDDIAI_Settings::sanitize_initialization_snippet_value($snippet);
        if ($snippet === '') {
            return '';
        }

        $prepared = $snippet;
        if (!preg_match('/<script\b/i', $prepared)) {
            $prepared = "<script type=\"module\">\n"
                . "  import Agent from '" . PREDDIAI_Frontend_Renderer::EMBED_CDN_URL . "';\n"
                . "  " . PREDDIAI_Settings::normalize_initialization_snippet_for_execution($prepared) . "\n"
                . '</script>';
        }

        if (PREDDIAI_Settings::detect_widget_type_from_snippet($prepared) === 'Standard' && !preg_match('/<agent-standard\b/i', $prepared)) {
            $width = $this->sanitize_dimension(isset($settings['standard_width']) ? $settings['standard_width'] : '100%', '100%');
            $height = $this->sanitize_dimension(isset($settings['standard_height']) ? $settings['standard_height'] : '600px', '600px');
            $prepared .= "\n<agent-standard style=\"width: " . esc_attr($width) . '; height: ' . esc_attr($height) . ";\"></agent-standard>";
        }

        return $prepared;
    }

    private function sanitize_dimension($value, $default) {
        $sanitized = trim((string) $value);
        if ($sanitized === '') {
            return $default;
        }

        if (preg_match('/^(auto|\\d+(\\.\\d+)?(px|%|rem|em|vh|vw))$/i', $sanitized)) {
            return $sanitized;
        }

        return $default;
    }

    private function assert_admin_request() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage Predictable Dialogs.', 'predictable-dialogs-ai-assistant'));
        }

        check_admin_referer(self::AUTH_NONCE_ACTION, self::AUTH_NONCE_NAME);
    }

    private function redirect_to_settings($status, $message) {
        $url = add_query_arg(
            array(
                'page' => PREDDIAI_Settings::MENU_SLUG,
                'preddiai_status' => sanitize_key($status),
                'preddiai_message' => sanitize_key($message),
            ),
            admin_url('admin.php')
        );

        wp_safe_redirect($url);
        exit;
    }

    private function site_origin() {
        $home_url = home_url('/');
        $scheme = wp_parse_url($home_url, PHP_URL_SCHEME);
        $host = wp_parse_url($home_url, PHP_URL_HOST);
        $port = wp_parse_url($home_url, PHP_URL_PORT);

        $origin = $scheme . '://' . $host;
        if ($port) {
            $origin .= ':' . $port;
        }

        return $origin;
    }

    private function generate_token($bytes = 32) {
        return $this->base64url_encode(random_bytes($bytes));
    }

    private function base64url_encode($value) {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
