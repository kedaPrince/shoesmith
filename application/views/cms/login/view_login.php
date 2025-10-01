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
                        <img class="logo"
                            src="<?=site_url()?>resources/cms/images/7diverse-high-resolution-logo-black-.png"
                            alt="<?= $this->config->item('site_name'); ?>" />
                    </div>
                    <div class="card">
                        <div class="header">
                            <p class="lead">Login to your account</p>
                        </div>
                        <div class="body form-auth-small">
                            <?= form_open(); ?>
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
                            <div class="login-error">
                                <?= form_error('password'); ?>
                            </div>
                            <button type="button" class="btn btn-primary btn-lg btn-block submit_button"
                                onclick="form_submit(this)">LOGIN</button>
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
    // $(document).ready(function(){
    // 	//$('form').parsley();
    $('input').attr('autocomplete', 'off');

    //Submit form on enter key
    $('form input').on('keyup', function(e) {
        if (e.keyCode == 13) {
            $(".submit_button").trigger('click');
        }
    });

    // });

    function form_submit(el) {

        $('form').parsley().whenValidate().done(function() {
            //Attempt login
            attempt_login();
            return false;
        });
    }

    function attempt_login(group) {
        //Check if max batches has been set else set to 5
        if (typeof group === 'undefined' || !group) {
            var group = 0;
        }

        var email = $('form').find('input[name="email"]').val();
        var password = $('form').find('input[name="password"]').val();

        //Attempt login
        show_loader();
        ajax_post('', {
            'email': email,
            'password': password,
            'group': group
        }, function(d) {
            if (d.success == true) {
                if (d.accounts) {
                    //Multiple logins has been found. Ask which section to login into
                    show_multi_login_popup(d.accounts);
                    hide_loader();
                } else {
                    //User has been logged in. Redirect.
                    window.location.replace(d.redirect);
                }
            } else {
                $('.login-error').html(d.message);
                $('input').removeClass('parsley-success').addClass('parsley-error');
                hide_loader();
            }
        }, {
            url: '<?= site_url(); ?>login/ajax_attempt_login'
        });
    }

    function show_multi_login_popup(accounts) {
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

    // Ensure you have the ucwords function defined
    function ucwords(str) {
        return str.replace(/^(.)|\s+(.)/g, function(letter) {
            return letter.toUpperCase();
        });
    }

    function show_loader() {
        $('.loader').show();
    }

    function hide_loader() {
        $('.loader').hide();
    }

    // function close_choice_popup() {
    // 	$('.login-choice-popup').removeClass('open');
    // }

    function ucwords(str) {
        str = str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
            return letter.toUpperCase();
        });
        return str;
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
        /* Ensure background is white */
        border: 1px solid #ccc;
        /* Add a border */
        color: #333;
        /* Text color */
        padding: 10px;
        transition: background-color 0.3s, color 0.3s;
        /* Smooth transition */
        outline: none;
        /* Remove default focus outline */
    }

    .account-item button:focus {
        box-shadow: none;
        /* Remove default focus shadow */
    }

    .account-item button:hover {
        background-color: #f0f0f0 !important;
        /* Light grey background on hover */
        color: #333 !important;
        /* Ensure text remains visible */
        border-color: #999 !important;
        /* Darken the border on hover */
    }

    .account-item.disabled button {
        cursor: not-allowed;
        opacity: 0.6;
    }
    </style>
</body>

</html>