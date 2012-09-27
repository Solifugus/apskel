<?

$condition = array(
	'php_function'     => 'isQuantity',
	'regex_pattern'    => '/\(\s*is\s*([=><]?)\s*([0-9]+)\s*"([^"]*)"\s*\)/i',
	'display_pattern'  => '(IS {|>|<}n "..")'
);

$conditions = '(is >1 "The test and condition is on.") and not (is 0 "The test condition is on.") or true';
$conditions = preg_replace( $condition['regex_pattern'], '$this->isQuantity( \'${1}\', ${2}, "${3}" )', $conditions );
print "[$conditions]\n";

$english_logic = array( '/[^"](.*)and(.*)[^"]/i', '/[^"](.*)or(.*)[^"]/i', '/[^"](.*)not(.*)[^"]/i' );
$english_logic = array( '/([^"]*)and([^"]*)/i', '/([^"]*)or([^"]*)/i', '/([^"]*)not([^"]*)/i' );
$php_logic     = array( '${1}&&${2}', '${1}||${2}', '${1}!${2}' );
$conditions = preg_replace( $english_logic, $php_logic, $conditions );
print "[$conditions]\n";

