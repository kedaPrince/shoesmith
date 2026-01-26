<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Recruiters extends CRUD_Controller
{
    public $pageName = 'recruiters';
    public $group = 'Recruiters';
    public $view = '';
    public $model = 'Model_recruiters';
    public $sorting = array('first_name' => 'ASC');
    public $singular = 'Recruiter';
    public $plural = 'Recruiters';
    public $quickManage = true;
    public $identifierField = 'first_name';
    public $hideSubNav = true;
    public $seoFields = false;
    public $quickManageSize = 4;
    public $sluggify = false;
    public $allowEdit = true;
    public $rowClick = 'edit-row';
    public $adding = true;

    public function __construct()
    {
        parent::__construct();
        
        $this->load->helper('profile_helper');
        $userType = getLoggedInUserType();
        $loginData = loginData();
        
        // For agency context, always allow editing and filter by agency
        $this->allowEdit = true;
        $this->rowClick = $this->allowEdit ? 'edit-row' : 'vieww-row';
        $this->adding = false;
        $this->editing = $this->allowEdit;
        $this->abling = $this->allowEdit;
        $this->deleting = $this->allowEdit;

        // Store agency ID for filtering
        $this->agency_id = isset($loginData['id']) ? $loginData['id'] : null;
        

        $this->load->model($this->folder . '/' . $this->model);
        $this->setup_listing();
        $this->setup_fields();

        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }

    private function setup_listing()
    {
        $allowEdit = $this->allowEdit;
        
        $this->listFields = array(
            'first_name' => array(
                'label' => lang('label_first_name'),
                'sort' => true,
            ),
            'last_name' => array(
                'label' => lang('label_last_name'),
                'sort' => true,
            ),
            'email' => array(
                'label' => lang('label_email'),
                'sort' => true,
            ),
            'agency_name' => array(
                'label' => lang('label_agency'),
                'type' => 'custom',
                'function' => function($value, $row) {
                    return !empty($row->agency_name) ? $row->agency_name : '-';
                },
                'sort' => false,
            ),
            'user_type' => array(
                'label' => lang('label_user_type'),
                'type' => 'custom',
                'function' => function($value, $row) {
                    return !empty($row->user_type) ? $row->user_type : '-';
                },
                'sort' => false,
            ),
        );

        $this->listActions = array(
            'edit' => array(
                'label'     => lang('label_edit'),
                'url'       => url($this->pageName . '/edit/{id}'),
                'icon'      => 'fa-edit',
                'class'     => 'edit-row',
                'function'  => function ($str, $row) use ($allowEdit) {
                    return (!$allowEdit) ? false : $str;
                },
            ),
            'enable' => array(
                'label'     => lang('label_enable'),
                'url'       => url($this->pageName . '/enable/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'enable-row btn-enable',
                'function'  => function ($str, $row) use ($allowEdit) {
                    return (!$allowEdit || $row->enabled) ? false : $str;
                },
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => url($this->pageName . '/disable/{id}'),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => function ($str, $row) use ($allowEdit) {
                    return (!$allowEdit || !$row->enabled) ? false : $str;
                },
            ),
            'delete' => array(
                'label'     => lang('label_delete'),
                'url'       => url($this->pageName . '/remove/{id}'),
                'icon'      => 'fa-trash-o',
                'class'     => 'delete-row btn-delete',
                'function'  => function ($str, $row) use ($allowEdit) {
                    return (!$allowEdit) ? false : $str;
                },
            ),
        );

        $this->filters = array(
            'general' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array('recruiters.first_name', 'recruiters.last_name', 'recruiters.email'),
            ),
        );
    }

    public function setup_fields()
    {
        $this->formFields = array(
            'main' => array(
                'first_name' => 'trim|required|strip_tags',
                'last_name'  => 'trim|required|strip_tags',
                'email'      => 'trim|required|valid_email|callback_is_unique_email',
                'phone'      => 'trim|strip_tags',
                'agency_id'  => 'trim|required|numeric',
                'usr_type_id'=> 'trim|required|numeric',
                'password'   => 'trim|callback_validate_password',
            ),
            'multi_selects' => array(
                'access_groups' => array(
                    'validation'  => 'trim',
                    'pivot_table' => 'pivot_recruiter_access_groups',
                    'main_field'  => 'recruiter_id',
                    'link_field'  => 'access_group_id',
                ),
            ),
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
        $this->load->view($this->folder . '/' . 'view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading' => lang($this->pageName . '_heading'),
            'noRows' => lang($this->pageName . '_no_rows'),
        ));
        $this->load->view($this->folder . '/' . 'view_footer');
    }

    public function quick_manage_extra($id, $row): array
    {
        $loginData = loginData();
        $agency_id = isset($loginData['id']) ? $loginData['id'] : null;
        
        // For agency context, only show their own agency in dropdown
        $agency_options = $this->{$this->model}->get_agency_options();
        
        // If agency user, filter to only show their agency
        if ($agency_id) {
            $filtered_agency_options = ['' => '-- Select Agency --'];
            foreach ($agency_options->result() as $agency) {
                if ($agency->id == $agency_id) {
                    $filtered_agency_options[$agency->id] = $agency->name;
                }
            }
            $agency_options = $filtered_agency_options;
        }
        
        return [
            'agency_options'      => $agency_options,
            'usr_type_options'    => $this->{$this->model}->get_usr_type_options(),
            'access_groups_all'   => $this->{$this->model}->get_access_groups_all(),
            'access_groups'       => $id ? $this->{$this->model}->get_access_groups((int)$id) : [],
        ];
    }

    // Override create to auto-set agency_id for agency users
    public function create_extra_params()
    {
        $loginData = loginData();
        $agency_id = isset($loginData['id']) ? $loginData['id'] : null;
        
        $params = ['enabled' => 1];
        
        // Auto-set agency_id for agency users
        if ($agency_id) {
            $params['agency_id'] = $agency_id;
        }
        
        if (!empty($this->input->post('password'))) {
            $params['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }
        return $params;
    }

    // Override update to prevent agency change for agency users
    public function update_extra_params($id)
    {
        $loginData = loginData();
        $agency_id = isset($loginData['id']) ? $loginData['id'] : null;
        
        $params = [];
        
        // For agency users, ensure they can't change the agency
        if ($agency_id) {
            $params['agency_id'] = $agency_id;
        }
        
        if (!empty($this->input->post('password'))) {
            $params['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }
        
        if (empty($this->input->post('password'))) {
            unset($this->formFields['main']['password']);
        }
        return $params;
    }

    public function ajax_view($id): void
    {
        $recruiter = $this->{$this->model}->get_by_id((int)$id);
        $identifier = $recruiter ? $recruiter->first_name . ' ' . $recruiter->last_name : '';

        $data = array(
            'heading'    => lang($this->pageName . '_heading'),
            'identifier' => $identifier,
            'noRows'     => lang($this->pageName . '_no_rows'),
            'recruiter'  => $recruiter,
        );

        $html = $this->load->view($this->folder . '/' . $this->pageName . '/ajax_view_single', $data, true);
        $this->output->set_output($html);
    }

    // Validation: Unique Email
    public function is_unique_email($email)
    {
        $id = $this->input->post('id');
        $this->form_validation->set_message('is_unique_email', lang('email_exists'));

        $result = $this->{$this->model}->is_unique_email($email, $id);
        return $result;
    }

    // Validation: Password
    public function validate_password()
    {
        $password = $this->input->post('password');
        $confirm  = $this->input->post('confirm_password');

        if (empty($password)) {
            return true;
        }

        if ($password !== $confirm) {
            $this->form_validation->set_message('validate_password', lang('validation_passwords_mismatch'));
            return false;
        }

        if (strlen($password) < 8) {
            $this->form_validation->set_message('validate_password', lang('validation_password_weak'));
            return false;
        }

        return true;
    }

// In recruiter logout function
public function logout()
{
    // Get recruiter ID before clearing session
    $login_data = $this->session->userdata('login');
    $recruiter_id = isset($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : 0;
    
    // ===== ONLY HERE: Remove from user_sessions table =====
    if ($recruiter_id) {
        $this->db->where('user_id', $recruiter_id)
                 ->where('user_type', 'recruiter')
                 ->delete('user_sessions');
        
        // Also set last_activity_at to old time
        $this->db->where('id', $recruiter_id)
                 ->update('recruiters', [
                     'last_activity_at' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
                 ]);
    }
    
    // Clear PHP session
    $this->session->unset_userdata('login');
    
    // Redirect to login
    redirect('recruiter/login');
}

public function update_activity()
{
    $login_data = $this->session->userdata('login');
    $recruiter_id = isset($login_data['recruiter']['id']) ? $login_data['recruiter']['id'] : 0;
    
    if ($recruiter_id) {
        // Update recruiter's last_activity_at
        $this->db->where('id', $recruiter_id)
                 ->update('recruiters', [
                     'last_activity_at' => date('Y-m-d H:i:s')
                 ]);
        
        // Update user_sessions
        $session_id = session_id();
        $this->db->where('session_id', $session_id)
                 ->where('user_id', $recruiter_id)
                 ->where('user_type', 'recruiter')
                 ->update('user_sessions', [
                     'last_activity' => date('Y-m-d H:i:s')
                 ]);
    }
    
    echo json_encode(['success' => true]);
}
}

