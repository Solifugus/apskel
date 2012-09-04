<?php

# File: ~/application_0.0/modules/user/user_views.php
# Purpose: provide views in support of the user module 

require_once('views.php');

# Class Declaration for Module's Views 
class UserViews extends Views
{
	# Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
		$this->loadViewTemplates(dirname(__FILE__));
	} 

	public function composeInitialize( $param_fields = array() ) {
		$environment = $this->framework->getEnvironment();

		// Establish parameter defaults 
		$messages       = 'Use this page to (re)initialize the user registry.';
		$warnings       = '';
		$super_user     = 'master';
		$super_forename = 'Master';
		$super_surname  = 'User';
		$super_email    = '';
		$database_user  = 'root';

		// Override parameter defaults
		extract( $param_fields );

		return <<<EndOfHTML
			<script type="text/javascript">
				function isPasswordMatch() {
					if( document.getElementById('super_password_once').value == document.getElementById('super_password_twice').value ) {
						document.getElementById('super_password').value = document.getElementById('super_password_once').value;
						return true;
					}
					else {
						document.getElementById('super_password_once').value  = '';
						document.getElementById('super_password_twice').value = '';
						document.getElementById('super_password').value       = '';
						document.getElementById('warnings').innerHTML = 'Passwords do not match.  Please re-enter them.';
						return false;
					}
				}

				function submitFormIfReady() {
					var ready = true;
					if( !isPasswordMatch() ) { ready = false; }
					if( ready ) {
						document.getElementById('user_initialize_form').submit();
					}
					else {
						return false;
					}
				}
			</script>
			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>	
			<div id="initialize_view" class="view_wrapper">
			<span id="title">Initialize User Registry</span><hr/>
			<form id="user_initialize_form" method="post" action="/user/initialize">
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">{$messages}</span><br/>
					<span id="warnings" class="warnings">{$warnings}</span><br/>
				</div>
				<hr/>
				<b>ATTENTION:</b> Understand that initializing the user registry will wipe out any and all existing users.
				After successfully initializing the user registry, a super user will be created (as specified below).   
				The new super user will be the only user until others are registered.<br/>
				<hr/>
				<b>Create a Super User</b><br/>
				<br/>
				<label class="label"  for="super_user">Super User:</label>	   <input name="super_user"           id="super_user"           class="input" value="{$super_user}"/><br/>
				<label class="label"  for="super_password_once">Password:</label>  <input name="super_password_once"  id="super_password_once"  class="input" type="password" /><br/>
				<label class="label"  for="super_password_twice">Password:</label> <input name="super_password_twice" id="super_password_twice" class="input" type="password" /><br/>
				<input name="super_password" id="super_password" type="hidden" value="" />
				<label class="label"  for="super_email">Email:</label>             <input name="super_email"          id="super_email"          class="input" value="{$super_email}" /><br/>
				<label class="label"  for="super_surname">Surname:</label>         <input name="super_surname"        id="super_surname"        class="input" value="{$super_surname}" /><br/>
				<label class="label"  for="super_forename">Forename:</label>       <input name="super_forename"       id="super_forename"       class="input" value="{$super_forename}"/><br/>
				<hr/>
				<b>Database User</b><br/>
				<br/>
				The credentials supplied hereunder must have privileges to create/drop tables in the $environment environment.  If not supplied, the database credentials for the $environment environment will be attempted.<br/>
				<br/>
				<label class="label"  for="database_user">User:</label>          <input name="database_user"     id="database_user"     class="input" value="{$database_user}"/><br/>
				<label class="label"  for="database_password">Password:</label>  <input name="database_password" id="database_password" class="input" type="password" /><br/>
				<br/>				
				<input type="hidden" name="fresh" id="fresh" value="false"/>
				<input class="button" id="initialize_submit" type="button" value="Initialize" onClick="submitFormIfReady();"/>
			</form>
			</div>
EndOfHTML;
	}

	public function composeLogin( $param_fields = array() ) {

		// Establish parameter defaults 
		$title     = 'Sign In';
		$user_name = '';
		$messages  = 'To sign in, enter your user name and password.';
		$warnings  = '';

		// Override parameter defaults
		extract( $param_fields );

		return <<<EndOfHTML
			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>	
			<div id="login_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
				<form method="post" action="/user/login">
					<label for="user_name" class="label">User Name: </label> <input name="user_name" id="user_name" class="input" value="{$user_name}"/><br/>
					<label for="password" class="label">Password: </label> <input name="password" id="password" class="input" type="password" /><br/>
					<input id="sign_in"   class="button" type="submit" value="Sign In"/><br/>
				</form>
				<a href="/user/register">Register</a>
			</div>
EndOfHTML;
	}

		public function composeRegister( $param_fields = array() ) {

		// Establish parameter defaults 
		$title     = 'New User Registration';
		$messages  = 'To register, enter and submit the following required fields.';
		$warnings  = '';

		// Override parameter defaults
		extract( $param_fields );

		return <<<EndOfHTML
		    <script type="text/javascript">
			  function isPasswordMatch() {
				  if( document.getElementById('password_once').value == document.getElementById('password_twice').value ) {
					  document.getElementById('password').value = document.getElementById('password_once').value;
					  return true;
				  }
				  else {
					  document.getElementById('password_once').value  = '';
					  document.getElementById('password_twice').value = '';
					  document.getElementById('password').value       = '';
					  document.getElementById('warnings').innerHTML = 'Passwords do not match.  Please re-enter them.';
					  return false;
				  }
			  }
				  function submitFormIfReady() {
				  var ready = true;
				  if( !isPasswordMatch() ) { ready = false; }
				  if( ready ) {
					  document.getElementById('profile_edit_form').submit();
				  }
				  else {
					  return false;
				  }
			  }
		  </script>

			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>	
			<div id="profile_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
				<form id="profile_edit_form" method="post" action="/user/register">
					<label for="user_name"      class="label">User Name: </label> <input name="user_name"      id="user_name"      class="input" value="{$user_name}"/><br/>
					<label for="email"          class="label">Email:     </label> <input name="email"          id="email"          class="input" value="{$email}" /><br/>
					<label for="forename"       class="label">Forename:  </label> <input name="forename"       id="forename"       class="input" value="{$forename}" /><br/>
					<label for="surname"        class="label">Surname:   </label> <input name="surname"        id="surname"        class="input" value="{$surname}" /><br/>
					<label for="password_once"  class="label">Password:  </label> <input name="password_once"  id="password_once"  class="input" type="password" /><br/>
					<label for="password_twice" class="label">Repeat:    </label> <input name="password_twice" id="password_twice" class="input" type="password" /><br/>
					<input type="hidden" name="password" id="password" value="" />
					<input type="hidden" name="fresh"    id="fresh"    value="false" />
					<input type="button" id="button_save" class="button" value="Submit" onClick="submitFormIfReady();" /><br/>
				</form>
			</div>
EndOfHTML;
	}

	
	public function composeEdit( $param_fields = array() ) {

		// Establish parameter defaults 
		$title     = 'User Profile Editor';
		$messages  = 'All user attributes for which editing is enabled, is available to edit below.  Changes are applied upon clicking the "Save" button';
		$warnings  = '';

		// Override parameter defaults
		extract( $param_fields );
		//$this->framework->showDebug( 'DEBUG', $param_fields );

		// TODO: add mechanism for viewing / editing custom attributes

		return <<<EndOfHTML
			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>	
			<div id="profile_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
				<form id="profile_edit_form" method="post" action="/user/edit">
					<label for="user_name"  class="label">User Name: </label> <input name="user_name" id="user_name" class="input" value="{$user_name}"/><br/>
					<label for="email"      class="label">Email:     </label> <input name="email"     id="email"     class="input" value="{$email}" /><br/>
					<label for="forename"   class="label">Forename:  </label> <input name="forename"  id="forename"  class="input" value="{$forename}" /><br/>
					<label for="surname"    class="label">Surname:   </label> <input name="surname"   id="surname"   class="input" value="{$surname}" /><br/>
					<input type="hidden" name="fresh" id="fresh" value="false" />
					<input type="button" id="button_save" class="button" value="Save" onClick="document.getElementById('profile_edit_form').submit();" /><br/>
				</form>
			</div>
EndOfHTML;
	}

	public function composeChange( $param_fields = array() ) {

		// Establish parameter defaults 
		$title     = 'Change Password';
		$messages  = 'Submit the following to change to a new password.';
		$warnings  = '';
		$user_name = '';

		// Override parameter defaults
		extract( $param_fields );

		// TODO: add mechanism for viewing / editing custom attributes

		return <<<EndOfHTML
			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>	
			<div id="profile_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
				To change your password, use the following:<br/>
				<form id="profile_edit_form" method="post" action="/user/password">
					<label for="old_password"  class="label">Old Password:  </label> <input name="old_password" id="old_password" class="input" type="password" /><br/>
					<label for="new_password"  class="label">New Password:  </label> <input name="new_password" id="new_password" class="input" type="password" /><br/>
					<label for="new_repeated"  class="label">New Repeated:  </label> <input name="new_repeated" id="new_password" class="input" type="password" /><br/>
					<input id="button_change"  class="button" type="submit" value="Apply Change"/><br/>
				</form>
			</div>
EndOfHTML;
	}

	public function composeRecover() {
		return <<<EndOfHTML
TODO: page to help recover lost user/password -- by mailing an activation code enabling automatic login
EndOfHTML;
	}

	public function composeDeactivate() {
		return <<<EndOfHTML
TODO: what to show after user deactivation.. 
EndOfHTML;
	}

	public function composeActivate() {
		return <<<EndOfHTML
TODO: what to show after user is activated and logged in..
EndOfHTML;
	}

} // End of UserView Class

