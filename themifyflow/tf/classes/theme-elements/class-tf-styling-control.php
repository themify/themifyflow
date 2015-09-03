<?php

class TF_Styling_Control {

	/**
	 * List of data to pass to front end and insert in templates.
	 * @var array
	 */
	var $js_data = array();

	/**
	 * List of data to pass to front end and insert in templates.
	 * @var array
	 */
	var $js_controls = array();

	public function __construct(){
		locate_template( 'tf/styling-config.php', true, true);
	}

	public function properties() {
		$controls = array(
			'background' => array(
				'name' => 'tf_background_properties',
				'type' => 'background_control',
				'label' => __( 'Background', 'themify-flow' )
			),
			'font' => array(
				'name' => 'tf_font_properties',
				'type' => 'font_control',
				'label' => __( 'Font', 'themify-flow'),
			),
			'border' => array(
				'name' => 'tf_border_properties',
				'type' => 'border_control',
				'label' => __( 'Border', 'themify-flow')
			),
			'padding' => array(
				'name' => 'tf_padding_properties',
				'type' => 'padding_control',
				'label' => __('Padding', 'themify-flow')
			),
			'margin' => array(
				'name' => 'tf_margin_properties',
				'type' => 'margin_control',
				'label' => __('Margin', 'themify-flow')
			),
			'width' => array(
				'name' => 'tf_width_properties',
				'type' => 'width_control',
				'label' => __('Width', 'themify-flow')
			),
			'height' => array(
				'name' => 'tf_height_properties',
				'type' => 'height_control',
				'label' => __('Height', 'themify-flow')
			),
			'min-width' => array(
				'name' => 'tf_min-width_properties',
				'type' => 'min-width_control',
				'label' => __('Minimum Width', 'themify-flow')
			),
			'max-width' => array(
				'name' => 'tf_max-width_properties',
				'type' => 'max-width_control',
				'label' => __('Maximum Width', 'themify-flow')
			),
			'min-height' => array(
				'name' => 'tf_min-height_properties',
				'type' => 'min-height_control',
				'label' => __('Minimum Height', 'themify-flow')
			),
			'float' => array(
				'name' => 'tf_float_properties',
				'type' => 'float_control',
				'label' => __('Float', 'themify-flow')
			),
			'opacity' => array(
				'name' => 'tf_opacity_properties',
				'type' => 'opacity_control',
				'label' => __('Opacity', 'themify-flow')
			),
			'position' => array(
				'name' => 'tf_position_properties',
				'type' => 'position_control',
				'label' => __('Position', 'themify-flow')
			),
			'z-index' => array(
				'name' => 'tf_z-index_properties',
				'type' => 'z-index_control',
				'label' => __('Z Index', 'themify-flow')
			),
		);
		if ( TF_Model::is_tf_styling_active() ) {
			$controls['customcss'] = array(
				'name' => 'tf_customcss_properties',
				'type' => 'customcss_control',
				'label' => __('Custom CSS', 'themify-flow')
			);
		}
		return apply_filters( 'tf_styling_control_sections', $controls );
	}

	public function selectors() {
		return array();
	}

	public function render_sections() {
		$out = '<ul class="tf_properties_list">';
		foreach( $this->properties() as $property => $attribute ) {
			$out .= sprintf(
				'<li id="%s_control" class="tf_properties_list_expanded tf_styling_property_%s">
					<strong class="tf_property_title %s"%s>%s</strong>
					<div class="tf_property_wrap"></div>
				</li>',
				esc_attr( $attribute['name'] ), $property,
				'customcss' === $property ? 'tf_expand_section' : 'tf_toggle_property_section',
				'customcss' === $property ? 'data-expand="customcss"' : '',
				wp_kses_data( $attribute['label'] )
			);
			$this->initialize_helpers( $attribute );
			$this->js_controls[$attribute['name']]['name'] = $attribute['name'];
			$this->js_controls[$attribute['name']]['type'] = $attribute['type'];
		}
		$out .= '</ul>';

		// Pass data for templates
		$this->js_data = array(
			'core' => array(
				'background_control' => array(
					'labels' => array(
						'image' 			=> __( 'Background Image', 'themify-flow' ),
						'repeatAll' 		=> __( 'Repeat All', 'themify-flow' ),
						'repeatHorizontal' 	=> __( 'Repeat Horizontal', 'themify-flow' ),
						'repeatVertical' 	=> __( 'Repeat Vertical', 'themify-flow' ),
						'noRepeat' 			=> __( 'No Repeat', 'themify-flow' ),
						'fullcover' 		=> __( 'Fullcover', 'themify-flow' ),
						'leftTop' 			=> __( 'Left Top', 'themify-flow' ),
						'leftCenter' 		=> __( 'Left Center', 'themify-flow' ),
						'leftBottom' 		=> __( 'Left Bottom', 'themify-flow' ),
						'rightTop' 			=> __( 'Right Top', 'themify-flow' ),
						'rightCenter' 		=> __( 'Right Center', 'themify-flow' ),
						'rightBottom' 		=> __( 'Right Bottom', 'themify-flow' ),
						'centerTop' 		=> __( 'Center Top', 'themify-flow' ),
						'centerCenter' 		=> __( 'Center Center', 'themify-flow' ),
						'centerBottom' 		=> __( 'Center Bottom', 'themify-flow' ),
						'noBackgroundImage'	=> __( 'No Background Image', 'themify-flow' ),
						'backgroundColor'	=> __( 'Background Color', 'themify-flow' ),
						'transparent'		=> __( 'Transparent', 'themify-flow' ),
					)
				),
				'padding_control' => array(
					'labels' => array(
						'padding'	 	=> __( 'Padding', 'themify-flow' ),
						'paddingTop' 	=> __( 'Padding Top', 'themify-flow' ),
						'paddingRight' 	=> __( 'Padding Right', 'themify-flow' ),
						'paddingBottom' => __( 'Padding Bottom', 'themify-flow' ),
						'paddingLeft' 	=> __( 'Padding Left', 'themify-flow' ),
						'applyToAll' 	=> __( 'Apply to all padding.', 'themify-flow' ),
					)
				),
				'margin_control' => array(
					'labels' => array(
						'margin'	 	=> __( 'Margin', 'themify-flow' ),
						'marginTop' 	=> __( 'Margin Top', 'themify-flow' ),
						'marginRight' 	=> __( 'Margin Right', 'themify-flow' ),
						'marginBottom' 	=> __( 'Margin Bottom', 'themify-flow' ),
						'marginLeft' 	=> __( 'Margin Left', 'themify-flow' ),
						'auto'	=> __( 'Auto', 'themify-flow' ),
						'applyToAll' 	=> __( 'Apply to all margin.', 'themify-flow' ),
					)
				),
				'width_control' => array(
					'labels' => array(
						'width'	=> __( 'Width', 'themify-flow' ),
						'auto'	=> __( 'Auto', 'themify-flow' ),
					)
				),
				'height_control' => array(
					'labels' => array(
						'height' => __( 'Height', 'themify-flow' ),
						'auto'	 => __( 'Auto', 'themify-flow' ),
					)
				),
				'min-width_control' => array(
					'labels' => array(
						'min-width'	=> __( 'Minimum Width', 'themify-flow' ),
					)
				),
				'max-width_control' => array(
					'labels' => array(
						'max-width'	=> __( 'Maximum Width', 'themify-flow' ),
					)
				),
				'min-height_control' => array(
					'labels' => array(
						'min-height' => __( 'Minimum Height', 'themify-flow' ),
					)
				),
				'position_control' => array(
					'labels' => array(
						'position'	=> __( 'Position', 'themify-flow' ),
						'absolute'	=> __( 'Absolute', 'themify-flow' ),
						'relative'	=> __( 'Relative', 'themify-flow' ),
						'fixed'		=> __( 'Fixed', 'themify-flow' ),
						'static'	=> __( 'Static', 'themify-flow' ),
						'top'		=> __( 'Top', 'themify-flow' ),
						'right'		=> __( 'Right', 'themify-flow' ),
						'bottom'	=> __( 'Bottom', 'themify-flow' ),
						'left'		=> __( 'Left', 'themify-flow' ),
						'auto'		=> __( 'Auto', 'themify-flow' ),
					)
				),
				'float_control' => array(
					'labels' => array(
						'float'	=> __( 'Float', 'themify-flow' ),
						'left'	=> __( 'Left', 'themify-flow' ),
						'right'	=> __( 'Right', 'themify-flow' ),
						'none'		=> __( 'None', 'themify-flow' ),
					)
				),
				'opacity_control' => array(
					'labels' => array()
				),
				'z-index_control' => array(
					'labels' => array()
				),
				'customcss_control' => array(
					'labels' => array()
				),
			),
			'controls' => $this->js_controls,
		);
		wp_localize_script( 'tf-view-styling-control-js', '_tf_styling', $this->js_data );

		return $out;
	}

	protected function initialize_helpers( $attribute ) {
		switch ( $attribute['type'] ) {
			case 'background_control':	
				ob_start();
				wp_enqueue_media();
				ob_end_clean();
			break;
			
			default:
			break;
		}
	}

	public function get_style( $module_slug ) {
		global $tf_modules;
		$module = $tf_modules->get_module( $module_slug );
		return $this->parse_style( $module->styles() );
	}

	public function parse_style( $styles ) {
		$out = array();
		if ( count( $styles ) > 0 ) {
			foreach( $styles as $key => $args ) {
				
				$defaults = array(
					'parent' => false,
					'children' => array(),
					'meta'   => array(),
				);

				// Do the same for 'meta' items.
				if ( ! empty( $defaults['meta'] ) && ! empty( $args['meta'] ) )
					$args['meta'] = wp_parse_args( $args['meta'], $defaults['meta'] );

				$args = wp_parse_args( $args, $defaults );

				if ( $args['parent'] ) {
					$out[ $args['parent'] ]['children'][ $key ] = $args;
				} else {
					unset( $args['parent'] );
					$out[ $key ] = $args;
				}

			}
		}
		return $out;
	}

	public function render_selector( $module ) {
		ob_start();
		$this->render_selector_style( $module );
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function render_selector_style( $module ) { ?>
		<?php
		if ( 'row' == $module ) {
			$tf_styling_selectors = $this->parse_style( TF_Shortcodes::row_styles() );
		} else if ( 'global' == $module ) {
			$tf_styling_selectors = $this->parse_style( $this->get_styling_global_settings() ); // param: module slug 
		} else {
			$tf_styling_selectors =  $this->get_style( $module );
		} 
		?>
		<?php if ( count( $tf_styling_selectors ) > 0 ): ?>
		<ul class="tf_elements_list">
			<?php foreach( $tf_styling_selectors as $key => $param ):
				$li_state_class = count( $param['children'] ) > 0 ? ' tf_list_has_child': '';
				$basic_styling = isset( $param['basic_styling'] ) ? ' data-tf-basic-styling="' . implode( ',', $param['basic_styling'] ) . '"' : ''; ?>
				<li class="<?php echo esc_attr( $li_state_class ); ?>">
					<?php 
					$parent_attr = isset( $param['selector'] ) && ! empty( $param['selector']) 
					? ' data-tf-style-selector="'. esc_attr( $param['selector'] ).'" data-tf-style-selector-key="'.$key.'"' 
					: '';
					$chain = isset( $param['chain_with_context'] ) && $param['chain_with_context'] ? ' data-tf-chain-with-context="chain"' : '';
					?>
					<span class="tf_element_list_title"<?php echo $parent_attr . $chain; ?><?php echo $basic_styling; ?>><?php echo $param['label'] ?></span>
					
					<?php if( count( $param['children'] ) > 0 ): ?>
					<ul>
						<?php foreach( $param['children'] as $child_key => $child_param ): ?>
						<li>
							<?php
							$parent_child_attr = isset( $child_param['selector'] ) && ! empty( $child_param['selector']) 
							? ' data-tf-style-selector="'. esc_attr( $child_param['selector'] ).'" data-tf-style-selector-key="'.$child_key.'"' 
							: '';
							$basic_styling = isset( $child_param['basic_styling'] ) ? ' data-tf-basic-styling="' . implode( ',', $child_param['basic_styling'] ) . '"' : '';
							$chain = isset( $child_param['chain_with_context'] ) && $child_param['chain_with_context'] ? ' data-tf-chain-with-context="chain"' : '';
							?>
							<span class="tf_element_list_title"<?php echo $parent_child_attr . $chain; ?><?php echo $basic_styling; ?>><?php echo $child_param['label']; ?></span>
						</li>
						<?php endforeach;?>
					</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; // count $tf_styling_selectors ?>

	<?php
	}

	public function get_bootstrap_styles( $post_id, $args = array() ) {
		$make_data = array();
		$styles = TF_Model::get_custom_styling( $post_id, $args );

		if ( ! empty( $styles ) && is_array( $styles ) ) {
			foreach( $styles as $uniqid => $setting ) {
				$temp_data = array(
					'ID' => $uniqid,
					'module' => $setting['module']
				);
				if ( isset( $setting['settings'] ) && count( $setting['settings'] ) > 0 ) {
					$temp_data['settings'] = array();
					foreach( $setting['settings'] as $selector_key => $properties ) {
						$temp_setting = array( 'SettingKey' => $selector_key );
						$temp_props = array();
						foreach( $properties as $property => $value ) {
							$temp_props[ $property ] = $value;
						}
						$temp_data['settings'][] = array_merge( $temp_setting, $temp_props );
					}
				}
				$make_data[] = $temp_data;
			}
		}

		return $make_data;
	}

	public function get_styling_global_settings() {
		return apply_filters( 'tf_styling_global_settings', array() );
	}

}

/** Initialize class */
$GLOBALS['tf_styling_control'] = new TF_Styling_Control();