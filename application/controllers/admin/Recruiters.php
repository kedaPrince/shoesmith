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
    public $identifierField = 'first_name'; // Changed from 'name'
    public $hideSubNav = true;
    public $seoFields = false;
    public $quickManageSize = 4;
    public $sluggify = false; // Recruiters don't need slugs
    public $allowEdit = true;
    public $rowClick = 'edit-row';
    public $adding = true;

    public function __construct()
    {
        parent::__construct();

        if (!function_exists('getLoggedInUserType')) {
            $this->load->helper('profile_helper');
        }
        $this->load->helper('profile_helper');
        $userType = getLoggedInUserType();
        $this->allowEdit = in_array($userType, ['Super Admin', 'General Admin']);
        $this->rowClick = $this->allowEdit ? 'edit-row' : 'vieww-row';
        if (!$this->allowEdit) {
            $this->adding = false;
        }

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
            'type' => 'custom', // ✅ Critical: tells system it's not a real column
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
                'function'  => function ($str, $row) {
                    return (!$this->allowEdit) ? false : $str;
                },
            ),
            'enable' => array(
                'label'     => lang('label_enable'),
                'url'       => url($this->pageName . '/enable/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'enable-row btn-enable',
                'function'  => function ($str, $row) {
                    return (!$this->allowEdit || $row->enabled) ? false : $str;
                },
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => url($this->pageName . '/disable/{id}'),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => function ($str, $row) {
                    return (!$this->allowEdit || !$row->enabled) ? false : $str;
                },
            ),
            'delete' => array(
                'label'     => lang('label_delete'),
                'url'       => url($this->pageName . '/remove/{id}'),
                'icon'      => 'fa-trash-o',
                'class'     => 'delete-row btn-delete',
                'function'  => function ($str, $row) {
                    return (!$this->allowEdit) ? false : $str;
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
        return [
            'agency_options'      => $this->{$this->model}->get_agency_options(),
            'usr_type_options'    => $this->{$this->model}->get_usr_type_options(),
            'access_groups_all'   => $this->{$this->model}->get_access_groups_all(),
            'access_groups'       => $id ? $this->{$this->model}->get_access_groups((int)$id) : [],
        ];
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

    // ✅ Let the model handle the query (like administrators do)
    $result = $this->{$this->model}->is_unique_email($email, $id);
    return $result;
}

    // Validation: Password
    public function validate_password()
    {
        $password = $this->input->post('password');
        $confirm  = $this->input->post('confirm_password');

        if (empty($password)) {
            return true; // Allow update without password change
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

    // Extra params for create
    public function create_extra_params()
{
    $params = ['enabled' => 1];
    if (!empty($this->input->post('password'))) {
        $params['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
    }
    return $params;
}

    // Extra params for update
    public function update_extra_params($id)
{
    $params = [];
    if (!empty($this->input->post('password'))) {
        $params['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
    }
    // If no password, remove it from formFields so it's not updated
    if (empty($this->input->post('password'))) {
        unset($this->formFields['main']['password']);
    }
    return $params;
}
}