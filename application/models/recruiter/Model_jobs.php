<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_jobs extends CRUD_Model
{
    protected $table = 'mod_jobs';

    public function main_selects() {
        parent::main_selects();  // Call parent to get id, enabled, etc.

        // Ensure joined fields are selected for listFields
        $this->db->select('agencies.name AS agency_name');
        $this->db->select('mod_industries.name AS industry_name');
    }

    public function joins()
    {
        $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
        $this->db->join('mod_industries', 'mod_industries.id = mod_jobs.industry_id', 'left');
    }

    // ✅ Override get_all to control SELECT and JOIN manually
    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null, $agency_id = null)
    {
        // Start fresh
        $this->db->reset_query();

        // Select base fields
        $this->db->select("{$this->table}.id, {$this->table}.enabled, {$this->table}.name, {$this->table}.reference_number, {$this->table}.employment_type");

        // Select joined fields with aliases
        $this->db->select('agencies.name AS agency_name');
        $this->db->select('mod_industries.name AS industry_name');

        // From main table
        $this->db->from($this->table);

        // Joins
        $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
        $this->db->join('mod_industries', 'mod_industries.id = mod_jobs.industry_id', 'left');

        // Filters
        $this->db->where("{$this->table}.removed", 0);
        if (!empty($agency_id)) {
            $this->db->where("{$this->table}.agency_id", $agency_id);
        }

        // Sorting
        if ($sort_by) {
            // Map listFields aliases to real columns
            $sort_map = [
                'name' => "{$this->table}.name",
                'reference_number' => "{$this->table}.reference_number",
                'employment_type' => "{$this->table}.employment_type",
                'industry' => 'mod_industries.name',
                'agency' => 'agencies.name',
            ];
            $real_sort = $sort_map[$sort_by] ?? "{$this->table}.name";
            $this->db->order_by($real_sort, $sort_order ?: 'ASC');
        } else {
            $this->db->order_by("{$this->table}.name", 'ASC');
        }

        // Limit
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get();
    }

   

    public function get_agency_options($user_agency_id = null)
    {
        $this->db->select('id, name');
        $this->db->from('agencies');
        $this->db->where('removed', 0);
        if (!empty($user_agency_id)) {
            $this->db->where('id', $user_agency_id);
        } else {
            $this->db->where('enabled', 1);
        }
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_industry_options()
    {
        $this->db->select('id, name');
        $this->db->from('mod_industries');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_skill_options()
    {
        $this->db->select('id, name');
        $this->db->from('mod_job_skills');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_qualification_options()
    {
        $this->db->select('id, name');
        $this->db->from('mod_job_qualifications');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_job_skills($job_id)
    {
        if (empty($job_id)) return [];
        $this->db->select('skill_id');
        $this->db->from('pivot_job_skills');
        $this->db->where('job_id', $job_id);
        return array_column($this->db->get()->result_array(), 'skill_id');
    }

    public function get_job_qualifications($job_id)
    {
        if (empty($job_id)) return [];
        $this->db->select('qualification_id');
        $this->db->from('pivot_job_qualifications');
        $this->db->where('job_id', $job_id);
        return array_column($this->db->get()->result_array(), 'qualification_id');
    }

    public function is_unique_reference($reference, $id = "")
    {
        $this->db->from($this->table);
        $this->db->where('reference_number', $reference);
        $this->db->where('removed', 0);
        if (!empty($id)) {
            $this->db->where('id !=', $id);
        }
        return $this->db->count_all_results() == 0;
    }
}