<?php

# File: ~/1.0/user/UserController.php
# Purpose: to provide a facility to manage users 

require_once('Controller.php');
require_once('user/UserModels.php');
require_once('user/UserViews.php');

# Template for Construction of a Controller 
class UserController extends Controller {
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
			return $this->views->getLogin( $param );
		}
		else {
			// Compose message indicating failure
			$is_bad_reason = "Initialization of the user registry failed: $is_bad_reason";
			$this->framework->logMessage( $is_bad_reason, WARNING );
		}

		if( $message > '' )       { $param['messages'] = $message; }
		if( $is_bad_reason > '' ) { $param['warnings'] = $is_bad_reason; }
		return $this->views->getInitialize( $param );
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
		if( strpos( $param_email, '@' ) === false ) { $result = false; } 
		return $result;
	}

	// *** Process Initialize Requests
	public function processAdd( $param ) {
	} // end of processAdd
	
	// *** Process Login Requests
	public function processLogin( $param, $missing ) {

		// Validate/Translate Input
		$user_name = '';
		$password  = '';

		extract( $param );
		if( !isset( $param['messages'] ) ) { $param['messages'] = ''; }
		if( !isset( $param['warnings'] ) ) { $param['warnings'] = ''; }
		$param['warnings'] .= $missing;

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
		else {
			$param['messages'] .= 'Enter your user name and password to login.';
		}

		// Show the login page
		return $this->views->getLogin( $param );
	}
	
	// *** Process Profile Requests (first time registration and later modifications)
	public function processProfile( $param, $missing ) {
		if( !isset( $param['messages'] ) ) { $param['messages'] = ''; } 
		if( !isset( $param['warnings'] ) ) { $param['warnings'] = ''; } 
		$param['warnings'] .= $missing;

		if( !isset( $param['user_name'] ) ) { $param['user_name'] = ''; }
		if( !isset( $param['email'] ) )    { $param['email']     = ''; }
		if( !isset( $param['surname'] ) )  { $param['surname']   = ''; }
		if( !isset( $param['forename'] ) ) { $param['forename']  = ''; }
		if( !isset( $param['password'] ) ) { $param['password']  = ''; }

		$authorized = false;
		if( ( isset( $param['user_name'] ) && $this->models->isSuperUser() ) || $param['action'] === 'register'  ) {
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
			return $this->views->getLogin( $param );
		}

		if( $authorized ) {
			// Update or get profile attributes
			$register = false;
			switch( strtolower( $param['action'] ) ) {
				case 'register':
					$register = true;
					break;
				case 'update':
					$this->models->saveUserDetails( $param );
					break;

				case 'show':
					break;

				default:
					$param['warnings'] .= "The \"{$param['action']}\" action is not recognized.";
			}

			if( $register === true ) {
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
					$is_bad_reason .= 'The user name chosen is already in use. ';  // TODO: compose specifically why not strong enough (using environment setting)
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
					$register = false; // Registration was successful so not registering any more..
				}


			}
			$param['register'] = $register;

			// If not a new registration then get profile recorded attributes..
			if( $register === false ) {
				$rows = $this->models->getUserDetails();
				foreach( $rows[0] as $field => $value ) {
					$param[$field] = $value;
				}
			}
			return $this->views->getProfile( $param );
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
	public function processChangePassword($param, $param_output_as) {
	}
	
	// *** Process Recover Access (Login / Password) Requests
	public function processRecoverAccess($param, $param_output_as) {
		return $this->views->recoverAccess();
	}
	
} // End of UserController Class
