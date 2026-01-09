<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

.ecms-field .c_dropdown ul.multiselect-container li a label.radio input {
    position: fixed;
    left: 2px;
    display: none !important;
}
</style>

<script>
// Simple tooltip override
function setupTooltips() {
    document.querySelectorAll('.general-actions .btn').forEach(btn => {
        const href = btn.href || '';
        if (href.includes('candidates_list/view')) {
            btn.title = 'View detailed';
        } else if (href.includes('/edit/')) {
            btn.title = 'Edit';
        } else if (href.includes('/onboarding/')) {
            btn.title = 'onboarding process';
        }
    });
}

// Run immediately and frequently
setupTooltips();
setInterval(setupTooltips, 500);
document.addEventListener('click', setupTooltips);
window.addEventListener('scroll', setupTooltips);

$(document).ready(function() {
    // Handle chat button click - SIMPLE VERSION
    $(document).on('click', '.chat-row', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var url = $(this).attr('href');
        var $button = $(this);
        var originalContent = $button.html();

        // Show loading briefly
        $button.html('<i class="fa fa-spinner fa-spin"></i> Opening...');
        $button.prop('disabled', true);

        // Open link directly (no AJAX)
        window.open(url, '_blank');

        // Restore button after 1 second
        setTimeout(function() {
            $button.html(originalContent);
            $button.prop('disabled', false);
        }, 1000);
    });

    // Quick chat button handler
    $(document).on('click', '.quick-chat-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var url = $(this).attr('href');
        var $button = $(this);
        var originalContent = $button.html();

        // Show loading briefly
        $button.html('<i class="fa fa-spinner fa-spin"></i>');
        $button.prop('disabled', true);

        // Open link directly
        window.open(url, '_blank');

        // Restore button after 1 second
        setTimeout(function() {
            $button.html(originalContent);
            $button.prop('disabled', false);
        }, 1000);
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
 * Alternative: Simple alert function if toast doesn't work
 */
function showAlert(message, type = 'info') {
    alert(message); // Fallback to simple alert
}
</script>