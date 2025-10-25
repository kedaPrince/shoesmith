<?php
class Model_templates extends CRUD_Model 
{
    protected $table = 'mod_layouts';

    public function get_by_id($id, $table = false)
    {
        log_message('debug', '=== GET_BY_ID CALLED FOR ID: ' . $id . ' ===');
        
        // First try to get from mod_layouts (single templates)
        $single_template = $this->db
            ->select('ml.*, sfs.name as schema_name, "single" as template_type')
            ->from('mod_layouts ml')
            ->join('sys_form_schemas sfs', 'sfs.id = ml.schema_id', 'left')
            ->where('ml.id', $id)
            ->where('ml.removed', 0)
            ->get()
            ->row();
            
        if ($single_template) {
            log_message('debug', 'Found single template: ' . $single_template->name);
            return $single_template;
        }
        
        // If not found in mod_layouts, try agency_custom_templates (composite templates)
        log_message('debug', 'Not found in mod_layouts, checking agency_custom_templates');
        $composite_template = $this->db
            ->select('act.*, "composite" as template_type')
            ->from('agency_custom_templates act')
            ->where('act.id', $id)
            ->where('act.enabled', 1)
            ->get()
            ->row();
            
        if ($composite_template) {
            log_message('debug', 'Found composite template: ' . $composite_template->template_name);
            return $composite_template;
        }
        
        log_message('debug', 'Template not found in any table for ID: ' . $id);
        return null;
    }

    public function get_hybrid_templates($filters = [])
    {
        log_message('debug', '=== GET_HYBRID_TEMPLATES CALLED ===');
        
        // Get single schema templates from mod_layouts
        $single_query = $this->db->select('ml.id, ml.name, ml.code, ml.schema_id, ml.description, ml.preview_image, ml.enabled, ml.created_at, sfs.name as schema_name, NULL as agency_id, NULL as template_name, NULL as section_count, "single" as template_type', false)
                 ->from('mod_layouts ml')
                 ->join('sys_form_schemas sfs', 'sfs.id = ml.schema_id', 'left')
                 ->where('ml.removed', 0);
        
        // Apply filters for single templates
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $single_query->group_start()
                     ->like('ml.name', $search)
                     ->or_like('sfs.name', $search)
                     ->or_like('ml.description', $search)
                     ->group_end();
        }
        
        if (!empty($filters['template_type']) && $filters['template_type'] === 'single') {
            // Only single templates requested
            $single_templates = $single_query->get()->result();
            log_message('debug', 'Single templates found: ' . count($single_templates));
            return $single_templates;
        }
        
        $single_templates = $single_query->get()->result();
        log_message('debug', 'Single templates found: ' . count($single_templates));
        
        // Get composite templates from agency_custom_templates
        $composite_query = $this->db->select('act.id, NULL as name, NULL as code, NULL as schema_id, act.description, NULL as preview_image, act.enabled, act.created_at, NULL as schema_name, act.agency_id, act.template_name, COUNT(ats.section_id) as section_count, "composite" as template_type', false)
                 ->from('agency_custom_templates act')
                 ->join('agency_template_sections ats', 'ats.agency_template_id = act.id', 'left')
                 ->where('act.enabled', 1)
                 ->group_by('act.id');
        
        // Apply filters for composite templates
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $composite_query->group_start()
                     ->like('act.template_name', $search)
                     ->or_like('act.description', $search)
                     ->group_end();
        }
        
        if (!empty($filters['template_type']) && $filters['template_type'] === 'composite') {
            // Only composite templates requested
            $composite_templates = $composite_query->get()->result();
            log_message('debug', 'Composite templates found: ' . count($composite_templates));
            return $composite_templates;
        }
        
        $composite_templates = $composite_query->get()->result();
        log_message('debug', 'Composite templates found: ' . count($composite_templates));
        
        // Combine results
        $all_templates = array_merge($single_templates, $composite_templates);
        
        log_message('debug', 'Hybrid templates found: ' . count($all_templates) . ' total');
        
        return $all_templates;
    }

    public function get_hybrid_template_count($filters = [])
    {
        $single_count = $this->db
            ->from('mod_layouts ml')
            ->where('ml.removed', 0)
            ->count_all_results();
            
        $composite_count = $this->db
            ->from('agency_custom_templates act')
            ->where('act.enabled', 1)
            ->count_all_results();
            
        return $single_count + $composite_count;
    }

    // Keep existing methods for single templates
    public function get_all($section = '') 
    {
        log_message('debug', '=== MODEL GET_ALL CALLED FOR SINGLE SCHEMA TEMPLATES ===');
        
        $query = $this->db
            ->select('ml.id, ml.name, ml.code, ml.schema_id, ml.description, ml.preview_image, ml.enabled, ml.created_at, sfs.name as schema_name, "single" as template_type')
            ->from('mod_layouts ml')
            ->join('sys_form_schemas sfs', 'sfs.id = ml.schema_id', 'left')
            ->where('ml.removed', 0)
            ->order_by('ml.id', 'DESC')
            ->get();
        
        log_message('debug', 'Model get_all found: ' . $query->num_rows() . ' single schema templates');
        
        return $query;
    }

    public function get_count() 
    {
        return $this->db
            ->from($this->table)
            ->where('removed', 0)
            ->count_all_results();
    }

    /**
     * Get template by code
     */
    public function get_by_code($code) 
    {
        return $this->db
            ->select('mod_layouts.*, sys_form_schemas.schema, sys_form_schemas.scripts, sys_form_schemas.styling')
            ->from('mod_layouts')
            ->join('sys_form_schemas', 'sys_form_schemas.id = mod_layouts.schema_id')
            ->where('mod_layouts.code', $code)
            ->where('mod_layouts.enabled', 1)
            ->where('sys_form_schemas.enabled', 1)
            ->get()
            ->row();
    }
}