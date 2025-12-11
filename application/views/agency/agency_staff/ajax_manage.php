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
        <h2>Add Agency Staff Member</h2>
        <p>
            Here you can <span>manage agencystaff members</span> that have access to this system.<br />
            Once added, an Email will be sent to them with instructions to setup a password.
        </p>
        <?php
        } else {
            ?>
        <h2>Edit Agency Staff Member <span><?= $row->first_name . ' ' . $row->last_name; ?></span></h2>
        <p>
            Here you can <span>edit agency staff members</span> that have access to this system.<br />
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

    <?php
        // Hide tabs when creating the user
        if (empty($row)) {
        ?>
    $('.qm-tabs-header').hide();
    <?php
        }
        ?>
});

// Fix for Dropzone CSRF token
$(document).ready(function() {
    // Wait a bit for Dropzone to initialize
    setTimeout(function() {
        // Get CSRF token from form
        var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrfToken = $('input[name="' + csrfName + '"]').val();

        if (!csrfToken) {
            // Try to get from meta tag
            csrfToken = $('meta[name="csrf-token"]').attr('content');
            csrfName = $('meta[name="csrf-token-name"]').attr('content') || csrfName;
        }

        if (csrfToken && typeof Dropzone !== 'undefined') {
            // Update all Dropzone instances in the quick manage form
            $('.quick-manage-container .dropzone, .quick-manage-container .dz-uploader').each(
                function() {
                    if (this.dropzone) {
                        // Update the Dropzone instance
                        this.dropzone.options.headers = this.dropzone.options.headers || {};
                        this.dropzone.options.headers['X-CSRF-TOKEN'] = csrfToken;

                        // Also add to form data
                        this.dropzone.on("sending", function(file, xhr, formData) {
                            formData.append(csrfName, csrfToken);
                        });

                        console.log('CSRF token added to Dropzone');
                    }
                });
        }
    }, 500);
});
</script>
<script type="text/javascript">
function ajax_submit_form(el, view, id) {
    // Get the form
    var form = $(el).closest('form');

    // Get CSRF token
    var csrfInput = form.find('input[name*="csrf"]').first();
    var csrfToken = csrfInput.val();
    var csrfName = csrfInput.attr('name');

    console.log('AJAX Request Debug:');
    console.log('URL:', form.attr('action'));
    console.log('Method: POST');
    console.log('CSRF Token being sent:', csrfToken ? csrfToken.substring(0, 10) + '...' : 'NOT FOUND');

    // Show loading state
    $(el).prop('disabled', true).addClass('loading');

    // Submit via AJAX
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('Success response:', response);

            // Check if CSRF token needs to be updated
            if (response.csrf) {
                console.log('New CSRF token received:', response.csrf.substring(0, 10) + '...');

                // Update CSRF token in the form
                csrfInput.val(response.csrf);

                // Also update any other CSRF inputs in the form
                form.find('input[name*="csrf"]').each(function() {
                    $(this).val(response.csrf);
                });

                // Update CSRF token in the page (meta tags or other forms)
                $('meta[name="csrf-token"]').attr('content', response.csrf);
                $('input[name="' + csrfName + '"]').not(form.find('input[name*="csrf"]')).val(response
                    .csrf);

                console.log('CSRF token updated in form');
            }

            // Handle success
            if (response.success) {
                console.log('✓ Form saved successfully!');

                // Show success message
                alert(view === 'update' ? 'Updated successfully!' : 'Created successfully!');

                // Close quick manage modal
                $('.close-quick-manage').click();

                // Reload the page after a short delay
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                // Show error
                alert(response.message || 'An error occurred');
                $(el).prop('disabled', false).removeClass('loading');

                // If CSRF error, try auto-resubmit with new token
                if (response.message === 'Invalid security token' && response.csrf) {
                    console.log('Auto-retrying with new CSRF token...');
                    // Update form and resubmit
                    csrfInput.val(response.csrf);
                    setTimeout(function() {
                        ajax_submit_form(el, view, id);
                    }, 500);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error Details:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);

            // Try to parse as JSON anyway
            try {
                var response = JSON.parse(xhr.responseText);
                alert(response.message || 'Server error: ' + error);

                // Check for CSRF token in error response
                if (response.csrf) {
                    console.log('New CSRF token in error response:', response.csrf.substring(0, 10) +
                        '...');
                    csrfInput.val(response.csrf);
                }
            } catch (e) {
                // If not JSON, show raw response
                alert('Server returned: ' + xhr.responseText.substring(0, 100));
                console.log('Raw response:', xhr.responseText.substring(0, 500));
            }

            $(el).prop('disabled', false).removeClass('loading');
        }
    });
}

// Make sure this function is available globally
if (typeof window.ajax_submit_form !== 'function') {
    window.ajax_submit_form = ajax_submit_form;
}

// Also define on_script_load if not defined
if (typeof window.on_script_load !== 'function') {
    window.on_script_load = function() {
        console.log('Quick manage scripts loaded');
        return true;
    };
}
</script>