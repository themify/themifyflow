<?php
/**
 * Layout class.
 * 
 * Handle Template region Header, Sidebar, Content, and Footer data.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
final class TF_Layout {
	/**
	 * Layout ID.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var int $layout_id
	 */
	public $layout_id = 0;

	/**
	 * Template Name.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $layout_name
	 */
	public $layout_name;

	/**
	 * Template post_content.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $layout_content
	 */
	public $layout_content;

	/**
	 * Template Header Option.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $header
	 */
	public $header = 'default';

	/**
	 * Template Footer Option
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $footer
	 */ 
	public $footer = 'default';

	/**
	 * Template Sidebar Option
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $sidebar
	 */
	public $sidebar = 'sidebar_right';

	/**
	 * Template Type Option.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $type
	 */
	public $type;

	/**
	 * Template Part Header section.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $region_header
	 */
	public $region_header;

	/**
	 * Template Part Sidebar section.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $region_sidebar
	 */
	public $region_sidebar;

	/**
	 * Template Part Footer section.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $region_footer
	 */
	public $region_footer;

	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		// Populate region with some default data
		$this->region_header = TF_Model::get_template_region_meta( $this->layout_id, 'header' );
		$this->region_sidebar = TF_Model::get_template_region_meta( $this->layout_id, 'sidebar' );
		$this->region_footer = TF_Model::get_template_region_meta( $this->layout_id, 'footer' );
	}

	/**
	 * Setup Template Layout.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param object $template Post Object.
	 */
	public function setup_layout( $template ) {

		if ( ! is_object( $template ) ) 
			return;

		$this->layout_id = $template->ID;
		$this->layout_name = $template->post_name;
		$this->layout_content = $template->post_content;

		// get header option
		if ( $template->tf_template_header_option )
			$this->header = $template->tf_template_header_option;

		// get header option
		if ( $template->tf_template_sidebar_option )
			$this->sidebar = $template->tf_template_sidebar_option;

		// get footer option
		if ( $template->tf_template_footer_option )
			$this->footer = $template->tf_template_footer_option;

		// get type option
		if ( $template->tf_template_type )
			$this->type = $template->tf_template_type;

		$this->region_header = empty( $template->tf_template_region_header ) ? TF_Model::get_template_region_meta( $template->ID, 'header' ) : sprintf( '[tf_template_part slug="%s"]', $template->tf_template_region_header );
		$this->region_sidebar = empty( $template->tf_template_region_sidebar ) ? TF_Model::get_template_region_meta( $template->ID, 'sidebar' ) : sprintf( '[tf_template_part slug="%s"]', $template->tf_template_region_sidebar );
		$this->region_footer = empty( $template->tf_template_region_footer ) ? TF_Model::get_template_region_meta( $template->ID, 'footer' ) : sprintf( '[tf_template_part slug="%s"]', $template->tf_template_region_footer );

		add_filter( 'body_class', array( $this, 'body_class'), 10 );
	}

	/**
	 * Filter body_class.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $classes Body classnames.
	 * @return array
	 */
	public function body_class( $classes ) {
		$classes[] = $this->sidebar;

		// Custom template css class
		$custom_css = get_post_meta( $this->layout_id, 'tf_template_custom_css_class', true );
		if ( ! empty( $custom_css ) ) {
			$custom_css_arr = explode(' ', $custom_css );
			foreach ( $custom_css_arr as $class ) {
				$classes[] = sanitize_html_class( $class );
			}
		}

		// return the $classes array
		return apply_filters( 'tf_body_class', $classes );
	}

	/**
	 * Render Template.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $region Template Part section.
	 * @return string
	 */
	public function render( $content ) {
		global $wp_embed;
		$content = $wp_embed->run_shortcode( $content );
		$content = do_shortcode( shortcode_unautop( $content ) );
		return $content;
	}

	public function get_styles_model( $args = array() ) {
		global $tf_styling_control;
		$styles = array();
		if ( is_singular( 'tf_template_part' ) ) {
			$styles = $tf_styling_control->get_bootstrap_styles( get_the_ID(), $args );
		} else {
			$styles = $tf_styling_control->get_bootstrap_styles( $this->layout_id, $args );
		}
		return $styles;
	}

	/**
	 * From the current template ID, return the list of template parts that compose their regions.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array|bool Associative array where each item is like region => template part id. False if no custom meta was found.
	 */
	public function get_template_parts_by_layout() {
		$layout_custom = get_post_custom( $this->layout_id );
		if ( is_array( $layout_custom ) ) {
			$template_parts = array();
			if ( ! empty( $layout_custom['tf_template_region_header'][0] ) ) {
				$template_parts['tf_template_region_header'] = str_replace( array( '[tf_template_part slug="', '"]' ), '', $layout_custom['tf_template_region_header'][0] );
			}
			if ( ! empty( $layout_custom['tf_template_region_sidebar'][0] ) ) {
				$template_parts['tf_template_region_sidebar'] = str_replace( array( '[tf_template_part slug="', '"]' ), '', $layout_custom['tf_template_region_sidebar'][0] );
			}
			if ( ! empty( $layout_custom['tf_template_region_footer'][0] ) ) {
				$template_parts['tf_template_region_footer'] = str_replace( array( '[tf_template_part slug="', '"]' ), '', $layout_custom['tf_template_region_footer'][0] );
			}
			return $template_parts;
		} else {
			return false;
		}
	}
}

/** Initialize class on init action */
add_action( 'init', 'tf_setup_layout_var' );
function tf_setup_layout_var() {
	global $TF_Layout;
	$TF_Layout = new TF_Layout();
}