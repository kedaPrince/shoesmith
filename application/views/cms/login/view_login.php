<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">

<head>
    <title>Login - <?= $this->config->item('site_name'); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="7Diverse">
    <!-- Add these meta tags in the <head> section -->
    <meta name="csrf-token-name" content="<?php echo $this->security->get_csrf_token_name(); ?>">
    <meta name="csrf-token" content="<?php echo $this->security->get_csrf_hash(); ?>">

    <link rel="icon" type="image/png" href="<?=site_url()?>resources/cms/images/favicon.ico" sizes="32x32">

    <!-- VENDOR CSS -->
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/parsleyjs/css/parsley.css">
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/plugins/theme/sweetalert2/sweetalert2.min.css">

    <!-- MAIN CSS -->
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/css/theme/main.min.css?v=<?= $this->config->item('version'); ?>">
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/css/ecms.min.css?v=<?= $this->config->item('version'); ?>">
    <link rel="stylesheet" type="text/css"
        href="<?= site_url(); ?>resources/cms/css/custom.min.css?v=<?= $this->config->item('version'); ?>">

    <script type="text/javascript" src="<?= site_url(); ?>resources/cms/plugins/theme/jquery/jquery-3.5.1.min.js">
    </script>
    <script type="text/javascript" src="<?= site_url(); ?>resources/cms/plugins/theme/parsleyjs/js/parsley.min.js">
    </script>
    <script>
    var csrf = '<?= $this->security->get_csrf_hash(); ?>';
    var csrfName = '<?=$this->security->get_csrf_token_name()?>';
    var exportable = '0';
    var dynamicPath = '';
    </script>
    <script type="text/javascript"
        src="<?= site_url(); ?>resources/cms/javascript/core.min.js?v=<?= $this->config->item('version'); ?>"></script>
    <!-- In the head section, add this script before your other scripts -->
    <script>
    // ✅ Define base_url for JavaScript
    var base_url = '<?php echo site_url(); ?>';
    var csrf = '<?= $this->security->get_csrf_hash(); ?>';
    var csrfName = '<?=$this->security->get_csrf_token_name()?>';
    var exportable = '0';
    var dynamicPath = '';
    </script>
</head>

<body data-theme="<?= $this->config->item('dark_mode') ? 'dark' : 'light'; ?>"
    data-color="<?= $this->config->item('theme'); ?>" class="font-nunito login-layout">
    <!-- WRAPPER -->
    <div id="wrapper">
        <div class="vertical-align-wrap">
            <div class="vertical-align-middle auth-main">
                <div class="auth-box">
                    <div class="top">
                        <img class="logo" src="<?=site_url()?>resources/cms/images/logo_black_web.png"
                            alt="<?= $this->config->item('site_name'); ?>" />
                    </div>
                    <div class="card">
                        <div class="header">
                            <p class="lead">Login to your account</p>
                        </div>
                        <div class="body form-auth-small">
                            <?php echo form_open('', ['id' => 'login-form']); ?>
                            <?php echo form_hidden('action', 'login'); ?>
                            <?php echo form_hidden('group', ''); ?>
                            <?php echo form_hidden('bgimage', set_value('bgimage', 0)); ?>

                            <!-- ADD THIS CSRF FIELD -->
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                value="<?php echo $this->security->get_csrf_hash(); ?>">

                            <div class="form-group">
                                <label for="signin-email" class="control-label sr-only">Email</label>
                                <input data-parsley-required-message="Email is required" data-parsley-type="email"
                                    data-parsley-type-message="Email should be a valid email address" name="email"
                                    type="email" class="form-control" id="signin-email" value="" placeholder="Email"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="signin-password" class="control-label sr-only">Password</label>
                                <input data-parsley-required-message="Password is required" name="password"
                                    type="password" class="form-control" id="signin-password" value=""
                                    placeholder="Password" required>
                            </div>
                            <div class="login-error" style="color: red; margin-bottom: 15px;"></div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block"
                                id="login-button">LOGIN</button>
                            <div class="bottom">
                                <span class="helper-text m-b-10"><i class="fa fa-lock"></i> <a
                                        href="<?php echo url('login/forgot-password'); ?>">Forgot password?</a></span>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END WRAPPER -->
    <script>
    function refreshCsrfToken() {
        $.ajax({
            url: '<?= site_url(); ?>login/ajax_refresh_token',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                // The CSRF token should be updated via the response
                console.log("CSRF token refreshed");
            }
        });
    }
    $(document).ready(function() {
        console.log("Document ready - JavaScript loaded");
        $('input').attr('autocomplete', 'off');

        // No need to get initial token from meta - it's in the PHP variable
        console.log("CSRF token name: <?=$this->security->get_csrf_token_name()?>");

        $('#login-form').on('submit', function(e) {
            console.log("Form submitted");
            e.preventDefault();

            $('.login-error').html('');

            // Disable button during login attempt
            $('#login-button').prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> LOGGING IN...');

            $('#login-form').parsley().whenValidate().done(function() {
                console.log("Form validation passed");
                // ✅ Call without parameters - function gets them from form
                attempt_login();
            }).fail(function() {
                console.log("Form validation failed");
                // Re-enable button
                $('#login-button').prop('disabled', false).html('LOGIN');
            });
        });

        $('#login-form input').on('keyup', function(e) {
            if (e.keyCode == 13) {
                console.log("Enter key pressed");
                $('#login-form').trigger('submit');
            }
        });
    });



    // In your login JavaScript file
    function attempt_login(email, password, group, action) {
        console.log('=== LOGIN ATTEMPT ===');

        // Get values from form
        email = $('#signin-email').val();
        password = $('#signin-password').val();
        group = $('input[name="group"]').val() || '';
        action = $('input[name="action"]').val();

        // Get CSRF token from meta tag or form
        var csrfName = $('meta[name="csrf-token-name"]').attr('content') || 'csrf_rfid_token';
        var csrfToken = $('input[name="' + csrfName + '"]').val() || $('meta[name="csrf-token"]').attr('content');

        console.log('CSRF Name:', csrfName);
        console.log('CSRF Token:', csrfToken ? csrfToken.substring(0, 10) + '...' : 'MISSING');

        var data = {
            email: email,
            password: password,
            group: group,
            action: action,
            [csrfName]: csrfToken // Dynamic property name
        };

        console.log('Sending data:', JSON.stringify(data));

        $.ajax({
            url: base_url + 'login/ajax_attempt_login',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                console.log('Login response:', response);

                // ✅ CRITICAL: Update CSRF token from response
                if (response.csrf) {
                    // Update form field
                    $('input[name="' + csrfName + '"]').val(response.csrf);
                    // Update meta tag
                    $('meta[name="csrf-token"]').attr('content', response.csrf);
                    console.log('CSRF token updated');
                }

                if (response.success === 1) {
                    // Login successful
                    console.log('Login successful, redirecting to:', response.redirect);
                    window.location.href = response.redirect;
                } else if (response.accounts) {
                    // Multiple accounts found
                    console.log('Multiple accounts found');
                    show_multi_login_popup(response.accounts);
                    // Re-enable button
                    $('#login-button').prop('disabled', false).html('LOGIN');
                } else {
                    // Login failed
                    console.log('Login failed:', response.message);
                    $('.login-error').html(response.message || 'Login failed. Please try again.');
                    $('#login-button').prop('disabled', false).html('LOGIN');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.log('XHR status:', xhr.status);
                console.log('Response text:', xhr.responseText);

                if (xhr.status === 403) {
                    // CSRF error - refresh page to get new token
                    $('.login-error').html('Session expired. Refreshing page...');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    $('.login-error').html('Network error: ' + error + '. Please try again.');
                }

                $('#login-button').prop('disabled', false).html('LOGIN');
            }
        });
    }



    function getFreshCsrfToken() {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: '<?= site_url(); ?>login/ajax_refresh_token',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.csrf) {
                        console.log("Got fresh CSRF token:", response.csrf);
                        resolve(response.csrf);
                    } else {
                        reject('No CSRF token in response');
                    }
                },
                error: function(xhr, status, error) {
                    console.log("CSRF token fetch error:", error);
                    reject('Failed to get CSRF token: ' + error);
                }
            });
        });
    }



    function show_multi_login_popup(accounts) {
        console.log("Showing account selection popup");

        var html = '<div class="account-list">';
        for (var i in accounts) {
            if (accounts[i].enabled) {
                html += '<div class="account-item"><button class="btn btn-outline-primary" onClick="attempt_login(\'' +
                    accounts[i].group + '\'); Swal.close()">' + ucwords(accounts[i].group) + ' - ' + accounts[i].name +
                    '</button></div>';
            } else {
                html += '<div class="account-item disabled"><button class="btn btn-outline-secondary" disabled>' +
                    ucwords(accounts[i].group) + ' - ' + accounts[i].name + ' (Disabled)</button></div>';
            }
        }
        html += '</div>';

        Swal.fire({
            title: 'Choose Account',
            html: html,
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: 'Cancel',
            showConfirmButton: false
        });
    }

    function ucwords(str) {
        return str.replace(/^(.)|\s+(.)/g, function(letter) {
            return letter.toUpperCase();
        });
    }

    // Global AJAX setup to handle CSRF token refresh
    $(document).ready(function() {
        // Store initial CSRF token
        let currentCsrfToken = $('meta[name="csrf-token"]').attr('content') ||
            $('input[name="csrf_rfid_token"]').val();

        // Set up AJAX to always include CSRF token
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (settings.type === 'POST' || settings.type === 'PUT' || settings.type ===
                    'DELETE') {
                    const csrfName = 'csrf_rfid_token';
                    settings.data += '&' + csrfName + '=' + encodeURIComponent(currentCsrfToken);
                }
            }
        });

        // Intercept all AJAX responses to update CSRF token
        $(document).ajaxComplete(function(event, xhr, settings) {
            try {
                // Try to get CSRF token from response
                const responseText = xhr.responseText;
                if (responseText) {
                    const data = JSON.parse(responseText);
                    if (data.csrf) {
                        currentCsrfToken = data.csrf;

                        // Update all CSRF tokens on page
                        $('input[name="csrf_rfid_token"]').val(data.csrf);
                        $('meta[name="csrf-token"]').attr('content', data.csrf);

                        console.log('CSRF token updated globally:', data.csrf);
                    }
                }
            } catch (e) {
                // Not a JSON response or parse error
            }
        });
    });
    </script>
    <!-- Theme Plugins -->
    <script src="<?= site_url(); ?>resources/cms/plugins/theme/sweetalert2/sweetalert2.min.js"></script>
    <style>
    .account-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .account-item {
        margin-bottom: 10px;
    }

    .account-item button {
        width: 100%;
        text-align: left;
        background-color: white;
        border: 1px solid #ccc;
        color: #333;
        padding: 10px;
        transition: background-color 0.3s, color 0.3s;
        outline: none;
    }

    .account-item button:focus {
        box-shadow: none;
    }

    .account-item button:hover {
        background-color: #f0f0f0 !important;
        color: #333 !important;
        border-color: #999 !important;
    }

    .account-item.disabled button {
        cursor: not-allowed;
        opacity: 0.6;
    }
    </style>
</body>

</html>