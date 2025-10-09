<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Notifications extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Check if user is logged in as recruiter
        $login_data = $this->session->userdata('login');
        if (empty($login_data['recruiter'])) {
            redirect('recruiter/dashboard');
        }
        $this->load->helper('notification'); // ← ADD HERE
        $this->load->model('Model_notifications');
        $this->recruiter_id = $login_data['recruiter']['id'];
    }

    /**
     * Load notifications in header (called from view_header)
     */
    public function load_header_notifications()
    {
        $unread_count = $this->Model_notifications->get_unread_count('recruiter', $this->recruiter_id);
        $notifications = $this->Model_notifications->get_for_receiver('recruiter', $this->recruiter_id, 5, 0, true);
        
        return [
            'unread_notifications_count' => $unread_count,
            'notifications' => $notifications
        ];
    }

    /**
     * Mark notification as read (AJAX)
     */
    public function mark_as_read()
    {
        $notification_id = $this->input->post('notification_id');
        
        if ($notification_id) {
            $success = $this->Model_notifications->mark_as_read($notification_id, 'recruiter', $this->recruiter_id);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    /**
     * Mark all notifications as read (AJAX)
     */
    public function mark_all_read()
    {
        $success = $this->Model_notifications->mark_all_as_read('recruiter', $this->recruiter_id);
        echo json_encode(['success' => $success]);
    }

    /**
     * Get unread count (AJAX)
     */
    public function get_count()
    {
        $unread_count = $this->Model_notifications->get_unread_count('recruiter', $this->recruiter_id);
        echo json_encode(['unread_count' => $unread_count]);
    }

    /**
     * Full notifications page
     */
    /**
 * Full notifications page
 */
public function index()
{
    $this->load->library('pagination');
    
    $config = [
        'base_url' => site_url('recruiter/notifications'),
        'total_rows' => $this->Model_notifications->get_unread_count('recruiter', $this->recruiter_id) + 
                       $this->Model_notifications->get_unread_count('recruiter', $this->recruiter_id, false),
        'per_page' => 20,
        'uri_segment' => 3
    ];
    
    $this->pagination->initialize($config);
    
    $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
    
    $notifications = $this->Model_notifications->get_for_receiver('recruiter', $this->recruiter_id, 20, $page);
    
    $data = [
        'notifications' => $notifications,
        'pagination' => $this->pagination->create_links(),
        'unread_count' => $this->Model_notifications->get_unread_count('recruiter', $this->recruiter_id)
    ];
    
    $this->load->view('recruiter/view_header');
    $this->load->view('recruiter/notifications/view_notifications', $data); // Updated view name
    $this->load->view('recruiter/view_footer');
}
}