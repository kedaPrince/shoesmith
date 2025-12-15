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
        $this->load->model('agency/Model_notifications');
        $this->zone = array(
            'title' => lang('label_dashboard'),
            'url'   => url('agency/'.$this->pageName)
        );
    }

    public function index() {
        // Get agency ID
        $agency_id = $this->get_agency_id();
        
        // Get agency stats
        $data['stats'] = $this->get_agency_stats($agency_id);
        
        // Get agency name
        $data['agency_name'] = $this->get_agency_name($agency_id);
        
        // Get recent activity
        $data['recent_activity'] = $this->get_recent_activity($agency_id);
        
        // Get notifications
        $data['notifications'] = $this->Model_notifications->get_agency_notifications($agency_id, 5)->result();
        $data['unread_count'] = $this->Model_notifications->get_unread_count($agency_id);
        $data['agency_id'] = $agency_id;
       
        $this->setup_breadcrumbs();
        $this->load->view($this->folder.'/view_header', $data);
        $this->load->view('agency/dashboard/view_dashboard_enhanced', $data);
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

    private function get_agency_stats($agency_id) {
        $stats = new stdClass();
        
        // Total candidates for this agency
        $stats->total_candidates = $this->db
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.removed', 0)
            ->count_all_results();
        
        // Active jobs for this agency
        $stats->active_jobs = $this->db
            ->from('mod_jobs')
            ->where('agency_id', $agency_id)
            ->where('removed', 0)
            ->count_all_results();
        
        // Status counts
        $status_query = $this->db
            ->select('c.status, COUNT(*) as count')
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.removed', 0)
            ->group_by('c.status')
            ->get();
        
        $stats->status_counts = [
            'hired' => 0,
            'interviewed' => 0,
            'shortlisted' => 0,
            'rejected' => 0,
            'new' => 0
        ];
        
        if ($status_query->num_rows() > 0) {
            foreach ($status_query->result() as $status) {
                if (isset($stats->status_counts[$status->status])) {
                    $stats->status_counts[$status->status] = $status->count;
                }
            }
        }
        
        // Onboarding statistics
        // Total candidates in onboarding
        $total_onboarding = $this->db
            ->select('COUNT(DISTINCT cop.candidate_id) as total')
            ->from('candidate_onboarding_progress cop')
            ->join('mod_jobs j', 'j.id = cop.job_id AND j.removed = 0')
            ->join('candidates c', 'c.id = cop.candidate_id AND c.removed = 0')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->get()
            ->row();
        
        $completed_onboarding = $this->db
            ->select('COUNT(DISTINCT cop.candidate_id) as completed')
            ->from('candidate_onboarding_progress cop')
            ->join('mod_jobs j', 'j.id = cop.job_id AND j.removed = 0')
            ->join('candidates c', 'c.id = cop.candidate_id AND c.removed = 0')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('cop.onboarding_stage', 'completed')
            ->get()
            ->row();
        
        $stats->onboarding_rate = ($total_onboarding && $total_onboarding->total > 0) ? 
            round(($completed_onboarding->completed / $total_onboarding->total) * 100) : 0;
        
        // Onboarding stage counts
        $onboarding_counts_query = $this->db
            ->select('cop.onboarding_stage, COUNT(DISTINCT cop.candidate_id) as count')
            ->from('candidate_onboarding_progress cop')
            ->join('mod_jobs j', 'j.id = cop.job_id AND j.removed = 0')
            ->join('candidates c', 'c.id = cop.candidate_id AND c.removed = 0')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->group_by('cop.onboarding_stage')
            ->get();
        
        $stats->onboarding_counts = [
            'not_started' => 0,
            'under_review' => 0,
            'submitted_to_hm' => 0,
            'requested_docs' => 0,
            'position_offered' => 0,
            'completed' => 0
        ];
        
        if ($onboarding_counts_query->num_rows() > 0) {
            foreach ($onboarding_counts_query->result() as $stage) {
                $key = str_replace('stage_', '', $stage->onboarding_stage);
                if (isset($stats->onboarding_counts[$key])) {
                    $stats->onboarding_counts[$key] = $stage->count;
                } elseif (isset($stats->onboarding_counts[$stage->onboarding_stage])) {
                    $stats->onboarding_counts[$stage->onboarding_stage] = $stage->count;
                }
            }
        }
        
        // Calculate average time to hire (in days)
        $hired_candidates = $this->db
            ->select('DATEDIFF(c.updated_at, c.created_at) as hire_days')
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.status', 'hired')
            ->where('c.removed', 0)
            ->where('c.updated_at IS NOT NULL')
            ->get()
            ->result();
        
        $total_days = 0;
        $count = 0;
        
        foreach ($hired_candidates as $candidate) {
            if ($candidate->hire_days > 0) {
                $total_days += $candidate->hire_days;
                $count++;
            }
        }
        
        $stats->avg_time_to_hire = $count > 0 ? round($total_days / $count) : 0;
        
        // Additional stats for the dashboard
        // Recent candidates (last 7 days)
        $stats->recent_candidates = $this->db
            ->select('COUNT(*) as count')
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.removed', 0)
            ->where('c.created_at >=', date('Y-m-d', strtotime('-7 days')))
            ->count_all_results();
        
        // Candidates awaiting review
        $stats->awaiting_review = $this->db
            ->select('COUNT(*) as count')
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.removed', 0)
            ->where('c.status', 'new')
            ->count_all_results();
        
        // Candidates in interview stage
        $stats->in_interview = $this->db
            ->select('COUNT(*) as count')
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.removed', 0)
            ->where('c.status', 'interviewed')
            ->count_all_results();
        
        return $stats;
    }

    private function get_agency_name($agency_id) {
        $agency = $this->db
            ->select('name')
            ->from('agencies')
            ->where('id', $agency_id)
            ->where('removed', 0)
            ->get()
            ->row();
        
        return $agency ? $agency->name : 'Agency Manager';
    }

    private function get_recent_activity($agency_id) {
        $activities = [];
        
        // Recent candidates added
        $recent_candidates = $this->db
            ->select('c.first_name, c.last_name, c.status, c.created_at')
            ->select("'candidate_added' as type")
            ->select("CONCAT('New candidate: ', c.first_name, ' ', c.last_name) as title")
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.removed', 0)
            ->order_by('c.created_at', 'DESC')
            ->limit(3)
            ->get()
            ->result_array();
        
        $activities = array_merge($activities, $recent_candidates);
        
        // Recent job postings
        $recent_jobs = $this->db
            ->select('name, created_at')
            ->select("'job_posted' as type")
            ->select("CONCAT('Job posted: ', name) as title")
            ->from('mod_jobs')
            ->where('agency_id', $agency_id)
            ->where('removed', 0)
            ->order_by('created_at', 'DESC')
            ->limit(2)
            ->get()
            ->result_array();
        
        $activities = array_merge($activities, $recent_jobs);
        
        // Recent hires
        $recent_hires = $this->db
            ->select('c.first_name, c.last_name, c.status_updated_at as created_at')
            ->select("'candidate_hired' as type")
            ->select("CONCAT('Candidate hired: ', c.first_name, ' ', c.last_name) as title")
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.status', 'hired')
            ->where('c.removed', 0)
            ->where('c.status_updated_at IS NOT NULL')
            ->order_by('c.status_updated_at', 'DESC')
            ->limit(2)
            ->get()
            ->result_array();
        
        $activities = array_merge($activities, $recent_hires);
        
        // Sort by date (newest first)
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Return only the 5 most recent activities
        return array_slice($activities, 0, 5);
    }

    // Advanced dashboard view
  public function advanced() {
    $agency_id = $this->get_agency_id();
    
    if (!$agency_id) {
        redirect('agency/login');
    }
    
    // Get comprehensive stats
    $data['stats'] = $this->get_advanced_agency_stats($agency_id);
    $data['agency_name'] = $this->get_agency_name($agency_id);
    $data['recent_activity'] = $this->get_recent_activity($agency_id);
    $data['recent_submissions'] = $this->get_recent_submissions($agency_id);
    $data['upcoming_tasks'] = $this->get_upcoming_tasks($agency_id);
    $data['notifications'] = $this->Model_notifications->get_agency_notifications($agency_id, 10)->result();
    $data['agency_id'] = $agency_id;
    
    $this->setup_breadcrumbs();
    
    $this->load->view($this->folder.'/view_header', $data);
    $this->load->view('agency/dashboard/view_dashboard_advanced', $data); // FIXED: Removed leading slash
    $this->load->view($this->folder.'/view_footer');
}

    private function get_advanced_agency_stats($agency_id) {
        $stats = new stdClass();
        
        // Basic stats
        $basic_stats = $this->get_agency_stats($agency_id);
        foreach ($basic_stats as $key => $value) {
            $stats->$key = $value;
        }
        
        // Additional advanced stats
        // HM Decision statistics
        $hm_stats = $this->db
            ->select('COUNT(*) as total, 
                     SUM(CASE WHEN hm_decision = "accepted" THEN 1 ELSE 0 END) as accepted,
                     SUM(CASE WHEN hm_decision = "rejected" THEN 1 ELSE 0 END) as rejected,
                     SUM(CASE WHEN hm_decision = "pending" THEN 1 ELSE 0 END) as pending')
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.removed', 0)
            ->where('c.hm_decision IS NOT NULL')
            ->get()
            ->row();
        
        $stats->hm_accepted = $hm_stats ? $hm_stats->accepted : 0;
        $stats->hm_rejected = $hm_stats ? $hm_stats->rejected : 0;
        $stats->hm_pending = $hm_stats ? $hm_stats->pending : 0;
        
        // Onboarding detailed stats - FIXED: Added table alias for onboarding_stage
        $onboarding_stats = $this->db
            ->select('COUNT(*) as total,
                     SUM(CASE WHEN cop.onboarding_stage = "completed" THEN 1 ELSE 0 END) as completed')
            ->from('candidate_onboarding_progress cop')
            ->join('mod_jobs j', 'j.id = cop.job_id AND j.removed = 0')
            ->join('candidates c', 'c.id = cop.candidate_id AND c.removed = 0')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->get()
            ->row();
        
        $stats->onboarding_total = $onboarding_stats ? $onboarding_stats->total : 0;
        $stats->onboarding_completed = $onboarding_stats ? $onboarding_stats->completed : 0;
        
        // Calculate success rate (hired vs total candidates)
        $success_rate = $this->db
            ->select('COUNT(*) as total,
                     SUM(CASE WHEN c.status = "hired" THEN 1 ELSE 0 END) as hired')
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.removed', 0)
            ->get()
            ->row();
        
        $stats->success_rate = ($success_rate && $success_rate->total > 0) ? 
            round(($success_rate->hired / $success_rate->total) * 100) : 0;
        
        // Recent activity count (last 7 days)
        $stats->recent_activity = $this->db
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.removed', 0)
            ->where('c.created_at >=', date('Y-m-d', strtotime('-7 days')))
            ->count_all_results();
        
        // This month's candidates
        $stats->candidates_this_month = $this->db
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->where('ca.agency_id', $agency_id)
            ->where('c.removed', 0)
            ->where('MONTH(c.created_at)', date('m'))
            ->where('YEAR(c.created_at)', date('Y'))
            ->count_all_results();
        
        return $stats;
    }

    private function get_recent_submissions($agency_id, $limit = 5) {
        return $this->db
            ->select('c.id, c.uuid, c.first_name, c.last_name, c.reference_number, c.status, c.created_at, j.name as job_name')
            ->from('candidates c')
            ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
            ->join('candidate_job_assignments cja', 'cja.candidate_id = c.id AND cja.removed = 0', 'left')
            ->join('mod_jobs j', 'j.id = cja.job_id', 'left')
            ->where('ca.agency_id', $agency_id)
            ->where('c.removed', 0)
            ->order_by('c.created_at', 'DESC')
            ->limit($limit)
            ->group_by('c.id')
            ->get()
            ->result();
    }

    private function get_upcoming_tasks($agency_id) {
    $tasks = [];
    
    // Documents pending review
    $docs_tasks = $this->db
        ->select('c.id as candidate_id, c.first_name, c.last_name, c.reference_number')
        ->select("'documents_request' as type")
        ->select("'Review submitted documents' as title")
        ->select("'Candidate has submitted required documents for review' as description")
        ->select("'high' as priority")
        ->select('NULL as due_date', false) // Use false to prevent escaping
        ->from('candidates c')
        ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
        ->join('candidate_onboarding_progress cop', 'cop.candidate_id = c.id', 'left')
        ->where('ca.agency_id', $agency_id)
        ->where('c.removed', 0)
        ->where('cop.documents_required', 1)
        ->where('cop.stage_documents_decision', 1)
        ->where('c.status !=', 'rejected')
        ->limit(3)
        ->get()
        ->result_array();
    
    $tasks = array_merge($tasks, $docs_tasks);
    
    // HM decisions pending
    $hm_tasks = $this->db
        ->select('c.id as candidate_id, c.first_name, c.last_name, c.reference_number')
        ->select("'hm_decision' as type")
        ->select("'Make HM decision' as title")
        ->select("'Hiring Manager decision is pending for candidate' as description")
        ->select("'medium' as priority")
        ->select('NULL as due_date', false) // Use false to prevent escaping
        ->from('candidates c')
        ->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner')
        ->where('ca.agency_id', $agency_id)
        ->where('c.removed', 0)
        ->where('c.hm_decision', 'pending')
        ->where('c.status IN ("reviewed", "shortlisted", "interviewed")')
        ->limit(2)
        ->get()
        ->result_array();
    
    $tasks = array_merge($tasks, $hm_tasks);
    
    return $tasks;
}

    /**
     * Setup breadcrumbs for dashboard
     * Must match parent class signature
     */
    public function setup_breadcrumbs($extraBefore = [], $extraAfter = [], $overideMain = []) {
        if (!empty($overideMain)) {
            $this->breadcrumbs = $overideMain;
        } else {
            $this->breadcrumbs = array(
                array(
                    'title' => lang('label_dashboard'),
                    'url'   => url('agency/'.$this->pageName)
                )
            );
        }
        
        // Add extra breadcrumbs before if provided
        if (!empty($extraBefore)) {
            $this->breadcrumbs = array_merge($extraBefore, $this->breadcrumbs);
        }
        
        // Add extra breadcrumbs after if provided
        if (!empty($extraAfter)) {
            $this->breadcrumbs = array_merge($this->breadcrumbs, $extraAfter);
        }
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
        
        // Setup breadcrumbs for notifications page
        $this->setup_breadcrumbs([], [
            array(
                'title' => 'Notifications',
                'url' => url('agency/'.$this->pageName . '/notifications')
            )
        ]);
        
        $this->load->view($this->folder.'/view_header', $data);
        $this->load->view('agency/dashboard/view_notifications', $data);
        $this->load->view($this->folder.'/view_footer');
    }

    // AJAX endpoint to get real-time stats
    public function get_dashboard_stats() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $agency_id = $this->get_agency_id();
        
        if (!$agency_id) {
            ajax_return(['success' => false, 'message' => 'Not logged in']);
            return;
        }
        
        $stats = $this->get_agency_stats($agency_id);
        
        ajax_return([
            'success' => true,
            'stats' => $stats
        ]);
    }
}