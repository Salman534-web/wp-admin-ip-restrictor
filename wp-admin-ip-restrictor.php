<?php
/**
 * Plugin Name: WP Admin IP Restrictor
 * Description: Restricts wp-admin and wp-login.php access to specific IP addresses. Manage IPs from the settings page.
 * Version: 1.0
 * Author: Md. Salman
 */

add_action('admin_menu', 'wp_admin_ip_restrictor_menu');
function wp_admin_ip_restrictor_menu() {
    add_options_page('Admin IP Restrictor', 'Admin IP Restrictor', 'manage_options', 'wp-admin-ip-restrictor', 'wp_admin_ip_restrictor_settings_page');
}

function wp_admin_ip_restrictor_settings_page() {
    ?>
    <div class="wrap">
        <h2>Allowed IP Addresses for Admin Access</h2>
        <form method="post" action="options.php">
            <?php
                settings_fields('wp_admin_ip_restrictor_group');
                do_settings_sections('wp-admin-ip-restrictor');
                submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'wp_admin_ip_restrictor_settings');
function wp_admin_ip_restrictor_settings() {
    register_setting('wp_admin_ip_restrictor_group', 'wp_allowed_ips', 'sanitize_textarea_field');

    add_settings_section('wp_ip_section', '', null, 'wp-admin-ip-restrictor');

    add_settings_field(
        'wp_allowed_ips',
        'Enter one IP per line',
        'wp_ip_field_html',
        'wp-admin-ip-restrictor',
        'wp_ip_section'
    );
}

function wp_ip_field_html() {
    $ips = get_option('wp_allowed_ips', '');
    echo '<textarea name="wp_allowed_ips" rows="10" cols="50" class="large-text code">' . esc_textarea($ips) . '</textarea>';
}

// Hook to update .htaccess after IP change
add_action('update_option_wp_allowed_ips', 'wp_write_htaccess_rules', 10, 2);

function wp_write_htaccess_rules($old_value, $new_value) {
    $ip_list = explode("\n", $new_value);
    $clean_ips = array_filter(array_map('trim', $ip_list));

    $rules = "\n# BEGIN WP ADMIN IP RESTRICTOR\n";
    $rules .= "<IfModule mod_rewrite.c>\n";
    $rules .= "RewriteEngine On\n";
    $rules .= "RewriteCond %{REQUEST_URI} ^/(wp-admin|wp-login\\.php)\n";

    foreach ($clean_ips as $ip) {
        $rules .= "RewriteCond %{REMOTE_ADDR} !=$ip\n";
    }

    $rules .= "RewriteRule ^(.*)$ - [R=403,L]\n";
    $rules .= "</IfModule>\n";
    $rules .= "# END WP ADMIN IP RESTRICTOR\n";

    $htaccess_path = ABSPATH . '.htaccess';

    if (file_exists($htaccess_path) && is_writable($htaccess_path)) {
        $content = file_get_contents($htaccess_path);
        $content = preg_replace('/# BEGIN WP ADMIN IP RESTRICTOR.*?# END WP ADMIN IP RESTRICTOR/s', '', $content);
        $content .= $rules;
        file_put_contents($htaccess_path, $content);
    }
}
