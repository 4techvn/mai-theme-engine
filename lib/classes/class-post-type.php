<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main plugin class.
 *
 * @package Mai_Post_Type
 */
class Mai_Post_Type {

	public $object;
	public $supports;
	public $settings;

	public function __construct( $post_type ) {

		$this->post_type = $post_type;
		$this->object    = get_post_type_object( $this->post_type );
 		$this->supports  = get_all_post_type_supports( $this->post_type );

		// Settings defaults array.
		$this->settings  = array(
			'banner_id'                           => true,
			'hide_banner'                         => true, // Archive (Main)
			'banner_disable_post_type'            => true, // Singular
			'banner_disable_taxonomies_post_type' => true, // Archives (Taxo)
			'banner_featured_image_post_type'     => true, // Singular
			'layout'                              => true, // Archives
			'layout_post_type'                    => true, // Singular
			'singular_image_post_type'            => true, // Singular
			'remove_meta_post_type'               => true, // Singular
			'enable_content_archive_settings'     => true,
			'columns'                             => true,
			'content_archive'                     => true,
			'content_archive_limit'               => true,
			'content_archive_thumbnail'           => true,
			'image_location'                      => true,
			'image_size'                          => true,
			'image_alignment'                     => true,
			'more_link'                           => true,
			// 'more_link_text'                   => true,
			// 'remove_loop'                         => true, // this is metabox only, not customizer i think - still need it?
			// 'remove_meta'                         => true,
			'posts_per_page'                      => true,
			'posts_nav'                           => true,
		);

		if ( $this->built_in() ) {
			$this->settings['banner_id']                 = false;
		}

		// If no archives.
		if ( ! $this->has_archives() ) {
			$this->settings['hide_banner']               = false;
			$this->settings['layout']                    = false;
		}

		// If no entry meta support.
		if ( ! ( $this->supports( 'genesis-entry-meta-after-content' ) || $this->supports( 'genesis-entry-meta-after-content' ) ) ) {
			// $this->settings['remove_meta']               = false;
			$this->settings['remove_meta_post_type']     = false;
		}

		// If no editor or no excerpt support.
		if ( ! ( $this->supports( 'editor' ) || $this->supports( 'excerpt' ) ) ) {

			$this->settings['content_archive']           = false;
			$this->settings['content_archive_limit']     = false;
		}

		// If no featured image support.
		if ( ! $this->supports( 'thumbnail' ) ) {
			$this->settings['singular_image_post_type']  = false;
			$this->settings['content_archive_thumbnail'] = false;
			$this->settings['image_location']            = false;
			$this->settings['image_size']                = false;
			$this->settings['image_alignment']           = false;
		}

		/**
		 * Filter to enabled/disable settings for each post type.
		 * This is great for adding CPT support for specific plugins like Woo/EDD/etc.
		 * Enabling disabled settings does not mean they will work as expected.
		 * Many plugins that register CPT's do have their own templates that break these default settings/filters in Genesis/Mai Pro.
		 */
		$this->settings = apply_filters( 'mai_cpt_settings', $this->settings, $this->post_type );

	}

	public function built_in() {
		return $this->object->_builtin;
	}

	public function has_archives() {
		// If the post type itself has an archive.
		if ( (bool) $this->object->has_archive ) {
			return true;
		}
		// Get page taxonomies.
		$taxos = get_object_taxonomies( $this->post_type, 'objects' );
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

	public function has_setting( $setting ) {
		if ( isset( $this->settings[ $setting ] ) && $this->settings[ $setting ] ) {
			return true;
		}
		return false;
	}

}
