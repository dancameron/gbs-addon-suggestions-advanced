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
			?><form action="<?php gb_suggestion_url() ?>" class="gb_vote_up" id="<?php the_ID() ?>_vote_up" >
				
				<input type="text" name="suggested_price" placeholder="<?php gb_price( get_the_id(), FALSE ) ?>">
				<option name="notification_preference">
					<select value="mobile"><?php gb_e('Mobile') ?></select>
					<select value="email"><?php gb_e('Email') ?></select>
				</option>
				<input type="text" name="mobile_number" placeholder="<?php echo gb_get_users_mobile_number() ?>">
				<input type="hidden" name="vote_suggestion_id" value="<?php echo get_the_ID() ?>">
				<input type="hidden" name="<?php echo SA_Voting::NONCE_NAME ?>" value="<?php echo wp_create_nonce( SA_Voting::NONCE ) ?>">
				<button>Submit</button>

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


function gb_suggested_can_vote( $deal_id = null, $user_id = null ) {
	if ( !is_user_logged_in() ) {
		return FALSE;
	}
	if ( null === $deal_id ) {
		global $post;
		$deal_id = $post->ID;
	}
	if ( null === $user_id ) {
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
