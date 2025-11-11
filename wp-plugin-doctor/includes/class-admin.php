<?php
/**
 * Admin Interface Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPPD_Admin {
    
    private $database;
    private $safe_mode;
    
    public function __construct() {
        $this->database = new WPPD_Database();
        $this->safe_mode = new WPPD_Safe_Mode();
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add admin bar menu
        add_action('admin_bar_menu', array($this, 'add_admin_bar_menu'), 100);
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // Register AJAX handlers
        add_action('wp_ajax_wppd_get_error_details', array($this, 'ajax_get_error_details'));
        add_action('wp_ajax_wppd_update_error_status', array($this, 'ajax_update_error_status'));
        add_action('wp_ajax_wppd_delete_error', array($this, 'ajax_delete_error'));
        add_action('wp_ajax_wppd_clear_all_errors', array($this, 'ajax_clear_all_errors'));
        add_action('wp_ajax_wppd_scan_conflicts', array($this, 'ajax_scan_conflicts'));
        add_action('wp_ajax_wppd_resolve_conflict', array($this, 'ajax_resolve_conflict'));
        
        // Dashboard widget
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        
        // Settings
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WP Plugin Doctor', 'wp-plugin-doctor'),
            __('Plugin Doctor', 'wp-plugin-doctor'),
            'manage_options',
            'wp-plugin-doctor',
            array($this, 'render_dashboard_page'),
            'dashicons-heart',
            80
        );
        
        add_submenu_page(
            'wp-plugin-doctor',
            __('Dashboard', 'wp-plugin-doctor'),
            __('Dashboard', 'wp-plugin-doctor'),
            'manage_options',
            'wp-plugin-doctor',
            array($this, 'render_dashboard_page')
        );
        
        add_submenu_page(
            'wp-plugin-doctor',
            __('Error Log', 'wp-plugin-doctor'),
            __('Error Log', 'wp-plugin-doctor'),
            'manage_options',
            'wppd-errors',
            array($this, 'render_errors_page')
        );
        
        add_submenu_page(
            'wp-plugin-doctor',
            __('Conflicts', 'wp-plugin-doctor'),
            __('Conflicts', 'wp-plugin-doctor'),
            'manage_options',
            'wppd-conflicts',
            array($this, 'render_conflicts_page')
        );
        
        add_submenu_page(
            'wp-plugin-doctor',
            __('Safe Mode', 'wp-plugin-doctor'),
            __('Safe Mode', 'wp-plugin-doctor'),
            'manage_options',
            'wppd-safe-mode',
            array($this, 'render_safe_mode_page')
        );
        
        add_submenu_page(
            'wp-plugin-doctor',
            __('Settings', 'wp-plugin-doctor'),
            __('Settings', 'wp-plugin-doctor'),
            'manage_options',
            'wppd-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Add admin bar menu
     */
    public function add_admin_bar_menu($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get recent error count
        $recent_errors = $this->database->get_error_count(array(
            'date_from' => date('Y-m-d H:i:s', strtotime('-24 hours'))
        ));
        
        $title = __('Plugin Doctor', 'wp-plugin-doctor');
        if ($recent_errors > 0) {
            $title .= ' <span class="wppd-admin-bar-count">' . $recent_errors . '</span>';
        }
        
        $wp_admin_bar->add_node(array(
            'id' => 'wp-plugin-doctor',
            'title' => $title,
            'href' => admin_url('admin.php?page=wp-plugin-doctor'),
            'meta' => array('class' => 'wppd-admin-bar')
        ));
        
        $wp_admin_bar->add_node(array(
            'parent' => 'wp-plugin-doctor',
            'id' => 'wppd-dashboard',
            'title' => __('Dashboard', 'wp-plugin-doctor'),
            'href' => admin_url('admin.php?page=wp-plugin-doctor')
        ));
        
        $wp_admin_bar->add_node(array(
            'parent' => 'wp-plugin-doctor',
            'id' => 'wppd-errors',
            'title' => __('View Errors', 'wp-plugin-doctor'),
            'href' => admin_url('admin.php?page=wppd-errors')
        ));
        
        if ($this->safe_mode->is_safe_mode_active()) {
            $wp_admin_bar->add_node(array(
                'parent' => 'wp-plugin-doctor',
                'id' => 'wppd-exit-safe-mode',
                'title' => __('⚠️ Exit Safe Mode', 'wp-plugin-doctor'),
                'href' => '#',
                'meta' => array('class' => 'wppd-exit-safe-mode-link')
            ));
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wp-plugin-doctor') === false && strpos($hook, 'wppd-') === false) {
            return;
        }
        
        wp_enqueue_style('wppd-admin', WPPD_PLUGIN_URL . 'assets/css/admin.css', array(), WPPD_VERSION);
        wp_enqueue_script('wppd-admin', WPPD_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), WPPD_VERSION, true);
        
        wp_localize_script('wppd-admin', 'wppdAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wppd_actions'),
            'safemode_nonce' => wp_create_nonce('wppd_safe_mode'),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this error?', 'wp-plugin-doctor'),
                'confirm_clear_all' => __('Are you sure you want to clear all errors? This cannot be undone.', 'wp-plugin-doctor'),
                'confirm_safe_mode' => __('Are you sure you want to enable safe mode? This will disable all plugins except WP Plugin Doctor.', 'wp-plugin-doctor'),
                'loading' => __('Loading...', 'wp-plugin-doctor')
            )
        ));
        
        // Chart.js for statistics
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', array(), '3.9.1', true);
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wppd_settings_group', 'wppd_settings');
    }
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'wppd_dashboard_widget',
            __('Plugin Health Monitor', 'wp-plugin-doctor'),
            array($this, 'render_dashboard_widget')
        );
    }
    
    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        $stats = $this->database->get_error_stats(7);
        $conflicts = $this->database->get_conflicts('active');
        
        ?>
        <div class="wppd-widget">
            <div class="wppd-widget-stats">
                <div class="wppd-stat">
                    <span class="wppd-stat-number"><?php echo esc_html($stats['total']); ?></span>
                    <span class="wppd-stat-label"><?php _e('Total Errors (7 days)', 'wp-plugin-doctor'); ?></span>
                </div>
                <div class="wppd-stat wppd-stat-fatal">
                    <span class="wppd-stat-number"><?php echo esc_html($stats['fatal']); ?></span>
                    <span class="wppd-stat-label"><?php _e('Fatal', 'wp-plugin-doctor'); ?></span>
                </div>
                <div class="wppd-stat wppd-stat-warning">
                    <span class="wppd-stat-number"><?php echo esc_html($stats['warning']); ?></span>
                    <span class="wppd-stat-label"><?php _e('Warning', 'wp-plugin-doctor'); ?></span>
                </div>
                <div class="wppd-stat wppd-stat-conflicts">
                    <span class="wppd-stat-number"><?php echo count($conflicts); ?></span>
                    <span class="wppd-stat-label"><?php _e('Active Conflicts', 'wp-plugin-doctor'); ?></span>
                </div>
            </div>
            
            <?php if ($stats['fatal'] > 0): ?>
            <div class="wppd-widget-alert">
                <p><?php _e('⚠️ Your site has fatal errors that need attention.', 'wp-plugin-doctor'); ?></p>
            </div>
            <?php endif; ?>
            
            <p class="wppd-widget-link">
                <a href="<?php echo admin_url('admin.php?page=wp-plugin-doctor'); ?>" class="button button-primary">
                    <?php _e('View Full Dashboard', 'wp-plugin-doctor'); ?>
                </a>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        $stats = $this->database->get_error_stats(7);
        $plugin_stats = $this->database->get_plugin_stats(7);
        $conflicts = $this->database->get_conflicts('active');
        $recent_errors = $this->database->get_recent_errors(10);
        
        include WPPD_PLUGIN_DIR . 'includes/views/dashboard.php';
    }
    
    /**
     * Render errors page
     */
    public function render_errors_page() {
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $filters = array();
        if (isset($_GET['severity'])) {
            $filters['severity'] = sanitize_text_field($_GET['severity']);
        }
        if (isset($_GET['plugin'])) {
            $filters['plugin'] = sanitize_text_field($_GET['plugin']);
        }
        
        $errors = $this->database->get_recent_errors($per_page, $offset, $filters);
        $total_errors = $this->database->get_error_count($filters);
        $total_pages = ceil($total_errors / $per_page);
        
        include WPPD_PLUGIN_DIR . 'includes/views/errors.php';
    }
    
    /**
     * Render conflicts page
     */
    public function render_conflicts_page() {
        $conflicts = $this->database->get_conflicts('active');
        
        include WPPD_PLUGIN_DIR . 'includes/views/conflicts.php';
    }
    
    /**
     * Render safe mode page
     */
    public function render_safe_mode_page() {
        $is_safe_mode = $this->safe_mode->is_safe_mode_active();
        $safe_mode_url = $this->safe_mode->generate_safe_mode_url();
        $backups = $this->safe_mode->get_backups();
        
        include WPPD_PLUGIN_DIR . 'includes/views/safe-mode.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (isset($_POST['wppd_settings_submit'])) {
            check_admin_referer('wppd_settings');
            
            $settings = array(
                'enable_monitoring' => isset($_POST['enable_monitoring']),
                'enable_email_alerts' => isset($_POST['enable_email_alerts']),
                'alert_email' => sanitize_email($_POST['alert_email']),
                'error_levels' => isset($_POST['error_levels']) ? array_map('sanitize_text_field', $_POST['error_levels']) : array(),
                'enable_safe_mode' => isset($_POST['enable_safe_mode']),
                'auto_disable_conflicts' => isset($_POST['auto_disable_conflicts']),
                'log_retention_days' => intval($_POST['log_retention_days'])
            );
            
            update_option('wppd_settings', $settings);
            
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'wp-plugin-doctor') . '</p></div>';
        }
        
        $settings = get_option('wppd_settings', array());
        
        include WPPD_PLUGIN_DIR . 'includes/views/settings.php';
    }
    
    /**
     * AJAX: Get error details
     */
    public function ajax_get_error_details() {
        check_ajax_referer('wppd_actions', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $error_id = isset($_POST['error_id']) ? intval($_POST['error_id']) : 0;
        
        if (!$error_id) {
            wp_send_json_error('Invalid error ID');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'wppd_errors';
        $error = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $error_id));
        
        if (!$error) {
            wp_send_json_error('Error not found');
        }
        
        wp_send_json_success($error);
    }
    
    /**
     * AJAX: Update error status
     */
    public function ajax_update_error_status() {
        check_ajax_referer('wppd_actions', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $error_id = isset($_POST['error_id']) ? intval($_POST['error_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        
        $this->database->update_error_status($error_id, $status);
        
        wp_send_json_success(array('message' => __('Error status updated', 'wp-plugin-doctor')));
    }
    
    /**
     * AJAX: Delete error
     */
    public function ajax_delete_error() {
        check_ajax_referer('wppd_actions', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $error_id = isset($_POST['error_id']) ? intval($_POST['error_id']) : 0;
        
        $this->database->delete_error($error_id);
        
        wp_send_json_success(array('message' => __('Error deleted', 'wp-plugin-doctor')));
    }
    
    /**
     * AJAX: Clear all errors
     */
    public function ajax_clear_all_errors() {
        check_ajax_referer('wppd_actions', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $this->database->delete_all_errors();
        
        wp_send_json_success(array('message' => __('All errors cleared', 'wp-plugin-doctor')));
    }
    
    /**
     * AJAX: Scan for conflicts
     */
    public function ajax_scan_conflicts() {
        check_ajax_referer('wppd_actions', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $detector = new WPPD_Conflict_Detector();
        $conflicts_found = $detector->scan_for_conflicts();
        
        wp_send_json_success(array(
            'message' => sprintf(__('Scan complete. Found %d potential conflicts.', 'wp-plugin-doctor'), $conflicts_found),
            'conflicts' => $conflicts_found
        ));
    }
    
    /**
     * AJAX: Resolve conflict
     */
    public function ajax_resolve_conflict() {
        check_ajax_referer('wppd_actions', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $conflict_id = isset($_POST['conflict_id']) ? intval($_POST['conflict_id']) : 0;
        
        $this->database->update_conflict_status($conflict_id, 'resolved');
        
        wp_send_json_success(array('message' => __('Conflict marked as resolved', 'wp-plugin-doctor')));
    }
}
