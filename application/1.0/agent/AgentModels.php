<?php

# File: ~/1.0/user/UserDataModels.php
# Purpose: to provide a business process work management facility views

require_once('Models.php');

# Template for Construction of a Controller 
class AgentModels extends Models 
{
	// Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
	} 

	// Initialize User Tables
        public function initializeTables() {
		$this->buildTables(true); 
        }

	public function getFlows( $search_string = '', $is_template = false, $is_active = true ) {
	}

	public function newFlow( $title, $is_activated = false, $is_template = false ) {
		$is_activated = $is_activated ? 'true' : 'false';
		$is_template  = $is_template  ? 'true' : 'false';
		$sql = "INSERT INTO work_flows ( title, is_activated, is_template ) VALUES ( '$title', $is_activated, $is_template )";
	}

	public function updateFlow( $fields ) {
	}

	public function getFlow( $flow_id ) {
	}

	public function getTask( $task_id ) {
		$sql = "SELECT * FROM work_tasks WHERE task_id = $task_id";
	}

	public function newTask( $flow_id, $title, $description, $start_date, $due_date, $minutes_estimated, $notes = '', $hardlinked = null ) {
		$sql = "INSERT INTO work_tasks ( title, description, start_date, due_date, minutes_estimated, minutes_spent, notes, is_activated) VALUES ( '$title', '$description', '$start_date', '$due_date', $minutes_estimated, 0, '$notes' )";
		$sql = "INSERT INTO work_flow_tasks ( flow_id, task_id, hardlinked ) VALUES ( $flow_id, $task_id, $hardlinked )";
	}

	public function newTaskAssignee( $task_id, $user_id, $is_approver = false ) {
		$is_approver  = $is_approver  ? 'true' : 'false';
		$sql = "INSERT INTO work_task_assignees ( task_id, user_id, minutes_spent, is_approver ) VALUES ( $task_id, $user_id, $is_approver )";
	}

	public function updateTaskAssignee( $task_id, $user_id, $is_approver ) {
	}

	public function removeTaskAssignee( $task_id, $user_id ) {
	}

	public function updateTask( $fields ) {
	}

	public function addTaskTime( $minutes ) {
	}

	public function getTodo( $task_id ) {
		$sql = <<<EndOfSQL
			SELECT id, title, start_date, due_date
			FROM work_flows
				INNER JOIN work_task_assignee ON work_task_assignees.task_id = work_tasks.id
			WHERE work_task_assignees.user_id = $user_id AND work_tasks.is_activated = true
EndOfSQL;
	}

} // End of Class

