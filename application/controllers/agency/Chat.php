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

        $this->agency_id = $this->get_user_agency_id();
        
        // Load models
        $this->load->model('agency/Model_chat_messages', 'chat_model');
    }

    private function set_security_headers() {
        if (!headers_sent()) {
            header('X-Frame-Options: DENY');
            header('X-Content-Type-Options: nosniff');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
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
     * Main chat page
     */
    public function index()
    {
        $agency_id = $this->get_user_agency_id();
        
        // Get all conversations
        $conversations = $this->{$this->model}->get_agency_conversations($agency_id);
        
        // Get available recruiters for new chats
        $available_recruiters = $this->{$this->model}->get_available_recruiters($agency_id);
        
        if (!empty($conversations)) {
            redirect('agency/chat/conversation/' . $conversations[0]->uuid);
            return;
        }
        
        if (!empty($available_recruiters)) {
            $conversation = $this->{$this->model}->get_or_create_conversation(
                $agency_id, 
                $available_recruiters[0]->id
            );
            
            if ($conversation) {
                redirect('agency/chat/conversation/' . $conversation->uuid);
                return;
            }
        }
        
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
        
        // GET CANDIDATE DETAILS IF CONVERSATION HAS CANDIDATE - FIXED
        $candidate_details = null;
        if ($conversation->candidate_id) {
            $this->load->model('agency/Model_candidates');
            // Pass agency_id as the second parameter
            $candidate_details = $this->Model_candidates->get_candidate_details(
                $conversation->candidate_id, 
                $agency_id  // Add this parameter
            );
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
        
        // ADD CSRF TOKEN TO VIEW DATA
        $csrf = array(
            'name' => $this->security->get_csrf_token_name(),
            'hash' => $this->security->get_csrf_hash()
        );
        
        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/chat/conversation', [
            'conversation' => $conversation,
            'messages' => $messages,
            'all_conversations' => $all_conversations_with_candidates,
            'available_recruiters' => $available_recruiters,
            'heading' => lang('chat_heading'),
            'agency_id' => $agency_id,
            'total_unread_count' => $total_unread_count,
            'recent_notifications' => $recent_notifications,
            'total_message_count' => $total_message_count,
            'candidate_details' => $candidate_details,
            'csrf_token' => $csrf, // ADD THIS LINE
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

    /**
     * Start new conversation with quick link
     */
    public function quick_start($recruiter_id = null)
    {
        $agency_id = $this->get_user_agency_id();
        
        if (!$recruiter_id) {
            show_404();
        }
        
        $conversation = $this->{$this->model}->get_or_create_conversation($agency_id, $recruiter_id);
        
        if ($conversation) {
            redirect('agency/chat/conversation/' . $conversation->uuid);
        } else {
            show_error('Failed to create conversation');
        }
    }

    /**
     * AJAX: Send message
     */
    public function ajax_send_message()
    {
        // Set proper headers FIRST
        header('Content-Type: application/json; charset=UTF-8');
        
        // Add debug logging
        log_message('debug', 'Agency ajax_send_message called');
        
        // Get inputs
        $conversation_uuid = $this->input->post('conversation_uuid');
        $message_text = $this->input->post('message');
        
        log_message('debug', 'Conversation UUID: ' . $conversation_uuid);
        log_message('debug', 'Message text: ' . ($message_text ? 'PROVIDED' : 'EMPTY'));
        
        // Basic validation
        if (empty($conversation_uuid) || empty($message_text)) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            exit();
        }
        
        // Get agency ID
        $agency_id = $this->get_user_agency_id();
        if (!$agency_id) {
            log_message('error', 'Agency not logged in for ajax_send_message');
            echo json_encode([
                'success' => false,
                'message' => 'Not logged in',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            exit();
        }
        
        // Verify conversation
        $conversation = $this->{$this->model}->get_conversation_for_agency_by_uuid($conversation_uuid, $agency_id);
        if (!$conversation) {
            log_message('error', 'Conversation not found for agency. UUID: ' . $conversation_uuid . ', Agency ID: ' . $agency_id);
            echo json_encode([
                'success' => false,
                'message' => 'Conversation not found',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            exit();
        }
        
        // Sanitize message
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
            log_message('debug', 'Agency message sent successfully. ID: ' . $message_id);
            echo json_encode([
                'success' => true,
                'message' => 'Message sent',
                'message_id' => $message_id,
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
        } else {
            log_message('error', 'Failed to send agency message');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send message',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
        }
        
        exit();
    }

    public function ajax_get_messages()
    {
        // Set JSON header FIRST
        header('Content-Type: application/json; charset=UTF-8');
        
        // Add debug logging
        log_message('debug', 'Agency ajax_get_messages called');
        
        $conversation_uuid = $this->input->post('conversation_uuid');
        $last_message_id = $this->input->post('last_message_id') ? (int)$this->input->post('last_message_id') : 0;
        $agency_id = $this->get_user_agency_id();
        
        log_message('debug', 'Agency ID: ' . ($agency_id ?: 'NOT FOUND'));
        log_message('debug', 'Conversation UUID: ' . $conversation_uuid);
        log_message('debug', 'Last Message ID: ' . $last_message_id);
        
        if (!$agency_id) {
            echo json_encode([
                'success' => false, 
                'message' => 'Agency not logged in',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            exit();
        }
        
        if (!$conversation_uuid) {
            echo json_encode([
                'success' => false, 
                'message' => 'Conversation UUID required',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            exit();
        }
        
        $conversation = $this->{$this->model}->get_conversation_for_agency_by_uuid($conversation_uuid, $agency_id);
        
        if (!$conversation) {
            echo json_encode([
                'success' => false, 
                'message' => 'Access denied',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            exit();
        }
        
        // Mark messages as read
        $this->{$this->model}->mark_messages_as_read($conversation->id, 'agency');
        
        // Get messages with sender names
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
        
        log_message('debug', 'Agency: Found ' . count($messages) . ' new messages');
        
        // Generate HTML for messages (for backward compatibility)
        $html = '';
        $last_id = $last_message_id;
        
        foreach ($messages as $message) {
            $messageClass = $message->sender_type == 'agency' ? 'sent' : 'received';
            $alignClass = $message->sender_type == 'agency' ? 'justify-content-end' : 'justify-content-start';
            
            $html .= '<div class="d-flex ' . $alignClass . ' mb-2" data-message-id="' . $message->id . '">';
            $html .= '<div class="message-container" style="max-width: 70%;">';
            $html .= '<div class="message-content d-flex align-items-baseline ' . ($message->sender_type == 'agency' ? 'justify-content-end' : 'justify-content-start') . '">';
            $html .= '<div class="message-text-time d-inline-flex align-items-baseline" style="background-color: ' . ($message->sender_type == 'agency' ? '#dcf8c6' : '#ffffff') . '; padding: 8px 12px; border-radius: 7.5px; box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);">';
            $html .= '<span class="message-text" style="font-size: 14.2px; color: #303030; line-height: 1.3; margin-right: 8px;">';
            $html .= nl2br(htmlspecialchars($message->message));
            $html .= '</span>';
            $html .= '<span class="message-meta d-inline-flex align-items-center">';
            $html .= '<small class="message-time" style="font-size: 11px; color: #667781; white-space: nowrap;">';
            $html .= date('g:i A', strtotime($message->created_at));
            $html .= '</small>';
            if ($message->sender_type == 'agency') {
                $html .= '<span class="message-status" style="margin-left: 4px;">';
                $html .= '<i class="fa fa-check' . ($message->is_read ? '-double' : '') . '" style="font-size: 10px; color: ' . ($message->is_read ? '#128C7E' : '#667781') . ';"></i>';
                $html .= '</span>';
            }
            $html .= '</span>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            
            if ($message->id > $last_id) {
                $last_id = $message->id;
            }
        }
        
        // Get the latest message ID
        $latest_message_id = $last_message_id;
        if (!empty($messages)) {
            $last_message = end($messages);
            $latest_message_id = $last_message->id;
        }
        
        echo json_encode([
            'success' => true,
            'html' => $html,
            'messages' => $messages, // Add structured data too
            'last_message_id' => $latest_message_id,
            'total_messages' => count($messages),
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
        exit();
    }

    public function ajax_get_conversations()
    {
        // Set JSON header FIRST
        header('Content-Type: application/json; charset=UTF-8');
        
        // Add debug logging
        log_message('debug', 'Agency ajax_get_conversations called');
        
        $agency_id = $this->get_user_agency_id();
        
        log_message('debug', 'Agency ID: ' . ($agency_id ?: 'NOT FOUND'));
        
        if (!$agency_id) {
            log_message('error', 'Agency not logged in for ajax_get_conversations');
            echo json_encode([
                'success' => false, 
                'message' => 'Agency not logged in',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            exit();
        }
        
        try {
            // Get conversations
            $conversations = $this->{$this->model}->get_agency_conversations($agency_id);
            
            log_message('debug', 'Agency: Found ' . count($conversations) . ' conversations');
            
            $total_unread_count = 0;
            $formatted_conversations = [];
            
            foreach ($conversations as $conv) {
                $unread = isset($conv->unread_count) ? $conv->unread_count : 0;
                $total_unread_count += $unread;
                
                $formatted_conversations[] = [
                    'id' => $conv->id,
                    'uuid' => $conv->uuid,
                    'recruiter_name' => $conv->recruiter_name,
                    'last_message' => $conv->last_message,
                    'last_message_at' => $conv->last_message_at,
                    'last_sender_type' => isset($conv->last_sender_type) ? $conv->last_sender_type : 'recruiter',
                    'unread_count' => $unread,
                    'is_online' => isset($conv->is_online) ? $conv->is_online : false,
                    'recruiter_id' => isset($conv->recruiter_id) ? $conv->recruiter_id : 0,
                    'candidate_id' => isset($conv->candidate_id) ? $conv->candidate_id : null
                ];
            }
            
            echo json_encode([
                'success' => true,
                'conversations' => $formatted_conversations,
                'total_unread_count' => $total_unread_count,
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error fetching agency conversations: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Server error',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
        }
        exit();
    }

    public function ajax_check_session()
    {
        header('Content-Type: application/json; charset=UTF-8');
        
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Session expired',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            exit();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Session valid',
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
        exit();
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
}