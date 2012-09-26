<?php

# File: ~/application_0.0/modules/agent/agent_views.php
# Purpose: provide views for the agent module
# 2012-09-12 ... created.

require_once('views.php');

# Class Declaration for agent Module's views
class AgentViews extends Views
{
	// Constructor
	public function __construct( $param_framework ) {
		parent::__construct();
		$this->framework = $param_framework;
		$this->loadViewTemplates(dirname(__FILE__));
	} // end of __construct method

} // end of AgentViews class
