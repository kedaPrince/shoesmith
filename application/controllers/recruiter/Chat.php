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
    // ===== START: CSRF FIX FOR AJAX REQUESTS =====
   
    $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // Also check for POST data with X-Requested-With header
    if (!$is_ajax && isset($_POST['X-Requested-With'])) {
        $is_ajax = true;
    }
    
    
    if ($is_ajax) {
        // Store original CSRF setting
        $original_csrf = true; // Assume it's enabled
        
        // Check current config if we can
        if (function_exists('config_item')) {
            $original_csrf = config_item('csrf_protection');
        }
        
        // Disable CSRF BEFORE parent constructor
        if (class_exists('CI_Controller')) {
            
            $_POST['_ci_csrf_override'] = true; 
        }
    }
    // ===== END: CSRF FIX =====
    
    // Now call parent constructor
    parent::__construct();
    
    $this->folder = 'recruiter';
    $this->load->model('recruiter/Model_chat_messages');
    $this->load->model('recruiter/Model_notifications');
    $this->load->helper('csrf');
    
    $login_data = $this->session->userdata('login');
    if (empty($login_data['recruiter'])) {
        redirect('recruiter/login');
    }
}

public function ajax_check_online_status()
{
    // PREVENT CACHING
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    $response = [
        'success' => false,
        'online_status' => [],
        'csrf_token' => $this->security->get_csrf_hash(),
        'timestamp' => microtime(true),
        'generated_at' => date('Y-m-d H:i:s'),
        'debug' => []
    ];
    
    try {
        $recruiter_id = $this->get_recruiter_id();
        
        if (!$recruiter_id) {
            $response['message'] = 'Recruiter not logged in';
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
            return;
        }
        
        // Get available agencies for this recruiter
        $available_agencies = $this->Model_chat_messages->get_available_agencies_simple($recruiter_id);
        
        $five_minutes_ago = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $onlineStatus = [];
        
        foreach ($available_agencies as $agency) {
            // Check if agency has active session in user_sessions
            $this->db->where('user_id', $agency->id)
                     ->where('user_type', 'agency')
                     ->where('last_activity >=', $five_minutes_ago);
            $active_session = $this->db->get('user_sessions')->row();
            
            $is_online = false;
            
            if ($active_session) {
                // Has active session in last 5 minutes
                $is_online = true;
            } else {
                // Fallback: Check agencies table
                $this->db->where('id', $agency->id);
                $this->db->group_start();
                $this->db->where('last_activity_at >=', $five_minutes_ago);
                $this->db->or_where('last_login >=', $five_minutes_ago);
                $this->db->group_end();
                $agency_row = $this->db->get('agencies')->row();
                
                $is_online = !empty($agency_row);
            }
            
            $onlineStatus[$agency->id] = $is_online;
        }
        
        $response['success'] = true;
        $response['online_status'] = $onlineStatus;
        $response['online_count'] = count(array_filter($onlineStatus));
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
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
        redirect('recruiter/chat/conversation/' . $conversations[0]->uuid);
        return;
    }
    
    // If no conversations but has agencies, create first conversation with first agency
    if (!empty($available_agencies)) {
        $conversation = $this->{$this->model}->get_or_create_conversation(
            $available_agencies[0]->id, 
            $recruiter_id
        );
        
        if ($conversation) {
            redirect('recruiter/chat/conversation/' . $conversation->uuid);
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
    
    // ADD THIS: Get detailed candidate information for onboarding display
    $candidate_details = null;
    if ($conversation->candidate_id) {
        // Load candidates model
        $this->load->model('recruiter/Model_candidates');
        $candidate_details = $this->Model_candidates->get_candidate_details($conversation->candidate_id);
    }
    
    $this->breadcrumbs = [
        ['title' => lang('chat_heading'), 'url' => url('chat')],
        ['title' => $conversation->agency_name, 'url' => '']
    ];
    
    // ADD CSRF TOKEN TO VIEW DATA
    $csrf = array(
        'name' => $this->security->get_csrf_token_name(),
        'hash' => $this->security->get_csrf_hash()
    );
    
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
        'agency_online' => $agency_online,
        'candidate_details' => $candidate_details,
        'csrf_token' => $csrf, // ADD THIS LINE
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


 protected function validate_csrf_with_buffer()
{
    return csrf_safe_validate($this, 10); // 10 second buffer
}
    
protected function ajax_response($data = [], $success = true)
{
    $csrf_data = get_csrf_response_data($this);
    $response = array_merge($data, $csrf_data);
    $response['success'] = $success;
    
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}
    
protected function check_csrf()
{
// Skip CSRF check for GET requests
if ($this->input->method() === 'get') {
    return true;
}

// Check CSRF token
$csrf_name = $this->security->get_csrf_token_name();
$csrf_token = $this->input->post($csrf_name);

if (!$csrf_token) {
    // Try to get from header
    $csrf_token = $this->input->get_request_header('X-CSRF-Token');
}

if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
    // Return JSON response instead of showing HTML error
    $this->output
        ->set_content_type('application/json')
        ->set_status_header(403)
        ->set_output(json_encode([
            'success' => false,
            'message' => 'CSRF token validation failed',
            'csrf_invalid' => true,
            'needs_retry' => true,
            'csrf_token' => $this->security->get_csrf_hash()
        ]));
    return false;
}

return true;
}


public function ajax_send_message()
{
    // Always return JSON with CSRF token
    $response = [
        'success' => false,
        'message' => '',
        'csrf_token' => $this->security->get_csrf_hash()
    ];
    
    // ===== FIX: Accept both GET and POST =====
    $conversation_uuid = $this->input->get_post('conversation_uuid');
    $message_text = $this->input->get_post('message');
    $recruiter_id = $this->get_recruiter_id();
    
    // ===== FIX: Skip CSRF for GET requests =====
    if ($this->input->method() === 'get') {
        $_GET['_ci_csrf_override'] = true;
    }

    
    if (!$recruiter_id) {
        $response['message'] = 'Session expired. Please refresh the page.';
        $response['session_expired'] = true;
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
        return;
    }
    
    if (!$conversation_uuid || !$message_text) {
        $response['message'] = 'Missing required parameters';
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
        return;
    }
    
    $conversation = $this->Model_chat_messages->get_conversation_for_recruiter_by_uuid(
        $conversation_uuid, 
        $recruiter_id
    );
    
    if (!$conversation) {
        $response['message'] = 'Conversation not found or access denied';
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
        return;
    }
    
    $message_id = $this->Model_chat_messages->send_message(
        $conversation->id,
        'recruiter',
        $recruiter_id,
        $message_text,
        'text',
        null
    );
    
    if ($message_id) {
        $response['success'] = true;
        $response['message'] = 'Message sent successfully';
        $response['message_id'] = $message_id;
        
        // Generate fresh CSRF token for next request
        $response['csrf_token'] = $this->security->get_csrf_hash();
    } else {
        $response['message'] = 'Failed to save message';
    }
    
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}
    

public function ajax_get_messages()
{
    // Always return JSON with CSRF token
    $response = [
        'success' => false,
        'message' => '',
        'csrf_token' => $this->security->get_csrf_hash()
    ];
    
    // Get parameters from either GET or POST
    $conversation_uuid = $this->input->get_post('conversation_uuid');
    $last_message_id = $this->input->get_post('last_message_id') ?: 0;
    $recruiter_id = $this->get_recruiter_id();
    
    if (!$conversation_uuid) {
        $response['message'] = 'Conversation UUID required';
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
        return;
    }
    
    $conversation = $this->Model_chat_messages->get_conversation_for_recruiter_by_uuid(
        $conversation_uuid, 
        $recruiter_id
    );
    
    if (!$conversation) {
        $response['message'] = 'Access denied';
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
        return;
    }
    
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
    
    
    $latest_message_id = $last_message_id;
    if (!empty($messages)) {
        $last_message = end($messages);
        $latest_message_id = $last_message->id;
    }
    
    $response['success'] = true;
    $response['messages'] = $messages;
    $response['last_message_id'] = $latest_message_id;
    $response['has_new_messages'] = !empty($messages);
    
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}
    
public function ajax_get_conversations()
{// Check CSRF first

    if (!$this->validate_csrf_with_buffer()) {
    
            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Security token expired. Please refresh.',
                    'csrf_invalid' => true,
                    'needs_retry' => true,
                    'csrf_token' => $this->security->get_csrf_hash() // Always return fresh token
                ]));
            return;
        }
    
        $recruiter_id = $this->get_recruiter_id();
        
        if (!$recruiter_id) {
            $this->ajax_response([
                'message' => 'Recruiter not logged in'
            ], false);
            return;
        }

    try {
        $conversations = $this->Model_chat_messages->get_recruiter_conversations($recruiter_id);
        $total_unread_count = 0;
        
        $formatted_conversations = [];
        foreach ($conversations as $conv) {
            $formatted_conversations[] = [
                'id' => $conv->id,
                'uuid' => $conv->uuid,
                'agency_name' => $conv->agency_name,
                'last_message' => $conv->last_message,
                'last_message_at' => $conv->last_message_at,
                'last_sender_type' => isset($conv->last_sender_type) ? $conv->last_sender_type : 'agency',
                'unread_count' => isset($conv->unread_count) ? $conv->unread_count : 0,
                'is_online' => isset($conv->is_online) ? $conv->is_online : false,
                'agency_id' => isset($conv->agency_id) ? $conv->agency_id : 0
            ];
            
            $total_unread_count += isset($conv->unread_count) ? $conv->unread_count : 0;
        }

        $this->ajax_response([
            'conversations' => $formatted_conversations,
            'total_unread_count' => $total_unread_count
        ], true);

    } catch (Exception $e) {
        $this->ajax_response([
            'message' => 'Server error'
        ], false);
    }
}
    
public function ajax_upload_documents()
{
    if ($this->input->method() === 'post') {
        $_POST['_ci_csrf_override'] = true;
    }
    
    header('Content-Type: application/json');

    $response = [
        'success' => false,
        'message' => 'Unknown error',
        'csrf_token' => $this->security->get_csrf_hash()
    ];
    
    try {
        $recruiter_id = $this->get_recruiter_id();
        if (!$recruiter_id) {
            $response['message'] = 'Session expired';
            echo json_encode($response);
            return;
        }
        
        $conversation_uuid = $this->input->post('conversation_uuid') ?: $this->input->get('conversation_uuid');
        $candidate_id = $this->input->post('candidate_id') ?: $this->input->get('candidate_id');
        
        if (!$conversation_uuid) {
            $response['message'] = 'Missing required parameter: conversation_uuid';
            echo json_encode($response);
            return;
        }
        
        if (empty($_FILES['documents'])) {
            $response['message'] = 'No files uploaded';
            echo json_encode($response);
            return;
        }
        
        
        if ($candidate_id) {
            $upload_path = FCPATH . 'uploads/candidate_documents/' . $candidate_id . '/';
            $relative_path = 'uploads/candidate_documents/' . $candidate_id . '/';
        } else {
            $upload_path = FCPATH . 'uploads/chat_documents/' . $recruiter_id . '/';
            $relative_path = 'uploads/chat_documents/' . $recruiter_id . '/';
        }
        
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
            $htaccess_content = "# Apache 2.4 security\n";
            $htaccess_content .= "Require all granted\n";
            $htaccess_content .= "Options -Indexes\n";
            $htaccess_content .= "<FilesMatch \"\.(php|phtml|inc|exe|dll|bat|cmd)$\">\n";
            $htaccess_content .= "    Require all denied\n";
            $htaccess_content .= "</FilesMatch>\n";

            file_put_contents($upload_path . '.htaccess', $htaccess_content);
        }
        
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'pdf|doc|docx|txt|jpg|jpeg|png|xls|xlsx';
        $config['max_size'] = 10240;
        $config['encrypt_name'] = true;
        $config['remove_spaces'] = true;
        
        $this->load->library('upload', $config);
        
        $uploaded_documents = [];
        $files = $_FILES['documents'];
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] == 0) {
                $_FILES['file']['name'] = $files['name'][$i];
                $_FILES['file']['type'] = $files['type'][$i];
                $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
                $_FILES['file']['error'] = $files['error'][$i];
                $_FILES['file']['size'] = $files['size'][$i];
                
                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    
                    $document_data = [
                        'document_name' => $files['name'][$i],
                        'file_name' => $upload_data['file_name'],
                        'file_path' => $relative_path . $upload_data['file_name'],
                        'file_type' => $upload_data['file_type'],
                        'file_size' => $upload_data['file_size'],
                        'uploaded_by' => $recruiter_id,
                        'uploaded_by_type' => 'recruiter',
                        'uploaded_from_chat' => 1,
                        'conversation_uuid' => $conversation_uuid,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    if ($candidate_id) {
                        $document_data['candidate_id'] = $candidate_id;
                    }
                    
                    $this->db->insert('candidate_documents', $document_data);
                    
                    if ($this->db->affected_rows() > 0) {
                        $document_id = $this->db->insert_id();
                        $uploaded_documents[] = [
                            'id' => $document_id,
                            'name' => $files['name'][$i],
                            'path' => base_url($document_data['file_path'])
                        ];
                        
                    }
                } else {
                }
            }
        }
        
        if (!empty($uploaded_documents)) {
            $response['success'] = true;
            $response['message'] = count($uploaded_documents) . ' document(s) uploaded successfully';
            $response['documents'] = $uploaded_documents;
        } else {
            $response['message'] = 'No files were successfully uploaded';
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Server error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
}
    
private function get_recruiter_id()
{
    $login_data = $this->session->userdata('login');
    return !empty($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : null;
}

// Add this to your Chat controller
public function ajax_check_session()
{
    $recruiter_id = $this->get_recruiter_id();
    
    if (!$recruiter_id) {
        ajax_return([
            'success' => false,
            'message' => 'Not logged in'
        ]);
        return;
    }
    
    ajax_return([
        'success' => true,
        'message' => 'Session valid'
    ]);
}

/**
 * AJAX: Get unread count for menu badge
 */
public function ajax_get_unread_count()
{
$recruiter_id = $this->get_recruiter_id();
$unread_count = $this->{$this->model}->get_unread_count_for_recruiter($recruiter_id);

ajax_return([
    'success' => true, 
    'unread_count' => $unread_count,
    'csrf_token' => $this->security->get_csrf_hash()
]);
}

public function ajax_start_conversation()
{
    $agency_id = $this->input->post('agency_id');
    $subject = $this->input->post('subject');
    $initial_message = $this->input->post('initial_message');
    $recruiter_id = $this->get_recruiter_id();
    
    if (empty($agency_id)) {
        ajax_return([
            'success' => false, 
            'message' => 'Please select an agency',
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
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
            'conversation_uuid' => $conversation->uuid,
            'redirect_url' => site_url('recruiter/chat/conversation/' . $conversation->uuid),
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
    } else {
        ajax_return([
            'success' => false, 
            'message' => 'Failed to create conversation',
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
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
        redirect('recruiter/chat/conversation/' . $conversation->uuid);
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

  
public function ajax_get_chat_notifications()
{
    $recruiter_id = $this->get_recruiter_id();
    
    $response = [
        'success' => false,
        'unread_count' => 0,
        'csrf_token' => $this->security->get_csrf_hash()
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
        $response['csrf_token'] = $this->security->get_csrf_hash();

    } catch (Exception $e) {
        // Log error but don't break the functionality
        $response['csrf_token'] = $this->security->get_csrf_hash();
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}


public function ajax_mark_notifications_read()
{
    $conversation_id = $this->input->post('conversation_id');
    $recruiter_id = $this->get_recruiter_id();
    
    if (!$conversation_id || !$recruiter_id) {
        ajax_return([
            'success' => false,
            'message' => 'Invalid parameters',
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    
    $conversation = $this->{$this->model}->get_conversation_for_recruiter($conversation_id, $recruiter_id);
    
    if (!$conversation) {
        ajax_return([
            'success' => false,
            'message' => 'Access denied',
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
        return;
    }
    
    $this->{$this->model}->mark_messages_as_read($conversation_id, 'recruiter');
    
    $unread_count = $this->{$this->model}->get_unread_count_for_recruiter($recruiter_id);
    
    ajax_return([
        'success' => true,
        'message' => 'Notifications marked as read',
        'unread_count' => $unread_count,
        'csrf_token' => $this->security->get_csrf_hash()
    ]);
}
/**
 * Start a job chat - creates/redirects to chat about a specific job
 */
public function start_job_chat($job_uuid)
{
    
    // Get recruiter ID from session
    $login_data = $this->session->userdata('login');
    $recruiter_id = !empty($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : null;
    
    if (!$recruiter_id) {
        redirect('recruiter/dashboard');
    }


    // Get job details by UUID
    $this->db->select('mod_jobs.*, agencies.name as agency_name, agencies.id as agency_id');
    $this->db->from('mod_jobs');
    $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
    $this->db->where('mod_jobs.uuid', $job_uuid);
    
    // Get user agency ID for filtering (if needed)
    $user_agency_id = !empty($login_data['recruiters']['agency_id']) ? $login_data['recruiters']['agency_id'] : null;
    
    if ($user_agency_id) {
        $this->db->where('mod_jobs.agency_id', $user_agency_id);
    }
    
    $job = $this->db->get()->row();
    
    if (!$job) {
        show_404();
    }


    // Load Chat model
    $this->load->model('recruiter/Model_chat_messages');
    
    // Check if conversation exists for this job and recruiter
    $this->db->select('*');
    $this->db->from('chat_conversations');
    $this->db->where('job_id', $job->id);
    $this->db->where('recruiter_id', $recruiter_id);
    $this->db->where('removed', 0);
    $this->db->order_by('created_at', 'DESC');
    $this->db->limit(1);
    
    $existing_conversation = $this->db->get()->row();
    
    if ($existing_conversation) {
        // Redirect to existing conversation
        redirect('recruiter/chat/conversation/' . $existing_conversation->uuid);
        return;
    }
    
    
    // Create new conversation
    $conversation_uuid = bin2hex(random_bytes(16));
    
    $conversation_data = [
        'uuid' => $conversation_uuid,
        'agency_id' => $job->agency_id,
        'recruiter_id' => $recruiter_id,
        'job_id' => $job->id,
        'candidate_id' => NULL, // This is a job chat, not candidate chat
        'title' => 'Job: ' . $job->name,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'removed' => 0,
        'enabled' => 1
    ];
    
    if ($this->db->insert('chat_conversations', $conversation_data)) {
        // Redirect to the new conversation
        redirect('recruiter/chat/conversation/' . $conversation_uuid);
    } else {
        show_error('Failed to create chat conversation. Please try again.');
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
$this->db->where('sender_type !=', $reader_type);
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
$this->db->where('cm.sender_type', 'agency');
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
    return (time() - $last_activity) < 300;
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
        return false;
    }
    
    $conversation_id = $this->db->insert_id();
    
    return $this->db->where('id', $conversation_id)->get('chat_conversations')->row();
}

public function download_document($candidate_id, $filename)
{
    $file_path = FCPATH . 'uploads/candidate_documents/' . $candidate_id . '/' . $filename;
    
    // Security check
    $recruiter_id = $this->get_recruiter_id();
    if (!$recruiter_id) {
        show_404();
    }
    
    // Verify the recruiter has access to this candidate
    $this->load->model('recruiter/Model_candidates');
    $has_access = $this->Model_candidates->check_recruiter_access($candidate_id, $recruiter_id);
    
    if (!$has_access) {
        show_404();
    }
    
    // Check if file exists
    if (!file_exists($file_path)) {
        show_404();
    }
    
    // Get the file's mime type
    $mime = mime_content_type($file_path);
    
    // Set headers
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Output the file
    readfile($file_path);
    exit;
}
}