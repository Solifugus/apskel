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

	// --- Articles -------------------------------------------------------------

	// Create or update an article. Updates require the caller to be its author
	// (a non-author or unknown id is refused). Returns the article id, or null.
	public function saveArticle( $user_id, $fields ) {
		$values = $this->framework->removeAllBut( array( 'title', 'article', 'tags', 'publish' ), $fields );
		// An empty publish date means "unscheduled" -> store NULL (not '').
		if( !isset( $values['publish'] ) || trim( (string) $values['publish'] ) === '' ) { $values['publish'] = null; }

		// Update path: only the author may overwrite an existing article.
		if( isset( $fields['id'] ) && is_numeric( $fields['id'] ) && (int) $fields['id'] > 0 ) {
			$existing = $this->getArticle( (int) $fields['id'] );
			if( $existing === null || $existing['author_id'] != $user_id ) { return null; }
			$this->setRecords( 'blog_articles', $values, array( 'id' => (int) $fields['id'], 'author_id' => $user_id ), false );
			return (int) $fields['id'];
		}

		// Insert path: stamp the author and add the row.
		$values['author_id'] = $user_id;
		return $this->addRecords( 'blog_articles', $values );
	}

	// One article (raw values) by id, with author_name attached, or null.
	public function getArticle( $id ) {
		if( !is_numeric( $id ) ) { return null; }
		$rows = $this->getRecords( 'blog_articles', array( 'id' => (int) $id ) );
		if( !is_array( $rows ) || count( $rows ) === 0 ) { return null; }
		$rows = $this->attachAuthors( $rows );
		return $rows[0];
	}

	// Published articles (publish set and not in the future), newest first, each
	// with author_name attached. With $include_unpublished, returns drafts too
	// (for an author managing their own work).
	public function getArticles( $include_unpublished = false ) {
		$rows = $this->getRecords( 'blog_articles', array(), array( 'order' => array( 'publish' => 'DESC', 'id' => 'DESC' ) ) );
		if( !is_array( $rows ) ) { return array(); }
		if( !$include_unpublished ) {
			$now = time();
			$published = array();
			foreach( $rows as $row ) {
				if( $row['publish'] !== null && trim( (string) $row['publish'] ) !== '' && strtotime( (string) $row['publish'] ) <= $now ) {
					$published[] = $row;
				}
			}
			$rows = $published;
		}
		return $this->attachAuthors( $rows );
	}

	// Resolve each row's author_id to an author_name from the users table (one
	// bound IN query). Rows keep their raw values; callers escape for output.
	private function attachAuthors( $rows ) {
		if( !is_array( $rows ) || count( $rows ) === 0 ) { return $rows; }
		$ids = array();
		foreach( $rows as $row ) {
			if( isset( $row['author_id'] ) && $row['author_id'] !== null ) { $ids[ (int) $row['author_id'] ] = true; }
		}
		$names = array();
		if( count( $ids ) ) {
			$users = $this->getRecords( 'users', array( 'id' => array_keys( $ids ) ), array( 'fields' => array( 'id', 'user_name' ) ) );
			foreach( $users as $user ) { $names[ (int) $user['id'] ] = $user['user_name']; }
		}
		foreach( $rows as $index => $row ) {
			$author_id = isset( $row['author_id'] ) ? (int) $row['author_id'] : 0;
			$rows[$index]['author_name'] = isset( $names[$author_id] ) ? $names[$author_id] : 'Unknown';
		}
		return $rows;
	}

} // end of BlogModels class
