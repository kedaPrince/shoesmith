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
	<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/parsleyjs/css/parsley.css">

	<!-- MAIN CSS -->
	<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/css/theme/main.min.css?v=<?= $this->config->item('version'); ?>">
	<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/css/ecms.min.css?v=<?= $this->config->item('version'); ?>">
	<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/css/custom.min.css?v=<?= $this->config->item('version'); ?>">

	<script type="text/javascript" src="<?= site_url(); ?>resources/cms/plugins/theme/jquery/jquery-3.5.1.min.js" ></script>
	<script type="text/javascript" src="<?= site_url(); ?>resources/cms/plugins/theme/parsleyjs/js/parsley.min.js"></script>
	<script>
		var csrf            = '<?= $this->security->get_csrf_hash(); ?>';
		var csrfName        = '<?=$this->security->get_csrf_token_name()?>';
		var exportable      = '0';
		var dynamicPath     = '';
	</script>
	<script type="text/javascript" src="<?= site_url(); ?>resources/cms/javascript/core.min.js?v=<?= $this->config->item('version'); ?>"></script>

</head>

<body data-theme="<?= $this->config->item('dark_mode') ? 'dark' : 'light'; ?>" data-color="<?= $this->config->item('theme'); ?>" class="font-nunito login-layout">
	<!-- WRAPPER -->
	<div id="wrapper">
		<div class="vertical-align-wrap">
			<div class="vertical-align-middle auth-main">
				<div class="auth-box">
                    <div class="top">
						<img class="logo" src="<?=site_url()?>resources/cms/images/7diverse-high-resolution-logo-black-.png" alt="<?= $this->config->item('site_name'); ?>"/>
                    </div>
					<div class="card">
                        <div class="header">
                            <p class="lead">Enter a new password</p>
                        </div>
                        <div class="body form-auth-small reset-password-form">
                            <?= form_open(); ?>
                                <?= form_hidden('action', 'reset_password'); ?>
                                <?= form_hidden('group', ''); ?>
                                <?= form_hidden('bgimage', set_value('bgimage', 0)); ?>
                                <div class="form-group">
                                    <label for="signin-password" class="control-label sr-only">Password</label>
                                    <input name="password" type="password" class="form-control" id="signin-password" value="" placeholder="Password" required>
                                    <span class="error-message hide">Please enter your password</span>
                                </div>
                                <div class="form-group">
                                    <label for="signin-password" class="control-label sr-only">Confirm Password</label>
                                    <input name="cpassword" type="password" class="form-control" id="signin-password" value="" placeholder="Confirm Password" required>
                                    <span class="error-message hide">Please confirm your password</span>
                                </div>
								<div class="login-error">
									<?= $this->session->forgotPasswordError ? $this->session->forgotPasswordError : "" ?>
									<?php $this->session->forgotPasswordError = ''; ?>
									<?= form_error('password'); ?>
									<?= form_error('cpassword'); ?>
								</div>
                                <button type="button" class="btn btn-primary btn-lg btn-block submit_button" onclick="form_submit(this)">SUBMIT</button>
							<?= form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END WRAPPER -->
    <style>
        .error-message {
            font-size: 12px;
            color: #D40D0D;
            display: block;
            margin: 2px 0 0 0;
            display: none;
        }
    </style>
    <script>
        var validation = false;

        function form_submit(el) {
            if (!confirmPasswordMatch()) {
                return false;
            }
            $('form').parsley().whenValidate().done(function() {
                $(el).closest('form').submit();
                return false;
            });
        }

        function isPasswordStrong(password, length = 10, containsSymbol = true, containsNumber = true, containsUppercase = true, containsLowercase = true) {
            // Check if password is x characters long
            if (length && password.length < length) { return false; }
            // Must contain a lowercase letter
            if (containsLowercase && !/[a-z]/.test(password)) { return false; }
            // Must contain an uppercase letter
            if (containsUppercase && !/[A-Z]/.test(password)) { return false; }
            // Must contain a number
            if (containsNumber && !/[0-9]/.test(password)) { return false; }
            // Must contain a symbol
            if (containsSymbol && !/[^a-zA-Z0-9\s]/.test(password)) { return false; }
            // Password is strong
            return true;
        }

        function confirmPasswordMatch() {
            var password = $('input[name="password"]').val();
            var passwordContainer = $('input[name="password"]').closest('.form-group');
            var confirmPassword = $('input[name="cpassword"]').val();
            var confirmPasswordContainer = $('input[name="cpassword"]').closest('.form-group');
            var errorMsg = passwordContainer.find('.error-message');
            var errorMsgConfirm = confirmPasswordContainer.find('.error-message');

            let error_message = '';
            if (password.length < 8) {
                error_message = 'Your password should be at least 8 characters';
            }
            if (!isPasswordStrong(password, 8, false, false, false, false)) {
                error_message = 'Your password is not strong enough';
            }
            if (password.length === 0) {
                error_message = 'Please enter your password';
            }
            if (error_message.length > 0) {
                errorMsg.text(error_message).show();
                passwordContainer.addClass('error');
            } else {
                errorMsg.text('').hide();
                passwordContainer.removeClass('error');
            }

            error_message_confirm = '';
            if (!isPasswordStrong(confirmPassword, 8, false, false, false, false)) {
                error_message_confirm = 'Your confirmed password is not strong enough';
            }
            if (password !== confirmPassword) {
                error_message_confirm = 'Passwords do not match';
            }
            if (confirmPassword.length === 0) {
                error_message_confirm = 'Please confirm your password';
            }
            if (error_message_confirm.length > 0) {
                errorMsgConfirm.text(error_message_confirm).show();
                confirmPasswordContainer.addClass('error');
            } else {
                errorMsgConfirm.text('').hide();
                confirmPasswordContainer.removeClass('error');
            }

            return error_message.length === 0 && error_message_confirm.length === 0;
        }

        $(document).ready(function() {
            var $ = jQuery;

            // Submit form on enter key
            $('form input').on('keyup', function(e) {
                if (e.keyCode == 13) {
                    $(".submit_button").trigger('click');
                }
            });

            // Add and hide error messages
            $('.reset-password-form .set-password-btn').on('click', function() {
                validation = true;

                $('.reset-password-form .form-group input[required]').each(function() {
                    if ($(this).val() == '') {
                        validation = false;
                        $(this).closest('.form-group').addClass('error');
                        $(this).closest('.form-group').find('.error-message').show();
                    } else {
                        $(this).closest('.form-group').removeClass('error');
                        $(this).closest('.form-group').find('.error-message').hide();
                        confirmPasswordMatch();
                    };
                });
            });

            $('input[name="password"]').on('input', function() {
                confirmPasswordMatch();
            });

            $('input[name="cpassword"]').on('input', function() {
                confirmPasswordMatch();
            });
        });
    </script>
</body>
</html>
