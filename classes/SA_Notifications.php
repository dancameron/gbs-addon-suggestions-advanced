<?php

class SA_Notifications extends Group_Buying_Controller {

	const NOTIFICATION_SMS_TYPE = 'gb_sms_suggestion_notification';
	const NOTIFICATION_TYPE = 'gb_email_suggestion_notification';
	const NOTIFICATIONS_SENT = 'gb_suggested_notifications_sent_v4a';

	public static function init() {

		// Register Notifications
		add_filter( 'gb_notification_types', array( get_class(), 'register_notification_type' ), 10, 1 );
		add_filter( 'gb_notification_shortcodes', array( get_class(), 'register_notification_shortcode' ), 10, 1 );

		// Voting hook, check for a preference
		add_action( 'gb_sa_set_vote', array( get_class(), 'maybe_add_notification_preference' ), 10, 3 );

		// Meta Box
		add_action( 'gb_suggested_deal_published', array( get_class(), 'maybe_send_notifications' ) );

	}

	public function register_notification_type( $notifications ) {
		$notifications[self::NOTIFICATION_SMS_TYPE] = array(
			'name' => self::__( 'SMS Alert: Suggestion Published' ),
			'description' => self::__( "Customize the SMS notification that is sent after a suggested deal is published." ),
			'shortcodes' => array( 'date', 'name', 'username', 'site_title', 'site_url', 'deal_url', 'deal_title', 'merchant_name' ),
			'default_title' => self::__( 'SMS Alert: Suggestion Published' ),
			'default_content' => '',
			'allow_preference' => TRUE
		);
		$notifications[self::NOTIFICATION_TYPE] = array(
			'name' => self::__( 'E-Mail Alert: Suggestion Published' ),
			'description' => self::__( "Customize the e-mail notification that is sent after a suggested deal is published." ),
			'shortcodes' => array( 'date', 'name', 'username', 'site_title', 'site_url', 'deal_url', 'deal_title', 'merchant_name' ),
			'default_title' => self::__( 'Your suggestions have been heard.' ),
			'default_content' => '',
			'allow_preference' => TRUE
		);
		return $notifications;
	}

	public function register_notification_shortcode( $default_shortcodes ) {
		$default_shortcodes['merchant_name'] = array(
			'description' => self::__( 'Used to return the merchant name.' ),
			'callback' => array( get_class(), 'checkout_merchant_shortcode' )
		);
		return $default_shortcodes;
	}

	public static function checkout_merchant_shortcode( $atts, $content, $code, $data ) {
		return $data['merchant_name'];
	}

	public function maybe_send_notifications( $suggested_deal ) {
		$votes = $suggested_deal->get_voters();
		foreach ( $votes as $user_id => $vote ) {
			foreach ( $vote as $data ) {
				self::send_sms_notification( $suggested_deal, $user_id, $data );
				self::send_notification( $suggested_deal, $user_id, $data );
			}
		}
		update_post_meta( $suggested_deal->get_id(), self::NOTIFICATIONS_SENT, time() );
	}

	public function mark_notifications_sent( $deal_id = 0 ) {
		update_post_meta( $deal_id, self::NOTIFICATIONS_SENT, time() );
	}

	public function when_notifications_sent( $deal_id ) {
		$meta = get_post_meta( $deal_id, self::NOTIFICATIONS_SENT, TRUE );
		return $meta;
	}

	public function has_notifications_sent( $deal_id ) {
		$meta = get_post_meta( $deal_id, self::NOTIFICATIONS_SENT, TRUE );
		return $meta != '';
	}

	///////////////////
	// Notifications //
	///////////////////
	
	public function send_sms_notification( $suggested_deal, $user_id, $data = array() ) {
		// $number
		$account = Group_Buying_Account::get_instance( $user_id );
		$number = SA_Registration::get_mobile_number( $account );

		// $message
		$merchant_id = $suggested_deal->get_merchant_id();
		$merchant_name = get_the_title( $merchant_id );
		$data = array(
				'user_id' => $user_id,
				'deal' => $suggested_deal,
				'merchant_name' => $merchant_name
			);
		$message = Group_Buying_Notifications::get_notification_content( self::NOTIFICATION_SMS_TYPE, $data );

		// And send
		$sms = SA_Twilio::send_sms( $number, $message );
	}

	public function send_notification( $suggested_deal ) {
		// $message
		$merchant_id = $suggested_deal->get_merchant_id();
		$merchant_name = get_the_title( $merchant_id );
		$data = array(
				'user_id' => $user_id,
				'deal' => $suggested_deal,
				'merchant_name' => $merchant_name
			);

		$to = Group_Buying_Notifications::get_user_email( $user_id );

		Group_Buying_Notifications::send_notification( self::NOTIFICATION_TYPE, $data, $to );
	}

	//////////////////////////////
	// Notification Preference //
	//////////////////////////////
	
	public function maybe_add_notification_preference( $user_id = 0, $suggestion_id = 0, $data = array() ) {
		if ( isset( $data['notification_preference'] ) && $data['notification_preference'] != '' ) {
			$account_id = Group_Buying_Account::get_account_id_for_user( $user_id );
			$notifications = get_post_meta( $account_id, '_'.Group_Buying_Notifications::NOTIFICATION_SUB_OPTION, TRUE );
			error_log( 'data' . print_r( $data, TRUE ) );
			error_log( 'before' . print_r( $notifications, TRUE ) );
			// determine preference
			switch ( $data['notification_preference'] ) {
				case 'mobile':
				case 'sms':
					$notifications[] = self::NOTIFICATION_SMS_TYPE;
					// Unset the email notification since sms is the preference
					foreach ( array_keys( $notifications, self::NOTIFICATION_TYPE, true ) as $key ) {
						unset( $notifications[$key] );
					}
					break;

				case 'email':
				default:
					$notifications[] = self::NOTIFICATION_TYPE;
					// unset SMS since it's the preference
					foreach ( array_keys( $notifications, self::NOTIFICATION_SMS_TYPE, true ) as $key ) {
						unset( $notifications[$key] );
					}
					break;
			}
			error_log( 'after' . print_r( $notifications, TRUE ) );
			update_post_meta( $account_id, '_'.Group_Buying_Notifications::NOTIFICATION_SUB_OPTION, $notifications );
		}
	}

	public function get_preference( $user_id ) {
		if ( !$user_id ) {
			$user_id = get_current_user_id();
		}
		$account_id = Group_Buying_Account::get_account_id_for_user( $user_id );
		$notifications = get_post_meta( $account_id, '_'.Group_Buying_Notifications::NOTIFICATION_SUB_OPTION, TRUE );
		
		if ( array_search( self::NOTIFICATION_SMS_TYPE, $notifications ) !== false )
			return 'mobile';

		if ( array_search( self::NOTIFICATION_TYPE, $notifications ) !== false )
			return 'email';

		return FALSE;
	}
}
