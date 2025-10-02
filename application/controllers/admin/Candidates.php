<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Candidates extends Admin_Controller {

    public $pageName = 'candidates';
    public $group = 'Candidates';
    public $view = '';
    public $model = 'Model_candidates';
    public $sorting = array('name' => 'ASC');
    public $singular = 'candidate';
    public $plural = 'candidates';
    public $quickManage = true;
    public $identifierField = 'name';
    public $hideSubNav = true;
    public $seoFields = false;
    public $quickManageSize = 2;
    public $sluggify = true;

     public function __construct()
    {
        parent::__construct();

        // Access check
        if (!function_exists('getLoggedInUserTypeMenu')) {
            $ci = &get_instance();
            $ci->load->helper('profile_helper');
        }
        if (getLoggedInUserTypeMenu() === 'staff') {
            redir('dashboard');
        }

        $this->setup_listing();
        $this->setup_fields();
        $this->load->model($this->folder . '/' . $this->model);
        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }


        public function index()
{
    $this->breadcrumbs = array(
        array(
            'title' => lang($this->pageName . '_heading'),
            'url'   => redir($this->pageName, true),
        ),
    );
    $this->view = 'listing';
    $this->load->view($this->folder . '/view_header');
    $this->load->view('cms/crud/view_list', array(
        'heading' => lang($this->pageName . '_heading'),
        'noRows'  => lang($this->pageName . '_no_rows'),
    ));
    $this->load->view($this->folder . '/view_footer');
}

}