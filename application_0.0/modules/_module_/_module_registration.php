<?php

// File: UserRegistration.php
// This file registers the web requests supported by this controller.  This file defines and describes the controller's API.
// Any request parameter passed into a request, must be defined here.  If not provided, the defined default value is assigned but every parameter defined is
// guaranteed to be passed into the called request.  The values of each request parameter are also sanitized with the addslashes() function.
// The following request parameters are allowed through automatically, if provided but otherwise not set (except for "fresh"):
//   fresh     -- if not provided, automatically exists set to true, any other value should presume to be false.
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
				array( 'name' => 'super_forename',    'required' => false, 'default' => 'User',   'description' => "Initial super user's family name." ),
				array( 'name' => 'super_surname',     'required' => false, 'default' => 'Master', 'description' => "Initial super user's given name." ),
				array( 'name' => 'super_email',       'required' => true,  'default' => null,     'description' => "Initial super user's email." ),
				array( 'name' => 'database_user',     'required' => false, 'default' => 'root',   'description' => "Database user sufficiently privileged to drop and create tables." ),
				array( 'name' => 'database_password', 'required' => false, 'default' => null,     'description' => "Privileged Database user's password" )
			)
		),
		'register'  => array(
			'description' => 'The "register" request is for registering a new user.',
			'parameters' => array( 
				array( 'name' => 'user_name', 'required' => false, 'default' => null,   'description' => "Name used to login with and as a casual reference to the user." ),
				array( 'name' => 'email',     'required' => false, 'default' => null,   'description' => "Email address for contacting the user." ),
				array( 'name' => 'forename',  'required' => false, 'default' => null,   'description' => "User's given (casually called) name." ),
				array( 'name' => 'surname',   'required' => false, 'default' => null,   'description' => "User's family name." ),
				array( 'name' => 'password',  'required' => false, 'default' => null,   'description' => "User's login password." ),
			)
		),
		'login'    => array(
			'description' => 'The "login" request is for signing the user in (identification/authentication).',
			'parameters' => array( 
				array( 'name' => 'user_name', 'required' => true, 'default' => null,    'description' => "User's login name." ),
				array( 'name' => 'password',  'required' => true, 'default' => null,    'description' => "User's login password." )
			)
		),
		'edit'  => array(
			'description' => 'The "Edit" request is for retrieval and/or updating (when fresh != true) of user attributes. When updating, only provided fields are updated--others are left as was.',
			'parameters' => array( 
				array( 'name' => 'user_name', 'required' => false, 'default' => null,   'description' => "Name used to login with and as a casual reference to the user." ),
				array( 'name' => 'password',  'required' => false, 'default' => null,   'description' => "User's login password." ),
				array( 'name' => 'email',     'required' => false, 'default' => null,   'description' => "Email address for contacting the user." ),
				array( 'name' => 'surname',   'required' => false, 'default' => null,   'description' => "User's family name." ),
				array( 'name' => 'forename',  'required' => false, 'default' => null,   'description' => "User's given (casually called) name." ),
				array( 'name' => 'active',    'required' => false, 'default' => null,   'description' => "Is user account active? yes/no" ),
				array( 'name' => 'super',     'required' => false, 'default' => null,   'description' => "Is user a super user? yes/no" ),
			)
		),
		'change'    => array(
			'description' => 'Change the user\'s password.',
			'parameters' => array( 
				array( 'name' => 'old_password', 'required' => true, 'default' => null,    'description' => "User's pre-existing password." ),
				array( 'name' => 'new_password', 'required' => true, 'default' => null,    'description' => "User's chosen new password." )
			)
		),
		'recover'    => array(
			'description' => 'Recover user account access, given the user\'s email address.  An email is sent with an activation code that enables one-time login (to view profile and change password).',
			'parameters' => array( 
				array( 'name' => 'email',     'required' => true, 'default' => null,    'description' => "User's pre-existing password." ),
			)
		),
		'deactivate'    => array(
			'description' => 'Deactivate the specified (or otherwise current) user.',
			'parameters' => array( 
				array( 'name' => 'user_reference', 'required' => false, 'default' => null,    'description' => "User's user name (if textual) or user ID (if numeric)." ),
			)
		),
		'activate'    => array(
			'description' => 'Activate the specified user.  Either the current user must be a super user or else the proper activation code must be supplied.',
			'parameters' => array( 
				array( 'name' => 'user_reference',  'required' => false, 'default' => null,    'description' => "User's user name (if textual) or user ID (if numeric)." ),
				array( 'name' => 'activation_code', 'required' => false, 'default' => null,    'description' => "Code required to activate the user, if current user is not a super user." ),
			)
		),
	)
);


$tables = array (
	'users' => array ( 
		'id'        => array ( 'type' => 'INT(11)',      'key' => 'primary' ),
		'user_name' => array ( 'type' => 'VARCHAR(15)',  'default' => 'null', 'filter' => null ),
		'password'  => array ( 'type' => 'VARCHAR(32)',  'default' => 'null', 'filter' => null ),
		'surname'   => array ( 'type' => 'VARCHAR(15)',  'default' => 'null', 'filter' => null ),
		'forename'  => array ( 'type' => 'VARCHAR(15)',  'default' => 'null', 'filter' => null ),
		'email'     => array ( 'type' => 'VARCHAR(60)',  'default' => 'null', 'filter' => null ),
		'super'     => array ( 'type' => 'BOOLEAN',      'default' => 0,      'filter' => null ),
		'active'    => array ( 'type' => 'BOOLEAN',      'default' => 0,      'filter' => null ),
		'notes'     => array ( 'type' => 'VARCHAR(200)', 'default' => 'null', 'filter' => null ),
		),
	'user_attributes' => array (
		'id'        => array ( 'type' => 'INT(11)',     'key' => 'primary' ),
		'user_id'   => array ( 'type' => 'INT(11)',     'default' => 'NULL' ),
		'attribute' => array ( 'type' => 'VARCHAR(15)', 'default' => 'NULL', 'filter' => null ),
		'value'     => array ( 'type' => 'TEXT',        'default' => 'NULL', 'filter' => null ),
		'editable'  => array ( 'type' => 'BOOLEAN',     'default' => 0,      'filter' => null ),
	), 
);

