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
            'url'       => site_url('agency/candidates_list/view/{id}'),
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
        'onboarding' => array(
            'label'     => 'Onboarding',
            'url'       => redir($this->pageName . '/onboarding/{id}', true),
            'icon'      => 'fa-eye',
            'class'     => 'onboarding-row',
            'title'     => 'Manage candidate onboarding process',
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

public function onboarding($candidate_id) 
{
    $agency_id = $this->get_user_agency_id();
    
    if (!$agency_id) {
        show_error('Access denied', 403);
    }

    $subquery = $this->db->select('candidate_id')
        ->from('candidate_agencies')
        ->where('candidate_id', $candidate_id)
        ->where('agency_id', $agency_id)
        ->get_compiled_select();
    
    $this->db->select('c.*, j.name as job_name, j.reference_number as job_ref');
    $this->db->from('candidates c');
    $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
    $this->db->where('c.id', $candidate_id);
    $this->db->where("c.id IN ($subquery)", null, false);
    $this->db->where('c.removed', 0);
    
    $candidate = $this->db->get()->row();

    if (empty($candidate)) {
        show_404();
    }

    $this->db->where('candidate_id', $candidate_id);
    $this->db->where('removed', 0);
    $total_documents_count = $this->db->count_all_results('candidate_documents');

    log_message('debug', "DIRECT DB CHECK - Total documents for candidate {$candidate_id}: {$total_documents_count}");

    $required_documents = [];
    $has_required_docs = false;
    $can_mark_reviewed = false;
    
    try {
        if (method_exists($this->{$this->model}, 'check_documents_submission_status')) {
            $documents_status = $this->{$this->model}->check_documents_submission_status($candidate_id);
            $required_documents = $documents_status['documents'] ?? [];
            $has_required_docs = $documents_status['has_documents'] ?? false;
            
            $can_mark_reviewed = $has_required_docs && 
                                !$candidate->stage_requested_docs && 
                                isset($candidate->documents_required) && 
                                $candidate->documents_required;

            if ($total_documents_count > 0 && count($required_documents) === 0) {
                log_message('debug', "FILTER ISSUE: Database has {$total_documents_count} documents but query returned 0");
            }
        } else {
            log_message('error', 'check_documents_submission_status method not found in model');
        }
    } catch (Exception $e) {
        log_message('error', 'Error loading required documents: ' . $e->getMessage());
        $required_documents = [];
        $has_required_docs = false;
        $can_mark_reviewed = false;
    }

    log_message('debug', '=== DOCUMENTS DEBUG ===');
    log_message('debug', 'Candidate ID: ' . $candidate_id);
    log_message('debug', 'Has required docs: ' . ($has_required_docs ? 'YES' : 'NO'));
    log_message('debug', 'Documents count: ' . count($required_documents));
    log_message('debug', 'Can mark reviewed: ' . ($can_mark_reviewed ? 'YES' : 'NO'));
    log_message('debug', 'Stage requested docs: ' . ($candidate->stage_requested_docs ? 'YES' : 'NO'));
    log_message('debug', 'Documents required: ' . (isset($candidate->documents_required) ? ($candidate->documents_required ? 'YES' : 'NO') : 'NOT SET'));

    $this->breadcrumbs = array(
        array(
            'title' => lang($this->pageName . '_heading'),
            'url'   => redir($this->pageName, true)
        ),
        array(
            'title' => htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name, ENT_QUOTES, 'UTF-8'),
            'url'   => redir($this->pageName . '/view/' . $candidate_id, true)
        ),
        array(
            'title' => 'Onboarding',
            'url'   => redir($this->pageName . '/onboarding/' . $candidate_id, true)
        ),
    );

    $this->load->view($this->folder . '/view_header');
    $this->load->view('agency/candidates/onboarding', array(
        'candidate' => $candidate,
        'heading' => 'Candidate Onboarding - ' . $candidate->first_name . ' ' . $candidate->last_name,
        'required_documents' => $required_documents,
        'has_required_docs' => $has_required_docs,
        'can_mark_reviewed' => $can_mark_reviewed
    ));
    $this->load->view($this->folder . '/view_footer');
}

public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
{
    $agency_id = $this->get_user_agency_id();
    
    if (empty($agency_id)) {
        log_message('error', 'No agency_id found for filtering candidates');
        $this->db->where('candidates.id', 0);
        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

    log_message('debug', 'Main Candidates controller: Filtering candidates for agency_id = ' . $agency_id);

    return parent::get_all($limit, $offset, $sort_by, $sort_order);
}

public function onboarding_listing() 
{
    $agency_id = $this->get_user_agency_id();
    
    if (!$agency_id) {
        show_error('Access denied', 403);
    }

    $stats = $this->{$this->model}->get_onboarding_stats($agency_id);
    
    $this->db->select('c.*, j.name as job_name');
    $this->db->from('candidates c');
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
    $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
    
    $this->db->where('ca.agency_id', $agency_id);
    $this->db->where('c.removed', 0);
    $this->db->group_by('c.id');
    $this->db->order_by('c.onboarding_progress', 'DESC');
    
    $candidates = $this->db->get()->result();

    log_message('debug', "Onboarding listing - Agency ID: {$agency_id}, Candidates found: " . count($candidates));

    $this->load->view($this->folder . '/view_header');
    $this->load->view('agency/candidates/onboarding_listing', array(
        'candidates' => $candidates,
        'stats' => $stats,
        'heading' => 'Onboarding Management',
        'current_agency_id' => $agency_id
    ));
    $this->load->view($this->folder . '/view_footer');
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
    $candidate_id = $this->input->post('candidate_id');
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

public function update_onboarding_stage() {
    $candidate_id = $this->input->post('candidate_id');
    $stage = $this->input->post('stage');
    $value = $this->input->post('value');

    $agency_id = $this->get_user_agency_id();
    if ($agency_id) {
        $exists = $this->db->select('1')
            ->from('candidate_agencies')
            ->where('candidate_id', $candidate_id)
            ->where('agency_id', $agency_id)
            ->get()
            ->row();
        
        if (!$exists) {
            ajax_return([
                'success' => false,
                'message' => 'Candidate not found or access denied'
            ]);
            return;
        }
    }

    if ($stage === 'stage_documents_decision' && $value == 0) {
        $this->db->where('id', $candidate_id)->update('candidates', [
            'documents_required' => null,
            'documents_notes' => null,
            'stage_documents_decision_at' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    $result = $this->{$this->model}->update_onboarding_stage($candidate_id, $stage, $value);

    if ($result) {
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

public function update_hm_decision() {
    $candidate_id = $this->input->post('candidate_id');
    $decision = $this->input->post('decision');
    $notes = $this->input->post('notes');

    $agency_id = $this->get_user_agency_id();
    if ($agency_id) {
        $exists = $this->db->select('1')
            ->from('candidate_agencies')
            ->where('candidate_id', $candidate_id)
            ->where('agency_id', $agency_id)
            ->get()
            ->row();
        
        if (!$exists) {
            ajax_return([
                'success' => false,
                'message' => 'Candidate not found or access denied'
            ]);
            return;
        }
    }

    $status_mapping = [
        'accepted' => 'hired',
        'rejected' => 'rejected'
    ];

    $result = $this->{$this->model}->update_hm_decision($candidate_id, $decision, $notes);

    if ($result) {
        if (isset($status_mapping[$decision])) {
            $new_status = $status_mapping[$decision];
            $this->db->where('id', $candidate_id)->update('candidates', [
                'status' => $new_status,
                'status_updated_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->send_hm_decision_notification($candidate_id, $decision, $notes);

        $decision_text = $decision === 'accepted' ? 'accepted' : 'rejected';
        $this->{$this->model}->log_candidate_activity([
            'candidate_id' => $candidate_id,
            'action' => 'hm_decision_' . $decision_text,
            'description' => 'Hiring Manager ' . $decision_text . ' the candidate - Status updated to: ' . $new_status . ($notes ? ' with notes' : ''),
            'created_by' => loginID('agency'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        ajax_return([
            'success' => true,
            'message' => 'Hiring Manager decision updated successfully',
            'new_status' => $new_status ?? null
        ]);
    } else {
        ajax_return([
            'success' => false,
            'message' => 'Failed to update Hiring Manager decision'
        ]);
    }
}

public function update_documents_decision() {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    try {
        $candidate_id = $this->input->post('candidate_id');
        $documents_required = $this->input->post('documents_required');
        $documents_notes = $this->input->post('documents_notes');

        if (empty($candidate_id)) {
            throw new Exception('Candidate ID is required');
        }

        if ($documents_required === '') {
            throw new Exception('Please specify if documents are required');
        }

        $agency_id = $this->get_user_agency_id();
        if ($agency_id) {
            $exists = $this->db->select('1')
                ->from('candidate_agencies')
                ->where('candidate_id', $candidate_id)
                ->where('agency_id', $agency_id)
                ->get()
                ->row();
            
            if (!$exists) {
                throw new Exception('Candidate not found or access denied');
            }
        }

        $documents_required_bool = ($documents_required == '1');

        $update_data = [
            'stage_documents_decision' => 1,
            'documents_required' => $documents_required_bool,
            'documents_notes' => $documents_notes ?: null,
            'stage_documents_decision_at' => date('Y-m-d H:i:s'),
            'onboarding_stage' => 'stage_documents_decision',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $candidate_id);
        $success = $this->db->update('candidates', $update_data);

        if (!$success) {
            throw new Exception('Failed to update database');
        }

        $this->{$this->model}->update_onboarding_progress($candidate_id);

        if ($documents_required_bool) {
            $this->send_documents_request_notification($candidate_id, $documents_notes);
        }

        $decision_text = $documents_required_bool ? 'documents_required' : 'no_documents_required';
        $this->{$this->model}->log_candidate_activity([
            'candidate_id' => $candidate_id,
            'action' => $decision_text,
            'description' => $documents_required_bool ? 'Additional documents required: ' . $documents_notes : 'No additional documents required',
            'created_by' => loginID('agency'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Documents decision updated successfully'
            ]));

    } catch (Exception $e) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
    }
}

private function update_onboarding_progress($candidate_id)
{
    $candidate = $this->get_candidate_details($candidate_id);
    
    if (!$candidate) {
        log_message('error', "Candidate {$candidate_id} not found for progress update");
        return;
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
    $total_considered_stages = count($stages);
    
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
            log_message('debug', "Auto-completing stage_requested_docs since documents are not required");
        }
        
        if (isset($candidate->stage_position_offered) && $candidate->stage_position_offered == 1) {
            $completed_stages = count($stages);
            log_message('debug', "All stages completed including skipped stage_requested_docs");
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
        $this->db->where('id', $candidate_id)->update($this->table, [
            'onboarding_completed_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        $this->db->where('id', $candidate_id)->update($this->table, [
            'onboarding_completed_at' => null
        ]);
    }

    $progress_percentage = ($completed_stages / count($stages)) * 100;

    $update_data = [
        'onboarding_stage' => $current_stage,
        'onboarding_progress' => $progress_percentage,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $result = $this->db->where('id', $candidate_id)->update($this->table, $update_data);

    log_message('debug', "=== PROGRESS CALCULATION DEBUG ===");
    log_message('debug', "Candidate: {$candidate_id}");
    log_message('debug', "Stages completed: {$completed_stages}/" . count($stages));
    log_message('debug', "Progress: {$progress_percentage}%");
    log_message('debug', "Current stage: {$current_stage}");
    log_message('debug', "Documents required: " . (isset($candidate->documents_required) ? $candidate->documents_required : 'NOT SET'));
    log_message('debug', "Stage requested docs: " . (isset($candidate->stage_requested_docs) ? $candidate->stage_requested_docs : 'NOT SET'));
    log_message('debug', "Stage position offered: " . (isset($candidate->stage_position_offered) ? $candidate->stage_position_offered : 'NOT SET'));
    log_message('debug', "=== END PROGRESS DEBUG ===");

    return $result;
}

private function send_documents_request_notification($candidate_id, $documents_notes) {
    try {
        $this->load->model('agency/Model_notifications');
        
        $candidate = $this->{$this->model}->get_candidate_details($candidate_id);
        
        if (!$candidate) {
            log_message('error', "Candidate {$candidate_id} not found for documents request notification");
            return false;
        }

        $submitting_agency_id = $this->get_submitting_agency_id($candidate_id);
        
        if (!$submitting_agency_id) {
            log_message('error', "No submitting agency found for candidate {$candidate_id}");
            return false;
        }

        $notification_sent = $this->Model_notifications->create_documents_request_notification(
            $candidate_id,
            $candidate->job_id,
            $submitting_agency_id,
            $documents_notes,
            $this->get_user_agency_id()
        );

        if ($notification_sent) {
            log_message('debug', "Documents request notification sent for candidate {$candidate_id} to agency {$submitting_agency_id}");
        } else {
            log_message('error', "Failed to send documents request notification for candidate {$candidate_id}");
        }

        return $notification_sent;

    } catch (Exception $e) {
        log_message('error', 'Error sending documents request notification: ' . $e->getMessage());
        return false;
    }
}

    public function update($id) {
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
            
            log_message('debug', 'Agency update - Filtered data: ' . print_r($_POST, true));
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

private function get_user_agency_id()
{
    $login = $this->session->userdata('login');
    
    log_message('debug', 'Agency session data: ' . print_r($login, true));
    
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
    
    log_message('error', 'CRITICAL: No agency_id found in session for agency user');
    show_error('Agency authentication failed. Please log in again.', 403);
    return null;
}

    public function index(): void{
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

    // Get agency info - this should be an object, not a result object
    try {
        $agency_result = $this->{$this->model}->get_agency_by_id($agency_id);
        $agencies_all = $agency_result; // This is an object, not a result object
    } catch (Exception $e) {
        log_message('error', 'Error loading agencies: ' . $e->getMessage());
        $agencies_all = null;
    }

    // Get jobs - ensure we're working with result objects
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
        log_message('error', 'Error loading jobs: ' . $e->getMessage());
        $jobs_all = ['' => 'Error loading jobs'];
    }

    // Get agents - ensure we're working with result objects
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
        log_message('error', 'Error loading agents: ' . $e->getMessage());
        $agents_all = ['' => 'Error loading agents'];
    }

    // Get candidate data
    if ($candidateId > 0) {
        try {
            if (method_exists($this->{$this->model}, 'get_candidate_details')) {
                $candidate_data = $this->{$this->model}->get_candidate_details($candidateId);
            } else {
                log_message('error', 'get_candidate_details method not found in model');
                $candidate_data = null;
            }
        } catch (Exception $e) {
            log_message('error', 'Error loading candidate data: ' . $e->getMessage());
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

public function enable($id) {
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
    parent::enable($id);
}

public function disable($id) {
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

public function edit($id) {
    $agency_id = $this->get_user_agency_id();
    if ($agency_id) {
        $exists = $this->db->select('1')
            ->from('candidate_agencies')
            ->where('candidate_id', $id)
            ->where('agency_id', $agency_id)
            ->get()
            ->row();
        
        if (!$exists) {
            show_error('Candidate not found or access denied', 403);
        }
    }
    parent::edit($id);
}

public function view($id)
{
    $agency_id = $this->get_user_agency_id();
    if (!$agency_id) {
        show_error('Access denied');
    }

    $this->db->select('c.*, j.name as job_name');
    $this->db->from('candidates c');
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
    $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
    $this->db->where('c.id', $id);
    $this->db->where('ca.agency_id', $agency_id);
    $this->db->where('c.removed', 0);
    
    $row = $this->db->get()->row();

    if (empty($row)) {
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

public function complete_onboarding() {
    $candidate_id = $this->input->post('candidate_id');
    
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

private function send_hm_decision_notification($candidate_id, $decision, $notes = '') {
    try {
        $this->load->model('agency/Model_notifications');
        
        $candidate = $this->{$this->model}->get_candidate_details($candidate_id);
        
        if (!$candidate) {
            log_message('error', "Candidate {$candidate_id} not found for HM decision notification");
            return false;
        }

        $submitting_agency_id = $this->get_submitting_agency_id($candidate_id);
        
        if (!$submitting_agency_id) {
            log_message('error', "No submitting agency found for candidate {$candidate_id}");
            return false;
        }

        $current_agency_id = $this->get_user_agency_id();

        $notification_sent = $this->Model_notifications->create_hm_decision_notification(
            $candidate_id,
            $candidate->job_id,
            $submitting_agency_id,
            $decision,
            $notes,
            $current_agency_id
        );

        if ($notification_sent) {
            log_message('debug', "HM decision notification sent for candidate {$candidate_id} to agency {$submitting_agency_id}");
        } else {
            log_message('error', "Failed to send HM decision notification for candidate {$candidate_id}");
        }

        return $notification_sent;

    } catch (Exception $e) {
        log_message('error', 'Error sending HM decision notification: ' . $e->getMessage());
        return false;
    }
}

    private function get_submitting_agency_id($candidate_id) {
        $this->db->select('agency_id')
                 ->from('candidate_agencies')
                 ->where('candidate_id', $candidate_id)
                 ->order_by('created_at', 'ASC')
                 ->limit(1);
        
        $result = $this->db->get()->row();
        
        if ($result) {
            return $result->agency_id;
        }

        $this->db->select('agency_id')
                 ->from('candidates')
                 ->where('id', $candidate_id);
        
        $result = $this->db->get()->row();
        
        return $result ? $result->agency_id : null;
    }

public function check_documents_submission($candidate_id) {
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
        log_message('error', "No agency ID found for documents query");
        return [];
    }
    
    $has_access = $this->check_agency_candidate_access($agency_id, $candidate_id);
    
    if (!$has_access) {
        log_message('error', "Agency {$agency_id} attempted to access documents for unauthorized candidate {$candidate_id}");
        return [];
    }

    log_message('debug', "Querying documents for candidate {$candidate_id} - ALL TYPES");

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
    
    log_message('debug', "Found " . count($result) . " documents for candidate {$candidate_id}");
    
    foreach ($result as $doc) {
        log_message('debug', "Document: ID={$doc->id}, Name='{$doc->document_name}', Type='{$doc->document_type}', Created='{$doc->created_at}'");
    }
    
    return $result;
}

/**
 * Get current agency ID from session
 */
private function get_current_agency_id() {
    $ci = &get_instance();
    $login = $ci->session->userdata('login');
    
    if (!empty($login['agency'])) {
        $agency_user = $login['agency'];
        
        // Check all possible agency ID locations
        if (!empty($agency_user['agency_id'])) {
            return $agency_user['agency_id'];
        } elseif (!empty($agency_user['id'])) {
            return $agency_user['id'];
        } elseif (!empty($agency_user['agency']['id'])) {
            return $agency_user['agency']['id'];
        }
    }
    
    log_message('error', 'No agency_id found in session');
    return null;
}

public function debug_agency_filtering()
{
    $agency_id = $this->get_user_agency_id();
    log_message('debug', '=== AGENCY FILTERING DEBUG ===');
    log_message('debug', 'Current Agency ID: ' . $agency_id);
    
    $this->db->select('c.id, c.first_name, c.last_name, ca.agency_id');
    $this->db->from('candidates c');
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'left');
    $this->db->where('c.removed', 0);
    
    if ($agency_id) {
        $this->db->where('ca.agency_id', $agency_id);
    }
    
    $all_candidates = $this->db->get()->result();
    
    log_message('debug', 'Candidates visible to current agency: ' . count($all_candidates));
    foreach ($all_candidates as $candidate) {
        log_message('debug', "Candidate {$candidate->id}: {$candidate->first_name} - Agency: {$candidate->agency_id}");
    }
    
    log_message('debug', '=== END DEBUG ===');
}



/**
 * Check if agency has access to candidate
 */
private function check_agency_candidate_access($agency_id, $candidate_id)
{
    $this->db->select('1');
    $this->db->from('candidate_agencies');
    $this->db->where('candidate_id', $candidate_id);
    $this->db->where('agency_id', $agency_id);
    
    return $this->db->get()->row() !== null;
}

/**
 * Get candidate details
 */
public function get_candidate_details($candidate_id)
{
    $this->db->select('c.*, j.name as job_name, j.reference_number as job_ref');
    $this->db->from('candidates c');
    $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
    $this->db->where('c.id', $candidate_id);
    $this->db->where('c.removed', 0);
    
    return $this->db->get()->row();
}

/**
 * Log candidate activity
 */
public function log_candidate_activity($activity_data)
{
    return $this->db->insert('candidate_activities', $activity_data);
}

/**
 * Check documents submission status
 */
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
}