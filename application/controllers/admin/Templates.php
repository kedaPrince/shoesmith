<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Hybrid_Query_Result {
    private $data;
    private $num_rows;
    
    public function __construct($data) {
        $this->data = $data;
        $this->num_rows = count($data);
    }
    
    public function result() {
        return $this->data;
    }
    
    public function result_array() {
        return array_map(function($item) {
            return (array) $item;
        }, $this->data);
    }
    
    public function num_rows() {
        return $this->num_rows;
    }
    
    public function row() {
        return !empty($this->data) ? $this->data[0] : null;
    }
    
    public function row_array() {
        return !empty($this->data) ? (array) $this->data[0] : null;
    }
}

class Templates extends CRUD_Controller 
{
    public $pageName = 'templates';
    public $group = 'templates';
    public $view = '';
    public $model = 'Model_templates';
    public $sorting = array('name' => 'ASC');
    public $singular = 'template';
    public $plural = 'templates';
    public $seoFields = false;
    public $quickManage = true;
    public $quickManageSize = 5;

    public function __construct() 
    {
        parent::__construct();
        
        $this->load->model($this->folder . '/' . $this->model);
        $this->load->model('admin/Model_template_instances');
        $this->load->model('admin/Model_agency_templates');
        
        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true)
        );
        $this->load->library('Form_builder');
        
        $this->setup_listing();
        $this->setup_fields();
    }

    public function setup_listing() 
    {
        $this->listFields = array(
            'id' => array(
                'label' => 'ID',
                'sort' => true
            ),
            'template_name' => array(
                'label' => 'Template Name',
                'sort' => true,
                'function' => function($value, $row) {
                    $template_type = isset($row->template_type) ? $row->template_type : 'single';
                    if ($template_type === 'composite') {
                        return isset($row->template_name) ? $row->template_name : 'Unnamed Composite Template';
                    }
                    return isset($row->name) ? $row->name : 'Unnamed Template';
                }
            ),
            'template_type' => array(
                'label' => 'Type',
                'sort' => true,
                'function' => function($value, $row) {
                    $template_type = isset($row->template_type) ? $row->template_type : 'single';
                    $badge = $template_type === 'composite' ? 'warning' : 'info';
                    $label = $template_type === 'composite' ? 'Composite' : 'Single';
                    return '<span class="badge badge-' . $badge . '">' . $label . '</span>';
                }
            ),
            'schema_name' => array(
                'label' => 'Form Schema',
                'sort' => true,
                'function' => function($value, $row) {
                    $template_type = isset($row->template_type) ? $row->template_type : 'single';
                    if ($template_type === 'composite') {
                        return '<span class="text-muted">Multiple Sections</span>';
                    }
                    return isset($value) ? $value : 'No schema';
                }
            ),
            'section_count' => array(
                'label' => 'Sections',
                'sort' => true,
                'function' => function($value, $row) {
                    $template_type = isset($row->template_type) ? $row->template_type : 'single';
                    if ($template_type === 'composite') {
                        return isset($value) ? $value : '0';
                    }
                    return '<span class="text-muted">-</span>';
                }
            ),
            'agency_id' => array(
                'label' => 'Agency ID',
                'sort' => true,
                'function' => function($value, $row) {
                    return isset($value) ? $value : '-';
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
            ),
            'preview' => array(
                'label' => 'Preview',
                'sort' => false,
                'function' => function($value, $row) {
                    $template_type = isset($row->template_type) ? $row->template_type : 'single';
                    if ($template_type === 'composite') {
                        $template_name = isset($row->template_name) ? htmlspecialchars($row->template_name) : 'Composite Template';
                        return '<button type="button" class="btn btn-sm btn-warning preview-composite-template" 
                                data-id="' . $row->id . '" data-name="' . $template_name . '">
                                <i class="fa fa-eye"></i> Preview
                            </button>';
                    } else {
                        $name = isset($row->name) ? htmlspecialchars($row->name) : 'Template';
                        return '<a href="' . site_url('admin/templates/preview/' . $row->id) . '" class="btn btn-sm btn-info">
                                <i class="fa fa-eye"></i> Preview
                            </a>';
                    }
                }
            ),
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

        $this->filters = array(
            'search' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => 'search'
            ),
            'template_type' => array(
                'label' => 'Template Type',
                'type' => 'dropdown',
                'field' => 'template_type',
                'options' => array(
                    '' => 'All Types',
                    'single' => 'Single Schema',
                    'composite' => 'Composite'
                )
            )
        );
    }

    public function setup_fields() 
    {
        $this->formFields = array(
            'main' => array(
                'name' => 'trim',
                'code' => 'trim|alpha_dash',
                'schema_id' => 'trim|numeric',
                'description' => 'trim',
                'preview_image' => 'trim',
                'enabled' => 'trim|numeric'
            )
        );
    }

    public function build_params($extra = array(), $group = 'main')
    {
        $params = array();
        
        $fields = ['name', 'code', 'schema_id', 'description', 'preview_image', 'enabled'];
        
        foreach ($fields as $field) {
            if ($this->input->post($field) !== null) {
                $params[$field] = $this->input->post($field);
            }
        }
        
        if (!empty($extra)) {
            $params = array_merge($params, $extra);
        }
        
        return $params;
    }

    public function setup_validation($page = 'add', $group = 'main')
    {
        parent::setup_validation($page, $group);
        
        if ($page === 'add') {
            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('schema_id', 'Schema', 'required|numeric');
        } else {
            $this->form_validation->set_rules('name', 'Name', 'trim');
            $this->form_validation->set_rules('schema_id', 'Schema', 'numeric');
        }
    }

    public function index() 
    {
        log_message('debug', '=== TEMPLATES INDEX CALLED ===');
        
        if ($this->input->is_ajax_request() || 
            $this->input->get('draw') !== null || 
            $this->input->get('page') !== null) {
            log_message('debug', 'AJAX request detected - calling ajax_results()');
            $this->ajax_results();
            return;
        }

        log_message('debug', 'Regular page load - showing listing view');

        $this->breadcrumbs = [
            [
                'title' => lang($this->pageName . '_heading') ?: 'Templates',
                'url' => redir($this->pageName, true)
            ]
        ];

        $this->view = 'listing';
        $this->load->view($this->folder . '/view_header');
        $this->load->view('cms/crud/view_list', [
            'heading' => lang($this->pageName . '_heading') ?: 'All Templates',
            'noRows' => lang($this->pageName . '_no_rows'),
            'items' => []
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

    public function get_all($section = '')
    {
        log_message('debug', '=== OVERRIDDEN GET_ALL CALLED - USING HYBRID DATA ===');
        
        $filters = [];
        $all_templates = $this->{$this->model}->get_hybrid_templates($filters);
        
        return new Hybrid_Query_Result($all_templates);
    }

    public function ajax_pager_fetch_batch($batch = 1, $section = "", $template = "listing")
    {
        log_message('debug', '=== OVERRIDDEN AJAX_PAGER_FETCH_BATCH CALLED - USING HYBRID DATA ===');
        
        $filters = [];
        $all_templates = $this->{$this->model}->get_hybrid_templates($filters);
        
        $amount = count($all_templates);
        
        $limit = 20;
        $offset = ($batch - 1) * $limit;
        $paginated_templates = array_slice($all_templates, $offset, $limit);
        
        $query = new Hybrid_Query_Result($paginated_templates);
        
        $html = $this->load->view('cms/crud/ajax_' . $template . '_rows', array(
            'query' => $query,
            'batch' => $batch,
            'amount' => $amount
        ), TRUE);

        $this->output->set_output($html);
    }

    public function pager_fetch_batch_extra($query, $batch, $section)
    {
        log_message('debug', '=== PAGER_FETCH_BATCH_EXTRA CALLED ===');
        
        if ($query instanceof Hybrid_Query_Result) {
            return $query;
        }
        
        $filters = [];
        $all_templates = $this->{$this->model}->get_hybrid_templates($filters);
        
        return new Hybrid_Query_Result($all_templates);
    }

    public function ajax_results() 
    {
        log_message('debug', '=== AJAX RESULTS USING MODEL HYBRID METHOD ===');

        $page   = max(1, (int) $this->input->get('page'));
        $limit  = min(100, max(1, (int) $this->input->get('limit')));
        $offset = ($page - 1) * $limit;
        $search = $this->input->get('search');
        $template_type = $this->input->get('template_type');
        $sort_field = $this->input->get('sort_field') ?: 'id';
        $sort_order = strtoupper($this->input->get('sort_order') ?: 'DESC');

        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($template_type) $filters['template_type'] = $template_type;

        log_message('debug', 'Filters: ' . print_r($filters, true));

        $all_templates = $this->{$this->model}->get_hybrid_templates($filters);

        log_message('debug', 'Raw hybrid templates count: ' . count($all_templates));

        $valid_sort_fields = ['id', 'name', 'template_name', 'template_type', 'schema_name', 'section_count', 'agency_id', 'created_at', 'enabled'];
        if (in_array($sort_field, $valid_sort_fields)) {
            usort($all_templates, function($a, $b) use ($sort_field, $sort_order) {
                $valA = $a->{$sort_field} ?? '';
                $valB = $b->{$sort_field} ?? '';
                
                if ($sort_order === 'ASC') {
                    return $valA <=> $valB;
                } else {
                    return $valB <=> $valA;
                }
            });
        }

        $total = count($all_templates);
        $paginated_templates = array_slice($all_templates, $offset, $limit);

        log_message('debug', 'AJAX RESULTS: Showing ' . count($paginated_templates) . ' of ' . $total . ' templates');

        $response = [
            'success' => true,
            'data' => $paginated_templates,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'last_page' => ceil($total / $limit)
            ]
        ];

        $this->output_json($response);
    }

    public function disable($id) 
    {
        log_message('debug', '=== CASCADING DISABLE START FOR TEMPLATE: ' . $id . ' ===');
        
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

        $messageParams = array('name' => $row->name ?? $row->template_name ?? 'Template');

        $this->db->trans_start();
        
        $result = $this->{$this->model}->disable($id);
        
        if ($result) {
            if (($row->template_type ?? 'single') === 'composite') {
                $this->cascade_disable_composite_template($id, $row->template_name ?? 'Composite Template');
            } else {
                $this->cascade_disable_single_template($id, $row->name ?? 'Template');
            }
            
            Logger::log('Disabled template and related instances: ' . ($row->name ?? $row->template_name), array('id' => $id));
        }
        
        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE && $result) {
            if (!is_ajax()) {
                flash_notification(langs($this->pageName . '_disable_success_description', $messageParams), 'success');
            }
            $this->disable_extra_success($row);
        } else {
            Anomalies::log('Failed to disable template and related instances: ' . ($row->name ?? $row->template_name), $this->db->last_query());
            if (!is_ajax()) {
                flash_notification(langs($this->pageName . '_disable_failed_description', $messageParams), 'error');
            } else {
                http_response_code(422);
                echo json_encode([
                    'success' => 0,
                    'header' => lang($this->pageName . '_disable_failed_heading'),
                    'body' => str_replace('{name}', $row->name ?? $row->template_name, lang($this->pageName . '_disable_failed_description'))
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
        log_message('debug', '=== CASCADING ENABLE START FOR TEMPLATE: ' . $id . ' ===');
        
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

        $messageParams = array('name' => $row->name ?? $row->template_name ?? 'Template');

        $this->db->trans_start();
        
        $result = $this->{$this->model}->enable($id);
        
        if ($result) {
            if (($row->template_type ?? 'single') === 'composite') {
                $this->cascade_enable_composite_template($id, $row->template_name ?? 'Composite Template');
            } else {
                $this->cascade_enable_single_template($id, $row->name ?? 'Template');
            }
            
            Logger::log('Enabled template and related instances: ' . ($row->name ?? $row->template_name), array('id' => $id));
        }
        
        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE && $result) {
            if (!is_ajax()) {
                flash_notification(langs($this->pageName . '_enable_success_description', $messageParams), 'success');
            }
            $this->enable_extra_success($row);
        } else {
            Anomalies::log('Failed to enable template and related instances: ' . ($row->name ?? $row->template_name), $this->db->last_query());
            if (!is_ajax()) {
                flash_notification(langs($this->pageName . '_enable_failed_description', $messageParams), 'error');
            } else {
                http_response_code(422);
                echo json_encode([
                    'success' => 0,
                    'header' => lang($this->pageName . '_enable_failed_heading'),
                    'body' => str_replace('{name}', $row->name ?? $row->template_name, lang($this->pageName . '_enable_failed_description'))
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

    private function cascade_disable_composite_template($template_id, $template_name) 
    {
        log_message('debug', 'Cascading disable for composite template: ' . $template_id);
        
        $instances = $this->db->select('id, name')
                             ->from('template_instances')
                             ->where('template_id', $template_id)
                             ->where('enabled', 1)
                             ->where('removed', 0)
                             ->get()
                             ->result();
        
        $disabled_count = 0;
        foreach ($instances as $instance) {
            $this->db->where('id', $instance->id)
                    ->update('template_instances', ['enabled' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
            
            if ($this->db->affected_rows() > 0) {
                $disabled_count++;
                Logger::log('Cascaded disable to template instance: ' . $instance->name, [
                    'template_id' => $template_id,
                    'template_name' => $template_name,
                    'instance_id' => $instance->id
                ]);
            }
        }
        
        log_message('debug', 'Disabled ' . $disabled_count . ' instances for composite template: ' . $template_id);
        return $disabled_count;
    }

    private function cascade_disable_single_template($template_id, $template_name) 
    {
        log_message('debug', 'Cascading disable for single template: ' . $template_id);
        
        $instances = $this->db->select('id, name')
                             ->from('template_instances')
                             ->where('template_id', $template_id)
                             ->where('enabled', 1)
                             ->where('removed', 0)
                             ->get()
                             ->result();
        
        $disabled_count = 0;
        foreach ($instances as $instance) {
            $this->db->where('id', $instance->id)
                    ->update('template_instances', ['enabled' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
            
            if ($this->db->affected_rows() > 0) {
                $disabled_count++;
                Logger::log('Cascaded disable to template instance: ' . $instance->name, [
                    'template_id' => $template_id,
                    'template_name' => $template_name,
                    'instance_id' => $instance->id
                ]);
            }
        }
        
        log_message('debug', 'Disabled ' . $disabled_count . ' instances for single template: ' . $template_id);
        return $disabled_count;
    }

    private function cascade_enable_composite_template($template_id, $template_name) 
    {
        log_message('debug', 'Cascading enable for composite template: ' . $template_id);
        
        $instances = $this->db->select('id, name')
                             ->from('template_instances')
                             ->where('template_id', $template_id)
                             ->where('enabled', 0)
                             ->where('removed', 0)
                             ->get()
                             ->result();
        
        $enabled_count = 0;
        foreach ($instances as $instance) {
            $this->db->where('id', $instance->id)
                    ->update('template_instances', ['enabled' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
            
            if ($this->db->affected_rows() > 0) {
                $enabled_count++;
                Logger::log('Cascaded enable to template instance: ' . $instance->name, [
                    'template_id' => $template_id,
                    'template_name' => $template_name,
                    'instance_id' => $instance->id
                ]);
            }
        }
        
        log_message('debug', 'Enabled ' . $enabled_count . ' instances for composite template: ' . $template_id);
        return $enabled_count;
    }

    private function cascade_enable_single_template($template_id, $template_name) 
    {
        log_message('debug', 'Cascading enable for single template: ' . $template_id);
        
        $instances = $this->db->select('id, name')
                             ->from('template_instances')
                             ->where('template_id', $template_id)
                             ->where('enabled', 0)
                             ->where('removed', 0)
                             ->get()
                             ->result();
        
        $enabled_count = 0;
        foreach ($instances as $instance) {
            $this->db->where('id', $instance->id)
                    ->update('template_instances', ['enabled' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
            
            if ($this->db->affected_rows() > 0) {
                $enabled_count++;
                Logger::log('Cascaded enable to template instance: ' . $instance->name, [
                    'template_id' => $template_id,
                    'template_name' => $template_name,
                    'instance_id' => $instance->id
                ]);
            }
        }
        
        log_message('debug', 'Enabled ' . $enabled_count . ' instances for single template: ' . $template_id);
        return $enabled_count;
    }

    public function disable_extra_before($row) 
    {
        log_message('debug', 'Template disable_extra_before called for: ' . ($row->name ?? $row->template_name));
        return TRUE;
    }

    public function disable_extra_success($row) 
    {
        log_message('debug', 'Template disable_extra_success called for: ' . ($row->name ?? $row->template_name));
        return TRUE;
    }

    public function enable_extra_before($row) 
    {
        log_message('debug', 'Template enable_extra_before called for: ' . ($row->name ?? $row->template_name));
        return TRUE;
    }

    public function enable_extra_success($row) 
    {
        log_message('debug', 'Template enable_extra_success called for: ' . ($row->name ?? $row->template_name));
        return TRUE;
    }

public function quick_manage_extra($id, $row) 
{
    log_message('debug', '=== QUICK_MANAGE_EXTRA CALLED FOR TEMPLATE: ' . $id . ' ===');
    
    // ADD CACHE PREVENTION HEADERS
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache"); 
    header("Expires: 0");
    
    if (!empty($row->template_type) && $row->template_type === 'composite') {
        $result = $this->quick_manage_composite($id, $row);
    } else {
        $result = $this->quick_manage_single($id, $row);
    }

    // DEBUG: Log what data is actually being returned
    log_message('debug', '=== RETURNING DATA FOR TEMPLATE: ' . $id . ' ===');
    log_message('debug', 'Template ID in result: ' . $id);
    log_message('debug', 'Form data count: ' . count($result['debug_form_data'] ?? []));
    if (isset($result['debug_form_data']['about_job'])) {
        log_message('debug', 'About Job content: ' . substr($result['debug_form_data']['about_job'], 0, 50));
    }
    
    return $result;
}

 private function quick_manage_single($id, $row) 
{
    log_message('debug', '=== QUICK_MANAGE_SINGLE START for Template ID: ' . $id . ' ===');
    log_message('debug', 'Row ID: ' . ($row->id ?? 'NULL'));
    log_message('debug', 'Row Name: ' . ($row->name ?? 'NULL'));
    log_message('debug', 'Row Schema ID: ' . ($row->schema_id ?? 'NULL'));
    
    $current_schema_data = null;
    $current_schema_name = null;
    $form_data = [];
    
    if (!empty($id) && !empty($row->schema_id) && $row->schema_id != 0) {
        log_message('debug', 'Schema ID found: ' . $row->schema_id);
        
        $current_schema = $this->db->select('name, schema, scripts, styling, enabled')
                                 ->from('sys_form_schemas')
                                 ->where('id', $row->schema_id)
                                 ->get()
                                 ->row();
        
        if ($current_schema) {
            log_message('debug', 'Schema found: ' . $current_schema->name);
            
            $current_schema_name = $current_schema->name;
            try {
                $schema_data = json_decode($current_schema->schema, true);
                $styling = json_decode($current_schema->styling ?? '{}', true);
                $scripts = json_decode($current_schema->scripts ?? '[]', true);
                
                log_message('debug', 'Looking for template instances for ACTUAL template ID: ' . $id);
                
                $instance = $this->db->select('id, template_id, name, form_data')
                                   ->from('template_instances')
                                   ->where('template_id', $id)
                                   ->where('removed', 0)
                                   ->order_by('id', 'DESC')
                                   ->limit(1)
                                   ->get()
                                   ->row();
                
                if ($instance) {
                    log_message('debug', 'Instance FOUND for template ID ' . $id . ': Instance ID=' . $instance->id . ', Name=' . $instance->name);
                    log_message('debug', 'Instance Template ID: ' . $instance->template_id);
                    
                    if (!empty($instance->form_data)) {
                        $form_data = json_decode($instance->form_data, true);
                        
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            log_message('error', 'JSON decode error: ' . json_last_error_msg());
                            $form_data = [];
                        }
                        
                        log_message('debug', 'Loaded form data for template ' . $id . ': ' . count($form_data) . ' fields');
                        foreach ($form_data as $key => $value) {
                            log_message('debug', 'Field [' . $key . '] = "' . substr($value, 0, 50) . '"');
                        }
                    } else {
                        log_message('debug', 'Instance found but form_data is empty for template ID: ' . $id);
                    }
                } else {
                    log_message('debug', 'NO instance found for template ID: ' . $id);
                    log_message('debug', 'Checking if any instances exist in database...');
                    
                    $all_instances = $this->db->select('id, template_id, name')
                                            ->from('template_instances')
                                            ->where('removed', 0)
                                            ->get()
                                            ->result();
                    
                    log_message('debug', 'All instances in database: ' . count($all_instances));
                    foreach ($all_instances as $inst) {
                        log_message('debug', 'Instance: ID=' . $inst->id . ', Template ID=' . $inst->template_id . ', Name=' . $inst->name);
                    }
                }
                
                try {
                    log_message('debug', 'Building form with saved data for template ID: ' . $id);
                    
                    $form_builder = $this->form_builder::make()
                        ->set_schema($schema_data)
                        ->set_styling($styling)
                        ->set_scripts($scripts);
                    
                    $form_result = $form_builder->make_form($form_data);
                    
                    if (!empty($form_result->form_view)) {
                        $form_html = $form_result->form_view;
                        log_message('debug', 'Form HTML generated successfully for template ID: ' . $id);
                        
                        // Pre-populate textareas, inputs, and selects
                        $pre_pop_count = 0;
                        foreach ($form_data as $field_name => $field_value) {
                            log_message('debug', '  Attempting pre-pop for [' . $field_name . '] = "' . substr($field_value, 0, 30) . '"');
                            
                            // Textareas
                            $textarea_pattern = '/(<textarea\s+[^>]*name=["\']' . preg_quote($field_name, '/') . '["\'][^>]*>)(.*?)(<\/textarea>)/is';
                            if (preg_match($textarea_pattern, $form_html, $matches)) {
                                log_message('debug', '    ✓ MATCHED textarea for ' . $field_name . ' (old content: "' . substr($matches[2], 0, 30) . '")');
                                $new_textarea = $matches[1] . htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8') . $matches[3];
                                $form_html = str_replace($matches[0], $new_textarea, $form_html);
                                $pre_pop_count++;
                            } else {
                                // Inputs (text, email, etc.)
                                $input_pattern = '/(<input\s+[^>]*name=["\']' . preg_quote($field_name, '/') . '["\'][^>]*)(value=["\'][^"\']*["\'])/i';
                                if (preg_match($input_pattern, $form_html, $input_matches)) {
                                    $new_value_attr = 'value="' . htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8') . '"';
                                    $new_input = str_replace($input_matches[2], $new_value_attr, $input_matches[0]);
                                    $form_html = str_replace($input_matches[0], $new_input, $form_html);
                                    log_message('debug', '    ✓ Updated input for ' . $field_name);
                                    $pre_pop_count++;
                                } else {
                                    // Selects (basic single-select)
                                    $select_pattern = '/(<select\s+[^>]*name=["\']' . preg_quote($field_name, '/') . '["\'][^>]*>)(.*?<\/select>)/is';
                                    if (preg_match($select_pattern, $form_html, $select_matches)) {
                                        $options_html = $select_matches[2];
                                        $new_options = preg_replace('/<option\s+[^>]*value=["\']' . preg_quote($field_value, '/') . '["\'][^>]*>/i', '$0 selected="selected"', $options_html);
                                        $new_select = $select_matches[1] . $new_options . '</select>';
                                        $form_html = str_replace($select_matches[0], $new_select, $form_html);
                                        log_message('debug', '    ✓ Updated select for ' . $field_name);
                                        $pre_pop_count++;
                                    } else {
                                        log_message('debug', '    ✗ NO MATCH for ' . $field_name . ' - check form_builder output');
                                    }
                                }
                            }
                        }
                        log_message('debug', '  Pre-populated ' . $pre_pop_count . '/' . count($form_data) . ' fields for single template ' . $id);
                        
                        $form_html = preg_replace('/action="[^"]*"/', 'action="#"', $form_html);
                        $form_html = preg_replace('/<style><\/style>/', '', $form_html);
                        
                        $current_schema_data = $form_html;
                    } else {
                        log_message('error', 'Form builder returned empty form view for template ID: ' . $id);
                        $current_schema_data = '<div class="alert alert-danger">Form builder returned empty form</div>';
                    }
                } catch (Exception $e) {
                    log_message('error', 'Form builder exception for template ID ' . $id . ': ' . $e->getMessage());
                    $current_schema_data = '<div class="alert alert-danger">Form builder error: ' . $e->getMessage() . '</div>';
                }
                    
            } catch (Exception $e) {
                log_message('error', 'Template schema error for template ID ' . $id . ': ' . $e->getMessage());
                $current_schema_data = '<div class="alert alert-danger">Error loading form: ' . $e->getMessage() . '</div>';
            }
        } else {
            log_message('debug', 'Schema not found for ID: ' . $row->schema_id);
            $current_schema_data = '<div class="alert alert-warning">Form schema not found</div>';
        }
    } else {
        log_message('debug', 'Missing or invalid schema_id: ' . ($row->schema_id ?? 'NULL'));
        $current_schema_data = '<div class="alert alert-warning">No form schema assigned to this template</div>';
    }

    log_message('debug', '=== QUICK_MANAGE_SINGLE END - Template ID: ' . $id . ' - Form data count: ' . count($form_data) . ' ===');

    return [
        'row' => $row,
        'current_form_preview' => $current_schema_data,
        'current_schema_name' => $current_schema_name,
        'identifier' => !empty($row->name) ? $row->name : 'Template',
        'debug_form_data' => $form_data,
        'template_id' => $id
    ];
}

    private function quick_manage_composite($id, $row) 
{
    log_message('debug', '=== QUICK_MANAGE_COMPOSITE START for Template ID: ' . $id . ' ===');
    
    $all_sections_html = '';
    $all_form_data = [];
    
    $instance = $this->db->select('id, template_id, name, form_data')
                       ->from('template_instances')
                       ->where('template_id', $id)
                       ->where('removed', 0)
                       ->order_by('id', 'DESC')
                       ->limit(1)
                       ->get()
                       ->row();
    
    log_message('debug', 'Looking for template instance for SPECIFIC composite template ID: ' . $id);
    
    if ($instance) {
        log_message('debug', 'Instance FOUND for composite template ID ' . $id . ': Instance ID=' . $instance->id . ', Name=' . $instance->name);
        
        if (!empty($instance->form_data)) {
            $all_form_data = json_decode($instance->form_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'JSON decode error in composite template: ' . json_last_error_msg());
                $all_form_data = [];
            } else {
                log_message('debug', 'Loaded composite template form data for template ID ' . $id . ': ' . count($all_form_data) . ' fields');
                log_message('debug', 'Form data keys: ' . implode(', ', array_keys($all_form_data)));
                // DEBUG: Log the actual values
                foreach ($all_form_data as $key => $value) {
                    log_message('debug', 'Form data [' . $key . '] = "' . $value . '"');
                }
            }
        } else {
            log_message('debug', 'Instance found but form_data is empty for composite template ID: ' . $id);
        }
    } else {
        log_message('debug', 'NO instance found for composite template ID: ' . $id);
    }
    
        
        $template_with_sections = $this->Model_agency_templates->get_template_with_sections($id);
        
        if ($template_with_sections && !empty($template_with_sections->sections)) {
            log_message('debug', 'Found ' . count($template_with_sections->sections) . ' sections for composite template ID: ' . $id);
            
            foreach ($template_with_sections->sections as $section) {
                log_message('debug', 'Processing section: ' . $section->name . ' (Schema ID: ' . $section->schema_id . ')');
                
                if (!empty($section->schema_id)) {
                    $section_data = [];
                    $schema = $this->db->select('schema')
                                     ->from('sys_form_schemas')
                                     ->where('id', $section->schema_id)
                                     ->get()
                                     ->row();
                    
                    if ($schema) {
                        $schema_fields = json_decode($schema->schema, true);
                        if (isset($schema_fields[0]['fields'])) {
                            $field_names = array_keys($schema_fields[0]['fields']);
                            foreach ($field_names as $field_name) {
                                if (isset($all_form_data[$field_name])) {
                                    $section_data[$field_name] = $all_form_data[$field_name];
                                    log_message('debug', 'Setting field [' . $field_name . '] = "' . substr($all_form_data[$field_name], 0, 50) . '"');
                                }
                            }
                        }
                    }
                    
                    $section_form = $this->get_section_form_preview($section->schema_id, $id, $section_data);
                    $all_sections_html .= $this->wrap_section_form($section, $section_form);
                } else {
                    log_message('debug', 'Section has no schema_id: ' . $section->name);
                    $all_sections_html .= $this->wrap_section_form($section, '<div class="alert alert-warning">No form schema assigned to this section</div>');
                }
            }
        } else {
            $all_sections_html = '<div class="alert alert-warning">No sections found in this composite template</div>';
        }

        log_message('debug', '=== QUICK_MANAGE_COMPOSITE END - Template ID: ' . $id . ' - Form data count: ' . count($all_form_data) . ' ===');

        return [
            'row' => $row,
            'current_form_preview' => $all_sections_html,
            'current_schema_name' => 'Composite Template',
            'identifier' => !empty($row->template_name) ? $row->template_name : 'Composite Template',
            'debug_form_data' => $all_form_data
        ];
    }

private function get_section_form_preview($schema_id, $template_id, $form_data = [])
{
    try {
        log_message('debug', 'Getting section form preview for schema_id: ' . $schema_id . ', template_id: ' . $template_id);
        
        $schema_data = $this->db->select('schema, scripts, styling, name as schema_name')
                               ->from('sys_form_schemas')
                               ->where('id', $schema_id)
                               ->where('enabled', 1)
                               ->get()
                               ->row();

        if (!$schema_data) {
            return '<div class="alert alert-danger">Schema not found</div>';
        }

        $schema = json_decode($schema_data->schema, true);
        $styling = json_decode($schema_data->styling ?? '{}', true);
        $scripts = json_decode($schema_data->scripts ?? '[]', true);

        $form_builder = $this->form_builder::make()
            ->set_schema($schema)
            ->set_styling($styling)
            ->set_scripts($scripts);

        $form_result = $form_builder->make_form($form_data);
        $form_html = $form_result->form_view;

        // Pre-populate textareas, inputs, and selects
        $pre_pop_count = 0;
        foreach ($form_data as $field_name => $field_value) {
            log_message('debug', '  Attempting pre-pop for [' . $field_name . '] = "' . substr($field_value, 0, 30) . '"');
            
            // Textareas
            $textarea_pattern = '/(<textarea\s+[^>]*name=["\']' . preg_quote($field_name, '/') . '["\'][^>]*>)(.*?)(<\/textarea>)/is';
            if (preg_match($textarea_pattern, $form_html, $matches)) {
                log_message('debug', '    ✓ MATCHED textarea for ' . $field_name . ' (old content: "' . substr($matches[2], 0, 30) . '")');
                $new_textarea = $matches[1] . htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8') . $matches[3];
                $form_html = str_replace($matches[0], $new_textarea, $form_html);
                $pre_pop_count++;
            } else {
                // Inputs (text, email, etc.)
                $input_pattern = '/(<input\s+[^>]*name=["\']' . preg_quote($field_name, '/') . '["\'][^>]*)(value=["\'][^"\']*["\'])/i';
                if (preg_match($input_pattern, $form_html, $input_matches)) {
                    $new_value_attr = 'value="' . htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8') . '"';
                    $new_input = str_replace($input_matches[2], $new_value_attr, $input_matches[0]);
                    $form_html = str_replace($input_matches[0], $new_input, $form_html);
                    log_message('debug', '    ✓ Updated input for ' . $field_name);
                    $pre_pop_count++;
                } else {
                    // Selects (basic single-select)
                    $select_pattern = '/(<select\s+[^>]*name=["\']' . preg_quote($field_name, '/') . '["\'][^>]*>)(.*?<\/select>)/is';
                    if (preg_match($select_pattern, $form_html, $select_matches)) {
                        $options_html = $select_matches[2];
                        $new_options = preg_replace('/<option\s+[^>]*value=["\']' . preg_quote($field_value, '/') . '["\'][^>]*>/i', '$0 selected="selected"', $options_html);
                        $new_select = $select_matches[1] . $new_options . '</select>';
                        $form_html = str_replace($select_matches[0], $new_select, $form_html);
                        log_message('debug', '    ✓ Updated select for ' . $field_name);
                        $pre_pop_count++;
                    } else {
                        log_message('debug', '    ✗ NO MATCH for ' . $field_name . ' - check form_builder output');
                    }
                }
            }
        }
        log_message('debug', '  Pre-populated ' . $pre_pop_count . '/' . count($form_data) . ' fields for section in template ' . $template_id);

        // FINAL HTML SNIPPET LOG (for key field, e.g., about_job)
        if (isset($form_data['about_job'])) {
            if (preg_match('/name=["\']about_job["\'][^>]*>(.*?)<\/textarea>/is', $form_html, $snippet)) {
                log_message('debug', '  FINAL HTML about_job value: "' . substr(trim(strip_tags($snippet[1])), 0, 50) . '"');
            }
        }

        $form_html = preg_replace('/<form[^>]*>/', '<div class="section-form">', $form_html);
        $form_html = str_replace('</form>', '</div>', $form_html);
        $form_html = preg_replace('/<div class="btn-container"[^>]*>.*?<\/div>/s', '', $form_html);
        $form_html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/is', '', $form_html);

        return $form_html;

    } catch (Exception $e) {
        log_message('error', 'Section form error: ' . $e->getMessage());
        return '<div class="alert alert-danger">Error loading section form</div>';
    }
}
    public function preview($template_id)
    {
        log_message('debug', '=== PREVIEW TEMPLATE CALLED: ' . $template_id . ' ===');
        
        $template = $this->{$this->model}->get_by_id($template_id);
        if (!$template) {
            show_404();
        }

        $quick_manage_data = $this->quick_manage_extra($template_id, $template);
        
        $this->load->view($this->folder . '/view_header');
        $this->load->view('admin/templates/preview', [
            'template' => $template,
            'form_preview' => $quick_manage_data['current_form_preview'] ?? '<div class="alert alert-warning">No form available</div>',
            'template_name' => $quick_manage_data['identifier'] ?? 'Template',
            'schema_name' => $quick_manage_data['current_schema_name'] ?? 'No Schema'
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

    private function wrap_section_form($section, $form_html)
    {
        return '
        <div class="composite-section mb-4" data-section-id="' . $section->id . '">
            <div class="section-header bg-light p-3 mb-3 border rounded">
                <h5>' . htmlspecialchars($section->name) . '</h5>
                <small class="text-muted">' . ($section->section_type ?? 'Section') . '</small>
            </div>
            ' . $form_html . '
        </div>';
    }

    public function ajax_listing()
    {
        log_message('debug', '=== AJAX_LISTING CALLED - USING HYBRID DATA ===');
        $this->ajax_results();
    }

    public function ajax_get_all()
    {
        log_message('debug', '=== AJAX_GET_ALL CALLED - USING HYBRID DATA ===');
        $this->ajax_results();
    }

    private function output_json($data) 
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    public function remove($id) 
    {
        log_message('debug', '=== CASCADING DELETE START FOR TEMPLATE: ' . $id . ' ===');
        
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

        $messageParams = array('name' => $row->name ?? $row->template_name ?? 'Template');

        $this->db->trans_start();
        
        $result = $this->{$this->model}->remove($id);
        
        if ($result) {
            $this->cascade_delete_to_instances($id, $row->name ?? $row->template_name);
            
            Logger::log('Removed template and related instances: ' . ($row->name ?? $row->template_name), array('id' => $id));
        }
        
        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE && $result) {
            flash_notification(langs($this->pageName . '_remove_success_description', $messageParams), 'success');
            $this->remove_extra_success($row);
        } else {
            Anomalies::log('Failed to remove template and related instances: ' . ($row->name ?? $row->template_name), $this->db->last_query());
            flash_notification(langs($this->pageName . '_remove_failed_description', $messageParams), 'error');
        }

        redir($this->pageName);
    }

    private function cascade_delete_to_instances($template_id, $template_name) 
    {
        log_message('debug', 'Cascading delete to instances for template: ' . $template_id);
        
        $instances = $this->db->select('id, name')
                             ->from('template_instances')
                             ->where('template_id', $template_id)
                             ->where('removed', 0)
                             ->get()
                             ->result();
        
        $deleted_count = 0;
        foreach ($instances as $instance) {
            $this->db->where('id', $instance->id)
                    ->update('template_instances', [
                        'removed' => 1, 
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            
            if ($this->db->affected_rows() > 0) {
                $deleted_count++;
                Logger::log('Cascaded delete to template instance: ' . $instance->name, [
                    'template_id' => $template_id,
                    'template_name' => $template_name,
                    'instance_id' => $instance->id
                ]);
            }
        }
        
        log_message('debug', 'Deleted ' . $deleted_count . ' instances for template: ' . $template_id);
        return $deleted_count;
    }

    public function remove_extra_before($row) 
    {
        log_message('debug', 'Template remove_extra_before called for: ' . ($row->name ?? $row->template_name));
        return TRUE;
    }

    public function remove_extra_success($row) 
    {
        log_message('debug', 'Template remove_extra_success called for: ' . ($row->name ?? $row->template_name));
        return TRUE;
    }

  public function update_ajax($template_id)
{
    log_message('debug', '=== UPDATE_AJAX CALLED FOR TEMPLATE: ' . $template_id . ' ===');
    
    if (!$this->input->is_ajax_request()) {
        show_404();
    }



    try {
        $post_data = $this->input->post();
        
        $system_fields = ['id', 'name', 'code', 'schema_id', 'description', 'preview_image', 'enabled', 'is_preview'];
        $form_data = array_diff_key($post_data, array_flip($system_fields));
        
        log_message('debug', 'Form data to save for template ' . $template_id . ': ' . print_r($form_data, true));

        if (empty($form_data)) {
            $this->output_json([
                'success' => false,
                'error' => 'No form data to save'
            ]);
            return;
        }

        $this->db->trans_start();

        $template = $this->{$this->model}->get_by_id($template_id);
        if (!$template) {
            throw new Exception('Template not found');
        }

        $template_type = $template->template_type ?? 'single';
        
        if ($template_type === 'composite') {
            $result = $this->save_composite_template_data($template_id, $form_data, $template);
        } else {
            $result = $this->save_single_template_data($template_id, $form_data, $template);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Database transaction failed');
        }

        $this->output_json([
            'success' => true,
            'message' => 'Template data saved successfully',
            'data' => $result
        ]);

    } catch (Exception $e) {
        log_message('error', 'Template save error: ' . $e->getMessage());
        $this->db->trans_rollback();
        
        $this->output_json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

    private function save_single_template_data($template_id, $form_data, $template)
    {
        log_message('debug', 'Saving single template data for template ID: ' . $template_id);
        
        $existing_instance = $this->db->where('template_id', $template_id)
                                     ->where('removed', 0)
                                     ->get('template_instances')
                                     ->row();

        $instance_data = [
            'template_id' => $template_id,
            'name' => $template->name ?? 'Template Instance',
            'form_data' => json_encode($form_data, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing_instance) {
            $this->db->where('id', $existing_instance->id)
                    ->update('template_instances', $instance_data);
            $instance_id = $existing_instance->id;
            log_message('debug', 'Updated existing template instance: ' . $instance_id . ' for template: ' . $template_id);
        } else {
            $instance_data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('template_instances', $instance_data);
            $instance_id = $this->db->insert_id();
            log_message('debug', 'Created new template instance: ' . $instance_id . ' for template: ' . $template_id);
        }

        $this->add_missing_columns('mod_layouts', $form_data);

        return [
            'instance_id' => $instance_id,
            'template_type' => 'single',
            'fields_saved' => count($form_data)
        ];
    }

    private function save_composite_template_data($template_id, $form_data, $template)
    {
        log_message('debug', 'Saving composite template data for template ID: ' . $template_id);
        
        $this->load->model('admin/Model_agency_templates');
        $composite_template = $this->Model_agency_templates->get_template_with_sections($template_id);
        
        if (!$composite_template || empty($composite_template->sections)) {
            throw new Exception('No sections found for composite template');
        }

        $existing_instance = $this->db->where('template_id', $template_id)
                                     ->where('removed', 0)
                                     ->get('template_instances')
                                     ->row();

        $instance_data = [
            'template_id' => $template_id,
            'name' => $template->template_name ?? 'Composite Template Instance',
            'form_data' => json_encode($form_data, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing_instance) {
            $this->db->where('id', $existing_instance->id)
                    ->update('template_instances', $instance_data);
            $instance_id = $existing_instance->id;
            log_message('debug', 'Updated existing composite template instance: ' . $instance_id . ' for template: ' . $template_id);
        } else {
            $instance_data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('template_instances', $instance_data);
            $instance_id = $this->db->insert_id();
            log_message('debug', 'Created new composite template instance: ' . $instance_id . ' for template: ' . $template_id);
        }

        $this->add_missing_columns('agency_custom_templates', $form_data);

        return [
            'instance_id' => $instance_id,
            'template_type' => 'composite',
            'fields_saved' => count($form_data),
            'sections_count' => count($composite_template->sections)
        ];
    }

    private function add_missing_columns($table_name, $form_data)
    {
        log_message('debug', 'Checking for missing columns in table: ' . $table_name);
        
        $existing_columns = $this->db->list_fields($table_name);
        log_message('debug', 'Existing columns: ' . implode(', ', $existing_columns));
        
        $added_columns = [];
        
        foreach ($form_data as $field_name => $field_value) {
            if (in_array($field_name, $existing_columns)) {
                continue;
            }
            
            if ($this->is_system_field($field_name) || !$this->is_valid_column_name($field_name)) {
                continue;
            }
            
            $column_type = $this->determine_column_type($field_value);
            
            try {
                $this->add_table_column($table_name, $field_name, $column_type);
                $added_columns[] = $field_name;
                log_message('debug', 'Added column: ' . $field_name . ' as ' . $column_type);
                
            } catch (Exception $e) {
                log_message('error', 'Failed to add column ' . $field_name . ': ' . $e->getMessage());
            }
        }
        
        if (!empty($added_columns)) {
            log_message('debug', 'Successfully added columns: ' . implode(', ', $added_columns));
        } else {
            log_message('debug', 'No new columns needed to be added');
        }
        
        return $added_columns;
    }

    private function add_table_column($table_name, $column_name, $column_type)
    {
        $sql = "ALTER TABLE `{$table_name}` ADD COLUMN `{$column_name}` {$column_type} NULL";
        return $this->db->query($sql);
    }

    private function determine_column_type($value)
    {
        if (is_numeric($value)) {
            return 'VARCHAR(255)';
        } elseif (is_string($value)) {
            $length = strlen($value);
            if ($length <= 255) {
                return 'VARCHAR(255)';
            } elseif ($length <= 1000) {
                return 'TEXT';
            } else {
                return 'LONGTEXT';
            }
        } elseif (is_bool($value)) {
            return 'TINYINT(1)';
        } else {
            return 'VARCHAR(255)';
        }
    }

    private function is_system_field($field_name)
    {
        $system_fields = [
            'id', 'name', 'code', 'schema_id', 'description', 'preview_image', 
            'enabled', 'removed', 'created_at', 'updated_at', 'template_id',
            'template_name', 'agency_id', 'template_type'
        ];
        
        return in_array($field_name, $system_fields);
    }

    private function is_valid_column_name($field_name)
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field_name);
    }
}