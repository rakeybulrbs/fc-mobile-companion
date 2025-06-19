=== FC Mobile Companion ===
Contributors: yourusername
Donate link: https://example.com/donate
Tags: mobile, REST API, application password, authentication, headless
Requires at least: 5.6
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enable secure mobile integration with your WordPress site via custom REST API login and application password generation.

== Description ==

**FC Mobile Companion** is a lightweight plugin designed to provide mobile apps with secure access to WordPress data using the REST API.

This plugin adds a custom login endpoint that authenticates users with their username and password, then generates a WordPress application password for use with subsequent API calls using Basic Auth.

**Key Features:**
- Custom `/wp-json/fc-mobile/v1/login` endpoint
- Secure application password generation
- Compatible with WordPress 5.6+
- Designed for mobile apps (React Native, Flutter, etc.)
- Works with built-in WP REST API

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/fc-mobile-companion`
2. Activate the plugin through the "Plugins" menu in WordPress
3. Done! You can now send POST requests to `/wp-json/fc-mobile/v1/login`

== Usage ==

Send a `POST` request to:

