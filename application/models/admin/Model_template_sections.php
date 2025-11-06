<?php
class Model_template_sections extends CRUD_Model 
{
    protected $table = 'mod_template_sections';

    public function __construct() 
    {
        parent::__construct();
    }

  // In your Model_template_sections model
public function get_all($section = '') 
{
    log_message('debug', '=== CUSTOM GET_ALL CALLED FOR TEMPLATE SECTIONS ===');
    
    $this->db->select('mts.*, sfs.name as schema_name, sfs.agency_id')
             ->from('mod_template_sections as mts')
             ->join('sys_form_schemas as sfs', 'sfs.id = mts.schema_id', 'left')
             ->where('mts.removed', 0);

    $agency_id = $this->session->userdata('agency_id');
    if ($agency_id) {
        $this->db->where('sfs.agency_id', $agency_id);
    }

    // Get filters from session
    $filters = get_ecms_filters($this->pageName);
    
    // Handle section_type filter
    if (!empty($filters['section_type']['value'])) {
        log_message('debug', 'Applying section_type filter from session: ' . $filters['section_type']['value']);
        $this->db->where('mts.section_type', $filters['section_type']['value']);
    }

    // Handle search filter
    if (!empty($filters['search']['value'])) {
        $this->db->group_start();
        $this->db->like('mts.name', $filters['search']['value']);
        $this->db->or_like('mts.code', $filters['search']['value']);
        $this->db->or_like('sfs.name', $filters['search']['value']);
        $this->db->group_end();
    }

    $this->db->order_by('mts.id', 'ASC');
    
    $query = $this->db->get();
    log_message('debug', 'Get All SQL: ' . $this->db->last_query());
    log_message('debug', 'Get All Results: ' . $query->num_rows());
    
    return $query;
}

public function get_count($filters = array())
{
    log_message('debug', '=== CUSTOM GET_COUNT CALLED FOR TEMPLATE SECTIONS ===');
    
    // If no filters passed, get them from session
    if (empty($filters)) {
        $session_filters = get_ecms_filters($this->pageName);
        if (!empty($session_filters['section_type']['value'])) {
            $filters['section_type'] = $session_filters['section_type']['value'];
        }
        if (!empty($session_filters['search']['value'])) {
            $filters['search'] = $session_filters['search']['value'];
        }
    }
    
    $this->db->select('COUNT(DISTINCT mts.id) AS row_count')
             ->from('mod_template_sections as mts')
             ->join('sys_form_schemas as sfs', 'sfs.id = mts.schema_id', 'left')
             ->where('mts.removed', 0);

    $agency_id = $this->session->userdata('agency_id');
    if ($agency_id) {
        $this->db->where('sfs.agency_id', $agency_id);
    }

    // Handle section_type filter safely
    if (isset($filters['section_type']) && $filters['section_type']) {
        log_message('debug', 'Applying section_type filter: ' . $filters['section_type']);
        $this->db->where('mts.section_type', $filters['section_type']);
    }

    // Handle search filter
    if (isset($filters['search']) && $filters['search']) {
        $this->db->group_start();
        $this->db->like('mts.name', $filters['search']);
        $this->db->or_like('mts.code', $filters['search']);
        $this->db->or_like('sfs.name', $filters['search']);
        $this->db->group_end();
    }

    $sql = $this->db->get_compiled_select();
    log_message('debug', 'Generated SQL: ' . $sql);
    
    $query = $this->db->get();
    $result = $query->row()->count;
    
    log_message('debug', 'Count result: ' . $result);
    return $result;
}
    public function get_by_type($section_type) 
    {
        return $this->get_all($section_type)->result();
    }

    public function get_available_section_types() 
    {
        return [
            'about' => 'About Section',
            'skills' => 'Skills & Competencies',
            'experience' => 'Work Experience',
            'education' => 'Education & Qualifications',
            'contact' => 'Contact Information',
            'services' => 'Services Offered',
            'portfolio' => 'Portfolio & Projects',
            'testimonials' => 'Testimonials & Reviews',
            'pricing' => 'Pricing & Packages',
            'custom' => 'Custom Section'
        ];
    }

    public function get_sections_with_schema($section_ids = []) 
    {
        $this->db->select('mts.*, sfs.schema, sfs.scripts, sfs.styling')
                 ->from($this->table . ' as mts')
                 ->join('sys_form_schemas as sfs', 'sfs.id = mts.schema_id')
                 ->where('mts.enabled', 1);
        
        if (!empty($section_ids)) {
            $this->db->where_in('mts.id', $section_ids);
        }
        
        $this->db->order_by('mts.sort_order', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get enabled sections for template builder
     */
    public function get_enabled_sections($agency_id = null, $section_type = null)
    {
        $this->db->select('mts.*, sfs.name as schema_name')
                 ->from($this->table . ' as mts')
                 ->join('sys_form_schemas as sfs', 'sfs.id = mts.schema_id', 'left')
                 ->where('mts.enabled', 1)
                 ->where('mts.removed', 0)
                 ->order_by('mts.name', 'ASC');
        
        if ($agency_id) {
            $this->db->where('sfs.agency_id', $agency_id);
        }
        
        if ($section_type && $section_type !== 'all') {
            $this->db->where('mts.section_type', $section_type);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Get sections for template builder with filtering
     */
    public function get_sections_for_builder($agency_id = null, $filters = [])
    {
        $this->db->select('mts.*, sfs.name as schema_name')
                 ->from($this->table . ' as mts')
                 ->join('sys_form_schemas as sfs', 'sfs.id = mts.schema_id', 'left')
                 ->where('mts.enabled', 1)
                 ->where('mts.removed', 0);
        
        if ($agency_id) {
            $this->db->where('sfs.agency_id', $agency_id);
        }
        
        // Apply filters
        if (!empty($filters['section_type']) && $filters['section_type'] !== 'all') {
            $this->db->where('mts.section_type', $filters['section_type']);
        }
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('mts.name', $filters['search']);
            $this->db->or_like('mts.description', $filters['search']);
            $this->db->or_like('mts.code', $filters['search']);
            $this->db->group_end();
        }
        
        $this->db->order_by('mts.name', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get section type statistics
     */
    public function get_section_stats($agency_id = null)
    {
        $this->db->select('section_type, COUNT(*) as count')
                 ->from($this->table . ' as mts')
                 ->join('sys_form_schemas as sfs', 'sfs.id = mts.schema_id', 'left')
                 ->where('mts.enabled', 1)
                 ->where('mts.removed', 0)
                 ->group_by('section_type')
                 ->order_by('count', 'DESC');
        
        if ($agency_id) {
            $this->db->where('sfs.agency_id', $agency_id);
        }
        
        return $this->db->get()->result();
    }



}
?>