<?php

# File: ~/application_0.0/modules/{{variablized_module}}/{{variablized_module}}_controllers.php
# Purpose: controllers in support of the {{module}} module 

require_once('controllers.php');
require_once('{{variablized_module}}/{{variablized_module}}_models.php');
require_once('{{variablized_module}}/{{variablized_module}}_views.php');

# Class Declaration for Module's Controllers 
class {{classifized_module}}Controllers extends Controllers {
	# Constructor
	public function __construct($param_framework) {
		// Retain access to the framework
		$this->framework               = $param_framework;
		
		// Instantiate the associated model and view
		$this->models = new {{classifized_module}}Models($this->framework);
		$this->views  = new {{classifized_module}}Views($this->framework);
	} // end of __construct

	// *** Process Initialize Requests
	public function processInitialize( $param ) {
		$messages = '';
		$warnings = '';

		if( $param['fresh'] !== true ) {
			// Validate inputs
			$is_bad = false;
			$is_bad_reason = '';

			// TODO: Write validation rules.  Make $is_bad == false if any fail and put the human readable reason in $is_bad_reason
	
			// If no problems with inputs, go ahead and attempt to initialize..
			if( !$is_bad ) {

				// Attempt execution of the SQL
				if( $database_user > '' && $database_password > '' ) {
					$this->models->initializeTables( $super_user, $super_password, $surname, $forename, $email, $database_user, $database_password );
				}
				else {
					$this->models->initializeTables( $super_user, $super_password, $surname, $forename, $email );
				}
	
				// Compose message indicating success
				$messages .= "Initialization of the {{variablized_module}} module was successful. ";
				$this->framework->logMessage( $messages, NOTICE );
				$param = array( 'messages' => "$messages You may now login as \"$super_user\".", 'user_name' => $super_user );
				return $this->views->composeLogin( $param );  // TODO: where to go after initialization of the module?  // XXX
			}
			else {
				// Compose message indicating failure
				$is_bad_reason = "Initialization of the {{variablized_module}} module failed: $is_bad_reason";
				$this->framework->logMessage( $is_bad_reason, WARNING );
			}

			if( $messages > '' )      { $param['messages'] = $messages; }
			if( $is_bad_reason > '' ) { $param['warnings'] = $is_bad_reason; }
		}
		return $this->views->composeInitialize( $param );
	}

	{{controller_methods}}

} // End of {{classifized_module}}Controller Class
