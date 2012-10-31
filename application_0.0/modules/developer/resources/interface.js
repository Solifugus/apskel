var user_statement = '';
var agent_response = '';
var transcript     = '';

function processAgentResponse() {
	var agent_json = $( '#agent_response' ).html();
	if( agent_json != '' ) {
		agent_json = 'agent_response = ' + agent_json + ';';
		eval( agent_json );
		transcript = $('#transcript').html();
		transcript = transcript + 'agent: ' + agent_response.verbal + "<br/>\n";
		$('#transcript').html( transcript );
		$( '#transcript' ).scrollTop( $( '#transcript' )[0].scrollHeight );
		$('#agent_response').html('');
	}
	
}

$(document).ready(function(){

	processAgentResponse();

	$('#user_statement').keypress(function( e ){
		if( e.which == 13 && $( '#user_statement' ).val() != '' ) {
			user_statement = $( '#user_statement' ).val();
			transcript = $( '#transcript' ).html();
			transcript = transcript + 'user: ' + user_statement + "<br/>\n";
			$( '#user_statement' ).val('');
			$( '#transcript' ).html( transcript );
			$( '#transcript' ).scrollTop( $( '#transcript' )[0].scrollHeight );
			var converse_uri = '../agent/converse/statement=' + encodeURIComponent( user_statement ) + '/return=json';
			$( '#agent_response' ).load( converse_uri, '', processAgentResponse );
		}	
	});
});

