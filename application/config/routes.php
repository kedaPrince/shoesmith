<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Agency Routes - ADD THESE
$route['agency'] = 'agency/dashboard';
$route['agency/dashboard'] = 'agency/dashboard/index';
$route['agency/login'] = 'login';
$route['agency/logout'] = 'login/logout/agency';
// Add to your routes.php
$route['agency/candidates'] = 'agency/candidates';
$route['agency/candidates/(:any)'] = 'agency/candidates/$1';
$route['agency/candidates/(:any)/(:any)'] = 'agency/candidates/$1/$2';

$route['staff/candidates'] = 'staff/candidates';
$route['staff/candidates/(:any)'] = 'staff/candidates/$1';
$route['staff/candidates/(:any)/(:any)'] = 'staff/candidates/$1/$2';

// Agency Routes
$route['agency/dashboard'] = 'agency/dashboard';
$route['agency/jobs_listings'] = 'agency/jobs_listings';
$route['agency/jobs_listings/(:any)'] = 'agency/jobs_listings/$1';

// Recruiter Routes - ADD THESE
// Recruiter Login Routes - ADD THESE
$route['recruiter/login'] = 'login';
$route['recruiter/login/(:any)'] = 'login/$1';
$route['recruiter/logout'] = 'login/logout/recruiter';

// Recruiter Dashboard Routes - POINT TO AGENCY CONTROLLERS
$route['recruiter/dashboard'] = 'agency/dashboard';
$route['recruiter/dashboard/(:any)'] = 'agency/dashboard/$1';

// Recruiter Jobs Routes - POINT TO AGENCY CONTROLLERS  
$route['recruiter/jobs_listings'] = 'agency/jobs_listings';
$route['recruiter/jobs_listings/(:any)'] = 'agency/jobs_listings/$1';


$route['browser/(:any)']  = "browser/$1";
$route['cron/(:any)']               			= 'cron/$1';

$route['images/(:num)/(:any)/(:any)/(:any)/(:any)/(:any)']   	= 'files/image/$2/$3/$4/$5/$6/$1';
$route['images/(:any)/(:any)/(:any)/(:any)/(:any)']   			= 'files/image/$1/$2/$3/$4/$5';
$route['files/(:any)/(:any)/(:any)/(:any)/(:any)']   			= 'files/file/$1/$2/$3/$4/$5';
$route['download/(:any)/(:any)/(:any)/(:any)/(:any)']   		= 'files/file/$1/$2/$3/$4/$5';

// Add to routes.php temporarily
$route['debug-session'] = function() {
    $ci =& get_instance();
    echo "<pre>";
    echo "Session Login Data:\n";
    print_r($ci->session->userdata('login'));
    echo "Is Logged In: " . $ci->session->userdata('is_logged_in') . "\n";
    echo "</pre>";
};
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

$route['login/(:any)/(:any)/(:any)']    = 'login/$1/$2/$3';
$route['login/logout/(:any)']           = 'login/logout/$1';
$route['login/(:any)/(:any)']           = 'login/$1/$2';
$route['login/(:any)']                  = 'login/$1';
$route['login']                         = 'login';

$route['(:any)/(:any)/(:any)/(:any)']       = 'front/$1/$2/$3/$4';
$route['(:any)/(:any)/(:any)']              = 'front/$1/$2/$3';
$route['(:any)/(:any)']                     = 'front/$1/$2';
$route['(:any)']                            = 'front/$1';

$route['default_controller'] = 'front/home';
$route['404_override'] = 'errors/error404';
$route['translate_uri_dashes'] = TRUE;

function build_route(&$route, $regex, $path, $verb='') {

    //Login groups or language - FIXED: ADDED AGENCY
    $ra['g'] = '(\badmin\b|\bstaff\b|\bagency\b)/';

    foreach ($ra as $g => $r) {
        $p = $path;

        $p = preg_replace_callback('/(\$\d+)/', 'increment_match_number_by_one', $p);

        //Set folder to be capture group if one of the login groups
        $folder = '$1/';

        if (!empty($verb)) {
            $route[$r.$regex][$verb] = $folder.$p;
        }
        else {
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

// Add this temporary route to test config
$route['test-config'] = function() {
    $ci =& get_instance();
    echo "<pre>";
    echo "Login Groups:\n";
    print_r($ci->config->item('login_groups'));
    echo "\nCurrent URL: " . current_url();
    echo "</pre>";
};

// Debug route - check recruiters
$route['debug-recruiters'] = function() {
    $ci =& get_instance();
    
    echo "<h1>Recruiters Debug</h1>";
    
    // Check if recruiters table has users
    $ci->db->where('removed', 0);
    $ci->db->where('enabled', 1);
    $recruiters = $ci->db->get('recruiters')->result();
    
    echo "<h2>Recruiters in database:</h2>";
    echo "<pre>";
    if (empty($recruiters)) {
        echo "No recruiters found in database!\n";
    } else {
        foreach ($recruiters as $recruiter) {
            echo "ID: " . $recruiter->id . "\n";
            echo "Name: " . $recruiter->first_name . " " . $recruiter->last_name . "\n";
            echo "Email: " . $recruiter->email . "\n";
            echo "Agency ID: " . $recruiter->agency_id . "\n";
            echo "Enabled: " . $recruiter->enabled . "\n";
            echo "Removed: " . $recruiter->removed . "\n";
            echo "---\n";
        }
    }
    echo "</pre>";
    
    // Check login groups config
    echo "<h2>Login Groups Config:</h2>";
    echo "<pre>";
    print_r($ci->config->item('login_groups'));
    echo "</pre>";
};