<?php
/**
 * Plugin Name: 404 Error Logger
 * Description: Log 404 errors on your WordPress site.
  * Version: 1.0
 * Author: PlainSurf Solutions
 * Author URI: https://plainsurf.com/
 * Requires PHP at least: 7.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'error_logger_activate');
register_deactivation_hook(__FILE__, 'error_logger_deactivate');

// Activation function
function error_logger_activate() {
    // Create a table to store 404 errors
    global $wpdb;
    $table_name = $wpdb->prefix . 'error_logger';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        url text NOT NULL,
        referrer text,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Deactivation function
function error_logger_deactivate() {
    // No cleanup required for this example
}

// Log 404 errors
function log_404_error() {
    if (is_404()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'error_logger';

        $wpdb->insert(
            $table_name,
            array(
                'time'     => current_time('mysql'),
                'url'      => esc_url($_SERVER['REQUEST_URI']),
                'referrer' => esc_url(wp_get_referer()),
            )
        );
    }
}
add_action('template_redirect', 'log_404_error');

// Add admin menu
function error_logger_menu() {
    add_menu_page(
        '404 Error Logs',
        '404 Error Logs',
        'manage_options',
        'error-logger',
        'error_logger_page'
    );
}
add_action('admin_menu', 'error_logger_menu');

// Admin page callback
function error_logger_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'error_logger';

    $errors = $wpdb->get_results("SELECT * FROM $table_name ORDER BY time DESC");

    ?>
    <div class="wrap">
        <h2>404 Error Logs</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Time</th>
                    <th>URL</th>
                    <th>Referrer</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($errors as $error) {
                    echo '<tr>';
                    echo '<td>' . esc_html($error->id) . '</td>';
                    echo '<td>' . esc_html($error->time) . '</td>';
                    echo '<td>' . esc_html($error->url) . '</td>';
                    echo '<td>' . esc_html($error->referrer) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

