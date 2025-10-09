<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Candidate Details</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= site_url('agency/dashboard') ?>"><i class="fa fa-dashboard"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= site_url('agency/jobs_listings') ?>" title="Jobs Listings">Jobs Listings</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= site_url('agency/candidates_list/index/' . $candidate->job_id) ?>"
                                title="Candidates">Candidates</a>
                        </li>
                        <li class="breadcrumb-item active">Candidate Details</li>
                    </ul>
                </div>
                <!-- <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action">
                            <a href="<?= site_url('agency/candidates_list/edit/' . $candidate->id) ?>"
                                class="btn btn-primary">
                                <i class="fa fa-edit"></i> Edit Candidate
                            </a>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>

        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div class="header">
                        <h2>
                            <i class="fa fa-user"></i>
                            <?= htmlspecialchars($candidate->first_name . ' ' . $candidate->last_name, ENT_QUOTES, 'UTF-8'); ?>
                            <small>Reference:
                                <?= htmlspecialchars($candidate->reference_number, ENT_QUOTES, 'UTF-8') ?></small>
                        </h2>
                        <ul class="header-dropdown">
                            <li>
                                <span class="badge badge-<?= get_status_badge($candidate->status) ?>">
                                    <?= ucfirst($candidate->status) ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="body">
                        <div class="row clearfix">
                            <div class="col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="header">
                                        <h2>Personal Information</h2>
                                    </div>
                                    <div class="body">
                                        <table class="table table-hover">
                                            <tr>
                                                <td width="40%"><strong>Reference Number</strong></td>
                                                <td><?= htmlspecialchars($candidate->reference_number, ENT_QUOTES, 'UTF-8') ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Full Name</strong></td>
                                                <td><?= htmlspecialchars($candidate->first_name, ENT_QUOTES, 'UTF-8') ?>
                                                    <?= htmlspecialchars($candidate->last_name, ENT_QUOTES, 'UTF-8') ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email</strong></td>
                                                <td>
                                                    <a
                                                        href="mailto:<?= htmlspecialchars($candidate->email, ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars($candidate->email, ENT_QUOTES, 'UTF-8') ?>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Phone</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->phone)): ?>
                                                    <?= htmlspecialchars($candidate->phone, ENT_QUOTES, 'UTF-8') ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">Not provided</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>ID Number</strong></td>
                                                <td>
                                                    <?= !empty($candidate->id_number) ? htmlspecialchars($candidate->id_number, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Date of Birth</strong></td>
                                                <td>
                                                    <?= !empty($candidate->date_of_birth) ? date('j M Y', strtotime($candidate->date_of_birth)) : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Gender</strong></td>
                                                <td>
                                                    <?= !empty($candidate->gender) ? ucfirst($candidate->gender) : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="header">
                                        <h2>Application Details</h2>
                                    </div>
                                    <div class="body">
                                        <table class="table table-hover">
                                            <tr>
                                                <td width="40%"><strong>Job Applied For</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->job_name)): ?>
                                                    <?= htmlspecialchars($candidate->job_name, ENT_QUOTES, 'UTF-8') ?>
                                                    <?php if (!empty($candidate->job_ref)): ?>
                                                    <br><small class="text-muted">Ref:
                                                        <?= htmlspecialchars($candidate->job_ref, ENT_QUOTES, 'UTF-8') ?></small>
                                                    <?php endif; ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">Not assigned</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Application Date</strong></td>
                                                <td><?= date('j M Y', strtotime($candidate->application_date)) ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status</strong></td>
                                                <td>
                                                    <span
                                                        class="badge badge-<?= get_status_badge($candidate->status) ?>">
                                                        <?= ucfirst($candidate->status) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Source</strong></td>
                                                <td>
                                                    <?= !empty($candidate->source) ? ucfirst($candidate->source) : '<span class="text-muted">Not specified</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Rating</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->rating)): ?>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i
                                                        class="fa fa-star<?= $i <= $candidate->rating ? ' text-warning' : ' text-muted' ?>"></i>
                                                    <?php endfor; ?>
                                                    (<?= $candidate->rating ?>/5)
                                                    <?php else: ?>
                                                    <span class="text-muted">Not rated</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row clearfix">
                            <div class="col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="header">
                                        <h2>Professional Information</h2>
                                    </div>
                                    <div class="body">
                                        <table class="table table-hover">
                                            <tr>
                                                <td width="40%"><strong>Highest Qualification</strong></td>
                                                <td><?= !empty($candidate->highest_qualification) ? htmlspecialchars($candidate->highest_qualification, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Years of Experience</strong></td>
                                                <td><?= !empty($candidate->years_experience) ? $candidate->years_experience . ' years' : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Current Position</strong></td>
                                                <td><?= !empty($candidate->current_position) ? htmlspecialchars($candidate->current_position, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Current Company</strong></td>
                                                <td><?= !empty($candidate->current_company) ? htmlspecialchars($candidate->current_company, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not provided</span>' ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <div class="card">
                                    <div class="header">
                                        <h2>Salary Information</h2>
                                    </div>
                                    <div class="body">
                                        <table class="table table-hover">
                                            <tr>
                                                <td width="40%"><strong>Current Salary</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->current_salary) && $candidate->current_salary > 0): ?>
                                                    R <?= number_format($candidate->current_salary, 2) ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">Not provided</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Expected Salary</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->expected_salary) && $candidate->expected_salary > 0): ?>
                                                    R <?= number_format($candidate->expected_salary, 2) ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">Not provided</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Notice Period</strong></td>
                                                <td>
                                                    <?php if (!empty($candidate->notice_period)): ?>
                                                    <?= $candidate->notice_period ?> days
                                                    <?php else: ?>
                                                    <span class="text-muted">Not provided</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($candidate->cover_letter)): ?>
                        <div class="row clearfix">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="header">
                                        <h2>Cover Letter</h2>
                                    </div>
                                    <div class="body">
                                        <div class="alert alert-info">
                                            <?= nl2br(htmlspecialchars($candidate->cover_letter, ENT_QUOTES, 'UTF-8')) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($candidate->notes)): ?>
                        <div class="row clearfix">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="header">
                                        <h2>Internal Notes</h2>
                                    </div>
                                    <div class="body">
                                        <div class="alert alert-warning">
                                            <?= nl2br(htmlspecialchars($candidate->notes, ENT_QUOTES, 'UTF-8')) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($candidate->cv_file)): ?>
                        <div class="row clearfix">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="header">
                                        <h2>CV / Resume</h2>
                                    </div>
                                    <div class="body">
                                        <a href="<?= base_url('uploads/' . $candidate->cv_file) ?>" target="_blank"
                                            class="btn btn-primary">
                                            <i class="fa fa-download"></i> Download CV
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="footer">
                        <a href="<?= site_url('agency/candidates_list/index/' . $candidate->job_id) ?>"
                            class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to Candidates
                        </a>

                        <?php if ($candidate->enabled): ?>
                        <a href="<?= site_url('agency/candidates_list/disable/' . $candidate->id) ?>"
                            class="btn btn-warning">
                            <i class="fa fa-eye-slash"></i> Disable
                        </a>
                        <?php else: ?>
                        <a href="<?= site_url('agency/candidates_list/enable/' . $candidate->id) ?>"
                            class="btn btn-success">
                            <i class="fa fa-eye"></i> Enable
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function for status badges
function get_status_badge($status) {
    switch ($status) {
        case 'new': return 'secondary';
        case 'reviewed': return 'info';
        case 'shortlisted': return 'warning';
        case 'interviewed': return 'primary';
        case 'hired': return 'success';
        case 'rejected': return 'danger';
        case 'on_hold': return 'dark';
        default: return 'secondary';
    }
}
?>