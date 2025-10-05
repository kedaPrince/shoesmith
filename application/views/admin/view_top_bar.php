<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- Top navbar div start -->
<nav class="navbar navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-brand">
            <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-bars"></i></button>
            <button type="button" class="btn-toggle-fullwidth"><i class="fa fa-bars"></i></button>
            <a><?= $this->config->item('site_name'); ?></a>
        </div>

        <div class="navbar-right">
            <div id="navbar-menu">
                <ul class="nav navbar-nav">
                    <li>
                        <a class="dark-mode-toggle icon-menu" href="javascript:toggle_dark_mode()"
                            title="Toggle Dark Mode" data-toggle="tt" data-placement="top">
                            <i class="dark-mode-disabled fa fa-moon-o"></i>
                            <i class="dark-mode-enabled fa fa-sun-o"></i>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url(); ?>" target="_blank" class="icon-menu" id="top-bar-return-btn"
                            data-toggle="tt" data-placement="top" title="Visit Site" data-original-title="Visit Site"
                            title="Visit Site">
                            Visit Site
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>