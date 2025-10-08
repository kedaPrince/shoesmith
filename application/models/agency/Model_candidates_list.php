<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_candidates_list extends CRUD_Model
{
    protected $table = 'candidates';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Override the main_selects to include joins
     */
    public function main_selects()
    {
        parent::main_selects();
        $this->db->select('mod_jobs.name as job_name, mod_jobs.reference_number as job_ref');
    }

    /**
     * Override joins to include job table
     */
    public function joins()
    {
        $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
    }

    /**
     * Override get_all to apply strict filtering
     */
    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
    {
        // Get filtering parameters from session
        $job_id = $this->session->userdata('current_job_id');
        $agency_id = $this->get_user_agency_id();
        
        log_message('debug', 'Model get_all - Job ID: ' . $job_id . ', Agency ID: ' . $agency_id);

        // Apply strict filtering
        if ($job_id && $agency_id) {
            $this->db->where('candidates.job_id', $job_id);
            $this->db->where('candidates.agency_id', $agency_id);
        }
        
        $this->db->where('candidates.removed', 0);

        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

    /**
     * Get user agency ID for model filtering
     */
    private function get_user_agency_id()
    {
        $CI =& get_instance();
        $login = $CI->session->userdata('login');
        
        if (!empty($login['agency'])) {
            $agency_user = $login['agency'];
            return !empty($agency_user['agency_id']) ? $agency_user['agency_id'] : 
                   (!empty($agency_user['id']) ? $agency_user['id'] : null);
        }
        
        return null;
    }

    /**
     * Keep these for backward compatibility
     */
    public function get_candidates_by_job($job_id, $agency_id, $limit = null, $offset = null, $sort_by = 'first_name', $sort_order = 'ASC')
    {
        $this->db->select('candidates.*, mod_jobs.name as job_name');
        $this->db->from($this->table);
        $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
        $this->db->where('candidates.job_id', (int)$job_id);
        $this->db->where('candidates.agency_id', (int)$agency_id);
        $this->db->where('candidates.removed', 0);

        if ($sort_by && in_array($sort_by, ['first_name', 'last_name', 'email', 'status', 'application_date'])) {
            $this->db->order_by($sort_by, $sort_order ?: 'ASC');
        } else {
            $this->db->order_by('application_date', 'DESC');
        }

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get();
    }

    public function count_candidates_by_job($job_id, $agency_id)
    {
        return $this->db->where('job_id', (int)$job_id)
                        ->where('agency_id', (int)$agency_id)
                        ->where('removed', 0)
                        ->count_all_results($this->table);
    }
}