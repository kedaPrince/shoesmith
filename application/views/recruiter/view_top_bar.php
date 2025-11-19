<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
// Load notifications directly in the top bar
$ci =& get_instance();
$ci->load->model('recruiter/Model_notifications');

// Get recruiter ID from session
$login_data = $ci->session->userdata('login');
$recruiter_id = !empty($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : null;

// Load notifications if recruiter is logged in
if ($recruiter_id) {
    $notifications = $ci->Model_notifications->get_unread_notifications($recruiter_id);
    $unread_count = $ci->Model_notifications->count_unread_notifications($recruiter_id);
} else {
    $notifications = [];
    $unread_count = 0;
}
?>

<style>
#navbar-menu li {
    float: inherit;
}

/* Notification Bell Styling */
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
    <?php if ($unread_count > 0): ?>.dropdown-toggle {
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
    padding: 8px 12px;
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
</style>

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
                            <?php if ($unread_count > 0): ?>
                            <span class="notification-badge"><?php echo $unread_count; ?></span>
                            <?php else: ?>
                            <span class="notification-badge" style="display: none;">0</span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu"
                            style="width: 350px; padding: 0; border-bottom-left-radius: 23px; border-bottom-right-radius: 23px;">
                            <li class="header"
                                style="background: #f8f9fa; padding: 10px 15px; border-bottom: 1px solid #dee2e6;color:#000;">
                                You have <span class="notification-count"><?php echo $unread_count; ?></span>
                                notifications
                            </li>
                            <li style="max-height: 250px; overflow-y: auto;">
                                <ul class="menu" style="list-style: none; padding: 0; margin: 0;">
                                    <?php if (!empty($notifications)): ?>
                                    <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                                    <li style="border-bottom: 1px solid #f0f0f0;">
                                        <a href="javascript:void(0);"
                                            onclick="markAsRead(<?php echo $notification->id; ?>)"
                                            style="display: block; padding: 10px 15px; color: #333; text-decoration: none;">
                                            <div style="float: left; margin-right: 10px;">
                                                <i class="fa fa-briefcase" style="color: #007bff;"></i>
                                            </div>
                                            <div style="overflow: hidden;">
                                                <h4 style="margin: 0 0 5px 0; font-size: 14px; font-weight: bold;">
                                                    <?php echo $notification->title; ?></h4>
                                                <p style="margin: 0 0 5px 0; font-size: 12px; color: #666;">
                                                    <?php echo character_limiter($notification->message, 50); ?></p>
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
                                        No new notifications
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                            <li class="footer"
                                style="background: #48b34d;padding: 10px 15px;/* border-top: 1px solid #dee2e6;border-radius: 23px; */text-align: center;color: #fff;">
                                <a href="<?php echo site_url('recruiter/dashboard/notifications'); ?>"
                                    style="color: #ffffff;text-decoration: none;font-weight: bold;">
                                    View All Notifications
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- <li>
                        <a class="dark-mode-toggle icon-menu" href="javascript:toggle_dark_mode()"
                            title="Toggle Dark Mode" data-toggle="tt" data-placement="top">
                            <i class="dark-mode-disabled fa fa-moon-o"></i>
                            <i class="dark-mode-enabled fa fa-sun-o"></i>
                        </a>
                    </li> -->
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

<script>
function markAsRead(notificationId) {
    fetch('<?php echo site_url("recruiter/dashboard/ajax_mark_notification_read"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'notification_id=' + notificationId +
                '&<?php echo $ci->security->get_csrf_token_name(); ?>=<?php echo $ci->security->get_csrf_hash(); ?>'
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                // Update badge count
                const badge = document.querySelector('.notification-badge');
                const count = document.querySelector('.notification-count');

                if (badge) badge.textContent = response.unread_count;
                if (count) count.textContent = response.unread_count;

                // Hide badge if count is 0
                if (badge) {
                    badge.style.display = response.unread_count == 0 ? 'none' : 'flex';
                }

                // Remove the notification from dropdown
                const notificationElement = document.querySelector('[onclick="markAsRead(' + notificationId +
                    ')"]');
                if (notificationElement) {
                    notificationElement.closest('li').remove();
                }

                // If no notifications left, show "No new notifications"
                const menu = document.querySelector('.menu');
                if (menu && menu.querySelectorAll('li').length === 0) {
                    menu.innerHTML =
                        '<li style="padding: 15px; text-align: center; color: #666;">No new notifications</li>';
                }
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
}
</script>