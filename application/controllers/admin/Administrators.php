<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Administrators extends CRUD_Controller
{
    public $pageName = 'administrators';
    public $group = 'Admin';
    public $view = '';
    public $model = 'Model_administrators';
    public $sorting = array('first_name' => 'ASC', 'last_name' => 'ASC');
    public $singular = 'Administrator';
    public $plural = 'Administrators';
    public $quickManage = true;
    public $identifierField = 'first_name';
    public $hideSubNav = true;
    public $quickManageSize = 3;

    public function __construct()
    {
        parent::__construct();

        // Access check
        if (!function_exists('getLoggedInUserTypeMenu')) {
            $ci = &get_instance();
            $ci->load->helper('profile_helper');
        }
        if (getLoggedInUserTypeMenu() === 'staff') {
            redir('dashboard');
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
            // 'telephone' => array(
            //     'label' => lang('label_telephone'),
            //     'sort' => true,
            // ),
        );

        $this->listActions = array(
            'edit' => array(
                'label'     => lang('label_edit'),
                'url'       => redir($this->pageName . '/edit/{id}', true),
                'icon'      => 'fa-edit',
                'class'     => 'edit-row',
            ),
            'enable' => array(
                'label'    => lang('label_enable'),
                'url'      => redir($this->pageName . '/enable/{id}', true),
                'icon'     => 'fa-eye',
                'class'    => 'enable-row btn-enable',
                'function' => (function ($str, $row) {
                    return ($row->enabled) ? false : ($row->id == loginID() ? '' : $str);
                }),
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => redir($this->pageName . '/disable/{id}', true),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => (function ($str, $row) {
                    return (!$row->enabled) ? false : ($row->id == loginID() ? '' : $str);
                }),
            ),
            'delete' => array(
                'label'     => lang('label_delete'),
                'url'       => redir($this->pageName . '/remove/{id}', true),
                'icon'      => 'fa-trash-o',
                'class'     => 'delete-row btn-delete',
                'function'  => (function ($str, $row) {
                    return $row->id == loginID() ? '' : $str;
                }),
            ),
        );

        $this->filters = array(
            //dropdown filter
            'general' => array(
                'label'     => lang('label_search'),
                'type'      => 'autocomplete',
                'field'     => array(
                    'CONCAT(usr_admins.first_name," ",usr_admins.last_name)',
                    'usr_admins.email',
                    // 'usr_admins.telephone',
                ),
            ),
        );
    }

    public function setup_fields(): void
    {
        $this->formFields = array(
            'main' => array(
                'first_name'                    => 'trim|required|prep_clean_method',
                'last_name'                     => 'trim|required|prep_clean_method',
                // 'telephone'                     => 'trim|strip_tags|numeric',
                'email'                         => 'trim|required|strip_tags|valid_email|callback_is_unique_email',
                'password'                      => 'trim|callback_validate_password',
                'usr_type_id'                   => 'trim|required|strip_tags|numeric',
            ),
            'profile_details' => array(
                'address_line_1'                => 'trim|required|strip_tags|max_length[100]',
                'address_line_2'                => 'trim|strip_tags|max_length[100]',
                'city'                          => 'trim|required|strip_tags|max_length[50]',
                'contact_number'                => 'trim|required|strip_tags|numeric',
                'country'                       => 'trim|required|strip_tags|max_length[50]',
                'date_of_birth'                 => 'trim',
                'date_of_employment'            => 'trim',
                'gender'                        => 'trim|required|strip_tags|max_length[1]',
                'id_number'                     => 'trim|required|strip_tags|numeric',
                'job_role'                      => 'trim|required|strip_tags|prep_clean_method|max_length[50]',
                'linkedin_profile_url'          => 'trim|strip_tags|max_length[256]',
            ),
            'medical_emergency_details' => array(
                'allergies'                     => 'trim|strip_tags',
                'doctor_phone_number'           => 'trim|strip_tags|numeric',
                'family_doctor'                 => 'trim|strip_tags|max_length[50]',
                'mandatory_medication_taken'    => 'trim|strip_tags',
                'medical_aid_member_number'     => 'trim|strip_tags|max_length[50]',
                'medical_aid_name'              => 'trim|strip_tags|max_length[150]',
                'medical_aid_plan'              => 'trim|strip_tags|max_length[50]',
                'medical_history'               => 'trim|strip_tags',
                'medical_problems'              => 'trim|strip_tags',
                'next_of_kin_first_name'        => 'trim|strip_tags|max_length[50]',
                'next_of_kin_last_name'         => 'trim|strip_tags|max_length[50]',
                'next_of_kin_phone_number'      => 'trim|strip_tags|numeric',
                'next_of_kin_relation'          => 'trim|strip_tags|max_length[50]',
            ),
            'multi_selects' => array(
                'access_groups' => array(
                    'validation'  => 'trim',
                    'pivot_table' => 'pivot_admin_access_groups',
                    'main_field'  => 'admin_id',
                    'link_field'  => 'access_group_id',
                ),
            ),
        );

        $this->formLabels = array();

        $this->uploaders = array(
            'profile_pic' => [
                'type'      => 'single_image',
                'table'     => 'usr_admins',
                'sizes'     => [50, 500],
                'ratio'     => (340 / 422),
                'info'      => '<strong>Image Requirements:</strong><br/>File Type: JPG, PNG, WEBP<br/>Min Size: 1080x960',
            ],
        );
    }

    public function index(): void
    {
        $this->breadcrumbs = array(
            array(
                'title' => lang($this->pageName . '_heading'),
                'url'   => redir($this->pageName, true)
            ),
        );
        $this->view = 'listing';

        $this->load->view($this->folder . '/' . 'view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading'           => lang($this->pageName . '_heading'),
            'noRows'            => lang($this->pageName . '_no_rows'),
        ));
        $this->load->view($this->folder . '/' . 'view_footer');
    }

    public function quick_manage_extra($id, $row): array
    {
        $userId = is_bool($id) || !is_object($row) ? 0 : (int)$row->id;

        return array(
            'access_groups_all' => $this->{$this->model}->get_access_groups_all(),
            'access_groups'     => $this->{$this->model}->get_access_groups($userId),
            'more_details'      => $this->{$this->model}->get_additional_profile_details($userId),
            'user_types_all'    => $this->{$this->model}->get_user_types_all(),
        );
    }

    /**
     * Is Unique Email
     *
     * Callback function to check whether or not the email already exists in the database
     *
     * @param string $email
     *
     * @return bool
     */
    public function is_unique_email(string $email): bool
    {
        $id = $this->input->post('id');

        $this->form_validation->set_message('is_unique_email', lang('email_exists'));
        $result = $this->{$this->model}->is_unique_email($email, $id);

        return $result;
    }

    /**
     * Send Password Mail
     *
     * Sends a mail to the user with a link to the password reset page.
     */
    private function send_password_mail($id): void
    {
        $emailData = $this->{$this->model}->get_reset_email_data($id);

        if ($emailData !== false) {
            $tokenData = $this->generate_reset_token('admin', $emailData['email']);

            //Set email parameters
            $emailData['link'] = site_url() . 'login/reset-password/' . $tokenData['token'] . '/' . md5($emailData['email']);
            $emailData['createDate'] = date("j F Y", strtotime($tokenData['created']));
            $emailData['createTime'] = date("H:i", strtotime($tokenData['created']));
            $emailData['site_name'] = $this->config->item('site_name');

            send_mail('new_admin_account', $emailData['email'], email_subject('subject_new_admin'), $emailData, '', true);
        } else {
            $message = '
                Unable to get email data for new user user.
                User UserID: ' . $id . '
            ';
            Anomalies::log(trim($message));
        }
    }

    /**
     * Generate Reset Token
     *
     * Generates a reset token.
     *
     * @param string $group
     * @param string $email
     * @param int $newUser
     *
     * @return array
     */
    private function generate_reset_token(string $group, string $email, int $newUser = 1): array
    {
        $token = random_string('alnum', 32);

        $date = date("Y-m-d H:i:s");
        $this->db->set('email', $email);
        $this->db->set('group', $group);
        $this->db->set('token', $token);
        $this->db->set('new_user', $newUser);

        //Because the created_at time needs to be return, it's manually set.
        $this->db->set('created_at', $date);
        $result = $this->db->insert('sys_password_reset_tokens');

        $data['token'] = $token;
        $data['created'] = $date;

        if ($result) {
            return $data;
        } else {
            $message = 'Failed to insert reset token';
            Anomalies::log(trim($message), $this->db->last_query());
        }
        return [];
    }

    public function validate_password(): bool
    {
        $password     = $this->input->post('password');
        $confirm    = $this->input->post('confirm_password');

        //Don't validate if password is blank
        if (empty($password)) {
            return true;
        }

        //Check if passwords matches
        if ($password != $confirm) {
            $this->form_validation->set_message('validate_password', lang('validation_passwords_mismatch'));
            return false;
        }

        //Check if password is strong enough
        if (!is_password_strong($password, 8, false, false, false, false)) {
            $this->form_validation->set_message('validate_password', lang('validation_password_weak'));
            return false;
        }

        return true;
    }

    /**
     * @return array<string,int>
     */
    public function create_extra_params(): array
    {
        $params['receive_notifications'] = $this->input->post('receive_notifications') ? 1 : 0;

        return $params;
    }

    public function update_extra_params($id): array
    {
        $params = array();
        if ($this->input->post('password')) {
            $newPassword = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
            $params['password'] = $newPassword;
        } else {
            //Unset password field
            unset($this->formFields['main']['password']);
        }

        $params['receive_notifications'] = $this->input->post('receive_notifications') ? 1 : 0;

        return $params;
    }

    public function create_success_extra($id): void
    {
        //Send out email notification
        $this->send_password_mail($id);
    }

    public function login_as($id): void
    {
        $row = $this->{$this->model}->get_by_id($id);
        if (!$row) {
            flash_notification(lang('access_denied_description'), 'warning');
            redir($this->pageName);
        }

        //Log action
        Logger::log('Login As Administrator', ['id' => $id]);

        $loginGroups    = $this->config->item('login_groups');
        $group          = 'admin';
        $defaultUrl     = $loginGroups[$group]['default_url'];
        $login          = loginData();

        if (!is_array($login)) $login = [];

        $login[$group] = array(
            'id'        => $row->id,
            'group'     => $group,
            'name'      => (!empty($row->name)) ? $row->name : $row->first_name . ' ' . $row->last_name,
            'redirect'  => site_url() . $defaultUrl,
            'enabled'   => $row->enabled,
        );

        if (!empty($loginGroups[$group]['session_fields'])) {
            foreach ($loginGroups[$group]['session_fields'] as $field) {
                $login[$group][$field] = !empty($row->{$field}) ? $row->{$field} : '';
            }
        }

        $this->session->set_userdata('login', $login);
        $this->session->set_userdata('is_logged_in', 1);

        redirect(site_url() . $defaultUrl);
    }

    public function update($id): void
    {
        parent::update($id);
    }
}
