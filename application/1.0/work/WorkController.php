<?php

# File: ~/1.0/user/WorkController.php
# Purpose: to provide a facility to manage and drive work flows 

require_once('Controller.php');
require_once('work/WorkModels.php');
require_once('work/WorkViews.php');

# Template for Construction of a Controller 
class WorkController extends Controller {
	# Constructor
	public function __construct($param_framework) {
		// Retain access to the framework
		$this->framework               = $param_framework;
		
		// Instantiate the associated model and view
		$this->models = new WorkModels($this->framework);
		$this->views  = new WorkViews($this->framework);
	} // end of __construct

	// *** Process Initialize Requests
	public function processInitialize( $param ) {

		$message = 'Initialization of the Work module was requested.';
		$this->framework->logMessage( $message, NOTICE );

		// Ensure the user is authorized -- warn and exit, if not
		// TODO: WORKING..

		// Perform the initialization
		$result = $this->models->buildTables( true );
		if( $result ) {
			$param['messages'] = 'Initialization of Work module tables was successful.';
			return $this->views->getFlow( $param );
		}
		else {
			// TODO: build generalized message view in the Models base view and use that.
			print "Failed initialization of Work module tables.";
		}
	}

	public function processTodo( $param ) {
		return $this->views->getTodo( $param );
	}

	public function processTask( $param ) {
		return $this->views->getTask( $param );
	}

	public function processNewtask( $param ) {
	}

	public function processFlows( $param ) {
		return $this->views->getFlows( $param );
	}

	public function processNewFlow( $param ) {
	}

	public function processReport( $param ) {
		return $this->getReport( $param );
	}

} // End of UserController Class
