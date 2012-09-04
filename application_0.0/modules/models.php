<?php

# File: ~/code_1.0/model.php
# Purpose: base class for all models 
#
# 2011-10-28:MCT: Created.

const DB_REGET = 1;

# The Base Class for All Models 
class Models {

	protected $framework;    // reference to system parameters and basic functions


	// Run a Query Returning any Results
	public function runQuery($param_sql) {
		// TODO: log the query
		return $this->system->database_connection->exec($param_query);
	}

	// Get Record Count(s)
	public function getRecordCount($param_model) {
	}

	// Get Record(s)
	public function getRecords($param_record) {
	}

	// setRecord(s) 
	public function setRecords($param_record, $param_ensure_exists = false) {
	}

	// addRecord(s)
	public function addRecords($param_model) {
		// Note: might need to come up with a way to only append where planned and not all levels of record
	}

	// ------------------ Method for Array Structure / Database Sychronization ---------------------
	public function synchronize( $data, $options ) {
		$data = array ( 'table_a' => array ( 'id' => array ( 'value' => 'a value', 'type' => 'varchar(15)', 'default' =>'', 'filter' => '/[A-Za-z ]/' ) ) );
	}

	public function getRecordTemplate() {
	}

	public function buildTables( $drop_and_rebuild = false ) {
		$success  = true;  // initially presume success, but mark as failed if any part fails. 
		$prefix = $this->framework->getDatabaseName() . '.' . $this->framework->getDatabasePrefix();
		$tables = $this->framework->getControllerTables();
		foreach( $tables as $table => $columns ) {
			// Drop or else check existence of table..
			if( $drop_and_rebuild === true ) {
				$sql = "DROP TABLE $prefix$table";
				$this->framework->runSql( $sql );  // TODO: error trap
				$table_exists = false;
			}
			else {
				$table_exists = isTableExist( $this->framework->getDatabasePrefix . "$table" );	
			}
	
			// Does the table exist?
			if( $table_exists === false ) {
				// Table does not exist so build it and return:
				$sql = "CREATE TABLE $prefix$table (";
				$primary_keys = '';
				foreach( $columns as $column => $attributes ) {
					if( isset( $attributes['type'] ) ) { $sql .= " $column " . $attributes['type']; }
					else {
						$message = "Column \"$column\" type is not specified in registration for table $prefix$table.";
						$this->logMessage( $message, CRITICAL );
						continue; // try to continue with the rest of the columns..
					}
					if( isset( $attributes['default'] ) ) { 
						$value = $this->translateValueForSqlInsert( $attributes['default'] );
						if( $value === null ) {
							$success = false;
							$this->framework->logMessage( "Failed trying to create table: $prefix$table", WARNING );
							continue;
						}
						else { $sql .= " DEFAULT $value"; }
					}
					if( isset( $attributes['key'] ) ) {
						switch( strtolower( $attributes['key'] ) ) {
							case 'primary':
								$sql .= " NOT NULL AUTO_INCREMENT "; 
								$primary_keys .= "$column, ";
								break;
						}
					} // end of key condition..
					$sql .= ', ';
				} // end of loop through columns
				if( $primary_keys !== '' ) {
					$primary_keys = trim( $primary_keys, ', ' );
					$sql .= " PRIMARY KEY( $primary_keys ) ";
				}
				$sql = trim( $sql, ', ' );
				$sql .= ')';
				$result = $this->framework->runSql( $sql );
				if( $result === null ) {
					$success = false;
					$this->framework->logMessage( "Failed trying to create table: $sql", WARNING );
				}
			}
			else 
			{
				// Table exists so perform any necessary alterations:
				print "WORKING..<br/>\n";
				$sql = "";
			}
		} // end of loop through tables
		return $success;
	}

	// Tranlsates PHP data value for insertion into a SQL statement, or returns null upon failure
	private function translateValueForSqlInsert( $value ) {
		switch( gettype( $value ) ) {
			case 'boolean':
				if( $value === true ) { $value = 'true'; }
				else { $value = 'false'; }
				break;

			case 'integer':
			case 'double':
				break;

			case 'string':
				$value = "'" . addslashes( $value ) . "'";
				break;

			case 'array':
			case 'object':
			case 'resource': 
				$this->framework->logMessage( 'Failed translating the following value for an SQL insert, because it is an unsupported value type (' . gettype( $value ) . '): ' . $this->framework->makeSingleLine( print_r( $value , true ) ) );
				$value = null;
				break;

			case 'NULL':
				$value = 'NULL';
				break;

			default:
				$this->framework->logMessage( 'Failed translating the following value for an SQL insert, because it is an uknown value type: ' . $this->framework->makeSingleLine( print_r( $value , true ) ) );
				$value = null;
		}
		return $value;
	}

	private function isTableExist( $table_name, $database_name = null ) {
		if( $database_name === null ) { $database_name = $this->framework->getDatabaseName(); }
		$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '$database_name' AND table_name = '$table_name'";
		$results = $this->framework->runSql( $sql );
		if( count( $results ) > 0 ) { return true; } else { return false; }
	}

	private function getTableDetails( $table_name, $database_name = null ) {
		if( $database_name === null ) { $database_name = $this->framework->getDatabaseName(); }
		$sql = "SHOW COLUMNS FROM $database_name.$table_name";
		$results = $this->framework->runSql( $sql );
		var_dump( $results );
		// WORKING..
	}

	// ------------------ Methods for Building SQL ---------------------
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

}

