<?php
/**
 *
 * @package     CMS
 * @Author      7Diverse
 * @Link        https://www.7diverse.co.za
 * @email       info@7diverse.co.za
 *
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your CodeIgniter root. Typically this will be your base URL,
| WITH a trailing slash:
|
|	http://example.com/
|
| WARNING: You MUST set this value!
|
| If it is not set, then CodeIgniter will try guess the protocol and path
| your installation, but due to security concerns the hostname will be set
| to $_SERVER['SERVER_ADDR'] if available, or localhost otherwise.
| The auto-detection mechanism exists only for convenience during
| development and MUST NOT be used in production!
|
| If you need to allow multiple domains, remember that this file is still
| a PHP script and you can easily do that on your own.
|
*/

$config['base_url']     = 'https://dev4.7diverse.co.za/cms5/';

$config['sys_email']    = 'prince@7diverse.co.za';

$config['version']    = date('Y-m-d-H');

$config['mail_config']  =  array(
	'protocol' 	=> 'smtp',
	'smtp_host' => 'smtp.7diverse.agency',
	'smtp_port' => 587,
	'smtp_user' => 'dev@7diverse.agency',
	'smtp_pass' => 'Man!ac4Cognac',
	'crlf'      => "\r\n",
	'newline'   => "\r\n",
	'mailtype'  => 'html'
);