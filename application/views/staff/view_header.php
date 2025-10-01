<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php 
    // If the page reloads, remove the saved filters. (Requested from client)
    $this->session->unset_userdata($this->pageName . 'Filters'); 
?>
<!doctype html>
<html lang="en">

<head>
<title><?= $this->config->item('site_name'); ?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="author" content="7Diverse">

<link rel="icon" type="image/png" href="<?=site_url()?>resources/cms/images/favicon.ico" sizes="32x32">

<!-- VENDOR CSS -->
<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/font-awesome/css/font-awesome.min.css">

<!-- <link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/multi-select/css/multi-select.css"> -->
<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap-multiselect/bootstrap-multiselect.css">
<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/parsleyjs/css/parsley.css">
<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/toastr/toastr.min.css">
<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/sweetalert2/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap-datepicker/bootstrap-datepicker3.css">

<!-- MAIN Project CSS file -->
<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/css/theme/main.min.css?v=<?= $this->config->item('version'); ?>">
<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/css/ecms.min.css?v=<?= $this->config->item('version'); ?>">
<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/css/custom.min.css?v=<?= $this->config->item('version'); ?>">

<!-- Extra Plugin CSS -->
<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/css/cropper.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Top Level JS -->
<script type="text/javascript" src="<?= site_url(); ?>resources/cms/plugins/ckeditor5/ckeditor.js"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    //Function to check if script has been loaded before executing
    function on_script_load(scriptName, func) {
        
        if (window[scriptName]) {
            //Script is loaded
            func();
        } else {
            //Script is not loaded, retry in .3 seconds.
            setTimeout(function() {
                on_script_load(scriptName, func);
            }, 300);
        }
    }
</script>


</head>
<body data-theme="<?= $this->config->item('dark_mode') ? 'dark' : 'light'; ?>" class="font-nunito right_icon_toggle">
<div id="wrapper" class="<?= $this->config->item('theme'); ?>">

    <!-- Page Loader -->
    <div class="page-loader-wrapper">
        <div class="loader">
            <div class="m-t-30"><img src="<?= site_url(); ?>resources/cms/images/loader.gif" width="64" height="64" alt="Iconic"></div>
            <p>Please wait...</p>
        </div>
    </div>

<?php 
    $this->load->view($this->folder.'/view_top_bar'); 
    $this->load->view($this->folder.'/view_left_sidebar');
    $this->load->view($this->folder.'/view_right_sidebar');
?>
