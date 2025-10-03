<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Model_candidates extends CRUD_Model
{
    protected $table = 'candidates';

    public function selects()
    {
        $this->db->distinct();
        edb_select('id, reference_number, first_name, last_name, email, phone, status, application_date, enabled', $this->table);
        
        // Apply agency filter for agency staff
        $agency_id = loginVar('agency_id', 'agency');
        if ($agency_id) {
            $this->db->where('candidates.agency_id', (int)$agency_id);
        }
        
        // Only get enabled records
        $this->db->where('candidates.enabled', 1);
    }

    /**
     * Is Unique Email
     *
     * Checks if given email already exists in the database.
     * If the id is passed, then it will ignore that entry.
     *
     * @param string $email
     * @param string $id (optional)
     *
     * @return bool
     */
    public function is_unique_email($email, $id = ""){
        $this->db->where('email', $email);
        $this->db->where('enabled', 1);
        
        if (!empty($id)) {
            $this->db->where('id !=', $id);
        }

        $query = $this->db->get($this->table);
        return $query->num_rows() == 0;
    }

 
    // In Model_candidates
    public function get_agencies_all(){
        try {
            $this->db->select('id, name');
            $this->db->from('agencies');
            $this->db->where('enabled', 1);
            $this->db->order_by('name', 'ASC');
            return $this->db->get()->result_array(); // Changed to result_array()
        } catch (Exception $e) {
            error_log('Error in get_agencies_all: ' . $e->getMessage());
            return [];
        }
    }

    public function get_jobs_all(){
        try {
            $this->db->select('id, job_title, job_reference');
            $this->db->from('jobs');
            $this->db->where('enabled', 1);
            $this->db->where('status', 'active');
            $this->db->order_by('job_title', 'ASC');
            return $this->db->get()->result_array(); // Changed to result_array()
        } catch (Exception $e) {
            error_log('Error in get_jobs_all: ' . $e->getMessage());
            return [];
        }
    }

    public function get_agency_agents_all(){
        try {
            $agency_id = loginVar('agency_id', 'agency');
            
            $this->db->select('id, first_name, last_name, email');
            $this->db->from('agency_staff');
            $this->db->where('enabled', 1);
            
            if ($agency_id) {
                $this->db->where('agency_id', (int)$agency_id);
            }
            
            $this->db->order_by('first_name', 'ASC');
            $this->db->order_by('last_name', 'ASC');
            
            return $this->db->get()->result_array(); // Changed to result_array()
        } catch (Exception $e) {
            error_log('Error in get_agency_agents_all: ' . $e->getMessage());
            return [];
        }
    }

    public function get_candidate_details($candidateId){
        if (!$candidateId) return null;
        
        try {
            $this->db->select('*');
            $this->db->from('candidates');
            $this->db->where('id', $candidateId);
            $this->db->where('enabled', 1);
            return $this->db->get()->row();
        } catch (Exception $e) {
            error_log('Error in get_candidate_details: ' . $e->getMessage());
            return null;
        }
    }

    public function generate_reference_number(){
        try {
            $prefix = 'CAND';
            $this->db->select('COUNT(*) as total');
            $this->db->from('candidates');
            $this->db->where('YEAR(created_at)', date('Y'));
            $this->db->where('enabled', 1);
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
            // Check if table exists before inserting
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
            // Check if table exists before inserting
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
            $this->db->select('*');
            $this->db->from('agency_staff');
            $this->db->where('id', $agentId);
            $this->db->where('enabled', 1);
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