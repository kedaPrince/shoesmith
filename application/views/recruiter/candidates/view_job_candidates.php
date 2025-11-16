<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <!-- Header Section -->
    <header class="page-header">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="<?php echo site_url('recruiter/dashboard'); ?>">
                                    <i class="fa fa-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?php echo site_url('recruiter/jobs'); ?>">Jobs</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?php echo site_url('recruiter/jobs/view/' . $job->id); ?>">
                                    <?php echo htmlspecialchars($job->name); ?>
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Candidates
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Quick Manage Style Container -->
                <div class="quick-manage-form-container">
                    <div class="quick-manage-heading">
                        <h2>
                            <i class="fa fa-users"></i>
                            <?php echo $heading; ?>
                        </h2>
                        <p class="text-muted mb-0">
                            Reference: <strong><?php echo htmlspecialchars($job->reference_number); ?></strong> |
                            Candidates: <strong><?php echo count($candidates); ?></strong>
                        </p>
                    </div>

                    <!-- Job Info Card -->
                    <div class="form-field-container">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5><i class="fa fa-info-circle text-primary"></i> Job Details</h5>
                                        <p><strong>Department:</strong>
                                            <?php echo htmlspecialchars($job->department ?: 'Not specified'); ?></p>
                                        <p><strong>Employment Type:</strong>
                                            <?php echo ucfirst($job->employment_type); ?></p>
                                        <p><strong>Industry:</strong>
                                            <?php echo htmlspecialchars(isset($job->industry_name) ? $job->industry_name : ($job->industry_id ? 'Industry #' . $job->industry_id : 'Not specified')); ?>
                                        </p>
                                        <p><strong>Agency:</strong>
                                            <?php echo htmlspecialchars(isset($job->agency_name) ? $job->agency_name : ($job->agency_id ? 'Agency #' . $job->agency_id : 'Not specified')); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5><i class="fa fa-cog text-success"></i> Actions</h5>
                                        <div class="action-buttons">
                                            <a href="<?php echo site_url('recruiter/candidates/add/' . $job->id); ?>"
                                                class="btn btn-success btn-sm mb-2">
                                                <i class="fa fa-user-plus"></i> Add Candidate to this Job
                                            </a>
                                            <a href="<?php echo site_url('recruiter/jobs/view/' . $job->id); ?>"
                                                class="btn btn-info btn-sm mb-2">
                                                <i class="fa fa-eye"></i> View Job Details
                                            </a>
                                            <a href="<?php echo site_url('recruiter/jobs'); ?>"
                                                class="btn btn-secondary btn-sm mb-2">
                                                <i class="fa fa-arrow-left"></i> Back to Jobs
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Candidates Listing -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fa fa-list"></i>
                                    Candidates (<?php echo count($candidates); ?>)
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($candidates)): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Reference</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Application Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($candidates as $candidate): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($candidate->reference_number); ?></strong>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($candidate->email); ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                            switch($candidate->status) {
                                                                case 'new': echo 'secondary'; break;
                                                                case 'reviewed': echo 'info'; break;
                                                                case 'shortlisted': echo 'warning'; break;
                                                                case 'interviewed': echo 'primary'; break;
                                                                case 'hired': echo 'success'; break;
                                                                case 'rejected': echo 'danger'; break;
                                                                default: echo 'secondary';
                                                            }
                                                        ?>">
                                                        <?php echo ucfirst($candidate->status); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo $candidate->application_date ? date('M j, Y', strtotime($candidate->application_date)) : 'N/A'; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?php echo site_url('recruiter/candidates/view/' . $candidate->id); ?>"
                                                            class="btn btn-info" title="View Candidate">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <a href="<?php echo site_url('recruiter/candidates/edit/' . $candidate->id); ?>"
                                                            class="btn btn-warning" title="Edit Candidate">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fa fa-users fa-4x text-muted mb-4"></i>
                                    <h4>No Candidates Found</h4>
                                    <p class="text-muted mb-4">There are no candidates assigned to this job yet.</p>
                                    <a href="<?php echo site_url('recruiter/candidates/add/' . $job->id); ?>"
                                        class="btn btn-success btn-lg">
                                        <i class="fa fa-user-plus"></i> Add First Candidate
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quick-manage-form-container {
    background: var(--card-color);
    border-radius: 8px;
    padding: 25px;
    border: 1px solid var(--border-color);
    margin-bottom: 20px;
}

.quick-manage-heading {
    margin-bottom: 25px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 15px;
}

.quick-manage-heading h2 {
    color: var(--primary-color);
    margin-bottom: 5px;
    font-size: 1.8rem;
}

.form-field-container {
    margin-top: 20px;
}

.card {
    border: 1px solid var(--border-color);
    border-radius: 8px;
}

.card-header {
    background: var(--light-bg) !important;
    border-bottom: 1px solid var(--border-color);
    padding: 15px 20px;
}

.card-header h5 {
    color: var(--font-color);
    margin-bottom: 0;
    font-weight: 600;
}

.table th {
    background: var(--light-bg);
    border-bottom: 1px solid var(--border-color);
    font-weight: 600;
    color: var(--font-color);
}

.btn-group .btn {
    border-radius: 4px;
    margin-right: 5px;
}

.action-buttons .btn {
    margin-right: 8px;
    margin-bottom: 8px;
}

.breadcrumb {
    background: transparent;
    margin-bottom: 0;
    padding: 0;
}

.page-header {
    padding: 20px 0;
    margin-bottom: 20px;
}

.page-header h1 {
    color: var(--font-color);
    margin-bottom: 5px;
    font-size: 1.8rem;
}

.text-muted {
    color: var(--muted-color) !important;
}

/* Badge colors matching your theme */
.badge-secondary {
    background-color: #6c757d;
}

.badge-info {
    background-color: #17a2b8;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-primary {
    background-color: #007bff;
}

.badge-success {
    background-color: #28a745;
}

.badge-danger {
    background-color: #dc3545;
}
</style>