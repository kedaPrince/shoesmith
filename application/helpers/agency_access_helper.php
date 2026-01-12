<?php
defined('BASEPATH') || exit('No direct script access allowed');

if (!function_exists('get_agency_staff_access_groups')) {
    /**
     * Get current agency staff member's access groups
     */
    function get_agency_staff_access_groups()
    {
        $ci =& get_instance();
        $ci->load->helper('profile_helper');
        
        $accessGroups = getLoggedInAccessGroups();
        return array_keys($accessGroups); // Return just the IDs
    }
}

if (!function_exists('agency_staff_can_access_page')) {
    /**
     * Check if agency staff can access a specific page
     */
    function agency_staff_can_access_page($page_name)
    {
        // Get user's access groups
        $user_groups = get_agency_staff_access_groups();
        
        // If user has full access (group 1), allow everything
        if (in_array(1, $user_groups)) {
            return true;
        }
        
        // Get page requirements
        $ci =& get_instance();
        $ci->load->helper('access_mappings_helper');
        $page_requirements = get_page_access_requirements();
        
        // If page not in requirements, allow access (for safety)
        if (!isset($page_requirements[$page_name])) {
            return true;
        }
        
        // Check if user has any required group
        $required_groups = $page_requirements[$page_name];
        return !empty(array_intersect($required_groups, $user_groups));
    }
}

if (!function_exists('filter_agency_staff_sitemap')) {
    /**
     * Filter sitemap for agency staff based on access groups
     */
    function filter_agency_staff_sitemap($sitemap)
    {
        $filtered = [];
        
        foreach ($sitemap as $item) {
            $item_copy = clone $item;
            
            // Check if user can access this main item
            if (agency_staff_can_access_page($item_copy->page ?? '')) {
                
                // Filter sub-items
                if (isset($item_copy->items) && is_array($item_copy->items)) {
                    $filtered_subitems = [];
                    foreach ($item_copy->items as $subitem) {
                        if (agency_staff_can_access_page($subitem->page ?? '')) {
                            $filtered_subitems[] = $subitem;
                        }
                    }
                    $item_copy->items = $filtered_subitems;
                }
                
                $filtered[] = $item_copy;
            }
        }
        
        return $filtered;
    }
}

if (!function_exists('get_agency_staff_access_debug_info')) {
    /**
     * Debug function for testing access
     */
    function get_agency_staff_access_debug_info()
    {
        $ci =& get_instance();
        $ci->load->helper('profile_helper');
        
        $accessGroups = getLoggedInAccessGroups();
        $group_ids = array_keys($accessGroups);
        $group_names = array_values($accessGroups);
        
        return [
            'group_ids' => $group_ids,
            'group_names' => $group_names,
            'has_full_access' => in_array(1, $group_ids),
            'count' => count($group_ids)
        ];
    }
}