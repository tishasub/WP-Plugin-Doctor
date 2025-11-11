<?php
/**
 * Errors Page View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wppd-errors-page">
    <h1 class="wp-heading-inline">
        <?php _e('Error Log', 'wp-plugin-doctor'); ?>
    </h1>
    
    <a href="#" class="page-title-action wppd-clear-all-errors">
        <?php _e('Clear All Errors', 'wp-plugin-doctor'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <!-- Filters -->
    <div class="wppd-filters">
        <form method="get" action="<?php echo admin_url('admin.php'); ?>">
            <input type="hidden" name="page" value="wppd-errors">
            
            <select name="severity">
                <option value=""><?php _e('All Severities', 'wp-plugin-doctor'); ?></option>
                <option value="fatal" <?php selected(isset($_GET['severity']) && $_GET['severity'] === 'fatal'); ?>>
                    <?php _e('Fatal', 'wp-plugin-doctor'); ?>
                </option>
                <option value="warning" <?php selected(isset($_GET['severity']) && $_GET['severity'] === 'warning'); ?>>
                    <?php _e('Warning', 'wp-plugin-doctor'); ?>
                </option>
                <option value="notice" <?php selected(isset($_GET['severity']) && $_GET['severity'] === 'notice'); ?>>
                    <?php _e('Notice', 'wp-plugin-doctor'); ?>
                </option>
            </select>
            
            <select name="plugin">
                <option value=""><?php _e('All Plugins', 'wp-plugin-doctor'); ?></option>
                <?php
                global $wpdb;
                $table = $wpdb->prefix . 'wppd_errors';
                $plugins = $wpdb->get_col("SELECT DISTINCT plugin_name FROM $table WHERE plugin_name IS NOT NULL AND plugin_name != '' ORDER BY plugin_name");
                foreach ($plugins as $plugin_name):
                ?>
                    <option value="<?php echo esc_attr($plugin_name); ?>" <?php selected(isset($_GET['plugin']) && $_GET['plugin'] === $plugin_name); ?>>
                        <?php echo esc_html($plugin_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="button"><?php _e('Filter', 'wp-plugin-doctor'); ?></button>
            <a href="<?php echo admin_url('admin.php?page=wppd-errors'); ?>" class="button">
                <?php _e('Reset', 'wp-plugin-doctor'); ?>
            </a>
        </form>
    </div>
    
    <!-- Errors Table -->
    <?php if (empty($errors)): ?>
        <div class="wppd-empty-state">
            <span class="dashicons dashicons-yes-alt"></span>
            <p><?php _e('No errors found.', 'wp-plugin-doctor'); ?></p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="wppd-col-severity"><?php _e('Severity', 'wp-plugin-doctor'); ?></th>
                    <th class="wppd-col-type"><?php _e('Type', 'wp-plugin-doctor'); ?></th>
                    <th class="wppd-col-message"><?php _e('Message', 'wp-plugin-doctor'); ?></th>
                    <th class="wppd-col-plugin"><?php _e('Plugin', 'wp-plugin-doctor'); ?></th>
                    <th class="wppd-col-time"><?php _e('Time', 'wp-plugin-doctor'); ?></th>
                    <th class="wppd-col-actions"><?php _e('Actions', 'wp-plugin-doctor'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($errors as $error): ?>
                <tr class="wppd-error-row wppd-severity-<?php echo esc_attr($error->severity); ?>">
                    <td class="wppd-col-severity">
                        <span class="wppd-severity-badge wppd-severity-<?php echo esc_attr($error->severity); ?>">
                            <?php echo esc_html(ucfirst($error->severity)); ?>
                        </span>
                    </td>
                    <td class="wppd-col-type">
                        <?php echo esc_html($error->error_type); ?>
                    </td>
                    <td class="wppd-col-message">
                        <div class="wppd-error-message-preview">
                            <?php echo esc_html(wp_trim_words($error->error_message, 15)); ?>
                        </div>
                        <?php if (!empty($error->error_file)): ?>
                        <div class="wppd-error-file">
                            <small>
                                <?php echo esc_html(basename($error->error_file)); ?>
                                <?php if ($error->error_line): ?>
                                    :<?php echo esc_html($error->error_line); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="wppd-col-plugin">
                        <?php if (!empty($error->plugin_name)): ?>
                            <span class="wppd-plugin-badge">
                                <?php echo esc_html($error->plugin_name); ?>
                            </span>
                        <?php else: ?>
                            <span class="wppd-unknown">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="wppd-col-time">
                        <?php echo human_time_diff(strtotime($error->created_at), current_time('timestamp')); ?> ago
                        <br>
                        <small><?php echo esc_html(date('Y-m-d H:i', strtotime($error->created_at))); ?></small>
                    </td>
                    <td class="wppd-col-actions">
                        <button class="button button-small wppd-view-error" data-error-id="<?php echo esc_attr($error->id); ?>">
                            <?php _e('View', 'wp-plugin-doctor'); ?>
                        </button>
                        <button class="button button-small wppd-delete-error" data-error-id="<?php echo esc_attr($error->id); ?>">
                            <?php _e('Delete', 'wp-plugin-doctor'); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="tablenav">
            <div class="tablenav-pages">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $page
                ));
                ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    
</div>

<!-- Error Details Modal -->
<div id="wppd-error-modal" class="wppd-modal" style="display: none;">
    <div class="wppd-modal-content wppd-modal-large">
        <span class="wppd-modal-close">&times;</span>
        <h2><?php _e('Error Details', 'wp-plugin-doctor'); ?></h2>
        <div id="wppd-error-details"></div>
    </div>
</div>
