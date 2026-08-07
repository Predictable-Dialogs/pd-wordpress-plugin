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
            'disable_widget' => 0,
            'initialization_snippet' => '',
            'excluded_pages' => '',
            'include_logged_in_user' => 1,
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

        $sanitized = array(
            'disable_widget' => !empty($input['disable_widget']) ? 1 : 0,
            'initialization_snippet' => $this->sanitize_initialization_snippet(isset($input['initialization_snippet']) ? $input['initialization_snippet'] : ''),
            'excluded_pages' => $this->sanitize_excluded_pages(isset($input['excluded_pages']) ? $input['excluded_pages'] : ''),
            'include_logged_in_user' => !empty($input['include_logged_in_user']) ? 1 : 0,
        );

        return wp_parse_args($sanitized, $defaults);
    }

    private function sanitize_initialization_snippet($raw_snippet) {
        $snippet = wp_unslash((string) $raw_snippet);
        $snippet = trim($snippet);

        // If users paste full script tags, only keep the script body.
        $snippet = preg_replace('/<script\b[^>]*>/i', '', $snippet);
        $snippet = preg_replace('/<\/script>/i', '', $snippet);

        // The plugin adds the CDN import itself.
        $snippet = preg_replace('/^\s*import\s+Agent\s+from\s+["\'][^"\']+["\'];?\s*$/mi', '', $snippet);

        return trim($snippet);
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
