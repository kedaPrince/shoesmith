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

public function get_or_create_candidate_conversation($agency_id, $recruiter_id, $candidate_id, $job_id = null)
    {
        // Check if candidate exists and has an agency
        $this->db->select('c.*, ca.agency_id as candidate_agency_id');
        $this->db->from('candidates c');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'left');
        $this->db->where('c.id', $candidate_id);
        $this->db->where('c.removed', 0);
        $candidate = $this->db->get()->row();
        
        if (!$candidate) {
            return false;
        }
        
        // Check if conversation already exists for this candidate
        $this->db->where('agency_id', $agency_id);
        $this->db->where('recruiter_id', $recruiter_id);
        $this->db->where('candidate_id', $candidate_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        
        $conversation = $this->db->get('chat_conversations')->row();
        
        if ($conversation) {
            return $conversation;
        }
        
        // Generate UUID for the conversation
        $uuid = $this->generate_uuid();
        $attempts = 0;
        while ($this->uuid_exists($uuid) && $attempts < 5) {
            $uuid = $this->generate_uuid();
            $attempts++;
        }
        
        // Get candidate details for conversation title
        $candidate_name = $candidate->first_name . ' ' . $candidate->last_name;
        $candidate_ref = $candidate->reference_number ?? '';
        
        // Create new conversation with UUID
        $conversation_data = [
            'agency_id' => $agency_id,
            'recruiter_id' => $recruiter_id,
            'candidate_id' => $candidate_id,
            'job_id' => $job_id ?: $candidate->job_id,
            'uuid' => $uuid,
            'title' => "Candidate: {$candidate_name} ({$candidate_ref})",
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'enabled' => 1
        ];
        
        $this->db->insert('chat_conversations', $conversation_data);
        
        if ($this->db->error()['code']) {
            log_message('error', 'Failed to create candidate conversation: ' . $this->db->error()['message']);
            return false;
        }
        
        $conversation_id = $this->db->insert_id();
        
        return $this->db->where('id', $conversation_id)->get('chat_conversations')->row();
    }

      public function generate_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    // Add UUID existence check
    private function uuid_exists($uuid)
    {
        $this->db->where('uuid', $uuid);
        $this->db->from('chat_conversations');
        return $this->db->count_all_results() > 0;
    }

public function get_recruiter_conversations($recruiter_id, $limit = null, $offset = null)
{
    $this->db->select('cc.*, a.name as agency_name, 
                      j.name as job_name, c.first_name, c.last_name, c.reference_number as candidate_ref,
                      CONCAT(c.first_name, " ", c.last_name) as candidate_name,
                      (SELECT COUNT(*) FROM chat_messages cm 
                       WHERE cm.conversation_id = cc.id AND cm.is_read = 0 
                       AND cm.sender_type = "agency") as unread_count,
                      last_msg.message as last_message,
                      last_msg.created_at as last_message_at');
    $this->db->from('chat_conversations cc');
    $this->db->join('agencies a', 'a.id = cc.agency_id', 'left');
    $this->db->join('mod_jobs j', 'j.id = cc.job_id', 'left');
    $this->db->join('candidates c', 'c.id = cc.candidate_id', 'left');
    $this->db->join('(SELECT conversation_id, message, created_at 
                     FROM chat_messages 
                     WHERE id IN (SELECT MAX(id) FROM chat_messages GROUP BY conversation_id)
                    ) last_msg', 'last_msg.conversation_id = cc.id', 'left');
    
    $this->db->where('cc.recruiter_id', $recruiter_id);
    $this->db->where('cc.enabled', 1);
    $this->db->where('cc.removed', 0);
    $this->db->order_by('cc.last_message_at', 'DESC');
    $this->db->order_by('cc.created_at', 'DESC');
    
    if ($limit) {
        $this->db->limit($limit, $offset);
    }
    
    return $this->db->get()->result();
}


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
public function get_conversation_for_recruiter_by_uuid($uuid, $recruiter_id)
    {
        $this->db->select('cc.*, 
                          a.name as agency_name, 
                          CONCAT(as.first_name, " ", as.last_name) as agency_user_name,
                          j.name as job_name,
                          CONCAT(c.first_name, " ", c.last_name) as candidate_name');
        $this->db->from('chat_conversations cc');
        $this->db->join('agencies a', 'a.id = cc.agency_id', 'left');
        $this->db->join('agency_staff as', 'as.agency_id = cc.agency_id', 'left');
        $this->db->join('mod_jobs j', 'j.id = cc.job_id', 'left');
        $this->db->join('candidates c', 'c.id = cc.candidate_id', 'left');
        $this->db->where('cc.uuid', $uuid);
        $this->db->where('cc.recruiter_id', $recruiter_id);
        $this->db->where('cc.enabled', 1);
        $this->db->where('cc.removed', 0);
        
        return $this->db->get()->row();
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

    public function mark_chat_notifications_read($conversation_id, $user_id, $user_type)
    {
        // Update ALL chat notifications for this user to mark them as read
        $this->db->where('receiver_id', $user_id);
        $this->db->where('receiver_type', $user_type);
        $this->db->where('type', 'chat');
        $this->db->where('is_read', 0);
        
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
            'type' => 'chat',
            'sender_type' => $sender_type,
            'sender_id' => $sender_id,
            'receiver_type' => $receiver_type,
            'receiver_id' => $recipient_id,
            'related_entity' => 'chat_conversation',
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
                 ->like('metadata', 'is_chat_notification')
                 ->order_by('created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Send message
     */
    public function send_message($conversation_id, $sender_type, $sender_id, $message, $message_type = 'text', $file_data = null)
    {
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
                    
            $this->db->insert('chat_messages', $message_data);
            $message_id = $this->db->insert_id();
            
            if ($this->db->error()['code']) {
                return false;
            }
            
            // Update conversation last message time
            $this->db->where('id', $conversation_id)
                    ->update('chat_conversations', [
                        'last_message_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
            return $message_id;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get agency details by ID
     */
    public function get_agency_details($agency_id)
    {
        $this->db->select('a.*, 
                          COUNT(DISTINCT r.id) as total_recruiters,
                          COUNT(DISTINCT j.id) as active_jobs,
                          (SELECT COUNT(*) FROM chat_conversations cc 
                           WHERE cc.agency_id = a.id AND cc.enabled = 1) as total_conversations');
        $this->db->from('agencies a');
        $this->db->join('recruiters r', 'r.agency_id = a.id AND r.enabled = 1 AND r.removed = 0', 'left');
        $this->db->join('mod_jobs j', 'j.agency_id = a.id AND j.enabled = 1 AND j.removed = 0', 'left');
        $this->db->where('a.id', $agency_id);
        $this->db->where('a.enabled', 1);
        $this->db->where('a.removed', 0);
        $this->db->group_by('a.id');
        
        return $this->db->get()->row();
    }

    /**
     * Get agency online status
     */
    public function get_agency_online_status($agency_id)
    {
        $this->db->select('last_activity_at, last_login');
        $this->db->from('agencies');
        $this->db->where('id', $agency_id);
        $result = $this->db->get()->row();
        
        if (!$result) return false;
        
        // Consider online if active within last 5 minutes
        $last_activity = $result->last_activity_at ? strtotime($result->last_activity_at) : 0;
        $last_login = $result->last_login ? strtotime($result->last_login) : 0;
        $last_active = max($last_activity, $last_login);
        
        return (time() - $last_active) < 300; // 5 minutes
    }

    /**
 * Get general conversation (without candidate)
 */
public function get_general_conversation($agency_id, $recruiter_id)
{
    $this->db->where('agency_id', $agency_id);
    $this->db->where('recruiter_id', $recruiter_id);
    $this->db->where('candidate_id IS NULL', null, false); // General chat has no candidate_id
    $this->db->where('enabled', 1);
    $this->db->where('removed', 0);
    
    return $this->db->get('chat_conversations')->row();
}

/**
 * Create conversation
 */
public function create_conversation($data)
{
    $this->db->insert('chat_conversations', $data);
    return $this->db->insert_id();
}

/**
 * Get conversation by ID
 */
public function get_conversation_by_id($conversation_id)
{
    $this->db->where('id', $conversation_id);
    $this->db->where('enabled', 1);
    $this->db->where('removed', 0);
    
    return $this->db->get('chat_conversations')->row();
}

/**
 * Get available agencies for recruiter (simple version)
 */
public function get_available_agencies_simple($recruiter_id)
{
    // Debug: Log the recruiter_id
    log_message('debug', 'Getting available agencies for recruiter_id: ' . $recruiter_id);
    
    try {
        // Option 1: Get agencies from existing conversations
        $this->db->select('DISTINCT a.id, a.name, a.email, a.logo', false);
        $this->db->from('agencies a');
        $this->db->join('chat_conversations cc', 'cc.agency_id = a.id', 'left');
        $this->db->where('cc.recruiter_id', $recruiter_id);
        $this->db->where('a.enabled', 1);
        $this->db->where('a.removed', 0);
        $this->db->order_by('a.name', 'ASC');
        
        $query = $this->db->get();
        $result = $query->result();
        
        log_message('debug', 'Found ' . count($result) . ' agencies from conversations');
        
        // If no agencies found from conversations, get agencies from candidate submissions
        if (empty($result)) {
            log_message('debug', 'No agencies from conversations, checking candidate submissions');
            
            $this->db->select('DISTINCT a.id, a.name, a.email, a.logo', false);
            $this->db->from('agencies a');
            $this->db->join('candidates c', 'c.agency_id = a.id', 'left');
            $this->db->where('c.assigned_agent_id', $recruiter_id);
            $this->db->where('a.enabled', 1);
            $this->db->where('a.removed', 0);
            $this->db->where('c.removed', 0);
            $this->db->order_by('a.name', 'ASC');
            
            $query = $this->db->get();
            $result = $query->result();
            
            log_message('debug', 'Found ' . count($result) . ' agencies from candidate submissions');
        }
        
        return $result;
        
    } catch (Exception $e) {
        log_message('error', 'Error in get_available_agencies_simple: ' . $e->getMessage());
        
        // Return empty array as fallback
        return [];
    }
}
public function get_unread_count_for_recruiter($recruiter_id)
    {
        $this->db->select('COUNT(*) as unread_count');
        $this->db->from('chat_messages cm');
        $this->db->join('chat_conversations cc', 'cc.id = cm.conversation_id');
        $this->db->where('cc.recruiter_id', $recruiter_id);
        $this->db->where('cm.sender_type', 'agency');  // Messages from agencies are unread for recruiter
        $this->db->where('cm.is_read', 0);
        $this->db->where('cm.enabled', 1);
        $this->db->where('cm.removed', 0);
        $this->db->where('cc.enabled', 1);
        $this->db->where('cc.removed', 0);
        
        $result = $this->db->get()->row();
                
        return $result ? $result->unread_count : 0;
    }
}