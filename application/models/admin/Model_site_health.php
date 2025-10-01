<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_site_health extends CRUD_Model {

    public function get_all_heartbeats() {
        $this->db->select("
            sys_heartbeats.id,
            sys_heartbeats.title,
            sys_heartbeats.status,
            sys_heartbeats.start_time,
            sys_heartbeats.end_time,
        ");
        $this->db->order_by("sys_heartbeats.id", "DESC");

        $query = $this->db->get("sys_heartbeats");

        return $query->result();
    }

    public function get_heartbeat($id) {
        $this->db->select("*");
        $this->db->where("sys_heartbeats.id", $id);

        $query = $this->db->get("sys_heartbeats");

        if ($query->num_rows() > 0) {
            $row = $query->row();

            return $row;
        }
        else {
            return false;
        }
    }

}