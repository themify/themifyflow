<?php

class TF_Module_Site_Logo extends TF_module {

	public function __construct() {
		parent::__construct(array(
			'name' => __( 'Site Logo', 'themify-flow' ),
			'slug' => 'site-logo',
			'description' => __( 'Site logo module', 'themify-flow' ),
			'shortcode' => 'tf_site_logo',
			'category' => 'global'
		));
	}

	public function fields() {
		return apply_filters( 'tf_module_site_logo_fields', array(
			'logo_type' => array(
				'type' => 'radio',
				'label' => '&nbsp;',
				'options' => array(
					array( 'name' => __( 'Text', 'themify-flow' ), 'value' => 'text', 'selected' => true ),
					array( 'name' => __( 'Image', 'themify-flow' ), 'value' => 'image' ),
				),
				'toggleable' => array( 'target_class' => 'logo_type' )
			),
			'title_tag' => array(
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
				),
				'row_class' => 'logo_type logo_type-text',
			),
			'logo_text' => array(
				'type' => 'text',
				'label' => __( 'Logo Text', 'themify-flow' ),
				'class' => 'tf_input_width_70',
				'description' => '<br/>' . sprintf( __( 'Leave blank to use the Site Title in <a href="%s">WP > Settings</a>', 'themify-flow' ), admin_url( 'options-general.php' ) ),
				'row_class' => 'logo_type logo_type-text',
			),
			'logo_image' => array(
				'type' => 'image',
				'label' => __( 'Image', 'themify-flow' ),
				'class' => 'tf_input_width_70',
				'row_class' => 'logo_type logo_type-image',
			),
			'image_dimensions' => array(
				'type' => 'multi',
				'label' => __( 'Image Dimensions', 'themify-flow' ),
				'fields' => array(
					'width' => array(
						'type' => 'text',
						'label' => __( '', 'themify-flow' ),
						'class' => 'tf_input_width_10',
						'description' => ' X ',
					),
					'height' => array(
						'type' => 'text',
						'label' => __( '', 'themify-flow' ),
						'class' => 'tf_input_width_10',
						'description' => 'px',
					),
				),
				'row_class' => 'logo_type logo_type-image',
			),
			'logo_link' => array(
				'type' => 'text',
				'label' => __( 'Logo Link', 'themify-flow' ),
				'class' => 'tf_input_width_70', // this is not working
				'description' => '<br/>' . __( 'Leave blank to use the Site URL', 'themify-flow' ),
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
			'tf_module_site_logo_container' => array(
				'label' => __( 'Site Logo Container', 'themify-flow' ),
				'selector' => '.tf_site_logo',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_site_logo' => array(
				'label' => __( 'Site Logo Text', 'themify-flow' ),
				'selector' => '.tf_site_logo a',
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
			'logo_type' => 'text',
			'title_tag' => 'p',
			'logo_text' => '',
			'logo_image' => '',
			'width' => '',
			'height' => '',
			'logo_link' => ''
		) ) );
		if( '' == $logo_text ) $logo_text = get_bloginfo( 'name' );
		if( '' == $logo_link ) $logo_link = home_url();

		ob_start(); ?>

		<?php if( $logo_type == 'text' ) : ?>
			<<?php echo $title_tag; ?> class="tf_site_logo">
				<a href="<?php echo $logo_link; ?>"><?php echo $logo_text; ?></a>
			</<?php echo $title_tag; ?>><!-- .tf_site_logo -->
		<?php else : ?>
			<figure class="tf_site_logo">
				<a href="<?php echo $logo_link; ?>"><img src="<?php echo TF_Model::get_attachment_url( $logo_image ); ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" alt="<?php echo $logo_text; ?>" /></a>
			</figure>
		<?php endif; ?>

		<?php
		$output = ob_get_clean();

		return $output;
	}
}

new TF_Module_Site_Logo();