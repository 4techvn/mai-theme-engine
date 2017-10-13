<?php


add_action( 'mai_before_content_archive', 'mai_do_archive_settings' );
function mai_do_archive_settings() {
	$post_type = mai_get_archive_post_type();
	if ( ! $post_type ) {
		return;
	}
	$archives = new Mai_Post_Type_Archives( $post_type );
}

/**
 * The post type archives class.
 * This handles the custom layout for post type, taxonomy archives, author, search, etc,
 * since they all fall back to 'post' as the post type to get their settings from.
 *
 * @package Mai_Post_Type_Archives
 */
class Mai_Post_Type_Archives extends Mai_Post_Type {

	protected $options = array(); // Values for genesis_options filter.

	// This should receive mai_get_archive_post_type().
	function __construct( $post_type ) {
		// Let the parent handle construction.
		parent::__construct( $post_type );
		// Run.
		$this->set_options();
		$this->genesis_options();
		$this->hooks();
	}

	public function set_options() {
		foreach( $this->placeholders as $placeholder ) {
			if ( $this->has_setting( $placeholder ) ) {
				// Set the real key, with the value.
				$this->options[ $this->key( $placeholder ) ] = $this->value( $placeholder );
			}
		}
	}

	public function genesis_options() {
		if ( ! is_array( $this->options ) || empty( $this->options ) ) {
			return;
		}
		add_filter( 'genesis_options', function( $options ) {
			foreach( $this->options as $key => $value ) {
				$options[ $key ] = $value;
			}
			return $options;
		});
	}

	public function hooks() {

		add_action( 'mai_before_content_archive', array( $this, 'settings' ) );
		add_action( 'mai_before_flex_loop',       array( $this, 'wraps' ) );

		if ( $this->has_setting( 'remove_loop' ) ) {
			$this->remove_loop();
		}
		if ( $this->has_setting( 'posts_per_page' ) ) {
			$this->posts_per_page();
		}
	}

	public function settings() {
		// Content.
		if ( $this->has_setting( 'content_archive' ) ) {
			if ( 'none' === $this->value( 'content_archive' ) ) {
				// Remove the post content
				remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
			}
		}
		// Remove the post image.
		remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
		// If we're showing the image.
		if ( $this->value( 'content_archive_thumbnail' ) && $this->value( 'image_location' ) ) {
			$this->do_image();
		}
	}

	/**
	 * Flex loop opening html and column filters.
	 * Add and remove the post/product class filters to create columns.
	 *
	 * @return  void
	 */
	public function wraps() {

		// Flex row wrap.
		$attributes['class'] = 'row gutter-30';
		printf( '<div %s>', genesis_attr( 'flex-row', $attributes ) );

		// Create an anonomous function using the column count
		$flex_classes = function( $classes ) {
			$classes[] = mai_get_flex_entry_classes_by_columns( $columns );
			// If background image or image is not aligned.
			if ( 'background' === $this->value( 'image_location' ) || empty( $this->value( 'image_alignment' ) ) ) {
				$classes[] = 'column';
			} else {
				$classes[] = 'image-' . $this->value( 'image_alignment' );
			}
			return $classes;
		};

		// Add flex entry classes
		add_filter( 'post_class', $flex_classes );
		add_filter( 'product_cat_class', $flex_classes );

		/**
		 * After the loops, remove the entry classes filters and close the flex loop.
		 * This makes sure the columns classes aren't applied to
		 * additional loops.
		 */
		add_action( 'mai_after_flex_loop', function() use ( $flex_classes ) {
			remove_filter( 'post_class', $flex_classes );
			remove_filter( 'product_cat_class', $flex_classes );
			echo '</div>';
		});
	}

	public function remove_loop() {
		// Check.
		if ( ! (bool) $this->value( 'remove_loop' ) ) {
			return;
		}
		// Run.
		remove_action( 'genesis_loop',           'genesis_do_loop' );
		remove_action( 'genesis_after_endwhile', 'genesis_posts_nav' );
		remove_action( 'genesis_after_loop',     'genesis_posts_nav' );
	}

	public function posts_per_page() {
		// Check.
		if ( ! $this->value( 'posts_per_page' ) ) {
			return;
		}
		// Run.
		add_filter( 'pre_get_posts', 'mai_content_archive_posts_per_page' );
		function mai_content_archive_posts_per_page( $query ) {
			// Bail if not the main query.
			if ( ! $query->is_main_query() || is_admin() || is_singular() ) {
				return;
			}
			$query->set( 'posts_per_page', absint( $this->value( 'posts_per_page' ) ) );
		}
	}

	/**
	 * Add the images in the correct location
	 */
	public function do_image() {
		// Before Entry.
		if ( 'before_entry' === $this->value( 'image_location' ) ) {
			add_action( 'genesis_entry_header', 'genesis_do_post_image', 2 );
		}
		// Before Title.
		elseif ( 'before_title' === $this->value( 'image_location' ) ) {
			add_action( 'genesis_entry_header', 'genesis_do_post_image', 8 );
		}
		// After Title.
		elseif ( 'after_title' === $this->value( 'image_location' ) ) {
			add_action( 'genesis_entry_header', 'genesis_do_post_image', 10 );
		}
		// Before Content.
		elseif ( 'before_content' === $this->value( 'image_location' ) ) {
			add_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
		}
		// Background Image.
		elseif ( 'background' === $this->value( 'image_location' ) ) {
			// Add the entry image as a background image.
			add_action( 'genesis_before_entry', 'mai_do_entry_image_background' );
			// Add the background image link.
			add_action( 'genesis_entry_header', 'mai_do_bg_image_link', 1 );
			// Remove bg image link function so additional loops are not affected.
			add_action( 'mai_after_content_archive', function() {
				remove_action( 'genesis_entry_header', 'mai_do_bg_image_link', 1 );
			});
		}
		// Add the location as a class to the image link.
		add_filter( 'genesis_attr_entry-image-link', function( $attributes ) {
			// Replace underscore with hyphen
			$location = str_replace( '_', '-', $this->value( 'image_location' ) );
			// Add the class
			$attributes['class'] .= sprintf( ' entry-image-%s', $this->value( 'image_location' ) );
			return $attributes;
		});
	}

}
