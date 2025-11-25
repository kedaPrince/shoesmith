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

    public function index($job_id = null)
{
    // Always get job_id from URL (segment 4), never rely only on session
    $job_id = $job_id ?: $this->uri->segment(4);
    $agency_id = $this->get_user_agency_id();

    if (!$job_id || !$agency_id) {
        show_error('Job ID is required', 400);
    }

    $job = $this->db->get_where('mod_jobs', ['id' => $job_id, 'agency_id' => $agency_id])->row();
    if (!$job) {
        show_error('Job not found or access denied', 404);
    }

    // Only set session for backward compatibility (optional)
    $this->session->set_userdata('current_job_id', (int)$job_id);

    $this->breadcrumbs = [
        ['title' => lang('jobs_listings_heading'), 'url' => site_url('agency/jobs_listings')],
        ['title' => 'Candidates for: ' . $job->name, 'url' => '#'],
    ];

    $this->view = 'listing';
    $this->load->view($this->folder . '/view_header');
    $this->load->view('cms/crud/view_list', [
        'heading' => 'Candidates for: ' . $job->name,
        'noRows'  => lang('candidates_no_rows'),
    ]);
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

    // Get job_id from URL (segment 5) or session
    $job_id = $this->uri->segment(5) ?: $this->session->userdata('current_job_id');
    if (!$job_id) {
        show_error('Job context missing', 400);
    }

    $this->db->select('c.*,
                       j.name as job_name,
                       j.reference_number as job_ref,
                       a.name as agency_name,
                       COALESCE(cja.status, "submitted") as status,
                       COALESCE(cja.assigned_at, cj.created_at, c.application_date) as application_date');
    $this->db->from('candidates c');
    $this->db->join('candidate_job_assignments cja',
                   'cja.candidate_id = c.id AND cja.job_id = ' . (int)$job_id . ' AND cja.removed = 0', 'left');
    $this->db->join('candidate_jobs cj',
                   'cj.candidate_id = c.id AND cj.job_id = ' . (int)$job_id, 'left');
    $this->db->join('mod_jobs j', 'j.id = ' . (int)$job_id, 'left');
    $this->db->join('agencies a', 'a.id = c.agency_id', 'left');
    $this->db->where('c.id', $candidate_id);
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
        'job_id'    => $job_id,  // Pass job_id to view
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
}