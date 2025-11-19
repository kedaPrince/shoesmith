<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">

<head>
    <title>Login - <?= $this->config->item('site_name'); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="7Diverse">

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
                            <?= form_open('', ['id' => 'login-form']); ?>
                            <?= form_hidden('action', 'login'); ?>
                            <?= form_hidden('group', ''); ?>
                            <?= form_hidden('bgimage', set_value('bgimage', 0)); ?>
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
                                        href="<?= url('login/forgot-password'); ?>">Forgot password?</a></span>
                            </div>
                            <?= form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END WRAPPER -->
    <script>
    $(document).ready(function() {
        console.log("Document ready - JavaScript loaded");

        $('input').attr('autocomplete', 'off');

        // Handle form submission
        $('#login-form').on('submit', function(e) {
            console.log("Form submitted");
            e.preventDefault(); // Prevent default form submission

            // Clear previous errors
            $('.login-error').html('');

            $('#login-form').parsley().whenValidate().done(function() {
                console.log("Form validation passed");
                attempt_login();
            }).fail(function() {
                console.log("Form validation failed");
            });
        });

        //Submit form on enter key
        $('#login-form input').on('keyup', function(e) {
            if (e.keyCode == 13) {
                console.log("Enter key pressed");
                $('#login-form').trigger('submit');
            }
        });
    });

    function attempt_login(group) {
        console.log("=== ATTEMPT LOGIN START ===");

        if (typeof group === 'undefined' || !group) {
            var group = 0;
        }

        var email = $('#signin-email').val();
        var password = $('#signin-password').val();

        console.log("Email:", email, "Group:", group);

        // Show loading
        $('#login-button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> LOGGING IN...');

        // Use basic jQuery AJAX for better control
        $.ajax({
            url: '<?= site_url(); ?>login/ajax_attempt_login',
            type: 'POST',
            dataType: 'json',
            data: {
                'email': email,
                'password': password,
                'group': group,
                'action': 'login',
                '<?=$this->security->get_csrf_token_name()?>': '<?= $this->security->get_csrf_hash(); ?>'
            },
            success: function(response) {
                console.log("=== AJAX SUCCESS ===");
                console.log("Full response:", response);

                if (response.success === 1) {
                    console.log("Login successful, redirecting to:", response.redirect);
                    window.location.href = response.redirect;
                } else if (response.accounts) {
                    console.log("Multiple accounts found");
                    show_multi_login_popup(response.accounts);
                } else {
                    console.log("Login failed:", response.message);
                    $('.login-error').html(response.message || 'Login failed. Please try again.');
                    $('#login-button').prop('disabled', false).html('LOGIN');
                }
            },
            error: function(xhr, status, error) {
                console.log("=== AJAX ERROR ===");
                console.log("Status:", status);
                console.log("Error:", error);
                console.log("Response Text:", xhr.responseText);

                // Try to parse the response if it's JSON
                try {
                    var response = JSON.parse(xhr.responseText);
                    $('.login-error').html(response.message || 'Login request failed');
                } catch (e) {
                    $('.login-error').html('Login request failed. Please try again.');
                }

                $('#login-button').prop('disabled', false).html('LOGIN');
            }
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