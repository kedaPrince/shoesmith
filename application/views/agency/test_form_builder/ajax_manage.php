<?php
defined('BASEPATH') || exit('No direct script access allowed');  ?>
<style>
.form-display {
    padding: 20px;
    margin: 10px -10px;
    border-radius: 2px;
    border: dotted 1px #a5a5a58c;
}

.form-control:disabled {
    background-color: var(--card-color);
    cursor: not-allowed;
}

.accordion-header {
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    margin: 0;
}

.accordion-toggle {
    padding: 10px;
}

.accordion-icon {
    transition: transform 0.3s ease;
    margin-left: 0.5rem;
}

.accordion-header.active .accordion-icon {
    transform: rotate(180deg);
}

.accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, opacity 0.3s ease;
    opacity: 0;
}

.accordion-content.active {
    max-height: 2000px;
    opacity: 1;
    overflow: visible;
}

.accordion-content.accordion-open {
    max-height: 2000px;
    opacity: 1;
    overflow: visible;
}

.accordion-header.accordion-open .accordion-icon {
    transform: rotate(180deg);
}

.info-text.accordion-header {
    margin-bottom: 0;
}

.accordion-content.active {
    padding: 30px 40px 30px 30px;
    background: #8b8b8b15;
    border-radius: 0 0 5px 5px;
    margin: 0 1px;
}

.custom-section-header {
    border-left: 4px solid #007bff;
    padding-left: 15px;
}

.custom-section-header h2 {
    color: #007bff;
    margin-bottom: 10px;
}

.alert-info {
    background-color: #e8f4fd;
    border-color: #b6e0fe;
}

/* Field selection styles */
.field-selection-group {
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 10px;
    background: #f9f9f9;
}

.field-option-group {
    margin-bottom: 5px;
}

.field-option-group label {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
    display: block;
}
</style>

<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qmfc qm-tabs">

    <div class="quick-manage-heading">
        <?php if (!empty($row->id)): ?>
        <h2>Edit <?= $this->singular ?> <span><?= htmlspecialchars($row->name) ?></span></h2>
        <?php else: ?>
        <h2>Add <?= $this->singular ?></h2>
        <?php endif; ?>
    </div>

    <pre style="display:none">Row: <?php print_r($row); ?></pre>
    <ul class="qm-tabs-header">
        <li rel="1">Form Options</li>
        <li rel="2">Layout</li>
        <li rel="3">Styling</li>
        <li rel="4">Scripts</li>
        <li rel="5">Form Result</li>
        <?php if (empty($form)): ?>
        <li rel="6">From DB Schema</li>
        <?php endif; ?>
    </ul>

    <div class="form-field-container">
        <?= form_open('/save_test'); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>
        <?= form_hidden('name', !empty($row->name) ? $row->name : ''); ?>
        <!-- ✅ Critical: Preserve name -->

        <!-- Tab 1: Form Options -->
        <div class="qm-tabs-tab" rel="1">
            <?php if (empty($form)): ?>
            <div class="row">
                <div class="col-lg-6">
                    <?php
                    $safe_tables = !empty($tables) && is_array($tables) ? $tables : ['mod_layouts' => 'mod layouts'];
                    echo field_dropdown(
                        'tables|label_select_table',
                        $safe_tables,
                        set_value('tables'),
                        '',
                        []
                    );
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-6">
                    <?= field_input('name|label_form_name',!empty($row->name) ? $row->name : '','required'); ?>
                </div>
                <div class="col-lg-6">
                    <?= field_input(
                    'form_element_id|label_form_element_id',
                    !empty($row->form_element_id) ? $row->form_element_id : '',
                    'required'
                ); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?= field_input(
                    'form_classes|label_form_classes',
                    !empty($row->form_classes) ? $row->form_classes : ''
                ); ?>
                    <sup>Add classes separated by spaces</sup>
                </div>
                <div class="col-lg-6">
                    <?= field_input(
                    'form_action|label_form_custom_function',
                    !empty($row->form_action) ? $row->form_action : '',
                    '', 
                    ['data-parsley-required' => 'false']
                ); ?>
                    <sup>A function that will run before data is saved.</sup>
                </div>
            </div>

            <div class="btn-container" style="clear: left;">
                <?= save_button('Save and Close'); ?>
                <?= cancel_button('Close', 'left'); ?>
            </div>
        </div>

        <!-- Tab 2: Layout Builder -->
        <div class="qm-tabs-tab" rel="2">
            <div class="row">
                <div class="col-lg-12 dynamic-field-container" rel="form-rows">
                    <a class="btn btn-secondary add_dynamic_field_set left">
                        <i class="fa fa-plus-circle"></i> Add Row
                    </a>
                </div>
            </div>
            <div class="btn-container" style="clear: left;">
                <?= save_button('Save and Close'); ?>
                <?= cancel_button('Close', 'left'); ?>
            </div>
        </div>

        <!-- Tab 3: Styling -->
        <div class="qm-tabs-tab" rel="3">
            <div class="row">
                <div class="col-lg-12 dynamic-field-container" rel="form-styling">
                    <a class="btn btn-secondary add_dynamic_field_set left">
                        <i class="fa fa-plus-circle"></i> Add Style Rule
                    </a>
                </div>
            </div>
            <div class="btn-container" style="clear: left;">
                <?= save_button('Save and Close'); ?>
                <?= cancel_button('Close', 'left'); ?>
            </div>
        </div>

        <!-- Tab 4: Scripts -->
        <div class="qm-tabs-tab" rel="4">
            <div class="row">
                <div class="col-lg-12 dynamic-field-container" rel="form-scripts">
                    <a class="btn btn-secondary add_dynamic_field_set left">
                        <i class="fa fa-plus-circle"></i> Add Script
                    </a>
                </div>
            </div>
            <div class="btn-container" style="clear: left;">
                <?= save_button('Save and Close'); ?>
                <?= cancel_button('Close', 'left'); ?>
            </div>
        </div>

        <?= form_close(); ?>

        <!-- Tab 5: Form Result -->
        <div class="qm-tabs-tab" rel="5">
            <h4>Form Layout Result</h4>
            <div class="form-display">
                <?php if (!empty($form)): ?>
                <?= $form ?>
                <?php else: ?>
                <div class="error">No form schema found. Please generate one.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab 6: From DB Schema -->
        <div class="qm-tabs-tab" rel="6">
            <h4>From DB Schema</h4>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-display">
                        <div class="form-sample">
                            <?= !empty($from_db_schema) ? $from_db_schema : '<em>No preview available.</em>'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Dynamic Field Templates -->
<div class="dynamic-field-template" rel="form-rows">...</div>
<div class="df-sub-field-template" rel="0">...</div>
<div class="dynamic-field-template" rel="form-styling">...</div>
<div class="dynamic-field-template" rel="form-scripts">...</div>

<div class="dynamic-field-template" rel="form-rows">
    <div class="dynamic-field-row" rel="0">
        <input type="hidden" name="position" class="position-field" value="0" />
        <div class="row">
            <div class="col-lg-12 d-flex justify-content-end gap-4">
                <a class="btn btn-secondary dynamic_field_set_move_up" title="Move Up"><i
                        class="fa fa-arrow-up"></i></a>
                <a class="btn btn-secondary dynamic_field_set_move_down" title="Move Down"><i
                        class="fa fa-arrow-down"></i></a>
                <a class="btn btn-danger remove_dynamic_field_set" title="Remove Item"><i class="fa fa-times"></i></a>
            </div>
            <div class="col-lg-6">
                <?= field_input('row_header|label_form_row_header', '', '', ['placeholder' => 'Optional']); ?>
            </div>
            <div class="col-lg-6">
                <?= field_input('row_custom_class|label_form_row_custom_class', '', '', ['placeholder' => 'Optional']); ?>
            </div>
        </div>

        <div class="df-sub-field-container row">
            <div class="df-sub-field-holder col-12" rel="form-rows"></div>
            <div class="col-12 mt-4 d-flex justify-content-end">
                <a class="btn btn-info add_df_sub_field" title="Add Input" rel="form-rows"><i class="fa fa-plus"></i>
                    Add form field</a>
            </div>
        </div>
    </div>
</div>

<div class="df-sub-field-template" rel="0">
    <div class="accordion-holder">
        <div class="m-0 mt-2 w-100 accordion-header info-text">
            <div class="accordion-toggle">
                <i class="fa fa-chevron-down accordion-icon"></i>
            </div>
            <h4 class="field-name">New Field</h4>
            <div>
                <a class="btn df_sub_set_move_up" title="Move Up"><i class="fa fa-arrow-up"></i></a>
                <a class="btn df_sub_set_move_down" title="Move Down"><i class="fa fa-arrow-down"></i></a>
                <a class="btn df_sub_set_clone" title="Clone Item"><i class="fa fa-clone"></i></a>
                <a class="btn remove_df_sub_field_set" title="Remove Item"><i class="fa fa-times"></i></a>
            </div>
        </div>
        <div class="accordion-content">
            <div class="col-lg-12 d-flex justify-content-end">
                <a class="btn remove_df_sub_field_set" title="Remove Item"><i class="fa fa-times"></i></a>
            </div>
            <div class="col-lg-12 df-sub-form-fields-holder">
                <!-- UPDATED FIELD NAME SECTION WITH DATABASE FIELD SELECTION -->
                <div class="row">
                    <div class="col-6">
                        <div class="form-group" rel="field_name">
                            <label for="field_name">Field Name *</label>
                            <div class="input-group">
                                <select class="form-control field_name_select" name="field_name_select" required>
                                    <option value="">-- Select Database Field --</option>
                                    <?php if (!empty($field_suggestions)): ?>
                                    <?php foreach ($field_suggestions as $group => $fields): ?>
                                    <optgroup label="<?= htmlspecialchars($group) ?>">
                                        <?php foreach ($fields as $value => $label): ?>
                                        <option value="<?= htmlspecialchars($value) ?>">
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fa fa-database" title="Select from database fields"></i>
                                    </span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Or type custom field name manually</small>
                            <input type="text" class="form-control field_name_custom mt-2"
                                placeholder="Custom field name (if not using database field)" style="display: none;">
                            <!-- Hidden field that will actually store the value for form submission -->
                            <input type="hidden" class="field_name_actual" name="field_name" value="">
                        </div>
                    </div>
                    <div class="col-6">
                        <?= field_dropdown_noscript('field_type|label_field_type', $input_types, '', 'required field_input_type'); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <?= field_input('field_label|label_field_label', '', '', ['placeholder' => 'Optional']); ?>
                    </div>
                    <div class="col-lg-6">
                        <?= field_checkbox('field_required|label_field_required', 1); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4>Responsiveness</h4>
                        <sup>Set the column width for different screen sizes (1-12)</sup>
                    </div>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-4">
                                <?= field_input('field_col_sm|label_field_col_sm', '12', 'col-size', [], 'number'); ?>
                            </div>
                            <div class="col-4">
                                <?= field_input('field_col_md|label_field_col_md', '6', 'col-size', [], 'number'); ?>
                            </div>
                            <div class="col-4">
                                <?= field_input('field_col_lg|label_field_col_lg', '6', 'col-size', [], 'number'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="col-12">
                            <h4>Attributes</h4>
                            <sup>Set the attributes for this input. (Attributes should be an array with key=>value
                                pairs)</sup>
                        </div>
                        <div class="col-12">
                            <?= field_textarea('field_attr|nan', '', ''); ?>
                        </div>
                    </div>
                    <div class="col-6 options" style="display:none">
                        <div class="col-12">
                            <h4>Input Options</h4>
                            <sup>Set the options for this input. (Options should be an array with key=>value
                                pairs)</sup>
                        </div>
                        <div class="col-12">
                            <?= field_textarea('field_input_options|nan', '', ''); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="dynamic-field-template" rel="form-styling">
    <div class="dynamic-field-row" rel="0">
        <input type="hidden" name="position" class="position-field" value="0" />
        <div class="row">
            <div class="col-lg-12 d-flex justify-content-end">
                <a class="btn btn-secondary dynamic_field_set_move_up" title="Move Up"><i
                        class="fa fa-arrow-up"></i></a>
                <a class="btn btn-secondary dynamic_field_set_move_down" title="Move Down"><i
                        class="fa fa-arrow-down"></i></a>
                <a class="btn btn-danger remove_dynamic_field_set" title="Remove Item"><i class="fa fa-times"></i></a>
            </div>
            <div class="col-lg-6">
                <?= field_input('styling_element|label_styling_element', '', ''); ?>
            </div>
            <div class="col-lg-12">
                <?= field_textarea('styling_css|label_styling_css', '', ''); ?>
            </div>
        </div>
    </div>
</div>

<div class="dynamic-field-template" rel="form-scripts">
    <div class="dynamic-field-row" rel="0">
        <div class="row">
            <div class="col-lg-12 d-flex justify-content-end">
                <a class="btn btn-danger remove_dynamic_field_set" title="Remove Item"><i class="fa fa-times"></i></a>
            </div>
            <div class="col-lg-12">
                <?= field_textarea('scripts|label_form_scripts', '', ''); ?>
            </div>
        </div>
    </div>
</div>
<!-- Add this to your form builder view -->
<div class="form-group">
    <label for="is_public">Form Visibility</label>
    <select name="is_public" id="is_public" class="form-control">
        <option value="0">Private (Only my agency)</option>
        <option value="1">Public (All agencies can see)</option>
    </select>
    <small class="form-text text-muted">Private forms are only visible to your agency. Public forms can be seen by all agencies.</small>
</div>
<?php if (!empty($agency_id)): ?>
<div class="alert alert-info">
    <i class="fa fa-building"></i> This form will be saved for your agency only.
</div>
<?php endif; ?>
<script>
/** START Dynamic sub fields standard functions **/
function make_df_sub_row(container, con, rowID) {
    // Initialize counter
    if (container.attr('data-counter') === undefined) {
        container.attr('data-counter', 0);
    }
    let counter = parseInt(container.attr('data-counter')) + 1;
    container.attr('data-counter', counter);

    let df_parent_rel = con;
    let df_row_rel = rowID;

    // Clone template
    let new_row = $('.df-sub-field-template').clone();
    new_row.removeClass('df-sub-field-template')
        .addClass('df-sub-field-row')
        .attr('rel', df_parent_rel)
        .attr('pos', counter);

    // Update input names
    new_row.find('input, select, textarea').each(function() {
        let input = $(this);
        let name = input.attr('name');

        if (!name) return;

        // Generate correct name
        let new_name = `df[${df_parent_rel}][${df_row_rel}][df-sub][${counter}][${name}]`;
        input.attr('name', new_name);
    });

    // Append to container
    container.append(new_row);

    // Initialize the new field row
    setTimeout(() => {
        initializeFieldRow(new_row);
    }, 100);

    return new_row;
}

function validateFormBeforeSubmit() {
    const formId = $('input[name="id"]').val();
    if (!formId || formId === '0') {
        alert('Invalid form ID. Please refresh and try again.');
        return false;
    }

    const formRows = $('.dynamic-field-container[rel="form-rows"] .dynamic-field-row');
    let hasValidFields = false;

    formRows.each(function() {
        const row = $(this);
        const subFields = row.find('.df-sub-field-row');

        subFields.each(function() {
            const fieldName = $(this).find('.field_name_actual').val();
            if (fieldName && fieldName.trim() !== '') {
                hasValidFields = true;
                return false;
            }
        });

        if (hasValidFields) return false;
    });

    if (!hasValidFields) {
        alert('Please add at least one form field with a field name before saving.');
        return false;
    }

    return true;
}

// Attach to form submission
$('.quick-manage-form-container form').on('submit', function(e) {
    if (!validateFormBeforeSubmit()) {
        e.preventDefault();
        return false;
    }
});

$('.qmfc').on('click', '.remove_df_sub_field_set', function() {
    $(this).closest('.df-sub-field-row').remove();
    setTimeout(updateFormResultPreview, 100);
});

$('.qmfc').on('click', '.add_df_sub_field', function() {
    let parent = $(this).closest('.df-sub-field-container');
    let rel = $(this).attr('rel');
    let container = parent.find('.df-sub-field-holder[rel="' + rel + '"]');
    let rowID = parent.closest('.dynamic-field-row').attr('rel');

    make_df_sub_row(container, rel, rowID);
    setTimeout(updateFormResultPreview, 100);
});

$('.qmfc').on('click', '.df_sub_set_clone', function() {
    let parent = $(this).closest('.df-sub-field-container');
    let rel = $(this).closest('.df-sub-field-holder').attr('rel');
    let container = parent.find('.df-sub-field-holder[rel="' + rel + '"]');
    let rowID = parent.closest('.dynamic-field-row').attr('rel');
    let cloneRow = $(this).closest('.df-sub-field-row');

    let new_row = make_df_sub_row(container, rel, rowID);

    // Copy values from cloned row
    cloneRow.find('input, select, textarea').each(function() {
        let input = $(this);
        let name = input.attr('name').split('[').pop().replace(']', '');
        let value = input.val();

        if (input.attr('type') === 'checkbox') {
            let isChecked = input.is(':checked');
            new_row.find('input[name*="[' + name + ']"]').prop('checked', isChecked);
            if (isChecked) {
                new_row.find('input[name*="[' + name + ']"]').closest('.form-group').addClass(
                    'checked');
            }
        } else {
            new_row.find('input[name*="[' + name + ']"], select[name*="[' + name +
                ']"], textarea[name*="[' + name + ']"]').val(value);
        }
    });

    setTimeout(updateFormResultPreview, 100);
});
/** END Dynamic sub fields standard functions **/

/** DATABASE FIELD SELECTION SYSTEM **/
$(document).ready(function() {
    console.log('🔄 Initializing field selection system...');
    console.log('Field suggestions available:', <?= !empty($field_suggestions) ? 'true' : 'false' ?>);
    console.log('Field suggestions:', <?= json_encode($field_suggestions ?? []) ?>);

    // Initialize all existing field rows
    initializeFieldRows();
});

function initializeFieldRows() {
    $('.df-sub-field-row').each(function() {
        initializeFieldRow($(this));
    });
}

function initializeFieldRow(fieldRow) {
    const nameInput = fieldRow.find('.field_name_actual');
    const currentValue = nameInput.val();

    // Populate the dropdown
    populateFieldDropdown(fieldRow);

    if (currentValue) {
        if (currentValue.includes('.')) {
            // This is a database field
            fieldRow.find('.field_name_select').val(currentValue);
            fieldRow.find('.field_name_custom').hide();
            autoSelectFieldType(currentValue, fieldRow);
        } else {
            // This is a custom field
            fieldRow.find('.field_name_custom').val(currentValue).show();
            fieldRow.find('.field_name_select').val('');
        }
    }

    update_header(fieldRow.find('.field_name_actual'));
}

function populateFieldDropdown(fieldRow) {
    const select = fieldRow.find('.field_name_select');
    const fieldSuggestions = <?= json_encode($field_suggestions ?? []) ?>;

    // Store current selection
    const currentValue = select.val();

    select.empty().append('<option value="">-- Select Database Field --</option>');

    if (fieldSuggestions && Object.keys(fieldSuggestions).length > 0) {
        Object.entries(fieldSuggestions).forEach(([group, fields]) => {
            const optgroup = $('<optgroup>').attr('label', group);
            Object.entries(fields).forEach(([value, label]) => {
                $('<option>')
                    .attr('value', value)
                    .text(label)
                    .appendTo(optgroup);
            });
            optgroup.appendTo(select);
        });

        // Restore selection if it exists
        if (currentValue) {
            select.val(currentValue);
        }
    } else {
        console.warn('No field suggestions available');
        select.append('<option value="">No database fields available</option>');
    }
}

// Field selection event handlers
$('.qmfc').on('change', '.field_name_select', function() {
    const select = $(this);
    const fieldRow = select.closest('.df-sub-field-row');
    const customInput = fieldRow.find('.field_name_custom');
    const hiddenInput = fieldRow.find('.field_name_actual');
    const selectedValue = select.val();

    console.log('Field selected:', selectedValue);

    if (selectedValue) {
        // Database field selected
        customInput.hide().val('');
        hiddenInput.val(selectedValue);

        // Auto-fill field label
        const fieldName = selectedValue.split('.').pop();
        const fieldLabel = fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        fieldRow.find('input[name*="[field_label]"]').val(fieldLabel);

        // Auto-select field type
        autoSelectFieldType(selectedValue, fieldRow);

    } else {
        // No database field selected, show custom input
        customInput.show();
        hiddenInput.val('');
    }

    update_header(hiddenInput);
    setTimeout(updateFormResultPreview, 300);
});

$('.qmfc').on('input', '.field_name_custom', function() {
    const customInput = $(this);
    const fieldRow = customInput.closest('.df-sub-field-row');
    const select = fieldRow.find('.field_name_select');
    const hiddenInput = fieldRow.find('.field_name_actual');

    const customValue = customInput.val().trim();

    if (customValue) {
        select.val(''); // Clear database selection
        hiddenInput.val(customValue);

        // Auto-fill field label for custom fields too
        const fieldLabel = customValue.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        fieldRow.find('input[name*="[field_label]"]').val(fieldLabel);
    } else {
        hiddenInput.val('');
    }

    update_header(customInput);
    setTimeout(updateFormResultPreview, 300);
});

function autoSelectFieldType(fieldId, fieldRow) {
    const dbFields = <?= json_encode($db_fields ?? []) ?>;
    const typeMapping = <?= json_encode($field_type_mapping ?? []) ?>;

    console.log('Auto-selecting field type for:', fieldId);

    // Find the field in our database fields array
    const fieldInfo = dbFields.find(field => field.id === fieldId);

    if (fieldInfo) {
        const dbType = fieldInfo.type.toLowerCase();
        let suggestedType = 'text';

        console.log('Database field info:', fieldInfo);

        // Map database types to form input types
        for (const [dbPattern, formType] of Object.entries(typeMapping)) {
            if (dbType.includes(dbPattern)) {
                suggestedType = formType;
                break;
            }
        }

        // Special cases
        if (dbType.includes('text') && (dbType.includes('long') || dbType.includes('medium'))) {
            suggestedType = 'ckeditor';
        } else if (dbType.includes('text')) {
            suggestedType = 'textarea';
        } else if (dbType.includes('int') && dbType.includes('tiny')) {
            suggestedType = 'checkbox';
        } else if (dbType.includes('enum')) {
            suggestedType = 'dropdown';
        }

        // Set the field type dropdown
        const typeSelect = fieldRow.find('select[name*="[field_type]"]');
        typeSelect.val(suggestedType);

        // Trigger change to show/hide options if needed
        typeSelect.trigger('change');

        console.log(`Auto-selected field type: ${suggestedType} for DB type: ${dbType}`);
    } else {
        console.warn('Field info not found for:', fieldId);
    }
}

function update_header(el) {
    let val = $(el).val();

    // If it's a database field, extract just the field name
    if (val && val.includes('.')) {
        val = val.split('.').pop();
    }

    let parent = $(el).closest('.accordion-holder');
    let header = parent.find('.accordion-header .field-name');
    header.text(val || 'New Field');
}

/** EXISTING FUNCTIONS **/
function make_slug(str, unslugify = false) {
    if (!unslugify) {
        return str.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^-|-$/g, '');
    } else {
        return str.replace(/_/g, ' ').replace(/^\w/, c => c.toUpperCase());
    }
}

function clearDynamicFieldRows() {
    console.log('🧹 Clearing existing dynamic field rows...');
    const container = $('.dynamic-field-container[rel="form-rows"]');
    container.empty();
    container.html(
        '<a class="btn btn-secondary add_dynamic_field_set left"><i class="fa fa-plus-circle"></i> Add Row</a>');
    console.log('✅ Container cleared');
}

function generate_from_table(table) {
    let url = '<?= site_url("agency/test_form_builder/generate_from_db_table"); ?>';
    $.ajax({
        url: url,
        type: 'POST',
        data: {
            table: table
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                console.log('✅ Table fields loaded:', response);

                // Show preview
                $('.qm-tabs-tab[rel="5"] .form-display').html(response.form_view);

                // Auto-fill name & ID
                let id = response.form_schema?.form?.id || table;
                let nameVal = id.replace(/_/g, ' ');
                nameVal = nameVal.charAt(0).toUpperCase() + nameVal.slice(1);
                $("input[name='name']").val(nameVal);
                $("input[name='form_element_id']").val(id);

                // Clear existing layout fields first
                clearDynamicFieldRows();

                // Wait for cleanup, then populate with new fields
                setTimeout(() => {
                    if (response.form_schema && response.form_schema[0] && response.form_schema[0]
                        .fields) {
                        console.log('📋 Starting field population with:', Object.keys(response
                            .form_schema[0].fields));
                        populateTableFields(response.form_schema[0].fields);
                    } else {
                        console.log('❌ No fields found in response');
                    }
                }, 300);

            } else {
                console.error('❌ API Error:', response.message);
            }
        },
        error: function(xhr) {
            console.error('🚨 AJAX Error:', xhr);
        }
    });
}

// Function for loading fields from database tables
function populateTableFields(fields) {
    console.log('🚀 Starting TABLE field population with:', fields);
    const container = $('.dynamic-field-container[rel="form-rows"]');

    // Add a row
    container.find('.add_dynamic_field_set').trigger('click');

    setTimeout(() => {
        const row = container.find('.dynamic-field-row').first();
        const addFieldBtn = row.find('.add_df_sub_field');
        const holder = row.find('.df-sub-field-holder[rel="form-rows"]');

        let fieldIndex = 0;
        const fieldEntries = Object.entries(fields);

        function addNextField() {
            if (fieldIndex >= fieldEntries.length) {
                console.log('✅ All TABLE fields added!');
                setTimeout(updateFormResultPreview, 500);
                return;
            }

            const [fieldName, config] = fieldEntries[fieldIndex];
            console.log(`➕ Adding TABLE field ${fieldIndex + 1}: ${fieldName}`);

            addFieldBtn.trigger('click');

            setTimeout(() => {
                const fieldRow = holder.find('.df-sub-field-row').last();

                // Set field values using the new field selection system
                const table = $('select[name="tables"]').val();
                const fullFieldName = table + '.' + fieldName;
                fieldRow.find('.field_name_select').val(fullFieldName).trigger('change');

                // Set other field properties
                fieldRow.find('input[name*="[field_label]"]').val(config.label || fieldName);

                if (config.required) {
                    fieldRow.find('input[name*="[field_required]"]').prop('checked', true).closest(
                        '.form-group').addClass('checked');
                }

                fieldRow.find('input[name*="[field_col_sm]"]').val(config.col?.sm || '12');
                fieldRow.find('input[name*="[field_col_md]"]').val(config.col?.md || '6');
                fieldRow.find('input[name*="[field_col_lg]"]').val(config.col?.lg || '6');

                // Handle attributes
                if (config.attr && Object.keys(config.attr).length > 0) {
                    fieldRow.find('textarea[name*="[field_attr]"]').val(JSON.stringify(config.attr,
                        null, 2));
                }

                // Handle options for specific field types
                if (['dropdown', 'multiselect', 'radio', 'checkbox'].includes(config.type)) {
                    fieldRow.find('.options').show();
                    if (config.options && Object.keys(config.options).length > 0) {
                        fieldRow.find('textarea[name*="[field_input_options]"]').val(JSON.stringify(
                            config.options, null, 2));
                    }
                }

                // Update header
                update_header(fieldRow.find('.field_name_actual'));

                console.log(`✅ TABLE Field "${fieldName}" added successfully`);

                fieldIndex++;
                setTimeout(addNextField, 200);
            }, 200);
        }

        addNextField();
    }, 500);
}

// Function for populating existing form fields when EDITING
var current_fields = <?= json_encode(isset($current_fields) ? $current_fields : []) ?>;
var has_populated = false;

function populateCurrentFields() {
    if (has_populated) {
        console.log('Already populated fields, skipping');
        return;
    }

    if (current_fields.length === 0) {
        console.log('No current fields to pre-populate');
        return;
    }

    console.log('🔄 EDIT MODE: Pre-populating ' + current_fields.length + ' existing fields');
    has_populated = true;

    const container = $('.dynamic-field-container[rel="form-rows"]');
    const holder = container.find('.df-sub-field-holder[rel="form-rows"]');

    // Clear existing fields
    holder.html('');
    holder.attr('data-counter', 0);

    // Add one row if we have fields
    const addBtn = container.find('.add_dynamic_field_set');
    addBtn.trigger('click');

    // Wait for row to be created, then populate fields
    setTimeout(() => {
        const row = container.find('.dynamic-field-row').first();
        const subHolder = row.find('.df-sub-field-holder[rel="form-rows"]');

        current_fields.forEach((field, index) => {
            setTimeout(() => {
                // Add sub-field
                const subAddBtn = row.find('.add_df_sub_field');
                subAddBtn.trigger('click');

                // Wait for sub-field to be created, then populate
                setTimeout(() => {
                    const subRow = subHolder.find('.df-sub-field-row').last();

                    // Populate field data using the new field selection system
                    if (field.field_name && field.field_name.includes('.')) {
                        // Database field
                        subRow.find('.field_name_select').val(field.field_name).trigger(
                            'change');
                    } else {
                        // Custom field
                        subRow.find('.field_name_custom').val(field.field_name).show();
                        subRow.find('.field_name_select').val('');
                    }

                    // Set other field properties
                    subRow.find('select[name*="[field_type]"]').val(field.field_type);
                    subRow.find('input[name*="[field_label]"]').val(field.field_label);

                    if (field.field_required === '1' || field.field_required === 1) {
                        subRow.find('input[name*="[field_required]"]').prop('checked',
                                true)
                            .closest('.form-group').addClass('checked');
                    }

                    subRow.find('input[name*="[field_col_sm]"]').val(field
                        .field_col_sm);
                    subRow.find('input[name*="[field_col_md]"]').val(field
                        .field_col_md);
                    subRow.find('input[name*="[field_col_lg]"]').val(field
                        .field_col_lg);
                    subRow.find('textarea[name*="[field_attr]"]').val(field.field_attr);
                    subRow.find('textarea[name*="[field_input_options]"]').val(field
                        .field_input_options);

                    // Update header
                    update_header(subRow.find('.field_name_actual'));

                    // Show options if needed
                    if (['dropdown', 'multiselect', 'radio', 'checkbox'].includes(field
                            .field_type)) {
                        subRow.find('.options').show();
                    }

                    console.log('✅ EDIT Field: ' + field.field_name);

                    // Update preview after last field
                    if (index === current_fields.length - 1) {
                        setTimeout(updateFormResultPreview, 300);
                    }
                }, 100);
            }, 200 * index);
        });
    }, 300);
}

// Table change event
$('.qmfc').on('change', 'select[name="tables"]', function() {
    let table = $(this).val();
    if (!table) return;

    console.log('🗂️ TABLE SELECTED: Loading fields from', table);

    let nameLabel = table.replace(/_/g, ' ');
    nameLabel = nameLabel.charAt(0).toUpperCase() + nameLabel.slice(1);
    $('input[name="name"]').val(nameLabel);
    $('input[name="form_element_id"]').val(table);

    // Generate preview and populate TABLE fields
    generate_from_table(table);
});

$('input[name="name"]').on('blur', function() {
    if ($('input[name="form_element_id"]').val() === '') {
        $('input[name="form_element_id"]').val(make_slug($(this).val()));
    }
});

$('.qmfc').on('change', '.field_input_type', function() {
    let field_type = $(this).val();
    let parent = $(this).closest('.df-sub-field-container');

    let let_option_fields = ['dropdown', 'multiselect', 'radio', 'checkbox'];
    if (let_option_fields.includes(field_type)) {
        parent.find('.options').show();
    } else {
        parent.find('.options').hide();
    }
    setTimeout(updateFormResultPreview, 300);
});

$('.qmfc').on('click', '.accordion-toggle', function() {
    let holder = $(this).closest('.accordion-holder');
    const content = holder.find('.accordion-content');
    const header = $(this);

    content.removeClass('accordion-open');
    header.removeClass('accordion-open');

    content.toggleClass('active');
    header.toggleClass('active');
});

function updateFormResultPreview() {
    console.log('🔄 Rebuilding Tab 5 from layout UI...');

    const schema = {
        form: {
            id: $('input[name="form_element_id"]').val() || 'form_' + Date.now(),
            class: $('input[name="form_classes"]').val() || '',
            action: $('input[name="form_action"]').val() || ''
        }
    };

    let rowIndex = 0;
    $('.dynamic-field-container[rel="form-rows"] .dynamic-field-row').each(function() {
        const row = $(this);
        const header = row.find('input[name*="[row_header]"]').val() || '';
        const customClass = row.find('input[name*="[row_custom_class]"]').val() || '';
        const fields = {};

        // Collect sub-fields
        row.find('.df-sub-field-row').each(function() {
            const fieldRow = $(this);

            const name = fieldRow.find('.field_name_actual').val();
            if (!name) return;

            const type = fieldRow.find('select[name*="[field_type]"]').val() || 'text';
            const label = fieldRow.find('input[name*="[field_label]"]').val() || name;
            const required = !!fieldRow.find('input[name*="[field_required]"]:checked').val();
            const colSM = fieldRow.find('input[name*="[field_col_sm]"]').val() || '12';
            const colMD = fieldRow.find('input[name*="[field_col_md]"]').val() || '6';
            const colLG = fieldRow.find('input[name*="[field_col_lg]"]').val() || '6';
            const attrStr = fieldRow.find('textarea[name*="[field_attr]"]').val() || '';
            const optionsStr = fieldRow.find('textarea[name*="[field_input_options]"]').val() || '';

            fields[name] = {
                type,
                label,
                required,
                col: {
                    sm: colSM,
                    md: colMD,
                    lg: colLG
                },
                attr: parseOptionsString(attrStr),
                options: parseOptionsString(optionsStr)
            };
        });

        schema[rowIndex] = {
            row: true,
            header,
            custom_class: customClass,
            fields
        };
        rowIndex++;
    });

    // Send to server to render HTML
    $.ajax({
        url: '<?= site_url("agency/test_form_builder/render_schema"); ?>',
        type: 'POST',
        data: {
            schema: JSON.stringify(schema)
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.form_view) {
                $('.qm-tabs-tab[rel="5"] .form-display').html(response.form_view);
                console.log('✅ Tab 5 updated with latest layout');
            } else {
                console.error('❌ Render failed:', response?.message);
            }
        },
        error: function(xhr) {
            console.error('🚨 AJAX Error:', xhr);
        }
    });
}

function parseOptionsString(str) {
    if (!str) return {};
    try {
        return JSON.parse(str);
    } catch (e) {
        const obj = {};
        str.trim().split('\n').forEach(line => {
            if (line.includes('=')) {
                const [k, v] = line.split('=', 2);
                obj[k.trim()] = v.trim();
            }
        });
        return obj;
    }
}

// Update form preview when fields change
$(document).on('change', '.field_name_actual, select[name*="[field_type]"], input[name*="[field_required]"]',
    function() {
        setTimeout(updateFormResultPreview, 500);
    });

$(document).ready(function() {
    console.log('Document ready - current fields:', current_fields);

    // Clear any automatic field creation
    $('.dynamic-field-container[rel="form-rows"] .df-sub-field-holder[rel="form-rows"]').each(function() {
        $(this).html('');
        $(this).attr('data-counter', 0);
    });

    // If we have current_fields (editing existing form), populate them
    if (current_fields.length > 0) {
        console.log('🔄 EDIT MODE DETECTED: Populating existing form fields');
        $('.dynamic-field-container[rel="form-rows"]').each(function() {
            if ($(this).find('.dynamic-field-row').length === 0) {
                $(this).append($('.dynamic-field-template[rel="form-rows"]').clone().children());
            }
        });

        // Pre-populate from current_fields for EDITING
        setTimeout(populateCurrentFields, 500);
    } else {
        console.log('➕ CREATE MODE: No existing fields to populate');
        // Don't auto-populate - user will either add fields manually or select a table
    }
});

// Add to your form builder JavaScript
function autoPopulateMedicalField(fieldName) {
    const medicalRecommendations = <?= json_encode($medical_recommendations ?? []) ?>;

    if (medicalRecommendations[fieldName]) {
        const rec = medicalRecommendations[fieldName];

        // Auto-populate field type
        $('select[name*="field_type"]').last().val(rec.type);

        // Auto-populate label if empty
        const $labelField = $('input[name*="field_label"]').last();
        if (!$labelField.val()) {
            $labelField.val(rec.label);
        }

        // Auto-populate placeholder in attributes
        const $attrField = $('textarea[name*="field_attr"]').last();
        let currentAttrs = {};
        try {
            currentAttrs = JSON.parse($attrField.val() || '{}');
        } catch (e) {
            currentAttrs = {};
        }

        if (rec.placeholder && !currentAttrs.placeholder) {
            currentAttrs.placeholder = rec.placeholder;
            $attrField.val(JSON.stringify(currentAttrs, null, 2));
        }

        // Mark as required if recommended
        if (rec.required) {
            $('select[name*="field_required"]').last().val('1');
        }

        toastr.success('Medical field auto-populated: ' + rec.label);
    }
}

// Call this when field name changes
$(document).on('change', 'input[name*="field_name"]', function() {
    const fieldName = $(this).val();
    if (fieldName.includes('usr_medical_emergency_details')) {
        autoPopulateMedicalField(fieldName);
    }
});
</script>