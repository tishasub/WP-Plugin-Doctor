<?php
/**
 * Dashboard View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wppd-dashboard">
    <h1 class="wp-heading-inline">
        <?php _e('WP Plugin Doctor Dashboard', 'wp-plugin-doctor'); ?>
    </h1>
    
    <?php if ($this->safe_mode->is_safe_mode_active()): ?>
    <div class="notice notice-warning" style="margin-top: 20px;">
        <p>
            <strong><?php _e('⚠️ Safe Mode Active', 'wp-plugin-doctor'); ?></strong> - 
            <?php _e('All plugins except WP Plugin Doctor are currently disabled.', 'wp-plugin-doctor'); ?>
            <a href="<?php echo admin_url('admin.php?page=wppd-safe-mode'); ?>" class="button button-small">
                <?php _e('Manage Safe Mode', 'wp-plugin-doctor'); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>
    
    <hr class="wp-header-end">
    
    <!-- Statistics Cards -->
    <div class="wppd-stats-grid">
        <div class="wppd-stat-card">
            <div class="wppd-stat-icon wppd-stat-total">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="wppd-stat-content">
                <h3><?php echo esc_html($stats['total']); ?></h3>
                <p><?php _e('Total Errors (7 days)', 'wp-plugin-doctor'); ?></p>
            </div>
        </div>
        
        <div class="wppd-stat-card">
            <div class="wppd-stat-icon wppd-stat-fatal">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
            <div class="wppd-stat-content">
                <h3><?php echo esc_html($stats['fatal']); ?></h3>
                <p><?php _e('Fatal Errors', 'wp-plugin-doctor'); ?></p>
            </div>
        </div>
        
        <div class="wppd-stat-card">
            <div class="wppd-stat-icon wppd-stat-warning">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="wppd-stat-content">
                <h3><?php echo esc_html($stats['warning']); ?></h3>
                <p><?php _e('Warnings', 'wp-plugin-doctor'); ?></p>
            </div>
        </div>
        
        <div class="wppd-stat-card">
            <div class="wppd-stat-icon wppd-stat-conflicts">
                <span class="dashicons dashicons-plugins-checked"></span>
            </div>
            <div class="wppd-stat-content">
                <h3><?php echo count($conflicts); ?></h3>
                <p><?php _e('Active Conflicts', 'wp-plugin-doctor'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="wppd-dashboard-content">
        
        <!-- Left Column -->
        <div class="wppd-dashboard-left">
            
            <!-- Recent Errors -->
            <div class="wppd-box">
                <h2><?php _e('Recent Errors', 'wp-plugin-doctor'); ?></h2>
                
                <?php if (empty($recent_errors)): ?>
                    <div class="wppd-empty-state">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <p><?php _e('No errors detected recently. Your site is healthy!', 'wp-plugin-doctor'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="wppd-error-list">
                        <?php foreach ($recent_errors as $error): ?>
                        <div class="wppd-error-item wppd-severity-<?php echo esc_attr($error->severity); ?>">
                            <div class="wppd-error-header">
                                <span class="wppd-severity-badge wppd-severity-<?php echo esc_attr($error->severity); ?>">
                                    <?php echo esc_html(ucfirst($error->severity)); ?>
                                </span>
                                <span class="wppd-error-type"><?php echo esc_html($error->error_type); ?></span>
                                <span class="wppd-error-time"><?php echo human_time_diff(strtotime($error->created_at), current_time('timestamp')) . ' ago'; ?></span>
                            </div>
                            <div class="wppd-error-message">
                                <?php echo esc_html(wp_trim_words($error->error_message, 20)); ?>
                            </div>
                            <?php if (!empty($error->plugin_name)): ?>
                            <div class="wppd-error-plugin">
                                <span class="dashicons dashicons-admin-plugins"></span>
                                <?php echo esc_html($error->plugin_name); ?>
                            </div>
                            <?php endif; ?>
                            <div class="wppd-error-actions">
                                <button class="button button-small wppd-view-error" data-error-id="<?php echo esc_attr($error->id); ?>">
                                    <?php _e('View Details', 'wp-plugin-doctor'); ?>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <p class="wppd-view-all">
                        <a href="<?php echo admin_url('admin.php?page=wppd-errors'); ?>" class="button">
                            <?php _e('View All Errors', 'wp-plugin-doctor'); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Plugin Statistics Chart -->
            <div class="wppd-box">
                <h2><?php _e('Errors by Plugin', 'wp-plugin-doctor'); ?></h2>
                
                <?php if (empty($plugin_stats)): ?>
                    <div class="wppd-empty-state">
                        <p><?php _e('No plugin errors to display.', 'wp-plugin-doctor'); ?></p>
                    </div>
                <?php else: ?>
                    <canvas id="wppd-plugin-chart" width="400" height="300"></canvas>
                    
                    <script>
                    jQuery(document).ready(function($) {
                        var ctx = document.getElementById('wppd-plugin-chart').getContext('2d');
                        
                        var pluginData = {};
                        <?php foreach ($plugin_stats as $stat): ?>
                            if (!pluginData['<?php echo esc_js($stat->plugin_name); ?>']) {
                                pluginData['<?php echo esc_js($stat->plugin_name); ?>'] = {
                                    fatal: 0,
                                    warning: 0,
                                    notice: 0
                                };
                            }
                            pluginData['<?php echo esc_js($stat->plugin_name); ?>']['<?php echo esc_js($stat->severity); ?>'] = <?php echo intval($stat->count); ?>;
                        <?php endforeach; ?>
                        
                        var labels = Object.keys(pluginData);
                        var fatalData = labels.map(function(label) { return pluginData[label].fatal; });
                        var warningData = labels.map(function(label) { return pluginData[label].warning; });
                        var noticeData = labels.map(function(label) { return pluginData[label].notice; });
                        
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'Fatal',
                                        data: fatalData,
                                        backgroundColor: '#dc3232'
                                    },
                                    {
                                        label: 'Warning',
                                        data: warningData,
                                        backgroundColor: '#f0ad4e'
                                    },
                                    {
                                        label: 'Notice',
                                        data: noticeData,
                                        backgroundColor: '#5bc0de'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                scales: {
                                    x: {
                                        stacked: true
                                    },
                                    y: {
                                        stacked: true,
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    });
                    </script>
                <?php endif; ?>
            </div>
            
        </div>
        
        <!-- Right Column -->
        <div class="wppd-dashboard-right">
            
            <!-- Quick Actions -->
            <div class="wppd-box">
                <h2><?php _e('Quick Actions', 'wp-plugin-doctor'); ?></h2>
                
                <div class="wppd-quick-actions">
                    <button class="button button-primary button-hero wppd-scan-conflicts">
                        <span class="dashicons dashicons-search"></span>
                        <?php _e('Scan for Conflicts', 'wp-plugin-doctor'); ?>
                    </button>
                    
                    <button class="button button-secondary button-hero wppd-enable-safe-mode">
                        <span class="dashicons dashicons-shield"></span>
                        <?php _e('Enable Safe Mode', 'wp-plugin-doctor'); ?>
                    </button>
                    
                    <a href="<?php echo admin_url('admin.php?page=wppd-errors'); ?>" class="button button-secondary button-hero">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php _e('View Error Log', 'wp-plugin-doctor'); ?>
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=wppd-settings'); ?>" class="button button-secondary button-hero">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php _e('Settings', 'wp-plugin-doctor'); ?>
                    </a>
                </div>
            </div>
            
            <!-- Active Conflicts -->
            <div class="wppd-box">
                <h2><?php _e('Active Conflicts', 'wp-plugin-doctor'); ?></h2>
                
                <?php if (empty($conflicts)): ?>
                    <div class="wppd-empty-state">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <p><?php _e('No active conflicts detected.', 'wp-plugin-doctor'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="wppd-conflict-list">
                        <?php foreach (array_slice($conflicts, 0, 5) as $conflict): ?>
                        <div class="wppd-conflict-item">
                            <div class="wppd-conflict-plugins">
                                <strong><?php echo esc_html($conflict->plugin_a); ?></strong>
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                <strong><?php echo esc_html($conflict->plugin_b); ?></strong>
                            </div>
                            <div class="wppd-conflict-type">
                                <?php echo esc_html($conflict->conflict_type); ?>
                            </div>
                            <div class="wppd-conflict-confidence">
                                <?php _e('Confidence:', 'wp-plugin-doctor'); ?> 
                                <span class="wppd-confidence-score"><?php echo esc_html($conflict->confidence_score); ?>%</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($conflicts) > 5): ?>
                    <p class="wppd-view-all">
                        <a href="<?php echo admin_url('admin.php?page=wppd-conflicts'); ?>" class="button">
                            <?php printf(__('View All %d Conflicts', 'wp-plugin-doctor'), count($conflicts)); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Site Health -->
            <div class="wppd-box">
                <h2><?php _e('Site Health Score', 'wp-plugin-doctor'); ?></h2>
                
                <?php
                // Calculate health score
                $health_score = 100;
                $health_score -= min(30, $stats['fatal'] * 10);
                $health_score -= min(20, $stats['warning'] * 2);
                $health_score -= min(15, count($conflicts) * 5);
                $health_score = max(0, $health_score);
                
                $health_class = 'good';
                if ($health_score < 50) {
                    $health_class = 'critical';
                } elseif ($health_score < 75) {
                    $health_class = 'warning';
                }
                ?>
                
                <div class="wppd-health-score wppd-health-<?php echo esc_attr($health_class); ?>">
                    <div class="wppd-health-number"><?php echo esc_html($health_score); ?></div>
                    <div class="wppd-health-label"><?php _e('Health Score', 'wp-plugin-doctor'); ?></div>
                </div>
                
                <div class="wppd-health-details">
                    <?php if ($health_score >= 75): ?>
                        <p><?php _e('✅ Your site is in good health! Keep monitoring for any new issues.', 'wp-plugin-doctor'); ?></p>
                    <?php elseif ($health_score >= 50): ?>
                        <p><?php _e('⚠️ Your site has some issues that should be addressed. Review the errors and conflicts.', 'wp-plugin-doctor'); ?></p>
                    <?php else: ?>
                        <p><?php _e('🚨 Your site has critical issues that need immediate attention. Consider enabling safe mode.', 'wp-plugin-doctor'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
    </div>
    
</div>

<!-- Error Details Modal -->
<div id="wppd-error-modal" class="wppd-modal" style="display: none;">
    <div class="wppd-modal-content">
        <span class="wppd-modal-close">&times;</span>
        <h2><?php _e('Error Details', 'wp-plugin-doctor'); ?></h2>
        <div id="wppd-error-details"></div>
    </div>
</div>
