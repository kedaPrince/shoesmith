<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_agencies extends CRUD_Model
{
    protected $table = 'agencies';

    /**
     * Get all active agencies
     */
    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
    {
        $this->db->where('removed', 0);
        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

    /**
     * Get agency options for dropdowns
     */
    public function get_agency_options()
    {
        $this->db->select('id, name');
        $this->db->from($this->table);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();

        $options = ['' => '-- Select Agency --'];
        foreach ($query->result() as $row) {
            $options[$row->id] = $row->name;
        }
        return $options;
    }

    /**
     * Check if agency email is unique
     */
    public function is_unique_email($email, $id = null)
    {
        $this->db->where('email', $email);
        $this->db->where('removed', 0);
        
        if ($id) {
            $this->db->where('id !=', $id);
        }
        
        return $this->db->get($this->table)->num_rows() === 0;
    }

    /**
     * Get agency by email for login
     */
    public function get_by_email($email)
    {
        return $this->db->where('email', $email)
                       ->where('login_enabled', 1)
                       ->where('enabled', 1)
                       ->where('removed', 0)
                       ->get($this->table)
                       ->row();
    }

    /**
     * Update last login
     */
    public function update_last_login($agency_id)
    {
        $this->db->where('id', $agency_id)
                 ->update($this->table, ['last_login' => date('Y-m-d H:i:s')]);
    }
}