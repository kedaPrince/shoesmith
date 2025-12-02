<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Chat extends CRUD_Controller
{
    public $pageName = 'chat';
    public $folder = 'agency';
    public $model = 'Model_chat_messages';
    public $quickManage = false;
    public $group = 'Chat';
    
    public function __construct()
    {
        parent::__construct();
        $this->folder = 'agency';
        
        // Load agency-specific chat model
        $this->load->model('agency/Model_chat_messages');
        
        // Load notifications model
        $this->load->model('agency/Model_notifications');
        
        // Verify agency access
        $login_data = $this->session->userdata('login');
        if (empty($login_data['agency'])) {
            redirect('agency/login');
        }
    }
    
    /**
 * Main chat page - redirects to first conversation or shows available recruiters
 */
public function index()
{
    $agency_id = $this->get_user_agency_id();
    
    // Get all conversations
    $conversations = $this->{$this->model}->get_agency_conversations($agency_id);
    
    // Get available recruiters for new chats
    $available_recruiters = $this->{$this->model}->get_available_recruiters($agency_id);
    
    // If user has conversations, redirect to the first one USING UUID
    if (!empty($conversations)) {
        redirect('agency/chat/conversation/' . $conversations[0]->uuid); // CHANGED: id → uuid
        return;
    }
    
    // If no conversations but has recruiters, create first conversation with first recruiter
    if (!empty($available_recruiters)) {
        $conversation = $this->{$this->model}->get_or_create_conversation(
            $agency_id, 
            $available_recruiters[0]->id
        );
        
        if ($conversation) {
            redirect('agency/chat/conversation/' . $conversation->uuid); // CHANGED: id → uuid
            return;
        }
    }
    
    // Fallback: Show chat with recruiters list (no conversations available)
    $this->load_chat_view($conversations, $available_recruiters);
}

    private function load_chat_view($conversations, $recruiters)
    {
        $agency_id = $this->get_user_agency_id();
        
        $this->breadcrumbs = [
            ['title' => lang('chat_heading'), 'url' => '']
        ];
        
        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/chat/conversation', [
            'conversation' => null,
            'messages' => [],
            'all_conversations' => $conversations,
            'available_recruiters' => $recruiters,
            'heading' => lang('chat_heading'),
            'agency_id' => $agency_id,
            'total_unread_count' => 0,
            'recent_notifications' => [],
            'total_message_count' => 0
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

    
public function conversation($uuid = null)
{
    $agency_id = $this->get_user_agency_id();
    
    if (!$uuid) {
        redirect('agency/chat');
        return;
    }
    
    // Get conversation by UUID instead of ID
    $conversation = $this->{$this->model}->get_conversation_for_agency_by_uuid($uuid, $agency_id);
    
    if (!$conversation) {
        show_404();
    }
    
    // Mark messages as read when opening conversation
    $this->{$this->model}->mark_messages_as_read($conversation->id, 'agency');
    
    // Get messages for this conversation
    $messages = $this->{$this->model}->get_conversation_messages($conversation->id, 100);
    
    // Get all conversations for sidebar
    $all_conversations = $this->{$this->model}->get_agency_conversations($agency_id);
    
    // Get available recruiters
    $available_recruiters = $this->{$this->model}->get_available_recruiters($agency_id);
    
    // Get total unread count
    $total_unread_count = $this->{$this->model}->get_unread_count_for_agency($agency_id);
    
    // Get total message count
    $total_message_count = $this->{$this->model}->get_total_message_count($agency_id);
    
    // Get recent notifications
    $recent_notifications = $this->{$this->model}->get_recent_notifications($agency_id, 'agency');
    
    $this->breadcrumbs = [
        ['title' => lang('chat_heading'), 'url' => site_url('agency/chat')],
        ['title' => 'Conversation with ' . htmlspecialchars($conversation->recruiter_name), 'url' => '']
    ];
    
    $this->load->view($this->folder . '/view_header');
    $this->load->view('agency/chat/conversation', [
        'conversation' => $conversation,
        'messages' => $messages,
        'all_conversations' => $all_conversations,
        'available_recruiters' => $available_recruiters,
        'heading' => lang('chat_heading'),
        'agency_id' => $agency_id,
        'total_unread_count' => $total_unread_count,
        'recent_notifications' => $recent_notifications,
        'total_message_count' => $total_message_count
    ]);
    $this->load->view($this->folder . '/view_footer');
}

/**
 * Start new conversation with quick link - USE UUID
 */
public function quick_start($recruiter_id = null)
{
    $agency_id = $this->get_user_agency_id();
    
    if (!$recruiter_id) {
        show_404();
    }
    
    // Create or get conversation
    $conversation = $this->{$this->model}->get_or_create_conversation($agency_id, $recruiter_id);
    
    if ($conversation) {
        // Redirect using UUID instead of ID
        redirect('agency/chat/conversation/' . $conversation->uuid);
    } else {
        show_error('Failed to create conversation');
    }
}

    

  

    /**
     * AJAX: Get unread count for menu badge
     */
    public function ajax_get_unread_count()
    {
        $agency_id = $this->get_user_agency_id();
        $unread_count = $this->{$this->model}->get_unread_count_for_agency($agency_id);
        
        ajax_return(['success' => true, 'unread_count' => $unread_count]);
    }

 
private function validate_csrf_token()
{
    if ($this->input->is_ajax_request()) {
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();
        
        // First check standard POST
        $csrf_token = $this->input->post($csrf_name);
        
        // If not found in POST, check raw input (for FormData)
        if (!$csrf_token) {
            $raw_input = file_get_contents('php://input');
            if ($raw_input) {
                // For FormData, we need to parse differently
                parse_str($raw_input, $parsed_input);
                $csrf_token = isset($parsed_input[$csrf_name]) ? $parsed_input[$csrf_name] : null;
            }
        }
        
        if (!$csrf_token) {
            ajax_return([
                'success' => false, 
                'message' => 'CSRF token missing', 
                'csrf' => $csrf_hash
            ]);
            return false;
        }
        
        if ($csrf_token !== $csrf_hash) {
            ajax_return([
                'success' => false, 
                'message' => 'Invalid CSRF token', 
                'csrf' => $csrf_hash
            ]);
            return false;
        }
        
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
    $agency_id = $this->get_user_agency_id();
    
    if (!$conversation_id || !$agency_id) {
        ajax_return([
            'success' => false,
            'message' => 'Invalid parameters'
        ]);
        return;
    }
    
    // FIX: Check if conversation belongs to agency
    $conversation = $this->{$this->model}->get_conversation_for_agency($conversation_id, $agency_id);
    
    if (!$conversation) {
        ajax_return([
            'success' => false,
            'message' => 'Access denied'
        ]);
        return;
    }
    
    $this->{$this->model}->mark_messages_as_read($conversation_id, 'agency');
    
    $unread_count = $this->{$this->model}->get_unread_count_for_agency($agency_id);
    
    ajax_return([
        'success' => true,
        'message' => 'Notifications marked as read',
        'unread_count' => $unread_count,
        'csrf' => $this->security->get_csrf_hash()
    ]);
}

public function ajax_send_message()
{
    if (!$this->validate_csrf_token()) {
        return;
    }
    
    // Accept ONLY conversation_uuid (secure)
    $conversation_uuid = $this->input->post('conversation_uuid');
    $message_text = $this->input->post('message');
    $agency_id = $this->get_user_agency_id();
    
    // Validate required parameters
    if (!$conversation_uuid || !$message_text || !$agency_id) {
        ajax_return([
            'success' => false,
            'message' => 'Missing required parameters'
        ]);
        return;
    }
    
    // Get conversation by UUID (secure method)
    $conversation = $this->{$this->model}->get_conversation_for_agency_by_uuid($conversation_uuid, $agency_id);
    
    if (!$conversation) {
        ajax_return([
            'success' => false,
            'message' => 'Access denied or conversation not found'
        ]);
        return;
    }
    
    // Send the message using the conversation ID (database ID internally)
    $message_id = $this->{$this->model}->send_message(
        $conversation->id, // Use database ID internally
        'agency',
        $agency_id,
        $message_text,
        'text',
        null
    );
    
    if ($message_id) {
        ajax_return([
            'success' => true,
            'message_id' => $message_id,
            'csrf' => $this->security->get_csrf_hash()
        ]);
    } else {
        ajax_return([
            'success' => false,
            'message' => 'Failed to save message'
        ]);
    }
}



public function ajax_get_messages()
{
    if (!$this->validate_csrf_token()) {
        return;
    }
    
    // Accept ONLY UUID (secure)
    $conversation_uuid = $this->input->post('conversation_uuid');
    $last_message_id = $this->input->post('last_message_id') ?: 0;
    $agency_id = $this->get_user_agency_id();
    
    if (!$conversation_uuid) {
        ajax_return(['success' => false, 'message' => 'Conversation UUID required']);
        return;
    }
    
    $conversation = $this->{$this->model}->get_conversation_for_agency_by_uuid($conversation_uuid, $agency_id);
    
    if (!$conversation) {
        ajax_return(['success' => false, 'message' => 'Access denied']);
        return;
    }
    
    // Mark messages as read
    $this->{$this->model}->mark_messages_as_read($conversation->id, 'agency');
    
    // Get messages
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
    
    $html = '';
    $last_id = $last_message_id;
    $has_new_messages = false;
    
    foreach ($messages as $message) {
        $message_html = $this->load->view('agency/chat/message_item', [
            'message' => $message, 
            'current_user_type' => 'agency'
        ], true);
        
        $html .= $message_html;
        $has_new_messages = true;
        
        if ($message->id > $last_id) {
            $last_id = $message->id;
        }
    }
    
    $response = [
        'success' => true,
        'html' => $html,
        'last_message_id' => $last_id,
        'has_new_messages' => $has_new_messages,
        'csrf' => $this->security->get_csrf_hash()
    ];
    
    ajax_return($response);
}

public function ajax_get_conversations()
{
    // This should validate CSRF too if it's a POST request
    // Or change it to GET request if it's truly read-only
    
    $agency_id = $this->get_user_agency_id();
    
    if (!$agency_id) {
        ajax_return(['success' => false, 'message' => 'Agency not logged in']);
        return;
    }
    
    $conversations = $this->{$this->model}->get_agency_conversations($agency_id);
    
    $total_unread_count = 0;
    foreach ($conversations as $conv) {
        $total_unread_count += isset($conv->unread_count) ? $conv->unread_count : 0;
    }
    
    ajax_return([
        'success' => true,
        'conversations' => $conversations,
        'total_unread_count' => $total_unread_count,
        'csrf' => $this->security->get_csrf_hash()
    ]);
}

    private function get_user_agency_id()
    {
        $login = $this->session->userdata('login');
        
        if (empty($login['agency'])) {
            return null;
        }
        
        $agency_user = $login['agency'];
        
        if (!empty($agency_user['agency_id'])) {
            return $agency_user['agency_id'];
        } elseif (!empty($agency_user['id'])) {
            return $agency_user['id'];
        } elseif (!empty($agency_user['agency']['id'])) {
            return $agency_user['agency']['id'];
        }
        
        return null;
    }

public function ajax_test_csrf()
{
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_hash = $this->security->get_csrf_hash();
    
    // Check ALL possible ways CSRF could be sent
    $post_token = $this->input->post($csrf_name);
    $get_token = $this->input->get($csrf_name);
    
    // Also check raw input
    $raw_input = file_get_contents('php://input');
    $raw_csrf = null;
    if ($raw_input) {
        parse_str($raw_input, $parsed_input);
        $raw_csrf = isset($parsed_input[$csrf_name]) ? $parsed_input[$csrf_name] : null;
    }
    
    $response = [
        'success' => true,
        'message' => 'CSRF Test Complete',
        'csrf' => $csrf_hash,
        'debug' => [
            'csrf_name' => $csrf_name,
            'csrf_hash' => $csrf_hash,
            'post_csrf_found' => !empty($post_token),
            'get_csrf_found' => !empty($get_token),
            'raw_csrf_found' => !empty($raw_csrf),
            'input_post_data' => $this->input->post(),
            '$_POST_data' => $_POST,
            'raw_input' => $raw_input,
            'request_method' => $this->input->method(),
            'content_type' => isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'Not set'
        ]
    ];
    
    ajax_return($response);
}
}