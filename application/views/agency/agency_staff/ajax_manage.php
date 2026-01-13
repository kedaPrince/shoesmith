<?php
defined('BASEPATH') || exit('No direct script access allowed');
?>
<script type="text/javascript">
// MUST BE AT THE VERY TOP - Define on_script_load immediately
if (typeof on_script_load !== 'function') {
    var on_script_load = function() {
        console.log('on_script_load function called');
        return true;
    };
}

// Make it available globally
window.on_script_load = on_script_load;
</script>
<?php
function getValue(object|null $more_details, string $name): string
{
    if (is_null($more_details)) {
        return '';
    }
    $name = trim($name);
    if (!isset($more_details->{$name})) {
        return '';
    }
    return $more_details->{$name};
}
?>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qm-tabs">
    <div class="quick-manage-heading">
        <?php
        if (empty($row)) {
            ?>
        <h2>Add Internal Staff Member</h2>
        <p>
            Here you can <span>manage Internal staff members</span> that have access to this system.<br />
            Once added, an Email will be sent to them with instructions to setup a password.
        </p>
        <?php
        } else {
            ?>
        <h2>Edit Internal Staff Member <span><?= $row->first_name . ' ' . $row->last_name; ?></span></h2>
        <p>
            Here you can <span>edit internal staff members</span> that have access to this system.<br />
            Once you have made the necessary changes, save your progress.
        </p>
        <?php
            if ($row->id == loginID()) {
                ?>
        <p>
            Please note that changes made to your account will only be visible on your next login.
        </p>
        <?php
            }
        }
        ?>
    </div>
    <ul class="qm-tabs-header">
        <li rel="1" class="active">General</li>
        <li rel="2">Profile</li>
        <li rel="3">Medical Emergency</li>
        <li rel="4">Access Group</li>
    </ul>
    <div class="form-field-container">
        <?php 
    // Get the correct action URL for create/update
    $action_url = !empty($row->id) ? 
        site_url('agency/agency_staff/update/' . $row->id) : 
        site_url('agency/agency_staff/create');
    ?>
        <?= form_open($action_url); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>
        <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
        <div rel="1" class="qm-tabs-tab active">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_image('profile_pic|label_profile_image', $row, ''); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('first_name', $row, 'required', [], 'text', 'Enter the first name'); ?>
                    <?= field_input('last_name', $row, 'required', [], 'text', 'Enter the surname'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('email', $row, 'required valid-email', [], 'email', 'Valid Email Address eg. administrator@yourdomain.co.za'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('usr_type_id|label_user_type', $user_types_all, $row, 'required'); ?>
                </div>
                <div class="col-lg-6">
                    <?php 
                // If user has a specific agency, show it as static text
                if (!empty($user_agency_id) && !empty($agency_options) && $agency_options->num_rows() == 1): 
                    $agency = $agency_options->row();
                ?>
                    <?= form_hidden('agency_id', $user_agency_id); ?>
                    <div class="form-control-static">
                        <strong>Agency:</strong><br>
                        <?= htmlspecialchars($agency->name, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <?php else: ?>
                    <?= field_dropdown('agency_id|label_agency', $agency_options, $row, 'required'); ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($row)): ?>
            <div class="row">
                <div class="info-text">
                    The password needs to be at least <strong>8 characters long</strong>.
                </div>
                <div class="col-lg-6">
                    <?= field_password('password', '', array('autocomplete' => 'off'), 'The password needs to be at least 8 characters long'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_password('confirm_password', '', array('autocomplete' => 'off'), 'Confirm your password'); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div rel="2" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('job_role|label_job_role', getValue($more_details, 'job_role'), 'required', [], 'text', 'Enter the job role'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('id_number|label_id_number', getValue($more_details, 'id_number'), 'required', [], 'text', 'Enter the ID number'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('address_line_1|label_address1', getValue($more_details, 'address_line_1'), 'required', [], 'text', 'Enter the address line 1'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('address_line_2|label_address2', getValue($more_details, 'address_line_2'), '', [], 'text', 'Enter the address line 2'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('city|label_city', getValue($more_details, 'city'), 'required', [], 'text', 'Enter the city'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('country|label_country', getValue($more_details, 'country'), 'required', [], 'text', 'Enter the country'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('contact_number|label_contact_number', getValue($more_details, 'contact_number'), 'required', [], 'text', 'Enter the contact number'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('gender|label_gender', getValue($more_details, 'gender'), 'required', [], 'text', 'Enter the gender'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_date('date_of_birth|label_date_of_birth', getValue($more_details, 'date_of_birth'), 'date-filter filter-value', 'yyyy-mm-dd', array()); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_date('date_of_employment|label_date_of_employment', getValue($more_details, 'date_of_employment'), 'date-filter filter-value', 'yyyy-mm-dd', array()); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_input('linkedin_profile_url|label_linkedin_profile_url', getValue($more_details, 'linkedin_profile_url'), 'required', [], 'text', 'Enter the LinkedIn profile URL'); ?>
                </div>
            </div>
        </div>
        <div rel="3" class="qm-tabs-tab">
            <?php
        $allergies = !is_null($more_details) && isset($more_details->allergies) ? $more_details->allergies : null;
        $doctor_phone_number = !is_null($more_details) && isset($more_details->doctor_phone_number) ? $more_details->doctor_phone_number : null;
        $family_doctor = !is_null($more_details) && isset($more_details->family_doctor) ? $more_details->family_doctor : null;
        $mandatory_medication_taken = !is_null($more_details) && isset($more_details->mandatory_medication_taken) ? $more_details->mandatory_medication_taken : null;
        $medical_aid_member_number = !is_null($more_details) && isset($more_details->medical_aid_member_number) ? $more_details->medical_aid_member_number : null;
        $medical_aid_name = !is_null($more_details) && isset($more_details->medical_aid_name) ? $more_details->medical_aid_name : null;
        $medical_aid_plan = !is_null($more_details) && isset($more_details->medical_aid_plan) ? $more_details->medical_aid_plan : null;
        $medical_history = !is_null($more_details) && isset($more_details->medical_history) ? $more_details->medical_history : null;
        $medical_problems = !is_null($more_details) && isset($more_details->medical_problems) ? $more_details->medical_problems : null;
        $next_of_kin_first_name = !is_null($more_details) && isset($more_details->next_of_kin_first_name) ? $more_details->next_of_kin_first_name : null;
        $next_of_kin_last_name = !is_null($more_details) && isset($more_details->next_of_kin_last_name) ? $more_details->next_of_kin_last_name : null;
        $next_of_kin_phone_number = !is_null($more_details) && isset($more_details->next_of_kin_phone_number) ? $more_details->next_of_kin_phone_number : null;
        $next_of_kin_relation = !is_null($more_details) && isset($more_details->next_of_kin_relation) ? $more_details->next_of_kin_relation : null;
        ?>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('next_of_kin_first_name|label_next_of_kin_first_name', $next_of_kin_first_name, '', ['placeholder' => lang('label_next_of_kin_first_name')], 'text', 'Enter the next of kin first name'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('next_of_kin_last_name|label_next_of_kin_last_name', $next_of_kin_last_name, '', ['placeholder' => lang('label_next_of_kin_last_name')], 'text', 'Enter the next of kin last name'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('next_of_kin_relation|label_next_of_kin_relation', $next_of_kin_relation, '', ['placeholder' => lang('label_next_of_kin_relation')], 'text', 'Enter the next of kin relation'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('next_of_kin_phone_number|label_next_of_kin_phone_number', $next_of_kin_phone_number, '', ['placeholder' => lang('label_next_of_kin_phone_number')], 'text', 'Enter the next of kin phone number'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('family_doctor|label_family_doctor', $family_doctor, '', [], 'text', 'Enter the family doctor name'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('doctor_phone_number|label_doctor_phone_number', $doctor_phone_number, '', [], 'text', 'Enter the family doctor phone number'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('medical_aid_name|label_medical_aid_name', $medical_aid_name, '', [], 'text', 'Enter the medical aid name'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('medical_aid_plan|label_medical_aid_plan', $medical_aid_plan, '', [], 'text', 'Enter the medical aid plan'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('medical_aid_member_number|label_medical_aid_member_number', $medical_aid_member_number, '', [], 'text', 'Enter the medical aid member number'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_textarea('allergies|label_allergies', $allergies, ''); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_textarea('medical_problems|label_medical_problems', $medical_problems, ''); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_textarea('medical_history|label_medical_history', $medical_history, ''); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_textarea('mandatory_medication_taken|label_mandatory_medication_taken', $mandatory_medication_taken, ''); ?>
                </div>
            </div>
        </div>
        <div rel="4" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_multi_select('access_groups|access_groups_heading', $access_groups_all, $access_groups); ?>
                </div>
            </div>
        </div>

        <div class="btn-container" style="clear: left;">
            <?php
        // echo save_button('Save and Close');
        echo qm_tab_buttons();
        echo qm_close_button();
        if (!empty($row) && $row->id != loginID()) {
            echo $row->enabled ? disable_button($identifier, $row->id) : enable_button($identifier, $row->id);
        }
        ?>
        </div>

        <?= form_close(); ?>
    </div>
</div>
<script type="text/javascript">
// MUST BE AT THE VERY TOP - Define on_script_load immediately
if (typeof on_script_load !== 'function') {
    var on_script_load = function() {
        console.log('on_script_load function called');
        return true;
    };
}
// Add this near the top of your JavaScript, after on_script_load
function close_quick_manage() {
    var $closeBtn = $('.close-quick-manage');
    if ($closeBtn.length) {
        $closeBtn.trigger('click');
    } else {
        // Try to find and trigger close
        $('.modal-close, .close-modal, [data-dismiss="modal"]').trigger('click');
    }
}

function refresh_listing() {
    // Try different methods to refresh the listing
    if (typeof $.fn.DataTable !== 'undefined' && $('.dataTable').length) {
        // DataTables
        $('.dataTable').DataTable().ajax.reload(null, false);
    } else if (typeof window.ajax_pager_fetch_batch === 'function') {
        // Custom pagination system
        window.ajax_pager_fetch_batch(1);
    } else if ($('.ajax-pager').length) {
        // AJAX pager system
        $('.ajax-pager').first().trigger('click');
    } else {
        // Fallback to page reload
        window.location.reload();
    }
}

// Make them globally available
window.close_quick_manage = close_quick_manage;
window.refresh_listing = refresh_listing;
// Make it available globally
window.on_script_load = on_script_load;
</script>

<script type="text/javascript">
// ============================================
// PERMANENT FIX FOR DROPZONE + FORM SUBMIT
// ============================================

// This runs when the page loads
$(document).ready(function() {
    console.log('🔧 Applying Dropzone fix on page load...');

    // 1. Modify Dropzone to not auto-process
    var dropzoneEl = $('.dropzone, .dz-uploader').first();
    if (dropzoneEl.length && dropzoneEl[0].dropzone) {
        var dz = dropzoneEl[0].dropzone;

        // Change Dropzone to NOT auto-process
        dz.options.autoProcessQueue = false;
        console.log('✅ Set Dropzone autoProcessQueue: false');

        // Update CSRF token for Dropzone
        var csrfToken = $('input[name="csrf_rfid_token"]').val();
        if (csrfToken) {
            dz.options.headers = dz.options.headers || {};
            dz.options.headers['X-CSRF-TOKEN'] = csrfToken;
        }
    }

    // Hide tabs when creating the user
    <?php if (empty($row)): ?>
    $('.qm-tabs-header').hide();
    <?php endif; ?>
});

// Fix for Dropzone CSRF token initialization
$(document).ready(function() {
    // Wait for Dropzone to initialize
    setTimeout(function() {
        var csrfToken = $('input[name="csrf_rfid_token"]').val();
        if (csrfToken && typeof Dropzone !== 'undefined') {
            $('.quick-manage-container .dropzone, .quick-manage-container .dz-uploader').each(
                function() {
                    if (this.dropzone) {
                        this.dropzone.options.headers = this.dropzone.options.headers || {};
                        this.dropzone.options.headers['X-CSRF-TOKEN'] = csrfToken;
                        this.dropzone.options.autoProcessQueue = false;
                        console.log('CSRF token added to Dropzone');
                    }
                });
        }
    }, 1000);
});

// ============================================
// UPDATED save_form FUNCTION
// ============================================

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

        // Get the form and Dropzone instance
        var form = $(el).closest('form');
        var dropzoneEl = $('.dropzone, .dz-uploader').first();
        var dz = dropzoneEl.length && dropzoneEl[0].dropzone ? dropzoneEl[0].dropzone : null;

        // Check if CSRF token exists
        var csrfInput = form.find('input[name*="csrf"]').first();
        if (!csrfInput.length) {
            alert('Security token missing. Please refresh the page.');
            return;
        }

        console.log('CSRF Token to send:', csrfInput.val().substring(0, 10) + '...');

        // Use the fixed submit function
        fixed_ajax_submit_form(el, view, id, dz);
    });
}

// ============================================
// FIXED ajax_submit_form FUNCTION
// ============================================

function fixed_ajax_submit_form(el, view, id, dz) {
    console.log('🔄 Fixed ajax_submit_form called');

    var form = $(el).closest('form');
    var csrfToken = $('input[name="csrf_rfid_token"]').val();

    // Validate form first
    form.parsley().whenValidate().done(function() {
        console.log('✓ Form validation passed');

        // Prepare FormData
        var formData = new FormData(form[0]);

        // Ensure profile_pic_validator is included
        if (!formData.has('profile_pic_validator')) {
            formData.append('profile_pic_validator', 'xxx');
        }

        // Handle Dropzone files
        if (dz && dz.files.length > 0) {
            console.log('Processing Dropzone files...');

            // Process each file
            var processNextFile = function(index) {
                if (index >= dz.files.length) {
                    // All files processed, submit form
                    submitFormData(formData, el, csrfToken);
                    return;
                }

                var file = dz.files[index];
                console.log('Adding file to FormData:', file.name);

                // Read the file and add to FormData
                var reader = new FileReader();
                reader.onload = function(e) {
                    // Convert to blob
                    var blob = new Blob([e.target.result], {
                        type: file.type
                    });

                    // Add to FormData
                    formData.append('profile_pic', blob, file.name);

                    // Process next file
                    processNextFile(index + 1);
                };
                reader.readAsArrayBuffer(file);
            };

            // Start processing files
            processNextFile(0);
        } else {
            // No files, submit directly
            submitFormData(formData, el, csrfToken);
        }

    }).fail(function() {
        console.log('✗ Form validation failed');
        alert('Please check all required fields');
    });
}

function submitFormData(formData, el, csrfToken) {
    console.log('📤 Submitting form...');

    var form = $(el).closest('form');

    // Show loading
    var $button = $(el);
    var originalText = $button.text();
    $button.prop('disabled', true).text('Saving...');

    // Also disable the close button to prevent accidental closure
    var $closeBtn = $('.close-quick-manage');
    if ($closeBtn.length) {
        $closeBtn.prop('disabled', true).css('opacity', '0.5');
    }

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('✅ Full Response:', response);

            if (response.success) {
                // Update CSRF token
                if (response.csrf) {
                    $('input[name="csrf_rfid_token"]').val(response.csrf);

                    // Also update Dropzone headers if it exists
                    var dropzoneEl = $('.dropzone, .dz-uploader').first();
                    if (dropzoneEl.length && dropzoneEl[0].dropzone) {
                        var dz = dropzoneEl[0].dropzone;
                        dz.options.headers = dz.options.headers || {};
                        dz.options.headers['X-CSRF-TOKEN'] = response.csrf;
                    }
                }

                // Show success message - check different possible message fields
                var message = response.message || response.flasherbody || 'Saved successfully!';
                alert(message);

                // ALWAYS reload after successful save
                console.log('Reloading page after successful save...');

                // First close any open modals
                setTimeout(function() {
                    // Try to close the quick manage modal
                    var $closeBtn = $('.close-quick-manage');
                    if ($closeBtn.length) {
                        $closeBtn.trigger('click');
                    }

                    // Small delay then reload
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                }, 1000);

            } else {
                // Show error message
                var errorMsg = response.error || response.flasherbody || 'Save failed';
                alert(errorMsg);
                $button.prop('disabled', false).text(originalText);
                $closeBtn.prop('disabled', false).css('opacity', '1');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error:', status, error);

            if (xhr.status === 403) {
                alert('Security token expired. Please refresh page.');
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            } else {
                alert('Error: ' + (xhr.responseText || error));
                $button.prop('disabled', false).text(originalText);
                $closeBtn.prop('disabled', false).css('opacity', '1');
            }
        }
    });
}

// Make sure the original ajax_submit_form function points to our fixed version
if (typeof window.ajax_submit_form !== 'function') {
    window.ajax_submit_form = fixed_ajax_submit_form;
}

// Also define on_script_load if not defined
if (typeof window.on_script_load !== 'function') {
    window.on_script_load = function() {
        console.log('Quick manage scripts loaded');
        return true;
    };
}
</script>