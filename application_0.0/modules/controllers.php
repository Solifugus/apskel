<?php

# File: ~/framework_1.0/controller.php
# Purpose: base class for all controllers

# The Base Class for All Controllers 
class Controllers
{
  protected $models;                // instance of the models class like-named with the controller class
  protected $views;                 // instance of the views class like-named with the controller class

  protected $framework;                // systems settings and basic functions

	public function buildSelect( $tables, $fields, $where ) {
		if( is_array( $fields ) ) {
			$fields_selectable = '';
			foreach( $fields as $field ) {
				$fields_selectable .= "$field, ";
			}
			$fields_selectable = trim( $fields_selectable, ', ' );
		}
		else {
			$fields_selectable = $fields;
		}

		$sql = "SELECT $fields_selectable FROM $tables WHERE $where";
		$results = $this->runSql( $sql );
		return $results;
	}

	public function buildUpdateSql( $tables, $fields, $where ) {
		if( is_array( $fields ) ) {
			$fields_updateable = '';
			foreach( $fields as $field => $value ) {
				$value = addslashes( $value );
				$fields_updateable .= "$field = $value, ";
			}
			$fields_updateable = trim( $fields_updateable, ', ' );
		}
		else {
			$fields_updateable = $fields;
		}

		$sql = "UPDATE $tables SET $fields_updateable WHERE $where";
		return $sql;
	}

	public function buildInsertSql( $tables, $fields, $where ) {
		if( is_array( $fields ) ) {
			$fields_insertable = '';
			$fields_selectable = '';
			foreach( $fields as $field => $value ) {
				$value = addslashes( $value );
				$fields_insertable .= "$value, ";
				$fields_selectable .= "$field, ";
			}
			$fields_insertable = trim( $fields_insertable, ', ' );
			$fields_selectable = trim( $fields_selectable, ', ' );
		}
		else {
			$this->framework->logMessage( 'The ->buildInsert() method in the ' . __FILE__ . ' file expected an associative array as its $fields parameter but an array was not provided.', CRITICAL );
			return null;
		}

		$sql = "INSERT INTO $tables ( $fields_selectable ) VALUES ( $fields_insertable ) WHERE $where";
		return $sql;
	}

	// Checks if record/s already exist and updates given fields (if exists) or inserts a new record with given fields (if does not exist)
	// @param $tables -- one or more tables plus any associations (e.g. "accounts a LEFT JOIN transactions t ON a.id = t.account_id")
	// @param $fields -- associative array of field names and respective values (e.g. ('account_id' => 456, 'description' => 'Bag of Apples', 'sold_on' => '2012-04-30'))
	// @param $where  -- the where clause to identify if the record/s already exist and/or to update it/them. 
	public function updateElseInsert( $tables, $fields, $where ) {
		// Prepare variables..
		$fields_selectable = '';
		$fields_updateable = '';
		$fields_insertable = '';
		foreach( $fields as $field => $value ) {
			$fields_selectable .= "$field, ";
			$fields_updateable .= "$field = $value, ";
			$fields_insertable .= "$value, ";
		}
		$fields_updateable = trim( $fields_updateable, ', ');
		$fields_insertable = trim( $fields_updateable, ', '); 

		// See if already there..
		$sql = "SELECT $fields_selectable FROM $tables WHERE $where";
		$results = $this->runSql( $sql );

		// If is there, run an update..
		if( count( $results ) > 0 ) {
			$sql = "UPDATE $tables SET $fields_updateable WHERE $where";
			$results = $this->runSql( $sql );
		}
		// If not there, run an insert..
		else {
			$sql = "INSERT INTO $tables ( $fields_selectable ) VALUES ( $fields_insertable )";
			$results = $this->runSql( $sql );
		}
		return $results;
	}

} // End of Controller Class


