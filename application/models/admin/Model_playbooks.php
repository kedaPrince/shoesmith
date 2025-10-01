<?php
class Model_playbooks extends CRUD_Model
{
    protected $table = 'mod_playbooks';
    private $userAccessGroups = [];
    private $accessiblePlaybookIds = [];
    private $filterByAccessGroups = true;
    private $loggedInUserType = '';

    public function __construct()
    {
        $this->load->helper('profile_helper');
        $userAccessGroups = getLoggedInAccessGroups();
        $this->userAccessGroups = [];
        foreach ($userAccessGroups as $index => $row) {
            $this->userAccessGroups[] = $index;
        }

        $this->loggedInUserType = getLoggedInUserType();
        if (in_array($this->loggedInUserType, ['Super Admin', 'General Admin'])) {
            $this->filterByAccessGroups = false;
        }

        $this->accessiblePlaybookIds = $this->get_accessible_playbook_ids();
    }

    public function selects()
    {
        //we don't need main table selects on the new ECMS
//        edb_select('id, playbook_type_id, name, slug, generate_index_page, generate_references, created_at, enabled', $this->table);
//        $this->db->select('mod_playbook_types.name as playbook_type');
        $this->db->select('(
            SELECT (GROUP_CONCAT(CONCAT("<span class=\"list-tag\">", mod_access_groups.name, "</span>") ORDER BY name SEPARATOR " "))
            FROM mod_access_groups
            INNER JOIN pivot_playbook_access_groups ON pivot_playbook_access_groups.access_group_id = mod_access_groups.id
            WHERE mod_access_groups.removed = 0
            AND pivot_playbook_access_groups.playbook_id = ' . $this->table . '.id
        ) AS access_groups', false);
    }

    public function wheres()
    {
        $this->db->where($this->table . '.removed', 0);
        if (!empty($this->accessiblePlaybookIds)) {
            $this->db->where($this->table . '.id IN (' . implode(',', $this->accessiblePlaybookIds) . ')');
        }
        parent::wheres();
    }

    public function joins()
    {
        $this->db->join('mod_playbook_types', 'mod_playbook_types.id = ' . $this->table . '.playbook_type_id AND mod_playbook_types.removed = 0', 'left');
        parent::joins();
    }

    public function get_playbook_types_all()
    {
        $this->db->select('id, name');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name');

        return $this->db->get('mod_playbook_types');
    }

    public function get_playbook_view(int $id): array|null
    {
        $this->db->select($this->table . '.name AS playbook_name');
        $this->db->select($this->table . '.slug AS playbook_slug');
        $this->db->select('ppps.playbook_id'); // Used for dynamic field override
        $this->db->select('ppps.position'); // Used for dynamic field override
        $this->db->select('ps.content');
        $this->db->select('ps.id AS playbook_section_id'); // Used for dynamic field override
        $this->db->select('ps.name');

        $this->db->join('pivot_playbook_playbook_sections AS ppps', 'ppps.playbook_id = ' . $this->table . '.id AND ppps.playbook_id = ' . $id, 'inner');
        $this->db->join('mod_playbook_sections AS ps', 'ps.id = ppps.playbook_section_id', 'inner');

        $this->db->where($this->table . '.enabled', 1);
        $this->db->where($this->table . '.id', $id);
        $this->db->where($this->table . '.removed', 0);
        $this->db->where('ps.enabled', 1);
        $this->db->where('ps.removed', 0);

        $this->db->order_by('ppps.position', 'asc');
        $this->db->order_by('ppps.id', 'asc');
        $this->db->order_by('ps.id', 'asc');

        return $this->db->get($this->table)->result();
    }

    public function get_access_groups(int $playbookId)
    {
        $this->db->select('mod_access_groups.id');
        $this->db->join('pivot_playbook_access_groups', 'pivot_playbook_access_groups.access_group_id = mod_access_groups.id', 'inner');
        $this->db->where('pivot_playbook_access_groups.playbook_id', $playbookId);
        $this->db->where('mod_access_groups.enabled', 1);
        $this->db->where('mod_access_groups.removed', 0);
        $this->db->order_by('mod_access_groups.name');

        $result = $this->db->get('mod_access_groups')->result_array();
        $idsList = [];
        foreach ($result as $row) {
            $idsList[] = (int)$row['id'];
        }

        return $idsList;
    }

    public function get_access_groups_all()
    {
        $this->db->select('id, name');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name');

        return $this->db->get('mod_access_groups');
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
        if (!empty($this->userAccessGroups)) {
            $this->db->select($this->table . '.id');
            $this->db->join('pivot_playbook_access_groups', 'pivot_playbook_access_groups.playbook_id = ' . $this->table . '.id AND pivot_playbook_access_groups.access_group_id IN (' . implode(',', $this->userAccessGroups) . ')', 'inner');
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

    public function get_resource_types_all()
    {
        $this->db->select('id, name');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name');

        return $this->db->get('list_resource_types');
    }

    public function get_playbook_sections_all()
    {
        $this->db->select('id, name');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        $this->db->order_by('name');
        $this->db->order_by('id');

        return $this->db->get('mod_playbook_sections');
    }

    private function update_playbook_sections(array $items): array
    {
        $result = [];
        $requiredFields = ['content', 'name', 'playbook_section_id'];
        $rowCounter = 0;
        foreach ($items as $id => $fields) {
            $row = [];
            foreach ($fields as $field => $value) {
                if (!in_array($field, $requiredFields)) {
                    continue;
                }
                $row[$field] = $field === 'playbook_section_id' ? (int)$value : $value;
            }

            if (empty($row['name'])) {
                $result[$rowCounter] = $row;
                $rowCounter++;
                continue;
            }

            $found = false;
            if ($row['playbook_section_id'] > 0) {
                $this->db->where('id', $row['playbook_section_id']);
                $results = $this->db->get('mod_playbook_sections')->result_array();
                if (!empty($results)) {
                    $found = true;
                }
            }

            $this->db->set('content', $row['content']);
            $this->db->set('name', $row['name']);
            $this->db->set('enabled', 1);
            $this->db->set('removed', 0);
            if ($found) {
                $this->db->where('id', $row['playbook_section_id']);
                $this->db->update('mod_playbook_sections');
            } else {
                $this->db->set('slug', sluggify($row['name'], 'mod_playbook_sections'));
                $this->db->insert('mod_playbook_sections');
                $row['playbook_section_id'] = $this->db->insert_id();
            }

            $result[$rowCounter] = $row;
            $rowCounter++;
        }

        return $result;
    }

    public function save_dynamic_fields($name, $table, $parentField, $parentID, $extraInsert = array(), $extraUpdate = array(), $forceInsert = false)
    {
        $this->dfNoXSS = $this->dfNoXSS;
        $xss = (!empty($this->dfNoXSS) && in_array($name, $this->dfNoXSS)) ? false : true;

        $items = $this->input->post('df[' . $name . ']', $xss);

        // Clear up array of empty values
        foreach ($items as $id => $fields) {
            $hasValue = 0;
            foreach ($fields as $field => $value) {
                !is_array($value) && !empty($value) && $hasValue++;
            }
            if (!$hasValue) {
                unset($items[$id]);
            }
        }

        $returnItems    = array();
        $dzUploaders    = array();

        // Get ids of items that already exists in the db
        $this->db->select('id, playbook_section_id');
        $this->db->where($parentField, $parentID);
        $query = $this->db->get($table);

        $deleteArray = array();
        $playbookSectionIds = [];
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $deleteArray[$row->id] = (int)$row->id;
                $playbookSectionIds[(int)$row->playbook_section_id] = (int)$row->playbook_section_id;
            }
        }

        $playbookSections = $this->update_playbook_sections($items);

        // Mark as removed
        $this->db->where($parentField, $parentID);
        $this->db->set('enabled', '0');
        $this->db->set('removed', '1');
        $this->db->update($table);

        $rowCounter = 0;
        $keepIds = [];
        foreach ($items as $id => $fields) {

            // Set posted fields to be saved
            foreach ($fields as $field => $value) {
                // Skip undesired fields due to override
                if (!in_array($field, ['playbook_id', 'playbook_section_id', 'position'])) {
                    continue;
                }

                // Optionally modify value
                $value = $this->df_modify_value($name, $field, $value);

                // Ignore arrays (like for multi-selects)
                if (!is_array($value)) {
                    if ($field !== 'playbook_id') {
                        $this->db->set($field, $value);
                    }
                } elseif ($field == 'uploaders') {
                    $dzUploaders = $value;
                }
            }

            if ($playbookSections[$rowCounter]['playbook_section_id'] === 0) {
                $rowCounter++;
                continue;
            }
            if (!isset($fields['position'])) {
                $rowCounter++;
                continue;
            }

            // Check if existing
            $this->db->select('id');
            $this->db->where($parentField, $parentID);
            $this->db->where('playbook_section_id', $playbookSections[$rowCounter]['playbook_section_id']);
            $results = $this->db->get($table)->result_array();
            if (empty($results)) {
                // Set extra data to be saved with each insert
                foreach ($extraInsert as $field => $value) {
                    $this->db->set($field, $value);
                }
                $this->db->set($parentField, $parentID);
                $this->db->set('created_at', date('Y-m-d H:i:s'));
                $this->db->set('enabled', 1);
                $this->db->set('playbook_section_id', $playbookSections[$rowCounter]['playbook_section_id']);
                $this->db->set('position', $rowCounter);
                $this->db->set('removed', 0);
                $this->db->set('updated_at', date('Y-m-d H:i:s'));
                $this->db->insert($table);

                $rowID = $this->db->insert_id();
            } else {
                $rowID = $results[0]['id'];
                $this->db->set('enabled', 1);
                $this->db->set('position', $fields['position']);
                $this->db->set('removed', 0);
                $this->db->set('updated_at', date('Y-m-d H:i:s'));
                $this->db->where($parentField, $parentID);
                $this->db->where('id', $rowID);
                $this->db->update($table);
            }
            $keepIds[] = $rowID;

            $returnItems['fields'][$rowID] = $fields;
            $returnItems['uploaders'][$rowID] = $dzUploaders;
            $rowCounter++;
        }

        // Check if any items remain in the delete array and delete them.
        if (!empty($deleteArray) && !empty($playbookSectionIds)) {
            $this->db->where($parentField, $parentID);
            $this->db->where_in('playbook_section_id', $playbookSectionIds);
            if (!empty($keepIds)) {
                $this->db->where_not_in('id', $keepIds);
            }
            $this->db->delete($table);

            // TODO: Remove files for this dynamic rowID
            $returnItems['deleted'] = $playbookSectionIds;
        }

        return $returnItems;
    }

    public function get_available_playbook_sections(int $playbook_id = 0, bool $getResult = true)
    {
        // Get IDs to exclude
        $playbookSectionIdstoExclude = [];
        $this->db->distinct();
        $this->db->select('playbook_section_id');
        $this->db->where('playbook_id', $playbook_id);
        $this->db->where('removed', 0);
        $results = $this->db->get('pivot_playbook_playbook_sections')->result();
        foreach ($results as $row) {
            $playbookSectionIdstoExclude[] = (int)$row->playbook_section_id;
        }

        $this->db->select('id, name');
        $this->db->where('enabled', 1);
        $this->db->where('removed', 0);
        if (!empty($playbookSectionIdstoExclude)) {
            $this->db->where_not_in('id', $playbookSectionIdstoExclude);
        }
        $this->db->order_by('name');

        if ($getResult) {
            return $this->db->get('mod_playbook_sections')->result();
        }
        return $this->db->get('mod_playbook_sections');
    }


    //layout functions
   public function get_all_mod_layouts() {
    $this->db->select('schema_id, name');
    $this->db->where('enabled', 1);
    $this->db->order_by('name', 'ASC');
    $query = $this->db->get('mod_layouts');

    if (!$query) {
        log_message('error', 'DB Error: ' . $this->db->error()['message']);
        return [];
    }

    return $query->result();
}
}