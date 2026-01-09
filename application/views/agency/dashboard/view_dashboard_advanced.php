<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="main-content">
    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div></div>
            <button class="toggle-view-btn" onclick="location.href='<?= site_url("agency/dashboard") ?>'">
                <i class="fa fa-chart-bar"></i> Main Dashboard
            </button>
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