
=== WP Admin IP Restrictor ===
Contributors: Md. Salman
Tags: security, admin access, ip restrict, wp-admin, login protection
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Restrict wp-admin and wp-login.php access to specific IP addresses. Manage allowed IPs from the WordPress dashboard.

== Description ==
This plugin helps secure your WordPress site by restricting access to the admin dashboard (`/wp-admin`) and login page (`/wp-login.php`) based on allowed IP addresses. All other IPs will receive a 403 Forbidden error.

**Features:**
- Admin settings page under "Settings > Admin IP Restrictor"
- Add one IP address per line
- Automatically updates your site's .htaccess file with the correct rules

== Installation ==
1. Upload the plugin ZIP via WordPress Dashboard > Plugins > Add New > Upload Plugin
2. Activate the plugin.
3. Go to **Settings > Admin IP Restrictor**
4. Enter the IP addresses (one per line) that should have access to wp-admin and wp-login.php
5. Save changes.

== Important Notes ==
- Ensure your `.htaccess` file is writable by the server.
- Always add your own IP before saving to avoid locking yourself out.
- If you get locked out, access your site via FTP or File Manager and manually edit or remove the `.htaccess` rules.

== Frequently Asked Questions ==
= How do I find my IP address? =
Visit https://whatismyipaddress.com or search "what is my IP" in Google.

= Can I add multiple IPs? =
Yes. Enter one IP per line in the settings textarea.

= What happens if someone not on the list tries to access the admin panel? =
They will receive a **403 Forbidden** response.

== Changelog ==
= 1.0 =
* Initial release
