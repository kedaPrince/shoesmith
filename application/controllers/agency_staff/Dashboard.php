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
    
    // Use the working helpers
    $this->load->helper(['profile_helper', 'agency_access_helper']);
    
    // Apply filter using the helper
    if (isset($this->siteMap) && function_exists('filter_agency_staff_sitemap')) {
        $this->siteMap = filter_agency_staff_sitemap($this->siteMap);
    }
    
    load_custom_page($this->folder.'/'.$this->pageName.'/view_dashboard');
}
    
public function simple_debug_filter()
{
    echo "<h2>DEBUG FILTER LOGIC</h2>";
    
    $this->load->helper(['profile_helper', 'agency_access_helper', 'access_mappings_helper']);
    
    // Get user's groups
    $group_ids = get_agency_staff_access_groups();
    echo "<p>User Group IDs: " . implode(', ', $group_ids) . "</p>";
    
    // Test each page
    $pages = ['dashboard', 'templates', 'notifications', 'candidates', 'chat', 'jobs_listings'];
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Page</th><th>Required Groups</th><th>User Has?</th></tr>";
    
    foreach ($pages as $page) {
        $requirements = get_page_access_requirements()[$page] ?? [];
        $has_access = agency_staff_can_access_page($page);
        
        echo "<tr>";
        echo "<td>$page</td>";
        echo "<td>" . implode(', ', $requirements) . "</td>";
        echo "<td>" . ($has_access ? '✅ YES' : '❌ NO') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Show full sitemap
    echo "<h3>Full Sitemap:</h3>";
    echo "<pre>" . print_r($this->siteMap, true) . "</pre>";
}




}