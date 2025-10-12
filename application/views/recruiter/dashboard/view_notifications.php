<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
/* Your existing CSS styles remain the same */
.notification-card.updated .notification-header {
    border-left: 4px solid #ffc107;
    background: #fffbf0;
}

.updated-fields {
    background: #fff8e1;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 12px;
    margin: 12px 0;
}

.updated-fields h5 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #856404;
    font-weight: 600;
}

.updated-field-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.updated-field-badge {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
}

.notification-type-badge {
    background: #17a2b8;
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 8px;
    text-transform: uppercase;
}

.notification-type-badge.new {
    background: #28a745;
}

.notification-type-badge.updated {
    background: #ffc107;
    color: #212529;
}

.update-highlight {
    animation: pulse-update 2s ease-in-out;
}

@keyframes pulse-update {
    0% {
        background-color: #fffbf0;
    }

    50% {
        background-color: #fff3cd;
    }

    100% {
        background-color: #fffbf0;
    }
}

/* Debug info for testing */
.debug-info {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
    margin: 10px 0;
    font-size: 12px;
    color: #6c757d;
}

.notification-message.update-message {
    font-weight: 600;
    color: #856404;
    background: #fff3cd;
    padding: 8px 12px;
    border-radius: 4px;
    border-left: 3px solid #ffc107;
}

/* Fix for accordion */
.notification-expandable {
    display: none;
    padding-top: 15px;
}

.notification-card.expanded .notification-expandable {
    display: block;
}

.notification-card.expanded .expand-indicator i {
    transform: rotate(180deg);
}

.expand-indicator i {
    transition: transform 0.3s ease;
}
</style>

<div id="main-content">
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

                        <div class="card-body">
                            <?php if (!empty($notifications)): ?>

                            <!-- Debug Info -->
                            <div class="debug-info">
                                <strong>Debug Info:</strong>
                                Total Notifications: <?php echo count($notifications); ?> |
                                New Jobs:
                                <?php echo count(array_filter($notifications, function($n) { return $n->type === 'job_added'; })); ?>
                                |
                                Updated Jobs:
                                <?php echo count(array_filter($notifications, function($n) { return $n->type === 'job_updated'; })); ?>
                                <?php 
                                // Debug: Show specific update notifications
                                $update_notifications = array_filter($notifications, function($n) { 
                                    return $n->type === 'job_updated'; 
                                });
                                foreach ($update_notifications as $update_notif) {
                                    echo "<!-- Update Notification ID: {$update_notif->id}, Updated Fields: {$update_notif->updated_fields} -->";
                                }
                                ?>
                            </div>

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
                                    
                                    $agency_name = !empty($notification->agency_name) ? $notification->agency_name : 'Your Agency';
                                    
                                    // Parse updated fields
                                    $updated_fields = [];
                                    $is_update_notification = $notification->type === 'job_updated';
                                    if ($is_update_notification && !empty($notification->updated_fields)) {
                                        $updated_fields = json_decode($notification->updated_fields, true);
                                        if (!is_array($updated_fields)) {
                                            $updated_fields = [];
                                        }
                                    }
                                    
                                    // Debug output for this notification
                                    echo "<!-- Notification ID: {$notification->id}, Type: {$notification->type}, Updated Fields: " . (!empty($updated_fields) ? implode(', ', $updated_fields) : 'none') . " -->";
                                ?>
                                <div class="notification-card <?php echo $notification->is_read ? '' : 'unread'; ?> <?php echo $is_update_notification ? 'updated update-highlight' : ''; ?>"
                                    data-notification-id="<?php echo $notification->id; ?>"
                                    data-notification-type="<?php echo $notification->type; ?>">

                                    <div class="notification-header">
                                        <div class="notification-title-section">
                                            <h3 class="notification-title">
                                                <?php echo htmlspecialchars($notification->title); ?>
                                                <span
                                                    class="notification-type-badge <?php echo $is_update_notification ? 'updated' : 'new'; ?>">
                                                    <?php echo $is_update_notification ? 'UPDATED' : 'NEW'; ?>
                                                </span>
                                            </h3>
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
                                                <?php if ($is_update_notification && !empty($updated_fields)): ?>
                                                <span class="notification-tag"
                                                    style="background: #ffc107; color: #212529;">
                                                    <i class="fa fa-edit"></i> <?php echo count($updated_fields); ?>
                                                    fields updated
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <span class="notification-time">
                                            <?php echo date('M j, g:i A', strtotime($notification->created_at)); ?>
                                        </span>
                                    </div>

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

                                    <div class="notification-expandable">
                                        <!-- Updated Fields Section -->
                                        <?php if ($is_update_notification && !empty($updated_fields)): ?>
                                        <div class="updated-fields">
                                            <h5><i class="fa fa-edit"></i> 🆕 Updated Information</h5>
                                            <p class="text-muted small mb-2">The following fields were recently updated:
                                            </p>
                                            <div class="updated-field-list">
                                                <?php 
                                                $field_labels = [
                                                    'name' => 'Job Title',
                                                    'reference_number' => 'Reference Number',
                                                    'department' => 'Department',
                                                    'employment_type' => 'Employment Type',
                                                    'description' => 'Job Description',
                                                    'project_overview' => 'Project Overview',
                                                    'pay_rate' => 'Pay Rate',
                                                    'salary_min' => 'Minimum Salary',
                                                    'salary_max' => 'Maximum Salary',
                                                    'roster' => 'Roster',
                                                    'accommodation' => 'Accommodation',
                                                    'transport' => 'Transport',
                                                    'is_remote' => 'Remote Work',
                                                    'industry_id' => 'Industry',
                                                    'application_email' => 'Application Email',
                                                    'application_url' => 'Application URL',
                                                    'closing_date' => 'Closing Date'
                                                ];
                                                
                                                foreach ($updated_fields as $field): 
                                                    $label = isset($field_labels[$field]) ? $field_labels[$field] : $field;
                                                ?>
                                                <span class="updated-field-badge">
                                                    <i class="fa fa-pencil-alt"></i>
                                                    <?php echo htmlspecialchars($label); ?>
                                                </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php elseif ($is_update_notification): ?>
                                        <div class="updated-fields">
                                            <h5><i class="fa fa-edit"></i> Job Updated</h5>
                                            <p class="text-muted small">This job has been updated with general changes.
                                            </p>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ($job_data && !empty($job_data->department)): ?>
                                        <div class="job-details-grid">
                                            <div class="job-detail-item">
                                                <span class="detail-label">Department</span>
                                                <span
                                                    class="detail-value"><?php echo htmlspecialchars($job_data->department); ?></span>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <div class="notification-content">
                                            <?php if ($is_update_notification): ?>
                                            <p class="notification-message update-message">
                                                <i class="fa fa-info-circle"></i>
                                                <?php echo htmlspecialchars($notification->message); ?>
                                            </p>
                                            <?php else: ?>
                                            <p class="notification-message">
                                                <?php echo htmlspecialchars($notification->message); ?>
                                            </p>
                                            <?php endif; ?>
                                        </div>

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

                        <?php if (!empty($notifications)): ?>
                        <div class="card-footer bg-transparent border-top">
                            <div class="row">
                                <div class="col">
                                    <small class="text-muted">
                                        Showing <?php echo count($notifications); ?> notification(s)
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <small class="text-muted">
                                        <i class="fa fa-circle text-success"></i> New Jobs
                                        <i class="fa fa-circle text-warning ml-2"></i> Updated Jobs
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
// Fixed JavaScript - No syntax errors
document.addEventListener('DOMContentLoaded', function() {
    const notificationCards = document.querySelectorAll('.notification-card');

    console.log('Found notification cards:', notificationCards.length);

    notificationCards.forEach(card => {
        card.addEventListener('click', function(e) {
            console.log('Card clicked:', card.dataset.notificationId);

            // Don't toggle if clicking on action buttons or links
            if (e.target.closest('.btn-notification') ||
                e.target.closest('.action-buttons') ||
                e.target.closest('a') ||
                e.target.tagName === 'BUTTON') {
                console.log('Clicked on action button, skipping toggle');
                return;
            }

            // Close all other notifications
            notificationCards.forEach(otherCard => {
                if (otherCard !== card && otherCard.classList.contains('expanded')) {
                    otherCard.classList.remove('expanded');
                    console.log('Closed other notification:', otherCard.dataset
                        .notificationId);
                }
            });

            // Toggle current notification
            card.classList.toggle('expanded');
            console.log('Toggled notification:', card.dataset.notificationId, 'Expanded:', card
                .classList.contains('expanded'));
        });
    });

    // Highlight update notifications
    const updateNotifications = document.querySelectorAll('.notification-card.updated');
    console.log('Found update notifications:', updateNotifications.length);
    updateNotifications.forEach(card => {
        console.log('Update notification:', card.dataset.notificationId, 'Type:', card.dataset
            .notificationType);
    });
});

// Close notifications when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.notification-card')) {
        const expandedCards = document.querySelectorAll('.notification-card.expanded');
        console.log('Closing all expanded notifications:', expandedCards.length);
        expandedCards.forEach(card => {
            card.classList.remove('expanded');
        });
    }
});

// Your existing JavaScript functions
function markAsRead(notificationId) {
    console.log('Marking as read:', notificationId);
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