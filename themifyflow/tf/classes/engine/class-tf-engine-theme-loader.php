<?php
/**
 * Theme Loader class.
 * 
 * Handle theme loader, set default theme.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Engine_Theme_Loader {
	
	/**
	 * Display Theme name.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $name
	 */
	public $name;

	/**
	 * Display Theme Slug.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $slug
	 */
	public $slug;

	/**
	 * Display Theme ID.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var int $theme_id
	 */
	public $theme_id = 0;

	/**
	 * Check if TF theme active founded.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @var boolean $theme_founded
	 */
	protected $theme_founded = false;

	/**
	 * Contructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		$theme = get_transient( 'tf_cached_active_theme' );
		if ( false === $theme ) {
			$args = array(
				'post_type'   => 'tf_theme',
				'post_status' => 'publish',
				'numberposts' => 1,
				'meta_query'  => array(
					array(
						'key'   => 'tf_active_theme',
						'value' => 'true'
					)
				)
			);
			$query = new WP_Query( $args );
			$theme = $query->get_posts();
			set_transient( 'tf_cached_active_theme', $theme, 0 );
		}
		
		if ( $theme ) {
			$this->theme_founded = true;
			$this->theme_id = $theme[0]->ID;
			$this->name = $theme[0]->post_title;
			$this->slug = $theme[0]->post_name;
		} else {
			$this->theme_founded = false;
			$this->name = __('Base', 'themify-flow');
			$this->slug = 'base';
		}

		// Add slug prefix based on active theme for Template and Template Part post types
		add_filter( 'wp_unique_post_slug', array( $this, 'add_prefix_post_slug' ), 10, 6 );
		add_action( 'tf_import_before_insert_post', array( $this, 'remove_prefix_post_slug'), 10, 2 );

	}

	/**
	 * Get theme info.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function get_theme_info() {
		if ( $this->theme_founded ) {
			$theme_info = get_post_meta( $this->theme_id, 'theme_info', true );
			if ( ! empty( $theme_info ) ) {
				return $theme_info;
			}
		}
		return $this->default_theme_info();
	}

	/**
	 * Default theme info.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @return array
	 */
	protected function default_theme_info() {
		return apply_filters( 'tf_default_theme_info', array(
			'tf_theme_description' => __('Base theme is the default Themify Flow theme', 'themify-flow'),
			'tf_theme_author' => 'Themify',
			'tf_theme_author_link' => 'http://themifyflow.com',
			'tf_theme_version' => '1.0.0'
		) );
	}

	/**
	 * Rewrite slug to add theme prefix {theme-name}-slug.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $slug 
	 * @param int $post_ID 
	 * @param string $post_status 
	 * @param string $post_type 
	 * @param string $post_parent 
	 * @param string $original_slug 
	 * @return string
	 */
	public function add_prefix_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		if ( 'tf_template' == $post_type || 'tf_template_part' == $post_type ) {
			if ( ! preg_match( '/^' . $this->slug . '/', $slug ) ) {
				$slug = $this->slug . '-' . $slug;
			}
		}
		// Change dash to underscore for theme post type slug
		if ( 'tf_theme' == $post_type ) 
			$slug = str_replace( '-', '_', $slug );
		
		return $slug;
	}

	public function remove_prefix_post_slug( $postdata, $source ) {
		if ( 'theme' == $source ) {
			remove_filter( 'wp_unique_post_slug', array( $this, 'add_prefix_post_slug' ), 10, 6 );
		}
	}
}