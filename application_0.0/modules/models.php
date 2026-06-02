<?php

# File: ~/code_1.0/model.php
# Purpose: base class for all models 
#
# 2011-10-28:MCT: Created.

const DB_REGET = 1;

# The Base Class for All Models 
class Models {

	protected $framework;    // reference to system parameters and basic functions

	public function __construct() {
	}

	// Run a Query Returning any Results
	public function runQuery($param_sql) {
		// TODO: log the query
		return $this->system->database_connection->exec($param_query);
	}

	// Get Record Count(s): $to_count (table) & $where--or else $to_count as a data model array
	public function getRecordCount( $to_count, $where = '' ) {
		if( is_array( $to_count ) ) {
			// TODO: build count query according to $model array structure..
			$sql = '';
		}
		else {
			$sql = "SELECT COUNT(*) AS quantity FROM {$to_count} WHERE {$where}";
		}
		$result = $this->framework->runSql( $sql );
		if( $result === null ) {
			// TODO: log this..
			return null;
		}
		return $result[0]['quantity'];
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
		$success = true;  // initially presume success, but mark as failed if any part fails.
		$prefix  = $this->framework->getDatabasePrefix();  // table-name prefix ('' by default)
		$driver  = $this->framework->getDatabaseDriver();
		$tables  = $this->framework->getModuleTables();
		foreach( $tables as $table => $columns ) {
			$table_name = $prefix . $table;

			// Drop (optionally) then check existence..
			if( $drop_and_rebuild === true ) {
				$this->framework->runSql( "DROP TABLE IF EXISTS {$table_name} CASCADE" );
				$table_exists = false;
			}
			else {
				$table_exists = $this->isTableExist( $table_name );
			}

			// Already there (and not rebuilding): leave it. ALTER/migration is a TODO.
			if( $table_exists ) { continue; }

			// Build the column definitions.
			$definitions  = array();
			$primary_keys = array();
			foreach( $columns as $column => $attributes ) {
				$is_primary = isset( $attributes['key'] ) && strtolower( $attributes['key'] ) === 'primary';
				if( $is_primary ) {
					// Auto-incrementing primary key (driver-specific).
					$definitions[]  = "{$column} " . ( $driver === 'pgsql' ? 'SERIAL' : 'INTEGER NOT NULL AUTO_INCREMENT' );
					$primary_keys[] = $column;
					continue;
				}
				if( !isset( $attributes['type'] ) ) {
					$this->framework->logMessage( "Column \"{$column}\" has no type in registration for table {$table_name}.", CRITICAL );
					$success = false;
					continue;
				}
				$definitions[] = "{$column} " . $this->normalizeColumnType( $attributes['type'], $driver )
				               . $this->renderColumnDefault( $attributes );
			}
			if( count( $primary_keys ) > 0 ) {
				$definitions[] = 'PRIMARY KEY (' . implode( ', ', $primary_keys ) . ')';
			}

			$sql = "CREATE TABLE {$table_name} (\n\t" . implode( ",\n\t", $definitions ) . "\n)";
			$result = $this->framework->runSql( $sql );
			if( $result === null ) {
				$success = false;
				$this->framework->logMessage( "Failed trying to create table: {$sql}", WARNING );
			}
		} // end of loop through tables
		return $success;
	}

	// Normalize a registration column type to the target SQL dialect.
	private function normalizeColumnType( $type, $driver = 'pgsql' ) {
		$type = trim( $type );
		if( $driver === 'pgsql' ) {
			// Postgres has no integer "display widths": INT(11) -> INTEGER, TINYINT(1) -> BOOLEAN, etc.
			if( preg_match( '/^tinyint\s*\(\s*1\s*\)$/i', $type ) ) { return 'BOOLEAN'; }
			if( preg_match( '/^big\s*int/i', $type ) )              { return 'BIGINT'; }
			if( preg_match( '/^small\s*int/i', $type ) )            { return 'SMALLINT'; }
			if( preg_match( '/^(int|integer|mediumint|tinyint)\s*(\(\s*\d+\s*\))?$/i', $type ) ) { return 'INTEGER'; }
			if( preg_match( '/^datetime$/i', $type ) )              { return 'TIMESTAMP'; }
		}
		return strtoupper( $type );
	}

	// Render a "DEFAULT ..." clause from a column's registration attributes (or '' if none).
	private function renderColumnDefault( $attributes ) {
		if( !array_key_exists( 'default', $attributes ) ) { return ''; }
		$default = $attributes['default'];
		// The old registration files use the string 'null'/'NULL' to mean SQL NULL.
		if( $default === null || ( is_string( $default ) && strtolower( $default ) === 'null' ) ) {
			return ' DEFAULT NULL';
		}
		if( is_bool( $default ) ) { return ' DEFAULT ' . ( $default ? 'TRUE' : 'FALSE' ); }
		// Booleans are often written as 0/1 in the legacy registrations.
		$type = isset( $attributes['type'] ) ? strtolower( $attributes['type'] ) : '';
		if( strpos( $type, 'bool' ) !== false ) {
			$falsey = ( $default === 0 || $default === '0' || $default === false || $default === '' );
			return ' DEFAULT ' . ( $falsey ? 'FALSE' : 'TRUE' );
		}
		if( is_int( $default ) || is_float( $default ) || ( is_string( $default ) && is_numeric( $default ) ) ) {
			return " DEFAULT {$default}";
		}
		return " DEFAULT '" . str_replace( "'", "''", $default ) . "'";
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
		$driver = $this->framework->getDatabaseDriver();
		if( $driver === 'pgsql' ) {
			// In Postgres, tables live in a schema (default 'public'), not the database name.
			$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = :table_name";
			$params = array( ':table_name' => $table_name );
		}
		else {
			if( $database_name === null ) { $database_name = $this->framework->getDatabaseName(); }
			$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = :schema AND table_name = :table_name";
			$params = array( ':schema' => $database_name, ':table_name' => $table_name );
		}
		$results = $this->framework->runSql( $sql, $params );
		return is_array( $results ) && count( $results ) > 0;
	}

	private function getTableDetails( $table_name, $database_name = null ) {
		if( $database_name === null ) { $database_name = $this->framework->getDatabaseName(); }
		$sql = "SHOW COLUMNS FROM $database_name.$table_name";
		$results = $this->framework->runSql( $sql );
		var_dump( $results );
		// WORKING..
	}

	// ------------------ Methods for Building SQL ---------------------
	// ------------------ Safe (parameterized) record helpers ---------------------
	// These run prepared statements with bound values. Pass plain PHP values
	// (NOT pre-quoted strings). This is the preferred data-access API; the
	// build*Sql() string builders further below are legacy and injection-prone.

	// Run a SELECT and return the rows. Placeholders in $where are bound from $params.
	public function buildSelect( $tables, $fields, $where = '', $params = array() ) {
		$columns = is_array( $fields ) ? implode( ', ', $fields ) : $fields;
		$sql = "SELECT {$columns} FROM {$tables}";
		if( $where !== '' && $where !== null ) { $sql .= " WHERE {$where}"; }
		return $this->framework->runSql( $sql, count( $params ) ? $params : null );
	}

	// INSERT one row from an associative array of column => value. Returns the new
	// row's id (RETURNING on pgsql, lastInsertId elsewhere), or null if $id_column is null.
	public function insertRecord( $table, $fields, $id_column = 'id' ) {
		$columns      = array_keys( $fields );
		$placeholders = array();
		$params       = array();
		foreach( $columns as $column ) {
			$placeholders[]       = ":{$column}";
			$params[":{$column}"] = $fields[$column];
		}
		$sql = "INSERT INTO {$table} ( " . implode( ', ', $columns ) . " ) VALUES ( " . implode( ', ', $placeholders ) . " )";
		if( $id_column !== null && $this->framework->getDatabaseDriver() === 'pgsql' ) {
			$rows = $this->framework->runSql( $sql . " RETURNING {$id_column}", $params );
			return ( is_array( $rows ) && count( $rows ) > 0 ) ? $rows[0][$id_column] : null;
		}
		$this->framework->runSql( $sql, $params );
		return ( $id_column === null ) ? null : $this->framework->getLastInsertId( $id_column );
	}

	// UPDATE rows from an associative array of column => value. Placeholders in
	// $where are bound from $where_params (kept distinct from the SET values).
	public function updateRecords( $table, $fields, $where = '', $where_params = array() ) {
		$assignments = array();
		$params      = array();
		foreach( $fields as $column => $value ) {
			$assignments[]            = "{$column} = :set_{$column}";
			$params[":set_{$column}"] = $value;
		}
		$sql = "UPDATE {$table} SET " . implode( ', ', $assignments );
		if( $where !== '' && $where !== null ) { $sql .= " WHERE {$where}"; }
		foreach( $where_params as $key => $value ) { $params[$key] = $value; }
		return $this->framework->runSql( $sql, $params );
	}

	// Update matching rows if any exist, else insert a new row. $fields is an
	// associative array of plain values; $where (with bound $where_params) identifies
	// existing rows.
	public function updateElseInsert( $table, $fields, $where = '', $where_params = array() ) {
		$exists_sql = "SELECT 1 FROM {$table}";
		if( $where !== '' && $where !== null ) { $exists_sql .= " WHERE {$where}"; }
		$existing = $this->framework->runSql( $exists_sql, count( $where_params ) ? $where_params : null );
		if( is_array( $existing ) && count( $existing ) > 0 ) {
			return $this->updateRecords( $table, $fields, $where, $where_params );
		}
		return $this->insertRecord( $table, $fields, null );
	}

	// ------------------ Legacy SQL string builders (DEPRECATED) ---------------------
	// WARNING: these interpolate values into SQL and rely on callers pre-quoting
	// strings (a leading "'" means "quote me"), which is injection-prone. They remain
	// only for the not-yet-migrated agent module. New code MUST use the parameterized
	// helpers above (buildSelect/insertRecord/updateRecords/updateElseInsert).

	public function buildUpdateSql( $tables, $fields, $where ) {
		if( is_array( $fields ) ) {
			$fields_updateable = '';
			foreach( $fields as $field => $value ) {
				if( substr( trim( $value ), 0, 1) == "'" ) { 
					$value = $this->framework->quoteForDatabase( trim( $value, "'" ) );
					$value = str_replace( '\"', '"', $value );
				}
				elseif( !is_numeric( $value ) ) {
					$this->framework->logMessage( 'The ->buildInsert() method in the ' . __FILE__ . " file was passed an unquoted string ({$value}) to insert.", WARNING );
				}
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

	public function buildInsertSql( $tables, $fields, $where = '' ) {
		if( is_array( $fields ) ) {
			$fields_insertable = '';
			$fields_selectable = '';
			foreach( $fields as $field => $value ) {
				if( substr( trim( $value ), 0, 1) == "'" ) { 
					$value = $this->framework->quoteForDatabase( trim( $value, "'" ) );
					$value = str_replace( '\"', '"', $value );
				}
				elseif( !is_numeric( $value ) ) {
					$this->framework->logMessage( 'The ->buildInsert() method in the ' . __FILE__ . " file was passed an unquoted string ({$value}) to insert.", WARNING );
				}
				$fields_insertable .= "$value, ";
				$fields_selectable .= "$field, ";
			}
			$fields_insertable = ltrim( rtrim( $fields_insertable, ', ' ) );
			$fields_selectable = ltrim( rtrim( $fields_selectable, ', ' ) );
		}
		else {
			$this->framework->logMessage( 'The ->buildInsert() method in the ' . __FILE__ . ' file expected an associative array as its $fields parameter but an array was not provided.', CRITICAL );
			return null;
		}

		if( $where > '' ) { $where = "WHERE {$where}"; }
		$sql = "INSERT INTO $tables ( $fields_selectable ) VALUES ( $fields_insertable ) $where";
		return $sql;
	}

	// Builds and runs insert SQL, returning id of inserted row
	public function insertAndGetId( $id, $tables, $fields, $where = '' ) {
		$sql = $this->buildInsertSql( $tables, $fields, $where );
		$this->framework->runSql( $sql );
		return $this->framework->getLastInsertId( $id );
	}

} // End of Models class

