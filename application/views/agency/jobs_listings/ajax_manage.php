<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php
// Get CSRF tokens for JavaScript
$csrf_token_name = $this->security->get_csrf_token_name();
$csrf_token_hash = $this->security->get_csrf_hash();
?>
<style>
body .form-control {
    color: var(--font-color);
    background: #dfdfdf;
}

body .form-control {
    color: #000000;
    background: #dfdfdf;
}

.clean-html-field {
    /* Ensure clean display of text */
    white-space: pre-wrap;
    word-wrap: break-word;
}

.job-description-field {
    min-height: 120px;
    font-family: inherit;
    line-height: 1.5;
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
                    <?php if (empty($row)): ?>
                    <div class="form-group">
                        <label for="reference_number">Reference Number</label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number"
                            value="<?= !empty($generated_reference) ? htmlspecialchars($generated_reference, ENT_QUOTES, 'UTF-8') : '' ?>"
                            readonly>
                        <small class="form-text text-muted">Reference number is automatically generated</small>
                    </div>
                    <?php else: ?>
                    <div class="form-group">
                        <label for="reference_number">Reference Number</label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number"
                            value="<?= !empty($row->reference_number) ? htmlspecialchars($row->reference_number, ENT_QUOTES, 'UTF-8') : '' ?>"
                            readonly>
                        <small class="form-text text-muted">Reference number cannot be changed</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
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
                    <?php
        $employment_type_options = [
            'full-time' => 'Full-time',
            'part-time' => 'Part-time', 
            'contract' => 'Contract',
            'internship' => 'Internship',
            'temporary' => 'Temporary'
        ];
        
        $selected_employment_type = !empty($row->employment_type) ? $row->employment_type : '';
        ?>
                    <div class="form-group">
                        <label for="employment_type">Job Type *</label>
                        <select name="employment_type" id="employment_type" class="form-control" required>
                            <option value="">Select Job Type</option>
                            <?php foreach ($employment_type_options as $value => $label): ?>
                            <option value="<?= $value ?>"
                                <?= $selected_employment_type == $value ? 'selected="selected"' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('description', $row, 'required', [
                    'placeholder' => 'Enter full job description',
                    'id' => 'job_description',
                    'class' => 'form-control job-description-field clean-html-field'
                ], 'Job Description'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 2: Project & Pay -->
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
                <!-- ADD LOCATION FIELD HERE -->
                <div class="col-lg-6">
                    <?= field_input('location', $row, '', [], 'text', 'Enter job location (e.g., New York, NY)'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('transport', $row, '', [], 'text', 'Transport details'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_checkbox('is_remote|label_remote', $row, '', 'Remote allowed', '1'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 3: Requirements - UPDATED TO TEXTAREAS -->
        <div rel="3" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_textarea('skills|label_skills', $row, '', [
                        'placeholder' => 'Enter required skills (one per line or comma separated)',
                        'rows' => 6,
                        'class' => 'form-control'
                    ], 'Required Skills'); ?>
                    <small class="form-text text-muted">Enter one skill per line or separate with commas</small>
                </div>
                <div class="col-lg-6">
                    <?= field_textarea('qualifications|label_qualifications', $row, '', [
                        'placeholder' => 'Enter required qualifications (one per line or comma separated)',
                        'rows' => 6,
                        'class' => 'form-control'
                    ], 'Required Qualifications'); ?>
                    <small class="form-text text-muted">Enter one qualification per line or separate with commas</small>
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

function generateReferenceNumber() {
    // Create form data with CSRF token
    var formData = new FormData();
    formData.append('<?= $csrf_token_name ?>', '<?= $csrf_token_hash ?>');

    $.ajax({
        url: '<?= site_url("agency/jobs_listings/generate_reference") ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#reference_number').val(response.reference);
            } else {
                alert('Failed to generate new reference');
            }
        },
        error: function() {
            alert('Error generating new reference');
        }
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



    // Generate reference number when button is clicked
    $('#generate-reference').on('click', function() {
        generateReferenceNumber();
    });
});

// Add this to jobs ajax_manage.php script section
// Add this to jobs ajax_manage.php script section
$(document).ready(function() {
    // Function to clean HTML content from form fields
    function cleanHTMLContent(text) {
        if (!text) return text;

        // Remove surrounding <p> tags but keep the content
        text = text.replace(/<p[^>]*>/gi, '').replace(/<\/p>/gi, '\n');

        // Remove other common HTML tags but keep their content
        text = text.replace(/<br\s*\/?>/gi, '\n');
        text = text.replace(/<div[^>]*>/gi, '').replace(/<\/div>/gi, '\n');
        text = text.replace(/<span[^>]*>/gi, '').replace(/<\/span>/gi, '');
        text = text.replace(/<strong[^>]*>/gi, '').replace(/<\/strong>/gi, '');
        text = text.replace(/<em[^>]*>/gi, '').replace(/<\/em>/gi, '');
        text = text.replace(/<b[^>]*>/gi, '').replace(/<\/b>/gi, '');
        text = text.replace(/<i[^>]*>/gi, '').replace(/<\/i>/gi, '');

        // Clean up multiple newlines
        text = text.replace(/\n\s*\n/g, '\n').trim();

        return text;
    }

    // Function to properly set form field values
    function setFormFieldValues() {
        console.log('Setting form field values...');

        // ✅ FIX: Set employment type
        const employmentType = '<?= !empty($row->employment_type) ? $row->employment_type : "" ?>';
        console.log('Employment type from PHP:', employmentType);

        if (employmentType) {
            const $employmentSelect = $('select[name="employment_type"]');
            console.log('Employment select found:', $employmentSelect.length);
            console.log('Setting employment type to:', employmentType);

            $employmentSelect.val(employmentType);

            // Double-check it was set
            setTimeout(function() {
                const currentVal = $employmentSelect.val();
                console.log('Employment type after setting:', currentVal);
                if (currentVal !== employmentType) {
                    console.warn('Employment type not set properly, trying again...');
                    $employmentSelect.val(employmentType).trigger('change');
                }
            }, 100);
        } else {
            console.warn('No employment type found in row data');
        }

        // ✅ FIX: Set industry
        const industryId = '<?= !empty($row->industry_id) ? $row->industry_id : "" ?>';
        console.log('Industry ID from PHP:', industryId);

        if (industryId) {
            const $industrySelect = $('select[name="industry_id"]');
            console.log('Industry select found:', $industrySelect.length);
            console.log('Setting industry ID to:', industryId);

            $industrySelect.val(industryId);

            // Double-check it was set
            setTimeout(function() {
                const currentVal = $industrySelect.val();
                console.log('Industry ID after setting:', currentVal);
                if (currentVal !== industryId) {
                    console.warn('Industry ID not set properly, trying again...');
                    $industrySelect.val(industryId).trigger('change');
                }
            }, 100);
        } else {
            console.warn('No industry ID found in row data');
        }

        // Clean up HTML content in text fields
        const $descriptionField = $('textarea[name="description"]');
        if ($descriptionField.length) {
            const currentValue = $descriptionField.val();
            if (currentValue && (currentValue.includes('<p>') || currentValue.includes('<br') || currentValue
                    .includes('<div'))) {
                const cleanedValue = cleanHTMLContent(currentValue);
                $descriptionField.val(cleanedValue);
                console.log('Cleaned HTML from description field');
            }
        }

        // Also clean other text fields that might contain HTML
        $('input[type="text"], textarea').not('[name="description"]').each(function() {
            const $field = $(this);
            const currentValue = $field.val();
            if (currentValue && (currentValue.includes('<p>') || currentValue.includes('<br') ||
                    currentValue.includes('<div'))) {
                const cleanedValue = cleanHTMLContent(currentValue);
                $field.val(cleanedValue);
            }
        });
    }

    // Initialize on document ready with multiple attempts
    let attempts = 0;
    const maxAttempts = 5;

    function initializeFormFields() {
        attempts++;
        console.log('Initializing form fields - attempt', attempts);

        // Check if selects are available
        const $employmentSelect = $('select[name="employment_type"]');
        const $industrySelect = $('select[name="industry_id"]');

        if ($employmentSelect.length > 0 && $industrySelect.length > 0) {
            console.log('Form selects are available, setting values...');
            setFormFieldValues();
        } else if (attempts < maxAttempts) {
            console.log('Form selects not ready, retrying...');
            setTimeout(initializeFormFields, 200);
        } else {
            console.error('Form selects not available after', maxAttempts, 'attempts');
        }
    }

    // Start initialization
    setTimeout(initializeFormFields, 100);

    // Also handle when quick manage is opened via AJAX
    $(document).on('quickmanage:loaded', function() {
        console.log('Quick manage loaded via AJAX, reinitializing form fields...');
        attempts = 0;
        setTimeout(initializeFormFields, 200);
    });

    // Handle tab changes to ensure fields are set
    $('.qm-tabs-header li').on('click', function() {
        setTimeout(function() {
            console.log('Tab changed, verifying form fields...');
            setFormFieldValues();
        }, 300);
    });
});
</script>