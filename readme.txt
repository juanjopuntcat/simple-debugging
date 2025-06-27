=== Simple Debugging ===
Contributors: juanjopuntcat
Tags: debug, development, logging, troubleshooting, wp-config
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Easily enable and manage WordPress debugging with a safe, user-friendly admin UI. Toggle debug settings, control access by user role, and review logs.

== Description ==

**Simple Debugging** lets administrators and site developers enable, configure, and manage WordPress debugging options safely from the admin dashboard. Set `WP_DEBUG`, `WP_DEBUG_LOG`, and `WP_DEBUG_DISPLAY` directly in `wp-config.php` with just a few clicks. Grant or restrict access to plugin settings for specific user roles, and easily view, filter, and paginate error logs in an intuitive table. No need to edit files or mess with code!

**Features:**
* Enable or disable WordPress debug constants with a simple UI.
* Automatically writes debug settings to `wp-config.php`.
* Role-based access: grant read or write access to specific user roles.
* Dedicated log tab: filter, search, paginate and sort error log entries.
* Secure: does not expose sensitive debug information to unauthorized users.
* Fully compatible with WordPress admin UI standards.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate **Simple Debugging** through the 'Plugins' menu in WordPress.
3. Go to `Tools > Simple Debugging` to configure settings.
4. Grant access to additional user roles if needed.
5. Review and manage your debug log directly from the admin panel.

== Frequently Asked Questions ==

= Where are the debug logs stored? =
By default, debug logs are written to `wp-content/debug.log`. You can view, filter, and search them directly from the plugin log tab.

= Will this plugin overwrite custom wp-config.php changes? =
No, it only edits a specific block that it manages itself, above the WordPress load line. Other customizations are preserved. If there is a problem writing to `wp-config.php`, you’ll see a warning and a snippet to copy-paste manually.

= What if wp-config.php is not writable? =
You will see a warning and get a code snippet to copy/paste manually into your `wp-config.php`. Without this step, debug options will not be updated.

= What happens if another plugin defines WP_DEBUG? =
Simple Debugging will always attempt to manage debug constants via `wp-config.php`. If another plugin or theme also defines these, there may be conflicts—WordPress uses the first definition it encounters. We recommend you only define debug constants in one place.

= Is this safe to use on live sites? =
While the plugin is safe, **enabling WP_DEBUG_DISPLAY on live sites is NOT recommended**. This can expose technical errors or sensitive data to visitors. Use the log-only option for production sites.

= Can I use this with other debug plugins? =
For best results, avoid defining `WP_DEBUG` in other plugins or themes. Use only one tool to manage debug constants.

= Can I clear or download the debug log? =
Not yet. Future versions may add features to clear or export log files from the admin UI.

= How are user roles managed? =
Administrators always have read & write access and this cannot be changed. Other roles can be set to “No Access”, “Read Only” (view plugin, not change settings), or “Read & Write”.

= What if my error log is huge? =
The Log tab uses pagination and filtering for performance, but very large log files may still impact loading time. Consider rotating or archiving your debug log periodically.

= Are my settings safe during WordPress/plugin updates? =
All plugin settings are stored as a WordPress option. The debug constants block is only updated or replaced in `wp-config.php` if you change settings in the plugin UI.

= Does this support multisite? =
This plugin is intended for single-site use. It has not been extensively tested in multisite installations.

= Can I request features or report bugs? =
Absolutely! Please open an issue or PR on [GitHub](https://github.com/juanjopuntcat/simple-debugging) or use the WordPress.org support forum.

== Screenshots ==

1. General tab: Toggle debugging options with clear descriptions.
2. Roles tab: Assign read or write access to each user role.
3. Log tab: View, filter, search, and paginate error log entries in a sortable table.

== Changelog ==

= 1.0.0 =
* Initial release: Modular UI, role-based access, pagination/filtering/sorting log viewer, and safe management of wp-config.php debug options.

== Upgrade Notice ==

= 1.0.0 =
First public release. Major features: admin UI for debug, role controls, log viewer.

== License ==

This plugin is free software, released under the GNU General Public License v2 (or later). See LICENSE file for details.

