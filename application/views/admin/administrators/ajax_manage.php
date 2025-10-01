<?php
defined('BASEPATH') || exit('No direct script access allowed');

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
            <h2>Add Administrator</h2>
            <p>
                Here you can <span>manage administrators</span> that have access to this system.<br />
                Once added, an Email will be sent to them with instructions to setup a password.
            </p>
            <?php
        } else {
            ?>
            <h2>Edit Administrator <span><?= $row->first_name . ' ' . $row->last_name; ?></span></h2>
            <p>
                Here you can <span>edit administrators</span> that have access to this system.<br />
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
        <?= form_open(); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>
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
                <!-- <div class="col-lg-6">
                    <?/*= field_input('telephone', $row, 'numbers_only valid-phone', [], 'tel', 'Enter the contact number of the user (numbers only)');*/ ?>
                </div> -->
            </div>

            <?php
            if (!empty($row)) {
                ?>
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
            <?php } ?>
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
                    <?= field_input('linkedin_profile_url|label_linkedin_profile_url', getValue($more_details, 'linkedin_profile_url'), '', [], 'text', 'Enter the LinkedIn profile URL'); ?>
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
                    <?= field_multi_select('access_groups|access_groups_heading', $access_groups_all, $access_groups, ''); ?>
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
</script>