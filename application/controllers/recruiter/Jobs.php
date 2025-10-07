<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Jobs extends CRUD_Controller
{
    public $pageName = 'jobs';
    public $group = 'Jobs';
    public $folder = 'recruiter';
    public $model = 'model_jobs';  // CHANGE: Lowercase to match file name for reliable loading
    public $singular = 'Job';
    public $plural = 'Jobs';
    public $identifierField = 'name';
    public $quickManage = true;
    public $sluggify = true;
    public $adding = true;
    public $allowEdit = true;
    public $sorting = array('name' => 'ASC');
    public $quickManageSize = 4;

    public function __construct()
    {
        parent::__construct();  // CHANGE: Parent first for proper inheritance

        $this->folder = 'recruiter';  // CHANGE: Moved inside construct after parent; set once

        // Allow only recruiters — session uses 'recruiters' (plural)
        $login_data = $this->session->userdata('login');
        if (empty($login_data['recruiter'])) {
            redirect('recruiter/dashboard');
        }

        $this->load->model($this->folder . '/' . $this->model);

        // Apply agency filter globally
        $user_agency_id = $this->get_user_agency_id();
        if ($user_agency_id) {
            $this->db->where('mod_jobs.agency_id', $user_agency_id);
        }

        $this->setup_listing();
        $this->setup_fields();

        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }

    private function setup_listing()
    {
        $this->listFields = array(
            'name' => array('label' => lang('label_title'), 'sort' => true),
            'reference_number' => array('label' => lang('label_reference_number'), 'sort' => true),
            'employment_type' => array('label' => lang('label_job_type'), 'sort' => true),
            'industry_name' => array(  // ← use alias name directly
            'label' => lang('label_industry'),
            'sort' => true,
        ),
             'agency_name' => array(    // ← use alias name directly
            'label' => lang('label_agency'),
            'sort' => true,
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
                'field' => array('mod_jobs.name', 'mod_jobs.reference_number'),
            ),
        );
    }

    public function setup_fields()
    {
        $this->formFields = array(
            'main' => array(
                'name' => 'trim|required|strip_tags',
                'reference_number' => 'trim|required|strip_tags|callback_is_unique_reference',
                'description' => 'trim',
                'department' => 'trim|strip_tags',
                'agency_id' => 'trim|required|numeric',
                'industry_id' => 'trim|numeric',
                'employment_type' => 'trim|required',
                'salary_min' => 'trim|numeric',
                'salary_max' => 'trim|numeric',
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
    $this->load->view($this->folder . '/view_header');
    $this->load->view('cms/crud/view_list', array(
        'heading' => lang($this->pageName . '_heading'),
        'noRows' => lang($this->pageName . '_no_rows'),
    ));
    $this->load->view($this->folder . '/view_footer');
}


    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
    {
        $user_agency_id = $this->get_user_agency_id();
        if ($user_agency_id) {
            $this->db->where('mod_jobs.agency_id', $user_agency_id);
        }
        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

    public function quick_manage_extra($id, $row): array
    {
        $user_agency_id = $this->get_user_agency_id();
        return [
            'agency_id' => $user_agency_id,
            'user_agency_id' => $user_agency_id,
            'current_agency_id' => $user_agency_id,
            'agency_options' => $this->{$this->model}->get_agency_options($user_agency_id),
            'industry_options' => $this->{$this->model}->get_industry_options(),
            'skill_options' => $this->{$this->model}->get_skill_options(),
            'qualification_options' => $this->{$this->model}->get_qualification_options(),
            'skills' => $id ? $this->{$this->model}->get_job_skills((int)$id) : [],
            'qualifications' => $id ? $this->{$this->model}->get_job_qualifications((int)$id) : [],
        ];
    }

    private function get_user_agency_id()
    {
        $login = $this->session->userdata('login');
        if (!empty($login['recruiters']['agency_id'])) {
            return (int) $login['recruiters']['agency_id'];
        }
        return null;
    }

    public function is_unique_reference($reference)
    {
        $id = $this->input->post('id');
        $this->form_validation->set_message('is_unique_reference', lang('ref_exists'));
        return $this->{$this->model}->is_unique_reference($reference, $id);
    }

    public function create()
    {
        if (!$this->input->post('agency_id')) {
            $agency_id = $this->get_user_agency_id();
            if ($agency_id) {
                $_POST['agency_id'] = $agency_id;
            }
        }
        parent::create();
    }

    public function update($id)
    {
        if (!$this->input->post('agency_id')) {
            $agency_id = $this->get_user_agency_id();
            if ($agency_id) {
                $_POST['agency_id'] = $agency_id;
            }
        }
        parent::update($id);
    }
}