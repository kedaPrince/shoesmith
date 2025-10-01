<?php
class Model_playbook_sections extends CRUD_Model
{
    protected $table = 'mod_playbook_sections';
    private $userAccessGroups = [];
    private $accessiblePlaybookIds = [];
    private $filterByAccessGroups = true;
    private $loggedInUserType = '';

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
    }

    public function selects()
    {
        edb_select('id, name, slug, created_at, enabled', $this->table);
    }

    public function joins()
    {
        if (!empty($this->accessiblePlaybookIds)) {
            $this->db->join('pivot_playbook_playbook_sections AS pps', 'pps.playbook_section_id = ' . $this->table . '.id AND pps.playbook_id IN (' . implode(',', $this->accessiblePlaybookIds) . ')', 'inner');
        }
        parent::joins();
    }

    public function wheres()
    {
        $this->db->where($this->table . '.removed', 0);
        parent::wheres();
    }

    public function sorting()
    {
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
}
