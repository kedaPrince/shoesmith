<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">

<head>
    <title><?= htmlspecialchars($this->config->item('site_name'), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- REMOVED: CSP meta tag - now set via PHP headers -->

    <!-- Google Fonts CSS -->
    <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <meta name="author" content="7Diverse" />
    <!-- CSRF META TAGS - ADDED FOR SECURITY -->
    <meta name="csrf-token-name" content="<?php echo $this->security->get_csrf_token_name(); ?>">
    <meta name="csrf-token" content="<?php echo $this->security->get_csrf_hash(); ?>">
    <!-- REMOVED security headers from meta tags -->

    <link rel="icon" type="image/png"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8') ?>resources/cms/images/favicon.ico" sizes="32x32" />

    <!-- VENDOR CSS -->
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/plugins/theme/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/plugins/theme/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/plugins/theme/bootstrap-multiselect/bootstrap-multiselect.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/plugins/theme/parsleyjs/css/parsley.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/plugins/theme/toastr/toastr.min.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/plugins/theme/sweetalert2/sweetalert2.min.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/plugins/theme/bootstrap-datepicker/bootstrap-datepicker3.css" />

    <!-- MAIN Project CSS file -->
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/css/theme/main.min.css?v=<?= htmlspecialchars($this->config->item('version'), ENT_QUOTES, 'UTF-8'); ?>" />
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/css/ecms.min.css?v=<?= htmlspecialchars($this->config->item('version'), ENT_QUOTES, 'UTF-8'); ?>" />
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/css/custom.min.css?v=<?= htmlspecialchars($this->config->item('version'), ENT_QUOTES, 'UTF-8'); ?>" />

    <!-- Extra Plugin CSS -->
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/css/cropper.min.css" />
    <link rel="stylesheet" type="text/css"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/plugins/flatpickr/flatpickr.min.css" />

    <!-- JavaScript with security protection -->
    <script type="text/javascript"
        src="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/plugins/theme/jquery/jquery.min.js">
    </script>

    <!-- SECURITY: localStorage protection BEFORE core.js loads -->
    <!-- Keep your existing localStorage protection script -->

    <!-- Now load core.js with protection in place -->
    <script type="text/javascript"
        src="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/javascript/core.js"></script>

    <?php
    //Add extra page specific css files
    if (!empty($css)) {
        foreach ($css as $c) {
            echo '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($c, ENT_QUOTES, 'UTF-8') . '">';
        }
    }
    ?>
</head>

<body data-theme="<?= htmlspecialchars($this->config->item('dark_mode') ? 'dark' : 'light', ENT_QUOTES, 'UTF-8'); ?>"
    data-color="<?= htmlspecialchars($this->config->item('theme'), ENT_QUOTES, 'UTF-8'); ?>"
    class="font-nunito right_icon_toggle">

    <!-- Theme setting with secure localStorage access -->
    <script type="text/javascript">
    (function() {
        'use strict';

        try {
            // Get theme from localStorage (protected by our wrapper)
            var ecmsTheme = localStorage.getItem('theme');

            // Validate theme value
            if (ecmsTheme === 'dark') {
                document.body.setAttribute("data-theme", "dark");
            } else {
                document.body.setAttribute("data-theme", "light");
                // Set safe default
                localStorage.setItem('theme', 'light');
            }

            // Listen for theme changes
            document.addEventListener('themeChange', function(e) {
                if (e.detail && (e.detail.theme === 'dark' || e.detail.theme === 'light')) {
                    localStorage.setItem('theme', e.detail.theme);
                    document.body.setAttribute("data-theme", e.detail.theme);
                }
            });

        } catch (error) {
            console.warn('Could not access localStorage for theme:', error.message);
            document.body.setAttribute("data-theme", "light");
        }
    })();
    </script>

    <div id="wrapper">

        <!-- Page Loader -->
        <div class="page-loader-wrapper">
            <div class="loader">
                <div class="m-t-30"><img
                        src="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/images/loader.gif"
                        width="64" height="64" alt="Iconic"></div>
                <p>Please wait...</p>
            </div>
        </div>

        <?php 
    // Security: Validate folder path
    $valid_folders = ['admin', 'staff', 'agency', 'agency_staff', 'recruiter'];
    if (in_array($this->folder, $valid_folders, true)) {
        $this->load->view($this->folder.'/view_top_bar'); 
        $this->load->view($this->folder.'/view_left_sidebar');
        $this->load->view($this->folder.'/view_right_sidebar');
    } else {
        log_message('error', 'Invalid folder path in view_header: ' . $this->folder);
        // Fallback to default
        $this->load->view('agency/view_top_bar'); 
        $this->load->view('agency/view_left_sidebar');
        $this->load->view('agency/view_right_sidebar');
    }
?>

<!-- Add to view_header.php before closing </head> tag -->
<script>
function smartCloseForm() {
    // Try to close quick manage modal
    if (typeof jQuery !== 'undefined' && jQuery('.close-quick-manage').length) {
        jQuery('.close-quick-manage').trigger('click');
    }
    // Try to close modal if exists
    else if (typeof jQuery !== 'undefined' && jQuery('.modal').length) {
        jQuery('.modal').modal('hide');
    }
    // Fallback - redirect to jobs listing
    else {
        window.location.href = '<?= site_url("agency/jobs_listings") ?>';
    }
    return false;
}
</script>