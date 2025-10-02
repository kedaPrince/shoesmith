<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_agencies extends CRUD_Model
{
    protected $table = 'agencies';

    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
    {
        $this->db->join('usr_types', 'usr_types.id = agencies.agency_type_id', 'left');
        $this->db->select('usr_types.title AS agency_type');
        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

    public function get_usr_type_options()
    {
        $this->db->select('id, title');
        $this->db->from('usr_types');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('title', 'ASC');
        $query = $this->db->get();

        $options = ['' => '-- Select Agency Type --'];
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
    
    return $this->db->get(); // 👈 Returns CI_DB_result object, NOT array
}

    public function get_access_groups($agency_id)
{
    if (!is_numeric($agency_id) || $agency_id <= 0) {
        return [];
    }

    $this->db->select('access_group_id');
    $this->db->from('pivot_agency_access_groups');
    $this->db->where('agency_id', (int)$agency_id);
    $query = $this->db->get();

    // Returns array of IDs like [1, 5, 7]
    return array_column($query->result_array(), 'access_group_id');
}

}