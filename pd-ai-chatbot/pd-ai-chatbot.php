<?php
/**
 * Plugin Name: AI Chatbot by PD
 * Plugin URI: https://predictabledialogs.com
 * Description: Add an AI chatbot to your WordPress site that instantly answers visitor questions from your documentation.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Predictable Dialogs
 * Author URI: https://predictabledialogs.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pd-ai-chatbot
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PD_WP_PLUGIN_FILE', __FILE__);
define('PD_WP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PD_WP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once PD_WP_PLUGIN_DIR . 'includes/class-pd-settings.php';
require_once PD_WP_PLUGIN_DIR . 'includes/class-pd-exclusion-matcher.php';
require_once PD_WP_PLUGIN_DIR . 'includes/class-pd-frontend-renderer.php';
require_once PD_WP_PLUGIN_DIR . 'includes/class-pd-plugin.php';

function pd_wp_boot_plugin() {
    $plugin = new PD_WP_Plugin();
    $plugin->init();
}

pd_wp_boot_plugin();
