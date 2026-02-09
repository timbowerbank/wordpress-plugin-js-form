<?php
/**
 * Database handler for form submissions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class JSPF_Database {

    private static $table_name = 'jspf_submissions';

    /**
     * Initialize the database class
     */
    public static function init() {
        // Any initialization if needed
    }

    /**
     * Get the table name with WordPress prefix
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }

    /**
     * Create the submissions table
     */
    public static function create_table() {
        global $wpdb;

        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            surname varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            telephone varchar(20) NOT NULL,
            message text NOT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            submission_time datetime DEFAULT CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Insert a new submission
     */
    public static function insert_submission($data) {
        global $wpdb;

        $table_name = self::get_table_name();

        $insert_data = array(
            'first_name' => sanitize_text_field($data['first_name']),
            'surname' => sanitize_text_field($data['surname']),
            'email' => sanitize_email($data['email']),
            'telephone' => sanitize_text_field($data['telephone']),
            'message' => sanitize_textarea_field($data['message']),
            'ip_address' => self::get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            'submission_time' => current_time('mysql')
        );

        $result = $wpdb->insert(
            $table_name,
            $insert_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Get all submissions
     */
    public static function get_submissions($limit = 100, $offset = 0) {
        global $wpdb;

        $table_name = self::get_table_name();

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        );

        return $wpdb->get_results($query);
    }

    /**
     * Get total submission count
     */
    public static function get_submission_count() {
        global $wpdb;

        $table_name = self::get_table_name();

        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    /**
     * Get a single submission
     */
    public static function get_submission($id) {
        global $wpdb;

        $table_name = self::get_table_name();

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id)
        );
    }

    /**
     * Delete a submission
     */
    public static function delete_submission($id) {
        global $wpdb;

        $table_name = self::get_table_name();

        return $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
    }

    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip = '';

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return sanitize_text_field($ip);
    }
}
