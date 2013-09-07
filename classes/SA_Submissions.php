<?php

/**
 * Hook into existing deal submission process, adding a hidden field. 
 * After submission process the suggested deal and redirect.
 */
class SA_Submissions extends Group_Buying_Controller {

	const SUGGEST_QUERY_VAR = 'suggestion';
	const SUGGESTION_OPTION_NAME = 'is_suggestion';
	private static $create_path = 'create_suggestion';

	public static function init() {
		add_action( 'submit_deal', array( get_class(), 'new_suggestion' ) );
		add_action( 'gb_deal_submission_fields', array( get_class(), 'suggestion_gb_deal_submission_fields' ) );
	}

	public static function new_suggestion( Group_Buying_Deal $deal ) {
		if ( isset( $_POST['gb_deal_'.self::SUGGESTION_OPTION_NAME] ) && $_POST['gb_deal_'.self::SUGGESTION_OPTION_NAME] ) {
			$suggestion = SA_Post_Type::get_instance( $deal->get_id() );
			$suggestion->make_suggested_deal();
			self::set_message( __( 'Thanks! Your Suggestion has been Submitted for Review.' ), self::MESSAGE_STATUS_INFO );
			wp_redirect( SA_Post_Type::get_url() );
			exit();
		}
	}

	public static function suggestion_gb_deal_submission_fields( $fields ) {
		if ( isset( $_REQUEST[self::SUGGEST_QUERY_VAR] ) && $_REQUEST[self::SUGGEST_QUERY_VAR] ) {
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
			$fields[self::SUGGESTION_OPTION_NAME] = array(
				'weight' => 500,
				'type' => 'hidden',
				'value' => '1',
			);
		}
		return $fields;
	}
}
