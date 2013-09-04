<?php 

/**
 * Form to submit a vote
 *
 * @param bool | print or return  
 * @return string 
 */
function gb_suggestion_button( $print = true ) {
	$out = '<form action="'.trailingslashit(get_home_url()).'" class="gb_vote_up" id="'.get_the_ID().'_vote_up" >';
	$out .= '<input type="hidden" name="gb_suggestion_vote" value="'.get_the_ID().'"/>';
	$out .= '<input type="image" src="'.SUGGESTIONS_RESOURCES.'thumb.png" />';
	$out .= '</form>';
	$form = apply_filters( 'gb_suggestion_button' , $out );
	if ( $print ) {
		print $form;
	}
	return $form;
}
function gb_get_suggestion_url() {
	return apply_filters('gb_get_suggestion_url', add_query_arg(array(Group_Buying_Suggestion::QUERY_VAR => 1),gb_deal_submission_url()) );
}
	function gb_suggestion_url() {
		echo apply_filters('gb_suggestion_url', gb_get_suggestions_url() );
	}

function gb_get_suggestions_url() {
	return apply_filters('gb_get_suggestions_url', Group_Buying_Suggestion::get_url() );
}
	function gb_suggestions_url() {
		echo apply_filters('gb_suggestions_url', gb_get_suggestions_url() );
	}

	
function gb_get_suggested_votes( $deal_id = null ) {
	if( null === $deal_id ) {
		global $post;
		$deal_id = $post->ID;
	}
	$deal = Group_Buying_Deal::get_instance( $deal_id );
	return apply_filters('gb_get_suggested_votes', Group_Buying_Suggestion::get_votes($deal) );
}
	function gb_suggested_votes( $deal_id = null ) {
		echo apply_filters('gb_suggested_votes', gb_get_suggested_votes($deal_id) );
	}

	
function gb_suggested_can_vote( $deal_id = null, $user_id = null ) {
	if ( !is_user_logged_in() ) {
		return FALSE;
	}
	if( null === $deal_id ) {
		global $post;
		$deal_id = $post->ID;
	}
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}
	$deal = Group_Buying_Deal::get_instance( $deal_id );
	return apply_filters('gb_suggested_can_vote', Group_Buying_Suggestion::allowed_to_vote($deal,$user_id) );
}

	
function gb_suggested_get_user_votes( $deal_id = null, $user_id = null ) {
	if( null === $deal_id ) {
		global $post;
		$deal_id = $post->ID;
	}
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}
	$deal = Group_Buying_Deal::get_instance( $deal_id );
	return apply_filters('gb_suggested_has_voted', Group_Buying_Suggestion::get_votes_by_user($deal,$user_id) );
}
	
	
function gb_suggested_has_voted( $deal_id = null, $user_id = null ) {
	if( null === $deal_id ) {
		global $post;
		$deal_id = $post->ID;
	}
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}
	$votes = gb_suggested_get_user_votes($deal_id, $user_id);
	if ( $votes != '0' ) {
		return TRUE;
	}
	return;
}
