/**
 * WP Plugin Doctor - Admin JavaScript
 */

(function($) {
    'use strict';
    
    // Initialize on document ready
    $(document).ready(function() {
        WPPD.init();
    });
    
    // Main plugin object
    var WPPD = {
        
        /**
         * Initialize all functionality
         */
        init: function() {
            this.errorActions();
            this.conflictActions();
            this.safeModeActions();
            this.modal();
            this.adminBar();
        },
        
        /**
         * Error-related actions
         */
        errorActions: function() {
            var self = this;
            
            // View error details
            $(document).on('click', '.wppd-view-error', function(e) {
                e.preventDefault();
                var errorId = $(this).data('error-id');
                self.showErrorDetails(errorId);
            });
            
            // Delete error
            $(document).on('click', '.wppd-delete-error', function(e) {
                e.preventDefault();
                
                if (!confirm(wppdAdmin.strings.confirm_delete)) {
                    return;
                }
                
                var errorId = $(this).data('error-id');
                var row = $(this).closest('tr');
                
                $.ajax({
                    url: wppdAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wppd_delete_error',
                        nonce: wppdAdmin.nonce,
                        error_id: errorId
                    },
                    success: function(response) {
                        if (response.success) {
                            row.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                    }
                });
            });
            
            // Clear all errors
            $(document).on('click', '.wppd-clear-all-errors', function(e) {
                e.preventDefault();
                
                if (!confirm(wppdAdmin.strings.confirm_clear_all)) {
                    return;
                }
                
                $.ajax({
                    url: wppdAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wppd_clear_all_errors',
                        nonce: wppdAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
            });
            
            // Update error status
            $(document).on('change', '.wppd-error-status', function() {
                var errorId = $(this).data('error-id');
                var status = $(this).val();
                
                $.ajax({
                    url: wppdAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wppd_update_error_status',
                        nonce: wppdAdmin.nonce,
                        error_id: errorId,
                        status: status
                    }
                });
            });
        },
        
        /**
         * Show error details in modal
         */
        showErrorDetails: function(errorId) {
            var modal = $('#wppd-error-modal');
            var detailsDiv = $('#wppd-error-details');
            
            detailsDiv.html('<div class="wppd-loading">Loading error details...</div>');
            modal.show();
            
            $.ajax({
                url: wppdAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wppd_get_error_details',
                    nonce: wppdAdmin.nonce,
                    error_id: errorId
                },
                success: function(response) {
                    if (response.success) {
                        var error = response.data;
                        var html = '';
                        
                        // Severity
                        html += '<div class="wppd-error-detail-row">';
                        html += '<strong>Severity:</strong> ';
                        html += '<span class="wppd-severity-badge wppd-severity-' + error.severity + '">';
                        html += error.severity.toUpperCase();
                        html += '</span>';
                        html += '</div>';
                        
                        // Error Type
                        html += '<div class="wppd-error-detail-row">';
                        html += '<strong>Error Type:</strong> ' + error.error_type;
                        html += '</div>';
                        
                        // Message
                        html += '<div class="wppd-error-detail-row">';
                        html += '<strong>Message:</strong><br>';
                        html += error.error_message;
                        html += '</div>';
                        
                        // File & Line
                        if (error.error_file) {
                            html += '<div class="wppd-error-detail-row">';
                            html += '<strong>File:</strong><br>';
                            html += error.error_file;
                            if (error.error_line) {
                                html += ' (Line: ' + error.error_line + ')';
                            }
                            html += '</div>';
                        }
                        
                        // Plugin
                        if (error.plugin_name) {
                            html += '<div class="wppd-error-detail-row">';
                            html += '<strong>Plugin:</strong> ' + error.plugin_name;
                            html += '</div>';
                        }
                        
                        // URL
                        if (error.url) {
                            html += '<div class="wppd-error-detail-row">';
                            html += '<strong>URL:</strong><br>';
                            html += '<a href="' + error.url + '" target="_blank">' + error.url + '</a>';
                            html += '</div>';
                        }
                        
                        // Time
                        html += '<div class="wppd-error-detail-row">';
                        html += '<strong>Time:</strong> ' + error.created_at;
                        html += '</div>';
                        
                        // Stack Trace
                        if (error.stack_trace) {
                            html += '<div class="wppd-error-detail-row">';
                            html += '<strong>Stack Trace:</strong>';
                            html += '<pre>' + error.stack_trace + '</pre>';
                            html += '</div>';
                        }
                        
                        detailsDiv.html(html);
                    } else {
                        detailsDiv.html('<p>Error details could not be loaded.</p>');
                    }
                }
            });
        },
        
        /**
         * Conflict-related actions
         */
        conflictActions: function() {
            // Scan for conflicts
            $(document).on('click', '.wppd-scan-conflicts', function(e) {
                e.preventDefault();
                var btn = $(this);
                var originalText = btn.text();
                
                btn.prop('disabled', true).text('Scanning...');
                
                $.ajax({
                    url: wppdAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wppd_scan_conflicts',
                        nonce: wppdAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        }
                    },
                    complete: function() {
                        btn.prop('disabled', false).text(originalText);
                    }
                });
            });
            
            // Resolve conflict
            $(document).on('click', '.wppd-resolve-conflict', function(e) {
                e.preventDefault();
                var conflictId = $(this).data('conflict-id');
                var card = $(this).closest('.wppd-conflict-card');
                
                $.ajax({
                    url: wppdAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wppd_resolve_conflict',
                        nonce: wppdAdmin.nonce,
                        conflict_id: conflictId
                    },
                    success: function(response) {
                        if (response.success) {
                            card.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                    }
                });
            });
            
            // Disable plugin from conflict
            $(document).on('click', '.wppd-disable-plugin', function(e) {
                e.preventDefault();
                var plugin = $(this).data('plugin');
                
                if (!confirm('Are you sure you want to disable this plugin?')) {
                    return;
                }
                
                $.ajax({
                    url: wppdAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wppd_disable_plugin',
                        nonce: wppdAdmin.nonce,
                        plugin: plugin
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Plugin has been disabled. Page will reload.');
                            location.reload();
                        }
                    }
                });
            });
        },
        
        /**
         * Safe mode actions
         */
        safeModeActions: function() {
            // Enable safe mode
            $(document).on('click', '.wppd-enable-safe-mode', function(e) {
                e.preventDefault();
                
                if (!confirm(wppdAdmin.strings.confirm_safe_mode)) {
                    return;
                }
                
                var btn = $(this);
                btn.prop('disabled', true).text('Enabling Safe Mode...');
                
                $.ajax({
                    url: wppdAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wppd_enable_safe_mode',
                        nonce: wppdAdmin.safemode_nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Safe mode enabled. Page will reload.');
                            location.reload();
                        }
                    }
                });
            });
            
            // Exit safe mode
            $(document).on('click', '.wppd-exit-safe-mode, .wppd-exit-safe-mode-link', function(e) {
                e.preventDefault();
                
                if (!confirm('Are you sure you want to exit safe mode and restore all plugins?')) {
                    return;
                }
                
                $.ajax({
                    url: wppdAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wppd_disable_safe_mode',
                        nonce: wppdAdmin.safemode_nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Safe mode disabled. Page will reload.');
                            location.reload();
                        }
                    }
                });
            });
        },
        
        /**
         * Modal functionality
         */
        modal: function() {
            // Close modal on X click
            $(document).on('click', '.wppd-modal-close', function() {
                $(this).closest('.wppd-modal').hide();
            });
            
            // Close modal on outside click
            $(document).on('click', '.wppd-modal', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });
            
            // Close modal on escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $('.wppd-modal').hide();
                }
            });
        },
        
        /**
         * Admin bar functionality
         */
        adminBar: function() {
            // Add styles to admin bar
            if ($('#wp-admin-bar-wp-plugin-doctor .wppd-admin-bar-count').length) {
                $('#wp-admin-bar-wp-plugin-doctor').css('background', 'rgba(217, 83, 79, 0.2)');
            }
        }
    };
    
})(jQuery);
