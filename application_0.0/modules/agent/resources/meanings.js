var anything_changed = false;

function newMeaning() {
	//$('#new_meaning_dialog').dialog( { buttons: { "Create": function() { $( this ).dialog( "close" ); } }, title: 'New Meaning', width: '600px', show: 'slide', hide: 'explode' } );
	$('#new_meaning_dialog').dialog( { 
		buttons: { 
			'Cancel': function() { 
				$( this ).dialog( 'close' ); 
			}, 
			'Create': function() { 
				$( '#new_meaning_form' ).submit(); 
				$( this ).dialog( 'close' ); 
			} 
		}, 
		title: 'New Meaning', 
		width: '600px', 
		show: 'slide', 
		hide: 'explode' 
	} );

	//$( '#ajax_return' ).load( converse_uri, '', processAgentResponse );
}

function recognizerHelp() {
	$('#recognizer_help_dialog').dialog( { 
		buttons: { 
			'OK': function() { 
				$( this ).dialog( 'close' ); 
			} 
		}, 
		title: 'Recognizer Help', 
		width: '600px', 
		show: 'slide', 
		hide: 'slide' 
	} );
}

function comparisonHelp() {
	$('#comparison_help_dialog').dialog( { 
		buttons: { 
			'OK': function() { 
				$( this ).dialog( 'close' ); 
			} 
		}, 
		title: 'Comparison Help', 
		width: '600px', 
		show: 'slide', 
		hide: 'slide' 
	} );
}

function paradigmHelp() {
	$('#paradigm_help_dialog').dialog( { 
		buttons: { 
			'OK': function() { 
				$( this ).dialog( 'close' ); 
			} 
		}, 
		title: 'Paradigm Help', 
		width: '600px', 
		show: 'slide', 
		hide: 'slide' 
	} );
}


function saveChanges() {
	// TODO: submit changes
}

function changeMade() {
	anything_changed = true;
}

$(document).ready(function(){
        $('#new_meaning_button').click( newMeaning );
	$('#recognizer_help_icon').click( recognizerHelp );
	$('#comparison_help_icon').click( comparisonHelp );
	$('#paradigm_help_icon').click( paradigmHelp );
});


