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

   public function __construct()
   {

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
            $this->db->where('agency_id', $user_agency_id);
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
            'name' => array(
                'label' => 'Job Title',
                'sort' => true,
            ),
            'reference_number' => array(
                'label' => lang('label_reference_number'),
                'sort' => true,
            ),
            'candidate_count' => array(
            'label' => 'Candidates',
            'sort' => true,
            'function' => function($str, $row) {
                // Get the controller instance inside the closure
                $ci = &get_instance();
                $agency_id = $ci->get_user_agency_id();
                
                if (!$agency_id) {
                    return '<span class="text-muted">0</span>';
                }
            
            // Count candidates for this job
            $ci->db->select('COUNT(DISTINCT cja.candidate_id) as count');
            $ci->db->from('candidate_job_assignments cja');
            $ci->db->join('candidates c', 'c.id = cja.candidate_id');
            $ci->db->join('candidate_agencies ca', 'ca.candidate_id = c.id');
            $ci->db->where('cja.job_id', $row->id);
            $ci->db->where('cja.removed', 0);
            $ci->db->where('ca.agency_id', $agency_id);
            $ci->db->where('c.removed', 0);
            
            $result = $ci->db->get()->row();
            $actual_count = $result ? $result->count : 0;
            
                    // Return formatted count with link - USE UUID
                    $url = site_url('agency/candidates_list/index/' . $row->uuid);
                    if ($actual_count > 0) {
                        return '<a href="' . $url . '" class="btn btn-sm btn-info" title="View ' . $actual_count . ' Candidates">' . $actual_count . '</a>';
                    } else {
                        return '<span class="text-muted">0</span>';
                    }
                }
            ),
                    'employment_type' => array(
                        'label' => lang('label_job_type'),
                        'sort' => true,
                    ),
                );
        
                $this->listActions = array(
                'edit' => array(
                    'label' => lang('label_edit'),
                    'url' => url($this->pageName . '/edit/{id}'), // Change back to {id}
                    'icon' => 'fa-edit',
                    'class' => 'edit-row',
                    'function' => function ($str, $row) {
                        return (!$this->allowEdit) ? false : $str;
                    },
                ),
                'view_candidates' => array(
                    'label' => 'View Candidates',
                    'url' => site_url('agency/candidates_list/index/{uuid}'), // Keep UUID here if needed
                    'icon' => 'fa-users',
                    'class' => 'btn-info',
                ),
                'enable' => array(
                    'label' => lang('label_enable'),
                    'url' => url($this->pageName . '/enable/{id}'), // Change back to {id}
                    'icon' => 'fa-eye',
                    'class' => 'enable-row btn-enable',
                    'function' => function ($str, $row) {
                        return (!$this->allowEdit || $row->enabled) ? false : $str;
                    },
                ),
                'disable' => array(
                    'label' => lang('label_disable'),
                    'url' => url($this->pageName . '/disable/{id}'), // Change back to {id}
                    'icon' => 'fa-eye-slash',
                    'class' => 'disable-row btn-disable',
                    'function' => function ($str, $row) {
                        return (!$this->allowEdit || !$row->enabled) ? false : $str;
                    },
                ),
                'delete' => array(
                    'label' => lang('label_delete'),
                    'url' => url($this->pageName . '/remove/{id}'), // Change back to {id}
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
                'description' => 'trim|strip_tags', // Add strip_tags here
                'project_overview' => 'trim|strip_tags', // Add strip_tags here
                'department' => 'trim|strip_tags',
                 'location' => 'trim|strip_tags',
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
                'skills' => 'trim|strip_tags', // Add strip_tags for skills
                'qualifications' => 'trim|strip_tags', // Add strip_tags for qualifications
            ),
            'multi_selects' => array(
                'skills' => array(
                    'validation' => 'trim|strip_tags', // Add strip_tags here too
                    'pivot_table' => 'pivot_job_skills',
                    'main_field' => 'job_id',
                    'link_field' => 'skill_id',
                ),
                'qualifications' => array(
                    'validation' => 'trim|strip_tags', // Add strip_tags here too
                    'pivot_table' => 'pivot_job_qualifications',
                    'main_field' => 'job_id',
                    'link_field' => 'qualification_id',
                ),
            ),
        );
    }

    public function get_list_fields() {
        $fields = parent::get_list_fields();
        
        // Remove candidate_count from the field selection since it's calculated
        if (isset($fields['candidate_count'])) {
            unset($fields['candidate_count']);
        }
        
        return $fields;
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

   
    public function generate_reference() {
    // Allow GET requests without CSRF
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Skip CSRF for GET requests
    }
    
    try {
        $reference = $this->{$this->model}->generate_reference_number();
        ajax_return(array(
            'success' => true,
            'reference' => $reference
        ));
    } catch (Exception $e) {
        error_log('Error generating reference: ' . $e->getMessage());
        ajax_return(array(
            'success' => false,
            'error' => 'Failed to generate reference number'
        ));
    }
    }

   
    public function create(){
        // PRE-PROCESS DATA TO REMOVE HTML TAGS
        $this->pre_process_job_data();

        if (!$this->input->post('uuid')) {
            $_POST['uuid'] = $this->generate_uuid();
        }
        
        // SIMPLIFIED SOLUTION: Handle industry_id safely without extra DB connection
        $industry_id = $this->input->post('industry_id');        
        if (!empty($industry_id) && is_numeric($industry_id)) {
            $industry_id = (int)$industry_id;
            $_POST['industry_id'] = $industry_id;
        } else {
            $_POST['industry_id'] = null;
        }
        
        // Auto-set agency_id if not provided
        if (!$this->input->post('agency_id')) {
            $user_agency_id = $this->get_user_agency_id();
            if (!empty($user_agency_id)) {
                $_POST['agency_id'] = $user_agency_id;
            }
        }
        
        parent::create();
    }


    /**
     * Override remove method to handle UUIDs
     */
    public function remove($identifier = null, $whereField = 'id') {        
        // If identifier is a UUID, convert it
        if (is_string($identifier) && strlen($identifier) == 36 && strpos($identifier, '-') !== false) {
            $id = $this->{$this->model}->get_job_id_from_uuid($identifier);
            
            if (!$id) {
                flash_notification('Job not found', 'error');
                redir($this->pageName);
                return;
            }
            
            // Call parent with the converted ID
            parent::remove($id, 'id');
        } else {
            // Call parent normally
            parent::remove($identifier, $whereField);
        }
    }

    /**
     * Override enable method to handle UUIDs
     */
    public function enable($identifier = null, $whereField = 'id') {
        // If identifier is a UUID, convert it
        if (is_string($identifier) && strlen($identifier) == 36 && strpos($identifier, '-') !== false) {
            $id = $this->{$this->model}->get_job_id_from_uuid($identifier);
            
            if (!$id) {
                flash_notification('Job not found', 'error');
                redir($this->pageName);
                return;
            }
            
            parent::enable($id, 'id');
        } else {
            parent::enable($identifier, $whereField);
        }
    }

    /**
     * Override disable method to handle UUIDs
     */
    public function disable($identifier = null, $whereField = 'id') {
        // If identifier is a UUID, convert it
        if (is_string($identifier) && strlen($identifier) == 36 && strpos($identifier, '-') !== false) {
            $id = $this->{$this->model}->get_job_id_from_uuid($identifier);
            
            if (!$id) {
                flash_notification('Job not found', 'error');
                redir($this->pageName);
                return;
            }
            
            parent::disable($id, 'id');
        } else {
            parent::disable($identifier, $whereField);
        }
    }

    /**
     * Override edit method (usually has different signature)
     */
    public function edit($identifier = null) {
        if (!$this->enforce_job_access($id)) {
            return;
        }
        // Check if it's a UUID
        if (is_string($identifier) && strlen($identifier) == 36 && strpos($identifier, '-') !== false) {
            $id = $this->{$this->model}->get_job_id_from_uuid($identifier);
            
            if (!$id) {
                flash_notification('Job not found', 'error');
                redir($this->pageName);
                return;
            }

            // After getting the ID, ensure we have proper data
            if ($id && !$this->enforce_job_access($id)) {
                return;
            }
            
            // Load the job data fresh to ensure all fields are included
            if ($id) {
                $this->data['job'] = $this->{$this->model}->get_job($id);
            }
            
            // Call parent edit with ID
            parent::edit($id);
        } else {
            parent::edit($identifier);
        }
    }


    private function generate_uuid() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Override the index method to ensure agency filtering
     */
    public function index(){
        //dd('here');
        $user_agency_id = $this->get_user_agency_id();
        
        // Apply agency filter directly to the model
        if (!empty($user_agency_id)) {
            $this->db->where('agency_id', $user_agency_id);
        }
        
        try {
            $query = $this->{$this->model}->get_all();
            
            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                }
            } else {
            }
        } catch (Exception $e) {
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
        // DEBUG: Check what's in the row
        if (!empty($row)) {
            if (property_exists($row, 'employment_type')) {
            }
        }
        $submodules = $this->session->submodules;
        $agency_id = !empty($submodules['job_listings']) ? $submodules['job_listings']->id : null;

        // Get the logged-in user's agency ID (works for both agency staff and recruiters)
        $user_agency_id = $this->get_user_agency_id();
        $agency_options = $this->{$this->model}->get_agency_options($user_agency_id);
        
        // FIX: Better data extraction from row
        $current_agency_id = $user_agency_id; // Default to user's agency
        if (!empty($id) && !empty($row) && !property_exists($row, 'employment_type')) {
            // Fetch fresh data from database including employment_type
            $job_data = $this->{$this->model}->get_job($id);
            if ($job_data) {
                $row->employment_type = $job_data->employment_type;
            }
        }
        if (!empty($row) && is_object($row)) {
            // More robust check for agency_id
            if (isset($row->agency_id) && !empty($row->agency_id)) {
                $current_agency_id = $row->agency_id;
            } else {
                // Try to get agency_id from the database if we have an ID
                if (!empty($id)) {
                    $job_data = $this->{$this->model}->get_by_id($id);
                    if ($job_data && isset($job_data->agency_id) && !empty($job_data->agency_id)) {
                        $current_agency_id = $job_data->agency_id;
                    }
                }
            }
        }
        
        $generated_reference = '';
        if (empty($row) || empty($row->reference_number)) {
            try {
                $generated_reference = $this->{$this->model}->generate_reference_number();
            } catch (Exception $e) {
                // Fallback if generation fails
                $generated_reference = 'JOB-' . date('Y') . '-' . rand(1000, 9999);
                error_log('Error generating reference: ' . $e->getMessage());
            }
        }
        
        return [
            'agency_id' => $agency_id,
            'user_agency_id' => $user_agency_id,
            'current_agency_id' => $current_agency_id,
            'user_agencies' => $user_agency_id,
            'agency_options' => $agency_options,
            'industry_options' => $this->{$this->model}->get_industry_options(),
            'skill_options' => $this->{$this->model}->get_skill_options(),
            'qualification_options' => $this->{$this->model}->get_qualification_options(),
            'skills' => $id ? $this->{$this->model}->get_job_skills((int)$id) : [],
            'qualifications' => $id ? $this->{$this->model}->get_job_qualifications((int)$id) : [],
            'row' => $row,
            'generated_reference' => $generated_reference
        ];
    }

    /**
     * Get the logged-in user's agency ID - Works for both agency staff and recruiters
     */
    private function get_user_agency_id(){
        // Get the login data from session
        $login_data = $this->session->userdata('login');
        
        
        // Check for agency staff login
        if (!empty($login_data['agency'])) {
            $agency_user = $login_data['agency'];
            
            if (!empty($agency_user['agency_id'])) {
                $agency_id = $agency_user['agency_id'];
                return $agency_id;
            } elseif (!empty($agency_user['id'])) {
                // Sometimes the agency ID might be stored in the user ID field
                $agency_id = $agency_user['id'];
                return $agency_id;
            }
        }
        
        // Check for recruiter login
        if (!empty($login_data['recruiter'])) {
            $recruiter_user = $login_data['recruiter'];
            
            if (!empty($recruiter_user['agency_id'])) {
                $agency_id = $recruiter_user['agency_id'];
                return $agency_id;
            }
        }
        
        // Fallback: check if we have direct agency_id in session
        $agency_id = $this->session->userdata('agency_id');
        if (!empty($agency_id)) {
            return $agency_id;
        }
        
        return null;
    }

    public function is_unique_reference($reference){
        $id = $this->input->post('id');
        $this->form_validation->set_message('is_unique_reference', lang('ref_exists'));
        return $this->{$this->model}->is_unique_reference($reference, $id);
    }

    /**
     * Override update method to track changes and send notifications
     */

    public function update($id)
    {
         if (!$this->enforce_job_access($id)) {
                return;
            }
            if (!is_ajax()) {
                // Validate CSRF for non-AJAX requests
                $csrf_name = $this->security->get_csrf_token_name();
                $csrf_token = $this->input->post($csrf_name);
                
                if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
                    flash_notification('Invalid CSRF token. Please try again.', 'error');
                    redir($this->pageName);
                    return;
                }
            }
                $this->pre_process_job_data();
                // Get original job data before update for change tracking - FIXED METHOD CALL
                $original_job = $this->{$this->model}->get_by_id($id);
                if (!$original_job) {
                    return parent::update($id);
                }

                // Track changed fields
                $changed_fields = $this->track_changed_fields($original_job);
        
                // SIMPLIFIED SOLUTION: Handle industry_id safely without extra DB connection
                $industry_id = $this->input->post('industry_id');
                
                // Safe industry_id handling - just ensure it's valid numeric or null
                if (!empty($industry_id) && is_numeric($industry_id)) {
                    $industry_id = (int)$industry_id;
                    // Let the database foreign key handle validation
                    $_POST['industry_id'] = $industry_id;
                } else {
                    // No industry_id or invalid, set to null
                    $_POST['industry_id'] = null;
                }
                
                // Ensure agency_id is set for updates
                if (!$this->input->post('agency_id')) {
                    $user_agency_id = $this->get_user_agency_id();
                    if (!empty($user_agency_id)) {
                        $_POST['agency_id'] = $user_agency_id;
                    }
                }

                
                // Call parent update
                $result = parent::update($id);
                
                // Send update notification if fields were changed
                if (!empty($changed_fields)) {
                    $this->send_update_notification($id, $original_job->agency_id, $changed_fields);
                } else {
                }
                
                return $result;
    }

    /**
     * Track which fields were changed during update
     */
    private function track_changed_fields($original_job) {
        $changed_fields = [];
        $post_data = $this->input->post();
        
        foreach ($post_data as $field => $new_value) {
            // Skip non-job fields and ID field
            if ($field === 'id' || !property_exists($original_job, $field)) {
                continue;
            }
            
            $original_value = $original_job->$field;
            
            // Handle different data types for comparison
            if ($field === 'is_remote') {
                // Handle checkbox values
                $original_bool = !empty($original_value) ? '1' : '0';
                $new_bool = !empty($new_value) ? '1' : '0';
                
                if ($original_bool !== $new_bool) {
                    $changed_fields[] = $field;
                }
            } elseif (is_numeric($original_value) && is_numeric($new_value)) {
                // Handle numeric comparisons
                if ((float)$original_value != (float)$new_value) {
                    $changed_fields[] = $field;
                }
            } else {
                // Handle string comparisons
                if ((string)$original_value !== (string)$new_value) {
                    $changed_fields[] = $field;
                }
            }
        }
        
        return $changed_fields;
    }

    /**
     * Send update notification to recruiters
     */
    private function send_update_notification($job_id, $agency_id, $changed_fields) {
        
        // Load recruiter notifications model (this is the key fix!)
        $this->load->model('recruiter/Model_notifications');
        
        // Get sender ID (current user)
        $sender_id = $this->get_user_agency_id();
     
        // Create update notification for ALL recruiters
        $result = $this->Model_notifications->create_job_notification(
            $job_id, 
            $agency_id, 
            $sender_id, 
            'job_updated', 
            $changed_fields
        );
                
        if ($result) {

        } else {
        }
        
        return $result;
    }
   
    // In Jobs_listings.php - update the create_success_extra method:
    public function create_success_extra($job_id) {
        
        // Load recruiter notifications model (this is the key fix!)
        $this->load->model('recruiter/Model_notifications');
        
        // Get agency_id and sender_id
        $agency_id = $this->input->post('agency_id');
        $sender_id = $this->get_user_agency_id();
        // Use the correct agency ID - prefer the one from the user session
        $effective_agency_id = !empty($sender_id) ? $sender_id : $agency_id;
        
        // Double-check the job was created with the correct agency
        $job_check = $this->db->get_where('mod_jobs', ['id' => $job_id])->row();
        if ($job_check) {
        }
        
        // Create notifications for ALL recruiters
        $result = $this->Model_notifications->create_job_notification($job_id, $effective_agency_id, $sender_id);
        
        
        if ($result) {
        } else {
        }
        
    }


    /**
     * Pre-process job data to remove HTML tags and clean input
     */
    private function pre_process_job_data() 
    {
        $text_fields = [
            'name', 'reference_number', 'description', 'project_overview', 
            'department', 'location', 'pay_rate', 'roster', 'accommodation', 'transport', // ADD 'location' here
            'application_email', 'application_url', 'skills', 'qualifications'
        ];
        
        foreach ($text_fields as $field) {
            if ($this->input->post($field)) {
                $clean_value = strip_tags($this->input->post($field));
                $_POST[$field] = $clean_value;
            }
        }
        
        // Also handle employment_type to ensure it's valid
        $employment_types = ['full-time', 'part-time', 'contract', 'internship', 'temporary'];
        $current_type = $this->input->post('employment_type');
        if (!in_array($current_type, $employment_types)) {
            $_POST['employment_type'] = 'full-time'; // default value
        }
    }

    private function enforce_job_access($job_id) 
    {
        $user_agency_id = $this->get_user_agency_id();
        
        if (!$user_agency_id) {
            show_404();
            return false;
        }
        
        
        // Check if job belongs to agency
        $this->db->select('1')
                ->from('mod_jobs')
                ->where('id', $job_id)
                ->where('agency_id', $user_agency_id)
                ->where('removed', 0);
        
        $result = $this->db->get()->row();
        
        if (!$result) {

            show_404(); 
            return false;
        }

        return true;
    }

    public function view($id) {
        if (!$this->enforce_job_access($id)) {
            return; 
        }
    
    }


    public function delete($id) {
        if (!$this->enforce_job_access($id)) {
            return;
        }
    }

}