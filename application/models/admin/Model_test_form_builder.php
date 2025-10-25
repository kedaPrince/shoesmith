<?php
class Model_test_form_builder extends CRUD_Model {
    protected $table = 'sys_form_schemas';

    // ✅ OVERRIDE THE UPDATE METHOD TO PREVENT NULL ERROR
    public function update(array $data, $whereValue, $whereField = 'id', $table = false) {
        $table = $table ? $table : $this->table;

        $data['updated_at'] = date('Y-m-d H:i:s');

        $result = $this->db->update($table, $data, array($whereField => $whereValue));
        
        if (!$result) {
            log_message('error', 'Update failed: ' . $this->db->last_query());
            Anomalies::log('Failed to update from CRUD', $this->db->last_query());
            return false;
        }

        // ✅ SIMPLE FIX: Return the ID we're updating instead of querying for it
        // This prevents the "Attempt to read property 'id' on null" error
        return $whereValue;
    }

    // Required by CRUD_Controller for listing
    public function get_all($section = '') {
        return $this->db
            ->from($this->table)
            ->where('removed IS NULL OR removed = 0', null, false)
            ->where('deleted_at IS NULL')
            ->order_by('id', 'DESC')
            ->get(); // ← DO NOT call ->result() here!
    }

    // Also required: total count
    public function get_count() {
        return $this->db
            ->from($this->table)
            ->where('removed IS NULL OR removed = 0', null, false)
            ->where('deleted_at IS NULL')
            ->count_all_results();
    }

    // For editing/loading single item
    public function get_by_id($id, $table = false) {
        if ($table === false) {
            $table = $this->table;
        }
        return $this->db
            ->where('id', $id)
            ->get($table)
            ->row();
    }

    // Keep if used elsewhere
    public function get_form_data($id) {
        return $this->db
            ->get_where($this->table, ['id' => $id])
            ->result_array();
    }

    // For input types dropdown in quick manage
    public function get_input_types() {
        return [
            ['id' => 'text',           'name' => 'Text'],
            ['id' => 'email',          'name' => 'Email'],
            ['id' => 'number',         'name' => 'Number'],
            ['id' => 'time',           'name' => 'Time'],
            ['id' => 'password',       'name' => 'Password'],
            ['id' => 'textarea',       'name' => 'Textarea'],
            ['id' => 'ckeditor_simple','name' => 'CKEditor Simple'],
            ['id' => 'ckeditor',       'name' => 'CKEditor'],
            ['id' => 'dropdown',       'name' => 'Dropdown'],
            ['id' => 'multiselect',    'name' => 'Multi Select'],
            ['id' => 'radio',          'name' => 'Radio'],
            ['id' => 'checkbox',       'name' => 'Checkbox'],
            ['id' => 'hidden',         'name' => 'Hidden'],
            ['id' => 'date',           'name' => 'Date'],
            ['id' => 'datetime',       'name' => 'Date Time'],
            ['id' => 'multifile',      'name' => 'Multi File Upload'],
            ['id' => 'file',           'name' => 'File Upload'],
            ['id' => 'image',          'name' => 'Image Upload'],
            ['id' => 'multiimage',     'name' => 'Multi Image Upload'],
            ['id' => 'button',         'name' => 'Button'],
        ];
    }
}