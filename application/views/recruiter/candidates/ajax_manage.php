<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php
// At the top of your ajax_manage.php file
$form_action = !empty($uuid) ? 
    site_url('recruiter/candidates/update/' . $uuid) : 
    site_url('recruiter/candidates/create');
?>

<?= form_open($form_action, ['enctype' => 'multipart/form-data', 'id' => 'mainCandidateForm']); ?>
<?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>
<?= form_hidden('uuid', !empty($uuid) ? $uuid : ''); ?>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qm-tabs">
    <div class="quick-manage-heading">
        <?php if (empty($row)): ?>
        <h2>Add Candidate</h2>
        <p>Submit a candidate to any agency and job.</p>
        <?php else: ?>
        <h2>Edit Candidate
            <span><?= htmlspecialchars($row->first_name . ' ' . $row->last_name, ENT_QUOTES, 'UTF-8'); ?></span>
        </h2>
        <?php endif; ?>
    </div>

    <ul class="qm-tabs-header">
        <li rel="1" class="active">Personal Details</li>
        <li rel="2">Professional Info</li>
        <li rel="3">Application</li>
        <li rel="4">Agency & Job</li>
        <?php if ((isset($has_pending_documents_request) && $has_pending_documents_request === true) || 
          (isset($force_required_tab) && $force_required_tab === true)): ?>
        <li rel="5" class="required-documents-tab">
            Required Documents
            <span class="badge badge-danger ml-1">!</span>
        </li>
        <?php endif; ?>
    </ul>

    <div class="form-field-container">
        <?= form_open('', ['enctype' => 'multipart/form-data', 'id' => 'mainCandidateForm']); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>
        <?= form_hidden('action', !empty($row->id) ? 'update' : 'create'); ?>

        <!-- Tab 1: Personal -->
        <div rel="1" class="qm-tabs-tab active">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="reference_number"><?= lang('label_reference_number') ?> *</label>
                        <div class="input-group">
                            <input type="text" name="reference_number" id="reference_number" class="form-control"
                                value="<?= !empty($row->reference_number) ? htmlspecialchars($row->reference_number, ENT_QUOTES, 'UTF-8') : '' ?>"
                                required placeholder="e.g., CAND-001">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="refresh-reference"
                                    title="Generate new reference">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Reference number is auto-generated</small>
                    </div>
                </div>
                <div class="col-lg-6">
                    <?= field_input('email', $row, 'required valid-email', [], 'email', 'candidate@email.com'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('first_name', $row, 'required', [], 'text', 'First Name'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('last_name', $row, 'required', [], 'text', 'Last Name'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('phone|label_phone', $row, '', [], 'text', 'Phone Number'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('id_number|label_security_number', $row, '', [], 'text', 'Security Number'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_date('date_of_birth|label_date_of_birth', $row, '', 'yyyy-mm-dd', []); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('gender|label_gender', 
                [
                    '' => '-- Select Gender --',
                    'male' => 'Male',
                    'female' => 'Female',
                    'other' => 'Other'
                ], 
                $row, ''); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('address|label_address', $row, '', [], 'Full Address'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <?= field_input('city|label_city', $row, '', [], 'text', 'City'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('province|label_state', $row, '', [], 'text', 'State'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('postal_code|label_postal_code', $row, '', [], 'text', 'Postal Code'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_input('country|label_country', $row, '', [], 'text', 'Country', 'Australia'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 2: Professional -->
        <div rel="2" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('highest_qualification|label_highest_qualification', $row, '', [], 'text', 'Highest Qualification'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('years_experience|label_years_experience', $row, 'numeric', [], 'number', 'Years of Experience'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('current_position|label_current_position', $row, '', [], 'text', 'Current Job Title'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('current_company|label_current_company', $row, '', [], 'text', 'Current Employer'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('current_salary|label_current_salary', $row, 'decimal', [], 'text', 'Current Salary'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('expected_salary|label_expected_salary', $row, 'decimal', [], 'text', 'Expected Salary'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('notice_period|label_notice_period', $row, 'numeric', [], 'number', 'Notice Period (days)'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('source|label_source', 
                [
                    'website' => 'Website',
                    'agency' => 'Agency',
                    'referral' => 'Referral',
                    'linkedin' => 'LinkedIn',
                    'indeed' => 'Indeed',
                    'other' => 'Other'
                ], 
                $row, ''); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('cover_letter|label_cover_letter', $row, '', [], 'Cover Letter'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="cv_file">CV/Resume *</label>
                        <?php if (!empty($row->cv_file)): ?>
                        <div class="current-file mb-2">
                            <p class="mb-1">
                                <strong>Current file:</strong>
                                <a href="<?= base_url('uploads/candidates/cv/' . $row->cv_file) ?>" target="_blank"
                                    class="text-primary">
                                    <i class="fa fa-download"></i> Download CV
                                </a>
                            </p>
                            <small class="text-muted">Upload a new file to replace the current one</small>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="cv_file" id="cv_file" class="form-control"
                            accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            <?= empty($row->cv_file) ? 'required' : '' ?>>
                        <small class="text-muted">
                            <strong>File Requirements:</strong> PDF, DOC, DOCX | Max Size: 10MB
                            <?= empty($row->cv_file) ? '<span class="text-danger">* Required</span>' : '' ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Application -->
        <div rel="3" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="status"><?= lang('label_status') ?> *</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="">-- Select Status --</option>
                            <option value="new"
                                <?= (!empty($row->status) && $row->status == 'new') ? 'selected' : '' ?>>New</option>
                            <option value="reviewed"
                                <?= (!empty($row->status) && $row->status == 'reviewed') ? 'selected' : '' ?>>Reviewed
                            </option>
                            <option value="shortlisted"
                                <?= (!empty($row->status) && $row->status == 'shortlisted') ? 'selected' : '' ?>>
                                Shortlisted</option>
                            <option value="interviewed"
                                <?= (!empty($row->status) && $row->status == 'interviewed') ? 'selected' : '' ?>>
                                Interviewed</option>
                            <option value="rejected"
                                <?= (!empty($row->status) && $row->status == 'rejected') ? 'selected' : '' ?>>Rejected
                            </option>
                            <option value="hired"
                                <?= (!empty($row->status) && $row->status == 'hired') ? 'selected' : '' ?>>Hired
                            </option>
                            <option value="on_hold"
                                <?= (!empty($row->status) && $row->status == 'on_hold') ? 'selected' : '' ?>>On Hold
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <?= field_date('application_date|label_application_date', $row, '', 'yyyy-mm-dd', []); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('rating|label_rating', 
            [
                '' => '-- Select Rating --',
                '1' => '★ (1) Poor',
                '2' => '★★ (2) Fair', 
                '3' => '★★★ (3) Good',
                '4' => '★★★★ (4) Very Good',
                '5' => '★★★★★ (5) Excellent'
            ], 
            $row, ''); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('notes|label_notes', $row, '', [], 'Internal Notes & Comments'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 4: Agency & Job -->
        <div rel="4" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_multi_select('additional_agency_ids|label_agencies', 
                $additional_agency_options, 
                $additional_agency_ids,
                'Select agencies (first selected becomes primary)',
                [], // empty array for attributes instead of true
                false // CHANGED: Make agencies optional
                ); ?>
                    <small class="text-muted"><?= lang('help_first_agency_primary') ?></small>
                </div>
                <div class="col-lg-6">
                    <?= field_multi_select('additional_job_ids|label_jobs', 
                $additional_job_options, 
                $additional_job_ids,
                'Select jobs (first selected becomes primary)',
                [], // CHANGED: Remove required parameter
                false // CHANGED: Make jobs optional
                ); ?>
                    <small class="text-muted"><?= lang('help_first_job_primary') ?></small>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="assigned_agent_id"><?= lang('label_assigned_agent') ?> *</label>

                        <?php if (empty($id) && !empty($logged_in_recruiter)): ?>
                        <!-- For new candidates: Auto-assign and show as read-only -->
                        <input type="hidden" name="assigned_agent_id" value="<?= $logged_in_recruiter->id ?>">
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars($logged_in_recruiter->first_name . ' ' . $logged_in_recruiter->last_name . ' (' . $logged_in_recruiter->email . ')', ENT_QUOTES, 'UTF-8') ?>"
                            readonly>
                        <small class="text-muted text-success">
                            <i class="fa fa-user-check"></i> You are automatically assigned as the recruiter for this
                            candidate
                        </small>
                        <?php else: ?>
                        <!-- For existing candidates: Show dropdown -->
                        <select name="assigned_agent_id" id="assigned_agent_id" class="form-control" required>
                            <option value="">-- Select Recruiter --</option>
                            <?php if (!empty($agents_all)): ?>
                            <?php foreach ($agents_all as $agent): ?>
                            <option value="<?= $agent->id ?>"
                                <?= (!empty($row->assigned_agent_id) && $row->assigned_agent_id == $agent->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($agent->first_name . ' ' . $agent->last_name . ' (' . $agent->email . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <option value="">-- No recruiters available --</option>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Select which recruiter this candidate is assigned to</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 5: Required Documents -->
        <?php if ((isset($has_pending_documents_request) && $has_pending_documents_request === true) || (isset($force_required_tab) && $force_required_tab === true)): ?>

        <div rel="5" class="qm-tabs-tab">
            <!-- Documents Request Alert -->
            <div class="alert alert-warning">
                <h5><i class="fa fa-exclamation-triangle"></i> Documents Requested by Agency</h5>
                <p class="mb-2"><strong>Required Documents:</strong> <?= $documents_request_notes ?></p>
                <p class="mb-0">Please upload the requested documents below. The agency will be notified when you submit
                    these documents.</p>
            </div>

            <!-- Single Unified Upload Form -->
            <div class="unified-documents-form">
                <form id="unifiedDocumentsForm" method="POST" enctype="multipart/form-data">
                    <!-- Add CSRF token here -->
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                        value="<?= $this->security->get_csrf_hash() ?>">

                    <input type="hidden" name="candidate_id" value="<?= $row->id ?>">
                    <input type="hidden" name="is_required_documents" value="1">
                    <?php if (!empty($pending_notification_id)): ?>
                    <input type="hidden" name="notification_id" value="<?= $pending_notification_id ?>">
                    <?php endif; ?>

                    <!-- Document Upload Fields -->
                    <div class="document-upload-section">
                        <h5 class="mb-3"><i class="fa fa-upload"></i> Upload Required Documents</h5>

                        <div id="documentUploadContainer">
                            <!-- First document field is always shown -->
                            <div class="document-upload-row mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Document Name *</label>
                                            <input type="text" name="required_documents[0][name]"
                                                class="form-control document-name"
                                                placeholder="e.g., ID Copy, Degree Certificate" data-required="true">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>File *</label>
                                            <input type="file" name="required_documents[0][file]"
                                                class="form-control document-file"
                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" data-required="true">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Description (Optional)</label>
                                            <textarea name="required_documents[0][description]"
                                                class="form-control document-description" rows="1"
                                                placeholder="Brief description..."></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button"
                                                class="btn btn-outline-danger btn-block remove-document"
                                                style="margin-top: 32px;" disabled>
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add More Documents Button -->
                        <div class="text-center mb-4">
                            <button type="button" class="btn btn-outline-primary" id="addMoreDocuments">
                                <i class="fa fa-plus"></i> Add Another Document
                            </button>
                        </div>
                    </div>

                    <!-- Submission Notes -->
                    <div class="form-group">
                        <label>Additional Notes for Agency (Optional)</label>
                        <textarea name="submission_notes" class="form-control" rows="2"
                            placeholder="Add any additional notes or comments for the agency..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success btn-lg" id="submitDocumentsBtn">
                            <i class="fa fa-paper-plane"></i> Submit All Documents to Agency
                        </button>
                    </div>
                </form>
            </div>

            <!-- Previously Submitted Documents Section -->
            <div class="submitted-documents-section mt-5">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fa fa-history"></i> Previously Submitted Documents</h5>
                    </div>
                    <div class="card-body">
                        <div id="submittedRequiredDocuments" class="table-responsive">
                            <!-- Submitted documents will be loaded here via AJAX -->
                            <div class="text-center text-muted py-4">
                                <i class="fa fa-spinner fa-spin"></i> Loading submitted documents...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="btn-container">
            <?= qm_tab_buttons(); ?>
            <?= qm_close_button(); ?>
            <button type="submit" class="btn btn-primary save-button">
                <i class="fa fa-save"></i> Save Candidate
            </button>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<script>
// ========== SMART CLOSE FUNCTION ==========
function smartCloseForm() {
    console.log('Smart close triggered...');

    // Check if we're in a modal context
    if (typeof close_qm === 'function' && !document.body.classList.contains('qm-full-page')) {
        console.log('Closing modal...');
        close_qm();
    }
    // Check if we're in full-page candidate details
    else if (window.location.pathname.includes('/view/')) {
        console.log('Redirecting from candidate details...');
        window.location.href = '<?= site_url("recruiter/candidates") ?>';
    }
    // Fallback: use existing close_qm if available
    else if (typeof close_qm === 'function') {
        console.log('Using fallback close_qm...');
        close_qm();
    }
    // Ultimate fallback
    else {
        console.log('Using ultimate fallback redirect...');
        window.location.href = '<?= site_url("recruiter/candidates") ?>';
    }
}
// ========== REFERENCE NUMBER HANDLING ==========
function initializeReferenceNumber() {
    const referenceField = document.getElementById('reference_number');
    const refreshBtn = document.getElementById('refresh-reference');

    console.log('Initializing reference number...');
    console.log('Reference field:', referenceField);
    console.log('Refresh button:', refreshBtn);

    // Generate initial reference if empty
    if (referenceField && !referenceField.value) {
        console.log('Reference field is empty, generating number...');
        generateReferenceNumber();
    } else if (referenceField) {
        console.log('Reference field already has value:', referenceField.value);
    }

    // Handle refresh button
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Refresh reference button clicked');
            generateReferenceNumber();
        });
    }
}

function generateReferenceNumber() {
    console.log('Generating reference number...');

    const referenceField = document.getElementById('reference_number');
    if (!referenceField) {
        console.error('Reference field not found');
        return;
    }

    // Show loading state
    referenceField.value = 'Generating...';

    fetch('<?= site_url("recruiter/candidates/generate_reference") ?>')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Reference generation response:', data);
            if (data.success && referenceField) {
                referenceField.value = data.reference;
                console.log('Reference number set to:', data.reference);
            } else {
                throw new Error(data.error || 'Failed to generate reference');
            }
        })
        .catch(error => {
            console.error('Error generating reference:', error);
            // Fallback reference number
            const timestamp = new Date().getTime();
            const random = Math.floor(Math.random() * 1000);
            const fallbackReference = 'CAND-' + timestamp + '-' + random;

            if (referenceField) {
                referenceField.value = fallbackReference;
            }
            console.log('Using fallback reference:', fallbackReference);
        });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing reference number...');
    initializeReferenceNumber();
});

// Also initialize when the quick manage modal is opened (for AJAX loading)
if (typeof initializeReferenceNumber === 'function') {
    // Try to initialize immediately in case DOM is already ready
    setTimeout(initializeReferenceNumber, 100);

    // Also try after a longer delay in case the modal takes time to render
    setTimeout(initializeReferenceNumber, 500);
}

// ========== FORM VALIDATION & SUBMISSION ==========
function save_form(el) {
    // Prevent the default core.min.js validation
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    // Use our custom validation and submission
    customSaveForm(el);
}

function customSaveForm(el) {
    const form = document.getElementById('mainCandidateForm');

    if (!form) {
        console.error('Form not found');
        alert('Form not found. Please refresh the page and try again.');
        return;
    }

    console.log('Form validation passed, submitting via AJAX...');

    // Submit the form via AJAX
    submitFormData(form);
}

function submitFormData(form) {
    if (!form) {
        console.error('Form is null');
        alert('Form error. Please refresh the page and try again.');
        return;
    }

    const formData = new FormData(form);

    // PROPERLY get the ID value
    const idElement = document.querySelector('input[name="id"]');
    const id = idElement ? idElement.value : '0';

    console.log('ID value found:', id);
    console.log('Is update?', id && id != '0');

    // CORRECTED: Use the proper endpoint
    const action = id && id != '0' && id !== '0' ?
        '<?= site_url("recruiter/candidates/update") ?>/' + id :
        '<?= site_url("recruiter/candidates/create") ?>';

    const submitBtn = document.querySelector('.save-button');
    if (!submitBtn) {
        console.error('Submit button not found');
        alert('Submit button not found. Please refresh the page and try again.');
        return;
    }

    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    console.log('Submitting to:', action);

    // ADD THIS CRITICAL HEADER to make it a proper AJAX request
    fetch(action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest' // THIS MAKES IT AN AJAX REQUEST
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json(); // Expect JSON response
        })
        .then(result => {
            console.log('Response received:', result);

            if (result.success) {
                // Success handling
                const successMessage = result.message || 'Candidate saved successfully!';
                console.log('Success:', successMessage);

                // Show success message
                if (typeof toastr !== 'undefined') {
                    toastr.success(successMessage);
                } else {
                    alert(successMessage);
                }

                // CHECK IF WE'RE ON SINGLE VIEW PAGE
                const isSingleViewPage = window.location.pathname.includes('/view/');

                if (isSingleViewPage) {
                    console.log('On single view page - redirecting to listing...');
                    // Redirect to candidates listing after a short delay
                    setTimeout(() => {
                        window.location.href = '<?= site_url("recruiter/candidates") ?>';
                    }, 1000);
                } else {
                    // Close the quick manage modal for other contexts
                    if (typeof close_quick_manage === 'function') {
                        close_quick_manage();
                    } else if (typeof close_qm === 'function') {
                        close_qm();
                    } else {
                        // Fallback: reload the page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                }
            } else {
                // Error handling
                const errorMessage = result.error || 'Failed to save candidate';
                console.error('Error:', errorMessage);

                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage);
                } else {
                    alert('Error: ' + errorMessage);
                }

                // Show validation errors if any
                if (result.fields) {
                    console.log('Validation errors:', result.fields);
                    // You can add code here to highlight invalid fields
                }
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            const errorMessage = 'Error saving candidate: ' + error.message;

            if (typeof toastr !== 'undefined') {
                toastr.error(errorMessage);
            } else {
                alert(errorMessage);
            }
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
}

// ========== FALLBACK FORM SUBMISSION ==========
const mainForm = document.getElementById('mainCandidateForm');
if (mainForm) {
    mainForm.addEventListener('submit', function(e) {
        e.preventDefault();
        customSaveForm(this);
    });
}

// ========== TAB HANDLING ==========
// Make sure tab switching works properly
document.querySelectorAll('.qm-tabs-header li').forEach(tab => {
    tab.addEventListener('click', function() {
        const tabId = this.getAttribute('rel');

        // Remove active class from all tabs and tab content
        document.querySelectorAll('.qm-tabs-header li').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.qm-tabs-tab').forEach(t => t.classList.remove('active'));

        // Add active class to clicked tab and corresponding content
        this.classList.add('active');
        const targetTab = document.querySelector('.qm-tabs-tab[rel="' + tabId + '"]');
        if (targetTab) {
            targetTab.classList.add('active');
        }
    });
});

// ========== DEBUGGING ==========
// Debug: Check what close functions are available
console.log('=== DEBUG CLOSE FUNCTIONS ===');
console.log('close_quick_manage:', typeof close_quick_manage);
console.log('close_qm:', typeof close_qm);
console.log('closeQuickManage:', typeof closeQuickManage);
console.log('jQuery modal:', typeof $ !== 'undefined' ? typeof $.fn.modal : 'jQuery not loaded');
console.log('Visible modals:', document.querySelectorAll('.modal:not(.hide)').length);

// Override any existing problematic close behavior
if (typeof close_quick_manage === 'function') {
    const originalClose = close_quick_manage;
    close_quick_manage = function() {
        console.log('close_quick_manage called - redirecting...');
        window.location.href = '<?= site_url("recruiter/candidates") ?>';
    };
}

// Temporary test - add this to your JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const idField = document.querySelector('input[name="id"]');
    const actionField = document.querySelector('input[name="action"]');
    console.log('ID Field value:', idField ? idField.value : 'NOT FOUND');
    console.log('Action Field value:', actionField ? actionField.value : 'NOT FOUND');

    // Force the form to use update if we have an ID
    if (idField && idField.value && idField.value != '0') {
        console.log('This should be an UPDATE operation for candidate ID:', idField.value);
    } else {
        console.log('This should be a CREATE operation');
    }
});

// ========== CLEAN DOCUMENTS FORM SETUP ==========

// Function to setup the documents form
function setupDocumentsForm() {
    console.log('Setting up documents form...');

    // Find the form or create it if missing
    let form = document.getElementById('unifiedDocumentsForm');

    if (!form) {
        console.log('Form not found, checking if we need to create it...');

        const unifiedDiv = document.querySelector('.unified-documents-form');
        if (!unifiedDiv) {
            console.error('unified-documents-form div not found');
            return;
        }

        // Check if form fields exist but form wrapper is missing
        if (unifiedDiv.querySelector('input[name="candidate_id"]') &&
            !unifiedDiv.querySelector('form')) {

            console.log('Form wrapper missing, creating it...');

            // Create form element
            form = document.createElement('form');
            form.id = 'unifiedDocumentsForm';
            form.method = 'POST';
            form.enctype = 'multipart/form-data';

            // Move all children from div to form
            while (unifiedDiv.firstChild) {
                form.appendChild(unifiedDiv.firstChild);
            }

            // Add form back to div
            unifiedDiv.appendChild(form);

            console.log('✅ Form created successfully');
        }
    }

    // Get the form (should exist now)
    form = document.getElementById('unifiedDocumentsForm');
    if (!form) {
        console.error('Form still not found after setup');
        return;
    }

    // Fix submit button
    const submitBtn = document.getElementById('submitDocumentsBtn');
    if (submitBtn) {
        // Change button type to submit
        submitBtn.type = 'submit';

        // Add form submit handler
        form.addEventListener('submit', handleDocumentsFormSubmit);

        console.log('✅ Form setup complete');
    }
}

// Function to handle form submission
// Function to handle form submission
async function handleDocumentsFormSubmit(e) {
    e.preventDefault();
    console.log('Form submission started...');

    const form = e.target;
    const submitBtn = form.querySelector('#submitDocumentsBtn');

    if (!submitBtn) {
        console.error('Submit button not found');
        return;
    }

    // Save original button state
    const originalText = submitBtn.innerHTML;
    const originalDisabled = submitBtn.disabled;

    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting...';

    try {
        // Validate form
        const nameInputs = form.querySelectorAll('.document-name');
        const fileInputs = form.querySelectorAll('.document-file');
        let isValid = true;
        let errorMessages = [];

        nameInputs.forEach((input, index) => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
                errorMessages.push(`Document ${index + 1}: Name is required`);
            } else {
                input.classList.remove('is-invalid');
            }
        });

        fileInputs.forEach((input, index) => {
            if (!input.files || input.files.length === 0) {
                isValid = false;
                input.classList.add('is-invalid');
                errorMessages.push(`Document ${index + 1}: File is required`);
            } else {
                // Check file size (max 10MB)
                const file = input.files[0];
                if (file.size > 10 * 1024 * 1024) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    errorMessages.push(`Document ${index + 1}: File size exceeds 10MB`);
                } else {
                    input.classList.remove('is-invalid');
                }
            }
        });

        if (!isValid) {
            alert('Please fix the following errors:\n\n' + errorMessages.join('\n'));
            submitBtn.disabled = originalDisabled;
            submitBtn.innerHTML = originalText;
            return;
        }

        // Create FormData
        const formData = new FormData(form);

        // Submit via AJAX
        const response = await fetch(
            '<?php echo site_url("recruiter/candidates/upload_required_documents"); ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

        const result = await response.json();
        console.log('Submission result:', result);

        if (result.success) {
            // Show success message
            if (typeof toastr !== 'undefined') {
                toastr.success(result.message || 'Documents submitted successfully!');
            } else {
                alert(result.message || 'Documents submitted successfully!');
            }

            // CRITICAL FIX: Check if we should redirect
            if (result.redirect && result.redirect_url) {
                console.log('Redirecting to:', result.redirect_url);
                // Short delay to show success message
                setTimeout(() => {
                    window.location.href = result.redirect_url;
                }, 1500);
            } else {
                // Close the modal or refresh
                setTimeout(() => {
                    if (typeof close_qm === 'function') {
                        close_qm();
                    } else if (typeof smartCloseForm === 'function') {
                        smartCloseForm();
                    } else {
                        window.location.reload();
                    }
                }, 1500);
            }
        } else {
            // Show error
            if (typeof toastr !== 'undefined') {
                toastr.error(result.error || 'Failed to submit documents');
            } else {
                alert('Error: ' + (result.error || 'Failed to submit documents'));
            }

            submitBtn.disabled = originalDisabled;
            submitBtn.innerHTML = originalText;
        }

    } catch (error) {
        console.error('Submission error:', error);
        alert('Error submitting documents: ' + error.message);
        submitBtn.disabled = originalDisabled;
        submitBtn.innerHTML = originalText;
    }
}

// Function to add more document fields
function addDocumentField() {
    const container = document.getElementById('documentUploadContainer');
    if (!container) return;

    const count = container.querySelectorAll('.document-upload-row').length;
    const index = count;

    const newRow = document.createElement('div');
    newRow.className = 'document-upload-row mb-3 p-3 border rounded';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Document Name *</label>
                    <input type="text" name="required_documents[${index}][name]" 
                           class="form-control document-name" 
                           placeholder="e.g., ID Copy, Degree Certificate" 
                           data-required="true">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>File *</label>
                    <input type="file" name="required_documents[${index}][file]" 
                           class="form-control document-file" 
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                           data-required="true">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Description (Optional)</label>
                    <textarea name="required_documents[${index}][description]" 
                              class="form-control document-description" 
                              rows="1" 
                              placeholder="Brief description..."></textarea>
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger btn-block remove-document" style="margin-top: 32px;">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    container.appendChild(newRow);

    // Enable remove button for the first row if this is the second row
    if (index === 1) {
        const firstRemoveBtn = container.querySelector('.document-upload-row:first-child .remove-document');
        if (firstRemoveBtn) {
            firstRemoveBtn.disabled = false;
        }
    }

    // Add event listener to the new remove button
    const removeBtn = newRow.querySelector('.remove-document');
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            newRow.remove();
            updateDocumentIndices();
        });
    }

    console.log(`Added document field ${index + 1}`);
}

// Function to update document indices after removal
function updateDocumentIndices() {
    const rows = document.querySelectorAll('.document-upload-row');
    rows.forEach((row, index) => {
        // Update name inputs
        const nameInput = row.querySelector('.document-name');
        if (nameInput) {
            nameInput.name = `required_documents[${index}][name]`;
        }

        // Update file inputs
        const fileInput = row.querySelector('.document-file');
        if (fileInput) {
            fileInput.name = `required_documents[${index}][file]`;
        }

        // Update description inputs
        const descInput = row.querySelector('.document-description');
        if (descInput) {
            descInput.name = `required_documents[${index}][description]`;
        }
    });

    // Disable remove button if only one row remains
    const removeButtons = document.querySelectorAll('.remove-document');
    if (removeButtons.length === 1) {
        removeButtons[0].disabled = true;
    }
}

// Setup function for Tab 5
function setupTab5() {
    console.log('Setting up Tab 5...');

    // Find the required documents tab header
    const requiredTab = document.querySelector('.required-documents-tab');
    if (requiredTab) {
        console.log('Required documents tab found');

        // Add click listener
        requiredTab.addEventListener('click', function() {
            console.log('Required documents tab clicked!');

            // Wait for tab to become active
            const checkTab = setInterval(() => {
                const tab5 = document.querySelector('.qm-tabs-tab[rel="5"]');
                if (tab5 && tab5.classList.contains('active')) {
                    clearInterval(checkTab);
                    console.log('Tab 5 is now active');

                    // Setup the form
                    setupDocumentsForm();

                    // Setup add more button
                    const addMoreBtn = document.getElementById('addMoreDocuments');
                    if (addMoreBtn) {
                        addMoreBtn.addEventListener('click', addDocumentField);
                    }
                }
            }, 100);
        });
    }

    // Check if Tab 5 is already active on page load
    const activeTab = document.querySelector('.qm-tabs-tab.active');
    if (activeTab && activeTab.getAttribute('rel') === '5') {
        console.log('Tab 5 is already active on page load');

        // Setup form and buttons
        setupDocumentsForm();

        const addMoreBtn = document.getElementById('addMoreDocuments');
        if (addMoreBtn) {
            addMoreBtn.addEventListener('click', addDocumentField);
        }
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up Tab 5...');
    setupTab5();
});

// Also run setup after a delay in case of dynamic loading
setTimeout(setupTab5, 1000);
</script>
<style>
.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.quick-manage-form-container .form-control:required {
    border-left: 3px solid #007bff;
}

.quick-manage-form-container .form-control.is-invalid {
    border-left: 3px solid #dc3545;
}
</style>

<style>
.unified-documents-form {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #dee2e6;
}

.document-upload-row {
    background: white;
    transition: all 0.3s ease;
}

.document-upload-row:hover {
    background: #f8f9fa;
    border-color: #007bff !important;
}

.required-documents-tab {
    background: #fff3cd !important;
    border-color: #ffeaa7 !important;
}

.badge {
    font-size: 0.7em;
}

.alert h5 {
    margin-bottom: 10px;
}

.alert p {
    margin-bottom: 5px;
}

.btn-lg {
    padding: 12px 30px;
    font-size: 1.1rem;
}

.card-header {
    font-weight: 600;
}

.multi-select {
    height: 120px !important;
    min-height: 120px;
}

.multi-select option {
    padding: 8px 12px;
}

.current-file {
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    margin-bottom: 8px;
    border: 1px solid #e9ecef;
}

.current-file a {
    color: #007bff;
    text-decoration: none;
}

.current-file a:hover {
    text-decoration: underline;
}

.parsley-errors-list {
    color: #dc3545;
    font-size: 0.875em;
    margin-top: 0.25rem;
}

.parsley-error {
    border-color: #dc3545 !important;
}

.alert {
    margin: 15px;
    border-radius: 4px;
}

.input-group .form-control.is-valid {
    border-color: #28a745;
}

.input-group .form-control.is-warning {
    border-color: #ffc107;
}

#refresh-reference:hover {
    background-color: #007bff;
    color: white;
}

.submitted-documents-section {
    border-top: 2px solid #e9ecef;
    padding-top: 20px;
}

.document-upload-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.btn-block {
    width: 100%;
}

.is-invalid {
    border-color: #dc3545 !important;
}

.text-muted {
    color: #6c757d !important;
}
</style>