<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<style>
/* Section Type Badges */
.section-type-badge {
    font-size: 0.75em;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 12px;
    display: inline-block;
}

.section-type-about {
    background: #007bff;
    color: white;
}

.section-type-skills {
    background: #17a2b8;
    color: white;
}

.section-type-experience {
    background: #28a745;
    color: white;
}

.section-type-education {
    background: #6f42c1;
    color: white;
}

.section-type-contact {
    background: #e83e8c;
    color: white;
}

.section-type-services {
    background: #20c997;
    color: white;
}

.section-type-portfolio {
    background: #fd7e14;
    color: white;
}

.section-type-testimonials {
    background: #ffc107;
    color: #212529;
}

.section-type-pricing {
    background: #dc3545;
    color: white;
}

.section-type-content {
    background: #6c757d;
    color: white;
}

.section-type-custom {
    background: #343a40;
    color: white;
}

/* Tab Styles */
.qm-tabs-header {
    display: flex;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 20px;
}

.qm-tabs-header li {
    padding: 10px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
}

.qm-tabs-header li.active {
    border-bottom-color: #007bff;
    color: #007bff;
    font-weight: 600;
}

.qm-tabs-tab {
    display: none;
}

.qm-tabs-tab[rel="1"] {
    display: block;
}

/* Section Type Badges for Listing */
.section-type-badge {
    font-size: 0.75em;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 12px;
    display: inline-block;
}



.btn-default {

    border-color: none !important;

}

.ecms-field .c_dropdown .btn-group>.btn {
    text-align: left;
    background-color: var(--body-color);
    border-color: none !important;
}

.section-type-about {
    background: #007bff;
    color: white;
}

.section-type-skills {
    background: #17a2b8;
    color: white;
}

.section-type-experience {
    background: #28a745;
    color: white;
}

.section-type-education {
    background: #6f42c1;
    color: white;
}

.section-type-contact {
    background: #e83e8c;
    color: white;
}

.section-type-services {
    background: #20c997;
    color: white;
}

.section-type-portfolio {
    background: #fd7e14;
    color: white;
}

.section-type-testimonials {
    background: #ffc107;
    color: #212529;
}

.section-type-pricing {
    background: #dc3545;
    color: white;
}

.section-type-content {
    background: #6c757d;
    color: white;
}

.section-type-custom {
    background: #343a40;
    color: white;
}
</style>

<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qmfc qm-tabs">

    <div class="quick-manage-heading">
        <?php if (!empty($row->id)): ?>
        <h2>Edit Template Section <span><?= htmlspecialchars($row->name ?? 'Unnamed Section') ?></span></h2>
        <?php else: ?>
        <h2>Add Template Section</h2>
        <?php endif; ?>
    </div>

    <ul class="qm-tabs-header">
        <li rel="1">Section Details</li>
        <li rel="2">Form Selection</li>
        <li rel="3">Preview</li>
    </ul>

    <div class="form-field-container">
        <?= form_open('', ['id' => 'main-form']); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>

        <!-- Tab 1: Section Details -->
        <div class="qm-tabs-tab" rel="1">
            <div class="alert alert-info mb-4">
                <h5><i class="fa fa-lightbulb-o"></i> Need a custom section?</h5>
                <p>Can't find the form you need? Create a custom form first, then come back to add it as a section.</p>
                <a href="<?= site_url('agency/template_sections/create_custom_section') ?>"
                    class="btn btn-warning btn-sm">
                    <i class="fa fa-plus"></i> Create Custom Form
                </a>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('name|label_name', !empty($row->name) ? $row->name : '', 'required'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('code|label_code', !empty($row->code) ? $row->code : '', 'required'); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?php
                    echo field_dropdown(
                        'section_type|label_section_type', 
                        $section_types, 
                        !empty($row->section_type) ? $row->section_type : 'content', 
                        'required'
                    );
                    ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input('category|label_category', !empty($row->category) ? $row->category : ''); ?>
                    <small class="form-text text-muted">e.g., "tech", "design", "marketing"</small>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('sort_order|label_sort_order', !empty($row->sort_order) ? $row->sort_order : 0, '', ['type' => 'number']); ?>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-check-label">Status</label>
                        <div class="form-check form-switch mt-2">
                            <?= field_checkbox('enabled|label_enabled', 1, !empty($row->enabled) ? $row->enabled : 1); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?= field_textarea('description|label_description', !empty($row->description) ? $row->description : '', '', ['rows' => 3]); ?>
                    <small class="form-text text-muted">Describe what this section will be used for</small>
                </div>
            </div>

            <div class="btn-container" style="clear: left;">
                <?= save_button('Save Section'); ?>
                <?= cancel_button('Close', 'left'); ?>
            </div>
        </div>

        <!-- Tab 2: Form Selection -->
        <div class="qm-tabs-tab" rel="2">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label">Select Form Schema</label>
                        <?php
                        echo field_dropdown(
                            'schema_id|label_form_schema', 
                            $form_schemas, 
                            !empty($row->schema_id) ? $row->schema_id : '', 
                            'required'
                        );
                        ?>
                        <small class="form-text text-muted">
                            Don't see the form you need?
                            <a href="<?= site_url('agency/template_sections/create_custom_section') ?>"
                                class="text-primary">
                                Create a custom form first
                            </a>
                        </small>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="alert alert-warning">
                        <i class="fa fa-info-circle"></i>
                        <strong>Note:</strong> The selected form will be rendered in templates using this section.
                    </div>
                </div>
            </div>

            <div class="btn-container" style="clear: left;">
                <?= save_button('Save Section'); ?>
                <?= cancel_button('Close', 'left'); ?>
            </div>
        </div>

        <?= form_close(); ?>

        <!-- Tab 3: Preview -->
        <div class="qm-tabs-tab" rel="3">
            <h4>Form Preview</h4>
            <div class="form-display">
                <?php if (!empty($preview_form)): ?>
                <?= $preview_form ?>
                <?php else: ?>
                <div class="alert alert-info">
                    <p>Select a form schema in the "Form Selection" tab to see a preview here.</p>
                    <p>The preview will show how the form will appear when rendered in templates.</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="btn-container" style="clear: left;">
                <?= save_button('Save Section'); ?>
                <?= cancel_button('Close', 'left'); ?>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-generate code from name
$('input[name="name"]').on('blur', function() {
    if ($('input[name="code"]').val() === '') {
        let code = $(this).val()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^-|-$/g, '');
        $('input[name="code"]').val(code);
    }
});

// Load form preview when schema is selected
$('select[name="schema_id"]').on('change', function() {
    const schemaId = $(this).val();
    if (schemaId) {
        loadFormPreview(schemaId);
    }
});

function loadFormPreview(schemaId) {
    $.ajax({
        url: '<?= site_url("agency/template_sections/get_form_preview") ?>',
        type: 'POST',
        data: {
            schema_id: schemaId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.form_view) {
                $('.qm-tabs-tab[rel="3"] .form-display').html(response.form_view);
            } else {
                $('.qm-tabs-tab[rel="3"] .form-display').html(
                    '<div class="alert alert-warning">Unable to load form preview</div>'
                );
            }
        },
        error: function() {
            $('.qm-tabs-tab[rel="3"] .form-display').html(
                '<div class="alert alert-danger">Error loading form preview</div>'
            );
        }
    });
}

// Refresh form schemas dropdown when returning from custom form creation
function refreshFormSchemas() {
    $.ajax({
        url: '<?= site_url("agency/template_sections/get_form_schemas") ?>',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const $dropdown = $('select[name="schema_id"]');
                $dropdown.empty();
                $dropdown.append('<option value="">Select Form Schema</option>');

                response.data.forEach(function(schema) {
                    $dropdown.append('<option value="' + schema.id + '">' + schema.name +
                        '</option>');
                });

                toastr.success('Form list updated!');
            }
        }
    });
}

// Check if we have a newly created form to auto-select
$(document).ready(function() {


    <?php if ($this->session->flashdata('form_created')): ?>
    const newFormId = <?= $this->session->flashdata('new_form_id') ?: 0 ?>;
    const newFormName = "<?= $this->session->flashdata('new_form_name') ?: '' ?>";

    if (newFormId > 0) {
        setTimeout(() => {
            const $dropdown = $('select[name="schema_id"]');
            if ($dropdown.find('option[value="' + newFormId + '"]').length > 0) {
                $dropdown.val(newFormId);
                toastr.success('Custom form "' + newFormName + '" created and selected!');
                // Load preview for the newly selected form
                loadFormPreview(newFormId);
            } else {
                refreshFormSchemas();
            }
        }, 500);
    }
    <?php endif; ?>

    // Initialize tab functionality
    $('.qm-tabs-header li').on('click', function() {
        const tabId = $(this).attr('rel');

        // Hide all tabs
        $('.qm-tabs-tab').hide();

        // Show selected tab
        $('.qm-tabs-tab[rel="' + tabId + '"]').show();

        // Update active tab
        $('.qm-tabs-header li').removeClass('active');
        $(this).addClass('active');
    });

    // Show first tab by default
    $('.qm-tabs-header li:first').trigger('click');

    // If we have a schema_id selected, load its preview
    <?php if (!empty($row->schema_id)): ?>
    setTimeout(() => {
        loadFormPreview(<?= $row->schema_id ?>);
    }, 1000);
    <?php endif; ?>
});

// Form submission handling
$('#main-form').on('submit', function(e) {
    e.preventDefault();

    const formData = $(this).serialize();

    $.ajax({
        url: '<?= site_url("agency/template_sections/save") ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success(response.message || 'Section saved successfully!');
                setTimeout(() => {
                    window.parent.location.reload();
                }, 1000);
            } else {
                toastr.error(response.message || 'Error saving section');
            }
        },
        error: function() {
            toastr.error('Network error occurred');
        }
    });
});
</script>