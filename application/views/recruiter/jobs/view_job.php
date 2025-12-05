its great that everything is showing as expected by the css is broken
<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <!-- Header Section (Breadcrumbs & Title) -->
    <header class="page-header">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="<?php echo site_url('recruiter/dashboard'); ?>">
                                    <i class="fa fa-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?php echo site_url('recruiter/jobs'); ?>">Jobs</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?php echo htmlspecialchars($job->name); ?>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div id="main-job-layout">

        <!-- Left Navigation Sidebar (20%) -->
        <nav class="job-sidebar-left">
            <div class="sidebar-content">
                <div class="job-actions-card">
                    <h6><i class="fa fa-cog mr-2"></i>Job Actions</h6>
                    <div class="action-buttons">
                        <a href="<?php echo site_url('recruiter/jobs'); ?>" class="btn btn-secondary btn-block">
                            <i class="fa fa-arrow-left"></i> Back to Jobs
                        </a>
                    </div>
                </div>

                <!-- Updated Fields Summary -->
                <?php if (!empty($updated_fields) && is_array($updated_fields)): ?>
                <div class="updated-fields-summary">
                    <h6><i class="fa fa-edit mr-2"></i>Recently Updated</h6>
                    <div class="updated-fields-list">
                        <?php 
                        $field_labels = [
                            'name' => 'Job Title',
                            'reference_number' => 'Reference Number',
                            'department' => 'Department',
                            'employment_type' => 'Employment Type',
                            'description' => 'Job Description',
                            'project_overview' => 'Project Overview',
                            'pay_rate' => 'Pay Rate',
                            'salary_min' => 'Minimum Salary',
                            'salary_max' => 'Maximum Salary',
                            'roster' => 'Roster',
                            'accommodation' => 'Accommodation',
                            'transport' => 'Transport',
                            'is_remote' => 'Remote Work',
                            'industry_id' => 'Industry',
                            'agency_id' => 'Agency',
                            'application_email' => 'Application Email',
                            'application_url' => 'Application URL',
                            'closing_date' => 'Closing Date',
                            'skills' => 'Skills',
                            'qualifications' => 'Qualifications'
                        ];
                        
                        foreach ($updated_fields as $field): 
                            $label = $field_labels[$field] ?? $field;
                        ?>
                        <div class="updated-field-item">
                            <i class="fa fa-pencil-alt text-warning mr-2"></i>
                            <span><?php echo htmlspecialchars($label); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="job-quick-info">
                    <h6><i class="fa fa-info-circle mr-2"></i>Quick Info</h6>
                    <div class="info-list">
                        <div class="info-item">
                            <small>Reference</small>
                            <strong><?php echo htmlspecialchars($job->reference_number); ?></strong>
                        </div>
                        <div class="info-item">
                            <small>Status</small>
                            <span class="badge badge-success">Active</span>
                        </div>
                        <div class="info-item">
                            <small>Type</small>
                            <strong>
                                <?php 
                                $employment_types = [
                                    'full-time' => 'Full Time',
                                    'part-time' => 'Part Time', 
                                    'contract' => 'Contract',
                                    'internship' => 'Internship',
                                    'temporary' => 'Temporary'
                                ];
                                echo $employment_types[$job->employment_type] ?? $job->employment_type;
                                ?>
                            </strong>
                        </div>
                        <div class="info-item">
                            <small>Remote</small>
                            <?php echo $job->is_remote ? 
                                '<span class="badge badge-success">Yes</span>' : 
                                '<span class="badge badge-secondary">No</span>'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Article Content (60%) -->

        <article class="job-main-content">
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1><?php echo htmlspecialchars($job->name); ?></h1>
                        <p class="text-muted">
                            <?php echo htmlspecialchars($job->department ?? 'No department specified'); ?>
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-success" onclick="showSubmitCandidateModal()">
                            <i class="fa fa-user-plus"></i> Submit My Candidate(s)
                        </button>
                        <!-- Update Notification Badge -->
                        <?php if (!empty($updated_fields) && is_array($updated_fields)): ?>
                        <div class="update-notification-badge">
                            <span class="badge badge-warning update-badge">
                                <i class="fa fa-edit"></i> Recently Updated
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Rest of your content continues here... -->

            <!-- Tab Navigation -->
            <div class="job-tabs-navigation">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#project-overview">
                            <i class="fa fa-project-diagram"></i> Project Overview
                            <?php if (in_array('project_overview', $updated_fields ?? [])): ?>
                            <span class="update-indicator"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#job-description">
                            <i class="fa fa-file-alt"></i> Job Description
                            <?php if (in_array('description', $updated_fields ?? [])): ?>
                            <span class="update-indicator"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#requirements">
                            <i class="fa fa-tasks"></i> Requirements
                            <?php if (in_array('skills', $updated_fields ?? []) || in_array('qualifications', $updated_fields ?? [])): ?>
                            <span class="update-indicator"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#application">
                            <i class="fa fa-paper-plane"></i> Application
                            <?php if (in_array('application_email', $updated_fields ?? []) || in_array('application_url', $updated_fields ?? []) || in_array('closing_date', $updated_fields ?? [])): ?>
                            <span class="update-indicator"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="job-tabs-content">
                <div class="tab-content">
                    <!-- Project Overview Tab -->
                    <div class="tab-pane fade show active" id="project-overview" role="tabpanel">
                        <div
                            class="tab-section <?php echo in_array('project_overview', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5><i class="fa fa-project-diagram text-primary mr-2"></i>Project Overview</h5>
                                <?php if (in_array('project_overview', $updated_fields ?? [])): ?>
                                <span class="badge badge-warning update-field-badge">
                                    <i class="fa fa-pencil-alt"></i> Updated
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="project-content">
                                <?php if (!empty($job->project_overview)): ?>
                                <?php echo nl2br(htmlspecialchars($job->project_overview)); ?>
                                <?php else: ?>
                                <p class="text-muted"><em>No project overview provided</em></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Job Description Tab -->
                    <div class="tab-pane fade" id="job-description" role="tabpanel">
                        <div
                            class="tab-section <?php echo in_array('description', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5><i class="fa fa-file-alt text-primary mr-2"></i>Job Description</h5>
                                <?php if (in_array('description', $updated_fields ?? [])): ?>
                                <span class="badge badge-warning update-field-badge">
                                    <i class="fa fa-pencil-alt"></i> Updated
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="description-content">
                                <?php echo !empty($job->description) ? nl2br(htmlspecialchars($job->description)) : '<p class="text-muted"><em>No description provided</em></p>'; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Requirements Tab -->
                    <!-- Requirements Tab -->
                    <div class="tab-pane fade" id="requirements" role="tabpanel">
                        <div class="requirements-grid">
                            <div
                                class="requirement-column <?php echo in_array('skills', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6><i class="fa fa-cogs text-primary"></i> Skills Required</h6>
                                    <?php if (in_array('skills', $updated_fields ?? [])): ?>
                                    <span class="badge badge-warning update-field-badge">
                                        <i class="fa fa-pencil-alt"></i> Updated
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($skills)): ?>
                                <div class="skills-list">
                                    <?php foreach ($skills as $skill): ?>
                                    <?php if (!empty(trim($skill))): ?>
                                    <span class="skill-tag">
                                        <i class="fa fa-check"></i>
                                        <?php echo htmlspecialchars(trim($skill)); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-muted">No specific skills required</p>
                                <?php endif; ?>
                            </div>

                            <div
                                class="requirement-column <?php echo in_array('qualifications', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6><i class="fa fa-graduation-cap text-success"></i> Qualifications</h6>
                                    <?php if (in_array('qualifications', $updated_fields ?? [])): ?>
                                    <span class="badge badge-warning update-field-badge">
                                        <i class="fa fa-pencil-alt"></i> Updated
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($qualifications)): ?>
                                <div class="qualifications-list">
                                    <?php foreach ($qualifications as $qualification): ?>
                                    <?php if (!empty(trim($qualification))): ?>
                                    <span class="qualification-tag">
                                        <i class="fa fa-award"></i>
                                        <?php echo htmlspecialchars(trim($qualification)); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-muted">No specific qualifications required</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Application Tab -->
                    <div class="tab-pane fade" id="application" role="tabpanel">
                        <div class="application-details">
                            <?php if (!empty($job->application_email)): ?>
                            <div
                                class="application-method <?php echo in_array('application_email', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                                <i class="fa fa-envelope fa-2x text-primary"></i>
                                <div class="method-details">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6>Apply via Email</h6>
                                        <?php if (in_array('application_email', $updated_fields ?? [])): ?>
                                        <span class="badge badge-warning update-field-badge">
                                            <i class="fa fa-pencil-alt"></i> Updated
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="mailto:<?php echo htmlspecialchars($job->application_email); ?>"
                                        class="application-link">
                                        <?php echo htmlspecialchars($job->application_email); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($job->application_url)): ?>
                            <div
                                class="application-method <?php echo in_array('application_url', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                                <i class="fa fa-globe fa-2x text-info"></i>
                                <div class="method-details">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6>Apply Online</h6>
                                        <?php if (in_array('application_url', $updated_fields ?? [])): ?>
                                        <span class="badge badge-warning update-field-badge">
                                            <i class="fa fa-pencil-alt"></i> Updated
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($job->application_url); ?>" target="_blank"
                                        class="application-link">
                                        Visit Application Portal
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($job->closing_date)): ?>
                            <div
                                class="closing-date <?php echo in_array('closing_date', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                                <i class="fa fa-calendar fa-2x text-warning"></i>
                                <div class="date-details">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6>Closing Date</h6>
                                        <?php if (in_array('closing_date', $updated_fields ?? [])): ?>
                                        <span class="badge badge-warning update-field-badge">
                                            <i class="fa fa-pencil-alt"></i> Updated
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <span
                                        class="<?php echo strtotime($job->closing_date) < time() ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo date('F j, Y', strtotime($job->closing_date)); ?>
                                        <?php if (strtotime($job->closing_date) < time()): ?>
                                        <small class="d-block">(Expired)</small>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <!-- Right Aside Sidebar (20%) -->
        <aside class="job-sidebar-right">
            <div class="sidebar-content">
                <div class="job-details-card">
                    <h6><i class="fa fa-building mr-2"></i>Company Details</h6>
                    <div class="details-list">
                        <div
                            class="detail-item <?php echo in_array('agency_id', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <small>Agency</small>
                                <?php if (in_array('agency_id', $updated_fields ?? [])): ?>
                                <span class="badge badge-warning update-field-badge-small">
                                    <i class="fa fa-pencil-alt"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                            <strong><?php echo htmlspecialchars($job->agency_name ?? 'Not specified'); ?></strong>
                        </div>
                        <div
                            class="detail-item <?php echo in_array('industry_id', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <small>Industry</small>
                                <?php if (in_array('industry_id', $updated_fields ?? [])): ?>
                                <span class="badge badge-warning update-field-badge-small">
                                    <i class="fa fa-pencil-alt"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                            <strong><?php echo htmlspecialchars($job->industry_name ?? 'Not specified'); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="compensation-card">
                    <h6><i class="fa fa-money-bill-wave mr-2"></i>Compensation</h6>
                    <div class="compensation-details">
                        <?php if (!empty($job->salary_min) || !empty($job->salary_max)): ?>
                        <div
                            class="salary-range <?php echo (in_array('salary_min', $updated_fields ?? []) || in_array('salary_max', $updated_fields ?? [])) ? 'recently-updated' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <small>Salary Range</small>
                                <?php if (in_array('salary_min', $updated_fields ?? []) || in_array('salary_max', $updated_fields ?? [])): ?>
                                <span class="badge badge-warning update-field-badge-small">
                                    <i class="fa fa-pencil-alt"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                            <strong class="text-success">
                                <?php 
                                        if ($job->salary_min && $job->salary_max) {
                                            echo htmlspecialchars($job->salary_min) . ' - ' . htmlspecialchars($job->salary_max);
                                        } elseif ($job->salary_min) {
                                            echo 'From ' . htmlspecialchars($job->salary_min);
                                        } elseif ($job->salary_max) {
                                            echo 'Up to ' . htmlspecialchars($job->salary_max);
                                        }
                                        ?>
                            </strong>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($job->pay_rate)): ?>
                        <div
                            class="pay-rate <?php echo in_array('pay_rate', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <small>Pay Rate</small>
                                <?php if (in_array('pay_rate', $updated_fields ?? [])): ?>
                                <span class="badge badge-warning update-field-badge-small">
                                    <i class="fa fa-pencil-alt"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                            <strong><?php echo htmlspecialchars($job->pay_rate); ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($job->roster) || !empty($job->accommodation) || !empty($job->transport)): ?>
                <div class="additional-info-card">
                    <h6><i class="fa fa-info-circle mr-2"></i>Additional Info</h6>
                    <div class="additional-details">
                        <?php if (!empty($job->roster)): ?>
                        <div
                            class="info-item <?php echo in_array('roster', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <small>Roster</small>
                                <?php if (in_array('roster', $updated_fields ?? [])): ?>
                                <span class="badge badge-warning update-field-badge-small">
                                    <i class="fa fa-pencil-alt"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                            <span><?php echo htmlspecialchars($job->roster); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($job->accommodation)): ?>
                        <div
                            class="info-item <?php echo in_array('accommodation', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <small>Accommodation</small>
                                <?php if (in_array('accommodation', $updated_fields ?? [])): ?>
                                <span class="badge badge-warning update-field-badge-small">
                                    <i class="fa fa-pencil-alt"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                            <span><?php echo htmlspecialchars($job->accommodation); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($job->transport)): ?>
                        <div
                            class="info-item <?php echo in_array('transport', $updated_fields ?? []) ? 'recently-updated' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <small>Transport</small>
                                <?php if (in_array('transport', $updated_fields ?? [])): ?>
                                <span class="badge badge-warning update-field-badge-small">
                                    <i class="fa fa-pencil-alt"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                            <span><?php echo htmlspecialchars($job->transport); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<script>
// Test functions for manual badge testing
function showTestBadges() {
    document.querySelectorAll('.test-badge').forEach(function(badge) {
        badge.style.display = 'inline-block';
    });
    document.querySelectorAll('.update-badge, .update-field-badge, .update-indicator').forEach(function(badge) {
        badge.style.display = 'inline-block';
    });
    console.log('Test badges shown');
}

function hideTestBadges() {
    document.querySelectorAll('.test-badge').forEach(function(badge) {
        badge.style.display = 'none';
    });
    console.log('Test badges hidden');
}

// Check if we should show badges based on updated_fields
document.addEventListener('DOMContentLoaded', function() {
    console.log('Job view loaded with updated fields:', <?php echo json_encode($updated_fields); ?>);

    <?php if (!empty($updated_fields) && is_array($updated_fields)): ?>
    console.log('Updated fields found, badges should be visible');
    <?php else: ?>
    console.log('No updated fields found, badges will be hidden');
    <?php endif; ?>
});
</script>
<script>
// Define global variables
const jobId = <?php echo $job->id; ?>;
const baseUrl = '<?php echo site_url(); ?>';
const csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
const csrfTokenHash = '<?php echo $this->security->get_csrf_hash(); ?>';

// Use Jobs controller URLs
const getCandidatesUrl = baseUrl + 'recruiter/jobs/ajax_get_candidates_for_job';
const assignCandidateUrl = baseUrl + 'recruiter/jobs/ajax_assign_candidate_to_job';

function showSubmitCandidateModal() {
    Swal.fire({
        title: 'Submit Candidate',
        html: `
            <div class="text-center">
                <p class="mb-4">How would you like to submit a candidate for this job?</p>
                <div class="row">
                    <div class="col-6">
                        <button type="button" class="btn btn-primary btn-block py-3" onclick="submitNewCandidate()">
                            <i class="fa fa-user-plus fa-2x mb-2"></i><br>
                            New Candidate
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-info btn-block py-3" onclick="showExistingCandidateModal()">
                            <i class="fa fa-users fa-2x mb-2"></i><br>
                            Existing Candidate
                        </button>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#6c757d',
        confirmButtonText: 'Cancel',
        showConfirmButton: true,
        cancelButtonText: 'Close',
        width: '600px'
    });
}

function submitNewCandidate() {
    Swal.close();
    window.location.href = baseUrl + 'recruiter/candidates/add/' + jobId;
}

function showExistingCandidateModal() {
    Swal.close();

    Swal.fire({
        title: 'Loading Candidates...',
        text: 'Please wait while we load your candidates',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const formData = new FormData();
    formData.append('job_id', jobId);
    formData.append(csrfTokenName, csrfTokenHash);

    fetch(getCandidatesUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            Swal.close();
            if (data.success && data.candidates && data.candidates.length > 0) {
                showCandidateSelectionModal(data.candidates);
            } else {
                Swal.fire({
                    title: 'No Candidates Found',
                    html: `<div class="text-center">
                    <i class="fa fa-users fa-3x text-muted mb-3"></i>
                    <p>${data.message || 'No candidates found.'}</p>
                    <p>Would you like to create a new candidate instead?</p>
                </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Create New Candidate',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) submitNewCandidate();
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error fetching candidates:', error);
            Swal.fire({
                title: 'Error',
                text: 'Failed to load candidates. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}

function showCandidateSelectionModal(candidates) {
    let optionsHtml = candidates.map(candidate =>
        `<div class="candidate-option">
            <input type="checkbox" id="candidate_${candidate.id}" name="candidates[]" value="${candidate.id}" class="candidate-checkbox">
            <label for="candidate_${candidate.id}" class="candidate-label">
                <strong>${candidate.first_name} ${candidate.last_name}</strong>
                <br>
                <small class="text-muted">${candidate.reference_number} • ${candidate.email}</small>
            </label>
        </div>`
    ).join('');

    Swal.fire({
        title: 'Select Candidates to Assign',
        html: `
            <div class="text-left">
                <p class="mb-3">Choose one or more candidates to assign to this job:</p>
                <div class="candidates-list" style="max-height: 400px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px;">
                    ${optionsHtml}
                </div>
                <div class="mt-3 text-muted small">
                    <i class="fa fa-info-circle"></i> You can select multiple candidates.
                </div>
                <div class="mt-2 selected-count text-primary" style="font-weight: 600;">
                    Selected: 0 candidates
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Assign Selected Candidates',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#17a2b8',
        width: '700px',
        preConfirm: () => {
            const selectedCandidates = Array.from(document.querySelectorAll('.candidate-checkbox:checked'))
                .map(checkbox => checkbox.value);
            if (selectedCandidates.length === 0) {
                Swal.showValidationMessage('Please select at least one candidate');
                return false;
            }
            return selectedCandidates;
        },
        didOpen: () => {
            const checkboxes = document.querySelectorAll('.candidate-checkbox');
            const selectedCount = document.querySelector('.selected-count');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const selected = document.querySelectorAll(
                        '.candidate-checkbox:checked').length;
                    selectedCount.textContent =
                        `Selected: ${selected} candidate${selected !== 1 ? 's' : ''}`;
                });
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            assignCandidatesToJob(result.value);
        }
    });
}

// FIXED: This is the function that's being called - make sure the name matches
function assignCandidatesToJob(candidateIds) {
    if (!Array.isArray(candidateIds) || candidateIds.length === 0) {
        Swal.fire('Error', 'No candidates selected', 'error');
        return;
    }

    Swal.fire({
        title: 'Assigning Candidates...',
        html: `Assigning ${candidateIds.length} candidate${candidateIds.length !== 1 ? 's' : ''} to job<br><small>Please wait</small>`,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const formData = new FormData();
    candidateIds.forEach(id => formData.append('candidate_ids[]', id));
    formData.append('job_id', jobId);
    formData.append(csrfTokenName, csrfTokenHash);

    fetch(assignCandidateUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    html: `Successfully assigned ${candidateIds.length} candidate${candidateIds.length !== 1 ? 's' : ''} to this job.`,
                    icon: 'success',
                    confirmButtonText: 'View Candidates'
                }).then(() => {
                    window.location.href = baseUrl + 'recruiter/candidates/for_job/' + jobId;
                });
            } else {
                let errorMessage = data.message || 'Failed to assign candidates to job.';
                if (data.errors) errorMessage += '\n' + data.errors.join('\n');
                Swal.fire('Error', errorMessage, 'error');
            }
        })
        .catch(error => {
            console.error('Error assigning candidates:', error);
            Swal.fire('Error', 'Failed to assign candidates. Please try again.', 'error');
        });
}

// Make functions globally available
window.showSubmitCandidateModal = showSubmitCandidateModal;
window.submitNewCandidate = submitNewCandidate;
window.showExistingCandidateModal = showExistingCandidateModal;
window.showCandidateSelectionModal = showCandidateSelectionModal;
window.assignCandidatesToJob = assignCandidatesToJob;
</script>