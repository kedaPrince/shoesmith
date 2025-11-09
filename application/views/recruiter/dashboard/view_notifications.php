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

/* HM Decision Notification Styles */
.notification-card.hm-decision {
    border-left: 4px solid #6f42c1;
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
}

.notification-card.hm-decision.accepted {
    border-left-color: #28a745;
    background: linear-gradient(135deg, #f8fff9 0%, #f0fff4 100%);
}

.notification-card.hm-decision.rejected {
    border-left-color: #dc3545;
    background: linear-gradient(135deg, #fff8f8 0%, #fff0f0 100%);
}

.hm-decision-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.hm-decision-badge.accepted {
    background: #28a745;
    color: white;
}

.hm-decision-badge.rejected {
    background: #dc3545;
    color: white;
}

.decision-notes {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
    position: relative;
}

.decision-notes:before {
    content: '"';
    font-size: 32px;
    color: #6c757d;
    opacity: 0.3;
    position: absolute;
    top: 5px;
    left: 10px;
}

.decision-notes-content {
    font-style: italic;
    color: #495057;
    line-height: 1.5;
    margin-left: 10px;
}

.candidate-highlight {
    background: linear-gradient(120deg, #a8edea 0%, #fed6e3 100%);
    border-radius: 10px;
    padding: 15px;
    margin: 10px 0;
    border: 1px solid #e9ecef;
}

.candidate-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
}

.candidate-info-item {
    text-align: center;
}

.candidate-info-label {
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 2px;
}

.candidate-info-value {
    font-size: 14px;
    font-weight: 700;
    color: #495057;
}

/* Toast Notification Styles */
.hm-decision-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 400px;
    max-width: 500px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    animation: slideInRight 0.5s ease-out;
}

.hm-decision-toast.accepted {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.hm-decision-toast.rejected {
    background: linear-gradient(135deg, #dc3545, #e83e8c);
    color: white;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }

    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px 10px;
}

.toast-title {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.toast-body {
    padding: 10px 20px 15px;
}

.toast-notes {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    padding: 10px;
    margin-top: 8px;
    font-style: italic;
}

.toast-close {
    background: none;
    border: none;
    color: inherit;
    font-size: 18px;
    cursor: pointer;
    opacity: 0.8;
}

.toast-close:hover {
    opacity: 1;
}

/* Pulse animation for new notifications */
@keyframes pulse-glow {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }

    70% {
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
    }

    100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
    }
}

.notification-card.hm-decision.unread {
    animation: pulse-glow 2s infinite;
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
                                |
                                HM Decisions:
                                <?php echo count(array_filter($notifications, function($n) { return $n->type === 'hm_decision'; })); ?>
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
                                    // HM Decision specific handling
                                    $is_hm_decision = $notification->type === 'hm_decision';
                                    $metadata = !empty($notification->metadata) ? json_decode($notification->metadata) : null;
                                    
                                    // Existing job notification handling
                                    $job_id = $notification->related_entity_id;
                                    $job_data = (object)[
                                        'salary_min' => $notification->salary_min,
                                        'salary_max' => $notification->salary_max,
                                        'employment_type' => $notification->employment_type,
                                        'department' => $notification->department,
                                        'is_remote' => $notification->is_remote
                                    ];
                                    
                                    $agency_name = !empty($notification->agency_name) ? $notification->agency_name : 'Your Agency';
                                    
                                    // Parse updated fields for job updates
                                    $updated_fields = [];
                                    $is_update_notification = $notification->type === 'job_updated';
                                    if ($is_update_notification && !empty($notification->updated_fields)) {
                                        $updated_fields = json_decode($notification->updated_fields, true);
                                        if (!is_array($updated_fields)) {
                                            $updated_fields = [];
                                        }
                                    }
                                    
                                    // Debug output for this notification
                                    echo "<!-- Notification ID: {$notification->id}, Type: {$notification->type} -->";
                                ?>

                                <!-- NOTIFICATION CARD - UPDATED WITH HM DECISION SUPPORT -->
                                <div class="notification-card <?php echo $notification->is_read ? '' : 'unread'; ?> 
                                     <?php echo $is_update_notification ? 'updated update-highlight' : ''; ?>
                                     <?php echo $is_hm_decision ? 'hm-decision ' . ($metadata ? $metadata->decision : '') : ''; ?>"
                                    data-notification-id="<?php echo $notification->id; ?>"
                                    data-notification-type="<?php echo $notification->type; ?>">

                                    <div class="notification-header">
                                        <div class="notification-title-section">
                                            <h3 class="notification-title">
                                                <?php echo htmlspecialchars($notification->title); ?>

                                                <!-- Notification Type Badges -->
                                                <?php if ($is_hm_decision && $metadata): ?>
                                                <span class="hm-decision-badge <?php echo $metadata->decision; ?>">
                                                    <?php echo ucfirst($metadata->decision); ?>
                                                </span>
                                                <?php else: ?>
                                                <span
                                                    class="notification-type-badge <?php echo $is_update_notification ? 'updated' : 'new'; ?>">
                                                    <?php echo $is_update_notification ? 'UPDATED' : 'NEW'; ?>
                                                </span>
                                                <?php endif; ?>
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

                                                <!-- Additional tags based on notification type -->
                                                <?php if ($is_update_notification && !empty($updated_fields)): ?>
                                                <span class="notification-tag"
                                                    style="background: #ffc107; color: #212529;">
                                                    <i class="fa fa-edit"></i> <?php echo count($updated_fields); ?>
                                                    fields updated
                                                </span>
                                                <?php endif; ?>

                                                <?php if ($is_hm_decision): ?>
                                                <span class="notification-tag"
                                                    style="background: #6f42c1; color: white;">
                                                    <i class="fa fa-user-tie"></i> HM Decision
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <span class="notification-time">
                                            <?php echo date('M j, g:i A', strtotime($notification->created_at)); ?>
                                        </span>
                                    </div>

                                    <!-- HM Decision Candidate Highlight -->
                                    <?php if ($is_hm_decision && $metadata): ?>
                                    <div class="candidate-highlight">
                                        <div class="candidate-info-grid">
                                            <div class="candidate-info-item">
                                                <div class="candidate-info-label">Candidate</div>
                                                <div class="candidate-info-value">
                                                    <?php echo htmlspecialchars($metadata->candidate_name); ?></div>
                                            </div>
                                            <div class="candidate-info-item">
                                                <div class="candidate-info-label">Reference</div>
                                                <div class="candidate-info-value">
                                                    <?php echo htmlspecialchars($metadata->candidate_reference); ?>
                                                </div>
                                            </div>
                                            <div class="candidate-info-item">
                                                <div class="candidate-info-label">Job</div>
                                                <div class="candidate-info-value">
                                                    <?php echo htmlspecialchars($metadata->job_name); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Quick Info Bar (for job notifications) -->
                                    <?php if (!$is_hm_decision && $job_data && ($job_data->salary_min || $job_data->salary_max || $job_data->employment_type)): ?>
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
                                        <!-- HM Decision Notes -->
                                        <?php if ($is_hm_decision && $metadata && !empty($metadata->notes)): ?>
                                        <div class="decision-notes">
                                            <div class="decision-notes-content">
                                                <?php echo nl2br(htmlspecialchars($metadata->notes)); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Updated Fields Section (for job updates) -->
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

                                        <!-- Job Details (for job notifications) -->
                                        <?php if (!$is_hm_decision && $job_data && !empty($job_data->department)): ?>
                                        <div class="job-details-grid">
                                            <div class="job-detail-item">
                                                <span class="detail-label">Department</span>
                                                <span
                                                    class="detail-value"><?php echo htmlspecialchars($job_data->department); ?></span>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Notification Message -->
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

                                        <!-- Action Buttons -->
                                        <div class="notification-actions">
                                            <div class="action-buttons">
                                                <?php if (!empty($job_id) && !$is_hm_decision): ?>
                                                <a href="<?php echo site_url('recruiter/jobs/view/' . $job_id); ?>"
                                                    class="btn btn-notification btn-view-job">
                                                    <i class="fa fa-eye"></i> View Full Job Details
                                                </a>
                                                <?php elseif ($is_hm_decision && $metadata): ?>
                                                <a href="<?php echo site_url('recruiter/candidates/view/' . $notification->related_entity_id); ?>"
                                                    class="btn btn-notification btn-view-job">
                                                    <i class="fa fa-user"></i> View Candidate
                                                </a>
                                                <?php else: ?>
                                                <span class="text-muted small">No linked content</span>
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
                                        <i class="fa fa-circle text-primary ml-2"></i> HM Decisions
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

// Real-time HM Decision Notifications
function showHMDecisionToast(notification) {
    const toast = document.createElement('div');
    toast.className = `hm-decision-toast ${notification.metadata.decision}`;

    toast.innerHTML = `
        <div class="toast-header">
            <h4 class="toast-title">
                ${notification.metadata.decision === 'accepted' ? '🎉' : '❌'}
                ${notification.title}
            </h4>
            <button class="toast-close">&times;</button>
        </div>
        <div class="toast-body">
            <div class="candidate-info-grid">
                <div class="candidate-info-item">
                    <div class="candidate-info-label">Candidate</div>
                    <div class="candidate-info-value">${notification.metadata.candidate_name}</div>
                </div>
                <div class="candidate-info-item">
                    <div class="candidate-info-label">Job</div>
                    <div class="candidate-info-value">${notification.metadata.job_name}</div>
                </div>
            </div>
            ${notification.metadata.notes ? `
                <div class="toast-notes">
                    <strong>HM Notes:</strong><br>
                    ${notification.metadata.notes}
                </div>
            ` : ''}
        </div>
    `;

    document.body.appendChild(toast);

    // Close button
    toast.querySelector('.toast-close').addEventListener('click', function() {
        toast.remove();
    });

    // Auto-remove after 10 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 10000);
}

// Check for new HM decision notifications periodically
function checkForNewHMNotifications() {
    const recruiterId = <?php echo $recruiter_id ?? 'null'; ?>;

    if (!recruiterId) return;

    $.ajax({
        url: '<?php echo site_url("recruiter/dashboard/get_hm_notifications"); ?>',
        type: 'GET',
        data: {
            recruiter_id: recruiterId
        },
        success: function(response) {
            if (response.success && response.notifications.length > 0) {
                response.notifications.forEach(notification => {
                    showHMDecisionToast(notification);

                    // Mark as read after showing
                    markAsRead(notification.id);
                });
            }
        }
    });
}

// Check every 30 seconds for new notifications
setInterval(checkForNewHMNotifications, 30000);

// Also check when page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(checkForNewHMNotifications, 2000);
});
</script>