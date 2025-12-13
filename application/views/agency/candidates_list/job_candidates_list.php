<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <input type="hidden" id="csrf-token" name="<?php echo $this->security->get_csrf_token_name(); ?>"
        value="<?php echo $this->security->get_csrf_hash(); ?>">

    <header class="page-header">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="<?php echo site_url('agency/dashboard'); ?>">
                                    <i class="fa fa-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?php echo site_url('agency/jobs_listings'); ?>">Jobs</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?php echo site_url('agency/jobs_listings/view/' . $job->uuid); ?>">
                                    <?php echo htmlspecialchars($job->name); ?>
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                All Candidates (<?php echo $total_candidates; ?>)
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <!-- Job Details Header Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="card-title mb-3">
                            <i class="fa fa-briefcase me-2"></i>
                            <?php echo htmlspecialchars($job->name); ?>
                        </h4>

                        <div class="row">
                            <?php if (!empty($job->department)): ?>
                            <div class="col-sm-6 col-md-4 mb-2">
                                <strong>Department:</strong>
                                <span class="text-muted"><?php echo htmlspecialchars($job->department); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($job->employment_type)): ?>
                            <div class="col-sm-6 col-md-4 mb-2">
                                <strong>Employment Type:</strong>
                                <span class="text-muted"><?php echo htmlspecialchars($job->employment_type); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($job->industry_name)): ?>
                            <div class="col-sm-6 col-md-4 mb-2">
                                <strong>Industry:</strong>
                                <span class="text-muted"><?php echo htmlspecialchars($job->industry_name); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($job->location)): ?>
                            <div class="col-sm-6 col-md-4 mb-2">
                                <strong>Location:</strong>
                                <span class="text-muted"><?php echo htmlspecialchars($job->location); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($job->reference_number)): ?>
                            <div class="col-sm-6 col-md-4 mb-2">
                                <strong>Reference:</strong>
                                <span class="text-muted"><?php echo htmlspecialchars($job->reference_number); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($job->agency_name)): ?>
                            <div class="col-sm-6 col-md-4 mb-2">
                                <strong>Agency:</strong>
                                <span class="text-muted"><?php echo htmlspecialchars($job->agency_name); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex flex-column align-items-end h-100">
                            <div class="btn-group-vertical w-100">

                                <a href="<?php echo site_url('agency/jobs_listings'); ?>" class="btn btn-info mb-2">
                                    <i class="fa fa-arrow-left me-2"></i> Back to Jobs
                                </a>
                                <a href="<?php echo site_url('agency/candidates'); ?>"
                                    class="btn btn-outline-secondary mt-2">
                                    <i class="fa fa-users me-2"></i> View All Candidates
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Candidates List Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fa fa-users me-2"></i>
                    All Candidates for: <?php echo htmlspecialchars($job->name); ?>
                    <span class="badge bg-primary ms-2"><?php echo $total_candidates; ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($candidates) && $total_candidates > 0): ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    Showing all candidates submitted by recruiters for this job.
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Reference</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Application Date</th>
                                <th>Onboarding Stage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $candidate): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($candidate->reference_number); ?></td>
                                <td><?php echo htmlspecialchars($candidate->email); ?></td>
                                <td><?php echo htmlspecialchars($candidate->phone ?? 'N/A'); ?></td>
                                <td>
                                    <?php 
                                    $status_badge = [
                                        'new' => 'secondary',
                                        'reviewed' => 'info',
                                        'shortlisted' => 'warning',
                                        'interviewed' => 'primary',
                                        'hired' => 'success',
                                        'rejected' => 'danger',
                                        'on_hold' => 'dark'
                                    ];
                                    $badge_class = $status_badge[$candidate->status] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                        <?php echo ucfirst($candidate->status); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($candidate->application_date)); ?></td>
                                <td>
                                    <?php
                                    $onboarding_badge = [
                                        'not_started' => 'secondary',
                                        'stage_under_review' => 'info',
                                        'stage_submitted_to_hm' => 'primary',
                                        'stage_requested_docs' => 'warning',
                                        'stage_position_offered' => 'success',
                                        'completed' => 'success'
                                    ];
                                    $onboarding_class = $onboarding_badge[$candidate->onboarding_stage] ?? 'secondary';
                                    $onboarding_label = str_replace('_', ' ', $candidate->onboarding_stage);
                                    $onboarding_label = ucwords($onboarding_label);
                                    ?>
                                    <span class="badge bg-<?php echo $onboarding_class; ?>">
                                        <?php echo $onboarding_label; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?php echo site_url('agency/candidates/view/' . $candidate->uuid); ?>"
                                            class="btn btn-info" title="View Candidate">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="<?php echo site_url('agency/candidates/onboarding/' . $candidate->uuid); ?>"
                                            class="btn btn-warning" title="Onboarding">
                                            <i class="fa fa-tasks"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="btn btn-danger" title="Remove from job"
                                            onclick="confirmRemoveCandidate('<?php echo $candidate->uuid; ?>', 
                                            '<?php echo htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fa fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Candidates Found</h5>
                    <p class="text-muted">No candidates have been submitted for this job yet.</p>
                    <p class="text-muted small mb-3">
                        <i class="fa fa-info-circle"></i>
                        Recruiters can submit candidates for this job.
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize variables
var jobUuid = <?php echo json_encode($job->uuid ?? ''); ?>;
var baseUrl = '<?php echo rtrim(site_url(), '/'); ?>/';
var csrfTokenName = <?php echo json_encode($this->security->get_csrf_token_name()); ?>;

function confirmRemoveCandidate(candidateUuid, candidateName) {
    Swal.fire({
        title: 'Confirm Removal',
        html: `Are you sure you want to remove <strong>${candidateName}</strong> from this job?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const csrfInput = document.getElementById('csrf-token');
            if (!csrfInput) {
                Swal.showValidationMessage('CSRF token missing');
                return false;
            }

            const formData = new FormData();
            formData.append('candidate_uuid', candidateUuid);
            formData.append('job_uuid', jobUuid);
            formData.append(csrfTokenName, csrfInput.value);

            return fetch(baseUrl + 'agency/candidates_list/remove_candidate_from_job', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Server error');
                    return response.json();
                })
                .then(data => {
                    if (data.csrf_token) {
                        csrfInput.value = data.csrf_token;
                    }
                    return data;
                });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Candidate removed from job successfully',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.value.message || 'Failed to remove candidate'
                });
            }
        }
    });
}
</script>

<style>
/* Custom styles */
.me-2 {
    margin-right: 0.5rem !important;
}

.ms-2 {
    margin-left: 0.5rem !important;
}

.btn-group-vertical .btn {
    text-align: left;
}

.table td,
.table th {
    vertical-align: middle;
}

.badge {
    font-size: 0.85em;
    padding: 0.4em 0.8em;
}

.btn-group-sm>.btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Hover effects */
.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

/* Action buttons styling */
.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>