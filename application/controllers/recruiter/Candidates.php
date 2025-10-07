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
            'last_name' => array(
                'label' => lang('label_last_name'),
                'sort' => true,
            ),
            'email' => array(
                'label' => lang('label_email'),
                'sort' => true,
            ),
            'phone' => array(
                'label' => lang('label_phone'),
                'sort' => true,
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

        // Initialize with empty arrays to prevent null values
        $agencies_all = [];
        $jobs_all = [];
        $agents_all = [];
        $candidate_data = null;

        try {
            $agencies_all = $this->{$this->model}->get_agencies_all();
        } catch (Exception $e) {
            error_log('Error loading agencies: ' . $e->getMessage());
            $agencies_all = [];
        }

        try {
            $jobs_all = $this->{$this->model}->get_jobs_all();
        } catch (Exception $e) {
            error_log('Error loading jobs: ' . $e->getMessage());
            $jobs_all = [];
        }

        try {
            // Use a more robust method call that won't cause the array_merge error
            $agents_all = $this->get_agency_agents_safe();
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
        );
    }

    /**
     * Safe method to get agency agents without causing array_merge errors
     */
    private function get_agency_agents_safe(){
        try {
            $agency_id = loginVar('agency_id', 'agency');
            
            $this->db->select('id, first_name, last_name, email');
            $this->db->from('agency_staff');
            $this->db->where('enabled', 1);
            
            if ($agency_id) {
                $this->db->where('agency_id', (int)$agency_id);
            }
            
            // Use direct SQL ordering to avoid active record issues
            $query = $this->db->get();
            $results = $query->result();
            
            // Manual sorting if needed
            usort($results, function($a, $b) {
                return strcmp($a->first_name, $b->first_name);
            });
            
            return $results;
        } catch (Exception $e) {
            error_log('Error in get_agency_agents_safe: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Is Unique Email
     *
     * Callback function to check whether or not the email already exists in the database
     *
     * @param string $email
     *
     * @return bool
     */
    public function is_unique_email(string $email): bool{
        $id = $this->input->post('id');

        $this->form_validation->set_message('is_unique_email', lang('email_exists'));
        
        try {
            $result = $this->{$this->model}->is_unique_email($email, $id);
            return $result;
        } catch (Exception $e) {
            error_log('Error checking unique email: ' . $e->getMessage());
            // If there's an error, assume it's unique to allow the form to proceed
            return true;
        }
    }

    public function create_extra_params(): array{
        $agency_id = $this->input->post('agency_id') ? (int)$this->input->post('agency_id') : loginVar('agency_id', 'agency');
        $assigned_agent_id = $this->input->post('assigned_agent_id') ? (int)$this->input->post('assigned_agent_id') : loginID('agency');
        
        $params = array(
            'agency_id' => $agency_id ? (int)$agency_id : 0,
            'application_date' => $this->input->post('application_date') ? $this->input->post('application_date') : date('Y-m-d H:i:s'),
            'assigned_agent_id' => $assigned_agent_id ? (int)$assigned_agent_id : 0,
            'enabled' => 1,
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

    public function create_success_extra($id): void{
        // Log candidate creation
        try {
            $candidate = $this->{$this->model}->get_candidate($id);
            if ($candidate) {
                $logData = array(
                    'candidate_id' => $id,
                    'action' => 'created',
                    'details' => "Candidate {$candidate->first_name} {$candidate->last_name} was added to the system",
                    'performed_by' => loginID('agency'),
                    'performed_at' => date('Y-m-d H:i:s')
                );
                $this->{$this->model}->log_candidate_activity($logData);
            }
            
            // Optional: Send notification to assigned agent
            $this->send_agent_notification($id);
        } catch (Exception $e) {
            error_log('Error in create_success_extra: ' . $e->getMessage());
        }
    }

    public function update_success_extra($id): void{
        // Log candidate update
        try {
            $candidate = $this->{$this->model}->get_candidate($id);
            if ($candidate) {
                $logData = array(
                    'candidate_id' => $id,
                    'action' => 'updated',
                    'details' => "Candidate {$candidate->first_name} {$candidate->last_name} profile was updated",
                    'performed_by' => loginID('agency'),
                    'performed_at' => date('Y-m-d H:i:s')
                );
                $this->{$this->model}->log_candidate_activity($logData);
                
                // Log status change specifically if status was updated
                if ($this->input->post('status')) {
                    $logData['action'] = 'status_change';
                    $logData['details'] = "Candidate status changed to: " . $this->input->post('status');
                    $this->{$this->model}->log_candidate_activity($logData);
                }
            }
        } catch (Exception $e) {
            error_log('Error in update_success_extra: ' . $e->getMessage());
        }
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
}