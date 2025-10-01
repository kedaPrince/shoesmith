<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Form_builder - Form Builder Library for CodeIgniter 3
 */
class Form_builder {
    protected $CI;
    public $form_data = array();
    public $form_schema = array();
    public $form_view = array();
    public $form_fields = array();
    public $form_scripts = array();
    public $form_styling = array();

    public function __construct() {
        $this->CI =& get_instance();
        
        $this->CI->lang->load('general_lang', 'english');
    }

    /**
     * Initialize the form builder and make
     */
    public static function make()
    {
        return new self();
    }

    /**
     * Get the schema for a specific table and column
     *
     * @param string $table The name of the table
     * @param string $get The column to fetch
     * @param mixed $where The value to filter by, can be an array or a single value
     * @return self
     * @throws Exception If the table or column does not exist
     */
    public function get_schema_from($table, $get, $where)
    {
        $CI =& get_instance();
        $db = $CI->db;
        // Check if table and column exist
        if (!$db->table_exists($table)) {
            throw new Exception("Table \"{$table}\" does not exist.");
        }

        if (!$db->field_exists($get, $table)) {
            throw new Exception("Column \"{$get}\" does not exist in table \"{$table}\".");
        }

        // check if $where is array or string/integer
        if(is_array($where)){
            $where_col = $where[0];
            $where_val = $where[1];
        } else {
            $where_col = $get;
            $where_val = $where;
        }

        // Fetch the form data based on the column and value
        $form = $db->select($get)
            ->from($table)
            ->where($where_col, $where_val)
            ->get()
            ->row();

        $this->set_schema($form);

        return $this;
    }

    /**
     * Get the schema for a specific form from the sys_form_schemas table
     *
     * @param int $form_id The ID of the form
     * @return self
     * @throws Exception If the form ID is invalid
     */
    public function get_schema($form_id)
    {
        if(!$form_id){
            throw new Exception("Form ID is required to fetch the schema.");
        }

        $db = $this->CI->db;

        // Check if table exists
        if (!$db->table_exists('sys_form_schemas')) {
            throw new Exception("Table \"sys_form_schemas\" does not exist.");
        }

        // Fetch the form schema based on the form ID
        $form = $db->select('*')
            ->from('sys_form_schemas')
            ->where('id', $form_id)
            ->get()
            ->row();

        if (!$form) {
            throw new Exception("Form with ID \"{$form_id}\" does not exist.");
        }

        $schema = !empty($form->schema) ? $this->convert_string_to_array($form->schema) : [];
        $styling = !empty($form->styling) ? $this->convert_string_to_array($form->styling) : [];
        $scripts = !empty($form->scripts) ? $this->convert_string_to_array($form->scripts) : [];
        
        $this->set_schema($schema);
        $this->set_styling($styling);
        $this->set_scripts($scripts);
        $this->set_form_fields();
        return $this;
    }

    /**
     * Update the schema for a specific table and column
     *
     * @param string $table The name of the table
     * @param string $get The column to update
     * @param mixed $where The value to filter by, can be an array or a single value
     * @param array $schema The new schema to set
     * @return bool True on success, false on failure
     * @throws Exception If the table or column does not exist
     */
    public function update_schema_to($table, $get, $where, $schema)
    {
        $CI =& get_instance();
        $db = $CI->db;

        // Check if table and column exist
        if (!$db->table_exists($table)) {
            throw new Exception("Table \"{$table}\" does not exist.");
        }

        if (!$db->field_exists($get, $table)) {
            throw new Exception("Column \"{$get}\" does not exist in table \"{$table}\".");
        }

        // check if $where is array or string/integer
        if(is_array($where)){
            $where_col = $where[0];
            $where_val = $where[1];
        } else {
            $where_col = $get;
            $where_val = $where;
        }

        // Update the form schema
        if($db->update($table, array($get => json_encode($schema)), array($where_col => $where_val))) {
            $this->set_schema($schema);
        }else{
            throw new Exception("Something went wrong with updating the schema.");
        }

        return $this;
    }

    /**
     * Set the form schema
     *
     * @param array $form_schema The form schema to set
     * @return self
     * @throws Exception If the form schema is not an array
     */
    public function set_schema($form_schema)
    {
        if (is_array($form_schema)) {
            $this->form_schema = $form_schema;
        } else {
            $form_schema = json_decode($form_schema, true);
            // Check if the decoded schema is an array
            if (is_array($form_schema)) {
                $this->form_schema = $form_schema;
            } else {
                throw new Exception("Form schema must be an array.");
            }
        }

        //Check if $form has scripts
        if(!empty($form_schema['scripts'])){
            $this->set_scripts($form_schema['scripts']);
        }

        //Check if $form has styling
        if(!empty($form_schema['styling'])){
            $this->set_styling($form_schema['styling']);
        }

        return $this;
    }

    /**
     * Make the form based on the schema and data
     *
     * @param mixed $data The data to populate the form with, can be an array or null
     * @return self
     */
    public function make_form($data = null)
    {
        //check if $this->form is set and not empty
        if (empty($this->form_schema)) {
            throw new Exception("Form schema is not set or is empty.");
        }

        // If data is not an array, convert it to an array
        if (!is_array($data)) {
            if (is_null($data)) {
                $this->form_data = [];
            } else {
                $this->form_data = (array) $data; // Convert to array if it's not already
            }
        }

        $fields = $this->setup_form_fields($this->form_data);
        $this->form_view = $this->form_view($fields);

        return $this;
    }

    /**
     * Set the scripts for the form
     *
     * @param array $scripts The scripts to set
     * @return self
     * @throws Exception If the scripts are not an array
     */
    public function set_scripts($scripts)
    {
        if (is_array($scripts)) {
            $this->form_scripts = $scripts;
        } else {
            // If scripts is a string, try to decode it as JSON
            $scripts = json_decode($scripts, true);
            // Check if the decoded scripts is an array
            if (is_array($scripts)) {
                $this->form_scripts = $scripts;
            } else {
                $this->form_scripts = [];
                // throw new Exception("Script must be an array.");
            }
        }

        return $this;
    }

    /**
     * Set the styling for the form
     * 
     * @param array $styling the array of styling
     * @return self
     * @throws Exception if the styling is not an array
     */
    public function set_styling($styling)
    {
        // This function can be used to set custom styling for the form
        if (is_array($styling)) {
            $this->form_styling = $styling;
        } else {
            // If styling is a string, try to decode it as JSON
            $styling = json_decode($styling, true);
            // Check if the decoded styling is an array
            if (is_array($styling)) {
                $this->form_styling = $styling;
            } else {
                $this->form_styling = [];
                // throw new Exception("Styling must be an array.");
            }
        }

        return $this;
    }

    private function set_form_fields()
    {
        $schema = $this->form_schema;

        if (empty($schema)) {
            throw new Exception("Form schema is not set or is empty.");
        }
        $this->form_fields = [];
        foreach ($schema as $segment) {
            if (!empty($segment['fields'])) {
                foreach ($segment['fields'] as $field_name => $field_data) {
                    // Add field to form_fields array
                    $this->form_fields[$field_name] = $field_data;
                }
            }
        }

    }

    /**
     * Set the form fields based on the schema
     *
     * @param array $form_schema The form schema to set
     * @return self
     */
    public function from_db($table){
        // This function can be used to create a form from a database table
        $CI =& get_instance();
        $db = $CI->db;
        // Check if table exists
        if (!$db->table_exists($table)) {
            throw new Exception("Table \"{$table}\" does not exist.");
        }

        // Fetch the table structure (column names and data types) 
        $table_structure = $db->query("SHOW COLUMNS FROM {$table}")->result_array();
        if (empty($table_structure)) {
            throw new Exception("No columns found for table \"{$table}\".");
        }

        $form_fields = [];
        // Map the columns to form fields
        foreach ($table_structure as $column) {

            $ignore_cols = ['id', 'created_at', 'updated_at', 'deleted_at', 'removed', 'enabled'];
            if (in_array($column['Field'], $ignore_cols)) {
                continue; // Skip ignored columns
            }

            $type = $column['Type']; // Default type
            $type = $this->db_col_type($type); // Call the function to get the column type

            $form_fields[$column['Field']] = [
                'label' => $this->db_label_regex($column['Field']), // Set the label for the field (must be added to the language file)
                'type' => $type, // set the type according to the db 'Data Type'
                'col' => [ // Set the column width for this field according to different screen sizes
                    'sm' => 12,
                    'md' => 6,
                    'lg' => 6
                ],
                'required' => ($column['Null'] === 'NO') ? true : false, // Set to true if the field is required
                'options' => $this->db_enum_options($column['Type']) // Get enum options if applicable
            ];
        }

        $schema = [
            'form' => [
                'id' => $table,
            ],
            [
                'row' => true, // set this variable to true if you want to create a new row (will add the correct html layout)
                'header' => 'label_basic_fields', // Set the header for this segment (Will only be displayed if row is true)
                'fields' => $form_fields
            ]
        ];

        $this->set_schema($schema); // Set the schema for the form

        return $this;

        return [
            'form_fields' => $form_fields,
            'structure' => $table_structure,
            'form_schema' => $this->form_schema,
        ];
    }

    /** Private Functions **/
    private function db_col_type($type)
    {
        // This function converts the database column type to a form field type

        $type = strtolower($type);

        // Extract base type (e.g., varchar(255) => varchar)
        if (preg_match('/^([a-z]+)[(]?/i', $type, $matches)) {
            $base_type = $matches[1];
        } else {
            $base_type = $type;
        }

        $text_types = ['text', 'varchar', 'char', 'tinytext'];
        $textarea_types = ['mediumtext', 'longtext'];
        $number_types = ['int', 'bigint', 'smallint', 'tinyint', 'decimal', 'float', 'double'];
        $date_types = ['date'];
        $datetime_types = ['datetime', 'timestamp'];
        $time_types = ['time'];
        $radio_types = ['enum'];

        if (in_array($base_type, $text_types)) {
            return 'text';
        } elseif (in_array($base_type, $textarea_types)) {
            return 'textarea';
        } elseif (in_array($base_type, $number_types)) {
            return 'number';
        } elseif (in_array($base_type, $date_types)) {
            return 'date';
        } elseif (in_array($base_type, $datetime_types)) {
            return 'date_time';
        } elseif (in_array($base_type, $time_types)) {
            return 'time';
        } elseif (in_array($base_type, $radio_types)) {
            return 'radio';
        }

        // Default to text
        return 'text';
    }

    private function db_label_regex($label)
    {
        // This function converts a string to a language file label format
        // converts string to snake case and adds 'label_' prefix (e.g. 'First Name' or 'first-name' to 'label_first_name')
        $label = strtolower($label);
        $label = preg_replace('/[^a-z0-9_]+/', '_', $label);
        return 'label_' . $label;
    }

    private function db_enum_options($type)
    {
        //check if type is enum
        if (empty($type) || strpos($type, 'enum') === false) {
            return [];
        }
        // This function extracts enum options from a MySQL enum type string
        if (preg_match('/^enum\((.*)\)$/i', $type, $matches)) {
            $options = explode(',', $matches[1]);
            // Remove quotes from each option and format them
            $options = array_map(function($option) {
                $value = trim($option, "'\"");
                // Replace underscores and hyphens with spaces
                $label = str_replace(['_', '-'], ' ', $value);
                // Only first letter capitalized, rest lowercase
                $label = ucfirst(strtolower($label));
                return [$value => $label];
            }, $options);
            // Flatten the array of arrays into a single associative array
            $options = array_merge(...$options);
            return $options;
        }
        return [];
    }

    private function form_view($fields)
    {
        // Set styling if exists
        $layout = $this->make_styling(); // Add styling to the form view

        // Set form info if exists
        $form_info = $this->make_form_info();
        $action = '';
        $attr = [];
        $form_open = form_open();


        // Check if form info is not empty
        if(!empty($form_info['form_info'])){
            $form_open = form_open('', $form_info['form_info']);
        }
        
        // Check if form info has action and if it is not empty
        if(isset($form_info['form_info']['action']) && !empty($form_info['form_info']['action'])){
            $action = $form_info['form_info']['action'];
            $form_open = form_open($action, $form_info['form_info']);
            $form_id =  $form_info['form_info']['id'] ?? '';
            $this->make_custom_save($form_id, $action);
            $attr['onclick'] = "form_custom_save(this, '$action')"; // use form action as custom url for ajax post
        }
       
        // make form layout
        $layout .= $form_open;
        $layout .= $fields;
        $layout .= '<div class="mt-2 form-errors" data-form="' . $form_info['form_info']['id'] . '"></div>';
        $layout .= '<div class="btn-container" style="clear: left;">';
        $layout .= save_button('Save and Close', '',  $attr);
        $layout .= cancel_button('Close', 'left');
        $layout .= '</div>';
        $layout .= form_close();

        // Add form scripts if exists
        $layout .= $this->make_scripts();

        return $layout;
    }

    private function make_styling()
    {
        $form_id = !empty($this->form_schema['form']['id']) ? '#' . $this->form_schema['form']['id'] : '';
        $html = '<style>';
        if(is_array($this->form_styling) && !empty($this->form_styling)){
            $style = '';
            foreach($this->form_styling as $selector => $styles){
                // Check if selector has commas
                if(strpos($selector, ',') !== false){
                    $selectors = explode(',', $selector);
                    $temp_selectors = '';

                    foreach($selectors as $sel){
                        $temp_selectors .= $form_id . ' ' . trim($sel) . ', ';
                    }
                    $temp_selectors = rtrim($temp_selectors, ', ');

                    $style .= $temp_selectors . ' {';
                }else{
                    $style .= $form_id . ' ' . $selector . ' {';
                }

                $style .= "\n";
                $style .=  "" . $styles;
                $style .= "\n";
                $style .= '}';
                $style .= "\n";

            }
            // Add style to the form view
            $html .= "" . $style . "\n";
        }
        $html .= '</style>';

        return $html;
    }

    private function make_scripts()
    {
        $layout = '';
        // Check if form schema has scripts
        if(!empty($this->form_scripts)){
            $layout .= '<script>';
            foreach($this->form_scripts as $script){
                $layout .= $script;
                $layout .= "\n";
            }
            $layout .= '</script>';
        }

        return $layout;

    }

    private function make_form_info()
    {
         // Set form info if exists
        $form_info = !empty($this->form_schema['form']) ? $this->form_schema['form'] : [];
        $action = '';
        //remove action from form_info if it exists
        if (!empty($form_info['form']['action'])) {
            $action = $form_info['form']['action'];
            unset($form_info['form']['action']);
        }

        return [
            'form_info' => $form_info,
            'action' => $action
        ];
    }

    private function setup_form_fields($data = null)
    {
        $form_schema = $this->form_schema;
        $fields_layout = '';
        $has_id_field = false;
        foreach($form_schema as $segment){

            if(!empty($segment['row']) && $segment['row'] === true){
                $header = !empty($segment['header']) ? $segment['header'] : '';
                $class = !empty($segment['custom_class']) ? ' ' . $segment['custom_class'] : '';
                
                // make row layout
                $fields_layout .= $this->make_row($header, $class);
            }
            
            if(!empty($segment['fields'])){

                foreach($segment['fields'] as $field_name => $field_data){
                    // make col layout
                    if(!empty($field_data['col']) && $field_data['col'] > 0){
                        $fields_layout .= $this->make_col($field_data['col']);
                    }

                    // add fields
                    if($field_name == 'id' && !$has_id_field){
                        $has_id_field = true;
                    }
                    
                    $fields_layout .= $this->make_field($field_name, $field_data, $data);
                    // add field to form_fields array
                    $this->form_fields[$field_name] = $field_data;
                    
                    // close col layout
                    if(!empty($field_data['col']) && $field_data['col'] > 0){
                        $fields_layout .= '</div>'; // close col
                    }
                }
            }

            // close row layout
            if(!empty($segment['row']) && $segment['row'] === true){
                $fields_layout .= '</div>'; // close row
            }
        }

        if(!$has_id_field){
            $id_field = $this->make_hidden_id_field($data);
            $fields_layout = $id_field . $fields_layout; // prepend hidden id field to the fields layout
        }

        return $fields_layout;
    }

    private function make_hidden_id_field($data = null)
    {
        $data = !empty($data['id']) ? $data['id'] : '';
        // Check if $data is an object and if so convert it to an array
        if (is_object($data)) {
            $data = (array) $data; // Convert to array if it's not already
        }
        
        //check if $data is an array and if have multiple values
        if (is_array($data) && count($data) > 1) {
            // If $data is an array with multiple values, set it to the first value
            $data = reset($data); // Get the first value of the array
        }

        // If $data is empty, set it to an empty string
        if (empty($data)) {
            $data = ''; // set empty
        }

        $id_field = '<div class="row hidden">';
        $id_field .= field_hidden('id', $data);
        $id_field .= '</div>';
        return $id_field;
    }

    private function make_row($header = '', $class = '')
    {
        if($class && $class !== '') {
            $class = ' ' . $class;
        }

        $row = '<div class="row' . $class . '">';
        if(!empty($header)){
            $row .= '<div class="col-12 form-group-header-wrapper"><span class="form-group-header">' . $header . '</span></div>';
        }
        return $row;

    }

    private function make_col($cols)
    {
        $col_class = '';
        if(is_array($cols)){
            foreach($cols as $key => $value){
                $col_class .= 'col-' . $key . '-' . $value . ' ';
            }
        }else{
            $col_class .= 'col-lg-' . $cols . ' ';
        }

        $col = '<div class="' . trim($col_class) . ' form-col">';
        return $col;

    }

    private function make_field($field_name, $field_data, $data)
    {
        $required = !empty($field_data['required']) ? ($field_data['required'] ? 'required' : '') : '';
        $type = !empty($field_data['type']) ? $field_data['type'] : 'text';
        $field = !empty($field_data['label']) ? $field_name . '|' . $field_data['label'] : $field_name;
        $extra = !empty($field_data['attr']) ? $this->convert_string_to_array($field_data['attr']) : [];

        if(isset($this->CI->lang)){
            if(!empty($field_data['label'])){
                $this->CI->lang->language['label_' . $field_name] = $field_data['label'];
            }else{
                // Convert field_name to a human-readable label and add 'label_' prefix
                $label = str_replace(['_', '-'], ' ', $field_name);
                $label = ucwords($label);
                $this->CI->lang->language['label_' . $field_name] = $label;
            }
            $field_data['label'] = 'label_' . $field_name;
            $field = $field_name . '|' . $field_data['label'];
        }

        $data = !empty($data[$field_name]) ? $data[$field_name] : '';
        
        // Check if $data is an object and if so convert it to an array
        if (is_object($data)) {
            $data = (array) $data; // Convert to array if it's not already
        }
        
        //check if $data is an array and if have multiple values
        if (is_array($data) && count($data) > 1) {
            // If $data is an array with multiple values, set it to the first value
            $data = reset($data); // Get the first value of the array
        }

        // If $data is empty, set it to an empty string
        if (empty($data)) {
            $data = ''; // set empty
        }

        switch ($type) {
            case 'text':
                $field = field_input($field, $data, $required, $extra, 'text');
                break;
            
            case 'email':
                $field = field_input($field, $data, $required, $extra, 'email');
                break;

            case 'number':
                $field = field_input($field, $data, $required, $extra, 'number');
                break;

            case 'time':
                $field = field_time($field, $data, $required);
                break;

            case 'password':
                $field = field_password($field, $required, $extra);
                break;

            case 'textarea':
                $field = field_textarea($field, $data, $required);
                break;
  
            case 'wysiwyg_simple':
            case 'ck_editor_simple':
            case 'ckeditor_simple':
                $ckOptions = !empty($field_data['ckeditor_options']) ? $field_data['ckeditor_options'] : [];
                $ckOptions = $this->convert_string_to_array($ckOptions);
                $runScript = !empty($field_data['script']) ? ($field_data['script'] ?? false) : true;
                $field = field_ckeditor_simple($field, $data, $required, $extra, $ckOptions, $runScript);
                break;
            
            case 'wysiwyg':
            case 'ck_editor':
            case 'ckeditor':
                $ckOptions = !empty($field_data['ckeditor_options']) ? $field_data['ckeditor_options'] : [];
                $ckOptions = $this->convert_string_to_array($ckOptions);
                $runScript = !empty($field_data['script']) ? ($field_data['script'] ?? false) : true;
                $field = field_ckeditor($field, $data, $required, $extra, $ckOptions, $runScript);
                break;

            case 'select':
            case 'dropdown':
                $options = !empty($field_data['options']) ? $field_data['options'] : [];
                $options = $this->convert_string_to_array($options);

                $noValueLabel = !empty($field_data['no_value_label']) ? $field_data['no_value_label'] : '';
                $script = !empty($field_data['script']) ? $field_data['script'] : '';
                $field = field_dropdown($field, $options, $data, $required, $extra, $noValueLabel);
                break;
            
            case 'multiselect':
            case 'multi_select':
                $options = !empty($field_data['options']) ? $field_data['options'] : [];
                $options = $this->convert_string_to_array($options);

                //check if $options is a single level array or array of arrays
                if (!empty($options) && is_array($options)) {
                    $isAssoc = function(array $arr) {
                        return ([] === $arr) ? false : array_keys($arr) !== range(0, count($arr) - 1);
                    };
                    // If options is associative, convert to correct format
                    if ($isAssoc($options)) {
                        $arr = [];
                        foreach($options as $key => $val){
                            $arr[] = ['id' => $key, 'name' => $val];
                        }
                        $options = $arr;
                    }
                }

                $field = field_multi_select($field, $options, $data, $required, $extra);
                break;

            case 'radio':
                $radioValues = !empty($field_data['radio_values']) ? $field_data['radio_values'] : (!empty($field_data['options']) ? $field_data['options'] : [1 => 'Yes', 0 => 'No']);
                $radioValues = $this->convert_string_to_array($radioValues);

                $field = field_radio($field, $data, $radioValues, $required, $extra);
                break;

            case 'checkbox':
                $checkboxLabel = !empty($field_data['checkbox_label']) ? $field_data['checkbox_label'] : '';
                $checkboxValue = !empty($field_data['checkbox_value']) ? $field_data['checkbox_value'] : (!empty($field_data['value']) ? $field_data['value'] : '1');
                $field = field_checkbox($field, $data, $required, $checkboxLabel, $checkboxValue);
                break;

            case 'hidden':
                $field = field_hidden($field, $data, $required, $extra);
                break;

            case 'date':
                $dateFormat = !empty($field_data['date_format']) ? $field_data['date_format'] : 'Y-m-d';
                $field = field_date($field, $data, $required, $dateFormat, $extra);
                break;

            case 'datetime':
            case 'date_time':
                $dateFormat = !empty($field_data['date_format']) ? $field_data['date_format'] : 'Y-m-d H:i:s';
                $options = !empty($field_data['options']) ? $field_data['options'] : [];
                $field = field_date_time($field, $data, $required, $dateFormat, $options, $extra);
                break;
            
            case 'multifile':
            case 'multifileupload':
            case 'multifile_upload':
            case 'multi_file':
            case 'multi_file_upload':
                $script = !empty($field_data['script']) ? $field_data['script'] : true;
                $field = field_multi_file($field, $data, $required, $script);
                break;

            case 'file':
            case 'file_upload':
                $script = !empty($field_data['script']) ? $field_data['script'] : true;
                $field = field_file($field, $data, $required, $script);
                break;

            case 'image':
            case 'image_upload':
                $script = !empty($field_data['script']) ? $field_data['script'] : true;
                $field = field_image($field, $data, $required, $script);
                break;
            
            case 'multiimage':
            case 'multiimageupload':
            case 'multiimage_upload':
            case 'multi_image':
            case 'multi_image_upload':
                $script = !empty($field_data['script']) ? $field_data['script'] : true;
                $field = field_multi_image($field, $data, $required, $script);
                break;
            
            case 'button':
            case 'btn':
                $label = !empty($field_data['label']) ? $field_data['label'] : 'Button';
                $class = !empty($field_data['class']) ? $field_data['class'] : '';
                $attr = !empty($field_data['attr']) ? $field_data['attr'] : [];
                $attr['name'] = $attr['id'] = !empty($field_name) ? $field_name : 'custom_button';
                $field = $this->make_custom_button($field, $class, $attr);
                break;

            default:
                // Default to text input if type is not recognized
                $field = field_input($field, $data, $required, $extra, 'text');
        }

        return $field;
    }

    private function convert_string_to_array($data) {
        if (is_array($data)) {
            return $data; // Already an array, return as-is
        }

        if (!is_string($data)) {
            return []; // Not a string or array, return empty array
        }

        // Step 1: Try decoding as JSON
        $json = json_decode($data, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json; // Valid JSON array
        }

        // Step 2: Handle PHP array string (e.g., '["rel" => "restore", "id" => "something",]')
        // Remove whitespace and normalize line breaks
        $data = trim(preg_replace('/\s+/', ' ', $data));
        
        // Remove outer quotes if present (from JSON encoding)
        $data = trim($data, '"');
        
        // Replace PHP => with JSON :
        $data = str_replace('=>', ':', $data);
        
        // Remove trailing comma before closing bracket
        $data = preg_replace('/,\s*]/', ']', $data);
        
        // Wrap the entire content (after opening bracket) in curly braces to form a JSON object
        if (preg_match('/^\[\s*["\']/', $data)) {
            $data = preg_replace('/^\[\s*([^\]]*)\s*]/', '[{$1}]', $data);
        }

        
        // Try decoding as JSON again
        $result = json_decode($data, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($result)) {
            return $result[0];
        }
        
        $result = $this->convert_json($data);
        if (is_array($result)) {
            return $result; // Successfully converted to array
        }

        // Fallback: wrap string in array if parsing fails
        return ['value' => $data];
    }

    private function convert_json($data)
    {
        // If $data is a string, try to convert it to an array
        if (is_string($data)) {
            // Try JSON decode first
            $json = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            $data = $json;
            } else {
            // Try to evaluate PHP array string (e.g. "['a'=>'b']" or "array('a'=>'b')")
            $eval = null;
            $code = '$eval = ' . $data . ';';
            try {
                // Suppress errors and use eval in a limited scope
                eval($code);
                if (is_array($eval)) {
                $data = $eval;
                }
            } catch (\Throwable $e) {
                // Ignore eval errors
            }
            }
        }
        return $data;
    }

    private function make_custom_save($form_id, $action)
    {
        $script = '';
        // make customer save script
        $script .= "function form_custom_save(form, action) {";
        $script .= "    let formData = $('#{$form_id}').serialize();";
        $script .= "    ajax_post(action, formData, function (d) {";
        $script .= "        if (d['success']) {";
        $script .= "            console.log('data:');";
        $script .= "            console.log(d);";
        $script .= "            let id = $('#{$form_id}').find('input[name=\"id\"]').val() || 0;";
        $script .= "            let action = 'save_and_close';";
        $script .= "            data = d['data'] || formData;"; // Use the data from the response or the serialized form data
        $script .= "            let pageType = 'listing';
                                if ($(form).closest('.ecms-single').length) {
                                    pageType = 'single';
                                }";
        $script .= "            let view  = (id==0) ? 'create' : 'update';";
        $script .= "            ajax_submit_form_post(id, action, data, pageType, view);";
        $script .= "        }";
        $script .= "        if (d['error']) {";
        $script .= "            console.error('Error:', d['error']);";
        $script .= "        }";
        $script .= "    });";
        $script .= "}";
        // Add the script to the form scripts
        $this->form_scripts[] = $script;
        // Return the script
        return $script;
    }

    private function make_custom_button($field, $class = "", $attr = array()) 
    {
        $fieldData = explode('|', $field);
        $fieldName = $fieldData[0];
        
        //Determine whether or not to show label and then get label data
        $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_' . $fieldName);
        $label = ! empty($label) ? $label : 'Button';

        //Set default attributes
        $attr['title'] = isset($attr['title']) ? $attr['title'] : $label;
        $attr['class'] = isset($attr['class']) ? $attr['class'] : 'btn btn-primary ' . $class;

        //Build html
        $html = '<a ' . build_attributes($attr) . '>' . $label . '</a>';

        return $html;
    }


    /******** Usage and example functions ********/

    /**
     * Test layout for the form builder
     */
    public function form_schema(){
        return [
            'form' => [ // This key should be set for any schema to be created
                'id' => 'form_builder_test', // set this variable to the id of the form
                'class' => 'form-horizontal', // set this variable to add additional classes to the form
                'action' => '/save', // set this variable to the action you want to perform. This function should exist in your controller
            ],
            [ // This is a segment of the form schema
                'row' => true, // set this variable to true if you want to create a new row (will add the correct html layout)
                'custom_class' => 'custom-class', // Set a custom class (or multiple classes separated by spaces) for this segment (optional)
                'header' => 'label_basic_fields', // Set the header for this segment (Will only be displayed if row is true)
                'fields' => [ // Set the fields for this segment
                    'title' => [ // This is the field name (should be unique and relate to the database column)
                        'col' => [ // Set the column width for this field according to different screen sizes
                            'md' => 12,
                            'lg' => 6
                        ],
                        'type' => 'text', // Set the field type (can be text, email, number, time, password, textarea, ck_editor, select, multi_select, radio, checkbox, date, date_time, file, image, multi_image)
                        'required' => false, // Set to true if the field is required
                        'attr' => [
                            "disabled" => true,
                            "data-node-nr" => 1,
                        ] // input attributes in array as key => value pairs
                    ],
                    'quantity' => [
                        'col' => 6, // Set as integer if you want to use the same width for all screen sizes
                        'type' => 'number',
                        'label' => 'label_quantity', // Set the label for the field (must be added to the language file)
                        'required' => true,
                    ]
                ]
            ],
            [
                'row' => true,
                'fields' => [
                    'email' => [
                        'col' => [
                            'md' => 12,
                            'lg' => 6
                        ],
                        'type' => 'email',
                        'required' => false,
                    ],
                    'time' => [
                        'col' => 6,
                        'type' => 'time',
                        'label' => 'label_time',
                        'required' => true,
                    ]
                ]
            ],
            [
                'row' => true,
                'fields' => [
                    'password' => [
                        'col' => [
                            'md' => 12,
                            'lg' => 6
                        ],
                        'type' => 'password',
                        'required' => false,
                    ],
                    'password_confirm' => [
                        'col' => 6,
                        'type' => 'password',
                        'label' => 'label_password',
                        'required' => true,
                    ]
                ]
            ],
            [
                'row' => true,
                'fields' => [
                    'textarea' => [
                        'col' => 12,
                        'type' => 'textarea',
                        'required' => false,
                    ],
                ]
            ],
            [
                'row' => true,
                'fields' => [
                    'ckeditor' => [
                        'col' => 12,
                        'type' => 'ckeditor',
                        'required' => false,
                    ],
                ]
            ],
            [
                'row' => true,
                'fields' => [
                    'ckeditor_simple' => [
                        'col' => 12,
                        'type' => 'ckeditor_simple',
                        'label' => 'label_ckeditor',
                        'required' => true,
                    ]
                ]
            ],
            [
                'row' => true,
                'fields' => [
                    'select' => [
                        'col' => [
                            'md' => 12,
                            'lg' => 6
                        ],
                        'type' => 'select',
                        'label' => 'label_options',
                        'required' => false,
                        'options' => [ // Set the options for the select field as key => value pairs
                            '1' => 'Option 1',
                            '2' => 'Option 2',
                            '3' => 'Option 3'
                        ]
                    ],
                    'multi_select' => [
                        'col' => 6,
                        'type' => 'multi_select',
                        'label' => 'label_options',
                        'required' => true,
                        'options' => [
                            '1' => 'Option 1',
                            '2' => 'Option 2',
                            '3' => 'Option 3'
                        ]
                    ]
                ]
            ],
            [
                'row' => true,
                'fields' => [
                    'radio' => [
                        'col' => 6,
                        'type' => 'radio',
                        'label' => 'label_options',
                        'required' => false,
                        'options' => [
                            '1' => 'Option 1',
                            '2' => 'Option 2',
                            '3' => 'Option 3'
                        ]
                    ],
                    'radio_default' => [ // If no options are set, it will use the default options (1 => 'Yes', 0 => 'No')
                        'col' => 6,
                        'type' => 'radio',
                        'label' => 'label_options',
                        'required' => true,
                    ],
                ]
            ],
            [
                'row' => true,
                'fields' => [
                    'checkbox1' => [
                        'col' => 2,
                        'type' => 'checkbox',
                        'required' => true,
                        'value' => 'chk_1' // Set the value for the checkbox, if not set it will default to 1
                    ],
                    'checkbox2' => [
                        'col' => 2,
                        'type' => 'checkbox',
                        'required' => true,
                        'value' => 'chk_2'
                    ],
                    'checkbox3' => [
                        'col' => 2,
                        'type' => 'checkbox',
                        'required' => true,
                        'value' => 'chk_3'
                    ],
                    'checkbox4' => [
                        'col' => 2,
                        'type' => 'checkbox',
                        'required' => true,
                        'checkbox_label' => 'Checkbox 4', // Set the label for the checkbox.
                        'value' => 'chk_4'
                    ],
                    'checkbox5' => [
                        'col' => 2,
                        'type' => 'checkbox',
                        'required' => true,
                        'checkbox_label' => 'Checkbox 5',
                        'value' => 'chk_5'
                    ],
                ]
            ],
            [
                'row' => true,
                'fields' => [
                    'date' => [
                        'col' => [
                            'md' => 12,
                            'lg' => 6
                        ],
                        'type' => 'date',
                        'required' => false,
                    ],
                    'date_time' => [
                        'col' => 6,
                        'type' => 'date_time',
                        'required' => true,
                    ]
                ]
            ],
            [
                'row' => true,
                'fields' => [
                    'file' => [
                        'col' => [
                            'md' => 12,
                            'lg' => 6
                        ],
                        'type' => 'file',
                        'required' => false,
                    ],
                    'multi_file' => [
                        'col' => 6,
                        'type' => 'multi_file',
                        'required' => true,
                    ]
                ]
            ],
            [
                'row' => true,
                'fields' => [
                    'image' => [
                        'col' => [
                            'md' => 12,
                            'lg' => 6
                        ],
                        'type' => 'image',
                        'required' => false,
                    ],
                    'multi_image' => [
                        'col' => 6,
                        'type' => 'multi_image',
                        'required' => true,
                    ]
                ]
            ],

        ];
    }

    /**
     * Generate the JavaScript for the form.
     */
    public function script_example() {
        // This function can be used to add custom scripts to the form
        // Each script should be a string and will be added to the form view between <script> tags already set
        // Each script can be in a separate value of the array
        return array(
            '$("#form_builder_test input").on("focus", function() {
                console.log("Form Builder Test Script Loaded");
            });',
        );
    }

    /**
     * Generate the styling for the form.
     */
    public function styling_example() {
        // This function can be used to add custom styling to the form
        // Each style should be an array with the selector as the key and the styles as the value
        // Each style will be added to the form view between <style> tags already set
        return array(
            '#form_builder_test' => "
                'background': 'linear-gradient(to right,rgb(58, 58, 58), #000)';
                'padding': '20px';
                'border-radius': '5px';
            ",
            '#form_builder_test input, #form_builder_test textarea, #form_builder_test .ck.ck-editor' =>"
                'background': 'linear-gradient(to right, #000, rgb(58, 58, 58))';
                'border': '1px solid rgb(243, 117, 15)';
                'padding': '10px';
            ",
        );
    }

    /**
     * Get the form view
     */
    public function get_form_view_example()
    {
        // $this->load->library('Form_builder'); This line should be added in the constructor method of your controller
        // $form = $this->form_builder::make() This will create a new instance of the Form_builder class (required)
        //         ->set_schema($this->form_schema()) This will set the form schema (required)
        //         ->set_scripts($this->script_example())  This will set the scripts for the form (optional)
        //         ->set_styling($this->styling_example())  This will set the styling for the form (optional)
        //         ->make_form($row); // This will make the form based on the schema and data (required) The $data is optional, if not set it will not populate the form fields with data
    
        // $form will be an instance of the Form_builder class with the form view set
        // You can then use $form->form_view to get the form view HTML
    }

    /**
     * Custom action example
     * This function can be used to handle custom actions for the form
     */
    public function custom_action_example() {
        $data = $this->input->post();
        // Custom saving logic can be added here
        dd($data);
        // For now, we just return the data as is
        return [
            'success' => true,
            'data' => $data
        ];
    }


}