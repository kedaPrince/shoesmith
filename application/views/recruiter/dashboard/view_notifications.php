<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
/* Your existing CSS styles remain the same */
</style>

<div id="main-content">
    <!-- Header Section -->
    <header class="page-header">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="<?php echo site_url('recruiter/dashboard'); ?>">
                                    <i class="fa fa-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Notifications</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="notifications-container">
                    <div class="card main-card">
                        <!-- Card Header -->
                        <div class="card-header-custom">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h2 class="mb-1">Job Notifications</h2>
                                    <small class="text-muted">Click on notifications to view more details</small>
                                </div>
                                <div class="col-auto">
                                    <?php if (!empty($notifications)): ?>
                                    <button class="btn btn-outline-primary" onclick="markAllAsRead()">
                                        <i class="fa fa-check-double"></i> Mark All as Read
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications List -->
                        <div class="card-body">
                            <?php if (!empty($notifications)): ?>
                            <div class="notifications-list">
                                <?php foreach ($notifications as $notification): ?>
                                <?php 
                                    $job_id = $notification->related_entity_id;
                                    $job_data = (object)[
                                        'salary_min' => $notification->salary_min,
                                        'salary_max' => $notification->salary_max,
                                        'employment_type' => $notification->employment_type,
                                        'department' => $notification->department,
                                        'is_remote' => $notification->is_remote
                                    ];
                                    
                                    // Agency name is now available from the query
                                    $agency_name = !empty($notification->agency_name) ? $notification->agency_name : 'Your Agency';
                                ?>
                                <div class="notification-card <?php echo $notification->is_read ? '' : 'unread'; ?>"
                                    data-notification-id="<?php echo $notification->id; ?>">

                                    <!-- Header -->
                                    <div class="notification-header">
                                        <div class="notification-title-section">
                                            <h3 class="notification-title">
                                                <?php echo htmlspecialchars($notification->title); ?></h3>
                                            <div class="notification-meta">
                                                <span class="meta-item">
                                                    <i class="fa fa-building"></i>
                                                    <?php echo htmlspecialchars($agency_name); ?>
                                                </span>
                                                <span class="meta-item">
                                                    <i class="fa fa-clock"></i>
                                                    <?php echo time_ago($notification->created_at); ?>
                                                </span>
                                                <?php if (!$notification->is_read): ?>
                                                <span class="notification-tag urgent">
                                                    <i class="fa fa-bell"></i> New
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <span class="notification-time">
                                            <?php echo date('M j, g:i A', strtotime($notification->created_at)); ?>
                                        </span>
                                    </div>

                                    <!-- Quick Info Bar (Always Visible) -->
                                    <?php if ($job_data && ($job_data->salary_min || $job_data->salary_max || $job_data->employment_type)): ?>
                                    <div class="quick-info-bar">
                                        <?php if ($job_data->salary_min || $job_data->salary_max): ?>
                                        <div class="quick-info-item">
                                            <span class="info-label">Salary</span>
                                            <span class="info-value salary">
                                                <?php 
                                                if (!empty($job_data->salary_min) && !empty($job_data->salary_max)) {
                                                    echo htmlspecialchars($job_data->salary_min) . ' - ' . htmlspecialchars($job_data->salary_max);
                                                } elseif (!empty($job_data->salary_min)) {
                                                    echo 'From ' . htmlspecialchars($job_data->salary_min);
                                                } elseif (!empty($job_data->salary_max)) {
                                                    echo 'Up to ' . htmlspecialchars($job_data->salary_max);
                                                } else {
                                                    echo 'Not specified';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($job_data->employment_type)): ?>
                                        <div class="quick-info-item">
                                            <span class="info-label">Job Type</span>
                                            <span class="info-value job-type">
                                                <?php 
                                                $employment_types = [
                                                    'full-time' => 'Full Time',
                                                    'part-time' => 'Part Time', 
                                                    'contract' => 'Contract',
                                                    'internship' => 'Internship',
                                                    'temporary' => 'Temporary'
                                                ];
                                                echo $employment_types[$job_data->employment_type] ?? $job_data->employment_type;
                                                ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>

                                        <div class="quick-info-item">
                                            <span class="info-label">Remote</span>
                                            <span class="info-value">
                                                <?php echo !empty($job_data->is_remote) ? 'Yes' : 'No'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Expandable Content -->
                                    <div class="notification-expandable">
                                        <!-- Job Details Grid -->
                                        <?php if ($job_data && !empty($job_data->department)): ?>
                                        <div class="job-details-grid">
                                            <?php if (!empty($job_data->department)): ?>
                                            <div class="job-detail-item">
                                                <span class="detail-label">Department</span>
                                                <span
                                                    class="detail-value"><?php echo htmlspecialchars($job_data->department); ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Content -->
                                        <div class="notification-content">
                                            <p class="notification-message">
                                                <?php echo htmlspecialchars($notification->message); ?>
                                            </p>
                                        </div>

                                        <!-- Actions -->
                                        <div class="notification-actions">
                                            <div class="action-buttons">
                                                <?php if (!empty($job_id)): ?>
                                                <a href="<?php echo site_url('recruiter/jobs/view/' . $job_id); ?>"
                                                    class="btn btn-notification btn-view-job">
                                                    <i class="fa fa-eye"></i> View Full Job Details
                                                </a>
                                                <?php else: ?>
                                                <span class="text-muted small">No job linked</span>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!$notification->is_read): ?>
                                            <button class="btn btn-notification btn-mark-read"
                                                onclick="markAsRead(<?php echo $notification->id; ?>)">
                                                <i class="fa fa-check"></i> Mark as Read
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Expand Indicator -->
                                    <div class="expand-indicator">
                                        <i class="fa fa-chevron-down"></i> Click to view more details
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fa fa-bell-slash"></i>
                                </div>
                                <h4>No Notifications</h4>
                                <p class="text-muted">You're all caught up! No new job notifications at this time.</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Footer -->
                        <?php if (!empty($notifications)): ?>
                        <div class="card-footer bg-transparent border-top">
                            <div class="row">
                                <div class="col">
                                    <small class="text-muted">
                                        Showing <?php echo count($notifications); ?> job notification(s)
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <small class="text-muted">
                                        <i class="fa fa-circle" style="color: var(--notification-primary);"></i> Unread
                                        notifications highlighted
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
// Toggle notification expansion
document.addEventListener('DOMContentLoaded', function() {
    const notificationCards = document.querySelectorAll('.notification-card');

    notificationCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't toggle if clicking on action buttons
            if (e.target.closest('.btn-notification') || e.target.closest('.action-buttons')) {
                return;
            }

            // Close all other notifications
            notificationCards.forEach(otherCard => {
                if (otherCard !== card && otherCard.classList.contains('expanded')) {
                    otherCard.classList.remove('expanded');
                }
            });

            // Toggle current notification
            card.classList.toggle('expanded');
        });
    });
});

// Close notifications when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.notification-card')) {
        document.querySelectorAll('.notification-card.expanded').forEach(card => {
            card.classList.remove('expanded');
        });
    }
});

// Your existing JavaScript functions
function markAsRead(notificationId) {
    $.post('<?php echo site_url("recruiter/dashboard/ajax_mark_notification_read"); ?>', {
        notification_id: notificationId,
        <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
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
            showToast('Notification marked as read', 'success');
        }
    }, 'json').fail(function(xhr, status, error) {
        console.error('Mark as read error:', error);
        showToast('Error marking notification as read', 'error');
    });
}

function markAllAsRead() {
    if (!confirm('Are you sure you want to mark all notifications as read?')) {
        return;
    }

    $.post('<?php echo site_url("recruiter/dashboard/ajax_mark_all_read"); ?>', {
        <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
    }, function(response) {
        if (response.success) {
            document.querySelectorAll('.notification-card').forEach(card => {
                card.classList.remove('unread');
                const markReadBtn = card.querySelector('.btn-mark-read');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
            });
            updateNotificationBadge(response.unread_count);
            showToast('All notifications marked as read', 'success');
        }
    }, 'json').fail(function(xhr, status, error) {
        console.error('Mark all read error:', error);
        showToast('Error marking notifications as read', 'error');
    });
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.textContent = count;
        if (count == 0) {
            badge.style.display = 'none';
        } else {
            badge.style.display = 'inline';
        }
    }
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>