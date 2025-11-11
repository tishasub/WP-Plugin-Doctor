<?php
/**
 * Database Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPPD_Database {
    
    private $table_errors;
    private $table_conflicts;
    
    public function __construct() {
        global $wpdb;
        $this->table_errors = $wpdb->prefix . 'wppd_errors';
        $this->table_conflicts = $wpdb->prefix . 'wppd_conflicts';
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql_errors = "CREATE TABLE IF NOT EXISTS {$this->table_errors} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            error_type varchar(50) NOT NULL,
            error_message text NOT NULL,
            error_file varchar(255) DEFAULT NULL,
            error_line int(11) DEFAULT NULL,
            stack_trace longtext DEFAULT NULL,
            plugin_slug varchar(255) DEFAULT NULL,
            plugin_name varchar(255) DEFAULT NULL,
            url varchar(255) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            severity varchar(20) DEFAULT 'notice',
            status varchar(20) DEFAULT 'new',
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY error_type (error_type),
            KEY plugin_slug (plugin_slug),
            KEY severity (severity),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $sql_conflicts = "CREATE TABLE IF NOT EXISTS {$this->table_conflicts} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            plugin_a varchar(255) NOT NULL,
            plugin_b varchar(255) NOT NULL,
            conflict_type varchar(50) NOT NULL,
            description text DEFAULT NULL,
            confidence_score int(11) DEFAULT 50,
            occurrences int(11) DEFAULT 1,
            last_seen datetime NOT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY plugin_a (plugin_a),
            KEY plugin_b (plugin_b),
            KEY conflict_type (conflict_type),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_errors);
        dbDelta($sql_conflicts);
    }
    
    /**
     * Log an error
     */
    public function log_error($data) {
        global $wpdb;
        
        $defaults = array(
            'error_type' => 'unknown',
            'error_message' => '',
            'error_file' => '',
            'error_line' => 0,
            'stack_trace' => '',
            'plugin_slug' => '',
            'plugin_name' => '',
            'url' => '',
            'user_agent' => '',
            'user_id' => get_current_user_id(),
            'severity' => 'notice',
            'status' => 'new',
            'created_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $wpdb->insert($this->table_errors, $data);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Log a conflict
     */
    public function log_conflict($plugin_a, $plugin_b, $conflict_type, $description = '', $confidence = 50) {
        global $wpdb;
        
        // Check if conflict already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_conflicts} 
            WHERE (plugin_a = %s AND plugin_b = %s) 
            OR (plugin_a = %s AND plugin_b = %s)
            AND status = 'active'",
            $plugin_a, $plugin_b, $plugin_b, $plugin_a
        ));
        
        if ($existing) {
            // Update existing conflict
            $wpdb->update(
                $this->table_conflicts,
                array(
                    'occurrences' => $existing->occurrences + 1,
                    'last_seen' => current_time('mysql'),
                    'confidence_score' => min(100, $existing->confidence_score + 5)
                ),
                array('id' => $existing->id)
            );
            return $existing->id;
        } else {
            // Insert new conflict
            $wpdb->insert($this->table_conflicts, array(
                'plugin_a' => $plugin_a,
                'plugin_b' => $plugin_b,
                'conflict_type' => $conflict_type,
                'description' => $description,
                'confidence_score' => $confidence,
                'occurrences' => 1,
                'last_seen' => current_time('mysql'),
                'status' => 'active',
                'created_at' => current_time('mysql')
            ));
            return $wpdb->insert_id;
        }
    }
    
    /**
     * Get recent errors
     */
    public function get_recent_errors($limit = 50, $offset = 0, $filters = array()) {
        global $wpdb;
        
        $where = array('1=1');
        
        if (!empty($filters['severity'])) {
            $where[] = $wpdb->prepare('severity = %s', $filters['severity']);
        }
        
        if (!empty($filters['plugin'])) {
            $where[] = $wpdb->prepare('plugin_slug = %s', $filters['plugin']);
        }
        
        if (!empty($filters['status'])) {
            $where[] = $wpdb->prepare('status = %s', $filters['status']);
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = $wpdb->prepare('created_at >= %s', $filters['date_from']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM {$this->table_errors} 
                WHERE {$where_clause} 
                ORDER BY created_at DESC 
                LIMIT %d OFFSET %d";
        
        return $wpdb->get_results($wpdb->prepare($sql, $limit, $offset));
    }
    
    /**
     * Get error count
     */
    public function get_error_count($filters = array()) {
        global $wpdb;
        
        $where = array('1=1');
        
        if (!empty($filters['severity'])) {
            $where[] = $wpdb->prepare('severity = %s', $filters['severity']);
        }
        
        if (!empty($filters['plugin'])) {
            $where[] = $wpdb->prepare('plugin_slug = %s', $filters['plugin']);
        }
        
        if (!empty($filters['status'])) {
            $where[] = $wpdb->prepare('status = %s', $filters['status']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_errors} WHERE {$where_clause}");
    }
    
    /**
     * Get active conflicts
     */
    public function get_conflicts($status = 'active') {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_conflicts} 
            WHERE status = %s 
            ORDER BY confidence_score DESC, occurrences DESC",
            $status
        ));
    }
    
    /**
     * Get error statistics
     */
    public function get_error_stats($days = 7) {
        global $wpdb;
        
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = array(
            'total' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_errors} WHERE created_at >= %s",
                $date_from
            )),
            'fatal' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_errors} WHERE severity = 'fatal' AND created_at >= %s",
                $date_from
            )),
            'warning' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_errors} WHERE severity = 'warning' AND created_at >= %s",
                $date_from
            )),
            'notice' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_errors} WHERE severity = 'notice' AND created_at >= %s",
                $date_from
            ))
        );
        
        return $stats;
    }
    
    /**
     * Get plugin error statistics
     */
    public function get_plugin_stats($days = 7) {
        global $wpdb;
        
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT plugin_slug, plugin_name, severity, COUNT(*) as count 
            FROM {$this->table_errors} 
            WHERE created_at >= %s AND plugin_slug IS NOT NULL AND plugin_slug != ''
            GROUP BY plugin_slug, severity 
            ORDER BY count DESC 
            LIMIT 20",
            $date_from
        ));
    }
    
    /**
     * Clean old logs
     */
    public function cleanup_old_logs($days = 30) {
        global $wpdb;
        
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_errors} WHERE created_at < %s",
            $date
        ));
        
        return $wpdb->rows_affected;
    }
    
    /**
     * Update error status
     */
    public function update_error_status($error_id, $status) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_errors,
            array('status' => $status),
            array('id' => $error_id)
        );
    }
    
    /**
     * Update conflict status
     */
    public function update_conflict_status($conflict_id, $status) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_conflicts,
            array('status' => $status),
            array('id' => $conflict_id)
        );
    }
    
    /**
     * Delete error
     */
    public function delete_error($error_id) {
        global $wpdb;
        
        return $wpdb->delete($this->table_errors, array('id' => $error_id));
    }
    
    /**
     * Delete all errors
     */
    public function delete_all_errors() {
        global $wpdb;
        
        return $wpdb->query("TRUNCATE TABLE {$this->table_errors}");
    }
}
