<?php
/**
 * Class Template Part post type.
 * 
 * Register post type, settings and page.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Template_Part {

	/**
	 * Post Type Name.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @var string $post_type
	 */
	protected $post_type = 'tf_template_part';

	/**
	 * Shortcode name.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @var string $shortcode
	 */ 
	protected $shortcode = 'tf_template_part';

	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct(){
		add_action( 'init', array( $this, 'register_post_types' ) );
		//add_action( 'init', array( $this, 'register_taxonomies') );
		add_action( 'tf_lightbox_render_form_template_part', array( $this, 'render_form' ) );
		add_action( 'tf_form_saving_template_part', array( $this, 'saving_form' ) );
		add_action( 'tf_form_validate_template_part', array( $this, 'validate_fields' ) );

		add_shortcode( $this->shortcode, array( $this, 'template_part_shortcode' ) );

		if ( ! is_admin() ) {
			add_filter( 'template_include', array( $this, 'render_template_part_region' ) );
		} else {
			add_filter( 'post_row_actions', array( $this, 'post_row_actions' ) );
			add_action( 'edit_form_top', array( $this, 'add_view_btn') );

			// Metaboxes
			add_action( 'add_meta_boxes', array( $this, 'options_metaboxes' ) );
			add_action( 'save_post', array( $this, 'save_option_metabox' ) );
		}

		// Duplicate Template
		add_action( 'tf_lightbox_render_form_template_part_duplicate', array( $this, 'render_duplicate_form' ) );
		add_action( 'tf_form_validate_template_part_duplicate', array( $this, 'validate_fields' ) );
		add_action( 'tf_form_saving_template_part_duplicate', array( $this, 'saving_duplicate_form' ) );

		add_action( 'after_setup_theme', array( $this, 'themify_builder_support' ) );
	}

	/**
	 * Register Post Type.
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
			apply_filters( 'tf_register_post_type_tf_template_part', array(
				'labels' => array(
					'name'               => __( 'Template Parts', 'themify-flow' ),
					'singular_name'      => __( 'Template Part', 'themify-flow' ),
					'menu_name'          => _x( 'Template Parts', 'admin menu', 'themify-flow' ),
					'name_admin_bar'     => _x( 'Template Part', 'add new on admin bar', 'themify-flow' ),
					'add_new'            => _x( 'Add New', 'template part', 'themify-flow' ),
					'add_new_item'       => __( 'Add New Template Part', 'themify-flow' ),
					'new_item'           => __( 'New Template Part', 'themify-flow' ),
					'edit_item'          => __( 'Edit Template Part', 'themify-flow' ),
					'view_item'          => __( 'View Template Part', 'themify-flow' ),
					'all_items'          => __( 'All Template Parts', 'themify-flow' ),
					'search_items'       => __( 'Search Template Parts', 'themify-flow' ),
					'parent_item_colon'  => __( 'Parent Template Parts:', 'themify-flow' ),
					'not_found'          => __( 'No template parts found.', 'themify-flow' ),
					'not_found_in_trash' => __( 'No template parts found in Trash.', 'themify-flow' )
				),
				'public'              => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => current_user_can( 'manage_options' ),
				'show_ui'             => true,
				'show_in_menu'        => false,
				'query_var'           => true,
				'rewrite'             => array( 'slug' => 'template-part' ),
				'capability_type'     => 'post',
				'has_archive'         => false,
				'hierarchical'        => false,
				'menu_position'       => null,
				'supports'            => array( 'title' ),
				'can_export'          => $can_export
			))
		);
	}

	public function register_taxonomies() {
		if ( taxonomy_exists( 'associated_theme' ) ) 
			register_taxonomy_for_object_type( 'associated_theme', 'tf_template_part' );
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
			$actions['tf-export-template_part'] = sprintf( '<a href="%s">%s</a>', 
				wp_nonce_url( admin_url( 'post.php?post=' . $post->ID . '&action=export_tf_template_part' ), 'export_tf_nonce' ), 
				__('Export', 'themify-flow') 
			);
			$actions['tf-duplicate-template-part'] = sprintf( '<a href="#" class="tf_lightbox_duplicate" data-type="template_part" data-post-id="%d">%s</a>', 
				$post->ID, 
				__('Duplicate', 'themify-flow') 
			);
			$actions['tf-replace-template-part'] = sprintf( '<a href="#" class="tf_lightbox_replace" data-type="template_part" data-post-id="%d">%s</a>', 
				$post->ID, 
				__('Replace', 'themify-flow') 
			);
			$actions['tf-view-template-part'] = sprintf( '<a href="%s" target="_blank">%s</a>', 
				esc_url( add_query_arg( 'tf', 1, get_permalink( $post->ID ) ) ), 
				__('Frontend Edit', 'themify-flow') 
			);
			if ( isset( $actions['inline hide-if-no-js'] ) ) 
				unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}

	/**
	 * Add view link button.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param object $post
	 */
	public function add_view_btn( $post ) {
		if ( $this->post_type == $post->post_type ) {
			echo sprintf( '<a href="%s" class="button">%s</a>', 
				esc_url( add_query_arg( 'tf', 1, get_permalink( $post->ID ) ) ),
				__('Switch to frontend', 'themify-flow') 
			);
		}
	}

	/**
	 * Post type creation fields.
	 * 
	 * @since 1.0.0
	 * @access protected
	 */
	protected function fields() {
		return apply_filters( 'tf_post_type_tf_template_part_fields', array(
			'tf_template_part_name' => array(
				'type'  => 'text',
				'label' => __('Name', 'themify-flow')
			),
			'tf_template_part_custom_css_class' => array(
				'type'  => 'text',
				'class' => 'tf_input_width_80',
				'label' => __('Custom CSS Class', 'themify-flow')
			)
		));
	}

	/**
	 * Render form on ajax.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function render_form() {
		echo TF_Form::open( array( 'id' => 'tf_template_part_form' ) );
		echo TF_Form::render( $this->fields() );
		echo TF_Form::submit_button( __('Add', 'themify-flow') );
		echo TF_Form::close();
	}

	/**
	 * Validate some form fields input.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $fields 
	 * @return json
	 */
	public function validate_fields( $fields ) {
		
		$validator = new TF_Validator( $fields, apply_filters( 'tf_form_validate_template_part_field_rules', array(
			'tf_template_part_name' => array( 'rule' => 'notEmpty', 'error_msg' => __('You should enter the template part name', 'themify-flow') )
		)));

		if ( $validator->fails() ) {
			wp_send_json_error( $validator->get_error_messages() );
		}

	}

	/**
	 * Saving form ajax.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $post_data 
	 * @return json
	 */
	public function saving_form( $post_data ) {
		global $TF;
		$name = isset( $post_data['tf_template_part_name'] ) ? sanitize_text_field( $post_data['tf_template_part_name'] ) : __('New Template Part', 'themify-flow');
		$custom_css = isset( $post_data['tf_template_part_custom_css_class'] ) ? sanitize_text_field( $post_data['tf_template_part_custom_css_class']  ) : '';
		$set_slug = sanitize_title( $TF->active_theme->slug . ' ' . $name );

		// Create post object
		$my_post = array(
			'post_title'  => $name,
			'post_name' => $set_slug,
			'post_status' => 'publish',
			'post_type'   => $this->post_type,
		);

		// Insert the post into the database
		$new_id = wp_insert_post( $my_post );
		if ( $new_id ) {
			// Update associated theme
			update_post_meta( $new_id, 'associated_theme', $TF->active_theme->slug );
			update_post_meta( $new_id, 'tf_template_part_custom_css_class', $custom_css );
			$callback_uri = add_query_arg( 'tf', 1, get_permalink( $new_id ) );

			wp_send_json_success( esc_url( $callback_uri ) );
		}
	}

	/**
	 * Template part shortcode.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @return string
	 */
	public function template_part_shortcode( $atts ) {
		global $TF, $TF_Layout;

		extract( shortcode_atts( array(
			'id'   => '',
			'slug' => ''
		), $atts ));

		$field_val = $slug;
		$field_name = 'post_name';
		if ( ! empty( $id ) ) {
			$field_val = $id;
			$field_name = 'ID';
		}
		$output = '';

		global $wpdb;
		$sql = $wpdb->prepare( "SELECT ID, post_content FROM $wpdb->posts WHERE post_type='%s' AND post_status='publish' AND {$field_name}='%s'", 
			$this->post_type, $field_val );
		$template = $wpdb->get_row( $sql );
		
		if ( $template ) {
			$TF->in_template_part = true;
			$classes[] = 'tf_template_part_wrapper';
			if ( 'post_name' == $field_name ) 
				$classes[] = sanitize_html_class( $slug );
			
			$custom_class = get_post_meta( $template->ID, 'tf_template_part_custom_css_class', true );
			if ( $custom_class ) {
				$custom = explode( ' ', $custom_class );
				foreach( $custom as $class ) {
					$classes[] = sanitize_html_class( $class );
				}
			}
			$output = sprintf( '<div class="%s">', implode(' ', $classes ) );
			$output .= apply_filters( 'tf_template_part_output', $TF_Layout->render( $template->post_content ), $template->ID );
			$output .= '</div>';
			$TF->in_template_part = false;
		}

		return $output;
	}

	/**
	 * Use custom template for Template Part post type.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $original_template 
	 * @return string
	 */
	public function render_template_part_region( $original_template ) {
		if ( is_singular( array( $this->post_type ) ) ) {
			global $TF;

			if ( isset( $_GET['iframe'] ) && $_GET['iframe'] == true ) {
				// remove admin bar inside iframe
				add_filter( 'show_admin_bar', '__return_false' );
			}

			$templatefilename = TF_Model::is_tf_editor_active() ? 'template-part-edit.php' : 'template-part.php';
			
			// locate on theme
			$return_template = locate_template(
				array(
					trailingslashit( 'templates' ) . $templatefilename
				)
			);

			// Get default template
			if ( ! $return_template )
				$return_template = $TF->framework_path() . '/includes/templates/' . $templatefilename;

			return $return_template;
		} else {
			return $original_template;
		}
	}

	/**
	 * Render form on ajax.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function render_duplicate_form() {
		$template_part_id = (int) $_POST['postid'];
		$data = array();
		$get_post = get_post( $template_part_id );
		$data['tf_template_part_name'] = $get_post->post_title . ' Copy';
		$data['tf_template_part_custom_css_class'] = get_post_meta( $get_post->ID, 'tf_template_part_custom_css_class', true );

		echo TF_Form::open( array( 'id' => 'tf_template_part_duplicate_form' ) );
		echo sprintf( '<input type="hidden" name="_template_part_id" value="%d">', $template_part_id );
		echo TF_Form::render( $this->fields(), $data );
		echo TF_Form::submit_button( __('Duplicate', 'themify-flow') );
		echo TF_Form::close();
	}

	/**
	 * Saving form ajax.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $post_data 
	 * @return json
	 */
	public function saving_duplicate_form( $post_data ) {
		global $TF, $tf_duplicate;
		$name = isset( $post_data['tf_template_part_name'] ) ? sanitize_text_field( $post_data['tf_template_part_name'] ) : __('New Template Part', 'themify-flow');
		$custom_css = isset( $post_data['tf_template_part_custom_css_class'] ) ? sanitize_text_field( $post_data['tf_template_part_custom_css_class'] ) : '';

		$template = get_post($post_data['_template_part_id']);
		$template->post_title = $name;
		$template->post_name = $name;

		$new_id = $tf_duplicate->duplicate( $template );

		if ( $new_id ) {
			// Update associated theme
			update_post_meta( $new_id, 'associated_theme', $TF->active_theme->slug );
			update_meta( $new_id, 'tf_template_part_custom_css_class', $custom_css );
		}
	}

	/**
	 * Metabox Settings.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function options_metaboxes() {
		add_meta_box(
			'tf_template_part_options_metabox',
			__( 'Template Part Options', 'themify-flow' ),
			array( $this, 'render_template_part_option' ),
			$this->post_type
		);
	}

	/**
	 * Render Template Options metabox.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param object $post 
	 */
	public function render_template_part_option( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'tf_template_part_option_custom_box', 'tf_template_part_option_custom_box_nonce' );
		$fields = $this->fields();
		unset( $fields['tf_template_part_name'] );
		$data = TF_Model::get_field_exist_values( $fields, $post->ID );

		echo '<div class="tf_interface">';
		echo TF_Form::render( $fields, $data );
		echo '</div>';
	}

	/**
	 * Save template option metabox.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param int $post_id
	 */
	public function save_option_metabox( $post_id ) {
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['tf_template_part_option_custom_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['tf_template_part_option_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'tf_template_part_option_custom_box' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		if ( $this->post_type != $_POST['post_type'] ) 
			return $post_id;	

		/* OK, its safe for us to save the data now. */

		// Save Parts Options
		foreach( $this->fields() as $key => $value ) {
			if ( 'tf_template_part_name' == $key ) continue;
			if ( isset( $_POST[ $key ] ) ) 
				update_post_meta( $post_id, $key, $_POST[ $key ] );
		}

	}

	/**
	 * Themify Builder plugin compatibility
	 *
	 * @since 1.1.8
	 */
	function themify_builder_support() {
		if( class_exists( 'Themify_Builder' ) ) {
			add_filter( 'themify_builder_post_types_support', array( $this, 'add_themify_builder_support' ) );
			add_filter( 'themify_post_types', array( $this, 'add_themify_builder_support' ) );
			add_filter( 'tf_template_part_output', array( $this, 'builder_render' ), 10, 2 );
		}
	}

	/**
	 * Enable Builder editor for the Flow Template Parts
	 *
	 * @since 1.1.8
	 */
	function add_themify_builder_support( $post_types ) {
		$post_types[$this->post_type] = $this->post_type;

		return $post_types;
	}

	/**
	 * Render Builder data for Flow Template Parts
	 *
	 * @since 1.1.8
	 */
	function builder_render( $output, $post_id ) {
		global $ThemifyBuilder;

		$builder_data = $ThemifyBuilder->get_builder_data( $post_id );
		$output .= $ThemifyBuilder->retrieve_template( 'builder-output.php', array( 'builder_output' => $builder_data, 'builder_id' => $post_id ), '', '', false );

		return $output;
	}
}

/** Initialize class */
$GLOBALS['TF_Template_Part'] = new TF_Template_Part();