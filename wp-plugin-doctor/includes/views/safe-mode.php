<?php
/**
 * Safe Mode Page View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wppd-safe-mode-page">
    <h1 class="wp-heading-inline">
        <?php _e('Safe Mode', 'wp-plugin-doctor'); ?>
    </h1>
    
    <hr class="wp-header-end">
    
    <?php if ($is_safe_mode): ?>
        <div class="notice notice-warning notice-large">
            <h2><?php _e('⚠️ Safe Mode is Currently Active', 'wp-plugin-doctor'); ?></h2>
            <p><?php _e('All plugins except WP Plugin Doctor have been disabled. Your site is running in safe mode.', 'wp-plugin-doctor'); ?></p>
            <p>
                <button class="button button-primary button-large wppd-exit-safe-mode">
                    <?php _e('Exit Safe Mode & Restore Plugins', 'wp-plugin-doctor'); ?>
                </button>
            </p>
        </div>
    <?php else: ?>
        <div class="notice notice-info notice-large">
            <h2><?php _e('ℹ️ Safe Mode is Not Active', 'wp-plugin-doctor'); ?></h2>
            <p><?php _e('Safe mode allows you to disable all plugins except WP Plugin Doctor to troubleshoot issues and regain access to your dashboard.', 'wp-plugin-doctor'); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Safe Mode Information -->
    <div class="wppd-box">
        <h2><?php _e('What is Safe Mode?', 'wp-plugin-doctor'); ?></h2>
        
        <p><?php _e('Safe Mode is an emergency recovery feature that temporarily disables all plugins except WP Plugin Doctor. This helps you:', 'wp-plugin-doctor'); ?></p>
        
        <ul>
            <li><?php _e('Regain access to your dashboard when a plugin conflict causes errors', 'wp-plugin-doctor'); ?></li>
            <li><?php _e('Isolate and identify which plugin is causing problems', 'wp-plugin-doctor'); ?></li>
            <li><?php _e('Safely re-enable plugins one by one to pinpoint conflicts', 'wp-plugin-doctor'); ?></li>
            <li><?php _e('Restore your site functionality without FTP access', 'wp-plugin-doctor'); ?></li>
        </ul>
        
        <h3><?php _e('How to Use Safe Mode', 'wp-plugin-doctor'); ?></h3>
        
        <ol>
            <li><?php _e('Click "Enable Safe Mode" below or use the emergency URL', 'wp-plugin-doctor'); ?></li>
            <li><?php _e('All plugins except WP Plugin Doctor will be disabled', 'wp-plugin-doctor'); ?></li>
            <li><?php _e('Go to the Plugins page and re-enable plugins one at a time', 'wp-plugin-doctor'); ?></li>
            <li><?php _e('Monitor for errors after each plugin activation', 'wp-plugin-doctor'); ?></li>
            <li><?php _e('Once issues are resolved, exit Safe Mode to restore all plugins', 'wp-plugin-doctor'); ?></li>
        </ol>
    </div>
    
    <!-- Safe Mode Controls -->
    <div class="wppd-box">
        <h2><?php _e('Safe Mode Controls', 'wp-plugin-doctor'); ?></h2>
        
        <?php if (!$is_safe_mode): ?>
            <div class="wppd-safe-mode-enable">
                <p><?php _e('Enable safe mode to disable all plugins except WP Plugin Doctor:', 'wp-plugin-doctor'); ?></p>
                <button class="button button-primary button-hero wppd-enable-safe-mode">
                    <span class="dashicons dashicons-shield-alt"></span>
                    <?php _e('Enable Safe Mode Now', 'wp-plugin-doctor'); ?>
                </button>
            </div>
        <?php else: ?>
            <div class="wppd-safe-mode-disable">
                <p><?php _e('Exit safe mode to restore all previously active plugins:', 'wp-plugin-doctor'); ?></p>
                <button class="button button-primary button-hero wppd-exit-safe-mode">
                    <span class="dashicons dashicons-yes"></span>
                    <?php _e('Exit Safe Mode', 'wp-plugin-doctor'); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Emergency Access URL -->
    <div class="wppd-box">
        <h2><?php _e('Emergency Access URL', 'wp-plugin-doctor'); ?></h2>
        
        <p><?php _e('If you cannot access your dashboard due to a fatal error, use this special URL to enable safe mode:', 'wp-plugin-doctor'); ?></p>
        
        <div class="wppd-emergency-url">
            <input type="text" readonly value="<?php echo esc_attr($safe_mode_url); ?>" class="large-text code" id="wppd-safe-mode-url">
            <button class="button wppd-copy-url" data-clipboard-target="#wppd-safe-mode-url">
                <span class="dashicons dashicons-clipboard"></span>
                <?php _e('Copy URL', 'wp-plugin-doctor'); ?>
            </button>
        </div>
        
        <p class="description">
            <?php _e('⚠️ Keep this URL secure! Anyone with this URL can enable safe mode on your site. Generate a new URL if you suspect it has been compromised.', 'wp-plugin-doctor'); ?>
        </p>
        
        <p>
            <button class="button wppd-regenerate-url">
                <?php _e('Regenerate Emergency URL', 'wp-plugin-doctor'); ?>
            </button>
        </p>
    </div>
    
    <!-- Plugin Backups -->
    <div class="wppd-box">
        <h2><?php _e('Plugin Configuration Backups', 'wp-plugin-doctor'); ?></h2>
        
        <p><?php _e('WP Plugin Doctor automatically creates backups of your active plugins configuration. You can restore from these backups if needed.', 'wp-plugin-doctor'); ?></p>
        
        <?php if (empty($backups)): ?>
            <p class="description"><?php _e('No backups available yet. Backups are created automatically when you make plugin changes.', 'wp-plugin-doctor'); ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Backup Date', 'wp-plugin-doctor'); ?></th>
                        <th><?php _e('Active Plugins', 'wp-plugin-doctor'); ?></th>
                        <th><?php _e('Actions', 'wp-plugin-doctor'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $index => $backup): ?>
                    <tr>
                        <td>
                            <?php echo esc_html(date('Y-m-d H:i:s', strtotime($backup['timestamp']))); ?>
                        </td>
                        <td>
                            <?php echo count($backup['active_plugins']); ?> plugins
                        </td>
                        <td>
                            <button class="button button-small wppd-restore-backup" data-backup-index="<?php echo esc_attr($index); ?>">
                                <?php _e('Restore', 'wp-plugin-doctor'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <p style="margin-top: 15px;">
            <button class="button wppd-create-backup">
                <?php _e('Create Backup Now', 'wp-plugin-doctor'); ?>
            </button>
        </p>
    </div>
    
    <!-- Currently Disabled Plugins (when in safe mode) -->
    <?php if ($is_safe_mode): ?>
    <div class="wppd-box">
        <h2><?php _e('Disabled Plugins', 'wp-plugin-doctor'); ?></h2>
        
        <p><?php _e('The following plugins are currently disabled by safe mode:', 'wp-plugin-doctor'); ?></p>
        
        <?php
        $disabled_plugins = get_option('wppd_disabled_plugins', array());
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $all_plugins = get_plugins();
        ?>
        
        <ul class="wppd-disabled-plugins-list">
            <?php foreach ($disabled_plugins as $plugin_file): ?>
                <?php if (isset($all_plugins[$plugin_file])): ?>
                <li>
                    <span class="dashicons dashicons-admin-plugins"></span>
                    <?php echo esc_html($all_plugins[$plugin_file]['Name']); ?>
                </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
</div>

<script>
jQuery(document).ready(function($) {
    // Copy URL to clipboard
    $('.wppd-copy-url').on('click', function() {
        var input = $($(this).data('clipboard-target'));
        input.select();
        document.execCommand('copy');
        
        var originalText = $(this).html();
        $(this).html('<span class="dashicons dashicons-yes"></span> Copied!');
        
        setTimeout(function() {
            $('.wppd-copy-url').html(originalText);
        }, 2000);
    });
    
    // Regenerate URL
    $('.wppd-regenerate-url').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to regenerate the emergency URL? The old URL will no longer work.', 'wp-plugin-doctor'); ?>')) {
            $.post(wppdAdmin.ajaxurl, {
                action: 'wppd_regenerate_safe_mode_url',
                nonce: wppdAdmin.safemode_nonce
            }, function(response) {
                if (response.success) {
                    $('#wppd-safe-mode-url').val(response.data.url);
                    alert('<?php _e('Emergency URL has been regenerated.', 'wp-plugin-doctor'); ?>');
                }
            });
        }
    });
    
    // Create backup
    $('.wppd-create-backup').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).text('<?php _e('Creating backup...', 'wp-plugin-doctor'); ?>');
        
        $.post(wppdAdmin.ajaxurl, {
            action: 'wppd_create_backup',
            nonce: wppdAdmin.nonce
        }, function(response) {
            if (response.success) {
                alert('<?php _e('Backup created successfully.', 'wp-plugin-doctor'); ?>');
                location.reload();
            }
            btn.prop('disabled', false).text('<?php _e('Create Backup Now', 'wp-plugin-doctor'); ?>');
        });
    });
    
    // Restore backup
    $('.wppd-restore-backup').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to restore this backup? Your current plugin configuration will be replaced.', 'wp-plugin-doctor'); ?>')) {
            var backupIndex = $(this).data('backup-index');
            
            $.post(wppdAdmin.ajaxurl, {
                action: 'wppd_restore_backup',
                nonce: wppdAdmin.nonce,
                backup_index: backupIndex
            }, function(response) {
                if (response.success) {
                    alert('<?php _e('Backup restored successfully. Page will reload.', 'wp-plugin-doctor'); ?>');
                    location.reload();
                }
            });
        }
    });
});
</script>
