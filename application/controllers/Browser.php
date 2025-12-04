<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Browser extends MY_Controller {
    public function files() {
        //Test

        $files = $this->get_files();

        $this->load->view('cms/browser/view_files', array(
            'files' => $files
        ));
    }

    public function upload() {
        // ============ ADDED CSRF PROTECTION ============
        // Check if this is a POST request (form submission)
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $csrf_name = $this->security->get_csrf_token_name();
            $csrf_token = $this->input->post($csrf_name);
            
            if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
                show_error('Invalid CSRF token', 400);
                return;
            }
            
            // Process form submission here if needed
            // (Currently empty, but protected if added later)
        }
        // ============ END CSRF PROTECTION ============
        
        // Load view for GET requests (display form)
        $this->load->view('cms/browser/view_upload', array(
            'heading' => ''
        ));
    }

    public function ajax_upload_file() {
        // ============ ADDED CSRF PROTECTION ============
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            ajax_return([
                'success' => false,
                'message' => 'Invalid CSRF token',
                'csrf' => $this->security->get_csrf_hash()
            ]);
            return;
        }
        // ============ END CSRF PROTECTION ============
        
        $uploadDir = 'resources/ckuploads';
        dir_create($uploadDir);

        $options['uploadDir'] = $uploadDir . '/';
        $options['title'] = 'auto';
        $options['extensions'] = array('jpg', 'jpeg', 'png', 'gif');

        $this->load->library('file_uploader', array(
            'name' => 'ckfiles',
            'options' => $options
        ));

        // call to upload the files
        $data = $this->file_uploader->upload();

        ajax_return($data);
    }

    public function ajax_remove_file() {
        // ============ ADDED CSRF PROTECTION ============
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            ajax_return([
                'success' => false,
                'message' => 'Invalid CSRF token',
                'csrf' => $this->security->get_csrf_hash()
            ]);
            return;
        }
        // ============ END CSRF PROTECTION ============
        
        if ($this->input->post('file')) {
            $file = 'resources/ckuploads/' . $this->input->post('file');

            //Remove physical file
            delete_file($file);
        }

        ajax_return();
    }

    private function get_files() {

        dir_create('resources/ckuploads');
        $path = abs_path() . 'resources/ckuploads';
        $fileData = array();


        if ($handle = opendir($path)) {
            while (FALSE !== ($file = readdir($handle))) {
                if ($file === '.' || $file === 'next' || $file === '..') continue;

                $fileData[] = array(
                    'name' => $file,
                    'type' => mime_content_type($path . '/' . $file),
                    'size' => filesize($path . '/' . $file),
                    'file' => site_url() . 'resources/ckuploads/' . $file,
                    'data' => array(
                        'url' => site_url() . 'resources/ckuploads/' . $file,
                        'filename' => $file
                    )
                );
            }
            closedir($handle);
        }

        return $fileData;
    }
}