<?php
/**
 * Framework Shortcodes class.
 * 
 * Register framework shortcodes.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Shortcodes {

	/**
	 * Constant self-closed shortcode.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	const SELF_CLOSED = 'self-closed';

	/**
	 * Constant enclosed shortcode.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	const ENCLOSED = 'enclosed';
	
	/**
	 * Initial.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public static function init() {
		$shortcodes = array(
			'row'         => __CLASS__ . '::row',
			'column'      => __CLASS__ . '::column',
			'sub_row'     => __CLASS__ . '::sub_row',
			'sub_column'  => __CLASS__ . '::sub_column',
			'back_row'    => __CLASS__ . '::back_row',
			'back_column' => __CLASS__ . '::back_column',
		);

		foreach( $shortcodes as $shortcode => $function ) {
			add_shortcode( 'tf_' . $shortcode, $function );
		}
	}

	/**
	 * Shortcode row
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public static function row( $atts, $content = null ) {
		global $pagenow;
		// Use wp_parse_args instead of shortcode_atts because we use custom filter.
		$atts = apply_filters( 'tf_shortcode_atts', wp_parse_args( $atts, array(
			'class'                  => '',
			'gutter'                 => 'tf_gutter_default',
                        'grid'                   => '1-col',
			'overlay_color'          => '',
			'row_anchor'             => '',
			'row_height'             => 'tf_row_height_default',
			'row_width'              => 'tf_row_width_default',
			'editable_markup'        => 'false'
		) ) );
		
		$before = '';
		$after = '';
            
		/* additional html attributes to add to the row wrapper */
		$html_atts = '';
                $classes = array_merge( 
				array( 'tf_row', $atts['gutter'], $atts['row_height'], $atts['row_width'], 'tf_row_block_' . $atts['sc_id'] ), 
				explode(' ', $atts['class'] ) 
			);
                if(!is_admin()){
                    $classes[] = 'grid_'.$atts['grid'];
                }
                if($atts['row_anchor']){
                    $classes[] = 'tf_anchor_'.$atts['row_anchor'];
                }
		$row_attrs = apply_filters( 'tf_row_attrs', array(
			'class' => $classes
		), $atts );
		$row_attrs['class'] = implode( ' ', apply_filters( 'tf_row_classes', $row_attrs['class'] ) );
		foreach( $row_attrs as $key => $value ) {
			$html_atts .= sprintf( " %s='%s'", $key, esc_attr( $value ) ); // using single quotes in case we need to store json objects in attributes
		}

		if ( isset( $atts['editable_markup'] ) && 'true' == $atts['editable_markup'] ) {
			$style_link = sprintf( '<div class="tf_styling_menu tf_row_top_item"><a href="#" title="%s" class="tf_styling_row tf-tooltips"><span class="ti-brush"></span></a></div>', __('Styling', 'themify-flow') );
			
			if ( 'backend' == TF_Model::get_current_builder_mode() ) 
				$style_link = '';

			$before = sprintf( '
				<div class="tf_row_top tf_interface">
					<div class="tf_row_top_toolbar">
						<div class="tf_row_menu">
							<div class="menu_icon"></div>
							<ul class="tf_dropdown">
								<li><a href="#" class="tf_option_row"><span class="ti-pencil"></span> %s</a></li>
								<li><a href="#" class="tf_duplicate_row"><span class="ti-layers"></span> %s</a></li>
								<li><a href="#" class="tf_delete_row"><span class="ti-close"></span> %s</a></li>
							</ul>
						</div>
						%s %s
					</div>
					<div class="toggle_row"></div>
				</div>
				<div class="tf_row_wrapper"><div class="tf_row_inner">',
				__('Options', 'themify-flow'),
				__('Duplicate', 'themify-flow'),
				__('Delete', 'themify-flow'),
				tf_grid_lists('row', $atts['gutter'] ),
				$style_link
				);
                        
			$before .= '<div class="tf_row_content">';
			$after = '</div></div></div>';

			$print_atts = $atts;
			unset( $print_atts['editable_markup'] );
			$output = sprintf( '<div data-tf-shortcode="%s" data-tf-atts="%s"%s>', 
				'tf_row', 
				esc_attr( json_encode( tf_escape_atts( $print_atts ) ) ),
				$html_atts
			);
		} else {
                       
			$before = '<div class="tf_row_wrapper"><div class="tf_row_inner">';
			$after = '</div><!-- /tf_row_inner --></div><!-- /tf_row_wrapper -->';
			$output = sprintf( '<div %s>', 
				$html_atts
			);
		}
   
		$output .= apply_filters( 'tf_row_before', $before, $atts );
		$output .= do_shortcode( $content );
		$output .= apply_filters( 'tf_row_after', $after, $atts );
		$output .= '</div><!-- /tf_row -->';

		return apply_filters( 'tf_shortcode_row', $output, $atts );
	}

	/**
	 * Shortcode column.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public static function column( $atts, $content = null ) {
		// Use wp_parse_args instead of shortcode_atts because we use custom filter.
		$atts = apply_filters( 'tf_shortcode_atts', wp_parse_args( $atts, array(
			'grid'   => '4-1'
		) ) );

		$print_classes = 'tf_col tf_col' . $atts['grid'];
		$before = '';
		$after = '';
		if ( isset( $atts['editable_markup'] ) && 'true' == $atts['editable_markup'] ) {
			$before = sprintf( '<div class="tf_module_holder"><div class="tf_empty_holder_text">%s</div>', __('Drop module here', 'themify-flow' ) );
			$after = '</div>';

			$print_atts = $atts;
			unset( $print_atts['editable_markup'] );
			$output = sprintf( '<div class="%s" data-tf-shortcode="%s" data-tf-atts="%s">', $print_classes, 'tf_column', esc_attr( json_encode( $print_atts ) ) );
		} else {
			$output = sprintf( '<div class="%s">', $print_classes );
		}

		$output .= $before;
		$output .= ! empty( $content ) ? do_shortcode( $content ) : '&nbsp;';
		$output .= $after;
		$output .= '</div><!-- /tf_col -->';

		return apply_filters( 'tf_shortcode_column', $output, $atts );
	}

	/**
	 * Shortcode sub_row.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public static function sub_row( $atts, $content = null ) {
		// Use wp_parse_args instead of shortcode_atts because we use custom filter.
		$atts = apply_filters( 'tf_shortcode_atts', wp_parse_args( $atts, array(
			'class'   => '',
			'gutter'  => 'tf_gutter_default',
                        'grid'    =>'1-col'
		) ) );

		$print_classes = array_merge( array( 'tf_sub_row', 'clearfix', $atts['gutter'] ), explode(' ', $atts['class'] ) );
                if(!is_admin()){
                    $print_classes[] = 'grid_'.$atts['grid'];
                }
		$before = '';
		$after = '';

		if ( isset( $atts['editable_markup'] ) && 'true' == $atts['editable_markup'] ) {
			$before = sprintf( '
				<div class="tf_sub_row_top">
					%s
					<ul class="sub_row_action">
						<li><a href="#" class="sub_row_duplicate"><span class="ti-layers"></span></a></li>
						<li><a href="#" class="sub_row_delete"><span class="ti-close"></span></a></li>
					</ul>
				</div>',
				tf_grid_lists('sub_row', $atts['gutter'] )
				);
			$before .= '<div class="tf_sub_row_content">';
			$after = '</div>';

			$print_atts = $atts;
			unset( $print_atts['editable_markup'] );
			$output = sprintf( '<div class="%s" data-tf-shortcode="%s" data-tf-atts="%s">', implode( ' ', $print_classes ), 'tf_sub_row', esc_attr( json_encode( $print_atts ) ) );
		} else {
			$output = sprintf( '<div class="%s">', implode( ' ', $print_classes ) );
		}
		
		$output .= $before;
		$output .= do_shortcode( $content );
		$output .= $after;
		$output .= '</div>';

		return apply_filters( 'tf_shortcode_sub_row', $output, $atts );
	}

	/**
	 * Shortcode sub_column.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public static function sub_column( $atts, $content = null ) {
		// Use wp_parse_args instead of shortcode_atts because we use custom filter.
		$atts = apply_filters( 'tf_shortcode_atts', wp_parse_args( $atts, array(
			'grid'   => '4-1'
		) ) );

		$print_classes = 'tf_col tf_col' . $atts['grid'];
		$before = '';
		$after = '';
		if ( isset( $atts['editable_markup'] ) && 'true' == $atts['editable_markup'] ) {
			$before = sprintf( '<div class="tf_module_holder"><div class="tf_empty_holder_text">%s</div>', __('Drop module here', 'themify-flow' ) );
			$after = '</div>';

			$print_atts = $atts;
			unset( $print_atts['editable_markup'] );
			$output = sprintf( '<div class="%s" data-tf-shortcode="%s" data-tf-atts="%s">', $print_classes, 'tf_sub_column', esc_attr( json_encode( $print_atts ) ) );
		} else {
			$output = sprintf( '<div class="%s">', $print_classes );
		}
		
		$output .= $before;
		$output .= ! empty( $content ) ? do_shortcode( $content ) : '&nbsp;';
		$output .= $after;
		$output .= '</div>';

		return apply_filters( 'tf_shortcode_sub_column', $output, $atts );
	}

	/**
	 * Shortcode sub_row.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public static function back_row( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'class'   => '',
			'gutter'  => 'tf_gutter_default',
                        'grid'    => '1-col'
		), $atts, 'tf_back_row' );
		
		$print_classes = array_merge( array( 'tf_back_row', 'clearfix', $atts['gutter'] ), explode(' ', $atts['class'] ) );
		
		$before = '';
		$after = '';

		if ( isset( $atts['editable_markup'] ) && 'true' == $atts['editable_markup'] ) {
			$before = sprintf( '
				<div class="tf_back_row_top">
					<div class="tf_left">
						<div class="tf_back_row_menu">
							<div class="tf_menu_icon tf_row_btn"><span class="ti-menu"></span></div>
							<ul class="tf_dropdown">
								<li><a href="#" class="tf_back_delete_row">Delete</a></li>
							</ul>
						</div>
						%s
						<!-- /tf_grid_menu -->
					</div>
					<!-- /tf_left -->
					<div class="tf_right">
						<a href="#" class="tf_row_btn tf_toggle_row"></a>
					</div>
					<!-- /tf_right -->
				</div>',
				tf_grid_lists('row', null, array('grid_menu_class' => 'tf_grid_menu', 'grid_icon_class' => 'tf_row_btn') )
				);
			$before .= '<div class="tf_back_row_content">';
			$after = '</div>';

			$print_atts = $atts;
			unset( $print_atts['editable_markup'] );
			$output = sprintf( '<div class="%s" data-tf-shortcode="%s" data-tf-atts="%s">', implode( ' ', $print_classes ), 'tf_back_row', esc_attr( json_encode( $print_atts ) ) );
		} else {
			$print_classes = array_merge( array( 'tf_row', 'clearfix', $atts['gutter'] ), explode(' ', $atts['class'] ) );
                        if(!is_admin()){
                            $print_classes[] = 'grid_'.$atts['grid'];
                        }
                        $output = sprintf( '<div class="%s">', implode( ' ', $print_classes ) );
		}
		
		$output .= $before;
		$output .= do_shortcode( $content );
		$output .= $after;
		$output .= '</div>';

		return apply_filters( 'tf_shortcode_back_row', $output, $atts );
	}

	/**
	 * Shortcode sub_column.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public static function back_column( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'grid'   => '4-1'
		), $atts, 'tf_back_column' );
		
		$print_classes = 'tf_back_col tf_col' . $atts['grid'];
		$before = '';
		$after = '';
		if ( isset( $atts['editable_markup'] ) && 'true' == $atts['editable_markup'] ) {
			$before = sprintf( '<div class="tf_module_holder"><div class="tf_empty_holder_text">%s</div>', __('Drop module here', 'themify-flow' ) );
			$after = '</div>';

			$print_atts = $atts;
			unset( $print_atts['editable_markup'] );
			$output = sprintf( '<div class="%s" data-tf-shortcode="%s" data-tf-atts="%s">', $print_classes, 'tf_back_column', esc_attr( json_encode( $print_atts ) ) );
		} else {
			$print_classes = 'tf_col tf_col' . $atts['grid'];
			$output = sprintf( '<div class="%s">', $print_classes );
		}
		
		$output .= $before;
		$output .= do_shortcode( $content );
		$output .= $after;
		$output .= '</div>';

		return apply_filters( 'tf_shortcode_back_column', $output, $atts );
	}

	/**
	 * Row option fields.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public static function row_fields() {
		return apply_filters( 'tf_row_option_fields', array(
			'gutter' => array(
				'type' => 'hidden',
				'default' => 'tf_gutter_default'
			),
			'row_width' => array(
				'type' => 'radio',
				'label' => __('Row Width', 'themify-flow'),
				'options' => array(
					array( 'name' => __('Default', 'themify-flow'), 'value' => 'tf_row_width_default', 'selected' => true),
					array( 'name' => __('Fullwidth', 'themify-flow'), 'value' => 'tf_row_full_width'),
				)
			),
			'row_height' => array(
				'type' => 'radio',
				'label' => __( 'Row Height', 'themify-flow'),
				'options' => array(
					array( 'name' => __('Default', 'themify-flow'), 'value' => 'tf_row_height_default', 'selected' => true ),
					array( 'name' => __('Fullheight (100% viewport height)', 'themify-flow'), 'value' => 'tf_row_full_height' )
				)
			),
			'separator_three' => array(
				'type' => 'separator'
			),
			'class' => array(
				'type' => 'text',
				'label' => __('Additional CSS class', 'themify-flow'),
				'class' => 'tf_input_width_80',
				'description' => '<br/>' . __('Add additional CSS class(es) for custom styling', 'themify-flow')
			),
			'row_anchor' => array(
				'type' => 'text',
				'label' => __('Row Anchor', 'themify-flow'),
				'class' => 'tf_input_width_80',
				'description' => '<br/>' . __('Example: enter &quot;about&quot; as row anchor and add &quot;#about&quot; link in navigation menu. When link is clicked, it will scroll to this row.', 'themify-flow')
			)
		) );
	}

	/**
	 * Row styles options.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public static function row_styles() {
		return apply_filters( 'tf_row_styles', array(
			'tf_row_container' => array(
				'label' => __('Row Container', 'themify-flow'),
				'selector' => '.tf_row_wrapper',
				'basic_styling' => array( 'background', 'font', 'border', 'padding', 'margin' )
			),
			'tf_row_inner_container' => array(
				'label' => __('Row Inner Container', 'themify-flow'),
				'chain_with_context' => true,
				'selector' => '.tf_row .tf_row_wrapper > .tf_row_inner, .tf_row.tf_row_full_width .tf_row_wrapper > .tf_row_inner',
				'basic_styling' => array( 'background', 'font', 'border', 'padding', 'margin' )
			),
			'tf_row_link' => array(
				'label' => __('Row Link', 'themify-flow'),
				'selector' => '.tf_row_wrapper a',
				'basic_styling' => array( 'font' )
			),
			'tf_row_link_hover' => array(
				'label' => __('Row Link Hover', 'themify-flow'),
				'selector' => '.tf_row_wrapper a:hover',
				'basic_styling' => array( 'font' )
			),
		) );
	}

	/**
	 * Return attributes to shortcode string.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public static function to_shortcode( $sc_name, $atts, $content = null, $enclosed = true ) {
		$shortcode = sprintf( '[%s', $sc_name );
		$shortcode .= count( $atts ) > 0 ? TF_Model::parse_attr( $atts ) : '';
		$shortcode .= ']';
		$shortcode .= $enclosed ? $content . sprintf( '[/%s]', $sc_name ) : '';
		return $shortcode;
	}
}