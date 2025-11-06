<?php
class Model_templates extends CRUD_Model 
{
    protected $table = 'mod_layouts';
private function get_agency_id()
{
    $agency_id = $this->session->userdata('agency_id');
    
    // If not found directly, check login data
    if (!$agency_id) {
        $login_data = $this->session->userdata('login');
        if (!empty($login_data['agency']['agency_id'])) {
            $agency_id = $login_data['agency']['agency_id'];
            log_message('debug', 'Got agency_id from login[agency][agency_id]: ' . $agency_id);
        } elseif (!empty($login_data['agency']['id'])) {
            $agency_id = $login_data['agency']['id'];
            log_message('debug', 'Got agency_id from login[agency][id]: ' . $agency_id);
        } else {
            log_message('debug', 'No agency ID found in session data');
        }
    } else {
        log_message('debug', 'Got agency_id directly from session: ' . $agency_id);
    }
    
    log_message('debug', 'Final agency ID retrieved: ' . ($agency_id ? $agency_id : 'NOT FOUND'));
    return $agency_id;
}
    public function get_by_id($id, $table = false)
    {
        log_message('debug', '=== GET_BY_ID CALLED FOR ID: ' . $id . ' ===');
        
        $agency_id = $this->get_agency_id();
        
        if (!$agency_id) {
            log_message('debug', 'No agency ID found - returning null');
            return null;
        }
        
        // First try to get from mod_layouts (single templates)
        $single_template = $this->db
            ->select('ml.*, sfs.name as schema_name, "single" as template_type')
            ->from('mod_layouts ml')
            ->join('sys_form_schemas sfs', 'sfs.id = ml.schema_id', 'left')
            ->where('ml.id', $id)
            ->where('ml.removed', 0)
            ->where('sfs.agency_id', $agency_id) // Agency filter
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
            ->where('act.agency_id', $agency_id) // Agency filter
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
    
    $agency_id = $this->get_agency_id();
    
    if (!$agency_id) {
        log_message('debug', 'NO AGENCY ID FOUND - RETURNING EMPTY ARRAY');
        return [];
    }
    
    log_message('debug', 'Filtering for agency ID: ' . $agency_id);
    
    $all_templates = [];

    // Get single schema templates from mod_layouts - ONLY for current agency
    $single_query = $this->db->select('ml.id, ml.name, ml.code, ml.schema_id, ml.description, ml.preview_image, ml.enabled, ml.created_at, sfs.name as schema_name, sfs.agency_id, NULL as template_name, NULL as section_count, "single" as template_type', false)
             ->from('mod_layouts ml')
             ->join('sys_form_schemas sfs', 'sfs.id = ml.schema_id', 'left')
             ->where('ml.removed', 0)
             ->where('sfs.agency_id', $agency_id); // STRICT agency filter
    
    log_message('debug', 'Single templates SQL: ' . $this->db->last_query());
    
    // Apply filters for single templates
    if (!empty($filters['search'])) {
        $search = $filters['search'];
        $single_query->group_start()
                 ->like('ml.name', $search)
                 ->or_like('sfs.name', $search)
                 ->or_like('ml.description', $search)
                 ->group_end();
    }
    
    $single_templates = $single_query->get()->result();
    log_message('debug', 'Single templates found for agency ' . $agency_id . ': ' . count($single_templates));
    
    // Debug single templates
    foreach ($single_templates as $template) {
        log_message('debug', 'Single Template - ID: ' . $template->id . ', Name: ' . $template->name . ', Agency: ' . ($template->agency_id ?? 'N/A'));
    }
    
    // Get composite templates from agency_custom_templates - ONLY for current agency
    $composite_query = $this->db->select('act.id, NULL as name, NULL as code, NULL as schema_id, act.description, NULL as preview_image, act.enabled, act.created_at, NULL as schema_name, act.agency_id, act.template_name, COUNT(ats.section_id) as section_count, "composite" as template_type', false)
             ->from('agency_custom_templates act')
             ->join('agency_template_sections ats', 'ats.agency_template_id = act.id', 'left')
             ->where('act.enabled', 1)
             ->where('act.agency_id', $agency_id) // STRICT agency filter
             ->group_by('act.id');
    
    log_message('debug', 'Composite templates SQL: ' . $this->db->last_query());
    
    // Apply filters for composite templates
    if (!empty($filters['search'])) {
        $search = $filters['search'];
        $composite_query->group_start()
                 ->like('act.template_name', $search)
                 ->or_like('act.description', $search)
                 ->group_end();
    }
    
    $composite_templates = $composite_query->get()->result();
    log_message('debug', 'Composite templates found for agency ' . $agency_id . ': ' . count($composite_templates));
    
    // Debug composite templates
    foreach ($composite_templates as $template) {
        log_message('debug', 'Composite Template - ID: ' . $template->id . ', Name: ' . $template->template_name . ', Agency: ' . ($template->agency_id ?? 'N/A'));
    }
    
    // Combine results
    $all_templates = array_merge($single_templates, $composite_templates);
    
    log_message('debug', 'Total hybrid templates found for agency ' . $agency_id . ': ' . count($all_templates));
    
    return $all_templates;
}

    public function get_hybrid_template_count($filters = [])
    {
        $agency_id = $this->session->userdata('agency_id');
        
        if (!$agency_id) {
            return 0;
        }
        
        $single_count = $this->db
            ->from('mod_layouts ml')
            ->join('sys_form_schemas sfs', 'sfs.id = ml.schema_id', 'left')
            ->where('ml.removed', 0)
            ->where('sfs.agency_id', $agency_id)
            ->count_all_results();
            
        $composite_count = $this->db
            ->from('agency_custom_templates act')
            ->where('act.enabled', 1)
            ->where('act.agency_id', $agency_id)
            ->count_all_results();
            
        return $single_count + $composite_count;
    }

    // Keep existing methods for single templates
    public function get_all($section = '') 
    {
        log_message('debug', '=== MODEL GET_ALL CALLED FOR SINGLE SCHEMA TEMPLATES ===');
        
        $agency_id = $this->get_agency_id();
        
        $query = $this->db
            ->select('ml.id, ml.name, ml.code, ml.schema_id, ml.description, ml.preview_image, ml.enabled, ml.created_at, sfs.name as schema_name, sfs.agency_id, "single" as template_type')
            ->from('mod_layouts ml')
            ->join('sys_form_schemas sfs', 'sfs.id = ml.schema_id', 'left')
            ->where('ml.removed', 0);
        
        // Add agency filter
        if ($agency_id) {
            $query->where('sfs.agency_id', $agency_id);
        } else {
            // Return empty if no agency ID
            return $this->db->where('1=0')->get();
        }
        
        $query->order_by('ml.id', 'DESC');
        
        log_message('debug', 'Model get_all found: ' . $query->get()->num_rows() . ' single schema templates for agency ' . $agency_id);
        
        return $query;
    }

    public function get_count() 
    {
        $agency_id = $this->session->userdata('agency_id');
        
        $query = $this->db
            ->from($this->table)
            ->where('removed', 0);
            
        if ($agency_id) {
            $query->join('sys_form_schemas sfs', 'sfs.id = ' . $this->table . '.schema_id', 'left')
                  ->where('sfs.agency_id', $agency_id);
        }
        
        return $query->count_all_results();
    }

    /**
     * Get template by code
     */
    public function get_by_code($code) 
    {
        $agency_id = $this->session->userdata('agency_id');
        
        $query = $this->db
            ->select('mod_layouts.*, sys_form_schemas.schema, sys_form_schemas.scripts, sys_form_schemas.styling')
            ->from('mod_layouts')
            ->join('sys_form_schemas', 'sys_form_schemas.id = mod_layouts.schema_id')
            ->where('mod_layouts.code', $code)
            ->where('mod_layouts.enabled', 1)
            ->where('sys_form_schemas.enabled', 1);
            
        // Add agency filter
        if ($agency_id) {
            $query->where('sys_form_schemas.agency_id', $agency_id);
        }
            
        return $query->get()->row();
    }
}
?>