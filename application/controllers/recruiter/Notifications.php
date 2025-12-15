<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Notifications extends CRUD_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('recruiter/Model_notifications');
    }

    public function ajax_get_notifications()
    {
        // ✅ ADD CSRF VALIDATION
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();
        
        // Check if this is a POST request (which requires CSRF validation)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf_token = $this->input->post($csrf_name);
            
            if (!$csrf_token || $csrf_token !== $csrf_hash) {
                $response = [
                    'success' => false,
                    'system_unread_count' => 0,
                    'chat_unread_count' => 0,
                    'message' => 'Invalid CSRF token. Please refresh.',
                    'csrf_token' => $this->security->get_csrf_hash() // Provide new token
                ];
                $this->output->set_content_type('application/json')->set_output(json_encode($response));
                return;
            }
        }
        // Remove AJAX check to avoid issues
        $login_data = $this->session->userdata('login');
        $recruiter_id = !empty($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : null;
        
        $response = [
            'success' => false,
            'system_unread_count' => 0,
            'chat_unread_count' => 0,
            'message' => ''
        ];

        try {
            if (!$recruiter_id) {
                $response['message'] = 'Recruiter not logged in';
                $this->output->set_content_type('application/json')->set_output(json_encode($response));
                return;
            }

            
            // Get system notifications count (exclude chat notifications)
            $system_unread_count = $this->Model_notifications->count_system_notifications($recruiter_id);
            
            // Get chat notifications count
            $this->load->model('recruiter/Model_chat_messages');
            $chat_unread_count = $this->Model_chat_messages->get_unread_count_for_recruiter($recruiter_id);
            
            $response['system_unread_count'] = (int)$system_unread_count;
            $response['chat_unread_count'] = (int)$chat_unread_count;
            $response['success'] = true;


        } catch (Exception $e) {
            $response['message'] = 'Server error: ' . $e->getMessage();
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }


}