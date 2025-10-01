<?php
class Model_playbook_sections extends CRUD_Model
{
    protected $table = 'mod_playbook_sections';
    private $accessiblePlaybookIds = [];
    private $filterByAccessGroups = true;
    private $loggedInUserType = '';
    private $playbook_id = '';
    private $playbook_id_filters = '';

    public function __construct()
    {
        $this->load->helper('profile_helper');
        $userAccessGroups = getLoggedInAccessGroups();
        $this->userAccessGroupsIDs = getLoggedInAccessGroupIds($userAccessGroups);
        $this->filterByAccessGroups = getEditAllowedStatus();

        $this->loggedInUserType = getLoggedInUserType();
        if (in_array($this->loggedInUserType, ['Super Admin', 'General Admin'])) {
            $this->filterByAccessGroups = false;
        }

        $this->accessiblePlaybookIds = $this->get_accessible_playbook_ids();

        // Get playbook ID if available and reset it
        if (isset($this->session->playbook_id) && !empty($this->session->playbook_id)) {
            $this->playbook_id = $this->session->playbook_id;
        }

        // Get playbook IDs to filter by
        $this->playbook_id_filters = $this->get_playbook_id_filters();
    }

    public function selects()
    {
        $this->db->distinct();
        edb_select('id, name, slug, created_at, enabled', $this->table);
    }

    public function joins()
    {
        $joinFilter = !empty($this->accessiblePlaybookIds) ? ' AND pps.playbook_id IN (' . implode(',', $this->accessiblePlaybookIds) . ')' : '';
        if (!empty($this->playbook_id) && is_numeric($this->playbook_id)) {
            $joinFilter .= ' AND pps.playbook_id = ' . $this->playbook_id;
        }
        $this->db->join('pivot_playbook_playbook_sections AS pps', 'pps.playbook_section_id = ' . $this->table . '.id' . $joinFilter, 'inner');
        parent::joins();
    }

    public function wheres()
    {
        if (!empty($this->playbook_id_filters)) {
            $this->db->where_in('id', $this->playbook_id_filters);
        }
        parent::wheres();
    }

    public function sorting()
    {
        if (!empty($this->playbook_id)) {
            $this->db->order_by('pps.playbook_id', 'asc');
            $this->db->order_by('pps.position', 'asc');
        }
        $this->db->order_by($this->table . '.name', 'asc');
        $this->db->order_by($this->table . '.id', 'asc');
    }

    public function get_playbooks_all()
    {
        $this->db->select('id, name');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name');

        return $this->db->get('mod_playbooks');
    }

    public function get_playbook_types_all()
    {
        $this->db->select('id, name');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name');

        return $this->db->get('mod_playbook_types');
    }

    public function get_accessible_playbook_ids(): array
    {
        if (!$this->filterByAccessGroups) {
            return [];
        }

        $playbookIds = [];

        // Get the playbook IDs that are not linked to an access group
        $this->db->select('id');
        $this->db->where('id NOT IN (SELECT DISTINCT playbook_id FROM pivot_playbook_access_groups)');
        $this->db->where('removed', 0);
        $this->db->order_by('id');
        $result = $this->db->get($this->table)->result_array();
        foreach ($result as $row) {
            $playbookIds[] = (int)$row['id'];
        }

        // Get playbook IDs that are permitted by the access groups
        if (!empty($this->userAccessGroupsIDs)) {
            $this->db->select($this->table . '.id');
            $this->db->join('pivot_playbook_access_groups', 'pivot_playbook_access_groups.playbook_id = ' . $this->table . '.id AND pivot_playbook_access_groups.access_group_id IN (' . implode(',', $this->userAccessGroupsIDs) . ')', 'inner');
            $this->db->where($this->table . '.removed', 0);
            if (!empty($playbookIds)) {
                $this->db->where_not_in($this->table . '.id', $playbookIds);
            }
            $this->db->order_by($this->table . '.id');
            $result = $this->db->get($this->table)->result_array();
            foreach ($result as $row) {
                $playbookIds[] = (int)$row['id'];
            }
        }

        return $playbookIds;
    }

    private function get_playbook_id_filters(): array {
        if (empty($this->accessiblePlaybookIds) && empty($this->playbook_id)) {
            return [];
        }

        $filter = [];
        if (!empty($this->accessiblePlaybookIds)) {
            $filter[] = 'playbook_id IN (' . implode(',', $this->accessiblePlaybookIds) . ')';
        }
        if (!empty($this->playbook_id) && is_numeric($this->playbook_id)) {
            $filter[] = 'playbook_id = ' . $this->playbook_id;
        }

        // Get IDs for array
        // SELECT DISTINCT playbook_id FROM pivot_playbook_playbook_sections WHERE ' . implode(' AND ', $filter) . ')
        $this->db->select('playbook_id');
        $this->db->distinct();
        if (!empty($this->accessiblePlaybookIds)) {
            $this->db->where_in('playbook_id', $this->accessiblePlaybookIds);
        }
        if (!empty($this->playbook_id) && is_numeric($this->playbook_id)) {
            $this->db->where('playbook_id', $this->playbook_id);
        }
        $results = $this->db->get('pivot_playbook_playbook_sections')->result();
        $playlist_ids_list = [];
        foreach ($results as $row) {
            $playlist_ids_list[] = $row->playbook_id;
        }

        return $playlist_ids_list;
    }
}
