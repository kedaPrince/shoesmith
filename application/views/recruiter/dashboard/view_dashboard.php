<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<section class="dashboard-container">
    <div>
        <h1>Welcome recruiter</h1>
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

<style>
.hm-decision-popup {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 99999;
    min-width: 400px;
    max-width: 500px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    animation: slideInRight 0.5s ease-out;
    border: none;
    font-family: Arial, sans-serif;
}

.hm-decision-popup.accepted {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.hm-decision-popup.rejected {
    background: linear-gradient(135deg, #dc3545, #e83e8c);
    color: white;
}

.hm-decision-popup .popup-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.hm-decision-popup .popup-title {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.hm-decision-popup .popup-close {
    background: none;
    border: none;
    color: inherit;
    font-size: 20px;
    cursor: pointer;
    opacity: 0.8;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hm-decision-popup .popup-close:hover {
    opacity: 1;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
}

.hm-decision-popup .popup-body {
    padding: 15px 20px;
}

.hm-decision-popup .candidate-info {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 12px;
    margin: 10px 0;
}

.hm-decision-popup .candidate-detail {
    margin: 5px 0;
    font-size: 14px;
}

.hm-decision-popup .hm-notes {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 6px;
    padding: 10px;
    margin-top: 10px;
    font-style: italic;
    font-size: 13px;
}

.hm-decision-popup .popup-footer {
    padding: 10px 20px 15px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.hm-decision-popup .popup-footer .btn {
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 12px;
    cursor: pointer;
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
</style>

<script>
// HM Decision Notifications - Cleaned Version
console.log('🚀 Dashboard JavaScript loaded - Cleaned Version');

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
    console.log('🔔 Checking for HM notifications...');

    const url = BASE_URL + 'recruiter/dashboard/get_hm_decision_notifications';
    console.log(`🔍 Fetching from: ${url}`);

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
            console.log('📨 HM notifications response:', data);

            if (data.success && Array.isArray(data.notifications) && data.notifications.length > 0) {
                console.log('🎯 Found ' + data.notifications.length + ' HM notifications');

                const displayedNotifications = getDisplayedNotifications();

                // Filter out already displayed notifications
                const newNotifications = data.notifications.filter(notification => {
                    const alreadyDisplayed = displayedNotifications.has(notification.id) ||
                        document.getElementById('hmPopup-' + notification.id);
                    return !alreadyDisplayed;
                });

                console.log('🆕 New notifications to display:', newNotifications.length);

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
                console.log('❌ No HM notifications found');
            }
        })
        .catch(error => {
            console.error('💥 Error checking HM notifications:', error);
        });
}

function showHmNotificationPopup(notification) {
    console.log('🔄 Showing HM notification popup:', notification);

    // Check if popup already exists for this notification
    if (document.getElementById('hmPopup-' + notification.id)) {
        console.log('ℹ️ Popup already exists for notification:', notification.id);
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
    console.log('🗑️ Closing popup:', notificationId);
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
    console.log('📖 Marking notification as read:', notificationId);

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
                console.log('✅ HM notification marked as read:', notificationId);
                // Remove from displayed notifications when marked as read
                const displayedNotifications = getDisplayedNotifications();
                displayedNotifications.delete(notificationId);
                saveDisplayedNotifications(displayedNotifications);
            } else {
                console.log('❌ Failed to mark notification as read');
            }
        })
        .catch(error => {
            console.error('💥 Error marking HM notification as read:', error);
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
        title: "🎉 Test Candidate Accepted",
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
    console.log('🏠 Dashboard loaded, checking for HM notifications...');

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