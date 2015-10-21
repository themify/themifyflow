<?php
/**
 * Model class.
 * 
 * Handle all operation related to database query, post data query, and variable state.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Model {
	/**
	 * Check TF Theme exists
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return boolean
	 */
	public static function theme_exists() {
		global $wpdb;
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='%s' AND post_status='%s'", array( 'tf_theme', 'publish' ) );

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Template types options
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public static function template_types() {
		return apply_filters( 'tf_template_types', array(
			array( 'name' => __('Archive', 'themify-flow'), 'value' => 'archive' ),
			array( 'name' => __('Single', 'themify-flow'), 'value' => 'single' ),
			array( 'name' => __('Page', 'themify-flow'), 'value' => 'page' )
		));
	}

	/**
	 * Module category options.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public static function module_types() {
		return apply_filters( 'tf_module_types', array(
			array( 'name' => __('Global', 'themify-flow'), 'value' => 'global' ),
			array( 'name' => __('Archive', 'themify-flow'), 'value' => 'archive' ),
			array( 'name' => __('Single', 'themify-flow'), 'value' => 'single' ),
			array( 'name' => __('Page', 'themify-flow'), 'value' => 'page' ),
			array( 'name' => __('Content', 'themify-flow'), 'value' => 'content' ),
		));
	}

	/**
	 * Check TF Editor is active.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return boolean
	 */
	public static function is_tf_editor_active() {
		return isset( $_GET['tf'] ) && true == $_GET['tf'] && is_user_logged_in() && current_user_can( 'manage_options');
	}

	/**
	 * Check if global styling or Custom CSS panel are active.
	 * 
	 * @since 1.0.0
	 *
	 * @uses is_tf_styling_active_only()
	 * @uses is_tf_custom_css_active_only()
	 *
	 * @return bool
	 */
	public static function is_tf_styling_active() {
		return ( self::is_tf_styling_active_only() || self::is_tf_custom_css_active_only() );
	}
	/**
	 * Check if Custom CSS panel is active. It involves having global styling active as well.
	 * 
	 * @since 1.0.0
	 *
	 * @uses is_tf_styling_active()
	 * @uses is_tf_custom_css_active_only()
	 *
	 * @return bool
	 */
	public static function is_tf_custom_css_active() {
		return self::is_tf_styling_active() && self::is_tf_custom_css_active_only();
	}

	/**
	 * Check if global styling is active.
	 * 
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_tf_styling_active_only() {
		return isset( $_GET['tf_global_styling'] ) && true == $_GET['tf_global_styling'] && is_user_logged_in() && current_user_can( 'manage_options');
	}

	/**
	 * Check if Custom CSS panel is active.
	 * 
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_tf_custom_css_active_only() {
		return isset( $_GET['tf_custom_css'] ) && true == $_GET['tf_custom_css'] && is_user_logged_in() && current_user_can( 'manage_options');
	}

	public static function get_current_builder_mode() {
		if ( ( is_admin() && ! isset( $_POST['tf_builder_mode'] ) ) || 
			( isset( $_POST['tf_builder_mode'] ) && 'backend' == $_POST['tf_builder_mode'] ) ) {
			return 'backend';
		}
		return 'frontend';
	}

	/**
	 * Get post_meta values by fields.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $fields 
	 * @param array $post_id 
	 * @return array
	 */
	public static function get_field_exist_values( $fields, $post_id ) {
		$keys = array_keys( $fields );
		$return = array();
		foreach( $keys as $key ) {
			$value = get_post_meta( $post_id, $key, true );
			if ( ! empty( $value ) ) 
				$return[ $key ] = $value;
		}
		return $return;
	}

	/**
	 * Grouped modules by category.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public static function get_module_group_by_category() {
		global $tf_modules;
		$categories = array();
		foreach( $tf_modules->get_modules() as $module ) {
			if ( is_array( $module->category ) ) {
				foreach( $module->category as $category ) {
					$categories[ $category ][] = $module;
				}
			} else {
				$categories[ $module->category ][] = $module;
			}
		}
		return $categories;
	}

	/**
	 * Get post types object.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $args 
	 * @param array $exclude 
	 * @return array
	 */
	public static function get_post_types( $args = array(), $exclude = array() ) {
		$args = wp_parse_args( $args, array(
			'public' => true
		) );

		$post_types = get_post_types( $args );
		
		if ( count( $exclude ) > 0 ) {
			foreach( $exclude as $type ) {
				if ( isset( $post_types[ $type ] ) ) 
					unset( $post_types[ $type ] );
			}
		}

		return array_map( 'get_post_type_object', $post_types );
	}

	/**
	 * Get taxonomies.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $args 
	 * @param array $exclude 
	 * @return array
	 */
	public static function get_taxonomies( $args = array(), $exclude = array() ) {
		$args = wp_parse_args( $args, array(
			'public' => true
		) );
		$taxonomies = get_taxonomies( $args );

		if ( count( $exclude ) > 0 ) {
			foreach( $exclude as $type ) {
				if ( isset( $taxonomies[ $type ] ) ) 
					unset( $taxonomies[ $type ] );
			}
		}
		
		return array_map( 'get_taxonomy', $taxonomies );
	}

	/**
	 * Save TF Template data.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param int $post_id 
	 * @param array $post_data 
	 */
	public static function save_template( $post_id, $post_data ) {
		$post_data = wp_parse_args( $post_data, array(
			'header'  => '',
			'sidebar' => '',
			'content' => '',
			'footer'  => ''
		) );
		$post_type = get_post_type( $post_id );
		$reserved_post_types = array( 'tf_template', 'tf_template_part' );

		if ( 'tf_template' == $post_type ) {
			$meta_keys = array(
				'tf_template_region_header'  => 'header', 
				'tf_template_region_sidebar' => 'sidebar', 
				'tf_template_region_footer'  => 'footer'
			);

			foreach( $meta_keys as $key => $value ) {
				update_post_meta( $post_id, $key, $post_data[ $value ] );
			}
		}

		$post_content = self::array_to_shortcode( $post_data['content'] );
		
		// Update template data post
		if ( in_array( $post_type, $reserved_post_types ) ) {
			$update_post = array(
				'ID' => $post_id,
				'post_content' => $post_content
			);
			wp_update_post( $update_post );
		}

		do_action( 'tf_save_template', $post_id, $post_data );
	}

	/**
	 * Saves styling data.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $post_id Entry where data will be saved as post meta.
	 * @param array $data Data to generate styles.
	 *
	 * @return bool|WP_Error
	 */
	public static function save_styling( $post_id, $data ) {
		// Compile data for CSS generation
		$make_data = array();
		if ( is_array( $data ) && count( $data ) > 0 ) {
			foreach( $data as $module ) {
				$make_data[ $module['ID'] ] = array(
					'module' => $module['module']
				);
				if ( isset( $module['settings'] ) && count( $module['settings'] ) > 0 ) {
					foreach( $module['settings'] as $fields ) {
						$setting_key = '';
						foreach( $fields as $key => $val ) {
							if ( 'SettingKey' == $key ) {
								$setting_key = $val;
							} else {
								$make_data[$module['ID']]['settings'][$setting_key][$key] = stripslashes_deep( $val );
							}
						}
					}
				}
			}
		}

		if ( ! is_null( $post_id ) ) {
			// meta name: tf_template_style_modules
			/*
			[0] => Array
		        (
		            [ID] => 54ebdb9aa3064
		            [module] => text
		            [styling_context] => .tf_module_block_54ebdb9aa3064
		            [tf_active_selector] => .module-text
		            [tf_active_selector_key] => tf_module_text_container
		            [settings] => Array
	                (
	                    [0] => Array
	                    (
	                        [SettingKey] => tf_module_text_container
	                        [tf_font_properties] => {"align":"right"}
	                    )
	                )
		        )

		    [1] => Array
		        (
		            [ID] => 54ebdb9aa2215
		            [module] => text
		            [styling_context] => .tf_module_block_54ebdb9aa2215
		            [tf_active_selector] => .module-text
		            [tf_active_selector_key] => tf_module_text_container
		            [settings] => Array
	                (
	                    [0] => Array
	                    (
	                        [SettingKey] => tf_module_text_container
	                        [tf_font_properties] => {"align":"right"}
	                    )
	                )
		        )

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
			*/
			/*
			Results:
			Array
			(
			    [54ebdb9aa3064] => Array
		        (
		            [module] => text
		            [settings] => Array
	                (
	                    [tf_module_text_container] => Array
	                    (
	                        [tf_font_properties] => stdClass Object
	                        (
	                            [align] => right
	                        )
	                    )
	                )
		        )
			    [54ebdb9aa2215] => Array
		        (
		            [module] => text
		            [settings] => Array
	                (
	                    [tf_module_text_container] => Array
	                    (
	                        [tf_font_properties] => stdClass Object
	                        (
	                            [align] => right
	                        )
	                    )
	                )
		        )
			)
			*/
			update_post_meta( $post_id, 'tf_template_style_modules', $make_data );
			$atomic = self::create_atomic_stylesheet( $post_id );
		} else {
			global $TF;

			if ( isset( $make_data['global']['settings']['body_main']['tf_customcss_properties'] ) ) {
				// Sanitize custom CSS
				$customcss = str_replace( '{"css":"', '', $make_data['global']['settings']['body_main']['tf_customcss_properties'] );
				$customcss = str_replace( '"}', '', $customcss );

				// If it was escaped as a single quote, undo it as an unescaped double quote
				$customcss = preg_replace( '/\\\'/', '"', $customcss );
				
				// Escape backslashes, single and double quotes
				$customcss = addslashes( $customcss );

				// Remove double backslashes inside strings, cases like \e456
				$customcss = preg_replace( '/\:(\s*?)(\"|\')(\\+)(.*?)(\"|\')/', ': $2\\\\$4$5', $customcss );

				// Restore the now safe custom CSS
				$make_data['global']['settings']['body_main']['tf_customcss_properties'] = '{"css":"' . $customcss . '"}';
			}

			update_post_meta( $TF->active_theme->theme_id, 'tf_theme_style_global', $make_data );
			$atomic = true;
		}

		// Generate CSS and create stylesheets.
		$global = self::create_stylesheets();

		// There was some error writing one of the stylesheets.
		if ( ! $global || ! $atomic ) {
			$error = new WP_Error();
			if ( ! $global ) {
				$error->add( 'file_global_write_error', __( 'Global stylesheet could not be created.', 'themify-flow' ) );
			}
			if ( ! $atomic ) {
				$error->add( 'file_atomic_write_error', __( 'Atomic stylesheet could not be created.', 'themify-flow' ) );
			}
			return $error;
		}

		// All good.
		return true;
	}

	/**
	 * Generates a stylesheet based in post, page or other custom post type styling.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $post_id Entry to fetch styles from.
	 * 
	 * @return bool
	 */
	public static function create_atomic_stylesheet( $post_id ) {
		if ( ! in_array( get_post_type( $post_id ), array( 'tf_theme', 'tf_template', 'tf_template_part' ) ) ) {
			global $tf_styles;
			$css_to_save = '';
			$content_style = $tf_styles->generate_css( self::get_custom_styling( $post_id, array( 
				'include_template_part' => false,
				'include_global_style'  => false,
				'include_module_style'  => true,
			) ) );
			if ( ! empty( $content_style ) ) {
				$css_to_save = "\n/* Builder Content Styling */\n$content_style";
			}
			if ( ! empty( $css_to_save ) ) {
				return self::write_stylesheet( self::get_atomic_stylesheet( 'bydir', $post_id ), $css_to_save, 'atomic' );
			}
		}
		return true; // nothing to write, but ends ok.
	}

	/**
	 * Generates a stylesheet based in global styling, custom css, templates and template parts.
	 * 
	 * @since 1.0.0
	 * 
	 * @return bool
	 */
	public static function create_stylesheets( $theme_id = 0 ) {
		global $tf_styles;
		$css_to_save = '';

		if ( 0 != $theme_id ) {
			$global_style = $tf_styles->generate_css( get_post_meta( $theme_id, 'tf_theme_style_global', true ), false, 'array' );
		} else {
			// Global
			$global_style = $tf_styles->generate_css( self::get_custom_styling( null, array( 
				'include_template_part' => false,
				'include_global_style'  => true,
				'include_module_style'  => false,
			) ), false, 'array' );
		}
		if ( isset( $global_style['global_styling'] ) && ! empty( $global_style['global_styling'] ) ) {
			$css_to_save .= "/* Global Styling */\n{$global_style['global_styling']}";
		}
		$module_style = '';
		foreach ( self::get_all_templates_and_parts() as $id ) {
			$module_style .= $tf_styles->generate_css( self::get_custom_styling( $id, array( 
				'include_template_part' => false, // set to false since self::get_all_templates_and_parts() return template part ids
				'include_global_style'  => false,
				'include_module_style'  => true,
			) ) );
		}
		if ( ! empty( $module_style ) ) {
			$css_to_save .= "\n/* Module Styling */\n$module_style";
		}
		if ( isset( $global_style['custom_css'] ) && ! empty( $global_style['custom_css'] ) ) {
			$css_to_save .= "/* Custom CSS */\n{$global_style['custom_css']}";
		}
		if ( ! empty( $css_to_save ) ) {
			return self::write_stylesheet( self::get_global_stylesheet( 'bydir' ), $css_to_save );
		}
		return true; // nothing to write, but ends ok.
	}

	/** 
	 * Write stylesheet file.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $css_file Server path where stylesheet will be written.
	 * @param string $css_to_save CSS to write to stylesheet.
	 * @param string $type Whether this is a global stylesheet or an atomic one.
	 * 
	 * @return bool
	 */
	public static function write_stylesheet( $css_file, $css_to_save, $type = 'global' ) {
		// Load WP Filesystem
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		WP_Filesystem();
		global $wp_filesystem;

		if ( $wp_filesystem->is_file( $css_file ) ) {
			$wp_filesystem->delete( $css_file );
		}
		if ( $wp_filesystem->put_contents( $css_file, $css_to_save, FS_CHMOD_FILE ) ) {
			update_option( "tf_stylesheet_{$type}_timestamp", current_time( 'y.m.d.H.i.s' ) );
			return true;
		}
		return false;
	}

	/**
	 * Return all templates and template parts related to the current theme.
	 * 
	 * @since 1.0.0
	 * 
	 * @return array
	 */
	public static function get_all_templates_and_parts() {

		$templates = false;
		//$templates = get_transient( 'tf_cached_all_templates_and_parts' );
		if ( false === $templates ) {

			global $TF;
			
			$all_data = array();
			
			$args = array(
				'post_type' => 'tf_template',
				'posts_per_page' => -1,
				'order' => 'DESC',
				'meta_query' => array(
					array(
						'key'   => 'associated_theme',
						'value' => $TF->active_theme->slug,
					)
				)
			);
			$query = new WP_Query( $args );
			$templates = $query->get_posts();

			if ( $templates ) {
				foreach( $templates as $key => $template ) {
					// Collect template ID
					$all_data[] = $template->ID;
				}
			}

			$args = array(
				'post_type' => 'tf_template_part',
				'posts_per_page' => -1,
				'order' => 'DESC',
				'meta_query' => array(
					array(
						'key'   => 'associated_theme',
						'value' => $TF->active_theme->slug,
					)
				)
			);
			$query = new WP_Query( $args );
			$templates = $query->get_posts();

			if ( $templates ) {
				foreach( $templates as $key => $template ) {
					// Collect template part ID
					$all_data[] = $template->ID;
				}
			}
			//set_transient( 'tf_cached_all_templates_and_parts', $all_data, 0 ); // no expired
		}

		return array_unique( $all_data );
	}

	/**
	 * Read Template Part settings data.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param int $post_id 
	 * @return array
	 */
	public static function read_template_data( $post_id ) {
		$return = array();
		$post_type = get_post_type( $post_id );

		if ( 'tf_template' == $post_type ) {
			$meta_keys = array(
				'tf_template_region_header'  => 'header', 
				'tf_template_region_sidebar' => 'sidebar', 
				'tf_template_region_footer'  => 'footer'
			);

			foreach ( $meta_keys as $key => $data ) {
				$return[ $data ] = get_post_meta( $post_id, $key, true );
			}
		}

		// get content
		$content = get_post( $post_id );
		if ( $content ) 
			$return['content'] = $content->post_content;

		return $return;
	}

	/**
	 * Read builder utility settings.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public static function read_utility_data() {
		$return = array(
			'current_edit_module' => '',
			'gutterClass' => self::get_grid_settings( 'gutter_class' ),
			'drop_module_text' => __('drop module here', 'themify-flow')
		);
		return $return;
	}

	/**
	 * Get shortcode attribute value by attribute name.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $shortcode 
	 * @param string $key 
	 * @return string
	 */
	public static function get_shortcode_atts_val( $shortcode, $key ) {
		$shortcode_atts = shortcode_parse_atts( $shortcode );
		if ( is_array( $shortcode_atts ) && isset( $shortcode_atts[$key] ) ) {
			return str_replace( array( '"', ']' ), '', $shortcode_atts[$key] );
		}
		$shortcode = isset( $shortcode_atts[1] ) ? $shortcode_atts[1] : '';
		parse_str( $shortcode, $output_sc );
		$shortcode = isset( $output_sc[ $key ] ) ? str_replace('"', '', stripslashes( $output_sc[ $key ] ) ) : '';
		$shortcode = str_replace( ']', '', $shortcode );
		return $shortcode;
	}

	/**
	 * Check if Template part exists.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $slug 
	 * @return boolean
	 */
	public static function template_part_exists( $slug ) {
		global $wpdb;
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='tf_template_part' AND post_status='publish' AND post_name='%s'", array( $slug ) );

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if template editable.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return boolean
	 */
	public static function is_template_editable() {
		global $TF_Layout;
		return current_user_can('edit_post', $TF_Layout->layout_id );
	}

	/**
	 * Get Template Part Title.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $slug 
	 * @return string
	 */
	public static function get_template_part_title( $slug ) {
		global $wpdb;
		$sql = $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type='tf_template_part' AND post_status='publish' AND post_name='%s' LIMIT 1", array( $slug ) );
		$title = $wpdb->get_var( $sql );
		return $title;
	}

	/**
	 * Get template region meta value.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param int $template_id 
	 * @param string $region 
	 * @return string
	 */
	public static function get_template_region_meta( $template_id, $region ) {
		global $TF;
		$meta = get_post_meta( $template_id, 'tf_template_region_' . $region, true );
		if ( ! empty( $meta ) ) {
			return sprintf( '[tf_template_part slug="%s"]', $meta );
		}

		$slug = $TF->active_theme->slug . '-' . $region;
		$return = '';
		$template_part = self::template_part_exists( $slug );
		if ( $template_part ) {
			$return = sprintf( '[tf_template_part slug="%s"]', $slug );
		}
		return $return;
	}

	/**
	 * Get Column Grid Settings.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public static function get_grid_settings( $setting = 'grid') {
		global $TF;
		
		$path = $TF->framework_uri() . '/assets/img/builder/';
		$grid_lists = array(
			array(
				// Grid FullWidth
				array( 'img' => $path . '1-col.png', 'data' => array( 'fullwidth') ),
				// Grid 2
				array( 'img' => $path . '2-col.png', 'data' => array( '4-2', '4-2' ) ),
				// Grid 3
				array( 'img' => $path . '3-col.png', 'data' => array( '3-1', '3-1', '3-1' ) ),
				// Grid 4
				array( 'img' => $path . '4-col.png', 'data' => array( '4-1', '4-1', '4-1', '4-1') ),
				// Grid 5
				array( 'img' => $path . '5-col.png', 'data' => array( '5-1', '5-1', '5-1', '5-1', '5-1' ) ),
				// Grid 6
				array( 'img' => $path . '6-col.png', 'data' => array( '6-1', '6-1', '6-1', '6-1', '6-1', '6-1' ) )
			),
			array(
				array( 'img' => $path . '1.4_3.4.png', 'data' => array( '4-1', '4-3' ) ),
				array( 'img' => $path . '1.4_1.4_2.4.png', 'data' => array( '4-1', '4-1', '4-2' ) ),
				array( 'img' => $path . '1.4_2.4_1.4.png', 'data' => array( '4-1', '4-2', '4-1') ),
				array( 'img' => $path . '2.4_1.4_1.4.png', 'data' => array( '4-2', '4-1', '4-1' ) ),
				array( 'img' => $path . '3.4_1.4.png', 'data' => array( '4-3', '4-1' ) )
			),
			array(
				array( 'img' => $path . '2.3_1.3.png', 'data' => array( '3-2', '3-1' ) ),
				array( 'img' => $path . '1.3_2.3.png', 'data' => array( '3-1', '3-2' ) )
			)
		);

		$gutters = array(
			array( 'name' => __('Default', 'themify-flow'), 'value' => 'tf_gutter_default' ),
			array( 'name' => __('Narrow', 'themify-flow'), 'value' => 'tf_gutter_narrow' ),
			array( 'name' => __('None', 'themify-flow'), 'value' => 'tf_gutter_none' ),
		);

		if ( 'grid' == $setting ) {
			return $grid_lists;
		} elseif( 'gutter_class' == $setting ) {
			$guiterClass = array();
			foreach( $gutters as $g ) {
				array_push( $guiterClass, $g['value'] );
			}
			return implode( ' ', $guiterClass );
		} else {
			return $gutters;
		}
	}

	/**
	 * Convert builder data array to shortcode string.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $data 
	 * @return string
	 */
	static public function array_to_shortcode( $data ) {
		global $tf_modules, $tf_module_elements;

		$shortcode = '';
		if ( is_array( $data ) ) {
			foreach( $data as $d ) {
				$test = array();
				$enclosed = true;
				if ( 'module' == $d['shortcode'] ) {
					$module_instance = $tf_modules->get_module( $d['module_name'] );
					$d['shortcode'] = $module_instance->shortcode;

					if ( ! $module_instance->has_shortcode_content() ) 
						$enclosed = false;
				} else if ( 'element' == $d['shortcode'] ) {
					$module_instance = $tf_module_elements->get_element( $d['module_name'] );
					$d['shortcode'] = $module_instance->shortcode;

					if ( $module_instance->get_close_type() != TF_Shortcodes::ENCLOSED ) 
						$enclosed = false;
				}

				array_push( $test, $d['shortcode'] );
				
				if ( isset( $d['params'] ) ) 
					array_push( $test, self::parse_array_to_str( $d['params'] ) );
				
				$shortcode .= '[' .implode( ' ', $test ) . ']';
				$shortcode .= isset( $d['content'] ) ? self::array_to_shortcode( $d['content'] ) : '';
				$shortcode .= $enclosed ? '[/'. $d['shortcode'].']' : '';
			}
		} else {
			$shortcode = $data;
		}
		return $shortcode;
	}

	/**
	 * Parse array to string parameter.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $array 
	 * @return string
	 */
	static public function parse_array_to_str( $array ) {
		$return = array();
		if ( ! is_array( $array ) ) return '';

		foreach( $array as $k => $v ) {
			$str = $k . '=' . '"' . htmlentities( $v, ENT_QUOTES, 'UTF-8' ) . '"';
			array_push( $return, $str );
		}
		return implode( ' ', $return );
	}

	/**
	 * Get template part edit url.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $shortcode 
	 * @return string
	 */
	public static function get_template_part_edit_url( $shortcode, $additional_query_arg = array() ) {
		global $wpdb;

		$slug = self::get_shortcode_atts_val( $shortcode, 'slug' );
		$sql = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='tf_template_part' AND post_status='publish' AND post_name='%s' LIMIT 1", array( $slug ) );
		$id = $wpdb->get_var( $sql );
		$query_args = array( 'tf' => true, 'iframe' => true );
		$query_args = array_merge( $query_args, $additional_query_arg );

		return add_query_arg( $query_args, get_permalink( $id ) );
	}

	/**
	 * Custom query post type using $wpdb object.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $field 
	 * @param string $post_type 
	 * @param string $slug 
	 * @param string $post_status 
	 * @return string|int|boolean
	 */
	public static function get_post_type_query( $field, $post_type, $slug, $post_status = 'publish' ) {
		global $wpdb;
		$post_status_query = is_null( $post_status ) ? '' : " AND post_status='". $post_status ."'";
		$sql = $wpdb->prepare( "SELECT ". $field ." FROM $wpdb->posts WHERE post_type='%s'". $post_status_query ." AND post_name='%s' LIMIT 1", 
			$post_type, $slug );
		return $wpdb->get_var( $sql );
	}

	/**
	 * Get attachment image by post_name.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $slug 
	 * @param string $size 
	 * @param boolean $icon 
	 * @param string $attr 
	 * @return string
	 */
	public static function get_attachment_image( $slug, $size = 'thumbnail', $icon = false, $attr = '' ) {
		$attachment_id = self::get_post_type_query( 'ID', 'attachment', $slug, null );
		$img = '';
		if ( $attachment_id ) 
			$img = wp_get_attachment_image( $attachment_id, $size, $icon, $attr );
		return $img;
	}

	/**
	 * Get attachment image url by post_name.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $slug 
	 * @return string
	 */
	public static function get_attachment_url( $slug ) {
		$attachment_id = self::get_post_type_query( 'ID', 'attachment', $slug, null );
		$url = '';
		if ( $attachment_id ) 
			$url = wp_get_attachment_url( $attachment_id );
		return $url;
	}

	/**
	 * Returns a list of web safe fonts
	 * @param bool $only_names Whether to return only the array keys or the values as well
	 * @return mixed|void
	 * @since 1.0.0
	 */
	public static function get_web_safe_font_list($only_names = false) {
		$web_safe_font_names = array(
			'Arial, Helvetica, sans-serif',
			'Verdana, Geneva, sans-serif',
			'Georgia, \'Times New Roman\', Times, serif',
			'\'Times New Roman\', Times, serif',
			'Tahoma, Geneva, sans-serif',
			'\'Trebuchet MS\', Arial, Helvetica, sans-serif',
			'Palatino, \'Palatino Linotype\', \'Book Antiqua\', serif',
			'\'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif'
		);

		if( ! $only_names ) {
			$web_safe_fonts = array(
				array('value' => 'default', 'name' => '', 'selected' => true),
				array('value' => '', 'name' => '--- '.__('Web Safe Fonts', 'themify-flow').' ---')
			);
			foreach( $web_safe_font_names as $font ) {
				$web_safe_fonts[] = array(
					'value' => $font,
					'name' => str_replace( '\'', '"', $font )
				);
			}
		} else {
			$web_safe_fonts = $web_safe_font_names;
		}

		return apply_filters( 'tf_get_web_safe_font_list', $web_safe_fonts );
	}
        
        /**
        * Return file to use depending if user selected Recommended or Full list in theme settings.
        *
        * @since 2.1.7
        *
        * @return string
        */
       public static function get_google_fonts_file() {
               $web_fonts = TF_Settings::get('webfonts');
               $font_type = isset($web_fonts['list'])?$web_fonts['list']:false;;

               global $TF;
               if ( 'full' == $font_type) {
                       $fonts = $TF->framework_path() . '/assets/js/google-fonts.json';
               } else {
                       $fonts = $TF->framework_path() . '/assets/js/google-fonts-recommended.json';
               }

               return apply_filters( 'tf_google_fonts_file', $fonts );
       }
        
	/**
	 * Get google font lists
	 * @return array
	 */
	public static function get_google_font_lists() {
		$fonts = self::grab_remote_google_fonts();
		return $fonts;
	}

	/**
	 * Grab google fonts lists from api
	 * @return array
	 */
	public static function grab_remote_google_fonts() {
		$web_fonts = TF_Settings::get('webfonts');
		$subsets = isset($web_fonts['subsets'])?$web_fonts['subsets']:false;
		$fonts_file_path = self::get_google_fonts_file();
		if($subsets && '' != $subsets) {
			$user_subsets = explode(',', str_replace(' ', '', $subsets));
		} else {
			$user_subsets = array();
		} 
		$subsets = apply_filters('tf_google_fonts_subsets', array_merge(array('latin'), $user_subsets));
		$subsets_count = count($subsets);
		if ( isset( $GLOBALS['google_fonts_json_file_cache'] ) && '' != $GLOBALS['google_fonts_json_file_cache'] ) {
			$response = $GLOBALS['google_fonts_json_file_cache'];
		} else {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
			global $wp_filesystem;
			$response = $wp_filesystem->get_contents( $fonts_file_path );
			if ( false === $response ) {
				$response = call_user_func( 'file_get_contents', $fonts_file_path );
				if ( false === $response ) {
					ob_start();
					include_once $fonts_file_path;
					$response = ob_get_contents();
					ob_end_clean();
				}
			}
			$GLOBALS['google_fonts_json_file_cache'] = $response;
		}
		$fonts = array();
		if( $response !== false ) {
			if ( isset( $GLOBALS['google_fonts_json_decode_cache'] ) && '' != $GLOBALS['google_fonts_json_decode_cache'] ) {
				$results = $GLOBALS['google_fonts_json_decode_cache'];
			} else {
				$results = json_decode( $response );
				$GLOBALS['google_fonts_json_decode_cache'] = $results;
			}
			foreach ( $results->items as $font ) {
                // If user specified additional subsets
				if( $subsets_count > 1) {
					$font_subsets = $font->subsets;
					$subsets_match = true;
                        // Check that all specified subsets are available in this font
					foreach ($subsets as $subset) {
						if(!in_array($subset, $font_subsets)) {
							$subsets_match = false;
						}
					}
                        // Ok, this font supports all subsets requested by user, add it to the list
					if($subsets_match) {
						$fonts[] = array(
							'family' => $font->family,
							'variant' => $font->variants
							);
					}
				} else {
					$fonts[] = array(
						'family' => $font->family,
						'variant' => $font->variants
						);
				}
			}
		}
		return $fonts;
	}

	/**
	 * Generate unique block id.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public static function generate_block_id() {
		return uniqid();
	}

	/**
	 * Get all lists of builder + module shortcodes.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public static function list_builder_shortcodes() {
		global $tf_modules;
		$static_sc = array( 'tf_column', 'tf_sub_column', 'tf_row', 'tf_sub_row' );
		$module_sc = $tf_modules->get_module_shortcodes();
		return array_merge( $static_sc, $module_sc );
	}

	/**
	 * Get builder close url.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public static function get_builder_close_url() {
		// check if has referer url
		if ( isset( $_GET['tf_source_uri'] ) && ! empty( $_GET['tf_source_uri'] ) ) 
			return esc_url( $_GET['tf_source_uri'] );
		
		return esc_url( remove_query_arg( 'tf' ) );
	}

	/**
	 * Get current browser url.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public static function get_current_browser_url() {
		global $wp;
		return home_url( add_query_arg( array(), $wp->request ) );
		//return esc_url( add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) ) );
	}

	/**
	 * Get all related template and template parts ids based on specific theme.
	 * 
	 * @since 1.0.0
	 * @param int $theme_id 
	 * @param string $theme_slug 
	 * @return array
	 */
	public static function get_theme_data_post_ids( $theme_id, $theme_slug ) {
		$return = array( $theme_id );
		$post_types = array( 'tf_template', 'tf_template_part');

		foreach ( $post_types as $post_type ) {
			// Get all template data
			$args = array(
				'post_type' => $post_type,
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key'     => 'associated_theme',
						'value' => $theme_slug
					)
				)
			);
			$query = new WP_Query( $args );
			$data = $query->get_posts();

			if ( $data ) {
				foreach( $data as $template ) {
					array_push( $return, $template->ID );
				}
			}
		}

		// Include the post thumbnail attachment post type
		if ( has_post_thumbnail( $theme_id ) ) 
			array_push( $return, get_post_thumbnail_id( $theme_id ) );

		return $return;

	}

	/**
	 * Determine if a post exists based on title, content, and date
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Post Name
	 * @param string $content Optional post content
	 * @param string $date Optional post date
	 * @return int Post ID if post exists, 0 otherwise.
	 */
	public static function post_exists( $slug, $content = '', $date = '' ) {
		global $wpdb, $TF;

		$post_name = wp_unslash( sanitize_post_field( 'post_name', $slug, 0, 'db' ) );

		$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
		$args = array();

		if ( !empty ( $slug ) ) {
			$query .= ' AND post_name = %s';
			$args[] = $post_name;
		}

		if ( !empty ( $args ) )
			return (int) $wpdb->get_var( $wpdb->prepare($query, $args) );

		return 0;
	}

	/**
	 * Get theme prefix from slug string.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $slug 
	 * @return string
	 */
	public static function get_theme_prefix_slug( $slug ) {
		$prefix = explode( '-', $slug );
		if ( isset( $prefix[0] ) )
			return $prefix[0];
		return 'base'; // default theme prefix
	}

	/**
	 * Remove theme prefix from slug.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $slug 
	 * @return string
	 */
	public static function remove_theme_prefix_slug( $slug ) {
		$prefix = explode( '-', $slug );
		if ( isset( $prefix[0] ) ) 
			unset( $prefix[0] );
		return implode( '-', $prefix );
	}

	/**
	 * Replace theme prefix string with current theme prefix.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $slug 
	 * @return string
	 */
	public static function replace_theme_prefix_slug( $slug, $replace_slug = null ) {
		global $TF;
		if ( is_null( $replace_slug ) ) {
			$replace_slug = $TF->active_theme->slug;
		}
		$prefix = explode( '-', $slug );
		if ( isset( $prefix[0] ) ) 
			$prefix[0] = $replace_slug;
		return implode( '-', $prefix );
	}

	/**
	 * Check if current given ID TF Theme is activated.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param int $post_id 
	 * @return boolean
	 */
	public static function is_theme_activate( $post_id ) {
		$active = get_post_meta( $post_id, 'tf_active_theme', true );
		if ( ! empty( $active ) && 'true' == $active ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Manipulate post count in wp admin list table.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $what 
	 * @param array $views 
	 * @return array
	 */
	public static function manipulate_views_count( $what, $views ) {
		global $user_ID, $wp_query;

		$total = $wp_query->post_count;
		$publish = $wp_query->post_count;

		$views['all'] = preg_replace( '/\(.+\)/U', '('.$total.')', $views['all'] ); 
		if ( isset( $views['publish'] ) ) {
			$views['publish'] = preg_replace( '/\(.+\)/U', '('.$publish.')', $views['publish'] );  
		}

		return $views;
	}

	/**
	 * Get custom styling panel data.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param int $layout_id 
	 * @return array
	 */
	public static function get_custom_styling( $layout_id = null, $args = array() ) {
		global $TF_Layout, $TF;
		$styles = array();
		$layout_id = is_null( $layout_id ) && is_object( $TF_Layout ) ? $TF_Layout->layout_id : $layout_id;
		
		$args = wp_parse_args( $args, array(
			'include_template_part' => false,
			'include_global_style'  => false,
			'include_module_style'  => true,
		) );

		if ( $args['include_global_style'] ) {
			$global_styles = get_post_meta( $TF->active_theme->theme_id, 'tf_theme_style_global', true );
			// object(TF_Engine_Theme_Loader)#237 (4) { ["name"]=> string(6) "Basico" ["slug"]=> string(6) "basico" ["theme_id"]=> int(4348) ["theme_founded":protected]=> bool(true) }
			if ( ! empty( $global_styles ) && is_array( $global_styles ) ) {
				$styles = array_merge( $styles, $global_styles );
			}
		}

		if ( $args[ 'include_module_style'] ) {
			$module_styles = get_post_meta( $layout_id, 'tf_template_style_modules', true );
			if ( ! empty( $module_styles ) && is_array( $module_styles ) ) {
				$styles = array_merge( $styles, $module_styles );
			}
			$module_styles = get_post_meta( get_the_ID(), 'tf_template_style_modules', true );
			if ( ! empty( $module_styles ) && is_array( $module_styles ) ) {
				$styles = array_merge( $styles, $module_styles );
			}
		}
		
		if ( $args['include_template_part'] ) {
			$template_part = array( 'header', 'sidebar', 'footer' );
			foreach( $template_part as $part ) {
				if ( ! empty( $TF_Layout->{'region_' . $part} ) ) {
					$template_slug = self::get_shortcode_atts_val( $TF_Layout->{'region_' . $part}, 'slug' );
					$template_styles = get_post_meta( self::get_post_type_query('ID', 'tf_template_part', $template_slug), 'tf_template_style_modules', true );
					if ( ! empty( $template_styles ) && is_array( $template_styles ) ) {
						$styles = array_merge( $styles, $template_styles );
					}
				}
			}
		}

		return $styles;
	}

	/**
	 * Return the URL or the directory path for the global styling stylesheet.
	 * 
	 * @since 1.0.0
	 *
	 * @param string $mode Whether to return the directory or the URL. Can be 'bydir' or 'byurl' correspondingly. 
	 *
	 * @return string
	 */
	public static function get_global_stylesheet( $mode = 'bydir' ) {
		global $TF;

		static $before;
		if ( ! isset( $before ) ) {
			$upload_dir = wp_upload_dir();
			$before = array(
				'bydir' => $upload_dir['basedir'],
				'byurl' => $upload_dir['baseurl'],
			);
		}

		if ( isset( $TF->active_theme ) && isset( $TF->active_theme->slug ) ) {
			$file_name = "tf-{$TF->active_theme->slug}-generated.css";
		} else {
			$file_name = 'tf-generated.css';
		}
		$stylesheet = "$before[$mode]/$file_name";

		/**
		 * Filters the return URL or directory path including the file name.
		 *
		 * @param string $stylesheet Path or URL for the global styling stylesheet.
		 * @param string $mode What was being retrieved, 'bydir' or 'byurl'.
		 *
		 */
		return apply_filters( 'tf_get_global_stylesheet', $stylesheet, $mode );
	}

	/**
	 * Return the URL or the directory path for a template, template part or content builder styling stylesheet.
	 * 
	 * @since 1.0.0
	 *
	 * @param string $mode Whether to return the directory or the URL. Can be 'bydir' or 'byurl' correspondingly. 
	 * @param int $atom ID of template, template part or content builder that we're working with. If it's null, uses the one in global $TF_Layout.
	 *
	 * @return string
	 */
	public static function get_atomic_stylesheet( $mode = 'bydir', $atom = null ) {
		static $before;
		if ( ! isset( $before ) ) {
			$upload_dir = wp_upload_dir();
			$before = array(
				'bydir' => $upload_dir['basedir'],
				'byurl' => $upload_dir['baseurl'],
			);
		}

		if ( is_null( $atom ) ) {
			global $TF_Layout;
			$atom = $TF_Layout->layout_id;
		}
		
		$atom = is_int( $atom ) ? get_post( $atom ) : get_page_by_path( $atom, OBJECT, 'tf_template_part' );
		if ( ! is_object( $atom ) ) {
			return '';
		}
		$atom = $atom->post_name;

		$stylesheet = "$before[$mode]/tf-$atom-generated.css";

		/**
		 * Filters the return URL or directory path including the file name.
		 *
		 * @param string $stylesheet Path or URL for the global styling stylesheet.
		 * @param string $mode What was being retrieved, 'bydir' or 'byurl'.
		 * @param int $atom ID of the template, template part or content builder that we're fetching.
		 *
		 */
		return apply_filters( 'tf_get_atomic_stylesheet', $stylesheet, $mode, $atom );
	}

	/** 
	 * Checks whether a file exists, can be loaded and is not empty.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $file_path Path in server to the file to check.
	 * 
	 * @return bool
	 */
	public static function is_readable_and_not_empty( $file_path = '' ) {
		if ( empty( $file_path ) ) {
			return false;
		}
		return is_readable( $file_path ) && 0 !== filesize( $file_path );
	}

	/**
	 * Return the field content shortcode.
	 * 
	 * @since 1.0.0
	 * @param array $arrays 
	 * @return string|boolean
	 */
	public static function get_shortcode_content_field( $arrays ) {
		foreach( $arrays as $key => $value ) {
			if ( isset( $value['set_as_content'] ) && 'true' == $value['set_as_content'] ) {
				return $key;
			}
		}
		return false;
	}

	/**
	 * Set active theme.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param int $id 
	 * @param boolean $set
	 */
	public static function set_active_theme( $id, $set = true ) {
		// Activate theme
		if ( $set ) {
			update_post_meta( $id, 'tf_active_theme', 'true' );
		} else {
			delete_post_meta( $id, 'tf_active_theme' );
		}
	}

	/**
	 * Check is current page is template page.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return boolean
	 */
	public static function is_template_page() {
		return is_singular( array( 'tf_template', 'tf_template_part' ) ) || defined( 'DOING_AJAX' );
	}

	/**
	 * Check is current page is content builder page.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return boolean
	 */
	public static function is_content_builder_page() {
		return ( is_singular() && ! is_singular( array( 'tf_template', 'tf_template_part' ) ) ) || defined( 'DOING_AJAX' );
	}

	/**
	 * Return attributes array to shortcode params string.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $attributes 
	 * @return string
	 */
	public static function parse_attr( $attributes ) {
		if ( is_string( $attributes ) ) {
			return ( ! empty( $attributes ) ) ? ' ' . trim( $attributes ) : '';
		}

		if ( is_array( $attributes ) ) {
			$attr = '';

			foreach ( $attributes as $key => $val ) {
				$attr .= ' ' . $key . '="' . $val . '"';
			}

			return $attr;
		}
	}

	/**
	 * Get individual style settings.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param int $layout_id 
	 * @param string $sc_id 
	 * @param string $type 
	 * @return array | boolean
	 */
	public static function get_shortcode_style( $layout_id, $sc_id, $type = 'module' ) {
		$styles = get_post_meta( $layout_id, 'tf_template_style_modules', true );
		if ( ! empty( $styles ) && is_array( $styles ) && isset( $styles[ $sc_id ] ) ) {
			return $styles[ $sc_id ];
		} else {
			return false;
		}
	}

	/**
	 * Get list of TF Themes.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $themes 
	 * @return array
	 */
	public static function prepare_themes_for_js( $themes = null ) {
		global $TF;

		$current_theme = $TF->active_theme->slug;

		/**
		 * Filter theme data before it is prepared for JavaScript.
		 *
		 * Passing a non-empty array will result in prepare_themes_for_js() returning
		 * early with that value instead.
		 *
		 * @since 1.0.0
		 *
		 * @param array      $prepared_themes An associative array of theme data. Default empty array.
		 * @param null|array $themes          An array of tf_theme objects to prepare, if any.
		 * @param string     $current_theme   The current theme slug.
		 */
		$prepared_themes = (array) apply_filters( 'pre_prepare_tf_themes_for_js', array(), $themes, $current_theme );

		if ( ! empty( $prepared_themes ) ) {
			return $prepared_themes;
		}

		// Make sure the current theme is listed first.
		$prepared_themes[ $current_theme ] = array();

		if ( null === $themes ) {
			$args = array(
				'post_type' => 'tf_theme',
				'posts_per_page' => -1,
				'order' => 'DESC'
			);
			$query = new WP_Query( $args );
			$themes = $query->get_posts();
		}

		$updates = array();
		$parents = array();

		foreach ( $themes as $theme ) {
			$slug = $theme->post_name;
			$encoded_slug = urlencode( $slug );
			$metadata = get_post_meta( $theme->ID, 'theme_info', true );

			$parent = false;
			$prepared_themes[ $slug ] = array(
				'id'           => $slug,
				'theme_id'     => $theme->ID,
				'name'         => $theme->post_title,
				'screenshot'   => array( self::get_attachment_url( $metadata['tf_theme_screenshot'] ) ), // @todo multiple
				'description'  => $metadata['tf_theme_description'],
				'author'       => $metadata['tf_theme_author'],
				'authorAndUri' => sprintf( '<a href="%s">%s</a>', $metadata['tf_theme_author_link'], $metadata['tf_theme_author'] ),
				'version'      => $metadata['tf_theme_version'],
				'tags'         => '',
				'parent'       => $parent,
				'active'       => $slug === $current_theme,
				'hasUpdate'    => isset( $updates[ $slug ] ),
				'update'       => false,
				'actions'      => array(
					'activate' => wp_nonce_url( admin_url( 'post.php?post=' . $theme->ID . '&action=activate_tf_theme' ), 'tf_theme_nonce' ),
					'export'   => wp_nonce_url( admin_url( 'post.php?post=' . $theme->ID . '&action=export_tf_theme' ), 'export_tf_nonce' ),
					'delete'   => wp_nonce_url( admin_url( 'post.php?post=' . $theme->ID . '&action=delete_tf_theme' ), 'tf_theme_delete_nonce' )
				),
			);
		}

		// Remove 'delete' action if theme has an active child
		if ( ! empty( $parents ) && array_key_exists( $current_theme, $parents ) ) {
			unset( $prepared_themes[ $parents[ $current_theme ] ]['actions']['delete'] );
		}

		/**
		 * Filter the themes prepared for JavaScript.
		 *
		 * Could be useful for changing the order, which is by name by default.
		 *
		 * @since 1.0.0
		 *
		 * @param array $prepared_themes Array of themes.
		 */
		$prepared_themes = apply_filters( 'tf_prepare_themes_for_js', $prepared_themes );
		$prepared_themes = array_values( $prepared_themes );
		return array_filter( $prepared_themes );
	}

	/**
	 * Query Posts function.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $post_type 
	 * @param array $args 
	 * @return array
	 */
	public static function get_posts( $post_type, $args = array() ) {
		global $TF;
		$args = wp_parse_args( $args, array(
			'post_type' => $post_type,
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'associated_theme',
					'value' => $TF->active_theme->slug,
				)
			)
		) );
		$query = new WP_Query( $args );
		return $query->get_posts();
	}

	/**
	 * Checks if a variable passed in the URL exists and if it's equal to some value.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $var Key to check in $_GET array.
	 * @param string $val If it's not empty, variable is compared to this.
	 * 
	 * @return bool
	 */
	public static function check_url_var( $var, $val = '' ) {
		if ( isset( $_GET[$var] ) ) {
			if ( ! empty( $val ) ) {
				return $val == $_GET[$var];
			}
			return true;
		}
		return false;
	}

	/**
	 * Find attachments post IDs from shortcode builder in each post IDs.
	 * 
	 * @since 1.0.0
	 * @param array $ids 
	 * @return array
	 */
	public static function find_attachment_ids_from_posts( $ids ) {
		$attach_ids = array();

		if ( is_array( $ids ) ) {
			foreach( $ids as $id ) {
				$data = get_post( $id );
				$content = in_array( $data->post_type, array( 'tf_template', 'tf_template_part', 'tf_theme' ) ) 
							? $data->post_content : 
							get_post_meta( $data->ID, 'tf_builder_content', true );

				$post_attach_ids = self::get_post_attachment_ids( $content );
				if ( count( $post_attach_ids ) > 0 ) 
					$attach_ids = array_merge( $attach_ids, $post_attach_ids );
			}
		}

		return array_unique( array_merge( $ids, $attach_ids ) );
	}

	/**
	 * Get all attachments IDs from shortcode content builder.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $content 
	 * @return array
	 */
	public static function get_post_attachment_ids( $content ) {
		$return = array();
		if ( false === strpos( $content, '[' ) ) {
			return $return;
		}

		preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER );
		if ( empty( $matches ) )
			return $return;

		foreach ( $matches as $shortcode ) {

			$atts = shortcode_parse_atts( $shortcode[3] );
			
			if ( in_array( $shortcode[2], array('tf_image', 'tf_site_logo' ) ) ) {
				
				$fields = array( 'image_url', 'logo_image' );
				foreach( $fields as $field ) {
					if ( isset( $atts[ $field ] ) && ! empty( $atts[ $field ] ) ) {
						$attachment = get_page_by_path( $atts[ $field ] );
						if ( is_object( $attachment ) ) {
							array_push( $return, $attachment->ID );
						}
					}
				}
			}

			if ( ! empty( $shortcode[5] ) ) {
				$merge = self::get_post_attachment_ids( $shortcode[5], $shortcode[2] );
				if ( count( $merge ) > 0 ) {
					$return = array_unique( array_merge( $return, $merge ) );
				}
			}
		}
		return $return;
	}

	/**
	 * Returns style for preview
	 * 
	 * @since 1.0.0
	 */
	public static function get_style_preview( $theme_id = 0 ) {
		global $tf_styles;
		$css = '';
		if ( 0 != $theme_id ) {
			$global_style = $tf_styles->generate_css( get_post_meta( $theme_id, 'tf_theme_style_global', true ), false, 'array' );
		} else {
			// Global
			$global_style = $tf_styles->generate_css( self::get_custom_styling( null, array( 
				'include_template_part' => false,
				'include_global_style'  => true,
				'include_module_style'  => false,
			) ), false, 'array' );
		}
		
		// Duplicates are first removed from module style data
		$module_style_data = array();
		foreach ( self::get_all_templates_and_parts() as $id ) {
			$module_style_data[] = self::get_custom_styling( $id, array( 
				'include_template_part' => false, // set to false since self::get_all_templates_and_parts() return template part ids
				'include_global_style'  => false,
				'include_module_style'  => true,
			) );
		}
		$render = array();
		foreach ( $module_style_data as $maybe_duplicated_style ) {
			foreach ( array_unique( array_keys( $maybe_duplicated_style ) ) as $unikey ) {
				if ( isset( $maybe_duplicated_style[$unikey] ) ) {
					$render[$unikey] = $maybe_duplicated_style[$unikey];
				}
			}
		}
		// Once module style data is clean, generate CSS
		$module_style = '';
		foreach ( $render as $unikey => $unique_styling ) {
			$module_style .= $tf_styles->generate_css( array( $unikey => $unique_styling ) );
		}

		// Save global and module styling in the same style tag which is modified with JS
		if ( ! empty( $global_style['global_styling'] ) || ! empty( $module_style ) ) {
			$css .= '<style id="tf-style-preview">';
			if ( ! empty( $global_style['global_styling'] ) ) {
				$css .= "/* Global Styling */\n{$global_style['global_styling']}";
			}
			if ( ! empty( $module_style ) ) {
				$css .= "/* Module Styling */\n$module_style";
			}
			$css .= '</style>';
		}

		// Save custom CSS in a separate style tag which is replaced when user types new CSS
		if ( isset( $global_style['custom_css'] ) && ! empty( $global_style['custom_css'] ) ) {
			$css .= "\n<style id=\"themify-custom-css\">\n{$global_style['custom_css']}</style>";
		}
		return $css;
	}

}