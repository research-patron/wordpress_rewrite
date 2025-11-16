=== Rewrite Toolkit ===
Contributors: openai-assistant
Requires at least: 6.2
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage rewrite tags and rules from the WordPress dashboard, REST API, or WP-CLI.

== Description ==
Rewrite Toolkit gives site teams a visual interface for defining rewrite tags and rules. Each change is instantly applied and can be previewed through a dedicated REST endpoint or the bundled WP-CLI command. The plugin was designed for editorial teams who frequently ship new landing pages, microsites, or documentation hubs without waiting for a developer to adjust `functions.php`.

== Features ==
* Settings page with searchable tables for rewrite tags and rewrite rules.
* Inline rule editor, duplicate-and-delete actions, and a prominent "Flush rewrite rules" button.
* REST API endpoints (`/wp-json/rewrite-toolkit/v1/*`) for listing tags/rules, triggering a flush, and validating new patterns.
* WP-CLI commands for CI pipelines: `wp rewrite-toolkit list`, `wp rewrite-toolkit flush`, `wp rewrite-toolkit test`.
* Helper functions that sanitize user input and render localized timestamps.

== Installation ==
1. Upload the `rewrite-toolkit` directory to `/wp-content/plugins/`.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Navigate to **Settings → Rewrite Toolkit** to start adding tags and rules.

== Frequently Asked Questions ==
= Does the plugin flush rewrite rules automatically? =
Yes. Every time you add, edit, or delete tags/rules the plugin flushes. You can also trigger a manual flush from the settings page, REST API, or WP-CLI.

= Can non-administrators use the UI? =
No. Only users with the `manage_options` capability can modify rules.

== REST API ==
* `GET /wp-json/rewrite-toolkit/v1/rules` — Returns all saved rules.
* `GET /wp-json/rewrite-toolkit/v1/tags` — Returns all saved tags.
* `POST /wp-json/rewrite-toolkit/v1/flush` — Flush rewrite rules (requires authentication with `manage_options`).
* `POST /wp-json/rewrite-toolkit/v1/rules/preview` — Pass `pattern` and `path` to test against PCRE.

== WP-CLI ==
Run `wp help rewrite-toolkit` to see all commands.

== Changelog ==
= 1.0.0 =
* Initial release.
