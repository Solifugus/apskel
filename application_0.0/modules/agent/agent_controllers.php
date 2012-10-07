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
		$warnings = '';
		$messages = '';
		$format   = 'xml';
		$wrapper  = 'html'; 
		$meanings = '';  // TODO: create way of specifying specific meanings.. and added to parameter of exportXml( .. )

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }


		// Get script in requested format 
		switch( strtolower( $format ) ) {
			case 'json':
				$param['script'] = $this->models->exportJson();
				break;
			case 'xml':
				$param['script'] = $this->models->exportXml();
				break;
			default:
		}

		// Compose and Output the View 
		switch( strtolower( $wrapper ) ) {
			case 'file': 
				// TODO:
				break;
			case 'html': 
				$param['title'] = 'Conversation Script Importer';
				$param['script'] = str_replace( '&', '&amp;', $param['script'] );
				$format = array( 'format' => 'template', 'template_file' => 'import.html' );
				break;
			default:
				$param['warning'] .= "The requested format ($wrapper) is not supported.";
				$format = array( 'format' => 'text' );  // TODO: do something better here..
		}
		return array( $param, $format );
	} // end of processExport controller

	// Controller for the Converse Request
	public function processConverse( $param = array() ) {
		//print "processConverse: " . nl2br( print_r( $param, true ) );

		// Set parameter defaults (in case any required ones are missing)
		$messages  = '';
		$warnings  = '';
		$statement = '';
		$return    = '';
		$topic     = 'Small Talk';
		$passcode  = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Converse logic
		$response = $this->models->reactTo( stripslashes( $statement ) );  // returns array( 'verbal' => '..", 'nonverbal' => '..' )

		// Compose and Output the View;
		$response['nonverbal'] = addslashes( $response['nonverbal'] );
		$xml_response = "<response actions=\"{$response['nonverbal']}\">\n<![CDATA[\n{$response['verbal']}\n]]>\n</response>\n";
		switch( strtolower( trim( $return ) ) ) {
			case 'xml':
				//return array( $xml_response, array( 'format' => 'preformatted', 'mime-type' => 'application/xml' ) );
				return array( $xml_response, array( 'format' => 'xml' ) );
				break;

			case 'json':
				return array( json_encode( $response ), array( 'format' => 'json' ) );
				break;

			case 'text':
				$format = array( 'format' => 'template', 'template_file' => 'interface.html' );
				return array( $response, $format );
				break;

			case 'html':
			default:
				//$param  = array( 'topic' => $topic, 'response' => $xml_response );
				$param  = array( 'topic' => $topic, 'response' => json_encode( $response ) );
				$format = array( 'format' => 'template', 'template_file' => 'interface.html' );
				return array( $param, $format );
				break;
		}
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

	// Controller for the Meanings Request
	public function processMeanings( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages  = '';
		$warnings  = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Editor logic
		$response = array();
		$response['meanings'] = $this->models->getAllMeanings();

		// Compose and Output the View
		$format = array( 'format' => 'template', 'template_file' => 'meanings.html' );
		return array( $response, $format );
	} // end of processMeanings request handler 

	// Controller for the "save_meaning" Request
	public function processSaveMeaning( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages   = '';
		$warnings   = '';
		$meaning_id = null;
		$recognizer = '';
		$paradigm   = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Editor logic
		list( $meaning_id, $problems) = $this->models->saveMeaning( $this->framework->removeAllBut( array( 'meaning_id', 'recognizer', 'paradigm'), $param ) );
		$response = array( 'meaning_id' => $meaning_id, 'warnings' => $warnings . $problems, 'messages' => $messages );

		// Compose and Output the View
		$format = array( 'format' => 'template', 'template_file' => 'meanings.html' );
		//return array( $response, $format );  // TODO: at some point, provide xml, json, and html return formats..
		return $this->processMeanings( array( 'fresh' => $fresh ) );  // for now, we'll just do this..
	} // end of processMeanings request handler 


	// Request Handler for Reactions 
	public function processReactions( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages   = '';
		$warnings   = '';
		$meaning_id = ''; 
		$passcode   = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Editor logic
		$response = array( 'reactions' => array(), 'meaning_id' => $meaning_id );
		$reactions = $this->models->getAllReactionsByMeaning( $meaning_id );
		foreach( $reactions as $reaction ) {
			switch( strtoupper( $reaction['functional'] ) ) {
				case 'T': $functional = 'True'; break;
				case 'F': $functional = 'False'; break;
				case 'U': $functional = 'Untested'; break;
				default:  $functional = 'Untested';
			}
			$response['reactions'][] = array( 'meaning_id' => $meaning_id, 'reaction_id' => $reaction['id'], 'priority' => $reaction['priority'], 'functional' => $functional, 'conditions' => $reaction['conditions'], 'actions' => $reaction['actions'] );
		}
		$meaning = $this->models->getMeaning( $meaning_id );
		$response = array_merge( $response, $meaning );
		$response['paradigm'] = $this->framework->mapToKey( $response['paradigm'], $this->models->paradigm_mappings, 'natural' );

		// Compose and Output the View
		$format = array( 'format' => 'template', 'template_file' => 'reactions.html' );
		return array( $response, $format );
	} // end of processReactions request handler 


	// Controller for the "save_meaning" Request
	public function processSaveReaction( $param = array() ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages    = '';
		$warnings    = '';
		$meaning_id  = null;
		$reaction_id = null;
		$priority    = '';
		$functional  = '';
		$conditions  = '';
		$actions     = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Editor logic
		list( $reaction_id, $problems) = $this->models->saveReaction( $this->framework->removeAllBut( array( 'meaning_id', 'reaction_id', 'priority', 'functional', 'conditions', 'actions' ), $param ) );
		$response = array( 'meaning_id' => $meaning_id, 'warnings' => $warnings . $problems, 'messages' => $messages );

		// Compose and Output the View
		$format = array( 'format' => 'template', 'template_file' => 'meanings.html' );
		//return array( $response, $format );  // TODO: at some point, provide xml, json, and html return formats..
		$response['fresh'] = $fresh;
		return $this->processReactions( $this->framework->removeAllBut( array( 'meaning_id', 'warnings', 'fresh' ), $response ) );  // for now, we'll just do this..
	} // end of processMeanings request handler 


} // end of AgentControllers class

