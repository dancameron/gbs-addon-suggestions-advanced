<?php
	
class SA_Suggestion extends Group_Buying_Deal {

	const TAX = 'gb_suggestions';
	const TERM = 'deals';
	const REWRITE_SLUG = 'suggested';
	const QUERY_VAR = 'suggestion';
	const MAX_VOTES = 1;

	private static $meta_keys = array(
		'creator' => '_suggestion_creator', // int
		'voters' => '_suggestion_voters_array', // int
		'votes' => '_suggestion_votes', // string
		'user_prefix' => 'gb_suggested_by_', // string
	);
	
	public static function init() {
		
		// register Locations taxonomy
		$singular = 'Suggestion';
		$plural = 'Suggestions';
		$taxonomy_args = array(
			'public' => FALSE,
			'show_ui' => FALSE,
			'rewrite' => array(
				'slug' => self::REWRITE_SLUG,
				'with_front' => TRUE,
				'hierarchical' => FALSE,
			),
		);
		self::register_taxonomy(self::TAX, array(Group_Buying_Deal::POST_TYPE), $singular, $plural, $taxonomy_args);
		
		add_action('pre_get_posts', array(get_class(), 'filter_query'), 10, 1);
	}

	/**
	 * Edit the query to remove suggestions from loops
	 *
	 * @param WP_Query $query
	 * @return void
	 */
	public static function filter_query( WP_Query $wp_query ) {
		// we only care if this is the query for vouchers
		if ( ( self::is_deal_query($wp_query) || self::is_deal_tax_query($wp_query) || is_search() ) && !is_admin() && $query->query_vars['post_status'] != 'pending' ) {
			// get all the user's purchases
			$wp_query->set('tax_query', array( array( 'taxonomy' => self::TAX, 'field' => 'slug', 'terms' => array( self::TERM ), 'operator' => 'NOT IN' )) );
		}
		return $wp_query;
	}

	public static function get_term_slug() {
		$term = get_term_by('slug', Group_Buying_Suggestion::TERM, Group_Buying_Suggestion::TAX);
		if ( !empty($term->slug) ) {
			return $term->slug;
		} else {
			$return = wp_insert_term(
				self::TERM, // the term 
				self::TAX, // the taxonomy
					array(
						'description'=> 'This is a suggested deal.',
						'slug' => self::TERM, )
				);
			return $return['slug'];
		}

	}

	public static function get_url() {
		return get_term_link( self::TERM, self::TAX);
	}

	public function allowed_to_vote( Group_Buying_Deal $deal, $user_id = null ) {
		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}
		$votes = self::get_votes_by_user($deal, $user_id);
		if ( $votes >= self::MAX_VOTES ) {
			return FALSE;
		}
		return TRUE;
	}
	
	public function set_user( Group_Buying_Deal $deal, $user_id = null ) {
		if ( null == $user_id ) {
			$user_id = get_current_user_id();
		}
		$deal->save_post_meta(array(self::$meta_keys['creator'] => $user_id));
	}

	public function set_vote( Group_Buying_Deal $deal, $user_id = null, $talley = 1 ) {
		if ( !self::allowed_to_vote($deal, $user_id) ) {
			return self::__('Limit Exceeded');
		}
		if ( $talley > 0 ) {
			self::add_voter($deal, $user_id);
			$votes = self::get_votes($deal) + $talley;
		} else {
			$votes = 0;
		}
		$deal->save_post_meta(array(self::$meta_keys['votes'] => $votes));
		return $votes;
	}

	public function get_votes( Group_Buying_Deal $deal ) {
		$votes = $deal->get_post_meta(self::$meta_keys['votes']);
		if ( empty($votes) ) {
			$votes = self::set_vote($deal,null,0);
		}
		return $votes;
	}

	public function get_votes_by_user( Group_Buying_Deal $deal, $user_id = null ) {
		$votes = $deal->get_post_meta(self::$meta_keys['user_prefix'].$user_id,TRUE);
		return $votes;
	}

	public function add_voter( Group_Buying_Deal $deal, $user_id = null ) {
		if ( null == $user_id ) {
			$user_id = get_current_user_id();
		}
		$voters = self::get_voters($deal);
		$voters = array_merge((array)$voters, array($user_id));
		$deal->save_post_meta(array(self::$meta_keys['voters'] => $voters));
		$count = $deal->save_post_meta(array(self::$meta_keys['user_prefix'].$user_id => 1));
		return $count;
	}

	public function get_voters( Group_Buying_Deal $deal ) {
		$voters = $deal->get_post_meta(self::$meta_keys['voters']);
		if ( empty($voters) || $voters = '' ) {
			return FALSE;
		}
		return $deal->get_post_meta(self::$meta_keys['voters']);
	}

	public static function is_suggestion_query( WP_Query $query = NULL ) {
		$taxonomy = get_query_var('taxonomy');
		if ( $taxonomy == self::TAX || $taxonomy == self::TAX || $taxonomy == self::TAX ) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Find all Suggestions associated with a specific user
	 * @static
	 * @param int $user_id ID of the user to search by
	 * @return array List of IDs of Suggestions associated with the given purchase
	 */
	public static function get_suggestions_by_user( $user_id ) {
		$suggestion_ids = self::find_by_meta( self::POST_TYPE, array( self::$meta_keys['creator'] => $user_id ) );
		return $suggestion_ids;
	}

	/**
	 * Get a list of pending suggestions for the given user
	 *
	 * @static
	 * @param int $deal_id
	 * @return array The IDs of the pending suggestions
	 */
	public static function get_pending_suggestions( $user_id ) {
		$query = new WP_Query(array(
			'post_type' => self::POST_TYPE,
			'post_status' => 'pending',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => self::$meta_keys['user'],
					'value' => $user_id,
					'type' => 'NUMERIC',
				),
			),
		));
		$suggestions = array();
		foreach ( $query->posts as $post ) {
			$suggestions[] = $post->ID;
		}
		return $suggestions;
	}

	/**
	 *
	 * @param int $user the user to look against
	 * @return array List of IDs for suggestions with this user
	 */
	public static function get_suggestion_by_user( $user ) {
		$suggestions = self::find_by_meta( self::POST_TYPE, array( self::$meta_keys['user'] => $user ) );
		return $suggestions;
	}
}