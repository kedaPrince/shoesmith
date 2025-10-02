<?php

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
        'group'         => 'Candidates',
        'label'         => "Candidates",
        'icon'          => 'fa-folder-open',
        'url'           => url('candidates'),
        'class'         => '',
        'show'          => true,
        'show_heading'  => false,
        'active_by' 	=> 'view'
    ),
    
);