<?php

# File: ~/application_0.0/modules/agent/agent_models.php
# Purpose: provide data access to the agent module
# 2012-09-12 ... created.

require_once('models.php');

# Class Declaration for agent Module's Models
class AgentModels extends Models {
	public $conditionals;  // registration of functions for use within reaction conditions
	public $actuationals;  // registration of functions for use within reaction action sequences
	public $paradigm_mappings;
	public $functional_mappings;

	// Constructor
	public function __construct( $param_framework ) {
		parent::__construct();
		$this->framework = $param_framework;

		// Register Paradigm Mappings (database uses 1 charachter while user should see whole word)
		$this->paradigm_mappings   = array( 'Natural'  => 'N', 'Cyclic' => 'C', 'Random' => 'R' );
		$this->comparison_mappings = array( 'Full'  => 'F', 'Partial' => 'P' );
		$this->functional_mappings = array( 'untested' => 'U', 'false'  => 'F', 'true'   => 'T' );
		$this->inversions          = array (
			'I'     => 'you',
			'you'   => 'me',
			'me'    => 'you',
			'am'    => 'are',
			'are'   => 'am',
			'my'    => 'your',  
			'your'  => 'my',
			'yours' => 'mine',
			'mine'  => 'yours',
			//'don\'t'  => 'do not',     // TODO: build in support for multiple word conversions
			//'won\'t'  => 'will not',
			//'can\'t'  => 'cannot',
			//'can not'  => 'cannot',
			//'isn\'t'  => 'is not',
		);

		// Register Conditional Functions
		$this->conditionals = array();
		$this->conditionals[] = array(
			'php_function'     => '$this->isQuantity( \'${1}\', ${2}, "${3}", $wildcards )',
			'regex_pattern'    => '/\(\s*is\s*([=><]?)\s*([0-9]+)\s*"([^"]*)"\s*\)/i',
			'display_pattern'  => '(IS {|>|<}n "..")',
			'description'      => 'True if n, greater than n, or less than n (respective to (nothing), >, or < being used) number of ".." matches are found in memory.',
		);
		$this->conditionals[] = array(
			'php_function'     => '$this->isAll( "${1}", \'${2}\', "${3}", $wildcards )',
			'regex_pattern'    => '/\(\s*isall\s*"([^"]*)"\s*([=><])\s*"([^"]*)"\s*\)/i',
			'display_pattern'  => '(ISALL ".." {=|>|<} "..")',
			'description'      => 'True if, for all of the first ".." matches, a match from the second ".." exists with like-named variables values greater than, less than, or equal (respective to <, >, or = being used).',
		);
		$this->conditionals[] = array(
			'php_function'     => '$this->isAny( "${1}", \'${2}\', "${3}", $wildcards )',
			'regex_pattern'    => '/\(\s*isany\s*"([^"]*)"\s*([=><])\s*"([^"]*)"\s*\)/i',
			'display_pattern'  => '(ISANY ".." {=|>|<} "..")',
			'description'      => 'True if, for any of the first ".." matches, a match from the second ".." exists with like-named variables values greater than, less than, or equal (respective to <, >, or = being used).',
		);
		$this->conditionals[] = array(
			'php_function'     => '$this->isCan( "${1}", $wildcards )',
			'regex_pattern'    => '/\(\s*iscan\s*"([^"]*)"\s*\)/i',
			'display_pattern'  => '(can "..")',
			'description'      => 'True if a path from the current status to the given agent response ("..") can be plotted.',
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

		$this->actuationals[] = array(
			'php_function'     => 'actionTest',
			'regex_pattern'    => '/^\s*test\s*(.*)\s*$/',
			'display_pattern'  => 'TEST ..',
			'description'      => 'Outputs "true" or "false", as per the conditional function provided.',
		);

	} // end of __construct method

	// Provide array of condition functions for documentation purposes..
	public function getConditionDescriptions() {
		$conditions = array();
		foreach( $this->conditionals as $conditional ) {
			$conditions[] = array ( 
				'display_pattern' => htmlspecialchars( $conditional['display_pattern'] ), 
				'description'     => htmlspecialchars( $conditional['description'] ) 
			); 
		}
		return $conditions;
	}

	// Provide array of action commands for documentation purposes..
	public function getActionDescriptions() {
		$actions = array();
		foreach( $this->actuationals as $actional ) { $actions[] = array ( 
			'display_pattern' => htmlspecialchars( $actional['display_pattern'] ), 
			'description'     => htmlspecialchars( $actional['description'] ) 
			); 
		}
		return $actions;
	}

	// Upload from XML (optionally removing all content prior to upload, else just adding/overwriting)
	public function importXml( $script_xml, $replace_all = false ) {
		$warnings = '';  // to collect any warnings to report back about import..

		// Get basic information
		//$xml = new SimpleXMLElement($argXml,LIBXML_NOCDATA);
		//$xml = simplexml_load_file($argXmlFile,'SimpleXMLElement',LIBXML_NOCDATA);
		$xml = simplexml_load_string( $script_xml );
		if( $xml === false ) { $warnings = 'Import aborted (no changes were applied).  The XML was malformed.'; }
		else {
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
				// Plain values, bound by insertRecord -- no manual quoting/concatenation.
				$fields = array();
				$fields['recognizer'] = (string) $meaning['recognizer'];
				$fields['length']     = $effectiveLength;
				if( isset( $meaning['paradigm'] ) ) { $fields['paradigm'] = $this->framework->mapToValue( (string) $meaning['paradigm'], $this->paradigm_mappings, 'N' ); }
				else { $fields['paradigm'] = 'N'; }
				if( isset( $meaning['comparison'] ) ) { $fields['comparison'] = $this->framework->mapToValue( (string) $meaning['comparison'], $this->paradigm_mappings, 'F' ); }
				else { $fields['comparison'] = 'F'; }
				$meaning_id = $this->insertRecord( 'agent_meanings', $fields, 'id' );
				if( $meaning_id === null ) {
					$warnings .= 'Meaning failed to insert: ' . htmlspecialchars( $fields['recognizer'] ) . "<br/>\n";
					// TODO: log this
					continue;
				}
				foreach($meaning->reaction as $reaction) {
					$reaction_fields = array(
						'meaning_id' => $meaning_id,
						'priority'   => ( isset( $reaction['priority'] ) && is_numeric( (string) $reaction['priority'] ) ) ? (int) $reaction['priority'] : 0,
						'conditions' => $this->framework->trimLines( $reaction['condition'] ),
						'actions'    => trim( $this->framework->trimLines( $reaction ), "\n" ),
						'functional' => (string) $reaction['functional'],
					);
					if( isset( $reaction['reaction_id'] ) ) {
						$reaction_id = (string) $reaction['reaction_id'];
						$this->updateElseInsert( 'agent_reactions', $reaction_fields, 'reaction_id = :where_rid', array( ':where_rid' => $reaction_id ) );
						// TODO: Some kind of error reporting here.. like down below..
					}
					else {
						$new_id = $this->insertRecord( 'agent_reactions', $reaction_fields, 'id' );
						if( $new_id === null ) {
							$warnings .= 'Reaction failed to insert under meaning ' . htmlspecialchars( $fields['recognizer'] ) . "<br/>\n";
							// TODO: log this
							continue;
						}
					}
				}
			}
		} // end of XML parse succeeded
		return $warnings;
	}

	// Download as XML ( by array of meanings (id or statement matched) or else all (Default))
	public function exportXml( $meanings = null ) {
		$xml = '<' .'?xml version="1.0" encoding="UTF-8"?' . '>' . "\n";
		$xml .= "<agent name=\"anyone\">\n";  // TODO: enable multiple agents.. probably each with own tables
		$sql = "SELECT * FROM agent_meanings ORDER BY recognizer";
		$meanings = $this->framework->runSql( $sql );
		foreach( $meanings as $meaning ) {
			if( isset( $meaning['paradigm'] ) ) { $paradigm = $this->framework->mapToKey( $meaning['paradigm'], $this->paradigm_mappings, 'Natural' ); }
			else { $paradigm = "Natural"; }
			if( isset( $meaning['comparison'] ) ) { $comparison = $this->framework->mapToKey( $meaning['comparison'], $this->comparison_mappings, 'Full' ); }
			else { $comparison = "Full"; }
			$xml .= "\t<meaning recognizer=\"{$meaning['recognizer']}\" comparison=\"{$comparison}\" paradigm=\"{$paradigm}\">\n";
			$reactions = $this->framework->runSql(
				'SELECT * FROM agent_reactions WHERE meaning_id = :meaning_id',
				array( ':meaning_id' => $meaning['id'] )
			);
			foreach( $reactions as $reaction ) {
				$conditions = htmlentities( $reaction['conditions'] );
				$xml .= "\t\t<reaction priority=\"{$reaction['priority']}\" functional=\"{$reaction['functional']}\" condition=\"{$conditions}\">\n";
				$xml .= "\t\t\t<![CDATA[\n";
				$xml .= $this->framework->prefixLines( $reaction['actions'], 4 );
				$xml .= "\t\t\t]]>\n";
				$xml .= "\t\t</reaction>\n";
			}
		$xml .= "\t</meaning>\n";
		}
		return $xml . "</agent>\n";
	}

	// Save Topic
	public function saveTopic( $fields ) {
		// Values are kept as plain PHP values and bound by updateElseInsert -- no
		// manual quoting (which was injection-prone).
		$fields['actions'] = $this->sanitizeActions( stripslashes( $fields['actions'] ) );
		$this->updateElseInsert( 'agent_topics', $fields, 'title = :where_title', array( ':where_title' => $fields['title'] ) );
		// TODO: get and return the record's ID; also log this..
	}

	// Adds a new meaning, as identified by recognizer
	public function saveMeaning( $fields ) {
		$warnings   = '';
		$meaning_id = null;
		if( isset( $fields['recognizer'] ) ) {
			$fields['recognizer'] = stripslashes( $fields['recognizer'] );
			$fields['length'] = $this->getLengthOfRecognizer( $fields['recognizer'] );
			if( !isset( $fields['meaning_id'] ) || $fields['meaning_id'] == '' ) {
				// Look up an existing meaning by recognizer (bound parameter).
				$results = $this->framework->runSql(
					'SELECT id AS meaning_id FROM agent_meanings WHERE recognizer = :recognizer',
					array( ':recognizer' => $fields['recognizer'] )
				);
				if( is_array( $results ) && count( $results ) > 0 ) { $fields['meaning_id'] = $results[0]['meaning_id']; }
			}
		}
		if( !isset( $fields['paradigm'] ) ) {  $fields['paradigm'] = ''; }
		$fields['paradigm'] = $this->framework->mapToValue( $fields['paradigm'], $this->paradigm_mappings, 'N' );
		// Values are kept plain and bound below -- no manual quoting.
		if( isset( $fields['meaning_id'] ) && $fields['meaning_id'] > 0 ) {
			if( is_numeric( $fields['meaning_id'] ) ) {
				$id = $fields['meaning_id'];
				unset( $fields['meaning_id'] );
				$results = $this->updateRecords( 'agent_meanings', $fields, 'id = :where_id', array( ':where_id' => $id ) );
				if( $results === null ) {
					$warnings   .= 'Updating meaning failed: problem trying to update the database. ';
					$meaning_id  = null;
				}
				else { $meaning_id = $id; }
			}
			else {
				// TODO: log meaning_id was not numeric..
				$warnings   .= "Updating meaning failed: meaning_id provided was not numeric.";
				$meaning_id  = $fields['meaning_id'];
			}
		}
		else {
			if( $fields['recognizer'] == '' ) { $warnings .= 'A new meanings with a blank recognizer is not allowed.  '; }
			else {
				if( isset( $fields['meaning_id'] ) ) { unset( $fields['meaning_id'] ); }
				$meaning_id = $this->insertRecord( 'agent_meanings', $fields, 'id' );
			}
		}
		return array( $meaning_id, $warnings );
	}

	// Adds a new reaction under the given meaning_id
	public function saveReaction( $fields ) {

		$warnings = '';
		if( !isset( $fields['meaning_id'] ) || !is_numeric( $fields['meaning_id'] ) ) {
			$warnings   .= 'To save the reaction, a meaning_id is required but was not provided.  ';
			$reaction_id = null;
		}
		else {
			// Validate/translate parameters
			if( isset( $fields['priority'] ) && !is_numeric( $fields['priority'] ) ) {
				$warnings .= 'The priority specified was not numeric.  Therefore, it was defaulted to 0.';
				$fields['priority'] = '0';
			}
			if( isset( $fields['functional'] ) ) {
				$fields['functional'] = $this->framework->mapToValue( stripslashes( $fields['functional'] ), $this->functional_mappings );
			}
			if( isset( $fields['conditions'] ) ) {
				$fields['conditions'] = $this->sanitizeConditions( stripslashes( $fields['conditions'] ) );
			}
			if( isset( $fields['actions'] ) ) {
				$fields['actions'] = $this->sanitizeActions( stripslashes( $fields['actions'] ) );
			}

			// Values stay plain and are bound below. Update existing (if given a
			// numeric reaction_id) or insert new otherwise.
			$columns = array( 'meaning_id', 'priority', 'functional', 'conditions', 'actions' );
			if( isset( $fields['reaction_id'] ) && is_numeric( $fields['reaction_id'] ) ) {
				$reaction_id = $fields['reaction_id'];
				$values  = $this->framework->removeAllBut( $columns, $fields );
				$results = $this->updateRecords( 'agent_reactions', $values, 'id = :where_id', array( ':where_id' => $reaction_id ) );
				if( $results === null ) {
					$warnings .= 'Updating the reaction failed: problem trying to update the database. ';
				}
			}
			else {
				// Do an insert..
				$values      = $this->framework->removeAllBut( $columns, $fields );
				$reaction_id = $this->insertRecord( 'agent_reactions', $values, 'id' );
			}
		}
		return array( $reaction_id, $warnings );
	}

	public function sanitizeConditions( $conditions ) {
		// WORKING... XXX
		return $conditions;
	}

	public function sanitizeActions( $actions ) {
		$sanitized_actions = '';
		$lines = explode( "\n", $actions );
		foreach( $lines as $line ) {
			// Check for Syntax Error 
			$line = trim( $line );
			if( $line == '' ) { continue; }
			$valid = false;
			foreach( $this->actuationals as $actuator ) {
				if( preg_match( $actuator['regex_pattern'], $line, $parameters ) ) {
					$valid = true;
					break;
				}
			}
			if( $valid == false && substr( $line, 0, 2 ) != '//' ) { $line = "// Syntax Error: {$line}"; }

			// Check for Injection Attack 
			// TODO: Check to see if this is true:
			//       - Only possible place for an injection is from within quotes.
			//       - Only way to inject is by escaping quotes.
			//       - There is no way to escape these quotes without breaking command syntax
			//         Hence the above synax error would comment it out.. rendering it not executed by PHP 

			$sanitized_actions .= "$line\n";
		}
	
		return $sanitized_actions;
	}

	public function getAllMeanings( $alphabetic = true ) {
		// Fixed literal ORDER BY clauses -- no interpolation of any kind.
		if( $alphabetic ) { $sql = "SELECT id, recognizer, comparison, paradigm FROM agent_meanings ORDER BY recognizer"; }
		else              { $sql = "SELECT id, recognizer, comparison, paradigm FROM agent_meanings ORDER BY length DESC"; }
		$results = $this->framework->runSql( $sql );
		$meanings = array();
		foreach( $results as $result ) {
			$paradigm   = $this->framework->mapToKey( $result['paradigm'],   $this->paradigm_mappings, 'Natural' );
			$comparison = $this->framework->mapToKey( $result['comparison'], $this->comparison_mappings, 'Full' );
			$meanings[] = array( 'id' => $result['id'], 'recognizer' => $result['recognizer'], 'comparison' => $comparison, 'paradigm' => $paradigm );
		}
		return $meanings;
	}

	public function getMeaning( $meaning_id ) {
		$results = $this->framework->runSql(
			'SELECT id, recognizer, paradigm FROM agent_meanings WHERE id = :id',
			array( ':id' => $meaning_id )
		);
		if( !is_array( $results ) || count( $results ) === 0 ) {
			// TODO: deal with no results returned possibility
			return null;
		}
		return $results[0];  // associative array of items in the select statement above
	}

	public function getAllReactionsByMeaning( $meaning_id ) {
		return $this->framework->runSql(
			'SELECT id, priority, functional, conditions, actions FROM agent_reactions WHERE meaning_id = :meaning_id ORDER BY priority',
			array( ':meaning_id' => $meaning_id )
		);
	}

	public function getAllTopics() {
		$sql = "SELECT id, title, description, actions FROM agent_topics ORDER BY title";
		$results = $this->framework->runSql( $sql );
		$topics = array();
		if( count( $results ) > 0 ) {
			foreach( $results as $result ) {
				$topics[] = array( 'id' => $result['id'], 'title' => $result['title'], 'description' => $result['description'], 'actions' => $result['actions'] );
			}
		}
		return $topics;
	}

	public function reactTo( $statement ) {
		$statement = trim( preg_replace( '/\s+/', ' ', $statement ) );
		$meaning = $this->findClosestMeaning( $statement );
		if( $meaning == null ) {
			// NOTE: The Standard Meaning of an Unrecognized Statement 
			// TODO: Perhaps, attempt to identify a sub-string of it and ask the user if that's what he/she means..
			//       Or, keep a running tab of these and which the users says he/she means more than x% of the time
			//       as to auto-assume thereafter..  If not correct, the user will restate..
			$meaning = $this->findClosestMeaning( "What is the meaning of: $statement" );
		}
		return $this->getAppropriateReaction( $meaning );
	}
	
	// Returns meaning_id of closest matching recognizer or null for none
	public function findClosestMeaning( $statement ) {
		// Was statement a specially expected phrase (expect ".." as"..)?
		if( isset( $_SESSION['agent'] ) && isset( $_SESSION['agent']['expecting'] ) ) {
			foreach( $_SESSION['agent']['expecting'] as $from => $to ) {
				$from = strtolower( trim( $from ) );
				if( $statement == $from ) { $statement = $to;  }
			}
			// Cease all expectations (TODO: enhance "expect as" action to specify longer durations)
			$_SESSION['agent']['expecting'] = array();
		}

		// Collect all possible matching meaning recognizers then start comparing for best match
		$sql = "SELECT id AS id, recognizer, paradigm FROM agent_meanings WHERE length > 1 AND comparison = 'F' ORDER BY length desc";
		$meanings = $this->framework->runSql( $sql );

		// Start by trying to match Full recognizers..
		$search = array( 'found' => false, 'meaning_id' => null, 'paradigm' => null, 'wildcards' => array() );
		if( !$search['found'] ) { $search = $this->isRecognized( $statement, $meanings, array( 'caseful', 'punctuated' ) ); }
		if( !$search['found'] ) { $search = $this->isRecognized( $statement, $meanings, array( 'caseful' ) ); }
		if( !$search['found'] ) { $search = $this->isRecognized( $statement, $meanings, array( 'punctuated' ) ); }
		if( !$search['found'] ) { $search = $this->isRecognized( $statement, $meanings, array( ) ); }

		// Convert aka terms/symbols to common ones (e.g. ' percent ' to '%', ' dollars ' to '$', etc)
		// TODO..

		// If still no match, try running anything in parenthesis separately from rest of statement
		// TODO 

		// If still no match, try matching Partial recognizers.. 
		$sql = "SELECT id AS id, recognizer, paradigm FROM agent_meanings WHERE length > 1 AND comparison = 'P' ORDER BY length desc";
		$meanings = $this->framework->runSql( $sql );
		if( !$search['found'] ) { $search = $this->isRecognized( $statement, $meanings, array( 'partial', 'caseful', 'punctuated' ) ); }
		if( !$search['found'] ) { $search = $this->isRecognized( $statement, $meanings, array( 'partial', 'caseful' ) ); }
		if( !$search['found'] ) { $search = $this->isRecognized( $statement, $meanings, array( 'partial', 'punctuated' ) ); }
		if( !$search['found'] ) { $search = $this->isRecognized( $statement, $meanings, array( 'partial' ) ); }

		// If still no match, try matching to a catch-all recognizer..
		if( !$search['found'] ) {
			$sql      = "SELECT id AS id, recognizer, paradigm FROM agent_meanings WHERE length = 1 AND recognizer like '[%]'";
			$meanings = $this->framework->runSql( $sql );
			$search   = $this->isRecognized( $statement, $meanings, array() ); 
		}

		// Matched or not, return the result..
		if( $search['found'] ) { return $search; }
		else { return null; }
	}

	// Checks for match between user statement and provided meaning recognizers
	private function isRecognized( $statement, $meanings, $how = array() ) {
		// Full punctuation and case match?
		$matched = false;
		$meaning_id = null;
		$paradigm   = null;

		foreach( $meanings as $meaning ) {
			$recognizer = $meaning['recognizer'];
			if( !in_array( 'caseful', $how ) ) {
				$statement  = strtolower( $statement );
				$recognizer = strtolower( $recognizer );
			}
			if( !in_array( 'punctuated', $how ) ) {
				$punctuation = array( '!', '.', '?',',' );
				$statement  = str_replace( $punctuation, '', $statement );
				$recognizer = str_replace( $punctuation, '', $recognizer );
			}
			if( !in_array( 'partial', $how ) ) { $partial = false; }
			else { $partial = true; }

			list( $matched, $wildcards ) = $this->compareStatementToRecognizer( $statement, $recognizer, $partial );
			if( $matched ) {
				$meaning_id = $meaning['id'];
				$paradigm   = $meaning['paradigm'];
				break;
			}
		}
		return array( 'found' => $matched, 'meaning_id' => $meaning_id, 'paradigm' => $paradigm, 'wildcards' => $wildcards );
	}

	// Returns array of reactions under meaning, as best refinable using SQL
	public function getAppropriateReaction( $meaning ) {
		extract( $meaning );  // gives $meaning_id and $wildcards
		switch( strtolower( $paradigm ) ) {
			case 'random':   $reaction = $this->randomReactionSelection( $meaning_id, $wildcards ); break;
			case 'cyclic':   $reaction = $this->cyclicReactionSelection( $meaning_id, $wildcards ); break;
			case 'natural': 
			default:         $reaction = $this->naturalReactionSelection( $meaning_id, $wildcards ); break;
		}

		if( $reaction['reaction_id'] === null ) {
			// No valid reactions exist
			// TODO: at least log this..
			return array( 'verbal' => 'DEBUG: no valid reaction (blank this out)', 'nonverbal' => '' ); 
		}

		// Execute actions from the selected reaction
		$response = $this->executeActions( $reaction['actions'], $wildcards );
		if( $response === null ) {
			// Error in actions, so mark as non-functional
			$response = array( 'verbal' => 'DEBUG: error in actions (blank this out)', 'nonverbal' => '' );
			// TODO: WORKING..
		}
		// return array( $response_text, $response_actions );
		return $response;  // associative array: 'verbal' => '..', 'nonverbal' => '..'
	}

	private function naturalReactionSelection( $meaning_id, $wildcards ) {
		// ( last_used IS NULL ) replaces the MySQL-only isnull() so never-used
		// reactions sort first on Postgres.
		$reactions = $this->framework->runSql(
			"SELECT agent_reactions.id AS next_id, conditions, actions, last_used
			 FROM agent_reactions LEFT JOIN agent_reactions_used ON agent_reactions.id = agent_reactions_used.reaction_id
			 WHERE agent_reactions.meaning_id = :meaning_id AND functional <> 'F'
			 ORDER BY ( last_used IS NULL ) DESC, last_used, priority",
			array( ':meaning_id' => $meaning_id )
		);
		$actions = '';
		$reaction_id = null;
		foreach( $reactions as $reaction ) {
			if( $this->areConditionsTrue( $reaction['conditions'], $wildcards ) ) {
				# Mark this reaction as having been used recently as to reduce its preferability for a little while..
				if( $reaction['last_used'] == null ) {
					// if reaction never before used then append a last_used record (as now).. // TODO: use real user_id below..
					$this->framework->runSql(
						'INSERT INTO agent_reactions_used ( reaction_id, user_id, last_used ) VALUES ( :reaction_id, 0, now() )',
						array( ':reaction_id' => $reaction['next_id'] )
					);
				}
				else {
					// if reaction used before then update its last_used record to now.. // TODO: use real user_id below..
					$this->framework->runSql(
						'UPDATE agent_reactions_used SET last_used = now() WHERE user_id = 0 AND reaction_id = :reaction_id',
						array( ':reaction_id' => $reaction['next_id'] )
					);
				}

				# Break out with the reaction_id and actions to perform
				$actions     = $reaction['actions'];
				$reaction_id = $reaction['next_id'];
				break;
			}
		}
		return array( 'reaction_id' => $reaction_id, 'actions' => $actions );
	}

	private function cyclicReactionSelection( $meaning_id, $wildcards ) {
		// ( last_used IS NULL ) replaces the MySQL-only isnull() so never-used
		// reactions sort first on Postgres.
		$reactions = $this->framework->runSql(
			"SELECT agent_reactions.id AS next_id, conditions, actions, last_used
			 FROM agent_reactions LEFT JOIN agent_reactions_used ON agent_reactions.id = agent_reactions_used.reaction_id
			 WHERE agent_reactions.meaning_id = :meaning_id AND functional <> 'F'
			 ORDER BY ( last_used IS NULL ) DESC, last_used, priority",
			array( ':meaning_id' => $meaning_id )
		);
		$actions = '';
		$reaction_id = null;
		foreach( $reactions as $reaction ) {
			if( $this->areConditionsTrue( $reaction['conditions'], $wildcards ) ) {
				# Mark this reaction as having been used recently as to reduce its preferability for a little while..
				if( $reaction['last_used'] == null ) {
					// if reaction never before used then append a last_used record (as now).. // TODO: use real user_id below..
					$this->framework->runSql(
						'INSERT INTO agent_reactions_used ( reaction_id, user_id, last_used ) VALUES ( :reaction_id, 0, now() )',
						array( ':reaction_id' => $reaction['next_id'] )
					);
				}
				else {
					// if reaction used before then update its last_used record to now.. // TODO: use real user_id below..
					$this->framework->runSql(
						'UPDATE agent_reactions_used SET last_used = now() WHERE user_id = 0 AND reaction_id = :reaction_id',
						array( ':reaction_id' => $reaction['next_id'] )
					);
				}

				# Break out with the reaction_id and actions to perform
				$actions     = $reaction['actions'];
				$reaction_id = $reaction['next_id'];
				break;
			}
		}
		return array( 'reaction_id' => $reaction_id, 'actions' => $actions );
	}

	private function randomReactionSelection( $meaning_id, $wildcards ) {
		$actions = '';
		$reactions = $this->framework->runSql(
			"SELECT id as reaction_id, conditions, actions FROM agent_reactions WHERE meaning_id = :meaning_id AND functional <> 'F' ORDER BY priority",
			array( ':meaning_id' => $meaning_id )
		);
		$number = count( $reactions );
		$invalids = array();
		if( $number == 0 ) { return array( 'reaction_id' => null, 'actions' => $actions ); }
		do {
			$selection = mt_rand( 0, $number );  // Mersenne Twister random number generation
			if( !in_array( $selection, $invalids ) ) {
				if( $this->areConditionsTrue( $reactions[$selection]['conditions'] ) ) {
					return array( 'reaction_id' => $reactions[$selection]['reaction_id'], 'actions' => $reactions[$selection]['actions'] );
				}
				else { $invalids[] = $selection; }
			}
		} while( count( $invalids ) < $number );
		return array( 'reaction_id' => null, 'actions' => $actions ); 
	}

	private function getLengthOfRecognizer( $recognizer ) {
		$recognizer = preg_replace('/\[[^\]]*\]/','*',$recognizer); 
		return strlen( $recognizer );
	}

	// If statement matches recognizer (converted) then return true and populate 
	public function compareStatementToRecognizer( $statement, $recognizer, $partial = false ) {
		// TODO: (1) Validate that brackets are matched; (2) Respect escaped brackets in recognizer

		// Convert recognizer to regex and collect wildcard names
		$recognizer = preg_quote( $recognizer,'/' );                         // escapes characters with non-literal regex meaning
		$recognizer = str_replace( '\[','[', $recognizer );                  // restores [ from escape
		$recognizer = str_replace( '\]',']', $recognizer );                  // restores ] from escape
		$regex      =  preg_replace( "/\[([^]]*)\]/","(.*)", $recognizer );  // translate recognizer to regular expression
		preg_match_all( "/\[[^]]*\]/", $recognizer, $wildcard_names );       // gets array of wildcard names

		// Check if matched and, if so, collect wildcard assignments
		if( $partial ) { $matched = preg_match('/'  . trim( $regex ) . '/',  trim( $statement ), $variable_matches ); }
		else           { $matched = preg_match('/^' . trim( $regex ) . '$/', trim( $statement ), $variable_matches); }
		$wildcards = array();
		if($matched) {
			// collect statement parameters and form into associate array
			$n = 0;
			foreach($wildcard_names[0] as $wildcard_name) {
				$wildcards[trim( $wildcard_name, '[]' )] = $variable_matches[++$n];
			}
		}
 
		return array( $matched, $wildcards );
	}

	private function writeInWildcardValues( $text, $wildcards, $invert = false ) {
		foreach( $wildcards as $wildcard => $value ) {
			if( $invert ) {
				$value = $this->framework->replaceWords( $this->inversions, $value, false );
			}
			$text = str_replace( '[' . trim( $wildcard, "[]" ) . ']', $value, $text );
		}
		return $text;
	}

	// Evaluate a reaction's conditions
	private function areConditionsTrue( $conditions, $wildcards ) {
		// empty should evaluate to true
		$conditions = trim( $conditions );
		if( $conditions == '' ) { return true; }

		// Resolve every registered conditional call -- (is .. ".."), (isall ..),
		// (isany ..), (iscan ..) -- to a boolean literal (1 or 0) by dispatching
		// to its is* method. Only text that matches a registered pattern is ever
		// executed; anything else is left in place and rejected below. This
		// replaces the old eval() of attacker-controllable condition text, which
		// allowed arbitrary PHP execution from the agent_reactions.conditions
		// field (editable via the save_reaction request).
		//
		// Note: unlike the old eval(), all conditionals are evaluated eagerly
		// (no &&/|| short-circuit). The is* predicates are side-effect-free
		// queries, so this only changes performance, not behavior.
		foreach( $this->conditionals as $conditional ) {
			$conditions = preg_replace_callback(
				$conditional['regex_pattern'],
				function( $matches ) use ( $conditional, $wildcards ) {
					return $this->callConditional( $conditional['php_function'], $matches, $wildcards ) ? '1' : '0';
				},
				$conditions
			);
		}

		// What remains must be a pure boolean expression over the literals 1/0,
		// the keywords and/or/not, parentheses and whitespace. Evaluate it with a
		// recursive-descent parser -- never eval().
		return $this->evaluateBoolean( $conditions );
	}

	// Dispatch a single matched conditional to its is* method. The method name is
	// read from the registered php_function template; the regex capture groups
	// become the leading arguments (in order) followed by $wildcards. This is the
	// only place a condition fragment is turned into a call, and it can only call
	// a method that actually exists on this object.
	private function callConditional( $template, $matches, $wildcards ) {
		if( !preg_match( '/\$this->(\w+)\s*\(/', $template, $name ) ) { return false; }
		$method = $name[1];
		if( !method_exists( $this, $method ) ) { return false; }
		$arguments = array();
		for( $index = 1; $index < count( $matches ); $index++ ) {
			$arguments[] = $matches[ $index ];
		}
		$arguments[] = $wildcards;
		return (bool) call_user_func_array( array( $this, $method ), $arguments );
	}

	// Safely evaluate a boolean expression containing only 1/0, and/or/not,
	// parentheses and whitespace. Returns false (and logs) on anything outside
	// that grammar, so unrecognized condition text can never execute.
	private function evaluateBoolean( $expression ) {
		$expression = preg_replace( '/\bnot\b/i', ' ! ',  $expression );
		$expression = preg_replace( '/\band\b/i', ' && ', $expression );
		$expression = preg_replace( '/\bor\b/i',  ' || ', $expression );

		if( preg_match( '/[^01!&|()\s]/', $expression ) ) {
			if( isset( $this->framework ) ) {
				$this->framework->logMessage( "Rejected unsafe agent condition: {$expression}", WARNING );
			}
			return false;
		}

		// Tokenize into: 1 0 ! ( ) && ||
		$tokens = array();
		$length = strlen( $expression );
		for( $index = 0; $index < $length; $index++ ) {
			$character = $expression[ $index ];
			if( ctype_space( $character ) ) { continue; }
			if( $character === '&' || $character === '|' ) {
				$next = ( $index + 1 < $length ) ? $expression[ $index + 1 ] : '';
				if( $character !== $next ) { return false; }  // lone & or | is invalid
				$tokens[] = $character . $character;
				$index++;
				continue;
			}
			$tokens[] = $character;  // one of 0 1 ! ( )
		}

		$position = 0;
		$value = $this->parseBooleanOr( $tokens, $position );
		if( $position !== count( $tokens ) ) { return false; }  // trailing garbage
		return $value;
	}

	private function parseBooleanOr( $tokens, &$position ) {
		$value = $this->parseBooleanAnd( $tokens, $position );
		while( isset( $tokens[ $position ] ) && $tokens[ $position ] === '||' ) {
			$position++;
			$right = $this->parseBooleanAnd( $tokens, $position );
			$value = $value || $right;
		}
		return $value;
	}

	private function parseBooleanAnd( $tokens, &$position ) {
		$value = $this->parseBooleanUnary( $tokens, $position );
		while( isset( $tokens[ $position ] ) && $tokens[ $position ] === '&&' ) {
			$position++;
			$right = $this->parseBooleanUnary( $tokens, $position );
			$value = $value && $right;
		}
		return $value;
	}

	private function parseBooleanUnary( $tokens, &$position ) {
		if( isset( $tokens[ $position ] ) && $tokens[ $position ] === '!' ) {
			$position++;
			return ! $this->parseBooleanUnary( $tokens, $position );
		}
		return $this->parseBooleanPrimary( $tokens, $position );
	}

	private function parseBooleanPrimary( $tokens, &$position ) {
		if( !isset( $tokens[ $position ] ) ) { return false; }
		$token = $tokens[ $position ];
		if( $token === '1' ) { $position++; return true; }
		if( $token === '0' ) { $position++; return false; }
		if( $token === '(' ) {
			$position++;
			$value = $this->parseBooleanOr( $tokens, $position );
			if( isset( $tokens[ $position ] ) && $tokens[ $position ] === ')' ) { $position++; }
			return $value;
		}
		$position++;  // unexpected token; consume and treat as false
		return false;
	}

	// (is {=|>|<}n "object")
	private function isQuantity( $adjective, $number, $object, $wildcards ) {
		$object  = $this->writeInWildcardValues( $object, $wildcards );
		$pattern = preg_replace( '/\[([^]]*)\]/', '%', $object );
		$rows    = $this->framework->runSql(
			'SELECT COUNT(*) AS quantity FROM agent_memories WHERE memory LIKE :pattern',
			array( ':pattern' => $pattern )
		);
		$counted = ( is_array( $rows ) && count( $rows ) > 0 ) ? $rows[0]['quantity'] : 0;
		//print "isQuantity: adjective = \"$adjective\", number = \"$number\", object = \"$object\", counted = \"$counted\"\n"; 
		switch( $adjective ) {
			case '>': if( $counted > $number ) { $answer = true; } else { $answer = false; } break;
			case '<': if( $counted < $number ) { $answer = true; } else { $answer = false; } break;
			case '=':
			case '':
			default: if( $counted == $number ) { $answer = true; } else { $answer = false; } break;
		}
		//print "Answer: " . ( $answer ? "TRUE\n" : "FALSE\n" );
		return $answer;
	}

	// (isall "subject" {=|>|<} "object")
	private function isAll( $subject, $relation, $object, $wildcards ) {
		$object = $this->writeInWildcardValues( $subject, $wildcards );
		$object = $this->writeInWildcardValues( $object, $wildcards );

		// Collect wildcard variable values from all matching subject memories
		$subject_records = $this->framework->runSql(
			'SELECT memory FROM agent_memories WHERE memory LIKE :pattern',
			array( ':pattern' => preg_replace( '/\[([^]]*)\]/', '%', $subject ) )
		);
		$subject_wildcards = array();
		$next = -1;
		foreach( $subject_records as $subject_record ) {
			list( $matched, $subject_wildcards[++$next] ) = $this->compareStatementToRecognizer( $subject_record, $subject );
		}

		// Collect wildcard variable values from all matching object memories
		$object_records = $this->framework->runSql(
			'SELECT memory FROM agent_memories WHERE memory LIKE :pattern',
			array( ':pattern' => preg_replace( '/\[([^]]*)\]/', '%', $object ) )
		);
		$object_wildcards = array();
		$next = -1;
		foreach( $object_records as $object_record ) {
			list( $matched, $object_wildcards[++$next] ) = $this->compareStatementToRecognizer( $object_record, $object );
		}

		// Do all values in subject_wildcards have the specified relation to a relative value in object_wildcards?
		// TODO: for each $subject_wildcards iteration, is there an iteration of $object_wildcards with the specified relation to its value set?

		print "isAll: subject = \"$subject\", relation = \"$relation\", object = \"$object\"\n"; 
		return true;
	}

	// (isany "subject" {=|>|<} "object")
	private function isAny( $subject, $relation, $object, $wildcards ) {
		$object = $this->writeInWildcardValues( $subject, $wildcards );
		$object = $this->writeInWildcardValues( $object, $wildcards );
		print "isAny: subject = \"$subject\", relation = \"$relation\", object = \"$object\"\n"; 
		return true;
	}

	// (isCan "object")
	private function isCan( $object, $wildcards ) {
		$object = $this->writeInWildcardValues( $object, $wildcards );
		print "isCan: object = \"$object\"\n"; 
		return true;
	}

	private function executeActions( $actions, $wildcards ) {
		$verbal = '';
		$nonverbal = '';
		$lines = explode( "\n", trim( $actions ) );
		foreach( $lines as $line ) {
			$line = trim( $line );
			$line = preg_replace( '/\/\/.*/', '', $line );  // Remove comments
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

	private function actionSay( $params, $wildcards ) {
		return array( $this->writeInWildcardValues( $params[1], $wildcards, true ) . ' ', '' );
	}

	private function actionRemember( $params, $wildcards ) {
		// TODO: add date/time, if provided for expiration
		$memory = $this->writeInWildcardValues( $params[1], $wildcards );
		// Store the memory if not already present (bound value; no addslashes).
		$this->updateElseInsert( 'agent_memories', array( 'memory' => $memory ), 'memory = :where_memory', array( ':where_memory' => $memory ) );
		return array( '', ";remembered" );
	}

	private function actionRecall( $params, &$wildcards ) {
		// create search pattern by applying wildcards from user statement
		$seeking = trim( $this->writeInWildcardValues( $params[1], $wildcards ) );

		// extract any other wildcards from memory
		$sql_pattern = preg_replace( '/\[([^]]*)\]/','%', $seeking );
		$matches = $this->framework->runSql(
			'SELECT memory FROM agent_memories WHERE memory LIKE :pattern',
			array( ':pattern' => $sql_pattern )
		);
		foreach( $matches as $match ) {
			list( $matched, $new_wildcards ) = $this->compareStatementToRecognizer( strtolower( $match['memory'] ), strtolower( $seeking ) );
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
		
		return array( '', ';recalled' );
	}

	private function actionForget( $params, $wildcards ) {
		$pattern = preg_replace( '/\[([^]]*)\]/','%', $params[1] );
		$this->framework->runSql(
			'DELETE FROM agent_memories WHERE memory LIKE :pattern',
			array( ':pattern' => $pattern )
		);
		return array( '', ';forgot' );
	}

	private function actionExpectAs( $params, $wildcards ) {
		// TODO: put in a session stack: What if a subsequent interpret as (or other articulation) settles this within the same request?
		if( !isset( $_SESSION['agent'] ) ) {  $_SESSION['agent'] = array(); }
		if( !isset( $_SESSION['agent']['expecting'] ) ) {  $_SESSION['agent']['expecting'] = array(); }
		$from = $this->writeInWildcardValues( trim( $params[1] ), $wildcards );
		$to   = $this->writeInWildcardValues( trim( $params[2] ), $wildcards );
		$_SESSION['agent']['expecting'][$from] = $to;
		return array( '', ';expecting' );
	}

	private function actionInterpretAs( $params, $wildcards ) {
		$statement = $this->writeInWildcardValues( trim( $params[1] ), $wildcards );
		$response = $this->reactTo( $statement );
		return array( $response['verbal'], $response['nonverbal'] );
	}

	private function actionWhatIf( $params, $wildcards ) {
		print "TODO: actionWhatIf()\n";
		return array( print_r( $params, true ), '' );
	}

	private function actionHowCould( $params, $wildcards ) {
		print "TODO: actionHowCould()\n";
		return array( print_r( $params, true ), '' );
	}

	private function actionWorkToward( $params, $wildcards ) {
		print "TODO: actionWorkToward()\n";
		return array( print_r( $params, true ), '' );
	}

	private function actionTest( $params, $wildcards ) {
		$conditions = $this->writeInWildcardValues( trim( $params[1] ), $wildcards );
		$truth = $this->areConditionsTrue( $conditions, array() );
		if( $truth ) { $verbal = 'true'; }
		else         { $verbal = 'false'; }
		return array( $verbal, '' );
	}

} // end of AgentModels class
