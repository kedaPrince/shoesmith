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

        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card ecms-single">
                    <div class="body">
                        <aside class="quick-manage-container"></aside>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        ajax_get('ajax_quick_manage/<?= (!empty($id)?$id:0); ?>', '', function (d) {
            if (d) {
                open_qm(d);
                $('body').addClass('qm-full-page');
            }
        });
    });
</script>