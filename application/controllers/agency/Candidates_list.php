<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidates_list extends CRUD_Controller
{
    public $pageName = 'candidates_list';
    public $group = 'agency';
    public $folder = 'agency';
    public $model = 'Model_candidates_list';
    public $singular = 'Candidate';
    public $plural = 'Candidates';
    public $identifierField = 'first_name';
    public $adding = false;

    public function __construct()
    {
        parent::__construct();

        $login_data = $this->session->userdata('login');
        if (empty($login_data['agency'])) {
            redirect('agency/dashboard');
        }

        $this->load->model($this->folder . '/' . $this->model);
        $this->setup_listing();
    }

    private function setup_listing()
    {
        $this->listFields = array(
            'reference_number' => array('label' => lang('label_reference_number'), 'sort' => true),
            'first_name'       => array('label' => lang('label_first_name'), 'sort' => true),
            'last_name'        => array('label' => lang('label_last_name'), 'sort' => true),
            'email'            => array('label' => lang('label_email'), 'sort' => true),
            'job_name'         => array('label' => lang('label_job'), 'sort' => true),
            'status'           => array(
                'label' => lang('label_status'),
                'sort' => true,
                'type'  => 'badge',
                'options' => array(
                    'submitted'    => array('class' => 'badge-primary', 'label' => 'Submitted'),
                    'shortlisted'  => array('class' => 'badge-success', 'label' => 'Shortlisted'),
                    'rejected'     => array('class' => 'badge-danger',  'label' => 'Rejected')
                )
            ),
            'application_date' => array(
                'label' => lang('label_application_date'),
                'sort'  => true,
                'type'  => 'date',
                'field' => 'COALESCE(cja.assigned_at, cj.created_at, c.application_date)'
            ),
        );

        $this->listActions = array(
            'view' => array(
                'label' => lang('label_view'),
                'url'   => site_url('agency/candidates_list/view/{id}'),
                'icon'  => 'fa-eye',
                'class' => 'view-row',
            ),
        );
    }

public function index($uuid_or_id = null)
{
    // Always get identifier from URL (segment 4), never rely only on session
    $uuid_or_id = $uuid_or_id ?: $this->uri->segment(4);
    $agency_id = $this->get_user_agency_id();

    if (!$uuid_or_id || !$agency_id) {
        show_error('Job identifier is required', 400);
    }

    // Get job using helper method or direct query
    $job = $this->get_job_by_identifier($uuid_or_id, $agency_id);
    
    if (!$job) {
        show_error('Job not found or access denied', 404);
    }

    $job_id = $job->id;
    $job_uuid = $job->uuid;
    $job_name = $job->name;

    // FIXED: Proper query to get active candidates for this job
    $this->db->select('c.*, 
                       j.name as job_name, 
                       j.uuid as job_uuid,
                       a.name as agency_name,
                       cja.status as application_status,
                       cja.assigned_at as application_date');
    $this->db->from('candidate_job_assignments cja');
    $this->db->join('candidates c', 'c.id = cja.candidate_id', 'inner');
    $this->db->join('mod_jobs j', 'j.id = cja.job_id', 'left');
    $this->db->join('agencies a', 'a.id = j.agency_id', 'left');
    
    // Join with candidate_agencies to ensure candidate belongs to this agency
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
    
    $this->db->where('cja.job_id', $job_id);
    $this->db->where('cja.removed', 0); // CRITICAL: Only active assignments
    $this->db->where('ca.agency_id', $agency_id);
    $this->db->where('c.removed', 0);
    $this->db->order_by('c.first_name', 'ASC');
    
    $candidates = $this->db->get()->result();
    $total_candidates = count($candidates);

    // DEBUG: Show what we found
    echo "<!-- DEBUG: Found " . $total_candidates . " candidates for job ID " . $job_id . " -->\n";
    echo "<!-- SQL: " . $this->db->last_query() . " -->\n";
    
    foreach ($candidates as $c) {
        echo "<!-- Candidate: " . $c->first_name . " " . $c->last_name . " (ID: " . $c->id . ") -->\n";
    }

    // Set session data for breadcrumbs
    $this->session->set_userdata('current_job_id', (int)$job_id);
    $this->session->set_userdata('current_job_uuid', $job_uuid);

    // Pass data to view
    $data = [
        'job' => $job,
        'candidates' => $candidates,
        'total_candidates' => $total_candidates,
        'job_id' => $job_id,
        'job_uuid' => $job_uuid,
        'heading' => 'Candidates for: ' . $job_name,
    ];

    // Load the custom view instead of CRUD view
    $this->load->view($this->folder . '/view_header');
    $this->load->view('agency/candidates_list/job_candidates_list', $data);
    $this->load->view($this->folder . '/view_footer');
}

    private function get_user_agency_id()
    {
        $login = $this->session->userdata('login');
        if (!empty($login['agency'])) {
            $a = $login['agency'];
            return $a['agency_id'] ?? $a['id'] ?? null;
        }
        return null;
    }

public function view($candidate_id = null)
{
    if (!$candidate_id) {
        show_error('Candidate ID required', 400);
    }

    // Get job_id from URL parameter (5th segment after candidate_id)
    $job_id = $this->uri->segment(5);
    
    if (!$job_id) {
        show_error('Job ID is required in the URL', 400);
    }

    // Verify the job belongs to the agency
    $agency_id = $this->get_user_agency_id();
    if ($agency_id) {
        $this->db->select('1');
        $this->db->from('mod_jobs');
        $this->db->where('id', $job_id);
        $this->db->where('agency_id', $agency_id);
        $valid_job = $this->db->get()->row();
        
        if (!$valid_job) {
            show_error('Job not found or access denied', 403);
        }
    }

    // FIXED: Query from candidate_job_assignments for this specific job
    $this->db->select('c.*,
                       j.name as job_name,
                       j.reference_number as job_ref,
                       a.name as agency_name,
                       cja.status,
                       cja.assigned_at as application_date');
    $this->db->from('candidate_job_assignments cja');
    $this->db->join('candidates c', 'c.id = cja.candidate_id', 'inner');
    $this->db->join('mod_jobs j', 'j.id = cja.job_id', 'left');
    $this->db->join('agencies a', 'a.id = j.agency_id', 'left');
    $this->db->where('cja.candidate_id', $candidate_id);
    $this->db->where('cja.job_id', $job_id);
    $this->db->where('cja.removed', 0);
    $this->db->where('c.removed', 0);

    $candidate = $this->db->get()->row();

    if (!$candidate) {
        show_error('Candidate not found or not assigned to this job', 404);
    }

    $this->breadcrumbs = [
        ['title' => lang('jobs_listings_heading'), 'url' => site_url('agency/jobs_listings')],
        ['title' => 'Candidates', 'url' => site_url("agency/candidates_list/index/{$job_id}")],
        ['title' => 'View Candidate', 'url' => '#'],
    ];

    $data = [
        'candidate' => $candidate,
        'job_id'    => $job_id,
        'heading'   => 'Candidate: ' . $candidate->first_name . ' ' . $candidate->last_name
    ];

    $this->load->view($this->folder . '/view_header');
    $this->load->view('agency/candidates_list/view', $data);
    $this->load->view($this->folder . '/view_footer');
}
    // Keep your _get_list_data and _get_list_count overrides
    public function _get_list_data($limit = null, $offset = null, $sort_by = null, $sort_order = null, $filter = null)
    {
        $job_id = $this->session->userdata('current_job_id');
        $agency_id = $this->get_user_agency_id();
        if ($job_id && $agency_id) {
            return $this->{$this->model}->get_all($limit, $offset, $sort_by, $sort_order, $filter);
        }
        return parent::_get_list_data($limit, $offset, $sort_by, $sort_order, $filter);
    }

    public function _get_list_count($filter = null)
    {
        $job_id = $this->session->userdata('current_job_id');
        $agency_id = $this->get_user_agency_id();
        if ($job_id && $agency_id) {
            return $this->{$this->model}->count_all($filter);
        }
        return parent::_get_list_count($filter);
    }

    /**
 * Helper method to get job by UUID or ID
 */
private function get_job_by_identifier($identifier, $agency_id = null)
{
    // Check if identifier is UUID or ID
    $is_uuid = (is_string($identifier) && strlen($identifier) == 36 && strpos($identifier, '-') !== false);
    
    if ($is_uuid) {
        $this->db->where('uuid', $identifier);
    } else {
        $this->db->where('id', $identifier);
    }
    
    if ($agency_id) {
        $this->db->where('agency_id', $agency_id);
    }
    
    $this->db->where('removed', 0);
    return $this->db->get('mod_jobs')->row();
}
/**
 * Remove candidate from job (AJAX)
 */
/**
 * Remove candidate from job (AJAX)
 */
public function remove_candidate_from_job()
{
    if (!$this->input->is_ajax_request()) {
        show_error('Direct access not allowed', 403);
    }
    
    $candidate_uuid = $this->input->post('candidate_uuid');
    $job_uuid = $this->input->post('job_uuid');
    $agency_id = $this->get_user_agency_id();
    
    if (!$candidate_uuid || !$job_uuid || !$agency_id) {
        ajax_return(['success' => false, 'message' => 'Invalid request']);
        return;
    }
    
    // Get candidate ID
    $candidate = $this->db->where('uuid', $candidate_uuid)
                          ->where('removed', 0)
                          ->get('candidates')
                          ->row();
    
    if (!$candidate) {
        ajax_return(['success' => false, 'message' => 'Candidate not found']);
        return;
    }
    
    // Get job ID
    $job = $this->db->where('uuid', $job_uuid)
                    ->where('removed', 0)
                    ->where('agency_id', $agency_id)
                    ->get('mod_jobs')
                    ->row();
    
    if (!$job) {
        ajax_return(['success' => false, 'message' => 'Job not found or access denied']);
        return;
    }
    
    // FIXED: Check if candidate is assigned to this job in candidate_job_assignments
    $assignment = $this->db->where('candidate_id', $candidate->id)
                           ->where('job_id', $job->id)
                           ->where('removed', 0)
                           ->get('candidate_job_assignments')
                           ->row();
    
    if (!$assignment) {
        // Also check candidate_jobs table as fallback
        $job_assignment = $this->db->where('candidate_id', $candidate->id)
                                   ->where('job_id', $job->id)
                                   ->get('candidate_jobs')
                                   ->row();
        
        if (!$job_assignment) {
            ajax_return(['success' => false, 'message' => 'Candidate is not assigned to this job']);
            return;
        }
        
        // Remove from candidate_jobs table
        $this->db->where('candidate_id', $candidate->id)
                 ->where('job_id', $job->id)
                 ->delete('candidate_jobs');
    } else {
        // Mark as removed in candidate_job_assignments
        $this->db->where('id', $assignment->id)
                 ->update('candidate_job_assignments', [
                     'removed' => 1,
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);
    }
    
    // Log the activity
    $activity_data = [
        'candidate_id' => $candidate->id,
        'action' => 'removed_from_job',
        'description' => 'Candidate removed from job: ' . $job->name,
        'created_by' => $agency_id,
        'created_by_type' => 'agency',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('candidate_activities', $activity_data);
    
    ajax_return([
        'success' => true,
        'message' => 'Candidate removed from job successfully'
    ]);
}


}