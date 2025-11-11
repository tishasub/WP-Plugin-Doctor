=== WP Plugin Doctor ===
Contributors: tisha92
Donate link:
Tags: error monitoring, plugin conflicts, debug, safe mode, error log, plugin doctor, conflict detection, php errors
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically detects plugin conflicts, monitors PHP errors, and offers actionable solutions to fix WordPress site issues.

== Description ==

Stop wasting hours hunting down plugin conflicts. **WP Plugin Doctor** automatically monitors your WordPress site for plugin-related errors, finds incompatibilities, and pinpoints precisely which plugin is causing problems.

= The Problem =

Conflicts among plugins are arguably the most annoying issues one faces while working with WordPress. A single incompatible plugin can cause your site to break, errors with messages that largely make no sense, or silent failures that destroy SEO and user experience. Normally, troubleshooting requires you to disable plugins one by one, which may take hours.

= The Solution =

WP Plugin Doctor silently works in the backend, performing its operational duties 24/7 for your site's health. Whenever conflicts arise, it directly identifies the problematic plugins, logs detailed error information, and provides clear, actionable recommendations for resolving the issue.

= Key Features =

**Error Detection & Logging
* Real time PHP error monitoring (Fatal, Warning, Notice)
* Detailed error logs with stack traces
* Plugin attribution for each error
* Searchable error history

* Context tracking: page, user, timestamp
Intelligent Conflict Detection
* Automatic plugin incompatibility scanning
* Detection of function and hook collisions
* Conflicting JavaScript libraries (e.g. jQuery)

* CSS/Style conflict identification
* Confidence scoring: 0-100%
**Safe Mode & Recovery
One-click activation of Safe Mode
* Emergency access URL for locked sites

* Automatic plugin configuration backups
* Selective plugin restoration
* Restore from backup functionality
Visual Dashboard
* Error statistics and trends

* Health score calculator
* Plugin-specific failure cause
* Interactive charts and graphs
* Quick action buttons

Proactive monitoring
Email notifications for critical errors
* Admin bar notifications
* Dashboard widget
* Weekly health reporting

**Professional Interface

* Modern, intuitive design
* Mobile-responsive layout
* Color-coded severity indicators
* Detailed error modals

* Actionable recommendations

= Perfect For =
* Site administrators who manage multiple plugins
* Developers debugging plugin issues
* Agencies managing client websites
* Anyone with plugin conflicts

* Sites with more intricate plugin ecosystems

= How It Works =
1. **Monitors** - Automatically captures all PHP errors, warnings, and notices
2. **Analyzes** - identifies the problematic plugins,
3. **Detects** - Finds conflicts between incompatible plugins
4. **Reports** - Shows clear statistics and error details

5. **Resolves** - Provides actionable recommendations

6. **Restores**: Safe mode instantly restores site functionality
= Benefits =
Save hours of troubleshooting time
Prevent site downtime caused by hidden conflicts
Maintain written records of all plugin issues.

• Make informed decisions about plugins

Recover quickly when problems occur

Proactively monitor site health
= Privacy & Security =
* All data stored in your WordPress database
* Absolutely no external tracking or phone-home

They include: *
Nonce verification for all actions securely

* Role-based access control, admin only
* Input sanitization and output escaping
* No data sent to third parties
== Installation ==

= Automatic Installation =

1. Log into your WordPress admin panel
2. Go to Plugins > Add New
3. Search for "WP Plugin Doctor"

4. Click "Install Now" then "Activate"

5. In your admin menu, locate and click Plugin Doctor
= Manual Installation =
1. Download the plugin ZIP file
2. Go to Plugins > Add New > Upload Plugin

3. Select the downloaded file followed by clicking "Install Now"

4. Click "Activate Plugin"
5. Go to Plugin Doctor in your admin menu
= FTP Installation =
1. Download and extract the plugin files

2. Upload the `wp-plugin-doctor` folder to `/wp-content/plugins/`

3. Go to Plugins page in WordPress admin

4. Locate "WP Plugin Doctor" and click "Activate"

= After Installation =

1. Go to **Plugin Doctor > Dashboard** to view site health

2. Go to **Plugin Doctor > Settings** to set options

3. If desired, enable email alerts (Settings page)

4. Copy your Emergency Access URL (Safe Mode page)

5. Store the emergency URL in a safe place

= First-Time Setup =

Recommended Settings:

* Enable error monitoring: ON

Error levels include: Fatal, Warning, Notice

* Email alerts: ON (for fatal errors)

* Log retention: 30 days

== Frequently Asked Questions ==

= Will this plugin slow down my site?

No. WP Plugin Doctor is designed with performance in mind. Error monitoring runs in the background with minimal overhead, and the dashboard interface utilizes AJAX to load data efficiently.

= Is it safe to use on a live production site? =

Yes, the plugin is production-ready. That being said, we always suggest you test any new plugins in a staging environment whenever possible.

= Can I use this on multiple sites? =
Yes, you can install the WP Plugin Doctor on as many WordPress sites as you want. The plugin is GPL-licensed.
= Will it work with my theme? =
Yes, WP Plugin Doctor monitors PHP errors regardless of your theme. It works with any WordPress theme.
= Can I export error logs? =

Currently, it is possible to view and filter the error logs in the dashboard. Export capability is envisioned for a future release.

= What happens in Safe Mode? =

Safe Mode temporarily disables all plugins except WP Plugin Doctor, allowing you to regain access to your dashboard if a plugin conflict has locked you out. You can then identify and fix the problem, then exit Safe Mode to restore all plugins.

= How do I use the Emergency Access URL?

If your site is broken and you cannot reach the admin dashboard, access the Emergency Access URL from another browser or device. This URL will activate Safe Mode and take you to the dashboard where you can fix the issue.

= Does this replace WordPress debug mode? =

No, it extends it. WP Plugin Doctor comes with an easy-to-use interface and additional analysis that WordPress debug mode does not have. You can use both together.

= How long are error logs kept? =

By default, logs are retained for 30 days. This can be changed via Settings (1-365 days). Logs are automatically cleaned up so as not to lead to database bloat.

= What kind of conflicts can it detect?

WP Plugin Doctor can detect:

* Function redeclarations conflicts
Perhaps hook priority conflicts.
* Conflicts in JavaScript libraries (jQuery)
* CSS/Style conflicts
* Plugins providing related features
= Can it automatically fix conflicts?

The plugin detects conflicts and makes suggestions, but does not make any changes without your confirmation. You can check the option "Auto-disable conflicting plugins" in Settings if you want to (use with care).

= What if I disable the plugin? =
Error monitoring ceases. Your error logs and settings remain in the database and will be available should you re-activate the plugin later.
= How do I completely wipe all data?
Before uninstallation, proceed to Settings and click "Clear All Data" to delete the error logs. Afterward, disable and delete the plugin. This will remove the plugin files. The database tables can be removed either through the uninstallation process or manually through phpMyAdmin.
= Does it work with multisite? =
The current version works on single WordPress installations. Multisite support is planned for a future release.
= Can I personalize the email notifications?
Presently, email notifications are sent for fatal errors only. You can turn alerts on/off and set the recipient email address under Settings. Further customization will be allowed in future versions.
== Screenshots ==
1. Dashboard displaying error statistics, health score, and recent errors
2. Error log with filtering by severity and plugin
3. Conflict Detection Page with Recommendations
4. Safe mode management and emergency access URL

5. Settings page with configuration options

6. Error details modal with stack trace

7. Admin bar notification showing error count

== Changelog ==
= 1.0.0 - 2024-11-09 =
* Initial release

* Real-time error monitoring and logging
* Intelligent conflict detection engine

- Safe mode with emergency recovery

* Visual dashboard with statistics and charts

* Error log with advanced filtering

* Conflict analysis with confidence scoring * Email notifications for critical errors * Plugin configuration backups * Admin bar integration * WordPress dashboard widget * Comprehensive settings page * Mobile responsive interface == Upgrade Notice == = 1.0.0 = Initial release of WP Plugin Doctor. Start monitoring your site for plugin conflicts and errors today! == Additional Information == = Requirements = * WordPress 5.0 or higher * PHP 7.2 or greater * MySQL 5.6 or higher = Support = * Documentation: See README.md in plugin folder * For issues and questions please use the WordPress.org support forum = Contributing = This plugin is open source. Contributions are welcome! = Privacy Policy = Data is never collected or transmitted outside your WordPress installation by the WP Plugin Doctor. Any error logs and/or statistics are stored in your local WordPress database. There's no tracking, analytics or phone-home functionality whatsoever included. == Credits == Developed with ❤️ for the WordPress community.
