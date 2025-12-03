<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">

<!-- ========== NUCLEAR SECURITY - RUNS BEFORE ANYTHING ========== -->
<script type="text/javascript">
// === NUCLEAR SECURITY LOCKDOWN ===
// This runs IMMEDIATELY when HTML starts parsing
console.log('🚨 NUCLEAR SECURITY: Initializing...');

// 1. FREEZE localStorage immediately
const originalLocalStorage = window.localStorage;
const frozenLocalStorage = {
    setItem: function(key, value) {
        console.error('🚫 NUCLEAR BLOCK: Attempt to setItem', key);
        throw new Error('SECURITY LOCKDOWN: localStorage.setItem disabled');
    },
    getItem: function(key) {
        console.warn('🔍 NUCLEAR: Reading', key);
        return originalLocalStorage.getItem(key);
    },
    removeItem: function(key) {
        console.warn('🗑️ NUCLEAR: Removing', key);
        return originalLocalStorage.removeItem(key);
    },
    clear: function() {
        console.warn('🧹 NUCLEAR: Clearing');
        return originalLocalStorage.clear();
    },
    key: function(index) {
        return originalLocalStorage.key(index);
    },
    get length() {
        return originalLocalStorage.length;
    }
};

// 2. Replace localStorage with frozen version
Object.defineProperty(window, 'localStorage', {
    get: function() {
        console.log('🔒 NUCLEAR: Accessing localStorage');
        return frozenLocalStorage;
    },
    configurable: false,
    enumerable: true
});

// 3. Also freeze sessionStorage for safety
const originalSessionStorage = window.sessionStorage;
Object.defineProperty(window, 'sessionStorage', {
    get: function() {
        console.log('🔒 NUCLEAR: Accessing sessionStorage');
        return {
            setItem: function(key, value) {
                console.error('🚫 NUCLEAR BLOCK: sessionStorage.setItem', key);
                throw new Error('SECURITY LOCKDOWN: sessionStorage disabled');
            },
            getItem: function(key) {
                return originalSessionStorage.getItem(key);
            },
            removeItem: function(key) {
                return originalSessionStorage.removeItem(key);
            },
            clear: function() {
                return originalSessionStorage.clear();
            },
            key: function(index) {
                return originalSessionStorage.key(index);
            },
            get length() {
                return originalSessionStorage.length;
            }
        };
    },
    configurable: false
});

console.log('✅ NUCLEAR SECURITY: Complete lockdown applied');
</script>

<head>
    <title><?= htmlspecialchars($this->config->item('site_name'), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Google Fonts CSS -->
    <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <meta name="author" content="7Diverse" />

    <link rel="icon" type="image/png"
        href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8') ?>resources/cms/images/favicon.ico" sizes="32x32" />

    <!-- ========== CONTROLLED UNLOCK SCRIPT ========== -->
    <script type="text/javascript">
    // === CONTROLLED UNLOCK: Allow ONLY specific safe operations ===
    (function() {
        'use strict';

        console.log('🔓 CONTROLLED UNLOCK: Initializing...');

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initControlledAccess);
        } else {
            initControlledAccess();
        }

        function initControlledAccess() {
            // SAFE KEYS - ONLY these are allowed
            const SAFE_KEYS = ['theme', 'ui_preferences', 'layout_settings', 'sidebar_state'];

            // Create controlled localStorage
            const controlledLocalStorage = {
                setItem: function(key, value) {
                    console.log(`🔓 Attempting to store: "${key}"`);

                    // Only allow safe keys
                    if (!SAFE_KEYS.includes(key)) {
                        console.error(`🚫 BLOCKED: "${key}" not in safe list`);
                        console.error(`Allowed: ${SAFE_KEYS.join(', ')}`);
                        throw new Error(`Security: Only these keys allowed: ${SAFE_KEYS.join(', ')}`);
                    }

                    // Size check
                    if (value && value.length > 10000) {
                        throw new Error('Security: Value too large');
                    }

                    console.log(`✅ Allowed: "${key}" = "${value}"`);
                    return originalLocalStorage.setItem(key, value);
                },

                getItem: function(key) {
                    console.log(`🔓 Reading: "${key}"`);
                    return originalLocalStorage.getItem(key);
                },

                removeItem: function(key) {
                    console.log(`🔓 Removing: "${key}"`);
                    return originalLocalStorage.removeItem(key);
                },

                clear: function() {
                    console.log('🔓 Clearing all (keeping theme if exists)');
                    const theme = originalLocalStorage.getItem('theme');
                    originalLocalStorage.clear();
                    if (theme) {
                        originalLocalStorage.setItem('theme', theme);
                    }
                },

                key: function(index) {
                    return originalLocalStorage.key(index);
                },

                get length() {
                    return originalLocalStorage.length;
                }
            };

            // Replace the nuclear lockdown with controlled access
            Object.defineProperty(window, 'localStorage', {
                get: function() {
                    return controlledLocalStorage;
                },
                configurable: false
            });

            console.log('✅ CONTROLLED UNLOCK: Complete');
            console.log(`✅ Allowed keys: ${SAFE_KEYS.join(', ')}`);

            // Initialize theme if not set
            if (!originalLocalStorage.getItem('theme')) {
                originalLocalStorage.setItem('theme', 'light');
            }

            // Test function
            window.testSecurity = function() {
                console.log('🔐 Running security tests...');

                // Test 1: Should FAIL
                try {
                    window.localStorage.setItem('auth_token', 'test123');
                    console.error('❌ FAIL: auth_token was allowed');
                    return false;
                } catch (e) {
                    console.log('✅ PASS: Blocked auth_token -', e.message);
                }

                // Test 2: Should WORK
                try {
                    window.localStorage.setItem('theme', 'dark');
                    console.log('✅ PASS: Allowed theme');
                    return true;
                } catch (e) {
                    console.error('❌ FAIL: Theme blocked -', e.message);
                    return false;
                }
            };
        }
    })();
    </script>

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

    <!-- Load jQuery AFTER security is set up -->
    <script type="text/javascript"
        src="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8'); ?>resources/cms/plugins/theme/jquery/jquery.min.js">
    </script>

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

    <!-- Theme setting - Uses the controlled localStorage -->
    <script type="text/javascript">
    (function() {
        'use strict';

        // Wait for controlled access to be ready
        setTimeout(function() {
            try {
                // Get theme from localStorage
                var ecmsTheme = window.localStorage.getItem('theme');
                console.log('🎨 Current theme:', ecmsTheme);

                // Validate theme value
                if (ecmsTheme === 'dark') {
                    document.body.setAttribute("data-theme", "dark");
                } else {
                    document.body.setAttribute("data-theme", "light");
                    // Ensure theme is set
                    window.localStorage.setItem('theme', 'light');
                }

                // Listen for theme changes
                document.addEventListener('themeChange', function(e) {
                    if (e.detail && (e.detail.theme === 'dark' || e.detail.theme === 'light')) {
                        console.log('🎨 Changing theme to:', e.detail.theme);
                        window.localStorage.setItem('theme', e.detail.theme);
                        document.body.setAttribute("data-theme", e.detail.theme);
                    }
                });

            } catch (error) {
                console.warn('Theme error:', error.message);
                document.body.setAttribute("data-theme", "light");
            }
        }, 100);
    })();
    </script>

    <!-- ========== FINAL VERIFICATION SCRIPT ========== -->
    <script type="text/javascript">
    window.addEventListener('load', function() {
        console.log('🔐 FINAL SECURITY VERIFICATION');

        setTimeout(function() {
            // Run security test
            if (window.testSecurity) {
                const result = window.testSecurity();
                console.log(result ? '✅ SECURITY: ALL TESTS PASSED' : '❌ SECURITY: TESTS FAILED');
            } else {
                console.error('🚨 CRITICAL: Security system not loaded!');
            }

            // Show current localStorage contents
            console.log('📋 Current localStorage contents:');
            for (let i = 0; i < window.localStorage.length; i++) {
                const key = window.localStorage.key(i);
                const value = window.localStorage.getItem(key);
                console.log(`  ${key}: "${value}"`);
            }
        }, 500);
    });
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