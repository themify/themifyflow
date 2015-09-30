<?php
/**
 * Module Text.
 * 
 * Show text blocks with rich editor.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Element_Featured_Image extends TF_Module_Element {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(array(
			'name' => __('Featured Image', 'themify-flow'),
			'slug' => 'featured-image',
			'shortcode' => 'tf_featured_image',
			'description' => 'Featured Image',
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
		return apply_filters( 'tf_element_featured_image_fields', array(
			'image_size' => array(
				'type' => 'select',
				'label' => __('Image Size', 'themify-flow'),
				'options' => tf_get_image_sizes_list()
			),
			'image_dimension' => array(
				'type' => 'multi',
				'label' => __('Dimensions', 'themify-flow'),
				'fields' => array(
					'image_width' => array(
						'type' => 'text',
						'class' => 'tf_input_width_20',
						'wrapper' => 'no',
						'description' => 'x'
					),
					'image_height' => array(
						'type' => 'text',
						'class' => 'tf_input_width_20',
						'wrapper' => 'no',
						'description' => 'px'
					)
				)
			),
			'image_link_to_post' => array(
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
			'image_size' => 'blank',
			'image_width' => '',
			'image_height' => '',
			'image_link_to_post' => 'yes',
                        'display_inline_block'=>false
		), $atts, $this->shortcode ); // must add the third params $this->shortcode, for builder shortcode rendering
		$output = '';
		$image_size = 'blank' != $atts['image_size'] ? $atts['image_size'] : 'large';
		
		if ( has_post_thumbnail( get_the_ID() ) ) {
			$post_thumbnail = get_post_thumbnail_id( get_the_ID() );
			$post_thumbnail_object = get_post( $post_thumbnail );
			$thumbnail_title = is_object( $post_thumbnail_object ) ? $post_thumbnail_object->post_title : '';
			$image_attribute = wp_get_attachment_image_src( $post_thumbnail, $image_size );
			$post_image = sprintf( '<img itemprop="image" src="%s" alt="%s" width="%s" height="%s" />',
				esc_url( $image_attribute[0] ),
				esc_attr( $thumbnail_title ),
				esc_attr( $atts['image_width'] ),
				esc_attr( $atts['image_height'] )
			);
                       
                        $class = isset($atts['display_inline_block']) && $atts['display_inline_block']?' tf_element_inline_block':'';
                        $output = '<figure class="tf_post_image'.$class.'">';
                        if ( 'no' == $atts['image_link_to_post'] ) {
				$output.=$post_image;
			}
                        else{
                            $output.= sprintf( '<a href="%s">%s</a>',
                                    get_permalink(),
                                    $post_image
                            );
                        }
                        $output.='</figure>';
		}

		return apply_filters( 'tf_shortcode_element_render', $output, $this->slug, $atts, $content );
	}
}

new TF_Element_Featured_Image();