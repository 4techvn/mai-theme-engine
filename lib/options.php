<?php

/**
 * This filter makes sure our custom settings are not wiped out when updating via Genesis > Theme Settings.
 * In 1.1.2 we were made aware of a critical bug where our custom settings were cleared anytime
 * a user would hit "Save" in Genesis > Theme Settings.
 *
 * @since   1.1.3
 *
 * @return  array
 */
add_filter( 'pre_update_option_genesis-settings', 'mai_enforce_custom_genesis_settings', 10, 2 );
function mai_enforce_custom_genesis_settings( $new_value, $old_value ) {

	// Bail if this isn't happening from a form submission page.
	if ( ! isset( $_POST ) || empty( $_POST ) ) {
		return $new_value;
	}

	// Bail if this isn't happening on a page that's submitting a 'genesis-settings' form.
	if ( ! isset( $_POST[ 'genesis-settings' ] ) || empty( $_POST[ 'genesis-settings' ] ) ) {
		return $new_value;
	}

	// Get the submitted and existing settings values.
	$values   = $_POST[ 'genesis-settings' ];
	$settings = get_option( 'genesis-settings' );

	// Loop through em.
	foreach ( (array) $settings as $key => $value ) {
		/**
		 * If a custom setting is not part of the $_POST submission,
		 * we need to add to the $new_value array it so it's not lost.
		 */
		if ( ! isset( $values[ $key ] ) ) {
			$new_value[ $key ] = genesis_get_option( $key );
		}
	}

	return $new_value;
}

/**
 * Filter the default options, adding our custom settings.
 * CPT settings defaults are filtered in /customizer/custom-post-types.php
 *
 * @param   array   $options  The genesis options.
 * @param   string  $setting  The setting key/name.
 *
 * @return  array   The modified options.
 */
add_filter( 'genesis_options', 'mai_genesis_options_defaults', 10, 2 );
function mai_genesis_options_defaults( $options, $setting ) {

	if ( GENESIS_SETTINGS_FIELD !== $setting ) {
		return $options;
	}

	// Default options.
	$all_options = mai_get_default_options();
	foreach ( $all_options as $key => $value ) {
		if ( ! isset( $options[$key] ) ) {
			$options[$key] = $value;
		}
	}

	// Return the modified options.
	return $options;
}

/**
 * Get a default option by name.
 *
 * @param  string  $key  The option name.
 *
 * @return string  The option value.
 */
function mai_get_default_option( $key ) {
	$options = mai_get_default_options();
	return $options[$key];
}

/**
 * Get all of the default options.
 *
 * @return  array  The options.
 */
function mai_get_default_options() {

	$defaults = array(
		// Genesis (G defaults are used for everything but Mai CPT settings).
		'content_archive'           => 'full',
		'content_archive_limit'     => 120,
		'content_archive_thumbnail' => 1,
		'image_size'                => 'one-third',
		'image_alignment'           => '',
		'posts_nav'                 => 'numeric',
		'site_layout'               => 'content-sidebar',
		// Mai General.
		'enable_sticky_header'      => 0,
		'enable_shrink_header'      => 0,
		'singular_image_page'       => 1,
		'singular_image_post'       => 1,
		'footer_widget_count'       => 2,
		'mobile_menu_style'         => 'standard',
		// Mai Banner.
		'enable_banner_area'        => 1,
		'banner_background_color'   => '#f1f1f1',
		'banner_id'                 => '',
		'banner_overlay'            => '',
		'banner_inner'              => '',
		'banner_height'             => 'md',
		'banner_content_width'      => 'auto',
		'banner_align_text'         => 'center',
		// 'banner_featured_image'     => 0,
		// 'banner_disable_post_types' => array(),
		// 'banner_disable_taxonomies' => array(),
		// Mai Archives.
		'columns'                   => 1,
		'image_location'            => 'before_title',
		'more_link'                 => 0,
		'more_link_text'            => '',
		// 'remove_meta'               => array(),
		'posts_per_page'            => get_option( 'posts_per_page' ),
		// Mai Singular.
		// 'singular_image_page'       => 1,
		// 'singular_image_post'       => 1,
		// 'remove_meta_post'          => array(),
		// Mai Layouts.
		// 'layout_page'               => '',
		// 'layout_post'               => '',
		'layout_archive'            => 'full-width-content',
		// Mai Utility.
		'mai_db_version'            => MAI_PRO_ENGINE_DB_VERSION,
	);

	/**
	 * Get post types.
	 *
	 * @return  array  Post types  array( 'name' => object )
	 */
	$post_types = mai_get_post_type_settings_post_types();

	if ( $post_types ) {
		// Loop through em.
		foreach ( $post_types as $post_type => $object ) {
			$defaults[ sprintf( 'banner_featured_image_%s', $post_type ) ]     = 0;
			$defaults[ sprintf( 'banner_disable_%s', $post_type ) ]            = 0;
			$defaults[ sprintf( 'banner_disable_taxonomies_%s', $post_type ) ] = array();
			$defaults[ sprintf( 'singular_image_%s', $post_type ) ]            = 1;
			$defaults[ sprintf( 'remove_meta_%s', $post_type ) ]               = array();
			$defaults[ sprintf( 'layout_%s', $post_type ) ]                    = '';
		}
	}
	return apply_filters( 'genesis_theme_settings_defaults', $defaults );
}

/**
 * Get a default CPT option by name.
 *
 * @param  string  $key  The option name.
 *
 * @return string  The option value.
 */
function mai_get_default_cpt_option( $key, $post_type = 'post' ) {
	$options = mai_get_default_cpt_options( $post_type );
	return $options[$key];
}

/**
 * Get all of the default CPT options.
 *
 * @return  array  The options.
 */
function mai_get_default_cpt_options( $post_type ) {
	// Defaults.
	$defaults = array(
		'banner_id'                       => '',
		'hide_banner'                     => 0,
		'layout'                          => mai_get_default_option( 'layout_archive' ),
		'enable_content_archive_settings' => 0,
		'columns'                         => mai_get_default_option( 'columns' ),
		'content_archive'                 => mai_get_default_option( 'content_archive' ),
		'content_archive_limit'           => mai_get_default_option( 'content_archive_limit' ),
		'content_archive_thumbnail'       => mai_get_default_option( 'content_archive_thumbnail' ),
		'image_location'                  => mai_get_default_option( 'image_location' ),
		'image_size'                      => mai_get_default_option( 'image_size' ),
		'image_alignment'                 => mai_get_default_option( 'image_alignment' ),
		'more_link'                       => mai_get_default_option( 'more_link' ),
		'more_link_text'                  => mai_get_default_option( 'more_link_text' ),
		// 'remove_meta'                     => mai_get_default_option( 'remove_meta' ),
		'posts_per_page'                  => mai_get_default_option( 'posts_per_page' ),
		'posts_nav'                       => mai_get_default_option( 'posts_nav' ),
	);
	return apply_filters( 'genesis_cpt_archive_settings_defaults', $defaults, $post_type );
}
