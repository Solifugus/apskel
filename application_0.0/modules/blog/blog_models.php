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
	$fields['ownder_id'] = $user_id;
	$this->updateElseInsert( 'blog_settings', $fields, "owner_id = $user_id" );
}

public function getBlog( $user_id ) {
	$sql = "SELECT * FROM blog_settings WHERE owner_id = $user_id";
	$results = $this->framework->runSql( $sql );
	if( $results === null ) { 
		$results = array();
		$results['title'] = "{$_SESSION['user_name']}'s Blog";
		$results['name'] = $_SESSION['user_name'];
		$results['description'] = "A personal blog by {$_SESSION['user_name']}.";
		$results['commenting'] = 'Disallowed';
		$this->saveBlog( $user_id, $results );
	}
	return $results;
}

} // end of BlogModels class
