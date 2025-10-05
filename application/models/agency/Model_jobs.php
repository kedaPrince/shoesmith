<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_jobs extends CRUD_Model
{
    protected $table = 'mod_jobs';

    // REMOVE the custom selects() method - let CRUD_Model handle it
    // The CRUD_Model automatically handles the main_selects()

    public function joins()
    {
        // Join agency (required)
        $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
        $this->db->select('agencies.name AS agency_name');

        // Join industry (optional)
        $this->db->join('mod_industries', 'mod_industries.id = mod_jobs.industry_id', 'left');
        $this->db->select('mod_industries.name AS industry_name');
    }

    // Get all agencies for dropdowns
    public function get_agency_options()
    {
        $this->db->select('id, name');
        $this->db->from('agencies');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    // Get all industries for dropdowns
    public function get_industry_options()
    {
        $this->db->select('id, name');
        $this->db->from('mod_industries');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    // Get all skills for multi-select
    public function get_skill_options()
    {
        $this->db->select('id, name');
        $this->db->from('mod_job_skills');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    // Get all qualifications for multi-select
    public function get_qualification_options()
    {
        $this->db->select('id, name');
        $this->db->from('mod_job_qualifications');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    // Get selected skills for a job
    public function get_job_skills($job_id)
    {
        if (empty($job_id)) return [];
        $this->db->select('skill_id');
        $this->db->from('pivot_job_skills');
        $this->db->where('job_id', $job_id);
        $query = $this->db->get();
        return array_column($query->result_array(), 'skill_id');
    }

    // Get selected qualifications for a job
    public function get_job_qualifications($job_id)
    {
        if (empty($job_id)) return [];
        $this->db->select('qualification_id');
        $this->db->from('pivot_job_qualifications');
        $this->db->where('job_id', $job_id);
        $query = $this->db->get();
        return array_column($query->result_array(), 'qualification_id');
    }

    // Unique reference number check
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