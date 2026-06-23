<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('pd_ai_chatbot_settings');
