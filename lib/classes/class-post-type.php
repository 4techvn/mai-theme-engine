<?php

/**
 * The class.
 *
 * @package Mai_Post_Type
 */
abstract class Mai_Post_Type {

	public $name;
	public $post_type;
	public $supports;
	public $placeholders;
	public $keys;
	public $values;
	public $settings;

	function __construct( $post_type ) {
		$this->name         = $post_type;
		$this->post_type    = get_post_type_object( $this->name );
		$this->supports     = get_all_post_type_supports( $this->name );
		$this->placeholders = $this->placeholders();
		$this->keys         = $this->keys();
		$this->values       = $this->values();
		$this->settings     = $this->settings();
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

	// public function options() {
	// 	return array(
	// 		'banner_disable_post_type',
	// 		'banner_featured_image_post_type',
	// 		'layout_post_type',
	// 		'singular_image_post_type',
	// 		'remove_meta_post_type',
	// 	);
	// }

	// public function cpt_options() {
	// 	return array(
	// 		'banner_id',
	// 		'hide_banner',                          // Archive (Main)
	// 		'layout_archive_post_type',             // Archives
	// 		'enable_content_archive_settings',
	// 		'columns',
	// 		'content_archive',
	// 		'content_archive_limit',
	// 		'content_archive_thumbnail',
	// 		'image_location',
	// 		'image_size',
	// 		'image_alignment',
	// 		'more_link',
	// 		'remove_loop',
	// 		'remove_meta',
	// 		'posts_per_page',
	// 		'posts_nav',
	// 	);
	// }

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
			$keys[ $placeholder ] = str_replace( 'post_type', $this->name, $placeholder );
		}
		return $keys;
	}

	public function value( $placeholder ) {
		return $this->values[ $placeholder ];
	}

	public function values() {
		// foreach ( $this->post_type->placeholders as $placeholder ) {
		// 	$this->values[ $placeholder ] = null;
		// }
		// Set.
		$this->values['remove_loop']               = mai_get_the_archive_setting(     $this->key( 'remove_loop' ) );
		$this->values['columns']                   = mai_get_columns();
		$this->values['content_archive']           = mai_get_archive_setting(         $this->key( 'content_archive' ),           true, genesis_get_option( $this->key( 'content_archive' ) ) );
		$this->values['content_archive_limit']     = absint( mai_get_archive_setting( $this->key( 'content_archive_limit' ),     true, genesis_get_option( $this->key( 'content_archive_limit' ) ) ) );
		$this->values['content_archive_thumbnail'] = mai_get_archive_setting(         $this->key( 'content_archive_thumbnail' ), true, genesis_get_option( $this->key( 'content_archive_thumbnail' ) ) );
		$this->values['image_location']            = mai_get_archive_setting(         $this->key( 'image_location' ),            true, genesis_get_option( $this->key( 'image_location' ) ) );
		$this->values['image_size']                = mai_get_archive_setting(         $this->key( 'image_size' ),                true, genesis_get_option( $this->key( 'image_size' ) ) );
		$this->values['image_alignment']           = mai_get_archive_setting(         $this->key( 'image_alignment' ),           true, genesis_get_option( $this->key( 'image_alignment' ) ) );
		$this->values['posts_per_page']            = mai_get_archive_setting(         $this->key( 'posts_per_page' ),            true );
		$this->values['posts_nav']                 = mai_get_archive_setting(         $this->key( 'posts_nav' ),                 true, genesis_get_option( $this->key( 'posts_nav' ) ) );
	}

	public function has_setting( $placeholder ) {
		if ( isset( $this->settings[ $placeholder ] ) && $this->settings[ $placeholder ] ) {
			return true;
		}
		return false;
	}

	public function settings() {

		$settings = array();

		// Start all settings as enabled/true.
		foreach ( $this->placeholders as $placeholder ) {
			$settings[ $placeholder ] = true;
		}

		// If built in post type.
		if ( $this->built_in() ) {
			$settings['banner_id'] = false;
		}

		// If no archives.
		if ( ! $this->has_archives() ) {
			$settings['hide_banner']                         = false;
			$settings['banner_disable_taxonomies_post_type'] = false;
			$settings['layout_archive_post_type']            = false;
			$settings['enable_content_archive_settings']     = false;
		}

		// If no entry meta support.
		if ( ! ( $this->supports( 'genesis-entry-meta-after-content' ) || $this->supports( 'genesis-entry-meta-after-content' ) ) ) {
			$settings['remove_meta_post_type'] = false;
		}

		// If no editor or no excerpt support.
		if ( ! ( $this->supports( 'editor' ) || $this->supports( 'excerpt' ) ) ) {
			$settings['content_archive']       = false;
			$settings['content_archive_limit'] = false;
		}

		// If no featured image support.
		if ( ! $this->supports( 'thumbnail' ) ) {
			$settings['singular_image_post_type']  = false;
			$settings['content_archive_thumbnail'] = false;
			$settings['image_location']            = false;
			$settings['image_size']                = false;
			$settings['image_alignment']           = false;
		}

		/**
		 * Filter to enabled/disable settings for each post type.
		 * This is great for adding CPT support for specific plugins like Woo/EDD/etc.
		 * Enabling disabled settings does not mean they will work as expected.
		 * Many plugins that register CPT's do have their own templates that break these default settings/filters in Genesis/Mai Pro.
		 */
		return apply_filters( 'mai_cpt_settings', $this->settings, $this->name );
	}

	public function built_in() {
		return $this->post_type->_builtin;
	}

	public function has_archives() {
		// If the post type itself has an archive.
		if ( (bool) $this->post_type->has_archive ) {
			return true;
		}
		// Get page taxonomies.
		$taxos = get_object_taxonomies( $this->name, 'objects' );
		// If taxonomies.
		if ( $taxos ) {
			// Check for public.
			foreach ( $taxos as $taxo ) {
				if ( $taxo->public ) {
					return true;
				}
			}
		}
		// Nope.
		return false;
	}

	public function supports( $key ) {
		if ( in_array( $key, $this->supports ) ) {
			return true;
		}
		return false;
	}

}
