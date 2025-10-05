<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Jobs_listings extends CRUD_Controller
{
    public $pageName = 'jobs_listings';
    public $group = 'agency';
    public $folder = 'agency'; // Set before parent::__construct()
    public $model = 'Model_jobs';
    public $singular = 'Job Listing';
    public $plural = 'Jobs Listings';
    public $identifierField = 'name';
    public $quickManage = true;
    public $sluggify = true;
    public $adding = true;
    public $allowEdit = true;
    public $sorting = array('name' => 'ASC');
     public $quickManageSize = 4;

    public function __construct()
    {
        $this->folder = 'agency'; // ✅ MUST be before parent::__construct()
        parent::__construct();

        // ... rest of your setup
        $this->load->model($this->folder . '/' . $this->model);
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
            'field' => 'agencies.name' // ✅ Specify the exact field from the join
        ),
        'employment_type' => array(
            'label' => lang('label_job_type'),
            'sort' => true,
        ),
    );

        $this->listActions = array(
            'edit' => array(
                'label' => lang('label_edit'),
                'url' => url($this->pageName . '/edit/{id}'),
                'icon' => 'fa-edit',
                'class' => 'edit-row',
                'function' => function ($str, $row) {
                    return (!$this->allowEdit) ? false : $str;
                },
            ),
            'enable' => array(
                'label' => lang('label_enable'),
                'url' => url($this->pageName . '/enable/{id}'),
                'icon' => 'fa-eye',
                'class' => 'enable-row btn-enable',
                'function' => function ($str, $row) {
                    return (!$this->allowEdit || $row->enabled) ? false : $str;
                },
            ),
            'disable' => array(
                'label' => lang('label_disable'),
                'url' => url($this->pageName . '/disable/{id}'),
                'icon' => 'fa-eye-slash',
                'class' => 'disable-row btn-disable',
                'function' => function ($str, $row) {
                    return (!$this->allowEdit || !$row->enabled) ? false : $str;
                },
            ),
            'delete' => array(
                'label' => lang('label_delete'),
                'url' => url($this->pageName . '/remove/{id}'),
                'icon' => 'fa-trash-o',
                'class' => 'delete-row btn-delete',
                'function' => function ($str, $row) {
                    return (!$this->allowEdit) ? false : $str;
                },
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
    public function build_params($extra = array(), $group = 'main')
{
    $params = parent::build_params($extra, $group);
    
    // Set default values for checkbox fields when they're not posted
    $checkboxFields = ['is_remote'];
    
    foreach ($checkboxFields as $field) {
        if (!isset($params[$field])) {
            $params[$field] = 0; // Default to unchecked (0)
        }
    }
    
    return $params;
}
public function create()
{
    // Debug: Check what's being posted
    log_message('debug', 'POST data: ' . print_r($this->input->post(), true));
    
    // Continue with normal create process...
    parent::create();
}
public function index()
{
    // Debug: Check if query works
    try {
        $query = $this->{$this->model}->get_all();
        log_message('debug', 'Jobs query executed successfully. Rows: ' . $query->num_rows());
        
        if ($query->num_rows() > 0) {
            $first_row = $query->row();
            log_message('debug', 'First job - ID: ' . $first_row->id . ', Enabled: ' . $first_row->enabled . ', Name: ' . $first_row->name);
            
            // Debug: Check available fields
            log_message('debug', 'Available fields in row: ' . implode(', ', array_keys((array)$first_row)));
        } else {
            log_message('debug', 'No jobs found in database');
        }
    } catch (Exception $e) {
        log_message('error', 'Jobs query failed: ' . $e->getMessage());
    }
    
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

public function quick_manage_extra($id, $row): array
{
    $submodules = $this->session->submodules;
    $agency_id = !empty($submodules['job_listings']) ? $submodules['job_listings']->id : null;

    return [
        'agency_id' => $agency_id,
        'agency_options' => $this->{$this->model}->get_agency_options(), // Returns Query object
        'industry_options' => $this->{$this->model}->get_industry_options(),
        'skill_options' => $this->{$this->model}->get_skill_options(),
        'qualification_options' => $this->{$this->model}->get_qualification_options(),
        'skills' => $id ? $this->{$this->model}->get_job_skills((int)$id) : [],
        'qualifications' => $id ? $this->{$this->model}->get_job_qualifications((int)$id) : [],
    ];
}

    public function is_unique_reference($reference)
    {
        $id = $this->input->post('id');
        $this->form_validation->set_message('is_unique_reference', lang('ref_exists'));
        return $this->{$this->model}->is_unique_reference($reference, $id);
    }
}