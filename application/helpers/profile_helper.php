<?php
function getLoggedInAccessGroups(): array
{
    $ci = &get_instance();
    $login = $ci->session->get_userdata();
    if (empty($login) || !isset($login['is_logged_in']) || $login['is_logged_in'] < 0) {
        return [];
    }

    $login = isset($login['login']['admin']) ? $login['login']['admin'] : $login['login']['staff'];

    // Get the access groups
    $pivotTable = 'pivot_' . $login['group'] . '_access_groups';
    $tableUsers = 'usr_' . ($login['group'] === 'admin' ? 'admins' : 'staff');
    $ci->db->distinct();
    $ci->db->select('mod_access_groups.id, mod_access_groups.name');
    $ci->db->join($pivotTable, $pivotTable . '.' . $login['group'] . '_id = mod_access_groups.id', 'inner');
    $ci->db->join($tableUsers, $tableUsers . '.id = ' . $pivotTable . '.' . $login['group'] . '_id AND ' . $tableUsers . '.id = ' . $login['id'], 'inner');
    $ci->db->order_by('mod_access_groups.id', 'asc');
    $results = $ci->db->get('mod_access_groups')->result();

    $list = [];
    foreach ($results as $row) {
        $list[(int)$row->id] = $row->name;
    }

    return $list;
}

function getLoggedInAccessGroupIds(array $userAccessGroups): array
{
    $accessGroupIds = [];
    foreach ($userAccessGroups as $index => $row) {
        $accessGroupIds[] = $index;
    }
    return $accessGroupIds;
}

function getLoggedInUserType(): string
{
    $ci = &get_instance();
    $login = $ci->session->get_userdata();
    if (empty($login) || !isset($login['is_logged_in']) || $login['is_logged_in'] < 0) {
        return '';
    }

    $login = isset($login['login']['admin']) ? $login['login']['admin'] : $login['login']['staff'];

    // Get the user type
    $tableUsers = 'usr_' . ($login['group'] === 'admin' ? 'admins' : 'staff');
    $ci->db->select('usr_types.title');
    $ci->db->join($tableUsers, $tableUsers . '.usr_type_id = usr_types.id AND ' . $tableUsers . '.id = ' . $login['id'], 'inner');
    $ci->db->where('usr_types.enabled', 1);
    $ci->db->where('usr_types.removed', 0);
    $result = $ci->db->get('usr_types')->row();
    if (empty($result)) {
        return '';
    }
    return $result->title;
}

function getLoggedInUserTypeMenu(): string
{
    $ci = &get_instance();
    $login = $ci->session->get_userdata();
    if (!isset($login['login']['admin']['id'])) {
        return 'staff';
    }
    $ci->db->select('usr_type_id');
    $ci->db->where('id', $login['login']['admin']['id']);
    $result = $ci->db->get('usr_admins')->row();
    if (empty($result)) {
        return 'staff';
    }
    if (!in_array((int)$result->usr_type_id, [1, 2])) {
        return 'staff';
    }

    return 'admin';
}

function getEditAllowedStatus(): bool
{
    $loggedInUserType = getLoggedInUserType();
    if (in_array($loggedInUserType, ['Super Admin', 'General Admin'])) {
        return true;
    }
    return false;
}

/**
 * Get user access groups based on user type and ID
 * 
 * @param int $id User ID
 * @param string $type User type ('admin' or 'staff')
 * @return array Array of access group objects
 * @throws InvalidArgumentException
 */
function getUserAccessGroups($id, $type): array
{
    // Validate input parameters
    if (!is_numeric($id) || $id <= 0) {
        throw new InvalidArgumentException("Invalid ID: {$id}");
    }
    
    if (!in_array($type, ['admin', 'staff'], true)) {
        throw new InvalidArgumentException("Invalid type: {$type}");
    }

    $ci = &get_instance();
    
    // Define table config based on type
    $pivotTable = $type === 'admin' ? 'pivot_admin_access_groups' : 'pivot_staff_access_groups';
    $idColumn = $type . '_id';

    // Sanitize table/column names
    $pivotTable = $ci->db->protect_identifiers($pivotTable);
    $idColumn = $ci->db->protect_identifiers($idColumn);
    
    try {
        $ci->db->select("mag.id, mag.name, mag.slug")
               ->from($pivotTable)
               ->join('mod_access_groups mag', "mag.id = {$pivotTable}.access_group_id", 'left')
               ->where("{$pivotTable}.{$idColumn}", $id);
               
        return $ci->db->get()->result();
    } catch (Exception $e) {
        // Log the error and return empty array or re-throw based on your needs
        log_message('error', 'Error fetching access groups: ' . $e->getMessage());
        return [];
    }
}