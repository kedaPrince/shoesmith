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
        <h2>Edit Job <span><?= htmlspecialchars($row->name ?? '', ENT_QUOTES, 'UTF-8'); ?></span></h2>
        <p>
            Update the job details below.<br />
            Changes will take effect immediately.
        </p>
        <?php endif; ?>
    </div>

    <ul class="qm-tabs-header">
        <li rel="1" class="active">General</li>
        <li rel="2">Pay</li>
        <li rel="3">Project</li>
        <li rel="4">Requirements</li>
        <li rel="5">Application</li>
        <li rel="6">Medical</li>
    </ul>

    <div class="form-field-container">
        <?= form_open(); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>

        <!-- Tab 1: General -->
        <div rel="1" class="qm-tabs-tab active">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('name', $row ?? null, 'required', [], 'text', 'Enter job title'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('reference_number', $row ?? null, 'required', [], 'text', 'Enter reference number'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('agency_id|label_agency', $agency_options ?? [], $row ?? null, 'required'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('industry_id|label_industry', $industry_options ?? [], $row ?? null, ''); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('department', $row ?? null, '', [], 'text', 'Enter department'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('employment_type|label_job_type', [
        'full-time' => 'Full-time',
        'part-time' => 'Part-time',
        'contract' => 'Contract',
        'internship' => 'Internship',
        'temporary' => 'Temporary',
        'casual' => 'Casual',
        'freelance' => 'Freelance',
        'seasonal' => 'Seasonal'
    ], $row ?? null, 'required'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('description', $row ?? null, 'required', ['placeholder' => 'Enter full job description'], 'Job Description'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 2: Pay -->
        <div rel="2" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('pay_type|label_pay_type', [
                'salary' => 'Salary',
                'hourly' => 'Hourly',
                'commission' => 'Commission',
                'bonus' => 'Bonus',
                'piece_rate' => 'Piece Rate',
                'tips' => 'Tips',
                'daily_rate' => 'Daily Rate',
                'weekly_rate' => 'Weekly Rate',
                'monthly_rate' => 'Monthly Rate',
                'annual_salary' => 'Annual Salary'
            ], $row ?? null); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('pay_cycle|label_pay_cycle', [
                'hourly' => 'Hourly',
                'daily' => 'Daily',
                'weekly' => 'Weekly',
                'bi-weekly' => 'Bi-Weekly',
                'monthly' => 'Monthly',
                'quarterly' => 'Quarterly',
                'annually' => 'Annually',
                'project' => 'Per Project',
                'milestone' => 'Per Milestone'
            ], $row ?? null); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('contract_type|label_contract_type', [
                'permanent' => 'Permanent',
                'fixed_term' => 'Fixed Term',
                'casual' => 'Casual',
                'seasonal' => 'Seasonal',
                'project_based' => 'Project Based',
                'consultancy' => 'Consultancy',
                'internship' => 'Internship',
                'probation' => 'Probation'
            ], $row ?? null); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('pay_rate', $row ?? null, '', [], 'text', 'Enter pay rate (e.g., $52.00)'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <?= field_input('salary_min', $row ?? null, 'numeric', [], 'number', 'Minimum salary', 'step="0.01"'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('salary_max', $row ?? null, 'numeric', [], 'number', 'Maximum salary', 'step="0.01"'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('salary_currency', $row ?? null, '', [], 'text', 'Currency (e.g., USD, EUR)', 'placeholder="USD"'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 3: Project -->
        <div rel="3" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('project_overview', $row ?? null, '', ['placeholder' => 'Enter project overview'], 'Project Overview'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('transport', $row ?? null, '', [], 'text', 'Transport details'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('roster', $row ?? null, '', [], 'text', 'Enter roster details'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('accommodation', $row ?? null, '', [], 'text', 'Accommodation provided'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('location', $row ?? null, '', [], 'text', 'Enter job location'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('site', $row ?? null, '', [], 'text', 'Enter site details'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_checkbox('is_remote|label_remote', $row ?? null, '', 'Remote work allowed', '1'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 4: Requirements -->
        <div rel="4" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_multi_select('skills|label_skills', $skill_options ?? [], $skills ?? [], 'Assign required skills'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_multi_select('qualifications|label_qualifications', $qualification_options ?? [], $qualifications ?? [], 'Assign required qualifications'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 5: Application -->
        <div rel="5" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_email('application_email', $row ?? null, 'valid-email', [], 'Enter application email'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('application_url', $row ?? null, 'valid-url', [], 'url', 'Enter application URL'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_date('closing_date|label_closing_date', $row ?? null, '', 'yyyy-mm-dd', []); ?>
                </div>
            </div>
        </div>

        <!-- Tab 6: Medical Requirements -->
        <div rel="6" class="qm-tabs-tab">
            <div class="alert alert-info">
                <i class="fa fa-heartbeat"></i> Job medical and physical requirements for this position.
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('mod_job_medical_requirements.medical_requirements', isset($medical_requirements->medical_requirements) ? $medical_requirements->medical_requirements : '', '', ['placeholder' => 'Describe specific medical requirements, health standards, or physical conditions required for this job...'], 'Specific Medical Requirements'); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('mod_job_medical_requirements.fitness_level|Required Fitness Level', [
                'low' => 'Low',
                'medium' => 'Medium', 
                'high' => 'High',
                'very_high' => 'Very High'
            ], isset($medical_requirements->fitness_level) ? $medical_requirements : null, ''); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_textarea('mod_job_medical_requirements.physical_demands', isset($medical_requirements->physical_demands) ? $medical_requirements->physical_demands : '', '', ['placeholder' => 'Describe physical demands (lifting, standing, walking, etc.)...'], 'Physical Demands'); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <h6 class="border-bottom pb-2 mb-3">Medical Screening Requirements</h6>
                </div>

                <div class="col-lg-4 mb-3">
                    <?= field_checkbox('mod_job_medical_requirements.health_screening_required|Health Screening Required', isset($medical_requirements->health_screening_required) ? $medical_requirements : null, '', 'Health screening required', '1'); ?>
                </div>

                <div class="col-lg-4 mb-3">
                    <?= field_checkbox('mod_job_medical_requirements.drug_test_required|Drug Test Required', isset($medical_requirements->drug_test_required) ? $medical_requirements : null, '', 'Drug test required', '1'); ?>
                </div>

                <div class="col-lg-4 mb-3">
                    <?= field_checkbox('mod_job_medical_requirements.vaccination_required|Vaccination Required', isset($medical_requirements->vaccination_required) ? $medical_requirements : null, '', 'Vaccination required', '1'); ?>
                </div>
            </div>

            <div class="row" id="vaccinations-section"
                style="<?= (isset($medical_requirements->vaccination_required) && $medical_requirements->vaccination_required) ? '' : 'display: none;' ?>">
                <div class="col-lg-12">
                    <?= field_textarea('mod_job_medical_requirements.specific_vaccinations', isset($medical_requirements->specific_vaccinations) ? $medical_requirements->specific_vaccinations : '', '', ['placeholder' => 'List specific vaccinations required (e.g., COVID-19, Hepatitis B, Flu shot, etc.)...'], 'Required Vaccinations'); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 mb-3">
                    <?= field_checkbox('mod_job_medical_requirements.medical_certificate_required|Medical Certificate Required', isset($medical_requirements->medical_certificate_required) ? $medical_requirements : null, '', 'Medical certificate required', '1'); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('mod_job_medical_requirements.work_environment', isset($medical_requirements->work_environment) ? $medical_requirements->work_environment : '', '', ['placeholder' => 'Describe the work environment (office, construction site, laboratory, etc.)...'], 'Work Environment Description'); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('mod_job_medical_requirements.hazard_exposures', isset($medical_requirements->hazard_exposures) ? $medical_requirements->hazard_exposures : '', '', ['placeholder' => 'List potential hazard exposures (chemicals, noise, heights, machinery, etc.)...'], 'Potential Hazard Exposures'); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('mod_job_medical_requirements.ppe_requirements', isset($medical_requirements->ppe_requirements) ? $medical_requirements->ppe_requirements : '', '', ['placeholder' => 'List required PPE (safety glasses, hard hat, gloves, respirator, etc.)...'], 'PPE Requirements'); ?>
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

    // Initialize multi-select if select2 is available
    if (typeof $.fn.select2 !== 'undefined') {
        $('select[multiple]').select2({
            width: '100%',
            placeholder: function() {
                return $(this).data('placeholder') || 'Select options';
            }
        });
    }
});

$(document).ready(function() {
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

    // Toggle vaccinations section based on vaccination requirement
    $('input[name="mod_job_medical_requirements.vaccination_required"]').change(function() {
        if ($(this).is(':checked')) {
            $('#vaccinations-section').slideDown();
        } else {
            $('#vaccinations-section').slideUp();
        }
    });

    // Initialize multi-select if select2 is available
    if (typeof $.fn.select2 !== 'undefined') {
        $('select[multiple]').select2({
            width: '100%',
            placeholder: function() {
                return $(this).data('placeholder') || 'Select options';
            }
        });
    }

    // Initialize select values
    $('.quick-manage-container select').each(function() {
        $(this).trigger('change');
    });
});
</script>