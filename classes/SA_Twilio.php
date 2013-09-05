<?php

if ( !class_exists( 'Services_Twilio' ) ) {
	require GB_SUGGESTIONS_ADVANCED_PATH . 'lib/twilio-php/Services/Twilio.php';
}

class SA_Twilio extends Group_Buying_Controller {
	public static $twilio;

	public static function init_twilio() {

		if ( !( isset( self::$twilio ) && is_a( self::$twilio, 'Services_Twilio' ) ) ) {

			SMS_Options::init();
			$account_sid = SMS_Options::$twilio_account; // Your Twilio account sid
			$auth_token = SMS_Options::$twilio_auth; // Your Twilio auth token

			try {
				self::$twilio = new Services_Twilio( $account_sid, $auth_token );
			} catch ( Exception $e ) {
				error_log( "exception caught: " . print_r( $e->getMessage(), true ) );
			}
		}
		return self::$twilio;
	}

	public static function send_sms( $mobile_number, $message ) {

		$formatted_mobile_number = '+'.preg_replace( "/[^0-9]/", '', $mobile_number );
		if ( strlen( $formatted_mobile_number ) < 12 ) {
			return;
		}

		$messages = self::sms_chunk_split( $message );
		foreach ( $messages as $message ) {
			try {
				$twilio_client = self::init_twilio();

				SMS_Options::init();
				$twilio_number = '+'.preg_replace( "/[^0-9]/", '', SMS_Options::$twilio_number ); // Your Twilio auth token

				$message = $twilio_client->account->sms_messages->create(
					$twilio_number, // From a Twilio number in your account
					$formatted_mobile_number,
					$message
				);

			} catch ( Exception $e ) {
				error_log( "exception caught: " . print_r( $e->getMessage(), true ) );
				return FALSE;
			}
		}
		return TRUE;
	}

	private function sms_chunk_split( $message ) {
		$message = preg_replace( '/[\r\n]+/', ' ', $message );
		$chunks = wordwrap( $message, 160, '\n' );
		return explode( '\n', $chunks );
	}
}
