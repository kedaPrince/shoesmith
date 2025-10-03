<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Agencies extends CRUD_Controller
{
    public $pageName = 'agencies';
    public $group = 'Agencies';
    public $view = '';
    public $model = 'Model_agencies';
    public $sorting = array('name' => 'ASC');
    public $singular = 'Agency';
    public $plural = 'Agencies';
    public $quickManage = true;
    public $identifierField = 'name';
    public $hideSubNav = true;
    public $seoFields = false;
    public $quickManageSize = 4;
    public $sluggify = true;
    public $allowEdit = true;
    public $rowClick = 'edit-row';
    public $adding = true;

    public function __construct()
    {
        parent::__construct();

        if (!function_exists('getLoggedInUserType')) {
            $this->load->helper('profile_helper');
        }
        $this->load->helper('profile_helper');
        $userType = getLoggedInUserType();
        $this->allowEdit = in_array($userType, ['Super Admin', 'General Admin']);
        $this->rowClick = $this->allowEdit ? 'edit-row' : 'vieww-row';
        if (!$this->allowEdit) {
            $this->adding = false;
        }

        $this->load->model($this->folder . '/' . $this->model);
        $this->setup_listing();
        $this->setup_fields();

        $this->zone = array(
            'title' => lang($this->pageName . '_heading'),
            'url' => redir($this->pageName, true),
        );
    }

    private function setup_listing()
    {
        $this->listFields = array(
            'name' => array(
                'label' => lang('label_company_name'),
                'sort' => true,
            ),
            'email' => array(
                'label' => lang('label_email'),
                'sort' => true,
            ),
            'phone' => array(
                'label' => lang('label_telephone'),
                'sort' => true,
            ),
            'industry' => array(
                'label' => lang('label_industry'),
                'sort' => true,
            ),
        );

        $this->listActions = array(
          
            'edit' => array(
                'label'     => lang('label_edit'),
                'url'       => url($this->pageName . '/edit/{id}'),
                'icon'      => 'fa-edit',
                'class'     => 'edit-row',
                'function'  => function ($str, $row) {
                    return (!$this->allowEdit) ? false : $str;
                },
            ),
            'enable' => array(
                'label'     => lang('label_enable'),
                'url'       => url($this->pageName . '/enable/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'enable-row btn-enable',
                'function'  => function ($str, $row) {
                    return (!$this->allowEdit || $row->enabled) ? false : $str;
                },
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => url($this->pageName . '/disable/{id}'),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => function ($str, $row) {
                    return (!$this->allowEdit || !$row->enabled) ? false : $str;
                },
            ),
            'delete' => array(
                'label'     => lang('label_delete'),
                'url'       => url($this->pageName . '/remove/{id}'),
                'icon'      => 'fa-trash-o',
                'class'     => 'delete-row btn-delete',
                'function'  => function ($str, $row) {
                    return (!$this->allowEdit) ? false : $str;
                },
            ),
        );

        $this->filters = array(
            'general' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array('agencies.name'),
            ),
        );
    }

    public function setup_fields()
    {
        $this->formFields = array(
            'main' => array(
                'name'                  => 'trim|required|strip_tags',
                'slug'                  => 'trim|strip_tags',
                'registration_number'   => 'trim|strip_tags',
                'vat_number'            => 'trim|strip_tags',
                'email'                 => 'trim|valid_email',
                'phone'                 => 'trim|strip_tags',
                'address'               => 'trim',
                'billing_contact'       => 'trim|strip_tags',
                'industry'              => 'trim|strip_tags',
                'website'               => 'trim|callback_valid_website',
                'logo'                  => 'trim|strip_tags',
            ),
        );


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
        // Agencies have no user-related data
        return array();
    }

    public function ajax_view($id): void
    {
        $agency = $this->{$this->model}->get_by_id((int)$id);
        $identifier = $agency ? $agency->name : '';

        $data = array(
            'heading'    => lang($this->pageName . '_heading'),
            'identifier' => $identifier,
            'noRows'     => lang($this->pageName . '_no_rows'),
            'agency'     => $agency,
        );

        $html = $this->load->view($this->folder . '/' . $this->pageName . '/ajax_view_single', $data, true);
        $this->output->set_output($html);
    }

    public function valid_website($url)
    {
        if (empty($url)) {
            return TRUE; // Allow empty
        }

        // Auto-prepend https:// if no protocol
        if (!preg_match('~^(http|https)://~i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        // Validate as URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            // Save the corrected URL back to POST
            $_POST['website'] = $url;
            return TRUE;
        }

        $this->form_validation->set_message('valid_website', 'The {field} field must be a valid URL.');
        return FALSE;
    }


}