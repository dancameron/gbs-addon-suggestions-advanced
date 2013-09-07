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
	}

	public static function override_template( $template ) {
		if ( SA_Post_Type::is_suggestion_query() ) {
			$template = self::locate_template( array(
					'deals/index-suggestions.php',
					'deals/suggestions.php',
				), FALSE );
			if ( $template == FALSE ) {
				$template = GB_SUGGESTIONS_ADVANCED_PATH . '/views/' . GBS_THEME_SLUG . '/gbs/deals/suggestions.php';
			}
		}
		return $template;
	}

	public static function custom_gb_deals_index_title( $title ) {
		if ( SA_Post_Type::is_suggestion_query() ) {
			$title = gb_e( 'Suggested Deals' );
		}
		return $title;

	}

	public static function screen() {
		if ( SA_Post_Type::is_suggestion_query() ) {
			wp_enqueue_style( 'suggested_style', GB_SUGGESTIONS_ADVANCED_RESOURCES_URL.'suggestions.css', '', '1', 'screen' );
		}
	}

}