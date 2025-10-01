<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Playbook_sections extends CRUD_Controller
{
    public $pageName = 'Playbook_sections';
    public $group = 'Playbook_sections';
    public $view = '';
    public $model = 'Model_playbook_sections';
    public $sorting = array('name' => 'ASC');
    public $singular = 'Playbook section';
    public $plural = 'Playbook sections';
    public $quickManage = true;
    public $identifierField = 'name';
    public $hideSubNav = true;
    public $seoFields = false;
    public $quickManageSize = 4;
    public $sluggify = true;
    public $adding = true;

    public function __construct()
    {
        parent::__construct();

        // Get edit allowed permission
        if (!function_exists('getLoggedInUserTypeMenu')) {
            $this->load->helper('profile_helper');
        }
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
                'sort'  => true,
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
            'general' => array(
                'label' => lang('label_search'),
                'type'  => 'autocomplete',
                'field' => array(
                    'mod_playbook_sections.name',
                    'mod_playbook_sections.content',
                ),
            ),
        );
    }

    public function setup_fields()
    {
        $this->formFields = array(
            'main' => array(
                'content'       => 'trim|required',
                'name'          => 'trim|required|strip_tags',
            ),
        );

        $this->formLabels = array();
    }

    public function index(string $playbookId = '')
    {
        // debug_listing();
        $playbookId = $this->get_playbook_id_from_slug($playbookId);
        $this->session->set_userdata('playbook_id', $playbookId);

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
            'playbooks_all' => $this->{$this->model}->get_playbooks_all(),
        );
    }

    public function ajax_view($id): void
    {
        $accessiblePlaybookIds = $this->{$this->model}->get_accessible_playbook_ids();
        $this->quickManageSize = 5;

        $identifier = ! empty($row) && ! empty($row->{$this->identifierField}) ? $row->{$this->identifierField} : '';
        $data = array(
            'access_allowed'    => empty($accessiblePlaybookIds) || in_array((int)$id, $accessiblePlaybookIds),
            'heading'           => lang($this->pageName . '_heading'),
            'identifier'        => $identifier,
            'noRows'            => lang($this->pageName . '_no_rows'),
            'row'               => $this->{$this->model}->get_by_id((int)$id),
        );

        $html = $this->load->view($this->folder . '/' . $this->pageName . '/ajax_view_single', $data, true);

        $this->output->set_output($html);
    }

    public function ajax_get_single_html(string $id): void
    {
        if (empty($id) || !is_numeric($id)) {
            ajax_return(array(
                'error'     => true,
                'message'   => 'Playbook section ID is empty or not a number',
                'success'   => false,
            ));
        }
        ajax_return((array)$this->{$this->model}->get_by_id($id));
    }

    public function playbook_id(string $id): void
    {
        // $this->session->set_userdata('playbook_id', $id);
        $this->index($id);
    }

    public function ajax_apply_filters()
    {
        $section = ''; // Keep this empty to prevent the payload section to contain "playbook_id" which then breaks the search
        $filters = ! empty($this->sections) && isset($this->filters[$section]) ? $this->filters[$section] : $this->filters;

        $filterData = array();
        foreach ($filters as $fname => $filter) {
            if ($filter['type'] == 'date_range' || $filter['type'] == 'range' || $filter['type'] == 'date_period') {
                if ($this->input->post($fname . '-from') != '' && $this->input->post($fname . '-to') != '') {
                    $filterData[$fname] = $filter;
                    $filterData[$fname]['value']['from'] = $this->input->post($fname . '-from');
                    $filterData[$fname]['value']['to'] = $this->input->post($fname . '-to');
                }
            } else if ($this->input->post($fname) || $this->input->post($fname) === '0') {
                $filterData[$fname] = $filter;
                $filterData[$fname]['value'] = $this->input->post($fname);
            }
        }

        // Add playbook ID to the filters
        $playbookId = $this->session->playbook_id;
        if (!empty($playbookId)) {
            $filterData[$fname]['playbook_id'] = $playbookId; 
        }

        $this->session->set_userdata($this->pageName . $section . 'Filters', $filterData);

        ajax_return();
    }

    private function get_playbook_id_from_slug(string $slug): string
    {
        if (empty($slug)) {
            return '';
        }
        $this->db->select('id');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        if (is_numeric($slug)) {
            $this->db->where('id', $slug);
        } else {
            $this->db->where('slug', $slug);
        }
        $results = $this->db->get('mod_playbooks')->row();
        if (!empty($results)) {
            return $results->id;
        }
        return '';
    }
}
