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
        
        // ✅ Set the correct folder for views
        $this->folder = 'admin';
    }

    private function setup_listing() 
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
                    return $section_types[$value] ?? $value;
                }
            ),
            'schema_name' => array('label' => 'Form Schema', 'sort' => true),
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

        $this->listActions = array(
          
            'edit' => array(
                'label' => lang('label_edit'),
                'url' => url($this->pageName . '/edit/{id}'),
                'icon' => 'fa-edit',
                'class' => 'edit-row'
            ),
            'enable' => array(
                'label' => lang('label_enable'),
                'url' => url($this->pageName . '/enable/{id}'),
                'icon' => 'fa-eye',
                'class' => 'enable-row btn-enable',
                'function' => (function ($str, $row) {
                    return ($row->enabled) ? false : $str;
                })
            ),
            'disable' => array(
                'label' => lang('label_disable'),
                'url' => url($this->pageName . '/disable/{id}'),
                'icon' => 'fa-eye-slash',
                'class' => 'disable-row btn-disable',
                'function' => (function ($str, $row) {
                    return (!$row->enabled) ? false : $str;
                })
            ),
            'delete' => array(
                'label' => lang('label_delete'),
                'url' => url($this->pageName . '/remove/{id}'),
                'icon' => 'fa-trash-o',
                'class' => 'delete-row btn-delete'
            )
        );

        $this->filters = array(
            'search' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array('mts.name', 'mts.code', 'sfs.name')
            ),
            'section_type' => array(
                'label' => 'Section Type',
                'type' => 'dropdown',
                'field' => 'mts.section_type',
                'options' => $section_types
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
            [
                'title' => lang($this->pageName . '_heading') ?: 'Template Sections',
                'url' => redir($this->pageName, true)
            ]
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
        $search = ($search !== null && $search !== '') ? trim($search) : null;

        $section_type = $this->input->get('section_type');
        $show_disabled = $this->input->get('show_disabled');

        $sort_field = $this->input->get('sort_field') ?: 'id';
        $sort_order = strtoupper($this->input->get('sort_order') ?: 'ASC');

        if (!in_array($sort_field, ['id', 'name', 'code', 'section_type', 'schema_name', 'enabled'])) {
            $sort_field = 'id';
        }
        if (!in_array($sort_order, ['ASC', 'DESC'])) {
            $sort_order = 'ASC';
        }

        // Build query with joins
        $this->db->select('mts.*, sfs.name as schema_name')
                 ->from('mod_template_sections as mts')
                 ->join('sys_form_schemas as sfs', 'sfs.id = mts.schema_id', 'left')
                 ->where('mts.removed', 0);

        if ($search) {
            $this->db->group_start();
            $this->db->like('mts.name', $search);
            $this->db->or_like('mts.code', $search);
            $this->db->or_like('sfs.name', $search);
            $this->db->group_end();
        }

        if ($section_type) {
            $this->db->where('mts.section_type', $section_type);
        }

        if ($show_disabled !== null) {
            $this->db->where('mts.enabled', $show_disabled ? 1 : 0);
        }

        $this->db->order_by($sort_field, $sort_order);
        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        $rows = $query->result();

        // Total count
        $total_query = clone $this->db;
        $total = $total_query->count_all_results();

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

    /**
     * Quick Manage - For Add/Edit in modal
     */
    public function ajax_quick_manage($id = 0)
    {
        $row = $this->{$this->model}->get_by_id($id);
        
        if (!$row && $id > 0) {
            ajax_return(['success' => false, 'error' => 'Section not found']);
            return;
        }

        if (!$row) {
            $row = (object)[
                'id' => 0,
                'name' => '',
                'code' => '',
                'schema_id' => '',
                'section_type' => '',
                'category' => '',
                'description' => '',
                'sort_order' => 0,
                'enabled' => 1
            ];
        }

        $identifier = !empty($row) && !empty($row->name) ? $row->name : '';
        $data = array(
            'row' => $row,
            'identifier' => $identifier
        );

        // Get extra data for the form
        $extra = $this->quick_manage_extra($id, $row);
        $data = array_merge($data, $extra);

        // Load the quick manage view
        $html = $this->load->view('admin/template_sections/ajax_manage', $data, TRUE);

        $this->output->set_output($html);
    }

public function preview($id)
{
    // Use the quick manage system which already has CSS loaded
    $this->ajax_quick_manage($id);
}

    /**
     * Get form preview for AJAX calls
     */
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

    // ✅ CUSTOM SECTION FUNCTIONALITY
    public function create_custom_section() 
    {
        $this->session->set_userdata('return_to_template_sections', current_url());
        redirect('admin/test_form_builder/add?source=template_sections');
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

    private function output_json($data) 
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    /**
     * Override disable method to cascade to related templates
     */
    public function disable($id) 
    {
        log_message('debug', '=== CASCADING DISABLE START FOR SECTION: ' . $id . ' ===');
        
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

    /**
     * Override enable method to cascade to related templates
     */
    public function enable($id) 
    {
        log_message('debug', '=== CASCADING ENABLE START FOR SECTION: ' . $id . ' ===');
        
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

    /**
     * Cascade disable section to related templates
     */
    private function cascade_disable_section_to_templates($section_id, $section_name, $schema_id) 
    {
        log_message('debug', 'Cascading disable section to templates for section: ' . $section_id);
        
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
        
        log_message('debug', 'Disabled ' . count($affected_templates) . ' templates for section: ' . $section_id);
        return $affected_templates;
    }

    /**
     * Cascade enable section to related templates
     */
    private function cascade_enable_section_to_templates($section_id, $section_name, $schema_id) 
    {
        log_message('debug', 'Cascading enable section to templates for section: ' . $section_id);
        
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
        
        log_message('debug', 'Enabled ' . count($affected_templates) . ' templates for section: ' . $section_id);
        return $affected_templates;
    }

    /**
     * Override remove method to cascade delete to related templates
     */
    public function remove($id) 
    {
        log_message('debug', '=== CASCADING DELETE START FOR SECTION: ' . $id . ' ===');
        
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

    /**
     * Cascade delete section to related templates
     */
    private function cascade_delete_section_to_templates($section_id, $section_name, $schema_id) 
    {
        log_message('debug', 'Cascading delete section to templates for section: ' . $section_id);
        
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
        
        log_message('debug', 'Deleted ' . count($affected_templates) . ' templates for section: ' . $section_id);
        return $affected_templates;
    }

    /**
     * Extra before remove hook
     */
    public function remove_extra_before($row) 
    {
        log_message('debug', 'Section remove_extra_before called for: ' . $row->name);
        return TRUE;
    }

    /**
     * Extra after remove success hook
     */
    public function remove_extra_success($row) 
    {
        log_message('debug', 'Section remove_extra_success called for: ' . $row->name);
        return TRUE;
    }

    public function test_agency_setup()
    {
        $this->session->set_userdata('agency_id', 1);
        echo "Agency ID set to 1 for testing. <a href='".site_url('admin/template_sections')."'>Go to Template Sections</a>";
    }
}
?>