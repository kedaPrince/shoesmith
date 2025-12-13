<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
</div>

<?php $this->load->view($this->folder.'/view_templates'); ?>

<!-- Theme Bundle -->
<script src="<?= site_url(); ?>resources/cms/plugins/theme/bundles/libscripts.bundle.js"></script>
<script src="<?= site_url(); ?>resources/cms/plugins/theme/bundles/vendorscripts.bundle.js"></script>

<!-- Theme Plugins -->
<script src="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap-multiselect/bootstrap-multiselect.js"></script>
<script src="<?= site_url(); ?>resources/cms/plugins/theme/parsleyjs/js/parsley.min.js"></script>
<script src="<?= site_url(); ?>resources/cms/plugins/theme/toastr/toastr.js"></script>
<script src="<?= site_url(); ?>resources/cms/plugins/theme/sweetalert2/sweetalert2.min.js"></script>
<script src="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
<script src="<?= site_url(); ?>resources/cms/plugins/theme/jquery-inputmask/jquery.inputmask.bundle.js"></script>

<!-- Extra plugins -->
<script type="text/javascript" src="<?= site_url(); ?>resources/cms/plugins/dropzone/dropzone.min.js"></script>
<script type="text/javascript" src="<?= site_url(); ?>resources/cms/javascript/cropper.min.js"></script>
<script type="text/javascript" src="<?= site_url(); ?>resources/cms/javascript/jquery-cropper.min.js"></script>
<script type="text/javascript" src="<?= site_url(); ?>resources/cms/javascript/ImageTools.js"></script>
<script type="text/javascript" src="<?= site_url(); ?>resources/cms/plugins/flatpickr/flatpickr.min.js"></script>

<!-- Core JS -->
<script src="<?= site_url(); ?>resources/cms/javascript/theme/common.js?v=<?= $this->config->item('version'); ?>">
</script>

<script type="text/javascript">
var crudLimit = <?= $this->config->item('CRUD_row_limit'); ?>;
var previousBatchCount = <?= $this->config->item('CRUD_row_limit'); ?>;
var csrf = '<?= $this->security->get_csrf_hash(); ?>';
var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
var dynamicPath = '<?= url($this->pageName); ?>';
var maxBatches = '<?= $this->config->item('CRUD_batch_limit'); ?>';
var noResults = '<?= lang('general_no_results'); ?>';
var siteURL = '<?= site_url(); ?>';
var section = '<?= ! is_numeric(uri_segment(2, 0)) ? uri_segment(2) : '' ?>';
var df = new Array();
var dffu = new Array();
var exportable = <?= ! empty($this->export) ? 1 : 0; ?>;
var uploadedImages = {};
var uploaderData = {};
var ecmsFieldOptions = {};
</script>
<script type="text/javascript"
    src="<?= site_url(); ?>resources/cms/javascript/core.min.js?v=<?= $this->config->item('version'); ?>"></script>
<script type="text/javascript"
    src="<?= site_url(); ?>resources/cms/javascript/custom.min.js?v=<?= $this->config->item('version'); ?>"></script>

<?php
//Add extra page specific javascript files
if (!empty($js)) {
    foreach ($js as $j) {
        echo '
            <script type="text/javascript" src="'.$j.'"></script>
        ';
    }
}
?>

<?php show_flash_notifications(); ?>
</body>

</html>