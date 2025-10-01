<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Access_groups extends CRUD_Controller
{
    public $pageName = 'access_groups';
    public $group = 'Access Groups';
    public $view = '';
    public $model = 'Model_access_groups';
    public $sorting = array('name' => 'ASC');
    public $singular = 'access group';
    public $plural = 'access groups';
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
                'icon'      => 'fa-eye',
                'class'     => 'enable-row btn-enable',
                'function'  => (function ($str, $row) {
                    return ($row->enabled) ? false : $str;
                }),
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => url($this->pageName . '/disable/{id}'),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => (function ($str, $row) {
                    return (!$row->enabled) ? false : $str;
                }),
            ),
            'delete' => array(
                'label'     => lang('label_delete'),
                'url'       => url($this->pageName . '/remove/{id}'),
                'icon'      => 'fa-trash-o',
                'class'     => 'delete-row btn-delete',
            ),
        );

        $this->filters = array(
            //dropdown filter
            'general' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array(
                    'mod_access_groups.name',
                ),
            ),
        );
    }

    public function setup_fields()
    {
        $this->formFields = array(
            'main' => array(
                'name' => 'trim|required|strip_tags',
            ),
            'multi_selects' => array(
                'admin_id' => array(
                    'validation'  => 'trim|required',
                    'pivot_table' => 'pivot_admin_access_groups',
                    'main_field'  => 'access_group_id',
                    'link_field'  => 'admin_id',
                ),
            ),
        );

        $this->formLabels = array();
    }

    public function index()
    {
        $this->breadcrumbs = array(
            array(
                'title' => lang($this->pageName . '_heading'),
                'url' => redir($this->pageName, true),
            ),
        );
        $this->view = 'listing';
        $this->load->view($this->folder . '/' . 'view_header');
        $this->load->view('cms/crud/view_list', array(
            'heading' => lang($this->pageName . '_heading'),
            'noRows' => lang($this->pageName . '_no_rows'),
        ));
        $this->load->view($this->folder . '/' . 'view_footer');
    }

    public function quick_manage_extra($id, $row): array
    {
        return array(
            'admins_all'    => $this->{$this->model}->get_admins_all(),
            'admins'       => $this->{$this->model}->get_admins((int)$id),
        );
    }
}
