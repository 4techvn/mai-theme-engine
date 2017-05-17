<?php

add_action( 'register_shortcode_ui', 'shortcode_ui_dev_advanced_example' );
/**
 * Shortcode UI setup for the grid shortcode.
 *
 * It is called when the Shortcake action hook `register_shortcode_ui` is called.
 *
 * This example shortcode has many editable attributes, and more complex UI.
 *
 * @since 1.0.0
 */
function shortcode_ui_dev_advanced_example() {

	$post_types			= get_post_types( array( 'public' => true, 'publicly_queryable' => true ), 'objects' );
	$post_type_options  = array();
	foreach ( $post_types as $post_type ) {
		$post_type_options[] = array(
			'value' => $post_type->name,
			'label' => $post_type->label,
		);
	}

	$taxos		   = get_taxonomies( array( 'public' => true ), 'objects' );
	$taxo_options  = array();
	foreach ( $taxos as $taxo ) {
		$taxo_options[] = array(
			'value' => $taxo->name,
			'label' => $taxo->label,
		);
	}

	/*
	 * Define the UI for attributes of the shortcode. Optional.
	 *
	 * In this demo example, we register multiple fields related to showing a quotation
	 * - Attachment, Citation Source, Select Page, Background Color, Alignment and Year.
	 *
	 * If no UI is registered for an attribute, then the attribute will
	 * not be editable through Shortcake's UI. However, the value of any
	 * unregistered attributes will be preserved when editing.
	 *
	 * Each array must include 'attr', 'type', and 'label'.
	 * * 'attr' should be the name of the attribute.
	 * * 'type' options include: text, checkbox, textarea, radio, select, email,
	 *     url, number, and date, post_select, attachment, color.
	 * * 'label' is the label text associated with that input field.
	 *
	 * Use 'meta' to add arbitrary attributes to the HTML of the field.
	 *
	 * Use 'encode' to encode attribute data. Requires customization in shortcode callback to decode.
	 *
	 * Depending on 'type', additional arguments may be available.
	 */
	$fields = array(
		// array(
		// 	'label'  => esc_html__( 'Citation Source', 'shortcode-ui-example', 'shortcode-ui' ),
		// 	'attr'   => 'source',
		// 	'type'   => 'text',
		// 	'encode' => true,
		// 	'meta'   => array(
		// 		'placeholder' => esc_html__( 'Test placeholder', 'shortcode-ui-example', 'shortcode-ui' ),
		// 		'data-test'   => 1,
		// 	),
		// ),
		array(
			'label'       => __( 'Content', 'maitheme' ),
			'description' => __( 'The type of content', 'maitheme' ),
			'attr'        => 'content',
			'type'        => 'select',
			'options'     => array(
				array(
					'label'	  => __( 'Post Types', 'maitheme' ),
					'options' => $post_type_options,
				),
				array(
					'label'	  => __( 'Taxonomies', 'maitheme' ),
					'options' => $taxo_options,
				),
			),
		),
		array(
			'label'       => __( 'Number', 'maitheme' ),
			'description' => __( 'The number of entries', 'maitheme' ),
			'attr'        => 'number',
			'type'        => 'number',
		),
	);
	/*
	 * Define the Shortcode UI arguments.
	 */
	$shortcode_ui_args = array(
		/*
		 * How the shortcode should be labeled in the UI. Required argument.
		 */
		'label' => __( 'Mai Grid', 'maitheme' ),
		/*
		 * Include an icon with your shortcode. Optional.
		 * Use a dashicon, or full HTML (e.g. <img src="/path/to/your/icon" />).
		 */
		'listItemImage' => 'dashicons-grid-view',
		/*
		 * Limit this shortcode UI to specific posts. Optional.
		 */
		'post_type' => array( 'page', 'post' ),
		/*
		 * Define the UI for attributes of the shortcode. Optional.
		 *
		 * See above, to where the the assignment to the $fields variable was made.
		 */
		'attrs' => $fields,
	);
	shortcode_ui_register_for_shortcode( 'grid', $shortcode_ui_args );
}
