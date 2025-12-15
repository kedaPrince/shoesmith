<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
:root {
    --bg-main: #0f172a;
    --card-bg: #1e293b;
    --primary: #3b82f6;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #06b6d4;
    --text-main: #f1f5f9;
    --text-muted: #94a3b8;
    --border: #334155;
}

#main-content {
    min-height: 100vh;
    padding: 32px;
    background: var(--bg-main);
    color: var(--text-main);
}

/* Header */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--border);
}

.dashboard-header h1 {
    font-size: 32px;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0;
}

.dashboard-header p {
    margin: 8px 0 0;
    color: var(--text-muted);
    font-size: 16px;
}

/* Toggle Button */
.dashboard-toggle {
    display: flex;
    justify-content: center;
    margin: 20px 0 30px;
    z-index: 100;
}

.toggle-switch {
    display: flex;
    gap: 10px;
    background: var(--card-bg);
    padding: 5px;
    border-radius: 12px;
    border: 1px solid var(--border);
    width: 99px;
    height: 3.5rem;
}

.toggle-btn {
    padding: 12px 24px;
    border: none;
    background: transparent;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 8px;
}

.toggle-btn.active {
    background: var(--primary);
    color: white;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

/* KPI Cards */
.kpi-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.kpi-card {
    background: linear-gradient(145deg, var(--card-bg) 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 25px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-top: 4px solid var(--primary);
    cursor: pointer;
    border: 1px solid var(--border);
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
    color: var(--danger);
}

/* Dashboard Sections */
.dashboard-section {
    background: var(--card-bg);
    border-radius: 16px;
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
    display: flex;
    align-items: center;
    gap: 8px;
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
    background: rgba(255, 255, 255, 0.05);
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
    background: rgba(255, 255, 255, 0.05);
}

/* Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
    color: white;
    border-radius: 16px;
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
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    border: 1px solid var(--border);
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

/* Tasks List */
.tasks-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.task-item {
    padding: 15px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 15px;
}

.task-item:last-child {
    border-bottom: none;
}

.task-item:hover {
    background: rgba(255, 255, 255, 0.05);
}

.task-priority {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.task-priority.high {
    background: var(--danger);
}

.task-priority.medium {
    background: var(--warning);
}

.task-priority.low {
    background: var(--success);
}

.task-content {
    flex: 1;
}

.task-title {
    font-weight: 600;
    color: var(--text-main);
    margin-bottom: 5px;
}

.task-description {
    font-size: 13px;
    color: var(--text-muted);
}

.task-actions {
    display: flex;
    gap: 10px;
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

/* Badges */
.badge {
    display: inline-block;
    padding: 4px 8px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 4px;
    text-transform: uppercase;
}

.badge-success {
    background: rgba(16, 185, 129, 0.2);
    color: var(--success);
}

.badge-danger {
    background: rgba(239, 68, 68, 0.2);
    color: var(--danger);
}

.badge-warning {
    background: rgba(245, 158, 11, 0.2);
    color: var(--warning);
}

.badge-info {
    background: rgba(6, 182, 212, 0.2);
    color: var(--info);
}

.badge-secondary {
    background: rgba(148, 163, 184, 0.2);
    color: var(--text-muted);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 20px;
    opacity: 0.5;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="main-content">
    <div class="container-fluid">
        <!-- Dashboard Toggle Button -->
        <div class="dashboard-toggle">
            <div class="toggle-switch">
                <button class="toggle-btn" onclick="location.href='<?= site_url("agency/dashboard") ?>'">
                    <i class="fa fa-tachometer-alt"></i> Main
                </button>
                <button class="toggle-btn active">
                    <i class="fa fa-chart-bar"></i> Advanced View
                </button>
            </div>
        </div>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Welcome back, <?= htmlspecialchars($agency_name ?? 'Agency Manager', ENT_QUOTES, 'UTF-8') ?>!</h2>
                <p>Advanced analytics dashboard with detailed insights</p>
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
                    <h3><i class="fa fa-history"></i> Recent Submissions</h3>
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
                    <div class="empty-state">
                        <i class="fa fa-users"></i>
                        <p>No recent submissions</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Status Breakdown -->
                <div class="dashboard-section">
                    <h3><i class="fa fa-chart-pie"></i> Candidate Status Breakdown</h3>
                    <div class="status-progress">
                        <?php if (!empty($stats->status_counts)): 
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
                        <?php endif; endforeach; else: ?>
                        <div class="empty-state">
                            <i class="fa fa-chart-pie"></i>
                            <p>No status data available</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Tasks & Quick Stats -->
            <div class="col-lg-4">
                <!-- Upcoming Tasks -->
                <div class="dashboard-section">
                    <h3><i class="fa fa-tasks"></i> Upcoming Tasks</h3>
                    <?php if (!empty($upcoming_tasks)): ?>
                    <ul class="tasks-list">
                        <?php foreach ($upcoming_tasks as $task): ?>
                        <li class="task-item">
                            <div class="task-priority <?= $task['priority'] ?? 'medium' ?>"></div>
                            <div class="task-content">
                                <div class="task-title">
                                    <?= htmlspecialchars($task['title'] ?? 'Task', ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="task-description">
                                    <?= htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if (!empty($task['candidate_name'])): ?>
                                <small class="text-muted">Candidate:
                                    <?= htmlspecialchars($task['candidate_name'], ENT_QUOTES, 'UTF-8') ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="task-actions">
                                <?php if (($task['type'] ?? '') == 'documents_request' && !empty($task['candidate_id'])): ?>
                                <a href="<?= site_url('agency/candidates/view/' . $task['candidate_id']) ?>"
                                    class="btn btn-sm btn-outline-primary" title="Review Documents">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fa fa-check-circle"></i>
                        <p>No pending tasks</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Stats -->
                <div class="dashboard-section">
                    <h3><i class="fa fa-bolt"></i> Quick Stats</h3>
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
                            <div class="stat-box-value">
                                <?= ($stats->onboarding_total ?? 0) - ($stats->onboarding_completed ?? 0) ?></div>
                            <div class="stat-box-label">In Progress</div>
                        </div>
                    </div>
                </div>

                <!-- KPI Chart -->
                <div class="dashboard-section">
                    <h3><i class="fa fa-chart-pie"></i> Performance Overview</h3>
                    <canvas id="kpiChart" width="300" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle buttons
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        if (btn.textContent.includes('Advanced View')) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
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
    new Chart(ctx, {
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
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#06b6d4'],
                borderWidth: 2,
                borderColor: '#1e293b'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#f1f5f9'
                    }
                },
                tooltip: {
                    enabled: true
                }
            }
        }
    });

    // Add hover effects
    const style = document.createElement('style');
    style.textContent = `
        .bg-success { background: linear-gradient(90deg, #10b981 0%, #22c55e 100%); }
        .bg-danger { background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%); }
        .bg-warning { background: linear-gradient(90deg, #f59e0b 0%, #f97316 100%); }
        .bg-info { background: linear-gradient(90deg, #06b6d4 0%, #0ea5e9 100%); }
        .bg-secondary { background: linear-gradient(90deg, #94a3b8 0%, #64748b 100%); }
        .bg-primary { background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%); }
    `;
    document.head.appendChild(style);

    // Auto-refresh every 60 seconds
    setInterval(function() {
        fetch('<?= site_url("agency/dashboard/get_dashboard_stats") ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Advanced dashboard stats refreshed');
                }
            })
            .catch(error => console.error('Error refreshing stats:', error));
    }, 60000);
});
</script>