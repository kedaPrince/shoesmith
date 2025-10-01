<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Agency_jobs_listings extends CRUD_Controller
{
    public $pageName = 'agency_jobs_listings';
    public $group = 'Agency Jobs Listings';
    public $view = '';
    public $model = 'Model_agency_jobs_listings';
    public $sorting = array('name' => 'ASC');
    public $singular = 'agency jobs listing';
    public $plural = 'agency jobs listings';
    public $quickManage = true;
    public $identifierField = 'name';
    public $hideSubNav = true;
    public $seoFields = false;
    public $quickManageSize = 2;
    public $sluggify = true;

    public function __construct()
    {
        parent::__construct();

        // Access check
        if (!function_exists('getLoggedInUserTypeMenu')) {
            $ci = &get_instance();
            $ci->load->helper('profile_helper');
        }
        if (getLoggedInUserTypeMenu() === 'staff') {
            redir('dashboard');
        }

        $this->setup_listing();
        $this->setup_fields();
        $this->load->model($this->folder . '/' . $this->model);
        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }

    public function index()
{
    $this->breadcrumbs = array(
        array(
            'title' => lang($this->pageName . '_heading'),
            'url'   => redir($this->pageName, true),
        ),
    );
    $this->view = 'listing';
    $this->load->view($this->folder . '/view_header');
    $this->load->view('cms/crud/view_list', array(
        'heading' => lang($this->pageName . '_heading'),
        'noRows'  => lang($this->pageName . '_no_rows'),
    ));
    $this->load->view($this->folder . '/view_footer');
}

    private function setup_listing()
    {
        $this->listFields = array(
            'name' => array(
                'label' => lang('label_title'),
                'sort' => true,
            ),
        );

        $this->listActions = array(     
            'edit' => array(
                'label'     => lang('label_edit'),
                'url'       => url($this->pageName . '/edit/{id}'),
                'icon'      => 'fa-edit',
                'class'     => 'edit-row',
            ),
            'enable' => array(
                'label'     => lang('label_enable'),
                'url'       => url($this->pageName . '/enable/{id}'),
                'icon'      => 'fa-check',
                'class'     => 'enable-row',
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => url($this->pageName . '/disable/{id}'),
                'icon'      => 'fa-ban',
                'class'     => 'disable-row',
                'function'  => (function ($str, $row) {
                     return ($row->enabled) ? $str : false;
                }),
            ),
            'view' => array(
                'label'     => lang('label_view'),
                'url'       => url($this->pageName . '/ajax_view/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'vieww-row', // Do not use view-row, it disables target _blank
            ),
            'remove' => array(
                'label'     => lang('label_remove'),
                'url'       => url($this->pageName . '/remove/{id}'),
                'icon'      => 'fa-trash',
                'class'     => 'remove-row',
            ),
        );
    }

    private function setup_fields()
    {
        $this->fields = array(
            'name' => array(
                'label'         => lang('label_title'),
                'type'          => 'text',
                'placeholder'   => lang('label_title'),
                'class'         => 'form-control',
                'required'      => true,
                'help'          => lang('help_title'),
            ),
            'description' => array(
                'label'         => lang('label_description'),
                'type'          => 'textarea',
                'placeholder'   => lang('label_description'),
                'class'         => 'form-control',
                'rows'          => 4,
                'help'          => lang('help_description'),
            ),
            'enabled' => array(
                'label'         => lang('label_enabled'),
                'type'          => 'checkbox',
                'class'         => '',
                'default'       => 1,
                'help'          => lang('help_enabled'),
            ),
        );
    }   
}