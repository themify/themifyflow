<?php
/**
 * Class for TF Theme
 * 
 * Register post type, settings and page.
 * 
 * @package ThemifyFlow
 */
class TF_Theme {

	/**
	 * Post type name.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @var string $post_type
	 */
	protected $post_type = 'tf_theme';

	/**
	 * Default fields.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @var array $defaults
	 */
	protected $defaults = array();

	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct(){
		$this->defaults = array(
			'tf_theme_name'        => __('New Theme', 'themify-flow'),
			'tf_theme_description' => '',
			'tf_theme_author'      => '',
			'tf_theme_author_link' => 'archive',
			'tf_theme_version'     => '1.0.0',
			'tf_theme_screenshot'  => '',
			'tf_theme_screenshot_attach_id' => '',
			'tf_import_base_template_and_part' => false
		);

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'tf_lightbox_render_form_theme', array( $this, 'render_form' ) );
		add_action( 'tf_form_saving_theme', array( $this, 'saving_form' ) );
		add_action( 'tf_form_validate_theme', array( $this, 'validate_fields' ) );

		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'activate_theme' ) );
			add_action( 'admin_init', array( $this, 'delete_theme' ) );
			add_filter( 'manage_edit-' . $this->post_type . '_columns', array( $this, 'edit_columns' ) );
			add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'manage_custom_column' ), 10, 2 );
			add_filter( 'post_row_actions', array( $this, 'post_row_actions' ) );
		}
		
		// Duplicate Theme
		add_action( 'tf_lightbox_render_form_theme_duplicate', array( $this, 'render_duplicate_form' ) );
		add_action( 'tf_form_validate_theme_duplicate', array( $this, 'validate_fields' ) );
		add_action( 'tf_form_saving_theme_duplicate', array( $this, 'saving_duplicate_form' ) );

		// Delete cached theme active
		add_action( 'save_post', array( $this, 'delete_theme_cached' ) );
		add_action( 'before_delete_post', array( $this, 'delete_theme_cached' ) );
		add_action( 'wp_trash_post', array( $this, 'delete_theme_cached' ) );

		// Delete related template and template part data
		add_action( 'delete_post', array( $this, 'delete_associated_templates' ) );
	}

	/**
	 * Register post type Theme.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function register_post_types() {
		global $pagenow;
		$can_export = true;
		
		// Disallow post type to be exportable in WP > Tools > Export
		if ( is_admin() && 'export.php' == $pagenow ) 
			$can_export = false;

		register_post_type( $this->post_type,
			apply_filters( 'tf_register_post_type_tf_theme', array(
				'labels' => array(
					'name'               => __( 'Themes', 'themify-flow' ),
					'singular_name'      => __( 'Theme', 'themify-flow' ),
					'menu_name'          => _x( 'Themes', 'admin menu', 'themify-flow' ),
					'name_admin_bar'     => _x( 'Theme', 'add new on admin bar', 'themify-flow' ),
					'add_new'            => _x( 'Add New', 'theme', 'themify-flow' ),
					'add_new_item'       => __( 'Add New Theme', 'themify-flow' ),
					'new_item'           => __( 'New Theme', 'themify-flow' ),
					'edit_item'          => __( 'Edit Theme', 'themify-flow' ),
					'view_item'          => __( 'View Theme', 'themify-flow' ),
					'all_items'          => __( 'All Themes', 'themify-flow' ),
					'search_items'       => __( 'Search Themes', 'themify-flow' ),
					'parent_item_colon'  => __( 'Parent Themes:', 'themify-flow' ),
					'not_found'          => __( 'No themes found.', 'themify-flow' ),
					'not_found_in_trash' => __( 'No themes found in Trash.', 'themify-flow' )
				),
				'public'              => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'query_var'           => true,
				'rewrite'             => array( 'slug' => 'theme' ),
				'capability_type'     => 'post',
				'has_archive'         => true,
				'hierarchical'        => false,
				'menu_position'       => null,
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ),
				'can_export'          => $can_export
			))
		);
	}

	/**
	 * Post Type Custom column.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $columns 
	 * @return array
	 */
	public function edit_columns( $columns ) {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Theme', 'themify-flow'),
			'screenshot' => __('Screenshot', 'themify-flow'),
			'author' => __('Author', 'themify-flow'),
			'status' => __('Status', 'themify-flow'),
			'date' => __('Date', 'themify-flow')
		);
		return $columns;
	}

	/**
	 * Manage Post Type Custom Columns.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $column 
	 * @param int $post_id 
	 */
	public function manage_custom_column( $column, $post_id  ) {
		switch ( $column ) {
			case 'screenshot':
				if ( has_post_thumbnail( $post_id ) ) 
					the_post_thumbnail( 'thumbnail' );
			break;

			case 'status':
				if ( TF_Model::is_theme_activate( $post_id ) ) {
					echo 'active';
				} else {
					echo '-';
				}
			break;
		}
	}

	/**
	 * Post Row Actions
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $actions 
	 * @return array
	 */
	public function post_row_actions( $actions ) {
		global $post;
		if ( $this->post_type == $post->post_type ) {

			$actions['tf-edit-theme'] = sprintf( '<a href="#" class="tf_lightbox_edit" data-type="theme" data-post-id="%d">%s</a>', 
				$post->ID, 
				__('Edit', 'themify-flow') 
			);

			if ( ! TF_Model::is_theme_activate( $post->ID ) ) {
				$actions['tf-activate-theme'] = sprintf( '<a href="%s">%s</a>', 
					wp_nonce_url( add_query_arg( array(
						'post' => $post->ID,
						'action' => 'activate_tf_theme',
					), admin_url( 'post.php' ) ), 'tf_theme_nonce' ), 
					__('Activate', 'themify-flow') 
				);
			} else {
				unset( $actions['trash'] );
			}			

			$actions['tf-export-theme'] = sprintf( '<a href="%s">%s</a>', 
				wp_nonce_url( admin_url( 'post.php?post=' . $post->ID . '&action=export_tf_theme' ), 'export_tf_nonce' ), 
				__('Export', 'themify-flow') 
			);

			$actions['tf-duplicate-theme'] = sprintf( '<a href="#" class="tf_lightbox_duplicate" data-type="theme" data-post-id="%d">%s</a>', 
				$post->ID, 
				__('Duplicate', 'themify-flow') 
			);

			$actions['tf-replace-theme'] = sprintf( '<a href="#" class="tf_lightbox_replace" data-type="theme" data-post-id="%d">%s</a>', 
				$post->ID, 
				__('Replace', 'themify-flow') 
			);
			if ( isset( $actions['inline hide-if-no-js'] ) ) 
				unset( $actions['inline hide-if-no-js'] );
			if ( isset( $actions['edit'] ) ) 
				unset( $actions['edit'] );
		}

		return $actions;
	}

	/**
	 * Return Theme info fields.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function fields() {
		return apply_filters( 'tf_post_type_tf_theme_fields', array(
			'tf_theme_name'        => array(
				'type'  => 'text',
				'label' => __( 'Name', 'themify-flow' )
			),
			'tf_theme_description' => array(
				'type'  => 'textarea',
				'label' => __( 'Description', 'themify-flow' )
			),
			'tf_theme_author'      => array(
				'type'  => 'text',
				'label' => __( 'Author', 'themify-flow' )
			),
			'tf_theme_author_link' => array(
				'type'  => 'text',
				'label' => __( 'Author Link', 'themify-flow' )
			),
			'tf_theme_version'     => array(
				'type'    => 'text',
				'label'   => __( 'Version', 'themify-flow' ),
				'default' => '1.0.0'
			),
			'tf_theme_screenshot'  => array(
				'type'  => 'image',
				'label' => __( 'Screenshot', 'themify-flow' ),
				'class' => 'tf_input_width_80',
			),
			'tf_theme_screenshot_attach_id'  => array(
				'type'  => 'hidden'
			),
			'tf_import_base_template_and_part' => array(
				'type'    => 'checkbox',
				'label'   => __('Base Templates & Parts', 'themify-flow'),
				'text'    => __('Import the base Templates & Parts', 'themify-flow'),
				'checked' => true
			)
		));
	}

	/**
	 * Render form options in Lightbox via Hooks
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function render_form( $method ) {
		$label_submit_btn = 'add' == $method ? __('Add', 'themify-flow') : __('Save', 'themify-flow');
		$data = array();
		$fields = $this->fields();
		if ( 'add' != $method ) {
			unset( $fields['tf_import_base_template_and_part'] );
		}
		if ( 'edit' == $method ) {
			$theme_id = $_POST['theme_id'];
			$data = get_post_meta( $theme_id, 'theme_info', true );
			$get_post = get_post( $theme_id );
			$data['tf_theme_name'] = $get_post->post_title;
		}

		echo TF_Form::open( array( 'id' => 'tf_theme_form' ) );
		echo sprintf( '<input type="hidden" name="tf_theme_form_state" value="%s">', $method );
		if ( 'edit' == $method ) {
			echo sprintf( '<input type="hidden" name="_theme_id" value="%d">', $theme_id );
		}
		echo TF_Form::render( $fields, $data );
		echo TF_Form::submit_button( $label_submit_btn );
		echo TF_Form::close();
	}

	/**
	 * Validate some form fields input via Hooks
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $fields 
	 * @return json
	 */
	public function validate_fields( $fields ) {
		
		$validator = new TF_Validator( $fields, apply_filters( 'tf_form_validate_theme_field_rules', array(
			'tf_theme_name' => array( 'rule' => 'notEmpty', 'error_msg' => __('You must enter the theme name', 'themify-flow') ),
			'tf_theme_description' => array( 'rule' => 'notEmpty', 'error_msg' => __('You must enter the theme description', 'themify-flow') ),
			'tf_theme_author' => array( 'rule' => 'notEmpty', 'error_msg' => __('You must enter the author name', 'themify-flow') )
		)));

		if ( $validator->fails() ) {
			wp_send_json_error( $validator->get_error_messages() );
		}

	}

	/**
	 * Save form post data via Hooks
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $post_data 
	 */
	public function saving_form( $post_data ) {
		global $TF;

		$post_data = wp_parse_args( $post_data, $this->defaults );

		if ( 'edit' == $post_data['tf_theme_form_state'] ) {
			$new_id = $post_data['_theme_id'];
			$new_post = array();
			$new_post['ID'] = $new_id;
			$new_post['post_title'] = sanitize_text_field( $post_data['tf_theme_name'] );

			wp_update_post( $new_post );
		} else {
			// Create post object
			$my_post = array(
				'post_title'  => sanitize_text_field( $post_data['tf_theme_name'] ),
				'post_status' => 'publish',
				'post_type'   => $this->post_type
			);
			$my_post['post_name'] = str_replace('-', '_', sanitize_title( $my_post['post_title'] ) );

			// Insert the post into the database
			$new_id = wp_insert_post( $my_post );
		}

		if ( $new_id ) {
			unset( $post_data['tf_theme_name'] );
			if ( '' != $post_data['tf_theme_screenshot'] && '' != $post_data['tf_theme_screenshot_attach_id'] ) {
				set_post_thumbnail( $new_id, $post_data['tf_theme_screenshot_attach_id'] );
			}
			update_post_meta( $new_id, 'theme_info', $post_data );

			// Importing templates and parts if checked
			if ( 'add' == $post_data['tf_theme_form_state'] && false !== $post_data['tf_import_base_template_and_part'] ) {
				
				$zip_file = $TF->framework_path() . '/includes/data/theme-base.zip';
				$filename = $TF->framework_path() . '/theme_export.xml';
				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
				}
				WP_Filesystem();
				global $wp_filesystem;

				if ( $wp_filesystem->exists( $zip_file ) ) {
					
					unzip_file( $zip_file, $TF->framework_path() );

					if( $wp_filesystem->exists( $filename ) ) {
						// Remove function hooked in class-tf-engine-style-loader.php
						remove_action( 'tf_import_end', array( 'TF_Model', 'create_stylesheets' ) );

						$query_post = get_post( $new_id );
						$import = new TF_Import();
						$import->fetch_attachments = true;
						$import->exclude_theme_post = true;
						$import->set_associated_theme = $query_post->post_name;
						$import->import( $filename );
						$wp_filesystem->delete( $filename );
					}
				}
			}

			// Return activate url
			$return_url = wp_nonce_url( admin_url( 'post.php?post=' . $new_id . '&action=activate_tf_theme' ), 'tf_theme_nonce' );
			$new_theme = 'add' == $post_data['tf_theme_form_state'] ? true : false;
			wp_send_json_success( array( 'newTheme' => $new_theme, 'url' => $return_url ) );
		}
	}

	/**
	 * Render duplicate form options in Lightbox via Hooks
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function render_duplicate_form() {
		$theme_id = (int) $_POST['postid'];
		$theme = get_post( $theme_id );
		$data = get_post_meta( $theme_id, 'theme_info', true );
		$data['tf_theme_name'] = $theme->post_title . ' Copy';

		$fields = $this->fields();
		if ( isset( $fields['tf_import_base_template_and_part'] ) ) {
			unset( $fields['tf_import_base_template_and_part'] );
		}

		echo TF_Form::open( array( 
			'id' => 'tf_theme_duplicate_form'
		) );
		wp_nonce_field( 'tf_theme_export_nonce', 'nonce_field' );
		echo sprintf( '<input type="hidden" name="_theme_id" value="%d">', $theme_id );
		echo TF_Form::render( $fields, $data );
		echo TF_Form::submit_button( __('Duplicate', 'themify-flow') );
		echo TF_Form::close();
	}

	/**
	 * Activate TF Theme action.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function activate_theme() {
		if ( isset( $_GET['action'] ) && 'activate_tf_theme' == $_GET['action'] && wp_verify_nonce($_GET['_wpnonce'], 'tf_theme_nonce') ) {
			$post_id = (int) $_GET['post'];
                        $this->set_active_theme($post_id);
			wp_redirect( add_query_arg( array( 'page' => 'tf-themes', 'action' => 'tf_theme_activated' ), admin_url( 'admin.php' ) ) );
			exit;
		} elseif ( isset( $_GET['action'] ) && 'tf_theme_activated' == $_GET['action'] ) {
			TF_Model::create_stylesheets();
		}
	}
        
        
        /**
	 * Activate TF Theme.
	 * 
	 * @since 1.0.0
	 * @access public
	 *
         */
        public  function set_active_theme($post_id){
            // Activate theme
            TF_Model::set_active_theme( $post_id );

            // set false to other themes
            $themes = get_posts(array(
                    'post_type'      => $this->post_type,
                    'posts_per_page' => -1,
                    'post__not_in'   => array( $post_id ),
                    'meta_query'     => array(
                            array(
                                    'key'   => 'tf_active_theme',
                                    'value' => 'true',
                            )
                    )
            ));

            if ( count( $themes ) > 0 ) {
                    foreach( $themes as $theme ) {
                            TF_Model::set_active_theme( $theme->ID, false );
                    }
            }
            // clear theme cached
            $this->delete_theme_cached( $post_id );
        }

        /**
	 * Delete TF Theme action.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function delete_theme() {
		if ( isset( $_GET['action'] ) && 'delete_tf_theme' == $_GET['action'] && wp_verify_nonce($_GET['_wpnonce'], 'tf_theme_delete_nonce') ) {
			$post_id = (int) $_GET['post'];
			wp_delete_post( $post_id, true ); // delete from db
			wp_redirect( admin_url( 'admin.php?page=tf-themes' ) );
			exit;
		}
	}

	/**
	 * Save form post data via Hooks
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $post_data 
	 */
	public function saving_duplicate_form( $post_data ) {
		global $tf_duplicate;

		$post_data = wp_parse_args( $post_data, $this->defaults );
		$theme = get_post($post_data['_theme_id']);
		$old_theme_slug = $theme->post_name;
		$theme->post_title = $post_data['tf_theme_name'];
		$theme->post_name = $post_data['tf_theme_name'];

		$new_id = $tf_duplicate->duplicate( $theme );

		// Insert the post into the database
		if ( $new_id ) {
			unset( $post_data['tf_theme_name'] );
			update_post_meta( $new_id, 'theme_info', $post_data );
			delete_post_meta( $new_id, 'tf_active_theme' );
			$new_theme = get_post( $new_id );

			// Duplicate all related templates and Parts
			$args = array(
				'post_type' => array( 'tf_template', 'tf_template_part' ),
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key'     => 'associated_theme',
						'value' => $old_theme_slug
					)
				)
			);
			$query = new WP_Query( $args );
			$data = $query->get_posts();

			if ( $data ) {
				
				// Remove auto prefix post slug
				global $TF;
				remove_filter( 'wp_unique_post_slug', array( $TF->active_theme, 'add_prefix_post_slug' ), 10, 6 );

				foreach( $data as $template ) {
					$template->post_name = str_replace( $old_theme_slug, $new_theme->post_name, $template->post_name );
					$new_template_id = $tf_duplicate->duplicate( $template );
					if ( $new_template_id ) {
						// Update Associated Theme
						update_post_meta( $new_template_id, 'associated_theme', $new_theme->post_name );

						// Update all regions
						if ( 'tf_template' == get_post_type( $new_template_id ) ) {
							$regions = array( 'header', 'sidebar', 'footer' );
							foreach( $regions as $region ) {
								if ( $meta_value = get_post_meta( $new_template_id, 'tf_template_region_' . $region, true ) ) {
									$new_meta_value = str_replace( $old_theme_slug, $new_theme->post_name, $meta_value );
									update_post_meta( $new_template_id, 'tf_template_region_' . $region, $new_meta_value );
								}
							}
						}
					}
				}
			}

			// Return activate url
			$return_url = wp_nonce_url( admin_url( 'post.php?post=' . $new_id . '&action=activate_tf_theme' ), 'tf_theme_nonce' );
			wp_send_json_success( $return_url );
		}
	}

	/**
	 * Delete theme cached.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param int $post_id 
	 * @param object $post 
	 * @param type $update 
	 */
	public function delete_theme_cached( $post_id ) {
		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) )
			return;

		// If this isn't a 'tf_theme' post, don't update it.
		if ( $this->post_type != get_post_type( $post_id ) ) 
			return;

		delete_transient( 'tf_cached_active_theme' );

		// Delete cache templates too
		delete_transient( 'tf_cached_template_assignment_archive' );
		delete_transient( 'tf_cached_template_assignment_single' );
		delete_transient( 'tf_cached_template_assignment_page' );
	}

	/**
	 * Delete associated template and template part data.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param int $post_id 
	 */
	public function delete_associated_templates( $post_id ) {
		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) )
			return;

		// If this isn't a 'tf_theme' post, don't update it.
		if ( $this->post_type != get_post_type( $post_id ) ) 
			return;

		$theme = get_post( $post_id );
		$datas = TF_Model::get_theme_data_post_ids( $post_id, $theme->post_name );
		if ( count( $datas ) > 0 ) {
			foreach( $datas as $data ) {
				if ( $post_id == $data ) continue;
				wp_delete_post( $data, true );
			}
		}
	}
}

/** Initialize class */
$GLOBALS['TF_Theme']  = new TF_Theme();