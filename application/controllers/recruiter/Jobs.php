<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Jobs extends CRUD_Controller
{
    public $pageName = 'jobs';
    public $group = 'Jobs';
    public $folder = 'recruiter';
    public $model = 'model_jobs';
    public $singular = 'Job';
    public $plural = 'Jobs';
    public $identifierField = 'name';
    public $quickManage = false; // Disable quick manage since recruiters can't edit
    public $sluggify = true;
    public $adding = false; // Remove Add Job button
    public $allowEdit = false; // Disable editing
    public $sorting = array('name' => 'ASC');
    public $quickManageSize = 4;

    public function __construct()
    {
        parent::__construct();

        $this->folder = 'recruiter';

        // Allow only recruiters
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
            'industry_name' => array(
                'label' => lang('label_industry'),
                'sort' => true,
            ),
            'agency_name' => array(
                'label' => lang('label_agency'),
                'sort' => true,
            ),
        );

        $this->listActions = array(
            'view' => array(
                'label'     => lang('label_view'),
                'url'       => url($this->pageName . '/view/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'view-row btn-info',
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

    // Block create and update methods for recruiters
    public function create()
    {
        show_404(); // Block access to create
    }

    public function update($id)
    {
        show_404(); // Block access to update
    }

    public function edit($id)
    {
        show_404(); // Block access to edit
    }

    public function enable($id)
    {
        show_404(); // Block access to enable
    }

    public function disable($id)
    {
        show_404(); // Block access to disable
    }

    /**
     * View job details - Only method recruiters can access
     */
    public function view($id)
    {
        $user_agency_id = $this->get_user_agency_id();
        
        // Get the job with agency filtering
        $this->db->where('mod_jobs.id', $id);
        if ($user_agency_id) {
            $this->db->where('mod_jobs.agency_id', $user_agency_id);
        }
        
        $job = $this->{$this->model}->get_by_id($id);
        
        if (!$job) {
            show_404();
        }

        // Load additional data
        $data['job'] = $job;
        $data['skills'] = $this->{$this->model}->get_job_skills((int)$id);
        $data['qualifications'] = $this->{$this->model}->get_job_qualifications((int)$id);
        $data['skill_options'] = $this->{$this->model}->get_skill_options();
        $data['qualification_options'] = $this->{$this->model}->get_qualification_options();
        
        // Set breadcrumbs
        $this->breadcrumbs = array(
            array(
                'title' => lang($this->pageName . '_heading'),
                'url' => redir($this->pageName, true),
            ),
            array(
                'title' => $job->name,
                'url' => '#',
            ),
        );

        // Load the view
        $this->load->view($this->folder . '/view_header');
        $this->load->view('recruiter/jobs/view_job', $data);
        $this->load->view($this->folder . '/view_footer');
    }
}