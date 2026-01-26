<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<?php
// At the top of your ajax_manage.php file
$form_action = !empty($uuid) ? 
    site_url('recruiter/candidates/update/' . $uuid) : 
    site_url('recruiter/candidates/create');
?>

<?= form_open($form_action, ['enctype' => 'multipart/form-data', 'id' => 'mainCandidateForm']); ?>
<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
    value="<?php echo $this->security->get_csrf_hash(); ?>">
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
        <!-- REMOVED: Required Documents Tab - Now handled separately -->
    </ul>

    <div class="form-field-container">
        <div class="form-field-container">
            <!-- REMOVED: <?= form_open('', ['enctype' => 'multipart/form-data', 'id' => 'mainCandidateForm']); ?> -->

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
                                    <?= (!empty($row->status) && $row->status == 'new') ? 'selected' : '' ?>>New
                                </option>
                                <option value="reviewed"
                                    <?= (!empty($row->status) && $row->status == 'reviewed') ? 'selected' : '' ?>>
                                    Reviewed
                                </option>
                                <option value="shortlisted"
                                    <?= (!empty($row->status) && $row->status == 'shortlisted') ? 'selected' : '' ?>>
                                    Shortlisted</option>
                                <option value="interviewed"
                                    <?= (!empty($row->status) && $row->status == 'interviewed') ? 'selected' : '' ?>>
                                    Interviewed</option>
                                <option value="rejected"
                                    <?= (!empty($row->status) && $row->status == 'rejected') ? 'selected' : '' ?>>
                                    Rejected
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
                                <i class="fa fa-user-check"></i> You are automatically assigned as the recruiter for
                                this
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
                throw new Error('Network response was not ok: '.response.status);
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

    // Get the ID value
    const idElement = document.querySelector('input[name="id"]');
    const id = idElement ? idElement.value : '0';

    console.log('ID value found:', id);
    console.log('Is update?', id && id != '0');

    // Determine correct endpoint
    let action;
    if (id && id != '0' && id !== '0') {
        // For update, use UUID if available
        const uuidElement = document.querySelector('input[name="uuid"]');
        const uuid = uuidElement ? uuidElement.value : null;
        if (uuid) {
            action = '<?= site_url("recruiter/candidates/update") ?>/' + uuid;
        } else {
            action = '<?= site_url("recruiter/candidates/update") ?>/' + id;
        }
    } else {
        action = '<?= site_url("recruiter/candidates/create") ?>';
    }

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

    fetch(action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`Server error: ${response.status}. Response: ${text}`);
                });
            }
            return response.json();
        })
        .then(result => {
            console.log('Response received:', result);

            if (result.success) {
                // SUCCESS HANDLING - COMPLETE CODE
                const successMessage = result.message || 'Candidate saved successfully!';
                console.log('Success:', successMessage);

                // Show success message
                if (typeof toastr !== 'undefined') {
                    toastr.success(successMessage);
                } else {
                    alert(successMessage);
                }

                // Check if we should redirect
                if (result.redirect && result.redirect_url) {
                    console.log('Redirecting to:', result.redirect_url);

                    // Close modal if open
                    setTimeout(() => {
                        // Try to close any open modal
                        if (typeof close_qm === 'function') {
                            close_qm();
                        } else if (typeof close_quick_manage === 'function') {
                            close_quick_manage();
                        }

                        // Redirect after short delay
                        setTimeout(() => {
                            window.location.href = result.redirect_url;
                        }, 500);
                    }, 1000);
                } else {
                    // Fallback - just close modal and reload
                    setTimeout(() => {
                        if (typeof close_qm === 'function') {
                            close_qm();
                            // Reload after modal closes
                            setTimeout(() => {
                                window.location.reload();
                            }, 300);
                        } else if (typeof close_quick_manage === 'function') {
                            close_quick_manage();
                            setTimeout(() => {
                                window.location.reload();
                            }, 300);
                        } else {
                            // No modal functions, just reload
                            window.location.reload();
                        }
                    }, 1500);
                }

            } else {
                // ERROR HANDLING - COMPLETE CODE
                const errorMessage = result.error || result.message || 'Failed to save candidate';
                console.error('Error:', errorMessage);

                // Show error message
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage);
                } else {
                    alert('Error: ' + errorMessage);
                }

                // Show validation errors if any
                if (result.fields) {
                    console.log('Validation errors:', result.fields);
                    // Highlight invalid fields
                    Object.keys(result.fields).forEach(fieldName => {
                        const field = form.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            field.classList.add('is-invalid');
                        }
                    });
                }

                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            // CATCH HANDLING - COMPLETE CODE
            console.error('Fetch error:', error);

            let errorMessage = 'Error saving candidate. Please try again.';

            if (error.message.includes('CSRF')) {
                errorMessage = 'Session expired. Please refresh the page and try again.';
            } else if (error.message.includes('Network')) {
                errorMessage = 'Network error. Please check your connection and try again.';
            } else if (error.message) {
                errorMessage = error.message;
            }

            // Show error message
            if (typeof toastr !== 'undefined') {
                toastr.error(errorMessage);
            } else {
                alert(errorMessage);
            }

            // Re-enable submit button
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