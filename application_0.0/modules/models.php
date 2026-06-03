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

	// Run an arbitrary SQL statement, optionally with bound parameters, and return
	// the framework's result. Prefer the typed record methods below; reach for this
	// only for statements they cannot express.
	public function runQuery( $param_sql, $param_parameters = null ) {
		return $this->framework->runSql( $param_sql, $param_parameters );
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

	// ==================================================================
	// The model keystone: a generic, registration-aware record API.
	//
	// These are the preferred way for modules to do CRUD. They build on the
	// parameterized helpers further below, and add two guarantees:
	//   * Identifier safety: every table/column name is checked against a strict
	//     identifier pattern (identifiers can't be bound as parameters), and --
	//     when the table is one the module registered -- against the registration
	//     itself, so typos and unexpected columns are rejected.
	//   * Value safety: every value is bound as a prepared-statement parameter,
	//     never interpolated. Registered column "filter" regexes (when present)
	//     are enforced on writes.
	// Criteria arrays are associative column => value:
	//   * value === null      -> "column IS NULL"
	//   * value is an array    -> "column IN (...)" (empty array matches nothing)
	//   * otherwise            -> "column = value"
	// and are AND-joined.
	// ==================================================================

	// SELECT rows from $table matching $criteria. $options may include:
	//   'fields' => '*' | array of column names   (default '*')
	//   'order'  => 'col' | array(col => 'ASC'|'DESC') | array of col names
	//   'limit'  => int,  'offset' => int
	// Returns an array of associative rows (empty array on no match or error).
	public function getRecords( $table, $criteria = array(), $options = array() ) {
		$qualified = $this->qualifyTable( $table );
		if( $qualified === false ) { return array(); }

		// Field list
		$fields = isset( $options['fields'] ) ? $options['fields'] : '*';
		if( is_array( $fields ) ) {
			foreach( $fields as $field ) {
				if( $field !== '*' && !$this->isSafeIdentifier( $field ) ) {
					$this->framework->logMessage( "Unsafe field identifier in getRecords: \"{$field}\".", WARNING );
					return array();
				}
			}
			$field_list = count( $fields ) ? implode( ', ', $fields ) : '*';
		}
		else {
			$field_list = ( $fields === '*' || $this->isSafeIdentifier( $fields ) ) ? $fields : '*';
		}

		$params = array();
		$where  = '';
		if( count( $criteria ) ) {
			$where = $this->buildCriteria( $criteria, $params );
			if( $where === false ) { return array(); }
		}

		$sql = "SELECT {$field_list} FROM {$qualified}";
		if( $where !== '' ) { $sql .= " WHERE {$where}"; }

		if( isset( $options['order'] ) ) {
			$order_by = $this->buildOrder( $options['order'] );
			if( $order_by !== '' ) { $sql .= " ORDER BY {$order_by}"; }
		}
		// LIMIT/OFFSET are cast to int, so they are safe to inline.
		if( isset( $options['limit'] )  && is_numeric( $options['limit'] ) )  { $sql .= ' LIMIT '  . (int) $options['limit']; }
		if( isset( $options['offset'] ) && is_numeric( $options['offset'] ) ) { $sql .= ' OFFSET ' . (int) $options['offset']; }

		$rows = $this->framework->runSql( $sql, count( $params ) ? $params : null );
		return is_array( $rows ) ? $rows : array();
	}

	// Upsert: if rows match $criteria, UPDATE them with $values; otherwise (when
	// $ensure_exists) INSERT a new row combining the criteria's scalar equality
	// columns with $values. Returns true on success, false on validation failure.
	public function setRecords( $table, $values, $criteria = array(), $ensure_exists = true ) {
		$qualified = $this->qualifyTable( $table );
		if( $qualified === false ) { return false; }
		if( !is_array( $values ) || count( $values ) === 0 ) {
			$this->framework->logMessage( "setRecords called with no values for table \"{$table}\".", WARNING );
			return false;
		}
		if( !$this->validateFields( $table, $values ) ) { return false; }

		$exists = ( count( $criteria ) > 0 ) ? ( $this->countRecords( $table, $criteria ) > 0 ) : false;

		if( $exists ) {
			$params      = array();
			$assignments = array();
			foreach( $values as $column => $value ) {
				$assignments[]             = "{$column} = :set_{$column}";
				$params[":set_{$column}"]  = $value;
			}
			$where = $this->buildCriteria( $criteria, $params );  // uses :w_ names, distinct from :set_
			if( $where === false ) { return false; }
			$this->framework->runSql( "UPDATE {$qualified} SET " . implode( ', ', $assignments ) . " WHERE {$where}", $params );
			return true;
		}

		if( $ensure_exists !== true ) { return false; }

		// Insert: identity columns from $criteria seed the new row.
		$record = $values;
		foreach( $criteria as $column => $value ) {
			if( !is_array( $value ) && !array_key_exists( $column, $record ) ) { $record[$column] = $value; }
		}
		return $this->insertRow( $qualified, $record );
	}

	// INSERT one row (associative array) or many (list of associative arrays).
	// Returns the new id for a single row, an array of ids for many, or null.
	public function addRecords( $table, $rows ) {
		$qualified = $this->qualifyTable( $table );
		if( $qualified === false ) { return null; }

		$is_list = is_array( $rows ) && array_key_exists( 0, $rows ) && is_array( $rows[0] );
		$list    = $is_list ? $rows : array( $rows );
		$id_col  = $this->primaryKey( $table );
		$ids     = array();
		foreach( $list as $row ) {
			if( !is_array( $row ) || count( $row ) === 0 ) { $ids[] = null; continue; }
			if( !$this->validateFields( $table, $row ) )   { $ids[] = null; continue; }
			$ids[] = $this->insertRecord( $qualified, $row, $id_col );
		}
		return $is_list ? $ids : $ids[0];
	}

	// DELETE rows matching $criteria. As a safety measure, deleting every row
	// (empty criteria) requires $allow_all = true.
	public function deleteRecords( $table, $criteria = array(), $allow_all = false ) {
		$qualified = $this->qualifyTable( $table );
		if( $qualified === false ) { return false; }
		$params = array();
		$where  = '';
		if( count( $criteria ) ) {
			$where = $this->buildCriteria( $criteria, $params );
			if( $where === false ) { return false; }
		}
		if( $where === '' && $allow_all !== true ) {
			$this->framework->logMessage( "Refusing to delete ALL rows from \"{$table}\" without \$allow_all = true.", WARNING );
			return false;
		}
		$sql = "DELETE FROM {$qualified}";
		if( $where !== '' ) { $sql .= " WHERE {$where}"; }
		$this->framework->runSql( $sql, count( $params ) ? $params : null );
		return true;
	}

	// COUNT rows in $table matching $criteria.
	public function countRecords( $table, $criteria = array() ) {
		$qualified = $this->qualifyTable( $table );
		if( $qualified === false ) { return 0; }
		$params = array();
		$where  = '';
		if( count( $criteria ) ) {
			$where = $this->buildCriteria( $criteria, $params );
			if( $where === false ) { return 0; }
		}
		$sql = "SELECT COUNT(*) AS quantity FROM {$qualified}";
		if( $where !== '' ) { $sql .= " WHERE {$where}"; }
		$result = $this->framework->runSql( $sql, count( $params ) ? $params : null );
		return ( is_array( $result ) && count( $result ) > 0 ) ? (int) $result[0]['quantity'] : 0;
	}

	// ------------------ Array-structure / database synchronization ---------------------
	// Reconcile a data-model structure into the database. $structure is keyed by
	// table name; each table maps column => value, OR column => array('value'=>...,
	// plus optional 'type'/'default'/'filter' metadata) -- the richer "cell" form
	// produced by getModuleTables()-style descriptions. Each table's record is
	// upserted: matched (by the table's primary key when present in the record, or
	// by $options['key']) rows are updated, otherwise a row is inserted.
	// $options:
	//   'rebuild' => true   -- drop and recreate this module's registered tables first
	//   'key'     => 'col' | array(cols)  -- columns that identify an existing row
	// Returns true if every table synchronized successfully.
	public function synchronize( $structure, $options = array() ) {
		if( !is_array( $structure ) ) { return false; }
		if( isset( $options['rebuild'] ) && $options['rebuild'] === true ) {
			$this->buildTables( true );
		}

		$success = true;
		foreach( $structure as $table => $columns ) {
			if( !is_array( $columns ) ) { continue; }

			// Flatten each column to its value, honoring the {'value' => ...} cell form.
			$record = array();
			foreach( $columns as $column => $cell ) {
				$record[$column] = ( is_array( $cell ) && array_key_exists( 'value', $cell ) ) ? $cell['value'] : $cell;
			}

			// Decide which columns identify an existing row.
			if( isset( $options['key'] ) ) {
				$key_columns = is_array( $options['key'] ) ? $options['key'] : array( $options['key'] );
			}
			else {
				$pk = $this->primaryKey( $table );
				$key_columns = ( $pk !== null && array_key_exists( $pk, $record ) ) ? array( $pk ) : array();
			}
			$criteria = array();
			foreach( $key_columns as $key_column ) {
				if( array_key_exists( $key_column, $record ) ) { $criteria[$key_column] = $record[$key_column]; }
			}

			if( $this->setRecords( $table, $record, $criteria, true ) === false ) { $success = false; }
		}
		return $success;
	}

	// A blank record for a table: column => registered default (NULL when none),
	// excluding the auto-generated primary key. With no $table, uses the module's
	// single registered table if it has exactly one.
	public function getRecordTemplate( $table = null ) {
		$tables = $this->framework->getModuleTables();
		if( $table === null ) {
			if( count( $tables ) !== 1 ) { return array(); }
			$columns = reset( $tables );
		}
		else {
			if( !isset( $tables[$table] ) || !is_array( $tables[$table] ) ) { return array(); }
			$columns = $tables[$table];
		}

		$template = array();
		foreach( $columns as $column => $attributes ) {
			if( isset( $attributes['key'] ) && strtolower( $attributes['key'] ) === 'primary' ) { continue; }
			$default = array_key_exists( 'default', $attributes ) ? $attributes['default'] : null;
			if( is_string( $default ) && strtolower( $default ) === 'null' ) { $default = null; }
			$template[$column] = $default;
		}
		return $template;
	}

	// ------------------ Internal helpers for the record keystone ---------------------

	// A name safe to drop into SQL as an identifier (no quoting/binding possible
	// for identifiers, so this is the front-line defence against injection there).
	private function isSafeIdentifier( $name ) {
		return is_string( $name ) && preg_match( '/^[A-Za-z_][A-Za-z0-9_]*$/', $name ) === 1;
	}

	// The registration's column map for a logical (unprefixed) table name, or null
	// if this module did not register such a table.
	private function tableColumns( $table ) {
		$tables = $this->framework->getModuleTables();
		return ( isset( $tables[$table] ) && is_array( $tables[$table] ) ) ? $tables[$table] : null;
	}

	// Validate a logical table name and return the physical name (with any
	// configured prefix) to use in SQL, or false if the name is unsafe.
	private function qualifyTable( $table ) {
		if( !$this->isSafeIdentifier( $table ) ) {
			$shown = is_string( $table ) ? $table : gettype( $table );
			$this->framework->logMessage( "Unsafe table identifier: \"{$shown}\".", WARNING );
			return false;
		}
		if( $this->tableColumns( $table ) === null ) {
			$this->framework->logMessage( "Table \"{$table}\" is not registered for module \"{$this->framework->getModuleName()}\"; proceeding by identifier safety only.", NOTICE );
		}
		$prefix = $this->framework->getDatabasePrefix();
		if( $prefix === null ) { $prefix = ''; }
		return $prefix . $table;
	}

	// The primary-key column name for a logical table, or null if unknown/none.
	private function primaryKey( $table ) {
		$columns = $this->tableColumns( $table );
		if( $columns === null ) { return null; }
		foreach( $columns as $column => $attributes ) {
			if( isset( $attributes['key'] ) && strtolower( $attributes['key'] ) === 'primary' ) { return $column; }
		}
		return null;
	}

	// Build an AND-joined WHERE clause from an associative criteria array, binding
	// values into $params (by reference). Returns the clause, or false if any
	// column name is unsafe. See the keystone header for criteria semantics.
	private function buildCriteria( $criteria, &$params ) {
		$clauses = array();
		foreach( $criteria as $column => $value ) {
			if( !$this->isSafeIdentifier( $column ) ) {
				$this->framework->logMessage( "Unsafe column identifier in criteria: \"{$column}\".", WARNING );
				return false;
			}
			if( $value === null ) {
				$clauses[] = "{$column} IS NULL";
			}
			elseif( is_array( $value ) ) {
				if( count( $value ) === 0 ) { $clauses[] = '1 = 0'; continue; }  // empty IN matches nothing
				$names = array();
				$index = 0;
				foreach( $value as $item ) {
					$placeholder         = ":w_{$column}_{$index}";
					$names[]             = $placeholder;
					$params[$placeholder] = $item;
					$index++;
				}
				$clauses[] = "{$column} IN ( " . implode( ', ', $names ) . " )";
			}
			else {
				$placeholder          = ":w_{$column}";
				$params[$placeholder] = $value;
				$clauses[]            = "{$column} = {$placeholder}";
			}
		}
		return implode( ' AND ', $clauses );
	}

	// Build an ORDER BY list from a column name, a list of names, or a
	// column => direction map. Unsafe column names are skipped (and logged).
	private function buildOrder( $order ) {
		if( is_string( $order ) ) { $order = array( $order => 'ASC' ); }
		if( !is_array( $order ) ) { return ''; }
		$parts = array();
		foreach( $order as $column => $direction ) {
			if( is_int( $column ) ) { $column = $direction; $direction = 'ASC'; }  // numeric-indexed list of names
			if( !$this->isSafeIdentifier( $column ) ) {
				$this->framework->logMessage( "Unsafe column in ORDER BY: \"{$column}\".", WARNING );
				continue;
			}
			$direction = ( strtoupper( trim( (string) $direction ) ) === 'DESC' ) ? 'DESC' : 'ASC';
			$parts[]   = "{$column} {$direction}";
		}
		return implode( ', ', $parts );
	}

	// Validate write values: every column name must be a safe identifier, and --
	// when the table is registered -- a known column whose registered "filter"
	// regex (if any) the value satisfies. Returns false (and logs) on any failure.
	private function validateFields( $table, $values ) {
		$columns = $this->tableColumns( $table );
		foreach( $values as $column => $value ) {
			if( !$this->isSafeIdentifier( $column ) ) {
				$this->framework->logMessage( "Unsafe column identifier \"{$column}\" for table \"{$table}\".", WARNING );
				return false;
			}
			if( $columns === null ) { continue; }  // unregistered table: identifier safety only
			if( !isset( $columns[$column] ) ) {
				$this->framework->logMessage( "Column \"{$column}\" is not registered for table \"{$table}\".", WARNING );
				return false;
			}
			$filter = isset( $columns[$column]['filter'] ) ? $columns[$column]['filter'] : null;
			if( is_string( $filter ) && $filter !== '' && $value !== null ) {
				if( @preg_match( $filter, (string) $value ) !== 1 ) {
					$this->framework->logMessage( "Value for \"{$table}.{$column}\" failed its registered filter.", WARNING );
					return false;
				}
			}
		}
		return true;
	}

	// INSERT one validated record into an already-qualified table name. Returns
	// true on success, false if a column name is unsafe.
	private function insertRow( $qualified, $record ) {
		$columns      = array();
		$placeholders = array();
		$params       = array();
		foreach( $record as $column => $value ) {
			if( !$this->isSafeIdentifier( $column ) ) {
				$this->framework->logMessage( "Unsafe column identifier in insert: \"{$column}\".", WARNING );
				return false;
			}
			$columns[]            = $column;
			$placeholders[]       = ":{$column}";
			$params[":{$column}"] = $value;
		}
		$sql = "INSERT INTO {$qualified} ( " . implode( ', ', $columns ) . " ) VALUES ( " . implode( ', ', $placeholders ) . " )";
		$this->framework->runSql( $sql, $params );
		return true;
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

	// Column metadata for a table, read from information_schema (driver-neutral).
	// Returns a list of rows: column_name, data_type, is_nullable, column_default.
	public function getTableDetails( $table_name, $database_name = null ) {
		if( !$this->isSafeIdentifier( $table_name ) ) {
			$this->framework->logMessage( "Unsafe table identifier in getTableDetails: \"{$table_name}\".", WARNING );
			return array();
		}
		$prefix = $this->framework->getDatabasePrefix();
		if( $prefix === null ) { $prefix = ''; }
		$physical = $prefix . $table_name;

		if( $this->framework->getDatabaseDriver() === 'pgsql' ) {
			$sql    = "SELECT column_name, data_type, is_nullable, column_default
			             FROM information_schema.columns
			            WHERE table_schema = 'public' AND table_name = :table
			         ORDER BY ordinal_position";
			$params = array( ':table' => $physical );
		}
		else {
			if( $database_name === null ) { $database_name = $this->framework->getDatabaseName(); }
			$sql    = "SELECT column_name, data_type, is_nullable, column_default
			             FROM information_schema.columns
			            WHERE table_schema = :schema AND table_name = :table
			         ORDER BY ordinal_position";
			$params = array( ':schema' => $database_name, ':table' => $physical );
		}
		$results = $this->framework->runSql( $sql, $params );
		return is_array( $results ) ? $results : array();
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

	// NOTE: the legacy string-builder helpers (buildInsertSql/buildUpdateSql/
	// insertAndGetId) and framework->quoteForDatabase() were removed once the
	// agent module -- their last consumer -- was migrated to prepared statements.
	// All data access now uses the parameterized helpers above
	// (buildSelect/insertRecord/updateRecords/updateElseInsert) and the
	// registration-aware record API (getRecords/setRecords/addRecords/...).

} // End of Models class

