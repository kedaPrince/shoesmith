<?php
class Model_administrators extends CRUD_Model
{
    protected $table = 'usr_admins';

    public function selects()
    {
        $this->db->distinct();
        edb_select('id, first_name, last_name, email, enabled, removed', $this->table);
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
        $this->db->select('id, email, first_name AS "name"');
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
        $this->db->select('usr_profile_details.address_line_1');
        $this->db->select('usr_profile_details.address_line_2');
        $this->db->select('usr_profile_details.city');
        $this->db->select('usr_profile_details.contact_number');
        $this->db->select('usr_profile_details.country');
        $this->db->select('usr_profile_details.date_of_birth');
        $this->db->select('usr_profile_details.date_of_employment');
        $this->db->select('usr_profile_details.gender');
        $this->db->select('usr_profile_details.id AS usr_profile_id');
        $this->db->select('usr_profile_details.id_number');
        $this->db->select('usr_profile_details.job_role');
        $this->db->select('usr_profile_details.linkedin_profile_url');
        $this->db->join('usr_profile_details', 'usr_profile_details.id = ' . $this->table . '.usr_profile_details_id', 'left');
        $this->db->join('usr_medical_emergency_details', 'usr_medical_emergency_details.id = usr_profile_details.usr_medical_emergency_details_id', 'left');
        $this->db->where($this->table . '.enabled', 1);
        $this->db->where($this->table . '.id', $userId);
        $this->db->where($this->table . '.removed', 0);
        $this->db->where('usr_medical_emergency_details.enabled', 1);
        $this->db->where('usr_medical_emergency_details.removed', 0);
        $this->db->where('usr_profile_details.enabled', 1);
        $this->db->where('usr_profile_details.removed', 0);

        return $this->db->get($this->table)->row();
    }

    public function get_access_groups(int $userId)
    {
        $this->db->select('mod_access_groups.id');
        $this->db->join('pivot_admin_access_groups', 'pivot_admin_access_groups.access_group_id = mod_access_groups.id', 'inner');
        $this->db->where('pivot_admin_access_groups.admin_id', $userId);
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
        $this->db->where_in('slug', ['super_admin', 'general_admin']);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('title');

        return $this->db->get('usr_types');
    }
}
