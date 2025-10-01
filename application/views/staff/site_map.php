<?php
$playbooks_menu_array = get_user_playbooks();

$this->siteMap = array(
    (object) [
        'group'         => 'Dashboard',
        'label'         => 'Dashboard',
        'icon'          => 'fa-dashboard',
        'url'           => url('dashboard'),
        'class'         => '',
        'show'          => true,
        'active_by'     => 'view',
    ],
    (object) array(
        'group'         => 'Playbooks',
        'label'         => "Playbooks",
        'icon'          => 'fa-folder-open',
        'url'           => url('playbooks'),
        'class'         => '',
        'show'          => true,
        'show_heading'  => false,
        'active_by' 	=> 'view',
        'items'			=> $playbooks_menu_array
    ),
    
);