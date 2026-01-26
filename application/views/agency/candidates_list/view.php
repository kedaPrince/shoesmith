<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<style>
.bg-secondary {
    background-color: #59c4bc26 !important;
}
</style>
<?php 
// Check if candidate is assigned to current agency
$current_agency_id = $this->session->userdata('login')['agency']['agency_id'] ?? null;
$candidate_id = $candidate->id;

// Load the model to check contact access
$this->load->model('agency/Model_candidates');

// Check if agency has access to contact info
$has_contact_access = false;
if ($current_agency_id) {
    // Check candidate agency access
    $this->db->select('1');
    $this->db->from('candidate_agencies');
    $this->db->where('candidate_id', $candidate_id);
    $this->db->where('agency_id', $current_agency_id);
    $has_access = $this->db->get()->row() !== null;
    
    echo "<!-- DEBUG: Has Access: " . ($has_access ? 'YES' : 'NO') . " -->";
    
    // Check contact information access
    $has_contact_access = $this->Model_candidates->check_contact_access($candidate_id, $current_agency_id);
    
    if (!$has_access) {
        // Show warning
        echo '<div class="alert alert-danger">';
        echo '<strong>SECURITY WARNING:</strong> You are viewing a candidate not assigned to your agency!';
        echo '</div>';
    }
}
?>
<div id="main-content">
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Candidate Details</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= site_url('agency/dashboard') ?>"><i class="fa fa-dashboard"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= site_url('agency/jobs_listings') ?>" title="Jobs Listings">Jobs Listings</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= site_url('agency/candidates_list/index/' . $candidate->job_id) ?>"
                                title="Candidates">Candidates</a>
                        </li>
                        <li class="breadcrumb-item active">Candidate Details</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div class="header">
                        <h2>
                            <i class="fa fa-user"></i>
                            <?= htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name, ENT_QUOTES, 'UTF-8'); ?>
                            <small>Reference:
                                <?= htmlspecialchars($candidate->reference_number, ENT_QUOTES, 'UTF-8') ?></small>
                        </h2>
                        <ul class="header-dropdown">
                            <li>
                                <span class="badge badge-<?= get_status_badge($candidate->status) ?>">
                                    <?= ucfirst($candidate->status) ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="body">
                        <!-- Contact Information Card (with masking) -->
                        <div class="row clearfix">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="header">
                                        <h2>
                                            <i class="fa fa-address-card"></i> Contact Information
                                            <?php if (!$has_contact_access): ?>
                                            <span class="badge badge-warning ml-2">
                                                <i class="fa fa-lock"></i> Restricted
                                            </span>
                                            <?php else: ?>
                                            <span class="badge badge-success ml-2">
                                                <i class="fa fa-check"></i> Access Granted
                                            </span>
                                            <?php endif; ?>
                                        </h2>
                                    </div>
                                    <div class="body" id="contact-info-section">
                                        <!-- Contact info will be loaded here via AJAX -->
                                        <div class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">Loading contact information...</span>
                                            </div>
                                            <p class="mt-2">Loading contact information...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row clearfix">
                            <div class="col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="header">
                                        <h2>Personal Information</h2>
                                    </div>
                                    <div class="body">
                                        <table class="table table-hover">
                                            <tr>
                                                <td width="40%"><strong>Reference Number</strong></td>
                                                <td><?= htmlspecialchars($candidate->reference_number, ENT_QUOTES, 'UTF-8') ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Full Name</strong></td>
                                                <td><?= htmlspecialchars($candidate->first_name, ENT_QUOTES, 'UTF-8') ?>
                                                    <?= htmlspecialchars($candidate->last_name, ENT_QUOTES, 'UTF-8') ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>ID Number</strong></td>
                                                <td>
                                                    <?= !empty($candidate->id_number) ? htmlspecialchars($candidate->id_number, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Date of Birth</strong></td>
                                                <td>
                                                    <?= !empty($candidate->date_of_birth) ? date('j M Y', strtotime($candidate->date_of_birth)) : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Gender</strong></td>
                                                <td>
                                                    <?= !empty($candidate->gender) ? ucfirst($candidate->gender) : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Nationality</strong></td>
                                                <td>
                                                    <?= !empty($candidate->nationality) ? htmlspecialchars($candidate->nationality, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="header">
                                        <h2>Application Details</h2>
                                    </div>
                                    <div class="body">
                                        <table class="table table-hover">
                                            <tr>
                                                <td width="40%"><strong>Job Applied For</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->job_name)): ?>
                                                    <?= htmlspecialchars($candidate->job_name, ENT_QUOTES, 'UTF-8') ?>
                                                    <?php if (!empty($candidate->job_ref)): ?>
                                                    <br><small class="text-muted">Ref:
                                                        <?= htmlspecialchars($candidate->job_ref, ENT_QUOTES, 'UTF-8') ?></small>
                                                    <?php endif; ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">Not assigned</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Application Date</strong></td>
                                                <td><?= date('j M Y', strtotime($candidate->application_date)) ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status</strong></td>
                                                <td>
                                                    <span
                                                        class="badge badge-<?= get_status_badge($candidate->status) ?>">
                                                        <?= ucfirst($candidate->status) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Source</strong></td>
                                                <td>
                                                    <?= !empty($candidate->source) ? ucfirst($candidate->source) : '<span class="text-muted">Not specified</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Rating</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->rating)): ?>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i
                                                        class="fa fa-star<?= $i <= $candidate->rating ? ' text-warning' : ' text-muted' ?>"></i>
                                                    <?php endfor; ?>
                                                    (<?= $candidate->rating ?>/5)
                                                    <?php else: ?>
                                                    <span class="text-muted">Not rated</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row clearfix">
                            <div class="col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="header">
                                        <h2>Professional Information</h2>
                                    </div>
                                    <div class="body">
                                        <table class="table table-hover">
                                            <tr>
                                                <td width="40%"><strong>Highest Qualification</strong></td>
                                                <td><?= !empty($candidate->highest_qualification) ? htmlspecialchars($candidate->highest_qualification, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Years of Experience</strong></td>
                                                <td><?= !empty($candidate->years_experience) ? $candidate->years_experience . ' years' : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Current Position</strong></td>
                                                <td><?= !empty($candidate->current_position) ? htmlspecialchars($candidate->current_position, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Current Company</strong></td>
                                                <td><?= !empty($candidate->current_company) ? htmlspecialchars($candidate->current_company, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Skills</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->skills)): ?>
                                                    <?= htmlspecialchars($candidate->skills, ENT_QUOTES, 'UTF-8') ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">Not provided</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="header">
                                        <h2>Salary & Availability</h2>
                                    </div>
                                    <div class="body">
                                        <table class="table table-hover">
                                            <tr>
                                                <td width="40%"><strong>Current Salary</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->current_salary) && $candidate->current_salary > 0): ?>
                                                    R <?= number_format($candidate->current_salary, 2) ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">Not provided</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Expected Salary</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->expected_salary) && $candidate->expected_salary > 0): ?>
                                                    R <?= number_format($candidate->expected_salary, 2) ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">Not provided</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Notice Period</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->notice_period)): ?>
                                                    <?= $candidate->notice_period ?> days
                                                    <?php else: ?>
                                                    <span class="text-muted">Not provided</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Availability</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->availability)): ?>
                                                    <?= ucfirst($candidate->availability) ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">Not specified</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Willing to Relocate</strong></td>
                                                <td>
                                                    <?php if (isset($candidate->willing_to_relocate)): ?>
                                                    <?= $candidate->willing_to_relocate ? 'Yes' : 'No' ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">Not specified</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <?php if (!empty($candidate->address)): ?>
                        <div class="row clearfix">
                            <div class="col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="header">
                                        <h2>Address Information</h2>
                                    </div>
                                    <div class="body">
                                        <table class="table table-hover">
                                            <tr>
                                                <td width="40%"><strong>Address</strong></td>
                                                <td><?= nl2br(htmlspecialchars($candidate->address, ENT_QUOTES, 'UTF-8')) ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>City</strong></td>
                                                <td>
                                                    <?= !empty($candidate->city) ? htmlspecialchars($candidate->city, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Postal Code</strong></td>
                                                <td>
                                                    <?= !empty($candidate->postal_code) ? htmlspecialchars($candidate->postal_code, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Country</strong></td>
                                                <td>
                                                    <?= !empty($candidate->country) ? htmlspecialchars($candidate->country, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($candidate->cover_letter)): ?>
                        <div class="row clearfix">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="header">
                                        <h2>Cover Letter</h2>
                                    </div>
                                    <div class="body">
                                        <div class="alert alert-info">
                                            <?= nl2br(htmlspecialchars($candidate->cover_letter, ENT_QUOTES, 'UTF-8')) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($candidate->notes)): ?>
                        <div class="row clearfix">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="header">
                                        <h2>Internal Notes</h2>
                                    </div>
                                    <div class="body">
                                        <div class="alert alert-warning">
                                            <?= nl2br(htmlspecialchars($candidate->notes, ENT_QUOTES, 'UTF-8')) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($candidate->cv_file)): ?>
                        <!-- <div class="row clearfix">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="header">
                                        <h2>CV / Resume</h2>
                                    </div>
                                    <div class="body">
                                        <a href="<?= base_url('uploads/' . $candidate->cv_file) ?>" target="_blank"
                                            class="btn btn-primary">
                                            <i class="fa fa-download"></i> Download CV
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <?php endif; ?>
                    </div>
                    <div class="footer">
                        <?php 
                        // Get the current job ID from session, fallback to URI segment if missing
                        $current_job_id = $this->session->userdata('current_job_id');
                        $back_job_id = $current_job_id ?: $this->uri->segment(4);
                        ?>
                        <a href="<?= site_url('agency/candidates_list/index/' . $back_job_id) ?>"
                            class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to Candidates
                        </a>

                        <?php if ($candidate->enabled): ?>
                        <a href="<?= site_url('agency/candidates_list/disable/' . $candidate->id) ?>"
                            class="btn btn-warning">
                            <i class="fa fa-eye-slash"></i> Disable
                        </a>
                        <?php else: ?>
                        <a href="<?= site_url('agency/candidates_list/enable/' . $candidate->id) ?>"
                            class="btn btn-success">
                            <i class="fa fa-eye"></i> Enable
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include SweetAlert for better alerts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Load contact info via AJAX
    loadContactInfo();

    function loadContactInfo() {
        console.log('Loading contact info for candidate:', '<?php echo $candidate->id; ?>');

        $.ajax({
            url: '<?php echo site_url("agency/candidates/check_contact_access_status"); ?>',
            type: 'GET',
            dataType: 'json',
            data: {
                candidate_uuid: '<?php echo isset($candidate->uuid) ? $candidate->uuid : $candidate->id; ?>'
            },
            success: function(response) {
                console.log('AJAX Response:', response);

                if (response.success) {
                    $('#contact-info-section').html(createContactInfoHtml(response.contact_info));
                } else {
                    $('#contact-info-section').html(`
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> 
                    ${response.message || 'Failed to load contact information'}
                    <br><small>Response: ${JSON.stringify(response)}</small>
                </div>
            `);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText, status, error);
                $('#contact-info-section').html(`
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> 
                Network error. Please refresh the page or try again later.
                <br><small>Status: ${status}, Error: ${error}</small>
                <br><small>Response: ${xhr.responseText}</small>
            </div>
        `);
            },
            complete: function() {
                console.log('AJAX request completed');
            }
        });
    }


    function createContactInfoHtml(contactInfo) {
        var html = '';

        if (contactInfo.has_access) {
            // Full access granted
            html += `
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fa fa-envelope"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Email Address</span>
                            <span class="info-box-number">${contactInfo.email || 'N/A'}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                ${contactInfo.email ? 
                                    `<a href="mailto:${contactInfo.email}" class="text-white" onclick="logContactAccess()">
                                        <i class="fa fa-paper-plane"></i> Send Email
                                    </a>` 
                                    : 'Email not available'
                                }
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fa fa-phone"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Phone Number</span>
                            <span class="info-box-number">${contactInfo.phone || 'N/A'}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                ${contactInfo.phone ? 
                                    `<a href="tel:${contactInfo.phone}" class="text-white" onclick="logContactAccess()">
                                        <i class="fa fa-phone"></i> Call Candidate
                                    </a>` 
                                    : 'Phone not available'
                                }
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            ${contactInfo.alternate_phone ? `
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fa fa-mobile-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Alternate Phone</span>
                            <span class="info-box-number">${contactInfo.alternate_phone}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                <a href="tel:${contactInfo.alternate_phone}" class="text-white" onclick="logContactAccess()">
                                    <i class="fa fa-phone"></i> Call Alternate
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            <div class="alert alert-success mt-3">
                <h5><i class="fa fa-check-circle"></i> Full Access Granted</h5>
                <p>You have been granted access to the candidate's contact information by the assigned recruiter.</p>
                <div class="mt-2">
                    <a href="${contactInfo.email ? 'mailto:' + contactInfo.email : '#'}" 
                       class="btn btn-success ${!contactInfo.email ? 'disabled' : ''}" onclick="logContactAccess()">
                        <i class="fa fa-envelope"></i> Email Candidate
                    </a>
                    <a href="${contactInfo.phone ? 'tel:' + contactInfo.phone : '#'}" 
                       class="btn btn-info ml-2 ${!contactInfo.phone ? 'disabled' : ''}" onclick="logContactAccess()">
                        <i class="fa fa-phone"></i> Call Candidate
                    </a>
                </div>
            </div>
        `;

            // Log the contact access view when the page loads with full access
            setTimeout(function() {
                logContactAccess();
            }, 500);

        } else {
            // Masked info with request button
            var requestStatusHtml = '';
            var requestButtonHtml = '';

            if (contactInfo.request_pending) {
                requestStatusHtml = `
                <div class="alert alert-info">
                    <h5><i class="fa fa-clock"></i> Request Pending</h5>
                    <p>Your request for contact information is pending approval by the recruiter.</p>
                    <p><strong>Last requested:</strong> ${formatDate(contactInfo.last_requested)}</p>
                    <button class="btn btn-sm btn-outline-info" onclick="requestContactAccess()">
                        <i class="fa fa-sync"></i> Request Again
                    </button>
                </div>
            `;
            } else if (contactInfo.request_status === 'denied') {
                requestStatusHtml = `
                <div class="alert alert-danger">
                    <h5><i class="fa fa-times-circle"></i> Request Denied</h5>
                    <p>Your request for contact information was denied by the recruiter.</p>
                    <p><strong>Last requested:</strong> ${formatDate(contactInfo.last_requested)}</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="requestContactAccess()">
                        <i class="fa fa-paper-plane"></i> Request Again
                    </button>
                </div>
            `;
            } else {
                requestButtonHtml = `
                <div class="text-center py-4">
                    <button class="btn btn-primary btn-lg" onclick="requestContactAccess()">
                        <i class="fa fa-paper-plane"></i> Request Contact Information
                    </button>
                    <p class="text-muted mt-2">
                        <small>This will send a notification to the assigned recruiter</small>
                    </p>
                </div>
            `;
            }

            html += `
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fa fa-envelope"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Email Address</span>
                            <span class="info-box-number">${contactInfo.masked_email}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 30%"></div>
                            </div>
                            <span class="progress-description">
                                <i class="fa fa-lock"></i> Restricted Access
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fa fa-phone"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Phone Number</span>
                            <span class="info-box-number">${contactInfo.masked_phone}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 30%"></div>
                            </div>
                            <span class="progress-description">
                                <i class="fa fa-lock"></i> Restricted Access
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card card-warning mt-3">
                <div class="card-header">
                    <h5 class="card-title"><i class="fa fa-shield-alt"></i> Privacy Protection</h5>
                </div>
                <div class="card-body">
                    <p>To protect candidate privacy, contact information is restricted by default.</p>
                    <p>You need to request access from the assigned recruiter to view full contact details.</p>
                    
                    ${requestStatusHtml}
                    ${requestButtonHtml}
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> 
                            Contact information will only be shared for legitimate hiring purposes.
                        </small>
                    </div>
                </div>
            </div>
        `;
        }

        return html;
    }


    function logContactAccess() {
        // Only log once per page view to avoid duplicate logs
        if (window.contactAccessLogged) {
            return;
        }

        // Get candidate ID from PHP
        var candidateId = <?php echo $candidate->id; ?>;

        $.ajax({
            url: '<?php echo site_url("agency/candidates/log_contact_access"); ?>/' + candidateId,
            type: 'POST',
            data: {
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function(response) {
                if (response.success) {
                    window.contactAccessLogged = true;
                    console.log('Contact access logged successfully');
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to log contact access:', error);
            }
        });
    }

    // Also update the formatDate function to be more robust
    function formatDate(dateString) {
        if (!dateString || dateString === 'N/A' || dateString === 'null' || dateString === 'undefined') {
            return 'N/A';
        }

        try {
            var date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return dateString; // Return original string if invalid date
            }

            var now = new Date();
            var diffMs = now - date;
            var diffMins = Math.floor(diffMs / 60000);
            var diffHours = Math.floor(diffMs / 3600000);
            var diffDays = Math.floor(diffMs / 86400000);

            // Show relative time for recent dates
            if (diffMins < 1) {
                return 'Just now';
            } else if (diffMins < 60) {
                return diffMins + ' minutes ago';
            } else if (diffHours < 24) {
                return diffHours + ' hours ago';
            } else if (diffDays < 7) {
                return diffDays + ' days ago';
            } else {
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        } catch (e) {
            return dateString;
        }
    }

    // Also update the formatDate function to be more robust
    function formatDate(dateString) {
        if (!dateString || dateString === 'N/A' || dateString === 'null') {
            return 'N/A';
        }

        try {
            var date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return dateString; // Return original string if invalid date
            }

            var now = new Date();
            var diffMs = now - date;
            var diffMins = Math.floor(diffMs / 60000);
            var diffHours = Math.floor(diffMs / 3600000);
            var diffDays = Math.floor(diffMs / 86400000);

            // Show relative time for recent dates
            if (diffMins < 1) {
                return 'Just now';
            } else if (diffMins < 60) {
                return diffMins + ' minutes ago';
            } else if (diffHours < 24) {
                return diffHours + ' hours ago';
            } else if (diffDays < 7) {
                return diffDays + ' days ago';
            } else {
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        } catch (e) {
            return dateString;
        }
    }

    function formatDate(dateString) {
        if (!dateString || dateString === 'N/A') return 'N/A';
        try {
            var date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateString;
        }
    }
});

// Global function for requesting contact access
function requestContactAccess() {
    // Get current CSRF token
    var csrf_token = $('meta[name="csrf-token"]').attr('content') ||
        $('input[name="<?= $this->security->get_csrf_token_name() ?>"]').val();

    Swal.fire({
        title: 'Request Contact Information',
        html: `
            <p>Please explain why you need access to ${$('h2 i.fa-user').parent().text().trim()}'s contact information:</p>
            <textarea id="request-notes" class="form-control" rows="4" 
                      placeholder="e.g., Need to schedule an interview, discuss job offer details, clarify application information, etc."></textarea>
            <div class="mt-2 text-muted small">
                <i class="fa fa-info-circle"></i> Your request will be sent to the assigned recruiter for approval.
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Send Request',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        preConfirm: () => {
            var notes = $('#request-notes').val();
            if (!notes || notes.trim() === '') {
                Swal.showValidationMessage('Please provide a reason for your request');
                return false;
            }
            return {
                notes: notes.trim()
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= site_url("agency/candidates/request_contact_access") ?>',
                type: 'POST',
                data: {
                    candidate_uuid: '<?= $candidate->uuid ?? $candidate->id ?>',
                    notes: result.value.notes,
                    '<?= $this->security->get_csrf_token_name(); ?>': csrf_token
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Sending Request...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    console.log('Request response:', response); // Debug

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Request Sent!',
                            html: `
                                <p>${response.message}</p>
                                <p class="text-muted small mt-2">
                                    <i class="fa fa-clock"></i> 
                                    The recruiter will be notified and can approve your request.
                                </p>
                            `,
                            timer: 4000,
                            showConfirmButton: true
                        });

                        // Reload contact info section after a short delay
                        setTimeout(function() {
                            loadContactInfo();
                        }, 2000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Request Failed',
                            html: `
                                <p>${response.message}</p>
                                <p class="text-muted small mt-2">
                                    If this persists, please contact support.
                                </p>
                            `,
                            showConfirmButton: true
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: `
                            <p>Failed to send request. Please try again.</p>
                            <p class="text-muted small mt-2">
                                <strong>Details:</strong> ${error}<br>
                                <strong>Status:</strong> ${status}
                            </p>
                        `
                    });
                }
            });
        }
    });
}

// Auto-check for access updates every 30 seconds
setInterval(function() {
    if (!$('#contact-info-section').find('.info-box.bg-success').length) {
        loadContactInfo();
    }
}, 30000);
</script>

<?php
// Helper function for status badges
function get_status_badge($status) {
    switch ($status) {
        case 'new': return 'secondary';
        case 'reviewed': return 'info';
        case 'shortlisted': return 'warning';
        case 'interviewed': return 'primary';
        case 'hired': return 'success';
        case 'rejected': return 'danger';
        case 'on_hold': return 'dark';
        default: return 'secondary';
    }
}


?>