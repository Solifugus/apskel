<?php

# File: ~/application_0.0/modules/blog/blog_controllers.php
# Purpose: provide controller logic for the blog module
# 2012-10-21 ... created.

require_once('controllers.php');
require_once('blog/blog_models.php');

# Class Declaration for blog Module's Controllers
class BlogControllers extends Controllers
{
	// Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;

		// Instantiate the associated model and view
		$this->models = new blogModels($this->framework);
	} // end of __construct method

	// Handler for the View Request
	public function processView( $param = array(), $missing = '' ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$tags = '';
		$author = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }
		if( !isset( $param['messages'] ) ) { $param['messages'] = ''; }

		// A specific article (?id=) or the published list, most recent first.
		if( isset( $param['id'] ) && is_numeric( $param['id'] ) && (int) $param['id'] > 0 ) {
			$one  = $this->models->getArticle( (int) $param['id'] );
			$rows = ( $one === null ) ? array() : array( $one );
		}
		else {
			$rows = $this->models->getArticles();
		}

		// Escape every user-supplied value for HTML output (the template engine
		// does not escape) and present as a list the view repeats per article.
		$param['articles'] = $this->renderableArticles( $rows );
		if( count( $param['articles'] ) === 0 ) {
			$param['messages'] = trim( $param['messages'] . ' No articles have been published yet.' );
		}

		// Compose and Output the View;
		$format = array( 'format' => 'template', 'template_file' => 'view.html' );
		return array( $param, $format );
	} // end of processView controller

	// Turn raw article rows into HTML-safe fields for the view template. The
	// framework's {{field}} substitution is a plain string replace (no escaping),
	// so all user content is escaped here to prevent stored XSS.
	private function renderableArticles( $rows ) {
		$articles = array();
		foreach( $rows as $row ) {
			$published = ( $row['publish'] !== null && trim( (string) $row['publish'] ) !== '' )
				? date( 'F j, Y', strtotime( (string) $row['publish'] ) ) : '';
			$articles[] = array(
				'id'          => (int) $row['id'],
				'title'       => htmlspecialchars( (string) $row['title'],   ENT_QUOTES ),
				'article'     => nl2br( htmlspecialchars( (string) $row['article'], ENT_QUOTES ) ),
				'tags'        => htmlspecialchars( (string) $row['tags'],    ENT_QUOTES ),
				'author_name' => htmlspecialchars( (string) ( isset( $row['author_name'] ) ? $row['author_name'] : '' ), ENT_QUOTES ),
				'publish'     => htmlspecialchars( $published, ENT_QUOTES ),
			);
		}
		return $articles;
	} // end of renderableArticles

	// Handler for the New Request -- a blank authoring form (delegates to Edit).
	public function processNew( $param = array(), $missing = '' ) {
		$param['id'] = '';  // force the "new article" path
		return $this->processEdit( $param, $missing );
	} // end of processNew controller

	// Handler for the Edit Request -- author/save a single article.
	public function processEdit( $param = array(), $missing = '' ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$id = '';
		$title = '';
		$article = '';
		$tags = '';
		$publish = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }
		if( !isset( $param['messages'] ) ) { $param['messages'] = ''; }

		// Authoring requires being logged in; bounce to login and return here.
		if( !isset( $_SESSION['user_id'] ) ) {
			$login = array( 'messages' => 'To write or edit an article, please log in.', 'next_page' => 'blog/edit' );
			return $this->framework->serviceRequest( 'user', 'login', $login );
		}

		// Persist only on an actual form submission (a plain GET just shows the form).
		if( $fresh !== true && $this->framework->isFormSubmission() ) {
			$saved_id = $this->models->saveArticle( $_SESSION['user_id'], $param );
			if( $saved_id === null ) {
				$param['warnings'] .= 'The article could not be saved (you may not be its author). ';
			}
			else {
				$param['messages'] .= 'Your article was saved. ';
				$param['id'] = $saved_id;
			}
		}

		// Pre-fill the form. Load an existing article the user owns, else echo back
		// whatever was just submitted. Either way values are HTML-escaped for the
		// form (browsers decode the entities again on submit, so edits round-trip).
		$existing = null;
		if( isset( $param['id'] ) && is_numeric( $param['id'] ) && (int) $param['id'] > 0 ) {
			$existing = $this->models->getArticle( (int) $param['id'] );
			if( $existing !== null && $existing['author_id'] != $_SESSION['user_id'] ) { $existing = null; }
		}
		if( $existing !== null ) {
			$param['id']         = (int) $existing['id'];
			$param['title']      = htmlspecialchars( (string) $existing['title'],   ENT_QUOTES );
			$param['article']    = htmlspecialchars( (string) $existing['article'], ENT_QUOTES );
			$param['tags']       = htmlspecialchars( (string) $existing['tags'],    ENT_QUOTES );
			$param['publish']    = htmlspecialchars( (string) $existing['publish'], ENT_QUOTES );
			$param['page_title'] = 'Edit Article';
		}
		else {
			$param['id']         = isset( $param['id'] ) ? $param['id'] : '';
			$param['title']      = htmlspecialchars( (string) ( isset( $param['title'] )   ? $param['title']   : '' ), ENT_QUOTES );
			$param['article']    = htmlspecialchars( (string) ( isset( $param['article'] ) ? $param['article'] : '' ), ENT_QUOTES );
			$param['tags']       = htmlspecialchars( (string) ( isset( $param['tags'] )    ? $param['tags']    : '' ), ENT_QUOTES );
			$param['publish']    = htmlspecialchars( (string) ( isset( $param['publish'] ) ? $param['publish'] : '' ), ENT_QUOTES );
			$param['page_title'] = 'New Article';
		}

		// Compose and Output the View;
		$format = array( 'format' => 'template', 'template_file' => 'edit.html' );
		return array( $param, $format );
	} // end of processEdit controller

	// Handler for the Manage Request
	public function processManage( $param = array(), $missing = '' ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages    = '';
		$warnings    = '';
		$title       = '';
		$name        = '';
		$description = '';
		$commenting  = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Manage logic
		if( !isset( $_SESSION['user_id'] ) ) {
			$param = array();
			$param['messages'] = 'To manage a blog, you must first be logged in.';
			$param['next_page'] = 'blog/manage';
			return $this->framework->serviceRequest( 'user', 'login', $param );
		}
		else {
			// Only persist on an actual form submission; a plain GET carries the
			// registration defaults (blank title/name, etc.) and would otherwise
			// overwrite the saved settings just by viewing the page.
			if( $this->framework->isFormSubmission() ) {
				$this->models->saveBlog( $_SESSION['user_id'], $param );
			}
			$blog = $this->models->getBlog( $_SESSION['user_id'] );
			$format = array( 'format' => 'template', 'template_file' => 'manage.html' );
		}

		// Compose and Output the View;
		return array( $blog, $format );
	} // end of processManage controller

	// Handler for the Moderate Request
	public function processModerate( $param = array(), $missing = '' ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$message_id = '';
		$publish = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Moderate logic
		// TODO

		// Compose and Output the View;
		$format = array( 'format' => 'template', 'template_file' => 'moderate.html' );
		return array( $param, $format );
	} // end of processModerate controller

	// Handler for the Comment Request
	public function processComment( $param = array(), $missing = '' ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$article_id = '';
		$reply_to_id = '';
		$title = '';
		$message = '';
		$tags = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Perform Comment logic
		// TODO

		// Compose and Output the View;
		$format = array( 'format' => 'template', 'template_file' => 'comment.html' );
		return array( $param, $format );
	} // end of processComment controller

} // end of BlogControllers class
