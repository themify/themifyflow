<?php
/**
 * Module Text.
 * 
 * Show text blocks with rich editor.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Element_Post_Title extends TF_Module_Element {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(array(
			'name' => __('Post Title', 'themify-flow'),
			'slug' => 'post-title',
			'shortcode' => 'tf_post_title',
			'description' => 'Post Title',
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
		return apply_filters( 'tf_element_post_title_fields', array(
			'post_title_tag' => array(
				'type' => 'select',
				'label' => __('Tag', 'themify-flow'),
				'options' => array(
					array( 'name' => 'H1', 'value' => 'h1' ),
					array( 'name' => 'H2', 'value' => 'h2' ),
					array( 'name' => 'H3', 'value' => 'h3' ),
					array( 'name' => 'H4', 'value' => 'h4' ),
					array( 'name' => 'H5', 'value' => 'h5' ),
					array( 'name' => 'H6', 'value' => 'h6' ),
				)
			),
			'post_title_link_to_post' => array(
				'type' => 'radio',
				'label' => __( 'Link to post', 'themify-flow'),
				'options' => array(
					array( 'name' => 'Yes', 'value' => 'yes', 'selected' => true ),
					array( 'name' => 'No', 'value' => 'no' )
				)
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
			'post_title_tag'   => 'h1',
			'post_title_link_to_post' => 'yes',
                        'display_inline_block'=>false
		), $atts, $this->shortcode ); // must add the third params $this->shortcode, for builder shortcode rendering
                $class = isset($atts['display_inline_block']) && $atts['display_inline_block']?' tf_element_inline_block':'';
		if ( 'no' == $atts['post_title_link_to_post'] ) {
			$output = sprintf( '<%1$s itemprop="headline" class="tf_post_title%3$s">%2$s</%1$s>', 
				$atts['post_title_tag'],
				get_the_title(),
                                $class
			);
		} else {
			$output = sprintf( '<%1$s class="tf_post_title%5$s"><a itemprop="headline" href="%2$s" title="%3$s">%4$s</a></%1$s>', 
				$atts['post_title_tag'],
				get_permalink(), 
				the_title_attribute( array( 'echo' => false ) ), 
				get_the_title(),
                                $class
			);
		}

		return apply_filters( 'tf_shortcode_element_render', $output, $this->slug, $atts, $content );
	}
}

new TF_Element_Post_Title();