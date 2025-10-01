<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Anomalies {
	
	public static function log($description="", $sql="", $class="", $method="", $current_url="")
	{
		$ci =& get_instance();
		!empty($class)          || $class = $ci->router->fetch_class();
		!empty($method)         || $method = $ci->router->fetch_method();
		!empty($current_url)    || $current_url = current_url();

		$ci->db->set('description', $description);
		$ci->db->set('sql_query', $sql);
		$ci->db->set('class_name', $class);
		$ci->db->set('method_name', $method);
		$ci->db->set('current_url', $current_url);
		$ci->db->set('created_at', date("Y-m-d H:i:s"));

		$ci->db->insert('sys_anomalies');
	}
}