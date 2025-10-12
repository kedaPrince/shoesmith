<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_notifications extends CRUD_Model {
    protected $table = 'notifications';

    public function __construct() {
        parent::__construct();
        log_message('debug', 'Model_notifications loaded');
    }

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


    public function create_job_notification($job_id, $agency_id, $sender_id, $type = 'job_added', $updated_fields = []) {
        log_message('debug', '=== NOTIFICATION CREATION START ===');
        log_message('debug', 'Creating notifications for Job: ' . $job_id . ', Agency: ' . $agency_id . ', Sender: ' . $sender_id . ', Type: ' . $type);
        
        if (!empty($updated_fields)) {
            log_message('debug', 'Updated fields: ' . implode(', ', $updated_fields));
        }
        
        // Get ALL recruiters for this agency
        $this->db->select('id, first_name, last_name, agency_id, usr_type_id');
        $this->db->from('recruiters');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $recruiters = $this->db->get()->result();
        
        log_message('debug', 'Found ' . count($recruiters) . ' recruiters for agency ' . $agency_id);
        
        if (empty($recruiters)) {
            log_message('debug', 'No recruiters found for agency: ' . $agency_id);
            return false;
        }

        // Get job details for the notification
        $job = $this->db->get_where('mod_jobs', ['id' => $job_id])->row();
        
        if (!$job) {
            log_message('debug', 'Job not found with ID: ' . $job_id);
            return false;
        }
        
        log_message('debug', 'Job found: ' . $job->name . ' (ID: ' . $job->id . ', Agency: ' . $job->agency_id . ')');

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
            $result = $this->db->insert_batch('notifications', $notifications);
            log_message('debug', 'Inserted ' . count($notifications) . ' notifications. Result: ' . ($result ? 'Success' : 'Failed'));
            
            if ($result) {
                log_message('debug', 'Last insert ID: ' . $this->db->insert_id());
                log_message('debug', 'Affected rows: ' . $this->db->affected_rows());
            }
            
            log_message('debug', '=== NOTIFICATION CREATION END ===');
            return $result;
        }

        log_message('debug', 'No notifications to insert');
        log_message('debug', '=== NOTIFICATION CREATION END ===');
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
     * Get all notifications for a recruiter with agency and job data
     */
    public function get_all_notifications($recruiter_id, $limit = null, $offset = null) {
        log_message('debug', 'Getting all notifications for recruiter: ' . $recruiter_id);
        
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
        
        $query = $this->db->get();
        $result = $query->result();
        
        log_message('debug', 'All notifications query: ' . $this->db->last_query());
        log_message('debug', 'Found ' . count($result) . ' total notifications for recruiter ' . $recruiter_id);
        
        // Debug: Check if we have update notifications
        foreach ($result as $notification) {
            if ($notification->type === 'job_updated') {
                log_message('debug', 'Found update notification ID: ' . $notification->id . ' with updated_fields: ' . $notification->updated_fields);
            }
        }
        
        return $result;
    }

    /**
     * Get unread notifications for a recruiter
     */
    public function get_unread_notifications($recruiter_id) {
        log_message('debug', 'Getting unread notifications for recruiter: ' . $recruiter_id);
        
        $this->db->select('n.*, a.name as agency_name, j.name as job_name, j.salary_min, j.salary_max, j.employment_type, j.department, j.is_remote');
        $this->db->from('notifications n');
        $this->db->join('agencies a', 'a.id = (SELECT agency_id FROM recruiters WHERE id = n.receiver_id)', 'left');
        $this->db->join('mod_jobs j', 'j.id = n.related_entity_id AND n.related_entity = "job"', 'left');
        $this->db->where('n.receiver_type', 'recruiter');
        $this->db->where('n.receiver_id', $recruiter_id);
        $this->db->where('n.is_read', 0);
        $this->db->order_by('n.created_at', 'DESC');
        
        $query = $this->db->get();
        $result = $query->result();
        
        log_message('debug', 'Unread notifications found: ' . count($result));
        return $result;
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read($notification_id, $recruiter_id) {
        $this->db->where('id', $notification_id);
        $this->db->where('receiver_type', 'recruiter');
        $this->db->where('receiver_id', $recruiter_id);
        
        return $this->db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark all notifications as read for a recruiter
     */
    public function mark_all_as_read($recruiter_id) {
        $this->db->where('receiver_type', 'recruiter');
        $this->db->where('receiver_id', $recruiter_id);
        $this->db->where('is_read', 0);
        
        return $this->db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Count unread notifications for a recruiter
     */
    public function count_unread_notifications($recruiter_id) {
        $this->db->where('receiver_type', 'recruiter');
        $this->db->where('receiver_id', $recruiter_id);
        $this->db->where('is_read', 0);
        $count = $this->db->count_all_results('notifications');
        
        log_message('debug', 'Unread count for recruiter ' . $recruiter_id . ': ' . $count);
        return $count;
    }
}