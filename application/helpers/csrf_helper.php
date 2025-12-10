<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
if (!function_exists('csrf_safe_validate')) {
    function csrf_safe_validate($ci_instance, $buffer_time = 30) { // Increased to 30 seconds
        $token_name = $ci_instance->security->get_csrf_token_name();
        $current_token = $ci_instance->security->get_csrf_hash();
        $submitted_token = $ci_instance->input->post($token_name);
        
        if (!$submitted_token) {
            log_message('debug', 'CSRF: No token submitted');
            return false;
        }
        
        // Quick check - if tokens match exactly
        if (hash_equals($current_token, $submitted_token)) {
            log_message('debug', 'CSRF: Token matches exactly');
            return true;
        }
        
        // Get buffer from session
        $session = $ci_instance->session;
        $buffer = $session->userdata('csrf_buffer') ?: [];
        $now = time();
        
        // Clean old buffer entries - FIXED LOGIC
        $cleaned_buffer = [];
        foreach ($buffer as $timestamp => $token) {
            if (($now - $timestamp) <= $buffer_time) {
                $cleaned_buffer[$timestamp] = $token;
            }
        }
        
        // Check against buffer
        foreach ($cleaned_buffer as $token) {
            if (hash_equals($token, $submitted_token)) {
                log_message('debug', 'CSRF: Token found in buffer');
                return true;
            }
        }
        
        // If we get here, token is invalid
        log_message('debug', 'CSRF validation failed');
        log_message('debug', 'Current token: ' . substr($current_token, 0, 10) . '...');
        log_message('debug', 'Submitted token: ' . substr($submitted_token, 0, 10) . '...');
        log_message('debug', 'Buffer tokens: ' . count($cleaned_buffer));
        
        // Add current token to buffer for future requests
        $cleaned_buffer[$now] = $current_token;
        $session->set_userdata('csrf_buffer', $cleaned_buffer);
        
        return false;
    }
}