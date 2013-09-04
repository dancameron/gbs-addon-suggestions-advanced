<?php

/**
 * Load via GBS Add-On API
 */
class Suggestions_Addon extends Group_Buying_Controller {
	const META_KEY = '_gbs_suggestions_advanced';

	public static function init() {

		// Model and Controller
		require_once('Suggestion.php');
		require_once('Suggestions.php');
		SA_Suggestion::init();
		SA_Suggestions::init();

		// Twilio Services
		require_once('SA_Twilio.php');

		// Notifications
		require_once('SA_Notifications.php');
		SMS_Notifications::init();

		// Options
		require_once('SA_Options.php');
		require_once('SA_MetaBox.php');

		if ( is_admin() ) {
			SMS_MetaBox::init();
			SMS_Options::init();
		}


	}

	public static function gb_addon( $addons ) {
		$addons['suggestions_advanced'] = array(
			'label' => self::__( 'Advanced Suggestions' ),
			'description' => self::__( 'Allow registered users submit a suggested deal, choose a notification method and select a price; allows others to vote on these suggestions.' ),
			'files' => array(),
			'callbacks' => array(
				array( __CLASS__, 'init' ),
			),
			'active' => TRUE,
		);
		return $addons;
	}

}