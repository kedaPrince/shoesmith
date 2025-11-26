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
            'all_conversations' => $all_conversations,
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
     * AJAX: Get new messages - FIXED VERSION
     */
    public function ajax_get_messages()
    {
        log_message('debug', '=== Agency ajax_get_messages called ===');
        
        // Remove AJAX check
        $conversation_id = $this->input->post('conversation_id');
        $last_message_id = $this->input->post('last_message_id') ?: 0;
        $agency_id = $this->get_user_agency_id();
        
        log_message('debug', "Agency fetch params - conversation_id: $conversation_id, last_message_id: $last_message_id, agency_id: $agency_id");
        
        $conversation = $this->{$this->model}->get_conversation_for_agency($conversation_id, $agency_id);
        if (!$conversation) {
            ajax_return(['success' => false, 'message' => 'Conversation not found']);
            return;
        }
        
        // Mark messages as read
        $this->{$this->model}->mark_messages_as_read($conversation_id, 'agency');
        
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
        
        log_message('debug', 'Agency found ' . count($messages) . ' new messages');
        
        $html = '';
        $last_id = $last_message_id;
        $has_new_messages = false;
        
        foreach ($messages as $message) {
            $html .= $this->load->view('agency/chat/message_item', [
                'message' => $message, 
                'current_user_type' => 'agency'
            ], true);
            $last_id = max($last_id, $message->id);
            $has_new_messages = true;
        }
        
        ajax_return([
            'success' => true,
            'html' => $html,
            'last_message_id' => $last_id,
            'has_new_messages' => $has_new_messages,
            'message_count' => count($messages)
        ]);
    }

    /**
     * AJAX: Get unread count for menu badge
     */
    public function ajax_get_unread_count()
    {
        // Remove AJAX check
        $agency_id = $this->get_user_agency_id();
        $unread_count = $this->{$this->model}->get_unread_count_for_agency($agency_id);
        
        ajax_return(['success' => true, 'unread_count' => $unread_count]);
    }

    /**
     * AJAX: Start new conversation
     */
    public function ajax_start_conversation()
    {
        // Remove AJAX check
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
                log_message('debug', 'Model_notifications class not found');
                return;
            }
            
            $this->load->model('agency/Model_notifications');
            
            // Debug: Check if method exists
            if (!method_exists($this->Model_notifications, 'create_chat_notification')) {
                log_message('debug', 'create_chat_notification method not found in Model_notifications');
                return;
            }
            
            if ($sender_type === 'agency') {
                log_message('debug', 'Calling create_chat_notification from Model_notifications');
                $this->Model_notifications->create_chat_notification(
                    $conversation->id,
                    $conversation->recruiter_id,
                    'recruiter',
                    $message,
                    $this->get_user_agency_id() // FIXED: Changed from get_agency_id() to get_user_agency_id()
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


// In your Agency Chat Controller - FIXED ajax_send_message method
public function ajax_send_message()
{
    log_message('debug', '=== AGENCY ajax_send_message called ===');
    
    try {
        $conversation_id = $this->input->post('conversation_id');
        $message = $this->input->post('message');
        $agency_id = $this->get_user_agency_id(); // FIXED: Changed from get_agency_id() to get_user_agency_id()
        
        log_message('debug', "Agency Send params - conversation_id: $conversation_id, agency_id: $agency_id");
        
        if (empty($conversation_id) || empty($message)) {
            ajax_return(['success' => false, 'message' => 'Missing required fields']);
            return;
        }
        
        $conversation = $this->{$this->model}->get_conversation_for_agency($conversation_id, $agency_id);
        if (!$conversation) {
            ajax_return(['success' => false, 'message' => 'Conversation not found']);
            return;
        }
        
        // ADD DEBUG LOGGING HERE
        log_message('debug', '=== BEFORE NOTIFICATION CREATION ===');
        log_message('debug', "Conversation ID: $conversation_id");
        log_message('debug', "Recruiter ID: " . $conversation->recruiter_id);
        log_message('debug', "Message: " . substr($message, 0, 100));
        log_message('debug', "Agency ID: $agency_id");
        
        $message_id = $this->{$this->model}->send_message(
            $conversation_id, 
            'agency', 
            $agency_id, 
            $message
        );
        
        if ($message_id) {
            log_message('debug', "Agency message sent successfully, ID: $message_id");
            
            // CRITICAL: Create notification for recruiter
            log_message('debug', '=== CREATING NOTIFICATION ===');
            $notification_id = $this->{$this->model}->create_chat_notification(
                $conversation_id,
                $conversation->recruiter_id, // Send to recruiter
                'recruiter', // Receiver type
                $message,
                $agency_id // Sender ID (agency)
            );
            
            log_message('debug', "Notification creation result: " . ($notification_id ? "Success ID: $notification_id" : "Failed"));
            
            ajax_return(['success' => true, 'message_id' => $message_id]);
        } else {
            log_message('debug', 'Failed to send message');
            ajax_return(['success' => false, 'message' => 'Failed to send message']);
        }
    } catch (Exception $e) {
        log_message('error', 'Error in agency ajax_send_message: ' . $e->getMessage());
        ajax_return(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
}

/**
 * Test notification creation for agency
 */
public function test_notification_creation($conversation_id)
{
    $agency_id = $this->get_user_agency_id();
    
    echo "=== AGENCY NOTIFICATION TEST ===<br>";
    echo "Agency ID: $agency_id<br>";
    echo "Conversation ID: $conversation_id<br><br>";
    
    // Get conversation
    $conversation = $this->{$this->model}->get_conversation_for_agency($conversation_id, $agency_id);
    if (!$conversation) {
        echo "❌ Conversation not found<br>";
        return;
    }
    
    echo "✅ Conversation found:<br>";
    echo "- ID: $conversation->id<br>";
    echo "- Agency ID: $conversation->agency_id<br>";
    echo "- Recruiter ID: $conversation->recruiter_id<br>";
    echo "- Title: $conversation->title<br><br>";
    
    // Test sending a message
    $test_message = "Test message for notification creation";
    echo "Testing message sending...<br>";
    
    $message_id = $this->{$this->model}->send_message(
        $conversation_id, 
        'agency', 
        $agency_id, 
        $test_message
    );
    
    if ($message_id) {
        echo "✅ Message sent successfully, ID: $message_id<br><br>";
        
        // Test creating notification
        echo "Testing notification creation...<br>";
        $notification_id = $this->{$this->model}->create_chat_notification(
            $conversation_id,
            $conversation->recruiter_id,
            'recruiter',
            $test_message,
            $agency_id
        );
        
        if ($notification_id) {
            echo "✅ Notification created successfully, ID: $notification_id<br><br>";
            
            // Check if notification exists in database
            $this->db->select('*')
                     ->from('notifications')
                     ->where('id', $notification_id);
            $notification = $this->db->get()->row();
            
            if ($notification) {
                echo "✅ Notification found in database:<br>";
                echo "- ID: $notification->id<br>";
                echo "- Message: $notification->message<br>";
                echo "- Type: $notification->type<br>";
                echo "- Receiver ID: $notification->receiver_id<br>";
                echo "- Receiver Type: $notification->receiver_type<br>";
                echo "- Created: $notification->created_at<br>";
            } else {
                echo "❌ Notification not found in database<br>";
            }
        } else {
            echo "❌ Failed to create notification<br>";
            
            // Check for database errors
            $error = $this->db->error();
            echo "Database error: " . $error['message'] . "<br>";
        }
    } else {
        echo "❌ Failed to send message<br>";
    }
    
    die();
}
}