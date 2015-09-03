<?php
/**
 * Framework Content Builder
 * 
 * Themify Flow to enable builder in regular post/page content
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */


class TF_Content_Builder {

	/**
	 * Class instance.
	 * 
	 * @since 1.0.0
	 * @access private
	 */
	private static $instance = null;
	
	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return	A single instance of this class.
	 */
	public static function get_instance() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}

	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access private
	 */
	private function __construct() {
		
		add_action( 'tf_save_template', array( $this, 'save_builder_content' ), 10, 2 );

		// Backend Builder
		if ( is_admin() ) {
			add_filter( 'tf_builder_metabox_screens', array( $this, 'enable_builder_metabox' ) );
			add_filter( 'tf_admin_enqueue_scripts_condition', array( $this, 'register_app_scripts' ), 10, 2 );
		} else {
			// Builder in All Post Types
			add_filter( 'the_content', array( $this, 'output_builder_content' ) );
			add_filter( 'template_include', array( $this, 'render_template_content_builder' ) );
			add_filter( 'tf_frontend_menus', array( $this, 'register_menu' ) );
		}
	}


	/**
	 * Enable builder metabox in content editor.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $post_types
	 */
	public function enable_builder_metabox( $post_types ) {
		$content_post_types = get_post_types( array( 'public' => true ) );
		if ( isset( $content_post_types['attachment'] ) ) 
			unset( $content_post_types['attachment'] );
		$post_types = array_merge( $post_types, array_keys( $content_post_types ) );
		return $post_types;
	}

	/**
	 * Return true when current page is admin content edit.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param boolean $return
	 * @param string $hook_suffix
	 */
	public function register_app_scripts( $return, $hook_suffix ) {
		$screen     = get_current_screen();
		$post_types = get_post_types( array( 'public' => true ) );
		if ( in_array( $screen->id, array_keys( $post_types ) ) ) 
			return true;
              
		return $return;
	}

	/**
	 * Output builder data to content.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $content
	 */
	public function output_builder_content( $content ) {
		global $post;
		$builder = get_post_meta( $post->ID, 'tf_builder_content', true );
		if ( $builder ) {
                    $content .= do_shortcode( $builder );
                }
		return $content;
	}

	/**
	 * Custom page for content builder edit frontend.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $original_template
	 */
	public function render_template_content_builder( $original_template ) {
		if ( ! TF_Model::is_tf_styling_active() && TF_Model::is_tf_editor_active() && is_singular() && ! is_singular( array( 'tf_template', 'tf_template_part' ) ) ) {
			global $TF;
			return $TF->framework_path() . '/includes/templates/template-content-builder.php' ;
		} else {
			return $original_template;
		}
	}

	/**
	 * Register frontend menu.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $menus
	 */
	public function register_menu( $menus ) {
		$content_menus = array();

		if ( is_singular() && ! is_singular( array( 'tf_template', 'tf_template_part' ) ) && TF_Model::is_tf_editor_active() && ! TF_Model::is_tf_styling_active() ) {
			$content_menus['content_builder_view'] = array(
				'label' => __('View the post/page', 'themify-flow'),
				'href' => get_permalink()
			);
			$content_menus['content_builder_import'] = array(
				'label' => __('Import Content', 'themify-flow'),
				'href' => '#',
				'meta'   => array( 'class' => 'tf_content_builder_import' )
			);
			$content_menus['content_builder_export'] = array(
				'label' => __('Export Content', 'themify-flow'),
				'href' => wp_nonce_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=export_tf_content_builder' ), 'export_tf_nonce' )
			);
			$menus = $content_menus;
		} else if ( is_singular() && ! is_singular( array( 'tf_template', 'tf_template_part' ) ) ) {
			$content_menus['content_builder_list'] = array(
				'label' => __('Content Builder', 'themify-flow'),
				'href' =>esc_url( add_query_arg( 'tf', 1, get_permalink() ) )
			);
			$menus = array_merge( $content_menus, $menus );
		}
		return $menus;
	}

	/**
	 * Save builder content data.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param int $post_id
	 * @param array $post_data
	 */
	public function save_builder_content( $post_id, $post_data ) {
		$post_type           = get_post_type( $post_id );
		$reserved_post_types = array( 'tf_template', 'tf_template_part' );
		
		// Save to post_meta
		if ( ! in_array( $post_type, $reserved_post_types ) ) {
			$post_content = TF_Model::array_to_shortcode( $post_data['content'] );
			update_post_meta( $post_id, 'tf_builder_content', $post_content );
		}
	}
}
add_action( 'tf_loaded', array( 'TF_Content_Builder', 'get_instance' ) );