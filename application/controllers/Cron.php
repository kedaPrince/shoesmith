<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends MY_Controller {

    public function __construct() {
        parent::__construct();
        
        // ============ ADDED SECURITY ============
        // Restrict to command line access only
        if (!$this->input->is_cli_request()) {
            show_error('This script can only be accessed via command line', 403);
            return;
        }
        // ============ END SECURITY ============
        
        $this->load->library('heartbeat');
        ini_set('max_execution_time', 600);
    }

    public function send_mails() {
    // ============ ADD CSRF PROTECTION ============
    $csrf_name = $this->security->get_csrf_token_name();
    $csrf_token = $this->input->post($csrf_name);
    
    if (!$csrf_token || $csrf_token !== $this->security->get_csrf_hash()) {
        show_error('Invalid CSRF token', 400);
        return;
    }
    // ============ END CSRF PROTECTION ============
    
    crontext('Send_mails start');
    $this->load->library('mailer');
    $this->mailer->process_queue();
   
    //Log every hour that the cron ran
    if (date('i')/1 == 0) {
        $this->db->set('logged_at', date('Y-m-d H:i:s'));
        $this->db->insert('log_cron_send_mails');
    }
    
    crontext('Send_mails end');
}

    /**
     * Crons meant to be run hourly
     */
    public function hourly() {
        crontext('Hourly start');
        $logID = $this->log_hourly('cron_start');


        crontext('Hourly end');
        $this->log_hourly('cron_end', $logID);
    }

    /**
     * Crons meant to be run once a day
     */
    public function daily() {
        crontext('Daily start');
        $logID = $this->log_daily('cron_start');


        crontext('Daily end');
        $this->log_daily('cron_end', $logID);
    }

    /**
     * Log Hourly
     *
     * Logs the hourly crons to make sure they all run.
     *
     * @param string $column
     * @param int $logID
     *
     * @return int
     */
    private function log_hourly($column, $logID = 0) {
        $this->db->set($column, date('Y-m-d H:i:s'));
        $column == 'cron_end' && $this->db->set('completed', 1);

        if ($column == 'cron_start') {
            $this->db->insert('log_hourly_crons');

            $id = $this->db->insert_id();

            return $id;
        } else {
            $this->db->where('id', $logID);
            $this->db->update('log_hourly_crons');

            return $logID;
        }
    }

    /**
     * Log Daily
     *
     * Logs the daily crons to make sure they all run.
     *
     * @param string $column
     * @param int $logID
     *
     * @return int
     */
    private function log_daily($column, $logID = 0) {
        $this->db->set($column, date('Y-m-d H:i:s'));
        $column == 'cron_end' && $this->db->set('completed', 1);

        if ($column == 'cron_start') {
            $this->db->insert('log_daily_crons');

            $id = $this->db->insert_id();

            return $id;
        } else {
            $this->db->where('id', $logID);
            $this->db->update('log_daily_crons');

            return $logID;
        }
    }
}