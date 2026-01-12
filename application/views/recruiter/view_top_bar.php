<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
// Load notifications directly in the top bar
$ci =& get_instance();
$ci->load->model('recruiter/Model_notifications');
$ci->load->model('recruiter/Model_chat_messages');

// Get recruiter ID from session
$login_data = $ci->session->userdata('login');
$recruiter_id = !empty($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : null;

// Load notifications if recruiter is logged in
if ($recruiter_id) {
    // Get ONLY SYSTEM notifications (exclude chat notifications)
    $system_notifications = $ci->Model_notifications->get_system_notifications($recruiter_id);
    $system_unread_count = $ci->Model_notifications->count_system_notifications($recruiter_id);
    
    // Get chat-specific unread count
    $chat_unread_count = $ci->Model_chat_messages->get_unread_count_for_recruiter($recruiter_id);
} else {
    $system_notifications = [];
    $system_unread_count = 0;
    $chat_unread_count = 0;
}
?>

<style>
#navbar-menu li {
    float: inherit;
}

/* Notification Bell Styling */
a#notification-bell {
    padding: 8px 14px;
}

.notifications-menu {
    position: relative;

    .dropdown-toggle {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        background-color: var(--primary-color);
        border-radius: 25px;
        color: var(--font-white);
        transition: all 0.3s ease;
        margin: 0 5px;

        .fa-bell-o {
            font-size: 18px;
            color: var(--font-white);
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -2px;
            background-color: #ff4444;
            color: white;
            border-radius: 50%;
            min-width: 20px;
            height: 20px;
            font-size: 11px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            line-height: 1;
        }

        &:hover {
            background-color: var(--primary-color2);
            transform: translateY(-1px);
        }
    }

    /* Pulse animation when there are notifications */
    <?php if ($system_unread_count > 0): ?>.notifications-menu .dropdown-toggle {
        animation: pulse 2s infinite;
    }


    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 68, 68, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 68, 68, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 68, 68, 0);
        }
    }

    <?php endif;
    ?>
}

/* Ensure other navbar icons have consistent styling */
.navbar-nav>li>a.icon-menu {
    padding: 8px 45px;
    margin: 0 5px;
    border-radius: 25px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
        background-color: var(--primary-color2);
        transform: translateY(-1px);
    }
}

/* Dark mode support */
body[data-theme="dark"] .notifications-menu .dropdown-toggle {
    background-color: var(--primary-color);

    &:hover {
        background-color: var(--primary-color2);
    }

    .notification-badge {
        border-color: none;
    }
}

.navbar-nav .dropdown-menu {
    background: #ffffff;
}

/* Chat Notification Bell Styling */
.chat-notifications-menu {
    position: relative;
}

.chat-notifications-menu .dropdown-toggle {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    background-color: #128C7E;
    border-radius: 25px;
    color: white;
    transition: all 0.3s ease;
    margin: 0 5px;
}

.chat-notifications-menu .dropdown-toggle .fa-comments {
    font-size: 18px;
    color: white;
}

.chat-notification-badge {
    position: absolute;
    top: -8px;
    right: -2px;
    background-color: #25D366;
    color: white;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    font-size: 11px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    line-height: 1;
}

.chat-notifications-menu .dropdown-toggle:hover {
    background-color: #0d745e;
    transform: translateY(-1px);
}

/* Pulse animation for chat notifications */
<?php if ($chat_unread_count > 0): ?>.chat-notifications-menu .dropdown-toggle {
    animation: chat-pulse 2s infinite;
}


@keyframes chat-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
    }

    70% {
        box-shadow: 0 0 0 10px rgba(37, 211, 102, 0);
    }

    100% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
    }
}

<?php endif;
?>
</style>

<script>
// Use window variables instead of declaring new const variables
// const csrfName = '<?php echo $ci->security->get_csrf_token_name(); ?>'; // REMOVE THIS
// const csrf = '<?php echo $ci->security->get_csrf_hash(); ?>'; // REMOVE THIS

// If window variables are not set, set them
if (typeof window.csrf_token_name === 'undefined') {
    window.csrf_token_name = '<?php echo $ci->security->get_csrf_token_name(); ?>';
}
if (typeof window.csrf_token_value === 'undefined') {
    window.csrf_token_value = '<?php echo $ci->security->get_csrf_hash(); ?>';
}
</script>

<!-- Top navbar div start -->
<nav class="navbar navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-brand">
            <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-bars"></i></button>
            <button type="button" class="btn-toggle-fullwidth"><i class="fa fa-bars"></i></button>
            <a><?= $this->config->item('site_name'); ?></a>
        </div>

        <div class="navbar-right">
            <div id="navbar-menu">
                <ul class="nav navbar-nav">
                    <li class="dropdown notifications-menu">
                        <a href="javascript:void(0);" class="dropdown-toggle icon-menu" data-toggle="dropdown"
                            aria-expanded="false" id="notification-bell">
                            <i class="fa fa-bell-o"></i>
                            <?php if ($system_unread_count > 0): ?>
                            <span class="notification-badge"><?php echo $system_unread_count; ?></span>
                            <?php else: ?>
                            <span class="notification-badge" style="display: none;">0</span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu"
                            style="width: 350px; padding: 0; border-bottom-left-radius: 23px; border-bottom-right-radius: 23px;">
                            <li class="header"
                                style="background: #f8f9fa; padding: 10px 15px; border-bottom: 1px solid #dee2e6;color:#000;">
                                You have <span class="notification-count"><?php echo $system_unread_count; ?></span>
                                system notifications
                            </li>
                            <li>
                                <a class="dark-mode-toggle icon-menu" href="javascript:toggle_dark_mode()"
                                    title="Toggle Dark Mode" data-toggle="tt" data-placement="top">
                                    <i class="dark-mode-disabled fa fa-moon-o"></i>
                                    <i class="dark-mode-enabled fa fa-sun-o"></i>
                                </a>
                            </li>
                            <li style="max-height: 250px; overflow-y: auto;">
                                <ul class="menu" style="list-style: none; padding: 0; margin: 0;">
                                    <?php if (!empty($system_notifications)): ?>
                                    <?php foreach (array_slice($system_notifications, 0, 5) as $notification): ?>
                                    <li style="border-bottom: 1px solid #f0f0f0;">
                                        <a href="javascript:void(0);"
                                            onclick="handleNotificationClick(<?php echo $notification->id; ?>)"
                                            style="display: block; padding: 10px 15px; color: #333; text-decoration: none; cursor: pointer;"
                                            class="notification-item">
                                            <div style="float: left; margin-right: 10px;">
                                                <i class="fa fa-briefcase" style="color: #007bff;"></i>
                                            </div>
                                            <div style="overflow: hidden;">
                                                <h4 style="margin: 0 0 5px 0; font-size: 14px; font-weight: bold;">
                                                    <?php echo $notification->title; ?>
                                                    <?php if (!$notification->is_read): ?>
                                                    <span
                                                        style="background: #ff4444; color: white; padding: 1px 6px; border-radius: 10px; font-size: 10px; margin-left: 5px; display: inline-block;">NEW</span>
                                                    <?php endif; ?>
                                                </h4>
                                                <p style="margin: 0 0 5px 0; font-size: 12px; color: #666;">
                                                    <?php echo character_limiter($notification->message, 50); ?>
                                                </p>
                                                <small style="color: #999; font-size: 11px;">
                                                    <i class="fa fa-clock-o"></i>
                                                    <?php echo time_ago($notification->created_at); ?>
                                                </small>
                                            </div>
                                            <div style="clear: both;"></div>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <li style="padding: 15px; text-align: center; color: #666;">
                                        No new system notifications
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                            <li class="footer"
                                style="background: #48b34d;padding: 10px 15px;text-align: center;color: #fff;">
                                <a href="<?php echo site_url('recruiter/dashboard/notifications'); ?>"
                                    style="color: #ffffff;text-decoration: none;font-weight: bold;">
                                    View All Notifications
                                </a>
                            </li>
                        </ul>
                    </li>


                    <!-- Chat Notification Bell -->
                    <li class="dropdown chat-notifications-menu">
                        <a href="javascript:void(0);" class="dropdown-toggle icon-menu" data-toggle="dropdown"
                            aria-expanded="false" id="chat-notification-bell">
                            <i class="fa fa-comments"></i>
                            <?php 
                                // Calculate chat-specific unread count
                                $chat_unread_count = 0;
                                if ($recruiter_id) {
                                    $ci->load->model('recruiter/Model_chat_messages');
                                    $chat_unread_count = $ci->Model_chat_messages->get_unread_count_for_recruiter($recruiter_id);
                                }
                                ?>
                            <?php if ($chat_unread_count > 0): ?>
                            <span class="chat-notification-badge"><?php echo $chat_unread_count; ?></span>
                            <?php else: ?>
                            <span class="chat-notification-badge" style="display: none;">0</span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu"
                            style="width: 350px; padding: 0; border-bottom-left-radius: 23px; border-bottom-right-radius: 23px;">
                            <li class="header"
                                style="background: #128C7E; padding: 10px 15px; border-bottom: 1px solid #0d745e;color:#fff;">
                                <i class="fa fa-comments"></i> You have <span
                                    class="chat-notification-count"><?php echo $chat_unread_count; ?></span>
                                unread messages
                            </li>
                            <li style="max-height: 250px; overflow-y: auto;">
                                <ul class="menu" style="list-style: none; padding: 0; margin: 0;">
                                    <?php 
                                    // Get recent chat conversations with unread messages
                                    $recent_chats = [];
                                    if ($recruiter_id) {
                                        $recent_chats = $ci->Model_chat_messages->get_recruiter_conversations($recruiter_id, 5);
                                    }
                                    ?>
                                    <?php if (!empty($recent_chats)): ?>
                                    <?php foreach ($recent_chats as $chat): ?>
                                    <?php if ($chat->unread_count > 0): ?>
                                    <li style="border-bottom: 1px solid #f0f0f0;">
                                        <a href="<?php echo site_url('recruiter/chat/conversation/' . $chat->uuid ?? $chat->id); ?>"
                                            style="display: block; padding: 10px 15px; color: #333; text-decoration: none; cursor: pointer;"
                                            class="chat-notification-item">
                                            <div style="float: left; margin-right: 10px;">
                                                <div
                                                    style="width: 40px; height: 40px; border-radius: 50%; background-color: #128C7E; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #ffffff;">
                                                    <?php echo substr(htmlspecialchars($chat->agency_name), 0, 1); ?>
                                                </div>
                                            </div>
                                            <div style="overflow: hidden;">
                                                <h4 style="margin: 0 0 5px 0; font-size: 14px; font-weight: bold;">
                                                    <?php echo htmlspecialchars($chat->agency_name); ?>
                                                    <span
                                                        style="background: #128C7E; color: white; padding: 1px 6px; border-radius: 10px; font-size: 10px; margin-left: 5px; display: inline-block;">
                                                        <?php echo $chat->unread_count ?? 0; ?> new
                                                    </span>
                                                </h4>
                                                <p style="margin: 0 0 5px 0; font-size: 12px; color: #666;">
                                                    <?php echo character_limiter($chat->last_message ?? 'No messages', 50); ?>
                                                </p>
                                                <small style="color: #999; font-size: 11px;">
                                                    <i class="fa fa-clock-o"></i>
                                                    <?php echo time_ago($chat->last_message_at ?? $chat->created_at); ?>
                                                </small>
                                            </div>
                                            <div style="clear: both;"></div>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <li style="padding: 15px; text-align: center; color: #666;">
                                        <i class="fa fa-comments"
                                            style="font-size: 24px; color: #128C7E; margin-bottom: 10px;"></i>
                                        <p style="margin: 0;">No unread messages</p>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                            <li class="footer"
                                style="background: #25D366;padding: 10px 15px;text-align: center;color: #fff;">
                                <a href="<?php echo site_url('recruiter/chat'); ?>"
                                    style="color: #ffffff;text-decoration: none;font-weight: bold;">
                                    <i class="fa fa-comments"></i> Open Chat
                                </a>
                            </li>

                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="icon-menu" id="top-bar-logout-btn" data-toggle="tt"
                            data-placement="top" title="Logout" onclick="confirmLogout()">
                            <i class="fa fa-sign-out"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</nav>

<script>
function confirmLogout() {
    // Use the same pattern as disable_button()
    Swal.fire({
        title: 'Confirm Logout',
        text: 'Are you sure you want to logout from the system?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Logout',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'swal2-confirm swal2-styled',
            cancelButton: 'swal2-cancel swal2-styled'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?php echo site_url("login/logout/recruiter"); ?>';
        }
    });
}

function markAsRead(notificationId) {
    const csrfName = '<?php echo $ci->security->get_csrf_token_name(); ?>';
    const csrf = '<?php echo $ci->security->get_csrf_hash(); ?>';

    $.post('<?php echo site_url("recruiter/dashboard/ajax_mark_notification_read"); ?>', {
        notification_id: notificationId,
        [csrfName]: csrf
    }, function(response) {
        if (response.success) {
            const notification = document.querySelector('[data-notification-id="' + notificationId + '"]');
            if (notification) {
                notification.classList.remove('unread');
                const markReadBtn = notification.querySelector('.btn-mark-read');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
            }
            updateNotificationBadge(response.unread_count);

            // Don't show toast when clicking notification items
            if (!window.suppressNotificationToast) {
                showToast('Notification marked as read', 'success');
            }
        }
    }, 'json').fail(function(xhr, status, error) {
        if (!window.suppressNotificationToast) {
            showToast('Error marking notification as read', 'error');
        }
    });
}

function handleNotificationClick(notificationId) {
    // Close the dropdown first
    $('.notifications-menu .dropdown-toggle').dropdown('toggle');

    // Immediately update the UI optimistically
    const badge = document.querySelector('.notification-badge');
    const count = document.querySelector('.notification-count');

    // Update badge count optimistically
    if (badge && badge.textContent > 0) {
        const newCount = parseInt(badge.textContent) - 1;
        badge.textContent = newCount;
        if (count) count.textContent = newCount;

        // Hide badge if count is 0
        if (newCount === 0) {
            badge.style.display = 'none';
        }
    }

    // Remove the "NEW" badge from this notification in the dropdown immediately
    const notificationElement = document.querySelector('[onclick="handleNotificationClick(' + notificationId + ')"]');
    if (notificationElement) {
        const newBadge = notificationElement.querySelector('span[style*="background: #ff4444"]');
        if (newBadge) {
            newBadge.remove();
        }

        // Also remove the entire notification item after a delay
        setTimeout(() => {
            notificationElement.closest('li').remove();
        }, 100);
    }

    // Mark as read via AJAX
    fetch('<?php echo site_url("recruiter/dashboard/ajax_mark_notification_read"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'notification_id=' + notificationId +
                '&<?php echo $ci->security->get_csrf_token_name(); ?>=<?php echo $ci->security->get_csrf_hash(); ?>'
        }).then(response => response.json())
        .then(response => {
            if (response.success) {
                // Update badge with actual count from server
                if (badge) {
                    badge.textContent = response.unread_count;
                    if (count) count.textContent = response.unread_count;
                    badge.style.display = response.unread_count == 0 ? 'none' : 'flex';
                }

                // Redirect to notifications page with the specific notification highlighted
                window.location.href = '<?php echo site_url("recruiter/dashboard/notifications"); ?>?read=' +
                    notificationId;
            }
        }).catch(error => {
            // Still redirect even if there's an error
            window.location.href = '<?php echo site_url("recruiter/dashboard/notifications"); ?>';
        });
}

// ===== REAL-TIME NOTIFICATION POLLING =====
function startRecruiterNotificationPolling() {
    // Poll for new notifications every 3 seconds
    setInterval(fetchRecruiterNotifications, 3000);

    // Initial fetch
    setTimeout(fetchRecruiterNotifications, 1000);
}

function fetchRecruiterNotifications() {
    console.log('Fetching system notifications...');

    // Use the global CSRF variables
    const csrfName = '<?php echo $ci->security->get_csrf_token_name(); ?>';
    const csrf = '<?php echo $ci->security->get_csrf_hash(); ?>';

    const params = {};
    params[csrfName] = csrf;

    console.log('Sending CSRF token:', csrfName + ' = ' + csrf);

    fetch('<?php echo site_url("recruiter/notifications/ajax_get_notifications"); ?>', {
            method: 'GET', // Change to GET to avoid CSRF issues for read-only endpoints
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('System notifications response:', data);
            if (data.success) {
                updateRecruiterNotificationUI(data);
            }
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
        });
}

function updateRecruiterNotificationUI(data) {
    const badge = document.querySelector('.notification-badge');
    const countElement = document.querySelector('.notification-count');
    const notificationBell = document.querySelector('.notifications-menu .dropdown-toggle');

    if (data.system_unread_count > 0) {
        // Update badge
        if (badge) {
            badge.textContent = data.system_unread_count > 99 ? '99+' : data.system_unread_count;
            badge.style.display = 'flex';
        }

        // Update count text
        if (countElement) {
            countElement.textContent = data.system_unread_count;
        }

        // Add pulse animation
        if (notificationBell) {
            notificationBell.style.animation = 'pulse 2s infinite';
        }
    } else {
        // Hide badge if no system notifications
        if (badge) {
            badge.style.display = 'none';
        }
        if (countElement) {
            countElement.textContent = '0';
        }

        // Remove pulse animation
        if (notificationBell) {
            notificationBell.style.animation = 'none';
        }
    }
}

// ===== CHAT NOTIFICATION POLLING =====
function startChatNotificationPolling() {
    // Poll for new chat messages every 3 seconds
    setInterval(fetchChatNotifications, 3000);

    // Initial fetch
    setTimeout(fetchChatNotifications, 1000);
}

function fetchChatNotifications() {
    console.log('Fetching chat notifications...');

    fetch('<?php echo site_url("recruiter/chat/ajax_get_chat_notifications"); ?>', {
            method: 'GET', // Change to GET to avoid CSRF issues for read-only endpoints
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.log('Error response text:', text);
                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Chat notifications response:', data);
            if (data.success) {
                updateChatNotificationUI(data);
            } else {
                console.error('Chat notification fetch failed:', data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching chat notifications:', error);
        });
}

function updateChatNotificationUI(data) {
    const badge = document.querySelector('.chat-notification-badge');
    const countElement = document.querySelector('.chat-notification-count');
    const chatBell = document.querySelector('.chat-notifications-menu .dropdown-toggle');

    if (data.unread_count > 0) {
        // Update badge
        if (badge) {
            badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
            badge.style.display = 'flex';
        }

        // Update count text
        if (countElement) {
            countElement.textContent = data.unread_count;
        }

        // Add pulse animation
        if (chatBell) {
            chatBell.style.animation = 'chat-pulse 2s infinite';
        }
    } else {
        // Hide badge if no notifications
        if (badge) {
            badge.style.display = 'none';
        }
        if (countElement) {
            countElement.textContent = '0';
        }

        // Remove pulse animation
        if (chatBell) {
            chatBell.style.animation = 'none';
        }
    }
}

// Start both polling when page loads
document.addEventListener('DOMContentLoaded', function() {
    startRecruiterNotificationPolling();
    startChatNotificationPolling();
});
</script>