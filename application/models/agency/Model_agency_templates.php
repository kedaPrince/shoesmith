<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Model_agency_templates extends CI_Model 
{
    protected $table = 'agency_custom_templates';

    public function __construct() 
    {
        parent::__construct();
        $this->load->model('agency/Model_template_sections');
    }

    public function save_agency_template($agency_id, $template_name, $description, $sections = []) 
    {
        // Start transaction
        $this->db->trans_start();

        try {
            // Debug: Check what we're receiving
            error_log("=== MODEL SAVE DEBUG ===");
            error_log("Sections received in model: " . print_r($sections, true));
            error_log("Sections type: " . gettype($sections));
            error_log("Is array: " . (is_array($sections) ? 'YES' : 'NO'));

            // Ensure sections is an array
            if (!is_array($sections)) {
                error_log("Sections is not array, converting...");
                if (is_string($sections)) {
                    $sections = json_decode($sections, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        error_log("JSON decode error in model: " . json_last_error_msg());
                        $sections = [];
                    }
                } else {
                    $sections = [];
                }
            }

            error_log("Sections after model processing: " . print_r($sections, true));
            error_log("Sections count in model: " . count($sections));

            // ALWAYS CREATE NEW TEMPLATE - don't check for existing
            // Create new template
            $this->db->insert($this->table, [
                'agency_id' => $agency_id,
                'template_name' => $template_name,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s'),
                'enabled' => 1
            ]);
            $template_id = $this->db->insert_id();

            // Insert new sections
            if (!empty($sections) && is_array($sections)) {
                $section_data = [];
                $order = 1;
                
                foreach ($sections as $section_id) {
                    // Ensure section_id is valid and not empty
                    if (!empty($section_id) && is_numeric($section_id)) {
                        $section_data[] = [
                            'agency_template_id' => $template_id,
                            'section_id' => (int)$section_id,
                            'sort_order' => $order,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $order++;
                    }
                }
                
                if (!empty($section_data)) {
                    error_log("Inserting section data: " . print_r($section_data, true));
                    $this->db->insert_batch('agency_template_sections', $section_data);
                }
            }

            // Complete transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }

            return $template_id;

        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }

    public function get_agency_template($agency_id) 
    {
        return $this->db->where('agency_id', $agency_id)
                       ->where('enabled', 1)
                       ->order_by('created_at', 'DESC')
                       ->limit(1)
                       ->get($this->table)
                       ->row();
    }

    public function get_agency_templates($agency_id) 
    {
        return $this->db->where('agency_id', $agency_id)
                       ->where('enabled', 1)
                       ->order_by('created_at', 'DESC')
                       ->get($this->table)
                       ->result();
    }

    public function get_template_by_id($template_id) 
    {
        return $this->db->where('id', $template_id)
                       ->where('enabled', 1)
                       ->get($this->table)
                       ->row();
    }

    public function get_template_with_sections($template_id) 
    {
        error_log("=== GET TEMPLATE WITH SECTIONS DEBUG ===");
        error_log("Looking for template ID: " . $template_id);
        
        $template = $this->db->where('id', $template_id)
                           ->get($this->table)
                           ->row();
        
        if (!$template) {
            error_log("Template not found for ID: " . $template_id);
            return null;
        }

        error_log("Found template: " . $template->template_name);

        // Get the section associations from agency_template_sections - NO ALIAS
        $template_sections = $this->db->select('agency_template_sections.section_id, agency_template_sections.sort_order')
                                     ->from('agency_template_sections')
                                     ->where('agency_template_sections.agency_template_id', $template_id)
                                     ->order_by('agency_template_sections.sort_order', 'ASC')
                                     ->get()
                                     ->result();

        error_log("Found " . count($template_sections) . " section associations in agency_template_sections");

        // Get the full section data for each section ID
        $template->sections = [];
        foreach ($template_sections as $ts) {
            error_log("Looking for section ID: " . $ts->section_id);
            
            $section = $this->db->where('id', $ts->section_id)
                               ->where('enabled', 1)
                               ->get('mod_template_sections')
                               ->row();
            
            if ($section) {
                error_log("Found section: " . $section->name . " (ID: " . $section->id . ")");
                // Create a clean section object with the correct ID
                $clean_section = new stdClass();
                $clean_section->id = $section->id; // This is the crucial part - use the actual section ID
                $clean_section->name = $section->name;
                $clean_section->code = $section->code;
                $clean_section->description = $section->description;
                $clean_section->section_type = $section->section_type;
                $clean_section->schema_id = $section->schema_id;
                $clean_section->sort_order = $ts->sort_order;
                $clean_section->enabled = $section->enabled;
                
                $template->sections[] = $clean_section;
            } else {
                error_log("Section not found for ID: " . $ts->section_id);
            }
        }

        error_log("Final sections count: " . count($template->sections));
        foreach ($template->sections as $s) {
            error_log(" - Section: " . $s->id . " - " . $s->name);
        }

        return $template;
    }

    public function get_template_with_sections_by_agency($agency_id) 
    {
        $template = $this->get_agency_template($agency_id);
        if (!$template) return null;

        return $this->get_template_with_sections($template->id);
    }

    public function update_agency_template($template_id, $template_name, $description, $sections = []) 
    {
        // Start transaction
        $this->db->trans_start();

        try {
            // Update template
            $this->db->where('id', $template_id);
            $this->db->update($this->table, [
                'template_name' => $template_name,
                'description' => $description,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Delete existing sections
            $this->db->delete('agency_template_sections', [
                'agency_template_id' => $template_id
            ]);

            // Insert new sections
            if (!empty($sections) && is_array($sections)) {
                $section_data = [];
                $order = 1;
                
                foreach ($sections as $section_id) {
                    if (!empty($section_id) && is_numeric($section_id)) {
                        $section_data[] = [
                            'agency_template_id' => $template_id,
                            'section_id' => (int)$section_id,
                            'sort_order' => $order,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $order++;
                    }
                }
                
                if (!empty($section_data)) {
                    $this->db->insert_batch('agency_template_sections', $section_data);
                }
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }

            return $template_id;

        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }

    public function render_agency_template($agency_id) 
    {
        $template = $this->get_template_with_sections_by_agency($agency_id);
        
        if (!$template || empty($template->sections)) {
            return '<div class="alert alert-warning">No template found for this agency or template has no sections.</div>';
        }

        $html = '<div class="agency-template-preview">';
        $html .= '<h2>' . htmlspecialchars($template->template_name) . '</h2>';
        
        if (!empty($template->description)) {
            $html .= '<p class="text-muted">' . htmlspecialchars($template->description) . '</p>';
        }

        foreach ($template->sections as $section) {
            $html .= $this->render_section($section);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render individual section
     */
    private function render_section($section) 
    {
        $html = '<div class="template-section-preview mb-4 p-3 border rounded">';
        $html .= '<h4>' . htmlspecialchars($section->name) . '</h4>';
        $html .= '<div class="section-meta small text-muted mb-2">';
        $html .= 'Type: ' . htmlspecialchars($section->section_type) . ' | ';
        $html .= 'Order: ' . $section->sort_order;
        $html .= '</div>';
        
        // Try to render the form schema if available
        $form_html = $this->render_section_form($section->schema_id);
        if ($form_html) {
            $html .= $form_html;
        } else {
            $html .= '<div class="alert alert-info">Section form content would be rendered here</div>';
        }
        
        $html .= '</div>';

        return $html;
    }

    private function render_section_form($schema_id) 
    {
        if (!$schema_id) return '';

        try {
            // Get schema data
            $schema_data = $this->db->select('schema, scripts, styling')
                                  ->from('sys_form_schemas')
                                  ->where('id', $schema_id)
                                  ->where('enabled', 1)
                                  ->get()
                                  ->row();

            if (!$schema_data) return '';

            $schema = json_decode($schema_data->schema, true);
            if (json_last_error() !== JSON_ERROR_NONE) return '';

            // Load form builder library
            $this->load->library('Form_builder');
            
            $styling = json_decode($schema_data->styling ?? '{}', true);
            $scripts = json_decode($schema_data->scripts ?? '[]', true);

            $form = $this->form_builder::make()
                ->set_schema($schema)
                ->set_styling($styling)
                ->set_scripts($scripts)
                ->make_form([]);

            return $form->form_view;

        } catch (Exception $e) {
            error_log('Form render error: ' . $e->getMessage());
            return '<div class="alert alert-danger">Error rendering form: ' . $e->getMessage() . '</div>';
        }
    }

    /**
     * Get available sections for agency - NO ALIASES
     */
    public function get_available_sections($agency_id = null) 
    {
        return $this->db->select('mod_template_sections.*, sys_form_schemas.name as schema_name')
                       ->from('mod_template_sections')
                       ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                       ->where('mod_template_sections.enabled', 1)
                       ->order_by('mod_template_sections.sort_order', 'ASC')
                       ->order_by('mod_template_sections.name', 'ASC')
                       ->get()
                       ->result();
    }

    /**
     * Delete agency template
     */
    public function delete_agency_template($agency_id) 
    {
        $template = $this->get_agency_template($agency_id);
        if (!$template) return false;

        $this->db->trans_start();
        
        // Delete sections
        $this->db->delete('agency_template_sections', ['agency_template_id' => $template->id]);
        
        // Delete template
        $this->db->delete($this->table, ['id' => $template->id]);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    public function get_all($section = '') 
    {
        return $this->db->where('enabled', 1)
                       ->order_by('template_name', 'ASC')
                       ->get($this->table);
    }

    /**
     * Get count of agency templates
     */
    public function get_count() 
    {
        return $this->db->where('enabled', 1)
                       ->count_all_results($this->table);
    }

    public function get_by_id($id, $table = false) 
    {
        if ($table === false) {
            $table = $this->table;
        }
        $this->db->where('enabled', 1);
        return $this->db->where('id', $id)->get($table)->row();
    }

    public function save_instance($template_id, $instance_name, $form_data) 
    {
        $data = [
            'template_id' => $template_id,
            'name' => $instance_name,
            'form_data' => json_encode($form_data),
            'created_at' => date('Y-m-d H:i:s'),
            'enabled' => 1
        ];

        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update_instance($instance_id, $form_data) 
    {
        $data = [
            'form_data' => json_encode($form_data),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $instance_id);
        return $this->db->update($this->table, $data);
    }

    public function get_instance($instance_id) 
    {
        return $this->db->get_where($this->table, ['id' => $instance_id])->row();
    }

    public function get_instances_by_template($template_id) 
    {
        return $this->db->get_where($this->table, ['template_id' => $template_id])->result();
    }
    
    public function add_section_to_template($template_id, $section_id) {
        // Get next position
        $this->db->select_max('sort_order');
        $this->db->from('agency_template_sections');
        $this->db->where('agency_template_id', $template_id);
        $query = $this->db->get();
        $max_position = $query->row()->sort_order ?? 0;

        $data = [
            'agency_template_id' => $template_id,
            'section_id' => $section_id,
            'sort_order' => $max_position + 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('agency_template_sections', $data);
    }
}
?>