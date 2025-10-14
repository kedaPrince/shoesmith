<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Candidates extends CRUD_Controller
{
    public $pageName = 'candidates';
    public $group = 'Candidates Management';
    public $folder = 'recruiter';
    public $model = 'Model_candidates';
    public $sorting = array('first_name' => 'ASC', 'last_name' => 'ASC');
    public $singular = 'candidate';
    public $plural = 'candidates';
    public $quickManage = true;
    public $identifierField = 'first_name';
    public $hideSubNav = false;
    public $quickManageSize = 3;

    public function __construct()
    {
        parent::__construct();
        $this->folder = 'recruiter';

        // Allow only recruiters
        $login_data = $this->session->userdata('login');
        if (empty($login_data['recruiter'])) {
            redirect('recruiter/dashboard');
        }

        $this->setup_listing();
        $this->setup_fields();
        $this->load->model($this->folder . '/' . $this->model);
        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }

    private function setup_listing(): void
    {
        $this->listFields = array(
            'reference_number' => array('label' => lang('label_reference_number'), 'sort' => true),
            'first_name' => array('label' => lang('label_first_name'), 'sort' => true),
            'last_name' => array('label' => lang('label_last_name'), 'sort' => true),
            'email' => array('label' => lang('label_email'), 'sort' => true),
            'agency_name' => array('label' => lang('label_agency'), 'sort' => true),
            'job_name' => array('label' => lang('label_job'), 'sort' => true),
            'status' => array('label' => lang('label_status'), 'sort' => true),
            'application_date' => array('label' => lang('label_application_date'), 'sort' => true, 'type' => 'date'),
        );

        $this->listActions = array(
            //'view' => array('label' => lang('label_view'), 'url' => url($this->pageName . '/view/{id}'), 'icon' => 'fa-eye', 'class' => 'view-row'),
            'edit' => array('label' => lang('label_edit'), 'url' => url($this->pageName . '/edit/{id}'), 'icon' => 'fa-edit', 'class' => 'edit-row'),
             'enable' => array(
                'label'     => lang('label_enable'),
                'url'       => url($this->pageName . '/enable/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'enable-row btn-enable',
                'function'  => (function ($str, $row) {
                    return ($row->enabled) ? false : $str;
                }),
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => url($this->pageName . '/disable/{id}'),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => (function ($str, $row) {
                    return (!$row->enabled) ? false : $str;
                }),
            ),
            'delete' => array('label' => lang('label_delete'), 'url' => url($this->pageName . '/remove/{id}'), 'icon' => 'fa-trash-o', 'class' => 'delete-row btn-delete'),
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

    public function setup_fields(): void
    {
        $this->formFields = array(
            'main' => array(
                'reference_number' => 'trim|required|strip_tags',
                'first_name' => 'trim|required|strip_tags',
                'last_name' => 'trim|required|strip_tags',
                'email' => 'trim|required|valid_email|callback_is_unique_email',
                'phone' => 'trim|strip_tags',
                'id_number' => 'trim|strip_tags',
                'date_of_birth' => 'trim|valid_date',
                'gender' => 'trim|strip_tags',
                'address' => 'trim|strip_tags',
                'city' => 'trim|strip_tags',
                'province' => 'trim|strip_tags',
                'postal_code' => 'trim|strip_tags',
                'country' => 'trim|strip_tags',
                'highest_qualification' => 'trim|strip_tags',
                'years_experience' => 'trim|numeric',
                'current_position' => 'trim|strip_tags',
                'current_company' => 'trim|strip_tags',
                'current_salary' => 'trim|decimal',
                'expected_salary' => 'trim|decimal',
                'notice_period' => 'trim|numeric',
                'cover_letter' => 'trim|strip_tags',
                'source' => 'trim|strip_tags',
                'status' => 'trim|required|strip_tags',
                'rating' => 'trim|numeric',
                'notes' => 'trim|strip_tags',
                'agency_id' => 'trim|required|numeric',
                'job_id' => 'trim|numeric',
                'assigned_agent_id' => 'trim|numeric',
            ),
            'multi_selects' => array(
                'additional_agency_ids' => array(
                      'validation' => 'trim|required',
                    'pivot_table' => 'candidate_agencies',
                    'main_field' => 'candidate_id',
                    'link_field' => 'agency_id',
                ),
                'additional_job_ids' => array(
                    'validation' => 'trim',
                    'pivot_table' => 'candidate_jobs',
                    'main_field' => 'candidate_id',
                    'link_field' => 'job_id',
                ),
            ),
        );

        $this->formLabels = array();
        $this->uploaders = array(
            'cv_file' => [
                'type' => 'single_file',
                'table' => 'candidates',
                'info' => '<strong>File Requirements:</strong><br/>File Type: PDF, DOC, DOCX<br/>Max Size: 10MB',
            ],
        );
    }

    public function index(): void
    {
        $this->breadcrumbs = array(
            array('title' => lang($this->pageName . '_heading'), 'url' => redir($this->pageName, true)),
        );
        $this->view = 'listing';
        $this->load->view($this->folder . '/view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading' => lang($this->pageName . '_heading'),
            'noRows' => lang($this->pageName . '_no_rows'),
        ));
        $this->load->view($this->folder . '/view_footer');
    }

    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
    {
        // Recruiters see ALL candidates they submitted (no agency filter)
        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

public function quick_manage_extra($id, $row): array
{
    // Handle the case where $row might be a string (empty) for new records
    if (is_string($row) || $row === null) {
        $row = new stdClass();
        $row->id = 0;
        $row->job_id = null;
        $row->agency_id = null;
    }

    // Check for pre-selected job from session (only for new candidates)
    $pre_selected_job_id = $this->session->userdata('pre_selected_job_id');
    
    if (empty($id) && $pre_selected_job_id) {
        // We're creating a new candidate with a pre-selected job
        $job = $this->{$this->model}->get_job_by_id($pre_selected_job_id);
        if ($job) {
            // Update the row object with pre-selected values
            $row->job_id = $job->id;
            $row->agency_id = $job->agency_id;
            
            // Clear the session after use
            $this->session->unset_userdata('pre_selected_job_id');
        }
    }

    // Get ALL agencies and jobs (no filtering)
    $agencies = $this->{$this->model}->get_agencies_all();
    $jobs = $this->{$this->model}->get_jobs_all();

    // Get agents for selected agency
    $agents = [];
    if (!empty($row->agency_id)) {
        $agents = $this->{$this->model}->get_agency_agents_by_agency($row->agency_id);
    }

    // Get ALL selected agencies and jobs (including primary)
    $all_additional_agency_ids = !empty($id) ? $this->{$this->model}->get_candidate_additional_agencies($id) : [];
    $all_additional_job_ids = !empty($id) ? $this->{$this->model}->get_candidate_additional_jobs($id) : [];
    
    // If no existing data but we have row data (like from job pre-selection), use that
    if (empty($all_additional_agency_ids) && !empty($row->agency_id)) {
        $all_additional_agency_ids[] = $row->agency_id;
    }
    if (empty($all_additional_job_ids) && !empty($row->job_id)) {
        $all_additional_job_ids[] = $row->job_id;
    }

    // Get options for multi-selects (all agencies/jobs)
    $additional_agency_options = $this->{$this->model}->get_additional_agency_options();
    $additional_job_options = $this->{$this->model}->get_additional_job_options();

    // Convert objects to arrays for the helper
    $agency_options_array = [];
    if (!empty($additional_agency_options)) {
        foreach ($additional_agency_options as $agency) {
            $agency_options_array[] = [
                'id' => $agency->id,
                'name' => $agency->name
            ];
        }
    }

    $job_options_array = [];
    if (!empty($additional_job_options)) {
        foreach ($additional_job_options as $job) {
            $job_options_array[] = [
                'id' => $job->id,
                'name' => $job->name . ' (' . $job->reference_number . ')'
            ];
        }
    }

    return [
        'agencies_all' => $agencies,
        'jobs_all' => $jobs,
        'agents_all' => $agents,
        'additional_agency_options' => $agency_options_array,
        'additional_job_options' => $job_options_array,
        'additional_agency_ids' => $all_additional_agency_ids,
        'additional_job_ids' => $all_additional_job_ids,
        'primary_agency_id' => $row->agency_id ?? null,
        'primary_job_id' => $row->job_id ?? null,
    ];
}

    public function is_unique_email(string $email): bool
    {
        $id = $this->input->post('id');
        $this->form_validation->set_message('is_unique_email', lang('email_exists'));
        return $this->{$this->model}->is_unique_email($email, $id);
    }

public function create_extra_params(): array
{
    $additional_agencies = $this->input->post('additional_agency_ids') ?: [];
    $additional_jobs = $this->input->post('additional_job_ids') ?: [];
    
    // First selected becomes primary
    $primary_agency_id = !empty($additional_agencies) ? $additional_agencies[0] : null;
    $primary_job_id = !empty($additional_jobs) ? $additional_jobs[0] : null;

    return [
        'application_date' => $this->input->post('application_date') ?: date('Y-m-d H:i:s'),
        'enabled' => 1,
        'agency_id' => $primary_agency_id,  // First agency becomes primary
        'job_id' => $primary_job_id,        // First job becomes primary
        // REMOVE the multi-select arrays from here - they're handled by pivot tables
    ];
}

public function update_extra_params($id): array
{
    $additional_agencies = $this->input->post('additional_agency_ids') ?: [];
    $additional_jobs = $this->input->post('additional_job_ids') ?: [];
    
    // First selected becomes primary
    $primary_agency_id = !empty($additional_agencies) ? $additional_agencies[0] : null;
    $primary_job_id = !empty($additional_jobs) ? $additional_jobs[0] : null;

    return [
        'agency_id' => $primary_agency_id,  // Update primary agency
        'job_id' => $primary_job_id,        // Update primary job
        // REMOVE the multi-select arrays from here - they're handled by pivot tables
    ];
}


public function after_create($id, $data)
{
    // Handle multi-select pivot tables after main record is created
    $this->handle_pivot_tables($id);
    return parent::after_create($id, $data);
}

public function after_update($id, $data)
{
    // Handle multi-select pivot tables after main record is updated
    $this->handle_pivot_tables($id);
    return parent::after_update($id, $data);
}

private function handle_pivot_tables($candidate_id)
{
    // Handle agency pivot table
    $additional_agencies = $this->input->post('additional_agency_ids') ?: [];
    
    // Clear existing agency associations
    $this->db->where('candidate_id', $candidate_id)->delete('candidate_agencies');
    
    // Insert new agency associations
    if (!empty($additional_agencies)) {
        $agency_data = [];
        foreach ($additional_agencies as $agency_id) {
            $agency_data[] = [
                'candidate_id' => $candidate_id,
                'agency_id' => $agency_id,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        $this->db->insert_batch('candidate_agencies', $agency_data);
    }

    // Handle job pivot table
    $additional_jobs = $this->input->post('additional_job_ids') ?: [];
    
    // Clear existing job associations
    $this->db->where('candidate_id', $candidate_id)->delete('candidate_jobs');
    
    // Insert new job associations
    if (!empty($additional_jobs)) {
        $job_data = [];
        foreach ($additional_jobs as $job_id) {
            $job_data[] = [
                'candidate_id' => $candidate_id,
                'job_id' => $job_id,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        $this->db->insert_batch('candidate_jobs', $job_data);
    }
}
// Add this method to your Candidates controller
public function add($job_id = null)
{
    // Check if we have a job_id from URL or from query string
    if (empty($job_id)) {
        $job_id = $this->input->get('job_id');
    }
    
    // Store the pre-selected job ID in session
    if ($job_id && is_numeric($job_id)) {
        $this->session->set_userdata('pre_selected_job_id', $job_id);
    }
    
    // Call parent add method which will handle the quick manage display
    parent::add();
}
    public function get_agents($agency_id)
    {
        $agents = $this->{$this->model}->get_agency_agents_by_agency((int)$agency_id);
        echo json_encode($agents);
    }

    public function view($id = null)
{
    if (empty($id) || !is_numeric($id)) {
        show_404();
    }

    $row = $this->{$this->model}->get_by_id($id);
    if (empty($row) || $row->removed) {
        show_404();
    }

    // Optional: Ensure the candidate belongs to this recruiter (if needed)
    // But you said recruiters see all they submitted, so maybe skip agency check

    $this->breadcrumbs = [
        ['title' => lang($this->pageName . '_heading'), 'url' => redir($this->pageName, true)],
        ['title' => htmlspecialchars($row->first_name . ' ' . $row->last_name, ENT_QUOTES, 'UTF-8'), 'url' => ''],
    ];

    $this->load->view($this->folder . '/view_header');
    $this->load->view('cms/crud/view_single', [
        'row' => $row,
        'heading' => lang('view_candidate_heading'),
    ]);
    $this->load->view($this->folder . '/view_footer');
}
}