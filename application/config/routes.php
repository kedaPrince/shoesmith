<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ========== SPECIFIC ROUTES - PUT THESE AT THE TOP ==========

// Agency Routes
$route['agency'] = 'agency/dashboard';
$route['agency/dashboard'] = 'agency/dashboard/index';
$route['agency/login'] = 'login';
$route['agency/logout'] = 'login/logout/agency';

// ========== UUID ROUTES FOR CANDIDATES ==========

// UUID routes for candidates - FIXED: Use $route array syntax
$route['recruiter/candidates/view/([a-f0-9\-]{36})'] = 'recruiter/candidates/view/$1';
$route['recruiter/candidates/edit/([a-f0-9\-]{36})'] = 'recruiter/candidates/edit/$1';
$route['recruiter/candidates/update/([a-f0-9\-]{36})'] = 'recruiter/candidates/update/$1';
$route['recruiter/candidates/start_candidate_chat/([a-f0-9\-]{36})'] = 'recruiter/candidates/start_candidate_chat/$1';

// Keep backward compatibility with numeric IDs (optional, can remove later)
$route['recruiter/candidates/view/(:num)'] = 'recruiter/candidates/view/$1';
$route['recruiter/candidates/edit/(:num)'] = 'recruiter/candidates/edit/$1';
$route['recruiter/candidates/update/(:num)'] = 'recruiter/candidates/update/$1';
$route['recruiter/candidates/start_candidate_chat/(:num)'] = 'recruiter/candidates/start_candidate_chat/$1';

// ========== ADD THESE UUID CHAT ROUTES ==========

// Agency Chat with UUID support
$route['agency/chat/conversation/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})'] = 'agency/chat/conversation/$1';
$route['recruiter/chat/conversation/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})'] = 'recruiter/chat/conversation/$1';

$route['recruiter/candidates/ajax_submit_required_documents'] = 'recruiter/candidates/ajax_submit_required_documents';
$route['recruiter/candidates/ajax_submit_required_documents/(:num)'] = 'recruiter/candidates/ajax_submit_required_documents/$1';

// Keep existing numeric ID routes for backward compatibility
$route['agency/chat/conversation/(:num)'] = 'agency/chat/conversation/$1';
$route['recruiter/chat/conversation/(:num)'] = 'recruiter/chat/conversation/$1';

$route['agency/notifications/ajax_get_chat_notifications'] = 'agency/notifications/ajax_get_chat_notifications';
$route['agency/candidates/open_candidate_chat/(:num)'] = 'agency/candidates/open_candidate_chat/$1';

// Add this too for any UUID format (more flexible)
$route['agency/chat/conversation/([a-fA-F0-9\-]{36})'] = 'agency/chat/conversation/$1';
$route['recruiter/chat/conversation/([a-fA-F0-9\-]{36})'] = 'recruiter/chat/conversation/$1';

$route['agency/candidates'] = 'agency/candidates';
$route['agency/candidates/(:any)'] = 'agency/candidates/$1';
$route['agency/candidates/(:any)/(:any)'] = 'agency/candidates/$1/$2';

$route['agency_staff/dashboard'] = 'agency_staff/dashboard';
$route['agency_staff/dashboard/(:any)'] = 'agency_staff/dashboard/$1';

$route['staff/candidates'] = 'staff/candidates';
$route['staff/candidates/(:any)'] = 'staff/candidates/$1';
$route['staff/candidates/(:any)/(:any)'] = 'staff/candidates/$1/$2';

$route['agency/dashboard'] = 'agency/dashboard';
$route['agency/jobs_listings'] = 'agency/jobs_listings';
$route['agency/jobs_listings/(:any)'] = 'agency/jobs_listings/$1';

$route['recruiter/login'] = 'login';
$route['recruiter/login/(:any)'] = 'login/$1';
$route['recruiter/logout'] = 'login/logout/recruiter';

// ADD THIS NEW ROUTE FOR ONBOARDING LISTING
$route['agency/candidates/onboarding_listing'] = 'agency/candidates/onboarding_listing';
$route['agency/candidates/onboarding_listing/(:any)'] = 'agency/candidates/onboarding_listing/$1';

// Template sections - THESE MUST BE ABOVE GENERIC ROUTES
$route['admin/template_sections'] = 'admin/template_sections';
$route['admin/template_sections/(:any)'] = 'admin/template_sections/$1';

// Override CRUD AJAX endpoints for templates
$route['admin/templates/ajax_listing'] = 'admin/templates/ajax_results';
$route['admin/templates/get_all'] = 'admin/templates/ajax_results';
$route['admin/templates/ajax_get_all'] = 'admin/templates/ajax_results';
$route['admin/templates/ajax_results'] = 'admin/templates/ajax_results';

// Agency templates  
$route['admin/agency_templates/build'] = 'admin/agency_templates/build';
$route['admin/agency_templates/build/(:num)'] = 'admin/agency_templates/build/$1';
$route['admin/agency_templates/save_custom_template'] = 'admin/agency_templates/save_custom_template';
$route['admin/agency_templates/preview_agency_template'] = 'admin/agency_templates/preview_agency_template';
$route['admin/agency_templates/preview_agency_template/(:num)'] = 'admin/agency_templates/preview_agency_template/$1';
$route['admin/agency_templates/get_available_sections'] = 'admin/agency_templates/get_available_sections';

// Agency Notification Routes
$route['agency/notifications'] = 'agency/notifications';
$route['agency/notifications/(:any)'] = 'agency/notifications/$1';
$route['agency/notifications/(:any)/(:any)'] = 'agency/notifications/$1/$2';

// Recruiter Routes
// HM Decision Notification Routes - TRY DIFFERENT PATTERNS
$route['recruiter/dashboard/get_hm_decision_notifications'] = 'recruiter/dashboard/get_hm_decision_notifications';
$route['recruiter/dashboard/mark_hm_notification_read'] = 'recruiter/dashboard/mark_hm_notification_read';

// Alternative route patterns
$route['recruiter/get_hm_decision_notifications'] = 'recruiter/dashboard/get_hm_decision_notifications';
$route['recruiter/mark_hm_notification_read'] = 'recruiter/dashboard/mark_hm_notification_read';

// Direct routes (bypass folder structure)
$route['get_hm_decision_notifications'] = 'recruiter/dashboard/get_hm_decision_notifications';
$route['mark_hm_notification_read'] = 'recruiter/dashboard/mark_hm_notification_read';

// AJAX routes for notifications
$route['agency/notifications/ajax_get_unread_count'] = 'agency/notifications/ajax_get_unread_count';
$route['agency/notifications/ajax_get_recent_notifications'] = 'agency/notifications/ajax_get_recent_notifications';

// Agency Templates List - FIXED ROUTES
$route['admin/agency-templates-list'] = 'admin/agency_templates_list';
$route['admin/agency-templates-list/(:any)'] = 'admin/agency_templates_list/$1';
$route['admin/agency-templates-list/(:any)/(:any)'] = 'admin/agency_templates_list/$1/$2';

// ========== DEBUG ROUTES ==========
$route['browser/(:any)'] = "browser/$1";
$route['cron/(:any)'] = 'cron/$1';
$route['images/(:num)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'files/image/$2/$3/$4/$5/$6/$1';
$route['images/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'files/image/$1/$2/$3/$4/$5';
$route['files/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'files/file/$1/$2/$3/$4/$5';
$route['download/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'files/file/$1/$2/$3/$4/$5';

// Recruiter Chat Routes
$route['recruiter/chat'] = 'recruiter/chat';
$route['recruiter/chat/(:any)'] = 'recruiter/chat/$1';
$route['recruiter/chat/(:any)/(:any)'] = 'recruiter/chat/$1/$2';
$route['recruiter/chat/(:any)/(:any)/(:any)'] = 'recruiter/chat/$1/$2/$3';

// Ensure the candidates/for_job route works
$route['recruiter/candidates/for_job/(:num)'] = 'recruiter/candidates/for_job/$1';
$route['candidates/for_job/(:num)'] = 'recruiter/candidates/for_job/$1';

// Agency Chat Routes  
$route['agency/chat'] = 'agency/chat';
$route['agency/chat/(:any)'] = 'agency/chat/$1';
$route['agency/chat/(:any)/(:any)'] = 'agency/chat/$1/$2';
$route['agency/chat/(:any)/(:any)/(:any)'] = 'agency/chat/$1/$2/$3';

// AJAX endpoints
$route['recruiter/chat/ajax_send_message'] = 'recruiter/chat/ajax_send_message';
$route['recruiter/chat/ajax_get_messages'] = 'recruiter/chat/ajax_get_messages';
$route['recruiter/chat/ajax_get_unread_count'] = 'recruiter/chat/ajax_get_unread_count';

$route['agency/chat/ajax_send_message'] = 'agency/chat/ajax_send_message';
$route['agency/chat/ajax_get_messages'] = 'agency/chat/ajax_get_messages';
$route['agency/chat/ajax_get_unread_count'] = 'agency/chat/ajax_get_unread_count';
$route['admin/administrators/stop_impersonating'] = 'admin/administrators/stop_impersonating';

$route['recruiter/jobs/view/(:any)'] = 'recruiter/jobs/view/$1';
$route['recruiter/candidates/for_job/(:any)'] = 'recruiter/candidates/for_job/$1';
$route['recruiter/candidates/add/(:any)'] = 'recruiter/candidates/add/$1';

// Update existing routes or add new ones
$route['recruiter/candidates/for_job/(:any)'] = 'recruiter/candidates/for_job/$1';
$route['recruiter/candidates/add/(:any)'] = 'recruiter/candidates/add/$1';
// Debug routes
$route['debug-session'] = function() {
    $ci =& get_instance();
    echo "<pre>";
    echo "Session Login Data:\n";
    print_r($ci->session->userdata('login'));
    echo "Is Logged In: " . $ci->session->userdata('is_logged_in') . "\n";
    echo "</pre>";
};

$route['debug-agency-templates-route'] = function() {
    $ci =& get_instance();
    
    echo "<h1>Agency Templates Route Debug</h1>";
    
    // Test if controller file exists
    $controller_path = APPPATH . 'controllers/admin/Agency_templates_list.php';
    echo "<p>Controller file exists: " . (file_exists($controller_path) ? 'YES' : 'NO') . "</p>";
    echo "<p>Controller path: {$controller_path}</p>";
    
    // Test the specific route
    $test_url = 'admin/agency-templates-list';
    echo "<h2>Testing URL: {$test_url}</h2>";
    
    // Load the router
    $ci->load->library('router');
    
    // Check if route exists
    if (isset($ci->router->routes[$test_url])) {
        echo "<p style='color: green'>✓ Route found: {$test_url} → " . $ci->router->routes[$test_url] . "</p>";
    } else {
        echo "<p style='color: red'>✗ Route NOT found: {$test_url}</p>";
    }
    
    echo "<h2>All admin routes:</h2>";
    echo "<pre>";
    foreach ($ci->router->routes as $pattern => $destination) {
        if (strpos($pattern, 'admin/') === 0) {
            echo "{$pattern} => {$destination}\n";
        }
    }
    echo "</pre>";
};

// ========== DEBUG ROUTES ==========
$route['test-candidates-route'] = function() {
    $ci =& get_instance();
    
    echo "<h1>Testing Candidates Route Specifically</h1>";
    
    // Test the exact route pattern
    $test_url = 'recruiter/candidates/for_job/92';
    echo "<h2>Testing: {$test_url}</h2>";
    
    $ci->load->library('router');
    
    // Check all possible route matches
    echo "<h3>Route Analysis:</h3>";
    foreach ($ci->router->routes as $pattern => $destination) {
        if (strpos($pattern, 'candidates') !== false || strpos($pattern, 'recruiter') !== false) {
            $matches = [];
            if (preg_match('#^'.$pattern.'$#', $test_url, $matches)) {
                echo "<p style='color: green'>✓ MATCHES: {$pattern} => {$destination}</p>";
                echo "<pre>Matches: " . print_r($matches, true) . "</pre>";
            } else {
                echo "<p style='color: gray'>✗ No match: {$pattern}</p>";
            }
        }
    }
    
    // Test if the generic route is catching it
    echo "<h3>Generic Route Test:</h3>";
    $generic_pattern = '(:any)/(:any)/(:any)/(:any)';
    $matches = [];
    if (preg_match('#^'.$generic_pattern.'$#', $test_url, $matches)) {
        echo "<p style='color: orange'>⚠ Matches generic route: {$generic_pattern}</p>";
        echo "<pre>Generic matches: " . print_r($matches, true) . "</pre>";
        echo "<p>Would route to: front/{$matches[1]}/{$matches[2]}/{$matches[3]}/{$matches[4]}</p>";
    }
};

// ========== DEBUG ROUTES ==========
$route['debug-routes'] = function() {
    $ci =& get_instance();
    $ci->load->library('router');
    
    echo "<h1>Route Debug - HM Decision Notifications</h1>";
    
    // Check if our specific route exists
    $test_route = 'recruiter/dashboard/get_hm_decision_notifications';
    echo "<h2>Checking route: {$test_route}</h2>";
    
    if (isset($ci->router->routes[$test_route])) {
        echo "<p style='color: green'>✓ Route FOUND: {$test_route} → " . $ci->router->routes[$test_route] . "</p>";
    } else {
        echo "<p style='color: red'>✗ Route NOT FOUND: {$test_route}</p>";
    }
    
    // Show all recruiter routes
    echo "<h2>All Recruiter Routes:</h2>";
    echo "<pre>";
    foreach ($ci->router->routes as $pattern => $destination) {
        if (strpos($pattern, 'recruiter/') === 0) {
            echo "{$pattern} => {$destination}\n";
        }
    }
    echo "</pre>";
    
    // Test if controller method exists
    echo "<h2>Controller Method Check:</h2>";
    $controller_path = APPPATH . 'controllers/recruiter/Dashboard.php';
    if (file_exists($controller_path)) {
        echo "<p style='color: green'>✓ Controller file exists: {$controller_path}</p>";
        
        // Check if method exists
        require_once($controller_path);
        if (method_exists('Dashboard', 'get_hm_decision_notifications')) {
            echo "<p style='color: green'>✓ Method exists: get_hm_decision_notifications</p>";
        } else {
            echo "<p style='color: red'>✗ Method NOT found: get_hm_decision_notifications</p>";
        }
    } else {
        echo "<p style='color: red'>✗ Controller file NOT found: {$controller_path}</p>";
    }
};

// ========== GENERIC ROUTES - KEEP THESE AT THE BOTTOM ==========

//Standard CMS
build_route($route, '(:any)/ajax_pager_fetch_batch/(:num)/(:any)', '$1/ajax_pager_fetch_batch/$2/$3');
build_route($route, '(:any)/ajax_pager_fetch_batch/(:num)', '$1/ajax_pager_fetch_batch/$2');
build_route($route, '(:any)/ajax_set_sorting/(:any)/(:any)/(:num)', '$1/ajax_set_sorting/$2/$3/$4');
build_route($route, '(:any)/ajax_pager_fetch_batch', '$1/ajax_pager_fetch_batch');
build_route($route, '(:any)/update', '$1/update', 'post');
build_route($route, '(:any)/update/(:any)', '$1/update/$2', 'post');
build_route($route, '(:any)/remove', '$1/remove');
build_route($route, '(:any)/remove/(:any)', '$1/remove/$2');
build_route($route, '(:any)/enable/(:any)', '$1/enable/$2');
build_route($route, '(:any)/disable/(:any)', '$1/disable/$2');
build_route($route, '(:any)/edit/(:any)', '$1/edit/$2');
build_route($route, '(:any)/process/(:any)', '$1/process/$2');
build_route($route, '(:any)/view/(:any)', '$1/view/$2');
build_route($route, '(:any)/create', '$1/create', 'post');
build_route($route, '(:any)/reset_filters', '$1/reset_filters');
build_route($route, '(:any)/dashboard', '$1/dashboard');
build_route($route, '(:any)/add', '$1/add');
build_route($route, '(:any)/ajax_get_results', '$1/ajax_get_results');
build_route($route, '(:any)/ajax_filter_by_letter', '$1/ajax_filter_by_letter');
build_route($route, '(:any)/ajax_(:any)', '$1/ajax_$2', 'post');
build_route($route, '(:any)/(:any)/(:any)', '$1/$2/$3');
build_route($route, '(:any)/(:any)', '$1/$2');
build_route($route, '(:any)', '$1');

$route['login/(:any)/(:any)/(:any)'] = 'login/$1/$2/$3';
$route['login/logout/(:any)'] = 'login/logout/$1';
$route['login/(:any)/(:any)'] = 'login/$1/$2';
$route['login/(:any)'] = 'login/$1';
$route['login'] = 'login';

$route['(:any)/(:any)/(:any)/(:any)'] = 'front/$1/$2/$3/$4';
$route['(:any)/(:any)/(:any)'] = 'front/$1/$2/$3';
$route['(:any)/(:any)'] = 'front/$1/$2';
$route['(:any)'] = 'front/$1';

$route['default_controller'] = 'front/home';
$route['404_override'] = 'errors/error404';
$route['translate_uri_dashes'] = TRUE;


function build_route(&$route, $regex, $path, $verb='') {
    //Login groups or language - FIXED: ADDED AGENCY
    $ra['g'] = '(\badmin\b|\bstaff\b|\bagency\b|\brecruiter\b|\brecruiters\b)/';

    foreach ($ra as $g => $r) {
        $p = $path;

        $p = preg_replace_callback('/(\$\d+)/', 'increment_match_number_by_one', $p);

        //Set folder to be capture group if one of the login groups
        $folder = '$1/';

        if (!empty($verb)) {
            $route[$r.$regex][$verb] = $folder.$p;
        } else {
            $route[$r.$regex] = $folder.$p;
        }
    }
}

function increment_match_number_by_one($matches) {
    return '$'.(str_replace('$', '', $matches[0])+1);
}

function increment_match_number_by_two($matches) {
    return '$'.(str_replace('$', '', $matches[0])+2);
}