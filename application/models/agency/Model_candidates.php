<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Model_candidates extends CRUD_Model
{
    protected $table = 'candidates';

public function selects()
{
    $this->db->distinct();
    
    // Get current agency ID
    $agency_id = $this->get_current_agency_id();
    
    // Select fields
    $this->db->select('candidates.id, candidates.enabled, candidates.reference_number, candidates.first_name, candidates.last_name, candidates.email, candidates.phone, candidates.status, candidates.application_date, mod_jobs.name as job_name, candidates.agency_id, candidates.job_id, candidates.onboarding_stage, candidates.stage_under_review, candidates.stage_submitted_to_hm, candidates.stage_requested_docs, candidates.stage_position_offered, candidates.onboarding_completed_at');
    
    // Join with jobs table
    $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
    
    // ✅ CRITICAL FIX: Join with candidate_agencies to filter by agency assignment
    if ($agency_id) {
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = candidates.id', 'inner');
        $this->db->where('ca.agency_id', $agency_id);
    }
    
    // Universal filters
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
     * Update onboarding stage
     */
    public function update_onboarding_stage($candidate_id, $stage, $value)
    {
        $update_data = array(
            $stage => $value,
            'updated_at' => date('Y-m-d H:i:s')
        );

        // If marking a stage as completed, update the timestamp
        if ($value == 1) {
            $stage_timestamp_field = $stage . '_at';
            $update_data[$stage_timestamp_field] = date('Y-m-d H:i:s');
        }

        // Update onboarding stage based on progress
        $this->update_onboarding_progress($candidate_id);

        return $this->db->where('id', $candidate_id)->update($this->table, $update_data);
    }

    /**
     * Calculate and update overall onboarding progress
     */
    private function update_onboarding_progress($candidate_id)
    {
        $candidate = $this->get_candidate_details($candidate_id);
        
        if (!$candidate) return;

        $stages = [
            'stage_under_review',
            'stage_submitted_to_hm', 
            'stage_requested_docs',
            'stage_position_offered'
        ];

        $completed_stages = 0;
        $current_stage = 'not_started';

        foreach ($stages as $index => $stage) {
            if ($candidate->$stage == 1) {
                $completed_stages++;
                $current_stage = $stage;
            } else {
                // Found the first incomplete stage
                $current_stage = $stage;
                break;
            }
        }

        // If all stages are completed
        if ($completed_stages == 4) {
            $current_stage = 'completed';
            $this->db->where('id', $candidate_id)->update($this->table, [
                'onboarding_completed_at' => date('Y-m-d H:i:s')
            ]);
        }

        $progress_percentage = ($completed_stages / 4) * 100;

        $this->db->where('id', $candidate_id)->update($this->table, [
            'onboarding_stage' => $current_stage,
            'onboarding_progress' => $progress_percentage
        ]);
    }

    /**
     * Get onboarding statistics for dashboard
     */
    public function get_onboarding_stats($agency_id = null)
    {
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id);
        }

        $this->db->select('
            COUNT(*) as total_candidates,
            SUM(stage_under_review) as under_review_count,
            SUM(stage_submitted_to_hm) as submitted_hm_count,
            SUM(stage_requested_docs) as requested_docs_count,
            SUM(stage_position_offered) as position_offered_count,
            SUM(onboarding_stage = "completed") as completed_count
        ');
        
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        
        return $this->db->get($this->table)->row();
    }

    /**
     * Get agency by ID (for dropdown)
     */
    public function get_agency_by_id($agency_id) {
        if (empty($agency_id)) {
            return false;
        }
        
        $this->db->select('id, name');
        $this->db->from('agencies');
        $this->db->where('id', $agency_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        
        return $this->db->get();
    }

    /**
     * Get jobs by agency ID
     */
    public function get_jobs_by_agency($agency_id) {
        if (empty($agency_id)) {
            return false;
        }
        
        $this->db->select('id, name, reference_number');
        $this->db->from('mod_jobs');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        
        return $this->db->get();
    }

    /**
     * Get agency agents by agency ID
     */
    public function get_agency_agents_by_agency($agency_id) {
        if (empty($agency_id)) {
            return false;
        }
        
        // Try to get from agency_staff table first
        $this->db->select('id, first_name, last_name, email');
        $this->db->from('agency_staff');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('first_name', 'ASC');
        
        $result = $this->db->get();
        
        // If no agency_staff found, try recruiters table
        if ($result->num_rows() === 0) {
            $this->db->select('id, first_name, last_name, email');
            $this->db->from('recruiters');
            $this->db->where('agency_id', $agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $this->db->order_by('first_name', 'ASC');
            
            $result = $this->db->get();
        }
        
        return $result;
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

   public function get_candidate_details($candidateId){
    if (!$candidateId) return null;
    
    try {
        $agency_id = $this->get_current_agency_id();
        
        $this->db->select('c.*');
        $this->db->from('candidates c');
        $this->db->where('c.id', $candidateId);
        $this->db->where('c.enabled', 1);
        $this->db->where('c.removed', 0);
        
        // ✅ Check both primary agency AND pivot table assignments
        if ($agency_id) {
            $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
            $this->db->where('ca.agency_id', $agency_id);
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

    /**
 * Complete onboarding process
 */
public function complete_onboarding($candidate_id) {
    $update_data = array(
        'onboarding_stage' => 'completed',
        'onboarding_progress' => 100,
        'onboarding_completed_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    );

    return $this->db->where('id', $candidate_id)->update($this->table, $update_data);
}
}