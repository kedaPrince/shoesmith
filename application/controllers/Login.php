<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Login extends MY_Controller {

    public function index()
    {
        $this->load->helper(array('form', 'string'));
        $this->load->library('form_validation');

        $this->setup_validation();
        $showCaptcha = $this->show_hide_captcha();

        $this->session->set_userdata('loginToken', random_string('alnum','32'));
        $token = $this->session->loginToken;

        if (is_ajax()) {
            ajax_return(array(
                'token'         => $token,
                'showCaptcha'   => $showCaptcha,
                'errors'        => validation_errors()
            ));
        }
        else {
            $this->load->view('cms/login/view_login', array(
                'token'         => $token,
                'showCaptcha'   => $showCaptcha
            ));
        }
    }

    public function forgot_password()
    {
        $this->load->helper(array('form', 'string'));
        $this->load->library('form_validation');

        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|callback_email_exists');
        $this->form_validation->set_rules('bgimage', 'BGImage', 'trim');

        $success = false;
        $message = '';
        if ($this->input->post('action') == 'forgot_password') {
            if ($this->form_validation->run() === true) {
                $email = $this->input->post('email');
                $query = $this->get_reset_email_data($email);

                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $emailData) {
                        $success = true;
                        $tokenData = $this->generate_reset_token($emailData['group'], $email);
                        $tokenHours = $this->config->item('reset_password_token_lifetime');
                        $loginGroups = $this->config->item('login_groups');
                        
                        //Set email parameters
                        $emailData['resetDate'] = date("j F Y", strtotime($tokenData['created']));
                        $emailData['resetTime'] = date("H:i", strtotime($tokenData['created']));
                        $emailData['validTime'] = $tokenHours == 1 ? lang('forgot_password_an_hour') : $tokenHours." ".lang('forgot_password_hours');
                        
                        //Check if custom reset password page is set
                        if (!empty($loginGroups[$emailData['group']]['custom_reset'])) {
                            $emailData['link'] = site_url().$loginGroups[$emailData['group']]['custom_reset'].$tokenData['token'].'/'.md5($email);
                        }
                        else {
                            $emailData['link'] = site_url().'login/reset-password/'.$tokenData['token'].'/'.md5($email);
                        }
                        
                        send_mail('forgot_password', $email, lang('forgot_password_email_subject').' ('.ucwords($emailData['group']).' Account)', $emailData, '', true);
                        
                        //Log action
                        Logger::log($emailData['name'].' ('.$emailData['group'].') has requested a forgot password', $emailData, $emailData['group'], $emailData['id']);
                        
                    }
                    $message = lang('forgot_password_success');
                    

                    if (!is_ajax()) {
                        $this->session->forgotPasswordSuccess = $message;
                        redir('login/forgot-password');
                    }
                }
                else {
                    $error = lang('forgot_password_issue');
                    if (!is_ajax()) {
                        $this->session->forgotPasswordError = $error;
                    }
                }

            }
        }

        if (is_ajax()) {

            $errors = validation_errors();
            if (!empty($error)) {
                $errors .= '<p>'.$error.'</p>';
            }

            ajax_return(array(
                'success'       => $success,
                'message'       => $message,
                'errors'        => $errors
            ));
        }
        else {
            $this->load->view('cms/login/view_forgot_password');
        }
    }

    public function reset_password()
    {
        $this->load->helper(array('form', 'string'));
        $this->load->library('form_validation');

        $x = $this->config->item('reset_password_token_lifetime');
        $y = $this->config->item('set_first_password_token_lifetime');

        //Look up the token if it exists
        $this->db->where('token', uri_segment(3));
        $this->db->where('IF(new_user, `created_at` >= "'.date('Y-m-d H:i:s', strtotime('-'.$y.' hour')).'", `created_at` >= "'.date('Y-m-d H:i:s', strtotime('-'.$x.' hour')).'")', '', false);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('sys_password_reset_tokens');

        if ($query->num_rows() > 0) {
            $row = $query->row();
            $md5email = uri_segment(4);

            //check if token passed matches the md5 of the email
            if (md5($row->email) == $md5email) {

                $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
                $this->form_validation->set_rules('cpassword', 'Confirm Password', 'required|min_length[8]|matches[password]');

                if ($this->input->post('action') == 'reset_password') {
                    if ($this->form_validation->run() === true) {
                        $password = $this->input->post('password');
                        $newPassword = password_hash($password, PASSWORD_DEFAULT);

                        $loginGroups = $this->config->item('login_groups');

                        $this->db->set('password', $newPassword);
                        $this->db->where('email', $row->email);
                        $result = $this->db->update($loginGroups[$row->group]['table']);

                        //Update token table
                        if ($result) {
                            $this->db->set('used', 1);
                            $this->db->where('id', $row->id);
                            $result = $this->db->update('sys_password_reset_tokens');
                            
                            if (!$result) {
                                $message = 'Unable to set password reset token as used.';
                                Anomalies::log(trim($message), $this->db->last_query());
                            }
                            
                            $this->do_login($row->email, $row->group);
                        }
                        else {
                            $message = 'Unable to set new password on password reset page.';
                            Anomalies::log(trim($message), $this->db->last_query());
                        }

                        //If it reaches this point then something went wrong
                        $error = lang('reset_password_issue');
                        $this->session->forgotPasswordError = $error;
                    }
                }


                $this->load->view('cms/login/view_reset_password', (array)$row);
            }
            else {
                $error = lang('reset_password_invalid_token');
                $this->session->forgotPasswordError = $error;
                redir('login/forgot-password');
            }

        }
        else {
            $error = lang('reset_password_invalid_token');
            $this->session->forgotPasswordError = $error;
            redir('login/forgot-password');
        }

    }

    public function logout($group="")
    {
        //Extract login session data
        $data = $this->session->login;

        //Get login group data
        $loginGroups = $this->config->item('login_groups');

        if (!empty($group)) {
            if (isset($data[$group])) {
                //Log logout action
                Logger::log($data[$group]['first_name'].' '.$data[$group]['last_name'].' ('.$data[$group]['group'].') has logged out', $data[$group], $group, $data[$group]['id']);

                //Logout given group
                unset($data[$group]);
            }

        }
        else {
            //No group specified, logout all login groups

            //Loop through each group and unset login data
            foreach ($loginGroups as $g => $groupData) {
                if (isset($data[$g])) {
                    //Log logout action
                    Logger::log($data[$g]['first_name'].' '.$data[$g]['last_name'].' ('.$data[$g]['group'].') has logged out', $data[$g], $g, $data[$g]['id']);

                    unset($data[$g]);
                }
            }
        }

        //Update login session data
        $this->session->set_userdata('login', $data);

        //Get page to redirect to
        if(isset($loginGroups[$group]) && isset($loginGroups[$group]['logout_redirect'])) {
            redirect(site_url().$loginGroups[$group]['logout_redirect']);
        }
        else {
            //Redirect to login page
            redirect(site_url().'login');
        }
    }

    public function setup_validation()
    {
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required|callback_check_password');
    }

    /**
     * Check Password
     *
     * Validation method to check if given password matches system.
     *
     * @return boolean
     */
    public function check_password()
    {
        //Get all login groups
        $loginGroups = $this->config->item('login_groups');

        $token = $this->session->loginToken;
        $password = $this->input->post('password');
        $email = $this->input->post('email');
        $group = $this->input->post('group');
        $table = $loginGroups[$group]['table'];

        $query = $this->db->query('
            SELECT
                id, first_name, last_name
            FROM '.$this->db->escape_str($table).'
            WHERE removed = 0 AND enabled = 1
            AND email = "'.$this->db->escape_str($email).'"
            AND MD5(CONCAT("'.$this->db->escape_str($token).'", password)) = "'.$this->db->escape_str($password).'"
        ');

        if ($query->num_rows() > 0) {
            return TRUE;
        }
        else {
            $this->form_validation->set_message('check_password', lang('login_error_invalid_password'));
            return FALSE;
        }

    }

    /**
     * Validate Captcha
     *
     * Validates the Google Recaptcha
     *
     * @param $str
     */
    public function validate_captcha($str)
    {
        $params = array(
            'secret'    => $this->config->item('recaptcha_secret'),
            'response'  => $str,
            'remoteip'  => $this->input->ip_address()
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);

        curl_close ($ch);

        $response = json_decode($server_output);

        if ($response->success) {
            return TRUE;
        }
        else {
            $this->form_validation->set_message('validate_captcha', lang('login_error_captcha'));
            return FALSE;
        }
    }

    /**
     * Email Exists
     *
     * Validation function to check whether or not email exists
     * in one of the user tables.
     *
     * @param $str
     *
     * @return bool
     */
    public function email_exists($str)
    {
        //Get all login groups
        $loginGroups = $this->config->item('login_groups');

        $email = $this->input->post('email');

        //Build a query to check across all defined login groups
        $sql = '';
        $i = 0;
        foreach ($loginGroups as $g => $group) {
            $i++;
            $sql .= '
                SELECT
                    id,
                    "'.$g.'" AS login_group
                FROM '.$group['table'].'
                WHERE removed = 0 AND email = "'.$this->db->escape_str($email).'"
            ';

            if ($i != count($loginGroups)) {
                $sql .= ' UNION ALL ';
            }
        }

        if (!empty($sql)) {
            $query = $this->db->query($sql);

            if ($query->num_rows() > 0) {
                return TRUE;
            }
            else {
                $this->form_validation->set_message('email_exists', lang('forgot_password_no_account'));
                return FALSE;
            }
        }
        else {
            $message = '
                Could not validate email for forgot password page
                because it might missing the login groups.
            ';
            Anomalies::log(trim($message));

            $this->form_validation->set_message('email_exists', lang('forgot_password_cant_validate'));
            return FALSE;
        }
    }

    /**
     * Show Hide Captcha
     *
     * Determines whether or not to show the Captcha.
     * Depending on the value in the config file,
     * this will either show after a number of failed attempts,
     * always or not at all.
     *
     * show_captcha == -1   : Never show
     * show_captcha == 0    : Always show
     * show_captcha >= 1    : Show after x amount of failed attempts
     *
     * @param boolean $noRules
     *
     * @return bool
     */
    private function show_hide_captcha($noRules = false)
    {
        $show = $this->config->item('show_captcha');

        if ($show > -1) {
            //Get number of failed attempts
            $failed = ($this->session->failedLogins) ? $this->session->failedLogins : 0;

            if ($failed >= $show) {
                $noRules || $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_validate_captcha');
                return true;
            }
        }
        return false;
    }

    /**
     * Get reset email data
     *
     * Gets the data needed for the reset password email.
     *
     * @param string $email
     *
     * @return object
     */
    private function get_reset_email_data($email)
    {
        //Get all login groups
        $loginGroups = $this->config->item('login_groups');

        //Build a query to check across all defined login groups
        $sql = '';
        $i = 0;
        foreach ($loginGroups as $g => $group) {
            $i++;
            $sql .= '
                SELECT
                    id, email, first_name AS "name",
                    "'.$g.'" AS "group"
                FROM '.$group['table'].'
                WHERE removed = 0 AND email = "'.$this->db->escape_str($email).'"
            ';

            if ($i != count($loginGroups)) {
                $sql .= ' UNION ALL ';

            }
        }

        if (!empty($sql)) {
            $query = $this->db->query($sql);

            //$row = $query->row_array();
            //return $row;

            return $query;
        }
        else {
            $message = '
                Something went wrong with the forgot password process.
                The query checking the emails could not be built,
                probably due to login groups not being specified.
            ';
            Anomalies::log(trim($message));
        }

        return false;
    }

    /**
     * Generate Reset Token
     *
     * Generates a reset token which will only be valid for an hour.
     *
     * @param $group
     * @param $email
     *
     * @return array
     */
    private function generate_reset_token($group, $email)
    {
        $token = random_string('alnum',32);

        $date = date("Y-m-d H:i:s");
        $this->db->set('email', $email);
        $this->db->set('group', $group);
        $this->db->set('token', $token);

        //Because the created_at time needs to be return, it's manually set.
        $this->db->set('created_at', $date);
        $result = $this->db->insert('sys_password_reset_tokens');

        $data['token'] = $token;
        $data['created'] = $date;

        if ($result) {
            return $data;
        }
        else {
            $message = 'Failed to insert reset token';
            Anomalies::log(trim($message), $this->db->last_query());
        }
    }

    /**
     * Ajax Refresh Token
     *
     * Get's the latest login and CSRF token,
     * in case one of them expired elsewhere
     */
    public function ajax_refresh_token() {

        if (!empty($this->session->loginToken)) {
            $token = $this->session->loginToken;
        }
        else {
            $token = random_string('alnum','32');
            $this->session->set_userdata('loginToken', $token);
        }

        ajax_return(array(
            'token'     => $token
        ));
    }

    public function ajax_check_captcha() {
        $showCaptcha = $this->show_hide_captcha(true);

        if ($showCaptcha) {
            $html = '<div class="g-recaptcha" data-sitekey="'.$this->config->item('recaptcha_site').'"></div>';
            $script = '<script src="//www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>';
        }
        else {
            $html = '';
            $script = '';
        }

        ajax_return(array(
            'html' => $html,
            'script' => $script
        ));
    }

    public function ajax_attempt_login() {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        log_message('debug', "=== LOGIN DEBUG START ===");
        log_message('debug', "Login attempt for: " . $email);

        //Reset filter data
        $this->session->unset_userdata('ecmsFilters');

        //Get all login groups
        $loginGroups = $this->config->item('login_groups');
        log_message('debug', "Available login groups: " . implode(', ', array_keys($loginGroups)));

        //Check which user accounts is associated with this login
        $accountData = array();
        foreach ($loginGroups as $group => $groupData) {

            //If group is specified then only do login check for specified group
            if ($this->input->post('group') && $group != $this->input->post('group')){ 
                continue;
            }

            $this->db->where('email', $email);
            $this->db->where('removed', 0);
            $this->db->where('enabled', 1);

            // Special handling for agency login (agencies table)
            if ($group == 'agency') {
                $this->db->where('login_enabled', 1); // Only agencies with login enabled
            }

            $query = $this->db->get($groupData['table']);
            
            log_message('debug', "Checking group: " . $group . " in table: " . $groupData['table']);
            log_message('debug', "SQL: " . $this->db->last_query());
            log_message('debug', "Found records: " . $query->num_rows());
            
            if ($query->num_rows() > 0) {
                $row = $query->row();
                
                // Special handling for agency name field
                if ($group == 'agency') {
                    // Agencies use 'name' field instead of first_name + last_name
                    $row->name = $row->name; // Already set in agencies table
                    $row->first_name = $row->name; // For compatibility
                    $row->last_name = ''; // Agencies don't have last_name
                }
                
                $accountData[$group] = $row;
                log_message('debug', "User found in " . $group . ": " . ($group == 'agency' ? $row->name : $row->first_name . " " . $row->last_name));
                log_message('debug', "User ID: " . $row->id . ", Email: " . $row->email);
            }
        }

        log_message('debug', "Total accounts found: " . count($accountData));
        log_message('debug', "Account groups: " . implode(', ', array_keys($accountData)));

        // If no accounts found, check if it's an agency staff specifically
        if (count($accountData) === 0) {
            log_message('debug', "No accounts found in initial search, checking agency_staff specifically");
            
            // Specifically check agency_staff table
            $this->db->where('email', $email);
            $this->db->where('removed', 0);
            $this->db->where('enabled', 1);
            $this->db->where('is_approved', 1); // Agency staff need to be approved
            
            $agencyStaffQuery = $this->db->get('agency_staff');
            log_message('debug', "Agency Staff SQL: " . $this->db->last_query());
            log_message('debug', "Agency Staff found: " . $agencyStaffQuery->num_rows());
            
            if ($agencyStaffQuery->num_rows() > 0) {
                $row = $agencyStaffQuery->row();
                $accountData['agency_staff'] = $row;
                log_message('debug', "Agency staff found: " . $row->first_name . " " . $row->last_name);
            }
        }

        //Check for multiple accounts
        if (count($accountData) > 1) {
            $accounts = array();
            foreach ($accountData as $group => $row) {
                $name = ($group == 'agency') ? $row->name : $row->first_name.' '.$row->last_name;
                $accounts[] = array(
                    'group'     => $group,
                    'enabled'   => $row->enabled,
                    'name'      => $name
                );
            }

            log_message('debug', "Multiple accounts found, showing selection modal");
            ajax_return(array(
                'success'   => 0,
                'accounts' => $accounts
            ));
            
            return;
        }

        //If your user only has one account
        if (count($accountData) == 1) {
            foreach ($accountData as $group => $row);

            log_message('debug', "Single account found in group: " . $group);
            log_message('debug', "Checking password for: " . $row->email);
            
            if(!empty($row->password)){
                log_message('debug', "Password hash in DB: " . $row->password);
                $passwordMatch = password_verify($password, $row->password);
                log_message('debug', "Password verification result: " . ($passwordMatch ? 'SUCCESS' : 'FAILED'));
                
                if (!$passwordMatch) {
                    log_message('debug', "PASSWORD VERIFICATION FAILED");
                    $this->session->unset_userdata('login');
                    $this->session->unset_userdata('is_logged_in');

                    ajax_return(array(
                        'success'   => 0,
                        'message'   => 'Invalid Email/Password'
                    ));
                    return;
                }
            } else {
                log_message('debug', "NO PASSWORD SET IN DATABASE");
                ajax_return(array(
                    'success'   => 0,
                    'message'   => 'The password for this account has not been set, please check your email.<br><br>Alternatively, click on forgot password below.'
                ));
                return;
            }

            //Make sure account is active
            if ($row->enabled) {
                // Special handling for agency name
                $name = ($group == 'agency') ? $row->name : (!empty($row->name) ? $row->name : $row->first_name.' '.$row->last_name);
                
                $login[$group] = array(
                    'id'            => $row->id,
                    'group'         => $group,
                    'name'          => $name,
                    'redirect'      => site_url().$loginGroups[$group]['default_url'],
                    'enabled'       => $row->enabled,
                    'profile_pic'   => isset($row->profile_pic) ? $row->profile_pic : '',
                );

                // Add specific session fields based on group
                if ($group == 'agency') {
                    $login[$group]['email'] = $row->email;
                    $login[$group]['contact_person'] = $row->contact_person;
                    $login[$group]['first_name'] = $row->name;
                    $login[$group]['last_name'] = '';
                    $login[$group]['agency_id'] = $row->id;  // ← This sets agency_id INSIDE login[agency]
                } elseif ($group == 'agency_staff') {
                    $login[$group]['email'] = $row->email;
                    $login[$group]['first_name'] = $row->first_name;
                    $login[$group]['last_name'] = $row->last_name;
                    $login[$group]['agency_id'] = $row->agency_id;
                    $login[$group]['usr_type_id'] = $row->usr_type_id;
                }

                // Add session fields from config
                if (!empty($loginGroups[$group]['session_fields'])) {
                    foreach ($loginGroups[$group]['session_fields'] as $field) {
                        if (isset($row->{$field})) {
                            $login[$group][$field] = $row->{$field};
                        }
                    }
                }

                log_message('debug', "LOGIN SUCCESS - Creating session for: " . $login[$group]['name']);
                log_message('debug', "Session data: " . print_r($login[$group], true));

                $this->session->set_userdata('login', $login);
                $this->session->set_userdata('is_logged_in', 1);

                $redirectUrl = site_url().$loginGroups[$group]['default_url'];
                if (!empty($this->session->loginRedirect)) {
                    $redirectUrl = $this->session->loginRedirect;
                    $this->session->unset_userdata('loginRedirect');
                }

                log_message('debug', "Final redirect URL: " . $redirectUrl);
                log_message('debug', "=== LOGIN DEBUG END ===");

                ajax_return(array(
                    'success' => 1,
                    'redirect' => $redirectUrl
                ));
            }
            else {
                log_message('debug', "ACCOUNT DISABLED");
                ajax_return(array(
                    'success'   => 0,
                    'message'   => 'Your account has been disabled.'
                ));
            }

            return;
        }

        log_message('debug', "NO ACCOUNTS FOUND");
        log_message('debug', "=== LOGIN DEBUG END ===");

        //If it get's here then the user doesn't have an account
        ajax_return(array(
            'success'   => 0,
            'message'   => 'Invalid Email/Password'
        ));
    }

    private function do_login($email, $group) {
        //Reset filter data
        $this->session->unset_userdata('ecmsFilters');

        $loginGroups = $this->config->item('login_groups');

        $table      = $loginGroups[$group]['table'];
        $defaultUrl = $loginGroups[$group]['default_url'];

        $this->db->where($table.'.email', $email);
        $this->db->where($table.'.removed', 0);
        $this->db->where($table.'.enabled', 1);

        $query = $this->db->get($table);

        if ($query->num_rows() > 0) {
            $row = $query->row();

            $login = loginData();

            if(!is_array($login)) $login = [];

            $login[$group] = array(
                'id'        => $row->id,
                'group'     => $group,
                'name'      => (!empty($row->name)) ? $row->name : $row->first_name.' '.$row->last_name,
                'redirect'  => site_url().$defaultUrl,
                'enabled'   => $row->enabled
            );
            // ✅ CRITICAL FIX: Add agency_id to session
            if ($group == 'agency') {
                $login[$group]['agency_id'] = $row->id;
                   $this->session->set_userdata('agency_id', $row->id);
            } elseif ($group == 'agency_staff') {
                $login[$group]['agency_id'] = $row->agency_id;
                $this->session->set_userdata('agency_id', $row->agency_id);
            }

            if (!empty($loginGroups[$group]['session_fields'])) {
                foreach ($loginGroups[$group]['session_fields'] as $field) {
                    $login[$group][$field] = !empty($row->{$field}) ? $row->{$field} : '';
                }
            }
            $this->session->set_userdata('login', $login);
            $this->session->set_userdata('is_logged_in', 1);

            redirect(site_url().$defaultUrl);
        }
    }


    
}