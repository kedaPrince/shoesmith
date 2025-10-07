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
        'group'         => 'Jobs',
        'page'          => 'jobs',
        'label'         => lang('jobs_heading'),
        'icon'          => 'fa-universal-access',
        'url'           => url('jobs'),
        'class'         => 'Jobs',
        'show'          => true,
        'show_heading'  => false,
        'active_by'     => 'view',
    ),
 

    (object) [
        'group'         => 'Candidates Management',
        'page'          => 'candidates',
        'label'         => lang('candidates_heading'),
        'icon'          => 'fa-users',
        'url'           => url('candidates'),
        'class'         => '',
        'show'          => true,
        'show_heading'  => true,
        'active_by'     => 'candidates',
        'items' => array(
            (object) [
                'page'          => 'candidates',
                'view'          => 'listing',
                'label'         => lang('candidates_heading'),
                'icon'          => 'fa-user-circle',
                'url'           => url('candidates'),
                'show'          => true,
            ],
            (object) [
                'page'          => 'candidates_resume_listings',
                'view'          => 'listing',
                'label'         => lang('candidates_resume_listings_heading'),
                'icon'          => 'fa-file-text',
                'url'           => url('candidates_resume_listings'),
                'show'          => true,
            ],
        ),
    ],
);