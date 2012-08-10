<?php

// File: UserRegistration.php
// This file registers the web requests supported by this controller.  This file defines and describes the controller's API.
// Any request parameter passed into a request, must be defined here.  If not provided, the defined default value is assigned but every parameter defined is
// guaranteed to be passed into the called request.  The values of each request parameter are also sanitized with the addslashes() function.
// The following request parameters are allowed through automatically, if provided but otherwise not set:
//   warnings  -- any warning messages to pass on to this request
//   messages  -- any regular messages to pass on to this request
//   return_to -- a page to configure to return to, after this request is handled
// After acquisition and sanitization, the request is called, passing the following parameters to it:
//   - an associative array of request parameters and their values
//   - a boolean value of whether or not all required parameters were provided
//   - a textual warning message for each missed required parameter, if any, otherwise '' (so controller can choose to show to user or not)

$requests = array(
	'description' => 'The "user" controller provides for basic user management.  On initialization, a super user must be created.  A super user is prohibited from disabling or removing super status of himself to ensure there is always at least one super user who we know can login.  The database user/password provided must have privileges to create/drop tables in the environment.',
	'requests'    => array( 
		'initialize' => array(
			'description' => 'This initializes the user tables within the database (creates and/or drops and recreates).',
			'parameters' => array( 
				array( 'name' => 'super_user',        'required' => false, 'default' => 'master', 'description' => "Initial super user's login name." ),
				array( 'name' => 'super_password',    'required' => true,  'default' => null,     'description' => "Initial super user's password." ),
				array( 'name' => 'super_surname',     'required' => false, 'default' => 'Master', 'description' => "Initial super user's given name." ),
				array( 'name' => 'super_forename',    'required' => false, 'default' => 'User',   'description' => "Initial super user's family name." ),
				array( 'name' => 'super_email',       'required' => true,  'default' => null,     'description' => "Initial super user's email." ),
				array( 'name' => 'database_user',     'required' => false, 'default' => 'root',   'description' => "Database user sufficiently privileged to drop and create tables." ),
				array( 'name' => 'database_password', 'required' => false, 'default' => null,     'description' => "Privileged Database user's password" )
			)
		),
		'add' => array(
			'description' => 'Adds a new user into the system.',
			'parameters' => array( 
				array( 'name' => 'user_name', 'required' => true, 'default' => null,   'description' => "User's login name." ),
				array( 'name' => 'password',  'required' => true, 'default' => null,   'description' => "User's login password." ),
				array( 'name' => 'active',    'required' => false, 'default' => true,  'description' => "Is user account active?" ),
				array( 'name' => 'super',     'required' => false, 'default' => false, 'description' => "Is user a super user?" )
			)
		),
		'login'    => array(
			'description' => 'The "login" request is for signing the user in (identification/authentication).',
			'parameters' => array( 
				array( 'name' => 'user_name', 'required' => true, 'default' => null,    'description' => "User's login name." ),
				array( 'name' => 'password',  'required' => true, 'default' => null,    'description' => "User's login password." )
			)
		),
		'password'    => array(
			'description' => 'The "password" request is for changing the user\'s password.',
			'parameters' => array( 
				array( 'name' => 'old_password', 'required' => true, 'default' => null,    'description' => "User's pre-existing password." ),
				array( 'name' => 'new_password', 'required' => true, 'default' => null,    'description' => "User's chosen new password." )
			)
		),
		'profile'  => array(
			'description' => 'The "profile" request is for setting and getting user attributes. Possible actions are "show" and "update".',
			'parameters' => array( 
				array( 'name' => 'user_name', 'required' => false, 'default' => null,   'description' => "Name used to login with and as a casual reference to the user." ),
				array( 'name' => 'password',  'required' => false, 'default' => null,   'description' => "User's login password." ),
				array( 'name' => 'email',     'required' => false, 'default' => null,   'description' => "Email address for contacting the user." ),
				array( 'name' => 'surname',   'required' => false, 'default' => null,   'description' => "User's family name." ),
				array( 'name' => 'forename',  'required' => false, 'default' => null,   'description' => "User's given (casually called) name." ),
				array( 'name' => 'active',    'required' => false, 'default' => null,   'description' => "Is user account active? yes/no" ),
				array( 'name' => 'super',     'required' => false, 'default' => null,   'description' => "Is user a super user? yes/no" ),
				array( 'name' => 'action',    'required' => false, 'default' => 'show', 'description' => "Is this just to show, to update, or to register the user?" )
			)
		),
		'registration'  => array(
			'description' => 'The "registration" request is for registering a new user.',
			'parameters' => array( 
				array( 'name' => 'user_name', 'required' => false, 'default' => null,   'description' => "Name used to login with and as a casual reference to the user." ),
				array( 'name' => 'email',     'required' => false, 'default' => null,   'description' => "Email address for contacting the user." ),
				array( 'name' => 'surname',   'required' => false, 'default' => null,   'description' => "User's family name." ),
				array( 'name' => 'forename',  'required' => false, 'default' => null,   'description' => "User's given (casually called) name." ),
				array( 'name' => 'password',  'required' => false, 'default' => null,   'description' => "User's login password." ),
			)
		),
	)
);


/*
Rob: 972-249-4951
*/

$tables = array (
	'users' => array ( 
		'id'        => array ( 'type' => 'INT(11)',     'key' => 'primary' ),
		'user_name' => array ( 'type' => 'VARCHAR(15)', 'default' => 'null', 'filter' => null ),
		'password'  => array ( 'type' => 'VARCHAR(32)', 'default' => 'null', 'filter' => null ),
		'surname'   => array ( 'type' => 'VARCHAR(15)', 'default' => 'null', 'filter' => null ),
		'forename'  => array ( 'type' => 'VARCHAR(15)', 'default' => 'null', 'filter' => null ),
		'email'     => array ( 'type' => 'VARCHAR(60)', 'default' => 'null', 'filter' => null ),
		'super'     => array ( 'type' => 'BOOLEAN',     'default' => 0,      'filter' => null ),
		'active'    => array ( 'type' => 'BOOLEAN',     'default' => 1,      'filter' => null ),
		),
	'user_attributes' => array (
		'id'        => array ( 'type' => 'INT(11)',     'key' => 'primary' ),
		'user_id'   => array ( 'type' => 'INT(11)',     'default' => 'NULL' ),
		'attribute' => array ( 'type' => 'VARCHAR(15)', 'default' => 'NULL', 'filter' => null ),
		'value'     => array ( 'type' => 'TEXT',        'default' => 'NULL', 'filter' => null ),
	), 
);

