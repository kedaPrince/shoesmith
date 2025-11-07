<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
if (!function_exists('character_limiter')) {
    function character_limiter($str, $n = 500, $end_char = '...') {
        if (strlen($str) < $n) {
            return $str;
        }
        return substr($str, 0, $n) . $end_char;
    }
}

if (!function_exists('time_ago')) {
    function time_ago($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return $diff . ' seconds ago';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . ' days ago';
        } else {
            return date('M j, Y', $time);
        }
    }
}