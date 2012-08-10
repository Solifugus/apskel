<?php

# File: ~/1.0/work/WorkViews.php
# Purpose: to provide a business process work management facility views

require_once('Views.php');

# Template for Construction of a Controller 
class AgentViews extends Views
{
	# Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
		$this->loadViewTemplates(dirname(__FILE__));
	} 

	public function getInterface( $param = array() ) {

		// Establish parameter defaults 
		$title     = 'Agent Interface';
		$messages  = 'You may interact with the agent through normal English textual conversation.';
		$warnings  = '';

		// Override parameter defaults
		extract( $param );

		// Transform task list to HTML table notation
		// WORKING..

		return <<<EndOfHTML
			<link rel="stylesheet" href="http://workmosaic.com/resource/work/main.css" type="text/css" media="screen"/>	
			<div id="interface_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
					<br/>
				<div id="transcript"/>
				<div id="input_statement"/>
			</div>
EndOfHTML;
	}

	public function getTask( $param = array() ) {

		// Establish parameter defaults 
		if( !is_numeric( $param['task_id'] ) ) { $task_id = 'NEW'; }
		else { $task_id = $param['task_id']; }
		$title     = "Task - $task_id";
		$messages  = 'relevant message';
		$warnings  = '';

		// Override parameter defaults
		extract( $param );

		return <<<EndOfHTML
			<link rel="stylesheet" href="http://workmosaic.com/resource/work/main.css" type="text/css" media="screen"/>	
			<div id="todo_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
					<br/>
					WORKING..
			</div>
EndOfHTML;
	}

	public function getFlows( $param = array() ) {

		// Establish parameter defaults 
		$title     = 'Work Flow Designs';
		$messages  = 'relevant message';
		$warnings  = '';

		// Override parameter defaults
		extract( $param );

		return <<<EndOfHTML
			<link rel="stylesheet" href="http://workmosaic.com/resource/work/main.css" type="text/css" media="screen"/>	
			<div id="todo_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
					<br/>
					WORKING..
			</div>
EndOfHTML;
	}

} // End of UserView Class

