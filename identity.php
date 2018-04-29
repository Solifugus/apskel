<?php

// File: ~/environments/identification.php
// Purpse: to determine what environment is being called by DNS substring
// Description: 
//   The first listed recognizer that is a substring of the URL's address determines the environment  
//   and version.  For example, after fully testing version '2.0' of the 'testing' environment, you
//   can move that code to the 'evaluation' environment by setting the 'evaluation' environment's
//   version to '2.0'.
// 2012-08-30:MCT:converted to Identity class (from global array assignments)


class Identity {
	public $mappings;   // URL substring to application, environment, version mappings
	public $settings;   // environment settings assignments
	public $databases;  // registered databases
	public $rewrites;   // URL rewrite rules

	public $request_url;       // full URL
	public $request_protocol;  // protocol used (e.g. cli, http, or https)
	public $request_address;   // address used (e.g. domain name or IP address)
	public $request_path;      // path after the address (e.g. /organ/controller)
	public $request_query;     // query after the path

	public $link_back_module;  // URL for view links to point back to

	public $application;  // display name of the web application
	public $version;      // version of the application (e.g. 1.0)
	public $environment;  // environment of the application (e.g. development, staging, or production)

	// *** Constructor
	public function __construct() {
		global $path_offset;

		// Get URL to Application, Environment, Version Mappings
		require_once( "{$path_offset}urls.php" );

		// Get URL Components, Make Mappings, and Collect Appropriate Settings
		$this->determineUrlComponents();        // parses out request protocol, address, and query string
		$this->determineApplicationSettings();  // makes mapping and loads relative settings
	}

	// Set module required to compose link back URL
	public function setLinkBackModule( $module ) {
		$this->link_back_module = $module;
	}

	// Get URL for views to link back to
	public function getLinkBackUrl( $protocol = null, $module_name = null ) {
		if( $protocol    === null ) { $protocol    = $this->request_protocol; }
		if( $module_name === null ) { $module_name = $this->link_back_module; }
		return "{$protocol}://{$this->request_address}/{$module_name}";	
	}

	// Get URL for resources to link back to
	public function getResourcesUrl( $protocol = null, $module_name = null ) {
		if( $protocol    === null ) { $protocol    = $this->request_protocol; }
		if( $module_name === null ) { $module_name = $this->link_back_module; }
		return "{$protocol}://{$this->request_address}/resources/{$module_name}";	
	}

	// *** Get URL Components (whether from webserver or command line arguments)
	protected function determineUrlComponents() {
		// Get basic information on the current request	
		if (!isset($_SERVER['SERVER_NAME'])) {
			// Request is from the command line
			$this->request_protocol = 'cli';
			global $argv;
			if (count($argv) == 1) { 
				echo "\nRequests from the command line must include a full URL + query string (like in a browser's location bar)\n";
				echo "and may also include a chosen session ID.  For example:\n";
				echo "\tphp index.php 'abc.com/user/login?logon=myuser' 100\n\n";
				// TODO: end the request
				exit;
			} 

			// Get properly formed request URL from command line
			$argv[1] = trim($argv[1]);
			if (!preg_match('/^[A-Za-z]+:\/\/.*$/',$argv[1])) {
				$this->request_url = 'cli://' . $argv[1];
			} else {
				$this->request_url = $argv[1];
			}
		} else {
			// Request is from a browser
			//list($protocol, $other) = explode(':',$_SERVER['SCRIPT_URI'],2);
			list($protocol, $other) = explode('/',$_SERVER['SERVER_PROTOCOL'],2);
			$this->request_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];  
		}
		
		$this->request_protocol = preg_replace('/^([A-Za-z]+):\/\/.*$/',"$1",$this->request_url);
		$this->request_address  = preg_replace('/^.*?:\/\/([^\/]+).*$/',"$1",$this->request_url);
		$this->request_path     = trim(preg_replace('/^.*?:\/\/[^\/]+([^\?]+).*$/',"$1",$this->request_url),'/ ');
		$this->request_query    = preg_replace('/^[^\?]+\?(.*)$/',"$1",$this->request_url);

		//print "Protocol: {$this->request_protocol}<br/>\n";
		//print "Address: {$this->request_address}<br/>\n";
		//print "Path: {$this->request_path}<br/>\n";
		//print "Query: {$this->request_query}<br/>\n";
	}

	// *** Get Application Level Settings
	protected function determineApplicationSettings() {
		global $path_offset;

		// Identify Application Version and Environment
		foreach($this->mappings as $mapping) {
			$found = false;
			if( strpos( $this->request_address, $mapping['recognizer'] ) !== false ) { 
				$found = true;
				$this->application = $mapping['application'];
				$this->environment = $mapping['environment'];
				$this->version     = $mapping['version'];
				break;
			}
		}
		if( $found !== true ) {
			// TODO: what is appropriate to do if the environment isn't identified?
			print "Environment \"{$this->request_address}\" not known.."; exit;  // TODO: better thing to do here?
		}

		// Load Application Settings
		$path_to_settings = "{$path_offset}environments_{$this->version}/{$this->environment}_settings.php";
		if( file_exists( $path_to_settings ) ) { 
			require_once( $path_to_settings ); 
			$this->settings  = $settings;
			$this->databases = $databases;
			$this->rewrites  = $rewrites;
		}
		else { 
			// TODO: appropriate thing if settings.php file is missing..
			print "Error: the \"$path_to_settings\" file is missing.\n"; exit;  // TODO: log this error..
		}

		// Set Working Direction to Appropriate Code Base
		chdir( "{$path_offset}application_{$this->version}/modules" );
	}

}
