<?php
/**
 * Uninstall script - Clean up database when plugin is deleted
 */

// Exit if accessed directly or if uninstall not called from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete the submissions table
global $wpdb;
$table_name = $wpdb->prefix . 'jspf_submissions';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Clean up any plugin options if needed
// delete_option('jspf_option_name');
