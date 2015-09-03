<?php
/**
 * Form class
 * 
 * Handle form field types
 * 
 * @package ThemifyBuilder
 * @since 1.0.0
 */
final class TF_Form {

	/**
	 * Form opening.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $args tag Form attributes
	 * @return type
	 */
	static public function open( $args ) {
		$opts = wp_parse_args( $args, array(
			'id'     => '',
			'class'  => '',
			'method' => 'POST',
			'action' => ''
		) );

		return sprintf( '<form id="%s" method="%s" action="%s" class="%s">', 
			esc_attr( $opts['id'] ), 
			esc_attr( $opts['method'] ), 
			$opts['action'], 
			esc_attr( $opts['class'] )
		);
	}

	/**
	 * Form closing.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	static public function close() {
		return '</form>';
	}

	/**
	 * Render form fields.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $fields Field properties. 
	 * @param array $exist_data Existing field values.
	 * @return string
	 */
	static public function render( $fields, $exist_data = array() ) {
		$output = '';
		foreach( $fields as $key => $field ) {
			$field = wp_parse_args( $field, array( 'row_class' => '', 'wrapper' => 'yes' ) );

			if( $field['type'] == 'multi' ) $field['row_class'] .= ' count-' . count( $field['fields'] );
			$output .= $field['type'] != 'separator' ? sprintf( '<div class="tf_lightbox_row %s tf_field_%s">', $field['row_class'], $field['type'] ) : '';
			if ( isset( $field['label'] ) ) {
				$output .= '<div class="tf_lightbox_label">'. esc_html( $field['label'] ) .'</div>';
			}
			$output .= $field['type'] != 'separator' && 'yes' == $field['wrapper'] ? '<div class="tf_lightbox_input">' : '';

			if ( $field['type'] == 'multi' ) {
				foreach( $field['fields'] as $key_multi => $field2 ) {
					$output .= '<div class="tf_lightbox_field">';
					if ( isset( $field2['label'] ) ) {
						$output .= '<div class="tf_lightbox_label">'. esc_html( $field2['label'] ) .'</div>';
					}
					$output .= self::print_field( $key_multi, $field2, $exist_data );
					$output .= '</div>';
				}
			} elseif( $field['type'] == 'repeater' ) {
				$output .= '<script type="text/html" id="repeater-' . $key . '-template" class="tf_repeater_template" data-name-pattern="'. $key .'" data-key="'. $key .'">';
				$output .= self::render( $field['fields'] );
				$output .= '</script>';
				$output .= '<ul class="tf_repeater_items" data-fields="'. implode( ',', $field['_temp'] ) .'">';
				$output .= '</ul>';
				$output .= '<a class="tf_add_row">' . ( isset( $field['new_row_text'] ) ? $field['new_row_text'] : __( 'Add New Row', 'themify-flow' ) ) . '</a>';
			} else {
				$output .= self::print_field( $key, $field, $exist_data );
			}

			$output .= '<span class="error"></span>';
			$output .= $field['type'] != 'separator' && 'yes' == $field['wrapper'] ? '</div>' : ''; // themify_builder_input
			$output .= $field['type'] != 'separator' ? '</div>' : ''; // themify_builder_field	
		}
		return $output;
	}

	/**
	 * Render form fields.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $fields Field properties. 
	 * @param array $exist_data Existing field values.
	 * @return string
	 */
	static public function render_element( $fields, $exist_data = array() ) {
		$output = '';
		foreach( $fields as $key => $field ) {
			$field = wp_parse_args( $field, array( 'wrapper' => 'yes' ) );
			
			$output .= $field['type'] != 'separator' ? '<div class="tf_back_active_module_row">' : '';
			if ( isset( $field['label'] ) ) {
				$output .= '<div class="tf_back_active_module_label"><label class="tf_lightbox_label_tag">'. esc_html( $field['label'] ) .'</label></div>';
			}
			$output .= 'yes' == $field['wrapper'] ? '<div class="tf_back_active_module_input">' : '';
			if ( $field['type'] != 'multi' ) {
				$output .= self::print_field( $key, $field, $exist_data );
			} else {
				foreach( $field['fields'] as $key_multi => $field2 ) {
				$output .= self::print_field( $key_multi, $field2, $exist_data );
				}
			}
			$output .= '<span class="error"></span>';
			$output .= 'yes' == $field['wrapper'] ? '</div>' : ''; // themify_builder_input
			$output .= $field['type'] != 'separator' ? '</div>' : ''; // themify_builder_field	
		}
		return $output;
	}

	/**
	 * Print the field based on field type.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $field_name Fieldname.
	 * @param array $field Field Properties.
	 * @param array $exist_data Exist field values.
	 * @return string
	 */
	static public function print_field( $field_name, $field, $exist_data = array() ) {
		global $wp_registered_sidebars, $wp_widget_factory;

		$field = wp_parse_args( $field, array(
			'class'   => '',
			'default' => ''
		) );
		$field_id = 'tf_field_' . $field_name; // prefix field id to avoid identic tag ID from another elements

		$output = '';
		$exist_value = isset( $exist_data[ $field_name ] ) ? $exist_data[ $field_name ] : '';

		switch ( $field['type'] ) {
			
			case 'text':
			case 'number':
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;
				$output .= sprintf( '<input type="%s" name="%s" id="%s" class="%s" value="%s">',
					esc_attr( $field['type'] ),
					esc_attr( $field_name ), 
					esc_attr( $field_id ), 
					esc_attr( $field['class'] ), 
					esc_attr( $exist_value ) 
				);
			break;

			case 'hidden':
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;
				$output .= sprintf( '<input type="hidden" name="%s" id="%s" class="%s" value="%s">', 
					esc_attr( $field_name ), 
					esc_attr( $field_id ), 
					esc_attr( $field['class'] ), 
					esc_attr( $exist_value ) 
				);
			break;

			case 'textarea':
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;
				$output .= sprintf( '<textarea id="%s" name="%s" class="%s" rows="4" cols="3">%s</textarea>', 
					esc_attr( $field_id ), 
					esc_attr( $field_name ), 
					esc_attr( $field['class'] ), 
					esc_textarea( $exist_value )
				);
			break;

			case 'select':
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;
                                $size = isset($field['size']) && intval($field['size'])>0 ?$field['size']:10;
                                $multi = isset($field['multiple']) ? 'multiple="multiple" size="'.$size.'"': '';
                                if($multi){                                                                  
                                    $field_name = $field_name.'[]';
                                    if($exist_value && !is_array($exist_value)){
                                        $exist_value = maybe_unserialize(stripslashes($exist_value));
                                    }
                                    else if(!is_array($exist_value)){
                                        $exist_value = array();
                                    }
                                    
                                }
                                $output .= sprintf( '<select name="%s" id="%s" class="%s" '.$multi.'>',
					esc_attr( $field_name ),
					esc_attr( $field_id ),
					esc_attr( $field['class'] )
				);
              
				foreach( $field['options'] as $option ) {
                                        if($multi){
                                            $selected = in_array($option['value'],$exist_value)?'selected="selected"':'';
                                        }
                                        else{
                                            $selected = selected( $exist_value, $option['value'], false );
                                        }
					$output .= sprintf( '<option value="%s"%s>%s</option>', 
						esc_attr( $option['value'] ),
						$selected,
						esc_html( $option['name'] )
					);
				}
				$output .= '</select>';
			break;

			case 'select_group':
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;
				$output .= sprintf( '<select name="%s" id="%s" class="%s">',
					esc_attr( $field_name ),
					esc_attr( $field_id ),
					esc_attr( $field['class'] )
				);
				if( isset( $field['show_empty'] ) )
					$output .= '<option value="">&nbsp;</option>';
				foreach( $field['options'] as $key => $group ) {
					$output .= sprintf( '<optgroup label="%s">', $group['label'] );
					foreach( $group['options'] as $option ) {
						$output .= sprintf( '<option value="%s"%s>%s</option>',
							esc_attr( $option['value'] ),
							selected( $exist_value, $option['value'], false ),
							esc_html( $option['name'] )
						);
					}
					$output .= '</optgroup>';
				}
				$output .= '</select>';
			break;

			case 'wp_editor':
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;
				$rows = isset( $field['rows'] ) ? $field['rows'] : 6;
				ob_start();
				wp_editor( $exist_value, $field_id, array(
					'textarea_name' => $field_name,
					'textarea_rows' => $rows,
					'editor_class' => 'tf_wp_editor ' . $field['class']
				) );
				$output .= ob_get_contents();
				ob_end_clean();
			break;

			case 'separator':
				$output .= isset( $field['meta']['html'] ) && '' != $field['meta']['html']? $field['meta']['html'] : '<hr class="meta_fields_separator" />';
			break;

			case 'image':
			case 'video': 
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;
				$input_type = 'image' == $field['type'] ? 'hidden' : 'text';
				$output .= sprintf( '<input type="%s" name="%s" id="%s" class="tf_upload_value tf_type-%s %s" value="%s">', 
					esc_attr( $input_type ),
					esc_attr( $field_name ), 
					esc_attr( $field_id ), 
					$field['type'],
					esc_attr( $field['class'] ), 
					esc_attr( $exist_value ) 
				);
				$output .= '<div class="tf_small">';
				if ( is_multisite() && !is_upload_space_available() ) {
					$output .= sprintf( __( '<p>Sorry, you have filled your %s MB storage quota so uploading has been disabled.</p>', 'themify-flow' ), get_space_allowed() );
				} else {
					$output .= sprintf( '<a href="#" class="tf_upload_media_library" data-uploader-title="%s" data-uploader-button-text="%s" data-library-type="%s">%s</a>',
							'image' == $field['type'] ? esc_attr__('Upload an Image', 'themify-flow') : esc_attr__('Upload a Video', 'themify-flow'),
							esc_attr__('Insert file URL', 'themify-flow'),
							esc_attr( $field['type'] ),
							'image' == $field['type'] ? esc_attr__( '+ Add Image', 'themify-flow') : esc_attr__( '+ Add Video', 'themify-flow') 
					);
					
					$max_upload_size = (int) wp_max_upload_size() / ( 1024 * 1024 );
					$output .= sprintf( __( '<p class="tf_max_upload_size">Maximum upload file size: %d MB.</p>', 'themify-flow' ), $max_upload_size );
				}
				$output .= '</div>';
				if ( 'image' == $field['type'] ) {
					$img_el = TF_Model::get_attachment_image( $exist_value, array( 50, 50 ) );
					$output .= sprintf( '<p class="tf_thumb_preview">
									<span class="tf_thumb_preview_placeholder">%s</span>
									<a href="#" class="tf_thumb_preview_delete"><span class="ti-close"></span></a>
								</p>', $img_el );
				}
				
			break;

			case 'color':
				$color = '' != $exist_value ? explode('_', $exist_value ) : array();
				$color_val = isset( $color[0] ) ? $color[0] : '';
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;

				$output .= '<div class="tf_custom_color_wrap">';
				$output .= sprintf( '<input type="text" class="tf_color_picker %s" value="%s">', 
					esc_attr( $field['class'] ), 
					esc_attr( $color_val ) 
				);
				$output .= sprintf( '<input type="hidden" name="%s" id="%s" class="tf_color_pick_value" value="%s">', 
					esc_attr( $field_name ), 
					esc_attr( $field_id ), 
					esc_attr( $exist_value ) 
				);
				$output .= '<a class="remove-color ti-close" href="#"></a></div>';
			break;

			case 'radio':
				$meta_attr = '';
				foreach( $field['options'] as $option ) {
					$checked = isset( $option['selected'] ) && $option['selected'] == true ? 'checked="checked"' : '';
					$checked = ( '' != $exist_value && $option['value'] == $exist_value ) ? 'checked="checked"' : $checked;
					$meta_attr = isset( $field['toggleable'] ) ? ' data-toggleable="' . $field['toggleable']['target_class'] . '-'. $option['value'] . '"' : '';

					$output .= sprintf( '<input type="radio" id="%s" class="%s" name="%s" value="%s" %s%s>', 
						esc_attr( $field_name . '_' . $option['value'] ), 
						esc_attr( $field['class'] ), 
						esc_attr( $field_name ), 
						esc_attr( $option['value'] ), 
						$checked, 
						$meta_attr
					);
					$output .= sprintf( '<label for="%s">%s</label>', 
						esc_attr( $field_name . '_' . $option['value'] ), 
						esc_html( $option['name'] )
					);
				}

			break;

			case 'checkbox':
				$checked = $exist_value || isset( $field['checked'] ) && true == $field['checked'] ? 'checked="checked"' : '';
				$output .= sprintf( '<label><input type="checkbox" name="%s" id="%s" class="%s" value="1" %s> %s</label>',
					esc_attr( $field_name ), 
					esc_attr( $field_id ), 
					esc_attr( $field['class'] ),
					$checked,
					$field['text']
				);

			break;

			case 'checkbox_group':
				$output .= sprintf( '<div class="tf_checkbox_group" data-field-name="%s">', $field_name );
				foreach( $field['options'] as $key => $value ) {
					$output .= sprintf( '<label><input type="checkbox" name="%s[%s]" id="%s" class="%s" value="1" data-key="%s"> %s</label>',
						esc_attr( $field_name ),
						$key,
						esc_attr( $field_id ), 
						esc_attr( $field['class'] ),
						$key,
						$value
					);
				}
				$output .= '</div>';

			break;

			case 'layout':

				$output .= sprintf( '<p id="%s" class="tf_option_icons tf-layout-icon">', esc_attr( $field_name ) );
				$value = '';
				foreach( $field['options'] as $option ) {
					$selected = isset( $option['selected'] ) && true == $option['selected'] ? ' selected': '';
					$value = isset( $option['selected'] ) && true == $option['selected'] && '' == $value ? $option['value'] : $value;
					
					// Check exist value
					if ( '' != $exist_value ) {
						$selected = $option['value'] == $exist_value ? ' selected' : '';
						$value = $exist_value;
					}

					$output .= sprintf( '<a href="#" data-value="%s" title="%s" class="tf-layout-option%s"><img src="%s" alt="%s" /></a>',
						esc_attr( $option['value'] ),
						esc_attr( $option['label'] ),
						$selected,
						esc_url( $option['img'] ),
						esc_attr( $option['label'] )
					);
				}
				$output .= sprintf( '<input type="hidden" name="%s" value="%s" class="val">', esc_attr( $field_name ), esc_attr( $value ) );
				$output .= '</p>';

			break;

			case 'template_assign':
				$selected = '' != $exist_value ? $exist_value : array();
				$output .= self::print_archive_tabs( $field_name, $selected );
				$output .= self::print_single_tabs( $field_name, $selected );
				$output .= self::print_page_tabs( $field_name, $selected );
			break;

			case 'template_part_select':
				$output .= sprintf( '<select name="%s" id="%s" class="%s">', 
					esc_attr( $field_name ), 
					esc_attr( $field_id ),
					esc_attr( $field['class'] ) 
				);
				$output .= '<option></option>';
				$posts = TF_Model::get_posts( 'tf_template_part' );
				foreach ( $posts as $part ) {
					$selected = selected( $exist_value, $part->post_name, false );
					$output .= sprintf( '<option value="%s" %s>%s</option>', 
						esc_attr( $part->post_name ), 
						$selected, 
						esc_html( $part->post_title )
					);
				}
				$output .= '</select><br/>';
				
				if ( ( isset( $field['show_extra_link'] ) && true == $field['show_extra_link'] ) || ! isset( $field['show_extra_link'] ) ) {
					$output .= sprintf( '<a href="%s" target="_blank" class="manage_template_part_link"><span class="tf_icon ti-folder"></span> %s</a>',
						admin_url('edit.php?post_type=tf_template_part'),
						__( 'Manage Template Part', 'themify-flow' )
					);
				}
			break;

			case 'builder':
				ob_start();
				global $TF;
				$TF->in_builder_lightbox = true;
				$exist_value = '' == $exist_value ? $field['default'] : wp_kses_stripslashes( $exist_value );
				$output .= '<div class="tf_back_builder tf_lightbox_builder_field-init" data-builder-field="'.$field_id.'">';
				TF_Interface::builder_element_panel( $field['options']['category'] );
				TF_Interface::builder_row_panel( $exist_value );
				$output .= ob_get_contents();
				$output .= '</div>';
				$TF->in_builder_lightbox = false;
				ob_end_clean();

				// Hold the builder value
				$output .= sprintf( '<input type="hidden" name="_has_builder[]" value="%s">', esc_attr( $field_name ) );
				$output .= sprintf( '<input type="hidden" name="%s" id="%s" class="tf_field_builder" value="%s">', 
					esc_attr( $field_name ), 
					esc_attr( $field_id ),  
					esc_attr( $exist_value ) 
				);
			break;

			case 'icon':
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;
				$output .= sprintf( '<input type="text" name="%s" id="%s" class="%s" value="%s">', 
					esc_attr( $field_name ), 
					esc_attr( $field_id ), 
					esc_attr( $field['class'] ), 
					esc_attr( $exist_value ) 
				);
				$output .= '<a class="button button-secondary hide-if-no-js tf_fa_toggle" href="#">' . __( 'Insert Icon', 'themify-flow' ) .'</a>';
			break;

			case 'gallery':
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;
				$output .= sprintf( '<input type="hidden" id="%s" name="%s" class="tf_gallery_value %s" value="%s">', 
					esc_attr( $field_id ), 
					esc_attr( $field_name ), 
					esc_attr( $field['class'] ), 
					esc_textarea( $exist_value )
				);

				$output .= sprintf('<a href="#" class="tf_browse_gallery_btn">%s</a>', __('+ Insert Gallery', 'themify-flow'));
				$output .= '<div class="tf_gallery_preview">'; // place outside images loop for js placeholder
				if ( $slider_images = tf_get_images_from_gallery_shortcode( $exist_value ) ) {
					foreach( $slider_images as $image ) {
						$output .= sprintf( '<p class="tf_thumb_preview">
										<span class="tf_thumb_preview_placeholder">%s</span>
									</p>', wp_get_attachment_image( $image->ID, array( 50,50 ) ) );
					}	
				}
				$output .= '</div>';

			break;

			case 'widget_area':
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;
				$output .= sprintf( '<select name="%s" id="%s" class="%s">',
					esc_attr( $field_name ),
					esc_attr( $field_id ),
					esc_attr( $field['class'] )
				);
				$output .= '<option value=""></option>';

				foreach( $wp_registered_sidebars as $k => $v ) {
					$output .= sprintf( '<option value="%s"%s>%s</option>', 
						esc_attr( $v['id'] ),
						selected( $exist_value, $v['id'], false ),
						esc_html( $v['name'] )
					);
				}
				$output .= '</select>';
			break;

			case 'widget':
				$exist_value = '' == $exist_value ? $field['default'] : $exist_value;
				$output .= sprintf( '<select name="%s" id="%s" class="tf-field-widget-select %s">',
					esc_attr( $field_name ),
					esc_attr( $field_id ),
					esc_attr( $field['class'] )
				);
				$output .= '<option value=""></option>';

				foreach( $wp_widget_factory->widgets as $class => $widget ) {
					$output .= sprintf( '<option data-idbase="%s" value="%s"%s>%s</option>', 
						esc_attr( $widget->id_base ),
						esc_attr( $class ),
						selected( $exist_value, $class, false ),
						esc_html( $widget->name )
					);
				}
				$output .= '</select>';
				$output .= '<div class="tf-widget-form"></div>';
			break;

			case 'html':
				$output .= $field['html'];
			break;

			default:
				/**
				 * Hook your own field type.
				 * 
				 * @since 1.0.0
				 */
				$output .= apply_filters( 'tf_form_fields', $field_id, $field_name, $exist_value );
			break;
		}

		$output .= isset( $field['description'] ) ? sprintf( '<small>%s</small>', $field['description'] ) : '';
		return $output;
	}

	/**
	 * Submit button.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $text Button text. 
	 * @param string $name 
	 * @return string
	 */
	static public function submit_button( $text, $name = 'submit' ) {
		return sprintf('<p class="tf_lightbox_save">
					<a class="tf_btn tf_cancel_lightbox">%s</a>
					<input type="submit" value="%s" name="%s" class="tf_btn tf_btn_save">
				</p>', __('Cancel', 'themify-flow'), esc_attr( $text ), esc_attr( $name ) );
	}

	/**
	 * Field Template Assignment Tab Archives.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $field_name Field name.
	 * @param array $selected Selected value.
	 * @return string
	 */
	static private function print_archive_tabs( $field_name, $selected = array() ) {
		$output = '<div id="visibility-tabs-archive" class="visibility-tabs ui-tabs visibility-tabs-'. $field_name .' visibility-tabs-'. $field_name .'-archive"><ul class="clearfix">';

		$taxonomies = apply_filters( 'tf_hooks_visibility_taxonomies', get_taxonomies( array( 'public' => true ) ) );
		$exclude_tax = array( 'post_format', 'product_shipping_class' );

		// Exclude unnecessary taxonomies
		foreach( $exclude_tax as $tax ) {
			if ( isset( $taxonomies[ $tax ] ) ) 
				unset( $taxonomies[ $tax ] );	
		}
		$taxonomies = array_map( 'get_taxonomy', $taxonomies );

		/* build the tab links */
		foreach( $taxonomies as $key => $tax ) {
			$output .= '<li><a href="#visibility-tab-archive-'. $key .'">' . $tax->labels->name . '</a></li>';
		}
		$output .= '<li><a href="#visibility-tab-archive-archives">' . __( 'Archives', 'themify-flow' ) . '</a></li>';
		$output .= '<li><a href="#visibility-tab-archive-taxonomies">' . __( 'Taxonomies', 'themify-flow' ) . '</a></li>';
		$output .= '</ul>';

		foreach( $taxonomies as $key => $tax ) {
			$output .= '<div id="visibility-tab-archive-'. $key .'" class="themify-visibility-options clearfix">';
			$categories = get_terms( $key, array( 'hide_empty' => true ) );

			$output .= wp_kses_post( sprintf( __( '<p><small>Check which %s where the template will be used for the %s views.</small></p>', 'themify-flow' ), $tax->labels->name, $tax->labels->singular_name ) );

			$checked = isset( $selected['archive'][ $key ]['all'] ) ? checked( $selected['archive'][ $key ]['all'], 'on', false ) : '';
			$output .= '<label class="label-full"><input class="tf_toggle_prop" type="checkbox" name="'.$field_name.'[archive]['.$key.'][all]" '. $checked .' />' . __('Apply to all', 'themify-flow') . '</label>';
			
			if ( count( $categories ) > 0 ) {
				foreach( $categories as $term ) {
					$checked = isset( $selected['archive'][ $key ][ $term->slug ] ) ? checked( $selected['archive'][ $key ][$term->slug], 'on', false ) : '';
					$output .= '<label><input type="checkbox" name="'.$field_name.'[archive]['.$key.']['. $term->slug .']" '. $checked .' />' . $term->name . '</label>';
				}
			}
			$output .= '</div>'; // tab-archives	
		}

		// Archives tab
		$output .= '<div id="visibility-tab-archive-archives" class="themify-visibility-options clearfix">';
		$archives = array(
			'is_search' => __('Search', 'themify-flow'),
			'is_date' => __('Date', 'themify-flow'),
			'is_author' => __('Author', 'themify-flow'),
			'is_year' => __('Year', 'themify-flow'),
			'is_day' => __('Day', 'themify-flow'),
			'is_month' => __('Month', 'themify-flow'),
			'is_home' => __('Latest Posts Homepage', 'themify-flow'),
		);

		$output .= wp_kses_post( __( '<p><small>Check which archive views where the template will be used. Note: if "Apply to all" is checked, it will apply to the archive views of all other custom post types as well.</small></p>', 'themify-flow' ) );

		$checked = isset( $selected['archive']['archive']['all'] ) ? checked( $selected['archive']['archive']['all'], 'on', false ) : '';
		$output .= '<label class="label-full"><input class="tf_toggle_prop" type="checkbox" name="'.$field_name.'[archive][archive][all]" '. $checked .' />' . __('Apply to all', 'themify-flow') . '</label>';

		foreach( $archives as $key => $label ) {
			$checked = isset( $selected['archive']['archive'][ $key ] ) ? checked( $selected['archive']['archive'][ $key ], 'on', false ) : '';
			$output .= '<label><input type="checkbox" name="'.$field_name.'[archive][archive]['. $key .']" '. $checked .' />' . $label . '</label>';
		}

		$post_types = apply_filters( 'tf_hooks_visibility_post_types', get_post_types( array( 'public' => true ) ) );
		unset( $post_types['page'] );
		$post_types = array_map( 'get_post_type_object', $post_types );
		foreach( $post_types as $key => $post_type ) {
			$checked = isset( $selected['archive']['archive']['post_type'][ $key ] ) ? checked( $selected['archive']['archive']['post_type'][ $key ], 'on', false ) : '';
			$output .= '<label><input type="checkbox" name="'.$field_name.'[archive][archive][post_type]['. $key .']" '. $checked .' />' . esc_html( $post_type->label ) . '</label>';
		}
		
		$output .= '</div>'; // tab-archive-archives

		// Taxonomies tab
		$output .= '<div id="visibility-tab-archive-taxonomies" class="themify-visibility-options clearfix">';
		$taxonomies = TF_Model::get_taxonomies( array(), array( 'category', 'post_tag' ) );

		$output .= wp_kses_post( __( '<p><small>Check which taxonomy archive views where the template will be used. Note: if "Apply to all" is checked, it will apply to the archive views of all other custom taxonomies as well.</small></p>', 'themify-flow' ) );

		$checked = isset( $selected['archive']['tax']['all'] ) ? checked( $selected['archive']['tax']['all'], 'on', false ) : '';
		$output .= '<label class="label-full"><input class="tf_toggle_prop" type="checkbox" name="'.$field_name.'[archive][tax][all]" '. $checked .' />' . __('Apply to all', 'themify-flow') . '</label>';

		if ( count( $taxonomies ) > 0 ) {
			foreach( $taxonomies as $key => $tax ) {
				$checked = isset( $selected['archive']['tax'][ $key ] ) ? checked( $selected['archive']['tax'][ $key ], 'on', false ) : '';
				$output .= '<label><input type="checkbox" name="'.$field_name.'[archive][tax]['. $key .']" '. $checked .' />' . $tax->label . '</label>';
			}
		}
		$output .= '</div>'; // tab-archive-taxonomies

		$output .= '</div>';
		return $output;
	}

	/**
	 * Field Template Assignment Tab Single.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $field_name Field name.
	 * @param array $selected Selected value.
	 * @return string
	 */
	static private function print_single_tabs( $field_name, $selected = array() ) {
		$output = '<div id="visibility-tabs-single" class="visibility-tabs ui-tabs visibility-tabs-'. $field_name .' visibility-tabs-'. $field_name .'-single"><ul class="clearfix">';

		/* build the tab links */
		$output .= '<li><a href="#visibility-tab-single-category">' . __( 'Categories', 'themify-flow' ) . '</a></li>';
		$output .= '<li><a href="#visibility-tab-single-post-types">' . __( 'Post Types', 'themify-flow' ) . '</a></li>';
		$output .= '<li><a href="#visibility-tab-single-singles">' . __( 'Posts', 'themify-flow' ) . '</a></li>';
		$output .= '</ul>';

		// Categories Tab
		$output .= '<div id="visibility-tab-single-category" class="themify-visibility-options clearfix">';
		$categories = get_terms( 'category', array( 'hide_empty' => true ) );

		$output .= wp_kses_post( __( '<p><small>Check which categories to apply this template to the single view of each entry filed under the category.</small></p>', 'themify-flow' ) );

		$checked = isset( $selected['single']['category']['all'] ) ? checked( $selected['single']['category']['all'], 'on', false ) : '';
		$output .= '<label class="label-full"><input class="tf_toggle_prop" type="checkbox" name="'.$field_name.'[single][category][all]" '. $checked .' />' . __('Apply to all', 'themify-flow') . '</label>';

		if ( count( $categories ) > 0 ) {
			foreach( $categories as $term ) {
				$checked = isset( $selected['single']['category'][$term->slug] ) ? checked( $selected['single']['category'][$term->slug], 'on', false ) : '';
				$output .= '<label><input type="checkbox" name="'.$field_name.'[single][category]['. $term->slug .']" '. $checked .' />' . $term->name . '</label>';
			}
		}
		$output .= '</div>'; // tab-single-category

		// Post Types tab
		$output .= '<div id="visibility-tab-single-post-types" class="themify-visibility-options clearfix">';
		$post_types = TF_Model::get_post_types( array(), array('tf_template', 'tf_template_part' ) );

		$output .= wp_kses_post( __( '<p><small>Check which post types to apply this template to the single view of each entry of their type. Note: if "Apply to all" is checked, it will apply to all single views of all other custom post types as well.</small></p>', 'themify-flow' ) );

		$checked = isset( $selected['single']['post_type']['all'] ) ? checked( $selected['single']['post_type']['all'], 'on', false ) : '';
		$output .= '<label class="label-full"><input class="tf_toggle_prop" type="checkbox" name="'.$field_name.'[single][post_type][all]" '. $checked .' />' . __('Apply to all', 'themify-flow') . '</label>';

		if ( count( $post_types ) > 0 ) {
			foreach( $post_types as $key => $type ) {
				$checked = isset( $selected['single']['post_type'][ $key ] ) ? checked( $selected['single']['post_type'][ $key ], 'on', false ) : '';
				$output .= '<label><input type="checkbox" name="'.$field_name.'[single][post_type]['. $key .']" '. $checked .' />' . $type->label . '</label>';
			}
		}
		$output .= '</div>'; // tab-single-post-types

		// Posts tab
		$output .= '<div id="visibility-tab-single-singles" class="themify-visibility-options clearfix">';
		$query_posts = get_posts( array( 
			'post_type' => array_keys( TF_Model::get_post_types( array(), array( 'page', 'tf_template', 'tf_template_part' ) ) ),
			'posts_per_page' => -1 
		) );

		$output .= wp_kses_post( __( '<p><small>Check which posts to apply this template to its single view.</small></p>', 'themify-flow' ) );

		$checked = isset( $selected['single']['singular']['all'] ) ? checked( $selected['single']['singular']['all'], 'on', false ) : '';
		$output .= '<label class="label-full"><input class="tf_toggle_prop" type="checkbox" name="'.$field_name.'[single][singular][all]" '. $checked .' />' . __('Apply to all', 'themify-flow') . '</label>';

		if ( count( $query_posts ) > 0 ) {
			foreach( $query_posts as $post_data ) {
				$checked = isset( $selected['single']['singular'][ $post_data->post_type ][ $post_data->post_name ] ) ? checked( $selected['single']['singular'][ $post_data->post_type ][ $post_data->post_name ], 'on', false ) : '';
				$output .= '<label><input type="checkbox" name="'.$field_name.'[single][singular]['. $post_data->post_type .']['.$post_data->post_name.']" '. $checked .' />' . $post_data->post_title . '</label>';
			}
		}
		$output .= '</div>'; // tab-archive-taxonomies

		$output .= '</div>';
		return $output;
	}

	/**
	 * Field Template Assignment Tab Page.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $field_name Field name.
	 * @param array $selected Selected value.
	 * @return string
	 */
	static private function print_page_tabs( $field_name, $selected = array() ) {
		$output = '<div id="visibility-tabs-page" class="ui-tabs visibility-tabs-'. $field_name .' visibility-tabs-'. $field_name .'-page">';

		// Page Tab
		$output .= '<div id="visibility-tab-page-page" class="themify-visibility-options ui-tabs-panel clearfix">';
		$key = 'page';
		$pages = get_posts( array( 'post_type' => $key, 'posts_per_page' => -1, 'status' => 'published' ) );

		$output .= wp_kses_post( __( '<p><small>Check which pages to apply this template to.</small></p>', 'themify-flow' ) );

		$checked = isset( $selected['page']['all'] ) ? checked( $selected['page']['all'], 'on', false ) : '';
		$output .= '<label class="label-full"><input class="tf_toggle_prop" type="checkbox" name="'.$field_name.'[page][all]" '. $checked .' />' . __('Apply to all', 'themify-flow') . '</label>';
		$checked = isset( $selected['page']['404'] ) ? checked( $selected['page']['404'], 'on', false ) : '';
		$output .= '<label><input type="checkbox" name="'.$field_name.'[page][404]" '. $checked .' />' . __('404', 'themify-flow') . '</label>';
		$checked = isset( $selected['page']['is_front_page'] ) ? checked( $selected['page']['is_front_page'], 'on', false ) : '';
		$output .= '<label><input type="checkbox" name="'.$field_name.'[page][is_front_page]" '. $checked .' />' . __('Static Front Page', 'themify-flow') . '</label>';

		if ( count( $pages ) > 0 ) {
			foreach( $pages as $page ) {
				$checked = isset( $selected['page'][$page->post_name] ) ? checked( $selected['page'][$page->post_name], 'on', false ) : '';
				$output .= '<label><input type="checkbox" name="'.$field_name.'[page]['. $page->post_name .']" '. $checked .' data-id="' . $page->ID . '" />' . $page->post_title . '</label>';
			}
		}
		$output .= '</div>'; // tab-single-category

		$output .= '</div>';
		return $output;
	}
}