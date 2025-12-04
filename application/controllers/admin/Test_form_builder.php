<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Test_form_builder extends CRUD_Controller {
    public $pageName = 'test_form_builder';
    public $group = 'form_tests';
    public $view = '';
    public $model = 'Model_test_form_builder';
    public $sorting = array('id' => 'ASC');
    public $singular = 'Form';
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
    log_message('debug', '=== QUICK_MANAGE_EXTRA CALLED ===');
    log_message('debug', 'ID: ' . $id);
    log_message('debug', 'Row ID: ' . ($row->id ?? 'NO ROW ID'));
    
    // ✅ DETECT SOURCE
    $source = 'form_builder'; // Default source
    $return_url = $this->session->userdata('return_to_template_sections');
    
    if ($return_url) {
        $source = 'template_sections';
        log_message('debug', 'Form builder opened from template sections');
    }

     // Get field suggestions from database tables
    $field_suggestions = $this->{$this->model}->get_field_suggestions();
    $db_fields = $this->{$this->model}->get_table_fields();
    $field_type_mapping = $this->{$this->model}->get_db_field_type_mapping();
    $medical_recommendations = $this->{$this->model}->get_medical_field_recommendations(); // ✅ ADD MEDICAL RECOMMENDATIONS

     log_message('debug', 'Field suggestions count: ' . count($field_suggestions));
    log_message('debug', 'DB fields count: ' . count($db_fields));
    log_message('debug', 'Medical recommendations: ' . count($medical_recommendations));

    // Get field suggestions from database tables
    $field_suggestions = $this->{$this->model}->get_field_suggestions();
    $db_fields = $this->{$this->model}->get_table_fields();
    $field_type_mapping = $this->{$this->model}->get_db_field_type_mapping();

    log_message('debug', 'Field suggestions count: ' . count($field_suggestions));
    log_message('debug', 'DB fields count: ' . count($db_fields));

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
        'df' => [],
        'current_fields' => [],
        'source' => $source,
        'field_suggestions' => $field_suggestions,
        'db_fields' => $db_fields,
        'field_type_mapping' => $field_type_mapping,
        'medical_recommendations' => $medical_recommendations // ✅ ADD MEDICAL RECOMMENDATIONS
    ];

    if (empty($id)) {
        log_message('debug', 'No ID - creating new form from source: ' . $source);
        return $default;
    }

    // ✅ CRITICAL FIX: Ensure we're loading the correct form
    $schema_row = $this->{$this->model}->get_by_id($id, 'sys_form_schemas');
    
    if (!$schema_row) {
        log_message('error', 'No schema found for id: ' . $id);
        return $default;
    }

    log_message('debug', 'Loaded schema from DB - name: ' . $schema_row->name);

    $decoded = json_decode($schema_row->schema ?? '{}', true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        log_message('error', 'Invalid JSON in schema: ' . json_last_error_msg());
        return $default;
    }

    // ✅ EXTRACT CURRENT FIELDS FOR DISPLAY
    $current_fields = [];
    if (isset($decoded['meta_data']['df']['form-rows'])) {
        foreach ($decoded['meta_data']['df']['form-rows'] as $form_row) {
            if (isset($form_row['df-sub'])) {
                foreach ($form_row['df-sub'] as $subField) {
                    if (!empty($subField['field_name'])) {
                        $current_fields[] = [
                            'field_name' => $subField['field_name'],
                            'field_type' => $subField['field_type'] ?? 'text',
                            'field_label' => $subField['field_label'] ?? '',
                            'field_required' => $subField['field_required'] ?? '0',
                            'field_col_sm' => $subField['field_col_sm'] ?? '12',
                            'field_col_md' => $subField['field_col_md'] ?? '6',
                            'field_col_lg' => $subField['field_col_lg'] ?? '6',
                            'field_attr' => $subField['field_attr'] ?? '',
                            'field_input_options' => $subField['field_input_options'] ?? ''
                        ];
                    }
                }
            }
        }
    }

    log_message('debug', 'Found ' . count($current_fields) . ' current fields');

    // Repair schema for form generation
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

        // ✅ CRITICAL: Ensure the row has ALL required properties including NAME
        $merged_row = (object)array_merge((array)$row, [
            'id' => $id,
            'name' => $schema_row->name ?? $row->name ?? 'Unnamed Form',
            'form_element_id' => $decoded['form']['id'] ?? $row->form_element_id ?? '',
            'form_classes' => $decoded['form']['class'] ?? $row->form_classes ?? '',
            'form_action' => $decoded['form']['action'] ?? $row->form_action ?? ''
        ]);

        log_message('debug', 'Final merged row - ID: ' . ($merged_row->id ?? 'NO ID') . ', Name: ' . ($merged_row->name ?? 'NO NAME'));

        $result = [
            'form' => $form->form_view,
            'schema' => $form->form_schema,
            'styling' => $styling,
            'scripts' => $scripts,
            'input_types' => $this->{$this->model}->get_input_types(),
            'form_schemas' => list_options('sys_form_schemas', 'name', 'asc', 'id'),
            'from_db_schema' => '',
            'tables' => $this->db_tables(),
            'row' => $merged_row,
            'df' => [],
            'current_fields' => $current_fields,
            'source' => $source,
            'field_suggestions' => $field_suggestions, // ✅ Include field suggestions
            'db_fields' => $db_fields, // ✅ Include detailed field info
            'field_type_mapping' => $field_type_mapping // ✅ Include type mapping
        ];

        log_message('debug', 'Returning data with source: ' . $source);
        log_message('debug', 'Field suggestions included: ' . count($field_suggestions) . ' groups');
        log_message('debug', 'DB fields included: ' . count($db_fields) . ' fields');

        return $result;

    } catch (Exception $e) {
        log_message('error', 'Form builder error: ' . $e->getMessage());
        // Return default with source included even on error
        $default['source'] = $source;
        return $default;
    }
}
public function verify_database_data($id) {
    log_message('debug', '=== VERIFYING DATABASE DATA ===');
    
    $this->db->select('id, name, schema, updated_at');
    $this->db->where('id', $id);
    $row = $this->db->get('sys_form_schemas')->row();
    
    if ($row) {
        log_message('debug', 'DB Row - ID: ' . $row->id);
        log_message('debug', 'DB Row - Name: ' . $row->name);
        log_message('debug', 'DB Row - Updated: ' . $row->updated_at);
        log_message('debug', 'DB Row - Schema: ' . substr($row->schema ?? 'EMPTY', 0, 500));
    } else {
        log_message('debug', 'No row found in database for ID: ' . $id);
    }
    
    return $row;
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
    log_message('debug', '=== UPDATE SUCCESS EXTRA CALLED ===');
    
    // Clear any session cache for this form
    $this->session->unset_userdata('form_builder_cache_' . $id);
    
    // ✅ UPDATE existing template section with new form data
    $form_data = [
        'name' => $this->input->post('name'),
        'description' => $this->input->post('description')
    ];
    $this->create_or_update_template_section($id, $form_data);

    // ✅ USE REPLACE TO UPDATE OR CREATE TEMPLATE
    $template_data = [
        'schema_id' => $id,
        'name' => $this->input->post('name') ?: '',
        'code' => $this->input->post('code') ?: '',
        'description' => $this->input->post('description') ?: '',
        'preview_image' => $this->input->post('preview_image') ?: '',
        'enabled' => 1,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // For new forms, add created_at
    $existing_template = $this->db->get_where('mod_layouts', ['schema_id' => $id])->row();
    if (!$existing_template) {
        $template_data['created_at'] = date('Y-m-d H:i:s');
    }
    
    $this->db->replace('mod_layouts', $template_data);
    log_message('debug', '✅ Replaced template for schema_id: ' . $id);

    log_message('debug', '=== UPDATE SUCCESS EXTRA COMPLETED ===');
}
public function cleanup_duplicate_templates()
{
    echo "<h2>Cleaning up duplicate templates:</h2>";
    echo "<pre>";
    
    // Find duplicate templates (same schema_id)
    $this->db->select('schema_id, COUNT(*) as count, GROUP_CONCAT(id) as template_ids')
             ->from('mod_layouts')
             ->where('schema_id IS NOT NULL')
             ->group_by('schema_id')
             ->having('count > 1');
    $duplicates = $this->db->get()->result();
    
    foreach ($duplicates as $dup) {
        echo "Found {$dup->count} templates for schema_id: {$dup->schema_id}\n";
        echo "Template IDs: {$dup->template_ids}\n";
        
        // Keep the most recent one, delete the rest
        $this->db->select('id')
                 ->from('mod_layouts')
                 ->where('schema_id', $dup->schema_id)
                 ->order_by('updated_at', 'DESC')
                 ->order_by('id', 'DESC')
                 ->limit(1);
        $keep = $this->db->get()->row();
        
        if ($keep) {
            $this->db->where('schema_id', $dup->schema_id)
                     ->where('id !=', $keep->id)
                     ->delete('mod_layouts');
            $deleted = $this->db->affected_rows();
            echo "✅ Kept template ID: {$keep->id}, deleted {$deleted} duplicates for schema_id: {$dup->schema_id}\n";
        }
        echo "---\n";
    }
    
    if (empty($duplicates)) {
        echo "No duplicate templates found!\n";
    }
    
    echo "Done!";
    echo "</pre>";
}
public function create_success_extra($id) 
{
    log_message('debug', '=== CREATE SUCCESS EXTRA CALLED ===');
    
    // Get form data
    $form_data = [
        'name' => $this->input->post('name'),
        'description' => $this->input->post('description')
    ];

    // ✅ AUTO-CREATE TEMPLATE SECTION ONLY FOR NEW FORMS
    $this->create_or_update_template_section($id, $form_data);

    // ✅ CHECK IF TEMPLATE ALREADY EXISTS (shouldn't for new forms, but just in case)
    $existing_template = $this->db->get_where('mod_layouts', ['schema_id' => $id])->row();
    
    if (!$existing_template) {
        // Only create template if it doesn't exist
        $this->db->insert('mod_layouts', [
            'schema_id' => $id,
            'name' => $this->input->post('name') ?: '',
            'code' => $this->input->post('code') ?: '',
            'description' => $this->input->post('description') ?: '',
            'preview_image' => $this->input->post('preview_image') ?: '',
            'enabled' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        log_message('debug', '✅ Created new template for new form ID: ' . $id);
    } else {
        log_message('debug', '⚠️ Template already exists for new form ID: ' . $id . ' - skipping template creation');
    }

    // ✅ FORCE CLEAR ANY CACHE
    $this->output->delete_cache();
}



public function store_df_data($data) {
      $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->post($csrf_name);
    
    if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
        log_message('error', 'CSRF validation failed in store_df_data()');
        return [
            'name' => 'Invalid CSRF Token',
            'schema' => json_encode([]),
            'styling' => json_encode([]),
            'scripts' => json_encode([]),
            'enabled' => 0
        ];
    }
    $df = $this->input->post('df');
    
    log_message('debug', '=== STORE_DF_DATA START ===');
    log_message('debug', 'Raw DF from POST: ' . print_r($df, true));

    // ✅ SAFETY CHECK: Ensure form-rows exists and is valid
    if (!isset($df['form-rows']) || !is_array($df['form-rows'])) {
        $df['form-rows'] = [];
    }

    // ✅ COMPLETE RESET: Start with fresh structure
    $clean_df = [
        'form-rows' => [],
        'form-styling' => isset($df['form-styling']) ? $df['form-styling'] : [],
        'form-scripts' => isset($df['form-scripts']) ? $df['form-scripts'] : []
    ];

    $row_counter = 0;
    foreach ($df['form-rows'] as $rowKey => $row) {
        // Skip if no sub-fields
        if (empty($row['df-sub']) || !is_array($row['df-sub'])) {
            continue;
        }
        
        $clean_row = [
            'position' => ++$row_counter,
            'row_header' => $row['row_header'] ?? '',
            'row_custom_class' => $row['row_custom_class'] ?? '',
            'df-sub' => []
        ];
        
        $sub_counter = 0;
        foreach ($row['df-sub'] as $subKey => $subField) {
            // Only include fields with valid field_name
            if (!empty($subField['field_name']) && is_string($subField['field_name'])) {
                $clean_row['df-sub'][++$sub_counter] = [
                    'field_name' => trim($subField['field_name']),
                    'field_type' => $subField['field_type'] ?? 'text',
                    'field_label' => $subField['field_label'] ?? '',
                    'field_required' => isset($subField['field_required']) ? $subField['field_required'] : '0',
                    'field_col_sm' => $subField['field_col_sm'] ?? '12',
                    'field_col_md' => $subField['field_col_md'] ?? '6',
                    'field_col_lg' => $subField['field_col_lg'] ?? '6',
                    'field_attr' => $subField['field_attr'] ?? '',
                    'field_input_options' => $subField['field_input_options'] ?? ''
                ];
            }
        }
        
        // Only add row if it has valid sub-fields
        if (!empty($clean_row['df-sub'])) {
            $clean_df['form-rows']['new_' . $row_counter] = $clean_row;
        }
    }

    log_message('debug', 'Cleaned DF: ' . print_r($clean_df, true));

    // Build schema from CLEAN data only
    $compiled_schema = [
        'form' => [
            'id' => $data['form_element_id'] ?? 'form_' . time(),
            'class' => $data['form_classes'] ?? '',
            'action' => $data['form_action'] ?? ''
        ]
    ];

    $schema_rows = $this->df_to_schema($clean_df, 'form-rows');
    $compiled_schema = array_merge($compiled_schema, $schema_rows);

    // ✅ ALWAYS ensure we have at least one valid row
    if (empty($schema_rows)) {
        $compiled_schema[0] = ['row' => true, 'fields' => []];
    }

    $compiled_schema['meta_data']['df'] = $clean_df;

    log_message('debug', 'Final compiled schema: ' . json_encode($compiled_schema));

    return [
        'name' => $data['name'] ?? 'Untitled Form',
        'schema' => json_encode($compiled_schema, JSON_UNESCAPED_UNICODE),
        'styling' => json_encode($this->df_to_styling($clean_df), JSON_UNESCAPED_UNICODE),
        'scripts' => json_encode($this->df_to_scripts($clean_df), JSON_UNESCAPED_UNICODE),
        'enabled' => 1
    ];
}

/**
 * Clean up fields that were deleted from the form builder
 */
private function cleanup_deleted_fields($form_id, &$new_df) {
    log_message('debug', '=== CLEANUP DELETED FIELDS ===');
    
    // Get current schema from database
    $current_row = $this->db->get_where('sys_form_schemas', ['id' => $form_id])->row();
    
    if (!$current_row || empty($current_row->schema)) {
        log_message('debug', 'No current schema found for cleanup');
        return;
    }
    
    $current_schema = json_decode($current_row->schema, true);
    $current_df = $current_schema['meta_data']['df'] ?? [];
    
    if (empty($current_df['form-rows'])) {
        log_message('debug', 'No current DF rows found for cleanup');
        return;
    }
    
    // Extract all current field names
    $current_field_names = [];
    foreach ($current_df['form-rows'] as $row) {
        if (!empty($row['df-sub'])) {
            foreach ($row['df-sub'] as $subField) {
                if (!empty($subField['field_name'])) {
                    $current_field_names[] = $subField['field_name'];
                }
            }
        }
    }
    
    // Extract all new field names
    $new_field_names = [];
    foreach ($new_df['form-rows'] as $row) {
        if (!empty($row['df-sub'])) {
            foreach ($row['df-sub'] as $subField) {
                if (!empty($subField['field_name'])) {
                    $new_field_names[] = $subField['field_name'];
                }
            }
        }
    }
    
    // Find fields that were deleted
    $deleted_fields = array_diff($current_field_names, $new_field_names);
    
    log_message('debug', 'Current fields: ' . implode(', ', $current_field_names));
    log_message('debug', 'New fields: ' . implode(', ', $new_field_names));
    log_message('debug', 'Deleted fields: ' . implode(', ', $deleted_fields));
    
    if (!empty($deleted_fields)) {
        log_message('debug', 'Cleaning up deleted fields: ' . implode(', ', $deleted_fields));
        
        // Remove these fields from mod_layouts if they exist
        foreach ($deleted_fields as $field_name) {
            // Check if column exists before trying to update
            if ($this->db->field_exists($field_name, 'mod_layouts')) {
                $this->db->set($field_name, null);
                log_message('debug', 'Setting field to NULL: ' . $field_name);
            } else {
                log_message('debug', 'Field does not exist in mod_layouts: ' . $field_name);
            }
        }
        
        // Execute the update
        $this->db->where('schema_id', $form_id);
        $update_result = $this->db->update('mod_layouts');
        log_message('debug', 'Cleanup update result: ' . ($update_result ? 'SUCCESS' : 'FAILED'));
    } else {
        log_message('debug', 'No fields to cleanup');
    }
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

public function update($id) {
    log_message('debug', '=== UPDATE METHOD START ===');
     // ✅ ADD CSRF VALIDATION
    if (!is_ajax()) {
        // Validate CSRF for non-AJAX requests
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            flash_notification('Invalid CSRF token. Please try again.', 'error');
            redir($this->pageName);
            return;
        }
    }
    log_message('debug', 'ID from URL: ' . $id);
    log_message('debug', 'POST ID: ' . $this->input->post('id'));
    log_message('debug', 'POST data keys: ' . print_r(array_keys($this->input->post()), true));
    
    // ✅ CRITICAL FIX: Use POST ID instead of URL parameter if they differ
    $post_id = $this->input->post('id');
    if ($post_id && $post_id != $id) {
        log_message('debug', 'ID MISMATCH DETECTED: URL=' . $id . ', POST=' . $post_id);
        $id = $post_id; // Use the POST ID
    }
    
 
    // ECMS no longer supports a direct form post
    if (!is_ajax()) {
        flash_notification(lang('not_ajax_error'), 'error');
        redir($this->pageName);
        return;
    }

    $this->setup_validation('edit');
    $identifier = $this->input->post($this->identifierField);

    if ($this->form_validation->run()) {
        log_message('debug', '=== VALIDATION PASSED ===');
        
        // ✅ VERIFY THE FORM EXISTS BEFORE UPDATING
        $existing_form = $this->{$this->model}->get_by_id($id);
        if (!$existing_form) {
            log_message('error', 'Form with ID ' . $id . ' does not exist');
            ajax_return(array(
                'success' => FALSE,
                'error' => 'Form not found. Please refresh and try again.'
            ));
            return;
        }
        
        $this->load->helper('string');
        $messageParams = array('name' => $identifier);

        //If you want to add extra data to the post
        $extra = $this->update_extra_params($id);
        log_message('debug', 'Extra params: ' . print_r($extra, true));

        $params = $this->build_params($extra);
        log_message('debug', 'Built params before modify: ' . print_r($params, true));

        //Check if we included the slug as an editable field
        if($this->sluggify && !empty($params['slug'])) {
            $params['slug'] = $this->sluggify($params['slug'], null, $id);
        }

        //If you want to modify the existing post data
        log_message('debug', '=== CALLING UPDATE_MODIFY_PARAMS ===');
        $params = $this->update_modify_params($params);
        log_message('debug', 'Params after modify: ' . print_r($params, true));

        $this->db->trans_start();
        $updateResult = $this->{$this->model}->update($params, $id);
        log_message('debug', 'Update result: ' . $updateResult);
        
        // ✅ VERIFY DATABASE UPDATE
        $this->verify_database_data($id);
    
        $this->process_dynamic_fields($id);
        $this->process_multi_selects($id);
        $this->process_uploads($id);

        //Run extra update functionality
        $this->update_success_extra($id);

        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE) {
            log_message('debug', '=== UPDATE SUCCESS ===');
            ajax_return(array(
                'success' => TRUE,
                'flasherbody' => langs($this->pageName . '_update_success_description', $messageParams),
                'extra' => $this->update_return_extra($id)
            ));
        } else {
            log_message('error', '=== UPDATE FAILED ===');
            Anomalies::log('Failed to edit ' . $this->singular, $this->db->last_query());
            ajax_return(array(
                'success' => FALSE,
                'error' => langs($this->pageName . '_update_failed_description', $messageParams)
            ));
        }
    } else {
        log_message('debug', '=== VALIDATION FAILED ===');
        log_message('debug', 'Validation errors: ' . validation_errors());
        ajax_return(array(
            'success' => FALSE,
            'error' => langs('validation_errors_ajax', ['errors' => validation_errors()]),
            'fields' => $this->form_validation->get_errors()
        ));
    }
    
    log_message('debug', '=== UPDATE METHOD END ===');
    return;
}


public function create_modify_params($params){
    log_message('debug', '=== CREATE_MODIFY_PARAMS CALLED ===');
    
    // ✅ ADD VALIDATION BEFORE PROCESSING
    if (empty($params['name'])) {
        $params['name'] = 'Untitled Form ' . date('Y-m-d H:i:s');
    }
    
    if (empty($params['form_element_id'])) {
        $params['form_element_id'] = 'form_' . time();
    }
    
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
    // ✅ EXTRA SAFETY CHECKS
    if(!isset($df[$sub_field]) || !is_array($df[$sub_field])){
        return [];
    }

    $schema = [];
    $row_index = 0;
    
    foreach ($df[$sub_field] as $field) {
        // ✅ SKIP MALFORMED ROWS
        if (!is_array($field)) {
            continue;
        }

        //set header and remove header from the field array
        $header = $field['row_header'] ?? '';
        unset($field['row_header']);

        //set row custom classes and remove row_custom_class from field array
        $row_custom_class = $field['row_custom_class'] ?? '';
        unset($field['row_custom_class']);

        //set row and remove row from the field array
        $row = $field['field_new_row'] ?? true;
        unset($field['field_new_row']);
        
        //set fields - with validation
        $fields = [];
        if(isset($field['df-sub']) && is_array($field['df-sub'])) {
            foreach ($field['df-sub'] as $key => $input) {
                // ✅ VALIDATE EACH INPUT FIELD
                if (!is_array($input) || empty($input['field_name'])) {
                    continue; // skip invalid fields
                }
                
                $field_name = trim($input['field_name']);
                if (empty($field_name)) {
                    continue; // skip empty field names
                }
                
                $fields[$field_name] = [
                    'type' => $input['field_type'] ?? 'text',
                    'required' => isset($input['field_required']) ? (bool)$input['field_required'] : false,
                    'label' => $input['field_label'] ?? $field_name,
                    'options' => $input['field_input_options'] ?? [],
                    'col' => [
                        'sm' => $input['field_col_sm'] ?? '12',
                        'md' => $input['field_col_md'] ?? '6',
                        'lg' => $input['field_col_lg'] ?? '6',
                    ],
                    'attr' => $input['field_attr'] ?? [],
                ];
            }
        }
        
        // ✅ ONLY ADD ROW IF IT HAS FIELDS
        if (!empty($fields)) {
            $schema[$row_index] = [
                'row' => $row,
                'header' => $header,
                'custom_class' => $row_custom_class,
                'fields' => $fields
            ];
            $row_index++;
        }
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

        // Build df structure for UI population - FIXED: Proper structure
        $df = [];
        $row_counter = 1;
        
        foreach ($from_db->form_schema as $row_index => $row_data) {
            if (isset($row_data['fields']) && is_array($row_data['fields'])) {
                $df['form-rows']['new_' . $row_counter] = [
                    'position' => $row_counter,
                    'row_header' => $row_data['header'] ?? 'Basic Fields',
                    'row_custom_class' => $row_data['custom_class'] ?? '',
                    'df-sub' => []
                ];

                $field_counter = 1;
                foreach ($row_data['fields'] as $name => $field) {
                    $df['form-rows']['new_' . $row_counter]['df-sub'][$field_counter] = [
                        'field_name' => $name,
                        'field_type' => $field['type'] ?? 'text',
                        'field_label' => $field['label'] ?? ucfirst(str_replace('_', ' ', $name)),
                        'field_required' => !empty($field['required']) ? '1' : '0',
                        'field_col_sm' => $field['col']['sm'] ?? '12',
                        'field_col_md' => $field['col']['md'] ?? '6',
                        'field_col_lg' => $field['col']['lg'] ?? '6',
                        'field_attr' => !empty($field['attr']) ? json_encode($field['attr']) : '',
                        'field_input_options' => !empty($field['options']) ? json_encode($field['options']) : ''
                    ];
                    $field_counter++;
                }
                $row_counter++;
            }
        }

        log_message('debug', 'Generated DF from DB table: ' . print_r($df, true));

        $this->output_json([
            'success' => true,
            'form_schema' => $from_db->form_schema,
            'form_view' => $from_db->form_view,
            'from_db_schema' => $this->schema_to_html($from_db->form_schema, false),
            'df' => $df // This will be used by JS to populate inputs
        ]);

    } catch (Exception $e) {
        log_message('error', 'Failed to generate form from DB: ' . $e->getMessage());
        $this->output_json([
            'success' => false,
            'message' => 'Error generating form: ' . $e->getMessage()
        ]);
    }
}
    
public function schema_to_html($schema, $show_buttons = true) {
    if (empty($schema)) {
        return '<pre><em>No schema data</em></pre>';
    }

    $html = '<style>.schema-html textarea{height:500px;}</style>';
    $html .= '<div class="schema-html">';
    if ($show_buttons) {
        $html .= '<button class="btn btn-primary" onclick="copySchemaToClipboard(this)">Copy Schema</button>';
    }
    $html .= field_textarea(
        'schema_array|label_form_scripts',
        htmlspecialchars(print_r($schema, true)),
        '',
        ['rows' => 100]
    );
    $html .= '</div>';

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
// Add this method to Test_form_builder.php (around line 100, after __construct)
public function render_schema() {
    $schema_json = $this->input->post('schema');
    if (empty($schema_json)) {
        $this->output->set_output(json_encode(['success' => false, 'message' => 'No schema provided']));
        return;
    }

    $schema = json_decode($schema_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $this->output->set_output(json_encode(['success' => false, 'message' => 'Invalid schema JSON']));
        return;
    }

    $this->load->model('admin/Model_test_form_builder'); // Ensure model is loaded
    $html = $this->build_form_html($schema);

    $this->output->set_content_type('application/json');
    $this->output->set_output(json_encode([
        'success' => true,
        'form_view' => $html
    ]));
}

// Add this helper method to generate HTML from schema
private function build_form_html($schema) {
    $html = '<form method="post" action="" class="dynamic-form">';
    foreach ($schema as $row_key => $row_data) {
        if (isset($row_data['row']) && $row_data['row'] === true) {
            $html .= $this->build_row_html($row_data);
        }
    }
    $html .= '</form>';
    return $html;
}

private function build_row_html($row_data) {
    $header = htmlspecialchars($row_data['header'] ?? '');
    $custom_class = $row_data['custom_class'] ?? '';
    $html = '<div class="row ' . $custom_class . '">';
    if (!empty($header)) {
        $html .= '<div class="col-12"><h5>' . $header . '</h5></div>';
    }

    foreach ($row_data['fields'] ?? [] as $field_key => $field) {
        $col_sm = $field['col']['sm'] ?? '12';
        $col_md = $field['col']['md'] ?? '6';
        $col_lg = $field['col']['lg'] ?? '4';
        $label = htmlspecialchars($field['label'] ?? $field_key);
        $required = $field['required'] ? ' required' : '';
        $attr = $this->build_attributes($field['attr'] ?? []);
        $name = $field_key; // Use field_key as name (e.g., "skills[]")

        $field_html = '<div class="col-sm-' . $col_sm . ' col-md-' . $col_md . ' col-lg-' . $col_lg . '">';
        $field_html .= '<label for="' . $name . '">' . $label . ($required ? ' <span class="text-danger">*</span>' : '') . '</label>';

        switch ($field['type']) {
            case 'multiselect':
                $field_html .= $this->build_multiselect_html($name, $field);
                break;
            // Add other types as needed (text, textarea, etc.)
            case 'text':
                $field_html .= '<input type="text" name="' . $name . '" id="' . $name . '" class="form-control"' . $required . $attr . '>';
                break;
            case 'textarea':
                $field_html .= '<textarea name="' . $name . '" id="' . $name . '" class="form-control"' . $required . $attr . '></textarea>';
                break;
            // ... other cases
            default:
                $field_html .= '<input type="' . htmlspecialchars($field['type']) . '" name="' . $name . '" id="' . $name . '" class="form-control"' . $required . $attr . '>';
        }

        $field_html .= '</div>';
        $html .= $field_html;
    }

    $html .= '</div>';
    return $html;
}

private function build_multiselect_html($name, $field) {
    $options = $this->get_multiselect_options($name, $field['options'] ?? []);
    $html = '<select name="' . $name . '[]" id="' . $name . '" multiple class="form-control multiselect-target"' . $this->build_attributes($field['attr'] ?? []) . ' data-placeholder="' . htmlspecialchars($field['label'] ?? 'Select options') . '">';
    
    foreach ($options as $value => $label) {
        $html .= '<option value="' . htmlspecialchars($value) . '">' . htmlspecialchars($label) . '</option>';
    }
    
    $html .= '</select>';
    return $html;
}

private function get_multiselect_options($name, $static_options) {
    // If static options provided in schema, use them
    if (!empty($static_options)) {
        return $static_options;
    }

    // Dynamic: Detect field name and fetch from DB
    $this->load->model('admin/Model_test_form_builder');
    if (strpos($name, 'qualifications') !== false) {
        $qualifications = $this->Model_test_form_builder->db->select('id, name')
            ->from('mod_job_qualifications')
            ->where('removed', 0)
            ->get()
            ->result_array();
        return array_column($qualifications, 'name', 'id');
    } elseif (strpos($name, 'skills') !== false) {
        $skills = $this->Model_test_form_builder->db->select('id, name')
            ->from('mod_job_skills')
            ->where('removed', 0)
            ->get()
            ->result_array();
        return array_column($skills, 'name', 'id');
    }

    return []; // Empty if no match
}

private function build_attributes($attr_array) {
    if (empty($attr_array)) return '';
    $attrs = [];
    foreach ($attr_array as $key => $val) {
        $attrs[] = htmlspecialchars($key) . '="' . htmlspecialchars($val) . '"';
    }
    return ' ' . implode(' ', $attrs);
}


public function get_form_by_schema_id($schema_id = null) 
{
    // Get from POST
    $schema_id = $this->input->post('schema_id') ?: $schema_id;

    if (!$schema_id || !is_numeric($schema_id)) {
        log_message('error', 'Invalid schema_id: ' . $schema_id);
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode(['success' => false, 'message' => 'Invalid schema ID']));
        return;
    }

    // Load schema
    $row = $this->db->where('id', $schema_id)->get('sys_form_schemas')->row();

    if (!$row) {
        log_message('error', 'Schema not found for ID: ' . $schema_id);
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode(['success' => false, 'message' => 'Form schema not found']));
        return;
    }

    $schema = json_decode($row->schema, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        log_message('error', 'JSON decode error: ' . json_last_error_msg());
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode(['success' => false, 'message' => 'Invalid schema format']));
        return;
    }

    try {
        $this->load->library('Form_builder');
        
        $styling = json_decode($row->styling ?? '{}', true);
        $scripts = json_decode($row->scripts ?? '[]', true);
        
        $form = $this->form_builder::make()
            ->set_schema($schema)
            ->set_styling($styling)
            ->set_scripts($scripts)
            ->make_form([]); // Empty data for new form

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode([
            'success' => true,
            'form_view' => $form->form_view,
            'form_schema' => $form->form_schema
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Form build failed: ' . $e->getMessage());
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Render failed: ' . $e->getMessage()
        ]));
    }
}

private function create_or_update_template_section($form_id, $form_data)
{
    // Check if template section already exists for this form
    $existing_section = $this->db->get_where('mod_template_sections', [
        'schema_id' => $form_id
    ])->row();

    // ✅ FIX: Generate unique code to prevent duplicates
    $base_code = strtolower(url_title($form_data['name'] ?? 'unnamed_section'));
    $code = $base_code;
    $counter = 1;
    
    // ✅ FIXED: Check if code already exists using proper query builder
    do {
        // Build query properly
        $this->db->from('mod_template_sections');
        $this->db->where('code', $code);
        
        // Exclude current section if updating
        if ($existing_section) {
            $this->db->where('id !=', $existing_section->id);
        }
        
        $check_query = $this->db->get();
        
        if ($check_query->num_rows() > 0) {
            $code = $base_code . '_' . $counter;
            $counter++;
        } else {
            break;
        }
    } while ($counter < 100); // Safety limit

    $section_data = [
        'name' => $form_data['name'] ?? 'Unnamed Section',
        'code' => $code, // ✅ Now guaranteed unique
        'description' => $form_data['description'] ?? 'Auto-created template section',
        'section_type' => 'content',
        'schema_id' => $form_id,
        'enabled' => 1,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    if ($existing_section) {
        // ✅ UPDATE existing section
        $this->db->where('id', $existing_section->id);
        $this->db->update('mod_template_sections', $section_data);
        log_message('debug', 'Updated existing template section for form ID: ' . $form_id . ' with code: ' . $code);
        return $existing_section->id;
    } else {
        // ✅ CREATE new section only if it doesn't exist
        $section_data['sort_order'] = $this->get_next_sort_order();
        $section_data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert('mod_template_sections', $section_data);
        $section_id = $this->db->insert_id();
        log_message('debug', 'Created new template section for form ID: ' . $form_id . ' -> Section ID: ' . $section_id . ' with code: ' . $code);
        return $section_id;
    }
}
public function cleanup_duplicate_codes()
{
     // ✅ ADD CSRF VALIDATION
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->post($csrf_name);
    
    if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
        echo "<h2>CSRF Validation Failed</h2>";
        echo "<p>Invalid CSRF token. Please refresh and try again.</p>";
        return;
    }
    echo "<h2>Cleaning up duplicate codes in mod_template_sections:</h2>";
    echo "<pre>";
    
    // Find duplicate codes
    $this->db->select('code, COUNT(*) as count, GROUP_CONCAT(id) as section_ids')
             ->from('mod_template_sections')
             ->group_by('code')
             ->having('count > 1');
    $duplicates = $this->db->get()->result();
    
    foreach ($duplicates as $dup) {
        echo "Found {$dup->count} sections with code: {$dup->code}\n";
        echo "Section IDs: {$dup->section_ids}\n";
        
        // Keep the oldest one, update the rest with unique codes
        $section_ids = explode(',', $dup->section_ids);
        $keep_id = min($section_ids); // Keep the oldest (lowest ID)
        
        foreach ($section_ids as $section_id) {
            if ($section_id != $keep_id) {
                $new_code = $dup->code . '_' . $section_id;
                $this->db->where('id', $section_id)
                         ->update('mod_template_sections', ['code' => $new_code]);
                echo "✅ Updated section ID {$section_id} with new code: {$new_code}\n";
            }
        }
        echo "---\n";
    }
    
    if (empty($duplicates)) {
        echo "No duplicate codes found!\n";
    }
    
    echo "Done!";
    echo "</pre>";
}
private function get_next_sort_order()
{
    $this->db->select_max('sort_order');
    $result = $this->db->get('mod_template_sections')->row();
    return ($result->sort_order ?? 0) + 1;
}
public function create_missing_template_sections()
{
        // ✅ ADD CSRF VALIDATION
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->post($csrf_name);
    
    if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
        echo "<h2>CSRF Validation Failed</h2>";
        echo "<p>Invalid CSRF token. Please refresh and try again.</p>";
        return;
    }
    
    // Find forms without template sections - FIXED: removed f.description
    $missing_sections = $this->db->select('f.id, f.name')
                                ->from('sys_form_schemas f')
                                ->join('mod_template_sections ts', 'ts.schema_id = f.id', 'left')
                                ->where('ts.id IS NULL')
                                ->where('f.enabled', 1)
                                ->get()
                                ->result();

    echo "<h2>Creating missing template sections:</h2>";
    echo "<pre>";

    foreach ($missing_sections as $form) {
        $section_data = [
            'name' => $form->name,
            'code' => strtolower(url_title($form->name)),
            'description' => 'Auto-created template section', // Default description
            'section_type' => 'content',
            'schema_id' => $form->id,
            'enabled' => 1,
            'sort_order' => $this->get_next_sort_order(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('mod_template_sections', $section_data);
        $section_id = $this->db->insert_id();

        echo "✅ Created template section for '{$form->name}' (Form ID: {$form->id}) -> Section ID: {$section_id}\n";
    }

    if (empty($missing_sections)) {
        echo "No missing template sections found!\n";
    }

    echo "Done!";
    echo "</pre>";
}

public function update_modify_params($params){
    log_message('debug', '=== UPDATE_MODIFY_PARAMS CALLED ===');
    log_message('debug', 'Original params: ' . print_r($params, true));
    log_message('debug', 'DF data exists: ' . (!empty($this->input->post('df')) ? 'YES' : 'NO'));
    
    if (!empty($this->input->post('df'))) {
        log_message('debug', 'DF data: ' . print_r($this->input->post('df'), true));
    }
    
    // ✅ ADD VALIDATION BEFORE PROCESSING
    if (empty($params['name'])) {
        $params['name'] = 'Untitled Form ' . date('Y-m-d H:i:s');
    }
    
    if (empty($params['form_element_id'])) {
        $params['form_element_id'] = 'form_' . time();
    }
    
    $result = $this->store_df_data($params);
    log_message('debug', 'After store_df_data: ' . print_r($result, true));
    
    return $result;
}

/**
     * Override disable method to cascade to related sections and templates
     */
    public function disable($id) 
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->post($csrf_name);
    
    if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
        if (is_ajax()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        } else {
            show_error('Invalid CSRF token', 400);
            return;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    show_error('Method not allowed', 405);
    return;
}
        log_message('debug', '=== CASCADING DISABLE START FOR FORM: ' . $id . ' ===');
        
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

        // Extra code to execute before disabling
        if (!$this->disable_extra_before($row)) {
            return FALSE;
        }

        $messageParams = array('name' => $row->name);

        // Start transaction for atomic operations
        $this->db->trans_start();
        
        // 1. Disable the form schema
        $result = $this->{$this->model}->disable($id);
        
        if ($result) {
            // 2. Cascade disable to related template sections
            $this->cascade_disable_to_sections($id, $row->name);
            
            // 3. Cascade disable to related templates
            $this->cascade_disable_to_templates($id, $row->name);
            
            Logger::log('Disabled form and related items: ' . $row->name, array('id' => $id));
        }
        
        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE && $result) {
            if (!is_ajax()) {
                flash_notification(langs($this->pageName . '_disable_success_description', $messageParams), 'success');
            }
            $this->disable_extra_success($row);
        } else {
            Anomalies::log('Failed to disable form and related items: ' . $row->name, $this->db->last_query());
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
     * Override enable method to cascade to related sections and templates
     */
    public function enable($id) 
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->post($csrf_name);
    
    if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
        if (is_ajax()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        } else {
            show_error('Invalid CSRF token', 400);
            return;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    show_error('Method not allowed', 405);
    return;
}
        log_message('debug', '=== CASCADING ENABLE START FOR FORM: ' . $id . ' ===');
        
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

        // Extra code to execute before enabling
        if (!$this->enable_extra_before($row)) {
            return FALSE;
        }

        $messageParams = array('name' => $row->name);

        // Start transaction for atomic operations
        $this->db->trans_start();
        
        // 1. Enable the form schema
        $result = $this->{$this->model}->enable($id);
        
        if ($result) {
            // 2. Cascade enable to related template sections
            $this->cascade_enable_to_sections($id, $row->name);
            
            // 3. Cascade enable to related templates
            $this->cascade_enable_to_templates($id, $row->name);
            
            Logger::log('Enabled form and related items: ' . $row->name, array('id' => $id));
        }
        
        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE && $result) {
            if (!is_ajax()) {
                flash_notification(langs($this->pageName . '_enable_success_description', $messageParams), 'success');
            }
            $this->enable_extra_success($row);
        } else {
            Anomalies::log('Failed to enable form and related items: ' . $row->name, $this->db->last_query());
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
     * Cascade disable to related template sections
     */
    private function cascade_disable_to_sections($schema_id, $schema_name) 
    {
        log_message('debug', 'Cascading disable to sections for schema: ' . $schema_id);
        
        // Find all template sections that use this schema
        $sections = $this->db->select('id, name')
                            ->from('mod_template_sections')
                            ->where('schema_id', $schema_id)
                            ->where('enabled', 1)
                            ->where('removed', 0)
                            ->get()
                            ->result();
        
        $disabled_count = 0;
        foreach ($sections as $section) {
            $this->db->where('id', $section->id)
                    ->update('mod_template_sections', ['enabled' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
            
            if ($this->db->affected_rows() > 0) {
                $disabled_count++;
                Logger::log('Cascaded disable to template section: ' . $section->name, [
                    'schema_id' => $schema_id,
                    'schema_name' => $schema_name,
                    'section_id' => $section->id
                ]);
            }
        }
        
        log_message('debug', 'Disabled ' . $disabled_count . ' template sections for schema: ' . $schema_id);
        return $disabled_count;
    }

/**
     * Cascade disable to related templates
     */
    private function cascade_disable_to_templates($schema_id, $schema_name) 
    {
        log_message('debug', 'Cascading disable to templates for schema: ' . $schema_id);
        
        // Find all templates that use this schema (mod_layouts table)
        $templates = $this->db->select('id, name')
                             ->from('mod_layouts')
                             ->where('schema_id', $schema_id)
                             ->where('enabled', 1)
                             ->where('removed', 0)
                             ->get()
                             ->result();
        
        $disabled_count = 0;
        foreach ($templates as $template) {
            $this->db->where('id', $template->id)
                    ->update('mod_layouts', ['enabled' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
            
            if ($this->db->affected_rows() > 0) {
                $disabled_count++;
                Logger::log('Cascaded disable to template: ' . $template->name, [
                    'schema_id' => $schema_id,
                    'schema_name' => $schema_name,
                    'template_id' => $template->id
                ]);
            }
        }
        
        log_message('debug', 'Disabled ' . $disabled_count . ' templates for schema: ' . $schema_id);
        return $disabled_count;
    }

/**
     * Cascade enable to related template sections
     */
    private function cascade_enable_to_sections($schema_id, $schema_name) 
    {
        log_message('debug', 'Cascading enable to sections for schema: ' . $schema_id);
        
        // Find all template sections that use this schema
        $sections = $this->db->select('id, name')
                            ->from('mod_template_sections')
                            ->where('schema_id', $schema_id)
                            ->where('enabled', 0)
                            ->where('removed', 0)
                            ->get()
                            ->result();
        
        $enabled_count = 0;
        foreach ($sections as $section) {
            $this->db->where('id', $section->id)
                    ->update('mod_template_sections', ['enabled' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
            
            if ($this->db->affected_rows() > 0) {
                $enabled_count++;
                Logger::log('Cascaded enable to template section: ' . $section->name, [
                    'schema_id' => $schema_id,
                    'schema_name' => $schema_name,
                    'section_id' => $section->id
                ]);
            }
        }
        
        log_message('debug', 'Enabled ' . $enabled_count . ' template sections for schema: ' . $schema_id);
        return $enabled_count;
    }


/**
     * Cascade enable to related templates
     */
    private function cascade_enable_to_templates($schema_id, $schema_name) 
    {
        log_message('debug', 'Cascading enable to templates for schema: ' . $schema_id);
        
        // Find all templates that use this schema (mod_layouts table)
        $templates = $this->db->select('id, name')
                             ->from('mod_layouts')
                             ->where('schema_id', $schema_id)
                             ->where('enabled', 0)
                             ->where('removed', 0)
                             ->get()
                             ->result();
        
        $enabled_count = 0;
        foreach ($templates as $template) {
            $this->db->where('id', $template->id)
                    ->update('mod_layouts', ['enabled' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
            
            if ($this->db->affected_rows() > 0) {
                $enabled_count++;
                Logger::log('Cascaded enable to template: ' . $template->name, [
                    'schema_id' => $schema_id,
                    'schema_name' => $schema_name,
                    'template_id' => $template->id
                ]);
            }
        }
        
        log_message('debug', 'Enabled ' . $enabled_count . ' templates for schema: ' . $schema_id);
        return $enabled_count;
    }
/**
     * Extra before disable hook - can be overridden
     */
    public function disable_extra_before($row) 
    {
        log_message('debug', 'Form disable_extra_before called for: ' . $row->name);
        return TRUE;
    }

    /**
     * Extra after disable success hook - can be overridden
     */
    public function disable_extra_success($row) 
    {
        log_message('debug', 'Form disable_extra_success called for: ' . $row->name);
        return TRUE;
    }

    /**
     * Extra before enable hook - can be overridden
     */
    public function enable_extra_before($row) 
    {
        log_message('debug', 'Form enable_extra_before called for: ' . $row->name);
        return TRUE;
    }

    /**
     * Extra after enable success hook - can be overridden
     */
    public function enable_extra_success($row) 
    {
        log_message('debug', 'Form enable_extra_success called for: ' . $row->name);
        return TRUE;
    }
/**
 * Override remove method to cascade delete to related sections and templates
 */
public function remove($id) 
{
       if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            show_error('Invalid CSRF token', 400);
            return;
        }
    } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        show_error('Method not allowed', 405);
        return;
    }
    
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
    log_message('debug', '=== CASCADING DELETE START FOR FORM: ' . $id . ' ===');
    
    $row = $this->{$this->model}->get_by_id($id);
    
    if (!$row) {
        flash_notification(lang('access_denied_description'), 'warning');
        redir($this->pageName);
        return FALSE;
    }

    // Extra code to execute before removing an entry
    if (!$this->remove_extra_before($row)) {
        redir($this->pageName);
        return FALSE;
    }

    $messageParams = array('name' => $row->name);

    // Start transaction for atomic operations
    $this->db->trans_start();
    
    // 1. Remove the form schema (soft delete)
    $result = $this->{$this->model}->remove($id);
    
    if ($result) {
        // 2. Cascade delete to related template sections
        $this->cascade_delete_to_sections($id, $row->name);
        
        // 3. Cascade delete to related templates
        $this->cascade_delete_to_templates($id, $row->name);
        
        Logger::log('Removed form and related items: ' . $row->name, array('id' => $id));
    }
    
    $this->db->trans_complete();

    if ($this->db->trans_status() !== FALSE && $result) {
        flash_notification(langs($this->pageName . '_remove_success_description', $messageParams), 'success');
        $this->remove_extra_success($row);
    } else {
        Anomalies::log('Failed to remove form and related items: ' . $row->name, $this->db->last_query());
        flash_notification(langs($this->pageName . '_remove_failed_description', $messageParams), 'error');
    }

    redir($this->pageName);
}

/**
 * Cascade delete to related template sections
 */
private function cascade_delete_to_sections($schema_id, $schema_name) 
{
    log_message('debug', 'Cascading delete to sections for schema: ' . $schema_id);
    
    // Find all template sections that use this schema
    $sections = $this->db->select('id, name')
                        ->from('mod_template_sections')
                        ->where('schema_id', $schema_id)
                        ->where('removed', 0)
                        ->get()
                        ->result();
    
    $deleted_count = 0;
    foreach ($sections as $section) {
        $this->db->where('id', $section->id)
                ->update('mod_template_sections', [
                    'removed' => 1, 
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        
        if ($this->db->affected_rows() > 0) {
            $deleted_count++;
            Logger::log('Cascaded delete to template section: ' . $section->name, [
                'schema_id' => $schema_id,
                'schema_name' => $schema_name,
                'section_id' => $section->id
            ]);
        }
    }
    
    log_message('debug', 'Deleted ' . $deleted_count . ' template sections for schema: ' . $schema_id);
    return $deleted_count;
}

/**
 * Cascade delete to related templates
 */
private function cascade_delete_to_templates($schema_id, $schema_name) 
{
    log_message('debug', 'Cascading delete to templates for schema: ' . $schema_id);
    
    // Find all templates that use this schema (mod_layouts table)
    $templates = $this->db->select('id, name')
                         ->from('mod_layouts')
                         ->where('schema_id', $schema_id)
                         ->where('removed', 0)
                         ->get()
                         ->result();
    
    $deleted_count = 0;
    foreach ($templates as $template) {
        $this->db->where('id', $template->id)
                ->update('mod_layouts', [
                    'removed' => 1, 
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        
        if ($this->db->affected_rows() > 0) {
            $deleted_count++;
            Logger::log('Cascaded delete to template: ' . $template->name, [
                'schema_id' => $schema_id,
                'schema_name' => $schema_name,
                'template_id' => $template->id
            ]);
        }
    }
    
    log_message('debug', 'Deleted ' . $deleted_count . ' templates for schema: ' . $schema_id);
    return $deleted_count;
}

/**
 * Extra before remove hook - can be overridden
 */
public function remove_extra_before($row) 
{
    log_message('debug', 'Form remove_extra_before called for: ' . $row->name);
    return TRUE;
}

/**
 * Extra after remove success hook - can be overridden
 */
public function remove_extra_success($row) 
{
    log_message('debug', 'Form remove_extra_success called for: ' . $row->name);
    return TRUE;
}


}