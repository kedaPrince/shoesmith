<?php defined('BASEPATH') OR exit('No direct script access allowed');

$pageTitle = !empty($pageMeta['seo_title']) ? $pageMeta['seo_title'] : "Site Default Title";
$pageDescription = !empty($pageMeta['seo_description']) ? $pageMeta['seo_description'] : "Site Default Description";
$pageKeywords = (isset($pageMeta['seo_keywords']) && !empty($pageMeta['seo_keywords'])) ? trim($pageMeta['seo_keywords']) : '';

?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?= $pageTitle ?></title>
    <meta name="description" content="<?= $pageDescription ?>" />
    <meta name="keywords" content="<?= $pageKeywords ?>" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
    <link type="text/css" rel="stylesheet"
        href="<?= site_url(); ?>resources/front/css/style.min.css?version=<?= $this->config->item('version'); ?>" />
    <link rel="icon" type="image/png" href="<?=site_url()?>resources/cms/images/favicon.ico" sizes="32x32">
    <meta id="Viewport" name="viewport"
        content="initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=yes, width=465" />
    <?php
if(!empty($resources)) {
    foreach($resources as $resource) {
        echo $resource;
    }
}
?>
    <script>
    new function viewport() {
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|bada|iemobile|BB[0-9.,:_-]{2,};/i.test(navigator.userAgent)) {
            var ww = (window.innerWidth < window.screen.width) ? window.innerWidth : window.screen
                .width; //get proper width
            var mw = 465; // min width of site
            var ratio = ww / mw; //calculate ratio
            if (ww < mw) { //smaller than minimum size
                document.getElementById('Viewport').setAttribute('content', 'initial-scale=' + ratio +
                    ', maximum-scale=' + ratio + ', minimum-scale=' + ratio + ', user-scalable=yes, width=' + ww);
            } else { //regular size
                document.getElementById('Viewport').setAttribute('content',
                    'initial-scale=1.0, maximum-scale=1, minimum-scale=1.0, user-scalable=yes, width=' + ww);
            }
        }
    };
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="text/javascript" src="<?= site_url(); ?>resources/front/js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript"
        src="<?= site_url(); ?>resources/front/js/mini/functions.js?version=<?= $this->config->item('version'); ?>">
    </script>
    <script>
    var csrf = '<?= $this->security->get_csrf_hash(); ?>';
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var dynamicPath = '<?= url($this->pageName); ?>';
    var site_url = '<?=site_url()?>';
    var uploadedImages = {};
    var cropper;
    var loader;
    </script>
    <link rel="preload" href="<?= site_url(); ?>resources/front/fonts/Heaters.woff2" as="font" type="font/woff2"
        crossorigin>

    <!---new code-->
    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;700&family=Work+Sans:wght@400;600&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="resources/front/animate/animate.min.css" rel="stylesheet">
    <link href="resources/front/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="resources/front/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="resources/front/css/home/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="resources/front/css/home/style.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">


</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark px-lg-5">
            <a href="<?= site_url() ?>" class="navbar-brand ms-4 ms-lg-0">
                <img class="w-100" src="resources/front/images/hyrevo-logo2.png" alt="Image">
            </a>
            <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse"
                data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav mx-auto p-4 p-lg-0">
                    <a href="<?= site_url() ?>" class="nav-item nav-link active">Home</a>
                    <!-- <a href="about.html" class="nav-item nav-link">About</a>
                    <a href="service.html" class="nav-item nav-link">Services</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Pages</a>
                        <div class="dropdown-menu m-0">
                            <a href="team.html" class="dropdown-item">Our Models</a>
                            <a href="testimonial.html" class="dropdown-item">Testimonial</a>
                            <a href="404.html" class="dropdown-item">404 Page</a>
                        </div>
                    </div> -->
                    <a href="<?= site_url() ?>contact" class="nav-item nav-link">Contact</a>
                </div>
                <div class="d-none d-lg-flex">
                    <a class="btn btn-outline-primary border-2" href="<?= site_url('login'); ?>">Login
                        Now</a>
                </div>
            </div>
        </nav>

    </header>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="resources/front/wow/wow.min.js"></script>
    <script src="resources/front/easing/easing.min.js"></script>
    <script src="resources/front/waypoints/waypoints.min.js"></script>
    <script src="resources/front/owlcarousel/owl.carousel.min.js"></script>
    <script src="resources/front/lightbox/js/lightbox.min.js"></script>
    <!-- Template Javascript -->
    <script src="resources/front/js/main.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>