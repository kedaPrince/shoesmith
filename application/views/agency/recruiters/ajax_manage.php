<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qm-tabs">
    <div class="quick-manage-heading">
        <?php if (empty($row)): ?>
        <h2>Add Contractors</h2>
        <p>
            Here you can <span>add a new contractor</span> to the system.<br />
            Assign them to an agency and set their role and permissions.
        </p>
        <?php else: ?>
        <h2>Edit Contractor
            <span><?= htmlspecialchars($row->first_name . ' ' . $row->last_name, ENT_QUOTES, 'UTF-8'); ?></span>
        </h2>
        <p>
            Update the contractor's details below.<br />
            Changes will take effect immediately.
        </p>
        <?php endif; ?>
    </div>

    <ul class="qm-tabs-header">
        <li rel="1" class="active">General</li>
        <li rel="2">Contact</li>
        <li rel="3">Permissions</li>
    </ul>

    <div class="form-field-container">
        <?= form_open(); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>

        <!-- Tab 1: General -->
        <div rel="1" class="qm-tabs-tab active">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('first_name', $row, 'required', [], 'text', 'Enter first name'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('last_name', $row, 'required', [], 'text', 'Enter last name'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_email('email', $row, 'required', [], 'Enter email address'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('phone', $row, '', [], 'tel', 'Enter phone number'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_dropdown('agency_id|label_agency', $agency_options, $row, 'required'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('usr_type_id|label_user_type', $usr_type_options, $row, 'required'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_password('password', 'required', ['placeholder' => 'Enter password (min 8 chars)'], 'Password'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_password('confirm_password', 'required', ['placeholder' => 'Confirm password'], 'Confirm Password'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 2: Contact -->
        <div rel="2" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('address', $row, '', ['placeholder' => 'Enter physical address'], 'Address'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 3: Permissions -->
        <div rel="3" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_multi_select('access_groups|label_access_groups', $access_groups_all, $access_groups, 'Assign access groups'); ?>
                </div>
            </div>
        </div>

        <div class="btn-container" style="clear: left;">
            <?= qm_tab_buttons(); ?>
            <?= qm_close_button(); ?>
            <?php if (!empty($row)): ?>
            <?= $row->enabled ? disable_button($identifier, $row->id) : enable_button($identifier, $row->id); ?>
            <?php endif; ?>
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
});
</script>