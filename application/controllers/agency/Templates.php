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
    
     // ✅ FIX: Ensure agency_id matches the login data
    $login_data = $this->session->userdata('login');
    if (!empty($login_data['agency']['agency_id'])) {
        $correct_agency_id = $login_data['agency']['agency_id'];
        $current_agency_id = $this->session->userdata('agency_id');
        
        if ($current_agency_id != $correct_agency_id) {
            log_message('debug', "Fixing agency_id mismatch: $current_agency_id -> $correct_agency_id");
            $this->session->set_userdata('agency_id', $correct_agency_id);
        }
    }
    
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
                $agency_id = isset($value) ? $value : (isset($row->agency_id) ? $row->agency_id : '-');
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
            $result['is_composite'] = true;
        } else {
            $result = $this->quick_manage_single($id, $row);
            $result['is_composite'] = false;
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
    // Fixed regex: Capture attributes before > and insert selected attr inside tag
    $option_pattern = '/(<option\s+[^>]*?value=["\']' . preg_quote($field_value, '/') . '["\'][^>]*?)>/i';
    $new_options = preg_replace($option_pattern, '$1 selected="selected">', $options_html);
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
                            
                            // Remove inline scripts to prevent double CKEditor initialization (matching composite behavior)
                            $form_html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/is', '', $form_html);
                            
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
            'template_id' => $id,
            'is_composite' => false
        ];
    }

private function quick_manage_composite($id, $row) 
{
    log_message('debug', '=== QUICK_MANAGE_COMPOSITE START for Template ID: ' . $id . ' ===');
    
    $all_sections_html = '';
    $all_form_data = [];
    
    // ✅ FIX: Get the template instance data for this specific composite template
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
        log_message('debug', '✅ Instance FOUND for composite template ID ' . $id . ': Instance ID=' . $instance->id . ', Name=' . $instance->name);
        
        if (!empty($instance->form_data)) {
            $all_form_data = json_decode($instance->form_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'JSON decode error in composite template: ' . json_last_error_msg());
                $all_form_data = [];
            } else {
                log_message('debug', '✅ Loaded composite template form data for template ID ' . $id . ': ' . count($all_form_data) . ' fields');
                
                // ✅ DEBUG: Log medical fields from instance
                log_message('debug', '🏥 MEDICAL FIELDS FROM INSTANCE:');
                foreach ($all_form_data as $key => $value) {
                    if (strpos($key, 'medical') !== false || strpos($key, 'doctor') !== false || strpos($key, 'next_of_kin') !== false) {
                        log_message('debug', '🏥 Instance medical field [' . $key . '] = "' . (is_array($value) ? 'ARRAY' : substr($value, 0, 50)) . '"');
                    }
                }
                
                // ✅ FIX: CONVERT FIELD NAMES FROM UNDERSCORE TO DOT FORMAT
                $converted_form_data = [];
                foreach ($all_form_data as $key => $value) {
                    // Convert from stored underscore format to form field dot format
                    $converted_key = $this->convert_to_form_field_name($key);
                    
                    // ✅ FIX: Handle array values properly for logging
                    if (is_array($value)) {
                        $log_value = 'ARRAY: ' . implode(', ', $value);
                    } else {
                        $log_value = $value;
                    }
                    
                    $converted_form_data[$converted_key] = $value;
                    
                    // ✅ DEBUG: Log medical field conversion
                    if (strpos($key, 'medical') !== false || strpos($converted_key, 'medical') !== false) {
                        log_message('debug', '🏥 Medical field converted [' . $key . '] -> [' . $converted_key . '] = "' . $log_value . '"');
                    } else {
                        log_message('debug', 'Form data converted [' . $key . '] -> [' . $converted_key . '] = "' . $log_value . '"');
                    }
                }
                $all_form_data = $converted_form_data;
            }
        } else {
            log_message('debug', '⚠️ Instance found but form_data is empty for composite template ID: ' . $id);
        }
    } else {
        log_message('debug', '❌ NO instance found for composite template ID: ' . $id);
    }
    
    // ✅ FIX: Also check agency_custom_templates for form data
    $agency_template_data = $this->db->select('*')
                                   ->from('agency_custom_templates')
                                   ->where('id', $id)
                                   ->get()
                                   ->row();
    
    if ($agency_template_data) {
        log_message('debug', '✅ Found agency_custom_templates record for ID: ' . $id);
        // Extract form data from agency_custom_templates columns
        $agency_columns = $this->db->list_fields('agency_custom_templates');
        $system_fields = ['id', 'agency_id', 'template_name', 'description', 'enabled', 'created_at', 'updated_at'];
        
        foreach ($agency_columns as $column) {
            if (!in_array($column, $system_fields) && !empty($agency_template_data->$column)) {
                // ✅ FIX: Convert column names to form field format
                $converted_column = $this->convert_to_form_field_name($column);
                
                // ✅ FIX: Handle array values for agency data too
                $column_value = $agency_template_data->$column;
                if (is_array($column_value)) {
                    $log_value = 'ARRAY: ' . implode(', ', $column_value);
                } else {
                    $log_value = $column_value;
                }
                
                $all_form_data[$converted_column] = $column_value;
                
                // ✅ DEBUG: Log medical fields from agency table
                if (strpos($column, 'medical') !== false || strpos($converted_column, 'medical') !== false) {
                    log_message('debug', '🏥 ✅ Added medical field from agency_custom_templates: ' . $column . ' -> ' . $converted_column . ' = "' . $log_value . '"');
                } else {
                    log_message('debug', '✅ Added field from agency_custom_templates: ' . $column . ' -> ' . $converted_column . ' = "' . $log_value . '"');
                }
            }
        }
    }
    
    log_message('debug', '✅ Final form data count before rendering: ' . count($all_form_data));
    
    // Get template with sections
    $template_with_sections = $this->Model_agency_templates->get_template_with_sections($id);
    
    if ($template_with_sections && !empty($template_with_sections->sections)) {
        log_message('debug', '✅ Found ' . count($template_with_sections->sections) . ' sections for composite template ID: ' . $id);
        
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
                            // ✅ FIX: Use exact form field names (with dots)
                            if (isset($all_form_data[$field_name])) {
                                $field_value = $all_form_data[$field_name];
                                
                                // ✅ FIX: Handle array values for section data
                                if (is_array($field_value)) {
                                    $log_value = 'ARRAY: ' . implode(', ', $field_value);
                                } else {
                                    $log_value = $field_value;
                                }
                                
                                $section_data[$field_name] = $field_value;
                                
                                // ✅ DEBUG: Log medical field assignment
                                if (strpos($field_name, 'medical') !== false) {
                                    log_message('debug', '🏥 ✅ Setting medical field [' . $field_name . '] = "' . $log_value . '"');
                                } else {
                                    log_message('debug', '✅ Setting field [' . $field_name . '] = "' . $log_value . '"');
                                }
                            } else {
                                if (strpos($field_name, 'medical') !== false) {
                                    log_message('debug', '🏥 ⚠️ Medical field [' . $field_name . '] not found in form data');
                                } else {
                                    log_message('debug', '⚠️ Field [' . $field_name . '] not found in form data');
                                }
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
        'debug_form_data' => $all_form_data,
        'is_composite' => true,
        'template_id' => $id
    ];
}

private function convert_to_form_field_name($stored_name)
{
    // Handle special cases first
    if ($stored_name === 'template_cache_id') {
        return 'template_cache_id';
    }
    
    // ✅ FIX: Convert mod_job_medical_requirements_* to mod_job_medical_requirements.*
    if (strpos($stored_name, 'mod_job_medical_requirements_') === 0) {
        $field_part = substr($stored_name, 29); // Remove "mod_job_medical_requirements_"
        return 'mod_job_medical_requirements.' . $field_part;
    }
    
    // Convert mod_jobs_name to mod_jobs.name
    if (strpos($stored_name, 'mod_jobs_') === 0) {
        $field_part = substr($stored_name, 9); // Remove "mod_jobs_"
        
        // ✅ FIX: Handle employment_type specifically
        if ($field_part === 'employment_type') {
            return 'mod_jobs.employment_type';
        }
        
        return 'mod_jobs.' . $field_part;
    }
    
    // Convert usr_medical_emergency_details_ to usr_medical_emergency_details.
    if (strpos($stored_name, 'usr_medical_emergency_details_') === 0) {
        $field_part = substr($stored_name, 30); // Remove "usr_medical_emergency_details_"
        return 'usr_medical_emergency_details.' . $field_part;
    }
    
    // ✅ IMPROVED: Handle employment type fields more broadly
    if ($stored_name === 'employment_type' || $stored_name === 'job_type') {
        return 'mod_jobs.employment_type';
    }
    
    // ✅ IMPROVED: Handle industry fields
    if (strpos($stored_name, 'industry') !== false) {
        if (strpos($stored_name, 'mod_industries_') === 0) {
            return 'mod_industries.id';
        }
        if (strpos($stored_name, 'mod_jobs_industry') === 0) {
            return 'mod_jobs.industry_id';
        }
        return 'industry_id';
    }
    
    // For other fields, return as is
    return $stored_name;
}

private function get_section_form_preview($schema_id, $template_id, $form_data = [])
{
    try {
        log_message('debug', 'Getting section form preview for schema_id: ' . $schema_id . ', template_id: ' . $template_id);
        
        // ✅ DEBUG: Log medical fields specifically
        log_message('debug', '🏥 MEDICAL FIELDS IN FORM DATA:');
        foreach ($form_data as $key => $value) {
            if (strpos($key, 'medical') !== false || strpos($key, 'doctor') !== false || strpos($key, 'next_of_kin') !== false) {
                log_message('debug', '🏥 Medical field [' . $key . '] = "' . (is_array($value) ? 'ARRAY' : substr($value, 0, 50)) . '"');
            }
        }
        
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

        // ✅ FIX: Pass the form data directly to make_form
        $form_result = $form_builder->make_form($form_data);
        $form_html = $form_result->form_view;

        // Additional manual population as backup
        $pre_pop_count = 0;
        foreach ($form_data as $field_name => $field_value) {
            // ✅ FIX: Handle array values for logging
            if (is_array($field_value)) {
                $log_value = 'ARRAY: ' . implode(', ', $field_value);
            } else {
                $log_value = $field_value;
            }
            
            // ✅ DEBUG: Log medical field population attempts
            if (strpos($field_name, 'medical') !== false || strpos($field_name, 'doctor') !== false || strpos($field_name, 'next_of_kin') !== false) {
                log_message('debug', '🏥 Manual pre-pop for medical field [' . $field_name . '] = "' . $log_value . '"');
            } else {
                log_message('debug', '  Manual pre-pop for [' . $field_name . '] = "' . $log_value . '"');
            }
            
            // Textareas
            $textarea_pattern = '/(<textarea\s+[^>]*name=["\']' . preg_quote($field_name, '/') . '["\'][^>]*>)(.*?)(<\/textarea>)/is';
            if (preg_match($textarea_pattern, $form_html, $matches)) {
                $current_content = trim($matches[2]);
                if (empty($current_content)) {
                    // ✅ FIX: Handle array values for textareas
                    if (is_array($field_value)) {
                        $field_value = implode(', ', $field_value);
                    }
                    $new_textarea = $matches[1] . htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8') . $matches[3];
                    $form_html = str_replace($matches[0], $new_textarea, $form_html);
                    $pre_pop_count++;
                    
                    if (strpos($field_name, 'medical') !== false) {
                        log_message('debug', '    🏥 ✓ Manual textarea update for medical field ' . $field_name);
                    } else {
                        log_message('debug', '    ✓ Manual textarea update for ' . $field_name);
                    }
                }
            } else {
                // Inputs (text, email, etc.)
                $input_pattern = '/(<input\s+[^>]*name=["\']' . preg_quote($field_name, '/') . '["\'][^>]*)(value=["\'][^"\']*["\'])?/i';
                if (preg_match($input_pattern, $form_html, $input_matches)) {
                    // ✅ FIX: Handle array values for inputs
                    if (is_array($field_value)) {
                        $field_value = implode(', ', $field_value);
                    }
                    $new_value_attr = 'value="' . htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8') . '"';
                    if (isset($input_matches[2])) {
                        $new_input = str_replace($input_matches[2], $new_value_attr, $input_matches[0]);
                    } else {
                        $new_input = $input_matches[0] . ' ' . $new_value_attr;
                    }
                    $form_html = str_replace($input_matches[0], $new_input, $form_html);
                    
                    if (strpos($field_name, 'medical') !== false) {
                        log_message('debug', '    🏥 ✓ Manual input update for medical field ' . $field_name);
                    } else {
                        log_message('debug', '    ✓ Manual input update for ' . $field_name);
                    }
                    $pre_pop_count++;
                } else {
                    // ✅ FIX: Better select handling for multi-select
                    $select_pattern = '/(<select\s+[^>]*name=["\']' . preg_quote($field_name, '/') . '["\'][^>]*>)(.*?<\/select>)/is';
                    if (preg_match($select_pattern, $form_html, $select_matches)) {
                        $options_html = $select_matches[2];
                        
                        // Handle multi-select (array values)
                        if (is_array($field_value)) {
                            foreach ($field_value as $selected_value) {
                                $option_pattern = '/(<option\s+[^>]*?value=["\']' . preg_quote($selected_value, '/') . '["\'][^>]*?)>/i';
                                $options_html = preg_replace($option_pattern, '$1 selected="selected">', $options_html);
                            }
                        } else {
                            // Single select
                            $option_pattern = '/(<option\s+[^>]*?value=["\']' . preg_quote($field_value, '/') . '["\'][^>]*?)>/i';
                            $options_html = preg_replace($option_pattern, '$1 selected="selected">', $options_html);
                        }
                        
                        $new_select = $select_matches[1] . $options_html . '</select>';
                        $form_html = str_replace($select_matches[0], $new_select, $form_html);
                        
                        if (strpos($field_name, 'medical') !== false) {
                            log_message('debug', '    🏥 ✓ Manual select update for medical field ' . $field_name);
                        } else {
                            log_message('debug', '    ✓ Manual select update for ' . $field_name);
                        }
                        $pre_pop_count++;
                    }
                }
            }
        }
        
        log_message('debug', '  Manual pre-populated ' . $pre_pop_count . ' fields for section in template ' . $template_id);

        // Clean up the form HTML
        $form_html = preg_replace('/<form[^>]*>/', '<div class="section-form">', $form_html);
        $form_html = str_replace('</form>', '</div>', $form_html);
        $form_html = preg_replace('/<div class="btn-container"[^>]*>.*?<\/div>/s', '', $form_html);
        
        // ✅ FIX: Don't remove ALL scripts - keep multi-select initialization scripts
        // Only remove problematic scripts that cause conflicts
        $form_html = preg_replace('/<script[^>]*>\s*CKEDITOR\.replace[^<]*<\/script>/is', '', $form_html);
        $form_html = preg_replace('/<script[^>]*>\s*\$\(document\)\.ready[^<]*<\/script>/is', '', $form_html);

        return $form_html;

    } catch (Exception $e) {
        log_message('error', 'Section form error: ' . $e->getMessage());
        return '<div class="alert alert-danger">Error loading section form: ' . $e->getMessage() . '</div>';
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

    // Also, update the wrap_section_form method to include the delete button:
private function wrap_section_form($section, $form_html)
{
    return '
    <div class="composite-section mb-4" data-section-id="' . $section->id . '">
        <div class="section-header bg-light p-3 mb-3 border rounded d-flex justify-content-between align-items-center">
            <div>
                <h5>' . htmlspecialchars($section->name) . '</h5>
                <small class="text-muted">' . ($section->section_type ?? 'Section') . '</small>
            </div>
            <button type="button" class="btn btn-sm btn-danger delete-section" data-section-id="' . $section->id . '" title="Delete this section">
                <i class="fa fa-trash"></i> Delete
            </button>
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
    // ✅ FIX: Set proper headers first
    header('Content-Type: application/json');
    
    // ✅ FIX: Completely suppress all output and errors
    ob_start();
    $old_error_level = error_reporting(0);
    ini_set('display_errors', 0);
    
    try {
        log_message('debug', '=== UPDATE_AJAX STARTED FOR TEMPLATE: ' . $template_id . ' ===');
        
        // Session validation
        if (!$this->session->userdata('is_logged_in') || empty($this->session->userdata('agency_id'))) {
            throw new Exception('Session expired. Please log in again.');
        }

        if (!$this->input->is_ajax_request()) {
            throw new Exception('Not an AJAX request');
        }

        $post_data = $this->input->post();
        
        // ✅ DEBUG: Log all POST data
        log_message('debug', '=== UPDATE_AJAX POST DATA ===');
        log_message('debug', 'POST keys: ' . implode(', ', array_keys($post_data)));
        foreach ($post_data as $key => $value) {
            if (is_array($value)) {
                log_message('debug', 'POST[' . $key . '] = ARRAY: ' . json_encode($value));
            } else {
                log_message('debug', 'POST[' . $key . '] = ' . substr($value, 0, 100));
            }
        }
        
        $system_fields = ['id', 'name', 'code', 'schema_id', 'description', 'preview_image', 'enabled', 'is_preview', 'template_cache_id'];
        $form_data = array_diff_key($post_data, array_flip($system_fields));
        
        log_message('debug', 'Form data to save for template ' . $template_id . ': ' . count($form_data) . ' fields');
        log_message('debug', 'Form data keys: ' . implode(', ', array_keys($form_data)));

        if (empty($form_data)) {
            throw new Exception('No form data to save');
        }

        // ✅ FIX: Test database connection first
        if (!$this->db->conn_id) {
            throw new Exception('Database connection failed');
        }

        $this->db->trans_start();

        $template = $this->{$this->model}->get_by_id($template_id);
        if (!$template) {
            throw new Exception('Template not found with ID: ' . $template_id);
        }

        log_message('debug', 'Template found: ' . ($template->name ?? $template->template_name ?? 'Unknown'));
        log_message('debug', 'Template type: ' . ($template->template_type ?? 'single'));

        $template_type = $template->template_type ?? 'single';
        
        if ($template_type === 'composite') {
            log_message('debug', 'Processing as COMPOSITE template');
            $result = $this->save_composite_template_data($template_id, $form_data, $template);
        } else {
            log_message('debug', 'Processing as SINGLE template');
            $result = $this->save_single_template_data($template_id, $form_data, $template);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Database transaction failed');
        }

        // ✅ FIX: Clear ALL output buffers completely
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        $response = [
            'success' => true,
            'message' => 'Template data saved successfully',
            'data' => $result
        ];
        
        log_message('debug', 'UPDATE_AJAX SUCCESS: Template ' . $template_id . ' saved successfully');
        
        // ✅ FIX: Use CI output class instead of echo
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response))
            ->_display();
        
        exit; // ✅ FIX: Stop execution after sending response

    } catch (Exception $e) {
        // ✅ FIX: Clear ALL output buffers completely
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        log_message('error', 'Template save error: ' . $e->getMessage());
        log_message('error', 'Error trace: ' . $e->getTraceAsString());
        
        if (isset($this->db) && method_exists($this->db, 'trans_rollback')) {
            @$this->db->trans_rollback();
        }
        
        $error_response = [
            'success' => false,
            'error' => $e->getMessage(),
            'template_id' => $template_id
        ];
        
        // ✅ FIX: Use CI output class instead of echo
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($error_response))
            ->_display();
        
        exit; // ✅ FIX: Stop execution after sending response
        
    } finally {
        // ✅ FIX: Restore error reporting
        error_reporting($old_error_level);
        ini_set('display_errors', 1);
        log_message('debug', '=== UPDATE_AJAX COMPLETED ===');
    }
}

private function debug_form_data_processing($form_data)
{
    log_message('debug', '=== DEBUG FORM DATA PROCESSING ===');
    log_message('debug', 'Total form fields: ' . count($form_data));
    
    foreach ($form_data as $key => $value) {
        if (is_array($value)) {
            log_message('debug', "Array field [{$key}] with " . count($value) . " elements");
            foreach ($value as $subkey => $subvalue) {
                log_message('debug', "  [{$subkey}] => " . (is_array($subvalue) ? 'ARRAY' : substr($subvalue, 0, 100)));
            }
        } else {
            log_message('debug', "Field [{$key}] => " . substr($value, 0, 100));
        }
    }
    log_message('debug', '=== END FORM DATA DEBUG ===');
}

private function save_composite_template_data($template_id, $form_data, $template)
{
    log_message('debug', 'Saving composite template data for template ID: ' . $template_id);
    log_message('debug', 'Form data keys to save: ' . implode(', ', array_keys($form_data)));
    
    try {
        // ✅ FIX: Load agency model instead of admin model
        $this->load->model('agency/Model_agency_templates');
        $composite_template = $this->Model_agency_templates->get_template_with_sections($template_id);
        
        if (!$composite_template || empty($composite_template->sections)) {
            throw new Exception('No sections found for composite template');
        }

        // ✅ FIX: Get current agency ID properly
        $agency_id = $this->session->userdata('agency_id');
        if (!$agency_id) {
            $login_data = $this->session->userdata('login');
            if (!empty($login_data['agency']['agency_id'])) {
                $agency_id = $login_data['agency']['agency_id'];
            } elseif (!empty($login_data['agency']['id'])) {
                $agency_id = $login_data['agency']['id'];
            }
        }
        
        log_message('debug', 'Current agency ID for saving: ' . $agency_id);

        // ✅ FIX: Debug form data processing
        $this->debug_form_data_processing($form_data);

        $existing_instance = $this->db->where('template_id', $template_id)
                                     ->where('removed', 0)
                                     ->get('template_instances')
                                     ->row();

        // ✅ FIX: Convert field names to underscore format for database storage
        $form_data_for_storage = [];
        foreach ($form_data as $key => $value) {
            // Convert dots to underscores for database storage compatibility
            $storage_key = str_replace('.', '_', $key);
            
            // ✅ FIX: Handle array values - convert to JSON string
            if (is_array($value)) {
                $form_data_for_storage[$storage_key] = json_encode($value, JSON_UNESCAPED_UNICODE);
                log_message('debug', 'Form data for storage: [' . $key . '] -> [' . $storage_key . '] = JSON_ARRAY: ' . json_encode($value));
            } else {
                $form_data_for_storage[$storage_key] = $value;
                log_message('debug', 'Form data for storage: [' . $key . '] -> [' . $storage_key . '] = "' . $value . '"');
            }
        }

        $instance_data = [
            'template_id' => $template_id,
            'name' => $template->template_name ?? 'Composite Template Instance',
            'form_data' => !empty($form_data_for_storage) ? json_encode($form_data_for_storage, JSON_UNESCAPED_UNICODE) : '{}',
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

        // Add missing columns to agency_custom_templates table (using underscore format)
        $this->add_missing_columns('agency_custom_templates', $form_data_for_storage);

        // ✅ FIX: Check if agency_custom_templates record exists before updating/inserting
        $existing_agency_template = $this->db->where('id', $template_id)
                                            ->get('agency_custom_templates')
                                            ->row();

        // ✅ FIX: Update agency_custom_templates with individual form_data fields
        $update_data = [];
        foreach ($form_data_for_storage as $key => $value) {
            // ✅ FIX: Skip system fields and ensure value is scalar (not array)
            if ($this->is_system_field($key)) {
                continue;
            }
            
            $update_data[$key] = $value;
            log_message('debug', 'Setting agency_custom_templates field: ' . $key . ' = "' . (is_array($value) ? 'ARRAY_CONVERTED_TO_JSON' : $value) . '"');
        }
        
        // ✅ FIX: Proper handling of agency_custom_templates record
        if ($existing_agency_template) {
            // Update existing record
            $this->db->where('id', $template_id)
                     ->update('agency_custom_templates', $update_data);
            $affected = $this->db->affected_rows();
            log_message('debug', 'Updated existing agency_custom_templates record for template: ' . $template_id . ' - Affected rows: ' . $affected);
        } else {
            // Insert new record only if it doesn't exist
            if (!$agency_id) {
                throw new Exception('Agency ID not found for creating new template record');
            }
            
            $base_data = [
                'id' => $template_id,
                'agency_id' => $agency_id,
                'template_name' => $template->template_name ?? 'Composite Template',
                'enabled' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $insert_data = array_merge($base_data, $update_data);
            
            try {
                $this->db->insert('agency_custom_templates', $insert_data);
                log_message('debug', 'Inserted new agency_custom_templates record for template: ' . $template_id . ' with agency_id: ' . $agency_id);
            } catch (Exception $e) {
                // If insert fails due to duplicate, try update instead
                log_message('warning', 'Insert failed, trying update: ' . $e->getMessage());
                $this->db->where('id', $template_id)
                         ->update('agency_custom_templates', $update_data);
                $affected = $this->db->affected_rows();
                log_message('debug', 'Updated agency_custom_templates after failed insert - Affected rows: ' . $affected);
            }
        }

        return [
            'instance_id' => $instance_id,
            'template_type' => 'composite',
            'fields_saved' => count($form_data),
            'sections_count' => count($composite_template->sections),
            'agency_id' => $agency_id,
            'agency_template_updated' => true
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Error in save_composite_template_data: ' . $e->getMessage());
        throw $e; // Re-throw to be caught by the main method
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

        // NEW: Update mod_layouts with individual form_data fields (assuming mod_layouts has id matching template_id)
        $update_data = [];
        foreach ($form_data as $key => $value) {
            $update_data[$key] = $value;
        }
        $this->db->where('id', $template_id)
                 ->update('mod_layouts', $update_data);
        $affected = $this->db->affected_rows();
        if ($affected === 0) {
            log_message('warning', 'No rows updated in mod_layouts for template ' . $template_id . ' - check if base record exists');
            // Optional: INSERT base if missing (adjust fields as needed for mod_layouts)
            $base_data = ['id' => $template_id, 'name' => $template->name ?? 'Single', 'enabled' => 1, /* other base fields */ 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')];
            $base_data = array_merge($base_data, $update_data);
            $this->db->insert('mod_layouts', $base_data);
        } else {
            log_message('debug', 'Updated ' . $affected . ' row(s) in mod_layouts for template ' . $template_id . ' with ' . count($form_data) . ' fields');
        }

        return [
            'instance_id' => $instance_id,
            'template_type' => 'single',
            'fields_saved' => count($form_data)
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

   public function get_available_sections_for_template($template_id) {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $this->load->model('admin/Model_template_sections');
    
    // Get ALL sections (with agency filtering built into get_all())
    $all_sections = $this->Model_template_sections->get_all()->result();

    // Exclude already added sections
    $added_sections = $this->db->select('section_id')
                               ->from('agency_template_sections')
                               ->where('agency_template_id', $template_id)
                               ->get()
                               ->result_array();
    $added_ids = array_column($added_sections, 'section_id');

    $available = [];
    foreach ($all_sections as $section) {
        if (!in_array($section->id, $added_ids)) {
            $available[] = $section;
        }
    }

    $this->output_json([
        'success' => true,
        'sections' => $available
    ]);
}
public function add_section_to_template($template_id) {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $section_id = $this->input->post('section_id');
    if (empty($section_id) || !is_numeric($section_id)) {
        $this->output_json(['success' => false, 'error' => 'Invalid section ID']);
        return;
    }

    // ✅ FIX: Load agency model instead of admin model
    $this->load->model('agency/Model_agency_templates');
    $result = $this->Model_agency_templates->add_section_to_template($template_id, $section_id);

    if ($result) {
        log_message('debug', 'Added section ' . $section_id . ' to composite template ' . $template_id);
        $this->output_json(['success' => true, 'message' => 'Section added successfully']);
    } else {
        log_message('error', 'Failed to add section ' . $section_id . ' to template ' . $template_id);
        $this->output_json(['success' => false, 'error' => 'Failed to add section']);
    }
}

    // In the Templates controller, add this new method:
public function remove_section_from_template($template_id) {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $section_id = $this->input->post('section_id');
    if (empty($section_id) || !is_numeric($section_id)) {
        $this->output_json(['success' => false, 'error' => 'Invalid section ID']);
        return;
    }

    // Verify the section belongs to this template
    $exists = $this->db->where([
        'agency_template_id' => $template_id,
        'section_id' => $section_id
    ])->count_all_results('agency_template_sections');
    if ($exists === 0) {
        $this->output_json(['success' => false, 'error' => 'Section not found in this template']);
        return;
    }

    $result = $this->db->delete('agency_template_sections', [
        'agency_template_id' => $template_id,
        'section_id' => $section_id
    ]);

    if ($result) {
        // Reorder remaining sections
        $this->reorder_sections_after_delete($template_id);
        log_message('debug', 'Removed section ' . $section_id . ' from composite template ' . $template_id);
        $this->output_json(['success' => true, 'message' => 'Section removed successfully']);
    } else {
        log_message('error', 'Failed to remove section ' . $section_id . ' from template ' . $template_id);
        $this->output_json(['success' => false, 'error' => 'Failed to remove section']);
    }
}

private function reorder_sections_after_delete($template_id) {
    $sections = $this->db->select('ats.id, ats.sort_order')
                         ->from('agency_template_sections as ats')
                         ->where('ats.agency_template_id', $template_id)
                         ->order_by('ats.sort_order', 'ASC')
                         ->get()
                         ->result();

    $order = 1;
    foreach ($sections as $sec) {
        $this->db->where('id', $sec->id)
                 ->update('agency_template_sections', ['sort_order' => $order]);
        $order++;
    }
}

/**
 * Send notification to recruiters when a job is created from template
 * This replicates the functionality from manual job creation
 */
private function send_template_job_notification($job_id, $job_data) {
    log_message('debug', '🔔 === TEMPLATE JOB NOTIFICATION START ===');
    
    try {
        // Load the same notification model used in manual job creation
        $this->load->model('recruiter/Model_notifications');
        
        // Get agency_id and sender_id (same logic as manual creation)
        $agency_id = $job_data['agency_id'];
        $sender_id = $this->get_user_agency_id();
        
        log_message('debug', 'Template Job Notification Details:');
        log_message('debug', ' - Job ID: ' . $job_id);
        log_message('debug', ' - Job Agency ID: ' . $agency_id);
        log_message('debug', ' - Sender Agency ID: ' . $sender_id);
        log_message('debug', ' - Job Title: ' . $job_data['name']);
        
        // Use the correct agency ID - prefer the one from the user session
        $effective_agency_id = !empty($sender_id) ? $sender_id : $agency_id;
        log_message('debug', ' - Effective agency_id for notifications: ' . $effective_agency_id);
        
        // Double-check the job was created with the correct agency
        $job_check = $this->db->get_where('mod_jobs', ['id' => $job_id])->row();
        if ($job_check) {
            log_message('debug', ' - Job agency_id in database: ' . $job_check->agency_id);
        }
        
        // Create notifications for ALL recruiters (same as manual creation)
        $result = $this->Model_notifications->create_job_notification(
            $job_id, 
            $effective_agency_id, 
            $sender_id, 
            'job_added' // Use the same type as manual creation
        );
        
        log_message('debug', 'Template job notification creation result: ' . ($result ? 'SUCCESS' : 'FAILED'));
        
        if ($result) {
            log_message('debug', '✅ Template job notification sent successfully for job ID: ' . $job_id);
        } else {
            log_message('error', '❌ Failed to send template job notification for job ID: ' . $job_id);
        }
        
        log_message('debug', '🔔 === TEMPLATE JOB NOTIFICATION END ===');
        return $result;
        
    } catch (Exception $e) {
        log_message('error', '🔔 Error in template job notification: ' . $e->getMessage());
        log_message('debug', '🔔 === TEMPLATE JOB NOTIFICATION END WITH ERROR ===');
        return false;
    }
}
/**
 * Get the logged-in user's agency ID - Works for both agency staff and recruiters
 * Same method as in Jobs_listings controller
 */
private function get_user_agency_id() {
    // Get the login data from session
    $login_data = $this->session->userdata('login');
    
    log_message('debug', 'Login data: ' . print_r($login_data, true));
    
    // Check for agency staff login
    if (!empty($login_data['agency'])) {
        $agency_user = $login_data['agency'];
        
        if (!empty($agency_user['agency_id'])) {
            $agency_id = $agency_user['agency_id'];
            log_message('debug', 'Found agency_id in login[agency] data: ' . $agency_id);
            return $agency_id;
        } elseif (!empty($agency_user['id'])) {
            // Sometimes the agency ID might be stored in the user ID field
            $agency_id = $agency_user['id'];
            log_message('debug', 'Found agency_id in login[agency] id field: ' . $agency_id);
            return $agency_id;
        }
    }
    
    // Check for recruiter login
    if (!empty($login_data['recruiter'])) {
        $recruiter_user = $login_data['recruiter'];
        
        if (!empty($recruiter_user['agency_id'])) {
            $agency_id = $recruiter_user['agency_id'];
            log_message('debug', 'Found agency_id in login[recruiter] data: ' . $agency_id);
            return $agency_id;
        }
    }
    
    // Fallback: check if we have direct agency_id in session
    $agency_id = $this->session->userdata('agency_id');
    if (!empty($agency_id)) {
        log_message('debug', 'Found agency_id directly in session: ' . $agency_id);
        return $agency_id;
    }
    
    log_message('debug', 'No agency_id found in session');
    return null;
}

public function save_as_job($template_id) 
{
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $this->output->set_content_type('application/json');

    try {
        log_message('debug', '🔍 === SAVE AS JOB DEBUG START ===');
        
        // Fetch the template
        $template = $this->{$this->model}->get_by_id($template_id);
        if (!$template) {
            throw new Exception('Template not found');
        }

        // Get ALL post data
        $all_post_data = $this->input->post();
        log_message('debug', '📋 ALL POST DATA KEYS: ' . implode(', ', array_keys($all_post_data)));
        
        $form_data = $this->input->post();
        unset($form_data[$this->security->get_csrf_token_name()]);

        if (empty($form_data)) {
            throw new Exception('No form data provided');
        }

        // ✅ FIX: Better field mapping for employment type and industry
        $employment_type = $this->get_field_value($form_data, [
            'mod_jobs.employment_type', 
            'mod_jobs_employment_type', 
            'employment_type',
            'job_type',
            'mod_job_type'
        ], 'full-time');
        
        $industry_id = $this->get_field_value($form_data, [
            'mod_industries.id',
            'mod_industries_id',
            'industry_id',
            'mod_jobs.industry_id',
            'mod_jobs_industry_id'
        ], null);

        log_message('debug', '✅ Employment Type Found: ' . $employment_type);
        log_message('debug', '✅ Industry ID Found: ' . $industry_id);

        // Extract skills and qualifications as comma-separated strings
        $skills_text = '';
        $qualifications_text = '';
        
        // Look for skills fields (existing code...)
        $possible_skill_fields = [
            'mod_job_skills.name',
            'mod_job_skills_name', 
            'skills',
            'mod_jobs.skills',
            'mod_jobs_skills'
        ];
        
        foreach ($possible_skill_fields as $field) {
            if (isset($form_data[$field]) && !empty(trim($form_data[$field]))) {
                $skills_text = trim($form_data[$field]);
                log_message('debug', '✅ Found skills: ' . $skills_text);
                break;
            }
        }
        
        // Look for qualifications fields (existing code...)
        $possible_qualification_fields = [
            'mod_job_qualifications.name',
            'mod_job_qualifications_name',
            'qualifications',
            'mod_jobs.qualifications',
            'mod_jobs_qualifications'
        ];
        
        foreach ($possible_qualification_fields as $field) {
            if (isset($form_data[$field]) && !empty(trim($form_data[$field]))) {
                $qualifications_text = trim($form_data[$field]);
                log_message('debug', '✅ Found qualifications: ' . $qualifications_text);
                break;
            }
        }

        // Complete job data with proper field mapping
        $job_data = [
            'name' => $this->get_field_value($form_data, ['mod_jobs.name', 'mod_jobs_name', 'name'], 'Unnamed Job'),
            'agency_id' => $template->agency_id ?? 1,
            'industry_id' => $industry_id,
            'employment_type' => $employment_type,
            'location' => $this->get_field_value($form_data, ['mod_jobs.location', 'mod_jobs_location', 'location'], ''),
            'site' => $this->get_field_value($form_data, ['mod_jobs.site', 'mod_jobs_site', 'site'], ''),
            'pay_cycle' => $this->get_field_value($form_data, ['mod_jobs.pay_cycle', 'mod_jobs_pay_cycle', 'pay_cycle'], ''),
            'description' => $this->get_field_value($form_data, ['mod_jobs.description', 'mod_jobs_description', 'description'], ''),
            'contract_type' => $this->get_field_value($form_data, ['mod_jobs.contract_type', 'mod_jobs_contract_type', 'contract_type'], 'permanent'),
            'pay_type' => $this->get_field_value($form_data, ['mod_jobs.pay_type', 'mod_jobs_pay_type', 'pay_type'], 'salary'),
            'pay_rate' => $this->get_field_value($form_data, ['mod_jobs.pay_rate', 'mod_jobs_pay_rate', 'pay_rate'], ''),
            'salary_min' => $this->get_field_value($form_data, ['mod_jobs.salary_min', 'mod_jobs_salary_min', 'salary_min'], ''),
            'salary_max' => $this->get_field_value($form_data, ['mod_jobs.salary_max', 'mod_jobs_salary_max', 'salary_max'], ''),
            'department' => $this->get_field_value($form_data, ['mod_jobs.department', 'mod_jobs_department', 'department'], ''),
            'project_overview' => $this->get_field_value($form_data, ['mod_jobs.project_overview', 'mod_jobs_project_overview', 'project_overview'], ''),
            'transport' => $this->get_field_value($form_data, ['mod_jobs.transport', 'mod_jobs_transport', 'transport'], 'no'),
            'roster' => $this->get_field_value($form_data, ['mod_jobs.roster', 'mod_jobs_roster', 'roster'], 'no'),
            'accommodation' => $this->get_field_value($form_data, ['mod_jobs.accommodation', 'mod_jobs_accommodation', 'accommodation'], 'no'),
            'is_remote' => $this->get_field_value($form_data, ['mod_jobs.is_remote', 'mod_jobs_is_remote', 'is_remote'], 0),
            'application_email' => $this->get_field_value($form_data, ['mod_jobs.application_email', 'mod_jobs_application_email', 'application_email'], ''),
            'application_url' => $this->get_field_value($form_data, ['mod_jobs.application_url', 'mod_jobs_application_url', 'application_url'], ''),
            'closing_date' => $this->get_field_value($form_data, ['mod_jobs.closing_date', 'mod_jobs_closing_date', 'closing_date'], ''),
            'position_quantity' => $this->get_field_value($form_data, ['mod_jobs.position_quantity', 'mod_jobs_position_quantity', 'position_quantity'], ''),
            'skills' => $skills_text,
            'qualifications' => $qualifications_text,
            'reference_number' => $this->generate_job_reference(),
            'enabled' => 1,
            'removed' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        log_message('debug', '📦 Job data prepared:');
        log_message('debug', '📦 - Employment Type: ' . $job_data['employment_type']);
        log_message('debug', '📦 - Industry ID: ' . $job_data['industry_id']);
        log_message('debug', '📦 - Skills: "' . $skills_text . '"');
        log_message('debug', '📦 - Qualifications: "' . $qualifications_text . '"');

        // Validate required fields
        if (empty($job_data['name']) || empty(trim($job_data['name']))) {
            throw new Exception('Missing or empty job name: "' . $job_data['name'] . '"');
        }
        
        if (empty($job_data['agency_id'])) {
            throw new Exception('Missing agency_id: ' . $job_data['agency_id']);
        }

        // Load Model_jobs
        $this->load->model('admin/Model_jobs');
        
        $job_id = $this->Model_jobs->save_from_template($template_id, $job_data, $form_data);

        if ($job_id) {
            log_message('debug', '✅ JOB SAVED WITH ID: ' . $job_id);
            
            // ✅ NEW: SEND NOTIFICATION TO RECRUITERS (same as manual job creation)
            $this->send_template_job_notification($job_id, $job_data);
            
            $this->output->set_output(json_encode([
                'success' => true,
                'message' => 'Job saved! ID: ' . $job_id,
                'job_id' => $job_id,
                'debug' => [
                    'employment_type' => $employment_type,
                    'industry_id' => $industry_id,
                    'skills_saved' => $skills_text,
                    'qualifications_saved' => $qualifications_text
                ]
            ]));
        } else {
            throw new Exception('Model_jobs->save_from_template() returned false');
        }
        
        log_message('debug', '🔍 === SAVE AS JOB DEBUG END ===');

    } catch (Exception $e) {
        log_message('error', '❌ Save as Job error: ' . $e->getMessage());
        $this->output->set_output(json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]));
    }
}

// ✅ NEW: Helper method to get field value with multiple possible names
private function get_field_value($form_data, $field_names, $default = null) {
    if (!is_array($field_names)) {
        $field_names = [$field_names];
    }
    
    foreach ($field_names as $field_name) {
        if (isset($form_data[$field_name]) && $form_data[$field_name] !== '') {
            return $form_data[$field_name];
        }
    }
    
    return $default;
}

// ✅ NEW: Generate unique job reference
private function generate_job_reference() {
    $ref = 'JOB-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Check if reference already exists
    $exists = $this->db->where('reference_number', $ref)
                      ->where('removed', 0)
                      ->count_all_results('mod_jobs') > 0;
    
    if ($exists) {
        // If exists, generate new one
        return 'JOB-' . date('Ymd') . '-' . rand(1000, 9999);
    }
    
    return $ref;
}

// Helper method for slug (if not already present)
private function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

public function force_agency_1_test() 
{
    echo "<h1>FORCE AGENCY 1 TEST</h1>";
    
    // Force agency_id = 1 in session
    $this->session->set_userdata('agency_id', 1);
    
    echo "Forced session agency_id to: " . $this->session->userdata('agency_id') . "<br>";
    
    // Now test the model
    $this->load->model('agency/Model_templates');
    $templates = $this->Model_templates->get_hybrid_templates([]);
    
    echo "Templates returned: " . count($templates) . "<br>";
    
    foreach ($templates as $template) {
        $agency = isset($template->agency_id) ? $template->agency_id : 'N/A';
        $type = isset($template->template_type) ? $template->template_type : 'single';
        $name = isset($template->name) ? $template->name : (isset($template->template_name) ? $template->template_name : 'Unnamed');
        
        $color = $agency == 1 ? 'green' : 'red';
        echo "<div style='color: $color;'>Type: $type, Name: $name, Agency ID: $agency</div>";
    }
    
    // Test the actual templates page
    echo "<h2>Test Actual Page:</h2>";
    echo "<a href='" . site_url('agency/templates') . "' target='_blank'>Go to Templates Page</a><br>";
    echo "Check if you now see only agency 1 templates.";
}

public function fix_agency_session() 
{
    echo "<h1>FIXING AGENCY SESSION</h1>";
    
    $login_data = $this->session->userdata('login');
    $current_agency_id = $this->session->userdata('agency_id');
    
    echo "Current session agency_id: $current_agency_id<br>";
    echo "Login data agency_id: " . ($login_data['agency']['agency_id'] ?? 'NOT SET') . "<br>";
    
    if (!empty($login_data['agency']['agency_id'])) {
        $correct_agency_id = $login_data['agency']['agency_id'];
        
        if ($current_agency_id != $correct_agency_id) {
            echo "⚠️ MISMATCH DETECTED! Fixing: $current_agency_id -> $correct_agency_id<br>";
            $this->session->set_userdata('agency_id', $correct_agency_id);
            echo "✅ Fixed! New session agency_id: " . $this->session->userdata('agency_id') . "<br>";
        } else {
            echo "✅ Session agency_id is correct<br>";
        }
    }
    
    // Test the model now
    echo "<h2>Testing Model After Fix:</h2>";
    $this->load->model('admin/Model_templates');
    $templates = $this->Model_templates->get_hybrid_templates([]);
    echo "Templates returned: " . count($templates) . "<br>";
    
    foreach ($templates as $template) {
        $agency = $template->agency_id ?? 'N/A';
        $color = $agency == $correct_agency_id ? 'green' : 'red';
        echo "<div style='color: $color;'>";
        echo "Type: {$template->template_type}, Name: {$template->name}, Agency ID: $agency";
        echo "</div>";
    }
    
    echo "<br><a href='" . site_url('agency/templates') . "'>Go to Templates Page</a>";
}

public function test_simple_ajax($template_id)
{
    header('Content-Type: application/json');
    
    try {
        // Clear any existing output
        if (ob_get_length()) ob_clean();
        
        $response = [
            'success' => true,
            'message' => 'Simple test successful',
            'template_id' => $template_id,
            'session_ok' => $this->session->userdata('is_logged_in') ? 'YES' : 'NO',
            'agency_id' => $this->session->userdata('agency_id'),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($response);
        exit;
        
    } catch (Exception $e) {
        // Clear any existing output
        if (ob_get_length()) ob_clean();
        
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }
}


}