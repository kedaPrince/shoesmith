<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qm-tabs">
    <div class="quick-manage-heading">
        <?php if (empty($row)): ?>
        <h2>Add Agency</h2>
        <p>
            Here you can <span>add a new agency (company)</span> to the system.<br />
            Enter the company's official details below.
        </p>
        <?php else: ?>
        <h2>Edit Agency <span><?= htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8'); ?></span></h2>
        <p>
            Update the agency's company details below.<br />
            Changes will take effect immediately.
        </p>
        <?php endif; ?>
    </div>

    <ul class="qm-tabs-header">
        <li rel="1" class="active">General</li>
        <li rel="2">Contact & Address</li>
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
                    <?= field_input('email', $row, 'valid-email', [], 'email', 'General contact email'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('phone', $row, '', [], 'tel', 'Main office phone number'); ?>
                </div>
            </div>
            <div class="row">
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