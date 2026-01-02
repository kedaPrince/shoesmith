<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

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
        <div class="stats">
            <h3><i class="fa fa-compass mr-2"></i> Candidate Onboarding Progress</h3>
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