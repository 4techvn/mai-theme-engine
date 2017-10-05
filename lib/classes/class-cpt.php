<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Setup CPT's customizer and Archive Settings fields.
 *
 * Possible keys/settings:
 *
 * 'banner_id'
 * 'hide_banner'
 * 'banner_disable_{post_type}           (saves to 'genesis-settings' option)
 * 'layout_{post_type}'                  (saves to 'genesis-settings' option)
 * 'layout'
 * 'singular_image_{post_type}'          (saves to 'genesis-settings' option)
 * 'remove_meta_{post_type}'             (saves to 'genesis-settings' option)
 * 'enable_content_archive_settings'
 * 'columns'
 * 'content_archive'
 * 'content_archive_limit'
 * 'content_archive_thumbnail'
 * 'image_location'
 * 'image_size'
 * 'image_alignment'
 * 'more_link'
 * 'more_link_text'
 * 'remove_meta'
 * 'posts_per_page'
 * 'posts_nav'
 *
 * @return  void
 */


/**
 * Main plugin class.
 *
 * @package Mai_CPT
 */
class Mai_CPT {

	protected $object;
	protected $supports;
	protected $settings;

	public function __construct( $post_type ) {

		$this->object   = get_post_type_object( $post_type );
 		$this->supports = get_all_post_type_supports( $post_type );

		// Settings defaults array.
		$this->settings = array(
			'banner_id'                           => true,
			'hide_banner'                         => true,
			'banner_disable_post_type'            => true,
			'banner_disable_taxonomies_post_type' => true,
			'banner_featured_image_post_type'     => true,
			'layout'                              => true,
			'layout_post_type'                    => true,
			'singular_image_post_type'            => true,
			'remove_meta_post_type'               => true,
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
			'remove_loop'                         => true,
			'remove_meta'                         => true,
			'posts_per_page'                      => true,
			'posts_nav'                           => true,
		);

		// If no entry meta support.
		if ( ! ( isset( $this->supports['genesis-entry-meta-before-content'] ) || isset( $this->supports['genesis-entry-meta-after-content'] ) ) ) {
			$this->settings['remove_meta']               = false;
			$this->settings['remove_meta_post_type']     = false;
		}

		// If no editor or no excerpt support.
		if ( ! ( isset( $this->supports['editor'] ) || isset( $this->supports['excerpt'] ) ) ) {
			$this->settings['content_archive']           = false;
			$this->settings['content_archive_limit']     = false;
		}

		// If no featured image support.
		if ( ! isset( $this->supports['thumbnail'] ) ) {
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
		$this->settings = apply_filters( 'mai_cpt_settings', $this->settings, $post_type );

	}

	public function supports( $key ) {
		if ( in_array( $this->supports ) ) {
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
