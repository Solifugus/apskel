<?php

$settings = array(
	'landing_page' => 'wiki',
	// Absolute path keeps logging working regardless of the request's working directory.
	'log_file'     => dirname( __DIR__ ) . '/logs/development.log'
);

$databases = array(
	array(
		"usage"    => "read_write",
		"address"  => "localhost",
		"port"     => "3306",
		"name"     => "development",
		"prefix"   => "",
		"user"     => "master",
		"password" => "fakeword"	
	)
);

$rewrites = array(
);
