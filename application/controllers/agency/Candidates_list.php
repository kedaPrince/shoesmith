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
    $job_id = $this->uri->segment(4); // Optional: get job_id from URL for context
    $agency_id = $this->get_user_agency_id();
    
    if (!$agency_id) {
        show_error('Access denied', 403);
    }

    // Verify candidate is assigned to this agency AND (if job_id given) to the job
    $this->db->select('c.*, j.name as job_name, j.reference_number as job_ref, a.name as agency_name');
    $this->db->from('candidates c');
    $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
    $this->db->join('agencies a', 'a.id = c.agency_id', 'left');
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
    $this->db->where('c.id', $id);
    $this->db->where('ca.agency_id', $agency_id);
    $this->db->where('c.removed', 0);
    
    if ($job_id) {
        $this->db->join('candidate_jobs cj', 'cj.candidate_id = c.id', 'inner');
        $this->db->where('cj.job_id', $job_id);
    }

    $candidate = $this->db->get()->row();

    if (!$candidate) {
        show_error('Candidate not found', 404);
    }

    $data = array(
        'candidate' => $candidate,
        'heading' => 'Candidate Details: ' . $candidate->first_name . ' ' . $candidate->last_name,
        'breadcrumbs' => array(
            array('title' => 'Jobs', 'url' => site_url('agency/jobs_listings')),
            array('title' => 'Candidates', 'url' => $job_id ? site_url("agency/candidates_list/index/{$job_id}") : site_url('agency/candidates_list')),
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

public function debug_job_candidates($job_id = null)
{
    if (empty($job_id)) {
        echo "<h2>Usage: /agency/candidates_list/debug_job_candidates/{job_id}</h2>";
        echo "<p>Example: <a href='" . site_url('agency/candidates_list/debug_job_candidates/8') . "'>/agency/candidates_list/debug_job_candidates/8</a></p>";
        return;
    }

    // Get job details
    $job = $this->db->get_where('mod_jobs', ['id' => $job_id])->row();
    if (!$job) {
        echo "<h2>Job ID $job_id not found</h2>";
        return;
    }

    echo "<h2>Debug: Candidates Assigned to Job ID: $job_id</h2>";
    echo "<h3>Job: " . htmlspecialchars($job->name, ENT_QUOTES, 'UTF-8') . " (Ref: " . ($job->reference_number ?: 'N/A') . ")</h3>";

    // Get candidates via pivot table
    $candidates = $this->db->select('c.*, a.name as agency_name')
                           ->from('candidate_jobs cj')
                           ->join('candidates c', 'c.id = cj.candidate_id')
                           ->join('agencies a', 'a.id = c.agency_id', 'left')
                           ->where('cj.job_id', $job_id)
                           ->where('c.removed', 0)
                           ->get()
                           ->result();

    if ($candidates) {
        echo "<h4>Candidates linked via `candidate_jobs`:</h4>";
        echo "<ul>";
        foreach ($candidates as $c) {
            // Get all agencies this candidate is assigned to
            $agencies = $this->db->select('ag.name')
                                 ->from('candidate_agencies ca')
                                 ->join('agencies ag', 'ag.id = ca.agency_id')
                                 ->where('ca.candidate_id', $c->id)
                                 ->get()
                                 ->result();
            $agency_list = implode(', ', array_column($agencies, 'name'));

            echo "<li>";
            echo "<strong>ID: {$c->id} | {$c->first_name} {$c->last_name} | Ref: {$c->reference_number}</strong><br>";
            echo "<small>";
            echo "Primary Agency: " . ($c->agency_name ?: 'None') . "<br>";
            echo "Assigned to agencies: " . ($agency_list ?: 'None') . "<br>";
            echo "Status: {$c->status}";
            echo "</small>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p><em>No candidates found in `candidate_jobs` for job ID $job_id.</em></p>";
    }

    // Also show candidates where candidates.job_id = job_id (primary link)
    $primary_candidates = $this->db->where('job_id', $job_id)
                                   ->where('removed', 0)
                                   ->get('candidates')
                                   ->result();

    if ($primary_candidates) {
        echo "<h4>Candidates with `candidates.job_id = $job_id` (Primary):</h4>";
        echo "<ul>";
        foreach ($primary_candidates as $c) {
            echo "<li>ID: {$c->id} | {$c->first_name} {$c->last_name} | Ref: {$c->reference_number}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p><em>No candidates with primary `job_id = $job_id`.</em></p>";
    }

    echo "<hr><p><em>Debug generated at " . date('Y-m-d H:i:s') . "</em></p>";
}

public function debug_current_job_candidates()
{
    $job_id = $this->session->userdata('current_job_id');
    $agency_id = $this->get_user_agency_id();
    
    echo "<h2>Current Session Debug</h2>";
    echo "<p>Current Job ID in session: " . ($job_id ?: 'NOT SET') . "</p>";
    echo "<p>Current Agency ID: " . ($agency_id ?: 'NOT SET') . "</p>";
    
    if ($job_id && $agency_id) {
        echo "<h3>Candidates that should show for job ID $job_id:</h3>";
        
        $this->db->select('c.id, c.first_name, c.last_name, c.reference_number, j.name as job_name, j.id as job_id');
        $this->db->from('candidates c');
        $this->db->join('candidate_jobs cj', 'cj.candidate_id = c.id', 'inner');
        $this->db->join('mod_jobs j', 'j.id = cj.job_id', 'left');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
        $this->db->where('cj.job_id', (int)$job_id);
        $this->db->where('ca.agency_id', (int)$agency_id);
        $this->db->where('c.removed', 0);
        
        $results = $this->db->get()->result();
        
        if ($results) {
            echo "<ul>";
            foreach ($results as $row) {
                echo "<li>ID: {$row->id} | {$row->first_name} {$row->last_name} | Job: {$row->job_name} (ID: {$row->job_id})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No candidates found with current filters.</p>";
        }
        
        // Show the actual query
        echo "<h3>Actual Query:</h3>";
        echo "<pre>" . $this->db->last_query() . "</pre>";
    }
}

public function debug_ajax_query()
{
    $job_id = $this->session->userdata('current_job_id');
    $agency_id = $this->get_user_agency_id();

    echo "<h2>AJAX Query Debug</h2>";
    echo "<p>Job ID: " . ($job_id ?: 'NOT SET') . "</p>";
    echo "<p>Agency ID: " . ($agency_id ?: 'NOT SET') . "</p>";

    if ($job_id && $agency_id) {
        $this->db->select('c.*, j.name as job_name, j.reference_number as job_ref');
        $this->db->from('candidates c');
        $this->db->join('candidate_jobs cj', 'cj.candidate_id = c.id', 'inner');
        $this->db->join('mod_jobs j', 'j.id = cj.job_id', 'left');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
        $this->db->where('cj.job_id', (int)$job_id);
        $this->db->where('ca.agency_id', (int)$agency_id);
        $this->db->where('c.removed', 0);

        $query = $this->db->get();
        
        echo "<h3>Query Results (" . $query->num_rows() . " rows):</h3>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Job</th><th>Reference</th></tr>";
        foreach ($query->result() as $row) {
            echo "<tr>";
            echo "<td>{$row->id}</td>";
            echo "<td>{$row->first_name} {$row->last_name}</td>";
            echo "<td>{$row->email}</td>";
            echo "<td>{$row->job_name}</td>";
            echo "<td>{$row->reference_number}</td>";
            echo "</tr>";
        }
        echo "</table>";

        echo "<h3>SQL Query:</h3>";
        echo "<pre>" . $this->db->last_query() . "</pre>";
    }
}
public function test_parent_method()
{
    echo "<h2>Testing Parent vs Child Methods</h2>";
    
    // Test what the parent get_all returns
    $parent_results = parent::get_all();
    echo "<p>Parent get_all returns: " . $parent_results->num_rows() . " rows</p>";
    
    // Test what your get_all returns
    $your_results = $this->get_all();
    echo "<p>Your get_all returns: " . $your_results->num_rows() . " rows</p>";
    
    echo "<h3>Parent Query:</h3>";
    echo "<pre>" . $this->db->last_query() . "</pre>";
}
}