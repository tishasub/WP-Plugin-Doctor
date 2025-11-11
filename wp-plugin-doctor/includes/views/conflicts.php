<?php
/**
 * Conflicts Page View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wppd-conflicts-page">
    <h1 class="wp-heading-inline">
        <?php _e('Plugin Conflicts', 'wp-plugin-doctor'); ?>
    </h1>
    
    <button class="page-title-action wppd-scan-conflicts">
        <?php _e('Scan for Conflicts', 'wp-plugin-doctor'); ?>
    </button>
    
    <hr class="wp-header-end">
    
    <p class="description">
        <?php _e('This page shows detected conflicts between plugins on your site. Higher confidence scores indicate more reliable detections.', 'wp-plugin-doctor'); ?>
    </p>
    
    <?php if (empty($conflicts)): ?>
        <div class="wppd-empty-state">
            <span class="dashicons dashicons-yes-alt"></span>
            <h3><?php _e('No Active Conflicts Detected', 'wp-plugin-doctor'); ?></h3>
            <p><?php _e('Your plugins appear to be working together harmoniously!', 'wp-plugin-doctor'); ?></p>
            <p>
                <button class="button button-primary wppd-scan-conflicts">
                    <?php _e('Run Manual Scan', 'wp-plugin-doctor'); ?>
                </button>
            </p>
        </div>
    <?php else: ?>
        <div class="wppd-conflicts-list">
            <?php foreach ($conflicts as $conflict): ?>
            <div class="wppd-conflict-card">
                <div class="wppd-conflict-header">
                    <div class="wppd-conflict-plugins-header">
                        <span class="wppd-plugin-name"><?php echo esc_html($conflict->plugin_a); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                        <span class="wppd-plugin-name"><?php echo esc_html($conflict->plugin_b); ?></span>
                    </div>
                    <div class="wppd-conflict-confidence">
                        <span class="wppd-confidence-label"><?php _e('Confidence:', 'wp-plugin-doctor'); ?></span>
                        <span class="wppd-confidence-score wppd-confidence-<?php echo $conflict->confidence_score >= 70 ? 'high' : ($conflict->confidence_score >= 40 ? 'medium' : 'low'); ?>">
                            <?php echo esc_html($conflict->confidence_score); ?>%
                        </span>
                    </div>
                </div>
                
                <div class="wppd-conflict-body">
                    <div class="wppd-conflict-type">
                        <strong><?php _e('Conflict Type:', 'wp-plugin-doctor'); ?></strong>
                        <span class="wppd-type-badge">
                            <?php echo esc_html(str_replace('_', ' ', ucwords($conflict->conflict_type, '_'))); ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($conflict->description)): ?>
                    <div class="wppd-conflict-description">
                        <strong><?php _e('Description:', 'wp-plugin-doctor'); ?></strong>
                        <p><?php echo esc_html($conflict->description); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="wppd-conflict-meta">
                        <div class="wppd-meta-item">
                            <span class="dashicons dashicons-clock"></span>
                            <?php _e('First seen:', 'wp-plugin-doctor'); ?>
                            <?php echo esc_html(date('Y-m-d H:i', strtotime($conflict->created_at))); ?>
                        </div>
                        <div class="wppd-meta-item">
                            <span class="dashicons dashicons-update"></span>
                            <?php _e('Last seen:', 'wp-plugin-doctor'); ?>
                            <?php echo human_time_diff(strtotime($conflict->last_seen), current_time('timestamp')); ?> ago
                        </div>
                        <div class="wppd-meta-item">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <?php printf(__('Occurred %d times', 'wp-plugin-doctor'), $conflict->occurrences); ?>
                        </div>
                    </div>
                </div>
                
                <div class="wppd-conflict-footer">
                    <div class="wppd-conflict-recommendations">
                        <strong><?php _e('Recommendations:', 'wp-plugin-doctor'); ?></strong>
                        <ul>
                            <?php
                            switch ($conflict->conflict_type) {
                                case 'function_redeclaration':
                                    echo '<li>' . __('Both plugins are trying to declare the same function. Consider keeping only one of these plugins.', 'wp-plugin-doctor') . '</li>';
                                    echo '<li>' . __('Contact the plugin authors to report this conflict.', 'wp-plugin-doctor') . '</li>';
                                    break;
                                case 'hook_priority_conflict':
                                    echo '<li>' . __('Both plugins use the same hook with the same priority. This may cause unpredictable behavior.', 'wp-plugin-doctor') . '</li>';
                                    echo '<li>' . __('Try deactivating one plugin temporarily to see if issues resolve.', 'wp-plugin-doctor') . '</li>';
                                    break;
                                case 'jquery_conflict':
                                    echo '<li>' . __('Multiple jQuery versions may be loaded, causing JavaScript errors.', 'wp-plugin-doctor') . '</li>';
                                    echo '<li>' . __('Check if either plugin has a setting to disable custom jQuery loading.', 'wp-plugin-doctor') . '</li>';
                                    break;
                                case 'similar_functionality':
                                    echo '<li>' . __('These plugins provide similar functionality and may interfere with each other.', 'wp-plugin-doctor') . '</li>';
                                    echo '<li>' . __('Consider using only one plugin for this purpose.', 'wp-plugin-doctor') . '</li>';
                                    break;
                                default:
                                    echo '<li>' . __('Review error logs for more details about this conflict.', 'wp-plugin-doctor') . '</li>';
                                    echo '<li>' . __('Try deactivating one plugin to see if the issue resolves.', 'wp-plugin-doctor') . '</li>';
                            }
                            ?>
                        </ul>
                    </div>
                    
                    <div class="wppd-conflict-actions">
                        <button class="button wppd-resolve-conflict" data-conflict-id="<?php echo esc_attr($conflict->id); ?>">
                            <?php _e('Mark as Resolved', 'wp-plugin-doctor'); ?>
                        </button>
                        
                        <?php if (!function_exists('get_plugins')) {
                            require_once ABSPATH . 'wp-admin/includes/plugin.php';
                        }
                        $all_plugins = get_plugins();
                        
                        // Find plugin files
                        $plugin_a_file = '';
                        $plugin_b_file = '';
                        foreach ($all_plugins as $plugin_file => $plugin_data) {
                            if (strpos($plugin_file, $conflict->plugin_a . '/') === 0) {
                                $plugin_a_file = $plugin_file;
                            }
                            if (strpos($plugin_file, $conflict->plugin_b . '/') === 0) {
                                $plugin_b_file = $plugin_file;
                            }
                        }
                        ?>
                        
                        <?php if ($plugin_a_file): ?>
                        <button class="button wppd-disable-plugin" data-plugin="<?php echo esc_attr($plugin_a_file); ?>">
                            <?php printf(__('Disable %s', 'wp-plugin-doctor'), esc_html($conflict->plugin_a)); ?>
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($plugin_b_file): ?>
                        <button class="button wppd-disable-plugin" data-plugin="<?php echo esc_attr($plugin_b_file); ?>">
                            <?php printf(__('Disable %s', 'wp-plugin-doctor'), esc_html($conflict->plugin_b)); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
</div>
