<?php

# File: ~/application_0.0/modules/agent/agent_views.php
# Purpose: provide views for the agent module
# 2012-09-12 ... created.

require_once('views.php');

# Class Declaration for agent Module's views
class AgentViews extends Views
{
	// Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
		$this->loadViewTemplates(dirname(__FILE__));
	} // end of __construct method

	// View for the Initialize Request
	public function composeInitialize( $param_fields = array() ) {

		// Set parameter defaults;
		$title = 'agent Initialize';
		$messages = 'Welcome to the agent Initialize page.';
		$warnings = '';

		// Override parameter defaults;
		extract( $param_fields );

		// Compose and Output the View;
		return <<<EndOfHTML
			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>
			<div id="initialize_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
			</div>
EndOfHTML;
	} // end of composeInitialize view

	// View for the Interface Request
	public function composeInterface( $param_fields = array() ) {

		// Set parameter defaults;
		$title = 'agent Interface';
		$messages = 'Welcome to the agent Interface page.';
		$warnings = '';
		$type = '';
		$topic = '';
		$passcode = '';

		// Override parameter defaults;
		extract( $param_fields );

		// Compose and Output the View;
		return <<<EndOfHTML
			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>
			<div id="interface_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
			</div>
EndOfHTML;
	} // end of composeInterface view

	// View for the Converse Request
	public function composeConverse( $param_fields = array() ) {

		// Set parameter defaults;
		$title = 'agent Converse';
		$messages = 'Welcome to the agent Converse page.';
		$warnings = '';
		$statement = '';
		$return_as = '';
		$topic = '';
		$passcode = '';

		// Override parameter defaults;
		extract( $param_fields );

		// Compose and Output the View;
		return <<<EndOfHTML
			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>
			<div id="converse_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
			</div>
EndOfHTML;
	} // end of composeConverse view

	// View for the Create_topic Request
	public function composeCreateTopic( $param_fields = array() ) {

		// Set parameter defaults;
		$title = 'agent Create_topic';
		$messages = 'Welcome to the agent Create_topic page.';
		$warnings = '';
		$topic = '';
		$passcode = '';

		// Override parameter defaults;
		extract( $param_fields );

		// Compose and Output the View;
		return <<<EndOfHTML
			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>
			<div id="create_topic_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
			</div>
EndOfHTML;
	} // end of composeCreate_topic view

	// View for the Set_topic Request
	public function composeSetTopic( $param_fields = array() ) {

		// Set parameter defaults;
		$title = 'agent Set_topic';
		$messages = 'Welcome to the agent Set_topic page.';
		$warnings = '';
		$topic = '';
		$passcode = '';

		// Override parameter defaults;
		extract( $param_fields );

		// Compose and Output the View;
		return <<<EndOfHTML
			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>
			<div id="set_topic_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
			</div>
EndOfHTML;
	} // end of composeSet_topic view

	// View for the Get_reasoning Request
	public function composeGetReasoning( $param_fields = array() ) {

		// Set parameter defaults;
		$title = 'agent Get_reasoning';
		$messages = 'Welcome to the agent Get_reasoning page.';
		$warnings = '';

		// Override parameter defaults;
		extract( $param_fields );

		// Compose and Output the View;
		return <<<EndOfHTML
			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>
			<div id="get_reasoning_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
			</div>
EndOfHTML;
	} // end of composeGet_reasoning view

	// View for the Put_reasoning Request
	public function composePutReasoning( $param_fields = array() ) {

		// Set parameter defaults;
		$title = 'agent Put_reasoning';
		$messages = 'Welcome to the agent Put_reasoning page.';
		$warnings = '';
		$logic = '';
		$scope = '';

		// Override parameter defaults;
		extract( $param_fields );

		// Compose and Output the View;
		return <<<EndOfHTML
			<link rel="stylesheet" href="http://apskel.com/resources/user/main.css" type="text/css" media="screen"/>
			<div id="put_reasoning_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
				<br/>
			</div>
EndOfHTML;
	} // end of composePut_reasoning view

} // end of AgentViews class
