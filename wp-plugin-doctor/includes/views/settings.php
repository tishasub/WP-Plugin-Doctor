<?php
/**
 * Settings Page View
 */

if (!defined('ABSPATH')) {
    exit;
}

$defaults = array(
    'enable_monitoring' => true,
    'enable_email_alerts' => false,
    'alert_email' => get_option('admin_email'),
    'error_levels' => array('fatal', 'warning', 'notice'),
    'enable_safe_mode' => true,
    'auto_disable_conflicts' => false,
    'log_retention_days' => 30
);

$settings = wp_parse_args($settings, $defaults);
?>

<div class="wrap wppd-settings-page">
    <h1><?php _e('WP Plugin Doctor Settings', 'wp-plugin-doctor'); ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin.php?page=wppd-settings'); ?>">
        <?php wp_nonce_field('wppd_settings'); ?>
        
        <table class="form-table">
            <!-- Error Monitoring -->
            <tr>
                <th scope="row">
                    <h2><?php _e('Error Monitoring', 'wp-plugin-doctor'); ?></h2>
                </th>
                <td></td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="enable_monitoring">
                        <?php _e('Enable Error Monitoring', 'wp-plugin-doctor'); ?>
                    </label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_monitoring" id="enable_monitoring" value="1" <?php checked($settings['enable_monitoring'], true); ?>>
                        <?php _e('Monitor and log PHP errors, warnings, and notices', 'wp-plugin-doctor'); ?>
                    </label>
                    <p class="description">
                        <?php _e('When enabled, WP Plugin Doctor will capture all PHP errors on your site.', 'wp-plugin-doctor'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label><?php _e('Error Levels to Monitor', 'wp-plugin-doctor'); ?></label>
                </th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="error_levels[]" value="fatal" <?php checked(in_array('fatal', $settings['error_levels'])); ?>>
                            <?php _e('Fatal Errors', 'wp-plugin-doctor'); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="error_levels[]" value="warning" <?php checked(in_array('warning', $settings['error_levels'])); ?>>
                            <?php _e('Warnings', 'wp-plugin-doctor'); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="error_levels[]" value="notice" <?php checked(in_array('notice', $settings['error_levels'])); ?>>
                            <?php _e('Notices', 'wp-plugin-doctor'); ?>
                        </label>
                    </fieldset>
                    <p class="description">
                        <?php _e('Select which error severity levels should be logged. Fatal errors are always recommended.', 'wp-plugin-doctor'); ?>
                    </p>
                </td>
            </tr>
            
            <!-- Email Alerts -->
            <tr>
                <th scope="row">
                    <h2><?php _e('Email Alerts', 'wp-plugin-doctor'); ?></h2>
                </th>
                <td></td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="enable_email_alerts">
                        <?php _e('Enable Email Alerts', 'wp-plugin-doctor'); ?>
                    </label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_email_alerts" id="enable_email_alerts" value="1" <?php checked($settings['enable_email_alerts'], true); ?>>
                        <?php _e('Send email notifications for critical errors', 'wp-plugin-doctor'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Receive email alerts when fatal errors are detected.', 'wp-plugin-doctor'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="alert_email">
                        <?php _e('Alert Email Address', 'wp-plugin-doctor'); ?>
                    </label>
                </th>
                <td>
                    <input type="email" name="alert_email" id="alert_email" value="<?php echo esc_attr($settings['alert_email']); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Email address to receive error alerts.', 'wp-plugin-doctor'); ?>
                    </p>
                </td>
            </tr>
            
            <!-- Safe Mode -->
            <tr>
                <th scope="row">
                    <h2><?php _e('Safe Mode', 'wp-plugin-doctor'); ?></h2>
                </th>
                <td></td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="enable_safe_mode">
                        <?php _e('Enable Safe Mode Feature', 'wp-plugin-doctor'); ?>
                    </label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_safe_mode" id="enable_safe_mode" value="1" <?php checked($settings['enable_safe_mode'], true); ?>>
                        <?php _e('Allow safe mode activation', 'wp-plugin-doctor'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Enables the safe mode feature for emergency recovery.', 'wp-plugin-doctor'); ?>
                    </p>
                </td>
            </tr>
            
            <!-- Conflict Detection -->
            <tr>
                <th scope="row">
                    <h2><?php _e('Conflict Detection', 'wp-plugin-doctor'); ?></h2>
                </th>
                <td></td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="auto_disable_conflicts">
                        <?php _e('Auto-Disable Conflicting Plugins', 'wp-plugin-doctor'); ?>
                    </label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="auto_disable_conflicts" id="auto_disable_conflicts" value="1" <?php checked($settings['auto_disable_conflicts'], true); ?>>
                        <?php _e('Automatically disable plugins when critical conflicts are detected', 'wp-plugin-doctor'); ?>
                    </label>
                    <p class="description">
                        <?php _e('⚠️ Use with caution: This will automatically deactivate plugins that cause fatal errors.', 'wp-plugin-doctor'); ?>
                    </p>
                </td>
            </tr>
            
            <!-- Data Management -->
            <tr>
                <th scope="row">
                    <h2><?php _e('Data Management', 'wp-plugin-doctor'); ?></h2>
                </th>
                <td></td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="log_retention_days">
                        <?php _e('Log Retention Period', 'wp-plugin-doctor'); ?>
                    </label>
                </th>
                <td>
                    <input type="number" name="log_retention_days" id="log_retention_days" value="<?php echo esc_attr($settings['log_retention_days']); ?>" min="1" max="365" class="small-text">
                    <?php _e('days', 'wp-plugin-doctor'); ?>
                    <p class="description">
                        <?php _e('Error logs older than this will be automatically deleted. Default: 30 days.', 'wp-plugin-doctor'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label><?php _e('Clear All Data', 'wp-plugin-doctor'); ?></label>
                </th>
                <td>
                    <button type="button" class="button button-secondary wppd-clear-all-data">
                        <?php _e('Clear All Error Logs', 'wp-plugin-doctor'); ?>
                    </button>
                    <p class="description">
                        <?php _e('Permanently delete all error logs and conflict data. This cannot be undone.', 'wp-plugin-doctor'); ?>
                    </p>
                </td>
            </tr>
            
        </table>
        
        <p class="submit">
            <input type="submit" name="wppd_settings_submit" class="button button-primary" value="<?php _e('Save Settings', 'wp-plugin-doctor'); ?>">
        </p>
    </form>
    
    <!-- System Information -->
    <div class="wppd-box" style="margin-top: 30px;">
        <h2><?php _e('System Information', 'wp-plugin-doctor'); ?></h2>
        
        <table class="widefat">
            <tr>
                <td><strong><?php _e('WordPress Version:', 'wp-plugin-doctor'); ?></strong></td>
                <td><?php echo get_bloginfo('version'); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('PHP Version:', 'wp-plugin-doctor'); ?></strong></td>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('Active Plugins:', 'wp-plugin-doctor'); ?></strong></td>
                <td><?php echo count(get_option('active_plugins', array())); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('Total Error Logs:', 'wp-plugin-doctor'); ?></strong></td>
                <td>
                    <?php
                    global $wpdb;
                    $table = $wpdb->prefix . 'wppd_errors';
                    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
                    echo number_format($count);
                    ?>
                </td>
            </tr>
            <tr>
                <td><strong><?php _e('Plugin Version:', 'wp-plugin-doctor'); ?></strong></td>
                <td><?php echo WPPD_VERSION; ?></td>
            </tr>
        </table>
    </div>
    
</div>

<script>
jQuery(document).ready(function($) {
    $('.wppd-clear-all-data').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to clear all error logs? This action cannot be undone.', 'wp-plugin-doctor'); ?>')) {
            if (confirm('<?php _e('This will permanently delete all error data. Are you absolutely sure?', 'wp-plugin-doctor'); ?>')) {
                $.post(wppdAdmin.ajaxurl, {
                    action: 'wppd_clear_all_errors',
                    nonce: wppdAdmin.nonce
                }, function(response) {
                    if (response.success) {
                        alert('<?php _e('All error logs have been cleared.', 'wp-plugin-doctor'); ?>');
                        location.reload();
                    }
                });
            }
        }
    });
});
</script>
