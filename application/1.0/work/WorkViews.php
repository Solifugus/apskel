<?php

# File: ~/1.0/work/WorkViews.php
# Purpose: to provide a business process work management facility views

require_once('Views.php');

# Template for Construction of a Controller 
class WorkViews extends Views
{
	# Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
		$this->loadViewTemplates(dirname(__FILE__));
	} 

	public function getTodo( $param = array() ) {

		// Establish parameter defaults 
		$title     = 'Tasks To Do';
		$messages  = 'The next X tasks due are listed below (next due first) and color coded by percentage of time left between begin and due dates (green = 50% or more, yellow = 25% or more, orange = less than 25%, and red = overdue).';
		$warnings  = '';

		// Override parameter defaults
		extract( $param );

		// Transform task list to HTML table notation
		// WORKING..

		return <<<EndOfHTML
			<link rel="stylesheet" href="http://workmosaic.com/resource/work/main.css" type="text/css" media="screen"/>	
			<div id="todo_view_wrapper" class="view_wrapper">
				<span id="title">$title</span><br/><hr/>
				<div id="message_area" class="message_area">
					<span id="messages" class="messages">$messages</span><br/>
					<span id="warnings" class="warnings">$warnings</span><br/>
				</div>
					<br/>
				<div id="todo_table"/>
					<table id="tasks_view" align="center" cellspacing="0" cellpadding="3">
						<tr id="title_row"><td colspan="2">Pending Tasks</td><td id="refresh_counter">X</td>
						<tr id="headings_row"><td>ID</td><td>Title</td><td>Due</td></tr>
						$task_listing
						<tr id="links_row"><td colspan="3"><a href="work/flow">Workflows</a> - <a href="workflow/reports">Reports</a></td></tr>
					</table>
				</div>
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

