<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- main left menu -->
<div id="left-sidebar" class="sidebar">
    <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-arrow-left"></i></button>
    <div class="sidebar-scroll">
        <div class="logo">
            <a title="<?= $this->config->item('site_name'); ?>">
                <img src="<?=site_url()?>resources/cms/images/logo_black_web.png"
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
                        <a href="<?= site_url('admin/administrators#edit/' . loginID()) ?>">
                            <i class="icon-user"></i> My Profile
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="<?= site_url('login/logout/' . $this->uri->segment(1)) ?>">
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

                    $groupClass = ($this->group == $menuGroup->group ? (!empty($menuGroup->items) ? 'active' : 'single-active') : '');
                    $groupClass .= (!empty($menuGroup->class) ? ' '.$menuGroup->class : '');
                    echo '
                                <li class="'.$groupClass.'">
                                    <a href="'.$menuGroup->url.'" class="'.(!empty($menuGroup->items) ? 'has-arrow' : '').'">
                                        <i class="fa '.$menuGroup->icon.'"></i>
                                        <span>'.$menuGroup->label.'</span>
                                    </a>
                    ';

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

                            $menuItemClass = $active . ' ' . (! empty($menuItem->class) ? $menuItem->class : '');
                            echo '
                                        <li class="'.$menuItemClass.'">
                                            <a href="'.$menuItem->url.'">'.$menuItem->label.'</a>
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