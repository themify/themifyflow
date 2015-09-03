<?php
/**
 * Module Text.
 * 
 * Show text blocks with rich editor.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Text extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __('Text', 'themify-flow'),
			'slug' => 'text',
			'shortcode' => 'tf_text',
			'description' => 'Text module',
			'category' => 'content'
		) );
	}

	/**
	 * Module settings field
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function fields() {
		return apply_filters( 'tf_module_text_fields', array(
			'content' => array(
				'type' => 'wp_editor',
				'wrapper' => 'no',
				'rows' => 16
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
		return apply_filters( 'tf_module_text_styles', array(
			'tf_module_text_container' => array(
				'label' => __('Text Container', 'themify-flow'),
				'selector' => '.tf_module_text',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_text_p' => array(
				'label' => __( 'Paragraph', 'themify-flow' ),
				'selector' => '.tf_module_text p',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_text_a' => array(
				'label' => __( 'Link', 'themify-flow' ),
				'selector' => '.tf_module_text a',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_text_h1' => array(
				'label' => __( 'H1', 'themify-flow' ),
				'selector' => '.tf_module_text h1',
				'basic_styling' => array( 'font', 'margin' ),
			),
			'tf_module_text_h2' => array(
				'label' => __( 'H2', 'themify-flow' ),
				'selector' => '.tf_module_text h2',
				'basic_styling' => array( 'font', 'margin' ),
			),
			'tf_module_text_h3' => array(
				'label' => __( 'H3', 'themify-flow' ),
				'selector' => '.tf_module_text h3',
				'basic_styling' => array( 'font', 'margin' ),
			),
			'tf_module_text_h4' => array(
				'label' => __( 'H4', 'themify-flow' ),
				'selector' => '.tf_module_text h4',
				'basic_styling' => array( 'font', 'margin' ),
			),
			'tf_module_text_h5' => array(
				'label' => __( 'H5', 'themify-flow' ),
				'selector' => '.tf_module_text h5',
				'basic_styling' => array( 'font', 'margin' ),
			),
			'tf_module_text_h6' => array(
				'label' => __( 'H6', 'themify-flow' ),
				'selector' => '.tf_module_text h6',
				'basic_styling' => array( 'font', 'margin' ),
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
		$atts = shortcode_atts( array(
		), $atts, $this->shortcode ); // must add the third params $this->shortcode, for builder shortcode rendering

		/** We can separate the module markup in separated template file later */
		$output = '<div class="tf_module_text">'; 
		$output .= apply_filters( 'tf_module_content', $content );
		$output .= '</div>';

		return $output;
	}
}

new TF_Module_Text();