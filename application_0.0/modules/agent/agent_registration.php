<?
# File: agent_registration.php
# 2012-09-12 ... Created.

$requests = array(
	'description' => 'Provides a agent to communicate with conversationally, including the ability to create and/or modify new topics and interaction logic.',
	'requests' => array(
		'initialize' => array(
			'description' => 'Sets up database tables for initial use of the agent module.',
			'parameters' => array(
			)
		),
		'converse' => array(
			'description' => 'This receives a user statement and returns a consequential response (statement and/or metadata).  Note that no response is one kind of valid response.',
			'parameters' => array(
				array( 'name' => 'statement', 'required' => true ,  'default' => '',     'description' => 'The user statement.  This should consist of a string of visible text characters.' ),
				array( 'name' => 'return',    'required' => false , 'default' => 'html', 'description' => 'The return format.  Options include "interface", "xml", "json", or "text".' ),
				array( 'name' => 'editable',  'required' => false , 'default' => true,   'description' => 'If return_as=html, should it provide editing features?' ),
				array( 'name' => 'topic',     'required' => false , 'default' => '',     'description' => 'The topic to converse on.' ),
				array( 'name' => 'passcode',  'required' => false , 'default' => '',     'description' => 'The passcode, required only if this is a private/secured topic.' ),
			)
		),
		'meanings' => array(
			'description' => 'Provides an interface showing a directory of meanings, with allowed editing features.',
			'parameters' => array(
			)
		),
		'save_meaning' => array(
			'description' => 'Appends the new meaning to the database, else overwrites if already exists (but only those attributes that are provided).',
			'parameters' => array(
				array( 'name' => 'meaning_id', 'required' => false , 'default' => '', 'description' => 'The unique ID for the meaning to save (not needed, if a new meaning).' ),
				array( 'name' => 'recognizer', 'required' => false , 'default' => '', 'description' => 'The Meaning\'s recognizer (can substitute for meaning_id).' ),
				array( 'name' => 'paradigm',   'required' => false , 'default' => '', 'description' => 'The meaning\'s paradigm for how valid reactions are selected (natural, cyclic, or random)' ),
			)
		),
		'reactions' => array(
			'description' => 'Provides and interface for editing a particular meaning\'s possible reactions.',
			'parameters' => array(
				array( 'name' => 'meaning_id', 'required' => true , 'default' => '', 'description' => 'The ID of the meaning underwhich the reactions fall.' ),
			)
		),
		'save_reaction' => array(
			'description' => 'Appends new or overwrites old reaction (but only reaction attributes provided).',
			'parameters' => array(
				array( 'name' => 'meaning_id',  'required' => false , 'default' => '',         'description' => 'Required only for new reaction, since no reaction_id exists for it yet.' ),
				array( 'name' => 'reaction_id', 'required' => false , 'default' => '',         'description' => 'The unique ID of the reaction to save (appended if not provided--but then a meaning_id would be required)' ),
				array( 'name' => 'priority',    'required' => false , 'default' => '',         'description' => 'Priority for selection.' ),
				array( 'name' => 'functional',  'required' => false , 'default' => 'Untested', 'description' => 'Is the reaction functional? (False or Untested--if untested, a test will occur causing a change to True or False, accordingly)' ),
				array( 'name' => 'conditions',  'required' => false , 'default' => '',         'description' => 'Conditional logic to determine when the reaction is valid for use or not.' ),
				array( 'name' => 'actions',     'required' => false , 'default' => '',         'description' => 'Action sequence to perform if the reaction is selected.' ),
				array( 'name' => 'topic',       'required' => false , 'default' => '',         'description' => 'If populated, the reaction is only valid under the context of the given topic.' ),
			)
		),
		'topics' => array(
			'description' => 'Provides an interface to create/edit topics.',
			'parameters' => array(
				array( 'name' => 'passcode', 'required' => false , 'default' => '', 'description' => 'The passcode, if provided then shows or allows editing/creation of private/secured topics associated with this passcode.' ),
			)
		),
		'save_topic' => array(
			'description' => 'Appends new or overwrites old topic.',
			'parameters' => array(
				array( 'name' => 'topic',    'required' => true ,  'default' => '', 'description' => 'The topic\'s unique (up to 15 character) title.' ),
				array( 'name' => 'actions',  'required' => false , 'default' => '', 'description' => 'The action sequence that sets up the topic (typically a list of "remember" actions with an "interpret as" or "say" action at the end).' ),
				array( 'name' => 'passcode', 'required' => false , 'default' => '', 'description' => 'Associates the topic with this passcode, rendering it private/secure.' ),
			)
		),
		'import' => array(
			'description' => 'Imports (into the agent) the the specified meaning(s) and respective reaction set(s).',
			'parameters' => array(
				array( 'name' => 'script',  'required' => true ,  'default' => '',    'description' => 'The conversation script (currently only XML is supported).' ),
				array( 'name' => 'replace', 'required' => false , 'default' => 'all', 'description' => '"all" deletes all before import; "some" only replaces reactions of the specified meanings;' ),
			)
		),
		'export' => array(
			'description' => 'Exports (out from the agent) the referenced meaning(s) and respective reaction set(s) (interaction logic) in the format requested.',
			'parameters' => array(
				array( 'name' => 'meaning', 'required' => false , 'default' => '',     'description' => 'A user statement to match the meaning to export.  If not provided, all is returned.' ),
				array( 'name' => 'format',  'required' => false , 'default' => 'xml',  'description' => 'The notation exported to (currently only XML).' ),
				array( 'name' => 'wrapper', 'required' => false , 'default' => 'html', 'description' => 'In an HTML ("html") page for hand editing or a file ("file")?' ),
			)
		),
	)
);

$tables = array(
	'agent_topics' => array(
		'id'          => array ( 'type' => 'INT(11)',       'key' => 'primary' ),
		'title'       => array ( 'type' => 'VARCHAR(15)',   'default' => null,    'filter' => null, 'description' => 'URL-compatible label for the topic.' ),
		'description' => array ( 'type' => 'VARCHAR(4000)', 'default' => 'N',     'filter' => null, 'description' => 'Free-hand textual description for the topic.' ),
		'actions'     => array ( 'type' => 'TEXT',          'default' => 'N',     'filter' => null, 'description' => 'The actions required to topic (ideally, list of "remember" statements ending with an "interpret as" statement.' ),
	),
	'agent_meanings' => array(
		'id'         => array ( 'type' => 'INT(11)',      'key' => 'primary' ),
		'recognizer' => array ( 'type' => 'VARCHAR(200)', 'default' => null,    'filter' => null ),
		'length'     => array ( 'type' => 'INT(11)',      'default' => null,    'filter' => null, 'description' => 'Common name of the skill in reference.' ),
		'paradigm'   => array ( 'type' => 'CHAR(1)',      'default' => 'N',     'filter' => null, 'description' => 'Method used to select concurrently valid reactions: (N)atural, (C)yclic, or (R)andom.' ),
	),
	'agent_reactions' => array(
		'id'         => array ( 'type' => 'INT(11)',                 'key' => 'primary' ),
		'meaning_id' => array ( 'type' => 'INT(11)',                 'default' => null, 'filter' => null, 'description' => 'The meaning under which this reaction applies.' ),
		'topic'      => array ( 'type' => 'VARCHAR(15)',             'default' => '',   'filter' => null, 'description' => 'If specified, a topic to restrict the reaction to.' ),
		'priority'   => array ( 'type' => 'TINYINT',                 'default' => 0,    'filter' => null, 'description' => 'Order to use, given a non-false condition.' ),
		'functional' => array ( 'type' => 'ENUM(\'T\',\'F\',\'U\')', 'default' => 'U',  'filter' => null, 'description' => 'Is this reaction functional (tested and no errors found)--(T)rue, (F)alse, or (U)nknown?' ),
		'conditions' => array ( 'type' => 'TEXT',                    'default' => '',   'filter' => null, 'description' => 'Textual condition coding to determine if this reaction is currently applicable.' ),
		'actions'    => array ( 'type' => 'TEXT',                    'default' => '',   'filter' => null, 'description' => 'Textual action sequence coding to execute when this reaction is applied.' ),
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

