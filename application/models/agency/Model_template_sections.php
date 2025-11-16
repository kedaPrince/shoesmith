<?php
class Model_template_sections extends CRUD_Model 
{
    protected $table = 'mod_template_sections';

    public function __construct() 
    {
        parent::__construct();
    }

    public function get_count() 
    {
        
        $agency_id = $this->session->userdata('agency_id');
      
        // Use COUNT(*) instead of COUNT(DISTINCT id) to get accurate count
        $this->db->select('COUNT(*) AS row_count', false);
        
        $this->main_joins();
        $this->main_wheres();
        $this->main_grouping();
        $this->main_filters();

        $query = $this->db->get('mod_template_sections');
        
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $count = $row->row_count;
          
            return $count;
        } else {
            $message = "Query for count returned 0 rows in the CRUD_model";
            anomalies::log($message, $this->db->last_query());
            return 0;
        }
    }

    public function main_joins()
    {
        // Use full table names - NO ALIASES
        $this->db->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left');
    }

    public function main_wheres()
    {
        // Use full table names - NO ALIASES
        $this->db->where('mod_template_sections.removed', 0);
        $this->db->where('mod_template_sections.enabled', 1);
        
        //  FIX: STRICT AGENCY FILTERING - Only show sections from current agency
        $agency_id = $this->session->userdata('agency_id');
        if ($agency_id) {
            $this->db->where('sys_form_schemas.agency_id', $agency_id);
        } else {
            $this->db->where('1=0'); // Show nothing if no agency ID
        }
    }

    public function main_filters()
    {
        // Get filters and apply them without aliases
        $filters = get_ecms_filters($this->pageName);
        if (!empty($filters['search'])) {
            $search_value = $filters['search'];
            // Ensure it's a string, not an array
            if (is_array($search_value)) {
                $search_value = isset($search_value['value']) ? $search_value['value'] : '';
            }
            
            if (!empty($search_value)) {
                $this->db->group_start();
                $this->db->like('mod_template_sections.name', $search_value);
                $this->db->or_like('mod_template_sections.code', $search_value);
                $this->db->or_like('sys_form_schemas.name', $search_value);
                $this->db->group_end();
            }
        }
        
        if (!empty($filters['section_type'])) {
            $section_type_value = $filters['section_type'];
            // Ensure it's a string, not an array
            if (is_array($section_type_value)) {
                $section_type_value = isset($section_type_value['value']) ? $section_type_value['value'] : '';
            }
            
            if (!empty($section_type_value) && $section_type_value !== 'all') {
                $this->db->where('mod_template_sections.section_type', $section_type_value);
            }
        }
    }

    public function main_grouping()
    {
        // No grouping needed
    }

    /**
     * OVERRIDE THE GET METHOD TO USE FULL TABLE NAMES WITH AGENCY FILTER
     */
    public function get($id = null, $single = false, $params = array())
    {
        $agency_id = $this->session->userdata('agency_id');
        
        $this->db->select('mod_template_sections.*, sys_form_schemas.name as schema_name')
                 ->from('mod_template_sections')
                 ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                 ->where('mod_template_sections.removed', 0)
                 ->where('mod_template_sections.enabled', 1);

        //  FIX: Apply agency filter
        if ($agency_id) {
            $this->db->where('sys_form_schemas.agency_id', $agency_id);
        } else {
            $this->db->where('1=0'); // Show nothing if no agency ID
        }

        if ($id != null) {
            $this->db->where('mod_template_sections.id', $id);
        }

        // Apply any additional filters
        if (!empty($params['filters'])) {
            foreach ($params['filters'] as $key => $value) {
                if ($key === 'search') {
                    $this->db->group_start();
                    $this->db->like('mod_template_sections.name', $value);
                    $this->db->or_like('mod_template_sections.code', $value);
                    $this->db->or_like('sys_form_schemas.name', $value);
                    $this->db->group_end();
                } elseif ($key === 'section_type') {
                    $this->db->where('mod_template_sections.section_type', $value);
                }
            }
        }

        if ($single) {
            return $this->db->get()->row();
        }

        return $this->db->get()->result();
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
            'content' => 'General Content',
            'custom' => 'Custom Section'
        ];
    }

     public function get_section_type_stats($agency_id = null)
    {
        $this->db->select('mod_template_sections.section_type, COUNT(*) as count')
                 ->from('mod_template_sections')
                 ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                 ->where('mod_template_sections.enabled', 1)
                 ->where('mod_template_sections.removed', 0)
                 ->group_by('mod_template_sections.section_type')
                 ->order_by('count', 'DESC');
        
        if ($agency_id) {
            $this->db->where('sys_form_schemas.agency_id', $agency_id);
        }
        
        $result = $this->db->get()->result();
        
        $section_types = $this->get_available_section_types();
        foreach ($result as $stat) {
            $stat->display_name = $section_types[$stat->section_type] ?? ucfirst($stat->section_type);
        }
        
        return $result;
    }
    /**
     * CUSTOM METHODS WITH AGENCY FILTERING
     */

    public function get_all($section_type = null) 
    {
        $agency_id = $this->session->userdata('agency_id');
        
        $this->db->select('mod_template_sections.*, sys_form_schemas.name as schema_name')
                 ->from('mod_template_sections')
                 ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                 ->where('mod_template_sections.removed', 0)
                 ->where('mod_template_sections.enabled', 1);
        
        //  FIX: Apply agency filter
        if ($agency_id) {
            $this->db->where('sys_form_schemas.agency_id', $agency_id);
        } else {
            $this->db->where('1=0'); // Show nothing if no agency ID
        }
        
        if ($section_type && $section_type !== 'all') {
            $this->db->where('mod_template_sections.section_type', $section_type);
        }
        
        $this->db->order_by('mod_template_sections.sort_order', 'ASC')
                 ->order_by('mod_template_sections.name', 'ASC');
                 
        return $this->db->get();
    }

    public function get_enabled_sections($agency_id = null, $section_type = null)
    {
        // Use session agency_id if not provided
        if (!$agency_id) {
            $agency_id = $this->session->userdata('agency_id');
        }
        
        $this->db->select('mod_template_sections.*, sys_form_schemas.name as schema_name')
                 ->from('mod_template_sections')
                 ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                 ->where('mod_template_sections.enabled', 1)
                 ->where('mod_template_sections.removed', 0);
        
        //  FIX: STRICT agency filtering
        if ($agency_id) {
            $this->db->where('sys_form_schemas.agency_id', $agency_id);
        } else {
            $this->db->where('1=0'); // Show nothing if no agency ID
        }
        
        if ($section_type && $section_type !== 'all') {
            $this->db->where('mod_template_sections.section_type', $section_type);
        }
        
        $this->db->order_by('mod_template_sections.name', 'ASC');
        
        return $this->db->get()->result();
    }


    /**
     * Get sections available for the current agency only
     */
    public function get_available_sections_for_agency($section_type = null)
    {
        $agency_id = $this->session->userdata('agency_id');
        
        if (!$agency_id) {
            return [];
        }
        
        
        $this->db->select('mod_template_sections.*, sys_form_schemas.name as schema_name')
                 ->from('mod_template_sections')
                 ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                 ->where('mod_template_sections.enabled', 1)
                 ->where('mod_template_sections.removed', 0)
                 ->where('sys_form_schemas.agency_id', $agency_id); //  STRICT agency filter
        
        if ($section_type && $section_type !== 'all') {
            $this->db->where('mod_template_sections.section_type', $section_type);
        }
        
        $this->db->order_by('mod_template_sections.sort_order', 'ASC')
                 ->order_by('mod_template_sections.name', 'ASC');
                 
        $sections = $this->db->get()->result();
        
        
        return $sections;
    }
}
?>