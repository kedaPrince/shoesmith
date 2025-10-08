<div class="page-header">
    <h1><?= $heading ?></h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h2>Candidate Information</h2>
            </div>
            <div class="body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Reference Number:</strong>
                            <?= htmlspecialchars($candidate->reference_number, ENT_QUOTES, 'UTF-8') ?></p>
                        <p><strong>Name:</strong> <?= htmlspecialchars($candidate->first_name, ENT_QUOTES, 'UTF-8') ?>
                            <?= htmlspecialchars($candidate->last_name, ENT_QUOTES, 'UTF-8') ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($candidate->email, ENT_QUOTES, 'UTF-8') ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($candidate->phone, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <?= ucfirst($candidate->status) ?></p>
                        <p><strong>Application Date:</strong>
                            <?= date('j M Y', strtotime($candidate->application_date)) ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($candidate->location, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                </div>

                <?php if (!empty($candidate->cover_letter)): ?>
                <div class="row">
                    <div class="col-md-12">
                        <h4>Cover Letter</h4>
                        <div class="well">
                            <?= nl2br(htmlspecialchars($candidate->cover_letter, ENT_QUOTES, 'UTF-8')) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">
                        <a href="<?= site_url('agency/candidates_list') ?>" class="btn btn-default">Back to
                            Candidates</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>