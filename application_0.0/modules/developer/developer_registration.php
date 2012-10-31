<?
# File: agent_registration.php
# 2012-09-12 ... Created.

$requests = array(
	'description' => 'A module to build new (or new version of old) modules.',
	'default' => 'define',
	'requests' => array(
		'define' => array(
			'description' => 'Page on which to define the module.',
			'parameters' => array(
				array( 'name' => 'version',     'required' => true , 'default' => '', 'description' => 'Application version to build the module under.' ),
				array( 'name' => 'name',        'required' => true , 'default' => '', 'description' => 'Name of the module.' ),
			)
		),
		'build' => array(
			'description' => 'Build the module as specified.',
			'parameters' => array(
				array( 'name' => 'version',     'required' => true , 'default' => '', 'description' => 'Application version to build the module under.' ),
				array( 'name' => 'name',        'required' => true , 'default' => '', 'description' => 'Name of the module.' ),
				array( 'name' => 'description', 'required' => true , 'default' => '', 'description' => 'Description of the module.' ),
				array( 'name' => 'requests',    'required' => true , 'default' => '', 'description' => 'JSON data structure of requests and their parameters.' ),
				array( 'name' => 'tables',      'required' => true , 'default' => '', 'description' => 'JSON data structure of tables and their columns.' ),
			)
		),
	)
);
