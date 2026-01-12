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

        // Check if user is logged in as agency staff
        $login_data = $this->session->userdata('login');
        $is_agency_logged_in = !empty($login_data['agency_staff']);
        
        if (!$is_agency_logged_in) {
            // Not logged in - redirect to login
            redirect('login');
        }

        $this->load->model($this->folder.'/'.$this->model);
        $this->zone = array(
            'title' => lang('label_dashboard'),
            'url'   => url($this->pageName)
        );
    }

public function index() {
    $this->setup_breadcrumbs();
    
    // DIRECT CHECK
    $login = $this->session->userdata('login');
    $user_id = $login['agency_staff']['id'] ?? 0;
    
    // Get groups directly
    $this->db->select('mag.id, mag.name');
    $this->db->from('mod_access_groups mag');
    $this->db->join('pivot_agency_staff_access_groups pag', 'mag.id = pag.access_group_id');
    $this->db->where('pag.agency_staff_id', $user_id);
    $user_groups = $this->db->get()->result_array();
    $group_ids = array_column($user_groups, 'id');
    
    // PROPERLY filter sitemap based on SPECIFIC groups
    $filtered_sitemap = [];
    foreach ($this->siteMap as $item) {
        $page = $item->page ?? '';
        $can_access = false;
        
        // Check each group the user has
        if (in_array(7, $group_ids)) { // Templates Management
            if (in_array($page, ['dashboard', 'templates', 'template_sections', 'agency_templates'])) {
                $can_access = true;
            }
        }
        
        if (in_array(9, $group_ids)) { // Notifications Management
            if (in_array($page, ['dashboard', 'notifications'])) {
                $can_access = true;
            }
        }
        
        // Special case: if user has NO groups, still show dashboard
        if (empty($group_ids) && $page === 'dashboard') {
            $can_access = true;
        }
        
        if ($can_access) {
            // Also need to filter sub-items if they exist
            if (isset($item->items) && is_array($item->items)) {
                $filtered_subitems = [];
                foreach ($item->items as $subitem) {
                    $subpage = $subitem->page ?? '';
                    $sub_can_access = false;
                    
                    if (in_array(7, $group_ids)) {
                        if (in_array($subpage, ['templates', 'template_sections', 'agency_templates'])) {
                            $sub_can_access = true;
                        }
                    }
                    
                    if (in_array(9, $group_ids)) {
                        if ($subpage === 'notifications') {
                            $sub_can_access = true;
                        }
                    }
                    
                    if ($sub_can_access) {
                        $filtered_subitems[] = $subitem;
                    }
                }
                $item->items = $filtered_subitems;
            }
            
            $filtered_sitemap[] = $item;
        }
    }
    
    $this->siteMap = $filtered_sitemap;
    
    load_custom_page($this->folder.'/'.$this->pageName.'/view_dashboard');
}
    





}