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
            'job_name' => array('label' => lang('label_job'), 'sort' => true, 'field' => 'mod_jobs.name'),
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
    }

    public function index()
    {
        // Get job_id from URL for filtering
        $job_id = $this->uri->segment(4); // agency/candidates_list/index/{job_id}
        $agency_id = $this->get_user_agency_id();
        
        if (!$job_id) {
            show_error('Job ID is required', 400);
        }
        
        // Verify the job belongs to the agency
        $job = $this->db->get_where('mod_jobs', [
            'id' => $job_id, 
            'agency_id' => $agency_id
        ])->row();
        
        if (!$job) {
            show_error('Job not found or you do not have permission to view candidates for this job', 404);
        }
        
        // Store job_id in session for filtering
        $this->session->set_userdata('current_job_id', $job_id);

        $this->breadcrumbs = array(
            array('title' => lang('jobs_listings_heading'), 'url' => site_url('agency/jobs_listings')),
            array('title' => 'Candidates for: ' . $job->name, 'url' => '#'),
        );

        $this->view = 'listing';
        $this->load->view($this->folder . '/view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading' => 'Candidates for: ' . $job->name,
            'noRows' => lang('candidates_no_rows'),
        ));
        $this->load->view($this->folder . '/view_footer');
    }

    /**
     * OVERRIDE the main listing method to force our filtering
     */
    public function listing()
    {
        $job_id = $this->session->userdata('current_job_id');
        $agency_id = $this->get_user_agency_id();
        
        log_message('debug', '=== CANDIDATES LISTING DEBUG ===');
        log_message('debug', 'Job ID: ' . $job_id);
        log_message('debug', 'Agency ID: ' . $agency_id);

        // Build the query with STRICT filtering
        $this->db->select('candidates.*, mod_jobs.name as job_name, mod_jobs.reference_number as job_ref');
        $this->db->from('candidates');
        $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
        
        // CRITICAL: Strict filtering - must match BOTH job_id AND agency_id
        $this->db->where('candidates.job_id', $job_id);
        $this->db->where('candidates.agency_id', $agency_id);
        $this->db->where('candidates.removed', 0);

        // Get total count
        $total_rows = $this->db->count_all_results('', false);
        
        // Handle sorting
        $sort_by = $this->input->get('sort_by');
        $sort_order = $this->input->get('sort_order');
        
        if ($sort_by && isset($this->listFields[$sort_by])) {
            $field = $this->listFields[$sort_by];
            $db_field = isset($field['field']) ? $field['field'] : "candidates.{$sort_by}";
            $this->db->order_by($db_field, $sort_order ?: 'ASC');
        } else {
            $this->db->order_by('candidates.application_date', 'DESC');
        }

        // Handle pagination
        $limit = $this->input->get('limit') ?: 30;
        $offset = $this->input->get('offset') ?: 0;
        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        
        log_message('debug', 'SQL: ' . $this->db->last_query());
        log_message('debug', 'Results: ' . $query->num_rows());
        log_message('debug', '=== END DEBUG ===');

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'rows' => $query->result(),
                'total' => $total_rows
            ]));
    }

    public function count_all()
    {
        $job_id = $this->session->userdata('current_job_id');
        $agency_id = $this->get_user_agency_id();
        
        if (!$job_id || !$agency_id) {
            return 0;
        }
        
        $this->db->from('candidates');
        $this->db->where('job_id', $job_id);
        $this->db->where('agency_id', $agency_id);
        $this->db->where('removed', 0);
        
        $count = $this->db->count_all_results();
        log_message('debug', 'Candidates count: ' . $count . ' for Job ID: ' . $job_id . ', Agency ID: ' . $agency_id);
        
        return $count;
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
        $agency_id = $this->get_user_agency_id();
        
        // Get candidate with STRICT agency and job verification
        $this->db->select('candidates.*, mod_jobs.name as job_name, mod_jobs.reference_number as job_ref');
        $this->db->from('candidates');
        $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
        $this->db->where('candidates.id', $id);
        $this->db->where('candidates.agency_id', $agency_id);
        $this->db->where('candidates.removed', 0);
        
        $candidate = $this->db->get()->row();

        if (!$candidate) {
            show_error('Candidate not found', 404);
        }

        $this->breadcrumbs = array(
            array('title' => lang('jobs_listings_heading'), 'url' => site_url('agency/jobs_listings')),
            array('title' => 'Candidates', 'url' => site_url('agency/candidates_list/index/' . $candidate->job_id)),
            array('title' => 'View Candidate', 'url' => '#'),
        );

        $data = array(
            'candidate' => $candidate,
            'heading' => 'Candidate Details: ' . $candidate->first_name . ' ' . $candidate->last_name
        );

        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/candidates_list/view', $data);
        $this->load->view($this->folder . '/view_footer');
    }
}