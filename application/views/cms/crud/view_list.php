<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$submodules = $this->session->submodules;
$submodule  = false;
if (!empty($submodules[$this->pageName])) {
    $submodule = $submodules[$this->pageName];
}
?>

<!-- mani page content body part -->
<div id="main-content">
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <?php 
                    if (!empty($heading)) {
                        echo '
                            <h2>'.$heading.'</h2>
                        ';
                    }
                    elseif (!empty($this->zone['title'])) {

                        $parentTitle = '';
                        if ($submodule) {
                            $parentTitle .= ' for '.$submodule->identifier;
                        }

                        echo '
                            <h2>'.$this->zone['title'].$parentTitle.'</h2>
                        ';
                    }

                    $parentUrl = parent_url();
                    if($parentUrl){
                        echo '
                            <a href="'.htmlspecialchars($parentUrl).'" class="btn btn-primary history-back-button" id="top-bar-back-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-arrow-left-short" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5"></path>
                                </svg>
                                Back
                            </a>
                        ';
                    }
                    
                    if (!empty($this->breadcrumbs)) {
                        echo '<ul class="breadcrumb">';

                        if (empty($this->hideDashboardBreadcrumb)) {
                            echo '
                                <li class="breadcrumb-item">
                                    <a href="'.url('dashboard').'"><i class="fa fa-dashboard"></i></a>
                                </li>';
                        }

                        $b = 1;
                        foreach ($this->breadcrumbs as $crumb) {
                            $active = ($crumb['url'] == current_url()) ? 'active' : '';

                            echo '
                            <li class="breadcrumb-item '.$active.'">
                                <a href="' . $crumb['url'] . '" title="' . $crumb['title'] . '">' . $crumb['title'] . '</a>
                            </li>
                            ';
                        }
                        echo '</ul>';
                    }
                    ?>
                </div>

                <?php
                if (empty($this->hideActions) || ! $this->hideActions) {
                    $addButtonLabel = !empty($this->addButtonLabel) ? $this->addButtonLabel : ('Add'.(!empty($this->singular)?' '.$this->singular:''));

                    //Add extra page actions
                    $extraPageActionHTML = '';
                    if (!empty($this->extraPageActions)) {
                        foreach ($this->extraPageActions as $pageAction) {
                            $paAttr = array();

                            //Set page action class
                            $paAttr['class'] = !empty($pageAction['class']) ? $pageAction['class'] : 'btn btn-secondary';

                            //Set page url
                            if (!empty($pageAction['url'])) {
                                $paAttr['href'] = $pageAction['url'];
                            }

                            $extraPageActionHTML .= '<a '.build_attributes($paAttr).'><i class="fa '.$pageAction['icon'].'" ></i> '.$pageAction['label'].'</a>'."\n";
                        }
                    }


                    echo '
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="d-flex flex-row-reverse">
                                <div class="page_action">
                                '.$extraPageActionHTML.'
                                '.(!empty($this->submoduleParent) ? '<a class="btn btn-secondary" href="' . $this->submoduleParent['url'] . '"><i class="fa fa-code-fork" ></i> '.$this->submoduleParent['label'].'</a>':'').'
                                '.(!empty($this->export) ? '<a class="btn btn-success export-button" href="'.url($this->pageName.'/export').'"><i class="fa fa-file-excel-o"></i> Export</a>':'').'
                                '.($this->adding ? '<a class="btn btn-primary add-item" '.(!$this->quickManage ? 'href="'.url($this->pageName).'/add"':'').' ><i class="fa fa-plus-circle"></i> '.$addButtonLabel.'</a>':'').'
                                </div>
                            </div>
                        </div>
                    ';
                }
                ?>
            </div>
        </div>

        <?php
        //Get Sorting
        if (!empty($this->session->{$this->pageName.'Sorting'})) {
            $sorting = $this->session->{$this->pageName.'Sorting'};
        }
        elseif (!empty($this->sorting)) {
            $sorting = $this->sorting;
        }
        else {
            $sorting = array();
        }

        $tableClasses = 'data-table ' . (!empty($this->listExpanded) ? 'expand-list-layout' : '');
        ?>

        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card ecms-listing">
                    <div class="listing-filters">
                        <?php $this->load->view('cms/crud/view_list_filters') ?>
                    </div>
                    <div class="body">
                        <div class="table-responsive">
                            <table
                                class="table table-hover js-basic-example dataTable table-custom m-b-0 <?= $tableClasses; ?>">
                                <thead>
                                    <tr class="header-row">
                                        <?php
                                        //If list items can expand, then make provision for expand and collapse buttons
                                        if ( ! empty($this->listExpanded)) {
                                            echo '<th></th>';
                                        }

                                        //Print listing headers
                                        $list = ! empty($this->sections) && isset($this->listFields[$section]) ? $this->listFields[$section] : $this->listFields;
                                        foreach ($list as $field => $options) {
                                            //Skip field if specified in submodule options
                                            if ($submodule && in_array($field, $submodule->hideFields)) {
                                                continue;
                                            }

                                            //Skip if field is set to hidden
                                            if (isset($options['show']) && !$options['show']) {
                                                continue;
                                            }

                                            $colClass = !empty($options['class']) ? $options['class'] : "";
                                            
                                            $attr = array(
                                                'data-field-header="' . $field . '"'
                                            );
                                            if ($options['sort']) {
                                                $colClass .= ' sortable';
                                                if ( ! empty($sorting[$field])) {
                                                    array_push($attr, 'data-sort-order="' . $sorting[$field] . '"');
                                                }
                                            }

                                            //Check for tooltips
                                            if ( ! empty($options['tooltip'])) {
                                                $attr[] = 'title="' . $options['tooltip'] . '"';
                                                $attr[] = 'data-toggle="tt"';
                                            }

                                            echo '<th class="' . trim($colClass) . '" ' . implode(' ', $attr) . '>' . $options['label'] . '</th>';
                                        }

                                        //Add a blank column for actions if set
                                        if (!empty($this->listActions)) {
                                            echo '<th class="action-column">Action</th>';
                                        }
                                    ?>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    if ($this->quickManage == TRUE) {
        ?>
<aside class="quick-manage-container" data-size="<?=$this->quickManageSize?>"
    data-original-size="<?=$this->quickManageSize?>">
</aside>
<?PHP
    }
?>
<?php
//For adding scripts and hidden containers
if (is_file(APPPATH . 'views/' . $this->folder . '/' . $this->pageName . '/view_list_extra.php')) {
	$this->load->view($this->folder . '/' . $this->pageName . '/view_list_extra');
}
?>
<script type="text/javascript">
var headerSize = 137;
var callOnce;
var defaultSorting = new Object();
var sorting = new Object();
var autoCompleteTimer;
<?php
if (!empty($this->sorting)) {
    foreach ($this->sorting as $field => $order) {
        echo 'defaultSorting.' . $field . ' = "' . $order . '";' . "\n";
    }
}
foreach ($sorting as $field => $order) {
    echo 'sorting.' . $field . ' = "' . $order . '";' . "\n";
}
?>

window.addEventListener('DOMContentLoaded', (event) => {

    load_results_init();

    $(document).on('batchLoaded', function() {
        $('[data-toggle="listing-tt"]').tooltip({
            trigger: 'hover'
        });
    });



    $(document).on('click', function() {
        $('.action-list-menu').removeClass('show');
    });


    // Click on row to view
    var rowClick = '<?= isset($this->rowClick) ? $this->rowClick : 'edit-row' ?>';
    $('.data-table').on('click', 'tr', function() {
        if (!$(this).hasClass('expanded-list-item')) {
            if ($(this).find('.' + rowClick).length) {
                $(this).find('.' + rowClick)[0].click();
            }
        }

    });

    $('.data-table').on('click', '.item-row', function(e) {
        var url = $(this).attr('href');

        if (url != '') {
            location.href = url;
        }

    });

    $('.data-table').on('click', '.no-click', function(e) {
        e.stopPropagation();
        e.preventDefault();
    });

    $('.data-table').on('click', '.general-actions .open_action_list', function(e) {
        $('.action-list-menu').removeClass('show');
        $(this).closest('.general-actions').find('.action-list-menu').addClass('show');
    });

    $('.data-table').on('click', '.general-actions > a', function(e) {
        e.stopPropagation();
    });


    //When scrollbar hits the bottom then load the next set of results
    $(window).on('scroll', function() {
        if ($(window).scrollTop() + $(window).height() >= ($(document).height() - 20)) {
            if ($('.loader.loading').length == 0) {
                load_results_bottom();
            }
        }
    });

    $('.data-table').on('click', '.sortable', function(e) {
        var field = $(this).attr('data-field-header');
        var dir = $(this).attr('data-sort-order');
        var newDir = "";
        var extend = (e.ctrlKey) ? 1 : 0;

        if (!extend) {
            sorting = {};
        }

        //Determine new sorting direction
        if (!extend && $('.header-row th[data-sort-order]').length > 1) {
            newDir = 'asc';
            sorting[field] = newDir;
        } else if (dir && dir.toLowerCase() == 'asc') {
            newDir = 'desc';
            sorting[field] = newDir;
        } else if (dir && dir.toLowerCase() == 'desc') {
            newDir = 'reset';
            delete sorting[field];
        } else {
            newDir = 'asc';
            sorting[field] = newDir;
        }

        set_sorting(field, newDir, extend);
        $(this).closest('.header-row').find('th').removeAttr('data-sort-order');

        if (Object.keys(sorting).length) {
            for (var i in sorting) {
                $('.header-row th[data-field-header="' + i + '"]').attr('data-sort-order', sorting[i]);
            }
        } else if (Object.keys(defaultSorting).length) {
            for (var i in defaultSorting) {
                $('.header-row th[data-field-header="' + i + '"]').attr('data-sort-order',
                    defaultSorting[i]);
            }
        }

    });

    //Dropdown filter
    $('.listing-filters').on('change', '.dropdown-filter', function() {
        show_loader();
        var filters = get_filter_params();
        filters.section = section;
        ajax_post('ajax_apply_filters', filters, function(d) {
            if (d.success) {
                load_results_init();
            }
        });

    });

    //Autocomplete filter
    $('.listing-filters').on('keyup', '.autocomplete-textbox', function() {

        var name = $(this).attr('name');
        var value = $(this).val();
        filters = get_filter_params();
        filters.section = section;
        if (value.length >= 0) {
            clearTimeout(autoCompleteTimer);
            autoCompleteTimer = setTimeout(function() {
                ajax_post('ajax_apply_filters', filters, function(d) {
                    if (d.success) {
                        load_results_init();
                    }
                });
            }, 500);
        }
    });

    //Date filter
    $('.listing-filters').on('change', '.date-filter', function() {
        var date = $(this).val();
        filters = get_filter_params();
        filters.section = section;
        ajax_post('ajax_apply_filters', filters, function(d) {
            if (d.success) {
                load_results_init();
            }
        });
    });

    //Date range filter
    $('.listing-filters').on('change', '.date-range-filter input', function() {
        var from = $(this).closest('.date-range-filter').find('.filter-value-from').val();
        var to = $(this).closest('.date-range-filter').find('.filter-value-to').val();

        if (to != '' && from != '') {
            filters = get_filter_params();
            filters.section = section;
            ajax_post('ajax_apply_filters', filters, function(d) {
                if (d.success) {
                    load_results_init();
                }
            });
        }
    });

    //Number range filter
    $('.listing-filters').on('change', '.int-range', function() {
        var from = $(this).closest('.range-filter').find('.filter-value-from').val();
        var to = $(this).closest('.range-filter').find('.filter-value-to').val();
        if (to != '' && from != '') {
            filters = get_filter_params();
            filters.section = section;
            ajax_post('ajax_apply_filters', filters, function(d) {
                if (d.success) {
                    load_results_init();
                }
            });
        }
    });

    //Delete confirmation
    $('.data-table').on('click', '.delete-row', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var link = $(this).attr('href');
        var body =
            "<?= ! empty($this->confirmDelete) ? $this->confirmDelete : lang('confirm_delete_body'); ?>";
        var body = body.replace('{item}', $(this).closest('tr').attr('data-identifier'));

        Swal.fire({
            text: body,
            showCancelButton: true,
            confirmButtonColor: "#FC737A",
            confirmButtonText: '<?= lang('label_delete'); ?>',
            closeOnConfirm: false
        }).then(function(result) {
            if (result.isConfirmed) {
                window.location.href = link
            }
        });

    });

    //Disable confirmation
    $('.data-table').on('click', '.disable-row', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var link = $(this).attr('href');
        var body =
            "<?= ! empty($this->confirmDisable) ? $this->confirmDisable : lang('confirm_disable_body'); ?>";
        var body = body.replace('{item}', $(this).closest('tr').attr('data-identifier'));

        Swal.fire({
            text: body,
            showCancelButton: true,
            confirmButtonColor: "#FFCF74",
            confirmButtonText: '<?= lang('label_disable'); ?>',
            closeOnConfirm: false
        }).then(function(result) {
            if (result.isConfirmed) {
                window.location.href = link
            }
        });

    });

    //Enable confirmation
    $('.data-table').on('click', '.enable-row', function(e) {

        e.preventDefault();
        e.stopPropagation();

        var link = $(this).attr('href');
        var body =
            "<?= lang(! empty($this->confirmEnable) ? $this->confirmEnable : 'confirm_enable_body'); ?>";
        var body = body.replace('{item}', $(this).closest('tr').attr('data-identifier'));

        Swal.fire({
            text: body,
            showCancelButton: true,
            confirmButtonColor: "#59C4BC",
            confirmButtonText: '<?= lang('label_enable'); ?>',
            closeOnConfirm: false
        }).then(function(result) {
            if (result.isConfirmed) {
                window.location.href = link
            }
        });

    });

    //For opening and closing expanding list items
    $('.data-table').on('click', '.list-expander', function(e) {
        e.stopPropagation();
        if ($(this).hasClass('open')) {
            $(this).removeClass('open');
            $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
            $(this).closest('tr').next('tr.expanded-list-item').removeClass('open');
        } else {
            $(this).addClass('open');
            $(this).find('i').removeClass('fa-plus').addClass('fa-minus');
            $(this).closest('tr').next('tr.expanded-list-item').addClass('open');
        }
    });

    $('.data-table').on('click', '.listing-position', function(e) {
        e.stopPropagation();
    });

    <?php
    if ($this->quickManage == TRUE) {
    ?>

    <?php if (empty($this->fullEdit) || $this->fullEdit != TRUE) { ?>
    $('.data-table').on('click', '.edit-row', function(e) {
        e.stopPropagation();
        e.preventDefault();
        var id = $(this).closest('tr').attr('data-row-id');

        ajax_get('ajax_quick_manage/' + id, '', function(d) {
            if (d) {
                open_qm(d);
            }
        });
    });

    <?php } ?>

    if (window.location.hash.substring(1) == 'add') {
        ajax_get('ajax_quick_manage/', '', function(d) {
            if (d) {
                open_qm(d);
            }
        });
    }

    if (window.location.hash.substring(1) && window.location.hash.substring(1)?.match(/^edit/)?.length) {
        var s = window.location.hash.substring(1).split('/');

        if (s.length > 1) {
            ajax_get('ajax_quick_manage/' + s[1], '', function(d) {
                if (d) {
                    open_qm(d);
                }
            });
        }
    }

    $(document).on('click', ' .add-item', function() {
        ajax_get('ajax_quick_manage/', '', function(d) {
            if (d) {
                open_qm(d);
            }
        });
    });

    $(document).on('click', '.close-quick-manage', function() {
        close_qm();
    });

    $(window).on('hashchange', function() {
        window.location.hash = "";
        ajax_get('ajax_quick_manage/', '', function(d) {
            if (d) {
                open_qm(d);
            }
        });
    });
    <?php
    }
    ?>

});

function clear_filters() {

    $('.listing-filters .filter-value').val('');

    // Clear dropdowns
    $('.listing-filters .custom-select').multiselect('select', ['']);
    $('.listing-filters .multiselect-search').val('').trigger('keydown');
    $('.listing-filters .multiselect-container input[type="radio"]').prop('checked', false);

    filters = get_filter_params();
    filters.section = section;
    ajax_post('ajax_apply_filters', filters, function(d) {
        if (d.success) {
            load_results_init();
        }
    });
}
</script>