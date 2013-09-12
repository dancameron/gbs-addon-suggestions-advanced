<?php 


/**
* Admin
*/
class SA_Admin extends Group_Buying_Controller {
	
	public static function init() {
		
		add_filter( 'views_edit-gb_deal', array( get_class(), 'modify_views' ) );

		// Admin columns
		add_filter( 'manage_edit-'.Group_Buying_Deal::POST_TYPE.'_columns', array( get_class(), 'register_columns' ) );
		add_filter( 'manage_'.Group_Buying_Deal::POST_TYPE.'_posts_custom_column', array( get_class(), 'column_display' ), 10, 2 );
		add_filter( 'manage_edit-'.Group_Buying_Deal::POST_TYPE.'_sortable_columns', array( get_class(), 'sortable_columns' ) );
		add_filter( 'request', array( get_class(), 'column_orderby' ) );
	}

	public function modify_views( $views ) {
		$term = get_term_by( 'slug', SA_Post_Type::get_term_slug(), SA_Post_Type::TAX );
		$class = ( isset( $_GET[SA_Post_Type::TAX] ) ) ? 'class="current"' : '' ;
		$views['suggested_deals'] = '<a href="'.esc_url ( add_query_arg( array( SA_Post_Type::TAX => $term->slug, 'post_type' => Group_Buying_Deal::POST_TYPE ), 'edit.php' ) ).'" '.$class.'>'.self::__('Suggestions').' <span class="count">('.$term->count.')</span></a>';
		return $views;
	}

	public static function register_columns( $columns ) {
		$columns['votes'] = self::__( 'Total Votes' );
		$columns['remaining_votes'] = self::__( 'Remaining Votes' );
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

		case 'remaining_votes':
			$total_votes = $suggested_deal->get_votes();
			$threshold = $suggested_deal->get_threshold();
			$remaining = ( $threshold-$total_votes >= 0 ) ? $threshold-$total_votes : 0 ;
			echo $remaining;
			break;

		default:
			// code...
			break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['votes'] = 'votes';
		$columns['remaining_votes'] = 'remaining_votes';
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