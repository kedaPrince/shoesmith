<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends CI_Controller
{
    public $pageName = 'chat';
    public $group = 'Chat';
    public $folder = 'agency_staff';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper(['profile_helper', 'agency_access_helper']);

        $login_data = $this->session->userdata('login');
        $is_agency_logged_in = !empty($login_data['agency']);
        
        if (!$is_agency_logged_in) {
            redirect('login');
        }

        if (!agency_staff_can_access_page('chat')) {
            $this->session->set_flashdata('error', 'Access denied to chat section');
            redirect('dashboard');
        }
    }

    public function index()
    {
        $this->load->model($this->folder . '/Model_chat');
        
        $data = [
            'title' => lang('chat_heading'),
            'conversations' => $this->Model_chat->get_conversations(loginID()),
            'unread_count' => $this->Model_chat->get_unread_count(loginID()),
        ];

        $this->load->view($this->folder . '/view_header');
        $this->load->view($this->folder . '/chat/view_index', $data);
        $this->load->view($this->folder . '/view_footer');
    }

    public function conversation($user_id)
    {
        $this->load->model($this->folder . '/Model_chat');
        
        $data = [
            'title' => 'Chat Conversation',
            'other_user' => $this->Model_chat->get_user($user_id),
            'messages' => $this->Model_chat->get_messages(loginID(), $user_id),
        ];

        // Mark messages as read
        $this->Model_chat->mark_as_read(loginID(), $user_id);

        $this->load->view($this->folder . '/view_header');
        $this->load->view($this->folder . '/chat/view_conversation', $data);
        $this->load->view($this->folder . '/view_footer');
    }

    public function send_message()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->load->model($this->folder . '/Model_chat');
        
        $to_user = $this->input->post('to_user');
        $message = $this->input->post('message');

        if (empty($message)) {
            ajax_return(['success' => false, 'error' => 'Message cannot be empty']);
        }

        $message_id = $this->Model_chat->send_message(loginID(), $to_user, $message);

        if ($message_id) {
            ajax_return(['success' => true, 'message_id' => $message_id]);
        } else {
            ajax_return(['success' => false, 'error' => 'Failed to send message']);
        }
    }

    public function get_new_messages()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->load->model($this->folder . '/Model_chat');
        
        $from_user = $this->input->post('from_user');
        $last_message_id = $this->input->post('last_message_id');

        $messages = $this->Model_chat->get_new_messages(loginID(), $from_user, $last_message_id);

        ajax_return(['success' => true, 'messages' => $messages]);
    }
}