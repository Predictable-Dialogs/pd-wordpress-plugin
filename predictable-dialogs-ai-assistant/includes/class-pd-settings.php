<?php

if (!defined('ABSPATH')) {
    exit;
}

class PD_WP_Settings {
    const OPTION_NAME = 'pd_ai_chatbot_settings';
    const SETTINGS_GROUP = 'pd_ai_chatbot_settings_group';
    const MENU_SLUG = 'predictable-dialogs-ai-assistant';

    public function register_hooks() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'register_menu'));
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
        add_menu_page(
            __('Predictable Dialogs AI Assistant Settings', 'predictable-dialogs-ai-assistant'),
            __('Predictable Dialogs', 'predictable-dialogs-ai-assistant'),
            'manage_options',
            self::MENU_SLUG,
            array($this, 'render_settings_page'),
            'dashicons-format-chat'
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

        $settings = self::get_settings();
        include PD_WP_PLUGIN_DIR . 'admin/views/settings-page.php';
    }
}
