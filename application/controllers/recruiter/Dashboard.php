<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CRUD_Controller {

    public $pageName    = 'dashboard';
    public $group       = 'Dashboard';
    public $view        = '';
    public $model       = 'Model_dashboard';
    public $sorting     = array();
    public $singular    = 'dashboard';
    public $plural      = 'dashboard';
    public $adding      = false;
    public $hideSubNav  = true;

    public function __construct() {
        parent::__construct();

        // Check if user is logged in as recruiter
        $login_data = $this->session->userdata('login');
        $is_recruiter_logged_in = !empty($login_data['recruiter']);
        
        if (!$is_recruiter_logged_in) {
            redirect('login');
        }

        $this->load->model($this->folder.'/'.$this->model);
        $this->load->model('recruiter/Model_notifications');
        $this->zone = array(
            'title' => lang('label_dashboard'),
            'url'   => url($this->pageName)
        );
    }

    public function index() {
        // Get recruiter ID
        $recruiter_id = $this->get_recruiter_id();
        
        // Get notifications for the recruiter
        $data['notifications'] = $this->Model_notifications->get_unread_notifications($recruiter_id);
        $data['unread_count'] = $this->Model_notifications->count_unread_notifications($recruiter_id);
        $data['recruiter_id'] = $recruiter_id;
       
        $this->setup_breadcrumbs();
        load_custom_page($this->folder.'/'.$this->pageName.'/view_dashboard', $data);
    }

    /**
     * Get the logged-in recruiter's ID
     */
    private function get_recruiter_id() {
        $login_data = $this->session->userdata('login');
        
        if (!empty($login_data['recruiter'])) {
            $recruiter = $login_data['recruiter'];
            return !empty($recruiter['id']) ? $recruiter['id'] : null;
        }
        
        return null;
    }

    /**
     * AJAX method to mark notification as read
     */
    public function ajax_mark_notification_read() {
        if (!is_ajax()) {
            ajax_error('Invalid request');
            return;
        }

        $notification_id = $this->input->post('notification_id');
        $recruiter_id = $this->get_recruiter_id();

        if (empty($notification_id) || empty($recruiter_id)) {
            ajax_error('Invalid parameters');
            return;
        }

        $result = $this->Model_notifications->mark_as_read($notification_id, $recruiter_id);
        
        if ($result) {
            $unread_count = $this->Model_notifications->count_unread_notifications($recruiter_id);
            ajax_return([
                'success' => true,
                'unread_count' => $unread_count
            ]);
        } else {
            ajax_error('Failed to mark notification as read');
        }
    }

    /**
     * AJAX method to mark all notifications as read
     */
    public function ajax_mark_all_read() {
        if (!is_ajax()) {
            ajax_error('Invalid request');
            return;
        }

        $recruiter_id = $this->get_recruiter_id();

        if (empty($recruiter_id)) {
            ajax_error('Invalid recruiter');
            return;
        }

        $result = $this->Model_notifications->mark_all_as_read($recruiter_id);
        
        if ($result) {
            ajax_return([
                'success' => true,
                'unread_count' => 0
            ]);
        } else {
            ajax_error('Failed to mark notifications as read');
        }
    }

    /**
     * Page to view all notifications
     */
    public function notifications() {
        $recruiter_id = $this->get_recruiter_id();
        
        $data['notifications'] = $this->Model_notifications->get_all_notifications($recruiter_id);
        $data['unread_count'] = $this->Model_notifications->count_unread_notifications($recruiter_id);
        
        $this->breadcrumbs = array(
            array(
                'title' => lang('label_dashboard'),
                'url' => url($this->pageName)
            ),
            array(
                'title' => 'Notifications',
                'url' => url($this->pageName . '/notifications')
            )
        );
        
        load_custom_page($this->folder.'/'.$this->pageName.'/view_notifications', $data);
    }
}