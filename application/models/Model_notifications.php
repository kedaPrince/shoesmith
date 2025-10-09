<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_notifications extends CI_Model
{
    protected $table = 'notifications';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Create a new notification
     */
    public function create($data)
    {
        $notification_data = [
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'system',
            'sender_type' => $data['sender_type'] ?? 'system',
            'sender_id' => $data['sender_id'] ?? null,
            'receiver_type' => $data['receiver_type'] ?? 'recruiter',
            'receiver_id' => $data['receiver_id'] ?? null,
            'related_entity' => $data['related_entity'] ?? null,
            'related_entity_id' => $data['related_entity_id'] ?? null,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table, $notification_data);
        return $this->db->insert_id();
    }

    /**
     * Get notifications for a receiver
     */
    public function get_for_receiver($receiver_type, $receiver_id, $limit = 10, $offset = 0, $unread_only = false)
    {
        $this->db->from($this->table);
        $this->db->where('receiver_type', $receiver_type);
        $this->db->where('receiver_id', $receiver_id);
        
        if ($unread_only) {
            $this->db->where('is_read', 0);
        }
        
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        return $this->db->get()->result();
    }

    /**
     * Get unread count for receiver
     */
    public function get_unread_count($receiver_type, $receiver_id)
    {
        $this->db->from($this->table);
        $this->db->where('receiver_type', $receiver_type);
        $this->db->where('receiver_id', $receiver_id);
        $this->db->where('is_read', 0);
        
        return $this->db->count_all_results();
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read($notification_id, $receiver_type, $receiver_id)
    {
        $this->db->where('id', $notification_id);
        $this->db->where('receiver_type', $receiver_type);
        $this->db->where('receiver_id', $receiver_id);
        
        return $this->db->update($this->table, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark all notifications as read for receiver
     */
    public function mark_all_as_read($receiver_type, $receiver_id)
    {
        $this->db->where('receiver_type', $receiver_type);
        $this->db->where('receiver_id', $receiver_id);
        $this->db->where('is_read', 0);
        
        return $this->db->update($this->table, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Create job added notification (for recruiters when agency adds job)
     */
    public function notify_job_added($job_id, $agency_id, $job_title)
    {
        // Get all recruiters to notify
        $recruiters = $this->db->get_where('recruiters', ['enabled' => 1, 'removed' => 0])->result();
        
        foreach ($recruiters as $recruiter) {
            $this->create([
                'title' => 'New Job Added by Agency',
                'message' => "Agency has added a new job: {$job_title}",
                'type' => 'job_added',
                'sender_type' => 'agency',
                'sender_id' => $agency_id,
                'receiver_type' => 'recruiter',
                'receiver_id' => $recruiter->id,
                'related_entity' => 'job',
                'related_entity_id' => $job_id
            ]);
        }
        
        return count($recruiters);
    }
}