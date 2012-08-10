<?php
# #####################################################
# ~/index.php -- initial reception for all web requests
# 
# 2011-04-02:MCT: created 
# 2011-04-29:MCT: modified to new directory scheme
# 2011-10-31:MCT: converted from raw functions to OO
# 2011-11-02:MCT: restructuring top level flow..  
# 2012-03-08:MCT: adding new environment loading code..

require_once('../environments/identification.php');
require_once('../application/framework.php');

$framework = new Framework();
echo $framework->getResource();

