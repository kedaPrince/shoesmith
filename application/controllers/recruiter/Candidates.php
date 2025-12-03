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
    
    // Load chat model for recruiter
    $this->load->model('recruiter/Model_chat_messages');
     // Load notifications model
    $this->load->model('recruiter/Model_notifications');
}

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
            'url'       => site_url('recruiter/candidates/view/{id}'),
            'icon'      => 'fa-eye',
            'class'     => 'view-row',
            'title'     => 'View detailed candidate profile',
        ),
        'edit' => array(
            'label'     => lang('label_edit'),
            'url'       => redir($this->pageName . '/edit/{id}', true),
            'icon'      => 'fa-edit',
            'class'     => 'edit-row',
            'title'     => 'Edit candidate information',
        ),
        // ADD CHAT ACTION FOR RECRUITER
        'chat' => array(
            'label'     => 'Chat',
            'url'       => site_url('recruiter/candidates/start_candidate_chat/{id}'),
            'icon'      => 'fa-comments',
            'class'     => 'chat-row',
            'title'     => 'Chat with agency about this candidate',
            'target'    => '_blank'
        ),
    );

    $this->filters = array(
        'search' => array(
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
     * Upload document for candidate (Recruiter)
     */
    public function upload_document()
        {
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
    $this->load->view('recruiter/candidates/view_list_extra'); // Add this line
    
    $this->load->view($this->folder . '/' . 'view_header');
    $this->load->view('cms/crud/view_list', array(
        'heading'           => lang($this->pageName . '_heading'),
        'noRows'            => lang($this->pageName . '_no_rows'),
        'filter_job_id'     => $job_id,
    ));
    $this->load->view($this->folder . '/' . 'view_footer');
}

    
        public function view($id)
{
    $row = $this->{$this->model}->get_candidate($id);
    
    if (empty($row)) {
        show_404();
    }

    // Check for pending documents requests
    $documents_request_data = $this->check_pending_documents_request($id);
    
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
    
    // Add chat button to view page
    echo '
    <script>
    $(document).ready(function() {
        // Add chat button to the action buttons area
        var chatButton = \'<a href="\' + base_url + \'recruiter/candidates/start_candidate_chat/' . $id . '\" class="btn btn-info btn-sm chat-row" title="Chat with agency about this candidate" target="_blank"><i class="fa fa-comments"></i> Chat with Agency</a>\';
        
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
        'id' => $id,
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
    public function ajax_quick_manage($id = 0)
        {
            $row = false;
            if (!empty($id)) {
                $row = $this->{$this->model}->get_by_id($id);
            }

            // Get quick manage extra data
            $extra_data = $this->quick_manage_extra($id, $row);
            
            // Check for URL parameter to force required documents tab
            $tab_required = $this->input->get('tab') === 'required';
            if ($tab_required) {
                $extra_data['force_required_tab'] = true;
            }

            $data = array(
                'row' => $row,
                'id' => $id,
            );

            // Merge with extra data - ensure all required variables are passed
            $data = array_merge($data, $extra_data);

            $this->load->view($this->folder . '/' . $this->pageName . '/ajax_manage', $data);
        }
    /**
     * Generate reference number via AJAX
     */
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
                    
                    
                    // CRITICAL: Ensure job_id is set from additional_job_ids
                    // $additional_jobs = $this->input->post('additional_job_ids') ?: [];
                    // if (empty($data['job_id']) && !empty($additional_jobs)) {
                    //     $data['job_id'] = $additional_jobs[0];
                    // }
                    
                    // // If still no job_id, return error
                    // if (empty($data['job_id'])) {
                    //     $error_message = 'Please select at least one job.';
                        
                    //     if ($is_ajax) {
                    //         $this->output
                    //             ->set_content_type('application/json')
                    //             ->set_output(json_encode(['success' => false, 'error' => $error_message]));
                    //         return;
                    //     } else {
                    //         $this->session->set_flashdata('error', $error_message);
                    //         redirect(redir($this->pageName, true));
                    //     }
                    // }
                    
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
                        
                       // In the create() method, after the candidate is created successfully:
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
                            
                            // ✅ ADD THIS: Send notification to agency about new candidate
                            try {
                                $job_id = $data['job_id'] ?? null;
                                $recruiter_id = $this->get_recruiter_id();
                                
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
                            
                            // For AJAX requests, return JSON
                            if ($is_ajax) {
                                $response = [
                                    'success' => $success,
                                    'message' => $message,
                                    'id' => $id
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
                                ->set_output(json_encode(['success' => false, 'error' => $error_message]));
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
    /**
     * Check if candidate already exists by email (without job requirement)
     */
    private function check_existing_candidate_by_email($email)
        {
            $this->db->where('email', $email);
            $this->db->where('removed', 0);
            
            $result = $this->db->get('candidates')->row();
            
            return $result;
        }

    public function update($id = null)
    {
        // Better AJAX detection
        $is_ajax = $this->input->is_ajax_request() || 
                (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        
        // Get ID from URL if not provided
        if (empty($id)) {
            $id = $this->input->post('id');
        }
        
        if (empty($id)) {
            if ($is_ajax) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'error' => 'Candidate ID is required for update.']));
                return;
            } else {
                show_404();
            }
        }

        if ($this->input->post()) {    
            // Validate form
            if ($this->validate_form('update')) {                
                // Get post data
                $data = $this->get_post_data();
                
                // Check for status change before update
                $old_candidate = $this->{$this->model}->get_by_id($id);
                $old_status = $old_candidate->status ?? null;
                $new_status = $data['status'] ?? null;
                $status_changed = ($old_status && $new_status && $old_status !== $new_status);
                
                // Add extra parameters
                $extra_params = $this->update_extra_params($id);                
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
                    $result = $this->{$this->model}->update($data, $id, 'id');
                    
                    if ($result) {
                        // Handle file upload
                        $this->handle_file_upload_manual($id);
                        
                        // Handle pivot tables - THIS IS WHERE THE FIX IS
                        $this->handle_pivot_tables($id);
                        
                        // Send status change notification if status changed
                        if ($status_changed) {
                            try {
                                $recruiter_id = $this->get_recruiter_id();
                                $this->create_status_change_notification($id, $old_status, $new_status, $recruiter_id);
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
                                'id' => $id,
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
                    redirect(redir($this->pageName . '/edit/' . $id, true));
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
            $candidate_id = $this->input->post('candidate_id');
            $notification_id = $this->input->post('notification_id');
            $submission_notes = $this->input->post('submission_notes');
            
            // Check if candidate exists and recruiter has access
            $candidate = $this->{$this->model}->get_candidate($candidate_id);
            if (empty($candidate)) {
                ajax_return(['success' => false, 'message' => 'Candidate not found.']);
                return;
            }
            
            // Check access
            $has_access = $this->{$this->model}->check_recruiter_candidate_access($this->get_recruiter_id(), $candidate_id);
            if (!$has_access) {
                ajax_return(['success' => false, 'message' => 'Access denied.']);
                return;
            }
            
            // Handle multiple document uploads
            $uploaded_documents = [];
            $errors = [];
            
            // Get the document data from POST
            $document_names = $this->input->post('required_documents');
            
            if (!empty($document_names) && is_array($document_names)) {
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
            } else {
                ajax_return(['success' => false, 'message' => 'No document data received.']);
                return;
            }
            
            if (!empty($errors) && empty($uploaded_documents)) {
                ajax_return(['success' => false, 'message' => 'All uploads failed: ' . implode(', ', $errors)]);
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
                
                ajax_return([
                    'success' => true, 
                    'message' => $message, 
                    'documents' => $uploaded_documents,
                    'stage_updated' => $stage_updated
                ]);
            } else {
                ajax_return(['success' => false, 'message' => 'No documents were successfully uploaded.']);
            }
        }

    private function upload_single_required_document($candidate_id, $document_name, $description, $file_index)
        {
            $config['upload_path'] = './uploads/candidate_documents/required/';
            $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
            $config['max_size'] = 10240; // 10MB
            $config['encrypt_name'] = true;
            
            // Create upload directory if it doesn't exist
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }
            
            $this->load->library('upload', $config);
            
            // Handle the file upload for this specific index - FIXED VERSION
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
                unlink($upload_data['full_path']);
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

            // ADD THIS: Filter by recruiter's assigned candidates - FIXED VERSION
            $this->filter_by_recruiter();

            // Add job filtering if job_id is provided in filters
            if (!empty($filters['job_id'])) {
                $job_id = $filters['job_id'];
                $this->db->group_start();
                $this->db->where('candidates.job_id', $job_id); // Primary job assignment
                $this->db->or_where("candidates.id IN (SELECT candidate_id FROM candidate_jobs WHERE job_id = $job_id)"); // Additional job assignments
                $this->db->group_end();
            }

            // Apply any additional filters from the CRUD system
            if (!empty($filters['general'])) {
                $this->db->group_start();
                foreach (['candidates.first_name', 'candidates.last_name', 'candidates.email', 'candidates.reference_number'] as $field) {
                    $this->db->or_like($field, $filters['general']);
                }
                $this->db->group_end();
            }

            if (!empty($filters['status'])) {
                $this->db->where('candidates.status', $filters['status']);
            }

            // Sorting
            if ($sort_by) {
                if (!in_array($sort_by, ['agency_name', 'job_name'])) {
                    $this->db->order_by("candidates.$sort_by", $sort_order ?: 'ASC');
                }
            }

            if ($limit !== null) {
                $this->db->limit($limit, $offset);
            }

            // DEBUG: Log the final query
            $query = $this->db->get();
           

            return $query;
        }

    public function quick_manage_extra($id, $row): array
    {
        // Safely handle the row parameter
        if (is_string($row) || $row === null || $row === false) {
            $row = new stdClass();
            $row->id = 0;
            $row->job_id = null;
            $row->agency_id = null;
            $row->first_name = null;
            $row->last_name = null;
            $row->assigned_agent_id = null;
        }

        $agencies = $this->{$this->model}->get_agencies_all();
        $jobs = $this->{$this->model}->get_jobs_all();
        $pre_selected_job_id = $this->session->userdata('pre_selected_job_id');
        
        if (empty($id) && $pre_selected_job_id) {
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
        if (empty($id) && !empty($logged_in_recruiter_id)) {
            $row->assigned_agent_id = $logged_in_recruiter_id;
        }

        // Get all recruiters for the dropdown (for existing candidates)
        $agents = $this->get_all_recruiters();

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

        // Check for pending documents requests
        $documents_request_data = $this->check_pending_documents_request($id);
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
            // ADD THIS: Pass the logged-in recruiter ID to the view
            'logged_in_recruiter_id' => $logged_in_recruiter_id,
            'logged_in_recruiter' => $this->get_logged_in_recruiter_details($logged_in_recruiter_id),
            

            
        ];
        // Temporary debug logging
    
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
public function for_job($job_id)
{
    $recruiter_id = $this->get_current_recruiter_id();
    
    log_message('debug', 'Candidates::for_job() called with job_id: ' . $job_id);
    log_message('debug', 'Recruiter ID: ' . $recruiter_id);
    
    // Get job details - ALL recruiters can see ALL jobs
    $this->db->select('mod_jobs.*, agencies.name as agency_name');
    $this->db->from('mod_jobs');
    $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
    $this->db->where('mod_jobs.id', $job_id);
    
    $job = $this->db->get()->row();
    
    if (!$job) {
        log_message('error', 'Job not found: ' . $job_id);
        show_404();
    }

    log_message('debug', 'Job found: ' . $job->name);
    
    // Load the jobs model
    $this->load->model('recruiter/model_jobs');
    
    // Get ONLY this recruiter's candidates for this job
    $candidates = $this->model_jobs->get_candidates_for_job($job_id, $recruiter_id);
    
    log_message('debug', 'Found ' . count($candidates) . ' candidates for job ' . $job_id . ' for recruiter ' . $recruiter_id);
    
    $data = [
        'candidates' => $candidates,
        'job' => $job,
        'total_candidates' => count($candidates),
        'current_recruiter_id' => $recruiter_id,
        'job_id' => $job_id // Pass job_id to the view
    ];

    // Set breadcrumbs
    $this->breadcrumbs = array(
        array(
            'title' => 'Jobs',
            'url' => site_url('recruiter/jobs'),
        ),
        array(
            'title' => $job->name,
            'url' => site_url('recruiter/jobs/view/' . $job->id),
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
/**
 * Remove candidate from job (CRUD-style)
 */
public function remove_from_job($candidate_id, $job_id)
{
    $recruiter_id = $this->get_recruiter_id();
    
    // Verify the assignment belongs to this recruiter and exists
    $assignment = $this->db->where('candidate_id', $candidate_id)
                          ->where('job_id', $job_id)
                          ->where('assigned_agent_id', $recruiter_id)
                          ->where('removed', 0)
                          ->get('candidate_job_assignments')
                          ->row();
    
    if (!$assignment) {
        flash_notification('Assignment not found or access denied', 'error');
        redirect('recruiter/candidates/for_job/' . $job_id);
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
    $job = $this->db->where('id', $job_id)->get('mod_jobs')->row();
    
    Logger::log('Removed candidate from job', [
        'candidate_id' => $candidate_id,
        'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
        'job_id' => $job_id,
        'job_name' => $job->name
    ]);

    flash_notification('Candidate removed from job successfully', 'success');
    redirect('recruiter/candidates/for_job/' . $job_id);
}


/**
 * Get candidate chat information for recruiter
 */
public function ajax_get_candidate_chat_info($candidate_id)
{
    if (!$this->input->is_ajax_request()) {
        show_404();
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

/**
 * Start chat for candidate (recruiter side)
 */
public function start_candidate_chat($candidate_id)
{
    $recruiter_id = $this->get_recruiter_id();
    
    if (!$recruiter_id) {
        show_error('Access denied', 403);
    }
    
    // Get candidate details and verify access
    $this->db->select('c.*, ca.agency_id')
             ->from('candidates c')
             ->join('candidate_agencies ca', 'ca.candidate_id = c.id')
             ->where('c.id', $candidate_id)
             ->where('c.removed', 0);
    
    $candidate = $this->db->get()->row();
    
    if (!$candidate) {
        show_404();
    }
    
    // Verify recruiter has access to this candidate
    $has_access = $this->{$this->model}->check_recruiter_candidate_access($recruiter_id, $candidate_id);
    
    if (!$has_access) {
        show_error('Access denied to this candidate', 403);
    }
    
    // Create or get conversation using UUID
    $conversation = $this->Model_chat_messages->get_or_create_candidate_conversation(
        $candidate->agency_id,
        $recruiter_id,
        $candidate_id,
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
}