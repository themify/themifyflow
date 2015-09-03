<?php
/**
 * Module Text.
 * 
 * Show text blocks with rich editor.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Element_Post_Excerpt_Content extends TF_Module_Element {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(array(
			'name' => __('Excerpt/Content', 'themify-flow'),
			'slug' => 'post-excerpt-content',
			'shortcode' => 'tf_post_excerpt_content',
			'description' => 'Display excerpt / content',
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
		return apply_filters( 'tf_element_post_excerpt_fields', array(
			'post_display' => array(
				'type' => 'radio',
				'label' => __( 'Excerpt/Content', 'themify-flow'),
				'options' => array(
					array( 'name' => __('Content', 'themify-flow'), 'value' => 'content', 'selected' => true ),
					array( 'name' => __('Excerpt', 'themify-flow'), 'value' => 'excerpt' )
				)
			),
                        'display_inline_block'=> array(
                                'type' => 'checkbox',
                                'label' => __( 'Display Inline', 'themify-flow' ),
                                'text' => __( 'Display this module inline (float left)', 'themify-flow' )
                                
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
			'post_display'   => 'content',
                        'display_inline_block'=>false
		), $atts, $this->shortcode ); // must add the third params $this->shortcode, for builder shortcode rendering
                $class = isset($atts['display_inline_block']) && $atts['display_inline_block']?' tf_element_inline_block':'';
                $output = '<div class="tf_post_content entry-content'.$class.'" itemprop="articleBody">';
		ob_start();
		global $post;
		if ( 'content' == $atts['post_display'] ) {
			if ( is_object( $post ) ) {
				the_content( __('More &rarr;', 'themify-flow') );
				edit_post_link(__('Edit', 'themify-flow'), '<span class="edit-button">[', ']</span>');
			}
		} else {
			if ( is_object( $post ) ) {
				the_excerpt();
			}
		}
		$output .= ob_get_contents();
		ob_end_clean();
		$output .= '</div>';

		return apply_filters( 'tf_shortcode_element_render', $output, $this->slug, $atts, $content );
	}
}

new TF_Element_Post_Excerpt_Content();