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
    public $adding = false;

    public function __construct(){
        parent::__construct();

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
        $this->load->model('agency/Model_chat_messages');

        $agency_id = $this->get_user_agency_id();
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
            'email' => array(
                'label' => lang('label_email'),
                'sort' => true,
            ),
            'job_name' => array(
                'label' => lang('label_job'),
                'sort' => true,
                'field' => 'jobs.name'
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
                    'url'       => site_url('agency/candidates/view/{uuid}?job={job_uuid}'),
                    'icon'      => 'fa-eye',
                    'class'     => 'view-row',
                    'title'     => 'View candidate',
                ),
            'edit' => array(
                'label'     => lang('label_edit'),
                'url'       => redir($this->pageName . '/edit/{uuid}', true),
                'icon'      => 'fa-edit',
                'class'     => 'edit-row',
                'title'     => 'Edit candidate information',
            ),
            'onboarding' => array(
                'label'     => 'Onboarding',
                'url'       => redir($this->pageName . '/onboarding/{uuid}?job={job_uuid}', true),
                'icon'      => 'fa-eye',
                'class'     => 'onboarding-row',
                'title'     => 'Manage candidate onboarding process',
            ),
            'chat' => array(
                'label'     => 'Chat',
                'url'       => site_url('agency/candidates/start_candidate_chat/{uuid}'),
                'icon'      => 'fa-comments',
                'class'     => 'chat-row',
                'title'     => 'Chat with recruiter about this candidate',
                'target'    => '_blank'
            ),
        );

        $this->filters = array(
            'general' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array(
                    'c.first_name',           // ✓ FIXED
                    'c.last_name',            // ✓ FIXED
                    'c.email',               // ✓ FIXED
                    'c.reference_number',    // ✓ FIXED
                ),
            ),
            'status' => array(
                'label' => lang('label_status'),
                'type' => 'dropdown',
                'field' => 'c.status',       // ✓ WAS: 'candidates.status'
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
                'field' => 'c.onboarding_stage',  // ✓ WAS: 'candidates.onboarding_stage'
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

    public function onboarding($uuid = null) 
    {
        if (!$uuid) {
            show_error('Candidate UUID required', 400);
        }
        
        // Force no cache
        $this->output->set_header('Cache-Control: no-cache, no-store, must-revalidate');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');
        
        // Get candidate
        $candidate = $this->{$this->model}->get_candidate_by_uuid($uuid);
        
        if (empty($candidate)) {
            show_404();
        }
        
        $candidate_id = $candidate->id;
        $candidate_uuid = $candidate->uuid;
        
        if (!$this->enforce_candidate_access($candidate_id)) {
            return;
        }
        
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            show_error('Access denied', 403);
        }

        // CRITICAL: Get the job from URL parameter
        $job_uuid = $this->input->get('job');
        
        if (!$job_uuid) {
            show_error('Job parameter is required. Please access onboarding through the correct link.', 400);
        }
        
        // Get the SPECIFIC job
        $job_info = $this->db->where('uuid', $job_uuid)
                            ->where('agency_id', $agency_id)
                            ->where('removed', 0)
                            ->get('mod_jobs')
                            ->row();
        
        if (!$job_info) {
            show_error('Job not found or you do not have access to it.', 404);
        }
        
        $job_id = $job_info->id;
        
        // Verify this candidate is assigned to THIS specific job
        $assignment = $this->db->where('candidate_id', $candidate_id)
                            ->where('job_id', $job_id)
                            ->where('removed', 0)
                            ->get('candidate_job_assignments')
                            ->row();
        
        if (!$assignment) {
            show_error('This candidate is not assigned to this job.', 400);
        }
        
        // Get onboarding progress for THIS SPECIFIC JOB ONLY
        $this->db->where('candidate_id', $candidate_id);
        $this->db->where('job_id', $job_id);
        $this->db->where('agency_id', $agency_id);
        $onboarding_progress = $this->db->get('candidate_onboarding_progress')->row();
        
        if (!$onboarding_progress) {
            // Create new record for this job
            $progress_data = [
                'candidate_id' => $candidate_id,
                'job_id' => $job_id,
                'agency_id' => $agency_id,
                'onboarding_stage' => 'not_started',
                'onboarding_progress' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('candidate_onboarding_progress', $progress_data);
            $progress_id = $this->db->insert_id();
            
            $onboarding_progress = $this->db->where('id', $progress_id)->get('candidate_onboarding_progress')->row();
        }
        
        // Merge candidate data with onboarding progress
        $candidate_data = (object) array_merge((array) $candidate, (array) $onboarding_progress);
        
        // Add job info to candidate data
        $candidate_data->job_name = $job_info->name ?? 'Unknown Job';
        $candidate_data->job_ref = $job_info->reference_number ?? 'N/A';
        $candidate_data->job_uuid = $job_info->uuid;
        $candidate_data->job_id = $job_id;
        
        // Add debug info to page
        echo "<!-- DEBUG: Showing job-specific onboarding -->\n";
        echo "<!-- Candidate: {$candidate_data->first_name} {$candidate_data->last_name} -->\n";
        echo "<!-- Job: {$candidate_data->job_name} (UUID: {$candidate_data->job_uuid}) -->\n";
        echo "<!-- Onboarding Stage: {$candidate_data->onboarding_stage} -->\n";

        // GET DOCUMENTS INFO (your existing code)
        $this->db->where('candidate_id', $candidate_id);
        $this->db->where('removed', 0);
        $total_documents_count = $this->db->count_all_results('candidate_documents');

        $required_documents = [];
        $has_required_docs = false;
        $can_mark_reviewed = false;
        
        if (method_exists($this->{$this->model}, 'check_documents_submission_status')) {
            $documents_status = $this->{$this->model}->check_documents_submission_status($candidate_id);
            $required_documents = $documents_status['documents'] ?? [];
            $has_required_docs = $documents_status['has_documents'] ?? false;
            
            $can_mark_reviewed = $has_required_docs && 
                                !$candidate_data->stage_requested_docs && 
                                isset($candidate_data->documents_required) && 
                                $candidate_data->documents_required;
        }

        // SETUP BREADCRUMBS (your existing code)
        $this->breadcrumbs = array(
            array(
                'title' => lang($this->pageName . '_heading'),
                'url'   => redir($this->pageName, true)
            ),
            array(
                'title' => htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name, ENT_QUOTES, 'UTF-8'),
                'url'   => redir($this->pageName . '/view/' . $candidate_uuid . '?job=' . $job_info->uuid, true)
            ),
            array(
                'title' => 'Onboarding',
                'url'   => redir($this->pageName . '/onboarding/' . $candidate_uuid . '?job=' . $job_info->uuid, true)
            ),
        );

        // LOAD VIEW (your existing code)
        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/candidates/onboarding', array(
            'candidate' => $candidate_data,
            'heading' => 'Candidate Onboarding - ' . $candidate->first_name . ' ' . $candidate->last_name,
            'required_documents' => $required_documents,
            'has_required_docs' => $has_required_docs,
            'can_mark_reviewed' => $can_mark_reviewed,
            'candidate_uuid' => $candidate_uuid,
            'job_uuid' => $job_info->uuid,
            'job_id' => $job_id,
            'agency_id' => $agency_id
        ));
        $this->load->view($this->folder . '/view_footer');
    }



    // In Candidates.php, update the get_all method:
    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
    {
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            $this->db->where('candidates.id', 0);
            return parent::get_all($limit, $offset, $sort_by, $sort_order);
        }
        
        // Use the model method that returns candidate-job assignments
        return $this->Model_candidates->get_candidate_job_assignments_for_current_agency($limit, $offset, $sort_by, $sort_order);
    }

    public function onboarding_listing() 
    {
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            show_error('Access denied', 403);
        }

        // ============================================
        // FIXED QUERY: Get stats with proper joins
        // ============================================
        $this->db->select('
            COUNT(DISTINCT cop.id) as total_candidates,
            COUNT(DISTINCT CASE WHEN cop.stage_under_review = 1 THEN cop.id END) as under_review_count,
            COUNT(DISTINCT CASE WHEN cop.stage_submitted_to_hm = 1 THEN cop.id END) as submitted_hm_count,
            COUNT(DISTINCT CASE WHEN cop.stage_hm_decision = 1 THEN cop.id END) as hm_decision_count,
            COUNT(DISTINCT CASE WHEN cop.stage_requested_docs = 1 THEN cop.id END) as requested_docs_count,
            COUNT(DISTINCT CASE WHEN cop.stage_position_offered = 1 THEN cop.id END) as position_offered_count,
            COUNT(DISTINCT CASE WHEN cop.onboarding_stage = "completed" THEN cop.id END) as completed_count,
            COUNT(DISTINCT CASE WHEN cop.hm_decision = "accepted" THEN cop.id END) as hm_accepted_count,
            COUNT(DISTINCT CASE WHEN cop.hm_decision = "rejected" THEN cop.id END) as hm_rejected_count
        ');
        $this->db->from('candidate_onboarding_progress cop');
        $this->db->join('candidates c', 'c.id = cop.candidate_id AND c.removed = 0', 'inner');
        $this->db->join('mod_jobs j', 'j.id = cop.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 'inner');
        $this->db->join('candidate_job_assignments cja', 'cja.candidate_id = c.id AND cja.job_id = j.id AND cja.removed = 0', 'inner');
        $this->db->where('cop.agency_id', $agency_id);
        
        $stats = $this->db->get()->row();
        
        // ============================================
        // FIXED QUERY: Get candidates with proper joins
        // ============================================
        $this->db->select('
            cop.*, 
            c.id as candidate_db_id,
            c.first_name, 
            c.last_name, 
            c.reference_number, 
            c.uuid as candidate_uuid,
            c.status as candidate_status,
            j.name as job_name, 
            j.uuid as job_uuid, 
            j.agency_id as job_agency_id,
            j.reference_number as job_ref,
            cja.id as assignment_id
        ');
        $this->db->from('candidate_onboarding_progress cop');
        $this->db->join('candidates c', 'c.id = cop.candidate_id AND c.removed = 0', 'inner');
        $this->db->join('mod_jobs j', 'j.id = cop.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 'inner');
        $this->db->join('candidate_job_assignments cja', 'cja.candidate_id = c.id AND cja.job_id = j.id AND cja.removed = 0', 'inner');
        $this->db->where('cop.agency_id', $agency_id);
        $this->db->order_by('cop.onboarding_progress', 'DESC');
        
        $candidates = $this->db->get()->result();

        // Debug: Log what we found
        error_log("DEBUG: Agency {$agency_id} sees " . count($candidates) . " candidates in onboarding");
        foreach ($candidates as $c) {
            error_log("  - Candidate {$c->first_name} {$c->last_name} (Job: {$c->job_name}, Job Agency: {$c->job_agency_id}, Assignment ID: {$c->assignment_id})");
        }

        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/candidates/onboarding_listing', array(
            'candidates' => $candidates,
            'stats' => $stats,
            'heading' => 'Onboarding Management',
            'current_agency_id' => $agency_id
        ));
        $this->load->view($this->folder . '/view_footer');
    }

    public function cleanup_orphaned_onboarding()
    {
        // This should be called via cron or manually
        echo "<pre>";
        echo "🧹 Cleaning up orphaned onboarding records\n";
        echo "=========================================\n\n";
        
        // 1. Delete onboarding records where job assignment doesn't exist
        $sql1 = "DELETE cop 
                FROM candidate_onboarding_progress cop
                LEFT JOIN candidate_job_assignments cja ON 
                    cja.candidate_id = cop.candidate_id 
                    AND cja.job_id = cop.job_id
                    AND cja.removed = 0
                WHERE cja.id IS NULL";
        
        $this->db->query($sql1);
        $deleted1 = $this->db->affected_rows();
        echo "✅ Deleted {$deleted1} onboarding records without job assignments\n";
        
        // 2. Delete onboarding records where job doesn't exist or is removed
        $sql2 = "DELETE cop 
                FROM candidate_onboarding_progress cop
                LEFT JOIN mod_jobs j ON j.id = cop.job_id AND j.removed = 0
                WHERE j.id IS NULL";
        
        $this->db->query($sql2);
        $deleted2 = $this->db->affected_rows();
        echo "✅ Deleted {$deleted2} onboarding records for removed jobs\n";
        
        // 3. Delete onboarding records where candidate doesn't exist or is removed
        $sql3 = "DELETE cop 
                FROM candidate_onboarding_progress cop
                LEFT JOIN candidates c ON c.id = cop.candidate_id AND c.removed = 0
                WHERE c.id IS NULL";
        
        $this->db->query($sql3);
        $deleted3 = $this->db->affected_rows();
        echo "✅ Deleted {$deleted3} onboarding records for removed candidates\n";
        
        // 4. Create missing onboarding records for valid assignments
        $sql4 = "INSERT INTO candidate_onboarding_progress (
                    candidate_id, 
                    job_id, 
                    agency_id,
                    onboarding_stage,
                    onboarding_progress,
                    created_at,
                    updated_at
                )
                SELECT 
                    cja.candidate_id,
                    cja.job_id,
                    j.agency_id,
                    'not_started' as onboarding_stage,
                    0 as onboarding_progress,
                    NOW() as created_at,
                    NOW() as updated_at
                FROM candidate_job_assignments cja
                INNER JOIN mod_jobs j ON j.id = cja.job_id AND j.removed = 0
                INNER JOIN candidates c ON c.id = cja.candidate_id AND c.removed = 0
                LEFT JOIN candidate_onboarding_progress cop ON 
                    cop.candidate_id = cja.candidate_id 
                    AND cop.job_id = cja.job_id
                    AND cop.agency_id = j.agency_id
                WHERE cja.removed = 0
                AND cop.id IS NULL
                GROUP BY cja.candidate_id, cja.job_id, j.agency_id";
        
        $this->db->query($sql4);
        $created = $this->db->affected_rows();
        echo "✅ Created {$created} missing onboarding records\n";
        
        echo "\n🎯 Total cleanup completed!\n";
        echo "Deleted: " . ($deleted1 + $deleted2 + $deleted3) . " records\n";
        echo "Created: {$created} records\n";
        echo "</pre>";
    }

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

    public function upload_document() {
        $candidate_uuid = $this->input->post('candidate_uuid'); 
        $candidate = $this->{$this->model}->get_candidate($candidate_uuid);
        if (!$candidate) {
            ajax_return(['success' => false, 'message' => 'Candidate not found']);
            return;
        }
        
        $candidate_id = $candidate->id;
        
        if (!$this->enforce_candidate_access($candidate_id)) {
            return;
        }
        
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            ajax_return(['success' => false, 'message' => 'Invalid CSRF token. Please refresh and try again.']);
            return;
        }
        
        $document_name = $this->input->post('document_name');
        $document_type = $this->input->post('document_type');
        $description = $this->input->post('description');

        if (empty($_FILES['document_file']['name'])) {
            ajax_return(['success' => false, 'message' => 'Please select a file to upload.']);
            return;
        }

        $config['upload_path'] = './uploads/candidate_documents/';
        $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
        $config['max_size'] = 10240;
        $config['encrypt_name'] = true;

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('document_file')) {
            ajax_return(['success' => false, 'message' => $this->upload->display_errors()]);
            return;
        }

        $upload_data = $this->upload->data();

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

        $this->load->model('recruiter/Model_candidates');
        $result = $this->Model_candidates->save_candidate_document($document_data);

        if ($result) {
            $this->load->model('agency/Model_notifications');
            $this->Model_notifications->create_documents_uploaded_notification($candidate_id, loginID('recruiter'), 1);

            ajax_return(['success' => true, 'message' => 'Document uploaded successfully!']);
        } else {
            ajax_return(['success' => false, 'message' => 'Failed to save document information.']);
        }
    }

    public function get_documents($candidate_id) {
        if (!$this->enforce_candidate_access($candidate_id)) {
            return;
        }
        $this->load->model('recruiter/Model_candidates');
        $documents = $this->Model_candidates->get_candidate_documents($candidate_id);
        
        $html = '';
        if (!empty($documents)) {
            foreach ($documents as $doc) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($doc->document_name) . '</td>';
                $html .= '<td><span class="badge badge-info">' . ucfirst(str_replace('_', ' ', $doc->document_type)) . '</span></td>';
                
                if (user_type() == 'agency') {
                    $html .= '<td>' . htmlspecialchars($doc->uploader_name ?? 'Recruiter') . '</td>';
                }
                
                $html .= '<td>' . date('M j, Y', strtotime($doc->created_at)) . '</td>';
                $html .= '<td>' . $this->format_file_size($doc->file_size) . '</td>';
                
                if (user_type() == 'agency') {
                    $html .= '<td>';
                    $html .= '<a href="' . base_url($doc->file_path) . '" target="_blank" class="btn btn-sm btn-primary" title="Download"><i class="fa fa-download"></i></a>';
                    $html .= '</td>';
                } else {
                    $html .= '<td>';
                    $html .= '<a href="' . base_url($doc->file_path) . '" target="_blank" class="btn btn-sm btn-primary" title="Download"><i class="fa fa-download"></i></a>';
                    $html .= '<button onclick="deleteDocument(' . $doc->id . ')" class="btn btn-sm btn-danger ml-1" title="Delete"><i class="fa fa-trash"></i></button>';
                    $html .= '</td>';
                }
                
                $html .= '</tr>';
            }
        } else {
            $html = '<tr><td colspan="6" class="text-center text-muted">No documents uploaded yet.</td></tr>';
        }
        
        echo $html;
    }

    private function format_file_size($bytes) {
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

   
    public function update_onboarding_stage() 
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        // Force no cache
        $this->output->set_header('Cache-Control: no-cache, no-store, must-revalidate');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');
        $this->output->set_content_type('application/json');
        
        try {
            $candidate_uuid = $this->input->post('candidate_uuid');
            $stage = $this->input->post('stage');
            $value = $this->input->post('value');
            $job_uuid = $this->input->post('job_uuid'); // CRITICAL: Get from POST
            
            if (empty($candidate_uuid) || empty($stage) || !isset($value)) {
                throw new Exception('Missing required fields');
            }
            
            // Get candidate
            $candidate = $this->db->where('uuid', $candidate_uuid)
                                ->where('removed', 0)
                                ->get('candidates')
                                ->row();
            
            if (!$candidate) {
                throw new Exception('Candidate not found');
            }
            
            $candidate_id = $candidate->id;
            $agency_id = $this->get_user_agency_id();
            
            if (!$agency_id) {
                throw new Exception('Agency not found');
            }
            
            // ============================================
            // CRITICAL: Get SPECIFIC job from job_uuid
            // ============================================
            if (empty($job_uuid)) {
                throw new Exception('Job UUID is required. Please specify which job to update.');
            }
            
            $job = $this->db->where('uuid', $job_uuid)
                        ->where('agency_id', $agency_id)
                        ->where('removed', 0)
                        ->get('mod_jobs')
                        ->row();
            
            if (!$job) {
                throw new Exception('Job not found or you do not have access to it');
            }
            
            $job_id = $job->id;
            
            // Verify this candidate is assigned to THIS specific job
            $assignment = $this->db->where('candidate_id', $candidate_id)
                                ->where('job_id', $job_id)
                                ->where('removed', 0)
                                ->get('candidate_job_assignments')
                                ->row();
            
            if (!$assignment) {
                throw new Exception('This candidate is not assigned to this job');
            }
            
            // ============================================
            // Update ONLY this specific job's onboarding
            // ============================================
            $timestamp = date('Y-m-d H:i:s');
            $timestamp_field = $stage . '_at';
            
            // Check if onboarding progress record exists
            $this->db->where('candidate_id', $candidate_id);
            $this->db->where('job_id', $job_id);
            $this->db->where('agency_id', $agency_id);
            $onboarding_record = $this->db->get('candidate_onboarding_progress')->row();
            
            if ($onboarding_record) {
                // Update existing record
                $update_data = [
                    $stage => $value,
                    'updated_at' => $timestamp
                ];
                
                // Add timestamp if column exists
                $table_fields = $this->db->list_fields('candidate_onboarding_progress');
                if (in_array($timestamp_field, $table_fields)) {
                    $update_data[$timestamp_field] = ($value == 1) ? $timestamp : null;
                }
                
                $this->db->where('id', $onboarding_record->id);
                $this->db->update('candidate_onboarding_progress', $update_data);
                $record_id = $onboarding_record->id;
            } else {
                // Create new record for this job
                $insert_data = [
                    'candidate_id' => $candidate_id,
                    'job_id' => $job_id,
                    'agency_id' => $agency_id,
                    $stage => $value,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
                
                // Add timestamp if column exists
                $table_fields = $this->db->list_fields('candidate_onboarding_progress');
                if (in_array($timestamp_field, $table_fields)) {
                    $insert_data[$timestamp_field] = ($value == 1) ? $timestamp : null;
                }
                
                $this->db->insert('candidate_onboarding_progress', $insert_data);
                $record_id = $this->db->insert_id();
            }
            
            // ============================================
            // IMPORTANT: DO NOT update candidates table!
            // Onboarding data should ONLY be in candidate_onboarding_progress
            // ============================================
            
            // Recalculate progress for THIS JOB ONLY
            if ($record_id) {
                $this->recalculate_job_progress($record_id);
            }
            
            $response = [
                'success' => true,
                'message' => 'Stage updated successfully for job: ' . $job->name,
                'csrf_token' => $this->security->get_csrf_hash(),
                'debug' => [
                    'candidate_id' => $candidate_id,
                    'job_id' => $job_id,
                    'job_uuid' => $job_uuid,
                    'job_name' => $job->name,
                    'stage' => $stage,
                    'value' => $value,
                    'record_id' => $record_id
                ]
            ];
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'csrf_token' => $this->security->get_csrf_hash()
            ];
            echo json_encode($response);
        }
        exit();
    }

    private function recalculate_job_progress($progress_id)
    {
        $this->db->where('id', $progress_id);
        $progress = $this->db->get('candidate_onboarding_progress')->row();
        
        if (!$progress) {
            return false;
        }
        
        // Define all stages
        $stages = [
            'stage_under_review',
            'stage_submitted_to_hm',
            'stage_hm_decision',
            'stage_documents_decision',
            'stage_requested_docs',
            'stage_position_offered'
        ];
        
        // Calculate completed stages for THIS JOB
        $completed_stages = 0;
        foreach ($stages as $stage) {
            if (!empty($progress->$stage) && $progress->$stage == 1) {
                $completed_stages++;
            }
        }
        
        // Handle documents decision logic
        if ($progress->stage_documents_decision == 1 && $progress->documents_required == 0) {
            // If no documents required, skip requested_docs stage
            if ($progress->stage_position_offered == 1) {
                $completed_stages = count($stages);
            } elseif ($progress->stage_requested_docs == 0) {
                $completed_stages++; // Count requested_docs as completed
            }
        }
        
        // Calculate percentage
        $progress_percentage = ($completed_stages / count($stages)) * 100;
        
        // Determine current stage
        $current_stage = 'not_started';
        if ($completed_stages == count($stages)) {
            $current_stage = 'completed';
        } else {
            // Find first incomplete stage for THIS JOB
            foreach ($stages as $stage) {
                if ($stage === 'stage_requested_docs' && 
                    $progress->stage_documents_decision == 1 && 
                    $progress->documents_required == 0) {
                    continue; // Skip if no docs required
                }
                
                if (empty($progress->$stage) || $progress->$stage == 0) {
                    $current_stage = $stage;
                    break;
                }
            }
        }
        
        // Update progress for THIS JOB ONLY
        $this->db->where('id', $progress_id);
        $this->db->update('candidate_onboarding_progress', [
            'onboarding_stage' => $current_stage,
            'onboarding_progress' => $progress_percentage,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    }

    private function get_current_job_id($candidate_id, $agency_id)
    {
        $job_uuid = $this->input->get('job') ?? $this->session->userdata('current_job_uuid');
        
        if ($job_uuid) {
            $job = $this->db->where('uuid', $job_uuid)
                           ->where('agency_id', $agency_id)
                           ->where('removed', 0)
                           ->get('mod_jobs')
                           ->row();
            if ($job) {
                return $job->id;
            }
        }
        
        $assignment = $this->db->select('cja.job_id')
                          ->from('candidate_job_assignments cja')
                          ->join('mod_jobs j', 'j.id = cja.job_id')
                          ->where('cja.candidate_id', $candidate_id)
                          ->where('cja.removed', 0)
                          ->where('j.agency_id', $agency_id)
                          ->where('j.removed', 0)
                          ->limit(1)
                          ->get()
                          ->row();
        
        return $assignment ? $assignment->job_id : null;
    }

    public function update_hm_decision() 
    {
        
        $candidate_uuid = $this->input->post('candidate_uuid');
        if (!$candidate_uuid) {
            $candidate_uuid = $this->input->get('candidate_uuid');
        }
        
        if ($candidate_uuid) {
            $candidate = $this->db->where('uuid', $candidate_uuid)
                                ->where('removed', 0)
                                ->get('candidates')
                                ->row();
            if ($candidate && !$this->enforce_candidate_access($candidate->id)) {
                // Return JSON error for AJAX
                echo json_encode([
                    'success' => false, 
                    'message' => 'Access denied',
                    'csrf_token' => $this->security->get_csrf_hash()
                ]);
                exit();
            }
        }
        // 🔴 END ACCESS CHECK
        // Force no cache
        $this->output->set_header('Cache-Control: no-cache, no-store, must-revalidate');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');
        $this->output->set_content_type('application/json');
        
        try {
            $candidate_uuid = $this->input->post('candidate_uuid');
            $decision = $this->input->post('decision');
            $notes = $this->input->post('notes');
            $job_uuid = $this->input->post('job_uuid');
            
            if (empty($candidate_uuid) || empty($decision)) {
                throw new Exception('Missing required fields');
            }
            
            if (empty($job_uuid)) {
                throw new Exception('Job UUID is required');
            }
            
            // Get candidate
            $candidate = $this->db->where('uuid', $candidate_uuid)
                                ->where('removed', 0)
                                ->get('candidates')
                                ->row();
            
            if (!$candidate) {
                throw new Exception('Candidate not found');
            }
            
            $candidate_id = $candidate->id;
            $agency_id = $this->get_user_agency_id();
            $timestamp = date('Y-m-d H:i:s');
            
            if (!$agency_id) {
                throw new Exception('Agency not found');
            }
            
            // Get SPECIFIC job
            $job = $this->db->where('uuid', $job_uuid)
                        ->where('agency_id', $agency_id)
                        ->where('removed', 0)
                        ->get('mod_jobs')
                        ->row();
            
            if (!$job) {
                throw new Exception('Job not found');
            }
            
            $job_id = $job->id;
            
            // ============================================
            // 1. Save HM decision to onboarding progress
            // ============================================
            $this->db->where('candidate_id', $candidate_id);
            $this->db->where('job_id', $job_id);
            $this->db->where('agency_id', $agency_id);
            $onboarding_record = $this->db->get('candidate_onboarding_progress')->row();
            
            if ($onboarding_record) {
                // Update existing record
                $update_data = [
                    'hm_decision' => $decision,
                    'hm_decision_notes' => $notes ?: null,
                    'hm_decision_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
                
                $this->db->where('id', $onboarding_record->id);
                $this->db->update('candidate_onboarding_progress', $update_data);
            } else {
                // Create new record
                $insert_data = [
                    'candidate_id' => $candidate_id,
                    'job_id' => $job_id,
                    'agency_id' => $agency_id,
                    'hm_decision' => $decision,
                    'hm_decision_notes' => $notes ?: null,
                    'hm_decision_at' => $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
                
                $this->db->insert('candidate_onboarding_progress', $insert_data);
            }
            
            // ============================================
                // 2. SEND TEST NOTIFICATION - SIMPLE VERSION
                // ============================================

                $notification_sent = $this->send_test_notification($candidate_id, $job_id, $decision, $notes);

                if ($notification_sent) {
                } else {
                }
            // ============================================
            // 3. Return response with debug info
            // ============================================
            $response = [
                'success' => true,
                'message' => 'Hiring Manager decision saved for job: ' . $job->name,
                'hm_decision' => $decision,
                'csrf_token' => $this->security->get_csrf_hash(),
                'debug' => [
                    'candidate_id' => $candidate_id,
                    'job_id' => $job_id,
                    'job_uuid' => $job_uuid,
                    'job_name' => $job->name,
                    'notification_sent' => $notification_sent,
                    'notification_function_called' => true
                ]
            ];
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'csrf_token' => $this->security->get_csrf_hash()
            ];
            echo json_encode($response);
        }
        exit();
    }

    /**
     * Send Position Offered Notification - CORRECT FOR YOUR TABLE STRUCTURE
     */
    private function send_position_offered_notification($candidate_id, $job_id)
    {
        try {
            // Get candidate info
            $candidate = $this->db->where('id', $candidate_id)
                                ->where('removed', 0)
                                ->get('candidates')
                                ->row();
            
            // Get job info
            $job = $this->db->where('id', $job_id)
                        ->where('removed', 0)
                        ->get('mod_jobs')
                        ->row();
            
            if (!$candidate || !$job) {
                return false;
            }
            
            // Get current agency info (sender)
            $current_agency_id = $this->get_user_agency_id();
            $current_agency_name = 'Hiring Manager';
            
            if ($current_agency_id) {
                $agency = $this->db->select('name')
                                ->from('agencies')
                                ->where('id', $current_agency_id)
                                ->get()
                                ->row();
                if ($agency) {
                    $current_agency_name = $agency->name;
                }
            }
            
            // Get recruiter who submitted this candidate (receiver)
            $recruiter = $this->db->select('r.*')
                                ->from('recruiters r')
                                ->join('candidates c', 'c.recruiter_id = r.id OR c.created_by = r.id', 'left')
                                ->where('c.id', $candidate_id)
                                ->limit(1)
                                ->get()
                                ->row();
            
            if (!$recruiter) {
                return false;
            }
            
            // Prepare notification data for YOUR table structure
            $notification_data = [
                'title' => "Position Offered: {$candidate->first_name} {$candidate->last_name}",
                'message' => "Position has been offered to {$candidate->first_name} {$candidate->last_name} for: {$job->name}",
                'type' => 'status_changed',
                'sender_type' => 'agency',
                'sender_id' => $current_agency_id,
                'receiver_type' => 'recruiter',
                'receiver_id' => $recruiter->id,
                'related_entity' => 'candidate',
                'related_entity_id' => $candidate_id,
                'metadata' => json_encode([
                    'candidate_id' => $candidate_id,
                    'job_id' => $job_id,
                    'status' => 'position_offered',
                    'hm_agency_id' => $current_agency_id,
                    'hm_agency_name' => $current_agency_name,
                    'job_name' => $job->name,
                    'job_ref' => $job->reference_number,
                    'candidate_name' => "{$candidate->first_name} {$candidate->last_name}",
                    'candidate_ref' => $candidate->reference_number
                ]),
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'removed' => 0,
                'enabled' => 1
            ];
            
            // Insert notification
            $result = $this->db->insert('notifications', $notification_data);
            
            if ($result) {
                $notification_id = $this->db->insert_id();
                return true;
            } else {
                return false;
            }
            
        } catch (Exception $e) {
            return false;
        }
    }


    // Add this method to your Candidates controller
    public function check_notifications()
    {
        $candidate_id = $this->input->get('candidate_id');
        $job_id = $this->input->get('job_id');
        
        header('Content-Type: application/json');
        
        if (!$candidate_id || !$job_id) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            return;
        }
        
        // Get notifications for this candidate AND job (job info is in metadata)
        $this->db->select('n.*');
        $this->db->from('notifications n');
        $this->db->where('n.related_entity', 'candidate');
        $this->db->where('n.related_entity_id', $candidate_id);
        $this->db->where("(n.metadata LIKE '%\"job_id\":{$job_id}%' OR n.metadata LIKE '%\"job_id\":\"{$job_id}\"%')");
        $this->db->where('n.removed', 0);
        $this->db->order_by('n.created_at', 'DESC');
        $this->db->limit(10);
        
        $notifications = $this->db->get()->result();
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
    }

    public function update_documents_decision() 
    {
        header('Content-Type: application/json; charset=UTF-8');
        
        // Strong cache control
        header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }
        
        try {
            $candidate_uuid = $this->input->post('candidate_uuid');
            $documents_required = $this->input->post('documents_required');
            $documents_notes = $this->input->post('documents_notes');
            $job_uuid = $this->input->post('job_uuid'); // CRITICAL: Get job UUID from POST

            if (empty($candidate_uuid)) {
                throw new Exception('Candidate UUID is required');
            }
            
            if (empty($job_uuid)) {
                throw new Exception('Job UUID is required. Please specify which job this decision is for.');
            }
            
            // Get candidate
            $candidate = $this->Model_candidates->get_candidate_by_uuid($candidate_uuid);
            if (!$candidate) {
                throw new Exception('Candidate not found');
            }
            
            $candidate_id = $candidate->id;
            $agency_id = $this->get_user_agency_id();
            $timestamp = date('Y-m-d H:i:s');
            
            if (!$agency_id) {
                throw new Exception('Agency not found');
            }

            // Get SPECIFIC job from job_uuid
            $job = $this->db->where('uuid', $job_uuid)
                        ->where('agency_id', $agency_id)
                        ->where('removed', 0)
                        ->get('mod_jobs')
                        ->row();
            
            if (!$job) {
                throw new Exception('Job not found or you do not have access to it');
            }
            
            $job_id = $job->id;
            
            // Verify this candidate is assigned to THIS specific job
            $assignment = $this->db->where('candidate_id', $candidate_id)
                                ->where('job_id', $job_id)
                                ->where('removed', 0)
                                ->get('candidate_job_assignments')
                                ->row();
            
            if (!$assignment) {
                throw new Exception('This candidate is not assigned to this job');
            }
            
            if ($documents_required === '') {
                throw new Exception('Please specify if documents are required');
            }

            $documents_required_bool = ($documents_required == '1');

            // ============================================
            // CRITICAL: Update ONLY this specific job's documents decision
            // ============================================
            
            // Check if onboarding progress record exists for THIS JOB
            $this->db->where('candidate_id', $candidate_id);
            $this->db->where('job_id', $job_id);
            $this->db->where('agency_id', $agency_id);
            $onboarding_record = $this->db->get('candidate_onboarding_progress')->row();
            
            if ($onboarding_record) {
                // Update existing record for THIS JOB
                $update_data = [
                    'stage_documents_decision' => 1,
                    'documents_required' => $documents_required_bool,
                    'documents_notes' => $documents_notes ?: null,
                    'stage_documents_decision_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
                
                $this->db->where('id', $onboarding_record->id);
                $update_result = $this->db->update('candidate_onboarding_progress', $update_data);
                
                if (!$update_result) {
                    throw new Exception('Failed to update documents decision for this job');
                }
                
                $record_id = $onboarding_record->id;
                
            } else {
                // Create new record for THIS JOB
                $insert_data = [
                    'candidate_id' => $candidate_id,
                    'job_id' => $job_id,
                    'agency_id' => $agency_id,
                    'stage_documents_decision' => 1,
                    'documents_required' => $documents_required_bool,
                    'documents_notes' => $documents_notes ?: null,
                    'stage_documents_decision_at' => $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
                
                $insert_result = $this->db->insert('candidate_onboarding_progress', $insert_data);
                $record_id = $this->db->insert_id();
                
                if (!$insert_result) {
                    throw new Exception('Failed to create documents decision record for this job');
                }
                
            }
            
            // ============================================
            // IMPORTANT: DO NOT update candidates table!
            // Keep it job-specific in candidate_onboarding_progress
            // ============================================
            
            // Recalculate progress for THIS JOB ONLY
            if ($record_id) {
                $this->recalculate_job_progress($record_id);
            }

            // Send notification if documents are required
            
            if ($documents_required_bool) {
                $notification_sent = $this->send_documents_request_notification($candidate_id, $job_id, $documents_notes);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Documents decision updated successfully for job: ' . $job->name,
                'documents_required' => $documents_required_bool,
                'csrf_token' => $this->security->get_csrf_hash(),
                'debug' => [
                    'candidate_id' => $candidate_id,
                    'job_id' => $job_id,
                    'job_uuid' => $job_uuid,
                    'job_name' => $job->name,
                    'documents_required' => $documents_required_bool,
                    'record_id' => $record_id
                ]
            ]);
            exit();

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            exit();
        }
    }

    private function update_onboarding_progress($candidate_id)
    {
        $candidate = $this->Model_candidates->get_by_id($candidate_id);
        
        if (!$candidate) {
            return false;
        }

        $stages = [
            'stage_under_review',
            'stage_submitted_to_hm', 
            'stage_hm_decision',
            'stage_documents_decision',
            'stage_requested_docs',
            'stage_position_offered'
        ];

        $completed_stages = 0;
        
        foreach ($stages as $stage) {
            if (isset($candidate->$stage) && $candidate->$stage == 1) {
                $completed_stages++;
            }
        }

        if (isset($candidate->stage_documents_decision) && 
            $candidate->stage_documents_decision == 1 && 
            isset($candidate->documents_required) && 
            $candidate->documents_required == 0) {
            
            if (!isset($candidate->stage_requested_docs) || $candidate->stage_requested_docs == 0) {
                $completed_stages++;
            }
            
            if (isset($candidate->stage_position_offered) && $candidate->stage_position_offered == 1) {
                $completed_stages = count($stages);
            }
        }

        $current_stage = 'not_started';
        
        if ($completed_stages == count($stages)) {
            $current_stage = 'completed';
        } elseif ($completed_stages > 0) {
            foreach ($stages as $stage) {
                if ($stage === 'stage_requested_docs' && 
                    isset($candidate->stage_documents_decision) && 
                    $candidate->stage_documents_decision == 1 && 
                    isset($candidate->documents_required) && 
                    $candidate->documents_required == 0) {
                    continue;
                }
                
                if (!isset($candidate->$stage) || $candidate->$stage == 0) {
                    $current_stage = $stage;
                    break;
                }
            }
        }

        if ($completed_stages == count($stages)) {
            $this->db->where('id', $candidate_id)->update('candidates', [
                'onboarding_completed_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->db->where('id', $candidate_id)->update('candidates', [
                'onboarding_completed_at' => null
            ]);
        }

        $progress_percentage = ($completed_stages / count($stages)) * 100;

        $update_data = [
            'onboarding_stage' => $current_stage,
            'onboarding_progress' => $progress_percentage,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->db->where('id', $candidate_id)->update('candidates', $update_data);
        return $result;
    }

    public function update($id) {
        if (!is_ajax()) {
            $csrf_name = $this->security->get_csrf_token_name();
            $csrf_token = $this->input->post($csrf_name);
            
            if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
                flash_notification('Invalid CSRF token. Please try again.', 'error');
                redir($this->pageName);
                return;
            }
        }
        
        if ($this->input->post()) {
            $allowed_fields = ['status', 'notes', 'rating'];
            $filtered_data = [];
            
            foreach ($allowed_fields as $field) {
                if ($this->input->post($field) !== null) {
                    $filtered_data[$field] = $this->input->post($field);
                }
            }
            
            $filtered_data['id'] = $id;
            $filtered_data['updated_at'] = date('Y-m-d H:i:s');
            
            if (isset($filtered_data['status'])) {
                $current_candidate = $this->{$this->model}->get_candidate($id);
                if ($current_candidate && $current_candidate->status != $filtered_data['status']) {
                    $filtered_data['status_updated_at'] = date('Y-m-d H:i:s');
                }
            }
            
            $_POST = $filtered_data;
            
        }
        
        parent::update($id);
    }

    public function create() {
        ajax_return(array(
            'success' => false,
            'error' => 'Agencies cannot add candidates directly. Please contact recruiters to add new candidates.'
        ));
    }

    public function onboarding_stats() {
        $agency_id = $this->get_user_agency_id();
        $stats = $this->{$this->model}->get_onboarding_stats($agency_id);

        ajax_return([
            'success' => true,
            'stats' => $stats
        ]);
    }

    public function index(): void
    {
        // TEMPORARY DEBUG - FIXED VERSION
        $agency_id = $this->get_user_agency_id();
        
        // Only run debug if we have an agency
        if ($agency_id) {
            // TEST 1: Simple query to see if candidates exist
            $this->db->select('COUNT(*) as total');
            $this->db->from('candidates c');
            $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
            $this->db->where('ca.agency_id', $agency_id);
            $this->db->where('c.removed', 0);
            $count_result = $this->db->get()->row();
            echo "<!-- DEBUG: Total candidates for agency {$agency_id}: " . ($count_result->total ?? 0) . " -->";
            
            // TEST 2: Check if candidates have job assignments
            $this->db->select('c.id, c.first_name, c.last_name, 
                            COUNT(cja.id) as assignment_count,
                            GROUP_CONCAT(j.name) as job_names');
            $this->db->from('candidates c');
            $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
            $this->db->join('candidate_job_assignments cja', 'cja.candidate_id = c.id AND cja.removed = 0', 'left');
            $this->db->join('mod_jobs j', 'j.id = cja.job_id', 'left');
            $this->db->where('ca.agency_id', $agency_id);
            $this->db->where('c.removed', 0);
            $this->db->group_by('c.id');
            $this->db->limit(5);
            
            $job_results = $this->db->get()->result();
            
            foreach ($job_results as $row) {
                echo "<!-- DEBUG CANDIDATE: {$row->first_name} {$row->last_name} - ";
                echo "Assignments: {$row->assignment_count} - ";
                echo "Jobs: " . ($row->job_names ?: 'NONE') . " -->";
            }
            
            // TEST 3: What does your main_selects() actually return?
            echo "<!-- TESTING main_selects() output -->";
            $this->db->select('c.id, c.first_name, c.last_name');
            
            // Add the EXACT same job_name subquery from your main_selects()
            $job_name_subquery = "(SELECT j2.name 
                                FROM candidate_job_assignments cja2 
                                JOIN mod_jobs j2 ON j2.id = cja2.job_id 
                                WHERE cja2.candidate_id = c.id 
                                AND cja2.removed = 0 
                                AND j2.agency_id = " . $this->db->escape($agency_id) . " 
                                LIMIT 1) as job_name";
            
            $this->db->select($job_name_subquery, false);
            $this->db->from('candidates c');
            $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
            $this->db->where('ca.agency_id', $agency_id);
            $this->db->where('c.removed', 0);
            $this->db->limit(3);
            
            $test_results = $this->db->get()->result();
            
            foreach ($test_results as $row) {
                echo "<!-- QUERY RESULT: {$row->first_name} - Job Name: \"" . ($row->job_name ?: 'NULL') . "\" -->";
            }
        }
        
        // Rest of your existing code (keep this exactly as it was)
        $this->debug_agency_filtering();
        $this->breadcrumbs = array(
            array(
                'title' => lang($this->pageName . '_heading'),
                'url'   => redir($this->pageName, true)
            ),
        );
        $this->view = 'listing';
        $this->load->view('agency/candidates/view_list_extra');

        $this->load->view($this->folder . '/' . 'view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading'           => lang($this->pageName . '_heading'),
            'noRows'            => lang($this->pageName . '_no_rows'),
        ));
        $this->load->view($this->folder . '/' . 'view_footer');
    }

    public function quick_manage_extra($id, $row) {
        $candidateId = is_bool($id) || !is_object($row) ? 0 : (int)$row->id;
        $agency_id = $this->get_user_agency_id();

        $agencies_all = [];
        $jobs_all = [];
        $agents_all = [];
        $candidate_data = null;

        try {
            $agency_result = $this->{$this->model}->get_agency_by_id($agency_id);
            $agencies_all = $agency_result;
        } catch (Exception $e) {
            $agencies_all = null;
        }

        $jobs_all = ['' => 'Select Job'];
        try {
            $jobs_result = $this->{$this->model}->get_jobs_by_agency($agency_id);
            if ($jobs_result && is_object($jobs_result) && method_exists($jobs_result, 'num_rows') && $jobs_result->num_rows() > 0) {
                foreach ($jobs_result->result() as $job) {
                    $jobs_all[$job->id] = $job->name . ' (' . ($job->reference_number ?? 'No Ref') . ')';
                }
            } else {
                $jobs_all = ['' => 'No jobs available'];
            }
        } catch (Exception $e) {
            $jobs_all = ['' => 'Error loading jobs'];
        }

        $agents_all = ['' => 'Select Agent'];
        try {
            $agents_result = $this->{$this->model}->get_agency_agents_by_agency($agency_id);
            if ($agents_result && is_object($agents_result) && method_exists($agents_result, 'num_rows') && $agents_result->num_rows() > 0) {
                foreach ($agents_result->result() as $agent) {
                    $agents_all[$agent->id] = $agent->first_name . ' ' . $agent->last_name . ' (' . $agent->email . ')';
                }
            } else {
                $agents_all = ['' => 'No agents available'];
            }
        } catch (Exception $e) {
            $agents_all = ['' => 'Error loading agents'];
        }

        if ($candidateId > 0) {
            try {
                if (method_exists($this->{$this->model}, 'get_candidate_details')) {
                    $candidate_data = $this->{$this->model}->get_candidate_details($candidateId);
                } else {
                    $candidate_data = null;
                }
            } catch (Exception $e) {
                $candidate_data = null;
            }
        }

        return array(
            'agencies_all'      => $agencies_all,
            'jobs_all'          => $jobs_all,
            'agents_all'        => $agents_all,
            'candidate_data'    => $candidate_data,
            'user_agency_id'    => $agency_id,
        );
    }

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
            'stage_under_review' => 0,
            'stage_submitted_to_hm' => 0,
            'stage_requested_docs' => 0,
            'stage_position_offered' => 0,
            'onboarding_stage' => 'not_started',
            'onboarding_progress' => 0,
        );
        
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
        
        if ($this->input->post('agency_id')) {
            $params['agency_id'] = (int)$this->input->post('agency_id');
        }
        
        if ($this->input->post('assigned_agent_id')) {
            $params['assigned_agent_id'] = (int)$this->input->post('assigned_agent_id');
        }
        
        if ($this->input->post('job_id')) {
            $params['job_id'] = (int)$this->input->post('job_id');
        }
        
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


    private function send_hm_decision_notification($candidate_id, $job_id, $decision, $notes = '')
    {
        try {
            // Get candidate info
            $candidate = $this->db->where('id', $candidate_id)
                                ->where('removed', 0)
                                ->get('candidates')
                                ->row();
            
            if (!$candidate) {
                return false;
            }
            
            // Get job info
            $job = $this->db->where('id', $job_id)
                        ->where('removed', 0)
                        ->get('mod_jobs')
                        ->row();
            
            if (!$job) {
                return false;
            }
            
            // Get current agency info (sender)
            $current_agency_id = $this->get_user_agency_id();
            $current_agency_name = 'Hiring Manager';
            
            if ($current_agency_id) {
                $agency = $this->db->select('name')
                                ->from('agencies')
                                ->where('id', $current_agency_id)
                                ->get()
                                ->row();
                if ($agency) {
                    $current_agency_name = $agency->name;
                }
            }
            
            // ============ CRITICAL FIX: Get recruiter from candidate.recruiter_id ============
            if (empty($candidate->recruiter_id)) {
                return false;
            }
            
            $recruiter_id = $candidate->recruiter_id;
            
            // Get recruiter details
            $recruiter = $this->db->select('id, first_name, last_name, agency_id')
                                ->from('recruiters')
                                ->where('id', $recruiter_id)
                                ->get()
                                ->row();
            
            if (!$recruiter) {
                return false;
            }
            // ============ END CRITICAL FIX ============
            
            // Prepare notification data
            $notification_data = [
                'title' => $decision === 'accepted' 
                    ? "Candidate Accepted: {$candidate->first_name} {$candidate->last_name}"
                    : "Candidate Rejected: {$candidate->first_name} {$candidate->last_name}",
                
                'message' => $decision === 'accepted'
                    ? "Your candidate {$candidate->first_name} {$candidate->last_name} has been ACCEPTED for the position: {$job->name}"
                    : "Your candidate {$candidate->first_name} {$candidate->last_name} has been REJECTED for the position: {$job->name}",
                
                'type' => 'hm_decision',
                'sender_type' => 'agency',
                'sender_id' => $current_agency_id,
                'receiver_type' => 'recruiter',
                'receiver_id' => $recruiter_id, // CRITICAL: Use candidate.recruiter_id
                'related_entity' => 'candidate',
                'related_entity_id' => $candidate_id,
                
                'metadata' => json_encode([
                    'candidate_id' => $candidate_id,
                    'job_id' => $job_id,
                    'decision' => $decision,
                    'notes' => $notes,
                    'hm_agency_id' => $current_agency_id,
                    'hm_agency_name' => $current_agency_name,
                    'job_name' => $job->name,
                    'job_ref' => $job->reference_number,
                    'candidate_name' => "{$candidate->first_name} {$candidate->last_name}",
                    'candidate_ref' => $candidate->reference_number
                ]),
                
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'removed' => 0,
                'enabled' => 1
            ];
            
            // Insert notification
            $result = $this->db->insert('notifications', $notification_data);
            
            if ($result) {
                $notification_id = $this->db->insert_id();
                return true;
            } else {
                return false;
            }
            
        } catch (Exception $e) {
            return false;
        }
    }

    private function send_documents_request_notification($candidate_id, $job_id, $documents_notes)
    {
        try {
            // Get candidate info
            $candidate = $this->db->where('id', $candidate_id)
                                ->where('removed', 0)
                                ->get('candidates')
                                ->row();
            
            if (!$candidate) {
                return false;
            }
            
            // Get job info
            $job = $this->db->where('id', $job_id)
                        ->where('removed', 0)
                        ->get('mod_jobs')
                        ->row();
            
            if (!$job) {
                return false;
            }
            
            // Get current agency info (sender)
            $current_agency_id = $this->get_user_agency_id();
            $current_agency_name = 'Hiring Manager';
            
            if ($current_agency_id) {
                $agency = $this->db->select('name')
                                ->from('agencies')
                                ->where('id', $current_agency_id)
                                ->get()
                                ->row();
                if ($agency) {
                    $current_agency_name = $agency->name;
                }
            }
            
            // ============ CRITICAL FIX: Get recruiter from candidate.recruiter_id ============
            if (empty($candidate->recruiter_id)) {
                return false;
            }
            
            $recruiter_id = $candidate->recruiter_id;
            
            // Get recruiter details
            $recruiter = $this->db->select('id, first_name, last_name, agency_id')
                                ->from('recruiters')
                                ->where('id', $recruiter_id)
                                ->get()
                                ->row();
            
            if (!$recruiter) {
                return false;
            }
            // ============ END CRITICAL FIX ============
            
            // Prepare notification data
            $notification_data = [
                'title' => "Documents Required: {$candidate->first_name} {$candidate->last_name}",
                'message' => "Additional documents are required for {$candidate->first_name} {$candidate->last_name} for the position: {$job->name}",
                'type' => 'documents_request',
                'sender_type' => 'agency',
                'sender_id' => $current_agency_id,
                'receiver_type' => 'recruiter',
                'receiver_id' => $recruiter_id, // CRITICAL: Use candidate.recruiter_id
                'related_entity' => 'candidate',
                'related_entity_id' => $candidate_id,
                'metadata' => json_encode([
                    'candidate_id' => $candidate_id,
                    'job_id' => $job_id,
                    'documents_notes' => $documents_notes,
                    'hm_agency_id' => $current_agency_id,
                    'hm_agency_name' => $current_agency_name,
                    'job_name' => $job->name,
                    'job_ref' => $job->reference_number,
                    'candidate_name' => "{$candidate->first_name} {$candidate->last_name}",
                    'candidate_ref' => $candidate->reference_number
                ]),
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'removed' => 0,
                'enabled' => 1
            ];
            
            // Insert notification
            $result = $this->db->insert('notifications', $notification_data);
            
            if ($result) {
                $notification_id = $this->db->insert_id();
                return true;
            } else {
                return false;
            }
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get submitting recruiter for a SPECIFIC job assignment
     */
    private function get_submitting_recruiter_for_job($candidate_id, $job_id)
    {
        // Try to get the recruiter from the job assignment
        $assignment = $this->db->select('cja.created_by, r.id as recruiter_id, r.agency_id')
                            ->from('candidate_job_assignments cja')
                            ->join('recruiters r', 'r.id = cja.created_by', 'left')
                            ->where('cja.candidate_id', $candidate_id)
                            ->where('cja.job_id', $job_id)
                            ->where('cja.removed', 0)
                            ->limit(1)
                            ->get()
                            ->row();
        
        if ($assignment && $assignment->recruiter_id) {
            return (object) [
                'id' => $assignment->recruiter_id,
                'agency_id' => $assignment->agency_id
            ];
        }
        
        // Fallback: Get any recruiter associated with this candidate
        $this->db->select('r.id, r.agency_id');
        $this->db->from('recruiters r');
        $this->db->join('candidates c', 'c.recruiter_id = r.id OR c.created_by = r.id', 'left');
        $this->db->where('c.id', $candidate_id);
        $this->db->where('r.id IS NOT NULL');
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }


    private function send_agent_notification($candidateId){
        try {
            $candidate = $this->{$this->model}->get_candidate($candidateId);
            
            if ($candidate && $candidate->assigned_agent_id) {
                $agent = $this->{$this->model}->get_agent($candidate->assigned_agent_id);
                
                if ($agent) {
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

    public function enable($uuid_or_id) {
        $candidate = $this->{$this->model}->get_candidate($uuid_or_id);
        
        if (empty($candidate)) {
            ajax_return(['success' => false, 'message' => 'Candidate not found']);
            return;
        }
        
        $candidate_id = $candidate->id;
        
        if (!$this->enforce_candidate_access($candidate_id)) {
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf_name = $this->security->get_csrf_token_name();
            $csrf_token = $this->input->post($csrf_name);
            
            if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
                if (is_ajax()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                    return;
                } else {
                    show_error('Invalid CSRF token', 400);
                    return;
                }
            }
        } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            show_error('Method not allowed', 405);
            return;
        }
        
        parent::enable($candidate_id);
    }

    public function disable($id) {
        if (!$this->enforce_candidate_access($id)) {
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf_name = $this->security->get_csrf_token_name();
            $csrf_token = $this->input->post($csrf_name);
            
            if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
                if (is_ajax()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                    return;
                } else {
                    show_error('Invalid CSRF token', 400);
                    return;
                }
            }
        } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            show_error('Method not allowed', 405);
            return;
        }
        $agency_id = $this->get_user_agency_id();
        if ($agency_id) {
            $exists = $this->db->select('1')
                ->from('candidate_agencies')
                ->where('candidate_id', $id)
                ->where('agency_id', $agency_id)
                ->get()
                ->row();
            
            if (!$exists) {
                ajax_return([
                    'success' => false,
                    'error' => 'Candidate not found or access denied'
                ]);
                return;
            }
        }
        parent::disable($id);
    }

    public function remove($id) {
        if (!$this->enforce_candidate_access($id)) {
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf_name = $this->security->get_csrf_token_name();
            $csrf_token = $this->input->post($csrf_name);
            
            if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
                if (is_ajax()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                    return;
                } else {
                    show_error('Invalid CSRF token', 400);
                    return;
                }
            }
        } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            show_error('Method not allowed', 405);
            return;
        }
        $agency_id = $this->get_user_agency_id();
        if ($agency_id) {
            $exists = $this->db->select('1')
                ->from('candidate_agencies')
                ->where('candidate_id', $id)
                ->where('agency_id', $agency_id)
                ->get()
                ->row();
            
            if (!$exists) {
                ajax_return([
                    'success' => false,
                    'error' => 'Candidate not found or access denied'
                ]);
                return;
            }
        }
        
        $this->db->select('job_id');
        $this->db->from('candidate_jobs');
        $this->db->where('candidate_id', $id);
        $job_associations = $this->db->get()->result_array();
        
        $result = parent::remove($id);
        
        if ($result && !empty($job_associations)) {
            foreach ($job_associations as $job) {
                $this->db->set('candidate_count', 'candidate_count - 1', false);
                $this->db->where('id', $job['job_id']);
                $this->db->update('mod_jobs');
            }
        }
        
        return $result;
    }

    public function edit($uuid_or_id = null)
    {
        if (!$uuid_or_id) {
            show_error('Candidate identifier required', 400);
            return;
        }
        
        // Get agency ID
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            show_error('Access denied', 403);
            return;
        }
        
        // Get candidate
        $candidate = $this->{$this->model}->get_candidate($uuid_or_id);
        
        if (empty($candidate)) {
            show_404();
            return;
        }
        
        $candidate_id = $candidate->id;
        
        // Check agency access via candidate_agencies table
        $has_access = $this->db->select('1')
            ->from('candidate_agencies')
            ->where('candidate_id', $candidate_id)
            ->where('agency_id', $agency_id)
            ->get()
            ->row();
        
        if (!$has_access) {
            show_error('Access denied to this candidate', 403);
            return;
        }
        
        // Also call enforce_candidate_access for additional security
        if (!$this->enforce_candidate_access($candidate_id)) {
            return;
        }
        
        parent::edit($candidate_id);
    }

    public function view($uuid = null)
    {
        if (!$uuid) {
            show_error('Candidate identifier required', 400);
            return;
        }
        
        // Get current agency ID
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            show_error('Access denied', 403);
            return;
        }
        
        // Get candidate with agency access check in ONE query
        $this->db->select('c.*');
        $this->db->from('candidates c');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id AND ca.agency_id = ' . $this->db->escape($agency_id));
        $this->db->where('c.uuid', $uuid);
        $this->db->where('c.removed', 0);
        $this->db->limit(1);
        
        $candidate = $this->db->get()->row();
        
        if (!$candidate) {
            show_error('Candidate not found or access denied', 404);
            return;
        }
        
        $candidate_id = $candidate->id;
        $candidate_uuid = $candidate->uuid;
        
        // Get job UUID from URL
        $job_uuid = $this->input->get('job');
        
        // Get SPECIFIC job assignment based on job_uuid
        if ($job_uuid) {
            // Try to get the specific job assignment
            $this->db->select('cja.job_id, j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref');
            $this->db->from('candidate_job_assignments cja');
            $this->db->join('mod_jobs j', 'j.id = cja.job_id');
            $this->db->where('cja.candidate_id', $candidate_id);
            $this->db->where('cja.removed', 0);
            $this->db->where('j.uuid', $job_uuid);
            $this->db->where('j.agency_id', $agency_id);
            $this->db->where('j.removed', 0);
            $this->db->limit(1);
            
            $specific_job = $this->db->get()->row();
            
            if ($specific_job) {
                // Candidate IS assigned to this specific job
                $candidate->job_id = $specific_job->job_id;
                $candidate->job_name = $specific_job->job_name;
                $candidate->job_uuid = $specific_job->job_uuid;
                $candidate->job_ref = $specific_job->job_ref;
            } else {
                // Candidate is NOT assigned to this specific job
                // Get any job assignment for this agency instead
                $this->db->select('cja.job_id, j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref');
                $this->db->from('candidate_job_assignments cja');
                $this->db->join('mod_jobs j', 'j.id = cja.job_id');
                $this->db->where('cja.candidate_id', $candidate_id);
                $this->db->where('cja.removed', 0);
                $this->db->where('j.agency_id', $agency_id);
                $this->db->where('j.removed', 0);
                $this->db->order_by('cja.created_at', 'DESC');
                $this->db->limit(1);
                
                $any_job = $this->db->get()->row();
                
                if ($any_job) {
                    $candidate->job_id = $any_job->job_id;
                    $candidate->job_name = $any_job->job_name;
                    $candidate->job_uuid = $any_job->job_uuid;
                    $candidate->job_ref = $any_job->job_ref;
                } else {
                    // No job assignments at all for this agency
                    $candidate->job_id = null;
                    $candidate->job_name = null;
                    $candidate->job_uuid = null;
                    $candidate->job_ref = null;
                }
            }
        } else {
            // No job UUID provided, get the first job assignment
            $this->db->select('cja.job_id, j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref');
            $this->db->from('candidate_job_assignments cja');
            $this->db->join('mod_jobs j', 'j.id = cja.job_id');
            $this->db->where('cja.candidate_id', $candidate_id);
            $this->db->where('cja.removed', 0);
            $this->db->where('j.agency_id', $agency_id);
            $this->db->where('j.removed', 0);
            $this->db->order_by('cja.created_at', 'DESC');
            $this->db->limit(1);
            
            $any_job = $this->db->get()->row();
            
            if ($any_job) {
                $candidate->job_id = $any_job->job_id;
                $candidate->job_name = $any_job->job_name;
                $candidate->job_uuid = $any_job->job_uuid;
                $candidate->job_ref = $any_job->job_ref;
            }
        }
        
        // Set data for the view
        $data = [
            'candidate' => $candidate,
            'candidate_id' => $candidate->id,
            'job_uuid' => $job_uuid ?: ($candidate->job_uuid ?? null),
            'heading' => 'Candidate Details - ' . $candidate->first_name . ' ' . $candidate->last_name,
        ];
        
        // Load the candidates_list/view.php file
        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/candidates_list/view', $data);
        $this->load->view($this->folder . '/view_footer');
    }

    private function check_candidate_agency_access_direct($agency_id, $candidate_id)
    {
        $this->db->select('1');
        $this->db->from('candidate_agencies ca');
        $this->db->join('candidates c', 'c.id = ca.candidate_id');
        $this->db->where('ca.candidate_id', $candidate_id);
        $this->db->where('ca.agency_id', $agency_id);
        $this->db->where('c.removed', 0);
        $this->db->limit(1);
        
        $result = $this->db->get()->row();
        return $result !== null;
    }

    public function complete_onboarding() {
        $candidate_uuid = $this->input->post('candidate_uuid'); 
        $candidate = $this->{$this->model}->get_candidate_by_uuid($candidate_uuid);
        
        if (!$candidate) {
            ajax_return(['success' => false, 'message' => 'Candidate not found']);
            return;
        }
        
        $candidate_id = $candidate->id;
        
        $agency_id = $this->get_user_agency_id();
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id);
        }

        $result = $this->{$this->model}->complete_onboarding($candidate_id);

        if ($result) {
            $this->{$this->model}->log_candidate_activity([
                'candidate_id' => $candidate_id,
                'action' => 'onboarding_completed',
                'description' => 'Onboarding process completed successfully',
                'created_by' => loginID('agency'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            ajax_return([
                'success' => true,
                'message' => 'Onboarding completed successfully'
            ]);
        } else {
            ajax_return([
                'success' => false,
                'message' => 'Failed to complete onboarding'
            ]);
        }
    }

  
    private function get_submitting_agency_id($candidate_id) {
        // Get the recruiter who created/owns this candidate
        $this->db->select('recruiter_id, created_by')
                ->from('candidates')
                ->where('id', $candidate_id)
                ->where('removed', 0);
        
        $candidate = $this->db->get()->row();
        
        if (!$candidate) {
            return null;
        }
        
        // Get the recruiter's agency
        $recruiter_id = $candidate->recruiter_id ?? $candidate->created_by;
        
        if (!$recruiter_id) {
            return null;
        }
        
        $this->db->select('agency_id')
                ->from('recruiters')
                ->where('id', $recruiter_id);
        
        $recruiter = $this->db->get()->row();
        
        return $recruiter ? $recruiter->agency_id : null;
    }

    public function check_documents_submission($candidate_id) {
        if (!$this->enforce_candidate_access($candidate_id)) {
            return;
        }
        $result = $this->{$this->model}->check_and_update_documents_stage($candidate_id);
        
        if ($result) {
            ajax_return([
                'success' => true,
                'message' => 'Documents stage updated successfully',
                'stage_updated' => true
            ]);
        } else {
            ajax_return([
                'success' => false,
                'message' => 'No required documents submitted yet',
                'stage_updated' => false
            ]);
        }
    }

    public function get_required_documents($candidate_id) {
        $agency_id = $this->get_current_agency_id();
        
        if (!$agency_id) {
            return [];
        }
        
        $has_access = $this->check_agency_candidate_access($agency_id, $candidate_id);
        
        if (!$has_access) {
            return [];
        }
        $this->db->select('cd.*, 
                        CASE 
                            WHEN cd.uploaded_by_type = "recruiter" THEN CONCAT(r.first_name, " ", r.last_name)
                            WHEN cd.uploaded_by_type = "agency" THEN CONCAT(a.first_name, " ", a.last_name)
                            ELSE "System"
                        END as uploader_name');
        $this->db->from('candidate_documents cd');
        $this->db->join('recruiters r', 'r.id = cd.uploaded_by AND cd.uploaded_by_type = "recruiter"', 'left');
        $this->db->join('agency_staff a', 'a.id = cd.uploaded_by AND cd.uploaded_by_type = "agency"', 'left');
        $this->db->where('cd.candidate_id', $candidate_id);
        $this->db->where('cd.removed', 0);
        
        $this->db->order_by('cd.created_at', 'DESC');
        
        $result = $this->db->get()->result();        
        foreach ($result as $doc) {
        }
        
        return $result;
    }

    private function get_current_agency_id() {
        $ci = &get_instance();
        $login = $ci->session->userdata('login');
        
        if (!empty($login['agency'])) {
            $agency_user = $login['agency'];
            
            if (!empty($agency_user['agency_id'])) {
                return $agency_user['agency_id'];
            } elseif (!empty($agency_user['id'])) {
                return $agency_user['id'];
            } elseif (!empty($agency_user['agency']['id'])) {
                return $agency_user['agency']['id'];
            }
        }
        
        return null;
    }

    public function debug_agency_filtering()
    {
        $agency_id = $this->get_user_agency_id();

        $this->db->select('c.id, c.first_name, c.last_name, ca.agency_id');
        $this->db->from('candidates c');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'left');
        $this->db->where('c.removed', 0);
        
        if ($agency_id) {
            $this->db->where('ca.agency_id', $agency_id);
        }
        
        $all_candidates = $this->db->get()->result();
        foreach ($all_candidates as $candidate) {
        }
        
    }

    private function check_agency_candidate_access($agency_id, $candidate_id)
    {
        $this->db->select('1');
        $this->db->from('candidate_agencies');
        $this->db->where('candidate_id', $candidate_id);
        $this->db->where('agency_id', $agency_id);
        
        return $this->db->get()->row() !== null;
    }

    public function get_candidate_details($identifier, $agency_id = null)
    {
        if (!$agency_id) {
            $agency_id = $this->get_current_agency_id();
        }
        
        if (!$agency_id) {
            return null;
        }
        
        // Determine if identifier is UUID or ID
        if (is_string($identifier) && strlen($identifier) == 36 && strpos($identifier, '-') !== false) {
            $this->db->where('c.uuid', $identifier);
        } else {
            $this->db->where('c.id', $identifier);
        }
        
        $this->db->select('c.*, 
                        cja.job_id,
                        j.name as job_name, 
                        j.uuid as job_uuid,
                        j.reference_number as job_ref,
                        j.agency_id as job_agency_id');
        $this->db->from('candidates c');
        
        // 🔒 CRITICAL FIX: Join with candidate_agencies to verify access
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id AND ca.agency_id = ' . $this->db->escape($agency_id), 'inner');
        
        // 🔒 CRITICAL FIX: Get job assignments ONLY for current agency
        $this->db->join('candidate_job_assignments cja', 
                    'cja.candidate_id = c.id AND cja.removed = 0', 
                    'left');
        
        // 🔒 CRITICAL FIX: Only show jobs from this agency
        $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 'left');
        
        $this->db->where('c.removed', 0);
        
        $result = $this->db->get()->row();
        
        // If no job found for this agency, still return candidate but without job info
        if ($result && !$result->job_id) {
            // Clear job-related fields
            $result->job_name = null;
            $result->job_uuid = null;
            $result->job_ref = null;
            $result->job_agency_id = null;
        }
        
        return $result;
    }

    public function log_candidate_activity($activity_data)
    {
        return $this->db->insert('candidate_activities', $activity_data);
    }

    public function check_documents_submission_status($candidate_id)
    {
        $this->db->select('*');
        $this->db->from('candidate_documents');
        $this->db->where('candidate_id', $candidate_id);
        $this->db->where('removed', 0);
        $this->db->where('document_type', 'required_document');
        $this->db->order_by('created_at', 'DESC');
        
        $documents = $this->db->get()->result();
        
        return [
            'documents' => $documents,
            'has_documents' => !empty($documents)
        ];
    }

    public function ajax_get_candidate_chat_info($candidate_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            ajax_return([
                'success' => false,
                'message' => 'Not logged in'
            ]);
            return;
        }

        
        if (!$this->enforce_candidate_access($candidate_id)) {
            // enforce_candidate_access() already sends the AJAX response
            return;
        }
        
        $this->db->select('c.*, ca.agency_id as submitting_agency_id')
                 ->from('candidates c')
                 ->join('candidate_agencies ca', 'ca.candidate_id = c.id')
                 ->where('c.id', $candidate_id)
                 ->where('c.removed', 0);
    
        $candidate = $this->db->get()->row();
        
        if (!$candidate) {
            ajax_return([
                'success' => false,
                'message' => 'Candidate not found'
            ]);
            return;
        }
        
        $recruiter = $this->Model_chat_messages->get_candidate_recruiter($candidate_id);
        
        if (!$recruiter) {
            ajax_return([
                'success' => false,
                'message' => 'Recruiter not found for this candidate'
            ]);
            return;
        }
        
        $conversation = $this->Model_chat_messages->get_or_create_candidate_conversation(
            $agency_id,
            $recruiter->id,
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
        
        ajax_return([
            'success' => true,
            'conversation' => [
                'uuid' => $conversation->uuid,
                'title' => $conversation->title,
                'recruiter_name' => $recruiter->first_name . ' ' . $recruiter->last_name,
                'recruiter_id' => $recruiter->id,
                'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                'candidate_ref' => $candidate->reference_number
            ],
            'chat_url' => site_url('/agency/chat/conversation/' . $conversation->uuid)
        ]);
    }

    public function start_candidate_chat($uuid_or_id)
    {
        if (!$uuid_or_id) {
            show_error('Candidate identifier required', 400);
            return;
        }
        
        $candidate = $this->{$this->model}->get_candidate($uuid_or_id);
        
        if (empty($candidate)) {
            show_404();
            return;
        }
        
        $candidate_id = $candidate->id;
        $candidate_uuid = $candidate->uuid;
        
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            show_error('Access denied', 403);
            return;
        }
        
        // Check agency access BEFORE proceeding
        $has_access = $this->db->select('1')
            ->from('candidate_agencies')
            ->where('candidate_id', $candidate_id)
            ->where('agency_id', $agency_id)
            ->get()
            ->row();
        
        if (!$has_access) {
            show_error('Access denied to this candidate', 403);
            return;
        }
        
        if (!$this->enforce_candidate_access($candidate_id)) {
            return;
        }
        
        $this->db->select('c.*')
                ->from('candidates c')
                ->join('candidate_agencies ca', 'ca.candidate_id = c.id')
                ->where('c.id', $candidate_id)
                ->where('ca.agency_id', $agency_id)
                ->where('c.removed', 0);
        
        $candidate = $this->db->get()->row();
        
        if (!$candidate) {
            show_404();
            return;
        }
        
        $recruiter = $this->Model_chat_messages->get_candidate_recruiter($candidate_id);
        
        if (!$recruiter) {
            show_error('No recruiter found for this candidate', 404);
            return;
        }
        
        $conversation = $this->Model_chat_messages->get_or_create_candidate_conversation(
            $agency_id,
            $recruiter->id,
            $candidate_id,
            $candidate->job_id
        );
        
        if ($conversation) {
            redirect('/agency/chat/conversation/' . $conversation->uuid);
        } else {
            show_error('Failed to create chat conversation');
        }
    }

    private function check_candidate_access($candidate_id)
    {
        $user_agency_id = $this->get_user_agency_id();
                
        if (empty($user_agency_id)) {
            $user_type = getLoggedInUserTypeMenu();
            return ($user_type === 'admin');
        }
        
        $this->db->select('1');
        $this->db->from('candidate_agencies ca');
        $this->db->join('candidates c', 'c.id = ca.candidate_id');
        $this->db->where('ca.candidate_id', $candidate_id);
        $this->db->where('ca.agency_id', $user_agency_id);
        $this->db->where('c.removed', 0);
        
        $result = $this->db->get()->row();
                
        if (!$result) {
            $this->db->select('1');
            $this->db->from('candidates c');
            $this->db->where('c.id', $candidate_id);
            $this->db->where('c.agency_id', $user_agency_id);
            $this->db->where('c.removed', 0);
            
            $direct_result = $this->db->get()->row();
                        
            if (!$direct_result) {
                $this->db->select('1');
                $this->db->from('candidates c');
                $this->db->where('c.id', $candidate_id);
                $candidate_exists = $this->db->get()->row();
                                
                if ($candidate_exists) {
                }
                return false;
            }
        }
        
        return true;
    }
    
    private function candidate_access_denied()
    {
        if ($this->input->is_ajax_request()) {
            ajax_return([
                'success' => false,
                'message' => 'Access denied to this candidate',
                'csrf' => $this->security->get_csrf_hash()
            ]);
        } else {
            show_error('Access denied to this candidate', 403);
        }
        exit;
    }

    private function enforce_candidate_access($candidate_id)
    {
        // Get current agency
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            $this->candidate_access_denied();
            return false;
        }
        
    
        $this->db->select('COUNT(*) as has_access');
        $this->db->from('candidate_agencies ca');
        $this->db->join('candidates c', 'c.id = ca.candidate_id');
        $this->db->where('ca.candidate_id', $candidate_id);
        $this->db->where('ca.agency_id', $agency_id);
        $this->db->where('c.removed', 0);
        $this->db->limit(1);
        
        $result = $this->db->get()->row();
        
        if (!$result || $result->has_access == 0) {
            $this->candidate_access_denied();
            return false;
        }
        
        return true;
    }

    private function can_view_candidate($candidate_id)
    {
        $user_agency_id = $this->get_user_agency_id();
        
        if (empty($user_agency_id)) {
            $user_type = getLoggedInUserTypeMenu();
            return ($user_type === 'admin' || $user_type === 'recruiter');
        }
        
        $this->db->select('1');
        $this->db->from('candidate_agencies ca');
        $this->db->join('candidates c', 'c.id = ca.candidate_id');
        $this->db->where('ca.candidate_id', $candidate_id);
        $this->db->where('ca.agency_id', $user_agency_id);
        $this->db->where('c.removed', 0);
        
        return $this->db->get()->row() !== null;
    }

    private function can_edit_candidate($candidate_id)
    {
        $user_type = getLoggedInUserTypeMenu();
        
        if ($user_type === 'recruiter' || $user_type === 'admin') {
            return $this->can_view_candidate($candidate_id);
        }
        
        return false;
    }

    private function enforce_view_access($candidate_id)
    {
        if (!$this->can_view_candidate($candidate_id)) {
            $this->access_denied('view');
            return false;
        }
        return true;
    }

    private function enforce_edit_access($candidate_id)
    {
        if (!$this->can_edit_candidate($candidate_id)) {
            $this->access_denied('edit');
            return false;
        }
        return true;
    }

    private function access_denied($action = 'access')
    {
        $user_type = getLoggedInUserTypeMenu();
        
        $message = ($action === 'edit' && $user_type === 'agency') 
            ? 'Agencies cannot edit candidates. Please contact a recruiter.'
            : 'Access denied to this candidate';
        
        if ($this->input->is_ajax_request()) {
            ajax_return([
                'success' => false,
                'message' => $message,
                'csrf' => $this->security->get_csrf_hash()
            ]);
        } else {
            show_error($message, 403);
        }
        exit;
    }

    private function get_user_agency_id()
    {
        $login_data = $this->session->userdata('login');
        
        if (!empty($login_data['agency'])) {
            $agency_user = $login_data['agency'];
            
            if (!empty($agency_user['agency_id'])) {
                return $agency_user['agency_id'];
            } elseif (!empty($agency_user['id'])) {
                return $agency_user['id'];
            }
        }
        
        return null;
    }

    public function migrate_onboarding_data()
    {
        echo "<h2>Starting Onboarding Data Migration</h2>";
        
        $this->db->select('c.id as candidate_id, cja.job_id, ca.agency_id, 
                       c.stage_under_review, c.stage_under_review_at,
                       c.stage_submitted_to_hm, c.stage_submitted_to_hm_at,
                       c.stage_hm_decision, c.hm_decision, c.hm_decision_at, c.hm_decision_notes,
                       c.stage_documents_decision, c.documents_required, c.documents_notes, c.stage_documents_decision_at,
                       c.stage_requested_docs, c.stage_requested_docs_at,
                       c.stage_position_offered, c.stage_position_offered_at,
                       c.onboarding_stage, c.onboarding_progress, c.onboarding_completed_at,
                       c.status');
        $this->db->from('candidates c');
        $this->db->join('candidate_job_assignments cja', 'cja.candidate_id = c.id AND cja.removed = 0', 'inner');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
        $this->db->where('c.removed', 0);
        $this->db->group_by('c.id, cja.job_id, ca.agency_id');
        
        $candidates = $this->db->get()->result();
        
        $migrated_count = 0;
        
        foreach ($candidates as $candidate) {
            $existing = $this->db->where('candidate_id', $candidate->candidate_id)
                                ->where('job_id', $candidate->job_id)
                                ->where('agency_id', $candidate->agency_id)
                                ->get('candidate_onboarding_progress')
                                ->row();
            
            if (!$existing) {
                $onboarding_data = [
                    'candidate_id' => $candidate->candidate_id,
                    'job_id' => $candidate->job_id,
                    'agency_id' => $candidate->agency_id,
                    'stage_under_review' => $candidate->stage_under_review,
                    'stage_under_review_at' => $candidate->stage_under_review_at,
                    'stage_submitted_to_hm' => $candidate->stage_submitted_to_hm,
                    'stage_submitted_to_hm_at' => $candidate->stage_submitted_to_hm_at,
                    'stage_hm_decision' => $candidate->stage_hm_decision,
                    'hm_decision' => $candidate->hm_decision,
                    'hm_decision_at' => $candidate->hm_decision_at,
                    'hm_decision_notes' => $candidate->hm_decision_notes,
                    'stage_documents_decision' => $candidate->stage_documents_decision,
                    'documents_required' => $candidate->documents_required,
                    'documents_notes' => $candidate->documents_notes,
                    'stage_documents_decision_at' => $candidate->stage_documents_decision_at,
                    'stage_requested_docs' => $candidate->stage_requested_docs,
                    'stage_requested_docs_at' => $candidate->stage_requested_docs_at,
                    'stage_position_offered' => $candidate->stage_position_offered,
                    'stage_position_offered_at' => $candidate->stage_position_offered_at,
                    'onboarding_stage' => $candidate->onboarding_stage,
                    'onboarding_progress' => $candidate->onboarding_progress,
                    'onboarding_completed_at' => $candidate->onboarding_completed_at,
                    'status' => $candidate->status,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('candidate_onboarding_progress', $onboarding_data);
                $migrated_count++;
                echo "Migrated candidate ID {$candidate->candidate_id} for job {$candidate->job_id}, agency {$candidate->agency_id}<br>";
            }
        }
        
        echo "<h3>Migration completed. Migrated {$migrated_count} records.</h3>";
    }

    public function test_onboarding_filter()
    {
        $agency_id = $this->get_user_agency_id();
        
        $agency_name = 'Unknown Agency';
        if ($agency_id) {
            $agency = $this->db->select('name')
                              ->from('agencies')
                              ->where('id', $agency_id)
                              ->get()
                              ->row();
            $agency_name = $agency ? $agency->name : 'Unknown Agency';
        }
        
        echo "<h2>Testing Onboarding Filter for {$agency_name} (ID: {$agency_id})</h2>";
        
        $this->db->select('cop.*, j.name as job_name, j.agency_id as job_agency_id, 
                          c.first_name, c.last_name');
        $this->db->from('candidate_onboarding_progress cop');
        $this->db->join('candidates c', 'c.id = cop.candidate_id AND c.removed = 0', 'inner');
        $this->db->join('mod_jobs j', 'j.id = cop.job_id AND j.removed = 0', 'inner');
        $this->db->where('j.agency_id', $agency_id);
        $this->db->where('cop.agency_id', $agency_id);
        $this->db->order_by('cop.onboarding_progress', 'DESC');
        
        $candidates = $this->db->get()->result();
        
        echo "<p><strong>Found with agency filter:</strong> " . count($candidates) . " candidates</p>";
        
        if (!empty($candidates)) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Candidate</th><th>Job</th><th>Job Agency ID</th><th>Onboarding Agency ID</th><th>Match?</th></tr>";
            
            foreach ($candidates as $c) {
                $match = ($c->job_agency_id == $agency_id && $c->agency_id == $agency_id);
                echo "<tr>";
                echo "<td>{$c->first_name} {$c->last_name}</td>";
                echo "<td>{$c->job_name}</td>";
                echo "<td>" . ($c->job_agency_id == $agency_id ? 
                    "<span style='color:green'>{$c->job_agency_id}</span>" : 
                    "<span style='color:red'>{$c->job_agency_id}</span>") . "</td>";
                echo "<td>" . ($c->agency_id == $agency_id ? 
                    "<span style='color:green'>{$c->agency_id}</span>" : 
                    "<span style='color:red'>{$c->agency_id}</span>") . "</td>";
                echo "<td>" . ($match ? "✅" : "❌") . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<h3>Without Agency Filter:</h3>";
        
        $this->db->select('cop.*, j.name as job_name, j.agency_id as job_agency_id, 
                          c.first_name, c.last_name, cop.agency_id as onboarding_agency_id');
        $this->db->from('candidate_onboarding_progress cop');
        $this->db->join('candidates c', 'c.id = cop.candidate_id AND c.removed = 0', 'inner');
        $this->db->join('mod_jobs j', 'j.id = cop.job_id AND j.removed = 0', 'inner');
        $this->db->order_by('cop.onboarding_progress', 'DESC');
        
        $all_candidates = $this->db->get()->result();
        
        echo "<p><strong>Found ALL records:</strong> " . count($all_candidates) . " candidates (including other agencies)</p>";
        
        if (!empty($all_candidates)) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Candidate</th><th>Job</th><th>Job Agency ID</th><th>Onboarding Agency ID</th><th>Visible to You?</th></tr>";
            
            foreach ($all_candidates as $c) {
                $visible = ($c->job_agency_id == $agency_id && $c->onboarding_agency_id == $agency_id);
                echo "<tr>";
                echo "<td>{$c->first_name} {$c->last_name}</td>";
                echo "<td>{$c->job_name}</td>";
                echo "<td>{$c->job_agency_id}</td>";
                echo "<td>{$c->onboarding_agency_id}</td>";
                echo "<td>" . ($visible ? 
                    "<span style='color:green'>✅ Should see</span>" : 
                    "<span style='color:red'>❌ Should NOT see</span>") . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<h3>Debug: All records in candidate_onboarding_progress table</h3>";
        
        $all_records = $this->db->select('cop.*, j.agency_id as job_agency_id')
                               ->from('candidate_onboarding_progress cop')
                               ->join('mod_jobs j', 'j.id = cop.job_id', 'left')
                               ->get()
                               ->result();
        
        echo "<p>Total records in table: " . count($all_records) . "</p>";
        
        if (!empty($all_records)) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Candidate ID</th><th>Job ID</th><th>Agency ID</th><th>Job Agency ID</th><th>Consistent?</th></tr>";
            
            foreach ($all_records as $r) {
                $consistent = ($r->agency_id == $r->job_agency_id);
                echo "<tr style='" . (!$consistent ? "background: #ffcccc;" : "") . "'>";
                echo "<td>{$r->id}</td>";
                echo "<td>{$r->candidate_id}</td>";
                echo "<td>{$r->job_id}</td>";
                echo "<td>{$r->agency_id}</td>";
                echo "<td>" . ($r->job_agency_id ? $r->job_agency_id : 'NULL') . "</td>";
                echo "<td>" . ($consistent ? "✅" : "❌") . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    public function fix_onboarding_data_leaks()
    {
        echo "<pre>";
        echo "🔧 Fixing Onboarding Data Leaks\n";
        echo "===============================\n\n";
        
        $sql = "DELETE cop 
                FROM candidate_onboarding_progress cop
                INNER JOIN mod_jobs j ON j.id = cop.job_id
                WHERE j.agency_id != cop.agency_id";
        
        $this->db->query($sql);
        $deleted = $this->db->affected_rows();
        
        echo "✅ Deleted {$deleted} onboarding records with mismatched agency ownership\n\n";
        echo "🎯 Now run your security test again: /agency/Test_security\n";
        echo "</pre>";
    }

    public function check_onboarding()
    {
        $candidate_uuid = $this->input->get('candidate_uuid');
        $job_uuid = $this->input->get('job_uuid');
        $agency_id = $this->get_user_agency_id();
        
        header('Content-Type: application/json');
        
        if (!$candidate_uuid || !$job_uuid || !$agency_id) {
            echo json_encode(['error' => 'Missing parameters']);
            return;
        }
        
        // Get candidate
        $candidate = $this->db->where('uuid', $candidate_uuid)
                            ->where('removed', 0)
                            ->get('candidates')
                            ->row();
        
        // Get job
        $job = $this->db->where('uuid', $job_uuid)
                    ->where('agency_id', $agency_id)
                    ->where('removed', 0)
                    ->get('mod_jobs')
                    ->row();
        
        // Check assignment
        $assignment = null;
        if ($candidate && $job) {
            $assignment = $this->db->where('candidate_id', $candidate->id)
                                ->where('job_id', $job->id)
                                ->where('removed', 0)
                                ->get('candidate_job_assignments')
                                ->row();
        }
        
        // Get onboarding progress
        $progress = null;
        if ($candidate && $job) {
            $progress = $this->db->where('candidate_id', $candidate->id)
                            ->where('job_id', $job->id)
                            ->where('agency_id', $agency_id)
                            ->get('candidate_onboarding_progress')
                            ->row();
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'candidate_exists' => !!$candidate,
                'job_exists' => !!$job,
                'assignment_exists' => !!$assignment,
                'progress_exists' => !!$progress,
                'candidate_id' => $candidate ? $candidate->id : null,
                'job_id' => $job ? $job->id : null,
                'assignment_id' => $assignment ? $assignment->id : null,
                'progress_id' => $progress ? $progress->id : null
            ]
        ]);
    }

    /**
     * SIMPLE TEST Notification - This will definitely work
     */
    private function send_test_notification($candidate_id, $job_id, $decision, $notes = '')
    {
        try {
            // Get simple data
            $candidate = $this->db->where('id', $candidate_id)->get('candidates')->row();
            $job = $this->db->where('id', $job_id)->get('mod_jobs')->row();
            
            if (!$candidate || !$job) {
                return false;
            }
            
            // SIMPLE notification data - minimal fields
            $notification_data = [
                'title' => "Test: {$candidate->first_name} {$candidate->last_name}",
                'message' => "Test notification for {$job->name} - Decision: {$decision}",
                'type' => 'hm_decision',
                'sender_type' => 'agency',
                'sender_id' => 1, // Hardcode for testing
                'receiver_type' => 'recruiter',
                'receiver_id' => 1, // Hardcode for testing
                'related_entity' => 'candidate',
                'related_entity_id' => $candidate_id,
                'metadata' => json_encode([
                    'test' => 'yes',
                    'candidate_id' => $candidate_id,
                    'job_id' => $job_id,
                    'decision' => $decision
                ]),
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'removed' => 0,
                'enabled' => 1
            ];
            
            // Insert with error checking
            $result = $this->db->insert('notifications', $notification_data);
            
            if ($result) {
                $notification_id = $this->db->insert_id();
                return true;
            } else {
                $error = $this->db->error();
                return false;
            }
            
        } catch (Exception $e) {
            return false;
        }
    }


    public function log_contact_access($candidate_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            ajax_return(['success' => false, 'message' => 'Agency not found']);
            return;
        }
        
        // Verify the agency has access to this candidate
        $this->db->select('1');
        $this->db->from('candidate_agencies');
        $this->db->where('candidate_id', $candidate_id);
        $this->db->where('agency_id', $agency_id);
        $has_access = $this->db->get()->row() !== null;
        
        if (!$has_access) {
            ajax_return(['success' => false, 'message' => 'Access denied']);
            return;
        }
        
        // ✅ ✅ ✅ CRITICAL SECURITY FIX: Check agency access FIRST ✅ ✅ ✅
        if (!$this->enforce_candidate_access($candidate_id)) {
            // enforce_candidate_access() already sends the AJAX response
            return;
        }
        
        // Also verify contact access is granted
        $this->db->select('1');
        $this->db->from('candidate_contact_access');
        $this->db->where('candidate_id', $candidate_id);
        $this->db->where('agency_id', $agency_id);
        $this->db->where('access_granted', 1);
        $this->db->where('removed', 0);
        $has_contact_access = $this->db->get()->row() !== null;
        
        if (!$has_contact_access) {
            ajax_return(['success' => false, 'message' => 'Contact access not granted']);
            return;
        }
        
        // Log the access
        $log_data = [
            'candidate_id' => $candidate_id,
            'agency_id' => $agency_id,
            'viewed_by' => loginID('agency'),
            'viewed_at' => date('Y-m-d H:i:s'),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        ];
        
        $result = $this->db->insert('contact_access_logs', $log_data);
        
        if ($result) {
            ajax_return(['success' => true]);
        } else {
            ajax_return(['success' => false, 'message' => 'Failed to log access']);
        }
    }

    // In agency/Candidates.php controller
    public function check_contact_access_status()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $candidate_identifier = $this->input->get('candidate_uuid');
        
        if (!$candidate_identifier) {
            ajax_return(['success' => false, 'message' => 'Candidate not specified']);
            return;
        }
        
        // Get candidate
        $this->load->model('agency/Model_candidates');
        
        // Check if identifier is UUID or ID
        if (is_numeric($candidate_identifier)) {
            $candidate = $this->Model_candidates->get_candidate($candidate_identifier);
        } else {
            $candidate = $this->Model_candidates->get_candidate_by_uuid($candidate_identifier);
        }
        
        if (!$candidate) {
            ajax_return(['success' => false, 'message' => 'Candidate not found']);
            return;
        }
        
        $candidate_id = $candidate->id;
        
        // ✅ ✅ ✅ CRITICAL SECURITY FIX: Check agency access FIRST ✅ ✅ ✅
        if (!$this->enforce_candidate_access($candidate_id)) {
            // enforce_candidate_access() already sends the AJAX response
            return;
        }
        
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            ajax_return(['success' => false, 'message' => 'Agency not found']);
            return;
        }
        
        // Check if has contact access (separate from basic candidate access)
        $has_contact_access = $this->Model_candidates->check_contact_access($candidate_id, $agency_id);
        
        if ($has_contact_access) {
            // Get full contact info
            $contact_info = [
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'alternate_phone' => $candidate->alternate_phone,
                'has_access' => true
            ];
        } else {
            // Get masked info
            $masked_info = $this->Model_candidates->mask_contact_info(
                $candidate->email, 
                $candidate->phone
            );
            
            // Check if request is pending
            $this->db->select('id, status, last_requested_at');
            $this->db->from('candidate_contact_access');
            $this->db->where('candidate_id', $candidate_id);
            $this->db->where('agency_id', $agency_id);
            $this->db->where('removed', 0);
            $request = $this->db->get()->row();
            
            $contact_info = array_merge($masked_info, [
                'has_access' => false,
                'request_pending' => ($request && $request->status == 'pending'),
                'request_status' => $request ? $request->status : null,
                'last_requested' => $request ? $request->last_requested_at : null
            ]);
        }
        
        ajax_return([
            'success' => true,
            'contact_info' => $contact_info,
            'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name
        ]);
    }

    /**
     * Request contact information access
     */
    public function request_contact_access()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            ajax_return(['success' => false, 'message' => 'Agency not found']);
            return;
        }
        
        $candidate_identifier = $this->input->post('candidate_uuid');
        $notes = $this->input->post('notes');
        
        if (!$candidate_identifier) {
            ajax_return(['success' => false, 'message' => 'Candidate not specified']);
            return;
        }
        
        // Load the model
        $this->load->model('agency/Model_candidates');
        
        // Get candidate
        if (is_numeric($candidate_identifier)) {
            $candidate = $this->Model_candidates->get_candidate($candidate_identifier);
        } else {
            $candidate = $this->Model_candidates->get_candidate_by_uuid($candidate_identifier);
        }
        
        if (!$candidate) {
            ajax_return(['success' => false, 'message' => 'Candidate not found']);
            return;
        }
        
        $candidate_id = $candidate->id;
        
        // ✅ ✅ ✅ CRITICAL SECURITY FIX: Check agency access FIRST ✅ ✅ ✅
        if (!$this->enforce_candidate_access($candidate_id)) {
            // enforce_candidate_access() already sends the AJAX response
            return;
        }
        
        // Rest of your existing code continues...
        // Verify the agency has access to this candidate first
        $this->db->select('1');
        $this->db->from('candidate_agencies');
        $this->db->where('candidate_id', $candidate_id);
        $this->db->where('agency_id', $agency_id);
        $has_access = $this->db->get()->row() !== null;
        
        if (!$has_access) {
            ajax_return(['success' => false, 'message' => 'Access denied to this candidate']);
            return;
        }
        
        // Check if a request already exists and was recently made (within last 24 hours)
        $this->db->where('candidate_id', $candidate_id);
        $this->db->where('agency_id', $agency_id);
        $this->db->where('removed', 0);
        $this->db->where('last_requested_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')));
        $existing_recent = $this->db->get('candidate_contact_access')->row();
        
        if ($existing_recent) {
            ajax_return([
                'success' => false, 
                'message' => 'You have already requested access within the last 24 hours. Please wait before requesting again.'
            ]);
            return;
        }
        
        // Request contact access
        $result = $this->Model_candidates->request_contact_access(
            $candidate_id, 
            $agency_id, 
            loginID('agency'), 
            $notes
        );
        
        if ($result) {
            // Send notification to recruiter
            $notification_result = $this->Model_candidates->create_contact_request_notification(
                $candidate_id, 
                $agency_id, 
                $result
            );
            
            ajax_return([
                'success' => true,
                'message' => 'Contact information request sent successfully to the recruiter.',
                'request_id' => $result
            ]);
        } else {
            ajax_return(['success' => false, 'message' => 'Failed to send request. Please try again.']);
        }
    }


}