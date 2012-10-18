var anything_changed = false;

function newReaction() {
	$('#new_reaction_dialog').dialog( { 
		buttons: { 
			'Cancel': function() { 
				$( this ).dialog( 'close' ); 
			}, 
			'Create': function() { 
				$( '#new_reaction_form' ).submit(); 
				$( this ).dialog( 'close' ); 
			} 
		}, 
		title: 'New Reaction', 
		width: '600px', 
		show: 'slide', 
		hide: 'explode' 
	} );
}

function conditionsHelp() {
	$('#conditions_help_dialog').dialog( { 
		buttons: { 
			'OK': function() { 
				$( this ).dialog( 'close' ); 
			} 
		}, 
		title: 'Conditions Help', 
		width: '600px', 
		show: 'slide', 
		hide: 'slide' 
	} );
}

function actionsHelp() {
	$('#actions_help_dialog').dialog( { 
		buttons: { 
			'OK': function() { 
				$( this ).dialog( 'close' ); 
			} 
		}, 
		title: 'Action Sequence Help', 
		width: '600px', 
		show: 'slide', 
		hide: 'slide' 
	} );
}

function makeDirty( that ) {
	alert( ' Dirty! ' );
}

function resetReaction( that ) {
}

$(document).ready(function(){
        $('#new_reaction_button').click( newReaction );
        $('#conditions_help_icon').click( conditionsHelp );
        $('#actions_help_icon').click( actionsHelp );

	//$('.modifiable').change( function( this ) { makeDirty( this ); } );
});


