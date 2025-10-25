<?php
class Model_template_instances extends CRUD_Model 
{
    protected $table = 'template_instances';
    
    public function __construct() 
    {
        parent::__construct();
    }

    public function save_instance($template_id, $instance_name, $form_data) 
    {
        log_message('debug', '=== MODEL save_instance CALLED ===');
        log_message('debug', 'Template ID: ' . $template_id);
        log_message('debug', 'Instance name: ' . $instance_name);
        log_message('debug', 'Form data: ' . print_r($form_data, true));

        // Validate template exists
        $template_exists = $this->db->where('id', $template_id)
                                  ->where('enabled', 1)
                                  ->count_all_results('mod_layouts');
        
        if (!$template_exists) {
            throw new Exception("Template with ID {$template_id} not found or not enabled");
        }

        // FIX: Save the form data directly as JSON
        $data = [
            'template_id' => $template_id,
            'name' => $instance_name,
            'form_data' => json_encode($form_data), // This should be the actual form data
            'created_at' => date('Y-m-d H:i:s')
        ];

        log_message('debug', 'Data to insert: ' . print_r($data, true));

        $this->db->insert($this->table, $data);
        $instance_id = $this->db->insert_id();

        log_message('debug', 'Insert ID: ' . $instance_id);

        if (!$instance_id) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . print_r($error, true));
            throw new Exception('Database insert failed: ' . $error['message']);
        }

        log_message('debug', '=== MODEL save_instance COMPLETED SUCCESSFULLY ===');
        return $instance_id;
    }

    /**
     * Update template instance
     */
    public function update_instance($instance_id, $form_data) 
    {
        $data = [
            'form_data' => json_encode($form_data), // This should be the actual form data
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $instance_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Get single instance
     */
    public function get_instance($instance_id) 
    {
        return $this->db->get_where($this->table, ['id' => $instance_id])->row();
    }

    /**
     * Get instances by template ID
     */
    public function get_instances_by_template($template_id) 
    {
        return $this->db->get_where($this->table, ['template_id' => $template_id])->result();
    }

    // Required CRUD_Model methods
    public function get_all($section = '') 
    {
        $this->db->where('removed', 0);
        return $this->db->get($this->table);
    }

    public function get_count() 
    {
        $this->db->where('removed', 0);
        return $this->db->count_all_results($this->table);
    }

    public function get_by_id($id, $table = false) 
    {
        if ($table === false) {
            $table = $this->table;
        }
        $this->db->where('removed', 0);
        return $this->db->where('id', $id)->get($table)->row();
    }
}
?>