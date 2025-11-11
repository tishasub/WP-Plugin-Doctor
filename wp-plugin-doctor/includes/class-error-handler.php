<?php
/**
 * Error Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPPD_Error_Handler {
    
    private $database;
    private $monitoring_active = false;
    
    public function __construct() {
        $this->database = new WPPD_Database();
    }
    
    /**
     * Start error monitoring
     */
    public function start_monitoring() {
        if ($this->monitoring_active) {
            return;
        }
        
        // Set custom error handler
        set_error_handler(array($this, 'handle_error'), E_ALL);
        
        // Set shutdown handler for fatal errors
        register_shutdown_function(array($this, 'handle_shutdown'));
        
        // Set exception handler
        set_exception_handler(array($this, 'handle_exception'));
        
        $this->monitoring_active = true;
    }
    
    /**
     * Handle PHP errors
     */
    public function handle_error($errno, $errstr, $errfile = '', $errline = 0) {
        // Skip if error reporting is turned off
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        // Determine error type and severity
        $error_info = $this->get_error_info($errno);
        
        // Skip if this error level is not monitored
        $settings = get_option('wppd_settings', array());
        if (isset($settings['error_levels']) && !in_array($error_info['severity'], $settings['error_levels'])) {
            return false;
        }
        
        // Get plugin information
        $plugin_info = $this->identify_plugin_from_file($errfile);
        
        // Prepare error data
        $error_data = array(
            'error_type' => $error_info['type'],
            'error_message' => $errstr,
            'error_file' => $errfile,
            'error_line' => $errline,
            'stack_trace' => $this->get_stack_trace(),
            'plugin_slug' => $plugin_info['slug'],
            'plugin_name' => $plugin_info['name'],
            'url' => $this->get_current_url(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'severity' => $error_info['severity']
        );
        
        // Log the error
        $error_id = $this->database->log_error($error_data);
        
        // Check for conflicts if plugin identified
        if (!empty($plugin_info['slug'])) {
            $this->check_for_conflicts($plugin_info['slug'], $error_data);
        }
        
        // Send alert if needed
        $this->maybe_send_alert($error_data, $error_id);
        
        // Don't prevent default error handling
        return false;
    }
    
    /**
     * Handle shutdown (fatal errors)
     */
    public function handle_shutdown() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            $this->handle_error($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
    
    /**
     * Handle exceptions
     */
    public function handle_exception($exception) {
        $plugin_info = $this->identify_plugin_from_file($exception->getFile());
        
        $error_data = array(
            'error_type' => 'Exception',
            'error_message' => $exception->getMessage(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'plugin_slug' => $plugin_info['slug'],
            'plugin_name' => $plugin_info['name'],
            'url' => $this->get_current_url(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'severity' => 'fatal'
        );
        
        $this->database->log_error($error_data);
    }
    
    /**
     * Get error type information
     */
    private function get_error_info($errno) {
        $error_types = array(
            E_ERROR => array('type' => 'E_ERROR', 'severity' => 'fatal'),
            E_WARNING => array('type' => 'E_WARNING', 'severity' => 'warning'),
            E_PARSE => array('type' => 'E_PARSE', 'severity' => 'fatal'),
            E_NOTICE => array('type' => 'E_NOTICE', 'severity' => 'notice'),
            E_CORE_ERROR => array('type' => 'E_CORE_ERROR', 'severity' => 'fatal'),
            E_CORE_WARNING => array('type' => 'E_CORE_WARNING', 'severity' => 'warning'),
            E_COMPILE_ERROR => array('type' => 'E_COMPILE_ERROR', 'severity' => 'fatal'),
            E_COMPILE_WARNING => array('type' => 'E_COMPILE_WARNING', 'severity' => 'warning'),
            E_USER_ERROR => array('type' => 'E_USER_ERROR', 'severity' => 'fatal'),
            E_USER_WARNING => array('type' => 'E_USER_WARNING', 'severity' => 'warning'),
            E_USER_NOTICE => array('type' => 'E_USER_NOTICE', 'severity' => 'notice'),
            E_STRICT => array('type' => 'E_STRICT', 'severity' => 'notice'),
            E_RECOVERABLE_ERROR => array('type' => 'E_RECOVERABLE_ERROR', 'severity' => 'warning'),
            E_DEPRECATED => array('type' => 'E_DEPRECATED', 'severity' => 'notice'),
            E_USER_DEPRECATED => array('type' => 'E_USER_DEPRECATED', 'severity' => 'notice')
        );
        
        return isset($error_types[$errno]) ? $error_types[$errno] : array('type' => 'Unknown', 'severity' => 'notice');
    }
    
    /**
     * Identify plugin from file path
     */
    private function identify_plugin_from_file($file) {
        $plugin_dir = WP_PLUGIN_DIR;
        
        // Check if file is in plugins directory
        if (strpos($file, $plugin_dir) === false) {
            return array('slug' => '', 'name' => '');
        }
        
        // Extract plugin slug from path
        $relative_path = str_replace($plugin_dir . '/', '', $file);
        $parts = explode('/', $relative_path);
        
        if (empty($parts[0])) {
            return array('slug' => '', 'name' => '');
        }
        
        $plugin_slug = $parts[0];
        
        // Get plugin name
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_plugins = get_plugins();
        $plugin_name = '';
        
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            if (strpos($plugin_file, $plugin_slug . '/') === 0) {
                $plugin_name = $plugin_data['Name'];
                break;
            }
        }
        
        return array(
            'slug' => $plugin_slug,
            'name' => $plugin_name ? $plugin_name : $plugin_slug
        );
    }
    
    /**
     * Get stack trace
     */
    private function get_stack_trace() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        
        // Remove error handler entries
        array_shift($trace);
        array_shift($trace);
        
        $trace_string = '';
        foreach ($trace as $i => $t) {
            $trace_string .= sprintf(
                "#%d %s(%d): %s%s%s()\n",
                $i,
                isset($t['file']) ? $t['file'] : 'unknown',
                isset($t['line']) ? $t['line'] : 0,
                isset($t['class']) ? $t['class'] : '',
                isset($t['type']) ? $t['type'] : '',
                isset($t['function']) ? $t['function'] : ''
            );
        }
        
        return $trace_string;
    }
    
    /**
     * Get current URL
     */
    private function get_current_url() {
        if (defined('DOING_CRON') && DOING_CRON) {
            return 'wp-cron.php';
        }
        
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return 'admin-ajax.php';
        }
        
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        
        return $protocol . '://' . $host . $uri;
    }
    
    /**
     * Check for conflicts
     */
    private function check_for_conflicts($plugin_slug, $error_data) {
        // Get conflict detector instance
        $conflict_detector = new WPPD_Conflict_Detector();
        $conflict_detector->analyze_error($plugin_slug, $error_data);
    }
    
    /**
     * Send alert if needed
     */
    private function maybe_send_alert($error_data, $error_id) {
        $settings = get_option('wppd_settings', array());
        
        // Only send alerts for fatal errors or if enabled for all
        if (!isset($settings['enable_email_alerts']) || !$settings['enable_email_alerts']) {
            return;
        }
        
        if ($error_data['severity'] !== 'fatal') {
            return;
        }
        
        $to = isset($settings['alert_email']) ? $settings['alert_email'] : get_option('admin_email');
        $subject = '[' . get_bloginfo('name') . '] Plugin Error Alert - ' . $error_data['error_type'];
        
        $message = "A critical error has been detected on your WordPress site.\n\n";
        $message .= "Error Type: " . $error_data['error_type'] . "\n";
        $message .= "Severity: " . $error_data['severity'] . "\n";
        $message .= "Message: " . $error_data['error_message'] . "\n\n";
        
        if (!empty($error_data['plugin_name'])) {
            $message .= "Suspected Plugin: " . $error_data['plugin_name'] . "\n\n";
        }
        
        $message .= "File: " . $error_data['error_file'] . "\n";
        $message .= "Line: " . $error_data['error_line'] . "\n";
        $message .= "URL: " . $error_data['url'] . "\n\n";
        
        $message .= "View full details in your dashboard:\n";
        $message .= admin_url('admin.php?page=wp-plugin-doctor&view=error&id=' . $error_id);
        
        wp_mail($to, $subject, $message);
    }
}
