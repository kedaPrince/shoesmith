<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Templates extends CRUD_Controller 
{

    public function __construct() 
    {
        parent::__construct();
        
        $this->setup_listing();
        $this->setup_fields();
        $this->load->model($this->folder . '/' . $this->model);
        $this->load->model('agency/Model_template_instances');
        
        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true)
        );
        $this->load->library('Form_builder');
    }

    /**
     * Save template instance - SIMPLIFIED
     */
    public function save_instance() 
    {
        // Log that we reached the method
        error_log('=== save_instance METHOD CALLED ===');
        
        // Get POST data
        $template_id = $this->input->post('template_id');
        $instance_name = $this->input->post('instance_name');
        $test_field = $this->input->post('test_field');
        
        error_log('Template ID: ' . $template_id);
        error_log('Instance name: ' . $instance_name);
        error_log('Test field: ' . $test_field);
        error_log('All POST data: ' . print_r($_POST, true));

        // Simple validation
        if (!$template_id || !$instance_name) {
            error_log('VALIDATION FAILED - missing required fields');
            $this->output_json([
                'success' => false,
                'message' => 'Template ID and instance name are required',
                'received_data' => [
                    'template_id' => $template_id,
                    'instance_name' => $instance_name,
                    'test_field' => $test_field
                ]
            ]);
            return;
        }

        try {
            // Prepare simple data
            $form_data = [
                'test_field' => $test_field,
                'submitted_at' => date('Y-m-d H:i:s')
            ];
            
            error_log('Calling model with data: ' . print_r($form_data, true));
            
            // Call model
            $instance_id = $this->Model_template_instances->save_instance($template_id, $instance_name, $form_data);
            
            error_log('Model returned instance ID: ' . $instance_id);
            
            $this->output_json([
                'success' => true,
                'message' => 'Template instance saved successfully!',
                'instance_id' => $instance_id,
                'debug' => [
                    'template_id' => $template_id,
                    'instance_name' => $instance_name,
                    'test_field' => $test_field
                ]
            ]);
            
        } catch (Exception $e) {
            error_log('EXCEPTION: ' . $e->getMessage());
            
            $this->output_json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'debug' => [
                    'error' => $e->getMessage()
                ]
            ]);
        }
    }
    
    private function output_json($data) 
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
?>