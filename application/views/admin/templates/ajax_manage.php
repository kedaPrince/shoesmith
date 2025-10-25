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
        var url = 'http://localhost/shoesmith/admin/templates/ajax_quick_manage/' + templateId + '?' +
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
            this.init();
        }

        init() {
            console.log('Raw currentTemplateData from embed:', JSON.stringify(this.currentTemplateData, null,
                2)); // CONFIRM EMBEDDED JSON

            console.log('=== QUICK MANAGE FORM LOADED FOR TEMPLATE:', this.templateId, '===');
            console.log('Template data count:', Object.keys(this.currentTemplateData).length);

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

        setFormValues() {
            console.log('=== SETTING FORM VALUES for Template:', this.templateId, '===');
            console.log('Data to set:', JSON.stringify(this.currentTemplateData, null, 2)); // FULL DUMP

            if (!this.currentTemplateData || Object.keys(this.currentTemplateData).length === 0) {
                console.log('No form data to set');
                return;
            }

            Object.keys(this.currentTemplateData).forEach(fieldName => {
                const fieldValue = this.currentTemplateData[fieldName];
                console.log('  Pre-set check for ' + fieldName + ': current DOM val = "' + $(
                    `[name="${fieldName}"]`).val().substring(0, 30) + '"'); // BEFORE SET
                this.setFieldValue(fieldName, fieldValue);
                console.log('  Post-set check for ' + fieldName + ': new DOM val = "' + $(
                    `[name="${fieldName}"]`).val().substring(0, 30) + '"'); // AFTER SET
            });

            console.log('Form values set for Template:', this.templateId);
        }

        setFieldValue(fieldName, fieldValue) {
            let $field = $(`[name="${fieldName}"]`);

            if ($field.length === 0) {
                $field = $(`#${fieldName}`);
            }

            if ($field.length === 0) {
                $field = $(`#ckeditor_${fieldName}`);
            }

            console.log('  Targeting field for ' + fieldName + ': found ' + $field.length + ' elements');

            if ($field.length > 0) {
                let currentVal = $field.val();
                if (currentVal && currentVal.trim() !== '' && currentVal !== fieldValue) {
                    console.warn('Skipping set for ' + fieldName + ': DOM has "' + currentVal.substring(0, 30) +
                        '", JS has "' + fieldValue.substring(0, 30) + '" – trusting pre-pop');
                    return; // Skip overwrite
                }

                const isCKEditor = $field.is('textarea') && $field.attr('id').includes('ckeditor');

                if (isCKEditor && this.ckeditorReady) {
                    console.log('  Setting CKEditor for ' + fieldName + ' = ' + fieldValue.substring(0, 30));
                    this.setCKEditorValue($field.attr('id'), fieldValue);
                } else if (isCKEditor && !this.ckeditorReady) {
                    console.log('  Setting Fallback for ' + fieldName + ' = ' + fieldValue.substring(0, 30));
                    this.setFallbackEditorValue($field.attr('id'), fieldValue);
                } else {
                    console.log('  Setting plain val for ' + fieldName + ' = ' + fieldValue.substring(0, 30));
                    $field.val(fieldValue).trigger('change');
                }
            } else {
                console.warn('Field not found:', fieldName);
                // FALLBACK SEARCH: Log all names
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

        debugFormState(stage) {
            console.log(`=== FORM STATE ${stage} for Template ${this.templateId} ===`);

            const elements = $(
                '#dynamic-form-content input, #dynamic-form-content select, #dynamic-form-content textarea');
            console.log(`Total form elements: ${elements.length}`);

            elements.each((index, element) => {
                const $el = $(element);
                if ($el.attr('name')) {
                    console.log(`Field ${index + 1}:`, $el.attr('name'), '=', $el.val().substring(0,
                        50));
                }
            });

            console.log('CKEditor instances:', this.editors.length);
            console.log('Fallback editors:', Array.from(this.fallbackEditors.keys()));
            console.log('=== END FORM STATE ===');
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
                .then(response => response.text())
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON response from server');
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
                setTimeout(() => {
                    this.closeForm();
                }, 1500);
            } else {
                this.showMessage(data.error || 'Failed to save template data', 'error');
                $('#save-button').prop('disabled', false).html('<i class="fa fa-save"></i> Save Template Data');
            }
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