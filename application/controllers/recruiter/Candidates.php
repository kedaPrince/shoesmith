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

    /**
     * Generate reference number via AJAX
     */
    public function generate_reference()
    {
        try {
            $reference = $this->{$this->model}->generate_reference_number();
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'reference' => $reference
                ]));
        } catch (Exception $e) {
            error_log('Error generating reference: ' . $e->getMessage());
            // Fallback
            $prefix = 'CAND';
            $year = date('Y');
            $count = $this->db->where('YEAR(created_at)', $year)
                             ->count_all_results('candidates');
            $sequence = $count + 1;
            $fallbackReference = $prefix . '-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'reference' => $fallbackReference
                ]));
        }
    }

    /**
     * Validate form - FIXED version
     */
    public function validate_form($action = 'create')
    {
        $this->load->library('form_validation');
        
        // Set validation rules based on formFields
        if (isset($this->formFields['main'])) {
            foreach ($this->formFields['main'] as $field => $rules) {
                $clean_field = preg_replace('/\|.*$/', '', $field);
                $this->form_validation->set_rules($clean_field, $this->get_field_label($field), $rules);
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

    /**
     * Get post data - FIXED version
     */
   public function get_post_data()
    {
        log_message('debug', '=== GET_POST_DATA STARTED ===');
        $data = array();
        
        // Process main fields
        if (isset($this->formFields['main'])) {
            foreach (array_keys($this->formFields['main']) as $field) {
                $clean_field = preg_replace('/\|.*$/', '', $field);
                
                // Skip fields that are handled separately
                if (in_array($clean_field, array('cv_file'))) {
                    continue;
                }
                
                $value = $this->input->post($clean_field);
                log_message('debug', "Field: {$clean_field}, Value: " . ($value === null ? 'NULL' : $value));
                
                if ($value !== null && $value !== '') {
                    // Handle date fields
                    if (in_array($clean_field, array('date_of_birth', 'application_date'))) {
                        $data[$clean_field] = date('Y-m-d', strtotime($value));
                    }
                    // Handle decimal fields
                    elseif (in_array($clean_field, array('current_salary', 'expected_salary'))) {
                        $data[$clean_field] = (float) $value;
                    }
                    // Handle numeric fields
                    elseif (in_array($clean_field, array('years_experience', 'notice_period', 'rating', 'agency_id', 'job_id', 'assigned_agent_id'))) {
                        $data[$clean_field] = (int) $value;
                    }
                    // Handle all other fields
                    else {
                        $data[$clean_field] = $value;
                    }
                } else {
                    // Set empty values to null
                    $data[$clean_field] = null;
                }
            }
        }
        
        log_message('debug', 'Final post data array: ' . print_r($data, true));
        log_message('debug', '=== GET_POST_DATA ENDED ===');
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

    /**
     * Create candidate - FIXED version
     */
    public function create()
    {
        if ($this->input->post()) {
            // Validate form
            if ($this->validate_form('create')) {
                // Get post data
                $data = $this->get_post_data();
                
                // Add extra parameters
                $extra_params = $this->create_extra_params();
                $data = array_merge($data, $extra_params);
                
                // Add created_at timestamp
                $data['created_at'] = date('Y-m-d H:i:s');
                
                // Insert the main record
               $id = $this->{$this->model}->create($data);
                
                if ($id) {
                    // Handle file upload
                    $this->handle_file_upload_manual($id);
                    
                    // Handle pivot tables
                    $this->handle_pivot_tables($id);
                    
                    // Send notifications to agencies
                    $this->send_agency_notifications($id);
                    
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

    /**
 * Update candidate - FIXED version
 */
public function update($id = null)
{
    log_message('debug', '=== UPDATE METHOD STARTED ===');
    log_message('debug', 'Candidate ID: ' . $id);
    log_message('debug', 'POST data: ' . print_r($this->input->post(), true));
    
    if ($this->input->post()) {
        log_message('debug', 'POST request detected');
        
        // Validate form
        if ($this->validate_form('update')) {
            log_message('debug', 'Form validation passed');
            
            // Get post data
            $data = $this->get_post_data();
            log_message('debug', 'Post data array: ' . print_r($data, true));
            log_message('debug', 'Data type: ' . gettype($data));
            log_message('debug', 'Is array: ' . (is_array($data) ? 'YES' : 'NO'));
            
            // Add extra parameters
            $extra_params = $this->update_extra_params($id);
            log_message('debug', 'Extra params: ' . print_r($extra_params, true));
            
            $data = array_merge($data, $extra_params);
            log_message('debug', 'Merged data: ' . print_r($data, true));
            
            // Add updated_at timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // CRITICAL: Ensure data is an array
            if (!is_array($data)) {
                log_message('error', 'DATA IS NOT AN ARRAY! Type: ' . gettype($data));
                $data = array(); // Force to empty array
            }
            
            log_message('debug', 'Final data before update: ' . print_r($data, true));
            
            // Update the main record - FIXED: Correct parameter order
            try {
                // CORRECTED: Pass parameters in the right order (data, id)
                $result = $this->{$this->model}->update($data, $id);
                log_message('debug', 'Update result: ' . ($result ? 'SUCCESS' : 'FAILED'));
                
                if ($result) {
                    // Handle file upload
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
            } catch (Exception $e) {
                log_message('error', 'Update exception: ' . $e->getMessage());
                if ($this->input->is_ajax_request()) {
                    echo json_encode(['success' => false, 'error' => 'Update failed: ' . $e->getMessage()]);
                    return;
                }
            }
        } else {
            log_message('debug', 'Form validation failed: ' . validation_errors());
        }
        
        // If we get here, there was an error
        if ($this->input->is_ajax_request()) {
            echo json_encode(['success' => false, 'error' => validation_errors()]);
            return;
        }
    } else {
        log_message('debug', 'No POST data received');
    }
    
    log_message('debug', '=== UPDATE METHOD ENDED ===');
    // Show the form
    $this->edit($id);
}

    /**
     * Send notifications to agencies when candidate is submitted
     */
   private function send_agency_notifications($candidate_id)
{
    $this->load->model('agency/Model_notifications');
    
    $additional_agencies = $this->input->post('additional_agency_ids') ?: [];
    $recruiter_id = $this->get_recruiter_id();
    $job_id = $this->input->post('job_id');
    
    foreach ($additional_agencies as $agency_id) {
        $this->Model_notifications->create_candidate_submission_notification(
            $candidate_id, 
            $agency_id, 
            $recruiter_id,
            $job_id
        );
    }
}
/**
     * Update HM Decision - AJAX endpoint
     */
    public function update_hm_decision()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $candidate_id = $this->input->post('candidate_id');
        $decision = $this->input->post('decision');
        $notes = $this->input->post('notes');

        // Validate inputs
        if (empty($candidate_id) || empty($decision)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        // Get candidate details
        $candidate = $this->{$this->model}->get_by_id($candidate_id);
        if (!$candidate) {
            echo json_encode(['success' => false, 'message' => 'Candidate not found']);
            return;
        }

        // Update candidate record
        $update_data = [
            'hm_decision' => $decision,
            'hm_decision_notes' => $notes,
            'hm_decision_at' => date('Y-m-d H:i:s'),
            'stage_hm_decision' => 1,
            'onboarding_stage' => 'stage_hm_decision',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $candidate_id);
        $result = $this->db->update('candidates', $update_data);

        if ($result) {
            // Send notifications to recruiters
            $this->load->model('recruiter/Model_notifications');
            $notification_sent = $this->Model_notifications->create_hm_decision_notification(
                $candidate_id,
                $candidate->job_id,
                $candidate->agency_id,
                $decision,
                $notes,
                $this->get_recruiter_id() // or hiring manager ID
            );

            echo json_encode([
                'success' => true,
                'message' => 'Decision recorded successfully',
                'notification_sent' => $notification_sent
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to record decision']);
        }
    }
    /**
     * Get the logged-in recruiter's ID
     */
    private function get_recruiter_id()
    {
        $login_data = $this->session->userdata('login');
        
        if (!empty($login_data['recruiter'])) {
            $recruiter = $login_data['recruiter'];
            return !empty($recruiter['id']) ? $recruiter['id'] : null;
        }
        
        return null;
    }

    private function handle_file_upload_manual($candidate_id)
    {
        if (!empty($_FILES['cv_file']['name']) && $_FILES['cv_file']['error'] == 0) {
            $upload_path = FCPATH . 'uploads/candidates/cv/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
            
            $filename = 'candidate_' . $candidate_id . '_' . time() . '_' . $_FILES['cv_file']['name'];
            $destination = $upload_path . $filename;
            
            if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $destination)) {
                // Update the candidate record with the filename
                $this->db->where('id', $candidate_id)
                         ->update('candidates', ['cv_file' => $filename]);
                return true;
            }
        }
        return true;
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
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function handle_pivot_tables($candidate_id)
    {
        // Handle agencies
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

        // Handle jobs
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