<?php

# ==================================================================================================================================== #
#  Heartbeat     :   A monitoring library for logging Crons, events, jobs, actions that take an unknown amount of time to complete.
# ==================================================================================================================================== #
#  Usage example :   start the heartbeat $this->heartbeat->start($title = "Example Action", $metadata = [])
#  Usage example :   end the heartbeat $this->heartbeat->end($success = true, $fail_details = []) 
#
#  $beatID = $this->heartbeat->start("Daily Backup", ['type' => 'full', "target" => "/backups"]);
#  $this->heartbeat->end(true);
#
#  $beatID = $this->heartbeat->start("Email Digest", ["frequency" => "daily"]);
#  $this->heartbeat->end(false, ["error" => "SMTP server not responding"]);
# ==================================================================================================================================== #

class Heartbeat {
    protected $CI;
    protected $table                = 'sys_heartbeats';
    protected $current_heartbeat_id = null;
    protected $default_status       = 'in-progress'; // other statuses = 
    
    // Default heartbeat data structure
    protected $heartbeat_data = [
        'title'              => 'Untitled',
        'status'             => null,
        'start_time'         => null,
        'end_time'           => null,
        'attempt_number'     => 1,
        'metadata'           => null,
        'fail_details'       => null,
        'execution_duration' => 0,
        'created_at'         => null,
        'updated_at'         => null
    ];

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        
        // Initialize default timestamps
        $this->heartbeat_data['status']     = $this->default_status;
        $this->heartbeat_data['start_time'] = date('Y-m-d H:i:s');
        $this->heartbeat_data['created_at'] = date('Y-m-d H:i:s');
    }

    /**
     * Start monitoring a new heartbeat
     *
     * @param string $title The name/identifier of the heartbeat
     * @param array $metadata Optional metadata about heartbeat
     * @return int The heartbeat ID
     */
    public function start($title, $metadata = []) {
        // Prepare heartbeat data
        $data = array_merge($this->heartbeat_data, [
            'title'             => $title,
            'attempt_number'    => $this->get_attempt_number($title),
            'metadata'          => !empty($metadata) ? json_encode($metadata) : null
        ]);

        // Insert the record
        $this->CI->db->insert($this->table, $data);
        $this->current_heartbeat_id = $this->CI->db->insert_id();

        return $this->current_heartbeat_id;
    }

    /**
     * End the current heartbeat
     *
     * @param bool $success Whether the heartbeat completed successfully
     * @param array $fail_details Optional details about the failure
     * @return bool
     */
    public function end($success = true, $fail_details = []) {
        if (!$this->current_heartbeat_id) {
            return false;
        }

        $end_time = date('Y-m-d H:i:s');

        $data = [
            'status'             => $success ? 'completed' : 'failed',
            'end_time'           => $end_time,
            'updated_at'         => $end_time,
            'execution_duration' => $this->calculate_duration($this->current_heartbeat_id, $end_time),
            'fail_details'       => !empty($fail_details) ? json_encode($fail_details) : null
        ];

        $success = $this->CI->db->where('id', $this->current_heartbeat_id)
                                ->update($this->table, $data);

        $this->current_heartbeat_id = null;
        return $success;
    }

    /**
     * Calculate execution duration in seconds
     */
    protected function calculate_duration($heartbeat_id, $end_time) {
        $heartbeat = $this->CI->db->select('start_time')
                                    ->where('id', $heartbeat_id)
                                    ->get($this->table)
                                    ->row();

        return $heartbeat ? strtotime($end_time) - strtotime($heartbeat->start_time) : 0;
    }

    /**
     * Get the attempt number for a specific heartbeat
     */
    protected function get_attempt_number($title) {
        $last_attempt = $this->CI->db->where('title', $title)
                                    ->order_by('id', 'DESC')
                                    ->get($this->table)
                                    ->row();

        return $last_attempt ? ($last_attempt->attempt_number + 1) : 1;
    }

    /**
     * Get the current heartbeat data
     * 
     * @return object|null
     */
    public function get_current() {
        if (!$this->current_heartbeat_id) {
            return null;
        }

        return $this->CI->db->where('id', $this->current_heartbeat_id)
                            ->get($this->table)
                            ->row();
    }

    /**
     * Get the last heartbeat for a specific title
     * 
     * @param string $title
     * @return object|null
     */
    public function get_last($title) {
        return $this->CI->db->where('title', $title)
                            ->order_by('id', 'DESC')
                            ->limit(1)
                            ->get($this->table)
                            ->row();
    }

    /**
     * Get heartbeat history
     * 
     * @param string $title
     * @param int $limit
     * @return array
     */
    public function get_history($title, $limit = 10) {
        return $this->CI->db->where('title', $title)
                            ->order_by('id', 'DESC')
                            ->limit($limit)
                            ->get($this->table)
                            ->result();
    }

    /**
     * Get the specific heartbeat data
     * 
     * @param int $limit
     * @return object|null
     */
    public function get($id) {
        if (!$id) {
            return null;
        }

        return $this->CI->db->where('id', $id)
                            ->get($this->table)
                            ->row();
    }
}
?>