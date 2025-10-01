<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="dashboard-container">
    <div class="site-health-dashboard-container">
        <h1>Site Health Dashboard</h1>
        
        <!-- Metrics Grid -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-value"><?= $metricsGridData['totalHeartbeats'] ?></div>
                <div class="metric-label">Total Heartbeats</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?= $metricsGridData['completedHeartbeats'] ?></div>
                <div class="metric-label">Completed Heartbeats</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?= $metricsGridData['successfulExecutions'] ?>%</div>
                <div class="metric-label">Successful Executions</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?= $metricsGridData['failedHeartbeats'] ?></div>
                <div class="metric-label">Failed Heartbeats</div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <div class="filters">
                <input type="text" class="form-control search-input" placeholder="Search heartbeats..." id="heartbeatSearch">
            </div>
            
            <div class="table-wrapper">
                <table class="monitoring-table" id="monitoringTable">
                    <thead>
                        <tr>
                            <th data-sort="title" class="sort-icon">Title</th>
                            <th data-sort="status" class="sort-icon">Status</th>
                            <th data-sort="executionTime" class="sort-icon">Execution Time</th>
                            <th data-sort="lastExecuted" class="sort-icon">Last Executed</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Table rows will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="heartbeatDetailsModal" class="heartbeat-details-modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="header-content">
                <i class="fa fa-tasks"></i>
                <h2>Heartbeat Details</h2>
            </div>
            <span class="close-modal"><i class="fa fa-times"></i></span>
        </div>
        <div class="modal-body">
            <div class="status-banner" id="taskStatusBanner">
                <span id="taskStatus">-</span>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <i class="fa fa-clock-o"></i>
                    <div class="detail-content">
                        <span class="label">Start Time</span>
                        <span id="taskStartTime" class="value">-</span>
                    </div>
                </div>
                <div class="detail-item">
                    <i class="fa fa-flag-checkered"></i>
                    <div class="detail-content">
                        <span class="label">End Time</span>
                        <span id="taskEndTime" class="value">-</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-grid">
                <div class="detail-item">
                    <i class="fa fa-hourglass-half"></i>
                    <div class="detail-content">
                        <span class="label">Execution Time</span>
                        <span id="taskExecutionTime" class="value">-</span>
                    </div>
                </div>
                <div class="detail-item">
                    <i class="fa fa-refresh"></i>
                    <div class="detail-content">
                        <span class="label">Attempts</span>
                        <span id="taskAttempts" class="value">-</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-sections">
                <div class="detail-section">
                    <div class="section-header">
                        <i class="fa fa-code"></i>
                        <h3>Metadata</h3>
                    </div>
                    <pre id="taskMetadata" class="code-block"></pre>
                </div>
                
                <div class="detail-section">
                    <div class="section-header">
                        <i class="fa fa-exclamation-triangle"></i>
                        <h3>Fail Details</h3>
                    </div>
                    <pre id="taskFailDetails" class="code-block"></pre>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="modal-close-btn">
                <i class="fa fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

<script>

    on_script_load("jQuery", function() {
        elog('here', jQuery(document));
        
        $(document).ready(function() {
            elog('heartbeat loaded');
            HeartBeats.data = <?php echo json_encode($heartbeats); ?>;
            HeartBeats.init();
        });
    });
    
</script>