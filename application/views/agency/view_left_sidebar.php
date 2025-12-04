<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- main left menu -->
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- main left menu -->
<div id="left-sidebar" class="sidebar">
    <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-arrow-left"></i></button>
    <div class="sidebar-scroll">
        <div class="logo">
            <a title="<?= $this->config->item('site_name'); ?>" href="<?= site_url() ?>" target="_blank">
                <!-- Always use white logo for dark mode -->
                <img src="<?= site_url() ?>resources/cms/images/logo_white_logo.png"
                    alt="<?= $this->config->item('site_name'); ?>" />
            </a>
            <hr>
        </div>
        <?php 
            $defaultProfilePic = site_url('resources/cms/images/no-user.png'); 
            $profilePic = !empty($this->loginData['profile_pic']) ? image_url($this->loginData['profile_pic']) : $defaultProfilePic;
            ?>
        <div class="user-account">
            <img src="<?= $profilePic ?>" class="rounded-circle user-photo" alt="User Profile Picture">
            <div class="dropdown">
                <span>Welcome,</span>
                <a href="javascript:void(0);" class="dropdown-toggle user-name" data-toggle="dropdown">
                    <strong><?= htmlspecialchars($this->loginData['first_name'] . ' ' . $this->loginData['last_name']) ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-right account">
                    <li>
                        <a href="<?= url('administrators/edit/' . loginID()) ?>">
                            <i class="icon-user"></i> My Profile
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <!-- ============ CHANGED: Logout link to POST form ============ -->
                        <form method="POST" action="<?= site_url('login/logout/' . $this->uri->segment(1)) ?>"
                            style="display: inline;" id="logout-form-sidebar">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>"
                                value="<?= $this->security->get_csrf_hash(); ?>">
                            <a href="javascript:void(0);"
                                onclick="document.getElementById('logout-form-sidebar').submit();"
                                style="display: block; padding: 3px 20px; clear: both; font-weight: normal; line-height: 1.42857143; color: #333; white-space: nowrap;">
                                <i class="icon-power"></i> Logout
                            </a>
                        </form>
                        <!-- ============ END CHANGES ============ -->
                    </li>
                </ul>
            </div>
            <hr>
        </div>

        <!-- Tab panes -->

        <?php
            if ( ! empty($this->siteMap)) {
                $submodules = $this->session->submodules;

                echo '
                <div class="tab-content padding-0">
                    <div class="tab-pane active" id="mainmenu">
                        <nav id="left-sidebar-nav" class="sidebar-nav">
                            <ul class="metismenu li_animation_delay">
                ';

                //Menu Icons
           foreach ($this->siteMap as $menuGroup) {
    // Check if menuGroup has required properties
    if (!is_object($menuGroup)) continue;
    
    // Determine whether or not to show menu group
    if (isset($menuGroup->show) && ! $menuGroup->show) {
        continue;
    }

    $groupClass = ($this->group == ($menuGroup->group ?? '') ? (!empty($menuGroup->items) ? 'active' : 'single-active') : '');
    $groupClass .= (!empty($menuGroup->class) ? ' '.$menuGroup->class : '');
    
    // Fix: Check if page property exists
    $resetSubmodule = (!empty($menuGroup->page) && !empty($submodules[$menuGroup->page])) ? '/reset' : '';
    
    echo '
    <li class="'.$groupClass.'">
        <a href="'.($menuGroup->url ?? '#').$resetSubmodule.'" class="'.(!empty($menuGroup->items) ? 'has-arrow' : '').'">
            <i class="fa '.($menuGroup->icon ?? 'fa-circle').'"></i>
            <span>'.($menuGroup->label ?? 'Menu').'</span>
        </a>';

}

                echo '
                            </ul>
                        </nav>
                    </div>
                </div>
                ';
            }
            ?>

    </div>
</div>