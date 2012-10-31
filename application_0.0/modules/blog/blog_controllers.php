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

		// Perform View logic
		// TODO

		// Compose and Output the View;
		$format = array( 'format' => 'template', 'template_file' => 'view.html' );
		return array( $param, $format );
	} // end of processView controller

	// Handler for the Edit Request
	public function processNew( $param = array(), $missing = '' ) {

		// Set parameter defaults (in case any required ones are missing)
		$messages = '';
		$warnings = '';
		$title = '';
		$article = '';
		$tags = '';
		$publish = '';

		// Convert all request variables to local variables (except for any required by missing)
		extract( $param );

		// Unless a fresh visit to this page, show any missing parameters as warnings.
		if( $fresh !== true ) { $param['warnings'] .= $missing; }

		// Compose and Output the View;
		return $this->processEdit( $param );
	} // end of processEdit controller

	// Handler for the Edit Request
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

		// Perform Edit logic
		if( $id == '' ) {
			$param['page_title'] = 'New Article';
			$param['messages'] = 'Enter and save the new article.';
		}
		else {
			$param['page_title'] = 'Edit Article';
			$param['messages'] = 'Edit and save changes to the article.';
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
			$this->models->saveBlog( $_SESSION['user_id'], $param );
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
