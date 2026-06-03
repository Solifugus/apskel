<?php

# File: ~/application_0.0/modules/developer/developer_views.php
# Purpose: views for the developer (web module-builder) module.
#
# NOTE: This file did not previously exist, so the developer module fataled on
# every request (its controller require_once'd a missing developer_views.php).
# These are minimal, honest placeholders: the web module-builder is not yet
# implemented -- the working tool is the `tools/build` CLI. They render plain,
# self-contained HTML (no SQL, no external template includes) so the module
# loads cleanly until the real builder UI is written.

require_once('views.php');

# Class Declaration for the developer Module's Views
class DeveloperViews extends Views {

	// Constructor
	public function __construct( $param_framework ) {
		parent::__construct();
		$this->framework = $param_framework;
	} // end of __construct

	// The module-definition page (placeholder).
	public function composeDefine( $param_fields = array() ) {
		$messages = '';
		$warnings = '';
		extract( $param_fields );

		return <<<EndOfHTML
<div id="developer_wrapper" class="view_wrapper">
	<span id="title" class="minor_title">Module Builder</span>
	<hr/>
	<span id="messages" class="messages">{$messages}</span>
	<span id="warnings" class="warnings">{$warnings}</span>
	<p>The web-based module builder is not yet implemented. For now, generate
	modules with the <code>tools/build</code> command-line tool from the project
	root.</p>
</div>
EndOfHTML;
	} // end of composeDefine

	// The build-result page (placeholder).
	public function composeBuild( $param_fields = array() ) {
		$messages = '';
		$warnings = '';
		extract( $param_fields );

		return <<<EndOfHTML
<div id="developer_wrapper" class="view_wrapper">
	<span id="title" class="minor_title">Module Builder</span>
	<hr/>
	<span id="messages" class="messages">{$messages}</span>
	<span id="warnings" class="warnings">{$warnings}</span>
	<p>Building modules from the web is not yet implemented. Use the
	<code>tools/build</code> CLI from the project root instead.</p>
</div>
EndOfHTML;
	} // end of composeBuild

} // end of DeveloperViews class
