<?php
class Model_access_groups extends CRUD_Model
{
    protected $table = 'mod_access_groups';

    public function get_admins_all()
    {
        $this->db->select('id');
        $this->db->select("CONCAT(first_name, ' ', last_name) AS name");
        $this->db->where('removed', 0);
        $this->db->order_by('first_name', 'asc');
        $this->db->order_by('last_name', 'asc');
        $this->db->order_by('id', 'asc');

        return $this->db->get('usr_admins');
    }

    public function get_admins(int $accessGroupId)
    {
        $this->db->select('usr_admins.id');
        $this->db->select("CONCAT(usr_admins.first_name, ' ', usr_admins.last_name) AS name");
        $this->db->join('pivot_admin_access_groups', 'pivot_admin_access_groups.admin_id = usr_admins.id', 'inner');
        $this->db->where('pivot_admin_access_groups.access_group_id', $accessGroupId);
        $this->db->where('usr_admins.removed', 0);
        $this->db->order_by('usr_admins.first_name', 'asc');
        $this->db->order_by('usr_admins.last_name', 'asc');
        $this->db->order_by('usr_admins.id', 'asc');

        $result = $this->db->get('usr_admins')->result_array();
        $idsList = [];
        foreach ($result as $row) {
            $idsList[] = (int)$row['id'];
        }

        return $idsList;
    }
}
