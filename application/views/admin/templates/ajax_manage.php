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
        <form id="main-form" method="post" accept-charset="utf-8"
            data-saved-data='<?= json_encode($debug_form_data ?? []) ?>'>
            <!-- ✅ CSRF PROTECTION ADDED -->
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                value="<?php echo $this->security->get_csrf_hash(); ?>" />

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
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
    /* Ensure overlay is below modal */
}

body.qm-full-page .quick-manage-overlay {
    pointer-events: none;
    /* Allow clicks through to modal */
}

#dynamic-form-content {
    pointer-events: auto !important;
    /* Ensure form remains clickable */
}

/* Add to your <style> block in the view for better delete button styling */
.delete-section {
    white-space: nowrap;
}

.composite-section:hover .delete-section {
    opacity: 1;
}

.delete-section:hover {
    background-color: #c82333 !important;
}

/* Add to your <style> block in the view - ensures delete buttons are always clickable and visible */
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
    /* Only gray if actually disabled */
}

.quick-manage-overlay {
    pointer-events: none !important;
    /* Allow clicks through overlay to buttons */
}

#dynamic-form-content {
    pointer-events: auto !important;
}

.composite-section:hover .section-header .delete-section {
    opacity: 1 !important;
    transform: none;
    /* Prevent any hover transforms blocking */
}

#deleteSectionModal .modal-footer button {
    pointer-events: auto !important;
    z-index: 1070 !important;
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
            // Better href parse for composite ?template_id= param
            const urlParams = new URLSearchParams(href.split('?')[1] || '');
            templateId = urlParams.get('template_id') ||
                href.split('/').filter(part => /^\d+$/.test(part)).pop(); // Last numeric part
        }

        if (!templateId || isNaN(templateId)) {
            let $row = $(this).closest('tr');
            templateId = $row.attr('data-id') || $row.data('template-id'); // Try attr too
        }

        console.log('Extracted template ID steps:', { // ADD LOG FOR ALL
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
            console.log('Button:', $(this)[0]); // ADD: Inspect button HTML
            return;
        }

        console.log('Loading quick manage for template:', templateId);

        // Use aggressive cache buster
        var cacheBuster = 't=' + new Date().getTime();
        var url = 'admin/templates/ajax_quick_manage/' + templateId + '?' +
            cacheBuster;

        console.log('Fetching from URL:', url);

        // Show loading
        if (typeof show_loading === 'function') {
            show_loading();
        } else {
            // Fallback loading indicator
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
                console.log('Status:', status);
                console.log('XHR:', xhr);

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
window.quickManageInitialized = false;

if (typeof window.QuickManageForm === 'undefined') {
    window.QuickManageForm = class QuickManageForm {
        constructor() {
            this.editors = [];
            this.templateId = <?= !empty($row->id) ? $row->id : 'null' ?>;
            this.isInitialized = false;
            this.ckeditorReady = false;
            this.fallbackEditors = new Map();
            this.editorPromises = [];
            this.currentTemplateData = <?= json_encode($debug_form_data ?? []) ?>;
            this.isComposite = <?= json_encode($is_composite ?? false) ?>;
            this.init();
        }

        init() {
            console.log('Raw currentTemplateData from embed:', JSON.stringify(this.currentTemplateData, null,
                2)); // CONFIRM EMBEDDED JSON

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
                this.cleanupForm();
                this.styleSectionHeaders();
                this.setupEventHandlers();
                if (this.isComposite) {
                    this.setupSectionManager();
                }
                await this.initializeWithCKEditor();
            } catch (error) {
                console.error('CKEditor v5 initialization failed:', error);
                await this.initializeWithFallback();
            }

            this.setFormValues();
            $('#editor-status').text('Ready - Template: ' + this.templateId);
            this.isInitialized = true;
            this.debugFormState('FINAL INIT COMPLETE');
        }

        setupSectionManager() {
            console.log('Setting up section manager for composite template:', this.templateId);

            // Handle Add Section button
            $('#add-section-btn').off('click.sectionmgr').on('click.sectionmgr', () => {
                console.log('Add section button clicked');
                $('#addSectionModal').appendTo('body').css('z-index', '1060').modal('show');
                this.loadAvailableSections();
            });

            // Handle confirm add
            $('#confirm-add-section').off('click.sectionmgr').on('click.sectionmgr', () => {
                const sectionId = $('#section-select').val();
                if (!sectionId) {
                    this.showMessage('Please select a section', 'error');
                    return;
                }
                this.addSection(sectionId);
            });

            // Enable/disable confirm button based on selection
            $('#section-select').off('change.sectionmgr').on('change.sectionmgr', (e) => {
                const val = $(e.target).val();
                $('#confirm-add-section').prop('disabled', !val);
            });

            // In setupSectionManager(), update the delete button click handler:
            $(document).off('click', '.delete-section').on('click', '.delete-section', (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation(); // Prevent any other handlers

                const $btn = $(e.currentTarget);
                if ($btn.prop('disabled')) {
                    console.log('Delete button disabled - skipping');
                    return;
                }

                const sectionId = $btn.data('section-id');
                const sectionName = $btn.closest('.composite-section').find('h5').text().trim();

                // Populate modal message with section name
                $('#delete-section-message').html(
                    `Are you sure you want to delete the section "<strong>${sectionName}</strong>"? This action cannot be
        undone and will remove all associated form data for this section.`
                );
                $('#confirm-delete-section').data('section-id', sectionId).prop('disabled',
                    false); // ENABLE THE BUTTON HERE

                // Show modal (append to body for z-index, similar to CRUD_Controller AJAX handling)
                $('#deleteSectionModal').appendTo('body').css({
                    'z-index': '1060',
                    'pointer-events': 'auto'
                }).modal('show');

                // Force focus to modal for accessibility (avoids aria-hidden issues)
                $('#deleteSectionModal').on('shown.bs.modal', () => {
                    $('#deleteSectionModal .btn-secondary').focus();
                });
            });

            // Handle confirm delete (unchanged)
            $('#confirm-delete-section').off('click.deletemgr').on('click.deletemgr', (e) => {
                const sectionId = $(e.currentTarget).data('section-id');
                if (!sectionId) {
                    this.showMessage('No section selected for deletion', 'error');
                    return;
                }
                $('#deleteSectionModal').modal('hide');
                this.removeSection(sectionId);
            });

            // Close modal handlers (update to ensure re-disable)
            $('#deleteSectionModal').off('hidden.bs.modal').on('hidden.bs.modal', () => {
                $('#confirm-delete-section').removeData('section-id').prop('disabled', true);
                // Re-append to original container if desired (or leave in body)
                $('#deleteSectionModal').appendTo('.section-manager');
            });

            // Close modal handlers
            $('#addSectionModal').off('hidden.bs.modal').on('hidden.bs.modal', () => {
                $('#section-select').val('');
                $('#confirm-add-section').prop('disabled', true);
                // Re-append to original container if desired (or leave in body)
                $('#addSectionModal').appendTo('.section-manager');
            });

            $('#deleteSectionModal').off('hidden.bs.modal').on('hidden.bs.modal', () => {
                $('#confirm-delete-section').removeData('section-id').prop('disabled', true);
                // Re-append to original container if desired (or leave in body)
                $('#deleteSectionModal').appendTo('.section-manager');
            });

            // After setup, ensure all delete buttons are enabled and clickable
            this.enableDeleteButtons();
        }

        async loadAvailableSections() {
            console.log('Loading available sections for template:', this.templateId);
            try {
                const response = await fetch(
                    `<?= site_url('admin/templates/get_available_sections_for_template/') ?>${this.templateId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                if (!response.ok) throw new Error('Failed to fetch sections');
                const data = await response.json();
                if (!data.success) throw new Error(data.error || 'Unknown error');

                const select = $('#section-select');
                select.empty().append('<option value="">Select a section...</option>');
                data.sections.forEach(section => {
                    select.append(
                        `<option value="${section.id}">${section.name || 'Unnamed Section'} (${section.section_type || 'General'})</option>`
                    );
                });

                // Enable button immediately if sections loaded (user can still change selection)
                if (data.sections.length > 0) {
                    $('#confirm-add-section').prop('disabled', false);
                } else {
                    $('#confirm-add-section').prop('disabled', true);
                }

                console.log(`Loaded ${data.sections.length} available sections`);
            } catch (error) {
                console.error('Failed to load available sections:', error);
                this.showMessage('Failed to load sections: ' + error.message, 'error');
                $('#section-select').html('<option value="">Error loading sections</option>');
                $('#confirm-add-section').prop('disabled', true);
            }
        }

        async addSection(sectionId) {
            console.log('Adding section ID', sectionId, 'to template:', this.templateId);
            try {
                const formData = new FormData();
                formData.append('section_id', sectionId);

                const response = await fetch(
                    `<?= site_url('admin/templates/add_section_to_template/') ?>${this.templateId}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                if (!response.ok) throw new Error('HTTP ' + response.status);
                const data = await response.json();

                if (data.success) {
                    console.log('Section added successfully');
                    $('#addSectionModal').modal('hide');
                    this.showMessage(data.message || 'Section added successfully', 'success');
                    await this.refreshQuickManage();
                } else {
                    throw new Error(data.error || 'Unknown error');
                }
            } catch (error) {
                console.error('Failed to add section:', error);
                this.showMessage('Failed to add section: ' + error.message, 'error');
            }
        }

        async removeSection(sectionId) {
            console.log('Removing section ID', sectionId, 'from template:', this.templateId);
            try {
                const formData = new FormData();
                formData.append('section_id', sectionId);

                const response = await fetch(
                    `<?= site_url('admin/templates/remove_section_from_template/') ?>${this.templateId}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                if (!response.ok) throw new Error('HTTP ' + response.status);
                const data = await response.json();

                if (data.success) {
                    console.log('Section removed successfully');
                    this.showMessage(data.message || 'Section removed successfully', 'success');
                    await this.refreshQuickManage();
                } else {
                    throw new Error(data.error || 'Unknown error');
                }
            } catch (error) {
                console.error('Failed to remove section:', error);
                this.showMessage('Failed to remove section: ' + error.message, 'error');
            }
        }

        async refreshQuickManage() {
            console.log('Refreshing quick manage for template:', this.templateId);
            try {
                const cacheBuster = 't=' + new Date().getTime();
                const url =
                    `<?= site_url('admin/templates/ajax_quick_manage/') ?>${this.templateId}?${cacheBuster}`;
                const response = await fetch(url);
                if (!response.ok) throw new Error('Failed to fetch updated content');
                const html = await response.text();

                // Parse new HTML and replace the form container
                const $newDoc = $('<div>').html(html);
                const $newFormContainer = $newDoc.find('.quick-manage-form-container');
                $('.quick-manage-form-container').html($newFormContainer.html());

                // Re-initialize the form (destroy old, create new)
                if (window.quickManageApp) {
                    window.quickManageApp.destroy();
                }
                window.quickManageApp = new window.QuickManageForm();

                // Wait a tick for DOM updates, then enable buttons
                setTimeout(() => {
                    if (window.quickManageApp && window.quickManageApp.enableDeleteButtons) {
                        window.quickManageApp.enableDeleteButtons();
                    }
                }, 500); // Adjust if needed based on CKEditor load time

                console.log('Quick manage refreshed successfully');
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
                    })
                    .removeAttr('aria-disabled');
                console.log(`Enabled delete button for section: ${$btn.data('section-id')}`);
            });
        }

        async initializeWithCKEditor() {
            console.log('Attempting CKEditor v5 initialization...');

            if (typeof ClassicEditor === 'undefined') {
                await this.loadCKEditor();
            }

            await this.initializeAllCKEditors();
            this.ckeditorReady = true;
            console.log('CKEditor v5 initialized successfully');
        }

        async initializeWithFallback() {
            console.log('Using fallback editor initialization...');
            await new Promise(resolve => setTimeout(resolve, 200));
            this.initializeFallbackEditors();
            console.log('Fallback editors initialized');
        }

        loadCKEditor() {
            return new Promise((resolve, reject) => {
                console.log('Loading CKEditor v5...');

                if (document.querySelector('script[src*="ckeditor5"]')) {
                    console.log('CKEditor script already in DOM, waiting for load...');
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
                        console.error('All CKEditor paths failed');
                        reject(new Error('All CKEditor v5 paths failed'));
                        return;
                    }

                    const scriptUrl = ckeditorPaths[index];
                    console.log('Trying CKEditor path:', scriptUrl);

                    const script = document.createElement('script');
                    script.src = scriptUrl;
                    script.onload = () => {
                        console.log('CKEditor script loaded successfully from:', scriptUrl);
                        this.waitForCKEditor(resolve, reject);
                    };
                    script.onerror = () => {
                        console.error('Failed to load CKEditor from:', scriptUrl);
                        tryLoadCKEditor(index + 1);
                    };

                    document.head.appendChild(script);
                };

                tryLoadCKEditor(0);
            });
        }

        waitForCKEditor(resolve, reject) {
            let attempts = 0;
            const maxAttempts = 100;
            const interval = 100;

            const checkCKEditor = () => {
                attempts++;
                if (typeof ClassicEditor !== 'undefined' && typeof ClassicEditor.create === 'function') {
                    console.log('CKEditor v5 fully loaded after', attempts * interval, 'ms');
                    resolve();
                } else if (attempts >= maxAttempts) {
                    console.error('CKEditor v5 timeout - ClassicEditor still not available');
                    reject(new Error('CKEditor v5 timeout'));
                } else {
                    setTimeout(checkCKEditor, interval);
                }
            };
            checkCKEditor();
        }

        initializeAllCKEditors() {
            return new Promise((resolve, reject) => {
                if (typeof ClassicEditor === 'undefined') {
                    console.log('ClassicEditor not available, rejecting...');
                    reject(new Error('ClassicEditor not available'));
                    return;
                }

                console.log('Initializing CKEditor v5 instances...');
                const textareas = $('textarea[id^="ckeditor_"]');
                console.log('Found CKEditor textareas:', textareas.length);

                if (textareas.length === 0) {
                    console.log('No CKEditor textareas found');
                    resolve();
                    return;
                }

                let initialized = 0;
                const total = textareas.length;
                const initializationErrors = [];

                textareas.each((index, textarea) => {
                    const $textarea = $(textarea);
                    const textareaId = $textarea.attr('id');

                    if (!textareaId) {
                        initialized++;
                        if (initialized === total) {
                            if (initializationErrors.length > 0) {
                                reject(new Error('CKEditor initialization errors: ' +
                                    initializationErrors.join(', ')));
                            } else {
                                resolve();
                            }
                        }
                        return;
                    }

                    if (this.editors.find(e => e.id === textareaId)) {
                        console.log('CKEditor already initialized for:', textareaId);
                        initialized++;
                        if (initialized === total) {
                            if (initializationErrors.length > 0) {
                                reject(new Error('CKEditor initialization errors: ' +
                                    initializationErrors.join(', ')));
                            } else {
                                resolve();
                            }
                        }
                        return;
                    }

                    console.log('Creating CKEditor for:', textareaId);

                    ClassicEditor
                        .create(textarea, {
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
                            height: 200,
                            removePlugins: [
                                'MediaEmbedToolbar'
                            ]
                        })
                        .then(editor => {
                            console.log('CKEditor successfully created for:', textareaId);

                            this.editors.push({
                                id: textareaId,
                                editor: editor
                            });

                            editor.model.document.on('change:data', () => {
                                const data = editor.getData();
                                const sourceElement = editor.sourceElement;
                                if (sourceElement) {
                                    sourceElement.value = data;
                                }
                            });

                            initialized++;
                            console.log(`CKEditor ${initialized}/${total} initialized:`,
                                textareaId);

                            if (initialized === total) {
                                if (initializationErrors.length > 0) {
                                    reject(new Error('CKEditor initialization errors: ' +
                                        initializationErrors.join(', ')));
                                } else {
                                    resolve();
                                }
                            }
                        })
                        .catch(error => {
                            console.error('CKEditor init failed for ' + textareaId + ':',
                                error);
                            initializationErrors.push(textareaId + ': ' + error.message);
                            initialized++;

                            if (initialized === total) {
                                if (initializationErrors.length > 0) {
                                    reject(new Error('CKEditor initialization errors: ' +
                                        initializationErrors.join(', ')));
                                } else {
                                    resolve();
                                }
                            }
                        });
                });
            });
        }

        initializeFallbackEditors() {
            console.log('Initializing fallback editors...');
            this.destroyFallbackEditors();

            $('textarea[id^="ckeditor_"]').each((index, textarea) => {
                const $textarea = $(textarea);
                const textareaId = $textarea.attr('id');

                if (!textareaId || this.fallbackEditors.has(textareaId)) return;

                this.createFallbackEditor($textarea);
            });

            console.log('Fallback editors initialized for', this.fallbackEditors.size, 'textareas');
        }

        createFallbackEditor($textarea) {
            const textareaId = $textarea.attr('id');
            const originalValue = $textarea.val() || '';

            const editorContainer = $('<div class="fallback-editor-container mb-3"></div>');
            const toolbar = $('<div class="fallback-editor-toolbar mb-2"></div>');
            const editor = $('<div class="fallback-editor" contenteditable="true"></div>');

            const buttons = [{
                    label: '<strong>B</strong>',
                    command: 'bold',
                    class: 'btn btn-sm btn-outline-secondary'
                },
                {
                    label: '<em>I</em>',
                    command: 'italic',
                    class: 'btn btn-sm btn-outline-secondary'
                },
                {
                    label: '<u>U</u>',
                    command: 'underline',
                    class: 'btn btn-sm btn-outline-secondary'
                },
                {
                    label: '• List',
                    command: 'insertUnorderedList',
                    class: 'btn btn-sm btn-outline-secondary'
                },
                {
                    label: '1. List',
                    command: 'insertOrderedList',
                    class: 'btn btn-sm btn-outline-secondary'
                }
            ];

            buttons.forEach(button => {
                const btn = $(
                    `<button type="button" class="${button.class}" data-command="${button.command}">${button.label}</button>`
                );
                btn.on('click', (e) => {
                    e.preventDefault();
                    document.execCommand(button.command, false, null);
                    editor.focus();
                });
                toolbar.append(btn);
            });

            editor.html(originalValue);
            editor.css({
                'min-height': '200px',
                'border': '1px solid #ddd',
                'padding': '10px',
                'background': 'white',
                'outline': 'none',
                'border-radius': '4px'
            });

            const syncEditor = () => {
                $textarea.val(editor.html());
            };
            editor.on('input blur keyup paste', syncEditor);

            editorContainer.append(toolbar);
            editorContainer.append(editor);
            $textarea.hide().after(editorContainer);

            this.fallbackEditors.set(textareaId, {
                container: editorContainer,
                original: $textarea,
                editor: editor
            });

            console.log('Fallback editor created for:', textareaId);
        }

        destroy() {
            console.log('DESTROYING QuickManageForm for Template:', this.templateId);

            this.destroyCKEditors();
            this.destroyFallbackEditors();

            $('#save-button').off('click.quickmanage');
            $('#close-button').off('click.quickmanage');
            $('.close-quick-manage').off('click.quickmanage');
            $(document).off('keyup.quickmanage');
            $('#main-form').off('keypress.quickmanage');

            // Clean up section manager
            if (this.isComposite) {
                $('#add-section-btn').off('click.sectionmgr');
                $('#confirm-add-section').off('click.sectionmgr');
                $('#section-select').off('change.sectionmgr');
                $('#addSectionModal').off('hidden.bs.modal');
                $(document).off('click', '.delete-section');
                $('#confirm-delete-section').off('click.deletemgr');
                $('#deleteSectionModal').off('hidden.bs.modal');
            }

            this.currentTemplateData = {};
            this.isInitialized = false;
            window.quickManageInitialized = false;
            this.editorPromises = [];

            console.log('QuickManageForm destroyed for Template:', this.templateId);
        }

        destroyCKEditors() {
            console.log('Destroying CKEditor instances:', this.editors.length);
            this.editors.forEach(({
                id,
                editor
            }) => {
                if (editor) {
                    editor.destroy().then(() => {
                        console.log('CKEditor destroyed:', id);
                    }).catch(error => {
                        console.error('Failed to destroy CKEditor:', id, error);
                    });
                }
            });
            this.editors = [];
        }

        destroyFallbackEditors() {
            console.log('Destroying fallback editors:', this.fallbackEditors.size);
            this.fallbackEditors.forEach((editorData, textareaId) => {
                try {
                    editorData.editor.off('input blur keyup paste');
                    editorData.container.remove();
                    editorData.original.show();
                    console.log('Fallback editor destroyed:', textareaId);
                } catch (e) {
                    console.error('Failed to destroy fallback editor:', textareaId, e);
                }
            });
            this.fallbackEditors.clear();
        }

        cleanupForm() {
            console.log('Cleaning up form for Template:', this.templateId);

            $('#dynamic-form-content form').each(function() {
                console.log('Removing nested form tag');
                const $nestedForm = $(this);
                const formContent = $nestedForm.html();
                $nestedForm.replaceWith('<div class="dynamic-form-fields">' + formContent + '</div>');
            });

            $('.dynamic-form-fields .btn-container').remove();
            $('.dynamic-form-fields .btn-primary').remove();
        }

        styleSectionHeaders() {
            console.log('Styling section headers for Template:', this.templateId);

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

            console.log('Section headers styled');
        }

        setupEventHandlers() {
            $('#save-button').off('click.quickmanage').on('click.quickmanage', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.saveForm();
            });

            $('#close-button').off('click.quickmanage').on('click.quickmanage', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.closeForm();
            });

            $('.close-quick-manage').off('click.quickmanage').on('click.quickmanage', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.closeForm();
            });

            $(document).off('keyup.quickmanage').on('keyup.quickmanage', (e) => {
                if (e.keyCode === 27) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.closeForm();
                }
            });

            // Updated keypress handler: Only prevent Enter for non-editable elements
            $('#main-form').off('keypress.quickmanage').on('keypress.quickmanage', (e) => {
                // Skip if inside CKEditor editable, fallback editor, textarea, or contenteditable
                const $target = $(e.target);
                const isInEditor = $target.is('textarea') ||
                    $target.closest('.ck-editor__editable').length > 0 ||
                    ($target.is('[contenteditable="true"]') || $target.closest(
                        '[contenteditable="true"]').length > 0);

                if (!isInEditor && e.keyCode === 13) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }

        debugFormFields() {
            console.log('=== DEBUG FORM FIELDS ===');
            const allFields = $(
                '#dynamic-form-content input, #dynamic-form-content select, #dynamic-form-content textarea');
            console.log('Total fields found in DOM:', allFields.length);

            allFields.each((index, element) => {
                const $el = $(element);
                const name = $el.attr('name');
                const id = $el.attr('id');
                const type = $el.attr('type');
                const value = $el.val();

                console.log(`Field ${index + 1}:`, {
                    name: name,
                    id: id,
                    type: type,
                    value: value ? value.substring(0, 50) : 'empty'
                });
            });
            console.log('=== END DEBUG ===');
        }

        setFormValues() {
            console.log('=== SETTING FORM VALUES for Template:', this.templateId, '===');
            console.log('Data to set:', JSON.stringify(this.currentTemplateData, null, 2));

            // Wait for DOM to be fully ready
            setTimeout(() => {
                // Debug: Check what fields are actually in the DOM
                this.debugFormFields();

                if (!this.currentTemplateData || Object.keys(this.currentTemplateData).length === 0) {
                    console.log('No form data to set');
                    return;
                }

                Object.keys(this.currentTemplateData).forEach(fieldName => {
                    const fieldValue = this.currentTemplateData[fieldName];

                    // Fix: Ensure fieldValue is a string before using substring
                    const displayValue = typeof fieldValue === 'string' ? fieldValue.substring(
                        0, 30) : String(fieldValue);

                    console.log('  Pre-set check for ' + fieldName + ': current DOM val = "' +
                        ($(`[name="${fieldName}"]`).val() || 'NOT FOUND') + '"');

                    this.setFieldValue(fieldName, fieldValue);

                    console.log('  Post-set check for ' + fieldName + ': new DOM val = "' +
                        ($(`[name="${fieldName}"]`).val() || 'NOT FOUND') + '"');
                });

                console.log('Form values set for Template:', this.templateId);
            }, 500);
        }

        setFieldValue(fieldName, fieldValue) {
            // ✅ IMPROVED: Try multiple selector patterns
            let $field = $(`[name="${fieldName}"]`);

            // If not found, try with escaped dots (for mod_jobs.name format)
            if ($field.length === 0 && fieldName.includes('.')) {
                const escapedName = fieldName.replace(/\./g, '\\.');
                $field = $(`[name="${escapedName}"]`);
            }

            // If still not found, try by data attribute
            if ($field.length === 0) {
                $field = $(`[data-field="${fieldName}"]`);
            }

            console.log('  Targeting field for ' + fieldName + ': found ' + $field.length + ' elements');

            if ($field.length > 0) {
                let currentVal = $field.val() || '';

                // Handle multi-select fields
                if ($field.is('select[multiple]')) {
                    // Convert fieldValue to array if it's a string
                    let valuesToSet = [];
                    if (Array.isArray(fieldValue)) {
                        valuesToSet = fieldValue;
                    } else if (typeof fieldValue === 'string' && fieldValue.includes(',')) {
                        valuesToSet = fieldValue.split(',').map(v => v.trim());
                    } else if (fieldValue) {
                        valuesToSet = [fieldValue];
                    }

                    $field.val(valuesToSet).trigger('change');
                    console.log('  Setting multi-select for ' + fieldName + ' = ' + valuesToSet.join(', '));

                } else {
                    // Handle single value fields
                    let currentDisplay = Array.isArray(currentVal) ? currentVal.join(', ') : currentVal;
                    let fieldDisplay = Array.isArray(fieldValue) ? fieldValue.join(', ') : fieldValue;

                    // Only skip if current value is different AND not empty
                    if (currentVal && typeof currentVal === 'string' && currentVal.trim() !== '' &&
                        currentVal.trim() !== String(fieldValue).trim()) {
                        console.warn('Skipping set for ' + fieldName + ': DOM has "' + currentDisplay.substring(
                                0, 30) +
                            '", JS has "' + (typeof fieldDisplay === 'string' ? fieldDisplay.substring(0,
                                    30) :
                                fieldDisplay) + '" – trusting pre-pop');
                        return; // Skip overwrite
                    }

                    // If current value is empty or same as what we want to set, proceed
                    console.log('  Setting plain val for ' + fieldName + ' = ' + (typeof fieldValue ===
                        'string' ? fieldValue.substring(0, 30) : fieldValue));
                    $field.val(fieldValue).trigger('change');
                }
            } else {
                console.warn('Field not found:', fieldName);
                // FALLBACK SEARCH: Log all names for debugging
                console.log('  All form names:', $('#dynamic-form-content [name]').map((i, el) => $(el).attr(
                    'name')).get());
            }
        }

        setCKEditorValue(editorId, value) {
            const editorInstance = this.editors.find(e => e.id === editorId);
            if (editorInstance && editorInstance.editor) {
                try {
                    $(`#${editorId}`).val(value);
                    editorInstance.editor.setData(value);
                    console.log('CKEditor value set:', editorId);
                } catch (e) {
                    console.error('Failed to set CKEditor value:', editorId, e);
                }
            } else {
                console.warn('CKEditor instance not found for:', editorId);
            }
        }

        setFallbackEditorValue(editorId, value) {
            if (this.fallbackEditors.has(editorId)) {
                const editorData = this.fallbackEditors.get(editorId);
                editorData.original.val(value);
                editorData.editor.html(value);
                console.log('Fallback editor value set:', editorId);
            } else {
                console.warn('Fallback editor not found for:', editorId);
            }
        }

        // In your QuickManageForm class, update these methods:

        debugFormState(stage) {
            console.log(`=== FORM STATE ${stage} for Template ${this.templateId} ===`);

            const elements = $(
                '#dynamic-form-content input, #dynamic-form-content select, #dynamic-form-content textarea'
            );
            console.log(`Total form elements: ${elements.length}`);

            elements.each((index, element) => {
                const $el = $(element);
                if ($el.attr('name')) {
                    let value = $el.val();
                    // Handle multi-select arrays
                    if (Array.isArray(value)) {
                        value = value.join(', ');
                    } else if (typeof value === 'string') {
                        value = value.substring(0, 50);
                    }
                    console.log(`Field ${index + 1}:`, $el.attr('name'), '=', value);
                }
            });

            console.log('CKEditor instances:', this.editors.length);
            console.log('Fallback editors:', Array.from(this.fallbackEditors.keys()));
            console.log('=== END FORM STATE ===');
        }

        debugFormFields() {
            console.log('=== DEBUG FORM FIELDS ===');
            const allFields = $(
                '#dynamic-form-content input, #dynamic-form-content select, #dynamic-form-content textarea'
            );
            console.log('Total fields found in DOM:', allFields.length);

            allFields.each((index, element) => {
                const $el = $(element);
                const name = $el.attr('name');
                const id = $el.attr('id');
                const type = $el.attr('type');
                let value = $el.val();

                // Handle multi-select arrays and other non-string values
                let displayValue;
                if (Array.isArray(value)) {
                    displayValue = value.join(', ');
                } else if (value && typeof value === 'string') {
                    displayValue = value.substring(0, 50);
                } else if (value === null || value === undefined) {
                    displayValue = 'empty';
                } else {
                    displayValue = String(value);
                }

                console.log(`Field ${index + 1}:`, {
                    name: name,
                    id: id,
                    type: type,
                    value: displayValue
                });
            });
            console.log('=== END DEBUG ===');
        }

        saveForm() {
            console.log('SAVE BUTTON CLICKED FOR TEMPLATE:', this.templateId);

            this.syncAllEditors();

            const formData = new FormData();
            let fieldCount = 0;

            $('#dynamic-form-content input, #dynamic-form-content select, #dynamic-form-content textarea').each(
                (index, element) => {
                    const $field = $(element);
                    const name = $field.attr('name');
                    let value = $field.val();

                    if (name && value !== undefined) {
                        formData.append(name, value);
                        fieldCount++;
                    }
                });

            $('input[type="hidden"]').each((index, element) => {
                const $field = $(element);
                const name = $field.attr('name');
                const value = $field.val();

                if (name && !formData.has(name)) {
                    formData.append(name, value);
                }
            });

            console.log('Total fields to submit for Template', this.templateId, ':', fieldCount);

            if (fieldCount === 0) {
                this.showMessage('No data to save!', 'error');
                return;
            }

            $('#save-button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

            const templateId = $('input[name="id"]').val();
            const cacheBuster = 't=' + new Date().getTime();
            const url = '<?= site_url("admin/templates/update_ajax/") ?>' + templateId + '?' + cacheBuster;

            fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    // ✅ FIX: First check if response is OK
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Raw response:', text);

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        // ✅ FIX: Better error handling for invalid JSON
                        console.error('JSON parse error:', e, 'Response text:', text);

                        // Check if it's an HTML error page
                        if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                            throw new Error('Server returned HTML instead of JSON. Check for PHP errors.');
                        } else {
                            throw new Error('Invalid JSON response from server: ' + text.substring(0, 100));
                        }
                    }
                    this.handleSaveResponse(data);
                })
                .catch(error => {
                    console.error('Save error for Template', this.templateId, ':', error);
                    this.showMessage('Save failed: ' + error.message, 'error');
                    $('#save-button').prop('disabled', false).html(
                        '<i class="fa fa-save"></i> Save Template Data');
                });
        }

        syncAllEditors() {
            if (this.ckeditorReady && this.editors.length > 0) {
                this.editors.forEach(({
                    editor
                }) => {
                    if (editor) {
                        try {
                            const data = editor.getData();
                            const sourceElement = editor.sourceElement;
                            if (sourceElement) {
                                sourceElement.value = data;
                            }
                            console.log('Synced CKEditor v5');
                        } catch (e) {
                            console.error('Failed to sync CKEditor v5:', e);
                        }
                    }
                });
            }

            this.fallbackEditors.forEach((editorData) => {
                editorData.editor.trigger('blur');
            });
        }

        handleSaveResponse(data) {
            console.log('Save response for Template', this.templateId, ':', data);

            if (data.success) {
                this.showMessage(data.message || 'Template data saved successfully!', 'success');

                // FIXED: Re-enable the save button and reset its HTML after success
                $('#save-button').prop('disabled', false).html('<i class="fa fa-save"></i> Save Template Data');

                // NEW: Do NOT close the form immediately. Instead, show "Save as Job" button
                this.showSaveAsJobButton();
            } else {
                this.showMessage(data.error || 'Failed to save template data', 'error');
                $('#save-button').prop('disabled', false).html('<i class="fa fa-save"></i> Save Template Data');
            }
        }

        showSaveAsJobButton() {
            const templateId = $('input[name="id"]').val();
            if (!templateId) {
                console.warn('No template ID found for Save as Job');
                return;
            }

            // Remove existing button if present
            $('#save-as-job-btn').remove();

            // Append new button to form footer
            const jobButton = `
            <button type="button" class="btn btn-primary ml-2" id="save-as-job-btn">
                <i class="fa fa-briefcase"></i> Save as Job Listing
            </button>
        `;
            $('#save-button').after(jobButton);

            // Bind click event
            $('#save-as-job-btn').on('click', () => this.saveAsJob(templateId));
        }

        saveAsJob(templateId) {
            console.log('SAVE AS JOB BUTTON CLICKED FOR TEMPLATE:', templateId);

            this.syncAllEditors();

            // ✅ FIX: Initialize formData properly
            const formData = new FormData();
            let fieldCount = 0;

            // Collect all form fields (same as saveForm)
            $('#dynamic-form-content input, #dynamic-form-content select, #dynamic-form-content textarea').each(
                (index, element) => {
                    const $field = $(element);
                    const name = $field.attr('name');
                    let value = $field.val();

                    if (name && value !== undefined) {
                        // ✅ FIX: Handle multi-select fields properly
                        if ($field.is('select[multiple]')) {
                            // For multi-select, get all selected values as array
                            const selectedValues = $field.val() || [];
                            if (Array.isArray(selectedValues)) {
                                selectedValues.forEach(val => {
                                    formData.append(name,
                                        val); // Keep original field name for template forms
                                });
                            }
                            fieldCount += selectedValues.length;
                        } else {
                            formData.append(name, value);
                            fieldCount++;
                        }
                    }
                });

            // ✅ FIX: Add hidden fields
            $('input[type="hidden"]').each((index, element) => {
                const $field = $(element);
                const name = $field.attr('name');
                const value = $field.val();

                if (name && !formData.has(name)) {
                    formData.append(name, value);
                }
            });

            console.log('Total fields to submit as Job for Template', templateId, ':', fieldCount);

            // ✅ DEBUG: Log what's actually being sent
            console.log('Form data being sent to save_as_job:');
            for (let pair of formData.entries()) {
                console.log('  ', pair[0] + ': ' + pair[1]);
            }

            if (fieldCount === 0) {
                this.showMessage('No data to save as Job!', 'error');
                return;
            }

            $('#save-as-job-btn').prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> Saving as Job...');

            const cacheBuster = 't=' + new Date().getTime();
            const url = `<?= site_url("admin/templates/save_as_job/") ?>${templateId}?${cacheBuster}`;

            fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        this.showMessage(data.message || 'Job listing saved successfully!', 'success');
                        // Optionally close after success, or keep open
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
                    console.error('Save as Job error for Template', templateId, ':', error);
                    this.showMessage('Save as Job failed: ' + error.message, 'error');
                    $('#save-as-job-btn').prop('disabled', false).html(
                        '<i class="fa fa-briefcase"></i> Save as Job Listing');
                });
        }

        closeForm() {
            console.log('Closing quick manage form for Template:', this.templateId);
            $('#dynamic-form-content').empty(); // Clear old HTML
            this.destroy();

            if (typeof close_qm === 'function') {
                close_qm();
            } else {
                $('.quick-manage-overlay').remove();
                $('.quick-manage-container').empty();
                $('body').removeClass('qm-full-page');
            }
        }

        showMessage(message, type = 'info') {
            console.log(type.toUpperCase() + ' for Template', this.templateId, ':', message);
            if (typeof toastr !== 'undefined') {
                toastr[type === 'error' ? 'error' : 'success'](message);
            } else {
                alert(type.toUpperCase() + ': ' + message);
            }
        }
    };
}
// Initialize multi-select if select2 is available
if (typeof $.fn.select2 !== 'undefined') {
    $('select[multiple]').select2({
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || 'Select options';
        }
    });
}
// Initialize QuickManageForm when DOM is ready
$(document).ready(function() {
    console.log('DOM Ready - Initializing QuickManageForm');

    if (window.quickManageApp) {
        console.log('Cleaning up previous instance...');
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
        } else {
            console.error('QuickManageForm class not defined');
        }
    }, 100);
});

function closeQuickManageSimple() {
    if (window.quickManageApp) {
        window.quickManageApp.closeForm();
    } else {
        $('.quick-manage-overlay').remove();
    }
}

$(document).on('click', '.preview-composite-template', function() {
    const templateId = $(this).data('id');
    const templateName = $(this).data('name');
    window.open('<?= site_url("admin/templates/preview/") ?>' + templateId, '_blank');
});
</script>