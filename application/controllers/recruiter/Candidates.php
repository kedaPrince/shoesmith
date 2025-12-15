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
        $this->perPage = 10; 

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
        
        // Load chat model for recruiter
        $this->load->model('recruiter/Model_chat_messages');
        // Load notifications model
        $this->load->model('recruiter/Model_notifications');

        log_message('debug', 'Candidates controller loaded with perPage: ' . $this->perPage);
    }


// ========== UPDATE LISTING CONFIGURATION TO USE UUID ==========
    private function setup_listing(): void
    {
        $this->listFields = array(
            'reference_number' => array(
                'label' => lang('label_reference_number'),
                'sort' => true,
            ),
            'first_name' => array(
                'label' => lang('label_first_name'),
                'sort' => true,
            ),
            'email' => array(
                'label' => lang('label_email'),
                'sort' => true,
            ),
            'job_name' => array(
                'label' => lang('label_job'),
                'sort' => true,
                'field' => 'mod_jobs.name'
            ),
            'status' => array(
                'label' => lang('label_status'),
                'sort' => true,
            ),
            'application_date' => array(
                'label' => lang('label_application_date'),
                'sort' => true,
                'type' => 'date',
            ),
        );

        $this->listActions = array(
            'view' => array(
                'label'     => lang('label_view'),
                'url'       => site_url('recruiter/candidates/view/{uuid}'), // CHANGED: Use UUID
                'icon'      => 'fa-eye',
                'class'     => 'view-row',
                'title'     => 'View detailed candidate profile',
            ),
            'edit' => array(
                'label'     => lang('label_edit'),
                'url'       => site_url('recruiter/candidates/edit/{uuid}'), // CHANGED: Use UUID
                'icon'      => 'fa-edit',
                'class'     => 'edit-row',
                'title'     => 'Edit candidate information',
            ),
            // CHAT ACTION FOR RECRUITER
            'chat' => array(
                'label'     => 'Chat',
                'url'       => site_url('recruiter/candidates/start_candidate_chat/{uuid}'), // CHANGED: Use UUID
                'icon'      => 'fa-comments',
                'class'     => 'chat-row',
                'title'     => 'Chat with agency about this candidate',
                'target'    => '_blank'
            ),
        );

        $this->filters = array(
               'general' => array(  // CHANGE 'search' to 'general'
            'label' => lang('label_search'),
            'type' => 'autocomplete',
            'field' => array(
                'candidates.first_name',
                'candidates.last_name', 
                'candidates.email',
                'candidates.reference_number',
            ),
        ),
        'status' => array(
            'label' => lang('label_status'),
            'type' => 'dropdown',
            'field' => 'candidates.status',
            'options' => array(
                '' => 'All Statuses',  // ADD empty option
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
                    'reference_number'  => 'trim|required|strip_tags',
                    'first_name'        => 'trim|required|strip_tags',
                    'last_name'         => 'trim|required|strip_tags',
                    'email'             => 'trim|required|valid_email|strip_tags',
                    'phone'             => 'trim|strip_tags',
                    'status'            => 'trim|required|strip_tags',
                    'rating'            => 'trim|numeric',
                    'notes'             => 'trim|strip_tags',
                ),
            );

            $this->formLabels = array();

            $this->uploaders = array(
                'cv_file' => [
                    'type'      => 'single_file',
                    'table'     => 'candidates',
                    'info'      => '<strong>File Requirements:</strong><br/>File Type: PDF, DOC, DOCX<br/>Max Size: 10MB',
                ],
            );
        }

/**
 * Check if recruiter has access to this candidate
 */
private function check_recruiter_candidate_access($candidate) {
    $recruiter_id = $this->get_recruiter_id();
    
    if (!$recruiter_id) {
        return false;
    }
    
    // DEBUG: Log for testing
    error_log("Access check - Recruiter ID: $recruiter_id, Candidate Agent ID: " . ($candidate->assigned_agent_id ?? 'NULL'));
    
    // Check if candidate belongs to this recruiter
    return isset($candidate->assigned_agent_id) && ($candidate->assigned_agent_id == $recruiter_id);
}

/**
 * Enforce access control
 */
private function enforce_recruiter_candidate_access($candidate_or_identifier, $is_ajax = false) {
    // Handle both candidate object or identifier
    if (is_string($candidate_or_identifier) || is_numeric($candidate_or_identifier)) {
        // It's an identifier, get the candidate
        $candidate = $this->{$this->model}->get_candidate($candidate_or_identifier);
    } else {
        // It's already a candidate object
        $candidate = $candidate_or_identifier;
    }
    
    if (!$candidate) {
        if ($is_ajax) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'message' => 'Candidate not found'
                ]));
        } else {
            show_404();
        }
        return false;
    }
    
    // Now check access
    if (!$this->check_recruiter_candidate_access($candidate)) {
        error_log("ACCESS DENIED - Recruiter tried to access candidate they don't own");
        
        if ($is_ajax) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'message' => 'Access denied to this candidate'
                ]));
        } else {
            show_error('Access denied', 403);
        }
        return false;
    }
    
    error_log("ACCESS GRANTED - Recruiter can access candidate");
    return true;
}
    /**
     * Upload document for candidate (Recruiter)
     */
    public function upload_document()
        {
               $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->post($csrf_name);
    
    if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
        ajax_return(['success' => false, 'message' => 'Invalid CSRF token. Please refresh and try again.']);
        return;
    }
            $candidate_id = $this->input->post('candidate_id');
            $document_name = $this->input->post('document_name');
            $document_type = $this->input->post('document_type');
            $description = $this->input->post('description');

            // Check if file was uploaded
            if (empty($_FILES['document_file']['name'])) {
                ajax_return(['success' => false, 'message' => 'Please select a file to upload.']);
                return;
            }

            // Upload configuration
            $config['upload_path'] = './uploads/candidate_documents/';
            $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
            $config['max_size'] = 10240; // 10MB
            $config['encrypt_name'] = true;

            // Create upload directory if it doesn't exist
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('document_file')) {
                ajax_return(['success' => false, 'message' => $this->upload->display_errors()]);
                return;
            }

            $upload_data = $this->upload->data();

            // Save document to database
            $document_data = [
                'candidate_id' => $candidate_id,
                'document_name' => $document_name,
                'file_name' => $upload_data['file_name'],
                'file_path' => 'uploads/candidate_documents/' . $upload_data['file_name'],
                'file_size' => $upload_data['file_size'],
                'file_type' => $upload_data['file_type'],
                'uploaded_by' => loginID('recruiter'),
                'uploaded_by_type' => 'recruiter',
                'document_type' => $document_type,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->{$this->model}->save_candidate_document($document_data);

            if ($result) {
                // Send notification to agency
                $this->load->model('recruiter/Model_notifications');
                $this->Model_notifications->create_documents_uploaded_notification($candidate_id, loginID('recruiter'), 1);

                ajax_return(['success' => true, 'message' => 'Document uploaded successfully!']);
            } else {
                ajax_return(['success' => false, 'message' => 'Failed to save document information.']);
            }
        }

    /**
     * Get documents for candidate (Recruiter)
     */
    public function get_documents($candidate_id)
        {
            $documents = $this->{$this->model}->get_candidate_documents($candidate_id);
            
            $html = '';
            if (!empty($documents)) {
                foreach ($documents as $doc) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($doc->document_name, ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '<td><span class="badge badge-info">' . ucfirst(str_replace('_', ' ', $doc->document_type)) . '</span></td>';
                    $html .= '<td>' . date('M j, Y', strtotime($doc->created_at)) . '</td>';
                    $html .= '<td>' . $this->format_file_size($doc->file_size) . '</td>';
                    $html .= '<td><span class="badge badge-success">Uploaded</span></td>';
                    $html .= '<td>';
                    $html .= '<a href="' . base_url($doc->file_path) . '" target="_blank" class="btn btn-sm btn-primary" title="Download"><i class="fa fa-download"></i></a>';
                    $html .= '<button onclick="deleteDocument(' . $doc->id . ')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fa fa-trash"></i></button>';
                    $html .= '</td>';
                    $html .= '</tr>';
                }
            } else {
                $html = '<tr><td colspan="6" class="text-center text-muted">No documents uploaded yet.</td></tr>';
            }
            
            echo $html;
        }

    /**
     * Format file size
     */
    private function format_file_size($bytes)
        {
            if ($bytes >= 1073741824) {
                return number_format($bytes / 1073741824, 2) . ' GB';
            } elseif ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            } elseif ($bytes > 1) {
                return $bytes . ' bytes';
            } elseif ($bytes == 1) {
                return '1 byte';
            } else {
                return '0 bytes';
            }
        }

    /**
     * Delete document
     */
    public function delete_document($document_id)
        {
              // ✅ ADD CSRF VALIDATION
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            ajax_return(['success' => false, 'message' => 'Invalid CSRF token. Please refresh and try again.']);
            return;
        }
            // Verify the document belongs to a candidate that this recruiter has access to
            $document = $this->{$this->model}->get_document($document_id);
            
            if (!$document) {
                ajax_return(['success' => false, 'message' => 'Document not found.']);
                return;
            }
            
            // Verify recruiter has access to this candidate
            $recruiter_id = loginID('recruiter');
            $has_access = $this->{$this->model}->check_recruiter_candidate_access($recruiter_id, $document->candidate_id);
            
            if (!$has_access) {
                ajax_return(['success' => false, 'message' => 'Access denied.']);
                return;
            }
            
            // Delete file from server
            if (file_exists($document->file_path)) {
                unlink($document->file_path);
            }
            
            // Delete from database
            $result = $this->{$this->model}->delete_candidate_document($document_id);
            
            if ($result) {
                ajax_return(['success' => true, 'message' => 'Document deleted successfully.']);
            } else {
                ajax_return(['success' => false, 'message' => 'Failed to delete document.']);
            }
        }

public function index(): void
{
    // Get job_id from URL parameters for filtering
    $job_id = $this->input->get('job_id');
    
    $this->breadcrumbs = array(
        array(
            'title' => lang($this->pageName . '_heading'),
            'url'   => redir($this->pageName, true)
        ),
    );
    
    // Load the view_list_extra for chat functionality
    $this->view = 'listing';
    $this->load->view('recruiter/candidates/view_list_extra');
    
    // ========== Get total candidates count ==========
    $filters = $this->get_filters_from_session();
    $this->{$this->model}->set_current_filters($filters);
    $total_candidates = $this->{$this->model}->count_all();
    
    // Get current page
    $page = $this->input->get('page') ?: 1;
    
    $this->load->view($this->folder . '/' . 'view_header');
    $this->load->view('cms/crud/view_list', array(
        'heading'           => lang($this->pageName . '_heading'),
        'noRows'            => lang($this->pageName . '_no_rows'),
        'filter_job_id'     => $job_id,
        'total_items'       => $total_candidates,
        'current_page'      => $page,
        'per_page'          => 10, // Explicitly set per page
    ));
    $this->load->view($this->folder . '/' . 'view_footer');
}


public function ajax_get_total_count()
{
    if (!$this->input->is_ajax_request()) {
        show_404();
    }
    
    // Get filters
    $filters = $this->get_filters_from_session();
    $this->{$this->model}->set_current_filters($filters);
    
    // Get total count
    $total = $this->{$this->model}->count_all();
    
    $this->output->set_content_type('application/json')
                 ->set_output(json_encode(['total' => $total]));
}
  public function edit($uuid_or_id = null)
{
    // ✅ ADD ACCESS CONTROL
    if ($uuid_or_id && !$this->enforce_recruiter_candidate_access($uuid_or_id)) {
        return;
    }
    
    // Call parent method
    parent::edit($uuid_or_id);
}  
// ========== UPDATE VIEW METHOD TO ACCEPT UUID ==========
public function view($uuid_or_id = null)
{
    if (!$uuid_or_id) {
        show_404();
    }

    // Get candidate - the model will handle both UUID and ID
    $row = $this->{$this->model}->get_candidate($uuid_or_id);
    
    if (empty($row)) {
        show_404();
    }
    
    // ✅✅✅ ADD THIS RIGHT HERE ✅✅✅
    // Check access
    if (!$this->enforce_recruiter_candidate_access($row, false)) {
        return;
    }
    // ✅✅✅ END OF ADDITION ✅✅✅
    
    // Store the actual ID and UUID for use in the system
    $candidate_id = $row->id;
    $candidate_uuid = $row->uuid;

        // Check for pending documents requests
        $documents_request_data = $this->check_pending_documents_request($candidate_id);
        
        // Add CSS to hide the Add Candidate button on view pages
        echo '
        <style>
        /* Hide Add Candidate button on candidate view pages */
        .add-item[href*="/candidates/add"] {
            display: none !important;
        }
        
        /* Alternative: Hide by button text */
        .btn-primary.add-item:has(i.fa-plus-circle) {
            display: none !important;
        }
        </style>
        ';
        
        // Update chat button to use UUID
        echo '
        <script>
        $(document).ready(function() {
            // Add chat button to the action buttons area
            var chatButton = \'<a href="\' + base_url + \'recruiter/candidates/start_candidate_chat/' . $candidate_uuid . '\" class="btn btn-info btn-sm chat-row" title="Chat with agency about this candidate" target="_blank"><i class="fa fa-comments"></i> Chat with Agency</a>\';
            
            // Find the action buttons container and add chat button
            setTimeout(function() {
                $(".action-buttons:first").prepend(chatButton + " ");
            }, 500);
        });
        </script>
        ';
        
        // Pass the data to the view
        $this->load->view($this->folder . '/view_header');
        $this->load->view('cms/crud/view_single', array(
            'row' => $row,
            'id' => $candidate_id, // Keep ID for internal use
            'uuid' => $candidate_uuid, // Add UUID for URLs
            'heading' => lang('view_candidate_heading'),
            'has_pending_documents_request' => $documents_request_data['has_request'],
            'documents_request_notes' => $documents_request_data['notes'],
            'pending_notification_id' => $documents_request_data['notification_id']
        ));
        $this->load->view($this->folder . '/view_footer');
    }
    
        public function filter_by_recruiter($recruiter_id = null)
        {
            if (!$recruiter_id) {
                // Get recruiter from session
                $login_data = $this->session->userdata('login');
                if (!empty($login_data['recruiter'])) {
                    $recruiter = $login_data['recruiter'];
                    $recruiter_id = $recruiter['id'];
                }
            }

            if ($recruiter_id) {
                // SHOW ONLY CANDIDATES ASSIGNED TO THIS RECRUITER
                $this->db->where('candidates.assigned_agent_id', $recruiter_id);
                
                // DEBUG: Log the filter being applied
            } else {
            }
            
            return $this;
        }
// ========== UPDATE AJAX QUICK MANAGE TO WORK WITH UUID ==========
    public function ajax_quick_manage($uuid_or_id = 0)
    {
        $row = false;
        $candidate_id = 0;
        
        if (!empty($uuid_or_id) && $uuid_or_id != '0') {
            // Get candidate by UUID or ID
            $row = $this->{$this->model}->get_candidate($uuid_or_id);
            if ($row) {
                $candidate_id = $row->id;
            }
        }

        // Get quick manage extra data
        $extra_data = $this->quick_manage_extra($candidate_id, $row);
        
        // Check for URL parameter to force required documents tab
        $tab_required = $this->input->get('tab') === 'required';
        if ($tab_required) {
            $extra_data['force_required_tab'] = true;
        }

        $data = array(
            'row' => $row,
            'id' => $candidate_id,
            'uuid' => $row ? $row->uuid : null,
        );

        // Merge with extra data - ensure all required variables are passed
        $data = array_merge($data, $extra_data);

        $this->load->view($this->folder . '/' . $this->pageName . '/ajax_manage', $data);
    }
    public function generate_reference()
        {
            try {
                // Ensure this is an AJAX request
                if (!$this->input->is_ajax_request()) {
                    throw new Exception('Direct access not allowed');
                }

                $reference = $this->{$this->model}->generate_reference_number();
                
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => true,
                        'reference' => $reference
                    ]));
                    
            } catch (Exception $e) {
                error_log('Error generating reference: ' . $e->getMessage());
                
                // Enhanced fallback
                $prefix = 'CAND';
                $year = date('Y');
                $month = date('m');
                
                // Get count for this year/month combination
                $this->db->where('YEAR(created_at)', $year);
                $this->db->where('MONTH(created_at)', $month);
                $count = $this->db->count_all_results('candidates');
                $sequence = $count + 1;
                
                $fallbackReference = $prefix . '-' . $year . $month . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
                
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => true,
                        'reference' => $fallbackReference,
                        'fallback' => true
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
   
        public function get_post_data()
        {
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
                    
                    // Special handling for job_id - get from additional_job_ids if available
                    if ($clean_field === 'job_id') {
                        $additional_jobs = $this->input->post('additional_job_ids') ?: [];
                        if (!empty($additional_jobs)) {
                            $value = $additional_jobs[0]; // First job becomes primary
                        } else {
                            $value = null; // No jobs selected
                        }
                    }
                    
                    // Special handling for agency_id
                    if ($clean_field === 'agency_id') {
                        $additional_agencies = $this->input->post('additional_agency_ids') ?: [];
                        if (!empty($additional_agencies)) {
                            $value = $additional_agencies[0]; // First agency becomes primary
                        } else {
                            $value = null; // No agencies selected
                        }
                    }
                    
                    if ($value !== null && $value !== '') {
                        // Handle status field
                        if ($clean_field === 'status') {
                            $data[$clean_field] = $value;
                        }
                        // Handle date fields
                        elseif (in_array($clean_field, array('date_of_birth', 'application_date'))) {
                            $data[$clean_field] = date('Y-m-d', strtotime($value));
                        }
                        // Handle decimal fields
                        elseif (in_array($clean_field, array('current_salary', 'expected_salary'))) {
                            $data[$clean_field] = (float) $value;
                        }
                        // Handle numeric fields
                        elseif (in_array($clean_field, array('years_experience', 'notice_period', 'rating', 'assigned_agent_id'))) {
                            $data[$clean_field] = (int) $value;
                        }
                        // Handle all other fields
                        else {
                            $data[$clean_field] = $value;
                        }
                    }
                }
            }
            
            // Add additional fields that might not be in formFields but are in the form
            $additional_fields = [
                'id_number', 'gender', 'address', 'city', 'province', 'postal_code', 'country',
                'highest_qualification', 'years_experience', 'current_position', 'current_company',
                'current_salary', 'expected_salary', 'notice_period', 'source', 'cover_letter',
                'assigned_agent_id', 'status'
            ];
            
            foreach ($additional_fields as $field) {
                if (!isset($data[$field])) {
                    $value = $this->input->post($field);
                    if ($value !== null && $value !== '') {
                        // Handle numeric fields
                        if (in_array($field, array('assigned_agent_id', 'years_experience', 'notice_period'))) {
                            $data[$field] = (int) $value;
                        } else {
                            $data[$field] = $value;
                        }
                    }
                }
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
    // Better AJAX detection
    $is_ajax = $this->input->is_ajax_request() || 
            (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    
    if ($this->input->post()) {
        // Validate form
        if ($this->validate_form('create')) {
            // Get post data
            $data = $this->get_post_data();
            
            // Get recruiter ID
            $recruiter_id = $this->get_recruiter_id();
            
            // Add recruiter_id to the data
            $data['recruiter_id'] = $recruiter_id; // ← ADD THIS LINE
            
            // Check if candidate already exists for this email and job
            if (!empty($data['email']) && !empty($data['job_id'])) {
                $existing_candidate = $this->check_existing_candidate($data['email'], $data['job_id']);
                
                if ($existing_candidate) {
                    // Update existing candidate instead of creating new one
                    return $this->update_existing_candidate($existing_candidate->id, $data);
                }
            }
            
            // Add extra parameters
            $extra_params = $this->create_extra_params();
            $data = array_merge($data, $extra_params);
            
            // Add created_at timestamp
            $data['created_at'] = date('Y-m-d H:i:s');
            
            try {
                // Insert the main record
                $id = $this->{$this->model}->create($data);
                
                if ($id) {                    
                    $success = true;
                    $message = 'Candidate created successfully!';
                    $warnings = [];
                    
                    // Handle file upload (continue even if it fails)
                    try {
                        $this->handle_file_upload_manual($id);
                    } catch (Exception $e) {
                        $warnings[] = 'File upload failed: ' . $e->getMessage();
                    }
                    
                    // Handle pivot tables (continue even if it fails)
                    try {
                        $this->handle_pivot_tables($id);
                    } catch (Exception $e) {
                        $warnings[] = 'Failed to update agency/job assignments: ' . $e->getMessage();
                    }
                    
                    // Send notification to agency about new candidate
                    try {
                        $job_id = $data['job_id'] ?? null;
                        
                        if ($job_id && $recruiter_id) {
                            $this->notify_agency_on_candidate_creation($id, $job_id, $recruiter_id);
                        } else {
                            $warnings[] = 'Could not send agency notification: Missing job ID or recruiter ID';
                        }
                    } catch (Exception $e) {
                        $warnings[] = 'Failed to send agency notification: ' . $e->getMessage();
                    }
                    
                    // Send notifications to agencies (continue even if it fails)
                    try {
                        $this->send_agency_notifications($id);
                    } catch (Exception $e) {
                        $warnings[] = 'Failed to send notifications: ' . $e->getMessage();
                    }
                    
                    // For AJAX requests, return JSON with redirect URL
                    if ($is_ajax) {
                        $response = [
                            'success' => $success,
                            'message' => $message,
                            'id' => $id,
                            'redirect' => true,
                            'redirect_url' => site_url('recruiter/candidates')
                        ];
                        
                        if (!empty($warnings)) {
                            $response['warnings'] = $warnings;
                        }
                        
                        $this->output
                            ->set_content_type('application/json')
                            ->set_output(json_encode($response));
                        return;
                    } else {
                        // For non-AJAX requests
                        $this->session->set_flashdata('success', $message);
                        redirect(redir($this->pageName, true));
                    }
                } else {
                    throw new Exception('Failed to create candidate record');
                }
            } catch (Exception $e) {
                // Handle database errors
                $error_message = $e->getMessage();
                
                // Check if it's a duplicate entry error
                if (strpos($error_message, 'Duplicate entry') !== false && strpos($error_message, 'unique_email_job') !== false) {
                    $error_message = 'A candidate with this email address already exists for the selected job. Please use a different email or select a different job.';
                }
                
                if ($is_ajax) {
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'success' => false, 
                            'error' => $error_message
                        ]));
                    return;
                } else {
                    $this->session->set_flashdata('error', $error_message);
                    redirect(redir($this->pageName, true));
                }
            }
        }
        
        // If we get here, there was a validation error
        $error_message = validation_errors() ?: 'Failed to create candidate. Please check the form data.';
        
        if ($is_ajax) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'error' => $error_message,
                    'fields' => $this->form_validation->error_array()
                ]));
            return;
        } else {
            $this->session->set_flashdata('error', $error_message);
            $this->add();
        }
    }
    
    // If no POST data and AJAX, return error
    if ($is_ajax) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'error' => 'No data received']));
        return;
    }
    
    // Show form for non-AJAX
    $this->add();
}


    private function check_existing_candidate_by_email($email)
        {
            $this->db->where('email', $email);
            $this->db->where('removed', 0);
            
            $result = $this->db->get('candidates')->row();
            
            return $result;
        }

public function update($uuid_or_id = null)
{
    // ✅ ADD CSRF VALIDATION for non-AJAX requests
    if (!is_ajax()) {
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            flash_notification('Invalid CSRF token. Please try again.', 'error');
            redir($this->pageName);
            return;
        }
    }
    
    // Better AJAX detection
    $is_ajax = $this->input->is_ajax_request() || 
            (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    
    // Get ID from URL if not provided
    if (empty($uuid_or_id)) {
        $uuid_or_id = $this->input->post('id');
    }
    
    if (empty($uuid_or_id)) {
        if ($is_ajax) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'error' => 'Candidate identifier is required for update.']));
            return;
        } else {
            show_404();
        }
    }

    // Get the actual candidate to get the ID
    $candidate = $this->{$this->model}->get_candidate($uuid_or_id);
    if (!$candidate) {
        if ($is_ajax) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'error' => 'Candidate not found.']));
            return;
        } else {
            show_404();
        }
    }

    // ✅ ADD ACCESS CONTROL CHECK HERE
    $recruiter_id = $this->get_recruiter_id();
    if (!$recruiter_id || $candidate->assigned_agent_id != $recruiter_id) {
        if ($is_ajax) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'error' => 'Access denied to this candidate'
                ]));
        } else {
            show_error('Access denied', 403);
        }
        return;
    }
    
    $candidate_id = $candidate->id;
    
    if ($this->input->post()) {    
        // Validate form
        if ($this->validate_form('update')) {                
            // Get post data
            $data = $this->get_post_data();
            
            // Check for status change before update
            $old_candidate = $this->{$this->model}->get_by_id($candidate_id);
            $old_status = $old_candidate->status ?? null;
            $new_status = $data['status'] ?? null;
            $status_changed = ($old_status && $new_status && $old_status !== $new_status);
            
            // Add extra parameters
            $extra_params = $this->update_extra_params($candidate_id);                
            $data = array_merge($data, $extra_params);                
            
            // Add updated_at timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // CRITICAL: Handle job assignments properly
            $additional_jobs = $this->input->post('additional_job_ids') ?: [];
            
            // If NO jobs are selected, set job_id to null
            if (empty($additional_jobs)) {
                $data['job_id'] = null;
                $data['agency_id'] = null; // Also clear agency_id if no jobs
            } else {
                // Set primary job_id to the first selected job
                $data['job_id'] = $additional_jobs[0];
                
                // Get agency_id from the primary job
                $primary_job = $this->db->select('agency_id')->from('mod_jobs')->where('id', $data['job_id'])->get()->row();
                if ($primary_job) {
                    $data['agency_id'] = $primary_job->agency_id;
                }
            }

            // CRITICAL: Ensure data is an array
            if (!is_array($data)) {
                $data = array();
            }

            try {
                // Update the main candidate record
                $result = $this->{$this->model}->update($data, $candidate_id, 'id');
                
                if ($result) {
                    // Handle file upload
                    $this->handle_file_upload_manual($candidate_id);
                    
                    // Handle pivot tables
                    $this->handle_pivot_tables($candidate_id);
                    
                    // Send status change notification if status changed
                    if ($status_changed) {
                        try {
                            $recruiter_id = $this->get_recruiter_id();
                            $this->create_status_change_notification($candidate_id, $old_status, $new_status, $recruiter_id);
                        } catch (Exception $e) {
                            // Log but don't break the update
                            log_message('error', 'Status change notification failed: ' . $e->getMessage());
                        }
                    }
                    
                    // Set success message
                    $message = 'Candidate updated successfully!';
                    
                    if ($is_ajax) {
                        $response = [
                            'success' => true, 
                            'message' => $message,
                            'id' => $candidate_id,
                            'uuid' => $candidate->uuid,
                            'redirect' => true,
                            'redirect_url' => site_url('recruiter/candidates')
                        ];
                        
                        $this->output
                            ->set_content_type('application/json')
                            ->set_output(json_encode($response));
                        return;
                    } else {
                        $this->session->set_flashdata('success', $message);
                        redirect(redir($this->pageName, true));
                    }
                    
                } else {
                    throw new Exception('No changes made or candidate not found');
                }
            } catch (Exception $e) {
                if ($is_ajax) {
                    $response = [
                        'success' => false, 
                        'error' => 'Update failed: ' . $e->getMessage()
                    ];
                    
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($response));
                    return;
                } else {
                    $this->session->set_flashdata('error', 'Update failed: ' . $e->getMessage());
                    redirect(redir($this->pageName, true));
                }
            }
        } else {
            if ($is_ajax) {
                $response = [
                    'success' => false, 
                    'error' => validation_errors() ?: 'Validation failed',
                    'fields' => $this->form_validation->error_array()
                ];
                
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response));
                return;
            } else {
                $this->session->set_flashdata('error', validation_errors());
                redirect(redir($this->pageName . '/edit/' . $candidate->uuid, true));
            }
        }
    } else {
        if ($is_ajax) {
            $response = [
                'success' => false, 
                'error' => 'No POST data received'
            ];
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
            return;
        } else {
            show_404();
        }
    }
}

    
        /**
     * Send notifications to agencies when candidate is submitted
     */
    private function send_agency_notifications($candidate_id)
        {
            try {
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
                return true;
            } catch (Exception $e) {
                return false;
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
 // ✅ ADD CSRF VALIDATION
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid CSRF token. Please refresh and try again.',
                'csrf' => $this->security->get_csrf_hash()
            ]);
            return;
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

public function upload_required_documents()
{
    
    // ========== DUAL CSRF VALIDATION ==========
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->post($csrf_name);
    
    // Try multiple validation methods
    $csrf_valid = false;
    
    // Method 1: Standard CodeIgniter validation
    if ($csrf_token && $this->security->csrf_verify($csrf_token)) {
        $csrf_valid = true;
    }
    
    // Method 2: Check if token exists in our database
    if (!$csrf_valid && $csrf_token) {
        $csrf_valid = $this->validate_csrf_from_database($csrf_token);
    }
    
    // Method 3: Check raw input for multipart forms
    if (!$csrf_valid) {
        $raw_input = file_get_contents('php://input');
        parse_str($raw_input, $input_data);
        $raw_csrf_token = isset($input_data[$csrf_name]) ? $input_data[$csrf_name] : null;
        
        if ($raw_csrf_token) {
            if ($this->security->csrf_verify($raw_csrf_token)) {
                $csrf_valid = true;
                $csrf_token = $raw_csrf_token;
            } elseif ($this->validate_csrf_from_database($raw_csrf_token)) {
                $csrf_valid = true;
                $csrf_token = $raw_csrf_token;
            }
        }
    }
    
    // Method 4: Check headers
    if (!$csrf_valid) {
        $header_token = $this->input->get_request_header('X-CSRF-TOKEN');
        if ($header_token) {
            if ($this->security->csrf_verify($header_token)) {
                $csrf_valid = true;
                $csrf_token = $header_token;
            } elseif ($this->validate_csrf_from_database($header_token)) {
                $csrf_valid = true;
                $csrf_token = $header_token;
            }
        }
    }
    
    if (!$csrf_valid) {
        // Generate new token for next request
        $new_csrf_hash = $this->security->get_csrf_hash();
        
        $response = [
            'success' => false, 
            'message' => 'Security validation failed. Please refresh the form and try again.',
            'csrf_token' => $new_csrf_hash,
            'csrf_name' => $csrf_name,
            'debug' => 'CSRF validation failed all methods'
        ];
        
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
        return;
    }
    
    // ========== REST OF THE METHOD (unchanged) ==========
    $candidate_identifier = $this->input->post('candidate_id');
    $notification_id = $this->input->post('notification_id');
    $submission_notes = $this->input->post('submission_notes');
    
    // Get candidate by UUID or ID
    $candidate = $this->{$this->model}->get_candidate($candidate_identifier);
    if (empty($candidate)) {
        $response = ['success' => false, 'message' => 'Candidate not found.'];
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
        return;
    }
    
    $candidate_id = $candidate->id;
    
    // Check access
    $has_access = $this->{$this->model}->check_recruiter_candidate_access($this->get_recruiter_id(), $candidate_id);
    if (!$has_access) {
        $response = ['success' => false, 'message' => 'Access denied.'];
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
        return;
    }
    
    // Handle multiple document uploads
    $uploaded_documents = [];
    $errors = [];
    
    // Get the document data from POST
    $document_names = $this->input->post('required_documents');
    
    // Check if we have documents to upload
    if (empty($document_names) || !is_array($document_names)) {
        $response = ['success' => false, 'message' => 'No documents to upload.'];
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
        return;
    }
    
    // Process each document
    foreach ($document_names as $index => $document_data) {
        if (!empty($document_data['name']) && isset($_FILES['required_documents']['name'][$index]['file'])) {
            $document_name = $document_data['name'];
            $description = isset($document_data['description']) ? $document_data['description'] : '';
            
            $upload_result = $this->upload_single_required_document(
                $candidate_id, 
                $document_name, 
                $description,
                $index
            );
            
            if ($upload_result['success']) {
                $uploaded_documents[] = $upload_result['document'];
            } else {
                $errors[] = "Document '{$document_name}': " . $upload_result['error'];
            }
        } else {
            $errors[] = "Document at index {$index} is missing name or file";
        }
    }
    
    if (!empty($errors) && empty($uploaded_documents)) {
        $response = ['success' => false, 'message' => 'All uploads failed: ' . implode(', ', $errors)];
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
        return;
    }
    
    if (!empty($uploaded_documents)) {
        // AUTO-UPDATE: Update the documents stage automatically
        $stage_updated = false;
        try {
            // Check if documents stage needs to be updated
            if (!$candidate->stage_requested_docs) {
                $stage_updated = $this->{$this->model}->check_and_update_documents_stage($candidate_id);
            }
        } catch (Exception $e) {
            log_message('error', 'Stage update error: ' . $e->getMessage());
        }
        
        // Send notification to agency about submitted required documents
        $this->load->model('recruiter/Model_notifications');
        
        // Check if the notification method exists
        $notification_sent = false;
        if (method_exists($this->Model_notifications, 'create_required_documents_submitted_notification')) {
            $notification_sent = $this->Model_notifications->create_required_documents_submitted_notification(
                $candidate_id, 
                $this->get_recruiter_id(), 
                count($uploaded_documents),
                $submission_notes,
                $notification_id
            );
        } else {
            // Fallback to the existing documents uploaded notification
            $notification_sent = $this->Model_notifications->create_documents_uploaded_notification(
                $candidate_id,
                $this->get_recruiter_id(),
                count($uploaded_documents)
            );
        }
        
        $message = count($uploaded_documents) . ' required document(s) submitted successfully!';
        
        // Add stage update information to message
        if ($stage_updated) {
            $message .= ' Documents stage has been automatically updated to "Submitted".';
        } else {
            $message .= ' Documents are now available for agency review.';
        }
        
        if (!empty($errors)) {
            $message .= ' Some documents failed: ' . implode(', ', $errors);
        }
        
        if (!$notification_sent) {
            $message .= ' (Note: Agency notification failed to send)';
        }
        
        // Generate NEW CSRF token for next request
        $new_csrf_hash = $this->security->get_csrf_hash();
        
        $response = [
            'success' => true, 
            'message' => $message, 
            'documents' => $uploaded_documents,
            'stage_updated' => $stage_updated,
            'redirect' => true,
            'redirect_url' => site_url('recruiter/candidates'),
            'csrf_token' => $new_csrf_hash,
            'csrf_name' => $csrf_name
        ];
        
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    } else {
        // Generate NEW CSRF token for next request
        $new_csrf_hash = $this->security->get_csrf_hash();
        
        $response = [
            'success' => false, 
            'message' => 'No documents were successfully uploaded.',
            'csrf_token' => $new_csrf_hash,
            'csrf_name' => $csrf_name
        ];
        
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
}

/**
 * Validate CSRF token from database (for cross-window validation)
 */
private function validate_csrf_from_database($csrf_token)
{
    if (empty($csrf_token)) {
        return false;
    }
    
    // Check if token exists in database and is not expired
    $this->db->where('token', $csrf_token)
             ->where('expires_at >', date('Y-m-d H:i:s'))
             ->limit(1);
    
    $token_record = $this->db->get('csrf_tokens')->row();
    
    if ($token_record) {
        // Token is valid, delete it (one-time use)
        $this->db->where('id', $token_record->id)->delete('csrf_tokens');
        return true;
    }
    
    return false;
}

    private function upload_single_required_document($candidate_id, $document_name, $description, $file_index)
{
    // Ensure upload path exists
    $upload_path = FCPATH . 'uploads/candidate_documents/required/';
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
        file_put_contents($upload_path . 'index.html', '');
    }
    
    $config['upload_path'] = $upload_path;
    
    // ========== FIXED FILE UPLOAD CONFIGURATION ==========
    $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
    $config['file_ext_tolower'] = true; // Convert extensions to lowercase
    $config['detect_mime'] = true; // Keep MIME detection on
    
    // Explicit MIME type definitions to fix recognition issues
    $config['mimes'] = array(
        'pdf' => array('application/pdf'),
        'doc' => array('application/msword', 'application/vnd.ms-office'),
        'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'),
        'jpg' => array('image/jpeg', 'image/pjpeg'),
        'jpeg' => array('image/jpeg', 'image/pjpeg'),
        'png' => array('image/png', 'image/x-png')
    );
    
    $config['max_size'] = 10240; // 10MB
    $config['encrypt_name'] = true;
    $config['overwrite'] = false;
    
    $this->load->library('upload', $config);
    
    // Handle the file upload for this specific index
    $file_data = [
        'name' => $_FILES['required_documents']['name'][$file_index]['file'],
        'type' => $_FILES['required_documents']['type'][$file_index]['file'],
        'tmp_name' => $_FILES['required_documents']['tmp_name'][$file_index]['file'],
        'error' => $_FILES['required_documents']['error'][$file_index]['file'],
        'size' => $_FILES['required_documents']['size'][$file_index]['file']
    ];
    
    // Check if file was actually uploaded
    if ($file_data['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'error' => 'File upload error: ' . $this->get_upload_error_message($file_data['error'])
        ];
    }
    
    // Use a temporary global $_FILES variable for the upload library
    $_FILES['document_file'] = $file_data;
    
    if (!$this->upload->do_upload('document_file')) {
        return [
            'success' => false,
            'error' => $this->upload->display_errors()
        ];
    }
    
    $upload_data = $this->upload->data();
    
    // Save document to database with special type
    $document_data = [
        'candidate_id' => $candidate_id,
        'document_name' => $document_name,
        'file_name' => $upload_data['file_name'],
        'file_path' => 'uploads/candidate_documents/required/' . $upload_data['file_name'],
        'file_size' => $upload_data['file_size'],
        'file_type' => $upload_data['file_type'],
        'uploaded_by' => $this->get_recruiter_id(),
        'uploaded_by_type' => 'recruiter',
        'document_type' => 'required_document',
        'description' => $description,
        'is_required_submission' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $result = $this->{$this->model}->save_candidate_document($document_data);
    
    if ($result) {
        return [
            'success' => true,
            'document' => $document_data
        ];
    } else {
        // Delete the uploaded file if database save failed
        @unlink($upload_data['full_path']);
        return [
            'success' => false,
            'error' => 'Failed to save document information'
        ];
    }
}
private function get_upload_error_message($error_code)
{
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
    ];
    
    return isset($errors[$error_code]) ? $errors[$error_code] : 'Unknown upload error';
}
/**
 * Get fresh CSRF token (AJAX endpoint)
 */
public function get_csrf_token()
{
    // Set JSON header - DON'T require AJAX for this endpoint
    $this->output->set_content_type('application/json');
    
    // Regenerate CSRF token
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_hash = $this->security->get_csrf_hash();
    
    // Return the token
    echo json_encode([
        'success' => true,
        'csrf_name' => $csrf_name,
        'csrf_token' => $csrf_hash
    ]);
}


    public function get_submitted_required_documents($candidate_id)
        {
            $documents = $this->{$this->model}->get_required_documents($candidate_id);
            
            $html = '';
            if (!empty($documents)) {
                $html .= '<table class="table table-striped">';
                $html .= '<thead><tr><th>Document Name</th><th>Submitted Date</th><th>Size</th><th>Actions</th></tr></thead>';
                $html .= '<tbody>';
                
                foreach ($documents as $doc) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($doc->document_name, ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '<td>' . date('M j, Y', strtotime($doc->created_at)) . '</td>';
                    $html .= '<td>' . $this->format_file_size($doc->file_size) . '</td>';
                    $html .= '<td>';
                    $html .= '<a href="' . base_url($doc->file_path) . '" target="_blank" class="btn btn-sm btn-primary" title="Download"><i class="fa fa-download"></i></a>';
                    $html .= '<button onclick="deleteDocument(' . $doc->id . ')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fa fa-trash"></i></button>';
                    $html .= '</td>';
                    $html .= '</tr>';
                }
                
                $html .= '</tbody></table>';
            } else {
                $html = '<div class="text-center text-muted p-4">No required documents submitted yet.</div>';
            }
            
            echo $html;
        }

    /**
     * Mark documents request as completed
     */
    public function mark_documents_request_complete()
        {
            $notification_id = $this->input->post('notification_id');
            $candidate_id = $this->input->post('candidate_id');
            
            if (empty($notification_id)) {
                ajax_return(['success' => false, 'message' => 'Notification ID required']);
                return;
            }
            
            // Mark notification as read/completed
            $this->load->model('recruiter/Model_notifications');
            $result = $this->Model_notifications->mark_as_read($notification_id, $this->get_recruiter_id());
            
            if ($result) {
                ajax_return(['success' => true, 'message' => 'Documents request marked as completed']);
            } else {
                ajax_return(['success' => false, 'message' => 'Failed to mark request as completed']);
            }
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

public function get_all($limit = null, $offset = null, $sort_by = 'first_name', $sort_order = 'ASC', $filters = [])
{
    // ========== PROPER PAGINATION HANDLING ==========
    $actual_limit = 10; // Force 10 per page
    $actual_offset = 0;
    
    // Check if this is a CRUD system call (string parameter)
    if (is_string($limit) && $limit === 'listing') {
        // Get page from URL or default to 1
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $actual_offset = ($page - 1) * $actual_limit;
    } else if (is_numeric($limit)) {
        // Direct call with numeric parameters
        $actual_limit = (int)$limit;
        $actual_offset = (int)$offset;
    }
    
    log_message('debug', 'Pagination: limit=' . $actual_limit . ', offset=' . $actual_offset . ', page=' . ($page ?? 'N/A'));
    
    // ========== YOUR EXISTING QUERY CODE ==========
    // Select base candidate fields
    $this->db->select('candidates.*');

    // Subquery: get all agency names for this candidate
    $this->db->select("(SELECT GROUP_CONCAT(a.name SEPARATOR ', ')
                        FROM candidate_agencies ca
                        JOIN agencies a ON a.id = ca.agency_id
                        WHERE ca.candidate_id = candidates.id
                        AND a.removed = 0 AND a.enabled = 1
                    ) AS agency_name", false);

    // Subquery: get all job names for this candidate
    $this->db->select("(SELECT GROUP_CONCAT(j.name SEPARATOR ', ')
                        FROM candidate_jobs cj
                        JOIN mod_jobs j ON j.id = cj.job_id
                        WHERE cj.candidate_id = candidates.id
                        AND j.removed = 0 AND j.enabled = 1
                    ) AS job_name", false);

    $this->db->from($this->table);
    $this->db->where('candidates.removed', 0);

    // Filter by recruiter's assigned candidates
    $this->filter_by_recruiter();

    // Add job filtering
    if (!empty($filters['job_id'])) {
        $job_id = $filters['job_id'];
        $this->db->group_start();
        $this->db->where('candidates.job_id', $job_id);
        $this->db->or_where("candidates.id IN (SELECT candidate_id FROM candidate_jobs WHERE job_id = $job_id)");
        $this->db->group_end();
    }

    // Apply search filter
    if (!empty($filters['general'])) {
        $this->db->group_start();
        foreach (['candidates.first_name', 'candidates.last_name', 'candidates.email', 'candidates.reference_number'] as $field) {
            $this->db->or_like($field, $filters['general']);
        }
        $this->db->group_end();
    }

    // Apply status filter
    if (!empty($filters['status'])) {
        $this->db->where('candidates.status', $filters['status']);
    }

    // Sorting
    if ($sort_by && !in_array($sort_by, ['agency_name', 'job_name'])) {
        $this->db->order_by("candidates.$sort_by", $sort_order ?: 'ASC');
    }

    // ========== APPLY PAGINATION ==========
    $this->db->limit($actual_limit, $actual_offset);

    // Execute query
    $query = $this->db->get();
    
    log_message('debug', 'Query returned ' . $query->num_rows() . ' rows');
    log_message('debug', 'SQL: ' . $this->db->last_query());

    return $query;
}

/**
 * Override the CRUD's ajax_list to ensure proper pagination
 */
public function ajax_list($section = '', $template = 'listing')
{
    if (!$this->input->is_ajax_request()) {
        show_404();
    }
    
    // Get current page
    $page = $this->input->post('page') ?: 1;
    $perPage = 10; // Force 10 per page
    
    // Calculate offset
    $offset = ($page - 1) * $perPage;
    
    // Get filters
    $filters = $this->get_filters_from_session();
    
    // Get sorting
    $sort_by = $this->input->post('sort_by') ?: 'first_name';
    $sort_order = $this->input->post('sort_order') ?: 'ASC';
    
    // Get data with proper pagination
    $this->{$this->model}->set_current_filters($filters);
    $query = $this->{$this->model}->get_all($perPage, $offset, $sort_by, $sort_order, $filters);
    
    // Get total count for pagination
    $total_rows = $this->{$this->model}->count_all();
    
    // Prepare data for view
    $data = [
        'rows' => $query->result(),
        'heading' => lang($this->pageName . '_heading'),
        'noRows' => lang($this->pageName . '_no_rows'),
        'total_rows' => $total_rows,
        'per_page' => $perPage,
        'current_page' => $page,
        'listFields' => $this->listFields,
        'listActions' => $this->listActions,
        'show_select' => true,
    ];
    
    // Load the listing view
    $this->load->view('cms/crud/' . $template, $data);
}

/**
 * Debug method to test pagination
 */
public function debug_pagination()
{
    echo "<h1>Candidates Pagination Debug</h1>";
    
    // Test different calling patterns
    echo "<h2>Test 1: Direct call with limit=10</h2>";
    $result1 = $this->Model_candidates->get_all(10, 0);
    echo "Rows returned: " . $result1->num_rows() . "<br><br>";
    
    echo "<h2>Test 2: CRUD-style call (string parameter)</h2>";
    $result2 = $this->Model_candidates->get_all('listing');
    echo "Rows returned: " . $result2->num_rows() . "<br><br>";
    
    echo "<h2>Test 3: Total count</h2>";
    $total = $this->Model_candidates->count_all();
    echo "Total candidates: " . $total . "<br><br>";
    
    echo "<h2>Check Logs</h2>";
    echo "Look in: application/logs/log-[date].php<br>";
    echo "Or enable browser console for JavaScript debugging<br>";
    
    echo "<h2>Live Test</h2>";
    echo '<a href="' . site_url('recruiter/candidates') . '" target="_blank">View Candidates Page</a><br>';
    echo '<a href="' . site_url('recruiter/candidates?page=2') . '" target="_blank">View Page 2</a>';
}
     public function quick_manage_extra($id, $row): array
    {
        // If $id is a UUID string, get the actual ID
        $candidate_id = $id;
        if (is_string($id) && strlen($id) == 36 && strpos($id, '-') !== false) {
            $candidate = $this->{$this->model}->get_by_uuid($id);
            if ($candidate) {
                $candidate_id = $candidate->id;
                if (!$row || empty($row->id)) {
                    $row = $candidate;
                }
            }
        }
        
        // Safely handle the row parameter
        if (is_string($row) || $row === null || $row === false) {
            $row = new stdClass();
            $row->id = 0;
            $row->job_id = null;
            $row->agency_id = null;
            $row->first_name = null;
            $row->last_name = null;
            $row->assigned_agent_id = null;
            $row->uuid = null;
        }

        $agencies = $this->{$this->model}->get_agencies_all();
        $jobs = $this->{$this->model}->get_jobs_all();
        $pre_selected_job_id = $this->session->userdata('pre_selected_job_id');
        
        if (empty($candidate_id) && $pre_selected_job_id) {
            $job = $this->{$this->model}->get_job_by_id($pre_selected_job_id);
            if ($job) {
                $row->job_id = $job->id;
                $row->agency_id = $job->agency_id;
                $this->session->unset_userdata('pre_selected_job_id');
            }
        }

        // Get existing data
        $agencies = $this->{$this->model}->get_agencies_all();
        $jobs = $this->{$this->model}->get_jobs_all();

        // FIX: Get the logged-in recruiter ID
        $logged_in_recruiter_id = $this->get_recruiter_id();
        
        // For new candidates, auto-assign the logged-in recruiter
        if (empty($candidate_id) && !empty($logged_in_recruiter_id)) {
            $row->assigned_agent_id = $logged_in_recruiter_id;
        }

        // Get all recruiters for the dropdown (for existing candidates)
        $agents = $this->get_all_recruiters();

        $all_additional_agency_ids = !empty($candidate_id) ? $this->{$this->model}->get_candidate_additional_agencies($candidate_id) : [];
        $all_additional_job_ids = !empty($candidate_id) ? $this->{$this->model}->get_candidate_additional_jobs($candidate_id) : [];
        
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

        // Check for pending documents requests
        $documents_request_data = $this->check_pending_documents_request($candidate_id);
        $force_required_tab = $this->input->get('tab') === 'required';

        if ($force_required_tab && !$documents_request_data['has_request']) {
            $documents_request_data = [
                'has_request' => true,
                'notes' => 'Additional documents are required for this candidate.',
                'notification_id' => null
            ];
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
            'has_pending_documents_request' => $documents_request_data['has_request'],
            'documents_request_notes' => $documents_request_data['notes'],
            'pending_notification_id' => $documents_request_data['notification_id'],
            'force_required_tab' => $force_required_tab,
            'logged_in_recruiter_id' => $logged_in_recruiter_id,
            'logged_in_recruiter' => $this->get_logged_in_recruiter_details($logged_in_recruiter_id),
        ];
    }
    //get logged-in recruiter details
    private function get_logged_in_recruiter_details($recruiter_id)
    {
        if (empty($recruiter_id)) {
            return null;
        }
        
        return $this->db->select('id, first_name, last_name, email')
                    ->from('recruiters')
                    ->where('id', $recruiter_id)
                    ->where('enabled', 1)
                    ->where('removed', 0)
                    ->get()
                    ->row();
    }

    // Add this method to get ALL recruiters
    private function get_all_recruiters()
    {
        return $this->db->select('id, first_name, last_name, email, agency_id')
                    ->from('recruiters')
                    ->where('enabled', 1)
                    ->where('removed', 0)
                    ->order_by('first_name', 'ASC')
                    ->get()
                    ->result();
    }

    private function check_pending_documents_request($candidate_id)
    {
        // If it's a new candidate (id = 0), no documents request
        if (empty($candidate_id) || $candidate_id == 0) {
            return [
                'has_request' => false,
                'notes' => '',
                'notification_id' => null
            ];
        }

        $this->load->model('recruiter/Model_notifications');
        
        $recruiter_id = $this->get_recruiter_id();
        if (empty($recruiter_id)) {
            return [
                'has_request' => false,
                'notes' => '',
                'notification_id' => null
            ];
        }

        // Get all notifications for this recruiter
        $notifications = $this->Model_notifications->get_hm_decision_notifications($recruiter_id, 100);
        

        foreach ($notifications as $notification) {
            
            if ($notification->related_entity_id == $candidate_id) {
                $metadata = !empty($notification->metadata) ? json_decode($notification->metadata, true) : [];
                
                
                // Check if this is a documents request - look for specific indicators
                $is_documents_request = false;
                $required_documents = '';
                
                // Check multiple indicators for documents request
                if (isset($metadata['notification_type']) && $metadata['notification_type'] === 'documents_request') {
                    $is_documents_request = true;
                    $required_documents = $metadata['required_documents'] ?? $metadata['notes'] ?? 'Additional documents are required';
                } 
                elseif (isset($metadata['decision']) && $metadata['decision'] === 'documents_required') {
                    $is_documents_request = true;
                    $required_documents = $metadata['required_documents'] ?? $metadata['notes'] ?? 'Additional documents are required';
                }
                elseif (strpos($notification->title, 'Additional Documents') !== false || 
                        strpos($notification->title, 'Documents Required') !== false ||
                        strpos($notification->title, 'Documents Requested') !== false) {
                    $is_documents_request = true;
                    $required_documents = $metadata['required_documents'] ?? $metadata['notes'] ?? 'Additional documents are required';
                }
                elseif (isset($metadata['required_documents']) && !empty($metadata['required_documents'])) {
                    $is_documents_request = true;
                    $required_documents = $metadata['required_documents'];
                }
                
                // For testing, let's be less strict about the "is_read" check
                if ($is_documents_request) {
                    return [
                        'has_request' => true,
                        'notes' => $required_documents,
                        'notification_id' => $notification->id
                    ];
                }
            }
        }
        
        return [
            'has_request' => false,
            'notes' => '',
            'notification_id' => null
        ];
    }

    public function is_unique_email(string $email): bool
    {
        $id = $this->input->post('id');
        $this->form_validation->set_message('is_unique_email', lang('email_exists'));
        return $this->{$this->model}->is_unique_email($email, $id);
    }

//     public function create_extra_params(): array
// {
//     $additional_agencies = $this->input->post('additional_agency_ids') ?: [];
//     $additional_jobs = $this->input->post('additional_job_ids') ?: [];
    
//     $primary_agency_id = !empty($additional_agencies) ? $additional_agencies[0] : null;
//     $primary_job_id = !empty($additional_jobs) ? $additional_jobs[0] : null;

//     // Get the logged-in recruiter ID
//     $recruiter_id = $this->get_recruiter_id();

//     return [
//         'application_date' => $this->input->post('application_date') ?: date('Y-m-d H:i:s'),
//         'enabled' => 1,
//         'agency_id' => $primary_agency_id,
//         'job_id' => $primary_job_id,
//         'assigned_agent_id' => $recruiter_id, // Auto-assign to the logged-in recruiter
//     ];
// }

    public function create_extra_params(): array
    {
        $additional_agencies = $this->input->post('additional_agency_ids') ?: [];
        $additional_jobs = $this->input->post('additional_job_ids') ?: [];
        
        // CHANGED: Handle cases where no agencies/jobs are selected
        $primary_agency_id = !empty($additional_agencies) ? $additional_agencies[0] : null;
        $primary_job_id = !empty($additional_jobs) ? $additional_jobs[0] : null;

        // Get the logged-in recruiter ID
        $recruiter_id = $this->get_recruiter_id();

        return [
            'application_date' => $this->input->post('application_date') ?: date('Y-m-d H:i:s'),
            'enabled' => 1,
            'agency_id' => $primary_agency_id, // Can be null
            'job_id' => $primary_job_id, // Can be null
            'assigned_agent_id' => $recruiter_id, // Auto-assign to the logged-in recruiter
            'recruiter_id' => $recruiter_id, // ← ADD THIS
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

    // private function handle_pivot_tables($candidate_id)
    // {
    //     // Handle agencies
    //     $additional_agencies = $this->input->post('additional_agency_ids') ?: [];
    //     $this->db->where('candidate_id', $candidate_id)->delete('candidate_agencies');
        
    //     if (!empty($additional_agencies)) {
    //         $agency_data = [];
    //         foreach ($additional_agencies as $agency_id) {
    //             $agency_data[] = [
    //                 'candidate_id' => $candidate_id,
    //                 'agency_id' => $agency_id,
    //                 'created_at' => date('Y-m-d H:i:s')
    //             ];
    //         }
    //         $this->db->insert_batch('candidate_agencies', $agency_data);
    //     }

    //     // Handle jobs
    //     $additional_jobs = $this->input->post('additional_job_ids') ?: [];
    //     $this->db->where('candidate_id', $candidate_id)->delete('candidate_jobs');
        
    //     if (!empty($additional_jobs)) {
    //         $job_data = [];
    //         foreach ($additional_jobs as $job_id) {
    //             $job_data[] = [
    //                 'candidate_id' => $candidate_id,
    //                 'job_id' => $job_id,
    //                 'created_at' => date('Y-m-d H:i:s')
    //             ];
    //         }
    //         $this->db->insert_batch('candidate_jobs', $job_data);
    //     }
    // }

   private function handle_pivot_tables($candidate_id)
{
    // Handle agencies - only if agencies were selected
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

    // Handle jobs - CRITICAL FIX: Properly handle job removal
    $additional_jobs = $this->input->post('additional_job_ids') ?: [];
    
    // Remove all existing job assignments for this candidate
    $this->db->where('candidate_id', $candidate_id)->delete('candidate_jobs');
    
    // If jobs are selected, create new assignments
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
        
        // Also update candidate_job_assignments table if it exists
        $this->update_candidate_job_assignments($candidate_id, $additional_jobs);
    } else {
        // If no jobs selected, remove from candidate_job_assignments too
        $this->db->where('candidate_id', $candidate_id)->delete('candidate_job_assignments');
    }
}

    /**
     * Update candidate_job_assignments table
     */
    private function update_candidate_job_assignments($candidate_id, $job_ids)
    {
        // Remove existing assignments
        $this->db->where('candidate_id', $candidate_id)->delete('candidate_job_assignments');
        
        if (!empty($job_ids)) {
            $recruiter_id = $this->get_recruiter_id();
            $assignment_data = [];
            
            foreach ($job_ids as $job_id) {
                $assignment_data[] = [
                    'candidate_id' => $candidate_id,
                    'job_id' => $job_id,
                    'assigned_agent_id' => $recruiter_id,
                    'assigned_at' => date('Y-m-d H:i:s'),
                    'status' => 'submitted'
                ];
            }
            
            $this->db->insert_batch('candidate_job_assignments', $assignment_data);
        }
    }

   
public function add($job_uuid = null)
{
    // Check if job_uuid is provided
    if (empty($job_uuid)) {
        // Fallback to get parameter (for backward compatibility)
        $job_uuid = $this->input->get('job_uuid');
        if (empty($job_uuid)) {
            // Also check for old job_id parameter for compatibility
            $job_id = $this->input->get('job_id');
            if ($job_id) {
                // Convert old ID to UUID if needed
                $this->db->select('uuid');
                $this->db->from('mod_jobs');
                $this->db->where('id', $job_id);
                $job = $this->db->get()->row();
                if ($job) {
                    $job_uuid = $job->uuid;
                }
            }
        }
    }
    
    // If we have a job_uuid, get the job details and store in session
    if ($job_uuid) {
        $this->db->select('id, uuid, name');
        $this->db->from('mod_jobs');
        $this->db->where('uuid', $job_uuid);
        $job = $this->db->get()->row();
        
        if ($job) {
            // Store both UUID and ID in session
            $this->session->set_userdata('pre_selected_job_uuid', $job_uuid);
            $this->session->set_userdata('pre_selected_job_id', $job->id);
            
            // Also pass to view data
            $this->data['pre_selected_job'] = [
                'uuid' => $job->uuid,
                'id' => $job->id,
                'name' => $job->name
            ];
        }
    }
    
    // ✅ Pass CSRF token to view
    $this->data['csrf_token_name'] = $this->security->get_csrf_token_name();
    $this->data['csrf_token_hash'] = $this->security->get_csrf_hash();
    
    // Call parent's add method with additional data
    parent::add();
}
   
        public function get_agents($agency_id)
    {
        $agents = $this->{$this->model}->get_agency_agents_by_agency((int)$agency_id);
        echo json_encode($agents);
    }


    
    public function test_quick_manage_data($candidate_id)
    {
        // Simulate what happens in quick_manage_extra
        $row = $this->{$this->model}->get_by_id($candidate_id);
        
        echo "<h3>Testing Quick Manage Data for Candidate ID: " . $candidate_id . "</h3>";
        
        if (!$row) {
            echo "<p style='color: red;'>Candidate not found!</p>";
            return;
        }
        
        echo "<p>Candidate Name: " . $row->first_name . " " . $row->last_name . "</p>";
        
        // Test the documents request check
        $documents_data = $this->check_pending_documents_request($candidate_id);
        
        echo "<h4>Documents Request Data:</h4>";
        echo "<pre>" . print_r($documents_data, true) . "</pre>";
        
        echo "<h4>Quick Manage Extra Result:</h4>";
        $quick_manage_data = $this->quick_manage_extra($candidate_id, $row);
        
        echo "<pre>" . print_r([
            'has_pending_documents_request' => $quick_manage_data['has_pending_documents_request'],
            'documents_request_notes' => $quick_manage_data['documents_request_notes'],
            'pending_notification_id' => $quick_manage_data['pending_notification_id']
        ], true) . "</pre>";
        
        echo "<h4>View the candidate:</h4>";
        echo "<a href='" . site_url('recruiter/candidates/view/' . $candidate_id . '?tab=required') . "' target='_blank'>View Candidate with Required Documents Tab</a>";
    }

// In application/controllers/recruiter/Candidates.php
public function for_job($job_uuid)
{
    $recruiter_id = $this->get_current_recruiter_id();
    
    log_message('debug', 'Candidates::for_job() called with job_uuid: ' . $job_uuid);
    log_message('debug', 'Recruiter ID: ' . $recruiter_id);
    
    // Get job details by UUID - ALL recruiters can see ALL jobs
    $this->db->select('mod_jobs.*, agencies.name as agency_name');
    $this->db->from('mod_jobs');
    $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
    $this->db->where('mod_jobs.uuid', $job_uuid);
    
    $job = $this->db->get()->row();
    
    if (!$job) {
        log_message('error', 'Job not found with UUID: ' . $job_uuid);
        show_404();
    }

    log_message('debug', 'Job found: ' . $job->name . ' (ID: ' . $job->id . ', UUID: ' . $job->uuid . ')');
    
    // Load the jobs model
    $this->load->model('recruiter/model_jobs');
    
    // Get ONLY this recruiter's candidates for this job (use internal ID)
    $candidates = $this->model_jobs->get_candidates_for_job($job->id, $recruiter_id);
    
    log_message('debug', 'Found ' . count($candidates) . ' candidates for job ' . $job->id . ' for recruiter ' . $recruiter_id);
    
    $data = [
        'candidates' => $candidates,
        'job' => $job,
        'job_uuid' => $job->uuid, // Pass UUID to view
        'job_id' => $job->id, // Pass internal ID to view
        'total_candidates' => count($candidates),
        'current_recruiter_id' => $recruiter_id
    ];

    // Set breadcrumbs - update URLs to use UUID
    $this->breadcrumbs = array(
        array(
            'title' => 'Jobs',
            'url' => site_url('recruiter/jobs'),
        ),
        array(
            'title' => $job->name,
            'url' => site_url('recruiter/jobs/view/' . $job->uuid), // Use UUID
        ),
        array(
            'title' => 'My Candidates (' . count($candidates) . ')',
            'url' => '#',
        ),
    );

    $this->load->view($this->folder . '/view_header');
    $this->load->view('recruiter/candidates/view_job_candidates', $data);
    $this->load->view($this->folder . '/view_footer');
}
    
        /**
     * Get the logged-in recruiter's agency ID
     */
    
    
        private function get_user_agency_id()
        {
            $login_data = $this->session->userdata('login');
            
            if (!empty($login_data['recruiter'])) {
                $recruiter = $login_data['recruiter'];
                return !empty($recruiter['agency_id']) ? $recruiter['agency_id'] : null;
            }
            
            return null;
        }
    
        /**
     * Check if candidate already exists for email and job
     */

    
        private function check_existing_candidate($email, $job_id)
        {
            
            $this->db->where('email', $email);
            $this->db->where('job_id', $job_id);
            $this->db->where('removed', 0);
            
            $result = $this->db->get('candidates')->row();
            
            
            return $result;
        }

    
        private function update_existing_candidate($candidate_id, $data)
        {
            
            // Remove fields that shouldn't be updated
            unset($data['created_at']);
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            try {
                $result = $this->{$this->model}->update($data, $candidate_id, 'id');
                
                if ($result) {
                    // Handle file upload
                    $this->handle_file_upload_manual($candidate_id);
                    
                    // Handle pivot tables
                    $this->handle_pivot_tables($candidate_id);
                                
                    // For AJAX requests, return JSON
                    if ($this->input->is_ajax_request()) {
                        $this->output
                            ->set_content_type('application/json')
                            ->set_output(json_encode([
                                'success' => true, 
                                'message' => 'Candidate information updated successfully!', 
                                'id' => $candidate_id
                            ]));
                        return true;
                    } else {
                        // For non-AJAX requests, redirect
                        $this->session->set_flashdata('success', 'Candidate information updated successfully!');
                        redirect(redir($this->pageName, true));
                    }
                } else {
                    throw new Exception('Failed to update candidate record');
                }
            } catch (Exception $e) {
                
                // For AJAX requests, return JSON error
                if ($this->input->is_ajax_request()) {
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'success' => false, 
                            'error' => 'Failed to update candidate: ' . $e->getMessage()
                        ]));
                    return false;
                } else {
                    $this->session->set_flashdata('error', 'Failed to update candidate: ' . $e->getMessage());
                    redirect(redir($this->pageName, true));
                }
            }
            
            return false;
        }


    /**
     * Override the CRUD ajax_get_results to ensure filters are passed - FIXED
     */

    public function ajax_get_results()
        {
            
            // Get filters from session using the correct key
            $filters = $this->get_filters_from_session();
            
            
            // Set filters to model
            $this->{$this->model}->set_current_filters($filters);
            
            // Call parent
            parent::ajax_get_results();
        }


    
        /**
     * Override the ajax_apply_filters to ensure it works
     */
    
    public function ajax_apply_filters()
        {
          
            
            // Call parent to handle the filter application
            parent::ajax_apply_filters();
            
            // Debug what was stored
            $filters = $this->session->userdata('ecms_filters_' . $this->pageName);
            
        }

    
        /**
     * Get filters from CRUD session - FIXED VERSION
     */
    private function get_filters_from_session()
        {
            $filters = [];
            
            // Check both possible session keys (ecmsFilters and ecms_filters_pageName)
            $session_filters = $this->session->userdata('ecmsFilters');
            
            if (!empty($session_filters) && !empty($session_filters[$this->pageName])) {
                $page_filters = $session_filters[$this->pageName];
                
                foreach ($page_filters as $filter_name => $filter_data) {
                    if (isset($filter_data['value']) && $filter_data['value'] !== '') {
                        $filters[$filter_name] = $filter_data['value'];
                    }
                }
            }
            
            // Also check the other possible session key
            $alt_session_filters = $this->session->userdata('ecms_filters_' . $this->pageName);
            if (!empty($alt_session_filters)) {
                foreach ($alt_session_filters as $filter_name => $filter_data) {
                    if (isset($filter_data['value']) && $filter_data['value'] !== '' && !isset($filters[$filter_name])) {
                        $filters[$filter_name] = $filter_data['value'];
                    }
                }
            }
            
            return $filters;
        }

    /**
     * Get applied filters from session or GET
     */
    private function get_applied_filters()
    {
        $filters = [];
        
        // Check session filters first (CRUD system stores filters in session)
        $session_filters = $this->session->userdata('ecms_filters_' . $this->pageName);
        
        if (!empty($session_filters)) {
            foreach ($session_filters as $filter_name => $filter_data) {
                if (!empty($filter_data['value'])) {
                    $filters[$filter_name] = $filter_data['value'];
                }
            }
        }
        
        // Also check GET parameters as fallback
        if (empty($filters) && !empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        
        return $filters;
    }

    /**
     * Load filters to model so get_all can access them
     */
    private function load_filters_to_model($filters)
    {
        // Make filters available to the model
        $this->{$this->model}->set_current_filters($filters);
    }

    /**
     * Override the CRUD ajax_pager_fetch_batch to ensure filters are passed
     */
    public function ajax_pager_fetch_batch($batch = 1, $section = "", $template = "listing")
    {
        
        // Get filters from session
        $filters = $this->get_filters_from_session();
        
        // Set filters to model
        $this->{$this->model}->set_current_filters($filters);
        
        // Call parent
        parent::ajax_pager_fetch_batch($batch, $section, $template);
    }



    /**
     * AJAX method to get candidates for job assignment
     */
    public function ajax_get_candidates_for_job()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $job_id = $this->input->post('job_id');
        $recruiter_id = $this->get_current_recruiter_id();

        if (!$recruiter_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Recruiter not found'
            ]);
            return;
        }

        // Get candidates that belong to this recruiter and are not assigned to this job
        $this->db->select('id, first_name, last_name, reference_number, email');
        $this->db->from('candidates');
        $this->db->where('recruiter_id', $recruiter_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        
        // Exclude candidates already assigned to this job
        $this->db->group_start();
        $this->db->where('job_id !=', $job_id);
        $this->db->or_where('job_id IS NULL');
        $this->db->group_end();
        
        $this->db->order_by('first_name', 'ASC');
        
        $query = $this->db->get();
        $candidates = $query->result_array();

        echo json_encode([
            'success' => true,
            'candidates' => $candidates
        ]);
    }

    /**
     * AJAX method to assign candidate to job
     */
    public function ajax_assign_candidate_to_job()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid CSRF token. Please refresh and try again.',
                'csrf' => $this->security->get_csrf_hash()
            ]);
            return;
        }
        $candidate_id = $this->input->post('candidate_id');
        $job_id = $this->input->post('job_id');
        $recruiter_id = $this->get_current_recruiter_id();

        if (!$recruiter_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Recruiter not found'
            ]);
            return;
        }

        // Verify candidate belongs to this recruiter
        $this->db->select('id');
        $this->db->from('candidates');
        $this->db->where('id', $candidate_id);
        $this->db->where('recruiter_id', $recruiter_id);
        $candidate_query = $this->db->get();

        if ($candidate_query->num_rows() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Candidate not found or access denied'
            ]);
            return;
        }

        // Update candidate's job assignment
        $this->db->where('id', $candidate_id);
        $result = $this->db->update('candidates', [
            'job_id' => $job_id,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Candidate assigned to job successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to assign candidate to job'
            ]);
        }
    }

    /**
     * Get current recruiter ID
     */
    private function get_current_recruiter_id()
    {
        $login_data = $this->session->userdata('login');
        return !empty($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : null;
    }

    /**
 * Create notification for agency when candidate is created and assigned to job
 */
private function notify_agency_on_candidate_creation($candidate_id, $job_id, $recruiter_id)
{
    // Get candidate and job details
    $candidate = $this->db->select('c.*, r.first_name as recruiter_first_name, r.last_name as recruiter_last_name, r.company_name')
                         ->from('candidates c')
                         ->join('recruiters r', 'r.id = c.assigned_agent_id')
                         ->where('c.id', $candidate_id)
                         ->get()
                         ->row();

    $job = $this->db->select('mod_jobs.*, agencies.name as agency_name')
                   ->from('mod_jobs')
                   ->join('agencies', 'agencies.id = mod_jobs.agency_id')
                   ->where('mod_jobs.id', $job_id)
                   ->get()
                   ->row();

    if (!$candidate || !$job) {
        log_message('error', "Candidate or job not found for notification - Candidate: $candidate_id, Job: $job_id");
        return false;
    }

    $recruiter_name = $candidate->company_name ?: $candidate->recruiter_first_name . ' ' . $candidate->recruiter_last_name;

    $notification_data = [
        'title' => 'New Candidate Submission',
        'message' => "New candidate {$candidate->first_name} {$candidate->last_name} has been submitted for job: {$job->name} by {$recruiter_name}",
        'type' => 'candidate_applied',
        'receiver_type' => 'agency',
        'receiver_id' => $job->agency_id, // Agency ID from the job
        'related_entity' => 'candidate',
        'related_entity_id' => $candidate_id,
        'sender_type' => 'recruiter',
        'sender_id' => $recruiter_id,
        'created_at' => date('Y-m-d H:i:s'),
        'is_read' => 0
    ];

    // Insert notification
    $result = $this->db->insert('notifications', $notification_data);
    
    if ($result) {
        log_message('debug', "Notification created for agency {$job->agency_id} about candidate {$candidate->id} for job {$job->id}");
    } else {
        log_message('error', "Failed to create notification for agency {$job->agency_id}");
    }
    
    return $result;
}

/**
 * Create notification when candidate status changes
 */
private function create_status_change_notification($candidate_id, $old_status, $new_status, $recruiter_id)
{
    // Get candidate details with job and agency info
    $candidate = $this->db->select('c.*, j.name as job_name, j.agency_id, a.name as agency_name, r.first_name as recruiter_first_name, r.last_name as recruiter_last_name')
                         ->from('candidates c')
                         ->join('mod_jobs j', 'j.id = c.job_id', 'left')
                         ->join('agencies a', 'a.id = j.agency_id', 'left')
                         ->join('recruiters r', 'r.id = c.assigned_agent_id', 'left')
                         ->where('c.id', $candidate_id)
                         ->get()
                         ->row();

    if (!$candidate) {
        log_message('error', "Candidate not found for status change notification: $candidate_id");
        return false;
    }

    $recruiter_name = $candidate->recruiter_first_name . ' ' . $candidate->recruiter_last_name;
    $job_name = $candidate->job_name ?: 'Unknown Job';

    $notification_data = [
        'title' => 'Candidate Status Updated',
        'message' => "{$candidate->first_name} {$candidate->last_name} status changed from " . ucfirst($old_status) . " to " . ucfirst($new_status) . " for job: {$job_name} by {$recruiter_name}",
        'type' => 'status_changed',
        'receiver_type' => 'agency',
        'receiver_id' => $candidate->agency_id,
        'related_entity' => 'candidate',
        'related_entity_id' => $candidate_id,
        'sender_type' => 'recruiter',
        'sender_id' => $recruiter_id,
        'metadata' => json_encode([
            'old_status' => $old_status,
            'new_status' => $new_status,
            'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
            'job_name' => $job_name,
            'recruiter_name' => $recruiter_name
        ]),
        'created_at' => date('Y-m-d H:i:s'),
        'is_read' => 0
    ];

    // Insert notification
    $result = $this->db->insert('notifications', $notification_data);
    
    if ($result) {
        log_message('debug', "Status change notification created for agency {$candidate->agency_id} about candidate {$candidate->id}");
    } else {
        log_message('error', "Failed to create status change notification for agency {$candidate->agency_id}");
    }
    
    return $result;
}
public function remove_from_job($candidate_id, $job_uuid)
{
    // ✅ ADD CSRF VALIDATION
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->get($csrf_name);
    
    if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
        flash_notification('Invalid CSRF token. Please try again.', 'error');
        redirect('recruiter/candidates/for_job/' . $job_uuid);
        return;
    }
    
    $recruiter_id = $this->get_recruiter_id();
    
    // Convert job UUID to ID
    $job = $this->db->select('id')
                    ->from('mod_jobs')
                    ->where('uuid', $job_uuid)
                    ->get()
                    ->row();
    
    if (!$job) {
        flash_notification('Job not found', 'error');
        redirect('recruiter/candidates/for_job/' . $job_uuid);
        return;
    }
    
    $job_id = $job->id;
    
    // Verify the assignment belongs to this recruiter and exists
    $assignment = $this->db->where('candidate_id', $candidate_id)
                          ->where('job_id', $job_id)
                          ->where('assigned_agent_id', $recruiter_id)
                          ->where('removed', 0)
                          ->get('candidate_job_assignments')
                          ->row();
    
    if (!$assignment) {
        flash_notification('Assignment not found or access denied', 'error');
        redirect('recruiter/candidates/for_job/' . $job_uuid);
        return;
    }

    // Soft delete the assignment in candidate_job_assignments
    $this->db->where('candidate_id', $candidate_id)
            ->where('job_id', $job_id)
            ->update('candidate_job_assignments', [
                'removed' => 1,
                'removed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

    // ALSO REMOVE from candidate_jobs table
    $this->db->where('candidate_id', $candidate_id)
            ->where('job_id', $job_id)
            ->delete('candidate_jobs');

    // Log the action
    $candidate = $this->db->where('id', $candidate_id)->get('candidates')->row();
    $job_details = $this->db->select('name')->where('uuid', $job_uuid)->get('mod_jobs')->row();
    
    Logger::log('Removed candidate from job', [
        'candidate_id' => $candidate_id,
        'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
        'job_uuid' => $job_uuid,
        'job_name' => $job_details->name ?? 'Unknown'
    ]);

    flash_notification('Candidate removed from job successfully', 'success');
    redirect('recruiter/candidates/for_job/' . $job_uuid);
}


/**
 * Get candidate chat information for recruiter
 */
public function ajax_get_candidate_chat_info($candidate_id)
{
    if (!$this->input->is_ajax_request()) {
        show_404();
    }
     // ✅ ADD ACCESS CONTROL
    if (!$this->enforce_recruiter_candidate_access($candidate_id)) {
        return; // Already handles AJAX response
    }
    $recruiter_id = $this->get_recruiter_id();
    
    if (!$recruiter_id) {
        ajax_return([
            'success' => false,
            'message' => 'Not logged in'
        ]);
        return;
    }
    
    // Get candidate details and agency info
    $this->db->select('c.*, ca.agency_id')
             ->from('candidates c')
             ->join('candidate_agencies ca', 'ca.candidate_id = c.id')
             ->where('c.id', $candidate_id)
             ->where('c.removed', 0)
             ->limit(1);
    
    $candidate = $this->db->get()->row();
    
    if (!$candidate) {
        ajax_return([
            'success' => false,
            'message' => 'Candidate not found'
        ]);
        return;
    }
    
    // Get agency name
    $this->db->select('name')
             ->from('agencies')
             ->where('id', $candidate->agency_id)
             ->where('removed', 0);
    $agency = $this->db->get()->row();
    
    // Get agency staff (hiring manager) for this candidate
    $this->db->select('id, first_name, last_name, email')
             ->from('agency_staff')
             ->where('agency_id', $candidate->agency_id)
             ->where('enabled', 1)
             ->where('removed', 0)
             ->order_by('id', 'ASC')
             ->limit(1);
    
    $agency_user = $this->db->get()->row();
    
    if (!$agency_user) {
        ajax_return([
            'success' => false,
            'message' => 'Agency contact not found for this candidate'
        ]);
        return;
    }
    
    // Check if conversation exists or create new one
    $conversation = $this->Model_chat_messages->get_or_create_candidate_conversation(
        $candidate->agency_id,
        $recruiter_id,
        $candidate_id,
        $candidate->job_id
    );
    
    if (!$conversation) {
        ajax_return([
            'success' => false,
            'message' => 'Could not create chat conversation'
        ]);
        return;
    }
    
    // Return conversation info
    ajax_return([
        'success' => true,
        'conversation' => [
            'uuid' => $conversation->uuid,
            'title' => $conversation->title,
            'agency_name' => $agency ? $agency->name : 'Unknown Agency',
            'agency_user_name' => $agency_user->first_name . ' ' . $agency_user->last_name,
            'agency_user_id' => $agency_user->id,
            'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
            'candidate_ref' => $candidate->reference_number
        ],
        'chat_url' => site_url('recruiter/chat/conversation/' . $conversation->uuid)
    ]);
}

public function start_candidate_chat($uuid_or_id)
{
    // Get candidate by UUID or ID first
    $candidate = $this->{$this->model}->get_candidate($uuid_or_id);
    
    if (!$candidate) {
        show_404();
    }
    
    // ✅ Now pass the candidate OBJECT to enforce_recruiter_candidate_access
    if (!$this->enforce_recruiter_candidate_access($candidate)) {
        return; // Already shows error
    }
    
    $recruiter_id = $this->get_recruiter_id();
    
    // Create or get conversation using UUID
    $conversation = $this->Model_chat_messages->get_or_create_candidate_conversation(
        $candidate->agency_id,
        $recruiter_id,
        $candidate->id,
        $candidate->job_id
    );
    
    if ($conversation && !empty($conversation->uuid)) {
        redirect('recruiter/chat/conversation/' . $conversation->uuid);
    } else {
        show_error('Failed to create chat conversation');
    }
}


public function conversation($uuid = null)
{
    $recruiter_id = $this->get_recruiter_id();
    
    if (!$uuid) {
        redirect('recruiter/chat');
        return;
    }
    
    // Get conversation by UUID instead of ID
    $conversation = $this->{$this->model}->get_conversation_for_recruiter_by_uuid($uuid, $recruiter_id);
    
    if (!$conversation) {
        show_404();
    }
    
    // Mark messages as read
    $this->{$this->model}->mark_messages_as_read($conversation->id, 'recruiter');
    
    $messages = $this->{$this->model}->get_conversation_messages($conversation->id);
    
    // Get all conversations for the sidebar
    $all_conversations = $this->{$this->model}->get_recruiter_conversations($recruiter_id);
    
    // Get available agencies for new chats
    $available_agencies = $this->{$this->model}->get_available_agencies_simple($recruiter_id);
    
    // Calculate total unread count
    $total_unread_count = $this->{$this->model}->get_unread_count_for_recruiter($recruiter_id);
    
    // Get recent notifications (initialize as empty array if method doesn't exist)
    $recent_notifications = [];
    if (method_exists($this->{$this->model}, 'get_recent_notifications')) {
        $recent_notifications = $this->{$this->model}->get_recent_notifications($recruiter_id, 'recruiter');
    }
    
    // Calculate total message count (initialize as 0 if method doesn't exist)
    $total_message_count = 0;
    if (method_exists($this->{$this->model}, 'get_total_message_count')) {
        $total_message_count = $this->{$this->model}->get_total_message_count($recruiter_id);
    }
    
    // Get agency details for the right sidebar
    $agency_details = $this->{$this->model}->get_agency_details($conversation->agency_id);
    
    // Get online status
    $agency_online = $this->{$this->model}->get_agency_online_status($conversation->agency_id);
    
    // ✅ ADD THIS: Get candidate details if conversation has candidate
    $candidate_details = null;
    $candidate_documents = [];
    if ($conversation->candidate_id) {
        $this->load->model('recruiter/Model_candidates');
        $candidate_details = $this->Model_candidates->get_candidate($conversation->candidate_id);
        
        // Get candidate documents if needed
        if ($candidate_details && method_exists($this->Model_candidates, 'get_candidate_documents')) {
            $candidate_documents = $this->Model_candidates->get_candidate_documents($conversation->candidate_id);
        }
    }
    
    // Ensure conversation has required properties
    if (!isset($conversation->unread_count)) {
        $conversation->unread_count = 0;
    }
    if (!isset($conversation->is_online)) {
        $conversation->is_online = 0;
    }
    
    $this->breadcrumbs = [
        ['title' => lang('chat_heading'), 'url' => url('chat')],
        ['title' => $conversation->agency_name, 'url' => '']
    ];
    
    $data = [
        'conversation' => $conversation,
        'messages' => $messages,
        'all_conversations' => $all_conversations,
        'available_agencies' => $available_agencies,
        'heading' => lang('chat_heading'),
        'recruiter_id' => $recruiter_id,
        'total_unread_count' => $total_unread_count,
        'recent_notifications' => $recent_notifications,
        'total_message_count' => $total_message_count,
        'agency_details' => $agency_details,
        'agency_online' => $agency_online,
        // ✅ ADD THESE:
        'candidate_details' => $candidate_details,
        'candidate_documents' => $candidate_documents,
    ];
    
    $this->load->view($this->folder . '/view_header');
    $this->load->view('recruiter/chat/conversation', $data);
    $this->load->view($this->folder . '/view_footer');
}

/**
 * AJAX: Switch from candidate-specific chat to general chat
 */
public function ajax_switch_chat_to_general()
{
    if (!$this->input->is_ajax_request()) {
        show_404();
    }
     // ✅ ADD CSRF VALIDATION
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid CSRF token. Please refresh and try again.',
                'csrf' => $this->security->get_csrf_hash()
            ]);
            return;
        }
    $conversation_uuid = $this->input->post('conversation_uuid');
    $recruiter_id = $this->get_recruiter_id();
    $agency_id = $this->input->post('agency_id');
    
    if (!$recruiter_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Recruiter not logged in',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    
    if (!$conversation_uuid || !$agency_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    
    // Check if a general conversation already exists with this agency
    $existing_general = $this->chat_model->get_general_conversation($agency_id, $recruiter_id);
    
    if ($existing_general) {
        // Redirect to existing general conversation
        $response = [
            'success' => true,
            'general_conversation_uuid' => $existing_general->uuid,
            'message' => 'Switched to existing general conversation',
            'csrf' => $this->security->get_csrf_hash()
        ];
    } else {
        // Create a new general conversation
        $general_data = [
            'agency_id' => $agency_id,
            'recruiter_id' => $recruiter_id,
            'candidate_id' => null, // Null for general chat
            'job_id' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'uuid' => $this->chat_model->generate_uuid()
        ];
        
        $new_conversation_id = $this->chat_model->create_conversation($general_data);
        
        if ($new_conversation_id) {
            $new_conversation = $this->chat_model->get_conversation_by_id($new_conversation_id);
            
            $response = [
                'success' => true,
                'general_conversation_uuid' => $new_conversation->uuid,
                'message' => 'Created new general conversation',
                'csrf' => $this->security->get_csrf_hash()
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to create general conversation',
                'csrf' => $this->security->get_csrf_hash()
            ];
        }
    }
    
    echo json_encode($response);
}

/**
 * AJAX endpoint for submitting required documents
 * This is a wrapper around upload_required_documents for consistency
 */
public function ajax_submit_required_documents()
{
    // Set JSON output
    $this->output->set_content_type('application/json');
    
    // Check if it's an AJAX request
    if (!$this->input->is_ajax_request()) {
        $this->output->set_output(json_encode([
            'success' => false,
            'error' => 'Direct access not allowed'
        ]));
        return;
    }
    // ✅ ADD CSRF VALIDATION
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Invalid CSRF token. Please refresh and try again.',
                'csrf' => $this->security->get_csrf_hash()
            ]));
            return;
        }
    // Call the existing upload_required_documents method
    // We need to capture its output
    ob_start();
    $this->upload_required_documents();
    $output = ob_get_clean();
    
    // If output is already JSON, return it
    if (json_decode($output)) {
        $this->output->set_output($output);
    } else {
        // Convert to JSON if it's not already
        $this->output->set_output(json_encode([
            'success' => false,
            'error' => 'Invalid response from server',
            'raw_output' => $output
        ]));
    }
}


/**
 * Get fresh CSRF token for AJAX forms
 */
public function get_fresh_csrf_token()
{
    // Set content type FIRST
    $this->output->set_content_type('application/json');
    
    try {
        // Get current CSRF info
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();
        
        // Return the token
        echo json_encode([
            'success' => true,
            'csrf_name' => $csrf_name,
            'csrf_token' => $csrf_hash,
            'timestamp' => time()
        ]);
        
    } catch (Exception $e) {
        // Return error
        echo json_encode([
            'success' => false,
            'error' => 'Failed to generate security token',
            'message' => $e->getMessage()
        ]);
    }
}

public function submit_required_documents()
{
    // Set JSON header
    header('Content-Type: application/json; charset=UTF-8');
    
    try {
        // Check session
        $recruiter_id = $this->get_recruiter_id();
        if (!$recruiter_id) {
            throw new Exception('Please log in to continue.');
        }
        
        // Get POST data
        $candidate_id = $this->input->post('candidate_id');
        $notification_id = $this->input->post('notification_id');
        $submission_notes = $this->input->post('submission_notes');
        
        if (empty($candidate_id)) {
            throw new Exception('Candidate ID is required.');
        }
        
        // Check if files were uploaded
        if (empty($_FILES['document_files']['name'][0])) {
            throw new Exception('Please select at least one file to upload.');
        }
        
        // Get document names
        $document_names = $this->input->post('document_names');
        $document_descriptions = $this->input->post('document_descriptions');
        
        if (empty($document_names) || !is_array($document_names)) {
            throw new Exception('Document names are required.');
        }
        
        // Process uploads
        $uploaded_count = 0;
        $upload_errors = [];
        
        // Create upload directory
        $upload_path = FCPATH . 'uploads/candidate_documents/required/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        // Process each file
        $file_count = count($_FILES['document_files']['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if (!empty($_FILES['document_files']['name'][$i])) {
                $document_name = isset($document_names[$i]) ? trim($document_names[$i]) : '';
                $description = isset($document_descriptions[$i]) ? trim($document_descriptions[$i]) : '';
                
                if (empty($document_name)) {
                    $upload_errors[] = "Document #" . ($i + 1) . " is missing a name";
                    continue;
                }
                
                // Configure upload
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
                $config['max_size'] = 10240; // 10MB
                $config['encrypt_name'] = true;
                
                $this->load->library('upload', $config);
                
                // Prepare file data
                $file_data = [
                    'name' => $_FILES['document_files']['name'][$i],
                    'type' => $_FILES['document_files']['type'][$i],
                    'tmp_name' => $_FILES['document_files']['tmp_name'][$i],
                    'error' => $_FILES['document_files']['error'][$i],
                    'size' => $_FILES['document_files']['size'][$i]
                ];
                
                $_FILES['upload_file'] = $file_data;
                
                if ($this->upload->do_upload('upload_file')) {
                    $upload_data = $this->upload->data();
                    
                    // Save to database
                    $document_data = [
                        'candidate_id' => $candidate_id,
                        'document_name' => $document_name,
                        'file_name' => $upload_data['file_name'],
                        'file_path' => 'uploads/candidate_documents/required/' . $upload_data['file_name'],
                        'file_size' => $upload_data['file_size'],
                        'file_type' => $upload_data['file_type'],
                        'uploaded_by' => $recruiter_id,
                        'uploaded_by_type' => 'recruiter',
                        'document_type' => 'required_document',
                        'description' => $description,
                        'is_required_submission' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    // Insert into database
                    $this->db->insert('candidate_documents', $document_data);
                    
                    if ($this->db->insert_id()) {
                        $uploaded_count++;
                        error_log("Uploaded: {$document_name} as {$upload_data['file_name']}");
                    } else {
                        @unlink($upload_data['full_path']);
                        $upload_errors[] = "Failed to save '{$document_name}' to database";
                    }
                    
                } else {
                    $upload_errors[] = "Document '{$document_name}': " . $this->upload->display_errors();
                }
            }
        }
        
        if ($uploaded_count > 0) {
            // Mark notification as read
            if (!empty($notification_id)) {
                $this->load->model('recruiter/Model_notifications');
                $this->Model_notifications->mark_as_read($notification_id, $recruiter_id);
            }
            
            $message = $uploaded_count . ' document(s) uploaded successfully!';
            if (!empty($upload_errors)) {
                $message .= ' Some failed: ' . implode(', ', $upload_errors);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'uploaded_count' => $uploaded_count,
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            
        } else {
            $error_msg = 'No documents were uploaded.';
            if (!empty($upload_errors)) {
                $error_msg .= ' Errors: ' . implode(', ', $upload_errors);
            }
            throw new Exception($error_msg);
        }
        
    } catch (Exception $e) {
        error_log('File upload error: ' . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
    }
}
// Add this helper method for CSRF validation
private function validate_csrf_token($token)
{
    $expected = $this->security->get_csrf_hash();
    
    if (!$token || !hash_equals($expected, $token)) {
        // Return JSON error instead of throwing exception
        echo json_encode([
            'success' => false,
            'message' => 'Invalid security token. Please refresh and try again.',
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
        exit();
    }
}
public function show_required_documents_form()
{
    // Enable error reporting temporarily
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Get parameters
    $candidate_id = $this->input->get('candidate_id');
    $notification_id = $this->input->get('notification_id');
    $documents_notes = $this->input->get('documents_notes');
    
    if (empty($candidate_id)) {
        show_error('Candidate ID is required', 400);
        return;
    }
    
    // Get candidate
    $candidate = $this->{$this->model}->get_candidate($candidate_id);
    if (!$candidate) {
        show_error('Candidate not found', 404);
        return;
    }
    
    // Check access
    $recruiter_id = $this->get_recruiter_id();
    if (!$recruiter_id) {
        show_error('Recruiter not logged in', 403);
        return;
    }
    
    // Verify the candidate belongs to this recruiter
    if ($candidate->assigned_agent_id != $recruiter_id) {
        show_error('Access denied to this candidate', 403);
        return;
    }
    
    // Get fresh CSRF token
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_hash = $this->security->get_csrf_hash();
    
    // Get documents request data
    $documents_request_data = $this->check_pending_documents_request($candidate->id);
    
    // Use provided notes or fallback
    $notes = '';
    if (!empty($documents_notes)) {
        $notes = urldecode($documents_notes);
    } elseif (!empty($documents_request_data['notes'])) {
        $notes = $documents_request_data['notes'];
    }
    
    // Load a clean view with complete HTML structure
    $this->load->view('recruiter/candidates/required_documents_form', [
        'candidate_id' => $candidate->id,
        'documents_request_notes' => $notes,
        'notification_id' => !empty($notification_id) ? $notification_id : ($documents_request_data['notification_id'] ?? null),
        'csrf_token_name' => $csrf_name,
        'csrf_token_hash' => $csrf_hash,
        'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
        'candidate_ref' => $candidate->reference_number
    ]);
}

/**
 * Store CSRF token in database for cross-window validation
 */
private function store_csrf_token_for_validation($csrf_token, $recruiter_id, $candidate_id)
{
    // Clean up old tokens (older than 1 hour)
    $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime('-1 hour')))
             ->delete('csrf_tokens');
    
    // Store new token
    $token_data = [
        'token' => $csrf_token,
        'recruiter_id' => $recruiter_id,
        'candidate_id' => $candidate_id,
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
    ];
    
    $this->db->insert('csrf_tokens', $token_data);
    return $this->db->insert_id();
}


public function onboarding_listing() 
{
    $recruiter_id = $this->get_recruiter_id();
    
    if (!$recruiter_id) {
        show_error('Access denied', 403);
    }

    echo "<!-- DEBUG: Recruiter ID = $recruiter_id -->\n";
    
    // ============================================
    // FIXED QUERY: Get ALL job assignments for this recruiter
    // ============================================
    $this->db->select('
        c.id as candidate_id,
        c.uuid as candidate_uuid,
        c.first_name,
        c.last_name,
        c.reference_number,
        
        cja.job_id,
        cja.id as assignment_id,
        cja.created_at as assigned_date,
        cja.updated_at as job_updated_at,
        
        j.name as job_name,
        j.uuid as job_uuid,
        j.reference_number as job_ref,
        j.agency_id as job_agency_id,
        
        cop.id as onboarding_progress_id,
        cop.onboarding_stage,
        cop.onboarding_progress,
        cop.stage_under_review,
        cop.stage_submitted_to_hm,
        cop.stage_hm_decision,
        cop.hm_decision,
        cop.hm_decision_notes,
        cop.stage_requested_docs,
        cop.stage_documents_decision,
        cop.documents_notes as required_documents_notes,
        cop.stage_position_offered,
        cop.created_at as progress_created_at,
        cop.updated_at as progress_updated_at
    ');
    
    // CRITICAL: Start from candidate_job_assignments - ONE ROW PER JOB
    $this->db->from('candidate_job_assignments cja');
    
    // Get candidate details
    $this->db->join('candidates c', 'c.id = cja.candidate_id AND c.removed = 0', 'inner');
    
    // Get job details
    $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.removed = 0', 'left');
    
    // Get onboarding progress for THIS job
    $this->db->join('candidate_onboarding_progress cop', 
        'cop.candidate_id = c.id AND cop.job_id = j.id', 
        'left');
    
    // Filter: Only this recruiter's assignments
    $this->db->group_start();
    $this->db->where('c.recruiter_id', $recruiter_id);
    $this->db->or_where('c.assigned_agent_id', $recruiter_id);
    
    $this->db->group_end();
    
    // Only active assignments
    $this->db->where('cja.removed', 0);
    
    // ORDER - show all rows
    $this->db->order_by('c.last_name', 'ASC');
    $this->db->order_by('c.first_name', 'ASC');
    $this->db->order_by('j.name', 'ASC');
    
    $query = $this->db->get();
    $candidates = $query->result();
    
    // DEBUG: Show what we found
    echo "<!-- DEBUG: Found " . count($candidates) . " records -->\n";
    foreach ($candidates as $index => $c) {
        echo "<!-- Record $index: {$c->first_name} {$c->last_name} → Job: " . 
             ($c->job_name ?: 'Unknown') . " (ID: {$c->job_id}) -->\n";
    }
    
    // Calculate statistics
    $stats = new stdClass();
    $stats->total_candidates = count($candidates);
    $stats->under_review_count = 0;
    $stats->submitted_hm_count = 0;
    $stats->hm_decision_count = 0;
    $stats->completed_count = 0;
    $stats->not_started_count = 0;
    
    foreach ($candidates as $candidate) {
        if ($candidate->onboarding_stage === 'completed' || $candidate->stage_position_offered) {
            $stats->completed_count++;
        } elseif ($candidate->stage_hm_decision && !empty($candidate->hm_decision)) {
            $stats->hm_decision_count++;
        } elseif ($candidate->stage_submitted_to_hm) {
            $stats->submitted_hm_count++;
        } elseif ($candidate->stage_under_review) {
            $stats->under_review_count++;
        } else {
            $stats->not_started_count++;
        }
    }

    $this->breadcrumbs = array(
        array(
            'title' => lang('candidates_heading'),
            'url'   => site_url('recruiter/candidates')
        ),
        array(
            'title' => 'Onboarding Management',
            'url'   => site_url('recruiter/candidates/onboarding_listing')
        ),
    );

    $this->load->view($this->folder . '/view_header');
    $this->load->view('recruiter/candidates/onboarding_listing', array(
        'candidates' => $candidates,
        'stats' => $stats,
        'heading' => 'Onboarding Management (View Only)',
        'current_recruiter_id' => $recruiter_id,
        'recruiter_name' => $this->get_recruiter_name($recruiter_id)
    ));
    $this->load->view($this->folder . '/view_footer');
}


/**
 * Get recruiter's name for display
 */
private function get_recruiter_name($recruiter_id)
{
    $this->db->select('first_name, last_name');
    $this->db->from('recruiters');
    $this->db->where('id', $recruiter_id);
    $this->db->where('removed', 0);
    $this->db->where('enabled', 1);
    
    $recruiter = $this->db->get()->row();
    
    if ($recruiter) {
        return $recruiter->first_name . ' ' . $recruiter->last_name;
    }
    
    return 'Recruiter';
}



/**
 * Calculate onboarding statistics
 */
private function calculate_onboarding_stats($candidates) {
    $stats = new stdClass();
    $stats->total_candidates = count($candidates);
    $stats->not_started_count = 0;
    $stats->under_review_count = 0;
    $stats->submitted_hm_count = 0;
    $stats->hm_decision_count = 0;
    $stats->completed_count = 0;
    $stats->documents_requested_count = 0;

    foreach ($candidates as $candidate) {
        // Determine stage
        if ($candidate->onboarding_stage === 'completed' || $candidate->stage_position_offered) {
            $stats->completed_count++;
        } elseif ($candidate->stage_hm_decision && !empty($candidate->hm_decision)) {
            $stats->hm_decision_count++;
        } elseif ($candidate->stage_submitted_to_hm) {
            $stats->submitted_hm_count++;
        } elseif ($candidate->stage_under_review) {
            $stats->under_review_count++;
        } elseif ($candidate->stage_requested_docs) {
            $stats->documents_requested_count++;
        } else {
            $stats->not_started_count++;
        }
    }

    return $stats;
}

/**
 * Determine current stage for a candidate-job assignment
 */
private function determine_stage($candidate) {
    if ($candidate->onboarding_stage === 'completed' || $candidate->stage_position_offered) {
        return 'completed';
    } elseif ($candidate->stage_hm_decision && !empty($candidate->hm_decision)) {
        return 'hm_decision';
    } elseif ($candidate->stage_submitted_to_hm) {
        return 'submitted_hm';
    } elseif ($candidate->stage_under_review) {
        return 'under_review';
    } else {
        return 'not_started';
    }
}
}