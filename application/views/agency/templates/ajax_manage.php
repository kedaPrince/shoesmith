<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<!-- Add this to your quick manage view file -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qmfc">
    <div class="quick-manage-heading">
        <h2>
            Edit: <?= htmlspecialchars($identifier ?? ($row->name ?? 'Template')) ?>
        </h2>
        <?php if (!empty($current_schema_name)): ?>
        <p class="text-muted">Schema: <?= htmlspecialchars($current_schema_name) ?></p>
        <?php endif; ?>
    </div>

    <div class="form-field-container">
        <form id="main-form" method="post" accept-charset="utf-8" enctype="application/x-www-form-urlencoded"
            data-saved-data='<?= json_encode($debug_form_data ?? []) ?>'>
            <!-- ✅ ADD CSRF TOKEN HERE -->
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                value="<?= $this->security->get_csrf_hash() ?>" />

            <input type="hidden" name="id" value="<?= !empty($row->id) ? $row->id : '' ?>" />
            <input type="hidden" name="name" value="<?= !empty($row->name) ? htmlspecialchars($row->name) : '' ?>" />
            <input type="hidden" name="code" value="<?= !empty($row->code) ? htmlspecialchars($row->code) : '' ?>" />
            <input type="hidden" name="schema_id" value="<?= !empty($row->schema_id) ? $row->schema_id : '' ?>" />
            <input type="hidden" name="description"
                value="<?= !empty($row->description) ? htmlspecialchars($row->description) : '' ?>" />
            <input type="hidden" name="preview_image"
                value="<?= !empty($row->preview_image) ? htmlspecialchars($row->preview_image) : '' ?>" />
            <input type="hidden" name="enabled" value="<?= !empty($row->enabled) ? $row->enabled : 1 ?>" />
            <input type="hidden" name="is_preview" value="0" />
            <input type="hidden" name="template_cache_id"
                value="<?= !empty($row->id) ? $row->id . '_' . time() : '' ?>" />

            <div class="custom-form-container edit-mode">
                <?php if (!empty($current_form_preview)): ?>
                <div class="edit-info alert alert-warning">
                    <i class="fa fa-edit"></i> Fill in the form data below.
                </div>

                <div id="dynamic-form-content">
                    <?= $current_form_preview ?>
                </div>
                <?php else: ?>
                <div class="alert alert-warning text-center">
                    <i class="fa fa-exclamation-triangle fa-2x mb-2"></i><br>
                    <strong>No form available</strong>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($is_composite) && $is_composite): ?>
            <div class="section-manager mt-4 p-3 border rounded bg-light">
                <h5><i class="fa fa-cogs"></i> Manage Sections</h5>
                <button type="button" class="btn btn-primary mb-2" id="add-section-btn">
                    <i class="fa fa-plus"></i> Add Section
                </button>
                <p class="text-muted small mb-0">Add more sections to this composite template.</p>

                <!-- Add Section Modal -->
                <div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addSectionModalLabel">Add New Section</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i
                                        class="fa fa-times" aria-hidden="true"></i></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="section-select" class="form-label">Select a Section</label>
                                    <select class="form-select" id="section-select">
                                        <option value="">Loading available sections...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="confirm-add-section" disabled>Add
                                    Section</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group mt-4">
                <button type="button" class="btn btn-success" id="save-button">
                    <i class="fa fa-save"></i> Save Template Data
                </button>
                <button type="button" class="btn btn-secondary" id="close-button">
                    <i class="fa fa-times"></i> Close
                </button>
            </div>

        </form>
    </div>
</div>

<div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; border-radius: 5px;">
    <h5>Debug - Expected Form Data</h5>
    <div><strong>Template ID:</strong> <?= !empty($row->id) ? $row->id : 'N/A' ?></div>
    <div><strong>Fields that should be saved:</strong></div>
    <ul>
        <?php foreach($debug_form_data ?? [] as $key => $value): ?>
        <li><strong><?= htmlspecialchars($key) ?>:</strong>
            <?= htmlspecialchars(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '') ?></li>
        <?php endforeach; ?>
        <?php if(empty($debug_form_data)): ?>
        <li>No form data available</li>
        <?php endif; ?>
    </ul>
    <div><strong>Editor Status:</strong> <span id="editor-status">Initializing...</span></div>
</div>
<!-- Delete Section Modal -->
<div class="modal fade" id="deleteSectionModal" tabindex="-1" aria-labelledby="deleteSectionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSectionModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i
                        class="fa fa-times" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                <p id="delete-section-message">Are you sure you want to delete this section? This action cannot be
                    undone and will remove all associated form data for this section.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-section" disabled>Delete
                    Section</button>
            </div>
        </div>
    </div>
</div>
<style>
body .form-control {
    color: #000000 !important;
}

.ecms-field .ck .ck-content {
    color: #000000 !important;
}

.custom-form-container {
    padding: 20px;
}

.edit-info {
    margin-bottom: 20px;
}

.edit-mode input:not([type="hidden"]),
.edit-mode select,
.edit-mode textarea {
    pointer-events: auto;
    background-color: white;
    opacity: 1;
}

.form-field-container .btn-container {
    display: none !important;
}

#dynamic-form-content input,
#dynamic-form-content select,
#dynamic-form-content textarea {
    display: block;
    width: 100%;
}

.bg-light {
    background-color: #1c3945 !important;
}

.fallback-editor-toolbar {
    border: 1px solid #ddd;
    border-bottom: none;
    padding: 5px;
    background: #f8f9fa;
}

.fallback-editor-toolbar .btn {
    margin-right: 5px;
}

.toolbar-separator {
    margin: 0 10px;
    color: #6c757d;
}

.section-manager {
    background-color: #f8f9fa;
}

.composite-section {
    border: 1px solid #dee2e6;
    margin-bottom: 20px;
    border-radius: 5px;
}

.section-header {
    background-color: #e9ecef;
    padding: 10px;
    border-radius: 5px 5px 0 0;
}

#addSectionModal .modal-body {
    pointer-events: auto !important;
}

#addSectionModal {
    z-index: 1060 !important;
}

.modal-backdrop {
    z-index: 1055 !important;
}

.modal-backdrop.show {
    opacity: 0.5 !important;
}

.quick-manage-overlay {
    z-index: 1040 !important;
}

body.qm-full-page .quick-manage-overlay {
    pointer-events: none;
}

#dynamic-form-content {
    pointer-events: auto !important;
}

.delete-section {
    white-space: nowrap;
}

.composite-section:hover .delete-section {
    opacity: 1;
}

.delete-section:hover {
    background-color: #c82333 !important;
}

.composite-section .section-header {
    position: relative;
    z-index: 10;
}

.delete-section {
    pointer-events: auto !important;
    opacity: 1 !important;
    cursor: pointer !important;
    white-space: nowrap;
}

.delete-section:disabled {
    opacity: 0.65 !important;
}

.quick-manage-overlay {
    pointer-events: none !important;
}

#dynamic-form-content {
    pointer-events: auto !important;
}

.composite-section:hover .section-header .delete-section {
    opacity: 1 !important;
    transform: none;
}

#deleteSectionModal .modal-footer button {
    pointer-events: auto !important;
    z-index: 1070 !important;
}

/* CKEditor Styles */
.ck-editor {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.ck-editor__editable {
    min-height: 200px;
    border: 1px solid #ddd;
    padding: 10px;
}

/* Hide original textareas that have CKEditor */
.ck-editor+textarea,
.ck-editor~textarea {
    display: none !important;
}

/* Show fallback only if no CKEditor */
.fallback-editor-container {
    display: none;
}

.no-ckeditor .fallback-editor-container {
    display: block;
}

/* Loading indicator */
.editor-loading {
    min-height: 200px;
    border: 1px solid #ddd;
    padding: 10px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    font-style: italic;
}

/* FIX FOR MODAL BUTTON VISIBILITY */
#addSectionModal .modal-footer {
    display: flex !important;
    justify-content: flex-end !important;
    align-items: center !important;
    gap: 10px !important;
    padding: 1rem !important;
    border-top: 1px solid #dee2e6 !important;
    background-color: white !important;
    position: relative !important;
    z-index: 100 !important;
}

#addSectionModal .modal-footer .btn {
    display: inline-block !important;
    width: auto !important;
    min-width: 100px !important;
    padding: 0.375rem 0.75rem !important;
    font-size: 1rem !important;
    line-height: 1.5 !important;
    position: relative !important;
    z-index: 101 !important;
    opacity: 1 !important;
    visibility: visible !important;
}

/* Ensure modal content doesn't hide footer */
#addSectionModal .modal-content {
    display: flex !important;
    flex-direction: column !important;
    min-height: 200px !important;
    position: relative !important;
    z-index: 99 !important;
}

#addSectionModal .modal-body {
    flex: 1 !important;
    padding: 1rem !important;
    position: relative !important;
    z-index: 98 !important;
}

/* Override any hiding styles */
#confirm-add-section {
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Fix Bootstrap modal stacking context */
.modal.show {
    display: block !important;
    padding-right: 17px !important;
}

.modal-backdrop.show {
    opacity: 0.5 !important;
}
</style>

<script>
// Override the quick manage functionality with proper cache busting
$(document).ready(function() {
    console.log('Initializing quick manage with cache busting...');

    // Remove any existing click handlers for edit buttons
    $('body').off('click', '.edit-row');

    // Add new click handler with proper cache busting and enhanced ID extraction
    $('body').on('click', '.edit-row', function(e) {
        e.preventDefault();
        e.stopPropagation();

        console.log('Edit button clicked', this);

        // Force close existing overlay first
        if (typeof close_qm === 'function') close_qm();
        else $('.quick-manage-container, .quick-manage-overlay').remove();

        // Enhanced extraction with more fallbacks + logs
        let templateId = $(this).data('id') ||
            $(this).attr('data-id') ||
            $(this).closest('tr[data-id]').data('id') ||
            $(this).closest('.data-row').data('id') ||
            $(this).find('[data-id]').first().data('id');

        if (!templateId || isNaN(templateId)) {
            let href = $(this).attr('href') || '';
            console.log('Fallback to href:', href);
            const urlParams = new URLSearchParams(href.split('?')[1] || '');
            templateId = urlParams.get('template_id') ||
                href.split('/').filter(part => /^\d+$/.test(part)).pop();
        }

        if (!templateId || isNaN(templateId)) {
            let $row = $(this).closest('tr');
            templateId = $row.attr('data-id') || $row.data('template-id');
        }

        console.log('Extracted template ID steps:', {
            buttonData: $(this).data('id'),
            buttonAttr: $(this).attr('data-id'),
            rowData: $(this).closest('tr').data('id'),
            hrefParam: new URLSearchParams($(this).attr('href')?.split('?')[1] || '').get(
                'template_id'),
            final: templateId
        });

        console.log('Final template ID:', templateId);

        if (!templateId || isNaN(templateId)) {
            console.error('No valid template ID found');
            return;
        }

        console.log('Loading quick manage for template:', templateId);

        // Use aggressive cache buster
        var cacheBuster = 't=' + new Date().getTime();
        var url = 'agency/templates/ajax_quick_manage/' + templateId + '?' + cacheBuster;

        console.log('Fetching from URL:', url);

        // Show loading
        if (typeof show_loading === 'function') {
            show_loading();
        } else {
            $('body').append('<div class="loading-overlay">Loading...</div>');
        }

        // Use jQuery ajax for better compatibility
        $.ajax({
            url: url,
            type: 'GET',
            cache: false,
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            },
            success: function(html) {
                console.log('Quick manage content loaded successfully');
                if (typeof hide_loading === 'function') {
                    hide_loading();
                } else {
                    $('.loading-overlay').remove();
                }
                open_qm(html);
            },
            error: function(xhr, status, error) {
                console.error('Error loading template:', error);
                if (typeof hide_loading === 'function') {
                    hide_loading();
                } else {
                    $('.loading-overlay').remove();
                }
                alert('Error loading template data. Please try again.');
            }
        });
    });

    console.log('Quick manage cache busting enabled successfully');
});

// Quick Manage Form Class
window.QuickManageForm = class QuickManageForm {
    constructor() {
        this.editors = [];
        this.templateId = <?= !empty($row->id) ? $row->id : 'null' ?>;
        this.isInitialized = false;
        this.ckeditorReady = false;
        this.fallbackEditors = new Map();
        this.currentTemplateData = <?= json_encode($debug_form_data ?? []) ?>;
        this.isComposite = <?= json_encode($is_composite ?? false) ?>;
        this.sectionManagerInitialized = false;
        this.init();
    }

    init() {
        console.log('=== QUICK MANAGE FORM LOADED FOR TEMPLATE:', this.templateId, '===');
        console.log('Template data count:', Object.keys(this.currentTemplateData).length);
        console.log('Is Composite:', this.isComposite);

        window.quickManageInitialized = true;

        if (window.quickManageApp) {
            console.log('Clearing previous QuickManageForm instance');
            window.quickManageApp.destroy();
        }
        window.quickManageApp = this;

        this.initializeForm();
    }

    async initializeForm() {
        try {
            // SYNC CSRF TOKEN FIRST - THIS IS CRITICAL!
            await this.syncCSRFToken();

            this.cleanupForm();
            this.styleSectionHeaders();
            this.setupEventHandlers();

            if (this.isComposite) {
                this.setupSectionManager();
            }

            // Set form values
            this.setFormValues();

            // Initialize editors
            await this.initializeEditors();

            $('#editor-status').text('Ready');
            this.isInitialized = true;

        } catch (error) {
            console.error('Form initialization error:', error);
        }
    }

    async initializeEditors() {
        console.log('Initializing editors...');

        // Try to load CKEditor if not available
        if (typeof ClassicEditor === 'undefined') {
            console.log('CKEditor not available, trying to load...');
            await this.loadCKEditor();
        }

        // If CKEditor is available, use it
        if (typeof ClassicEditor !== 'undefined' && typeof ClassicEditor.create === 'function') {
            await this.initializeCKEditors();
        } else {
            console.log('CKEditor not available, using textareas as-is');
            // Just show the textareas
            $('textarea[id^="ckeditor_"]').show();
        }
    }

    loadCKEditor() {
        return new Promise((resolve, reject) => {
            console.log('Loading CKEditor v5...');

            if (document.querySelector('script[src*="ckeditor5"]')) {
                console.log('CKEditor script already in DOM');
                this.waitForCKEditor(resolve, reject);
                return;
            }

            const ckeditorPaths = [
                '<?= base_url() ?>assets/ckeditor5/build/ckeditor.js',
                '<?= base_url() ?>assets/js/ckeditor5/ckeditor.js',
                'https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js'
            ];

            let attempts = 0;
            const tryLoadCKEditor = (index) => {
                if (index >= ckeditorPaths.length) {
                    console.log('All CKEditor paths failed, continuing without CKEditor');
                    resolve(); // Resolve anyway, we'll use textareas
                    return;
                }

                const scriptUrl = ckeditorPaths[index];
                console.log('Trying CKEditor path:', scriptUrl);

                const script = document.createElement('script');
                script.src = scriptUrl;
                script.onload = () => {
                    console.log('CKEditor script loaded from:', scriptUrl);
                    this.waitForCKEditor(resolve, reject);
                };
                script.onerror = () => {
                    console.log('Failed to load CKEditor from:', scriptUrl);
                    tryLoadCKEditor(index + 1);
                };

                document.head.appendChild(script);
            };

            tryLoadCKEditor(0);
        });
    }

    waitForCKEditor(resolve, reject) {
        let attempts = 0;
        const maxAttempts = 50;
        const interval = 100;

        const checkCKEditor = () => {
            attempts++;
            if (typeof ClassicEditor !== 'undefined' && typeof ClassicEditor.create === 'function') {
                console.log('CKEditor v5 fully loaded after', attempts * interval, 'ms');
                resolve();
            } else if (attempts >= maxAttempts) {
                console.log('CKEditor v5 timeout - continuing without it');
                resolve(); // Resolve anyway
            } else {
                setTimeout(checkCKEditor, interval);
            }
        };
        checkCKEditor();
    }

    async initializeCKEditors() {
        const textareas = $('textarea[id^="ckeditor_"]');
        console.log('Found CKEditor textareas:', textareas.length);

        if (textareas.length === 0) {
            return;
        }

        for (const textarea of textareas) {
            const $textarea = $(textarea);
            const textareaId = $textarea.attr('id');

            try {
                const editor = await ClassicEditor.create(textarea, {
                    toolbar: {
                        items: [
                            'bold', 'italic', 'underline',
                            '|',
                            'bulletedList', 'numberedList',
                            '|',
                            'link',
                            '|',
                            'removeFormat'
                        ]
                    },
                    height: 200
                });

                this.editors.push({
                    id: textareaId,
                    editor: editor
                });

                console.log('CKEditor created for:', textareaId);

                // Sync changes back to textarea
                editor.model.document.on('change:data', () => {
                    const data = editor.getData();
                    $textarea.val(data);
                });

            } catch (error) {
                console.error('Failed to create CKEditor for', textareaId, error);
                // Show the textarea
                $textarea.show();
            }
        }

        this.ckeditorReady = true;
        console.log('CKEditor initialization complete');
    }

    setupSectionManager() {
        $('#deleteSectionModal').modal('dispose').removeData('bs.modal');
        $('#confirm-delete-section').off('click.deletemgr').removeData('section-id');
        if (this.sectionManagerInitialized) {
            console.log('Section manager already initialized, skipping...');
            return;
        }
        this.cleanupDuplicateModals();
        if (this.sectionManagerInitialized) {
            console.log('Section manager already initialized, skipping...');
            return;
        }

        console.log('Setting up section manager for composite template:', this.templateId);

        // Clear existing handlers to prevent duplicates
        $('#add-section-btn').off('click.sectionmgr');
        $('#confirm-add-section').off('click.sectionmgr');
        $('#section-select').off('change.sectionmgr');
        $(document).off('click', '.delete-section');
        $(document).off('click.deletemgr', '#confirm-delete-section');

        // Handle Add Section button
        // Handle Add Section button
        $('#add-section-btn').on('click.sectionmgr', async () => {
            console.log('Add section button clicked');

            // Show loading state in dropdown
            const select = $('#section-select');
            select.empty().html('<option value="">Loading sections...</option>');
            $('#confirm-add-section').prop('disabled', true);

            // Load sections FIRST
            try {
                await this.loadAvailableSections();

                // Only show modal AFTER sections are loaded
                $('#addSectionModal').appendTo('body').css('z-index', '1060').modal('show');

                // Small delay to ensure modal is fully shown
                setTimeout(() => {
                    if (select.find('option').length > 1) { // Has sections besides default
                        $('#confirm-add-section').prop('disabled', false);
                    }
                }, 100);

            } catch (error) {
                console.error('Failed to load sections:', error);
                select.empty().html('<option value="">Error loading sections</option>');
                $('#addSectionModal').appendTo('body').css('z-index', '1060').modal('show');
            }
        });

        // Handle confirm add
        $('#confirm-add-section').on('click.sectionmgr', () => {
            const sectionId = $('#section-select').val();
            if (!sectionId) {
                this.showMessage('Please select a section', 'error');
                return;
            }
            this.addSection(sectionId);
        });

        $('#addSectionModal').off('hidden.bs.modal').on('hidden.bs.modal', () => {
            $('#section-select').val('').empty().html('<option value="">Select a section...</option>');

            // === FIX: Reset button text AND state ===
            $('#confirm-add-section').prop('disabled', true).html('Add Section');

            $('#addSectionModal').appendTo('.section-manager');
        });

        // Fix Cancel and X buttons for Add Section modal
        const addModal = $('#addSectionModal');
        if (addModal.length) {
            // Fix X button (btn-close)
            const addCloseBtn = addModal.find('.btn-close');
            addCloseBtn.off('click.modal-close').on('click.modal-close', function(e) {
                e.preventDefault();
                e.stopPropagation();
                addModal.modal('hide');
            });

            // Fix Cancel button (text says "Cancel")  
            const addCancelBtn = addModal.find('button').filter(function() {
                const text = $(this).text().trim().toLowerCase();
                return text === 'cancel';
            });

            addCancelBtn.off('click.modal-close').on('click.modal-close', function(e) {
                e.preventDefault();
                e.stopPropagation();
                addModal.modal('hide');
            });
        }


        // Delete section handler - using event delegation for dynamically added buttons
        $(document).on('click', '.delete-section', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const $btn = $(e.currentTarget);
            if ($btn.prop('disabled')) {
                console.log('Delete button disabled - skipping');
                return;
            }

            const sectionId = $btn.data('section-id');
            const sectionName = $btn.closest('.composite-section').find('h5').text().trim();

            $('#delete-section-message').html(
                `Are you sure you want to delete the section "<strong>${sectionName}</strong>"? This action cannot be undone and will remove all associated form data for this section.`
            );
            $('#confirm-delete-section').data('section-id', sectionId).prop('disabled', false);

            $('#deleteSectionModal').appendTo('body').css({
                'z-index': '1060',
                'pointer-events': 'auto'
            }).modal('show');
        });

        // Handle confirm delete
        $(document).on('click.deletemgr', '#confirm-delete-section', (e) => {
            const sectionId = $(e.currentTarget).data('section-id');
            if (!sectionId) {
                this.showMessage('No section selected for deletion', 'error');
                return;
            }
            $('#deleteSectionModal').modal('hide');
            this.removeSection(sectionId);
        });

        $('#addSectionModal').off('hidden.bs.modal').on('hidden.bs.modal', () => {
            $('#section-select').val('').empty().html('<option value="">Select a section...</option>');

            // === FIX: Reset button text AND state ===
            $('#confirm-add-section').prop('disabled', true).html('Add Section');

            $('#addSectionModal').appendTo('.section-manager');
        });

        $('#deleteSectionModal').off('hidden.bs.modal').on('hidden.bs.modal', () => {
            $('#confirm-delete-section').removeData('section-id').prop('disabled', true);
            $('#deleteSectionModal').appendTo('.section-manager');
        });

        // Fix Cancel and X buttons with direct click handlers
        const deleteModal = $('#deleteSectionModal');
        if (deleteModal.length) {
            // Fix X button (btn-close)
            const closeBtn = deleteModal.find('.btn-close');
            closeBtn.off('click.modal-close').on('click.modal-close', function(e) {
                e.preventDefault();
                e.stopPropagation();
                deleteModal.modal('hide');
            });

            // Fix Cancel button (text says "Cancel")  
            const cancelBtn = deleteModal.find('button').filter(function() {
                const text = $(this).text().trim().toLowerCase();
                return text === 'cancel';
            });

            cancelBtn.off('click.modal-close').on('click.modal-close', function(e) {
                e.preventDefault();
                e.stopPropagation();
                deleteModal.modal('hide');
            });
        }

        // Enable delete buttons
        this.enableDeleteButtons();

        // Enable delete buttons
        this.enableDeleteButtons();

        this.sectionManagerInitialized = true;


        // Ensure proper z-index

        $('#deleteSectionModal').css('z-index', '1060');
        $('.modal-backdrop').css('z-index', '1055');
        console.log('Section manager setup complete');
    }
    cleanupDuplicateModals() {
        // Remove any duplicate modals
        const modals = $('[id="addSectionModal"]');
        if (modals.length > 1) {
            console.log(`Cleaning up ${modals.length - 1} duplicate modals`);
            // Keep the first one, remove the rest
            modals.slice(1).remove();
        }

        // Also clean up duplicate backdrops
        const backdrops = $('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops.slice(1).remove();
        }
    }
    async loadAvailableSections() {
        console.log('Loading available sections for template:', this.templateId);
        try {
            // Clear existing options first
            const select = $('#section-select');
            select.empty().html('<option value="">Select a section...</option>');
            $('#confirm-add-section').prop('disabled', true);

            const response = await fetch(
                `<?= site_url('agency/templates/get_available_sections_for_template/') ?>${this.templateId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server response not OK:', response.status, errorText);
                throw new Error(`Server returned ${response.status}: ${errorText.substring(0, 100)}`);
            }

            const data = await response.json();
            console.log('Sections data received:', data);

            if (!data.success) {
                throw new Error(data.error || 'Server returned unsuccessful response');
            }

            if (!data.sections || !Array.isArray(data.sections)) {
                console.error('Invalid sections data:', data);
                throw new Error('Invalid sections data format');
            }

            // Add sections to dropdown
            data.sections.forEach(section => {
                if (section && section.id && section.name) {
                    const option = $('<option>', {
                        value: section.id,
                        text: `${section.name} (${section.section_type || 'General'})`
                    });
                    select.append(option);
                } else {
                    console.warn('Invalid section object:', section);
                }
            });

            if (data.sections.length > 0) {
                // === CRITICAL FIX: Wrap everything in setTimeout ===
                setTimeout(() => {
                    // === FIX 1: AUTO-SELECT FIRST OPTION ===
                    const firstSectionId = data.sections[0].id;
                    select.val(firstSectionId);

                    // === FIX 2: RE-ATTACH CHANGE HANDLER ===
                    select.off('change.sectionmgr').on('change.sectionmgr', (e) => {
                        const val = $(e.target).val();
                        console.log('Dropdown changed to:', val);
                        $('#confirm-add-section').prop('disabled', !val);
                    });

                    // === FIX 3: ENABLE BUTTON (since we have a selection) ===
                    $('#confirm-add-section').prop('disabled', false);

                    console.log(`✓ Loaded ${data.sections.length} available sections into dropdown`);
                    console.log(`✓ Auto-selected first section: ${firstSectionId}`);
                    console.log(`✓ Button should be enabled`);

                    // Force the dropdown to update visually
                    select.trigger('change');

                    // Debug: Check what's actually in the dropdown
                    console.log('Dropdown options count:', select.find('option').length);
                    console.log('Dropdown value after auto-select:', select.val());
                    console.log('Confirm button disabled:', $('#confirm-add-section').prop('disabled'));
                }, 100); // ← 100ms delay for DOM to update

                return true;
            } else {
                console.log('No sections available to add');
                select.append('<option value="">No sections available</option>');
                return false;
            }

        } catch (error) {
            console.error('Failed to load available sections:', error);
            this.showMessage('Failed to load sections: ' + error.message, 'error');

            const select = $('#section-select');
            select.empty().html('<option value="">Error loading sections</option>');
            $('#confirm-add-section').prop('disabled', true);
            throw error;
        }
    }

    async syncCSRFToken() {
        try {
            console.log('Syncing CSRF token with server...');

            // Get current token from server
            const response = await fetch('<?= site_url("agency/templates/test_csrf_debug") ?>');
            const data = await response.json();

            // Update ALL CSRF inputs with the server's token
            $(`input[name="${data.csrf_name}"]`).val(data.csrf_hash);

            // Also update meta tags if they exist
            document.querySelectorAll('meta[name="csrf-token"]').forEach(meta => {
                meta.setAttribute('content', data.csrf_hash);
            });

            console.log('CSRF token synced:', data.csrf_hash.substring(0, 20) + '...');
            return data.csrf_hash;

        } catch (error) {
            console.error('CSRF sync failed:', error);
            return null;
        }
    }
    cleanupModalState() {
        console.log('Cleaning up modal state...');
        // Remove ALL extra backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 1) {
            for (let i = 1; i < backdrops.length; i++) {
                backdrops[i].remove();
            }
        }

        // Force remove Bootstrap modal backdrop
        $('.modal-backdrop').remove();

        // Remove modal-open class from body
        $('body').removeClass('modal-open');

        // Reset body overflow and padding
        $('body').css({
            'overflow': '',
            'padding-right': ''
        });

        // Force hide all modals
        $('.modal').each(function() {
            $(this).removeClass('show');
            $(this).css('display', 'none');
            $(this).attr('aria-hidden', 'true');
        });

        // If Bootstrap modal instances exist, hide them properly
        $('.modal').modal('hide');

        console.log('Modal state cleaned up');
    }
    async addSection(sectionId) {
        console.log('Adding section ID', sectionId, 'to template:', this.templateId);
        try {
            // ENSURE CSRF IS SYNCED BEFORE MAKING REQUEST
            const csrfToken = await this.syncCSRFToken();
            if (!csrfToken) {
                throw new Error('Failed to sync CSRF token');
            }

            const csrfName = 'csrf_rfid_token';

            // Disable button to prevent duplicate clicks
            $('#confirm-add-section').prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> Adding...');

            const formData = new FormData();
            formData.append('section_id', sectionId);
            formData.append(csrfName, csrfToken);

            const response = await fetch(
                `<?= site_url('agency/templates/add_section_to_template/') ?>${this.templateId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

            console.log('Response status:', response.status);

            // Check for 403/CSRF errors first
            if (response.status === 403) {
                throw new Error('CSRF token expired. Please refresh the page.');
            }

            const responseText = await response.text();
            let responseData;

            try {
                responseData = JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                console.error('Raw response:', responseText.substring(0, 200));
                throw new Error('Invalid server response');
            }

            // Update CSRF token if returned (the server might send a new one)
            if (responseData.csrf) {
                this.updateCSRFToken(responseData.csrf);
            }

            if (responseData.success) {
                console.log('Section added successfully');

                // CRITICAL FIX: Clean up modal state BEFORE anything else
                this.cleanupModalState();

                // Close modal
                $('#addSectionModal').modal('hide');

                // Reset modal state
                setTimeout(() => {
                    $('#section-select').val('').trigger('change');
                    $('#confirm-add-section').prop('disabled', true).html('Add Section');
                }, 300);

                this.showMessage(responseData.message || 'Section added successfully', 'success');

                // Refresh the form to show new section
                await this.refreshQuickManage();

            } else {
                // Reset button on server error
                $('#confirm-add-section').prop('disabled', false).html('Add Section');
                throw new Error(responseData.error || 'Unknown error');
            }
        } catch (error) {
            console.error('Failed to add section:', error);
            this.showMessage('Failed to add section: ' + error.message, 'error');

            // ALWAYS reset button on any error
            $('#confirm-add-section').prop('disabled', false).html('Add Section');
        }
    }

    async removeSection(sectionId) {
        console.log('Removing section ID', sectionId, 'from template:', this.templateId);
        try {
            // ENSURE CSRF IS SYNCED
            const csrfToken = await this.syncCSRFToken();
            if (!csrfToken) {
                throw new Error('Failed to sync CSRF token');
            }

            const csrfName = 'csrf_rfid_token';

            // Disable delete button to prevent multiple clicks
            $('#confirm-delete-section').prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> Deleting...');

            const formData = new FormData();
            formData.append('section_id', sectionId);
            formData.append(csrfName, csrfToken);

            const response = await fetch(
                `<?= site_url('agency/templates/remove_section_from_template/') ?>${this.templateId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

            console.log('Response status:', response.status);

            const responseText = await response.text();
            let responseData;

            try {
                responseData = JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                console.error('Raw response:', responseText.substring(0, 200));
                throw new Error('Invalid server response');
            }

            // Update CSRF token if returned
            if (responseData.csrf) {
                this.updateCSRFToken(responseData.csrf);
            }

            if (responseData.success) {
                console.log('Section removed successfully');
                this.showMessage(responseData.message || 'Section removed successfully', 'success');

                // Reset button state
                $('#confirm-delete-section').prop('disabled', false).html('Delete Section');

                // Refresh the form to show updated sections
                await this.refreshQuickManage();
            } else {
                throw new Error(responseData.error || 'Unknown error');
            }
        } catch (error) {
            console.error('Failed to remove section:', error);
            this.showMessage('Failed to remove section: ' + error.message, 'error');
            $('#confirm-delete-section').prop('disabled', false).html('Delete Section');
        }
    }

    updateCSRFToken(newToken) {
        if (!newToken) return false;

        console.log('Updating CSRF token to:', newToken.substring(0, 20) + '...');

        // 1. Update meta tag
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            metaToken.setAttribute('content', newToken);
            console.log('Updated meta tag');
        }

        // 2. Update form input (find by name from meta tag)
        const tokenNameMeta = document.querySelector('meta[name="csrf-token-name"]');
        if (tokenNameMeta) {
            const csrfName = tokenNameMeta.getAttribute('content');

            // Update ALL inputs with this name
            $(`input[name="${csrfName}"]`).each(function() {
                $(this).val(newToken);
            });

            console.log(`Updated form inputs with name="${csrfName}"`);
        }

        // 3. Also update any hidden CSRF inputs that might have different names
        $('input[type="hidden"]').each(function() {
            const name = $(this).attr('name');
            if (name && (name.includes('csrf') || name.includes('token'))) {
                $(this).val(newToken);
                console.log(`Also updated input with name="${name}"`);
            }
        });

        return true;
    }

    async refreshQuickManage() {
        console.log('Refreshing quick manage for template:', this.templateId);
        try {
            // CRITICAL FIX: Clean up modal state before refreshing
            this.cleanupModalState();

            const cacheBuster = 't=' + new Date().getTime();
            const url =
                `<?= site_url('agency/templates/ajax_quick_manage/') ?>${this.templateId}?${cacheBuster}`;
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('Failed to fetch updated content');
            }

            const html = await response.text();

            // Before replacing content, check if there's a new CSRF token in the response
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;

            const newMetaToken = tempDiv.querySelector('meta[name="csrf-token"]');
            if (newMetaToken && newMetaToken.getAttribute('content')) {
                const newToken = newMetaToken.getAttribute('content');
                this.updateCSRFToken(newToken);
                console.log('Updated CSRF token from refreshed content');
            }

            const $newDoc = $('<div>').html(html);
            const $newFormContainer = $newDoc.find('.quick-manage-form-container');

            // === CRITICAL FIX: COMPLETELY REPLACE the form container ===
            $('.quick-manage-form-container').replaceWith($newFormContainer);

            // === FIX: Store templateId BEFORE destroying instance ===
            const templateId = this.templateId;
            const isComposite = this.isComposite;

            // === CRITICAL FIX: Destroy OLD instance ===
            if (window.quickManageApp) {
                window.quickManageApp.destroy();
            }

            // Wait for DOM to be ready
            setTimeout(() => {
                // === FIX: Create NEW instance instead of using destroyed 'this' ===
                if (typeof window.QuickManageForm !== 'undefined') {
                    try {
                        window.quickManageApp = new window.QuickManageForm();

                        // === FIX: Manually set properties since new instance might not have them ===
                        window.quickManageApp.templateId = templateId;
                        window.quickManageApp.isComposite = isComposite;

                        // Force initialize section manager if composite
                        if (isComposite && window.quickManageApp.setupSectionManager) {
                            window.quickManageApp.sectionManagerInitialized = false;
                            window.quickManageApp.setupSectionManager();
                            window.quickManageApp.enableDeleteButtons();
                        }

                        console.log('✅ Quick manage COMPLETELY refreshed with NEW instance');
                    } catch (error) {
                        console.error('Failed to create new QuickManageForm:', error);
                    }
                }
            }, 100);

        } catch (error) {
            console.error('Failed to refresh quick manage:', error);
            this.showMessage('Failed to refresh form. Please reload.', 'error');
        }
    }

    enableDeleteButtons() {
        $('.delete-section').each((i, btn) => {
            const $btn = $(btn);
            $btn.prop('disabled', false)
                .css({
                    'pointer-events': 'auto',
                    'opacity': '1',
                    'cursor': 'pointer'
                });
        });
    }

    cleanupForm() {
        console.log('Cleaning up form for Template:', this.templateId);

        $('#dynamic-form-content form').each(function() {
            const $nestedForm = $(this);
            const formContent = $nestedForm.html();
            $nestedForm.replaceWith('<div class="dynamic-form-fields">' + formContent + '</div>');
        });

        $('.dynamic-form-fields .btn-container').remove();
        $('.dynamic-form-fields .btn-primary').remove();
    }

    styleSectionHeaders() {
        $('h1:not(.quick-manage-heading h1)').addClass('section-header');
        $('h2:not(.quick-manage-heading h2)').each(function(index) {
            if (index % 3 === 0) {
                $(this).addClass('section-header');
            } else if (index % 3 === 1) {
                $(this).addClass('section-header-secondary');
            } else {
                $(this).addClass('section-header-tertiary');
            }
        });

        $('h3').addClass('form-row-header');
        $('h4').addClass('field-group-header');
    }

    setupEventHandlers() {
        // Clear existing handlers first
        $('#save-button').off('click.quickmanage');
        $('#close-button').off('click.quickmanage');
        $('.close-quick-manage').off('click.quickmanage');
        $(document).off('keyup.quickmanage');
        $('#main-form').off('keypress.quickmanage');

        $('#save-button').on('click.quickmanage', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.saveForm();
        });

        $('#close-button').on('click.quickmanage', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.closeForm();
        });

        $('.close-quick-manage').on('click.quickmanage', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.closeForm();
        });

        $(document).on('keyup.quickmanage', (e) => {
            if (e.keyCode === 27) {
                e.preventDefault();
                e.stopPropagation();
                this.closeForm();
            }
        });

        $('#main-form').on('keypress.quickmanage', (e) => {
            const $target = $(e.target);
            const isInEditor = $target.is('textarea') ||
                $target.closest('.ck-editor__editable').length > 0 ||
                ($target.is('[contenteditable="true"]') || $target.closest('[contenteditable="true"]')
                    .length > 0);

            if (!isInEditor && e.keyCode === 13) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }

    setFormValues() {
        console.log('=== SETTING FORM VALUES ===');

        // Wait a bit for DOM to be ready
        setTimeout(() => {
            Object.keys(this.currentTemplateData).forEach(fieldName => {
                const fieldValue = this.currentTemplateData[fieldName];
                this.setFieldValue(fieldName, fieldValue);
            });

            console.log('Form values set');
        }, 100);
    }

    convert_to_form_field_name(stored_name) {
        if (stored_name === 'template_cache_id') {
            return 'template_cache_id';
        }

        if (stored_name.startsWith('mod_job_medical_requirements_')) {
            const field_part = stored_name.substring('mod_job_medical_requirements_'.length);
            return 'mod_job_medical_requirements.' + field_part;
        }

        if (stored_name.startsWith('mod_jobs_')) {
            const field_part = stored_name.substring('mod_jobs_'.length);
            return 'mod_jobs.' + field_part;
        }

        if (stored_name.startsWith('usr_medical_emergency_details_')) {
            const field_part = stored_name.substring('usr_medical_emergency_details_'.length);
            return 'usr_medical_emergency_details.' + field_part;
        }

        return stored_name;
    }

    setFieldValue(fieldName, fieldValue) {
        let convertedFieldName = this.convert_to_form_field_name(fieldName);
        let $field = $(`[name="${convertedFieldName}"]`);

        if ($field.length === 0 && convertedFieldName.includes('.')) {
            const escapedName = convertedFieldName.replace(/\./g, '\\.');
            $field = $(`[name="${escapedName}"]`);
        }

        if ($field.length === 0) {
            $field = $(`[name="${fieldName}"]`);
        }

        if ($field.length === 0) {
            $field = $(`[data-field="${convertedFieldName}"]`);
        }

        if ($field.length > 0) {
            if ($field.is('select[multiple]')) {
                let valuesToSet = [];
                if (Array.isArray(fieldValue)) {
                    valuesToSet = fieldValue;
                } else if (typeof fieldValue === 'string' && fieldValue.includes(',')) {
                    valuesToSet = fieldValue.split(',').map(v => v.trim());
                } else if (fieldValue) {
                    valuesToSet = [fieldValue];
                }
                $field.val(valuesToSet).trigger('change');
            } else {
                $field.val(fieldValue).trigger('change');
            }
        }
    }

    saveForm() {
        console.log('SAVE BUTTON CLICKED FOR TEMPLATE:', this.templateId);

        // Sync CKEditor values
        this.editors.forEach(({
            editor
        }) => {
            if (editor) {
                const data = editor.getData();
                const sourceElement = editor.sourceElement;
                if (sourceElement) {
                    sourceElement.value = data;
                }
            }
        });

        // Collect all form data
        const formData = {};

        // Get CSRF token from form (after sync)
        const csrfToken = $('input[name="csrf_rfid_token"]').val();
        if (csrfToken) {
            formData['csrf_rfid_token'] = csrfToken;
        } else {
            console.error('No CSRF token found');
            this.showMessage('Security token not found. Please refresh the page.', 'error');
            return;
        }

        // Collect from dynamic form content
        $('#dynamic-form-content input, #dynamic-form-content select, #dynamic-form-content textarea').each((
            index, element) => {
            const $field = $(element);
            const name = $field.attr('name');
            let value = $field.val();

            if (name && value !== undefined && name !== 'csrf_rfid_token') {
                if ($field.is('select[multiple]')) {
                    const selectedValues = $field.val() || [];
                    if (Array.isArray(selectedValues)) {
                        formData[name] = selectedValues;
                    }
                } else {
                    formData[name] = value;
                }
            }
        });

        // Add hidden fields
        $('#main-form input[type="hidden"]').each((index, element) => {
            const $field = $(element);
            const name = $field.attr('name');
            const value = $field.val();

            if (name && value !== undefined && !formData[name] && name !== 'csrf_rfid_token') {
                formData[name] = value;
            }
        });

        console.log('Total fields to submit:', Object.keys(formData).length);

        if (Object.keys(formData).length === 0) {
            this.showMessage('No data to save!', 'error');
            return;
        }

        $('#save-button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        const templateId = $('input[name="id"]').val();
        const cacheBuster = 't=' + new Date().getTime();
        const url = '<?= site_url("agency/templates/update_ajax/") ?>' + templateId + '?' + cacheBuster;

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            traditional: true,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (data) => {
                // Update CSRF token if returned
                if (data.csrf) {
                    this.updateCSRFToken(data.csrf);
                }

                this.handleSaveResponse(data);
            },
            error: (xhr, status, error) => {
                console.error('Save error:', error, 'Response:', xhr.responseText);

                // Check if it's a CSRF error
                if (xhr.status === 403 || (xhr.responseText && xhr.responseText.includes('CSRF'))) {
                    this.showMessage(
                        'Security token expired. Please refresh the page and try again.',
                        'error');
                } else {
                    this.showMessage('Save failed: ' + (xhr.responseText || error), 'error');
                }

                $('#save-button').prop('disabled', false).html(
                    '<i class="fa fa-save"></i> Save Template Data');
            }
        });
    }

    handleSaveResponse(data) {
        console.log('Save response:', data);

        if (data.success) {
            this.showMessage(data.message || 'Template data saved successfully!', 'success');
            $('#save-button').prop('disabled', false).html('<i class="fa fa-save"></i> Save Template Data');
            this.showSaveAsJobButton();
        } else {
            this.showMessage(data.error || 'Failed to save template data', 'error');
            $('#save-button').prop('disabled', false).html('<i class="fa fa-save"></i> Save Template Data');
        }
    }

    showSaveAsJobButton() {
        const templateId = $('input[name="id"]').val();
        if (!templateId) return;

        $('#save-as-job-btn').remove();

        const jobButton = `
            <button type="button" class="btn btn-primary ml-2" id="save-as-job-btn">
                <i class="fa fa-briefcase"></i> Save as Job Listing
            </button>
        `;
        $('#save-button').after(jobButton);

        $('#save-as-job-btn').on('click', () => this.saveAsJob(templateId));
    }

    saveAsJob(templateId) {
        console.log('SAVE AS JOB BUTTON CLICKED FOR TEMPLATE:', templateId);

        // Sync CKEditor values
        this.editors.forEach(({
            editor
        }) => {
            if (editor) {
                const data = editor.getData();
                const sourceElement = editor.sourceElement;
                if (sourceElement) {
                    sourceElement.value = data;
                }
            }
        });

        const params = new URLSearchParams();
        let fieldCount = 0;

        const csrfToken = $('input[name="csrf_rfid_token"]').val();
        if (csrfToken) {
            params.append('csrf_rfid_token', csrfToken);
        }

        $('#dynamic-form-content input, #dynamic-form-content select, #dynamic-form-content textarea').each(
            (index, element) => {
                const $field = $(element);
                const name = $field.attr('name');
                let value = $field.val();

                if (name && value !== undefined) {
                    if ($field.is('select[multiple]')) {
                        const selectedValues = $field.val() || [];
                        if (Array.isArray(selectedValues)) {
                            selectedValues.forEach(val => {
                                params.append(name, val);
                            });
                            fieldCount += selectedValues.length;
                        }
                    } else {
                        params.append(name, value);
                        fieldCount++;
                    }
                }
            });

        $('input[type="hidden"]').each((index, element) => {
            const $field = $(element);
            const name = $field.attr('name');
            const value = $field.val();

            if (name && !params.has(name)) {
                params.append(name, value);
            }
        });

        console.log('Total fields to submit as Job:', fieldCount);

        if (fieldCount === 0) {
            this.showMessage('No data to save as Job!', 'error');
            return;
        }

        $('#save-as-job-btn').prop('disabled', true).html(
            '<i class="fa fa-spinner fa-spin"></i> Saving as Job...');

        const cacheBuster = 't=' + new Date().getTime();
        const url = `<?= site_url("agency/templates/save_as_job/") ?>${templateId}?${cacheBuster}`;

        fetch(url, {
                method: 'POST',
                body: params.toString(),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showMessage(data.message || 'Job listing saved successfully!', 'success');
                    setTimeout(() => {
                        this.closeForm();
                    }, 2000);
                } else {
                    this.showMessage(data.error || 'Failed to save as Job', 'error');
                }
                $('#save-as-job-btn').prop('disabled', false).html(
                    '<i class="fa fa-briefcase"></i> Save as Job Listing');
            })
            .catch(error => {
                console.error('Save as Job error:', error);
                this.showMessage('Save as Job failed: ' + error.message, 'error');
                $('#save-as-job-btn').prop('disabled', false).html(
                    '<i class="fa fa-briefcase"></i> Save as Job Listing');
            });
    }

    closeForm() {
        console.log('Closing quick manage form for Template:', this.templateId);
        this.destroy();

        if (typeof close_qm === 'function') {
            close_qm();
        } else {
            $('.quick-manage-overlay').remove();
            $('.quick-manage-container').empty();
            $('body').removeClass('qm-full-page');
        }
    }

    destroy() {
        console.log('DESTROYING QuickManageForm for Template:', this.templateId);

        this.editors.forEach(({
            editor
        }) => {
            if (editor && editor.destroy) {
                editor.destroy().catch(e => console.error('Failed to destroy CKEditor:', e));
            }
        });
        this.editors = [];

        this.fallbackEditors.forEach((editorData, textareaId) => {
            try {
                editorData.editor.off('input blur keyup paste');
                editorData.container.remove();
                editorData.original.show();
            } catch (e) {
                console.error('Failed to destroy fallback editor:', textareaId, e);
            }
        });
        this.fallbackEditors.clear();

        $('#save-button').off('click.quickmanage');
        $('#close-button').off('click.quickmanage');
        $('.close-quick-manage').off('click.quickmanage');
        $(document).off('keyup.quickmanage');
        $('#main-form').off('keypress.quickmanage');

        if (this.isComposite) {
            $('#add-section-btn').off('click.sectionmgr');
            $('#confirm-add-section').off('click.sectionmgr');
            $('#section-select').off('change.sectionmgr');
            $('#addSectionModal').off('hidden.bs.modal');
            $(document).off('click', '.delete-section');
            $('#confirm-delete-section').off('click.deletemgr');
            $('#deleteSectionModal').off('hidden.bs.modal');
            this.sectionManagerInitialized = false;
        }

        this.isInitialized = false;
        window.quickManageInitialized = false;
    }

    showMessage(message, type = 'info') {
        console.log(type.toUpperCase() + ':', message);
        if (typeof toastr !== 'undefined') {
            toastr[type === 'error' ? 'error' : 'success'](message);
        } else {
            alert(type.toUpperCase() + ': ' + message);
        }
    }
};

// Initialize QuickManageForm when DOM is ready
$(document).ready(function() {
    console.log('DOM Ready - Initializing QuickManageForm');

    if (window.quickManageApp) {
        window.quickManageApp.destroy();
        window.quickManageApp = null;
    }

    window.quickManageInitialized = false;

    setTimeout(() => {
        if (typeof window.QuickManageForm !== 'undefined') {
            try {
                window.quickManageApp = new window.QuickManageForm();
            } catch (error) {
                console.error('Failed to initialize QuickManageForm:', error);
                $('#editor-status').text('Initialization Failed');
            }
        }
    }, 100);
});

// Initialize multi-select if select2 is available
if (typeof $.fn.select2 !== 'undefined') {
    $('select[multiple]').select2({
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || 'Select options';
        }
    });
}

// Fix duplicate modal issue
function fixDuplicateModals() {
    console.log('🔧 FIXING DUPLICATE MODALS ISSUE');

    // Find all modals with id addSectionModal
    const modals = $('[id="addSectionModal"]');
    console.log(`Found ${modals.length} modals with id="addSectionModal"`);

    if (modals.length > 1) {
        // Keep the first one, remove the rest
        modals.slice(1).each(function(i) {
            console.log(`Removing duplicate modal ${i + 1}`);
            $(this).remove();
        });

        console.log(`Removed ${modals.length - 1} duplicate modals. Only 1 remains.`);
    }

    // Also check for modals without proper IDs
    const allModals = $('.modal');
    console.log(`Total .modal elements: ${allModals.length}`);

    // Re-initialize the remaining modal
    const remainingModal = $('#addSectionModal');
    if (remainingModal.length === 1) {
        console.log('Re-initializing the modal...');

        // Move to body to avoid container issues
        remainingModal.appendTo('body');

        // Hide and show to reset
        remainingModal.modal('hide');
        setTimeout(() => {
            remainingModal.modal('show');
            console.log('Modal should now work properly.');
        }, 300);
    }
}

// Also check where duplicates are coming from
function findModalSources() {
    console.log('\n🔍 FINDING WHERE MODALS COME FROM');

    // Check if quick manage is creating duplicates
    console.log('Checking QuickManageForm initialization...');
    if (window.quickManageApp) {
        console.log('QuickManageApp exists');
        console.log('Is composite?', window.quickManageApp.isComposite);
    }

    // Check HTML sources
    $('[id="addSectionModal"]').each(function(i) {
        console.log(`\nModal ${i}:`);
        console.log('Parent chain:');
        let parent = $(this).parent();
        let depth = 0;
        while (parent.length && depth < 5) {
            console.log(
                `  ${'  '.repeat(depth)}${parent[0].tagName}${parent.attr('id') ? '#' + parent.attr('id') : ''}${parent.attr('class') ? '.' + parent.attr('class').split(' ')[0] : ''}`
            );
            parent = parent.parent();
            depth++;
        }

        // Check if it's inside quick manage container
        const inQM = $(this).closest('.quick-manage-container, .quick-manage-form-container, .qmfc').length > 0;
        console.log('In quick manage container?', inQM);
    });
}

// Emergency fix - Create a fresh modal
function createFreshModal() {
    console.log('🔄 CREATING FRESH MODAL');

    // Remove all existing addSectionModal elements
    $('[id="addSectionModal"]').remove();

    // Create new modal HTML
    const newModalHTML = `
    <div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSectionModalLabel">Add New Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-times" aria-hidden="true"></i></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="section-select" class="form-label">Select a Section</label>
                        <select class="form-select" id="section-select">
                            <option value="">Select a section...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirm-add-section">Add Section</button>
                </div>
            </div>
        </div>
    </div>
    `;

    // Add to body
    $('body').append(newModalHTML);

    console.log('New modal created. Now re-initializing event handlers...');

    // Re-initialize event handlers
    if (window.quickManageApp && window.quickManageApp.setupSectionManager) {
        // Remove old handlers first
        $('#add-section-btn').off('click.sectionmgr');
        $('#confirm-add-section').off('click.sectionmgr');
        $('#section-select').off('change.sectionmgr');

        // Re-setup section manager
        window.quickManageApp.sectionManagerInitialized = false;
        window.quickManageApp.setupSectionManager();

        console.log('Event handlers re-initialized.');
    }

    // Show the modal
    setTimeout(() => {
        $('#addSectionModal').modal('show');
        console.log('Fresh modal shown.');
    }, 100);
}
</script>