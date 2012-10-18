<?php

// File: ~/environments/urls.php

// Get URL to Application, Environment, Version Mappings
$this->mappings = array();
$this->mappings[] = array( 'recognizer'=>'apskel.dev', 'application'=>'ThinkMosaic', 'environment'=>'development', 'version'=>'0.0' );
$this->mappings[] = array( 'recognizer'=>'apskel.stg', 'application'=>'ThinkMosaic', 'environment'=>'staging',     'version'=>'0.0' );
$this->mappings[] = array( 'recognizer'=>'apskel.com', 'application'=>'ThinkMosaic', 'environment'=>'production',  'version'=>'0.0' );

