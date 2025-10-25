<?php
class Model_template_sections extends CRUD_Model 
{
    protected $table = 'mod_template_sections';

    public function __construct() 
    {
        parent::__construct();
    }

    public function get_all($section_type = null) 
{
    $this->db->select('mts.*, sfs.name as schema_name')
             ->from($this->table . ' as mts')
             ->join('sys_form_schemas as sfs', 'sfs.id = mts.schema_id', 'left')
             ->where('mts.removed', 0); // Only show non-removed items
    
    if ($section_type) {
        $this->db->where('mts.section_type', $section_type);
    }
    
    $this->db->order_by('mts.sort_order', 'ASC')
             ->order_by('mts.name', 'ASC');
             
    return $this->db->get();
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
}
?>