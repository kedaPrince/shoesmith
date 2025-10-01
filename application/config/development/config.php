<?php
/**
 *
 * @package     ECMS
 * @Author      7diverse
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

$config['base_url']     = 'http://localhost/shoesmith/';

$config['sys_email']    = 'happy@shoesmith.co.za';

$config['version']    = rand(0,99999);

$config['mail_config']  =  array(
    'protocol' => 'smtp',
    'smtp_host' => 'smtp.mailtrap.io',
    'smtp_port' => 2525,
    'smtp_user' => 'e91e6467c0e0c6',
    'smtp_pass' => '861d101f573ec7',
    'crlf' => "\r\n",
    'newline' => "\r\n",
    'mailtype' => 'html'
);