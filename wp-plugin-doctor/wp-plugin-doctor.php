<?php
/**
 * Plugin Name: WP Plugin Doctor
 * Plugin URI: https://github.com/tishasub/WP-Plugin-Doctor
 * Description: Automatically detects plugin conflicts, monitors PHP errors, and provides actionable solutions to fix WordPress site issues.
 * Version: 1.0.0
 * Author: tisha92
 * Author URI: https://betatech.co
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-plugin-doctor
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPPD_VERSION', '1.0.0');
define('WPPD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPPD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPPD_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once WPPD_PLUGIN_DIR . 'includes/class-error-handler.php';
require_once WPPD_PLUGIN_DIR . 'includes/class-conflict-detector.php';
require_once WPPD_PLUGIN_DIR . 'includes/class-database.php';
require_once WPPD_PLUGIN_DIR . 'includes/class-admin.php';
require_once WPPD_PLUGIN_DIR . 'includes/class-safe-mode.php';

/**
 * Main Plugin Class
 */
class WP_Plugin_Doctor {

    private static $instance = null;

    public $error_handler;
    public $conflict_detector;
    public $database;
    public $admin;
    public $safe_mode;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize plugin
     */
    private function init() {
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Initialize components
        $this->database = new WPPD_Database();
        $this->error_handler = new WPPD_Error_Handler();
        $this->conflict_detector = new WPPD_Conflict_Detector();
        $this->safe_mode = new WPPD_Safe_Mode();

        // Initialize admin interface
        if (is_admin()) {
            $this->admin = new WPPD_Admin();
        }

        // Add actions
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init_monitoring'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        $this->database->create_tables();

        // Set default options
        if (!get_option('wppd_settings')) {
            update_option('wppd_settings', array(
                'enable_monitoring' => true,
                'enable_email_alerts' => false,
                'alert_email' => get_option('admin_email'),
                'error_levels' => array('fatal', 'warning', 'notice'),
                'enable_safe_mode' => true,
                'auto_disable_conflicts' => false,
                'log_retention_days' => 30
            ));
        }

        // Schedule cleanup job
        if (!wp_next_scheduled('wppd_cleanup_old_logs')) {
            wp_schedule_event(time(), 'daily', 'wppd_cleanup_old_logs');
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('wppd_cleanup_old_logs');
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('wp-plugin-doctor', false, dirname(WPPD_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Initialize error monitoring
     */
    public function init_monitoring() {
        $settings = get_option('wppd_settings', array());

        if (isset($settings['enable_monitoring']) && $settings['enable_monitoring']) {
            $this->error_handler->start_monitoring();
        }
    }
}

// Initialize the plugin
function wppd_init() {
    return WP_Plugin_Doctor::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'wppd_init', 1);
