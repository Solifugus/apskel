<?php

# File: ~/application_0.0/modules/wiki/wiki_models.php
# Purpose: provide data access to the wiki module
# 2018-04-30 ... created.

require_once('models.php');

# Class Declaration for wiki Module's Models
class WikiModels extends Models
{
// Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
	} // end of __construct method

// Insert applicable model access methods in here..

} // end of WikiModels class
