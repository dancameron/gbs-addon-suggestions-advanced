<?php

class SA_Post_Type extends Group_Buying_Deal {

	const TAX = 'deal_suggestions';
	const TERM = 'deals';
	const TERM_SLUG = 'suggested_deal';
	const REWRITE_SLUG = 'suggested';
	const QUERY_VAR = 'suggestion';
	const MAX_VOTES = 1;

	private static $instances = array();

	private static $meta_keys = array(
		'author' => '_suggestion_author', // int
		'threshold' => '_vote_threshold', // int
		'voters' => '_suggestion_voters', // associated array
		//'user_meta_prefix' => 'gb_suggested_by_', // string
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
		self::register_taxonomy( self::TAX, array( Group_Buying_Deal::POST_TYPE ), $singular, $plural, $taxonomy_args );

		// Filter Suggestions
		add_action( 'pre_get_posts', array( get_class(), 'filter_query' ), 10, 1 );
	}

	/**
	 *
	 *
	 * @static
	 * @param int     $id
	 * @return Group_Buying_Attribute
	 */
	public static function get_instance( $id = 0 ) {
		if ( !$id ) {
			return NULL;
		}
		if ( !isset( self::$instances[$id] ) || !self::$instances[$id] instanceof self ) {
			self::$instances[$id] = new self( $id );
		}
		if ( self::$instances[$id]->post->post_type != parent::POST_TYPE ) {
			return NULL;
		}
		return self::$instances[$id];
	}

	protected function __construct( $id ) {
		parent::__construct( $id );
	}

	public function is_suggested_deal() {
		$term = array_pop( wp_get_object_terms( $this->get_id(), self::TAX ) );
		return $term->slug == self::TERM_SLUG;
	}

	public function make_suggested_deal() {
		wp_set_object_terms( $this->get_id(), self::get_term_slug(), self::TAX );
	}

	public function unmake_suggested_deal() {
		wp_set_object_terms( $this->get_id(), array(), self::TAX );
	}

	public static function get_url() {
		return get_term_link( self::TERM, self::TAX );
	}

	public function set_author( $user_id = 0 ) {
		if ( !$user_id ) {
			$user_id = get_current_user_id();
		}
		$this->save_post_meta( array( self::$meta_keys['author'] => $user_id ) );
	}

	public function get_author() {
		return $this->get_post_meta( self::$meta_keys['author'] );
	}

	public function set_threshold( $threshold = 0 ) {
		$this->save_post_meta( array( self::$meta_keys['threshold'] => $threshold ) );
	}

	public function get_threshold() {
		return $this->get_post_meta( self::$meta_keys['threshold'] );
	}

	public function set_vote( $user_id = 0, $data = array() ) {
		if ( !$user_id ) {
			$user_id = get_current_user_id();
		}
		$voters = $this->get_voters();
		if ( empty( $voters ) ) {
			$voters = array();
		}
		$voters[$user_id] = $data;
		$this->save_post_meta( array( self::$meta_keys['voters'] => $voters ) );
	}

	public function get_voters() {
		return $this->get_post_meta( self::$meta_keys['voters'] );
	}

	public function get_vote_by_user( $user_id = 0 ) {
		if ( !$user_id ) {
			$user_id = get_current_user_id();
		}
		$vote = 0;
		$voters = $this->get_voters();
		if ( array_key_exists( $voters[$user_id] ) ) {
			$vote = count( array_keys( $voters, $user_id, true ) );
		}
		return $vote;
	}

	public function allowed_to_vote( $user_id = null ) {
		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}
		$votes = $this->get_votes_by_user( $user_id );
		if ( $votes >= self::MAX_VOTES ) {
			return FALSE;
		}
		return TRUE;
	}

	public static function get_term_slug() {
		$term = get_term_by( 'slug', self::TERM_SLUG, self::TAX );
		if ( !empty( $term->slug ) ) {
			return $term->slug;
		} else {
			$return = wp_insert_term(
				self::TERM, // the term
				self::TAX, // the taxonomy
				array(
					'description'=> 'This is a suggested deal.',
					'slug' => self::TERM_SLUG )
			);
			return $return['slug'];
		}
	}
	/**
	 * Edit the query to remove suggestions from loops
	 *
	 * @param WP_Query $query
	 * @return void
	 */
	public static function filter_query( WP_Query $wp_query ) {
		// we only care if this is the query for vouchers
		if ( ( self::is_deal_query( $wp_query ) || self::is_deal_tax_query( $wp_query ) || is_search() ) && !is_admin() && $query->query_vars['post_status'] != 'pending' ) {
			// get all the user's purchases
			$wp_query->set( 'tax_query', array( array( 'taxonomy' => self::TAX, 'field' => 'slug', 'terms' => array( self::TERM ), 'operator' => 'NOT IN' ) ) );
		}
		return $wp_query;
	}

	public static function is_suggestion_query( WP_Query $query = NULL ) {
		$taxonomy = get_query_var( 'taxonomy' );
		if ( $taxonomy == self::TAX || $taxonomy == self::TAX || $taxonomy == self::TAX ) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Find all Suggestions associated with a specific user
	 *
	 * @static
	 * @param int     $user_id ID of the user to search by
	 * @return array List of IDs of Suggestions associated with the given purchase
	 */
	public static function get_suggestions_by_user( $user_id ) {
		$suggestion_ids = self::find_by_meta( self::POST_TYPE, array( self::$meta_keys['author'] => $user_id ) );
		return $suggestion_ids;
	}

	/**
	 * Get a list of pending suggestions for the given user
	 *
	 * @static
	 * @param int     $deal_id
	 * @return array The IDs of the pending suggestions
	 */
	public static function get_pending_suggestions( $user_id ) {
		$query = new WP_Query( 
			array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'pending',
				'posts_per_page' => -1,
				'orderby' => 'id',
				'meta_query' => array(
					array(
						'key' => self::$meta_keys['user'],
						'value' => $user_id,
						'type' => 'NUMERIC',
					),
				),
			) );
		$posts = get_posts( $args );
		return $posts;
	}
}
