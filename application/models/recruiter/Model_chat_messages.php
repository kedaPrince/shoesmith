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
     * Get conversations for recruiter
     */
    public function get_recruiter_conversations($recruiter_id, $limit = null, $offset = null)
    {
        $this->db->select('c.*, a.name as agency_name, a.logo as agency_logo, 
                          j.name as job_name, cand.first_name, cand.last_name,
                          (SELECT COUNT(*) FROM chat_messages cm 
                           WHERE cm.conversation_id = c.id AND cm.is_read = 0 
                           AND cm.sender_type = "agency") as unread_count,
                          last_msg.message as last_message,
                          last_msg.created_at as last_message_at');
        $this->db->from('chat_conversations c');
        $this->db->join('agencies a', 'a.id = c.agency_id', 'left');
        $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
        $this->db->join('candidates cand', 'cand.id = c.candidate_id', 'left');
        $this->db->join('(SELECT conversation_id, message, created_at 
                         FROM chat_messages 
                         WHERE id IN (SELECT MAX(id) FROM chat_messages GROUP BY conversation_id)
                        ) last_msg', 'last_msg.conversation_id = c.id', 'left');
        
        $this->db->where('c.recruiter_id', $recruiter_id);
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
     * Get conversation by ID with access check for recruiter
     */
    public function get_conversation_for_recruiter($conversation_id, $recruiter_id)
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
        $this->db->where('cc.id', $conversation_id);
        $this->db->where('cc.recruiter_id', $recruiter_id);
        $this->db->where('cc.enabled', 1);
        $this->db->where('cc.removed', 0);
        
        return $this->db->get()->row();
    }

    /**
     * Get unread message count for recruiter
     */
    public function get_unread_count_for_recruiter($recruiter_id)
    {
        $this->db->select('COUNT(*) as unread_count');
        $this->db->from('chat_messages cm');
        $this->db->join('chat_conversations cc', 'cc.id = cm.conversation_id');
        $this->db->where('cc.recruiter_id', $recruiter_id);
        $this->db->where('cm.sender_type', 'agency');
        $this->db->where('cm.is_read', 0);
        $this->db->where('cm.enabled', 1);
        $this->db->where('cm.removed', 0);
        $this->db->where('cc.enabled', 1);
        $this->db->where('cc.removed', 0);
        
        $result = $this->db->get()->row();
        return $result ? $result->unread_count : 0;
    }

    /**
     * Get agencies that recruiter can chat with - FIXED VERSION
     */
    public function get_available_agencies($recruiter_id)
    {
        // Get agencies from existing conversations
        $this->db->select('DISTINCT a.id, a.name, a.email, a.logo')
                 ->from('agencies a')
                 ->join('chat_conversations cc', 'cc.agency_id = a.id', 'left')
                 ->where('cc.recruiter_id', $recruiter_id)
                 ->where('a.enabled', 1)
                 ->where('a.removed', 0);
        
        $this->db->order_by('a.name', 'ASC'); // Fixed order_by syntax
        
        return $this->db->get()->result();
    }

    /**
     * Simple method: Get all active agencies - FIXED VERSION
     */
    public function get_available_agencies_simple($recruiter_id)
    {
        $this->db->select('id, name, email, logo')
                 ->from('agencies')
                 ->where('enabled', 1)
                 ->where('removed', 0);
        
        $this->db->order_by('name', 'ASC'); // Fixed order_by syntax
        
        return $this->db->get()->result();
    }

    /**
     * Get agencies including recruiter's own agency
     */
    public function get_available_agencies_complete($recruiter_id)
    {
        // First get the recruiter's agency_id
        $recruiter = $this->db->select('agency_id')
                             ->from('recruiters')
                             ->where('id', $recruiter_id)
                             ->where('enabled', 1)
                             ->where('removed', 0)
                             ->get()
                             ->row();
        
        if (!$recruiter) {
            return [];
        }
        
        // Get agencies: recruiter's agency + agencies with existing conversations
        $this->db->select('DISTINCT a.id, a.name, a.email, a.logo')
                 ->from('agencies a')
                 ->where('a.enabled', 1)
                 ->where('a.removed', 0)
                 ->group_start()
                     ->where('a.id', $recruiter->agency_id) // Recruiter's own agency
                     ->or_where_in('a.id', function($query) use ($recruiter_id) {
                         $query->select('agency_id')
                               ->from('chat_conversations')
                               ->where('recruiter_id', $recruiter_id)
                               ->where('enabled', 1)
                               ->where('removed', 0);
                     })
                 ->group_end();
        
        $this->db->order_by('a.name', 'ASC'); // Fixed order_by syntax
        
        return $this->db->get()->result();
    }

    /**
     * Get or create conversation
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
        
        // Create new conversation
        $conversation_data = [
            'agency_id' => $agency_id,
            'recruiter_id' => $recruiter_id,
            'job_id' => $job_id,
            'candidate_id' => $candidate_id,
            'title' => $this->generate_conversation_title($agency_id, $recruiter_id, $job_id, $candidate_id),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'enabled' => 1
        ];
        
        $this->db->insert('chat_conversations', $conversation_data);
        $conversation_id = $this->db->insert_id();
        
        return $this->db->where('id', $conversation_id)->get('chat_conversations')->row();
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
            // Get agency name for general conversation
            $agency = $this->db->select('name')->from('agencies')->where('id', $agency_id)->get()->row();
            if ($agency) {
                $title_parts[] = $agency->name;
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
            'type' => 'chat', // ← CHANGED THIS LINE from 'system' to 'chat'
            'sender_type' => $sender_type, // 'agency' or 'recruiter'
            'sender_id' => $sender_id,
            'receiver_type' => $receiver_type, // 'agency' or 'recruiter'
            'receiver_id' => $recipient_id,
            'related_entity' => 'agency',
            'related_entity_id' => $conversation_id,
            'metadata' => json_encode([
                'conversation_id' => $conversation_id,
                'message_preview' => $this->truncate_message($message, 50),
                'is_chat_notification' => true
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
 * Send message - DEBUG VERSION
 */
public function send_message($conversation_id, $sender_type, $sender_id, $message, $message_type = 'text', $file_data = null)
{
    log_message('debug', '=== send_message called ===');
    log_message('debug', "Params: conversation_id: $conversation_id, sender_type: $sender_type, sender_id: $sender_id, message: " . substr($message, 0, 50));
    
    try {
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
        
        log_message('debug', 'Inserting message data: ' . print_r($message_data, true));
        
        $this->db->insert('chat_messages', $message_data);
        $message_id = $this->db->insert_id();
        
        log_message('debug', "Message inserted with ID: $message_id");
        
        if ($this->db->error()['code']) {
            log_message('error', 'Database error: ' . $this->db->error()['message']);
            return false;
        }
        
        // Update conversation last message time
        $this->db->where('id', $conversation_id)
                 ->update('chat_conversations', [
                     'last_message_at' => date('Y-m-d H:i:s'),
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);
        
        log_message('debug', 'Conversation updated successfully');
        
        return $message_id;
    } catch (Exception $e) {
        log_message('error', 'Error in send_message: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        return false;
    }

}

}