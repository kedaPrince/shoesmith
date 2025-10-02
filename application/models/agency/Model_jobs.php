<?php
class Model_jobs extends CRUD_Model
{
    protected $table = 'mod_jobs';

    public function selects()
    {
        $this->db->distinct();
        edb_select('id, title, description, location, salary, type, status', $this->table);
    }
}