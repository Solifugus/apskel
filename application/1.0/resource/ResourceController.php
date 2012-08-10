<?php

# File: ~/application/1.0/resource/ResourceController.php
# Purpose: to provide a facility to retrieve / manage web page resources (e.g. css, images, javascript libraries, etc.) 

require_once('Controller.php');

# Template for Construction of a Controller 
class ResourceController extends Controller {
	# Constructor
	public function __construct($param_framework) {
		// Retain access to the framework
		$this->framework               = $param_framework;
	} // end of __construct

	// *** Process Initialize Requests
	public function processGet( $param ) {
		// Find named resource in the first place, in order of presidence.. 
		if( !isset( $param['name'] ) ) {
			// TODO: provide proper warning, etc..
			return "When requesting a resource, no name was given.<br/>\n";
		}

		// Order of presidence: Environment should overwrite application which overwrites controller.. (except for css, it's a concatenation order)
		$resource_type             = strtolower( substr( strrchr( $param['name'], '.' ), 1 ) );
		$controller_resource_path  = $this->framework->getControllerName() . '/resources/' . $param['name'];
		$environment_resource_path = 'resources/' . $param['name'];
		$application_resource_path = '../resources/' . $param['name'];

		// To concatenate or supersede?
		if( $resource_type == 'css' ) {
			// Concatenate controller + application + environment
			$found = false;
			$resource_content = "/* Resource: {$param['name']} */\n\n";
				print " \"$controller_resource_path\" exists.<br/>\n";
			if( file_exists( $controller_resource_path ) ) { 
				$found = true;
				$resource_content .= "/* From: $controller_resource_path: */\n" . file_get_contents( $controller_resource_path ) . "\n\n";; 
			}
			if( file_exists( $application_resource_path ) ) {
				$found = true;
				$resource_content .= "/* From: $application_resource_path: */\n" . file_get_contents( $application_resource_path ) . "\n\n";; 
			}
			if( file_exists( $environment_resource_path ) ) {
				$found = true;
				$resource_content .= "/* From: $environment_resource_path: */\n" . file_get_contents( $environment_resource_path ) . "\n\n";; 
			}
			if( $found !== true ) { $resource_content = ''; }
		}
		else {
			// Supersede controller with application with environment
			$active_resource_path = null;
			if( file_exists( $controller_resource_path ) )  { $active_resource_path = $controller_resource_path; }
			if( file_exists( $application_resource_path ) ) { $active_resource_path = $application_resource_path; }
			if( file_exists( $environment_resource_path ) ) { $active_resource_path = $environment_resource_path; }
			if( $active_resource_path !== null ) {
				$found = true;
				$resource_content = file_get_contents( $active_resource_path );
			}
			else { $found = false; }
		}

		// Was the resource not found?
		if( $found !== true) {
			// TODO: put a proper error/warning here..
			print "Resource \"{$param['name']}\" was not found.<br/>\n";
		}

		// Return directly to browser
		return $resource_content;
	}
	
} // End of UserController Class

