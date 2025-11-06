<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_jobs extends CRUD_Model
{
    protected $table = 'mod_jobs';

    public function main_selects() {
        // Include candidate_count in the selection
        $this->db->select([
            'mod_jobs.id',
            'mod_jobs.enabled', 
            'mod_jobs.name',
            'mod_jobs.reference_number',
            'mod_jobs.employment_type',
            'mod_jobs.industry_id',
            'mod_jobs.agency_id',
            'mod_jobs.candidate_count',
            'mod_jobs.skills', // Add skills column
            'mod_jobs.qualifications', // Add qualifications column
            'agencies.name AS agency_name',
            'mod_industries.name AS industry_name'
        ]);
    }

    public function joins()
    {
        $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
        $this->db->join('mod_industries', 'mod_industries.id = mod_jobs.industry_id', 'left');
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

    // Remove get_skill_options() method since skills are now stored directly
    // Remove get_qualification_options() method since qualifications are now stored directly
    
    // Remove get_job_skills() method - skills are now in mod_jobs.skills column
    // Remove get_job_qualifications() method - qualifications are now in mod_jobs.qualifications column

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