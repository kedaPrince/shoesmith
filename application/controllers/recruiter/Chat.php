<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Chat extends CRUD_Controller
{
    public $pageName = 'chat';
    public $folder = 'recruiter';
    public $model = 'Model_chat_messages';
    public $quickManage = false;
    public $group = 'Chat';
    

    
    public function __construct()
    {
        parent::__construct();
        $this->folder = 'recruiter';
        
        // Load recruiter-specific chat model
        $this->load->model('recruiter/Model_chat_messages');
        
        // Load notifications model
        $this->load->model('recruiter/Model_notifications');
        
        // Verify recruiter access
        $login_data = $this->session->userdata('login');
        if (empty($login_data['recruiter'])) {
            redirect('recruiter/login');
        }
    }


    
   /**
 * Main chat page - redirects to first conversation or shows available agencies
 */
public function index()
{
    $recruiter_id = $this->get_recruiter_id();
    
    // Get all conversations
    $conversations = $this->{$this->model}->get_recruiter_conversations($recruiter_id);
    
    // Get available agencies for new chats
    $available_agencies = $this->{$this->model}->get_available_agencies_simple($recruiter_id);
    
    // If user has conversations, redirect to the first one USING UUID
    if (!empty($conversations)) {
        redirect('recruiter/chat/conversation/' . $conversations[0]->uuid); // CHANGED: id → uuid
        return;
    }
    
    // If no conversations but has agencies, create first conversation with first agency
    if (!empty($available_agencies)) {
        $conversation = $this->{$this->model}->get_or_create_conversation(
            $available_agencies[0]->id, 
            $recruiter_id
        );
        
        if ($conversation) {
            redirect('recruiter/chat/conversation/' . $conversation->uuid); // CHANGED: id → uuid
            return;
        }
    }
    
    // Fallback: Show chat with agencies list (no conversations available)
    $this->load_chat_view($conversations, $available_agencies);
}

    

    private function load_chat_view($conversations, $agencies)
    {
        $recruiter_id = $this->get_recruiter_id();
        
        $this->breadcrumbs = [
            ['title' => lang('chat_heading'), 'url' => '']
        ];
        
        $this->load->view($this->folder . '/view_header');
        $this->load->view('recruiter/chat/conversation', [
            'conversation' => null,
            'messages' => [],
            'all_conversations' => $conversations,
            'available_agencies' => $agencies,
            'heading' => lang('chat_heading'),
            'recruiter_id' => $recruiter_id,
            'total_unread_count' => 0,
            'recent_notifications' => [],
            'total_message_count' => 0
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

    public function conversation($uuid = null)
{
    $recruiter_id = $this->get_recruiter_id();
    
    if (!$uuid) {
        redirect('recruiter/chat');
        return;
    }
    
    // Get conversation by UUID instead of ID
    $conversation = $this->{$this->model}->get_conversation_for_recruiter_by_uuid($uuid, $recruiter_id);
    
    if (!$conversation) {
        show_404();
    }
    
    // Mark messages as read
    $this->{$this->model}->mark_messages_as_read($conversation->id, 'recruiter');
    
    $messages = $this->{$this->model}->get_conversation_messages($conversation->id);
    
    // Get all conversations for the sidebar
    $all_conversations = $this->{$this->model}->get_recruiter_conversations($recruiter_id);
    
    // Get available agencies for new chats
    $available_agencies = $this->{$this->model}->get_available_agencies_simple($recruiter_id);
    
    // Calculate total unread count
    $total_unread_count = $this->{$this->model}->get_unread_count_for_recruiter($recruiter_id);
    
    // Get recent notifications (initialize as empty array if method doesn't exist)
    $recent_notifications = [];
    if (method_exists($this->{$this->model}, 'get_recent_notifications')) {
        $recent_notifications = $this->{$this->model}->get_recent_notifications($recruiter_id, 'recruiter');
    }
    
    // Calculate total message count (initialize as 0 if method doesn't exist)
    $total_message_count = 0;
    if (method_exists($this->{$this->model}, 'get_total_message_count')) {
        $total_message_count = $this->{$this->model}->get_total_message_count($recruiter_id);
    }
    
    // Get agency details for the right sidebar
    $agency_details = $this->{$this->model}->get_agency_details($conversation->agency_id);
    
    // Get online status
    $agency_online = $this->{$this->model}->get_agency_online_status($conversation->agency_id);
    
    // Ensure conversation has required properties
    if (!isset($conversation->unread_count)) {
        $conversation->unread_count = 0;
    }
    if (!isset($conversation->is_online)) {
        $conversation->is_online = 0;
    }
    
    $this->breadcrumbs = [
        ['title' => lang('chat_heading'), 'url' => url('chat')],
        ['title' => $conversation->agency_name, 'url' => '']
    ];
    
    $data = [
        'conversation' => $conversation,
        'messages' => $messages,
        'all_conversations' => $all_conversations,
        'available_agencies' => $available_agencies,
        'heading' => lang('chat_heading'),
        'recruiter_id' => $recruiter_id,
        'total_unread_count' => $total_unread_count,
        'recent_notifications' => $recent_notifications,
        'total_message_count' => $total_message_count,
        'agency_details' => $agency_details,
        'agency_online' => $agency_online
    ];
    
    $this->load->view($this->folder . '/view_header');
    $this->load->view('recruiter/chat/conversation', $data);
    $this->load->view($this->folder . '/view_footer');
}

    /**
     * Start new conversation
     */
    public function start()
    {
        $recruiter_id = $this->get_recruiter_id();
        
        // Use the simple method to avoid any SQL errors
        $agencies = $this->{$this->model}->get_available_agencies_simple($recruiter_id);
        
        $this->breadcrumbs = [
            ['title' => lang('chat_heading'), 'url' => url('chat')],
            ['title' => lang('label_start_conversation'), 'url' => '']
        ];
        
        $this->load->view($this->folder . '/view_header');
        $this->load->view('recruiter/chat/start', [
            'agencies' => $agencies,
            'heading' => lang('label_start_conversation'),
            'recruiter_id' => $recruiter_id
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

public function ajax_send_message()
{
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_hash = $this->security->get_csrf_hash();
    
    $conversation_uuid = $this->input->post('conversation_uuid');
    $message_text = $this->input->post('message');
    $recruiter_id = $this->get_recruiter_id();
    
    // Log what we received
    log_message('debug', 'AJAX Send Message - CSRF token received: ' . ($this->input->post($csrf_name) ? 'YES' : 'NO'));
    log_message('debug', 'AJAX Send Message - Conversation UUID: ' . $conversation_uuid);
    log_message('debug', 'AJAX Send Message - Message text: ' . ($message_text ? 'YES' : 'NO'));
    
    if (!$conversation_uuid || !$message_text || !$recruiter_id) {
        ajax_return([
            'success' => false,
            'message' => 'Missing required parameters',
            'csrf' => $csrf_hash
        ]);
        return;
    }
    
    // Get conversation by UUID
    $conversation = $this->Model_chat_messages->get_conversation_for_recruiter_by_uuid(
        $conversation_uuid, 
        $recruiter_id
    );
    
    if (!$conversation) {
        ajax_return([
            'success' => false,
            'message' => 'Conversation not found or access denied',
            'csrf' => $csrf_hash
        ]);
        return;
    }
    
    // Send message using conversation ID (not UUID)
    $message_id = $this->Model_chat_messages->send_message(
        $conversation->id, // Use ID here
        'recruiter',
        $recruiter_id,
        $message_text,
        'text',
        null
    );
    
    if ($message_id) {
        ajax_return([
            'success' => true,
            'message_id' => $message_id,
            'csrf' => $csrf_hash
        ]);
    } else {
        ajax_return([
            'success' => false,
            'message' => 'Failed to save message',
            'csrf' => $csrf_hash
        ]);
    }
}


public function ajax_get_messages()
{
    // Skip CSRF validation temporarily to debug
    $csrf_hash = $this->security->get_csrf_hash();
    
    $conversation_uuid = $this->input->post('conversation_uuid');
    $last_message_id = $this->input->post('last_message_id') ?: 0;
    $recruiter_id = $this->get_recruiter_id();
    
    // Enable logging
    log_message('debug', 'AJAX Get Messages - UUID: ' . $conversation_uuid);
    log_message('debug', 'AJAX Get Messages - Last Message ID: ' . $last_message_id);
    
    if (!$conversation_uuid) {
        ajax_return([
            'success' => false, 
            'message' => 'Conversation UUID required',
            'csrf' => $csrf_hash
        ]);
        return;
    }
    
    // Get conversation by UUID
    $conversation = $this->Model_chat_messages->get_conversation_for_recruiter_by_uuid(
        $conversation_uuid, 
        $recruiter_id
    );
    
    if (!$conversation) {
        ajax_return([
            'success' => false, 
            'message' => 'Access denied',
            'csrf' => $csrf_hash
        ]);
        return;
    }
    
    // Get new messages
    $this->db->select('cm.*, 
                      CASE 
                          WHEN cm.sender_type = "agency" THEN a.name
                          WHEN cm.sender_type = "recruiter" THEN CONCAT(r.first_name, " ", r.last_name)
                      END as sender_name');
    $this->db->from('chat_messages cm');
    $this->db->join('agencies a', 'a.id = cm.sender_id AND cm.sender_type = "agency"', 'left');
    $this->db->join('recruiters r', 'r.id = cm.sender_id AND cm.sender_type = "recruiter"', 'left');
    $this->db->where('cm.conversation_id', $conversation->id);
    
    if ($last_message_id > 0) {
        $this->db->where('cm.id >', $last_message_id);
    }
    
    $this->db->where('cm.enabled', 1);
    $this->db->where('cm.removed', 0);
    $this->db->order_by('cm.created_at', 'ASC');
    
    $query = $this->db->get();
    $messages = $query->result();
    
    log_message('debug', 'AJAX Get Messages - Found ' . count($messages) . ' new messages');
    
    // Get the latest message ID
    $latest_message_id = $last_message_id;
    if (!empty($messages)) {
        $last_message = end($messages);
        $latest_message_id = $last_message->id;
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'messages' => $messages,
        'last_message_id' => $latest_message_id,
        'has_new_messages' => !empty($messages),
        'csrf' => $csrf_hash
    ];
    
    // Also include HTML for backward compatibility
    if (!empty($messages)) {
        $html = '';
        foreach ($messages as $message) {
            $html .= $this->load->view('recruiter/chat/message_item', [
                'message' => $message, 
                'current_user_type' => 'recruiter'
            ], true);
        }
        $response['html'] = $html;
    }
    
    ajax_return($response);
}

// Add this to your Chat controller
public function ajax_check_session()
{
    $recruiter_id = $this->get_recruiter_id();
    
    if (!$recruiter_id) {
        ajax_return([
            'success' => false,
            'message' => 'Not logged in',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    
    ajax_return([
        'success' => true,
        'message' => 'Session valid',
        'csrf' => $this->security->get_csrf_hash()
    ]);
}
    /**
     * AJAX: Get unread count for menu badge
     */
    public function ajax_get_unread_count()
    {
        $recruiter_id = $this->get_recruiter_id();
        $unread_count = $this->{$this->model}->get_unread_count_for_recruiter($recruiter_id);
        
        ajax_return(['success' => true, 'unread_count' => $unread_count]);
    }

    /**
 * AJAX: Start new conversation
 */
public function ajax_start_conversation()
{
    $agency_id = $this->input->post('agency_id');
    $subject = $this->input->post('subject');
    $initial_message = $this->input->post('initial_message');
    $recruiter_id = $this->get_recruiter_id();
    
    if (empty($agency_id)) {
        ajax_return(['success' => false, 'message' => 'Please select an agency']);
        return;
    }
    
    // Create conversation
    $conversation = $this->{$this->model}->get_or_create_conversation($agency_id, $recruiter_id);
    
    if ($conversation) {
        // Send initial message if provided
        if (!empty($initial_message)) {
            $this->{$this->model}->send_message(
                $conversation->id,
                'recruiter',
                $recruiter_id,
                $initial_message
            );
        }
        
        ajax_return([
            'success' => true, 
            'conversation_id' => $conversation->id,
            'conversation_uuid' => $conversation->uuid, // Add this
            'redirect_url' => site_url('recruiter/chat/conversation/' . $conversation->uuid) // CHANGED: id → uuid
        ]);
    } else {
        ajax_return(['success' => false, 'message' => 'Failed to create conversation']);
    }
}
/**
 * Quick start conversation - USE UUID
 */
public function quick_start($agency_id = null)
{
    $recruiter_id = $this->get_recruiter_id();
    
    if (!$agency_id) {
        show_404();
    }
    
    // Create or get conversation
    $conversation = $this->{$this->model}->get_or_create_conversation($agency_id, $recruiter_id);
    
    if ($conversation) {
        // Redirect using UUID instead of ID
        redirect('recruiter/chat/conversation/' . $conversation->uuid);
    } else {
        show_error('Failed to create conversation');
    }
}
   /**
 * Start new conversation from candidate/job context
 */
public function start_conversation($agency_id, $job_id = null, $candidate_id = null)
{
    $recruiter_id = $this->get_recruiter_id();
    
    $conversation = $this->{$this->model}->get_or_create_conversation(
        $agency_id, 
        $recruiter_id, 
        $job_id, 
        $candidate_id
    );
    
    if ($conversation) {
        redirect('recruiter/chat/conversation/' . $conversation->uuid); // CHANGED: id → uuid
    } else {
        show_error('Failed to create conversation');
    }
}

    private function send_chat_notification($conversation, $message, $sender_type)
    {
        // This would integrate with your existing notification system
        $this->load->model('recruiter/Model_notifications');
        
        if ($sender_type === 'recruiter') {
            // Notify agency
            $this->Model_notifications->create_chat_notification(
                $conversation->id,
                $conversation->agency_id,
                'agency',
                $message,
                $this->get_recruiter_id()
            );
        }
    }

    private function get_recruiter_id()
    {
        $login_data = $this->session->userdata('login');
        return !empty($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : null;
    }

    public function ajax_get_chat_notifications()
{
    $recruiter_id = $this->get_recruiter_id();
    
    $response = [
        'success' => false,
        'unread_count' => 0
    ];

    try {        
        // Load notifications model
        $this->load->model('recruiter/Model_notifications');
        
        // Get chat-specific unread count from chat messages
        $chat_messages_count = $this->Model_chat_messages->get_unread_count_for_recruiter($recruiter_id);
        
        // Also count chat notifications from notifications table
        $chat_notifications_count = $this->Model_notifications->count_chat_notifications($recruiter_id);
        
        // Use the larger count (either from chat messages or notifications)
        $total_chat_unread = max($chat_messages_count, $chat_notifications_count);
        
        $response['unread_count'] = $total_chat_unread;
        $response['success'] = true;

    } catch (Exception $e) {
        // Log error but don't break the functionality
        log_message('error', 'Error in ajax_get_chat_notifications: ' . $e->getMessage());
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}
private function validate_csrf_token()
{
    if ($this->input->is_ajax_request()) {
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();
        
        // Try to get CSRF token from POST data
        $csrf_token = $this->input->post($csrf_name);
        
        // If not in POST, try from raw input (for FormData)
        if (!$csrf_token) {
            $raw_input = file_get_contents('php://input');
            if ($raw_input) {
                parse_str($raw_input, $parsed_input);
                $csrf_token = isset($parsed_input[$csrf_name]) ? $parsed_input[$csrf_name] : null;
            }
        }
        
        // Debug logging
        log_message('debug', 'CSRF Validation - Token received: ' . ($csrf_token ? 'YES' : 'NO'));
        log_message('debug', 'CSRF Validation - Expected hash: ' . $csrf_hash);
        
        if (!$csrf_token) {
            log_message('error', 'CSRF token missing in request');
            ajax_return([
                'success' => false, 
                'message' => 'CSRF token missing', 
                'csrf' => $csrf_hash
            ]);
            return false;
        }
        
        if ($csrf_token !== $csrf_hash) {
            log_message('error', 'CSRF token mismatch. Received: ' . $csrf_token . ', Expected: ' . $csrf_hash);
            ajax_return([
                'success' => false, 
                'message' => 'Invalid CSRF token', 
                'csrf' => $csrf_hash
            ]);
            return false;
        }
        
        log_message('debug', 'CSRF token validated successfully');
        return true;
    }
    
    return true;
}

public function ajax_mark_notifications_read()
{
    if (!$this->validate_csrf_token()) {
        return;
    }
    
    $conversation_id = $this->input->post('conversation_id');
    $recruiter_id = $this->get_recruiter_id();  // CHANGED: Use recruiter_id
    
    if (!$conversation_id || !$recruiter_id) {
        ajax_return([
            'success' => false,
            'message' => 'Invalid parameters'
        ]);
        return;
    }
    
    // CHANGED: Use recruiter permission check
    $conversation = $this->{$this->model}->get_conversation_for_recruiter($conversation_id, $recruiter_id);
    
    if (!$conversation) {
        ajax_return([
            'success' => false,
            'message' => 'Access denied'
        ]);
        return;
    }
    
    // CHANGED: Mark as read for recruiter
    $this->{$this->model}->mark_messages_as_read($conversation_id, 'recruiter');
    
    // CHANGED: Get unread count for recruiter
    $unread_count = $this->{$this->model}->get_unread_count_for_recruiter($recruiter_id);
    
    ajax_return([
        'success' => true,
        'message' => 'Notifications marked as read',
        'unread_count' => $unread_count,
        'csrf' => $this->security->get_csrf_hash()
    ]);
}

    public function ajax_get_conversations()
    {
        $recruiter_id = $this->get_recruiter_id();
        
        if (!$recruiter_id) {
            ajax_return(['success' => false, 'message' => 'Recruiter not logged in']);
            return;
        }

        try {
            // Get updated conversations with unread counts
            $conversations = $this->{$this->model}->get_recruiter_conversations($recruiter_id);
            
            // Calculate TOTAL unread count across all conversations
            $total_unread_count = 0;
            foreach ($conversations as $conv) {
                $total_unread_count += isset($conv->unread_count) ? $conv->unread_count : 0;
            }
            
            // Format the data for the sidebar
            $formatted_conversations = [];
            foreach ($conversations as $conv) {
                $formatted_conversations[] = [
                    'id' => $conv->id,
                    'agency_name' => $conv->agency_name,
                    'last_message' => $conv->last_message,
                    'last_message_at' => $conv->last_message_at,
                    'last_sender_type' => isset($conv->last_sender_type) ? $conv->last_sender_type : 'agency',
                    'unread_count' => isset($conv->unread_count) ? $conv->unread_count : 0,
                    'is_online' => isset($conv->is_online) ? $conv->is_online : false,
                    'agency_id' => isset($conv->agency_id) ? $conv->agency_id : 0
                ];
            }

            ajax_return([
                'success' => true,
                'conversations' => $formatted_conversations,
                'total_unread_count' => $total_unread_count
            ]);

        } catch (Exception $e) {
            log_message('error', 'Error fetching conversations: ' . $e->getMessage());
            ajax_return(['success' => false, 'message' => 'Server error']);
        }
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
        
        // Generate UUID
        $uuid = $this->generate_uuid();
        
        // Check if this UUID already exists
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
            // If duplicate UUID, try with new UUID
            if ($this->db->error()['code'] == 1062) {
                $conversation_data['uuid'] = $this->generate_uuid();
                $this->db->insert('chat_conversations', $conversation_data);
            }
        }
        
        $conversation_id = $this->db->insert_id();
        
        return $this->db->where('id', $conversation_id)->get('chat_conversations')->row();
    }

    // 2. Generate UUID v4
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

    // 3. Check if UUID exists
    private function uuid_exists($uuid)
    {
        $this->db->where('uuid', $uuid);
        $this->db->from('chat_conversations');
        return $this->db->count_all_results() > 0;
    }

    // 4. Get conversation by UUID for recruiter
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

    // 5. Get conversation by ID for recruiter (for legacy support)
    public function get_conversation_for_recruiter($conversation_id, $recruiter_id)
    {
        $this->db->select('cc.*, a.name as agency_name');
        $this->db->from('chat_conversations cc');
        $this->db->join('agencies a', 'a.id = cc.agency_id', 'left');
        $this->db->where('cc.id', $conversation_id);
        $this->db->where('cc.recruiter_id', $recruiter_id);
        $this->db->where('cc.enabled', 1);
        $this->db->where('cc.removed', 0);
        
        return $this->db->get()->row();
    }

    // 6. Get recruiter conversations
    public function get_recruiter_conversations($recruiter_id, $limit = null, $offset = null)
    {
        $this->db->select('cc.*, a.name as agency_name, 
                          j.name as job_name, c.first_name, c.last_name,
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

    // 7. Get available agencies for recruiter (simple version)
    public function get_available_agencies_simple($recruiter_id)
    {
        // Get agencies where recruiter has submitted candidates
        $this->db->select('DISTINCT a.id, a.name')
                ->from('agencies a')
                ->join('candidate_agencies ca', 'ca.agency_id = a.id')
                ->join('candidates c', 'c.id = ca.candidate_id')
                ->where('c.assigned_agent_id', $recruiter_id)
                ->where('a.enabled', 1)
                ->where('a.removed', 0)
                ->order_by('a.name', 'ASC');
        
        return $this->db->get()->result();
    }

    // 8. Get conversation messages
    public function get_conversation_messages($conversation_id, $limit = 100, $offset = 0)
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

    // 9. Send message
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

    // 10. Mark messages as read
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

    // 11. Get unread count for recruiter
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

    // 12. Get agency details
    public function get_agency_details($agency_id)
    {
        $this->db->select('a.*, 
                          COUNT(DISTINCT j.id) as active_jobs,
                          (SELECT COUNT(*) FROM chat_conversations cc 
                           WHERE cc.agency_id = a.id AND cc.enabled = 1) as total_conversations');
        $this->db->from('agencies a');
        $this->db->join('mod_jobs j', 'j.agency_id = a.id AND j.enabled = 1 AND j.removed = 0', 'left');
        $this->db->where('a.id', $agency_id);
        $this->db->where('a.enabled', 1);
        $this->db->where('a.removed', 0);
        $this->db->group_by('a.id');
        
        return $this->db->get()->row();
    }

    // 13. Get agency online status (simplified - check if any agency staff is active)
    public function get_agency_online_status($agency_id)
    {
        $this->db->select('MAX(last_activity_at) as last_activity')
                ->from('agency_staff')
                ->where('agency_id', $agency_id)
                ->where('enabled', 1)
                ->where('removed', 0);
        
        $result = $this->db->get()->row();
        
        if (!$result || !$result->last_activity) {
            return false;
        }
        
        // Consider online if active within last 5 minutes
        $last_activity = strtotime($result->last_activity);
        return (time() - $last_activity) < 300; // 5 minutes
    }

    // 14. Generate conversation title
    private function generate_conversation_title($agency_id, $recruiter_id, $job_id, $candidate_id)
    {
        $title_parts = [];
        
        if ($candidate_id) {
            $candidate = $this->db->select('first_name, last_name')->from('candidates')->where('id', $candidate_id)->get()->row();
            if ($candidate) {
                $title_parts[] = $candidate->first_name . ' ' . $candidate->last_name;
            }
        }
        
        if ($job_id) {
            $job = $this->db->select('name')->from('mod_jobs')->where('id', $job_id)->get()->row();
            if ($job) {
                $title_parts[] = $job->name;
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

    // 15. Get or create candidate-specific conversation (for recruiter)
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
}