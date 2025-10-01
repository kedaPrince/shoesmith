<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class CRUD_model extends MY_Model {

    protected $table = '';

    public function __construct() {
        parent::__construct();
    }

    public function get_all($section = '') {
        $this->main_selects();
        $this->main_joins();
        $this->main_wheres();
        $this->main_grouping();
        $this->main_filters();
        $this->main_limit();
        $this->main_sorting();

        $query = $this->db->get($this->table); 

        return $query;
    }

    public function get_all_export() {

        //Overide field data
        $this->listFields = $this->export['fields'];

	    $this->main_selects();
	    $this->main_joins();
	    $this->main_wheres();
	    $this->main_grouping();
	    $this->main_filters();
	    $this->main_sorting();

	    $query = $this->db->get($this->table);

	    return $query;
    }

    /**
     * Get Count
     *
     * Returns the total of number of rows for the listing.
     *
     * @return int
     */
    public function get_count() {
        $this->db->select('COUNT(DISTINCT ' . $this->table . '.id) AS row_count');

        $this->main_joins();
        $this->main_wheres();
        $this->main_grouping();
        $this->main_filters();

        $query = $this->db->get($this->table);
        if ($query->num_rows() > 0) {
            $row = $query->row();

            return $row->row_count;
        } else {
            $message = "Query for count returned 0 rows in the the CRUD_model";
            anomalies::log($message, $this->db->last_query());
        }
    }

    public function main_selects() {
        // $this->db->select($this->table . '.*');

        $this->db->select($this->table.'.id');
        $this->db->select($this->table.'.enabled');

        foreach ($this->listFields as $field => $options) {
            $type           = isset($options['type']) ? $options['type'] : 'field';
            $selectField    = isset($options['field']) ? $options['field'].' AS '.$field : $this->table.'.'.$field;
            $escape         = isset($options['escape']) ? $options['escape'] : true;

            if ($type == 'field') {
                //Standard field select
                $this->db->select($selectField, $escape);
            }
            elseif ($type == 'tags') {
                //Clickable tags
                $tagTable       = isset($options['tagTable']) ? $options['tagTable'] : $this->table;
                $tagID          = isset($options['tagID']) ? $options['tagID'] : 'id';
                $tagField       = isset($options['tagField']) ? $options['tagField'] : $tagTable.'.name';
                $tagWheres      = isset($options['tagWheres']) ? $options['tagWheres'] : 'AND '.$tagTable.'.removed = 0';
                $tagOrder       = isset($options['tagOrder']) ? $options['tagOrder'] : $tagField. ' ASC';
                $parentTable    = isset($options['parentTable']) ? $options['parentTable'] : $this->table;
                $parentField    = isset($options['parentField']) ? $options['parentField'] : 'id';
                $linkTable      = isset($options['linkTable']) ? $options['linkTable'] :$tagTable;
                $linkField      = isset($options['linkField']) ? $options['linkField'] : 'id';
                $selectFieldExtra = isset($options['selectFieldExtra']) ? $options['selectFieldExtra'] : '';
                if (!empty($selectFieldExtra)) {
                    $selectFieldExtra = $tagTable.'.'.$selectFieldExtra;
                }

                $tagJoins = '';
                if (!empty($options['tagJoins'])) {
                    foreach (force_multidimensional_array($options['tagJoins']) as $join) {
                        $joinType           = isset($join['type']) ? $join['type'] : 'INNER';
                        $joinParentTable    = isset($join['parentTable']) ? $join['parentTable'] : $options['tagTable'];
                        $joinParentField    = isset($join['parentField']) ? $join['parentField'] : 'id';
                        $joinExtra          = isset($join['extra']) ? $join['extra'] : '';
    
                        $tagJoins .= '
                            '.$joinType.' JOIN '.$join['table'].' ON '.$join['table'].'.'.$join['field'].' = '.$joinParentTable.'.'.$joinParentField.'
                                '.$joinExtra.'
                        ';
                    }
                }
                $concat = $tagTable.'.'.$tagID.', "|",'.$tagField;
                if (!empty($selectFieldExtra)) {
                    $concat .= ', "|", '.$selectFieldExtra;
                }

                $this->db->select('(
                    SELECT GROUP_CONCAT(DISTINCT CONCAT('.$concat.') ORDER BY '.$tagOrder.')
                    FROM '.$tagTable.'
                    '.$tagJoins.'
                    WHERE '.$linkTable.'.'.$linkField.' = '.$parentTable.'.'.$parentField.'
                    '.$tagWheres.'
                ) AS '.$field, false);


            }
            elseif ($type == 'count') {
                //Count subquery
                $countTable     = isset($options['countTable']) ? $options['countTable'] : $this->table;
                $countField     = isset($options['countField']) ? $options['countField'] : $countTable.'.id';
                $countWheres    = isset($options['countWheres']) ? $options['countWheres'] : 'AND '.$countTable.'.removed = 0';
                $parentTable    = isset($options['parentTable']) ? $options['parentTable'] : $this->table;
                $parentField    = isset($options['parentField']) ? $options['parentField'] : 'id';
                $linkTable      = isset($options['linkTable']) ? $options['linkTable'] : $countTable;
                $linkField      = isset($options['linkField']) ? $options['linkField'] : 'id';

                $countJoins = '';
                if (!empty($options['countJoins'])) {
                    foreach (force_multidimensional_array($options['countJoins']) as $join) {
                        $joinType           = isset($join['type']) ? $join['type'] : 'INNER';
                        $joinParentTable    = isset($join['parentTable']) ? $join['parentTable'] : $options['countTable'];
                        $joinParentField    = isset($join['parentField']) ? $join['parentField'] : 'id';
                        $joinExtra          = isset($join['extra']) ? $join['extra'] : '';
    
                        $countJoins .= '
                            '.$joinType.' JOIN '.$join['table'].' ON '.$join['table'].'.'.$join['field'].' = '.$joinParentTable.'.'.$joinParentField.'
                                '.$joinExtra.'
                        ';
                    }
                }

                $this->db->select('(
                    SELECT COUNT(DISTINCT '.$countField.') 
                    FROM '.$countTable.'
                    '.$countJoins.'
                    WHERE '.$linkTable.'.'.$linkField.' = '.$parentTable.'.'.$parentField.'
                    '.$countWheres.'
                ) AS '.$field, false);
            }

        }

        //Extra selects
        $this->selects();
    }

    public function selects() {
    }

    public function main_joins() {
        //Extra joins
        $this->joins();
    }

    public function joins() {
    }

    public function main_wheres() {
        $this->db->where($this->table . '.removed', 0);

        $submodules = $this->session->submodules;
        if (!empty($submodules[$this->pageName])) {
            $submodule = $submodules[$this->pageName];
            $table = !empty($submodule->table) ? $submodule->table : $this->table;
            $this->db->where($table.'.'.$submodule->field, $submodule->id);
        }

        //Extra listing restrictions
        $this->wheres();
    }

    public function wheres() {
    }

    public function main_grouping() {
        $this->grouping();
    }

    public function grouping() {
    }

    public function main_filters() {
        //Check if any filters has been set
        $filters = get_ecms_filters($this->pageName);
        if (!empty($filters)) {

            //Loop through
            foreach ($filters as $filterName => $options) {

                //Format date values back to db format
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

        //Extra filters
        $this->filters();
    }

    public function filters() {
    }

    /**
     * Filters SQL
     *
     * Alternative function to filters() which does not make use of the
     * active record class, but instead returns the sql.
     *
     * Use $queryGroup if this function is going to be used multiple times in the same query
     * For example in unions or subqueries.
     *
     * @param string $queryGroup
     *
     * @return string
     */
    public function filters_sql($queryGroup = '') {
        $sql = '';
        $conditions = array();
        $conditionGroup = array();
        //Check if any filters has been set

        $filters = get_ecms_filters($this->pageName);
        if (!empty($filters)) {

            //Loop through
            foreach ($filters as $filterName => $options) {

                $optionsSQL = !empty($options['sql']) ? $options['sql'] : '';
                $optionsSQL = !empty($optionsSQL[$queryGroup]) ? $optionsSQL[$queryGroup] : $optionsSQL;
                if (!empty($optionsSQL)) {
                    array_push($conditions, $optionsSQL);
                } else {
                    $escape = !empty($options['escape']) ? $options['escape'] : true;

                    $conditionGroup = array();
                    $optionsField = !empty($options['field']) ? $options['field'] : array();
                    $optionsField = !empty($optionsField[$queryGroup]) ? $optionsField[$queryGroup] : $optionsField;

                    foreach ((array) $optionsField as $key => $field) {
                        if ($options['type'] == 'date_range') {
                            if ($escape) {
                                array_push($conditionGroup, '(' . $field . ' >= "' . $this->db->escape_str($options['value']['from']) . '" AND ' . $field . ' <= "' . $this->db->escape_str($options['value']['to']) . '")');
                            } else {
                                array_push($conditionGroup, '(' . $field . ' >= "' . $options['value']['from'] . '" AND ' . $field . ' <= "' . $options['value']['to'] . '")');
                            }
                        }
                        else if ($options['type'] == 'date_period') {
                            if ($escape) {
                                array_push($conditionGroup, '(' . $options['field'] . ' >= "' . $this->db->escape_str($options['value']['from']) . '" AND ' . $options['field2'] . ' <= "' . $this->db->escape_str($options['value']['to']) . '")');
                            } else {
                                array_push($conditionGroup, '(' . $options['field'] . ' >= "' . $options['value']['from'] . '" AND ' . $options['field2'] . ' <= "' . $options['value']['to'] . '")');
                            }
                        }
                        else if ($options['type'] == 'autocomplete') {
                            $orGroup = array();

                            foreach ((array) $field as $f) {
                                array_push($orGroup, $f . ' LIKE "%' . ($escape ? $this->db->escape_str($options['value']) : $options['value']) . '%"');
                            }

                            //Add or group to conditions group
                            if (!empty($orGroup)) {
                                array_push($conditionGroup, '(' . implode(' OR ', $orGroup) . ')');
                            }
                        } else {
                            array_push($conditionGroup, $field . ' = "' . ($escape ? $this->db->escape_str($options['value']) : $options['value']) . '"');
                        }
                    }

                    //Add conditions group to main conditions array
                    if (!empty($conditionGroup)) {
                        array_push($conditions, '(' . implode(' AND ', $conditionGroup) . ')');
                    }
                }
            }
        }

        //Build sql
        if (!empty($conditions)) {
            $sql = '(' . implode(' AND ', $conditionGroup) . ')';
        }

        return $sql;
    }

    public function main_limit() {
        //Get row limit
        if (!empty($this->session->{$this->pageName . 'RowLimit'})) {
            $rl = $this->session->{$this->pageName . 'RowLimit'};
        } else if (!empty($this->rowLimit)) {
            $rl = $this->rowLimit;
        } else {
            $rl = $this->config->item('CRUD_row_limit');
        }

        //Fix for some weird php bug where the empty() function sees this as empty even though it's not.
        $this->page = $this->page;

        //Get page number (default 1) and work out offset
        if (!empty($this->page)) {
            $page = $this->page;
        } else {
            $page = 1;
        }

        $offset = ($page - 1) * $rl;

        $this->db->limit($rl, $offset);
    }

    /**
     * Limit SQL
     *
     * Alternative function to limit() which does not make use of the
     * active record class, but instead returns the sql.
     *
     * @return string
     */
    public function limit_sql() {
        //Get row limit
        if (!empty($this->session->{$this->pageName . 'RowLimit'})) {
            $rl = $this->session->{$this->pageName . 'RowLimit'};
        } else if (!empty($this->rowLimit)) {
            $rl = $this->rowLimit;
        } else {
            $rl = $this->config->item('CRUD_row_limit');
        }

        //Fix for some weird php bug where the empty() function sees this as empty even though it's not.
        $this->page = $this->page;

        //Get page number (default 1) and work out offset
        if (!empty($this->page)) {
            $page = $this->page;
        } else {
            $page = 1;
        }

        $offset = ($page - 1) * $rl;

        $sql = 'LIMIT ' . $offset . ', ' . $rl;

        return $sql;
    }

    public function main_sorting() {
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

    public function sorting_sql() {
        $sortClauses = array();
        if (!empty($this->session->{$this->pageName . 'Sorting'})) {
            $sorting = $this->session->{$this->pageName . 'Sorting'};
            foreach ($sorting as $field => $dir) {
                array_push($sortClauses, $field . ' ' . $dir);
            }
        } else {
            foreach ($this->sorting as $field => $dir) {
                array_push($sortClauses, $field . ' ' . $dir);
            }
        }

        //Build SQL
        $sql = '';
        if (!empty($sortClauses)) {
            $sql = 'ORDER BY ' . implode(', ', $sortClauses);
        }

        return $sql;
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

        //Get id to return
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

    /**
     * Get simple list data
     *
     * Returns a simple list of data ordered by name in ascending order.
     *
     * @param string $table
     * @param string $field
     * @param mixed $value
     *
     * @return object
     */
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

    public function get_by_id($id, $table = false) {
        $table = $table ? $table : $this->table;
        $this->db->where($table . '.removed', 0);
        $this->db->where($table . '.id', $id);

        $query = $this->db->get($table);

        if ($query->num_rows() > 0) {
            $row = $query->row();

            return $row;
        }
        else {
            return false;
        }
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
        // if (is_array($fields)) {
        // 	foreach ($fields as $field) {
        // 		$this->db->group_start();
        // 		foreach ($value as $val) {
        // 			$this->db->like($field, $val);
        // 		}
        // 		$this->db->group_end();
        // 	}
        // }
        // else {
        // 	foreach ($value as $val) {
        // 		$this->db->or_like($fields, $val);
        // 	}
        //}
        $this->db->group_end();
        $this->db->limit(100);

        $query = $this->db->get($this->table); 
        return $query;
    }

    /**
     * Check Unique Slug
     *
     * Checks if the given slug already exists in the database.
     * If so then append a number and then recall function to confirm if unique.
     *
     * @param string $slug
     * @param string $table
     * @param int $id
     *
     * @return string
     */
    public function check_unique_slug($slug, $table = null, $id = 0) {
        $table = (!empty($table)) ? $table : $this->table;

        $this->db->where('slug', $slug);

        if ($id) {
            $this->db->where('id !=', $id);
        }

        $this->db->limit(1);
        $query = $this->db->get($table);

        if ($query->num_rows() > 0) {
            //Slug is not unique.
            if (preg_match('/\d+$/', $slug)) {
                $slug = preg_replace_callback('/(\d+$)/', function($matches) use ($slug) {
                    $str = preg_replace('/\d+$/', '', $slug);

                    return ($matches[0] + 1);
                }, $slug);
            } else {
                $slug .= '2';
            }

            //Call function again to recheck the slug
            $slug = $this->check_unique_slug($slug, $table);
        }

        //At this point the slug is unique with the given table
        return $slug;
    }

    /**
     * Get Assignment Data
     *
     * Returns the data for an assignment grid
     *
     * @param string $group
     * @param int $parentID
     * @param int $filters
     *
     * @return object
     */
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

            //Loop through
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

    /**
     * Save Assignment Data
     *
     * Saves the association between the parent and child table
     *
     * @param $group
     * @param $parentID
     * @param $childID
     * @param $childValue
     */
    public function save_assignment_data($group, $parentID, $childID, $childValue) {
        $groupOptions = $this->assignmentGroup[$group];

        if ($childValue) {
            //Insert association
            $this->db->set($groupOptions['parent_id'], $parentID);
            $this->db->set($groupOptions['child_id'], $childID);
            $result = $this->db->insert($groupOptions['pivot_table']);

            //Log assignment
            if ($result) {
                Logger::log(ucwords($groupOptions['child']) . ' has been assigned to ' . $groupOptions['parent'], array(
                    'group' => $group,
                    'parentID' => $parentID,
                    'childID' => $childID,
                    'childValue' => $childValue
                ));
            }
        } else {
            //Delete association
            $this->db->where($groupOptions['parent_id'], $parentID);
            $this->db->where($groupOptions['child_id'], $childID);
            $this->db->limit(1);
            $result = $this->db->delete($groupOptions['pivot_table']);

            //Log unassignment
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

    /**
     * Save Dynamic Fields
     *
     * Saves the data for the given set of dynamic fields.
     *
     * @param string $name
     * @param string $table
     * @param string $parentField
     * @param int $parentID
     * @param array $extraInsert
     * @param array $extraUpdate
     * @param boolean $forceInsert
     *
     * @return array
     */
    public function save_dynamic_fields($name, $table, $parentField, $parentID, $extraInsert = array(), $extraUpdate = array(), $forceInsert = false) {
        $this->dfNoXSS = $this->dfNoXSS;
        $this->dfSluggify = $this->dfSluggify;
        $xss = (!empty($this->dfNoXSS) && in_array($name, $this->dfNoXSS)) ? FALSE : TRUE;
        $sluggifyField = (!empty($this->dfSluggify) && array_key_exists($name, $this->dfSluggify)) ? $this->dfSluggify[$name] : FALSE;

        $items = $this->input->post('df[' . $name . ']', $xss);

	    //Clear up array of empty values
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

        //Get ids of items that already exists in the db
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

            //Set posted fields to be saved
            foreach ($fields as $field => $value) {

                //Optionally modify value
                $value = $this->df_modify_value($name, $field, $value);

                //Ignore arrays (like for multi-selects)
                if (!is_array($value)) {
                    $this->db->set($field, $value);
                } elseif ($field == 'uploaders') {
                    $dzUploaders = $value;
                }
            }

            //If $id contains new then it's a new item which should be added
            //ForceInsert will bypass this condition and insert it anyway
            if ($forceInsert || preg_match('/new\_\d+/', $id)) {
                //Set extra data to be saved with each insert
                foreach ($extraInsert as $field => $value) {
                    $this->db->set($field, $value);
                }

                //Check if slug needs to be set
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
                //Set extra data to be saved with each update
                foreach ($extraUpdate as $field => $value) {
                    $this->db->set($field, $value);
                }

                $this->db->set('updated_at', date('Y-m-d H:i:s'));
                $this->db->where($parentField, $parentID);
                $this->db->where('id', $id);
                $this->db->update($table);

                //Item still existed after update, so remove from delete array
                if (isset($deleteArray[$id])) {
                    unset($deleteArray[$id]);
                }

                $rowID = $id;
            }

            $returnItems['fields'][$rowID] = $fields;
            $returnItems['uploaders'][$rowID] = $dzUploaders;
        }

        //Check if any items remain in the delete array and delete them.
        if (!empty($deleteArray)) {
            $this->db->set('removed', 1);
            $this->db->set('deleted_at', date('Y-m-d H:i:s'));
            $this->db->where($parentField, $parentID);
            $this->db->where_in('id', $deleteArray);
            $this->db->update($table);

            //TODO: Remove files for this dynamic rowID
            $returnItems['deleted'] = $deleteArray;
        }

        return $returnItems;
    }

    public function df_modify_value($name, $field, $value) {
        return $value;
    }

    /**
     * Get Dynamic Field Data
     *
     * Returns the saved dynamic field data to be used on an edit form
     *
     * @param string $name
     * @param string $table
     * @param string $parentField
     * @param int $parentID
     * @param array $fields
     * @param array $df
     *
     * @return array
     */
    public function get_dynamic_field_data($name, $table, $parentField, $parentID, $fields, $df=array(), $formatters=array()) {
        //Get items from given child table
        $this->db->where($parentField, $parentID);
        $this->db->where('removed', 0);
        $this->db->order_by('position', 'asc');
        $query = $this->db->get($table);

        //Loop through items add add to an array to be used by the dynamic fields function
        $df[$name] = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                foreach ($fields as $field) {
                    //Check if the field needs to be formatted
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

    /**
     * Search Cities
     *
     * Used by primarily by the city field autocomplete control.
     * Searches the db for cities matching the given string.
     * If countyID is not 0 then it will filter search by given country.
     *
     * @param string $search
     * @param int $countryID
     * @param string $searchType
     *
     * @return Object
     */
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

		//filter by country if given
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
			//Not unique
			return false;
		}
		else {
			//Is unique
			return true;
		}
    }
    
    /**
	 * Is Unique
	 *
	 * Checks if given entry already exists in the database.
	 * If the id is passed, then it will ignore that entry.
	 *
	 * @param string $str
	 * @param string $id (optional)
	 * @param string $field (optional)
	 * @param array $extra (optional)
	 *
	 * @return bool
	 */
	public function is_unique($str, $id = "", $field="", $extra=array()) {

		$this->db->select('id');
		$this->db->where($field, $str);
        $this->db->where('removed', 0);
        
        //Add extra conditions if given
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

        //Get current position
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

        //Get current position
        $this->db->select('position');
        $this->db->where('id', $id);
        $this->db->where('removed', 0);
        !empty($filters) && $this->db->where($filters);
        $query = $this->db->get($this->table);
        $position = $query->row()->position;

        if ($dir == 'up' && $position != 1) {
            //change position of the entry that going to be moved down when this entry moves up
            $this->db->set('position', $position);
            $this->db->where('position', $position - 1);
            $this->db->where('removed', 0);
            !empty($filters) && $this->db->where($filters);
            $this->db->update($this->table);

            //Change the entry to the new position
            $this->db->set('position', $position - 1);
            $this->db->where('id', $id);
            $this->db->where('removed', 0);
            !empty($filters) && $this->db->where($filters);
            $this->db->update($this->table);
        }
        elseif ($dir == 'down') {
            //Get last position
            $this->db->select('MAX(position) AS last_position');
            $this->db->where('removed', 0);
            !empty($filters) && $this->db->where($filters);
            $query = $this->db->get($this->table);
            $lastPosition = $query->row()->last_position;

            //Only move down if it's not the last entry
            if ($position != $lastPosition) {
                //change position of the entry that going to be moved down when this entry moves up
                $this->db->set('position', $position);
                $this->db->where('position', $position + 1);
                $this->db->where('removed', 0);
                !empty($filters) && $this->db->where($filters);
                $this->db->update($this->table);

                //Change the entry to the new position
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

    // Resets back to order by id
    public function reset_positioning($filters=array()) {
        $this->db->query('SET @i:=0;');
        $this->db->set('position', '@i:=(@i+1)', false);
        $this->db->where('removed', 0);
        !empty($filters) && $this->db->where($filters);
        $this->db->update($this->table);
    }

    // Resets positions without loosing previous positioning data
    public function positioning_close_gaps($filters = array()) {
        // Get all items ordered by their current position
        $this->db->select('id');
        $this->db->where('removed', 0);
        !empty($filters) && $this->db->where($filters);
        $this->db->order_by('position', 'ASC');
        $items = $this->db->get($this->table)->result_array();

        // Update positions in the correct order
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

    /**
	 * Is Unique Login Email
	 *
	 * Checks if given email already exists in the database.
	 * If the id is passed, then it will ignore that entry.
	 *
	 * @param string $email
	 * @param string $loginID (optional)
	 *
	 * @return bool
	 */
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

    /**
     * Sluggifies a string
     *
     * @param  string $str
     *
     * @return string
     */
    function sluggify($str, $table = null) {
        if (!empty($str)) {

            //Make sure slug isn't greater than 250 characters
            $str = substr($str, 0, 250);

            //Format initial string
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
}