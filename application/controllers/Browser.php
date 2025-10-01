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
        $this->load->view('cms/browser/view_upload', array(
            'heading' => ''
        ));
    }

    public function ajax_upload_file() {
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
                if ($file === '.' || $file === '..') continue;

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
