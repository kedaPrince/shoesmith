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
    public $adding = false;

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
            'cv_file' => array(
                'label' => lang('label_cv_file'), 
                'sort' => false, 
                'type' => 'custom',
                'function' => 'custom_field_cv_file'
            ),
            'application_date' => array('label' => lang('label_application_date'), 'sort' => true, 'type' => 'date'),
            'status' => array('label' => lang('label_status'), 'sort' => true),
        );

        // FIXED: Change 'title' to 'label' to match what the view expects
        $this->listActions = array(
            'download' => array(
                'label' => 'Download CV', // CHANGED from 'title' to 'label'
                'icon' => 'fa-download',
                'class' => 'btn btn-sm btn-primary',
                'url' => site_url('recruiter/candidates_resume_listings/download_cv/{id}'),
                'target' => '_self'
            )
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
            // 'has_cv' => array(
            //     'label' => lang('label_has_cv'),
            //     'type' => 'dropdown',
            //     'field' => 'candidates.cv_file',
            //     'options' => array(
            //         '1' => 'With CV',
            //         '0' => 'Without CV',
            //     ),
            // ),
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
    
    // Check if candidate exists AND belongs to current recruiter
    if (!$candidate) {
        flash_notification(lang('cv_not_found_or_no_access'), 'error');
        redirect(redir($this->pageName, true));
    }
    
    // Double-check recruiter ownership (redundant but safe)
    $login_data = $this->session->userdata('login');
    if (!empty($login_data['recruiter'])) {
        // Access as array instead of object
        $recruiter_id = $login_data['recruiter']['id'] ?? $login_data['recruiter']->id ?? null;
        if ($recruiter_id && $candidate->recruiter_id != $recruiter_id) {
            flash_notification(lang('no_permission_to_access_cv'), 'error');
            redirect(redir($this->pageName, true));
        }
    }
    
    if (empty($candidate->cv_file)) {
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

    public function custom_field_cv_file($value, $row)
    {
        if (empty($value)) {
            return '<span class="text-muted">No CV</span>';
        }

        $cv_extension = pathinfo($value, PATHINFO_EXTENSION);
        $cv_size = file_exists(FCPATH . 'uploads/candidates/cv/' . $value) ? 
                   round(filesize(FCPATH . 'uploads/candidates/cv/' . $value) / 1024, 1) : 0;
        
        $file_icon = $this->get_file_icon($cv_extension);
        $truncated_name = $this->truncate_filename($value, 25);

        $html = '<div class="cv-file-info">';
        $html .= '<div class="file-type">';
        $html .= '<i class="fa fa-file-' . $file_icon . '-o"></i>';
        $html .= '<span class="text-uppercase">' . $cv_extension . '</span>';
        $html .= '</div>';
        $html .= '<div class="file-size">';
        $html .= '<small class="text-muted">' . $cv_size . ' KB</small>';
        $html .= '</div>';
        $html .= '<div class="file-name">';
        $html .= '<small>' . $truncated_name . '</small>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    private function get_file_icon($extension)
    {
        $icons = [
            'pdf' => 'pdf',
            'doc' => 'word',
            'docx' => 'word',
            'txt' => 'text',
        ];
        
        return isset($icons[strtolower($extension)]) ? $icons[strtolower($extension)] : 'file';
    }

    private function truncate_filename($filename, $length = 25)
    {
        if (strlen($filename) <= $length) {
            return $filename;
        }
        
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $max_name_length = $length - strlen($extension) - 1;
        
        if (strlen($name) > $max_name_length) {
            $name = substr($name, 0, $max_name_length - 3) . '...';
        }
        
        return $name . '.' . $extension;
    }
}