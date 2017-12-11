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
 * 'featured_image'
 * 'featured_image_location'
 * 'featured_image_size'        // TODO.
 * 'featured_image_alignment'   // TODO.
 * 'remove_meta_single'
 * 'enable_content_archive_settings'
 * 'columns'
 * 'content_archive'
 * 'content_archive_limit'
 * 'content_archive_thumbnail'
 * 'image_location'
 * 'image_size'
 * 'image_alignment'
 * 'more_link'
 * 'more_link_text'             // TODO.
 * 'remove_meta_archive'
 * 'posts_per_page'
 * 'posts_nav'
 *
 * @return  void
 */
// add_action( 'init', 'mai_post_type_settings_init', 999 );
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
		// $this->genesis_settings = 'genesis-settings';
		$this->genesis_settings = GENESIS_SETTINGS_FIELD;
		$this->settings_field   = 'mai_content_settings';
		$this->section_id       = sprintf( 'mai_%s_settings', $this->name );
		$this->prefix           = sprintf( '%s_', $this->name );

		/**
		 * Add Mai CPT support here.
		 * This should happen here, internally only. Please don't add 'mai-settings' support to CPT's manually.
		 */
		add_post_type_support( $this->name, 'mai-settings' );

		// Customizer settings.
		add_action( 'customize_register', array( $this, 'customizer_settings' ), 22 );
	}

	/**
	 * Register the customizer settings sections and fields.
	 *
	 * @param   object  $wp_customize  The customizer object.
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

		if ( $this->has_setting( 'banner_id' )
			|| $this->has_setting( 'hide_banner' )
			|| $this->has_setting( 'banner_disable' )
			|| $this->has_setting( 'banner_disable_taxonomies' ) ) {

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
					'default'           => absint( mai_get_default_post_type_setting( 'banner_id', $this->name ) ),
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

		if ( $this->has_setting( 'hide_banner' )
			|| $this->has_setting( 'banner_disable' )
			|| $this->has_setting( 'banner_disable_taxonomies' ) ) {

			// Hide banner, heading only.
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
						'label'           => __( 'Hide Banner', 'mai-pro-engine' ),
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
					'default'           => mai_sanitize_one_zero( mai_get_default_post_type_setting( 'hide_banner', $this->name ) ),
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

			// Disable banner singular.
			$wp_customize->add_setting(
				$this->get_setting_id( 'banner_disable' ),
				array(
					'default'           => mai_sanitize_one_zero( mai_get_default_post_type_setting( 'banner_disable' ) ),
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

			// Disable banner taxonomies.
			$disable_taxonomies = array();
			$taxonomies         = get_object_taxonomies( $this->name, 'objects' );
			if ( $taxonomies ) {
				foreach ( $taxonomies as $taxo ) {
					if ( $this->name == mai_get_taxonomy_post_type( $taxo ) ) {
						$disable_taxonomies[ $taxo->name ] = $taxo->label;
					}
				}

				$wp_customize->add_setting(
					$this->get_setting_id( 'banner_disable_taxonomies' ),
					array(
						'default'           => $this->customizer_multicheck_sanitize_key( mai_get_default_post_type_setting( 'banner_disable_taxonomies' ) ),
						'type'              => 'option',
						'sanitize_callback' => array( $this, 'customizer_multicheck_sanitize_key' ),
					)
				);
				$wp_customize->add_control(
					new Mai_Customize_Control_Multicheck( $wp_customize,
						$this->prefix . 'banner_disable_taxonomies',
						array(
							'label'           => __( 'Hide Banner on (taxonomies)', 'mai-pro-engine' ),
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

		if ( $this->has_setting( 'banner_featured_image' ) ) {

			// Banner featured image, heading only.
			$wp_customize->add_setting(
				$this->get_setting_id( 'banner_featured_image_heading' ),
				array(
					'default' => '',
					'type'    => 'option',
				)
			);
			$wp_customize->add_control(
				new Mai_Customize_Control_Content( $wp_customize,
					$this->prefix . 'banner_featured_image_heading',
					array(
						'label'           => __( 'Featured Image on (single entries)', 'mai-pro-engine' ),
						'section'         => $this->section_id,
						'settings'        => false,
						'active_callback' => function() use ( $wp_customize ) {
							return ( (bool) ! $wp_customize->get_setting( $this->get_setting_id( 'banner_disable' ) )->value() && $this->is_banner_area_enabled_globally( $wp_customize ) );
						},
					)
				)
			);

			// Banner featured image.
			$wp_customize->add_setting(
				$this->get_setting_id( 'banner_featured_image' ),
				array(
					'default'           => mai_sanitize_one_zero( mai_get_default_post_type_setting( 'banner_featured_image' ) ),
					'type'              => 'option',
					'sanitize_callback' => 'mai_sanitize_one_zero',
				)
			);
			$wp_customize->add_control(
				$this->prefix . 'banner_featured_image',
				array(
					'label'           => __( 'Use featured image as banner image', 'mai-pro-engine' ),
					'section'         => $this->section_id,
					'settings'        => $this->get_setting_id( 'banner_featured_image' ),
					'priority'        => 10,
					'type'            => 'checkbox',
					'active_callback' => function() use ( $wp_customize ) {
						return ( (bool) ! $wp_customize->get_setting( $this->get_setting_id( 'banner_disable' ) )->value() && $this->is_banner_area_enabled_globally( $wp_customize ) );
					},
				)
			);

		}

		if ( $this->has_setting( 'layout_archive' ) || $this->has_setting( 'layout_single' ) ) {

			// Layouts break.
			$wp_customize->add_setting(
				$this->get_setting_id( 'layouts_break' ),
				array(
					'default' => '',
					'type'    => 'option',
				)
			);
			$wp_customize->add_control(
				new Mai_Customize_Control_Break( $wp_customize,
					$this->prefix . 'layouts_break',
					array(
						'label'    => __( 'Layouts', 'mai-pro-engine' ),
						'section'  => $this->section_id,
						'settings' => false,
					)
				)
			);

		}

		// Archives Layout.
		if ( $this->has_setting( 'layout_archive' ) ) {

			// Single layout.
			if ( $this->has_setting( 'layout_single' ) ) {

				$wp_customize->add_setting(
					$this->get_setting_id( 'layout_single' ),
					array(
						'default'           => sanitize_key( mai_get_default_post_type_setting( 'layout_single', $this->name ) ),
						'type'              => 'option',
						'sanitize_callback' => 'sanitize_key',
					)
				);
				$wp_customize->add_control(
					$this->prefix . 'layout_single',
					array(
						'label'    => __( 'Single Entries', 'mai-pro-engine' ),
						'section'  => $this->section_id,
						'settings' => $this->get_setting_id( 'layout_single' ),
						'type'     => 'select',
						'choices'  => array_merge( array( '' => __( '- Site Default -', 'mai-pro-engine' ) ), genesis_get_layouts_for_customizer() ),
					)
				);

			}

			$wp_customize->add_setting(
				$this->get_setting_id( 'layout_archive' ),
				array(
					'default'           => sanitize_key( mai_get_default_post_type_setting( 'layout_archive', $this->name ) ),
					'type'              => 'option',
					'sanitize_callback' => 'sanitize_key',
				)
			);
			$wp_customize->add_control(
				$this->prefix . 'layout_archive',
				array(
					'label'    => __( 'Archives', 'mai-pro-engine' ),
					'section'  => $this->section_id,
					'settings' => $this->get_setting_id( 'layout_archive' ),
					'type'     => 'select',
					'choices'  => array_merge( array( '' => __( '- Archives Default -', 'mai-pro-engine' ) ), genesis_get_layouts_for_customizer() ),
				)
			);

		}

		if ( $this->has_setting( 'featured_image_location' ) || $this->has_setting( 'remove_meta_single' ) ) {

			// Single Entry settings break.
			$wp_customize->add_setting(
				$this->get_setting_id( 'singular_break' ),
				array(
					'default' => '',
					'type'    => 'option',
				)
			);
			$wp_customize->add_control(
				new Mai_Customize_Control_Break( $wp_customize,
					$this->prefix . 'singular_break',
					array(
						'label'    => __( 'Single Entries', 'mai-pro-engine' ),
						'section'  => $this->section_id,
						'settings' => false,
					)
				)
			);

		}

		// Featured Image.
		if ( $this->has_setting( 'featured_image' ) ) {

			// Featured Image heading.
			$wp_customize->add_setting(
				$this->get_setting_id( 'featured_image_location_heading' ),
				array(
					'default' => '',
					'type'    => 'option',
				)
			);
			$wp_customize->add_control(
				new Mai_Customize_Control_Content( $wp_customize,
					$this->prefix . 'featured_image_location_heading',
					array(
						'label'    => __( 'Featured Image', 'mai-pro-engine' ),
						'section'  => $this->section_id,
						'settings' => false,
					)
				)
			);

			// Featured Image Location.
			$wp_customize->add_setting(
				$this->get_setting_id( 'featured_image' ),
				array(
					'default'           => mai_sanitize_one_zero( mai_get_default_post_type_setting( 'featured_image' ) ),
					'type'              => 'option',
					'sanitize_callback' => 'mai_sanitize_one_zero',
				)
			);
			$wp_customize->add_control(
				$this->prefix . 'featured_image',
				array(
					'label'           => __( 'Display the Featured Image', 'mai-pro-engine' ),
					'section'         => $this->section_id,
					'settings'        => $this->get_setting_id( 'featured_image' ),
					'type'            => 'checkbox',
				)
			);

			// Featured Image Location.
			$wp_customize->add_setting(
				$this->get_setting_id( 'featured_image_location' ),
				array(
					'default'           => sanitize_key( mai_get_default_post_type_setting( 'featured_image_location', $this->name ) ),
					'type'              => 'option',
					'sanitize_callback' => 'sanitize_key',
				)
			);
			$wp_customize->add_control(
				$this->prefix . 'featured_image_location',
				array(
					'label'    => __( 'Image Location', 'mai-pro-engine' ),
					'section'  => $this->section_id,
					'settings' => $this->get_setting_id( 'featured_image_location' ),
					'type'     => 'select',
					'choices'  => array(
						'before_entry'   => __( 'Before Entry', 'mai-pro-engine' ),
						'before_title'   => __( 'Before Title', 'mai-pro-engine' ),
						'after_title'    => __( 'After Title', 'mai-pro-engine' ),
						'before_content' => __( 'Before Content', 'mai-pro-engine' ),
					),
					'active_callback' => function() use ( $wp_customize ) {
						return ( (bool) $wp_customize->get_setting( $this->get_setting_id( 'featured_image' ) )->value() );
					},
				)
			);

		}

		// Remove Meta single.
		if ( $this->has_setting( 'remove_meta_single' ) ) {

			$remove_meta_choices = array();

			if ( $this->supports( 'genesis-entry-meta-before-content' ) ) {
				$remove_meta_choices['post_info'] = __( 'Remove Post Info', 'mai-pro-engine' );
			}

			if ( $this->supports( 'genesis-entry-meta-after-content' ) ) {
				$remove_meta_choices['post_meta'] = __( 'Remove Post Meta', 'mai-pro-engine' );
			}

			$wp_customize->add_setting(
				$this->get_setting_id( 'remove_meta_single' ),
				array(
					'default'           =>  $this->customizer_multicheck_sanitize_key( mai_get_default_post_type_setting( 'remove_meta_single', $this->name ) ),
					'type'              => 'option',
					'sanitize_callback' => array( $this, 'customizer_multicheck_sanitize_key' ),
				)
			);
			$wp_customize->add_control(
				new Mai_Customize_Control_Multicheck( $wp_customize,
					$this->prefix . 'remove_meta_single',
					array(
						'label'    => __( 'Entry Meta', 'mai-pro-engine' ),
						'section'  => $this->section_id,
						'settings' => $this->get_setting_id( 'remove_meta_single' ),
						'priority' => 10,
						'choices'  => $remove_meta_choices,
					)
				)
			);

		}

		// If has any acrhives.
		if ( $this->has_archives() ) {

			// If has any archive settings.
			if ( $this->has_setting( 'enable_content_archive_settings' )
				|| $this->has_setting( 'columns' )
				|| $this->has_setting( 'content_archive' )
				|| $this->has_setting( 'content_archive_limit' ) ) {

				// Archive settings break.
				$wp_customize->add_setting(
					$this->get_setting_id( 'post_type_archives_break' ),
					array(
						'default' => '',
						'type'    => 'option',
					)
				);
				$wp_customize->add_control(
					new Mai_Customize_Control_Break( $wp_customize,
						$this->prefix . 'post_type_archives_break',
						array(
							'label'    => __( 'Archives', 'mai-pro-engine' ),
							'section'  => $this->section_id,
							'settings' => false,
						)
					)
				);

				// Enable Content Archive Settings.
				if ( $this->has_setting( 'enable_content_archive_settings' ) ) {

					// Enable Content Archive Settings.
					$wp_customize->add_setting(
						$this->get_setting_id( 'enable_content_archive_settings' ),
						array(
							'default'           => mai_sanitize_one_zero( mai_get_default_post_type_setting( 'enable_content_archive_settings', $this->name ) ),
							'type'              => 'option',
							'sanitize_callback' => 'mai_sanitize_one_zero',
						)
					);
					$wp_customize->add_control(
						$this->prefix . 'enable_content_archive_settings',
						array(
							'label'    => __( 'Enable custom archive settings', 'mai-pro-engine' ),
							'section'  => $this->section_id,
							'settings' => $this->get_setting_id( 'enable_content_archive_settings' ),
							'priority' => 10,
							'type'     => 'checkbox',
						)
					);

				}

				// Columns.
				if ( $this->has_setting( 'columns' ) ) {

					// Columns.
					$wp_customize->add_setting(
						$this->get_setting_id( 'columns' ),
						array(
							'default'           => absint( mai_get_default_post_type_setting( 'columns', $this->name ) ),
							'type'              => 'option',
							'sanitize_callback' => 'absint',
						)
					);
					$wp_customize->add_control(
						$this->prefix . 'columns',
						array(
							'label'    => __( 'Columns', 'mai-pro-engine' ),
							'section'  => $this->section_id,
							'settings' => $this->get_setting_id( 'columns' ),
							'priority' => 10,
							'type'     => 'select',
							'choices'  => array(
								1 => __( 'None', 'mai-pro-engine' ),
								2 => __( '2', 'mai-pro-engine' ),
								3 => __( '3', 'mai-pro-engine' ),
								4 => __( '4', 'mai-pro-engine' ),
								6 => __( '6', 'mai-pro-engine' ),
							),
							'active_callback' => function() use ( $wp_customize ) {
								return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
							},
						)
					);

				}

				// Content Type.
				if ( $this->has_setting( 'content_archive' ) ) {

					$content_archive_choices = array(
						'none' => __( 'No content', 'mai-pro-engine' ),
					);
					if ( $this->supports( 'editor' ) ) {
						$content_archive_choices['full'] = __( 'Entry content', 'genesis' );
					}
					if ( $this->supports( 'excerpt' ) ) {
						$content_archive_choices['excerpts'] = __( 'Entry excerpts', 'genesis' );
					}

					// Content Type.
					$wp_customize->add_setting(
						$this->get_setting_id( 'content_archive' ),
						array(
							'default'           => sanitize_key( mai_get_default_post_type_setting( 'content_archive', $this->name ) ),
							'type'              => 'option',
							'sanitize_callback' => 'sanitize_key',
						)
					);
					$wp_customize->add_control(
						$this->prefix . 'content_archive',
						array(
							'label'           => __( 'Content', 'mai-pro-engine' ),
							'section'         => $this->section_id,
							'settings'        => $this->get_setting_id( 'content_archive' ),
							'priority'        => 10,
							'type'            => 'select',
							'choices'         => $content_archive_choices,
							'active_callback' => function() use ( $wp_customize ) {
								return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
							},
						)
					);

					// Content Limit.
					if ( $this->has_setting( 'content_archive_limit' ) ) {

						$wp_customize->add_setting(
							$this->get_setting_id( 'content_archive_limit' ),
							array(
								'default'           => absint( mai_get_default_post_type_setting( 'content_archive_limit', $this->name ) ),
								'type'              => 'option',
								'sanitize_callback' => 'absint',
							)
						);
						$wp_customize->add_control(
							$this->prefix . 'content_archive_limit',
							array(
								'label'           => __( 'Limit content to how many characters?', 'mai-pro-engine' ),
								'description'     => __( '(0 for no limit)', 'mai-pro-engine' ),
								'section'         => $this->section_id,
								'settings'        => $this->get_setting_id( 'content_archive_limit' ),
								'priority'        => 10,
								'type'            => 'number',
								'active_callback' => function() use ( $wp_customize ) {
									return (bool) ( $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value() && ( 'none' != $wp_customize->get_setting( $this->get_setting_id( 'content_archive' ) )->value() ) );
								},
							)
						);

					}
				}
			}

			// Featured Image.
			if ( $this->has_setting( 'content_archive_thumbnail' ) ) {

				// Archive featured image, heading only.
				$wp_customize->add_setting(
					$this->get_setting_id( 'post_type_archives_featured_image_heading' ),
					array(
						'default' => '',
						'type'    => 'option',
					)
				);
				$wp_customize->add_control(
					new Mai_Customize_Control_Content( $wp_customize,
						$this->prefix . 'post_type_archives_featured_image_heading',
						array(
							'label'           => __( 'Featured Image', 'mai-pro-engine' ),
							'section'         => $this->section_id,
							'settings'        => false,
							'active_callback' => function() use ( $wp_customize ) {
								return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
							},
						)
					)
				);

				// Featured Image.
				$wp_customize->add_setting(
					$this->get_setting_id( 'content_archive_thumbnail' ),
					array(
						'default'           => mai_sanitize_one_zero( mai_get_default_post_type_setting( 'content_archive_thumbnail', $this->name ) ),
						'type'              => 'option',
						'sanitize_callback' => 'mai_sanitize_one_zero',
					)
				);
				$wp_customize->add_control(
					$this->prefix . 'content_archive_thumbnail',
					array(
						'label'           => __( 'Display the Featured Image', 'mai-pro-engine' ),
						'section'         => $this->section_id,
						'settings'        => $this->get_setting_id( 'content_archive_thumbnail' ),
						'type'            => 'checkbox',
						'active_callback' => function() use ( $wp_customize ) {
							return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
						},
					)
				);

				// Image Location.
				if ( $this->has_setting( 'image_location' ) ) {

					$wp_customize->add_setting(
						$this->get_setting_id( 'image_location' ),
						array(
							'default'           => sanitize_key( mai_get_default_post_type_setting( 'image_location', $this->name ) ),
							'type'              => 'option',
							'sanitize_callback' => 'sanitize_key',
						)
					);
					$wp_customize->add_control(
						$this->prefix . 'image_location',
						array(
							'label'    => __( 'Image Location', 'genesis' ),
							'section'  => $this->section_id,
							'settings' => $this->get_setting_id( 'image_location' ),
							'priority' => 10,
							'type'     => 'select',
							'choices'  => array(
								'background'     => __( 'Background Image', 'mai-pro-engine' ),
								'before_entry'   => __( 'Before Entry', 'mai-pro-engine' ),
								'before_title'   => __( 'Before Title', 'mai-pro-engine' ),
								'after_title'    => __( 'After Title', 'mai-pro-engine' ),
								'before_content' => __( 'Before Content', 'mai-pro-engine' ),
							),
							'active_callback' => function() use ( $wp_customize ) {
								return (bool) ( $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value() && (bool) $wp_customize->get_setting( $this->get_setting_id( 'content_archive_thumbnail' ) )->value() );
							},
						)
					);

				}

				// Image Size.
				if ( $this->has_setting( 'image_size' ) ) {

					// Image Size.
					$wp_customize->add_setting(
						$this->get_setting_id( 'image_size' ),
						array(
							'default'           => sanitize_key( mai_get_default_post_type_setting( 'image_size', $this->name ) ),
							'type'              => 'option',
							'sanitize_callback' => 'sanitize_key',
						)
					);
					$wp_customize->add_control(
						$this->prefix . 'image_size',
						array(
							'label'           => __( 'Image Size', 'genesis' ),
							'section'         => $this->section_id,
							'settings'        => $this->get_setting_id( 'image_size' ),
							'priority'        => 10,
							'type'            => 'select',
							'choices'         => $this->customizer_get_image_sizes_config(),
							'active_callback' => function() use ( $wp_customize, $settings_field ) {
								return (bool) ( $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value() && (bool) $wp_customize->get_setting( $this->get_setting_id( 'content_archive_thumbnail' ) )->value() );
							},
						)
					);

				}

				// Image Alignment.
				if ( $this->has_setting( 'image_alignment' ) ) {

					$wp_customize->add_setting(
						$this->get_setting_id( 'image_alignment' ),
						array(
							'default'           => sanitize_key( mai_get_default_post_type_setting( 'image_alignment', $this->name ) ),
							'type'              => 'option',
							'sanitize_callback' => 'sanitize_key',
						)
					);
					$wp_customize->add_control(
						$this->prefix . 'image_alignment',
						array(
							'label'    => __( 'Image Alignment', 'genesis' ),
							'section'  => $this->section_id,
							'settings' => $this->get_setting_id( 'image_alignment' ),
							'priority' => 10,
							'type'     => 'select',
							'choices'  => array(
								''            => __( '- None -', 'genesis' ),
								'aligncenter' => __( 'Center', 'genesis' ),
								'alignleft'   => __( 'Left', 'genesis' ),
								'alignright'  => __( 'Right', 'genesis' ),
							),
							'active_callback' => function() use ( $wp_customize ) {
								// Showing featured image and background is not image location.
								return (bool) ( $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value() && $wp_customize->get_setting( $this->get_setting_id( 'content_archive_thumbnail' ) )->value() && ( 'background' != $wp_customize->get_setting( $this->get_setting_id( 'image_location' ) )->value() ) );
							},
						)
					);

				}

			}

			if ( $this->has_setting( 'more_link' ) ) {

				// More Link heading.
				$wp_customize->add_setting(
					$this->get_setting_id( 'post_type_more_link_heading' ),
					array(
						'default' => '',
						'type'    => 'option',
					)
				);
				$wp_customize->add_control(
					new Mai_Customize_Control_Content( $wp_customize,
						$this->prefix . 'post_type_more_link_heading',
						array(
							'label'           => __( 'Read More Link', 'mai-pro-engine' ),
							'section'         => $this->section_id,
							'settings'        => false,
							'active_callback' => function() use ( $wp_customize ) {
								return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
							},
						)
					)
				);

				// More Link.
				$wp_customize->add_setting(
					$this->get_setting_id( 'more_link' ),
					array(
						'default'           => mai_sanitize_one_zero( mai_get_default_post_type_setting( 'more_link', $this->name ) ),
						'type'              => 'option',
						'sanitize_callback' => 'mai_sanitize_one_zero',
					)
				);
				$wp_customize->add_control(
					$this->prefix . 'more_link',
					array(
						'label'           => __( 'Display the Read More link', 'mai-pro-engine' ),
						'section'         => $this->section_id,
						'settings'        => $this->get_setting_id( 'more_link' ),
						'priority'        => 10,
						'type'            => 'checkbox',
						'active_callback' => function() use ( $wp_customize ) {
							return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
						},
					)
				);

			}

			// Entry Meta.
			if ( $this->has_setting( 'remove_meta_archive' ) ) {

				$remove_meta_choices = array();

				if ( $this->supports( 'genesis-entry-meta-before-content' ) ) {
					$remove_meta_choices['post_info'] = __( 'Remove Post Info', 'mai-pro-engine' );
				}

				if ( $this->supports( 'genesis-entry-meta-after-content' ) ) {
					$remove_meta_choices['post_meta'] = __( 'Remove Post Meta', 'mai-pro-engine' );
				}

				$wp_customize->add_setting(
					$this->get_setting_id( 'remove_meta_archive' ),
					array(
						'default'           => $this->customizer_multicheck_sanitize_key( mai_get_default_post_type_setting( 'remove_meta_archive', $this->name ) ),
						'type'              => 'option',
						'sanitize_callback' => array( $this, 'customizer_multicheck_sanitize_key' ),
					)
				);
				$wp_customize->add_control(
					new Mai_Customize_Control_Multicheck( $wp_customize,
						$this->prefix . 'remove_meta_archive',
						array(
							'label'           => __( 'Entry Meta', 'mai-pro-engine' ),
							'section'         => $this->section_id,
							'settings'        => $this->get_setting_id( 'remove_meta_archive' ),
							'priority'        => 10,
							'choices'         => $remove_meta_choices,
							'active_callback' => function() use ( $wp_customize ) {
								return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
							},
						)
					)
				);

			}

			// Posts Per Page.
			if ( $this->has_setting( 'posts_per_page' ) ) {

				$wp_customize->add_setting(
					$this->get_setting_id( 'posts_per_page' ),
					array(
						'default'           => absint( mai_get_default_post_type_setting( 'posts_per_page', $this->name ) ),
						'type'              => 'option',
						'sanitize_callback' => 'absint',
					)
				);
				$wp_customize->add_control(
					$this->prefix . 'posts_per_page',
					array(
						'label'           => __( 'Entries Per Page', 'mai-pro-engine' ),
						'section'         => $this->section_id,
						'settings'        => $this->get_setting_id( 'posts_per_page' ),
						'priority'        => 10,
						'type'            => 'number',
						'active_callback' => function() use ( $wp_customize ) {
							return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
						},
					)
				);

			}

			// Posts Nav.
			if ( $this->has_setting( 'posts_nav' ) ) {

				$wp_customize->add_setting(
					$this->get_setting_id( 'posts_nav' ),
					array(
						'default'           => sanitize_key( mai_get_default_post_type_setting( 'posts_nav', $this->name ) ),
						'type'              => 'option',
						'sanitize_callback' => 'sanitize_key',
					)
				);
				$wp_customize->add_control(
					$this->prefix . 'posts_nav',
					array(
						'label'    => __( 'Pagination', 'genesis' ),
						'section'  => $this->section_id,
						'settings' => $this->get_setting_id( 'posts_nav' ),
						'priority' => 10,
						'type'     => 'select',
						'choices'  => array(
							'prev-next' => __( 'Previous / Next', 'genesis' ),
							'numeric'   => __( 'Numeric', 'genesis' ),
						),
						'active_callback' => function() use ( $wp_customize ) {
							return (bool) $wp_customize->get_setting( $this->get_setting_id( 'enable_content_archive_settings' ) )->value();
						},
					)
				);

			}

		}
	}

	function get_setting_id( $key ) {
		if ( 'post' === $this->name && 'posts_per_page' === $key ) {
			return 'posts_per_page';
		}
		return sprintf( "%s['%s']['%s']", $this->settings_field, $this->name, $key );
	}

	/**
	 * Get field name attribute value.
	 *
	 * @param   string  $key Option name.
	 *
	 * @return  string  Option name as key of settings field.
	 */
	function get_genesis_setting_id( $key ) {
		return sprintf( "%s[%s]", $this->genesis_settings, $key );
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
