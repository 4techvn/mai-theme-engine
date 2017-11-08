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
	public $keys;
	public $settings;

	function __construct( $post_type ) {
		$this->name      = $post_type;
		$this->post_type = get_post_type_object( $this->name );
		$this->supports  = get_all_post_type_supports( $this->name );
		$this->keys      = $this->keys();
		$this->settings  = $this->settings();
	}

	/**
	 * Generic placeholder names for all keys.
	 * Actual key names may be post_type specific.
	 *
	 * @return  array  Placeholder key names.
	 */
	public function keys() {
		return array(
			'banner_background_color',
			'banner_id',
			'hide_banner',                // Archive (Main)
			'banner_disable',             // Singular
			'banner_disable_taxonomies',  // Archives (Taxo)
			'banner_featured_image',      // Singular
			'layout_archive',             // Archives
			'layout_single',              // Singular
			'featured_image_location',    // Singular
			'remove_meta_single',         // Singular
			'enable_content_archive_settings',
			'columns',
			'content_archive',
			'content_archive_limit',
			'content_archive_thumbnail',
			'image_location',
			'image_size',
			'image_alignment',
			'more_link',
			'remove_meta',
			'posts_per_page',
			'posts_nav',
		);
	}

	public function get_setting_id( $key ) {
		return sprintf( 'genesis-settings[%s][%s]', $this->name, $key );
	}

	public function has_setting( $setting ) {
		if ( isset( $this->settings[ $setting ] ) && $this->settings[ $setting ] ) {
			return true;
		}
		return false;
	}

	public function settings() {

		$settings = array();

		// Start all settings as enabled/true.
		foreach ( $this->keys as $key ) {
			$settings[ $key ] = true;
		}

		// If no archives.
		if ( ! $this->has_archives() ) {
			$settings['hide_banner']                     = false;
			$settings['banner_disable_taxonomies']       = false;
			$settings['layout_archive']                  = false;
			$settings['enable_content_archive_settings'] = false;
			$settings['columns']                         = false;
			$settings['content_archive']                 = false;
			$settings['content_archive_limit']           = false;
			$settings['content_archive_thumbnail']       = false;
			$settings['image_location']                  = false;
			$settings['image_size']                      = false;
			$settings['image_alignment']                 = false;
			$settings['more_link']                       = false;
			$settings['remove_meta']                     = false;
			$settings['posts_per_page']                  = false;
			$settings['posts_nav']                       = false;
		}

		// If no entry meta support.
		if ( ! ( $this->supports( 'genesis-entry-meta-after-content' ) || $this->supports( 'genesis-entry-meta-after-content' ) ) ) {
			$settings['remove_meta_single']              = false;
		}

		// If no editor or no excerpt support.
		if ( ! ( $this->supports( 'editor' ) || $this->supports( 'excerpt' ) ) ) {
			$settings['content_archive']                 = false;
			$settings['content_archive_limit']           = false;
		}

		// If no featured image support.
		if ( ! $this->supports( 'thumbnail' ) ) {
			$settings['featured_image_location']         = false;
			$settings['content_archive_thumbnail']       = false;
			$settings['image_location']                  = false;
			$settings['image_size']                      = false;
			$settings['image_alignment']                 = false;
		}

		/**
		 * Filter to enabled/disable settings for each post type.
		 * This is great for adding CPT support for specific plugins like Woo/EDD/etc.
		 * Enabling disabled settings does not mean they will work as expected.
		 * Many plugins that register CPT's do have their own templates that break these default settings/filters in Genesis/Mai Pro.
		 */
		return apply_filters( 'mai_post_type_settings', $settings, $this->name );
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
