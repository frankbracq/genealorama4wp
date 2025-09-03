/**
 * Admin settings JavaScript for Genealorama plugin
 */
jQuery(document).ready(function($) {
    // Toggle secret visibility
    $('#toggle-secret').on('click', function() {
        const input = $('#genealorama_partner_secret_display');
        const icon = $(this).find('span.dashicons');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass(genealoramaAdmin.eyeIcon).addClass(genealoramaAdmin.eyeSlashIcon);
        } else {
            input.attr('type', 'password');
            icon.removeClass(genealoramaAdmin.eyeSlashIcon).addClass(genealoramaAdmin.eyeIcon);
        }
    });
    
    // Connect to Genealorama
    $('#genealorama-connect-btn').on('click', function() {
        const email = $('#genealorama_email').val();
        if (!email) {
            showMessage('connection-message', 'error', 'Please enter your email.');
            return;
        }
        
        const $btn = $(this);
        const $spinner = $('#connection-spinner');
        
        $btn.prop('disabled', true);
        $spinner.show();
        
        $.ajax({
            url: genealoramaAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'genealorama_get_credentials',
                email: email,
                nonce: genealoramaAdmin.nonce
            },
            success: function(response) {
                $btn.prop('disabled', false);
                $spinner.hide();
                
                if (response.success) {
                    $('#genealorama_partner_id').val(response.data.partner_id);
                    $('#genealorama_partner_secret_display').val(response.data.partner_secret);
                    showMessage('connection-message', 'success', 'Connected successfully! Your credentials have been validated.');
                    
                    // Show options section after successful connection
                    $('.genealorama-options-section').fadeIn();
                    
                    // Update status
                    updateConnectionStatus(true);
                } else {
                    showMessage('connection-message', 'error', response.data.message || 'Connection failed.');
                    updateConnectionStatus(false);
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                $spinner.hide();
                showMessage('connection-message', 'error', 'Connection failed. Please try again.');
                updateConnectionStatus(false);
            }
        });
    });
    
    // Save display options
    $('.genealorama-display-option').on('change', function() {
        const $option = $(this);
        const optionName = $option.attr('name');
        const optionValue = $option.is(':checked') ? '1' : '0';
        
        // Add visual feedback
        const $container = $option.closest('.switch-container, .form-group');
        $container.addClass('saving');
        
        $.ajax({
            url: genealoramaAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'genealorama_save_display_option',
                option_name: optionName,
                option_value: optionValue,
                nonce: genealoramaAdmin.nonce
            },
            success: function(response) {
                $container.removeClass('saving');
                if (response.success) {
                    $container.addClass('saved');
                    setTimeout(() => $container.removeClass('saved'), 2000);
                }
            },
            error: function() {
                $container.removeClass('saving');
                // Revert the change on error
                $option.prop('checked', !$option.prop('checked'));
            }
        });
    });
    
    // Helper functions
    function showMessage(containerId, type, message) {
        const $container = $('#' + containerId);
        $container.removeClass('notice-success notice-error notice-warning')
                 .addClass('notice notice-' + type)
                 .html('<p>' + message + '</p>')
                 .show();
        
        // Hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => $container.fadeOut(), 5000);
        }
    }
    
    function updateConnectionStatus(connected) {
        const $status = $('.connection-status');
        const $badge = $status.find('.status-badge');
        
        if (connected) {
            $badge.removeClass('disconnected')
                  .addClass('connected')
                  .text('Connected');
            $status.find('.status-text').text('Successfully connected to Genealorama');
        } else {
            $badge.removeClass('connected')
                  .addClass('disconnected')
                  .text('Disconnected');
            $status.find('.status-text').text('Not connected to Genealorama');
        }
    }
    
    // Initialize connection status on page load
    const hasCredentials = $('#genealorama_partner_id').val() && $('#genealorama_partner_secret_display').val();
    updateConnectionStatus(hasCredentials);
    
    // Show options section if already connected
    if (hasCredentials) {
        $('.genealorama-options-section').show();
    }
});