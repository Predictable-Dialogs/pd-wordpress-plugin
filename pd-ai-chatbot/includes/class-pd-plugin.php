<?php

if (!defined('ABSPATH')) {
    exit;
}

class PD_WP_Plugin {
    /**
     * @var PD_WP_Settings
     */
    private $settings;

    /**
     * @var PD_WP_Frontend_Renderer
     */
    private $frontend_renderer;

    public function __construct() {
        $this->settings = new PD_WP_Settings();
        $this->frontend_renderer = new PD_WP_Frontend_Renderer();
    }

    public function init() {
        $this->settings->register_hooks();
        $this->frontend_renderer->register_hooks();
    }
}
