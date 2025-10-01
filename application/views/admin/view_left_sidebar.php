<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- main left menu -->
<div id="left-sidebar" class="sidebar">
    <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-arrow-left"></i></button>
    <div class="sidebar-scroll">
        <div class="logo">
            <a title="<?= $this->config->item('site_name'); ?>" href="<?= site_url() ?>" target="_blank">
                <img src="<?=site_url()?>resources/cms/images/admin-ajax.png"
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
                        <a href="<?= site_url() . 'login/logout/' . $this->uri->segment(1) ?>">
                            <i class="icon-power"></i> Logout
                        </a>
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

                    //Determine whether or not to show menu group
                    if (isset($menuGroup->show) && ! $menuGroup->show) {
                        continue;
                    }

                    $groupClass     = ($this->group == $menuGroup->group ? (!empty($menuGroup->items) ? 'active' : 'single-active') : '');
                    $groupClass     .= (!empty($menuGroup->class) ? ' '.$menuGroup->class : '');
                    $resetSubmodule = !empty($submodules[$menuGroup->page]) ? '/reset' : '';
                    echo '
                                <li class="'.$groupClass.'">
                                    <a href="'.$menuGroup->url.$resetSubmodule.'" class="'.(!empty($menuGroup->items) ? 'has-arrow' : '').'">
                                        <i class="fa '.$menuGroup->icon.'"></i>
                                        <span>'.$menuGroup->label.'</span>
                                    </a>
                    ';

                    if (!empty($menuGroup->subLink)) {
                        $sublinkIcon = !empty($menuGroup->subLink->icon) ? $menuGroup->subLink->icon : 'fa-plus-circle';
                        echo '
                                    <a href="'.$menuGroup->subLink->url.'" class="sub-link" title="'.attr_clean($menuGroup->subLink->label).'" data-toggle="menu"  data-placement="bottom">
                                        <i class="fa '.$sublinkIcon.'"></i>
                                    </a>
                        ';
                    }

                    //Check for menu items
                    if (!empty($menuGroup->items)) {

                        echo '      <ul>';

                        //Menu items
                        foreach ($menuGroup->items as $menuItem) {

                            //Determine whether or not to show menu item
                            if (isset($menuItem->show) && ! $menuItem->show) {
                                continue;
                            }

                            //Determine how menu it is marked as being active
                            if ( ! empty($menuGroup->active_by) && $menuGroup->active_by == 'view') {
                                $active = $this->pageName == $menuItem->page && $this->view == $menuItem->view ? 'active' : '';
                            }
                            elseif (!empty($menuGroup->active_by) && $menuGroup->active_by == 'url') {
                                $active = current_url() == $menuItem->url ? 'active' : '';
                            }
                            else {
                                $active = $this->pageName == $menuItem->page ? 'active' : '';
                            }

                            $menuItemClass  = $active.' '.(!empty($menuItem->class) ? $menuItem->class : '');
                            $resetSubmodule = !empty($submodules[$menuItem->page]) ? '/reset' : '';

                            $sublink = '';
                            if (!empty($menuItem->subLink)) {
                                $sublinkIcon = !empty($menuItem->subLink->icon) ? $menuItem->subLink->icon : 'fa-plus-circle';
                                $sublink = '
                                            <a href="'.$menuItem->subLink->url.'" class="sub-link-inline" title="'.attr_clean($menuItem->subLink->label).'" data-toggle="menu"  data-placement="bottom">
                                                <i class="fa '.$sublinkIcon.'"></i>
                                            </a>
                                ';
                            }
                            
                            echo '
                                        <li class="'.$menuItemClass.'">
                                            <a href="'.$menuItem->url.$resetSubmodule.'">'.$menuItem->label.'</a>
                                            '.$sublink.'
                                        </li>
                            ';

                        }

                        echo '      </ul>';
                    }

                    echo '
                                </li>
                    ';
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