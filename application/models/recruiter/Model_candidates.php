<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_candidates extends CRUD_Model
{
    protected $table = 'candidates';

    public function get_all($limit = null, $offset = null, $sort_by = 'first_name', $sort_order = 'ASC', $job_id = null)
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

        // Add job filtering if job_id is provided
        if (!empty($job_id)) {
            $this->db->group_start();
            $this->db->where('candidates.job_id', $job_id); // Primary job assignment
            $this->db->or_where("candidates.id IN (SELECT candidate_id FROM candidate_jobs WHERE job_id = $job_id)"); // Additional job assignments
            $this->db->group_end();
        }

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

    /**
     * Get candidate details
     */
    public function get_candidate($id)
    {
        $this->db->select('c.*, j.name as job_name, j.reference_number as job_ref');
        $this->db->from('candidates c');
        $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
        $this->db->where('c.id', $id);
        $this->db->where('c.removed', 0);
        
        return $this->db->get()->row();
    }

    /**
     * Save candidate document
     */
    public function save_candidate_document($data)
    {
        return $this->db->insert('candidate_documents', $data);
    }

    /**
     * Get candidate documents
     */
    public function get_candidate_documents($candidate_id)
    {
        return $this->db->where('candidate_id', $candidate_id)
                        ->where('removed', 0)
                        ->order_by('created_at', 'DESC')
                        ->get('candidate_documents')
                        ->result();
    }

    /**
     * Get single document
     */
    public function get_document($document_id)
    {
        return $this->db->where('id', $document_id)
                        ->where('removed', 0)
                        ->get('candidate_documents')
                        ->row();
    }

    /**
     * Delete candidate document (soft delete)
     */
    public function delete_candidate_document($document_id)
    {
        return $this->db->where('id', $document_id)
                        ->update('candidate_documents', [
                            'removed' => 1,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
    }

    /**
     * Check if recruiter has access to candidate
     */
    public function check_recruiter_candidate_access($recruiter_id, $candidate_id)
    {
        // Get recruiter's agency
        $recruiter = $this->db->select('agency_id')
                             ->from('recruiters')
                             ->where('id', $recruiter_id)
                             ->where('enabled', 1)
                             ->where('removed', 0)
                             ->get()
                             ->row();
        
        if (!$recruiter) {
            return false;
        }

        // Check if candidate is associated with recruiter's agency
        $this->db->select('1')
                 ->from('candidate_agencies')
                 ->where('candidate_id', $candidate_id)
                 ->where('agency_id', $recruiter->agency_id);
        
        return $this->db->get()->row() !== null;
    }

    /**
     * Main query for candidates listing
     */
    public function main_selects()
    {
        $this->db->select('candidates.*');
        $this->db->select('mod_jobs.name as job_name');
    }

    public function main_joins()
    {
        $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
    }

    public function main_wheres()
    {
        // Filter by recruiter's agency
        $login_data = $this->session->userdata('login');
        if (!empty($login_data['recruiter'])) {
            $recruiter = $login_data['recruiter'];
            $agency_id = $recruiter['agency_id'] ?? $recruiter['id'];
            
            $this->db->join('candidate_agencies ca', 'ca.candidate_id = candidates.id', 'inner');
            $this->db->where('ca.agency_id', $agency_id);
        }
        
        $this->db->where('candidates.removed', 0);
    }

    public function main_sorting()
    {
        $this->db->order_by('candidates.created_at', 'DESC');
    }

    /**
     * Generate reference number for candidate
     */
    public function generate_reference_number()
    {
        $prefix = 'CAND';
        $year = date('Y');
        
        // Get the last reference number
        $this->db->select('reference_number')
                 ->from('candidates')
                 ->like('reference_number', $prefix . '-' . $year, 'after')
                 ->order_by('id', 'DESC')
                 ->limit(1);
        
        $last_ref = $this->db->get()->row();
        
        if ($last_ref) {
            $last_number = intval(substr($last_ref->reference_number, -4));
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }
        
        return $prefix . '-' . $year . '-' . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if email is unique
     */
    public function is_unique_email($email, $id = null)
    {
        $this->db->where('email', $email);
        $this->db->where('removed', 0);
        
        if ($id) {
            $this->db->where('id !=', $id);
        }
        
        return $this->db->count_all_results('candidates') === 0;
    }


    /**
     * Check if required documents have been submitted and update stage
     */
    public function check_and_update_documents_stage($candidate_id) {
        // Get required documents
        $required_documents = $this->get_required_documents($candidate_id);
        
        if (!empty($required_documents)) {
            // Documents have been submitted, update the stage
            $update_data = array(
                'stage_requested_docs' => 1,
                'stage_requested_docs_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            $result = $this->db->where('id', $candidate_id)->update($this->table, $update_data);

            if ($result) {
                $this->update_onboarding_progress($candidate_id);
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get required documents for candidate (documents with type 'required_document')
     */
    public function get_required_documents($candidate_id) {
        $this->db->select('cd.*, 
                        CASE 
                            WHEN cd.uploaded_by_type = "recruiter" THEN CONCAT(r.first_name, " ", r.last_name)
                            WHEN cd.uploaded_by_type = "agency" THEN CONCAT(a.first_name, " ", a.last_name)
                            ELSE "System"
                        END as uploader_name');
        $this->db->from('candidate_documents cd');
        $this->db->join('recruiters r', 'r.id = cd.uploaded_by AND cd.uploaded_by_type = "recruiter"', 'left');
        $this->db->join('agency_staff a', 'a.id = cd.uploaded_by AND cd.uploaded_by_type = "agency"', 'left');
        $this->db->where('cd.candidate_id', $candidate_id);
        $this->db->where('cd.document_type', 'required_document'); // Filter for required documents
        $this->db->where('cd.removed', 0);
        $this->db->order_by('cd.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get candidates filtered by job ID
     */
    public function get_candidates_by_job($job_id, $limit = null, $offset = null, $sort_by = 'first_name', $sort_order = 'ASC')
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

        $this->db->from('candidates');
        $this->db->where('candidates.removed', 0);

        // Add job filtering - check both primary job_id and candidate_jobs pivot table
        $this->db->group_start();
        $this->db->where('candidates.job_id', $job_id); // Primary job assignment
        $this->db->or_where("candidates.id IN (SELECT candidate_id FROM candidate_jobs WHERE job_id = $job_id)"); // Additional job assignments
        $this->db->group_end();

        // Sorting
        if ($sort_by) {
            if (!in_array($sort_by, ['agency_name', 'job_name'])) {
                $this->db->order_by("candidates.$sort_by", $sort_order ?: 'ASC');
            }
        }

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get();
    }

    /**
     * Count candidates by job ID
     */
    public function count_candidates_by_job($job_id)
    {
        $this->db->from('candidates');
        $this->db->where('candidates.removed', 0);
        
        // Add job filter
        $this->db->group_start();
        $this->db->where('candidates.job_id', $job_id); // Primary job assignment
        $this->db->or_where("candidates.id IN (SELECT candidate_id FROM candidate_jobs WHERE job_id = $job_id)"); // Additional job assignments
        $this->db->group_end();
        
        return $this->db->count_all_results();
    }

    public function create(array $data, $table = false)
    {
    
        
        // Use the provided table or default to $this->table
        $target_table = $table ? $table : $this->table;
        
        $result = $this->db->insert($target_table, $data);
        
        if ($result) {
            $id = $this->db->insert_id();
            
            return $id;
        } else {
            $error = $this->db->error();
            return false;
        }
    }

}