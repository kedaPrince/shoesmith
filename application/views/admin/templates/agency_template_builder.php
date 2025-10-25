<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<style>
.template-builder {
    display: flex;
    gap: 20px;
    min-height: 600px;
}

.available-sections {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 15px;
    background: #f9f9f9;
}

.template-preview {
    flex: 2;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 15px;
    background: white;
}

.section-item {
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 10px;
    margin-bottom: 10px;
    background: white;
    cursor: move;
}

.section-item:hover {
    border-color: #007bff;
}

.section-item h5 {
    margin: 0 0 5px 0;
    color: #333;
}

.section-type {
    font-size: 0.8em;
    color: #666;
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
}

.template-section {
    border: 2px dashed #007bff;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 15px;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.template-section.placeholder {
    border: 2px dashed #ccc;
    background: #f0f0f0;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
}

.section-controls {
    margin-top: 10px;
    text-align: right;
}

.filter-controls {
    margin-bottom: 15px;
}

#template-sections {
    min-height: 200px;
}

.section-title {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
    margin-bottom: 15px;
    color: #495057;
}

.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.drop-zone {
    border: 2px dashed #28a745 !important;
    background: #f0fff4 !important;
}

/* New styles for template management */
.template-management {
    background: #e9ecef;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 20px;
}

.template-status {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.status-badge {
    font-size: 0.9em;
    padding: 5px 10px;
    border-radius: 15px;
}

.btn-group-template {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Template Selector Styles */
.template-selector {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 20px;
}

.template-list {
    max-height: 200px;
    overflow-y: auto;
}

.template-item {
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    margin-bottom: 5px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.template-item:hover {
    background-color: #e9ecef;
}

.template-item.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.template-meta {
    font-size: 0.8em;
    color: #6c757d;
}

.template-item.active .template-meta {
    color: #e9ecef;
}
</style>

<div id="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h2>Template Builder</h2>

                <!-- Template Selector -->
                <?php if (!empty($all_templates) && !$is_new): ?>
                <div class="template-selector">
                    <h5>Select Template to Edit</h5>
                    <div class="template-list">
                        <?php foreach ($all_templates as $template): ?>
                        <div class="template-item <?= ($current_template && $current_template->id == $template->id) ? 'active' : '' ?>"
                            onclick="window.location='<?= site_url('admin/agency_templates/select_template/' . $agency_id . '/' . $template->id) ?>'">
                            <strong><?= htmlspecialchars($template->template_name) ?></strong>
                            <div class="template-meta">
                                Created: <?= date('M j, Y g:i A', strtotime($template->created_at)) ?>
                                <?= $template->id == ($current_template->id ?? '') ? '(Currently Editing)' : '' ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-2">
                        <a href="<?= site_url('admin/agency_templates/build/' . $agency_id . '?new=true') ?>"
                            class="btn btn-success btn-sm">
                            <i class="fa fa-plus"></i> Create New Template
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Template Management Header -->
                <div class="template-management">
                    <div class="template-status">
                        <div>
                            <strong>Agency ID:</strong> <?= $agency_id ?>
                            <?php if ($is_new): ?>
                            <span class="badge badge-primary status-badge">Creating New Template</span>
                            <?php elseif ($current_template): ?>
                            <span class="badge badge-success status-badge">
                                Editing: <?= htmlspecialchars($current_template->template_name) ?>
                            </span>
                            <?php else: ?>
                            <span class="badge badge-warning status-badge">No Template Selected</span>
                            <?php endif; ?>
                        </div>
                        <div class="btn-group-template">
                            <?php if (!$is_new && !empty($all_templates)): ?>
                            <a href="<?= site_url('admin/agency_templates/build/' . $agency_id . '?new=true') ?>"
                                class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Create New Template
                            </a>
                            <?php endif; ?>
                            <a href="<?= site_url('admin/templates') ?>" class="btn btn-info btn-sm">
                                <i class="fa fa-list"></i> View All Templates
                            </a>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <?php if ($is_new): ?>
                    You are creating a new template. Drag and drop sections to build your custom template.
                    <?php else: ?>
                    <?= $current_template ? 'Editing existing template.' : 'No template found. Create a new template by adding sections.' ?>
                    Drag and drop sections to build your custom template.
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Template Builder Form -->
        <?= form_open('admin/agency_templates/save_custom_template', ['id' => 'template-builder-form']); ?>
        <?= form_hidden('agency_id', $agency_id); ?>
        <?= form_hidden('template_id', $current_template->id ?? ''); ?>
        <?= form_hidden('sections', ''); ?>

        <div class="template-builder">
            <!-- Available Sections Panel -->
            <div class="available-sections">
                <h4>Available Sections</h4>
                <div class="filter-controls">
                    <select id="sectionTypeFilter" class="form-control">
                        <option value="">All Section Types</option>
                        <?php foreach ($section_types as $key => $label): ?>
                        <option value="<?= $key ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="availableSectionsList">
                    <?php foreach ($sections as $section): ?>
                    <div class="section-item" data-section-id="<?= $section->id ?>"
                        data-section-type="<?= $section->section_type ?>" draggable="true">
                        <h5><?= htmlspecialchars($section->name) ?></h5>
                        <div class="section-type">
                            <?= $section_types[$section->section_type] ?? $section->section_type ?>
                        </div>
                        <p class="small text-muted mb-2"><?= htmlspecialchars($section->description) ?></p>
                        <button type="button" class="btn btn-sm btn-primary add-section"
                            onclick="addSectionToTemplate(<?= $section->id ?>)">
                            <i class="fa fa-plus"></i> Add to Template
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Template Preview Panel -->
            <div class="template-preview">
                <h4>
                    <?php if ($is_new): ?>
                    Create New Template
                    <?php else: ?>
                    <?= $current_template ? 'Edit Template: ' . htmlspecialchars($current_template->template_name) : 'Create Template' ?>
                    <?php endif; ?>
                </h4>

                <div class="form-group">
                    <label>Template Name *</label>
                    <input type="text" name="template_name" class="form-control" required
                        value="<?= htmlspecialchars($current_template->template_name ?? 'My Custom Template') ?>"
                        placeholder="Enter template name">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2"
                        placeholder="Template description"><?= htmlspecialchars($current_template->description ?? '') ?></textarea>
                </div>

                <div id="template-sections" class="sortable-sections">
                    <?php if (!empty($current_sections) && !$is_new): ?>
                    <?php 
                        $current_section_data = [];
                        foreach ($current_sections as $section_id) {
                            foreach ($sections as $section) {
                                if ($section->id == $section_id) {
                                    $current_section_data[] = $section;
                                    break;
                                }
                            }
                        }
                        ?>
                    <?php foreach ($current_section_data as $section): ?>
                    <div class="template-section" data-section-id="<?= $section->id ?>" draggable="true">
                        <h5 class="section-title"><?= htmlspecialchars($section->name) ?></h5>
                        <div class="section-type small text-muted">
                            <?= $section_types[$section->section_type] ?? $section->section_type ?>
                        </div>
                        <div class="section-controls">
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeSection(this)">
                                <i class="fa fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="template-section placeholder" id="placeholder">
                        <div class="text-center">
                            <i class="fa fa-arrows-alt fa-2x mb-2"></i><br>
                            Drag sections here to build your template
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i>
                        <?= $current_template && !$is_new ? 'Update Template' : 'Save Template' ?>
                    </button>

                    <?php if ($current_template && !$is_new): ?>
                    <button type="button" id="previewTemplate" class="btn btn-info">
                        <i class="fa fa-eye"></i> Preview Template
                    </button>
                    <a href="<?= site_url('admin/agency_templates/manage_instance/' . $agency_id) ?>"
                        class="btn btn-primary">
                        <i class="fa fa-edit"></i> Fill Template Content
                    </a>
                    <?php endif; ?>

                    <button type="button" id="resetTemplate" class="btn btn-warning">
                        <i class="fa fa-refresh"></i> Reset Template
                    </button>

                    <?php if ($is_new): ?>
                    <a href="<?= site_url('admin/agency_templates/build/' . $agency_id) ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Cancel
                    </a>
                    <?php else: ?>
                    <a href="<?= site_url('admin/templates') ?>" class="btn btn-secondary">
                        <i class="fa fa-list"></i> View All Templates
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?= form_close(); ?>
    </div>
</div>

<script>
// Global variables
let currentAgencyId = <?= $agency_id ?>;
let selectedSections = <?= !empty($current_sections) && !$is_new ? json_encode($current_sections) : '[]' ?>.map(id =>
    parseInt(id));
let isCreatingNew = <?= $is_new ? 'true' : 'false' ?>;

// DOM elements
const templateSections = document.getElementById('template-sections');
const sectionTypeFilter = document.getElementById('sectionTypeFilter');
const templateForm = document.getElementById('template-builder-form');
const sectionsInput = document.querySelector('input[name="sections"]');

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Template builder initialized - Mode:', isCreatingNew ? 'Creating New' : 'Editing Existing');
    initializeDragAndDrop();
    initializeEventListeners();
    updateSectionsInput(); // Initialize the hidden input

    // If creating new, ensure we start with empty template
    if (isCreatingNew) {
        resetTemplate();
    }
});

function initializeEventListeners() {
    // Section type filter
    sectionTypeFilter.addEventListener('change', function() {
        const filter = this.value;
        const sectionItems = document.querySelectorAll('#availableSectionsList .section-item');
        sectionItems.forEach(item => {
            if (!filter || item.dataset.sectionType === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Form submission
    templateForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate form
        const templateName = document.querySelector('input[name="template_name"]').value;
        if (!templateName.trim()) {
            alert('Please enter a template name');
            return;
        }

        if (selectedSections.length === 0) {
            alert('Please add at least one section to your template');
            return;
        }

        // Update sections input before submission
        updateSectionsInput();

        console.log('Submitting form with sections:', selectedSections);

        // Show loading state
        const submitBtn = templateForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' +
            (isCreatingNew ? 'Creating...' : 'Updating...');

        // Submit the form
        this.submit();
    });

    // Preview button
    const previewBtn = document.getElementById('previewTemplate');
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            window.open('<?= site_url("admin/agency_templates/preview_agency_template/") ?>' + currentAgencyId,
                '_blank');
        });
    }

    // Reset button
    document.getElementById('resetTemplate').addEventListener('click', function() {
        if (confirm('Are you sure you want to reset the template? This will remove all sections.')) {
            resetTemplate();
        }
    });
}

function initializeDragAndDrop() {
    // Make sections draggable
    const draggableSections = document.querySelectorAll('.section-item, .template-section');

    draggableSections.forEach(section => {
        section.addEventListener('dragstart', handleDragStart);
        section.addEventListener('dragend', handleDragEnd);
    });

    // Make template area droppable
    templateSections.addEventListener('dragover', handleDragOver);
    templateSections.addEventListener('drop', handleDrop);
    templateSections.addEventListener('dragenter', handleDragEnter);
    templateSections.addEventListener('dragleave', handleDragLeave);
}

function handleDragStart(e) {
    this.classList.add('dragging');
    e.dataTransfer.setData('text/plain', this.dataset.sectionId);
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
    this.classList.remove('dragging');

    // Remove drop zone styling from all elements
    document.querySelectorAll('.drop-zone').forEach(el => {
        el.classList.remove('drop-zone');
    });
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDragEnter(e) {
    e.preventDefault();
    this.classList.add('drop-zone');
}

function handleDragLeave(e) {
    this.classList.remove('drop-zone');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();

    this.classList.remove('drop-zone');

    const draggedElement = document.querySelector('.dragging');
    if (draggedElement && draggedElement.classList.contains('section-item')) {
        // Adding new section from available sections
        const sectionId = parseInt(draggedElement.dataset.sectionId);
        if (!selectedSections.includes(sectionId)) {
            addSectionToTemplate(sectionId);
        }
    } else if (draggedElement && draggedElement.classList.contains('template-section')) {
        // Reordering existing sections
        const afterElement = getDragAfterElement(templateSections, e.clientY);
        const draggedSectionId = parseInt(draggedElement.dataset.sectionId);

        if (afterElement) {
            templateSections.insertBefore(draggedElement, afterElement);
        } else {
            templateSections.appendChild(draggedElement);
        }

        // Update order
        updateSectionsFromDOM();
    }

    return false;
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.template-section:not(.dragging)')];

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return {
                offset: offset,
                element: child
            };
        } else {
            return closest;
        }
    }, {
        offset: Number.NEGATIVE_INFINITY
    }).element;
}

function updateSectionsInput() {
    // Update the hidden input with current sections
    sectionsInput.value = JSON.stringify(selectedSections);
    console.log('Updated sections input:', sectionsInput.value);
}

function updateSectionsFromDOM() {
    // Update selectedSections array based on current DOM order
    const sectionElements = templateSections.querySelectorAll('.template-section[data-section-id]');
    selectedSections = Array.from(sectionElements).map(section => parseInt(section.dataset.sectionId));
    updateSectionsInput();
}

function addSectionToTemplate(sectionId) {
    sectionId = parseInt(sectionId);

    if (selectedSections.includes(sectionId)) {
        alert('This section is already in your template.');
        return;
    }

    const sectionItem = document.querySelector(`.section-item[data-section-id="${sectionId}"]`);
    if (!sectionItem) return;

    const sectionName = sectionItem.querySelector('h5').textContent;
    const sectionType = sectionItem.querySelector('.section-type').textContent;

    // Create new section in template
    const newSection = document.createElement('div');
    newSection.className = 'template-section';
    newSection.dataset.sectionId = sectionId;
    newSection.draggable = true;
    newSection.innerHTML = `
        <h5 class="section-title">${sectionName}</h5>
        <div class="section-type small text-muted">${sectionType}</div>
        <div class="section-controls">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeSection(this)">
                <i class="fa fa-times"></i> Remove
            </button>
        </div>
    `;

    // Remove placeholder if it exists
    const placeholder = document.getElementById('placeholder');
    if (placeholder) placeholder.remove();

    templateSections.appendChild(newSection);
    selectedSections.push(sectionId);

    // Update the form input
    updateSectionsInput();

    // Add drag events
    newSection.addEventListener('dragstart', handleDragStart);
    newSection.addEventListener('dragend', handleDragEnd);
}

function removeSection(button) {
    const sectionElement = button.closest('.template-section');
    const sectionId = parseInt(sectionElement.dataset.sectionId);

    sectionElement.remove();
    selectedSections = selectedSections.filter(id => id !== sectionId);

    // Update the form input
    updateSectionsInput();

    // Add placeholder if no sections left
    if (templateSections.querySelectorAll('.template-section').length === 0) {
        const placeholder = document.createElement('div');
        placeholder.className = 'template-section placeholder';
        placeholder.id = 'placeholder';
        placeholder.innerHTML = `
            <div class="text-center">
                <i class="fa fa-arrows-alt fa-2x mb-2"></i><br>
                Drag sections here to build your template
            </div>
        `;
        templateSections.appendChild(placeholder);
    }
}

function resetTemplate() {
    // Clear all sections
    selectedSections = [];
    templateSections.innerHTML = '';

    // Update the form input
    updateSectionsInput();

    // Reset template name and description if creating new
    if (isCreatingNew) {
        document.querySelector('input[name="template_name"]').value = 'My Custom Template';
        document.querySelector('textarea[name="description"]').value = '';
    }

    // Add placeholder
    const placeholder = document.createElement('div');
    placeholder.className = 'template-section placeholder';
    placeholder.id = 'placeholder';
    placeholder.innerHTML = `
        <div class="text-center">
            <i class="fa fa-arrows-alt fa-2x mb-2"></i><br>
            Drag sections here to build your template
        </div>
    `;
    templateSections.appendChild(placeholder);

    console.log('Template reset - ready for new template creation');
}
</script>