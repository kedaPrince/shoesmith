<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Block_Cross_Role {
    
    public function check_access() {
        $CI =& get_instance();
        
        // Skip for CLI requests
        if (is_cli()) {
            return;
        }
        
        // Get current URI
        $uri = $CI->uri->uri_string();
        $segment1 = $CI->uri->segment(1);
        $segment2 = $CI->uri->segment(2);
        
        // Skip check for these public/essential pages
        $skip_pages = [
            'login', 'logout', 'forgot_password', 'reset_password',
            'ajax_', 'api/', 'webhook/', 'callback/'
        ];
        
        foreach ($skip_pages as $page) {
            if (strpos($uri, $page) === 0 || 
                strpos($uri, '/' . $page) !== false ||
                $segment2 === $page) {
                return;
            }
        }
        
        // Get user session
        $login_data = $CI->session->userdata('login');
        
        if (empty($login_data)) {
            return; // Not logged in
        }
        
        // Determine user type (agency, recruiter, etc.)
        $user_type = null;
        $types = array_keys($login_data);
        if (!empty($types[0])) {
            $user_type = $types[0];
        }
        
        // ============================================
        // BLOCK AGENCY FROM RECRUITER ROUTES
        // ============================================
        if ($user_type === 'agency') {
            // Block access to recruiter controllers
            if ($segment1 === 'recruiter') {
                $this->block_access('Agency users cannot access recruiter pages.');
            }
            
            // Also check directory-based routing
            $directory = $CI->router->fetch_directory();
            if (strpos($directory, 'recruiter') !== false) {
                $this->block_access('Agency users cannot access recruiter resources.');
            }
        }
        
        // ============================================
        // BLOCK RECRUITER FROM AGENCY ROUTES  
        // ============================================
        if ($user_type === 'recruiter') {
            // Block access to agency controllers
            if ($segment1 === 'agency') {
                $this->block_access('Recruiter users cannot access agency pages.');
            }
            
            // Also check directory-based routing
            $directory = $CI->router->fetch_directory();
            if (strpos($directory, 'agency') !== false) {
                $this->block_access('Recruiter users cannot access agency resources.');
            }
        }
    }
    
    private function block_access($message) {
        $CI =& get_instance();
        
        if ($CI->input->is_ajax_request()) {
            $CI->output->set_status_header(403);
            $CI->output->set_content_type('application/json');
            $CI->output->set_output(json_encode([
                'success' => false,
                'message' => $message,
                'csrf_token' => $CI->security->get_csrf_hash()
            ]));
            $CI->output->_display();
            exit;
        } else {
            show_error($message, 403);
        }
    }
}