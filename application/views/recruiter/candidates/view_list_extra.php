<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
/* Fix font size */
.ecms-listing * {
    font-size: 14px !important;
}

/* Instant tooltips - POSITIONED ON TOP */
.general-actions .btn {
    position: relative;
}

.general-actions .btn:hover::after {
    content: attr(title);
    position: absolute;
    top: -35px;
    left: 50%;
    transform: translateX(-50%);
    background: #000;
    color: #fff;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 9999;
    opacity: 1;
    transition: none;
}

/* Optional: Add a small arrow pointing down to the button */
.general-actions .btn:hover::before {
    content: '';
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    border: 4px solid transparent;
    border-top-color: #000;
    z-index: 10000;
}
</style>

<script>
$(document).ready(function() {
    // Handle chat button click with loading state (RECRUITER VERSION)
    $(document).on('click', '.chat-row', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var url = $(this).attr('href');
        var candidateId = url.split('/').pop();
        var $button = $(this);

        // Store original button content
        var originalContent = $button.html();
        var originalClass = $button.attr('class');

        // Show loading state
        $button.html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        $button.attr('class', originalClass + ' disabled');
        $button.prop('disabled', true);

        // Get chat info via AJAX (RECRUITER ENDPOINT)
        $.ajax({
            url: base_url + 'recruiter/candidates/ajax_get_candidate_chat_info/' + candidateId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Open chat in new tab
                    window.open(response.chat_url, '_blank');

                    // Show success message
                    showToast('success', 'Chat opened in new tab');
                } else {
                    // Show error message
                    showToast('error', response.message || 'Failed to start chat');
                }
            },
            error: function(xhr, status, error) {
                console.error('Chat error:', error);
                showToast('error', 'Network error. Please try again.');

                // Fallback: Try direct link
                setTimeout(function() {
                    window.open(url, '_blank');
                }, 500);
            },
            complete: function() {
                // Restore button state after a short delay
                setTimeout(function() {
                    $button.html(originalContent);
                    $button.attr('class', originalClass);
                    $button.prop('disabled', false);
                }, 1000);
            }
        });
    });

    // Quick chat button handler
    $(document).on('click', '.quick-chat-btn', function() {
        var candidateId = $(this).data('candidate-id');
        var candidateName = $(this).data('candidate-name');

        // Show loading
        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.html('<i class="fa fa-spinner fa-spin"></i>');

        // Get chat info
        $.ajax({
            url: base_url + 'recruiter/candidates/ajax_get_candidate_chat_info/' + candidateId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.open(response.chat_url, '_blank');
                    showToast('success', 'Opening chat about ' + candidateName);
                } else {
                    showToast('error', response.message);
                }
            },
            error: function() {
                showToast('error', 'Failed to start chat');
            },
            complete: function() {
                $btn.html(originalHtml);
            }
        });
    });
});

/**
 * Show toast notification
 */
function showToast(type, message) {
    // Check if toast container exists
    if ($('#toast-container').length === 0) {
        $('body').append(
            '<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>');
    }

    var bgColor = type === 'success' ? '#28a745' : '#dc3545';
    var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

    var toastId = 'toast-' + Date.now();
    var toastHtml = '<div id="' + toastId + '" style="background: ' + bgColor +
        '; color: white; padding: 12px 20px; margin-bottom: 10px; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); min-width: 250px; display: flex; align-items: center;">' +
        '<i class="fa ' + icon + '" style="margin-right: 10px;"></i>' +
        '<span>' + message + '</span>' +
        '<button onclick="$(\'#' + toastId +
        '\').remove()" style="margin-left: auto; background: none; border: none; color: white; cursor: pointer;">' +
        '<i class="fa fa-times"></i>' +
        '</button>' +
        '</div>';

    $('#toast-container').append(toastHtml);

    // Auto remove after 5 seconds
    setTimeout(function() {
        $('#' + toastId).fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Simple tooltip override
 */
function setupTooltips() {
    document.querySelectorAll('.general-actions .btn').forEach(btn => {
        const href = btn.href || '';
        if (href.includes('/view/')) {
            btn.title = 'View detailed';
        } else if (href.includes('/edit/')) {
            btn.title = 'Edit';
        } else if (href.includes('/chat/')) {
            btn.title = 'Chat with agency';
        }
    });
}

// Run immediately and frequently
setupTooltips();
setInterval(setupTooltips, 500);
document.addEventListener('click', setupTooltips);
window.addEventListener('scroll', setupTooltips);
</script>