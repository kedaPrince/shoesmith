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
    public $abling                  = TRUE;

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
        'name' => array(
            'label' => lang('label_title'), 
            'sort' => true,
            'function' => function($str, $row) {
                $is_expired = $this->is_job_expired($row);
                $name = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
                
                if ($is_expired) {
                    return '<span class="expired-job-text">' . $name . '</span>';
                }
                return $name;
            }
        ),
        'reference_number' => array(
            'label' => lang('label_reference_number'), 
            'sort' => true,
            'function' => function($str, $row) {
                $is_expired = $this->is_job_expired($row);
                $ref = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
                
                if ($is_expired) {
                    return '<span class="expired-job-text">' . $ref . '</span>';
                }
                return $ref;
            }
        ),
        'employment_type' => array(
            'label' => lang('label_job_type'), 
            'sort' => true,
            'function' => function($str, $row) {
                $is_expired = $this->is_job_expired($row);
                $employment_types = [
                    'full-time' => 'Full Time',
                    'part-time' => 'Part Time', 
                    'contract' => 'Contract',
                    'internship' => 'Internship',
                    'temporary' => 'Temporary'
                ];
                $display_value = $employment_types[$str] ?? $str;
                
                if ($is_expired) {
                    return '<span class="expired-job-text">' . $display_value . '</span>';
                }
                return $display_value;
            }
        ),
        'closing_date' => array(
            'label' => 'Closing Date', 
            'sort' => true,
            'function' => function($str, $row) {
                if (empty($row->closing_date) || $row->closing_date == '0000-00-00') {
                    return '<span class="text-muted">Not set</span>';
                }
                
                $closing_date = date('M j, Y', strtotime($row->closing_date));
                $today = date('Y-m-d');
                $is_expired = $this->is_job_expired($row);
                
                if ($is_expired) {
                    return '<span class="text-danger expired-job-text" title="Job expired"><i class="fa fa-exclamation-circle"></i> ' . $closing_date . '</span>';
                }
                
                // Check if closing date is within 7 days
                $one_week_later = date('Y-m-d', strtotime('+7 days'));
                if ($row->closing_date <= $one_week_later) {
                    return '<span class="text-warning" title="Closing soon"><i class="fa fa-clock-o"></i> ' . $closing_date . '</span>';
                }
                
                return '<span class="text-success">' . $closing_date . '</span>';
            }
        ),
        'industry_name' => array(
            'label' => lang('label_industry'),
            'sort' => true,
            'function' => function($str, $row) {
                $is_expired = $this->is_job_expired($row);
                $industry = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
                
                if ($is_expired) {
                    return '<span class="expired-job-text">' . $industry . '</span>';
                }
                return $industry;
            }
        ),
        'agency_name' => array(
            'label' => lang('label_agency'),
            'sort' => true,
            'function' => function($str, $row) {
                $is_expired = $this->is_job_expired($row);
                $agency = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
                
                if ($is_expired) {
                    return '<span class="expired-job-text">' . $agency . '</span>';
                }
                return $agency;
            }
        ),

        'candidate_count' => array(
            'label' => 'My Candidates',
            'sort' => true,
            'function' => function($str, $row) {
                // Load the model if not already loaded
                $this->load->model('recruiter/model_jobs');
                
                // Get current recruiter ID
                $recruiter_id = $this->get_current_recruiter_id();
                
                // Use the new method to count ONLY this recruiter's candidates
                $count = $this->model_jobs->get_candidate_count_for_job($row->id, $recruiter_id);
                
                $url = site_url('recruiter/candidates/for_job/' . $row->id);
                $is_expired = $this->is_job_expired($row);
                
                if ($count > 0) {
                    if ($is_expired) {
                        return '<span class="btn btn-sm btn-secondary expired-job-btn" title="Job expired - view only">' . $count . '</span>';
                    }
                    return '<a href="' . $url . '" class="btn btn-sm btn-info" title="View my ' . $count . ' Candidates">' . $count . '</a>';
                } else {
                    if ($is_expired) {
                        return '<span class="text-muted expired-job-text">0</span>';
                    }
                    return '<span class="text-muted">0</span>';
                }
            }
        ),
    );

    $this->listActions = array(
        'view' => array(
            'label'     => lang('label_view'),
            'url'       => url($this->pageName . '/view/{id}'),
            'icon'      => 'fa-eye',
            'class'     => 'view-row btn-info',
            'title'     => 'View job details',
        ),
        'view_candidates' => array(
            'label'     => 'View Candidates',
            'url'       => url('candidates/for_job/{id}'),
            'icon'      => 'fa-users',
            'class'     => 'view-candidates-row btn-primary',
            'title'     => 'View candidates for this job',
            'function'  => function($str, $row) {
                // Hide Add Candidate button for expired jobs
                return !$this->is_job_expired($row) ? $str : false;
            }
        ),
        'add_candidate' => array(
            'label'     => 'Add Candidate',
            'url'       => url('candidates/add/{id}'),
            'icon'      => 'fa-user-plus',
            'class'     => 'add-candidate-row btn-success',
            'function'  => function($str, $row) {
                // Hide Add Candidate button for expired jobs
                return !$this->is_job_expired($row) ? $str : false;
            }
        ),
    );

    //he built-in listRowAttributes for styling
    $this->listRowAttributes = function($row) {
        $is_expired = $this->is_job_expired($row);
        if ($is_expired) {
            return [
                'class' => 'disabled'
            ];
        }
        return [];
    };

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
                'skills' => 'trim', // Skills as direct column
                'qualifications' => 'trim', // Qualifications as direct column
            ),
            // Remove the multi_selects section since we're using direct columns now
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


public function ajax_assign_candidate_to_job()
{
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $candidate_ids = $this->input->post('candidate_ids');
    $job_id = $this->input->post('job_id');
    $recruiter_id = $this->get_current_recruiter_id();

    if (!$recruiter_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Recruiter not found'
        ]);
        return;
    }

    if (empty($candidate_ids)) {
        echo json_encode([
            'success' => false,
            'message' => 'No candidates selected'
        ]);
        return;
    }

    // Ensure candidate_ids is an array
    if (!is_array($candidate_ids)) {
        $candidate_ids = [$candidate_ids];
    }

    $success_count = 0;
    $errors = [];

    foreach ($candidate_ids as $candidate_id) {
        // Check if assignment already exists
        $existing = $this->db->get_where('candidate_job_assignments', [
            'candidate_id' => $candidate_id,
            'job_id' => $job_id
        ])->row();

        if ($existing) {
            $errors[] = "Candidate ID $candidate_id is already assigned to this job";
            continue;
        }

        // Verify candidate exists and belongs to recruiter
        $candidate = $this->db->get_where('candidates', [
            'id' => $candidate_id,
            'assigned_agent_id' => $recruiter_id,
            'enabled' => 1,
            'removed' => 0
        ])->row();

        if (!$candidate) {
            $errors[] = "Candidate ID $candidate_id not found or not accessible";
            continue;
        }

        // Create new assignment
        $assignment_data = [
            'candidate_id' => $candidate_id,
            'job_id' => $job_id,
            'assigned_agent_id' => $recruiter_id,
            'assigned_at' => date('Y-m-d H:i:s'),
            'status' => 'submitted'
        ];

        if ($this->db->insert('candidate_job_assignments', $assignment_data)) {
            $success_count++;
        } else {
            $errors[] = "Failed to assign candidate ID $candidate_id";
        }
    }

    $response = [
        'success' => $success_count > 0,
        'message' => "Successfully assigned $success_count candidate(s) to the job",
        'assigned_count' => $success_count
    ];

    if (!empty($errors)) {
        $response['errors'] = $errors;
    }

    echo json_encode($response);
}


    /**
 * Check if a job has expired based on closing date
 */
private function is_job_expired($job) {
    if (empty($job->closing_date) || $job->closing_date == '0000-00-00') {
        return false;
    }
    
    $today = date('Y-m-d');
    return $job->closing_date < $today;
}

   /**
 * Override get_all to set enabled=0 for expired jobs
 * This will automatically trigger the red row styling from the CRUD system
 */
public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
{
    $user_agency_id = $this->get_user_agency_id();
    if ($user_agency_id) {
        $this->db->where('mod_jobs.agency_id', $user_agency_id);
    }
    
    $result = parent::get_all($limit, $offset, $sort_by, $sort_order);
    
    // Modify the result objects to set enabled=0 for expired jobs
    if ($result && method_exists($result, 'result')) {
        $rows = $result->result();
        foreach ($rows as $row) {
            if ($this->is_job_expired($row)) {
                $row->enabled = 0; // This triggers the automatic red row styling
            }
        }
    }
    
    return $result;
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
    
    // Get the job with agency filtering and proper joins
    $this->db->select('mod_jobs.*, agencies.name as agency_name, mod_industries.name as industry_name');
    $this->db->from('mod_jobs');
    $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
    $this->db->join('mod_industries', 'mod_industries.id = mod_jobs.industry_id', 'left');
    $this->db->where('mod_jobs.id', $id);
    
    if ($user_agency_id) {
        $this->db->where('mod_jobs.agency_id', $user_agency_id);
    }
    
    $job = $this->db->get()->row();
    
    if (!$job) {
        show_404();
    }

    // Load additional data
    $data['job'] = $job;
    $data['skills'] = !empty($job->skills) ? explode(',', $job->skills) : [];
    $data['qualifications'] = !empty($job->qualifications) ? explode(',', $job->qualifications) : [];
    $data['skill_options'] = null;
    $data['qualification_options'] = null;
    $data['updated_fields'] = $this->get_updated_fields_for_job($id);
    
    // Only pass recruiter ID to view
    $data['recruiter_id'] = $this->get_current_recruiter_id();

    // Debug: Log the job agency ID
    log_message('debug', 'View method - Job Agency ID: ' . $job->agency_id);
    log_message('debug', 'View method - Recruiter ID: ' . $data['recruiter_id']);

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

// In your controller that shows candidates for a job
public function view_candidates($job_id)
{
    $recruiter_id = $this->get_current_recruiter_id();
    
    // Get job details
    $this->db->select('mod_jobs.*, agencies.name as agency_name');
    $this->db->from('mod_jobs');
    $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
    $this->db->where('mod_jobs.id', $job_id);
    
    $user_agency_id = $this->get_user_agency_id();
    if ($user_agency_id) {
        $this->db->where('mod_jobs.agency_id', $user_agency_id);
    }
    
    $job = $this->db->get()->row();
    
    if (!$job) {
        show_404();
    }

    // Get candidates using the new method
    $data['candidates'] = $this->model_jobs->get_candidates_for_job($job_id, $recruiter_id);
    $data['job'] = $job;
    $data['total_candidates'] = count($data['candidates']);

    // Load your view
    $this->load->view($this->folder . '/view_header');
    $this->load->view('recruiter/jobs/view_job_candidates', $data);
    $this->load->view($this->folder . '/view_footer');
}

public function ajax_get_candidates_for_job()
{
    log_message('debug', 'ajax_get_candidates_for_job method called');
    
    $job_id = $this->input->post('job_id');
    $recruiter_id = $this->get_current_recruiter_id();

    log_message('debug', 'Job ID: ' . $job_id);
    log_message('debug', 'Recruiter ID: ' . $recruiter_id);

    if (!$recruiter_id) {
        log_message('debug', 'No recruiter ID found');
        echo json_encode([
            'success' => false,
            'message' => 'Recruiter not found'
        ]);
        return;
    }

    // Get candidates that belong to this recruiter and are not assigned to this job
    $this->db->select('c.id, c.first_name, c.last_name, c.reference_number, c.email, c.assigned_agent_id');
    $this->db->from('candidates c');
    $this->db->where('c.assigned_agent_id', $recruiter_id);
    $this->db->where('c.enabled', 1);
    $this->db->where('c.removed', 0);
    
    // Exclude candidates already assigned to this job using LEFT JOIN
    $this->db->join('candidate_job_assignments cja', 'cja.candidate_id = c.id AND cja.job_id = ' . $this->db->escape($job_id), 'left');
    $this->db->where('cja.id IS NULL'); // Only get candidates without assignment to this job
    
    $this->db->order_by('c.first_name', 'ASC');
    
    $query = $this->db->get();
    $candidates = $query->result_array();

    log_message('debug', 'Found ' . count($candidates) . ' candidates for assigned_agent_id ' . $recruiter_id);

    echo json_encode([
        'success' => true,
        'candidates' => $candidates,
        'debug_info' => [
            'recruiter_id' => $recruiter_id,
            'total_candidates' => count($candidates),
            'candidate_ids' => array_column($candidates, 'id')
        ]
    ]);
}


    /**
     * Get updated fields from notifications for this job
     */
    private function get_updated_fields_for_job($job_id) {
        $recruiter_id = $this->get_current_recruiter_id();
        
        if (!$recruiter_id) {
            return [];
        }


        // Get the latest unread update notification for this job
        $this->db->select('updated_fields, id, created_at, type');
        $this->db->from('notifications');
        $this->db->where('receiver_type', 'recruiter');
        $this->db->where('receiver_id', $recruiter_id);
        $this->db->where('related_entity', 'job');
        $this->db->where('related_entity_id', $job_id);
        $this->db->where("(type = 'job_updated' OR type = '')");
        $this->db->where('is_read', 0);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);
        
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $notification = $query->row();
            
            if (!empty($notification->updated_fields)) {
                $updated_fields = json_decode($notification->updated_fields, true);
                return is_array($updated_fields) ? $updated_fields : [];
            }
        } else {
        }

        return [];
    }

    /**
     * Get current recruiter ID from session
     */
    private function get_current_recruiter_id() {
        $login_data = $this->session->userdata('login');
        $recruiter_id = !empty($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : null;
        return $recruiter_id;
    }


}