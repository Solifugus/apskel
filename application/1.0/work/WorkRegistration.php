<?php

// File: WorkRegistration.php
// This file registers the web requests supported by this controller.  This file defines and describes the controller's API.
// Any request parameter passed into a request, must be defined here.  If not provided, the defined default value is assigned but every parameter defined is
// guaranteed to be passed into the called request.  The values of each request parameter are also sanitized with the addslashes() function.
// The following request parameters are allowed through automatically, if provided but otherwise not set:
//   warnings  -- any warning messages to pass on to this request
//   messages  -- any regular messages to pass on to this request
//   return_to -- a page to configure to return to, after this request is handled
// After acquisition and sanitization, the request is called, passing the following parameters to it:
//   - an associative array of request parameters and their values
//   - a boolean value of whether or not all required parameters were provided
//   - a textual warning message for each missed required parameter, if any, otherwise '' (so controller can choose to show to user or not)

$requests = array(
	'description' => '',
	'requests'    => array(
		'initialize'  => array(
			'description' => 'Sets up database tables for initial use of Workflow.',
			'parameters' => array(
			)
		),
		'todo' => array(
			'description' => 'A user\s current ToDo list of work items.',
			'parameters' => array(
				array( 'name' => 'user', 'required' => false, 'default' => null, 'description' => 'User who\'s task list to access.  This default to the currently logged in user.' ),
				array( 'name' => 'max_tasks', 'required' => false, 'default' => 10, 'description' => 'Number of tasks to show at a time, at most.' ),
				array( 'name' => 'refresh_after', 'required' => false, 'default' => 15, 'description' => 'Number of seconds between each refresh.' ),
			)
		),
		'task' => array(
			'description' => 'View/edit details on a particular work item.',
			'parameters' => array(
				array( 'name' => 'action',  'required' => false, 'default' => 'show', 'description' => '"show" or "save" actions.' ),
				array( 'name' => 'task_id', 'required' => false, 'default' => null,   'description' => 'ID of the task.' ),
			)
		),
		'newtask' => array(
			'description' => 'Create a new task.  This returns the new task\'s ID.',
			'parameters' => array(
				array( 'name' => 'flow_id',           'required' => true,  'default' => null,  'description' => 'ID of the flow underwhich this task will exist.' ),
				array( 'name' => 'title',             'required' => false, 'default' => '',    'description' => 'The human readable title of the task.' ),
				array( 'name' => 'description',       'required' => false, 'default' => null,  'description' => 'The human readable description of the task.' ),
				array( 'name' => 'start_date',        'required' => false, 'default' => null,  'description' => 'Date/time (YYYY-MM-DD hh-mm-ss) upon which work may commence.  No date indicates immediately.' ),
				array( 'name' => 'due_date',          'required' => false, 'default' => null,  'description' => 'Date/time (YYYY-MM-DD hh:mm:ss) by which work is expected to be done.  No date indicates eternity.' ),
				array( 'name' => 'minutes_estimated', 'required' => false, 'default' => 0,     'description' => 'Minutes of work estimated to be required to do this work.' ),
				array( 'name' => 'notes',             'required' => false, 'default' => '',    'description' => 'Chronological list of notes relevant to this task.' ),
				array( 'name' => 'hardlinked',        'required' => false, 'default' => false, 'description' => 'If hardlinked, any templating will result in this task (not a copy) being linked to the new flow.' ),
			)
		),
		'flows' => array(
			'description' => 'Create or find a workflow and view or edit it.',
			'parameters' => array(
				array( 'name' => 'flow_id', 'required' => false, 'default' => -1, 'description' => 'Flow ID or -1 to create a new flow.' ),
			)
		),
		'newflow' => array(
			'description' => 'Create a new work flow.  This returns the flow\'s new ID.',
			'parameters' => array(
				array( 'name' => 'template_from_id',  'required' => false, 'default' => null,  'description' => 'To create from an existing template, specify the template\'s flow ID.' ),
				array( 'name' => 'title',             'required' => false, 'default' => '',    'description' => 'The human readable title for the new flow' ),
				array( 'name' => 'description',       'required' => false, 'default' => '',    'description' => 'The human readable description of the new flow' ),
				array( 'name' => 'activated',         'required' => false, 'default' => false, 'description' => 'Should the flow be initially activated?' ),
				array( 'name' => 'template',          'required' => false, 'default' => false, 'description' => 'Should the flow be a template or else an actual flow for direct use?' ),
			)
		),
		'reports' => array(
			'description' => 'Creaet or find a work report and view or edit it.',
			'parameters' => array(
			)
		),
	)
);

$tables = array(
	'work_user_skills' => array(
		'id'         => array ( 'type' => 'INT(11)', 'key' => 'primary' ),
		'user_id'    => array ( 'type' => 'INT(11)',     'default' => null, 'filter' => null ),
		'skill'      => array ( 'type' => 'VARCHAR(15)', 'default' => '',   'filter' => null, 'description' => 'Common name of the skill in reference.' ),
		'timeliness' => array ( 'type' => 'DOUBLE',      'default' => null, 'filter' => null, 'description' => 'Rate of how well deadlines are met (on the calendar).' ),
		'efficiency' => array ( 'type' => 'DOUBLE',      'default' => null, 'filter' => null, 'description' => 'Rate of time spent finishing work under this skill.' ),
		'quality'    => array ( 'type' => 'DOUBLE',      'default' => null, 'filter' => null, 'description' => 'Rate of work approved verses rejected.' ),
	),
	'work_flows' => array(
		'id'           => array ( 'type' => 'INT(11)', 'key' => 'primary' ),
		'title'        => array ( 'type' => 'VARCHAR(200)', 'default' => '',    'filter' => null, 'description' => 'The human readable title of this work flow.' ),
		'description'  => array ( 'type' => 'TEXT',         'default' => '',    'filter' => null, 'description' => 'The human readable description of this work flow.' ),
		'is_template'  => array ( 'type' => 'BOOLEAN',      'default' => false, 'filter' => null, 'description' => 'Is this workflow intended as a template to be instantiated from?' ),
		'is_activated' => array ( 'type' => 'BOOLEAN',      'default' => false, 'filter' => null, 'description' => 'A simple toggle to deem active or inactive. Work will be driven only while active.' ),
	),
	'work_flow_tasks' => array(
		'id'          => array ( 'type' => 'INT(11)', 'key' => 'primary' ),
		'flow_id'     => array ( 'type' => 'INT(11)',     'default' => null, 'filter' => null, 'description' => 'The workflow underwhich the task falls.' ),
		'task_id'     => array ( 'type' => 'INT(11)',     'default' => null, 'filter' => null, 'description' => 'The task that falls under the workflow.' ),
		'hardlinked'  => array ( 'type' => 'BOOLEAN',     'default' => false, 'filter' => null, 'description' => 'To reference the same task instance (true) or make a copy of it (false).' ),
	),
	'work_tasks' => array(
		'id'                => array ( 'type' => 'INT(11)', 'key' => 'primary' ),
		'title'             => array ( 'type' => 'VARCHAR(30)', 'default' => '',    'filter' => null, 'description' => 'A brief descriptive label for this task.' ),
		'description'       => array ( 'type' => 'TEXT',        'default' => '',    'filter' => null, 'description' => 'A full description of this task.' ),
		'start_date'        => array ( 'type' => 'DATETIME',    'default' => null,  'filter' => null, 'description' => 'Date/time the task is to begin, given all dependencies are met by then.' ),
		'due_date'          => array ( 'type' => 'DATETIME',    'default' => null,  'filter' => null, 'description' => 'Date/time the task is due for completion.' ),
		'minutes_estimated' => array ( 'type' => 'DOUBLE',      'default' => 0,     'filter' => null, 'description' => 'Estimated time required for completion, in minutes.' ),
		'minutes_spent'     => array ( 'type' => 'DOUBLE',      'default' => 0,     'filter' => null, 'description' => 'Time spent so far, in minutes.' ),
		'notes'             => array ( 'type' => 'TEXT',        'default' => '',    'filter' => null, 'description' => 'General comments that might be useful for the task.' ),
		'is_activated'      => array ( 'type' => 'BOOLEAN',     'default' => false, 'filter' => null, 'description' => 'Is this task activated? (as distinct from any particular flow)' ),
	),
	'work_task_assignees' => array(
		'id'             => array ( 'type' => 'INT(11)', 'key' => 'primary' ),
		'task_id'        => array ( 'type' => 'INT(11)', 'default' => null,  'filter' => null, 'description' => 'The task assigned to.' ),
		'user_id'        => array ( 'type' => 'INT(11)', 'default' => null,  'filter' => null, 'description' => 'The user assigned to the task.' ),
		'minutes_spent'  => array ( 'type' => 'DOUBLE',  'default' => 0,     'filter' => null, 'description' => 'Time this user has so far spent on the task, in minutes.' ),
		'is_approver'    => array ( 'type' => 'BOOLEAN', 'default' => false, 'filter' => null, 'description' => 'This person\'s approval is explicitly required before the task is considered complete.' ),
	),
	'work_task_resources' => array(
		'id'             => array ( 'type' => 'INT(11)', 'key' => 'primary' ),
		'task_id'        => array ( 'type' => 'INT(11)',      'default' => null, 'filter' => null, 'description' => 'A task to which this resource is associated.' ),
		'resource_type'  => array ( 'type' => 'VARCHAR(3)',   'default' => '',   'filter' => null, 'description' => 'A three character indicator of the kind of resource (TODO: list resource types here).' ),
		'reference'      => array ( 'type' => 'VARCHAR(255)', 'default' => '',   'filter' => null, 'description' => 'The URL or other text reference to the resource.' ),
	),
	'work_task_prerequisites' => array(
		'id'          => array ( 'type' => 'INT(11)', 'key' => 'primary' ),
		'task_id'     => array ( 'type' => 'INT(11)', 'default' => null, 'filter' => null, 'description' => 'The task requiring the other before it can start.' ),
		'required_id' => array ( 'type' => 'INT(11)', 'default' => null, 'filter' => null, 'description' => 'A task required to be completed before this task can start.' ),
	),
	'work_task_skills' => array(
		'id'     => array ( 'type' => 'INT(11)', 'key' => 'primary' ),
		'skill'  => array ( 'type' => 'VARCHAR(15)', 'default' => '', 'filter' => null, 'description' => 'Short textual label of this type of skill.' ),
	),
	'work_task_events' => array(
		'id'        => array ( 'type' => 'INT(11)', 'key' => 'primary' ),
		'task_id'   => array ( 'type' => 'INT(11)',   'default' => null, 'filter' => null, 'description' => 'The task under which this event occured.' ),
		'user_id'   => array ( 'type' => 'INT(11)',   'default' => null, 'filter' => null, 'description' => 'The user under whom this event occured.' ),
		'post_date' => array ( 'type' => 'DATETIME',  'default' => null,   'filter' => null, 'description' => 'The date/time this event occured.' ),
		'event'     => array ( 'type' => 'TEXT',      'default' => '',   'filter' => null, 'description' => 'A textual description of the event.' ),
	),
	'work_reports' => array(
		'id'        => array ( 'type' => 'INT(11)', 'key' => 'primary' ),
		'title'     => array ( 'type' => 'VARCHAR(200)', 'default' => '',    'filter' => null, 'description' => 'The label of the report.' ),
		'is_active' => array ( 'type' => 'BOOLEAN',      'default' => false, 'filter' => null, 'description' => 'The report is currently usable?' ),
		'query'     => array ( 'type' => 'TEXT',         'default' => '',    'filter' => null, 'description' => 'SQL for the report.' ),
	),
);

