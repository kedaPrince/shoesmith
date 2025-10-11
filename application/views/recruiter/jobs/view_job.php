<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <!-- Header Section (Breadcrumbs & Title) -->
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
                            <li class="breadcrumb-item active" aria-current="page">
                                <?php echo htmlspecialchars($job->name); ?>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area with Flex Layout -->
    <div id="main-job-layout">
        <!-- Left Navigation Sidebar (20%) -->
        <nav class="job-sidebar-left">
            <div class="sidebar-content">
                <div class="job-actions-card">
                    <h6><i class="fa fa-cog mr-2"></i>Job Actions</h6>
                    <div class="action-buttons">

                        <a href="<?php echo site_url('recruiter/jobs'); ?>" class="btn btn-secondary btn-block">
                            <i class="fa fa-arrow-left"></i> Back to Jobs
                        </a>
                    </div>
                </div>

                <div class="job-quick-info">
                    <h6><i class="fa fa-info-circle mr-2"></i>Quick Info</h6>
                    <div class="info-list">
                        <div class="info-item">
                            <small>Reference</small>
                            <strong><?php echo htmlspecialchars($job->reference_number); ?></strong>
                        </div>
                        <div class="info-item">
                            <small>Status</small>
                            <span class="badge badge-success">Active</span>
                        </div>
                        <div class="info-item">
                            <small>Type</small>
                            <strong>
                                <?php 
                                $employment_types = [
                                    'full-time' => 'Full Time',
                                    'part-time' => 'Part Time', 
                                    'contract' => 'Contract',
                                    'internship' => 'Internship',
                                    'temporary' => 'Temporary'
                                ];
                                echo $employment_types[$job->employment_type] ?? $job->employment_type;
                                ?>
                            </strong>
                        </div>
                        <div class="info-item">
                            <small>Remote</small>
                            <?php echo $job->is_remote ? 
                                '<span class="badge badge-success">Yes</span>' : 
                                '<span class="badge badge-secondary">No</span>'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Article Content (60%) -->
        <article class="job-main-content">
            <div class="content-header">
                <h1><?php echo htmlspecialchars($job->name); ?></h1>
                <p class="text-muted"><?php echo htmlspecialchars($job->department ?? 'No department specified'); ?></p>
            </div>

            <!-- Tab Navigation -->
            <div class="job-tabs-navigation">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#project-overview">
                            <i class="fa fa-project-diagram"></i> Project Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#job-description">
                            <i class="fa fa-file-alt"></i> Job Description
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#requirements">
                            <i class="fa fa-tasks"></i> Requirements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#application">
                            <i class="fa fa-paper-plane"></i> Application
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="job-tabs-content">
                <div class="tab-content">
                    <!-- Project Overview Tab -->
                    <div class="tab-pane fade show active" id="project-overview" role="tabpanel">
                        <div class="tab-section">
                            <h5><i class="fa fa-project-diagram text-primary mr-2"></i>Project Overview</h5>
                            <div class="project-content">
                                <?php if (!empty($job->project_overview)): ?>
                                <?php echo nl2br(htmlspecialchars($job->project_overview)); ?>
                                <?php else: ?>
                                <p class="text-muted"><em>No project overview provided</em></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Job Description Tab -->
                    <div class="tab-pane fade" id="job-description" role="tabpanel">
                        <div class="tab-section">
                            <h5><i class="fa fa-file-alt text-primary mr-2"></i>Job Description</h5>
                            <div class="description-content">
                                <?php echo !empty($job->description) ? nl2br(htmlspecialchars($job->description)) : '<p class="text-muted"><em>No description provided</em></p>'; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Requirements Tab -->
                    <div class="tab-pane fade" id="requirements" role="tabpanel">
                        <div class="requirements-grid">
                            <div class="requirement-column">
                                <h6><i class="fa fa-cogs text-primary"></i> Skills Required</h6>
                                <?php if (!empty($skills) && $skill_options && $skill_options->num_rows() > 0): ?>
                                <?php
                        $skill_names = [];
                        foreach ($skill_options->result() as $skill) {
                            $skill_names[$skill->id] = $skill->name;
                        }
                        ?>
                                <div class="skills-list">
                                    <?php foreach ($skills as $skill_id): ?>
                                    <?php if (isset($skill_names[$skill_id])): ?>
                                    <span class="skill-tag">
                                        <i class="fa fa-check"></i>
                                        <?php echo htmlspecialchars($skill_names[$skill_id]); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-muted">No specific skills required</p>
                                <?php endif; ?>
                            </div>

                            <div class="requirement-column">
                                <h6><i class="fa fa-graduation-cap text-success"></i> Qualifications</h6>
                                <?php if (!empty($qualifications) && $qualification_options && $qualification_options->num_rows() > 0): ?>
                                <?php
                        $qualification_names = [];
                        foreach ($qualification_options->result() as $qualification) {
                            $qualification_names[$qualification->id] = $qualification->name;
                        }
                        ?>
                                <div class="qualifications-list">
                                    <?php foreach ($qualifications as $qualification_id): ?>
                                    <?php if (isset($qualification_names[$qualification_id])): ?>
                                    <span class="qualification-tag">
                                        <i class="fa fa-award"></i>
                                        <?php echo htmlspecialchars($qualification_names[$qualification_id]); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-muted">No specific qualifications required</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Application Tab -->
                    <div class="tab-pane fade" id="application" role="tabpanel">
                        <div class="application-details">
                            <?php if (!empty($job->application_email)): ?>
                            <div class="application-method">
                                <i class="fa fa-envelope fa-2x text-primary"></i>
                                <div class="method-details">
                                    <h6>Apply via Email</h6>
                                    <a href="mailto:<?php echo htmlspecialchars($job->application_email); ?>"
                                        class="application-link">
                                        <?php echo htmlspecialchars($job->application_email); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($job->application_url)): ?>
                            <div class="application-method">
                                <i class="fa fa-globe fa-2x text-info"></i>
                                <div class="method-details">
                                    <h6>Apply Online</h6>
                                    <a href="<?php echo htmlspecialchars($job->application_url); ?>" target="_blank"
                                        class="application-link">
                                        Visit Application Portal
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($job->closing_date)): ?>
                            <div class="closing-date">
                                <i class="fa fa-calendar fa-2x text-warning"></i>
                                <div class="date-details">
                                    <h6>Closing Date</h6>
                                    <span
                                        class="<?php echo strtotime($job->closing_date) < time() ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo date('F j, Y', strtotime($job->closing_date)); ?>
                                        <?php if (strtotime($job->closing_date) < time()): ?>
                                        <small class="d-block">(Expired)</small>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <!-- Right Aside Sidebar (20%) -->
        <aside class="job-sidebar-right">
            <div class="sidebar-content">
                <div class="job-details-card">
                    <h6><i class="fa fa-building mr-2"></i>Company Details</h6>
                    <div class="details-list">
                        <div class="detail-item">
                            <small>Agency</small>
                            <strong><?php echo htmlspecialchars($job->agency_name ?? 'Not specified'); ?></strong>
                        </div>
                        <div class="detail-item">
                            <small>Industry</small>
                            <strong><?php echo htmlspecialchars($job->industry_name ?? 'Not specified'); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="compensation-card">
                    <h6><i class="fa fa-money-bill-wave mr-2"></i>Compensation</h6>
                    <div class="compensation-details">
                        <?php if (!empty($job->salary_min) || !empty($job->salary_max)): ?>
                        <div class="salary-range">
                            <small>Salary Range</small>
                            <strong class="text-success">
                                <?php 
                                if ($job->salary_min && $job->salary_max) {
                                    echo htmlspecialchars($job->salary_min) . ' - ' . htmlspecialchars($job->salary_max);
                                } elseif ($job->salary_min) {
                                    echo 'From ' . htmlspecialchars($job->salary_min);
                                } elseif ($job->salary_max) {
                                    echo 'Up to ' . htmlspecialchars($job->salary_max);
                                }
                                ?>
                            </strong>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($job->pay_rate)): ?>
                        <div class="pay-rate">
                            <small>Pay Rate</small>
                            <strong><?php echo htmlspecialchars($job->pay_rate); ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($job->roster) || !empty($job->accommodation) || !empty($job->transport)): ?>
                <div class="additional-info-card">
                    <h6><i class="fa fa-info-circle mr-2"></i>Additional Info</h6>
                    <div class="additional-details">
                        <?php if (!empty($job->roster)): ?>
                        <div class="info-item">
                            <small>Roster</small>
                            <span><?php echo htmlspecialchars($job->roster); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($job->accommodation)): ?>
                        <div class="info-item">
                            <small>Accommodation</small>
                            <span><?php echo htmlspecialchars($job->accommodation); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($job->transport)): ?>
                        <div class="info-item">
                            <small>Transport</small>
                            <span><?php echo htmlspecialchars($job->transport); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<style>
/* Main Layout Structure */
#main-job-layout {
    min-height: 800px;
    margin: 0;
    padding: 20px;
    display: flex;
    flex-flow: row;
    gap: 20px;
}

/* Left Navigation Sidebar (20%) */
#main-job-layout>.job-sidebar-left {
    flex: 1 6 20%;
    order: 1;
}

/* Main Article Content (60%) */
#main-job-layout>.job-main-content {
    flex: 3 1 60%;
    order: 2;
    background: var(--card-color);
    border-radius: 8px;
    padding: 25px;
    border: 1px solid var(--border-color);
}

/* Right Aside Sidebar (20%) */
#main-job-layout>.job-sidebar-right {
    flex: 1 6 20%;
    order: 3;
}

/* Sidebar Cards */
.sidebar-content>div {
    background: var(--card-color);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.sidebar-content h6 {
    color: var(--primary-color);
    margin-bottom: 15px;
    font-weight: 600;
}

/* Header */
.page-header {

    padding: 20px;
    margin: -20px -20px 20px -20px;
}

/* Content Styling */
.content-header h1 {
    color: var(--font-color);
    margin-bottom: 5px;
    font-size: 1.8rem;
}

.job-tabs-navigation {
    margin: 25px 0;
}

.nav-tabs .nav-link {
    color: var(--font-color);
    border: none;
    padding: 12px 20px;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: var(--primary-color);
    border-bottom: 3px solid var(--primary-color);
    background: transparent;
}

.tab-section {
    margin-bottom: 30px;
}

.tab-section h5 {
    color: var(--font-color);
    margin-bottom: 15px;
    font-weight: 600;
}

/* Lists and Items */
.info-list .info-item,
.details-list .detail-item,
.additional-details .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--border-color);
}

.info-list .info-item:last-child,
.details-list .detail-item:last-child,
.additional-details .info-item:last-child {
    border-bottom: none;
}

/* Skills and Qualifications */
.requirements-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.skills-list,
.qualifications-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.skill-tag,
.qualification-tag {
    background: var(--primary-color);
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.qualification-tag {
    background: var(--success-color);
}

/* Application Methods */
.application-details {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.application-method,
.closing-date {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.application-method i,
.closing-date i {
    margin-top: 5px;
}

.method-details h6,
.date-details h6 {
    margin-bottom: 5px;
    color: var(--font-color);
}

.application-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.application-link:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media all and (max-width: 1024px) {
    #main-job-layout {
        flex-flow: column;
    }

    #main-job-layout>.job-sidebar-left,
    #main-job-layout>.job-main-content,
    #main-job-layout>.job-sidebar-right {
        order: 0;
        flex: none;
        width: 100%;
    }

    .requirements-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<!-- Your existing HTML structure remains the same, just adding the enhanced CSS -->

<style>
/* Color Hunt Palettes */
:root {
    /* Palette 1: Professional Blue (Header & Navigation) */
    --palette-blue-1: #2D46B9;
    --palette-blue-2: #1E3163;
    --palette-blue-3: #6A89CC;
    --palette-blue-4: #C4D7E0;

    /* Palette 2: Earth Tones (Main Content) */
    --palette-earth-1: #A27B5C;
    --palette-earth-2: #3F4E4F;
    --palette-earth-3: #DCD7C9;
    --palette-earth-4: #2C3639;

    /* Palette 3: Fresh Green (Requirements) */
    --palette-green-1: #379237;
    --palette-green-2: #54B435;
    --palette-green-3: #82CD47;
    --palette-green-4: #F0FF42;

    /* Palette 4: Warm Orange (Application) */
    --palette-orange-1: #FF6B6B;
    --palette-orange-2: #FFA500;
    --palette-orange-3: #FFD93D;
    --palette-orange-4: #FF9A3C;

    /* Palette 5: Elegant Purple (Sidebar) */
    --palette-purple-1: #6C4AB6;
    --palette-purple-2: #8D72E1;
    --palette-purple-3: #8D9EFF;
    --palette-purple-4: #B9E0FF;

    /* Palette 6: Modern Gradient (Backgrounds) */
    --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --gradient-4: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

/* Main Layout Structure with Beautiful Backgrounds */
#main-job-layout {
    min-height: 800px;
    margin: 0;
    padding: 20px;
    display: flex;
    flex-flow: row;
    gap: 20px;

}



.breadcrumb {
    background: transparent;
    margin-bottom: 0;
}

.breadcrumb-item a {
    color: rgba(255, 255, 255, 0.9) !important;
    text-decoration: none;
}

.breadcrumb-item.active {
    color: rgba(255, 255, 255, 0.7) !important;
}

.breadcrumb-item+.breadcrumb-item::before {
    color: rgba(255, 255, 255, 0.5);
}

/* Left Navigation Sidebar (20%) - Purple Theme */
#main-job-layout>.job-sidebar-left {
    flex: 1 6 20%;
    order: 1;
}

.job-sidebar-left .sidebar-content>div {
    background: linear-gradient(135deg, #28a745 0%, #0c0d19 100%);
    border: none;
    border-radius: 15px;
    padding: 25px 20px;
    margin-bottom: 25px;
    box-shadow: 0 8px 25px rgba(108, 74, 182, 0.3);
    color: white;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.job-sidebar-left .sidebar-content>div:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px #28a745;
}

.job-sidebar-left .sidebar-content>div::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 0%, rgba(255, 255, 255, 0.1) 100%);
}

.job-sidebar-left h6 {
    color: white !important;
    margin-bottom: 20px;
    font-weight: 600;
    font-size: 1.1rem;
    position: relative;
    z-index: 2;
}

.job-sidebar-left .btn {
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    font-weight: 500;
    border-radius: 10px;
    padding: 10px 15px;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.job-sidebar-left .btn:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
}

.job-sidebar-left .info-list .info-item {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding: 12px 0;
    position: relative;
    z-index: 2;
}

.job-sidebar-left .info-list .info-item:last-child {
    border-bottom: none;
}

.job-sidebar-left .info-list small {
    color: rgba(255, 255, 255, 0.8);
}

.job-sidebar-left .info-list strong {
    color: white;
}

/* Main Article Content (60%) - Earth Tone Theme */
#main-job-layout>.job-main-content {
    flex: 3 1 60%;
    order: 2;
    background: linear-gradient(135deg, #2F5249 0%, #2F5249 100%);
    border-radius: 20px;
    padding: 30px;
    border: none;
    box-shadow: 0 10px 30px rgba(63, 78, 79, 0.1);
    position: relative;
    overflow: hidden;
}

.job-main-content::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, var(--palette-earth-1) 0%, transparent 70%);
    opacity: 0.1;
    border-radius: 50%;
}

.content-header h1 {
    color: var(--palette-earth-4);
    margin-bottom: 8px;
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #00fd5a 0%, #ffffff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.content-header .text-muted {
    color: #fff !important;
    font-size: 1.1rem;
}

/* Enhanced Tab Navigation */
.job-tabs-navigation {
    margin: 30px 0;
    background: white;
    border-radius: 15px;
    padding: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.nav-tabs {
    border-bottom: none;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 5px;
}

.nav-tabs .nav-link {
    color: var(--palette-earth-2);
    border: none;
    padding: 15px 25px;
    font-weight: 600;
    border-radius: 10px;
    margin: 0 5px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.nav-tabs .nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.5s;
}

.nav-tabs .nav-link:hover::before {
    left: 100%;
}

.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #2F5249 0%, var(--palette-earth-2) 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(63, 78, 79, 0.3);
}

/* Tab Content Sections */
.tab-section {
    margin-bottom: 35px;
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border-left: 5px solid var(--palette-earth-1);
}

.tab-section h5 {
    color: var(--palette-earth-4);
    margin-bottom: 20px;
    font-weight: 700;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.description-content,
.project-content {
    color: var(--palette-earth-2);
    line-height: 1.8;
    font-size: 1.05rem;
}

/* Requirements Grid - Green Theme */
.requirements-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.requirement-column {
    background: linear-gradient(135deg, var(--palette-green-4) 0%, #FFFFFF 100%);
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(130, 205, 71, 0.2);
    border: 2px solid rgba(130, 205, 71, 0.1);
}

.requirement-column h6 {
    color: var(--palette-green-1);
    margin-bottom: 20px;
    font-weight: 700;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.skills-list,
.qualifications-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.skill-tag,
.qualification-tag {
    background: linear-gradient(135deg, var(--palette-green-2) 0%, var(--palette-green-3) 100%);
    color: white;
    padding: 12px 18px;
    border-radius: 25px;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(84, 180, 53, 0.3);
    transition: all 0.3s ease;
}

.skill-tag:hover,
.qualification-tag:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(84, 180, 53, 0.4);
}

.qualification-tag {
    background: linear-gradient(135deg, var(--palette-green-1) 0%, var(--palette-green-2) 100%);
}

/* Application Details - Orange Theme */
.application-details {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.application-method,
.closing-date {
    display: flex;
    align-items: center;
    gap: 20px;
    background: linear-gradient(135deg, #FFF9F9 0%, #FFFFFF 100%);
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(255, 107, 107, 0.1);
    border-left: 5px solid var(--palette-orange-1);
    transition: all 0.3s ease;
}

.application-method:hover,
.closing-date:hover {
    transform: translateX(10px);
    box-shadow: 0 8px 25px rgba(255, 107, 107, 0.2);
}

.application-method i,
.closing-date i {
    font-size: 2.5rem;
    background: linear-gradient(135deg, var(--palette-orange-1) 0%, var(--palette-orange-2) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.method-details h6,
.date-details h6 {
    margin-bottom: 8px;
    color: var(--palette-orange-1);
    font-weight: 700;
    font-size: 1.1rem;
}

.application-link {
    color: var(--palette-orange-1);
    text-decoration: none;
    font-weight: 600;
    font-size: 1.05rem;
    transition: all 0.3s ease;
}

.application-link:hover {
    color: var(--palette-orange-2);
    text-decoration: underline;
    transform: translateX(5px);
}

/* Right Aside Sidebar (20%) - Blue Gradient Theme */
#main-job-layout>.job-sidebar-right {
    flex: 1 6 20%;
    order: 3;
}

.job-sidebar-right .sidebar-content>div {
    background: linear-gradient(136deg, #437057 0%, #000000 100%);
    border: none;
    border-radius: 15px;
    padding: 25px 20px;
    margin-bottom: 25px;
    box-shadow: 0 8px 25px #28a7452e;
    color: white;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.job-sidebar-right .sidebar-content>div:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px #437057;
}

.job-sidebar-right .sidebar-content>div::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 0%, rgba(255, 255, 255, 0.1) 100%);
}

.job-sidebar-right h6 {
    color: white !important;
    margin-bottom: 20px;
    font-weight: 600;
    font-size: 1.1rem;
    position: relative;
    z-index: 2;
}

.job-sidebar-right .details-list .detail-item,
.job-sidebar-right .compensation-details>div,
.job-sidebar-right .additional-details .info-item {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding: 12px 0;
    position: relative;
    z-index: 2;
}

.job-sidebar-right .details-list .detail-item:last-child,
.job-sidebar-right .compensation-details>div:last-child,
.job-sidebar-right .additional-details .info-item:last-child {
    border-bottom: none;
}

.job-sidebar-right small {
    color: rgba(255, 255, 255, 0.8);
}

.job-sidebar-right strong,
.job-sidebar-right span {
    color: white;
}

.job-sidebar-right .text-success {
    color: #82CD47 !important;
}

/* Responsive Design */
@media all and (max-width: 1024px) {
    #main-job-layout {
        flex-flow: column;
        gap: 15px;
    }

    #main-job-layout>.job-sidebar-left,
    #main-job-layout>.job-main-content,
    #main-job-layout>.job-sidebar-right {
        order: 0;
        flex: none;
        width: 100%;
    }

    .requirements-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .page-header {
        margin: -20px -20px 20px -20px;
        padding: 20px;
    }
}

/* Animation for page load */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.job-sidebar-left .sidebar-content>div,
.job-main-content,
.job-sidebar-right .sidebar-content>div {
    animation: fadeInUp 0.6s ease-out;
}

.job-sidebar-left .sidebar-content>div:nth-child(2) {
    animation-delay: 0.1s;
}

.job-sidebar-right .sidebar-content>div:nth-child(2) {
    animation-delay: 0.2s;
}

.job-sidebar-right .sidebar-content>div:nth-child(3) {
    animation-delay: 0.3s;
}
</style>