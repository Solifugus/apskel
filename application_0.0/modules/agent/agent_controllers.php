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
		parent::__construct();

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

	// Controller for the Import Request
	public function processImport( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$script   = '';
		$replace  = false;

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );
		$script = stripslashes( $script );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }


		// Perform Interface logic
		$warnings .= $this->models->importXml( $script, $replace );
		if( $warnings == '' ) { $messages .= 'The import was successful.  '; }
		$param['messages'] = $messages;
		$param['warnings'] = $warnings;
		$param['title']    = 'Conversation Script Importer';
		$param['script']   = $script;

		// Compose and Output the View;
		$format = array( 'format' => 'template', 'template_file' => 'import.html' );
		return array( $param, $format );
	} // end of processImport controller

	// Controller for the Export Request
	public function processExport( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$format   = 'xml';
		$wrapper  = '';
		$meanings = '';  // TODO: create way of specifying specific meanings.. and added to parameter of exportXml( .. )

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }


		// Perform Interface logic
		$param['script'] = $this->models->exportXml(  );
		$param['title']           = 'Conversation Script Importer';

		// Compose and Output the View;
		$format = array( 'format' => 'template', 'template_file' => 'import.html' );
		return array( $param, $format );
	} // end of processExport controller

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
		//print "processConverse: " . nl2br( print_r( $param, true ) );

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
		$meaning = $this->models->findClosestMeaning( $statement );
		if( $meaning == null ) {
			// NOTE: The Standard Meaning of an Unrecognized Statement 
			// TODO: Perhaps, attempt to identify a sub-string of it and ask the user if that's what he/she means..
			//       Or, keep a running tab of these and which the users says he/she means more than x% of the time
			//       as to auto-assume thereafter..  If not correct, the user will restate..
			$meaning = $this->models->findClosestMeaning( "What is the meaning of: $statement" );
		}

		$response = $this->models->getAppropriateReaction( $meaning );

		// Compose and Output the View;
		//return $this->views->composeConverse( $param );
		$response['nonverbal'] = addslashes( $response['nonverbal'] );
		$xml_response = "<" . "?xml version=\"1.0\" encoding=\"UTF-8\"?" . ">\n";
		$xml_response = "<response actions=\"{$response['nonverbal']}\">\n<![CDATA[\n{$response['verbal']}\n]]>\n</response>\n";
		return $xml_response;
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
		$logic = '';  // XML (TODO: ultimately, I want this to support SIN and JSON, too)
		$scope = '';  // "amalgamate" or "replace"

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

