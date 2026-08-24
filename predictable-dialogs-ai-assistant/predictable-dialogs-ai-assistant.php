<?php
/**
 * Plugin Name: Predictable Dialogs AI Assistant
 * Plugin URI: https://predictabledialogs.com
 * Description: Add an AI chatbot to your WordPress site that instantly answers visitor questions from your documentation.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: jai@predictabledialogs.com
 * Author URI: https://github.com/Predictable-Dialogs
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: predictable-dialogs-ai-assistant
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PREDDIAI_PLUGIN_FILE', __FILE__);
define('PREDDIAI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PREDDIAI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PREDDIAI_PLUGIN_VERSION', '0.1.0');

if (!defined('PREDDIAI_APP_URL')) {
    define('PREDDIAI_APP_URL', 'https://predictabledialogs.com');
}

if (!defined('PREDDIAI_API_URL')) {
    define('PREDDIAI_API_URL', 'https://app.predictabledialogs.com');
}

require_once PREDDIAI_PLUGIN_DIR . 'includes/class-preddiai-settings.php';
require_once PREDDIAI_PLUGIN_DIR . 'includes/class-preddiai-auth.php';
require_once PREDDIAI_PLUGIN_DIR . 'includes/class-preddiai-exclusion-matcher.php';
require_once PREDDIAI_PLUGIN_DIR . 'includes/class-preddiai-frontend-renderer.php';
require_once PREDDIAI_PLUGIN_DIR . 'includes/class-preddiai-plugin.php';

function preddiai_boot_plugin() {
    $plugin = new PREDDIAI_Plugin();
    $plugin->init();
}

preddiai_boot_plugin();
