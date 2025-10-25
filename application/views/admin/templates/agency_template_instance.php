<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
.template-instance-form {
    max-width: 100%;
    margin: 0 auto;
}

.section-form {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 20px;
    margin-bottom: 20px;
    background: #f9f9f9;
}

.section-header {
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.form-preview {
    border: 1px dashed #ccc;
    padding: 15px;
    background: white;
    margin-top: 10px;
}

.loading-form {
    text-align: center;
    padding: 20px;
    color: #666;
}

/* Remove individual form buttons */
.form-preview .btn-container,
.form-preview .save-btn,
.form-preview button[type="submit"] {
    display: none !important;
}

/* Ensure forms are displayed as form elements, not actual forms */
.form-preview form {
    border: none !important;
    padding: 0 !important;
    background: transparent !important;
}

.form-preview .form-group {
    margin-bottom: 15px;
}
</style>

<div id="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h2>Fill Template Content - <?= htmlspecialchars($current_template->template_name) ?></h2>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    Fill in the content for each section of your template. All sections will be saved together when you
                    click "Save Template Instance".
                </div>
            </div>
        </div>

        <?= form_open('admin/agency_templates/save_template_instance', ['class' => 'template-instance-form', 'id' => 'templateInstanceForm']) ?>

        <?= form_hidden('agency_id', $agency_id) ?>
        <?= form_hidden('template_instance_id', $template_instance_id) ?>
        <?= form_hidden('template_id', $current_template->id) ?>

        <div class="form-group">
            <label>Instance Name</label>
            <?= form_input('instance_name', $template_instance_id ? 'Instance ' . date('Y-m-d H:i') : 'New Instance ' . date('Y-m-d H:i'), 'class="form-control" required') ?>
        </div>

        <?php if (!empty($template->sections)): ?>
        <?php foreach ($template->sections as $index => $section): ?>
        <div class="section-form" data-section-id="<?= $section->id ?>">
            <div class="section-header">
                <h4><?= htmlspecialchars($section->name) ?></h4>
                <small class="text-muted">Type: <?= $section->section_type ?></small>
            </div>

            <div class="form-preview" id="form-preview-<?= $section->id ?>">
                <div class="loading-form">
                    <i class="fa fa-spinner fa-spin"></i> Loading form for <?= htmlspecialchars($section->name) ?>...
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="alert alert-warning">
            No sections found in this template. Please add sections to your template first.
        </div>
        <?php endif; ?>

        <div class="form-group text-center">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fa fa-save"></i> Save Template Instance
            </button>
            <a href="<?= site_url('admin/agency_templates/build/' . $agency_id) ?>" class="btn btn-secondary btn-lg">
                <i class="fa fa-arrow-left"></i> Back to Template Builder
            </a>
        </div>

        <?= form_close() ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Template instance form loaded');
    console.log('Template sections:', <?= json_encode(array_map(function($s) { 
        return ['id' => $s->id, 'name' => $s->name, 'schema_id' => $s->schema_id]; 
    }, $template->sections ?? [])) ?>);

    // Load each form via AJAX
    <?php foreach ($template->sections as $section): ?>
    loadFormForSection(<?= $section->id ?>, <?= $section->schema_id ?>);
    <?php endforeach; ?>

    // Add form validation
    const form = document.getElementById('templateInstanceForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const instanceName = document.querySelector('input[name="instance_name"]').value;
            if (!instanceName.trim()) {
                e.preventDefault();
                alert('Please enter an instance name');
                return false;
            }

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
        });
    }
});

function loadFormForSection(sectionId, schemaId) {
    const container = document.getElementById('form-preview-' + sectionId);

    if (!container) {
        console.error('Container not found for section:', sectionId);
        return;
    }

    console.log('Loading form for section:', sectionId, 'schema:', schemaId);

    // Load form via AJAX
    fetch('<?= site_url("admin/test_form_builder/get_form_by_schema_id") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'schema_id=' + schemaId
        })
        .then(response => response.json())
        .then(data => {
            console.log('Form load response for section', sectionId, ':', data);

            if (data.success && data.form_view) {
                // Remove individual form buttons and styling
                let formHtml = data.form_view;

                // Remove form tags and buttons
                formHtml = formHtml.replace(/<form[^>]*>/gi, '<div class="form-content">');
                formHtml = formHtml.replace(/<\/form>/gi, '</div>');
                formHtml = formHtml.replace(/<div class="btn-container"[^>]*>.*?<\/div>/gis, '');
                formHtml = formHtml.replace(/<button[^>]*type="submit"[^>]*>.*?<\/button>/gis, '');
                formHtml = formHtml.replace(/<input[^>]*type="submit"[^>]*>/gis, '');

                container.innerHTML = formHtml;

                // Initialize any dynamic elements in the form
                initializeFormElements(container);
            } else {
                container.innerHTML = '<div class="alert alert-danger">Failed to load form: ' + (data.message ||
                    'Unknown error') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error loading form for section', sectionId, ':', error);
            container.innerHTML = '<div class="alert alert-danger">Error loading form: ' + error.message + '</div>';
        });
}

function initializeFormElements(container) {
    // Initialize any special form elements like datepickers, etc.
    const dateInputs = container.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // You can add datepicker initialization here if needed
    });

    // Initialize CKEditor if present
    if (typeof CKEDITOR !== 'undefined') {
        const textareas = container.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            if (textarea.classList.contains('ckeditor') || textarea.classList.contains('ckeditor_simple')) {
                // CKEditor should auto-initialize based on class
                console.log('CKEditor textarea found:', textarea.name);
            }
        });
    }

    // Log all form fields for debugging
    const formFields = container.querySelectorAll('input, select, textarea');
    console.log('Form fields loaded for section:', Array.from(formFields).map(field => ({
        name: field.name,
        type: field.type,
        value: field.value
    })));
}
</script>