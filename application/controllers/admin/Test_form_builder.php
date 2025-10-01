<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Test_form_builder extends CRUD_Controller {
    public $pageName = 'test_form_builder';
    public $group = 'form_tests';
    public $view = '';
    public $model = 'Model_test_form_builder';
    public $sorting = array('id' => 'ASC');
    public $singular = 'test_form';
    public $plural = 'test_forms';
    public $seoFields = true;
    public $quickManage = true;
    public $quickManageSize = 5;
    public $sluggify = false;


    public function __construct() {
        parent::__construct();
        
        $this->setup_listing();
        $this->setup_fields();
        $this->load->model($this->folder . '/' . $this->model);
        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true)
        );
        $this->load->library('Form_builder');
    }

    private function setup_listing() {
        $this->listFields = array(
            'id' => array(
                'label' => 'ID',
                'sort' => true
            ),
            'name' => array(
                'label' => lang('label_name'),
                'sort' => true
            )
        );

        $this->listActions = array(
            'edit' => array(
                'label'     => lang('label_edit'),
                'url'       => url($this->pageName . '/edit/{id}'),
                'icon'      => 'fa-edit',
                'class'     => 'edit-row',
            ),
            'enable' => array(
                'label'     => lang('label_enable'),
                'url'       => url($this->pageName . '/enable/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'enable-row btn-enable',
                'function'  => (function ($str, $row) {
                    return ($row->enabled) ? false : $str;
                })
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => url($this->pageName . '/disable/{id}'),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => (function ($str, $row) {
                    return (!$row->enabled) ? false : $str;
                })
            ),
            'delete' => array(
                'label'     => lang('label_delete'),
                'url'       => url($this->pageName . '/remove/{id}'),
                'icon'      => 'fa-trash-o',
                'class'     => 'delete-row btn-delete',
            )
        );

        $this->filters = array(
            //dropdown filter
            'general' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array(
                    'sys_form_schemas.name'
                )
            )
        );
    }

public function setup_fields() {
    $this->formFields = array(
        'main' => array(
            'name' => 'trim|required|strip_tags',
            'schema' => 'trim|strip_tags',
            'scripts' => 'trim|strip_tags',
            'styling' => 'trim|strip_tags',
            'form_element_id' => 'trim|strip_tags',
            'form_classes' => 'trim|strip_tags',
            'form_action' => 'trim|strip_tags',
            // No dynamic_fields section!
        )
    );
}

  public function index() {
    $this->breadcrumbs = [
        [
            'title' => lang($this->pageName . '_heading') ?: 'Form Builder',
            'url' => redir($this->pageName, true)
        ]
    ];

    $this->view = 'listing';
    $this->load->view($this->folder . '/view_header');
    $this->load->view('cms/crud/view_list', [
        'heading' => lang($this->pageName . '_heading'),
        'noRows' => lang($this->pageName . '_no_rows')
    ]);
    $this->load->view($this->folder . '/view_footer');
}

//this collects the forms from table and show in the listing view
public function ajax_results() {
    // Sanitize input
    $page   = max(1, (int) $this->input->get('page'));
    $limit  = min(100, max(1, (int) $this->input->get('limit')));
    $offset = ($page - 1) * $limit;

    // Search filter
    $search = $this->input->get('general');
    $search = ($search !== null && $search !== '') ? trim($search) : null;

    // Sorting
    $sort_field = $this->input->get('sort_field') ?: 'id';
    $sort_order = strtoupper($this->input->get('sort_order') ?: 'ASC');

    if (!in_array($sort_field, ['id', 'name'])) {
        $sort_field = 'id';
    }
    if (!in_array($sort_order, ['ASC', 'DESC'])) {
        $sort_order = 'ASC';
    }

    // CORRECT TABLE NAME
    $this->db->from('sys_form_schemas');
    $this->db->where('removed', 0);
    $this->db->where('deleted_at IS NULL');

    if ($search) {
        $this->db->like('name', $search);
    }

    $this->db->order_by($sort_field, $sort_order);
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    $rows = $query->result(); // ← Should contain your data

    // Total count
    $total_query = clone $this->db;
    $total = $total_query->count_all_results();

    log_message('debug', 'ajax_results: Found ' . count($rows) . ' rows, total=' . $total);

    // Format each row
    $data = array_map(function ($row) {
        $row->schema = json_decode($row->schema, true) ?: [];
        $row->scripts = json_decode($row->scripts, true) ?: [];
        $row->styling = json_decode($row->styling, true) ?: [];
        $row->enabled = !empty($row->enabled) ? 1 : 0;
        return $row;
    }, $rows);

    // Send response
    $response = [
        'success' => true,
        'data' => $data,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'last_page' => ceil($total / $limit)
        ]
    ];

    $this->output_json($response);
}


public function quick_manage_extra($id, $row) {
    $schema_row = null;

    if (!empty($id)) {
        $schema_row = $this->{$this->model}->get_by_id($id, 'sys_form_schemas');
    }

    // Default return data
    $default = [
        'form' => '',
        'schema' => [],
        'styling' => [],
        'scripts' => [],
        'input_types' => $this->{$this->model}->get_input_types(),
        'form_schemas' => list_options('sys_form_schemas', 'name', 'asc', 'id'),
        'from_db_schema' => '',
        'tables' => $this->db_tables(),
        'row' => $row,
        'df' => []
    ];

    if (!$schema_row || empty($schema_row->schema)) {
        log_message('debug', 'No schema found for id: ' . $id);
        return $default;
    }

    $decoded = json_decode($schema_row->schema, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        log_message('error', 'Invalid JSON in schema: ' . json_last_error_msg());
        return $default;
    }

    // Extract df
    $df = !empty($decoded['meta_data']['df']) ? $decoded['meta_data']['df'] : [];

    // Repair schema if needed
    if (!isset($decoded[0])) {
        $decoded[0] = ['row' => true, 'fields' => []];
    }
    if (!isset($decoded['form'])) {
        $decoded['form'] = [
            'id' => $schema_row->form_element_id ?: 'form_' . $id,
            'class' => $schema_row->form_classes ?: '',
            'action' => $schema_row->form_action ?: ''
        ];
    }

    $styling = json_decode($schema_row->styling ?? '{}', true);
    $scripts = json_decode($schema_row->scripts ?? '[]', true);
    $styling = is_array($styling) ? $styling : [];
    $scripts = is_array($scripts) ? $scripts : [];

    try {
        $form = $this->form_builder::make()
            ->set_schema($decoded)
            ->set_styling($styling)
            ->set_scripts($scripts)
            ->make_form();

        $layout_row = $this->db->get_where('mod_layouts', ['schema_id' => $id])->row();
        if (!$layout_row) {
            $layout_row = new stdClass();
        }

        $field_values = (array)$layout_row;
        foreach ($decoded[0]['fields'] as $name => $cfg) {
            if (!isset($field_values[$name])) {
                $field_values[$name] = '';
            }
        }

        // ✅ Merge form metadata into $row
        $merged_row = (object)array_merge((array)$row, $field_values, [
            'form_element_id' => $decoded['form']['id'] ?? $row->form_element_id ?? '',
            'form_classes' => $decoded['form']['class'] ?? $row->form_classes ?? '',
            'form_action' => $decoded['form']['action'] ?? $row->form_action ?? ''
        ]);

        return [
            'form' => $form->form_view,
            'schema' => $form->form_schema,
            'styling' => $styling,
            'scripts' => $scripts,
            'input_types' => $this->{$this->model}->get_input_types(),
            'form_schemas' => list_options('sys_form_schemas', 'name', 'asc', 'id'),
            'from_db_schema' => '',
            'tables' => $this->db_tables(),
            'row' => $merged_row,
            'df' => $df
        ];

    } catch (Exception $e) {
        log_message('error', 'Form builder error: ' . $e->getMessage());
        log_message('error', 'Schema causing error: ' . print_r($decoded, true));
    }

    // ✅ Always return something
    return $default;
}

private function fallback_response($row) {
    try {
        $from_db = $this->form_builder::make()
            ->from_db('mod_layouts')
            ->make_form();

        return [
            'form' => '',
            'schema' => '',
            'styling' => [],
            'scripts' => [],
            'input_types' => $this->{$this->model}->get_input_types(),
            'form_schemas' => list_options('sys_form_schemas', 'name', 'asc', 'id'),
            'from_db_view' => $from_db->form_view,
            'from_db_schema' => $this->schema_to_html($from_db->form_schema, false),
            'tables' => $this->db_tables(),
            'row' => $row
        ];
    } catch (Exception $e) {
        log_message('error', 'Fallback failed: ' . $e->getMessage());
        return [
            'form' => '<div class="error">Could not load form. Please try again.</div>',
            'schema' => [],
            'styling' => [],
            'scripts' => [],
            'input_types' => [],
            'form_schemas' => [],
            'from_db_schema' => '<em>No preview available</em>',
            'tables' => $this->db_tables(),
            'row' => $row
        ];
    }
}


public function update_success_extra($id) {
    // Optional: Delete any orphaned record with schema_id = 0
    $this->db->delete('mod_layouts', ['schema_id' => 0]);

    // Update or upsert mod_layouts record
    $this->db->replace('mod_layouts', [
        'schema_id' => $id,
        'name' => $this->input->post('name') ?: '',
        'code' => $this->input->post('code') ?: '',
        'description' => $this->input->post('description') ?: '',
        'preview_image' => $this->input->post('preview_image') ?: ''
    ]);
}
public function create_success_extra($id) {
    // Get POST data directly
    $this->db->insert('mod_layouts', [
        'schema_id' => $id,
        'name' => $this->input->post('name') ?: '',
        'code' => $this->input->post('code') ?: '',
        'description' => $this->input->post('description') ?: '',
        'preview_image' => $this->input->post('preview_image') ?: '',
        'enabled' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ]);
}

public function store_df_data($data) {
    $df = $this->input->post('df');

    $compiled_schema = [
        'form' => [
            'id' => $data['form_element_id'] ?? 'form_' . time(),
            'class' => $data['form_classes'] ?? '',
            'action' => $data['form_action'] ?? ''
        ]
    ];

    $schema_rows = $this->df_to_schema($df, 'form-rows');
    $compiled_schema = array_merge($compiled_schema, $schema_rows);

    if (!isset($compiled_schema[0])) {
        $compiled_schema[0] = ['row' => true, 'fields' => []];
    }

    $compiled_schema['meta_data']['df'] = $df;

    return [
        'name' => $data['name'] ?? 'Untitled Form',
        'schema' => json_encode($compiled_schema),
        'styling' => json_encode($this->df_to_styling($df)),
        'scripts' => json_encode($this->df_to_scripts($df)),
        'enabled' => 1
    ];
}
    public function db_tables()
    {
        //return a list of all tables in the database
        $tables = $this->db->list_tables();
        $table_list = [];
        foreach ($tables as $table) {
            $table_list[$table] = $table;
        }

        return $table_list;
    }

    public function update_modify_params($params){
        return $this->store_df_data($params);
    }

    public function create_modify_params($params){
        return $this->store_df_data($params);
    }

    public function get_all_dynamic_field_data($rowID = 0){        
        $df = array();
		if ( ! empty($this->formFields['dynamic_fields']) && $this->input->post('df')) {
            $df = $this->input->post('df');
		} elseif ( ! empty($this->formFields['dynamic_fields']) && ! empty($rowID)) {
            
            $df = $this->{$this->model}->get_form_data($rowID)[0];
            $df = json_decode($df['schema'], true);
            $df = $df['meta_data']['df'] ?? [];
		}

		return $df;
    }

 
    public function df_to_schema($df, $sub_field) {
        if(!isset($df[$sub_field])){
            return [];
        }

        $schema = [];
        foreach ($df[$sub_field] as $field) {
            //set header and remove header from the field array
            $header = $field['row_header'] ?? '';
            unset($field['row_header']);

            //set row custom classes and remove row_custom_class from field array
            $row_custom_class = $field['row_custom_class'] ?? '';
            unset($field['row_custom_class']);

            //set row and remove row from the field array
            $row = $field['field_new_row'] ?? true;
            unset($field['field_new_row']);
            
            //set fields
            $fields = [];
            if(!isset($field['df-sub']) || !is_array($field['df-sub'])) {
                continue; // skip if df-sub is not set or not an array
            }
            foreach ($field['df-sub'] as $key => $input) {
                $fields[$input['field_name']] = [
                    'type' => $input['field_type'],
                    'required' => isset($input['field_required']) ? $input['field_required'] : false,
                    'label' => $input['field_label'],
                    'options' => $input['field_input_options'],
                    'col' => [
                        'sm' => $input['field_col_sm'],
                        'md' => $input['field_col_md'],
                        'lg' => $input['field_col_lg'],
                    ],
                    'attr' => $input['field_attr'],
                ];
            }
            $schema[] = [
                'row' => $row,
                'header' => $header,
                'custom_class' => $row_custom_class,
                'fields' => $fields
            ];
        }

        return $schema;
    }

    public function df_to_styling($df_styling) {
    if (!is_array($df_styling)) {
        return [];
    }

    $result = [];
    foreach ($df_styling as $styles) {
        $element = $styles['styling_element'] ?? '';
        $css = $styles['styling_css'] ?? '';

        if (!empty($element) && !empty($css)) {
            $result[$element] = $css;
        }
    }

    return $result;
}

    public function df_to_scripts($df_scripts) {
    if (!is_array($df_scripts)) {
        return [];
    }

    $result = [];
    foreach ($df_scripts as $script_holder) {
        $script = $script_holder['scripts'] ?? '';
        if (!empty($script)) {
            $result[] = $script;
        }
    }

    return $result;
}

public function generate_from_db_table() {
    $table = $this->input->post('table');
    if (!$table) {
        return $this->output_json([
            'success' => false,
            'message' => lang('label_no_table_selected')
        ]);
    }

    try {
        // Generate form from DB
        $from_db = $this->form_builder::make()
            ->from_db($table)
            ->make_form();

        // Build minimal df structure for UI population
        $df = [
            'form-rows' => [
                'new_' . time() => [
                    'position' => '1',
                    'row_header' => 'Basic Fields',
                    'row_custom_class' => '',
                    'df-sub' => []
                ]
            ]
        ];

        $counter = 1;
        foreach ($from_db->form_schema[0]['fields'] as $name => $field) {
    $df['form-rows']['new_' . time()]['df-sub'][$counter] = [
        'field_name' => $name,
        'field_type' => $field['type'],
        'field_label' => $field['label'],
        'field_required' => match($name) {
            'schema_id', 'code' => '', // Not required
            default => !empty($field['required']) ? '1' : ''
        },
        'field_col_sm' => $field['col']['sm'] ?? '12',
        'field_col_md' => $field['col']['md'] ?? '6',
        'field_col_lg' => $field['col']['lg'] ?? '6',
        'field_attr' => $field['attr'] ?? '',
        'field_input_options' => $field['options'] ?? ''
    ];
    $counter++;
}

        $this->output_json([
            'success' => true,
            'form_schema' => $from_db->form_schema,
            'form_view' => $from_db->form_view,
            'from_db_schema' => $this->schema_to_html($from_db->form_schema, false),
            'df' => $df // ← This will be used by JS to populate inputs
        ]);

    } catch (Exception $e) {
        log_message('error', 'Failed to generate form from DB: ' . $e->getMessage());
        $this->output_json([
            'success' => false,
            'message' => 'Error generating form: ' . $e->getMessage()
        ]);
    }
}

    public function schema_to_html($schema) {
    if (empty($schema)) {
        return '<pre><em>No schema data</em></pre>';
    }

    $html = '<style>.schema-html textarea{height:500px;}</style>';
    $html .= '<div class="schema-html">';
    $html .= '<button class="btn btn-primary" onclick="copySchemaToClipboard(this)">Copy Schema</button>';
    $html .= field_textarea(
        'schema_array|label_form_scripts',
        htmlspecialchars(print_r($schema, true)),
        '',
        ['rows' => 100]
    );
    $html .= '</div>';

    //$html .= '<script>function copySchemaToClipboard(el){...}</script>';

    return $html;
}




    private function output_json($data) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    public function custom_saving() {
        $data = $this->input->post();
        // Custom saving logic can be added here

        // For now, we just return the data as is
        return $this->output_json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function custom_form_schema()
    {
        return ;
    }
public function render_schema() {
    $schema_json = $this->input->post('schema');
    if (!$schema_json) {
        return $this->output_json(['success' => false, 'message' => 'No schema provided']);
    }

    $schema = json_decode($schema_json, true);
    if (!$schema) {
        return $this->output_json(['success' => false, 'message' => 'Invalid schema']);
    }

    try {
        $form = $this->form_builder::make()
            ->set_schema($schema)
            ->make_form();

        $this->output_json([
            'success' => true,
            'form_view' => $form->form_view
        ]);
    } catch (Exception $e) {
        log_message('error', 'Render schema error: ' . $e->getMessage());
        $this->output_json([
            'success' => false,
            'message' => 'Error rendering form'
        ]);
    }
}
//     public function debug_forms() {
//     $rows = $this->Model_test_form_builder->get_all();
//     echo "<h2>Forms from sys_form_schemas:</h2><pre>";
//     print_r($rows);
//     echo "</pre>";
// }

//    public function get_form() {
//     // Accept id from POST or GET
//     $id = $this->input->post('id') ?: $this->input->get('id');

//     if (!$id) {
//         $this->output_json([
//             'success' => false,
//             'message' => 'Form ID is required'
//         ]);
//         return;
//     }

//     try {
//         $form = $this->form_builder::make()
//             ->get_schema($id)
//             ->make_form();

//         $this->output_json([
//             'success' => true,
//             'id' => $id,
//             'form' => [
//                 'form_view' => $form->form_view,
//                 'form_schema' => $form->form_schema
//             ]
//         ]);
//     } catch (Exception $e) {
//         log_message('error', 'Failed to load form schema for ID ' . $id . ': ' . $e->getMessage());
//         $this->output_json([
//             'success' => false,
//             'message' => 'Error loading form: ' . $e->getMessage()
//         ]);
//     }
// }

public function get_form_by_schema_id($schema_id = null) {
    // Get from URI or POST
    $schema_id = $schema_id ?: $this->input->post('schema_id');

    if (!$schema_id || !is_numeric($schema_id)) {
        log_message('error', 'Invalid schema_id: ' . $schema_id);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid schema ID']);
        return;
    }

    // Load schema
    $row = $this->db->where('id', $schema_id)->get('sys_form_schemas')->row();

    if (!$row) {
        log_message('error', 'Schema not found for ID: ' . $schema_id);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Form schema not found']);
        return;
    }

    $schema = json_decode($row->schema, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        log_message('error', 'JSON decode error: ' . json_last_error_msg());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid schema format']);
        return;
    }

    try {
        $form = $this->form_builder::make()->set_schema($schema)->make_form();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'form_view' => $form->form_view,
            'form_schema' => $form->form_schema
        ]);
    } catch (Exception $e) {
        log_message('error', 'Form build failed: ' . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Render failed: ' . $e->getMessage()
        ]);
    }
}
}