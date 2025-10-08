<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
.onboarding-container {
    max-width: 1000px;
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

/* Loading states */
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

/* Responsive design */
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
                    <!-- Header Section -->
                    <div class="onboarding-header">
                        <h1 class="onboarding-title">Candidate Onboarding</h1>
                        <p class="onboarding-subtitle">Track and manage the onboarding progress for
                            <?= $candidate->first_name . ' ' . $candidate->last_name ?></p>
                    </div>

                    <div class="onboarding-content">
                        <!-- Candidate Information -->
                        <div class="candidate-info-card">
                            <h5 style="margin-bottom: 20px; color: #495057; font-weight: 600;">Candidate Information
                            </h5>
                            <div class="candidate-info-grid">
                                <div class="info-item">
                                    <span class="info-label">Reference Number</span>
                                    <span class="info-value"><?= $candidate->reference_number ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Full Name</span>
                                    <span
                                        class="info-value"><?= $candidate->first_name . ' ' . $candidate->last_name ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Email</span>
                                    <span class="info-value"><?= $candidate->email ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Phone</span>
                                    <span class="info-value"><?= $candidate->phone ?: 'Not provided' ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Application Status</span>
                                    <span class="info-value">
                                        <span
                                            class="badge badge-<?= $candidate->status === 'hired' ? 'success' : ($candidate->status === 'rejected' ? 'danger' : 'info') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $candidate->status)) ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Application Date</span>
                                    <span
                                        class="info-value"><?= date('M j, Y', strtotime($candidate->application_date)) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Section -->
                        <div class="onboarding-progress-section">
                            <div class="progress-header">
                                <h3 class="progress-title">Onboarding Progress</h3>
                                <div class="progress-percentage"><?= round($candidate->onboarding_progress) ?>%</div>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-fill" id="animated-progress" style="width: 0%"></div>
                            </div>
                            <div class="progress-stats">
                                <span>Started</span>
                                <span>In Progress</span>
                                <span>Completed</span>
                            </div>
                        </div>

                        <!-- Onboarding Stages -->
                        <h4 style="margin-bottom: 20px; color: #495057; font-weight: 600;">Onboarding Stages</h4>
                        <div class="onboarding-stages">
                            <!-- Stage 1: Under Review -->
                            <div
                                class="stage-card <?= $candidate->stage_under_review ? 'completed' : ($candidate->onboarding_stage === 'stage_under_review' ? 'active' : '') ?>">
                                <div class="stage-header">
                                    <div class="stage-number">1</div>
                                    <?php if ($candidate->stage_under_review): ?>
                                    <span class="completion-badge">Completed</span>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <div class="stage-title">Under Review</div>
                                    <div class="stage-description">
                                        Initial screening and review of candidate application, qualifications, and
                                        experience.
                                    </div>
                                    <?php if ($candidate->stage_under_review_at): ?>
                                    <div class="stage-date">
                                        Completed:
                                        <?= date('M j, Y g:i A', strtotime($candidate->stage_under_review_at)) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <button
                                        class="btn btn-<?= $candidate->stage_under_review ? 'warning' : 'success' ?> btn-toggle-stage"
                                        data-stage="stage_under_review"
                                        data-value="<?= $candidate->stage_under_review ? 0 : 1 ?>"
                                        data-stage-name="Under Review"
                                        data-action="<?= $candidate->stage_under_review ? 'reopen' : 'complete' ?>">
                                        <?= $candidate->stage_under_review ? '↶ Reopen Stage' : '✓ Mark Complete' ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Stage 2: Submitted to Hiring Manager -->
                            <div
                                class="stage-card <?= $candidate->stage_submitted_to_hm ? 'completed' : ($candidate->onboarding_stage === 'stage_submitted_to_hm' ? 'active' : '') ?>">
                                <div class="stage-header">
                                    <div class="stage-number">2</div>
                                    <?php if ($candidate->stage_submitted_to_hm): ?>
                                    <span class="completion-badge">Completed</span>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <div class="stage-title">Submitted to Hiring Manager</div>
                                    <div class="stage-description">
                                        Candidate profile has been submitted to the hiring manager for consideration and
                                        feedback.
                                    </div>
                                    <?php if ($candidate->stage_submitted_to_hm_at): ?>
                                    <div class="stage-date">
                                        Completed:
                                        <?= date('M j, Y g:i A', strtotime($candidate->stage_submitted_to_hm_at)) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <button
                                        class="btn btn-<?= $candidate->stage_submitted_to_hm ? 'warning' : 'success' ?> btn-toggle-stage"
                                        data-stage="stage_submitted_to_hm"
                                        data-value="<?= $candidate->stage_submitted_to_hm ? 0 : 1 ?>"
                                        data-stage-name="Submitted to Hiring Manager"
                                        data-action="<?= $candidate->stage_submitted_to_hm ? 'reopen' : 'complete' ?>"
                                        <?= !$candidate->stage_under_review ? 'disabled' : '' ?>>
                                        <?= $candidate->stage_submitted_to_hm ? '↶ Reopen Stage' : '✓ Mark Complete' ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Stage 3: Requested Further Documents -->
                            <div
                                class="stage-card <?= $candidate->stage_requested_docs ? 'completed' : ($candidate->onboarding_stage === 'stage_requested_docs' ? 'active' : '') ?>">
                                <div class="stage-header">
                                    <div class="stage-number">3</div>
                                    <?php if ($candidate->stage_requested_docs): ?>
                                    <span class="completion-badge">Completed</span>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <div class="stage-title">Requested Further Documents</div>
                                    <div class="stage-description">
                                        Additional verification documents have been requested from the candidate for
                                        background checks.
                                    </div>
                                    <?php if ($candidate->stage_requested_docs_at): ?>
                                    <div class="stage-date">
                                        Completed:
                                        <?= date('M j, Y g:i A', strtotime($candidate->stage_requested_docs_at)) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <button
                                        class="btn btn-<?= $candidate->stage_requested_docs ? 'warning' : 'success' ?> btn-toggle-stage"
                                        data-stage="stage_requested_docs"
                                        data-value="<?= $candidate->stage_requested_docs ? 0 : 1 ?>"
                                        data-stage-name="Requested Further Documents"
                                        data-action="<?= $candidate->stage_requested_docs ? 'reopen' : 'complete' ?>"
                                        <?= !$candidate->stage_submitted_to_hm ? 'disabled' : '' ?>>
                                        <?= $candidate->stage_requested_docs ? '↶ Reopen Stage' : '✓ Mark Complete' ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Stage 4: Position Offered -->
                            <div
                                class="stage-card <?= $candidate->stage_position_offered ? 'completed' : ($candidate->onboarding_stage === 'stage_position_offered' ? 'active' : '') ?>">
                                <div class="stage-header">
                                    <div class="stage-number">4</div>
                                    <?php if ($candidate->stage_position_offered): ?>
                                    <span class="completion-badge">Completed</span>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-content">
                                    <div class="stage-title">Position Offered</div>
                                    <div class="stage-description">
                                        Formal job offer has been extended to the candidate with terms and conditions.
                                    </div>
                                    <?php if ($candidate->stage_position_offered_at): ?>
                                    <div class="stage-date">
                                        Completed:
                                        <?= date('M j, Y g:i A', strtotime($candidate->stage_position_offered_at)) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="stage-actions">
                                    <button
                                        class="btn btn-<?= $candidate->stage_position_offered ? 'warning' : 'success' ?> btn-toggle-stage"
                                        data-stage="stage_position_offered"
                                        data-value="<?= $candidate->stage_position_offered ? 0 : 1 ?>"
                                        data-stage-name="Position Offered"
                                        data-action="<?= $candidate->stage_position_offered ? 'reopen' : 'complete' ?>"
                                        <?= !$candidate->stage_requested_docs ? 'disabled' : '' ?>>
                                        <?= $candidate->stage_position_offered ? '↶ Reopen Stage' : '✓ Mark Complete' ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Completion Celebration -->
                        <?php if ($candidate->onboarding_completed_at): ?>
                        <div class="completion-celebration">
                            <h4>🎉 Onboarding Completed Successfully! 🎉</h4>
                            <p>All onboarding stages were completed on
                                <?= date('F j, Y \a\t g:i A', strtotime($candidate->onboarding_completed_at)) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Navigation -->
                        <div class="navigation-actions">
                            <div class="breadcrumb-nav">
                                <a href="<?= redir('candidates', true) ?>"><i class="fa fa-arrow-left"></i> Back to
                                    Candidates List</a>
                            </div>
                            <div>
                                <a href="<?= redir('candidates/view/' . $candidate->id, true) ?>"
                                    class="btn btn-outline-primary">
                                    <i class="fa fa-user"></i> View Candidate Profile
                                </a>
                                <a href="<?= redir('candidates/edit/' . $candidate->id, true) ?>"
                                    class="btn btn-primary">
                                    <i class="fa fa-edit"></i> Edit Candidate
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
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
// Use the existing on_script_load function from your template
on_script_load('jQuery', function() {
    jQuery(document).ready(function($) {
        console.log('Onboarding script initialized');

        // Animate progress bar on load
        setTimeout(function() {
            $('#animated-progress').css('width', '<?= $candidate->onboarding_progress ?>%');
        }, 500);

        // Stage update handler with confirmation modal
        $('.btn-toggle-stage').on('click', function(e) {
            e.preventDefault();
            console.log('Stage button clicked');

            const button = $(this);
            const stage = button.data('stage');
            const value = button.data('value');
            const stageName = button.data('stage-name');
            const action = button.data('action');
            const candidateId = <?= $candidate->id ?>;

            // Set confirmation message based on action
            let message = '';
            if (action === 'complete') {
                message = `Are you sure you want to mark the "${stageName}" stage as complete?`;
            } else {
                message =
                    `Are you sure you want to reopen the "${stageName}" stage? This will reset progress for subsequent stages.`;
            }

            // Show confirmation modal
            $('#confirmationMessage').text(message);
            $('#confirmationModal').modal('show');

            // Handle confirmation
            $('#confirmAction').off('click').on('click', function() {
                $('#confirmationModal').modal('hide');

                // Show loading state
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
                            // Show success message using your existing notification system
                            if (typeof toastr !== 'undefined') {
                                toastr.success(
                                    'Stage updated successfully!');
                            } else {
                                alert('Stage updated successfully!');
                            }

                            // Reload the page after a short delay to show the updated state
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error('Error: ' + response.message);
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
                                'An error occurred while updating the stage. Please try again.');
                        }
                        button.prop('disabled', false).html(originalText);
                        button.closest('.stage-card').removeClass(
                        'loading');
                        console.error('AJAX Error:', error);
                    }
                });
            });
        });

        // Add hover effects
        $('.stage-card').hover(
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