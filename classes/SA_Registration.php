<?php

class SA_Registration extends Group_Buying_Controller {

	// List all of your field IDs here as constants
	const MOBILE_NUMBER = 'gb_account_fields_mobile';

	public static function init() {

		// Voting hook, check if mobile number needs to be changed
		add_action( 'gb_sa_set_vote', array( get_class(), 'maybe_change_number' ), 10, 3 );

		// registration hooks
		add_filter( 'gb_account_registration_panes', array( get_class(), 'get_registration_panes' ), 100 );
		// add_filter( 'gb_validate_account_registration', array( get_class(), 'validate_account_fields' ), 10, 4 );
		add_action( 'gb_registration', array( get_class(), 'process_registration' ), 50, 5 );

		// Add the options to the account edit screens
		add_filter( 'gb_account_edit_panes', array( get_class(), 'get_edit_fields' ), 0, 2 );
		add_action( 'gb_process_account_edit_form', array( get_class(), 'process_edit_account' ) );

		// Hook into the reports
		add_filter( 'set_deal_purchase_report_data_column', array( get_class(), 'reports_columns' ), 10, 2 );
		add_filter( 'set_merchant_purchase_report_column', array( get_class(), 'reports_columns' ), 10, 2 );
		add_filter( 'set_accounts_report_data_column', array( get_class(), 'reports_columns' ), 10, 2 );
		add_filter( 'gb_deal_purchase_record_item', array( get_class(), 'reports_record' ), 10, 3 );
		add_filter( 'gb_merch_purchase_record_item', array( get_class(), 'reports_record' ), 10, 3 );
		add_filter( 'gb_accounts_record_item', array( get_class(), 'reports_account_record' ), 10, 3 );

	}

	public function maybe_change_number( $user_id = 0, $suggestion_id = 0, $data = array() ) {
		if ( isset( $data['mobile_number'] ) && ( strlen( $data['mobile_number'] ) > 7 ) ) {
			$account_id = Group_Buying_Account::get_account_id_for_user( $user_id );
			self::set_mobile_number( $account_id, $data['mobile_number'] );
		}
	}

	/**
	 * Add the report coloumns.
	 *
	 * @param array
	 * @return null
	 */
	public function reports_columns( $columns ) {
		// Add as many as you want with their own key that will be used later.
		$columns['sa_mobile_number'] = self::__( 'Mobile' );
		return $columns;
	}

	/**
	 * Add the report record for deal purchase and merchant report.
	 *
	 * @param array
	 * @return null
	 */
	public function reports_record( $array, $purchase, $account ) {
		if ( !is_a( $account, 'Group_Buying_Account' ) ) {
			return $array;
		}
		// Add as many as you want with their own matching key from the reports_column
		$array['sa_mobile_number'] = get_post_meta( $account->get_ID(), '_'.self::MOBILE_NUMBER, TRUE );
		return $array;
	}

	/**
	 * Add the report record for account report
	 *
	 * @param array
	 * @return null
	 */
	public function reports_account_record( $array, $account ) {
		// Add as many as you want with their own matching key from the reports_column
		$array['sa_mobile_number'] = get_post_meta( $account->get_ID(), '_'.self::MOBILE_NUMBER, TRUE );
		return $array;
	}

	/**
	 * Hook into the process registration action
	 *
	 * @param array
	 * @return null
	 */
	public function process_registration( $user = null, $user_login = null, $user_email = null, $password = null, $post = null ) {
		$account = Group_Buying_Account::get_instance( $user->ID );
		// using the single callback below
		self::process_form( $account );
	}

	/**
	 * Hook into the process edit account action
	 *
	 * @param array
	 * @return null
	 */
	public static function process_edit_account( Group_Buying_Account $account ) {
		// using the single callback below
		self::process_form( $account );
	}

	/**
	 * Process the form submission and save the meta
	 *
	 * @param array   | Group_Buying_Account
	 * @return null
	 */
	public static function process_form( Group_Buying_Account $account ) {
		delete_post_meta( $account->get_ID(), '_'.self::MOBILE_NUMBER );
		// Copy all of the new fields below, copy the below if it's a basic field.
		if ( isset( $_POST[self::MOBILE_NUMBER] ) && $_POST[self::MOBILE_NUMBER] != '' ) {
			// TODO check length and throw and error
			self::set_mobile_number( $account->get_ID(), $_POST[self::MOBILE_NUMBER] );
		}
	}

	/**
	 * Add a file as a post attachment.
	 *
	 * @return null
	 */
	public static function set_attachement( $post_id, $files ) {
		if ( !function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin' . '/includes/image.php';
			require_once ABSPATH . 'wp-admin' . '/includes/file.php';
			require_once ABSPATH . 'wp-admin' . '/includes/media.php';
		}
		foreach ( $files as $file => $array ) {
			if ( $files[$file]['error'] !== UPLOAD_ERR_OK ) {
				self::set_message( 'upload error : ' . $files[$file]['error'] );
			}
			$attach_id = media_handle_upload( $file, $post_id );
		}
		// Make it a thumbnail while we're at it.
		if ( $attach_id > 0 ) {
			update_post_meta( $post_id, '_thumbnail_id', $attach_id );
		}
		return $attach_id;
	}

	/**
	 * Validate the form submitted
	 *
	 * @return array
	 */
	public function validate_account_fields( $errors, $username, $email_address, $post ) {
		// If the field is required it should
		if ( isset( $post[self::MOBILE_NUMBER] ) && $post[self::MOBILE_NUMBER] == '' ) {
			$errors[] = self::__( '"Mobile Number" is required.' );
		}
		return $errors;
	}

	/**
	 * Add the default pane to the account edit form
	 *
	 * @param array   $panes
	 * @return array
	 */
	public function get_registration_panes( array $panes ) {
		$panes['mobile_fields'] = array(
			'weight' => 90,
			'body' => self::_load_view_string( 'registration-pane', array( 'fields' => self::fields() ) ),
		);
		return $panes;
	}

	/**
	 * Add the fields to the registration form
	 *
	 * @param Group_Buying_Account $account
	 * @return array
	 */
	private function fields( $account = NULL ) {
		$fields = array(
			'mobile' => array(
				'weight' => 0, // sort order
				'label' => self::__( 'Mobile Number' ), // the label of the field
				'type' => 'text', // type of field (e.g. text, textarea, checkbox, etc. )
				'required' => FALSE, // If this is false then don't validate the post in validate_account_fields
				'placeholder' => 'X-XXX-XXX-XXXX' // the default value
			),
			// add new fields here within the current array.
		);
		$fields = apply_filters( 'sa_registration_fields', $fields );
		return $fields;
	}

	/**
	 * Add the default pane to the account edit form
	 *
	 * @param array   $panes
	 * @param Group_Buying_Account $account
	 * @return array
	 */
	public function get_edit_fields( array $panes, Group_Buying_Account $account ) {
		$panes['mobile_fields'] = array(
			'weight' => 50,
			'body' => self::_load_view_string( 'registration-pane', array( 'fields' => self::edit_fields( $account ) ) ),
		);
		return $panes;
	}


	/**
	 * Add the fields to the account form
	 *
	 * @param Group_Buying_Account $account
	 * @return array
	 */
	private function edit_fields( $account = NULL ) {
		$fields = array(
			'mobile' => array(
				'weight' => 0, // sort order
				'label' => self::__( 'Mobile Number' ), // the label of the field
				'type' => 'text', // type of field (e.g. text, textarea, checkbox, etc. )
				'required' => FALSE, // If this is false then don't validate the post in validate_account_fields
				'placeholder' => 'X-XXX-XXX-XXXX', // the default value
				'default' => self::get_mobile_number( $account )
			),
			// add new fields here within the current array.
		);
		uasort( $fields, array( get_class(), 'sort_by_weight' ) );
		$fields = apply_filters( 'invite_only_fields', $fields );
		return $fields;
	}

	/**
	 * return a view as a string.
	 *
	 */
	private static function _load_view_string( $path, $args ) {
		ob_start();
		if ( !empty( $args ) ) extract( $args );
		@include GB_SUGGESTIONS_ADVANCED_PATH . 'views/'.$path.'.php';
		return ob_get_clean();
	}

	public function set_mobile_number( $account_id, $number ) {
		update_post_meta( $account_id, '_'.self::MOBILE_NUMBER, $number );
	}

	public function get_mobile_number( Group_Buying_Account $account ) {
		return get_post_meta( $account->get_ID(), '_'.self::MOBILE_NUMBER, TRUE );
	}
}
