<?php

# File: ~/1.0/workflow/WorkflowViews.php
# Purpose: to provide a business process work management facility views

require_once('Views.php');

# Template for Construction of a Controller 
class UserViews extends Views
{
	# Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
		$this->loadViewTemplates(dirname(__FILE__));
	} 

	public function getLogin( $param_fields = array() ) {

		// Establish parameter defaults 
		$title     = 'Sign In';
		$user_name = '';
		$messages  = 'Enter your user name and password to login.';
		$warnings  = '';

		// Override parameter defaults
		extract( $param_fields );

		return <<<EndOfHTML
			<link rel="stylesheet" href="http://workmosaic.com/resource/user/main.css" type="text/css" media="screen"/>	
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
				<a href="/user/profile/action=register">Register</a>
			</div>
EndOfHTML;
	}

	public function getNewPassword( $param_fields = array() ) {

		// Establish parameter defaults 
		$title     = 'User Profile';
		$messages  = 'You may change any of your user profile information, here.';
		$warnings  = '';
		$user_name = '';
		$email     = '';
		$forename  = '';
		$surname   = '';

		// Override parameter defaults
		extract( $param_fields );

		// TODO: add mechanism for viewing / editing custom attributes

		$css = $this->getCss();
		return <<<EndOfHTML
			$css
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
					<input id="button_change"  class="button" type="submit" value="Change"/><br/>
				</form>
			</div>
EndOfHTML;
	}

	public function getProfile( $param_fields = array() ) {

		// Establish parameter defaults 
		$title     = 'User Profile';
		$messages  = '';
		$warnings  = '';

		// Override parameter defaults
		extract( $param_fields );

		// TODO: add mechanism for viewing / editing custom attributes
		if( $register === true ) {
			$action = 'register';  // triggers what happens in controller..
			$get_password_javascript = <<<EndOfJavascript
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
EndOfJavascript;
			$get_password_html = <<<EndOfHTML
				<label class="label"  for="password_once">Password:</label>  <input name="password_once"  id="password_once"  class="input" type="password" /><br/>
				<label class="label"  for="password_twice">Repeat:</label> <input name="password_twice" id="password_twice" class="input" type="password" /><br/>
				<input name="password" id="password" type="hidden" value="" />
EndOfHTML;
		}
		else {
			$action = 'update';  // This is a profile view/update, not a registration
			$get_password_javascript = '';
			$get_password_html = '';
		}

		$css = $this->getCss();
		return <<<EndOfHTML
			$css
			$get_password_javascript
			<div id="profile_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
				<form id="profile_edit_form" method="post" action="/user/profile">
					<label for="user_name"  class="label">User Name: </label> <input name="user_name" id="user_name" class="input" value="{$user_name}"/><br/>
					<label for="email"      class="label">Email:     </label> <input name="email"     id="email"     class="input" value="{$email}" /><br/>
					<label for="surname"    class="label">Surname:   </label> <input name="surname"   id="surname"   class="input" value="{$surname}" /><br/>
					<label for="forename"   class="label">Forename:  </label> <input name="forename"  id="forename"  class="input" value="{$forename}" /><br/>
					$get_password_html
					<input type="hidden" name="action" id="action" value="$action" />
					<input type="button" id="button_save" class="button" value="Save" onClick="submitFormIfReady();" /><br/>
				</form>
			</div>
EndOfHTML;
	}

	public function getRecoverAccess() {
		return <<<EndOfHTML
TODO: page to help recover lost user/password
EndOfHTML;
	}

	public function getInitialize( $param_fields = array() ) {
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

		$css = $this->getCss();
		return <<<EndOfHTML
			$css
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
				<input class="button" id="initialize_submit" type="button" value="Initialize" onClick="submitFormIfReady();"/>
			</form>
			</div>
EndOfHTML;
	}

	public function getCss() {
		return <<<EndOfCSS
			<style type="text/css">
				body { background-color: #009999; }
				.view_wrapper { display: block; width: 640px; padding: 5px; border: 1px; border: solid 1px #85b1de; border-radius: 20px; margin-left: auto; margin-right: auto; background-color: #d6e5f4; font-family: Arial, Sans-Serif; font-size: 13px; }
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

} // End of UserView Class

