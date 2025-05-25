<?php
/*
Plugin Name: S WP Admin Guard
Description: Restrict wp-admin and wp-login access by IP, log attempts, email alerts, and redirect denied access.
Version: 2.2
Author: Md. Salman
*/

defined('ABSPATH') or die('No script kiddies please!');

// Paths
define('SWPAG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SWPAG_LOG_FILE', SWPAG_PLUGIN_DIR . 'access-log.txt');

// Activate plugin and add installer IP
register_activation_hook(__FILE__, function () {
    $options = get_option('swpag_settings', []);
    if (!isset($options['allowed_ips']) || empty($options['allowed_ips'])) {
        $installer_ip = $_SERVER['REMOTE_ADDR'];
        $options['allowed_ips'] = [$installer_ip];
        $options['email_alert'] = false;
        $options['redirect_url'] = '';
        update_option('swpag_settings', $options);
    }
});

// Admin menu
add_action('admin_menu', function () {
    add_menu_page('S WP Admin Guard', 'S WP Admin Guard', 'manage_options', 'swp-admin-guard', 'swpag_settings_page');
});

// Settings page
function swpag_settings_page() {
    $options = get_option('swpag_settings', [
        'allowed_ips' => [],
        'email_alert' => false,
        'redirect_url' => ''
    ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('swpag_save_settings')) {
        $options['allowed_ips'] = array_map('trim', explode("
", $_POST['allowed_ips']));
        $options['email_alert'] = isset($_POST['email_alert']);
        $options['redirect_url'] = esc_url_raw($_POST['redirect_url']);
        update_option('swpag_settings', $options);
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $allowed_ips = implode("\n", $options['allowed_ips']);
    $email_alert = $options['email_alert'] ? 'checked' : '';
    $redirect_url = esc_attr($options['redirect_url']);
    $log_url = plugins_url('access-log.txt', __FILE__);
    echo '<div class="wrap"><h1>S WP Admin Guard</h1>
        <form method="post">';
    wp_nonce_field('swpag_save_settings');
    echo "<p><label><strong>Allowed IPs (one per line):</strong><br><textarea name='allowed_ips' rows='5' cols='40'>{$allowed_ips}</textarea></label></p>
        <p><label><input type='checkbox' name='email_alert' {$email_alert}> Enable Email Alerts</label></p>
        <p><label>Redirect URL for Blocked Access:<br><input type='url' name='redirect_url' value='{$redirect_url}' style='width: 300px'></label></p>
        <p><input type='submit' class='button-primary' value='Save Settings'></p>
        <p><a href='{$log_url}' download>Download Log File</a></p>
        </form></div>";
}

// Block unauthorized IPs
add_action('init', function () {
    if (is_admin() && !defined('DOING_AJAX')) {
        $options = get_option('swpag_settings', ['allowed_ips' => []]);
        $allowed_ips = $options['allowed_ips'] ?? [];
        $user_ip = $_SERVER['REMOTE_ADDR'];
        if (!in_array($user_ip, $allowed_ips)) {
            $timestamp = date("Y-m-d H:i:s");
            $log = "[{$timestamp}] Blocked IP: {$user_ip} -> {$_SERVER['REQUEST_URI']}
";
            file_put_contents(SWPAG_LOG_FILE, $log, FILE_APPEND);

            if (!empty($options['email_alert'])) {
                wp_mail(get_option('admin_email'), 'Blocked Admin Access Attempt', $log);
            }

            $redirect_url = $options['redirect_url'];
            if (!empty($redirect_url)) {
                wp_redirect($redirect_url);
            } else {
                wp_die('Access Denied. You are not allowed to access this page.');
            }
            exit;
        }
    }
});
?>
