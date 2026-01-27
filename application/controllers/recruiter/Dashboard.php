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
        
        // Get recruiter details
        $recruiter_details = $this->get_recruiter_details($recruiter_id);
        
        // Get notifications for the recruiter
        $data['notifications'] = $this->Model_notifications->get_unread_notifications($recruiter_id);
        $data['unread_count'] = $this->Model_notifications->count_unread_notifications($recruiter_id);
        $data['recruiter_id'] = $recruiter_id;
        
        // Get KPIs and analytics (only for advanced view)
        $data['stats'] = $this->get_recruiter_statistics($recruiter_id);
        $data['recent_submissions'] = $this->get_recent_submissions($recruiter_id);
        $data['upcoming_tasks'] = $this->get_upcoming_tasks($recruiter_id);
        $data['recruiter_details'] = $recruiter_details;
        
        // Get current dashboard view preference
        $data['dashboard_view'] = $this->session->userdata('recruiter_dashboard_view') ?: 'advanced'; // Default to advanced
        $data['toggle_url'] = site_url('recruiter/dashboard/toggle_view');
       
        $this->setup_dashboard_breadcrumbs();
        
        // Load appropriate view based on preference
        if ($data['dashboard_view'] === 'simple') {
            load_custom_page($this->folder.'/'.$this->pageName.'/view_dashboard', $data);
        } else {
            load_custom_page($this->folder.'/'.$this->pageName.'/view_dashboard_advanced', $data);
        }
    }

    /**
     * Toggle dashboard view between simple and advanced
     */
    public function toggle_view() {
        $current_view = $this->session->userdata('recruiter_dashboard_view') ?: 'advanced';
        $new_view = ($current_view === 'simple') ? 'advanced' : 'simple';
        
        $this->session->set_userdata('recruiter_dashboard_view', $new_view);
        
        // Redirect back to dashboard
        redirect('recruiter/dashboard');
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
     * Get recruiter details
     */
    private function get_recruiter_details($recruiter_id) {
        $this->db->select('first_name, last_name, email,  phone, created_at');
        $this->db->from('recruiters');
        $this->db->where('id', $recruiter_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        
        return $this->db->get()->row();
    }

    /**
     * Get recruiter statistics for dashboard KPIs
     */
    private function get_recruiter_statistics($recruiter_id) {
        if (empty($recruiter_id)) {
            return null;
        }

        $stats = new stdClass();
        
        // 1. Total candidates submitted
        $this->db->select('COUNT(*) as total_candidates');
        $this->db->from('candidates');
        $this->db->where('assigned_agent_id', $recruiter_id);
        $this->db->or_where('recruiter_id', $recruiter_id);
        $this->db->where('removed', 0);
        $result = $this->db->get()->row();
        $stats->total_candidates = $result ? $result->total_candidates : 0;
        
        // 2. Candidates by status
        $status_counts = [
            'new' => 0,
            'reviewed' => 0,
            'shortlisted' => 0,
            'interviewed' => 0,
            'hired' => 0,
            'rejected' => 0,
            'on_hold' => 0
        ];
        
        $this->db->select('status, COUNT(*) as count');
        $this->db->from('candidates');
        $this->db->where('assigned_agent_id', $recruiter_id);
        $this->db->or_where('recruiter_id', $recruiter_id);
        $this->db->where('removed', 0);
        $this->db->group_by('status');
        $status_results = $this->db->get()->result();
        
        foreach ($status_results as $row) {
            if (isset($status_counts[$row->status])) {
                $status_counts[$row->status] = $row->count;
            }
        }
        $stats->status_counts = $status_counts;
        
        // 3. Candidates submitted this month
        $this->db->select('COUNT(*) as this_month');
        $this->db->from('candidates');
        $this->db->where('assigned_agent_id', $recruiter_id);
        $this->db->or_where('recruiter_id', $recruiter_id);
        $this->db->where('MONTH(application_date)', date('m'));
        $this->db->where('YEAR(application_date)', date('Y'));
        $this->db->where('removed', 0);
        $result = $this->db->get()->row();
        $stats->candidates_this_month = $result ? $result->this_month : 0;
        
        // 4. Onboarding progress
        $this->db->select('
            COUNT(*) as total,
            SUM(CASE WHEN onboarding_stage = "completed" OR stage_position_offered = 1 THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN stage_hm_decision = 1 AND hm_decision = "accepted" THEN 1 ELSE 0 END) as hm_accepted,
            SUM(CASE WHEN stage_hm_decision = 1 AND hm_decision = "rejected" THEN 1 ELSE 0 END) as hm_rejected
        ');
        $this->db->from('candidates');
        $this->db->group_start();
        $this->db->where('assigned_agent_id', $recruiter_id);
        $this->db->or_where('recruiter_id', $recruiter_id);
        $this->db->group_end();
        $this->db->where('removed', 0);
        $onboarding_result = $this->db->get()->row();
        
        $stats->onboarding_total = $onboarding_result ? $onboarding_result->total : 0;
        $stats->onboarding_completed = $onboarding_result ? $onboarding_result->completed : 0;
        $stats->hm_accepted = $onboarding_result ? $onboarding_result->hm_accepted : 0;
        $stats->hm_rejected = $onboarding_result ? $onboarding_result->hm_rejected : 0;
        
        // 5. Jobs with submitted candidates
        $this->db->select('COUNT(DISTINCT job_id) as active_jobs');
        $this->db->from('candidates');
        $this->db->group_start();
        $this->db->where('assigned_agent_id', $recruiter_id);
        $this->db->or_where('recruiter_id', $recruiter_id);
        $this->db->group_end();
        $this->db->where('removed', 0);
        $this->db->where('job_id IS NOT NULL');
        $result = $this->db->get()->row();
        $stats->active_jobs = $result ? $result->active_jobs : 0;
        
        // 6. Recent activity (last 7 days)
        $this->db->select('COUNT(*) as recent_activity');
        $this->db->from('candidates');
        $this->db->group_start();
        $this->db->where('assigned_agent_id', $recruiter_id);
        $this->db->or_where('recruiter_id', $recruiter_id);
        $this->db->group_end();
        $this->db->where('removed', 0);
        $this->db->where('updated_at >=', date('Y-m-d H:i:s', strtotime('-7 days')));
        $result = $this->db->get()->row();
        $stats->recent_activity = $result ? $result->recent_activity : 0;
        
        // 7. Calculate success rate (hired vs total)
        if ($stats->total_candidates > 0) {
            $stats->success_rate = round(($status_counts['hired'] / $stats->total_candidates) * 100, 1);
        } else {
            $stats->success_rate = 0;
        }
        
        // 8. Calculate onboarding completion rate
        if ($stats->onboarding_total > 0) {
            $stats->onboarding_rate = round(($stats->onboarding_completed / $stats->onboarding_total) * 100, 1);
        } else {
            $stats->onboarding_rate = 0;
        }
        
        return $stats;
    }

    /**
     * Get recent submissions (last 5 candidates)
     */
    private function get_recent_submissions($recruiter_id) {
        if (empty($recruiter_id)) {
            return [];
        }
        
        $this->db->select('c.*, j.name as job_name');
        $this->db->from('candidates c');
        $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
        $this->db->group_start();
        $this->db->where('c.assigned_agent_id', $recruiter_id);
        $this->db->or_where('c.recruiter_id', $recruiter_id);
        $this->db->group_end();
        $this->db->where('c.removed', 0);
        $this->db->order_by('c.created_at', 'DESC');
        $this->db->limit(5);
        
        return $this->db->get()->result();
    }

    /**
     * Get upcoming tasks (documents due, follow-ups, etc.)
     */
    private function get_upcoming_tasks($recruiter_id) {
        if (empty($recruiter_id)) {
            return [];
        }
        
        $tasks = [];
        
        // 1. Candidates with pending documents request
        $this->db->select('c.*, n.id as notification_id, n.metadata');
        $this->db->from('candidates c');
        $this->db->join('notifications n', 'n.related_entity_id = c.id AND n.type = "documents_request" AND n.is_read = 0', 'inner');
        $this->db->where('c.assigned_agent_id', $recruiter_id);
        $this->db->or_where('c.recruiter_id', $recruiter_id);
        $this->db->where('c.removed', 0);
        $this->db->where('n.receiver_type', 'recruiter');
        $this->db->where('n.receiver_id', $recruiter_id);
        $documents_pending = $this->db->get()->result();
        
        foreach ($documents_pending as $candidate) {
            $metadata = !empty($candidate->metadata) ? json_decode($candidate->metadata, true) : [];
            $tasks[] = [
                'type' => 'documents_request',
                'title' => 'Documents Required',
                'description' => 'Submit required documents for ' . $candidate->first_name . ' ' . $candidate->last_name,
                'candidate_id' => $candidate->id,
                'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                'notification_id' => $candidate->notification_id,
                'notes' => $metadata['notes'] ?? '',
                'priority' => 'high',
                'due_date' => date('Y-m-d', strtotime('+2 days'))
            ];
        }
        
        // 2. Candidates with HM decision pending (if applicable)
        $this->db->select('c.*, j.name as job_name');
        $this->db->from('candidates c');
        $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
        $this->db->group_start();
        $this->db->where('c.assigned_agent_id', $recruiter_id);
        $this->db->or_where('c.recruiter_id', $recruiter_id);
        $this->db->group_end();
        $this->db->where('c.stage_submitted_to_hm', 1);
        $this->db->where('c.stage_hm_decision', 0);
        $this->db->where('c.removed', 0);
        $hm_pending = $this->db->get()->result();
        
        foreach ($hm_pending as $candidate) {
            $submitted_date = !empty($candidate->stage_submitted_to_hm_at) ? $candidate->stage_submitted_to_hm_at : $candidate->updated_at;
            $days_pending = floor((time() - strtotime($submitted_date)) / (60 * 60 * 24));
            
            $tasks[] = [
                'type' => 'hm_decision_pending',
                'title' => 'Awaiting HM Decision',
                'description' => $candidate->first_name . ' ' . $candidate->last_name . ' for ' . ($candidate->job_name ?: 'Job'),
                'candidate_id' => $candidate->id,
                'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                'job_name' => $candidate->job_name,
                'days_pending' => $days_pending,
                'priority' => $days_pending > 7 ? 'high' : 'medium',
                'due_date' => date('Y-m-d', strtotime('+1 day'))
            ];
        }
        
        // Sort by priority and due date
        usort($tasks, function($a, $b) {
            $priority_order = ['high' => 1, 'medium' => 2, 'low' => 3];
            return $priority_order[$a['priority']] <=> $priority_order[$b['priority']];
        });
        
        return array_slice($tasks, 0, 5); // Return only top 5 tasks
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
        // Check if this is a POST request (form submission)
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            // If it's a POST without valid CSRF, redirect to GET version
            $csrf_name = $this->security->get_csrf_token_name();
            $csrf_token = $this->input->post($csrf_name);
            
            if (!$csrf_token || !hash_equals($this->security->get_csrf_hash(), $csrf_token)) {
                // Redirect to GET version of the page
                redirect('recruiter/dashboard/notifications', 'refresh');
                return;
            }
        }
        
        $recruiter_id = $this->get_recruiter_id();
        
        // Get all notifications
        $notifications = $this->Model_notifications->get_all_notifications($recruiter_id);
        
        // Load the jobs model to get UUIDs
        $this->load->model('recruiter/model_jobs');
        
        // Add job UUIDs to each notification
        foreach ($notifications as &$notification) {
            // Initialize job_uuid property
            $notification->job_uuid = '';
            
            // Only get UUID for job-related notifications (not HM decisions or documents requests)
            if ($notification->related_entity === 'job' && !empty($notification->related_entity_id)) {
                // Check if it's a job notification (not candidate/HM decision)
                $is_hm_decision = $notification->type === 'hm_decision';
                $is_documents_request = $notification->type === 'documents_request';
                
                if (!$is_hm_decision && !$is_documents_request) {
                    $job = $this->model_jobs->get_job_by_id($notification->related_entity_id);
                    if ($job && !empty($job->uuid)) {
                        $notification->job_uuid = $job->uuid;
                    }
                }
            }
        }
        
        $data['notifications'] = $notifications;
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

    public function check_documents_button() {
        echo "<h1>Documents Request Button Test</h1>";
        
        // Get a specific documents request notification
        $this->db->where('id', 615); // Use one of your notification IDs
        $notification = $this->db->get('notifications')->row();
        
        if (!$notification) {
            echo "No notification found";
            return;
        }
        
        echo "<h3>Notification Details:</h3>";
        echo "<pre>";
        print_r($notification);
        echo "</pre>";
        
        echo "<h3>Metadata:</h3>";
        $metadata = json_decode($notification->metadata);
        echo "<pre>";
        print_r($metadata);
        echo "</pre>";
        
        // Test the detection logic
        $is_documents_request = (
            $notification->type === 'documents_request' ||
            strpos($notification->title, 'Documents Required') !== false ||
            strpos($notification->title, 'Additional Documents') !== false ||
            (!empty($metadata->required_documents) || !empty($metadata->documents_notes))
        );
        
        echo "<h3>Detection Result:</h3>";
        echo "Is Documents Request? " . ($is_documents_request ? 'YES' : 'NO');
        
        if ($is_documents_request) {
            echo "<h3 style='color:green;'>✅ Button should appear!</h3>";
            echo "<button class='btn btn-warning' style='margin: 20px; padding: 10px;'>
                    <i class='fa fa-upload'></i> Submit Required Documents
                  </button>";
        } else {
            echo "<h3 style='color:red;'>❌ Button would NOT appear</h3>";
        }
    }

}