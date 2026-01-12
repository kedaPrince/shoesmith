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

    // ==================== ADD THESE METHODS ====================

    /**
     * Get recruiter for a candidate
     * 
     * @param int $candidate_id
     * @return object|null
     */
    public function get_candidate_recruiter($candidate_id)
    {
        $this->db->select('r.*')
                 ->from('candidates c')
                 ->join('recruiters r', 'r.id = c.recruiter_id', 'left')
                 ->where('c.id', $candidate_id)
                 ->where('c.removed', 0)
                 ->where('r.enabled', 1)
                 ->where('r.removed', 0);
        
        return $this->db->get()->row();
    }

    /**
     * Get candidate details including submitting agency
     * 
     * @param int $candidate_id
     * @return object|null
     */
    public function get_candidate_with_submitting_agency($candidate_id)
    {
        $this->db->select('c.*, 
                          ca.agency_id as submitting_agency_id,
                          ca.agency_id as submitting_agency,
                          a.name as agency_name')
                 ->from('candidates c')
                 ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'left')
                 ->join('agencies a', 'a.id = ca.agency_id', 'left')
                 ->where('c.id', $candidate_id)
                 ->where('c.removed', 0)
                 ->order_by('ca.created_at', 'ASC') // Get the first agency (submitting agency)
                 ->limit(1);
        
        return $this->db->get()->row();
    }

    /**
     * Check if agency has access to candidate chat
     * 
     * @param int $agency_id
     * @param int $candidate_id
     * @return bool
     */
    public function can_agency_access_candidate_chat($agency_id, $candidate_id)
    {
        // Check if agency has access to candidate via candidate_agencies
        $this->db->select('1')
                 ->from('candidate_agencies ca')
                 ->join('candidates c', 'c.id = ca.candidate_id')
                 ->where('ca.candidate_id', $candidate_id)
                 ->where('ca.agency_id', $agency_id)
                 ->where('c.removed', 0);
        
        return $this->db->get()->row() !== null;
    }

    /**
     * Get candidate conversation history
     * 
     * @param int $candidate_id
     * @param int $agency_id
     * @return array
     */
    public function get_candidate_conversation_history($candidate_id, $agency_id)
    {
        $this->db->select('cc.*, 
                          CONCAT(r.first_name, " ", r.last_name) as recruiter_name,
                          r.profile_pic as recruiter_photo,
                          (SELECT COUNT(*) FROM chat_messages cm 
                           WHERE cm.conversation_id = cc.id AND cm.is_read = 0 
                           AND cm.sender_type = "recruiter") as unread_count')
                 ->from('chat_conversations cc')
                 ->join('recruiters r', 'r.id = cc.recruiter_id', 'left')
                 ->where('cc.candidate_id', $candidate_id)
                 ->where('cc.agency_id', $agency_id)
                 ->where('cc.enabled', 1)
                 ->where('cc.removed', 0)
                 ->order_by('cc.last_message_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get last message in conversation
     * 
     * @param int $conversation_id
     * @return object|null
     */
    public function get_last_conversation_message($conversation_id)
    {
        $this->db->select('*')
                 ->from('chat_messages')
                 ->where('conversation_id', $conversation_id)
                 ->where('enabled', 1)
                 ->where('removed', 0)
                 ->order_by('created_at', 'DESC')
                 ->limit(1);
        
        return $this->db->get()->row();
    }

    /**
     * Send initial message when starting candidate chat
     * 
     * @param int $conversation_id
     * @param int $sender_id
     * @param string $sender_type
     * @param int $candidate_id
     * @return int Message ID
     */
    public function send_initial_candidate_message($conversation_id, $sender_id, $sender_type, $candidate_id)
    {
        // Get candidate details
        $candidate = $this->get_candidate_with_submitting_agency($candidate_id);
        
        if (!$candidate) {
            return false;
        }
        
        $candidate_name = $candidate->first_name . ' ' . $candidate->last_name;
        $candidate_ref = $candidate->reference_number ?? 'N/A';
        
        // Create initial message
        $message = "Chat started about candidate: {$candidate_name} ({$candidate_ref})";
        
        $message_data = [
            'conversation_id' => $conversation_id,
            'sender_type' => $sender_type,
            'sender_id' => $sender_id,
            'message' => $message,
            'message_type' => 'system',
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'enabled' => 1
        ];
        
        $this->db->insert('chat_messages', $message_data);
        return $this->db->insert_id();
    }

    /**
     * Get conversation participant details
     * 
     * @param int $conversation_id
     * @param string $participant_type
     * @return object|null
     */
    public function get_conversation_participant($conversation_id, $participant_type)
    {
        $this->db->select('*')
                 ->from('chat_conversations')
                 ->where('id', $conversation_id)
                 ->where('enabled', 1)
                 ->where('removed', 0);
        
        $conversation = $this->db->get()->row();
        
        if (!$conversation) {
            return null;
        }
        
        if ($participant_type === 'recruiter') {
            // Get recruiter details
            $this->db->select('*')
                     ->from('recruiters')
                     ->where('id', $conversation->recruiter_id)
                     ->where('enabled', 1)
                     ->where('removed', 0);
            return $this->db->get()->row();
        } elseif ($participant_type === 'agency') {
            // Get agency details
            $this->db->select('*')
                     ->from('agencies')
                     ->where('id', $conversation->agency_id)
                     ->where('enabled', 1)
                     ->where('removed', 0);
            return $this->db->get()->row();
        }
        
        return null;
    }

    /**
     * Update conversation activity
     * 
     * @param int $conversation_id
     * @param int $user_id
     * @param string $user_type
     * @return bool
     */
    public function update_conversation_activity($conversation_id, $user_id, $user_type)
    {
        $activity_data = [
            'conversation_id' => $conversation_id,
            'user_id' => $user_id,
            'user_type' => $user_type,
            'last_activity_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Check if activity record exists
        $this->db->where('conversation_id', $conversation_id)
                 ->where('user_id', $user_id)
                 ->where('user_type', $user_type);
        
        $existing = $this->db->get('chat_conversation_activities')->row();
        
        if ($existing) {
            // Update existing
            $this->db->where('id', $existing->id)
                     ->update('chat_conversation_activities', $activity_data);
        } else {
            // Insert new
            $this->db->insert('chat_conversation_activities', $activity_data);
        }
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Get other participant's last seen time
     * 
     * @param int $conversation_id
     * @param string $current_user_type
     * @return string|null
     */
    public function get_other_participant_last_seen($conversation_id, $current_user_type)
    {
        $other_user_type = ($current_user_type === 'agency') ? 'recruiter' : 'agency';
        
        $this->db->select('c.*')
                 ->from('chat_conversations c')
                 ->where('c.id', $conversation_id);
        
        $conversation = $this->db->get()->row();
        
        if (!$conversation) {
            return null;
        }
        
        // Get the other participant's ID
        $other_user_id = ($other_user_type === 'agency') ? $conversation->agency_id : $conversation->recruiter_id;
        
        // Get their last activity from chat_conversation_activities
        $this->db->select('last_activity_at')
                 ->from('chat_conversation_activities')
                 ->where('conversation_id', $conversation_id)
                 ->where('user_id', $other_user_id)
                 ->where('user_type', $other_user_type)
                 ->order_by('last_activity_at', 'DESC')
                 ->limit(1);
        
        $activity = $this->db->get()->row();
        
        if ($activity) {
            return $activity->last_activity_at;
        }
        
        // Fallback to user's last login
        if ($other_user_type === 'recruiter') {
            $this->db->select('last_login')
                     ->from('recruiters')
                     ->where('id', $other_user_id);
            $user = $this->db->get()->row();
            return $user ? $user->last_login : null;
        } else {
            $this->db->select('last_login')
                     ->from('agency_staff')
                     ->where('id', $other_user_id);
            $user = $this->db->get()->row();
            return $user ? $user->last_login : null;
        }
    }

    // ==================== YOUR EXISTING METHODS ====================

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
            'uuid' => $uuid,
            'title' => $this->generate_conversation_title($agency_id, $recruiter_id, $job_id, $candidate_id),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'enabled' => 1
        ];
        
        $this->db->insert('chat_conversations', $conversation_data);
        
        if ($this->db->error()['code']) {
            log_message('error', 'Failed to create conversation: ' . $this->db->error()['message']);
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
        $this->db->where('cc.uuid', $uuid);
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
    
    // ===== ADD THIS: Create notification for the other party =====
    if ($message_id) {
        // Get conversation details to know who to notify
        $conversation = $this->db->where('id', $conversation_id)
                                 ->get('chat_conversations')
                                 ->row();
        
        if ($conversation) {
            if ($sender_type === 'recruiter') {
                // Recruiter sent message, notify agency
                $recipient_id = $conversation->agency_id;
                $recipient_type = 'agency';
            } else {
                // Agency sent message, notify recruiter
                $recipient_id = $conversation->recruiter_id;
                $recipient_type = 'recruiter';
            }
            
            // Create the notification
            $this->create_chat_notification(
                $conversation_id,
                $recipient_id,
                $recipient_type,
                $message,
                $sender_id
            );
        }
    }
    // ===== END ADDITION =====
    
    return $message_id;
}

    /**
     * Mark messages as read
     */
    public function mark_messages_as_read($conversation_id, $reader_type)
    {
        $this->db->where('conversation_id', $conversation_id);
        $this->db->where('sender_type !=', $reader_type);
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
        $this->db->where('cm.sender_type', 'recruiter');
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
        $this->db->select('r.id, r.first_name, r.last_name, r.email, r.profile_pic, 
                      r.last_activity_at, r.last_login') 
                ->from('recruiters r')
                ->where('r.agency_id', $agency_id)
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
    $sender_type = ($recipient_type === 'recruiter') ? 'agency' : 'recruiter';
    $receiver_type = $recipient_type;
    
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
        
        $last_activity = $result->last_activity_at ? strtotime($result->last_activity_at) : 0;
        $last_login = $result->last_login ? strtotime($result->last_login) : 0;
        $last_active = max($last_activity, $last_login);
        
        return (time() - $last_active) < 300;
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

    /**
     * Get or create candidate-specific conversation
     */
    public function get_or_create_candidate_conversation($agency_id, $recruiter_id, $candidate_id, $job_id = null)
    {
        // Check if candidate exists and has a recruiter
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
        
        // Generate UUID
        $uuid = $this->generate_uuid();
        $attempts = 0;
        while ($this->uuid_exists($uuid) && $attempts < 5) {
            $uuid = $this->generate_uuid();
            $attempts++;
        }
        
        // Get candidate details for conversation title
        $candidate_name = $candidate->first_name . ' ' . $candidate->last_name;
        $candidate_ref = $candidate->reference_number ?? '';
        
        // Create new conversation
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

    public function get_general_conversation($agency_id, $recruiter_id)
    {
        $this->db->where('agency_id', $agency_id);
        $this->db->where('recruiter_id', $recruiter_id);
        $this->db->where('candidate_id IS NULL', null, false);
        $this->db->where('is_active', 1);
        $query = $this->db->get('chat_conversations');
        
        return $query->row();
    }
    
    public function create_conversation($data)
    {
        $this->db->insert('chat_conversations', $data);
        return $this->db->insert_id();
    }
    
    public function get_conversation_by_id($conversation_id)
    {
        $this->db->where('id', $conversation_id);
        $this->db->where('is_active', 1);
        $query = $this->db->get('chat_conversations');
        
        return $query->row();
    }
}