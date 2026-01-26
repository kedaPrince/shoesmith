<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-user-circle"></i> 
                    <?php echo $heading; ?>
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('agency/candidates/view/' . $candidate->uuid); ?>" class="btn btn-sm btn-default">
                        <i class="fa fa-arrow-left"></i> Back to Candidate
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fa fa-envelope"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Email Address</span>
                                <span class="info-box-number"><?php echo htmlspecialchars($contact_info['email']); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    Full access granted
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fa fa-phone"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Phone Number</span>
                                <span class="info-box-number"><?php echo htmlspecialchars($contact_info['phone']); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    Full access granted
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($contact_info['alternate_phone'])): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fa fa-mobile-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Alternate Phone</span>
                                <span class="info-box-number"><?php echo htmlspecialchars($contact_info['alternate_phone']); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    Alternate contact number
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <h5><i class="fa fa-info-circle"></i> Important Information</h5>
                    <ul>
                        <li>This contact information is confidential and should only be used for legitimate hiring purposes.</li>
                        <li>Do not share this information with unauthorized parties.</li>
                        <li>Always respect the candidate's privacy and follow data protection regulations.</li>
                        <li>Access will be logged for audit purposes.</li>
                    </ul>
                </div>
                
                <div class="text-center mt-4">
                    <a href="mailto:<?php echo htmlspecialchars($contact_info['email']); ?>" class="btn btn-primary">
                        <i class="fa fa-envelope"></i> Send Email
                    </a>
                    <a href="tel:<?php echo htmlspecialchars($contact_info['phone']); ?>" class="btn btn-success ml-2">
                        <i class="fa fa-phone"></i> Call Candidate
                    </a>
                    <a href="<?php echo site_url('agency/candidates/view/' . $candidate->uuid); ?>" class="btn btn-default ml-2">
                        <i class="fa fa-user"></i> Back to Profile
                    </a>
                </div>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fa fa-history"></i> Access granted on: 
                    <?php echo date('F j, Y \a\t g:i A'); ?>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Log access view
    $.ajax({
        url: '<?php echo site_url("agency/candidates/log_contact_access/' . $candidate->id . '"); ?>',
        type: 'POST',
        data: {
            '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
        }
    });
});
</script>