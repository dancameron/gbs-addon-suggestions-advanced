<?php

class SA_MetaBox {
	const OPTION_NAME = 'gb_suggested_deal';
	const VOTES_OPTION_NAME = 'gb_suggested_deal_vote_threshold';

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ), 10, 0 );
		add_action( 'save_post', array( __CLASS__, 'save_meta_box' ), 10, 2 );
	}

	public function get_deals_numbers( $deal_id ) {
		$meta = get_post_meta( $deal_id, SMS_Checkout_Notes_Addon::META_KEY, TRUE );
		return $meta;
	}

	public static function add_meta_box() {
		add_meta_box( 'suggested', gb__( 'Suggested' ), array( __CLASS__, 'show_meta_box' ), Group_Buying_Deal::POST_TYPE, 'side' );
	}

	public static function show_meta_box( $post ) {
		$suggestion = SA_Post_Type::get_instance( $post->ID );

		// Is suggested Deal
		$checked = $suggestion->is_suggested_deal();
		printf( '<p><label><input type="checkbox" name="%s" %s /> %s</label></p>', self::OPTION_NAME, checked( $checked, TRUE, FALSE ), gb__( 'This is a suggested deal.' ) );

		$threshold = ( $suggestion->get_threshold() ) ? $suggestion->get_threshold() : 10 ;
		printf( '<p><label>%s&nbsp;<input type="number" value="%s" name="%s" min="1" style="width:4em;"/></label>.</p>', gb__( 'The voting threshold will be' ), $threshold, self::VOTES_OPTION_NAME );
	}

	public static function save_meta_box( $post_id, $post ) {
		// only continue if it's a deal post
		if ( $post->post_type != Group_Buying_Deal::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( empty( $_POST[self::VOTES_OPTION_NAME] ) || wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		$suggestion = SA_Post_Type::get_instance( $post->ID );
		$suggestion->unmake_suggested_deal();
		if ( $_POST[self::OPTION_NAME] ) {
			$suggestion->make_suggested_deal();
		}
		$suggestion->set_threshold( $_POST[self::VOTES_OPTION_NAME] );
	}
}
