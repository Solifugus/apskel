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
		title: 'New Meaning', 
		width: '600px', 
		show: 'slide', 
		hide: 'explode' 
	} );
}

function makeDirty( that ) {
	alert( ' Dirty! ' );
}

function resetReaction( that ) {
}

$(document).ready(function(){
        $('#new_reaction_button').click( newReaction );

	//$('.modifiable').change( function( this ) { makeDirty( this ); } );
});


