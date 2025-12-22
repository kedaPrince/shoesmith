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
            } elseif (!empty($login_data['agency']['id'])) {
                $agency_id = $login_data['agency']['id'];
            } else {
            }
        } else {
        }
        
        return $agency_id;
    }

public function get_by_id($id, $table = false)
{
    $agency_id = $this->get_agency_id();
    
    if (!$agency_id) {
        return null;
    }
    
    // First try to get from mod_layouts (single templates) WITH AGENCY NAME
    $single_template = $this->db
        ->select('ml.*, sfs.name as schema_name, a.name as agency_name, "single" as template_type')
        ->from('mod_layouts ml')
        ->join('sys_form_schemas sfs', 'sfs.id = ml.schema_id', 'left')
        ->join('agencies a', 'a.id = sfs.agency_id', 'left')
        ->where('ml.id', $id)
        ->where('ml.removed', 0)
        ->where('sfs.agency_id', $agency_id)
        ->get()
        ->row();
        
    if ($single_template) {
        return $single_template;
    }
    
    // If not found in mod_layouts, try agency_custom_templates (composite templates) WITH AGENCY NAME
    $composite_template = $this->db
        ->select('act.*, a.name as agency_name, "composite" as template_type')
        ->from('agency_custom_templates act')
        ->join('agencies a', 'a.id = act.agency_id', 'left')
        ->where('act.id', $id)
        ->where('act.enabled', 1)
        ->where('act.agency_id', $agency_id)
        ->get()
        ->row();
        
    if ($composite_template) {
        return $composite_template;
    }
    
    return null;
}

public function get_hybrid_templates($filters = [])
{        
    $agency_id = $this->get_agency_id();
    
    if (!$agency_id) {
        return [];
    }
    
    $all_templates = [];

    // Get single schema templates from mod_layouts - WITH AGENCY NAME
    $single_query = $this->db->select('ml.id, ml.name as template_name, ml.code, ml.schema_id, ml.description, ml.preview_image, ml.enabled, ml.created_at, 
                                      sfs.name as schema_name, sfs.agency_id, 
                                      a.name as agency_name, 
                                      NULL as section_count, 
                                      "single" as template_type', false)
            ->from('mod_layouts ml')
            ->join('sys_form_schemas sfs', 'sfs.id = ml.schema_id', 'left')
            ->join('agencies a', 'a.id = sfs.agency_id', 'left')
            ->where('ml.removed', 0)
            ->where('sfs.agency_id', $agency_id);
    
    // Apply filters for single templates
    if (!empty($filters['search'])) {
        $search = $filters['search'];
        $single_query->group_start()
                ->like('ml.name', $search)
                ->or_like('sfs.name', $search)
                ->or_like('ml.description', $search)
                ->or_like('a.name', $search)
                ->group_end();
    }
    
    $single_templates = $single_query->get()->result();
    
    // Get composite templates from agency_custom_templates - WITH AGENCY NAME
    $composite_query = $this->db->select('act.id, 
                                         act.template_name, 
                                         NULL as code, 
                                         NULL as schema_id, 
                                         act.description, 
                                         NULL as preview_image, 
                                         act.enabled, 
                                         act.created_at, 
                                         NULL as schema_name, 
                                         act.agency_id, 
                                         a.name as agency_name,
                                         COUNT(ats.section_id) as section_count, 
                                         "composite" as template_type', false)
            ->from('agency_custom_templates act')
            ->join('agency_template_sections ats', 'ats.agency_template_id = act.id', 'left')
            ->join('agencies a', 'a.id = act.agency_id', 'left')
            ->where('act.enabled', 1)
            ->where('act.agency_id', $agency_id)
            ->group_by('act.id');
    
    // Apply filters for composite templates
    if (!empty($filters['search'])) {
        $search = $filters['search'];
        $composite_query->group_start()
                ->like('act.template_name', $search)
                ->or_like('act.description', $search)
                ->or_like('a.name', $search)
                ->group_end();
    }
    
    $composite_templates = $composite_query->get()->result();
    
    // Combine results
    $all_templates = array_merge($single_templates, $composite_templates);
    
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
    // Line 164 - Update this method to include the $section parameter
public function get_all($section = '') 
{
    $agency_id = $this->get_agency_id();
    
    log_message('debug', 'Model_templates::get_all() called with section: ' . $section . ', agency_id: ' . $agency_id);
    
    $query = $this->db
        ->select('ml.id, ml.name, ml.code, ml.schema_id, ml.description, ml.preview_image, ml.enabled, ml.created_at, sfs.name as schema_name, sfs.agency_id, "single" as template_type')
        ->from('mod_layouts ml')
        ->join('sys_form_schemas sfs', 'sfs.id = ml.schema_id', 'left')
        ->where('ml.removed', 0);
    
    // Add agency filter
    if ($agency_id) {
        $query->where('sfs.agency_id', $agency_id);
    } else {
        log_message('error', 'No agency_id found in Model_templates::get_all()');
        // Return empty if no agency ID
        return $this->db->where('1=0')->get('mod_layouts');
    }
    
    // If section parameter is provided, you can use it for filtering
    // This is optional - only use if you need section-based filtering
    if (!empty($section)) {
        // Example: filter by section name or code
        // $query->like('ml.name', $section);
        // OR filter by schema section
        // $query->where('ml.code', $section);
        
        // For now, just log it since we don't know how you use it
        log_message('debug', 'Section parameter provided: ' . $section);
    }
    
    $query->order_by('ml.id', 'DESC');
    
    $result = $query->get();
    
    log_message('debug', 'Model_templates::get_all() query: ' . $this->db->last_query());
    log_message('debug', 'Model_templates::get_all() found: ' . $result->num_rows() . ' templates');
    
    return $result;
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