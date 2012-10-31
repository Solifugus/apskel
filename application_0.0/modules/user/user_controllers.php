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
	public function processInitialize( $param = array(), $missing = '' ) {
		$messages           = '';
		$super_user         = 'master';
		$super_password     = '';
		$database_user      = 'root';
		$database_password  = '';
		$surname            = 'Master';
		$forename           = 'User';
		$email              = '';

		extract( $param );

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
	
			if( !$this->isValidEmail( $super_email ) ) {
				$is_bad = true;
				$is_bad_reason .= "The super user email (\"{$super_email}\") given does not look valid. "; 
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
				$messages .= "Initialization of the user registry was successful. ";
				$this->framework->logMessage( $messages, NOTICE );
				$param = array( 'messages' => "$messages You may now login as \"$super_user\".", 'user_name' => $super_user );
				$format = array( 'format' => 'template', 'template_file' => 'login.html' );
				return array( $param, $format );
			}
			else {
				// Compose message indicating failure
				$is_bad_reason = "Initialization of the user registry failed: $is_bad_reason";
				$this->framework->logMessage( $is_bad_reason, WARNING );
			}
		}
		if( $messages > '' )          { $param['messages'] = $messages; }
		if( isset( $is_bad_reason ) ) { $param['warnings'] = $is_bad_reason; }

		$format = array( 'format' => 'template', 'template_file' => 'initialize.html' );
		return array( $param, $format );
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
	public function processRegister( $param = array(), $missing ) {
		$messages  = '';
		$warnings  = '';
		$user_name = '';
		$email     = '';
		$surname   = '';
		$forename  = '';
		$password  = '';

		extract( $param );

		if( $param['fresh'] !== true ) {
			$warnings .= $missing;
			// Validate inputs
			$is_bad = false;
			$is_bad_reason = '';

			// Check for valid CAPTCHA
			// TODO

			if( !$this->isUserStrong( $user_name ) ) {
				$is_bad = true;
				$is_bad_reason .= 'The user name provided is no good. ';  // TODO: compose specifically why not strong enough (using environment setting)
			}

			if( !$this->isPasswordStrong( $password ) ) {
				$is_bad = true;
				$is_bad_reason .= 'The user password given was not strong enough. ';  // TODO: compose specifically why not strong enough (using environment setting)
			}
	
			if( $this->models->isUserExist( $user_name ) ) {
				$is_bad = true;
				$is_bad_reason .= 'The user name chosen is already in use. '; 
			}
	
			if( !$this->isValidEmail( $email ) ) {
				$is_bad = true;
				$is_bad_reason .= 'The user email given does not look valid. '; 
			}
	
			// If invalid registration then provide warnings as to why..
			if( $is_bad === true ) {
				$warnings .= $is_bad_reason;
			}
			else {
				// Validation is good so register the user!! Yeah!!
				$this->models->addUser( $user_name, $email, $password );
				$messages .= "The \"{$user_name}\" user was successfully registered.";
				return "TODO: Ok, registered successfully.. where to go now?";
			}
		} // end of validating input (if other than fresh call to page)
		$param = array( 
			'messages' => $messages,
			'warnings' => $warnings,
			'user_name' => $user_name,
			'email'     => $email,
			'surname'   => $surname,
			'forename'  => $forename
		);
		$format = array( 'format' => 'template', 'template_file' => 'register.html' );
		return array( $param, $format );
	}
	
	// Process Login Requests
	public function processLogin( $param, $missing ) {

		// Validate/Translate Input
		$user_name = '';
		$password  = '';
		$next_page = '';

		extract( $param );
		if( $next_page == '' ) { $next_page = $this->framework->getSetting('post_login_page'); }
		if( !isset( $param['warnings'] ) ) { $param['warnings'] = ''; }
		if( $param['fresh'] !== true ) { $param['warnings'] .= $missing; }

		// If user name and password given, try to login
		if( $user_name > '' && $password > '' ) {
			// If user/password are correct, go to appropriate page
			if( $this->models->isPasswordCorrect( $user_name, $password, true ) ) {
				$this->models->login( $user_name );
				$module            = $this->framework->getUriModule( $next_page );
				$request           = $this->framework->getUriRequest( $next_page );
				$param             = $this->framework->getUriParameters( $next_page );
				$param['messages'] = "Login as \"{$user_name}\" was successful.";
				return $this->framework->serviceRequest( $module, $request, $param );
			} else {
				$param['warnings'] .= 'Authentication Failed. ';
			}
		}

		// Show the login page
		$format = array( 'format' => 'template', 'template_file' => 'login.html' );
		return array( $param, $format );
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
			$format = array( 'format' => 'template', 'template_file' => 'edit.html' );
			return array( $param, $format );
		} 
		else {
			// Present not authorized warning, wait 5 seconds, and return to previous URL 
			$previous_url = $_SERVER['HTTP_REFERER']; 
			// TODO: make the following nicer..
			//header("Refresh: 5; URL=\"{$previous_url}\""); 
			//return "You are not authorized to view the user profile: {$param['warnings']}\n"; 
			//return "You are not authorized to view the user profile: {$param['warnings']}\n<meta http-equiv=\"refresh\" content=\"5;URL=$previous_url\">"; 
			$param['warnings'] .= "You are not authorized to view the user profile."; 
			$format = array( 'format' => 'template', 'template_file' => 'warning.html' );
			return array( $param, $format );
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

	// *** Process to Log User Out 
	public function processLogout( $param, $missing ) {
		$messages  = '';
		$warnings  = '';
		$user_name = '';

		extract( $param );

		$logged_out = false;

		// If no user name provided but a user is logged in, presume that user..
		if( $user_name == '' ) {
			if( isset( $_SESSION['user_name'] ) ) {
				$user_name = $_SESSION['user_name'];
			}
		}

		// If the user is currently logged in session user..
		if( $user_name == $_SESSION['user_name'] ) {
			$logged_out = $this->models->logout( $user_name );
		}
		// If the user is not currently logged in session user.. 
		else {
			if( $this->models->isSuperUser() ) {
				$logged_out = $this->models->logout( $user_name );
			}	
			else {
				$warnings .= "The currently logged in user is not allowed to log out a different user.  ";
				// TODO: log this..
			}
		}

		if( $logged_out ) {
			$messages .= "The \"{$user_name}\" user is logged out.  ";
			$format    = array( 'format' => 'template', 'template_file' => 'messages.html' );
		}
		else {
			$warnings .= "The user was not logged out.  ";
			$format    = array( 'format' => 'template', 'template_file' => 'messages.html' );
		}
		$param = array( 'messages' => $messages, 'warnings' => $warnings );
		//return array( $param, $format );
		return $this->framework->serviceRequest( 'user', 'login', $param );
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
