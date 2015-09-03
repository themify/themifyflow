<?php
/**
 * Ajax request class.
 * 
 * Handle all ajax requests
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Ajax {

	/**
	 * Action nonce name.
	 * 
	 * @var string $action_nonce
	 */
	private $action_nonce = 'tf_nonce';

	/**
	 * Field nonce name in $_REQUEST.
	 * 
	 * @var string $field_nonce
	 */
	private $field_nonce = 'nonce';

	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$ajax_events = array(
			'lightbox'                     => false,
			'form_save'                    => false,
			'builder_form_save'            => false,
			'builder_panel_save'           => false,
			'builder_read_data'            => false,
			'builder_update_template_part' => false,
			'plupload'                     => false,
			'shortcode_render'             => false,
			'save_global_styling'          => false,
			'clear_global_styling'         => false,
			'clear_template_style'         => false,
			'generate_temp_stylesheet'     => false
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_tf_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_tf_' . $ajax_event, array( $this, $ajax_event ) );
			}
		}
	}

	/**
	 * Lightbox ajax page.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return json
	 */
	public function lightbox() {

		// Check ajax referer
		check_ajax_referer( $this->action_nonce, $this->field_nonce );

		$type = isset( $_POST['type'] ) ? $_POST['type'] : '';
		$method = isset( $_POST['method'] ) ? $_POST['method'] : 'add';
		
		ob_start();
			echo '<div class="lightbox_inner">';
			do_action( 'tf_lightbox_render_form_' . $type, $method );
			echo '</div>';
		$data = ob_get_contents();
		ob_end_clean();

		wp_send_json_success( $data );
	}

	/**
	 * Fires when form submit ajax.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return json
	 */
	public function form_save() {

		// Check ajax referer
		check_ajax_referer( $this->action_nonce, $this->field_nonce );

		$parse_data = array();
		$type = isset( $_POST['type'] ) ? $_POST['type'] : '';
		$data = isset( $_POST['data'] ) ? $_POST['data'] : array();
		wp_parse_str( $data, $output_data );

		// Validate Fields
		do_action( 'tf_form_validate_' . $type, $output_data );
		
		// Saving Fields
		do_action( 'tf_form_saving_' . $type, $output_data );
		
		wp_send_json_success();

	}

	/**
	 * Fires when builder form submit ajax.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return json
	 */
	public function builder_form_save() {

		// Check ajax referer
		check_ajax_referer( $this->action_nonce, $this->field_nonce );

		$parse_data = array();
		$type = isset( $_POST['type'] ) ? $_POST['type'] : '';
		$data = isset( $_POST['data'] ) ? $_POST['data'] : array();
		wp_parse_str( $data, $output_data );

		// Validate Fields
		do_action( 'tf_builder_form_validate_' . $type, $output_data );
		
		// Saving Fields
		do_action( 'tf_builder_form_saving_' . $type, $output_data );

		wp_send_json_success();
	}

	/**
	 * Fires when Builder Template save ajax.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return json
	 */
	public function builder_panel_save() {
		// Check ajax referer
		check_ajax_referer( $this->action_nonce, $this->field_nonce );

		$data = isset( $_POST['data'] ) ? $_POST['data'] : array();
		$dataStyling = isset( $_POST['dataStyling'] ) ? json_decode( stripslashes( $_POST['dataStyling'] ), true ) : array();
		$postid = (int) $_POST['template_id'];

		TF_Model::save_template( $postid, $data );
		TF_Model::save_styling( $postid, $dataStyling );
		wp_send_json_success();
	}

	/**
	 * Read builder data ( Template parts, content ).
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function builder_read_data() {
		// Check ajax referer
		check_ajax_referer( $this->action_nonce, $this->field_nonce );
		
		$type = isset( $_POST['type'] ) ? $_POST['type'] : 'template';
		$postid = (int) $_POST['template_id'];

		switch ( $type ) {

			case 'utility':
				$data = TF_Model::read_utility_data();
			break;

			case 'module':
				global $tf_styling_control;

				$data = array(
					'selectors_html_section' => $tf_styling_control->render_selector( wp_kses_stripslashes( $_POST['module'] ) ),
					'styling_context' => '.tf_module_block_' . $_POST['shortcode_id'],
					'uniqid' => $_POST['shortcode_id'],
					'module' => $_POST['module'],
					'mode' => 'module'
				);
			break;

			case 'row':
				global $tf_styling_control;

				$data = array(
					'selectors_html_section' => $tf_styling_control->render_selector( 'row' ),
					'styling_context' => '.tf_row_block_' . $_POST['shortcode_id'],
					'uniqid' => $_POST['shortcode_id'],
					'module' => 'row',
					'mode' => 'module'
				);
			break;

			case 'global':
				global $tf_styling_control;

				$data = array(
					'selectors_html_section' => $tf_styling_control->render_selector( 'global' ),
					'styling_context' => '',
					'uniqid' => 'global',
					'module' => 'global',
					'mode' => 'global'
				);
			break;

			default:
				$data = TF_Model::read_template_data( $postid );
			break;
		}

		wp_send_json_success( $data );
	}

	/**
	 * Update Template Part region.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return json
	 */
	public function builder_update_template_part() {
		// Check ajax referer
		check_ajax_referer( $this->action_nonce, $this->field_nonce );
		global $TF_Layout, $tf_editor_ui;

		$temp_layout = $TF_Layout;

		$template_id = intval( $_POST['template_part_id'] );
		$parent_template_id = intval( $_POST['parent_template_id']);
		$parent_post = get_post( $parent_template_id );
		$template_part = get_post( $template_id );
		$region = sanitize_text_field( $_POST['region'] );
		$shortcode = sprintf( '[%s id="%d"]', 'tf_template_part', $template_id );
		$edit_url = add_query_arg( array('tf' => true, 'iframe' => true, 'tf_region' => $region), get_permalink( $template_id ) );
		
		// Setup Layout
		$TF_Layout->setup_layout( $parent_post );
		
		$css = TF_Model::get_style_preview();
		if ( ! empty( $css ) ) {
			$css = '<style id="tf-style-preview">' . $css . '</style>';
		}

		$tf_editor_ui->force_read_shortcode(); // Apply filters shortcode_atts

		$data = array(
			'region'    => $region,
			'html'      => do_shortcode( $shortcode ),
			'shortcode' => $shortcode,
			'edit_url'  => $edit_url,
			'caption'   => sprintf( __('Template Part: %s', 'themify-flow'), esc_html( $template_part->post_title ) ),
			'styles'    => $css,
		);

		$TF_Layout = $temp_layout;
		wp_send_json_success( $data );
	}

	/**
	 * Plupload action.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return json
	 */
	public function plupload() {
		$imgid = $_POST['imgid'];

	    check_ajax_referer( $this->action_nonce, $this->field_nonce );
		
		/** Decide whether to send this image to Media. @var String */
		$add_to_media_library = isset( $_POST['tomedia'] ) ? $_POST['tomedia'] : false;
		/** If post ID is set, uploaded image will be attached to it. @var String */
		$postid = isset( $_POST['topost'] )? intval( $_POST['topost'] ) : '';
		$import_type = isset( $_POST['import_type'] )? $_POST['import_type'] : 'import';
		$method = isset( $_POST['import_method'] ) ? $_POST['import_method'] : 'add';
		$import_source = isset( $_POST['import_source'] ) ? $_POST['import_source'] : 'theme';
	 
	    /** Handle file upload storing file|url|type. @var Array */
	    $file = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'tf_plupload'));
		
		if ( $file && ! isset( $file['error'] ) ) {
			//let's see if it's an image, a zip file or something else
			$ext = explode('/', $file['type']);
			
			// Import routines
			$allowed_types = array( 'zip', 'rar', 'plain', 'xml' );
			if( in_array( $ext[1], $allowed_types ) ){
				
				$url = wp_nonce_url('admin.php?page=themify-flow');

				if (false === ($creds = request_filesystem_credentials($url) ) ) {
					return true;
				}
				if ( ! WP_Filesystem($creds) ) {
					request_filesystem_credentials($url, '', true);
					return true;
				}
				
				global $wp_filesystem, $TF, $tf_export;
				
				if( 'zip' == $ext[1] || 'rar' == $ext[1] ) {
					unzip_file( $file['file'], $TF->framework_path() );
					$file_meta = $tf_export->get_filename_data( 'tf_' . $import_source );
					$filename = $TF->framework_path() . '/' . sanitize_file_name( $file_meta['file'] );
				} else {
					$filename = $file['file'];
				}

				if( $wp_filesystem->exists( $filename ) ) {
						
					if ( 'content_builder' == $import_source ) {
						$import = new TF_Import_Content_Builder();
						$import->content_builder_id = $postid;
						$import->fetch_attachments = true;
						$import->import( $filename );
					} else {
						$import = new TF_Import();
						$import->fetch_attachments = true;
						$import->method = $method;
						$import->source = $import_source;
						if ( 'edit' == $method ) {
							$import->edit_import_id = $postid;
						}
						$import->import( $filename );
						if ( $import->fails() ) {
							$file['error'] = implode( '\n', $import->get_error_messages() );
						} else {
							$file['activate_theme_uri'] = wp_nonce_url( admin_url( 'post.php?post=' . $import->return_ID . '&action=activate_tf_theme' ), 'tf_theme_nonce' );
						}	
					}
					
					$wp_filesystem->delete( $filename );
					$wp_filesystem->delete( $file['file'] );
				} else {
					$file['error'] = __('Data could not be loaded because import data not founded in the zip.', 'themify-flow');
					
					// Delete dump file
					foreach( $tf_export->file_names as $data_file ) {
						if ( $wp_filesystem->exists( $TF->framework_path() . '/' . $data_file['file'] ) ) {
							$wp_filesystem->delete( $TF->framework_path() . '/' . $data_file['file'] );
						}
					}
				}
				
			} else {
				//Image Upload routines
				if( 'tomedia' == $add_to_media_library ){
					
					// Insert into Media Library
					// Set up options array to add this file as an attachment
			        $attachment = array(
			            'post_mime_type' => sanitize_mime_type($file['type']),
			            'post_title' => str_replace('-', ' ', sanitize_file_name(pathinfo($file['file'], PATHINFO_FILENAME))),
			            'post_status' => 'inherit'
			        );
					
					if( $postid ){
						$attach_id = wp_insert_attachment( $attachment, $file['file'], $postid );
					} else {
						$attach_id = wp_insert_attachment( $attachment, $file['file'] );
					}

					// Common attachment procedures
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				    $attach_data = wp_generate_attachment_metadata( $attach_id, $file['file'] );
				    wp_update_attachment_metadata($attach_id, $attach_data);
					
					if( $postid ) {
						
						$full = wp_get_attachment_image_src( $attach_id, 'full' );
						
						if( $_POST['featured'] ){
							//Set the featured image for the post
							set_post_thumbnail($postid, $attach_id);
						}
						update_post_meta($postid, $_POST['fields'], $full[0]);
						update_post_meta($postid, '_'.$_POST['fields'] . '_attach_id', $attach_id);
						
						$thumb = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
						
						//Return URL for the image field in meta box
						$file['thumb'] = $thumb[0];
						
					}
				}
				
			}
			$file['type'] = $ext[1];

		}
		
		// send the uploaded file url in response
		echo json_encode($file);
	    exit;
	}

	/**
	 * Shortcode render.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return json
	 */
	public function shortcode_render() {

		// Check ajax referer
		check_ajax_referer( $this->action_nonce, $this->field_nonce );

		$type = isset( $_POST['type'] ) ? $_POST['type'] : '';
		$method = isset( $_POST['method'] ) ? $_POST['method'] : 'add';
		$mode = isset( $_POST['mode'] ) ? $_POST['mode'] : 'frontend';
		$data = array();
		
		switch ( $method ) {
			case 'duplicate':
				global $TF_Layout;
				if ( 'module' == $type ) {
					global $tf_modules, $tf_styles;
		
					$module_instance = $tf_modules->get_module( $_POST['module'] );
					$atts = isset( $_POST['shortcode_params'] ) ? $_POST['shortcode_params'] : '';
					$content = isset( $_POST['shortcode_content'] ) ? wp_kses_stripslashes( $_POST['shortcode_content'] ) : '';
					$template_id = (int) $_POST['template_id'];
					$data_styling = isset( $_POST['data_styling'] ) ? json_decode( stripslashes( $_POST['data_styling'] ), true ) : array();
					$styles = array();
					$atts['sc_id'] = TF_Model::generate_block_id(); // generate new sc_id
					if ( is_array( $data_styling ) && count( $data_styling ) > 0 ) {
						$data_styling['ID'] = $atts['sc_id'];
						$styles[ $atts['sc_id'] ] = array(
							'module' => $data_styling['module']
						);

						if ( isset( $data_styling['settings'] ) && count( $data_styling['settings'] ) > 0 ) {
							foreach( $data_styling['settings'] as $fields ) {
								$setting_key = '';
								foreach( $fields as $key => $val ) {
									if ( 'SettingKey' == $key ) {
										$setting_key = $val;
									} else {
										$styles[ $atts['sc_id'] ]['settings'][ $setting_key ][ $key ] = stripslashes_deep( $val );
									}
								}
							}
						}
					}

					$render_style = '';
					if ( count( $styles ) > 0 ) {
						$render_style = '<style type="text/css" id="tf-template-temp-'.$atts['sc_id'].'-css">' . $tf_styles->generate_css( $styles ) . '</style>';
					}

					if ( isset( $atts['editable_markup'] ) ) 
						unset( $atts['editable_markup'] );

					if ( get_magic_quotes_gpc() ) {
						$atts = stripslashes_deep( $atts );
					}

					$shortcode_string = $module_instance->to_shortcode( $atts, $content );
					global $post;
					$post = get_post( $template_id );
					
					setup_postdata( $post );
					$shortcode = $TF_Layout->render( $shortcode_string );
					
					$data = array(
						'module'  => sanitize_text_field( $_POST['module'] ),
						'content' => tf_escape_atts( $content ),
						'atts'    => $atts,
						'caption' => $module_instance->name,
						'element' => $shortcode,
						'styles' => $render_style,
						'model' => $data_styling
					);

				} else if ( 'row' == $type ) {
					global $tf_editor_ui;

					$row_data = isset( $_POST['row_data'] ) ? stripslashes_deep( $_POST['row_data'] ) : array();
					tf_recursive_unset( $row_data, 'sc_id' );
					tf_recursive_unset( $row_data, 'editable_markup' );
					$shortcode = TF_Model::array_to_shortcode( array( $row_data ) );
					$tf_editor_ui->force_editable_shortcode( $mode );
					$data = $TF_Layout->render( $shortcode );
				}
			break;
		}

		wp_send_json_success( $data );
	}

	/**
	 * save global styling.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return json
	 */
	public function save_global_styling() {
		// Check ajax referer
		check_ajax_referer( $this->action_nonce, $this->field_nonce );
		$data = isset( $_POST['data_styling'] ) ? json_decode( stripslashes( $_POST['data_styling'] ), true ) : array();
		$save = TF_Model::save_styling( null, array( $data ) );
		if ( is_wp_error( $save ) ) {
			wp_send_json_error( $save );
		} else {
			wp_send_json_success();
		}
	}

	/**
	 * Clear global styling.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return json
	 */
	public function clear_global_styling() {
		// Check ajax referer
		check_ajax_referer( $this->action_nonce, $this->field_nonce );
		global $TF;
		delete_post_meta( $TF->active_theme->theme_id, 'tf_theme_style_global' );
		TF_Model::save_styling( null, '' );
		wp_send_json_success();
	}

	/**
	 * Update template stylesheet.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return json
	 */
	public function clear_template_style() {
		// Check ajax referer
		check_ajax_referer( $this->action_nonce, $this->field_nonce );
		global $tf_styles;

		$template_id = intval( $_POST['template_id'] );
		$dataStyling = isset( $_POST['dataStyling'] ) ? json_decode( stripslashes( $_POST['dataStyling'] ), true ) : array();
		TF_Model::save_styling( $template_id, $dataStyling );
		$styles = TF_Model::get_custom_styling( $template_id, array( 
			'include_template_part' => true,
			'include_global_style' => true
		));
		$data = '<style type="text/css" id="tf-template-layout-css">' . $tf_styles->generate_css( $styles ) . '</style>';
		wp_send_json_success( $data );
	}

	public function generate_temp_stylesheet() {
		// Check ajax referer
		check_ajax_referer( $this->action_nonce, $this->field_nonce );
		
		global $tf_styles;
		$post_data = isset( $_POST['data_styling'] ) ? json_decode( stripslashes( $_POST['data_styling'] ), true ) : array();
		$render_style = array();

		if ( is_array( $post_data ) && count( $post_data ) > 0 ) {
			foreach( $post_data as $module ) {
				$make_data = array();
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
								$make_data[ $module['ID'] ]['settings'][ $setting_key ][ $key ] = stripslashes_deep( $val );
							}
						}
					}
				}
				$render_style[ $module['ID'] ] = '<style type="text/css" id="tf-template-temp-'.$module['ID'].'-css">' . $tf_styles->generate_css( $make_data ) . '</style>';
			}
		}
		
		/*$data = isset( $_POST['data_styling'] ) ? json_decode( stripslashes( $_POST['data_styling'] ), true ) : array();
		TF_Model::save_styling( $postid, array( $data ) );*/

		wp_send_json_success( $render_style );	
	}
}

new TF_Ajax();