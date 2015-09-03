<?php

/**
 * Framework backend class.
 * 
 * Builder backend editor
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Backend {
	/**
	 * Constructor
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'builder_metaboxes' ) );

		// Force 1 column layout admin screen layout
		add_filter( 'get_user_option_screen_layout_tf_template', array( $this, 'force_single_column_layout_post' ) );
		add_filter( 'get_user_option_screen_layout_tf_template_part', array( $this, 'force_single_column_layout_post' ) );
	}

	/**
	 * Backend builder metaboxes.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function builder_metaboxes() {
		$screens = apply_filters( 'tf_builder_metabox_screens', array( 'tf_template', 'tf_template_part' ) );

		foreach ( $screens as $screen ) {
			add_meta_box(
				'tf_builder_backend_metabox',
				__( 'Flow Content Builder', 'themify-flow' ),
				array( $this, 'render_builder_backend' ),
				$screen
			);
			
			if ( in_array( $screen, array( 'tf_template', 'tf_template_part' ) ) ) {
				// Remove slug div
				remove_meta_box( 'slugdiv', $screen, 'normal' );

				// Remove submit div
				remove_meta_box( 'submitdiv', $screen, 'side' );
			}
		}
	}

	/**
	 * Render builder backend editor.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param object $post 
	 */
	public function render_builder_backend( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'tf_backend_builder_custom_box', 'tf_backend_builder_custom_box_nonce' );
		global $TF;
		include_once( sprintf( '%s/includes/templates/template-backend-builder.php', $TF->framework_path() ) );
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

	/**
	 * Force single column layouts.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return int
	 */
	public function force_single_column_layout_post() {
		return 1;
	}
}

new TF_Backend();