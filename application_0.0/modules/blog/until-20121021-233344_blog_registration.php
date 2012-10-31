<?
# File: agent_registration.php
# 2012-09-12 ... Created.

$requests = array(
	'description' => 'Provides dimensional blogs.  A user may pick dimensions of interest and thereafter view only articles relating to them.',
	'default' => 'articles',
	'requests' => array(
		'view' => array(
			'description' => 'Provides a page with the most recent article shown and links to others provided, most recent first.',
			'parameters' => array(
				array( 'name' => 'subject', 'required' => false , 'default' => true, 'description' => 'Comma separated list of subjects to include (by default: all subjects).' ),
				array( 'name' => 'author',  'required' => false , 'default' => true, 'description' => 'Comma separated list of authors who\'s work to include (by default: all authors).' ),
			)
		),
		'edit' => array(
			'description' => 'Provides article to edit and can save article received.',
			'parameters' => array(
				array( 'name' => 'id',        'required' => false, 'default' => '', 'description' => 'For saving edits on existing articles, this is required to reference the article to overwrite.' ),
				array( 'name' => 'title',     'required' => false, 'default' => '', 'description' => 'The viewable title of the article.' ),
				array( 'name' => 'article',   'required' => false, 'default' => '', 'description' => 'The text of the article.' ),
				array( 'name' => 'subjects',  'required' => false, 'default' => '', 'description' => 'Comma separated list of subjects underwhich this article applies.' ),
				array( 'name' => 'publish',   'required' => false, 'default' => '', 'description' => 'Date upon which to publish (default is not to publish).' ),
			)
		),
		'manage' => array(
			'description' => 'Provides a page for basic blog configuration parameters.',
			'parameters' => array(
				array( 'name' => 'title',        'required' => false , 'default' => '',           'description' => 'Title to display on top of the blog.' ),
				array( 'name' => 'name',         'required' => false , 'default' => '',           'description' => 'Short name for the blog.' ),
				array( 'name' => 'description',  'required' => false , 'default' => '',           'description' => 'Comma separated list of authors who\'s work to include (by default: all authors).' ),
				array( 'name' => 'commenting',   'required' => false , 'default' => 'Disallowed', 'description' => '"Disallowed", "Moderated", or "Unmoderated".' ),
			)
		),
		'moderate' => array(
			'description' => 'Enables or disables display of the specified message.',
			'parameters' => array(
				array( 'name' => 'message_id', 'required' => true, 'default' => null, 'description' => 'ID of the message in reference.' ),
				array( 'name' => 'publish',    'required' => true, 'default' => null, 'description' => 'Allow to be published: true or false.' ),
			)
		),
		'comment' => array(
			'description' => 'Adds a comment under the specified blog article.',
			'parameters' => array(
				array( 'name' => 'article_id',   'required' => false , 'default' => null, 'description' => 'Message ID to add this comment under (default is top-level).' ),
				array( 'name' => 'reply_to_id',  'required' => false , 'default' => null, 'description' => 'Message ID to add this comment under (default is top-level).' ),
				array( 'name' => 'title',        'required' => false , 'default' => '',   'description' => 'Title to display on top of the message.' ),
				array( 'name' => 'message',      'required' => false , 'default' => '',   'description' => 'The text of the message.' ),
				array( 'name' => 'subjects',     'required' => false , 'default' => '',   'description' => 'Comma separated list of subjects that this comment applies to.' ),
			)
		),
	)
);

