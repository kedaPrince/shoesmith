<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Jobs_listings extends CRUD_Controller{
    public $pageName = 'jobs_listings';
    public $group = 'agency';
    public $folder = 'agency';
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

   public function __construct(){
        $this->folder = 'agency';
        parent::__construct();

        // Check if user is logged in as either agency OR recruiter
        $login_data = $this->session->userdata('login');
        $is_agency_logged_in = !empty($login_data['agency']);
        $is_recruiter_logged_in = !empty($login_data['recruiter']);
        
        if (!$is_agency_logged_in && !$is_recruiter_logged_in) {
            // Not logged in at all - redirect to login
            redirect('login');
        }

        $this->load->model($this->folder . '/' . $this->model);
        
        // Apply agency filter immediately after loading model
        $user_agency_id = $this->get_user_agency_id();
        if (!empty($user_agency_id)) {
            $this->db->where('mod_jobs.agency_id', $user_agency_id);
            log_message('debug', 'Applied global agency filter in constructor: ' . $user_agency_id);
        }
        
        $this->setup_listing();
        $this->setup_fields();

        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }

    private function setup_listing(){
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
            'field' => 'agencies.name'
        ),
        // Add this to ensure agency_id is available in row objects
        'agency_id' => array(
            'label' => 'Agency ID',
            'sort' => true,
            'hidden' => true // Hide from listing but make it available in row data
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

    public function setup_fields(){
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

    /**
     * Override the get_all method to filter by agency
     */
    /**
     * Override the get_all method to filter by agency
     */

    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null){
        $user_agency_id = $this->get_user_agency_id();
        
        if (!empty($user_agency_id)) {
            log_message('debug', 'Filtering jobs by agency_id: ' . $user_agency_id);
            $this->db->where('mod_jobs.agency_id', $user_agency_id);
        } else {
            log_message('debug', 'No agency_id found for filtering jobs');
        }
        
        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

    public function build_params($extra = array(), $group = 'main'){
        $params = parent::build_params($extra, $group);
        
        $checkboxFields = ['is_remote'];
        
        foreach ($checkboxFields as $field) {
            if (!isset($params[$field])) {
                $params[$field] = 0;
            }
        }
        
        return $params;
    }

    public function create(){
        log_message('debug', 'POST data: ' . print_r($this->input->post(), true));
        
        // Auto-set agency_id if not provided
        if (!$this->input->post('agency_id')) {
            $user_agency_id = $this->get_user_agency_id();
            if (!empty($user_agency_id)) {
                $_POST['agency_id'] = $user_agency_id;
                log_message('debug', 'Auto-setting agency_id to: ' . $user_agency_id);
            }
        }
        
        parent::create();
    }

    /**
     * Override the index method to ensure agency filtering
     */
    public function index(){
        $user_agency_id = $this->get_user_agency_id();
        log_message('debug', 'Current user agency_id: ' . $user_agency_id);
        
        // Apply agency filter directly to the model
        if (!empty($user_agency_id)) {
            $this->db->where('mod_jobs.agency_id', $user_agency_id);
            log_message('debug', 'Applied agency filter in index method: ' . $user_agency_id);
        }
        
        try {
            $query = $this->{$this->model}->get_all();
            log_message('debug', 'Jobs query executed successfully. Rows: ' . $query->num_rows());
            
            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                    log_message('debug', 'Job - ID: ' . $row->id . ', Agency ID: ' . $row->agency_id . ', Name: ' . $row->name);
                }
            } else {
                log_message('debug', 'No jobs found in database for agency: ' . $user_agency_id);
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

   
    public function quick_manage_extra($id, $row): array{
    $submodules = $this->session->submodules;
    $agency_id = !empty($submodules['job_listings']) ? $submodules['job_listings']->id : null;

    // Get the logged-in user's agency ID (works for both agency staff and recruiters)
    $user_agency_id = $this->get_user_agency_id();
    
    log_message('debug', 'User Agency ID: ' . $user_agency_id);
    log_message('debug', 'Submodule Agency ID: ' . $agency_id);
    
    $agency_options = $this->{$this->model}->get_agency_options($user_agency_id);
    
    log_message('debug', 'Agency Options Count: ' . $agency_options->num_rows());
    if ($agency_options->num_rows() > 0) {
        foreach ($agency_options->result() as $agency) {
            log_message('debug', 'Agency Option - ID: ' . $agency->id . ', Name: ' . $agency->name);
        }
    }
    
    // FIX: Safe way to get current agency_id from row
    $current_agency_id = $user_agency_id; // Default to user's agency
    
    // FIX: Safe way to get current agency_id from row
    $current_agency_id = $user_agency_id; // Default to user's agency

    if (!empty($row) && is_object($row)) {
        // More robust check for agency_id
        if (isset($row->agency_id) && !empty($row->agency_id)) {
            $current_agency_id = $row->agency_id;
            log_message('debug', 'Found agency_id in row object: ' . $current_agency_id);
        } else {
            // Try to get agency_id from the database if we have an ID
            if (!empty($id)) {
                $job_data = $this->{$this->model}->get_by_id($id);
                if ($job_data && isset($job_data->agency_id) && !empty($job_data->agency_id)) {
                    $current_agency_id = $job_data->agency_id;
                    log_message('debug', 'Found agency_id from database lookup: ' . $current_agency_id);
                } else {
                    log_message('debug', 'No agency_id found in database for job ID: ' . $id);
                }
            } else {
                log_message('debug', 'No ID provided, using user agency ID: ' . $user_agency_id);
            }
        }
    } else {
        log_message('debug', 'No row object provided, using user agency ID: ' . $user_agency_id);
    }
    
    return [
        'agency_id' => $agency_id,
        'user_agency_id' => $user_agency_id,
        'current_agency_id' => $current_agency_id, // Use this in the view
        'user_agencies' => $user_agency_id, // For compatibility with view
        'agency_options' => $agency_options,
        'industry_options' => $this->{$this->model}->get_industry_options(),
        'skill_options' => $this->{$this->model}->get_skill_options(),
        'qualification_options' => $this->{$this->model}->get_qualification_options(),
        'skills' => $id ? $this->{$this->model}->get_job_skills((int)$id) : [],
        'qualifications' => $id ? $this->{$this->model}->get_job_qualifications((int)$id) : [],
    ];
    }

    /**
     * Get the logged-in user's agency ID - Works for both agency staff and recruiters
     */
    /**
     * Get the logged-in user's agency ID - Works for both agency staff and recruiters
     */
    private function get_user_agency_id(){
        // Get the login data from session
        $login_data = $this->session->userdata('login');
        
        log_message('debug', 'Login data: ' . print_r($login_data, true));
        
        // Check for agency staff login
        if (!empty($login_data['agency'])) {
            $agency_user = $login_data['agency'];
            
            if (!empty($agency_user['agency_id'])) {
                $agency_id = $agency_user['agency_id'];
                log_message('debug', 'Found agency_id in login[agency] data: ' . $agency_id);
                return $agency_id;
            } elseif (!empty($agency_user['id'])) {
                // Sometimes the agency ID might be stored in the user ID field
                $agency_id = $agency_user['id'];
                log_message('debug', 'Found agency_id in login[agency] id field: ' . $agency_id);
                return $agency_id;
            }
        }
        
        // Check for recruiter login
        if (!empty($login_data['recruiter'])) {
            $recruiter_user = $login_data['recruiter'];
            
            if (!empty($recruiter_user['agency_id'])) {
                $agency_id = $recruiter_user['agency_id'];
                log_message('debug', 'Found agency_id in login[recruiter] data: ' . $agency_id);
                return $agency_id;
            }
        }
        
        // Fallback: check if we have direct agency_id in session
        $agency_id = $this->session->userdata('agency_id');
        if (!empty($agency_id)) {
            log_message('debug', 'Found agency_id directly in session: ' . $agency_id);
            return $agency_id;
        }
        
        log_message('debug', 'No agency_id found in session');
        return null;
    }

    public function is_unique_reference($reference){
        $id = $this->input->post('id');
        $this->form_validation->set_message('is_unique_reference', lang('ref_exists'));
        return $this->{$this->model}->is_unique_reference($reference, $id);
    }

    /**
     * Override update method to ensure agency_id is set
     */
    public function update($id){
        // Ensure agency_id is set for updates
        if (!$this->input->post('agency_id')) {
            $user_agency_id = $this->get_user_agency_id();
            if (!empty($user_agency_id)) {
                $_POST['agency_id'] = $user_agency_id;
                log_message('debug', 'Auto-setting agency_id for update: ' . $user_agency_id);
            }
        }
        
        parent::update($id);
    }
}