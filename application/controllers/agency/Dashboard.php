<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CRUD_Controller {

    public $pageName    = 'dashboard';
    public $group       = 'Dashboard';
    public $folder      = 'agency';
    public $view        = '';
    public $model       = 'Model_dashboard';
    public $sorting     = array();
    public $singular    = 'dashboard';
    public $plural      = 'dashboard';
    public $adding      = false;
    public $hideSubNav  = true;

    public function __construct() {
        parent::__construct();

        // Check if user is logged in as agency
        $login_data = $this->session->userdata('login');
        $is_agency_logged_in = !empty($login_data['agency']);
        
        if (!$is_agency_logged_in) {
            redirect('agency/login');
        }

        $this->load->model($this->folder.'/'.$this->model);
        $this->load->model('agency/Model_notifications'); // Changed from recruiter to agency
        $this->zone = array(
            'title' => lang('label_dashboard'),
            'url'   => url('agency/'.$this->pageName)
        );
    }

    public function index() {
        // Get agency ID
        $agency_id = $this->get_agency_id();
        
        // Get notifications for the agency - using agency model methods
        $data['notifications'] = $this->Model_notifications->get_agency_notifications($agency_id, 5)->result();
        $data['unread_count'] = $this->Model_notifications->get_unread_count($agency_id);
        $data['agency_id'] = $agency_id;
       
        $this->setup_breadcrumbs();
        $this->load->view($this->folder.'/view_header', $data);
        $this->load->view('agency/dashboard/view_dashboard', $data);
        $this->load->view($this->folder.'/view_footer');
    }

    /**
     * Get the logged-in agency's ID
     */
    private function get_agency_id() {
        $login_data = $this->session->userdata('login');
        
        if (!empty($login_data['agency'])) {
            $agency = $login_data['agency'];
            // Try agency_id first, then fall back to id
            if (!empty($agency['agency_id'])) {
                return $agency['agency_id'];
            } elseif (!empty($agency['id'])) {
                return $agency['id'];
            }
        }
        
        return null;
    }

    /**
     * AJAX method to mark notification as read for agency
     */
    public function ajax_mark_notification_read() {
        if (!is_ajax()) {
            ajax_error('Invalid request');
            return;
        }

        $notification_id = $this->input->post('notification_id');
        $agency_id = $this->get_agency_id();

        if (empty($notification_id) || empty($agency_id)) {
            ajax_error('Invalid parameters');
            return;
        }

        $result = $this->Model_notifications->mark_as_read($notification_id, $agency_id);
        
        if ($result) {
            $unread_count = $this->Model_notifications->get_unread_count($agency_id);
            ajax_return([
                'success' => true,
                'unread_count' => $unread_count
            ]);
        } else {
            ajax_error('Failed to mark notification as read');
        }
    }

    /**
     * AJAX method to mark all notifications as read for agency
     */
    public function ajax_mark_all_read() {
        if (!is_ajax()) {
            ajax_error('Invalid request');
            return;
        }

        $agency_id = $this->get_agency_id();

        if (empty($agency_id)) {
            ajax_error('Invalid agency');
            return;
        }

        $result = $this->Model_notifications->mark_all_as_read($agency_id);
        
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
     * Page to view all notifications for agency
     */
    public function notifications() {
        $agency_id = $this->get_agency_id();
        
        $data['notifications'] = $this->Model_notifications->get_agency_notifications($agency_id)->result();
        $data['unread_count'] = $this->Model_notifications->get_unread_count($agency_id);
        
        $this->breadcrumbs = array(
            array(
                'title' => lang('label_dashboard'),
                'url' => url('agency/'.$this->pageName)
            ),
            array(
                'title' => 'Notifications',
                'url' => url('agency/'.$this->pageName . '/notifications')
            )
        );
        
        $this->load->view($this->folder.'/view_header', $data);
        $this->load->view('agency/dashboard/view_notifications', $data);
        $this->load->view($this->folder.'/view_footer');
    }
}