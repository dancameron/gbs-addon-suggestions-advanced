<?php 


/**
* Admin
*/
class SA_Admin extends Group_Buying_Controller {
	
	public static function init() {
		// Admin columns
		add_filter( 'manage_edit-'.Group_Buying_Deal::POST_TYPE.'_columns', array( get_class(), 'register_columns' ) );
		add_filter( 'manage_'.Group_Buying_Deal::POST_TYPE.'_posts_custom_column', array( get_class(), 'column_display' ), 10, 2 );
		add_filter( 'manage_edit-'.Group_Buying_Deal::POST_TYPE.'_sortable_columns', array( get_class(), 'sortable_columns' ) );
		add_filter( 'request', array( get_class(), 'column_orderby' ) );
	}

	public static function register_columns( $columns ) {
		$columns['votes'] = self::__( 'Votes' );
		return $columns;
	}


	public static function column_display( $column_name, $id ) {
		global $post;
		$suggested_deal = SA_Post_Type::get_instance( $id );

		if ( !is_a( $suggested_deal, 'Group_Buying_Deal' ) )
			return; // return for that temp post

		switch ( $column_name ) {

		case 'votes':
			echo $suggested_deal->get_votes();
			break;

		default:
			// code...
			break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['votes'] = 'votes';
		return $columns;
	}

	public function column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && is_admin() ) {
			switch ( $vars['orderby'] ) {
			case 'votes':
				$vars = array_merge( $vars, array(
						'meta_key' => '_suggestion_votes',
						'orderby' => 'meta_value_num'
					) );
				break;
			default:
				// code...
				break;
			}
		}

		return $vars;
	}
}