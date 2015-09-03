<?php
/**
 * Module Divider.
 * 
 * Display Divider
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Divider extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Divider', 'themify-flow' ),
			'slug' => 'divider',
			'shortcode' => 'tf_divider',
			'description' => 'Divider module',
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
		global $TF;

		$image_base = $TF->framework_uri() . '/assets/img/builder';

		return apply_filters( 'tf_module_divider_fields', array(
			'layout'  => array(
				'type'       => 'layout',
				'label'      => __( 'Divider Layout', 'themify-flow' ),
				'options'    => array(
					array( 'img' => $image_base . '/solid.png', 'value' => 'solid', 'label' => __( 'Solid', 'themify-flow' ), 'selected' => true ),
					array( 'img' => $image_base . '/dashed.png', 'value' => 'dashed', 'label' => __( 'Dashed', 'themify-flow' ) ),
					array( 'img' => $image_base . '/dotted.png', 'value' => 'dotted', 'label' => __( 'Dotted', 'themify-flow' ) ),
					array( 'img' => $image_base . '/double.png', 'value' => 'double', 'label' => __( 'Double', 'themify-flow' ) ),
				)
			),
			'thinkness' => array(
				'type' => 'number',
				'label' => __( 'Stroke Thickness', 'themify-flow' ),
				'class' => 'tf_input_width_10',
				'description' => ' px',
				'default' => '1'
			),
			'divider_color' => array(
				'type' => 'color',
				'label' => __( 'Divider Color', 'themify-flow' ),
			)
		) );
	}

	/**
	 * Module style selectors.
	 * 
	 * Hold module style selectors to be used in Styling Panel.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function styles() {
		return apply_filters( 'tf_module_divider_styles', array(
			'tf_divider' => array(
				'label' => __( 'Divider', 'themify-flow' ),
				'selector' => '.tf_divider',
				'basic_styling' => array( 'margin' ),
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
		extract( shortcode_atts( array(
			'layout' => 'solid',
			'thinkness' => '',
			'divider_color' => '',
		), $atts, $this->shortcode ) );

		ob_start(); ?>

		<div class="tf_divider <?php echo $layout; ?>" style="border-top-width: <?php echo empty( $thinkness ) ? 1 : $thinkness; ?>px;
			<?php if( '' != $divider_color ) : ?>border-top-color: <?php echo tf_get_rgba_color( $divider_color ); ?>;<?php endif; ?>
		"></div>

		<?php
		$output = ob_get_clean();
		return $output;
	}
}

new TF_Module_Divider();