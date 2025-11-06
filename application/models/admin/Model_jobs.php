<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_jobs extends CRUD_Model
{
    protected $table = 'mod_jobs';

    public function main_selects() {
        $this->db->select([
            'mod_jobs.*',
            'agencies.name AS agency_name',
            'mod_industries.name AS industry_name'
        ]);
    }

    public function joins()
    {
        $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
        $this->db->join('mod_industries', 'mod_industries.id = mod_jobs.industry_id', 'left');
    }

    public function get_agency_options($user_agency_id = null)
    {
        $this->db->select('id, name');
        $this->db->from('agencies');
        $this->db->where('removed', 0);
        if (!empty($user_agency_id)) {
            $this->db->where('id', $user_agency_id);
        } else {
            $this->db->where('enabled', 1);
        }
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_industry_options()
    {
        $this->db->select('id, name');
        $this->db->from('mod_industries');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_skill_options()
    {
        $this->db->select('id, name');
        $this->db->from('mod_job_skills');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_qualification_options()
    {
        $this->db->select('id, name');
        $this->db->from('mod_job_qualifications');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name', 'ASC');
        return $this->db->get();
    }

    public function get_job_skills($job_id)
    {
        if (empty($job_id)) return [];
        $this->db->select('skill_id');
        $this->db->from('pivot_job_skills');
        $this->db->where('job_id', $job_id);
        return array_column($this->db->get()->result_array(), 'skill_id');
    }

    public function get_job_qualifications($job_id)
    {
        if (empty($job_id)) return [];
        $this->db->select('qualification_id');
        $this->db->from('pivot_job_qualifications');
        $this->db->where('job_id', $job_id);
        return array_column($this->db->get()->result_array(), 'qualification_id');
    }

    public function is_unique_reference($reference, $id = "")
    {
        $this->db->from($this->table);
        $this->db->where('reference_number', $reference);
        $this->db->where('removed', 0);
        if (!empty($id)) {
            $this->db->where('id !=', $id);
        }
        return $this->db->count_all_results() == 0;
    }

    // Admin-specific methods
    public function get_admins_all()
    {
        $this->db->select('id, name, email');
        $this->db->from('admins');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        return $this->db->get();
    }

    public function get_admins($job_id)
    {
        if (empty($job_id)) return [];
        return [];
    }

    // ✅ UPDATED: Save job medical requirements instead of user medical data
    public function save_job_medical_requirements($job_id, $medical_data)
    {
        log_message('debug', '🏥 === SAVE_JOB_MEDICAL_REQUIREMENTS START ===');
        log_message('debug', '🏥 Job ID: ' . $job_id);
        log_message('debug', '🏥 Medical data keys: ' . implode(', ', array_keys($medical_data)));
        
        try {
            // Prepare medical requirements data
            $medical_requirements_data = [
                'job_id' => $job_id,
                'medical_requirements' => $medical_data['medical_requirements'] ?? null,
                'fitness_level' => $medical_data['fitness_level'] ?? 'medium',
                'physical_demands' => $medical_data['physical_demands'] ?? null,
                'health_screening_required' => !empty($medical_data['health_screening_required']) ? 1 : 0,
                'drug_test_required' => !empty($medical_data['drug_test_required']) ? 1 : 0,
                'vaccination_required' => !empty($medical_data['vaccination_required']) ? 1 : 0,
                'specific_vaccinations' => $medical_data['specific_vaccinations'] ?? null,
                'medical_certificate_required' => !empty($medical_data['medical_certificate_required']) ? 1 : 0,
                'work_environment' => $medical_data['work_environment'] ?? null,
                'hazard_exposures' => $medical_data['hazard_exposures'] ?? null,
                'ppe_requirements' => $medical_data['ppe_requirements'] ?? null,
                'enabled' => 1,
                'removed' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Check if medical requirements already exist for this job
            $existing = $this->get_job_medical_requirements($job_id);
            
            if ($existing) {
                // Update existing medical requirements
                $this->db->where('id', $existing->id)
                        ->update('mod_job_medical_requirements', $medical_requirements_data);
                $medical_id = $existing->id;
                log_message('debug', '🏥 Medical requirements updated with ID: ' . $medical_id);
            } else {
                // Insert new medical requirements
                $this->db->insert('mod_job_medical_requirements', $medical_requirements_data);
                $medical_id = $this->db->insert_id();
                log_message('debug', '🏥 Medical requirements inserted with ID: ' . $medical_id);
            }

            log_message('debug', '🏥 === SAVE_JOB_MEDICAL_REQUIREMENTS SUCCESS ===');
            return $medical_id;
            
        } catch (Exception $e) {
            log_message('error', '❌ Save job medical requirements error: ' . $e->getMessage());
            return false;
        }
    }

    // ✅ UPDATED: Get job medical requirements (single object)
    public function get_job_medical_requirements($job_id)
    {
        $this->db->select('*')
                 ->from('mod_job_medical_requirements')
                 ->where('job_id', $job_id)
                 ->where('removed', 0)
                 ->where('enabled', 1)
                 ->limit(1);
        
        return $this->db->get()->row(); // ✅ Returns a single object or null
    }

    // ✅ UPDATED: Save from template with medical requirements
    public function save_from_template($template_id, $job_data, $form_data = [])
    {
        log_message('debug', '🔍 === MODEL_JOBS SAVE_FROM_TEMPLATE START ===');
        
        try {
            $this->db->trans_start();

            // ✅ Ensure pay_cycle and site are included in job_data
            $job_data['pay_cycle'] = $job_data['pay_cycle'] ?? '';
            $job_data['site'] = $job_data['site'] ?? '';
            
            log_message('debug', '📦 Job data being saved:');
            log_message('debug', '📦 - Name: ' . ($job_data['name'] ?? 'N/A'));
            log_message('debug', '📦 - Site: ' . ($job_data['site'] ?? 'N/A')); 
            log_message('debug', '📦 - Pay Cycle: ' . ($job_data['pay_cycle'] ?? 'N/A'));
            log_message('debug', '📦 - Location: ' . ($job_data['location'] ?? 'N/A'));

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

    // ✅ REMOVED: Old user medical data methods
    // - save_job_medical_details() 
    // - get_job_medical_details()
    // - update_job_medical_details()

    private function save_pivot_data($job_id, $items, $pivot_table, $link_field) {
        log_message('debug', '📌 === SAVE_PIVOT_DATA START ===');
        log_message('debug', '📌 Job ID: ' . $job_id);
        log_message('debug', '📌 Pivot table: ' . $pivot_table);
        log_message('debug', '📌 Link field: ' . $link_field);
        log_message('debug', '📌 Items to save: ' . (is_array($items) ? implode(', ', $items) : 'NOT ARRAY'));

        if (empty($items)) {
            log_message('debug', '📌 No items to save');
            return;
        }

        $pivot_data = [];
        $valid_items = 0;
        
        foreach ($items as $item_id) {
            if (is_numeric($item_id)) {
                // ✅ FIX: Only include columns that exist in the pivot tables
                $pivot_item = [
                    'job_id' => $job_id,
                    $link_field => (int)$item_id
                ];
                
                // ✅ FIX: Only add created_at if the table has this column
                // Check if pivot table has created_at column
                $table_columns = $this->db->list_fields($pivot_table);
                  log_message('debug', '🔍 Table ' . $pivot_table . ' columns: ' . implode(', ', $table_columns));
                if (in_array('created_at', $table_columns)) {
                    $pivot_item['created_at'] = date('Y-m-d H:i:s');
                }
                
                $pivot_data[] = $pivot_item;
                $valid_items++;
                log_message('debug', '📌 Added pivot: job_id=' . $job_id . ', ' . $link_field . '=' . $item_id);
            } else {
                log_message('debug', '📌 Skipping non-numeric item: ' . $item_id);
            }
        }

        log_message('debug', '📌 Valid items: ' . $valid_items);
        log_message('debug', '📌 Pivot data count: ' . count($pivot_data));

        if (!empty($pivot_data)) {
            // Delete existing pivots first
            log_message('debug', '📌 Deleting existing pivots for job_id: ' . $job_id);
            $this->db->delete($pivot_table, ['job_id' => $job_id]);
            
            // Insert new
            log_message('debug', '📌 Inserting ' . count($pivot_data) . ' records into ' . $pivot_table);
            $insert_result = $this->db->insert_batch($pivot_table, $pivot_data);
            
            if ($insert_result) {
                log_message('debug', '✅ Successfully inserted ' . count($pivot_data) . ' records into ' . $pivot_table);
            } else {
                $error = $this->db->error();
                log_message('error', '❌ Failed to insert into ' . $pivot_table . ': ' . $error['message']);
                throw new Exception('Failed to insert into ' . $pivot_table . ': ' . $error['message']);
            }
        } else {
            log_message('debug', '📌 No pivot data to insert');
        }
        
        log_message('debug', '📌 === SAVE_PIVOT_DATA END ===');
    }
}