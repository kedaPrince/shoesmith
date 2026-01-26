<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<div id="main-content">
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2><?php echo $heading; ?></h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= site_url('recruiter/dashboard') ?>"><i class="fa fa-dashboard"></i></a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('recruiter/candidates') ?>">Candidates</a></li>
                        <li class="breadcrumb-item active">Contact Requests</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div class="header">
                        <h2>
                            <i class="fa fa-envelope"></i> Pending Contact Information Requests
                            <span class="badge badge-warning ml-2"><?php echo count($requests); ?></span>
                        </h2>
                    </div>
                    <div class="body">
                        <?php if (empty($requests)): ?>
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i> No pending contact information requests.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Candidate</th>
                                            <th>Agency</th>
                                            <th>Requested By</th>
                                            <th>Requested On</th>
                                            <th>Notes</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requests as $request): ?>
                                        <tr id="request-<?php echo $request->id; ?>">
                                            <td>
                                                <strong><?php echo htmlspecialchars($request->first_name . ' ' . $request->last_name); ?></strong><br>
                                                <small class="text-muted">Ref: <?php echo htmlspecialchars($request->reference_number); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($request->agency_name); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($request->agency_email); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($request->requested_by_first_name): ?>
                                                    <?php echo htmlspecialchars($request->requested_by_first_name . ' ' . $request->requested_by_last_name); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($request->created_at)); ?><br>
                                                <small class="text-muted"><?php echo date('g:i A', strtotime($request->created_at)); ?></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($request->notes)): ?>
                                                    <button class="btn btn-sm btn-info" onclick="viewRequestNotes(<?php echo $request->id; ?>)">
                                                        <i class="fa fa-eye"></i> View Notes
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">No notes</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-success" onclick="grantAccess(<?php echo $request->id; ?>, <?php echo $request->candidate_id; ?>)">
                                                    <i class="fa fa-check"></i> Grant
                                                </button>
                                                <button class="btn btn-sm btn-danger ml-1" onclick="denyAccess(<?php echo $request->id; ?>, <?php echo $request->candidate_id; ?>)">
                                                    <i class="fa fa-times"></i> Deny
                                                </button>
                                                <a href="<?php echo site_url('recruiter/candidates/contact_requests/' . $request->candidate_id); ?>" class="btn btn-sm btn-info ml-1">
                                                    <i class="fa fa-list"></i> All Requests
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include SweetAlert -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// CSRF token for AJAX requests
var csrf_token_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrf_token = '<?php echo $csrf_token; ?>';

function viewRequestNotes(requestId) {
    $.ajax({
        url: '<?php echo site_url("recruiter/candidates/get_request_details"); ?>/' + requestId,
        type: 'GET',
        success: function(response) {
            if (response.success && response.request) {
                var request = response.request;
                
                Swal.fire({
                    title: 'Request Details',
                    html: `
                        <div class="text-left">
                            <p><strong>Candidate:</strong> ${request.first_name} ${request.last_name} (${request.reference_number})</p>
                            <p><strong>Agency:</strong> ${request.agency_name}</p>
                            <p><strong>Requested By:</strong> ${request.requested_by_first_name || 'N/A'} ${request.requested_by_last_name || ''}</p>
                            <p><strong>Requested On:</strong> ${formatDate(request.created_at)}</p>
                            <p><strong>Request Notes:</strong></p>
                            <div class="alert alert-info">${request.notes || 'No notes provided'}</div>
                            <p><strong>Times Requested:</strong> ${request.request_count || 1}</p>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: 'Close'
                });
            } else {
                Swal.fire('Error', response.message || 'Failed to load request details', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to load request details', 'error');
        }
    });
}

function grantAccess(requestId, candidateId) {
    Swal.fire({
        title: 'Grant Access',
        html: `
            <p>You are about to grant contact information access to this agency.</p>
            <p>Optional: Add notes for the agency (e.g., "Please use this information responsibly"):</p>
            <textarea id="grant-notes" class="form-control" rows="3" 
                      placeholder="Optional notes for the agency..."></textarea>
        `,
        showCancelButton: true,
        confirmButtonText: 'Grant Access',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        preConfirm: () => {
            return {
                notes: $('#grant-notes').val()
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo site_url("recruiter/candidates/grant_contact_access"); ?>',
                type: 'POST',
                data: {
                    request_id: requestId,
                    candidate_id: candidateId,
                    notes: result.value.notes,
                    [csrf_token_name]: csrf_token
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Access Granted',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Remove the row from the table
                        $('#request-' + requestId).fadeOut(500, function() {
                            $(this).remove();
                        });
                        
                        // Update notification count
                        updateNotificationCount();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to process request. Please try again.'
                    });
                }
            });
        }
    });
}

function denyAccess(requestId, candidateId) {
    Swal.fire({
        title: 'Deny Access',
        html: `
            <p>You are about to deny contact information access to this agency.</p>
            <p>Please provide a reason for denial (this will be sent to the agency):</p>
            <textarea id="deny-reason" class="form-control" rows="3" 
                      placeholder="Reason for denying access..." required></textarea>
        `,
        showCancelButton: true,
        confirmButtonText: 'Deny Access',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        preConfirm: () => {
            var reason = $('#deny-reason').val();
            if (!reason || reason.trim() === '') {
                Swal.showValidationMessage('Please provide a reason for denial');
                return false;
            }
            return { reason: reason.trim() };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo site_url("recruiter/candidates/deny_contact_access"); ?>',
                type: 'POST',
                data: {
                    request_id: requestId,
                    candidate_id: candidateId,
                    reason: result.value.reason,
                    [csrf_token_name]: csrf_token
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Access Denied',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Remove the row from the table
                        $('#request-' + requestId).fadeOut(500, function() {
                            $(this).remove();
                        });
                        
                        // Update notification count
                        updateNotificationCount();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to process request. Please try again.'
                    });
                }
            });
        }
    });
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    var date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function updateNotificationCount() {
    // This function would update the notification badge count
    // You can implement this based on your notification system
    console.log('Request processed, update notification count if needed');
}
</script>