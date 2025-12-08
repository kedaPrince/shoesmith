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
document.addEventListener('DOMContentLoaded', function() {
    // Handle chat button click with loading state (RECRUITER VERSION)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.chat-row')) {
            e.preventDefault();
            e.stopPropagation();

            var button = e.target.closest('.chat-row');
            var url = button.getAttribute('href');
            var candidateId = url.split('/').pop();

            // Store original button content
            var originalContent = button.innerHTML;
            var originalClass = button.className;

            // Show loading state
            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';
            button.className = originalClass + ' disabled';
            button.disabled = true;

            // Get chat info via AJAX (RECRUITER ENDPOINT)
            fetch(base_url + 'recruiter/candidates/ajax_get_candidate_chat_info/' + candidateId)
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        // Open chat in new tab
                        window.open(response.chat_url, '_blank');

                        // Show success message
                        showToast('success', 'Chat opened in new tab');
                    } else {
                        // Show error message
                        showToast('error', response.message || 'Failed to start chat');
                    }
                })
                .catch(error => {
                    console.error('Chat error:', error);
                    showToast('error', 'Network error. Please try again.');

                    // Fallback: Try direct link
                    setTimeout(function() {
                        window.open(url, '_blank');
                    }, 500);
                })
                .finally(() => {
                    // Restore button state after a short delay
                    setTimeout(function() {
                        button.innerHTML = originalContent;
                        button.className = originalClass;
                        button.disabled = false;
                    }, 1000);
                });
        }
    });

    // Quick chat button handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.quick-chat-btn')) {
            var button = e.target.closest('.quick-chat-btn');
            var candidateId = button.getAttribute('data-candidate-id');
            var candidateName = button.getAttribute('data-candidate-name');

            // Show loading
            var originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

            // Get chat info
            fetch(base_url + 'recruiter/candidates/ajax_get_candidate_chat_info/' + candidateId)
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        window.open(response.chat_url, '_blank');
                        showToast('success', 'Opening chat about ' + candidateName);
                    } else {
                        showToast('error', response.message);
                    }
                })
                .catch(() => {
                    showToast('error', 'Failed to start chat');
                })
                .finally(() => {
                    button.innerHTML = originalHtml;
                });
        }
    });
});
/**
 * Show toast notification (Vanilla JS version)
 */
function showToast(type, message) {
    // Check if toast container exists
    var toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
        document.body.appendChild(toastContainer);
    }

    var bgColor = type === 'success' ? '#28a745' : '#dc3545';
    var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

    var toastId = 'toast-' + Date.now();
    var toastHtml = '<div id="' + toastId + '" style="background: ' + bgColor +
        '; color: white; padding: 12px 20px; margin-bottom: 10px; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); min-width: 250px; display: flex; align-items: center;">' +
        '<i class="fa ' + icon + '" style="margin-right: 10px;"></i>' +
        '<span>' + message + '</span>' +
        '<button style="margin-left: auto; background: none; border: none; color: white; cursor: pointer;">' +
        '<i class="fa fa-times"></i>' +
        '</button>' +
        '</div>';

    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = toastHtml;
    var toastElement = tempDiv.firstChild;

    // Add click handler for close button
    var closeBtn = toastElement.querySelector('button');
    closeBtn.addEventListener('click', function() {
        toastElement.style.opacity = '0';
        toastElement.style.transition = 'opacity 0.3s';
        setTimeout(function() {
            if (toastElement.parentNode) {
                toastElement.parentNode.removeChild(toastElement);
            }
        }, 300);
    });

    toastContainer.appendChild(toastElement);

    // Auto remove after 5 seconds
    setTimeout(function() {
        if (toastElement.parentNode) {
            toastElement.style.opacity = '0';
            toastElement.style.transition = 'opacity 0.3s';
            setTimeout(function() {
                if (toastElement.parentNode) {
                    toastElement.parentNode.removeChild(toastElement);
                }
            }, 300);
        }
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