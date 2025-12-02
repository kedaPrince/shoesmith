<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Model_chat_messages extends CRUD_Model 
{
    public $table = 'chat_conversations';
    public $pageName = 'chat';
    
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get conversations for agency
     */
    public function get_agency_conversations($agency_id, $limit = null, $offset = null)
    {
        $this->db->select('c.*, CONCAT(r.first_name, " ", r.last_name) as recruiter_name, 
                          j.name as job_name, cand.first_name, cand.last_name,
                          (SELECT COUNT(*) FROM chat_messages cm 
                           WHERE cm.conversation_id = c.id AND cm.is_read = 0 
                           AND cm.sender_type = "recruiter") as unread_count,
                          last_msg.message as last_message,
                          last_msg.created_at as last_message_at');
        $this->db->from('chat_conversations c');
        $this->db->join('recruiters r', 'r.id = c.recruiter_id', 'left');
        $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
        $this->db->join('candidates cand', 'cand.id = c.candidate_id', 'left');
        $this->db->join('(SELECT conversation_id, message, created_at 
                         FROM chat_messages 
                         WHERE id IN (SELECT MAX(id) FROM chat_messages GROUP BY conversation_id)
                        ) last_msg', 'last_msg.conversation_id = c.id', 'left');
        
        $this->db->where('c.agency_id', $agency_id);
        $this->db->where('c.enabled', 1);
        $this->db->where('c.removed', 0);
        $this->db->order_by('c.last_message_at', 'DESC');
        $this->db->order_by('c.created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Get conversation by ID with access check for agency
     */
    public function get_conversation_for_agency($conversation_id, $agency_id)
{
    // ADD DEBUGGING
    log_message('debug', 'get_conversation_for_agency called: conversation=' . $conversation_id . ', agency=' . $agency_id);
    
    $this->db->select('cc.*, 
                      a.name as agency_name, 
                      CONCAT(r.first_name, " ", r.last_name) as recruiter_name,
                      j.name as job_name,
                      CONCAT(c.first_name, " ", c.last_name) as candidate_name');
    $this->db->from('chat_conversations cc');
    $this->db->join('agencies a', 'a.id = cc.agency_id', 'left');
    $this->db->join('recruiters r', 'r.id = cc.recruiter_id', 'left');
    $this->db->join('mod_jobs j', 'j.id = cc.job_id', 'left');
    $this->db->join('candidates c', 'c.id = cc.candidate_id', 'left');
    $this->db->where('cc.id', $conversation_id);
    $this->db->where('cc.agency_id', $agency_id);
    $this->db->where('cc.enabled', 1);
    $this->db->where('cc.removed', 0);
    
    $result = $this->db->get()->row();
    
    // ADD DEBUGGING
    log_message('debug', 'Query result: ' . ($result ? 'FOUND' : 'NOT FOUND'));
    if ($result) {
        log_message('debug', 'Result agency_id: ' . $result->agency_id);
    }
    
    return $result;
}

    /**
 * Get or create conversation - WITH UUID FIX
 */
public function get_or_create_conversation($agency_id, $recruiter_id, $job_id = null, $candidate_id = null)
{
    // Check if conversation already exists
    $this->db->where('agency_id', $agency_id);
    $this->db->where('recruiter_id', $recruiter_id);
    $this->db->where('job_id', $job_id);
    $this->db->where('candidate_id', $candidate_id);
    $this->db->where('enabled', 1);
    $this->db->where('removed', 0);
    
    $conversation = $this->db->get('chat_conversations')->row();
    
    if ($conversation) {
        return $conversation;
    }
    
    // Generate UUID FIRST
    $uuid = $this->generate_uuid();
    
    // Check if this UUID already exists (unlikely but possible)
    $attempts = 0;
    while ($this->uuid_exists($uuid) && $attempts < 5) {
        $uuid = $this->generate_uuid();
        $attempts++;
    }
    
    // Create new conversation WITH UUID
    $conversation_data = [
        'agency_id' => $agency_id,
        'recruiter_id' => $recruiter_id,
        'job_id' => $job_id,
        'candidate_id' => $candidate_id,
        'uuid' => $uuid, // ADD THIS
        'title' => $this->generate_conversation_title($agency_id, $recruiter_id, $job_id, $candidate_id),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'enabled' => 1
    ];
    
    $this->db->insert('chat_conversations', $conversation_data);
    
    if ($this->db->error()['code']) {
        log_message('error', 'Failed to create conversation: ' . $this->db->error()['message']);
        // If still duplicate, try one more time with new UUID
        if ($this->db->error()['code'] == 1062) {
            $conversation_data['uuid'] = $this->generate_uuid();
            $this->db->insert('chat_conversations', $conversation_data);
        }
    }
    
    $conversation_id = $this->db->insert_id();
    
    return $this->db->where('id', $conversation_id)->get('chat_conversations')->row();
}

/**
 * Generate a proper UUID v4
 */
private function generate_uuid()
{
    // Generate proper UUID v4
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
/**
 * Get conversation by UUID with access check for agency
 */
public function get_conversation_for_agency_by_uuid($uuid, $agency_id)
{
    $this->db->select('cc.*, 
                      a.name as agency_name, 
                      CONCAT(r.first_name, " ", r.last_name) as recruiter_name,
                      j.name as job_name,
                      CONCAT(c.first_name, " ", c.last_name) as candidate_name');
    $this->db->from('chat_conversations cc');
    $this->db->join('agencies a', 'a.id = cc.agency_id', 'left');
    $this->db->join('recruiters r', 'r.id = cc.recruiter_id', 'left');
    $this->db->join('mod_jobs j', 'j.id = cc.job_id', 'left');
    $this->db->join('candidates c', 'c.id = cc.candidate_id', 'left');
    $this->db->where('cc.uuid', $uuid); // Use UUID instead of ID
    $this->db->where('cc.agency_id', $agency_id);
    $this->db->where('cc.enabled', 1);
    $this->db->where('cc.removed', 0);
    
    return $this->db->get()->row();
}
/**
 * Check if UUID already exists in database
 */
private function uuid_exists($uuid)
{
    $this->db->where('uuid', $uuid);
    $this->db->from('chat_conversations');
    return $this->db->count_all_results() > 0;
}

    /**
     * Generate conversation title
     */
    private function generate_conversation_title($agency_id, $recruiter_id, $job_id, $candidate_id)
    {
        $title_parts = [];
        
        if ($job_id) {
            $job = $this->db->select('name')->from('mod_jobs')->where('id', $job_id)->get()->row();
            if ($job) {
                $title_parts[] = $job->name;
            }
        }
        
        if ($candidate_id) {
            $candidate = $this->db->select('first_name, last_name')->from('candidates')->where('id', $candidate_id)->get()->row();
            if ($candidate) {
                $title_parts[] = $candidate->first_name . ' ' . $candidate->last_name;
            }
        }
        
        if (empty($title_parts)) {
            // Get recruiter name for general conversation
            $recruiter = $this->db->select('first_name, last_name')->from('recruiters')->where('id', $recruiter_id)->get()->row();
            if ($recruiter) {
                $title_parts[] = $recruiter->first_name . ' ' . $recruiter->last_name;
            }
        }
        
        return implode(' - ', $title_parts) ?: 'General Conversation';
    }

    /**
     * Get messages for conversation
     */
    public function get_conversation_messages($conversation_id, $limit = 50, $offset = 0)
    {
        $this->db->select('cm.*, 
                          CASE 
                              WHEN cm.sender_type = "agency" THEN a.name
                              WHEN cm.sender_type = "recruiter" THEN CONCAT(r.first_name, " ", r.last_name)
                          END as sender_name');
        $this->db->from('chat_messages cm');
        $this->db->join('agencies a', 'a.id = cm.sender_id AND cm.sender_type = "agency"', 'left');
        $this->db->join('recruiters r', 'r.id = cm.sender_id AND cm.sender_type = "recruiter"', 'left');
        $this->db->where('cm.conversation_id', $conversation_id);
        $this->db->where('cm.enabled', 1);
        $this->db->where('cm.removed', 0);
        $this->db->order_by('cm.created_at', 'ASC');
        $this->db->limit($limit, $offset);
        
        return $this->db->get()->result();
    }

    /**
     * Send message
     */
    public function send_message($conversation_id, $sender_type, $sender_id, $message, $message_type = 'text', $file_data = null)
    {
        $message_data = [
            'conversation_id' => $conversation_id,
            'sender_type' => $sender_type,
            'sender_id' => $sender_id,
            'message' => $message,
            'message_type' => $message_type,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'enabled' => 1
        ];
        
        if ($file_data) {
            $message_data['file_name'] = $file_data['file_name'];
            $message_data['file_path'] = $file_data['file_path'];
            $message_data['file_size'] = $file_data['file_size'];
        }
        
        $this->db->insert('chat_messages', $message_data);
        $message_id = $this->db->insert_id();
        
        // Update conversation last message time
        $this->db->where('id', $conversation_id)
                 ->update('chat_conversations', [
                     'last_message_at' => date('Y-m-d H:i:s'),
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);
        
        return $message_id;
    }

    /**
     * Mark messages as read
     */
    public function mark_messages_as_read($conversation_id, $reader_type)
    {
        $this->db->where('conversation_id', $conversation_id);
        $this->db->where('sender_type !=', $reader_type); // Only mark messages from other user as read
        $this->db->where('is_read', 0);
        $this->db->update('chat_messages', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->db->affected_rows();
    }

    /**
     * Get unread message count for agency
     */
    public function get_unread_count_for_agency($agency_id)
    {
        $this->db->select('COUNT(*) as unread_count');
        $this->db->from('chat_messages cm');
        $this->db->join('chat_conversations cc', 'cc.id = cm.conversation_id');
        $this->db->where('cc.agency_id', $agency_id);
        $this->db->where('cm.sender_type', 'recruiter');  // Messages from recruiters are unread for agency
        $this->db->where('cm.is_read', 0);
        $this->db->where('cm.enabled', 1);
        $this->db->where('cm.removed', 0);
        $this->db->where('cc.enabled', 1);
        $this->db->where('cc.removed', 0);
        
        $result = $this->db->get()->row();
        return $result ? $result->unread_count : 0;
    }

    /**
     * Get recruiters that agency can chat with
     */
    public function get_available_recruiters($agency_id)
    {
        // Get recruiters from the same agency
        $this->db->select('r.id, r.first_name, r.last_name, r.email, r.profile_pic')
                ->from('recruiters r')
                ->where('r.agency_id', $agency_id) // Only show recruiters from the same agency
                ->where('r.enabled', 1)
                ->where('r.removed', 0)
                ->order_by('r.first_name', 'ASC');
        
        return $this->db->get()->result();
    }


   /**
     * Create chat notification for your existing table structure
     */
    public function create_chat_notification($conversation_id, $recipient_id, $recipient_type, $message, $sender_id)
    {
        // Determine sender and receiver types based on your table structure
        $sender_type = ($recipient_type === 'recruiter') ? 'agency' : 'recruiter';
        $receiver_type = $recipient_type; // 'agency' or 'recruiter'
        
        $notification_data = [
            'title' => 'New Chat Message',
            'message' => $this->truncate_message($message),
            'type' => 'chat', // CHANGED: Use 'chat' type to separate from system notifications
            'sender_type' => $sender_type, // 'agency' or 'recruiter'
            'sender_id' => $sender_id,
            'receiver_type' => $receiver_type, // 'agency' or 'recruiter'
            'receiver_id' => $recipient_id,
            'related_entity' => 'chat_conversation', // More specific entity type
            'related_entity_id' => $conversation_id,
            'metadata' => json_encode([
                'conversation_id' => $conversation_id,
                'message_preview' => $this->truncate_message($message, 50),
                'is_chat_notification' => true,
                'sender_type' => $sender_type
            ]),
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'enabled' => 1
        ];

        $this->db->insert('notifications', $notification_data);
        return $this->db->insert_id();
    }

    /**
     * Truncate long messages for notifications
     */
    private function truncate_message($message, $max_length = 100)
    {
        if (strlen($message) > $max_length) {
            return substr($message, 0, $max_length) . '...';
        }
        return $message;
    }

    /**
     * Get unread chat notifications for user
     */
    public function get_unread_chat_notifications($user_id, $user_type)
    {
        $this->db->select('*')
                 ->from('notifications')
                 ->where('receiver_id', $user_id)
                 ->where('receiver_type', $user_type)
                 ->where('is_read', 0)
                 ->where('enabled', 1)
                 ->where('removed', 0)
                 ->like('metadata', 'is_chat_notification') // Filter chat notifications
                 ->order_by('created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Mark chat notifications as read for a conversation
     */
    public function mark_chat_notifications_read($conversation_id, $user_id, $user_type)
    {
        $this->db->where('receiver_id', $user_id)
                 ->where('receiver_type', $user_type)
                 ->where('related_entity_id', $conversation_id)
                 ->where('is_read', 0)
                 ->where('enabled', 1)
                 ->where('removed', 0);
        
        $this->db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->db->affected_rows();
    }

        /**
     * Get recruiter details for right sidebar
     */
    public function get_recruiter_details($recruiter_id)
    {
        $this->db->select('r.*, a.name as agency_name, 
                          COUNT(DISTINCT j.id) as active_jobs,
                          (SELECT COUNT(*) FROM chat_conversations cc 
                           WHERE cc.recruiter_id = r.id AND cc.enabled = 1) as total_conversations');
        $this->db->from('recruiters r');
        $this->db->join('agencies a', 'a.id = r.agency_id', 'left');
        $this->db->join('mod_jobs j', 'j.recruiter_id = r.id AND j.enabled = 1 AND j.removed = 0', 'left');
        $this->db->where('r.id', $recruiter_id);
        $this->db->where('r.enabled', 1);
        $this->db->where('r.removed', 0);
        $this->db->group_by('r.id');
        
        return $this->db->get()->row();
    }

    /**
     * Get recruiter online status
     */
    public function get_recruiter_online_status($recruiter_id)
    {
        $this->db->select('last_activity_at, last_login');
        $this->db->from('recruiters');
        $this->db->where('id', $recruiter_id);
        $result = $this->db->get()->row();
        
        if (!$result) return false;
        
        // Consider online if active within last 5 minutes
        $last_activity = $result->last_activity_at ? strtotime($result->last_activity_at) : 0;
        $last_login = $result->last_login ? strtotime($result->last_login) : 0;
        $last_active = max($last_activity, $last_login);
        
        return (time() - $last_active) < 300; // 5 minutes
    }

    /**
     * Get total message count for agency
     */
    public function get_total_message_count($agency_id)
    {
        $this->db->select('COUNT(*) as total_count')
                 ->from('chat_messages cm')
                 ->join('chat_conversations cc', 'cc.id = cm.conversation_id')
                 ->where('cc.agency_id', $agency_id)
                 ->where('cm.enabled', 1)
                 ->where('cm.removed', 0)
                 ->where('cc.enabled', 1)
                 ->where('cc.removed', 0);
        
        $result = $this->db->get()->row();
        return $result ? $result->total_count : 0;
    }

    /**
     * Get recent notifications for agency
     */
    public function get_recent_notifications($agency_id, $user_type = 'agency')
    {
        $this->db->select('*')
                 ->from('notifications')
                 ->where('receiver_id', $agency_id)
                 ->where('receiver_type', $user_type)
                 ->where('enabled', 1)
                 ->where('removed', 0)
                 ->order_by('created_at', 'DESC')
                 ->limit(10);
        
        return $this->db->get()->result();
    }

}