<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Agency_templates extends CRUD_Controller 
{
    public $pageName = 'agency_templates';
    public $model = 'Model_agency_templates';
    public $group = 'templates';
    public $singular = 'agency_template';
    public $plural = 'agency_templates';
    public $view = '';
    public $sorting = array('id' => 'ASC');
    public $seoFields = false;
    public $quickManage = false;

    public function __construct() 
    {
        parent::__construct();
        $this->load->model('agency/Model_agency_templates');
        $this->load->model('agency/Model_template_sections');

        // Setup basic listing for CRUD compatibility
        $this->setup_listing();
    }

     private function setup_listing() 
    {
        $this->listFields = array(
            'id' => array('label' => 'ID', 'sort' => true),
            'template_name' => array('label' => 'Template Name', 'sort' => true),
            'agency_id' => array('label' => 'Agency ID', 'sort' => true),
            'created_at' => array('label' => 'Created', 'sort' => true)
        );
    }

    public function build($agency_id = null) 
    {
        if (!$agency_id) {
            $agency_id = $this->session->userdata('agency_id') ?? 1;
        }

        // Get all available sections
        $sections = $this->Model_template_sections->get_all()->result();
        
        // Check if we're creating a new template or editing existing
        $template_id = $this->input->get('template_id');
        $is_new = $this->input->get('new') === 'true' || empty($template_id);
        
        $current_template = null;
        $current_sections = [];
        
        if (!$is_new && !empty($template_id)) {
            // Editing specific template
            $current_template = $this->{$this->model}->get_template_by_id($template_id);
        } elseif (!$is_new) {
            // Get latest template (for backward compatibility)
            $current_template = $this->{$this->model}->get_agency_template($agency_id);
        }
        
        // Load sections if editing existing template
        if ($current_template) {
            $template_with_sections = $this->{$this->model}->get_template_with_sections($current_template->id);
            if ($template_with_sections && !empty($template_with_sections->sections)) {
                $current_sections = array_column($template_with_sections->sections, 'id');
            }
        }

        // Get all templates for this agency to show in template selector
        $all_templates = $this->{$this->model}->get_agency_templates($agency_id);

        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/templates/agency_template_builder', [
            'agency_id' => $agency_id,
            'template_id' => $template_id,
            'sections' => $sections,
            'current_template' => $current_template,
            'current_sections' => $current_sections,
            'all_templates' => $all_templates,
            'section_types' => $this->Model_template_sections->get_available_section_types(),
            'is_new' => $is_new
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

    public function save_custom_template() 
    {
        // ============ FIX: Manually parse input for AJAX/fetch requests ============
        // Check if we need to parse the raw input
        if (empty($_POST) && !empty($this->input->raw_input_stream)) {
            parse_str($this->input->raw_input_stream, $_POST);
            
            // Also update CodeIgniter's input cache
            $this->input->_post_args = $_POST;
            $this->input->post = $_POST;
        }
        // ============ END FIX ============
        
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();
        $csrf_token = $this->input->post($csrf_name);
        
        try {
            $agency_id = $this->input->post('agency_id');
            $template_id = $this->input->post('template_id');
            $template_name = $this->input->post('template_name');
            $description = $this->input->post('description');
            $sections_json = $this->input->post('sections');
            $is_new = empty($template_id);

            // Process sections
            $sections = [];
            if (!empty($sections_json)) {
                $sections = json_decode($sections_json, true);
                if (!is_array($sections)) {
                    $sections = [];
                }
            }

            if ($is_new) {
                // Save NEW template
                $template_id = $this->{$this->model}->save_agency_template(
                    $agency_id, 
                    $template_name, 
                    $description, 
                    $sections
                );
                $message = 'New template created successfully!';
            } else {
                // Update EXISTING template
                $template_id = $this->{$this->model}->update_agency_template(
                    $template_id,
                    $template_name, 
                    $description, 
                    $sections
                );
                $message = 'Template updated successfully!';
            }

            if ($template_id) {
                $this->session->set_flashdata('success', $message);
                redirect('agency/templates'); // Redirect to templates listing
            } else {
                throw new Exception('Failed to save template');
            }
                
        } catch (Exception $e) {
            
            $this->session->set_flashdata('error', 'Error saving template: ' . $e->getMessage());
            redirect('agency/agency_templates/build/' . ($agency_id ?? 1));
        }
    }

    public function select_template($agency_id, $template_id)
    {
        $template = $this->{$this->model}->get_template_by_id($template_id);
        if ($template && $template->agency_id == $agency_id) {
            redirect('agency/agency_templates/build/' . $agency_id . '?template_id=' . $template_id);
        } else {
            $this->session->set_flashdata('error', 'Template not found');
            redirect('agency/agency_templates/build/' . $agency_id);
        }
    }

    // Add method to create new template
    public function create_new($agency_id)
    {
            redirect('agency/agency_templates/build/' . $agency_id . '?new=true');
        }
        public function debug_template_status($agency_id = null) {
        if (!$agency_id) {
            $agency_id = $this->session->userdata('agency_id') ?? 1;
        }

        $this->output->set_content_type('application/json');
        
        $template = $this->{$this->model}->get_agency_template($agency_id);
        $template_with_sections = $template ? $this->{$this->model}->get_template_with_sections($template->id) : null;
        
        $this->output->set_output(json_encode([
            'success' => true,
            'agency_id' => $agency_id,
            'current_template' => $template,
            'template_with_sections' => $template_with_sections,
            'sections_count' => $template_with_sections ? count($template_with_sections->sections) : 0,
            'all_sections' => $this->Model_template_sections->get_all()->result()
        ]));
    }

    public function preview_agency_template($agency_id = null) 
    {
        if (!$agency_id) {
            $agency_id = $this->session->userdata('agency_id') ?? 1;
        }

        $template_html = $this->{$this->model}->render_agency_template($agency_id);

        $this->load->view($this->folder . '/view_header');
        echo '<div class="container-fluid">';
        echo '<div class="row">';
        echo '<div class="col-12">';
        echo '<h2>Your Custom Template Preview</h2>';
        echo $template_html;
        echo '</div>';
        echo '</div>';
        echo '</div>';
        $this->load->view($this->folder . '/view_footer');
    }

    public function get_available_sections() 
    {
        $section_type = $this->input->get('section_type');
        
        $sections = $section_type 
            ? $this->Model_template_sections->get_by_type($section_type)
            : $this->Model_template_sections->get_all()->result();

        // Use $this->output directly
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'sections' => $sections
            ]));
    }

    public function manage_instance($agency_id = null, $template_instance_id = null) 
    {
        if (!$agency_id) {
            $agency_id = $this->session->userdata('agency_id') ?? 1;
        }

        // Get the agency's current template
        $current_template = $this->{$this->model}->get_agency_template($agency_id);
        
        if (!$current_template) {
            show_error('No template found for this agency. Please create a template first.');
        }

        // Get template with sections
        $template_with_sections = $this->{$this->model}->get_template_with_sections($current_template->id);
        
        if (!$template_with_sections || empty($template_with_sections->sections)) {
            show_error('Template has no sections. Please add sections to your template.');
        }

        // Load template instance if editing
        $instance_data = [];
        if ($template_instance_id) {
            $this->load->model('agency/Model_template_instances');
            $instance = $this->Model_template_instances->get_instance($template_instance_id);
            if ($instance) {
                $instance_data = json_decode($instance->form_data, true) ?: [];
            }
        }

        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/templates/agency_template_instance', [
            'agency_id' => $agency_id,
            'template' => $template_with_sections,
            'template_instance_id' => $template_instance_id,
            'instance_data' => $instance_data,
            'current_template' => $current_template
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

    public function save_template_instance() 
    {
        $this->output->set_content_type('application/json');

        try {
            $agency_id = $this->input->post('agency_id');
            $template_instance_id = $this->input->post('template_instance_id');
            $instance_name = $this->input->post('instance_name');
            $form_data = $this->input->post();

            // Remove non-form fields
            unset($form_data['agency_id']);
            unset($form_data['template_instance_id']);
            unset($form_data['instance_name']);
            unset($form_data[$this->security->get_csrf_token_name()]);

            if (!$agency_id || !$instance_name) {
                throw new Exception('Agency ID and instance name are required');
            }

            $this->load->model('agency/Model_template_instances');
            
            if ($template_instance_id) {
                // Update existing instance
                $result = $this->Model_template_instances->update_instance($template_instance_id, $form_data);
                $message = 'Template instance updated successfully';
            } else {
                // Get template ID from agency
                $template = $this->{$this->model}->get_agency_template($agency_id);
                if (!$template) {
                    throw new Exception('No template found for this agency');
                }
                
                // Create new instance
                $template_instance_id = $this->Model_template_instances->save_instance(
                    $template->id, 
                    $instance_name, 
                    $form_data
                );
                $message = 'Template instance saved successfully';
            }

            $this->output->set_output(json_encode([
                'success' => true,
                'message' => $message,
                'template_instance_id' => $template_instance_id
            ]));

        } catch (Exception $e) {
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]));
        }
    }

    public function edit_instance($agency_id = null) 
    {
        if (!$agency_id) {
            $agency_id = $this->session->userdata('agency_id') ?? 1;
        }

        // Get the agency's current template
        $current_template = $this->{$this->model}->get_agency_template($agency_id);
        
        if (!$current_template) {
            show_error('No template found for this agency. Please create a template first.');
        }

        // Get template with sections
        $template_with_sections = $this->{$this->model}->get_template_with_sections($current_template->id);
        
        if (!$template_with_sections || empty($template_with_sections->sections)) {
            show_error('Template has no sections. Please add sections to your template.');
        }

        // Get existing instance data or create new
        $this->load->model('agency/Model_template_instances');
        $instance = $this->Model_template_instances->get_instances_by_template($current_template->id);
        $instance_data = [];
        
        if (!empty($instance)) {
            $instance_data = json_decode($instance[0]->form_data, true) ?: [];
        }

        $this->load->view($this->folder . '/view_header');
        $this->load->view('agency/templates/agency_template_editor', [
            'agency_id' => $agency_id,
            'template' => $template_with_sections,
            'instance_data' => $instance_data,
            'current_template' => $current_template,
            'instance_id' => !empty($instance[0]->id) ? $instance[0]->id : null
        ]);
        $this->load->view($this->folder . '/view_footer');
    }

    public function get_template_data($agency_id = null)
    {
        if (!$agency_id) {
            $agency_id = $this->session->userdata('agency_id') ?? 1;
        }

        $template = $this->{$this->model}->get_template_with_sections_by_agency($agency_id);
        
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode([
            'success' => true,
            'template' => $template ? [
                'id' => $template->id,
                'name' => $template->template_name,
                'sections' => $template->sections,
                'section_count' => count($template->sections ?? [])
            ] : null
        ]));
    }


    public function reset_template($agency_id = 1)
    {
        // ============ ADDED CSRF PROTECTION ============
        // Check if this is a POST request (should be)
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $csrf_name = $this->security->get_csrf_token_name();
            $csrf_token = $this->input->post($csrf_name);
            
            if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
                show_error('Invalid security token', 400);
                return;
            }
        } else {
            // If GET request, show confirmation form with CSRF
            $this->load->view($this->folder . '/view_header');
            echo '<div class="container">';
            echo '<h2>Reset Template</h2>';
            echo '<p>Are you sure you want to reset the template for agency ' . $agency_id . '?</p>';
            echo '<form method="POST" action="' . site_url('agency/agency_templates/reset_template/' . $agency_id) . '">';
            echo '<input type="hidden" name="' . $this->security->get_csrf_token_name() . '" value="' . $this->security->get_csrf_hash() . '">';
            echo '<button type="submit" class="btn btn-danger">Yes, Reset Template</button>';
            echo ' <a href="' . site_url('agency/agency_templates/build/' . $agency_id) . '" class="btn btn-secondary">Cancel</a>';
            echo '</form>';
            echo '</div>';
            $this->load->view($this->folder . '/view_footer');
            return;
        }
        // ============ END CSRF PROTECTION ============
            $this->db->trans_start();
            
            // Delete template sections
            $template = $this->{$this->model}->get_agency_template($agency_id);
            if ($template) {
                $this->db->delete('agency_template_sections', ['agency_template_id' => $template->id]);
                $this->db->delete('agency_custom_templates', ['id' => $template->id]);
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status()) {
                echo "Template reset successfully for agency $agency_id";
            } else {
                echo "Error resetting template";
            }
            
            // Redirect back to builder
            redirect('agency/agency_templates/build/' . $agency_id);
    }


    public function debug_post() 
    {
        // ============ SAME FIX ============
        if (empty($_POST) && !empty($this->input->raw_input_stream)) {
            parse_str($this->input->raw_input_stream, $_POST);
            $this->input->_post_args = $_POST;
            $this->input->post = $_POST;
        }
        // ============ END FIX ============       
        // Check CodeIgniter Input class
        $csrf_name = $this->security->get_csrf_token_name();
 
        
        // Return JSON
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode([
            'post' => $_POST,
            'raw_input' => file_get_contents('php://input'),
            'csrf_name' => $csrf_name,
            'csrf_value' => $_POST[$csrf_name] ?? null,
            'ci_csrf_value' => $this->input->post($csrf_name),
            'ci_all_post' => $this->input->post(),
            'server' => [
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'NOT SET',
                'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'NOT SET'
            ]
        ]));
    }

}