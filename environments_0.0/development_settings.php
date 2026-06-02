<?php

$settings = array(
	'landing_page' => 'wiki',
	// Absolute path keeps logging working regardless of the request's working directory.
	'log_file'     => dirname( __DIR__ ) . '/logs/development.log'
);

$databases = array(
	array(
		"usage"    => "read_write",
		"driver"   => "pgsql",
		"address"  => "localhost",
		"port"     => "5432",
		"name"     => "apskel_development",
		"prefix"   => "",
		"user"     => "apskel",
		"password" => "apskel_dev"
	)
);

$rewrites = array(
);
