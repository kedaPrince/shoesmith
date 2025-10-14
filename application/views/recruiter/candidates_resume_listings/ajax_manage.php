<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

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
                                    <th width="20%"><?= lang('label_cv_file') ?></th>
                                    <th width="10%"><?= lang('label_application_date') ?></th>
                                    <th width="10%"><?= lang('label_status') ?></th>
                                    <th width="15%"><?= lang('label_actions') ?></th>
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
                                        <div class="cv-info">
                                            <div class="file-type">
                                                <i class="fa fa-file-<?= $this->get_file_icon($cv_extension) ?>-o"></i>
                                                <span class="text-uppercase"><?= $cv_extension ?></span>
                                            </div>
                                            <div class="file-size">
                                                <small class="text-muted"><?= $cv_size ?> KB</small>
                                            </div>
                                            <div class="file-name">
                                                <small title="<?= htmlspecialchars($row->cv_file) ?>">
                                                    <?= $this->truncate_filename($row->cv_file, 25) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= !empty($row->application_date) ? date('M j, Y', strtotime($row->application_date)) : 'N/A' ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $this->get_status_badge($row->status) ?>">
                                            <?= ucfirst($row->status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= site_url('recruiter/candidates_resume_listings/download_cv/' . $row->id) ?>"
                                                class="btn btn-sm btn-primary download-cv"
                                                title="<?= lang('label_download_cv') ?>">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-info preview-cv"
                                                data-candidate-id="<?= $row->id ?>"
                                                data-candidate-name="<?= htmlspecialchars($row->first_name . ' ' . $row->last_name) ?>"
                                                title="<?= lang('label_preview_cv') ?>">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <a href="<?= site_url('recruiter/candidates/view/' . $row->id) ?>"
                                                class="btn btn-sm btn-secondary view-candidate"
                                                title="<?= lang('label_view_candidate') ?>">
                                                <i class="fa fa-user"></i>
                                            </a>
                                        </div>
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
                        <a href="<?= site_url('recruiter/candidates/add') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i>
                            <?= lang('label_add_candidate') ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CV Preview Modal -->
<div class="modal fade" id="cvPreviewModal" tabindex="-1" role="dialog" aria-labelledby="cvPreviewModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cvPreviewModalLabel">
                    <i class="fa fa-file-text"></i>
                    <?= lang('label_cv_preview') ?>: <span id="candidate-name"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="cv-preview-container">
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only"><?= lang('label_loading') ?>...</span>
                        </div>
                        <p class="mt-2"><?= lang('label_loading_cv') ?>...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="download-cv-link" class="btn btn-primary">
                    <i class="fa fa-download"></i>
                    <?= lang('label_download_cv') ?>
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?= lang('label_close') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // CV Preview functionality
    $('.preview-cv').on('click', function() {
        const candidateId = $(this).data('candidate-id');
        const candidateName = $(this).data('candidate-name');

        $('#candidate-name').text(candidateName);
        $('#download-cv-link').attr('href',
            '<?= site_url("recruiter/candidates_resume_listings/download_cv/") ?>' + candidateId);

        // Show loading state
        $('#cv-preview-container').html(`
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only"><?= lang('label_loading') ?>...</span>
                </div>
                <p class="mt-2"><?= lang('label_loading_cv') ?>...</p>
            </div>
        `);

        // Load CV preview
        $.get('<?= site_url("recruiter/candidates_resume_listings/preview_cv/") ?>' + candidateId)
            .done(function(response) {
                // For PDF files, we'll use an iframe
                const cvUrl =
                    '<?= site_url("recruiter/candidates_resume_listings/preview_cv/") ?>' +
                    candidateId;
                $('#cv-preview-container').html(`
                    <iframe src="${cvUrl}" width="100%" height="600px" frameborder="0">
                        <p><?= lang('label_browser_no_support') ?></p>
                    </iframe>
                `);
            })
            .fail(function() {
                $('#cv-preview-container').html(`
                    <div class="alert alert-danger text-center">
                        <i class="fa fa-exclamation-triangle"></i>
                        <?= lang('error_loading_cv') ?>
                    </div>
                `);
            });

        $('#cvPreviewModal').modal('show');
    });

    // Download CV with progress indicator
    $('.download-cv').on('click', function(e) {
        const $btn = $(this);
        const originalHtml = $btn.html();

        $btn.prop('disabled', true).html(
            '<i class="fa fa-spinner fa-spin"></i> <?= lang('label_downloading') ?>...');

        setTimeout(function() {
            $btn.prop('disabled', false).html(originalHtml);
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

.resume-listing-container .cv-info {
    font-size: 0.875rem;
}

.resume-listing-container .file-type {
    font-weight: bold;
}

.resume-listing-container .file-size {
    color: #6c757d;
}

.resume-listing-container .file-name {
    margin-top: 2px;
}

.badge-new {
    background-color: #007bff;
}

.badge-reviewed {
    background-color: #17a2b8;
}

.badge-shortlisted {
    background-color: #28a745;
}

.badge-interviewed {
    background-color: #ffc107;
    color: #212529;
}

.badge-rejected {
    background-color: #dc3545;
}

.badge-hired {
    background-color: #6610f2;
}

.badge-on_hold {
    background-color: #6c757d;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

#cvPreviewModal .modal-xl {
    max-width: 90%;
}

@media (max-width: 768px) {
    .resume-listing-container .table-responsive {
        font-size: 0.875rem;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>