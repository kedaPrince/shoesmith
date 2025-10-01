<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Front_Controller extends MY_Controller {
	public $page = '';
	public $view = '';

	public function __construct() {
		parent::__construct();

		$this->load->helper('front_helper');

		$this->folder = empty($this->folder) ? 'front' : $this->folder;
		$this->load->model('../core/Front_Model');

		if (empty($this->session->loginToken)) {
			$this->session->set_userdata('loginToken', random_string('alnum', '32'));
		}

		//Default image sizes
		$this->imageSizes = array(
			'thumbs' => array(
				'width' => 150,
				'height' => 135
			),
			'big_thumbs' => array(
				'width' => 201,
				'height' => 201
			)
		);
	}
}
