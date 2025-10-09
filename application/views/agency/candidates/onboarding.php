<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>

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
                <!-- In the header section - around line 50+ -->
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action">
                            <!-- ADD THIS BUTTON -->
                            <a href="<?= site_url('agency/candidates_list/view/' . $candidate->id) ?>"
                                class="btn btn-info">
                                <i class="fa fa-user-circle"></i> View Full Profile
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

                        <!-- Completion Actions -->
                        <?php if ($candidate->stage_under_review && $candidate->stage_submitted_to_hm && $candidate->stage_requested_docs && $candidate->stage_position_offered): ?>
                        <?php if (!$candidate->onboarding_completed_at): ?>
                        <div class="completion-actions">
                            <h4 style="margin-bottom: 15px; color: #28a745;">🎉 All Stages Completed!</h4>
                            <p style="margin-bottom: 20px; color: #6c757d;">All onboarding stages have been completed.
                                You can now mark the entire onboarding process as complete.</p>
                            <button class="btn btn-success btn-lg" id="completeOnboarding">
                                <i class="fa fa-check-circle"></i> Complete Onboarding Process
                            </button>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>

                        <!-- Completion Celebration -->
                        <?php if ($candidate->onboarding_completed_at): ?>
                        <div class="completion-celebration">
                            <h4>🎉 Onboarding Completed Successfully! 🎉</h4>
                            <p>All onboarding stages were completed on
                                <?= date('F j, Y \a\t g:i A', strtotime($candidate->onboarding_completed_at)) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Navigation -->
                        <!-- In your onboarding view file - around line 350+ -->
                        <div class="navigation-actions">
                            <div class="breadcrumb-nav">
                                <a href="<?= redir('candidates', true) ?>"><i class="fa fa-arrow-left"></i> Back to
                                    Candidates List</a>
                            </div>
                            <div>
                                <!-- ADD THIS BUTTON -->
                                <a href="<?= site_url('agency/candidates_list/view/' . $candidate->id) ?>"
                                    class="btn btn-info">
                                    <i class="fa fa-user-circle"></i> View Full Profile
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

<!-- Complete Onboarding Modal -->
<div class="modal fade" id="completeOnboardingModal" tabindex="-1" role="dialog"
    aria-labelledby="completeOnboardingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeOnboardingModalLabel">Complete Onboarding Process</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to mark the entire onboarding process as complete? This will:</p>
                <ul>
                    <li>Set onboarding progress to 100%</li>
                    <li>Record the completion timestamp</li>
                    <li>Mark the candidate as fully onboarded</li>
                </ul>
                <p>This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmCompleteOnboarding">Complete
                    Onboarding</button>
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
                                'An error occurred while updating the stage. Please try again.'
                            );
                        }
                        button.prop('disabled', false).html(originalText);
                        button.closest('.stage-card').removeClass(
                            'loading');
                        console.error('AJAX Error:', error);
                    }
                });
            });
        });

        // Complete onboarding handler
        $('#completeOnboarding').on('click', function(e) {
            e.preventDefault();
            $('#completeOnboardingModal').modal('show');
        });

        // Handle complete onboarding confirmation
        $('#confirmCompleteOnboarding').on('click', function() {
            const button = $(this);
            const candidateId = <?= $candidate->id ?>;

            button.prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> Completing...');

            $.ajax({
                url: '<?= site_url("agency/candidates/complete_onboarding") ?>',
                type: 'POST',
                data: {
                    candidate_id: candidateId
                },
                success: function(response) {
                    $('#completeOnboardingModal').modal('hide');

                    if (response.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Onboarding completed successfully!');
                        } else {
                            alert('Onboarding completed successfully!');
                        }

                        // Reload the page to show the completion state
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Error: ' + response.message);
                        } else {
                            alert('Error: ' + response.message);
                        }
                        button.prop('disabled', false).html('Complete Onboarding');
                    }
                },
                error: function(xhr, status, error) {
                    $('#completeOnboardingModal').modal('hide');
                    if (typeof toastr !== 'undefined') {
                        toastr.error(
                            'An error occurred while completing onboarding. Please try again.'
                        );
                    } else {
                        alert(
                            'An error occurred while completing onboarding. Please try again.'
                        );
                    }
                    console.error('AJAX Error:', error);
                }
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