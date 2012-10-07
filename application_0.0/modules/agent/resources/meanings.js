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

function saveChanges() {
	// TODO: submit changes
}

function changeMade() {
	anything_changed = true;
}

$(document).ready(function(){
        $('#new_meaning_button').click( newMeaning );
});


