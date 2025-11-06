<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

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
                            <a href="<?= redir('candidates/view/' . $candidate->id, true) ?>" class="btn btn-info">
                                <i class="fa fa-user"></i> View Profile
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
                            <h4>⏳ Waiting for Job Assignment</h4>
                            <p>This candidate needs to be assigned to a job before onboarding can begin.<br>
                                Please contact a recruiter to assign this candidate to a job.</p>
                        </div>
                        <?php else: ?>
                        <div class="job-assignment-info">
                            <h5>📋 Job Assignment</h5>
                            <p><strong>Job:</strong> <?= $candidate->job_name ?? 'Not specified' ?></p>
                            <p><strong>Reference:</strong> <?= $candidate->job_ref ?? 'N/A' ?></p>
                        </div>

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

                            <!-- STAGE 3: REQUESTED DOCS -->
                            <div
                                class="stage-card <?= $candidate->stage_requested_docs ? 'completed' : ($candidate->onboarding_stage == 'stage_requested_docs' ? 'active' : '') ?> <?= !$candidate->stage_submitted_to_hm ? 'disabled-stage' : '' ?>">
                                <div class="stage-header">
                                    <div class="stage-number">3</div>
                                    <?php if ($candidate->stage_requested_docs): ?>
                                    <div class="completion-badge">Completed</div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <h4 class="stage-title">Requested Further Documents</h4>
                                    <p class="stage-description">Additional documentation requested from candidate</p>
                                    <?php if ($candidate->stage_requested_docs_at): ?>
                                    <div class="stage-date">Completed:
                                        <?= date('M j, Y', strtotime($candidate->stage_requested_docs_at)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <?php if (!$candidate->stage_requested_docs && $candidate->stage_submitted_to_hm): ?>
                                    <button class="btn btn-success btn-toggle-stage" data-stage="stage_requested_docs"
                                        data-value="1" data-action="complete"
                                        data-stage-name="Requested Further Documents">
                                        <i class="fa fa-check"></i> Mark Complete
                                    </button>
                                    <?php elseif ($candidate->stage_requested_docs): ?>
                                    <button class="btn btn-warning btn-toggle-stage" data-stage="stage_requested_docs"
                                        data-value="0" data-action="reopen"
                                        data-stage-name="Requested Further Documents">
                                        <i class="fa fa-undo"></i> Reopen Stage
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-secondary" disabled title="Complete previous stage first">
                                        <i class="fa fa-lock"></i> Locked
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- STAGE 4: POSITION OFFERED -->
                            <div
                                class="stage-card <?= $candidate->stage_position_offered ? 'completed' : ($candidate->onboarding_stage == 'stage_position_offered' ? 'active' : '') ?> <?= !$candidate->stage_requested_docs ? 'disabled-stage' : '' ?>">
                                <div class="stage-header">
                                    <div class="stage-number">4</div>
                                    <?php if ($candidate->stage_position_offered): ?>
                                    <div class="completion-badge">Completed</div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <h4 class="stage-title">Position Offered</h4>
                                    <p class="stage-description">Formal job offer extended to candidate</p>
                                    <?php if ($candidate->stage_position_offered_at): ?>
                                    <div class="stage-date">Completed:
                                        <?= date('M j, Y', strtotime($candidate->stage_position_offered_at)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <?php if (!$candidate->stage_position_offered && $candidate->stage_requested_docs): ?>
                                    <button class="btn btn-success btn-toggle-stage" data-stage="stage_position_offered"
                                        data-value="1" data-action="complete" data-stage-name="Position Offered">
                                        <i class="fa fa-check"></i> Mark Complete
                                    </button>
                                    <?php elseif ($candidate->stage_position_offered): ?>
                                    <button class="btn btn-warning btn-toggle-stage" data-stage="stage_position_offered"
                                        data-value="0" data-action="reopen" data-stage-name="Position Offered">
                                        <i class="fa fa-undo"></i> Reopen Stage
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-secondary" disabled title="Complete previous stage first">
                                        <i class="fa fa-lock"></i> Locked
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($candidate->onboarding_stage === 'completed'): ?>
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

    <script>
    on_script_load('jQuery', function() {
        jQuery(document).ready(function($) {
            console.log('Onboarding script initialized');

            setTimeout(function() {
                $('#animated-progress').css('width', '<?= $candidate->onboarding_progress ?>%');
            }, 500);

            $('.btn-toggle-stage:not(:disabled)').on('click', function(e) {
                e.preventDefault();
                console.log('Stage button clicked');

                const hasJobAssignment = <?= !empty($candidate->job_id) ? 'true' : 'false' ?>;

                if (!hasJobAssignment) {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(
                            'This candidate needs to be assigned to a job before onboarding can begin. Please contact a recruiter.'
                        );
                    } else {
                        alert(
                            'This candidate needs to be assigned to a job before onboarding can begin. Please contact a recruiter.'
                        );
                    }
                    return false;
                }

                const button = $(this);
                const stage = button.data('stage');
                const value = button.data('value');
                const stageName = button.data('stage-name');
                const action = button.data('action');
                const candidateId = <?= $candidate->id ?>;

                let message = '';
                if (action === 'complete') {
                    message =
                        `Are you sure you want to mark the "${stageName}" stage as complete?`;
                } else {
                    message =
                        `Are you sure you want to reopen the "${stageName}" stage? This will reset progress for subsequent stages.`;
                }

                $('#confirmationMessage').text(message);
                $('#confirmationModal').modal('show');

                $('#confirmAction').off('click').on('click', function() {
                    $('#confirmationModal').modal('hide');

                    const originalText = button.html();
                    button.prop('disabled', true).html(
                        '<i class="fa fa-spinner fa-spin"></i> Processing...');
                    button.closest('.stage-card').addClass('loading');

                    $.ajax({
                        url: '<?= site_url("agency/candidates/update_onboarding_stage") ?>',
                        type: 'POST',
                        data: {
                            candidate_id: candidateId,
                            stage: stage,
                            value: value
                        },
                        success: function(response) {
                            if (response.success) {
                                if (typeof toastr !== 'undefined') {
                                    toastr.success(
                                        'Stage updated successfully!');
                                } else {
                                    alert('Stage updated successfully!');
                                }

                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                if (typeof toastr !== 'undefined') {
                                    toastr.error('Error: ' + response
                                        .message);
                                } else {
                                    alert('Error: ' + response.message);
                                }
                                button.prop('disabled', false).html(
                                    originalText);
                                button.closest('.stage-card').removeClass(
                                    'loading');
                            }
                        },
                        error: function(xhr, status, error) {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(
                                    'An error occurred while updating the stage. Please try again.'
                                );
                            } else {
                                alert(
                                    'An error occurred while updating the stage. Please try again.'
                                );
                            }
                            button.prop('disabled', false).html(
                                originalText);
                            button.closest('.stage-card').removeClass(
                                'loading');
                            console.error('AJAX Error:', error);
                        }
                    });
                });
            });

            $('.stage-card:not(.disabled-stage)').hover(
                function() {
                    if (!$(this).hasClass('completed') && !$(this).hasClass('active')) {
                        $(this).css('transform', 'translateY(-2px)');
                    }
                },
                function() {
                    if (!$(this).hasClass('completed') && !$(this).hasClass('active')) {
                        $(this).css('transform', 'translateY(0)');
                    }
                }
            );
        });
    });
    </script>