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
    
    // Set security headers
    $this->set_security_headers();
    
    // Load agency-specific chat model
    $this->load->model('agency/Model_chat_messages');
    
    // Load notifications model
    $this->load->model('agency/Model_notifications');
    
    // Verify agency access
    $login_data = $this->session->userdata('login');
    if (empty($login_data['agency'])) {
        redirect('agency/login');
    }

    $this->agency_id = $this->session->userdata('agency_id');
    
    // Load models - CORRECT PATH
    $this->load->model('agency/Model_chat_messages', 'chat_model'); // Load with alias
}

private function set_security_headers() {
    // Check if headers are already sent
    if (!headers_sent()) {
        // Set security headers
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Set CSP header (adjust based on environment)
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval'; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com; ";
        $csp .= "img-src 'self' data:; ";
        $csp .= "connect-src 'self'; ";
        $csp .= "frame-ancestors 'none';";
        
        header("Content-Security-Policy: " . $csp);
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
    
    // Get conversation by UUID
    $conversation = $this->{$this->model}->get_conversation_for_agency_by_uuid($uuid, $agency_id);
    
    if (!$conversation) {
        show_404();
    }
    
    // Mark messages as read
    $this->{$this->model}->mark_messages_as_read($conversation->id, 'agency');
    
    // Get messages
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
    
    // GET CANDIDATE DETAILS IF CONVERSATION HAS CANDIDATE - ADD THIS
    $candidate_details = null;
    if ($conversation->candidate_id) {
        $this->load->model('agency/Model_candidates');
        $candidate_details = $this->Model_candidates->get_candidate_details($conversation->candidate_id);
    }
    
    // Also get candidate info for each conversation in sidebar
    $all_conversations_with_candidates = [];
    foreach ($all_conversations as $conv) {
        $conv->candidate_info = null;
        if ($conv->candidate_id) {
            $this->db->select('first_name, last_name, reference_number, email');
            $this->db->from('candidates');
            $this->db->where('id', $conv->candidate_id);
            $conv->candidate_info = $this->db->get()->row();
        }
        $all_conversations_with_candidates[] = $conv;
    }
    
    $this->breadcrumbs = [
        ['title' => lang('chat_heading'), 'url' => site_url('agency/chat')],
        ['title' => 'Conversation with ' . htmlspecialchars($conversation->recruiter_name), 'url' => '']
    ];
    
    $this->load->view($this->folder . '/view_header');
    $this->load->view('agency/chat/conversation', [
        'conversation' => $conversation,
        'messages' => $messages,
        'all_conversations' => $all_conversations_with_candidates, // Updated with candidate info
        'available_recruiters' => $available_recruiters,
        'heading' => lang('chat_heading'),
        'agency_id' => $agency_id,
        'total_unread_count' => $total_unread_count,
        'recent_notifications' => $recent_notifications,
        'total_message_count' => $total_message_count,
        'candidate_details' => $candidate_details,
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

    

  

   public function ajax_get_unread_count()
{
    $agency_id = $this->get_user_agency_id();
    $unread_count = 0;
    
    if ($agency_id) {
        $unread_count = $this->{$this->model}->get_unread_count_for_agency($agency_id);
    }
    
    // Return JSON response
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => true,
            'unread_count' => $unread_count
        ]));
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

// Add at the top of your methods
public function ajax_send_message()
{
    // DISABLE error display for clean JSON
    ini_set('display_errors', 0);
    error_reporting(0);
    
    // Set proper headers FIRST
    header('Content-Type: application/json; charset=UTF-8');
    
    // Validate CSRF
    if (!$this->validate_csrf_token()) {
        return; // validate_csrf_token already outputs JSON
    }
    
    // Get inputs
    $conversation_uuid = $this->input->post('conversation_uuid');
    $message_text = $this->input->post('message');
    
    // Basic validation
    if (empty($conversation_uuid) || empty($message_text)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    
    // Get agency ID
    $agency_id = $this->get_user_agency_id();
    if (!$agency_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Not logged in',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    
    // Verify conversation
    $conversation = $this->{$this->model}->get_conversation_for_agency_by_uuid($conversation_uuid, $agency_id);
    if (!$conversation) {
        echo json_encode([
            'success' => false,
            'message' => 'Conversation not found',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    
    // Sanitize message (using your helper)
    $this->load->helper('security');
    $message_text = sanitize_message($message_text);
    
    // Send message
    $message_id = $this->{$this->model}->send_message(
        $conversation->id,
        'agency',
        $agency_id,
        $message_text
    );
    
    if ($message_id) {
        echo json_encode([
            'success' => true,
            'message' => 'Message sent',
            'message_id' => $message_id,
            'csrf' => $this->security->get_csrf_hash()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send message',
            'csrf' => $this->security->get_csrf_hash()
        ]);
    }
    
    // Exit to prevent any other output
    exit();
}



public function ajax_get_messages()
{
    if (!$this->validate_csrf_token()) {
        return;
    }
    
    $conversation_uuid = $this->input->post('conversation_uuid');
    $last_message_id = $this->input->post('last_message_id') ? (int)$this->input->post('last_message_id') : 0;
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
    
    // Get ONLY NEW messages (messages with ID > last_message_id)
    $this->db->select('cm.*');
    $this->db->from('chat_messages cm');
    $this->db->where('cm.conversation_id', $conversation->id);
    
    // CRITICAL: Only get messages AFTER last_message_id
    if ($last_message_id > 0) {
        $this->db->where('cm.id >', $last_message_id);
    } else {
        // If last_message_id is 0, get last 20 messages only (not all)
        $this->db->order_by('cm.id', 'DESC');
        $this->db->limit(20);
    }
    
    $this->db->where('cm.enabled', 1);
    $this->db->where('cm.removed', 0);
    $this->db->order_by('cm.created_at', 'ASC');
    
    $query = $this->db->get();
    $messages = $query->result();
    
    $html = '';
    $last_id = $last_message_id;
    
    foreach ($messages as $message) {
        // Render each message
        $message_html = $this->load->view('agency/chat/message_item', [
            'message' => $message, 
            'current_user_type' => 'agency'
        ], true);
        
        $html .= $message_html;
        
        if ($message->id > $last_id) {
            $last_id = $message->id;
        }
    }
    
    $response = [
        'success' => true,
        'html' => $html,
        'last_message_id' => $last_id,
        'total_messages' => count($messages),
        'csrf' => $this->security->get_csrf_hash()
    ];
    
    ajax_return($response);
}

public function ajax_get_conversations()
{
    // ✅ ADD CSRF VALIDATION if this is a POST request
    if ($this->input->server('REQUEST_METHOD') === 'POST') {
        if (!$this->validate_csrf_token()) {
            return;
        }
    }
    
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

/**
 * AJAX: Get chat notification count for top bar
 */
public function ajax_get_chat_notifications()
{
    $login_data = $this->session->userdata('login');
    $agency_staff_id = !empty($login_data['agency']['id']) ? $login_data['agency']['id'] : null;
    
    $response = [
        'success' => false,
        'unread_count' => 0,
        'message' => ''
    ];

    try {
        if (!$agency_staff_id) {
            $response['message'] = 'Agency not logged in';
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        // First, check if we have the chat model loaded
        if (!isset($this->Model_chat_messages)) {
            $this->load->model('agency/Model_chat_messages');
        }
        
        // Get chat-specific unread count
        $unread_count = $this->Model_chat_messages->get_unread_count_for_agency($agency_staff_id);
        
        $response['unread_count'] = (int)$unread_count;
        $response['success'] = true;

    } catch (Exception $e) {
        $response['message'] = 'Server error: ' . $e->getMessage();
        log_message('error', 'Chat notification error: ' . $e->getMessage());
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

public function switch_to_general($conversation_uuid, $recruiter_id)
{
    // ✅ ADD CSRF VALIDATION for POST requests
    if ($this->input->server('REQUEST_METHOD') === 'POST') {
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            flash_notification('Invalid CSRF token. Please refresh and try again.', 'error');
            redirect('agency/chat');
            return;
        }
    }
    
    // Get agency ID
    $agency_id = $this->get_user_agency_id();
    
    if (!$agency_id) {
        redirect('agency/login');
        return;
    }
    
    // Check if a general conversation already exists with this recruiter
    $existing_general = $this->chat_model->get_general_conversation($agency_id, $recruiter_id);
    
    if ($existing_general) {
        // Redirect to existing general conversation
        redirect('agency/chat/conversation/' . $existing_general->uuid);
    } else {
        // Create a new general conversation
        $general_data = [
            'agency_id' => $agency_id,
            'recruiter_id' => $recruiter_id,
            'candidate_id' => null, // Null for general chat
            'job_id' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'uuid' => $this->chat_model->generate_uuid()
        ];
        
        $new_conversation_id = $this->chat_model->create_conversation($general_data);
        
        if ($new_conversation_id) {
            $new_conversation = $this->chat_model->get_conversation_by_id($new_conversation_id);
            redirect('agency/chat/conversation/' . $new_conversation->uuid);
        } else {
            show_error('Failed to create general conversation');
        }
    }
}

public function ajax_switch_chat_to_general()
{
    // Check if this is an AJAX request
    if (!$this->input->is_ajax_request()) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request'
        ]);
        return;
    }
    
    // ✅ ADD PROPER CSRF VALIDATION
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->post($csrf_name);
    
    if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid CSRF token. Please refresh and try again.',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    
    // Use the correct method to get agency_id
    $agency_id = $this->get_user_agency_id();
    
    $conversation_uuid = $this->input->post('conversation_uuid');
    $recruiter_id = $this->input->post('recruiter_id');
    
    if (!$agency_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Agency not logged in',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    
    if (!$conversation_uuid || !$recruiter_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters',
            'csrf' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    
    // Check if a general conversation already exists with this recruiter
    $existing_general = $this->chat_model->get_general_conversation($agency_id, $recruiter_id);
    
    if ($existing_general) {
        // Redirect to existing general conversation
        $response = [
            'success' => true,
            'general_conversation_uuid' => $existing_general->uuid,
            'message' => 'Switched to existing general conversation',
            'csrf' => $this->security->get_csrf_hash()
        ];
    } else {
        // Create a new general conversation
        $general_data = [
            'agency_id' => $agency_id,
            'recruiter_id' => $recruiter_id,
            'candidate_id' => null, // Null for general chat
            'job_id' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'uuid' => $this->chat_model->generate_uuid()
        ];
        
        $new_conversation_id = $this->chat_model->create_conversation($general_data);
        
        if ($new_conversation_id) {
            $new_conversation = $this->chat_model->get_conversation_by_id($new_conversation_id);
            
            $response = [
                'success' => true,
                'general_conversation_uuid' => $new_conversation->uuid,
                'message' => 'Created new general conversation',
                'csrf' => $this->security->get_csrf_hash()
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to create general conversation',
                'csrf' => $this->security->get_csrf_hash()
            ];
        }
    }
    
    echo json_encode($response);
}
// Add this property to your Chat class
protected $rate_limits = [
    'send_message' => ['limit' => 10, 'window' => 60], // 10 messages per minute
    'get_messages' => ['limit' => 30, 'window' => 60], // 30 requests per minute
];

// Add this method

// In Chat controller - Replace the check_rate_limit method entirely:
private function check_rate_limit($action) {
    // Temporarily disable rate limiting to fix CSRF issue
    return true;
    
    /* Comment out or remove the old code:
    $ip = $this->input->ip_address();
    $user_id = $this->get_user_agency_id();
    $key = "rate_limit_{$action}_{$user_id}_{$ip}";
    
    $this->load->driver('cache', ['adapter' => 'file']);
    */
}

}