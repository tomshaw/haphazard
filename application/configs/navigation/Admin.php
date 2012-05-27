<?php
return array(
    array(
    	'label'      => 'Administration',
    	'title'      => 'Administration',
        'module'     => 'admin',
        'controller' => 'index',
    	'action'     => 'index',
    	'resource'   => 'admin',
    	'pages'      => array(
    		array(
    			'label'      => 'Administration general',
    			'title'      => 'Administration general',
    			'module'     => 'admin',
    			'controller' => 'index',
    			'action'     => 'index',
    			'resource'   => 'admin',
    			'class'		 => 'nav-header'
    		)
    	)
	),
	array(
		'label'      => 'User listing',
		'title'      => 'User listing',
		'module'     => 'admin',
		'controller' => 'users',
		'action'     => 'index',
		'resource'   => 'admin',
		'pages'		 => array(
			array(
				'label'      => 'Create user',
				'title'      => 'Create user',
				'module'     => 'admin',
				'controller' => 'users',
				'action'     => 'create',
				'resource'   => 'admin',
				'id'		 => 'create'
			)				
		)
	),
    array(
    	'label'      => 'Blog entries',
    	'title'      => 'Blog entries',
    	'module'     => 'admin',
    	'controller' => 'entries',
    	'action'     => 'index',
    	'resource'   => 'admin',
    	'class'		 => 'nav-header'
    )   
);