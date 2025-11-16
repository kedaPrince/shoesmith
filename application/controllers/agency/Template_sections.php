<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Template_sections extends CRUD_Controller 
{
    public $pageName = 'template_sections';
    public $group = 'templates';
    public $model = 'Model_template_sections';
    public $singular = 'template_section';
    public $plural = 'template_sections';
    public $view = '';
    public $sorting = array('id' => 'ASC');
    public $seoFields = false;
    public $quickManage = true;
    public $quickManageSize = 5;
    public $sluggify = false;

    public function __construct() 
    {
        parent::__construct();
        $this->load->model($this->folder . '/' . $this->model);
        $this->setup_listing();
        $this->setup_fields();
        
        $this->folder = 'agency';
    }

    public function setup_listing() 
    {
        $section_types = [
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

        $this->listFields = array(
            'id' => array('label' => 'ID', 'sort' => true),
            'name' => array('label' => 'Name', 'sort' => true),
            'code' => array('label' => 'Code', 'sort' => true),
            'section_type' => array(
                'label' => 'Section Type', 
                'sort' => true,
                'function' => function($value, $row) use ($section_types) {
                    return isset($section_types[$value]) ? 
                        '<span class="badge badge-info section-type-badge section-type-' . $value . '">' . $section_types[$value] . '</span>' : 
                        '<span class="badge badge-secondary">' . $value . '</span>';
                }
            ),
            'schema_name' => array('label' => 'Form Schema', 'sort' => true),
            'agency_id' => array(
                'label' => 'Agency ID',
                'sort' => true,
                'function' => function($value, $row) {
                    $agency_id = isset($row->agency_id) ? $row->agency_id : '-';
                    $current_agency_id = $this->session->userdata('agency_id');
                    $badge_class = ($agency_id == $current_agency_id) ? 'badge-success' : 'badge-secondary';
                    return '<span class="badge ' . $badge_class . '">' . $agency_id . '</span>';
                }
            ),
            'enabled' => array(
                'label' => 'Status',
                'sort' => true,
                'function' => function($value, $row) {
                    return $value ? 
                        '<span class="badge badge-success">Enabled</span>' : 
                        '<span class="badge badge-secondary">Disabled</span>';
                }
            )
        );

        $this->filters = array(
            'search' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array('name', 'code')
            ),
            'section_type' => array(
                'label' => 'Section Type',
                'type' => 'dropdown',
                'field' => 'section_type',
                'options' => $section_types
            )
        );
         $this->listActions = array(
            'edit' => array(
                'label'     => lang('label_edit'),
                'url'       => '',
                'icon'      => 'fa-edit',
                'class'     => 'edit-row',
                'function'  => function($str, $row) {
                    $template_type = isset($row->template_type) ? $row->template_type : 'single';
                    if ($template_type === 'composite') {
                        $agency_id = isset($row->agency_id) ? $row->agency_id : 1;
                        return url('agency_templates/build/' . $agency_id . '?template_id=' . $row->id);
                    } else {
                        return url('templates/edit/' . $row->id);
                    }
                }
            ),
            'enable' => array(
                'label'     => lang('label_enable'),
                'url'       => url($this->pageName . '/enable/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'enable-row btn-enable',
                'function'  => (function ($str, $row) {
                    return (isset($row->enabled) && $row->enabled) ? false : $str;
                })
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => url($this->pageName . '/disable/{id}'),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => (function ($str, $row) {
                    return (!isset($row->enabled) || !$row->enabled) ? false : $str;
                })
            ),
            'delete' => array(
                'label'     => lang('label_delete'),
                'url'       => url($this->pageName . '/remove/{id}'),
                'icon'      => 'fa-trash-o',
                'class'     => 'delete-row btn-delete',
                'function'  => function($str, $row) {
                    $template_type = isset($row->template_type) ? $row->template_type : 'single';
                    if ($template_type === 'composite') {
                        return url('agency_templates/delete/' . $row->id);
                    }
                    return $str;
                }
            )
        );
    }

    public function setup_fields() 
    {
        $this->formFields = array(
            'main' => array(
                'name' => 'trim|required',
                'code' => 'trim|required|alpha_dash',
                'schema_id' => 'trim|required|numeric',
                'section_type' => 'trim|required',
                'category' => 'trim',
                'description' => 'trim',
                'sort_order' => 'trim|numeric',
                'enabled' => 'trim|numeric'
            )
        );
    }

    public function index() 
    {
        $this->breadcrumbs = [
            ['title' => lang($this->pageName . '_heading') ?: 'Template Sections', 'url' => redir($this->pageName, true)]
        ];

        $this->view = 'listing';
        $this->load->view($this->folder . '/view_header');
        $this->load->view('cms/crud/view_list', [
            'heading' => lang($this->pageName . '_heading') ?: 'Template Sections',
            'noRows' => lang($this->pageName . '_no_rows') ?: 'No template sections found'
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

   public function ajax_results() 
    {
        $page   = max(1, (int) $this->input->get('page'));
        $limit  = min(100, max(1, (int) $this->input->get('limit')));
        $offset = ($page - 1) * $limit;

        $search = $this->input->get('search');
        $section_type = $this->input->get('section_type');
        $show_disabled = $this->input->get('show_disabled');

        $sort_field = $this->input->get('sort_field') ?: 'id';
        $sort_order = strtoupper($this->input->get('sort_order') ?: 'ASC');

        // Get current agency ID
        $agency_id = $this->session->userdata('agency_id');
        
        if (!$agency_id) {
            // Return empty results if no agency ID
            $response = [
                'success' => true,
                'data' => [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => 0,
                    'last_page' => 0
                ]
            ];
            $this->output_json($response);
            return;
        }
        // SIMPLE QUERY WITH AGENCY FILTER
        $this->db->select('mod_template_sections.*, sys_form_schemas.name as schema_name')
                ->from('mod_template_sections')
                ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                ->where('mod_template_sections.removed', 0)
                ->where('mod_template_sections.enabled', 1)
                ->where('sys_form_schemas.agency_id', $agency_id); // ✅ AGENCY FILTER

        // Section type filter
        if ($section_type && $section_type !== 'all') {
            $this->db->where('mod_template_sections.section_type', $section_type);
        }

        // Search filter
        if ($search) {
            $this->db->group_start();
            $this->db->like('mod_template_sections.name', $search);
            $this->db->or_like('mod_template_sections.code', $search);
            $this->db->or_like('sys_form_schemas.name', $search);
            $this->db->group_end();
        }

        $this->db->order_by($sort_field, $sort_order);
        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        $rows = $query->result();

        // Total count WITH AGENCY FILTER
        $this->db->select('COUNT(*) as total')
                ->from('mod_template_sections')
                ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                ->where('mod_template_sections.removed', 0)
                ->where('mod_template_sections.enabled', 1)
                ->where('sys_form_schemas.agency_id', $agency_id); // ✅ AGENCY FILTER

        if ($section_type && $section_type !== 'all') {
            $this->db->where('mod_template_sections.section_type', $section_type);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('mod_template_sections.name', $search);
            $this->db->or_like('mod_template_sections.code', $search);
            $this->db->or_like('sys_form_schemas.name', $search);
            $this->db->group_end();
        }

        $total_result = $this->db->get()->row();
        $total = $total_result->total;


        $response = [
            'success' => true,
            'data' => $rows,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'last_page' => ceil($total / $limit)
            ]
        ];

        $this->output_json($response);
    }

    private function output_json($data) 
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

  // Add this to your Template_sections.php controller

    public function get_count($filters = array())
    {
        
        $this->db->select('COUNT(*) as count')
                ->from('mod_template_sections')
                ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                ->where('mod_template_sections.removed', 0)
                ->where('mod_template_sections.enabled', 1);

        // Use full table names - NO ALIASES
        if (isset($filters['section_type']) && $filters['section_type']) {
            $this->db->where('mod_template_sections.section_type', $filters['section_type']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $this->db->group_start();
            $this->db->like('mod_template_sections.name', $filters['search']);
            $this->db->or_like('mod_template_sections.code', $filters['search']);
            $this->db->or_like('sys_form_schemas.name', $filters['search']);
            $this->db->group_end();
        }

        $query = $this->db->get();
        $result = $query->row()->count;
        
        return $result;
    }

    /**
     * OVERRIDE THE MAIN DATA METHOD TO PREVENT ALIAS USAGE
     */
    public function get_data($filters = array(), $sorting = array(), $pagination = array())
    {
        
        $page = isset($pagination['page']) ? $pagination['page'] : 1;
        $limit = isset($pagination['limit']) ? $pagination['limit'] : 20;
        $offset = ($page - 1) * $limit;

        $this->db->select('mod_template_sections.*, sys_form_schemas.name as schema_name')
                ->from('mod_template_sections')
                ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                ->where('mod_template_sections.removed', 0)
                ->where('mod_template_sections.enabled', 1);

        // Apply filters
        if (isset($filters['section_type']) && $filters['section_type']) {
            $this->db->where('mod_template_sections.section_type', $filters['section_type']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $this->db->group_start();
            $this->db->like('mod_template_sections.name', $filters['search']);
            $this->db->or_like('mod_template_sections.code', $filters['search']);
            $this->db->or_like('sys_form_schemas.name', $filters['search']);
            $this->db->group_end();
        }

        // Apply sorting
        $sort_field = isset($sorting['field']) ? $sorting['field'] : 'mod_template_sections.id';
        $sort_order = isset($sorting['order']) ? $sorting['order'] : 'ASC';
        $this->db->order_by($sort_field, $sort_order);

        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * OVERRIDE THE MAIN FILTERS TO PREVENT AUTO-ALIAS APPLICATION
     */
    public function main_filters()
    {

        $filters = get_ecms_filters($this->pageName);
        
        // Return empty array to prevent parent from processing
        return array();
    }

    /**
     * OVERRIDE THE MAIN JOINS TO PREVENT AUTO-ALIAS APPLICATION
     */
    public function main_joins()
    {

        return array();
    }

    public function ajax_quick_manage($id = 0)
    {
        $row = $this->{$this->model}->get_by_id($id);
        
        if (!$row && $id > 0) {
            ajax_return(['success' => false, 'error' => 'Section not found']);
            return;
        }

        // Get form schemas and section types for the form
        $agency_id = $this->session->userdata('agency_id');
        
        $this->db->select('id, name')
                 ->from('sys_form_schemas')
                 ->where('enabled', 1)
                 ->order_by('name', 'ASC');
                 
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id)
                     ->or_where('agency_id IS NULL');
        }
        
        $form_schemas = $this->db->get()->result_array();
        
        $schema_options = ['' => 'Select Form Schema'];
        foreach ($form_schemas as $schema) {
            $schema_options[$schema['id']] = $schema['name'];
        }

        $section_types = [
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

        // Load form preview if we have a schema_id
        $preview_form = '';
        if (!empty($row->schema_id)) {
            try {
                $this->load->library('Form_builder');
                
                $schema_row = $this->db->get_where('sys_form_schemas', ['id' => $row->schema_id])->row();
                
                if ($schema_row) {
                    $schema = json_decode($schema_row->schema ?? '{}', true);
                    $styling = json_decode($schema_row->styling ?? '{}', true);
                    $scripts = json_decode($schema_row->scripts ?? '[]', true);
                    
                    $form = $this->form_builder::make()
                        ->set_schema($schema)
                        ->set_styling($styling)
                        ->set_scripts($scripts)
                        ->make_form();
                        
                    $preview_form = $form->form_view;
                }
            } catch (Exception $e) {
                $preview_form = '<div class="alert alert-danger">Error loading form: ' . $e->getMessage() . '</div>';
            }
        }

        $data = array(
            'row' => $row,
            'form_schemas' => $schema_options,
            'section_types' => $section_types,
            'preview_form' => $preview_form
        );

        // Load the quick manage view
        $html = $this->load->view('agency/template_sections/ajax_manage', $data, TRUE);

        $this->output->set_output($html);
    }

    public function preview($id)
    {
        $this->ajax_quick_manage($id);
    }

    public function get_form_preview()
    {
        $schema_id = $this->input->post('schema_id');
        
        if (!$schema_id) {
            $this->output_json([
                'success' => false,
                'message' => 'No schema ID provided'
            ]);
            return;
        }

        try {
            $this->load->library('Form_builder');
            
            $schema_row = $this->db->get_where('sys_form_schemas', ['id' => $schema_id])->row();
            
            if (!$schema_row) {
                $this->output_json([
                    'success' => false,
                    'message' => 'Form schema not found'
                ]);
                return;
            }

            $schema = json_decode($schema_row->schema ?? '{}', true);
            $styling = json_decode($schema_row->styling ?? '{}', true);
            $scripts = json_decode($schema_row->scripts ?? '[]', true);
            
            $form = $this->form_builder::make()
                ->set_schema($schema)
                ->set_styling($styling)
                ->set_scripts($scripts)
                ->make_form();
                
            $this->output_json([
                'success' => true,
                'form_view' => $form->form_view
            ]);
            
        } catch (Exception $e) {
            $this->output_json([
                'success' => false,
                'message' => 'Error loading form: ' . $e->getMessage()
            ]);
        }
    }

    public function quick_manage_extra($id, $row) 
    {
        $agency_id = $this->session->userdata('agency_id');
        
        $this->db->select('id, name')
                 ->from('sys_form_schemas')
                 ->where('enabled', 1)
                 ->order_by('name', 'ASC');
                 
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id)
                     ->or_where('agency_id IS NULL');
        }
        
        $form_schemas = $this->db->get()->result_array();
        
        $schema_options = ['' => 'Select Form Schema'];
        foreach ($form_schemas as $schema) {
            $schema_options[$schema['id']] = $schema['name'];
        }

        $section_types = [
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

        return [
            'form_schemas' => $schema_options,
            'section_types' => $section_types,
            'row' => $row
        ];
    }

    public function create_modify_params($params)
    {
        if (empty($params['sort_order'])) {
            $params['sort_order'] = 0;
        }
        if (!isset($params['enabled'])) {
            $params['enabled'] = 1;
        }
        
        $params['created_at'] = date('Y-m-d H:i:s');
        
        return $params;
    }

    public function update_modify_params($params)
    {
        $params['updated_at'] = date('Y-m-d H:i:s');
        return $params;
    }

    public function create_custom_section() 
    {
        $this->session->set_userdata('return_to_template_sections', current_url());
        redirect('agency/test_form_builder/add?source=template_sections');
    }

    public function get_form_schemas() 
    {
        $agency_id = $this->session->userdata('agency_id');
        
        $this->db->select('id, name, form_element_id, created_at')
                 ->from('sys_form_schemas')
                 ->where('enabled', 1)
                 ->order_by('name', 'ASC');
                 
        if ($agency_id) {
            $this->db->where('agency_id', $agency_id)
                     ->or_where('agency_id IS NULL');
        }
        
        $schemas = $this->db->get()->result_array();
        
        $this->output_json([
            'success' => true,
            'data' => $schemas,
            'new_forms_count' => 0
        ]);
    }

    public function create_success_extra($id) 
    {
        parent::create_success_extra($id);
        
        $return_url = $this->session->userdata('return_to_template_sections');
        if ($return_url) {
            $this->session->set_flashdata('form_created', true);
            $this->session->set_flashdata('new_form_id', $id);
            $this->session->set_flashdata('new_form_name', $this->input->post('name'));
            $this->session->unset_userdata('return_to_template_sections');
        }
    }

    public function get_sections_by_type($type = null)
    {
        $agency_id = $this->session->userdata('agency_id');
        
        $this->db->select('mod_template_sections.*, sys_form_schemas.name as schema_name')
                 ->from('mod_template_sections')
                 ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                 ->where('mod_template_sections.enabled', 1)
                 ->where('mod_template_sections.removed', 0);
        
        if ($agency_id) {
            $this->db->where('sys_form_schemas.agency_id', $agency_id);
        }
        
        if ($type && $type !== 'all') {
            $this->db->where('mod_template_sections.section_type', $type);
        }
        
        $this->db->order_by('mod_template_sections.name', 'ASC');
        
        $sections = $this->db->get()->result();
        
        $this->output_json([
            'success' => true,
            'data' => $sections,
            'count' => count($sections)
        ]);
    }


    public function get($id = null, $single = false, $params = array())
    {
        if ($id != null) {
            $this->db->where('id', $id);
        }

        $this->db->select('mod_template_sections.*, sys_form_schemas.name as schema_name')
                 ->from('mod_template_sections')
                 ->join('sys_form_schemas', 'sys_form_schemas.id = mod_template_sections.schema_id', 'left')
                 ->where('mod_template_sections.removed', 0);

        if ($single) {
            return $this->db->get()->row();
        }

        return $this->db->get()->result();
    }

    public function disable($id) 
    {
        
        $row = $this->{$this->model}->get_by_id($id);
        
        if (!$row) {
            if (!is_ajax()) {
                flash_notification(lang('access_denied_description'), 'warning');
            } else {
                http_response_code(422);
                echo json_encode([
                    'success' => 0,
                    'error' => [
                        'header' => lang('access_denied_heading'),
                        'body' => lang('access_denied_description')
                    ]
                ]);
            }
            return;
        }

        if (!$this->disable_extra_before($row)) {
            return FALSE;
        }

        $messageParams = array('name' => $row->name);

        $this->db->trans_start();
        
        $result = $this->{$this->model}->disable($id);
        
        if ($result) {
            $this->cascade_disable_section_to_templates($id, $row->name, $row->schema_id);
            Logger::log('Disabled template section and related items: ' . $row->name, array('id' => $id));
        }
        
        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE && $result) {
            if (!is_ajax()) {
                flash_notification(langs($this->pageName . '_disable_success_description', $messageParams), 'success');
            }
            $this->disable_extra_success($row);
        } else {
            Anomalies::log('Failed to disable template section and related items: ' . $row->name, $this->db->last_query());
            if (!is_ajax()) {
                flash_notification(langs($this->pageName . '_disable_failed_description', $messageParams), 'error');
            } else {
                http_response_code(422);
                echo json_encode([
                    'success' => 0,
                    'header' => lang($this->pageName . '_disable_failed_heading'),
                    'body' => str_replace('{name}', $row->name, lang($this->pageName . '_disable_failed_description'))
                ]);
            }
        }

        if (is_ajax()) {
            http_response_code(200);
            echo json_encode(['success' => 1]);
        } else {
            redir($this->pageName);
        }
    }

    public function enable($id) 
    {
        
        $row = $this->{$this->model}->get_by_id($id);
        
        if (!$row) {
            if (!is_ajax()) {
                flash_notification(lang('access_denied_description'), 'warning');
            } else {
                http_response_code(422);
                echo json_encode([
                    'success' => 0,
                    'error' => [
                        'header' => lang('access_denied_heading'),
                        'body' => lang('access_denied_description')
                    ]
                ]);
            }
            return;
        }

        if (!$this->enable_extra_before($row)) {
            return FALSE;
        }

        $messageParams = array('name' => $row->name);

        $this->db->trans_start();
        
        $result = $this->{$this->model}->enable($id);
        
        if ($result) {
            $this->cascade_enable_section_to_templates($id, $row->name, $row->schema_id);
            Logger::log('Enabled template section and related items: ' . $row->name, array('id' => $id));
        }
        
        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE && $result) {
            if (!is_ajax()) {
                flash_notification(langs($this->pageName . '_enable_success_description', $messageParams), 'success');
            }
            $this->enable_extra_success($row);
        } else {
            Anomalies::log('Failed to enable template section and related items: ' . $row->name, $this->db->last_query());
            if (!is_ajax()) {
                flash_notification(langs($this->pageName . '_enable_failed_description', $messageParams), 'error');
            } else {
                http_response_code(422);
                echo json_encode([
                    'success' => 0,
                    'header' => lang($this->pageName . '_enable_failed_heading'),
                    'body' => str_replace('{name}', $row->name, lang($this->pageName . '_enable_failed_description'))
                ]);
            }
        }

        if (is_ajax()) {
            http_response_code(200);
            echo json_encode(['success' => 1]);
        } else {
            redir($this->pageName);
        }
    }

    private function cascade_disable_section_to_templates($section_id, $section_name, $schema_id) 
    {
        
        $affected_templates = [];
        
        $direct_templates = $this->db->select('id, name')
                                    ->from('mod_layouts')
                                    ->where('schema_id', $schema_id)
                                    ->where('enabled', 1)
                                    ->where('removed', 0)
                                    ->get()
                                    ->result();
        
        foreach ($direct_templates as $template) {
            $this->db->where('id', $template->id)
                    ->update('mod_layouts', ['enabled' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
            
            if ($this->db->affected_rows() > 0) {
                $affected_templates[] = $template->name;
                Logger::log('Cascaded disable from section to template: ' . $template->name, [
                    'section_id' => $section_id,
                    'section_name' => $section_name,
                    'template_id' => $template->id
                ]);
            }
        }
        
        return $affected_templates;
    }

    private function cascade_enable_section_to_templates($section_id, $section_name, $schema_id) 
    {
        
        $affected_templates = [];
        
        $direct_templates = $this->db->select('id, name')
                                    ->from('mod_layouts')
                                    ->where('schema_id', $schema_id)
                                    ->where('enabled', 0)
                                    ->where('removed', 0)
                                    ->get()
                                    ->result();
        
        foreach ($direct_templates as $template) {
            $this->db->where('id', $template->id)
                    ->update('mod_layouts', ['enabled' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
            
            if ($this->db->affected_rows() > 0) {
                $affected_templates[] = $template->name;
                Logger::log('Cascaded enable from section to template: ' . $template->name, [
                    'section_id' => $section_id,
                    'section_name' => $section_name,
                    'template_id' => $template->id
                ]);
            }
        }
        
        return $affected_templates;
    }

    public function remove($id) 
    {
        
        $row = $this->{$this->model}->get_by_id($id);
        
        if (!$row) {
            flash_notification(lang('access_denied_description'), 'warning');
            redir($this->pageName);
            return FALSE;
        }

        if (!$this->remove_extra_before($row)) {
            redir($this->pageName);
            return FALSE;
        }

        $messageParams = array('name' => $row->name);

        $this->db->trans_start();
        
        $result = $this->{$this->model}->remove($id);
        
        if ($result) {
            $this->cascade_delete_section_to_templates($id, $row->name, $row->schema_id);
            Logger::log('Removed template section and related items: ' . $row->name, array('id' => $id));
        }
        
        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE && $result) {
            flash_notification(langs($this->pageName . '_remove_success_description', $messageParams), 'success');
            $this->remove_extra_success($row);
        } else {
            Anomalies::log('Failed to remove template section and related items: ' . $row->name, $this->db->last_query());
            flash_notification(langs($this->pageName . '_remove_failed_description', $messageParams), 'error');
        }

        redir($this->pageName);
    }

    private function cascade_delete_section_to_templates($section_id, $section_name, $schema_id) 
    {
        
        $affected_templates = [];
        
        $direct_templates = $this->db->select('id, name')
                                    ->from('mod_layouts')
                                    ->where('schema_id', $schema_id)
                                    ->where('removed', 0)
                                    ->get()
                                    ->result();
        
        foreach ($direct_templates as $template) {
            $this->db->where('id', $template->id)
                    ->update('mod_layouts', [
                        'removed' => 1, 
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            
            if ($this->db->affected_rows() > 0) {
                $affected_templates[] = $template->name;
                Logger::log('Cascaded delete from section to template: ' . $template->name, [
                    'section_id' => $section_id,
                    'section_name' => $section_name,
                    'template_id' => $template->id
                ]);
            }
        }
        
        return $affected_templates;
    }

    public function remove_extra_before($row) 
    {
        return TRUE;
    }

    public function remove_extra_success($row) 
    {
        return TRUE;
    }

    public function test_agency_setup()
    {
        $this->session->set_userdata('agency_id', 1);
    
    }

    public function test_schema_loading($section_id = 59)
    {
        $row = $this->{$this->model}->get_by_id($section_id);
        echo "Section: " . ($row->name ?? 'Not found') . "<br>";
        echo "Schema ID: " . ($row->schema_id ?? 'None') . "<br>";
        
        if (!empty($row->schema_id)) {
            $schema_row = $this->db->get_where('sys_form_schemas', ['id' => $row->schema_id])->row();
            echo "Schema found: " . (!empty($schema_row) ? 'YES - ' . $schema_row->name : 'NO') . "<br>";
            
            if ($schema_row) {
                $schema = json_decode($schema_row->schema ?? '{}', true);
                echo "Schema fields: " . count($schema['fields'] ?? []) . "<br>";
                
                $this->load->library('Form_builder');
                $form = $this->form_builder::make()
                    ->set_schema($schema)
                    ->make_form();
                    
                echo "Form generated: " . (!empty($form->form_view) ? 'YES' : 'NO') . "<br>";
            }
        }
    }

    private function generate_debug_form_data($schema)
    {
        $debug_data = [];
        
        if (!empty($schema['fields'])) {
            foreach ($schema['fields'] as $field) {
                if (isset($field['name'])) {
                    $field_name = $field['name'];
                    switch ($field['type'] ?? 'text') {
                        case 'textarea':
                        case 'ckeditor':
                            $debug_data[$field_name] = "Sample content for {$field['label']}";
                            break;
                        case 'select':
                        case 'dropdown':
                            $debug_data[$field_name] = "option1";
                            break;
                        case 'checkbox':
                            $debug_data[$field_name] = "1";
                            break;
                        default:
                            $debug_data[$field_name] = "Sample {$field['label']}";
                            break;
                    }
                }
            }
        }
        
        return $debug_data;
    }


}
?>