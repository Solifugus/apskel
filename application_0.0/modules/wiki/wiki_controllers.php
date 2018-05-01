<?php

# File: ~/application_0.0/modules/wiki/wiki_controllers.php
# Purpose: provide controller logic for the wiki module
# 2018-04-30 ... created.

require_once('controllers.php');
require_once('wiki/wiki_models.php');

# Class Declaration for wiki Module's Controllers
class WikiControllers extends Controllers
{
	// Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;

		// Instantiate the associated model and view
		$this->models = new wikiModels($this->framework);
	} // end of __construct method

	public function process( $param = array(), $missing = '' ) {
		$param['page'] = 'main';
		return $this->processGet( $param, $missing );
	}

	// Handler for the Get Request
	public function processGet( $param = array(), $missing = '' ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$name = '';
		$asof = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Get logic
		$param['template_file'] = $param['page'];	

		// Compose and Output the View;
		$format = array( 'format' => 'template', 'template_file' => 'get.html' );
		return array( $param, $format );
	} // end of processGet controller

	// Handler for the Put Request
	public function processPut( $param = array(), $missing = '' ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$text = '';
		$production = '';
		$from = '';
		$thru = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Put logic
		// TODO

		// Compose and Output the View;
		$format = array( 'format' => 'template', 'template_file' => 'put.html' );
		return array( $param, $format );
	} // end of processPut controller

	// Handler for the Directory Request
	public function processDirectory( $param = array(), $missing = '' ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$asof = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Directory logic
		// TODO

		// Compose and Output the View;
		$format = array( 'format' => 'template', 'template_file' => 'directory.html' );
		return array( $param, $format );
	} // end of processDirectory controller

} // end of WikiControllers class
