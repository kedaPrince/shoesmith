<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$submodules = $this->session->submodules;
$submodule  = false;
if (!empty($submodules[$this->pageName])) {
    $submodule = $submodules[$this->pageName];
}

$filters        = $this->filters;
$filterSession  = get_ecms_filters($this->pageName);

//Only show filters if there are any.
if ( ! empty($filters)) {
    echo '<div class="body">';

    //Loop through all filters
    echo '<div class="row clearfix">';
    foreach ($filters as $fname => $filter) {

        //Skip filter if specified in submodule options
        if ($submodule && in_array($fname, $submodule->hideFilters)) {
            continue;
        }

        $gridClass = !empty($filter['grid_class']) ? $filter['grid_class'] : 'col-xl-2 col-lg-3 col-md-4 col-sm-6';

        echo '<div class="'.$gridClass.'">';

        $fAttr = ! empty($filter['attr']) ? $filter['attr'] : [];
        $fClass = ! empty($filter['class']) ? $filter['class'] : '';

        if ($filter['type'] == 'dropdown') {

            $allLabel = ! empty($filter['all_label']) ? $filter['all_label'] : lang('label_all');
            $fValue = !empty($filterSession[$fname]) ? $filterSession[$fname] : '';
            $options = (is_object($filter['options'])) ? $filter['options']->result_array() : $filter['options'];
            echo '<label for="'.$fname.'">'.$filter['label'].'</label>';
            echo field_dropdown($fname.'|label_blank|hide', $options, isset($fValue['value']) ? $fValue['value'] : '', 'dropdown-filter filter-value '.$fClass, $fAttr, '', $allLabel);

        }
        elseif ($filter['type'] == 'search') {

        }
        elseif ($filter['type'] == 'date') {
            $icon = !empty($filter['icon']) ? $filter['icon'] : 'filter_list';

            echo '<label for="'.$fname.'">'.$filter['label'].'</label>';
            echo field_date($fname.'|label_search|hide', $fValue, 'date-filter filter-value '.$fClass, 'dd M yyyy', $fAttr);
        }
        elseif ($filter['type'] == 'date_range') {
            $icon = !empty($filter['icon']) ? $filter['icon'] : 'filter_list';
            $fromValue = !empty($filterSession[$fname]['value']['from']) ? $filterSession[$fname]['value']['from'] : '';
            $toValue = !empty($filterSession[$fname]['value']['to']) ? $filterSession[$fname]['value']['to'] : '';

            echo '
                <label>'.$filter['label'].'</label>
                <div class="date-range-filter">
                    <div class="input-group date" data-date-autoclose="true" data-provide="datepicker" data-date-format="dd M yyyy">
                        <input type="text" class="input-sm form-control filter-value filter-value-from" name="'.$fname.'-from" value="'.$fromValue.'" placeholder="'.lang('label_from').'" />
                        <div class="input-group-append">                                            
                            <button class="btn btn-outline-secondary" type="button"><i class="fa fa-calendar"></i></button>
                        </div>
                    </div>
                    
                    <div class="input-group date" data-date-autoclose="true" data-provide="datepicker" data-date-format="dd M yyyy">
                        <input type="text" class="input-sm form-control filter-value filter-value-to" name="'.$fname.'-to" value="'.$toValue.'" placeholder="'.lang('label_to').'" />
                        <div class="input-group-append">                                            
                            <button class="btn btn-outline-secondary" type="button"><i class="fa fa-calendar"></i></button>
                        </div>
                    </div>
                </div>
            ';

        }
        elseif ($filter['type'] == 'range') {
            $icon = !empty($filter['icon']) ? $filter['icon'] : 'filter_list';
            $fromValue = !empty($filterSession[$fname]['value']['from']) ? $filterSession[$fname]['value']['from'] : '';
            $toValue = !empty($filterSession[$fname]['value']['to']) ? $filterSession[$fname]['value']['to'] : '';

            echo '<div class="general-filter range-filter">
                    <label><i class="material-icons-outlined">'.$icon.'</i> '.$filter['label'].'</label>
                    <input type="text" name="'.$fname.'-from" class="int-range filter-value filter-value-from" value="'.$fromValue.'" placeholder="'.lang('label_from').'" />
                    <input type="text" name="'.$fname.'-to" class="int-range filter-value filter-value-to" value="'.$toValue.'" placeholder="'.lang('label_to').'" />
                </div>';
        }
        elseif ($filter['type'] == 'autocomplete') {
            if (!empty($filterSession[$fname])  && !empty($filterSession[$fname]['value'])) {
                $fValue = $filterSession[$fname]['value'];
            } else {
                $fValue = '';
            }

            echo '<label for="'.$fname.'">'.$filter['label'].'</label>';
            echo field_input($fname.'|label_search|hide', $fValue, 'autocomplete-textbox filter-value '.$fClass, $fAttr);
        }

        echo '</div>';
    }
    //Clear filter
    echo '
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <button type="button" class="clear-filters btn btn-light" onclick="clear_filters()"><i class="fa fa-times"></i> Clear Filters</a>
        </div>';

    echo '</div>';


    echo '</div>';
}