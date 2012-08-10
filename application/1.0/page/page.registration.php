<?php

// Every module should have a registration file, such as this.
// The only requests that will be recognized under this controller are those registered herein.
// Any parameters not registered herein will be ignored.  If assigned a default of null, a 
// parameter will be required to call the request.  Otherwise, if not given in the request then
// the default herein will be presumed.  Also all values herein will be cleaned with addslashes() 
// prior to being passed to its request handler.

$this->application_controllers['page'] = array (
	'description' => 'The "page" controller provides a facility for creation, editing, and operation of a mosaic of web pages.  A textual notation and editor is provided for rapid creation of any mix of statis and dynamic pages through composition of sections and widgets.',
	'requests'    => array ( 
		'generate' => array (
			'description' => 'The "generate" request is for initial setup of the controller\'s data.  "clean" ensures there is an empty new space for storage or relevant recrods.  Other requests are for population of sample/test data.',
			'parameters' => array ('clean' => false)
		),
		'show'  => array (
			'description' => 'The "show" request presents and enables a page for use.',
			'parameters' => array('page' => null),
		),
		'edit'    => array (
			'description' => 'The "edit" request provides the pages editor for page creation, spliting, uniting, and editing.',
			'parameters' => array('pages' => '')
		)
	)
);

