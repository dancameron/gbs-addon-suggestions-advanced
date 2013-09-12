<?php 


/**
* Voting
*/
class SA_Voting {

	const NONCE_NAME = 'sa_voting_nonce_name';
	const NONCE = 'sa_voting_nonce';
	const ACCOUNT_META_SUGGESTED = '_voted_suggestions';
	
	public function init() {

		// AJAX Vote
		add_action( 'wp_ajax_sa_voting',  array( get_class(), 'ajax_vote' ), 10, 0 );

		// add suggestion to account meta
		add_action( 'gb_sa_set_vote', array( get_class(), 'mark_vote_on_account' ), 10, 3 );

		if ( !is_admin() ) {
			add_filter( 'wp_print_scripts', array( get_class(), 'scripts' ) );
		}
	}

	public function ajax_vote() {
		// Unserialize the data sent
		$data = wp_parse_args( $_REQUEST['data'] );

		// Validate nonce
		$nonce = ( isset( $_REQUEST[self::NONCE_NAME] ) ) ? $_REQUEST[self::NONCE_NAME] : $data[self::NONCE_NAME] ;
		if ( !wp_verify_nonce( $nonce, self::NONCE ) )
			die ( 'Not going to fall for it!');

		$suggested_deal = SA_Post_Type::get_instance( $_REQUEST['id'] );
		if ( get_post_type( $suggested_deal->get_ID() ) != Group_Buying_Deal::POST_TYPE )
			die('Fail');

		// Record vote
		if ( isset( $data['suggested_price'] ) ) {
			$data['suggested_price'] = preg_replace( "/[^0-9]/", "", $data['suggested_price'] );
		}
		if ( isset( $data['suggested_price_high'] ) ) {
			$data['suggested_price_high'] = preg_replace( "/[^0-9]/", "", $data['suggested_price_high'] );
		}

		// Set vote
		$votes = $suggested_deal->set_vote( get_current_user_id(), $data );
		$total_votes = count( $votes );
		$threshold = $suggested_deal->get_threshold();
		$remaining = ( $threshold-$total_votes >= 0 ) ? $threshold-$total_votes : 0 ;
		echo $remaining;
		die();
	}

	public function mark_vote_on_account( $user_id = 0, $suggestion_id = 0, $data = array() ) {
		if ( !$user_id ) {
			$user_id = get_current_user_id();
		}
		$account_id = Group_Buying_Account::get_account_id_for_user( $user_id );
		$suggestions = get_post_meta( $account_id, self::ACCOUNT_META_SUGGESTED, TRUE );
		if ( !is_array( $suggestions ) ) {
			$suggestions = array();
		}
		$suggestions[] = $suggestion_id;
		update_post_meta( $account_id, self::ACCOUNT_META_SUGGESTED, $suggestions );
	}

	public static function suggestions_user_voted( $user_id ) {
		$account_id = Group_Buying_Account::get_account_id_for_user( $user_id );
		return get_post_meta( $account_id, self::ACCOUNT_META_SUGGESTED, TRUE );
	}

	public static function scripts() {
		if ( SA_Post_Type::is_suggestion_query() ) {
			wp_enqueue_script( 'gb_suggested_js', GB_SUGGESTIONS_ADVANCED_RESOURCES_URL.'suggestions.jquery.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-slider' ), '1', true );
		}
	}

	public function get_prices( $suggestion_id = 0, $price_suggestions = 'default' ) {
		if ( !$suggestion_id ) {
			global $post;
			$suggestion_id = $post->ID;
		}
		$suggestion = SA_Post_Type::get_instance( $suggestion_id );
		$votes = $suggestion->get_voters( FALSE );
		$prices = array();
		foreach ( $votes as $user_id => $user_votes ) {
			foreach ( $user_votes as $key => $data ) {
				switch ( $price_suggestions ) {
					case 'all':
						$prices[] = $data['suggested_price'];
						$prices[] = $data['suggested_price_high'];
						break;
					case 'high':
						$prices[] = $data['suggested_price_high'];
						break;
					
					case 'low':
					default:
						$prices[] = $data['suggested_price'];
						break;
				}
			}
		}
		return array_filter($prices);
	}

	function mmmr_prices( $output = 'mean', $prices = 'default' ) {
		if ( !is_array( $prices ) ) {
			$prices = self::get_prices( 0, $prices );
		}
		$prices = $prices;
		if ( empty( $prices ) ) {
			return 'N/A';
		}
		switch ( $output ) {
		case 'mean':
		case 'average':
			$count = count( $prices );
			$sum = array_sum( $prices );
			$total = $sum / $count;
			break;
		case 'median':
			rsort( $prices );
			$middle = round( count( $prices ) / 2 );
			$total = $prices[$middle-1];
			break;
		case 'mode':
			$v = array_count_values( $prices );
			arsort( $v );
			foreach ( $v as $k => $v ) {$total = $k; break;}
			break;
		case 'range':
			sort( $prices );
			$sml = $prices[0];
			rsort( $prices );
			$lrg = $prices[0];
			$total = $lrg - $sml;
			break;
		}
		return $total;
	}

}