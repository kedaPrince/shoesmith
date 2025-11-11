<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

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
            📋 Required Documents
            <span class="badge badge-danger ml-1">!</span>
        </li>
        <?php endif; ?>
    </ul>

    <div class="form-field-container">
        <?= form_open('', ['enctype' => 'multipart/form-data', 'id' => 'mainCandidateForm']); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>

        <?= form_hidden('agency_id', !empty($row->agency_id) ? $row->agency_id : ''); ?>
        <?= form_hidden('job_id', !empty($row->job_id) ? $row->job_id : ''); ?>

        <!-- Tab 1: Personal -->
        <div rel="1" class="qm-tabs-tab active">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="reference_number"><?= lang('label_reference_number') ?> *</label>
                        <div class="input-group">
                            <input type="text" name="reference_number" id="reference_number" class="form-control"
                                value="<?= !empty($row->reference_number) ? htmlspecialchars($row->reference_number, ENT_QUOTES, 'UTF-8') : '' ?>"
                                required placeholder="e.g., CAND-001" readonly>
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
                    <?= field_input('id_number|label_id_number', $row, '', [], 'text', 'ID Number'); ?>
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
                    <?= field_input('province|label_province', $row, '', [], 'text', 'Province'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('postal_code|label_postal_code', $row, '', [], 'text', 'Postal Code'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_input('country|label_country', $row, '', [], 'text', 'Country', 'South Africa'); ?>
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
                    <?= field_dropdown('status|label_status', 
                [
                    'new' => 'New',
                    'reviewed' => 'Reviewed',
                    'shortlisted' => 'Shortlisted',
                    'interviewed' => 'Interviewed',
                    'rejected' => 'Rejected',
                    'hired' => 'Hired',
                    'on_hold' => 'On Hold'
                ], 
                $row, 'required'); ?>
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
            true // required - moved to the correct parameter position
        ); ?>
                    <small class="text-muted"><?= lang('help_first_agency_primary') ?></small>
                </div>
                <div class="col-lg-6">
                    <?= field_multi_select('additional_job_ids|label_jobs', 
            $additional_job_options, 
            $additional_job_ids,
            'Select jobs (first selected becomes primary)'
        ); ?>
                    <small class="text-muted"><?= lang('help_first_job_primary') ?></small>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('assigned_agent_id|label_assigned_agent', 
            !empty($agents_all) ? array_reduce($agents_all, function($carry, $agent) {
                $carry[$agent->id] = $agent->first_name . ' ' . $agent->last_name;
                return $carry;
            }, ['' => lang('select_assigned_agent')]) : ['' => '-- Select Agency First --'], 
            $row, 
            ''
        ); ?>
                </div>
            </div>
        </div>

        <!-- Tab 5: Required Documents (Clean and Simplified) -->
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
                <div id="unifiedDocumentsForm" enctype="multipart/form-data">
                    <input type="hidden" name="candidate_id" value="<?= $row->id ?>">
                    <input type="hidden" name="is_required_documents" value="1">
                    <input type="hidden" name="notification_id" value="<?= $pending_notification_id ?>">

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
                                                placeholder="e.g., ID Copy, Degree Certificate" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>File *</label>
                                            <input type="file" name="required_documents[0][file]"
                                                class="form-control document-file"
                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
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
                        <button type="button" class="btn btn-success btn-lg" id="submitDocumentsBtn">
                            <i class="fa fa-paper-plane"></i> Submit All Documents to Agency
                        </button>
                    </div>
                </div>
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
// ========== VANILLA JS FALLBACK ==========
document.getElementById('mainCandidateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this);
    var id = document.getElementById('id').value;
    var action = id && id != '0' ?
        '<?= site_url("recruiter/candidates/update") ?>/' + id :
        '<?= site_url("recruiter/candidates/create") ?>';

    var submitBtn = document.querySelector('.save-button');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    fetch(action, {
            method: 'POST',
            body: formData
        })
        .then(r => r.text())
        .then(response => {
            var result = JSON.parse(response);
            if (result.success) {
                alert('Saved successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (result.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa fa-save"></i> Save Candidate';
        });
});
</script>

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