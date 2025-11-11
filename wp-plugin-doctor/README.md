# WP Plugin Doctor

**Automatic Plugin Conflict Detection & Error Diagnosis for WordPress**

## Description

WP Plugin Doctor is a comprehensive WordPress plugin that automatically monitors your site for plugin-related errors, detects incompatibilities, and provides actionable solutions to fix WordPress site issues.

Stop wasting hours hunting down plugin conflicts. WP Plugin Doctor tells you exactly which plugin is causing problems.

## Features

### Core Features
- ✅ **Real-Time Error Monitoring** - Captures PHP errors, warnings, notices, and fatal errors as they happen
- ✅ **Intelligent Conflict Detection** - Analyzes error patterns to pinpoint which plugins are incompatible
- ✅ **Root Cause Analysis** - Uses smart algorithms to determine which plugin triggered the error
- ✅ **Safe Recovery Mode** - Automatically disable problematic plugins to restore site functionality
- ✅ **Detailed Error Reports** - View comprehensive logs with stack traces, timestamps, and affected URLs
- ✅ **Proactive Alerts** - Get notified immediately when conflicts are detected via email
- ✅ **Visual Dashboard** - Beautiful, intuitive interface with statistics and charts
- ✅ **Plugin Health Score** - Overall site health monitoring at a glance

### Dashboard Features
- Statistics cards showing total errors, fatal errors, warnings, and active conflicts
- Recent errors list with quick actions
- Error distribution charts by plugin
- Quick action buttons for common tasks
- Site health score calculation
- Active conflicts overview

### Error Management
- Comprehensive error log with filtering by severity and plugin
- Detailed error information including stack traces
- Error categorization (Fatal, Warning, Notice)
- Plugin attribution for each error
- Pagination for large error logs
- Bulk actions for error management

### Conflict Detection
- Function redeclaration detection
- Hook priority conflicts
- jQuery version conflicts
- CSS/Style conflicts
- Similar functionality detection
- Manual conflict scanning
- Confidence scoring for detected conflicts
- Actionable recommendations for each conflict

### Safe Mode
- Emergency recovery URL for locked-out sites
- One-click safe mode activation
- Selective plugin restoration
- Plugin configuration backups
- Restore from backup functionality

### Settings
- Enable/disable error monitoring
- Select which error levels to track
- Configure email alerts
- Set alert email address
- Auto-disable conflicting plugins (optional)
- Log retention period configuration
- Clear all data functionality

## Installation

### Method 1: Upload via WordPress Admin

1. Download the `wp-plugin-doctor` folder
2. Zip the folder into `wp-plugin-doctor.zip`
3. Go to WordPress Admin > Plugins > Add New
4. Click "Upload Plugin"
5. Choose the `wp-plugin-doctor.zip` file
6. Click "Install Now"
7. Activate the plugin

### Method 2: Manual Installation

1. Download the `wp-plugin-doctor` folder
2. Upload the entire folder to `/wp-content/plugins/`
3. Go to WordPress Admin > Plugins
4. Activate "WP Plugin Doctor"

### Method 3: FTP Upload

1. Connect to your server via FTP
2. Navigate to `/wp-content/plugins/`
3. Upload the `wp-plugin-doctor` folder
4. Go to WordPress Admin > Plugins
5. Activate the plugin

## First-Time Setup

After activation:

1. Go to **Plugin Doctor** in the WordPress admin menu
2. Review the dashboard to see if any existing errors are detected
3. Go to **Settings** to configure:
   - Enable error monitoring (recommended: ON)
   - Select error levels to track (recommended: Fatal, Warning, Notice)
   - Configure email alerts if desired
   - Set log retention period (default: 30 days)
4. Copy your Emergency Access URL from the **Safe Mode** page and save it securely

## Usage Guide

### Viewing the Dashboard

Navigate to **Plugin Doctor > Dashboard** to see:
- Total error count for the last 7 days
- Fatal errors, warnings, and notices breakdown
- Active conflicts count
- Recent errors list
- Errors by plugin chart
- Quick action buttons
- Site health score

### Checking Error Logs

Navigate to **Plugin Doctor > Error Log** to:
- View all detected errors
- Filter by severity (Fatal, Warning, Notice)
- Filter by plugin
- View detailed error information
- Delete individual errors
- Clear all errors

### Viewing Conflicts

Navigate to **Plugin Doctor > Conflicts** to:
- See all detected plugin conflicts
- View conflict confidence scores
- Read detailed descriptions of each conflict
- See recommendations for resolving conflicts
- Mark conflicts as resolved
- Disable conflicting plugins directly

### Using Safe Mode

Navigate to **Plugin Doctor > Safe Mode** to:
- Enable safe mode (disables all plugins except WP Plugin Doctor)
- Access your emergency recovery URL
- Create manual backups of plugin configurations
- Restore from previous backups
- View disabled plugins when in safe mode

#### Emergency Access

If your site is locked due to a fatal error:

1. Use the Emergency Access URL you saved earlier
2. The URL format: `https://yoursite.com/?wppd_safe_mode=1&wppd_key=YOUR_KEY`
3. This will activate safe mode and redirect you to the dashboard
4. From there, you can identify and disable the problematic plugin

### Scanning for Conflicts

You can manually scan for conflicts at any time:

1. Go to **Plugin Doctor > Dashboard** or **Conflicts**
2. Click "Scan for Conflicts"
3. The plugin will analyze all active plugins
4. New conflicts will be added to the conflicts list

### Email Alerts

To receive email notifications:

1. Go to **Plugin Doctor > Settings**
2. Enable "Email Alerts"
3. Enter your alert email address
4. Save settings

You'll receive emails when:
- Fatal errors are detected
- New conflicts are found (if configured)

### Admin Bar Quick Access

When logged in, you'll see a "Plugin Doctor" menu in the admin bar showing:
- Recent error count (last 24 hours)
- Quick links to Dashboard and Error Log
- Exit Safe Mode button (when safe mode is active)

## Technical Details

### System Requirements
- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

### Database Tables

The plugin creates two custom tables:
- `wp_wppd_errors` - Stores error logs
- `wp_wppd_conflicts` - Stores detected conflicts

### Performance Impact

WP Plugin Doctor is designed to have minimal impact on site performance:
- Error logging uses efficient database operations
- Front-end monitoring is lightweight
- Heavy analysis is done in the background
- Dashboard loads are optimized with caching

### Data Privacy

- All data is stored in your WordPress database
- No data is sent to external servers
- Error logs may contain file paths and error messages
- Stack traces are stored for debugging purposes
- User information is stored only for context (user ID, IP, user agent)

## Troubleshooting

### Plugin Not Detecting Errors

1. Ensure error monitoring is enabled in Settings
2. Check that the error levels you want to track are selected
3. Verify that your PHP error reporting is configured correctly
4. Try triggering a known error to test the system

### Safe Mode Not Working

1. Regenerate your emergency access URL
2. Ensure the Safe Mode feature is enabled in Settings
3. Check file permissions on your WordPress installation
4. Clear any server-side caching

### Email Alerts Not Sending

1. Verify your WordPress site can send emails
2. Check the alert email address in Settings
3. Look in your spam/junk folder
4. Test WordPress email functionality with another plugin

### Database Issues

If you encounter database errors:

1. Deactivate and reactivate the plugin to recreate tables
2. Check database user permissions
3. Ensure your database server is running properly

## Frequently Asked Questions

**Q: Will this plugin slow down my site?**
A: No, WP Plugin Doctor is designed to have minimal performance impact. Error monitoring happens in the background and is optimized for speed.

**Q: Can I use this on a live production site?**
A: Yes, the plugin is safe for production use. However, we recommend testing in a staging environment first.

**Q: Will it detect conflicts between my theme and plugins?**
A: Yes, the plugin can detect conflicts involving themes, plugins, or WordPress core.

**Q: How long are error logs kept?**
A: By default, logs are kept for 30 days. You can adjust this in Settings (1-365 days).

**Q: Can I export error logs?**
A: Currently, logs can be viewed in the dashboard. Export functionality is planned for a future version.

**Q: Does this replace WordPress debug logging?**
A: No, it complements it. WP Plugin Doctor provides a user-friendly interface and additional analysis features.

**Q: What happens if I deactivate the plugin?**
A: Error monitoring stops immediately. Your data remains in the database and will be available if you reactivate.

**Q: What happens if I delete the plugin?**
A: All plugin data (error logs, conflicts, settings) will remain in your database. To completely remove data, use the "Clear All Data" option in Settings before deletion.

## Support

For issues, questions, or feature requests:
- Check the documentation above
- Review the Settings page for configuration options
- Test in safe mode to isolate issues
- Check the Error Log for clues

## Changelog

### Version 1.0.0
- Initial release
- Error monitoring and logging
- Conflict detection engine
- Safe mode functionality
- Visual dashboard with statistics
- Email alerts
- Plugin backups and restore
- Admin bar integration

## Credits

Developed with ❤️ for the WordPress community

## License

GPLv2 or later - http://www.gnu.org/licenses/gpl-2.0.html
