<?php

# File: ~/application_0.0/modules/developer/developer_controllers.php
# Purpose: provide controller logic for the developer (web module-builder) module.
#
# NOTE: This file previously contained a verbatim copy of the agent module's
# controllers (processConverse/processSaveReaction/etc.) which did NOT match
# this module's own registration (define/build) and pulled in the agent's
# legacy, injection-prone models. That copy has been excised. These clean
# handlers honor the registered define/build requests and contain no SQL. The
# web module-builder itself is not yet implemented -- the working tool is the
# `tools/build` CLI -- so for now these render honest placeholders.

require_once('controllers.php');
require_once('developer/developer_models.php');
require_once('developer/developer_views.php');

# Class Declaration for the developer Module's Controllers
class DeveloperControllers extends Controllers {

	// Constructor
	public function __construct( $param_framework ) {
		parent::__construct();
		$this->framework = $param_framework;

		// Instantiate the associated model and view
		$this->models = new DeveloperModels( $this->framework );
		$this->views  = new DeveloperViews( $this->framework );
	} // end of __construct

	// Default handler -- show the module-definition page.
	public function process( $param = array() ) {
		return $this->processDefine( $param );
	} // end of process

	// Handler for the Define Request (page on which to define a module to build)
	public function processDefine( $param = array(), $missing = '' ) {
		if( !isset( $param['warnings'] ) ) { $param['warnings'] = ''; }
		if( !isset( $param['messages'] ) ) { $param['messages'] = ''; }
		if( isset( $param['fresh'] ) && $param['fresh'] !== true ) { $param['warnings'] .= $missing; }

		return $this->views->composeDefine( $param );
	} // end of processDefine

	// Handler for the Build Request (build the module as specified)
	public function processBuild( $param = array(), $missing = '' ) {
		if( !isset( $param['warnings'] ) ) { $param['warnings'] = ''; }
		if( !isset( $param['messages'] ) ) { $param['messages'] = ''; }
		if( isset( $param['fresh'] ) && $param['fresh'] !== true ) { $param['warnings'] .= $missing; }

		// Only act on an actual submission, not a plain page view.
		if( isset( $param['fresh'] ) && $param['fresh'] !== true && $this->framework->isFormSubmission() ) {
			$version     = isset( $param['version'] )     ? $param['version']     : '';
			$name        = isset( $param['name'] )        ? $param['name']        : '';
			$description = isset( $param['description'] ) ? $param['description'] : '';
			$requests    = isset( $param['requests'] )    ? $param['requests']    : '';
			$tables      = isset( $param['tables'] )      ? $param['tables']      : '';
			if( $this->models->buildModule( $version, $name, $description, $requests, $tables ) ) {
				$param['messages'] .= "Module \"{$name}\" was built. ";
			}
			else {
				$param['warnings'] .= 'The web module-builder is not yet implemented; use the tools/build CLI. ';
			}
		}

		return $this->views->composeBuild( $param );
	} // end of processBuild

} // end of DeveloperControllers class
