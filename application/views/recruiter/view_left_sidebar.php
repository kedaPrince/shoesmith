<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- main left menu -->
<div id="left-sidebar" class="sidebar">
    <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-arrow-left"></i></button>
    <div class="sidebar-scroll">
        <div class="logo">
            <a title="<?= $this->config->item('site_name'); ?>">
                <img src="<?=site_url()?>resources/cms/images/hyrevo-logo2.png"
                    alt="<?= $this->config->item('site_name'); ?>" />
            </a>
            <hr>
        </div>
        <?php 
            $defaultProfilePic = site_url('resources/cms/images/no-user.png'); 
            $profilePic = !empty($this->loginData['profile_pic']) ? image_url($this->loginData['profile_pic']) : $defaultProfilePic;
            
            // Handle agency vs other user types
            $userName = '';
            if (isset($this->loginData['first_name']) && isset($this->loginData['last_name'])) {
                $userName = htmlspecialchars($this->loginData['first_name'] . ' ' . $this->loginData['last_name']);
            } elseif (isset($this->loginData['name'])) {
                $userName = htmlspecialchars($this->loginData['name']);
            } else {
                $userName = 'User';
            }
            ?>
        <div class="user-account">
            <img src="<?= $profilePic ?>" class="rounded-circle user-photo" alt="User Profile Picture">
            <div class="dropdown">
                <span>Welcome,</span>
                <a href="javascript:void(0);" class="dropdown-toggle user-name" data-toggle="dropdown">
                    <strong><?= $userName ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-right account">
                    <li>
                        <?php if (isset($this->loginData['group']) && $this->loginData['group'] == 'agency'): ?>
                        <a href="<?= site_url('agency/agencies#edit/' . loginID()) ?>">
                            <i class="icon-user"></i> My Profile
                        </a>
                        <?php else: ?>
                        <a href="<?= site_url('admin/administrators#edit/' . loginID()) ?>">
                            <i class="icon-user"></i> My Profile
                        </a>
                        <?php endif; ?>
                    </li>
                    <li class="divider"></li>
                    <!-- Logout Button -->
                     <li>
                        <a href="javascript:void(0);" class="icon-menu" id="top-bar-logout-btn" data-toggle="tt"
                            data-placement="top" title="Logout" onclick="confirmLogout()">
                            <i class="fa fa-sign-out"></i> Logout
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
<script>
function confirmRecruiterLogout() {
    Swal.fire({
        title: 'Confirm Logout',
        text: 'Are you sure you want to logout from the system?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Logout',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'swal2-confirm swal2-styled',
            cancelButton: 'swal2-cancel swal2-styled'
        },
        backdrop: `
            rgba(0,0,0,0.4)
            url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%2390caf9' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E")
        `,
        buttonsStyling: false,
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Logging out...',
                text: 'Please wait while we log you out',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Perform logout via POST request with CSRF token
            performRecruiterLogout();
        }
    });
}

function performRecruiterLogout() {
    // Create a dynamic form for POST request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= site_url("login/logout/recruiter") ?>';
    form.style.display = 'none';

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '<?= $this->security->get_csrf_token_name() ?>';
    csrfInput.value = '<?= $this->security->get_csrf_hash() ?>';
    form.appendChild(csrfInput);

    // Submit the form
    document.body.appendChild(form);
    form.submit();
}

// Alternative: Simple GET request logout (if your controller accepts GET)
function simpleRecruiterLogout() {
    Swal.fire({
        title: 'Confirm Logout',
        text: 'Are you sure you want to logout from the system?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Logout',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'swal2-confirm swal2-styled',
            cancelButton: 'swal2-cancel swal2-styled'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to logout URL (GET request)
            window.location.href = '<?= site_url("login/logout/recruiter") ?>';
        }
    });
}
</script>
<style>
.swal2-popup {
    border-radius: 15px !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.swal2-title {
    color: #333 !important;
    font-weight: 600 !important;
}

.swal2-confirm {
    border-radius: 8px !important;
    padding: 10px 24px !important;
    font-weight: 500 !important;
}

.swal2-cancel {
    border-radius: 8px !important;
    padding: 10px 24px !important;
    font-weight: 500 !important;
}

/* Make logout button in navbar consistent */
#top-bar-logout-btn {
    background-color: #dc3545 !important;
    color: white !important;
    border-radius: 25px;
    padding: 8px 45px;
    transition: all 0.3s ease;
    margin: 0 5px;
}

#top-bar-logout-btn:hover {
    background-color: #c82333 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}
</style>