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
            $this->db->where('agency_id', $user_agency_id);
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
            $count = $row->candidate_count;
            
            // Return formatted count with link
            $url = site_url('agency/candidates_list/index/' . $row->id);
            if ($count > 0) {
                return '<a href="' . $url . '" class="btn btn-sm btn-info" title="View ' . $count . ' Candidates">' . $count . '</a>';
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
                'url' => url($this->pageName . '/edit/{id}'),
                'icon' => 'fa-edit',
                'class' => 'edit-row',
                'function' => function ($str, $row) {
                    return (!$this->allowEdit) ? false : $str;
                },
            ),
           'view_candidates' => array(
            'label' => 'View Candidates',
            'url' => site_url('agency/candidates_list/index/{id}'), // Changed to use new controller
            'icon' => 'fa-users',
            'class' => 'btn-info',
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
            'description' => 'trim|strip_tags', // Add strip_tags here
            'project_overview' => 'trim|strip_tags', // Add strip_tags here
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
/**
 * Override field selection to exclude calculated fields
 */
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
/**
 * Generate reference number for job (called from AJAX)
 */
public function generate_reference() {
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
    log_message('debug', '=== JOB CREATION START ===');
    log_message('debug', 'POST data: ' . print_r($this->input->post(), true));
    
    // PRE-PROCESS DATA TO REMOVE HTML TAGS
    $this->pre_process_job_data();
    
    // SIMPLIFIED SOLUTION: Handle industry_id safely without extra DB connection
    $industry_id = $this->input->post('industry_id');
    log_message('debug', 'Raw Industry ID from POST: ' . $industry_id);
    
    // Safe industry_id handling - just ensure it's valid numeric or null
    if (!empty($industry_id) && is_numeric($industry_id)) {
        $industry_id = (int)$industry_id;
        // Let the database foreign key handle validation
        $_POST['industry_id'] = $industry_id;
        log_message('debug', 'Setting industry_id to: ' . $industry_id);
    } else {
        // No industry_id or invalid, set to null
        $_POST['industry_id'] = null;
        log_message('debug', 'No valid industry_id provided, setting to null');
    }
    
    // Auto-set agency_id if not provided
    if (!$this->input->post('agency_id')) {
        $user_agency_id = $this->get_user_agency_id();
        if (!empty($user_agency_id)) {
            $_POST['agency_id'] = $user_agency_id;
            log_message('debug', 'Auto-setting agency_id to: ' . $user_agency_id);
        }
    }
    
    // Final validation before parent::create()
    log_message('debug', 'Final industry_id before create: ' . $_POST['industry_id']);
    log_message('debug', 'Final agency_id before create: ' . $_POST['agency_id']);
    
    log_message('debug', '=== JOB CREATION END ===');
    
    parent::create();
}

    /**
     * Override the index method to ensure agency filtering
     */
    public function index(){
       // dd('here');
        $user_agency_id = $this->get_user_agency_id();
        log_message('debug', 'Current user agency_id: ' . $user_agency_id);
        
        // Apply agency filter directly to the model
        if (!empty($user_agency_id)) {
            $this->db->where('agency_id', $user_agency_id);
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

   
public function quick_manage_extra($id, $row): array
{
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
    
    // ✅ FIX: Better data extraction from row
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
        
        // ✅ DEBUG: Log employment type and industry data
        log_message('debug', 'Job Row Data for Quick Manage:');
        log_message('debug', ' - Employment Type: ' . ($row->employment_type ?? 'NOT SET'));
        log_message('debug', ' - Industry ID: ' . ($row->industry_id ?? 'NOT SET'));
        log_message('debug', ' - Agency ID: ' . ($row->agency_id ?? 'NOT SET'));
        log_message('debug', ' - Job Name: ' . ($row->name ?? 'NOT SET'));
    } else {
        log_message('debug', 'No row object provided, using user agency ID: ' . $user_agency_id);
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
        // ✅ FIX: Ensure row data is passed correctly
        'row' => $row
    ];
}

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
     * Override update method to track changes and send notifications
     */
    /**
 * Override update method to track changes and send notifications
 */
    public function update($id){
        log_message('debug', '=== JOB UPDATE START ===');
        log_message('debug', 'Updating job ID: ' . $id);
        log_message('debug', 'POST data: ' . print_r($this->input->post(), true));
        // PRE-PROCESS DATA TO REMOVE HTML TAGS
        $this->pre_process_job_data();
        
          // Get original job data before update for change tracking - FIXED METHOD CALL
    $original_job = $this->{$this->model}->get_by_id($id);
    if (!$original_job) {
        log_message('error', 'Original job not found for ID: ' . $id);
        return parent::update($id);
    }
    
    log_message('debug', 'Original job data - Name: ' . $original_job->name . ', Agency: ' . $original_job->agency_id);
        
        // Track changed fields
        $changed_fields = $this->track_changed_fields($original_job);
        
        // SIMPLIFIED SOLUTION: Handle industry_id safely without extra DB connection
        $industry_id = $this->input->post('industry_id');
        log_message('debug', 'Raw Industry ID from POST: ' . $industry_id);
        
        // Safe industry_id handling - just ensure it's valid numeric or null
        if (!empty($industry_id) && is_numeric($industry_id)) {
            $industry_id = (int)$industry_id;
            // Let the database foreign key handle validation
            $_POST['industry_id'] = $industry_id;
            log_message('debug', 'Setting industry_id to: ' . $industry_id);
        } else {
            // No industry_id or invalid, set to null
            $_POST['industry_id'] = null;
            log_message('debug', 'No valid industry_id provided, setting to null');
        }
        
        // Ensure agency_id is set for updates
        if (!$this->input->post('agency_id')) {
            $user_agency_id = $this->get_user_agency_id();
            if (!empty($user_agency_id)) {
                $_POST['agency_id'] = $user_agency_id;
                log_message('debug', 'Auto-setting agency_id for update: ' . $user_agency_id);
            }
        }
        
        // Final validation before parent::update()
        log_message('debug', 'Final industry_id before update: ' . $_POST['industry_id']);
        log_message('debug', 'Final agency_id before update: ' . $_POST['agency_id']);
        log_message('debug', 'Changed fields detected: ' . implode(', ', $changed_fields));
        
        // Call parent update
        $result = parent::update($id);
        
        // Send update notification if fields were changed
        if (!empty($changed_fields)) {
            $this->send_update_notification($id, $original_job->agency_id, $changed_fields);
        } else {
            log_message('debug', 'No fields changed, skipping update notification');
        }
        
        log_message('debug', '=== JOB UPDATE END ===');
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
                    log_message('debug', 'Field changed: ' . $field . ' from ' . $original_bool . ' to ' . $new_bool);
                }
            } elseif (is_numeric($original_value) && is_numeric($new_value)) {
                // Handle numeric comparisons
                if ((float)$original_value != (float)$new_value) {
                    $changed_fields[] = $field;
                    log_message('debug', 'Field changed: ' . $field . ' from ' . $original_value . ' to ' . $new_value);
                }
            } else {
                // Handle string comparisons
                if ((string)$original_value !== (string)$new_value) {
                    $changed_fields[] = $field;
                    log_message('debug', 'Field changed: ' . $field . ' from "' . $original_value . '" to "' . $new_value . '"');
                }
            }
        }
        
        return $changed_fields;
    }

    /**
     * Send update notification to recruiters
     */
    private function send_update_notification($job_id, $agency_id, $changed_fields) {
        log_message('debug', '=== SENDING UPDATE NOTIFICATION START ===');
        
        // Load recruiter notifications model (this is the key fix!)
        $this->load->model('recruiter/Model_notifications');
        
        // Get sender ID (current user)
        $sender_id = $this->get_user_agency_id();
        
        log_message('debug', 'Update Notification Details:');
        log_message('debug', ' - Job ID: ' . $job_id);
        log_message('debug', ' - Agency ID: ' . $agency_id);
        log_message('debug', ' - Sender ID: ' . $sender_id);
        log_message('debug', ' - Changed Fields: ' . implode(', ', $changed_fields));
        
        // Create update notification for ALL recruiters
        $result = $this->Model_notifications->create_job_notification(
            $job_id, 
            $agency_id, 
            $sender_id, 
            'job_updated', 
            $changed_fields
        );
        
        log_message('debug', 'Update notification creation result: ' . ($result ? 'SUCCESS' : 'FAILED'));
        
        if ($result) {
            log_message('debug', '✅ Update notification sent successfully for job ID: ' . $job_id);
            log_message('debug', '✅ Recruiters will see these updated fields: ' . implode(', ', $changed_fields));
        } else {
            log_message('error', '❌ Failed to send update notification for job ID: ' . $job_id);
        }
        
        log_message('debug', '=== SENDING UPDATE NOTIFICATION END ===');
        return $result;
    }

   
   
  
   
    // In Jobs_listings.php - update the create_success_extra method:
    public function create_success_extra($job_id) {
        log_message('debug', '=== JOB CREATION - NOTIFICATION PROCESS START ===');
        
        // Load recruiter notifications model (this is the key fix!)
        $this->load->model('recruiter/Model_notifications');
        
        // Get agency_id and sender_id
        $agency_id = $this->input->post('agency_id');
        $sender_id = $this->get_user_agency_id();
        
        log_message('debug', 'Job Creation Details:');
        log_message('debug', ' - Job ID: ' . $job_id);
        log_message('debug', ' - POST agency_id: ' . $agency_id);
        log_message('debug', ' - User agency_id: ' . $sender_id);
        
        // Use the correct agency ID - prefer the one from the user session
        $effective_agency_id = !empty($sender_id) ? $sender_id : $agency_id;
        log_message('debug', ' - Effective agency_id for notifications: ' . $effective_agency_id);
        
        // Double-check the job was created with the correct agency
        $job_check = $this->db->get_where('mod_jobs', ['id' => $job_id])->row();
        if ($job_check) {
            log_message('debug', ' - Job agency_id in database: ' . $job_check->agency_id);
        }
        
        // Create notifications for ALL recruiters
        $result = $this->Model_notifications->create_job_notification($job_id, $effective_agency_id, $sender_id);
        
        log_message('debug', 'Notification creation result: ' . ($result ? 'SUCCESS' : 'FAILED'));
        
        if ($result) {
            log_message('debug', '✅ New job notification sent successfully for job ID: ' . $job_id);
        } else {
            log_message('error', '❌ Failed to send new job notification for job ID: ' . $job_id);
        }
        
        log_message('debug', '=== JOB CREATION - NOTIFICATION PROCESS END ===');
    }

/**
 * Debug method to check candidate counts for each job
 */
public function debug_candidates_count() {
    log_message('debug', '=== DEBUG CANDIDATES COUNT START ===');
    
    // Get user agency ID for filtering
    $user_agency_id = $this->get_user_agency_id();
    
    // Build query to get jobs with candidate counts
    $this->db->select('mj.id, mj.name, mj.reference_number, COUNT(cj.id) as candidate_count');
    $this->db->from('mod_jobs mj');
    $this->db->join('candidate_jobs cj', 'mj.id = cj.job_id', 'left');
    
    // Apply agency filter if user has one
    if (!empty($user_agency_id)) {
        $this->db->where('mj.agency_id', $user_agency_id);
        log_message('debug', 'Applied agency filter: ' . $user_agency_id);
    }
    
    $this->db->group_by('mj.id');
    $this->db->order_by('mj.name', 'ASC');
    
    $query = $this->db->get();
    $results = $query->result();
    
    // Display results in browser
    echo "<h1>Candidate Counts by Job</h1>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th>Job ID</th>";
    echo "<th>Job Name</th>";
    echo "<th>Reference Number</th>";
    echo "<th>Candidate Count</th>";
    echo "</tr>";
    
    $total_candidates = 0;
    
    if (!empty($results)) {
        foreach ($results as $job) {
            echo "<tr>";
            echo "<td>{$job->id}</td>";
            echo "<td>{$job->name}</td>";
            echo "<td>{$job->reference_number}</td>";
            echo "<td style='text-align: center;'><strong>{$job->candidate_count}</strong></td>";
            echo "</tr>";
            
            $total_candidates += $job->candidate_count;
            
            // Also log for debugging
            log_message('debug', "Job ID: {$job->id}, Name: {$job->name}, Candidates: {$job->candidate_count}");
        }
        
        echo "<tr style='background-color: #e6f7ff;'>";
        echo "<td colspan='3' style='text-align: right;'><strong>Total Candidates:</strong></td>";
        echo "<td style='text-align: center;'><strong>{$total_candidates}</strong></td>";
        echo "</tr>";
    } else {
        echo "<tr><td colspan='4' style='text-align: center;'>No jobs found</td></tr>";
    }
    
    echo "</table>";
    
    // Additional debug info
    echo "<br><br>";
    echo "<h3>Debug Information:</h3>";
    echo "<ul>";
    echo "<li>User Agency ID: " . ($user_agency_id ?: 'Not set') . "</li>";
    echo "<li>Total Jobs Found: " . count($results) . "</li>";
    echo "<li>Database: " . $this->db->database . "</li>";
    echo "</ul>";
    
    // Check database tables
    echo "<h3>Table Check:</h3>";
    $candidate_jobs_count = $this->db->count_all('candidate_jobs');
    $mod_jobs_count = $this->db->count_all('mod_jobs');
    
    echo "<ul>";
    echo "<li>Total records in candidate_jobs table: {$candidate_jobs_count}</li>";
    echo "<li>Total records in mod_jobs table: {$mod_jobs_count}</li>";
    echo "</ul>";
    
    log_message('debug', '=== DEBUG CANDIDATES COUNT END ===');
}
/**
 * Pre-process job data to remove HTML tags and clean input
 */
private function pre_process_job_data() {
    $text_fields = [
        'name', 'reference_number', 'description', 'project_overview', 
        'department', 'pay_rate', 'roster', 'accommodation', 'transport',
        'application_email', 'application_url', 'skills', 'qualifications'
    ];
    
    foreach ($text_fields as $field) {
        if ($this->input->post($field)) {
            $clean_value = strip_tags($this->input->post($field));
            $_POST[$field] = $clean_value;
            log_message('debug', "Cleaned field {$field}: " . substr($clean_value, 0, 50) . '...');
        }
    }
    
    // Also handle employment_type to ensure it's valid
    $employment_types = ['full-time', 'part-time', 'contract', 'internship', 'temporary'];
    $current_type = $this->input->post('employment_type');
    if (!in_array($current_type, $employment_types)) {
        $_POST['employment_type'] = 'full-time'; // default value
    }
}
}