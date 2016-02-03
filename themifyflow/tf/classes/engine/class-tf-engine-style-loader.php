<?php

class TF_Engine_Style_Loader {

	/** Database Design */
	/*
	tf_template_style_modules = array(
		'uuid' => array(
			'module' => 'text',
			'settings' => array(
				'module_title' => array(
					'tf_font_properties' => {},
					'tf_border_properties' => {},
				),
				'module_content' => array(
					'tf_font_properties' => {},
					'tf_border_properties' => {},
				)
			)
		)
	)

	tf_template_style_global = array(
		'header_wrap' => array(
			'tf_font_properties' => {},
			'tf_border_properties' => {},
		),
		'site_logo' => array(
			'tf_font_properties' => {},
			'tf_border_properties' => {},
		),
	)
	*/

	/**
	 * Collection of font names and variants.
	 * 
	 * @var $webfonts array
	 */
	var $web_fonts = array();

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'render_style' ), 9 );
	}

	/**
	 * Parses saved styling looking for Google Fonts references to load them.
	 * 
	 * @since 1.0.0
	 */
	public function render_style() {
		$styles = TF_Model::get_custom_styling( null, array( 
			'include_template_part' => true,
			'include_global_style' => true
		));
		if ( count( $styles ) > 0 ) {
			$this->generate_css( $styles, true );
		}
	}

	/**
	 * If Flow editor is active, generates and returns CSS based on global, template, template part and module styling. Loads Google Fonts.
	 * If Flow is not active, loads Google Fonts.
	 * 
	 * @since 1.0.0
	 * 
	 * @uses TF_Shortcodes::row_styles()
	 * @uses TF_Styling_Control::get_styling_global_settings()
	 * @uses TF_Module_Loader::get_module()
	 * @uses TF_Module::styles()
	 * @uses TF_Engine_Style_Loader::get_fonts_to_load()
	 * @uses TF_Engine_Style_Loader::build_css_rule()
	 * 
	 * @param array $styles Styles saved. Only used if Flow editor is active.
	 * @param bool $only_load_fonts Parses selectors and properties looking for references to fonts. If any is found, it's loaded.
	 * @param string $format Whether to return a continuous string or an associative array.
	 * 
	 * @return string|void Valid CSS if Flow editor is active, otherwise nothing is returned.
	 */
	public function generate_css( $styles, $only_load_fonts = false, $format = 'string' ) {
		global $tf_modules, $tf_styling_control;
		$selectors = array();
		foreach( $styles as $context => $module ) {
			if ( 'row' == $module['module'] ) {
				$selector = TF_Shortcodes::row_styles();
				$context_selector = '.tf_row_block_' . $context;
			} else if( 'global' == $module['module'] ) {
				if ( ! ( $tf_styling_control instanceof TF_Styling_Control ) ) {
					global $TF;
					include_once( $TF->framework_path() . '/classes/theme-elements/class-tf-styling-control.php' );
					$GLOBALS['tf_styling_control'] = new TF_Styling_Control();
				}
				$selector = $tf_styling_control->get_styling_global_settings();
				$context_selector = '';
			} else {
				$module_instance = $tf_modules->get_module( $module['module'] );
				if ( false !== $module_instance ) {
					$selector = $module_instance->styles();
					$context_selector = '.tf_module_block_' . $context;
				}
			}
			if ( isset( $module['settings'] ) && count( $module['settings'] ) > 0 ) {
				foreach( $module['settings'] as $style_key => $properties ) {
					if ( ! isset( $selector[ $style_key ]['selector'] ) ) continue;
					if ( $only_load_fonts ) {
						$selectors[] = array( 'properties' => $properties );
					} else {
						$chain_with_context = isset( $selector[ $style_key ]['chain_with_context'] ) && true == $selector[ $style_key ]['chain_with_context'];
						$selectors[] = array(
							'context' => $context_selector,
							'selector' => $selector[ $style_key ]['selector'],
							'chain' => $chain_with_context,
							'properties' => $properties
						);
					}
				}
			}
		}
		if ( $only_load_fonts ) {
			$this->get_fonts_to_load( $selectors );
		} else {
			return $this->build_css_rule( $selectors, $format );
		}
	}

	/**
	 * Parses the styles and extracts the fonts that must be enqueued.
	 *
	 * @since 1.0.0
	 *
	 * @uses TF_Engine_Style_Loader::maybe_load_fonts()
	 *
	 * @param array $selectors Selectors and properties to apply where there might be a reference to Google Fonts.
	 */
	public function get_fonts_to_load( $selectors ) {
		foreach( $selectors as $rule ) {
			foreach( $rule['properties'] as $property => $value ) {
				if ( 'tf_font_properties' != $property ) {
					continue;
				}
				$font = json_decode( $value );
				if ( isset( $font->family->name ) && '' != $font->family->name && isset( $font->family->fonttype ) && 'google' == $font->family->fonttype ) {
					// Add to list of fonts that will be loaded later.
					$this->web_fonts[] = $font->family;
				}
			}
		}
		$this->maybe_load_fonts();
	}

	/**
	 * Check if the font passed references a Google Font and if so, enqueue it.
	 * 
	 * @since 1.0.0
	 *
	 * @uses TF_Engine_Style_loader::web_fonts
	 */
	function maybe_load_fonts() {
		if ( ! empty( $this->web_fonts ) && is_array( $this->web_fonts ) ) {
			$the_fonts = array();
			foreach ( $this->web_fonts as $font ) {
				if ( is_array( $font->variant ) ) {
					$font->variant = implode( ',', array_filter( $font->variant, 'trim' ) );
				}
				$the_fonts[] = str_replace( ' ', '+', $font->name ) . ':' . $font->variant;
			}
			$subsets = 'latin';
			$web_fonts_setting = TF_Settings::get( 'webfonts' );
			if ( isset( $web_fonts_setting['subsets'] ) ) {
				$web_fonts_setting['subsets'] = trim( $web_fonts_setting['subsets'] );
				if ( ! empty( $web_fonts_setting['subsets'] ) ) {
					$subsets .= ',' . str_replace( ' ', '', $web_fonts_setting['subsets'] );
				}
			}
			$the_fonts = implode( '|', $the_fonts );
			wp_enqueue_style( 'tf-google-fonts', tf_https_esc( 'http://fonts.googleapis.com/css' ) . "?family=$the_fonts&subset=$subsets", array( 'flow-style' ) );
		}
	}

	/**
	 * Outputs image width and height for the logo/description image if they are available.
	 *
	 * @param string $mod_name
	 * @return string
	 */
	public function build_image_size_rule( $mod_name ) {
		$element = json_decode( $this->get_cached_mod( $mod_name ) );
		$element_props = '';
		if ( ! empty( $element->imgwidth ) ) {
			$element_props .= "\twidth: {$element->imgwidth}px;\n";
		}
		if ( ! empty( $element->imgheight ) ) {
			$element_props .= "\theight: {$element->imgheight}px;\n";
		}
		return $element_props;
	}

	/**
	 * Outputs color for the logo in text mode since it's needed for the <a>.
	 *
	 * @param string $mod_name
	 * @return string
	 */
	public function build_color_rule( $mod_name ) {
		$element = json_decode( $this->get_cached_mod( $mod_name ) );
		$element_props = '';
		if ( ! empty( $element->imgwidth ) ) {
			$element_props .= "\twidth: {$element->imgwidth}px;\n";
		}
		if ( isset( $element->color ) && '' != $element->color ) {
			$element_props .= "\tcolor: #$element->color;\n";
			if ( isset( $element->opacity ) && '' != $element->opacity && '1' != $element->opacity && '1.00' != $element->opacity ) {
				$element_props .= "\tcolor: rgba(" . $this->hex2rgb( $element->color ) . ',' . $element->opacity . ");\n";
			}
		}
		return $element_props;
	}

	/** 
	 * Build CSS rules and return in format specified.
	 * 
	 * @since 1.0.0
	 * 
	 * @param array $selectors
	 * @param string Specifies to return all style joined or separated in an associative array.
	 *
	 * @return string|array
	 */
	public function build_css_rule( $selectors, $format = 'string' ) {
		$out = '';
		$customcss = '';
		foreach( $selectors as $rule ) {
			$selectors = explode( ',', $rule['selector'] );
			$rule['selector'] = array();
			$separator = $rule['chain'] ? '' : ' ';
			foreach( $selectors as $key => $selector ) {
				$rule['selector'][] = $rule['context'] . $separator . trim( $selector );
			}
			$property = $this->build_property( $rule['properties'] );
			if ( ! empty( $property ) ) {
				$out .= sprintf( "%s {\n%s}\n", implode( ",\n", $rule['selector'] ), $property );
			}
			if ( isset( $rule['properties']['tf_customcss_properties'] ) ) {
				$customcss .= $rule['properties']['tf_customcss_properties'];
			}
		}
		$this->maybe_load_fonts();
		if ( 'string' == $format ) {
			$out .= $this->clean_custom_css( $customcss );
		} else {
			$out = array(
				'global_styling' => $out,
				'custom_css'     => $this->clean_custom_css( $customcss ),
			);
		}
		return $out;
	}

	/** 
	 * Gets raw custom CSS, cleans it, builds a proper object, and gets custom CSS from it.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $customcss JSON-formatted custom CSS.
	 *
	 * @return string
	 */
	public function clean_custom_css( $customcss ) {
		// Append Custom CSS. This is extracted from the array.
		if ( ! empty( $customcss ) ) {
			// Remove JSON stuff
			$customcss = str_replace( '{"css":"', '', $customcss );
			$customcss = str_replace( '"}', '', $customcss );

			// If it was escaped as a single quote, undo it as an unescaped double quote
			$customcss = preg_replace( '/\\\'/', '"', $customcss );
			
			// Escape backslashes, single and double quotes
			$customcss = addslashes( $customcss );
			// Remove double backslashes inside strings, cases like \e456
			$customcss = preg_replace( '/\:(\s*?)(\"|\')(\\+)(.*?)(\"|\')/', ': $2\\\\$4$5', $customcss );
			// Escape line breaks
			$customcss = str_replace( "\n", '\\n', $customcss );
			
			$customcss = str_replace( "\t", '\\t', $customcss );

			// Rebuild JSON
			$customcss = '{"css":"' . $customcss . '"}';

			$customcss = json_decode( $customcss );
			$customcss = $customcss->css . "\n";
		}
		return $customcss;
	}

	/**
	 * Check if the variant passed is a weight and returns it. Otherwise returns empty string.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $variant
	 *
	 * @return string
	 */
	function get_font_weight( $variant ) {
		$variant = str_replace( 'italic', '', $variant );
		if ( in_array( $variant, array( '100', '200', '300', '400', '500', '600', '700', '800', '900' ) ) ) {
			return $variant;
		}
		return '';
	}

	public function build_property( $properties ) {
		$out = '';
		$prefix = '';
		$suffix = '';

		foreach( $properties as $property => $value ) {
			switch ( $property ) {
				case 'tf_font_properties':
					$font = json_decode( $value );
					$font_style = '';
					$font_weight = '';
					if ( ! isset( $font->nostyle ) || '' == $font->nostyle ) {
						if ( isset( $font->italic ) && '' != $font->italic ) {
							$font_style = sprintf("\tfont-style:%s;\n", $prefix . $font->italic . $suffix );
						}
						if ( isset( $font->bold ) && '' != $font->bold ) {
							$font_weight = sprintf("\tfont-weight:%s;\n", $prefix . $font->bold . $suffix );
						}
						if ( isset( $font->underline ) && '' != $font->underline ) {
							$out .= sprintf("\ttext-decoration:%s;\n", $prefix . $font->underline . $suffix );
						} elseif ( isset( $font->linethrough ) && '' != $font->linethrough ) {
							$out .= sprintf("\ttext-decoration:%s;\n", $prefix . $font->linethrough . $suffix );
						}
					} else {
						$font_style = sprintf("\tfont-style:%s;\n", $prefix . 'normal' . $suffix );
						$font_weight = sprintf("\tfont-weight:%s;\n", $prefix . 'normal' . $suffix );
						$out .= sprintf("\ttext-decoration:%s;\n", $prefix . 'none' . $suffix );
					}
					if ( isset( $font->family->name ) && '' != $font->family->name ) {
						if (  'google' == $font->family->fonttype ) {
							// Add to list of fonts that will be loaded later.
							$this->web_fonts[] = $font->family;
							$family_name = '"' . $font->family->name . '"';
							if ( isset( $font->family->variant ) ) {
								if ( 'regular' !== $font->family->variant && '400' !== $font->family->variant ) {
									if ( false !== stripos( $font->family->variant, 'italic' ) ) {
										$font_style = sprintf("\tfont-style:%s;\n", $prefix . 'italic' . $suffix );
									}
									$variant_weight = $this->get_font_weight( $font->family->variant );
									if ( '' !== $variant_weight ) {
										$font_weight = sprintf("\tfont-weight:%s;\n", $prefix . $variant_weight . $suffix );
									}
								}
							}
						} else {
							$family_name = $font->family->name;
						}
						$out .= sprintf("\tfont-family:%s;\n", $prefix . $family_name . $suffix );
					}
					// Apply font style and weight
					$out .= $font_style . $font_weight;
					if ( isset( $font->sizenum ) && '' != $font->sizenum ) {
						$unit = isset( $font->sizeunit ) && '' != $font->sizeunit ? $font->sizeunit : 'px';
						$out .= sprintf("\tfont-size:%s;\n", $prefix . $font->sizenum . $unit . $suffix );
					}
					if ( isset( $font->linenum ) && '' != $font->linenum ) {
						$unit = isset( $font->lineunit ) && '' != $font->lineunit ? $font->lineunit : 'px';
						$out .= sprintf("\tline-height:%s;\n", $prefix . $font->linenum . $unit . $suffix );
					}
					if ( isset( $font->texttransform ) && '' != $font->texttransform ) {
						if ( isset( $font->notexttransform ) && 'notexttransform' == $font->notexttransform ) {
							$out .= sprintf("\ttext-transform:%s\n;", $prefix . 'none' . $suffix );
						} else {
							$out .= sprintf("\ttext-transform:%s;\n", $prefix . $font->texttransform . $suffix );
						}
					}
					if ( isset( $font->align ) && '' != $font->align ) {
						if ( 'noalign' != $font->align ) {
							$out .= sprintf("\ttext-align:%s;\n", $prefix . $font->align . $suffix );
						} else {
							if ( '' == is_rtl() ) {
								$out .= sprintf("\ttext-align:%s;\n", $prefix . 'left' . $suffix );
							} else {
								$out .= sprintf("\ttext-align:%s;\n", $prefix . 'right' . $suffix );
							}
						}
					}
					if ( isset( $font->color ) && '' != $font->color && 'none' != $font->color ) {
						$out .= "\tcolor: #$font->color;\n";
						if ( isset( $font->opacity ) && '' != $font->opacity && '1' != $font->opacity && '1.00' != $font->opacity ) {
							$out .= "\tcolor: rgba(" . $this->hex2rgb( $font->color ) . ',' . $font->opacity . ");\n";
						}
					}
				break;

				case 'tf_border_properties':
					$border = json_decode( $value );
					if ( isset( $border->disabled ) && 'disabled' == $border->disabled ) {
						$out .= "\tborder: none;";
					} else {
						$same = ( isset( $border->same ) && '' != $border->same ) ? 'same' : '';
						if ( '' == $same ) {
							foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
								if ( isset( $border->{$side} ) ) {
									$border_side = $border->{$side};
									$out .= $this->setBorder( $border_side, 'border-' . $side );
								}
							}
						} else {
							$out .= $this->setBorder( $border );
						}
					}
				break;

				case 'tf_background_properties':
					$bg = json_decode( $value );

					if ( isset( $bg->noimage ) && 'noimage' == $bg->noimage ) {
						$out .= "\tbackground-image: none;";
					} elseif ( isset( $bg->src ) && '' != $bg->src ) {
						$out .= sprintf("\tbackground-image: url(%s);\n", $prefix . $bg->src . $suffix );
						if ( isset( $bg->style ) && '' != $bg->style ) {
							if ( 'fullcover' == $bg->style ) {
								$out .= "\tbackground-size: cover;\n";
							} else {
								$out .= "\tbackground-repeat: {$bg->style};\n";
							}
						}
					}
					if ( isset( $bg->position ) && '' != $bg->position ) {
						$out .= "\tbackground-position: {$bg->position};\n";
					}
					if ( isset( $bg->transparent ) && '' != $bg->transparent ) {
						$out .= "\tbackground-color: $bg->transparent;\n";
					} elseif ( isset( $bg->color ) && '' != $bg->color && 'none' != $bg->color ) {
						$out .= "\tbackground-color: #$bg->color;\n";
						if ( isset( $bg->opacity ) && '' != $bg->opacity && '1' != $bg->opacity && '1.00' != $bg->opacity ) {
							$out .= "\tbackground-color: rgba(" . $this->hex2rgb( $bg->color ) . ',' . $bg->opacity . ");\n";
						}
					}
				break;

				case 'tf_padding_properties':
				case 'tf_margin_properties':
					$margin_padding = json_decode( $value );
					$style = 'tf_margin_properties' === $property ? 'margin' : 'padding';
					if ( isset( $margin_padding->same ) && '' != $margin_padding->same ) {
						if ( 'margin' == $style && isset( $margin_padding->auto ) && 'auto' == $margin_padding->auto ) {
							$out .= "\t$style: auto;\n";
						} else {
							$out .= $this->setDimension( $margin_padding, $style );
						}
					} else {
						foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
							if ( isset( $margin_padding->{$side} ) ) {
								if ( 'margin' == $style && isset( $margin_padding->{$side}->auto ) && 'auto' == $margin_padding->{$side}->auto ) {
									$out .= "\t$style-$side: auto;\n";
								} else {
									$this_side = $margin_padding->{$side};
									$out .= $this->setDimension( $this_side, $style . '-' . $side );
								}
							}
						}
					}
				break;

				case 'tf_width_properties':
				case 'tf_height_properties':
					$width_height = json_decode( $value );
					$style = 'tf_width_properties' === $property ? 'width' : 'height';
					if ( isset( $width_height->auto ) && 'auto' == $width_height->auto ) {
						$out .= "\t$style: auto;\n";
					} else {
						$out .= $this->setDimension( $width_height, $style );
					}
				break;

				case 'tf_min-width_properties':
				case 'tf_max-width_properties':
				case 'tf_min-height_properties':
					$dimension = json_decode( $value );
					$style = preg_replace( '/tf_(.*?)_properties/i', '$1', $property );
					$out .= $this->setDimension( $dimension, $style );
				break;

				case 'tf_position_properties':
					$position = json_decode( $value );
					foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
						if ( isset( $position->{$side} ) && ! empty( $position->{$side} ) ) {
							if ( isset( $position->{$side}->auto ) && 'auto' == $position->{$side}->auto ) {
								$out .= "\t$side: auto;\n";
							} else {
								$this_side = $position->{$side};
								$out .= $this->setDimension( $this_side, $side );
							}
						}
					}
					$out .= "\tposition: $position->position;\n";
				break;

				case 'tf_float_properties':
					$float = json_decode( $value );
					$out .= "\tfloat: $float->float;\n";
				break;

				case 'tf_opacity_properties':
					$opacity = json_decode( $value );
					$out .= "\topacity: ";
					$out .= absint( $opacity->opacity ) / 100;
					$out .= ";\n";
				break;

				case 'tf_z-index_properties':
					$z_index = json_decode( $value );
					$out .= "\tz-index: $z_index->zindex;\n";
				break;
			}
		}
		return $out;
	}

	/**
	 * Generate border properties.
	 *
	 * @uses hex2rgb()
	 *
	 * @param object $border Object with all the necessary values.
	 * @param string $property Property to set, can be border or border-left for example
	 * @return string
	 */
	function setBorder( $border, $property = 'border' ) {
		$out = '';
		if ( isset( $border->style ) && 'none' != $border->style ) {
			if ( '' != $border->style ) {
				$out .= "\t$property-style: $border->style;\n";
			}
			if ( isset( $border->width ) && '' != $border->width ) {
				$out .= "\t$property-width: {$border->width}px;\n";
			}
			if ( isset( $border->color ) && '' != $border->color && 'none' != $border->color ) {
				$out .= "\t$property-color: #$border->color;\n";
				if ( isset( $border->opacity ) && '' != $border->opacity && '1' != $border->opacity && '1.00' != $border->opacity ) {
					$out .= "\t$property-color: rgba(" . $this->hex2rgb( $border->color ) . ',' . $border->opacity . ");\n";
				}
			}
		} else {
			$out .= "\t$property: none;\n";
		}
		return $out;
	}

	/**
	 * Generate dimension properties for cases like padding or margin.
	 *
	 * @param object $object Object with all the necessary values.
	 * @param string $property Property to set, can be margin or padding-left for example
	 * @return string
	 */
	function setDimension( $object, $property = 'margin' ) {
		$out = '';
		if ( isset( $object->unit ) && 'px' != $object->unit ) {
			$unit = $object->unit;
		} else {
			$unit = 'px';
		}
		if ( isset( $object->width ) && '' != $object->width ) {
			$out .= "\t$property: {$object->width}$unit;\n";
		}
		return $out;
	}

	/**
	 * Converts color in hexadecimal format to RGB format.
	 *
	 * @param string $hex Color in hexadecimal format.
	 * @return string Color in RGB components separated by comma.
	 */
	function hex2rgb( $hex ) {
		$hex = str_replace( "#", "", $hex );

		if ( strlen( $hex ) == 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}
		return implode( ',', array( $r, $g, $b ) );
	}

	public function dd( $debug ) {
		echo '<pre>';
		print_r( $debug );
		echo '</pre>';
	}
}

/** Initialize class */
$GLOBALS['tf_styles'] = new TF_Engine_Style_Loader();

add_action( 'tf_import_end', array( 'TF_Model', 'create_stylesheets' ) );