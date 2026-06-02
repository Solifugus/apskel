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
	$fields['owner_id'] = $user_id;
	$this->updateElseInsert( 'blog_settings', $fields, 'owner_id = :owner_id', array( ':owner_id' => $user_id ) );
}

public function getBlog( $user_id ) {
	$results = $this->buildSelect( 'blog_settings', '*', 'owner_id = :owner_id', array( ':owner_id' => $user_id ) );
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
