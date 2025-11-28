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
        
        // If user has conversations, redirect to the first one
        if (!empty($conversations)) {
            redirect('recruiter/chat/conversation/' . $conversations[0]->id);
            return;
        }
        
        // If no conversations but has agencies, create first conversation with first agency
        if (!empty($available_agencies)) {
            $conversation = $this->{$this->model}->get_or_create_conversation(
                $available_agencies[0]->id, 
                $recruiter_id
            );
            
            if ($conversation) {
                redirect('recruiter/chat/conversation/' . $conversation->id);
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

    public function conversation($conversation_id = null)
    {
        $recruiter_id = $this->get_recruiter_id();
        
        if (!$conversation_id) {
            redirect('recruiter/chat');
            return;
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

    /**
     * AJAX: Send message - FIXED VERSION WITH NOTIFICATIONS
     */
    public function ajax_send_message()
    {
        try {
            $conversation_id = $this->input->post('conversation_id');
            $message = $this->input->post('message');
            $recruiter_id = $this->get_recruiter_id();
                    
            if (empty($conversation_id) || empty($message)) {
                ajax_return(['success' => false, 'message' => 'Missing required fields']);
                return;
            }
            
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
                // Create notification for agency
                $notification_id = $this->{$this->model}->create_chat_notification(
                    $conversation_id,
                    $conversation->agency_id, // Send to agency
                    'agency', // Receiver type
                    $message,
                    $recruiter_id // Sender ID (recruiter)
                );
                            
                ajax_return(['success' => true, 'message_id' => $message_id]);
            } else {
                ajax_return(['success' => false, 'message' => 'Failed to send message']);
            }
        } catch (Exception $e) {
            ajax_return(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function ajax_get_messages()
    {
        $conversation_id = $this->input->post('conversation_id');
        $last_message_id = $this->input->post('last_message_id') ?: 0;
        $recruiter_id = $this->get_recruiter_id();
            
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
        
        // Get ONLY new messages (messages with ID greater than last_message_id)
        $this->db->select('cm.*, 
                        CASE 
                            WHEN cm.sender_type = "agency" THEN a.name
                            WHEN cm.sender_type = "recruiter" THEN CONCAT(r.first_name, " ", r.last_name)
                        END as sender_name');
        $this->db->from('chat_messages cm');
        $this->db->join('agencies a', 'a.id = cm.sender_id AND cm.sender_type = "agency"', 'left');
        $this->db->join('recruiters r', 'r.id = cm.sender_id AND cm.sender_type = "recruiter"', 'left');
        $this->db->where('cm.conversation_id', $conversation_id);
        
        // Only get messages newer than last_message_id
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
            $message_html = $this->load->view('recruiter/chat/message_item', [
                'message' => $message, 
                'current_user_type' => 'recruiter'
            ], true);
            
            $html .= $message_html;
            $has_new_messages = true;
            
            // Update last_id to the highest message ID
            if ($message->id > $last_id) {
                $last_id = $message->id;
            }
        }
        
        $response = [
            'success' => true,
            'html' => $html,
            'last_message_id' => $last_id,
            'has_new_messages' => $has_new_messages,
            'message_count' => count($messages),
            'debug' => [
                'requested_last_id' => $last_message_id,
                'returned_last_id' => $last_id,
                'new_messages_found' => count($messages)
            ]
        ];
        
        ajax_return($response);
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

    public function ajax_mark_notifications_read()
    {
        $conversation_id = $this->input->post('conversation_id');
        $recruiter_id = $this->get_recruiter_id();
            
        if (!$conversation_id || !$recruiter_id) {
            ajax_return(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }
        
        try {
            // Mark chat messages as read
            $messages_updated = $this->Model_chat_messages->mark_messages_as_read($conversation_id, 'recruiter');
            
            // Mark chat notifications as read
            $this->load->model('recruiter/Model_notifications');
            $notifications_updated = $this->Model_notifications->mark_chat_notifications_read($conversation_id, $recruiter_id, 'recruiter');
            
            // Get updated unread count
            $unread_count = $this->Model_chat_messages->get_unread_count_for_recruiter($recruiter_id);
                    
            ajax_return([
                'success' => true,
                'messages_updated' => $messages_updated,
                'notifications_updated' => $notifications_updated,
                'unread_count' => $unread_count
            ]);
            
        } catch (Exception $e) {
            ajax_return(['success' => false, 'message' => 'Server error']);
        }
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
}