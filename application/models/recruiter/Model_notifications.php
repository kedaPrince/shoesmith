<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Model_notifications extends CRUD_Model
{
    public $table = 'notifications';
    public $pageName = 'notifications';
    
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get unread notifications for recruiter
     */
    public function get_unread_notifications($recruiter_id)
    {
        $this->db->select('n.*, a.name as agency_name, j.name as job_name, j.salary_min, j.salary_max, j.employment_type, j.department, j.is_remote');
        $this->db->from('notifications n');
        $this->db->join('agencies a', 'a.id = (SELECT agency_id FROM recruiters WHERE id = n.receiver_id)', 'left');
        $this->db->join('mod_jobs j', 'j.id = n.related_entity_id AND n.related_entity = "job"', 'left');
        $this->db->where('n.receiver_type', 'recruiter');
        $this->db->where('n.receiver_id', $recruiter_id);
        $this->db->where('n.is_read', 0);
        $this->db->order_by('n.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Count unread notifications for recruiter
     */
    public function count_unread_notifications($recruiter_id)
    {
        $this->db->where('receiver_type', 'recruiter');
        $this->db->where('receiver_id', $recruiter_id);
        $this->db->where('is_read', 0);
        return $this->db->count_all_results('notifications');
    }

    /**
     * Get all notifications for recruiter
     */
    public function get_all_notifications($recruiter_id, $limit = null, $offset = null)
    {
        $this->db->select('n.*, a.name as agency_name, j.name as job_name, j.salary_min, j.salary_max, j.employment_type, j.department, j.is_remote');
        $this->db->from('notifications n');
        $this->db->join('agencies a', 'a.id = (SELECT agency_id FROM recruiters WHERE id = n.receiver_id)', 'left');
        $this->db->join('mod_jobs j', 'j.id = n.related_entity_id AND n.related_entity = "job"', 'left');
        $this->db->where('n.receiver_type', 'recruiter');
        $this->db->where('n.receiver_id', $recruiter_id);
        $this->db->order_by('n.created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Mark notification as read for recruiter
     */
    public function mark_as_read($notification_id, $recruiter_id)
    {
        $this->db->where('id', $notification_id);
        $this->db->where('receiver_type', 'recruiter');
        $this->db->where('receiver_id', $recruiter_id);
        $this->db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Mark all notifications as read for recruiter
     */
    public function mark_all_as_read($recruiter_id)
    {
        $this->db->where('receiver_type', 'recruiter');
        $this->db->where('receiver_id', $recruiter_id);
        $this->db->where('is_read', 0);
        $this->db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Get updated fields for job notifications
     */
    public function get_updated_fields($job_id, $recruiter_id) {
        $this->db->select('updated_fields');
        $this->db->from('notifications');
        $this->db->where('receiver_type', 'recruiter');
        $this->db->where('receiver_id', $recruiter_id);
        $this->db->where('related_entity', 'job');
        $this->db->where('related_entity_id', $job_id);
        $this->db->where('type', 'job_updated');
        $this->db->where('is_read', 0);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);
        
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $notification = $query->row();
            if (!empty($notification->updated_fields)) {
                $updated_fields = json_decode($notification->updated_fields, true);
                return is_array($updated_fields) ? $updated_fields : [];
            }
        }

        return [];
    }

    /**
     * Create job notification for recruiters
     */
    public function create_job_notification($job_id, $agency_id, $sender_id, $type = 'job_added', $updated_fields = []) {
        // Get ALL recruiters for this agency
        $this->db->select('id, first_name, last_name, agency_id, usr_type_id');
        $this->db->from('recruiters');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $recruiters = $this->db->get()->result();
        
        if (empty($recruiters)) {
            return false;
        }

        // Get job details for the notification
        $job = $this->db->get_where('mod_jobs', ['id' => $job_id])->row();
        
        if (!$job) {
            return false;
        }

        // Prepare notification content based on type
        $notification_content = $this->prepare_notification_content($type, $job, $updated_fields);
        
        $notifications = [];
        foreach ($recruiters as $recruiter) {
            $notification_data = [
                'title' => $notification_content['title'],
                'message' => $notification_content['message'],
                'type' => $type,
                'sender_type' => 'agency',
                'sender_id' => $sender_id,
                'receiver_type' => 'recruiter',
                'receiver_id' => $recruiter->id,
                'related_entity' => 'job',
                'related_entity_id' => $job_id,
                'updated_fields' => !empty($updated_fields) ? json_encode($updated_fields) : null,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $notifications[] = $notification_data;
        }

        // Insert all notifications
        if (!empty($notifications)) {
            return $this->db->insert_batch('notifications', $notifications);
        }

        return false;
    }

    /**
     * Prepare notification content based on type
     */
    private function prepare_notification_content($type, $job, $updated_fields = []) {
        $content = [
            'title' => '',
            'message' => ''
        ];

        switch ($type) {
            case 'job_updated':
                $content['title'] = 'Job Updated: ' . $job->name;
                
                if (!empty($updated_fields)) {
                    $field_labels = $this->get_field_labels();
                    $updated_list = [];
                    
                    foreach ($updated_fields as $field) {
                        $updated_list[] = $field_labels[$field] ?? $field;
                    }
                    
                    $content['message'] = 'The job "' . $job->name . '" has been updated. Changed fields: ' . implode(', ', $updated_list);
                } else {
                    $content['message'] = 'The job "' . $job->name . '" has been updated with general changes.';
                }
                break;

            case 'job_added':
            default:
                $content['title'] = 'New Job Posted: ' . $job->name;
                $content['message'] = 'A new job "' . $job->name . '" has been posted and is ready for review.';
                break;
        }

        return $content;
    }

    /**
     * Get human-readable field labels
     */
    private function get_field_labels() {
        return [
            'name' => 'Job Title',
            'reference_number' => 'Reference Number',
            'department' => 'Department',
            'employment_type' => 'Employment Type',
            'description' => 'Job Description',
            'project_overview' => 'Project Overview',
            'pay_rate' => 'Pay Rate',
            'salary_min' => 'Minimum Salary',
            'salary_max' => 'Maximum Salary',
            'roster' => 'Roster',
            'accommodation' => 'Accommodation',
            'transport' => 'Transport',
            'is_remote' => 'Remote Work',
            'industry_id' => 'Industry',
            'application_email' => 'Application Email',
            'application_url' => 'Application URL',
            'closing_date' => 'Closing Date'
        ];
    }

    /**
     * Create HM Decision notification for recruiters - FIXED VERSION
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
                // Prepare metadata properly
                $metadata = [
                    'decision' => $decision, // THIS WAS MISSING
                    'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                    'candidate_reference' => $candidate->reference_number,
                    'job_name' => $job_name,
                    'requesting_agency' => $requesting_agency_name,
                    'decision_notes' => $notes,
                    'notes' => $notes, // Add this for compatibility
                    'action_required' => $decision === 'accepted' ? 'Continue with onboarding process' : 'No further action required',
                    'notification_type' => 'hm_decision',
                    'action_url' => site_url("recruiter/candidates/view/{$candidate_id}")
                ];

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
                    'metadata' => json_encode($metadata), // Encode the full metadata array
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
     * Prepare HM Decision notification content
     */
    private function prepare_hm_decision_content($decision, $candidate, $job, $notes)
    {
        $candidate_name = $candidate->first_name . ' ' . $candidate->last_name;
        $job_name = $job ? $job->name : 'the position';
        
        if ($decision === 'accepted') {
            $title = "🎉 Candidate Accepted: {$candidate_name}";
            $message = "Great news! The hiring manager has accepted {$candidate_name} for {$job_name}.";
        } else {
            $title = "❌ Candidate Rejected: {$candidate_name}";
            $message = "The hiring manager has decided not to move forward with {$candidate_name} for {$job_name}.";
        }

        if (!empty($notes)) {
            $message .= "\n\n📝 Hiring Manager's Notes:\n" . $notes;
        }

        return [
            'title' => $title,
            'message' => $message
        ];
    }

    /**
     * Create notification for agency when documents are uploaded by recruiter
     */
    public function create_documents_uploaded_notification($candidate_id, $uploaded_by_recruiter_id, $document_count = 1) {
        try {
            // Get candidate details
            $this->db->select('first_name, last_name, reference_number');
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

            // Get all agency users for the recruiter's agency
            $this->db->select('id, first_name, last_name');
            $this->db->from('agency_staff');
            $this->db->where('agency_id', $recruiter->agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $agency_users = $this->db->get()->result();

            if (empty($agency_users)) {
                log_message('error', "No agency users found for agency {$recruiter->agency_id} to send documents uploaded notification");
                return false;
            }

            $notifications_created = 0;

            foreach ($agency_users as $agency_user) {
                $notification_data = [
                    'title' => '📄 Documents Uploaded',
                    'message' => "Recruiter {$recruiter->first_name} {$recruiter->last_name} has uploaded {$document_count} document(s) for candidate {$candidate->first_name} {$candidate->last_name} ({$candidate->reference_number}).",
                    'type' => 'documents_uploaded',
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
     * Get HM decision notifications for recruiter
     */
    public function get_hm_decision_notifications($recruiter_id, $limit = null)
    {
        $this->db->select('n.*, c.first_name, c.last_name, c.reference_number as candidate_ref, j.name as job_name, j.reference_number as job_ref');
        $this->db->from('notifications n');
        $this->db->join('candidates c', 'c.id = n.related_entity_id', 'left');
        $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
        $this->db->where('n.receiver_type', 'recruiter');
        $this->db->where('n.receiver_id', $recruiter_id);
        $this->db->where('n.type', 'hm_decision');
        $this->db->order_by('n.created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get()->result();
    }

    public function create_required_documents_submitted_notification($candidate_id, $recruiter_id, $document_count = 1, $submission_notes = '', $original_notification_id = null) {
        try {
            // Get candidate details
            $this->db->select('first_name, last_name, reference_number, agency_id, job_id');
            $this->db->from('candidates');
            $this->db->where('id', $candidate_id);
            $candidate = $this->db->get()->row();
            
            if (!$candidate) {
                log_message('error', "Candidate {$candidate_id} not found for required documents submitted notification");
                return false;
            }

            // Get recruiter details
            $this->db->select('first_name, last_name, agency_id');
            $this->db->from('recruiters');
            $this->db->where('id', $recruiter_id);
            $recruiter = $this->db->get()->row();

            if (!$recruiter) {
                log_message('error', "Recruiter {$recruiter_id} not found for required documents submitted notification");
                return false;
            }

            // Get job details
            $job_name = 'Unknown Job';
            if ($candidate->job_id) {
                $this->db->select('name');
                $this->db->from('mod_jobs');
                $this->db->where('id', $candidate->job_id);
                $job = $this->db->get()->row();
                if ($job) {
                    $job_name = $job->name;
                }
            }

            // Get all agency users for the candidate's agency
            $this->db->select('id, first_name, last_name');
            $this->db->from('agency_staff');
            $this->db->where('agency_id', $candidate->agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $agency_users = $this->db->get()->result();

            if (empty($agency_users)) {
                log_message('error', "No agency users found for agency {$candidate->agency_id} to send required documents submitted notification");
                return false;
            }

            $notifications_created = 0;

            foreach ($agency_users as $agency_user) {
                // Prepare the message
                $message = "Recruiter {$recruiter->first_name} {$recruiter->last_name} has submitted {$document_count} required document(s) for candidate {$candidate->first_name} {$candidate->last_name} ({$candidate->reference_number}) for position: {$job_name}.";
                
                if (!empty($submission_notes)) {
                    $message .= "\n\n📝 Recruiter's Notes: " . $submission_notes;
                }

                $notification_data = [
                    'title' => '📋 Required Documents Submitted',
                    'message' => $message,
                    'type' => 'required_documents_submitted',
                    'sender_type' => 'recruiter',
                    'sender_id' => $recruiter_id,
                    'receiver_type' => 'agency',
                    'receiver_id' => $agency_user->id,
                    'related_entity' => 'candidate',
                    'related_entity_id' => $candidate_id,
                    'metadata' => json_encode([
                        'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                        'candidate_reference' => $candidate->reference_number,
                        'recruiter_name' => $recruiter->first_name . ' ' . $recruiter->last_name,
                        'job_name' => $job_name,
                        'document_count' => $document_count,
                        'submission_notes' => $submission_notes,
                        'original_notification_id' => $original_notification_id,
                        'action_required' => 'Review the submitted required documents',
                        'notification_type' => 'required_documents_submitted',
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

            log_message('debug', "Created {$notifications_created} required documents submitted notifications for candidate {$candidate_id}");
            return $notifications_created > 0;

        } catch (Exception $e) {
            log_message('error', 'Error creating required documents submitted notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create notification for documents request from agency to recruiter
     */
    public function create_documents_request_notification($candidate_id, $agency_id, $requesting_user_id, $required_documents_notes, $job_id = null) {
        try {
            // Get candidate details
            $this->db->select('first_name, last_name, reference_number, agency_id');
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

            // Get all recruiters for the candidate's agency
            $this->db->select('id, first_name, last_name');
            $this->db->from('recruiters');
            $this->db->where('agency_id', $candidate->agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $recruiters = $this->db->get()->result();

            if (empty($recruiters)) {
                log_message('error', "No recruiters found for agency {$candidate->agency_id} to send documents request notification");
                return false;
            }

            $notifications_created = 0;

            foreach ($recruiters as $recruiter) {
                $notification_data = [
                    'title' => '📄 Additional Documents Required',
                    'message' => "The agency requires additional documents for candidate {$candidate->first_name} {$candidate->last_name} ({$candidate->reference_number}) for position: {$job_name}.\n\nRequired Documents: {$required_documents_notes}",
                    'type' => 'documents_request',
                    'sender_type' => 'agency',
                    'sender_id' => $requesting_user_id,
                    'receiver_type' => 'recruiter',
                    'receiver_id' => $recruiter->id,
                    'related_entity' => 'candidate',
                    'related_entity_id' => $candidate_id,
                    'metadata' => json_encode([
                        'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                        'candidate_reference' => $candidate->reference_number,
                        'job_name' => $job_name,
                        'required_documents' => $required_documents_notes,
                        'notes' => $required_documents_notes, // For compatibility
                        'action_required' => 'Upload the requested documents',
                        'notification_type' => 'documents_request',
                        'action_url' => site_url("recruiter/candidates/view/{$candidate_id}?tab=required")
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

            log_message('debug', "Created {$notifications_created} documents request notifications for candidate {$candidate_id}");
            return $notifications_created > 0;

        } catch (Exception $e) {
            log_message('error', 'Error creating documents request notification: ' . $e->getMessage());
            return false;
        }
    }

}