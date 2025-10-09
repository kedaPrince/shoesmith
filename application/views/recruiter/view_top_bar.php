<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
                    <!-- Notifications Dropdown -->
                    <li class="dropdown notifications-menu">
                        <a href="#" class="dropdown-toggle icon-menu" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bell-o"></i>
                            <?php 
                            $unread_count = isset($unread_notifications_count) ? $unread_notifications_count : 0;
                            if ($unread_count > 0): ?>
                            <span class="notification-badge"><?= $unread_count > 99 ? '99+' : $unread_count ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">
                                You have <span class="notification-count"><?= $unread_count ?></span> new notifications
                            </li>
                            <li>
                                <ul class="menu notifications-list">
                                    <?php if (!empty($notifications)): ?>
                                    <?php foreach ($notifications as $notification): ?>
                                    <li class="notification-item <?= $notification->is_read ? 'read' : 'unread' ?>"
                                        data-notification-id="<?= $notification->id ?>">
                                        <a href="<?= $this->get_notification_link($notification) ?>"
                                            class="notification-link"
                                            onclick="markNotificationAsRead(<?= $notification->id ?>)">
                                            <div class="notification-content">
                                                <h4 class="notification-title">
                                                    <?= htmlspecialchars($notification->title) ?></h4>
                                                <p class="notification-message">
                                                    <?= htmlspecialchars($notification->message) ?></p>
                                                <small class="notification-time">
                                                    <i class="fa fa-clock-o"></i>
                                                    <?= $this->time_elapsed_string($notification->created_at) ?>
                                                </small>
                                            </div>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <li class="no-notifications">
                                        <div class="notification-content text-center">
                                            <p>No new notifications</p>
                                        </div>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                            <li class="footer">
                                <a href="<?= site_url('recruiter/notifications') ?>">View All Notifications</a>
                                <?php if ($unread_count > 0): ?>
                                <a href="javascript:void(0)" onclick="markAllNotificationsAsRead()" class="pull-right">
                                    Mark all as read
                                </a>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a class="dark-mode-toggle icon-menu" href="javascript:toggle_dark_mode()"
                            title="Toggle Dark Mode" data-toggle="tt" data-placement="top">
                            <i class="dark-mode-disabled fa fa-moon-o"></i>
                            <i class="dark-mode-enabled fa fa-sun-o"></i>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url(); ?>" target="_blank" class="icon-menu" id="top-bar-return-btn"
                            data-toggle="tt" data-placement="top" title="Visit Site" data-original-title="Visit Site"
                            title="Visit Site">
                            Visit Site
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
/* Notification Styles */
.notifications-menu .dropdown-menu {
    width: 350px;
    padding: 0;
}

.notification-badge {
    background: #dc3545;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 10px;
    position: absolute;
    top: 8px;
    right: 8px;
    min-width: 18px;
    text-align: center;
}

.notifications-list {
    max-height: 300px;
    overflow-y: auto;
    list-style: none;
    padding: 0;
    margin: 0;
}

.notification-item {
    border-bottom: 1px solid #f4f4f4;
}

.notification-item.unread {
    background-color: #f8f9fa;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-link {
    display: block;
    padding: 10px 15px;
    color: #333;
    text-decoration: none;
}

.notification-link:hover {
    background-color: #f5f5f5;
    text-decoration: none;
}

.notification-content h4 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
}

.notification-content p {
    margin: 0 0 5px 0;
    font-size: 12px;
    color: #666;
}

.notification-time {
    color: #999;
    font-size: 11px;
}

.no-notifications {
    padding: 20px;
    text-align: center;
    color: #999;
}

.dropdown-menu .header {
    background: #f8f9fa;
    padding: 10px 15px;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
}

.dropdown-menu .footer {
    background: #f8f9fa;
    padding: 10px 15px;
    border-top: 1px solid #dee2e6;
}

.dropdown-menu .footer a {
    color: #007bff;
    text-decoration: none;
}

.dropdown-menu .footer a:hover {
    text-decoration: underline;
}
</style>

<script>
function markNotificationAsRead(notificationId) {
    $.ajax({
        url: '<?= site_url("recruiter/notifications/mark_as_read") ?>',
        type: 'POST',
        data: {
            notification_id: notificationId
        },
        success: function(response) {
            // Remove the unread styling
            $('.notification-item[data-notification-id="' + notificationId + '"]').removeClass('unread')
                .addClass('read');

            // Update badge count
            updateNotificationBadge();
        }
    });
}

function markAllNotificationsAsRead() {
    $.ajax({
        url: '<?= site_url("recruiter/notifications/mark_all_read") ?>',
        type: 'POST',
        success: function(response) {
            // Mark all as read in UI
            $('.notification-item').removeClass('unread').addClass('read');

            // Update badge count to zero
            $('.notification-badge').remove();
            $('.notification-count').text('0');
        }
    });
}

function updateNotificationBadge() {
    let currentCount = parseInt($('.notification-badge').text()) || 0;
    if (currentCount > 1) {
        $('.notification-badge').text(currentCount - 1);
        $('.notification-count').text(currentCount - 1);
    } else {
        $('.notification-badge').remove();
        $('.notification-count').text('0');
    }
}

// Auto-refresh notifications every 30 seconds
setInterval(function() {
    $.ajax({
        url: '<?= site_url("recruiter/notifications/get_count") ?>',
        type: 'GET',
        success: function(response) {
            if (response.unread_count > 0) {
                if ($('.notification-badge').length === 0) {
                    $('.fa-bell-o').after('<span class="notification-badge">' + response
                        .unread_count + '</span>');
                } else {
                    $('.notification-badge').text(response.unread_count > 99 ? '99+' : response
                        .unread_count);
                }
                $('.notification-count').text(response.unread_count);
            } else {
                $('.notification-badge').remove();
                $('.notification-count').text('0');
            }
        }
    });
}, 30000);
</script>