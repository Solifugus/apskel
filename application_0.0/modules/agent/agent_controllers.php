<?php

# File: ~/application_0.0/modules/agent/agent_controllers.php
# Purpose: provide controller logic for the agent module
# 2012-09-12 ... created.

require_once('controllers.php');
require_once('agent/agent_models.php');
require_once('agent/agent_views.php');

# Class Declaration for agent Module's Controllers
class AgentControllers extends Controllers
{
	// Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;

		// Instantiate the associated model and view
		$this->models = new agentModels($this->framework);
		$this->views = new agentViews($this->framework);
	} // end of __construct method

	// Controller for the Initialize Request
	public function processInitialize( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Do provided parameters validate?
		$is_bad = false;
		//TODO: make $is_bad = true if parameters do not validate
		
		if( !$is_bad ) {
			$this->models->buildTables( true );
			$messages .= 'Initialization of the agent module was successful. ';
			$this->framework->logMessage( $messages, NOTICE );
			return $messages; // TODO: in this case, return what view?
		}
		else {
		if( $messages !== '' ) { $param['messages'] = $messages; }
		return $this->views->composeInitialize( $param );
		}
		
	} // end of processInitialize controller

	// Controller for the Interface Request
	public function processInterface( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$type     = '';
		$topic    = '';
		$passcode = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Interface logic
		$param['response_format'] = 'template=interface.html';
		$param['title']           = 'Conversational Interface';
		$param['transcript']      = '';  // any initial agent response statement

		// Compose and Output the View;
		$format = array( 'format' => 'template', 'template_file' => 'interface.html' );
		return array( $param, $format );
	} // end of processInterface controller

	// Controller for the Converse Request
	public function processConverse( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$statement = '';
		$return_as = '';
		$topic = '';
		$passcode = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Converse logic
		// TODO

		// Compose and Output the View;
		return $this->views->composeConverse( $param );
	} // end of processConverse controller

	// Controller for the Create_topic Request
	public function processCreateTopic( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$topic = '';
		$passcode = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Create_topic logic
		// TODO

		// Compose and Output the View;
		return $this->views->composeCreateTopic( $param );
	} // end of processCreate_topic controller

	// Controller for the Set_topic Request
	public function processSetTopic( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$topic = '';
		$passcode = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Set_topic logic
		// TODO

		// Compose and Output the View;
		return $this->views->composeSetTopic( $param );
	} // end of processSet_topic controller

	// Controller for the Get_reasoning Request
	public function processGetReasoning( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Get_reasoning logic
		// TODO

		// Compose and Output the View;
		return $this->views->composeGetReasoning( $param );
	} // end of processGet_reasoning controller

	// Controller for the Put_reasoning Request
	public function processPutReasoning( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$logic = '';
		$scope = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Put_reasoning logic
		// TODO

		// Compose and Output the View;
		return $this->views->composePutReasoning( $param );
	} // end of processPut_reasoning controller

} // end of AgentControllers class
