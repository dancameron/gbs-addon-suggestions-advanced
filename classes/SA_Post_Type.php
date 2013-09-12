<?php

class SA_Post_Type extends Group_Buying_Deal {

	const TAX = 'deal_suggestions';
	const TERM = 'Suggested Deals';
	const TERM_SLUG = 'deals';
	const REWRITE_SLUG = 'suggested';
	const QUERY_VAR = 'suggestion';
	const MAX_VOTES = 1;

	private static $instances = array();

	private static $meta_keys = array(
		// deal meta
		'author' => '_suggestion_author', // int
		'threshold' => '_vote_threshold', // int
		'votes' => '_suggestion_votes', // associated array
		'voters' => '_suggestion_voters_v4a', // associated array
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

	public static function get_url() {
		return get_term_link( self::TERM_SLUG, self::TAX );
	}

	public function is_suggested_deal() {
		$term = array_pop( wp_get_object_terms( $this->get_id(), self::TAX ) );
		return $term->slug == self::TERM_SLUG;
	}

	public function make_suggested_deal() {
		wp_set_object_terms( $this->get_id(), self::get_term_slug(), self::TAX );
	}

	public function unmake_suggested_deal() {
		do_action( 'gb_suggestion_published', $this );
		wp_set_object_terms( $this->get_id(), array(), self::TAX );
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
		do_action( 'gb_sa_set_vote', $user_id, $this->get_id(), $data );
		$voters[$user_id][] = $data;
		$this->save_post_meta( array( self::$meta_keys['voters'] => $voters ) );
		return $voters;
	}

	public function get_voters() {
		$meta = $this->get_post_meta( self::$meta_keys['voters'] );
		if ( !is_array( $meta ) ) {
			$meta = array();
		}
		return array_filter($meta);
	}

	public function set_votes( $votes ) {
		$this->save_post_meta( array( self::$meta_keys['votes'] => $votes ) );
	}

	public function get_votes( $refresh = TRUE ) {
		$voters = $this->get_voters();
		$votes = count( $voters );
		if ( $refresh ) {
			// Set the votes as a meta field, mostly so the admin can sort based on a single meta field
			$this->set_votes( $votes );
		}
		return $votes;
	}

	public function get_vote_by_user( $user_id = 0 ) {
		if ( !$user_id ) {
			$user_id = get_current_user_id();
		}
		$vote = 0;
		$voters = $this->get_voters();
		if ( array_key_exists( $user_id, $voters ) ) {
			$votes = count( $voters[$user_id] );
		}
		return $votes;
	}

	public function allowed_to_vote( $user_id = 0 ) {
		if ( !$user_id ) {
			$user_id = get_current_user_id();
		}
		$votes = $this->get_vote_by_user( $user_id );
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
			$term = wp_insert_term(
				self::TERM, // the term
				self::TAX, // the taxonomy
				array(
					'description'=> 'These are suggested deals.',
					'slug' => self::TERM_SLUG )
			);
			return self::TERM_SLUG;
		}
	}
	/**
	 * Edit the query to remove suggestions from loops
	 *
	 * @param WP_Query $query
	 * @return void
	 */
	public static function filter_query( WP_Query $wp_query ) {
		// we only care if this is the query for deals
		if ( !isset( $wp_query->query_vars['by_pass_suggestion_filter'] ) && !self::is_suggestion_query( $wp_query ) && !is_admin() ) {
			// remove all suggestions
			$wp_query->set( 'tax_query', array( array( 'taxonomy' => self::TAX, 'field' => 'slug', 'terms' => array( self::TERM_SLUG ), 'operator' => 'NOT IN' ) ) );
		}
		return $wp_query;
	}

	public static function is_suggestion_query( WP_Query $wp_query = NULL ) {
		if ( is_a( $wp_query, 'WP_Query' ) ) {
			return $wp_query->is_tax( self::TAX, self::TERM_SLUG );	
		}
		$taxonomy = get_query_var( 'taxonomy' );
		if ( $taxonomy == self::TAX ) {
			return TRUE;
		}
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
	 * Get a list of all suggestions for the given user
	 *
	 * @static
	 * @param int     $deal_id
	 * @return array The IDs of the pending suggestions
	 */
	public static function get_all_suggestions( $user_id ) {
		$query = new WP_Query(
			array(
				'post_type' => self::POST_TYPE,
				'posts_per_page' => -1,
				'orderby' => 'id',
				'meta_query' => array(
					array(
						'key' => self::$meta_keys['author'],
						'value' => $user_id,
						'type' => 'NUMERIC',
					),
				),
			) );
		$posts = get_posts( $args );
		return $posts;
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
						'key' => self::$meta_keys['author'],
						'value' => $user_id,
						'type' => 'NUMERIC',
					),
				),
			) );
		$posts = get_posts( $args );
		return $posts;
	}
}
