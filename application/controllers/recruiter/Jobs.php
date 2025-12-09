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
    public $quickManage = false;
    public $sluggify = true;
    public $adding = false;
    public $allowEdit = false;
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

    // Debug agency filter
    $user_agency_id = $this->get_user_agency_id();
    log_message('debug', 'User agency ID: ' . ($user_agency_id ?: 'NULL'));
    
    if ($user_agency_id) {
        log_message('debug', 'Applying agency filter: agency_id = ' . $user_agency_id);
        $this->db->where('mod_jobs.agency_id', $user_agency_id);
    }

    $this->setup_listing();
    $this->setup_fields();

    $this->zone = array(
        'title' => lang($this->pageName . '_heading'),
        'url' => redir($this->pageName, true),
    );
    
    $this->sorting = array(
        'closing_date' => 'DESC',
        'name' => 'ASC'
    );


}
/**
 * Check if a job is expired based on closing date
 */
private function is_job_expired($job)
{
    if (empty($job->closing_date) || $job->closing_date == '0000-00-00') {
        return false;
    }
    
    $today = date('Y-m-d');
    return $job->closing_date < $today;
}
    private function setup_listing()
    {
        $this->listFields = array(
            'name' => array(
                'label' => lang('label_title'), 
                'sort' => true,
                'function' => function($str, $row) {
                    $is_expired = $this->is_job_expired($row);
                    $name = !empty($str) ? htmlspecialchars($str, ENT_QUOTES, 'UTF-8') : 'N/A';
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
                
                // FIX: Use UUID instead of ID
                $url = site_url('recruiter/candidates/for_job/' . $row->uuid); // <-- CHANGED TO UUID
                
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

           // In setup_listing() method, update the listActions:
        $this->listActions = array(
    'view' => array(
        'label'     => lang('label_view'),
        'url'       => url($this->pageName . '/view/{uuid}'),
        'icon'      => 'fa-eye',
        'class'     => 'view-row btn-info',
        'title'     => 'View job details',
    ),
    'view_candidates' => array(
        'label'     => 'View Candidates',
        'url'       => url('candidates/for_job/{uuid}'),
        'icon'      => 'fa-users',
        'class'     => 'view-candidates-row btn-primary',
        'title'     => 'View candidates for this job',
    ),
    'add_candidate' => array(
        'label'     => 'Add Candidate',
        'url'       => url('candidates/add/{uuid}'),
        'icon'      => 'fa-user-plus',
        'class'     => 'add-candidate-row btn-success',
        'title'     => 'Add candidate to this job',
    ),
    // ADD THIS NEW ACTION FOR CHAT
    'chat' => array(
        'label'     => 'Chat',
        'url'       => url('chat/start_job_chat/{uuid}'),
        'icon'      => 'fa-comments',
        'class'     => 'chat-job-row btn-warning',
        'title'     => 'Chat with agency about this job',
    ),
);

        //built-in listRowAttributes for styling
        $this->listRowAttributes = function($row) {
            $is_expired = $this->is_job_expired($row);
            if ($is_expired) {
                return [
                    'class' => 'expired-job-row'
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
                'skills' => 'trim',
                'qualifications' => 'trim', 
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


public function ajax_assign_candidate_to_job()
{
    if (!$this->input->is_ajax_request()) {
        echo json_encode([
            'success' => false,
            'message' => 'Direct access not allowed',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }

    $candidate_ids = $this->input->post('candidate_ids');
    $job_id = $this->input->post('job_id');
    $recruiter_id = $this->get_current_recruiter_id();

    if (!$recruiter_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Recruiter not found',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }

    if (empty($job_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Job ID is required',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }

    if (empty($candidate_ids) || !is_array($candidate_ids)) {
        echo json_encode([
            'success' => false,
            'message' => 'No candidates selected',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }

    $success_count = 0;
    $errors = [];

    $job = $this->db->select('mod_jobs.*, agencies.name as agency_name')
                    ->from('mod_jobs')
                    ->join('agencies', 'agencies.id = mod_jobs.agency_id')
                    ->where('mod_jobs.id', $job_id)
                    ->get()
                    ->row();

    if (!$job) {
        echo json_encode([
            'success' => false,
            'message' => 'Job not found',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }

    foreach ($candidate_ids as $candidate_id) {
        $candidate_id = (int) $candidate_id;

        $existing = $this->db->where('candidate_id', $candidate_id)
                            ->where('job_id', $job_id)
                            ->where('removed', 0)
                            ->get('candidate_job_assignments')
                            ->row();

        if ($existing) {
            $errors[] = "Candidate already assigned";
            continue;
        }

        $candidate = $this->db->select('c.*')
                             ->from('candidates c')
                             ->where('c.id', $candidate_id)
                             ->where('c.assigned_agent_id', $recruiter_id)
                             ->where('c.enabled', 1)
                             ->where('c.removed', 0)
                             ->get()
                             ->row();

        if (!$candidate) {
            $errors[] = "Candidate not found or access denied";
            continue;
        }

        $removed_assignment = $this->db->where('candidate_id', $candidate_id)
                                      ->where('job_id', $job_id)
                                      ->where('removed', 1)
                                      ->get('candidate_job_assignments')
                                      ->row();

        if ($removed_assignment) {
            $this->db->where('id', $removed_assignment->id)
                    ->update('candidate_job_assignments', [
                        'removed' => 0,
                        'removed_at' => null,
                        'status' => 'submitted',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            $this->sync_candidate_jobs_table($candidate_id, $job_id, 'reactivate');
            $success_count++;
        } else {
            $assignment_data = [
                'candidate_id' => $candidate_id,
                'job_id' => $job_id,
                'assigned_agent_id' => $recruiter_id,
                'assigned_at' => date('Y-m-d H:i:s'),
                'status' => 'submitted',
                'removed' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->db->insert('candidate_job_assignments', $assignment_data)) {
                $this->sync_candidate_jobs_table($candidate_id, $job_id, 'create');
                $success_count++;
            } else {
                $errors[] = "Failed to assign candidate";
            }
        }

        $this->create_agency_notification($candidate, $job, $recruiter_id);
    }

    if ($success_count > 0) {
        echo json_encode([
            'success' => true,
            'message' => "Successfully assigned $success_count candidate(s) to the job",
            'assigned_count' => $success_count,
            'csrf' => $this->security->get_csrf_hash()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => !empty($errors) ? implode('; ', $errors) : 'Assignment failed',
            'csrf' => $this->security->get_csrf_hash()
        ]);
    }
}
public function ajax_get_candidates_for_job()
{
    if (!$this->input->is_ajax_request()) {
        echo json_encode([
            'success' => false,
            'message' => 'Direct access not allowed',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }

    try {
        $job_id = $this->input->post('job_id');
        $recruiter_id = $this->get_current_recruiter_id();

        if (empty($job_id)) {
            throw new Exception('Job ID is required');
        }

        if (!$recruiter_id) {
            throw new Exception('Recruiter not found');
        }

        $this->db->select('c.id, c.first_name, c.last_name, c.reference_number, c.email, c.assigned_agent_id');
        $this->db->from('candidates c');
        $this->db->where('c.assigned_agent_id', $recruiter_id);
        $this->db->where('c.enabled', 1);
        $this->db->where('c.removed', 0);
        $this->db->join('candidate_job_assignments cja', "cja.candidate_id = c.id AND cja.job_id = " . $this->db->escape($job_id) . " AND cja.removed = 0", 'left');
        $this->db->where('cja.id IS NULL');
        $this->db->order_by('c.first_name', 'ASC');
        
        $query = $this->db->get();
        $candidates = $query->result_array();

        echo json_encode([
            'success' => true,
            'candidates' => $candidates,
            'csrf' => $this->security->get_csrf_hash(),
            'debug_info' => [
                'recruiter_id' => $recruiter_id,
                'total_candidates' => count($candidates),
                'candidate_ids' => array_column($candidates, 'id')
            ]
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to load candidates: ' . $e->getMessage(),
            'csrf' => $this->security->get_csrf_hash()
        ]);
    }
}
    /**
     * Get the agency ID of the logged-in recruiter
     */
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

   public function view($uuid)
{
    $user_agency_id = $this->get_user_agency_id();
    
    $this->db->select('mod_jobs.*, agencies.name as agency_name, mod_industries.name as industry_name');
    $this->db->from('mod_jobs');
    $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
    $this->db->join('mod_industries', 'mod_industries.id = mod_jobs.industry_id', 'left');
    $this->db->where('mod_jobs.uuid', $uuid);
    
    if ($user_agency_id) {
        $this->db->where('mod_jobs.agency_id', $user_agency_id);
    }
    
    $job = $this->db->get()->row();
    
    if (!$job) {
        show_404();
    }

    // THESE TWO LINES ARE CRITICAL — DO NOT SKIP THEM!
    $data['job_id']   = $job->id;        // Internal ID for AJAX
    $data['job_uuid'] = $job->uuid;      // UUID for URLs

    $data['job'] = $job;
    $data['skills'] = !empty($job->skills) ? explode(',', $job->skills) : [];
    $data['qualifications'] = !empty($job->qualifications) ? explode(',', $job->qualifications) : [];
    $data['updated_fields'] = $this->get_updated_fields_for_job($job->id);
    $data['recruiter_id'] = $this->get_current_recruiter_id();

    $this->breadcrumbs = [
        ['title' => lang($this->pageName . '_heading'), 'url' => redir($this->pageName, true)],
        ['title' => $job->name, 'url' => '#'],
    ];

    $this->load->view($this->folder . '/view_header');
    $this->load->view('recruiter/jobs/view_job', $data);  // ← $data is passed here
    $this->load->view($this->folder . '/view_footer');
}

    
public function view_candidates($job_uuid)
{
    $recruiter_id = $this->get_current_recruiter_id();
    log_message('debug', 'view_candidates - Recruiter ID: ' . ($recruiter_id ?: 'NULL'));

    // Get job details by UUID
    $this->db->select('mod_jobs.*, agencies.name as agency_name');
    $this->db->from('mod_jobs');
    $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
    $this->db->where('mod_jobs.uuid', $job_uuid);
    
    $user_agency_id = $this->get_user_agency_id();
    log_message('debug', 'view_candidates - User agency ID: ' . ($user_agency_id ?: 'NULL'));
    
    if ($user_agency_id) {
        $this->db->where('mod_jobs.agency_id', $user_agency_id);
    }
    
    $job = $this->db->get()->row();
    
    if (!$job) {
        log_message('error', 'Job not found for UUID: ' . $job_uuid);
        show_404();
    }

    log_message('debug', 'Job found - ID: ' . $job->id . ', Agency: ' . $job->agency_id);

    // ✅ FIX: Get candidates BEFORE logging count
    $candidates = $this->model_jobs->get_candidates_for_job($job->id, $recruiter_id);
    log_message('debug', 'Candidates count: ' . count($candidates));

    $data['candidates'] = $candidates;
    $data['job'] = $job;
    $data['job_uuid'] = $job->uuid;
    $data['job_id'] = $job->id;
    $data['total_candidates'] = count($candidates);

    $this->load->view($this->folder . '/view_header');
    $this->load->view('recruiter/jobs/view_job_candidates', $data);
    $this->load->view($this->folder . '/view_footer');
}



    /**
     * Get updated fields from notifications for this job
     */
    private function get_updated_fields_for_job($job_id) 
    {
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
    private function get_current_recruiter_id() 
    {
        $login_data = $this->session->userdata('login');
        $recruiter_id = !empty($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : null;
        return $recruiter_id;
    }

    public function debug_job_assignments($job_id)
    {
        $recruiter_id = $this->get_current_recruiter_id();
        
        echo "<h2>Debug Info for Job ID: $job_id</h2>";
        echo "<h3>Recruiter ID: $recruiter_id</h3>";
        
        // Show all candidates for this recruiter
        echo "<h4>All Candidates for Recruiter:</h4>";
        $all_candidates = $this->db->select('id, first_name, last_name, reference_number')
                                ->from('candidates')
                                ->where('assigned_agent_id', $recruiter_id)
                                ->where('enabled', 1)
                                ->where('removed', 0)
                                ->get()
                                ->result();
        echo "<pre>";
        print_r($all_candidates);
        echo "</pre>";
        
        // Show active assignments for this job
        echo "<h4>Active Assignments for Job:</h4>";
        $active_assignments = $this->db->select('cja.*, c.first_name, c.last_name')
                                    ->from('candidate_job_assignments cja')
                                    ->join('candidates c', 'c.id = cja.candidate_id')
                                    ->where('cja.job_id', $job_id)
                                    ->where('cja.removed', 0)
                                    ->get()
                                    ->result();
        echo "<pre>";
        print_r($active_assignments);
        echo "</pre>";
        
        // Show removed assignments for this job
        echo "<h4>Removed Assignments for Job:</h4>";
        $removed_assignments = $this->db->select('cja.*, c.first_name, c.last_name')
                                    ->from('candidate_job_assignments cja')
                                    ->join('candidates c', 'c.id = cja.candidate_id')
                                    ->where('cja.job_id', $job_id)
                                    ->where('cja.removed', 1)
                                    ->get()
                                    ->result();
        echo "<pre>";
        print_r($removed_assignments);
        echo "</pre>";
        
        // Test the query used in ajax_get_candidates_for_job
        echo "<h4>Candidates Available for Assignment (AJAX query result):</h4>";
        $available_candidates = $this->db->select('c.id, c.first_name, c.last_name, c.reference_number, c.email')
                                        ->from('candidates c')
                                        ->where('c.assigned_agent_id', $recruiter_id)
                                        ->where('c.enabled', 1)
                                        ->where('c.removed', 0)
                                        ->where("c.id NOT IN (
                                            SELECT candidate_id 
                                            FROM candidate_job_assignments 
                                            WHERE job_id = $job_id 
                                            AND removed = 0
                                        )")
                                        ->get()
                                        ->result();
        echo "<pre>";
        print_r($available_candidates);
        echo "</pre>";
    }

/**
 * Sync candidate_jobs table when assignments are made
 */
private function sync_candidate_jobs_table($candidate_id, $job_id, $action = 'create')
{
    if ($action === 'create' || $action === 'reactivate') {
        // Check if record already exists in candidate_jobs
        $existing = $this->db->where('candidate_id', $candidate_id)
                            ->where('job_id', $job_id)
                            ->get('candidate_jobs')
                            ->row();
        
        if (!$existing) {
            // Create new record in candidate_jobs
            $candidate_job_data = [
                'candidate_id' => $candidate_id,
                'job_id' => $job_id,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('candidate_jobs', $candidate_job_data);
        }
        // If it exists, we don't need to do anything - it's already there
    }
}

/**
 * Create notification for agency when candidate is assigned to job
 */
private function create_agency_notification($candidate, $job, $recruiter_id)
{
    // Get recruiter details
    $recruiter = $this->db->select('first_name, last_name')
                        ->from('recruiters')
                        ->where('id', $recruiter_id)
                        ->get()
                        ->row();

    $recruiter_name = $recruiter ? ($recruiter->first_name . ' ' . $recruiter->last_name) : 'Unknown Recruiter';

    // Prepare notification data
    $notification_data = [
        'title' => 'New Candidate Submission',
        'message' => "{$candidate->first_name} {$candidate->last_name} has been submitted for job: {$job->name} by {$recruiter_name}",
        'type' => 'candidate_applied',
        'receiver_type' => 'agency',
        'receiver_id' => $job->agency_id,
        'related_entity' => 'candidate',
        'related_entity_id' => $candidate->id,
        'sender_type' => 'recruiter',
        'sender_id' => $recruiter_id,
        'created_at' => date('Y-m-d H:i:s'),
        'is_read' => 0
    ];

    // Insert notification
    $this->db->insert('notifications', $notification_data);
}

}