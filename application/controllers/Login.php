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

    if ($this->input->server('REQUEST_METHOD') === 'POST' && $this->input->post('action') == 'forgot_password') {
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            if (is_ajax()) {
                ajax_return(array(
                    'success' => false,
                    'message' => 'Invalid security token',
                    'csrf' => $this->security->get_csrf_hash()
                ));
            } else {
                $this->session->set_flashdata('error', 'Invalid security token');
                redirect('login/forgot-password');
            }
            return;
        }
    }

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
                    
                    $emailData['resetDate'] = date("j F Y", strtotime($tokenData['created']));
                    $emailData['resetTime'] = date("H:i", strtotime($tokenData['created']));
                    $emailData['validTime'] = $tokenHours == 1 ? lang('forgot_password_an_hour') : $tokenHours." ".lang('forgot_password_hours');
                    
                    if (!empty($loginGroups[$emailData['group']]['custom_reset'])) {
                        $emailData['link'] = site_url().$loginGroups[$emailData['group']]['custom_reset'].$tokenData['token'].'/'.md5($email);
                    }
                    else {
                        $emailData['link'] = site_url().'login/reset-password/'.$tokenData['token'].'/'.md5($email);
                    }
                    
                    send_mail('forgot_password', $email, lang('forgot_password_email_subject').' ('.ucwords($emailData['group']).' Account)', $emailData, '', true);
                    
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

    if ($this->input->server('REQUEST_METHOD') === 'POST' && $this->input->post('action') == 'reset_password') {
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            $this->session->set_flashdata('error', 'Invalid security token');
            redirect('login/reset-password/' . uri_segment(3) . '/' . uri_segment(4));
            return;
        }
    }

    $x = $this->config->item('reset_password_token_lifetime');
    $y = $this->config->item('set_first_password_token_lifetime');

    $this->db->where('token', uri_segment(3));
    $this->db->where('IF(new_user, `created_at` >= "'.date('Y-m-d H:i:s', strtotime('-'.$y.' hour')).'", `created_at` >= "'.date('Y-m-d H:i:s', strtotime('-'.$x.' hour')).'")', '', false);
    $this->db->order_by('id', 'DESC');
    $this->db->limit(1);
    $query = $this->db->get('sys_password_reset_tokens');

    if ($query->num_rows() > 0) {
        $row = $query->row();
        $md5email = uri_segment(4);

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
public function cleanup_old_sessions()
{
    // Delete sessions older than 5 minutes (more aggressive)
    $five_minutes_ago = date('Y-m-d H:i:s', strtotime('-5 minutes'));
    
    // Get sessions to delete
    $this->db->select('user_id, user_type');
    $this->db->where('last_activity <', $five_minutes_ago);
    $expired_sessions = $this->db->get('user_sessions')->result();
    
    // Delete the sessions
    $deleted = $this->db->where('last_activity <', $five_minutes_ago)
                       ->delete('user_sessions');
    
    // Update recruiters who had expired sessions
    $recruiter_ids = [];
    foreach ($expired_sessions as $session) {
        if ($session->user_type == 'recruiter') {
            $recruiter_ids[] = $session->user_id;
        }
    }
    
    if (!empty($recruiter_ids)) {
        $this->db->where_in('id', array_unique($recruiter_ids))
                 ->update('recruiters', [
                     'last_logout_at' => date('Y-m-d H:i:s'),
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);
    }
    
    log_message('debug', 'Cleaned up ' . $deleted . ' old sessions');
    echo "Cleaned up " . $deleted . " old sessions at " . date('Y-m-d H:i:s');
}

public function ajax_update_logout_time()
{
    $data = $this->session->login;
    
    if (isset($data['recruiter'])) {
        $recruiter_id = $data['recruiter']['id'];
        
        // Delete ALL sessions for this recruiter
        $this->db->where('user_id', $recruiter_id)
                 ->where('user_type', 'recruiter')
                 ->delete('user_sessions');
        
        // Update last_logout_at for recruiter
        $this->db->where('id', $recruiter_id)
                 ->update('recruiters', [
                     'last_logout_at' => date('Y-m-d H:i:s'),
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);
        
        log_message('debug', 'Cleared sessions for recruiter: ' . $recruiter_id);
    }
    
    $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true]));
}
public function logout($group = "")
{
    // Get current session ID BEFORE doing anything
    $current_session_id = session_id();
    
    // Log the session ID for debugging
    log_message('debug', 'Logout called. Session ID: ' . $current_session_id);
    
    // Allow both GET and POST requests for logout
    if ($this->input->server('REQUEST_METHOD') === 'POST') {
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            show_error('Invalid security token', 400);
            return;
        }
    }
    
    $data = $this->session->login;
    $loginGroups = $this->config->item('login_groups');

    if (!empty($group)) {
        if (isset($data[$group])) {
            $user_id = $data[$group]['id'];
            
            Logger::log($data[$group]['first_name'].' '.$data[$group]['last_name'].' ('.$data[$group]['group'].') has logged out', $data[$group], $group, $user_id);
            
            // ✅ CRITICAL FIX: Delete ALL sessions for this user
            if ($group == 'recruiter' && $user_id) {
                log_message('debug', 'Deleting sessions for recruiter ID: ' . $user_id);
                
                // Delete from user_sessions table
                $deleted = $this->db->where('user_id', $user_id)
                         ->where('user_type', 'recruiter')
                         ->delete('user_sessions');
                
                log_message('debug', 'Deleted ' . $deleted . ' sessions from user_sessions table');
                
                // Also update recruiters table
                $this->db->where('id', $user_id)
                         ->update('recruiters', [
                             'last_logout_at' => date('Y-m-d H:i:s'),
                             'updated_at' => date('Y-m-d H:i:s')
                         ]);
            }
            
            unset($data[$group]);
        }
    } else {
        foreach ($loginGroups as $g => $groupData) {
            if (isset($data[$g])) {
                $user_id = $data[$g]['id'];
                
                Logger::log($data[$g]['first_name'].' '.$data[$g]['last_name'].' ('.$data[$g]['group'].') has logged out', $data[$g], $g, $user_id);
                
                // ✅ CRITICAL FIX: Delete ALL sessions for this recruiter
                if ($g == 'recruiter' && $user_id) {
                    log_message('debug', 'Deleting sessions for recruiter ID: ' . $user_id);
                    
                    $deleted = $this->db->where('user_id', $user_id)
                             ->where('user_type', 'recruiter')
                             ->delete('user_sessions');
                    
                    log_message('debug', 'Deleted ' . $deleted . ' sessions from user_sessions table');
                    
                    $this->db->where('id', $user_id)
                             ->update('recruiters', [
                                 'last_logout_at' => date('Y-m-d H:i:s'),
                                 'updated_at' => date('Y-m-d H:i:s')
                             ]);
                }
                
                unset($data[$g]);
            }
        }
    }

    // Update the session data
    $this->session->set_userdata('login', $data);
    
    // If no more logged in groups, destroy session completely
    if (empty($data)) {
        $this->session->unset_userdata('is_logged_in');
        $this->session->unset_userdata('agency_id');
        $this->session->unset_userdata('loginRedirect');
        
        // ✅ Also delete the current session from user_sessions table
        if ($current_session_id) {
            $this->db->where('session_id', $current_session_id)
                     ->delete('user_sessions');
        }
        
        $this->session->sess_destroy();
    }

    // Set a success message
    $this->session->set_flashdata('success', 'You have been logged out successfully.');

    // Redirect to appropriate login page
    if(isset($loginGroups[$group]) && isset($loginGroups[$group]['logout_redirect'])) {
        redirect($loginGroups[$group]['logout_redirect']);
    } else {
        // Redirect to generic login page
        redirect('login');
    }
}

public function test_logout_fix()
{
    echo "<h3>Testing Logout Session Cleanup</h3>";
    
    // Check if logged in as recruiter
    $data = $this->session->login;
    
    if (!isset($data['recruiter'])) {
        echo "<p>Not logged in as recruiter. Please log in first.</p>";
        return;
    }
    
    $recruiter_id = $data['recruiter']['id'];
    $session_id = session_id();
    
    echo "<p>Recruiter ID: {$recruiter_id}</p>";
    echo "<p>Current Session ID: {$session_id}</p>";
    
    // Check current sessions
    $this->db->where('user_id', $recruiter_id)
             ->where('user_type', 'recruiter');
    $sessions = $this->db->get('user_sessions')->result();
    
    echo "<p>Current sessions in database: " . count($sessions) . "</p>";
    
    foreach ($sessions as $session) {
        echo "<p>Session ID: {$session->session_id} | Last Activity: {$session->last_activity}</p>";
    }
    
    // Test the delete
    echo "<hr><h4>Testing Session Deletion:</h4>";
    
    $deleted = $this->db->where('user_id', $recruiter_id)
                       ->where('user_type', 'recruiter')
                       ->delete('user_sessions');
    
    echo "<p>Delete command affected rows: {$deleted}</p>";
    
    // Verify
    $this->db->where('user_id', $recruiter_id)
             ->where('user_type', 'recruiter');
    $remaining = $this->db->get('user_sessions')->num_rows();
    
    echo "<p>Sessions remaining after delete: {$remaining}</p>";
    
    echo "<hr><a href='/login/logout/recruiter'>Click here to actually logout</a>";
}

public function ajax_update_activity()
{
    $data = $this->session->login;
    $current_session_id = session_id();
    
    foreach ($data as $group => $user) {
        if (isset($user['id'])) {
            // Update user_sessions table
            $this->db->where('user_id', $user['id'])
                     ->where('user_type', $group)
                     ->where('session_id', $current_session_id)
                     ->update('user_sessions', [
                         'last_activity' => date('Y-m-d H:i:s')
                     ]);
            
            // For recruiters, also update recruiters table
            if ($group == 'recruiter') {
                $this->db->where('id', $user['id'])
                         ->update('recruiters', [
                             'last_activity_at' => date('Y-m-d H:i:s'),
                             'updated_at' => date('Y-m-d H:i:s')
                         ]);
            }
        }
    }
    
    $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true]));
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
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $csrf_name = $this->security->get_csrf_token_name();
            $csrf_token = $this->input->post($csrf_name);
            
            if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
                $this->form_validation->set_message('email_exists', 'Invalid security token');
                return FALSE;
            }
        }

        $loginGroups = $this->config->item('login_groups');
        $email = $this->input->post('email');
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
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_token = $this->input->post($csrf_name);
        
        if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
            ajax_return([
                'success' => false,
                'message' => 'Invalid CSRF token',
                'csrf' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        if (!empty($this->session->loginToken)) {
            $token = $this->session->loginToken;
        }
        else {
            $token = random_string('alnum','32');
            $this->session->set_userdata('loginToken', $token);
        }

        ajax_return(array(
            'success' => true,
            'token' => $token,
            'csrf' => $this->security->get_csrf_hash()
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
    // First, get fresh CSRF token
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_hash = $this->security->get_csrf_hash();
    
    $email = $this->input->post('email');
    $password = $this->input->post('password');
    $group = $this->input->post('group');

    log_message('debug', "Login attempt for: " . $email);

    $this->session->unset_userdata('ecmsFilters');
    $loginGroups = $this->config->item('login_groups');

    $accountData = array();
    
    // If group is specified, check only that group
    if ($group) {
        if (isset($loginGroups[$group])) {
            $this->db->where('email', $email);
            $this->db->where('removed', 0);
            $this->db->where('enabled', 1);

            if ($group == 'agency') {
                $this->db->where('login_enabled', 1);
            }

            $query = $this->db->get($loginGroups[$group]['table']);
            
            if ($query->num_rows() > 0) {
                $row = $query->row();
                $accountData[$group] = $row;
            }
        }
    } else {
        // No group specified, check all groups
        foreach ($loginGroups as $group => $groupData) {
            $this->db->where('email', $email);
            $this->db->where('removed', 0);
            $this->db->where('enabled', 1);

            if ($group == 'agency') {
                $this->db->where('login_enabled', 1);
            }

            $query = $this->db->get($groupData['table']);
            
            if ($query->num_rows() > 0) {
                $row = $query->row();
                $accountData[$group] = $row;
            }
        }
    }

    // Also check agency_staff if no accounts found
    if (count($accountData) === 0) {
        $this->db->where('email', $email);
        $this->db->where('removed', 0);
        $this->db->where('enabled', 1);
        $this->db->where('is_approved', 1);
        
        $agencyStaffQuery = $this->db->get('agency_staff');
        
        if ($agencyStaffQuery->num_rows() > 0) {
            $row = $agencyStaffQuery->row();
            $accountData['agency_staff'] = $row;
        }
    }

    if (count($accountData) >= 1) {
        foreach ($accountData as $group => $row);

        if(!empty($row->password)){
            $passwordMatch = password_verify($password, $row->password);
            
            if (!$passwordMatch) {
                $this->session->unset_userdata('login');
                $this->session->unset_userdata('is_logged_in');

                ajax_return(array(
                    'success'   => 0,
                    'message'   => 'Invalid Email/Password',
                    'csrf'      => $csrf_hash
                ));
                return;
            }
        } else {
            ajax_return(array(
                'success'   => 0,
                'message'   => 'Invalid Email/Password',
                'csrf'      => $csrf_hash
            ));
            return;
        }

        if ($row->enabled) {
            $name = ($group == 'agency') ? $row->name : (!empty($row->name) ? $row->name : $row->first_name.' '.$row->last_name);
            
            $login[$group] = array(
                'id'            => $row->id,
                'group'         => $group,
                'name'          => $name,
                'redirect'      => site_url().$loginGroups[$group]['default_url'],
                'enabled'       => $row->enabled,
                'profile_pic'   => isset($row->profile_pic) ? $row->profile_pic : '',
            );

            // ================ ADDED: ONLINE STATUS TRACKING ================
            if ($group == 'agency') {
                $login[$group]['email'] = $row->email;
                $login[$group]['contact_person'] = $row->contact_person;
                $login[$group]['first_name'] = $row->name;
                $login[$group]['last_name'] = '';
                $login[$group]['agency_id'] = $row->id;
                
                // Update last login for agency
                $this->db->where('id', $row->id)
                         ->update('agencies', [
                             'last_login' => date('Y-m-d H:i:s'),
                             'updated_at' => date('Y-m-d H:i:s')
                         ]);
                         
                // Create/update session record
                $session_data = [
                    'user_id' => $row->id,
                    'user_type' => 'agency',
                    'session_id' => session_id(),
                    'ip_address' => $this->input->ip_address(),
                    'user_agent' => $this->input->user_agent(),
                    'last_activity' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->update_or_create_session($session_data);
                
            } elseif ($group == 'agency_staff') {
                $login[$group]['email'] = $row->email;
                $login[$group]['first_name'] = $row->first_name;
                $login[$group]['last_name'] = $row->last_name;
                $login[$group]['agency_id'] = $row->agency_id;
                $login[$group]['usr_type_id'] = $row->usr_type_id;
                
                // Update last login for agency staff
                $this->db->where('id', $row->id)
                         ->update('agency_staff', [
                             'last_login' => date('Y-m-d H:i:s'),
                             'updated_at' => date('Y-m-d H:i:s')
                         ]);
                         
                // Create/update session record
                $session_data = [
                    'user_id' => $row->id,
                    'user_type' => 'agency_staff',
                    'session_id' => session_id(),
                    'ip_address' => $this->input->ip_address(),
                    'user_agent' => $this->input->user_agent(),
                    'last_activity' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->update_or_create_session($session_data);
                
            } elseif ($group == 'recruiter') {
                $login[$group]['email'] = $row->email;
                $login[$group]['first_name'] = $row->first_name;
                $login[$group]['last_name'] = $row->last_name;
                $login[$group]['agency_id'] = $row->agency_id;
                $login[$group]['usr_type_id'] = $row->usr_type_id;
                
                // Update recruiter's updated_at timestamp
                $this->db->where('id', $row->id)
                         ->update('recruiters', [
                             'updated_at' => date('Y-m-d H:i:s')
                         ]);
                
                // ✅ CRITICAL: Create/update session record for recruiter online status
                $session_data = [
                    'user_id' => $row->id,
                    'user_type' => 'recruiter',
                    'session_id' => session_id(),
                    'ip_address' => $this->input->ip_address(),
                    'user_agent' => $this->input->user_agent(),
                    'last_activity' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->update_or_create_session($session_data);
                
            } elseif ($group == 'candidate') {
                $login[$group]['email'] = $row->email;
                $login[$group]['first_name'] = $row->first_name;
                $login[$group]['last_name'] = $row->last_name;
                $login[$group]['agency_id'] = isset($row->agency_id) ? $row->agency_id : null;
                
                // Create/update session record for candidate
                $session_data = [
                    'user_id' => $row->id,
                    'user_type' => 'candidate',
                    'session_id' => session_id(),
                    'ip_address' => $this->input->ip_address(),
                    'user_agent' => $this->input->user_agent(),
                    'last_activity' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->update_or_create_session($session_data);
            }
            // ================ END ADDED CODE ================

            if (!empty($loginGroups[$group]['session_fields'])) {
                foreach ($loginGroups[$group]['session_fields'] as $field) {
                    if (isset($row->{$field})) {
                        $login[$group][$field] = $row->{$field};
                    }
                }
            }

            $this->session->set_userdata('login', $login);
            $this->session->set_userdata('is_logged_in', 1);

            $redirectUrl = site_url().$loginGroups[$group]['default_url'];
            if (!empty($this->session->loginRedirect)) {
                $redirectUrl = $this->session->loginRedirect;
                $this->session->unset_userdata('loginRedirect');
            }

            ajax_return(array(
                'success' => 1,
                'redirect' => $redirectUrl,
                'csrf'    => $csrf_hash
            ));
        }
        else {
            ajax_return(array(
                'success'   => 0,
                'message'   => 'Invalid Email/Password',
                'csrf'      => $csrf_hash
            ));
        }

        return;
    }

    ajax_return(array(
        'success'   => 0,
        'message'   => 'Invalid Email/Password',
        'csrf'      => $csrf_hash
    ));
}

// ================ ADD THIS HELPER METHOD TO THE SAME CLASS ================

/**
 * Update or create session record in user_sessions table
 */
private function update_or_create_session($session_data)
{
    // Check if session already exists
    $this->db->where('user_id', $session_data['user_id'])
             ->where('user_type', $session_data['user_type'])
             ->where('session_id', $session_data['session_id']);
    
    $existing = $this->db->get('user_sessions')->row();
    
    if ($existing) {
        // Update existing session
        $this->db->where('id', $existing->id)
                 ->update('user_sessions', $session_data);
    } else {
        // Insert new session
        $this->db->insert('user_sessions', $session_data);
    }
    
    // Clean up old sessions for this user (keep only the latest 5)
    $this->cleanup_user_sessions($session_data['user_id'], $session_data['user_type']);
}

    /**
     * Clean up old sessions for a user
     */
    private function cleanup_user_sessions($user_id, $user_type)
    {
        // Get all sessions for this user, ordered by last_activity
        $this->db->where('user_id', $user_id)
                ->where('user_type', $user_type)
                ->order_by('last_activity', 'DESC');
        
        $sessions = $this->db->get('user_sessions')->result();
        
        // If more than 5 sessions, delete the oldest ones
        if (count($sessions) > 5) {
            $sessions_to_delete = array_slice($sessions, 5);
            
            foreach ($sessions_to_delete as $session) {
                $this->db->where('id', $session->id)
                        ->delete('user_sessions');
            }
        }
        
        // Also delete sessions older than 1 day
        $one_day_ago = date('Y-m-d H:i:s', strtotime('-1 day'));
        $this->db->where('user_id', $user_id)
                ->where('user_type', $user_type)
                ->where('last_activity <', $one_day_ago)
                ->delete('user_sessions');
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

// When recruiter logs in, update user_sessions
public function update_recruiter_session($recruiter_id)
{
    $session_id = session_id();
    $ip_address = $this->input->ip_address();
    $user_agent = $this->input->user_agent();
    
    $session_data = [
        'session_id' => $session_id,
        'user_id' => $recruiter_id,
        'user_type' => 'recruiter',
        'ip_address' => $ip_address,
        'user_agent' => substr($user_agent, 0, 255),
        'last_activity' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Check if session already exists
    $this->db->where('session_id', $session_id)
             ->where('user_id', $recruiter_id)
             ->where('user_type', 'recruiter');
    
    $existing = $this->db->get('user_sessions')->row();
    
    if ($existing) {
        // Update existing session
        $this->db->where('id', $existing->id)
                 ->update('user_sessions', $session_data);
    } else {
        // Insert new session
        $this->db->insert('user_sessions', $session_data);
    }
}
    
}