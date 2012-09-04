#!/usr/bin/php

<?php


// Global Color Codes for BASH 
$BLACK       = "\033[0;30m";
$DARKGRAY    = "\033[1;30m";
$BLUE        = "\033[0;34m";
$LIGHTBLUE   = "\033[1;34m";  // current relevant emphasis text
$GREEN       = "\033[0;32m";  // standard text
$LIGHTGREEN  = "\033[1;32m";
$CYAN        = "\033[0;36m";
$LIGHTCYAN   = "\033[1;36m";
$RED         = "\033[0;31m";  // error text
$LIGHTRED    = "\033[1;31m";
$PURPLE      = "\033[0;35m";
$LIGHTPURPLE = "\033[1;35m";
$BROWN       = "\033[0;33m";
$YELLOW      = "\033[1;33m";  // user entered text
$LIGHTGRAY   = "\033[0;37m";
$WHITE       = "\033[1;37m";

// Global Data
$module_name = '_module_';                                        // name of the new module to build
/*
$requests = array( 'description' => '', 'requests' => array() );  // initially blank controller design
$tables = array();                                                // initially blank database design
*/

require_once( '../application_0.0/modules/_module_/_module_registration.php' );

mainScreen();

function mainScreen() {
	global $GREEN, $LIGHTBLUE, $YELLOW, $RED;
	global $module_name;
	$message     = "To build a new module, ensure you have named it, designed its controllers and\nits database.  Then use the build option and finally quit.  No changes are\napplied until you select build.\n";
showmenu:
	// Heading 
	system('clear');
	echo "{$GREEN}\nmkmodule -- Wizard to Start Building a New Apskel Module\n";
	echo "\n";
	if( $message > '' ) {
		echo "{$LIGHTBLUE}$message";
		echo "\n";
	}

	// Menu
	echo "$GREEN";
	echo "\t(N)ame of New Module: \"{$YELLOW}{$module_name}{$GREEN}\"\n";
	echo "\t(C)ontroller Design\n";
	echo "\t(D)atabase Design\n";
	echo "\t(B)uild the Module\n";
	echo "\t(Q)uit\n";
	echo "\n";
	
getselection:
	// Menu Selection Input and Main Program Loop
	$option = input("Select Menu Option: ");
	$option = strtolower( substr( trim( $option ), 0, 1 ) );
	switch( $option ) {

		// name of new module
		case "n":
			$module_name = input( 'Module Name:' );
			$message     = "Renaming the module was successful.\n";
			goto showmenu;
			break;

		// controller design
		case "c":
			controller();
			$message = "Exited from controller design screen.\n";
			goto showmenu;
			break;

		// database design
		case "d":
			database();
			$message = "Exited from database design screen.\n";
			goto showmenu;
			break;

		// build the module
		case "b":
			build();
			$message = "Exited from build process.\n";
			goto showmenu;
			break;

		// quit
		case "q":
			echo "{$GREEN}Exiting";
			sleep(1); echo '.';
			sleep(1); echo '.';
			sleep(1); echo '.';
			sleep(1);
			system('reset');
			exit;
			break;

		default:
			echo "{$RED}The choices to enter are n, c, d, b, or q, dummy.\n";
			goto getselection;
			break;
	}
}

function controller() {
	global $GREEN, $LIGHTBLUE, $YELLOW, $RED;
	global $module_name, $requests;
	$message     = "A controller is a collection of requests, each with a set of parameters.  For\nexample, the URL \"http://somewhere.com/{$module_name}/add?a=2&b=3\" is such\nthat the request is \"add\" and its parameters are \"a\" and \"b\".\n";
showmenu:
	// Heading 
	system('clear');
	echo "{$GREEN}\nController Design Screen\n";
	echo "\n";
	if( $message > '' ) {
		echo "{$LIGHTBLUE}$message";
		echo "\n";
	}

	// Menu
	echo $GREEN;
	echo "(D)escription of Module:\n";
	echo "\n";
	echo "{$YELLOW}{$requests['description']}\n";
	echo "\n";
	echo "{$GREEN}Controller Requests (select number to edit in detail):\n";
	$number = 0;
	$mapping = array();
	foreach( $requests['requests'] as $request => $parameters ) {
		echo "{$GREEN}(" . ++$number . ") \"{$YELLOW}$request{$GREEN}\"\n";
		$mapping[$number] = $request;
	}
	echo "(N)ew request\n";
	echo "(R)emove request\n";
	echo "(Q)uit Back to Main Screen (preserves change made)\n"; 
	echo "\n";
	$option = input( "{$LIGHTBLUE}Selection: {$YELLOW}" );
}

function database() {
}

function build() {
}
	
function input( $output ) {
	global $LIGHTBLUE, $YELLOW;
	echo "{$LIGHTBLUE}$output{$YELLOW}";
	$stdin = fopen('/dev/stdin', 'r');
	$input = trim( fgets( $stdin ) );
	return $input;
}

