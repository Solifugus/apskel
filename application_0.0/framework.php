<?php
// File: framework.php
// Purpose: interprets web requests, calls proper controllers, consolidates, wraps, filters, and returns to browser or command line client 
// History
// 2011-10-13 MCT Created.
// 2011-11-20 MCT Added CLI support
// 2011-11-23 MCT Built logging mechanism
// 2011-12-05 MCT Adapted for new file layout and config file layout
// 2011-01-11 MCT Partially added multiple simultaneous database support (must finish)
// 2012-01-06 MCT Built in ability to make sub-requests (not from a client but from controller to controller)
// 2012-01-25 MCT Rebuilt auto RESTful API documentation generation & warnings for misuse
// 2012-04-17 MCT Added framework for getting resources, according to environment/hierarchy
// 2012-05-02 MCT Built new URI extraction functions and landing page by environment config
// 2012-08-31 MCT Added "fresh" default parameter to all requests and showDebug() function

// Global Defines for Log Message Types
define('FATAL',    0);
define('CRITICAL', 1);
define('WARNING',  2);
define('DEBUG',    3);
define('NOTICE',   4);

class Framework {
	// Identity object (how request arrived, to where, and settings thereof)
	protected $identity;

	// Request Settings
	protected $session_id;             // current session ID (from php or artificially maintained via cli)
	protected $request_session_id;     // identifies the current session
	protected $controller_name;
	protected $request_name;
		
	// Application Settings
	protected $application_name;          // name of this application
	protected $application_environments;  // settings for all application environments
	protected $application_log_path;      // path to where environment log files are
	protected $application_data_path;     // path to where environment data sub-directories are
	protected $application_controllers;   // registration of controllers, requests, and their parameters

	// Environment Settings
	protected $environment_name;          // name of this environment
	protected $environment_version;       // version of application to use for this environment
	protected $environment_write;         // actually allow writes to database or just pretend to write
	protected $environment_debug;         // output debugging info?
	protected $environment_salt;          // key to make encryptions unique to this environment
	protected $environment_address;       // address (or part thereof) used to identify this environment by
	protected $environment_email_from;    // return address for email from 
	protected $environment_admin_emails;  // email address list of administrators for current environment
	protected $environment_log;           // path + name for log file

	// Database Settings
	protected $database_connection = null; // current request's PDO database connection

	// Internal Settings
	protected $page_wrapping = 'html';     // 'html' (default), 'none', exensible..

	// Request Processing Related Methods
	public function getControllerName() {
		return strtolower($this->getClassifizedName( $this->controller_name ));
	}

	public function getControllerFileName( $param_controller_name = null ) {
		if( $param_controller_name == null ) { $controller_name = $this->getControllerName(); }
		else                                 { $controller_name = $param_controller_name; }
		return $this->getVariablizedName( $controller_name) . '/' . $this->getVariablizedName( $controller_name . '_controllers' ) . '.php';
	}

	public function getControllerRegistrationFileName( $param_controller_name = null ) {
		if( $param_controller_name == null ) { $controller_name = $this->getControllerName(); }
		else                                 { $controller_name = $param_controller_name; }
		return $this->getVariablizedName( $controller_name) . '/' . $this->getVariablizedName( $controller_name . '_registration' ) . '.php';
	}

	public function getControllerClassName( $param_controller_name ) {
		return $this->getClassifizedName( $param_controller_name ) . 'Controllers';
	}

	public function getRequestName() {
		return strtolower($this->request_name);
	}

	public function getRequestMethodName( $param_request_name = null) {
		if( $param_request_name == null ) { $request_name = $this->getControllerName(); }
		else                                 { $request_name = $param_request_name; }
		return 'process' . ucfirst( $this->getFunctionizedName( $request_name ) );
	}

	// Configuration Related Methods

	// DNS Name Request was Sent To
	public function getRequestAddress() {
		return $this->request_address;
	}

	// Application's Environment
	public function getEnvironment() {
		return $this->identity->environment;
	}

	// Application's Name
	public function getApplication() {
		return $this->identity->application;
	}

	// Get ID to a Database of the Specified Usage 
	public function getIdOfDatabaseFor( $param_usage = 'read_write' ) {
		// TODO: build failover mechanism..
		$options = array();
		$last = count( $this->identity->databases );
		for( $id = 0; $id < $last; $id++ ) {
			if( strtolower($this->identity->databases[$id]['usage']) === strtolower( $param_usage ) ) { $options[] = $id; }
		}
		if( count( $options ) == 0 ) { return null; }
		else {
			return $options[0];  // TODO: make this a random selection of options available, except but keep persistent throughout request 
		}
	}

	// Database Server Address
	public function getDatabaseAddress( $id = 0 ) {
		return $this->identity->databases[$id]['address'];
	}

	// Database Port Number or Null for Default
	public function getDatabasePort( $id = 0 ) {
		return $this->databases[$id]['name'];
	}

	// Name of Database in Database Server
	public function getDatabaseName( $id = 0 ) {
		return $this->identity->databases[$id]['name'];
	}

	// Prefix to Append to Each Table Name in Database
	public function getDatabasePrefix( $id = 0 ) {
		return $this->identity->databases[$id]['prefix'];
	}

	// User for Access to Database
	public function getDatabaseUser( $id = 0 ) {
		return $this->identity->databases[$id]['user'];
	}

	// User's Password for Access to Database
	public function getDatabasePassword( $id = 0 ) {
		return $this->identity->databases[$id]['password'];
	}

	// How Database May be Used: 'read_write', 'read_only', or 'failover'
	public function getDatabaseUsage( $id = 0 ) {
		return $this->databases[$id]['usage'];
	}
	
	public function getUriController( $uri ) {
		$controller_request = preg_replace( '/\?.*$/', '', $uri );
		list( $controller, $request ) = @explode( '/', $controller_request, 2 );
		return $controller;
	}
	
	public function getUriRequest( $uri ) {
		$controller_request = preg_replace( '/\?.*$/', '', $uri );
		list( $controller, $request ) = @explode( '/', $controller_request, 2 );
		return $request;
	}

	public function getUriParameters( $uri ) {
		$request_parameters = array();
		if( strpos( $uri, '?' ) !== false ) {
			list( $controller_request, $parameters ) = @explode('?', $uri, 2);
			$parameters         = urldecode($parameters);
			$parameters         = explode('&', $parameters);
			foreach ($parameters as $parameter) {
				list($key, $value) = explode('=', $parameter, 2);
				$request_parameters[$key] = $value;
			}
		}
		return $request_parameters;
	}
	
	// *** Gathers and Prepares all Global Settings
	public function __construct( $identity ) {
		ini_set( 'display_errors', 1 );
		error_reporting( E_ALL );
		$this->identity = $identity;
		$this->registration = array();          // prepares place to hold controller registrations relevant for this request
		$this->determineSessionVariables();
		$this->determineRequestDetails();
	}


	public function getSetting( $attribute ) {
		if( isset( $this->identity->settings[$attribute] ) ) {
			return $this->identity->settings[$attribute];
		}
		else {
			return null;
		}
	}


	// *** Revive or Initialize the Session  (TODO: Abstract to Maintain a CLI Session AND ALSO store session in database)
	protected function determineSessionVariables() {
		// Start a/the Session and Initiatize, if New  
		if (!session_start()) {
			// TODO: add cli session support in here..
			print "Unable to start a session.  Is this a cookie issue?\n";
			$this->logMessage("Failed to initialize a session.\n", FATAL);
			exit;
		} else {
			$this->session_id = session_id();
		}
		
		// If Session is New, Set Up Defaults, etc.
		if (!isset($_SESSION['user_name'])) {
			# Can we get the user?
			# TODO: shibboleth or other methods..
			
			# Set the landing controller/request as default
			$_SESSION['controller'] = $this->getUriController( $this->identity->settings['landing_page'] ); // controller last set as session default
			$_SESSION['request']    = $this->getUriRequest( $this->identity->settings['landing_page'] );    // request last se as session default

			// current session output format, unless overridden
			if ($this->identity->request_protocol == 'cli') {
				$_SESSION['output_format'] = 'html';
			} else {
				$_SESSION['output_format'] = 'html';
			}
			
			// TODO: put landing parameters into $_REQUEST (underriding what's already there)
		}
	}

	// **** Ensure Session Variables are Saved for Later Revival
	protected function saveSessionVariables() {
		// TODO: note that CLI sessions will need saving but regular session are already handled by PHP
	}

	// *** End the Session
	protected function endSession() {
		// TODO: basic function required for logouts and timeouts
	}

	// *** Process Web Request to Set Basic Information (but not execute it)
	protected function determineRequestDetails($param_show = false) {
		// Remove any slashes to web request ($_REQUEST) parameters that may have been added by PHP
		if (get_magic_quotes_gpc()) {
			$_REQUEST = stripAllSlashes($_REQUEST);
		}
		
		// Perform Any URI Rewrite Rules
		// TODO: design and code this feature so don't need mod_rewrite rules..

		// If '/' separated parameters exist, extract them.
		if ( $this->identity->request_path > '' ) {
			$uri_parameters = explode('/', $this->identity->request_path);

			// Get controller name, if in URL
			if (count($uri_parameters > 0)) {
				$controller = $uri_parameters[0];
			}

			// Get request name, if in URL
			if ( count($uri_parameters) > 1 ) {
				$request = $uri_parameters[1];
			}

			// Get array element number of first parameter (presuming first two are, indeed, controller and request)
			if ( count($uri_parameters) > 2 ) {
				$current_param = 2;
			} else {
				$current_param = count($uri_parameters) - 1;
			}

			// If $controller doesn't exist then interpret as a parameter and revert to the session default controller/request..
			$controller = strtolower($controller);
			if ( !$this->isController( $controller ) && $controller !== 'resource' ) {
				$controller    = $_SESSION['controller'];
				$request       = $_SESSION['request'];
				$current_param = 0; // revert to all uriParameters being request parameters
			}
			
			// if $request doesn't exist under $controller then interpret as a parameter and revert to the controller's 'default' request..
			if( !isset( $request ) && $controller !== 'resource' ) {
				$request       = $_SESSION['request'];
				$current_param = 1; // revert to all uriParameters after the first (controller) as being request parameters
			}
			
			// Collect request parameters and override web request ($_REQUEST) parameters with them..
			while ($current_param < count($uri_parameters)) {
				# for each request parameter, take anything after first '=' as its value else assign null to it
				$parts = explode('=', urldecode($uri_parameters[$current_param]), 2);
				if (isset($parts[1])) {
					$_REQUEST[trim($parts[0])] = trim($parts[1]);
				} else {
					$_REQUEST[trim($parts[0])] = null;
				}
				$current_param += 1;
			}
		}

		// If still no controller or request then use defaults from session (which are initially landing defaults)
		if(!isset($controller) || $controller == '') {
			$controller = $_SESSION['controller'];
		}
		if(!isset($request) || $request == '') {
			$request = $_SESSION['request'];
		}

		
		// Set Controller and Request Related Properties
		$this->controller_name = $controller;  // controller name 
		$this->request_name    = $request;     // request name
	} // End of determineRequestDetails() 

	// *** Top level request response method
	public function getResource() {
		$page_content = $this->getView();
		switch( $this->page_wrapping ) {
			case 'none':
				return $page_content;
				break;

			case 'html':
			default:
				return "<html>\n<body>" . $page_content . "\n</body>\n</html>\n"; 
				
		}
	}

	// *** Execute the Controller and Return the View
	public function getView( $controller_name = null, $request_name = null, $parameters = null ) { 
		// Default to the current web request, else explicitly specified request (can be a subrequest)
		if( $controller_name === null ) { $controller_name  = $this->controller_name; }
		if( $request_name    === null ) { $request_name     = $this->request_name; }
		if( $parameters      === null ) { $parameters       = $_REQUEST; }

		//print "DEBUG:\nController: $controller_name; Request: $request_name; Parameters:"; var_dump($parameters); exit; // MCT XXX

		// The 'resource' controller is built-in for getting resource files (css, javascript, images, etc)
		if( $controller_name == 'resource' ) {
			$controller_name = strtolower( $request_name );
			$file_name = trim( strtolower( substr( $this->identity->request_path, strlen( "resource/$controller_name/" ) ) ), '/' );
			$this->page_wrapping = 'none';
			return $this->getResourceFile( $file_name, $controller_name ); 
		}

		// Begin with the presumption that the request is good
		$request_is_fatal = false;
		$request_is_fatal_reason = '';
		
		// Get Controller's Registration
		$registration = $this->getControllerRequests( $controller_name );
		if( $registration === null ) {
			$request_is_fatal = true;
			$registration_file_name = $this->getControllerRegistrationFileName( $controller_name );
			$request_is_fatal_reason .= "There is no registration file for the \"$controller_name\" controller (\"~/application_{$this->identity->version}/modules/$registration_file_name\"). "; 
		}

		$sanitized_parameters  = array();  // array to collect sanitized request parameters provided and/or their registered defaults
		$missing_parameters    = '';       // place to collect warnings on missing required request parameters (if any)

		// Does the request exist under the controller?
		if( !isset( $registration['requests'][$request_name] ) ) {
			$request_is_fatal = true;
			$request_is_fatal_reason .= "The \"$request_name\" request is not registered under the \"$controller_name\" controller.";
		}
		else {
			// Does the registration file look corrupted?
			if( !isset( $registration['requests'][$request_name]['parameters'] ) || !is_array( $registration['requests'][$request_name]['parameters'] ) ) {
				$request_is_fatal = true;
				$request_is_fatal_reason .= "The \"$controller_name\"'s \"$request_name\" request has malformed parameter registrations.";
			}
			else {
				// Collect request parameters according to the registration
				foreach( $registration['requests'][$request_name]['parameters'] as $parameter ) {
					if( isset( $_REQUEST[$parameter['name']] ) ) { 
						// Parameter was provided on web request to collect it..
						$sanitized_parameters[$parameter['name']] = addslashes( $_REQUEST[$parameter['name']] );
					}
					else {
						// Parameter was not provided on web request to collect its default value..
						$sanitized_parameters[$parameter['name']] = addslashes( $parameter['default'] );

						// If required, annotate because the parameter was not provided.
						if( $parameter['required'] === true ) {
							$missing_parameters .= "The \"{$parameter['name']}\" parameter was required but not provided. ";
						}
					}
				}
			}
		}

		// if the "fresh" parameter wasn't provided, add it and assign it as boolean true (for controller/view to know user just arrived here)
		if( !isset( $_REQUEST['fresh'] ) ) { $sanitized_parameters['fresh'] = true; }
		else                               { $sanitized_parameters['fresh'] = addslashes( $_REQUEST['fresh'] ); }

		// Does the controller's code file actually exist?
		$controller_file_name = $this->getControllerFileName( $controller_name );
		if( !file_exists( $controller_file_name ) ) {
			$request_is_fatal = true;
			$request_is_fatal_reason = "The \"$controller_name\" controller has no associated programming code (~/application_{$this->identity->version}/modules/$controller_file_name). ";
		}
		else {
			// Does the controller's class exist?
			require_once( $controller_file_name );
			$controller_class_name = $this->getControllerClassName( $controller_name );
			if( !class_exists( $controller_class_name ) ) {
				$request_is_fatal = true;
				$request_is_fatal_reason = "The \"$controller_name\" controller's class is not defined in its code file (~/application_{$this->identity->version}/modules/$controller_file_name). ";
			}
			else {
				// Does the request's method exist?
				$controller = new $controller_class_name( $this );
				$request_method_name = $this->getRequestMethodName( $request_name );
				if( !method_exists( $controller, $request_method_name ) ) {
					$request_is_fatal = true;
					$request_is_fatal_reason = "The \"$controller_name\" controller's \"$request_method_name\" method is not defined in the code (~/application_{$this->identity->version}/modules/$controller_file_name). ";
				}
			}
			
		}

		// If a Bad Request, Return the help for the request or else help for the entire controller.. 
		if( $request_is_fatal ) {
			$view = $this->getCss();
			$view .= "<div id=\"bad_request_view_wrapper\" class=\"view_wrapper\">\n"; 
			$view .= "<div id=\"message_area\">\n";
			$view .= "<span id=\"warnings\">The web request failed: $request_is_fatal_reason</span><br/>\n";
			$view .= "</div>\n";
			$view .= "Controller: \"{$this->controller_name}\"<br/>\n";
			$view .= "Description:<br\n{$registration['description']}\n\n";
			if( isset( $registration['requests'][$request_name] ) ) {
				$view .= $this->getRequestHelp( $request_name, $registration['requests'][$request_name] );
			}
			else {
				foreach( $registration['requests'] as $request => $details ) {
					$view .= $this->getRequestHelp( $request, $details );
				}
			}
			$view .= "</div>\n";

			$this->logMessage( "Failed to process request: {$request_is_fatal_reason}.\n", NOTICE );
			return $view;
		}
		else {
			// If a Good Request, Return the Following..
			return $controller->$request_method_name( $sanitized_parameters, $missing_parameters );  // TODO: deal with output format mechanism
		}

	} // End of getView()

	private function getRequestHelp( $request, $details ) {
		$view = "\t* Request \"{$request}\" -- {$details['description']}<br/>\n";
		foreach( $details['parameters'] as $parameter ) {
			$view .= "\t\t- \"{$parameter['name']}\": {$parameter['description']}<br/>\n";
			if( $parameter['default'] === null ) {
				$default = "Must be supplied<br/>\n";
			}
			else {
				$default = "When not supplied, defaults to  \"{$parameter['default']}\"";
			}
			$view .="\t\t\t$default<br/>\n";
		}
		return $view;
	}

	private function getCss() {
               return <<<EndOfCSS
                        <style type="text/css">
                                body { background-color: #009999; }
                                .view_wrapper { display: block; width: 640px; padding: 5px; border: 1px; border: solid 1px #85b1de; border-radius: 20px; margin-left: auto; margin-right: auto; background-
                                #title { display: block; width: 100%; font-size: 16px; text-align: center; font-weight: bold; }
                                .label { display: inline-block; width: 100px;  }
                                .input { width: 150px;  border: solid 1px #85b1de; background-color: #EDF2F7; }
                                .input:hover { border-color: orange; }
                                .warnings { color: #FF0000; font-weight: bold; }
                                .messages { color: #000000; }
                                .message_area { border: 1px solid black; width: 95%; border-radius: 10px; padding: 10px; background-color: #AAAAFF; color: #000000; }
                                .button {
                                        -moz-box-shadow:inset 0px 1px 0px 0px #caefab;
                                        -webkit-box-shadow:inset 0px 1px 0px 0px #caefab;
                                        box-shadow:inset 0px 1px 0px 0px #caefab;
                                        background-color:transparent;
                                        -moz-border-radius:6px;
                                        -webkit-border-radius:6px;
                                        border-radius:6px;
                                        border:1px solid #85b1de;
                                        display:inline-block;
                                        color: #0000ff;
                                        font-family:arial;
                                        font-size:15px;
                                        font-weight:bold;
                                        padding:6px 24px;
                                        text-decoration:none;
                                        text-shadow:1px 1px 0px #aade7c;
                                }
                                .button:active {
                                        position:relative;
                                        top:1px;
                                }
                                .button:hover {
                                        background-color: #85b1de;
                                }
                        </style>
EndOfCSS;
	}
	
	// Converts displayable name to variable-name convention
	public function getVariablizedName( $param_name ) {
		$param_name = strtolower($param_name);
		$param_name = str_replace(' ', '_', $param_name);
		return $param_name;
	}
	
	// Converts displayable name to function-name convention
	public function getFunctionizedName($param_name) {
		$name = str_replace( '_', ' ', $param_name );
		$name = ucwords($name);
		$name = str_replace(' ', '', $name); // TODO: convert to regex, eliminating all non-alphabetic characters
		$name = lcfirst($name);
		return $name;
	}
	
	// Converts displayable name to class-name convention (word's should be space separated)
	public function getClassifizedName($param_name) {
		$param_name = ucwords($param_name);
		$param_name = str_replace(' ', '', $param_name); // TODO: convert to regex, eliminating all non-alphabetic characters
		return $param_name;
	}

	// Compose a message in <pre> tags, echo'ing by default (mostly useful for debugging)
	public function showDebug( $a = 'I Am Skipped', $b = 'I Am Skipped' ) {
		if( $a !== 'I Am Skipped') {
			if( is_array( $a ) ) { echo "\n<pre>\n" . print_r( $a, true ) . "\n</pre>\n"; }
			else                 { echo $a; }
		}

		if( $b !== 'I Am Skipped') {
			if( is_array( $b ) ) { echo "\n<pre>\n" . print_r( $b, true ) . "\n</pre>\n"; }
			else                 { echo $b; }
		}
	}


	# Write message(s) to log--takes single string or array of strings
	public function logMessage($argMessage, $argNature = NOTICE) {
		# Get date/time
		$when = date('[Y-m-d H:i:s T]');
		if (isset($_SESSION['user'])) {
			$user = $_SESSION['user'];
		} else {
			$user = 'unspecified';
		}
		$session = session_id();
		if (is_array($argMessage)) {
			$argMessage = print_r($argMessage, true);
		}
		// Open the log and document this web request..
		try {
			$fo         = fopen($this->getSetting('log_file'), 'a');
			$log_message = "$when " . $this->getMessageNature($argNature) . " ($session as $user): {$argMessage}\n";
			fputs($fo, "$log_message");
			fclose($fo);
		}
		catch (PDOException $e) {
			$print_message = "Error in this web application: " . $e->getMessage() . "<br>\n<br>\nWhile trying to log this message:<br>\n$log_message<br>\n";
			print "$print_message";
			$this->mailAdmin("FATAL ERROR", $print_message);
			exit(1);
		}
		
		# If a disaster then notify administrator(s) by email..
		if ($argNature == FATAL) {
			print "A serious system error occured and support staff are being notified.\n";
			$this->mailAdmin($this->getMessageNature($argNature), $logMessage);
		}
	}
	
	# Returns text translation of a message nature define
	private function getMessageNature($argNature) {
		switch ($argNature) {
			case 0:
				return 'FATAL';
				break;
			case 1:
				return 'CRITICAL';
				break;
			case 2:
				return 'WARNING';
				break;
			case 3:
				return 'DEBUG';
				break;
			case 4:
				return 'NOTICE';
				break;
			default:
				return "LOG TYPE #$argNature";
				break;
		}
	}
	
	# Emails all registered administrators
	public function mailAdmin($argSubject, $argMessage) {
		$replyTo = "FROM: {$this->getSetting('email_from')}\r\n";
		mail($this->getSetting('admin_emails'), "{$this->getApplication()}: $argSubject", $argMessage, $replyTo);
	}
	
	# Insert prefix to the beginning of each line in the text
	public function prefixString($argPrefix, $argTimes, $argText) {
		$reply = '';
		$lines = explode("\n", $argText);
		foreach ($lines as $line) {
			$reply .= str_repeat($argPrefix, $argTimes) . trim($line) . "\n";
		}
		return $reply;
	}
	
	# Recursively Strip Slashes in an Array
	public function stripAllSlashes($argData) {
		return is_array($argData) ? array_map('stripAllSlashes', $argData) : stripslashes($argData);
	}

	// *** Run a Query and Return any Result
	public function runSql( $param_sql, $param_user = null, $param_password = null ) {
		// Ensure we have a useable database connection
		if(!$this->database_connection) {
			$database_id = $this->getIdOfDatabaseFor('read_write'); // TODO: build mechanism to know when to use each database usage type..
			if( $database_id === null ) {
				print "No database found to use..<br/>\n";
				// TODO: log error
			}
			$address  = $this->getDatabaseAddress($database_id);
			$database = $this->getDatabaseName($database_id);
			$user     = $this->getDatabaseUser($database_id);
			$password = $this->getDatabasePassword($database_id);
			//$this->database_connection = $this->openDatabase($this->getDatabaseAddress($database_id), $this->getDatabaseName($database_id), $this->getDatabaseUser($database_id), $this->getDatabasePassword($database_id));
			$this->database_connection = $this->openDatabase( $address, $database, $user, $password );
		}

		// Run the given SQL
		try {
			$result = $this->database_connection->query($param_sql);
		} catch(PDOException $error) {
			$message = "Query failed: $error\n$param_sql";
			$this->logMessage($message, CRITICAL);
			return null;
		}

		// Log the Query
		// TODO 

		// Return can be used as an array of rows of associative column names/values
		if( is_object( $result ) ) {
			$rows = $result->fetchAll();  
		}
		else {
			$message = "Unable to retrieve results from query: $param_sql";
			return null;
		}
		return $rows;
	}
	

	// *** Open the database or report failure to do so
	public function openDatabase( $param_database_address, $param_database_name, $param_database_user, $param_database_password ) {
		# Try to open a database connection
		try {
			$db = new PDO("mysql:host=$param_database_address;dbname=$param_database_name", $param_database_user, $param_database_password);
		}
		catch (PDOException $errorObject) {
			# If a priviledged user/password was provided, retry with those..
			// TODO: Modify for new way -- probably use protected properties 'priviledged_user' and 'privileged_password'
			if (isset($_REQUEST['systemrequest']) && strtolower($_REQUEST['systemrequest']) == 'create database') {
				# were we given user and password parameters?
				if (isset($_REQUEST['user']) && isset($_REQUEST['password'])) {
					$priviledged_user     = $_REQUEST['user']; # to try in place of environment's user
					$priviledged_password = $_REQUEST['password']; # to try in place of environment's password
					unset($_REQUEST['systemrequest']); # ensure we don't cause an infinite recursive loop              
					$db = $this->openDatabase($param_database_address, $param_databaseName, $priviledged_user, $priviledged_password);
					if ($db !== null) {
						return $db;
					}
				}
				
				# We were not given the user and password parameters, along with the 'setup' request.
				else {
					$message = "A database setup was requested for {$this->settings->getApplication()}'s {$this->settings->getEnvironment()} environment, but a user and password were not provided.";
					$this->logMessage($message, WARNING);
				}
			}
			
			# Log failure to connect to database
			$errorMessage = $errorObject->getmessage();
			$this->logMessage("The {$this->getEnvironment()} environment failed to open a database connection: $errorMessage\n", WARNING);
			
			# If database unknown, try to create it
			if (stristr($errorMessage, 'Unknown database')) {
				# Attempt raw database connection with no database specified
				$wasError = false;
				try {
					$db = new PDO("mysql:host=$param_database_address", $param_database_user, $param_database_password);
				}
				catch (PDOException $errorObject) {
					$wasError     = true;
					$errorMessage = $errorObject->getmessage();
					$message      = "Attempt to connect to {$this->getApplication()}'s {$this->getEnvironment()} environment's database server without specifying a database failed: $errorMessage";
					print "$message\n";
					$this->logMessage($message, WARNING);
				}
				if (!$wasError) {
					# Successful connection to database, proceed to run SQL create statements..
					$message = "Connecting to the {$this->getApplication()}'s {$this->getEnvironment()} environment database server (not specifying a database) succeeded.\n";
					$this->logMessage($message, NOTICE);
					$wasError = false;
					try {
						$stmt = $db->prepare( $this->getCreateDatabaseSql( $param_database_name, $param_database_user, $param_database_password ) ); 
						$stmt->execute();
					}
					catch (PDOException $errorObject) {
						$wasError          = true;
						$params['message'] = "Creating an initial database for {$this->getApplication()}'s {$this->getEnvironment()} environment failed: " . $errorObject->getmessage() . "\n";
						$this->logMessage($params['message'], FATAL);
					}
					if (!$wasError) {
						$message .= "Creation of the database for the {$this->getApplication()}'s {$this->getEnvironment()} environment was successful.\n";
						$this->logMessage($message, NOTICE);
						return $db;
					}
					# Report Failure to auto-create database and Request a privileged user/password
					# TODO
				} // end !$wasError
			}
			
			# If access denied for user, request a priviledged user/password and exit (Over CLI or HTTPS only)
			elseif (stristr($errorMessage, 'Access denied for user')) {
				# If running from command line, just prompt the user for priviledged user/password and try to initialize..
				# TODO
				
				# If running from a browser, provide a web form to return priviledged user/password via HTTPS
				# TODO
				exit(0);
			}
			
			# If database connection failed for unrecoverable reason, report fatal error and exit
			else {
				$message = "A fatal error occured trying to connect to {$this->getApplication()}'s {$this->getEnvironment()} environment: $errorMessage.\n";
				$this->logMessage($message, FATAL);
				print "$message<br/>Support staff are being notified.";
				exit(0);
			}
		} // end catch
		
		# A database connection is properly established
		return $db;
	}
	
	// Get Create Database SQL for the User Registered for This Database
	private function getCreateDatabaseSQL( $param_database_name, $param_database_user, $param_database_password ) {
		$web_server_address      = 'localhost'; # TODO: determine this dynamically..  and how to make this apply to all frontend webservers?
		
		return <<<EndOfSQL
  
			CREATE DATABASE $param_database_name;
  
			GRANT ALL PRIVILEGES ON $param_database_name.* TO $param_database_user@$web_server_address IDENTIFIED BY '$param_database_password';
EndOfSQL;
	}
	
	// Get Number of Rows in Query
	public function getNumberOfRows($argSql) {
		// TODO
		return 0;
	}
	
	// Does the specified controller exist?
	public function isController( $param_controller = null) {
		if( $param_controller == null ) { $controller = $this->getControllerName; }
		else                            { $controller = $param_controller; }
		return file_exists( $this->getControllerFileName( $controller ) );
	}
	
	// Does the specified request of the specified controller exist?
	public function isRequest($param_controller, $param_request) {
		if(!$this->isController($param_controller)) {
			return false;
		}
		$registration = $this->getControllerRequests( $param_controller );
		if( !isset( $registration['requests'][$param_request] ) ) { return false; }
		else                                                      { return true; }
	}

	// Get list (numeric array) of controllers
	public function getListOfControllers( $param_version = null ) {
		if( $param_version == null ) { $version = $this->identity->version; }
		else                         { $version = $param_version; }
		// TODO: return array of controller names under version directory..
	}

	// Get registration for controller
	public function getControllerRequests( $param_controller = null ) {
		if( $param_controller == null ) { $controller = $this->getControllerName(); }
		else                            { $controller = $param_controller; }

		// If not loaded from file then load first..
		if( !isset( $this->registration[$controller] ) || !is_array( $this->registration[$controller] ) ) { $this->loadControllerRegistration( $param_controller ); }
		return $this->registration[$controller]['requests'];
	}

	public function getControllerTables( $param_controller = null ) {
		if( $param_controller == null ) { $controller = $this->getControllerName(); }
		else                            { $controller = $param_controller; }

		// If not loaded from file then load first..
		if( !isset( $this->registration[$controller] ) || !is_array( $this->registration[$controller] ) ) { $this->loadControllerRegistration( $param_controller ); }
		return $this->registration[$controller]['tables'];
	}

	private function loadControllerRegistration( $param_controller ) {
		$this->registration[ $param_controller ] = array();
		$registration_file_name = $this->getControllerRegistrationFileName( $param_controller );
		if( !file_exists( $registration_file_name ) ) { 
			print "Internal Error: Controller $param_controller's file $registration_file_name does not appear to exist.<br/>\n";
			return null; 
		}
		include( $registration_file_name ); // TODO: deal with any warning emitted.. 
		if( !isset( $requests ) || !is_array( $requests ) ) {
			// TODO: make proper error
			exit("ERROR: \"$registration_file_name\" did not properly assign \$requests!");
		}
		else {
			$this->registration[$param_controller]['requests'] = $requests;
			if( isset( $tables ) ) {
				$this->registration[$param_controller]['tables'] = $tables;
			}
			else { $this->reigstration[$param_controller]['tables'] = array(); }
		}
	}
	
	// Provide a unique ID for an unknown user (to track from here on out)
	public function getNewUnknownUser() {
		// TODO: Generic new unique identifier and register as unknown user
		return rand(0, 1000000);
	}
	
	// Get requested resource (css, javascript, image, etc)
	public function getResourceFile( $file_name, $controller ) {
		// Order of presidence: Environment should overrides application which overrrides controller.. (except for css, it's a concatenation order)
		$resource_type             = strtolower( substr( strrchr( $file_name, '.' ), 1 ) );
		$controller_resource_path  = $controller . '/resources/' . $file_name;
		$environment_resource_path = "../resources-{$this->identity->environment}/" . $file_name;
		$application_resource_path = '../resources/' . $file_name;

		//print "CWD: " . getcwd() . "<br/>\n";
		//print "Controller: [$controller_resource_path]<br/>\n";
		//print "Environment: [$environment_resource_path]<br/>\n";
		//print "Application: [$application_resource_path]<br/>\n";

		// To concatenate or supersede?
		if( $resource_type == 'css' ) {
			header('Content-Type: text/css; charset=UTF-8');
			// Concatenate controller + application + environment
			$found = false;
			$resource_content = "/* NOTE: Gathering Resource: {$file_name} *" . "/\n\n";
			if( file_exists( $controller_resource_path ) ) { 
				$found = true;
				$resource_content .= "/* NOTE -- Found \"$file_name\" at controller level (\"$controller_resource_path\") *" . "/\n" . file_get_contents( $controller_resource_path ) . "\n\n";; 
			}
			else { $resource_content .= "/* Resource \"$file_name\" does not exist at the controller level ($controller_resource_path). *" . "/\n"; }
			if( file_exists( $application_resource_path ) ) {
				$found = true;
				$resource_content .= "/* NOTE -- Found \"$file_name\" in the main global application area (\"$application_resource_path\"; this overrides the controller level) *" . "/\n" . file_get_contents( $application_resource_path ) . "\n\n";; 
			}
			else { $resource_content .= "/* Resource \"$file_name\" does not exist as in the main global application area ($application_resource_path). *" . "/\n"; }
			if( file_exists( $environment_resource_path ) ) {
				$found = true;
				$resource_content .= "/* NOTE -- Found \"$file_name\" in the {$this->identity->environment} environment's global application area (\"$environment_resource_path\"; this overrides the controller and main global application areas) *" . "/\n" . file_get_contents( $environment_resource_path ) . "\n\n";; 
			}
			else { $resource_content .= "/* Resource \"$file_name\" does not exist as in the {$this->identity->environment} environment's global application area ($environment_resource_path). *" . "/\n"; }
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
			print "Resource \"{$file_name}\" was not found.<br/>\n";
			$resource_content = '';  // TODO: perhaps insert error comment, according to $resource_type..
		}

		// Return directly to browser
		return $resource_content;
	}

	public function makeSingleLine( $text ) {
		return preg_replace( "/(\r\n|\n|\r)/s", ' ', $text );
	}
	

} // End of Framework Class
