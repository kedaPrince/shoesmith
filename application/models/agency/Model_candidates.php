<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Model_candidates extends CRUD_Model
{
    protected $table = 'candidates';

    public function selects()
    {
        $this->db->distinct();
        
        // CORRECTED: Clean field selection without duplicate table references
        $this->db->select('candidates.id, candidates.enabled, candidates.reference_number, candidates.first_name, candidates.last_name, candidates.email, candidates.phone, candidates.status, candidates.application_date, mod_jobs.name as job_name, candidates.agency_id');
        
        // Join with jobs table to get job names
        $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
        
        // Apply agency filter for agency staff - ENSURES ONLY CURRENT AGENCY CANDIDATES
        $agency_id = $this->get_current_agency_id();
        if ($agency_id) {
            $this->db->where('candidates.agency_id', (int)$agency_id);
            log_message('debug', 'Model: Filtering candidates for agency_id: ' . $agency_id);
        } else {
            log_message('error', 'Model: No agency_id found for filtering candidates');
        }
        
        // Only get enabled records
        $this->db->where('candidates.enabled', 1);
        $this->db->where('candidates.removed', 0);
    }

    /**
     * Get current agency ID from session
     */
    private function get_current_agency_id()
    {
        $ci = &get_instance();
        $login = $ci->session->userdata('login');
        
        if (!empty($login['agency'])) {
            $agency_user = $login['agency'];
            
            // Try agency_id first, then fall back to id
            if (!empty($agency_user['agency_id'])) {
                return $agency_user['agency_id'];
            } elseif (!empty($agency_user['id'])) {
                return $agency_user['id'];
            }
        }
        
        return null;
    }

    /**
     * OVERRIDE get_all to ensure agency filtering
     */
    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
    {
        // Apply agency filter again to be safe
        $agency_id = $this->get_current_agency_id();
        if ($agency_id) {
            $this->db->where('candidates.agency_id', (int)$agency_id);
            log_message('debug', 'Model get_all: Applied agency filter: ' . $agency_id);
        }
        
        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

    /**
     * Get agency by ID (for dropdown)
     */
    public function get_agency_by_id($agency_id){
        try {
            $this->db->select('id, name');
            $this->db->from('agencies');
            $this->db->where('id', $agency_id);
            $this->db->where('enabled', 1);
            return $this->db->get()->result_array();
        } catch (Exception $e) {
            error_log('Error in get_agency_by_id: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get jobs by agency ID
     */
    public function get_jobs_by_agency($agency_id){
        try {
            $this->db->select('id, name as job_title, reference_number as job_reference');
            $this->db->from('mod_jobs');
            $this->db->where('agency_id', $agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $this->db->order_by('name', 'ASC');
            return $this->db->get()->result_array();
        } catch (Exception $e) {
            error_log('Error in get_jobs_by_agency: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get agency agents by agency ID
     */
    public function get_agency_agents_by_agency($agency_id){
        try {
            $this->db->select('id, first_name, last_name, email');
            $this->db->from('agency_staff');
            $this->db->where('agency_id', $agency_id);
            $this->db->where('enabled', 1);
            $this->db->order_by('first_name', 'ASC');
            $this->db->order_by('last_name', 'ASC');
            return $this->db->get()->result_array();
        } catch (Exception $e) {
            error_log('Error in get_agency_agents_by_agency: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Is Unique Email - WITH AGENCY FILTER
     */
    public function is_unique_email($email, $id = ""){
        $agency_id = $this->get_current_agency_id();
        
        $this->db->where('email', $email);
        $this->db->where('enabled', 1);
        
        // Only check uniqueness within the same agency
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id);
        }
        
        if (!empty($id)) {
            $this->db->where('id !=', $id);
        }

        $query = $this->db->get($this->table);
        return $query->num_rows() == 0;
    }

    public function get_agencies_all(){
        try {
            $this->db->select('id, name');
            $this->db->from('agencies');
            $this->db->where('enabled', 1);
            $this->db->order_by('name', 'ASC');
            return $this->db->get()->result_array();
        } catch (Exception $e) {
            error_log('Error in get_agencies_all: ' . $e->getMessage());
            return [];
        }
    }

    public function get_jobs_all(){
        try {
            $agency_id = $this->get_current_agency_id();
            
            $this->db->select('id, name as job_title, reference_number as job_reference');
            $this->db->from('mod_jobs');
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            
            // Only show jobs for current agency
            if ($agency_id) {
                $this->db->where('agency_id', $agency_id);
            }
            
            $this->db->order_by('name', 'ASC');
            return $this->db->get()->result_array();
        } catch (Exception $e) {
            error_log('Error in get_jobs_all: ' . $e->getMessage());
            return [];
        }
    }

    public function get_agency_agents_all(){
        try {
            $agency_id = $this->get_current_agency_id();
            
            $this->db->select('id, first_name, last_name, email');
            $this->db->from('agency_staff');
            $this->db->where('enabled', 1);
            
            if ($agency_id) {
                $this->db->where('agency_id', (int)$agency_id);
            }
            
            $this->db->order_by('first_name', 'ASC');
            $this->db->order_by('last_name', 'ASC');
            
            return $this->db->get()->result_array();
        } catch (Exception $e) {
            error_log('Error in get_agency_agents_all: ' . $e->getMessage());
            return [];
        }
    }

    public function get_candidate_details($candidateId){
        if (!$candidateId) return null;
        
        try {
            $agency_id = $this->get_current_agency_id();
            
            $this->db->select('*');
            $this->db->from('candidates');
            $this->db->where('id', $candidateId);
            $this->db->where('enabled', 1);
            
            // Only allow access to candidates from current agency
            if ($agency_id) {
                $this->db->where('agency_id', $agency_id);
            }
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            error_log('Error in get_candidate_details: ' . $e->getMessage());
            return null;
        }
    }

    public function generate_reference_number(){
        try {
            $prefix = 'CAND';
            $agency_id = $this->get_current_agency_id();
            
            $this->db->select('COUNT(*) as total');
            $this->db->from('candidates');
            $this->db->where('YEAR(created_at)', date('Y'));
            $this->db->where('enabled', 1);
            
            // Count only within current agency
            if ($agency_id) {
                $this->db->where('agency_id', $agency_id);
            }
            
            $result = $this->db->get()->row();
            
            $sequence = ($result->total ?? 0) + 1;
            return $prefix . '-' . date('Y') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            error_log('Error in generate_reference_number: ' . $e->getMessage());
            return $prefix . '-' . date('Y') . '-0001';
        }
    }

    public function log_candidate_activity($logData){
        try {
            if ($this->db->table_exists('candidate_activity_logs')) {
                return $this->db->insert('candidate_activity_logs', $logData);
            }
            return false;
        } catch (Exception $e) {
            error_log('Error in log_candidate_activity: ' . $e->getMessage());
            return false;
        }
    }

    public function create_agent_notification($notificationData){
        try {
            if ($this->db->table_exists('agent_notifications')) {
                return $this->db->insert('agent_notifications', $notificationData);
            }
            return false;
        } catch (Exception $e) {
            error_log('Error in create_agent_notification: ' . $e->getMessage());
            return false;
        }
    }

    public function get_agent($agentId){
        try {
            $agency_id = $this->get_current_agency_id();
            
            $this->db->select('*');
            $this->db->from('agency_staff');
            $this->db->where('id', $agentId);
            $this->db->where('enabled', 1);
            
            // Only allow access to agents from current agency
            if ($agency_id) {
                $this->db->where('agency_id', $agency_id);
            }
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            error_log('Error in get_agent: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get candidate by ID (alias for get_candidate_details)
     */
    public function get_candidate($id){
        return $this->get_candidate_details($id);
    }
}