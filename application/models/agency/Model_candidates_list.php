<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_candidates_list extends CRUD_Model
{
    protected $table = 'candidates';

    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null, $filter = null)
    {
        // Get the CI instance to access session
        $CI =& get_instance();
        $job_id = $CI->session->userdata('current_job_id');
        $agency_id = $CI->session->userdata('login')['agency']['agency_id'] ?? $CI->session->userdata('login')['agency']['id'] ?? null;

        // Log for debugging
        log_message('debug', "Model get_all called - Job ID: " . $job_id . ", Agency ID: " . $agency_id);

        if ($job_id && $agency_id) {
            // Build custom query for job-specific candidates
            $CI->db->select('c.*, j.name as job_name, j.reference_number as job_ref, a.name as agency_name');
            $CI->db->from('candidates c');
            $CI->db->join('candidate_jobs cj', 'cj.candidate_id = c.id', 'inner');
            $CI->db->join('mod_jobs j', 'j.id = cj.job_id', 'left');
            $CI->db->join('agencies a', 'a.id = c.agency_id', 'left');
            $CI->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
            $CI->db->where('cj.job_id', (int)$job_id);
            $CI->db->where('ca.agency_id', (int)$agency_id);
            $CI->db->where('c.removed', 0);

            if ($sort_by) {
                $CI->db->order_by($sort_by, $sort_order ?: 'ASC');
            } else {
                $CI->db->order_by('c.application_date', 'DESC');
            }

            if ($limit) {
                $CI->db->limit($limit, $offset);
            }

            $query = $CI->db->get();
            
            // Log the query and results
            log_message('debug', "Custom query executed: " . $CI->db->last_query());
            log_message('debug', "Custom query results: " . $query->num_rows() . " rows");
            
            return $query;
        }

        // Fallback to parent method
        return parent::get_all($limit, $offset, $sort_by, $sort_order, $filter);
    }

    public function count_all($filter = null)
    {
        $CI =& get_instance();
        $job_id = $CI->session->userdata('current_job_id');
        $agency_id = $CI->session->userdata('login')['agency']['agency_id'] ?? $CI->session->userdata('login')['agency']['id'] ?? null;

        if ($job_id && $agency_id) {
            $CI->db->from('candidates c');
            $CI->db->join('candidate_jobs cj', 'cj.candidate_id = c.id', 'inner');
            $CI->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
            $CI->db->where('cj.job_id', (int)$job_id);
            $CI->db->where('ca.agency_id', (int)$agency_id);
            $CI->db->where('c.removed', 0);

            $count = $CI->db->count_all_results();
            log_message('debug', "Custom count results: " . $count . " candidates");
            
            return $count;
        }

        return parent::count_all($filter);
    }
}