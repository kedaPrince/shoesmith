<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qm-tabs">
    <div class="quick-manage-heading">
        <?php if (empty($row)): ?>
        <h2>Add Agency</h2>
        <p>
            Here you can <span>add a new agency (company)</span> to the system.<br />
            Enter the company's official details and login credentials below.
        </p>
        <?php else: ?>
        <h2>Edit Agency <span><?= htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8'); ?></span></h2>
        <p>
            Update the agency's company details and login settings below.<br />
            Changes will take effect immediately.
        </p>
        <?php endif; ?>
    </div>

    <ul class="qm-tabs-header">
        <li rel="1" class="active">General</li>
        <li rel="2">Contact & Address</li>
        <li rel="3">Login Credentials</li>
    </ul>

    <div class="form-field-container">
        <?= form_open(); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>

        <!-- Tab 1: General -->
        <div rel="1" class="qm-tabs-tab active">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('name', $row, 'required', [], 'text', 'Enter the legal company name'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('slug', $row, '', [], 'text', 'URL-friendly slug'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('registration_number', $row, '', [], 'text', 'Company registration number'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('vat_number', $row, '', [], 'text', 'VAT / Tax ID number'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('industry', $row, '', [], 'text', 'Industry or sector'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('website', $row, 'valid-url', [], 'url', 'Official company website'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 2: Contact & Address -->
        <div rel="2" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('email', $row, 'required valid-email', [], 'email', 'Login email address'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('phone', $row, '', [], 'tel', 'Main office phone number'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('contact_person', $row, '', [], 'text', 'Primary contact person'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('billing_contact', $row, '', [], 'text', 'Billing department contact'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('address', $row, '', ['placeholder' => 'Enter full physical address'], 'Company physical address'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 3: Login Credentials -->
        <div rel="3" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-12">
                    <div class="info-text">
                        <strong>Login Credentials:</strong><br>
                        The agency will use these credentials to access their dashboard and manage their staff members.
                    </div>
                </div>
            </div>

            <?php if (empty($row)): ?>
            <!-- Password fields for new agency -->
            <div class="row">
                <div class="col-lg-6">
                    <?= field_password('password', '', ['required' => 'required', 'autocomplete' => 'new-password'], 'Set login password (min 8 characters)'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_password('confirm_password', '', ['required' => 'required', 'autocomplete' => 'new-password'], 'Confirm password'); ?>
                </div>
            </div>
            <?php else: ?>
            <!-- Password fields for existing agency (optional) -->
            <div class="row">
                <div class="col-lg-6">
                    <?= field_password('password', '', ['autocomplete' => 'new-password'], 'Leave blank to keep current password'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_password('confirm_password', '', ['autocomplete' => 'new-password'], 'Confirm new password'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_checkbox('login_enabled|label_login_enabled', $row, '1'); ?>
                </div>
            </div>
            <?php endif; ?>
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