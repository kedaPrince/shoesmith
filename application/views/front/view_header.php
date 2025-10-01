<?php defined('BASEPATH') OR exit('No direct script access allowed');

$pageTitle = !empty($pageMeta['seo_title']) ? $pageMeta['seo_title'] : "Site Default Title";
$pageDescription = !empty($pageMeta['seo_description']) ? $pageMeta['seo_description'] : "Site Default Description";
$pageKeywords = (isset($pageMeta['seo_keywords']) && !empty($pageMeta['seo_keywords'])) ? trim($pageMeta['seo_keywords']) : '';

?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title><?= $pageTitle ?></title>
        <meta name="description" content="<?= $pageDescription ?>" />
        <meta name="keywords" content="<?= $pageKeywords ?>" />

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
        <link type="text/css" rel="stylesheet" href="<?= site_url(); ?>resources/front/css/style.min.css?version=<?= $this->config->item('version'); ?>"/>
        <link rel="icon" type="image/png" href="<?=site_url()?>resources/cms/images/favicon.ico" sizes="32x32">
        <meta id="Viewport" name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=yes, width=465"/>
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
                    var ww = (window.innerWidth < window.screen.width) ? window.innerWidth : window.screen.width; //get proper width
                    var mw = 465; // min width of site
                    var ratio = ww / mw; //calculate ratio
                    if (ww < mw) { //smaller than minimum size
                        document.getElementById('Viewport').setAttribute('content', 'initial-scale=' + ratio + ', maximum-scale=' + ratio + ', minimum-scale=' + ratio + ', user-scalable=yes, width=' + ww);
                    } else { //regular size
                        document.getElementById('Viewport').setAttribute('content', 'initial-scale=1.0, maximum-scale=1, minimum-scale=1.0, user-scalable=yes, width=' + ww);
                    }
                }
            };
        </script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script type="text/javascript" src="<?= site_url(); ?>resources/front/js/jquery-3.3.1.min.js"></script>
        <script type="text/javascript" src="<?= site_url(); ?>resources/front/js/mini/functions.js?version=<?= $this->config->item('version'); ?>"></script>
        <script>
            var csrf            = '<?= $this->security->get_csrf_hash(); ?>';
            var csrfName        = '<?= $this->security->get_csrf_token_name(); ?>';
            var dynamicPath     = '<?= url($this->pageName); ?>';
            var site_url        = '<?=site_url()?>';
            var uploadedImages  = {};
            var cropper;
            var loader;
        </script>
        <link rel="preload" href="<?= site_url(); ?>resources/front/fonts/Heaters.woff2" as="font" type="font/woff2" crossorigin>
    </head>
    <body>
        <header>
        </header>
