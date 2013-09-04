jQuery(document).ready(function($){
	jQuery(".gb_vote_up").submit(function(event) {

		/* stop form from submitting normally */
		event.preventDefault(); 
    
		/* get some values from elements on the page: */
		var $form = $( this ),
		deal_id = $form.find( 'input[name="gb_suggestion_vote"]' ).val(),
		url = $form.attr( 'action' );

		/* Send the data using post and put the results in a div */
		$.post( url, { gb_suggestion_vote: deal_id },
		function( data ) {
				$( "#"+deal_id+"_vote_up" ).fadeOut();
				$( "#"+deal_id+"_vote_result" ).empty().append( data );
			}
		);
	});
});