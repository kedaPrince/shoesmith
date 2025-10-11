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

/**
 * Get Language
 *
 * Returns the current language based on the current url
 *
 * @return string
 */
function get_language() {
	$ci =& get_instance();
	$languages = $ci->config->item('languages');

	if (strlen($ci->uri->segment(2) == 2)) {
		$l = $ci->uri->segment(2, '');
		if (isset($languages[$l])) {
			$lan = $languages[$l];
		}
	}
	elseif (strlen($ci->uri->segment(1) == 2)) {
		$l = $ci->uri->segment(1, '');
		if (isset($languages[$l])) {
			$lan = $languages[$l];
		}
	}
	else {
		//Language could not be determined, so use default
		$lan = $ci->config->item('language');
	}

	return $lan;
}

function uri_segment($num, $default = "") {
	$ci =& get_instance();
	$loginGroups = $ci->config->item('login_groups');

	$segSkip = 0;

	//Check lst segment is login group, otherwise check for language, else return as is.
	if (array_key_exists($ci->uri->segment(1), $loginGroups)) {
		$segSkip++;
		if (strlen($ci->uri->segment(2)) == 2) {
			$segSkip++;
		}
	}
	elseif (strlen($ci->uri->segment(1)) == 2) {
		$segSkip++;
	}

	return $ci->uri->segment($num + $segSkip, $default);
}

/**
 * Sluggifies a string
 * 
 * @param string $str
 * @return string
 */
function sluggify($str, $table) {
	if ( ! empty($str)) {
		$str = strtolower($str);
		$str = str_replace(' ', '-', $str);
		$str = html_entity_decode($str);
		$str = strip_tags($str);
		$str = stripslashes($str);
		$str = str_replace('\'', '', $str);
		$str = preg_replace('/[^a-z0-9]+/', '-', $str);
		$str = trim($str, '-');

		$slug = check_unique_slug($str, $table);

		return $slug;
	}

	return FALSE;
}

function check_unique_slug($slug, $table) {
	$ci =& get_instance();

	$ci->db->where('slug', $slug);
	$ci->db->limit(1);
	$query = $ci->db->get($table);

	if ($query->num_rows() > 0) {
		//Slug is not unique.
		if (preg_match('/\d+$/', $slug)) {
			$slug = preg_replace_callback('/(\d+$)/', function($matches) use ($slug) {
				$str = preg_replace('/\d+$/', '', $slug);

				return ($matches[0] + 1);
			}, $slug);
		} else {
			$slug .= '2';
		}

		//Call function again to recheck the slug
		$slug = check_unique_slug($slug, $table);
	}

	//At this point the slug is unique with the given table
	return $slug;
}

/**
 * Die and dump
 * @param  [string] $str
 */
function dd($str, $die = TRUE) {
	$bt = debug_backtrace();
	$caller = array_shift($bt);

	echo '<div style="weight: bold">' . $caller['file'] . ': ' . $caller['line'] . '</div>' . "\n";
	echo '<pre>';
	is_bool($str) ? var_dump($str) : print_r($str);
	echo '</pre>' . "\n\n";
	$die && die;
}

function redirect_back($redirect = TRUE) {
	$ci =& get_instance();
	if (isset($_SERVER['HTTP_REFERER'])) {
		$referer = $_SERVER['HTTP_REFERER'];
		redirect($referer);
	}

	if ($redirect) {
		redirect($ci->router->fetch_class());
	}

	return $referer;
}



/**
 * Flash Notification
 * 
 * Adds to the flashNotifications array which is saved in session which will be shown on the next page load
 * or after specific events
 * 
 * @param string $message
 * @param string $type (optional)
 * @param string $position (optional)
 * @param int $timeout (optional)
 */
function flash_notification($message, $type="info", $position="bottom-right", $timeout=3000) {
	$ci =& get_instance();

	//Build new notification
	$newNotification = array(
		'message' 	=> $message,
		'type'		=> $type,
		'position'	=> $position,
		'timeout'	=> $timeout
	);

	//Get what's currently saved in the array
	$notifications = $ci->session->flashNotifications;

	//If there's nothing there then force it to an array
	if (empty($notifications)) {
		$notifications = array();
	}

	//Add new notification
	$notifications[] = $newNotification;

	//Add array back to session
	$ci->session->flashNotifications = $notifications;
}

/**
 * Show Flash Notifications
 * 
 * Show all flash notifications stored in session
 */
function show_flash_notifications() {
	$ci =& get_instance();

	//Get notifications stored in session
	$notifications = $ci->session->flashNotifications;

	//If no notifications then do nothing
	if (empty($notifications)) {
		return false;
	}

	//For multiple notifications well add extra time to the later ones so they stay a little longer
	$extraTime = 0;

	echo '
		<script>
			$(function() {
	';
	foreach ($notifications as $n) {
		echo '
				toastr.options.timeOut = '.($n['timeout'] + $extraTime).';
				toastr.options.closeButton = true;
				toastr.options.positionClass = \'toast-'.$n['position'].'\';
				toastr[\''.$n['type'].'\'](\''.$n['message'].'\');
		';

		$extraTime += 1000;
	}

	echo '
			});
		</script>
	';

	//Empty out session
	$ci->session->flashNotifications = null;
}

/**
 * Select Options
 *
 * Builds a list of options for dropdowns.
 *
 * @param array $options
 * @param int $value
 * @param string $idField (optional)
 * @param string $nameField (optional)
 * @return string
 */
function select_options($options, $value, $idField = "id", $nameField = "name") {
	if (is_object($options)) {
		$options = $options->result_array();
	}

	//If array contents is not an array then assume the key value pare is id => value
	if (current($options) != '' && ! is_array(current($options))) {
		//Convert this array to the expected format
		$newOptions = array();
		foreach ($options as $k => $v) {
			$newOptions[] = array(
				$idField => $k,
				$nameField => $v
			);
		}
		$options = $newOptions;
	}

	$html = '';
	foreach ($options as $row) {

		//Check if removed option is passed
		$optionClass = '';
		if (!empty($row['removed'])) {
			$optionClass = 'data-class="removed"';
		}

		$selected = ($value !== '' && $row[$idField] == $value) ? 'selected="selected"' : '';
		$html .= '<option '.$optionClass.' value="' . $row[$idField] . '" ' . $selected . '>' . $row[$nameField] . '</option>' . "\n";
	}

	return $html;
}

function text_select_options($options, $value, $group, $idField = "id", $nameField = "name") {
	if (is_object($options)) {
		$options = $options->result_array();
	}

	$html = '';
	foreach ($options as $row) {
		$checked = ($row[$idField] == $value) ? 'checked="checked"' : '';
		$selected = ($row[$idField] == $value) ? 'selected' : '';
		$html .= '<li rel="' . $row[$nameField] . '"><label class="' . $selected . '" rel="' . $row[$nameField] . '">' . $row[$nameField] . '<input ' . $checked . ' type="radio" name="' . $group . '" value="' . $row[$idField] . '"/></label></li>';
	}

	return $html;
}


function multi_select_options($options, $value, $idField = "id", $nameField = "name") {
	if (is_object($options)) {
		$options = $options->result_array();
	}

	$html = '';
	foreach ($options as $row) {
		$selected = ($row[$idField] == in_array($row[$idField], (array)$value)) ? 'selected="selected"' : '';
		$html .= '<option value="' . $row[$idField] . '" '.$selected.'><label class="' . $selected . '">'.$row[$nameField].'</option>';
	}

	return $html;
}

/**
 * Ajax return
 *
 * Outputs a json response with the new csrf token
 * as well as the extra reponse data
 *
 * @param array $data (optional)
 */
function ajax_return($data = array(), $header = 200) {
	$ci =& get_instance();

	$data = array_merge(array(
		'success' => TRUE,
		'csrf' => $ci->security->get_csrf_hash(),
	), $data);

	$ci->output->set_status_header($header);
	$ci->output->set_content_type('application/json');
	$ci->output->set_output(json_encode($data));
}

function langs($line, $replacements = array()) {
	$ci =& get_instance();
	$line = $ci->lang->line($line);

	if ( ! empty($replacements)) {
		$ci->load->library('parser');
		$line = $ci->parser->parse_string($line, $replacements, TRUE);
	}

	return $line;
}

function set_checked($field, $value = '', $optionValue = 1) {
	if (set_value($field, $value) == $optionValue) {
		$checked = 'checked="checked"';
	} else {
		$checked = '';
	}
	return $checked;
}

function span_set_checked($field, $value = '', $optionValue = 1) {
	if (set_value($field, $value) == $optionValue) {
		$checked = 'checked';
	} else {
		$checked = '';
	}
	return $checked;
}

function clean($str) {
	return trim(preg_replace('/\s+/', ' ', $str));
}

function is_ajax() {
	return 'xmlhttprequest' == ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}

function js_escape($str) {
	$str = addslashes($str);
	$str = preg_replace('/\r/', '\\r', $str);
	$str = preg_replace('/\n/', '\\n', $str);

	return $str;
}

function entity_decoder($string) {
	return preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
		return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
	}, $string);
}

function clean_dynamic_field_name($str) {
	$output = array_filter(explode(' ', preg_replace('/\W/', ' ', $str)));
	end($output);
	$key = key($output);
	return $output[$key];
}

/**
 * Dir Create
 *
 * Check if file path is accessable.
 * If not, then create it.
 */
function dir_create($path) {

	$pathArray = explode('/', $path);
	$dir = 'resources';

	foreach ($pathArray as $p) {
		if ($p == 'resources') {
			continue;
		}
		$dir .= '/' . $p;

		if ( ! is_dir($dir)) {
			mkdir($dir, 0755);
		}
	}
}

function abs_path() {
	//$path = realpath(dirname(__FILE__).'../../../').'/';
	$path = dirname(__FILE__);
	$path = preg_replace('/application\/helpers$/', '', $path);

	//For windows environments
	$path = preg_replace('/\\\\application\\\\helpers/', '/', $path);

	return $path;
}

/**
 * Delete when new file uploader is in place
 */
function src_link($hash, $file, $size = '') {
	$size = ! empty($size) ? '_' . $size : '';
	$link = site_url() . 'files/' . $hash . $size . '/' . $file;

	return $link;
}

/**
 * Delete when new file uploader is in place
 */
function image_link($hash, $file, $size = '') {
	$size = ! empty($size) ? '_' . $size : '';

	if ( ! empty($hash) && ! empty($file)) {
		$link = site_url() . 'images/' . $hash . $size . '/' . $file;
	} else {
		$link = site_url() . 'resources/no_image/general.png';
	}

	return $link;
}

function download_link($str) {
	$link = site_url() . 'download/'.$str;

	return $link;
}


function format_bytes($bytes) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB');
	$size = array(1, 1024, pow(1024, 2), pow(1024, 3), pow(1024, 4));
	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$value = $bytes / $size[$pow];

	return round($value) . ' ' . $units[$pow];
}

function unique_filename($path, $file) {
	if (file_exists($path . $file)) {
		//Generate new filename
		$ci =& get_instance();
		$ci->load->helper('string');;

		$ext = pathinfo($file, PATHINFO_EXTENSION);
		$newfile = random_string('alnum', 12) . '.' . $ext;

		return unique_filename($path, $newfile);
	}
	else {
		return $file;
	}
}

function safe_file_name($filename) {
	$filename = preg_replace('/[^\d\w\.\_]+/', '-', $filename);
	return $filename;
}

function login_token() {
	$ci =& get_instance();
	$token = $ci->session->userdata('loginToken');

	return $token;
}

/**
 * Delete File
 *
 * Deletes the given file from the server.
 * Also deletes any empty folders left behind
 *
 * @param string $file
 *
 * @return bool|null
 */
function delete_file($file) {
	$dir = pathinfo($file, PATHINFO_DIRNAME);
	//Delete file from server
	file_exists($file) && unlink($file);

	//Check if dir is empty
	$isEmpty = TRUE;
	if ( ! empty($dir)) {
		if ( ! is_readable($dir)) return NULL;
		$handle = opendir($dir);
		while (FALSE !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				$isEmpty = FALSE;
				break;
			}
		}
		closedir($handle);
	}

	//If dir is empty then delete it.
	$isEmpty && rmdir($dir);
}

/**
 * Output File
 *
 * This function helps with outputting large files
 * for downloading purposes
 *
 * @param string $filename
 */
function output_file($filename) {
	$file = @fopen($filename, "rb");
	while ( ! feof($file)) {
		print(@fread($file, 1024 * 8));
		if (ob_get_level() > 0) ob_flush();
		flush();
	}
	@fclose($file);
}

/**
 * Output Temp File
 *
 * Combines the output_file and delete_file functions
 * to serve up temp files and delete them afterwards
 *
 * @param $filename
 */
function output_temp_file($filename) {
	output_file($filename);
	delete_file($filename);
}

function value_exists($val, $default = FALSE) {
	if (isset($val)) {
		return $val;
	} else {
		if ($default !== FALSE) {
			return $default;
		}
	}

	return '';
}

function determine_separator($path) {
	$file = file_get_contents($path, false, NULL, 0, 1000);

	$commas = array();
	$semicolons = array();

	preg_match_all('/\,/', $file, $commas);
	preg_match_all('/\;/', $file, $semicolons);

	$commaCount = count($commas[0]);
	$semicolonCount = count($semicolons[0]);

	if ($semicolonCount > $commaCount) {
		return ';';
	} else {
		return ',';
	}
}

function array_merge_sum($a1, $a2) {
	foreach ($a2 as $key => $val) {
		if ( ! empty($a1[$key])) {
			$a1[$key] += $val;
		}
		else {
			$a1[$key] = $val;
		}
	}

	return $a1;
}

function format_link($link) {
	//Check if link has protocol, other prepend it
	if ( ! preg_match('/\:\/\//', $link)) {
		$link = 'https://' . $link;
	}

	return $link;
}

function build_attributes($attr = array()) {
	$str = '';
	foreach ($attr as $a => $v) {
		if ($v != '') {
			$str .= $a . '="' . $v . '" ';
		}
	}
	return $str;
}

function qm_next_button($label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : "Next";

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'qm-btn-next btn btn-secondary float-right ' . $class;
	$attr['onclick'] = isset($attr['onclick']) ? $attr['onclick'] : 'qm_next_tab(this)';

	//Build html
	$html = '<a ' . build_attributes($attr) . '>' . $label . '</a>';

	return $html;
}

function qm_prev_button($label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : "Back";

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'qm-btn-prev btn btn-secondary float-right ' . $class;
	$attr['onclick'] = isset($attr['onclick']) ? $attr['onclick'] : 'qm_prev_tab(this)';

	//Build html
	$html = '<a ' . build_attributes($attr) . '>' . $label . '</a>';

	return $html;
}

function qm_close_button($label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : "Cancel";

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'qm-btn-close btn btn-light float-right ' . $class;
	$attr['onclick'] = isset($attr['onclick']) ? $attr['onclick'] : 'close_qm()';

	//Build html
	$html = '<a ' . build_attributes($attr) . '>' . $label . '</a>';

	return $html;
}

function qm_save_button($label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : 'Save and Close';

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'qm-btn-save btn btn-primary float-right ' . $class;
	$attr['onclick'] = isset($attr['onclick']) ? $attr['onclick'] : 'save_form(this)';

	//Build html
	$html = '<a ' . build_attributes($attr) . '>' . $label . '</a>';

	return $html;
}

function qm_save_new_button($label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : 'Save and New';

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'qm-btn-save btn btn-primary float-right ' . $class;
	$attr['onclick'] = isset($attr['onclick']) ? $attr['onclick'] : 'save_form(this, \'save_and_new\')';

	//Build html
	$html = '<a ' . build_attributes($attr) . '>' . $label . '</a>';

	return $html;
}

function qm_save_close_button($label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : 'Save and New';

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'qm-btn-save btn btn-primary float-right ' . $class;
	$attr['onclick'] = isset($attr['onclick']) ? $attr['onclick'] : 'save_form(this, \'save_and_close\')';

	//Build html
	$html = '<a ' . build_attributes($attr) . '>' . $label . '</a>';

	return $html;
}

function qm_tab_buttons() {
	$html = '';
	$html .= qm_save_button();
	$html .= qm_save_new_button();
	$html .= qm_next_button();
	$html .= qm_prev_button();

	return $html;
}

function save_and_new_button($label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : lang('label_save_and_new');

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'btn btn-primary float-right ' . $class;
	$attr['onclick'] = isset($attr['onclick']) ? $attr['onclick'] : 'save_form(this, \'save_and_new\')';

	//Build html
	$html = '<a ' . build_attributes($attr) . '>' . $label . '</a>';

	return $html;
}

function save_button($label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : lang('label_save');

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'btn btn-primary float-right ' . $class;
	$attr['onclick'] = isset($attr['onclick']) ? $attr['onclick'] : 'save_form(this)';

	//Build html
	$html = '<a ' . build_attributes($attr) . '>' . $label . '</a>';

	return $html;
}

function cancel_button($label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : lang('label_cancel');

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'btn btn-light float-right ' . $class;
	$attr['href'] = isset($attr['href']) ? $attr['href'] : url(get_instance()->pageName);

	//Build html
	$html = '<a ' . build_attributes($attr) . '>' . $label . '</a>';

	return $html;
}

function disable_button($identifier = "", $id = "", $label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : lang('label_disable');
	$id = ! empty($id) ? $id : get_instance()->uri->segment(4, 0);
	$identifier = ! empty($identifier) ? $identifier : lang('label_this_item');

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'btn btn-danger float-left ' . $class;
	$attr['href'] = isset($attr['href']) ? $attr['href'] : url(get_instance()->pageName . '/disable/' . $id);
	$attr['data-type'] = isset($attr['data-type']) ? $attr['data-type'] : 'disable';
	$attr['onclick'] = isset($attr['onclick']) ? $attr['onclick'] : 'confirm_action(this, event)';

	//Build html
	$html = '
		<a ' . build_attributes($attr) . '><i class="fa fa-eye-slash"></i> ' . $label . '</a>
	';
	$html .= '
		<span class="confirm_data">
			<span class="header">' . lang('confirm_disable_heading') . '</span>
			<span class="body"><p>' . langs('confirm_disable_body', array('item' => $identifier)) . '</p></span>
			<span class="confirm">' . lang('label_disable') . '</span>
			<span class="cancel">' . lang('label_cancel') . '</span>
		</span>
	';

	return $html;
}

function enable_button($identifier = "", $id = "", $label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : lang('label_enable');
	$id = ! empty($id) ? $id : get_instance()->uri->segment(4, 0);
	$identifier = ! empty($identifier) ? $identifier : lang('label_this_item');

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'btn btn-success float-left ' . $class;
	$attr['href'] = isset($attr['href']) ? $attr['href'] : url(get_instance()->pageName . '/enable/' . $id);
	$attr['data-type'] = isset($attr['data-type']) ? $attr['data-type'] : 'enable';
	$attr['onclick'] = isset($attr['onclick']) ? $attr['onclick'] : 'confirm_action(this, event)';

	//Build html
	$html = '
		<a ' . build_attributes($attr) . '><i class="fa fa-eye"></i> '.$label.'</a>
	';
	$html .= '
		<span class="confirm_data">
			<span class="header">' . lang('confirm_enable_heading') . '</span>
			<span class="body"><p>' . langs('confirm_enable_body', array('item' => $identifier)) . '</p></span>
			<span class="confirm">' . lang('label_enable') . '</span>
			<span class="cancel">' . lang('label_cancel') . '</span>
		</span>
	';

	return $html;
}

function delete_button($identifier = "", $id = "", $label = "", $class = "", $attr = array()) {
	$label = ! empty($label) ? $label : lang('label_delete');
	$id = ! empty($id) ? $id : get_instance()->uri->segment(4, 0);
	$identifier = ! empty($identifier) ? $identifier : lang('label_this_item');

	//Set default attributes
	$attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
	$attr['class'] = isset($attr['class']) ? $attr['class'] : 'btn btn-danger float-left delete ' . $class;
	$attr['href'] = isset($attr['href']) ? $attr['href'] : url(get_instance()->pageName . '/remove/' . $id);
	$attr['data-type'] = isset($attr['data-type']) ? $attr['data-type'] : 'delete';
	$attr['onclick'] = isset($attr['onclick']) ? $attr['onclick'] : 'confirm_action(this, event)';

	//Build html
	$html = '<a '.build_attributes($attr) . '><i class="fa fa-trash-o"></i> '. $label.'</a>';
	$html .= '
		<span class="confirm_data">
			<span class="header">' . lang('confirm_delete_heading') . '</span>
			<span class="body"><p>' . langs('confirm_delete_body', array('item' => $identifier)) . '</p></span>
			<span class="confirm">' . $label . '</span>
			<span class="cancel">' . lang('label_cancel') . '</span>
		</span>
	';

	return $html;
}

function blurb($str, $length = 100) {
	$str = trim(strip_tags($str));
	if (strlen($str) > $length) {
		$cutPos = strpos($str, ' ', $length);
		$str = trim(substr($str, 0, $cutPos));

		//Remove last word
		$cutPos = strrpos($str, ' ');
		$str = trim(substr($str, 0, $cutPos));

		//Add ellipsis
		$str .= '...';
	}

	return $str;
}

/**
 * Comma list
 *
 * Creates a comma separated list from a query object
 *
 * @param object $query
 * @param string $field
 * @param string $separator
 * @param string $function
 *
 * @return string
 */
function comma_list($query, $field = 'name', $separator = ', ', $function = NULL) {
	if ($query->num_rows() > 0) {
		$tempArray = array();
		foreach ($query->result() as $row) {
			if ( ! empty($function) && is_callable($function)) {
				$tempArray[] = call_user_func($function, $row->{$field}, $row, $field);
			}
			else {
				$tempArray[] = $row->{$field};
			}
		}

		return implode($separator, $tempArray);
	}
	else {
		return '';
	}
}

function full_name($group = '') {
	return loginVar('first_name', $group) . ' ' . loginVar('last_name', $group);
}

function image_url($imagePath, $imageSize = FALSE, $noImage = 'no-image.svg') {
	if ( ! empty($imagePath)) {
		$fullPath = site_url() . 'images/' . ($imageSize ? $imageSize . '/' : '') . $imagePath;
	} else {
		$fullPath = site_url() . 'resources/no_image/' . $noImage;
	}

	return $fullPath;
}

function email_subject($str) {
	$ci =& get_instance();
	$siteName = $ci->config->item('site_name');

	$subject = $siteName . ' - ' . lang($str);

	return $subject;
}

function to_id_key_array($query, $valueField, $idField = 'id') {
	$a = array();
	if ($query->num_rows() > 0) {
		foreach ($query->result() as $row) {
			$a[$row->{$idField}] = $row->{$valueField};
		}
	}
	return $a;
}


function get_setting($setting, $default = FALSE) {
	$ci =& get_instance();

	$ci->db->where('setting', $setting);
	$ci->db->limit(1);
	$query = $ci->db->get('sys_settings');

	if ($query->num_rows() > 0) {
		$row = $query->row();

		return $row->value;
	}
	else {
		return $default;
	}
}

function safe_divide($num, $divideBy, $round = FALSE) {
	if ( ! empty($divideBy) && $divideBy > 0) {
		$result = $num / $divideBy;
		return $round !== FALSE ? round($result, $round) : $result;
	}
	else {
		return 0;
	}
}

function attr_clean($str) {
	if (empty($str)) {
		return $str;
	}

	$str = htmlentities($str);
	return clean($str);
}

function dash_url($path) {
	return str_replace('_', '-', url($path));
}

function debug_listing() {
	$ci =& get_instance();

	$ci->{$ci->model}->get_all();
	dd($ci->db->last_query());
}


function back_location() {
	$ci =& get_instance();

	if(!empty($ci->backLocation)) {
		return $ci->backLocation;
	}
	elseif(!empty($ci->view) && $ci->view != 'listing') {
		return $ci->zone;
	}
	else {
		return false;
	}
}

function ajax_error($error) {
	ajax_return([
		'success' 	=> 0,
		'error' 	=> $error
	]);
}

function is_password_strong($password, $length=10, $containsSymbol=true, $containsNumber=true, $containsUppercase=true, $containsLowercase=true) {
	//Check if password is x characters long
	if ($length && strlen($password) < $length) {
		return false;
	}

	//Must contain a lowercase letter
    if ($containsLowercase && !preg_match('/[a-z]/', $password)) {
        return false;
    }

    //Must contain an uppercase letter
	if ($containsUppercase && !preg_match('/[A-Z]/', $password)) {
        return false;
    }

    //Must contain a number
	if ($containsNumber && !preg_match('/[0-9]/', $password)) {
        return false;
    }

    //Must contain a symbol
	if ($containsSymbol && !preg_match('/[^a-zA-Z0-9\s]/', $password)) {
        return false;
    }

    //Password is strong
    return true;
}

function date_now() {
	return date('Y-m-d H:i:s');
}

function curdate() {
	return date('Y-m-d');
}

function bulk_update($table, $data, $idField = 'id') {
	$ci =& get_instance();

	$ids = array();
	$whens = array();
	foreach ($data as $id => $rowData) {
		foreach ($rowData as $field => $value) {
			$whens[$field][] = ' WHEN '.$ci->db->escape_str($idField).' = "' . $ci->db->escape_str($id) . '" THEN "' . $ci->db->escape_str($value) . '" ';
		}
		$ids[] = $ci->db->escape_str($id);
	}

	$sql = 'UPDATE '.$table.' SET ';

	foreach ($whens as $field => $when) {
		$sql .= ' ' . $ci->db->escape_str($field) . ' = CASE ';
		$sql .= implode(' ', $when);
		$sql .= ' ELSE ' . $ci->db->escape_str($field) . ' END,';
	}

	//trim trailing comma
	$sql = trim($sql, ',');

	$sql .= ' WHERE '.$idField.' IN (' . implode(', ', $ids) . ') ';

	$result = $ci->db->query($sql);

	return $result;
}

function extract_values($dataArray, $keyArray) {
	$extractedValues = array();
	foreach ($keyArray as $k) {
		$extractedValues[$k] = isset($dataArray[$k]) ? $dataArray[$k] : '';
	}

	return $extractedValues;
}
function notify_admins($template, $subject, $emailData, $force = FALSE) {
	$ci =& get_instance();

	//Get all admins
	$ci->db->select('
		first_name,
		last_name,
		email
	');
	$ci->db->where('removed', 0);
	$ci->db->where('enabled', 1);
	$ci->db->where('receive_notifications', 1);

	$query = $ci->db->get('usr_admins');

	if ($query->num_rows() > 0) {
		foreach ($query->result() as $row) {
			$emailData['name'] = $row->first_name . ' ' . $row->last_name;
			$emailData['email'] = $row->email;
			send_mail($template, $row->email, $subject, $emailData, "", $force);
		}
	}
}

function ordinal($number) {
	$ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
	if (($number % 100) >= 11 && ($number % 100) <= 13) {
		$abbreviation = $number . 'th';
    }
    else {
		$abbreviation = $number . $ends[$number % 10];
	}

	return $abbreviation;
}

function parse_string($template, $data) {
	$ci =& get_instance();

	$ci->load->library('parser');

	return $ci->parser->parse_string($template, $data);
}

function file_type_list($group='all') {
	$fileTypes['documents'] 		= ['doc', 'docx', 'pdf', 'odt', 'txt', 'rtf'];
	$fileTypes['spreadsheets'] 		= ['xls', 'xlsx', 'ods'];
	$fileTypes['presentations'] 	= ['ppt', 'pptx', 'odd'];
	$fileTypes['archives'] 			= ['zip', 'rar', '7z'];
	$fileTypes['images'] 			= ['jpg', 'jpeg', 'gif', 'png'];

	$returnFileTypes = array();

	if (is_array($group)) {
		
		foreach ($group as $g) {
			if (!empty($fileTypes[$g])) {
				$returnFileTypes = array_merge($returnFileTypes, $fileTypes[$g]);
			}
		}
		
	}
	elseif ($group == 'all') {
		foreach ($fileTypes as $types) {
			$returnFileTypes = array_merge($returnFileTypes, $types);
		}
	}
	else {
		if (!empty($fileTypes[$group])) {
			$returnFileTypes = $fileTypes[$group];
		}
	}

	return $returnFileTypes;
}

function edb_select($fields, $table) {
	$ci =& get_instance();

	if (is_array($fields)) {
		$fields = implode(', ', $fields);
	}

	//prefix table names to fields
	$select = preg_replace('/(\w+)/', $table.'.$1', $fields);

	$ci->db->select($select);
}

function crontext($str) {
	echo '['.date('ymd His').'] '.$str;
	echo is_cli() ? "\n" : "<br/>";
}

function curl_get_contents($path) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $path);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	if (($data = curl_exec($ch)) === false) {
		$result = '';
	} else {
		$result = $data;
	}
	curl_close($ch);

	return $result;
}

function bulk_insert($table, $data) {
	$ci =& get_instance();

	$dataChunked = array_chunk($data, 500);

	$ci->db->trans_start();
	foreach ($dataChunked as $chunk) {
		$ci->db->insert_batch($table, $chunk);
	}
	$ci->db->trans_complete();

	if ($ci->db->trans_status() !== FALSE) {
		return true;
	}
	else {
		return false;
	}
}

function setup_dynamic_fields($df=array()) {
	echo '<script>' . "\n";
	echo 'df = {};' . "\n";

	echo 'df = '.json_encode($df).';' . "\n";
	
	echo '</script>' . "\n";
}

function list_options($table, $orderyBy='name', $orderyDir='asc', $idField='id', $nameField='name') {
	$ci =& get_instance();

	$ci->db->select($table.'.'.$idField.' AS id');
	$ci->db->select($table.'.'.$nameField.' AS name');
	$ci->db->where($table.'.removed', 0);
	$ci->db->order_by($orderyBy, $orderyDir);
	$query = $ci->db->get($table);

	return $query;
}

function form_buttons($row) {
	$ci =& get_instance();

	$identifier = !empty($row) && !empty($row->{$ci->identifierField}) ? $row->{$ci->identifierField} : '';
	$html		= '';

	//Show save button
	$html .= qm_save_button();

	//If adding is enabled show save and new button
	if ($ci->adding) {
		$html .= qm_save_new_button();
	}

	// //Show save and close button
	// $html .= qm_save_close_button();

	//Show cancel button
	$html .= qm_close_button();

	//TODO: DISABLED UNTIL WE CAN FIGURE OUT BETTER LAYOUT
	// //If abling is enabled then show enable/disable button
	// if ($ci->abling && !empty($row)) {
	// 	$html .= $row->enabled ? disable_button($identifier, $row->id) : enable_button($identifier, $row->id);
	// }

	// //If deleting is enabled then show enable/disable button
	// if ($ci->deleting && !empty($row)) {
	// 	$html .= delete_button($identifier, $row->id);
	// }

	return $html;
}

function list_value($table, $idValue, $default='', $idField='id', $nameField='name') {
	$ci =& get_instance();

	$ci->db->select($table.'.'.$nameField.' AS name');
	$ci->db->where($table.'.'.$idField, $idValue);
	$ci->db->where($table.'.removed', 0);
	$ci->db->limit(1);
	$query = $ci->db->get($table);

	if ($query->num_rows() > 0) {
		$row = $query->row();

		return $row->{$nameField};
	}
	else {
		return $default;
	}
}

function seo_fields($row, $includeHeading=true, $class='') {
	$ci =& get_instance();

	$html = '';
	if ($ci->seoFields) {
		$html = '
			'.($includeHeading ? '<h2>SEO Fields</h2>' : '').'
			<div class="row">
				<div class="col-lg-6">
					'.field_input('seo_title', $row, $class).'
				</div>
				<div class="col-lg-6">
					'.field_input('seo_keywords', $row, $class).'
				</div>
				<div class="col-lg-12">
					'.field_textarea('seo_description', $row, $class).'
				</div>
			</div>
		';
	}
	
	return $html;
}

function format_phone($num)
{
	$result = sprintf(
		"%s %s %s",
		substr($num, 0, 3),
		substr($num, 3, 3),
		substr($num, 6)
	);
	return $result;
}

function force_multidimensional_array($array) {
    // Check if the array is associative or not multidimensional
    $isMultidimensional = array_filter($array, 'is_array');

    // If it's not multidimensional, wrap it inside another array
    if (empty($isMultidimensional)) {
        return [$array];
    }

    // If it is multidimensional, return as-is
    return $array;
}

function linked_table_options($linkTable, $mainTable, $linkField, $linkTableName='name', $orderBy='name', $orderByDir='ASC', $linkTableID='id' ) {
	$ci =& get_instance();

	$linkTableName = $linkTableName == 'name' ? $linkTable.'.'.$linkTableID : $linkTableName;
	$linkTableID = $linkTableID == 'id' ? $linkTable.'.'.$linkTableID : $linkTableID;

	$ci->db->distinct();
	$ci->db->select($linkTableID.' AS id', false);
	$ci->db->select($linkTableName.' AS name', false);
	$ci->db->join($mainTable, $mainTable.'.'.$linkField.' = '.$linkTableID);
	$ci->db->where($mainTable.'.removed', 0);
	$ci->db->order_by($orderBy, $orderByDir);
	$query = $ci->db->get($linkTable);

	return $query;
}

function yes_no_options($yesName='Yes', $noName='No', $yesID=1, $noID=0) {

	$options = array(
		['id' => $yesID, 'name' => $yesName],
		['id' => $noID, 'name' => $noName]
	);

	return $options;

}

function download_button($filePath='') {
	$html = '-';
	if (!empty($filePath)) {
		$html = '
			<a onclick="event.stopPropagation();" href="'.download_link($filePath).'" class="btn btn-secondary btn-sm" target="_blank">
				<i class="fa fa-download"></i>&nbsp;&nbsp;Download
			</a>
		';
	}

	return $html;
}

function array_deep_merge(array $array1, array $array2): array {
    foreach ($array2 as $key => $value) {
        if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
            // If both values are arrays, merge them recursively
            $array1[$key] = array_deep_merge($array1[$key], $value);
        } else {
            // Otherwise, overwrite with the value from the second array
            $array1[$key] = $value;
        }
    }
    return $array1;
}

function is_submodule($parentPageName, $field=null) {
	$ci =& get_instance();
	$submodules = $ci->session->submodules;
	if (!empty($submodules[$parentPageName])) {
		if (!$field || $submodules[$parentPageName]->field == $field) {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}

function currency($amount, $symbol='R', $decimal=2) {
	$currencyString = $symbol.' '.number_format(safe_divide($amount, 100, $decimal), $decimal, '.', '&nbsp;');

	return $currencyString;
}

function submodule_parent_id($pageName) {
	$ci =& get_instance();
	$submodules = $ci->session->submodules;
	if (!empty($submodules[$pageName])) {
		return $submodules[$pageName]->id;
	}
	else {
		return 0;
	}
}

function submodule_data($pageName) {
	$ci =& get_instance();
	$submodules = $ci->session->submodules;
	if (!empty($submodules[$pageName])) {
		return $submodules[$pageName];
	}
	else {
		return false;
	}
}

function get_ecms_filters($pageName) {
	$ci =& get_instance();

	$ecmsFilters    = $ci->session->ecmsFilters;
	$filters  		= !empty($ecmsFilters[$pageName]) ? $ecmsFilters[$pageName] : [];

	return $filters;
}

function set_ecms_filters($pageName, $filterOptions) {
	$ci =& get_instance();

	$ecmsFilters    		= $ci->session->ecmsFilters;
	$ecmsFilters[$pageName]	= $filterOptions;

	$ci->session->ecmsFilters = $ecmsFilters;
}

function unset_ecms_filters($pageName) {
	$ci =& get_instance();

	$ecmsFilters = $ci->session->ecmsFilters;
	if (isset($ecmsFilters[$pageName])) {
		unset($ecmsFilters[$pageName]);
	}

	$ci->session->ecmsFilters = $ecmsFilters;
}

function get_multi_select_values($field, $id=0) {
	if (!$id) {
		return [];
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