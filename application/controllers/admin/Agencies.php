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

        // Get edit allowed permission
        if (!function_exists('getLoggedInUserTypeMenu')) {
            $this->load->helper('profile_helper');
        }
        $this->load->helper('profile_helper');
        $userType = getLoggedInUserType();
        $this->allowEdit = in_array($userType, ['Super Admin', 'General Admin']);
        $this->rowClick = $this->allowEdit ? 'edit-row' : 'vieww-row';
        if (!$this->allowEdit) {
            $this->adding = false;
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
             'access_groups' => array(
                 'label' => lang('label_access_groups'),
                 'sort' => false,
                 'field' => 'access_groups',
                 'type' => 'custom'
            ),
            'agency_type' => array(
                'label' => 'Agency Type',
                'type' => 'field',
                'field' => 'mod_agency_types.name',
                'sort' => true,
            ),
        );

        $this->listActions = array(
            'view' => array(
                'label'     => lang('label_view'),
                'url'       => url($this->pageName . '/ajax_view/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'vieww-row', // Do not use view-row, it disables target _blank
            ),
            'edit' => array(
                'label'     => lang('label_edit'),
                'url'       => url($this->pageName . '/edit/{id}'),
                'icon'      => 'fa-edit',
                'class'     => 'edit-row',
                'function'  => (function ($str, $row) {
                    return (!$this->allowEdit) ? false : $str;
                }),
            ),
            'enable' => array(
                'label'     => lang('label_enable'),
                'url'       => url($this->pageName . '/enable/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'enable-row btn-enable',
                'function'  => (function ($str, $row) {
                    return (!$this->allowEdit || $row->enabled) ? false : $str;
                }),
            ),
            'disable' => array(
                'label'     => lang('label_disable'),
                'url'       => url($this->pageName . '/disable/{id}'),
                'icon'      => 'fa-eye-slash',
                'class'     => 'disable-row btn-disable',
                'function'  => (function ($str, $row) {
                    return (!$this->allowEdit || !$row->enabled) ? false : $str;
                }),
            ),
            'delete' => array(
                'label'     => lang('label_delete'),
                'url'       => url($this->pageName . '/remove/{id}'),
                'icon'      => 'fa-trash-o',
                'class'     => 'delete-row btn-delete',
                'function'  => (function ($str, $row) {
                    return (!$this->allowEdit) ? false : $str;
                }),
            ),
        );

        $this->filters = array(
            //dropdown filter
            'general' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array(
                    'mod_agencies.name',
                ),
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
                'agency_type_id'      => 'trim|strip_tags|numeric',
            ),
            'multi_selects' => array(
                'agency_access_groups' => array(
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
            'dynamic_fields' => array(
                'items' => array(
                    'table' => 'pivot_agency_agency_sections',
                    'parent_field' => 'agency_id',
                    'fields' => array(
                        'agency_section_id',
                        'position',
                    )
                )
            )
        );

        $this->formLabels = array();
    }

    public function index()
    {
//        debug_listing();
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
        $this->load->view($this->folder . '/' . $this->pageName . '/view_list_custom');
    }

   public function quick_manage_extra($id, $row): array
    {
        return array(
            'access_groups'                 => is_bool($id) ? [] : $this->{$this->model}->get_access_groups((int)$id),
            'access_groups_all'             => $this->{$this->model}->get_access_groups_all(),
            'available_agency_sections'   => $this->{$this->model}->get_available_agency_sections((int)$id, false),
            'agency_sections'             => is_bool($id) ? [] : $this->{$this->model}->get_agency_view((int)$id),
            'layouts'                       => $this->{$this->model}->get_all_mod_layouts()
        );
    }

    public function ajax_view($id): void
    {
        $accessibleAgencyIds = $this->{$this->model}->get_accessible_agency_ids();
        $this->quickManageSize = 5;

        $agencySections = $this->{$this->model}->get_agency_view((int)$id);

        $identifier = ! empty($row) && ! empty($row->{$this->identifierField}) ? $row->{$this->identifierField} : '';
        $data = array(
            'access_allowed'    => empty($accessibleAgencyIds) || in_array((int)$id, $accessibleAgencyIds),
            'heading'           => lang($this->pageName . '_heading'),
            'identifier'        => $identifier,
            'noRows'            => lang($this->pageName . '_no_rows'),
            'document_index'    => $this->generate_document_index($agencySections),
            'agency'          => $this->{$this->model}->get_by_id((int)$id),
            'agency_sections' => $agencySections,
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
        }
        $result['content'] = (array)$this->{$this->model}->get_available_agency_sections((int)$id, $getResult);
        $result['success'] = true;

        ajax_return($result);
    }

    private function generate_document_index(array $agencySections): array
    {
        $documentIndex = [];
        foreach ($agencySections as $row) {
            if (empty($row->content)) {
                continue;
            }

            // Check if URL exists
            $posAStart = strpos($row->content, '<a', 0);
            if (is_bool($posAStart)) {
                continue;
            }
            $posAStart = 0;

            // Process all URLs found
            while (!is_bool($posAStart)) {
                // Exit loop if no more A tag found
                $posAStart = strpos($row->content, '<a', $posAStart + 1);
                if (is_bool($posAStart)) {
                    break;
                }

                // Extract text and URL
                $posAEnd = strpos($row->content, '</a>', $posAStart + 1);
                $tag = substr($row->content, $posAStart, $posAEnd - $posAStart);
                $text = strtoupper(trim(strip_tags($tag)));
                $posHrefStart = strpos($tag, 'href="', 0);
                if (is_bool($posHrefStart)) {
                    continue;
                }
                $posHrefEnd = strpos($tag, '"', $posHrefStart + 7);
                if (($posHrefEnd - $posHrefStart) <= 1) {
                    continue;
                }
                $url = substr($tag, $posHrefStart + 6, $posHrefEnd - 9);

                // Skip URLs that are empty or an email addresses
                if (empty($url) || substr($url, 0, 7) === 'mailto:') {
                    continue;
                }
                $posAt = strpos($url, '@', 0);
                if (!is_bool($posAt) && $posAt > 0) {
                    continue;
                }
                $documentIndex[$url] = $text;
            }
        }
        return $documentIndex;
    }

    public function process_dynamic_fields($parentID): array
    {
        $dfData = array();

        if (! empty($this->formFields['dynamic_fields'])) {
            foreach ($this->formFields['dynamic_fields'] as $field => $data) {
                $dfData[$field] = $this->{$this->model}->save_dynamic_fields($field, $data['table'], $data['parent_field'], $parentID);

                // Process uploaders
                if (isset($dfData[$field]['uploaders']) && !empty($dfData[$field]['uploaders'])) {
                    foreach ($dfData[$field]['uploaders'] as $rowID => $dzUploader) {
                        foreach ($dzUploader as $uploader) {
                            $uploaderPostData = explode('|', $uploader);
                            $uploaderFieldName = $uploaderPostData[0];
                            $dfFieldName = $uploaderPostData[1];

                            // Get uploaderData
                            if (!empty($this->uploaders[$uploaderFieldName])) {
                                $uploaderData = $this->uploaders[$uploaderFieldName];

                                if (in_array($uploaderData['type'], ['multi_image', 'multi_file'])) {
                                    $uploaderData['dbField'] = $uploaderFieldName;
                                    $this->process_multi_uploads($rowID, $dfFieldName, $uploaderData);
                                } elseif (in_array($uploaderData['type'], ['single_image', 'single_file'])) {
                                    $uploaderData['dbField'] = $uploaderFieldName;
                                    $this->process_single_uploads($rowID, $dfFieldName, $uploaderData);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $dfData;
    }

    //layout for ajax view single
    public function ajax_get_available_layouts($playbook_id = 0) {
    $result = [
        'error' => false,
        'message' => '',
        'success' => false,
        'content' => []
    ];

    // You can optionally filter by permissions or context later
    $layouts = $this->{$this->model}->get_all_mod_layouts();

    if (!empty($layouts)) {
        $result['content'] = $layouts;
        $result['success'] = true;
    } else {
        $result['message'] = 'No layouts found.';
        $result['success'] = true; // Still success, just empty
    }

    ajax_return($result);
}



}