<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Agencies extends CRUD_Controller
{
    public $pageName = 'agencies';
    public $group = 'Agencies';
    public $view = '';
    public $model = 'Model_agencies';
    public $sorting = array('name' => 'ASC');
    public $singular = 'Agency';
    public $plural = 'Agencies';
    public $quickManage = true;
    public $identifierField = 'name';
    public $hideSubNav = true;
    public $seoFields = false;
    public $quickManageSize = 4;
    public $sluggify = true;
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
            'name' => array(
                'label' => lang('label_company_name'),
                'sort' => true,
            ),
            'email' => array(
                'label' => lang('label_email'),
                'sort' => true,
            ),
            'phone' => array(
                'label' => lang('label_telephone'),
                'sort' => true,
            ),
            'industry' => array(
                'label' => lang('label_industry'),
                'sort' => true,
            ),
            'login_enabled' => array(
                'label' => lang('label_login_enabled'),
                'sort' => true,
                'format' => 'boolean',
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
            'login_as' => array(
                'label'     => lang('label_login_as_agency'),
                'url'       => url($this->pageName . '/login_as/{id}'),
                'icon'      => 'fa-sign-in',
                'class'     => 'login-as-agency btn-info',
                'function'  => function ($str, $row) {
                    return (!$this->allowEdit || !$row->login_enabled) ? false : $str;
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
                'field' => array('agencies.name'),
            ),
        );
    }

    public function setup_fields()
    {
        $this->formFields = array(
            'main' => array(
                'name'                  => 'trim|required|strip_tags',
                'slug'                  => 'trim|strip_tags',
                'registration_number'   => 'trim|strip_tags',
                'vat_number'            => 'trim|strip_tags',
                'email'                 => 'trim|required|valid_email|callback_is_unique_agency_email',
                'password'              => 'trim|callback_validate_agency_password',
                'contact_person'        => 'trim|strip_tags',
                'phone'                 => 'trim|strip_tags',
                'address'               => 'trim',
                'billing_contact'       => 'trim|strip_tags',
                'industry'              => 'trim|strip_tags',
                'website'               => 'trim|callback_valid_website',
                'logo'                  => 'trim|strip_tags',
            ),
        );
    }

    /**
     * Validate agency password
     */
    public function validate_agency_password(): bool
    {
        $password = $this->input->post('password');
        $confirm = $this->input->post('confirm_password');

        // Don't validate if password is blank during update
        if (empty($password) && $this->input->post('id')) {
            return true;
        }

        // Check if passwords match
        if ($password != $confirm) {
            $this->form_validation->set_message('validate_agency_password', lang('validation_passwords_mismatch'));
            return false;
        }

        // Check if password is strong enough
        if (!is_password_strong($password, 8, false, false, false, false)) {
            $this->form_validation->set_message('validate_agency_password', lang('validation_password_weak'));
            return false;
        }

        return true;
    }

    /**
     * Check if agency email is unique
     */
    public function is_unique_agency_email(string $email): bool
    {
        $id = $this->input->post('id');

        $this->form_validation->set_message('is_unique_agency_email', lang('email_exists'));
        $result = $this->{$this->model}->is_unique_email($email, $id);

        return $result;
    }

    public function create_extra_params(): array
    {
        $params = [
            'is_approved' => 1,
            'login_enabled' => 1,
            'enabled' => 1
        ];

        // Hash password if provided
        if ($this->input->post('password')) {
            $params['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }

        return $params;
    }

    public function update_extra_params($id): array
    {
        $params = [];

        // Hash new password if provided
        if ($this->input->post('password')) {
            $params['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        } else {
            // Unset password field if not changing
            unset($this->formFields['main']['password']);
        }

        $params['login_enabled'] = $this->input->post('login_enabled') ? 1 : 0;

        return $params;
    }

    public function create_success_extra($id): void
    {
        // Send welcome email to agency
        $this->send_agency_welcome_email($id);
    }

    /**
     * Send welcome email to agency
     */
    private function send_agency_welcome_email($agency_id): void
    {
        $agency = $this->{$this->model}->get_by_id($agency_id);
        
        if ($agency && !empty($agency->email)) {
            $emailData = [
                'agency_name' => $agency->name,
                'contact_person' => $agency->contact_person,
                'email' => $agency->email,
                'login_url' => site_url('login'),
                'site_name' => $this->config->item('site_name')
            ];

            // Send welcome email
            send_mail('new_agency_account', $agency->email, 'Welcome to ' . $this->config->item('site_name'), $emailData, '', true);
        }
    }

    /**
     * Login as agency
     */
/**
 * Login as agency
 */
public function login_as($id): void
{
    // ============ ADDED CSRF PROTECTION ============
    // Check if this is a POST request (should be triggered by form)
    if ($this->input->server('REQUEST_METHOD') === 'POST') {
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            show_error('Invalid CSRF token', 400);
            return;
        }
    } else {
        // If GET request, redirect to proper form or show error
        show_error('This action requires a POST request', 405);
        return;
    }
    // ============ END CSRF PROTECTION ============
    
    if (!$this->allowEdit) {
        flash_notification(lang('access_denied_description'), 'warning');
        redir($this->pageName);
    }

    $agency = $this->{$this->model}->get_by_id($id);
    if (!$agency || !$agency->login_enabled) {
        flash_notification('Agency login is not enabled or agency not found', 'warning');
        redir($this->pageName);
    }

    // Log action
    Logger::log('Login As Agency', ['agency_id' => $id, 'agency_name' => $agency->name]);

    $login = loginData();
    if (!is_array($login)) $login = [];

    $login['agency'] = [
        'id' => $agency->id,
        'group' => 'agency',
        'name' => $agency->name,
        'email' => $agency->email,
        'contact_person' => $agency->contact_person,
        'redirect' => site_url('agency/dashboard'),
        'enabled' => $agency->enabled,
        'login_enabled' => $agency->login_enabled
    ];

    $this->session->set_userdata('login', $login);
    $this->session->set_userdata('is_logged_in', 1);

    // Update last login
    $this->{$this->model}->update_last_login($agency->id);

    redirect('agency/dashboard');
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
        // No extra data needed for agencies
        return array();
    }

    public function ajax_view($id): void
    {
        $agency = $this->{$this->model}->get_by_id((int)$id);
        $identifier = $agency ? $agency->name : '';

        $data = array(
            'heading'    => lang($this->pageName . '_heading'),
            'identifier' => $identifier,
            'noRows'     => lang($this->pageName . '_no_rows'),
            'agency'     => $agency,
        );

        $html = $this->load->view($this->folder . '/' . $this->pageName . '/ajax_view_single', $data, true);
        $this->output->set_output($html);
    }

    public function valid_website($url)
    {
        if (empty($url)) {
            return TRUE; // Allow empty
        }

        // Auto-prepend https:// if no protocol
        if (!preg_match('~^(http|https)://~i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        // Validate as URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            // Save the corrected URL back to POST
            $_POST['website'] = $url;
            return TRUE;
        }

        $this->form_validation->set_message('valid_website', 'The {field} field must be a valid URL.');
        return FALSE;
    }
}