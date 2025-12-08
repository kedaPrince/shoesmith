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
                                <a href="<?php echo site_url('recruiter/dashboard'); ?>">
                                    <i class="fa fa-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?php echo site_url('recruiter/jobs'); ?>">Jobs</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?php echo site_url('recruiter/jobs/view/' . $job->id); ?>">
                                    <?php echo htmlspecialchars($job->name); ?>
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                My Candidates (<?php echo $total_candidates; ?>)
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

                            <?php if (!empty($job->industry)): ?>
                            <div class="col-sm-6 col-md-4 mb-2">
                                <strong>Industry:</strong>
                                <span class="text-muted"><?php echo htmlspecialchars($job->industry); ?></span>
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
                                <button type="button" class="btn btn-primary mb-2" onclick="showSubmitCandidateModal()">
                                    <i class="fa fa-user-plus me-2"></i> Add Candidate to this Job
                                </button>
                                <a href="<?php echo site_url('recruiter/jobs/view/' . $job->uuid); ?>"
                                    class="btn btn-info mb-2">
                                    <i class="fa fa-eye me-2"></i> View Job Details
                                </a>
                                <a href="<?php echo site_url('recruiter/jobs'); ?>" class="btn btn-outline-secondary">
                                    <i class="fa fa-arrow-left me-2"></i> Back to Jobs
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
                    My Candidates for: <?php echo htmlspecialchars($job->name); ?>
                    <span class="badge bg-primary ms-2"><?php echo $total_candidates; ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($candidates) && $total_candidates > 0): ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    Showing only your candidates for this job. Other recruiters may have submitted their own candidates.
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Reference</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Assigned Date</th>
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
                                    <span class="badge bg-<?php 
                                                switch($candidate->assignment_status) {
                                                    case 'submitted': echo 'info'; break;
                                                    case 'shortlisted': echo 'success'; break;
                                                    case 'rejected': echo 'danger'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                        <?php echo ucfirst($candidate->assignment_status); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($candidate->assigned_at)); ?></td>
                                <td>
                                    <a href="<?php echo site_url('recruiter/candidates/view/' . $candidate->id); ?>"
                                        class="btn btn-sm btn-info" title="View Candidate">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-sm btn-danger"
                                        title="Remove from this job" onclick="confirmRemoveCandidate(<?php echo $candidate->id; ?>, 
                                        '<?php echo htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name, ENT_QUOTES, 'UTF-8'); ?>', 
                                        '<?php echo $job->uuid; ?>')">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fa fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Candidates Submitted</h5>
                    <p class="text-muted">You haven't submitted any candidates for this job yet.</p>
                    <p class="text-muted small mb-3">
                        <i class="fa fa-info-circle"></i>
                        Other recruiters may have submitted candidates, but you can only see your own submissions.
                    </p>
                    <button type="button" class="btn btn-primary" onclick="showSubmitCandidateModal()">
                        <i class="fa fa-user-plus"></i> Submit My Candidate(s)
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Use the SAME variables as Job View
var jobId = <?php echo json_encode($job_id ?? 0); ?>;
var jobUuid = <?php echo json_encode($job_uuid ?? ''); ?>;
var baseUrl = '<?php echo rtrim(site_url(), '/'); ?>/';
var csrfTokenName = <?php echo json_encode($this->security->get_csrf_token_name()); ?>;

// No DOMContentLoaded needed — buttons use onclick

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

    const csrfInput = document.getElementById('csrf-token');
    const formData = new FormData();
    formData.append('job_id', jobId);
    formData.append(csrfTokenName, csrfInput.value);

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

function confirmRemoveCandidate(candidateId, candidateName, jobUuid) {
    Swal.fire({
        title: 'Confirm Removal',
        text: 'Are you sure you want to remove ' + candidateName + ' from this job?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Build URL with CSRF token from hidden input
            const csrfInput = document.getElementById('csrf-token');
            const csrfToken = csrfInput ? csrfInput.value : '';
            const url = baseUrl + 'recruiter/candidates/remove_from_job/' +
                candidateId + '/' + jobUuid +
                '?' + csrfTokenName + '=' + encodeURIComponent(csrfToken);
            window.location.href = url;
        }
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

    const csrfInput = document.getElementById('csrf-token');
    if (!csrfInput) {
        Swal.close();
        Swal.fire('Error', 'CSRF token missing. Please refresh.', 'error');
        return;
    }

    const formData = new FormData();
    candidateIds.forEach(id => formData.append('candidate_ids[]', id));
    formData.append('job_id', jobId);
    formData.append(csrfTokenName, csrfInput.value);

    fetch(baseUrl + 'recruiter/jobs/ajax_assign_candidate_to_job', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Server returned ' + response.status);
            return response.json();
        })
        .then(data => {
            Swal.close();

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
                    window.location.reload(); // ← Reload current page (candidate list)
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

<style>
/* Multi-select Candidate Styles */
.candidate-option {
    padding: 12px 15px;
    margin: 8px 0;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background: white;
}

.candidate-option:hover {
    border-color: #17a2b8;
    background: #f8f9fa;
}

.candidate-option:has(.candidate-checkbox:checked) {
    border-color: #17a2b8;
    background: #e7f7ff;
}

.candidate-checkbox {
    transform: scale(1.2);
}

.candidates-list::-webkit-scrollbar {
    width: 6px;
}

.candidates-list::-webkit-scrollbar-thumb {
    background: #17a2b8;
    border-radius: 3px;
}

.selected-count {
    padding: 8px 12px;
    background: #e7f7ff;
    border-radius: 6px;
    border-left: 4px solid #17a2b8;
}

/* Bootstrap 5 Spacing Fix */
.me-2 {
    margin-right: 0.5rem !important;
}

.ms-2 {
    margin-left: 0.5rem !important;
}
</style>