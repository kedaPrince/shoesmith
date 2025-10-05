<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_jobs extends CRUD_Model{
    protected $table = 'mod_jobs';

    public function joins(){
        $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
        $this->db->select('mod_jobs.agency_id, agencies.name AS agency_name'); // Explicitly select agency_id

        $this->db->join('mod_industries', 'mod_industries.id = mod_jobs.industry_id', 'left');
        $this->db->select('mod_industries.name AS industry_name');
    }

    /**
     * Override get_all to support agency filtering
     */
    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null, $agency_id = null){
        // Apply agency filter if provided
        if (!empty($agency_id)) {
            $this->db->where('mod_jobs.agency_id', $agency_id);
            log_message('debug', 'Model filtering jobs by agency_id: ' . $agency_id);
        }
        
        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

    public function get_agency_options($user_agency_id = null){
        $this->db->select('id, name');
        $this->db->from('agencies');
        $this->db->where('removed', 0);
        
        log_message('debug', 'Filtering agencies with user_agency_id: ' . $user_agency_id);
        
        // Filter by user's agency if provided
        if (!empty($user_agency_id)) {
            $this->db->where('id', $user_agency_id);
            // Don't check enabled status for user's own agency
            log_message('debug', 'Filtering to show only agency ID: ' . $user_agency_id);
        } else {
            // For general listing, only show enabled agencies
            $this->db->where('enabled', 1);
            log_message('debug', 'Showing all enabled agencies');
        }
        
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    // Other methods remain the same...
    public function get_industry_options(){
        $this->db->select('id, name');
        $this->db->from('mod_industries');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_skill_options(){
        $this->db->select('id, name');
        $this->db->from('mod_job_skills');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_qualification_options(){
        $this->db->select('id, name');
        $this->db->from('mod_job_qualifications');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_job_skills($job_id){
        if (empty($job_id)) return [];
        $this->db->select('skill_id');
        $this->db->from('pivot_job_skills');
        $this->db->where('job_id', $job_id);
        $query = $this->db->get();
        return array_column($query->result_array(), 'skill_id');
    }

    public function get_job_qualifications($job_id){
        if (empty($job_id)) return [];
        $this->db->select('qualification_id');
        $this->db->from('pivot_job_qualifications');
        $this->db->where('job_id', $job_id);
        $query = $this->db->get();
        return array_column($query->result_array(), 'qualification_id');
    }

    public function is_unique_reference($reference, $id = "") {
        $this->db->from($this->table);
        $this->db->where('reference_number', $reference);
        $this->db->where('removed', 0);
        if (!empty($id)) {
            $this->db->where('id !=', $id);
        }
        return $this->db->count_all_results() == 0;
    }
}