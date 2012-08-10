<?php

# File: ~/1.0/user/UserDataModels.php
# Purpose: to provide a business process work management facility views

require_once('Models.php');

# Template for Construction of a Controller 
class UserModels extends Models 
{
	// Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
	} 

	// Does user exist? 
	// @param $param_login -- required login name or ID
	public function isUserExist( $param_login ) {

		// Set where clause based on if login or ID given
		if(is_numeric( $param_login )) {
			$where = " id = $param_login ";
		} else {
			$param_login = addslashes( $param_login );
			$where = " user_name = '$param_login'";
		}

		// See if user exists or not
		$sql = "SELECT id, user_name FROM users WHERE $where";
		$results = $this->framework->runSql($sql);
		if( count( $results ) > 0 ) { return true; } else { return false; }
	}

	// Add User 
	// @param $param_login -- required login name
	// @param $param_email -- required email
	// @param $param_password -- optionally set the password here (blank will never be matched--so it's safe)
	// @return user's ID number
	public function addUser( $param_login, $param_email, $param_password = '' ) {
		$salt = $this->framework->getSetting('salt');
		if( !$this->isUserExist( $param_login ) ) {
                	if( $param_password !== '' ) { $param_password = md5( $salt . md5( $param_password ) ); }
			$sql = "INSERT INTO users ( user_name, password, email ) VALUES ('$param_login', '$param_password', '$param_email')";
			$this->framework->runSql($sql);
		} else { 
			// TODO: warning for trying to ad user that already exists..
		}
	}

	// Set User Details
	// @param $param_user    -- required user's login or ID number 
	// @param $param_details -- required associative array of user attributes to set
	public function saveUserDetails( $param_fields, $param_user = null ) {
		// Ensure we had the user ID and this is an authorized operation
		$authorized = false;
		if( $param_user === null ) { 
			if( isset( $_SESSION['user_id'] ) ) {
				$authorized = true;
				$user_id = $_SESSION['user_id'];
			}
		}
		else {
			if( is_numeric( $param_user ) ) { $user_id = $param_user; }
			else { $user_id = $this->getUserId( $param_user ); }
			if( $_SESSION['user_id'] != $user_id && $this->isSuperUser() === true ) { $authorized = true; } 
		}

		if( $authorized !== true ) {
			if( isset( $_SESSION['user_name'] ) ) { $current_user_name = $_SESSION['user_name']; }
			else { $current_user_name = '(unknown)'; }
			if( isset( $_SESSION['user_id'] ) )   { $current_user_id   = $_SESSION['user_id']; }
			else { $current_user_id = '(unknown)'; }
			$this->framework->logMessage( "The \"{$current_user_name}\" ({$current_user_id}) user tried to update user ID #{$user_id}) but was not authorized to do so. ", WARNING );
		}

		// Update any standard user attributes provided..
		$attributes = array( 'user_name', 'surname', 'forename', 'email' );
		$fields = array();
		foreach( $attributes as $attribute ) {
			if( isset( $param_fields[$attribute] ) ) {
				if( is_numeric( $param_fields[$attribute] ) ) {
					$fields[$attribute] = $param_fields[$attribute];
				}
				else { 
					$fields[$attribute] = "'{$param_fields[$attribute]}'"; 
				}
			}
		}
		if( count( $fields ) > 0 ) {
			$sql = $this->buildUpdateSql( 'users', $fields, "id = $user_id" );
			$result = $this->framework->runSql( $sql );
		}

		// Update or Insert custom user attributes
		foreach( $param_fields as $field => $value ) {
			if( substr($field, 0, 7) === 'custom_' ) { 
				$attribute = substr( $field, 6 );
				$update_field = array( 'attribute' => $attribute, 'value' => $value ); 
				$result = updateElseInsert( 'user_attributes', $update_field, "user_id = $user_id AND attribute = '$attribute'" ); 
			}
		}
	}

	// Get User Details
	// @param $param_user    -- required user's login or ID number 
	public function getUserDetails( $param_user = null ) {
		if( $param_user === null && isset( $_SESSION['user_name'] ) ) { $user_name = $_SESSION['user_name']; }
		else { $user_name = $param_user; }

		//if( $use
		//$user_name = strtolower($user_name);

		// Ensure user is allowed to receive this user's details
		// TODO

		// Get user details
		$table_prefix = $this->framework->getDatabaseName() . '.' . $this->framework->getDatabasePrefix();
		$sql = "SELECT * FROM {$table_prefix}users WHERE user_name = '{$user_name}';";
		$results = $this->framework->runSql( $sql );
		// TODO: add custom attributes
		return $results;
	}

	// Authenticate User via Password 
	// @param $param_user     -- required user's login or ID number 
	// @param $param_password -- the password to check against
	// #param $param_login    -- log the user in, if correct?
	public function isPasswordCorrect( $param_user, $param_password,  $param_login = false ) {
		$is_correct    = false;
		$table_prefix = $this->framework->getDatabaseName() . '.' . $this->framework->getDatabasePrefix();
                $salt         = $this->framework->getSetting('salt');
                if( $salt === null ) { $salt = $this->famework->getEnvironment(); }
		$user_name = strtolower($param_user);
                $password = md5( $salt . md5( $param_password ) );
		$sql = "SELECT id, user_name FROM {$table_prefix}users WHERE user_name = '$user_name' AND password = '$password';";
		$results = $this->framework->runSql($sql);
		if( count( $results ) === 1 ) {
			$is_correct = true;
			if( $param_login ) {
				$_SESSION['failed_logins'] = 0;
				$_SESSION['user_id']       = $results[0]['id'];
				$_SESSION['user_name']     = $results[0]['user_name'];
			}
		}
		else {
			// Did not equal 1 result -- Either no match or there are duplicates
			if( !isset( $_SESSION['failed_logins'] ) ) { $_SESSION['failed_logins'] = 0; }
			$_SESSION['failed_logins'] += 1;
			// TODO: add 3 strikes you're out logic..
		}
		return $is_correct;
	}

	public function isSuperUser() {
		return false;  // TODO
	}

	// Initialize User Tables
        public function initializeTables( $param_user, $param_password, $param_surname, $param_forename, $param_email, $param_database_user = null, $param_database_password = null ) {
                $user     = $param_user;
                $salt     = $this->framework->getSetting('salt');
                if( $salt === null ) { $salt = $this->famework->getEnvironment(); }
                $password = md5( $salt . md5( $param_password ) );
                $surname  = $param_surname;
                $forename = $param_forename;
                $email    = $param_email;

		$this->buildTables(true); 
                $sql = "INSERT INTO {$table_prefix}users ( user_name, password, surname, forename, email, super) VALUES ('$user', '$password', '$surname', '$forename', '$email', true);";
		$this->framework->runSql($sql);
		return; 

		/*
		$table_prefix = $this->framework->getDatabaseName() . '.' . $this->framework->getDatabasePrefix();

                $sql = <<<EndOfSQL
                DROP TABLE IF EXISTS {$table_prefix}users;
                CREATE TABLE {$table_prefix}users (
                        id         INTEGER not null auto_increment,
                        user_name  VARCHAR(15),           -- user reference for login and general display/use
			password   VARCHAR(32),           -- md5('salt' . md5('password')),
                        surname    VARCHAR(15),           -- family or main name
                        forename   VARCHAR(15),           -- given or sub-name
                        email      VARCHAR(60),           -- email through which to communicate with the user outside of this site
                        super      BOOLEAN DEFAULT false, -- is a super user?  true = yes; false = no
                        active     BOOLEAN DEFAULT true,  -- is an active user?  true = yes; false = no
                        PRIMARY KEY ( id )
                );

                INSERT INTO {$table_prefix}users ( user_name, password, surname, forename, email, super) VALUES ('$user', '$password', '$surname', '$forename', '$email', true);

                DROP TABLE IF EXISTS {$table_prefix}user_attributes;
                CREATE TABLE {$table_prefix}user_attributes (
                        id  INTEGER not null auto_increment,
                        user_id    INTEGER,      -- references the user
                        attribute  VARCHAR(15),  -- variable attribute name
                        value      TEXT,         -- variable attribute value
                        PRIMARY KEY ( id )
                );
EndOfSQL;
		$this->framework->runSql($sql);
		*/
        }

} // End of Class

