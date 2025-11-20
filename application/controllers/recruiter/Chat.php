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
        
        // Verify recruiter access
        $login_data = $this->session->userdata('login');
        if (empty($login_data['recruiter'])) {
            redirect('recruiter/login');
        }
    }

    public function index()
    {
        $recruiter_id = $this->get_recruiter_id();
        $conversations = $this->{$this->model}->get_recruiter_conversations($recruiter_id);
        
        $this->breadcrumbs = [
            ['title' => lang('chat_heading'), 'url' => '']
        ];
        
        $this->load->view($this->folder . '/view_header');
        $this->load->view('recruiter/chat/index', [
            'conversations' => $conversations,
            'heading' => lang('chat_heading'),
            'recruiter_id' => $recruiter_id
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

    public function conversation($conversation_id = null)
    {
        $recruiter_id = $this->get_recruiter_id();
        
        if (!$conversation_id) {
            show_404();
        }
        
        $conversation = $this->{$this->model}->get_conversation_for_recruiter($conversation_id, $recruiter_id);
        
        if (!$conversation) {
            show_404();
        }
        
        // Mark messages as read
        $this->{$this->model}->mark_messages_as_read($conversation_id, 'recruiter');
        
        $messages = $this->{$this->model}->get_conversation_messages($conversation_id);
        
        // Get all conversations for the sidebar
        $all_conversations = $this->{$this->model}->get_recruiter_conversations($recruiter_id);
        
        $this->breadcrumbs = [
            ['title' => lang('chat_heading'), 'url' => url('chat')],
            ['title' => $conversation->title, 'url' => '']
        ];
        
        $this->load->view($this->folder . '/view_header');
        $this->load->view('recruiter/chat/conversation', [
            'conversation' => $conversation,
            'messages' => $messages,
            'all_conversations' => $all_conversations,
            'heading' => $conversation->title,
            'recruiter_id' => $recruiter_id
        ]);
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

/**
 * AJAX: Send message - FIXED VERSION WITHOUT NOTIFICATIONS
 */
public function ajax_send_message()
{
    log_message('debug', '=== ajax_send_message called ===');
    
    try {
        // Remove AJAX check since we have explicit routes
        $conversation_id = $this->input->post('conversation_id');
        $message = $this->input->post('message');
        $recruiter_id = $this->get_recruiter_id();
        
        log_message('debug', "Send params - conversation_id: $conversation_id, message: " . substr($message, 0, 50) . ", recruiter_id: $recruiter_id");
        
        if (empty($conversation_id) || empty($message)) {
            log_message('debug', 'Missing required fields');
            ajax_return(['success' => false, 'message' => 'Missing required fields']);
            return;
        }
        
        $conversation = $this->{$this->model}->get_conversation_for_recruiter($conversation_id, $recruiter_id);
        if (!$conversation) {
            log_message('debug', 'Conversation not found');
            ajax_return(['success' => false, 'message' => 'Conversation not found']);
            return;
        }
        
        $message_id = $this->{$this->model}->send_message(
            $conversation_id, 
            'recruiter', 
            $recruiter_id, 
            $message
        );
        
        if ($message_id) {
            log_message('debug', "Message sent successfully, ID: $message_id");
            
            // TEMPORARILY DISABLE NOTIFICATIONS - COMMENT THIS OUT
            // $this->send_chat_notification($conversation, $message, 'recruiter');
            
            ajax_return(['success' => true, 'message_id' => $message_id]);
        } else {
            log_message('debug', 'Failed to send message');
            ajax_return(['success' => false, 'message' => 'Failed to send message']);
        }
    } catch (Exception $e) {
        log_message('error', 'Error in ajax_send_message: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        ajax_return(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
}

    /**
     * AJAX: Get new messages - FIXED VERSION
     */
    public function ajax_get_messages()
    {
        log_message('debug', '=== ajax_get_messages called ===');
        
        // Remove AJAX check since we have explicit routes
        $conversation_id = $this->input->post('conversation_id');
        $last_message_id = $this->input->post('last_message_id') ?: 0;
        $recruiter_id = $this->get_recruiter_id();
        
        log_message('debug', "Fetch params - conversation_id: $conversation_id, last_message_id: $last_message_id, recruiter_id: $recruiter_id");
        
        if (!$conversation_id) {
            ajax_return(['success' => false, 'message' => 'Conversation ID required']);
            return;
        }
        
        $conversation = $this->{$this->model}->get_conversation_for_recruiter($conversation_id, $recruiter_id);
        if (!$conversation) {
            ajax_return(['success' => false, 'message' => 'Conversation not found']);
            return;
        }
        
        // Mark messages as read
        $this->{$this->model}->mark_messages_as_read($conversation_id, 'recruiter');
        
        // Get only new messages
        $this->db->select('cm.*, 
                          CASE 
                              WHEN cm.sender_type = "agency" THEN a.name
                              WHEN cm.sender_type = "recruiter" THEN CONCAT(r.first_name, " ", r.last_name)
                          END as sender_name');
        $this->db->from('chat_messages cm');
        $this->db->join('agencies a', 'a.id = cm.sender_id AND cm.sender_type = "agency"', 'left');
        $this->db->join('recruiters r', 'r.id = cm.sender_id AND cm.sender_type = "recruiter"', 'left');
        $this->db->where('cm.conversation_id', $conversation_id);
        
        if ($last_message_id > 0) {
            $this->db->where('cm.id >', $last_message_id);
        }
        
        $this->db->where('cm.enabled', 1);
        $this->db->where('cm.removed', 0);
        $this->db->order_by('cm.created_at', 'ASC');
        
        $messages = $this->db->get()->result();
        
        log_message('debug', 'Found ' . count($messages) . ' new messages');
        
        $html = '';
        $last_id = $last_message_id;
        $has_new_messages = false;
        
        foreach ($messages as $message) {
            $html .= $this->load->view('recruiter/chat/message_item', [
                'message' => $message, 
                'current_user_type' => 'recruiter'
            ], true);
            $last_id = max($last_id, $message->id);
            $has_new_messages = true;
        }
        
        $response = [
            'success' => true,
            'html' => $html,
            'last_message_id' => $last_id,
            'has_new_messages' => $has_new_messages,
            'message_count' => count($messages)
        ];
        
        log_message('debug', 'Sending response with ' . count($messages) . ' messages');
        ajax_return($response);
    }

    /**
     * AJAX: Get unread count for menu badge
     */
    public function ajax_get_unread_count()
    {
        // Remove AJAX check
        $recruiter_id = $this->get_recruiter_id();
        $unread_count = $this->{$this->model}->get_unread_count_for_recruiter($recruiter_id);
        
        ajax_return(['success' => true, 'unread_count' => $unread_count]);
    }

    /**
     * AJAX: Start new conversation
     */
    public function ajax_start_conversation()
    {
        // Remove AJAX check
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
                'redirect_url' => site_url('recruiter/chat/conversation/' . $conversation->id)
            ]);
        } else {
            ajax_return(['success' => false, 'message' => 'Failed to create conversation']);
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
            redirect('recruiter/chat/conversation/' . $conversation->id);
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
}