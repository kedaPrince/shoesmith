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

    // Convert objects to arrays for dropdown options
    $agencies_all_array = [];
    if (!empty($agencies_all) && is_object($agencies_all)) {
        $agencies_all_array = $agencies_all->result_array();
    } elseif (is_array($agencies_all)) {
        $agencies_all_array = $agencies_all;
    }

    $jobs_all_array = [];
    if (!empty($jobs_all) && is_object($jobs_all)) {
        $jobs_all_array = $jobs_all->result_array();
    } elseif (is_array($jobs_all)) {
        $jobs_all_array = $jobs_all;
    }

    $agents_all_array = [];
    if (!empty($agents_all) && is_object($agents_all)) {
        $agents_all_array = $agents_all->result_array();
    } elseif (is_array($agents_all)) {
        $agents_all_array = $agents_all;
    }
?>

<style>
a.btn.btn-primary.add-item {
    background: #f00 !important;
    color: #fff !important;
}
</style>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qm-tabs">
    <div class="quick-manage-heading">
        <?php if (empty($row)): ?>
        <h2>Add Candidate</h2>
        <p>
            Here you can <span>add new candidates</span> to the system.<br />
            Fill in the candidate's details and application information.
        </p>
        <?php else: ?>
        <h2>Edit Candidate <span><?= $row->first_name . ' ' . $row->last_name; ?></span></h2>
        <p>
            Here you can <span>edit candidate information</span> and track their application progress.<br />
            Update their status, notes, and other relevant details.
        </p>
        <?php endif; ?>
    </div>

    <ul class="qm-tabs-header">
        <li rel="1" class="active">Personal Details</li>
        <li rel="2">Professional Information</li>
        <li rel="3">Application Details</li>
        <li rel="4">Agency Management</li>
    </ul>

    <div class="form-field-container">
        <?= form_open(); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>

        <!-- Tab 1: Personal Details -->
        <div rel="1" class="qm-tabs-tab active">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('reference_number|label_reference_number', $row, 'required', [], 'text', 'e.g., CAND-001'); ?>
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
                    <?= field_input('phone|label_phone', $row, '', [], 'tel', '+27111234567'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('id_number|label_id_number', $row, '', [], 'text', 'ID Number'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_date('date_of_birth|label_date_of_birth', $row, '', 'yyyy-mm-dd', [], 'Date of Birth'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('gender|label_gender', 
                        ['' => 'Please Select', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'], 
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

        <!-- Tab 2: Professional Information -->
        <div rel="2" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('highest_qualification|label_highest_qualification', $row, '', [], 'text', 'e.g., BSc Computer Science'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('years_experience|label_years_experience', $row, 'numeric', [], 'number', 'Years of experience'); ?>
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
                    <?= field_input('current_salary|label_current_salary', $row, 'decimal', [], 'text', 'Current Salary (ZAR)'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('expected_salary|label_expected_salary', $row, 'decimal', [], 'text', 'Expected Salary (ZAR)'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('notice_period|label_notice_period', $row, 'numeric', [], 'number', 'Notice Period (days)'); ?>
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
                        $row, ''); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('cover_letter|label_cover_letter', $row, '', [], 'Cover Letter / Additional Notes'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 3: Application Details -->
        <div rel="3" class="qm-tabs-tab">
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
                        $row, 'required'); ?>
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
                        $row, ''); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_date('application_date|label_application_date', $row, '', 'yyyy-mm-dd', [], 'Application Date'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('cv_file|label_cv_file', $row, '', [], 'text', 'CV File Path/URL'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('notes|label_notes', $row, '', [], 'Internal Notes & Comments'); ?>
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

        <!-- Tab 4: Agency Management -->
        <div rel="4" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('agency_id|label_agency', $agencies_all_array, $row, 'required'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('job_id|label_job', $jobs_all_array, $row, ''); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('assigned_agent_id|label_assigned_agent', $agents_all_array, $row, ''); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_checkbox('enabled|label_enabled', $row, '1', !empty($row) ? $row->enabled : 1); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="info-box">
                        <h4>Agency Information</h4>
                        <p>This candidate is managed by your agency. Ensure all information is accurate and up-to-date.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="btn-container" style="clear: left;">
            <?php
            echo save_button('Save Candidate');
            echo qm_tab_buttons();
            echo qm_close_button();
            if (!empty($row)) {
                echo $row->enabled ? disable_button($identifier, $row->id) : enable_button($identifier, $row->id);
            }
            ?>
        </div>

        <?= form_close(); ?>
    </div>
</div>

<script type="text/javascript">
function save_form(el) {
    // This removes the parsley validation for elements outside of the form.
    let elementsToRemove = document.querySelectorAll('[class*=parsley-class-container]');
    elementsToRemove.forEach(function(element) {
        if (!element.closest('.quick-manage-form-container')) {
            let classesToRemove = Array.from(element.classList).filter(function(className) {
                return className.includes('parsley-class-container');
            });
            element.classList.remove(...classesToRemove);
        }
    });

    $(el).closest('form').parsley().whenValidate().done(function() {
        let view = '<?= !empty($row->id) ? 'update' : 'create' ?>';
        let id = <?= !empty($row->id) ? $row->id : '0' ?>;

        ajax_submit_form(el, view, id);
    });
}

$(document).ready(function() {
    // Initialize all select values on page load
    $('.quick-manage-container select').each(function() {
        $(this).trigger('change');
    });

    // Auto-generate reference number if empty
    if ($('input[name="reference_number"]').val() === '') {
        $.get('<?= site_url("agency/candidates/generate_reference") ?>', function(data) {
            if (data.success) {
                $('input[name="reference_number"]').val(data.reference);
            }
        });
    }

    // Set application date to today if empty
    if ($('input[name="application_date"]').val() === '') {
        let today = new Date().toISOString().split('T')[0];
        $('input[name="application_date"]').val(today);
    }

    <?php if (empty($row)): ?>
    $('.qm-tabs-header').hide();
    <?php endif; ?>
});
</script>