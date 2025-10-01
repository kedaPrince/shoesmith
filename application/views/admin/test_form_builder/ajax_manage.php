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
            <h4 class="field-name"></h4>
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
                <div class="row">
                    <div class="col-6">
                        <?= field_input('field_name|label_field_name', '', 'required'); ?>
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

<script>
/** START Dynamic sub fields standard functions **/
//Add and populate sub dynamic
function dynamic_fields_add_extra(con, rowID) {
    let parent = $('.dynamic-field-container[rel="' + con + '"]');
    let sub_row = parent.find('.dynamic-field-row[rel="' + rowID + '"]');
    let container = sub_row.find('.df-sub-field-holder[rel="' + con + '"]');

    if (typeof df[con] !== 'undefined' && typeof df[con][rowID] !== 'undefined' && typeof df[con][rowID]['df-sub'] !==
        'undefined') {
        let counter = Object.keys(df[con][rowID]['df-sub']).length;
        for (counter; counter > 0; counter--) {
            make_df_sub_row(container, con, rowID);
        }
    }

}

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

    // Optional pre-hook
    if (typeof before_df_sub_add_function === "function") {
        before_df_sub_add_function(con, rowID, container, new_row);
    }

    // Update input names and restore values
    new_row.find('input, select, textarea').each(function() {
        let input = $(this);
        let name = input.attr('name');

        if (!name) return;

        // Generate correct name
        let new_name = `df[${df_parent_rel}][${df_row_rel}][df-sub][${counter}][${name}]`;
        input.attr('name', new_name);

        // ✅ Delay restoration to ensure element is in DOM
        setTimeout(() => {
            let val = null;

            // Try to get value from global df object
            if (window.df && window.df[con] && window.df[con][rowID] && window.df[con][rowID][
                    'df-sub'
                ]) {
                const subData = window.df[con][rowID]['df-sub'];

                // Match by position (counter)
                for (let key in subData) {
                    if (subData.hasOwnProperty(key)) {
                        const numKey = isNaN(key) ? parseInt(key.replace('new_', '')) : parseInt(key);
                        if (numKey === counter || key == counter || key == String(counter)) {
                            val = subData[key][name];
                            break;
                        }
                    }
                }
            }

            if (val !== null && val !== undefined) {
                if (input.attr('type') === 'checkbox') {
                    if (val == "1" || val === true || val == "true") {
                        input.prop('checked', true).attr('checked', 'checked');
                        input.closest('.form-group').addClass('checked');
                    } else {
                        input.prop('checked', false).attr('checked', '');
                        input.closest('.form-group').removeClass('checked');
                    }
                } else if (input.attr('type') === 'radio') {
                    $(`input[name="${input.attr('name')}"][value="${val}"]`).prop('checked', true);
                } else {
                    input.val(val);
                    // Update accordion header
                    if (name === 'field_name') {
                        update_header(input);
                    }
                }
            }
        }, 50); // Small delay ensures stability
    });

    // Append to container
    container.append(new_row);

    // Optional post-hook
    if (typeof after_df_sub_add_function === "function") {
        after_df_sub_add_function(con, rowID, container, new_row);
    }

    return new_row;
}

// function update_header(el) {
//     let val = $(el).val();
//     let parent = $(el).closest('.accordion-holder')
//     let header = parent.find('.accordion-header .field-name');
//     // Set the field name in the header
//     header.text(val);
// }

function show_form_schema(form_id, target, callback = null) {
    if (!form_id) {
        target.html('<div class="error">Please select a form schema.</div>');
        return;
    }

    target.html('<div class="loading">Loading form {' + form_id + '}...</div>');
    let url = '<?= site_url('admin/test_form_builder/get_form'); ?>';

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            id: form_id
        },
        dataType: 'html',
        success: function(response) {
            //decode the response if it's JSON
            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    target.html('<div class="error">Error parsing response.</div>');
                    return;
                }
            }
            target.html(response.form.form_view);

            if (callback && typeof callback === 'function') {
                callback(response);
                // return response.form.form_schema.form.id;
            }
        },
        error: function() {
            target.html('<div class="error">Error loading form.</div>');
        }
    });
}

function df_sub_fields_clone(container, rel, rowID, cloneRow) {
    var new_row = make_df_sub_row(container, rel, rowID);
    var new_row_pos = new_row.attr('pos');
    var clone_row_pos = cloneRow.attr('pos');

    //update new_row with cloneRow data, except field name
    new_row.find('input, select, textarea').each(function() {
        var label_for = $(this).closest('.form-group').find('label').attr('for');
        var name = 'df[' + rel + '][' + rowID + '][df-sub][' + new_row_pos + '][' + label_for + ']';
        var clone_name = 'df[' + rel + '][' + rowID + '][df-sub][' + clone_row_pos + '][' + label_for + ']';
        var cloneValue = cloneRow.find('[name="' + clone_name + '"]').val();

        var append = '';
        if (label_for == 'field_name') {
            append = '_' + new_row_pos;

            new_row.find('.field-name').text(cloneValue + append);
        }

        $(this).val(cloneValue + append);
    });

    console.log(new_row);
    console.log(container);
    console.log(rel);
    console.log(rowID);

}

function df_sub_fields_move(con, row, dir) {
    var dfRow = $(row);
    var dfCount = $(con).find('.df-sub-field-row').length;
    var dfPos = $(row).attr('pos');


    if (dir == 'up') {
        // Check if first sub row in container
        if (dfPos > 1) {
            dfRow.prev('.df-sub-field-row').before(dfRow);
        } else {
            // Check if there is a previous row
            var parent_row = con.closest('.dynamic-field-row');
            var prev_row = parent_row.prev('.dynamic-field-row');

            // If previous row found, move the row to the previous row
            if (prev_row.length) {
                // move row to the previous row
                var new_con = prev_row.find('.df-sub-field-holder');
                new_con.append(dfRow);
                // reset the position of the moved row
                let prev_rel = $(con).closest('.dynamic-field-row').attr('rel');
                df_object_move_row(new_con, dfPos, prev_rel);
                reset_df_sub_positions(new_con);
            }


        }
    } else if (dir == 'down') {
        // Check if last sub row in container
        if (dfPos < dfCount) {
            dfRow.next('.df-sub-field-row').after(dfRow);
        } else {
            //check if there is a next row
            var parent_row = con.closest('.dynamic-field-row');
            var next_row = parent_row.next('.dynamic-field-row');

            if (!next_row.length || next_row.length == 0) {
                // if no next row found, add a new one
                var parent_con = $(con).closest('.dynamic-field-container');
                parent_con.find('.add_dynamic_field_set').trigger('click');
                next_row = parent_row.next('.dynamic-field-row');
            }

            // move row to the next row
            var new_con = next_row.find('.df-sub-field-holder');
            new_con.prepend(dfRow);
            // reset the position of the moved row
            let prev_rel = $(con).closest('.dynamic-field-row').attr('rel');
            df_object_move_row(new_con, dfPos, prev_rel);
            reset_df_sub_positions(new_con);
        }
    }

    reset_df_sub_positions(con);

    if (typeof dynamic_fields_move_extra === "function") {
        dynamic_fields_move_up_extra(con, row, dir);
    }

}

function df_object_move_row(con, prev_pos, prev_row) {
    // just check if df is empty
    if (typeof df === 'undefined' || df === null || Object.keys(df).length === 0) {
        // if empty skip this step
        return;
    }

    let parent = $(con).closest('.dynamic-field-container');
    let parent_rel = parent.attr('rel');
    let rowID = $(con).closest('.dynamic-field-row').attr('rel');

    //check if df[parent_rel][rowID] exists, if not create it
    if (typeof df[parent_rel] === 'undefined' || typeof df[parent_rel][rowID] === 'undefined') {
        //remove 'new_' from rowID if it exists
        var pos = rowID.indexOf('new_');
        if (pos !== -1) {
            pos = rowID.substring(pos + 4);
        }
        df[parent_rel][rowID] = {
            'df-sub': {
                [1]: df[parent_rel][prev_row]['df-sub'][prev_pos] || {}
            },
            'row_header': '',
            'position': pos
        };
    } else {
        var df_row = df[parent_rel][rowID]['df-sub'];
    }
}

function reset_df_sub_positions(con) {
    let parent = $(con).closest('.dynamic-field-container');
    let parent_rel = parent.attr('rel');
    let rowID = $(con).closest('.dynamic-field-row').attr('rel');

    // just check if df is empty
    if (typeof df === 'undefined' || df === null || Object.keys(df).length === 0) {
        var df_row = {};
    } else {
        var df_row = df[parent_rel][rowID]['df-sub'];
    }

    // Create a new array to store the reordered df_row
    var new_df_row = {};

    // Iterate through the DOM elements to get the new order
    $(con).find('.df-sub-field-row').each(function(index) {
        let new_pos = index + 1; // Positions start at 1
        let prev_pos = parseInt($(this).attr('pos'), 10);

        // Update the pos attribute in the DOM
        $(this).attr('pos', new_pos);
        con.attr('data-counter', new_pos);

        $field_name = 'df[' + parent_rel + '][' + rowID + '][df-sub][' + new_pos + ']';
        let new_name = '';

        // Update the input names
        $(this).find('input, select, textarea').each(function() {

            let name = $(this).closest('.form-group').find('label').attr('for');
            if (name) {
                new_name = $field_name + '[' + name + ']';
                $(this).attr('name', new_name);
            }

        });

        // Map the old position's data to the new position
        new_df_row[new_pos] = df_row[prev_pos];
    });

    if (df_row !== undefined && Object.keys(df_row).length > 0) {
        // Replace the old df_row with the new ordered one
        df[parent_rel][rowID]['df-sub'] = new_df_row;
    }
}

$('.qmfc').on('click', '.remove_df_sub_field_set', function() {
    let container = $(this).closest('.dynamic-field-container');
    let rel = container.attr('rel');

    //find the closest df-sub-field-row and remove it
    let parent = $(this).closest('.df-sub-field-row');
    parent.remove();

    //update the input names for the remaining sub fields
    let rows = container.find('.dynamic-field-row');
    rows.each(function() {
        let counter = 1;
        let row = $(this).attr('rel');
        let sub_rows = $(this).find('.df-sub-form-fields-holder');
        sub_rows.each(function() {
            let groups = $(this).find('.form-group');
            groups.each(function() {
                let input_name = $(this).find('label').attr('for');
                let name = "df[" + rel + "][" + row + "][df-sub][" + counter + "][" +
                    input_name + "]";
                let input = $(this).find('input, select, textarea');
                input.attr('name', name);
            });
            counter++;
        });
    });
});

$('.qmfc').on('click', '.add_df_sub_field', function() {
    let parent = $(this).closest('.df-sub-field-container');
    let rel = $(this).attr('rel');
    let new_row = $('.df-sub-field-template').clone();
    new_row.removeClass('df-sub-field-template').addClass('df-sub-field-row').attr('rel', rel)
    let container = parent.find('.df-sub-field-holder[rel="' + rel + '"]');
    let rowID = parent.closest('.dynamic-field-row').attr('rel');

    make_df_sub_row(container, rel, rowID);
});

$('.qmfc').on('click', '.df_sub_set_move_up', function() {
    var con = $(this).closest('.df-sub-field-container');
    var row = $(this).closest('.df-sub-field-row');
    df_sub_fields_move(con, row, 'up');
});

$('.qmfc').on('click', '.df_sub_set_clone', function() {
    let parent = $(this).closest('.df-sub-field-container');
    let rel = $(this).closest('.df-sub-field-holder').attr('rel');
    let new_row = $('.df-sub-field-template').clone();
    new_row.removeClass('df-sub-field-template').addClass('df-sub-field-row').attr('rel', rel)
    let container = parent.find('.df-sub-field-holder[rel="' + rel + '"]');
    let rowID = parent.closest('.dynamic-field-row').attr('rel');
    let cloneRow = $(this).closest('.df-sub-field-row');

    df_sub_fields_clone(container, rel, rowID, cloneRow);
});

$('.qmfc').on('click', '.df_sub_set_move_down', function() {
    var con = $(this).closest('.df-sub-field-container');
    var row = $(this).closest('.df-sub-field-row');
    df_sub_fields_move(con, row, 'down');
});
/** END Dynamic sub fields standard functions **/


// Use hook function to update the header of collapsible rows after a new sub field is added
function after_df_sub_add_function(con, rowID, container, new_row, input) {

    let field_group = new_row.find('.form-group');
    if (field_group.attr('rel') == 'field_name') {
        let field = field_group.find('input, select, textarea');
        update_header(field);
    }

}

function make_slug(str, unslugify = false) {
    if (!unslugify) {
        return str.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^-|-$/g, '');
    } else {
        return str.replace(/_/g, ' ').replace(/^\w/, c => c.toUpperCase());
    }
}

function generate_from_table(table) {
    let url = '<?= site_url("admin/test_form_builder/generate_from_db_table"); ?>';
    $.ajax({
        url: url,
        type: 'POST',
        data: {
            table: table
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Show preview
                $('.qm-tabs-tab[rel="5"] .form-display').html(response.form_view);

                // Auto-fill name & ID
                let id = response.form_schema?.form?.id || table;
                let nameVal = id.replace(/_/g, ' ');
                nameVal = nameVal.charAt(0).toUpperCase() + nameVal.slice(1);
                $("input[name='name']").val(nameVal);
                $("input[name='form_element_id']").val(id);

                // ✅ ADD THE CONSOLE.LOG HERE
                console.log('Fields to populate:', response.form_schema[0].fields);

                // Populate layout only if fields exist
                if (response.form_schema && response.form_schema[0] && response.form_schema[0].fields) {
                    populate_form_layout(response.form_schema[0].fields);
                }

            } else {
                $('.qm-tabs-tab[rel="5"] .form-display').html('<div class="error">Failed: ' + (response
                    .message || 'Unknown error') + '</div>');
            }
        },
        error: function(xhr) {
            $('.qm-tabs-tab[rel="5"] .form-display').html(
                '<div class="error">AJAX Error. Open console.</div>');
            console.error('generate_from_table error:', xhr);
        }
    });
}

function populate_form_layout(fields) {
    console.log('Starting populate_form_layout with:', fields);

    const container = $('.dynamic-field-container[rel="form-rows"]');
    const addBtn = container.find('.add_df_sub_field');
    const holder = container.find('.df-sub-field-holder[rel="form-rows"]');

    // Reset holder
    holder.html('');
    holder.attr('data-counter', 0);

    let currentIndex = 0;
    const fieldEntries = Object.entries(fields);

    function addNextField() {
        if (currentIndex >= fieldEntries.length) {
            // ✅ ALL FIELDS DONE — NOW UPDATE TAB 5
            setTimeout(updateFormResultPreview, 600);
            return;
        }

        const [fieldName, config] = fieldEntries[currentIndex];
        console.log(`👉 Adding field #${currentIndex + 1}:`, fieldName);

        addBtn.trigger('click');

        // Wait for new row to render
        setTimeout(() => {
            const newRow = holder.find('.df-sub-field-row').last();
            const nameInput = newRow.find('input[name*="[field_name]"]');

            // Poll until input exists and is visible
            const waitForInput = setInterval(() => {
                if (nameInput.length && nameInput.is(':visible')) {
                    clearInterval(waitForInput);

                    // Set values
                    nameInput.val(fieldName);
                    update_header(nameInput);

                    newRow.find('input[name*="[field_label]"]').val(config.label || fieldName);
                    newRow.find('select[name*="[field_type]"]').val(config.type);
                    if (config.required) {
                        newRow.find('input[name*="[field_required]"]').prop('checked', true)
                            .closest('.form-group').addClass('checked');
                    }
                    newRow.find('input[name*="[field_col_sm]"]').val(config.col?.sm || '12');
                    newRow.find('input[name*="[field_col_md]"]').val(config.col?.md || '6');
                    newRow.find('input[name*="[field_col_lg]"]').val(config.col?.lg || '6');
                    newRow.find('textarea[name*="[field_attr]"]').val(config.attr || '');

                    // Show options if needed
                    if (['dropdown', 'multiselect', 'radio', 'checkbox'].includes(config.type)) {
                        newRow.find('.options').show();
                        const optionsStr = '[\n' +
                            Object.entries(config.options || {}).map(([k, v]) => `${k}=${v}`).join(
                                ',\n') +
                            '\n]';
                        newRow.find('textarea[name*="[field_input_options]"]').val(optionsStr);
                    }

                    // Move to next field
                    currentIndex++;
                    setTimeout(addNextField, 100); // Staggered delay

                }
            }, 50);

            // Safety timeout
            setTimeout(() => {
                if (waitForInput._intervalId) clearInterval(waitForInput._intervalId);
            }, 2000);

        }, 100);
    }

    addNextField(); // Start loop
}
//
$('.qmfc').on('change', 'select[name="tables"]', function() {
    let table = $(this).val();
    if (!table) return;

    // Set Form Name (human-readable)
    let nameLabel = table.replace(/_/g, ' ');
    nameLabel = nameLabel.charAt(0).toUpperCase() + nameLabel.slice(1);
    $('input[name="name"]').val(nameLabel);

    // Set Element ID
    $('input[name="form_element_id"]').val(table);

    // Generate preview
    generate_from_table(table);
});

// If the form element ID input is empty, set it to a slugified version of the name input when name losses focus
$('input[name="name"]').on('blur', function() {
    if ($('input[name="form_element_id"]').val() === '') {
        $('input[name="form_element_id"]').val(make_slug($(this).val()));
    }
});

// sluggify the form element ID input when it changes
$('.qmfc').on('change', '.form-group[rel="field_name"] input', function() {
    update_header(this);
    let val = make_slug($(this).val());
    $(this).val(val);
});

// show options if the field type is dropdown, multiselect, radio or checkbox
$('.qmfc').on('change', '.field_input_type', function() {
    let field_type = $(this).val();
    let parent = $(this).closest('.df-sub-field-container');

    let let_option_fields = ['dropdown', 'multiselect', 'radio', 'checkbox'];
    //check if the field type is in the let_option_fields array
    if (let_option_fields.includes(field_type)) {
        //show the options row
        parent.find('.options').show();
    } else {
        //hide the options row
        parent.find('.options').hide();
    }
});

// Toggle accordion content on header click
$('.qmfc').on('click', '.accordion-toggle', function() {
    //get parent accordion-holder
    let holder = $(this).closest('.accordion-holder');
    const content = holder.find('.accordion-content');
    const header = $(this);

    // Remove accordion-open class if it exists (so it can be controlled by active class)
    content.removeClass('accordion-open');
    header.removeClass('accordion-open');

    // Toggle current accordion
    content.toggleClass('active');
    header.toggleClass('active');
});


var df = <?= json_encode(isset($df) ? $df : []) ?>;
var window_df = <?= json_encode(isset($df) ? $df : []) ?>;
console.log('Loaded df:', df);

function update_header(el) {
    let val = $(el).val();
    let parent = $(el).closest('.accordion-holder');
    let header = parent.find('.accordion-header .field-name');
    header.text(val || 'New Field');
}
window.update_header = update_header;

$(document).ready(function() {
    clearDynamicFieldRows();
    setTimeout(() => {
        // Ensure all containers have at least one row
        $('.dynamic-field-container[rel="form-rows"]').each(function() {
            if ($(this).find('.dynamic-field-row').length === 0) {
                $(this).append($('.dynamic-field-template[rel="form-rows"]').clone()
                    .children());
            }
        });

        // Run dynamic_fields_add_extra for each container
        $('.dynamic-field-container').each(function() {
            const con = $(this).attr('rel');
            const rows = $(this).find('.dynamic-field-row');
            rows.each(function() {
                const rowID = $(this).attr('rel');
                if (typeof df !== 'undefined' && df[con] && df[con][rowID]) {
                    dynamic_fields_add_extra(con, rowID);
                }
            });
        });
    }, 600);
});

// Fix for styling/scripts templates being misplaced
if ($('.dynamic-field-template[rel="form-scripts"]').parent().hasClass('form-field-container')) {
    $('body').append($('.dynamic-field-template[rel="form-scripts"]'));
}

function clearDynamicFieldRows() {
    $('.dynamic-field-container[rel="form-rows"] .df-sub-field-holder[rel="form-rows"]').each(function() {
        $(this).html(''); // Remove all sub-field rows
        $(this).attr('data-counter', 0);
    });
}
// Sync layout field values from Tab 5 to hidden inputs in form
function syncLayoutFieldsToForm() {
    const sourceContainer = $('.qm-tabs-tab[rel="5"] .form-display'); // Where fields are shown
    const targetForm = $('.quick-manage-form-container form'); // Your main form

    ['name', 'code', 'description', 'preview_image'].forEach(field => {
        const $sourceInput = sourceContainer.find(`input[name="${field}"]`);
        if ($sourceInput.length) {
            // Remove existing hidden input
            targetForm.find(`input[name="layout_${field}"]`).remove();
            // Add updated value
            const val = $sourceInput.val();
            $('<input>').attr({
                type: 'hidden',
                name: `layout_${field}`,
                value: val
            }).appendTo(targetForm);
            console.log(`Synced ${field}:`, val);
        }
    });
}

// Run on save and after any change in Tab 5
$(document).on('click', '.save-btn, button[type="submit"]', function() {
    setTimeout(syncLayoutFieldsToForm, 100); // Ensure latest values
});

// Also sync when user types
$('.qm-tabs-tab[rel="5"]').on('input', 'input', function() {
    syncLayoutFieldsToForm();
});


function refreshFormPreview() {
    const table = $('select[name="tables"]').val();
    if (table) {
        generate_from_table(table); // Regenerates full form
    }
}

function rebuildFormPreviewFromDF() {
    if (!window.df || !window.df['form-rows']) return;

    // Build schema from df
    const schema = {
        form: {
            id: $('input[name="form_element_id"]').val() || 'form_' + Date.now(),
            class: $('input[name="form_classes"]').val() || '',
            action: $('input[name="form_action"]').val() || ''
        }
    };

    let rowIndex = 0;
    for (const rowKey in window.df['form-rows']) {
        if (!window.df['form-rows'].hasOwnProperty(rowKey)) continue;

        const row = window.df['form-rows'][rowKey];
        const fields = {};

        if (row['df-sub']) {
            Object.values(row['df-sub']).forEach(input => {
                if (!input.field_name) return;

                fields[input.field_name] = {
                    type: input.field_type || 'text',
                    label: input.field_label || input.field_name,
                    required: !!input.field_required,
                    col: {
                        sm: input.field_col_sm || '12',
                        md: input.field_col_md || '6',
                        lg: input.field_col_lg || '6'
                    },
                    attr: input.field_attr ? parseOptionsString(input.field_attr) : {},
                    options: input.field_input_options ? parseOptionsString(input.field_input_options) : {}
                };
            });
        }

        schema[rowIndex] = {
            row: true,
            header: row.row_header || '',
            custom_class: row.row_custom_class || '',
            fields
        };
        rowIndex++;
    }

    // Send to server
    $.ajax({
        url: '<?= site_url("admin/test_form_builder/render_schema"); ?>',
        type: 'POST',
        data: {
            schema: JSON.stringify(schema)
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.form_view) {
                $('.qm-tabs-tab[rel="5"] .form-display').html(response.form_view);
            }
        },
        error: function() {
            console.error('Failed to rebuild form preview');
        }
    });
}

// Helper: Convert string like "key=value" into object
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
// After adding/removing/moving/cloning a field
$(document).on('click',
    '.add_df_sub_field, .remove_df_sub_field_set, .df_sub_set_move_up, .df_sub_set_move_down, .df_sub_set_clone',
    function() {
        setTimeout(rebuildFormPreviewFromDF, 300);
    });

// Also when field_name or type changes
$(document).on('change', 'input[name*="[field_name]"], select[name*="[field_type]"]', function() {
    setTimeout(rebuildFormPreviewFromDF, 500);
});

// After adding, removing, moving, cloning
$(document).on('click',
    '.add_df_sub_field, .remove_df_sub_field_set, .df_sub_set_move_up, .df_sub_set_move_down, .df_sub_set_clone',
    function() {
        setTimeout(updateFormResultPreview, 300);
    });

// When field name, type, or required changes
$(document).on('change', 'input[name*="[field_name]"], select[name*="[field_type]"], input[name*="[field_required]"]',
    function() {
        setTimeout(updateFormResultPreview, 500);
    });
// On edit: if df exists, rebuild preview
$(document).ready(function() {
    if (typeof df !== 'undefined' && Object.keys(df).length > 0) {
        setTimeout(rebuildFormPreviewFromDF, 800);
    }
});

function updateFormResultPreview() {
    console.log('🔄 Rebuilding Tab 5 from layout UI...');

    // Start building schema
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
        const rowID = row.attr('rel');
        const header = row.find('input[name*="[row_header]"]').val() || '';
        const customClass = row.find('input[name*="[row_custom_class]"]').val() || '';
        const fields = {};

        // Collect sub-fields
        row.find('.df-sub-field-row').each(function() {
            const fieldRow = $(this);

            const name = fieldRow.find('input[name*="[field_name]"]').val();
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
        url: '<?= site_url("admin/test_form_builder/render_schema"); ?>',
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
                $('.qm-tabs-tab[rel="5"] .form-display').html('<div class="error">Render failed</div>');
            }
        },
        error: function(xhr) {
            console.error('🚨 AJAX Error:', xhr);
            $('.qm-tabs-tab[rel="5"] .form-display').html('<div class="error">AJAX Error</div>');
        }
    });
}

// Helper: Parse string like "key=value" into object
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

function clearDynamicFieldRows() {
    $('.dynamic-field-container[rel="form-rows"] .df-sub-field-holder[rel="form-rows"]').each(function() {
        $(this).html(''); // Remove all sub-field rows
        $(this).attr('data-counter', 0);
    });
}
</script>