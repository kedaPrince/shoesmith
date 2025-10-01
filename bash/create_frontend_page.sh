#!/bin/bash

page="$1"
page="${page// /$'_'}"
view="view_${page}.php"
controller="${page^}.php"

folder="${2:-front}"
folder="${folder// /$'_'}"


echo "Creating folder ${page}..."

mkdir ../application/views/$folder/$page

echo "Creating view file ${view}..."

touch ../application/views/$folder/$page/$view

echo "
<h1>$1 page</h1>
"> ../application/views/$folder/$page/$view

echo "Creating SCSS file ${view}..."

touch ../application/views/$folder/$page/style.scss

echo "Add SCSS to Grunt..."

FILE=../Gruntfile.js

LINE_NUMBER=10

SCSS="application/views/${folder}/${page}/style.scss"

sed -i "$((LINE_NUMBER+1))i '${SCSS}'," "$FILE"


echo "Creating model ${page}..."

touch ../application/models/$folder/Model_$page.php

echo "Populate model ${page}..."

echo "<?php

      class Model_${page} extends Front_model {


      }


"> ../application/models/$folder/Model_$page.php


echo "Creating controller ${controller}..."

touch ../application/controllers/$folder/$controller

echo "Populate controller ${controller}..."

echo "<?php
  defined('BASEPATH') or exit('No direct script access allowed');

  class ${page^} extends Front_Controller {
  	public \$pageName = '${page}';
  	public \$model = 'Model_${page}';
  	public \$folder = '${folder}';

  	public function __construct() {
  		parent::__construct();
  		\$this->load->model(\$this->folder . '/' .\$this->model);
  	}

  	public function index() {
  		\$page_meta['seo_title'] = 'Site Title';
  		\$page_meta['seo_keywords'] = 'Site Keywords';
  		\$page_meta['seo_description'] = 'Site Description';

        \$resources = [
          '<link rel=\"stylesheet\" type=\"text/css\" href=\"'.site_url().'resources/front/css/${page}/style.min.css?version='.\$this->config->item('version').'\">'
        ];

        \$this->load->view(\$this->folder . '/view_header', array(
          'page_meta' => \$page_meta,
          'resources' => \$resources
        ));
  		\$this->load->view(\$this->folder . '/${page}/view_${page}', array());
  		\$this->load->view(\$this->folder . '/view_footer');
  	}
  }

"> ../application/controllers/$folder/$controller

