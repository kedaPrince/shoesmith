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
    // After parent::__construct() but before any lang() calls
log_message('debug', 'Testing lang() function...');
try {
    $test_lang = lang('label_first_name');
    log_message('debug', 'lang() function works: ' . $test_lang);
} catch (Exception $e) {
    log_message('error', 'lang() function error: ' . $e->getMessage());
}
    
    // ENABLE ERROR DISPLAY FOR DEBUGGING (remove in production)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // LOG ENTRY POINT
    log_message('debug', '=== AGENCY_STAFF CONSTRUCTOR START ===');
    log_message('debug', 'Request URI: ' . uri_string());
    log_message('debug', 'Session ID: ' . session_id());
    log_message('debug', 'Session status: ' . session_status());
    
    // Check session library
    if (!isset($this->session)) {
        log_message('error', 'Session library not loaded!');
        $this->load->library('session');
    }
    
    // Log session data
    $session_data = $this->session->all_userdata();
    log_message('debug', 'Session data: ' . print_r($session_data, true));
    
    // Log cookies
    log_message('debug', 'Cookies: ' . print_r($_COOKIE, true));
    
    // Check access helper
    log_message('debug', 'Loading profile_helper...');
    if (!function_exists('getLoggedInUserTypeMenu')) {
        $this->load->helper('profile_helper');
        log_message('debug', 'profile_helper loaded');
    }
    
    try {
        $userType = getLoggedInUserTypeMenu();
        log_message('debug', 'User type: ' . $userType);
        
        if (!in_array($userType, ['admin', 'agency'])) {
            log_message('warning', 'User not authorized. Type: ' . $userType);
            redir('dashboard');
        }
    } catch (Exception $e) {
        log_message('error', 'Error in getLoggedInUserTypeMenu: ' . $e->getMessage());
    }
    
    // Load model with logging
    $model_name = $this->folder . '/' . $this->model;
    log_message('debug', 'Loading model: ' . $model_name);
    
    try {
        $this->load->model($model_name);
        log_message('debug', 'Model loaded successfully');
    } catch (Exception $e) {
        log_message('error', 'Failed to load model: ' . $e->getMessage());
        show_error('Model loading failed: ' . $e->getMessage());
    }
    
    log_message('debug', 'Setting up listing...');
    $this->setup_listing();
    
    log_message('debug', 'Setting up fields...');
    $this->setup_fields();
    
    $this->zone = array(
        'title' => lang($this->pageName . '_heading'),
        'url' => redir($this->pageName, true),
    );
    
    log_message('debug', '=== AGENCY_STAFF CONSTRUCTOR END ===');
}

private function setup_listing(): void
{
    log_message('debug', '=== SETUP_LISTING START ===');
    
    // Get user's agency ID to filter options
    $user_agency_id = $this->get_user_agency_id();
    log_message('debug', 'User agency ID in setup_listing: ' . ($user_agency_id ?: 'NULL'));
    
    // Get agency options for the filter - filtered by user's agency
    $agency_options = [];
    log_message('debug', 'Querying agencies...');
    
    try {
        $agencies_query = $this->db->select('id, name')
                                  ->where('enabled', 1)
                                  ->where('removed', 0);
        
        if (!empty($user_agency_id)) {
            $agencies_query->where('id', $user_agency_id);
            log_message('debug', 'Filtering agencies by ID: ' . $user_agency_id);
        }
        
        $agencies_query->order_by('name');
        $agencies_result = $agencies_query->get('agencies');
        log_message('debug', 'Agencies query executed. Rows: ' . $agencies_result->num_rows());
        
        if ($agencies_result->num_rows() > 0) {
            $agency_options = $agencies_result->result_array();
            log_message('debug', 'Agency options loaded: ' . count($agency_options));
        } else {
            log_message('warning', 'No agencies found for user agency ID: ' . $user_agency_id);
        }
    } catch (Exception $e) {
        log_message('error', 'Error querying agencies: ' . $e->getMessage());
        log_message('error', 'Query: ' . $this->db->last_query());
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
log_message('debug', 'List fields set');
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
 log_message('debug', 'List actions set');
        $this->filters = array(
            'general' => array(
                'label'     => lang('label_search'),
                'type'      => 'autocomplete',
                'field'     => array(
                    'CONCAT(first_name," ",last_name)',
                    'email',
                    'job_role',
                ),
            ),
            'agency' => array(
                'label'     => lang('label_agency'),
                'type'      => 'dropdown',
                'field'     => 'agency_id',
                'options'   => $agency_options,
                'id_field'  => 'id',
                'name_field'=> 'name',
            ),
        );
        log_message('debug', 'Filters set. Agency options count: ' . count($agency_options));
    log_message('debug', '=== SETUP_LISTING END ===');
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



public function index(): void
{
    // AGENCY FILTERING REMOVED - Model handles it
    
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
        // ============ ADDED CSRF PROTECTION ============
        // Check if CSRF token is provided (could be in GET or POST)
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->get($csrf_name) ?: $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            // For GET requests, show a confirmation form with CSRF token
            if ($this->input->server('REQUEST_METHOD') === 'GET') {
                $row = $this->{$this->model}->get_by_id($id);
                if (!$row) {
                    flash_notification(lang('access_denied_description'), 'warning');
                    redir($this->pageName);
                }
                
                $this->load->view($this->folder . '/' . 'view_header');
                echo '<div class="container mt-5">';
                echo '<div class="row justify-content-center">';
                echo '<div class="col-md-6">';
                echo '<div class="card">';
                echo '<div class="card-header">';
                echo '<h4>Confirm Login As Agency Staff</h4>';
                echo '</div>';
                echo '<div class="card-body">';
                echo '<p>Are you sure you want to log in as <strong>' . htmlspecialchars($row->first_name . ' ' . $row->last_name) . '</strong>?</p>';
                echo '<p class="text-muted">This will log you out of your current session and log in as the selected staff member.</p>';
                echo '<form method="POST" action="' . site_url('agency/agency_staff/login_as/' . $id) . '">';
                echo '<input type="hidden" name="' . $this->security->get_csrf_token_name() . '" value="' . $this->security->get_csrf_hash() . '">';
                echo '<button type="submit" class="btn btn-danger">Yes, Login As</button>';
                echo ' <a href="' . site_url('agency/agency_staff') . '" class="btn btn-secondary">Cancel</a>';
                echo '</form>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                $this->load->view($this->folder . '/' . 'view_footer');
                return;
            } else {
                if (is_ajax()) {
                    ajax_return([
                        'success' => false,
                        'message' => 'Invalid security token',
                        'csrf' => $this->security->get_csrf_hash()
                    ]);
                } else {
                    $this->session->set_flashdata('error', 'Invalid security token');
                    redirect(previous_url());
                }
                return;
            }
        }
        // ============ END CSRF PROTECTION ============
        
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


  // ============================================
    // 🔒 ADD THESE SECURITY CHECKS TO ALL METHODS
    // ============================================


/**
 * 🔒 SECURITY FIX: Override parent's _get_data to add agency filtering
 * This is called by listing and AJAX methods
 */
public function _get_data($limit = null, $offset = null, $sort_by = null, $sort_order = null)
{
    // Let parent handle with our secured model
    return parent::_get_data($limit, $offset, $sort_by, $sort_order);
}

/**
 * 🔒 SECURITY FIX: Secure the AJAX pager method
 */
public function ajax_pager_fetch_batch($batch = 1, $section = "", $template = "listing")
{
    // Parent will use our secured get_all() method
    parent::ajax_pager_fetch_batch($batch, $section, $template);
}

/**
 * 🔒 SECURITY FIX: Override quick_manage method
 */
public function quick_manage($id = false)
{
    if ($id && !$this->check_staff_access($id)) {
        $this->access_denied();
        return;
    }
    
    parent::quick_manage($id);
}

/**
 * 🔒 Check if current user can access this staff member - FIXED VERSION
 */
private function check_staff_access($staff_id)
{
    // Get user's agency ID
    $user_agency_id = $this->get_user_agency_id();
    
    if (empty($user_agency_id)) {
        // If no agency ID, check if admin
        $user_type = getLoggedInUserTypeMenu();
        if ($user_type === 'admin') {
            return true; // Admins can access everything
        }
        return false; // No agency, not admin = no access
    }
    
    // DIRECT DATABASE CHECK - Don't rely on model method
    $this->db->select('1');
    $this->db->from('agency_staff');
    $this->db->where('id', $staff_id);
    $this->db->where('agency_id', $user_agency_id);
    $this->db->where('removed', 0);
    
    $result = $this->db->get()->row();
    
    if (!$result) {
        // Log the violation
        log_message('error', 'ACCESS VIOLATION: Agency ' . $user_agency_id . 
                   ' tried to access staff ' . $staff_id);
        return false;
    }
    
    return true;
}

/**
 * 🔒 Universal access denied handler
 */
private function access_denied()
{
    if (is_ajax()) {
        ajax_return([
            'success' => false,
            'error' => 'Access denied to this staff member'
        ]);
    } else {
        show_error('Access denied', 403);
    }
    exit; // Stop execution
}

/**
 * 🔒 SECURITY FIX: Handle all CRUD operations with access control
 * This is a universal pre-check for any staff operation
 */
private function check_crud_access($id = null)
{
    if ($id && !$this->check_staff_access($id)) {
        $this->access_denied();
        return false;
    }
    return true;
}

// Override any other parent methods that might exist
public function ajax_get_staff($id)
{
    if (!$this->check_crud_access($id)) {
        return;
    }
    
    // If parent has this method, call it
    if (method_exists(get_parent_class($this), 'ajax_get_staff')) {
        parent::ajax_get_staff($id);
    } else {
        // Return minimal safe data or error
        if (is_ajax()) {
            ajax_return([
                'success' => false,
                'error' => 'Method not available'
            ]);
        }
    }
}
public function edit($id): void
{
    // 🔒 Simple, direct check
    if (!$this->check_staff_access($id)) {
        $this->access_denied();
    }
    
    parent::edit($id);
}

    /**
     * 🔒 Override parent view method with agency check
     */
    public function view($id): void
    {
        // Check agency access
        if (!$this->check_staff_access($id)) {
            if (is_ajax()) {
                ajax_return([
                    'success' => false,
                    'error' => 'Access denied to this staff member'
                ]);
            } else {
                flash_notification('Access denied to this staff member', 'error');
                redirect($this->pageName);
            }
            return;
        }
        
        // If parent doesn't have view() method, handle it here
        if (method_exists(get_parent_class($this), 'view')) {
            parent::view($id);
        } else {
            show_404();
        }
    }

    /**
     * 🔒 Override parent enable method with agency check
     */
    public function enable($id): void
    {
        // Check agency access
        if (!$this->check_staff_access($id)) {
            if (is_ajax()) {
                ajax_return([
                    'success' => false,
                    'error' => 'Access denied to this staff member'
                ]);
            } else {
                show_error('Access denied', 403);
            }
            return;
        }
        
        parent::enable($id);
    }

    /**
     * 🔒 Override parent disable method with agency check
     */
    public function disable($id): void
    {
        // Check agency access
        if (!$this->check_staff_access($id)) {
            if (is_ajax()) {
                ajax_return([
                    'success' => false,
                    'error' => 'Access denied to this staff member'
                ]);
            } else {
                show_error('Access denied', 403);
            }
            return;
        }
        
        parent::disable($id);
    }

    /**
     * 🔒 Override parent remove method with agency check
     */
    public function remove($id): void
    {
        // Check agency access
        if (!$this->check_staff_access($id)) {
            if (is_ajax()) {
                ajax_return([
                    'success' => false,
                    'error' => 'Access denied to this staff member'
                ]);
            } else {
                show_error('Access denied', 403);
            }
            return;
        }
        
        parent::remove($id);
    }

    /**
     * 🔒 Override parent update method with agency check
     */
    public function update($id): void
    {
        // Check agency access
        if (!$this->check_staff_access($id)) {
            if (is_ajax()) {
                ajax_return([
                    'success' => false,
                    'error' => 'Access denied to this staff member'
                ]);
            } else {
                flash_notification('Access denied to this staff member', 'error');
                redirect($this->pageName);
            }
            return;
        }
        
        parent::update($id);
    }

    /**
     * 🔒 Override parent create method (if needed)
     */
/**
 * Handle additional profile data
 */
private function handle_additional_data($staff_id, $form_data = [])
{
    try {
        // If form_data is empty, try to get from POST
        if (empty($form_data) && !empty($_POST)) {
            $form_data = $_POST;
        }
        
        // Profile details
        $profile_data = [
            'job_role'          => isset($form_data['job_role']) ? trim($form_data['job_role']) : '',
            'id_number'         => isset($form_data['id_number']) ? trim($form_data['id_number']) : '',
            'contact_number'    => isset($form_data['contact_number']) ? trim($form_data['contact_number']) : '',
            'gender'            => isset($form_data['gender']) ? trim($form_data['gender']) : '',
            'linkedin_profile_url' => isset($form_data['linkedin_profile_url']) ? trim($form_data['linkedin_profile_url']) : '',
            'date_of_birth'     => isset($form_data['date_of_birth']) ? trim($form_data['date_of_birth']) : NULL,
            'date_of_employment' => isset($form_data['date_of_employment']) ? trim($form_data['date_of_employment']) : NULL,
            'address_line_1'    => isset($form_data['address_line_1']) ? trim($form_data['address_line_1']) : '',
            'address_line_2'    => isset($form_data['address_line_2']) ? trim($form_data['address_line_2']) : '',
            'city'              => isset($form_data['city']) ? trim($form_data['city']) : '',
            'country'           => isset($form_data['country']) ? trim($form_data['country']) : '',
            'updated_at'        => date('Y-m-d H:i:s')
        ];
        
        // Update agency_staff with profile data
        $this->db->where('id', $staff_id);
        $this->db->update('agency_staff', $profile_data);
        
        // Update name field
        $first_name = isset($form_data['first_name']) ? trim($form_data['first_name']) : '';
        $last_name = isset($form_data['last_name']) ? trim($form_data['last_name']) : '';
        if ($first_name || $last_name) {
            $this->db->where('id', $staff_id);
            $this->db->update('agency_staff', [
                'name' => $first_name . ' ' . $last_name
            ]);
        }
        
        // Handle access groups if provided
        if (isset($form_data['access_groups'])) {
            $access_groups = $form_data['access_groups'];
            if (!is_array($access_groups)) {
                $access_groups = [$access_groups];
            }
            
            foreach ($access_groups as $group_id) {
                if ($group_id) {
                    $this->db->insert('pivot_agency_staff_access_groups', [
                        'agency_staff_id' => $staff_id,
                        'access_group_id' => $group_id
                    ]);
                }
            }
        }
        
        log_message('debug', 'Additional data saved for staff ID: ' . $staff_id);
        
    } catch (Exception $e) {
        log_message('error', 'Additional data error: ' . $e->getMessage());
        // Don't fail the whole create if additional data fails
    }
}

/**
 * Handle profile picture upload
 */
private function handle_profile_pic_upload($staff_id)
{
    $config['upload_path'] = './uploads/agency_staff/';
    $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
    $config['max_size'] = 5120; // 5MB
    $config['encrypt_name'] = true;
    
    // Create directory if it doesn't exist
    if (!is_dir($config['upload_path'])) {
        mkdir($config['upload_path'], 0755, true);
    }
    
    $this->load->library('upload', $config);
    
    if (!$this->upload->do_upload('profile_pic')) {
        $error = $this->upload->display_errors();
        log_message('error', 'Profile pic upload failed: ' . $error);
        throw new Exception('Profile picture upload failed: ' . $error);
    }
    
    $upload_data = $this->upload->data();
    log_message('debug', 'File uploaded: ' . $upload_data['file_name']);
    
    // Update staff record with image filename
    $this->db->where('id', $staff_id);
    $this->db->update('agency_staff', [
        'profile_pic' => $upload_data['file_name']
    ]);
}


    /**
     * 🔒 Get the logged-in user's agency ID
     */
    private function get_user_agency_id()
    {
        $login_data = $this->session->userdata('login');
        
        if (!empty($login_data['agency'])) {
            $agency_user = $login_data['agency'];
            
            if (!empty($agency_user['id'])) {
                return $agency_user['id'];
            }
        }
        
        return null;
    }

/**
     * 🔒 Override ajax_quick_manage method with agency check
     */
    public function ajax_quick_manage($id = FALSE)
    {
        if ($id && !$this->check_staff_access($id)) {
            ajax_return([
                'success' => false,
                'error' => 'Access denied to this staff member'
            ]);
            return;
        }
        
        parent::ajax_quick_manage($id);
    }

    /**
 * Debug method to test access
 */
public function test_access_control($staff_id)
{
    echo "<h2>Access Control Test</h2>";
    
    // Test 1: Get current agency
    $user_agency_id = $this->get_user_agency_id();
    echo "Current User Agency ID: <strong>" . ($user_agency_id ?: 'NULL') . "</strong><br>";
    
    // Test 2: Check access
    $can_access = $this->check_staff_access($staff_id);
    echo "Can access staff $staff_id: <strong>" . ($can_access ? 'YES' : 'NO') . "</strong><br>";
    
    // Test 3: Direct DB check
    $this->db->select('id, agency_id, first_name, last_name');
    $this->db->from('agency_staff');
    $this->db->where('id', $staff_id);
    $this->db->where('removed', 0);
    $staff = $this->db->get()->row();
    
    if ($staff) {
        echo "Staff exists: " . $staff->first_name . " " . $staff->last_name . "<br>";
        echo "Staff Agency ID: " . $staff->agency_id . "<br>";
        echo "Match user agency? " . ($staff->agency_id == $user_agency_id ? 'YES' : 'NO') . "<br>";
    } else {
        echo "Staff not found or removed<br>";
    }
    
    echo "<hr>";
    echo "<a href='/shoesmith/agency/agency_staff/edit/$staff_id'>Try to edit staff $staff_id</a>";
}


}