$(document).ready(function() {

    //Initialise Quick Manange
    qm_init();

    //Initialise Numbers Only Validation
    numbers_only_validation_init();

    //Check for uploaders and add validators
    load_custom_parsley_validators();

    //Initials listing positions
    listing_position_init();

    //Init menu tooltips
    init_menu_tooltips();

});

function qm_init() {
    // Jerry asked for this. close quick manage whenever you click outside it
    $(document).on('click', function(event) {

        //Don't close quick manage if clicking inside quick manage container
        if ($(event.target).closest('.quick-manage-container').length) {
            return;
        }

        //Don't close quick manage if any of the hidden template items get triggered
        if ($(event.target).closest('.hidden-templates').length) {
            return;
        }

        //Don't close if uploader field is triggered
        if ($(event.target).closest('.dz-hidden-input').length) {
            return;
        }

        //Don't close if using image cropping popup
        if ($(event.target).closest('.image-popup').length) {
            return;
        }

        //Don't close when removing an image/file
        if ($(event.target).closest('.dz-preview').length) {
            return;
        }
        
        //Don't close when interacting with sweet alert popups
        if ($(event.target).closest('.swal2-container').length) {
            return;
        }
        
        //Don't close when using flatpickr date picker
        if ($(event.target).closest('.flatpickr-calendar').length) {
            return;
        }

        //Don't close when viewing the full single page
        if ($(event.target).closest('.qm-full-page').length) {
            return;
        }

        //Temporarily disabled because it's super annoying :(
        //close_qm();
    });

    //Setup tabbing
    $('.quick-manage-container').on('click', '.qm-tabs .qm-tabs-header li', function() {
        qm_open_tab($(this).attr('rel'));
    });
}

function numbers_only_validation_init() {
    //Only allow numbers to be entered
    $(document).on('keydown', '.numbers_only', function(e){
        var valid = false;
        //Arrow keys
        if (e.keyCode >= 37 && e.keyCode <= 40) {
            valid = true 
        }
        //num keys
        if (e.keyCode >= 96 && e.keyCode <= 110) {
            valid = true 
        }
        //num keys
        if (e.keyCode >= 48 && e.keyCode <= 57) {
            valid = true 
        }
        //backspace, tab and enter
        if ( e.keyCode == 8 || e.keyCode == 9 || e.keyCode == 13) {
            valid = true 
        }
        //Check for period
        if (!$(this).hasClass('no_period') && e.keyCode == 190) {
            valid = true;
        }
        //Escape key
        if (e.keyCode == 27) {
            valid = true 
        }
        //Home, End, Del keys
        if (e.keyCode == 36 || e.keyCode == 35 || e.keyCode == 46) {
            valid = true 
        }
        //Ctrl + (v, c, x, z) paste, copy, cut, undo
        if (e.ctrlKey && (e.keyCode == 86 || e.keyCode == 67 || e.keyCode == 88 || e.keyCode == 90)) {
            valid = true 
        }
        //Negative form numpad and hyphen on number row
        if (!e.ctrlKey && (e.keyCode == 189 || e.keyCode === 109)) {
            valid = true;
        }

        //Skip <>_ and symbols above numbers on the keyboard
        if (valid && !e.ctrlKey) {
            // Skip if arrow keys or control characters
            const skipKeyCodes = [27, 35, 36, 37, 38, 39, 40, 46];
            if (e.keyCode >= 32 && !skipKeyCodes.includes(e.keyCode)) {
                var cleanValue = e.key.replace(/[^\d.-]/g, '');
                if (cleanValue === '') {
                    valid = false;
                }
            }
        }

        return valid;
    });

    //Strip all non-numbers from pasted content for number_only fields
    $(document).on('paste', '.numbers_only', function(e){
        var me = $(this);
        setTimeout(function() {
            var cleanValue = me.val().replace(/[^\d.-]/g, '');

            me.val(cleanValue);
        }, 10);
    });
}

function listing_position_init() {
    $('.data-table').on('change', '.listing-position', function(e){
        var val     = $(this).val();
        var id      = $(this).attr('rel');
        var num     = $(this).attr('data-total');

        change_position(id, val, num);
    });
}

function compare_objects(o1, o2) {
    var k = '';
    for(k in o1) if(o1[k] != o2[k]) return false;
    for(k in o2) if(o1[k] != o2[k]) return false;
    return true;
}

function validate_email(email) {
    //Don't validate if not filled in
    if (!email.length) {
        return true;
    }

    var emailReg = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return emailReg.test( email );
}

function validate_phone(phone) {
    //Don't validate if not filled in
    if (!phone.length) {
        return true;
    }

    var phoneReg = /^0\d{9}$/;
    return phoneReg.test(phone);
}

function validate_url(url) {
    //Don't validate if not filled in
    if (!url.length) {
        return true;
    }

    var urlReg = /^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/;
    return urlReg.test( url );
}

//CMS FUNCTIONS
function load_results_bottom() {
    if ($('.data-table tbody tr.data-table-row').length < crudLimit || previousBatchCount < crudLimit) {
        return;
    }
    show_loader();

    //Check if max batches has been set else set to 5
    if (typeof maxBatches === 'undefined' || !maxBatches) {
        var maxBatches = 5;
    }

    //Check if section has been set
    if (typeof section === 'undefined' || !section) {
        var urlSection = '';
    }
    else {
        //If it is set, just prepend a slash so it can be added to the end of the url
        var urlSection = '/'+section;
    }

    //Get last batch number
    var batch   = $('.data-table tbody tr.data-table-row:last').attr('data-row-batch');
    var lastRow = $('.data-table tbody tr.data-table-row:last');

    //Request next batch
    $.ajax({
        url: dynamicPath+'/ajax_pager_fetch_batch/'+(batch/1+1)+urlSection,
        cache: false
    }).done(function(d) {
        hide_loader();
        if (d != '') {
            //Append data
            $('.data-table tbody > tr:last').after(d);

            //Trigger custom event to state that the batch has been loaded
            $(document).trigger('batchLoaded');

        }
    }).fail(function(){
        hide_loader();
        console.log('Failed to fetch the next set of data for the listing!');
    });
}

function load_results_init(template) {
    show_loader();

    //Check if section has been set
    if (typeof section === 'undefined' || !section) {

        section = $("#hidden_section").val();
        var urlSection = '';
    }
    else {
        //If it is set, just prepend a slash so it can be added to the end of the url
        var urlSection = '/'+section;
    }
    //Get the batch to load initialy
    var batch = 1;

    //determine what template to use
    if (typeof template === 'undefined' || !template) {
        var template = 'listing';
    }


    if (template == 'listing') {
        //Request next batch
        $.ajax({
            url: dynamicPath+'/ajax_pager_fetch_batch/'+(batch/1)+urlSection,
            cache: false
        }).done(function(d) {
            hide_loader();
            //Replace data in table
            if ($('.data-table tbody').length) {
                if (d != '') {
                    $('.data-table tbody').html(d);
                    //$('.filter-icon-holder').html('Results: ' + $('#amount').html())
                    previousBatchCount = $(d).length;
                }
                else {
                    $('.data-table tbody').html('<tr><td class="no-rows" colspan="'+$('.data-table tr.header-row th').length+'">'+noResults+'</td></tr>');
                }

                //Trigger custom event to state that the first batch has been loaded
                $(document).trigger('batchInit');

                //Trigger custom event to state that the batch has been loaded
                $(document).trigger('batchLoaded');
            }

        }).fail(function(){
            hide_loader();
            console.log('Failed to fetch the next set of data for the listing!');
        });
    }

}

function set_sorting(field, dir, extend) {
    show_loader();

    extend = (typeof extend !== 'undefined') ?  extend : 0;

    //Request next batch
    $.ajax({
        url: dynamicPath+'/ajax_set_sorting/'+field+'/'+dir+'/'+extend,
        cache: false
    }).done(function(d) {
        if (d != '') {
            load_results_init();
        }
    }).fail(function(){
        hide_loader();
        console.log('Failed to set sorting!');
    });

}

async function fetch_post(path, params, callback, options) {
    var formData = new FormData();

    // Add data to the FormData instance
    for (var key in params) {
        formData.append(params[key]['name'], params[key]['value']);
    }

    formData.append(csrfName, csrf);
    formData.append('is_fetch', true);

    var callback = (typeof callback !== 'undefined') ? callback : null;
    var options = (typeof options !== 'undefined') ? options : {};

    options.url = (!options.hasOwnProperty('url')) ?  dynamicPath+'/'+path : options.url;


    try {
        let response = await fetch(options.url, {
            method: 'POST',
            body: formData
        });

        let result = await response.json();

        // Refresh csrf token
        csrf = result.csrf;

        // Also refresh the token for all forms
        $('form input[name="'+csrfName+'"]').each(function(){
            $(this).val(csrf);
        });

        if (typeof callback == 'function') {
            callback(result);
        }
        else if (typeof window[callback] == 'function') {
            window[callback](result);
        }

    } catch (error) {
        hide_loader();
        console.log('Failed to do ajax post!');
    }
}

//Function for making ajax posts
function ajax_post(path, params, callback, options) {

    params[csrfName] = csrf;

    var callback = (typeof callback !== 'undefined') ? callback : null;
    var options = (typeof options !== 'undefined') ? options : {};

    options.url = (!options.hasOwnProperty('url')) ?  dynamicPath+'/'+path : options.url;
    options.method = (!options.hasOwnProperty('method')) ? 'POST' : options.method;
    options.cache = (!options.hasOwnProperty('cache')) ? false : options.cache;
    options.data = (!options.hasOwnProperty('data')) ? params : options.data;

    $.ajax(options).done(function(d) {
        //Refresh csrf token
        csrf = d.csrf;

        //Also refresh the token for all forms
        $('form input[name="'+csrfName+'"]').each(function(){
            $(this).val(csrf);
        });

        //Run callback function
        if (typeof callback == 'function') {
            callback(d);
        }
        else if (typeof window[callback] == 'function') {
            window[callback](d);
        }
    }).fail(function() {
        hide_loader();
        console.log('Failed to do ajax post!');
    });
}

//Function for making ajax gets
function ajax_get(path, params, callback, options) {

    var callback = (typeof callback !== 'undefined') ? callback : null;
    var options = (typeof options !== 'undefined') ? options : {};

    options.url = (!options.hasOwnProperty('url')) ?  dynamicPath+'/'+path : options.url;
    options.method = 'GET';
    options.cache = (!options.hasOwnProperty('cache')) ? false : options.cache;
    options.data = (!options.hasOwnProperty('data')) ? params : options.data;

    $.ajax(options).done(function(d) {

        //Run callback function
        if (typeof callback == 'function') {
            callback(d);
        }
        else if (typeof window[callback] == 'function') {
            window[callback](d);
        }
    }).fail(function() {
        hide_loader();
        console.log('Failed to do ajax get!');
    });
}

//TODO: Test in new theme
//Builds dynamic dropdown lists
function setup_dynamic_dropdowns(parent, rel) {
    //show_loader();
    var val = parent.val();
    var childClass = parent.attr('data-child-list');
    if (childClass) {
        var child = (typeof rel !== 'undefined') ? $('div[rel="' + rel + '"] .'+childClass) : $('.'+childClass);
        var table = child.attr('data-table');
        var parentField = (typeof parent.attr('data-field') !== 'undefined')  ? parent.attr('data-field') : parent.attr('name');
        var initVal = child.attr('data-initial-value');

        var firstOption = child.find('.static_option')[0].outerHTML;
        if (val != '') {
            ajax_post('ajax_get_list_values',{
                table: table,
                field: parentField,
                value: val,
                selected: initVal
            }, function(d){
                if (d.success) {
                    child.html(firstOption+''+d.html);
                    child.prop('disabled', false);
                    setup_dynamic_dropdowns(child, rel);
                };
            });
        }
        else {
            child.html(firstOption);
            child.prop('disabled', true);
            setup_dynamic_dropdowns(child, rel);
        }
    }
    else {
        hide_loader();
    }
}

//Get filter params
function get_filter_params() {
    var filters = {};
    $('.listing-filters .filter-value').each(function(){
        filters[$(this).attr('name')] = $(this).val();
    });

    $('.extra-filters .filter-value').each(function(){
        filters[$(this).attr('name')] = $(this).val();
    });

    return filters;
}

function show_loader() {
    $('.page-loader-wrapper').fadeIn('300');
    $('.page-loader-wrapper .loader').addClass('loading');
}

function hide_loader() {
    $('.page-loader-wrapper').fadeOut('300');
    $('.page-loader-wrapper .loader').removeClass('loading');
    $(document).trigger('loaderDone');
}

function setup_dynamic_template() {
    $('.dynamic-field-template').each(function(){
        var rel = $(this).attr('rel');

        //Setup general fields
        $(this).find('[name]').each(function(){
            var name = $(this).attr('name');
            $(this).attr('rel', name);

            //Ignore file uploaders
            // if ($(this).hasClass('file-upload-field')) {
            //     $(this).attr('name', 'df:'+rel+':xxx:'+name);
            //     return true;
            // }

            $(this).attr('name', 'df['+rel+'][xxx]['+name+']');
        });

        //Setup multi-selects
        $(this).find('.multi_select').each(function(){
            var name = $(this).attr('rel');
            $(this).find('[type="checkbox"]').each(function(){
                $(this).attr('name', 'df['+rel+'][xxx]['+name+'][]');
            })
        });
    });

    $('.dynamic-field-container').each(function(){
        var con = $(this).attr('rel');

        df_load_data(con);

        if ($(this).find('.dynamic-field-row').length == 0) {
            //Add empty row
            dynamic_fields_add(con);
        }
        else {
            //Get the highest number of the new fields
            var highestNum = 0;
            $(this).find('.dynamic-field-row').each(function(){
                var rel = $(this).attr('rel');
                if (rel.match(/new_\d+/)) {
                    var num = rel.replace('new_', '');
                    if (num > highestNum) {
                        highestNum = num;
                    }
                }
            });

            $('.dynamic-field-container[rel="'+con+'"]').attr('data-counter', highestNum);
        }
    });

    $('.dynamic-field-container').on('click', '.add_dynamic_field_set', function(){
        var con = $(this).closest('.dynamic-field-container').attr('rel');
        dynamic_fields_add(con);
    });

    $('.dynamic-field-container').on('click', '.remove_dynamic_field_set', function(){
        var con = $(this).closest('.dynamic-field-container').attr('rel');
        var row = $(this).closest('.dynamic-field-row').attr('rel');
        dynamic_fields_remove(con, row);
    });

    $('.dynamic-field-container').on('click', '.duplicate_dynamic_field_set', function(){
        var con = $(this).closest('.dynamic-field-container').attr('rel');
        var row = $(this).closest('.dynamic-field-row').attr('rel');
        dynamic_fields_duplicate(con, row);
    });

    $('.dynamic-field-container').on('click', '.dynamic_field_set_move_up', function(){
        var con = $(this).closest('.dynamic-field-container').attr('rel');
        var row = $(this).closest('.dynamic-field-row').attr('rel');
        dynamic_fields_move(con, row, 'up');
    });

    $('.dynamic-field-container').on('click', '.dynamic_field_set_move_down', function(){
        var con = $(this).closest('.dynamic-field-container').attr('rel');
        var row = $(this).closest('.dynamic-field-row').attr('rel');
        dynamic_fields_move(con, row, 'down');
    });

}

function df_load_data(con) {
    if (df.hasOwnProperty(con)) {
        var posArray = new Array();
        for (var id in df[con]) {
            //If position property exist then rearrange array by position
            if (df[con][id].hasOwnProperty('position')) {
                posArray[df[con][id]['position']/1-1] = id;
            }
            else {
                dynamic_fields_add(con, id);
            }
        }

        if (posArray.length) {
            //Loop fields in position ordering
            for (var p in posArray) {
                var pos = posArray[p];
                dynamic_fields_add(con, pos);
            }
        }
    }
}

function dynamic_fields_add(con, id, stopScripts){
    id = (typeof id !== 'undefined') ? id : null;
    stopScripts = (typeof stopScripts !== 'undefined') ? stopScripts : {};

    //Next row num will be the number
    var rowID;

    //If ID is not given, then add new blank field
    if (!id) {
        //New fields will be named like "new_#". This determines the number.
        var c = $('.dynamic-field-container[rel="'+con+'"]').attr('data-counter');
        c = c ? c/1 + 1 : 1;
        $('.dynamic-field-container[rel="'+con+'"]').attr('data-counter', c);
        rowID = 'new_'+c;
    }
    else {
        rowID = id;
    }

    //Code to execute before adding a new field
    if (typeof dynamic_fields_add_extra_before === "function") {
        var continueExecution = dynamic_fields_add_extra_before(con, rowID);
        if (!continueExecution) {
            return false;
        }
    }

    //Add fields with new row number
    $('.dynamic-field-template[rel="'+con+'"] .dynamic-field-row').clone().attr('rel', rowID).insertBefore('.dynamic-field-container[rel="'+con+'"] .add_dynamic_field_set');

    var dfRow = $('.dynamic-field-container[rel="'+con+'"] .dynamic-field-row[rel="'+rowID+'"]');

    // This is the fix for multiple error messages. Each dynamic field requires a unique id
    dfRow.find('[name]').each(function(){
        var name = $(this).attr('name');

        name = name.replace(/\[xxx\]/, '['+rowID+']');

        $(this).attr('name', name);

        // Set unique parsley error container for each field
        var uniqueErrorContainerId = 'error-container-' + rowID + '-' + $(this).attr('name').replace(/\W/g, '_');
        $(this).attr('data-parsley-errors-container', '#' + uniqueErrorContainerId);

        // Add the unique error container to the field's HTML
        $(this).closest('.ecms-field').find('.field-error-container').attr('id', uniqueErrorContainerId);
    });

    //Check if data exists for this row
    if (df.hasOwnProperty(con) && df[con].hasOwnProperty(id)) {
        for (var field in df[con][id]) {
            var dfField = dfRow.find('[name="df['+con+']['+id+']['+field+']"]');

            //If checkbox then mark as checked
            if (dfField.attr('type') == 'checkbox') {
                if (dfField.attr('value') == df[con][id][field]) {
                    dfField.attr('checked', 'checked').prop('checked', true);
                    dfField.closest('checkbox').addClass('checked');
                }
                else {
                    dfField.attr('checked', '').prop('checked', false);
                    dfField.closest('checkbox').removeClass('checked');
                }
            }
            else {
                dfField.val(df[con][id][field])
            }
        }
    }

    if (typeof dynamic_fields_add_function === "function") {
        dynamic_fields_add_function(con, rowID);
    }

    //Setup uploaders
    if (!stopScripts.hasOwnProperty('uploaders')) {
        
        dfRow.find('.dz-uploader').each(function(){ // @ ryno, I had to change this
            var uploaderField   = $(this)

            if (uploaderField.length) {

                var uploaderName    = uploaderField.attr('rel');
                var dfName          = 'df_'+con+'_'+rowID+'_'+uploaderName;
                var ecmsField       = uploaderField.closest('.ecms-field');

                uploaderField.attr('rel', dfName);
                ecmsField.attr('rel', dfName);
                ecmsField.find('input[rel="'+uploaderName+'_validator"]').attr('name', dfName+'_validator');
                ecmsField.find('input[rel="'+uploaderName+'_validator"]').attr('rel', dfName+'_validator');

                //Add this field to the list of uploaders
                var html = '<input class="ignore" type="hidden" name="df['+con+']['+rowID+'][uploaders][]" value="'+uploaderName+'|'+dfName+'" />';
                $('.dynamic-field-container[rel="'+con+'"] .dynamic-field-row[rel="'+rowID+'"]').append(html);

                if (rowID.match(/new/)) {
                    window.uploaderData[dfName] = [];
                    setTimeout(function() {
                        setup_dz_uploader(dfName, uploaderName);
                    }, 300);
                    return;
                }

                //Fetch uploaded data
                ajax_post('ajax_get_df_upload_data', {
                    action: 'get_df_upload_data',
                    originalFieldName: uploaderName,
                    rowID: rowID.replace('r', '')
                }, function(d) {
                    if (d.success) {
                        window.uploaderData[dfName] = d.data;
                    }
                    else {
                        console.log(d.error);
                    }

                    //Setup uploader
                    setup_dz_uploader(dfName, uploaderName);
                });
            }

        })

    }

    reset_dynamic_field_positions(con);

    //Detect if we are working within tabs and add the parsley validation groups to the fields
    if ($('.dynamic-field-container[rel="'+con+'"]').closest('.qm-tabs-tab').length) {
        let tab = $('.dynamic-field-container[rel="'+con+'"]').closest('.qm-tabs-tab').attr('rel');

        dfRow.find('input').attr('data-parsley-group', 'tab-' + tab);
        dfRow.find('textarea').attr('data-parsley-group', 'tab-' + tab);
        dfRow.find('select').attr('data-parsley-group', 'tab-' + tab);
    }

    if (typeof dynamic_fields_add_extra === "function") {
        dynamic_fields_add_extra(con, rowID);
    }

    return rowID;
}

function dynamic_fields_remove(con, row){

    $('.dynamic-field-container[rel="'+con+'"] .dynamic-field-row[rel="'+row+'"]').remove();

    //If all rows gets removed, then add a blank row
    if (!$('.dynamic-field-container[rel="'+con+'"] .dynamic-field-row').length) {
        dynamic_fields_add(con)
    }

    reset_dynamic_field_positions(con);

    if (typeof dynamic_fields_remove_extra === "function") {
        dynamic_fields_remove_extra(con, row);
    }

}

function dynamic_fields_move(con, row, dir){
    var dfRow   = $('.dynamic-field-container[rel="'+con+'"] .dynamic-field-row[rel="'+row+'"]');
    var dfCount = $('.dynamic-field-container[rel="'+con+'"] .dynamic-field-row').length;
    var dfPos   = dfRow.find('.position-field').val();

    if (dir == 'up') {
        //Can't move up if first row
        if (dfPos > 1) {
          dfRow.prev('.dynamic-field-row').before(dfRow);
            // dfRow.exchangePositionWith(dfRow.prev('.dynamic-field-row'));
        }
    }
    else if (dir == 'down') {
        //Can't move down if last row
        if (dfPos < dfCount) {
            dfRow.next('.dynamic-field-row').after(dfRow);
            // dfRow.exchangePositionWith(dfRow.next('.dynamic-field-row'));
        }
    }

    reset_dynamic_field_positions(con);

    if (typeof dynamic_fields_move_extra === "function") {
        dynamic_fields_move_up_extra(con, row, dir);
    }

}

function reset_dynamic_field_positions(con) {
    var i = 0;
    $('.dynamic-field-container[rel="'+con+'"] .dynamic-field-row').each(function(){
        i++;
        $(this).find('.position-field').val(i);
    });
}

//TODO: Adjust for new theme
function dynamic_fields_duplicate(con, row){
    var toDuplicate = $('.dynamic-field-container[rel="'+con+'"] .dynamic-field-row[rel="'+row+'"]');
    var newRowID = dynamic_fields_add(con, null, {fileuploader:true});

    //Populate new row with data from row to duplicate
    $('.dynamic-field-container[rel="'+con+'"] .dynamic-field-row[rel="'+newRowID+'"]').find('[name]:not(.ignore)').each(function(){

        $(this).val(toDuplicate.find('[rel="'+$(this).attr('rel')+'"]').val());
        if ($(this).val() !== '') {
            $(this).next('.validation-icon').addClass('pass');
        }

    });

    //Populate any multiselect files
    $('.dynamic-field-container[rel="'+con+'"] .dynamic-field-row[rel="'+newRowID+'"]').find('.multi_select').each(function(){
        $(this).val(toDuplicate.find('[rel="'+$(this).attr('rel')+'"]').val());
    });


    if (typeof dynamic_fields_duplicate_extra === "function") {
        dynamic_fields_duplicate_extra(con, newRowID, row);
    }

}

function show_error(msg) {
    Swal.fire({
        title: 'Error',
        html: msg,
        showCancelButton: false,
        confirmButtonColor: "#59c4bc",
        confirmButtonText: "Ok",
        closeOnConfirm: true
    });
}

function confirm_action(el, event) {

    event.stopPropagation();
    event.preventDefault();

    var confirmData     = $(el).next('span.confirm_data');
    var header          = confirmData.find('.header').text();
    var body            = confirmData.find('.body').text();
    var colour          = confirmData.find('.colour').text();
    var confirmLabel    = confirmData.find('.confirm').text();
    var link            = $(el).attr('href');

    Swal.fire({
        text: body,
        showCancelButton: true,
        confirmButtonColor: colour ? colour : "#59c4bc",
        confirmButtonText: confirmLabel,
        closeOnConfirm: false
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = link
        }
    });

}

function rotate_image(){

    var conWidth    = cropper.containerData.width;
    var conHeight   = cropper.containerData.height;
    var imgWidth    = cropper.canvas.clientWidth;
    var imgHeight   = cropper.canvas.clientHeight;

    var newImgWidth     = 0;
    var newImgHeight    = 0;
    var newLeft         = 0;
    var newTop          = 0;

    if (imgWidth > imgHeight) {
        newImgHeight    = conHeight;
        newImgWidth     = null;

        //Make sure new image size still fits container
        checkWidth     = newImgHeight * (imgHeight/imgWidth);
        if (checkWidth > conWidth) {
            newImgHeight    = null;
            newImgWidth     = conWidth;
        }
    }
    else {
        newImgHeight    = null;
        newImgWidth     = conWidth;

        //Make sure new image size still fits container
        checkHeight     = newImgWidth * (imgWidth/imgHeight);
        if (checkHeight > conHeight) {
            newImgHeight    = conHeight;
            newImgWidth     = null;
        }
    }

    if (newImgHeight) {
        //Position center horizontally
        newLeft = (conWidth/2) - (newImgHeight * (imgHeight/imgWidth))/2;
    }
    else {
        //Position center vertically
        newTop = (conHeight/2) - (newImgWidth * (imgWidth/imgHeight))/2;
    }

    cropper = cropper.rotate(90);
    cropper = cropper.moveTo(0,0);
    cropper = cropper.setCanvasData({
        left: newLeft,
        top: newTop,
        height: newImgHeight,
        width: newImgWidth
    });

}

if (!HTMLCanvasElement.prototype.toBlob) {
    Object.defineProperty(HTMLCanvasElement.prototype, 'toBlob', {
        value: function (callback, type, quality) {
        var canvas = this;
        setTimeout(function() {

            var binStr = atob( canvas.toDataURL(type, quality).split(',')[1] ),
                len = binStr.length,
                arr = new Uint8Array(len);

            for (var i = 0; i < len; i++ ) {
            arr[i] = binStr.charCodeAt(i);
            }

            callback( new Blob( [arr], {type: type || 'image/png'} ) );

        });
        }
    });
}

function show_image_popup(html) {
    $('.image-popup .image-popup-content').html(html);
    $('.image-popup').addClass('open');
}

function close_image_popup() {
    $('.image-popup').removeClass('open');
    $('.image-popup .image-popup-content').html('');
}

function ajax_submit_form(el, view, id, action="save_and_close") {
    var form = $(el).closest('form');
    form.find('.ecms-field').attr('data-error', '');
    form.parsley().reset();
    var data = form.serialize();

    // Determine listing or single page type
    var pageType = 'listing';
    if ($(el).closest('.ecms-single').length) {
        pageType = 'single';
    }

    if(view == 'update') {
        show_loader();
        ajax_post('update/'+id, data, function (d) {
            if (d['success']) {
                if (typeof form_update_success === 'function') {
                    var params = new URLSearchParams(data);
                    var unserializedData = Object.fromEntries(params.entries());

                    form_update_success(id, unserializedData, d.extra);
                }

                if (pageType == 'listing') {
                    load_results_init();
                    reload_filters();
                    if (action == 'save_and_new') {
                        ajax_get('ajax_quick_manage/0', '', function (d) {
                            if (d) {
                                open_qm(d);
                                hide_loader();
                            }
                        });
                    }
                    else if (action == 'save_and_stay') {
                        ajax_get('ajax_quick_manage/' + id, '', function (d) {
                            if (d) {
                                open_qm(d);
                                hide_loader();
                            }
                        });
                    }
                    else {
                        close_qm();
                    }
                }
                else {
                    hide_loader();
                }
                flash_notification(d['flasherbody'], 'success');
            }
            else {
                hide_loader();
                show_error(d['error']);

                //Show field errors
                if (d.hasOwnProperty('fields')) {
                    for (var f in d.fields) {
                        form_error(form, f, d.fields[f]);
                    }
                }
            }
            
        });
    } else {
        show_loader();
        ajax_post('create', data, function (d) {
            if (d['success']) {
                if (typeof form_create_success === 'function') {
                    var params = new URLSearchParams(data);
                    var unserializedData = Object.fromEntries(params.entries());

                    form_create_success(unserializedData, d.extra);
                }

                if (pageType == 'listing') {
                    load_results_init();
                    reload_filters();
                    if (action == 'save_and_new') {
                        ajax_get('ajax_quick_manage/0', '', function (d) {
                            if (d) {
                                open_qm(d);
                                hide_loader();
                            }
                        });
                    }
                    else if (action == 'save_and_stay') {
                        ajax_get('ajax_quick_manage/' + id, '', function (d) {
                            if (d) {
                                open_qm(d);
                                hide_loader();
                            }
                        });
                    }
                    else {
                        close_qm();
                    }
                }
                else {
                    hide_loader();
                }
                flash_notification(d['flasherbody'], 'success');
            }
            else {
                hide_loader();
                show_error(d['error']);

                //Show field errors
                if (d.hasOwnProperty('fields')) {
                    for (var f in d.fields) {
                        form_error(form, f, d.fields[f]);
                    }
                }
            }
            
        });
    }
}

function form_error(form, field, error) {
    clear_form_error(form, field)
    form.find('.ecms-field[rel="'+field+'"]').attr('data-error', error);
    form.find('.ecms-field[rel="'+field+'"] .form-control').parsley().addError('custom-error', {message: error});
}

function clear_form_error(form, field) {
    form.find('.ecms-field[rel="'+field+'"]').attr('data-error', '');
    form.find('.ecms-field[rel="'+field+'"] .form-control').parsley().removeError('custom-error');
}

//Function to replace the original clone function which doesn't copy values but this one does.
(function (original) {
    jQuery.fn.clone = function () {
      var result           = original.apply(this, arguments),
          my_textareas     = this.find('textarea').add(this.filter('textarea')),
          result_textareas = result.find('textarea').add(result.filter('textarea')),
          my_selects       = this.find('select').add(this.filter('select')),
          result_selects   = result.find('select').add(result.filter('select'));

      for (var i = 0, l = my_textareas.length; i < l; ++i) $(result_textareas[i]).val($(my_textareas[i]).val());
      for (var i = 0, l = my_selects.length;   i < l; ++i) result_selects[i].selectedIndex = my_selects[i].selectedIndex;

      return result;
    };
}) (jQuery.fn.clone);

$.fn.exchangePositionWith = function(selector) {
    var other = $(selector);
    this.after(other.clone());
    other.after(this).remove();
};

function change_position(id, position, num) {
    ajax_post('ajax_change_position', {
        id: id,
        position: position,
        num: num
    }, function(d) {
        load_results_init();
    })
}

function flash_notification(message, type="info", position="bottom-right", timeout=3000) {
    toastr.options.timeOut          = timeout;
    toastr.options.closeButton      = true;
    toastr.options.positionClass    = 'toast-'+position;
    toastr[type](message);    
}

function setup_dz_uploaders() {
    $('.dz-uploader').each(function(){
        setup_dz_uploader($(this).attr('rel'));
    });
}

function setup_dz_uploader(dzfield, dzOptionsField=false) {
    let optionsField = (dzOptionsField) ? dzOptionsField : dzfield;
    let settings = (valid_property_tree(ecmsFieldOptions, optionsField, 'settings')) ? ecmsFieldOptions[optionsField].settings : {};

    //Setup dropzone
    $('.dz-uploader[rel="'+dzfield+'"]').dropzone(Object.assign({
        url: dynamicPath+'/ajax_dz_upload',
        parallelUploads: 1,
        previewTemplate: $('.dz-thumb-template').html(),
        previewsContainer: '.dz-uploader[rel="'+dzfield+'"] .file-thumbs',
        init: function() {

            this.idCounter = 1;
            this.deleted = [];

            //If single file/image uploader then change control to single uploader
            if ($('.dz-uploader[rel="'+dzfield+'"]').is('.dz-single-file, .dz-single-image')) {
                this.hiddenFileInput.removeAttribute('multiple');
            }

            dz_repopulate(dzfield, $('.dz-thumb-template'), optionsField);

            //When file has been added
            this.on('addedfile', function(file) {

                let me = this;

                setTimeout(function(){

                    //Add class to indicate files are in the uploader
                    $(me.element).addClass('has-files');
                        
                    //Remove file 
                    if ($('.dz-uploader[rel="'+dzfield+'"]').is('.dz-single-file, .dz-single-image') && me.files.length > 1) {
                        me.removeFile(me.files[0]);
                    }

                    //Give position number to image
                    var pos = $(file.previewElement).prev('.dz-preview').length ? $(file.previewElement).prev('.dz-preview').attr('data-position')/1 + 1 : 1;
                    $(file.previewElement).attr('data-position', pos);

                    //Give a unique ID to the field
                    $(file.previewElement).attr('data-id', 'new_'+me.idCounter);
                    me.idCounter++;
                }, 10);


            });

            //When file has been uploaded successfully
            this.on('success', function(file, response) {

                var imagePos = $(file.previewElement).attr('data-position');
                var imageID = $(file.previewElement).attr('data-id');
                let uploader = $('.dz-uploader[rel="'+dzfield+'"]');

                if (this.deleted.indexOf(imageID) != -1 ) {
                    return false;
                }

                //create hidden fields
                var dataFields = '<input class="dz-file-data dz-file" rel="'+imageID+'" type="hidden" name="'+dzfield+'[id_'+imageID+'][file]" value="'+response.file_name+'" />' +
                    '<input class="dz-file-data dz-path" rel="'+imageID+'" type="hidden" name="'+dzfield+'[id_'+imageID+'][path]" value="'+response.upload_path+'" />' +
                    '<input class="dz-file-data dz-file-props" rel="'+imageID+'" type="hidden" name="'+dzfield+'[id_'+imageID+'][fields]['+dzfield+'_type]" data-field="'+dzfield+'_type" value="'+response.type+'" />' +
                    '<input class="dz-file-data dz-file-props" rel="'+imageID+'" type="hidden" name="'+dzfield+'[id_'+imageID+'][fields]['+dzfield+'_ext]" data-field="'+dzfield+'_ext" value="'+response.ext+'" />' +
                    '<input class="dz-file-data dz-file-props" rel="'+imageID+'" type="hidden" name="'+dzfield+'[id_'+imageID+'][fields]['+dzfield+'_size]" data-field="'+dzfield+'_size" value="'+response.size+'" />' +
                    '<input class="dz-file-data dz-fields" rel="'+imageID+'" type="hidden" name="'+dzfield+'[id_'+imageID+'][fields][caption]" data-field="caption" value="" />' +
                    '<input class="dz-file-data dz-fields dz-position" rel="'+imageID+'" type="hidden" name="'+dzfield+'[id_'+imageID+'][position]" data-field="position" value="'+imagePos+'" />' +
                    '<input class="dz-file-data dz-status" rel="'+imageID+'" type="hidden" name="'+dzfield+'[id_'+imageID+'][status]" data-field="status" value="new" />'
                ;

                
                if (uploader.is('.dz-single-image, .dz-single-file')) {
                    $(this.element).find('.file-data').html(dataFields);
                }
                else {
                    $(this.element).find('.file-data').append(dataFields);
                }

                setTimeout(function() {
                    $(file.previewElement).removeClass('dz-success');
                }, 3000);
            });

        },
        transformFile: function(file, done) {   // Added a check for skip cropper class to remove the cropper functionality
            
            //Don't transform file uploaders
            if ($(this.element).is('.dz-multi-file, .dz-single-file')) {
                done(file);
                return;
            }

            // Check for "skip-cropper" class
            if ($(this.element).hasClass('skip-cropper')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = new Image();
                    img.onload = function() {
                        // Calculate the aspect ratio
                        var aspectRatio = img.width / img.height;
                        var thumbnailWidth = this.options.thumbnailWidth;
                        var thumbnailHeight = Math.round(thumbnailWidth / aspectRatio); // Adjust height based on actual aspect ratio
                        
                        // Directly create a thumbnail with actual aspect ratio
                        this.createThumbnail(
                            file,
                            thumbnailWidth,
                            thumbnailHeight,
                            this.options.thumbnailMethod,
                            false, 
                            function(dataURL) {
                                // Update the Dropzone file thumbnail
                                this.emit('thumbnail', file, dataURL);
                                
                                // Log the file (optional)
                                console.log('file is', file);
                                
                                // Apply the thumbnail as background image
                                $(file.previewElement).find('.dz-image').css('background-image', 'url('+dataURL+')');
                                
                                // Continue with the upload process
                                done(file);
                            }.bind(this) // Ensure 'this' refers to the Dropzone instance
                        );
                    }.bind(this); // Bind the onload function to ensure 'this' refers to the Dropzone instance
                    
                    img.src = e.target.result; // Set the source of the image to the Data URL
                }.bind(this); // Bind the FileReader onload to ensure 'this' refers to the Dropzone instance
                
                reader.readAsDataURL(file); // Read the file as a Data URL
                return; // Exit the function early
            }

            
            var myDropZone = this;
            var imagePopup = $('.image-popup-template').clone().attr('class', 'image-popup');

            imagePopup.on('click','.finish-image-editing', function() {

                var canvas = cropper.getCroppedCanvas({
                    fillColor: 'transparent',
                    imageSmoothingEnabled: false,
                    imageSmoothingQuality: 'high',
                });

                var ext = file.name.split('.').pop().toLowerCase();

                var mime = '';
                if (ext == 'jpg' || ext == 'jpeg' || ext == 'jfif') {
                    mime = 'image/jpeg';
                } else if(ext =='webp') {
                    mime = 'image/webp';
                } else {
                    mime = 'image/png';
                }

                //Create blob
                canvas.toBlob(function(blob) {

                    var ratio = 1
                    if (valid_property_tree(ecmsFieldOptions, optionsField, 'cropper', 'aspectRatio')) {
                        ratio = ecmsFieldOptions[optionsField].cropper.aspectRatio;
                    }

                    // Create a new Dropzone file thumbnail
                    myDropZone.createThumbnail(
                        blob,
                        myDropZone.options.thumbnailWidth,
                        myDropZone.options.thumbnailWidth / ratio,
                        myDropZone.options.thumbnailMethod,
                        false, 
                        function(dataURL) {
                        
                            // Update the Dropzone file thumbnail
                            myDropZone.emit('thumbnail', file, dataURL);        
                            
                            $(file.previewElement).find('.dz-image').css('background-image', 'url('+dataURL+')');
                            
                            // Return the file to Dropzone
                            done(blob);     
                            
                            //Close popup
                            imagePopup.remove();
                        }
                    );
                }, mime, 1.00);

                
            });

            imagePopup.on('click','.close-image-popup', function() {
                let uploader = $(file.previewElement).closest('.dz-uploader');
                done(file);

                //Mark files as deleted
                myDropZone.deleted.push($(file.previewElement).attr('data-id'));

                //Remove preview
                $(file.previewElement).remove();

                //Show no-image placeholder
                if (!uploader.find('.dz-preview').length) {
                    uploader.removeClass('has-files');
                }

                //Close popup
                imagePopup.remove();
            });

            //Rotate image
            imagePopup.on('click', '.rotate-image', function() {
                dz_rotate_image(cropper);
            });
            
            var cropper;
            ImageTools.resize(file, {
                width: 5000, // maximum width
                height: 5000 // maximum height
            }, function(blob, didItResize) {
                imagePopup.appendTo('body');
                
                show_image_popup('<img class="cropper-image" src="'+window.URL.createObjectURL(blob)+'" />');

                let cropperOptions = {
                    aspectRatio: 1,
                    responsive: false,
                    rotatable: true,
                    zoomable: false,
                    zoomOnTouch: false,
                    zoomOnWheel: false,
                    autoCropArea: 1
                }

                //Overide cropper options
                if (ecmsFieldOptions.hasOwnProperty(optionsField) && ecmsFieldOptions[optionsField].hasOwnProperty('cropper')) {
                    for (let o in ecmsFieldOptions[optionsField].cropper) {
                        cropperOptions[o] = ecmsFieldOptions[optionsField].cropper[o];
                    }
                }

                $('.cropper-image').cropper(cropperOptions);
                
                cropper = $('.cropper-image').data('cropper');

            });
        }
    }, settings));

    $('.dz-uploader[rel="'+dzfield+'"]').on('click', '.dz-pos-prev', function() {
        var thumb = $(this).closest('.dz-preview');

        //You can't go more back than the first position.
        if (!thumb.prev().length) {
            return false;
        }

        //Exchange image positions
        thumb.exchangePositionWith(thumb.prev());

        //Adjust position numbering
        dz_reset_positioning($(this).closest('.dz-uploader'));
        
    });

    $('.dz-uploader[rel="'+dzfield+'"]').on('click', '.dz-pos-next', function() {
        var thumb = $(this).closest('.dz-preview');

        //You can't go more forward than the last position.
        if (!thumb.next().length) {
            return false;
        }

        //Exchange image positions
        thumb.exchangePositionWith(thumb.next());

        //Adjust position numbering
        dz_reset_positioning($(this).closest('.dz-uploader'));
    });

    $('.dz-uploader[rel="'+dzfield+'"]').on('click', '.dz-remove', function() {
        var imageID = $(this).closest('.dz-preview').attr('data-id');
        var uploader = $(this).closest('.dz-uploader');
        
        if (imageID.match(/^new/)?.length) {
            //Remove image data of new images
            uploader.find('.dz-file-data[rel="'+imageID+'"]').remove();
        }
        else {
            //Mark saved images as removed
            uploader.find('.dz-status[rel="'+imageID+'"]').val('removed');
        }
        
        //Remove image from uploader
        $(this).closest('.dz-preview').remove();

        if (!uploader.find('.dz-preview').length) {
            uploader.removeClass('has-files');
        }

        //Adjust position numbering
        dz_reset_positioning(uploader);
    });

    //Download trigger for single image uploader
    $('.dz-uploader[rel="'+dzfield+'"].dz-single-image').on('click', '.dz-download', function() {
        if (uploaderData.hasOwnProperty(dzfield)) {
            let downloadSize = (valid_property_tree(ecmsFieldOptions, optionsField, 'downloadSize')) ? ecmsFieldOptions[optionsField].downloadSize : 350;
            let downloadFileName = uploaderData[dzfield][0].path.match(/.*\/(.*)$/);

            $('body').append('<a class="temp-uploader-download-link" href="'+siteURL+'images/'+downloadSize+'/'+uploaderData[dzfield][0].path+'" download="'+downloadFileName[1]+'" /></a>')
            $('.temp-uploader-download-link')[0].dispatchEvent(new MouseEvent('click'));
            $('.temp-uploader-download-link').remove();
        }
    });

    //Download trigger for single file uploader
    $('.dz-uploader[rel="'+dzfield+'"].dz-single-file').on('click', '.dz-download', function() {
        if (uploaderData.hasOwnProperty(dzfield)) {
            window.location.href = siteURL+'download/'+uploaderData[dzfield][0].path;
        }
    });

    //Download trigger for multi file uploader
    $('.dz-uploader[rel="'+dzfield+'"].dz-multi-file').on('click', '.dz-download', function() {
        let fileID = $(this).closest('.dz-preview').attr('data-id');
        if (uploaderData.hasOwnProperty(dzfield)) {
            for (let f of uploaderData[dzfield]) {
                if (f.id == fileID) {
                    window.location.href = siteURL+'download/'+f.path;
                    break;
                }
            }
        }
    });

    //Download trigger for multi image uploader
    $('.dz-uploader[rel="'+dzfield+'"].dz-multi-image').on('click', '.dz-download', function() {
        let imageID = $(this).closest('.dz-preview').attr('data-id');
        if (uploaderData.hasOwnProperty(dzfield)) {
            for (let i of uploaderData[dzfield]) {
                if (i.id == imageID) {
                    let downloadSize = (valid_property_tree(ecmsFieldOptions, optionsField, 'downloadSize')) ? ecmsFieldOptions[optionsField].downloadSize : 350;
                    let downloadFileName = i.path.match(/.*\/(.*)$/);
                    $('body').append('<a class="temp-uploader-download-link" href="'+siteURL+'images/'+downloadSize+'/'+i.path+'" download="'+downloadFileName[1]+'" /></a>');
                    $('.temp-uploader-download-link')[0].dispatchEvent(new MouseEvent('click'));
                    $('.temp-uploader-download-link').remove();
                    break;
                }
            }
        }
    });

    //Add caption
    $('.dz-uploader[rel="'+dzfield+'"]').on('click', '.dz-caption', function() {
        let fileID          = $(this).closest('.dz-preview').attr('data-id');
        let uploader        = $(this).closest('.dz-uploader');
        let captionField    = $('.image-caption-template').clone();
        let currentValue    = uploader.find('.file-data .dz-fields[rel="'+fileID+'"][data-field="caption"]').val();

        //Repopulate field
        captionField.find('.image-caption-field').attr('value', currentValue);
        captionField.find('.image-caption-field').attr('autofocus', 'autofocus');
        captionField.find('.image-caption-field').attr('onfocus', 'var temp_value=this.value; this.value=\'\'; this.value=temp_value');

        custom_popup('Edit Caption', captionField.html(), [{
            buttonText: 'Cancel',
            buttonClass: 'btn-light'
        }, {
            buttonText: 'Save',
            buttonFunction: (function() {
                let value = $('.custom-popup-body .image-caption-field').val();
                let currentStatus = uploader.find('.file-data .dz-status[rel="'+fileID+'"][data-field="status"]').val();

                uploader.find('.file-data .dz-fields[rel="'+fileID+'"][data-field="caption"]').val(value);

                if (currentStatus == 'nochange') {
                    uploader.find('.file-data .dz-status[rel="'+fileID+'"][data-field="status"]').val('updated');
                }

                close_popup();
            })
        }]);

        //Focus on field when popup is open
        $('.custom-popup-body .image-caption-field').focus();
    });



}

function dz_rotate_image(cropper){

    var conWidth    = cropper.containerData.width;
    var conHeight   = cropper.containerData.height;
    var imgWidth    = cropper.canvas.clientWidth;
    var imgHeight   = cropper.canvas.clientHeight;

    var newImgWidth     = 0;
    var newImgHeight    = 0;
    var newLeft         = 0;
    var newTop          = 0;

    if (imgWidth > imgHeight) {
        newImgHeight    = conHeight;
        newImgWidth     = null;

        //Make sure new image size still fits container
        checkWidth     = newImgHeight * (imgHeight/imgWidth);
        if (checkWidth > conWidth) {
            newImgHeight    = null;
            newImgWidth     = conWidth;
        }
    }
    else {
        newImgHeight    = null;
        newImgWidth     = conWidth;

        //Make sure new image size still fits container
        checkHeight     = newImgWidth * (imgWidth/imgHeight);
        if (checkHeight > conHeight) {
            newImgHeight    = conHeight;
            newImgWidth     = null;
        }
    }

    if (newImgHeight) {
        //Position center horizontally
        newLeft = (conWidth/2) - (newImgHeight * (imgHeight/imgWidth))/2;
    }
    else {
        //Position center vertically
        newTop = (conHeight/2) - (newImgWidth * (imgWidth/imgHeight))/2;
    }

    cropper = cropper.rotate(90);
    cropper = cropper.moveTo(0,0);
    cropper = cropper.setCanvasData({
        left: newLeft,
        top: newTop,
        height: newImgHeight,
        width: newImgWidth
    });

}

function dz_reset_positioning(uploader) {
    var p = 0;
    uploader.find('.dz-preview').each(function(){
        p++;
        $(this).attr('data-position', p);

        //Update position of data field
        var imageID = $(this).attr('data-id');
        uploader.find('.dz-position[rel="'+imageID+'"]').val(p);
        
        //Update status of image
        var currentStatus = uploader.find('.dz-status[rel="'+imageID+'"]').val();
        uploader.find('.dz-status[rel="'+imageID+'"]').val(currentStatus == 'nochange' ? 'updated' : currentStatus);

    });
}

function dz_repopulate(field, previewTemplate, optionsField) {
    if (!uploaderData.hasOwnProperty(field) || !uploaderData[field].length) {
        return;
    }

    //Remove no-image placeholder
    $('.dz-uploader[rel="'+field+'"]').addClass('has-files');

    let thumbSize = valid_property_tree(ecmsFieldOptions, optionsField, 'thumbSize') ? ecmsFieldOptions[optionsField].thumbSize : 350;
    
    let preview;
    for (let file of uploaderData[field]) {
        let ffields     = file.hasOwnProperty('fields') ? file.fields : {};
        let fsizeRaw    = ffields.hasOwnProperty(field+'_size') ? file[field+'_size'] : 0
        let fsize       = pretty_filesize(fsizeRaw);
        let fpos        = file.hasOwnProperty('position') ? file.position : 0;

        //Add field data
        for (let f in ffields) {
            $('<input />', {
                'type': 'hidden',
                // class: 'dz-file-data dz-fields '+(fpos ? 'dz-position' : ''),
                class: 'dz-file-data dz-fields',
                rel: file.id,
                'data-field': f,
                name:  field+'['+file.id+'][fields]['+f+']',
                value: ffields[f]
            }).appendTo('.dz-uploader[rel="'+field+'"] .file-data');
        }

        //Add position
        $('<input />', {
            'type': 'hidden',
            class: 'dz-file-data dz-fields dz-position',
            rel: file.id,
            'data-field': 'position',
            name:  field+'['+file.id+'][position]',
            value: fpos
        }).appendTo('.dz-uploader[rel="'+field+'"] .file-data');

        //Current status of the file
        $('<input />', {
            'type': 'hidden',
            class: 'dz-file-data dz-status',
            rel: file.id,
            'data-field': 'status',
            name:  field+'['+file.id+'][status]',
            value: 'nochange'
        }).prependTo('.dz-uploader[rel="'+field+'"] .file-data');

        //Create thumb
        preview = previewTemplate.clone();
        preview.find('.dz-preview').addClass('dz-complete').attr('data-id', file.id).attr('data-position', fpos);
        preview.find('.dz-size span').html('<strong>'+fsize.number+'</strong> '+fsize.unit);
        preview.find('.dz-filename span').html(file.file_name);
        preview.find('.dz-image img').attr('src', siteURL + 'images/'+thumbSize+'/' + file.path);
        preview.find('.dz-image').css('background-image', 'url('+siteURL + 'images/'+thumbSize+'/' + file.path+')');

        //Add thumb to uploader
        $('.dz-uploader[rel="'+field+'"] .file-thumbs').append(preview.html());
    }
}

function pretty_filesize(filesize) {
    //Return value in bytes
    if (filesize < 1024) {
        return {
            number: filesize,
            unit: 'B'
        }
    }

    //Return value in kilobytes
    if (filesize/1024 < 1024) {
        return {
            number: (filesize/1024).toFixed(2),
            unit: 'KB'
        }
    }

    //Return value in megabytes
    if (filesize/1024/1024 < 1024) {
        return {
            number: (filesize/1024/1024).toFixed(2),
            unit: 'MB'
        }
    }

    //Return value in gigabytes
    return {
        number: (filesize/1024/1024/1024).toFixed(2),
        unit: 'GB'
    }

}

function valid_property_tree(obj, ...properties) {
    let propertyTree = $.extend(true,{},obj);
    for (let p of properties) {
        if (propertyTree === undefined || !propertyTree.hasOwnProperty(p)) {
            return false;
        }

        propertyTree = propertyTree[p];
    }

    return true;
}

function qm_next_tab(el) {
    const currentTab    = $('.qm-tabs-header li.active');
    const validate      = $('.qm-tabs').hasClass('validate-tabs');
    let nextTab         = "";

    //Don't continue if the button is disabled
    if ($(el).hasClass('disabled')) {
        return false;
    }

    if (currentTab.next('li').length) {
        nextTab = currentTab.next('li').attr('rel');
    }
    else {
        nextTab = currentTab.find('li:first-child').attr('rel');
    }

    if (validate) {
        if (valid_form(el, 'tab-' + currentTab.attr('rel'))) {
            qm_open_tab(nextTab);
        }
    }
    else {
        qm_open_tab(nextTab);
    }
    
}

function qm_prev_tab(el) {
    const currentTab    = $('.qm-tabs-header li.active');
    let prevTab         = "";

    //Don't continue if the button is disabled
    if ($(el).hasClass('disabled')) {
        return false;
    }

    if (currentTab.prev('li').length) {
        prevTab = currentTab.prev('li').attr('rel');
    }
    else {
        prevTab = currentTab.find('li:last-child').attr('rel');
    }

    qm_open_tab(prevTab);
}

function qm_open_tab(tab, force=false) {
    //For any extra functionality that needs to happen before opening a tab
    let continueExecution = true;
    if (!force && typeof qm_open_tab_extra === "function") {
        continueExecution = qm_open_tab_extra(tab);
    }

    //Open tab unless stopped by above function
    if (continueExecution) {

        //If tab has data-href then redirect to that page
        if ($('.qm-tabs-header li[rel="'+tab+'"]').attr('data-href')) {
            window.location.href = $('.qm-tabs-header li[rel="'+tab+'"]').attr('data-href');
            return false;
        }

        //Set active tab container
        $('.qm-tabs-tab').removeClass('active');
        $('.qm-tabs-tab[rel="'+tab+'"]').addClass('active');
    
        //Set active tab button0
        $('.qm-tabs-header li').removeClass('active');
        $('.qm-tabs-header li[rel="'+tab+'"]').addClass('active');

        //Show or hide buttons
        if ($('.qm-tabs-header li').length > 1) {
            var aTab = $('.qm-tabs-header li.active')
            if (aTab.is(':first-child')) {
                $('.qm-btn-prev').hide();
                $('.qm-btn-next').show();
                $('.qm-btn-save').hide();
            }
            else if (aTab.is(':last-child')) {
                $('.qm-btn-prev').show();
                $('.qm-btn-next').hide();
                $('.qm-btn-save').show();
            }
            else {
                $('.qm-btn-prev').show();
                $('.qm-btn-next').show();
                $('.qm-btn-save').hide();
            }

        }
        else {
            $('.qm-btn-prev').hide();
            $('.qm-btn-next').hide();
            $('.qm-btn-save').show();
        }

        //Execute extra funtionality after tab has opened
        if (typeof qm_open_tab_success === "function") {
            qm_open_tab_success(tab);
        }
    }
}

function open_qm(html, restore_original_size=true, clip_body=true) {
    clip_body && $('body').addClass('qm-open');

    reset_qm();

    if (restore_original_size) {
        $('.quick-manage-container').attr('data-size', $('.quick-manage-container').attr('data-original-size'));
    }

    //Open quick manage slide out
    $('.quick-manage-container').addClass('open').html(html);

    // Check if it has tabs
    if ($('.quick-manage-form-container').hasClass('qm-tabs')) {
        prepare_tabs();
    }

    // Check if dynamic fields are present
    if ($('.dynamic-field-container').length) {
        setup_dynamic_template();
    }

    //Correct height of form container based on content passed in qm header
    let qmHeaderHeight = $('.quick-manage-container .quick-manage-heading').outerHeight()/1;
    let qmTabHeaderHeight = $('.quick-manage-container .qm-tabs-header').outerHeight()/1;

    $('.quick-manage-container .form-field-container').css('height', 'calc(100% - '+(qmHeaderHeight+qmTabHeaderHeight-50)+'px)');

    $(document).trigger('quickManageOpened');
}

function close_qm() {
    $('body').removeClass('qm-open');
    $('.quick-manage-container').removeClass('open').html('');
    $(document).find('.dz-hidden-input').remove();
    $(document).trigger('quickManageClosed');
}

function reset_qm() {
    $('.quick-manage-container').html('');
    $(document).find('.dz-hidden-input').remove();
}

function prepare_tabs() {
    var firstTab = $('.qm-tabs-header li:first-child').attr('rel');

    //Prepare field groups
    $('.qm-tabs-tab').each(function() {
        var tab = $(this).attr('rel');

        $(this).find('input').attr('data-parsley-group', 'tab-' + tab);
        $(this).find('textarea').attr('data-parsley-group', 'tab-' + tab);
        $(this).find('select').attr('data-parsley-group', 'tab-' + tab);
    });

    qm_open_tab(firstTab);
}

function custom_popup(heading, body, buttons=[]) {
    var html = $('<div class="custom-popup-body">'+body+'</div>');

    if (buttons.length) {
        var buttonsHTML = $('<div class="custom-popup-buttons"></div>');
        for (var b of buttons) {
            //Check if class is included
            var buttonClass     = (b.hasOwnProperty('buttonClass')) ? b.buttonClass : 'btn-secondary';
            var buttonText      = (b.hasOwnProperty('buttonText')) ? b.buttonText : 'Ok';
            
            var button = $('<button class="btn '+buttonClass+'">'+buttonText+'</button>');

            if (b.hasOwnProperty('buttonFunction')) {
                button.on('click', b.buttonFunction);
            }
            else {
                button.on('click', function(){
                    Swal.close();
                });
            }

            buttonsHTML.append(button);
        }

        html.append(buttonsHTML);
    }

    Swal.fire({
        title: heading,
        html: '[Popup Content]',
        showConfirmButton: false,
        showCancelButton: false
    });

    var popupHTML = Swal.getHtmlContainer();

    $(popupHTML).html(html);

    $('.swal2-popup').addClass('custom-swal-popup');
}

function close_popup() {
    Swal.close();
}

function load_custom_parsley_validators() {

    //Validate that the minimum number of files required
    window.Parsley.addValidator('minFiles', {
        validateString: function(value, requirement, instance) {
            let realValue = $(instance.element).closest('.ecms-field').find('.dz-complete:not(.dz-error)').length;
            return realValue >= requirement;
        },
        requirementType: 'string',
        messages: {
            en: 'You need to upload at least %s files',
        }
    });

    //Validate that a file has been uploaded
    window.Parsley.addValidator('fileRequired', {
        validateString: function(value, requirement, instance) {
            let realValue = $(instance.element).closest('.ecms-field').find('.dz-complete:not(.dz-error)').length;
            return realValue >= requirement;
        },
        requirementType: 'string',
        messages: {
            en: 'An file is required'
        }
    });

    //Validate that the maximum number of files allowed
    window.Parsley.addValidator('maxFiles', {
        validateString: function(value, requirement, instance) {
            let realValue = $(instance.element).closest('.ecms-field').find('.dz-complete:not(.dz-error)').length;
            return (requirement && realValue <= requirement);
        },
        requirementType: 'string',
        messages: {
            en: 'You are only allowed to upload %s file(s)',
        }
    });

    //Validate that there are no file errors
    window.Parsley.addValidator('checkFiles', {
        validateString: function(value, requirement, instance) {
            let realValue = $(instance.element).closest('.ecms-field').find('.dz-error').length;
            return !realValue;
        },
        requirementType: 'string',
        messages: {
            en: 'Please check and remove files with errors',
        }
    });

    // Validate ckeditor
    window.Parsley.addValidator('checkRequiredCkeditor', {
        validateString: function(value, requirement, instance) {
            let realValue = $(instance.element).closest('.ecms-field').find('.dz-error').length;
            return false;
        },
        requirementType: 'string',
        messages: {
            en: 'Please check and remove files with errors',
        }
    });

    //Validate that the minimum number of images required
    window.Parsley.addValidator('minImages', {
        validateString: function(value, requirement, instance) {
            let realValue = $(instance.element).closest('.ecms-field').find('.dz-complete:not(.dz-error)').length;
            return realValue >= requirement;
        },
        requirementType: 'string',
        messages: {
            en: 'You need to upload at least %s images'
        }
    });

    //Validate that an image has been uploaded
    window.Parsley.addValidator('imageRequired', {
        validateString: function(value, requirement, instance) {
            let realValue = $(instance.element).closest('.ecms-field').find('.dz-complete:not(.dz-error)').length;
            return realValue >= requirement;
        },
        requirementType: 'string',
        messages: {
            en: 'An image is required'
        }
    });
    
}

function elog(label, value='') {
    if (value === '') {
        console.log('%c'+label, 'color: #0085cf');
        return;
    }

    if (Array.isArray(value)) {
        console.log('%c'+label, 'color: #0085cf', $.extend(true, [], value));
    } 
    else if (typeof value === 'object') {
        console.log('%c'+label, 'color: #0085cf', $.extend(true, {}, value));
    }
    else {
        console.log('%c'+label, 'color: #0085cf', value);
    }

}

function custom_qm(path, size=1) {
    //Update qm container size
    $('.quick-manage-container').attr('data-size', size);

    //Load content for container
    show_loader();
    ajax_get(path, {}, function (d) {
        hide_loader();
        if (d) {
            //Open container
            open_qm(d);
        }
    });
}

function toggle_dark_mode() {
    let currentMode = $('body').attr('data-theme');

    if (currentMode == 'dark') {
        //Activate light mode
        $('body').attr('data-theme', 'light');
        localStorage.setItem('theme', 'light');
    }
    else {
        //Activate dark mode
        $('body').attr('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    }
}

function df_con(con) {
    return $('.dynamic-field-container[rel="'+con+'"]');
}

function df_row(con, rowID) {
    return $('.dynamic-field-container[rel="'+con+'"] .dynamic-field-row[rel="'+rowID+'"]');
}

function valid_form(el, group=null) {
    const form            = $(el).closest('form');
    const parsleyForm     = form.parsley();
    const parsleyOptions  = group ? { group: group } : {};

    parsleyForm.validate(parsleyOptions);

    //If tabbing system is used then highlight tabs with errors
    if (form.closest('.quick-manage-form-container').hasClass('qm-tabs')) {
        $('.qm-tabs-header li').each(function() {
            const tab = $(this).attr('rel');

            if (document.querySelector('.qm-tabs-tab[rel="'+tab+'"] .parsley-error')) {
                $(this).addClass('error');
            }
            else {
                $(this).removeClass('error');
            }
        });
    }

    //If form isn't valid then show errors and popup
    if (!parsleyForm.isValid(parsleyOptions)) {

        // Gather fields with errors
        const errorFields = form.find('.parsley-error').map(function() {
            const field     = $(this).closest('.ecms-field');
            const label     = field.find('label').html().trim();
            const errors    = field.find('.parsley-errors-list li').map(function() {
                return $(this).html();
            }).get();

            return `${errors?.join(', ')}`;
        }).get();

        // Show the popup
        let errorHTML = '<strong>The following error(s) occurred:</strong><br/>{errors}<br/><br/>Please check the field(s) highlighted in red.';
        errorHTML = errorHTML.replace('{errors}', errorFields.join('<br/>'));
        show_error(errorHTML);

        return false;
    } else {
       return true;
    }
}

function save_form(el, action="save_and_close") {
    let continueExecution = true;

    //For if you want to do something extra or overide the default save_form behaviour.
    if (typeof save_form_extra === "function") {
        continueExecution = save_form_extra(el);
    }
    
    if (continueExecution && valid_form(el)) {
        const form  = $(el).closest('form');
        const id    = form.find('input[name="id"]').val();
        const view  = id/1 ? 'update' : 'create';

        ajax_submit_form(el, view, id, action);
    }
}

function reload_filters() {
    $('.listing-filters').addClass('loading');
    ajax_get('ajax_get_filters', {}, function(d) {
        if (d.success) {
            $('.listing-filters').html(d.html);
        }
        else {
            console.log('Could not load filters.');
        }
        $('.listing-filters').removeClass('loading');
    })
}

function init_menu_tooltips() {
    if($('[data-toggle="menu"]').length > 0) {
		$('[data-toggle="menu"]').tooltip({
			trigger: 'hover',
            template: `
                <div class="tooltip menu-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>
            `
		});
	}
}



// Define base_url if not already defined
if (typeof base_url === 'undefined') {
    // Try to get it from meta tag or define it
    var base_url = window.location.protocol + '//' + window.location.host + '/';
    
    // If your site is in a subdirectory, adjust accordingly
    var pathArray = window.location.pathname.split('/');
    if (pathArray.length > 2) {
        base_url += pathArray[1] + '/';
    }
}