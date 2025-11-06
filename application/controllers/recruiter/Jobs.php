<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Jobs extends CRUD_Controller
{
    public $pageName = 'jobs';
    public $group = 'Jobs';
    public $folder = 'recruiter';
    public $model = 'model_jobs';
    public $singular = 'Job';
    public $plural = 'Jobs';
    public $identifierField = 'name';
    public $quickManage = false; // Disable quick manage since recruiters can't edit
    public $sluggify = true;
    public $adding = false; // Remove Add Job button
    public $allowEdit = false; // Disable editing
    public $sorting = array('name' => 'ASC');
    public $quickManageSize = 4;

    public function __construct()
    {
        parent::__construct();

        $this->folder = 'recruiter';

        // Allow only recruiters
        $login_data = $this->session->userdata('login');
        if (empty($login_data['recruiter'])) {
            redirect('recruiter/dashboard');
        }

        $this->load->model($this->folder . '/' . $this->model);

        // Apply agency filter globally
        $user_agency_id = $this->get_user_agency_id();
        if ($user_agency_id) {
            $this->db->where('mod_jobs.agency_id', $user_agency_id);
        }

        $this->setup_listing();
        $this->setup_fields();

        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }

    private function setup_listing()
    {
        $this->listFields = array(
            'name' => array('label' => lang('label_title'), 'sort' => true),
            'reference_number' => array('label' => lang('label_reference_number'), 'sort' => true),
            'employment_type' => array('label' => lang('label_job_type'), 'sort' => true),
            'industry_name' => array(
                'label' => lang('label_industry'),
                'sort' => true,
            ),
            'agency_name' => array(
                'label' => lang('label_agency'),
                'sort' => true,
            ),
            'candidate_count' => array(
                'label' => 'Candidates',
                'sort' => true,
                'function' => function($str, $row) {
                    $count = isset($row->candidate_count) ? $row->candidate_count : 0;
                    $url = site_url('recruiter/candidates?job_id=' . $row->id);
                    if ($count > 0) {
                        return '<a href="' . $url . '" class="btn btn-sm btn-info" title="View ' . $count . ' Candidates">' . $count . '</a>';
                    } else {
                        return '<span class="text-muted">0</span>';
                    }
                }
            ),
        );

        $this->listActions = array(
            'view' => array(
                'label'     => lang('label_view'),
                'url'       => url($this->pageName . '/view/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'view-row btn-info',
            ),
            'add_candidate' => array(
                'label'     => 'Add Candidate',
                'url'       => site_url('recruiter/candidates/add/{id}'),
                'icon'      => 'fa-user-plus',
                'class'     => 'add-candidate-row btn-success',
                'title'     => 'Add candidate to this job',
            ),
        );

        $this->filters = array(
            'general' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array('mod_jobs.name', 'mod_jobs.reference_number'),
            ),
        );
    }

    public function setup_fields()
{
    $this->formFields = array(
        'main' => array(
            'name' => 'trim|required|strip_tags',
            'reference_number' => 'trim|required|strip_tags|callback_is_unique_reference',
            'description' => 'trim',
            'department' => 'trim|strip_tags',
            'agency_id' => 'trim|required|numeric',
            'industry_id' => 'trim|numeric',
            'employment_type' => 'trim|required',
            'salary_min' => 'trim|numeric',
            'salary_max' => 'trim|numeric',
            'pay_rate' => 'trim|strip_tags',
            'is_remote' => 'trim|numeric',
            'roster' => 'trim|strip_tags',
            'accommodation' => 'trim|strip_tags',
            'transport' => 'trim|strip_tags',
            'application_email' => 'trim|valid_email',
            'application_url' => 'trim|valid_url',
            'closing_date' => 'trim',
            'skills' => 'trim', // Skills as direct column
            'qualifications' => 'trim', // Qualifications as direct column
        ),
        // Remove the multi_selects section since we're using direct columns now
    );
}

    public function index()
    {
        $this->breadcrumbs = array(
            array(
                'title' => lang($this->pageName . '_heading'),
                'url' => redir($this->pageName, true),
            ),
        );
        $this->view = 'listing';
        $this->load->view($this->folder . '/view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading' => lang($this->pageName . '_heading'),
            'noRows' => lang($this->pageName . '_no_rows'),
        ));
        $this->load->view($this->folder . '/view_footer');
    }

    public function get_all($limit = null, $offset = null, $sort_by = null, $sort_order = null)
    {
        $user_agency_id = $this->get_user_agency_id();
        if ($user_agency_id) {
            $this->db->where('mod_jobs.agency_id', $user_agency_id);
        }
        return parent::get_all($limit, $offset, $sort_by, $sort_order);
    }

    private function get_user_agency_id()
    {
        $login = $this->session->userdata('login');
        if (!empty($login['recruiters']['agency_id'])) {
            return (int) $login['recruiters']['agency_id'];
        }
        return null;
    }

    public function is_unique_reference($reference)
    {
        $id = $this->input->post('id');
        $this->form_validation->set_message('is_unique_reference', lang('ref_exists'));
        return $this->{$this->model}->is_unique_reference($reference, $id);
    }

    public function edit($id)
    {
        show_404(); // Block access to edit
    }

    public function enable($id)
    {
        show_404(); // Block access to enable
    }

    public function disable($id)
    {
        show_404(); // Block access to disable
    }

    /**
     * View job details - Only method recruiters can access
     */
   /**
 * View job details - Only method recruiters can access
 */
public function view($id)
{
    $user_agency_id = $this->get_user_agency_id();
    
    // Get the job with agency filtering and proper joins
    $this->db->select('mod_jobs.*, agencies.name as agency_name, mod_industries.name as industry_name');
    $this->db->from('mod_jobs');
    $this->db->join('agencies', 'agencies.id = mod_jobs.agency_id', 'left');
    $this->db->join('mod_industries', 'mod_industries.id = mod_jobs.industry_id', 'left');
    $this->db->where('mod_jobs.id', $id);
    
    if ($user_agency_id) {
        $this->db->where('mod_jobs.agency_id', $user_agency_id);
    }
    
    $job = $this->db->get()->row();
    
    if (!$job) {
        show_404();
    }

    // Load additional data - skills and qualifications are now direct columns
    $data['job'] = $job;
    
    // Parse skills and qualifications from the direct columns
    $data['skills'] = !empty($job->skills) ? explode(',', $job->skills) : [];
    $data['qualifications'] = !empty($job->qualifications) ? explode(',', $job->qualifications) : [];
    
    // Remove these as they're no longer needed from database
    $data['skill_options'] = null;
    $data['qualification_options'] = null;
    
    // Get updated fields for badges
    $data['updated_fields'] = $this->get_updated_fields_for_job($id);

    // Set breadcrumbs
    $this->breadcrumbs = array(
        array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        ),
        array(
            'title' => $job->name,
            'url' => '#',
        ),
    );

    // Load the view
    $this->load->view($this->folder . '/view_header');
    $this->load->view('recruiter/jobs/view_job', $data);
    $this->load->view($this->folder . '/view_footer');
}

    /**
     * Get updated fields from notifications for this job
     */
    private function get_updated_fields_for_job($job_id) {
        $recruiter_id = $this->get_current_recruiter_id();
        
        if (!$recruiter_id) {
            log_message('debug', 'No recruiter ID found');
            return [];
        }

        log_message('debug', 'Looking for update notifications for job: ' . $job_id . ', recruiter: ' . $recruiter_id);

        // Get the latest unread update notification for this job
        $this->db->select('updated_fields, id, created_at, type');
        $this->db->from('notifications');
        $this->db->where('receiver_type', 'recruiter');
        $this->db->where('receiver_id', $recruiter_id);
        $this->db->where('related_entity', 'job');
        $this->db->where('related_entity_id', $job_id);
        $this->db->where("(type = 'job_updated' OR type = '')");
        $this->db->where('is_read', 0);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);
        
        $query = $this->db->get();
        
        log_message('debug', 'Notifications query: ' . $this->db->last_query());
        log_message('debug', 'Found notifications: ' . $query->num_rows());

        if ($query->num_rows() > 0) {
            $notification = $query->row();
            log_message('debug', 'Found notification ID: ' . $notification->id . ' with updated_fields: ' . $notification->updated_fields);
            
            if (!empty($notification->updated_fields)) {
                $updated_fields = json_decode($notification->updated_fields, true);
                log_message('debug', 'Decoded updated fields: ' . print_r($updated_fields, true));
                return is_array($updated_fields) ? $updated_fields : [];
            }
        } else {
            log_message('debug', 'No unread update notifications found for this job');
        }

        return [];
    }

    /**
     * Get current recruiter ID from session
     */
    private function get_current_recruiter_id() {
        $login_data = $this->session->userdata('login');
        $recruiter_id = !empty($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : null;
        log_message('debug', 'Current recruiter ID from session: ' . $recruiter_id);
        return $recruiter_id;
    }
}