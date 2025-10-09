<?php
defined('BASEPATH') || exit('No direct script access allowed');

    function getValue(object|null $more_details, string $name): string{
        if (is_null($more_details)) {
            return '';
        }
        $name = trim($name);
        if (!isset($more_details->{$name})) {
            return '';
        }
        return $more_details->{$name};
    }

    // Check if we're in edit mode (existing candidate)
    $is_edit_mode = !empty($row);
?>

<style>
a.btn.btn-primary.add-item {
    background: #f00 !important;
    color: #fff !important;
}

/* Clean read-only styles - no background colors */
.readonly-field {
    cursor: not-allowed !important;
}

.form-control[readonly] {
    background-color: transparent !important;
}

.disabled-tab {
    opacity: 0.6;
    pointer-events: none;
}

/* Modal styles */
.info-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.info-modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 400px;
    border-radius: 5px;
    text-align: center;
}

.info-modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.info-modal-close:hover {
    color: black;
}
</style>

<!-- Info Modal -->
<div id="infoModal" class="info-modal">
    <div class="info-modal-content">
        <span class="info-modal-close">&times;</span>
        <h4>Information</h4>
        <p id="modalMessage"></p>
        <button class="btn btn-primary" onclick="closeModal()">OK</button>
    </div>
</div>

<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qm-tabs">
    <div class="quick-manage-heading">
        <?php if (empty($row)): ?>
        <h2>Add Candidate</h2>
        <p>
            <strong style="color: #dc3545;">Agencies cannot add candidates directly.</strong><br />
            Please contact recruiters to add new candidates to the system.
        </p>
        <?php else: ?>
        <h2>View Candidate <span><?= $row->first_name . ' ' . $row->last_name; ?></span></h2>
        <p>
            <strong>Viewing candidate information</strong> - Most fields are read-only.<br />
            You can update the candidate status and add internal notes.
        </p>
        <?php endif; ?>
    </div>

    <ul class="qm-tabs-header">
        <li rel="1" class="active">Personal Details</li>
        <li rel="2" class="<?= $is_edit_mode ? 'disabled-tab' : '' ?>">Professional Information</li>
        <li rel="3" class="active">Application Details</li>
        <li rel="4" class="<?= $is_edit_mode ? 'disabled-tab' : '' ?>">Agency Management</li>
    </ul>

    <div class="form-field-container">
        <?= form_open(); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>

        <!-- Tab 1: Personal Details (Read-only for agencies) -->
        <div rel="1" class="qm-tabs-tab active">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('reference_number|label_reference_number', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'e.g., CAND-001'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('email', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'email', 'candidate@email.com'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('first_name', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'First Name'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('last_name', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'Last Name'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('phone|label_phone', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'tel', '+27111234567'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('id_number|label_id_number', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'ID Number'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_date('date_of_birth|label_date_of_birth', $row, '', 'yyyy-mm-dd', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'Date of Birth'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('gender|label_gender', 
                        ['' => 'Please Select', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'], 
                        $row, '', ['disabled' => 'disabled']); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('address|label_address', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'Full Address'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <?= field_input('city|label_city', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'City'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('province|label_province', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'Province'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('postal_code|label_postal_code', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'Postal Code'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_input('country|label_country', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'Country', 'South Africa'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 2: Professional Information (Read-only for agencies) -->
        <div rel="2" class="qm-tabs-tab <?= $is_edit_mode ? 'disabled-tab' : '' ?>">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('highest_qualification|label_highest_qualification', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'e.g., BSc Computer Science'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('years_experience|label_years_experience', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'number', 'Years of experience'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('current_position|label_current_position', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'Current Job Title'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('current_company|label_current_company', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'Current Employer'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('current_salary|label_current_salary', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'Current Salary (ZAR)'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('expected_salary|label_expected_salary', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'Expected Salary (ZAR)'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('notice_period|label_notice_period', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'number', 'Notice Period (days)'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('source|label_source', 
                        [
                            '' => 'Please Select',
                            'website' => 'Website',
                            'agency' => 'Agency',
                            'referral' => 'Referral', 
                            'linkedin' => 'LinkedIn',
                            'indeed' => 'Indeed',
                            'other' => 'Other'
                        ], 
                        $row, '', ['disabled' => 'disabled']); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('cover_letter|label_cover_letter', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'Cover Letter / Additional Notes'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 3: Application Details (Status and notes are editable) -->
        <div rel="3" class="qm-tabs-tab active">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('status|label_status', 
                        [
                            'new' => 'New',
                            'reviewed' => 'Under Review', 
                            'shortlisted' => 'Shortlisted',
                            'interviewed' => 'Interviewed',
                            'rejected' => 'Rejected',
                            'hired' => 'Hired',
                            'on_hold' => 'On Hold'
                        ], 
                        $row, 'required'); // Status is editable ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('rating|label_rating', 
                        [
                            '' => 'No Rating',
                            '1' => '★ Poor',
                            '2' => '★★ Fair', 
                            '3' => '★★★ Good',
                            '4' => '★★★★ Very Good',
                            '5' => '★★★★★ Excellent'
                        ], 
                        $row, ''); // Rating is editable ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_date('application_date|label_application_date', $row, '', 'yyyy-mm-dd', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'Application Date'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('cv_file|label_cv_file', $row, '', ['readonly' => 'readonly', 'class' => 'readonly-field'], 'text', 'CV File Path/URL'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('notes|label_notes', $row, '', [], 'Internal Notes & Comments'); // Notes are editable ?>
                </div>
            </div>

            <?php if (!empty($row)): ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="info-box">
                        <h4>Application Timeline</h4>
                        <p><strong>Created:</strong>
                            <?= !empty($row->created_at) ? date('j M Y, H:i', strtotime($row->created_at)) : 'N/A' ?>
                        </p>
                        <p><strong>Last Updated:</strong>
                            <?= !empty($row->updated_at) ? date('j M Y, H:i', strtotime($row->updated_at)) : 'N/A' ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tab 4: Agency Management (Read-only for agencies) -->
        <div rel="4" class="qm-tabs-tab <?= $is_edit_mode ? 'disabled-tab' : '' ?>">
            <div class="row">
                <div class="col-lg-6">
                    <?php if (!empty($user_agency_id)): ?>
                    <?= form_hidden('agency_id', $user_agency_id); ?>
                    <div class="form-group">
                        <label class="control-label">Agency</label>
                        <div class="form-control-static">
                            <strong>
                                <?php 
                                    if (!empty($agencies_all) && is_object($agencies_all) && $agencies_all->num_rows() > 0) {
                                        $agency = $agencies_all->row();
                                        echo htmlspecialchars($agency->name, ENT_QUOTES, 'UTF-8');
                                    } else {
                                        echo 'Your Agency';
                                    }
                                ?>
                            </strong>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="form-group">
                        <label class="control-label">Agency</label>
                        <div class="form-control-static">
                            <strong>Your Agency</strong>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('job_id|label_job', $jobs_all, $row, '', ['disabled' => 'disabled']); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('assigned_agent_id|label_assigned_agent', $agents_all, $row, '', ['disabled' => 'disabled']); ?>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="control-label"><?= lang('label_enabled') ?></label>
                        <div class="form-control-static">
                            <strong><?= !empty($row) && $row->enabled ? 'Yes' : 'No' ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="btn-container" style="clear: left;">
            <?php if (!empty($row)): ?>
            <?php
                // Only show save button for existing candidates (for status/notes updates)
                echo save_button('Update Status & Notes');
                echo qm_tab_buttons();
                echo qm_close_button();
                ?>
            <?php else: ?>
            <!-- No save button for new candidates -->
            <?= qm_close_button(); ?>
            <?php endif; ?>
        </div>

        <?= form_close(); ?>
    </div>
</div>

<script type="text/javascript">
// Modal functions
function showModal(message) {
    document.getElementById('modalMessage').innerHTML = message;
    document.getElementById('infoModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('infoModal').style.display = 'none';
}

// Close modal when clicking on X
document.querySelector('.info-modal-close').addEventListener('click', closeModal);

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    var modal = document.getElementById('infoModal');
    if (event.target == modal) {
        closeModal();
    }
});

function save_form(el) {
    // Remove parsley validation from read-only fields first
    $('.readonly-field').each(function() {
        $(this).removeAttr('data-parsley-required');
        $(this).removeAttr('required');
    });

    $(el).closest('form').parsley().whenValidate().done(function() {
        let view = '<?= !empty($row->id) ? 'update' : 'create' ?>';
        let id = <?= !empty($row->id) ? $row->id : '0' ?>;

        ajax_submit_form(el, view, id);
    }).fail(function() {
        // Handle validation errors properly without alerts
        let statusField = $('select[name="status"]');
        if (statusField.length && !statusField.val()) {
            // Use the existing CRUD system's validation display instead of modal
            statusField.focus();
            statusField.addClass('parsley-error');
        }
    });
}

$(document).ready(function() {
    // Initialize all select values on page load
    $('.quick-manage-container select').each(function() {
        $(this).trigger('change');
    });

    // Disable tabs for agencies in edit mode
    $('.disabled-tab').on('click', function(e) {
        e.preventDefault();
        showModal('This section contains read-only information for agency users.');
    });

    // Remove parsley validation from read-only fields
    $('.readonly-field').each(function() {
        $(this).removeAttr('data-parsley-required');
        $(this).removeAttr('required');
    });

    // Handle tab clicks - prevent switching to disabled tabs
    $('.qm-tabs-header li').on('click', function() {
        if ($(this).hasClass('disabled-tab')) {
            showModal('This section contains read-only information for agency users.');
            return false;
        }

        var tabId = $(this).attr('rel');

        // Remove active class from all tabs and tab content
        $('.qm-tabs-header li').removeClass('active');
        $('.qm-tabs-tab').removeClass('active');

        // Add active class to clicked tab and corresponding content
        $(this).addClass('active');
        $('.qm-tabs-tab[rel="' + tabId + '"]').addClass('active');
    });

    <?php if (empty($row)): ?>
    // Hide all tabs and show message for new candidate form
    $('.qm-tabs-header').hide();
    $('.qm-tabs-tab').hide();

    // Show message that agencies can't add candidates
    $('.form-field-container').prepend(
        '<div class="alert alert-warning" style="margin: 20px;">' +
        '<strong><i class="fa fa-exclamation-triangle"></i> Information</strong><br>' +
        'Agencies cannot add candidates directly. Please contact recruiters to add new candidates to the system.' +
        '</div>'
    );
    <?php endif; ?>
});
</script>