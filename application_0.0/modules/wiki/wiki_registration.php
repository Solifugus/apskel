<?php
# File: wiki_registration.php
# 2018-04-30 ... Created.

$requests = array(
	'description' => 'Wiki-based informational pages.',
	'default' => '',
	'requests' => array(
		'' => array(
			'description' => 'Retrieve default "main" wiki page.',
			'parameters' => array()
		),
		'get' => array(
			'description' => 'Get wiki page.',
			'parameters' => array(
				array( 'name' => 'name', 'required' => true , 'default' => 'main', 'description' => 'Name of wiki page.' ),
				array( 'name' => 'asof', 'required' => false , 'default' => '', 'description' => 'Date/Time of status of page to retrieve from.' ),
			)
		),
		'put' => array(
			'description' => 'Store new copy of wik page.',
			'parameters' => array(
				array( 'name' => 'text', 'required' => true , 'default' => null, 'description' => 'Wiki text of the page.' ),
				array( 'name' => 'production', 'required' => true , 'default' => 1, 'description' => 'Is this page production or draft version?' ),
				array( 'name' => 'from', 'required' => false , 'default' => '', 'description' => 'Show version of page from this date/time.' ),
				array( 'name' => 'thru', 'required' => false , 'default' => '', 'description' => 'Show version of page through this date/time.' ),
			)
		),
		'directory' => array(
			'description' => '',
			'parameters' => array(
				array( 'name' => 'asof', 'required' => false , 'default' => '', 'description' => 'Show directory of pages snapshot asof this date/time.' ),
			)
		),
	)
);

$tables = array(
	'pages' => array(
		'id' => array( 'type' => 'INTEGER', 'default' => '0', 'key' => 'primary', 'filter' => '//' ),
		'page' => array( 'type' => 'TEXT', 'default' => '', 'key' => '', 'filter' => '//' ),
		'asof' => array( 'type' => 'DATETIME', 'default' => '', 'key' => '', 'filter' => '//' ),
		'from' => array( 'type' => 'DATETIME', 'default' => '', 'key' => '', 'filter' => '//' ),
		'thru' => array( 'type' => 'DATETIME', 'default' => '', 'key' => '', 'filter' => '//' ),
		'user_id' => array( 'type' => 'INTEGER', 'default' => 'null', 'key' => '', 'filter' => '//' ),
	),
);
