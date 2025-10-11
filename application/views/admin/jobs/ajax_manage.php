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
        <li rel="2">Project & Pay</li>
        <li rel="3">Requirements</li>
        <li rel="4">Application</li>
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
                        'temporary' => 'Temporary'
                    ], $row ?? null, 'required'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('description', $row ?? null, 'required', ['placeholder' => 'Enter full job description'], 'Job Description'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 2: Project & Pay -->
        <div rel="2" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('project_overview', $row ?? null, '', ['placeholder' => 'Enter project overview'], 'Project Overview'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <?= field_input('pay_rate', $row ?? null, '', [], 'text', 'Enter pay rate (e.g., $52.00)'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('salary_min', $row ?? null, 'numeric', [], 'number', 'Minimum salary', 'step="0.01"'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('salary_max', $row ?? null, 'numeric', [], 'number', 'Maximum salary', 'step="0.01"'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Salary Currency</label>
                        <select name="salary_currency" class="form-control">
                            <option value="ZAR"
                                <?= set_select('salary_currency', 'ZAR', isset($row->salary_currency) && $row->salary_currency == 'ZAR') ?>>
                                ZAR</option>
                            <option value="USD"
                                <?= set_select('salary_currency', 'USD', isset($row->salary_currency) && $row->salary_currency == 'USD') ?>>
                                USD</option>
                            <option value="EUR"
                                <?= set_select('salary_currency', 'EUR', isset($row->salary_currency) && $row->salary_currency == 'EUR') ?>>
                                EUR</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <?= field_input('roster', $row ?? null, '', [], 'text', 'Enter roster details'); ?>
                </div>
                <div class="col-lg-4">
                    <?= field_input('accommodation', $row ?? null, '', [], 'text', 'Accommodation provided'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('transport', $row ?? null, '', [], 'text', 'Transport details'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_checkbox('is_remote|label_remote', $row ?? null, '', 'Remote work allowed', '1'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 3: Requirements -->
        <div rel="3" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_multi_select('skills|label_skills', $skill_options ?? [], $skills ?? [], 'Assign required skills'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_multi_select('qualifications|label_qualifications', $qualification_options ?? [], $qualifications ?? [], 'Assign required qualifications'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 4: Application -->
        <div rel="4" class="qm-tabs-tab">
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
</script>