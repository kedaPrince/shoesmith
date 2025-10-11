<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Redir
 *
 * Redirects the users to the given path.
 * The correct login group and language will be added to the path if needed.
 * Can be used to get the correct paths for links if return is set to true.
 *
 * @param string $path
 * @param bool $return
 * @param string $group
 *
 * @return mixed
 */
	function redir($path, $return = FALSE, $group = NULL) {
		$ci =& get_instance();
		$loginGroups = $ci->config->item('login_groups');

		if ( ! empty($group) && $return) {
			return site_url() . $group . '/' . $path;
		}

		$url = site_url();
		if (array_key_exists($ci->uri->segment(1), $loginGroups)) {
			//Add the login group segment
			$url .= $ci->uri->segment(1) . '/';

			if (strlen($ci->uri->segment(2) ? $ci->uri->segment(2) : 0) == 2) {
				//Add the language segment
				$url .= $ci->uri->segment(2) . '/';
			}
		} elseif (strlen($ci->uri->segment(1) ? $ci->uri->segment(1) : 0) == 2) {
			//No login segment present, so just add the language segment if it's there
			$url .= $ci->uri->segment(1) . '/';
		}

		if ( ! $return) {
			//Do the redirect
			redirect($url . $path);
		} else {
			return $url . $path;
		}
	}

/**
 * URL
 *
 * Generates and return a url based on the given path.
 *
 * @param string $path
 * @param string $group
 *
 * @return string
 */
	function url($path, $group = NULL) {
		return redir($path, TRUE, $group);
	}

/**
 * Send mail
 *
 * Parses the template and loads the mails to the mailing queue.
 *
 * @param string $template
 * @param string $to
 * @param string $subject
 * @param array $params
 * @param string $from
 * @param bool $force
 * @param array $cc
 * @param array $bcc
 * @param array $files
 */
	function send_mail($template, $to, $subject, $params = array(), $from = "", $force = FALSE, $cc = array(), $bcc = array(), $files = array()) {
		$ci =& get_instance();
		$ci->load->library('mailer');

		! empty($from) || $from = $ci->config->item('sys_email');
		! empty($params['site_name']) || $params['site_name'] = $ci->config->item('site_name');
		! empty($params['login_url']) || $params['login_url'] = site_url() . 'login';
		! empty($params['site_url']) || $params['site_url'] = site_url();

		$lan = get_language();

		//Load the template
		$html = $ci->load->view('cms/emails/' . $lan . '/' . $template . '.html', '', TRUE);
		if ( ! empty($params)) {
			$ci->load->library('parser');

			//Parse the template
			$html = $ci->parser->parse_string($html, $params, TRUE);
			$subject = $ci->parser->parse_string($subject, $params, TRUE);
		}

		//Add mail to mailing queue
		if (is_array($to)) {
			foreach ($to as $t) {
				$ci->mailer->queue($t, $from, $subject, $html, $force, $cc, $bcc, $files);
			}
		}
		else {
			$ci->mailer->queue($to, $from, $subject, $html, $force, $cc, $bcc, $files);
		}
	}

/**
 * Send mail
 *
 * Parses the template and loads the mails to the mailing queue.
 *
 * @param string $template
 * @param string $to
 * @param string $subject
 * @param array $params
 * @param string $from
 * @param bool $force
 * @param array $cc
 * @param array $bcc
 * @param array $files
 */
	function send_mail_with_data($template, $to, $subject, $params = array(), $from = "", $force = FALSE, $cc = array(), $bcc = array(), $files = array()) {
		$ci =& get_instance();
		$ci->load->library('mailer');

		! empty($from) || $from = $ci->config->item('sys_email');

		$lan = get_language();

		$view = '';

		if (file_exists(APPPATH . 'views/cms/emails/' . $lan . '/' . $template . '.php')) {
			$view = 'cms/emails/' . $lan . '/' . $template . '.php';
		} elseif (file_exists(APPPATH . 'views/cms/emails/' . $lan . '/' . $template . '.html')) {
			$view = 'cms/emails/' . $lan . '/' . $template . '.php';
		} else {
			return;
		}

		//Load the template
		$html = $ci->load->view($view, array(
			'data' => $params
		), TRUE);

		//Add mail to mailing queue
		if (is_array($to)) {
			foreach ($to as $address) {
				$ci->mailer->queue($address, $from, $subject, $html, $force, $cc, $bcc, $files);
			}
		} else {
			$ci->mailer->queue($to, $from, $subject, $html, $force, $cc, $bcc, $files);
		}
	}

	function get_user_playbooks(){
		$ci =& get_instance();

		// if staff
		if (is_group_logged_in("staff")){
			// get staff member
			$conncet = $ci->db;
			$conncet->join('pivot_staff_access_groups', 'pivot_staff_access_groups.access_group_id = pivot_playbook_access_groups.access_group_id');
			$conncet->join('usr_staff', 'usr_staff.id = pivot_staff_access_groups.staff_id');
			$conncet->join('mod_playbooks', 'pivot_playbook_access_groups.playbook_id = mod_playbooks.id');
			$conncet->where('usr_staff.id', loginID());
			$query = $conncet->get('pivot_playbook_access_groups');

			$return_data  = $query->result_array();
			if (!empty($return_data)){
				return format_menu_items($return_data);
			}
		}
		return [];
	}