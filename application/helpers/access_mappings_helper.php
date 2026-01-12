<?php
defined('BASEPATH') || exit('No direct script access allowed');

if (!function_exists('get_user_type_access_group_defaults')) {
    /**
     * Get default access groups for each user type
     * Based on usr_types table:
     * 5 = Agency Admin
     * 6 = Agency Manager  
     * 7 = Agency Agent
     * 8 = Agency Support
     */
    function get_user_type_access_group_defaults()
    {
        return [
            5 => [1],           // Agency Admin → Full Access (group 1)
            6 => [1, 2],        // Agency Manager → Full + Staff Management
            7 => [1, 3, 10],    // Agency Agent → Full + Candidate + Onboarding
            8 => [1, 4],        // Agency Support → Full + Basic Access
        ];
    }
}

if (!function_exists('get_page_access_requirements')) {
    /**
     * Define which access groups can access which pages
     * Based on mod_access_groups table:
     * 1 = Agency Admin - Full Access
     * 2 = Agency Manager - Staff Management  
     * 3 = Agency Agent - Candidate Management
     * 4 = Agency Support - Basic Access
     * 5 = Recruiter Access
     * 6 = Jobs Management
     * 7 = Templates Management
     * 8 = Chat Access
     * 9 = Notifications Management
     * 10 = Onboarding Management
     * 11 = Reports & Analytics
     */
    function get_page_access_requirements()
    {
        return [
            // Dashboard - everyone with any access group
            'dashboard' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
            
            // Agency Staff Management
            'agency_staff' => [1, 2], // Admin or Manager
            
            // Recruiters
            'recruiters' => [1, 5], // Admin or Recruiter Access
            
            // Jobs
            'jobs_listings' => [1, 6], // Admin or Jobs Management
            
            // Candidates
            'candidates' => [1, 3, 5, 10], // Admin, Agent, Recruiter, Onboarding
            'candidates_resume_listings' => [1, 3, 5],
            'candidates_onboarding' => [1, 3, 10],
            
            // Notifications
            'notifications' => [1, 9], // Admin or Notifications
            
            // Chat
            'chat' => [1, 8], // Admin or Chat
            
            // Templates
            'templates' => [1, 7], // Admin or Templates
            'template_sections' => [1, 7],
            'agency_templates' => [1, 7],
        ];
    }
}