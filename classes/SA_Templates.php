<?php 


/**
* Templating
*/
class SA_Templates extends Group_Buying_Controller {
	
	public static function init() {
		add_filter( 'template_include', array( get_class(), 'override_template' ) );
		add_filter( 'gb_deals_index_title', array( get_class(), 'custom_gb_deals_index_title' ) );

		if ( !is_admin() ) {
			add_filter( 'wp_print_styles', array( get_class(), 'screen' ) );
		}

		add_action( 'account_section_before_dash', array( get_class(), 'dashboard' ) );
	}

	public static function override_template( $template ) {
		if ( SA_Post_Type::is_suggestion_query() ) {
			$template = self::locate_template( array(
					'deals/index-suggestions.php',
					'deals/suggestions.php',
				), FALSE );
			if ( $template == FALSE ) {
				$template = GB_SUGGESTIONS_ADVANCED_PATH . 'views/' . GBS_THEME_SLUG . '/gbs/deals/suggestions.php';
			}
		}
		return $template;
	}

	public function dashboard() {
		$template_path = self::locate_template( array(
				'account/voting_dashboard.php',
				'account/voting-dashboard.php',
			), FALSE );
		if ( $template_path == FALSE ) {
			$template_path = GB_SUGGESTIONS_ADVANCED_PATH . 'views/' . GBS_THEME_SLUG . '/gbs/account/voting_dashboard.php';
		}
		print self::_load_view_string( $template_path );
	}

	public static function custom_gb_deals_index_title( $title ) {
		if ( SA_Post_Type::is_suggestion_query() ) {
			$title = gb_e( 'Suggested Deals' );
		}
		return $title;

	}

	public static function screen() {
		if ( SA_Post_Type::is_suggestion_query() ) {
			wp_enqueue_style( 'gb-timestamp-jquery-ui-css' );
			wp_enqueue_style( 'gb_frontend_jquery_ui_style' );
			wp_enqueue_style( 'suggested_style', GB_SUGGESTIONS_ADVANCED_RESOURCES_URL.'suggestions.css', array(), '1', 'screen' );
		}
	}

	/**
	 * return a view as a string.
	 *
	 */
	private static function _load_view_string( $path ) {
		ob_start();
		include $path;
		return ob_get_clean();
	}

}