<?php
defined('BASEPATH') or exit('No direct script access allowed');

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

        $this->load->model($this->folder . '/' . $this->model);
        $this->zone = array(
            'title' => lang('label_dashboard'),
            'url' => redir($this->pageName, true)
        );
    }

    public function index() {
        $this->breadcrumbs = array(
            array(
                'title' => lang('label_dashboard'),
                'url' => redir($this->pageName, true)
            )
        );
        $this->view = 'listing';

        $this->load->view($this->folder . '/' . 'view_header');
        $this->load->view($this->folder . '/' . $this->pageName . '/view_dashboard', array(
            'heading' => lang('label_dashboard'),
        ));
        $this->load->view($this->folder . '/' . 'view_footer');
    }
}
