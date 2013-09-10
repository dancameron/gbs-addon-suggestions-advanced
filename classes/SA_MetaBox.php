<?php

class SA_MetaBox {
	const OPTION_NAME = 'gb_suggested_deal';
	const VOTES_OPTION_NAME = 'gb_suggested_deal_vote_threshold';
	const PUBLISH_OPTION_NAME = 'gb_notifiy_voters';

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ), 10, 0 );
		add_action( 'save_post', array( __CLASS__, 'save_meta_box' ), 10, 2 );
	}

	public function get_deals_numbers( $deal_id ) {
		$meta = get_post_meta( $deal_id, SMS_Checkout_Notes_Addon::META_KEY, TRUE );
		return $meta;
	}

	public static function add_meta_box() {
		add_meta_box( 'suggested', gb__( 'Suggested' ), array( __CLASS__, 'show_meta_box' ), Group_Buying_Deal::POST_TYPE, 'side' );
	}

	public static function show_meta_box( $post ) {
		$suggestion = SA_Post_Type::get_instance( $post->ID );

		?>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					jQuery( "input[name='<?php echo self::PUBLISH_OPTION_NAME ?>']" ).on( 'click', function() {
						jQuery( "input[name='<?php echo self::OPTION_NAME ?>']").prop("checked", false);
					});
					jQuery( "input[name='<?php echo self::OPTION_NAME ?>']" ).on( 'click', function() {
						jQuery( "input[name='<?php echo self::PUBLISH_OPTION_NAME ?>']").prop("checked", false);
					});
				});
			</script>
		<?php

		// Is suggested Deal
		$checked = $suggestion->is_suggested_deal();
		printf( '<p><label><input type="checkbox" name="%s" %s /> %s</label></p>', self::OPTION_NAME, checked( $checked, TRUE, FALSE ), gb__( 'This is a suggested deal.' ) );

		if ( SA_Notifications::when_notifications_sent( $post->ID ) == '' ) {
			printf( '<p><label><input type="checkbox" name="%s"/> %s</label></p><p class="description">%s</p>', self::PUBLISH_OPTION_NAME, gb__( 'Publish and send all "suggested published" notifications.' ), gb__( 'Cannot be undone.' ) );
			echo '<span class="meta_box_block_divider"></span>';
		}
		else {
			printf( gb__( 'Notifications sent: %s.' ), date( get_option( 'date_format' ).', '.get_option( 'time_format' ), SA_Notifications::when_notifications_sent( $post->ID ) ) );
		}

		$threshold = ( $suggestion->get_threshold() ) ? $suggestion->get_threshold() : 10 ;
		printf( '<p><label>%s&nbsp;<input type="number" value="%s" name="%s" min="1" style="width:4em;"/></label>.</p>', gb__( 'The voting threshold will be' ), $threshold, self::VOTES_OPTION_NAME );
		
		do_action( 'gb_suggestions_meta_box', $post->ID );
		
		echo '<span class="meta_box_block_divider"></span>';

		// Show current votes, high low
		printf( '<p class="description">Mean/Average: %s</p>', gb_get_formatted_money( SA_Voting::mmmr_prices( 'mean' ) ) );
		printf( '<p class="description">Median: %s</p>', gb_get_formatted_money( SA_Voting::mmmr_prices( 'median' ) ) );
		printf( '<p class="description">Mode: %s</p>', gb_get_formatted_money( SA_Voting::mmmr_prices( 'mode' ) ) );
		printf( '<p class="description">Range: %s</p>', gb_get_formatted_money( SA_Voting::mmmr_prices( 'range' ) ) );
	}

	public static function save_meta_box( $post_id, $post ) {
		// only continue if it's a deal post
		if ( $post->post_type != Group_Buying_Deal::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( empty( $_POST[self::VOTES_OPTION_NAME] ) || wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		$suggestion = SA_Post_Type::get_instance( $post->ID );
		$suggestion->unmake_suggested_deal();
		if ( $_POST[self::OPTION_NAME] ) {
			$suggestion->make_suggested_deal();
		}
		if ( $_POST[self::PUBLISH_OPTION_NAME] ) {
			do_action( 'gb_suggested_deal_published', $suggestion );
		}
		$suggestion->set_threshold( $_POST[self::VOTES_OPTION_NAME] );
	}
}
