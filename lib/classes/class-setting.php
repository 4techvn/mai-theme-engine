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

	protected $placeholder;
	protected $key;
	protected $value;
	protected $placeholders;
	protected $singular_meta;
	protected $direct;
	protected $check;
	protected $fallback;
	protected $post_type;
	protected $cpts;

	/**
	 * Setup the object.
	 *
	 * @param  string  $name  The setting name. May be a placeholder if the key uses the post_type name in it.
	 */
	function __construct( $key ) {
		$this->placeholder   = $key;
		$this->key           = $this->key();
		$this->value         = $this->value();
		$this->placeholders  = $this->placeholders();  // array
		$this->singular_meta = $this->singular_meta(); // bool
		$this->direct        = $this->direct();        // bool
		$this->check         = $this->check();         // bool
		$this->fallback      = $this->fallback();      // bool
		$this->post_type     = $this->post_type();
		$this->cpts          = genesis_get_cpt_archive_types_names();
	}

	/**
	 * Get the actual key name from the placeholder.
	 *
	 * @return  string  The actual key name.
	 */
	public function key() {
		return $this->keys[ $this->placeholder ];
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
	 * Get the post type to use to swap placeholders and build key names.
	 *
	 * @return  string  The post type name, or empty string.
	 */
	public function post_type() {
		$post_type = '';
		if ( is_singular() ) {
			$post_type = get_post_type();
			if ( ! $post_type ) {
				$post_type = get_query_var( 'post_type' );
			}
		}
		elseif ( mai_is_content_archive() ) {
			$post_type = mai_get_archive_post_type();
		}
		return $post_type;
	}

	/**
	 * Generic placeholder names for all keys.
	 * Actual key names may be post_type specific.
	 *
	 * Keys that are used for all post types, including post/page, have post_type specific keys.
	 *
	 * @return  array  Placeholder key names.
	 */
	public function placeholders() {
		return array(
			'enable_sticky_header',
			'enable_shrink_header',
			'footer_widget_count',
			'mobile_menu_style',
			'enable_banner_area',
			'banner_background_color',
			'banner_id',
			'banner_id_post_type',
			'banner_overlay',
			'banner_inner',
			'banner_height',
			'banner_content_width',
			'banner_align_text',
			'hide_banner',
			'banner_disable_post_type',
			'banner_disable_taxonomies_post_type',
			'banner_featured_image_post_type',
			'layout_archive_post_type',
			'layout_post_type',
			'layout_archive',
			'site_layout',
			'singular_image_post_type',
			'mai_hide_featured_image',
			'remove_meta_post_type',
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
			'posts_per_page',
			'posts_nav',
		);
	}

	/**
	 * This should check each key individually.
	 * Get the first value, then the hierarchy or post-type.
	 * Maybe get fallback here, or in another method.
	 *
	 * @return [type] [description]
	 */
	public function get_value() {
		switch ( $this->placeholder ) {
			case 'enable_sticky_header':
				$value = genesis_get_option( $this->key );
				break;
			case 'enable_shrink_header':
				$value = genesis_get_option( $this->key );
				break;
			case 'footer_widget_count':
				$value = genesis_get_option( $this->key );
				break;
			case 'mobile_menu_style':
				$value = genesis_get_option( $this->key );
				break;
			case 'enable_banner_area':
				$value = genesis_get_option( $this->key );
				break;
			case 'banner_background_color':
				$value = genesis_get_option( $this->key );
				break;
			case 'banner_id':
				$value = '';
				break;
			case 'banner_id_post_type':
				$value = '';
				break;
			case 'banner_overlay':
				$value = '';
				break;
			case 'banner_inner':
				$value = '';
				break;
			case 'banner_height':
				$value = '';
				break;
			case 'banner_content_width':
				$value = '';
				break;
			case 'banner_align_text':
				$value = '';
				break;
			case 'hide_banner':
				$value = '';
				break;
			case 'banner_disable_post_type':
				$value = '';
				break;
			case 'banner_disable_taxonomies_post_type':
				$value = '';
				break;
			case 'banner_featured_image_post_type':
				$value = '';
				break;
			case 'layout_archive_post_type':
				$value = '';
				break;
			case 'layout_post_type':
				$value = '';
				break;
			case 'layout_archive':
				$value = '';
				break;
			case 'site_layout':
				$value = '';
				break;
			case 'singular_image_post_type':
				$value = '';
				break;
			case 'mai_hide_featured_image':
				$value = '';
				break;
			case 'remove_meta_post_type':
				$value = '';
				break;
			case 'enable_content_archive_settings':
				$value = '';
				break;
			case 'columns':
				$value = '';
				break;
			case 'content_archive':
				$value = '';
				break;
			case 'content_archive_limit':
				$value = '';
				break;
			case 'content_archive_thumbnail':
				$value = '';
				break;
			case 'image_location':
				$value = '';
				break;
			case 'image_size':
				$value = '';
				break;
			case 'image_alignment':
				$value = '';
				break;
			case 'more_link':
				$value = '';
				break;
			case 'remove_loop':
				$value = '';
				break;
			case 'posts_per_page':
				$value = '';
				break;
			case 'posts_nav':
				$value = '';
				break;
			default:
				$value = null;
			break;
		}
	}

	public function singular_meta() {
		$keys = array(
			'hide_banner',
			'mai_hide_featured_image',
		);
		return in_array( $this->placeholder, $keys ) ? true : false;
	}

	public function singular_option() {
		$keys = array(
			'banner_id_post_type',
			'banner_disable_post_type',
		);
		return in_array( $this->placeholder, $keys ) ? true : false;
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
		return in_array( $this->placeholder, $keys ) ? true : false;
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
		return in_array( $this->placeholder, $keys ) ? true : false;
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
		return ! in_array( $this->placeholder, $keys ) ? true : false;
	}

	public function value() {

		$value = null;

		// Single post/page/cpt.
		if ( is_singular() ) {
			if ( $this->singular_meta ) {
				$value = get_post_meta( get_the_ID(), $this->key, true );
			}
		}

		// Blog.
		elseif ( is_home() ) {

			// If singular meta and static blog page.
			if ( $this->singular_meta && ( $posts_page_id = get_option( 'page_for_posts' ) ) ) {
				$value = get_post_meta( $posts_page_id, $this->key, true );
			} else {
				$value = genesis_get_option( $this->key );
			}

			// If direct.
			if ( $this->direct ) {
				return $value;
			}

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

				// Save as variable for direct call.
				$term_meta = get_term_meta( $queried_object->term_id, $this->key, true );

				if ( $this->direct ) {
					return $term_meta;
				}

				// If not checking, or checking and is enabled, use as value.
				if ( ! $this->check || ( $this->check && (bool) get_term_meta( $queried_object->term_id, 'enable_content_archive_settings', true ) ) ) {
					$value = $term_meta;
				}

				// If no value.
				if ( ! $value ) {

					// Get hierarchical taxonomy term meta.
					$value = $this->get_term_meta_value_in_hierarchy( $queried_object, $this->key, $this->check );

					// If no value and post type has settings, and not checking for content archive settings enabled or checking and they are enabled.
					if ( ! $value && in_array( $this->post_type, genesis_get_cpt_archive_types_names() ) && ( ! $this->check || ( $this->check && (bool) genesis_get_cpt_option( 'enable_content_archive_settings', $this->post_type ) ) ) ) {
						$value = genesis_get_cpt_option( $this->key, $this->post_type );
					}
				}
			}
		}

		// CPT archive. Need to check for 'mai-cpt-settings' otherwise it won't have any settings to check.
		elseif ( is_post_type_archive() && post_type_supports( $this->post_type, 'mai-cpt-settings' ) ) {

			// Save as variable for direct call.
			$post_type_option = genesis_get_cpt_option( $this->key );

			if ( $this->direct ) {
				return $post_type_option;
			}

			// If not checking, or checking and is enabled, use as value.
			if ( ! $this->check || ( $this->check && (bool) genesis_get_cpt_option( 'enable_content_archive_settings', $this->post_type ) ) ) {
				$value = $post_type_option;
			}
		}

		// Author archive.
		elseif ( is_author() ) {

			$author_meta = get_the_author_meta( $this->key, get_query_var( 'author' ) );

			if ( $this->direct ) {
				return $author_meta;
			}

			if ( ! $this->check || ( $this->check && (bool) get_the_author_meta( 'enable_content_archive_settings', get_query_var( 'author' ) ) ) ) {
				$value = $author_meta;
			}
		}

		// Maybe get fallback (Does this work for search results?).
		$value = ( ( null !== $value ) && $this->fallback ) ? genesis_get_option( $this->key ) : $value;

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

}
