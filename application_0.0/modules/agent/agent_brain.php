<?php

# File: ~/application_0.0/modules/agent/agent_brain.php
# Purpose: provide agent's conversational identification and reaction logic
# 2012-09-17 ... created.

# Class Declaration for agent Module's Controllers
class AgentBrain 
{
	private $articulations;  // array of eact statement - reaction/response cycle and whether real or contemplated

	// Constructor
	public function __construct( $user_statement ) {
		$this->articulations = array();
	} // end of __construct method

	// Trades statement for response/reaction + internal reactions 
	public function getReaction( $statement, $actual = true ) {
		// Identify Meaning
		$meaning_id = $this->getMeaning( $statement );

		// Get Lead Appropriate Reaction ( associative array of: response, action_sequence, meaning_id, reaction_id )
		$reaction = $this->getLeadAppropriateReaction( $meaning_id );

		// Actuate or Contemplate ( associative array of: response, triggers )
		$results = $this->executeReaction( $reaction, $actual ); 

		return $results;
	} // end of getReaction() 

	public function getMeaning( $statement ) {
	}

	public function getLeadAppropripriateReaction( $meaning_id ) {
	}

	public function executeReaction( $reaction, $actual = true ) {
	}

	// Push memory to a stack (so can affect memory during contemplation and be able to pop it back again)
	public function pushMemory() {
	}

	// Pop memory from the stack (for when done contemplating)
	public function popMemory() {
	}


} // end of AgentBrain class
