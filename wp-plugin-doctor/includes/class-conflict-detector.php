<?php
/**
 * Conflict Detector Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPPD_Conflict_Detector {
    
    private $database;
    
    public function __construct() {
        $this->database = new WPPD_Database();
    }
    
    /**
     * Analyze error for potential conflicts
     */
    public function analyze_error($plugin_slug, $error_data) {
        // Get list of active plugins
        $active_plugins = $this->get_active_plugins();
        
        // Check for function conflicts
        $this->check_function_conflicts($plugin_slug, $active_plugins, $error_data);
        
        // Check for hook conflicts
        $this->check_hook_conflicts($plugin_slug, $active_plugins, $error_data);
        
        // Check for JavaScript conflicts
        if (strpos($error_data['error_message'], 'jQuery') !== false || 
            strpos($error_data['error_message'], 'JavaScript') !== false) {
            $this->check_javascript_conflicts($plugin_slug, $active_plugins);
        }
        
        // Check for style conflicts
        if (strpos($error_data['error_file'], '.css') !== false) {
            $this->check_style_conflicts($plugin_slug, $active_plugins);
        }
    }
    
    /**
     * Get active plugins
     */
    private function get_active_plugins() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $active_plugins = get_option('active_plugins', array());
        $all_plugins = get_plugins();
        
        $plugins = array();
        foreach ($active_plugins as $plugin_file) {
            if (isset($all_plugins[$plugin_file])) {
                $plugin_slug = dirname($plugin_file);
                if ($plugin_slug === '.') {
                    $plugin_slug = basename($plugin_file, '.php');
                }
                
                $plugins[$plugin_slug] = array(
                    'name' => $all_plugins[$plugin_file]['Name'],
                    'version' => $all_plugins[$plugin_file]['Version'],
                    'file' => $plugin_file
                );
            }
        }
        
        return $plugins;
    }
    
    /**
     * Check for function conflicts
     */
    private function check_function_conflicts($plugin_slug, $active_plugins, $error_data) {
        // Check if error message indicates function redeclaration
        if (stripos($error_data['error_message'], 'cannot redeclare') !== false ||
            stripos($error_data['error_message'], 'function') !== false) {
            
            // Extract function name from error message
            preg_match('/function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/i', $error_data['error_message'], $matches);
            
            if (isset($matches[1])) {
                $function_name = $matches[1];
                
                // Check which other plugins might have this function
                foreach ($active_plugins as $slug => $plugin_info) {
                    if ($slug === $plugin_slug) {
                        continue;
                    }
                    
                    // Check if this plugin might have the conflicting function
                    if ($this->plugin_has_function($slug, $function_name)) {
                        $this->database->log_conflict(
                            $plugin_slug,
                            $slug,
                            'function_redeclaration',
                            "Both plugins attempt to declare function: {$function_name}",
                            75
                        );
                    }
                }
            }
        }
    }
    
    /**
     * Check if plugin has a function
     */
    private function plugin_has_function($plugin_slug, $function_name) {
        $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
        
        if (!is_dir($plugin_dir)) {
            return false;
        }
        
        // Search for function in plugin files (simple grep-like search)
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($plugin_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        $php_files = array();
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php_files[] = $file->getPathname();
                
                // Limit to 20 files for performance
                if (count($php_files) >= 20) {
                    break;
                }
            }
        }
        
        foreach ($php_files as $file) {
            $content = @file_get_contents($file);
            if ($content && strpos($content, 'function ' . $function_name) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for hook conflicts
     */
    private function check_hook_conflicts($plugin_slug, $active_plugins, $error_data) {
        global $wp_filter;
        
        // Get hooks used by this plugin
        $common_hooks = array('init', 'wp_enqueue_scripts', 'admin_enqueue_scripts', 'wp_head', 'wp_footer');
        
        foreach ($common_hooks as $hook) {
            if (!isset($wp_filter[$hook])) {
                continue;
            }
            
            $callbacks = $wp_filter[$hook]->callbacks;
            
            // Check for multiple plugins on same hook with same priority
            foreach ($callbacks as $priority => $functions) {
                $plugin_count = 0;
                $plugins_on_hook = array();
                
                foreach ($functions as $function) {
                    $callback_plugin = $this->identify_callback_plugin($function['function']);
                    
                    if ($callback_plugin && !in_array($callback_plugin, $plugins_on_hook)) {
                        $plugins_on_hook[] = $callback_plugin;
                        $plugin_count++;
                    }
                }
                
                // If multiple plugins use same hook at same priority, potential conflict
                if ($plugin_count > 1 && in_array($plugin_slug, $plugins_on_hook)) {
                    foreach ($plugins_on_hook as $other_plugin) {
                        if ($other_plugin !== $plugin_slug) {
                            $this->database->log_conflict(
                                $plugin_slug,
                                $other_plugin,
                                'hook_priority_conflict',
                                "Both plugins use hook '{$hook}' at priority {$priority}",
                                50
                            );
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Identify plugin from callback
     */
    private function identify_callback_plugin($callback) {
        $plugin_dir = WP_PLUGIN_DIR;
        
        // Handle different callback types
        if (is_array($callback)) {
            if (is_object($callback[0])) {
                $reflection = new ReflectionClass($callback[0]);
                $file = $reflection->getFileName();
            } elseif (is_string($callback[0])) {
                if (class_exists($callback[0])) {
                    $reflection = new ReflectionClass($callback[0]);
                    $file = $reflection->getFileName();
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } elseif (is_string($callback)) {
            if (function_exists($callback)) {
                $reflection = new ReflectionFunction($callback);
                $file = $reflection->getFileName();
            } else {
                return null;
            }
        } else {
            return null;
        }
        
        if (empty($file) || strpos($file, $plugin_dir) === false) {
            return null;
        }
        
        // Extract plugin slug
        $relative_path = str_replace($plugin_dir . '/', '', $file);
        $parts = explode('/', $relative_path);
        
        return isset($parts[0]) ? $parts[0] : null;
    }
    
    /**
     * Check for JavaScript conflicts
     */
    private function check_javascript_conflicts($plugin_slug, $active_plugins) {
        // Check for jQuery version conflicts
        global $wp_scripts;
        
        if (!$wp_scripts) {
            return;
        }
        
        $jquery_versions = array();
        
        foreach ($active_plugins as $slug => $plugin_info) {
            // Check if plugin enqueues custom jQuery
            if ($this->plugin_enqueues_jquery($slug)) {
                $jquery_versions[$slug] = true;
            }
        }
        
        if (count($jquery_versions) > 1 && isset($jquery_versions[$plugin_slug])) {
            foreach ($jquery_versions as $other_slug => $value) {
                if ($other_slug !== $plugin_slug) {
                    $this->database->log_conflict(
                        $plugin_slug,
                        $other_slug,
                        'jquery_conflict',
                        'Both plugins enqueue their own jQuery versions',
                        65
                    );
                }
            }
        }
    }
    
    /**
     * Check if plugin enqueues jQuery
     */
    private function plugin_enqueues_jquery($plugin_slug) {
        $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
        
        if (!is_dir($plugin_dir)) {
            return false;
        }
        
        // Search for jQuery enqueue in plugin files
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($plugin_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = @file_get_contents($file->getPathname());
                if ($content && (
                    strpos($content, 'wp_enqueue_script') !== false && 
                    strpos($content, 'jquery') !== false
                )) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check for style conflicts
     */
    private function check_style_conflicts($plugin_slug, $active_plugins) {
        // Basic style conflict detection
        // In a real implementation, this would check for CSS selector conflicts
        // For now, we'll just log potential conflicts based on common patterns
        
        foreach ($active_plugins as $slug => $plugin_info) {
            if ($slug === $plugin_slug) {
                continue;
            }
            
            // Check if both plugins load styles
            if ($this->plugin_has_styles($plugin_slug) && $this->plugin_has_styles($slug)) {
                $this->database->log_conflict(
                    $plugin_slug,
                    $slug,
                    'style_conflict',
                    'Both plugins enqueue styles that may conflict',
                    40
                );
            }
        }
    }
    
    /**
     * Check if plugin has styles
     */
    private function plugin_has_styles($plugin_slug) {
        $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
        
        if (!is_dir($plugin_dir)) {
            return false;
        }
        
        // Check for CSS files or wp_enqueue_style calls
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($plugin_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                if ($file->getExtension() === 'css') {
                    return true;
                }
                
                if ($file->getExtension() === 'php') {
                    $content = @file_get_contents($file->getPathname());
                    if ($content && strpos($content, 'wp_enqueue_style') !== false) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Scan for potential conflicts (manual scan)
     */
    public function scan_for_conflicts() {
        $active_plugins = $this->get_active_plugins();
        $conflicts_found = 0;
        
        // Check each pair of plugins
        $plugin_slugs = array_keys($active_plugins);
        
        for ($i = 0; $i < count($plugin_slugs); $i++) {
            for ($j = $i + 1; $j < count($plugin_slugs); $j++) {
                $plugin_a = $plugin_slugs[$i];
                $plugin_b = $plugin_slugs[$j];
                
                // Check for common conflict patterns
                if ($this->check_common_conflicts($plugin_a, $plugin_b)) {
                    $conflicts_found++;
                }
            }
        }
        
        return $conflicts_found;
    }
    
    /**
     * Check for common conflict patterns between two plugins
     */
    private function check_common_conflicts($plugin_a, $plugin_b) {
        $conflict_found = false;
        
        // Check for similar functionality (crude check based on plugin names)
        $name_a = strtolower($plugin_a);
        $name_b = strtolower($plugin_b);
        
        $common_keywords = array('seo', 'cache', 'security', 'backup', 'contact-form', 'gallery', 'slider');
        
        foreach ($common_keywords as $keyword) {
            if (strpos($name_a, $keyword) !== false && strpos($name_b, $keyword) !== false) {
                $this->database->log_conflict(
                    $plugin_a,
                    $plugin_b,
                    'similar_functionality',
                    "Both plugins provide {$keyword} functionality and may conflict",
                    35
                );
                $conflict_found = true;
            }
        }
        
        return $conflict_found;
    }
}
