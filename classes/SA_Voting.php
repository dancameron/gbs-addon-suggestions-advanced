<?php 


/**
* Voting
*/
class SA_Voting {

	const NONCE_NAME = 'sa_voting_nonce_name';
	const NONCE = 'sa_voting_nonce';
	
	public function init() {

		// AJAX Vote
		add_action( 'wp_ajax_sa_voting',  array( get_class(), 'ajax_vote' ), 10, 0 );

		if ( !is_admin() ) {
			add_filter( 'wp_print_scripts', array( get_class(), 'scripts' ) );
		}
	}

	public function ajax_vote() {
		// Unserialize the data sent
		$data = wp_parse_args( $_REQUEST['data'] );
		error_log( 'data' . print_r( $data, TRUE ) );
		// Validate nonce
		$nonce = ( isset( $_REQUEST[self::NONCE_NAME] ) ) ? $_REQUEST[self::NONCE_NAME] : $data[self::NONCE_NAME] ;
		if ( !wp_verify_nonce( $nonce, self::NONCE ) )
			die ( 'Not going to fall for it!');

		$suggested_deal = SA_Post_Type::get_instance( $_REQUEST['id'] );
		if ( get_post_type( $suggested_deal->get_ID() ) != Group_Buying_Deal::POST_TYPE )
			die('Fail');

		// Record vote
		$votes = $suggested_deal->set_vote( get_current_user_id(), $data );
		echo count( $votes );
		die();
	}

	public static function scripts() {
		if ( SA_Post_Type::is_suggestion_query() ) {
			wp_enqueue_script( 'gb_suggested_js', GB_SUGGESTIONS_ADVANCED_RESOURCES_URL.'suggestions.jquery.js', array( 'jquery' ), '1', true );
		}
	}

}