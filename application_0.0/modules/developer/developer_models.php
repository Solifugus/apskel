<?php

# File: ~/application_0.0/modules/developer/developer_models.php
# Purpose: model logic for the developer (web module-builder) module.
#
# NOTE: This file previously contained a verbatim copy of the agent module's
# models (it even declared "class AgentModels"), carrying the agent's legacy
# string-built / quoteForDatabase SQL into a second, routable module -- a
# duplicate SQL-injection surface and a class-name collision. The web
# module-builder was never actually implemented here (its real counterpart is
# the working `tools/build` CLI). The vulnerable copy has been excised and
# replaced with this clean, SQL-free stub so the module loads without fataling
# and exposes no injection surface. Implement the real builder later, on top of
# the parameterized helpers in modules/models.php.

require_once('models.php');

# Class Declaration for the developer Module's Models
class DeveloperModels extends Models {

	// Constructor
	public function __construct( $param_framework ) {
		parent::__construct();
		$this->framework = $param_framework;
	} // end of __construct

	// The web module-builder is not yet implemented. The working implementation
	// is the `tools/build` CLI; this method is a placeholder so callers have a
	// stable seam to build against later (it intentionally performs no SQL).
	// @return false (nothing built)
	public function buildModule( $version, $name, $description, $requests, $tables ) {
		$this->framework->logMessage( "developer/build requested for module \"{$name}\" but the web builder is not yet implemented (use the tools/build CLI).", NOTICE );
		return false;
	}

} // end of DeveloperModels class
