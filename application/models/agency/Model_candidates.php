<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_candidates extends CRUD_Model
{
    protected $table = 'candidates';

    public function __construct() {
        parent::__construct();
    }

// IN Model_candidates.php - COMPLETELY REVISED main_selects()
public function main_selects()
{
    $agency_id = $this->get_current_agency_id();
    
    // Start with candidate_job_assignments as base table
    // This gives us ONE ROW PER JOB ASSIGNMENT
    $this->db->from('candidate_job_assignments cja');
    
    // Join with candidates
    $this->db->join('candidates c', 'c.id = cja.candidate_id AND c.removed = 0', 'inner');
    
    // Select candidate fields with aliases
    $this->db->select('c.*, c.id as candidate_id, c.enabled as candidate_enabled, c.uuid as candidate_uuid');
    
    if ($agency_id) {
        // CRITICAL: Only show jobs from THIS agency
        $this->db->join('mod_jobs j', 
            'j.id = cja.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 
            'inner');
        
        // Also verify candidate belongs to this agency
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id AND ca.agency_id = ' . $this->db->escape($agency_id), 'inner');
        
        // Select job information
        $this->db->select('j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref');
        
        // Select the assignment ID for grouping if needed
        $this->db->select('cja.id as assignment_id');
        
    } else {
        // Admin view - see everything
        $user_type = getLoggedInUserTypeMenu();
        if ($user_type !== 'admin') {
            $this->db->where('c.id', 0);
        }
        
        $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.removed = 0', 'left');
        $this->db->select('j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref');
    }
    
    // Only active assignments
    $this->db->where('cja.removed', 0);
    
    // NO GROUP BY - we want multiple rows!
    // $this->db->group_by('c.id'); // REMOVE THIS!
}

    public function main_joins()
{
   // $this->db->join('mod_jobs', 'mod_jobs.id = candidates.job_id', 'left');
}

    public function main_wheres()
    {
        $this->db->where('candidates.removed', 0);
    }

    public function main_grouping()
    {
        $this->grouping();
    }

    public function grouping()
    {
    }

    public function main_filters()
    {
        $filters = get_ecms_filters($this->pageName);
        if (!empty($filters)) {
            foreach ($filters as $filterName => $options) {
                if ($options['type'] == 'date_range' || $options['type'] == 'date_period') {
                    $options['value']['from'] = date('Y-m-d', strtotime($options['value']['from']));
                    $options['value']['to'] = date('Y-m-d', strtotime($options['value']['to']));
                }
                elseif ($options['type'] == 'date') {
                    $options['value'] = date('Y-m-d', strtotime($options['value']));
                }

                if (!empty($options['sql'])) {
                    if ($options['type'] == 'date_range' || $options['type'] == 'range') {
                        $sql = str_replace('{value1}', $options['value']['from'], $options['sql']);
                        $sql = str_replace('{value2}', $options['value']['to'], $sql);
                        $sql = str_replace('{field}', $options['field'], $sql);
                        
                        $this->db->where($sql, null, false);
                    } else {
                        $sql = str_replace('{value}', $options['value'], $options['sql']);
                        $sql = str_replace('{field}', $options['field'], $sql);
    
                        $this->db->where($sql, null, false);
                    }
                } else {
                    $escape = !empty($options['escape']) ? $options['escape'] : true;
                    $this->db->group_start();

                    foreach ((array) $options['field'] as $key => $field) {
                        if ($options['type'] == 'date_range' || $options['type'] == 'range') {
                            $this->db->where('(' . $field . ' >= "' . $options['value']['from'] . ' 00:00:00" AND ' . $field . ' <= "' . $options['value']['to'] . ' 23:59:59")', null, $escape);
                        } else if ($options['type'] == 'date_period') {
                            $this->db->where('(' . $options['field'] . ' <= "' . $options['value']['from'] . '" AND ' . $options['field2'] . ' <= "' . $options['value']['to'] . '")', null, $escape);

                        } else if ($options['type'] == 'autocomplete') {
                            foreach ((array) $field as $f) {
                                $this->db->or_like($f, $options['value'], $escape);
                            }
                        } else if ($options['type'] == 'multiselect') {
                            $this->db->where_in($field, $options['value'], $escape);
                        } else if ($options['type'] == 'dropdown_multi_table') {
                            $this->db->or_where($options['field1'], $options['value'], $escape);
                            $this->db->or_where($options['field2'], $options['value'], $escape);
                        } else {
                            $this->db->where($field, $options['value'], $escape);
                        }
                    }

                    $this->db->group_end();
                }
            }
        }

        $this->filters();
    }

    public function filters()
    {
    }

    public function main_limit()
    {
        if (!empty($this->session->{$this->pageName . 'RowLimit'})) {
            $rl = $this->session->{$this->pageName . 'RowLimit'};
        } else if (!empty($this->rowLimit)) {
            $rl = $this->rowLimit;
        } else {
            $rl = $this->config->item('CRUD_row_limit');
        }

        $this->page = $this->page;

        if (!empty($this->page)) {
            $page = $this->page;
        } else {
            $page = 1;
        }

        $offset = ($page - 1) * $rl;

        $this->db->limit($rl, $offset);
    }

    public function main_sorting()
    {
        if (!empty($this->session->{$this->pageName . 'Sorting'})) {
            $sorting = $this->session->{$this->pageName . 'Sorting'};
            foreach ($sorting as $field => $dir) {
                $this->db->order_by($field, $dir);
            }
        } else {
            foreach ($this->sorting as $field => $dir) {
                $this->db->order_by($field, $dir);
            }
        }
    }

    public function create(array $data, $table = false) {
        $table = $table ? $table : $this->table;
        if (!$this->db->insert($table, $data)) {
            Anomalies::log('Failed to create from CRUD', $this->db->last_query());
            return false;
        }
        return $this->db->insert_id();
    }

    public function create_batch(array $data, $table = false) {
        $table = $table ? $table : $this->table;
        if (!$this->db->insert_batch($table, $data)) {
            Anomalies::log('Failed to create batch from CRUD', $this->db->last_query());
            return false;
        }
        return true;
    }

    public function update(array $data, $whereValue, $whereField = 'id', $table = false) {
        $table = $table ? $table : $this->table;

        $data['updated_at'] = date('Y-m-d H:i:s');

        if (!$this->db->update($table, $data, array($whereField => $whereValue))) {
            Anomalies::log('Failed to update from CRUD', $this->db->last_query());
            return false;
        }

        $this->db->select('id');
        $this->db->limit(1);
        $this->db->where($whereField, $whereValue);
        $id = $this->db->get($table)->row()->id;

        return $id;
    }

    public function remove($whereValue, $whereField = 'id', $table = false) {
        $data = array(
            'removed' => 1,
            'deleted_at' => date("Y-m-d H:i:s")
        );

        $table = $table ? $table : $this->table;

        if (!$this->db->update($table, $data, array($whereField => $whereValue))) {
            Anomalies::log('Failed to remove from CRUD', $this->db->last_query());
            return false;
        }

        return true;
    }

    public function enable($whereValue, $whereField = 'id') {
        $data = array(
            'enabled' => 1
        );
        if (!$this->db->update($this->table, $data, array($whereField => $whereValue))) {
            Anomalies::log('Failed to enable from CRUD', $this->db->last_query());
            return false;
        }

        return true;
    }

    public function disable($whereValue, $whereField = 'id') {
        $data = array(
            'enabled' => 0
        );
        if (!$this->db->update($this->table, $data, array($whereField => $whereValue))) {
            Anomalies::log('Failed to disable from CRUD', $this->db->last_query());
            return false;
        }

        return true;
    }
   
    public function get_id_for_slug($slug, $table = false) {
        $this->db->where('removed', 0);
        $this->db->where('slug', $slug);

        if (!$table) {
            $table = $this->table;
        }

        if (!$row = $this->db->get($table)) {
            Anomalies::log('Failed to retrieve id for slug', $this->db->last_query());
        }

        $row = $row->row();

        if (count($row) > 0) {
            return $row->id;
        }

        return false;
    }

    public function get_id_for_col($col, $val, $table = false, $where = array()) {
        $this->db->where('removed', 0);
        $this->db->where($col, $val);

        if (!empty($where)) {
            foreach ($where as $value) {
                $this->db->where($value['where'], $value['value']);
            }
        }

        if (!$table) {
            $table = $this->table;
        }

        if (!$row = $this->db->get($table)) {
            Anomalies::log('Failed to retrieve id for col', $this->db->last_query());
            return false;
        }

        if ($row->num_rows() == 0) {
            return false;
        }

        $row = $row->row();

        if (count($row) > 0) {
            return $row->id;
        }
        return false;
    }

    public function soft_delete($id) {
        if (empty($id)) {
            Anomalies::log('Failed to soft delete by id - no id supplied');
            return false;
        }

        $this->db->where('id', $id);
        $this->db->set('enabled', 0);
        $this->db->set('removed', 1);
        $this->db->set('deleted_at', date('Y-m-d H:i:s'));
        if (!$this->db->update($this->table)) {
            Anomalies::log('Failed to soft delete by id', $this->db->last_query());
            return false;
        }

        return true;
    }

    public function get_simple_list_data($table, $field = null, $value = null) {
        $this->db->select('id, name');

        if (!empty($field)) {
            $this->db->where($field, $value);
        }
        $this->db->where($table . '.removed', 0);
        $this->db->order_by('position', 'asc');
        $this->db->order_by('name', 'asc');
        $query = $this->db->get($table);

        return $query;
    }

    public function get_by_slug($slug, $table = false) {
        $table = $table ? $table : $this->table;
        $this->db->where($table . '.removed', 0);
        $this->db->where($table . '.slug', $slug);
        $query = $this->db->get($table);

        if ($query->num_rows() == 1) {
            $row = $query->row();
            return $row;
        } elseif ($query->num_rows() > 1) {
            Anomalies::log('Duplicate slugs found while getting entry by slug', $this->db->last_query());
            return false;
        } elseif ($query->num_rows() < 1) {
            Anomalies::log('No entries found while getting an entry by slug', $this->db->last_query());
            return false;
        }
    }

    private function get_current_agency_id()
    {
        $ci =& get_instance();
        $login = $ci->session->userdata('login');
        
        if (!empty($login['agency'])) {
            $agency_user = $login['agency'];
            
            if (!empty($agency_user['agency_id'])) {
                return $agency_user['agency_id'];
            } elseif (!empty($agency_user['id'])) {
                return $agency_user['id'];
            } elseif (!empty($agency_user['agency']['id'])) {
                return $agency_user['agency']['id'];
            }
        }
        
        return null;
    }
    
// IN Model_candidates.php - Update get_all()
public function get_all($limit = 0, $offset = 0, $section = '')
{
    $agency_id = $this->get_current_agency_id();
    
    // Use the new base table approach
    $this->db->from('candidate_job_assignments cja');
    $this->db->join('candidates c', 'c.id = cja.candidate_id AND c.removed = 0', 'inner');
    
    // Select with aliases
    $this->db->select('c.*, c.id as candidate_id, c.enabled as candidate_enabled, c.uuid as candidate_uuid');
    
    if (empty($agency_id)) {
        $user_type = getLoggedInUserTypeMenu();
        if ($user_type !== 'admin') {
            $this->db->where('c.id', 0);
        }
        $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.removed = 0', 'left');
    } else {
        // CRITICAL: Agency-specific filtering
        $this->db->join('mod_jobs j', 
            'j.id = cja.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 
            'inner');
        $this->db->join('candidate_agencies ca', 
            'ca.candidate_id = c.id AND ca.agency_id = ' . $this->db->escape($agency_id), 
            'inner');
    }
    
    $this->db->select('j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref');
    $this->db->where('cja.removed', 0);
    
    // NO GROUP BY - allow multiple rows
    // $this->db->group_by('c.id'); // REMOVE THIS!
    
    if (!empty($this->sorting)) {
        foreach ($this->sorting as $field => $direction) {
            // Handle sorting for candidate or job fields
            if (in_array($field, ['first_name', 'last_name', 'email', 'reference_number'])) {
                $this->db->order_by('c.' . $field, $direction);
            } elseif ($field === 'job_name') {
                $this->db->order_by('j.name', $direction);
            } else {
                $this->db->order_by($field, $direction);
            }
        }
    }
    
    if ($limit > 0) {
        $this->db->limit($limit, $offset);
    }
    
    return $this->db->get();
}
    
    public function get_by_id($id, $table = false)
    {
        $table = $table ? $table : $this->table;
        
        $agency_id = $this->get_current_agency_id();
        
        $this->db->where('candidates.id', $id);
        $this->db->where('candidates.removed', 0);
        
        if (!empty($agency_id)) {
            $this->db->join('candidate_agencies ca', 'ca.candidate_id = candidates.id AND ca.agency_id = ' . $this->db->escape($agency_id), 'inner');
        } else {
            $user_type = getLoggedInUserTypeMenu();
            if ($user_type !== 'admin') {
                return null;
            }
        }
        
        return $this->db->get($table)->row();
    }

    public function get_candidate_jobs_secure($candidate_id, $agency_id = null)
    {
        if (!$agency_id) {
            $agency_id = $this->get_current_agency_id();
        }
        
        if (!$agency_id) {
            return [];
        }
        
        $this->db->select('1');
        $this->db->from('candidate_agencies ca');
        $this->db->where('ca.candidate_id', $candidate_id);
        $this->db->where('ca.agency_id', $agency_id);
        $has_access = $this->db->get()->row() !== null;
        
        if (!$has_access) {
            return [];
        }
        
        $this->db->select('cja.*, j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref, j.agency_id as job_agency_id');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 'inner');
        $this->db->where('cja.candidate_id', $candidate_id);
        $this->db->where('cja.removed', 0);
        
        return $this->db->get()->result();
    }

    public function get_candidate_jobs_for_current_agency($candidate_id)
    {
        $agency_id = $this->get_current_agency_id();
        
        if (!$agency_id) {
            return [];
        }
        
        $this->db->select('cja.*, j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id));
        $this->db->where('cja.candidate_id', $candidate_id);
        $this->db->where('cja.removed', 0);
        
        return $this->db->get()->result();
    }

    public function get_candidate_full($identifier)
    {
        $agency_id = $this->get_current_agency_id();
        
        if (!$agency_id) {
            return null;
        }
        
        if (is_string($identifier) && strlen($identifier) == 36 && strpos($identifier, '-') !== false) {
            $this->db->where('c.uuid', $identifier);
        } else {
            $this->db->where('c.id', $identifier);
        }
        
        $this->db->select('c.*, c.uuid as candidate_uuid, 
                           j.name as job_name, j.uuid as job_uuid, j.agency_id as job_agency_id,
                           a.name as agency_name, a.uuid as agency_uuid,
                           r.first_name as recruiter_first_name, r.last_name as recruiter_last_name');
        $this->db->from('candidates c');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id AND ca.agency_id = ' . $this->db->escape($agency_id), 'inner');
        $this->db->join('candidate_job_assignments cja', 'cja.candidate_id = c.id AND cja.removed = 0', 'left');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 'left');
        $this->db->join('agencies a', 'a.id = j.agency_id', 'left');
        $this->db->join('recruiters r', 'r.id = c.assigned_agent_id', 'left');
        $this->db->where('c.removed', 0);
        
        return $this->db->get()->row();
    }

    public function get_autocomplete_results($fields, $value, $select = "id, name", $where = FALSE) {
        $this->db->select($select);
        $this->main_joins();
        $this->db->group_start();

        if ($where) {
            $this->db->where($where);
        }
        $value = explode(',', $value);
        $this->main_wheres();
        $this->db->group_end();
        $this->db->group_start();
        foreach ($value as $val) {
            $this->db->group_start();
            foreach ((array) $fields as $field) {
                $this->db->or_like($field, $val);
            }
            $this->db->group_end();
        }
        $this->db->group_end();
        $this->db->limit(100);

        $query = $this->db->get($this->table); 
        return $query;
    }

    public function check_unique_slug($slug, $table = null, $id = 0) {
        $table = (!empty($table)) ? $table : $this->table;

        $this->db->where('slug', $slug);

        if ($id) {
            $this->db->where('id !=', $id);
        }

        $this->db->limit(1);
        $query = $this->db->get($table);

        if ($query->num_rows() > 0) {
            if (preg_match('/\d+$/', $slug)) {
                $slug = preg_replace_callback('/(\d+$)/', function($matches) use ($slug) {
                    $str = preg_replace('/\d+$/', '', $slug);

                    return ($matches[0] + 1);
                }, $slug);
            } else {
                $slug .= '2';
            }

            $slug = $this->check_unique_slug($slug, $table);
        }

        return $slug;
    }

    public function get_assignment_data($group, $parentID, $filters) {
        $groupOptions = $this->assignmentGroup[$group];

        $this->db->select($groupOptions['child_field'] . ' AS name');
        $this->db->select($groupOptions['child_table'] . '.id');
        $this->db->select('IF(ISNULL(' . $groupOptions['pivot_table'] . '.id), 0, 1) AS assigned');
        $joinCondition = $groupOptions['pivot_table'] . '.' . $groupOptions['child_id'] . ' = ' . $groupOptions['child_table'] . '.id ';
        $joinCondition .= 'AND ' . $groupOptions['pivot_table'] . '.' . $groupOptions['parent_id'] . ' = "' . $this->db->escape_str($parentID) . '"';
        $this->db->join($groupOptions['pivot_table'], $joinCondition, 'left');
        $this->db->where($groupOptions['child_table'] . '.removed', 0);
        $this->db->order_by('name', "ASC");

        if (!empty($groupOptions['extend_query'])) {
            $this->{$groupOptions['extend_query']}();
        }

        if (!empty($filters)) {

            $filterOptions = $this->assignmentFilters[$group];

            foreach ($filterOptions as $filterName => $options) {
                if (!empty($options['sql'])) {
                    $this->db->where($options['sql'], null, false);
                } else {
                    if ($filters[$filterName] != '') {
                        $escape = !empty($options['escape']) ? $options['escape'] : true;
                        $this->db->group_start();

                        foreach ((array) $options['field'] as $key => $field) {
                            if ($options['type'] == 'autocomplete') {
                                foreach ((array) $field as $f) {
                                    $this->db->or_like($f, $filters[$filterName], $escape);
                                }
                            } else {
                                $this->db->where($field, $filters[$filterName], $escape);
                            }
                        }

                        $this->db->group_end();
                    }
                }
            }
        }

        $query = $this->db->get($groupOptions['child_table']);

        return $query;
    }

    public function save_assignment_data($group, $parentID, $childID, $childValue) {
        $groupOptions = $this->assignmentGroup[$group];

        if ($childValue) {
            $this->db->set($groupOptions['parent_id'], $parentID);
            $this->db->set($groupOptions['child_id'], $childID);
            $result = $this->db->insert($groupOptions['pivot_table']);

            if ($result) {
                Logger::log(ucwords($groupOptions['child']) . ' has been assigned to ' . $groupOptions['parent'], array(
                    'group' => $group,
                    'parentID' => $parentID,
                    'childID' => $childID,
                    'childValue' => $childValue
                ));
            }
        } else {
            $this->db->where($groupOptions['parent_id'], $parentID);
            $this->db->where($groupOptions['child_id'], $childID);
            $this->db->limit(1);
            $result = $this->db->delete($groupOptions['pivot_table']);

            if ($result) {
                Logger::log(ucwords($groupOptions['child']) . ' has been unassigned to ' . $groupOptions['parent'], array(
                    'group' => $group,
                    'parentID' => $parentID,
                    'childID' => $childID,
                    'childValue' => $childValue
                ));
            }
        }

        return $result;
    }

    public function save_dynamic_fields($name, $table, $parentField, $parentID, $extraInsert = array(), $extraUpdate = array(), $forceInsert = false) {
        $this->dfNoXSS = $this->dfNoXSS;
        $this->dfSluggify = $this->dfSluggify;
        $xss = (!empty($this->dfNoXSS) && in_array($name, $this->dfNoXSS)) ? FALSE : TRUE;
        $sluggifyField = (!empty($this->dfSluggify) && array_key_exists($name, $this->dfSluggify)) ? $this->dfSluggify[$name] : FALSE;

        $items = $this->input->post('df[' . $name . ']', $xss);

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

        $this->db->select('id');
        $this->db->where($parentField, $parentID);
        $this->db->where('removed', 0);
        $query = $this->db->get($table);

        $deleteArray = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $deleteArray[$row->id] = $row->id;
            }
        }

        foreach ($items as $id => $fields) {
            $id = str_replace('r','', $id);

            foreach ($fields as $field => $value) {

                $value = $this->df_modify_value($name, $field, $value);

                if (!is_array($value)) {
                    $this->db->set($field, $value);
                } elseif ($field == 'uploaders') {
                    $dzUploaders = $value;
                }
            }

            if ($forceInsert || preg_match('/new\_\d+/', $id)) {
                foreach ($extraInsert as $field => $value) {
                    $this->db->set($field, $value);
                }

                if ($sluggifyField && !empty($fields[$sluggifyField])) {
                    $slug = $this->{$this->model}->sluggify($fields[$sluggifyField], $table);
                    $this->db->set('slug', $slug);
                }

                $this->db->set($parentField, $parentID);
                $this->db->set('updated_at', date('Y-m-d H:i:s'));
                $this->db->set('created_at', date('Y-m-d H:i:s'));
                $this->db->insert($table);

                $rowID = $this->db->insert_id();
            } else {
                foreach ($extraUpdate as $field => $value) {
                    $this->db->set($field, $value);
                }

                $this->db->set('updated_at', date('Y-m-d H:i:s'));
                $this->db->where($parentField, $parentID);
                $this->db->where('id', $id);
                $this->db->update($table);

                if (isset($deleteArray[$id])) {
                    unset($deleteArray[$id]);
                }

                $rowID = $id;
            }

            $returnItems['fields'][$rowID] = $fields;
            $returnItems['uploaders'][$rowID] = $dzUploaders;
        }

        if (!empty($deleteArray)) {
            $this->db->set('removed', 1);
            $this->db->set('deleted_at', date('Y-m-d H:i:s'));
            $this->db->where($parentField, $parentID);
            $this->db->where_in('id', $deleteArray);
            $this->db->update($table);

            $returnItems['deleted'] = $deleteArray;
        }

        return $returnItems;
    }

    public function df_modify_value($name, $field, $value) {
        return $value;
    }

    public function get_dynamic_field_data($name, $table, $parentField, $parentID, $fields, $df=array(), $formatters=array()) {
        $this->db->where($parentField, $parentID);
        $this->db->where('removed', 0);
        $this->db->order_by('position', 'asc');
        $query = $this->db->get($table);

        $df[$name] = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                foreach ($fields as $field) {
                    if (!empty($formatters[$field]) && is_callable($formatters[$field])) {
                        $df[$name]['r'.$row->id][$field] = call_user_func($formatters[$field], $row->{$field});
                    }
                    else {
                        $df[$name]['r'.$row->id][$field] = $row->{$field};
                    }
                }
            }
        }

        return $df;
    }

    public function search_cities($search, $countryID = 0, $searchType = 'after') {
        $this->db->select('
            list_cities.id AS city_id,
            list_cities.name AS city,
            IFNULL(list_regions.id, 0) AS region_id,
            list_regions.name AS region
        ');

        $this->db->join('list_regions', 'list_regions.id = list_cities.region_id', 'left');
        $this->db->where('list_cities.removed', 0);
        $this->db->where('list_cities.enabled', 1);

        $this->db->like('list_cities.name', $search, $searchType);

        $countryID && $this->db->where('list_cities.country_id', $countryID);

        $this->db->order_by('city', 'ASC');
        $this->db->order_by('region', 'ASC');

        $query = $this->db->get('list_cities');

        return $query;
    }

    public function slug_check($query) {
        if ($query->num_rows() == 1) {
            $row = $query->row();
            return $row;
        } elseif ($query->num_rows() > 1) {
            Anomalies::log('Duplicate slugs found while getting entry by slug', $this->db->last_query());
            return false;
        } elseif ($query->num_rows() < 1) {
            Anomalies::log('No entries found while getting an entry by slug', $this->db->last_query());
            return false;
        }
    }

    public function hard_delete($params, $table) {
        $this->db->where($params);
        $result = $this->db->delete($table);

        return $result;
    }

    public function check_unique($value, $field,  $id='', $idField='id', $table='') {
        $this->db->where($field, $value);

        if (!empty($id)){
            $this->db->where($idField.' !=', $id);
        }

        $table = !empty($table) ? $table : $this->table;

        $query = $this->db->get($table);

        if ($query->num_rows() > 0) {
            return false;
        }
        else {
            return true;
        }
    }
    
    public function is_unique($str, $id = "", $field="", $extra=array()) {
        $this->db->select('id');
        $this->db->where($field, $str);
        $this->db->where('removed', 0);
        
        if (!empty($extra)) {
            foreach ($extra as $eField => $eValue) {
                $this->db->where($eField, $eValue);
            }
        }

        if (!empty($id)) {
            $this->db->where('id !=', $id);
        }

        $query = $this->db->get($this->table);
        if ($query->num_rows() == 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function create_login($email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->db->set('email', $email);
        $this->db->set('password', $hashedPassword);
        $result = $this->db->insert('sys_logins');

        if ($result) {
            $id = $this->db->insert_id();

            return $id;
        }
        else {
            return 0;
        }
    }

    public function create_login_hash($email) {
        $this->db->set('email', $email);
        $this->db->set('password', random_string('alnum', '32'));
        $result = $this->db->insert('sys_logins');

        if ($result) {
            $id = $this->db->insert_id();

            return $id;
        }
        else {
            return 0;
        }
    }

    public function multi_insert($data, $table) {
        foreach ($data as $insertArray) {
            foreach ($insertArray as $field => $value) {
                $this->db->set($field, $value);
            }
            $this->db->insert($table);
        }
    }

    public function get_dynamic_content($table, $parentField, $parentID) {
        $this->db->where($parentField, $parentID);
        $this->db->where('removed', 0);
        $this->db->order_by('dc_position', 'asc');
        $query = $this->db->get($table);

        return $query;
    }

    public function change_position($id, $position, $num, $filters=array()) {
        $this->db->trans_start();

        $this->db->select('position');
        $this->db->where('id', $id);
        $this->db->where('removed', 0);
        !empty($filters) && $this->db->where($filters);
        $query = $this->db->get($this->table);
        $position_old = $query->row()->position;

        $this->db->set('position',$position);
        $this->db->where('id', $id);
        $this->db->where('removed', 0);
        !empty($filters) && $this->db->where($filters);
        $this->db->update($this->table);

        if($position > $position_old) {
                $this->db->set('position','position - 1', false);
                $this->db->where('position <= ',  $position);
                $this->db->where('position >= ',  $position_old);
                $this->db->where('removed', 0);
                $this->db->where('id != ', $id);
                !empty($filters) && $this->db->where($filters);
                $this->db->update($this->table);
        } else {
            if($position == $num) {
                $this->db->set('position','position - 1', false);
                $this->db->where('position > ',  $position);
                $this->db->where('removed', 0);
                $this->db->where('id != ', $id);
                !empty($filters) && $this->db->where($filters);
                $this->db->update($this->table);
            } else {
                $this->db->set('position','position + 1', false);
                $this->db->where('position >= ',  $position);
                $this->db->where('position <= ',  $position_old);
                $this->db->where('removed', 0);
                $this->db->where('id != ', $id);
                !empty($filters) && $this->db->where($filters);
                $this->db->update($this->table);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE) {
            return true;
        }
        else {
            return false;
        }
    }

    public function change_position_old($id, $dir, $filters=array()) {
        $this->db->trans_start();

        $this->db->select('position');
        $this->db->where('id', $id);
        $this->db->where('removed', 0);
        !empty($filters) && $this->db->where($filters);
        $query = $this->db->get($this->table);
        $position = $query->row()->position;

        if ($dir == 'up' && $position != 1) {
            $this->db->set('position', $position);
            $this->db->where('position', $position - 1);
            $this->db->where('removed', 0);
            !empty($filters) && $this->db->where($filters);
            $this->db->update($this->table);

            $this->db->set('position', $position - 1);
            $this->db->where('id', $id);
            $this->db->where('removed', 0);
            !empty($filters) && $this->db->where($filters);
            $this->db->update($this->table);
        }
        elseif ($dir == 'down') {
            $this->db->select('MAX(position) AS last_position');
            $this->db->where('removed', 0);
            !empty($filters) && $this->db->where($filters);
            $query = $this->db->get($this->table);
            $lastPosition = $query->row()->last_position;

            if ($position != $lastPosition) {
                $this->db->set('position', $position);
                $this->db->where('position', $position + 1);
                $this->db->where('removed', 0);
                !empty($filters) && $this->db->where($filters);
                $this->db->update($this->table);

                $this->db->set('position', $position + 1);
                $this->db->where('id', $id);
                $this->db->where('removed', 0);
                !empty($filters) && $this->db->where($filters);
                $this->db->update($this->table);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() !== FALSE) {
            return true;
        }
        else {
            return false;
        }
    }

    public function reset_positioning($filters=array()) {
        $this->db->query('SET @i:=0;');
        $this->db->set('position', '@i:=(@i+1)', false);
        $this->db->where('removed', 0);
        !empty($filters) && $this->db->where($filters);
        $this->db->update($this->table);
    }

    public function positioning_close_gaps($filters = array()) {
        $this->db->select('id');
        $this->db->where('removed', 0);
        !empty($filters) && $this->db->where($filters);
        $this->db->order_by('position', 'ASC');
        $items = $this->db->get($this->table)->result_array();

        $position = 1;
        foreach ($items as $item) {
            $this->db->where('id', $item['id']);
            $this->db->update($this->table, array('position' => $position));
            $position++;
        }
    }

    public function get_new_position($filters=array()) {
        $this->db->select('IFNULL(MAX(position), 0) + 1 AS new_position');
        $this->db->where('removed', 0);
        !empty($filters) && $this->db->where($filters);
        $query = $this->db->get($this->table);
        $newPosition = $query->row()->new_position;

        return $newPosition;
    }

    public function get_login_email($loginID) {
        $this->db->select('email');
        $this->db->where('id', $loginID);
        $this->db->where('removed', 0);
        
        $query = $this->db->get('sys_logins');

        if ($query->num_rows() > 0) {
            $row = $query->row();

            return $row->email;
        }
        else {
            return false;
        }
    }

    public function is_unique_login_email($email, $loginID = "") {
        $this->db->where('email', $email);
        $this->db->where('removed', 0);
        
        if (!empty($loginID)) {
            $this->db->where('id !=', $loginID);
        }

        $query = $this->db->get('sys_logins');

        if ($query->num_rows() == 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function get_login_id($id, $table="") {
        $table = !empty($table) ? $table : $this->table;

        $this->db->select('login_id');
        $this->db->where('id', $id);
        $query = $this->db->get($this->table);

        if ($query->num_rows() > 0) {
            $row = $query->row();

            return $row->login_id;
        }
        else {
            return false;
        }
    }

    public function update_login($parentID, $emailField="login_email", $table="") {
        $table = !empty($table) ? $table : $this->table;

        $this->db->select('login_id');
        $this->db->where('removed', 0);
        $this->db->where('id', $parentID);
        $query = $this->db->get($table);

        if ($query->num_rows() > 0) {
            $loginID = $query->row()->login_id;

            if ($this->input->post('password')) {
                $newPassword = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
                $this->db->set('password', $newPassword);
            }
            $this->db->set('email', $this->input->post($emailField));
            $this->db->where('id', $loginID);
            $this->db->update('sys_logins');
        }
    }

    function sluggify($str, $table = null) {
        if (!empty($str)) {
            $str = substr($str, 0, 250);
            $str = strtolower($str);
            $str = html_entity_decode($str);
            $str = strip_tags($str);
            $str = stripslashes($str);
            $str = str_replace('\'', '', $str);
            $str = preg_replace('/[^a-z0-9]+/', '-', $str);
            $str = trim($str, '-');

            $slug = $this->{$this->model}->check_unique_slug($str, $table);

            return $slug;
        }

        return false;
    }

    public function get_agency_by_id($agency_id)
    {
        $this->db->select('*');
        $this->db->from('agencies');
        $this->db->where('id', $agency_id);
        $this->db->where('removed', 0);
        
        return $this->db->get()->row();
    }

    public function update_onboarding_stage($candidate_id, $stage, $value)
    {
        $update_data = array(
            $stage => $value,
            'updated_at' => date('Y-m-d H:i:s')
        );

        if ($value == 1) {
            $stage_timestamp_field = $stage . '_at';
            $update_data[$stage_timestamp_field] = date('Y-m-d H:i:s');
        } else {
            $stage_timestamp_field = $stage . '_at';
            $update_data[$stage_timestamp_field] = null;
        }

        $result = $this->db->where('id', $candidate_id)->update($this->table, $update_data);

        if ($result) {
            $this->update_onboarding_progress($candidate_id);
        }

        return $result;
    }

    public function update_hm_decision($candidate_id, $decision, $notes = null)
    {
        $update_data = array(
            'hm_decision' => $decision,
            'hm_decision_notes' => $notes,
            'hm_decision_by' => $this->get_current_agency_id(),
            'hm_decision_at' => date('Y-m-d H:i:s'),
            'stage_hm_decision' => 1,
            'stage_hm_decision_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $result = $this->db->where('id', $candidate_id)->update($this->table, $update_data);

        if ($result) {
            $this->update_onboarding_progress($candidate_id);
        }

        return $result;
    }

    public function update_onboarding_progress($candidate_id)
    {
        $candidate = $this->get_by_id($candidate_id);
        
        if (!$candidate) {
            return false;
        }

        $stages = [
            'stage_under_review',
            'stage_submitted_to_hm', 
            'stage_hm_decision',
            'stage_documents_decision',
            'stage_requested_docs',
            'stage_position_offered'
        ];

        $completed_stages = 0;
        $total_stages = count($stages);
        
        foreach ($stages as $stage) {
            $is_completed = false;
            
            if (isset($candidate->$stage)) {
                if ($stage === 'stage_requested_docs') {
                    if ($candidate->$stage == 1 || 
                        (isset($candidate->documents_required) && $candidate->documents_required == 0)) {
                        $is_completed = true;
                    }
                } else {
                    $is_completed = ($candidate->$stage == 1);
                }
            }
            
            if ($is_completed) {
                $completed_stages++;
            }
        }

        $current_stage = 'not_started';
        
        if ($completed_stages == $total_stages) {
            $current_stage = 'completed';
        } elseif ($completed_stages > 0) {
            foreach ($stages as $stage) {
                $is_incomplete = true;
                
                if (isset($candidate->$stage)) {
                    if ($stage === 'stage_requested_docs') {
                        $is_incomplete = ($candidate->$stage == 0 && 
                                        (!isset($candidate->documents_required) || $candidate->documents_required == 1));
                    } else {
                        $is_incomplete = ($candidate->$stage == 0);
                    }
                }
                
                if ($is_incomplete) {
                    $current_stage = $stage;
                    break;
                }
            }
        }

        if ($completed_stages == $total_stages) {
            $this->db->where('id', $candidate_id)->update($this->table, [
                'onboarding_completed_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->db->where('id', $candidate_id)->update($this->table, [
                'onboarding_completed_at' => null
            ]);
        }

        $progress_percentage = round(($completed_stages / $total_stages) * 100);

        $update_data = [
            'onboarding_stage' => $current_stage,
            'onboarding_progress' => $progress_percentage,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->where('id', $candidate_id)->update($this->table, $update_data);
    }

    public function get_jobs_by_agency($agency_id)
    {
        try {
            $this->db->select('j.*');
            $this->db->from('mod_jobs j');
            $this->db->where('j.agency_id', $agency_id);
            $this->db->where('j.removed', 0);
            $this->db->where('j.enabled', 1);
            $this->db->order_by('j.name', 'ASC');
            
            $query = $this->db->get();
            
            return $query;
            
        } catch (Exception $e) {
            return false;
        }
    }

    public function get_agency_agents_by_agency($agency_id)
    {
        try {
            $this->db->select('id, first_name, last_name, email, contact_number as phone');
            $this->db->from('agency_staff');
            $this->db->where('agency_id', $agency_id);
            $this->db->where('removed', 0);
            $this->db->where('enabled', 1);
            $this->db->order_by('first_name', 'ASC');
            
            $query = $this->db->get();
            
            return $query;
            
        } catch (Exception $e) {
            return false;
        }
    }

    public function get_candidate($identifier)
    {
        if (is_string($identifier) && strlen($identifier) == 36 && strpos($identifier, '-') !== false) {
            $this->db->where('uuid', $identifier);
        } else {
            $this->db->where('id', $identifier);
        }
        
        $this->db->where('removed', 0);
        return $this->db->get('candidates')->row();
    }

    /**
 * 🔒 Get ONLY this agency's job assignments for a candidate (SECURITY FIXED)
 */
public function get_candidate_jobs_for_agency_secure($candidate_id, $agency_id = null)
{
    if (!$agency_id) {
        $agency_id = $this->get_current_agency_id();
    }
    
    if (!$agency_id) {
        return [];
    }
    
    // First verify candidate belongs to this agency
    $this->db->select('1');
    $this->db->from('candidate_agencies ca');
    $this->db->where('ca.candidate_id', $candidate_id);
    $this->db->where('ca.agency_id', $agency_id);
    $has_access = $this->db->get()->row() !== null;
    
    if (!$has_access) {
        return [];
    }
    
    // Now get ONLY jobs from this agency
    $this->db->select('cja.*, 
                      j.name as job_name, 
                      j.uuid as job_uuid, 
                      j.reference_number as job_ref,
                      j.agency_id as job_agency_id');
    $this->db->from('candidate_job_assignments cja');
    
    // 🔒 CRITICAL: Filter by BOTH candidate_job_assignments.agency_id AND jobs.agency_id
    $this->db->join('mod_jobs j', 
                   'j.id = cja.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id) . 
                   ' AND cja.agency_id = ' . $this->db->escape($agency_id), 
                   'inner');
    
    $this->db->where('cja.candidate_id', $candidate_id);
    $this->db->where('cja.removed', 0);
    
    return $this->db->get()->result();
}

/**
 * 🔒 Debug method to check for data leaks
 */
public function debug_candidate_job_access($candidate_id, $agency_id)
{
    echo "=== DEBUG CANDIDATE JOB ACCESS ===\n";
    echo "Candidate ID: {$candidate_id}\n";
    echo "Agency ID: {$agency_id}\n\n";
    
    // What the application SHOULD show
    echo "1. Application View (Secure):\n";
    $secure_jobs = $this->get_candidate_jobs_for_agency_secure($candidate_id, $agency_id);
    foreach ($secure_jobs as $job) {
        echo "   - {$job->job_name} (Agency: {$job->job_agency_id})\n";
    }
    
    // What the raw data contains
    echo "\n2. Raw Database Data (Unfiltered):\n";
    $this->db->select('cja.*, j.name as job_name, j.agency_id as job_agency_id');
    $this->db->from('candidate_job_assignments cja');
    $this->db->join('mod_jobs j', 'j.id = cja.job_id', 'left');
    $this->db->where('cja.candidate_id', $candidate_id);
    $this->db->where('cja.removed', 0);
    $all_jobs = $this->db->get()->result();
    
    foreach ($all_jobs as $job) {
        $status = ($job->job_agency_id == $agency_id) ? "✅ YOURS" : "🚫 OTHER AGENCY";
        echo "   - {$job->job_name} (Agency: {$job->job_agency_id}) - {$status}\n";
    }
    
    // Check if candidate_job_assignments table has agency_id column
    echo "\n3. Checking candidate_job_assignments table structure:\n";
    $this->db->query("SHOW COLUMNS FROM candidate_job_assignments");
    $columns = $this->db->get()->result();
    $has_agency_column = false;
    foreach ($columns as $col) {
        echo "   - {$col->Field} ({$col->Type})\n";
        if ($col->Field == 'agency_id') {
            $has_agency_column = true;
        }
    }
    
    if (!$has_agency_column) {
        echo "\n⚠️ WARNING: candidate_job_assignments table doesn't have agency_id column!\n";
        echo "   This is causing data leaks. You need to add agency_id to this table.\n";
    }
}


    public function get_candidate_details($identifier, $agency_id = null)
    {
        if (!$agency_id) {
            $agency_id = $this->get_current_agency_id();
        }
        
        if (!$agency_id) {
            return null;
        }
        
        if (is_string($identifier) && strlen($identifier) == 36 && strpos($identifier, '-') !== false) {
            $this->db->where('c.uuid', $identifier);
        } else {
            $this->db->where('c.id', $identifier);
        }
        
        $this->db->select('c.*');
        $this->db->from('candidates c');
        $this->db->where('c.removed', 0);
        
        $candidate = $this->db->get()->row();
        
        if (!$candidate) {
            return null;
        }
        
        $this->db->select('cja.job_id, j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref, j.agency_id as job_agency_id');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 'left');
        $this->db->where('cja.candidate_id', $candidate->id);
        $this->db->where('cja.removed', 0);
        $this->db->order_by('cja.created_at', 'DESC');
        $this->db->limit(1);
        
        $job_details = $this->db->get()->row();
        
        if ($job_details) {
            foreach ($job_details as $key => $value) {
                $candidate->$key = $value;
            }
        }
        
        return $candidate;
    }

    public function get_candidate_jobs_for_agency($candidate_id, $agency_id = null)
    {
        if (!$agency_id) {
            $agency_id = $this->get_current_agency_id();
        }
        
        if (!$agency_id) {
            return [];
        }
        
        $this->db->select('cja.*, j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref, j.agency_id as job_agency_id');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 'inner');
        $this->db->where('cja.candidate_id', $candidate_id);
        $this->db->where('cja.removed', 0);
        
        return $this->db->get()->result();
    }

    public function verify_candidate_agency_access($candidate_id, $agency_id = null)
    {
        if (!$agency_id) {
            $agency_id = $this->get_current_agency_id();
        }
        
        $this->db->select('1');
        $this->db->from('candidate_agencies ca');
        $this->db->where('ca.candidate_id', $candidate_id);
        $this->db->where('ca.agency_id', $agency_id);
        $this->db->limit(1);
        
        return $this->db->get()->row() !== null;
    }

    public function get_all_secure($limit = 0, $offset = 0)
    {
        $agency_id = $this->get_current_agency_id();
        
        if (!$agency_id) {
            $user_type = getLoggedInUserTypeMenu();
            if ($user_type !== 'admin') {
                $this->db->where('candidates.id', 0);
            }
        } else {
            $this->db->join('candidate_agencies ca', 'ca.candidate_id = candidates.id AND ca.agency_id = ' . $this->db->escape($agency_id), 'inner');
            $this->db->join('candidate_job_assignments cja', 'cja.candidate_id = candidates.id AND cja.removed = 0 AND cja.agency_id = ' . $this->db->escape($agency_id), 'inner');
            $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 'inner');
        }
        
        $this->db->where('candidates.removed', 0);
        
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get($this->table);
    }

    public function get_candidate_job_for_agency($candidate_id, $agency_id)
    {
        $this->db->select('cja.*, j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id', 'inner');
        $this->db->where('cja.candidate_id', $candidate_id);
        $this->db->where('cja.removed', 0);
        $this->db->where('j.agency_id', $agency_id);
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }

    public function is_unique_email($email, $id = null)
    {
        $this->db->where('email', $email);
        $this->db->where('removed', 0);
        
        if ($id) {
            $this->db->where('id !=', $id);
        }
        
        $query = $this->db->get('candidates');
        return $query->num_rows() === 0;
    }

    public function generate_reference_number()
    {
        $prefix = 'CAND-' . date('Y') . '-';
        
        $this->db->select('reference_number');
        $this->db->from('candidates');
        $this->db->where('reference_number LIKE', $prefix . '%');
        $this->db->order_by('reference_number', 'DESC');
        $this->db->limit(1);
        
        $last_ref = $this->db->get()->row();
        
        if ($last_ref) {
            $last_number = intval(str_replace($prefix, '', $last_ref->reference_number));
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }
        
        return $prefix . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    }

    public function get_agent($agent_id)
    {
        $this->db->select('*');
        $this->db->from('agency_staff');
        $this->db->where('id', $agent_id);
        $this->db->where('removed', 0);
        
        return $this->db->get()->row();
    }

    public function create_agent_notification($notification_data)
    {
        return $this->db->insert('agent_notifications', $notification_data);
    }

    public function complete_onboarding($candidate_id)
    {
        $data = [
            'onboarding_stage' => 'completed',
            'onboarding_progress' => 100,
            'onboarding_completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $candidate_id);
        return $this->db->update('candidates', $data);
    }

    public function get_required_documents($identifier)
    {
        $candidate = $this->get_candidate($identifier);
        if (!$candidate) {
            return [];
        }
        
        $candidate_id = $candidate->id;
        $agency_id = $this->get_current_agency_id();
        
        if (!$agency_id) {
            return [];
        }
        
        $has_access = $this->check_agency_candidate_access($agency_id, $candidate_id);
        
        if (!$has_access) {
            return [];
        }

        $this->db->select('cd.*, 
                          CASE 
                              WHEN cd.uploaded_by_type = "recruiter" THEN CONCAT(r.first_name, " ", r.last_name)
                              WHEN cd.uploaded_by_type = "agency" THEN CONCAT(a.first_name, " ", a.last_name)
                              ELSE "System"
                          END as uploader_name');
        $this->db->from('candidate_documents cd');
        $this->db->join('recruiters r', 'r.id = cd.uploaded_by AND cd.uploaded_by_type = "recruiter"', 'left');
        $this->db->join('agency_staff a', 'a.id = cd.uploaded_by AND cd.uploaded_by_type = "agency"', 'left');
        $this->db->where('cd.candidate_id', $candidate_id);
        $this->db->where('cd.removed', 0);
        $this->db->order_by('cd.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    public function check_documents_submission_status($identifier)
    {
        $required_documents = $this->get_required_documents($identifier);
        
        return [
            'has_documents' => !empty($required_documents),
            'document_count' => count($required_documents),
            'documents' => $required_documents
        ];
    }

    public function check_and_update_documents_stage($candidate_id)
    {
        $this->db->select('COUNT(*) as doc_count');
        $this->db->from('candidate_documents');
        $this->db->where('candidate_id', $candidate_id);
        $this->db->where('document_type', 'required_document');
        $this->db->where('removed', 0);
        
        $result = $this->db->get()->row();
        $has_documents = $result && $result->doc_count > 0;
        
        if ($has_documents) {
            $this->db->where('id', $candidate_id);
            $this->db->update('candidates', [
                'stage_requested_docs' => 1,
                'stage_requested_docs_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->update_onboarding_progress($candidate_id);
            
            return true;
        }
        
        return false;
    }

    public function save_candidate_document($document_data)
    {
        return $this->db->insert('candidate_documents', $document_data);
    }

    public function get_candidate_documents($candidate_id)
    {
        $this->db->select('cd.*, 
                        CASE 
                            WHEN cd.uploaded_by_type = "recruiter" THEN CONCAT(r.first_name, " ", r.last_name)
                            WHEN cd.uploaded_by_type = "agency" THEN CONCAT(a.first_name, " ", a.last_name)
                            ELSE "System"
                        END as uploader_name');
        $this->db->from('candidate_documents cd');
        $this->db->join('recruiters r', 'r.id = cd.uploaded_by AND cd.uploaded_by_type = "recruiter"', 'left');
        $this->db->join('agency_staff a', 'a.id = cd.uploaded_by AND cd.uploaded_by_type = "agency"', 'left');
        $this->db->where('cd.candidate_id', $candidate_id);
        $this->db->where('cd.removed', 0);
        $this->db->order_by('cd.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    public function apply_agency_filter($agency_id = null)
    {
        if (!$agency_id) {
            $agency_id = $this->get_current_agency_id();
        }
        
        if ($agency_id) {
            $this->db->join('candidate_agencies ca', 'ca.candidate_id = candidates.id', 'inner');
            $this->db->where('ca.agency_id', $agency_id);
            $this->db->group_by('candidates.id');
        }
        
        return $this;
    }

    public function create_documents_request_notification($candidate_id, $job_id, $agency_id, $documents_notes, $requesting_agency_id = null) {
        $candidate = $this->get_candidate($candidate_id);
        if (!$candidate) {
            return false;
        }
        
        $candidate_id = $candidate->id;
        
        try {
            $this->db->select('first_name, last_name, reference_number');
            $this->db->from('candidates');
            $this->db->where('id', $candidate_id);
            $candidate = $this->db->get()->row();
            
            if (!$candidate) {
                return false;
            }

            $job_name = 'Unknown Job';
            if ($job_id) {
                $this->db->select('name');
                $this->db->from('mod_jobs');
                $this->db->where('id', $job_id);
                $job = $this->db->get()->row();
                if ($job) {
                    $job_name = $job->name;
                }
            }

            $requesting_agency_name = 'Hiring Manager';
            if ($requesting_agency_id) {
                $this->db->select('name');
                $this->db->from('agencies');
                $this->db->where('id', $requesting_agency_id);
                $agency = $this->db->get()->row();
                if ($agency) {
                    $requesting_agency_name = $agency->name;
                }
            }

            $this->db->select('id, first_name, last_name');
            $this->db->from('recruiters');
            $this->db->where('agency_id', $agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $recruiters = $this->db->get()->result();

            if (empty($recruiters)) {
                return false;
            }

            $notifications_created = 0;

            foreach ($recruiters as $recruiter) {
                $notification_data = [
                    'title' => '📋 Additional Documents Required',
                    'message' => "The hiring manager ({$requesting_agency_name}) requires additional documents for candidate {$candidate->first_name} {$candidate->last_name} ({$candidate->reference_number}) for position: {$job_name}.",
                    'type' => 'hm_decision',
                    'sender_type' => 'agency',
                    'sender_id' => $requesting_agency_id,
                    'receiver_type' => 'recruiter',
                    'receiver_id' => $recruiter->id,
                    'related_entity' => 'candidate',
                    'related_entity_id' => $candidate_id,
                    'metadata' => json_encode([
                        'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                        'candidate_reference' => $candidate->reference_number,
                        'job_name' => $job_name,
                        'requesting_agency' => $requesting_agency_name,
                        'required_documents' => $documents_notes,
                        'action_required' => 'Please upload the required documents to the candidate profile',
                        'notification_type' => 'documents_request',
                        'action_url' => site_url("recruiter/candidates/view/{$candidate_id}#documents")
                    ]),
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'enabled' => 1
                ];

                if ($this->db->insert('notifications', $notification_data)) {
                    $notifications_created++;
                }
            }
            return $notifications_created > 0;

        } catch (Exception $e) {
            return false;
        }
    }

    public function create_documents_uploaded_notification($candidate_id, $uploaded_by_recruiter_id, $document_count = 1) {
        try {
            $this->db->select('first_name, last_name, reference_number, agency_id');
            $this->db->from('candidates');
            $this->db->where('id', $candidate_id);
            $candidate = $this->db->get()->row();
            
            if (!$candidate) {
                return false;
            }

            $this->db->select('first_name, last_name, agency_id');
            $this->db->from('recruiters');
            $this->db->where('id', $uploaded_by_recruiter_id);
            $recruiter = $this->db->get()->row();

            if (!$recruiter) {
                return false;
            }

            $this->db->select('id, first_name, last_name');
            $this->db->from('agency_staff');
            $this->db->where('agency_id', $candidate->agency_id);
            $this->db->where('enabled', 1);
            $this->db->where('removed', 0);
            $agency_users = $this->db->get()->result();

            if (empty($agency_users)) {
                return false;
            }

            $notifications_created = 0;

            foreach ($agency_users as $agency_user) {
                $notification_data = [
                    'title' => '📄 Documents Uploaded',
                    'message' => "Recruiter {$recruiter->first_name} {$recruiter->last_name} has uploaded {$document_count} document(s) for candidate {$candidate->first_name} {$candidate->last_name} ({$candidate->reference_number}).",
                    'type' => 'candidate_applied',
                    'sender_type' => 'recruiter',
                    'sender_id' => $uploaded_by_recruiter_id,
                    'receiver_type' => 'agency',
                    'receiver_id' => $agency_user->id,
                    'related_entity' => 'candidate',
                    'related_entity_id' => $candidate_id,
                    'metadata' => json_encode([
                        'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                        'candidate_reference' => $candidate->reference_number,
                        'recruiter_name' => $recruiter->first_name . ' ' . $recruiter->last_name,
                        'document_count' => $document_count,
                        'action_required' => 'Review the uploaded documents',
                        'notification_type' => 'documents_uploaded',
                        'action_url' => site_url("agency/candidates/view/{$candidate_id}#documents")
                    ]),
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'enabled' => 1
                ];

                if ($this->db->insert('notifications', $notification_data)) {
                    $notifications_created++;
                }
            }
            return $notifications_created > 0;

        } catch (Exception $e) {
            return false;
        }
    }

    public function create_hm_decision_notification($candidate_id, $job_id, $agency_id, $decision, $notes = '', $requesting_agency_id = null) {
    try {
        // Get candidate details
        $this->db->select('first_name, last_name, reference_number, recruiter_id');
        $this->db->from('candidates');
        $this->db->where('id', $candidate_id);
        $candidate = $this->db->get()->row();
        
        if (!$candidate) {
            log_message('error', 'Candidate not found for notification: ' . $candidate_id);
            return false;
        }
        
        // ============ CRITICAL: Check if candidate has recruiter_id ============
        if (empty($candidate->recruiter_id)) {
            log_message('error', 'Candidate has no recruiter_id: ' . $candidate_id);
            return false;
        }
        
        $recruiter_id = $candidate->recruiter_id;
        
        // Get recruiter to verify
        $recruiter = $this->db->where('id', $recruiter_id)
                             ->where('enabled', 1)
                             ->where('removed', 0)
                             ->get('recruiters')
                             ->row();
        
        if (!$recruiter) {
            log_message('error', 'Recruiter not found or disabled: ' . $recruiter_id);
            return false;
        }
        // ============ END CRITICAL ============
        
        // Rest of your method stays the same, but use $recruiter_id directly
        // Instead of querying for all recruiters in an agency
        
        $notification_data = [
            'title' => "Candidate {$decision}: {$candidate->first_name} {$candidate->last_name}",
            'message' => "The hiring manager has {$decision} candidate {$candidate->first_name} {$candidate->last_name}",
            'type' => 'hm_decision',
            'sender_type' => 'agency',
            'sender_id' => $requesting_agency_id,
            'receiver_type' => 'recruiter',
            'receiver_id' => $recruiter_id, // Use the candidate's recruiter_id
            'related_entity' => 'candidate',
            'related_entity_id' => $candidate_id,
            'metadata' => json_encode([
                'decision' => $decision,
                'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                'candidate_reference' => $candidate->reference_number,
                'notes' => $notes
            ]),
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'enabled' => 1
        ];
        
        // Insert notification
        $result = $this->db->insert('notifications', $notification_data);
        
        if ($result) {
            log_message('debug', "HM decision notification sent to recruiter {$recruiter_id}");
            return true;
        } else {
            log_message('error', "Failed to insert HM decision notification");
            return false;
        }
        
    } catch (Exception $e) {
        log_message('error', "ERROR in create_hm_decision_notification: " . $e->getMessage());
        return false;
    }
}

    public function update_documents_decision($candidate_id, $documents_required, $documents_notes) {
        $update_data = [
            'stage_documents_decision' => 1,
            'documents_required' => $documents_required,
            'documents_notes' => $documents_notes ?: null,
            'stage_documents_decision_at' => date('Y-m-d H:i:s'),
            'onboarding_stage' => 'stage_documents_decision',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $candidate_id);
        $result = $this->db->update('candidates', $update_data);
        
        if ($result) {
            $this->update_onboarding_progress($candidate_id);
        }
        
        return $result;
    }

    private function get_empty_stats_object()
    {
        return (object)[
            'total_candidates' => 0,
            'under_review_count' => 0,
            'submitted_hm_count' => 0,
            'hm_decision_count' => 0,
            'requested_docs_count' => 0,
            'position_offered_count' => 0,
            'completed_count' => 0,
            'hm_accepted_count' => 0,
            'hm_rejected_count' => 0
        ];
    }

    private function check_agency_candidate_access($agency_id, $candidate_id) {
        $this->db->select('1')
                 ->from('candidate_agencies')
                 ->where('candidate_id', $candidate_id)
                 ->where('agency_id', $agency_id);
        
        return $this->db->get()->row() !== null;
    }

    public function get_document($document_id)
    {
        return $this->db->where('id', $document_id)
                        ->where('removed', 0)
                        ->get('candidate_documents')
                        ->row();
    }

    public function delete_candidate_document($document_id)
    {
        return $this->db->where('id', $document_id)
                        ->update('candidate_documents', [
                            'removed' => 1,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
    }

    public function check_recruiter_candidate_access($recruiter_id, $identifier)
    {
        $candidate = $this->get_candidate($identifier);
        if (!$candidate) {
            return false;
        }
        
        $candidate_id = $candidate->id;
        
        $recruiter = $this->db->select('agency_id')
                         ->from('recruiters')
                         ->where('id', $recruiter_id)
                         ->where('enabled', 1)
                         ->where('removed', 0)
                         ->get()
                         ->row();
    
        if (!$recruiter) {
            return false;
        }

        $this->db->select('1')
                 ->from('candidate_agencies')
                 ->where('candidate_id', $candidate_id)
                 ->where('agency_id', $recruiter->agency_id);
    
        return $this->db->get()->row() !== null;
    }

    public function log_candidate_activity($logData){
        try {
            if ($this->db->table_exists('candidate_activity_logs')) {
                return $this->db->insert('candidate_activity_logs', $logData);
            }
            return false;
        } catch (Exception $e) {
            error_log('Error in log_candidate_activity: ' . $e->getMessage());
            return false;
        }
    }

    public function get_onboarding_stats($agency_id = null)
    {
        $agency_id = $agency_id ?: $this->get_current_agency_id();
        
        if (!$agency_id) {
            return $this->get_empty_stats_object();
        }

        try {
            $this->db->select('
                COUNT(DISTINCT cop.id) as total_candidates,
                COUNT(DISTINCT CASE WHEN cop.stage_under_review = 1 THEN cop.id END) as under_review_count,
                COUNT(DISTINCT CASE WHEN cop.stage_submitted_to_hm = 1 THEN cop.id END) as submitted_hm_count,
                COUNT(DISTINCT CASE WHEN cop.stage_hm_decision = 1 THEN cop.id END) as hm_decision_count,
                COUNT(DISTINCT CASE WHEN cop.stage_requested_docs = 1 THEN cop.id END) as requested_docs_count,
                COUNT(DISTINCT CASE WHEN cop.stage_position_offered = 1 THEN cop.id END) as position_offered_count,
                COUNT(DISTINCT CASE WHEN cop.onboarding_stage = "completed" THEN cop.id END) as completed_count,
                COUNT(DISTINCT CASE WHEN cop.hm_decision = "accepted" THEN cop.id END) as hm_accepted_count,
                COUNT(DISTINCT CASE WHEN cop.hm_decision = "rejected" THEN cop.id END) as hm_rejected_count
            ');
            
            $this->db->from('candidate_onboarding_progress cop');
            $this->db->join('mod_jobs j', 'j.id = cop.job_id AND j.removed = 0', 'inner');
            $this->db->where('j.agency_id', $agency_id);
            $this->db->where('cop.agency_id', $agency_id);

            $result = $this->db->get()->row();

            return $result;

        } catch (Exception $e) {
            log_message('error', 'Error in get_onboarding_stats: ' . $e->getMessage());
            return $this->get_empty_stats_object();
        }
    }
    
    public function get_candidates_for_listing($agency_id = null, $limit = null, $offset = null, $filters = [])
    {
        $this->db->select('c.*, c.uuid as candidate_uuid, 
                           j.name as job_name, j.uuid as job_uuid,
                           ca.agency_id');
        $this->db->from('candidates c');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id', 'inner');
        $this->db->join('mod_jobs j', 'j.id = c.job_id', 'left');
        
        if ($agency_id) {
            $this->db->where('ca.agency_id', $agency_id);
        }
        
        $this->db->where('c.removed', 0);
        
        if (!empty($filters)) {
            if (!empty($filters['status'])) {
                $this->db->where('c.status', $filters['status']);
            }
            if (!empty($filters['onboarding_stage'])) {
                $this->db->where('c.onboarding_stage', $filters['onboarding_stage']);
            }
            if (!empty($filters['search'])) {
                $this->db->group_start();
                $this->db->like('c.first_name', $filters['search']);
                $this->db->or_like('c.last_name', $filters['search']);
                $this->db->or_like('c.email', $filters['search']);
                $this->db->or_like('c.reference_number', $filters['search']);
                $this->db->group_end();
            }
        }
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        $this->db->order_by('c.first_name', 'ASC');
        $this->db->group_by('c.id');
        
        return $this->db->get()->result();
    }

    public function get_candidate_uuid($identifier)
    {
        if (is_string($identifier) && strlen($identifier) == 36 && strpos($identifier, '-') !== false) {
            return $identifier;
        } else {
            $this->db->select('uuid');
            $this->db->from('candidates');
            $this->db->where('id', $identifier);
            $this->db->where('removed', 0);
            $result = $this->db->get()->row();
            
            return $result ? $result->uuid : null;
        }
    }

    public function get_candidate_id_from_uuid($uuid)
    {
        $this->db->select('id');
        $this->db->from('candidates');
        $this->db->where('uuid', $uuid);
        $this->db->where('removed', 0);
        $result = $this->db->get()->row();
        
        return $result ? $result->id : null;
    }

    public function uuid_exists($uuid)
    {
        $this->db->select('1');
        $this->db->from('candidates');
        $this->db->where('uuid', $uuid);
        $this->db->where('removed', 0);
        
        return $this->db->get()->num_rows() > 0;
    }

    public function generate_uuid()
    {
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        } else {
            return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
            );
        }
    }

    public function get_candidate_details_by_agency($identifier, $agency_id = null)
    {
        if (!$agency_id) {
            $agency_id = $this->get_current_agency_id();
        }
        
        $candidate = $this->get_candidate($identifier);
        if (!$candidate) {
            return null;
        }
        
        $this->db->select('cja.*, j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref, j.agency_id as job_agency_id');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id', 'inner');
        $this->db->where('cja.candidate_id', $candidate->id);
        $this->db->where('cja.removed', 0);
        $this->db->where('j.agency_id', $agency_id);
        $this->db->limit(1);
        
        $job_assignment = $this->db->get()->row();
        
        if ($job_assignment) {
            $candidate->job_name = $job_assignment->job_name;
            $candidate->job_uuid = $job_assignment->job_uuid;
            $candidate->job_id = $job_assignment->job_id;
            $candidate->job_ref = $job_assignment->job_ref;
            $candidate->job_agency_id = $job_assignment->job_agency_id;
        } else {
            $candidate->job_name = null;
            $candidate->job_uuid = null;
            $candidate->job_id = null;
            $candidate->job_ref = null;
            $candidate->job_agency_id = null;
        }
        
        return $candidate;
    }

    public function debug_candidate_assignments($candidate_id)
    {
        log_message('debug', '=== DEBUG CANDIDATE ASSIGNMENTS ===');
        log_message('debug', 'Candidate ID: ' . $candidate_id);
        
        $this->db->select('cja.*, j.name as job_name, j.uuid as job_uuid, j.agency_id as job_agency_id');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id', 'left');
        $this->db->where('cja.candidate_id', $candidate_id);
        $this->db->where('cja.removed', 0);
        $assignments = $this->db->get()->result();
        
        foreach ($assignments as $assignment) {
            log_message('debug', 'Assignment - Job ID: ' . $assignment->job_id . 
                       ', Job Name: ' . $assignment->job_name . 
                       ', Agency ID: ' . $assignment->job_agency_id);
        }
        
        return $assignments;
    }

public function get_candidate_details_by_agency_and_job($identifier, $agency_id = null, $job_uuid = null)
{
    if (!$agency_id) {
        $agency_id = $this->get_current_agency_id();
    }
    
    if (!$agency_id) {
        return null;
    }
    
    // Get candidate basic info
    if (is_string($identifier) && strlen($identifier) == 36 && strpos($identifier, '-') !== false) {
        $this->db->where('c.uuid', $identifier);
    } else {
        $this->db->where('c.id', $identifier);
    }
    
    $this->db->select('c.*');
    $this->db->from('candidates c');
    $this->db->where('c.removed', 0);
    
    // Check access through candidate_agencies
    $this->db->join('candidate_agencies ca', 'ca.candidate_id = c.id AND ca.agency_id = ' . $this->db->escape($agency_id), 'inner');
    
    $candidate = $this->db->get()->row();
    
    if (!$candidate) {
        return null;
    }
    
    $candidate_id = $candidate->id;
    
    // Now get the SPECIFIC job assignment based on job_uuid parameter
    if ($job_uuid) {
        // Get the specific job assignment
        $this->db->select('cja.*, j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref, j.agency_id as job_agency_id');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.uuid = "' . $this->db->escape_str($job_uuid) . '"', 'inner');
        $this->db->where('cja.candidate_id', $candidate_id);
        $this->db->where('cja.removed', 0);
        $this->db->where('j.agency_id', $agency_id);
        
        $job_assignment = $this->db->get()->row();
        
        if ($job_assignment) {
            // Add job info to candidate object
            foreach ($job_assignment as $key => $value) {
                if ($key != 'id') { // Avoid overwriting candidate id
                    $candidate->$key = $value;
                }
            }
            return $candidate;
        }
    }
    
    // If no specific job_uuid provided, get the FIRST job assignment for this agency
    $this->db->select('cja.*, j.name as job_name, j.uuid as job_uuid, j.reference_number as job_ref, j.agency_id as job_agency_id');
    $this->db->from('candidate_job_assignments cja');
    $this->db->join('mod_jobs j', 'j.id = cja.job_id AND j.agency_id = ' . $this->db->escape($agency_id), 'inner');
    $this->db->where('cja.candidate_id', $candidate_id);
    $this->db->where('cja.removed', 0);
    $this->db->order_by('cja.created_at', 'DESC'); // Get most recent first
    $this->db->limit(1);
    
    $job_assignment = $this->db->get()->row();
    
    if ($job_assignment) {
        foreach ($job_assignment as $key => $value) {
            if ($key != 'id') {
                $candidate->$key = $value;
            }
        }
    } else {
        // No job assignment found for this agency
        $candidate->job_name = null;
        $candidate->job_uuid = null;
        $candidate->job_ref = null;
        $candidate->job_agency_id = null;
    }
    
    return $candidate;
}

    public function get_onboarding_candidates_by_agency($agency_id, $limit = null, $offset = null)
    {
        $this->db->select('cop.*, c.first_name, c.last_name, c.reference_number, c.uuid as candidate_uuid,
                          j.name as job_name, j.uuid as job_uuid, j.agency_id as job_agency_id');
        $this->db->from('candidate_onboarding_progress cop');
        $this->db->join('candidates c', 'c.id = cop.candidate_id AND c.removed = 0', 'inner');
        $this->db->join('mod_jobs j', 'j.id = cop.job_id AND j.removed = 0 AND j.agency_id = ' . $this->db->escape($agency_id), 'inner');
        $this->db->where('cop.agency_id', $agency_id);
        $this->db->group_by('cop.id');
        $this->db->order_by('cop.onboarding_progress', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    public function get_candidate_by_uuid($uuid)
    {
        return $this->db->where('uuid', $uuid)
                        ->where('removed', 0)
                        ->get('candidates')
                        ->row();
    }

    public function get_onboarding_progress($candidate_id, $job_id, $agency_id)
    {
        return $this->db->where('candidate_id', $candidate_id)
                        ->where('job_id', $job_id)
                        ->where('agency_id', $agency_id)
                        ->get('candidate_onboarding_progress')
                        ->row();
    }

    public function update_onboarding_progress_record($candidate_id, $job_id, $agency_id, $data)
    {
        $existing = $this->get_onboarding_progress($candidate_id, $job_id, $agency_id);
        
        if ($existing) {
            $this->db->where('id', $existing->id);
            $this->db->update('candidate_onboarding_progress', $data);
            return $existing->id;
        } else {
            $data['candidate_id'] = $candidate_id;
            $data['job_id'] = $job_id;
            $data['agency_id'] = $agency_id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $this->db->insert('candidate_onboarding_progress', $data);
            return $this->db->insert_id();
        }
    }

    public function update_onboarding_stage_scoped($candidate_id, $job_id, $agency_id, $stage, $value)
    {
        $progress = $this->get_onboarding_progress($candidate_id, $job_id, $agency_id);
        
        if (!$progress) {
            $progress_data = [
                'candidate_id' => $candidate_id,
                'job_id' => $job_id,
                'agency_id' => $agency_id,
                $stage => $value,
                $stage . '_at' => ($value == 1) ? date('Y-m-d H:i:s') : null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('candidate_onboarding_progress', $progress_data);
            $progress_id = $this->db->insert_id();
        } else {
            $update_data = [
                $stage => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($value == 1) {
                $update_data[$stage . '_at'] = date('Y-m-d H:i:s');
            } else {
                $update_data[$stage . '_at'] = null;
            }
            
            $this->db->where('id', $progress->id);
            $this->db->update('candidate_onboarding_progress', $update_data);
            $progress_id = $progress->id;
        }
        
        if ($progress_id) {
            $this->recalculate_onboarding_progress($progress_id);
        }
        
        return true;
    }

    public function recalculate_onboarding_progress($onboarding_id)
    {
        $progress = $this->db->where('id', $onboarding_id)
                            ->get('candidate_onboarding_progress')
                            ->row();
        
        if (!$progress) return false;
        
        $stages = [
            'stage_under_review',
            'stage_submitted_to_hm', 
            'stage_hm_decision',
            'stage_documents_decision',
            'stage_requested_docs',
            'stage_position_offered'
        ];
        
        $completed_stages = 0;
        $total_stages = count($stages);
        
        foreach ($stages as $stage) {
            if ($progress->$stage == 1) {
                $completed_stages++;
            }
        }
        
        $current_stage = 'not_started';
        
        if ($completed_stages == $total_stages) {
            $current_stage = 'completed';
        } elseif ($completed_stages > 0) {
            foreach ($stages as $stage) {
                if ($progress->$stage == 0) {
                    $current_stage = $stage;
                    break;
                }
            }
        }
        
        $progress_percentage = round(($completed_stages / $total_stages) * 100);
        
        $update_data = [
            'onboarding_stage' => $current_stage,
            'onboarding_progress' => $progress_percentage,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($current_stage === 'completed') {
            $update_data['onboarding_completed_at'] = date('Y-m-d H:i:s');
        } else {
            $update_data['onboarding_completed_at'] = null;
        }
        
        $this->db->where('id', $onboarding_id);
        return $this->db->update('candidate_onboarding_progress', $update_data);
    }

    public function audit_security_leaks($agency_id = null)
    {
        if (!$agency_id) {
            $agency_id = $this->get_current_agency_id();
        }
        
        $leaks = [];
        
        $this->db->select('COUNT(DISTINCT cja.id) as leak_count,
                          GROUP_CONCAT(DISTINCT j.agency_id) as leaked_agencies');
        $this->db->from('candidate_job_assignments cja');
        $this->db->join('mod_jobs j', 'j.id = cja.job_id');
        $this->db->join('candidate_agencies ca', 'ca.candidate_id = cja.candidate_id AND ca.agency_id = ' . $this->db->escape($agency_id));
        $this->db->where('cja.removed', 0);
        $this->db->where('j.agency_id !=', $agency_id);
        
        $job_leak = $this->db->get()->row();
        
        if ($job_leak->leak_count > 0) {
            $leaks[] = [
                'type' => 'job_data_leak',
                'count' => $job_leak->leak_count,
                'agencies' => $job_leak->leaked_agencies,
                'message' => '❌ Can see ' . $job_leak->leak_count . ' job assignments from other agencies'
            ];
        }
        
        return $leaks;
    }
}