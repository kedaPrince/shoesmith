<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Candidates_list extends CRUD_Controller
{
    public $pageName = 'candidates_list';
    public $group = 'agency';
    public $folder = 'agency';
    public $model = 'Model_candidates_list';
    public $singular = 'Candidate';
    public $plural = 'Candidates';
    public $identifierField = 'first_name';
    public $quickManage = false;
    public $adding = false;
    public $allowEdit = false;
    public $sorting = array('application_date' => 'DESC');
    public $sluggify = false;
    public $quickManageSize = 4;

    public function __construct()
    {
        parent::__construct();

        // Check login - only allow agency users
        $login_data = $this->session->userdata('login');
        if (empty($login_data['agency'])) {
            redirect('agency/dashboard');
        }

        $this->load->model($this->folder . '/' . $this->model);
        $this->setup_listing();
        
        $this->zone = array(
            'title' => lang('candidates_heading'),
            'url' => redir('candidates_list', true), // FIXED: Added missing comma
        );
    }

   private function setup_listing()
{
    $this->listFields = array(
        'reference_number' => array('label' => lang('label_reference_number'), 'sort' => true),
        'first_name' => array('label' => lang('label_first_name'), 'sort' => true),
        'last_name' => array('label' => lang('label_last_name'), 'sort' => true),
        'email' => array('label' => lang('label_email'), 'sort' => true),
        // ✅ UNCOMMENT: Add job column back
        'job_name' => array('label' => lang('label_job'), 'sort' => true),
        'status' => array('label' => lang('label_status'), 'sort' => true),
        'application_date' => array('label' => lang('label_application_date'), 'sort' => true, 'type' => 'date'),
    );

    $this->listActions = array(
        'view' => array(
            'label' => lang('label_view'),
            'url' => site_url('agency/candidates_list/view/{id}'),
            'icon' => 'fa-eye',
            'class' => 'view-row',
        ),
    );

    $this->filters = array(
        'search' => array(
            'label' => lang('label_search'),
            'type' => 'autocomplete',
            'field' => array('candidates.first_name', 'candidates.last_name', 'candidates.email', 'candidates.reference_number'),
        ),
        'status' => array(
            'label' => lang('label_status'),
            'type' => 'dropdown',
            'field' => 'candidates.status',
            'options' => array(
                'new' => 'New',
                'reviewed' => 'Reviewed',
                'shortlisted' => 'Shortlisted',
                'interviewed' => 'Interviewed',
                'rejected' => 'Rejected',
                'hired' => 'Hired',
                'on_hold' => 'On Hold',
            ),
        ),
    );


        $this->listActions = array(
            'view' => array(
                'label' => lang('label_view'),
                'url' => site_url('agency/candidates_list/view/{id}'),
                'icon' => 'fa-eye',
                'class' => 'view-row',
            ),
        );

        $this->filters = array(
            'search' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array('candidates.first_name', 'candidates.last_name', 'candidates.email', 'candidates.reference_number'),
            ),
            'status' => array(
                'label' => lang('label_status'),
                'type' => 'dropdown',
                'field' => 'candidates.status',
                'options' => array(
                    'new' => 'New',
                    'reviewed' => 'Reviewed',
                    'shortlisted' => 'Shortlisted',
                    'interviewed' => 'Interviewed',
                    'rejected' => 'Rejected',
                    'hired' => 'Hired',
                    'on_hold' => 'On Hold',
                ),
            ),
        );
    }

public function index()
{
    $job_id = $this->uri->segment(4);
    $agency_id = $this->get_user_agency_id();
    
    if (!$job_id || !$agency_id) {
        show_error('Job ID is required', 400);
    }
    
    // Verify job belongs to agency
    $job = $this->db->get_where('mod_jobs', ['id' => $job_id, 'agency_id' => $agency_id])->row();
    if (!$job) {
        show_error('Job not found', 404);
    }
    
    // ✅ CLEAR any previous session data and set new
    $this->session->unset_userdata('current_job_id');
    $this->session->set_userdata('current_job_id', $job_id);
    
    // ✅ VERIFY session was set
    $verify_job_id = $this->session->userdata('current_job_id');
    if ($verify_job_id != $job_id) {
        show_error('Session error: job ID not properly set', 500);
    }

    $this->breadcrumbs = [
        ['title' => lang('jobs_listings_heading'), 'url' => site_url('agency/jobs_listings')],
        ['title' => 'Candidates for: ' . $job->name, 'url' => '#'],
    ];

    $this->view = 'listing';
    $this->load->view($this->folder . '/view_header');
    $this->load->view('cms/crud/view_list', [
        'heading' => 'Candidates for: ' . $job->name,
        'noRows' => lang('candidates_no_rows'),
    ]);
    $this->load->view($this->folder . '/view_footer');
}




    private function get_user_agency_id()
    {
        $login = $this->session->userdata('login');
        
        // Only check for agency staff login (not recruiters)
        if (!empty($login['agency'])) {
            $agency_user = $login['agency'];
            
            if (!empty($agency_user['agency_id'])) {
                return $agency_user['agency_id'];
            } elseif (!empty($agency_user['id'])) {
                return $agency_user['id'];
            }
        }
        
        log_message('error', 'No agency_id found in session for agency user');
        return null;
    }

public function view($id)
{
    $job_id = $this->session->userdata('current_job_id');
    $agency_id = $this->get_user_agency_id();
    
    if (!$agency_id) {
        show_error('Access denied', 403);
    }

    // ✅ FIXED: Only check agency assignment, not job assignment
    // This allows viewing candidates from the agency even if they're not in the current job
    $this->db->select('c.*, j.name as job_name, j.reference_number as job_ref, a.name as agency_name');
    $this->db->from('candidates c');
    $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
    $this->db->join('agencies a', 'a.id = c.agency_id', 'left');
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
    $this->db->where('c.id', $id);
    $this->db->where('ca.agency_id', $agency_id);
    $this->db->where('c.removed', 0);

    $candidate = $this->db->get()->row();

    if (!$candidate) {
        show_error('Candidate not found', 404);
    }

    // Use the candidate's actual job_id for breadcrumbs, not the session job_id
    $breadcrumb_job_id = $candidate->job_id ?: $job_id;

    $data = array(
        'candidate' => $candidate,
        'heading' => 'Candidate Details: ' . $candidate->first_name . ' ' . $candidate->last_name,
        'breadcrumbs' => array(
            array('title' => 'Jobs', 'url' => site_url('agency/jobs_listings')),
            array('title' => 'Candidates', 'url' => $breadcrumb_job_id ? site_url("agency/candidates_list/index/{$breadcrumb_job_id}") : site_url('agency/candidates_list')),
            array('title' => 'View Candidate', 'url' => '#'),
        )
    );

    $this->load->view($this->folder . '/view_header');
    $this->load->view('agency/candidates_list/view', $data);
    $this->load->view($this->folder . '/view_footer');
}
public function _get_list_data($limit = null, $offset = null, $sort_by = null, $sort_order = null, $filter = null)
{
    $job_id = $this->session->userdata('current_job_id');
    $agency_id = $this->get_user_agency_id();

    if (!$job_id || !$agency_id) {
        return parent::_get_list_data($limit, $offset, $sort_by, $sort_order, $filter);
    }

    // ✅ CORRECTED QUERY: Only show candidates assigned to THIS specific job
    $this->db->select('c.*, j.name as job_name, j.reference_number as job_ref, a.name as agency_name');
    $this->db->from('candidates c');
    $this->db->join('candidate_jobs cj', 'cj.candidate_id = c.id', 'inner');
    $this->db->join('mod_jobs j', 'j.id = cj.job_id', 'left');
    $this->db->join('agencies a', 'a.id = c.agency_id', 'left');
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
    
    // ✅ CRITICAL FIX: Filter by the specific job AND agency
    $this->db->where('cj.job_id', (int)$job_id);
    $this->db->where('ca.agency_id', (int)$agency_id);
    $this->db->where('c.removed', 0);

    if ($sort_by && isset($this->listFields[$sort_by])) {
        if ($sort_by === 'job_name') {
            $this->db->order_by('j.name', $sort_order ?: 'ASC');
        } else {
            $this->db->order_by("c.{$sort_by}", $sort_order ?: 'ASC');
        }
    } else {
        $this->db->order_by('c.application_date', 'DESC');
    }

    if ($limit !== null) {
        $this->db->limit($limit, $offset);
    }

    return $this->db->get();
}

public function _get_list_count($filter = null)
{
    $job_id = $this->session->userdata('current_job_id');
    $agency_id = $this->get_user_agency_id();

    if (!$job_id || !$agency_id) {
        return parent::_get_list_count($filter);
    }

    $this->db->from('candidates c');
    $this->db->join('candidate_jobs cj', 'cj.candidate_id = c.id', 'inner');
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
    $this->db->where('cj.job_id', (int)$job_id);
    $this->db->where('ca.agency_id', (int)$agency_id);
    $this->db->where('c.removed', 0);

    return $this->db->count_all_results();
}


public function debug_view_access($candidate_id = 12)
{
    $job_id = $this->session->userdata('current_job_id');
    $agency_id = $this->get_user_agency_id();
    
    echo "<h2>Debug View Access - Candidate ID: $candidate_id</h2>";
    echo "<p>Job ID from session: " . ($job_id ?: 'NOT SET') . "</p>";
    echo "<p>Agency ID: " . ($agency_id ?: 'NOT SET') . "</p>";
    
    if ($agency_id) {
        // Create a fresh query without interfering with active DB state
        $db = $this->load->database('default', TRUE);
        
        $db->select('c.*, j.name as job_name, j.reference_number as job_ref, a.name as agency_name');
        $db->from('candidates c');
        $db->join('mod_jobs j', 'j.id = c.job_id', 'left');
        $db->join('agencies a', 'a.id = c.agency_id', 'left');
        $db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
        $db->where('c.id', $candidate_id);
        $db->where('ca.agency_id', $agency_id);
        $db->where('c.removed', 0);
        
        echo "<h3>View Method Query:</h3>";
        echo "<pre>" . $db->get_compiled_select() . "</pre>";
        
        $result = $db->get();
        echo "<p>Query Results: " . $result->num_rows() . " rows</p>";
        
        if ($result->num_rows() > 0) {
            $candidate = $result->row();
            echo "<p style='color: green;'>✓ Candidate found: " . $candidate->first_name . " " . $candidate->last_name . "</p>";
            echo "<p>Job: " . ($candidate->job_name ?: 'Not assigned') . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Candidate NOT found with current query</p>";
            
            // Let's check why...
            echo "<h3>Debugging why candidate is not found:</h3>";
            
            // Check if candidate exists at all
            $exists = $db->where('id', $candidate_id)->where('removed', 0)->get('candidates')->row();
            echo "<p>Candidate exists in database: " . ($exists ? 'YES - ' . $exists->first_name . ' ' . $exists->last_name : 'NO') . "</p>";
            
            // Check agency assignment
            $agency_assigned = $db->where('candidate_id', $candidate_id)
                                 ->where('agency_id', $agency_id)
                                 ->get('candidate_agencies')
                                 ->row();
            echo "<p>Assigned to agency $agency_id: " . ($agency_assigned ? 'YES' : 'NO') . "</p>";
            
            // Check ALL agency assignments
            $all_agencies = $db->select('a.id, a.name')
                              ->from('candidate_agencies ca')
                              ->join('agencies a', 'a.id = ca.agency_id')
                              ->where('ca.candidate_id', $candidate_id)
                              ->get()
                              ->result();
            echo "<p>All agency assignments:</p>";
            if ($all_agencies) {
                echo "<ul>";
                foreach ($all_agencies as $agency) {
                    $current = ($agency->id == $agency_id) ? ' ✅ CURRENT' : '';
                    echo "<li>Agency ID: {$agency->id} - {$agency->name}{$current}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No agency assignments found</p>";
            }
            
            // Check job assignment
            $job_assigned = $db->select('cj.job_id, j.name as job_name')
                              ->from('candidate_jobs cj')
                              ->join('mod_jobs j', 'j.id = cj.job_id', 'left')
                              ->where('cj.candidate_id', $candidate_id)
                              ->get()
                              ->result();
            echo "<p>Job assignments:</p>";
            if ($job_assigned) {
                echo "<ul>";
                foreach ($job_assigned as $job) {
                    $current = ($job->job_id == $job_id) ? ' ✅ CURRENT SESSION JOB' : '';
                    echo "<li>Job ID: {$job->job_id} - {$job->job_name}{$current}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No job assignments found</p>";
            }
        }
        
        $db->close();
    }
    
    echo "<hr><p><a href='" . site_url('agency/candidates_list/index/' . $job_id) . "'>Back to Candidates List</a></p>";
}

}