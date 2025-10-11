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
<<<<<<< HEAD
	}

	$ci =& get_instance();

	$selectedValues = [];
	if (is_array($ci->input->post($field))) {
		foreach ($ci->input->post($field) as $postedValue) {
			$selectedValues[] = $postedValue;
		}
	}
	else {
		$options = $ci->formFields['multi_selects'][$field];

		$ci->db->select($options['link_field']);
        $ci->db->where($options['main_field'], $id);
        $results = $ci->db->get($options['pivot_table'])->result();

        foreach ($results as $row) {
            $selectedValues[] = $row->{$options['link_field']};
        }
	}

	return $selectedValues;
}

function parent_url() {
	$ci =& get_instance();

	$submodule = submodule_data($ci->pageName);

	if (!$submodule) {
		return false;
	}

	return url($submodule->pageName);
}

function trans_start() {
	$ci =& get_instance();

	$ci->db->trans_start();
}

function trans_complete() {
	$ci =& get_instance();

	$ci->db->trans_complete();
}

function trans_success() {
	$ci =& get_instance();

	return ($ci->db->trans_status() !== FALSE);
}

function load_listing_page($pageData=array(), $headerData=array(), $footerData=array()) {
	$ci =& get_instance();

	//Load the listing view
	$ci->load->view($ci->folder.'/view_header', $headerData);
	$ci->load->view('cms/crud/view_list', $pageData);
	$ci->load->view($ci->folder.'/view_footer', $footerData);
}

function load_single_page($pageData=array(), $headerData=array(), $footerData=array()) {
	$ci =& get_instance();

	//Load the single view
	$ci->load->view($ci->folder.'/view_header', $headerData);
	$ci->load->view('cms/crud/view_single', $pageData);
	$ci->load->view($ci->folder.'/view_footer', $footerData);
}

function load_custom_page($path, $pageData=array(), $headerData=array(), $footerData=array()) {
	$ci =& get_instance();

	//Load the custom view
	$ci->load->view($ci->folder.'/view_header', $headerData);
	$ci->load->view($path, $pageData);
	$ci->load->view($ci->folder.'/view_footer', $footerData);
}

function users_allowed($userTypes, $redirectPath='') {
	$userTypes = !is_array($userTypes) ? [$userTypes] : $userTypes;

	if(!in_array(loginVar('user_type_id', 'user'), $userTypes)) {
		redirect(site_url().$redirectPath);
	}
}

function format_menu_items($items_array){
    $array_items = [];
    foreach ($items_array as $item){
        $url = "playbook_sections/playbook_id/".$item['slug'];
        $mnu_item = (object)array(
            'page'          => 'playbook_sections',
            'view'          => 'listing',
            'label'         =>  $item['name'],
            'icon'          => 'fa-folder-open',
            'url'           => url($url),
            'show'          => true,
        );
        array_push($array_items , $mnu_item);
    }

    if (!empty($array_items)){
        return $array_items;
    }
    else{
        return [];
    }
}


// Add this to application/helpers/general_helper.php or create a new helper
if (!function_exists('time_ago')) {
    function time_ago($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $mins = round($diff / 60);
            return $mins . ' min' . ($mins == 1 ? '' : 's') . ' ago';
        } elseif ($diff < 86400) {
            $hours = round($diff / 3600);
            return $hours . ' hour' . ($hours == 1 ? '' : 's') . ' ago';
        } elseif ($diff < 604800) {
            $days = round($diff / 86400);
            return $days . ' day' . ($days == 1 ? '' : 's') . ' ago';
        } else {
            return date('M j, Y', $time);
        }
    }
}

if (!function_exists('character_limiter')) {
    function character_limiter($str, $n = 500, $end_char = '&#8230;') {
        if (strlen($str) < $n) {
            return $str;
        }
        
        $str = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));
        
        if (strlen($str) <= $n) {
            return $str;
        }
        
        $out = "";
        foreach (explode(' ', trim($str)) as $val) {
            $out .= $val.' ';
            if (strlen($out) >= $n) {
                $out = trim($out);
                return (strlen($out) === strlen($str)) ? $out : $out.$end_char;
            }
        }
    }
}
=======
	}
>>>>>>> 711e81e28e28cf18860d036865290b5d2e3dc386
