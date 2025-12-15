<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
.onboarding-listing-container {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.onboarding-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    border-left: 4px solid #007bff;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #007bff;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: #6c757d;
    font-weight: 600;
}

.onboarding-table {
    width: 100%;
    border-collapse: collapse;
}

.onboarding-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #e9ecef;
}

.onboarding-table td {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}

.progress-cell {
    min-width: 200px;
}

.progress-bar-small {
    background: #f8f9fa;
    border-radius: 10px;
    height: 12px;
    overflow: hidden;
    position: relative;
}

.progress-fill-small {
    background: linear-gradient(90deg, #28a745, #20c997);
    height: 100%;
    border-radius: 10px;
    transition: all 0.8s ease;
}

.stage-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.stage-not-started {
    background: #6c757d;
    color: white;
}

.stage-under-review {
    background: #007bff;
    color: white;
}

.stage-submitted-to-hm {
    background: #17a2b8;
    color: white;
}

.stage-requested-docs {
    background: #ffc107;
    color: black;
}

.stage-position-offered {
    background: #fd7e14;
    color: white;
}

.stage-hm-decision {
    background: #6f42c1;
    color: white;
}

.stage-hm-accepted {
    background: #28a745;
    color: white;
}

.stage-hm-rejected {
    background: #dc3545;
    color: white;
}

.stage-completed {
    background: #28a745;
    color: white;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.stage-hm-decision {
    background: #6f42c1;
    color: white;
}

.stage-hm-accepted {
    background: #28a745;
    color: white;
}

.stage-hm-rejected {
    background: #dc3545;
    color: white;
}
</style>

<div id="main-content">
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Onboarding Management</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= site_url('agency/dashboard') ?>"><i class="fa fa-dashboard"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= redir('candidates', true) ?>">Candidates</a>
                        </li>
                        <li class="breadcrumb-item active">Onboarding</li>
                    </ul>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action">
                            <a href="<?= redir('candidates', true) ?>" class="btn btn-primary">
                                <i class="fa fa-users"></i> Back to Candidates
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="onboarding-listing-container">
                    <div class="card">
                        <div class="header">
                            <h2>Onboarding Progress Overview</h2>
                        </div>
                        <div class="body">
                            <!-- Statistics Cards -->
                            <div class="onboarding-stats">
                                <div class="stat-card">
                                    <div class="stat-number"><?= $stats->total_candidates ?? 0 ?></div>
                                    <div class="stat-label">Total Candidates</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number"><?= $stats->under_review_count ?? 0 ?></div>
                                    <div class="stat-label">Under Review</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number"><?= $stats->submitted_hm_count ?? 0 ?></div>
                                    <div class="stat-label">Submitted to HM</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number"><?= $stats->hm_decision_count ?? 0 ?></div>
                                    <div class="stat-label">HM Decision</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-number"><?= $stats->completed_count ?? 0 ?></div>
                                    <div class="stat-label">Completed</div>
                                </div>
                            </div>

                            <!-- Candidates Table -->
                            <div class="table-responsive">
                                <table class="onboarding-table">
                                    <thead>
                                        <tr>
                                            <th>Candidate</th>
                                            <th>Job</th>
                                            <th>Current Stage</th>
                                            <th>Progress</th>
                                            <th>Last Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($candidates)): ?>
                                        <?php foreach ($candidates as $candidate): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name, ENT_QUOTES, 'UTF-8') ?></strong><br>
                                                <small class="text-muted"><?= $candidate->reference_number ?></small>
                                            </td>
                                            <td><?= $candidate->job_name ? htmlspecialchars($candidate->job_name, ENT_QUOTES, 'UTF-8') : 'Not assigned' ?>
                                            </td>
                                            <td>
                                                <?php
                                                   // TEMPORARY DEBUG - Remove this after testing
                                                    echo "<!-- DEBUG: candidate_id: {$candidate->id}, hm_decision: " . ($candidate->hm_decision ?? 'NULL') . ", status: {$candidate->status}, stage_hm_decision: {$candidate->stage_hm_decision} -->";
                                                    
                                                    // FIXED: Improved stage determination logic
                                                    $stage_class = 'stage-not-started';
                                                    $stage_label = 'Not Started';
                                                    
                                                    //  FIX: Check if candidate is rejected - only if BOTH hm_decision is 'rejected' AND status is 'rejected'
                                                    // When we reopen HM decision, we reset hm_decision to null but status might still be 'rejected'
                                                    $is_rejected = ($candidate->hm_decision === 'rejected' && $candidate->status === 'rejected');
                                                    
                                                    if ($is_rejected) {
                                                        $stage_class = 'stage-hm-rejected';
                                                        $stage_label = 'Rejected';
                                                    }
                                                    // Check if onboarding is completed
                                                    elseif ($candidate->onboarding_stage === 'completed' || $candidate->stage_position_offered) {
                                                        $stage_class = 'stage-completed';
                                                        $stage_label = 'Completed';
                                                    }
                                                    //  FIXED: Check HM decision status - only if stage_hm_decision is completed AND hm_decision has a value
                                                    elseif ($candidate->stage_hm_decision && !empty($candidate->hm_decision)) {
                                                        if ($candidate->hm_decision === 'accepted') {
                                                            $stage_class = 'stage-hm-accepted';
                                                            $stage_label = 'HM Accepted';
                                                        } elseif ($candidate->hm_decision === 'rejected') {
                                                            $stage_class = 'stage-hm-rejected';
                                                            $stage_label = 'HM Rejected';
                                                        }
                                                    }
                                                    // Check individual stages in order to find the current active stage
                                                    else {
                                                        // Define stages in order
                                                        $stages = [
                                                            'stage_under_review' => ['label' => 'Under Review', 'class' => 'stage-under-review'],
                                                            'stage_submitted_to_hm' => ['label' => 'Submitted to HM', 'class' => 'stage-submitted-to-hm'],
                                                            'stage_hm_decision' => ['label' => 'HM Decision', 'class' => 'stage-hm-decision'],
                                                            'stage_documents_decision' => ['label' => 'Docs Decision', 'class' => 'stage-requested-docs'],
                                                            'stage_requested_docs' => ['label' => 'Requested Docs', 'class' => 'stage-requested-docs'],
                                                            'stage_position_offered' => ['label' => 'Position Offered', 'class' => 'stage-position-offered']
                                                        ];
                                                        
                                                        $current_stage_found = false;
                                                        
                                                        // Loop through stages to find the first incomplete stage
                                                        foreach ($stages as $stage_key => $stage_info) {
                                                            $stage_completed = !empty($candidate->$stage_key) && $candidate->$stage_key == 1;
                                                            
                                                            if (!$stage_completed && !$current_stage_found) {
                                                                // This is the current active stage (first incomplete stage)
                                                                $stage_class = $stage_info['class'];
                                                                $stage_label = $stage_info['label'];
                                                                $current_stage_found = true;
                                                            }
                                                        }
                                                        
                                                        // If all stages are completed but not marked as 'completed'
                                                        if (!$current_stage_found) {
                                                            $stage_class = 'stage-completed';
                                                            $stage_label = 'Completed';
                                                        }
                                                    }
                                                    ?>
                                                <span class="stage-badge <?= $stage_class ?>"><?= $stage_label ?></span>
                                            </td>
                                            <td class="progress-cell">
                                                <div class="progress-bar-small">
                                                    <div class="progress-fill-small"
                                                        style="width: <?= $candidate->onboarding_progress ?>%"></div>
                                                </div>
                                                <small class="text-muted"><?= round($candidate->onboarding_progress) ?>%
                                                    complete</small>
                                            </td>
                                            <td>
                                                <?= $candidate->updated_at ? date('M j, Y g:i A', strtotime($candidate->updated_at)) : 'Never' ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">

                                                    <a href="<?= redir('candidates/onboarding/' . $candidate->candidate_uuid . '?job=' . $candidate->job_uuid, true) ?>"
                                                        class="btn btn-primary btn-sm" title="Manage Onboarding">
                                                        <i class="fa fa-tasks"></i> Manage
                                                    </a>

                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fa fa-users fa-2x mb-2"></i><br>
                                                No candidates found for onboarding management.
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    document.querySelectorAll('.progress-fill-small').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
});
</script>