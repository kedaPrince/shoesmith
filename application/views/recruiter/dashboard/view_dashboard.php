<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
:root {
    --bg-main: #f8fafc;
    --card-bg: #ffffff;
    --primary: #2563eb;
    --success: #16a34a;
    --text-main: #0f172a;
    --text-muted: #64748b;
    --border: #e5e7eb;
}

#main-content {
    min-height: 100vh;
    padding: 32px;
    color: #ffffff;
}

.card {
    border-radius: 14px;
    border: 1px solid var(--border);
    padding: 24px;
    background: linear-gradient(49deg, #59c4bc5e -60%, #010e1a1a 55%) !important;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.dashboard-header h1 {
    font-size: 28px;
    font-weight: 700;
    margin: 0;
}

.dashboard-header p {
    margin: 6px 0 0;
    color: var(--text-muted);
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    margin-bottom: 35px;
}

.stat-title {
    font-size: 14px;
    color: var(--text-muted);
    margin-bottom: 10px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
}

.stat-footer {
    margin-top: 10px;
    font-size: 13px;
    color: var(--success);
}

/* Chart */
canvas {
    width: 100% !important;
    height: 70px !important;
}

/* Navigation Grid with Gradients */
.nav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 18px;
}

.nav-card {
    border-radius: 14px;
    padding: 22px;
    display: flex;
    align-items: center;
    gap: 18px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    color: white;
    position: relative;
    overflow: hidden;
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.nav-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.nav-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
    color: white;
}

.nav-card:hover::before {
    opacity: 1;
}

.nav-card:hover .nav-icon {
    transform: scale(1.1) rotate(5deg);
}

.nav-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 1;
}

.nav-text {
    position: relative;
    z-index: 1;
}

.nav-text strong {
    font-size: 16px;
    font-weight: 600;
    display: block;
    margin-bottom: 4px;
}

.nav-text span {
    font-size: 13px;
    opacity: 0.9;
    font-weight: 400;
}

/* Gradient backgrounds for navigation items */
.nav-card:nth-child(1) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.nav-card:nth-child(2) {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.nav-card:nth-child(3) {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.nav-card:nth-child(4) {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.nav-card:nth-child(5) {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.nav-card:nth-child(6) {
    background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
}

.nav-card:nth-child(7) {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
}

.nav-card:nth-child(8) {
    background: linear-gradient(135deg, #5ee7df 0%, #b490ca 100%);
}

.nav-card:nth-child(9) {
    background: linear-gradient(135deg, #d299c2 0%, #fef9d7 100%);
}

.nav-card:nth-child(10) {
    background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%);
}

.nav-card:nth-child(11) {
    background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
}

.nav-card:nth-child(12) {
    background: linear-gradient(135deg, #a3bded 0%, #6991c7 100%);
}

/* Additional gradient patterns for more items */
.nav-card:nth-child(13) {
    background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
}

.nav-card:nth-child(14) {
    background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
}

.nav-card:nth-child(15) {
    background: linear-gradient(135deg, #fad0c4 0%, #ffd1ff 100%);
}

.nav-card:nth-child(16) {
    background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
}

/* Button */
.btn-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    #main-content {
        padding: 20px;
    }

    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }

    .nav-grid {
        grid-template-columns: 1fr;
    }

    .nav-card {
        padding: 18px;
    }

    .nav-icon {
        width: 50px;
        height: 50px;
        font-size: 22px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="main-content">
    <div class="container-fluid">

        <div class="dashboard-header">
            <div>
                <h1>Welcome back, <?= htmlspecialchars($recruiter_details->first_name ?? 'Recruiter') ?></h1>
                <p>Here's what's happening with your recruitment pipeline</p>
            </div>

            <button class="btn btn-primary"
                onclick="location.href='<?= site_url("recruiter/dashboard/toggle_view") ?>'">
                Advanced View
            </button>
        </div>

        <div class="stats-grid">

            <div class="card">
                <div class="stat-title">Total Candidates</div>
                <div class="stat-value"><?= $stats->total_candidates ?? 0 ?></div>
                <canvas id="candidatesChart"></canvas>
                <div class="stat-footer">▲ Pipeline growth</div>
            </div>

            <div class="card">
                <div class="stat-title">Hired Candidates</div>
                <div class="stat-value"><?= $stats->status_counts['hired'] ?? 0 ?></div>
                <canvas id="hiredChart"></canvas>
                <div class="stat-footer">▲ Successful placements</div>
            </div>

            <div class="card">
                <div class="stat-title">Active Jobs</div>
                <div class="stat-value"><?= $stats->active_jobs ?? 0 ?></div>
                <canvas id="jobsChart"></canvas>
                <div class="stat-footer">● Currently hiring</div>
            </div>

        </div>

        <!-- Navigation with Gradients -->
        <div class="nav-grid">
            <?php 
            $navCounter = 0;
            foreach ($this->siteMap as $group): 
                if (!$group->show) continue;

                if (isset($group->items)): 
                    foreach ($group->items as $item): 
                        if (!$item->show) continue;
                        $navCounter++;
            ?>
            <a href="<?= $item->url ?>" class="nav-card">
                <div class="nav-icon">
                    <i class="fa <?= $item->icon ?>"></i>
                </div>
                <div class="nav-text">
                    <strong><?= $item->label ?></strong>
                    <span>Manage <?= strtolower($item->label) ?></span>
                </div>
            </a>
            <?php 
                    endforeach; 
                else: 
                    $navCounter++;
            ?>
            <a href="<?= $group->url ?>" class="nav-card">
                <div class="nav-icon">
                    <i class="fa <?= $group->icon ?>"></i>
                </div>
                <div class="nav-text">
                    <strong><?= $group->label ?></strong>
                    <span>Manage <?= strtolower($group->label) ?></span>
                </div>
            </a>
            <?php 
                endif; 
            endforeach; 
            ?>
        </div>

    </div>
</div>
// In recruiter dashboard template
<script>
// Update activity every 30 seconds
setInterval(function() {
    fetch('/recruiter/update_activity', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
}, 30000);

// Also update on page interactions
document.addEventListener('click', function() {
    fetch('/recruiter/update_activity', {
        method: 'POST'
    });
});

document.addEventListener('keypress', function() {
    fetch('/recruiter/update_activity', {
        method: 'POST'
    });
});
</script>
<script>
// Update toggle button active state
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-btn');
    toggleButtons.forEach(btn => {
        if (btn.textContent.includes('Simple View')) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
});

// HM Decision Notifications - EXACTLY as they were
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

<script>
function sparkline(el, data, color) {
    new Chart(document.getElementById(el), {
        type: 'line',
        data: {
            labels: data.map((_, i) => i),
            datasets: [{
                data,
                borderColor: color,
                tension: .4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    display: false
                },
                y: {
                    display: false
                }
            },
        }
    });
}

// Example data – replace later with real stats
sparkline('candidatesChart', [2, 5, 8, 12, 15, 22], '#2563eb');
sparkline('hiredChart', [1, 2, 3, 5, 8], '#16a34a');
sparkline('jobsChart', [3, 4, 6, 6, 7], '#0ea5e9');
</script>