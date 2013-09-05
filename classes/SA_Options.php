<?php

class SA_Options extends Group_Buying_Controller {
	const ACCOUNT = 'gb_twilio_account';
	const AUTH = 'gb_twilio_auth';
	const NUMBER = 'gb_twilio_number';
	public static $twilio_number;
	public static $twilio_account;
	public static $twilio_auth;

	public static function init() {
		self::$twilio_account = get_option( self::ACCOUNT, '' );
		self::$twilio_auth = get_option( self::AUTH, '' );
		self::$twilio_number = get_option( self::NUMBER, '' );

		// Options
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 0 );
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_twilio_settings';
		add_settings_section( $section, self::__( 'Suggested Deals: Twilio settings' ), array( get_class(), 'display_settings_section' ), $page );
		// Settings
		register_setting( $page, self::ACCOUNT );
		register_setting( $page, self::AUTH );
		register_setting( $page, self::NUMBER );
		// Fields
		add_settings_field( self::ACCOUNT, self::__( 'Twilio Account SID' ), array( get_class(), 'display_twilio_account_option' ), $page, $section );
		add_settings_field( self::AUTH, self::__( 'Twilio Authentication Token' ), array( get_class(), 'display_twilio_auth_option' ), $page, $section );
		add_settings_field( self::NUMBER, self::__( 'From Number: Twilio number in your account' ), array( get_class(), 'display_twilio_number_option' ), $page, $section );
	}

	public static function display_twilio_account_option() {
		echo '<input name="'.self::ACCOUNT.'" id="'.self::ACCOUNT.'" type="text" value="'.self::$twilio_account.'">';
	}

	public static function display_twilio_auth_option() {
		echo '<input name="'.self::AUTH.'" id="'.self::AUTH.'" type="text" value="'.self::$twilio_auth.'">';
	}

	public static function display_twilio_number_option() {
		echo '<input name="'.self::NUMBER.'" id="'.self::NUMBER.'" type="text" placeholder="+XXXXXXXXXXX" value="'.self::$twilio_number.'">';
	}

}
