<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_recruiters extends CRUD_Model
{
    protected $table = 'recruiters';

    public function selects()
    {
        // Select base recruiter fields
        edb_select('id, first_name, last_name, email, phone, agency_id, usr_type_id, enabled, removed', $this->table);
        
        // Add joins and aliases
        $this->db->join('agencies', 'agencies.id = recruiters.agency_id', 'left');
        $this->db->join('usr_types', 'usr_types.id = recruiters.usr_type_id', 'left');
        $this->db->select('agencies.name AS agency_name');
        $this->db->select('usr_types.title AS user_type');
        
        // Add agency filtering for agency users
        $loginData = loginData();
        if (isset($loginData['id']) && $loginData['group'] == 'agency') {
            $this->db->where('recruiters.agency_id', $loginData['id']);
        }
    }

    // Keep your existing helper methods
    public function get_agency_options()
    {
        $this->db->select('id, name');
        $this->db->from('agencies');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        
        // For agency users, only show their own agency
        $loginData = loginData();
        if (isset($loginData['id']) && $loginData['group'] == 'agency') {
            $this->db->where('id', $loginData['id']);
        }
        
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_usr_type_options()
    {
        $this->db->select('id, title');
        $this->db->from('usr_types');
        $this->db->where_in('id', [5, 6, 7, 8]);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('title', 'ASC');
        $query = $this->db->get();
        $options = ['' => '-- Select User Type --'];
        foreach ($query->result() as $row) {
            $options[$row->id] = $row->title;
        }
        return $options;
    }

    public function get_access_groups_all()
    {
        $this->db->select('id, name');
        $this->db->from('mod_access_groups');
        $this->db->where('removed', 0);
        $this->db->where('enabled', 1);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_access_groups($recruiter_id)
    {
        if (!is_numeric($recruiter_id) || $recruiter_id <= 0) {
            return [];
        }
        $this->db->select('access_group_id');
        $this->db->from('pivot_recruiter_access_groups');
        $this->db->where('recruiter_id', (int)$recruiter_id);
        $query = $this->db->get();
        return array_column($query->result_array(), 'access_group_id');
    }

    public function is_unique_email($email, $id = "")
    {
        $this->db->from($this->table);
        $this->db->where('email', $email);
        $this->db->where('removed', 0);
        
        // Add agency filtering for agency users
        $loginData = loginData();
        if (isset($loginData['id']) && $loginData['group'] == 'agency') {
            $this->db->where('agency_id', $loginData['id']);
        }
        
        if (!empty($id)) {
            $this->db->where('id !=', $id);
        }
        return $this->db->count_all_results() == 0;
    }
}