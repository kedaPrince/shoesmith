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
        
        // Verify agency access
        $login_data = $this->session->userdata('login');
        if (empty($login_data['agency'])) {
            redirect('agency/login');
        }
    }

    public function index()
    {
        $agency_id = $this->get_user_agency_id();
        $conversations = $this->{$this->model}->get_agency_conversations($agency_id);
        
        $this->breadcrumbs = [
            ['title' => lang('chat_heading'), 'url' => '']
        ];
        
        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/chat/index', [
            'conversations' => $conversations,
            'heading' => lang('chat_heading'),
            'agency_id' => $agency_id
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

   public function conversation($conversation_id = null)
    {
        $agency_id = $this->get_user_agency_id();
        
        if (!$conversation_id) {
            show_404();
        }
        
        $conversation = $this->{$this->model}->get_conversation_for_agency($conversation_id, $agency_id);
        
        if (!$conversation) {
            show_404();
        }
        
        // Mark messages as read
        $this->{$this->model}->mark_messages_as_read($conversation_id, 'agency');
        
        $messages = $this->{$this->model}->get_conversation_messages($conversation_id);
        
        // Get all conversations for the sidebar
        $all_conversations = $this->{$this->model}->get_agency_conversations($agency_id);
        
        $this->breadcrumbs = [
            ['title' => lang('chat_heading'), 'url' => url('chat')],
            ['title' => $conversation->title, 'url' => '']
        ];
        
        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/chat/conversation', [
            'conversation' => $conversation,
            'messages' => $messages,
            'all_conversations' => $all_conversations, // Pass all conversations for sidebar
            'heading' => $conversation->title,
            'agency_id' => $agency_id
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

    /**
     * Start new conversation
     */
    public function start()
    {
        $agency_id = $this->get_user_agency_id();
        $recruiters = $this->{$this->model}->get_available_recruiters($agency_id);
        
        $this->breadcrumbs = [
            ['title' => lang('chat_heading'), 'url' => url('chat')],
            ['title' => lang('label_start_conversation'), 'url' => '']
        ];
        
        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/chat/start', [
            'recruiters' => $recruiters,
            'heading' => lang('label_start_conversation'),
            'agency_id' => $agency_id
        ]);
        $this->load->view($this->folder . '/view_footer');
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
        $agency_id = $this->get_user_agency_id();
        
        $conversation = $this->{$this->model}->get_conversation_for_agency($conversation_id, $agency_id);
        if (!$conversation) {
            ajax_return(['success' => false, 'message' => 'Conversation not found']);
            return;
        }
        
        // Mark messages as read
        $this->{$this->model}->mark_messages_as_read($conversation_id, 'agency');
        
        // Get all messages (or only new ones if you want to optimize)
        $messages = $this->{$this->model}->get_conversation_messages($conversation_id);
        
        $html = '';
        $last_id = $last_message_id;
        $has_new_messages = false;
        
        foreach ($messages as $message) {
            if ($message->id > $last_message_id) {
                $html .= $this->load->view('agency/chat/message_item', [
                    'message' => $message, 
                    'current_user_type' => 'agency'
                ], true);
                $last_id = max($last_id, $message->id);
                $has_new_messages = true;
            }
        }
        
        ajax_return([
            'success' => true,
            'html' => $html,
            'last_message_id' => $last_id,
            'has_new_messages' => $has_new_messages
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
        
        $agency_id = $this->get_user_agency_id();
        $unread_count = $this->{$this->model}->get_unread_count_for_agency($agency_id);
        
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
        
        $recruiter_id = $this->input->post('recruiter_id');
        $subject = $this->input->post('subject');
        $initial_message = $this->input->post('initial_message');
        $agency_id = $this->get_user_agency_id();
        
        if (empty($recruiter_id)) {
            ajax_return(['success' => false, 'message' => 'Please select a recruiter']);
            return;
        }
        
        // Create conversation
        $conversation = $this->{$this->model}->get_or_create_conversation($agency_id, $recruiter_id);
        
        if ($conversation) {
            // Send initial message if provided
            if (!empty($initial_message)) {
                $this->{$this->model}->send_message(
                    $conversation->id,
                    'agency',
                    $agency_id,
                    $initial_message
                );
            }
            
            ajax_return([
                'success' => true, 
                'conversation_id' => $conversation->id,
                'redirect_url' => site_url('agency/chat/conversation/' . $conversation->id)
            ]);
        } else {
            ajax_return(['success' => false, 'message' => 'Failed to create conversation']);
        }
    }

   private function send_chat_notification($conversation, $message, $sender_type)
    {
        // Debug: Check if model exists
        if (!class_exists('Model_notifications')) {
            return;
        }
        
        $this->load->model('agency/Model_notifications');
        
        // Debug: Check if method exists
        if (!method_exists($this->Model_notifications, 'create_chat_notification')) {
            return;
        }
        
        if ($sender_type === 'agency') {
            $this->Model_notifications->create_chat_notification(
                $conversation->id,
                $conversation->recruiter_id,
                'recruiter',
                $message,
                $this->get_user_agency_id()
            );
        }
    }

   private function get_user_agency_id()
    {
        $login = $this->session->userdata('login');
        
        // Debug: Check session data
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


    public function ajax_send_message()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $conversation_id = $this->input->post('conversation_id');
        $message = $this->input->post('message');
        $agency_id = $this->get_user_agency_id(); // ← FIXED THIS LINE
        
        if (empty($conversation_id) || empty($message)) {
            ajax_return(['success' => false, 'message' => 'Missing required fields']);
            return;
        }
        
        // Verify conversation access - use agency_id instead of recruiter_id
        $conversation = $this->{$this->model}->get_conversation_for_agency($conversation_id, $agency_id); // ← FIXED THIS LINE
        if (!$conversation) {
            ajax_return(['success' => false, 'message' => 'Conversation not found']);
            return;
        }
        
        $message_id = $this->{$this->model}->send_message(
            $conversation_id, 
            'agency', 
            $agency_id, // ← FIXED THIS LINE
            $message
        );
        
        if ($message_id) {
            // TEMPORARILY DISABLE NOTIFICATIONS FOR TESTING
            // $this->send_chat_notification($conversation, $message, 'agency');
            
            ajax_return(['success' => true, 'message_id' => $message_id]);
        } else {
            ajax_return(['success' => false, 'message' => 'Failed to send message']);
        }
    }
}