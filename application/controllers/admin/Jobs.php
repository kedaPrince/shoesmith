<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Jobs extends CRUD_Controller
{
    public $pageName = 'jobs';
    public $group = 'Jobs';
    public $view = '';
    public $model = 'Model_jobs';
    public $sorting = array('name' => 'ASC');
    public $singular = 'job';
    public $plural = 'jobs';
    public $quickManage = true;
    public $identifierField = 'name';
    public $hideSubNav = true;
    public $seoFields = false;
    public $quickManageSize = 4;
    public $sluggify = true;
    
    // ADD THIS LINE to specify custom ajax manage path
    public $ajaxManageView = 'admin/jobs/ajax-manage';

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

    private function setup_listing()
    {
        $this->listFields = array(
            'name' => array(
                'label' => lang('label_title'),
                'sort' => true,
            ),
            'reference_number' => array(
                'label' => lang('label_reference_number'),
                'sort' => true,
            ),
            'agency_name' => array(
                'label' => lang('label_agency'),
                'sort' => true,
            ),
            'industry_name' => array(
                'label' => lang('label_industry'),
                'sort' => true,
            ),
            'employment_type' => array(
                'label' => lang('label_job_type'),
                'sort' => true,
            ),
            'enabled' => array(
                'label' => lang('label_status'),
                'sort' => true,
                'function' => function($value, $row) {
                    return $value ? '<span class="badge badge-success">Enabled</span>' : '<span class="badge badge-danger">Disabled</span>';
                }
            ),
        );

        $this->listActions = array(
            'edit' => array(
                'label'     => lang('label_edit'),
                'url'       => url($this->pageName . '/edit/{id}'),
                'icon'      => 'fa-edit',
                'class'     => 'edit-row',
            ),
            'enable' => array(
                'label'     => lang('label_enable'),
                'url'       => url($this->pageName . '/enable/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'enable-row btn-enable',
                'function'  => (function ($str, $row) {
                    return ($row->enabled) ? false : $str;
                }),
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => url($this->pageName . '/disable/{id}'),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => (function ($str, $row) {
                    return (!$row->enabled) ? false : $str;
                }),
            ),
            'delete' => array(
                'label'     => lang('label_delete'),
                'url'       => url($this->pageName . '/remove/{id}'),
                'icon'      => 'fa-trash-o',
                'class'     => 'delete-row btn-delete',
            ),
        );

        $this->filters = array(
            'general' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array(
                    'mod_jobs.name',
                    'mod_jobs.reference_number',
                ),
            ),
            'agency' => array(
                'label' => lang('label_agency'),
                'type' => 'dropdown',
                'field' => 'mod_jobs.agency_id',
                'options' => $this->get_agency_filter_options(),
            ),
            'industry' => array(
                'label' => lang('label_industry'),
                'type' => 'dropdown',
                'field' => 'mod_jobs.industry_id',
                'options' => $this->get_industry_filter_options(),
            ),
        );
    }

    private function get_agency_filter_options()
    {
        $this->load->model('admin/Model_jobs');
        $agencies = $this->Model_jobs->get_agency_options();
        $options = ['' => 'All Agencies'];
        foreach ($agencies->result() as $agency) {
            $options[$agency->id] = $agency->name;
        }
        return $options;
    }

    private function get_industry_filter_options()
    {
        $this->load->model('admin/Model_jobs');
        $industries = $this->Model_jobs->get_industry_options();
        $options = ['' => 'All Industries'];
        foreach ($industries->result() as $industry) {
            $options[$industry->id] = $industry->name;
        }
        return $options;
    }

    public function setup_fields()
    {
        $this->formFields = array(
            'main' => array(
                'name' => 'trim|required|strip_tags',
                'reference_number' => 'trim|required|strip_tags|callback_is_unique_reference',
                'description' => 'trim',
                'project_overview' => 'trim',
                'department' => 'trim|strip_tags',
                'agency_id' => 'trim|required|numeric',
                'industry_id' => 'trim|numeric',
                'employment_type' => 'trim|required',
                'salary_min' => 'trim|numeric',
                'salary_max' => 'trim|numeric',
                'salary_currency' => 'trim|strip_tags',
                'pay_rate' => 'trim|strip_tags',
                'is_remote' => 'trim|numeric',
                'roster' => 'trim|strip_tags',
                'accommodation' => 'trim|strip_tags',
                'transport' => 'trim|strip_tags',
                'application_email' => 'trim|valid_email',
                'application_url' => 'trim|valid_url',
                'closing_date' => 'trim',
            ),
            'multi_selects' => array(
                'skills' => array(
                    'validation' => 'trim',
                    'pivot_table' => 'pivot_job_skills',
                    'main_field' => 'job_id',
                    'link_field' => 'skill_id',
                ),
                'qualifications' => array(
                    'validation' => 'trim',
                    'pivot_table' => 'pivot_job_qualifications',
                    'main_field' => 'job_id',
                    'link_field' => 'qualification_id',
                ),
            ),
        );
    }

    public function index()
    {
        $this->breadcrumbs = array(
            array(
                'title' => lang($this->pageName . '_heading'),
                'url' => redir($this->pageName, true),
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

    public function quick_manage_extra($id, $row): array
    {
        return array(
            'agency_options' => $this->{$this->model}->get_agency_options(),
            'industry_options' => $this->{$this->model}->get_industry_options(),
            'skill_options' => $this->{$this->model}->get_skill_options(),
            'qualification_options' => $this->{$this->model}->get_qualification_options(),
            'skills' => $id ? $this->{$this->model}->get_job_skills((int)$id) : [],
            'qualifications' => $id ? $this->{$this->model}->get_job_qualifications((int)$id) : [],
        );
    }

    public function is_unique_reference($reference)
    {
        $id = $this->input->post('id');
        $this->form_validation->set_message('is_unique_reference', lang('ref_exists'));
        return $this->{$this->model}->is_unique_reference($reference, $id);
    }
}