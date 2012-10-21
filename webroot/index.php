<?php
# #####################################################
# ~/index.php -- initial reception for all web requests
# 
# 2011-04-02:MCT: created 
# 2011-04-29:MCT: modified to new directory scheme
# 2011-10-31:MCT: converted from raw functions to OO
# 2011-11-02:MCT: restructuring top level flow..  
# 2012-03-08:MCT: adding new environment loading code..
# 2012-08-29:MCT: revised framework layout
# 2012-10-19:MCT: made work under ../webroot or one level up

// In ~/webroot or one above?
if( strpos( __FILE__, '/webroot/' ) === false ) { $path_offset = ''; }
else {
	$path_offset = '../';
	$path_offset_from_modules = '../../';
}

// Determine How Request Arrived, Where To, and the Settings Thereof
require_once( "{$path_offset}identity.php" );
$identity = new Identity();  // Note: changes working directory

// Process the Request
require_once("{$path_offset_from_modules}application_{$identity->version}/framework.php");
$framework = new Framework( $identity );
echo $framework->serviceRequest();

