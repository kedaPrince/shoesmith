<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_agency_staff extends CRUD_Model
{
    protected $table = 'agency_staff';

public function get_all($limit = 0, $offset = 0, $section = '')
{
    $this->db->distinct();
    $this->db->join('agencies', 'agencies.id = agency_staff.agency_id', 'left');
    
    // Explicitly select all needed fields including agency_id
    $this->db->select('
        agency_staff.*,
        agencies.name as agency_name
    ', false);
    
    $this->db->where('agency_staff.removed', 0);
    
    // Apply agency filtering - get agency ID from session
    $ci =& get_instance();
    $agency_id = null;
    
    // Get agency ID from session
    if (isset($ci->session) && $ci->session->has_userdata('login')) {
        $login_data = $ci->session->userdata('login');
        
        if (!empty($login_data['agency']) && !empty($login_data['agency']['id'])) {
            $agency_id = $login_data['agency']['id'];
        }
    }
    
    // Apply agency filter if we have an agency ID
    if (!empty($agency_id)) {
        $this->db->where('agency_staff.agency_id', $agency_id);
        log_message('debug', 'Model_agency_staff - Filtering by agency_id: ' . $agency_id);
    } else {
        log_message('debug', 'Model_agency_staff - No agency filter applied');
    }
    
    // Apply sorting
    if (!empty($this->sorting)) {
        foreach ($this->sorting as $field => $direction) {
            $this->db->order_by($field, $direction);
        }
    }
    
    if ($limit > 0) {
        $this->db->limit($limit, $offset);
    }
    
    $query = $this->db->get($this->table);
    log_message('debug', 'Model_agency_staff get_all query: ' . $this->db->last_query());
    return $query;
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
        $this->db->where_in('slug', ['agency_admin', 'agency_staff']); // Adjust slugs as needed
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
            log_message('debug', 'Model_agency_staff - Filtering agencies to show only ID: ' . $agency_id);
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