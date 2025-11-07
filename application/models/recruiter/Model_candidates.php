<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_candidates extends CRUD_Model
{
    protected $table = 'candidates';

    // Override to include joins for agency/job names in listing
   public function get_all($limit = null, $offset = null, $sort_by = 'first_name', $sort_order = 'ASC')
{
    // Select base candidate fields
    $this->db->select('candidates.*');

    // Subquery: get all agency names for this candidate
    $this->db->select("(SELECT GROUP_CONCAT(a.name SEPARATOR ', ')
                        FROM candidate_agencies ca
                        JOIN agencies a ON a.id = ca.agency_id
                        WHERE ca.candidate_id = candidates.id
                        AND a.removed = 0 AND a.enabled = 1
                       ) AS agency_name", false);

    // Subquery: get all job names for this candidate
    $this->db->select("(SELECT GROUP_CONCAT(j.name SEPARATOR ', ')
                        FROM candidate_jobs cj
                        JOIN mod_jobs j ON j.id = cj.job_id
                        WHERE cj.candidate_id = candidates.id
                        AND j.removed = 0 AND j.enabled = 1
                       ) AS job_name", false);

    $this->db->from($this->table);
    $this->db->where('candidates.removed', 0);

    // Sorting
    if ($sort_by) {
        // Only allow sorting on real fields (not virtual agency_name/job_name for now)
        if (!in_array($sort_by, ['agency_name', 'job_name'])) {
            $this->db->order_by("candidates.$sort_by", $sort_order ?: 'ASC');
        }
        // Optional: add complex sorting later if needed
    }

    if ($limit !== null) {
        $this->db->limit($limit, $offset);
    }

    return $this->db->get();
}

    public function count_all()
    {
        return $this->db->where('removed', 0)->count_all_results($this->table);
    }

    public function is_unique_email($email, $id = "")
    {
        $this->db->where('email', $email);
        $this->db->where('enabled', 1);
        if (!empty($id)) {
            $this->db->where('id !=', $id);
        }
        return $this->db->get($this->table)->num_rows() == 0;
    }

    public function get_agencies_all()
    {
        return $this->db->select('id, name')
                        ->from('agencies')
                        ->where('enabled', 1)
                        ->where('removed', 0)
                        ->order_by('name', 'ASC')
                        ->get()
                        ->result();
    }

    public function get_jobs_all()
    {
        return $this->db->select('id, name as job_title, reference_number as job_reference')
                        ->from('mod_jobs')
                        ->where('enabled', 1)
                        ->where('removed', 0)
                        ->order_by('name', 'ASC')
                        ->get()
                        ->result();
    }

    public function get_agency_agents_by_agency($agency_id)
    {
        return $this->db->select('id, first_name, last_name, email')
                        ->from('agency_staff')
                        ->where('agency_id', (int)$agency_id)
                        ->where('enabled', 1)
                        ->order_by('first_name', 'ASC')
                        ->get()
                        ->result();
    }

    /**
     * Generate reference number for new candidates
     */
    public function generate_reference_number()
    {
        try {
            $prefix = 'CAND';
            $year = date('Y');
            
            // Count candidates created this year
            $this->db->where('YEAR(created_at)', $year);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $count = $this->db->count_all_results($this->table);
            
            $sequence = $count + 1;
            $reference = $prefix . '-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            
            // Log for debugging
            log_message('debug', "Generated reference number: {$reference} (count: {$count})");
            
            return $reference;
        } catch (Exception $e) {
            error_log('Error in generate_reference_number: ' . $e->getMessage());
            // Fallback reference
            $prefix = 'CAND';
            $year = date('Y');
            $timestamp = time() % 10000;
            return $prefix . '-' . $year . '-' . str_pad($timestamp, 4, '0', STR_PAD_LEFT);
        }
    }

    // Get additional agencies assigned to candidate
    public function get_candidate_additional_agencies($candidate_id)
    {
        if (empty($candidate_id)) return [];
        
        $result = $this->db->select('ca.agency_id')
                        ->from('candidate_agencies ca')
                        ->where('ca.candidate_id', $candidate_id)
                        ->get()
                        ->result_array();
        
        return array_column($result, 'agency_id');
    }

    // Get additional jobs assigned to candidate
    public function get_candidate_additional_jobs($candidate_id)
    {
        if (empty($candidate_id)) return [];
        
        $result = $this->db->select('cj.job_id')
                        ->from('candidate_jobs cj')
                        ->where('cj.candidate_id', $candidate_id)
                        ->get()
                        ->result_array();
        
        return array_column($result, 'job_id');
    }

    // NEW METHODS - For multi-select options (like skills pattern)
public function get_additional_agency_options($exclude_id = null)
{
    $this->db->select('id, name')
             ->from('agencies')
             ->where('enabled', 1)
             ->where('removed', 0);
    if ($exclude_id) {
        $this->db->where('id !=', $exclude_id);
    }
    return $this->db->order_by('name', 'ASC')->get()->result();
}

public function get_additional_job_options($exclude_id = null)
{
    $this->db->select('id, name, reference_number')
             ->from('mod_jobs')
             ->where('enabled', 1)
             ->where('removed', 0);
    if ($exclude_id) {
        $this->db->where('id !=', $exclude_id);
    }
    return $this->db->order_by('name', 'ASC')->get()->result();
}


public function get_by_id($id, $table = false)
{
    $table = $table ?: $this->table;
    return $this->db->where($table . '.id', $id)
                    ->where($table . '.removed', 0)
                    ->get($table)
                    ->row();
}

// Add this method to your Model_candidates
public function get_job_by_id($job_id)
{
    return $this->db->select('mj.*, a.name as agency_name')
                    ->from('mod_jobs mj')
                    ->join('agencies a', 'a.id = mj.agency_id', 'left')
                    ->where('mj.id', $job_id)
                    ->where('mj.enabled', 1)
                    ->where('mj.removed', 0)
                    ->get()
                    ->row();
}

}