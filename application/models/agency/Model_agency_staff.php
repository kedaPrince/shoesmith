<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_agency_staff extends CRUD_Model
{
    protected $table = 'agency_staff';

     /**
     * 🔒 SECURITY FIX: Get user's agency ID from session
     * This should be called in EVERY query method
     */
    private function get_current_agency_id()
    {
        $ci =& get_instance();
        $login = $ci->session->userdata('login');
        
        if (!empty($login['agency'])) {
            $agency_user = $login['agency'];
            
            // Check all possible agency ID locations
            if (!empty($agency_user['agency_id'])) {
                return $agency_user['agency_id'];
            } elseif (!empty($agency_user['id'])) {
                return $agency_user['id'];
            } elseif (!empty($agency_user['agency']['id'])) {
                return $agency_user['agency']['id'];
            }
        }
        
        return null;
    }

    /**
     * 🔒 SECURITY FIX: Get staff by ID with STRICT agency check
     * Must match parent class signature: get_by_id($id, $table = false)
     */
    public function get_by_id($id, $table = false)
    {
        $table = $table ? $table : $this->table;
        
        $this->db->where('id', $id);
        $this->db->where('removed', 0);
        
        // 🔒 CRITICAL: Always apply agency filter for non-admin users
        $agency_id = $this->get_current_agency_id();
        
        if (!empty($agency_id)) {
            $this->db->where('agency_id', $agency_id);
        } else {
            // If no agency ID and not admin, return empty
            $user_type = getLoggedInUserTypeMenu();
            if ($user_type !== 'admin') {
                return null;
            }
        }
        
        return $this->db->get($table)->row();
    }

    /**
     * Main selects for CRUD system
     */
    public function main_selects()
    {
        $this->db->from('agency_staff');
        $this->db->join('agencies', 'agencies.id = agency_staff.agency_id', 'left');
        
        $this->db->select('
            agency_staff.*,
            agencies.name as agency_name
        ', false);
    }

    /**
     * Main where conditions
     */
    public function main_wheres()
    {
        $this->db->where('agency_staff.removed', 0);
        
        // Apply agency filtering
        $agency_id = $this->get_current_agency_id();
        
        if (!empty($agency_id)) {
            $this->db->where('agency_staff.agency_id', $agency_id);
        } else {
            // If no agency ID and not admin, show nothing
            $user_type = getLoggedInUserTypeMenu();
            if ($user_type !== 'admin') {
                $this->db->where('agency_staff.id', 0);
            }
        }
    }

    /**
     * Apply filters from CRUD system
     */
    public function main_filters()
    {
        $pageName = 'agency_staff';
        $filters = get_ecms_filters($pageName);
        
        if (!empty($filters)) {
            foreach ($filters as $filterName => $options) {
                if (!isset($options['value']) || $options['value'] === '') {
                    continue; // Skip empty filters
                }
                
                $value = $options['value'];
                
                switch ($filterName) {
                    case 'general':
                        // Apply search filter
                        if (!empty($value)) {
                            $this->db->group_start();
                            $this->db->or_like('CONCAT(agency_staff.first_name," ",agency_staff.last_name)', $value);
                            $this->db->or_like('agency_staff.email', $value);
                            $this->db->or_like('agency_staff.job_role', $value);
                            $this->db->group_end();
                        }
                        break;
                        
                    case 'agency':
                        // Apply agency filter
                        $this->db->where('agency_staff.agency_id', $value);
                        break;
                }
            }
        }
        
        $this->filters();
    }

    /**
     * Additional filters (can be extended)
     */
    public function filters()
    {
        // Custom filters can be added here if needed
    }

    /**
     * Main joins (if any)
     */
    public function main_joins()
    {
        // Joins are already in main_selects()
    }

    /**
     * Override get_count() to work with filters
     */
    public function get_count()
    {
        // Start fresh
        $this->db->flush_cache();
        
        // Build the query
        $this->main_selects();
        $this->main_wheres();
        $this->main_filters();
        
        // Count rows
        $this->db->select('COUNT(agency_staff.id) as count', false);
        
        $query = $this->db->get();
        
        if ($query && $query->num_rows() > 0) {
            return (int) $query->row()->count;
        }
        
        return 0;
    }

    /**
     * Override get_all() to work with CRUD system
     */
    public function get_all($limit = 0, $offset = 0, $section = '')
    {
        $this->db->flush_cache();
        
        // Build the query
        $this->main_selects();
        $this->main_wheres();
        $this->main_filters();
        
        // Apply sorting
        if (!empty($this->sorting)) {
            foreach ($this->sorting as $field => $direction) {
                $this->db->order_by($field, $direction);
            }
        }
        
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get();
    }


    /**
 * 🔒 SECURITY FIX: Universal access check for any staff ID - DEBUG VERSION
 */
public function can_access_staff($staff_id)
{
    log_message('debug', '=== MODEL CAN_ACCESS_STAFF ===');
    log_message('debug', 'Checking access to staff ID: ' . $staff_id);
    
    $agency_id = $this->get_current_agency_id();
    log_message('debug', 'Current Agency ID from session: ' . ($agency_id ?: 'NULL'));
    
    if (empty($agency_id)) {
        // Check if user is admin
        $ci =& get_instance();
        $ci->load->helper('profile_helper');
        $user_type = getLoggedInUserTypeMenu();
        log_message('debug', 'User type: ' . $user_type);
        return ($user_type === 'admin');
    }
    
    // Check if staff belongs to user's agency
    $this->db->select('id, agency_id, first_name, last_name');
    $this->db->from($this->table);
    $this->db->where('id', $staff_id);
    $this->db->where('agency_id', $agency_id);
    $this->db->where('removed', 0);
    
    $result = $this->db->get()->row();
    
    if ($result) {
        log_message('debug', 'Staff found: ' . $result->first_name . ' ' . $result->last_name);
        log_message('debug', 'Staff Agency ID: ' . $result->agency_id);
        log_message('debug', 'Access: GRANTED');
    } else {
        log_message('debug', 'Staff not found or wrong agency');
        log_message('debug', 'Query: ' . $this->db->last_query());
        log_message('debug', 'Access: DENIED');
    }
    
    return $result !== null;
}

    // Remove the selects() method entirely or keep it empty
    public function selects()
    {
        // Leave this empty to prevent the CRUD system from auto-adding fields
    }

    /**
     * Get user's agency information
     */
    public function get_user_agency($user_id)
    {
        $this->db->select('agency_staff.*, agencies.name as agency_name');
        $this->db->from('agency_staff');
        $this->db->join('agencies', 'agencies.id = agency_staff.agency_id', 'left');
        $this->db->where('agency_staff.id', $user_id);
        $this->db->where('agency_staff.enabled', 1);
        $this->db->where('agency_staff.removed', 0);
        $this->db->where('agencies.enabled', 1);
        $this->db->where('agencies.removed', 0);
        
        return $this->db->get()->row();
    }

    public function get_additional_profile_details(int $userId)
    {
        $this->db->select('usr_medical_emergency_details.allergies');
        $this->db->select('usr_medical_emergency_details.doctor_phone_number');
        $this->db->select('usr_medical_emergency_details.family_doctor');
        $this->db->select('usr_medical_emergency_details.id AS usr_medical_emergency_details_id');
        $this->db->select('usr_medical_emergency_details.mandatory_medication_taken');
        $this->db->select('usr_medical_emergency_details.medical_aid_member_number');
        $this->db->select('usr_medical_emergency_details.medical_aid_name');
        $this->db->select('usr_medical_emergency_details.medical_aid_plan');
        $this->db->select('usr_medical_emergency_details.medical_history');
        $this->db->select('usr_medical_emergency_details.medical_problems');
        $this->db->select('usr_medical_emergency_details.next_of_kin_first_name');
        $this->db->select('usr_medical_emergency_details.next_of_kin_last_name');
        $this->db->select('usr_medical_emergency_details.next_of_kin_phone_number');
        $this->db->select('usr_medical_emergency_details.next_of_kin_relation');
        
        // Select profile details from agency_staff table (since we added the columns)
        $this->db->select('agency_staff.address_line_1');
        $this->db->select('agency_staff.address_line_2');
        $this->db->select('agency_staff.city');
        $this->db->select('agency_staff.contact_number');
        $this->db->select('agency_staff.country');
        $this->db->select('agency_staff.date_of_birth');
        $this->db->select('agency_staff.date_of_employment');
        $this->db->select('agency_staff.gender');
        $this->db->select('agency_staff.id_number');
        $this->db->select('agency_staff.job_role');
        $this->db->select('agency_staff.linkedin_profile_url');
        
        // Join with medical emergency details through usr_profile_details_id
        $this->db->join('usr_medical_emergency_details', 'usr_medical_emergency_details.id = agency_staff.usr_profile_details_id', 'left');
        
        $this->db->where('agency_staff.enabled', 1);
        $this->db->where('agency_staff.id', $userId);
        $this->db->where('agency_staff.removed', 0);
        $this->db->where('usr_medical_emergency_details.enabled', 1);
        $this->db->where('usr_medical_emergency_details.removed', 0);

        return $this->db->get($this->table)->row();
    }

    /**
     * Alternative method using the separate profile details table
     * Use this if you prefer to use the normalized table structure
     */
    public function get_additional_profile_details_normalized(int $userId)
    {
        $this->db->select('usr_medical_emergency_details.allergies');
        $this->db->select('usr_medical_emergency_details.doctor_phone_number');
        $this->db->select('usr_medical_emergency_details.family_doctor');
        $this->db->select('usr_medical_emergency_details.id AS usr_medical_emergency_details_id');
        $this->db->select('usr_medical_emergency_details.mandatory_medication_taken');
        $this->db->select('usr_medical_emergency_details.medical_aid_member_number');
        $this->db->select('usr_medical_emergency_details.medical_aid_name');
        $this->db->select('usr_medical_emergency_details.medical_aid_plan');
        $this->db->select('usr_medical_emergency_details.medical_history');
        $this->db->select('usr_medical_emergency_details.medical_problems');
        $this->db->select('usr_medical_emergency_details.next_of_kin_first_name');
        $this->db->select('usr_medical_emergency_details.next_of_kin_last_name');
        $this->db->select('usr_medical_emergency_details.next_of_kin_phone_number');
        $this->db->select('usr_medical_emergency_details.next_of_kin_relation');
        $this->db->select('agency_staff_profile_details.address_line_1');
        $this->db->select('agency_staff_profile_details.address_line_2');
        $this->db->select('agency_staff_profile_details.city');
        $this->db->select('agency_staff_profile_details.contact_number');
        $this->db->select('agency_staff_profile_details.country');
        $this->db->select('agency_staff_profile_details.date_of_birth');
        $this->db->select('agency_staff_profile_details.date_of_employment');
        $this->db->select('agency_staff_profile_details.gender');
        $this->db->select('agency_staff_profile_details.id AS agency_staff_profile_id');
        $this->db->select('agency_staff_profile_details.id_number');
        $this->db->select('agency_staff_profile_details.job_role');
        $this->db->select('agency_staff_profile_details.linkedin_profile_url');
        
        $this->db->join('agency_staff_profile_details', 'agency_staff_profile_details.agency_staff_id = agency_staff.id', 'left');
        $this->db->join('usr_medical_emergency_details', 'usr_medical_emergency_details.id = agency_staff_profile_details.usr_medical_emergency_details_id', 'left');
        
        $this->db->where('agency_staff.enabled', 1);
        $this->db->where('agency_staff.id', $userId);
        $this->db->where('agency_staff.removed', 0);
        $this->db->where('agency_staff_profile_details.enabled', 1);
        $this->db->where('agency_staff_profile_details.removed', 0);
        $this->db->where('usr_medical_emergency_details.enabled', 1);
        $this->db->where('usr_medical_emergency_details.removed', 0);

        return $this->db->get($this->table)->row();
    }

    public function get_access_groups(int $userId)
    {
        $this->db->select('mod_access_groups.id');
        $this->db->join('pivot_agency_staff_access_groups', 'pivot_agency_staff_access_groups.access_group_id = mod_access_groups.id', 'inner');
        $this->db->where('pivot_agency_staff_access_groups.agency_staff_id', $userId);
        $this->db->where('mod_access_groups.enabled', 1);
        $this->db->where('mod_access_groups.removed', 0);
        $this->db->order_by('mod_access_groups.name');
        
        $result = $this->db->get('mod_access_groups')->result_array();
        $idsList = [];
        foreach ($result as $row) {
            $idsList[] = (int)$row['id'];
        }

        return $idsList;
    }

    /**
     * Add joins for listing queries
     */
    public function add_listing_joins()
    {
        $this->db->join('agencies', 'agencies.id = agency_staff.agency_id', 'left');
        $this->db->select('agencies.name as agency_name');
    }

    public function get_access_groups_all()
    {
        $this->db->select('id, name');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name');

        return $this->db->get('mod_access_groups');
    }

    public function get_user_types_all()
    {
        $this->db->select('id, title as name');
        // Include all agency-related user types
        $this->db->where_in('slug', ['agency_admin', 'agency_manager', 'agency_agent', 'agency_support']);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('title');

        return $this->db->get('usr_types');
    }

    /**
     * Get agency options for dropdowns
     */
    public function get_agency_options($agency_id = null) {
        $this->db->select('id, name');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        
        // If agency_id is provided, filter to show only that agency
        if ($agency_id) {
            $this->db->where('id', $agency_id);
        }
        
        $this->db->order_by('name');
        return $this->db->get('agencies');
    }

    /**
     * Is Unique Email
     *
     * Checks if given email already exists in the database.
     * If the id is passed, then it will ignore that entry.
     *
     * @param string $email
     * @param string $id (optional)
     *
     * @return bool
     */
    public function is_unique_email($email, $id = "")
    {
        //Build a query to check across all defined login groups
        $loginGroups = $this->config->item('login_groups');

        $sqlArray = array();
        foreach ($loginGroups as $group) {
            $sql = 'SELECT id FROM ' . $group['table'] . ' WHERE removed = 0 AND email = "' . $this->db->escape_str($email) . '"';
            if (!empty($id)) {
                $sql .= ' AND id != "' . $id . '" ';
            }

            $sqlArray[] = $sql;
        }

        $query = $this->db->query(implode(' UNION ALL ', $sqlArray));
        if ($query->num_rows() == 0) {
            return true;
        }
        return false;
    }

    // To be compliant with GDPR information must be completely removed and not soft deleted
    public function remove($whereValue, $whereField = 'id', $table = false)
    {
        $table = $table ? $table : $this->table;

        if (!$this->db->delete($table, array($whereField => $whereValue))) {
            Anomalies::log('Failed to delete from CRUD', $this->db->last_query());
            return false;
        }

        return true;
    }

    /**
     * Get New User Email Data
     *
     * Gets the data needed for the new user email.
     *
     * @param int $id
     *
     * @return array
     */
    public function get_reset_email_data($id)
    {
        $this->db->select('id, email, name, first_name, last_name');
        $this->db->where('removed', 0);
        $this->db->where('id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->table);

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $row;
        }
        return false;
    }

    /**
     * Get staff by agency
     */
    public function get_staff_by_agency($agency_id)
    {
        $this->db->select('id, first_name, last_name, name, email, job_role');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('first_name, last_name');
        
        return $this->db->get($this->table)->result();
    }
}