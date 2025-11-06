<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_jobs extends CRUD_Model{
    protected $table = 'mod_jobs';

// public function joins(){
//     $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
//     $this->db->select('mod_jobs.agency_id, agencies.name AS agency_name');

//     $this->db->join('mod_industries', 'mod_industries.id = mod_jobs.industry_id', 'left');
//     $this->db->select('mod_industries.name AS industry_name');
// }
public function save_from_template($template_id, $job_data, $form_data = [])
{
    log_message('debug', '🔍 === MODEL_JOBS SAVE_FROM_TEMPLATE START ===');
    
    try {
        $this->db->trans_start();

        // ✅ FIX: Ensure employment_type and industry_id are properly handled
        if (empty($job_data['employment_type'])) {
            $job_data['employment_type'] = 'full-time'; // Default value
        }
        
        if (empty($job_data['industry_id']) || !is_numeric($job_data['industry_id'])) {
            $job_data['industry_id'] = null; // Set to null if invalid
        }
        
        // ✅ FIX: Final HTML cleaning before saving to database
        $text_fields = [
            'name', 'location', 'site', 'pay_cycle', 'description', 
            'contract_type', 'pay_type', 'pay_rate', 'department', 
            'project_overview', 'transport', 'roster', 'accommodation',
            'application_email', 'application_url', 'skills', 'qualifications'
        ];
        
        foreach ($text_fields as $field) {
            if (isset($job_data[$field]) && !empty($job_data[$field])) {
                // Final cleanup to ensure no HTML tags remain
                $job_data[$field] = strip_tags($job_data[$field]);
                $job_data[$field] = preg_replace('/\s+/', ' ', $job_data[$field]); // Normalize whitespace
                $job_data[$field] = trim($job_data[$field]);
            }
        }
        
        log_message('debug', '📦 Final job data before save:');
        log_message('debug', '📦 - Employment Type: ' . $job_data['employment_type']);
        log_message('debug', '📦 - Industry ID: ' . $job_data['industry_id']);
        log_message('debug', '📦 - Description length: ' . strlen($job_data['description'] ?? ''));
        log_message('debug', '📦 - Description sample: ' . substr($job_data['description'] ?? '', 0, 100));

        // Insert the main job record
        $this->db->insert('mod_jobs', $job_data);
        $job_id = $this->db->insert_id();
        
        if (!$job_id) {
            throw new Exception('Failed to insert job record');
        }

        log_message('debug', '✅ Job record inserted with ID: ' . $job_id);

        // Handle skills
        if (!empty($form_data['skills'])) {
            $skills = is_array($form_data['skills']) ? $form_data['skills'] : [$form_data['skills']];
            foreach ($skills as $skill_id) {
                if (!empty($skill_id)) {
                    $this->db->insert('pivot_job_skills', [
                        'job_id' => $job_id,
                        'skill_id' => $skill_id
                    ]);
                }
            }
            log_message('debug', '✅ Skills added: ' . count($skills));
        }

        // Handle qualifications  
        if (!empty($form_data['qualifications'])) {
            $qualifications = is_array($form_data['qualifications']) ? $form_data['qualifications'] : [$form_data['qualifications']];
            foreach ($qualifications as $qualification_id) {
                if (!empty($qualification_id)) {
                    $this->db->insert('pivot_job_qualifications', [
                        'job_id' => $job_id,
                        'qualification_id' => $qualification_id
                    ]);
                }
            }
            log_message('debug', '✅ Qualifications added: ' . count($qualifications));
        }

        // ✅ UPDATED: Handle medical requirements instead of user medical data
        if (!empty($form_data['medical_requirements_data'])) {
            $medical_id = $this->save_job_medical_requirements($job_id, $form_data['medical_requirements_data']);
            if ($medical_id) {
                log_message('debug', '✅ Medical requirements added for job: ' . $job_id . ' with medical ID: ' . $medical_id);
            } else {
                log_message('error', '❌ Failed to save medical requirements for job: ' . $job_id);
            }
        } else {
            log_message('debug', '⚠️ No medical requirements data to save for job: ' . $job_id);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Database transaction failed');
        }

        log_message('debug', '🔍 === MODEL_JOBS SAVE_FROM_TEMPLATE SUCCESS ===');
        return $job_id;

    } catch (Exception $e) {
        $this->db->trans_rollback();
        log_message('error', '❌ Model_jobs save_from_template error: ' . $e->getMessage());
        return false;
    }
}
/**
 * Generate unique reference number for job
 */
public function generate_reference_number() {
    $prefix = 'JOB';
    $year = date('Y');
    
    // Get the highest reference number for this year
    $this->db->select('reference_number');
    $this->db->like('reference_number', $prefix . '-' . $year, 'after');
    $this->db->order_by('reference_number', 'DESC');
    $this->db->limit(1);
    $query = $this->db->get($this->table);
    
    if ($query->num_rows() > 0) {
        $last_ref = $query->row()->reference_number;
        // Extract the number part and increment
        preg_match('/-(\d+)$/', $last_ref, $matches);
        $next_num = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
    } else {
        $next_num = 1;
    }
    
    // Format with leading zeros
    $reference = $prefix . '-' . $year . '-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
    
    // Double check it's unique
    $counter = 0;
    while ($this->db->where('reference_number', $reference)->count_all_results($this->table) > 0 && $counter < 100) {
        $next_num++;
        $reference = $prefix . '-' . $year . '-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
        $counter++;
    }
    
    if ($counter >= 100) {
        throw new Exception('Unable to generate unique reference number after 100 attempts');
    }
    
    return $reference;
}
     /**
 * Override get_all to support agency filtering
 */
public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null, $agency_id = null){
    // Apply agency filter if provided
    if (!empty($agency_id)) {
        $this->db->where('mod_jobs.agency_id', $agency_id);
        log_message('debug', 'Model filtering jobs by agency_id: ' . $agency_id);
    }
    
    return parent::get_all($limit, $offset, $sort_by, $sort_order);
}
/**
 * Override main_selects to include candidate count as calculated field
 */
public function main_selects() {
    // First call parent to get the basic fields
    parent::main_selects();
    
    // Add candidate count as a calculated field
    $this->db->select('(
        SELECT COUNT(*) 
        FROM candidate_jobs 
        WHERE candidate_jobs.job_id = mod_jobs.id
    ) as candidate_count', false);
    
    // ✅ FIX: Ensure employment_type is selected
    $this->db->select('mod_jobs.employment_type, mod_jobs.industry_id, mod_jobs.agency_id');
}

public function joins(){
    $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
    $this->db->select('mod_jobs.agency_id, agencies.name AS agency_name');

    $this->db->join('mod_industries', 'mod_industries.id = mod_jobs.industry_id', 'left');
    $this->db->select('mod_industries.name AS industry_name, mod_jobs.industry_id');
    
    // ✅ FIX: Ensure employment_type is included
    $this->db->select('mod_jobs.employment_type');
}
    public function get_agency_options($user_agency_id = null){
        $this->db->select('id, name');
        $this->db->from('agencies');
        $this->db->where('removed', 0);
        
        log_message('debug', 'Filtering agencies with user_agency_id: ' . $user_agency_id);
        
        // Filter by user's agency if provided
        if (!empty($user_agency_id)) {
            $this->db->where('id', $user_agency_id);
            // Don't check enabled status for user's own agency
            log_message('debug', 'Filtering to show only agency ID: ' . $user_agency_id);
        } else {
            // For general listing, only show enabled agencies
            $this->db->where('enabled', 1);
            log_message('debug', 'Showing all enabled agencies');
        }
        
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    // Other methods remain the same...
    public function get_industry_options(){
        $this->db->select('id, name');
        $this->db->from('mod_industries');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_skill_options(){
        $this->db->select('id, name');
        $this->db->from('mod_job_skills');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_qualification_options(){
        $this->db->select('id, name');
        $this->db->from('mod_job_qualifications');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_job_skills($job_id){
        if (empty($job_id)) return [];
        $this->db->select('skill_id');
        $this->db->from('pivot_job_skills');
        $this->db->where('job_id', $job_id);
        $query = $this->db->get();
        return array_column($query->result_array(), 'skill_id');
    }

    public function get_job_qualifications($job_id){
        if (empty($job_id)) return [];
        $this->db->select('qualification_id');
        $this->db->from('pivot_job_qualifications');
        $this->db->where('job_id', $job_id);
        $query = $this->db->get();
        return array_column($query->result_array(), 'qualification_id');
    }

    public function is_unique_reference($reference, $id = "") {
        $this->db->from($this->table);
        $this->db->where('reference_number', $reference);
        $this->db->where('removed', 0);
        if (!empty($id)) {
            $this->db->where('id !=', $id);
        }
        return $this->db->count_all_results() == 0;
    }
}