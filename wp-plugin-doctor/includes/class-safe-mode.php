<?php
/**
 * Safe Mode Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPPD_Safe_Mode {
    
    private $safe_mode_option = 'wppd_safe_mode_active';
    private $disabled_plugins_option = 'wppd_disabled_plugins';
    
    public function __construct() {
        // Check for safe mode parameter
        add_action('plugins_loaded', array($this, 'check_safe_mode_request'), 1);
        
        // Add safe mode notice
        add_action('admin_notices', array($this, 'safe_mode_notice'));
        
        // Handle AJAX requests
        add_action('wp_ajax_wppd_enable_safe_mode', array($this, 'ajax_enable_safe_mode'));
        add_action('wp_ajax_wppd_disable_safe_mode', array($this, 'ajax_disable_safe_mode'));
        add_action('wp_ajax_wppd_disable_plugin', array($this, 'ajax_disable_plugin'));
    }
    
    /**
     * Check for safe mode URL parameter
     */
    public function check_safe_mode_request() {
        // Check for safe mode key in URL
        if (isset($_GET['wppd_safe_mode']) && isset($_GET['wppd_key'])) {
            $key = get_option('wppd_safe_mode_key');
            
            if ($_GET['wppd_key'] === $key) {
                $this->enable_safe_mode();
                wp_redirect(admin_url('admin.php?page=wp-plugin-doctor'));
                exit;
            }
        }
    }
    
    /**
     * Enable safe mode
     */
    public function enable_safe_mode() {
        // Get currently active plugins
        $active_plugins = get_option('active_plugins', array());
        
        // Store them for later restoration
        update_option($this->disabled_plugins_option, $active_plugins);
        
        // Disable all plugins except WP Plugin Doctor
        $safe_plugins = array();
        foreach ($active_plugins as $plugin) {
            if (strpos($plugin, 'wp-plugin-doctor') !== false) {
                $safe_plugins[] = $plugin;
            }
        }
        
        update_option('active_plugins', $safe_plugins);
        update_option($this->safe_mode_option, true);
        
        return true;
    }
    
    /**
     * Disable safe mode
     */
    public function disable_safe_mode() {
        // Restore previously active plugins
        $disabled_plugins = get_option($this->disabled_plugins_option, array());
        
        if (!empty($disabled_plugins)) {
            update_option('active_plugins', $disabled_plugins);
        }
        
        delete_option($this->safe_mode_option);
        delete_option($this->disabled_plugins_option);
        
        return true;
    }
    
    /**
     * Check if safe mode is active
     */
    public function is_safe_mode_active() {
        return get_option($this->safe_mode_option, false);
    }
    
    /**
     * Display safe mode notice
     */
    public function safe_mode_notice() {
        if (!$this->is_safe_mode_active()) {
            return;
        }
        
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong><?php _e('Safe Mode Active', 'wp-plugin-doctor'); ?></strong> - 
                <?php _e('All plugins except WP Plugin Doctor have been disabled.', 'wp-plugin-doctor'); ?>
                <a href="#" id="wppd-exit-safe-mode" class="button button-small" style="margin-left: 10px;">
                    <?php _e('Exit Safe Mode', 'wp-plugin-doctor'); ?>
                </a>
            </p>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#wppd-exit-safe-mode').on('click', function(e) {
                e.preventDefault();
                if (confirm('<?php _e('Are you sure you want to exit safe mode and re-enable all plugins?', 'wp-plugin-doctor'); ?>')) {
                    $.post(ajaxurl, {
                        action: 'wppd_disable_safe_mode',
                        nonce: '<?php echo wp_create_nonce('wppd_safe_mode'); ?>'
                    }, function(response) {
                        if (response.success) {
                            window.location.reload();
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Generate safe mode URL
     */
    public function generate_safe_mode_url() {
        // Generate or get existing safe mode key
        $key = get_option('wppd_safe_mode_key');
        
        if (!$key) {
            $key = wp_generate_password(32, false);
            update_option('wppd_safe_mode_key', $key);
        }
        
        return add_query_arg(
            array(
                'wppd_safe_mode' => '1',
                'wppd_key' => $key
            ),
            home_url()
        );
    }
    
    /**
     * AJAX: Enable safe mode
     */
    public function ajax_enable_safe_mode() {
        check_ajax_referer('wppd_safe_mode', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $this->enable_safe_mode();
        
        wp_send_json_success(array(
            'message' => __('Safe mode enabled. All plugins have been disabled.', 'wp-plugin-doctor')
        ));
    }
    
    /**
     * AJAX: Disable safe mode
     */
    public function ajax_disable_safe_mode() {
        check_ajax_referer('wppd_safe_mode', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $this->disable_safe_mode();
        
        wp_send_json_success(array(
            'message' => __('Safe mode disabled. All plugins have been restored.', 'wp-plugin-doctor')
        ));
    }
    
    /**
     * AJAX: Disable specific plugin
     */
    public function ajax_disable_plugin() {
        check_ajax_referer('wppd_actions', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $plugin_file = isset($_POST['plugin']) ? sanitize_text_field($_POST['plugin']) : '';
        
        if (empty($plugin_file)) {
            wp_send_json_error('No plugin specified');
        }
        
        // Deactivate the plugin
        deactivate_plugins($plugin_file);
        
        wp_send_json_success(array(
            'message' => sprintf(__('Plugin %s has been disabled.', 'wp-plugin-doctor'), $plugin_file)
        ));
    }
    
    /**
     * Create backup of plugin configuration
     */
    public function create_backup() {
        $backup = array(
            'active_plugins' => get_option('active_plugins', array()),
            'timestamp' => current_time('mysql'),
            'site_url' => get_site_url()
        );
        
        $backups = get_option('wppd_plugin_backups', array());
        
        // Keep only last 5 backups
        if (count($backups) >= 5) {
            array_shift($backups);
        }
        
        $backups[] = $backup;
        update_option('wppd_plugin_backups', $backups);
        
        return true;
    }
    
    /**
     * Restore from backup
     */
    public function restore_backup($backup_index) {
        $backups = get_option('wppd_plugin_backups', array());
        
        if (!isset($backups[$backup_index])) {
            return false;
        }
        
        $backup = $backups[$backup_index];
        
        update_option('active_plugins', $backup['active_plugins']);
        
        return true;
    }
    
    /**
     * Get available backups
     */
    public function get_backups() {
        return get_option('wppd_plugin_backups', array());
    }
}
