#!/bin/bash

page="$1"
page="${page// /$'_'}"

singular="$2"

group="$3"

echo "Creating folder ${page}..."

mkdir ../application/views/admin/$page

echo "Creating ajax_manage..."

touch ../application/views/admin/$page/ajax_manage.php


echo "<?php
 defined('BASEPATH') || exit('No direct script access allowed');  ?>
 <a class=\"close-quick-manage\"><i class=\"fa fa-times\"></i></a>
 <div class=\"quick-manage-form-container qmfc\">
     <div class=\"quick-manage-heading\">
         <?PHP
         if(!empty(\$row->id)) {
         ?>
             <h2>Edit <?= \$this->singular ?> <span><?= \$row->title ?></span></h2>
         <?PHP
         } else {
         ?>
             <h2>Add <?= \$this->singular ?></h2>
         <?PHP
         }
         ?>
     </div>
     <div class=\"form-field-container\">
         <?= form_open(); ?>
         <?= form_hidden('id', !empty(\$row->id) ? \$row->id : 0); ?>
         <div class=\"row\">
             <div class=\"col-lg-8\">
                 <?= field_input('name', \$row, 'required'); ?>
             </div>
         </div>

         <?PHP
         if (\$this->seoFields) {
         ?>
             <h2>SEO Fields</h2>
             <div class=\"row\">
                 <div class=\"col-lg-6\">
                     <?= field_input('seo_title', \$row, ''); ?>
                 </div>
                 <div class=\"col-lg-6\">
                     <?= field_input('seo_keywords', \$row, ''); ?>
                 </div>
                 <div class=\"col-lg-12\">
                     <?= field_textarea('seo_description', \$row, ''); ?>
                 </div>
             </div>
         <?PHP
         }
         ?>
         <div class=\"btn-container\" style=\"clear: left;\">
             <?php
             echo save_button('Save and Close');
             echo cancel_button('Close', 'left');
             ?>
         </div>
         <?= form_close(); ?>
     </div>
 </div>
 <script type=\"text/javascript\">
     function save_form(el) {
         \$(el).closest('form').parsley().whenValidate().done(function() {
             var view = '<?=!empty(\$row->id) ? 'update' : 'create'?>';
             var id = <?=!empty(\$row->id) ? \$row->id : '0'?>;

             ajax_submit_form(el, view, id);
         });
     }

     if (typeof setup_image_fields === 'function') {
         setup_image_fields();
     }
 </script>

"> ../application/views/admin/$page/ajax_manage.php


echo "Creating controller ${page^}..."

touch ../application/controllers/admin/${page^}.php

echo "<?php
 defined('BASEPATH') or exit('No direct script access allowed');

 class ${page^} extends CRUD_Controller {
 	public \$pageName = '${page}';
 	public \$group = '${group}';
 	public \$view = '';
 	public \$model = 'Model_${page}';
 	public \$sorting = array('name' => 'ASC');
 	public \$singular = '${singular}';
 	public \$plural = '${page}';
 	public \$seoFields = true;
 	public \$quickManage = true;
 	public \$quickManageSize = 2;
 	public \$sluggify = true;

 	public function __construct() {
 		parent::__construct();

 		\$this->setup_listing();
 		\$this->setup_fields();
 		\$this->load->model(\$this->folder . '/' . \$this->model);
 		\$this->zone = array(
 			'title' => lang(\$this->pageName . '_heading'),
 			'url' => redir(\$this->pageName, true)
 		);
 	}

 	private function setup_listing() {
 		\$this->listFields = array(
 			'name' => array(
 				'label' => lang('label_title'),
 				'sort' => true
 			)
 		);

        \$this->listActions = array(
           'edit' => array(
               'label'     => lang('label_edit'),
               'url'       => url(\$this->pageName . '/edit/{id}'),
               'icon'      => 'fa-edit',
               'class'     => 'edit-row',
           ),
           'enable' => array(
               'label'     => lang('label_enable'),
               'url'       => url(\$this->pageName . '/enable/{id}'),
               'icon'      => 'fa-eye',
               'class'     => 'enable-row btn-enable',
               'function'  => (function (\$str, \$row) {
                   return (\$row->enabled) ? false : \$str;
               })
           ),
           'disable' => array(
               'label'     => lang('label_disable'),
               'url'       => url(\$this->pageName . '/disable/{id}'),
               'icon'      => 'fa-eye-slash',
               'class'     => 'disable-row btn-disable',
               'function'  => (function (\$str, \$row) {
                   return (!\$row->enabled) ? false : \$str;
               })
           ),
           'delete' => array(
               'label'     => lang('label_delete'),
               'url'       => url(\$this->pageName . '/remove/{id}'),
               'icon'      => 'fa-trash-o',
               'class'     => 'delete-row btn-delete',
           )
        );

 		\$this->filters = array(
 			//dropdown filter
 			'general' => array(
 				'label' => lang('label_search'),
 				'type' => 'autocomplete',
 				'field' => array(
 					'mod_${page}.name'
 				)
 			)
 		);
 	}

 	public function setup_fields() {
 		\$this->formFields = array(
 			'main' => array(
 				'name' => 'trim|required|strip_tags'
 			)
 		);
 		if (\$this->seoFields) {
 			\$this->formFields['main']['seo_title'] = 'trim|strip_tags';
 			\$this->formFields['main']['seo_description'] = 'trim|strip_tags';
 			\$this->formFields['main']['seo_keywords'] = 'trim|strip_tags';
 		}
 		\$this->formLabels = array();
 	}

 	public function index() {
 		\$this->breadcrumbs = array(
 			array(
 				'title' => lang(\$this->pageName . '_heading'),
 				'url' => redir(\$this->pageName, true)
 			)
 		);
 		\$this->view = 'listing';
 		\$this->load->view(\$this->folder . '/' . 'view_header');
 		\$this->load->view('cms/crud/view_list', array(
 			'heading' => lang(\$this->pageName . '_heading'),
 			'noRows' => lang(\$this->pageName . '_no_rows')
 		));
 		\$this->load->view(\$this->folder . '/' . 'view_footer');
 	}
 }
"> ../application/controllers/admin/${page^}.php


echo "Creating model ${page}..."

touch ../application/models/admin/Model_${page}.php

echo "<?php
class Model_${page} extends CRUD_Model {
	protected \$table = 'mod_${page}';
}
"> ../application/models/admin/Model_${page}.php

echo "Add general_labels..."

FILE=../application/language/english/general_lang.php

LINE_NUMBER=2

LANG="general_labels(\$lang, '${page}', '${singular}', '${page}');"

sed -i "$((LINE_NUMBER+1))i ${LANG}" "$FILE"

echo "Add DB table..."

PHP_FILE="../application/config/development/database.php"

HOST=$(grep -oP "'hostname'\s*=>\s*'\K[^']+" "$PHP_FILE")
USERNAME=$(grep -oP "'username'\s*=>\s*'\K[^']+" "$PHP_FILE")
PASSWORD=$(grep -oP "'password'\s*=>\s*'\K[^']+" "$PHP_FILE")
DATABASE=$(grep -oP "'database'\s*=>\s*'\K[^']+" "$PHP_FILE")

php ./create_table.php "mod_${page}" "${HOST}" "${USERNAME}" "${PASSWORD}" "${DATABASE}"




