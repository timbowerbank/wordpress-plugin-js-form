<?php
/**
 * Form submission handler
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class JSPF_Form_Handler {

    /**
     * Initialize the form handler
     */
    public static function init() {
        // Register AJAX handlers for both logged-in and non-logged-in users
        add_action('wp_ajax_jspf_submit_form', array(__CLASS__, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_jspf_submit_form', array(__CLASS__, 'handle_form_submission'));
    }

    /**
     * Handle form submission via AJAX
     */
    public static function handle_form_submission() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'jspf_contact_form')) {
            wp_send_json_error(array(
                'message' => 'Security verification failed.'
            ), 403);
        }

        // Validate and sanitize input
        $errors = array();

        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $surname = isset($_POST['surname']) ? sanitize_text_field($_POST['surname']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $telephone = isset($_POST['telephone']) ? sanitize_text_field($_POST['telephone']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        // Validation
        if (empty($first_name)) {
            $errors[] = 'First name is required.';
        }

        if (empty($surname)) {
            $errors[] = 'Surname is required.';
        }

        if (empty($email) || !is_email($email)) {
            $errors[] = 'Valid email is required.';
        }

        if (empty($telephone)) {
            $errors[] = 'Telephone is required.';
        } elseif (!self::validate_uk_telephone($telephone)) {
            $errors[] = 'Please provide a valid UK telephone number.';
        }

        if (empty($message)) {
            $errors[] = 'Message is required.';
        }

        // If there are validation errors, return them
        if (!empty($errors)) {
            wp_send_json_error(array(
                'message' => implode(' ', $errors)
            ), 400);
        }

        // Prepare data for database
        $submission_data = array(
            'first_name' => $first_name,
            'surname' => $surname,
            'email' => $email,
            'telephone' => $telephone,
            'message' => $message
        );

        // Save to database
        $submission_id = JSPF_Database::insert_submission($submission_data);

        if (!$submission_id) {
            wp_send_json_error(array(
                'message' => 'Failed to save submission. Please try again.'
            ), 500);
        }

        // Send email notification
        $email_sent = self::send_email_notification($submission_data);

        if (!$email_sent) {
            // Log error but don't fail the request
            error_log('JSPF: Failed to send email notification for submission ID: ' . $submission_id);
        }

        // Success response
        wp_send_json_success(array(
            'message' => 'Thank you! Your message has been sent successfully.',
            'submission_id' => $submission_id
        ));
    }

    /**
     * Validate UK telephone number
     */
    private static function validate_uk_telephone($telephone) {
        // Remove spaces for validation
        $clean_tel = str_replace(' ', '', $telephone);

        // UK mobile pattern: 07xxx xxxxxx or +447xxx xxxxxx
        $mobile_pattern = '/^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}$/';

        // UK landline pattern: 01xxx xxxxx or 02x xxxx xxxx etc.
        $landline_pattern = '/^(\+44\s?[1-9]\d{1,4}|\(?0[1-9]\d{1,4}\)?)\s?\d{3,4}\s?\d{3,4}$/';

        return preg_match($mobile_pattern, $telephone) || preg_match($landline_pattern, $telephone);
    }

    /**
     * Send email notification
     */
    private static function send_email_notification($data) {
        $to = JSPF_RECIPIENT_EMAIL;

        $subject = 'New Contact Form Submission from ' . $data['first_name'] . ' ' . $data['surname'];

        $message = "You have received a new contact form submission:\n\n";
        $message .= "Name: " . $data['first_name'] . ' ' . $data['surname'] . "\n";
        $message .= "Email: " . $data['email'] . "\n";
        $message .= "Telephone: " . $data['telephone'] . "\n\n";
        $message .= "Message:\n" . $data['message'] . "\n\n";
        $message .= "---\n";
        $message .= "Submitted: " . current_time('mysql') . "\n";
        $message .= "IP Address: " . self::get_client_ip();

        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
            'Reply-To: ' . $data['first_name'] . ' ' . $data['surname'] . ' <' . $data['email'] . '>'
        );

        // Use wp_mail which will automatically use WP Mail SMTP if configured
        return wp_mail($to, $subject, $message, $headers);
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
