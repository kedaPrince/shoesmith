<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_model extends CI_model {

    public function __construct() {
        parent::__construct();
    }

    public function get_uploader_data($table, $parentField, $parentID, $pathField, $fields=array(), $includePosition=true) {

        //Select default fields
        $this->db->select('id');
        $includePosition && $this->db->select('position');
        $this->db->select($pathField);

        //Select extra fields
        if (!empty($fields)) {
            $this->db->select($fields);
        }

        $this->db->where($parentField, $parentID);
        $this->db->where('removed', 0);
        $this->db->where('enabled', 1);
        $includePosition && $this->db->order_by('position', 'asc');
        $query = $this->db->get($table);

        return $query;
    }
}
