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
     * Create HM Decision notification for recruiters
     */
    public function create_hm_decision_notification($candidate_id, $job_id, $agency_id, $decision, $notes = '', $sender_id = null)
    {
        // Get candidate details
        $candidate = $this->db->select('first_name, last_name, reference_number')
                             ->from('candidates')
                             ->where('id', $candidate_id)
                             ->get()
                             ->row();
        
        if (!$candidate) {
            return false;
        }

        // Get job details
        $job = $this->db->select('name, reference_number')
                       ->from('mod_jobs')
                       ->where('id', $job_id)
                       ->get()
                       ->row();

        // Get ALL recruiters for this agency
        $this->db->select('id, first_name, last_name, agency_id');
        $this->db->from('recruiters');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $recruiters = $this->db->get()->result();
        
        if (empty($recruiters)) {
            return false;
        }

        // Prepare notification content
        $notification_content = $this->prepare_hm_decision_content($decision, $candidate, $job, $notes);
        
        $notifications = [];
        foreach ($recruiters as $recruiter) {
            $notification_data = [
                'title' => $notification_content['title'],
                'message' => $notification_content['message'],
                'type' => 'hm_decision',
                'sender_type' => 'hiring_manager',
                'sender_id' => $sender_id,
                'receiver_type' => 'recruiter',
                'receiver_id' => $recruiter->id,
                'related_entity' => 'candidate',
                'related_entity_id' => $candidate_id,
                'metadata' => json_encode([
                    'decision' => $decision,
                    'notes' => $notes,
                    'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                    'candidate_reference' => $candidate->reference_number,
                    'job_name' => $job ? $job->name : 'Unknown Job',
                    'job_reference' => $job ? $job->reference_number : 'N/A'
                ]),
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


}