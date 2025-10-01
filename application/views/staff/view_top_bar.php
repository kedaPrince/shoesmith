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
                        <?php 
                            if($this->session->userdata('previous_url')){
                                $previous_url = $this->session->userdata('previous_url');
                                
                                echo '
                                    <li>
                                        <a href="'.htmlspecialchars($previous_url).'" class="icon-menu" id="top-bar-back-btn" title="Back">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                                            </svg>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="'.site_url().'" target="_blank" class="icon-menu" id="top-bar-return-btn" data-toggle="tooltip" data-placement="top" title="Visit Site" data-original-title="Visit Site" title="Visit Site">
                                            Visit Site
                                        </a>
                                    </li>
                                ';
                            }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </nav>