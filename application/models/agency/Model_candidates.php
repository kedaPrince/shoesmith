<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Model_candidates extends CRUD_Model
{
    protected $table = 'candidates';

public function selects()
{
    $this->db->distinct();
    
    // Get current agency ID
    $agency_id = $this->get_current_agency_id();
    
    // Select fields
    $this->db->select('candidates.id, candidates.enabled, candidates.reference_number, candidates.first_name, candidates.last_name, candidates.email, candidates.phone, candidates.status, candidates.application_date, mod_jobs.name as job_name, candidates.agency_id, candidates.job_id, candidates.onboarding_stage, candidates.stage_under_review, candidates.stage_submitted_to_hm, candidates.stage_requested_docs, candidates.stage_position_offered, candidates.onboarding_completed_at');
    
    // Join with jobs table
    $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
    
    // ✅ CRITICAL FIX: Use ONLY pivot table for filtering
    if ($agency_id) {
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = candidates.id', 'inner');
        $this->db->where('ca.agency_id', $agency_id);
        // Remove the primary agency_id filter to avoid conflicts
        // $this->db->where('candidates.agency_id', $agency_id); // COMMENT THIS OUT
    }
    
    // Universal filters
    $this->db->where('candidates.enabled', 1);
    $this->db->where('candidates.removed', 0);
}
/**
 * Apply agency filter to query - CENTRALIZED METHOD
 */
public function apply_agency_filter($agency_id = null)
{
    if (!$agency_id) {
        $agency_id = $this->get_current_agency_id();
    }
    
    if ($agency_id) {
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = candidates.id', 'inner');
        $this->db->where('ca.agency_id', $agency_id);
        $this->db->group_by('candidates.id'); // Avoid duplicates
    }
    
    return $this;
}
 


/**
 * Update onboarding stage - ENHANCED VERSION
 */
public function update_onboarding_stage($candidate_id, $stage, $value)
{
    $update_data = array(
        $stage => $value,
        'updated_at' => date('Y-m-d H:i:s')
    );

    // If marking a stage as completed, update the timestamp
    if ($value == 1) {
        $stage_timestamp_field = $stage . '_at';
        $update_data[$stage_timestamp_field] = date('Y-m-d H:i:s');
    } else {
        // If reopening a stage, clear the timestamp
        $stage_timestamp_field = $stage . '_at';
        $update_data[$stage_timestamp_field] = null;
    }

    $result = $this->db->where('id', $candidate_id)->update($this->table, $update_data);

    // ✅ CRITICAL: Always update onboarding progress after stage change
    if ($result) {
        $this->update_onboarding_progress($candidate_id);
    }

    return $result;
}


/**
 * Calculate and update overall onboarding progress - FIXED VERSION
 */
public function update_onboarding_progress($candidate_id)
{
    $candidate = $this->get_candidate_details($candidate_id);
    
    if (!$candidate) {
        log_message('error', "Candidate {$candidate_id} not found for progress update");
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

    $completed_stages = 0;
    $total_stages = count($stages);
    
    // Count completed stages with special handling for skipped stages
    foreach ($stages as $stage) {
        $is_completed = false;
        
        // Check if stage property exists
        if (isset($candidate->$stage)) {
            // Special case: stage_requested_docs is considered completed if documents are not required
            if ($stage === 'stage_requested_docs') {
                if ($candidate->$stage == 1 || 
                    (isset($candidate->documents_required) && $candidate->documents_required == 0)) {
                    $is_completed = true;
                }
            } 
            // Regular stage completion check
            else {
                $is_completed = ($candidate->$stage == 1);
            }
        }
        
        if ($is_completed) {
            $completed_stages++;
        }
    }

    // Determine current stage
    $current_stage = 'not_started';
    
    if ($completed_stages == $total_stages) {
        $current_stage = 'completed';
    } elseif ($completed_stages > 0) {
        // Find the current active stage (first incomplete stage)
        foreach ($stages as $stage) {
            $is_incomplete = true;
            
            // Check if stage property exists
            if (isset($candidate->$stage)) {
                // Special case: stage_requested_docs is not incomplete if documents are not required
                if ($stage === 'stage_requested_docs') {
                    $is_incomplete = ($candidate->$stage == 0 && 
                                    (!isset($candidate->documents_required) || $candidate->documents_required == 1));
                } else {
                    $is_incomplete = ($candidate->$stage == 0);
                }
            }
            
            if ($is_incomplete) {
                $current_stage = $stage;
                break;
            }
        }
    }

    // Update completion status
    if ($completed_stages == $total_stages) {
        $this->db->where('id', $candidate_id)->update($this->table, [
            'onboarding_completed_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        $this->db->where('id', $candidate_id)->update($this->table, [
            'onboarding_completed_at' => null
        ]);
    }

    $progress_percentage = round(($completed_stages / $total_stages) * 100);

    $update_data = [
        'onboarding_stage' => $current_stage,
        'onboarding_progress' => $progress_percentage,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    log_message('debug', "Progress update - Candidate: {$candidate_id}, Stages: {$completed_stages}/{$total_stages}, Progress: {$progress_percentage}%");

    return $this->db->where('id', $candidate_id)->update($this->table, $update_data);
}

/**
     * Create documents request notification for recruiters using existing notifications table
     */
    public function create_documents_request_notification($candidate_id, $job_id, $agency_id, $documents_notes, $requesting_agency_id = null) {
        try {
            // Get candidate details
            $this->db->select('first_name, last_name, reference_number');
            $this->db->from('candidates');
            $this->db->where('id', $candidate_id);
            $candidate = $this->db->get()->row();
            
            if (!$candidate) {
                log_message('error', "Candidate {$candidate_id} not found for documents request notification");
                return false;
            }

            // Get job details
            $job_name = 'Unknown Job';
            if ($job_id) {
                $this->db->select('name');
                $this->db->from('mod_jobs');
                $this->db->where('id', $job_id);
                $job = $this->db->get()->row();
                if ($job) {
                    $job_name = $job->name;
                }
            }

            // Get requesting agency name
            $requesting_agency_name = 'Hiring Manager';
            if ($requesting_agency_id) {
                $this->db->select('name');
                $this->db->from('agencies');
                $this->db->where('id', $requesting_agency_id);
                $agency = $this->db->get()->row();
                if ($agency) {
                    $requesting_agency_name = $agency->name;
                }
            }

            // Get all recruiters from the submitting agency
            $this->db->select('id, first_name, last_name');
            $this->db->from('recruiters');
            $this->db->where('agency_id', $agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $recruiters = $this->db->get()->result();

            if (empty($recruiters)) {
                log_message('error', "No recruiters found for agency {$agency_id} to send documents request notification");
                return false;
            }

            $notifications_created = 0;

            foreach ($recruiters as $recruiter) {
                $notification_data = [
                    'title' => '📋 Additional Documents Required',
                    'message' => "The hiring manager ({$requesting_agency_name}) requires additional documents for candidate {$candidate->first_name} {$candidate->last_name} ({$candidate->reference_number}) for position: {$job_name}.",
                    'type' => 'hm_decision',
                    'sender_type' => 'agency',
                    'sender_id' => $requesting_agency_id,
                    'receiver_type' => 'recruiter',
                    'receiver_id' => $recruiter->id,
                    'related_entity' => 'candidate',
                    'related_entity_id' => $candidate_id,
                    'metadata' => json_encode([
                        'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                        'candidate_reference' => $candidate->reference_number,
                        'job_name' => $job_name,
                        'requesting_agency' => $requesting_agency_name,
                        'required_documents' => $documents_notes,
                        'action_required' => 'Please upload the required documents to the candidate profile',
                        'notification_type' => 'documents_request',
                        'action_url' => site_url("recruiter/candidates/view/{$candidate_id}#documents")
                    ]),
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'enabled' => 1
                ];

                // Insert notification using your existing table
                if ($this->db->insert('notifications', $notification_data)) {
                    $notifications_created++;
                }
            }

            log_message('debug', "Created {$notifications_created} documents request notifications for candidate {$candidate_id}");
            return $notifications_created > 0;

        } catch (Exception $e) {
            log_message('error', 'Error creating documents request notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create notification for agency when documents are uploaded by recruiter
     */
    public function create_documents_uploaded_notification($candidate_id, $uploaded_by_recruiter_id, $document_count = 1) {
        try {
            // Get candidate details
            $this->db->select('first_name, last_name, reference_number, agency_id');
            $this->db->from('candidates');
            $this->db->where('id', $candidate_id);
            $candidate = $this->db->get()->row();
            
            if (!$candidate) {
                log_message('error', "Candidate {$candidate_id} not found for documents uploaded notification");
                return false;
            }

            // Get recruiter details
            $this->db->select('first_name, last_name, agency_id');
            $this->db->from('recruiters');
            $this->db->where('id', $uploaded_by_recruiter_id);
            $recruiter = $this->db->get()->row();

            if (!$recruiter) {
                log_message('error', "Recruiter {$uploaded_by_recruiter_id} not found for documents uploaded notification");
                return false;
            }

            // Get all agency users (for the agency that requested documents)
            $this->db->select('id, first_name, last_name');
            $this->db->from('agency_staff');
            $this->db->where('agency_id', $candidate->agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $agency_users = $this->db->get()->result();

            if (empty($agency_users)) {
                log_message('error', "No agency users found for agency {$candidate->agency_id} to send documents uploaded notification");
                return false;
            }

            $notifications_created = 0;

            foreach ($agency_users as $agency_user) {
                $notification_data = [
                    'title' => '📄 Documents Uploaded',
                    'message' => "Recruiter {$recruiter->first_name} {$recruiter->last_name} has uploaded {$document_count} document(s) for candidate {$candidate->first_name} {$candidate->last_name} ({$candidate->reference_number}).",
                    'type' => 'candidate_applied',
                    'sender_type' => 'recruiter',
                    'sender_id' => $uploaded_by_recruiter_id,
                    'receiver_type' => 'agency',
                    'receiver_id' => $agency_user->id,
                    'related_entity' => 'candidate',
                    'related_entity_id' => $candidate_id,
                    'metadata' => json_encode([
                        'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                        'candidate_reference' => $candidate->reference_number,
                        'recruiter_name' => $recruiter->first_name . ' ' . $recruiter->last_name,
                        'document_count' => $document_count,
                        'action_required' => 'Review the uploaded documents',
                        'notification_type' => 'documents_uploaded',
                        'action_url' => site_url("agency/candidates/view/{$candidate_id}#documents")
                    ]),
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'enabled' => 1
                ];

                // Insert notification
                if ($this->db->insert('notifications', $notification_data)) {
                    $notifications_created++;
                }
            }

            log_message('debug', "Created {$notifications_created} documents uploaded notifications for candidate {$candidate_id}");
            return $notifications_created > 0;

        } catch (Exception $e) {
            log_message('error', 'Error creating documents uploaded notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create HM decision notification for recruiters
     */
    public function create_hm_decision_notification($candidate_id, $job_id, $agency_id, $decision, $notes = '', $requesting_agency_id = null) {
        try {
            // Get candidate details
            $this->db->select('first_name, last_name, reference_number');
            $this->db->from('candidates');
            $this->db->where('id', $candidate_id);
            $candidate = $this->db->get()->row();
            
            if (!$candidate) {
                log_message('error', "Candidate {$candidate_id} not found for HM decision notification");
                return false;
            }

            // Get job details
            $job_name = 'Unknown Job';
            if ($job_id) {
                $this->db->select('name');
                $this->db->from('mod_jobs');
                $this->db->where('id', $job_id);
                $job = $this->db->get()->row();
                if ($job) {
                    $job_name = $job->name;
                }
            }

            // Get requesting agency name
            $requesting_agency_name = 'Hiring Manager';
            if ($requesting_agency_id) {
                $this->db->select('name');
                $this->db->from('agencies');
                $this->db->where('id', $requesting_agency_id);
                $agency = $this->db->get()->row();
                if ($agency) {
                    $requesting_agency_name = $agency->name;
                }
            }

            // Get all recruiters from the submitting agency
            $this->db->select('id, first_name, last_name');
            $this->db->from('recruiters');
            $this->db->where('agency_id', $agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $recruiters = $this->db->get()->result();

            if (empty($recruiters)) {
                log_message('error', "No recruiters found for agency {$agency_id} to send HM decision notification");
                return false;
            }

            $notifications_created = 0;
            $decision_icon = $decision === 'accepted' ? '✅' : '❌';
            $decision_text = $decision === 'accepted' ? 'accepted' : 'rejected';

            foreach ($recruiters as $recruiter) {
                $notification_data = [
                    'title' => "{$decision_icon} Candidate {$decision_text}",
                    'message' => "The hiring manager ({$requesting_agency_name}) has {$decision_text} candidate {$candidate->first_name} {$candidate->last_name} ({$candidate->reference_number}) for position: {$job_name}.",
                    'type' => 'hm_decision',
                    'sender_type' => 'agency',
                    'sender_id' => $requesting_agency_id,
                    'receiver_type' => 'recruiter',
                    'receiver_id' => $recruiter->id,
                    'related_entity' => 'candidate',
                    'related_entity_id' => $candidate_id,
                    'metadata' => json_encode([
                        'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                        'candidate_reference' => $candidate->reference_number,
                        'job_name' => $job_name,
                        'requesting_agency' => $requesting_agency_name,
                        'decision' => $decision,
                        'decision_notes' => $notes,
                        'action_required' => $decision === 'accepted' ? 'Continue with onboarding process' : 'No further action required',
                        'notification_type' => 'hm_decision',
                        'action_url' => site_url("recruiter/candidates/view/{$candidate_id}")
                    ]),
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'enabled' => 1
                ];

                // Insert notification
                if ($this->db->insert('notifications', $notification_data)) {
                    $notifications_created++;
                }
            }

            log_message('debug', "Created {$notifications_created} HM decision notifications for candidate {$candidate_id}");
            return $notifications_created > 0;

        } catch (Exception $e) {
            log_message('error', 'Error creating HM decision notification: ' . $e->getMessage());
            return false;
        }
    }



/**
 * Save candidate document
 */
public function save_candidate_document($data) {
    return $this->db->insert('candidate_documents', $data);
}

/**
 * Get candidate documents
 */
public function get_candidate_documents($candidate_id) {
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
    
    return $this->db->get()->result();
}
/**
 * Update documents decision
 */
// In your Model_candidates.php - ADD THIS METHOD
public function update_documents_decision($candidate_id, $documents_required, $documents_notes) {
    $update_data = [
        'stage_documents_decision' => 1,
        'documents_required' => $documents_required,
        'documents_notes' => $documents_notes ?: null,
        'stage_documents_decision_at' => date('Y-m-d H:i:s'),
        'onboarding_stage' => 'stage_documents_decision',
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $this->db->where('id', $candidate_id);
    return $this->db->update('candidates', $update_data);
}
/**
 * Get onboarding statistics for dashboard - FIXED VERSION (Pivot Table Only)
 */
public function get_onboarding_stats($agency_id = null)
{
    $agency_id = $agency_id ?: $this->get_current_agency_id();
    
    if (!$agency_id) {
        log_message('error', 'No agency_id provided for onboarding stats');
        return $this->get_empty_stats_object();
    }

    try {
        // Build query using ONLY pivot table for filtering
        $this->db->select('
            COUNT(DISTINCT c.id) as total_candidates,
            COUNT(DISTINCT CASE WHEN c.stage_under_review = 1 THEN c.id END) as under_review_count,
            COUNT(DISTINCT CASE WHEN c.stage_submitted_to_hm = 1 THEN c.id END) as submitted_hm_count,
            COUNT(DISTINCT CASE WHEN c.stage_hm_decision = 1 THEN c.id END) as hm_decision_count,
            COUNT(DISTINCT CASE WHEN c.stage_requested_docs = 1 THEN c.id END) as requested_docs_count,
            COUNT(DISTINCT CASE WHEN c.stage_position_offered = 1 THEN c.id END) as position_offered_count,
            COUNT(DISTINCT CASE WHEN c.onboarding_stage = "completed" THEN c.id END) as completed_count,
            COUNT(DISTINCT CASE WHEN c.hm_decision = "accepted" THEN c.id END) as hm_accepted_count,
            COUNT(DISTINCT CASE WHEN c.hm_decision = "rejected" THEN c.id END) as hm_rejected_count
        ');
        
        $this->db->from('candidates c');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
        
        // ✅ CRITICAL: Filter ONLY by pivot table agency_id
        $this->db->where('ca.agency_id', $agency_id);
        $this->db->where('c.removed', 0);

        $result = $this->db->get()->row();

        return $result;

    } catch (Exception $e) {
        log_message('error', 'Error getting onboarding stats: ' . $e->getMessage());
        return $this->get_empty_stats_object();
    }
}


/**
 * Get empty stats object
 */
private function get_empty_stats_object()
{
    return (object)[
        'total_candidates' => 0,
        'under_review_count' => 0,
        'submitted_hm_count' => 0,
        'hm_decision_count' => 0,
        'requested_docs_count' => 0,
        'position_offered_count' => 0,
        'completed_count' => 0,
        'hm_accepted_count' => 0,
        'hm_rejected_count' => 0
    ];
}

    /**
     * Get agency by ID (for dropdown)
     */
    public function get_agency_by_id($agency_id) {
        if (empty($agency_id)) {
            return false;
        }
        
        $this->db->select('id, name');
        $this->db->from('agencies');
        $this->db->where('id', $agency_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        
        return $this->db->get();
    }

    /**
     * Get jobs by agency ID
     */
    public function get_jobs_by_agency($agency_id) {
        if (empty($agency_id)) {
            return false;
        }
        
        $this->db->select('id, name, reference_number');
        $this->db->from('mod_jobs');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        
        return $this->db->get();
    }

    /**
     * Get agency agents by agency ID
     */
    public function get_agency_agents_by_agency($agency_id) {
        if (empty($agency_id)) {
            return false;
        }
        
        // Try to get from agency_staff table first
        $this->db->select('id, first_name, last_name, email');
        $this->db->from('agency_staff');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('first_name', 'ASC');
        
        $result = $this->db->get();
        
        // If no agency_staff found, try recruiters table
        if ($result->num_rows() === 0) {
            $this->db->select('id, first_name, last_name, email');
            $this->db->from('recruiters');
            $this->db->where('agency_id', $agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $this->db->order_by('first_name', 'ASC');
            
            $result = $this->db->get();
        }
        
        return $result;
    }

    /**
     * Is Unique Email - WITH AGENCY FILTER
     */
    public function is_unique_email($email, $id = ""){
        $agency_id = $this->get_current_agency_id();
        
        $this->db->where('email', $email);
        $this->db->where('enabled', 1);
        
        // Only check uniqueness within the same agency
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id);
        }
        
        if (!empty($id)) {
            $this->db->where('id !=', $id);
        }

        $query = $this->db->get($this->table);
        return $query->num_rows() == 0;
    }

/**
 * Get candidate details with all onboarding fields
 */
public function get_candidate_details($candidateId) {
    if (!$candidateId) return null;
    
    try {
        $agency_id = $this->get_current_agency_id();
        
        $this->db->select('c.*, 
                          c.stage_documents_decision, 
                          c.stage_documents_decision_at, 
                          c.documents_required, 
                          c.documents_notes');
        $this->db->from('candidates c');
        $this->db->where('c.id', $candidateId);
        $this->db->where('c.enabled', 1);
        $this->db->where('c.removed', 0);
        
        // ✅ Check both primary agency AND pivot table assignments
        if ($agency_id) {
            $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
            $this->db->where('ca.agency_id', $agency_id);
        }
        
        return $this->db->get()->row();
    } catch (Exception $e) {
        error_log('Error in get_candidate_details: ' . $e->getMessage());
        return null;
    }
}

    public function generate_reference_number(){
        try {
            $prefix = 'CAND';
            $agency_id = $this->get_current_agency_id();
            
            $this->db->select('COUNT(*) as total');
            $this->db->from('candidates');
            $this->db->where('YEAR(created_at)', date('Y'));
            $this->db->where('enabled', 1);
            
            // Count only within current agency
            if ($agency_id) {
                $this->db->where('agency_id', $agency_id);
            }
            
            $result = $this->db->get()->row();
            
            $sequence = ($result->total ?? 0) + 1;
            return $prefix . '-' . date('Y') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            error_log('Error in generate_reference_number: ' . $e->getMessage());
            return $prefix . '-' . date('Y') . '-0001';
        }
    }

    public function log_candidate_activity($logData){
        try {
            if ($this->db->table_exists('candidate_activity_logs')) {
                return $this->db->insert('candidate_activity_logs', $logData);
            }
            return false;
        } catch (Exception $e) {
            error_log('Error in log_candidate_activity: ' . $e->getMessage());
            return false;
        }
    }

    public function create_agent_notification($notificationData){
        try {
            if ($this->db->table_exists('agent_notifications')) {
                return $this->db->insert('agent_notifications', $notificationData);
            }
            return false;
        } catch (Exception $e) {
            error_log('Error in create_agent_notification: ' . $e->getMessage());
            return false;
        }
    }

    public function get_agent($agentId){
        try {
            $agency_id = $this->get_current_agency_id();
            
            $this->db->select('*');
            $this->db->from('agency_staff');
            $this->db->where('id', $agentId);
            $this->db->where('enabled', 1);
            
            // Only allow access to agents from current agency
            if ($agency_id) {
                $this->db->where('agency_id', $agency_id);
            }
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            error_log('Error in get_agent: ' . $e->getMessage());
            return null;
        }
    }
    
  /**
 * Check documents submission status (AGENCY side - read only)
 */
public function check_documents_submission_status($candidate_id) {
    $required_documents = $this->get_required_documents($candidate_id);
    
    return [
        'has_documents' => !empty($required_documents),
        'document_count' => count($required_documents),
        'documents' => $required_documents
    ];
}

/**
 * Get required documents for candidate (AGENCY side - read only)
 */
public function get_required_documents($candidate_id) {
    // Verify agency has access to this candidate
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

    log_message('debug', "Querying ALL documents for candidate {$candidate_id}");

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
    
    log_message('debug', "Found " . count($result) . " total documents for candidate {$candidate_id}");
    
    // Log each document found
    foreach ($result as $doc) {
        log_message('debug', "Document: ID={$doc->id}, Name='{$doc->document_name}', Type='{$doc->document_type}', Created='{$doc->created_at}'");
    }
    
    return $result;
}
/**
 * Check if agency has access to candidate
 */
private function check_agency_candidate_access($agency_id, $candidate_id) {
    $this->db->select('1')
             ->from('candidate_agencies')
             ->where('candidate_id', $candidate_id)
             ->where('agency_id', $agency_id);
    
    return $this->db->get()->row() !== null;
}

// ✅ ADD THESE TO Model_candidates.php

/**
 * Update HM decision - FIXED VERSION
 */
public function update_hm_decision($candidate_id, $decision, $notes = null)
{
    $update_data = array(
        'hm_decision' => $decision,
        'hm_decision_notes' => $notes,
        'hm_decision_by' => $this->get_current_agency_id(),
        'hm_decision_at' => date('Y-m-d H:i:s'),
        'stage_hm_decision' => 1, // Mark the stage as complete
        'stage_hm_decision_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    );

    $result = $this->db->where('id', $candidate_id)->update($this->table, $update_data);

    if ($result) {
        // ✅ CRITICAL: Update onboarding progress
        $this->update_onboarding_progress($candidate_id);
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
        
        if (!empty($agency_user['agency_id'])) {
            return $agency_user['agency_id'];
        } elseif (!empty($agency_user['id'])) {
            return $agency_user['id'];
        }
    }
    
    return null;
}
    /**
     * Get single document
     */
    public function get_document($document_id)
    {
        return $this->db->where('id', $document_id)
                        ->where('removed', 0)
                        ->get('candidate_documents')
                        ->row();
    }

    /**
     * Delete candidate document (soft delete)
     */
    public function delete_candidate_document($document_id)
    {
        return $this->db->where('id', $document_id)
                        ->update('candidate_documents', [
                            'removed' => 1,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
    }

    /**
     * Check if recruiter has access to candidate
     */
    public function check_recruiter_candidate_access($recruiter_id, $candidate_id)
    {
        // Get recruiter's agency
        $recruiter = $this->db->select('agency_id')
                             ->from('recruiters')
                             ->where('id', $recruiter_id)
                             ->where('enabled', 1)
                             ->where('removed', 0)
                             ->get()
                             ->row();
        
        if (!$recruiter) {
            return false;
        }

        // Check if candidate is associated with recruiter's agency
        $this->db->select('1')
                 ->from('candidate_agencies')
                 ->where('candidate_id', $candidate_id)
                 ->where('agency_id', $recruiter->agency_id);
        
        return $this->db->get()->row() !== null;
    }

    /**
     * Get candidate details
     */
    public function get_candidate($id)
    {
        $this->db->select('c.*, j.name as job_name, j.reference_number as job_ref');
        $this->db->from('candidates c');
        $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
        $this->db->where('c.id', $id);
        $this->db->where('c.removed', 0);
        
        return $this->db->get()->row();
    }

    /**
     * Main query for candidates listing
     */
    public function main_selects()
    {
        $this->db->select('candidates.*');
        $this->db->select('mod_jobs.name as job_name');
    }

    public function main_joins()
    {
        $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
    }

    public function main_wheres()
    {
        // Filter by recruiter's agency
        $login_data = $this->session->userdata('login');
        if (!empty($login_data['recruiter'])) {
            $recruiter = $login_data['recruiter'];
            $agency_id = $recruiter['agency_id'] ?? $recruiter['id'];
            
            $this->db->join('candidate_agencies ca', 'ca.candidate_id = candidates.id', 'inner');
            $this->db->where('ca.agency_id', $agency_id);
        }
        
        $this->db->where('candidates.removed', 0);
    }

    public function main_sorting()
    {
        $this->db->order_by('candidates.created_at', 'DESC');
    }

    /**
 * Complete onboarding process
 */
public function complete_onboarding($candidate_id) {
    $update_data = array(
        'onboarding_stage' => 'completed',
        'onboarding_progress' => 100,
        'onboarding_completed_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    );

    return $this->db->where('id', $candidate_id)->update($this->table, $update_data);
}
}