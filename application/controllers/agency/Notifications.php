<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Notifications extends CRUD_Controller
{
    public $pageName = 'notifications';
    public $group = 'Notifications';
    public $folder = 'agency';
    public $model = 'Model_notifications';
    public $singular = 'notification';
    public $plural = 'notifications';
    public $listing = true;
    public $hideSubNav = false;
    public $abling = false;
    public $deleting = false;
    public $adding = false;
    public $editing = false;

    public function __construct()
    {
        parent::__construct();
        $this->folder = 'agency';

        // Allow only agencies
        $login_data = $this->session->userdata('login');
        if (empty($login_data['agency'])) {
            redirect('agency/login');
        }

        // Load model first
        $this->load->model('agency/' . $this->model);
        
        $this->setup_listing();
        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }

    private function setup_listing(): void
    {
        $this->listFields = array(
            'title' => array('label' => lang('label_title'), 'sort' => true),
            'message' => array('label' => lang('label_message'), 'sort' => true),
            'type' => array(
                'label' => lang('label_type'),
                'sort' => true,
                'type' => 'badge',
                'options' => array(
                    'candidate_applied' => array('class' => 'badge-primary', 'label' => 'Candidate Applied'),
                    'status_changed' => array('class' => 'badge-warning', 'label' => 'Status Changed'),
                    'job_added' => array('class' => 'badge-success', 'label' => 'New Job'),
                    'system' => array('class' => 'badge-info', 'label' => 'System')
                )
            ),
            'first_name' => array(
                'label' => lang('label_candidate'),
                'sort' => false,
                'field' => "CONCAT(c.first_name, ' ', c.last_name) AS candidate_name",
                'function' => function($value, $row) {
                    if (!empty($row->first_name)) {
                        return $row->first_name . ' ' . $row->last_name;
                    }
                    return '-';
                }
            ),
            'job_name' => array('label' => lang('label_job'), 'sort' => true),
            'recruiter_company' => array('label' => lang('label_recruiter'), 'sort' => true),
            'created_at' => array(
                'label' => lang('label_created_at'), 
                'sort' => true, 
                'type' => 'datetime',
                'function' => function($value, $row) {
                    return date('M j, Y g:i A', strtotime($value));
                }
            ),
            'is_read' => array(
                'label' => lang('label_status'),
                'sort' => true,
                'type' => 'badge',
                'options' => array(
                    '0' => array('class' => 'badge-danger', 'label' => 'Unread'),
                    '1' => array('class' => 'badge-secondary', 'label' => 'Read')
                )
            ),
        );

        $this->listActions = array(
            'mark_read' => array(
                'label' => lang('label_mark_read'),
                'url' => url($this->pageName . '/mark_read/{id}'),
                'icon' => 'fa-eye',
                'class' => 'mark-read-btn',
                'function' => function($str, $row) {
                    return $row->is_read ? false : $str;
                }
            ),
            'view_related' => array(
                'label' => lang('label_view_related'),
                'url' => url($this->pageName . '/view_related/{id}'),
                'icon' => 'fa-external-link',
                'class' => 'view-related-btn'
            ),
        );

        // Get notification types safely
        $notification_types = array(
            'candidate_applied' => 'Candidate Applications',
            'status_changed' => 'Status Changes', 
            'job_added' => 'New Jobs',
            'system' => 'System Notifications'
        );

        $this->filters = array(
            'type' => array(
                'label' => lang('label_type'),
                'type' => 'dropdown',
                'field' => 'n.type',
                'options' => $notification_types,
            ),
            'is_read' => array(
                'label' => lang('label_status'),
                'type' => 'dropdown',
                'field' => 'n.is_read',
                'options' => array(
                    '0' => 'Unread',
                    '1' => 'Read'
                ),
            ),
            'date_range' => array(
                'label' => lang('label_date_range'),
                'type' => 'date_range',
                'field' => 'n.created_at',
            ),
        );
    }

    public function index()
    {
        $this->breadcrumbs = array(
            array('title' => lang($this->pageName . '_heading'), 'url' => redir($this->pageName, true)),
        );
        
        // Get unread count for display
        $login_data = $this->session->userdata('login');
        $agency_id = $login_data['agency']['id'] ?? 0;
        $unread_count = $this->{$this->model}->get_unread_count($agency_id);
        
        $this->view = 'listing';
        $this->load->view($this->folder . '/view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading' => lang($this->pageName . '_heading') . ($unread_count > 0 ? " <span class='badge badge-danger'>{$unread_count} Unread</span>" : ""),
            'noRows' => lang($this->pageName . '_no_rows'),
            'extra_actions' => $this->get_extra_actions(),
        ));
        $this->load->view($this->folder . '/view_footer');
    }

    /**
     * Get extra actions for notifications page
     */
    private function get_extra_actions()
    {
        $login_data = $this->session->userdata('login');
        $agency_id = $login_data['agency']['id'] ?? 0;
        $unread_count = $this->{$this->model}->get_unread_count($agency_id);
        
        $actions = '';
        
        if ($unread_count > 0) {
            $actions .= '<button type="button" class="btn btn-success btn-mark-all-read" data-url="' . url($this->pageName . '/mark_all_read') . '">';
            $actions .= '<i class="fa fa-check-double"></i> Mark All as Read';
            $actions .= '</button>';
        }
        
        return $actions;
    }

    /**
     * Mark single notification as read
     */
    public function mark_read($id)
    {
        $login_data = $this->session->userdata('login');
        $agency_id = $login_data['agency']['id'] ?? 0;
        
        $result = $this->{$this->model}->mark_as_read($id, $agency_id);
        
        if ($result) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
                return;
            } else {
                $this->session->set_flashdata('success', 'Notification marked as read');
            }
        } else {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'error' => 'Failed to mark notification as read']);
                return;
            } else {
                $this->session->set_flashdata('error', 'Failed to mark notification as read');
            }
        }
        
        redirect(redir($this->pageName, true));
    }

    /**
     * Mark all notifications as read
     */
    public function mark_all_read()
    {
        $login_data = $this->session->userdata('login');
        $agency_id = $login_data['agency']['id'] ?? 0;
        
        $result = $this->{$this->model}->mark_all_as_read($agency_id);
        
        if ($result) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
                return;
            } else {
                $this->session->set_flashdata('success', 'All notifications marked as read');
            }
        } else {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'error' => 'Failed to mark notifications as read']);
                return;
            } else {
                $this->session->set_flashdata('error', 'Failed to mark notifications as read');
            }
        }
        
        redirect(redir($this->pageName, true));
    }

        /**
     * View related entity (candidate, job, etc.)
     */
    public function view_related($id)
    {
        $login_data = $this->session->userdata('login');
        $agency_id = $login_data['agency']['id'] ?? 0;
        
        // Get notification
        $notification = $this->db->where('id', $id)
                                ->where('receiver_type', 'agency')
                                ->where('receiver_id', $agency_id)
                                ->get('notifications')
                                ->row();
        
        if (!$notification) {
            show_404();
        }
        
        // Mark as read when viewing
        $this->{$this->model}->mark_as_read($id, $agency_id);
        
        // Redirect based on related entity
        switch ($notification->related_entity) {
            case 'candidate':
                redirect('agency/candidates/view/' . $notification->related_entity_id);
                break;
            case 'job':
                redirect('agency/jobs_listings/view/' . $notification->related_entity_id); // This should point to your jobs listings
                break;
            case 'agency':
                redirect('agency/dashboard');
                break;
            default:
                $this->session->set_flashdata('info', 'No specific action for this notification type');
                redirect(redir($this->pageName, true));
                break;
        }
    }

    /**
     * AJAX get unread count for navbar
     */
    public function ajax_get_unread_count()
    {
        $login_data = $this->session->userdata('login');
        $agency_id = $login_data['agency']['id'] ?? 0;
        $unread_count = $this->{$this->model}->get_unread_count($agency_id);
        
        echo json_encode(['unread_count' => $unread_count]);
    }

    /**
     * AJAX get recent notifications for dropdown
     */
    public function ajax_get_recent_notifications()
    {
        $login_data = $this->session->userdata('login');
        $agency_id = $login_data['agency']['id'] ?? 0;
        
        $notifications = $this->{$this->model}->get_agency_notifications($agency_id, 5);
        
        $html = '';
        if ($notifications->num_rows() > 0) {
            foreach ($notifications->result() as $notification) {
                $time_ago = $this->time_ago($notification->created_at);
                $read_class = $notification->is_read ? '' : 'unread';
                $badge_class = $notification->is_read ? 'badge-secondary' : 'badge-danger';
                
                $html .= '<div class="notification-item ' . $read_class . '" data-id="' . $notification->id . '">';
                $html .= '<div class="notification-content">';
                $html .= '<h6 class="notification-title">' . $notification->title . '</h6>';
                $html .= '<p class="notification-message">' . $notification->message . '</p>';
                $html .= '<small class="text-muted">' . $time_ago . '</small>';
                $html .= '</div>';
                if (!$notification->is_read) {
                    $html .= '<span class="badge ' . $badge_class . '">New</span>';
                }
                $html .= '</div>';
            }
        } else {
            $html = '<div class="notification-item">';
            $html .= '<p class="text-muted text-center">No notifications</p>';
            $html .= '</div>';
        }
        
        echo json_encode(['html' => $html, 'count' => $notifications->num_rows()]);
    }

    /**
     * Helper function for time ago
     */
    private function time_ago($datetime)
    {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } else {
            return floor($diff / 86400) . ' days ago';
        }
    }

}