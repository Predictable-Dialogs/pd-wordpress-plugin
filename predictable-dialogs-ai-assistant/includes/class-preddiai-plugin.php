<?php

if (!defined('ABSPATH')) {
    exit;
}

class PREDDIAI_Plugin {
    /**
     * @var PREDDIAI_Settings
     */
    private $settings;

    /**
     * @var PREDDIAI_Frontend_Renderer
     */
    private $frontend_renderer;

    public function __construct() {
        $this->settings = new PREDDIAI_Settings();
        $this->frontend_renderer = new PREDDIAI_Frontend_Renderer();
    }

    public function init() {
        $this->settings->register_hooks();
        $this->frontend_renderer->register_hooks();
    }
}
