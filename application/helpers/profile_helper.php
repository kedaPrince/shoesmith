<?php
function getLoggedInAccessGroups(): array
{
    $ci = &get_instance();
    $login = $ci->session->get_userdata();
    if (empty($login) || !isset($login['is_logged_in']) || $login['is_logged_in'] < 0) {
        return [];
    }

    // FIXED: Include agency in the login data check
    if (isset($login['login']['admin'])) {
        $loginData = $login['login']['admin'];
        $userGroup = 'admin';
    } elseif (isset($login['login']['staff'])) {
        $loginData = $login['login']['staff'];
        $userGroup = 'staff';
    } elseif (isset($login['login']['agency'])) {
        $loginData = $login['login']['agency'];
        $userGroup = 'agency';
    } else {
        return [];
    }

    // Get the access groups
    $pivotTable = 'pivot_' . $userGroup . '_access_groups';
    $tableUsers = 'usr_' . ($userGroup === 'admin' ? 'admins' : ($userGroup === 'staff' ? 'staff' : 'agency_staff'));
    
    $ci->db->distinct();
    $ci->db->select('mod_access_groups.id, mod_access_groups.name');
    $ci->db->join($pivotTable, $pivotTable . '.' . $userGroup . '_id = mod_access_groups.id', 'inner');
    $ci->db->join($tableUsers, $tableUsers . '.id = ' . $pivotTable . '.' . $userGroup . '_id AND ' . $tableUsers . '.id = ' . $loginData['id'], 'inner');
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

    // FIXED: Include agency in the login data check
    if (isset($login['login']['admin'])) {
        $loginData = $login['login']['admin'];
        $userGroup = 'admin';
        $tableUsers = 'usr_admins';
    } elseif (isset($login['login']['staff'])) {
        $loginData = $login['login']['staff'];
        $userGroup = 'staff';
        $tableUsers = 'usr_staff';
    } elseif (isset($login['login']['agency'])) {
        $loginData = $login['login']['agency'];
        $userGroup = 'agency';
        $tableUsers = 'agency_staff';
    } else {
        return '';
    }

    // Get the user type
    $ci->db->select('usr_types.title');
    $ci->db->join($tableUsers, $tableUsers . '.usr_type_id = usr_types.id AND ' . $tableUsers . '.id = ' . $loginData['id'], 'inner');
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
    
    // FIXED: Check for agency first, then admin, then staff
    if (isset($login['login']['agency'])) {
        return 'agency';
    } elseif (isset($login['login']['admin'])) {
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
    } elseif (isset($login['login']['staff'])) {
        return 'staff';
    }
    
    return '';
}

function getEditAllowedStatus(): bool
{
    $loggedInUserType = getLoggedInUserType();
    // FIXED: Include agency admin roles if needed
    if (in_array($loggedInUserType, ['Super Admin', 'General Admin', 'Agency Admin'])) {
        return true;
    }
    return false;
}

/**
 * Get user access groups based on user type and ID
 * 
 * @param int $id User ID
 * @param string $type User type ('admin', 'staff', or 'agency')
 * @return array Array of access group objects
 * @throws InvalidArgumentException
 */
function getUserAccessGroups($id, $type): array
{
    // Validate input parameters - FIXED: Include 'agency'
    if (!is_numeric($id) || $id <= 0) {
        throw new InvalidArgumentException("Invalid ID: {$id}");
    }
    
    if (!in_array($type, ['admin', 'staff', 'agency'], true)) {
        throw new InvalidArgumentException("Invalid type: {$type}");
    }

    $ci = &get_instance();
    
    // Define table config based on type - FIXED: Include agency
    $pivotTable = 'pivot_' . $type . '_access_groups';
    $idColumn = $type . '_id';

    // For agency, we need to handle the table name differently
    $tableUsers = $type === 'agency' ? 'agency_staff' : 'usr_' . ($type === 'admin' ? 'admins' : 'staff');

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