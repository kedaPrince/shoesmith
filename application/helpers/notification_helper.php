<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notification Helper
 * 
 * Helper functions for notification system
 */

if (!function_exists('time_elapsed_string')) {
    /**
     * Convert datetime to human-readable time difference
     * 
     * @param string $datetime The datetime string
     * @param bool $full Whether to show full time difference
     * @return string Human-readable time difference
     */
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}

if (!function_exists('get_notification_link')) {
    /**
     * Generate appropriate link for notification based on type
     * 
     * @param object $notification The notification object
     * @return string URL for the notification
     */
    function get_notification_link($notification) {
        switch ($notification->type) {
            case 'job_added':
                return site_url('recruiter/jobs/edit/' . $notification->related_entity_id);
            case 'candidate_applied':
                return site_url('recruiter/candidates/edit/' . $notification->related_entity_id);
            default:
                return site_url('recruiter/notifications');
        }
    }
}

if (!function_exists('get_notification_icon')) {
    /**
     * Get appropriate icon for notification type
     * 
     * @param string $type Notification type
     * @return string FontAwesome icon class
     */
    function get_notification_icon($type) {
        switch ($type) {
            case 'job_added':
                return 'fa-briefcase';
            case 'candidate_applied':
                return 'fa-user-plus';
            case 'status_changed':
                return 'fa-exchange';
            default:
                return 'fa-bell';
        }
    }
}

if (!function_exists('get_notification_badge_class')) {
    /**
     * Get appropriate badge color class for notification type
     * 
     * @param string $type Notification type
     * @return string Bootstrap badge class
     */
    function get_notification_badge_class($type) {
        switch ($type) {
            case 'job_added':
                return 'badge-primary';
            case 'candidate_applied':
                return 'badge-success';
            case 'status_changed':
                return 'badge-warning';
            default:
                return 'badge-info';
        }
    }
}