<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Home extends Front_Controller {
	public $pageName 	= 'home';
	public $model 		= 'Model_front';
	public $folder 		= 'front';

	public function __construct() {
		parent::__construct();
		$this->load->model($this->folder . '/' . $this->model);
	}

	public function index() {
		$page_meta['seo_title'] 		= "Site Title";
		$page_meta['seo_keywords'] 		= "Site Keywords";
		$page_meta['seo_description'] 	= "Site Description";

		$resources = [
			'<link rel="stylesheet" type="text/css" href="'.site_url().'resources/front/css/home/style.min.css?version='.$this->config->item('version').'">'
		];

		$this->load->view($this->folder . '/view_header', array(
			'page_meta' => $page_meta,
			'resources' => $resources
		));
		$this->load->view($this->folder . '/home/view_home', array());
		$this->load->view($this->folder . '/view_footer');
	}
}
