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

        // Load model BEFORE calling setup_fields()
        $this->load->model($this->folder . '/' . $this->model);

        // Now safe to call setup methods
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
                'label' => lang('label_company'),
                'sort' => true,
            ),
            'email' => array(
                'label' => lang('label_email'),
                'sort' => true,
            ),
            'access_groups' => array(
                'label' => lang('label_access_groups'),
                'sort' => false,
                'field' => 'access_groups',
                'type' => 'custom'
            ),
             'agency_type' => array(
                'label' => lang('label_agency_types'),
                'type' => 'custom', // Use 'custom' instead of 'field'
                'function' => function($value, $row) {
                    return !empty($row->agency_type) ? $row->agency_type : '-';
                },
                'sort' => false,
        ),
        );

        $this->listActions = array(
            'view' => array(
                'label'     => lang('label_view'),
                'url'       => url($this->pageName . '/ajax_view/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'vieww-row',
            ),
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
                'generate_index_page'   => 'trim|strip_tags|numeric',
                'generate_references'   => 'trim|strip_tags|numeric',
                'name'                  => 'trim|required|strip_tags',
                'agency_type_id'        => 'trim|strip_tags|numeric',
            ),
            'multi_selects' => array(
            'agency_access_groups' => array(  // ← matches field name
                'validation'        => 'trim',
                'pivot_table'       => 'pivot_agency_access_groups',
                'main_field'        => 'agency_id',
                'link_field'        => 'access_group_id',
            ),
        ),
            'resources' => array(
                'resource_id'           => 'trim|required|strip_tags|numeric',
                'resource_type_id'      => 'trim|required|strip_tags|numeric',
            ),
            // 'dynamic_fields' => array(
            //     'items' => array(
            //         'table' => 'pivot_agency_agency_sections',
            //         'parent_field' => 'agency_id',
            //         'fields' => array(
            //             'agency_section_id',
            //             'position',
            //         )
            //     )
            // )
        );

        // Get dropdown options from the model
        $usrTypeOptions = $this->{$this->model}->get_usr_type_options();

        $this->formLabels = array(
            'agency_type_id' => array(
                'label' => lang('label_agency_types'),
                'type' => 'dropdown',
                'options' => $usrTypeOptions,
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
    $access_groups = [];
    if ($id && is_numeric($id) && $id > 0) {
        $access_groups = $this->{$this->model}->get_access_groups_all((int)$id);
    }
    // Ensure it's always an array
    if (!is_array($access_groups)) {
        $access_groups = [];
    }

    return [
        'access_groups'       => $access_groups,
        'access_groups_all'   => $this->{$this->model}->get_access_groups_all(),
        'usr_type_options'    => $this->{$this->model}->get_usr_type_options(),
    ];
}

    public function ajax_view($id): void
    {
        $accessibleAgencyIds = $this->{$this->model}->get_accessible_agency_ids();
        $this->quickManageSize = 5;
        $agencySections = $this->{$this->model}->get_agency_view((int)$id);
        $agency = $this->{$this->model}->get_by_id((int)$id);
        $identifier = $agency ? $agency->name : '';

        $data = array(
            'access_allowed'    => empty($accessibleAgencyIds) || in_array((int)$id, $accessibleAgencyIds),
            'heading'           => lang($this->pageName . '_heading'),
            'identifier'        => $identifier,
            'noRows'            => lang($this->pageName . '_no_rows'),
            'document_index'    => $this->generate_document_index($agencySections),
            'agency'            => $agency,
            'agency_sections'   => $agencySections,
        );

        $html = $this->load->view($this->folder . '/' . $this->pageName . '/ajax_view_single', $data, true);
        $this->output->set_output($html);
    }

    public function ajax_get_available_agency_sections(string $id, bool $getResult = true): void
    {
        $result = array(
            'error'     => false,
            'message'   => '',
            'success'   => false,
            'content'   => [],
        );
        if (empty($id) || !is_numeric($id)) {
            $result['error'] = true;
            $result['message'] = 'Agency ID is empty or not a number';
        } else {
            $result['content'] = (array)$this->{$this->model}->get_available_agency_sections((int)$id, $getResult);
            $result['success'] = true;
        }
        ajax_return($result);
    }

    private function generate_document_index(array $agencySections): array
    {
        $documentIndex = [];
        foreach ($agencySections as $row) {
            if (empty($row->content)) continue;

            $posAStart = strpos($row->content, '<a');
            if ($posAStart === false) continue;

            while ($posAStart !== false) {
                $posAEnd = strpos($row->content, '</a>', $posAStart);
                if ($posAEnd === false) break;

                $tag = substr($row->content, $posAStart, $posAEnd - $posAStart + 4);
                $text = strtoupper(trim(strip_tags($tag)));

                $posHrefStart = strpos($tag, 'href="');
                if ($posHrefStart === false) {
                    $posAStart = strpos($row->content, '<a', $posAStart + 1);
                    continue;
                }

                $posHrefEnd = strpos($tag, '"', $posHrefStart + 6);
                if ($posHrefEnd === false || $posHrefEnd <= $posHrefStart + 6) {
                    $posAStart = strpos($row->content, '<a', $posAStart + 1);
                    continue;
                }

                $url = substr($tag, $posHrefStart + 6, $posHrefEnd - ($posHrefStart + 6));

                if (empty($url) || substr($url, 0, 7) === 'mailto:' || strpos($url, '@') !== false) {
                    $posAStart = strpos($row->content, '<a', $posAStart + 1);
                    continue;
                }

                $documentIndex[$url] = $text;
                $posAStart = strpos($row->content, '<a', $posAEnd + 4);
            }
        }
        return $documentIndex;
    }

    public function process_dynamic_fields($parentID): array
    {
        $dfData = array();
        if (!empty($this->formFields['dynamic_fields'])) {
            foreach ($this->formFields['dynamic_fields'] as $field => $data) {
                $dfData[$field] = $this->{$this->model}->save_dynamic_fields($field, $data['table'], $data['parent_field'], $parentID);
                // Upload handling remains as-is (not shown for brevity)
            }
        }
        return $dfData;
    }

    public function ajax_get_available_layouts($playbook_id = 0)
    {
        $result = [
            'error' => false,
            'message' => '',
            'success' => false,
            'content' => []
        ];
        $layouts = $this->{$this->model}->get_all_mod_layouts();
        if (!empty($layouts)) {
            $result['content'] = $layouts;
            $result['success'] = true;
        } else {
            $result['message'] = 'No layouts found.';
            $result['success'] = true;
        }
        ajax_return($result);
    }
}