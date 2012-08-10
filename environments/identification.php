<?php

// File: ~/environments/identification.php
// Purpse: to determine what environment is being called by DNS substring
// Description: 
//   The first listed recognizer that is a substring of the URL's address determines the environment  
//   and version.  For example, after fully testing version '2.0' of the 'testing' environment, you
//   can move that code to the 'evaluation' environment by setting the 'evaluation' environment's
//   version to '2.0'.

$identifications = array();
$identifications[] = array( 'recognizer'=>'workmosaic.dev', 'application'=>'WorkMosaic', 'environment'=>'development', 'version'=>'1.0' );
$identifications[] = array( 'recognizer'=>'workmosaic.stg', 'application'=>'WorkMosaic', 'environment'=>'staging',     'version'=>'1.0' );
$identifications[] = array( 'recognizer'=>'workmosaic.com', 'application'=>'WorkMosaic', 'environment'=>'production',  'version'=>'1.0' );

