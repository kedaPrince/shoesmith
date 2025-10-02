<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
/* Dark Mode Variables */
:root {
    --bg-primary: #ffffff;
    --bg-secondary: #f8f9fa;
    --text-primary: #333333;
    --text-secondary: #6c757d;
    --border-color: #dee2e6;
    --navbar-bg: #ffffff;
    --navbar-text: #333333;
    --navbar-border: #e3e3e3;
    --icon-color: #333333;
    --dropdown-bg: #ffffff;
    --dropdown-text: #333333;
    --search-bg: #f8f9fa;
    --search-text: #333333;
    --search-border: #dee2e6;
}

[data-theme="dark"] {
    --bg-primary: #1a1a1a;
    --bg-secondary: #2d2d2d;
    --text-primary: #ffffff;
    --text-secondary: #b0b0b0;
    --border-color: #404040;
    --navbar-bg: #2d2d2d;
    --navbar-text: #ffffff;
    --navbar-border: #404040;
    --icon-color: #ffffff;
    --dropdown-bg: #2d2d2d;
    --dropdown-text: #ffffff;
    --search-bg: #3d3d3d;
    --search-text: #ffffff;
    --search-border: #555555;
}

/* Navbar Styling */
.navbar {
    background-color: var(--navbar-bg) !important;
    border-bottom: 1px solid var(--navbar-border);
    color: var(--navbar-text);
}

.navbar-brand a {
    color: var(--navbar-text) !important;
    text-decoration: none;
}

.navbar-brand a:hover {
    color: var(--navbar-text) !important;
}

/* Navbar Links and Icons */
.navbar-nav>li>a {
    color: var(--navbar-text) !important;
}

.navbar-nav>li>a:hover,
.navbar-nav>li>a:focus {
    color: var(--navbar-text) !important;
    background-color: var(--bg-secondary);
}

/* Icons */
.navbar-nav>li>a i {
    color: var(--icon-color);
}

.navbar-nav>li>a svg {
    fill: var(--icon-color);
}

/* Search Form */
.search-form .form-control {
    background-color: var(--search-bg);
    border-color: var(--search-border);
    color: var(--search-text);
}

.search-form .form-control::placeholder {
    color: var(--text-secondary);
}

.search-form .btn-default {
    background-color: var(--search-bg);
    border-color: var(--search-border);
    color: var(--icon-color);
}

.search-form .btn-default:hover {
    background-color: var(--bg-secondary);
    border-color: var(--border-color);
    color: var(--icon-color);
}

/* Dropdown Menu */
.dropdown-menu {
    background-color: var(--dropdown-bg);
    border: 1px solid var(--border-color);
    color: var(--dropdown-text);
}

.dropdown-menu>li>a {
    color: var(--dropdown-text) !important;
}

.dropdown-menu>li>a:hover {
    background-color: var(--bg-secondary);
    color: var(--dropdown-text) !important;
}

.dropdown-menu .header {
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
}

/* Notification Dot */
.notification-dot {
    background-color: #ff4444;
}

/* Dark/Light Mode Toggle Icons */
.dark-mode-disabled {
    display: inline-block;
}

.dark-mode-enabled {
    display: none;
}

[data-theme="dark"] .dark-mode-disabled {
    display: none;
}

[data-theme="dark"] .dark-mode-enabled {
    display: inline-block;
}

/* Button Toggle Styles */
.btn-toggle-offcanvas,
.btn-toggle-fullwidth {
    background: transparent;
    border: none;
    color: var(--icon-color) !important;
}

.btn-toggle-offcanvas:hover,
.btn-toggle-fullwidth:hover {
    background-color: var(--bg-secondary);
    color: var(--icon-color) !important;
}

/* Text colors in notifications */
.dropdown-menu .text-success {
    color: #28a745 !important;
}

.dropdown-menu .text-warning {
    color: #ffc107 !important;
}

[data-theme="dark"] .dropdown-menu .text-success {
    color: #34ce57 !important;
}

[data-theme="dark"] .dropdown-menu .text-warning {
    color: #ffd54f !important;
}
</style>
<!-- Top navbar div start -->
<nav class="navbar navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-brand">
            <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-bars"></i></button>
            <button type="button" class="btn-toggle-fullwidth"><i class="fa fa-bars"></i></button>
            <a href="<?= site_url('admin/dashboard'); ?>"><?= $this->config->item('site_name'); ?></a>
        </div>

        <div class="navbar-right">
            <form id="navbar-search" class="navbar-form search-form">
                <input value="" class="form-control" placeholder="Search here..." type="text">
                <button type="button" class="btn btn-default"><i class="icon-magnifier"></i></button>
            </form>

            <div id="navbar-menu">
                <ul class="nav navbar-nav">
                    <!-- Dark/Light Mode Toggle -->
                    <li>
                        <a class="dark-mode-toggle icon-menu" href="javascript:toggle_dark_mode()"
                            title="Toggle Dark Mode" data-toggle="tt" data-placement="top">
                            <i class="dark-mode-disabled fa fa-moon-o"></i>
                            <i class="dark-mode-enabled fa fa-sun-o"></i>
                        </a>
                    </li>

                    <!-- Back Button -->
                    <?php if($this->session->userdata('previous_url')): 
                        $previous_url = $this->session->userdata('previous_url');
                    ?>
                    <li>
                        <a href="<?= htmlspecialchars($previous_url); ?>" class="icon-menu" id="top-bar-back-btn"
                            title="Back">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-arrow-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                            </svg>
                        </a>
                    </li>
                    <li>
                        <a href="<?= site_url(); ?>" target="_blank" class="icon-menu" id="top-bar-return-btn"
                            data-toggle="tooltip" data-placement="top" title="Visit Site">
                            <i class="fa fa-external-link"></i> Visit Site
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Notifications -->
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle icon-menu" data-toggle="dropdown">
                            <i class="fa fa-bell"></i>
                            <span class="notification-dot"></span>
                        </a>
                        <ul class="dropdown-menu notifications">
                            <li class="header"><strong>You have</strong> 4 new notifications</li>
                            <li>
                                <a href="javascript:void(0);">
                                    <i class="fa fa-user text-success"></i>
                                    <span>New user registered</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);">
                                    <i class="fa fa-warning text-warning"></i>
                                    <span>Server load is high</span>
                                </a>
                            </li>
                        </ul>
                    </li>


                </ul>
            </div>
        </div>
    </div>
</nav>
<script>
// Initialize dark mode on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeDarkMode();
});

function initializeDarkMode() {
    // Get saved theme or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';

    // Apply theme to body
    document.body.setAttribute('data-theme', savedTheme);

    // Update icon visibility
    updateDarkModeIcons(savedTheme === 'dark');

    console.log('Dark mode initialized:', savedTheme);
}

function updateDarkModeIcons(isDark) {
    if (isDark) {
        $('.dark-mode-disabled').css('display', 'none');
        $('.dark-mode-enabled').css('display', 'inline-block');
    } else {
        $('.dark-mode-disabled').css('display', 'inline-block');
        $('.dark-mode-enabled').css('display', 'none');
    }
}

// Your existing toggle function - make sure it updates everything
function toggle_dark_mode() {
    let currentTheme = $('body').attr('data-theme') || 'light';
    let newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    // Apply new theme
    $('body').attr('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);

    // Update icons
    updateDarkModeIcons(newTheme === 'dark');

    console.log('Theme changed to:', newTheme);
}
</script>