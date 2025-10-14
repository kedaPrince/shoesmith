<?php defined('BASEPATH') || exit('No direct script access allowed'); 

// Helper functions for this view
function get_file_icon($extension) {
    $icons = [
        'pdf' => 'pdf',
        'doc' => 'word',
        'docx' => 'word',
        'txt' => 'text',
    ];
    
    return isset($icons[strtolower($extension)]) ? $icons[strtolower($extension)] : 'file';
}

function truncate_filename($filename, $length = 25) {
    if (strlen($filename) <= $length) {
        return $filename;
    }
    
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $max_name_length = $length - strlen($extension) - 1; // -1 for the dot
    
    if (strlen($name) > $max_name_length) {
        $name = substr($name, 0, $max_name_length - 3) . '...';
    }
    
    return $name . '.' . $extension;
}

function get_status_badge($status) {
    $badges = [
        'new' => 'primary',
        'reviewed' => 'info',
        'shortlisted' => 'success',
        'interviewed' => 'warning',
        'rejected' => 'danger',
        'hired' => 'dark',
        'on_hold' => 'secondary',
    ];
    
    return isset($badges[$status]) ? $badges[$status] : 'secondary';
}
?>

<div class="resume-listing-container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-file-text"></i>
                        <?= lang('candidates_resume_listings_heading') ?>
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-primary" id="cv-count">
                            <?= $query->num_rows() ?> <?= lang('label_cvs_found') ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if ($query->num_rows() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="10%"><?= lang('label_reference_number') ?></th>
                                    <th width="15%"><?= lang('label_candidate') ?></th>
                                    <th width="15%"><?= lang('label_contact') ?></th>
                                    <th width="25%"><?= lang('label_cv_file') ?></th>
                                    <th width="15%"><?= lang('label_application_date') ?></th>
                                    <th width="15%"><?= lang('label_status') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $count = 1;
                                    foreach ($query->result() as $row): 
                                        $cv_extension = pathinfo($row->cv_file, PATHINFO_EXTENSION);
                                        $cv_size = file_exists(FCPATH . 'uploads/candidates/cv/' . $row->cv_file) ? 
                                                   round(filesize(FCPATH . 'uploads/candidates/cv/' . $row->cv_file) / 1024, 1) : 0;
                                    ?>
                                <tr data-candidate-id="<?= $row->id ?>">
                                    <td><?= $count++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row->reference_number) ?></strong>
                                    </td>
                                    <td>
                                        <div class="candidate-info">
                                            <strong><?= htmlspecialchars($row->first_name . ' ' . $row->last_name) ?></strong>
                                            <?php if (!empty($row->job_name)): ?>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fa fa-briefcase"></i>
                                                <?= htmlspecialchars($row->job_name) ?>
                                            </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div>
                                                <i class="fa fa-envelope"></i>
                                                <?= htmlspecialchars($row->email) ?>
                                            </div>
                                            <?php if (!empty($row->phone)): ?>
                                            <div>
                                                <i class="fa fa-phone"></i>
                                                <?= htmlspecialchars($row->phone) ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="cv-file-section">
                                            <div class="cv-info-container">
                                                <div class="cv-file-details">
                                                    <div class="file-icon">
                                                        <i
                                                            class="fa fa-file-<?= get_file_icon($cv_extension) ?>-o fa-2x text-primary"></i>
                                                    </div>
                                                    <div class="file-meta">
                                                        <div class="file-name"
                                                            title="<?= htmlspecialchars($row->cv_file) ?>">
                                                            <strong><?= truncate_filename($row->cv_file, 30) ?></strong>
                                                        </div>
                                                        <div class="file-info">
                                                            <span
                                                                class="file-type badge badge-light"><?= strtoupper($cv_extension) ?></span>
                                                            <span class="file-size text-muted"><?= $cv_size ?> KB</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="download-action">
                                                    <a href="<?= site_url('recruiter/candidates_resume_listings/download_cv/' . $row->id) ?>"
                                                        class="btn btn-sm btn-primary download-cv-btn"
                                                        title="Download CV">
                                                        <i class="fa fa-download"></i>
                                                        Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= !empty($row->application_date) ? date('M j, Y', strtotime($row->application_date)) : 'N/A' ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= get_status_badge($row->status) ?>">
                                            <?= ucfirst($row->status) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center p-5">
                        <i class="fa fa-file-text-o fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted"><?= lang('candidates_resume_listings_no_rows') ?></h4>
                        <p class="text-muted"><?= lang('candidates_resume_listings_no_cvs_description') ?></p>
                        <!-- REMOVED: Add Candidate button -->
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Download CV with progress indicator
    $('.download-cv-btn').on('click', function(e) {
        const $btn = $(this);
        const originalHtml = $btn.html();

        // Show loading state immediately
        $btn.html(`
            <i class="fa fa-spinner fa-spin"></i>
            Downloading...
        `);
        $btn.prop('disabled', true);

        // Let the browser handle the download naturally
        // The page will navigate to the download URL

        // Restore original content after 3 seconds (in case download is quick)
        setTimeout(function() {
            $btn.html(originalHtml);
            $btn.prop('disabled', false);
        }, 3000);
    });

    // Update CV count
    function updateCvCount() {
        const count = $('tbody tr').length;
        $('#cv-count').text(count + ' <?= lang('label_cvs_found') ?>');
    }

    // Initialize
    updateCvCount();
});
</script>

<style>
.resume-listing-container .candidate-info {
    line-height: 1.2;
}

.resume-listing-container .contact-info {
    font-size: 0.875rem;
    line-height: 1.3;
}

.cv-file-section {
    padding: 8px 0;
}

.cv-info-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.cv-file-details {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.file-icon {
    flex-shrink: 0;
}

.file-meta {
    flex: 1;
    min-width: 0;
    /* Allow text truncation */
}

.file-name {
    margin-bottom: 4px;
}

.file-name strong {
    font-size: 0.9rem;
    color: #333;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8rem;
}

.file-type {
    font-size: 0.7rem;
    padding: 2px 6px;
}

.download-action {
    flex-shrink: 0;
}

.download-cv-btn {
    white-space: nowrap;
    transition: all 0.2s ease;
}

.download-cv-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Make the entire CV section slightly interactive */
.cv-info-container:hover .file-name strong {
    color: #007bff;
}

@media (max-width: 768px) {
    .resume-listing-container .table-responsive {
        font-size: 0.875rem;
    }

    .cv-info-container {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .download-action {
        align-self: stretch;
    }

    .download-cv-btn {
        width: 100%;
        text-align: center;
    }
}
</style>