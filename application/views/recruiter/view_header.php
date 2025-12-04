<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">

<head>
    <title><?= $this->config->item('site_name'); ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="author" content="7Diverse" />
    <!-- CSRF META TAGS - ADDED FOR SECURITY -->
    <meta name="csrf-token-name" content="<?php echo $this->security->get_csrf_token_name(); ?>">
    <meta name="csrf-token" content="<?php echo $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
        value="<?php echo $this->security->get_csrf_hash(); ?>" id="csrf_token_input">

    <link rel="icon" type="image/png" href="<?=site_url()?>resources/cms/images/favicon.ico" sizes="32x32" />

    <!-- VENDOR CSS -->
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/font-awesome/css/font-awesome.min.css" />

    <!-- Replace your current Font Awesome with this -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- <link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/multi-select/css/multi-select.css"> -->
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap-multiselect/bootstrap-multiselect.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/parsleyjs/css/parsley.css" />
    <link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/toastr/toastr.min.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/sweetalert2/sweetalert2.min.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap-datepicker/bootstrap-datepicker3.css" />

    <!-- MAIN Project CSS file -->
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/css/theme/main.min.css?v=<?= $this->config->item('version'); ?>" />
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/css/ecms.min.css?v=<?= $this->config->item('version'); ?>" />
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/css/custom.min.css?v=<?= $this->config->item('version'); ?>" />

    <!-- Extra Plugin CSS -->
    <link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/css/cropper.min.css" />
    <link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/flatpickr/flatpickr.min.css" />
    <!-- Now load core.js with protection in place -->
    <script type="text/javascript"
        src="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/javascript/core.js"></script>

    <?php
//Add extra page specific css files
if (!empty($css)) {
    foreach ($css as $c) {
        echo '
            <link rel="stylesheet" type="text/css" href="'.$c.'">
        ';
    }
}
?>

    <!-- Top Level JS -->
    <script type="text/javascript"
        src="<?= site_url(); ?>resources/cms/plugins/ckeditor5/custom/ecms-upload-adapter.js"></script>
    <script type="text/javascript" src="<?= site_url(); ?>resources/cms/plugins/ckeditor5/ckeditor.js"></script>
    <!-- FIX THIS LINE: Change from assets/js/core.js to resources/cms/javascript/core.js -->
    <script type="text/javascript" src="<?= site_url(); ?>resources/cms/javascript/core.js"></script>



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

    <!-- In view_header.php - Update the script to use window. prefix -->
    <script>
    // Set global CSRF token for AJAX requests - Use window. prefix
    window.csrf_token_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
    window.csrf_token_value = '<?php echo $this->security->get_csrf_hash(); ?>'; // Changed from window.csrf_token

    // Function to get CSRF token for AJAX requests
    window.getCsrfToken = function() {
        return {
            name: window.csrf_token_name,
            value: window.csrf_token_value
        };
    };

    // Function to update CSRF token when it changes
    window.updateCsrfToken = function(newToken) {
        window.csrf_token_value = newToken;
        document.getElementById('csrf_token_input').value = newToken;
        document.querySelector('meta[name="csrf-token"]').content = newToken;
    };
    </script>
</head>

<body data-theme="<?= $this->config->item('dark_mode') ? 'dark' : 'light'; ?>"
    data-color="<?= $this->config->item('theme'); ?>" class="font-nunito right_icon_toggle">
    <script>
    //Set dark/light mode
    var ecmsTheme = localStorage.getItem('theme');
    if (ecmsTheme == 'dark') {
        document.body.setAttribute("data-theme", "dark");
    } else {
        document.body.setAttribute("data-theme", "light");
    }
    </script>
    <div id="wrapper">

        <!-- Page Loader -->
        <div class="page-loader-wrapper">
            <div class="loader">
                <div class="m-t-30"><img src="<?= site_url(); ?>resources/cms/images/loader.gif" width="64" height="64"
                        alt="Iconic"></div>
                <p>Please wait...</p>
            </div>
        </div>

        <?php 
    $this->load->view($this->folder.'/view_top_bar'); 
    $this->load->view($this->folder.'/view_left_sidebar');
    $this->load->view($this->folder.'/view_right_sidebar');
?>