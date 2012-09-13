<?php

# File: ~/application_0.0/modules/agent/agent_models.php
# Purpose: provide data access to the agent module
# 2012-09-12 ... created.

require_once('models.php');

# Class Declaration for agent Module's Models
class AgentModels extends Models
{
// Constructor
	public function __construct( $param_framework ) {
		$this->framework = $param_framework;
	} // end of __construct method

// Insert applicable model access methods in here..

} // end of AgentModels class
