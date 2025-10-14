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
    </ul>

    <div class="form-field-container">
        <?= form_open(); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>

        <?= form_hidden('agency_id', !empty($row->agency_id) ? $row->agency_id : ''); ?>
        <?= form_hidden('job_id', !empty($row->job_id) ? $row->job_id : ''); ?>

        <?php
        // Set default agency_id from the first selected additional agency (if any)
        $default_agency_id = '';
        if (!empty($additional_agency_ids) && is_array($additional_agency_ids)) {
            $default_agency_id = $additional_agency_ids[0];
        }
        ?>


        <!-- Tab 1: Personal -->
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
            <!-- Add other personal fields as needed -->
        </div>

        <!-- Tab 2: Professional -->
        <div rel="2" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('current_position|label_current_position', $row, '', [], 'text', 'Current Job Title'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('current_company|label_current_company', $row, '', [], 'text', 'Current Employer'); ?>
                </div>
            </div>
            <!-- Add other professional fields -->
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
                <div class="col-lg-12">
                    <?= field_textarea('notes|label_notes', $row, '', [], 'Internal Notes'); ?>
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
                    <small class="text-muted">First selected agency will be set as primary</small>
                </div>
                <div class="col-lg-6">
                    <?= field_multi_select('additional_job_ids|label_jobs', 
                $additional_job_options, 
                $additional_job_ids,
                'Select jobs (first selected becomes primary)'
            ); ?>
                    <small class="text-muted">First selected job will be set as primary</small>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('assigned_agent_id|label_assigned_agent', 
                !empty($agents_all) ? array_reduce($agents_all, function($carry, $agent) {
                    $carry[$agent->id] = $agent->first_name . ' ' . $agent->last_name;
                    return $carry;
                }, ['' => '-- Select Agent --']) : ['' => '-- Select Agency First --'], 
                $row, 
                ''
            ); ?>
                </div>
            </div>
        </div>

        <div class="btn-container">
            <?= qm_tab_buttons(); ?>
            <?= qm_close_button(); ?>
            <?= save_button('Save Candidate'); ?>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<script>
function save_form(el) {
    $(el).closest('form').parsley().whenValidate().done(function() {
        let view = '<?= !empty($row->id) ? 'update' : 'create' ?>';
        let id = <?= !empty($row->id) ? $row->id : '0' ?>;
        ajax_submit_form(el, view, id);
    });
}

$(document).ready(function() {
    // Auto-generate reference if new
    if ($('input[name="reference_number"]').val() === '') {
        $('input[name="reference_number"]').val('<?= $this->Model_candidates->generate_reference_number() ?>');
    }

    // Set today's date
    if ($('input[name="application_date"]').val() === '') {
        $('input[name="application_date"]').val(new Date().toISOString().split('T')[0]);
    }

    // Update hidden agency_id and job_id when multi-selects change
    $('select[name="additional_agency_ids[]"]').on('change', function() {
        const selectedAgencies = $(this).val();
        if (selectedAgencies && selectedAgencies.length > 0) {
            // Use the first selected agency as primary
            const primaryAgencyId = selectedAgencies[0];
            $('input[name="agency_id"]').val(primaryAgencyId);

            // Load agents for the primary agency
            $.get('<?= site_url("recruiter/candidates/get_agents/") ?>' + primaryAgencyId, function(
                data) {
                let options = '<option value="">-- Select Agent --</option>';
                $.each(data, function(index, agent) {
                    options +=
                        `<option value="${agent.id}">${agent.first_name} ${agent.last_name}</option>`;
                });
                $('#assigned_agent_id').html(options);
            });
        } else {
            $('input[name="agency_id"]').val('');
            $('#assigned_agent_id').html('<option value="">-- Select Agency First --</option>');
        }
    });

    // Update hidden job_id when jobs multi-select changes
    $('select[name="additional_job_ids[]"]').on('change', function() {
        const selectedJobs = $(this).val();
        if (selectedJobs && selectedJobs.length > 0) {
            // Use the first selected job as primary
            const primaryJobId = selectedJobs[0];
            $('input[name="job_id"]').val(primaryJobId);
        } else {
            $('input[name="job_id"]').val('');
        }
    });

    // Initialize with current values (in case of edit)
    const currentAgencies = $('select[name="additional_agency_ids[]"]').val();
    if (currentAgencies && currentAgencies.length > 0) {
        $('input[name="agency_id"]').val(currentAgencies[0]);
    }

    const currentJobs = $('select[name="additional_job_ids[]"]').val();
    if (currentJobs && currentJobs.length > 0) {
        $('input[name="job_id"]').val(currentJobs[0]);
    }

    // Initialize multi-select styles
    $('select[multiple]').each(function() {
        $(this).addClass('multi-select');
    });
});
</script>

<style>
.multi-select {
    height: 120px !important;
    min-height: 120px;
}

.multi-select option {
    padding: 8px 12px;
}
</style>