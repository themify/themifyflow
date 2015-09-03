<?php
/**
 * Module Author Box.
 * 
 * The module loads the includes/author-box.php file inside the theme, to override that
 * create a child theme and copy the file to the same location in the child theme and modify.
 *
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Author_Box extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Author Box', 'themify-flow' ),
			'slug' => 'author-box',
			'shortcode' => 'tf_author_box',
			'description' => __( 'Author Box', 'themify-flow' ),
			'category' => 'single'
			// 'category' => array( 'single', 'page' )
		) );
	}

	/**
	 * Module settings field.
	 * 
	 * Display module options.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function fields() {
		return apply_filters( 'tf_module_author_box_fields', array(
			'avatar_size' => array(
				'type' => 'text',
				'label' => __( 'Author Avatar Size', 'themify-flow' ),
				'class' => 'tf_input_width_10',
				'description' => 'px'
			)
		) );
	}

	/**
	 * Module style selectors.
	 * 
	 * Hold module stye selectors to be used in Styling Panel.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function styles() {
		return apply_filters( 'tf_module_author_box_styles', array(
			'tf_module_author_box_container' => array(
				'label' => __( 'Author Box Container', 'themify-flow' ),
				'selector' => '.tf_author_box',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_author_box_avatar' => array(
				'label' => __( 'Author Avatar', 'themify-flow' ),
				'selector' => '.tf_author_avatar',
				'basic_styling' => array( 'border', 'font', 'margin', 'background' ),
			),
			'tf_module_author_box_name' => array(
				'label' => __( 'Author Name', 'themify-flow' ),
				'selector' => '.tf_author_name',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_author_box_bio' => array(
				'label' => __( 'Author Bio', 'themify-flow' ),
				'selector' => '.tf_author_bio',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
		) );
	}

	/**
	 * Render main shortcode.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public function render_shortcode( $atts, $content = null ) {
		extract( $atts = shortcode_atts( array(
			'avatar_size' => 50
		), array_filter( $atts ), $this->shortcode ) );

		// call the action hook used for get_template_part
		do_action( 'get_template_part_includes/author-box' );

		$template = locate_template( array( 'includes/author-box.php' ), false );
		ob_start();
			include( $template );
		$output = ob_get_clean();

		return $output;
	}
}

/** Initialize module */
new TF_Module_Author_Box();