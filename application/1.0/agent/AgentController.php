<?php

# File: ~/1.0/user/AgentController.php
# Purpose: to provide a facility to manage and drive agent conversations 

require_once('Controller.php');
require_once('agent/AgentModels.php');
require_once('agent/AgentViews.php');

# Template for Construction of a Controller 
class AgentController extends Controller {
	# Constructor
	public function __construct($param_framework) {
		// Retain access to the framework
		$this->framework               = $param_framework;
		
		// Instantiate the associated model and view
		$this->models = new AgentModels($this->framework);
		$this->views  = new AgentViews($this->framework);
	} // end of __construct

	// *** Process Initialize Requests
	public function processInitialize( $param ) {

		$message = 'Initialization of the Agent module was requested.';
		$this->framework->logMessage( $message, NOTICE );

		// Ensure the user is authorized -- warn and exit, if not
		// TODO: WORKING..

		// Perform the initialization
		$result = $this->models->buildTables( true );
		if( $result ) {
			$param['messages'] = 'Initialization of Agent module tables was successful.';
			return $this->views->getInterface( $param );
		}
		else {
			// TODO: build generalized message view in the Models base view and use that.
			print "Failed initialization of Agent module tables.";
		}
	}

	public function processPutReasoning( $param ) {
		// ~/meaning0/recognizer: hello [name]
		// ~/meaning0/reaction0/conditions: 
		// ~/meaning0/reaction0/action0/say: Hello, [name]!
		// ~/meaning0/reaction0/action1/forget: user's name is *
		// ~/meaning0/reaction0/action1/remember: user's name is [name]
		// ~/meaning0/reaction0/action2/interpret as: How are you?
		// ~/meaning0/reaction0/action3/expect: yes
		// ~/meaning0/reaction0/action3/expect/as: no 
		// ~/meaning0/reaction0/action/
		// ~/meaning0/reaction0/action/
		// ~/meaning0/reaction0/action/
		// ~/meaning0/reaction0/action/
		// ~/meaning0/reaction0/action/
		return "";
	}

	public function processGetReasoning( $param ) {
	}

	public function processCreateTopic( $param ) {
	}

	public function processSetTopic( $param ) {
	}

	public function processInterface( $param ) {
		return $this->views->getInterface();
	}

	public function processConverse( $param ) {
	}


} // End of UserController Class
