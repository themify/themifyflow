<?php
/**
 * Module Text.
 * 
 * Show text blocks with rich editor.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Element_Post_Text extends TF_Module_Element {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(array(
			'name' => __('Text', 'themify-flow'),
			'slug' => 'post-text',
			'shortcode' => 'tf_post_text',
			'close_type' => TF_Shortcodes::ENCLOSED,
			'description' => 'Post Text',
			'category' => 'loop'
		));

		// Register shortcode
		add_shortcode( $this->shortcode, array( $this, 'render_shortcode' ) );
	}

	/**
	 * Module settings field
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function fields() {
		return apply_filters( 'tf_element_post_text_fields', array(
			'post_text_content' => array(
				'type' => 'wp_editor',
				'wrapper' => 'no',
				'set_as_content' => 'true'
			),
                        'display_inline_block'=> array(
                                'type' => 'checkbox',
                                'label' => __( 'Display Inline', 'themify-flow' ),
                                'text' => __( 'Display this module inline (float left)', 'themify-flow' ),
                        )
		) );
	}

	/**
	 * Render main shortcode.
	 * 
	 * Should be returned with apply_filters('tf_shortcode_module_render') so it can be editable in Builder.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public function render_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
                    'display_inline_block'=>false
		), $atts, $this->shortcode ); // must add the third params $this->shortcode, for builder shortcode rendering
		$class = isset($atts['display_inline_block']) && $atts['display_inline_block']?' tf_element_inline_block':'';
		$output = sprintf( '<div class="post-text%s">%s</div>',$class, do_shortcode( $content ) );		
		return apply_filters( 'tf_shortcode_element_render', $output, $this->slug, $atts, $content );
	}
}

new TF_Element_Post_Text();