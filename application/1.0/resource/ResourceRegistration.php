<?php

// File: registration.php
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
// ***************************
// Ideas for future development include (1) a style editor; (2) user access controls; (3) an upload feature; ..

$requests = array(
	'description' => 'The "resource" controller controls access to resource files (e.g. css, images, javascript, etc) via RESTful calls based on a hierarchy.  Resources may exist for the controller, the application, or the environment--the latter of which supersedes the former.  For example, "http://example.com/resource/javascript/jquery.js".',
	'requests'    => array( 
		'get' => array(
			'description' => 'Returns a file of whatever type it is.  E.g. http://example.com/resource/get/name=something.css',
			'parameters' => array( 
				array( 'name' => 'name',        'required' => true, 'default' => null, 'description' => "Relative file name to resource." ),
			)
		),
		'put' => array(
			'description' => 'Adds a new file. (TODO: not yet implemented).',
			'parameters' => array( 
				array( 'name' => 'name',      'required' => true, 'default' => null,   'description' => "Relative file name to resource." ),
			)
		),
		'list'    => array(
			'description' => 'Show a list of resources. (TODO: not yet implemented)',
			'parameters' => array( 
			)
		),
	)
);


