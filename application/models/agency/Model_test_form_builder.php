<?php
class Model_test_form_builder extends CRUD_Model {
    protected $table = 'sys_form_schemas';

    public function update(array $data, $whereValue, $whereField = 'id', $table = false) {
        $table = $table ? $table : $this->table;

        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // FIXED: Use proper session checking
        if ($this->session->userdata('agency_id')) {
            $data['agency_id'] = $this->session->userdata('agency_id');
            $data['is_public'] = 0; // Agency-specific forms are not public by default
        }
        
        // Rest of your update logic...
        if (isset($data['schema'])) {
            $schema = json_decode($data['schema'], true);
            // Ensure multi-select has options if dynamic
            foreach ($schema as &$row) {
                if (isset($row['fields'])) {
                    foreach ($row['fields'] as &$field) {
                        if ($field['type'] === 'multiselect' && empty($field['options'])) {
                            // Auto-populate from suggestions if name matches
                            $suggestions = $this->get_field_suggestions();
                            // Logic to match and set options (simplified)
                            $field['options'] = $suggestions['Qualification Fields'] ?? [];
                        }
                    }
                }
            }
            $data['schema'] = json_encode($schema);
        }

        $result = $this->db->update($table, $data, array($whereField => $whereValue));
        
        if (!$result) {
            Anomalies::log('Failed to update from CRUD', $this->db->last_query());
            return false;
        }

        //  SIMPLE FIX: Return the ID we're updating instead of querying for it
        // This prevents the "Attempt to read property 'id' on null" error
        return $whereValue;
    }

    public function get_count() 
    {
        
        $agency_id = $this->session->userdata('agency_id');
        
        $this->db->from($this->table)
                 ->where('removed', 0)
                 ->where('deleted_at IS NULL');

        //  AGENCY FILTERING
        if ($agency_id) {
            $this->db->where("(agency_id = $agency_id OR is_public = 1)");
        } else {
            $this->db->where('is_public', 1);
        }

        return $this->db->count_all_results();
    }

    public function get_all($section = '') 
    {
        
        $agency_id = $this->session->userdata('agency_id');
        
        $this->db->from($this->table)
                 ->where('removed', 0)
                 ->where('deleted_at IS NULL');

        //  AGENCY FILTERING
        if ($agency_id) {
            $this->db->where("(agency_id = $agency_id OR is_public = 1)");
        } else {
            $this->db->where('is_public', 1);
        }

        $this->db->order_by('name', 'ASC');
        
        return $this->db->get();
    }

    public function get_by_id($id, $table = false) 
    {
        if ($table === false) {
            $table = $this->table;
        }
        
        $this->db->where('removed', 0)
                 ->where('deleted_at IS NULL');
                 
        //  ADD AGENCY CHECK FOR SECURITY
        $agency_id = $this->session->userdata('agency_id');
        if ($agency_id) {
            $this->db->where("(agency_id = $agency_id OR is_public = 1)");
        } else {
            $this->db->where('is_public', 1);
        }
        
        return $this->db->where('id', $id)->get($table)->row();
    }

    // Keep if used elsewhere
    public function get_form_data($id) {
        return $this->db
            ->get_where($this->table, ['id' => $id])
            ->result_array();
    }

    // For input types dropdown in quick manage
    public function get_input_types() {
        return [
            ['id' => 'text',           'name' => 'Text'],
            ['id' => 'email',          'name' => 'Email'],
            ['id' => 'number',         'name' => 'Number'],
            ['id' => 'time',           'name' => 'Time'],
            ['id' => 'password',       'name' => 'Password'],
            ['id' => 'textarea',       'name' => 'Textarea'],
            ['id' => 'ckeditor_simple','name' => 'CKEditor Simple'],
            ['id' => 'ckeditor',       'name' => 'CKEditor'],
            ['id' => 'dropdown',       'name' => 'Dropdown'],
            ['id' => 'multiselect',    'name' => 'Multi Select'],
            ['id' => 'radio',          'name' => 'Radio'],
            ['id' => 'checkbox',       'name' => 'Checkbox'],
            ['id' => 'hidden',         'name' => 'Hidden'],
            ['id' => 'date',           'name' => 'Date'],
            ['id' => 'datetime',       'name' => 'Date Time'],
            ['id' => 'multifile',      'name' => 'Multi File Upload'],
            ['id' => 'file',           'name' => 'File Upload'],
            ['id' => 'image',          'name' => 'Image Upload'],
            ['id' => 'multiimage',     'name' => 'Multi Image Upload'],
            ['id' => 'button',         'name' => 'Button'],
        ];
    }

    /**
     * Get all field names from specified tables for dropdown
     */
    public function get_table_fields($tables = []) {
        $fields = [];
        
        $allowed_tables = [
            'mod_jobs',
            'mod_job_qualifications', 
            'mod_job_skills',
            'mod_industries',
            'pivot_job_qualifications',
            'usr_medical_emergency_details', //  KEEP USER MEDICAL TABLE
            'pivot_job_medical_details',     //  KEEP MEDICAL PIVOT TABLE
            'mod_job_medical_requirements'   //  ADD JOB MEDICAL REQUIREMENTS TABLE
        ];
        
        foreach ($allowed_tables as $table) {
            if ($this->db->table_exists($table)) {
                $table_fields = $this->db->field_data($table);
                foreach ($table_fields as $field) {
                    // Skip system fields
                    if (in_array($field->name, ['id', 'created_at', 'updated_at', 'deleted_at', 'removed', 'enabled'])) {
                        continue;
                    }
                    
                    $fields[] = [
                        'id' => $table . '.' . $field->name,
                        'name' => $table . ' - ' . $field->name . ' (' . $field->type . ')',
                        'table' => $table,
                        'field' => $field->name,
                        'type' => $field->type,
                        'max_length' => $field->max_length,
                        'primary_key' => $field->primary_key
                    ];
                }
            } else {
            }
        }
        
        return $fields;
    }

    /**
     * Get field suggestions based on database structure
     */
    public function get_field_suggestions() {
        $suggestions = [];
        
        $tables = [
            'mod_jobs' => 'Job Fields',
            'mod_job_qualifications' => 'Qualification Fields',
            'mod_job_skills' => 'Skill Fields', 
            'mod_industries' => 'Industry Fields',
            'pivot_job_qualifications' => 'Job Qualifications Pivot',
            'usr_medical_emergency_details' => 'Medical Emergency Fields', //  KEEP USER MEDICAL
            'pivot_job_medical_details' => 'Job Medical Pivot',           //  KEEP MEDICAL PIVOT
            'mod_job_medical_requirements' => 'Job Medical Requirements'  //  ADD JOB MEDICAL REQUIREMENTS
        ];
        
        foreach ($tables as $table => $label) {
            if ($this->db->table_exists($table)) {
                $table_fields = $this->db->list_fields($table);
                $suggestions[$label] = [];
                foreach ($table_fields as $field) {
                    // Skip system fields
                    if (in_array($field, ['id', 'created_at', 'updated_at', 'deleted_at', 'removed', 'enabled'])) {
                        continue;
                    }
                    $suggestions[$label][$table . '.' . $field] = $field;
                }
            } else {
            }
        }
        
        return $suggestions;
    }

    /**
     * Get database field type to map to form input type
     */
    public function get_db_field_type_mapping() {
        return [
            'varchar' => 'text',
            'char' => 'text',
            'text' => 'textarea',
            'longtext' => 'ckeditor',
            'mediumtext' => 'ckeditor',
            'int' => 'number',
            'integer' => 'number',
            'decimal' => 'number',
            'float' => 'number',
            'double' => 'number',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'datetime',
            'time' => 'time',
            'year' => 'number',
            'tinyint' => 'checkbox',
            'smallint' => 'number',
            'mediumint' => 'number',
            'bigint' => 'number',
            'enum' => 'dropdown',
            'set' => 'multiselect',
            'blob' => 'textarea',
            'longblob' => 'textarea'
        ];
    }

    /**
     * Get medical field recommendations (BOTH user medical AND job medical requirements)
     */
    public function get_medical_field_recommendations() {
        return [
            //  KEEP EXISTING USER MEDICAL FIELDS
            'usr_medical_emergency_details.next_of_kin_first_name' => [
                'type' => 'text',
                'label' => 'Next of Kin First Name',
                'placeholder' => 'Enter first name',
                'required' => true
            ],
            'usr_medical_emergency_details.next_of_kin_last_name' => [
                'type' => 'text', 
                'label' => 'Next of Kin Last Name',
                'placeholder' => 'Enter last name',
                'required' => true
            ],
            'usr_medical_emergency_details.next_of_kin_relation' => [
                'type' => 'text',
                'label' => 'Relationship',
                'placeholder' => 'e.g., Spouse, Parent, Child'
            ],
            'usr_medical_emergency_details.next_of_kin_phone_number' => [
                'type' => 'text',
                'label' => 'Emergency Phone Number',
                'placeholder' => 'Enter phone number'
            ],
            'usr_medical_emergency_details.medical_history' => [
                'type' => 'textarea',
                'label' => 'Medical History',
                'placeholder' => 'Enter relevant medical history'
            ],
            'usr_medical_emergency_details.medical_problems' => [
                'type' => 'textarea',
                'label' => 'Medical Problems', 
                'placeholder' => 'Enter any known medical problems'
            ],
            'usr_medical_emergency_details.mandatory_medication_taken' => [
                'type' => 'textarea',
                'label' => 'Mandatory Medications',
                'placeholder' => 'List mandatory medications'
            ],
            'usr_medical_emergency_details.allergies' => [
                'type' => 'textarea',
                'label' => 'Allergies',
                'placeholder' => 'List any allergies'
            ],
            'usr_medical_emergency_details.medical_aid_name' => [
                'type' => 'text',
                'label' => 'Medical Aid Name',
                'placeholder' => 'Enter medical aid provider'
            ],
            'usr_medical_emergency_details.medical_aid_member_number' => [
                'type' => 'text',
                'label' => 'Medical Aid Number',
                'placeholder' => 'Enter member number'
            ],
            'usr_medical_emergency_details.medical_aid_plan' => [
                'type' => 'text',
                'label' => 'Medical Aid Plan',
                'placeholder' => 'Enter plan name'
            ],
            'usr_medical_emergency_details.family_doctor' => [
                'type' => 'text',
                'label' => 'Family Doctor',
                'placeholder' => 'Enter doctor name'
            ],
            'usr_medical_emergency_details.doctor_phone_number' => [
                'type' => 'text',
                'label' => 'Doctor Phone',
                'placeholder' => 'Enter doctor phone number'
            ],
            
            // ✅ ADD NEW JOB MEDICAL REQUIREMENTS FIELDS
            'mod_job_medical_requirements.medical_requirements' => [
                'type' => 'textarea',
                'label' => 'Medical Requirements',
                'placeholder' => 'Describe specific medical requirements for this job',
                'required' => false
            ],
            'mod_job_medical_requirements.fitness_level' => [
                'type' => 'dropdown',
                'label' => 'Required Fitness Level',
                'placeholder' => 'Select fitness level',
                'required' => false,
                'options' => [
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'very_high' => 'Very High'
                ]
            ],
            'mod_job_medical_requirements.physical_demands' => [
                'type' => 'textarea',
                'label' => 'Physical Demands',
                'placeholder' => 'Describe physical demands of the job',
                'required' => false
            ],
            'mod_job_medical_requirements.health_screening_required' => [
                'type' => 'checkbox',
                'label' => 'Health Screening Required',
                'placeholder' => '',
                'required' => false
            ],
            'mod_job_medical_requirements.drug_test_required' => [
                'type' => 'checkbox',
                'label' => 'Drug Test Required',
                'placeholder' => '',
                'required' => false
            ],
            'mod_job_medical_requirements.vaccination_required' => [
                'type' => 'checkbox',
                'label' => 'Vaccination Required',
                'placeholder' => '',
                'required' => false
            ],
            'mod_job_medical_requirements.specific_vaccinations' => [
                'type' => 'textarea',
                'label' => 'Required Vaccinations',
                'placeholder' => 'List specific vaccinations required',
                'required' => false
            ],
            'mod_job_medical_requirements.medical_certificate_required' => [
                'type' => 'checkbox',
                'label' => 'Medical Certificate Required',
                'placeholder' => '',
                'required' => false
            ],
            'mod_job_medical_requirements.work_environment' => [
                'type' => 'textarea',
                'label' => 'Work Environment',
                'placeholder' => 'Describe the work environment',
                'required' => false
            ],
            'mod_job_medical_requirements.hazard_exposures' => [
                'type' => 'textarea',
                'label' => 'Hazard Exposures',
                'placeholder' => 'List potential hazard exposures',
                'required' => false
            ],
            'mod_job_medical_requirements.ppe_requirements' => [
                'type' => 'textarea',
                'label' => 'PPE Requirements',
                'placeholder' => 'List personal protective equipment requirements',
                'required' => false
            ]
        ];
    }
}