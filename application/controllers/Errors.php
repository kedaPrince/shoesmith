<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Errors extends My_Controller {
    public $pageName = 'error';

    public function __construct() {
        parent::__construct();

    }

    public function error404() {
        $group = current_login_group();

        $loginGroups = $this->config->item('login_groups');

        if (!empty($loginGroups[$group]['default_url'])) {
            $homeURL = site_url().$loginGroups[$group]['default_url'];
        }
        else {
            $homeURL = site_url();
        }

        $pageMeta['seo_title'] = $this->config->item('site_name').' | 404';
        $this->load->view('front/view_header', array(
            'pageName'      => $this->pageName,
            'pageMeta'      => $pageMeta
        ));
        $this->load->view('front/errors/view_error_404', array(
            'homeURL'       => $homeURL
        ));
        $this->load->view('front/view_footer', array(
            'pageName'      => $this->pageName,
        ));
    }

}
