<?php

/**
 * Form to submit a vote
 *
 * @param bool    | print or return
 * @return string
 */
function gb_suggestion_form( $print = true ) {
	if ( is_user_logged_in() ) {
		ob_start();
			?><form action="<?php gb_suggestion_url() ?>" class="gb_vote_up" id="<?php the_ID() ?>_vote_up" data-form-id=<?php the_id() ?>>
				
				<div class="suggested_price_wrap">
					<label for="suggested_price_<?php the_ID() ?>"><?php gb_e('Suggest a price:') ?></label>
					<input type="text" name="suggested_price" id="suggested_price_<?php the_ID() ?>" class="suggested_price" data-suggested-price="<?php echo gb_get_price() ?>00">
					<div id="price_slider_range_<?php the_ID() ?>" class="price_slider_range" class="clearfix" data-form-id=<?php the_id() ?>></div>
				</div>
				
				<?php if ( gb_get_suggestion_notification_preference() ): // Show only if preference isn't set ?>
					<div class="suggested_notification_wrap">
						<label for="suggested_price_<?php the_ID() ?>"><?php gb_e('Notification Preference:') ?></label>
						<select name="notification_preference" class="notification_preference">
							<option value="mobile"><?php gb_e('Mobile') ?></option>
							<option value="email"><?php gb_e('Email') ?></option>
						</select>
						<?php if ( gb_get_users_mobile_number() ): // Shown only if mobile number isn't already set ?>
							<input type="text" name="mobile_number" class="mobile_number" placeholder="<?php echo gb_get_users_mobile_number() ?>">
						<?php endif ?>
						<input type="text" name="email_address" class="email_address" value="<?php echo gb_get_user_email() ?>" readonly>
					</div>
				<?php endif ?>
				
				<input type="hidden" name="vote_suggestion_id" value="<?php echo get_the_ID() ?>">
				<input type="hidden" name="<?php echo SA_Voting::NONCE_NAME ?>" value="<?php echo wp_create_nonce( SA_Voting::NONCE ) ?>">

				<input class="form-submit submit" type="submit" value="<?php gb_e('Vote'); ?>" name="gb_suggestion_submit" />

			</form><?php
		$out = ob_get_clean();
	}
	else {
		$out = gb__('Please log in or register to vote.');
	}

	$form = apply_filters( 'gb_suggestion_button' , $out );
	if ( $print ) {
		print $form;
	}
	return $form;
}
function gb_get_suggestion_url() {
	return apply_filters( 'gb_get_suggestion_url', add_query_arg( array( SA_Submissions::SUGGEST_QUERY_VAR => 1 ), gb_deal_submission_url() ) );
}
function gb_suggestion_url() {
	echo apply_filters( 'gb_suggestion_url', gb_get_suggestions_url() );
}

function gb_get_suggestions_url() {
	return apply_filters( 'gb_get_suggestions_url', SA_Post_Type::get_url() );
}
function gb_suggestions_url() {
	echo apply_filters( 'gb_suggestions_url', gb_get_suggestions_url() );
}


function gb_get_suggested_votes( $deal_id = null ) {
	if ( null === $deal_id ) {
		global $post;
		$deal_id = $post->ID;
	}
	$suggested_deal = SA_Post_Type::get_instance( $deal_id );
	return apply_filters( 'gb_get_suggested_votes', $suggested_deal->get_votes() );
}
	function gb_suggested_votes( $deal_id = null ) {
		echo apply_filters( 'gb_suggested_votes', gb_get_suggested_votes( $deal_id ) );
	}
	
function gb_get_suggested_votes_remaining( $deal_id = null ) {
	if( null === $deal_id ) {
		global $post;
		$deal_id = $post->ID;
	}
	$votes = gb_get_suggested_votes( $deal_id );
	$threshold = gb_get_suggested_votes_threshold( $deal_id );
	$remaining = ( $threshold-$votes >= 0 ) ? $threshold-$votes : 0 ;
	return apply_filters('gb_get_suggested_votes_remaining', $remaining );
}
	function gb_suggested_votes_remaining( $deal_id = null ) {
		echo apply_filters('gb_suggested_votes_remaining', gb_get_suggested_votes_remaining($deal_id) );
	}

	
function gb_get_suggested_votes_threshold( $deal_id = null ) {
	if( null === $deal_id ) {
		global $post;
		$deal_id = $post->ID;
	}
	$suggested_threshold = SA_Post_Type::get_instance( $deal_id );
	return apply_filters('gb_get_suggested_votes_threshold', $suggested_threshold->get_threshold() );
}
	function gb_suggested_votes_threshold( $deal_id = null ) {
		echo apply_filters('gb_suggested_votes_threshold', gb_get_suggested_votes_threshold($deal_id) );
	}

function gb_suggested_can_vote( $deal_id = 0, $user_id = 0 ) {
	if ( !is_user_logged_in() ) {
		return FALSE;
	}
	if ( !$deal_id ) {
		global $post;
		$deal_id = $post->ID;
	}
	if ( !$user_id ) {
		$user_id = get_current_user_id();
	}
	$suggested_deal = SA_Post_Type::get_instance( $deal_id );
	return apply_filters( 'gb_suggested_can_vote', $suggested_deal->allowed_to_vote( $user_id ) );
}


function gb_suggested_get_user_vote( $deal_id = null, $user_id = null ) {
	if ( null === $deal_id ) {
		global $post;
		$deal_id = $post->ID;
	}
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}
	$suggested_deal = SA_Post_Type::get_instance( $deal_id );
	return apply_filters( 'gb_suggested_has_voted', $suggested_deal->get_vote_by_user( $user_id ) );
}


function gb_suggested_has_voted( $deal_id = null, $user_id = null ) {
	if ( null === $deal_id ) {
		global $post;
		$deal_id = $post->ID;
	}
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}
	$votes = gb_suggested_get_user_votes( $deal_id, $user_id );
	if ( $votes != '0' ) {
		return TRUE;
	}
	return;
}

function gb_get_users_mobile_number( $user_id = 0 ) {
	if ( !$user_id ) {
		$user_id = get_current_user_id();
	}
	$account = Group_Buying_Account::get_instance( $user_id );
	return SA_Registration::get_mobile_number( $account );
}

function gb_get_suggestion_notification_preference( $user_id = 0 ) {
	if ( !$user_id ) {
		$user_id = get_current_user_id();
	}
	return SA_Notifications::get_preference( $user_id );
}

if ( !function_exists('gb_get_user_email') ) {
	function gb_get_user_email( $user_id = 0 ) {
		if ( !$user_id ) {
			$user_id = get_current_user_id();
		}
		$user = get_userdata( $user_id );
		if ( !is_a( $user, 'WP_User' ) ) {
			if ( self::DEBUG ) error_log( "Get User Email FAILED: " . print_r( $user, true ) );
		}
		return $user->user_email;
	}
}