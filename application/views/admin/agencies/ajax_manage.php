<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qm-tabs">
    <div class="quick-manage-heading">
        <?php if (empty($row)): ?>
        <h2>Add Agency</h2>
        <p>
            Here you can <span>add a new agency</span> to the system.<br />
            Assign an agency type and access groups to control permissions.
        </p>
        <?php else: ?>
        <h2>Edit Agency <span><?= htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8'); ?></span></h2>
        <p>
            Update the agency details below.<br />
            Changes will take effect immediately.
        </p>
        <?php endif; ?>
    </div>

    <ul class="qm-tabs-header">
        <li rel="1" class="active">General</li>
        <li rel="2">Contact & Address</li>
        <li rel="3">Access Control</li>
    </ul>

    <div class="form-field-container">
        <?= form_open(); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>

        <!-- Tab 1: General -->
        <div rel="1" class="qm-tabs-tab active">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('name', $row, 'required', [], 'text', 'Enter the agency name'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_dropdown('agency_type_id|label_agency_types', $usr_type_options, $row, 'required'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('email', $row, 'valid-email', [], 'email', 'Enter agency email (optional)'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('phone', $row, '', [], 'tel', 'Enter agency phone number (optional)'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 2: Contact & Address -->
        <div rel="2" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-12">
                    <?= field_textarea('address', $row, '', ['placeholder' => 'Enter full agency address'], 'Enter the agency’s physical address'); ?>
                </div>
            </div>
        </div>

        <!-- Tab 3: Access Control -->
        <div rel="3" class="qm-tabs-tab">
            <div class="row">
                <div class="col-lg-6">
                    <?= field_multi_select('access_groups|Access Groups', $access_groups_all, $access_groups, 'Assign access groups to this agency'); ?>
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