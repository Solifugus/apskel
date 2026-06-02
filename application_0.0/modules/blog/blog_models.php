<?php

# File: ~/application_0.0/modules/blog/blog_models.php
# Purpose: provide data access to the blog module
# 2012-10-21 ... created.

require_once('models.php');

# Class Declaration for blog Module's Models
class BlogModels extends Models
{
	// The commenting column is a single CHAR; the manage form speaks whole words.
	// These maps translate between the two in saveBlog()/getBlog().
	private $commenting_to_code = array( 'Disallowed' => 'D', 'Moderated' => 'M', 'Unmoderated' => 'U' );
	private $commenting_to_word = array( 'D' => 'Disallowed', 'M' => 'Moderated', 'U' => 'Unmoderated' );

// Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
	} // end of __construct method

	// Normalize the human-facing commenting word ("Disallowed"/"Moderated"/"Unmoderated")
	// down to the single-character code the CHAR(1) column stores. Already-coded values
	// (D/M/U) pass through; anything unrecognized falls back to (D)isallowed.
	private function commentingCode( $value ) {
		$value = (string) $value;
		if( isset( $this->commenting_to_code[$value] ) ) { return $this->commenting_to_code[$value]; }
		if( isset( $this->commenting_to_word[$value] ) ) { return $value; }
		return 'D';
	}

	// Expand a stored single-character code back to its human-facing word for display.
	private function commentingWord( $code ) {
		$code = (string) $code;
		return isset( $this->commenting_to_word[$code] ) ? $this->commenting_to_word[$code] : 'Disallowed';
	}

public function saveBlog( $user_id, $fields ) {
	$fields = $this->framework->removeAllBut( array( 'title', 'name', 'description', 'commenting' ), $fields );
	// The manage form posts the commenting setting as a whole word; the column is a
	// single CHAR, so translate it before writing (an un-mapped word would otherwise
	// overflow the column and the upsert would fail).
	if( isset( $fields['commenting'] ) ) {
		$fields['commenting'] = $this->commentingCode( $fields['commenting'] );
	}
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
		$defaults['owner_id']   = $user_id;
		$defaults['commenting'] = $this->commentingWord( $defaults['commenting'] );
		return array( $defaults );
	}
	// Expand the stored code back to a word so the manage form shows the current setting.
	foreach( $results as $index => $row ) {
		if( isset( $row['commenting'] ) ) {
			$results[$index]['commenting'] = $this->commentingWord( $row['commenting'] );
		}
	}
	return $results;
}

} // end of BlogModels class
