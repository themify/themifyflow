<?php

class TF_Module_Icon extends TF_module {

	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Icon', 'themify-flow' ),
			'slug' => 'icon',
			'description' => __( 'Icon', 'themify-flow' ),
			'shortcode' => 'tf_icon',
			'category' => array( 'content', 'global' )
		) );
	}

	/**
	 * Module options.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function fields() {
		return apply_filters( 'tf_module_icon_fields', array(
			'size' => array(
				'type' => 'radio',
				'label' => __( 'Size', 'themify-flow' ),
				'options' => array(
					array( 'name' => __( 'Normal', 'themify-flow' ), 'value' => 'normal', 'selected' => true ),
					array( 'name' => __( 'Small', 'themify-flow' ), 'value' => 'small' ),
					array( 'name' => __( 'Large', 'themify-flow' ), 'value' => 'large' ),
					array( 'name' => __( 'xLarge', 'themify-flow' ), 'value' => 'xlarge' ),
				),
			),
			'style' => array(
				'type' => 'radio',
				'label' => __( 'Icon Background Style', 'themify-flow' ),
				'options' => array(
					array( 'name' => __( 'Circle', 'themify-flow' ), 'value' => 'circle', 'selected' => true ),
					array( 'name' => __( 'Rounded', 'themify-flow' ), 'value' => 'rounded' ),
					array( 'name' => __( 'Squared', 'themify-flow' ), 'value' => 'squared' ),
					array( 'name' => __( 'None', 'themify-flow' ), 'value' => 'none' ),
				),
			),
			'icons' => array(
				'type' => 'repeater',
				'fields' => array(
					'icon_def' => array(
						'type' => 'multi',
						'label' => __( 'Icon', 'themify-flow' ),
						'fields' => array(
							'icon' => array(
								'type' => 'icon',
								'label' => __( 'Icon', 'themify-flow' ),
							),
							'icon_color' => array(
								'type' => 'color',
								'label' => __( 'Color', 'themify-flow' ),
							),
							'icon_bg' => array(
								'type' => 'color',
								'label' => __( 'Background', 'themify-flow' ),
							),
						),
					),
					'label' => array(
						'type' => 'text',
						'label' => __( 'Label', 'themify-flow' ),
						'class' => 'tf_input_width_70',
					),
					'link' => array(
						'type' => 'text',
						'label' => __( 'Link', 'themify-flow' ),
						'class' => 'tf_input_width_70',
					),
				),
				'new_row_text' => __( '+ Add New', 'themify-flow' ),
				'_temp' => array( 'icon', 'icon_color', 'icon_bg', 'label', 'link' ) // this should be removed
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
		return apply_filters( 'tf_module_icon_styles', array(
			'tf_module_icon_container' => array(
				'label' => __( 'Icons Container', 'themify-flow' ),
				'selector' => '.tf_icons',
				'basic_styling' => array( 'background', 'padding', 'margin', 'border' ),
			),
			'tf_module_icon_icon' => array(
				'label' => __( 'Icon', 'themify-flow' ),
				'selector' => '.none .tf_icon .tf_icon_icon, .small .tf_icon .tf_icon_icon, .large .tf_icon .tf_icon_icon, .xlarge .tf_icon .tf_icon_icon',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_icon_link' => array(
				'label' => __( 'Icon Link', 'themify-flow' ),
				'selector' => '.tf_icon_link',
				'basic_styling' => array( 'background', 'padding', 'margin' ),
			),
			'tf_module_icon_link_hover' => array(
				'label' => __( 'Icon Link Hover', 'themify-flow' ),
				'selector' => '.tf_icon_link:hover',
				'basic_styling' => array( 'background' ),
			),
			'tf_module_icon_label' => array(
				'label' => __( 'Icon Label', 'themify-flow' ),
				'selector' => '.tf_icon_label',
				'basic_styling' => array( 'font' ),
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
			'size' => 'normal',
			'style' => 'circle',
			'icons' => 0
		), array_filter( $atts ) ) );

		// Begin building markup for icon.
		$output = '';

		if( $icons > 0 && ! empty( $atts['icons_order'] ) ) {
			$output .= sprintf( '<ul class="tf_icons %s %s">', $size, $style );
			$ids = explode( ',', $atts['icons_order'] );
			foreach( $ids as $id ) {
				foreach( array( 'icon', 'icon_color', 'icon_bg', 'label', 'link' ) as $prop ) {
					$name = "icons_{$id}_{$prop}";
					$$prop = isset( $atts[$name] ) ? $atts[$name] : '';
				}

				$output .= '<li class="tf_icon">';
				// Set front and background colors.
				$colors = '';
				$style_attr = '';
				if ( ! empty( $icon_bg ) ) {
					$colors .= 'background-color: ' . tf_get_rgba_color( $icon_bg ) . ';';
				}
				if ( ! empty( $icon_color ) ) {
					$colors .= 'color: ' . tf_get_rgba_color( $icon_color ) . ';';
				}
				if ( ! empty( $colors ) ) {
					$style_attr = 'style="' . esc_attr( $colors ) . '"';
				}

				// Sanitize link
				$output .= '<a href="' . esc_url( $link ) . '" class="tf_icon_link">';

				// Build icon
				if ( ! empty( $icon ) ) {
					$output .= '<i class="tf_icon_icon fa ' . esc_attr( $icon ) . '" ' . $style_attr . '></i>';
				}

				// Build label
				if ( ! empty( $label ) ) {
					$output .= '<span class="tf_icon_label">' . $label . '</span>';
				}
				$output .= '</a>';

				$output .= '</li>';
			}
			$output .= '</ul>';
		}

		return $output;
	}

	public function has_shortcode_content() {
		return true;
	}
}

new TF_Module_Icon();