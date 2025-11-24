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
       
        $this->setup_dashboard_breadcrumbs();
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
    $notification_id = $this->input->post('notification_id');
    $recruiter_id = $this->session->userdata('login')['recruiter']['id'] ?? null;
    
    if ($recruiter_id && $notification_id) {
        $success = $this->Model_notifications->mark_as_read($notification_id, $recruiter_id);
        $unread_count = $this->Model_notifications->count_unread_notifications($recruiter_id);
        
        // Store in session to persist across redirects
        $this->session->set_userdata('notification_just_read', $notification_id);
        
        echo json_encode([
            'success' => $success,
            'unread_count' => $unread_count
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'unread_count' => 0
        ]);
    }
}

    /**
     * AJAX method to mark all notifications as read
     */
    public function ajax_mark_all_read() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $recruiter_id = $this->get_recruiter_id();

        if (empty($recruiter_id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid recruiter']);
            return;
        }

        $result = $this->Model_notifications->mark_all_as_read($recruiter_id);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'unread_count' => 0
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read']);
        }
    }

    /**
     * Page to view all notifications
     */
    public function notifications() {
        $recruiter_id = $this->get_recruiter_id();
        
        $data['notifications'] = $this->Model_notifications->get_all_notifications($recruiter_id);
        $data['unread_count'] = $this->Model_notifications->count_unread_notifications($recruiter_id);
        
        $this->setup_notifications_breadcrumbs();
        load_custom_page($this->folder.'/'.$this->pageName.'/view_notifications', $data);
    }

/**
 * AJAX method to get HM decision notifications for popup
 */
public function get_hm_decision_notifications() {
    // Enable AJAX check for security
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $recruiter_id = $this->get_recruiter_id();
    
    if (empty($recruiter_id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid recruiter']);
        return;
    }

    // Get unread HM decision notifications
    $this->db->select('n.*, c.first_name, c.last_name, c.reference_number as candidate_ref, 
                      j.name as job_name, j.reference_number as job_ref');
    $this->db->from('notifications n');
    $this->db->join('candidates c', 'c.id = n.related_entity_id', 'left');
    $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
    $this->db->where('n.receiver_type', 'recruiter');
    $this->db->where('n.receiver_id', $recruiter_id);
    $this->db->where('n.type', 'hm_decision');
    $this->db->where('n.is_read', 0);
    $this->db->order_by('n.created_at', 'DESC');
    
    $notifications = $this->db->get()->result();

    // Format notifications for response
    $formatted_notifications = [];
    foreach ($notifications as $notification) {
        $metadata = !empty($notification->metadata) ? json_decode($notification->metadata, true) : null;
        
        $formatted_notifications[] = [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'is_read' => $notification->is_read,
            'created_at' => $notification->created_at,
            'candidate_name' => $notification->first_name . ' ' . $notification->last_name,
            'candidate_ref' => $notification->candidate_ref,
            'job_name' => $notification->job_name,
            'job_ref' => $notification->job_ref,
            'related_entity_id' => $notification->related_entity_id,
            'metadata' => $metadata
        ];
    }

    // Set proper content type for JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'notifications' => $formatted_notifications
    ]);
}

/**
 * AJAX method to mark HM decision notification as read
 */
public function mark_hm_notification_read() {
    // Enable AJAX check for security
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $notification_id = $this->input->post('notification_id');
    $recruiter_id = $this->get_recruiter_id();

    if (empty($notification_id) || empty($recruiter_id)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }

    $result = $this->Model_notifications->mark_as_read($notification_id, $recruiter_id);
    
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
    }
}



    /**
     * Get HM notifications (legacy method for compatibility)
     */
    public function get_hm_notifications() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $recruiter_id = $this->get_recruiter_id();
        
        if (empty($recruiter_id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid recruiter']);
            return;
        }

        $notifications = $this->Model_notifications->get_hm_decision_notifications($recruiter_id, 5);
        
        // Format notifications for response
        $formatted_notifications = [];
        foreach ($notifications as $notification) {
            $metadata = !empty($notification->metadata) ? json_decode($notification->metadata) : null;
            
            $formatted_notifications[] = [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'is_read' => $notification->is_read,
                'created_at' => $notification->created_at,
                'metadata' => $metadata
            ];
        }

        echo json_encode([
            'success' => true,
            'notifications' => $formatted_notifications
        ]);
    }

    /**
     * Setup breadcrumbs for dashboard page
     */
    public function setup_dashboard_breadcrumbs() {
        $this->breadcrumbs = array(
            array(
                'title' => lang('label_dashboard'),
                'url' => url($this->pageName)
            )
        );
    }

    /**
     * Setup breadcrumbs for notifications page
     */
    public function setup_notifications_breadcrumbs() {
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
    }

    /**
 * Test method to check if controller is working
 */
public function test_hm_endpoint() {
    echo "<h1>HM Endpoint Test</h1>";
    echo "<p>If you can see this, the controller is working.</p>";
    echo "<p>Method: get_hm_decision_notifications</p>";
    
    // Test the method directly
    $this->get_hm_decision_notifications();
}

/**
 * Upload document for candidate (called from notifications)
 */
public function upload_document() {
    $candidate_id = $this->input->post('candidate_id');
    $notification_id = $this->input->post('notification_id');
    $document_name = $this->input->post('document_name');
    $document_type = $this->input->post('document_type');
    $description = $this->input->post('description');

    // Check if file was uploaded
    if (empty($_FILES['document_file']['name'])) {
        ajax_return(['success' => false, 'message' => 'Please select a file to upload.']);
        return;
    }

    // Upload configuration
    $config['upload_path'] = './uploads/candidate_documents/';
    $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
    $config['max_size'] = 10240; // 10MB
    $config['encrypt_name'] = true;

    // Create upload directory if it doesn't exist
    if (!is_dir($config['upload_path'])) {
        mkdir($config['upload_path'], 0755, true);
    }

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('document_file')) {
        ajax_return(['success' => false, 'message' => $this->upload->display_errors()]);
        return;
    }

    $upload_data = $this->upload->data();

    // Save document to database
    $document_data = [
        'candidate_id' => $candidate_id,
        'document_name' => $document_name,
        'file_name' => $upload_data['file_name'],
        'file_path' => 'uploads/candidate_documents/' . $upload_data['file_name'],
        'file_size' => $upload_data['file_size'],
        'file_type' => $upload_data['file_type'],
        'uploaded_by' => loginID('recruiter'),
        'uploaded_by_type' => 'recruiter',
        'document_type' => $document_type,
        'description' => $description,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $this->load->model('recruiter/Model_candidates');
    $result = $this->Model_candidates->save_candidate_document($document_data);

    if ($result) {
        // Send notification to agency
        $this->load->model('recruiter/Model_notifications');
        $this->Model_notifications->create_documents_uploaded_notification($candidate_id, loginID('recruiter'), 1);

        // Mark the original notification as read
        $this->load->model('recruiter/Model_notifications');
        $this->Model_notifications->mark_as_read($notification_id, loginID('recruiter'));

        ajax_return(['success' => true, 'message' => 'Document uploaded successfully!']);
    } else {
        ajax_return(['success' => false, 'message' => 'Failed to save document information.']);
    }
}
}