
function newTopic() {
	$('#new_topic_dialog').dialog( { 
		buttons: { 
			'Cancel': function() { 
				$( this ).dialog( 'close' ); 
			}, 
			'Create': function() { 
				$( '#new_topic_form' ).submit(); 
				$( this ).dialog( 'close' ); 
			} 
		}, 
		title: 'New Topic', 
		width: '600px', 
		show: 'slide', 
		hide: 'explode' 
	} );
}


$(document).ready(function(){
        $('#create_topic_button').click( newTopic );
});


