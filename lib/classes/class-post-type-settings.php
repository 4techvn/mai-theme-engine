<?php

/**
 * Setup CPT's customizer and Archive Settings fields.
 *
 * Possible keys/settings:
 *
 * 'banner_id'
 * 'hide_banner'
 * 'banner_disable
 * 'banner_disable_taxonomies
 * 'banner_featured_image
 * 'layout_archive'
 * 'layout_single'
 * 'singular_image_location' (singular_image_size later?)
 * 'remove_meta'
 * 'enable_content_archive_settings'
 * 'columns'
 * 'content_archive'
 * 'content_archive_limit'
 * 'content_archive_thumbnail'
 * 'image_location'
 * 'image_size'
 * 'image_alignment'
 * 'more_link'
 * 'remove_meta'
 * 'posts_per_page'
 * 'posts_nav'
 *
 * @return  void
 */
add_action( 'init', 'mai_post_type_settings_init', 999 );
function mai_post_type_settings_init() {

	// Get post types.
	$post_types = mai_get_post_type_settings_post_types();

	// Bail if no post types.
	if ( ! $post_types ) {
		return;
	}

	// Loop through the post types.
	foreach ( $post_types as $post_type ) {
		$settings = new Mai_Post_Type_Settings( $post_type );
	}
}

/**
 * The class.
 *
 * @package Mai_Post_Type_Settings
 */
class Mai_Post_Type_Settings extends Mai_Post_Type {

	protected $genesis_settings;
	protected $section_id;
	protected $prefix;

	function __construct( $post_type ) {

		// Construct the parent.
		parent::__construct( $post_type );

		// Construct child params.
		$this->genesis_settings = 'genesis-settings';
		$this->section_id       = sprintf( 'mai_%s_settings', $this->name );
		$this->prefix           = sprintf( '%s_', $this->name );

		/**
		 * Add Mai CPT support here.
		 * This should happen here, internally only. Please don't add 'mai-settings' support to CPT's manually.
		 */
		add_post_type_support( $this->name, 'mai-settings' );

		// Actions.
		add_action( 'customize_register', array( $this, 'customizer_settings' ), 22 );
	}

	/**
	 * Register the customizer settings sections and fields.
	 *
	 * @param   object  $wp_customize  The customizeer object.
	 *
	 * @return  void.
	 */
	function customizer_settings( $wp_customize ) {

		// Mai {post type name} Settings.
		$wp_customize->add_section(
			$this->section_id,
			array(
				'title'    => sprintf( __( 'Mai %s Settings', 'mai-pro-engine' ), $this->post_type->label ),
				'priority' => '39',
			)
		);

		if ( $this->has_setting( 'banner_id' ) || $this->has_setting( 'hide_banner' ) || $this->has_setting( 'banner_disable' ) || $this->has_setting( 'banner_disable_taxonomies' ) ) {

			// Banner break.
			$wp_customize->add_setting(
				$this->get_setting_id( 'banner_break' ),
				array(
					'default' => '',
					'type'    => 'option',
				)
			);
			$wp_customize->add_control(
				new Mai_Customize_Control_Break( $wp_customize,
					$this->prefix . 'banner_break',
					array(
						'label'           => __( 'Banner Area', 'mai-pro-engine' ),
						'section'         => $this->section_id,
						'settings'        => false,
						'active_callback' => function() use ( $wp_customize ) {
							return $this->is_banner_area_enabled_globally( $wp_customize );
						},
					)
				)
			);

		}

		if ( $this->has_setting( 'banner_id' ) ) {

			// Banner Image
			$wp_customize->add_setting(
				$this->get_setting_id( 'banner_id' ),
				array(
					'default'           => absint( mai_get_default_cpt_option( 'banner_id', $this->name ) ),
					'type'              => 'option',
					'sanitize_callback' => 'absint',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Media_Control( $wp_customize,
				$this->prefix . 'banner_id',
				array(
					'label'           => __( 'Default Banner Image', 'mai-pro-engine' ),
					'description'     => __( 'This will be the default banner image for archives and single entries.', 'mai-pro-engine' ),
					'section'         => $this->section_id,
					'settings'        => $this->get_setting_id( 'banner_id' ),
					'active_callback' => function() use ( $wp_customize ) {
						return $this->is_banner_area_enabled_globally( $wp_customize );
					},
				)
			) );

		}

		if ( $this->has_setting( 'hide_banner' ) || $this->has_setting( 'banner_disable' ) || $this->has_setting( 'banner_disable_taxonomies' ) ) {

			// Disable banner, heading only.
			$wp_customize->add_setting(
				$this->get_setting_id( 'hide_banner_heading' ),
				array(
					'default' => '',
					'type'    => 'option',
				)
			);
			$wp_customize->add_control(
				new Mai_Customize_Control_Content( $wp_customize,
					$this->prefix . 'hide_banner_heading',
					array(
						'label'           => __( 'Hide banner', 'mai-pro-engine' ),
						'section'         => $this->section_id,
						'settings'        => false,
						'active_callback' => function() use ( $wp_customize ) {
							return $this->is_banner_area_enabled_globally( $wp_customize );
						},
					)
				)
			);

		}

		if ( $this->has_setting( 'hide_banner' ) ) {

			// Hide banner CPT archive.
			$wp_customize->add_setting(
				$this->get_setting_id( 'hide_banner' ),
				array(
					'default'           => mai_sanitize_one_zero( mai_get_default_cpt_option( 'hide_banner', $this->name ) ),
					'type'              => 'option',
					'sanitize_callback' => 'mai_sanitize_one_zero',
				)
			);
			$wp_customize->add_control(
				$this->prefix . 'hide_banner',
				array(
					'label'           => __( 'Hide banner on main archive', 'mai-pro-engine' ),
					'section'         => $this->section_id,
					'settings'        => $this->get_setting_id( 'hide_banner' ),
					'type'            => 'checkbox',
					'active_callback' => function() use ( $wp_customize ) {
						return $this->is_banner_area_enabled_globally( $wp_customize );
					},
				)
			);

		}

		if ( $this->has_setting( 'banner_disable' ) ) {

			// Disable banner singular (saves to genesis-settings option).
			$wp_customize->add_setting(
				$this->get_setting_id( 'banner_disable' ),
				array(
					'default'           => mai_sanitize_one_zero( mai_get_default_option( 'banner_disable' ) ),
					'type'              => 'option',
					'sanitize_callback' => 'mai_sanitize_one_zero',
				)
			);
			$wp_customize->add_control(
				$this->prefix . 'banner_disable',
				array(
					'label'           => __( 'Hide banner on single entries', 'mai-pro-engine' ),
					'section'         => $this->section_id,
					'settings'        => $this->get_setting_id( 'banner_disable' ),
					'type'            => 'checkbox',
					'active_callback' => function() use ( $wp_customize ) {
						return $this->is_banner_area_enabled_globally( $wp_customize );
					},
				)
			);

		}

		if ( $this->has_setting( 'banner_disable_taxonomies' ) ) {

			// Disable banner taxonomies (saves to genesis-settings option).
			$disable_taxonomies = array();
			$taxonomies         = get_object_taxonomies( $this->name, 'objects' );
			if ( $taxonomies ) {
				foreach ( $taxonomies as $taxo ) {
					if ( $this->name == mai_get_taxonomy_post_type( $taxo ) ) {
						$disable_taxonomies[ $taxo->name ] = $taxo->label;
					}
				}

				// TODO: Default option needs to be fixed. The core mai_get_default_option() function needs to get post type options or something.
				$wp_customize->add_setting(
					$this->get_setting_id( 'banner_disable_taxonomies' ),
					array(
						'default'           => $this->customizer_multicheck_sanitize_key( mai_get_default_option( 'banner_disable_taxonomies' ) ),
						'type'              => 'option',
						'sanitize_callback' => array( $this, 'customizer_multicheck_sanitize_key' ),
					)
				);
				$wp_customize->add_control(
					new Mai_Customize_Control_Multicheck( $wp_customize,
						$this->prefix . 'banner_disable_taxonomies',
						array(
							'label'           => __( 'Hide banner on (taxonomies)', 'mai-pro-engine' ),
							'section'         => $this->section_id,
							'settings'        => $this->get_setting_id( 'banner_disable_taxonomies' ),
							'choices'         => $disable_taxonomies,
							'active_callback' => function() use ( $wp_customize ) {
								return $this->is_banner_area_enabled_globally( $wp_customize );
							},
						)
					)
				);
			}

		}

		// if ( $this->has_setting( 'banner_featured_image_post_type' ) ) {

		// 	// Banner featured image, heading only.
		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'banner_featured_image_heading' ),
		// 		array(
		// 			'default' => '',
		// 			'type'    => 'option',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		new Mai_Customize_Control_Content( $wp_customize,
		// 			$this->prefix . 'banner_featured_image_heading',
		// 			array(
		// 				'label'           => __( 'Featured Image on (single entries)', 'mai-pro-engine' ),
		// 				'section'         => $this->section_id,
		// 				'settings'        => false,
		// 				'active_callback' => function() use ( $wp_customize ) {
		// 					return ( (bool) ! $wp_customize->get_setting( $this->get_setting_id( $this->genesis_settings, $this->banner_disable_key ) )->value() && $this->is_banner_area_enabled_globally( $wp_customize, $this->genesis_settings ) );
		// 				},
		// 			)
		// 		)
		// 	);

		// 	// Banner featured image (saves to genesis-settings option).
		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( $this->genesis_settings, $this->banner_featured_image_key ),
		// 		array(
		// 			'default'           => mai_sanitize_one_zero( mai_get_default_option( $this->banner_featured_image_key ) ),
		// 			'type'              => 'option',
		// 			'sanitize_callback' => 'mai_sanitize_one_zero',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		$this->prefix . $this->banner_featured_image_key,
		// 		array(
		// 			'label'           => __( 'Use featured image as banner image', 'mai-pro-engine' ),
		// 			'section'         => $this->section_id,
		// 			'settings'        => $this->get_setting_id( $this->genesis_settings, $this->banner_featured_image_key ),
		// 			'priority'        => 10,
		// 			'type'            => 'checkbox',
		// 			'active_callback' => function() use ( $wp_customize ) {
		// 				return ( (bool) ! $wp_customize->get_setting( $this->get_setting_id( $this->genesis_settings, $this->banner_disable_key ) )->value() && $this->is_banner_area_enabled_globally( $wp_customize, $this->genesis_settings ) );
		// 			},
		// 		)
		// 	);

		// }

		// if ( $this->has_setting( 'layout' ) || $this->has_setting( 'layout_post_type' ) ) {

		// 	// Layouts break.
		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'cpt_archive_layouts_break' ),
		// 		array(
		// 			'default' => '',
		// 			'type'    => 'option',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		new Mai_Customize_Control_Break( $wp_customize,
		// 			$this->prefix . 'cpt_archive_layouts_break',
		// 			array(
		// 				'label'    => __( 'Layouts', 'mai-pro-engine' ),
		// 				'section'  => $this->section_id,
		// 				'settings' => false,
		// 			)
		// 		)
		// 	);

		// }

		// // Archive Layout.
		// if ( $this->has_setting( 'layout' ) ) {

		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'layout' ),
		// 		array(
		// 			'default'           => sanitize_key( mai_get_default_cpt_option( 'layout' ) ),
		// 			'type'              => 'option',
		// 			'sanitize_callback' => 'sanitize_key',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		$this->prefix . 'layout',
		// 		array(
		// 			'label'    => __( 'Archives', 'mai-pro-engine' ),
		// 			'section'  => $this->section_id,
		// 			'settings' => $this->get_setting_id( 'layout' ),
		// 			'type'     => 'select',
		// 			'choices'  => array_merge( array( '' => __( '- Archives Default -', 'mai-pro-engine' ) ), genesis_get_layouts_for_customizer() ),
		// 		)
		// 	);

		// }

		// // Single layout (saves to genesis-settings option).
		// if ( $this->has_setting( 'layout_post_type' ) ) {

		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( $this->genesis_settings, $this->singular_layout_key ),
		// 		array(
		// 			'default'           => sanitize_key( mai_get_default_option( $this->singular_layout_key ) ),
		// 			'type'              => 'option',
		// 			'sanitize_callback' => 'sanitize_key',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		$this->prefix . $this->singular_layout_key,
		// 		array(
		// 			'label'    => __( 'Single Entries', 'mai-pro-engine' ),
		// 			'section'  => $this->section_id,
		// 			'settings' => $this->get_setting_id( $this->genesis_settings, $this->singular_layout_key ),
		// 			'type'     => 'select',
		// 			'choices'  => array_merge( array( '' => __( '- Site Default -', 'mai-pro-engine' ) ), genesis_get_layouts_for_customizer() ),
		// 		)
		// 	);

		// }

		// if ( $this->has_setting( 'singular_image_post_type' ) || $this->has_setting( 'remove_meta_post_type' ) ) {

		// 	// Single Entry settings break.
		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'cpt_singular_entries_break' ),
		// 		array(
		// 			'default' => '',
		// 			'type'    => 'option',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		new Mai_Customize_Control_Break( $wp_customize,
		// 			$this->prefix . 'cpt_singular_entries_break',
		// 			array(
		// 				'label'    => __( 'Single Entries', 'mai-pro-engine' ),
		// 				'section'  => $this->section_id,
		// 				'settings' => false,
		// 			)
		// 		)
		// 	);

		// }

		// // Featured Image.
		// if ( $this->has_setting( 'singular_image_post_type' ) ) {

		// 	// Featured Image heading.
		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'cpt_featured_image_customizer_heading' ),
		// 		array(
		// 			'default' => '',
		// 			'type'    => 'option',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		new Mai_Customize_Control_Content( $wp_customize,
		// 			$this->prefix . 'cpt_featured_image_customizer_heading',
		// 			array(
		// 				'label'    => __( 'Featured Image', 'mai-pro-engine' ),
		// 				'section'  => $this->section_id,
		// 				'settings' => false,
		// 			)
		// 		)
		// 	);

		// 	// Featured Image (saves to genesis-settings option).
		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( $this->genesis_settings, $this->singular_image_key ),
		// 		array(
		// 			'default'           => mai_sanitize_one_zero( mai_get_default_option( $this->singular_image_key ) ),
		// 			'type'              => 'option',
		// 			'sanitize_callback' => 'mai_sanitize_one_zero',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		$this->prefix . $this->singular_image_key,
		// 		array(
		// 			'label'    => __( 'Display the Featured Image', 'mai-pro-engine' ),
		// 			'section'  => $this->section_id,
		// 			'settings' => $this->get_setting_id( $this->genesis_settings, $this->singular_image_key ),
		// 			'type'     => 'checkbox',
		// 		)
		// 	);

		// }

		// // Entry Meta single (saves to genesis-settings option).
		// if ( $this->has_setting( 'remove_meta_post_type' ) ) {

		// 	$remove_meta_choices = array();

		// 	if ( $this->supports( 'genesis-entry-meta-before-content' ) ) {
		// 		$remove_meta_choices['post_info'] = __( 'Remove Post Info', 'mai-pro-engine' );
		// 	}

		// 	if ( $this->supports( 'genesis-entry-meta-after-content' ) ) {
		// 		$remove_meta_choices['post_meta'] = __( 'Remove Post Meta', 'mai-pro-engine' );
		// 	}

		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( $this->genesis_settings, $this->remove_meta_single_key ),
		// 		array(
		// 			'default'           =>  $this->customizer_multicheck_sanitize_key( mai_get_default_option( $this->remove_meta_single_key ) ),
		// 			'type'              => 'option',
		// 			'sanitize_callback' => array( $this, 'customizer_multicheck_sanitize_key' ),
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		new Mai_Customize_Control_Multicheck( $wp_customize,
		// 			$this->prefix . $this->remove_meta_single_key,
		// 			array(
		// 				'label'    => __( 'Entry Meta', 'mai-pro-engine' ),
		// 				'section'  => $this->section_id,
		// 				'settings' => $this->get_setting_id( $this->genesis_settings, $this->remove_meta_single_key ),
		// 				'priority' => 10,
		// 				'choices'  => $remove_meta_choices,
		// 			)
		// 		)
		// 	);

		// }

		// // If has any acrhives.
		// if ( $this->has_archives() ) {

		// 	// If has any archive settings.
		// 	if ( $this->has_setting( 'enable_content_archive_settings' )
		// 		|| $this->has_setting( 'columns' )
		// 		|| $this->has_setting( 'content_archive' )
		// 		|| $this->has_setting( 'content_archive_thumbnail' )
		// 		|| $this->has_setting( 'image_location' )
		// 		|| $this->has_setting( 'image_size' )
		// 		|| $this->has_setting( 'image_alignment' )
		// 		|| $this->has_setting( 'remove_meta' ) ) {

		// 		// Archive settings break.
		// 		$wp_customize->add_setting(
		// 			$this->get_setting_id( 'cpt_archives_break' ),
		// 			array(
		// 				'default' => '',
		// 				'type'    => 'option',
		// 			)
		// 		);
		// 		$wp_customize->add_control(
		// 			new Mai_Customize_Control_Break( $wp_customize,
		// 				$this->prefix . 'cpt_archives_break',
		// 				array(
		// 					'label'    => __( 'Archives', 'mai-pro-engine' ),
		// 					'section'  => $this->section_id,
		// 					'settings' => false,
		// 				)
		// 			)
		// 		);

		// 		// Enable Content Archive Settings.
		// 		if ( $this->has_setting( 'enable_content_archive_settings' ) ) {

		// 			// Enable Content Archive Settings.
		// 			$wp_customize->add_setting(
		// 				$this->get_setting_id( 'enable_content_archive_settings' ),
		// 				array(
		// 					'default'           => mai_sanitize_one_zero( mai_get_default_cpt_option( 'enable_content_archive_settings' ) ),
		// 					'type'              => 'option',
		// 					'sanitize_callback' => 'mai_sanitize_one_zero',
		// 				)
		// 			);
		// 			$wp_customize->add_control(
		// 				$this->prefix . 'enable_content_archive_settings',
		// 				array(
		// 					'label'    => __( 'Enable custom archive settings', 'mai-pro-engine' ),
		// 					'section'  => $this->section_id,
		// 					'settings' => $this->get_setting_id( 'enable_content_archive_settings' ),
		// 					'priority' => 10,
		// 					'type'     => 'checkbox',
		// 				)
		// 			);

		// 		}

		// 		// Columns.
		// 		if ( $this->has_setting( 'columns' ) ) {

		// 			// Columns.
		// 			$wp_customize->add_setting(
		// 				$this->get_setting_id( 'columns' ),
		// 				array(
		// 					'default'           => absint( mai_get_default_cpt_option( 'columns' ) ),
		// 					'type'              => 'option',
		// 					'sanitize_callback' => 'absint',
		// 				)
		// 			);
		// 			$wp_customize->add_control(
		// 				$this->prefix . 'columns',
		// 				array(
		// 					'label'    => __( 'Columns', 'mai-pro-engine' ),
		// 					'section'  => $this->section_id,
		// 					'settings' => $this->get_setting_id( 'columns' ),
		// 					'priority' => 10,
		// 					'type'     => 'select',
		// 					'choices'  => array(
		// 						1 => __( 'None', 'mai-pro-engine' ),
		// 						2 => __( '2', 'mai-pro-engine' ),
		// 						3 => __( '3', 'mai-pro-engine' ),
		// 						4 => __( '4', 'mai-pro-engine' ),
		// 						6 => __( '6', 'mai-pro-engine' ),
		// 					),
		// 					'active_callback' => function() use ( $wp_customize ) {
		// 						return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
		// 					},
		// 				)
		// 			);

		// 		}

		// 		// Content Type.
		// 		if ( $this->has_setting( 'content_archive' ) ) {

		// 			$content_archive_choices = array(
		// 				'none' => __( 'No content', 'mai-pro-engine' ),
		// 			);

		// 			if ( $this->supports( 'editor' ) ) {
		// 				$content_archive_choices['full'] = __( 'Entry content', 'genesis' );
		// 			}

		// 			if ( $this->supports( 'excerpt' ) ) {
		// 				$content_archive_choices['excerpts'] = __( 'Entry excerpts', 'genesis' );
		// 			}

		// 			// Content Type.
		// 			$wp_customize->add_setting(
		// 				$this->get_setting_id( 'content_archive' ),
		// 				array(
		// 					'default'           => sanitize_key( mai_get_default_cpt_option( 'content_archive' ) ),
		// 					'type'              => 'option',
		// 					'sanitize_callback' => 'sanitize_key',
		// 				)
		// 			);
		// 			$wp_customize->add_control(
		// 				$this->prefix . 'content_archive',
		// 				array(
		// 					'label'           => __( 'Content', 'mai-pro-engine' ),
		// 					'section'         => $this->section_id,
		// 					'settings'        => $this->get_setting_id( 'content_archive' ),
		// 					'priority'        => 10,
		// 					'type'            => 'select',
		// 					'choices'         => $content_archive_choices,
		// 					'active_callback' => function() use ( $wp_customize ) {
		// 						return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
		// 					},
		// 				)
		// 			);

		// 			// Content Limit.
		// 			if ( $this->has_setting( 'content_archive_limit' ) ) {

		// 				$wp_customize->add_setting(
		// 					$this->get_setting_id( 'content_archive_limit' ),
		// 					array(
		// 						'default'           => absint( mai_get_default_cpt_option( 'content_archive_limit' ) ),
		// 						'type'              => 'option',
		// 						'sanitize_callback' => 'absint',
		// 					)
		// 				);
		// 				$wp_customize->add_control(
		// 					$this->prefix . 'content_archive_limit',
		// 					array(
		// 						'label'           => __( 'Limit content to how many characters?', 'mai-pro-engine' ),
		// 						'description'     => __( '(0 for no limit)', 'mai-pro-engine' ),
		// 						'section'         => $this->section_id,
		// 						'settings'        => $this->get_setting_id( 'content_archive_limit' ),
		// 						'priority'        => 10,
		// 						'type'            => 'number',
		// 						'active_callback' => function() use ( $wp_customize ) {
		// 							return (bool) ( $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value() && ( 'none' != $wp_customize->get_setting( $this->get_setting_id( 'content_archive' ) )->value() ) );
		// 						},
		// 					)
		// 				);

		// 			}
		// 		}
		// 	}
		// }

		// // Featured Image.
		// if ( $this->has_setting( 'content_archive_thumbnail' ) ) {

		// 	// Archive featured image, heading only.
		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'cpt_archives_featured_image_heading' ),
		// 		array(
		// 			'default' => '',
		// 			'type'    => 'option',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		new Mai_Customize_Control_Content( $wp_customize,
		// 			$this->prefix . 'cpt_archives_featured_image_heading',
		// 			array(
		// 				'label'           => __( 'Featured Image', 'mai-pro-engine' ),
		// 				'section'         => $this->section_id,
		// 				'settings'        => false,
		// 				'active_callback' => function() use ( $wp_customize ) {
		// 					return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
		// 				},
		// 			)
		// 		)
		// 	);

		// 	// Featured Image.
		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'content_archive_thumbnail' ),
		// 		array(
		// 			'default'           => mai_sanitize_one_zero( mai_get_default_cpt_option( 'content_archive_thumbnail' ) ),
		// 			'type'              => 'option',
		// 			'sanitize_callback' => 'mai_sanitize_one_zero',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		$this->prefix . 'content_archive_thumbnail',
		// 		array(
		// 			'label'           => __( 'Display the Featured Image', 'mai-pro-engine' ),
		// 			'section'         => $this->section_id,
		// 			'settings'        => $this->get_setting_id( 'content_archive_thumbnail' ),
		// 			'type'            => 'checkbox',
		// 			'active_callback' => function() use ( $wp_customize ) {
		// 				return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
		// 			},
		// 		)
		// 	);

		// 	// Image Location.
		// 	if ( $this->has_setting( 'image_location' ) ) {

		// 		$wp_customize->add_setting(
		// 			$this->get_setting_id( 'image_location' ),
		// 			array(
		// 				'default'           => sanitize_key( mai_get_default_cpt_option( 'image_location' ) ),
		// 				'type'              => 'option',
		// 				'sanitize_callback' => 'sanitize_key',
		// 			)
		// 		);
		// 		$wp_customize->add_control(
		// 			$this->prefix . 'image_location',
		// 			array(
		// 				'label'    => __( 'Image Location', 'genesis' ),
		// 				'section'  => $this->section_id,
		// 				'settings' => $this->get_setting_id( 'image_location' ),
		// 				'priority' => 10,
		// 				'type'     => 'select',
		// 				'choices'  => array(
		// 					'background'     => __( 'Background Image', 'mai-pro-engine' ),
		// 					'before_entry'   => __( 'Before Entry', 'mai-pro-engine' ),
		// 					'before_title'   => __( 'Before Title', 'mai-pro-engine' ),
		// 					'after_title'    => __( 'After Title', 'mai-pro-engine' ),
		// 					'before_content' => __( 'Before Content', 'mai-pro-engine' ),
		// 				),
		// 				'active_callback' => function() use ( $wp_customize ) {
		// 					return (bool) ( $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value() && (bool) $wp_customize->get_setting( $this->get_setting_id( 'content_archive_thumbnail' ) )->value() );
		// 				},
		// 			)
		// 		);

		// 	}

		// 	// Image Size.
		// 	if ( $this->has_setting( 'image_size' ) ) {

		// 		// Image Size.
		// 		$wp_customize->add_setting(
		// 			$this->get_setting_id( 'image_size' ),
		// 			array(
		// 				'default'           => sanitize_key( mai_get_default_cpt_option( 'image_size' ) ),
		// 				'type'              => 'option',
		// 				'sanitize_callback' => 'sanitize_key',
		// 			)
		// 		);
		// 		$wp_customize->add_control(
		// 			$this->prefix . 'image_size',
		// 			array(
		// 				'label'           => __( 'Image Size', 'genesis' ),
		// 				'section'         => $this->section_id,
		// 				'settings'        => $this->get_setting_id( 'image_size' ),
		// 				'priority'        => 10,
		// 				'type'            => 'select',
		// 				'choices'         => $this->customizer_get_image_sizes_config(),
		// 				'active_callback' => function() use ( $wp_customize, $settings_field ) {
		// 					return (bool) ( $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value() && (bool) $wp_customize->get_setting( $this->get_setting_id( 'content_archive_thumbnail' ) )->value() );
		// 				},
		// 			)
		// 		);

		// 	}

		// 	// Image Alignment.
		// 	if ( $this->has_setting( 'image_alignment' ) ) {

		// 		$wp_customize->add_setting(
		// 			$this->get_setting_id( 'image_alignment' ),
		// 			array(
		// 				'default'           => sanitize_key( mai_get_default_cpt_option( 'image_alignment' ) ),
		// 				'type'              => 'option',
		// 				'sanitize_callback' => 'sanitize_key',
		// 			)
		// 		);
		// 		$wp_customize->add_control(
		// 			$this->prefix . 'image_alignment',
		// 			array(
		// 				'label'    => __( 'Image Alignment', 'genesis' ),
		// 				'section'  => $this->section_id,
		// 				'settings' => $this->get_setting_id( 'image_alignment' ),
		// 				'priority' => 10,
		// 				'type'     => 'select',
		// 				'choices'  => array(
		// 					''            => __( '- None -', 'genesis' ),
		// 					'aligncenter' => __( 'Center', 'genesis' ),
		// 					'alignleft'   => __( 'Left', 'genesis' ),
		// 					'alignright'  => __( 'Right', 'genesis' ),
		// 				),
		// 				'active_callback' => function() use ( $wp_customize ) {
		// 					// Showing featured image and background is not image location.
		// 					return (bool) ( $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value() && $wp_customize->get_setting( $this->get_setting_id( 'content_archive_thumbnail' ) )->value() && ( 'background' != $wp_customize->get_setting( $this->get_setting_id( 'image_location' ) )->value() ) );
		// 				},
		// 			)
		// 		);

		// 	}

		// }

		// if ( $this->has_setting( 'more_link' ) ) {

		// 	// More Link heading
		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'cpt_more_link_heading' ),
		// 		array(
		// 			'default' => '',
		// 			'type'    => 'option',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		new Mai_Customize_Control_Content( $wp_customize,
		// 			$this->prefix . 'cpt_more_link_heading',
		// 			array(
		// 				'label'           => __( 'Read More Link', 'mai-pro-engine' ),
		// 				'section'         => $this->section_id,
		// 				'settings'        => false,
		// 				'active_callback' => function() use ( $wp_customize ) {
		// 					return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
		// 				},
		// 			)
		// 		)
		// 	);

		// 	// More Link
		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'more_link' ),
		// 		array(
		// 			'default'           => mai_sanitize_one_zero( mai_get_default_cpt_option( 'more_link' ) ),
		// 			'type'              => 'option',
		// 			'sanitize_callback' => 'mai_sanitize_one_zero',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		$this->prefix . 'more_link',
		// 		array(
		// 			'label'           => __( 'Display the Read More link', 'mai-pro-engine' ),
		// 			'section'         => $this->section_id,
		// 			'settings'        => $this->get_setting_id( 'more_link' ),
		// 			'priority'        => 10,
		// 			'type'            => 'checkbox',
		// 			'active_callback' => function() use ( $wp_customize ) {
		// 				return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
		// 			},
		// 		)
		// 	);

		// }

		// // Entry Meta.
		// if ( $this->has_setting( 'remove_meta_post_type' ) ) {

		// 	$remove_meta_choices = array();

		// 	if ( $this->supports( 'genesis-entry-meta-before-content' ) ) {
		// 		$remove_meta_choices['post_info'] = __( 'Remove Post Info', 'mai-pro-engine' );
		// 	}

		// 	if ( $this->supports( 'genesis-entry-meta-after-content' ) ) {
		// 		$remove_meta_choices['post_meta'] = __( 'Remove Post Meta', 'mai-pro-engine' );
		// 	}

		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'remove_meta_post_type' ),
		// 		array(
		// 			'default'           => $this->customizer_multicheck_sanitize_key( mai_get_default_cpt_option( 'remove_meta_post_type' ) ),
		// 			'type'              => 'option',
		// 			'sanitize_callback' => array( $this, 'customizer_multicheck_sanitize_key' ),
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		new Mai_Customize_Control_Multicheck( $wp_customize,
		// 			$this->prefix . 'remove_meta_post_type',
		// 			array(
		// 				'label'           => __( 'Entry Meta', 'mai-pro-engine' ),
		// 				'section'         => $this->section_id,
		// 				'settings'        => $this->get_setting_id( 'remove_meta_post_type' ),
		// 				'priority'        => 10,
		// 				'choices'         => $remove_meta_choices,
		// 				'active_callback' => function() use ( $wp_customize ) {
		// 					return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
		// 				},
		// 			)
		// 		)
		// 	);

		// }

		// // Remove Meta.
		// if ( $this->has_setting( 'remove_meta' ) ) {

		// 	// Remove Meta.
		// 	$wp_customize->add_setting(
		// 		_mai_get_setting_field( $settings_field, 'remove_meta' ),
		// 		array(
		// 			'default'           => _mai_customizer_multicheck_sanitize_key( mai_get_default_option( 'remove_meta' ) ),
		// 			'type'              => 'option',
		// 			'sanitize_callback' => '_mai_customizer_multicheck_sanitize_key',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		new Mai_Customize_Control_Multicheck( $wp_customize,
		// 			'remove_meta',
		// 			array(
		// 				'label'    => __( 'Entry Meta', 'mai-pro-engine' ),
		// 				'section'  => $section,
		// 				'settings' => _mai_get_setting_field( $settings_field, 'remove_meta' ),
		// 				'choices'  => $remove_meta_choices,
		// 			)
		// 		)
		// 	);

		// }

		// // Posts Per Page.
		// if ( $this->has_setting( 'posts_per_page' ) ) {

		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'posts_per_page' ),
		// 		array(
		// 			'default'           => absint( mai_get_default_cpt_option( 'posts_per_page' ) ),
		// 			'type'              => 'option',
		// 			'sanitize_callback' => 'absint',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		$this->prefix . 'posts_per_page',
		// 		array(
		// 			'label'           => __( 'Entries Per Page', 'mai-pro-engine' ),
		// 			'section'         => $this->section_id,
		// 			'settings'        => $this->get_setting_id( 'posts_per_page' ),
		// 			'priority'        => 10,
		// 			'type'            => 'number',
		// 			'active_callback' => function() use ( $wp_customize ) {
		// 				return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
		// 			},
		// 		)
		// 	);

		// }

		// // Posts Nav.
		// if ( $this->has_setting( 'posts_nav' ) ) {

		// 	$wp_customize->add_setting(
		// 		$this->get_setting_id( 'posts_nav' ),
		// 		array(
		// 			'default'           => sanitize_key( mai_get_default_cpt_option( 'posts_nav' ) ),
		// 			'type'              => 'option',
		// 			'sanitize_callback' => 'sanitize_key',
		// 		)
		// 	);
		// 	$wp_customize->add_control(
		// 		$this->prefix . 'posts_nav',
		// 		array(
		// 			'label'    => __( 'Pagination', 'genesis' ),
		// 			'section'  => $this->section_id,
		// 			'settings' => $this->get_setting_id( 'posts_nav' ),
		// 			'priority' => 10,
		// 			'type'     => 'select',
		// 			'choices'  => array(
		// 				'prev-next' => __( 'Previous / Next', 'genesis' ),
		// 				'numeric'   => __( 'Numeric', 'genesis' ),
		// 			),
		// 			'active_callback' => function() use ( $wp_customize ) {
		// 				return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
		// 			},
		// 		)
		// 	);

		// }

	}

	/**
	 * Helper function to check if the banner area is enabled globally.
	 *
	 * @param   object  $wp_customize    The customizer object.
	 * @param   string  $settings_field  The genesis setting to check. This should always be 'genesis-settings'.
	 *
	 * @return  bool.
	 */
	function is_banner_area_enabled_globally( $wp_customize ) {
		return (bool) $wp_customize->get_setting( $this->get_genesis_setting_id( 'enable_banner_area' ) )->value();
	}

	/**
	 * Get field name attribute value.
	 *
	 * @param   string  $key Option name.
	 *
	 * @return  string  Option name as key of settings field.
	 */
	function get_genesis_setting_id( $key ) {
		return sprintf( '%s[%s]', $this->genesis_settings, $key );
	}

	/**
	 * Get the image sizes array for Kirki.
	 *
	 * @return  array.
	 */
	function customizer_get_image_sizes_config() {
		// Get our image size options
		$sizes   = genesis_get_image_sizes();
		$options = array();
		foreach ( $sizes as $index => $value ) {
			$options[$index] = sprintf( '%s (%s x %s)', $index, $value['width'], $value['height'] );
		}
		return $options;
	}

	/**
	 * Helper function to sanitize all values in an array with 'sanitize_key' function.
	 *
	 * @param   array  $values  The values to sanitize.
	 *
	 * @return  array  The sanitize array.
	 */
	function customizer_multicheck_sanitize_key( $values ) {
		$multi_values = ! is_array( $values ) ? explode( ',', $values ) : $values;
		return ! empty( $multi_values ) ? array_map( 'sanitize_key', $multi_values ) : array();
	}

}
