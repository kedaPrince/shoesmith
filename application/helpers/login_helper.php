<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * LoginID
 *
 * Returns the User ID for the logged in user.
 * By default, If login session is not found, then redirect to the login page.
 * Setting redirect to false will return 0 instead of redirecting the user.
 *
 * @param string $group (optional)
 * @param bool $redirect (optional)
 *
 * @return int
 */
function loginID($group="", $redirect=true)
{
	$ci =& get_instance();
	!empty($group)          || $group = $ci->uri->segment(1,'');

	$loginGroups = $ci->config->item('login_groups');

	if (!empty($loginGroups[$group])) {
		if (!empty($ci->session->login[$group])) {
			return $ci->session->login[$group]['id'];
		}
		else {
			if ($redirect) {
				$ci->session->set_userdata('last_page', array($group => current_url()));
				goto_login();
			}
			else {
				return 0;
			}
		}
	}
	else {
		return 0;
	}


}

/**
 * LoginData
 *
 * Returns the data array for the logged in user.
 * If login session is not found, then redirect to the login page.
 *
 * @param string $group
 * @return bool
 */
function loginData($group="")
{
	$ci =& get_instance();
	!empty($group)          || $group = $ci->uri->segment(1,'');

	$loginGroups = $ci->config->item('login_groups');

	if (!empty($loginGroups[$group])) {
		if (!empty($ci->session->login[$group])) {
			return $ci->session->login[$group];
		}
		else {
			$ci->session->set_userdata('last_page', array($group => current_url()));
            return false;
		}
	}
	else {
		return false;
	}

}

function loginVar($var, $group="") {
	$loginData = loginData($group);

	if (!empty($loginData[$var])) {
		return $loginData[$var];
	}
	else {
		return "";
	}
}

function is_logged_in($log_out_url = 'login', $group = '') {
	$ci =& get_instance();

	!empty($group)          || $group = $ci->uri->segment(1,'');

	if (empty($ci->session->login[$group])) {
		$ci->session->set_userdata('last_page', array($group => current_url()));
		goto_login($log_out_url);
	}

}

function goto_login($log_out_url = 'login') {
	if (is_ajax()) {
		echo '<script>window.location = "'.site_url().'login";</script>';
		exit();
	}
	else {
		redirect(site_url().$log_out_url);
	}
}

function is_group_logged_in($group) {
	$ci =& get_instance();

	if (is_array($group)) {
		foreach ($group as $g) {
			if (!empty($ci->session->login) && !empty($ci->session->login[$g])) {
				return true;
			}
		}
	}
	elseif (!empty($ci->session->login) && !empty($ci->session->login[$group])) {
		return true;
	}

	return false;
}

function go_default_url($group='') {
	$ci =& get_instance();
	!empty($group)          || $group = $ci->uri->segment(1,'');

	$loginGroups    = $ci->config->item('login_groups');
	$url            = $loginGroups[$group]['default_url'];

	redir($url);
}

function setLoginVar($var, $value, $group="") {
	$ci =& get_instance();
	!empty($group)          || $group = $ci->uri->segment(1,'');

	$loginData 	= loginData($group);
	$login 		= $ci->session->login;

	$loginData[$var] 	= $value;
	$login[$group] 		= $loginData;

	$ci->session->login = $login;
}

function current_login_group() {
	$ci =& get_instance();

	$group 			= $ci->uri->segment(1,'');
	$loginGroups    = $ci->config->item('login_groups');

	if (!empty($loginGroups[$group])) {
		return $group;
	}
	else {
		return '';
	}
}