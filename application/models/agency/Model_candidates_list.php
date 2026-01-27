<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_candidates_list extends CRUD_Model
{
    protected $table = 'candidates';
    public $pageName = 'candidates_list';

    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null, $filter = null)
    {
        $CI =& get_instance();
        $job_id = $CI->session->userdata('current_job_id');
        $agency_id = $this->get_agency_id_from_session();

        if ($job_id && $agency_id) {
            return $this->get_candidates_for_job($job_id, $agency_id, $limit, $offset, $sort_by, $sort_order);
        }

        return parent::get_all($limit, $offset, $sort_by, $sort_order, $filter);
    }

    private function get_agency_id_from_session()
    {
        $CI =& get_instance();
        $login = $CI->session->userdata('login');
        return $login['agency']['agency_id'] ?? $login['agency']['id'] ?? null;
    }

    private function get_candidates_for_job($job_id, $agency_id, $limit = null, $offset = null, $sort_by = null, $sort_order = null)
    {
        $this->db->select('c.*, 
                        j.name as job_name, 
                        j.reference_number as job_ref, 
                        a.name as agency_name,
                        COALESCE(cja.status, "submitted") as status,
                        COALESCE(cja.assigned_at, cj.created_at, c.application_date) as application_date');

        $this->db->from('candidates c');
        
        $this->db->join('candidate_job_assignments cja', 
                    'cja.candidate_id = c.id AND cja.job_id = ' . (int)$job_id . ' AND cja.removed = 0', 'left');
        $this->db->join('candidate_jobs cj', 
                    'cj.candidate_id = c.id AND cj.job_id = ' . (int)$job_id, 'left');
        
        $this->db->join('mod_jobs j', 'j.id = ' . (int)$job_id, 'left');
        $this->db->join('agencies a', 'a.id = c.agency_id', 'left');

        // REMOVED candidate_agencies join entirely
        // Because: if a candidate is assigned to a job, they should appear — regardless of candidate_agencies

        $this->db->where('c.removed', 0);
        $this->db->where('(cja.candidate_id IS NOT NULL OR cj.candidate_id IS NOT NULL)', null, false);

        $this->db->group_by('c.id');
        $this->apply_custom_sorting($sort_by, $sort_order);

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get();
    }

    private function apply_custom_sorting($sort_by = null, $sort_order = null)
    {
        $sort_mapping = [
            'status'           => 'COALESCE(cja.status, "submitted")',
            'application_date' => 'COALESCE(cja.assigned_at, cj.created_at, c.application_date)',
            'job_name'         => 'j.name',
            'reference_number' => 'c.reference_number',
            'first_name'       => 'c.first_name',
            'last_name'        => 'c.last_name',
            'email'            => 'c.email'
        ];

        $direction = ($sort_order && strtoupper(trim($sort_order)) === 'ASC') ? 'ASC' : 'DESC';

        if ($sort_by && isset($sort_mapping[$sort_by])) {
            $this->db->order_by($sort_mapping[$sort_by], $direction);
        } else {
            $this->db->order_by('COALESCE(cja.assigned_at, cj.created_at, c.application_date) DESC');
        }
    }

    public function count_all($filter = null)
    {
        $CI =& get_instance();
        $job_id = $CI->session->userdata('current_job_id');
        $agency_id = $this->get_agency_id_from_session();

        if ($job_id && $agency_id) {
            $this->db->select('COUNT(DISTINCT c.id) as total_count');
            $this->db->from('candidates c');
            $this->db->join('candidate_job_assignments cja', 
                           'cja.candidate_id = c.id AND cja.job_id = ' . (int)$job_id . ' AND cja.removed = 0', 'left');
            $this->db->join('candidate_jobs cj', 
                           'cj.candidate_id = c.id AND cj.job_id = ' . (int)$job_id, 'left');
            $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
            $this->db->where('ca.agency_id', (int)$agency_id);
            $this->db->where('c.removed', 0);
            $this->db->where('(cja.candidate_id IS NOT NULL OR cj.candidate_id IS NOT NULL)', null, false);

            $result = $this->db->get()->row();
            return $result ? $result->total_count : 0;
        }

        return parent::count_all($filter);
    }

    public function main_sorting()
    {
        $CI =& get_instance();
        $job_id = $CI->session->userdata('current_job_id');
        $agency_id = $this->get_agency_id_from_session();

        if ($job_id && $agency_id) {
            if (!empty($this->session->{$this->pageName . 'Sorting'})) {
                $sorting = $this->session->{$this->pageName . 'Sorting'};
                $map = [
                    'status'           => 'COALESCE(cja.status, "submitted")',
                    'application_date' => 'COALESCE(cja.assigned_at, cj.created_at, c.application_date)',
                    'job_name'         => 'j.name',
                    'reference_number' => 'c.reference_number',
                    'first_name'       => 'c.first_name',
                    'last_name'        => 'c.last_name',
                    'email'            => 'c.email'
                ];
                foreach ($sorting as $field => $dir) {
                    $f = $map[$field] ?? $field;
                    $d = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
                    $this->db->order_by($f, $d);
                }
            } else {
                $this->db->order_by('COALESCE(cja.assigned_at, cj.created_at, c.application_date) DESC');
            }
        } else {
            parent::main_sorting();
        }
    }
}