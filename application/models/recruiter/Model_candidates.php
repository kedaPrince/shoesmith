<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_candidates extends CRUD_Model
{
    protected $table = 'candidates';

    // Override to include joins for agency/job names in listing
    public function get_all($limit = null, $offset = null, $sort_by = 'first_name', $sort_order = 'ASC')
    {
        $this->db->select('candidates.*, agencies.name as agency_name, mod_jobs.name as job_name');
        $this->db->from($this->table);
        $this->db->join('agencies', 'agencies.id = candidates.agency_id', 'left');
        $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
        $this->db->where('candidates.removed', 0);

        if ($sort_by) {
            // Map virtual fields
            $sort_map = [
                'agency_name' => 'agencies.name',
                'job_name' => 'mod_jobs.name',
            ];
            $real_sort = $sort_map[$sort_by] ?? "candidates.$sort_by";
            $this->db->order_by($real_sort, $sort_order ?: 'ASC');
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

    public function generate_reference_number()
    {
        $prefix = 'CAND';
        $count = $this->db->where('YEAR(created_at)', date('Y'))
                          ->where('enabled', 1)
                          ->count_all_results($this->table);
        $sequence = $count + 1;
        return $prefix . '-' . date('Y') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}