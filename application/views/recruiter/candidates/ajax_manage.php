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
        <?= form_open('', ['enctype' => 'multipart/form-data']); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>

        <?= form_hidden('agency_id', !empty($row->agency_id) ? $row->agency_id : ''); ?>
        <?= form_hidden('job_id', !empty($row->job_id) ? $row->job_id : ''); ?>

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
    // Create FormData object to handle file uploads
    let form = $(el).closest('form')[0];
    let formData = new FormData(form);

    // Add additional data that might be needed
    formData.append('ajax', '1');

    $(form).parsley().whenValidate().done(function() {
        let view = '<?= !empty($row->id) ? 'update' : 'create' ?>';
        let id = <?= !empty($row->id) ? $row->id : '0' ?>;

        // Determine the correct URL based on whether we're creating or updating
        let url = '<?= site_url("recruiter/candidates/") ?>';
        if (view === 'update' && id > 0) {
            url += 'update/' + id;
        } else {
            url += 'create';
        }

        // Use custom AJAX for file uploads
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $(el).prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> Saving...');
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    show_message(response.message || 'Candidate saved successfully!', 'success');

                    // Refresh the listing or close the quick manage
                    setTimeout(function() {
                        $('.close-quick-manage').trigger('click');
                        // Reload the candidates listing
                        if (typeof load_listing === 'function') {
                            load_listing('<?= $this->pageName ?>');
                        } else {
                            location.reload();
                        }
                    }, 1000);
                } else {
                    // Show error message
                    show_message(response.error || 'Error saving candidate', 'error');
                    $(el).prop('disabled', false).html('Save Candidate');

                    // Highlight error fields
                    if (response.fields) {
                        $.each(response.fields, function(field, error) {
                            let fieldElement = $('[name="' + field + '"]');
                            fieldElement.addClass('parsley-error');
                            // Remove existing error messages
                            fieldElement.next('.parsley-errors-list').remove();
                            fieldElement.after('<span class="parsley-errors-list filled">' +
                                error + '</span>');
                        });
                    }
                }
            },
            error: function(xhr, status, error) {
                show_message('Error saving candidate: ' + error, 'error');
                $(el).prop('disabled', false).html('Save Candidate');
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    });
}

$(document).ready(function() {
    // Auto-generate reference if new
    if ($('input[name="reference_number"]').val() === '') {
        $('input[name="reference_number"]').val('<?= $this->Model_candidates->generate_reference_number() ?>');
    }

    // Set today's date for application date if empty
    if ($('input[name="application_date"]').val() === '') {
        $('input[name="application_date"]').val(new Date().toISOString().split('T')[0]);
    }

    // Set default country if empty
    if ($('input[name="country"]').val() === '') {
        $('input[name="country"]').val('South Africa');
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
                if (data && data.length > 0) {
                    $.each(data, function(index, agent) {
                        options +=
                            `<option value="${agent.id}">${agent.first_name} ${agent.last_name}</option>`;
                    });
                }
                $('#assigned_agent_id').html(options);

                // Set the current agent if editing
                <?php if (!empty($row->assigned_agent_id)): ?>
                $('#assigned_agent_id').val('<?= $row->assigned_agent_id ?>');
                <?php endif; ?>
            }).fail(function() {
                $('#assigned_agent_id').html('<option value="">-- No agents found --</option>');
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

        // Trigger agent loading for current agency
        $.get('<?= site_url("recruiter/candidates/get_agents/") ?>' + currentAgencies[0], function(data) {
            let options = '<option value="">-- Select Agent --</option>';
            if (data && data.length > 0) {
                $.each(data, function(index, agent) {
                    options +=
                        `<option value="${agent.id}">${agent.first_name} ${agent.last_name}</option>`;
                });
            }
            $('#assigned_agent_id').html(options);

            // Set the current agent if editing
            <?php if (!empty($row->assigned_agent_id)): ?>
            $('#assigned_agent_id').val('<?= $row->assigned_agent_id ?>');
            <?php endif; ?>
        }).fail(function() {
            $('#assigned_agent_id').html('<option value="">-- No agents found --</option>');
        });
    }

    const currentJobs = $('select[name="additional_job_ids[]"]').val();
    if (currentJobs && currentJobs.length > 0) {
        $('input[name="job_id"]').val(currentJobs[0]);
    }

    // Initialize multi-select styles
    $('select[multiple]').each(function() {
        $(this).addClass('multi-select');
    });

    // File upload size validation
    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        if (file) {
            const fileSize = file.size / 1024 / 1024; // in MB
            if (fileSize > 10) {
                alert('File size must be less than 10MB');
                $(this).val('');
            } else {
                // Validate file type
                const fileName = file.name.toLowerCase();
                const validExtensions = ['.pdf', '.doc', '.docx'];
                const isValidExtension = validExtensions.some(ext => fileName.endsWith(ext));

                if (!isValidExtension) {
                    alert('Please select a valid file type: PDF, DOC, or DOCX');
                    $(this).val('');
                }
            }
        }
    });

    // Tab navigation
    $('.qm-tabs-header li').on('click', function() {
        const tabId = $(this).attr('rel');
        $('.qm-tabs-header li').removeClass('active');
        $(this).addClass('active');
        $('.qm-tabs-tab').removeClass('active');
        $('.qm-tabs-tab[rel="' + tabId + '"]').addClass('active');
    });

    // Pre-populate from job if coming from jobs listing
    <?php if (!empty($primary_job_id) && !empty($primary_agency_id)): ?>
    // Ensure the job and agency are selected in multi-selects
    const jobSelect = $('select[name="additional_job_ids[]"]');
    const agencySelect = $('select[name="additional_agency_ids[]"]');

    if (!jobSelect.val() || jobSelect.val().length === 0) {
        jobSelect.val([<?= $primary_job_id ?>]);
        $('input[name="job_id"]').val(<?= $primary_job_id ?>);
    }

    if (!agencySelect.val() || agencySelect.val().length === 0) {
        agencySelect.val([<?= $primary_agency_id ?>]);
        $('input[name="agency_id"]').val(<?= $primary_agency_id ?>);

        // Load agents for the pre-selected agency
        $.get('<?= site_url("recruiter/candidates/get_agents/") ?>' + <?= $primary_agency_id ?>, function(
        data) {
            let options = '<option value="">-- Select Agent --</option>';
            if (data && data.length > 0) {
                $.each(data, function(index, agent) {
                    options +=
                        `<option value="${agent.id}">${agent.first_name} ${agent.last_name}</option>`;
                });
            }
            $('#assigned_agent_id').html(options);
        }).fail(function() {
            $('#assigned_agent_id').html('<option value="">-- No agents found --</option>');
        });
    }
    <?php endif; ?>
});

// Helper function to show messages
function show_message(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const messageHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="margin: 15px;">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;

    // Remove any existing alerts
    $('.alert').remove();

    // Add new alert at the top of the form
    $('.quick-manage-heading').after(messageHtml);

    // Auto-remove success messages after 5 seconds
    if (type === 'success') {
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }
}
</script>

<style>
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
</style>