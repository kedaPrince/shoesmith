<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CRUD_Controller {

    public $pageName    = 'dashboard';
    public $group       = 'Dashboard';
    public $view        = '';
    public $model       = 'Model_dashboard';
    public $sorting     = array();
    public $singular    = 'dashboard';
    public $plural      = 'dashboard';
    public $adding      = false;
    public $hideSubNav  = true;

    public function __construct() {
        parent::__construct();

        // Check if user is logged in as either agency OR recruiter
        $login_data = $this->session->userdata('login');
        $is_agency_logged_in = !empty($login_data['agency']);
        $is_recruiter_logged_in = !empty($login_data['recruiter']);
        
        if (!$is_agency_logged_in && !$is_recruiter_logged_in) {
            // Not logged in at all - redirect to login
            redirect('login');
        }

        $this->load->model($this->folder.'/'.$this->model);
        $this->zone = array(
            'title' => lang('label_dashboard'),
            'url'   => url($this->pageName)
        );
    }

    public function index() {
        $this->setup_breadcrumbs();
        load_custom_page($this->folder.'/'.$this->pageName.'/view_dashboard');
    }
}