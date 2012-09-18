
// Setup event callbacks on document ready
$(document).ready(function(){
	$('#user_statement').keypress(function( e ){
		if( e.which == 13 && $('#user_statement').val() > '' ) {
			//$.getJSON( '../agent/converse', function( response_json ) {
			//	alert( 'We did an AJAX call retrieving json!' );
			//});
			$( '#full_response' ).load( '../agent/converse' );
			eval( $( '#full_response' ).val() );  // TODO: BORKEN 
			alert( 'Response: ' + response );
		}	
	});
});

