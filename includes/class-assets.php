<?php
/**
 * Assets handler - enqueues CSS and JS files
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class JSPF_Assets {

    /**
     * Initialize the assets handler
     */
    public static function init() {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }

    /**
     * Enqueue CSS and JS files only on the contact page
     */
    public static function enqueue_assets() {
        // Only load on the /contact/ page
        if (!is_page('contact')) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'jspf-contact-form',
            JSPF_PLUGIN_URL . 'assets/css/contact-form.css',
            array(),
            JSPF_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'jspf-contact-form',
            JSPF_PLUGIN_URL . 'assets/js/contact-form.js',
            array(), // No dependencies since we load after DOMContentLoaded
            JSPF_VERSION,
            true // Load in footer
        );

        // Localize script with AJAX URL and nonce
        wp_localize_script(
            'jspf-contact-form',
            'jspfData',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('jspf_contact_form')
            )
        );
    }
}
