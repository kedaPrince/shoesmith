<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="content-header">
    <h1>
        <?= !empty($heading) ? $heading : 'View Candidate' ?>
    </h1>
</div>

<div class="content">
    <div class="box">
        <div class="box-body">
            <?php if (!empty($row)): ?>
            <dl class="dl-horizontal">
                <dt>Reference Number:</dt>
                <dd><?= htmlspecialchars($row->reference_number, ENT_QUOTES, 'UTF-8') ?></dd>

                <dt>Name:</dt>
                <dd><?= htmlspecialchars($row->first_name . ' ' . $row->row->last_name, ENT_QUOTES, 'UTF-8') ?></dd>

                <dt>Email:</dt>
                <dd><?= htmlspecialchars($row->email, ENT_QUOTES, 'UTF-8') ?></dd>

                <dt>Status:</dt>
                <dd><?= htmlspecialchars($row->status, ENT_QUOTES, 'UTF-8') ?></dd>

                <dt>Application Date:</dt>
                <dd><?= !empty($row->application_date) ? date('Y-m-d', strtotime($row->application_date)) : '' ?></dd>

                <!-- Add more fields as needed -->
            </dl>
            <?php else: ?>
            <p>Candidate not found.</p>
            <?php endif; ?>
        </div>
        <div class="box-footer">
            <a href="<?= site_url('recruiter/candidates') ?>" class="btn btn-default">Back to List</a>
        </div>
    </div>
</div>