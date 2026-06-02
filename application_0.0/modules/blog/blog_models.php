<?php

# File: ~/application_0.0/modules/blog/blog_models.php
# Purpose: provide data access to the blog module
# 2012-10-21 ... created.

require_once('models.php');

# Class Declaration for blog Module's Models
class BlogModels extends Models
{
// Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
	} // end of __construct method

public function saveBlog( $user_id, $fields ) {
	$fields = $this->framework->removeAllBut( array( 'title', 'name', 'description', 'commenting' ), $fields );
	// Upsert via the model keystone: owner_id identifies the row and is merged in
	// automatically on insert. Columns are validated against the blog_settings
	// registration and all values are bound.
	$this->setRecords( 'blog_settings', $fields, array( 'owner_id' => $user_id ), true );
}

public function getBlog( $user_id ) {
	$results = $this->getRecords( 'blog_settings', array( 'owner_id' => $user_id ) );
	if( empty( $results ) ) {
		$defaults = array(
			'title'       => "{$_SESSION['user_name']}'s Blog",
			'name'        => $_SESSION['user_name'],
			'description' => "A personal blog by {$_SESSION['user_name']}.",
			'commenting'  => 'D',  // (D)isallowed -- the commenting column is a single CHAR
		);
		$this->saveBlog( $user_id, $defaults );
		$defaults['owner_id'] = $user_id;
		return array( $defaults );
	}
	return $results;
}

} // end of BlogModels class
