<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_candidates extends CRUD_Model
{
    protected $table = 'candidates';

public function get_all($limit = null, $offset = null, $sort_by = 'first_name', $sort_order = 'ASC', $filters = [])
    {
        // DEBUG: Start of get_all
        
        // Use current filters if no filters passed
        if (empty($filters)) {
            $filters = $this->get_current_filters();
        } else {
        }
        
        // Log all parameters for debugging
        
        // YOUR EXISTING CODE - DON'T CHANGE THIS PART
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

        // Filter by recruiter's assigned candidates
        $recruiter_id = $this->get_recruiter_id();
        if ($recruiter_id) {
            $this->db->where('candidates.assigned_agent_id', $recruiter_id);
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $this->db->where('candidates.status', $filters['status']);
        }

        // Apply search filter
        if (!empty($filters['general'])) {
            $this->db->group_start();
            foreach (['candidates.first_name', 'candidates.last_name', 'candidates.email', 'candidates.reference_number'] as $field) {
                $this->db->or_like($field, $filters['general']);
            }
            $this->db->group_end();
        }

        // Apply job_id filter
        if (!empty($filters['job_id'])) {
            $job_id = $filters['job_id'];
            $this->db->group_start();
            $this->db->where('candidates.job_id', $job_id);
            $this->db->or_where("candidates.id IN (SELECT candidate_id FROM candidate_jobs WHERE job_id = $job_id)");
            $this->db->group_end();
            
        }

        // Sorting
        if ($sort_by) {
            if (!in_array($sort_by, ['agency_name', 'job_name'])) {
                $this->db->order_by("candidates.$sort_by", $sort_order ?: 'ASC');
            }
        }

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
 
        return $query;
    }

public function ajax_pager_fetch_batch($batch = 1, $section = "", $template = "listing")
{
    log_message('debug', 'Candidates::ajax_pager_fetch_batch() called');
    log_message('debug', '  batch=' . $batch . ', section="' . $section . '"');
    
    // Get current page from URL
    $page = $this->input->get('page') ?: $batch;
    $limit = $this->perPage; // Should be 10
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    log_message('debug', '  Using page=' . $page . ', limit=' . $limit . ', offset=' . $offset);
    
    // Get filters from session
    $filters = $this->get_filters_from_session();
    log_message('debug', '  Active filters: ' . json_encode($filters));
    
    // Set filters to model
    $this->{$this->model}->set_current_filters($filters);
    
    // Call parent with calculated limit/offset
    $this->page = $batch;
    
    try {
        // Call get_all with explicit limit/offset
        $query = $this->{$this->model}->get_all($limit, $offset, 'first_name', 'ASC', $filters);
    } catch(Exception $e) {
        log_message('error', 'Error in get_all: ' . $e->getMessage());
        
        // Fallback to parent method
        parent::ajax_pager_fetch_batch($batch, $section, $template);
        return;
    }
    
    $amount = $this->{$this->model}->count_all();
    log_message('debug', '  Total candidates: ' . $amount);
    
    // Generate HTML
    $html = $this->load->view('cms/crud/ajax_' . $template . '_rows', array(
        'query' => $query,
        'batch' => $batch,
        'amount' => $amount
    ), TRUE);
    
    $this->output->set_output($html);
}

/**
 * Get paginated results with proper counting
 */
public function get_paginated($page = 1, $per_page = 10)
{
    $offset = ($page - 1) * $per_page;
    
    // Get filters from session via controller
    $filters = isset($this->current_filters) ? $this->current_filters : [];
    
    // Get total count
    $total = $this->count_all();
    
    // Get paginated results
    $query = $this->get_all($per_page, $offset, 'first_name', 'ASC', $filters);
    
    return [
        'data' => $query->result(),
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $page,
        'total_pages' => ceil($total / $per_page)
    ];
}
/**
 * Get candidate details with job information
 */
public function get_candidate_details($candidate_id)
{
    $this->db->select('c.*, j.name as job_name, j.reference_number as job_ref');
    $this->db->from('candidates c');
    $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
    $this->db->where('c.id', $candidate_id);
    $this->db->where('c.removed', 0);
    
    return $this->db->get()->row();
}
protected $current_filters = [];

/**
 * Set current filters for the query
 */
public function set_current_filters($filters)
{
    $this->current_filters = $filters;
}

/**
 * Get current filters
 */
public function get_current_filters()
{
    return $this->current_filters;
}
// Add this helper method to get recruiter ID
private function get_recruiter_id()
{
    $login_data = $this->session->userdata('login');
    if (!empty($login_data['recruiter'])) {
        return $login_data['recruiter']['id'];
    }
    return null;
}

   public function count_all() 
{
    $this->db->from($this->table);
    $this->db->where('removed', 0);

    // ADD THIS: Apply the same recruiter filter
    $recruiter_id = $this->get_recruiter_id();
    if ($recruiter_id) {
        $this->db->where('assigned_agent_id', $recruiter_id);
    }

    return $this->db->count_all_results();
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
            $agency_id = $recruiter['agency_id'] ?? null;
            
            if ($agency_id) {
                $this->db->join('candidate_agencies ca', 'ca.candidate_id = candidates.id', 'inner');
                $this->db->where('ca.agency_id', $agency_id);
            }
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
    $month = date('m');
    
    // Get the latest reference number for this year/month
    $this->db->select('reference_number')
             ->from('candidates')
             ->where('YEAR(created_at)', $year)
             ->where('MONTH(created_at)', $month)
             ->order_by('id', 'DESC')
             ->limit(1);
    
    $last_ref = $this->db->get()->row();
    
    if ($last_ref && !empty($last_ref->reference_number)) {
        // Extract sequence number from last reference
        $pattern = '/^' . $prefix . '-' . $year . $month . '-(\d+)$/';
        if (preg_match($pattern, $last_ref->reference_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        } else {
            // If pattern doesn't match, start from 1
            $sequence = 1;
        }
    } else {
        // First candidate for this month
        $sequence = 1;
    }
    
    return $prefix . '-' . $year . $month . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
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

   

    // ========== UUID METHODS ==========
    
    /**
     * Generate a UUID v4
     */
    public function generate_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Get candidate by UUID
     */
    public function get_by_uuid($uuid)
    {
        return $this->db->where('uuid', $uuid)
                       ->where('removed', 0)
                       ->get($this->table)
                       ->row();
    }
    
    /**
     * Get candidate by ID or UUID (smart method)
     */
    public function get_candidate($identifier)
    {
        // Check if identifier is UUID (36 characters with hyphens)
        if (strlen($identifier) == 36 && strpos($identifier, '-') !== false) {
            return $this->get_candidate_by_uuid($identifier);
        } else {
            // Assume it's numeric ID
            return $this->get_candidate_by_id($identifier);
        }
    }
    
    /**
     * Get candidate by UUID with all relationships
     */
    public function get_candidate_by_uuid($uuid)
    {
        $this->db->select('c.*, 
            CONCAT(r.first_name, " ", r.last_name) as assigned_recruiter_name,
            a.name as agency_name,
            j.name as job_name,
            j.reference_number as job_reference')
                 ->from('candidates c')
                 ->join('recruiters r', 'r.id = c.assigned_agent_id', 'left')
                 ->join('agencies a', 'a.id = c.agency_id', 'left')
                 ->join('mod_jobs j', 'j.id = c.job_id', 'left')
                 ->where('c.uuid', $uuid)
                 ->where('c.removed', 0);
        
        return $this->db->get()->row();
    }
    
    /**
     * Get candidate by ID with all relationships
     */
    public function get_candidate_by_id($id)
    {
        $this->db->select('c.*, 
            CONCAT(r.first_name, " ", r.last_name) as assigned_recruiter_name,
            a.name as agency_name,
            j.name as job_name,
            j.reference_number as job_reference')
                 ->from('candidates c')
                 ->join('recruiters r', 'r.id = c.assigned_agent_id', 'left')
                 ->join('agencies a', 'a.id = c.agency_id', 'left')
                 ->join('mod_jobs j', 'j.id = c.job_id', 'left')
                 ->where('c.id', $id)
                 ->where('c.removed', 0);
        
        return $this->db->get()->row();
    }
    
    /**
     * Override create method to generate UUID
     */
    public function create($data, $table = false)
    {
        // Generate UUID for new candidate
        $data['uuid'] = $this->generate_uuid();
        
        // Use the provided table or default to $this->table
        $target_table = $table ? $table : $this->table;
        
        $result = $this->db->insert($target_table, $data);
        
        if ($result) {
            $id = $this->db->insert_id();
            
            // Return both ID and UUID
            $record = $this->get_by_id($id);
            $record->uuid = $data['uuid'];
            
            return $id;
        } else {
            $error = $this->db->error();
            return false;
        }
    }
    
    /**
     * Check if UUID exists
     */
    public function uuid_exists($uuid)
    {
        return $this->db->where('uuid', $uuid)
                       ->where('removed', 0)
                       ->count_all_results($this->table) > 0;
    }
    

}