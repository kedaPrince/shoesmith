<?php
defined('BASEPATH') || exit('No direct script access allowed');
?>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qmfc">
    <div class="quick-manage-heading">
        <?php
        if (!empty($row->id)) {
        ?>
            <h2>Edit <?= $this->singular ?> <span><?= $row->name ?></span></h2>
        <?php
        } else {
        ?>
            <h2>Add <?= $this->singular ?></h2>
        <?php
        }
        ?>
        <p>In this module, you can manage the various playbook types.</p>
    </div>
    <div class="form-field-container">
        <?= form_open(); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>
        <div class="row">
            <div class="col-lg-12">
                <?= field_input('name', $row, 'required'); ?>
            </div>
        </div>
        <?php
        if ($this->seoFields) {
        ?>
            <h2>SEO Fields</h2>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('seo_title', $row, ''); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('seo_keywords', $row, ''); ?>
                </div>
                <div class="col-lg-12">
                    <?= field_textarea('seo_description', $row, ''); ?>
                </div>
            </div>
        <?php
        }
        ?>
        <div class="btn-container" style="clear: left;">
            <?php
            echo save_button('Save and Close');
            echo cancel_button('Close', 'left');
            ?>
        </div>
        <?= form_close(); ?>
    </div>
</div>
<script type="text/javascript">
    function save_form(el) {
        $(el).closest('form').parsley().whenValidate().done(function() {
            var view = '<?= !empty($row->id) ? 'update' : 'create' ?>';
            var id = <?= !empty($row->id) ? $row->id : '0' ?>;

            ajax_submit_form(el, view, id);
        });
    }

    if (typeof setup_image_fields === 'function') {
        setup_image_fields();
    }
</script>