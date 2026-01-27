<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Model_notifications extends CRUD_model
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
     * Get job UUID by ID
     */
    public function get_job_uuid($job_id)
    {
        $this->db->select('uuid');
        $this->db->from('mod_jobs');
        $this->db->where('id', $job_id);
        $result = $this->db->get()->row();
        
        return $result ? $result->uuid : '';
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
     * Mark all notifications as read for recruiter (including chat notifications)
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
        
        $affected_rows = $this->db->affected_rows();
        
        return $affected_rows > 0;
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
     * Create candidate submission notification for agency
     */
    public function create_candidate_submission_notification($candidateData, $createdById)
    {
        try {
            // Get candidate details from the candidateData array
            $candidate_id = $candidateData['id'] ?? 0;
            
            // Load candidate details from database if not provided in array
            if ($candidate_id) {
                $this->db->select('first_name, last_name, reference_number, agency_id, job_id');
                $this->db->from('candidates');
                $this->db->where('id', $candidate_id);
                $candidate = $this->db->get()->row();
            } else {
                // Create dummy candidate object from provided data
                $candidate = (object)[
                    'first_name' => $candidateData['first_name'] ?? '',
                    'last_name' => $candidateData['last_name'] ?? '',
                    'reference_number' => $candidateData['reference_number'] ?? '',
                    'agency_id' => $candidateData['agency_id'] ?? 0,
                    'job_id' => $candidateData['job_id'] ?? null
                ];
            }
            
            if (!$candidate) {
                return false;
            }
            
            // Get recruiter details
            $this->db->select('first_name, last_name, agency_id');
            $this->db->from('recruiters');
            $this->db->where('id', $createdById);
            $recruiter = $this->db->get()->row();
            
            if (!$recruiter) {
                return false;
            }
            
            // Get job name
            $job_name = 'Multiple Jobs';
            if (!empty($candidate->job_id)) {
                $job = $this->db->where('id', $candidate->job_id)->get('mod_jobs')->row();
                if ($job) {
                    $job_name = $job->name;
                }
            }
            
            // Get recruiter name
            $recruiter_name = '';
            if ($recruiter) {
                $recruiter_name = $recruiter->first_name . ' ' . $recruiter->last_name;
            }
            
            // Get all agency staff for this agency to notify
            $this->db->select('id, first_name, last_name');
            $this->db->from('agency_staff');
            $this->db->where('agency_id', $candidate->agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $agency_users = $this->db->get()->result();
            
            if (empty($agency_users)) {
                return false;
            }
            
            $notifications_created = 0;
            
            foreach ($agency_users as $agency_user) {
                $notification_data = [
                    'title' => ' New Candidate Submission',
                    'message' => "Recruiter {$recruiter_name} submitted candidate {$candidate->first_name} {$candidate->last_name} ({$candidate->reference_number}) for job: {$job_name}",
                    'type' => 'candidate_applied',
                    'sender_type' => 'recruiter',
                    'sender_id' => $createdById,
                    'receiver_type' => 'agency',
                    'receiver_id' => $agency_user->id,
                    'related_entity' => 'candidate',
                    'related_entity_id' => $candidate_id,
                    'metadata' => json_encode([
                        'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                        'candidate_reference' => $candidate->reference_number,
                        'recruiter_name' => $recruiter_name,
                        'job_name' => $job_name,
                        'notification_type' => 'candidate_submission',
                        'action_required' => 'Review candidate submission',
                        'action_url' => site_url("agency/candidates/view/{$candidate_id}")
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
            
            return $notifications_created > 0;
            
        } catch (Exception $e) {
            // Log error if needed
            return false;
        }
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
                return false;
            }

            $notifications_created = 0;
            $decision_icon = $decision === 'accepted' ? '' : '';
            $decision_text = $decision === 'accepted' ? 'accepted' : 'rejected';

            foreach ($recruiters as $recruiter) {
                // Prepare metadata properly
                $metadata = [
                    'decision' => $decision,
                    'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                    'candidate_reference' => $candidate->reference_number,
                    'job_name' => $job_name,
                    'requesting_agency' => $requesting_agency_name,
                    'decision_notes' => $notes,
                    'notes' => $notes,
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
                    'metadata' => json_encode($metadata),
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

            return $notifications_created > 0;

        } catch (Exception $e) {
            return false;
        }
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
                return false;
            }

            // Get recruiter details
            $this->db->select('first_name, last_name, agency_id');
            $this->db->from('recruiters');
            $this->db->where('id', $uploaded_by_recruiter_id);
            $recruiter = $this->db->get()->row();

            if (!$recruiter) {
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
                return false;
            }

            $notifications_created = 0;

            foreach ($agency_users as $agency_user) {
                $notification_data = [
                    'title' => ' Documents Uploaded',
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

            return $notifications_created > 0;

        } catch (Exception $e) {
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
                return false;
            }

            // Get recruiter details
            $this->db->select('first_name, last_name, agency_id');
            $this->db->from('recruiters');
            $this->db->where('id', $recruiter_id);
            $recruiter = $this->db->get()->row();

            if (!$recruiter) {
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
                return false;
            }

            $notifications_created = 0;

            foreach ($agency_users as $agency_user) {
                // Prepare the message
                $message = "Recruiter {$recruiter->first_name} {$recruiter->last_name} has submitted {$document_count} required document(s) for candidate {$candidate->first_name} {$candidate->last_name} ({$candidate->reference_number}) for position: {$job_name}.";
                
                if (!empty($submission_notes)) {
                    $message .= "\n\nRecruiter's Notes: " . $submission_notes;
                }

                $notification_data = [
                    'title' => ' Required Documents Submitted',
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

            return $notifications_created > 0;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Mark chat notifications as read for specific conversation
     */
    public function mark_chat_notifications_read($conversation_id, $user_id, $user_type)
    {
        
        // Update chat notifications for this user and conversation
        $this->db->where('receiver_id', $user_id);
        $this->db->where('receiver_type', $user_type);
        $this->db->where('type', 'chat');
        $this->db->where('is_read', 0);
        
        // Also filter by conversation_id in metadata
        $this->db->where("JSON_EXTRACT(metadata, '$.conversation_id') =", $conversation_id);
        
        $update_data = [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->update('notifications', $update_data);
        $affected_rows = $this->db->affected_rows();
        
        
        return $affected_rows;
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
                return false;
            }

            $notifications_created = 0;

            foreach ($recruiters as $recruiter) {
                $notification_data = [
                    'title' => ' Additional Documents Required',
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
                        'notes' => $required_documents_notes,
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

            return $notifications_created > 0;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get candidate's job assignments
     */
    public function get_candidate_job_assignments($candidate_id)
    {
        $this->db->select('cj.job_id, j.name as job_name, j.reference_number as job_reference');
        $this->db->from('candidate_jobs cj');
        $this->db->join('mod_jobs j', 'j.id = cj.job_id');
        $this->db->where('cj.candidate_id', $candidate_id);
        $this->db->where('j.enabled', 1);
        $this->db->where('j.removed', 0);
        
        return $this->db->get()->result();
    }

    /**
     * Check if candidate is assigned to any jobs
     */
    public function has_job_assignments($candidate_id)
    {
        $this->db->from('candidate_jobs');
        $this->db->where('candidate_id', $candidate_id);
        return $this->db->count_all_results() > 0;
    }

    /**
     * Get only system notifications (exclude chat notifications)
     */
    public function get_system_notifications($recruiter_id, $limit = null)
    {
        $this->db->select('*')
                 ->from('notifications')
                 ->where('receiver_id', $recruiter_id)
                 ->where('receiver_type', 'recruiter')
                 ->where('is_read', 0)
                 ->where('enabled', 1)
                 ->where('removed', 0)
                 ->where('type !=', 'chat') // Exclude chat notifications
                 ->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get()->result();
    }

   /**
     * Count only system notifications (exclude chat notifications)
     */
    public function count_system_notifications($recruiter_id)
    {
        $this->db->select('COUNT(*) as unread_count')
                ->from('notifications')
                ->where('receiver_id', $recruiter_id)
                ->where('receiver_type', 'recruiter')
                ->where('is_read', 0)
                ->where('enabled', 1)
                ->where('removed', 0)
                ->where('type !=', 'chat'); // Exclude chat notifications
        
        $result = $this->db->get()->row();
        return $result ? $result->unread_count : 0;
    }

    /**
     * Count chat notifications specifically
     */
    public function count_chat_notifications($recruiter_id)
    {
        $this->db->select('COUNT(*) as unread_count')
                ->from('notifications')
                ->where('receiver_id', $recruiter_id)
                ->where('receiver_type', 'recruiter')
                ->where('is_read', 0)
                ->where('enabled', 1)
                ->where('removed', 0)
                ->where('type', 'chat'); // Only chat notifications
        
        $result = $this->db->get()->row();
        return $result ? $result->unread_count : 0;
    }

    /**
     * Create notification for contact info request
     */
    public function create_contact_request_notification($candidate_id, $agency_id, $request_id)
    {
        try {
            // Get candidate info
            $this->db->select('c.*, r.id as recruiter_id, r.first_name as recruiter_first_name, r.last_name as recruiter_last_name');
            $this->db->from('candidates c');
            $this->db->join('recruiters r', 'r.id = c.recruiter_id', 'left');
            $this->db->where('c.id', $candidate_id);
            $candidate = $this->db->get()->row();
            
            if (!$candidate) {
                return false;
            }
            
            // Get agency info
            $agency = $this->db->where('id', $agency_id)->get('agencies')->row();
            
            // Prepare notification
            $notification_data = [
                'title' => "Contact Info Request: {$candidate->first_name} {$candidate->last_name}",
                'message' => "Agency {$agency->name} has requested contact information for candidate {$candidate->first_name} {$candidate->last_name}",
                'type' => 'contact_request',
                'sender_type' => 'agency',
                'sender_id' => $agency_id,
                'receiver_type' => 'recruiter',
                'receiver_id' => $candidate->recruiter_id,
                'related_entity' => 'candidate',
                'related_entity_id' => $candidate_id,
                'metadata' => json_encode([
                    'candidate_id' => $candidate_id,
                    'candidate_name' => "{$candidate->first_name} {$candidate->last_name}",
                    'candidate_ref' => $candidate->reference_number,
                    'agency_id' => $agency_id,
                    'agency_name' => $agency->name,
                    'request_id' => $request_id,
                    'action_url' => site_url("recruiter/candidates/contact_requests/{$candidate_id}")
                ]),
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'removed' => 0,
                'enabled' => 1
            ];
            
            return $this->db->insert('notifications', $notification_data);
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create notification for contact info granted
     */
    public function create_contact_granted_notification($candidate_id, $agency_id, $recruiter_id, $notes = '')
    {
        try {
            // Get candidate info
            $candidate = $this->db->where('id', $candidate_id)->get('candidates')->row();
            if (!$candidate) {
                return false;
            }
            
            // Get agency info
            $agency = $this->db->where('id', $agency_id)->get('agencies')->row();
            
            // Get recruiter info
            $recruiter = $this->db->where('id', $recruiter_id)->get('recruiters')->row();
            
            // Prepare notification
            $notification_data = [
                'title' => "Contact Info Access Granted",
                'message' => "Recruiter {$recruiter->first_name} {$recruiter->last_name} has granted you access to contact information for {$candidate->first_name} {$candidate->last_name}",
                'type' => 'contact_granted',
                'sender_type' => 'recruiter',
                'sender_id' => $recruiter_id,
                'receiver_type' => 'agency',
                'receiver_id' => $agency_id,
                'related_entity' => 'candidate',
                'related_entity_id' => $candidate_id,
                'metadata' => json_encode([
                    'candidate_id' => $candidate_id,
                    'candidate_name' => "{$candidate->first_name} {$candidate->last_name}",
                    'candidate_ref' => $candidate->reference_number,
                    'recruiter_id' => $recruiter_id,
                    'recruiter_name' => "{$recruiter->first_name} {$recruiter->last_name}",
                    'notes' => $notes,
                    'action_url' => site_url("agency/candidates/view/{$candidate->uuid}")
                ]),
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'removed' => 0,
                'enabled' => 1
            ];
            
            return $this->db->insert('notifications', $notification_data);
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create notification for contact info denied
     */
    public function create_contact_denied_notification($candidate_id, $agency_id, $recruiter_id, $reason = '')
    {
        try {
            // Get candidate info
            $candidate = $this->db->where('id', $candidate_id)->get('candidates')->row();
            if (!$candidate) {
                return false;
            }
            
            // Get agency info
            $agency = $this->db->where('id', $agency_id)->get('agencies')->row();
            
            // Get recruiter info
            $recruiter = $this->db->where('id', $recruiter_id)->get('recruiters')->row();
            
            // Prepare notification
            $notification_data = [
                'title' => "Contact Info Access Denied",
                'message' => "Recruiter {$recruiter->first_name} {$recruiter->last_name} has denied your request for contact information for {$candidate->first_name} {$candidate->last_name}",
                'type' => 'contact_denied',
                'sender_type' => 'recruiter',
                'sender_id' => $recruiter_id,
                'receiver_type' => 'agency',
                'receiver_id' => $agency_id,
                'related_entity' => 'candidate',
                'related_entity_id' => $candidate_id,
                'metadata' => json_encode([
                    'candidate_id' => $candidate_id,
                    'candidate_name' => "{$candidate->first_name} {$candidate->last_name}",
                    'candidate_ref' => $candidate->reference_number,
                    'recruiter_id' => $recruiter_id,
                    'recruiter_name' => "{$recruiter->first_name} {$recruiter->last_name}",
                    'denial_reason' => $reason,
                    'action_url' => site_url("agency/candidates/view/{$candidate->uuid}")
                ]),
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'removed' => 0,
                'enabled' => 1
            ];
            
            return $this->db->insert('notifications', $notification_data);
            
        } catch (Exception $e) {
            return false;
        }
    }
    
}