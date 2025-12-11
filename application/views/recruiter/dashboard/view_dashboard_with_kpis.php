<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
/* KPI Cards Styling */
.kpi-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.kpi-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-top: 4px solid #007bff;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.kpi-card.primary {
    border-top-color: #007bff;
}

.kpi-card.success {
    border-top-color: #28a745;
}

.kpi-card.warning {
    border-top-color: #ffc107;
}

.kpi-card.info {
    border-top-color: #17a2b8;
}

.kpi-value {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 10px;
    color: #333;
}

.kpi-label {
    font-size: 14px;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kpi-trend {
    font-size: 12px;
    margin-top: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.kpi-trend.up {
    color: #28a745;
}

.kpi-trend.down {
    color: #dc3545;
}

/* Dashboard Sections */
.dashboard-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 25px;
}

.dashboard-section h3 {
    color: #333;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

/* Status Progress Bars */
.status-progress {
    margin-top: 20px;
}

.progress-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.progress-label {
    width: 120px;
    font-size: 14px;
    color: #495057;
}

.progress-bar-container {
    flex: 1;
    height: 20px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    margin: 0 15px;
}

.progress-bar-fill {
    height: 100%;
    border-radius: 10px;
    transition: width 1s ease;
}

.progress-count {
    width: 50px;
    text-align: right;
    font-weight: 600;
    font-size: 14px;
}

/* Recent Submissions Table */
.recent-submissions-table {
    width: 100%;
    border-collapse: collapse;
}

.recent-submissions-table th {
    background: #f8f9fa;
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #e9ecef;
}

.recent-submissions-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #e9ecef;
}

.recent-submissions-table tr:hover {
    background: #f8f9fa;
}

/* Tasks List */
.tasks-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.task-item {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.task-item:last-child {
    border-bottom: none;
}

.task-item:hover {
    background: #f8f9fa;
}

.task-priority {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.task-priority.high {
    background: #dc3545;
}

.task-priority.medium {
    background: #ffc107;
}

.task-priority.low {
    background: #28a745;
}

.task-content {
    flex: 1;
}

.task-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.task-description {
    font-size: 13px;
    color: #6c757d;
}

.task-actions {
    display: flex;
    gap: 10px;
}

/* Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.welcome-text h2 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
}

.welcome-text p {
    opacity: 0.9;
    margin: 0;
}

.welcome-stats {
    display: flex;
    gap: 20px;
}

.welcome-stat {
    text-align: center;
}

.welcome-stat-value {
    font-size: 24px;
    font-weight: 700;
}

.welcome-stat-label {
    font-size: 12px;
    opacity: 0.8;
    text-transform: uppercase;
}

/* Responsive Design */
@media (max-width: 768px) {
    .kpi-container {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }

    .welcome-banner {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }

    .welcome-stats {
        flex-wrap: wrap;
        justify-content: center;
    }

    .progress-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .progress-bar-container {
        width: 100%;
        margin: 0;
    }
}
</style>

<div id="main-content">
    <div class="container-fluid">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Welcome back,
                    <?= htmlspecialchars($recruiter_details->first_name ?? 'Recruiter', ENT_QUOTES, 'UTF-8') ?>!</h2>
                <p>Here's your recruitment performance overview</p>
            </div>
            <div class="welcome-stats">
                <div class="welcome-stat">
                    <div class="welcome-stat-value"><?= $stats->total_candidates ?? 0 ?></div>
                    <div class="welcome-stat-label">Total Candidates</div>
                </div>
                <div class="welcome-stat">
                    <div class="welcome-stat-value"><?= $stats->active_jobs ?? 0 ?></div>
                    <div class="welcome-stat-label">Active Jobs</div>
                </div>
                <div class="welcome-stat">
                    <div class="welcome-stat-value"><?= $stats->success_rate ?? 0 ?>%</div>
                    <div class="welcome-stat-label">Success Rate</div>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-container">
            <div class="kpi-card primary">
                <div class="kpi-value"><?= $stats->candidates_this_month ?? 0 ?></div>
                <div class="kpi-label">This Month</div>
                <div class="kpi-trend up">
                    <i class="fa fa-arrow-up"></i>
                    <span>New submissions</span>
                </div>
            </div>

            <div class="kpi-card success">
                <div class="kpi-value"><?= $stats->status_counts['hired'] ?? 0 ?></div>
                <div class="kpi-label">Hired</div>
                <div class="kpi-trend">
                    <span>Success stories</span>
                </div>
            </div>

            <div class="kpi-card info">
                <div class="kpi-value"><?= $stats->onboarding_rate ?? 0 ?>%</div>
                <div class="kpi-label">Onboarding Rate</div>
                <div class="kpi-trend">
                    <span>Completion rate</span>
                </div>
            </div>

            <div class="kpi-card warning">
                <div class="kpi-value"><?= $stats->recent_activity ?? 0 ?></div>
                <div class="kpi-label">7-Day Activity</div>
                <div class="kpi-trend up">
                    <i class="fa fa-chart-line"></i>
                    <span>Recent updates</span>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Recent Submissions & Status Breakdown -->
            <div class="col-lg-8">
                <!-- Recent Submissions -->
                <div class="dashboard-section">
                    <h3><i class="fa fa-history mr-2"></i> Recent Submissions</h3>
                    <?php if (!empty($recent_submissions)): ?>
                    <table class="recent-submissions-table">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Job</th>
                                <th>Status</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_submissions as $candidate): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name, ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <small class="text-muted"><?= $candidate->reference_number ?></small>
                                </td>
                                <td><?= $candidate->job_name ? htmlspecialchars($candidate->job_name, ENT_QUOTES, 'UTF-8') : 'Not assigned' ?>
                                </td>
                                <td>
                                    <?php 
                                    $status_badge_class = '';
                                    switch($candidate->status) {
                                        case 'hired': $status_badge_class = 'badge-success'; break;
                                        case 'rejected': $status_badge_class = 'badge-danger'; break;
                                        case 'interviewed': $status_badge_class = 'badge-warning'; break;
                                        case 'shortlisted': $status_badge_class = 'badge-info'; break;
                                        default: $status_badge_class = 'badge-secondary';
                                    }
                                    ?>
                                    <span class="badge <?= $status_badge_class ?>">
                                        <?= ucfirst($candidate->status) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($candidate->created_at)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fa fa-users fa-2x mb-3"></i><br>
                        No recent submissions
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Status Breakdown -->
                <div class="dashboard-section">
                    <h3><i class="fa fa-chart-pie mr-2"></i> Candidate Status Breakdown</h3>
                    <div class="status-progress">
                        <?php if (!empty($stats->status_counts)): ?>
                        <?php 
                            $total_statuses = array_sum($stats->status_counts);
                            foreach ($stats->status_counts as $status => $count):
                                if ($count > 0):
                                    $percentage = $total_statuses > 0 ? round(($count / $total_statuses) * 100) : 0;
                                    $color_class = '';
                                    switch($status) {
                                        case 'hired': $color_class = 'bg-success'; break;
                                        case 'rejected': $color_class = 'bg-danger'; break;
                                        case 'interviewed': $color_class = 'bg-warning'; break;
                                        case 'shortlisted': $color_class = 'bg-info'; break;
                                        default: $color_class = 'bg-secondary';
                                    }
                            ?>
                        <div class="progress-item">
                            <div class="progress-label"><?= ucfirst(str_replace('_', ' ', $status)) ?></div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill <?= $color_class ?>" style="width: <?= $percentage ?>%">
                                </div>
                            </div>
                            <div class="progress-count"><?= $count ?></div>
                        </div>
                        <?php endif; endforeach; ?>
                        <?php else: ?>
                        <div class="text-center text-muted py-3">No status data available</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Upcoming Tasks -->
            <div class="col-lg-4">
                <div class="dashboard-section">
                    <h3><i class="fa fa-tasks mr-2"></i> Upcoming Tasks</h3>
                    <?php if (!empty($upcoming_tasks)): ?>
                    <ul class="tasks-list">
                        <?php foreach ($upcoming_tasks as $task): ?>
                        <li class="task-item">
                            <div class="task-priority <?= $task['priority'] ?>"></div>
                            <div class="task-content">
                                <div class="task-title"><?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="task-description">
                                    <?= htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if (!empty($task['due_date'])): ?>
                                <small class="text-muted">Due: <?= date('M j', strtotime($task['due_date'])) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="task-actions">
                                <?php if ($task['type'] == 'documents_request'): ?>
                                <a href="<?= site_url('recruiter/candidates/view/' . $task['candidate_id']) ?>"
                                    class="btn btn-sm btn-outline-primary" title="Submit Documents">
                                    <i class="fa fa-upload"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fa fa-check-circle fa-2x mb-3"></i><br>
                        No pending tasks
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Stats -->
                <div class="dashboard-section">
                    <h3><i class="fa fa-bolt mr-2"></i> Quick Stats</h3>
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stat-value"><?= $stats->hm_accepted ?? 0 ?></div>
                            <div class="stat-label">HM Accepted</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-value"><?= $stats->hm_rejected ?? 0 ?></div>
                            <div class="stat-label">HM Rejected</div>
                        </div>
                        <div class="col-6">
                            <div class="stat-value"><?= $stats->onboarding_completed ?? 0 ?></div>
                            <div class="stat-label">Onboarding Done</div>
                        </div>
                        <div class="col-6">
                            <div class="stat-value"><?= $stats->onboarding_total - $stats->onboarding_completed ?></div>
                            <div class="stat-label">In Progress</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Section (Existing) -->
        <div class="dashboard-section mt-4">
            <h3><i class="fa fa-compass mr-2"></i> Quick Navigation</h3>
            <section class="dashboard-container">
                <div>
                    <h2>What would you like to see?</h2>
                    <ul>
                        <?php
                        foreach ($this->siteMap as $group) { 
                            if (!$group->show) {
                                continue;
                            }
                            
                            // Check if the group has sub-items
                            if (isset($group->items) && is_array($group->items)) {
                                foreach ($group->items as $subItem) {
                                    if (!$subItem->show || (isset($subItem->view) && $subItem->view == 'add')) {
                                        continue;
                                    }
                                    
                                    echo '
                                        <li>
                                            <a href="'.$subItem->url.'" title="'.$subItem->label.'">
                                                <i class="fa '.$subItem->icon.'" ></i>
                                                <span>'.$subItem->label.'</span>
                                            </a>
                                        </li>
                                    ';

                                    // Check for nested sub-items
                                    if (isset($subItem->items) && is_array($subItem->items)) {
                                        foreach ($subItem->items as $nestedSubItem) {
                                            if (!$nestedSubItem->show || (isset($nestedSubItem->view) && $nestedSubItem->view == 'add')) {
                                                continue;
                                            }
                                            
                                            echo '
                                                <li class="nested-sub-item">
                                                    <a href="'.$nestedSubItem->url.'" title="'.$nestedSubItem->label.'">
                                                        <i class="fa '.$nestedSubItem->icon.'" ></i>
                                                        <span>'.$nestedSubItem->label.'</span>
                                                    </a>
                                                </li>
                                            ';
                                        }
                                    }
                                }
                            } else {
                                echo '
                                    <li>
                                        <a href="'.$group->url.'" title="'.$group->label.'">
                                            <i class="fa '.$group->icon.'" ></i>
                                            <span>'.$group->label.'</span>
                                        </a>
                                    </li>
                                ';
                            }
                        }
                        ?>
                    </ul>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    document.querySelectorAll('.progress-bar-fill').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });

    // Add click animation to KPI cards
    document.querySelectorAll('.kpi-card').forEach(card => {
        card.addEventListener('click', function() {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
});
</script>
<script>
// HM Decision Notifications - Cleaned Version
console.log(' Dashboard JavaScript loaded - Cleaned Version');

// Base URL for API endpoints
const BASE_URL = '<?= site_url("") ?>';

// Track displayed notifications in sessionStorage to persist across refreshes
function getDisplayedNotifications() {
    const stored = sessionStorage.getItem('displayedHmNotifications');
    return stored ? new Set(JSON.parse(stored)) : new Set();
}

function saveDisplayedNotifications(displayedSet) {
    sessionStorage.setItem('displayedHmNotifications', JSON.stringify([...displayedSet]));
}

function checkHmNotifications() {
    console.log(' Checking for HM notifications...');

    const url = BASE_URL + 'recruiter/dashboard/get_hm_decision_notifications';
    console.log(` Fetching from: ${url}`);

    fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status} - ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log(' HM notifications response:', data);

            if (data.success && Array.isArray(data.notifications) && data.notifications.length > 0) {
                console.log(' Found ' + data.notifications.length + ' HM notifications');

                const displayedNotifications = getDisplayedNotifications();

                // Filter out already displayed notifications
                const newNotifications = data.notifications.filter(notification => {
                    const alreadyDisplayed = displayedNotifications.has(notification.id) ||
                        document.getElementById('hmPopup-' + notification.id);
                    return !alreadyDisplayed;
                });

                console.log(' New notifications to display:', newNotifications.length);

                newNotifications.forEach((notification, index) => {
                    // Add to displayed set immediately and save to sessionStorage
                    displayedNotifications.add(notification.id);
                    saveDisplayedNotifications(displayedNotifications);

                    // Show each notification with a slight delay
                    setTimeout(() => {
                        showHmNotificationPopup(notification);
                    }, index * 1000);
                });
            } else {
                console.log(' No HM notifications found');
            }
        })
        .catch(error => {
            console.error(' Error checking HM notifications:', error);
        });
}

function showHmNotificationPopup(notification) {
    console.log(' Showing HM notification popup:', notification);

    // Check if popup already exists for this notification
    if (document.getElementById('hmPopup-' + notification.id)) {
        console.log('ℹ Popup already exists for notification:', notification.id);
        return;
    }

    const decision = notification.metadata ? notification.metadata.decision : 'pending';
    const candidateName = notification.candidate_name || 'Unknown Candidate';
    const candidateRef = notification.candidate_ref || 'N/A';
    const jobName = notification.job_name || 'Unknown Job';
    const jobRef = notification.job_ref || 'N/A';
    const hmNotes = notification.metadata ? notification.metadata.notes : '';

    // Create popup element
    const popupElement = document.createElement('div');
    popupElement.className = `hm-decision-popup ${decision}`;
    popupElement.id = `hmPopup-${notification.id}`;

    popupElement.innerHTML = `
        <div class="popup-header">
            <h4 class="popup-title">
                ${decision === 'accepted' ? '🎉 Candidate Accepted' : '❌ Candidate Rejected'}
            </h4>
            <button class="popup-close">&times;</button>
        </div>
        <div class="popup-body">
            <div class="candidate-info">
                <div class="candidate-detail"><strong>Candidate:</strong> ${candidateName}</div>
                <div class="candidate-detail"><strong>Reference:</strong> ${candidateRef}</div>
                <div class="candidate-detail"><strong>Job:</strong> ${jobName} (${jobRef})</div>
            </div>
            ${hmNotes ? `
                <div class="hm-notes">
                    <strong>Hiring Manager Notes:</strong><br>
                    ${hmNotes}
                </div>
            ` : ''}
        </div>
        <div class="popup-footer">
            <button class="btn btn-sm btn-light">Close</button>
            <a href="<?= site_url('recruiter/candidates/view/') ?>${notification.related_entity_id}" class="btn btn-sm btn-primary" target="_blank">
                View Candidate
            </a>
        </div>
    `;

    // Add event listeners
    const closeButton = popupElement.querySelector('.popup-close');
    const closeBtn = popupElement.querySelector('.btn.btn-light');

    const closeHandler = () => {
        console.log('🗑️ Closing popup and marking as read:', notification.id);
        markHmNotificationAsRead(notification.id);
        closeHmPopup(notification.id);
    };

    closeButton.addEventListener('click', closeHandler);
    closeBtn.addEventListener('click', closeHandler);

    console.log('📝 Adding popup to DOM');
    // Add popup to body
    document.body.appendChild(popupElement);
    console.log('✅ Popup added');
}

function closeHmPopup(notificationId) {
    console.log(' Closing popup:', notificationId);
    const popup = document.getElementById('hmPopup-' + notificationId);
    if (popup) {
        // Add fade out animation
        popup.style.transition = 'all 0.3s ease';
        popup.style.opacity = '0';
        popup.style.transform = 'translateX(100%)';

        setTimeout(() => {
            if (popup.parentNode) {
                popup.parentNode.removeChild(popup);
            }
        }, 300);
    }
}

function markHmNotificationAsRead(notificationId) {
    console.log(' Marking notification as read:', notificationId);

    const url = BASE_URL + 'recruiter/dashboard/mark_hm_notification_read';

    const formData = new FormData();
    formData.append('notification_id', notificationId);
    formData.append('<?php echo $this->security->get_csrf_token_name(); ?>',
        '<?php echo $this->security->get_csrf_hash(); ?>');

    fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Mark read response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log(' HM notification marked as read:', notificationId);
                // Remove from displayed notifications when marked as read
                const displayedNotifications = getDisplayedNotifications();
                displayedNotifications.delete(notificationId);
                saveDisplayedNotifications(displayedNotifications);
            } else {
                console.log(' Failed to mark notification as read');
            }
        })
        .catch(error => {
            console.error(' Error marking HM notification as read:', error);
        });
}
// Add to your existing HM decision notification system
function checkForPositionOfferedNotifications() {
    const recruiterId = <?php echo $recruiter_id ?? 'null'; ?>;

    if (!recruiterId) return;

    $.ajax({
        url: '<?php echo site_url("recruiter/dashboard/get_position_offered_notifications"); ?>',
        type: 'GET',
        data: {
            recruiter_id: recruiterId
        },
        success: function(response) {
            if (response.success && response.notifications.length > 0) {
                response.notifications.forEach(notification => {
                    showPositionOfferedPopup(notification);
                    // Mark as read after showing
                    markAsRead(notification.id);
                });
            }
        }
    });
}

function showPositionOfferedPopup(notification) {
    const popupElement = document.createElement('div');
    popupElement.className = 'hm-decision-popup accepted';
    popupElement.id = 'positionPopup-' + notification.id;

    popupElement.innerHTML = `
        <div class="popup-header">
            <h4 class="popup-title">
                🎉 Position Offered!
            </h4>
            <button class="popup-close">&times;</button>
        </div>
        <div class="popup-body">
            <div class="candidate-info">
                <div class="candidate-detail"><strong>Candidate:</strong> ${notification.metadata?.candidate_name || 'N/A'}</div>
                <div class="candidate-detail"><strong>Job:</strong> ${notification.metadata?.job_name || 'N/A'}</div>
                <div class="candidate-detail"><strong>Offered By:</strong> ${notification.metadata?.offering_agency || 'Hiring Manager'}</div>
            </div>
            <div class="hm-notes">
                <strong>Next Step:</strong> ${notification.metadata?.action_required || 'Contact candidate to confirm acceptance'}
            </div>
        </div>
        <div class="popup-footer">
            <button class="btn btn-sm btn-light">Close</button>
            <a href="<?= site_url('recruiter/candidates/view/') ?>${notification.related_entity_id}" class="btn btn-sm btn-primary" target="_blank">
                View Candidate
            </a>
        </div>
    `;

    // Add event listeners
    const closeButton = popupElement.querySelector('.popup-close');
    const closeBtn = popupElement.querySelector('.btn.btn-light');

    const closeHandler = () => {
        closePositionPopup(notification.id);
    };

    closeButton.addEventListener('click', closeHandler);
    closeBtn.addEventListener('click', closeHandler);

    document.body.appendChild(popupElement);
}

function closePositionPopup(notificationId) {
    const popup = document.getElementById('positionPopup-' + notificationId);
    if (popup) {
        popup.style.transition = 'all 0.3s ease';
        popup.style.opacity = '0';
        popup.style.transform = 'translateX(100%)';

        setTimeout(() => {
            if (popup.parentNode) {
                popup.parentNode.removeChild(popup);
            }
        }, 300);
    }
}

// Add to your existing interval checks
setInterval(checkForPositionOfferedNotifications, 30000);
// Debug function to test popup manually - REMOVE AUTO-CALL
function testPopupManually() {
    console.log('Testing popup manually...');

    const testNotification = {
        id: 999,
        title: " Test Candidate Accepted",
        message: "Great news! The hiring manager has accepted Test Candidate for Test Job.",
        candidate_name: "Test Candidate",
        candidate_ref: "CAND-TEST-001",
        job_name: "Test Job",
        job_ref: "JOB-TEST-001",
        related_entity_id: 23,
        metadata: {
            decision: "accepted",
            notes: "This is a test notification"
        }
    };

    showHmNotificationPopup(testNotification);
}

// Single DOMContentLoaded event listener
document.addEventListener('DOMContentLoaded', function() {


    // Clear any existing test notifications from session storage on page load
    const displayedNotifications = getDisplayedNotifications();
    if (displayedNotifications.has(999)) {
        displayedNotifications.delete(999);
        saveDisplayedNotifications(displayedNotifications);
    }

    // Check for real notifications after 2 seconds
    setTimeout(checkHmNotifications, 2000);

    // Check for HM notifications every 30 seconds (reduced frequency)
    setInterval(checkHmNotifications, 30000);
});

// Debug: List all available endpoints
console.log('🔧 Debug Info:');
console.log('Base URL:', BASE_URL);
console.log('Current Path:', window.location.pathname);

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
});
</script>