jQuery(document).ready(function($){

	//////////////////////////////
	// Click Vote interactions //
	//////////////////////////////
	var enabled_forms = [];

	jQuery('.suggested_price_wrap').hide();
	jQuery('.suggested_notification_wrap').hide();

	jQuery( ".gb_vote_up" ).submit(function(event) {

		/* stop form from submitting normally */
		event.preventDefault();

		/* get some values from elements on the page: */
		var $form = $( this ),
		suggestion_id = $form.find( 'input[name="vote_suggestion_id"]' ).val(),
		current_user_id = $form.find( 'input[name="vote_submission_user_id"]' ).val(),
		submission_data = $form.serialize();

		if ( !(~jQuery.inArray(suggestion_id, enabled_forms)) ) {
			jQuery( '#' + suggestion_id + '_vote_up .suggested_price_wrap' ).show();
			jQuery( '#' + suggestion_id + '_vote_up .suggested_notification_wrap' ).show();
			form_enabled = true;
			enabled_forms.push( suggestion_id );
			return;
		};

		$.post( gb_ajax_url, { 
			action: 'sa_voting', 
			id: suggestion_id, 
			user_id: current_user_id, 
			data: submission_data
		}, 
		function( data ) {
			$form.fadeOut();
			$( '#' + suggestion_id + '_cannot_vote' ).show();
			$( '#' + suggestion_id + '_vote_result' ).html(data);
		});

	});

	jQuery( ".gb_vote_up" ).each( function(i, obj) {
		var form_id = jQuery(obj).data('form-id'),
			price_input = $( '#suggested_price_' + form_id ),
			price_input_high = $( '#suggested_price_high_' + form_id ),
			price_value = price_input.data('suggested-price'),
			price_value_high = price_input_high.data('suggested-price-high');
		
		$( price_input ).val( "$" + price_value );
		$( price_input_high ).val( "$" + price_value_high );
		
		$( '#' + form_id + '_vote_up .price_slider_range' ).slider({
			range: true,
			min: 0,
			max: price_value_high+100,
			values: [price_value, price_value_high],
			slide: function( event, ui ) {
				price_input.val( "$" + ui.values[ 0 ] );
				console.log(form_id);
				price_input_high.val( "$" + ui.values[ 1 ] );
			}
		});
	});

	jQuery('.email_address').hide();
	jQuery('.notification_preference').on('change', function (e) {
		var option = this.value;
		if ( option === 'mobile' ) {
			$('.email_address').hide();
			$('.mobile_number').show();
		}
		else {
			$('.email_address').show();
			$('.mobile_number').hide();
		}
	});


});