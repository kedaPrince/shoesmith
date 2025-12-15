<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidates_list extends CRUD_Controller
{
    public $pageName = 'candidates_list';
    public $group = 'agency';
    public $folder = 'agency';
    public $model = 'Model_candidates';
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
        $this->load->model('agency/Model_candidates');
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
private function enforce_candidate_access($candidate_id)
{
    $agency_id = $this->get_user_agency_id();
    
    if (!$agency_id) {
        $this->candidate_access_denied();
        return false;
    }
    
    $has_access = $this->check_candidate_agency_access_direct($agency_id, $candidate_id);
    
    if (!$has_access) {
        $this->candidate_access_denied();
        return false;
    }
    
    return true;
}

private function candidate_access_denied()
{
    if ($this->input->is_ajax_request()) {
        ajax_return([
            'success' => false,
            'message' => 'Access denied to this candidate',
            'csrf' => $this->security->get_csrf_hash()
        ]);
    } else {
        show_error('Access denied to this candidate', 403);
    }
    exit;
}

private function check_candidate_agency_access_direct($agency_id, $candidate_id)
{
    $this->db->select('1');
    $this->db->from('candidate_agencies ca');
    $this->db->join('candidates c', 'c.id = ca.candidate_id');
    $this->db->where('ca.candidate_id', $candidate_id);
    $this->db->where('ca.agency_id', $agency_id);
    $this->db->where('c.removed', 0);
    $this->db->limit(1);
    
    $result = $this->db->get()->row();
    return $result !== null;
}

private function get_user_agency_id()
{
    $login_data = $this->session->userdata('login');
    
    if (!empty($login_data['agency'])) {
        $agency_user = $login_data['agency'];
        
        if (!empty($agency_user['agency_id'])) {
            return $agency_user['agency_id'];
        } elseif (!empty($agency_user['id'])) {
            return $agency_user['id'];
        }
    }
    
    return null;
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
    
    // 🔒 CRITICAL: Join with candidate_agencies to ensure candidate belongs to this agency
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id AND ca.agency_id = ' . $this->db->escape($agency_id), 'inner');
    
    $this->db->where('cja.job_id', $job_id);
    $this->db->where('cja.removed', 0); // CRITICAL: Only active assignments
    $this->db->where('j.agency_id', $agency_id); // 🔒 Ensure job belongs to agency
    $this->db->where('c.removed', 0);
    $this->db->order_by('c.first_name', 'ASC');
    
    $candidates = $this->db->get()->result();
    $total_candidates = count($candidates);

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


public function view($uuid_or_id = null)
{
    if (!$uuid_or_id) {
        show_error('Candidate identifier required', 400);
    }

    $candidate = $this->{$this->model}->get_candidate($uuid_or_id);
    
    if (empty($candidate)) {
        show_404();
    }
    
    $candidate_id = $candidate->id;
    $candidate_uuid = $candidate->uuid;
    
    // Check access
    if (!$this->enforce_candidate_access($candidate_id)) {
        return;
    }
    
    $agency_id = $this->get_user_agency_id();
    
    // Get job UUID from URL first, then session
    $job_uuid = $this->input->get('job');
    
    // If not in URL, check if we're coming from a job candidates list
    if (!$job_uuid) {
        // Check the referrer URL
        $referrer = $this->input->server('HTTP_REFERER');
        if ($referrer && strpos($referrer, 'candidates_list/index/') !== false) {
            // Extract job UUID from referrer URL
            $pattern = '/candidates_list\/index\/([a-f0-9\-]{36})/';
            if (preg_match($pattern, $referrer, $matches)) {
                $job_uuid = $matches[1];
            }
        }
    }
    
    // Still no job UUID? Check session
    if (!$job_uuid) {
        $job_uuid = $this->session->userdata('current_job_uuid');
    }
    
    // Debug: Log what we found
    log_message('debug', 'Candidate view - Job UUID found: ' . $job_uuid . ' from URL: ' . $this->input->get('job') . ' from referrer: ' . $referrer);
    
    // Now get candidate with specific job context
    $row = $this->{$this->model}->get_candidate_details_by_agency_and_job($uuid_or_id, $agency_id, $job_uuid);

    if (empty($row)) {
        show_404();
    }

    // Pass job_uuid to view for breadcrumbs/back links
    $data = [
        'candidate' => $row,
        'candidate_id' => $row->id,
        'job_uuid' => $job_uuid ?: ($row->job_uuid ?? null),
        'heading' => 'Candidate Details - ' . $row->first_name . ' ' . $row->last_name,
    ];

    // Load view
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

/**
 * Check if agency has access to candidate
 */
private function check_candidate_agency_access($agency_id, $candidate_id)
{
    // Check candidate_agencies pivot table
    $this->db->select('1');
    $this->db->from('candidate_agencies ca');
    $this->db->join('candidates c', 'c.id = ca.candidate_id');
    $this->db->where('ca.candidate_id', $candidate_id);
    $this->db->where('ca.agency_id', $agency_id);
    $this->db->where('c.removed', 0);
    $this->db->limit(1);
    
    return $this->db->get()->row() !== null;
}

/**
 * Check if agency has access to candidate-job assignment
 */
private function check_candidate_job_access($agency_id, $candidate_id, $job_id)
{
    $this->db->select('1');
    $this->db->from('candidate_job_assignments cja');
    $this->db->join('mod_jobs j', 'j.id = cja.job_id');
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = cja.candidate_id');
    $this->db->where('cja.candidate_id', $candidate_id);
    $this->db->where('cja.job_id', $job_id);
    $this->db->where('cja.removed', 0);
    $this->db->where('j.agency_id', $agency_id);
    $this->db->where('ca.agency_id', $agency_id);
    $this->db->limit(1);
    
    return $this->db->get()->row() !== null;
}
}