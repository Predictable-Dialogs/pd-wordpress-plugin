<?php

if (!defined('ABSPATH')) {
    exit;
}

class PREDDIAI_Settings {
    const OPTION_NAME = 'preddiai_settings';
    const SETTINGS_GROUP = 'preddiai_settings_group';
    const MENU_SLUG = 'predictable-dialogs-ai-assistant';

    /**
     * @var string
     */
    private $settings_page_hook = '';

    public function register_hooks() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public static function get_defaults() {
        return array(
            'agent_name' => '',
            'agent_display_name' => '',
            'widget_type' => 'Standard',
            'disable_widget' => 0,
            'initialization_snippet' => '',
            'excluded_pages' => '',
            'include_logged_in_user' => 1,
            'popup_auto_show_enabled' => 1,
            'popup_auto_show_delay_seconds' => 3,
            'bubble_preview_message_enabled' => 1,
            'bubble_preview_message_text' => 'Need help? Tap here to chat with us!',
            'bubble_preview_message_avatar_url' => '',
            'bubble_preview_auto_show_enabled' => 1,
            'bubble_preview_auto_show_delay_seconds' => 3,
            'bubble_placement' => 'right',
            'bubble_button_size' => 'medium',
            'bubble_button_color' => '#2b3e13',
            'bubble_custom_icon_src' => '',
            'standard_placement_mode' => 'all_pages',
            'standard_content_position' => 'below_content',
            'standard_page_id' => 0,
            'standard_width' => '100%',
            'standard_height' => '600px',
        );
    }

    public static function get_settings() {
        $defaults = self::get_defaults();
        $saved = get_option(self::OPTION_NAME, array());

        if (!is_array($saved)) {
            $saved = array();
        }

        $settings = wp_parse_args($saved, $defaults);

        return $settings;
    }

    public function register_settings() {
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_NAME,
            array($this, 'sanitize_settings')
        );
    }

    public function register_menu() {
        $this->settings_page_hook = add_menu_page(
            __('Predictable Dialogs AI Assistant Settings', 'predictable-dialogs-ai-assistant'),
            __('Predictable Dialogs', 'predictable-dialogs-ai-assistant'),
            'manage_options',
            self::MENU_SLUG,
            array($this, 'render_settings_page'),
            'dashicons-format-chat'
        );
    }

    public function enqueue_admin_assets($hook_suffix) {
        $fallback_hook = 'toplevel_page_' . self::MENU_SLUG;

        if ($hook_suffix !== $this->settings_page_hook && $hook_suffix !== $fallback_hook) {
            return;
        }

        wp_enqueue_style(
            'preddiai-admin-settings',
            PREDDIAI_PLUGIN_URL . 'admin/css/settings-page.css',
            array(),
            PREDDIAI_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'preddiai-admin-settings',
            PREDDIAI_PLUGIN_URL . 'admin/js/settings-page.js',
            array(),
            PREDDIAI_PLUGIN_VERSION,
            true
        );
    }

    public function sanitize_settings($input) {
        $defaults = self::get_defaults();
        $input = is_array($input) ? $input : array();
        $current = self::get_settings();

        $sanitized = array(
            'agent_name' => $this->sanitize_plain_text(isset($input['agent_name']) ? $input['agent_name'] : $current['agent_name']),
            'agent_display_name' => $this->sanitize_plain_text(isset($input['agent_display_name']) ? $input['agent_display_name'] : $current['agent_display_name']),
            'widget_type' => $this->sanitize_widget_type(isset($input['widget_type']) ? $input['widget_type'] : $current['widget_type']),
            'disable_widget' => !empty($input['disable_widget']) ? 1 : 0,
            'initialization_snippet' => $this->sanitize_initialization_snippet(isset($input['initialization_snippet']) ? $input['initialization_snippet'] : ''),
            'excluded_pages' => $this->sanitize_excluded_pages(isset($input['excluded_pages']) ? $input['excluded_pages'] : ''),
            'include_logged_in_user' => !empty($input['include_logged_in_user']) ? 1 : 0,
            'popup_auto_show_enabled' => !empty($input['popup_auto_show_enabled']) ? 1 : 0,
            'popup_auto_show_delay_seconds' => $this->sanitize_positive_int(isset($input['popup_auto_show_delay_seconds']) ? $input['popup_auto_show_delay_seconds'] : $defaults['popup_auto_show_delay_seconds'], $defaults['popup_auto_show_delay_seconds']),
            'bubble_preview_message_enabled' => !empty($input['bubble_preview_message_enabled']) ? 1 : 0,
            'bubble_preview_message_text' => $this->sanitize_plain_text(isset($input['bubble_preview_message_text']) ? $input['bubble_preview_message_text'] : $defaults['bubble_preview_message_text']),
            'bubble_preview_message_avatar_url' => $this->sanitize_url(isset($input['bubble_preview_message_avatar_url']) ? $input['bubble_preview_message_avatar_url'] : ''),
            'bubble_preview_auto_show_enabled' => !empty($input['bubble_preview_auto_show_enabled']) ? 1 : 0,
            'bubble_preview_auto_show_delay_seconds' => $this->sanitize_positive_int(isset($input['bubble_preview_auto_show_delay_seconds']) ? $input['bubble_preview_auto_show_delay_seconds'] : $defaults['bubble_preview_auto_show_delay_seconds'], $defaults['bubble_preview_auto_show_delay_seconds']),
            'bubble_placement' => $this->sanitize_choice(isset($input['bubble_placement']) ? $input['bubble_placement'] : $defaults['bubble_placement'], array('right', 'left'), $defaults['bubble_placement']),
            'bubble_button_size' => $this->sanitize_choice(isset($input['bubble_button_size']) ? $input['bubble_button_size'] : $defaults['bubble_button_size'], array('medium', 'large'), $defaults['bubble_button_size']),
            'bubble_button_color' => $this->sanitize_hex_color(isset($input['bubble_button_color']) ? $input['bubble_button_color'] : $defaults['bubble_button_color'], $defaults['bubble_button_color']),
            'bubble_custom_icon_src' => $this->sanitize_url(isset($input['bubble_custom_icon_src']) ? $input['bubble_custom_icon_src'] : ''),
            'standard_placement_mode' => $this->sanitize_choice(isset($input['standard_placement_mode']) ? $input['standard_placement_mode'] : $defaults['standard_placement_mode'], array('manual', 'selected_page', 'all_pages'), $defaults['standard_placement_mode']),
            'standard_content_position' => $this->sanitize_choice(isset($input['standard_content_position']) ? $input['standard_content_position'] : $current['standard_content_position'], array('above_content', 'below_content'), $defaults['standard_content_position']),
            'standard_page_id' => isset($input['standard_page_id']) ? absint($input['standard_page_id']) : $defaults['standard_page_id'],
            'standard_width' => $this->sanitize_dimension(isset($input['standard_width']) ? $input['standard_width'] : $defaults['standard_width'], $defaults['standard_width']),
            'standard_height' => $this->sanitize_dimension(isset($input['standard_height']) ? $input['standard_height'] : $defaults['standard_height'], $defaults['standard_height']),
        );

        return self::apply_snippet_derived_settings(wp_parse_args($sanitized, $defaults));
    }

    private function sanitize_plain_text($value) {
        return sanitize_text_field(wp_unslash((string) $value));
    }

    private function sanitize_widget_type($value) {
        return $this->sanitize_choice($value, array('Bubble', 'Popup', 'Standard'), 'Standard');
    }

    private function sanitize_choice($value, $allowed, $default) {
        $candidate = $this->sanitize_plain_text($value);
        return in_array($candidate, $allowed, true) ? $candidate : $default;
    }

    private function sanitize_positive_int($value, $default) {
        $number = absint($value);
        return $number > 0 ? $number : $default;
    }

    private function sanitize_hex_color($value, $default) {
        $color = sanitize_hex_color(wp_unslash((string) $value));
        return $color ? $color : $default;
    }

    private function sanitize_url($value) {
        return esc_url_raw(wp_unslash((string) $value));
    }

    private function sanitize_dimension($value, $default) {
        return self::sanitize_dimension_value($value, $default);
    }

    private static function sanitize_dimension_value($value, $default) {
        $sanitized = trim(wp_unslash((string) $value));
        if ($sanitized === '') {
            return $default;
        }

        if (preg_match('/^(auto|\\d+(\\.\\d+)?(px|%|rem|em|vh|vw))$/i', $sanitized)) {
            return $sanitized;
        }

        return $default;
    }

    private function sanitize_initialization_snippet($raw_snippet) {
        return self::sanitize_initialization_snippet_value(wp_unslash((string) $raw_snippet));
    }

    public static function sanitize_initialization_snippet_value($raw_snippet) {
        return trim((string) $raw_snippet);
    }

    public static function normalize_initialization_snippet_for_execution($raw_snippet) {
        $snippet = self::sanitize_initialization_snippet_value($raw_snippet);

        $snippet = preg_replace('/<script\b[^>]*>/i', '', $snippet);
        $snippet = preg_replace('/<\/script>/i', '', $snippet);
        $snippet = preg_replace('/^\s*import\s+Agent\s+from\s+["\'][^"\']+["\'];?\s*$/mi', '', $snippet);
        $snippet = preg_replace('/<agent-standard\b[^>]*>\s*<\/agent-standard>/i', '', $snippet);

        return trim($snippet);
    }

    public static function detect_widget_type_from_snippet($raw_snippet) {
        $snippet = (string) $raw_snippet;

        if (preg_match('/\bAgent\.initBubble\s*\(/', $snippet)) {
            return 'Bubble';
        }

        if (preg_match('/\bAgent\.initPopup\s*\(/', $snippet)) {
            return 'Popup';
        }

        if (preg_match('/\bAgent\.initStandard\s*\(/', $snippet) || preg_match('/<agent-standard\b/i', $snippet)) {
            return 'Standard';
        }

        return '';
    }

    public static function extract_standard_dimensions_from_snippet($raw_snippet) {
        $dimensions = array(
            'width' => '',
            'height' => '',
        );

        if (!preg_match('/<agent-standard\b[^>]*style\s*=\s*(["\'])(.*?)\1/is', (string) $raw_snippet, $style_match)) {
            return $dimensions;
        }

        $style = $style_match[2];
        if (preg_match('/(?:^|;)\s*width\s*:\s*([^;]+)/i', $style, $width_match)) {
            $dimensions['width'] = self::sanitize_dimension_value($width_match[1], '');
        }

        if (preg_match('/(?:^|;)\s*height\s*:\s*([^;]+)/i', $style, $height_match)) {
            $dimensions['height'] = self::sanitize_dimension_value($height_match[1], '');
        }

        return $dimensions;
    }

    public static function apply_snippet_derived_settings($settings) {
        if (!is_array($settings)) {
            return $settings;
        }

        $snippet = isset($settings['initialization_snippet']) ? (string) $settings['initialization_snippet'] : '';
        if (trim($snippet) === '') {
            return $settings;
        }

        $agent_name = sanitize_text_field(self::extract_string_property($snippet, 'agentName'));
        if ($agent_name !== '') {
            $settings['agent_name'] = $agent_name;
            if (empty($settings['agent_display_name'])) {
                $settings['agent_display_name'] = $agent_name;
            }
        }

        $widget_type = self::detect_widget_type_from_snippet($snippet);
        if ($widget_type !== '') {
            $settings['widget_type'] = $widget_type;
        }

        if ($widget_type === 'Popup') {
            $delay_ms = self::extract_int_property($snippet, 'autoShowDelay');
            $settings['popup_auto_show_enabled'] = $delay_ms > 0 ? 1 : 0;
            if ($delay_ms > 0) {
                $settings['popup_auto_show_delay_seconds'] = max(1, (int) round($delay_ms / 1000));
            }
        }

        if ($widget_type === 'Bubble') {
            $placement = self::extract_string_property($snippet, 'placement');
            if (in_array($placement, array('left', 'right'), true)) {
                $settings['bubble_placement'] = $placement;
            }

            $button_size = self::extract_string_property($snippet, 'size');
            if (in_array($button_size, array('medium', 'large'), true)) {
                $settings['bubble_button_size'] = $button_size;
            }

            $button_color = sanitize_hex_color(self::extract_string_property($snippet, 'backgroundColor'));
            if ($button_color) {
                $settings['bubble_button_color'] = $button_color;
            }

            $custom_icon_src = esc_url_raw(self::extract_string_property($snippet, 'customIconSrc'));
            if ($custom_icon_src !== '') {
                $settings['bubble_custom_icon_src'] = $custom_icon_src;
            }

            $preview_block = self::extract_object_block($snippet, 'previewMessage');
            $settings['bubble_preview_message_enabled'] = $preview_block !== '' ? 1 : 0;
            if ($preview_block !== '') {
                $message = self::extract_string_property($preview_block, 'message');
                if ($message !== '') {
                    $settings['bubble_preview_message_text'] = sanitize_text_field($message);
                }

                $avatar_url = esc_url_raw(self::extract_string_property($preview_block, 'avatarUrl'));
                $settings['bubble_preview_message_avatar_url'] = $avatar_url;

                $delay_ms = self::extract_int_property($preview_block, 'autoShowDelay');
                $settings['bubble_preview_auto_show_enabled'] = $delay_ms > 0 ? 1 : 0;
                if ($delay_ms > 0) {
                    $settings['bubble_preview_auto_show_delay_seconds'] = max(1, (int) round($delay_ms / 1000));
                }
            }
        }

        if ($widget_type === 'Standard') {
            $dimensions = self::extract_standard_dimensions_from_snippet($snippet);
            if ($dimensions['width'] !== '') {
                $settings['standard_width'] = $dimensions['width'];
            }
            if ($dimensions['height'] !== '') {
                $settings['standard_height'] = $dimensions['height'];
            }
        }

        return $settings;
    }

    private static function extract_string_property($snippet, $property) {
        $pattern = '/\b' . preg_quote($property, '/') . '\s*:\s*(["\'])(.*?)\1/s';
        if (!preg_match($pattern, (string) $snippet, $match)) {
            return '';
        }

        return stripcslashes((string) $match[2]);
    }

    private static function extract_int_property($snippet, $property) {
        $pattern = '/\b' . preg_quote($property, '/') . '\s*:\s*(\d+)/';
        if (!preg_match($pattern, (string) $snippet, $match)) {
            return 0;
        }

        return absint($match[1]);
    }

    private static function extract_object_block($snippet, $property) {
        $pattern = '/\b' . preg_quote($property, '/') . '\s*:\s*\{/s';
        if (!preg_match($pattern, (string) $snippet, $match, PREG_OFFSET_CAPTURE)) {
            return '';
        }

        $start = strpos((string) $snippet, '{', (int) $match[0][1]);
        if ($start === false) {
            return '';
        }

        $depth = 0;
        $length = strlen((string) $snippet);
        for ($index = $start; $index < $length; $index++) {
            $char = $snippet[$index];
            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr((string) $snippet, $start, $index - $start + 1);
                }
            }
        }

        return '';
    }

    private function sanitize_excluded_pages($raw_pages) {
        $input = wp_unslash((string) $raw_pages);

        $parts = array_filter(array_map('trim', explode(',', $input)), static function ($value) {
            return $value !== '';
        });

        return implode(', ', $parts);
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $preddiai_settings = self::get_settings();
        include PREDDIAI_PLUGIN_DIR . 'admin/views/settings-page.php';
    }
}
