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
     (object)array(
        'group'         => 'Candidates Profile',
        'page'          => 'candidates',
        'label'         => lang('candidates_heading'),
        'icon'          => 'fa-universal-access',
        'url'           => url('jobs'),
        'class'         => 'Jobs',
        'show'          => true,
        'show_heading'  => false,
        'active_by'     => 'view',
          'items' => array(
            (object)array(
                'page'          => 'candidates',
                'view'          => 'listing',
                'label'         => lang('candidates_heading'),
                'icon'          => 'fa-folder-open',
                'url'           => url('candidates'),
                'show'          => true,
            ),
            (object)array(
                'page'          => 'candidates_resume_listings',
                'view'          => 'listing',
                'label'         => lang('candidates_resume_listings_heading'),
                'icon'          => 'fa-folder-open',
                'url'           => url('candidates_resume_listings'),
                'show'          => true,
            ),
        ),
    ),
    
);