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
     * Main query for notifications listing - CRUD methods
     */
    public function main_selects()
    {
        // Include enabled and removed columns with default values
        $this->db->select('notifications.*, 1 as enabled, 0 as removed');
        $this->db->select('c.first_name, c.last_name, c.email');
        $this->db->select('j.name as job_name');
        $this->db->select('r.first_name as recruiter_first_name, r.last_name as recruiter_last_name');
    }

    public function main_joins()
    {
        $this->db->join('candidates c', 'c.id = notifications.related_entity_id AND notifications.related_entity = "candidate"', 'left');
        
        // Join to get the job through candidate_jobs pivot table
        $this->db->join('candidate_jobs cj', 'cj.candidate_id = c.id', 'left');
        $this->db->join('mod_jobs j', 'j.id = cj.job_id', 'left');
        
        // Join to get recruiter info
        $this->db->join('recruiters r', 'r.id = notifications.sender_id AND notifications.sender_type = "recruiter"', 'left');
    }

    public function main_wheres()
    {
        $login_data = $this->session->userdata('login');
        $agency_id = $login_data['agency']['id'] ?? 0;
        
        $this->db->where('notifications.receiver_type', 'agency');
        $this->db->where('notifications.receiver_id', $agency_id);
    }

    public function main_sorting()
    {
        $this->db->order_by('notifications.is_read', 'ASC');
        $this->db->order_by('notifications.created_at', 'DESC');
    }

    /**
     * Get notifications for agency
     */
    public function get_agency_notifications($agency_id, $limit = null, $offset = null)
    {
        $this->db->select('notifications.*, 1 as enabled, 0 as removed, c.first_name, c.last_name, c.email, j.name as job_name, r.first_name as recruiter_first_name, r.last_name as recruiter_last_name');
        $this->db->from('notifications');
        $this->db->join('candidates c', 'c.id = notifications.related_entity_id AND notifications.related_entity = "candidate"', 'left');
        
        // Join to get the job through candidate_jobs pivot table
        $this->db->join('candidate_jobs cj', 'cj.candidate_id = c.id', 'left');
        $this->db->join('mod_jobs j', 'j.id = cj.job_id', 'left');
        
        // Join to get recruiter info
        $this->db->join('recruiters r', 'r.id = notifications.sender_id AND notifications.sender_type = "recruiter"', 'left');
        
        $this->db->where('notifications.receiver_type', 'agency');
        $this->db->where('notifications.receiver_id', $agency_id);
        $this->db->order_by('notifications.created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get();
    }

    /**
     * Get unread notification count for agency
     */
    public function get_unread_count($agency_id)
    {
        $this->db->where('receiver_type', 'agency');
        $this->db->where('receiver_id', $agency_id);
        $this->db->where('is_read', 0);
        return $this->db->count_all_results('notifications');
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read($notification_id, $agency_id)
    {
        $this->db->where('id', $notification_id);
        $this->db->where('receiver_type', 'agency');
        $this->db->where('receiver_id', $agency_id);
        $this->db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Mark all notifications as read for agency
     */
    public function mark_all_as_read($agency_id)
    {
        $this->db->where('receiver_type', 'agency');
        $this->db->where('receiver_id', $agency_id);
        $this->db->where('is_read', 0);
        $this->db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Create candidate submission notification for agency
     */
    public function create_candidate_submission_notification($candidate_id, $agency_id, $recruiter_id, $job_id = null)
    {
        $candidate = $this->db->where('id', $candidate_id)->get('candidates')->row();
        $recruiter = $this->db->where('id', $recruiter_id)->get('recruiters')->row();
        
        // Get job name - try multiple ways to find the job
        $job_name = 'Multiple Jobs';
        if ($job_id) {
            $job = $this->db->where('id', $job_id)->get('mod_jobs')->row();
            if ($job) {
                $job_name = $job->name;
            }
        } else {
            // Try to get job from candidate_jobs table
            $this->db->select('j.name');
            $this->db->from('candidate_jobs cj');
            $this->db->join('mod_jobs j', 'j.id = cj.job_id');
            $this->db->where('cj.candidate_id', $candidate_id);
            $this->db->limit(1);
            $job_row = $this->db->get()->row();
            if ($job_row) {
                $job_name = $job_row->name;
            }
        }
        
        // Get recruiter name
        $recruiter_name = '';
        if ($recruiter) {
            if (!empty($recruiter->first_name)) {
                $recruiter_name = $recruiter->first_name . ' ' . $recruiter->last_name;
            } else {
                $recruiter_name = 'A Recruiter';
            }
        }
        
        $notification_data = [
            'title' => 'New Candidate Submission',
            'message' => "Recruiter {$recruiter_name} submitted candidate {$candidate->first_name} {$candidate->last_name} for job: {$job_name}",
            'type' => 'candidate_applied',
            'sender_type' => 'recruiter',
            'sender_id' => $recruiter_id,
            'receiver_type' => 'agency',
            'receiver_id' => $agency_id,
            'related_entity' => 'candidate',
            'related_entity_id' => $candidate_id,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('notifications', $notification_data);
    }

    /**
     * Override CRUD methods to prevent enable/disable actions
     */
    public function enable($whereValue, $whereField = 'id', $table = false)
    {
        // Notifications don't have enable/disable functionality
        return true;
    }

    public function disable($whereValue, $whereField = 'id', $table = false)
    {
        // Notifications don't have enable/disable functionality
        return true;
    }

    public function remove($whereValue, $whereField = 'id', $table = false)
    {
        // Instead of deleting, mark as read or use soft delete
        $this->db->where($whereField, $whereValue);
        return $this->db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Compatibility methods for Dashboard
     */
    public function get_notifications_for_user($user_type, $user_id, $unread_only = false, $limit = 5)
    {
        if ($user_type !== 'agency') {
            return [];
        }
        
        $this->db->where('receiver_type', 'agency')
                 ->where('receiver_id', $user_id)
                 ->order_by('created_at', 'DESC');
        
        if ($unread_only) {
            $this->db->where('is_read', 0);
        }
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get($this->table)->result();
    }

    public function count_unread_notifications_for_user($user_type, $user_id)
    {
        if ($user_type !== 'agency') {
            return 0;
        }
        
        return $this->db->where('receiver_type', 'agency')
                        ->where('receiver_id', $user_id)
                        ->where('is_read', 0)
                        ->count_all_results($this->table);
    }

    public function get_all_notifications_agency($agency_id)
    {
        return $this->get_agency_notifications($agency_id)->result();
    }

    public function mark_as_read_agency($notification_id, $agency_id)
    {
        return $this->mark_as_read($notification_id, $agency_id);
    }

    public function mark_all_as_read_agency($agency_id)
    {
        return $this->mark_all_as_read($agency_id);
    }
}