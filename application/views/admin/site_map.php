<?php
$this->siteMap = array(
    (object) [
        'group'         => 'Dashboard',
        'page'          => 'dashboard',
        'label'         => 'Dashboard',
        'icon'          => 'fa-dashboard',
        'url'           => url('dashboard'),
        'class'         => '',
        'show'          => true,
    ],
     (object)array(
        'group'         => 'Recruiters',
        'page'          => 'recruiters',
        'label'         => lang('recruiters_heading'),
        'icon'          => 'fa-universal-access',
        'url'           => url('recruiters'),
        'class'         => 'Recruiters',
        'show'          => true,
        'show_heading'  => false,
        'active_by'     => 'view',
    ),
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
      (object)array(
        'group'         => 'Agency Management',
        'page'          => 'agencies',
        'label'         => lang('agencies_heading'),
        'icon'          => 'fa-universal-access',
        'url'           => url('jobs'),
        'class'         => 'Jobs',
        'show'          => true,
        'show_heading'  => false,
        'active_by'     => 'view',
          'items' => array(
            (object)array(
                'page'          => 'agencies',
                'view'          => 'listing',
                'label'         => lang('agencies_heading'),
                'icon'          => 'fa-folder-open',
                'url'           => url('agencies'),
                'show'          => true,
            ),
            (object)array(
                'page'          => 'agency_jobs_listings',
                'view'          => 'listing',
                'label'         => lang('agency_jobs_listings_heading'),
                'icon'          => 'fa-folder-open',
                'url'           => url('agency_jobs_listings'),
                'show'          => true,
            ),
        ),
    ),
   

    (object)array(
        'group'         => 'Users',
        'page'          => 'administrators',
        'label'         => lang('users_heading'),
        'icon'          => 'fa-users',
        'url'           => url('administrators'),
        'class'         => '',
        'show'          => true,
        'show_heading'  => false,
        'active_by'     => 'view',
        'items' => array(
            (object)array(
                'page'          => 'admin', 'super_admin', 'general_admin',
                'view'          => 'listing',
                'label'         => lang('administrators_heading'),
                'icon'          => 'fa-desktop',
                'url'           => url('administrators'),
                'show'          => true,
            ),
            (object)array(
                'page'          => 'staff',
                'view'          => 'listing',
                'label'         => lang('staff_heading'),
                'icon'          => 'fa-users',
                'url'           => url('staff'),
                'show'          => true,
            ),
        )
    ),
     (object)array(
        
            'group' => 'Test Form Builder',
            'page' => 'test_form_builder',
            'label' => 'Test Form Builder',
            'icon' => 'fa-edit',
            'url' => url('test_form_builder'),
            'class' => '',
            'show' => true,
            'active_by' => 'view',

     ),
    
   
);