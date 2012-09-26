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
		'import' => array(
			'description' => 'Imports the conversation script.',
			'parameters' => array(
				array( 'name' => 'script',  'required' => true ,  'default' => '',    'description' => 'The conversation script (currently only XML is supported).' ),
				array( 'name' => 'replace', 'required' => false , 'default' => 'all', 'description' => '"all" deletes all before import; "some" only replaces reactions of the specified meanings;' ),
			)
		),
		'export' => array(
			'description' => 'Exports the conversation script into the specified format.',
			'parameters' => array(
				array( 'name' => 'format',  'required' => false , 'default' => 'xml',  'description' => 'The notation exported to (currently only XML).' ),
				array( 'name' => 'wrapper', 'required' => false , 'default' => 'html', 'description' => 'In an HTML ("html") page for hand editing or a file ("file")?' ),
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
				array( 'name' => 'statement', 'required' => true ,  'default' => '',          'description' => 'The user statement.  This should consist of a string of visible text characters.' ),
				array( 'name' => 'return_as', 'required' => false , 'default' => 'interface', 'description' => 'The return format.  Options include "interface", "xml", or "text".' ),
				array( 'name' => 'topic',     'required' => false , 'default' => '',          'description' => 'The topic to converse on.' ),
				array( 'name' => 'passcode',  'required' => false , 'default' => '',          'description' => 'The passcode, required only if this is a private/secured topic.' ),
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
	'agent_meanings' => array(
		'id'         => array ( 'type' => 'INT(11)',      'key' => 'primary' ),
		'recognizer' => array ( 'type' => 'VARCHAR(200)', 'default' => null,    'filter' => null ),
		'length'     => array ( 'type' => 'INT(11)',      'default' => null,    'filter' => null, 'description' => 'Common name of the skill in reference.' ),
		'paradigm'   => array ( 'type' => 'CHAR(1)',      'default' => 'N',     'filter' => null, 'description' => 'Method used to select concurrently valid reactions: (N)atural, (C)yclic, or (R)andom.' ),
	),
	'agent_reactions' => array(
		'id'         => array ( 'type' => 'INT(11)',                 'key' => 'primary' ),
		'meaning_id' => array ( 'type' => 'INT(11)',                 'default' => null, 'filter' => null, 'description' => 'The meaning under which this reaction applies.' ),
		'priority'   => array ( 'type' => 'TINYINT',                 'default' => 0,    'filter' => null, 'description' => 'Order to use, given a non-false condition.' ),
		'functional' => array ( 'type' => 'ENUM(\'T\',\'F\',\'U\')', 'default' => 'U',  'filter' => null, 'description' => 'Is this reaction functional (tested and no errors found)--(T)rue, (F)alse, or (U)nknown?' ),
		'conditions' => array ( 'type' => 'VARCHAR(4000)',           'default' => '',   'filter' => null, 'description' => 'Textual condition coding to determine if this reaction is currently applicable.' ),
		'actions'    => array ( 'type' => 'VARCHAR(4000)',           'default' => '',   'filter' => null, 'description' => 'Textual action sequence coding to execute when this reaction is applied.' ),
	),
	'agent_reactions_used' => array(
		'id'          => array ( 'type' => 'INT(11)',  'key' => 'primary' ),
		'user_id'     => array ( 'type' => 'INT(11)',  'default' => null, 'filter' => null, 'description' => 'The ID of the user to whom this reaction use record refers.' ),
		'meaning_id'  => array ( 'type' => 'INT(11)',  'default' => null, 'filter' => null, 'description' => 'The meaning under which this reaction applies.' ),
		'reaction_id' => array ( 'type' => 'INT(11)',  'default' => null, 'filter' => null, 'description' => 'The reaction in reference.' ),
		'last_used'   => array ( 'type' => 'DATETIME', 'default' => null, 'filter' => null, 'description' => 'The date/time this reaction was last used for the specified user.' ),
	),
	'agent_memories' => array(
		'id'      => array ( 'type' => 'INT(11)', 'key' => 'primary' ),
		'memory'  => array ( 'type' => 'VARCHAR(4000)', 'default' => '', 'filter' => null, 'description' => 'Each memory is a simple textual statement itself.' ),
		'expires' => array ( 'type' => 'DATETIME', 'default' => null,    'filter' => null, 'description' => 'The date/time this memory disappears.  Null means at the end of the current session.' ),
	),
);
