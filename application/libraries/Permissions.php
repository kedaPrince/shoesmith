<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Permissions {

	private static $restrictions = array();
	
	public static function restrict($level, $url="", $pageName="", $pageView="")
	{
		$ci =& get_instance();
		!empty($pageName)       || $pageName = $ci->pageName;

		$user       = loginData();
		$loginGroup = $ci->config->item('login_groups');

		!empty($url)            || $url = $loginGroup[$user['group']]['default_url'];

		//Check if user has sufficient rights to access this page, if not then redirect
		if (!self::has($level, $pageName, $pageView)) {
			$message = '
				'.$user['first_name'].' '.$user['last_name'].' ('.$user['group'].':'.$user['id'].')
				attempted to access the following page:
				'.$pageName.' (Level: '.$level.')
			';
			Anomalies::log(trim($message));
			flash_notification(lang('unauthorized_description'), 'warning');
			redir($url);
		}
	}

	public static function has($level, $pageName="", $pageView="")
	{
		$ci =& get_instance();
		!empty($pageName)       || $pageName = $ci->pageName;

		//Append view to pageName
		$pageName .= $pageView;

		if (is_super_user()) {
			//Super user is allowed to access everything
			return true;
		}
		else {
			//Check if use has sufficient rights to access the page
			$restrictions = self::get_restrictions();

			if (!empty($restrictions[$pageName])) {
				//Check if the user has a high enough access level for this page
				if ($restrictions[$pageName] >= $level) {
					return true;
				}
			}
		}

		return false;
	}

	public static function get_restrictions() {
		$ci =& get_instance();

		$user   = loginData();

		$restrictions = self::$restrictions;
		if (empty($restrictions)) {
			$query = $ci->db->query('
				SELECT sys_permisions.page_name, sys_permisions.page_view, pivot_user_permission.permission_level
				FROM sys_permisions
				INNER JOIN pivot_user_permission ON pivot_user_permission.login_group = "'.$ci->db->escape_str($user['group']).'"
					AND pivot_user_permission.user_id = '.$ci->db->escape_str($user['id']).'
					AND pivot_user_permission.permission_id = sys_permisions.id
			');

			if ($query->num_rows() > 0) {
				foreach ($query->result() as $row) {
					self::$restrictions[$row->page_name.$row->page_view] = $row->permission_level;
				}
			}
			else {
				Anomalies::log('Unable to get restrictions', $ci->db->last_query());
			}
		}

		return self::$restrictions;
	}
}