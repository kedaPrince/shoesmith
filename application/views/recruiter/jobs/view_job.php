<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <!-- Header Section (Breadcrumbs & Title) -->
    <input type="hidden" id="csrf-token" name="<?php echo $this->security->get_csrf_token_name(); ?>"
        value="<?php echo $this->security->get_csrf_hash(); ?>">
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
                        <button type="button" class="btn btn-success" id="submitCandidateBtn">
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
// ====================================================================
// FINAL 100% WORKING SCRIPT FOR JOB VIEW PAGE
// Works perfectly — no more "jobId is not defined", no broken CSS
// ====================================================================

var jobId = <?php echo json_encode($job_id ?? 0); ?>;
var jobUuid = <?php echo json_encode($job_uuid ?? ''); ?>;
var baseUrl = '<?php echo rtrim(site_url(), '/'); ?>/';
var csrfTokenName = <?php echo json_encode($this->security->get_csrf_token_name()); ?>;
var csrfTokenHash = <?php echo json_encode($this->security->get_csrf_hash()); ?>;

// Attach main button
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('submitCandidateBtn');
    if (btn) {
        btn.addEventListener('click', showSubmitCandidateModal);
    }
    console.log('%cJob View Script Loaded Successfully', 'color:#28a745;font-weight:bold', {
        jobId,
        jobUuid
    });
});

// Main modal: New or Existing candidate
function showSubmitCandidateModal() {
    Swal.fire({
        title: 'Submit Candidate',
        html: `
            <div class="text-center py-4">
                <p class="h5 mb-4">How would you like to submit a candidate?</p>
                <div class="row g-4">
                    <div class="col-6">
                        <button class="btn btn-primary btn-lg w-100 py-4 shadow-sm" onclick="goToAddCandidate()">
                            <i class="fa fa-user-plus fa-2x mb-2 d-block"></i>
                            <strong>New Candidate</strong>
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-info btn-lg w-100 py-4 shadow-sm" onclick="loadExistingCandidates()">
                            <i class="fa fa-users fa-2x mb-2 d-block"></i>
                            <strong>Existing Candidate</strong>
                        </button>
                    </div>
                </div>
            </div>
        `,
        width: '600px',
        showCancelButton: true,
        cancelButtonText: 'Close',
        confirmButtonText: 'Cancel',
        customClass: {
            popup: 'rounded-3'
        }
    });
}

function goToAddCandidate() {
    Swal.close();
    window.location.href = baseUrl + 'recruiter/candidates/add/' + jobUuid;
}

function loadExistingCandidates() {
    Swal.fire({
        title: 'Loading your candidates...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // ✅ Get token from hidden input (not hardcoded PHP values)
    const csrfInput = document.getElementById('csrf-token');
    const formData = new FormData();
    formData.append('job_id', jobId);
    formData.append(csrfTokenName, csrfInput.value); // ← Use current token

    fetch(baseUrl + 'recruiter/jobs/ajax_get_candidates_for_job', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            Swal.close();

            // ✅ UPDATE hidden input with new token
            if (res.csrf) {
                csrfInput.value = res.csrf;
            }

            if (!res.success || !res.candidates || res.candidates.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Candidates Found',
                    text: 'You don’t have any candidates available to submit. Want to create one?',
                    showCancelButton: true,
                    confirmButtonText: 'Create New Candidate',
                    cancelButtonText: 'Close'
                }).then(r => r.isConfirmed && goToAddCandidate());
                return;
            }

            showCandidateSelectionModal(res.candidates);
        })
        .catch(() => {
            Swal.fire('Error', 'Failed to load candidates', 'error');
        });
}

function showCandidateSelectionModal(candidates) {
    let html = '';
    candidates.forEach(c => {
        html += `
            <div class="border rounded p-3 mb-2 bg-white shadow-sm">
                <label class="d-flex align-items-center" style="cursor:pointer">
                    <input type="checkbox" class="me-3 mt-1" value="${c.id}">
                    <div>
                        <strong>${c.first_name} ${c.last_name}</strong><br>
                        <small class="text-muted">${c.reference_number || '—'} • ${c.email}</small>
                    </div>
                </label>
            </div>`;
    });

    Swal.fire({
        title: `Select Candidate${candidates.length > 1 ? 's' : ''} to Submit`,
        html: `
            <div style="max-height:420px; overflow-y:auto; padding:5px">
                ${html}
            </div>
            <div class="text-center mt-3">
                <strong>Selected: <span id="selectedCount">0</span></strong>
            </div>
        `,
        width: '700px',
        showCancelButton: true,
        confirmButtonText: 'Submit Selected',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const selected = Array.from(document.querySelectorAll('input[type=checkbox]:checked'))
                .map(cb => cb.value);
            if (selected.length === 0) {
                Swal.showValidationMessage('Please select at least one candidate');
                return false;
            }
            return selected;
        },
        didOpen: () => {
            document.querySelectorAll('input[type=checkbox]').forEach(cb => {
                cb.addEventListener('change', () => {
                    const count = document.querySelectorAll('input[type=checkbox]:checked')
                        .length;
                    document.getElementById('selectedCount').textContent = count;
                });
            });
        }
    }).then(result => {
        if (result.isConfirmed) {
            submitCandidates(result.value);
        }
    });
}

function submitCandidates(candidateIds) {
    Swal.fire({
        title: 'Submitting...',
        text: `Submitting ${candidateIds.length} candidate${candidateIds.length > 1 ? 's' : ''}...`,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // ✅ Always get token from hidden input
    const csrfInput = document.getElementById('csrf-token');
    if (!csrfInput) {
        Swal.close();
        Swal.fire('Error', 'CSRF token missing. Please refresh.', 'error');
        return;
    }

    const formData = new FormData();
    candidateIds.forEach(id => formData.append('candidate_ids[]', id));
    formData.append('job_id', jobId);
    formData.append(csrfTokenName, csrfInput.value); // ← Fresh token

    fetch(baseUrl + 'recruiter/jobs/ajax_assign_candidate_to_job', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server returned ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            Swal.close();

            // ✅ Update token for future requests
            if (data.csrf) {
                csrfInput.value = data.csrf;
            }

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: `${candidateIds.length} candidate${candidateIds.length > 1 ? 's' : ''} submitted!`,
                    confirmButtonText: 'View Candidates'
                }).then(() => {
                    window.location.href = baseUrl + 'recruiter/candidates/for_job/' + jobUuid;
                });
            } else {
                Swal.fire('Error', data.message || 'Assignment failed.', 'error');
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Assignment error:', error);
            Swal.fire('Error', 'Failed to submit candidates. Please refresh the page.', 'error');
        });
}
</script>