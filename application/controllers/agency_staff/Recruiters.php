<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recruiters extends CRUD_Controller
{
    public $pageName = 'recruiters';
    public $group = 'Recruiters';
    public $folder = 'agency_staff';
    public $view = '';
    public $model = 'Model_recruiters';
    public $sorting = array('name' => 'ASC');
    public $singular = 'Recruiter';
    public $plural = 'Recruiters';
    public $quickManage = true;
    public $identifierField = 'name';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper(['profile_helper', 'agency_access_helper']);

        $login_data = $this->session->userdata('login');
        $is_agency_logged_in = !empty($login_data['agency']);
        
        if (!$is_agency_logged_in) {
            redirect('login');
        }

        if (!agency_staff_can_access_page('recruiters')) {
            if ($this->input->is_ajax_request()) {
                ajax_return([
                    'success' => false,
                    'error' => 'Access denied to recruiters section'
                ]);
            } else {
                $this->session->set_flashdata('error', 'Access denied to recruiters section');
                redirect('dashboard');
            }
            exit;
        }

        $this->load->model($this->folder . '/' . $this->model);
        $this->setup_listing();
        $this->setup_fields();
        
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
                'url' => redir($this->pageName, true)
            ),
        );
        $this->view = 'listing';

        $this->load->view($this->folder . '/' . 'view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading' => lang($this->pageName . '_heading'),
            'noRows' => lang($this->pageName . '_no_rows'),
        ));
        $this->load->view($this->folder . '/' . 'view_footer');
    }

    private function setup_listing(): void
    {
        $this->listFields = array(
            'name' => array(
                'label' => lang('label_name'),
                'sort' => true,
            ),
            'email' => array(
                'label' => lang('label_email'),
                'sort' => true,
            ),
            'phone' => array(
                'label' => lang('label_phone'),
                'sort' => true,
            ),
            'agency_name' => array(
                'label' => lang('label_agency'),
                'sort' => true,
            ),
        );

        $this->listActions = array(
            'view' => array(
                'label' => lang('label_view'),
                'url' => redir($this->pageName . '/view/{id}', true),
                'icon' => 'fa-eye',
                'class' => 'view-row',
            ),
            'edit' => array(
                'label' => lang('label_edit'),
                'url' => redir($this->pageName . '/edit/{id}', true),
                'icon' => 'fa-edit',
                'class' => 'edit-row',
            ),
        );
    }

    private function setup_fields(): void
    {
        $this->formFields = array(
            'main' => array(
                'name' => 'trim|required',
                'email' => 'trim|required|valid_email',
                'phone' => 'trim|required',
                'agency_id' => 'trim|required|numeric',
            ),
        );
    }
}