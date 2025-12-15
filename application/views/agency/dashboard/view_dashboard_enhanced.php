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

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(145deg, var(--card-bg) 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 24px;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    border-color: var(--primary);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary) 0%, var(--info) 100%);
}

.stat-card.success::before {
    background: linear-gradient(90deg, var(--success) 0%, #22c55e 100%);
}

.stat-card.warning::before {
    background: linear-gradient(90deg, var(--warning) 0%, #f97316 100%);
}

.stat-card.danger::before {
    background: linear-gradient(90deg, var(--danger) 0%, #dc2626 100%);
}

.stat-title {
    font-size: 14px;
    color: var(--text-muted);
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.stat-value {
    font-size: 36px;
    font-weight: 800;
    margin-bottom: 8px;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
}

.stat-trend.up {
    color: var(--success);
}

.stat-trend.down {
    color: var(--danger);
}

/* Charts Container */
.charts-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    margin-bottom: 40px;
}

@media (max-width: 1200px) {
    .charts-container {
        grid-template-columns: 1fr;
    }
}

.chart-card {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 24px;
    border: 1px solid var(--border);
}

.chart-card h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--text-main);
}

.chart-card canvas {
    width: 100% !important;
    max-height: 300px;
}

/* Recent Activity */
.recent-activity {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 24px;
    border: 1px solid var(--border);
    margin-bottom: 40px;
}

.recent-activity h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--text-main);
}

.activity-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border-bottom: 1px solid var(--border);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    background: rgba(255, 255, 255, 0.05);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.activity-icon.success {
    background: rgba(16, 185, 129, 0.2);
    color: var(--success);
}

.activity-icon.warning {
    background: rgba(245, 158, 11, 0.2);
    color: var(--warning);
}

.activity-icon.primary {
    background: rgba(59, 130, 246, 0.2);
    color: var(--primary);
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    margin-bottom: 4px;
}

.activity-time {
    font-size: 12px;
    color: var(--text-muted);
}

/* Navigation Grid */
.nav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.nav-card {
    border-radius: 16px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    color: white;
    position: relative;
    overflow: hidden;
    border: none;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
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
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
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
    border-radius: 14px;
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

/* Buttons */
.toggle-view-btn {
    background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.toggle-view-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(59, 130, 246, 0.3);
}

/* Stats Overview */
.stats-overview {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 30px;
}

.stat-overview-item {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    border: 1px solid var(--border);
}

.stat-overview-value {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-overview-label {
    font-size: 12px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
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

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .stats-overview {
        grid-template-columns: repeat(2, 1fr);
    }

    .nav-grid {
        grid-template-columns: 1fr;
    }

    .nav-card {
        padding: 20px;
    }

    .nav-icon {
        width: 50px;
        height: 50px;
        font-size: 22px;
    }
}

@media (max-width: 480px) {
    .stats-overview {
        grid-template-columns: 1fr;
    }
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
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div>
                <h1>Welcome back, <?= htmlspecialchars($agency_name ?? 'Agency Manager', ENT_QUOTES, 'UTF-8') ?></h1>
                <p>Your agency performance dashboard</p>
            </div>

            <button class="toggle-view-btn" onclick="location.href='<?= site_url("agency/dashboard/advanced") ?>'">
                <i class="fa fa-chart-bar"></i> Advanced Analytics
            </button>
        </div>

        <!-- Quick Stats Overview -->
        <div class="stats-overview">
            <div class="stat-overview-item">
                <div class="stat-overview-value"><?= $stats->awaiting_review ?? 0 ?></div>
                <div class="stat-overview-label">Awaiting Review</div>
            </div>
            <div class="stat-overview-item">
                <div class="stat-overview-value"><?= $stats->in_interview ?? 0 ?></div>
                <div class="stat-overview-label">In Interview</div>
            </div>
            <div class="stat-overview-item">
                <div class="stat-overview-value"><?= $stats->hm_accepted ?? 0 ?></div>
                <div class="stat-overview-label">HM Accepted</div>
            </div>
            <div class="stat-overview-item">
                <div class="stat-overview-value"><?= $stats->recent_candidates ?? 0 ?></div>
                <div class="stat-overview-label">Last 7 Days</div>
            </div>
        </div>

        <!-- KPI Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Total Candidates</div>
                <div class="stat-value"><?= $stats->total_candidates ?? 0 ?></div>
                <div class="stat-trend up">
                    <i class="fa fa-arrow-up"></i>
                    <span>+<?= $stats->recent_candidates ?? 0 ?> this week</span>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-title">Active Jobs</div>
                <div class="stat-value"><?= $stats->active_jobs ?? 0 ?></div>
                <div class="stat-trend up">
                    <i class="fa fa-briefcase"></i>
                    <span>Open positions</span>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-title">Onboarding Rate</div>
                <div class="stat-value"><?= $stats->onboarding_rate ?? 0 ?>%</div>
                <div class="stat-trend">
                    <span>Completion progress</span>
                </div>
            </div>

            <div class="stat-card danger">
                <div class="stat-title">Time to Hire</div>
                <div class="stat-value"><?= $stats->avg_time_to_hire ?? 0 ?> days</div>
                <div class="stat-trend">
                    <span>Average duration</span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-container">
            <div class="chart-card">
                <h3><i class="fa fa-chart-pie mr-2"></i> Candidate Status Distribution</h3>
                <canvas id="statusChart"></canvas>
            </div>

            <div class="chart-card">
                <h3><i class="fa fa-chart-bar mr-2"></i> Onboarding Progress</h3>
                <canvas id="onboardingChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <h3><i class="fa fa-history mr-2"></i> Recent Activity</h3>
            <?php if (!empty($recent_activity)): ?>
            <ul class="activity-list">
                <?php foreach ($recent_activity as $activity): ?>
                <li class="activity-item">
                    <div class="activity-icon <?= $activity['type'] ?? 'primary' ?>">
                        <?php if (($activity['type'] ?? '') == 'candidate_added'): ?>
                        <i class="fa fa-user-plus"></i>
                        <?php elseif (($activity['type'] ?? '') == 'job_posted'): ?>
                        <i class="fa fa-briefcase"></i>
                        <?php elseif (($activity['type'] ?? '') == 'candidate_hired'): ?>
                        <i class="fa fa-trophy"></i>
                        <?php else: ?>
                        <i class="fa fa-bell"></i>
                        <?php endif; ?>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">
                            <?= htmlspecialchars($activity['title'] ?? 'Activity', ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="activity-time">
                            <?= date('M j, Y g:i A', strtotime($activity['created_at'] ?? 'now')) ?></div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-history"></i>
                <p>No recent activity</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Navigation -->
        <div class="chart-card">
            <h3><i class="fa fa-compass mr-2"></i> Quick Navigation</h3>
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
</div>

<script>
// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hired', 'Interviewed', 'Shortlisted', 'Rejected', 'New'],
            datasets: [{
                data: [
                    <?= $stats->status_counts['hired'] ?? 0 ?>,
                    <?= $stats->status_counts['interviewed'] ?? 0 ?>,
                    <?= $stats->status_counts['shortlisted'] ?? 0 ?>,
                    <?= $stats->status_counts['rejected'] ?? 0 ?>,
                    <?= $stats->status_counts['new'] ?? 0 ?>
                ],
                backgroundColor: [
                    '#10b981',
                    '#f59e0b',
                    '#3b82f6',
                    '#ef4444',
                    '#94a3b8'
                ],
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
                        color: '#f1f5f9',
                        padding: 20
                    }
                }
            }
        }
    });

    // Onboarding Progress Chart
    const onboardingCtx = document.getElementById('onboardingChart').getContext('2d');
    new Chart(onboardingCtx, {
        type: 'bar',
        data: {
            labels: ['Not Started', 'Under Review', 'Submitted to HM', 'Requested Docs',
                'Position Offered', 'Completed'
            ],
            datasets: [{
                label: 'Candidates',
                data: [
                    <?= $stats->onboarding_counts['not_started'] ?? 0 ?>,
                    <?= $stats->onboarding_counts['under_review'] ?? 0 ?>,
                    <?= $stats->onboarding_counts['submitted_to_hm'] ?? 0 ?>,
                    <?= $stats->onboarding_counts['requested_docs'] ?? 0 ?>,
                    <?= $stats->onboarding_counts['position_offered'] ?? 0 ?>,
                    <?= $stats->onboarding_counts['completed'] ?? 0 ?>
                ],
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: '#3b82f6',
                borderWidth: 2
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
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#94a3b8'
                    },
                    grid: {
                        color: '#334155'
                    }
                },
                x: {
                    ticks: {
                        color: '#94a3b8',
                        maxRotation: 45
                    },
                    grid: {
                        color: '#334155'
                    }
                }
            }
        }
    });

    // Add hover effects to stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Add loading animation for navigation cards
    document.querySelectorAll('.nav-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.style.opacity = '0';
        card.style.animation = 'fadeInUp 0.5s ease forwards';
    });

    // Define the animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);

    // Auto-refresh stats every 60 seconds
    setInterval(function() {
        fetch('<?= site_url("agency/dashboard/get_dashboard_stats") ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Stats refreshed');
                }
            })
            .catch(error => console.error('Error refreshing stats:', error));
    }, 60000);
});
</script>