<?php

# File: ~/application_0.0/modules/user/user_models.php
# Purpose: provide data access to the user module
#
# Security note: all queries below use prepared statements with bound
# parameters (Framework::runSql($sql, $params)). Passwords are stored and
# verified with PHP's password_hash()/password_verify() (bcrypt by default),
# never with the old salted-MD5 scheme.

require_once('models.php');

# Class Declaration for Module's Models
class UserModels extends Models
{
	// Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
	}

	// Fully-qualified table name (honors any configured table prefix).
	private function table( $name ) {
		return $this->framework->getDatabasePrefix() . $name;
	}

	// Interpret a value that may represent a database boolean (PDO/pgsql often yields 't'/'f').
	private function isTrue( $value ) {
		return $value === true || $value === 1 || $value === '1' || $value === 't' || $value === 'true';
	}

	// Does user exist?
	// @param $param_login -- required login name or ID
	public function isUserExist( $param_login ) {
		$users = $this->table( 'users' );
		if( is_numeric( $param_login ) ) {
			$sql    = "SELECT id FROM {$users} WHERE id = :id";
			$params = array( ':id' => $param_login );
		}
		else {
			$sql    = "SELECT id FROM {$users} WHERE LOWER(user_name) = LOWER(:user_name)";
			$params = array( ':user_name' => $param_login );
		}
		$results = $this->framework->runSql( $sql, $params );
		return is_array( $results ) && count( $results ) > 0;
	}

	// Get a user's numeric ID from a login name (or null if not found).
	public function getUserId( $param_login ) {
		if( is_numeric( $param_login ) ) { return $param_login; }
		$users = $this->table( 'users' );
		$results = $this->framework->runSql(
			"SELECT id FROM {$users} WHERE LOWER(user_name) = LOWER(:user_name)",
			array( ':user_name' => $param_login )
		);
		return ( is_array( $results ) && count( $results ) > 0 ) ? $results[0]['id'] : null;
	}

	// Add User
	// @param $param_login    -- required login name
	// @param $param_email    -- required email
	// @param $param_password -- optionally set the password here (blank leaves it unset, which can never match)
	// @return user's ID number, or null
	public function addUser( $param_login, $param_email, $param_password = '' ) {
		if( $this->isUserExist( $param_login ) ) {
			$this->framework->logMessage( "Attempt to add user \"{$param_login}\" who already exists.", WARNING );
			return null;
		}
		$hash  = ( $param_password !== '' ) ? password_hash( $param_password, PASSWORD_DEFAULT ) : null;
		$users = $this->table( 'users' );
		$results = $this->framework->runSql(
			"INSERT INTO {$users} ( user_name, password, email ) VALUES ( :user_name, :password, :email ) RETURNING id",
			array( ':user_name' => $param_login, ':password' => $hash, ':email' => $param_email )
		);
		return ( is_array( $results ) && count( $results ) > 0 ) ? $results[0]['id'] : null;
	}

	// Set User Details
	// @param $param_fields -- required associative array of user attributes to set
	// @param $param_user   -- optional user's login or ID (defaults to the logged-in user)
	public function saveUserDetails( $param_fields, $param_user = null ) {
		// Ensure we have the user ID and this is an authorized operation
		$authorized = false;
		$user_id    = null;
		if( $param_user === null ) {
			if( isset( $_SESSION['user_id'] ) ) {
				$authorized = true;
				$user_id    = $_SESSION['user_id'];
			}
		}
		else {
			$user_id = is_numeric( $param_user ) ? $param_user : $this->getUserId( $param_user );
			// A user may edit themselves; a super user may edit anyone.
			if( isset( $_SESSION['user_id'] ) && ( $_SESSION['user_id'] == $user_id || $this->isSuperUser() === true ) ) {
				$authorized = true;
			}
		}

		if( $authorized !== true || $user_id === null ) {
			$current_user_name = isset( $_SESSION['user_name'] ) ? $_SESSION['user_name'] : '(unknown)';
			$current_user_id   = isset( $_SESSION['user_id'] )   ? $_SESSION['user_id']   : '(unknown)';
			$this->framework->logMessage( "The \"{$current_user_name}\" ({$current_user_id}) user tried to update user ID #{$user_id} but was not authorized to do so.", WARNING );
			return false;
		}

		// Update any standard user attributes provided..
		$attributes  = array( 'user_name', 'surname', 'forename', 'email' );
		$assignments = array();
		$params      = array();
		foreach( $attributes as $attribute ) {
			if( isset( $param_fields[$attribute] ) ) {
				$assignments[]            = "{$attribute} = :{$attribute}";
				$params[":{$attribute}"]  = $param_fields[$attribute];
			}
		}
		if( count( $assignments ) > 0 ) {
			$params[':id'] = $user_id;
			$users = $this->table( 'users' );
			$this->framework->runSql(
				"UPDATE {$users} SET " . implode( ', ', $assignments ) . " WHERE id = :id",
				$params
			);
		}

		// Update or insert custom user attributes (field names prefixed with "custom_").
		$user_attributes = $this->table( 'user_attributes' );
		foreach( $param_fields as $field => $value ) {
			if( substr( $field, 0, 7 ) === 'custom_' ) {
				$attribute = substr( $field, 7 );
				$existing  = $this->framework->runSql(
					"SELECT id FROM {$user_attributes} WHERE user_id = :user_id AND attribute = :attribute",
					array( ':user_id' => $user_id, ':attribute' => $attribute )
				);
				if( is_array( $existing ) && count( $existing ) > 0 ) {
					$this->framework->runSql(
						"UPDATE {$user_attributes} SET value = :value WHERE user_id = :user_id AND attribute = :attribute",
						array( ':value' => $value, ':user_id' => $user_id, ':attribute' => $attribute )
					);
				}
				else {
					$this->framework->runSql(
						"INSERT INTO {$user_attributes} ( user_id, attribute, value ) VALUES ( :user_id, :attribute, :value )",
						array( ':user_id' => $user_id, ':attribute' => $attribute, ':value' => $value )
					);
				}
			}
		}
		return true;
	}

	// Get User Details
	// @param $param_user -- optional user's login or ID (defaults to the logged-in user)
	public function getUserDetails( $param_user = null ) {
		if( $param_user === null && isset( $_SESSION['user_name'] ) ) { $user_name = $_SESSION['user_name']; }
		else { $user_name = $param_user; }

		// TODO: ensure the current user is allowed to receive this user's details.

		$users = $this->table( 'users' );
		$results = $this->framework->runSql(
			"SELECT * FROM {$users} WHERE LOWER(user_name) = LOWER(:user_name)",
			array( ':user_name' => $user_name )
		);
		// TODO: add custom attributes from user_attributes.
		return $results;
	}

	// Authenticate User via Password
	// @param $param_user     -- required user's login (or ID)
	// @param $param_password -- the password to check
	// @param $param_login    -- log the user in, if correct?
	public function isPasswordCorrect( $param_user, $param_password, $param_login = false ) {
		$is_correct = false;
		$users      = $this->table( 'users' );
		$results    = $this->framework->runSql(
			"SELECT id, user_name, password FROM {$users} WHERE LOWER(user_name) = LOWER(:user_name)",
			array( ':user_name' => $param_user )
		);

		if( is_array( $results ) && count( $results ) === 1 && password_verify( $param_password, (string) $results[0]['password'] ) ) {
			$is_correct = true;
			if( $param_login ) {
				$_SESSION['failed_logins'] = 0;
				$_SESSION['user_id']       = $results[0]['id'];
				$_SESSION['user_name']     = $results[0]['user_name'];
			}
		}
		else {
			// Either no/duplicate match or a bad password.
			if( !isset( $_SESSION['failed_logins'] ) ) { $_SESSION['failed_logins'] = 0; }
			$_SESSION['failed_logins'] += 1;
			// TODO: add lockout ("three strikes") logic.
		}
		return $is_correct;
	}

	// Is the currently logged-in user a super user?
	public function isSuperUser() {
		if( !isset( $_SESSION['user_id'] ) ) { return false; }
		$users   = $this->table( 'users' );
		$results = $this->framework->runSql(
			"SELECT super FROM {$users} WHERE id = :id",
			array( ':id' => $_SESSION['user_id'] )
		);
		return ( is_array( $results ) && count( $results ) > 0 ) ? $this->isTrue( $results[0]['super'] ) : false;
	}

	// Logs user in -- irrespective of any validation
	public function login( $user_name ) {
		$user_id = $this->getUserId( $user_name );
		if( $user_id === null ) {
			$this->framework->logMessage( "login() called for unknown user \"{$user_name}\".", WARNING );
			return false;
		}
		// New privilege level -> new session id + CSRF token (defeats fixation).
		$this->framework->regenerateSession();
		$_SESSION['user_name'] = $user_name;
		$_SESSION['user_id']   = $user_id;
		return true;
	}

	public function logout( $user_name ) {
		if( isset( $_SESSION['user_name'] ) && $user_name == $_SESSION['user_name'] ) {
			unset( $_SESSION['user_name'] );
			unset( $_SESSION['user_id'] );
			$this->framework->regenerateSession();
			return true;
		}
		if( $this->isSuperUser() ) {
			// TODO: log out a user of another session..
			return true;
		}
		return false;
	}

	// Initialize User Tables (drops and recreates) and create the initial super user.
	public function initializeTables( $param_user, $param_password, $param_surname, $param_forename, $param_email, $param_database_user = null, $param_database_password = null ) {
		$this->buildTables( true );

		$hash  = password_hash( $param_password, PASSWORD_DEFAULT );
		$users = $this->table( 'users' );
		$this->framework->runSql(
			"INSERT INTO {$users} ( user_name, password, surname, forename, email, super, active )
			 VALUES ( :user_name, :password, :surname, :forename, :email, TRUE, TRUE )",
			array(
				':user_name' => $param_user,
				':password'  => $hash,
				':surname'   => $param_surname,
				':forename'  => $param_forename,
				':email'     => $param_email,
			)
		);
		return;
	}

} // End of Class
