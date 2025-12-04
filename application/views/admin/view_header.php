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

    <link rel="icon" type="image/png" href="<?=site_url()?>resources/cms/images/favicon.ico" sizes="32x32" />

    <!-- VENDOR CSS -->
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/font-awesome/css/font-awesome.min.css" />

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
    <!-- Now load core.js with protection in place -->
    <script type="text/javascript"
        src="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/javascript/core.js"></script>


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

    // Global CSRF Configuration - ADDED FOR SECURITY
    const CSRF_TOKEN_NAME = '<?php echo $this->security->get_csrf_token_name(); ?>';
    const CSRF_TOKEN_VALUE = '<?php echo $this->security->get_csrf_hash(); ?>';

    // Function to setup CSRF token for AJAX requests
    function setupCSRF() {
        // Setup jQuery AJAX defaults
        if (typeof jQuery !== 'undefined') {
            $.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    if (settings.type === 'POST' || settings.type === 'PUT' || settings.type === 'DELETE' ||
                        settings.type === 'PATCH') {
                        // Add CSRF token to form data
                        if (settings.data instanceof FormData) {
                            settings.data.append(CSRF_TOKEN_NAME, CSRF_TOKEN_VALUE);
                        }
                        // Add CSRF token to URL encoded data
                        else if (typeof settings.data === 'string' && settings.data.indexOf(
                            CSRF_TOKEN_NAME) === -1) {
                            settings.data += '&' + encodeURIComponent(CSRF_TOKEN_NAME) + '=' +
                                encodeURIComponent(CSRF_TOKEN_VALUE);
                        }
                        // Add CSRF header
                        xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN_VALUE);
                    }
                }
            });
        }
    }

    // Initialize CSRF when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        setupCSRF();
    });
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

    <!-- IMPERSONATION WARNING BANNER - ADDED FOR SECURITY -->
    <?php if ($this->session->userdata('impersonating')): ?>
    <div class="impersonation-warning"
        style="position: fixed; top: 0; left: 0; right: 0; z-index: 9999; margin: 0; border-radius: 0;">
        <div class="alert alert-warning alert-dismissible fade show mb-0" style="border-radius: 0;">
            <div class="container">
                <i class="fa fa-user-secret me-2"></i>
                <strong>Impersonation Active:</strong> You are currently logged in as another user.
                <a href="<?php echo site_url('admin/administrators/stop_impersonating'); ?>" class="alert-link fw-bold">
                    <i class="fa fa-sign-out me-1"></i> Return to your account
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <script>
    // Adjust page content to account for the warning banner
    document.addEventListener('DOMContentLoaded', function() {
        const warningBanner = document.querySelector('.impersonation-warning');
        if (warningBanner) {
            const bannerHeight = warningBanner.offsetHeight;
            document.body.style.paddingTop = bannerHeight + 'px';

            // Store in sessionStorage to maintain across page reloads
            sessionStorage.setItem('impersonationWarningHeight', bannerHeight);
        }
    });
    </script>
    <?php endif; ?>

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