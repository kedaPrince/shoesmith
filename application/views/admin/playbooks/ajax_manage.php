<?php
defined('BASEPATH') || exit('No direct script access allowed');

// if (!isset($row->generate_index_page) || $row->generate_index_page === '') {
//     $row->generate_index_page = '0';
// }
// if (!isset($row->generate_references) || $row->generate_references === '') {
//     $row->generate_references = '0';
// }
?>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qmfc">
    <div class="quick-manage-heading">
        <?php
        if (!empty($row->id)) {
        ?>
        <h2>Edit <?= $this->singular ?> <span><?= $row->name ?></span></h2>
        <?php
        } else {
        ?>
        <h2>Add <?= $this->singular ?></h2>
        <?php
        }
        ?>
        <p>In this module, you can manage the various playbooks.</p>
    </div>
    <div class="form-field-container">
        <?= form_open(); ?>
        <?= form_hidden('id', !empty($row->id) ? $row->id : 0); ?>
        <?= form_hidden('playbook_type_id', !empty($row->playbook_type_id) ? $row->playbook_type_id : 0); ?>
        <div class="form-fields-holder">
            <div class="row">
                <div class="col-lg-12">
                    <?= field_input('name', $row, 'required'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?= field_multi_select('playbook_access_groups|label_access_groups', $access_groups_all, $access_groups, ''); ?>
                </div>
                <div class="col-lg-3">
                    <?= field_radio('generate_index_page|label_generate_index_page', $row, ['1' => 'Yes', '0' => 'No']) ?>
                </div>
                <div class="col-lg-3">
                    <?= field_radio('generate_references|label_generate_references', $row, ['1' => 'Yes', '0' => 'No']) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <h4>Playbook Sections</h4>
                </div>
            </div>
        </div>
        <div class="row dynamic-fields-container relative">
            <div class="col-12 relative dynamic-field-container" rel="items">
                <a class="btn btn-secondary add_dynamic_field_set left hide">
                    <i class="fa fa-plus-circle"></i> Add Item
                </a>
            </div>
        </div>
        <?php
        if ($this->seoFields) {
        ?>
        <h2>SEO Fields</h2>
        <div class="row">
            <div class="col-lg-6">
                <?= field_input('seo_title', $row, ''); ?>
            </div>
            <div class="col-lg-6">
                <?= field_input('seo_keywords', $row, ''); ?>
            </div>
            <div class="col-lg-12">
                <?= field_textarea('seo_description', $row, ''); ?>
            </div>
        </div>
        <?php
        }
        ?>

        <!-- Resource adding -->
        <div class="resource-adding-container relative">
            <div class="row">
                <div class="col-lg-12 relative" style="margin-bottom:1rem;">
                    <h4 class="mt-0">Add resource</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 relative">
                    <?php
                    $options = [
                        1 => 'Create Section',
                        2 => 'Existing Section',
                        3 => 'Select Form/Layout'  // ← New option
                    ];
                    ?>
                    <?= field_dropdown('resource_type_id|label_resource_type', $options, '', ''); ?>
                </div>
                <div class="col-lg-6 relative resource-playbook-section hide">
                    <?= field_dropdown('playbook_section_id|label_playbook_section', $available_playbook_sections, '', ''); ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6 relative resource-layout hide">
            <?= field_dropdown('layout_schema_id|label_select_layout', [], '', ''); ?>
            <sup>Select a pre-built form/layout</sup>
        </div>
        <!-- CK Editor container -->
        <div class="ckeditor-container hide" style="clear: left;">
            <div class="row">
                <div class="col-lg-12 relative">
                    <h4 class="mt-0">Edit Playbook Section</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 relative">
                    <?= field_input('ckeditor_name|label_title', '', ''); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 relative">
                    <?= field_ckeditor('content', '', ''); ?>
                    <div class="btn-container no-hide" style="clear: left; position: relative;">
                        <a title="Close" class="btn btn-primary float-right"
                            onclick="unsetCKEditor(this, true)">Close</a>
                        <a title="Save and Close" class="btn btn-primary float-right"
                            onclick="unsetCKEditor(this)">Save</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buttons at the bottom -->
        <div class="btn-container" style="clear: left; position: sticky; z-index: 10; bottom: 0;">
            <?php
            // echo save_button('Save and add sections');
            echo save_button('Save and Close');
            echo cancel_button('Close', 'left');
            ?>
        </div>
        <?= form_close(); ?>
    </div>
</div>
<div class="dynamic-field-template" rel="items">
    <div class="dynamic-field-row" rel="0" style="margin-top: 2rem; padding-bottom: 2rem;">
        <input type="hidden" name="position" class="position-field" value="0" />
        <input type="hidden" name="playbook_id" value="<?= isset($row->id) ? $row->id : 0; ?>" />
        <input type="hidden" name="playbook_section_id" value="0" />
        <div class="row">
            <div class="col-lg-12">
                <?= field_input('name|label_title', '', '', []); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 text-editor-holder">
                <div>
                    <label class=""><?= lang('label_content') ?></label>
                    <div class="content-fake max-lines"></div>
                </div>
                <div class="hide">
                    <?= field_textarea('content|label_content', '', '', []); ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <a class="remove_dynamic_field_set btn btn-secondary" title="<?= lang('button_remove_item'); ?>"><i
                        class="material-icons-outlined">Remove</i></a>
                <a class="dynamic_field_set_move_up btn btn-secondary arrow-up" title="<?= lang('button_move_up'); ?>"
                    style="background-size: 30%;">&nbsp;</a>
                <a class="dynamic_field_set_move_down btn btn-secondary arrow-down"
                    title="<?= lang('button_move_down'); ?>" style="background-size: 30%;">&nbsp;</a>
            </div>
        </div>
    </div>
</div>
<style>
.dynamic-field-template {
    display: none;
}

.dynamic-field-row {
    margin: 2rem 0 0 0;
    padding-bottom: 2rem;
}

.content-fake {
    background-color: white;
    border-radius: 0.25rem;
    border: 1px solid #ced4da;
    color: #495057;
    height: fit-content;
    margin: 0 0 1rem 0;
    min-height: 114px;
    padding: .375rem .75rem;
    width: 100%;
}

.name-fake {
    background-color: white;
    border-radius: 0.25rem;
    border: 1px solid #ced4da;
    color: #495057;
    height: 34px;
    margin: 0 0 1rem 0;
    min-height: 34px;
    /*padding: .375rem .75rem;*/
    padding: 6px 12px;
    width: 100%;
}

.mt-0 {
    margin-top: 0;
}

.resource-adding-container {
    border: 1px solid silver;
    margin-top: 1.5rem;
    padding: 1rem;
    z-index: 2;
}

.relative {
    position: relative;
}

.max-lines {
    display: block;
    line-height: 1.8em;
    max-height: 3.6em;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    width: 800px;
    word-wrap: break-word;
}
</style>
<script type="text/javascript">
var ajaxCallInProgress = false;
var editingTextareaID = '';
var playbookId = <?= isset($row->id) ? $row->id : 0; ?>;

// Override dynamic fields
df = <?php
            $sections = [
                'items' => [],
            ];
            foreach ($playbook_sections as $playbook_section) {
                $sections['items']['r' . $playbook_section->playbook_section_id] = $playbook_section;
            }
            echo json_encode($sections);
            ?>;

function save_form(el) {
    $(el).closest('form').parsley().whenValidate().done(function() {
        var view = '<?= !empty($row->id) ? 'update' : 'create' ?>';
        var id = <?= !empty($row->id) ? $row->id : '0' ?>;

        ajax_submit_form(el, view, id);
    });
}

/**
 * Used by unsetCKEditor to save the form, but without closing quick manage.
 */
function ajax_save_form(el, view, id) {
    var form = $(el).closest('form');
    form.find('.ecms-field').attr('data-error', '');
    form.parsley().reset();
    var data = form.serialize();
    let url = view == 'update' ? 'update/' + id : 'create';

    show_loader();
    ajax_post(url, data, function(d) {
        hide_loader();
        $('.smart-uploader').remove();
        if (d['success']) {
            flash_notification(d['flasherbody'], 'success');
        } else {
            show_error(d['error']);

            // Show field errors
            if (d.hasOwnProperty('fields')) {
                for (var f in d.fields) {
                    form_error(form, f, d.fields[f]);
                }
            }
        }
    });
}

if (typeof setup_image_fields === 'function') {
    setup_image_fields();
}

function dynamic_fields_add_extra(con, rowID) {
    if (typeof rowID !== 'string') {
        return false;
    }

    let selector = '.dynamic-field-container > .dynamic-field-row[rel="' + rowID + '"] ';
    let name = $(selector + 'input[rel="name"]').val();
    let content = $(selector + 'textarea[rel="content"]').val();
    $(selector + '.name-fake').html(name);
    $(selector + '.content-fake').html(content);
}

function populateTextArea(playbookSectionId, playbookSectionIdOrigin, name, content) {
    const selector = '.dynamic-field-container > .dynamic-field-row[rel="' + playbookSectionId + '"] ';
    $(selector + 'input[rel="playbook_section_id"]').val(playbookSectionIdOrigin);
    $(selector + 'input[rel="name"]').val(name);
    $(selector + 'textarea[rel="content"]').val(content);
    $(selector + '.content-fake').html(content);
}

function setCKEditor(name, content) {
    // Set the data
    $('.ckeditor-container .ecms-field input[name="ckeditor_name"]').val(name);
    window.ckeditor.content.setData(content);

    // Toggle display
    toggleCKEditor(true);
    return true;
}

function unsetCKEditor(el, noSave = false) {
    // Set the data
    if (noSave === null || noSave === false) {
        let content = window.ckeditor.content.getData();
        let name = $('.ckeditor-container .ecms-field input[name="ckeditor_name"]').val();
        let playbookSectionIdOrigin = null;
        populateTextArea(editingTextareaID, playbookSectionIdOrigin, name, content);
    }

    // Toggle display
    toggleCKEditor(false);

    // Save the form
    // $(el).closest('form').parsley().whenValidate().done(function() {
    //     var view = '<?= !empty($row->id) ? 'update' : 'create' ?>';
    //     var id = <?= !empty($row->id) ? $row->id : '0' ?>;
    //     ajax_save_form(el, view, id);
    // });
}

function toggleCKEditor(state) {
    toggleResourceAdding(!state);
    toggleFormFields(!state);
    toggleDynamicfields(!state);
    toggleButtonContainer(!state);
    toggleCKEditorContainer(state);
}

function toggleCKEditorContainer(state) {
    if (state) {
        $('.ckeditor-container').removeClass('hide');
    } else {
        $('.ckeditor-container').addClass('hide');
    }
}

function toggleButtonContainer(state) {
    if (state) {
        $('.btn-container').removeClass('hide');
    } else {
        $('.btn-container').not('.no-hide').addClass('hide');
    }
}

function toggleDynamicfields(state) {
    if (state) {
        $('.dynamic-fields-container').removeClass('hide');
    } else {
        $('.dynamic-fields-container').addClass('hide');
    }
}

function toggleFormFields(state) {
    if (state) {
        $('.form-field-container .form-fields-holder').removeClass('hide');
    } else {
        $('.form-field-container .form-fields-holder').addClass('hide');
    }
}

function toggleResourceAdding(state) {
    if (state) {
        $('.resource-adding-container').removeClass('hide');
    } else {
        $('.resource-adding-container').addClass('hide');
    }
}

function toggleResourceSectionList(state) {
    if (state) {
        $('.resource-adding-container .resource-playbook-section').removeClass('hide');
    } else {
        $('.resource-adding-container .resource-playbook-section').addClass('hide');
    }
}

function fetchExistingPlaybookSection(playbookSectionId, addNew = true) {
    if (ajaxCallInProgress) {
        return;
    }
    ajaxCallInProgress = true;
    ajax_post('../playbook_sections/ajax_get_single_html/' + playbookSectionId, {}, function(d) {
        ajaxCallInProgress = false;
        if (d.success && typeof d.content !== 'undefined') {
            let playbookSectionIdNew = playbookSectionId;
            if (addNew) {
                playbookSectionIdNew = addNewSectionAndGetId();
            }
            populatePlaybookSection(playbookSectionIdNew, playbookSectionId, d.name, d.content);
            // toggleResourceAdding(false);
            toggleDynamicfields(true);
            scrollToElement('div.dynamic-field-row[rel="' + playbookSectionIdNew + '"]');
        } else {
            console.warn('[fetchExistingPlaybookSection]', d);
        }
    });
}

function fetchAvailablePlaybookSections(playbook_id) {
    if (ajaxCallInProgress) {
        return false;
    }
    if (typeof playbook_id !== 'string' && typeof playbook_id !== 'number') {
        return false;
    }
    if (typeof playbook_id === 'string') {
        if (isNaN(playbook_id)) {
            console.warn('Playbook ID is not a number');
            return false;
        }
    }

    // Fetch the data
    ajaxCallInProgress = true;
    ajax_post('ajax_get_available_playbook_sections/' + playbook_id, {}, function(d) {
        ajaxCallInProgress = false;
        if (d.success && typeof d.content === 'object') {
            const skipIdsList = getUsedPlaybookSectionIds();
            replaceSelect('.resource-adding-container .ecms-field[rel="playbook_section_id"]', d.content,
                skipIdsList);
        } else {
            console.warn(d.message);
        }
    });
    return true;
}

function getUsedPlaybookSectionIds() {
    let playbookSectionIdsList = [];
    $('.dynamic-field-container').children('.dynamic-field-row').each(function() {
        let value = $(this).children('input[rel="playbook_section_id"]').val();
        if (isNaN(value) || value === '0') {
            return;
        }
        playbookSectionIdsList.push(value);
    });

    return playbookSectionIdsList;
}

function populatePlaybookSection(playbookSectionId, playbookSectionIdOrigin, name, content) {
    // toggleResourceAdding(false);
    toggleDynamicfields(true);
    populateTextArea(playbookSectionId, playbookSectionIdOrigin, name, content);
}

function resetSelect(ref) {
    // Reset the actual select list
    $(ref)[0].selectedIndex = 0;

    // Reset the search
    $(ref).siblings('.btn-group').children('ul').children('li.multiselect-item').children('.input-group').children(
        'input.multiselect-search').val('');

    // More the active marked on the displayed list
    $(ref).siblings('.btn-group').children('ul').children('li.active').removeClass('active');
    $(ref).siblings('.btn-group').children('ul').children('li').eq(1).addClass('active');

    // Reset the visible selected text
    $(ref).siblings('.btn-group').children('button.dropdown-toggle').children('span').text($(ref + ' > option')[0]
        .text);

    // Inside resetSelect(selector)
    if (selector === 'select[name="layout_schema_id"]') {
        $(selector).val('');
    }
}

function replaceSelect(selector, data, skipIdsList) {
    // Clear the select list
    const selectorSelect = selector + ' select';
    $(selectorSelect).find('option').remove().end().append(
        '<option class="static_option" value="">Please select...</option>').val('');

    // Clear the displayed list
    const selectorList = selector + ' ul.dropdown-menu';
    $(selectorList).children('li').each(function() {
        // Keep search filter and please select
        if ($(this).hasClass('filter') || $(this).hasClass('active')) {
            return;
        }
        $(this).remove();
    });

    // Append the data
    for (let index in data) {
        // Check if already used
        let skip = false;
        for (let skipIdsIndex in skipIdsList) {
            if (data[index].id === skipIdsList[skipIdsIndex]) {
                skip = true;
                break;
            }
        }
        if (skip) {
            continue;
        }
        $(selectorSelect).append('<option value="' + data[index].id + '">' + data[index].name + '</option>');
        $(selectorList).append('<li><a tagindex="0"><label class="radio"><input type="radio" value="' + data[index].id +
            '"> ' + data[index].name + '</label></a></li>');
    }
}

function addNewSectionAndGetId() {
    $('.add_dynamic_field_set').trigger('click');
    let playbookSectionIdNew = '';
    $('div.dynamic-field-container').children('.dynamic-field-row').each(function() {
        playbookSectionIdNew = $(this).attr('rel');
    });
    return playbookSectionIdNew;
}

function scrollToElement(identifier) {
    $([document.documentElement, document.body]).animate({
        scrollTop: $(identifier).offset().top
    }, 2000);
}

jQuery(document).ready(function() {
    var $ = jQuery;

    // Show CK Editor
    $('.dynamic-field-container').on('click', '> .dynamic-field-row .text-editor-holder .content-fake',
        function(event) {
            event.preventDefault();

            // Set the ID for CK editor and then show the editor passing along content
            editingTextareaID = $(this).parent().parent().parent().parent().attr('rel');
            let selector = '.dynamic-field-container > .dynamic-field-row[rel="' + editingTextareaID +
                '"] ';
            let name = $(selector + 'input[rel="name"]').val();
            let content = $(selector + 'textarea[rel="content"]').val();
            setCKEditor(name, content);
        });

    // Add resource button
    $('.add_resource_field_set').on('click', function(event) {
        toggleDynamicfields(false);
        toggleResourceSectionList(false);
        resetSelect('select[name="resource_type_id"]');
        resetSelect('select[name="playbook_section_id"]');
        resetSelect('select[name="layout_schema_id"]');
        // toggleResourceAdding(true);
    });

    // Resource type selection
    $('div[rel="resource_type_id"] .btn-group > ul > li > a > label').on('click', function(event) {
        event.preventDefault();

        toggleResourceSectionList($(this).text() === ' Existing Section');
        if ($(this).text() === ' Create Section') {
            // toggleResourceAdding(false);
            toggleDynamicfields(true);
            scrollToElement('div.dynamic-field-row[rel="' + addNewSectionAndGetId() + '"]');
        }
        if ($(this).text() === ' Existing Section') {
            fetchAvailablePlaybookSections(playbookId);
        }
        //layout option
        if ($(this).text() === ' Select Form/Layout') {
            toggleResourceSectionList(false); // Hide section list
            toggleLayoutSelector(true); // Show layout selector
            fetchAvailableLayouts(playbookId);
        }
    });

    // Existing playbook section
    $('div[rel="playbook_section_id"] .btn-group > ul').on('click', 'li > a > label', function(event) {
        event.preventDefault();

        let playbookSectionId = 0;
        $(this).children('input[type="radio"]').each(function(index, row) {
            playbookSectionId = parseInt($(this).val());
        });
        if (playbookSectionId === 0) {
            console.warn('Playbook section ID not found');
            return;
        }
        fetchExistingPlaybookSection(playbookSectionId, true);
        toggleResourceSectionList(false);
    });

    // Close the add resource section
    // $('.resource-adding-container-close').on('click', function(event) {
    //     event.preventDefault();
    //     // toggleResourceAdding(false);
    //     toggleDynamicfields(true);
    //     toggleResourceSectionList(true);
    // });
});

function toggleLayoutSelector(state) {
    if (state) {
        $('.resource-layout').removeClass('hide');
    } else {
        $('.resource-layout').addClass('hide');
    }
}

function fetchAvailableLayouts(playbookId) {
    if (ajaxCallInProgress) return;
    ajaxCallInProgress = true;

    $.ajax({
        url: '<?= site_url("admin/playbooks/ajax_get_available_layouts"); ?>/' + playbookId,
        type: 'POST',
        dataType: 'json',
        success: function(d) {
            ajaxCallInProgress = false;
            if (d.success && d.content.length > 0) {
                const $select = $('.resource-layout select[name="layout_schema_id"]');

                // ✅ Clear previous options
                $select.empty().append('<option value="">Please select...</option>');

                // ✅ Add new options
                d.content.forEach(function(item) {
                    $select.append(`<option value="${item.schema_id}">${item.name}</option>`);
                });

                // ✅ Get multiselect container ID for cleanup
                const msId = $select.attr('id') || 'layout_' + Date.now();
                $select.attr('id', msId);

                // ✅ Destroy existing multiselect if exists
                if ($select.data('multiselect')) {
                    $select.multiselect('destroy');
                }

                // ✅ Re-init multiselect
                $select.multiselect({
                    enableFiltering: true,
                    enableCaseInsensitiveFiltering: true,
                    maxHeight: 200,
                    buttonClass: 'form-control',
                    includeSelectAllOption: false
                }).multiselect('rebuild');

                toggleLayoutSelector(true); // Ensure visible

            } else {
                console.warn('No layouts available:', d.message);
                toggleLayoutSelector(false);
            }
        },
        error: function(xhr, status, err) {
            ajaxCallInProgress = false;
            console.error('Failed to load layouts', err);
            toggleLayoutSelector(false);
        }
    });
}
// Inside document.ready()
$('.resource-layout select[name="layout_schema_id"]').on('change', function() {
    const schemaId = $(this).val();
    if (!schemaId) return;

    // Insert the form immediately
    loadAndInsertFormLayout(schemaId);

    // Close resource panel
    toggleResourceAdding(false);
});

function loadAndInsertFormLayout(schemaId) {
    show_loader();
    console.log('🔍 Attempting to load form for schemaId:', schemaId);

    $.ajax({
        url: '<?= site_url("admin/test_form_builder/get_form_by_schema_id"); ?>/' + schemaId,
        type: 'POST',
        dataType: 'json',
        data: {
            schema_id: schemaId
        }, // Explicitly send POST data
        success: function(response) {
            hide_loader();
            console.log('✅ Success - Response:', response);

            if (response.success && response.form_view) {
                const $container = $('.dynamic-field-container[rel="items"]');
                const fakeRowId = 'layout_' + Date.now();

                let html = `
                <div class="dynamic-field-row" rel="${fakeRowId}" style="margin-top: 2rem; padding-bottom: 2rem;">
                    <input type="hidden" name="df[items][${fakeRowId}][playbook_section_id]" value="0">
                    <input type="hidden" name="df[items][${fakeRowId}][position]" value="0">
                    <input type="hidden" name="df[items][${fakeRowId}][schema_id]" value="${schemaId}">
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <h4>Form: ${response.form_schema.form.id}</h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            ${response.form_view}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <a class="remove_dynamic_field_set btn btn-secondary">Remove</a>
                        </div>
                    </div>
                </div>`;

                $container.append(html);
                toggleDynamicfields(true);
                scrollToElement('.dynamic-field-row[rel="' + fakeRowId + '"]');
            } else {
                flash_notification('Error loading form: ' + (response.message || 'Unknown error'), 'error');
            }
        },
        error: function(xhr, status, err) {
            hide_loader();
            console.error('🚨 AJAX Error:', status, err);
            console.error('XHR Response:', xhr.responseText); // 👈 This shows the HTML that broke JSON
            flash_notification('AJAX Error: Check console.', 'error');
        }
    });
}
</script>