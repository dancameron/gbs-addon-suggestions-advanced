<?php

class SA_MetaBox {
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ), 10, 0 );
		add_action( 'save_post', array( __CLASS__, 'save_meta_box' ), 10, 2 );
	}


	public function get_deals_numbers( $deal_id ) {
		$meta = get_post_meta( $deal_id, SMS_Checkout_Notes_Addon::META_KEY, TRUE );
		return $meta;
	}
	public function get_deals_numbers_array( $deal_id ) {
		$meta = self::get_deals_numbers( $deal_id );

		if ( !$meta )
			return FALSE;

		if ( !is_array( $meta ) ) {
			$array = explode( ',', $meta );
			return self::trim_array( $array );
		}
		return $meta;
	}

	public static function add_meta_box() {
		add_meta_box( 'gbs_sharing_rewards', gb__( 'SMS Checkout Notes' ), array( __CLASS__, 'show_meta_box' ), Group_Buying_Deal::POST_TYPE, 'side' );
	}

	public static function show_meta_box( $post ) {
		$numbers = self::get_deals_numbers( $post->ID, FALSE );
		printf( '<label for="">%s</label>', gb__( 'Numbers to send after purchase. Comma seperated list.' ) );
		printf( '<input type="text" value="%s" name="gbs_sms_checkout_notes" />', $numbers );
		printf( '<p class="description">%s</p>', gb__( 'Numbers must be the complete and 11 numbers in length, e.g. 18057654321.' ) );
		//prp( self::get_deals_numbers_array($post->ID) );

	}

	public static function save_meta_box( $post_id, $post ) {
		// only continue if it's a deal post
		if ( $post->post_type != Group_Buying_Deal::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( empty( $_POST['gbs_sms_checkout_notes'] ) || wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		update_post_meta( $post_id, SMS_Checkout_Notes_Addon::META_KEY, $_POST['gbs_sms_checkout_notes'] );
	}

	/**
	 * Trim inputs and arrays
	 * @param  string/array $value value/s to trim
	 * @return
	 */
	public static function trim_array( $array ) {
		if ( is_array( $array ) ) {
			$return = array();
			foreach ( $array as $k => $v ) {
				$return[$k] = is_array( $v ) ? self::trim_input( $v ) : trim( $v );
			}
			return $return;
		}
		return trim( $array );
	}
}
