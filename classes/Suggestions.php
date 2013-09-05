<?php

class SA_Suggestions extends Group_Buying_Controller {

	const SUGGEST_QUERY_VAR = 'suggestion';
	private static $create_path = 'create_suggestion';

	public static function init() {

		// Deal Submission
		add_action( 'submit_deal', array( get_class(), 'new_suggestion' ) );
		add_action( 'gb_deal_submission_fields', array( get_class(), 'suggestion_gb_deal_submission_fields' ) );

		// Templates
		add_filter( 'template_include', array( get_class(), 'override_template' ) );
		add_filter( 'gb_deals_index_title', array( get_class(), 'custom_gb_deals_index_title' ) );


		// Admin columns
		add_filter( 'manage_edit-'.Group_Buying_Deal::POST_TYPE.'_columns', array( get_class(), 'register_columns' ) );
		add_filter( 'manage_'.Group_Buying_Deal::POST_TYPE.'_posts_custom_column', array( get_class(), 'column_display' ), 10, 2 );
		add_filter( 'manage_edit-'.Group_Buying_Deal::POST_TYPE.'_sortable_columns', array( get_class(), 'sortable_columns' ) );
		add_filter( 'request', array( get_class(), 'column_orderby' ) );

		add_filter( 'wp_print_scripts', array( get_class(), 'scripts' ) );
		add_filter( 'wp_print_styles', array( get_class(), 'screen' ) );

		add_action( 'parse_request', array( get_class(), 'vote_up' ) );
	}

	/////////////////////
	// AJAX Functions //
	/////////////////////



	//////////////
	// Utility //
	//////////////


	public static function vote_up() {
		// TODO nonce and more
		if ( isset( $_REQUEST['gb_suggestion_vote'] ) ) {
			$id = $_REQUEST['gb_suggestion_vote'];
			$deal = Group_Buying_Deal::get_instance( $id );
			$votes = Group_Buying_Suggestion::set_vote( $deal );
			print $votes;
			die();
		}
	}

	/**
	 * Add a vote
	 * @param Group_Buying_Deal $deal                    
	 * @param integer            $user_id                 
	 * @param string            $notification_preference email/sms/null
	 * @param integer           $talley                  
	 */
	public function add_vote( Group_Buying_Deal $deal, $user_id = null, $notification_preference = 'email', $talley = 1 ) {
		if ( $deal->allowed_to_vote( $user_id ) ) {
			return self::__( 'Limit Exceeded' );
		}

		// add voter record
		self::add_voter( $deal, $user_id );
		$votes = self::get_votes( $deal ) + $talley;
		
		$deal->save_post_meta( array( self::$meta_keys['votes'] => $votes ) );
		return $votes;
	}

	public function add_voter( Group_Buying_Deal $deal, $user_id = null ) {
		if ( null == $user_id ) {
			$user_id = get_current_user_id();
		}
		$voters = $this->get_voters();
		$voters = array_merge( (array)$voters, array( $user_id ) );
		$this->save_post_meta( array( self::$meta_keys['voters'] => $voters ) );
		$count = $this->save_post_meta( array( self::$meta_keys['user_prefix'].$user_id => 1 ) );
		return $count;
	}


	/////////////////
	// Submission //
	/////////////////

	public static function new_suggestion( Group_Buying_Deal $deal ) {
		if ( isset( $_POST['gb_deal_suggestion'] ) && $_POST['gb_deal_suggestion'] ) {
			wp_set_object_terms( $deal->get_id(), Group_Buying_Suggestion::get_term_slug(), Group_Buying_Suggestion::TAX );
			self::set_message( __( 'Thanks! Your Suggestion has been Submitted for Review.' ), self::MESSAGE_STATUS_INFO );
			wp_redirect( Group_Buying_Suggestion::get_url() );
			exit();
		}
	}

	public static function suggestion_gb_deal_submission_fields( $fields ) {
		if ( isset( $_REQUEST[Group_Buying_Suggestion::QUERY_VAR] ) && $_REQUEST[Group_Buying_Suggestion::QUERY_VAR] ) {
			unset( $fields['exp'] );
			unset( $fields['shipping'] );
			unset( $fields['tax'] );
			unset( $fields['purchase_limits'] );
			unset( $fields['min_purchases'] );
			unset( $fields['max_purchases'] );
			unset( $fields['max_per_user'] );
			unset( $fields['deal_details'] );
			unset( $fields['value'] );
			unset( $fields['amount_saved'] );
			unset( $fields['highlights'] );
			unset( $fields['fine_print'] );
			unset( $fields['voucher_details'] );
			unset( $fields['voucher_how_to_use'] );
			unset( $fields['voucher_map'] );
			unset( $fields['serial_numbers'] );
			$fields['suggestion'] = array(
				'weight' => 500,
				'type' => 'hidden',
				'value' => '1',
			);
		}
		return $fields;
	}

	/////////////
	// Filter //
	/////////////




	////////////////
	// Templates //
	////////////////

	public static function override_template( $template ) {
		if ( Group_Buying_Suggestion::is_suggestion_query() ) {
			$template = self::locate_template( array(
					'deals/index-suggestions.php',
					'deals/suggestions.php',
				), FALSE );
			if ( $template == FALSE ) {
				$template = SUGGESTIONS_PATH . '/views/' . GBS_THEME_SLUG . '/gbs/deals/suggestions.php';
				error_log( "template: " . print_r( $template, true ) );
			}
		}
		return $template;
	}

	public static function scripts() {
		if ( SA_Post_Type::is_suggestion_query() ) {
			wp_enqueue_script( 'suggested_js', SUGGESTIONS_RESOURCES.'suggestions.jquery.js', array( 'jquery' ), '1', true );
		}
	}

	public static function screen() {
		if ( SA_Post_Type::is_suggestion_query() ) {
			wp_enqueue_style( 'suggested_style', SUGGESTIONS_RESOURCES.'suggestions.css', '', '1', 'screen' );
		}
	}
	public static function custom_gb_deals_index_title( $title ) {
		if ( Group_Buying_Suggestion::is_suggestion_query() ) {
			$title = gb_e( 'Suggested Deals' );
		}
		return $title;

	}




	public static function register_columns( $columns ) {
		$columns['votes'] = self::__( 'Votes' );
		return $columns;
	}


	public static function column_display( $column_name, $id ) {
		global $post;
		$deal = Group_Buying_Deal::get_instance( $id );

		if ( !is_a( $deal, 'Group_Buying_Deal' ) )
			return; // return for that temp post

		switch ( $column_name ) {

		case 'votes':
			echo Group_Buying_Suggestion::get_votes( $deal );
			break;

		default:
			// code...
			break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['votes'] = 'votes';
		return $columns;
	}

	public function column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && is_admin() ) {
			switch ( $vars['orderby'] ) {
			case 'votes':
				$vars = array_merge( $vars, array(
						'meta_key' => '_suggestion_votes',
						'orderby' => 'meta_value_num'
					) );
				break;
			default:
				// code...
				break;
			}
		}

		return $vars;
	}
}
