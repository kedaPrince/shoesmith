<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Agency_Staff extends CRUD_Controller
{
    public $pageName = 'agency_staff';
    public $group = 'Agency';
    public $folder = 'agency';
    public $view = '';
    public $model = 'Model_agency_staff';
    public $sorting = array('first_name' => 'ASC', 'last_name' => 'ASC');
    public $singular = 'Agency Staff Member';
    public $plural = 'Agency Staff Members';
    public $quickManage = true;
    public $identifierField = 'first_name';
    public $hideSubNav = true;
    public $quickManageSize = 3;

    public function __construct()
    {
        parent::__construct();

        // Access check - allow both agency and admin access
        if (!function_exists('getLoggedInUserTypeMenu')) {
            $ci = &get_instance();
            $ci->load->helper('profile_helper');
        }
        
        $userType = getLoggedInUserTypeMenu();
        if (!in_array($userType, ['admin', 'agency'])) {
            redir('dashboard');
        }

        $this->load->model($this->folder . '/' . $this->model);
        
        $this->setup_listing();
        $this->setup_fields();
        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }

private function setup_listing(): void
{
    // Get user's agency ID to filter options
    $user_agency_id = $this->get_user_agency_id();
    
    // Get agency options for the filter - filtered by user's agency
    $agency_options = [];
    $agencies_query = $this->db->select('id, name')
                              ->where('enabled', 1)
                              ->where('removed', 0);
    
    if (!empty($user_agency_id)) {
        $agencies_query->where('id', $user_agency_id);
    }
    
    $agencies_query->order_by('name');
    $agencies_result = $agencies_query->get('agencies');
    
    if ($agencies_result->num_rows() > 0) {
        $agency_options = $agencies_result->result_array();
    }

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
            'sort' => true,
            'field' => 'agencies.name'
        ),
        'job_role' => array(
            'label' => lang('label_job_role'),
            'sort' => true,
        ),
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
                    return !$row->enabled ? $str : false; // Show only if disabled
                }),
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => redir($this->pageName . '/disable/{id}', true),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => (function ($str, $row) {
                    return $row->enabled ? $str : false; // Show only if enabled
                }),
            ),
            'delete' => array(
                'label'     => lang('label_delete'),
                'url'       => redir($this->pageName . '/remove/{id}', true),
                'icon'      => 'fa-trash-o',
                'class'     => 'delete-row btn-delete',
            ),
        );

        $this->filters = array(
            'general' => array(
                'label'     => lang('label_search'),
                'type'      => 'autocomplete',
                'field'     => array(
                    'CONCAT(agency_staff.first_name," ",agency_staff.last_name)',
                    'agency_staff.email',
                    'agency_staff.job_role',
                ),
            ),
            'agency' => array(
                'label'     => lang('label_agency'),
                'type'      => 'dropdown',
                'field'     => 'agency_staff.agency_id',
                'options'   => $agency_options,
                'id_field'  => 'id',
                'name_field'=> 'name',
            ),
        );
    }

    public function setup_fields(): void
    {
        $this->formFields = array(
            'main' => array(
                'first_name'                    => 'trim|required|prep_clean_method',
                'last_name'                     => 'trim|required|prep_clean_method',
                'email'                         => 'trim|required|strip_tags|valid_email|callback_is_unique_email',
                'password'                      => 'trim|callback_validate_password',
                'usr_type_id'                   => 'trim|required|strip_tags|numeric',
                'agency_id'                     => 'trim|required|strip_tags|numeric',
            ),
            'profile_details' => array(
                'job_role'                      => 'trim|required|strip_tags|prep_clean_method|max_length[50]',
                'id_number'                     => 'trim|required|strip_tags|numeric',
                'contact_number'                => 'trim|required|strip_tags|numeric',
                'gender'                        => 'trim|required|strip_tags|max_length[1]',
                'linkedin_profile_url'          => 'trim|strip_tags|max_length[256]',
                'date_of_birth'                 => 'trim',
                'date_of_employment'            => 'trim',
                'address_line_1'                => 'trim|required|strip_tags|max_length[100]',
                'address_line_2'                => 'trim|strip_tags|max_length[100]',
                'city'                          => 'trim|required|strip_tags|max_length[50]',
                'country'                       => 'trim|required|strip_tags|max_length[50]',
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
                    'validation'  => 'trim|required',
                    'pivot_table' => 'pivot_agency_staff_access_groups',
                    'main_field'  => 'agency_staff_id',
                    'link_field'  => 'access_group_id',
                ),
            ),
        );

        $this->formLabels = array();

        $this->uploaders = array(
            'profile_pic' => [
                'type'      => 'single_image',
                'table'     => 'agency_staff',
                'sizes'     => [50, 500],
                'ratio'     => (340 / 422),
                'info'      => '<strong>Image Requirements:</strong><br/>File Type: JPG, PNG, WEBP<br/>Min Size: 1080x960',
            ],
        );
    }

    /**
     * Override the main data fetching method used by the listing
     */
    public function _get_data($limit = null, $offset = null, $sort_by = null, $sort_order = null)
    {
        $user_agency_id = $this->get_user_agency_id();
        
        if (!empty($user_agency_id)) {
            log_message('debug', 'Applying agency filter for agency_staff in _get_data: ' . $user_agency_id);
            $this->db->where('agency_staff.agency_id', $user_agency_id);
        } else {
            log_message('debug', 'No agency_id found for filtering agency_staff');
        }
        
        return parent::_get_data($limit, $offset, $sort_by, $sort_order);
    }

    public function index(): void
    {
        $user_agency_id = $this->get_user_agency_id();
        log_message('debug', 'Current user agency_id for agency_staff: ' . $user_agency_id);
        
        // Apply agency filter directly before the listing loads
        if (!empty($user_agency_id)) {
            $this->db->where('agency_staff.agency_id', $user_agency_id);
            log_message('debug', 'Applied agency filter in index method for agency_staff: ' . $user_agency_id);
        }
        
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

    /**
     * Override the pager fetch batch to ensure agency filtering
     */
   /**
 * Override the pager fetch batch to ensure agency filtering
 */
public function ajax_pager_fetch_batch($batch = 1, $section = "", $template = "listing")
{
    $this->page = $batch;
    
    $user_agency_id = $this->get_user_agency_id();
    
    try {
        // Apply agency filter before calling get_all
        if (!empty($user_agency_id)) {
            $this->db->where('agency_staff.agency_id', $user_agency_id);
            log_message('debug', 'Applied agency filter in ajax_pager_fetch_batch: ' . $user_agency_id);
        }
        
        $query = $this->{$this->model}->get_all($section);
    }
    catch(Exception $e) {
        echo $e->getMessage();
        exit();
    }

    $amount = $this->{$this->model}->get_count();
    
    $html = $this->load->view('cms/crud/ajax_' . $template . '_rows', array(
        'query' => $query,
        'batch' => $batch,
        'amount' => $amount
    ), TRUE);

    $this->output->set_output($html);
}

    public function quick_manage_extra($id, $row): array
    {
        $userId = is_bool($id) || !is_object($row) ? 0 : (int)$row->id;

        // Get user's agency ID to filter agency options
        $user_agency_id = $this->get_user_agency_id();
        
        $input_data = array(
            'access_groups_all' => $this->{$this->model}->get_access_groups_all(),
            'access_groups'     => $this->{$this->model}->get_access_groups($userId),
            'more_details'      => $this->{$this->model}->get_additional_profile_details($userId),
            'user_types_all'    => $this->{$this->model}->get_user_types_all(),
            'agency_options'    => $this->{$this->model}->get_agency_options($user_agency_id),
            'user_agency_id'    => $user_agency_id,
        );

        return $input_data;
    }

    /**
     * Get the logged-in user's agency ID - Works for both agency staff and recruiters
     */
   /**
 * Get the logged-in user's agency ID - Works for both agency staff and recruiters
 */
/**
 * Get the logged-in user's agency ID
 */
private function get_user_agency_id()
{
    // Get the login data from session
    $login_data = $this->session->userdata('login');
    
    // Check for agency login - when logged in as agency, the ID is the agency ID
    if (!empty($login_data['agency'])) {
        $agency_user = $login_data['agency'];
        
        if (!empty($agency_user['id'])) {
            // When logged in as agency, the ID is the agency ID
            $agency_id = $agency_user['id'];
            log_message('debug', 'Agency_Staff - Using agency ID from session: ' . $agency_id);
            return $agency_id;
        }
    }
    
    log_message('debug', 'Agency_Staff - No agency ID found in session');
    return null;
}

    /**
     * Is Unique Email
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
     */
    private function send_password_mail($id): void
    {
        $emailData = $this->{$this->model}->get_reset_email_data($id);

        if ($emailData !== false) {
            $tokenData = $this->generate_reset_token('agency', $emailData['email']);

            //Set email parameters
            $emailData['link'] = site_url() . 'login/reset-password/' . $tokenData['token'] . '/' . md5($emailData['email']);
            $emailData['createDate'] = date("j F Y", strtotime($tokenData['created']));
            $emailData['createTime'] = date("H:i", strtotime($tokenData['created']));
            $emailData['site_name'] = $this->config->item('site_name');

            send_mail('new_agency_staff_account', $emailData['email'], email_subject('subject_new_agency_staff'), $emailData, '', true);
        } else {
            $message = '
                Unable to get email data for new agency staff.
                Agency Staff ID: ' . $id . '
            ';
            Anomalies::log(trim($message));
        }
    }

    /**
     * Generate Reset Token
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
        $params['is_approved'] = 1; // Auto-approve agency staff

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
        // Process usr_medical_emergency_details
        $medicalData = [
            'allergies'                     => $this->input->post('allergies'),
            'doctor_phone_number'           => $this->input->post('doctor_phone_number'),
            'family_doctor'                 => $this->input->post('family_doctor'),
            'mandatory_medication_taken'    => $this->input->post('mandatory_medication_taken'),
            'medical_aid_member_number'     => $this->input->post('medical_aid_member_number'),
            'medical_aid_name'              => $this->input->post('medical_aid_name'),
            'medical_aid_plan'              => $this->input->post('medical_aid_plan'),
            'medical_history'               => $this->input->post('medical_history'),
            'medical_problems'              => $this->input->post('medical_problems'),
            'next_of_kin_first_name'        => $this->input->post('next_of_kin_first_name'),
            'next_of_kin_last_name'         => $this->input->post('next_of_kin_last_name'),
            'next_of_kin_phone_number'      => $this->input->post('next_of_kin_phone_number'),
            'next_of_kin_relation'          => $this->input->post('next_of_kin_relation'),
            'created_at'                    => date('Y-m-d H:i:s'),
            'updated_at'                    => date('Y-m-d H:i:s'),
            'enabled'                       => 1,
            'removed'                       => 0,
        ];

        // Insert into usr_medical_emergency_details
        $this->db->insert('usr_medical_emergency_details', $medicalData);
        $medicalId = $this->db->insert_id();

        if ($medicalId) {
            // Update agency_staff with profile data directly in the table
            $profileData = [
                'usr_profile_details_id'        => $medicalId, // Using medical ID as profile details ID
                'job_role'                      => $this->input->post('job_role'),
                'id_number'                     => $this->input->post('id_number'),
                'contact_number'                => $this->input->post('contact_number'),
                'gender'                        => $this->input->post('gender'),
                'linkedin_profile_url'          => $this->input->post('linkedin_profile_url'),
                'date_of_birth'                 => $this->input->post('date_of_birth'),
                'date_of_employment'            => $this->input->post('date_of_employment'),
                'address_line_1'                => $this->input->post('address_line_1'),
                'address_line_2'                => $this->input->post('address_line_2'),
                'city'                          => $this->input->post('city'),
                'country'                       => $this->input->post('country'),
                'updated_at'                    => date('Y-m-d H:i:s'),
            ];

            // Update the agency_staff record with profile data
            $this->db->where('id', $id);
            $this->db->update('agency_staff', $profileData);

            // Also update the name field by concatenating first and last name
            $this->db->where('id', $id);
            $this->db->update('agency_staff', [
                'name' => $this->input->post('first_name') . ' ' . $this->input->post('last_name')
            ]);
        } else {
            Anomalies::log("Failed to create usr_medical_emergency_details for agency staff ID {$id}", $this->db->last_query());
        }

        // Send password reset email
        $this->send_password_mail($id);
    }

    public function update_success_extra($id): void
    {
        // Update profile data in agency_staff table
        $profileData = [
            'job_role'                      => $this->input->post('job_role'),
            'id_number'                     => $this->input->post('id_number'),
            'contact_number'                => $this->input->post('contact_number'),
            'gender'                        => $this->input->post('gender'),
            'linkedin_profile_url'          => $this->input->post('linkedin_profile_url'),
            'date_of_birth'                 => $this->input->post('date_of_birth'),
            'date_of_employment'            => $this->input->post('date_of_employment'),
            'address_line_1'                => $this->input->post('address_line_1'),
            'address_line_2'                => $this->input->post('address_line_2'),
            'city'                          => $this->input->post('city'),
            'country'                       => $this->input->post('country'),
            'updated_at'                    => date('Y-m-d H:i:s'),
        ];

        // Update the agency_staff record with profile data
        $this->db->where('id', $id);
        $this->db->update('agency_staff', $profileData);

        // Also update the name field
        $this->db->where('id', $id);
        $this->db->update('agency_staff', [
            'name' => $this->input->post('first_name') . ' ' . $this->input->post('last_name')
        ]);
    }

    public function login_as($id): void
    {
        $row = $this->{$this->model}->get_by_id($id);
        if (!$row) {
            flash_notification(lang('access_denied_description'), 'warning');
            redir($this->pageName);
        }

        //Log action
        Logger::log('Login As Agency Staff', ['id' => $id]);

        $loginGroups    = $this->config->item('login_groups');
        $group          = 'agency';
        $defaultUrl     = $loginGroups[$group]['default_url'];
        $login          = loginData();

        if (!is_array($login)) $login = [];

        $login[$group] = array(
            'id'        => $row->id,
            'group'     => $group,
            'name'      => (!empty($row->name)) ? $row->name : $row->first_name . ' ' . $row->last_name,
            'redirect'  => site_url() . $defaultUrl,
            'enabled'   => $row->enabled,
            'agency_id' => $row->agency_id,
            'email'     => $row->email,
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

    /**
     * Build parameters for create/update
     */
    public function build_params($extra = array(), $group = 'main')
    {
        $params = parent::build_params($extra, $group);
        
        // Set default values for checkboxes
        $checkboxFields = ['receive_notifications'];
        
        foreach ($checkboxFields as $field) {
            if (!isset($params[$field])) {
                $params[$field] = 0;
            }
        }
        
        return $params;
    }

    /**
     * Debug method to verify agency filtering
     */
    public function debug_agency_staff_filter()
    {
        $user_agency_id = $this->get_user_agency_id();
        echo "<h3>Debug Agency Staff Filter</h3>";
        echo "<p>User Agency ID from session: " . ($user_agency_id ?? 'NULL') . "</p>";
        
        // Check session data
        echo "<h4>Session Login Data:</h4>";
        echo "<pre>";
        print_r($this->session->userdata('login'));
        echo "</pre>";
        
        // Test 1: Direct database query without filter
        echo "<h4>Direct Database Query (No Filter):</h4>";
        $this->db->select('id, first_name, last_name, agency_id');
        $this->db->from('agency_staff');
        $this->db->where('removed', 0);
        $all_staff = $this->db->get()->result();
        echo "<p>All staff in database: " . count($all_staff) . "</p>";
        foreach ($all_staff as $staff) {
            echo "Staff ID: {$staff->id}, Name: {$staff->first_name} {$staff->last_name}, Agency ID: {$staff->agency_id}<br>";
        }
        
        // Test 2: Query with agency filter
        echo "<h4>Query with Agency Filter:</h4>";
        $this->db->select('id, first_name, last_name, agency_id');
        $this->db->from('agency_staff');
        $this->db->where('agency_id', $user_agency_id);
        $this->db->where('removed', 0);
        $filtered_staff = $this->db->get()->result();
        echo "<p>Filtered staff for agency {$user_agency_id}: " . count($filtered_staff) . "</p>";
        foreach ($filtered_staff as $staff) {
            echo "Staff ID: {$staff->id}, Name: {$staff->first_name} {$staff->last_name}, Agency ID: {$staff->agency_id}<br>";
        }
        
        // Test 3: What the model returns
        echo "<h4>Model get_all() Result:</h4>";
        $model_result = $this->{$this->model}->get_all();
        echo "<p>Model returned: " . $model_result->num_rows() . " rows</p>";
        foreach ($model_result->result() as $staff) {
            echo "Staff ID: {$staff->id}, Name: {$staff->first_name} {$staff->last_name}, Agency ID: {$staff->agency_id}<br>";
        }
    }


    
}