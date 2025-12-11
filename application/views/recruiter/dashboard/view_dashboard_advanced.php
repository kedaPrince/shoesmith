<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
/* ===============================
   DARK SAAS DASHBOARD THEME
=============================== */
:root {
    --bg-page: #0f111a;
    --bg-card: #1a1c2a;
    --bg-card-soft: #1f2133;
    --border: #2c2f42;

    --primary: #3b82f6;
    --success: #22c55e;
    --warning: #f59e0b;
    --info: #17a2b8;

    --text-main: #e5e7eb;
    --text-muted: #9ca3af;

    --radius: 12px;
}

* {
    box-sizing: border-box;
    font-family: 'Inter', system-ui, sans-serif;
}

body {
    margin: 0;
    background: var(--bg-page);
    color: var(--text-main);
}

/* Toggle Button */
.dashboard-toggle {
    display: flex;
    justify-content: center;
    margin: 20px 0 50px;
    z-index: 100;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    /* width: 95px; */
    height: 1.5rem;
    margin: 0;
}



.toggle-btn {
    padding: 8px 16px;
    border: none;
    background: transparent;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--text-muted);
    width: 300px !important;
}

.toggle-btn.active {
    background: #59c4bc;
    color: #fff;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.toggle-btn i {
    margin-right: 5px;
}

/* KPI Cards */
.kpi-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.kpi-card {
    background: var(--bg-card);
    border-radius: var(--radius);
    padding: 25px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-top: 4px solid var(--primary);
    cursor: pointer;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
}

.kpi-card.primary {
    border-top-color: var(--primary);
}

.kpi-card.success {
    border-top-color: var(--success);
}

.kpi-card.warning {
    border-top-color: var(--warning);
}

.kpi-card.info {
    border-top-color: var(--info);
}

.kpi-value {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--text-main);
}

.kpi-label {
    font-size: 14px;
    color: var(--text-muted);
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
    color: var(--success);
}

.kpi-trend.down {
    color: #dc3545;
}

/* Dashboard Sections */
.dashboard-section {
    background: var(--bg-card);
    border-radius: var(--radius);
    padding: 25px;
    margin-bottom: 25px;
    border: 1px solid var(--border);
}

.dashboard-section h3 {
    color: var(--text-main);
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--border);
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
    color: var(--text-main);
}

.progress-bar-container {
    flex: 1;
    height: 20px;
    background: #2c2f42;
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
    color: var(--text-main);
}

/* Tables */
.recent-submissions-table {
    width: 100%;
    border-collapse: collapse;
}

.recent-submissions-table th {
    background: var(--bg-card-soft);
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    color: var(--text-main);
    border-bottom: 2px solid var(--border);
}

.recent-submissions-table td {
    padding: 12px 15px;
    border-bottom: 1px solid var(--border);
    color: var(--text-main);
}

.recent-submissions-table tr:hover {
    background: #2c2f42;
}

/* Welcome Banner */
.welcome-banner {
    background: #1f2133;
    color: var(--text-main);
    border-radius: var(--radius);
    padding: 25px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
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

/* Quick Stats */
.quick-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.stat-box {
    background: #2c2f42;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}

.stat-box-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 5px;
}

.stat-box-label {
    font-size: 12px;
    color: var(--text-muted);
    text-transform: uppercase;
    font-weight: 600;
}

/* Responsive */
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
        <!-- Dashboard Toggle Button -->
        <div class="dashboard-toggle">
            <div class="toggle-switch">
                <button class="toggle-btn active" onclick="window.location.href='<?= $toggle_url ?>'">
                    <i class="fa fa-chart-bar"></i> Advanced View
                </button>
                <button class="toggle-btn" onclick="window.location.href='<?= $toggle_url ?>'">
                    <i class="fa fa-list"></i> Simple View
                </button>
            </div>
        </div>

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
                <div class="kpi-trend up"><i class="fa fa-arrow-up"></i>New submissions</div>
            </div>
            <div class="kpi-card success">
                <div class="kpi-value"><?= $stats->status_counts['hired'] ?? 0 ?></div>
                <div class="kpi-label">Hired</div>
                <div class="kpi-trend">Success stories</div>
            </div>
            <div class="kpi-card info">
                <div class="kpi-value"><?= $stats->onboarding_rate ?? 0 ?>%</div>
                <div class="kpi-label">Onboarding Rate</div>
                <div class="kpi-trend">Completion rate</div>
            </div>
            <div class="kpi-card warning">
                <div class="kpi-value"><?= $stats->recent_activity ?? 0 ?></div>
                <div class="kpi-label">7-Day Activity</div>
                <div class="kpi-trend up"><i class="fa fa-chart-line"></i>Recent updates</div>
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
                                <td><strong><?= htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name, ENT_QUOTES, 'UTF-8') ?></strong><br>
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
                                    <span
                                        class="badge <?= $status_badge_class ?>"><?= ucfirst($candidate->status) ?></span>
                                </td>
                                <td><?= date('M j, Y', strtotime($candidate->created_at)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="text-center text-muted py-4"><i class="fa fa-users fa-2x mb-3"></i><br>No recent
                        submissions</div>
                    <?php endif; ?>
                </div>

                <!-- Status Breakdown -->
                <div class="dashboard-section">
                    <h3><i class="fa fa-chart-pie mr-2"></i> Candidate Status Breakdown</h3>
                    <div class="status-progress">
                        <?php if (!empty($stats->status_counts)):
                            $total_statuses = array_sum($stats->status_counts);
                            foreach ($stats->status_counts as $status => $count):
                                if ($count > 0):
                                    $percentage = $total_statuses > 0 ? round(($count / $total_statuses) * 100) : 0;
                                    $color_class = match($status) {
                                        'hired' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'interviewed' => 'bg-warning',
                                        'shortlisted' => 'bg-info',
                                        default => 'bg-secondary',
                                    };
                        ?>
                        <div class="progress-item">
                            <div class="progress-label"><?= ucfirst(str_replace('_',' ',$status)) ?></div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill <?= $color_class ?>" style="width: <?= $percentage ?>%">
                                </div>
                            </div>
                            <div class="progress-count"><?= $count ?></div>
                        </div>
                        <?php endif; endforeach; else: ?>
                        <div class="text-center text-muted py-3">No status data available</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: KPI Graph -->
            <div class="col-lg-4">
                <div class="dashboard-section">
                    <h3><i class="fa fa-chart-pie mr-2"></i> KPI</h3>
                    <canvas id="kpiChart" width="300" height="300"></canvas>
                </div>

                <div class="dashboard-section">
                    <h3><i class="fa fa-bolt mr-2"></i> Quick Stats</h3>
                    <div class="quick-stats">
                        <div class="stat-box">
                            <div class="stat-box-value"><?= $stats->hm_accepted ?? 0 ?></div>
                            <div class="stat-box-label">HM Accepted</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-value"><?= $stats->hm_rejected ?? 0 ?></div>
                            <div class="stat-box-label">HM Rejected</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-value"><?= $stats->onboarding_completed ?? 0 ?></div>
                            <div class="stat-box-label">Onboarding Done</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-value"><?= $stats->onboarding_total - $stats->onboarding_completed ?>
                            </div>
                            <div class="stat-box-label">In Progress</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle buttons
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.classList.toggle('active', btn.textContent.includes('Advanced View'));
    });

    // Animate progress bars
    document.querySelectorAll('.progress-bar-fill').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => bar.style.width = width, 500);
    });

    // KPI card click animation
    document.querySelectorAll('.kpi-card').forEach(card => {
        card.addEventListener('click', () => {
            card.style.transform = 'scale(0.98)';
            setTimeout(() => card.style.transform = '', 150);
        });
    });

    // KPI Chart
    const ctx = document.getElementById('kpiChart').getContext('2d');
    const kpiChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Hired', 'Interviewed', 'Rejected', 'Shortlisted'],
            datasets: [{
                data: [
                    <?= $stats->status_counts['hired'] ?? 0 ?>,
                    <?= $stats->status_counts['interviewed'] ?? 0 ?>,
                    <?= $stats->status_counts['rejected'] ?? 0 ?>,
                    <?= $stats->status_counts['shortlisted'] ?? 0 ?>
                ],
                backgroundColor: ['#22c55e', '#f59e0b', '#dc3545', '#17a2b8'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#e5e7eb'
                    }
                },
                tooltip: {
                    enabled: true
                }
            }
        }
    });

    // HM / Position Notifications
    const BASE_URL = '<?= site_url("") ?>';

    function getDisplayedNotifications() {
        const stored = sessionStorage.getItem('displayedHmNotifications');
        return stored ? new Set(JSON.parse(stored)) : new Set();
    }

    function saveDisplayedNotifications(set) {
        sessionStorage.setItem('displayedHmNotifications', JSON.stringify([...set]));
    }

    function checkHmNotifications() {
        fetch(BASE_URL + 'recruiter/dashboard/get_hm_decision_notifications', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.ok ? res.json() : Promise.reject(res))
            .then(data => {
                if (data.success && data.notifications.length) {
                    const displayed = getDisplayedNotifications();
                    data.notifications.forEach((n, i) => {
                        if (!displayed.has(n.id) && !document.getElementById('hmPopup-' + n.id)) {
                            displayed.add(n.id);
                            saveDisplayedNotifications(displayed);
                            setTimeout(() => showHmNotificationPopup(n), i * 1000);
                        }
                    });
                }
            }).catch(console.error);
    }

    function showHmNotificationPopup(notification) {
        if (document.getElementById('hmPopup-' + notification.id)) return;
        const decision = notification.metadata?.decision || 'pending';
        const popup = document.createElement('div');
        popup.className = `hm-decision-popup ${decision}`;
        popup.id = 'hmPopup-' + notification.id;
        popup.innerHTML = `
            <div class="popup-header">
                <h4 class="popup-title">${decision==='accepted'?'🎉 Candidate Accepted':'❌ Candidate Rejected'}</h4>
                <button class="popup-close">&times;</button>
            </div>
            <div class="popup-body">
                <div><strong>Candidate:</strong> ${notification.candidate_name || 'Unknown'}</div>
                <div><strong>Reference:</strong> ${notification.candidate_ref || 'N/A'}</div>
                <div><strong>Job:</strong> ${notification.job_name || 'Unknown'} (${notification.job_ref || 'N/A'})</div>
                ${notification.metadata?.notes?`<div><strong>Notes:</strong><br>${notification.metadata.notes}</div>`:''}
            </div>
            <div class="popup-footer">
                <button class="btn btn-sm btn-light">Close</button>
                <a href="<?= site_url('recruiter/candidates/view/') ?>${notification.related_entity_id}" class="btn btn-sm btn-primary" target="_blank">View Candidate</a>
            </div>
        `;
        popup.querySelectorAll('.popup-close, .btn-light').forEach(btn => btn.addEventListener('click', () =>
            closeHmPopup(notification.id)));
        document.body.appendChild(popup);
    }

    function closeHmPopup(id) {
        const popup = document.getElementById('hmPopup-' + id);
        if (!popup) return;
        popup.style.transition = 'all 0.3s ease';
        popup.style.opacity = '0';
        popup.style.transform = 'translateX(100%)';
        setTimeout(() => popup.remove(), 300);
    }

    setTimeout(checkHmNotifications, 2000);
    setInterval(checkHmNotifications, 30000);
});
</script>