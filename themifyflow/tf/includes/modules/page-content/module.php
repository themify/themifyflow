<?php
/**
 * Module Page Content.
 * 
 * Show content of the page.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Page_Content extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Page Content', 'themify-flow' ),
			'slug' => 'page-content',
			'shortcode' => 'tf_page_content',
			'description' => __( 'Display the content of the page.', 'themify-flow' ),
			'category' => 'page'
		) );
	}

	/**
	 * Module settings field.
	 * 
	 * Display module options.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function fields() {
		return apply_filters( 'tf_module_page_content_fields', array(
			// No options for this module.
		) );
	}

	/**
	 * Module style selectors.
	 * 
	 * Hold module stye selectors to be used in Styling Panel.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function styles() {
		return apply_filters( 'tf_module_page_content_styles', array(
			'tf_module_page_content_container' => array(
				'label' => __( 'Page Content Container', 'themify-flow' ),
				'selector' => '.tf_page_content',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
		) );
	}

	/**
	 * Render main shortcode.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public function render_shortcode( $atts, $content = null ) {
		/** Use condition here to prevent infinite loop issue that cause memory limit error */
		/** If viewing template page (view/frontend editor) echo preview text since the actual the_content is the builder data in a bunch of shortcodes */
		$output = '';
		if ( ! TF_Model::is_template_page() ) {
			// make sure $post exist
			global $post;
			if ( is_object( $post ) && ! is_admin() ) {
				$output = apply_filters( 'the_content', $post->post_content );
				$output = str_replace( ']]>', ']]&gt;', $output );
			}
		} else {
			$output = sprintf('<p>%s</p>', __('<strong>This is only preview text.</strong> The text content here will be replaced with actual page content when viewing the real page.', 'themify-flow') );
		}
		
		return '<div class="tf_page_content">' . $output . '</div>';
	}
}

/** Initialize module */
new TF_Module_Page_Content();