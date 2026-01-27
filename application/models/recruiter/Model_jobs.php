<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_jobs extends CRUD_Model
{
    protected $table = 'mod_jobs';

    public function main_selects() {

        $this->db->select([
            'mod_jobs.id',
            'mod_jobs.uuid', 
            'mod_jobs.enabled', 
            'mod_jobs.name',
            'mod_jobs.reference_number',
            'mod_jobs.employment_type',
            'mod_jobs.industry_id',
            'mod_jobs.agency_id',
            'mod_jobs.candidate_count',
            'mod_jobs.closing_date', // ADD THIS LINE
            'mod_jobs.skills',
            'mod_jobs.qualifications',
            'agencies.name AS agency_name',
            'mod_industries.name AS industry_name'
        ]);
    }

    public function get_job_by_id($job_id)
    {
        $this->db->select('uuid');
        $this->db->from('mod_jobs');
        $this->db->where('id', $job_id);
        $this->db->where('removed', 0);
        return $this->db->get()->row();
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

    public function get_candidates_for_job($job_id, $recruiter_id = null)
    {
        if (empty($job_id) || !is_numeric($job_id)) {
            return [];
        }

        $this->db->select('
            c.id,
            c.first_name,
            c.last_name, 
            c.reference_number,
            c.email,
            c.phone,
            c.assigned_agent_id,
            cja.assigned_at,
            cja.status as assignment_status
        ');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('candidates c', 'c.id = cja.candidate_id', 'inner');
        $this->db->where('cja.job_id', (int)$job_id);
        $this->db->where('cja.removed', 0); // Only active assignments
        $this->db->where('c.enabled', 1);
        $this->db->where('c.removed', 0);
        
        if ($recruiter_id && is_numeric($recruiter_id)) {
            $this->db->where('c.assigned_agent_id', (int)$recruiter_id);
        } else {
        }
        
        $this->db->order_by('c.first_name', 'ASC');
        
        $result = $this->db->get()->result();
        
        return $result;
    }

    public function get_candidate_count_for_job($job_id, $recruiter_id = null)
    {
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('candidates c', 'c.id = cja.candidate_id');
        $this->db->where('cja.job_id', $job_id);
        $this->db->where('cja.removed', 0); // ADD THIS LINE - filter out removed assignments
        $this->db->where('c.enabled', 1);
        $this->db->where('c.removed', 0);
        
        if ($recruiter_id) {
            $this->db->where('c.assigned_agent_id', $recruiter_id);
        }
        
        return $this->db->count_all_results();
    }

   
    public function get_all_with_expired_last()
    {
        $this->main_selects();
        $this->joins();
        
        // Add WHERE clause for removed if needed
        $this->db->where('mod_jobs.removed', 0);
        
        // Sort with expired jobs at bottom
        $this->db->order_by("CASE 
            WHEN closing_date = '0000-00-00' THEN 0
            WHEN closing_date < CURDATE() THEN 1 
            ELSE 0 
        END", 'ASC');
        $this->db->order_by('mod_jobs.name', 'ASC');
        
        return $this->db->get('mod_jobs');
    }

}