<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-fluid">
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <h2>Notifications</h2>
                    <div class="header-dropdown">
                        <?php if ($unread_count > 0): ?>
                        <button class="btn btn-primary" onclick="markAllNotificationsAsRead()">
                            <i class="fa fa-check"></i> Mark All as Read
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="body">
                    <?php if (!empty($notifications)): ?>
                    <div class="list-group">
                        <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item <?= $notification->is_read ? '' : 'list-group-item-warning' ?>">
                            <div class="row">
                                <div class="col-md-1 text-center">
                                    <i
                                        class="fa <?= get_notification_icon($notification->type) ?> fa-2x text-muted"></i>
                                </div>
                                <div class="col-md-9">
                                    <h5 class="mb-1"><?= htmlspecialchars($notification->title) ?></h5>
                                    <p class="mb-1"><?= htmlspecialchars($notification->message) ?></p>
                                    <small class="text-muted">
                                        <i class="fa fa-clock-o"></i>
                                        <?= time_elapsed_string($notification->created_at) ?>
                                    </small>
                                </div>
                                <div class="col-md-2 text-right">
                                    <?php if (!$notification->is_read): ?>
                                    <button class="btn btn-sm btn-outline-primary"
                                        onclick="markNotificationAsRead(<?= $notification->id ?>)">
                                        Mark Read
                                    </button>
                                    <?php endif; ?>
                                    <a href="<?= get_notification_link($notification) ?>" class="btn btn-sm btn-info">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($pagination)): ?>
                    <div class="text-center mt-3">
                        <?= $pagination ?>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa fa-bell-o fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No notifications</h4>
                        <p class="text-muted">You're all caught up!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markNotificationAsRead(notificationId) {
    $.ajax({
        url: '<?= site_url("recruiter/notifications/mark_as_read") ?>',
        type: 'POST',
        data: {
            notification_id: notificationId
        },
        success: function(response) {
            location.reload();
        }
    });
}

function markAllNotificationsAsRead() {
    $.ajax({
        url: '<?= site_url("recruiter/notifications/mark_all_read") ?>',
        type: 'POST',
        success: function(response) {
            location.reload();
        }
    });
}
</script>