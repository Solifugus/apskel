<?php

# File: ~/application_0.0/modules/user/user_controllers.php
# Purpose: controllers in support of the user module 

require_once('controllers.php');
require_once('user/user_models.php');
require_once('user/user_views.php');

# Class Declaration for Module's Controllers 
class UserControllers extends Controllers {
	# Constructor
	public function __construct($param_framework) {
		// Retain access to the framework
		$this->framework               = $param_framework;
		
		// Instantiate the associated model and view
		$this->models = new UserModels($this->framework);
		$this->views  = new UserViews($this->framework);
	} // end of __construct

	// *** Process Initialize Requests
	public function processInitialize( $param ) {
		$message            = '';
		$super_user         = $param['super_user'];
		$super_password     = $param['super_password'];
		$database_user      = $param['database_user'];
		$database_password  = $param['database_password'];
		$surname            = $param['super_surname'];
		$forename           = $param['super_forename'];
		$email              = $param['super_email'];

		if( $param['fresh'] !== true ) {
			// Validate inputs
			$is_bad = false;
			$is_bad_reason = '';
	
			if( !$this->isUserStrong( $super_user ) ) {
				$is_bad = true;
				$is_bad_reason .= 'The super user name provided is no good. ';  // TODO: compose specifically why not strong enough (using environment setting)
			}
	
			if( !$this->isPasswordStrong( $super_password ) ) {
				$is_bad = true;
				$is_bad_reason .= 'The super user password given was not strong enough. ';  // TODO: compose specifically why not strong enough (using environment setting)
			}
	
			if( !$this->isValidEmail( $email ) ) {
				$is_bad = true;
				$is_bad_reason .= 'The super user email given does not look valid. '; 
			}

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
				$message .= "Initialization of the user registry was successful. ";
				$this->framework->logMessage( $message, NOTICE );
				$param = array( 'messages' => "$message You may now login as \"$super_user\".", 'user_name' => $super_user );
				return $this->views->composeLogin( $param );
			}
			else {
				// Compose message indicating failure
				$is_bad_reason = "Initialization of the user registry failed: $is_bad_reason";
				$this->framework->logMessage( $is_bad_reason, WARNING );
			}

			if( $message > '' )       { $param['messages'] = $message; }
			if( $is_bad_reason > '' ) { $param['warnings'] = $is_bad_reason; }
		}
		return $this->views->composeInitialize( $param );
	}

	public function isUserStrong( $param_user ) {
		$result = true;
		if( strlen( $param_user ) < 6 ) { $result = false; }
		// TODO: add more rules and parameterize them at some point.. (use environment setting)
		return $result;
	}

	public function isPasswordStrong( $param_password ) {
		$result = true;
		if( strlen( $param_password ) < 8 ) { $result = false; } 
		// TODO: add more rules and parameterize them at some point.. (use environment setting)
		return $result;
	}

	public function isValidEmail( $param_email ) {
		$result = true;
		if( $param_email == '' ) { $result = false; }
		if( strpos( $param_email, '@' ) === false ) { $result = false; }  // TODO: use a proper regex for email
		return $result;
	}

	// *** Process Register Requests (create user + activation code, but leave inactive)
	public function processRegister( $param, $missing ) {
		//if( !isset( $param['messages'] ) ) { $param['messages'] = ''; } 
		if( !isset( $param['warnings'] ) ) { $param['warnings'] = ''; } 
		$param['warnings'] .= $missing;

		if( !isset( $param['user_name'] ) ) { $param['user_name'] = ''; }
		if( !isset( $param['email'] ) )    { $param['email']     = ''; }
		if( !isset( $param['surname'] ) )  { $param['surname']   = ''; }
		if( !isset( $param['forename'] ) ) { $param['forename']  = ''; }
		if( !isset( $param['password'] ) ) { $param['password']  = ''; }

		if( $param['fresh'] !== true ) {
			// If valid parameters exist, register the user..
			// Validate inputs
			$is_bad = false;
			$is_bad_reason = '';

			// Check for valid CAPTCHA
			// TODO

			if( !$this->isUserStrong( $param['user_name'] ) ) {
				$is_bad = true;
				$is_bad_reason .= 'The user name provided is no good. ';  // TODO: compose specifically why not strong enough (using environment setting)
			}

			if( !$this->isPasswordStrong( $param['password'] ) ) {
				$is_bad = true;
				$is_bad_reason .= 'The user password given was not strong enough. ';  // TODO: compose specifically why not strong enough (using environment setting)
			}
	
			if( $this->models->isUserExist( $param['user_name'] ) ) {
				$is_bad = true;
				$is_bad_reason .= 'The user name chosen is already in use. '; 
			}
	
			if( !$this->isValidEmail( $param['email'] ) ) {
				$is_bad = true;
				$is_bad_reason .= 'The user email given does not look valid. '; 
			}
	
			// If invalid registration then provide warnings as to why..
			if( $is_bad === true ) {
				$param['warnings'] .= $is_bad_reason;
			}
			else {
				// Validation is good so register the user!! Yeah!!
				$this->models->addUser( $param['user_name'], $param['email'], $param['password'] );
				$param['messages'] .= "The \"{$param['user_name']}\" user was successfully registered.";
				return "TODO: Ok, registered successfully.. where to go now?";
			}
		} // end of validating input (if other than fresh call to page)
		return $this->views->composeRegister( $param );
	}
	
	// Process Login Requests
	public function processLogin( $param, $missing ) {

		// Validate/Translate Input
		$user_name = '';
		$password  = '';

		extract( $param );
		//if( !isset( $param['messages'] ) ) { $param['messages'] = 'To sign in, enter your user name and password.'; }
		if( !isset( $param['warnings'] ) ) { $param['warnings'] = ''; }
		if( $param['fresh'] !== true ) { $param['warnings'] .= $missing; }

		// If user name and password given, try to login
		if( $user_name > '' && $password > '' ) {
			// If user/password are correct, go to appropriate page
			if( $this->models->isPasswordCorrect( $user_name, $password, true ) ) {
				$post_login_page = $this->framework->getSetting('post_login_page');
				$controller = $this->framework->getUriController( $post_login_page );
				$request    = $this->framework->getUriRequest( $post_login_page );
				$parameters = $this->framework->getUriParameters( $post_login_page );
				$parameters['messages'] = "Login as XX was successful.";
				return $this->framework->getView( $controller, $request, $parameters );
			} else {
				$param['warnings'] .= 'Authentication Failed. ';
			}
		}

		// Show the login page
		return $this->views->composeLogin( $param );
	}
	
	// *** Process Profile Requests (first time registration and later modifications)
	public function processEdit( $param, $missing ) {
		//if( !isset( $param['messages'] ) ) { $param['messages'] = ''; } 
		if( !isset( $param['warnings'] ) ) { $param['warnings'] = ''; } 
		$param['warnings'] .= $missing;

		if( !isset( $param['user_name'] ) ) { $param['user_name'] = ''; }
		if( !isset( $param['email'] ) )     { $param['email']     = ''; }
		if( !isset( $param['surname'] )  )  { $param['surname']   = ''; }
		if( !isset( $param['forename'] ) )  { $param['forename']  = ''; }
		if( !isset( $param['super'] ) )     { $param['super']     = ''; }
		if( !isset( $param['active'] ) )    { $param['active']    = ''; }
		if( !isset( $param['notes'] ) )     { $param['notes']     = ''; }

		$authorized = false;
		if( ( isset( $param['user_name'] ) && $this->models->isSuperUser() ) ) {
			// A super user is requesting a specified user's profile..
			$authorized = true;
		}
		elseif( isset( $_SESSION['user_name'] ) ) {
			if( isset( $param['user_name'] ) && $param['user_name'] > '' && $param['user_name'] != $_SESSION['user_name']) {
				// A non-super user is requesting someone else's profile..
				$param['warnings'] .= "You are not authorized to access \"{$param['user_name']}\"'s profile. ";
			}
			else {
				// The logged in user is requesting his own profile.. 
				$authorized = true;
				$param['user_name'] = $_SESSION['user_name'];
			}
		}
		else {
			// User is not even logged in
			$param['warnings'] .= 'You are not currently logged in and therefore cannot view any user profile.';
			return $this->views->composeLogin( $param );
		}

		if( $authorized ) {
			if( $param['fresh'] !== true ) {
				// TODO: some validation here..
				$this->models->saveUserDetails( $param );
				$param['messages'] = 'Changes were successfully saved.';
			}
			$details = $this->models->getUserDetails( $param['user_name'] );
			//$this->framework->showDebug( $details );
			$param['email']     = $details[0]['email'];
			$param['surname']   = $details[0]['surname'];
			$param['forename']  = $details[0]['forename'];
			$param['super']     = $details[0]['super'];
			$param['active']    = $details[0]['active'];
			$param['notes']     = $details[0]['notes'];
			return $this->views->composeEdit( $param );
		} 
		else {
			// Present not authorized warning, wait 5 seconds, and return to previous URL 
			$previous_url = $_SERVER['HTTP_REFERER']; 
			// TODO: make the following nicer..
			//header("Refresh: 5; URL=\"{$previous_url}\""); 
			//return "You are not authorized to view the user profile: {$param['warnings']}\n"; 
			return "You are not authorized to view the user profile: {$param['warnings']}\n<meta http-equiv=\"refresh\" content=\"5;URL=$previous_url\">"; 
		}
	}

	// *** Process Change Password Requests
	public function processChange($param, $missing) {
		// TODO : build this..
		return $this->views->composeChange( $param );
	}
	
	// *** Process Recover Access Requests (given email address, emails one-time use activation code so user can login to change password)
	public function processRecover($param, $missing) {
		// TODO: collect email address, put new activation code in user's notes field and mail to user
		return $this->views->composeRecover();
	}
	
	// *** Process Deactivate User Requests
	public function processDeactivate($param, $missing) {
		// TODO: If same user or super user, deactivate user
		return $this->views->composeDeactivate();
	}
	
	// *** Process Activate User Requests (activate and login user, if activation_code is correct (same as in user's notes field))
	public function processActivate($param, $missing) {
		// TODO: collect activation code and activate and login user, if correct (matching user's notes field)
		return $this->views->composeActivate();
	}
	
} // End of UserController Class
