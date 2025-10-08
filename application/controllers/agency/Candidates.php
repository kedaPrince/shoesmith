<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Candidates extends CRUD_Controller{
    public $pageName = 'candidates';
    public $group = 'Candidates Management';
    public $view = '';
    public $model = 'Model_candidates';
    public $sorting = array('first_name' => 'ASC', 'last_name' => 'ASC');
    public $singular = 'candidate';
    public $plural = 'candidates';
    public $quickManage = true;
    public $identifierField = 'first_name';
    public $hideSubNav = false;
    public $quickManageSize = 3;

    public function __construct(){
        parent::__construct();

        // Access check
        if (!function_exists('getLoggedInUserTypeMenu')) {
            $ci = &get_instance();
            $ci->load->helper('profile_helper');
        }
        
        $userType = getLoggedInUserTypeMenu();
        if ($userType !== 'agency') {
            error_log("REDIRECTING TO DASHBOARD - User type: " . $userType);
            redir('dashboard');
        }

        $this->setup_listing();
        $this->setup_fields();
        $this->load->model($this->folder . '/' . $this->model);
        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );

        // DEBUG: Log agency information
        $agency_id = $this->get_user_agency_id();
        log_message('debug', 'Candidates Controller: Current agency_id = ' . $agency_id);
    }

    private function setup_listing(): void{
        $this->listFields = array(
            'reference_number' => array(
                'label' => lang('label_reference_number'),
                'sort' => true,
            ),
            'first_name' => array(
                'label' => lang('label_first_name'),
                'sort' => true,
            ),
            // 'last_name' => array(
            //     'label' => lang('label_last_name'),
            //     'sort' => true,
            // ),
            'email' => array(
                'label' => lang('label_email'),
                'sort' => true,
            ),
            // 'phone' => array(
            //     'label' => lang('label_phone'),
            //     'sort' => true,
            // ),
            'job_name' => array(
                'label' => lang('label_job'),
                'sort' => true,
                'field' => 'mod_jobs.name'
            ),
            'status' => array(
                'label' => lang('label_status'),
                'sort' => true,
            ),
            'onboarding_stage' => array(
                'label' => 'Onboarding Stage',
                'sort' => true,
                'function' => function($value, $row) {
                    return $this->get_onboarding_stage_display($row);
                }
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
                'url'       => redir($this->pageName . '/view/{id}', true),
                'icon'      => 'fa-eye',
                'class'     => 'view-row',
            ),
            'edit' => array(
                'label'     => lang('label_edit'),
                'url'       => redir($this->pageName . '/edit/{id}', true),
                'icon'      => 'fa-edit',
                'class'     => 'edit-row',
            ),
            'onboarding' => array(
                'label'     => 'Onboarding',
                'url'       => redir($this->pageName . '/onboarding/{id}', true),
                'icon'      => 'fa-eye',
                'class'     => 'onboarding-row',
            ),
            'enable' => array(
                'label'    => lang('label_enable'),
                'url'      => redir($this->pageName . '/enable/{id}', true),
                'icon'     => 'fa-eye',
                'class'    => 'enable-row btn-enable',
                'function' => (function ($str, $row) {
                    return ($row->enabled) ? false : $str;
                }),
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => redir($this->pageName . '/disable/{id}', true),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => (function ($str, $row) {
                    return (!$row->enabled) ? false : $str;
                }),
            ),
            'delete' => array(
                'label'     => lang('label_delete'),
                'url'       => redir($this->pageName . '/remove/{id}', true),
                'icon'      => 'fa-trash-o',
                'class'     => 'delete-row btn-delete',
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
            'onboarding_stage' => array(
                'label' => 'Onboarding Stage',
                'type' => 'dropdown',
                'field' => 'candidates.onboarding_stage',
                'options' => array(
                    'not_started' => 'Not Started',
                    'stage_under_review' => 'Under Review',
                    'stage_submitted_to_hm' => 'Submitted to HM',
                    'stage_requested_docs' => 'Requested Documents',
                    'stage_position_offered' => 'Position Offered',
                    'completed' => 'Completed',
                ),
            ),
        );
    }

    public function setup_fields(): void{
        $this->formFields = array(
            'main' => array(
                'reference_number'  => 'trim|required|strip_tags',
                'first_name'        => 'trim|required|strip_tags',
                'last_name'         => 'trim|required|strip_tags',
                'email'             => 'trim|required|valid_email|callback_is_unique_email',
                'phone'             => 'trim|strip_tags',
                'id_number'         => 'trim|strip_tags',
                'date_of_birth'     => 'trim|valid_date',
                'gender'            => 'trim|strip_tags',
                'address'           => 'trim|strip_tags',
                'city'              => 'trim|strip_tags',
                'province'          => 'trim|strip_tags',
                'postal_code'       => 'trim|strip_tags',
                'country'           => 'trim|strip_tags',
                'highest_qualification' => 'trim|strip_tags',
                'years_experience'  => 'trim|numeric',
                'current_position'  => 'trim|strip_tags',
                'current_company'   => 'trim|strip_tags',
                'current_salary'    => 'trim|decimal',
                'expected_salary'   => 'trim|decimal',
                'notice_period'     => 'trim|numeric',
                'cover_letter'      => 'trim|strip_tags',
                'source'            => 'trim|strip_tags',
                'status'            => 'trim|required|strip_tags',
                'rating'            => 'trim|numeric',
                'notes'             => 'trim|strip_tags',
                'agency_id'         => 'trim|required|numeric',
                'job_id'            => 'trim|numeric',
                'assigned_agent_id' => 'trim|numeric',
                // Onboarding stages
                'stage_under_review' => 'trim|numeric',
                'stage_submitted_to_hm' => 'trim|numeric',
                'stage_requested_docs' => 'trim|numeric',
                'stage_position_offered' => 'trim|numeric',
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
     * Get onboarding stage display for listing
     */
    private function get_onboarding_stage_display($row) {
        $stages = [
            'stage_under_review' => ['label' => 'Under Review', 'completed' => $row->stage_under_review],
            'stage_submitted_to_hm' => ['label' => 'Submitted to HM', 'completed' => $row->stage_submitted_to_hm],
            'stage_requested_docs' => ['label' => 'Requested Docs', 'completed' => $row->stage_requested_docs],
            'stage_position_offered' => ['label' => 'Position Offered', 'completed' => $row->stage_position_offered],
        ];

        $current_stage = 'Not Started';
        $all_completed = true;

        foreach ($stages as $stage => $data) {
            if ($data['completed'] == 1) {
                $current_stage = $data['label'];
            } else {
                $all_completed = false;
                break;
            }
        }

        if ($all_completed) {
            return '<span class="label label-success">Completed</span>';
        }

        return '<span class="label label-info">' . $current_stage . '</span>';
    }

    /**
     * Onboarding management page
     */
    /**
 * Onboarding management page
 */
public function onboarding($id) {
    $agency_id = $this->get_user_agency_id();
    if ($agency_id) {
        $this->db->where('agency_id', $agency_id);
    }

    $candidate = $this->{$this->model}->get_candidate_details($id);
    
    if (!$candidate) {
        show_error('Candidate not found or you do not have permission to access it.');
    }

    $this->breadcrumbs = array(
        array(
            'title' => lang($this->pageName . '_heading'),
            'url'   => redir($this->pageName, true)
        ),
        array(
            'title' => 'Onboarding: ' . $candidate->first_name . ' ' . $candidate->last_name,
            'url'   => redir($this->pageName . '/onboarding/' . $id, true)
        ),
    );

    $this->load->view($this->folder . '/' . 'view_header');
    // CORRECTED: Load from agency/candidates folder
    $this->load->view('agency/candidates/onboarding', array(
        'candidate' => $candidate,
        'heading'   => 'Onboarding: ' . $candidate->first_name . ' ' . $candidate->last_name,
    ));
    $this->load->view($this->folder . '/' . 'view_footer');
}

    /**
     * Update onboarding stage via AJAX
     */
    public function update_onboarding_stage() {
        $candidate_id = $this->input->post('candidate_id');
        $stage = $this->input->post('stage');
        $value = $this->input->post('value');

        // Verify the candidate belongs to the current agency
        $agency_id = $this->get_user_agency_id();
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id);
        }

        $result = $this->{$this->model}->update_onboarding_stage($candidate_id, $stage, $value);

        if ($result) {
            // Log the activity
            $stage_labels = [
                'stage_under_review' => 'Under Review',
                'stage_submitted_to_hm' => 'Submitted to Hiring Manager',
                'stage_requested_docs' => 'Requested Further Documents',
                'stage_position_offered' => 'Position Offered'
            ];

            $action = $value ? 'completed' : 'reopened';
            $this->{$this->model}->log_candidate_activity([
                'candidate_id' => $candidate_id,
                'action' => 'onboarding_stage_' . $action,
                'description' => $stage_labels[$stage] . ' stage ' . $action,
                'created_by' => loginID('agency'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            ajax_return([
                'success' => true,
                'message' => 'Onboarding stage updated successfully'
            ]);
        } else {
            ajax_return([
                'success' => false,
                'message' => 'Failed to update onboarding stage'
            ]);
        }
    }

    /**
     * Get onboarding statistics
     */
    public function onboarding_stats() {
        $agency_id = $this->get_user_agency_id();
        $stats = $this->{$this->model}->get_onboarding_stats($agency_id);

        ajax_return([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * OVERRIDE get_all to filter by agency_id - ENHANCED
     */
    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
    {
        $agency_id = $this->get_user_agency_id();
        
        if (!empty($agency_id)) {
            $this->db->where('candidates.agency_id', $agency_id);
            log_message('debug', 'Controller get_all: Applied agency filter for candidates: ' . $agency_id);
        } else {
            log_message('error', 'Controller get_all: No agency_id found for filtering');
            // For safety, show no candidates if no agency ID
            $this->db->where('candidates.agency_id', 0);
        }
        
        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

    /**
     * Get user agency ID - ENHANCED
     */
    private function get_user_agency_id()
    {
        $login = $this->session->userdata('login');
        
        if (!empty($login['agency'])) {
            $agency_user = $login['agency'];
            
            log_message('debug', 'Agency user session data: ' . print_r($agency_user, true));
            
            if (!empty($agency_user['agency_id'])) {
                return $agency_user['agency_id'];
            } elseif (!empty($agency_user['id'])) {
                return $agency_user['id'];
            }
        }
        
        log_message('error', 'No agency_id found in session for agency user');
        return null;
    }

    public function index(): void{
        $this->breadcrumbs = array(
            array(
                'title' => lang($this->pageName . '_heading'),
                'url'   => redir($this->pageName, true)
            ),
        );
        $this->view = 'listing';

        $this->load->view($this->folder . '/' . 'view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading'           => lang($this->pageName . '_heading'),
            'noRows'            => lang($this->pageName . '_no_rows'),
        ));
        $this->load->view($this->folder . '/' . 'view_footer');
    }

    public function quick_manage_extra($id, $row): array{
        $candidateId = is_bool($id) || !is_object($row) ? 0 : (int)$row->id;
        $agency_id = $this->get_user_agency_id();

        // Initialize with empty arrays to prevent null values
        $agencies_all = [];
        $jobs_all = [];
        $agents_all = [];
        $candidate_data = null;

        try {
            // Only get the current agency
            $agencies_all = $this->{$this->model}->get_agency_by_id($agency_id);
        } catch (Exception $e) {
            error_log('Error loading agencies: ' . $e->getMessage());
            $agencies_all = [];
        }

        try {
            // Only get jobs for this agency
            $jobs_all = $this->{$this->model}->get_jobs_by_agency($agency_id);
        } catch (Exception $e) {
            error_log('Error loading jobs: ' . $e->getMessage());
            $jobs_all = [];
        }

        try {
            // Only get agents for this agency
            $agents_all = $this->{$this->model}->get_agency_agents_by_agency($agency_id);
        } catch (Exception $e) {
            error_log('Error loading agents: ' . $e->getMessage());
            $agents_all = [];
        }

        try {
            $candidate_data = $this->{$this->model}->get_candidate_details($candidateId);
        } catch (Exception $e) {
            error_log('Error loading candidate data: ' . $e->getMessage());
            $candidate_data = null;
        }

        return array(
            'agencies_all'      => $agencies_all,
            'jobs_all'          => $jobs_all,
            'agents_all'        => $agents_all,
            'candidate_data'    => $candidate_data,
            'user_agency_id'    => $agency_id,
        );
    }

    /**
     * Is Unique Email
     */
    public function is_unique_email(string $email): bool{
        $id = $this->input->post('id');

        $this->form_validation->set_message('is_unique_email', lang('email_exists'));
        
        try {
            $result = $this->{$this->model}->is_unique_email($email, $id);
            return $result;
        } catch (Exception $e) {
            error_log('Error checking unique email: ' . $e->getMessage());
            return true;
        }
    }

    public function create_extra_params(): array{
        $agency_id = $this->get_user_agency_id();
        $assigned_agent_id = $this->input->post('assigned_agent_id') ? (int)$this->input->post('assigned_agent_id') : loginID('agency');
        
        $params = array(
            'agency_id' => $agency_id ? (int)$agency_id : 0,
            'application_date' => $this->input->post('application_date') ? $this->input->post('application_date') : date('Y-m-d H:i:s'),
            'assigned_agent_id' => $assigned_agent_id ? (int)$assigned_agent_id : 0,
            'enabled' => 1,
            // Initialize onboarding stages
            'stage_under_review' => 0,
            'stage_submitted_to_hm' => 0,
            'stage_requested_docs' => 0,
            'stage_position_offered' => 0,
            'onboarding_stage' => 'not_started',
            'onboarding_progress' => 0,
        );
        
        // Auto-generate reference number if not provided
        if (!$this->input->post('reference_number')) {
            try {
                $params['reference_number'] = $this->{$this->model}->generate_reference_number();
            } catch (Exception $e) {
                error_log('Error generating reference number: ' . $e->getMessage());
                $params['reference_number'] = 'CAND-' . date('Y') . '-0001';
            }
        }
        
        return $params;
    }

    public function update_extra_params($id): array{
        $params = array();
        
        // Only update these fields if they're provided
        if ($this->input->post('agency_id')) {
            $params['agency_id'] = (int)$this->input->post('agency_id');
        }
        
        if ($this->input->post('assigned_agent_id')) {
            $params['assigned_agent_id'] = (int)$this->input->post('assigned_agent_id');
        }
        
        if ($this->input->post('job_id')) {
            $params['job_id'] = (int)$this->input->post('job_id');
        }
        
        // Update status change timestamp if status changed
        try {
            $currentCandidate = $this->{$this->model}->get_candidate($id);
            if ($currentCandidate && $this->input->post('status') && $currentCandidate->status != $this->input->post('status')) {
                $params['status_updated_at'] = date('Y-m-d H:i:s');
            }
        } catch (Exception $e) {
            error_log('Error getting candidate for status update: ' . $e->getMessage());
        }
        
        return $params;
    }

    /**
     * Generate reference number for candidate (called from AJAX)
     */
    public function generate_reference(){
        try {
            $reference = $this->{$this->model}->generate_reference_number();
            ajax_return(array(
                'success' => true,
                'reference' => $reference
            ));
        } catch (Exception $e) {
            error_log('Error generating reference: ' . $e->getMessage());
            ajax_return(array(
                'success' => false,
                'error' => 'Failed to generate reference number'
            ));
        }
    }

    /**
     * Send notification to assigned agent
     */
    private function send_agent_notification($candidateId){
        try {
            $candidate = $this->{$this->model}->get_candidate($candidateId);
            
            if ($candidate && $candidate->assigned_agent_id) {
                // Get agent details
                $agent = $this->{$this->model}->get_agent($candidate->assigned_agent_id);
                
                if ($agent) {
                    // Here you would typically send an email or internal notification
                    $notificationData = array(
                        'agent_id' => $agent->id,
                        'candidate_id' => $candidateId,
                        'message' => "New candidate assigned: {$candidate->first_name} {$candidate->last_name}",
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    
                    $this->{$this->model}->create_agent_notification($notificationData);
                }
            }
        } catch (Exception $e) {
            error_log('Error sending agent notification: ' . $e->getMessage());
        }
    }

    /**
     * OVERRIDE: Enable candidate with agency check
     */
    public function enable($id) {
        // Verify the candidate belongs to the current agency
        $agency_id = $this->get_user_agency_id();
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id);
        }
        parent::enable($id);
    }

    /**
     * OVERRIDE: Disable candidate with agency check
     */
    public function disable($id) {
        // Verify the candidate belongs to the current agency
        $agency_id = $this->get_user_agency_id();
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id);
        }
        parent::disable($id);
    }

    /**
     * OVERRIDE: Remove candidate with agency check
     */
    public function remove($id) {
        // Verify the candidate belongs to the current agency
        $agency_id = $this->get_user_agency_id();
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id);
        }
        parent::remove($id);
    }

    /**
     * OVERRIDE: View candidate with agency check
     */
    public function view($id) {
        // Verify the candidate belongs to the current agency
        $agency_id = $this->get_user_agency_id();
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id);
        }
        parent::view($id);
    }

    /**
     * OVERRIDE: Edit candidate with agency check
     */
    public function edit($id) {
        // Verify the candidate belongs to the current agency
        $agency_id = $this->get_user_agency_id();
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id);
        }
        parent::edit($id);
    }

    /**
     * Debug function to check agency filtering
     */
    public function debug_agency() {
        if (ENVIRONMENT !== 'development') {
            show_404();
        }

        echo "<h1>Agency Debug Information</h1>";
        
        echo "<h2>Session Data:</h2>";
        echo "<pre>";
        print_r($this->session->userdata('login'));
        echo "</pre>";
        
        echo "<h2>Current Agency ID:</h2>";
        $agency_id = $this->get_user_agency_id();
        echo "Agency ID: " . ($agency_id ?? 'NOT FOUND');
        
        echo "<h2>Database Candidates Count:</h2>";
        $total_candidates = $this->db->count_all('candidates');
        echo "Total candidates in database: " . $total_candidates . "<br>";
        
        $this->db->where('agency_id', $agency_id);
        $agency_candidates = $this->db->count_all_results('candidates');
        echo "Candidates for agency {$agency_id}: " . $agency_candidates . "<br>";
        
        echo "<h2>Sample Candidates:</h2>";
        $this->db->select('id, reference_number, first_name, last_name, agency_id');
        $this->db->from('candidates');
        $this->db->limit(10);
        $candidates = $this->db->get()->result();
        echo "<pre>";
        print_r($candidates);
        echo "</pre>";
    }
}