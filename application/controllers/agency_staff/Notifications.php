<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends CRUD_Controller
{
    public $pageName = 'notifications';
    public $group = 'Notifications';
    public $folder = 'agency_staff';
    public $view = '';
    public $model = 'Model_notifications';
    public $sorting = array('created_at' => 'DESC');
    public $singular = 'Notification';
    public $plural = 'Notifications';
    public $quickManage = false;

    public function __construct()
    {
        parent::__construct();

        $this->load->helper(['profile_helper', 'agency_access_helper']);

        $login_data = $this->session->userdata('login');
        $is_agency_logged_in = !empty($login_data['agency']);
        
        if (!$is_agency_logged_in) {
            redirect('login');
        }

        if (!agency_staff_can_access_page('notifications')) {
            if ($this->input->is_ajax_request()) {
                ajax_return([
                    'success' => false,
                    'error' => 'Access denied to notifications section'
                ]);
            } else {
                $this->session->set_flashdata('error', 'Access denied to notifications section');
                redirect('dashboard');
            }
            exit;
        }

        $this->load->model($this->folder . '/' . $this->model);
        $this->setup_listing();
        
        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }

    public function index()
    {
        $this->breadcrumbs = array(
            array(
                'title' => lang($this->pageName . '_heading'),
                'url' => redir($this->pageName, true)
            ),
        );
        $this->view = 'listing';

        $this->load->view($this->folder . '/' . 'view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading' => lang($this->pageName . '_heading'),
            'noRows' => lang($this->pageName . '_no_rows'),
        ));
        $this->load->view($this->folder . '/' . 'view_footer');
    }

    private function setup_listing(): void
    {
        $this->listFields = array(
            'title' => array(
                'label' => lang('label_title'),
                'sort' => true,
            ),
            'message' => array(
                'label' => lang('label_message'),
                'sort' => true,
            ),
            'type' => array(
                'label' => lang('label_type'),
                'sort' => true,
            ),
            'is_read' => array(
                'label' => lang('label_read_status'),
                'sort' => true,
                'function' => function($value, $row) {
                    return $value ? 'Read' : 'Unread';
                }
            ),
            'created_at' => array(
                'label' => lang('label_created'),
                'sort' => true,
            ),
        );

        $this->listActions = array(
            'mark_read' => array(
                'label' => lang('label_mark_read'),
                'url' => redir($this->pageName . '/mark_read/{id}', true),
                'icon' => 'fa-check',
                'class' => 'mark-read-btn',
                'function' => function($str, $row) {
                    return !$row->is_read ? $str : false;
                }
            ),
            'delete' => array(
                'label' => lang('label_delete'),
                'url' => redir($this->pageName . '/delete/{id}', true),
                'icon' => 'fa-trash',
                'class' => 'delete-btn',
            ),
        );
    }

    public function mark_read($id)
    {
        $this->db->where('id', $id);
        $this->db->update('notifications', ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        
        $this->session->set_flashdata('success', 'Notification marked as read');
        redirect($this->pageName);
    }

    public function mark_all_read()
    {
        $user_id = loginID();
        $this->db->where('user_id', $user_id);
        $this->db->where('is_read', 0);
        $this->db->update('notifications', [
            'is_read' => 1, 
            'read_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->session->set_flashdata('success', 'All notifications marked as read');
        redirect($this->pageName);
    }
}