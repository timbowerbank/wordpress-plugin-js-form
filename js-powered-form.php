<?php
/**
 * Plugin Name: JS Powered Contact Form
 * Plugin URI: https://example.com
 * Description: A JavaScript-powered contact form with honeypot protection, validation, and email notifications
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: js-powered-form
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JSPF_VERSION', '1.0.0');
define('JSPF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JSPF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JSPF_RECIPIENT_EMAIL', 'your-email@example.com'); // CHANGE THIS to your email address

// Include required files
require_once JSPF_PLUGIN_DIR . 'includes/class-database.php';
require_once JSPF_PLUGIN_DIR . 'includes/class-form-handler.php';
require_once JSPF_PLUGIN_DIR . 'includes/class-admin.php';
require_once JSPF_PLUGIN_DIR . 'includes/class-assets.php';

/**
 * Initialize the plugin
 */
function jspf_init() {
    // Initialize classes
    JSPF_Database::init();
    JSPF_Form_Handler::init();
    JSPF_Admin::init();
    JSPF_Assets::init();
}
add_action('plugins_loaded', 'jspf_init');

/**
 * Activation hook
 */
function jspf_activate() {
    JSPF_Database::create_table();
}
register_activation_hook(__FILE__, 'jspf_activate');

/**
 * Deactivation hook
 */
function jspf_deactivate() {
    // Nothing to do on deactivation
}
register_deactivation_hook(__FILE__, 'jspf_deactivate');
