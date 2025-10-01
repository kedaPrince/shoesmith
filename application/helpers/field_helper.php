<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function field_input($field, $value = "", $class = "required", $extra = array(), $type = "text") { 
    $fieldData = explode('|', $field);
    $fieldName = $fieldData[0];

    //Extra value out of row object if $value is object
    $value = is_object($value) && property_exists($value, $fieldName) ? $value->$fieldName : $value;

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_' . $fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Set field attributes
    $attr = array(
        'title'         => $label,
        'class'         => 'form-control '.$class, 
        'autocomplete'  => 'off',
    );
    $attr = array_merge($attr, $extra);

    //Check if we should show the label as placeholder
    if ($labelClass == 'placeholder') {
        $attr['placeholder'] = $label;
    }

    if($type == "email") {
        $attr['data-parsley-type'] = 'email';
        $attr['data-parsley-type-message'] = $label.' should be a valid email address';
    }

    if (!empty($attr['minlength']) && !empty($attr['maxlength']) ) {
        $attr['data-parsley-length-message'] = $label.' length is invalid. It should be between %s and %s characters long';
    } else if (!empty($attr['minlength'])) {
        $attr['data-parsley-minlength-message'] = $label.' is too short. It should have %s characters or more';
    } else if (!empty($attr['maxlength'])) {
        $attr['data-parsley-maxlength-message'] = $label.' is too long. It should have %s characters or fewer';
    }

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';

        $rMark = 'required-mark';
        $labelClass == 'placeholder' && $attr['placeholder'] .= '*';
    }

    $html = trim('
        <div class="ecms-field form-group" rel="'.$fieldName.'" data-error="'.attr_clean(form_error($fieldName)).'">
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            '.form_input($fieldName, set_value($fieldName, $value, FALSE), $attr, $type).'
        </div>
    ');

    return $html;
}

function field_time($field, $value = "", $class = "required", $extra = array(), $runScript = true) {
    $fieldData = explode('|', $field);
    $fieldName = $fieldData[0];
    $fieldID   = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());

    //Extra value out of row object if $value is object
    $value = is_object($value) && property_exists($value, $fieldName) ? $value->$fieldName : $value;

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_' . $fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Set field attributes
    $attr = array( 
        'title'                         => $label,
        'class'                         => 'form-control time24 '.$class ,
        'autocomplete'                  => 'off',
        'data-parsley-errors-container' => '.'.$fieldID.'_error',
    );
    $attr = array_merge($attr, $extra);

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';

        $rMark = 'required-mark';
    }

    $html = trim('
        <div class="ecms-field form-group" rel="'.$fieldName.'" data-error="'.attr_clean(form_error($fieldName)).'">
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="icon-clock"></i></span>
                </div>
                '.form_input($fieldName, set_value($fieldName, $value, FALSE), $attr, 'text').'
            </div>
            <span class="field-error-container '.$fieldID.'_error"></span>
        </div>
        '.($runScript ? '
        <script>
            $(".ecms-field[rel=\"'.$fieldName.'\"] .time24").inputmask("hh:mm", { placeholder: "__:__ _m", alias: "time24", hourFormat: "24" });
        </script>
        ' : '').'
    ');

    return $html;
}

function field_password($field, $class = "required", $extra = array()) {
    $fieldData = explode('|', $field);
    $fieldName = $fieldData[0];

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_' . $fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Set field attributes
    $attr = array(
        'title'         => $label,
        'class'         => 'form-control '.$class, 
        'autocomplete'  => 'off',
        'placeholder'   => $label, 
    );
    $attr = array_merge($attr, $extra);

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';

        $rMark = 'required-mark';
    }

    $html = trim('
        <div class="ecms-field form-group" rel="'.$fieldName.'" data-error="'.attr_clean(form_error($fieldName)).'">
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            '.form_password($fieldName, '', $attr).'
        </div>
    ');

    return $html;
}

function field_textarea($field, $value = "", $class = "required", $extra = array()) {
    $fieldData = explode('|', $field);
    $fieldName = $fieldData[0];

    //Extra value out of row object if $value is object
    $value = is_object($value) && property_exists($value, $fieldName) ? $value->$fieldName : $value;

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_' . $fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Set field attributes
    $attr = array(
        'title'         => $label,
        'class'         => 'form-control '.$class, 
        'autocomplete'  => 'off',
    );
    $attr = array_merge($attr, $extra);

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';

        $rMark = 'required-mark';
    }

    $html = trim('
        <div class="ecms-field form-group" data-error="'.attr_clean(form_error($fieldName)).'">
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            '.form_textarea($fieldName, set_value($fieldName, $value, FALSE), $attr).'
        </div>
    ');

    return $html;
}

function field_dropdown_noscript($field, $options = array(), $value = "", $class = "required", $extra = array(), $noValueLabel='') {
    return field_dropdown($field, $options, $value, $class, $extra, $noValueLabel, false);
}

function field_dropdown($field, $options = array(), $value = "", $class = "required", $extra = array(), $noValueLabel = '', $runScript = true) {
    $ci         =& get_instance();
    $fieldData  = explode('|', $field);
    $fieldName  = $fieldData[0];
    $fieldID    = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());

    //Extra value out of row object if $value is object
    $value = is_object($value) && property_exists($value, $fieldName) ? $value->$fieldName : $value;

    //Check if value is passed for submodule
    if ($value == '') {
        $submodules = $ci->session->submodules;
        if (!empty($submodules[$ci->pageName]->manageField) && $submodules[$ci->pageName]->manageField == $fieldName) {
            $value = $submodules[$ci->pageName]->id;
        }
    }

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_' . $fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Set field attributes
    $attr = array(
        'title'                         => $label,
        'class'                         => 'form-control custom-select ' . $class,
        'autocomplete'                  => 'off',
        'data-parsley-errors-container' => '.' . $fieldID . '_error',
        'data-parsley-class-handler'    => '.' . $fieldID . '_parsley',
    );
	$attr = array_merge($attr, $extra);

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';

        $rMark = 'required-mark';
    }

    //Set first option for the "no value" selection
    $firstOption = !empty($noValueLabel) ? $noValueLabel : 'Please select...';

    $html = trim('
        <div class="ecms-field form-group" rel="'.$fieldName.'">
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            <div class="c_dropdown '.$fieldID.'_parsley">
                <select id="'.$fieldID.'" name="'.$fieldName.'" ' . build_attributes($attr) . ' >
                    <option class="static_option" value="">'.$firstOption.'</option>
                    '.select_options($options, set_value($fieldName, $value, FALSE)).'
                </select>
            </div>
            <span class="field-error-container '.$fieldID.'_error"></span>

            '.($runScript ? '
            <script>
                on_script_load("jQuery", function() {
                    $("#'.$fieldID.'").multiselect({
                        enableFiltering: true,
                        enableCaseInsensitiveFiltering: true,
                        maxHeight: 200,
                        onDropdownShow: function(event) {
                            let thisDropDown = $(event.currentTarget).closest(".c_dropdown");

                            thisDropDown.find("select option").each(function() {
                                if ($(this).data("class")) {
                                    let value = $(this).val();
                                    thisDropDown.find(".multiselect-container input[value=\""+value+"\"]").closest("li").addClass($(this).data("class"));
                                }
                            })
                        }
                    });
                });
            </script>
            ' : '').'
            
        </div>
    ');

    return $html;
}

function field_multi_select($field, $options = array(), $value = "", $class = "required", $extra = array()) {
    $ci         =& get_instance();
    $fieldData  = explode('|', $field);
    $fieldName  = $fieldData[0];
    $fieldID    = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());

    $value = is_object($value) && property_exists($value, $fieldName) ? $value->$fieldName : $value;

    //Convert comma separated string to array if $value is string
    $value = is_string($value) && !empty($value) ? explode(',', $value) : $value;

    //Check if value is passed for submodule
    if (empty($value)) {
        $submodules = $ci->session->submodules;
        if (!empty($submodules[$ci->pageName]->manageField) && $submodules[$ci->pageName]->manageField == $fieldName) {
            $value = [$submodules[$ci->pageName]->id];
        }
    }

    //Determine whether or not to show label and then get label data
    $label         = ! empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_' . $fieldName);
    $labelClass    = ! empty($fieldData[2]) ? $fieldData[2] : '';

    //Set field attributes
    $attr = array(
        'title'                         => $label,
        'class'                         => 'form-control ecms-multiselect multiselect multiselect-custom '.$class,
        'autocomplete'                  => 'off',
        'data-parsley-errors-container' => '.'.$fieldID.'_error',
        'data-parsley-class-handler'    => '.'.$fieldID.'_parsley'
    );
    $attr = array_merge($attr, $extra);

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';

        $rMark = 'required-mark';
    }

    $html = trim('
        <div class="ecms-field form-group" rel="'.$fieldName.'">
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            <div class="c_multiselect '.$fieldID.'_parsley">
                <select id="'.$fieldID.'" name="'.$fieldName.'[]" multiple="multiple" '.build_attributes($attr).' >
                    '.multi_select_options($options, set_value($fieldName, $value, FALSE)).'
                </select>
            </div>
            <span class="field-error-container '.$fieldID.'_error"></span>
            <script>
                $("#'.$fieldID.'").multiselect({
                    enableFiltering: true,
                    enableCaseInsensitiveFiltering: true,
                    maxHeight: 200
                });
            </script>
        </div>
    ');


    return $html;
}

function field_radio($field, $value = "", $radioValues = [1 => 'Yes', 0 => 'No'], $class = "", $extra = array()) {
    $fieldData = explode('|', $field);
    $fieldName = $fieldData[0];
    $fieldID   = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());

    //Extra value out of row object if $value is object
    $value = is_object($value) && property_exists($value, $fieldName) ? $value->$fieldName : $value;

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_' . $fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Set field attributes
    $attr = array(
        'title'                         => $label,
        'class'                         => $class,
        'autocomplete'                  => 'off',
        'data-parsley-errors-container' => '.'.$fieldID.'_error',
    );
    $attr = array_merge($attr, $extra);

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message']  = $label.' is required';

        $rMark = 'required-mark';
    }

    $radioHTML = '';

    foreach ($radioValues as $radioValue => $radioLabel) {
        $radioHTML .= trim('
            <label class="fancy-radio custom-color-green">
                <input type="radio" name="' . $fieldName . '" value="' . $radioValue . '" ' . set_checked($fieldName, $value, $radioValue) . ' ' .build_attributes($attr) . ' />
                <span><i></i> '.$radioLabel.'</span>
            </label>
        ');
    }

    $html = trim('
        <div class="ecms-field form-group radio-group" data-error="'.attr_clean(form_error($fieldName)).'">
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            <div class="fancy-radio-group">
                '.$radioHTML.'
            </div>
            <span class="field-error-container '.$fieldID.'_error"></span>
        </div>
    ');

    return $html;
}

function field_checkbox($field, $value = "", $class = "", $checkboxLabel = "", $checkboxValue = "1", $extra = array()) {
    $fieldData = explode('|', $field);
    $fieldName = $fieldData[0];
    $fieldID   = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());

    //Extra value out of row object if $value is object
    $value = is_object($value) && property_exists($value, $fieldName) ? $value->$fieldName : $value;

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_' . $fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Set field attributes
    $attr = array(
        'title'                         => $label,
        'class'                         => $class,
        'autocomplete'                  => 'off',
        'data-parsley-errors-container' => '.'.$fieldID.'_error'
    );
    $attr = array_merge($attr, $extra);

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';

        $rMark = 'required-mark';
    }

    //if $checkboxLabel is empty then assign the field label to it
    if ($checkboxLabel == "") {
        $checkboxLabel = $label;
    }

    $html = trim('
        <div class="ecms-field form-group" data-error="'.attr_clean(form_error($fieldName)).'">
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            <div class="fancy-checkbox-group">
                <label class="fancy-checkbox">
                    <input type="checkbox" name="' . $fieldName.'" value="'.$checkboxValue.'" '.set_checked($fieldName, $value, $checkboxValue).' '.build_attributes($attr).' />
                    <span>'.$checkboxLabel.'</span>
                </label>
            </div>
            <span class="field-error-container '.$fieldID.'_error"></span>
        </div>
    ');

    return $html;
}

function field_ckeditor_simple($field, $value="", $class="required", $extra=array(), $ckOptions=array(), $runScript=true) {

    //Set default toolbar
    if (empty($ckOptions['toolbar'])) {
        $ckOptions['toolbar']['items'] = array(
            'bold', 'italic', 'underline', 'link'
        );
        $ckOptions['toolbar']['shouldNotGroupWhenFull'] = true;
        
    }

    $class = 'simple-editor '.$class;

    return field_ckeditor($field, $value, $class, $extra, $ckOptions, $runScript);
}


function field_ckeditor($field, $value="", $class="required", $extra=array(), $ckOptions=array(), $runScript=true) {
    $ci        =& get_instance();
    $fieldData = explode('|', $field);
    $fieldName = $fieldData[0];
    $fieldID   = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());

    // Extra value out of row object if $value is object
    $value = is_object($value) && property_exists($value, $fieldName) ? $value->$fieldName : $value;

    // Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_'.$fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    // Set field attributes
    $attr = array(
        'id'                            => 'ckeditor_'.$fieldName,
        'title'                         => $label, 
        'class'                         => $class, 
        'autocomplete'                  => 'off',
        'placeholder'                   => $label,
        'data-parsley-errors-container' => '.'.$fieldID.'_error',
        'data-parsley-class-handler'    => '.'.$fieldID.'_parsley .ck-editor'
    );
    $attr = array_merge($attr, $extra);

    //Get options
    $options = !empty($ci->ckUploaders[$fieldName]) ? $ci->ckUploaders[$fieldName] : array();

    //Short hand to set ratio
    if (!empty($options['ratio'])) {
        $options['aspectRatio'] = $options['ratio'];
    }

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';

        $rMark = 'required-mark';
        $labelClass == 'placeholder' && $attr['placeholder'] .= '*';
    } 

    //Set default toolbar
    if (empty($ckOptions['toolbar'])) {
        $ckOptions['toolbar']['items'] = array(
            'bold', 'italic', 'underline', 'link', 'blockQuote', '|',
            'bulletedList', 'numberedList', 'indent', 'outdent', '|',
            'heading', '|',
            'imageUpload', 'mediaEmbed', '|',
            'insertTable', 'tableProperties', 'tableCellProperties',  '|',
            'undo', 'redo', 'removeFormat', '|',
            'sourceEditing'
        );
        $ckOptions['toolbar']['shouldNotGroupWhenFull'] = true;
    }

    $html = trim('
        <div class="ecms-field form-group '.$fieldID.'_parsley" rel="'.$fieldName.'">
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            '.form_textarea($fieldName, set_value($fieldName, $value, false), $attr).'
            <span class="field-error-container '.$fieldID.'_error"></span>
        </div>
        '.($runScript ? '
        <script type="text/javascript">
            ecmsFieldOptions["'.$fieldName.'"] = {};
            ecmsFieldOptions["'.$fieldName.'"].cropper = '.json_encode($options).';

            ClassicEditor
            .create(document.querySelector(\'#ckeditor_'.$fieldName.'\'), '.json_encode((object)$ckOptions).')
            .then(editor => {
                editor.plugins.get( \'FileRepository\' ).createUploadAdapter = ( loader ) => {
                    return new ECMSUploadAdapter( loader, \''.$fieldName.'\' );
                };
                editor.model.document.on(\'change:data\', () => {
                    $(\'#ckeditor_'.$fieldName.'\').val(editor.getData());
                });
            })
            .catch(error => {
                console.error(error);
            });
        </script>
        ' : '').'
    ');

    return $html;
}

function field_hidden($field, $value = "", $class = "", $extra = array()) {
    $fieldData = explode('|', $field);
    $fieldName = $fieldData[0];

    //Extra value out of row object if $value is object
    $value = is_object($value) && property_exists($value, $fieldName) ? $value->$fieldName : $value;

    //Set field attributes
    $attr = array(
        'class' => $class
    );
    $attr = array_merge($attr, $extra);

    $html = trim('
        <input type="hidden" name="'.$fieldName.'" value="'.set_value($fieldName, $value, FALSE).'" '.build_attributes($attr).' />
    ');

    return $html;
}

function field_date($field, $value = "", $class = "required", $format="yyyy-mm-dd", $extra = array()) {
    $fieldData = explode('|', $field);
    $fieldName = $fieldData[0];
    $fieldID   = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());

    //Extra value out of row object if $value is object
    $value = is_object($value) && property_exists($value, $fieldName) ? $value->$fieldName : $value;

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_' . $fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Set field attributes
    $attr = array(
        'title'                         => $label,
        'class'                         => 'input-sm form-control '.$class, 
        'autocomplete'                  => 'off',
        'data-parsley-errors-container' => '.'.$fieldID.'_error'
    );
    $attr = array_merge($attr, $extra);

    //Check if we should show the label as placeholder
    if ($labelClass == 'placeholder') {
        $attr['placeholder'] = $label;
    }

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';

        $rMark = 'required-mark';

        $labelClass == 'placeholder' && $attr['placeholder'] .= '*';
    }

    $html = trim('
        <div class="ecms-field form-group" rel="'.$fieldName.'" data-error="'.attr_clean(form_error($fieldName)).'">
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            <div class="input-group date" data-date-autoclose="true" data-provide="datepicker" data-date-format="'.$format.'">
                <input type="text" name="'.$fieldName.'" value="'.set_value($fieldName, $value, FALSE).'" '.build_attributes($attr).' />
                <div class="input-group-append">                                            
                    <button class="btn btn-outline-secondary" type="button"><i class="fa fa-calendar"></i></button>
                </div>
            </div>
            <span class="field-error-container '.$fieldID.'_error"></span>
        </div>
    ');

    return $html;
}

function field_date_time($field, $value = "", $class = "required", $format="Y-m-d H:i", $dateOptions=array(), $extra = array(), $runScript=true) {
    $fieldData = explode('|', $field);
    $fieldName = $fieldData[0];
    $fieldID   = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());

    //Extra value out of row object if $value is object
    $value = is_object($value) && property_exists($value, $fieldName) ? $value->$fieldName : $value;

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_' . $fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Set field attributes
    $attr = array(
        'title'                         => $label,
        'class'                         => 'input-sm form-control '.$class,
        'autocomplete'                  => 'off',
        'data-parsley-errors-container' => '.'.$fieldID.'_error'
    );
    $attr = array_merge($attr, $extra);

    //Set default date options
    $defaultDateOptions = array(
        'enableTime' => true,
        'dateFormat' => $format,
        'allowInput' => true
    );
    $dateOptions = array_merge($defaultDateOptions, $dateOptions);

    //Check if we should show the label as placeholder
    if ($labelClass == 'placeholder') {
        $attr['placeholder'] = $label;
    }

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';

        $rMark = 'required-mark';

        $labelClass == 'placeholder' && $attr['placeholder'] .= '*';
    }

    $html = trim('
        <div class="ecms-field date-time-picker form-group" rel="'.$fieldName.'" data-error="'.attr_clean(form_error($fieldName)).'">
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            <div class="input-group calendar-with-icon" data-date-autoclose="true" >
                <input type="text" name="'.$fieldName.'" value="'.set_value($fieldName, $value, FALSE).'" '.build_attributes($attr).' />
            </div>
            <span class="field-error-container '.$fieldID.'_error"></span>
        </div>
        '.($runScript ? '
        <script type="text/javascript">
            $(document).ready(function() {
                flatpickr(".form-field-container .date-time-picker[rel=' .$fieldName. '] input", '.json_encode((object)$dateOptions).');
            });
        </script>
        ' : '').'
    ');

    return $html;
}

function field_multi_file($field, $row=array(), $class="required", $loadScript=true) {
    $ci         =& get_instance();
    $fieldData  = explode('|', $field);
    $fieldName  = $fieldData[0];
    $fieldID    = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());
    $html       = '';

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_'.$fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Get options
    $options = !empty($ci->uploaders[$fieldName]) ? $ci->uploaders[$fieldName] : array();

    //Check if uploader options have been passed
    $settings = !empty($options['settings']) ? $options['settings'] : [];

    //Set info
    $info = !empty($options['info']) ? $options['info'] : 'Please upload a valid file.';

    //Set min and max files
    $minFiles = !empty($options['min_files']) ? $options['min_files'] : 0;
    $maxFiles = !empty($options['max_files']) ? $options['max_files'] : 0;

    //Set field attributes
    $attr = array(
        'title' => $label,
        'class' => 'form-control dz-uploader dz-multi-file '.$class.' '.$fieldID.'_parsley'
    );

    //Validator Attributes
    $validatorAttr = array(
        'data-parsley-errors-container' => '.'.$fieldID.'_error',
        'data-parsley-class-handler'    => '.'.$fieldID.'_parsley',
        'data-parsley-max-files'        => $maxFiles,
        'data-parsley-check-files'      => 'check'
    );

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required']                           = 'required';
        $attr['data-parsley-required-message']      = $label.' is required';
        $validatorAttr['data-parsley-min-files']    = $minFiles ? $minFiles : 1;

        $rMark = 'required-mark';
    }

    //Get uploaded data
    $uploaderData = method_exists($ci, 'get_uploader_data') ? $ci->{'get_uploader_data'}($fieldName, $row) : array();

    $html = '
        <div class="ecms-field form-group" rel="'.$fieldName.'" data-error="'.attr_clean(form_error($fieldName)).'">
            <input type="text" class="hide" name="'.$fieldName.'_validator" value="xxx" '.build_attributes($validatorAttr).' />
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            <div rel="'.$fieldName.'" '.build_attributes($attr).'>
                <div class="no-files">
                    <span class="no-files-icon"><i class="icon-cloud-upload"></i></span>
                    <span class="no-files-text">Drag and drop or click here to upload files:<br/>'.$info.'</span>
                </div>
                <div class="file-thumbs"></div>
                <div class="file-data"></div>
            </div>
            <span class="field-error-container '.$fieldID.'_error"></span>
            <script>
                ecmsFieldOptions["'.$fieldName.'"] = {};
                ecmsFieldOptions["'.$fieldName.'"].settings = '.json_encode($settings).';

                uploaderData["'.$fieldName.'"] = '.(!empty($uploaderData) ? json_encode($uploaderData) : '[]').';

                '.($loadScript ? 'setup_dz_uploader("'.$fieldName.'");' : '').'
            </script>
        </div>
    ';

    return $html;
}

function field_file($field, $row=array(), $class="required", $loadScript=true) {
    $ci         =& get_instance();
    $fieldData  = explode('|', $field);
    $fieldName  = $fieldData[0];
    $fieldID    = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());
    $html       = '';

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_'.$fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Get options
    $options = !empty($ci->uploaders[$fieldName]) ? $ci->uploaders[$fieldName] : array();

    //Check if uploader options have been passed
    $settings = !empty($options['settings']) ? $options['settings'] : [];

    //Set info
    $info = !empty($options['info']) ? $options['info'] : 'Please upload a valid file.';

    //Set field attributes
    $attr = array(
        'title' => $label,
        'class' => 'form-control dz-uploader dz-single-file '.$class.' '.$fieldID.'_parsley',
    );

    //Validator Attributes
    $validatorAttr = array(
        'data-parsley-errors-container' => '.'.$fieldID.'_error',
        'data-parsley-class-handler'    => '.'.$fieldID.'_parsley',
        'data-parsley-max-files'        => 1,
        'data-parsley-check-files'      => 'check'
    );

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';
        $validatorAttr['data-parsley-file-required'] = 1;

        $rMark = 'required-mark';
    }

    //Get uploaded data
    $uploaderData = method_exists($ci, 'get_uploader_data') ? $ci->{'get_uploader_data'}($fieldName, $row) : array();

    $html = '
        <div class="ecms-field form-group" rel="'.$fieldName.'" data-error="'.attr_clean(form_error($fieldName)).'">
            <input type="text" class="hide ignore" name="'.$fieldName.'_validator" value="xxx" '.build_attributes($validatorAttr).' />
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            <div rel="'.$fieldName.'" '.build_attributes($attr).'>
                <div class="no-files">
                    <span class="no-files-icon"><i class="icon-cloud-upload"></i></span>
                    <span class="no-files-text">Drag and drop or click here to upload files:<br/>'.$info.'</span>
                </div>
                <div class="file-thumbs"></div>
                <div class="file-data"></div>
            </div>
            <span class="field-error-container '.$fieldID.'_error"></span>
            <script>
                ecmsFieldOptions["'.$fieldName.'"] = {};
                ecmsFieldOptions["'.$fieldName.'"].settings = '.json_encode($settings).';

                elog("Loaded uploader html and set ecmsFieldOptions");

                uploaderData["'.$fieldName.'"] = '.(!empty($uploaderData) ? json_encode($uploaderData) : '[]').';

                '.($loadScript ? 'setup_dz_uploader("'.$fieldName.'");' : '').'
            </script>
        </div>
    ';

    return $html;
}

function field_image($field, $row=array(), $class="required", $loadScript=true) {
    $ci         =& get_instance();
    $fieldData  = explode('|', $field);
    $fieldName  = $fieldData[0];
    $fieldID    = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());
    $html       = '';

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_'.$fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Get options
    $options = !empty($ci->uploaders[$fieldName]) ? $ci->uploaders[$fieldName] : array();

    //Check if uploader options have been passed
    $settings                   = !empty($options['settings']) ? $options['settings'] : [];
    $settings['thumbnailWidth'] = !empty($settings['thumbnailWidth']) ? $settings['thumbnailWidth'] : 350;
    $settings['acceptedFiles']  = !empty($settings['acceptedFiles']) ? $settings['acceptedFiles'] : 'image/jpeg, image/png, image/gif, image/webp';
    $settings['uploadMultiple']  = false;
    $settings['parallelUploads']  = 1;

    //Check if cropper options is set
    $cropper = !empty($options['cropper']) ? $options['cropper'] : [];

    //Set ratio
    $cropper['aspectRatio'] = (!empty($options['ratio']) ? $options['ratio'] : (!empty($cropper['aspectRatio']) ? $cropper['aspectRatio'] : 1));

    //Set download size for uploader
    $downloadSize = !empty($options['sizes']) ? max($options['sizes']) : 350;

    //Set info
    $info = !empty($options['info']) ? $options['info'] : 'Please upload a valid file.';

    //Set field attributes
    $attr = array(
        'title' => $label,
        'class' => 'form-control dz-uploader dz-single-image '.$class.' '.$fieldID.'_parsley',
    );

    //Validator Attributes
    $validatorAttr = array(
        'data-parsley-errors-container' => '.'.$fieldID.'_error',
        'data-parsley-class-handler'    => '.'.$fieldID.'_parsley',
        'data-parsley-max-files'        => 1,
        'data-parsley-check-files'      => 'check'
    );

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required'] = 'required';
        $attr['data-parsley-required-message'] = $label.' is required';
        $validatorAttr['data-parsley-image-required'] = 1;

        $rMark = 'required-mark';
    }

    //Get uploaded data
    $uploaderData = method_exists($ci, 'get_uploader_data') ? $ci->{'get_uploader_data'}($fieldName, $row) : array();

    $html = '
        <div class="ecms-field form-group" rel="'.$fieldName.'" data-error="'.attr_clean(form_error($fieldName)).'">
            <input type="text" class="hide" name="'.$fieldName.'_validator" value="xxx" '.build_attributes($validatorAttr).' />
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            <div rel="'.$fieldName.'" '.build_attributes($attr).'>
                <div class="no-files">
                    <span class="no-files-text">'.$info.'</span>
                </div>
                <div class="file-thumbs"></div>
                <div class="file-data"></div>
            </div>
            <span class="field-error-container '.$fieldID.'_error"></span>
            <script>
                ecmsFieldOptions["'.$fieldName.'"] = {};
                ecmsFieldOptions["'.$fieldName.'"].settings = '.json_encode($settings).';
                ecmsFieldOptions["'.$fieldName.'"].cropper = '.json_encode($cropper).';
                ecmsFieldOptions["'.$fieldName.'"].thumbSize = '.$settings['thumbnailWidth'].';
                ecmsFieldOptions["'.$fieldName.'"].downloadSize = '.$downloadSize.';

                uploaderData["'.$fieldName.'"] = '.(!empty($uploaderData) ? json_encode($uploaderData) : '[]').';

                '.($loadScript ? 'setup_dz_uploader("'.$fieldName.'");' : '').'
            </script>
        </div>
    ';

    return $html;
}

function field_multi_image($field, $row=array(), $class="required", $loadScript=true) {
    $ci         =& get_instance();
    $fieldData  = explode('|', $field);
    $fieldName  = $fieldData[0];
    $fieldID    = preg_replace('/[^\w\d\_]/', '_', $fieldName.'_'.uniqid());
    $html       = '';

    //Determine whether or not to show label and then get label data
    $label      = !empty($fieldData[1]) ? lang($fieldData[1]) : lang('label_'.$fieldName);
    $labelClass = !empty($fieldData[2]) ? $fieldData[2] : '';

    //Get options
    $options = !empty($ci->uploaders[$fieldName]) ? $ci->uploaders[$fieldName] : array();

    //Check if uploader options have been passed
    $settings                   = !empty($options['settings']) ? $options['settings'] : [];
    $settings['thumbnailWidth'] = !empty($settings['thumbnailWidth']) ? $settings['thumbnailWidth'] : 350;
    $settings['acceptedFiles']  = !empty($settings['acceptedFiles']) ? $settings['acceptedFiles'] : 'image/jpeg, image/png, image/gif';

    //Check if cropper options is set
    $cropper = !empty($options['cropper']) ? $options['cropper'] : [];

    //Set ratio
    $cropper['aspectRatio'] = (!empty($options['ratio']) ? $options['ratio'] : (!empty($cropper['aspectRatio']) ? $cropper['aspectRatio'] : 1));

    //Set download size for uploader
    $downloadSize = !empty($options['sizes']) ? max($options['sizes']) : 350;

    //Set info
    $info = !empty($options['info']) ? $options['info'] : 'Please upload a valid file.';

    //Set min and max files
    $minFiles = !empty($options['min_files']) ? $options['min_files'] : 0;
    $maxFiles = !empty($options['max_files']) ? $options['max_files'] : 0;

    //Set field attributes
    $attr = array(
        'title' => $label,
        'class' => 'form-control dz-uploader dz-multi-image '.$class.' '.$fieldID.'_parsley',
    );

    //Validator Attributes
    $validatorAttr = array(
        'data-parsley-errors-container' => '.'.$fieldID.'_error',
        'data-parsley-class-handler'    => '.'.$fieldID.'_parsley',
        'data-parsley-max-files'        => $maxFiles,
        'data-parsley-check-files'      => 'check'
    );

    //Check if field should be required
    $rMark = '';
    if (preg_match('/\brequired\b/', $class)) {
        $attr['required']                           = 'required';
        $attr['data-parsley-required-message']      = $label.' is required';
        $validatorAttr['data-parsley-min-images']   = $minFiles ? $minFiles : 1;

        $rMark = 'required-mark';
    }

    //Get uploaded data
    $uploaderData = method_exists($ci, 'get_uploader_data') ? $ci->{'get_uploader_data'}($fieldName, $row) : array();

    $html = '
        <div class="ecms-field form-group" rel="'.$fieldName.'" data-error="'.attr_clean(form_error($fieldName)).'">
            <input type="text" class="hide" name="'.$fieldName.'_validator" value="xxx" '.build_attributes($validatorAttr).' />
            <label class="'.trim($labelClass.' '.$rMark).'" for="'.$fieldName.'">'.$label.'</label>
            <div rel="'.$fieldName.'" '.build_attributes($attr).'>
                <div class="no-files">
                    <span class="no-files-icon"><i class="icon-cloud-upload"></i></span>
                    <span class="no-files-text">Drag and drop or click here to upload files:<br/>'.$info.'</span>
                </div>
                <div class="file-thumbs"></div>
                <div class="file-data"></div>
            </div>
            <span class="field-error-container '.$fieldID.'_error"></span>
            <script>
                ecmsFieldOptions["'.$fieldName.'"] = {};
                ecmsFieldOptions["'.$fieldName.'"].settings = '.json_encode($settings).';
                ecmsFieldOptions["'.$fieldName.'"].cropper = '.json_encode($cropper).';
                ecmsFieldOptions["'.$fieldName.'"].thumbSize = '.$settings['thumbnailWidth'].';
                ecmsFieldOptions["'.$fieldName.'"].downloadSize = '.$downloadSize.';

                uploaderData["'.$fieldName.'"] = '.(!empty($uploaderData) ? json_encode($uploaderData) : '[]').';

                '.($loadScript ? 'setup_dz_uploader("'.$fieldName.'");' : '').'
            </script>
        </div>
    ';

    return $html;
}

function field_text($field, $length, $value = "", $class = "required", $extra = array()) {

    $extra['maxlength'] = $length;

    return field_input($field, $value, $class, $extra, 'text');
}

function field_tel($field, $value = "", $class = "required", $extra = array()) {

    $extra['maxlength'] = 16;

    return field_input($field, $value, $class, $extra, 'tel');
}

function field_email($field, $value = "", $class = "required", $extra = array()) {

    $extra['maxlength'] = 128;

    return field_input($field, $value, $class, $extra, 'email');
}