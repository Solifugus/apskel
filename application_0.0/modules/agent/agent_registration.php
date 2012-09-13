<?
# File: agent_registration.php
# 2012-09-12 ... Created.

$requests = array(
	'description' => 'Provides a agent to communicate with conversationally on given topics, including the ability to create and/or modify new topics and reasoning.',
	'requests' => array(
		'initialize' => array(
			'description' => 'Sets up database tables for initial use of Workflow.',
			'parameters' => array(
			)
		),
		'interface' => array(
			'description' => 'This provides a web interface for conversing.',
			'parameters' => array(
				array( 'name' => 'type', 'required' => false , 'default' => 'user', 'description' => 'The types include "user" (ordinary user), "surrogate" (can take-over for agent), or "maintenance" (for post-use review).' ),
				array( 'name' => 'topic', 'required' => false , 'default' => '', 'description' => 'The topic to converse on.' ),
				array( 'name' => 'passcode', 'required' => false , 'default' => '', 'description' => 'The passcode, required only if this is a private/secured topic.' ),
			)
		),
		'converse' => array(
			'description' => 'This receives a user statement and returns a consequential response (statement and/or metadata).  Note that no response is one kind of valid response.',
			'parameters' => array(
				array( 'name' => 'statement', 'required' => true , 'default' => '', 'description' => 'The user statement.  This should consist of a string of visible text characters.' ),
				array( 'name' => 'return_as', 'required' => false , 'default' => 'interface', 'description' => 'The return format.  Options include "interface", "xml", or "text".' ),
				array( 'name' => 'topic', 'required' => false , 'default' => '', 'description' => 'The topic to converse on.' ),
				array( 'name' => 'passcode', 'required' => false , 'default' => '', 'description' => 'The passcode, required only if this is a private/secured topic.' ),
			)
		),
		'create_topic' => array(
			'description' => 'This creates a new topic.',
			'parameters' => array(
				array( 'name' => 'topic', 'required' => false , 'default' => '', 'description' => 'The topic to converse on.' ),
				array( 'name' => 'passcode', 'required' => false , 'default' => '', 'description' => 'The passcode, required only if this is a private/secured topic.' ),
			)
		),
		'set_topic' => array(
			'description' => 'This sets (changes) the topic.',
			'parameters' => array(
				array( 'name' => 'topic', 'required' => true , 'default' => '', 'description' => 'The topic to converse on.' ),
				array( 'name' => 'passcode', 'required' => false , 'default' => '', 'description' => 'The passcode, required only if this is a private/secured topic.' ),
			)
		),
		'get_reasoning' => array(
			'description' => 'Returns the reasoning logic for the session\'s previous statement response.',
			'parameters' => array(
			)
		),
		'put_reasoning' => array(
			'description' => 'This is to modify the conversation script through amalgamation (or replacement) of submitted reasoning logic.',
			'parameters' => array(
				array( 'name' => 'logic', 'required' => true , 'default' => '', 'description' => 'The logic to put into the knowledge base.' ),
				array( 'name' => 'scope', 'required' => false , 'default' => 'amalgamate', 'description' => 'To "amalgamate" or "replace" into the knowledge base.' ),
			)
		),
	)
);

$tables = array(
);
