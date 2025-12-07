<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
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
                            <i class="fa fa-briefcase mr-2"></i>
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
                                <!-- Add Candidate to this Job Button -->
                                <button type="button" class="btn btn-primary mb-2" onclick="showSubmitCandidateModal()">
                                    <i class="fa fa-user-plus mr-2"></i> Add Candidate to this Job
                                </button>

                                <!-- View Job Details Button -->
                                <a href="<?php echo site_url('recruiter/jobs/view/' . $job->uuid); ?>"
                                    class="btn btn-info mb-2">
                                    <i class="fa fa-eye mr-2"></i> View Job Details
                                </a>

                                <!-- Back to Jobs Button -->
                                <a href="<?php echo site_url('recruiter/jobs'); ?>" class="btn btn-outline-secondary">
                                    <i class="fa fa-arrow-left mr-2"></i> Back to Jobs
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
                    <i class="fa fa-users mr-2"></i>
                    My Candidates for: <?php echo htmlspecialchars($job->name); ?>
                    <span class="badge badge-primary ml-2"><?php echo $total_candidates; ?></span>
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
                                    <span class="badge badge-<?php 
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
                                    <!-- In the table row -->
                                    <!-- In the table row - make sure it passes job->uuid -->
                                    <a href="javascript:void(0);" class="btn btn-sm btn-danger"
                                        title="Remove from this job" onclick="confirmRemoveCandidate(<?php echo $candidate->id; ?>, 
                                        '<?php echo htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name, ENT_QUOTES, 'UTF-8'); ?>', 
                                        '<?php echo $job->uuid; ?>')">
                                        <!-- ← Make sure this is $job->uuid -->
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
                    <!-- Updated button to show modal instead of redirecting -->
                    <button type="button" class="btn btn-primary" onclick="showSubmitCandidateModal()">
                        <i class="fa fa-user-plus"></i> Submit My Candidate(s)
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add the JavaScript for the modal functionality -->
<script>
// Define jobId at the top to avoid PHP in JavaScript string issues
const jobUuid = '<?php echo $job->uuid; ?>';
const baseUrl = '<?php echo site_url(); ?>';
const csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
const csrfTokenHash = '<?php echo $this->security->get_csrf_hash(); ?>';
const recruiterId = '<?php echo $current_recruiter_id; ?>';

// Use Jobs controller URLs instead of Candidates
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
        customClass: {
            confirmButton: 'swal2-confirm swal2-styled',
            cancelButton: 'swal2-cancel swal2-styled'
        },
        width: '600px'
    });
}

function submitNewCandidate() {
    // Close the current modal
    Swal.close();

    // Redirect to add candidate page with job ID
    window.location.href = baseUrl + 'recruiter/candidates/add/' + jobId;
}

function showExistingCandidateModal() {
    // First close the current modal
    Swal.close();

    console.log('Making AJAX request to:', getCandidatesUrl);
    console.log('Job ID:', jobId);

    // Show loading state while fetching candidates
    Swal.fire({
        title: 'Loading Candidates...',
        text: 'Please wait while we load your candidates',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Create form data
    const formData = new FormData();
    formData.append('job_id', jobId);
    formData.append(csrfTokenName, csrfTokenHash);

    // Fetch existing candidates via AJAX with proper headers
    fetch(getCandidatesUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', text);
                    throw new Error('Invalid JSON response from server');
                }
            });
        })
        .then(data => {
            console.log('AJAX response data:', data);
            Swal.close();

            if (data.success && data.candidates && data.candidates.length > 0) {
                showCandidateSelectionModal(data.candidates);
            } else {
                let message = data.message || 'No candidates found.';
                Swal.fire({
                    title: 'No Candidates Found',
                    html: `
                    <div class="text-center">
                        <i class="fa fa-users fa-3x text-muted mb-3"></i>
                        <p>${message}</p>
                        <p>Would you like to create a new candidate instead?</p>
                    </div>
                `,
                    showCancelButton: true,
                    confirmButtonText: 'Create New Candidate',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#28a745',
                    customClass: {
                        confirmButton: 'swal2-confirm swal2-styled',
                        cancelButton: 'swal2-cancel swal2-styled'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitNewCandidate();
                    }
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error fetching candidates:', error);

            let errorMessage = 'Failed to load candidates. Please try again.';
            if (error.message.includes('404')) {
                errorMessage = 'AJAX endpoint not found (404). Please contact support.';
            } else if (error.message.includes('Invalid JSON')) {
                errorMessage = 'Server returned an invalid response. Please try again.';
            }

            Swal.fire({
                title: 'Error',
                text: errorMessage,
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
                    <i class="fa fa-info-circle"></i> You can select multiple candidates. Only showing candidates not already assigned to this job.
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
        customClass: {
            confirmButton: 'swal2-confirm swal2-styled',
            cancelButton: 'swal2-cancel swal2-styled'
        },
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
            // Add event listeners to update selected count
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

function assignCandidatesToJob(candidateIds) {
    if (!Array.isArray(candidateIds) || candidateIds.length === 0) {
        console.error('No candidate IDs provided');
        return;
    }

    // Show loading state
    Swal.fire({
        title: 'Assigning Candidates...',
        html: `Assigning ${candidateIds.length} candidate${candidateIds.length !== 1 ? 's' : ''} to job<br><small>Please wait</small>`,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Create form data
    const formData = new FormData();
    candidateIds.forEach(id => {
        formData.append('candidate_ids[]', id);
    });
    formData.append('job_id', jobId);
    formData.append(csrfTokenName, csrfTokenHash);

    // Assign candidates to job via AJAX
    fetch(assignCandidateUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', text);
                    throw new Error('Invalid JSON response from server');
                }
            });
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    html: `Successfully assigned ${candidateIds.length} candidate${candidateIds.length !== 1 ? 's' : ''} to this job.`,
                    icon: 'success',
                    confirmButtonText: 'View Candidates'
                }).then(() => {
                    // Refresh the page to show the new candidates
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'Failed to assign candidates to job.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error assigning candidates:', error);
            Swal.fire({
                title: 'Error',
                text: 'Failed to assign candidates. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}

function confirmRemoveCandidate(candidateId, candidateName, jobUuid) { // ← FIX: Changed to jobUuid
    // Get CSRF token
    const csrfInput = document.querySelector('input[name="csrf_rfid_token"]');
    if (!csrfInput) {
        console.error("CSRF token input not found!");
        alert("Error: CSRF token not found. Please refresh the page.");
        return;
    }

    const csrfToken = csrfInput.value;
    console.log("CSRF Token:", csrfToken);

    Swal.fire({
        title: 'Confirm Removal',
        text: 'Are you sure you want to remove ' + candidateName + ' from this job?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'swal2-confirm swal2-styled',
            cancelButton: 'swal2-cancel swal2-styled'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // ✅ FIX: Use jobUuid parameter (not undefined variable)
            // ✅ FIX: Add CSRF token to URL
            const url = '<?php echo site_url("recruiter/candidates/remove_from_job/"); ?>' +
                candidateId + '/' + jobUuid +
                '?csrf_rfid_token=' + encodeURIComponent(csrfToken);

            console.log("Redirecting to:", url);
            window.location.href = url;
        }
    });
}
// Make sure the function is available globally
window.showSubmitCandidateModal = showSubmitCandidateModal;
window.submitNewCandidate = submitNewCandidate;
window.showExistingCandidateModal = showExistingCandidateModal;
window.showCandidateSelectionModal = showCandidateSelectionModal;
window.assignCandidatesToJob = assignCandidatesToJob;
</script>

<!-- Add the CSS for the modal styling -->
<style>
/* Multi-select Candidate Styles */
.candidate-option {
    padding: 12px 15px;
    margin: 8px 0;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
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
    margin-right: 12px;
    transform: scale(1.2);
}

.candidate-label {
    cursor: pointer;
    margin: 0;
    flex: 1;
}

.candidates-list {
    scrollbar-width: thin;
    scrollbar-color: #17a2b8 #f1f1f1;
}

.candidates-list::-webkit-scrollbar {
    width: 6px;
}

.candidates-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.candidates-list::-webkit-scrollbar-thumb {
    background: #17a2b8;
    border-radius: 3px;
}

.candidates-list::-webkit-scrollbar-thumb:hover {
    background: #138496;
}

.selected-count {
    padding: 8px 12px;
    background: #e7f7ff;
    border-radius: 6px;
    border-left: 4px solid #17a2b8;
}

/* Submit Candidate Button Styles */
.btn-success,
.btn-primary {
    position: relative;
    z-index: 10;
    cursor: pointer !important;
    pointer-events: auto !important;
}

.btn-success:hover,
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

/* Swal Modal Customizations */
.swal2-popup {
    border-radius: 15px !important;
}

.swal2-title {
    color: #2C3639 !important;
    font-weight: 700 !important;
}

.swal2-confirm {
    border-radius: 8px !important;
    font-weight: 600 !important;
}

.swal2-cancel {
    border-radius: 8px !important;
    font-weight: 600 !important;
}

/* Job Details Header Styles */
.card-title {
    color: #ffffffff;
    font-weight: 600;
}

.btn-group-vertical .btn {
    text-align: left;
    justify-content: flex-start;
}

.btn-group-vertical .btn i {
    margin-right: 8px;
    width: 16px;
    text-align: center;
}
</style>