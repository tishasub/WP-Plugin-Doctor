<?php
/**
 * Uninstall WP Plugin Doctor
 * 
 * Removes all plugin data when the plugin is deleted
 * This file is only called when the plugin is uninstalled via WordPress admin
 * 
 * @package WP_Plugin_Doctor
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('wppd_settings');
delete_option('wppd_safe_mode_active');
delete_option('wppd_disabled_plugins');
delete_option('wppd_safe_mode_key');
delete_option('wppd_plugin_backups');

// Delete scheduled cron jobs
wp_clear_scheduled_hook('wppd_cleanup_old_logs');

// Drop custom database tables
global $wpdb;

$table_errors = $wpdb->prefix . 'wppd_errors';
$table_conflicts = $wpdb->prefix . 'wppd_conflicts';

$wpdb->query("DROP TABLE IF EXISTS {$table_errors}");
$wpdb->query("DROP TABLE IF EXISTS {$table_conflicts}");

// Clear any cached data
wp_cache_flush();
