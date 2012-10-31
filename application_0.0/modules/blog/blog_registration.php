<?
# File: blog_registration.php
# 2012-10-21 ... Created.

$requests = array(
	'description' => 'Provides a multi-subject and multi-author blog.',
	'default' => '',
	'requests' => array(
		'view' => array(
			'description' => 'Provides a page with the most recent article shown and links to others provided, most recent first.',
			'parameters' => array(
				array( 'name' => 'tags',   'required' => false , 'default' => 'all', 'description' => 'Comma separated list of subjects to include (by default: all subjects).' ),
				array( 'name' => 'author', 'required' => false , 'default' => 'all', 'description' => 'Comma separated list of authors who\'s work to include (by default: all authors).' ),
			)
		),
		'new' => array(
			'description' => 'Provides article to edit and can save article received.',
			'parameters' => array(
				array( 'name' => 'title',   'required' => false , 'default' => '', 'description' => 'The viewable title of the article.' ),
				array( 'name' => 'article', 'required' => false , 'default' => '', 'description' => 'The text of the article.' ),
				array( 'name' => 'tags',    'required' => false , 'default' => '', 'description' => 'Comma separated list of subjects underwhich this article applies.' ),
				array( 'name' => 'publish', 'required' => false , 'default' => '', 'description' => 'Date upon which to publish (default is not to publish).' ),
			)
		),
		'edit' => array(
			'description' => 'Provides article to edit and can save article received.',
			'parameters' => array(
				array( 'name' => 'id',      'required' => false , 'default' => '', 'description' => 'For saving edits on existing articles, this is required to reference the article to overwrite.' ),
				array( 'name' => 'title',   'required' => false , 'default' => '', 'description' => 'The viewable title of the article.' ),
				array( 'name' => 'article', 'required' => false , 'default' => '', 'description' => 'The text of the article.' ),
				array( 'name' => 'tags',    'required' => false , 'default' => '', 'description' => 'Comma separated list of subjects underwhich this article applies.' ),
				array( 'name' => 'publish', 'required' => false , 'default' => '', 'description' => 'Date upon which to publish (default is not to publish).' ),
			)
		),
		'manage' => array(
			'description' => 'Provides a page for basic blog configuration parameters.',
			'parameters' => array(
				array( 'name' => 'title', 'required' => false , 'default' => '', 'description' => 'Title to display on top of the blog.' ),
				array( 'name' => 'name', 'required' => false , 'default' => '', 'description' => 'Short name for the blog.' ),
				array( 'name' => 'description', 'required' => false , 'default' => '', 'description' => 'Comma separated list of authors who\'s work to include (by default: all authors).' ),
				array( 'name' => 'commenting', 'required' => false , 'default' => 'Disallowed', 'description' => '"Disallowed", "Moderated", or "Unmoderated".' ),
			)
		),
		'moderate' => array(
			'description' => 'Enables or disables display of the specified message.',
			'parameters' => array(
				array( 'name' => 'message_id', 'required' => true , 'default' => null, 'description' => 'ID of the message in reference.' ),
				array( 'name' => 'publish', 'required' => true , 'default' => null, 'description' => 'Allow to be published: true or false.' ),
			)
		),
		'comment' => array(
			'description' => 'Adds a comment under the specified blog article.',
			'parameters' => array(
				array( 'name' => 'article_id', 'required' => false , 'default' => null, 'description' => 'Message ID to add this comment under (default is top-level).' ),
				array( 'name' => 'reply_to_id', 'required' => false , 'default' => null, 'description' => 'Message ID to add this comment under (default is top-level).' ),
				array( 'name' => 'title', 'required' => false , 'default' => '', 'description' => 'Title to display on top of the message.' ),
				array( 'name' => 'message', 'required' => false , 'default' => '', 'description' => 'The text of the message.' ),
				array( 'name' => 'tags', 'required' => false , 'default' => '', 'description' => 'Comma separated list of subjects that this comment applies to.' ),
			)
		),
	)
);

$tables = array(
	'blog_settings' => array(
		'id'          => array ( 'type' => 'INT(11)',       'key' => 'primary' ),
		'owner_id'    => array ( 'type' => 'INT(11)',       'default' => null,  'filter' => null, 'description' => 'User ID of the owner of this blog.' ),
		'title'       => array ( 'type' => 'VARCHAR(200)',  'default' => '',    'filter' => null, 'description' => 'Public title of the blog.' ),
		'name'        => array ( 'type' => 'VARCHAR(30)',   'default' => '',    'filter' => null, 'description' => 'Short title of the blog.' ),
		'description' => array ( 'type' => 'TEXT',          'default' => '',    'filter' => null, 'description' => 'Textual description of the blog.' ),
		'commenting'  => array ( 'type' => 'CHAR',          'default' => 'D',   'filter' => null, 'description' => 'Whether commenting is (D)isallowed, (M)oderated, or (U)nmoderated.' ),
	),
	'blog_articles' => array(
		'id'        => array ( 'type' => 'INT(11)',       'key' => 'primary' ),
		'author_id' => array ( 'type' => 'INT(11)',       'default' => null,  'filter' => null, 'description' => 'User ID of the author of this article.' ),
		'title'     => array ( 'type' => 'VARCHAR(200)',  'default' => '',    'filter' => null, 'description' => 'Public title of the article.' ),
		'article'   => array ( 'type' => 'TEXT',          'default' => '',    'filter' => null, 'description' => 'Text of the article.' ),
		'tags'      => array ( 'type' => 'VARCHAR(200)',  'default' => '',    'filter' => null, 'description' => 'Tags associated with the article.' ),
		'publish'   => array ( 'type' => 'DATETIME',      'default' => null,  'filter' => null, 'description' => 'When the article is to be published, or null for not scheduled.' ),
	),
	'blog_comments' => array(
		'id'          => array ( 'type' => 'INT(11)',       'key' => 'primary' ),
		'article_id'  => array ( 'type' => 'INT(11)',       'default' => null,  'filter' => null, 'description' => 'Identifies the article under which this comment is posted.' ),
		'under_id'    => array ( 'type' => 'INT(11)',       'default' => null,  'filter' => null, 'description' => 'ID of the comment this comment is in reply to (else null).' ),
		'author_id'   => array ( 'type' => 'INT(11)',       'default' => null,  'filter' => null, 'description' => 'User ID of the author of this article.' ),
		'title'       => array ( 'type' => 'VARCHAR(200)',  'default' => '',    'filter' => null, 'description' => 'Public title of the article.' ),
		'comment'     => array ( 'type' => 'TEXT',          'default' => '',    'filter' => null, 'description' => 'Text of the article.' ),
		'tags'        => array ( 'type' => 'VARCHAR(200)',  'default' => '',    'filter' => null, 'description' => 'Tags associated with the article.' ),
		'publish'     => array ( 'type' => 'DATETIME',      'default' => null,  'filter' => null, 'description' => 'When the article is to be published, or null for not scheduled.' ),
	),
);

