<?php

class SA_Notifications extends Group_Buying_Controller {

	const NOTIFICATION_TYPE = 'gb_sms_notes_notifications_sent';

	public static function init() {

		// Register Notifications
		add_filter( 'gb_notification_types', array( get_class(), 'register_notification_type' ), 10, 1 );
		add_filter( 'gb_notification_shortcodes', array( get_class(), 'register_notification_shortcode' ), 10, 1 );

	}

	public function register_notification_type( $notifications ) {
		$notifications[self::NOTIFICATION_TYPE] = array(
			'name' => self::__( 'SMS Alert: Purchase Notes' ),
			'description' => self::__( "Customize the SMS notification that is sent when a customer completes a checkout." ),
			'shortcodes' => array( 'date', 'name', 'username', 'site_title', 'site_url', 'deal_url', 'deal_title', 'checkout_note', 'merchant_name', 'item_name', 'item_qty' ),
			'default_title' => self::__( 'SMS Alert: Purchase Notes' ),
			'default_content' => '',
			'allow_preference' => FALSE
		);
		return $notifications;
	}

	public function register_notification_shortcode( $default_shortcodes ) {
		$default_shortcodes['checkout_note'] = array(
			'description' => self::__( 'Used to return the checkout note.' ),
			'callback' => array( get_class(), 'checkout_note_shortcode' )
		);
		$default_shortcodes['merchant_name'] = array(
			'description' => self::__( 'Used to return the merchant name.' ),
			'callback' => array( get_class(), 'checkout_merchant_shortcode' )
		);
		$default_shortcodes['item_name'] = array(
			'description' => self::__( 'Used to return the item purchased.' ),
			'callback' => array( get_class(), 'checkout_item_shortcode' )
		);
		$default_shortcodes['item_qty'] = array(
			'description' => self::__( 'Used to return the item qty purchased.' ),
			'callback' => array( get_class(), 'checkout_item_qty_shortcode' )
		);
		return $default_shortcodes;
	}

	public static function checkout_note_shortcode( $atts, $content, $code, $data ) {
		return $data['checkout_note'];
	}

	public static function checkout_merchant_shortcode( $atts, $content, $code, $data ) {
		return $data['merchant_name'];
	}

	public static function checkout_item_shortcode( $atts, $content, $code, $data ) {
		return $data['item_name'];
	}

	public static function checkout_item_qty_shortcode( $atts, $content, $code, $data ) {
		return $data['item_qty'];
	}

	public function get_message( $data ) {
		return Group_Buying_Notifications::get_notification_content( self::NOTIFICATION_TYPE, $data );
	}
}
