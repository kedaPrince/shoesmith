<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<style>
    body .form-control {
    color: var(--font-color);
    background: #dfdfdf;
}
body .form-control {
    color: #000000;
    background: #dfdfdf;
}
</style>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qm-tabs">
    <div class="quick-manage-heading">
        <?php if (empty($row)): ?>
        <h2>Add Job</h2>
        <p>
            Here you can <span>add a new job listing</span> to the system.<br />
            All jobs are automatically assigned to this agency.
        </p>
        <?php else: ?>
        <h2>Edit Job <span><?= htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8'); ?></span></h2>
        <p>
            Update the job details below.<br />
            Changes will take effect immediately.
        </p>
        <?php endif; ?>
    </div>

    <ul class="qm-tabs-header">
        <li rel="1" class="active">General</li>
        <li rel="2">Project & Pay</li>
        <li rel="3">Requirements</li>
        <li rel="4">Application</li>
    </ul>

    <div class="form-field-container">
        <?= form_open(); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>

        <!-- Auto-set agency_id -->
        <?php if (!empty($agency_id)): ?>
        <?= form_hidden('agency_id', $agency_id); ?>
        <?php endif; ?>

        <!-- Tab 1: General -->
        <div rel="1" class="qm-tabs-tab active">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('name', $row, 'required', [], 'text', 'Enter job title'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('reference_number', $row, 'required', [], 'text', 'Enter reference number'); ?>
                </div>
            </div>
            <div class="row">
                <!-- Replace the agency dropdown section -->
                <!-- Replace the agency dropdown section -->
                <div class="col-lg-6">
                    <?php 
                        // Determine if we should show agency as static field
                        $show_static_agency = !empty($current_agency_id) || !empty($user_agency_id);
                        $final_agency_id = !empty($current_agency_id) ? $current_agency_id : $user_agency_id;
                    ?>

                    <?php if ($show_static_agency && !empty($final_agency_id)): ?>
                    <?= form_hidden('agency_id', $final_agency_id); ?>
                    <div class="form-control-static">
                        <strong>Agency:</strong><br>
                        <?php 
            // Display agency name
            if (!empty($agency_options) && $agency_options->num_rows() > 0) {
                $agency_name = $agency_options->row()->name;
                echo htmlspecialchars($agency_name, ENT_QUOTES, 'UTF-8');
                log_message('debug', 'Displaying static agency: ' . $agency_name . ' (ID: ' . $final_agency_id . ')');
            } else {
                echo 'Your Agency (ID: ' . $final_agency_id . ')';
                log_message('debug', 'Agency options empty, showing fallback for ID: ' . $final_agency_id);
            }
            ?>
                    </div>
                    <?php else: ?>
                    <?= field_dropdown('agency_id|label_agency', $agency_options, $row, 'required'); ?>
                    <?php log_message('debug', 'Showing agency dropdown with ' . $agency_options->num_rows() . ' options'); ?>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('industry_id|label_industry', $industry_options, $row, ''); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('department', $row, '', [], 'text', 'Enter department'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('employment_type|label_job_type', [
                        'full-time' => 'Full-time',
                        'part-time' => 'Part-time',
                        'contract' => 'Contract',
                        'internship' => 'Internship',
                        'temporary' => 'Temporary'
                    ], $row, 'required'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('description', $row, 'required', ['placeholder' => 'Enter full job description'], 'Job Description'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 2: Project & Pay -->
        <div rel="2" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('project_overview', $row, '', ['placeholder' => 'Enter project overview'], 'Project Overview'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <?= field_input('pay_rate', $row, '', [], 'text', 'Enter pay rate (e.g., $52.00)'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('salary_min', $row, 'numeric', [], 'text', 'Minimum salary'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('salary_max', $row, 'numeric', [], 'text', 'Maximum salary'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('roster', $row, '', [], 'text', 'Enter roster details'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('accommodation', $row, '', [], 'text', 'Accommodation provided'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('transport', $row, '', [], 'text', 'Transport details'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_checkbox('is_remote|label_remote', $row, '', 'Remote allowed', '1'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 3: Requirements -->
        <div rel="3" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_multi_select('skills|label_skills', $skill_options, $skills, 'Assign required skills'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_multi_select('qualifications|label_qualifications', $qualification_options, $qualifications, 'Assign required qualifications'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 4: Application -->
        <div rel="4" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_email('application_email', $row, 'valid-email', [], 'Enter application email'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('application_url', $row, 'valid-url', [], 'url', 'Enter application URL'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_date('closing_date|label_closing_date', $row, '', 'yyyy-mm-dd', []); ?>
                </div>
            </div>
        </div>

        <div class="btn-container" style="clear: left;">
            <?= qm_tab_buttons(); ?>
            <?= qm_close_button(); ?>
        </div>

        <?= form_close(); ?>
    </div>
</div>

<script type="text/javascript">
function save_form(el) {
    $(el).closest('form').parsley().whenValidate().done(function() {
        let view = '<?= !empty($row->id) ? 'update' : 'create' ?>';
        let id = <?= !empty($row->id) ? $row->id : '0' ?>;
        ajax_submit_form(el, view, id);
    });
}

$(document).ready(function() {
    $('.quick-manage-container select').each(function() {
        $(this).trigger('change');
    });

    // Handle tab clicks
    $('.qm-tabs-header li').on('click', function() {
        var tabId = $(this).attr('rel');

        // Remove active class from all tabs and tab content
        $('.qm-tabs-header li').removeClass('active');
        $('.qm-tabs-tab').removeClass('active');

        // Add active class to clicked tab and corresponding content
        $(this).addClass('active');
        $('.qm-tabs-tab[rel="' + tabId + '"]').addClass('active');
    });

    // Initialize select values
    $('.quick-manage-container select').each(function() {
        $(this).trigger('change');
    });
});
</script>