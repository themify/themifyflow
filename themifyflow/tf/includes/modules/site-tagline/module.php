<?php

class TF_Module_Site_Tagline extends TF_module {

	public function __construct() {
		parent::__construct(array(
			'name' => __( 'Site Tagline', 'themify-flow' ),
			'slug' => 'site-tagline',
			'description' => __( 'Site tagline module', 'themify-flow' ),
			'shortcode' => 'tf_site_tagline',
			'category' => 'global'
		));
	}

	public function fields() {
		return apply_filters( 'tf_module_site_tagline_fields', array(
			'tag' => array(
				'type' => 'select',
				'label' => __( 'HTML Tag', 'themify-flow' ),
				'options' => array(
					array( 'name' => __( 'Paragraph', 'themify-flow' ), 'value' => 'p' ),
					array( 'name' => 'H1', 'value' => 'h1' ),
					array( 'name' => 'H2', 'value' => 'h2' ),
					array( 'name' => 'H3', 'value' => 'h3' ),
					array( 'name' => 'H4', 'value' => 'h4' ),
					array( 'name' => 'H5', 'value' => 'h5' ),
					array( 'name' => 'H6', 'value' => 'h6' ),
				)
			),
			'tagline_text' => array(
				'type' => 'text',
				'label' => __( 'Tagline Text', 'themify-flow' ),
				'class' => 'tf_input_width_70',
				'description' => '<br/>' . sprintf( __( 'Leave blank to use the Tagline in <a href="%s">WP > Settings</a>', 'themify-flow' ), admin_url( 'options-general.php' ) ),
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
		return apply_filters( 'tf_module_site_logo_styles', array(
			'tf_module_site_tagline' => array(
				'label' => __( 'Site Tagline', 'themify-flow' ),
				'selector' => '.tf_site_tagline',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
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
			'tag' => 'p',
			'tagline_text' => ''
		), $atts, $this->shortcode ) );

		if( $tagline_text == '' ) $tagline_text = get_bloginfo( 'description' );
		ob_start(); ?>

		<<?php echo $tag; ?> class="tf_site_tagline">
			<?php echo $tagline_text; ?>
		</<?php echo $tag; ?>><!-- .tf_site_tagline -->

		<?php
		$output = ob_get_clean();

		return $output;
	}
}

new TF_Module_Site_Tagline();