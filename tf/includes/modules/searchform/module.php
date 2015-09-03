<?php
/**
 * Module Search Form.
 * 
 * Show the WP search form.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Searchform extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Search Form', 'themify-flow' ),
			'slug' => 'searchform',
			'shortcode' => 'tf_searchform',
			'description' => __( 'Display WordPress search form.', 'themify-flow' ),
			'category' => 'global'
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
		return apply_filters( 'tf_module_searchform_fields', array(
			// no options
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
		return apply_filters( 'tf_module_searchform_styles', array(
			'tf_module_searchform_container' => array(
				'label' => __( 'Search Container', 'themify-flow' ),
				'selector' => '.tf_searchform',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_searchform_input' => array(
				'label' => __( 'Search Input', 'themify-flow' ),
				'selector' => '.tf_searchform input[type=text]',
				'basic_styling' => array( 'border', 'font', 'margin', 'background' ),
			)
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
		ob_start(); ?>

		<?php get_search_form( true ); // true: echo the result ?>

		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/** Initialize module */
new TF_Module_Searchform();