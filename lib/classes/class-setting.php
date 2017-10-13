<?php

function mai_get_setting( $placeholder ) {

	// Allow devs to short circuit this function.
	$pre = apply_filters( "mai_pre_get_setting_{$key}", null );
	if ( null !== $pre ) {
		return $pre;
	}

	// Setup caches.
	static $settings_cache = array();

	// Check settings cache.
	if ( isset( $settings_cache[ $key ] ) ) {
		// Option has been cached.
		return $settings_cache[ $key ];
	}

	// New setting object.
	$setting = new Mai_Setting( $placeholder );

	// Setting has not been previously been cached, so cache now.
	$settings_cache[ $key ] = is_array( $setting->value ) ? stripslashes_deep( $setting->value ) : stripslashes( wp_kses_decode_entities( $setting->value ) );

	return $settings_cache[ $key ];
}

/**
 *
 * @package Mai_Setting
 */
class Mai_Setting {

	protected $name;
	protected $key;
	protected $value;
	protected $placeholders;
	protected $direct;
	protected $fallback;
	protected $check;
	protected $post_type;

	/**
	 * Setup the object.
	 *
	 * @param  string  $name  The setting name. May be a placeholder if the key uses the post_type name in it.
	 */
	function __construct( $name ) {
		$this->name         = $name;
		$this->key          = $this->key( $key );
		$this->value        = $this->value( $key );
		$this->placeholders = $this->placeholders();
		$this->direct       = $this->direct();   // bool
		$this->fallback     = $this->fallback(); // bool
		$this->check        = $this->check();    // bool
		$this->post_type    = mai_get_archive_post_type();
	}

	public function key( $placeholder ) {
		return $this->keys[ $placeholder ];
	}

	/**
	 * Get array of key names with post_type placeholders swapped for actual key name.
	 *
	 * @return  array  The keys as array( 'placeholder' => 'key' ).
	 */
	public function keys() {
		$keys = array();
		foreach( $this->placeholders as $placeholder ) {
			$keys[ $placeholder ] = str_replace( 'post_type', $this->post_type, $placeholder );
		}
		return $keys;
	}

 	/**
 	 * Generic placeholder names for all keys.
 	 * Actual key names may be post_type specific.
 	 *
 	 * @return  array  Placeholder key names.
 	 */
	public function placeholders() {
		return array(
			'banner_id',
			'hide_banner',                          // Archive (Main)
			'banner_disable_post_type',             // Singular
			'banner_disable_taxonomies_post_type',  // Archives (Taxo)
			'banner_featured_image_post_type',      // Singular
			'layout_archive_post_type',             // Archives
			'layout_post_type',                     // Singular
			'singular_image_post_type',             // Singular
			'remove_meta_post_type',                // Singular
			'enable_content_archive_settings',
			'columns',
			'content_archive',
			'content_archive_limit',
			'content_archive_thumbnail',
			'image_location',
			'image_size',
			'image_alignment',
			'more_link',
			'remove_loop',
			'remove_meta',
			'posts_per_page',
			'posts_nav',
		);
	}

	/**
	 * Check the keys the require a direct value.
	 *
	 * @return  bool
	 */
	public function direct() {

		// TODO: ADD SINGULAR KEYS HERE!

		$keys = array(
			'hide_banner',
			'remove_loop',
			'enable_content_archive_settings',
		);
		return in_array( $this->key, $keys ) ? true : false;
	}

	/**
	 * Check the keys that need a fallback.
	 * Most keys need a fallback, so we'll check against the ones the don't.
	 *
	 * @return  bool
	 */
	public function fallback() {
		$keys = array(
			'posts_per_page',
		)
		return ! in_array( $this->key, $keys ) ? true : false;
	}

	/**
	 * Check against the keys that need archive settings enabled.
	 *
	 * @return  bool
	 */
	public function check() {
		$keys = array(
			'content_archive',
			'content_archive_limit',
			'content_archive_thumbnail',
			'image_location',
			'image_size',
			'image_alignment',
			'more_link',
			'remove_meta',
			'posts_nav',
			'posts_per_page',
		);
		return in_array( $this->key, $keys ) ? true : false;
	}

	public function value( $key ) {

		$value = null;

		// Single post/page/cpt.
		if ( is_singular() ) {

		}

		// Blog.
		elseif ( is_home() ) {

			// If direct and blog is a static page.
			if ( $this->direct && ( $posts_page_id = get_option( 'page_for_posts' ) ) ) {
				return get_post_meta( $posts_page_id, $key, true );
			}

			$value = genesis_get_option( $key );
		}

		// Term archive.
		elseif ( is_category() || is_tag() || is_tax() ) {

			$queried_object = get_queried_object();

			/**
			 * Check if we have an object.
			 * We hit an issue when permlinks have /%category%/ in the base and a user
			 * 404's via top level URL like example.com/non-existent-slug.
			 * This returned true for is_category() and blew things up.
			 */
			if ( $queried_object ) {

				$term_meta = get_term_meta( $queried_object->term_id, $key, true );

				if ( $this->direct ) {
					return $term_meta;
				}

				// If not checking, or checking and is enabled.
				if ( ! $this->check || ( $this->check && (bool) get_term_meta( $queried_object->term_id, 'enable_content_archive_settings', true ) ) ) {
					$value = $term_meta;
				}

				// If no value.
				if ( ! $value ) {

					// Get hierarchical taxonomy term meta.
					$value = $this->get_term_meta_value_in_hierarchy( $queried_object, $key, $this->check );

					// If no value and 'post' is not the archive taxonomy, and not checking for content archive settings enabled or checking and they are enabled.
					if ( ! $value && ( 'post' !== $this->post_type ) && ( ! $this->check || ( $this->check && (bool) genesis_get_cpt_option( 'enable_content_archive_settings', $this->post_type ) ) ) ) {
						$value = genesis_get_cpt_option( $key, $this->post_type );
					}
				}
			}
		}

		// CPT archive.
		// elseif ( is_post_type_archive() && post_type_supports( $this->post_type, 'mai-cpt-settings' ) ) {
		elseif ( is_post_type_archive() ) {

			$post_type_option = genesis_get_cpt_option( $key );

			if ( $this->direct ) {
				return $post_type_option;
			}

			if ( ! $this->check || ( $this->check && (bool) genesis_get_cpt_option( 'enable_content_archive_settings', $this->post_type ) ) ) {
				$value = $post_type_option;
			}
		}

		// Author archive.
		elseif ( is_author() ) {
			$value = get_the_author_meta( $key, get_query_var( 'author' ) );
		}

		// Maybe get fallback.
		$value = ( ! $value && $this->fallback ) ? genesis_get_option( $key ) : $value;

		return $value;
	}

	/**
	 * Get the metadata value for the term. This function walks up the term hierarchy,
	 * searching each parent level to find a value for the given meta key. When it finds
	 * one, it's returned.
	 *
	 * To perform an archive settings check, turn on the $check_for_archive_setting flag to
	 * true.  This extra check does the following:
	 *
	 *   1.  Checks each level's  `enable_content_archive_settings` value.
	 *   2.  If it's enabled, then that level's meta value is returned, regardless if
	 *          it has a value or not.
	 *
	 * It works a level override, forcing that level to return it's value.
	 *
	 * @param  WP_Term  $term                       Term object
	 * @param  string   $meta_key                   Meta key for the value you want to retrieve
	 * @param  bool     $check_for_archive_setting  Flag to check if the `enable_content_archive_settings`
	 *                                              is set.  When TRUE, check if this flag is set.
	 *
	 * @return mixed
	 */
	function get_term_meta_value_in_hierarchy( WP_Term $term, $meta_key, $check_for_archive_setting = false ) {
		$meta_keys = array( $meta_key );
		if ( $check_for_archive_setting ) {
			$meta_keys[] = 'enable_content_archive_settings';
		}
		$term_ancestors = $this->get_hierarchichal_term_metadata( $term, $meta_keys );
		if ( false === $term_ancestors ) {
			return;
		}
		// Loop through the objects until you find one that has a meta value.
		foreach( (array) $term_ancestors as $term_ancestor ) {
			// If checking content archive setting.
			if ( $check_for_archive_setting ) {
				// If setting is on.
				if ( $term_ancestor->metadata2 ) {
					return $term_ancestor->metadata1;
				}
			}
			// Not checking for content archive, and we have a value
			elseif ( $term_ancestor->metadata1 ) {
				return $term_ancestor->metadata1;
			}
		}
		// Whoops, didn't find one with a value for that meta key.
		return;
	}

	/**
	 * Get the specified metadata value for the term or from
	 * one of it's parent terms.
	 *
	 * @param  WP_Term       $term      Term object
	 * @param  string|array  $meta_key  The meta key(s) to retrieve.
	 *
	 * @return mixed|null
	 */
	function get_hierarchichal_term_metadata( WP_Term $term, $meta_key ) {
		if ( ! is_taxonomy_hierarchical( $term->taxonomy ) ) {
			return;
		}
		if ( 0 === $term->parent ) {
			return;
		}
		return $this->get_terms_ancestory_tree( $term->term_id, $meta_key );
	}

	/**
	 * Get an array of term ancestors for the given term id, meaning
	 * the SQL query starts at the given term id and then walks up
	 * the parent tree as it stores the columns.
	 *
	 * The result is an array of stdClass objects that have the following:
	 *      term_id   => int
	 *      parent_id => int
	 *      metadata1 => value of that meta key's column
	 *      ..
	 *      metadataN => value of the meta key #N
	 *
	 * @param  integer  $term_id
	 * @param  array    $meta_keys  Array of meta key(s) to retrieve.
	 *
	 * @return array|bool
	 */
	function get_terms_ancestory_tree( $term_id, array $meta_keys ) {
		global $wpdb;
		// Build the SQL Query first.
		$sql_query = $this->build_terms_ancestory_tree_sql_query( $meta_keys );
		// Assemble the values, i.e. get them in the right order
		// to insert into the SQL query.
		$values = $meta_keys;
		array_unshift( $values, $term_id );
		// Prepare the values and then insert into the SQL query.
		// We are swapping out the %d/%f/%s placeholders with their value.
		$sql_query = $wpdb->prepare( $sql_query, $values );
		// Run the query to get records from the database.
		$records = $wpdb->get_results( $sql_query );
		// Check if we got records back from the database. If yes,
		// return the records.
		if ( $records && is_array( $records ) ) {
			return $records;
		}
		// Oh poo, we something when wrong.
		return false;
	}

	/**
	 * Build the SQL Query string.
	 *
	 * @param array $meta_keys Array of meta key(s) to retrieve.
	 *
	 * @return string
	 */
	function build_terms_ancestory_tree_sql_query( array $meta_keys  ) {
		global $wpdb;
		$number_of_meta_keys = count( $meta_keys );
		$sql_query = "SELECT t.term_id, @parent := t.parent AS parent_id";
		for( $suffix_number = 1; $suffix_number <= $number_of_meta_keys; $suffix_number++ ) {
			$sql_query .= sprintf( ', tm%1$d.meta_value AS metadata%1$d', $suffix_number );
		}
		$sql_query .= "\n" .
		"FROM (
			SELECT *
			FROM {$wpdb->term_taxonomy} AS tt
				ORDER BY
				CASE
					WHEN tt.term_id > tt.parent THEN tt.term_id
					ELSE tt.parent
				END DESC
		) AS t
		JOIN (
			SELECT @parent := %d
		) AS tmp";
		for( $suffix_number = 1; $suffix_number <= $number_of_meta_keys; $suffix_number++ ) {
			$sql_query .= "\n" . sprintf(
					'LEFT JOIN %1$s AS tm%2$d ON tm%2$d.term_id = @parent AND tm%2$d.meta_key = ',
					$wpdb->termmeta,
					$suffix_number
				);
			$sql_query .= '%s';
		}
		$sql_query .= "\n" . "WHERE t.term_id = @parent;";
		return $sql_query;
	}

	public function maybe_get_fallback( $key, $original_value ) {
		if ( ! $this->fallback ) {
			return $original_value;
		}

	}




	/**
	 * THIS IS ALL OLD AND FOR REFERENCE TO COMPARE AND MAKE SURE I GOT IT ALL.
	 */


	/**
	 * Get an archive setting value with fallback.
	 *
	 * @param   string  $key                        The field key to check.
	 * @param   bool    $check_for_archive_setting  Whether to check if custom archive settings are enabled.
	 * @param   mixed   $fallback                   The value to fall back to if we don't get a value via setting.
	 *
	 * @return  mixed
	 */
	function get_archive_setting_by_template( $key, $check_for_archive_setting, $fallback = false ) {

		$meta = null;

		// Blog.
		if ( is_home() ) {
			$meta = genesis_get_option( $key );
		}

		// Taxonomy archive.
		elseif ( is_category() || is_tag() || is_tax() ) {

			$queried_object = get_queried_object();

			/**
			 * Check if we have an object.
			 * We hit an issue when permlinks have /%category%/ in the base and a user
			 * 404's via top level URL like example.com/non-existent-slug.
			 * This returned true for is_category() and blew things up.
			 */
			if ( $queried_object ) {

				// If checking enabled and is enabled.
				if ( ! $check_for_archive_setting || ( $check_for_archive_setting && $enabled = get_term_meta( $queried_object->term_id, 'enable_content_archive_settings', true ) ) ) {
					$meta = get_term_meta( $queried_object->term_id, $key, true );
				}

				// If no meta
				if ( ! $meta ) {

					// Get hierarchical taxonomy term meta
					$meta = $this->get_term_meta_value_in_hierarchy( $queried_object, $key, $check_for_archive_setting );

					// If no meta
					if ( ! $meta ) {

						// If post or page taxonomy.
						if ( is_category() || is_tag() ) {
							$meta = genesis_get_option( $key );
						}

						// Custom taxonomy archive.
						else {
							$post_type = mai_get_archive_post_type();
							if ( $post_type ) {
								if ( ! $check_for_archive_setting || ( $check_for_archive_setting && $enabled = genesis_get_cpt_option( 'enable_content_archive_settings', $post_type ) ) ) {
									if ( 'post' === $post_type ) {
										$meta = genesis_get_option( $key );
									} else {
										$meta = genesis_get_cpt_option( $key, $post_type );
									}
								}
							}
						}
					}
				}
			}
		}

		/**
		 * CPT archive.
		 * This may be called too early to use get_post_type().
		 */
		elseif ( is_post_type_archive() && post_type_supports( mai_get_archive_post_type(), 'mai-cpt-settings' ) ) {
			if ( ! $check_for_archive_setting || ( $check_for_archive_setting && $enabled = genesis_get_cpt_option( 'enable_content_archive_settings' ) ) ) {
				$meta = genesis_get_cpt_option( $key );
			}
		}

		// Author archive.
		elseif ( is_author() ) {
			if ( ! $check_for_archive_setting || ( $check_for_archive_setting && $enabled = get_the_author_meta( 'enable_content_archive_settings', get_query_var( 'author' ) ) ) ) {
				$meta = get_the_author_meta( $key, get_query_var( 'author' ) );
			}
		}

		// If we have meta, return it
		if ( null !== $meta ) {
			return $meta;
		}

		// If we have a fallback, return it
		elseif ( $fallback ) {
			return $fallback;
		}

		// Return
		return null;
	}

}
