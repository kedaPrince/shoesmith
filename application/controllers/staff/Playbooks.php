<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Playbooks extends CRUD_Controller
{
    public $pageName = 'playbooks';
    public $group = 'Playbooks';
    public $view = '';
    public $model = 'Model_playbooks';
    public $sorting = array('name' => 'ASC');
    public $singular = 'Playbook';
    public $plural = 'Playbooks';
    public $quickManage = true;
    public $identifierField = 'name';
    public $hideSubNav = true;
    public $seoFields = false;
    public $quickManageSize = 4;
    public $sluggify = true;
    public $allowEdit = true;
    public $rowClick = 'edit-row';
    public $adding = false;

    public function __construct()
    {
        parent::__construct();
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
            ),
        );

        $this->listActions = array(
            'view' => array(
                'label'     => lang('label_view'),
                'url'       => url($this->pageName . '/ajax_view/{id}'),
                'icon'      => 'fa-eye',
                'class'     => 'vieww-row', // Do not use view-row, it disables target _blank
            ),
        );

        $this->filters = array(
            //dropdown filter
            'general' => array(
                'label' => lang('label_search'),
                'type' => 'autocomplete',
                'field' => array(
                    'mod_playbooks.name',
                ),
            ),
        );
    }

    public function setup_fields()
    {
        $this->formFields = array(
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
        $this->load->view($this->folder . '/' . $this->pageName . '/view_list_custom');
    }

    public function ajax_view($id): void
    {
        $accessiblePlaybookIds = $this->{$this->model}->get_accessible_playbook_ids();
        $this->quickManageSize = 5;

        $playbookSections = $this->{$this->model}->get_playbook_view((int)$id);

        $identifier = ! empty($row) && ! empty($row->{$this->identifierField}) ? $row->{$this->identifierField} : '';
        $data = array(
            'access_allowed'    => empty($accessiblePlaybookIds) || in_array((int)$id, $accessiblePlaybookIds),
            'heading'           => lang($this->pageName . '_heading'),
            'identifier'        => $identifier,
            'noRows'            => lang($this->pageName . '_no_rows'),
            'document_index'    => $this->generate_document_index($playbookSections),
            'playbook'          => $this->{$this->model}->get_by_id((int)$id),
            'playbook_sections' => $playbookSections,
        );

        $html = $this->load->view($this->folder . '/' . $this->pageName . '/ajax_view_single', $data, true);

        $this->output->set_output($html);
    }

    public function ajax_get_available_playbook_sections(string $id, bool $getResult = true): void
    {
        $result = array(
            'error'     => false,
            'message'   => '',
            'success'   => false,
            'content'   => [],
        );
        if (empty($id) || !is_numeric($id)) {
            $result['error'] = true;
            $result['message'] = 'Playbook ID is empty or not a number';
        }
        $result['content'] = (array)$this->{$this->model}->get_available_playbook_sections((int)$id, $getResult);
        $result['success'] = true;

        ajax_return($result);
    }

    private function generate_document_index(array $playbookSections): array
    {
        $documentIndex = [];
        foreach ($playbookSections as $row) {
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
}
