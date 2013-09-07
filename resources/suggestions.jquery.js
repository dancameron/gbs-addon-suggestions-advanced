jQuery(document).ready(function($){

	jQuery(".gb_vote_up").submit(function(event) {

		/* stop form from submitting normally */
		event.preventDefault();

		/* get some values from elements on the page: */
		var $form = $( this ),
		suggestion_id = $form.find( 'input[name="vote_suggestion_id"]' ).val(),
		current_user_id = $form.find( 'input[name="vote_submission_user_id"]' ).val(),
		submission_data = $form.serialize();

		$.post( gb_ajax_url, { 
			action: 'sa_voting', 
			id: suggestion_id, 
			user_id: current_user_id, 
			data: submission_data
		}, 
		function( data ) {
			alert( data );
		});

	});
});