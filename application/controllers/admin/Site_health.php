<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site_health extends CRUD_Controller {

    public $pageName    = 'site_health';
    public $group       = 'Site Health';
    public $view        = '';
    public $model       = 'Model_site_health';
    public $sorting     = array();
    public $singular    = 'health';
    public $plural      = 'health';
    public $adding      = false;
    public $hideSubNav  = true;

    public function __construct() {
        parent::__construct();

        $this->load->model($this->folder.'/'.$this->model);
        $this->zone = array(
            'title' => lang('label_site_health'),
            'url'   => url($this->pageName)
        );

    }

    public function index() {
        $this->setup_breadcrumbs();

        $heartbeats             = $this->Model_site_health->get_all_heartbeats();
        $transformedHeartbeats  = $this->transformHeartbeats($heartbeats);
        $metricsGridData        = $this->extractSiteHealthMetrics($heartbeats);
        $extraJS                = array(
            site_url().'resources/cms/javascript/heartbeats/heartbeats.min.js'
        );
        
        load_custom_page($this->folder.'/'.$this->pageName.'/view_site_health', [
            'heading'           => lang('label_site_health'),
            'heartbeats'        => $transformedHeartbeats,
            'metricsGridData'   => $metricsGridData,
        ], [], [
            'js' => $extraJS
        ]);
    }

    /**
     * Transform the heartbeat array into the desired format.
     *
     * @param array $heartbeats The array of heartbeat objects.
     * @return array The transformed array.
     */
    private function transformHeartbeats($heartbeats) {
        $now = time();

        return array_map(function($heartbeat) use ($now) {
            $lastExecuted   = $heartbeat->end_time ? $heartbeat->end_time : $heartbeat->start_time;
            $executionTime  = $heartbeat->end_time ? strtotime($heartbeat->end_time) - strtotime($heartbeat->start_time) : 0;

            $formattedLastExecuted  = $this->formatTimeDifference($now, strtotime($lastExecuted));
            $formattedExecutionTime = $this->formatExecutionTime($executionTime);

            return [
                'id'            => $heartbeat->id,
                'title'         => $heartbeat->title,
                'status'        => $heartbeat->status,
                'lastExecuted'  => $formattedLastExecuted,
                'executionTime' => $formattedExecutionTime
            ];
        }, $heartbeats);
    }

    /**
     * Extract the site health metrics from the heartbeat array.
     *
     * @param array $heartbeats The array of heartbeat objects.
     * @return array The transformed array.
     */
    private function extractSiteHealthMetrics($heartbeats = []) {

        $totalHeartbeats        = count($heartbeats);
        $failedHeartbeats       = count(array_filter($heartbeats, fn($heartbeat) => $heartbeat->status === 'failed'));;
        $completedHeartbeats    = count(array_filter($heartbeats, fn($heartbeat) => $heartbeat->status === 'completed'));
        $successfulExecutions   = ($totalHeartbeats) ? number_format(($completedHeartbeats / $totalHeartbeats) * 100, 1) : '-';

        return [
            'totalHeartbeats'      => $totalHeartbeats,
            'failedHeartbeats'     => $failedHeartbeats,
            'completedHeartbeats'  => $completedHeartbeats,
            'successfulExecutions' => $successfulExecutions,
        ];
    }

    /**
     * Format the time difference as a human-readable string.
     *
     * @param int $now The current time.
     * @param int $then The time in the past.
     * @return string The formatted time difference.
     */
    private function formatTimeDifference($now, $then) {
        $diff = $now - $then;

        if ($diff < 60) {
            return $diff . 's ago';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' mins ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hrs ago';
        } else {
            return floor($diff / 86400) . ' days ago';
        }
    }

    /**
     * Format the execution time as a human-readable string.
     *
     * @param int $seconds The execution time in seconds.
     * @return string The formatted execution time.
     */
    private function formatExecutionTime($seconds) {
        if ($seconds < 60) {
            return $seconds . 's';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $seconds = $seconds % 60;
            return $minutes . 'm ' . $seconds . 's';
        } else {
            $hours      = floor($seconds / 3600);
            $minutes    = floor(($seconds % 3600) / 60);
            $seconds    = $seconds % 60;
            return $hours . 'h ' . $minutes . 'm ' . $seconds . 's';
        }
    }

    function ajax_get_heartbeat_details() {
        $id = $this->input->post('id');

        try {
            $heartbeat = $this->Model_site_health->get_heartbeat($id);

            $now            = time();
            $lastExecuted   = $heartbeat->end_time ? $heartbeat->end_time : $heartbeat->start_time;
            $executionTime  = $heartbeat->end_time ? strtotime($heartbeat->end_time) - strtotime($heartbeat->start_time) : 0;

            $formattedLastExecuted  = $this->formatTimeDifference($now, strtotime($lastExecuted));
            $formattedExecutionTime = $this->formatExecutionTime($executionTime);
            $statuses = [
                'completed'     => "Completed",
                'in-progress'   => "In Progress",
                'failed'        => "Failed",
            ];

            $responseArr = [
                'id'            => $heartbeat->id,
                'title'         => $heartbeat->title,
                'status'        => $statuses[$heartbeat->status],
                'startTime'     => ($heartbeat->start_time) ? date("d M Y h:i:s", strtotime($heartbeat->start_time)) : '-',
                'endTime'       => ($heartbeat->end_time) ? date("d M Y h:i:s", strtotime($heartbeat->end_time)) : '-',
                'attempts'      => $heartbeat->attempt_number,
                'metadata'      => $heartbeat->metadata,
                'failDetails'   => $heartbeat->fail_details,
                'executionTime' => $formattedExecutionTime,
                'lastExecuted'  => $formattedLastExecuted,
            ];

            ajax_return(array(
                'success'   => true,
                'data'      => $responseArr,
            ));
        } catch (\Throwable $th) {
            throw $th;
        }
    }

}