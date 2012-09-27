<?php

# File: ~/application_0.0/modules/agent/agent_models.php
# Purpose: provide data access to the agent module
# 2012-09-12 ... created.

require_once('models.php');

# Class Declaration for agent Module's Models
class AgentModels extends Models {
	public $conditionals;  // registration of functions for use within reaction conditions
	public $actuationals;  // registration of functions for use within reaction action sequences

	// Constructor
	public function __construct( $param_framework ) {
		parent::__construct();
		$this->framework = $param_framework;
		// Register Conditional Functions
		$this->conditionals = array();
		$this->conditionals[] = array(
			'php_function'     => 'isQuantity',
			'regex_pattern'    => '/^\s*is\s*(>|<)?\s*[0-9]+\s*"([^"])"\s*$/',
			'display_pattern'  => '(IS {|>|<}n "..")',
			'description'      => 'True if n, greater than n, or less than n (respective to (nothing), >, or < being used) number of “..” matches are found in memory.',
		);
		$this->conditionals[] = array(
			'php_function'     => 'isAll',
			'regex_pattern'    => '',
			'display_pattern'  => '(ISALL ".." {=|>|<} "..")',
			'description'      => 'True if, for all of the first “..” matches, a match from the second “..” exists with like-named variables values greater than, less than, or equal (respective to <, >, or = being used).',
		);
		$this->conditionals[] = array(
			'php_function'     => 'isAny',
			'regex_pattern'    => '',
			'display_pattern'  => '(ISANY ".." {=|>|<} "..")',
			'description'      => 'True if, for any of the first “..” matches, a match from the second “..” exists with like-named variables values greater than, less than, or equal (respective to <, >, or = being used).',
		);
		$this->conditionals[] = array(
			'php_function'     => 'isCan',
			'regex_pattern'    => '',
			'display_pattern'  => '(can "..")',
			'description'      => 'True if a path from the current status to the given agent response (“..”) can be plotted.',
		);

		// Register Actuational Functions
		$this->actuationals[] = array(
			'php_function'     => 'actionSay',
			'regex_pattern'    => '/^\s*say\s*"([^"]*)"\s*$/i',
			'display_pattern'  => 'SAY ".."',
			'description'      => 'Outputs a textual message to the user.',
		);

		$this->actuationals[] = array(
			'php_function'     => 'actionRemember',
			//'regex_pattern'    => '/^\s*remember\s*"([^"]*)"\s*$/i',
			'regex_pattern'    => '/^\s*remember\s*"([^"]*)"\s*((for|until)\s*"([^"]*)"\s*)?$/i',
			'display_pattern'  => 'REMEMBER ".." [{FOR|UNTIL} ".."]',
			'description'      => 'Outputs a textual statement to long term memory.  Optionally, the number of minutes "FOR" may be specified or a date/time "UNTIL".',
		);

		$this->actuationals[] = array(
			'php_function'     => 'actionRecall',
			//'regex_pattern'    => '/^\s*recall\s*"([^"])"\s*$/',
			'regex_pattern'    => '/^\s*recall\s*"([^"]*)"\s*(and|or)?\s*$/i',
			'display_pattern'  => 'RECALL ".." [{AND|OR}]',
			'description'      => 'Inputs any variables in the string from long term memory.  Multiple finds will be assigned as comma delimited values.  If "AND" or "OR" are appended then the last value (if more than one match) will be prefixed with it.',
		);

		$this->actuationals[] = array(
			'php_function'     => 'actionForget',
			'regex_pattern'    => '/^\s*forget\s*"([^"]*)"\s*(after "([^"])"\s*)?$/i',
			'display_pattern'  => 'FORGET ".." [AFTER ".."]',
			'description'      => 'Removes all matching long term memories, variable names are ignored as variables are only used as wildcards.  If an "AFTER" value is given, it is used to scheduled to be forgotten after the given date/time or minutes to pass.',
		);

		$this->actuationals[] = array(
			'php_function'     => 'actionExpectAs',
			'regex_pattern'    => '/^\s*expect\s*"([^"]*)"\s*as\s*"([^"]*)"\s*$/i',
			'display_pattern'  => 'EXPECT ".." AS ".."',
			'description'      => 'Schedules that, if the next user statement is as specified, to interpret that as the other specified statement.  The first statement is an input (assigns to any variables) and the second statement is an output (writes out contents of any variables).',
		);

		$this->actuationals[] = array(
			'php_function'     => 'actionInterpretAs',
			'regex_pattern'    => '/^\s*interpret\s*as\s*"([^"]*)"\s*$/i',
			'display_pattern'  => 'INTERPRET AS ".."',
			'description'      => 'Performs as if the user also just entered the given statement.  This does not exclude any actions under the current reaction.  All outputs to the user are concatenated, in order.',
		);

		$this->actuationals[] = array(
			'php_function'     => 'actinWhatIf',
			'regex_pattern'    => '/^\s*what\s*if\s*"([^"])"\s*$/',
			'display_pattern'  => 'WHAT IF ".."',
			'description'      => 'Notes the response statement and long term memories that would result if the given user statement were entered under the current status.',
		);

		$this->actuationals[] = array(
			'php_function'     => 'actionHowCould',
			'regex_pattern'    => '/^\s*how\s*could\s*"([^"])"\s*else\s*"([^"])"\s*$/',
			'display_pattern'  => 'HOW COULD ".." ELSE ".."',
			'description'      => 'Solves for and notes what sequence of interactions (if any) would lead from the current status to the specified response statement.  If not solvable then interpret as the else statement.',
		);

		$this->actuationals[] = array(
			'php_function'     => 'actionWorkToward',
			'regex_pattern'    => '/^\s*work\s*toward\s*"([^"])"\s*else\s*"([^"])"\s*$/',
			'display_pattern'  => 'WORK TOWARD ".." ELSE ".."',
			'description'      => 'Solves for what sequence of interactions (if any) would lead from the current status to the specified response statement and takes the next actionable step to try and get there.  If not solvable then interpret as the else statement.',
		);

	} // end of __construct method

	// Upload from XML (optionally removing all content prior to upload, else just adding/overwriting)
	public function importXml( $script_xml, $replace_all = false ) {
		$warnings = '';  // to collect any warnings to report back about import..

		// Get basic information
		//$xml = new SimpleXMLElement($argXml,LIBXML_NOCDATA);
		//$xml = simplexml_load_file($argXmlFile,'SimpleXMLElement',LIBXML_NOCDATA);
		$xml = simplexml_load_string( $script_xml );

		$agent = $xml['name'];

		// Remove the agent script, if replacing
		if( $replace_all ) {
			//$sql = "DELETE m, r, u FROM Meanings AS m LEFT JOIN Reactions AS r ON m.id = r.meaning_id LEFT JOIN UsedReactions AS u ON r.id = u.reaction_id  WHERE m.agent = {$agent_name} AND m.id = r.meaning_id;";
			$sql = "DELETE FROM agent_meanings";
			$this->framework->runSql( $sql );
			$sql = "DELETE FROM agent_reactions";
			$this->framework->runSql( $sql );
			$sql = "DELETE FROM agent_used_reactions";
			$this->framework->runSql( $sql );
			$sql = "DELETE FROM agent_meanings";
			$this->framework->runSql( $sql );
			// TODO: log this..
		}

		foreach($xml->meaning as $meaning) {
			$effectiveLength = $this->getLengthOfRecognizer($meaning['recognizer']);
			//$sql = 'INSERT INTO agent_meanings ( recognizer, length, agent ) VALUES ( ' . $framework->quote($meaning['recognizer']) . ",{$effectiveLength}," . $argDb->quote($agent) . ');';
			$fields = array();
			$fields['recognizer'] = $this->framework->quoteForDatabase( $meaning['recognizer'] );
			$fields['length']     = $effectiveLength;
			if( isset( $meaning['paradigm'] ) && strlen( $meaning['paradigm'] ) > 0 ) {
				$fields['paradigm'] = "'" . strtoupper( substr( $meaning['paradigm'], 0, 1 ) ) . "'";
			}
			$sql = $this->buildInsertSql( 'agent_meanings', $fields );
			$num = $this->framework->runSql($sql);
			if($num == 0) {
				$warnings .=  "Meaning failed to insert: \"{$sql}\"\n"; 
				// TODO: log this
				continue; 
			}
			$meaning_id = $this->framework->getLastInsertId('id');
			foreach($meaning->reaction as $reaction) {
				$sql = "INSERT INTO agent_reactions ( meaning_id, priority, conditions, actions, functional ) VALUES ($meaning_id," . $reaction['priority'] . ',' .  $this->framework->quoteForDatabase( trim( $reaction['condition'] ) ) . ',' . $this->framework->quoteForDatabase( trim( $reaction ) ) . ',' . $this->framework->quoteForDatabase( $reaction['functional'] ) . ' )';
				$num = $this->framework->runSql($sql);
				if($num == 0) {
					$warnings .= "Reaction failed to insert: \"{$sql}\".\n";
					// TODO: log this
					continue;
				}
			}
		}
		return $warnings;
	}

	// Download as XML ( by array of meanings (id or statement matched) or else all (Default))
	public function exportXml( $meanings = null ) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= "<agent name=\"anyone\">\n";  // TODO: enable multiple agents.. probably each with own tables
		$sql = "SELECT * FROM agent_meanings ORDER BY length DESC";
		$meanings = $this->framework->runSql( $sql );
		foreach( $meanings as $meaning ) {
			switch( $meaning['paradigm'] ) {
				case 'C': $paradigm = 'cyclic'; break;
				case 'R': $paradigm = 'random'; break;
				case 'N': $paradigm = 'natural';
				default: $paradigm = 'natural';
			}
			$xml .= "\t<meaning recognizer=\"{$meaning['recognizer']}\" paradigm=\"{$paradigm}\">\n";
			$sql = "SELECT * FROM agent_reactions WHERE meaning_id = {$meaning['id']}";
			$reactions = $this->framework->runSql( $sql );
			foreach( $reactions as $reaction ) {
				$xml .= "\t\t<reaction priority=\"{$reaction['priority']}\" functional=\"{$reaction['functional']}\" condition=\"{$reaction['conditions']}\">\n";
				$xml .= "\t\t\t<![CDATA[\n";
				$xml .= $this->framework->prefixLines( $reaction['actions'], 4 );
				$xml .= "\t\t\t]]>\n";
				$xml .= "\t\t</reaction>\n";
			}
		$xml .= "\t</meaning>\n";
		}
		return $xml . "</agent>\n";
	}

	// Adds a new meaning, as identified by recognizer
	public function addMeaning( $recognizer ) {
		// TODO: ensure a duplicate doesn't already exist..
		$length = getLengthOfRecognizer( $recognizer );
		$sql = "INSERT INTO agent_meanings ( recognizer, length ) VALUES( '{$recognizer}', {$length} )";
		$this->framework->runSql( $sql );
	}

	// Adds a new reaction under the given meaning_id
	public function addReaction( $meaning_id, $conditions, $actions, $is_functional = 'U' ) {
		$sql = "INSERT INTO agent_reactions ( meaning_id, conditions, actions, functional ) VALUES ( {$meaning_id}, '{$conditions}', '{$actions}', '{$is_functional}' )";
		$this->framework->runSql( $sql );
	}

	// Replaces (overwrites) the specified reaction
	public function replaceReaction( $reaction_id, $conditions, $actions, $is_functional = 'U' ) {
		$sql = "UPDATE agent_reactions SET meaning_id, conditions = '{$conditions}', actions = '{$actions}', functional = '{$is_functional}' WHERE reaction_id = {$reaction_id}' )";
		$this->framework->runSql( $sql );
	}

	public function remember( $memory, $expiration ) {
	}

	public function forget( $memory ) {
	}

	// Returns meaning_id of closest matching recognizer or null for none
	public function findClosestMeaning( $statement ) {
		$statement_length = strlen( $statement );
		$sql = "SELECT id AS id, recognizer FROM agent_meanings WHERE length <= {$statement_length} ORDER BY length desc";
		$meanings = $this->framework->runSql( $sql );

		$matched = false;
		$notes = "Seeking the meaning of: $statement\n";

		// Full punctuation and case match?
		foreach( $meanings as $meaning ) {
			$recognizer = $meaning['recognizer'];
			list( $matched, $wildcards ) = $this->compareStatementToRecognizer( $statement, $recognizer );
			if( $matched ) {
				$meaning_id = $meaning['id'];
				break;
			}
		}
		if( !$matched ) {
			$notes .= "Full punctuation and caseful match: not found.\n";
		}

		// Convert aka terms/symbols to common ones (e.g. ' percent ' to '%', ' dollars ' to '$', etc)
		// TODO..

		// Full punctuation caseless match?
		if( !$matched ) {
			foreach( $meanings as $meaning ) {
				$recognizer = $meaning['recognizer'];
				list( $matched, $wildcards ) = $this->compareStatementToRecognizer( strtolower( $statement ), strtolower( $recognizer ) );
				if( $matched ) {
					$meaning_id = $meaning['id'];
					break;
				}
			}
			$notes .= "Full punctuation caseless match: not found.\n";
		}

		// No punctuation but caseful match?
		if( !$matched ) {
			$punctuation = array( '!', '.', '?',',' );
			$modified_statement  = str_replace( $punctuation, '', $statement );
			foreach( $meanings as $meaning ) {
				$recognizer = $meaning['recognizer'];
				$modified_recognizer = str_replace( $punctuation, '', $recognizer );
				list( $matched, $wildcards ) = $this->compareStatementToRecognizer( $modified_statement, $modified_recognizer );
				if( $matched ) {
					$meaning_id = $meaning['id'];
					break;
				}
			}
			$notes .= "No punctuation but caseful match: not found.\n";
		}

		// No punctuation caseless match?
		if( !$matched ) {
			foreach( $meanings as $meaning ) {
				$recognizer = $meaning['recognizer'];
				$modified_recognizer = str_replace( $punctuation, '', $recognizer );
				list( $matched, $wildcards ) = $this->compareStatementToRecognizer( strtolower( $modified_statement ), strtolower( $modified_recognizer ) );
				if( $matched ) {
					$meaning_id = $meaning['id'];
					break;
				}
			}
			$notes .= "No punctuation caseless match: not found.\n";
		}

		// If still no match, try removing anything in parenthesis
		// TODO..

		//print nl2br( $notes );  -- DEBUG

		// Matched or not, return the result..
		if( $matched ) { return array( 'meaning_id' => $meaning_id, 'wildcards' => $wildcards ); }
		else { return null; }
	}

	// Returns array of reactions under meaning, as best refinable using SQL
	public function getAppropriateReaction( $meaning ) {
		extract( $meaning );  // gives $meaning_id and $wildcards
		$sql = "
			SELECT agent_reactions.id AS next_id, conditions, actions, last_used
			FROM agent_reactions LEFT JOIN agent_reactions_used ON agent_reactions.id = agent_reactions_used.reaction_id 
			WHERE agent_reactions.meaning_id = {$meaning_id} and functional <> 'F' 
			ORDER BY isnull(last_used) desc, last_used, priority;
		";
		$reactions = $this->framework->runSql( $sql );
		$actions = '';
		$reaction_id = null;
		foreach( $reactions as $reaction ) {
			if( $this->areConditionsTrue( $reaction['conditions'], $wildcards ) ) {
				# Mark this reaction as having been used recently as to reduce its preferability for a little while.. 
				if( $reaction['last_used'] == null ) {
					// if reaction never before used then append a last_used record (as now)..
					$sql = "INSERT INTO agent_reactions_used ( reactionId, conversationId, whenUsed ) VALUES ( {$reaction['next_id']}, 0, now() )";  
					$this->framework->runSql( $sql );
				} 
				else {
					// if reaction used before then update its last_used record to now..
					$sql = "UPDATE agent_reactions_used SET last_used = now() WHERE reaction_id = {$reaction['next_id']}";  
					$this->framework->runSql( $sql );
				}
	
				# Break out with the reaction_id and actions to perform 
				$actions     = $reaction['actions'];
				$reaction_id = $reaction['next_id'];
				break;
			}
		}
		if( $reaction_id === null ) {
			// No valid reactions exist
			// TODO: at least log this..
			return array( 'verbal' => 'DEBUG: no valid reaction (blank this out)', 'nonverbal' => '' ); 
		}

		// Execute actions from the selected reaction
		$response = $this->executeActions( $actions, $wildcards );
		if( $response === null ) {
			// Error in actions, so mark as non-functional
			$response = array( 'verbal' => 'DEBUG: error in actions (blank this out)', 'nonverbal' => '' );
			// TODO: WORKING..
		}
		// return array( $response_text, $response_actions );
		return $response;  // associative array: 'verbal' => '..', 'nonverbal' => '..'
	}

	private function getLengthOfRecognizer( $recognizer ) {
		$recognizer = preg_replace('/\[[^\]]*\]/','*',$recognizer); 
		return strlen( $recognizer );
	}

	// If statement matches recognizer (converted) then return true and populate 
	public function compareStatementToRecognizer( $statement, $recognizer ) {
		// TODO: (1) Validate that brackets are matched; (2) Respect escaped brackets in recognizer

		// Convert recognizer to regex and collect wildcard names
		$recognizer = preg_quote( $recognizer,'/' );                         // escapes characters with non-literal regex meaning
		$recognizer = str_replace( '\[','[', $recognizer );                  // restores [ from escape
		$recognizer = str_replace( '\]',']', $recognizer );                  // restores ] from escape
		$regex      =  preg_replace( "/\[([^]]*)\]/","(.*)", $recognizer );  // translate recognizer to regular expression
		preg_match_all( "/\[[^]]*\]/", $recognizer, $wildcard_names );       // gets array of wildcard names

		// Check if matched and, if so, collect wildcard assignments
		$matched = preg_match('/' . trim( $regex ) . '/', trim( $statement ), $variable_matches);
		$wildcards = array();
		if($matched) {
			// collect statement parameters and form into associate array
			$n = 0;
			foreach($wildcard_names[0] as $wildcard_name) {
				$wildcards[$wildcard_name] = $variable_matches[++$n];
			}
		}
 
		return array( $matched, $wildcards );
	}

	// Evaluate a reaction's conditions
	private function areConditionsTrue( $conditions, $wildcards ) {
		// empty should evaluate to true
		$conditions = trim( $conditions );
		if( $conditions == '' ) { return true; }

		// Translate to PHP 
		// TODO: WORKING.. 

		// Ensure only allowed code is included: &&, ||, !, (, ), and the is* functions
		// TODO: WORKING..

		// Evaluate and return the result 
		eval( "\$result = {$conditions};\n" );
		return $result;
	}

	private function isQuantity( $param ) {
	}

	private function isAll( $param ) {
	}

	private function isAny( $param ) {
	}

	private function isCan( $param ) {
	}

	private function executeActions( $actions, $wildcards ) {
		// WORKING..
		$verbal = '';
		$nonverbal = '';
		$lines = explode( "\n", trim( $actions ) );
		foreach( $lines as $line ) {
			$line = trim( $line );
			if( $line == '' ) { continue; }
			$actuator_function = null;
			foreach( $this->actuationals as $actuator ) {
				if( preg_match( $actuator['regex_pattern'], $line, $parameters ) ) {
					$actuator_function = $actuator['php_function'];
					break;
				}
			}
			if( $actuator_function === null ) {
				// The action $line was not recognized, therefor this reaction is non-functional
				print "DEBUG bad action regex: /$line/<br/>\n";
				return null;
			}
			list( $verbal_append, $nonverbal_append ) = $this->$actuator_function( $parameters, $wildcards );
			$verbal    .= $verbal_append;
			$nonverbal .= $nonverbal_append;
		}

		return array( 'verbal' => $verbal, 'nonverbal' => $nonverbal );
	}

	private function writeInWildcardValues( $text, $wildcards ) {
		foreach( $wildcards as $wildcard => $value ) {
			$text = str_replace( '[' . trim( $wildcard, "[]" ) . ']', $value, $text );
		}
		return $text;
	}

	private function actionSay( $params, $wildcards ) {
		return array( $this->writeInWildcardValues( $params[1], $wildcards ), '' );
	}

	private function actionRemember( $params, $wildcards ) {
		// TODO: add date/time, if provided for expiration
		$fields = array();
		$fields['memory'] = trim( "'" . addslashes( $this->writeInWildcardValues( $params[1], $wildcards ) ) . "'" );
		$sql = $this->buildInsertSql( 'agent_memories', $fields );
		$this->framework->runSql( $sql );
		return array( '', ";remembered" );
	}

	private function actionRecall( $params, &$wildcards ) {
		// create search pattern by applying wildcards from user statement
		$seeking = trim( "'" . addslashes( $this->writeInWildcardValues( $params[1], $wildcards ) ) . "'" );

		// extract any other wildcards from memory
		$sql_pattern = preg_replace( '/\[([^]]*)\]/','%', $seeking );
		$sql = "SELECT memory FROM agent_memories WHERE memory LIKE {$sql_pattern}";
		$matches = $this->framework->runSql( $sql );
		foreach( $matches as $match ) {
			list( $matched, $new_wildcards ) = $this->compareStatementToRecognizer( $match['memory'], $seeking );
			if( !$matched ) {
				// TODO: log this..
				print "DEBUG: During recall, \"{$match['memory']}\" failed to match \"$seeking\" in PHP, although it did in SQL.<br/>\n";
				continue;
			}
			foreach( $new_wildcards as $wildcard => $value ) {
				// TODO: What if there is a comma in $value?
				if( isset( $wildcards[$wildcard] ) ) {
					$wildcards[$wildcard] .= ",{$value}";
				}
				else {
					$wildcards[$wildcard] = $value;
				}
			}
		}
		
		return array( $sql . "\n" . print_r( $wildcards, true ), ';recalled' );
	}

	private function actionForget( $params, $wildcards ) {
		$pattern =  preg_replace( '/\[([^]]*)\]/','%', $params[1] );
		$sql = "DELETE FROM agent_memories WHERE memory LIKE '{$pattern}'";
		$this->framework->runSql( $sql );
		return array( '', ';forgot' );
	}

	private function actionExpectAs( $params, $wildcards ) {
		return array( print_r( $params, true ), '' );
	}

	private function actionInterpretAs( $params, $wildcards ) {
		return array( print_r( $params, true ), '' );
	}

	private function actionWhatIf( $params, $wildcards ) {
		return array( print_r( $params, true ), '' );
	}

	private function actionHowCould( $params, $wildcards ) {
		return array( print_r( $params, true ), '' );
	}

	private function actionWorkToward( $params, $wildcards ) {
		return array( print_r( $params, true ), '' );
	}

} // end of AgentModels class