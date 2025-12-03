<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('sanitize_message')) {
    function sanitize_message($input) {
        if (is_array($input)) {
            return array_map('sanitize_message', $input);
        }
        
        // Remove NULL bytes
        $input = str_replace(chr(0), '', $input);
        
        // Strip tags but preserve basic formatting
        $allowed_tags = '<br><strong><em><u><code><pre><p><ul><ol><li>';
        $input = strip_tags($input, $allowed_tags);
        
        // Convert special characters (but allow the allowed tags)
        $input = htmlspecialchars_decode($input, ENT_QUOTES);
        $input = htmlentities($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove script patterns (case insensitive)
        $patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/javascript\s*:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/onmouseover\s*=/i',
            '/onmouseout\s*=/i',
            '/vbscript\s*:/i',
            '/expression\s*\(/i',
        ];
        
        $input = preg_replace($patterns, '', $input);
        
        // Remove SQL injection patterns
        $sql_patterns = [
            '/(\s|^)(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|TRUNCATE|EXEC|UNION)(\s|$)/i',
            '/\-\-.*/',
            '/#.*/',
            '/\/\*.*\*\//',
        ];
        
        $input = preg_replace($sql_patterns, '', $input);
        
        return trim($input);
    }
}

if (!function_exists('validate_uuid_v4')) {
    function validate_uuid_v4($uuid) {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }
}

if (!function_exists('sanitize_filename')) {
    function sanitize_filename($filename) {
        $filename = preg_replace('/[^a-zA-Z0-9\.\-\_]/', '', $filename);
        $filename = str_replace(['..', './', '/'], '', $filename);
        return basename($filename);
    }
}

if (!function_exists('generate_secure_token')) {
    function generate_secure_token($length = 32) {
        return bin2hex(random_bytes($length));
    }
}

if (!function_exists('validate_recaptcha')) {
    function validate_recaptcha($response, $secret_key) {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secret_key,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        return json_decode($result)->success;
    }
}