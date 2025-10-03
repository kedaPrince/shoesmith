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
     * Get agency options for dropdowns (e.g., when assigning staff to agencies)
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
}