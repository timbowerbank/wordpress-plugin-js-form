<?php
/**
 * Admin interface for viewing form submissions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class JSPF_Admin {

    /**
     * Initialize the admin class
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_post_jspf_delete_submission', array(__CLASS__, 'handle_delete_submission'));
    }

    /**
     * Add admin menu item
     */
    public static function add_admin_menu() {
        add_menu_page(
            'Form Submissions',
            'Form Submissions',
            'manage_options',
            'jspf-submissions',
            array(__CLASS__, 'render_submissions_page'),
            'dashicons-email',
            30
        );

        add_submenu_page(
            'jspf-submissions',
            'View Submission',
            null, // Don't show in menu
            'manage_options',
            'jspf-view-submission',
            array(__CLASS__, 'render_single_submission_page')
        );
    }

    /**
     * Render submissions list page
     */
    public static function render_submissions_page() {
        // Handle bulk actions
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['submissions'])) {
            check_admin_referer('bulk-submissions');

            foreach ($_POST['submissions'] as $submission_id) {
                JSPF_Database::delete_submission(intval($submission_id));
            }

            echo '<div class="notice notice-success is-dismissible"><p>Selected submissions deleted.</p></div>';
        }

        // Get submissions
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;

        $submissions = JSPF_Database::get_submissions($per_page, $offset);
        $total_submissions = JSPF_Database::get_submission_count();
        $total_pages = ceil($total_submissions / $per_page);

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Form Submissions</h1>
            <hr class="wp-header-end">

            <?php if (empty($submissions)): ?>
                <p>No submissions yet.</p>
            <?php else: ?>
                <form method="post">
                    <?php wp_nonce_field('bulk-submissions'); ?>
                    <input type="hidden" name="action" value="delete">

                    <div class="tablenav top">
                        <div class="alignleft actions bulkactions">
                            <select name="action2">
                                <option value="-1">Bulk Actions</option>
                                <option value="delete">Delete</option>
                            </select>
                            <input type="submit" class="button action" value="Apply">
                        </div>
                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo $total_submissions; ?> items</span>
                            <?php if ($total_pages > 1): ?>
                                <span class="pagination-links">
                                    <?php if ($current_page > 1): ?>
                                        <a class="prev-page button" href="<?php echo admin_url('admin.php?page=jspf-submissions&paged=' . ($current_page - 1)); ?>">
                                            <span aria-hidden="true">‹</span>
                                        </a>
                                    <?php endif; ?>

                                    <span class="paging-input">
                                        <span class="tablenav-paging-text"><?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
                                    </span>

                                    <?php if ($current_page < $total_pages): ?>
                                        <a class="next-page button" href="<?php echo admin_url('admin.php?page=jspf-submissions&paged=' . ($current_page + 1)); ?>">
                                            <span aria-hidden="true">›</span>
                                        </a>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <td class="manage-column column-cb check-column">
                                    <input type="checkbox" id="cb-select-all">
                                </td>
                                <th scope="col" class="manage-column">Name</th>
                                <th scope="col" class="manage-column">Email</th>
                                <th scope="col" class="manage-column">Telephone</th>
                                <th scope="col" class="manage-column">Message</th>
                                <th scope="col" class="manage-column">Date</th>
                                <th scope="col" class="manage-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="submissions[]" value="<?php echo esc_attr($submission->id); ?>">
                                    </th>
                                    <td>
                                        <strong>
                                            <a href="<?php echo admin_url('admin.php?page=jspf-view-submission&id=' . $submission->id); ?>">
                                                <?php echo esc_html($submission->first_name . ' ' . $submission->surname); ?>
                                            </a>
                                        </strong>
                                    </td>
                                    <td><?php echo esc_html($submission->email); ?></td>
                                    <td><?php echo esc_html($submission->telephone); ?></td>
                                    <td><?php echo esc_html(wp_trim_words($submission->message, 10)); ?></td>
                                    <td><?php echo esc_html(date('Y-m-d H:i', strtotime($submission->created_at))); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=jspf-view-submission&id=' . $submission->id); ?>" class="button button-small">View</a>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=jspf_delete_submission&id=' . $submission->id), 'delete_submission_' . $submission->id); ?>"
                                           class="button button-small"
                                           onclick="return confirm('Are you sure you want to delete this submission?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo $total_submissions; ?> items</span>
                            <?php if ($total_pages > 1): ?>
                                <span class="pagination-links">
                                    <?php if ($current_page > 1): ?>
                                        <a class="prev-page button" href="<?php echo admin_url('admin.php?page=jspf-submissions&paged=' . ($current_page - 1)); ?>">
                                            <span aria-hidden="true">‹</span>
                                        </a>
                                    <?php endif; ?>

                                    <span class="paging-input">
                                        <span class="tablenav-paging-text"><?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
                                    </span>

                                    <?php if ($current_page < $total_pages): ?>
                                        <a class="next-page button" href="<?php echo admin_url('admin.php?page=jspf-submissions&paged=' . ($current_page + 1)); ?>">
                                            <span aria-hidden="true">›</span>
                                        </a>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <script>
                    document.getElementById('cb-select-all').addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('input[name="submissions[]"]');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });
                </script>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render single submission view page
     */
    public static function render_single_submission_page() {
        if (!isset($_GET['id'])) {
            echo '<div class="wrap"><h1>Invalid submission ID</h1></div>';
            return;
        }

        $submission_id = intval($_GET['id']);
        $submission = JSPF_Database::get_submission($submission_id);

        if (!$submission) {
            echo '<div class="wrap"><h1>Submission not found</h1></div>';
            return;
        }

        ?>
        <div class="wrap">
            <h1>View Submission</h1>
            <a href="<?php echo admin_url('admin.php?page=jspf-submissions'); ?>" class="button">← Back to All Submissions</a>

            <table class="form-table" style="margin-top: 20px;">
                <tbody>
                    <tr>
                        <th scope="row">Submission ID</th>
                        <td><?php echo esc_html($submission->id); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">First Name</th>
                        <td><?php echo esc_html($submission->first_name); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Surname</th>
                        <td><?php echo esc_html($submission->surname); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Email</th>
                        <td>
                            <a href="mailto:<?php echo esc_attr($submission->email); ?>">
                                <?php echo esc_html($submission->email); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Telephone</th>
                        <td>
                            <a href="tel:<?php echo esc_attr($submission->telephone); ?>">
                                <?php echo esc_html($submission->telephone); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Message</th>
                        <td>
                            <div style="white-space: pre-wrap; background: #f5f5f5; padding: 15px; border-radius: 4px;">
                                <?php echo esc_html($submission->message); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Submitted</th>
                        <td><?php echo esc_html(date('F j, Y, g:i a', strtotime($submission->created_at))); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">IP Address</th>
                        <td><?php echo esc_html($submission->ip_address); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">User Agent</th>
                        <td><?php echo esc_html($submission->user_agent); ?></td>
                    </tr>
                </tbody>
            </table>

            <p class="submit">
                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=jspf_delete_submission&id=' . $submission->id), 'delete_submission_' . $submission->id); ?>"
                   class="button button-secondary"
                   onclick="return confirm('Are you sure you want to delete this submission?');">Delete This Submission</a>
            </p>
        </div>
        <?php
    }

    /**
     * Handle submission deletion
     */
    public static function handle_delete_submission() {
        if (!isset($_GET['id'])) {
            wp_die('Invalid submission ID');
        }

        $submission_id = intval($_GET['id']);

        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_submission_' . $submission_id)) {
            wp_die('Security check failed');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to delete submissions');
        }

        // Delete submission
        JSPF_Database::delete_submission($submission_id);

        // Redirect back to submissions page
        wp_redirect(admin_url('admin.php?page=jspf-submissions&deleted=1'));
        exit;
    }
}
