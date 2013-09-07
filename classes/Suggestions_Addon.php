<?php

/**
 * Load via GBS Add-On API
 */
class Suggestions_Addon extends Group_Buying_Controller {
	const META_KEY = '_gbs_suggestions_advanced';

	public static function init() {

		// Model and Controller
		require_once 'SA_Post_Type.php';
		SA_Post_Type::init();

		// Submission
		require_once 'SA_Submissions.php';
		SA_Submissions::init();

		// Mobile Number Registration
		require_once 'SA_Registration.php';
		SA_Registration::init();

		// Voting
		require_once 'SA_Voting.php';
		SA_Voting::init();

		// Templating
		require_once 'SA_Templates.php';
		SA_Templates::init();

		// Notifications
		require_once 'SA_Notifications.php';
		SA_Notifications::init();

		// Twilio Services
		require_once 'SA_Twilio.php';

		// Options
		require_once 'SA_Options.php';
		require_once 'SA_MetaBox.php';

		if ( is_admin() ) {

			// Admin
			require_once 'SA_Admin.php';
			SA_Admin::init();

			SA_Options::init();
			SA_MetaBox::init();
		}


		require_once GB_SUGGESTIONS_ADVANCED_PATH . 'lib/template-tags.php';

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
