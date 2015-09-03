<?php
/**
 * Module Widget Area.
 * 
 * Display WordPress widget areas (aka "sidebar"s).
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Widget_Area extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Widget Area', 'themify-flow' ),
			'slug' => 'widget_area',
			'shortcode' => 'tf_widget_area',
			'description' => __( 'Display widget area.', 'themify-flow' ),
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
		return apply_filters( 'tf_module_widget_area_fields', array(
			'area' => array(
				'type' => 'widget_area',
				'label' => __( 'Area', 'themify-flow' ),
			),
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
		return apply_filters( 'tf_module_widget_area_styles', array(
			'tf_module_widget_area_container' => array(
				'label' => __( 'Widget Area Container', 'themify-flow' ),
				'selector' => '.tf_widget_area',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_widget_area_widget_container' => array(
				'label' => __( 'Widget Container', 'themify-flow' ),
				'selector' => '.tf_widget',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_widget_area_widget_title' => array(
				'label' => __( 'Widget Title', 'themify-flow' ),
				'selector' => '.tf_widget_title',
				'basic_styling' => array( 'font', 'margin', 'padding', 'border' ),
			),
			'tf_module_widget_area_link' => array(
				'label' => __( 'Widget Link', 'themify-flow' ),
				'selector' => '.tf_widget a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_widget_area_link_hover' => array(
				'label' => __( 'Widget Link Hover', 'themify-flow' ),
				'selector' => '.tf_widget a:hover',
				'basic_styling' => array( 'font' ),
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
		extract( wp_parse_args( $atts, array(
			'area' => ''
		) ) );
		ob_start(); ?>

		<?php if( is_active_sidebar( $area ) ) : ?>
			<div class="tf_widget_area">
				<?php dynamic_sidebar( $area ); ?>
			</div>
		<?php endif; ?>

		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/** Initialize module */
new TF_Module_Widget_Area();