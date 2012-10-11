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

// In ~/webroot or one above?
if( strpos( __FILE__, '/webroot/' ) === false ) { $path = ''; }
else { $path = '../'; }

// Determine How Request Arrived, Where To, and the Settings Thereof
require_once( "{$path}identity.php" );
$identity = new Identity();

// Process the Request
require_once("{$path}framework.php");
$framework = new Framework( $identity );
echo $framework->serviceRequest();

