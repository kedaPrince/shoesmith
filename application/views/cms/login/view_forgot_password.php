<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">

<head>
	<title>Forgot Password - <?= $this->config->item('site_name'); ?></title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="author" content="7Diverse">

	<link rel="icon" type="image/png" href="<?=site_url()?>resources/cms/images/favicon.ico" sizes="32x32">

	<!-- VENDOR CSS -->
	<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/parsleyjs/css/parsley.css">
	<link rel="stylesheet" type="text/css" href="<?= site_url(); ?>resources/cms/plugins/theme/sweetalert2/sweetalert2.min.css">

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
						<img class="logo" src="<?=site_url()?>resources/cms/images/logo-image.png" alt="<?= $this->config->item('site_name'); ?>"/>
                    </div>
					<div class="card">
                        <div class="header">
                            <p class="lead">Recover my password</p>
                        </div>
                        <div class="body form-auth-small">
                            <p>Please enter your email address below to receive instructions on how to reset your password.</p>
                            <?= form_open(); ?>
								<?= form_hidden('action', 'forgot_password'); ?>
								<?= form_hidden('redirect', 'false'); ?>
								<?= form_hidden('bgimage', set_value('bgimage', 0)); ?>
								<div class="form-group">
                                    <label for="signin-email" class="control-label sr-only">Email</label>
                                    <input name="email" type="email" class="form-control" id="signin-email" value="" placeholder="Email" required data-parsley-required-message="Please enter an email address.">
                                </div>
								<div class="login-error">
									<?= $this->session->forgotPasswordError ? $this->session->forgotPasswordError: "" ?>
									<?php $this->session->forgotPasswordError = ''; ?>
									<?= $this->session->forgotPasswordSuccess ? $this->session->forgotPasswordSuccess: "" ?>
									<?php $this->session->forgotPasswordSuccess = ''; ?>
									<?= form_error('email'); ?>
								</div>
                                <button type="button" class="btn btn-primary btn-lg btn-block submit_button" onclick="form_submit(this)">RESET PASSWORD</button>
                                <div class="bottom">
                                    <span class="helper-text">Know your password? <a href="<?= url('login'); ?>">Login</a></span>
                                </div>
							<?= form_close(); ?>
                        </div>
                    </div>
				</div>
			</div>
		</div>
	</div>
	<!-- END WRAPPER -->
	<script type="text/javascript">
		$(document).ready(function(){
			//Submit form on enter key
			$('form input').on('keyup', function(e){
				if(e.keyCode == 13){
					$(".submit_button").trigger('click');
				}
			});
			
		});

		function form_submit(el) {

			$('form').parsley().whenValidate().done(function() {
				$(el).closest('form').submit();
				return false;
			});
		}
	</script>
</body>
</html>