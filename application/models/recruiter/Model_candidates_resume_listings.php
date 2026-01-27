<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Model_candidates_resume_listings extends CRUD_Model
{
    public $table = 'candidates';
    public $pageName = 'candidates_resume_listings';

    public function __construct()
    {
        parent::__construct();
    }

public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
{
    $this->db->select('
        candidates.*,
        agencies.name as agency_name,
        mod_jobs.name as job_name
    ');
    
    $this->db->from('candidates');
    $this->db->join('agencies', 'agencies.id = candidates.agency_id', 'left');
    $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
    
    $this->db->where('candidates.removed', 0);
    $this->db->where('candidates.enabled', 1);
    
    // ADD THIS: Filter by recruiter
    $login_data = $this->session->userdata('login');
    if (!empty($login_data['recruiter'])) {
        // Access as array instead of object
        $recruiter_id = $login_data['recruiter']['id'] ?? $login_data['recruiter']->id ?? null;
        if ($recruiter_id) {
            $this->db->where('candidates.recruiter_id', $recruiter_id);
        }
    }

    // Only show candidates with CV files
    $this->db->where('candidates.cv_file IS NOT NULL');
    $this->db->where('candidates.cv_file !=', '');

    // Apply filters
    $filters = $this->session->userdata('ecms_filters');
    if (!empty($filters[$this->pageName])) {
        foreach ($filters[$this->pageName] as $filter_name => $filter) {
            switch ($filter_name) {
                case 'search':
                    if (!empty($filter['value'])) {
                        $search_term = $this->db->escape_like_str($filter['value']);
                        $this->db->group_start();
                        $this->db->or_like('candidates.first_name', $search_term);
                        $this->db->or_like('candidates.last_name', $search_term);
                        $this->db->or_like('candidates.email', $search_term);
                        $this->db->or_like('candidates.reference_number', $search_term);
                        $this->db->group_end();
                    }
                    break;

                case 'status':
                    if (!empty($filter['value'])) {
                        $this->db->where('candidates.status', $filter['value']);
                    }
                    break;

                case 'has_cv':
                    if ($filter['value'] === '0') {
                        $this->db->where('(candidates.cv_file IS NULL OR candidates.cv_file = "")');
                    }
                    break;
            }
        }
    }

    // Handle sorting
    if (!empty($sort_by) && !empty($sort_order)) {
        $this->db->order_by($sort_by, $sort_order);
    } else {
        $this->db->order_by('candidates.application_date', 'DESC');
    }

    // Handle limit and offset for pagination
    if (!is_null($limit)) {
        $this->db->limit($limit, $offset);
    }

    $query = $this->db->get();
    return $query;
}

public function get_count()
{
    $this->db->from('candidates');
    $this->db->where('removed', 0);
    $this->db->where('enabled', 1);
    $this->db->where('cv_file IS NOT NULL');
    $this->db->where('cv_file !=', '');
    
    
    $login_data = $this->session->userdata('login');
    if (!empty($login_data['recruiter'])) {
        // Access as array instead of object
        $recruiter_id = $login_data['recruiter']['id'] ?? $login_data['recruiter']->id ?? null;
        if ($recruiter_id) {
            $this->db->where('candidates.recruiter_id', $recruiter_id);
        }
    }

    // Apply filters
    $filters = $this->session->userdata('ecms_filters');
    if (!empty($filters[$this->pageName])) {
        foreach ($filters[$this->pageName] as $filter_name => $filter) {
            switch ($filter_name) {
                case 'search':
                    if (!empty($filter['value'])) {
                        $search_term = $this->db->escape_like_str($filter['value']);
                        $this->db->group_start();
                        $this->db->or_like('candidates.first_name', $search_term);
                        $this->db->or_like('candidates.last_name', $search_term);
                        $this->db->or_like('candidates.email', $search_term);
                        $this->db->or_like('candidates.reference_number', $search_term);
                        $this->db->group_end();
                    }
                    break;

                case 'status':
                    if (!empty($filter['value'])) {
                        $this->db->where('candidates.status', $filter['value']);
                    }
                    break;

                case 'has_cv':
                    if ($filter['value'] === '0') {
                        $this->db->where('(candidates.cv_file IS NULL OR candidates.cv_file = "")');
                    }
                    break;
            }
        }
    }

    return $this->db->count_all_results();
}

public function get_candidate_with_cv($id)
{
    $this->db->select('candidates.*, agencies.name as agency_name, mod_jobs.name as job_name');
    $this->db->from('candidates');
    $this->db->join('agencies', 'agencies.id = candidates.agency_id', 'left');
    $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
    $this->db->where('candidates.id', $id);
    $this->db->where('candidates.removed', 0);
    
    
    $login_data = $this->session->userdata('login');
    if (!empty($login_data['recruiter'])) {
        // Access as array instead of object
        $recruiter_id = $login_data['recruiter']['id'] ?? $login_data['recruiter']->id ?? null;
        if ($recruiter_id) {
            $this->db->where('candidates.recruiter_id', $recruiter_id);
        }
    }
    
    $query = $this->db->get();
    return $query->row();
}

    public function get_cv_stats()
    {
        $stats = array();

        // Total candidates with CV
        $this->db->where('removed', 0);
        $this->db->where('enabled', 1);
        $this->db->where('cv_file IS NOT NULL');
        $this->db->where('cv_file !=', '');
        $stats['total_with_cv'] = $this->db->count_all_results('candidates');

        // CVs by status
        $this->db->select('status, COUNT(*) as count');
        $this->db->from('candidates');
        $this->db->where('removed', 0);
        $this->db->where('enabled', 1);
        $this->db->where('cv_file IS NOT NULL');
        $this->db->where('cv_file !=', '');
        $this->db->group_by('status');
        $query = $this->db->get();
        $stats['by_status'] = $query->result();

        return $stats;
    }
}