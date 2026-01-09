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
                                class="table table-hover js-basic-example dataTable table-custom m-b-0 <?= $tableClasses; ?>"
                                data-total-items="<?php echo isset($total_items) ? $total_items : 0; ?>">
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

                        <!-- ========== PAGINATION SECTION ========== -->
                        <div class="pagination-container" style="padding: 20px 0; border-top: 1px solid #dee2e6;">
                            <div id="fallback-pagination" style="display: none;">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mb-2">
                                        <!-- Pagination links will be inserted here by JavaScript -->
                                    </ul>
                                </nav>
                                <div class="text-center text-muted" id="pagination-info"></div>
                            </div>
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

// ========== PAGINATION VARIABLES ==========
var currentPage = 1;
var totalPages = 1;
var totalItems = 0;
var itemsPerPage = 10; // Should match $this->perPage in controller
// ========== END PAGINATION VARIABLES ==========

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

    // ========== INITIALIZE PAGINATION ==========
    setTimeout(function() {
        setupPagination();
    }, 500);

    $(document).on('batchLoaded', function() {
        $('[data-toggle="listing-tt"]').tooltip({
            trigger: 'hover'
        });

        // Update pagination after batch loads
        setTimeout(updatePaginationInfo, 100);
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

    // Additional functionality: Click anywhere on job row to view (while keeping action buttons)
    $('.data-table').on('click', 'tr', function(e) {
        // Don't trigger if clicking on action buttons or expanded items
        if (!$(this).hasClass('expanded-list-item') &&
            !$(e.target).closest('a, button, .btn, .action-buttons, .no-click').length) {

            // Find the view button specifically for jobs
            var viewButton = $(this).find('.view-row');
            if (viewButton.length && viewButton.attr('href') && viewButton.attr('href').includes(
                    '/jobs/')) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = viewButton.attr('href');
            }
        }
    });

    // Apply red styling to expired jobs
    function styleExpiredJobs() {
        $('.data-table tbody tr').each(function() {
            var $row = $(this);
            var $closingDateCell = $row.find('td').eq(
                3); // Adjust index based on closing date column position

            // Check if this is an expired job (has danger class or exclamation icon in closing date)
            var isExpired = $closingDateCell.find('.text-danger').length > 0 ||
                $closingDateCell.find('.fa-exclamation-circle').length > 0;

            if (isExpired) {
                $row.addClass('disabled')
                    .css({
                        'background-color': 'rgb(185 0 0 / 35%)',
                        'border-left': '4px solid #dc3545'
                    });
            }
        });
    }

    // Apply styling when page loads and when new batches are loaded
    $(document).ready(function() {
        styleExpiredJobs();
    });

    $(document).on('batchLoaded', function() {
        setTimeout(styleExpiredJobs, 100);
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
                // Reset to page 1 when filters change
                currentPage = 1;
                setTimeout(renderPagination, 500);
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
                        // Reset to page 1 when filters change
                        currentPage = 1;
                        setTimeout(renderPagination, 500);
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
                // Reset to page 1 when filters change
                currentPage = 1;
                setTimeout(renderPagination, 500);
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
                    // Reset to page 1 when filters change
                    currentPage = 1;
                    setTimeout(renderPagination, 500);
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
                    // Reset to page 1 when filters change
                    currentPage = 1;
                    setTimeout(renderPagination, 500);
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

// ========== PAGINATION FUNCTIONS ==========
function setupPagination() {
    console.log('Setting up pagination...');

    // Check current page from URL
    var urlParams = new URLSearchParams(window.location.search);
    var pageParam = urlParams.get('page');

    if (pageParam) {
        currentPage = parseInt(pageParam);
    }

    // Get total items
    getTotalCount();
}

function getTotalCount() {
    // Get total from data attribute
    var table = document.querySelector('.data-table[data-total-items]');

    if (table) {
        totalItems = parseInt(table.getAttribute('data-total-items')) || 0;
        calculatePagination();
        renderPagination();
    } else {
        // Try to get from visible rows
        setTimeout(function() {
            var rows = document.querySelectorAll('.data-table tbody tr:not(.expanded-list-item)');
            totalItems = rows.length;

            if (totalItems > 0) {
                calculatePagination();
                renderPagination();
            }
        }, 1000);
    }
}

function calculatePagination() {
    if (totalItems > 0 && itemsPerPage > 0) {
        totalPages = Math.ceil(totalItems / itemsPerPage);
    }
}

function renderPagination() {
    if (totalPages <= 1) {
        // Hide pagination if only one page
        document.querySelector('.pagination-container').style.display = 'none';
        return;
    }

    document.querySelector('.pagination-container').style.display = 'block';

    var paginationHTML = generatePaginationHTML();
    var paginationUl = document.querySelector('#fallback-pagination .pagination');

    if (paginationUl) {
        paginationUl.innerHTML = paginationHTML;
        document.getElementById('fallback-pagination').style.display = 'block';

        // Update info text
        updatePaginationInfo();

        // Attach click handlers
        attachPaginationClickHandlers();
    }
}

function generatePaginationHTML() {
    var html = '';

    // Previous button
    if (currentPage > 1) {
        html += '<li class="page-item"><a class="page-link pagination-link" href="#" data-page="' + (currentPage - 1) +
            '">&laquo; Previous</a></li>';
    } else {
        html += '<li class="page-item disabled"><span class="page-link">&laquo; Previous</span></li>';
    }

    // Page numbers
    var startPage = Math.max(1, currentPage - 2);
    var endPage = Math.min(totalPages, currentPage + 2);

    // First page
    if (startPage > 1) {
        html += '<li class="page-item"><a class="page-link pagination-link" href="#" data-page="1">1</a></li>';
        if (startPage > 2) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Middle pages
    for (var i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
            html += '<li class="page-item active"><span class="page-link">' + i + '</span></li>';
        } else {
            html += '<li class="page-item"><a class="page-link pagination-link" href="#" data-page="' + i + '">' + i +
                '</a></li>';
        }
    }

    // Last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        html += '<li class="page-item"><a class="page-link pagination-link" href="#" data-page="' + totalPages + '">' +
            totalPages + '</a></li>';
    }

    // Next button
    if (currentPage < totalPages) {
        html += '<li class="page-item"><a class="page-link pagination-link" href="#" data-page="' + (currentPage + 1) +
            '">Next &raquo;</a></li>';
    } else {
        html += '<li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>';
    }

    return html;
}

function attachPaginationClickHandlers() {
    document.querySelectorAll('.pagination-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var page = parseInt(this.getAttribute('data-page'));

            if (page && page !== currentPage) {
                goToPage(page);
            }
        });
    });
}

function goToPage(page) {
    // Update URL
    var url = new URL(window.location);
    url.searchParams.set('page', page);
    window.history.pushState({}, '', url);

    // Update current page
    currentPage = page;

    // Clear table and reload
    $('.data-table tbody').empty();
    load_results_init();

    // Update pagination
    setTimeout(renderPagination, 500);
}

function updatePaginationInfo() {
    var infoDiv = document.getElementById('pagination-info');
    if (infoDiv && totalItems > 0) {
        var start = ((currentPage - 1) * itemsPerPage) + 1;
        var end = Math.min(currentPage * itemsPerPage, totalItems);
        infoDiv.innerHTML = 'Showing ' + start + ' to ' + end + ' of ' + totalItems + ' candidates';
    }
}

// Handle browser back/forward buttons
window.addEventListener('popstate', function() {
    var urlParams = new URLSearchParams(window.location.search);
    var pageParam = urlParams.get('page');

    if (pageParam) {
        var newPage = parseInt(pageParam);
        if (newPage !== currentPage) {
            currentPage = newPage;
            $('.data-table tbody').empty();
            load_results_init();
            setTimeout(renderPagination, 500);
        }
    }
});

// Modify get_filter_params to include page
var originalGetFilterParams = get_filter_params;
get_filter_params = function() {
    var filters = originalGetFilterParams();
    filters.page = currentPage;
    return filters;
};

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
            // Reset to page 1 when clearing filters
            currentPage = 1;
            load_results_init();
            setTimeout(renderPagination, 500);
        }
    });
}
</script>
<style>
/* Add pointer cursor for clickable job rows */
.data-table tbody tr {
    cursor: pointer;
}

/* Pointer cursor for action buttons */
.data-table tbody tr .btn,
.data-table tbody tr a,
.data-table tbody tr button,
.data-table tbody tr .action-buttons {
    cursor: pointer !important;
}

/* Keep default cursor for expanded items only */
.data-table tbody tr.expanded-list-item {
    cursor: default !important;
}

/* Pagination styles */
.pagination {
    margin: 0;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.pagination .page-link {
    color: #007bff;
    padding: 6px 12px;
    margin: 0 2px;
    border: 1px solid #dee2e6;
    border-radius: 3px;
}

.pagination .page-link:hover {
    background-color: #e9ecef;
    text-decoration: none;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
}

.pagination-container {
    padding: 20px 0;
    border-top: 1px solid #dee2e6;
}

#pagination-info {
    font-size: 0.9em;
    color: #6c757d;
}
</style>