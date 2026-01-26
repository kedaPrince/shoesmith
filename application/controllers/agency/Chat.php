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
        // ===== START: CSRF FIX FOR AJAX REQUESTS =====
        // Check if this is an AJAX request
        $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // Also check for POST data with X-Requested-With header
        if (!$is_ajax && isset($_POST['X-Requested-With'])) {
            $is_ajax = true;
        }
        
        // If it's an AJAX request, disable CodeIgniter's CSRF protection
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


        
        $this->folder = 'agency';
        $this->load->model('agency/Model_chat_messages');
        $this->load->model('agency/Model_notifications');
        $this->load->helper('csrf');
        
        $login_data = $this->session->userdata('login');
        if (empty($login_data['agency'])) {
            redirect('agency/login');
        }
        
        // ===== ADD THIS: Track agency session activity =====
        $agency_id = $this->get_user_agency_id();
        if ($agency_id) {
            $this->update_agency_activity($agency_id);
        }
    }

    // ===== ADD THIS METHOD to agency Chat.php =====
    private function update_agency_activity($agency_id)
    {
        // Update or create user session for agency
        $session_data = [
            'user_id' => $agency_id,
            'user_type' => 'agency',
            'last_activity' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Check if session exists
        $existing_session = $this->db->where('user_id', $agency_id)
                                    ->where('user_type', 'agency')
                                    ->get('user_sessions')
                                    ->row();
        
        if ($existing_session) {
            // Update existing session
            $this->db->where('id', $existing_session->id)
                    ->update('user_sessions', [
                        'last_activity' => date('Y-m-d H:i:s')
                    ]);
        } else {
            // Create new session
            $this->db->insert('user_sessions', $session_data);
        }
        
        // Also update agencies table for backup
        $this->db->where('id', $agency_id)
                ->update('agencies', [
                    'last_activity_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
    }

    // This method should exist in agency Chat.php:
    public function ajax_get_chat_notifications()
    {
        $agency_id = $this->get_user_agency_id();
        
        $response = [
            'success' => false,
            'unread_count' => 0,
            'csrf_token' => $this->security->get_csrf_hash()
        ];

        try {
            if (!$agency_id) {
                $response['message'] = 'Not logged in';
                $this->output->set_content_type('application/json')->set_output(json_encode($response));
                return;
            }

            // Get unread count from chat messages
            $unread_count = $this->Model_chat_messages->get_unread_count_for_agency($agency_id);
            
            $response['unread_count'] = (int)$unread_count;
            $response['success'] = true;

        } catch (Exception $e) {
            $response['message'] = 'Server error: ' . $e->getMessage();
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
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
            redirect('/agency/chat/conversation/' . $conversations[0]->uuid);
            return;
        }
        
        if (!empty($available_recruiters)) {
            $conversation = $this->{$this->model}->get_or_create_conversation(
                $agency_id, 
                $available_recruiters[0]->id
            );
            
            if ($conversation) {
                redirect('/agency/chat/conversation/' . $conversation->uuid);
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
        // In your conversation() method, add:
        if (isset($conversation) && $conversation->recruiter_id) {
            // Update recruiter's activity in user_sessions
            $this->db->where('user_id', $conversation->recruiter_id)
                    ->where('user_type', 'recruiter')
                    ->update('user_sessions', [
                        'last_activity' => date('Y-m-d H:i:s')
                    ]);
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

    public function ajax_send_message()
    {
        // Always return CSRF token in response
        $response = [
            'success' => false,
            'message' => '',
            'csrf_token' => $this->security->get_csrf_hash() // Always include fresh token
        ];
        
        // Check if it's POST (requires CSRF validation) or GET (bypass CSRF like Notifications)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // For POST requests, validate CSRF
            $csrf_name = $this->security->get_csrf_token_name();
            $csrf_token = $this->input->post($csrf_name);
            
            if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
                $response['message'] = 'Security token expired. Please try again.';
                $response['csrf_invalid'] = true;
                $response['needs_retry'] = true;
                
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response));
                return;
            }
        }
        // For GET requests, skip CSRF validation (like Notifications controller does)
        
        $conversation_uuid = $this->input->get_post('conversation_uuid');
        $message_text = $this->input->get_post('message');
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
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
        
        $conversation = $this->Model_chat_messages->get_conversation_for_agency_by_uuid(
            $conversation_uuid, 
            $agency_id
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
            'agency',
            $agency_id,
            $message_text,
            'text',
            null
        );
        
        if ($message_id) {
            $this->Model_chat_messages->create_chat_notification(
                $conversation->id,
                $conversation->recruiter_id,  // Send to recruiter
                'recruiter',                  // Recruiter type
                $message_text,                // Message content
                $agency_id                    // Sender is agency
            );
            // ===== ADD THIS: Update sender's activity =====
            // If agency sent message, update agency activity
            $this->db->where('user_id', $agency_id)
                    ->where('user_type', 'agency')
                    ->update('user_sessions', [
                        'last_activity' => date('Y-m-d H:i:s')
                    ]);
            
            // ===== ADD THIS: Also update conversation activity =====
            if (isset($conversation) && $conversation->recruiter_id) {
                // Update recruiter's activity since they received a message
                $this->db->where('user_id', $conversation->recruiter_id)
                        ->where('user_type', 'recruiter')
                        ->update('user_sessions', [
                            'last_activity' => date('Y-m-d H:i:s')
                        ]);
            }
            // =====================================================
            
            $response['success'] = true;
            $response['message'] = 'Message sent successfully';
            $response['message_id'] = $message_id;
            $response['csrf_token'] = $this->security->get_csrf_hash();
        } else {
            $response['message'] = 'Failed to save message';
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // Add this to Chat.php or create separate recruiter chat controller
    public function ajax_recruiter_send_message()
    {
        $recruiter_id = $this->session->userdata('login')['recruiter']['id'] ?? 0;
        
        if (!$recruiter_id) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }
        
        // ... get message data ...
        
        $message_id = $this->Model_chat_messages->send_message(
            $conversation_id,
            'recruiter',
            $recruiter_id,
            $message_text,
            'text',
            null
        );
        
        if ($message_id) {
            // ===== CRITICAL: Update recruiter's activity =====
            $this->db->where('user_id', $recruiter_id)
                    ->where('user_type', 'recruiter')
                    ->update('user_sessions', [
                        'last_activity' => date('Y-m-d H:i:s')
                    ]);
            
            // Also update recruiters table
            $this->db->where('id', $recruiter_id)
                    ->update('recruiters', [
                        'last_activity_at' => date('Y-m-d H:i:s')
                    ]);
            // ================================================
            
            echo json_encode(['success' => true, 'message_id' => $message_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send']);
        }
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
        $agency_id = $this->get_user_agency_id();
        
        if (!$conversation_uuid) {
            $response['message'] = 'Conversation UUID required';
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
            return;
        }
        
        $conversation = $this->Model_chat_messages->get_conversation_for_agency_by_uuid(
            $conversation_uuid, 
            $agency_id
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
    {
        // Check CSRF first
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
            
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            $this->ajax_response([
                'message' => 'Agency not logged in'
            ], false);
            return;
        }

        try {
            $conversations = $this->Model_chat_messages->get_agency_conversations($agency_id);
            $total_unread_count = 0;
            
            $formatted_conversations = [];
            foreach ($conversations as $conv) {
                $formatted_conversations[] = [
                    'id' => $conv->id,
                    'uuid' => $conv->uuid,
                    'recruiter_name' => $conv->recruiter_name,
                    'last_message' => $conv->last_message,
                    'last_message_at' => $conv->last_message_at,
                    'last_sender_type' => isset($conv->last_sender_type) ? $conv->last_sender_type : 'recruiter',
                    'unread_count' => isset($conv->unread_count) ? $conv->unread_count : 0,
                    'is_online' => isset($conv->is_online) ? $conv->is_online : false,
                    'recruiter_id' => isset($conv->recruiter_id) ? $conv->recruiter_id : 0
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

    public function ajax_check_session()
    {
        $agency_id = $this->get_user_agency_id();
        
        if (!$agency_id) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Not logged in',
                    'csrf_token' => $this->security->get_csrf_hash()
                ]));
            return;
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Session valid',
                'csrf_token' => $this->security->get_csrf_hash()
            ]));
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

    public function cleanup_old_sessions()
    {
        // Delete sessions older than 1 day
        $one_day_ago = date('Y-m-d H:i:s', strtotime('-1 day'));
        $this->db->where('last_activity <', $one_day_ago)
                ->delete('user_sessions');
        
        echo "Cleaned up old sessions";
    }

    public function ajax_check_online_status()
    {
        // PREVENT CACHING
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        $response = [
            'success' => true,
            'online_status' => [],
            'csrf_token' => $this->security->get_csrf_hash(),
            'timestamp' => microtime(true),
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $agency_id = $this->get_user_agency_id();
            
            if (!$agency_id) {
                throw new Exception('Not logged in');
            }
            
            // FIRST: Clean up expired sessions (older than 10 minutes)
            $ten_minutes_ago = date('Y-m-d H:i:s', strtotime('-10 minutes'));
            $this->db->where('last_activity <', $ten_minutes_ago)
                    ->delete('user_sessions');
            
            // GET ALL RECRUITERS FOR THIS AGENCY
            $recruiters = $this->Model_chat_messages->get_available_recruiters($agency_id);
            
            $onlineStatus = [];
            
            foreach ($recruiters as $recruiter) {
                // Check if there's an active session within last 10 minutes
                $this->db->select('id, last_activity');
                $this->db->from('user_sessions');
                $this->db->where('user_id', $recruiter->id);
                $this->db->where('user_type', 'recruiter');
                $this->db->where('last_activity >=', $ten_minutes_ago);
                $this->db->limit(1);
                
                $session_exists = $this->db->get()->row() !== null;
                
                $onlineStatus[$recruiter->id] = $session_exists;
                
                // Also update the recruiter's last_activity_at in recruiters table
                if ($session_exists) {
                    $this->db->where('id', $recruiter->id)
                            ->update('recruiters', [
                                'last_activity_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                }
            }
            
            $response['online_status'] = $onlineStatus;
            $response['debug_info'] = [
                'current_time' => date('Y-m-d H:i:s'),
                'threshold' => $ten_minutes_ago,
                'total_recruiters' => count($recruiters),
                'online_count' => array_sum($onlineStatus),
                'note' => '10-minute threshold, sessions auto-cleaned'
            ];
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }


}