<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php
defined('BASEPATH') || exit('No direct script access allowed');

// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}
?>
<style>
.onboarding-container {
    max-width: 1231px;
    margin: 0 auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.onboarding-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    position: relative;
}

.onboarding-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="0,0 1000,100 0,100"/></svg>');
    background-size: cover;
}

.onboarding-title {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    position: relative;
    z-index: 2;
}

.onboarding-subtitle {
    margin: 5px 0 0 0;
    font-size: 16px;
    opacity: 0.9;
    position: relative;
    z-index: 2;
}

.onboarding-content {
    padding: 30px;
}

.candidate-info-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 30px;
    border-left: 4px solid #667eea;
}

.candidate-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-weight: 600;
    color: #495057;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.info-value {
    color: #212529;
    font-size: 14px;
    font-weight: 500;
}

.onboarding-progress-section {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    border: 1px solid #e9ecef;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.progress-title {
    font-size: 18px;
    font-weight: 600;
    color: #212529;
    margin: 0;
}

.progress-percentage {
    font-size: 24px;
    font-weight: 700;
    color: #28a745;
}

.progress-bar-container {
    background: #f8f9fa;
    border-radius: 10px;
    height: 16px;
    margin-bottom: 15px;
    overflow: hidden;
    position: relative;
}

.progress-fill {
    background: linear-gradient(90deg, #28a745, #20c997);
    height: 100%;
    border-radius: 10px;
    transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% {
        left: -100%;
    }

    100% {
        left: 100%;
    }
}

.progress-stats {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #6c757d;
}

.onboarding-stages {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.stage-card {
    background: #fff;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 25px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stage-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #6c757d;
    transition: all 0.3s ease;
}

.stage-card.completed::before {
    background: #28a745;
}

.stage-card.active::before {
    background: #007bff;
}

.stage-card.rejected::before {
    background: #dc3545;
}

.stage-card.completed {
    border-color: #28a745;
    background: linear-gradient(135deg, #f8fff9 0%, #f0fff4 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.15);
}

.stage-card.active {
    border-color: #007bff;
    background: linear-gradient(135deg, #f8fbff 0%, #f0f7ff 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.15);
}

.stage-card.pending {
    border-color: #ffc107;
    background: linear-gradient(135deg, #fffdf6 0%, #fff9e6 100%);
}

.stage-card.rejected {
    border-color: #dc3545;
    background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
}

.stage-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.stage-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s ease;
}

.stage-card.completed .stage-number {
    background: #28a745;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.stage-card.active .stage-number {
    background: #007bff;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
}

.stage-card.rejected .stage-number {
    background: #dc3545;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.completion-badge {
    background: #28a745;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rejection-badge {
    background: #dc3545;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stage-content {
    margin-bottom: 20px;
}

.stage-title {
    font-weight: 700;
    margin-bottom: 8px;
    color: #212529;
    font-size: 16px;
}

.stage-description {
    font-size: 13px;
    color: #6c757d;
    line-height: 1.5;
    margin-bottom: 10px;
}

.stage-date {
    font-size: 11px;
    color: #28a745;
    font-style: italic;
    font-weight: 500;
}

.stage-rejection-date {
    font-size: 11px;
    color: #dc3545;
    font-style: italic;
    font-weight: 500;
}

.stage-actions {
    margin-top: 15px;
}

.btn-toggle-stage {
    width: 100%;
    padding: 10px 15px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-toggle-stage::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transition: all 0.3s ease;
    transform: translate(-50%, -50%);
}

.btn-toggle-stage:hover::before {
    width: 300px;
    height: 300px;
}

.btn-toggle-stage:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.btn-toggle-stage:disabled:hover {
    transform: none;
}

.completion-celebration {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 30px;
    animation: pulse 2s infinite;
}

.rejection-notice {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 30px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.02);
    }

    100% {
        transform: scale(1);
    }
}

.completion-celebration h4 {
    margin: 0 0 10px 0;
    font-size: 24px;
    font-weight: 700;
}

.completion-celebration p {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
}

.rejection-notice h4 {
    margin: 0 0 10px 0;
    font-size: 24px;
    font-weight: 700;
}

.rejection-notice p {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
}

.navigation-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.breadcrumb-nav {
    font-size: 14px;
    color: #6c757d;
}

.breadcrumb-nav a {
    color: #007bff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb-nav a:hover {
    color: #0056b3;
    text-decoration: underline;
}

.loading {
    position: relative;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    border-radius: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .onboarding-content {
        padding: 20px;
    }

    .candidate-info-grid {
        grid-template-columns: 1fr;
    }

    .onboarding-stages {
        grid-template-columns: 1fr;
    }

    .navigation-actions {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}

.disabled-stage {
    opacity: 0.6;
    pointer-events: none;
    position: relative;
}

.disabled-stage::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 12px;
}

.rejected-stage {
    opacity: 0.6;
    pointer-events: none;
    position: relative;
    background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
    border-color: #dc3545;
}

.rejected-stage::after {
    content: 'Rejected - Process Stopped';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(220, 53, 69, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dc3545;
    font-weight: 600;
    font-size: 14px;
    z-index: 2;
}

.waiting-assignment-message {
    background: linear-gradient(135deg, #ffc107, #ff9800);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 25px;
    animation: pulse 2s infinite;
}

.waiting-assignment-message h4 {
    margin: 0 0 10px 0;
    font-size: 20px;
    font-weight: 700;
}

.waiting-assignment-message p {
    margin: 0;
    font-size: 14px;
    opacity: 0.9;
}

.job-assignment-info {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.job-assignment-info h5 {
    margin: 0 0 10px 0;
    color: #1976d2;
    font-weight: 600;
}

.job-assignment-info p {
    margin: 5px 0;
    font-size: 14px;
}

.documents-decision-badge {
    background: #17a2b8;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.documents-required {
    background: #ffc107;
    color: black;
}

.documents-not-required {
    background: #28a745;
    color: white;
}
</style>

<div id="main-content">
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Candidate Onboarding</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= site_url('agency/dashboard') ?>"><i class="fa fa-dashboard"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= redir('candidates', true) ?>">Candidates</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= redir('candidates/view/' . $candidate->id, true) ?>">
                                <?= htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Onboarding</li>
                    </ul>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action">
                            <a href="<?= redir('candidates/edit/' . $candidate->id, true) ?>" class="btn btn-primary">
                                <i class="fa fa-edit"></i> Edit Candidate
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="onboarding-container">
                    <div class="onboarding-header">
                        <h1 class="onboarding-title">Candidate Onboarding</h1>
                        <p class="onboarding-subtitle">Track and manage the onboarding progress for
                            <?= $candidate->first_name . ' ' . $candidate->last_name ?></p>
                    </div>

                    <div class="onboarding-content">
                        <?php if (empty($candidate->job_id)): ?>
                        <div class="waiting-assignment-message">
                            <h4> Waiting for Job Assignment</h4>
                            <p>This candidate needs to be assigned to a job before onboarding can begin.<br>
                                Please contact a recruiter to assign this candidate to a job.</p>
                        </div>
                        <?php else: ?>
                        <div class="job-assignment-info">
                            <h5> Job Assignment</h5>
                            <p><strong>Job:</strong> <?= $candidate->job_name ?? 'Not specified' ?></p>
                            <p><strong>Reference:</strong> <?= $candidate->job_ref ?? 'N/A' ?></p>
                        </div>

                        <!-- Check if candidate is rejected -->
                        <?php 
                        //  FIX: Only show rejection notice if BOTH hm_decision is 'rejected' AND status is 'rejected'
                        // When HM decision is reopened, hm_decision becomes null but status might still be 'rejected'
                        $is_rejected = ($candidate->hm_decision === 'rejected' && $candidate->status === 'rejected'); 
                        ?>

                        <!-- Show rejection notice if candidate is rejected -->
                        <?php if ($is_rejected): ?>
                        <div class="rejection-notice">
                            <h4> Candidate Rejected</h4>
                            <p>This candidate has been rejected. The onboarding process has been stopped.<br>
                                To continue with onboarding, please change the HM decision or candidate status.</p>
                            <?php if ($candidate->hm_decision_at): ?>
                            <p><strong>Rejected on:</strong>
                                <?= date('F j, Y \a\t g:i A', strtotime($candidate->hm_decision_at)) ?></p>
                            <?php endif; ?>
                            <?php if ($candidate->hm_decision_notes): ?>
                            <p><strong>Reason:</strong>
                                <?= htmlspecialchars($candidate->hm_decision_notes, ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- ADD THE ONBOARDING PROGRESS AND STAGES HERE -->
                        <div class="onboarding-progress-section">
                            <div class="progress-header">
                                <h3 class="progress-title">Onboarding Progress</h3>
                                <div class="progress-percentage"><?= round($candidate->onboarding_progress) ?>%</div>
                            </div>
                            <div class="progress-bar-container">
                                <div id="animated-progress" class="progress-fill"
                                    style="width: <?= $candidate->onboarding_progress ?>%"></div>
                            </div>
                            <div class="progress-stats">
                                <span>Started</span>
                                <span>In Progress</span>
                                <span>Completed</span>
                            </div>
                        </div>

                        <div class="onboarding-stages">
                            <!-- STAGE 1: UNDER REVIEW -->
                            <div
                                class="stage-card <?= $candidate->stage_under_review ? 'completed' : ($candidate->onboarding_stage == 'stage_under_review' ? 'active' : '') ?>">
                                <div class="stage-header">
                                    <div class="stage-number">1</div>
                                    <?php if ($candidate->stage_under_review): ?>
                                    <div class="completion-badge">Completed</div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <h4 class="stage-title">Under Review</h4>
                                    <p class="stage-description">Initial candidate review and assessment</p>
                                    <?php if ($candidate->stage_under_review_at): ?>
                                    <div class="stage-date">Completed:
                                        <?= date('M j, Y', strtotime($candidate->stage_under_review_at)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <?php if (!$candidate->stage_under_review): ?>
                                    <button class="btn btn-success btn-toggle-stage" data-stage="stage_under_review"
                                        data-value="1" data-action="complete" data-stage-name="Under Review">
                                        <i class="fa fa-check"></i> Mark Complete
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-warning btn-toggle-stage" data-stage="stage_under_review"
                                        data-value="0" data-action="reopen" data-stage-name="Under Review">
                                        <i class="fa fa-undo"></i> Reopen Stage
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- STAGE 2: SUBMITTED TO HM -->
                            <div
                                class="stage-card <?= $candidate->stage_submitted_to_hm ? 'completed' : ($candidate->onboarding_stage == 'stage_submitted_to_hm' ? 'active' : '') ?> <?= !$candidate->stage_under_review ? 'disabled-stage' : '' ?>">
                                <div class="stage-header">
                                    <div class="stage-number">2</div>
                                    <?php if ($candidate->stage_submitted_to_hm): ?>
                                    <div class="completion-badge">Completed</div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <h4 class="stage-title">Submitted to Hiring Manager</h4>
                                    <p class="stage-description">Candidate profile submitted for HM review</p>
                                    <?php if ($candidate->stage_submitted_to_hm_at): ?>
                                    <div class="stage-date">Completed:
                                        <?= date('M j, Y', strtotime($candidate->stage_submitted_to_hm_at)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <?php if (!$candidate->stage_submitted_to_hm && $candidate->stage_under_review): ?>
                                    <button class="btn btn-success btn-toggle-stage" data-stage="stage_submitted_to_hm"
                                        data-value="1" data-action="complete"
                                        data-stage-name="Submitted to Hiring Manager">
                                        <i class="fa fa-check"></i> Mark Complete
                                    </button>
                                    <?php elseif ($candidate->stage_submitted_to_hm): ?>
                                    <button class="btn btn-warning btn-toggle-stage" data-stage="stage_submitted_to_hm"
                                        data-value="0" data-action="reopen"
                                        data-stage-name="Submitted to Hiring Manager">
                                        <i class="fa fa-undo"></i> Reopen Stage
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-secondary" disabled title="Complete previous stage first">
                                        <i class="fa fa-lock"></i> Locked
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- STAGE 3: HM DECISION -->
                            <div
                                class="stage-card <?= $candidate->stage_hm_decision ? ($candidate->hm_decision === 'rejected' ? 'rejected' : 'completed') : ($candidate->onboarding_stage == 'stage_hm_decision' ? 'active' : '') ?> <?= !$candidate->stage_submitted_to_hm ? 'disabled-stage' : '' ?>">
                                <div class="stage-header">
                                    <div class="stage-number">3</div>
                                    <?php if ($candidate->stage_hm_decision): ?>
                                    <?php if ($candidate->hm_decision === 'rejected'): ?>
                                    <div class="rejection-badge">Rejected</div>
                                    <?php else: ?>
                                    <div class="completion-badge">Completed</div>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <h4 class="stage-title">Hiring Manager Decision</h4>
                                    <p class="stage-description">Awaiting Hiring Manager acceptance or rejection</p>

                                    <?php if ($candidate->stage_hm_decision): ?>
                                    <div class="stage-date">
                                        Decision: <strong
                                            class="<?= $candidate->hm_decision === 'accepted' ? 'text-success' : 'text-danger' ?>">
                                            <?= ucfirst($candidate->hm_decision) ?>
                                        </strong>
                                        <?php if ($candidate->hm_decision_at): ?>
                                        <br>Decided: <?= date('M j, Y', strtotime($candidate->hm_decision_at)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($candidate->hm_decision_notes): ?>
                                    <div class="stage-notes mt-2">
                                        <small><strong>Notes:</strong>
                                            <?= htmlspecialchars($candidate->hm_decision_notes, ENT_QUOTES, 'UTF-8') ?></small>
                                    </div>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <?php if (!$candidate->stage_hm_decision && $candidate->stage_submitted_to_hm): ?>
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                        data-target="#hmDecisionModal">
                                        <i class="fa fa-clipboard-check"></i> Record Decision
                                    </button>
                                    <?php elseif ($candidate->stage_hm_decision): ?>
                                    <button class="btn btn-warning btn-toggle-stage" data-stage="stage_hm_decision"
                                        data-value="0" data-action="reopen" data-stage-name="Hiring Manager Decision">
                                        <i class="fa fa-undo"></i> Reopen Stage
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-secondary" disabled title="Complete previous stage first">
                                        <i class="fa fa-lock"></i> Locked
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- STAGE 4: DOCUMENTS DECISION - NEW STAGE -->
                            <div
                                class="stage-card <?= (isset($candidate->stage_documents_decision) && $candidate->stage_documents_decision) ? 'completed' : ($candidate->onboarding_stage == 'stage_documents_decision' ? 'active' : '') ?> <?= (!$candidate->stage_hm_decision || $candidate->hm_decision !== 'accepted' || $is_rejected) ? 'disabled-stage' : '' ?> <?= $is_rejected ? 'rejected-stage' : '' ?>">
                                <div class="stage-header">
                                    <div class="stage-number">4</div>
                                    <?php if (isset($candidate->stage_documents_decision) && $candidate->stage_documents_decision): ?>
                                    <div
                                        class="documents-decision-badge <?= (isset($candidate->documents_required) && $candidate->documents_required) ? 'documents-required' : 'documents-not-required' ?>">
                                        <?= (isset($candidate->documents_required) && $candidate->documents_required) ? 'Docs Required' : 'No Docs Needed' ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <h4 class="stage-title">Documents Decision</h4>
                                    <p class="stage-description">Decide if additional documents are required from the
                                        candidate</p>

                                    <?php if (isset($candidate->stage_documents_decision) && $candidate->stage_documents_decision): ?>
                                    <?php if (isset($candidate->documents_required) && $candidate->documents_required): ?>
                                    <div class="stage-date">
                                        <strong class="text-warning"> Documents Required</strong>
                                        <?php if (isset($candidate->documents_notes) && $candidate->documents_notes): ?>
                                        <div class="stage-notes mt-2">
                                            <small><strong>Required Documents:</strong>
                                                <?= htmlspecialchars($candidate->documents_notes, ENT_QUOTES, 'UTF-8') ?></small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="stage-date">
                                        <strong class="text-success"> No Additional Documents Required</strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($candidate->stage_documents_decision_at) && $candidate->stage_documents_decision_at): ?>
                                    <div class="stage-date">Decided:
                                        <?= date('M j, Y', strtotime($candidate->stage_documents_decision_at)) ?></div>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <?php if ((!isset($candidate->stage_documents_decision) || !$candidate->stage_documents_decision) && $candidate->stage_hm_decision && $candidate->hm_decision === 'accepted' && !$is_rejected): ?>
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                        data-target="#documentsDecisionModal">
                                        <i class="fa fa-file-alt"></i> Make Decision
                                    </button>
                                    <?php elseif ((isset($candidate->stage_documents_decision) && $candidate->stage_documents_decision) && !$is_rejected): ?>
                                    <button class="btn btn-warning btn-toggle-stage"
                                        data-stage="stage_documents_decision" data-value="0" data-action="reopen"
                                        data-stage-name="Documents Decision">
                                        <i class="fa fa-undo"></i> Reopen Decision
                                    </button>
                                    <?php else: ?>
                                    <?php if ($is_rejected): ?>
                                    <button class="btn btn-secondary" disabled
                                        title="Candidate rejected - process stopped">
                                        <i class="fa fa-ban"></i> Process Stopped
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-secondary" disabled title="Complete previous stage first">
                                        <i class="fa fa-lock"></i> Locked
                                    </button>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div
                                class="stage-card <?= (isset($candidate->stage_requested_docs) && $candidate->stage_requested_docs) ? 'completed' : ($candidate->onboarding_stage == 'stage_requested_docs' ? 'active' : '') ?> <?= ((!isset($candidate->stage_documents_decision) || !$candidate->stage_documents_decision || (isset($candidate->documents_required) && !$candidate->documents_required) || $is_rejected) ? 'disabled-stage' : '' )?> <?= $is_rejected ? 'rejected-stage' : '' ?>">
                                <div class="stage-header">
                                    <div class="stage-number">5</div>
                                    <?php if (isset($candidate->stage_requested_docs) && $candidate->stage_requested_docs): ?>
                                    <div class="completion-badge">Submitted & Reviewed</div>
                                    <?php elseif ($has_required_docs && !$candidate->stage_requested_docs): ?>
                                    <div class="badge badge-warning">Documents Submitted</div>
                                    <?php elseif (isset($candidate->documents_required) && !$candidate->documents_required): ?>
                                    <!--  ADD THIS: Show skipped badge when documents are not required -->
                                    <div class="badge badge-info">Skipped</div>
                                    <?php elseif (isset($candidate->documents_required) && $candidate->documents_required): ?>
                                    <div class="badge badge-info">Awaiting Documents</div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <h4 class="stage-title">Requested Further Documents</h4>
                                    <p class="stage-description">Additional documentation requested from candidate</p>

                                    <?php if (isset($candidate->documents_required) && !$candidate->documents_required): ?>
                                    <!--  ADD THIS: Show skipped message -->
                                    <div class="stage-date text-info">
                                        <strong> Stage Skipped - No Documents Required</strong>
                                    </div>
                                    <?php elseif ($has_required_docs && !$candidate->stage_requested_docs): ?>
                                    <div class="stage-date text-warning">
                                        <strong> Documents Submitted - Awaiting Review</strong>
                                    </div>
                                    <?php elseif (isset($candidate->stage_requested_docs_at) && $candidate->stage_requested_docs_at): ?>
                                    <div class="stage-date">Completed:
                                        <?= date('M j, Y', strtotime($candidate->stage_requested_docs_at)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <?php if ((!isset($candidate->stage_requested_docs) || !$candidate->stage_requested_docs) && (isset($candidate->stage_documents_decision) && $candidate->stage_documents_decision) && (isset($candidate->documents_required) && $candidate->documents_required) && !$is_rejected): ?>
                                    <button class="btn btn-success btn-toggle-stage" data-stage="stage_requested_docs"
                                        data-value="1" data-action="complete"
                                        data-stage-name="Requested Further Documents">
                                        <i class="fa fa-check"></i> Mark Complete
                                    </button>
                                    <?php elseif ((isset($candidate->stage_requested_docs) && $candidate->stage_requested_docs) && !$is_rejected): ?>
                                    <button class="btn btn-warning btn-toggle-stage" data-stage="stage_requested_docs"
                                        data-value="0" data-action="reopen"
                                        data-stage-name="Requested Further Documents">
                                        <i class="fa fa-undo"></i> Reopen Stage
                                    </button>
                                    <?php else: ?>
                                    <?php if ($is_rejected): ?>
                                    <button class="btn btn-secondary" disabled
                                        title="Candidate rejected - process stopped">
                                        <i class="fa fa-ban"></i> Process Stopped
                                    </button>
                                    <?php elseif (isset($candidate->documents_required) && !$candidate->documents_required): ?>
                                    <button class="btn btn-secondary" disabled title="Documents not required - skipped">
                                        <i class="fa fa-forward"></i> Skipped
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-secondary" disabled title="Complete previous stage first">
                                        <i class="fa fa-lock"></i> Locked
                                    </button>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- STAGE 6: POSITION OFFERED -->
                            <?php
                            $position_offered_disabled = false;
                            if (!$candidate->stage_documents_decision) {
                                $position_offered_disabled = true;
                            } elseif ($candidate->documents_required && !$candidate->stage_requested_docs) {
                                $position_offered_disabled = true;
                            } elseif ($is_rejected) {
                                $position_offered_disabled = true;
                            }
                            ?>
                            <div
                                class="stage-card <?= (isset($candidate->stage_position_offered) && $candidate->stage_position_offered) ? 'completed' : ($candidate->onboarding_stage == 'stage_position_offered' ? 'active' : '') ?> <?= $position_offered_disabled ? 'disabled-stage' : '' ?> <?= $is_rejected ? 'rejected-stage' : '' ?>">
                                <div class="stage-header">
                                    <div class="stage-number">
                                        <?= (isset($candidate->documents_required) && $candidate->documents_required) ? '6' : '5' ?>
                                    </div>
                                    <?php if (isset($candidate->stage_position_offered) && $candidate->stage_position_offered): ?>
                                    <div class="completion-badge">Completed</div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <h4 class="stage-title">Position Offered</h4>
                                    <p class="stage-description">Formal job offer extended to candidate</p>
                                    <?php if (isset($candidate->stage_position_offered_at) && $candidate->stage_position_offered_at): ?>
                                    <div class="stage-date">Completed:
                                        <?= date('M j, Y', strtotime($candidate->stage_position_offered_at)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <?php if ((!isset($candidate->stage_position_offered) || !$candidate->stage_position_offered) && !$position_offered_disabled): ?>
                                    <button class="btn btn-success btn-toggle-stage" data-stage="stage_position_offered"
                                        data-value="1" data-action="complete" data-stage-name="Position Offered">
                                        <i class="fa fa-check"></i> Mark Complete
                                    </button>
                                    <?php elseif ((isset($candidate->stage_position_offered) && $candidate->stage_position_offered) && !$is_rejected): ?>
                                    <button class="btn btn-warning btn-toggle-stage" data-stage="stage_position_offered"
                                        data-value="0" data-action="reopen" data-stage-name="Position Offered">
                                        <i class="fa fa-undo"></i> Reopen Stage
                                    </button>
                                    <?php else: ?>
                                    <?php if ($is_rejected): ?>
                                    <button class="btn btn-secondary" disabled
                                        title="Candidate rejected - process stopped">
                                        <i class="fa fa-ban"></i> Process Stopped
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-secondary" disabled title="Complete previous stage first">
                                        <i class="fa fa-lock"></i> Locked
                                    </button>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($candidate->onboarding_stage === 'completed' && !$is_rejected): ?>
                        <div class="completion-celebration">
                            <h4>🎉 Onboarding Completed!</h4>
                            <p>All stages have been successfully completed. Candidate is ready for the next steps.</p>
                            <?php if ($candidate->onboarding_completed_at): ?>
                            <p><strong>Completed on:</strong>
                                <?= date('F j, Y \a\t g:i A', strtotime($candidate->onboarding_completed_at)) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Stage Update</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="confirmationMessage">Are you sure you want to update this stage?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- HM Decision Modal -->
    <div class="modal fade" id="hmDecisionModal" tabindex="-1" role="dialog" aria-labelledby="hmDecisionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hmDecisionModalLabel">Record Hiring Manager Decision</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="hmDecisionForm">
                        <input type="hidden" name="candidate_id" value="<?= $candidate->id ?>">

                        <div class="form-group">
                            <label for="decision"><strong>Decision</strong></label>
                            <select class="form-control" id="decision" name="decision" required>
                                <option value="">Select Decision</option>
                                <option value="accepted"> Accept Candidate</option>
                                <option value="rejected"> Reject Candidate</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="notes">Decision Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"
                                placeholder="Add any notes about the decision..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveHmDecision">Save Decision</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Decision Modal -->
    <div class="modal fade" id="documentsDecisionModal" tabindex="-1" role="dialog"
        aria-labelledby="documentsDecisionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentsDecisionModalLabel">Documents Requirement Decision</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="documentsDecisionForm">
                        <input type="hidden" name="candidate_id" value="<?= $candidate->id ?>">

                        <div class="form-group">
                            <label for="documents_required"><strong>Are additional documents required?</strong></label>
                            <select class="form-control" id="documents_required" name="documents_required" required>
                                <option value="">Select Option</option>
                                <option value="1"> Yes, documents are required</option>
                                <option value="0"> No, no additional documents needed</option>
                            </select>
                        </div>

                        <div class="form-group" id="documentsNotesGroup" style="display: none;">
                            <label for="documents_notes">Required Documents Details</label>
                            <textarea class="form-control" id="documents_notes" name="documents_notes" rows="4"
                                placeholder="Specify which documents are required from the candidate (e.g., ID copy, qualifications, references, etc.)..."></textarea>
                            <small class="form-text text-muted">This information will be sent to the recruiter.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveDocumentsDecision">Save Decision</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    <div class="card mt-4">
        <div class="card-header bg-warning text-white">
            <h4 class="card-title mb-0"> Required Documents</h4>
        </div>
        <div class="card-body">
            <!-- Documents Status Alert -->
            <div id="documentsStatusAlert" class="alert alert-info">
                <h5><i class="fa fa-info-circle"></i> Documents Status</h5>
                <p id="documentsStatusText">
                    <?php if ($candidate->stage_requested_docs): ?>
                    <span class="text-success"><i class="fa fa-check-circle"></i> <strong>Documents Submitted:</strong>
                        All required documents have been submitted and reviewed.</span>
                    <?php elseif (isset($candidate->documents_required) && $candidate->documents_required): ?>
                    <span class="text-warning"><i class="fa fa-clock"></i> <strong>Awaiting Documents:</strong> Required
                        documents have been requested from the recruiter.</span>
                    <?php else: ?>
                    <span class="text-info"><i class="fa fa-info-circle"></i> <strong>No Documents Required:</strong> No
                        additional documents are needed at this stage.</span>
                    <?php endif; ?>
                </p>

                <?php if (isset($candidate->documents_notes) && $candidate->documents_notes && $candidate->documents_required): ?>
                <div class="mt-2 p-2 bg-light rounded">
                    <strong>Required Documents:</strong>
                    <?= htmlspecialchars($candidate->documents_notes, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Required Documents List -->
            <div id="requiredDocumentsList">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Document Name</th>
                                <th>Type</th>
                                <th>Document Type Value</th>
                                <th>Uploaded By</th>
                                <th>Uploaded Date</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($required_documents)): ?>
                            <?php foreach ($required_documents as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc->document_name) ?></td>
                                <td>
                                    <?php if ($doc->document_type === 'required_document'): ?>
                                    <span class="badge badge-warning">Required Document</span>
                                    <?php else: ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($doc->document_type) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($doc->document_type) ?></small></td>
                                <td><?= htmlspecialchars($doc->uploader_name ?? 'Recruiter') ?></td>
                                <td><?= date('M j, Y', strtotime($doc->created_at)) ?></td>
                                <td><?= formatFileSize($doc->file_size) ?></td>
                                <td>
                                    <a href="<?= base_url($doc->file_path) ?>" target="_blank"
                                        class="btn btn-sm btn-primary" title="Download">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No documents found for this candidate.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mark as Reviewed Button -->
            <?php if (isset($can_mark_reviewed) && $can_mark_reviewed): ?>
            <div class="text-center mt-3">
                <button id="markDocumentsReviewed" class="btn btn-success btn-lg">
                    <i class="fa fa-check-circle"></i> Mark Documents as Reviewed & Complete Stage
                </button>
                <p class="text-muted mt-2">Click this button after reviewing all submitted required documents</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Complete Vanilla JS solution for onboarding
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Onboarding script initialized - Clean Version');

        // Initialize progress bar animation
        setTimeout(function() {
            const progressBar = document.getElementById('animated-progress');
            if (progressBar) {
                progressBar.style.width = '<?= $candidate->onboarding_progress ?>%';
            }
        }, 500);

        // Documents notes field toggle - SIMPLIFIED
        const documentsRequired = document.getElementById('documents_required');
        const notesGroup = document.getElementById('documentsNotesGroup');

        if (documentsRequired && notesGroup) {
            documentsRequired.addEventListener('change', function() {
                notesGroup.style.display = this.value === '1' ? 'block' : 'none';
            });
        }

        // Universal modal hiding function
        function hideModal(modalId) {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                // Bootstrap 4 method
                $(modalElement).modal('hide');
            }
        }

        // Stage toggle functionality
        const stageButtons = document.querySelectorAll('.btn-toggle-stage:not(:disabled)');
        stageButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Stage button clicked');

                const stage = button.dataset.stage;
                const value = button.dataset.value;
                const stageName = button.dataset.stageName;
                const action = button.dataset.action;
                const candidateId = <?= $candidate->id ?>;

                let message = '';
                if (action === 'complete') {
                    message =
                        `Are you sure you want to mark the "${stageName}" stage as complete?`;
                } else {
                    message =
                        `Are you sure you want to reopen the "${stageName}" stage? This will reset progress for subsequent stages.`;
                }

                const confirmationMessage = document.getElementById('confirmationMessage');
                if (confirmationMessage) {
                    confirmationMessage.textContent = message;
                }

                // Show confirmation modal
                $('#confirmationModal').modal('show');

                // Handle confirm action
                const confirmAction = document.getElementById('confirmAction');
                const newConfirmAction = confirmAction.cloneNode(true);
                confirmAction.parentNode.replaceChild(newConfirmAction, confirmAction);

                newConfirmAction.onclick = function() {
                    $('#confirmationModal').modal('hide');

                    const originalText = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> Processing...';
                    button.closest('.stage-card').classList.add('loading');

                    // Make AJAX request
                    const formData = new FormData();
                    formData.append('candidate_id', candidateId);
                    formData.append('stage', stage);
                    formData.append('value', value);

                    fetch('<?= site_url("agency/candidates/update_onboarding_stage") ?>', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (typeof toastr !== 'undefined') {
                                    toastr.success('Stage updated successfully!');
                                }
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                throw new Error(data.message ||
                                    'Failed to update stage');
                            }
                        })
                        .catch(error => {
                            console.error('AJAX Error:', error);
                            if (typeof toastr !== 'undefined') {
                                toastr.error(
                                    'An error occurred while updating the stage. Please try again.'
                                );
                            }
                            button.disabled = false;
                            button.innerHTML = originalText;
                            button.closest('.stage-card').classList.remove('loading');
                        });
                };
            });
        });

        // HM Decision functionality
        const saveHmDecision = document.getElementById('saveHmDecision');
        if (saveHmDecision) {
            saveHmDecision.addEventListener('click', function() {
                const decision = document.getElementById('decision').value;
                const notes = document.getElementById('notes').value;

                if (!decision) {
                    toastr.warning('Please select a decision');
                    return;
                }

                const button = this;
                const originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

                const formData = new FormData();
                formData.append('candidate_id', <?= $candidate->id ?>);
                formData.append('decision', decision);
                formData.append('notes', notes);

                fetch('<?= site_url("agency/candidates/update_hm_decision") ?>', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastr.success('Decision recorded successfully!');
                            $('#hmDecisionModal').modal('hide');
                            document.getElementById('hmDecisionForm').reset();
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            throw new Error(data.message || 'Failed to save decision');
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Error:', error);
                        toastr.error(
                            'An error occurred while saving the decision. Please try again.');
                        button.disabled = false;
                        button.innerHTML = originalText;
                    });
            });
        }

        // Documents Decision functionality - CLEAN VERSION
        const saveDocumentsDecision = document.getElementById('saveDocumentsDecision');
        if (saveDocumentsDecision) {
            // Remove any existing event listeners
            const newSaveBtn = saveDocumentsDecision.cloneNode(true);
            saveDocumentsDecision.parentNode.replaceChild(newSaveBtn, saveDocumentsDecision);

            newSaveBtn.addEventListener('click', function() {
                const documentsRequired = document.getElementById('documents_required').value;
                const documentsNotes = document.getElementById('documents_notes').value;

                if (!documentsRequired) {
                    toastr.warning('Please select whether documents are required');
                    return;
                }

                if (documentsRequired === '1' && !documentsNotes.trim()) {
                    toastr.warning('Please specify which documents are required');
                    return;
                }

                const button = this;
                const originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

                const formData = new FormData();
                formData.append('candidate_id', <?= $candidate->id ?>);
                formData.append('documents_required', documentsRequired);
                formData.append('documents_notes', documentsNotes);

                console.log('Sending documents decision request...');

                fetch('<?= site_url("agency/candidates/update_documents_decision") ?>', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Success response:', data);
                        if (data.success) {
                            toastr.success(data.message ||
                                'Documents decision saved successfully!');
                            $('#documentsDecisionModal').modal('hide');
                            document.getElementById('documentsDecisionForm').reset();

                            // Hide notes group
                            const notesGroup = document.getElementById('documentsNotesGroup');
                            if (notesGroup) notesGroup.style.display = 'none';

                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            throw new Error(data.message || 'Failed to save documents decision');
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Error:', error);
                        toastr.error(
                            'An error occurred while saving the documents decision. Please try again.'
                        );
                        button.disabled = false;
                        button.innerHTML = originalText;
                    });
            });
        }

        // Mark documents as reviewed
        const markDocumentsReviewed = document.getElementById('markDocumentsReviewed');
        if (markDocumentsReviewed) {
            markDocumentsReviewed.addEventListener('click', function() {
                const button = this;
                const originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

                const formData = new FormData();
                formData.append('candidate_id', <?= $candidate->id ?>);
                formData.append('stage', 'stage_requested_docs');
                formData.append('value', '1');

                fetch('<?= site_url("agency/candidates/update_onboarding_stage") ?>', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastr.success('Documents marked as reviewed! Stage completed.');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            throw new Error(data.message || 'Failed to mark documents as reviewed');
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Error:', error);
                        toastr.error('An error occurred. Please try again.');
                        button.disabled = false;
                        button.innerHTML = originalText;
                    });
            });
        }

        // Reset forms when modals are hidden
        $('#hmDecisionModal').on('hidden.bs.modal', function() {
            document.getElementById('hmDecisionForm').reset();
        });

        $('#documentsDecisionModal').on('hidden.bs.modal', function() {
            document.getElementById('documentsDecisionForm').reset();
            const notesGroup = document.getElementById('documentsNotesGroup');
            if (notesGroup) notesGroup.style.display = 'none';
        });

        // Hover effects for stage cards
        const stageCards = document.querySelectorAll('.stage-card:not(.disabled-stage)');
        stageCards.forEach(function(card) {
            card.addEventListener('mouseenter', function() {
                if (!this.classList.contains('completed') && !this.classList.contains(
                        'active')) {
                    this.style.transform = 'translateY(-2px)';
                }
            });

            card.addEventListener('mouseleave', function() {
                if (!this.classList.contains('completed') && !this.classList.contains(
                        'active')) {
                    this.style.transform = 'translateY(0)';
                }
            });
        });
    });
    </script>