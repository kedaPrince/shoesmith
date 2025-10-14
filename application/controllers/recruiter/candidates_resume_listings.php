<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Candidates_resume_listings extends CRUD_Controller
{
    public $pageName = 'candidates_resume_listings';
    public $group = 'Candidates Management';
    public $folder = 'recruiter';
    public $model = 'Model_candidates_resume_listings';
    public $sorting = array('first_name' => 'ASC', 'last_name' => 'ASC');
    public $singular = 'candidate resume';
    public $plural = 'candidates resumes';
    public $quickManage = false;
    public $identifierField = 'first_name';
    public $hideSubNav = false;

    public function __construct()
    {
        parent::__construct();
        $this->folder = 'recruiter';

        // Allow only recruiters
        $login_data = $this->session->userdata('login');
        if (empty($login_data['recruiter'])) {
            redirect('recruiter/dashboard');
        }

        $this->setup_listing();
        $this->setup_fields();
        $this->load->model($this->folder . '/' . $this->model);
        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }

    private function setup_listing(): void
    {
        $this->listFields = array(
            'reference_number' => array('label' => lang('label_reference_number'), 'sort' => true),
            'first_name' => array('label' => lang('label_first_name'), 'sort' => true),
            'last_name' => array('label' => lang('label_last_name'), 'sort' => true),
            'email' => array('label' => lang('label_email'), 'sort' => true),
            'phone' => array('label' => lang('label_phone'), 'sort' => true),
            'cv_file' => array('label' => lang('label_cv_file'), 'sort' => false, 'type' => 'custom'),
            'application_date' => array('label' => lang('label_application_date'), 'sort' => true, 'type' => 'date'),
            'status' => array('label' => lang('label_status'), 'sort' => true),
        );

        $this->listActions = array(
            'download_cv' => array(
                'label' => lang('label_download_cv'),
                'url' => url($this->pageName . '/download_cv/{id}'),
                'icon' => 'fa-download',
                'class' => 'btn-primary download-cv',
            ),
            'view' => array(
                'label' => lang('label_view'),
                'url' => url('candidates/view/{id}'),
                'icon' => 'fa-eye',
                'class' => 'btn-info view-candidate',
            ),
        );

        $this->filters = array(
            'search' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array('candidates.first_name', 'candidates.last_name', 'candidates.email', 'candidates.reference_number'),
            ),
            'status' => array(
                'label' => lang('label_status'),
                'type' => 'dropdown',
                'field' => 'candidates.status',
                'options' => array(
                    'new' => 'New',
                    'reviewed' => 'Reviewed',
                    'shortlisted' => 'Shortlisted',
                    'interviewed' => 'Interviewed',
                    'rejected' => 'Rejected',
                    'hired' => 'Hired',
                    'on_hold' => 'On Hold',
                ),
            ),
            'has_cv' => array(
                'label' => lang('label_has_cv'),
                'type' => 'dropdown',
                'field' => 'candidates.cv_file',
                'options' => array(
                    '1' => 'With CV',
                    '0' => 'Without CV',
                ),
            ),
        );
    }

    public function setup_fields(): void
    {
        // This controller is mainly for viewing/downloading, not editing
        $this->formFields = array();
        $this->formLabels = array();
    }

    public function index(): void
    {
        $this->breadcrumbs = array(
            array('title' => lang('candidates_heading'), 'url' => redir('candidates', true)),
            array('title' => lang($this->pageName . '_heading'), 'url' => redir($this->pageName, true)),
        );
        
        $this->view = 'listing';
        $this->load->view($this->folder . '/view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading' => lang($this->pageName . '_heading'),
            'noRows' => lang($this->pageName . '_no_rows'),
        ));
        $this->load->view($this->folder . '/view_footer');
    }

    public function download_cv($id)
    {
        $candidate = $this->{$this->model}->get_candidate_with_cv($id);
        
        if (!$candidate || empty($candidate->cv_file)) {
            flash_notification(lang('cv_not_found'), 'error');
            redirect(redir($this->pageName, true));
        }

        $file_path = FCPATH . 'uploads/candidates/cv/' . $candidate->cv_file;
        
        if (!file_exists($file_path)) {
            flash_notification(lang('cv_file_not_found'), 'error');
            redirect(redir($this->pageName, true));
        }

        // Get file info
        $file_info = pathinfo($file_path);
        $clean_name = $candidate->first_name . '_' . $candidate->last_name . '_CV.' . $file_info['extension'];
        
        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $clean_name . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Clear output buffer
        ob_clean();
        flush();
        
        // Output file
        readfile($file_path);
        exit;
    }

    public function preview_cv($id)
    {
        $candidate = $this->{$this->model}->get_candidate_with_cv($id);
        
        if (!$candidate || empty($candidate->cv_file)) {
            show_error(lang('cv_not_found'));
        }

        $file_path = FCPATH . 'uploads/candidates/cv/' . $candidate->cv_file;
        
        if (!file_exists($file_path)) {
            show_error(lang('cv_file_not_found'));
        }

        $file_info = pathinfo($file_path);
        $mime_type = $this->get_mime_type($file_info['extension']);

        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: inline; filename="' . $candidate->cv_file . '"');
        header('Content-Length: ' . filesize($file_path));
        
        readfile($file_path);
        exit;
    }

    private function get_mime_type($extension)
    {
        $mime_types = array(
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        );

        return isset($mime_types[strtolower($extension)]) ? $mime_types[strtolower($extension)] : 'application/octet-stream';
    }
}