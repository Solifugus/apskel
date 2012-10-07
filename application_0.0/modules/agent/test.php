<?

		$conditions = 'out or " or in " and not " or " out and';
		print "AFTER: $conditions\n";  
		$quoted = false;
		for( $position = 0; $position <= strlen( $conditions ); $position++ ) {
			if( substr( $conditions, $position, 1) == '"' ) {
				if( $quoted ) { $quoted = false; }
				else          { $quoted = true; }
				continue;
			}
			if( !$quoted ) {
				if( strtolower( substr( $conditions, $position, 3 ) ) == 'and' ) { 
					$conditions = substr( $conditions, 0, $position ) . '&&' . substr( $conditions, $position + 3 );      
				}
				if( strtolower( substr( $conditions, $position, 2 ) ) == 'or' ) { 
					$conditions = substr( $conditions, 0, $position ) . '||' . substr( $conditions, $position + 2 );      
				}
				if( strtolower( substr( $conditions, $position, 3 ) ) == 'not' ) { 
					$conditions = substr( $conditions, 0, $position ) . '!' . substr( $conditions, $position + 3 );      
				}
			}
		}
		print "AFTER: $conditions\n";  

