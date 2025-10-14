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
                'cv_file' => 'trim|strip_tags', 
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
                'upload_path' => FCPATH . 'uploads/candidates/cv/',
                'info' => '<strong>File Requirements:</strong><br/>File Type: PDF, DOC, DOCX<br/>Max Size: 10MB',
                'allowed_types' => 'pdf|doc|docx',
                'max_size' => 10240,
            ],
        );
    }
public function validate_form($action = 'create')
{
    $this->load->library('form_validation');
    
    // Set validation rules based on formFields
    if (isset($this->formFields['main'])) {
        foreach ($this->formFields['main'] as $field => $rules) {
            $this->form_validation->set_rules($field, $this->get_field_label($field), $rules);
        }
    }
    
    // Validate multi-select fields
    if (isset($this->formFields['multi_selects'])) {
        foreach ($this->formFields['multi_selects'] as $field => $config) {
            if (isset($config['validation'])) {
                $this->form_validation->set_rules($field . '[]', $this->get_field_label($field), $config['validation']);
            }
        }
    }
    
    return $this->form_validation->run();
}

public function get_post_data()
{
    $data = array();
    
    // Process main fields
    if (isset($this->formFields['main'])) {
        foreach (array_keys($this->formFields['main']) as $field) {
            // Skip fields that are handled separately
            if (in_array($field, array('cv_file'))) {
                continue;
            }
            
            $value = $this->input->post($field);
            
            // Handle date fields
            if (in_array($field, array('date_of_birth', 'application_date')) && !empty($value)) {
                $data[$field] = date('Y-m-d', strtotime($value));
            }
            // Handle decimal fields
            elseif (in_array($field, array('current_salary', 'expected_salary')) && !empty($value)) {
                $data[$field] = (float) $value;
            }
            // Handle numeric fields
            elseif (in_array($field, array('years_experience', 'notice_period', 'rating')) && !empty($value)) {
                $data[$field] = (int) $value;
            }
            // Handle empty strings for optional fields
            elseif ($value === '') {
                $data[$field] = null;
            }
            else {
                $data[$field] = $value;
            }
        }
    }
    
    // Handle file upload field separately
    if (!empty($_FILES['cv_file']['name'])) {
        // File upload will be handled in handle_file_upload_manual method
        // We don't set cv_file here as it will be updated after successful upload
    }
    
    return $data;
}

private function get_field_label($field)
{
    // Remove everything after | for field names like 'reference_number|label_reference_number'
    $clean_field = preg_replace('/\|.*$/', '', $field);
    
    if (isset($this->formLabels[$clean_field])) {
        return $this->formLabels[$clean_field];
    }
    
    // Check if we have a custom label in the field name
    if (strpos($field, '|') !== false) {
        $parts = explode('|', $field);
        if (count($parts) > 1) {
            return lang($parts[1]);
        }
    }
    
    // Fallback to field name
    return ucfirst(str_replace('_', ' ', $clean_field));
}
public function create()
{
    if ($this->input->post()) {
        // First validate the form
        $validation_passed = false;
        
        if (method_exists($this, 'validate_form')) {
            $validation_passed = $this->validate_form('create');
        } elseif (method_exists(get_parent_class($this), 'validate_form')) {
            $validation_passed = parent::validate_form('create');
        } else {
            // Fallback validation
            $validation_passed = $this->fallback_validate_form();
        }
        
        if ($validation_passed) {
            // Get post data
            if (method_exists($this, 'get_post_data')) {
                $data = $this->get_post_data();
            } elseif (method_exists(get_parent_class($this), 'get_post_data')) {
                $data = parent::get_post_data();
            } else {
                $data = $this->fallback_get_post_data();
            }
            
            // Add extra parameters
            $extra_params = $this->create_extra_params();
            $data = array_merge($data, $extra_params);
            
            // Add created_at timestamp
            $data['created_at'] = date('Y-m-d H:i:s');
            
            // Insert the main record - use create method instead of insert
            $id = $this->{$this->model}->create($data);
            
            if ($id) {
                // Handle file upload manually
                $this->handle_file_upload_manual($id);
                
                // Handle pivot tables
                $this->handle_pivot_tables($id);
                
                // Set success message
                $this->session->set_flashdata('success', lang('record_created'));
                
                if ($this->input->is_ajax_request()) {
                    echo json_encode(['success' => true, 'message' => lang('record_created'), 'id' => $id]);
                    return;
                } else {
                    redirect(redir($this->pageName, true));
                }
            }
        }
        
        // If we get here, there was an error
        if ($this->input->is_ajax_request()) {
            echo json_encode(['success' => false, 'error' => validation_errors()]);
            return;
        }
    }
    
    // Show the form
    $this->add();
}

public function update($id = null)
{
    if ($this->input->post()) {
        // First validate the form
        $validation_passed = false;
        
        if (method_exists($this, 'validate_form')) {
            $validation_passed = $this->validate_form('update');
        } elseif (method_exists(get_parent_class($this), 'validate_form')) {
            $validation_passed = parent::validate_form('update');
        } else {
            // Fallback validation
            $validation_passed = $this->fallback_validate_form();
        }
        
        if ($validation_passed) {
            // Get post data
            if (method_exists($this, 'get_post_data')) {
                $data = $this->get_post_data();
            } elseif (method_exists(get_parent_class($this), 'get_post_data')) {
                $data = parent::get_post_data();
            } else {
                $data = $this->fallback_get_post_data();
            }
            
            // Add extra parameters
            $extra_params = $this->update_extra_params($id);
            $data = array_merge($data, $extra_params);
            
            // Update the main record - CORRECTED PARAMETER ORDER
            $result = $this->{$this->model}->update($data, $id);
            
            if ($result) {
                // Handle file upload manually
                $this->handle_file_upload_manual($id);
                
                // Handle pivot tables
                $this->handle_pivot_tables($id);
                
                // Set success message
                $this->session->set_flashdata('success', lang('record_updated'));
                
                if ($this->input->is_ajax_request()) {
                    echo json_encode(['success' => true, 'message' => lang('record_updated')]);
                    return;
                } else {
                    redirect(redir($this->pageName, true));
                }
            }
        }
        
        // If we get here, there was an error
        if ($this->input->is_ajax_request()) {
            echo json_encode(['success' => false, 'error' => validation_errors()]);
            return;
        }
    }
    
    // Show the form
    $this->edit($id);
}

// Fallback methods
private function fallback_validate_form()
{
    $this->load->library('form_validation');
    if (isset($this->formFields['main'])) {
        foreach ($this->formFields['main'] as $field => $rules) {
            $this->form_validation->set_rules($field, ucfirst(str_replace('_', ' ', $field)), $rules);
        }
    }
    return $this->form_validation->run();
}

private function fallback_get_post_data()
{
    $data = array();
    if (isset($this->formFields['main'])) {
        foreach (array_keys($this->formFields['main']) as $field) {
            if ($field !== 'cv_file') { // Skip file fields
                $data[$field] = $this->input->post($field);
            }
        }
    }
    return $data;
}

    private function handle_file_upload_manual($candidate_id)
    {
        log_message('info', '=== FILE UPLOAD START for candidate: ' . $candidate_id . ' ===');
        
        if (!empty($_FILES['cv_file']['name']) && $_FILES['cv_file']['error'] == 0) {
            log_message('info', 'CV file detected: ' . $_FILES['cv_file']['name']);
            
            $upload_path = FCPATH . 'uploads/candidates/cv/';
            $filename = 'candidate_' . $candidate_id . '_' . time() . '_' . $_FILES['cv_file']['name'];
            $destination = $upload_path . $filename;
            
            log_message('info', 'Attempting to upload to: ' . $destination);
            
            if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $destination)) {
                log_message('info', 'File upload SUCCESS: ' . $filename);
                
                // Update the candidate record with the filename
                $this->db->where('id', $candidate_id)
                         ->update('candidates', ['cv_file' => $filename]);
                
                log_message('info', 'Database updated with filename: ' . $filename);
                
                return true;
            } else {
                $error = error_get_last();
                log_message('error', 'File upload FAILED: ' . $error['message']);
                return false;
            }
        } else {
            if (isset($_FILES['cv_file'])) {
                log_message('info', 'CV file error: ' . $_FILES['cv_file']['error']);
            } else {
                log_message('info', 'No CV file in FILES array');
            }
        }
        
        log_message('info', '=== FILE UPLOAD END ===');
        return true;
    }

    // Rest of your existing methods remain the same...
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
        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

    public function quick_manage_extra($id, $row): array
    {
        if (is_string($row) || $row === null) {
            $row = new stdClass();
            $row->id = 0;
            $row->job_id = null;
            $row->agency_id = null;
        }

        $pre_selected_job_id = $this->session->userdata('pre_selected_job_id');
        
        if (empty($id) && $pre_selected_job_id) {
            $job = $this->{$this->model}->get_job_by_id($pre_selected_job_id);
            if ($job) {
                $row->job_id = $job->id;
                $row->agency_id = $job->agency_id;
                $this->session->unset_userdata('pre_selected_job_id');
            }
        }

        $agencies = $this->{$this->model}->get_agencies_all();
        $jobs = $this->{$this->model}->get_jobs_all();

        $agents = [];
        if (!empty($row->agency_id)) {
            $agents = $this->{$this->model}->get_agency_agents_by_agency($row->agency_id);
        }

        $all_additional_agency_ids = !empty($id) ? $this->{$this->model}->get_candidate_additional_agencies($id) : [];
        $all_additional_job_ids = !empty($id) ? $this->{$this->model}->get_candidate_additional_jobs($id) : [];
        
        if (empty($all_additional_agency_ids) && !empty($row->agency_id)) {
            $all_additional_agency_ids[] = $row->agency_id;
        }
        if (empty($all_additional_job_ids) && !empty($row->job_id)) {
            $all_additional_job_ids[] = $row->job_id;
        }

        $additional_agency_options = $this->{$this->model}->get_additional_agency_options();
        $additional_job_options = $this->{$this->model}->get_additional_job_options();

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
        
        $primary_agency_id = !empty($additional_agencies) ? $additional_agencies[0] : null;
        $primary_job_id = !empty($additional_jobs) ? $additional_jobs[0] : null;

        return [
            'application_date' => $this->input->post('application_date') ?: date('Y-m-d H:i:s'),
            'enabled' => 1,
            'agency_id' => $primary_agency_id,
            'job_id' => $primary_job_id,
        ];
    }

    public function update_extra_params($id): array
    {
        $additional_agencies = $this->input->post('additional_agency_ids') ?: [];
        $additional_jobs = $this->input->post('additional_job_ids') ?: [];
        
        $primary_agency_id = !empty($additional_agencies) ? $additional_agencies[0] : null;
        $primary_job_id = !empty($additional_jobs) ? $additional_jobs[0] : null;

        return [
            'agency_id' => $primary_agency_id,
            'job_id' => $primary_job_id,
        ];
    }

    private function handle_pivot_tables($candidate_id)
    {
        $additional_agencies = $this->input->post('additional_agency_ids') ?: [];
        $this->db->where('candidate_id', $candidate_id)->delete('candidate_agencies');
        
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

        $additional_jobs = $this->input->post('additional_job_ids') ?: [];
        $this->db->where('candidate_id', $candidate_id)->delete('candidate_jobs');
        
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

    public function add($job_id = null)
    {
        if (empty($job_id)) {
            $job_id = $this->input->get('job_id');
        }
        
        if ($job_id && is_numeric($job_id)) {
            $this->session->set_userdata('pre_selected_job_id', $job_id);
        }
        
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