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
            'view' => array('label' => lang('label_view'), 'url' => url($this->pageName . '/view/{id}'), 'icon' => 'fa-eye', 'class' => 'view-row'),
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
        // Get ALL agencies and jobs (no filtering)
        $agencies = $this->{$this->model}->get_agencies_all();
        $jobs = $this->{$this->model}->get_jobs_all();

        // Get agents for selected agency (dynamic via JS later if needed)
        $agents = [];
        if (!empty($row->agency_id)) {
            $agents = $this->{$this->model}->get_agency_agents_by_agency($row->agency_id);
        }

        return [
            'agencies_all' => $agencies,
            'jobs_all' => $jobs,
            'agents_all' => $agents,
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
        return [
            'application_date' => $this->input->post('application_date') ?: date('Y-m-d H:i:s'),
            'enabled' => 1,
        ];
    }

    public function update_extra_params($id): array
    {
        return [];
    }

    public function get_agents($agency_id)
{
    $agents = $this->{$this->model}->get_agency_agents_by_agency((int)$agency_id);
    echo json_encode($agents);
}
}