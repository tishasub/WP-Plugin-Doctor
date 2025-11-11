# WP Plugin Doctor - Installation & File Structure

## File Structure

```
wp-plugin-doctor/
├── wp-plugin-doctor.php          # Main plugin file
├── README.md                      # Documentation
├── assets/
│   ├── css/
│   │   └── admin.css             # Admin dashboard styles
│   └── js/
│       └── admin.js              # Admin dashboard JavaScript
└── includes/
    ├── class-admin.php            # Admin interface & dashboard
    ├── class-conflict-detector.php # Conflict detection engine
    ├── class-database.php         # Database operations
    ├── class-error-handler.php    # Error monitoring & logging
    ├── class-safe-mode.php        # Safe mode functionality
    └── views/
        ├── dashboard.php          # Main dashboard view
        ├── errors.php             # Error log page view
        ├── conflicts.php          # Conflicts page view
        ├── safe-mode.php          # Safe mode page view
        └── settings.php           # Settings page view
```

## Quick Installation

### Option 1: Upload via WordPress

1. Zip the entire `wp-plugin-doctor` folder
2. Go to: WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the zip file
4. Click "Install Now" then "Activate"

### Option 2: FTP Upload

1. Upload the `wp-plugin-doctor` folder to `/wp-content/plugins/`
2. Go to WordPress Admin → Plugins
3. Find "WP Plugin Doctor" and click "Activate"

### Option 3: Command Line (if you have SSH access)

```bash
cd /path/to/wordpress/wp-content/plugins/
# If you have the folder on your computer, upload it via FTP
# Then activate via WordPress admin
```

## After Installation

1. Navigate to **Plugin Doctor** in the WordPress admin menu
2. The plugin will automatically create required database tables
3. Error monitoring starts immediately
4. Configure settings under **Plugin Doctor → Settings**
5. Save your Emergency Access URL from **Plugin Doctor → Safe Mode**

## What Gets Created

### Database Tables
- `wp_wppd_errors` - Stores error logs
- `wp_wppd_conflicts` - Stores detected plugin conflicts

### WordPress Options
- `wppd_settings` - Plugin settings
- `wppd_safe_mode_key` - Emergency access key
- `wppd_plugin_backups` - Plugin configuration backups

### Menu Items
- Main Menu: "Plugin Doctor"
- Submenus: Dashboard, Error Log, Conflicts, Safe Mode, Settings
- Admin Bar: Quick access with error count badge

## First Steps After Installation

1. **Review Dashboard**: Check if any existing errors are detected
2. **Configure Settings**: Enable/disable features as needed
3. **Save Emergency URL**: Copy from Safe Mode page for emergencies
4. **Test Error Detection**: The plugin will start monitoring immediately
5. **Run Conflict Scan**: Click "Scan for Conflicts" on the dashboard

## Testing the Plugin

To verify the plugin is working:

1. Check the Dashboard - you should see statistics (even if showing 0)
2. Go to Error Log - table should be visible
3. Go to Settings - all options should be accessible
4. Check Admin Bar - "Plugin Doctor" menu should appear

## Uninstallation

To completely remove the plugin:

1. Go to **Plugin Doctor → Settings**
2. Click "Clear All Data" (optional - removes all error logs)
3. Deactivate the plugin via WordPress Admin → Plugins
4. Delete the plugin

Note: Database tables will remain unless you manually drop them or use a cleanup plugin.

To manually remove database tables:
```sql
DROP TABLE IF EXISTS wp_wppd_errors;
DROP TABLE IF EXISTS wp_wppd_conflicts;
DELETE FROM wp_options WHERE option_name LIKE 'wppd_%';
```

## Troubleshooting Installation

### Plugin doesn't activate
- Check PHP version (requires 7.2+)
- Check WordPress version (requires 5.0+)
- Check error logs for specific errors

### Database tables not created
- Check database user permissions
- Ensure WordPress can write to the database
- Try deactivating and reactivating the plugin

### Dashboard doesn't load
- Clear browser cache
- Check for JavaScript errors in browser console
- Ensure wp-content/plugins/wp-plugin-doctor/assets/ is accessible

### No menu appears
- Clear WordPress cache
- Check user permissions (requires 'manage_options')
- Verify plugin is activated

## Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.2 or higher  
- **MySQL**: 5.6 or higher
- **Browser**: Modern browser (Chrome, Firefox, Safari, Edge)

## File Permissions

Recommended permissions:
- Folders: 755
- PHP files: 644
- CSS/JS files: 644

The plugin does not write files to disk (only to database).

## Support

For issues or questions, check:
1. README.md for detailed documentation
2. Dashboard for error detection capabilities  
3. Settings page for configuration options

## Version

Current Version: 1.0.0
Released: 2024
