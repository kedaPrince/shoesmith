<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logger
{
    /**
     * Logs all related info of a user pertaining to his current action
     * @param  string $action           User current action    
     * @param  array $additionalInfo    Any additional info you want saved
     * @param  string $group    Login Group
     * @param  string $userID    User ID
     */
    public static function log($action, array $additionalInfo = NULL, $group='', $userID='')
    {
        $ci =& get_instance();

	    //Get current user group
	    $loginGroups = $ci->config->item('login_groups');
	    if (empty($group) && !empty($loginGroups[$ci->uri->segment(1,'')])) {
		    $group = $ci->uri->segment(1,'');
	    }

	    //Set id to log
	    $userID = !empty($userID) ? $userID : loginID($group, false);

	    //Set parameters
        $params = [
            'user_id'       => $userID,
	        'login_group'   => $group,
            'session_id'    => session_id(),
            'ip_address'    => $ci->input->ip_address(),
	        'user_agent'    => $ci->input->user_agent(),
            'current_page'  => uri_segment(1),
            'current_url'   => current_url(),
            'action'        => $action,
            'additional'    => $additionalInfo !== NULL ? json_encode($additionalInfo) : NULL,
            'created_at'    => date("Y-m-d H:i:s"),
        ];

        $ci->db->insert('sys_logger', $params);
    }

}