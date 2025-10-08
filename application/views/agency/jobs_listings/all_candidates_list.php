<div class="page-header">
    <h1><?= lang('candidates_heading') ?></h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="body">
                <div class="table-responsive">
                    <table class="table table-hover dataTable">
                        <thead>
                            <tr>
                                <th><?= lang('label_reference_number') ?></th>
                                <th><?= lang('label_first_name') ?></th>
                                <th><?= lang('label_last_name') ?></th>
                                <th><?= lang('label_email') ?></th>
                                <th><?= lang('label_job') ?></th>
                                <th><?= lang('label_status') ?></th>
                                <th><?= lang('label_application_date') ?></th>
                                <th><?= lang('label_actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($candidates)): ?>
                            <tr>
                                <td colspan="8" class="text-center"><?= lang('candidates_no_rows') ?></td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($candidates as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c->reference_number, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($c->first_name, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($c->last_name, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($c->email, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if (!empty($c->job_name)): ?>
                                    <?= htmlspecialchars($c->job_name, ENT_QUOTES, 'UTF-8') ?>
                                    (<?= htmlspecialchars($c->job_ref, ENT_QUOTES, 'UTF-8') ?>)
                                    <?php else: ?>
                                    <em><?= lang('label_not_assigned') ?></em>
                                    <?php endif; ?>
                                </td>
                                <td><?= ucfirst($c->status) ?></td>
                                <td><?= date('j M Y', strtotime($c->application_date)) ?></td>
                                <td>
                                    <a href="<?= site_url("agency/candidates/view/{$c->id}") ?>"
                                        class="btn btn-sm btn-info"><?= lang('label_view') ?></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>