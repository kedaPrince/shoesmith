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
     * AJAX: Send message
     */
    public function ajax_send_message()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $conversation_id = $this->input->post('conversation_id');
        $message = $this->input->post('message');
        $recruiter_id = $this->get_recruiter_id();
        
        if (empty($conversation_id) || empty($message)) {
            ajax_return(['success' => false, 'message' => 'Missing required fields']);
            return;
        }
        
        // Verify conversation access
        $conversation = $this->{$this->model}->get_conversation_for_recruiter($conversation_id, $recruiter_id);
        if (!$conversation) {
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
            // Send notification to agency
            $this->send_chat_notification($conversation, $message, 'recruiter');
            
            ajax_return(['success' => true, 'message_id' => $message_id]);
        } else {
            ajax_return(['success' => false, 'message' => 'Failed to send message']);
        }
    }

    /**
     * AJAX: Get new messages
     */
    public function ajax_get_messages()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $conversation_id = $this->input->post('conversation_id');
        $last_message_id = $this->input->post('last_message_id');
        $recruiter_id = $this->get_recruiter_id();
        
        $conversation = $this->{$this->model}->get_conversation_for_recruiter($conversation_id, $recruiter_id);
        if (!$conversation) {
            ajax_return(['success' => false, 'message' => 'Conversation not found']);
            return;
        }
        
        // Mark messages as read
        $this->{$this->model}->mark_messages_as_read($conversation_id, 'recruiter');
        
        $messages = $this->{$this->model}->get_conversation_messages($conversation_id);
        
        $html = '';
        $last_id = 0;
        
        foreach ($messages as $message) {
            if ($message->id > $last_message_id) {
                $html .= $this->load->view('recruiter/chat/message_item', ['message' => $message, 'current_user_type' => 'recruiter'], true);
            }
            $last_id = max($last_id, $message->id);
        }
        
        ajax_return([
            'success' => true,
            'html' => $html,
            'last_message_id' => $last_id,
            'has_new_messages' => !empty($html)
        ]);
    }

    /**
     * AJAX: Get unread count for menu badge
     */
    public function ajax_get_unread_count()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $recruiter_id = $this->get_recruiter_id();
        $unread_count = $this->{$this->model}->get_unread_count_for_recruiter($recruiter_id);
        
        ajax_return(['success' => true, 'unread_count' => $unread_count]);
    }

    /**
     * AJAX: Start new conversation
     */
    public function ajax_start_conversation()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
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