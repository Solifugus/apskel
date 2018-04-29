<?php

// File: ~/environments/urls.php

// Get URL to Application, Environment, Version Mappings
$this->mappings = array();
$this->mappings[] = array( 'recognizer'=>'localhost', 'application'=>'ApskelSample', 'environment'=>'development', 'version'=>'0.0' );
$this->mappings[] = array( 'recognizer'=>'apskel.tst', 'application'=>'ApskelSample', 'environment'=>'test',        'version'=>'0.0' );
$this->mappings[] = array( 'recognizer'=>'apskel.com', 'application'=>'ApskelSample', 'environment'=>'production',  'version'=>'0.0' );

