<?php
/**
 * Frontend class.
 * 
 * Handle operation on frontend page and Builder engine.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Frontend {
	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 11 );

		add_filter( 'tf_module_content', 'wptexturize'        );
		add_filter( 'tf_module_content', 'convert_smilies'    );
		add_filter( 'tf_module_content', 'convert_chars'      );
		add_filter( 'tf_module_content', 'wpautop'            );
		add_filter( 'tf_module_content', 'shortcode_unautop'  );
		add_filter( 'tf_module_content', 'do_shortcode'  	  );

		add_action( 'init', array( $this, 'setup_editor' ) );

		add_filter( 'body_class', array( $this, 'body_class'), 10 );

		// Template part choose lightbox
		add_action( 'tf_lightbox_render_form_region_template_part', array( $this, 'render_template_part_region_form' ) );
		add_action( 'tf_builder_form_validate_region_template_part', array( $this, 'validate_region_fields' ) );
		add_action( 'tf_builder_form_saving_region_template_part', array( $this, 'saving_region_form' ) );

		// Module action
		add_action( 'tf_lightbox_render_form_module', array( $this, 'render_module_form' ) );
		add_action( 'tf_builder_form_saving_module', array( $this, 'save_module_form' ) );
                add_action('tf_modules_loaded',array('TF_Template_Options','get_instance'));
		// Row Option
		add_action( 'tf_lightbox_render_form_row_option', array( $this, 'render_row_option_form' ) );
		add_action( 'tf_builder_form_saving_row', array( $this, 'save_row_form' ) );

		// Frontend Menu
		add_action( 'wp_footer', array( $this, 'frontend_menu') );
	}

	function setup_editor() {
		if ( TF_Model::is_tf_editor_active() ) {
			add_action( 'wp_footer', array( $this, 'render_module_panel' ) );
			add_action( 'wp_footer', array( $this, 'render_javascript_tmpl' ) );
			add_action( 'wp_footer', array( $this, 'render_preview_style' ) );
		}
	}

	/** 
	 * Styles are loaded in preview in the page so they can be modified with JS.
	 * If we need to clear a style, the rule and/or property will be deleted from the inline style.
	 * 
	 * @since 1.0.0
	 */
	function render_preview_style() {
		$css = '';
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		WP_Filesystem();
		global $wp_filesystem;

		// Get stylesheet based in global styling, templates, template parts and custom CSS.
		$css = TF_Model::get_style_preview();

		if ( ! empty( $css ) ) {
			echo $css;
		}
	}

	/**
	 * Enqueue scripts and styles
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_scripts() {
		global $TF, $TF_Layout;
                $framework_url = $TF->framework_uri();
                $version = $TF->get_version();
		if ( TF_Model::is_tf_editor_active() ) {
			wp_enqueue_style( 'tf-icons', $framework_url . '/assets/css/themify-icons/themify-icons.css', array(), $version );
			wp_enqueue_style( 'tf-admin-ui', $framework_url . '/assets/css/tf-admin.css', array(), $version );
			wp_enqueue_style( 'tf-minicolors-css', $framework_url . '/assets/css/jquery.minicolors.css', array(), $version );
		}
                else{
                     wp_enqueue_script('themify-scroll', $framework_url . '/assets/js/themify.scroll-highlight.js' , array( 'jquery' ), $version, true );
                }

		/* FontAwesome library
		 * uses the same handle as used by Themify Builder to prevent double loading the fonts */
		wp_enqueue_style( 'tf-icon-font', $framework_url . '/assets/css/fontawesome/css/font-awesome.min.css', array(), '4.3' );

		if ( ! TF_Model::is_tf_editor_active() ) {
			// Load styling stylesheets. Doesn't use framework_uri() because it's placed in the theme root.

			// Enqueue stylesheet based in global styling, templates, template parts and custom CSS.
			if ( TF_Model::is_readable_and_not_empty( TF_Model::get_global_stylesheet( 'bydir' ) ) ) {
				wp_enqueue_style( 'tf-global-styling', TF_Model::get_global_stylesheet( 'byurl' ), array(), get_option( 'tf_stylesheet_global_timestamp' ) );
			}
			// Enqueue atomic stylesheet based in builder content in this entry. 
			if ( is_singular() ) {
				if ( TF_Model::is_readable_and_not_empty( TF_Model::get_atomic_stylesheet( 'bydir', get_the_ID() ) ) ) {
					wp_enqueue_style( 'tf-atomic-styling', TF_Model::get_atomic_stylesheet( 'byurl', get_the_ID() ), array(), get_option( 'tf_stylesheet_atomic_timestamp' ) );
				}
			}
		}
	
		if ( TF_Model::is_tf_editor_active() ) {

			$load_depend_scripts = array(
				'underscore',
				'backbone',
				'wp-util',
				'jquery-ui-core',
				'jquery-ui-tabs',
				'jquery-ui-droppable', 
				'jquery-ui-sortable',
				'media-upload',
				'jquery-ui-dialog',
				'wpdialogs',
				'wpdialogs-popup',
				'wplink',
				'editor',
				'quicktags',
			);

			// Include jQuery UI Touch
			if ( wp_is_mobile() ) 
				array_push( $load_depend_scripts, 'jquery-touch-punch' );
			
			$load_vendor_scripts = array(
				'tf-nicescroll-js' => '/jquery.nicescroll.js',
				'tf-minicolors-js' => '/jquery.minicolors.js',
				'tf-tipsy-js'      => '/jquery.tipsy.js'
			);

			$load_app_scripts = array(
				'tf-app-js' => '/tf.js',
				'tf-util-js' => '/utils.js',
				'tf-utility-js' => '/models/utility.js',
				'tf-model-template-js' => '/models/template.js',
				'tf-model-style-js' => '/models/style.js',
				'tf-model-control-js' => '/models/control.js',
				'tf-model-element-style-js' => '/models/elementstyle.js',
				'tf-collection-control-js' => '/collections/controls.js',
				'tf-collection-element-styles-js' => '/collections/elementstyles.js',
				'tf-view-lightbox-js' => '/views/lightbox.js',
				'tf-view-loader-js' => '/views/loader.js',
				'tf-view-template-part-js' => '/views/templatepart.js',
				'tf-mixins-builder-js' => '/mixins/builder.js',
				'tf-view-builder-js' => '/views/builder.js',
				'tf-view-builder-element-js' => '/views/builderelement.js',
				'tf-mixins-styling-control-js' => '/mixins/stylingcontrolfield.js',
				'tf-view-styling-control-js' => '/views/stylingcontrol.js',
				'tf-setup-js' => '/setup.js'
			);

			if( function_exists( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}

			foreach( $load_depend_scripts as $script ) {
				wp_enqueue_script( $script );
			}

			foreach( $load_vendor_scripts as $handle => $script ) {
				wp_enqueue_script( $handle, $framework_url . '/assets/js/vendor' . $script , array( 'jquery' ), $version, true );
			}

			foreach( $load_app_scripts as $handle => $script ) {
				wp_enqueue_script( $handle, $framework_url . '/assets/js/tf' . $script , array( 'jquery' ), $version, true );	
				if ( 'tf-app-js' == $handle ) {
					$region = isset( $_GET['tf_region'] ) ? $_GET['tf_region'] : '';
					$parent_template_id = isset( $_GET['parent_template_id'] ) ? $_GET['parent_template_id'] : '';
					wp_localize_script( 'tf-app-js', '_tf_app', array( 
						'post_id' => get_the_ID(),
						'template_part_delete' => __('Are you sure to remove this template part?', 'themify-flow' ),
						'module_delete' => __('Are you sure to delete this module?', 'themify-flow' ),
						'sub_row_delete' => __('Are you sure to delete this sub row?', 'themify-flow' ),
						'row_delete' => __('Are you sure to delete this row?', 'themify-flow' ),
						'drop_module_text' => __('drop module here', 'themify-flow' ),
						'row_option_title' => __('Row Options', 'themify-flow'),
						'region' => $region,
						'template_type' => $TF_Layout->type,
						'layout_id' => $TF_Layout->layout_id,
						'parent_template_id' => $parent_template_id,
						'nonce' => wp_create_nonce( 'tf_nonce' ),
						'is_global_styling' => TF_Model::is_tf_styling_active(),
						'is_custom_css' => TF_Model::is_tf_custom_css_active(),
						'clear_style_text' => __('Do you want to clear all Styling data in this panel?', 'themify-flow'),
						'base_path' => $TF->framework_path(),
						'base_uri' => $framework_url,
						'beforeunload' => __( 'You have unsaved changes. Please save the builder data.', 'themify-flow'),
						'isTouch' => wp_is_mobile(),
						'leaving_template_lightbox' => __('Close editor without saving the changes ?', 'themify-flow')
					) );
					
					$plupload_settings = $TF->get_plupload_settings();
					wp_localize_script( 'tf-app-js', '_tf_app_plupload', $plupload_settings );
				}
			}
		}
	}

	/**
	 * Render javascript _.underscore template
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function render_javascript_tmpl() {
		global $TF;
		include_once( sprintf( '%s/includes/tmpl/tmpl-backend.php', $TF->framework_path() ) );
		include_once( sprintf( '%s/includes/tmpl/tmpl-styling-panel.php', $TF->framework_path() ) );
	}

	/**
	 * Render module panel.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function render_module_panel() {
		global $TF;
		include_once( sprintf( '%s/includes/templates/template-module-panel.php', $TF->framework_path() ) );
	}

	/**
	 * Filter body classes
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $classes 
	 * @return array
	 */
	public function body_class( $classes ) {
		global $TF, $TF_Layout;

		$classes[] = 'tf_theme-' . $TF->active_theme->slug;
		$classes[] = wp_is_mobile() ? 'touch' : 'no-touch';

		if ( TF_Model::is_tf_editor_active() ) {
			$classes[] = 'frontend tf_active tf_admin';
		}

		if ( TF_Model::is_tf_custom_css_active() ) {
			$classes[] = 'tf_custom_css_active';
		}

		if ( TF_Model::is_tf_custom_css_active_only() ) {
			$classes[] = 'tf_custom_css_active_only';
		}

		if ( !empty( $TF_Layout->layout_name ) ) {
			$classes[] = 'tf_template tf_template_' . $TF_Layout->layout_name;
		}
                $responsive = TF_Settings::get('disable_responsive');
                if($responsive && checked($responsive,'on',false)){
                    $classes[] = 'tf_responsive_disabled';
                }
		// return the $classes array
		return $classes;
	}

	/**
	 * Region Template Part setting fields.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @return array
	 */
	protected function region_fields() {
		return apply_filters( 'tf_template_part_region_fields', array(
			'tf_template_part_shortcode' => array(
				'type'  => 'template_part_select',
				'label' => __('Select Template Part')
			)
		) );
	}

	/**
	 * Fires to render Template Part Region Form
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $method Form state add|edit|delete
	 */
	public function render_template_part_region_form( $method ) {
		$label_submit_btn = 'add' == $method ? __('Add', 'themify-flow') : __('Update', 'themify-flow');
		$data = array();
		$template_id = $_POST['template_id'];

		if ( 'edit' == $method ) {
			$data[ 'tf_template_part_shortcode'] = sanitize_text_field( $_POST['slug'] );
		}

		echo TF_Form::open( array( 'id' => 'tf_template_part_region_form' ) );
		echo sprintf( '<input type="hidden" name="_state" value="%s">', $method );
		echo sprintf( '<input type="hidden" name="_post_id" value="%s">', $template_id );
		echo sprintf( '<input type="hidden" name="_target_region" value="%s">', $_POST['region'] );
		echo TF_Form::render( $this->region_fields(), $data );
		echo TF_Form::submit_button( $label_submit_btn );
		echo TF_Form::close();
	}

	/**
	 * Validate Template Part form.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $fields 
	 * @return json
	 */
	public function validate_region_fields( $fields ) {
		
		$validator = new TF_Validator( $fields, apply_filters( 'tf_builder_form_validate_region_template_part_field_rules', array(
			'tf_template_part_shortcode' => array( 'rule' => 'notEmpty', 'error_msg' => __('You should select template part', 'themify-flow') )
		)));

		if ( $validator->fails() ) {
			wp_send_json_error( $validator->get_error_messages() );
		}

	}

	/**
	 * Fires to save Template Part form.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $post_data Form submit data.
	 * @return json
	 */
	public function saving_region_form( $post_data ) {
		global $tf_editor_ui;
		$slug = sanitize_text_field( $post_data['tf_template_part_shortcode'] );
		$shortcode = sprintf( '[%s slug="%s"]', 'tf_template_part', $slug );
		$edit_url = TF_Model::get_template_part_edit_url( $shortcode, array( 'tf_region' => $post_data['_target_region'] ) );

		//$css = TF_Model::get_style_preview();
		global $TF, $tf_styles;
		$atom = get_page_by_path( $slug, OBJECT, 'tf_template_part' );
		
		if ( is_object( $atom ) ) {
			$css = $tf_styles->generate_css( TF_Model::get_custom_styling( $atom->ID, array( 
				'include_template_part' => true,
				'include_global_style'  => false,
				'include_module_style'  => true,
			) ) );
		} else {
			$css = '';
		}
		
		$tf_editor_ui->force_read_shortcode();
		
		$data = array(
			'region'    => $post_data['_target_region'],
			'html'      => do_shortcode( $shortcode ),
			'shortcode' => $shortcode,
			'edit_url'  => $edit_url,
			'slug'      => $slug,
			'caption'   => sprintf( __('Template Part: %s', 'themify-flow'), TF_Model::get_template_part_title( $slug ) ),
			'styles'	=> $css,
		);
		wp_send_json_success( $data );
	}

	/**
	 * Fires to render module add/edit form.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $method Form state method (add|edit|delete)
	 */
	public function render_module_form( $method ) {
		global $tf_modules;

		$label_submit_btn = 'add' == $method ? __('Add', 'themify-flow') : __('Update', 'themify-flow');
		$data = array();
		$module = isset( $_POST['module'] ) ? $_POST['module'] : '';
		$module_instance = $tf_modules->get_module( $module );
		$atts = isset( $_POST['shortcode_params'] ) ? $_POST['shortcode_params'] : array();
		$content = isset( $_POST['shortcode_content'] ) ? stripslashes( $_POST['shortcode_content'] ) : '';
		$shortcode_id = isset( $atts['sc_id'] ) ? $atts['sc_id'] : TF_Model::generate_block_id();
		$template_id = isset( $_POST['template_id'] ) ? $_POST['template_id'] : 0;

		if ( $module_instance === false ) return;

		if ( 'edit' == $method ) {
			if ( ! empty( $content ) ) {
				$atts['content'] = $content;
			}
			$data = $atts;
		}

		if ( get_magic_quotes_gpc() ) {
			$data = stripslashes_deep( $data );
		}

		echo TF_Form::open( array( 'id' => 'tf_module_form' ) );
		echo sprintf( '<input type="hidden" name="_state" value="%s">', $method );
		echo sprintf( '<input type="hidden" name="_module_name" value="%s">', $module );
		echo sprintf( '<input type="hidden" name="_template_id" value="%d">', $template_id );
		echo sprintf( '<input type="hidden" name="sc_id" value="%s">', $shortcode_id );
		echo TF_Form::render( $module_instance->get_fields(), $data );
		echo TF_Form::submit_button( $label_submit_btn );
		echo TF_Form::close();
	}

	/**
	 * Fires when module form saved.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $post_data 
	 * @return json
	 */
	public function save_module_form( $post_data ) {
		global $tf_modules, $TF_Layout;
		
		$module_instance = $tf_modules->get_module( $post_data['_module_name'] );

		// Check if has builder
		if ( isset( $post_data['_has_builder'] ) && count( $post_data['_has_builder'] ) > 0 ) {
			foreach( $post_data['_has_builder'] as $builder ) {
				if ( isset( $post_data[ $builder ] ) ) {
					if ( get_magic_quotes_gpc() ) {
						$string = stripslashes( $post_data[ $builder ] );
					} else {
						$string = $post_data[ $builder ];
					}
					$raw_data = json_decode( $string, true );
					$post_data[ $builder ] = TF_Model::array_to_shortcode( $raw_data );
				}
			}
		} elseif( isset( $post_data[ 'content' ] ) && is_array( $post_data[ 'content' ] ) ) { // if "content" is sent as an array, serialize and save it as the content of the shortcode
			$post_data[ 'content' ] = serialize( $post_data[ 'content' ] );
		}

		$content = isset( $post_data['content'] ) ? stripslashes( $post_data['content'] ) : '';
		$module_fields = $module_instance->get_fields();

		$atts = $this->set_atts( $module_fields, $post_data );
		if ( isset( $atts['content'] ) ) 
			unset( $atts['content'] );

		// set sc_id
		$atts['sc_id'] = $post_data['sc_id'];

		$shortcode_string = $module_instance->to_shortcode( $atts, $content );
		global $post;
		$post = get_post( $post_data['_template_id'] );
		
		setup_postdata( $post );
		$shortcode = $TF_Layout->render( $shortcode_string );

		$data = array(
			'module'  => $post_data['_module_name'],
			'content' => $content,
			'atts'    => $atts,
			'caption' => $module_instance->name,
			'element' => $shortcode
		);
		wp_send_json_success( $data );	
	}

	public function set_atts( $module_fields, $post_data ) {
		$atts = array();
		foreach( $module_fields as $key => $value ) {
			/* handle multi-dimensional field types that contain other fields */
			if( isset( $value['fields'] ) && $value['type'] != 'repeater' ) {
				$atts = array_merge( $atts, $this->set_atts( $value['fields'], $post_data ) );
			} else {
				$v = '';
				if( isset( $post_data[ $key ] ) ) {
					if( $value['type'] == 'checkbox_group' ) {
						$atts[ $key ] = implode( ',', array_keys( $post_data[ $key ] ) );
					} else if( $value['type'] == 'repeater' ) {
						$atts[ $key ] = count( $post_data[ $key ] );
						$atts[ "{$key}_order" ] = implode( ',', array_keys( $post_data[ $key ] ) );
						foreach( $post_data[ $key ] as $row_id => $row_fields ) {
							foreach( $row_fields as $row_field_id => $row_field_value ) {
								$atts[ "{$key}_{$row_id}_{$row_field_id}" ] = $row_field_value;
							}
						}
					} else {
						$v = $post_data[ $key ];
						$atts[ $key ] = maybe_serialize( $v ); // if $v is_array serialize it.. otherwise return original string
					}
				}
			}
		}

		return apply_filters( 'tf_module_save_atts', $atts, $module_fields, $post_data );
	}

	/**
	 * Fires to render row option form.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $method Form state method (add|edit|delete)
	 */
	public function render_row_option_form( $method ) {
		$label_submit_btn = 'add' == $method ? __('Add', 'themify-flow') : __('Update', 'themify-flow');
		$data = array();
		$shortcode = isset( $_POST['shortcode'] ) ? $_POST['shortcode'] : '';
		$atts = isset( $_POST['shortcode_params'] ) ? $_POST['shortcode_params'] : array();
		$shortcode_id = isset( $atts['sc_id'] ) ? $atts['sc_id'] : TF_Model::generate_block_id();
		$template_id = isset( $_POST['template_id'] ) ? $_POST['template_id'] : 0;
		$mode = isset( $_POST['mode'] ) ? $_POST['mode'] : 'frontend';

		// proses data here
		if ( 'edit' == $method ) {
			$data = stripslashes_deep( $atts );
		}

		echo TF_Form::open( array( 'id' => 'tf_row_option_form' ) );
		echo sprintf( '<input type="hidden" name="_state" value="%s">', $method );
		echo sprintf( '<input type="hidden" name="_shortcode_name" value="%s">', $shortcode );
		echo sprintf( '<input type="hidden" name="_template_id" value="%d">', $template_id );
		echo sprintf( '<input type="hidden" name="sc_id" value="%s">', $shortcode_id );
		echo sprintf( '<input type="hidden" name="_mode" value="%s">', $mode );
		echo TF_Form::render( TF_Shortcodes::row_fields(), $data );
		echo TF_Form::submit_button( $label_submit_btn );
		echo TF_Form::close();
	}

	/**
	 * Fires when module form saved.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $post_data 
	 * @return json
	 */
	public function save_row_form( $post_data ) {
		$atts = array();
		$fields = array_merge( array_keys( TF_Shortcodes::row_fields() ), array( 'sc_id' ) );

		foreach( $fields as $key ) {
			if ( isset( $post_data[ $key ] ) ) {
				$atts[ $key ] = htmlentities($post_data[ $key ], ENT_QUOTES, 'UTF-8');
			}
		}

		$atts['editable_markup'] = 'true';
		global $tf_editor_ui;
		$shortcode_string = TF_Shortcodes::to_shortcode( 'tf_row', $atts );
		$tf_editor_ui->force_editable_shortcode( $post_data['_mode'] );
		$shortcode = do_shortcode( $shortcode_string );

		$data = array(
			'atts'    => $atts,
			'element' => $shortcode
		);
		wp_send_json_success( $data );	
	}

	public function frontend_menu() {

		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options') ) return;

		if ( isset( $_GET['iframe'] ) && $_GET['iframe'] == true ) return;

		global $TF, $TF_Layout;
		$menus_list = array(
			'edit_template' => array(
				'label' => __('Edit Template', 'themify-flow'),
				'href' => esc_url( add_query_arg( 'tf', 1, get_permalink( get_the_ID() ) ) )
			),
			'import_export_template' => array(
				'label' => __('Import/Export', 'themify-flow'),
				'href' => '#'
			),
			'import_template' => array(
				'label' => __('Import', 'themify-flow'),
				'href' => '#',
				'parent' => 'import_export_template',
				'meta'   => array( 'class' => 'tf_import_template' )
			),
			'export_template' => array(
				'label' => __('Export', 'themify-flow'),
				'href' => wp_nonce_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=export_tf_template' ), 'export_tf_nonce' ),
				'parent' => 'import_export_template',
				'meta'   => array( 'class' => 'tf_template_admin_menu_export' )
			),
			'template_option' => array(
				'label' => __('Template Options', 'themify-flow'),
				'href' => '#',
				'meta'   => array( 'class' => 'tf_template_admin_menu_options' )
			)
		);

		$menus = array();

		if ( is_singular( 'tf_template' ) && ! TF_Model::is_tf_editor_active() ) {
			$menus['edit_template'] = $menus_list['edit_template'];
		} else if ( is_singular( 'tf_template' ) && TF_Model::is_tf_editor_active() ) {
			$menus['import_export_template'] = $menus_list['import_export_template'];
			$menus['import_template'] = $menus_list['import_template'];
			$menus['export_template'] = $menus_list['export_template'];
			$menus['template_option'] = $menus_list['template_option'];
		} else if ( is_singular( 'tf_template_part' ) && ! TF_Model::is_tf_editor_active() ) {
			$menus['edit_template_part'] = array(
				'label' => __('Edit Template Part', 'themify-flow'),
				'href' => esc_url( add_query_arg( 'tf', 1, get_permalink( get_the_ID() ) ) )
			);
		} else if ( is_singular( 'tf_template_part' ) && TF_Model::is_tf_editor_active() ) {
			$menus['import_export_template_part'] = array(
				'label' => __('Import/Export', 'themify-flow'),
				'href' => '#'
			);
			$menus['import_template_part'] = array(
				'label' => __('Import', 'themify-flow'),
				'href' => '#',
				'parent' => 'import_export_template_part',
				'meta'   => array( 'class' => 'tf_import_template_part' )
			);
			$menus['export_template_part'] = array(
				'label' => __('Export', 'themify-flow'),
				'href' => wp_nonce_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=export_tf_template_part' ), 'export_tf_nonce' ),
				'parent' => 'import_export_template_part'
			);
		} else {

			// In use template
			if ( 0 !== $TF_Layout->layout_id ) {
				$menus['in_use_template'] = array(
					'label' => __('Edit In-use Template', 'themify-flow'),
					'href' => esc_url( add_query_arg( array( 'tf' => 1, 'tf_source_uri' => TF_Model::get_current_browser_url() ), get_permalink( $TF_Layout->layout_id ) ) )
				);
			}

			// Template
			$menus['template_list'] = array(
				'label' => __('Templates', 'themify-flow'),
				'href' => '#'
			);
			$menus['template_list_add_new'] = array(
				'label' => __('Add New', 'themify-flow'),
				'href' => admin_url('edit.php?post_type=tf_template#tf_add_new'),
				'parent' => 'template_list'
			);
			$menus['template_list_view'] = array(
				'label' => __('View all Templates', 'themify-flow'),
				'href' => admin_url('edit.php?post_type=tf_template'),
				'parent' => 'template_list'
			);

			// Template Part
			$menus['template_part_list'] = array(
				'label' => __('Template Parts', 'themify-flow'),
				'href' => '#'
			);
			$menus['template_part_list_add_new'] = array(
				'label' => __('Add New', 'themify-flow'),
				'href' => admin_url('edit.php?post_type=tf_template_part#tf_add_new'),
				'parent' => 'template_part_list'
			);
			$menus['template_part_list_view'] = array(
				'label' => __('View all Template Parts', 'themify-flow'),
				'href' => admin_url('edit.php?post_type=tf_template_part'),
				'parent' => 'template_part_list'
			);

			if ( TF_Model::is_tf_styling_active() ) {
				$menus['global_styling'] = array(
					'label' => __('Global Styling', 'themify-flow'),
					'href'  => '#',
					'meta'  => array( 'class' => 'tf_load_global_styling' )
				);
				$menus['custom_css'] = array(
					'label' => __('Custom CSS', 'themify-flow'),
					'href'  => '#',
					'meta'  => array( 'class' => 'tf_load_customcss' )
				);
			} else {
				$menus['edit_global_styling'] = array(
					'label' => __('Global Styling', 'themify-flow'),
					'href' => esc_url( add_query_arg( array( 'tf' => 1, 'tf_global_styling' => 1 ) ) ),
				);
				$menus['edit_custom_css'] = array(
					'label' => __('Custom CSS', 'themify-flow'),
					'href' => esc_url( add_query_arg( array( 'tf' => 1, 'tf_custom_css' => 1 ) ) ),
				);
			}
			$menus['theme_settings'] = array(
				'label' => __('Settings', 'themify-flow'),
				'href' => admin_url( 'admin.php?page=tf-settings' ),
			);
		}

		$menus = apply_filters( 'tf_frontend_menus', $menus );

		$frontend_menus = tf_parse_menu( $menus );

		include_once( sprintf( '%s/includes/templates/template-frontend-menu.php', $TF->framework_path() ) );
	}
}

/** Initialize class */
new TF_Frontend();