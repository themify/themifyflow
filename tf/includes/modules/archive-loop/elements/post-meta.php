<?php
/**
 * Module Text.
 * 
 * Show text blocks with rich editor.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Element_Post_Meta extends TF_Module_Element {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(array(
			'name' => __('Post Meta', 'themify-flow'),
			'slug' => 'post-meta',
			'shortcode' => 'tf_post_meta',
			'close_type' => TF_Shortcodes::ENCLOSED,
			'description' => 'Display post meta',
			'category' => 'loop'
		));

		// Register shortcode
		add_shortcode( $this->shortcode, array( $this, 'render_shortcode' ) );

		// Additional shortcodes
		$shortcodes = array( 'post_date', 'post_author', 'post_category', 'post_tag', 'post_comment_count' );
		foreach( $shortcodes as $shortcode ) {
			add_shortcode( 'tf_' . $shortcode, array( $this, $shortcode ) );
		}
	}

	/**
	 * Module settings field
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function fields() {
		return apply_filters( 'tf_element_post_meta_fields', array(
			'info' => array(
				'type' => 'html',
				'html' =>  __( '<small>Use the following shortcodes to form post meta (HTML tags support):<br/>
					[tf_post_date] = Post Date<br/>
					[tf_post_author] = Post Author<br/>
					[tf_post_tag] = Post Tags<br/>
					[tf_post_category] = Post Categories<br/>
					[tf_post_comment_count] = Post Comment Count</small>', 'themify-flow'),
			),
			'post_meta_content' => array(
				'set_as_content' => 'true',
				'type' => 'text',
				'default' => sprintf( __('Posted on %s by %s in %s %s'), 
					'[tf_post_date]',
					'[tf_post_author]',
					'[tf_post_category]',
					'[tf_post_comment_count]'
				),
				'class' => 'tf_input_width_100',
				'wrapper' => 'no'
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
		$output = '<p class="tf_post_meta entry-meta'.$class.'">';
		$output .= do_shortcode( $content );
		$output .= '</p>';

		return apply_filters( 'tf_shortcode_element_render', $output, $this->slug, $atts, $content );
	}

	public function post_date( $atts ) {
		return sprintf( '<time datetime="%s" class="tf_post_date" pubdate>%s</time>',
			get_the_time( 'o-m-d' ),
			get_the_date( apply_filters('tf_post_meta_loop_date', '') )
		);
	}

	public function post_author( $atts ) {
		return sprintf( '<span class="tf_post_author">%s</span>', tf_get_author_link() );
	}

	public function post_category( $atts ) {
		ob_start();
		the_category(', ');
		$category = ob_get_contents();
		ob_end_clean();

		return sprintf( '<span class="tf_post_category">%s</span>', $category );
	}

	public function post_tag( $atts ) {
		ob_start();
		the_tags(' <span class="tf_post_tag">', ', ', '</span>');
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	public function post_comment_count( $atts ) {
		$output = '';

		if ( get_the_ID() && comments_open() ) {
			ob_start();
			comments_popup_link( __( '0 Comments', 'themify-flow' ), __( '1 Comment', 'themify-flow' ), __( '% Comments', 'themify-flow' ) );
			$comment = ob_get_contents();
			ob_end_clean();
			$output = sprintf( '<span class="tf_post_comment">%s</span>', $comment );
		}
		return $output;
	}
}

new TF_Element_Post_Meta();