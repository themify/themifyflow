<?php
/**
 * Module Page Featured Image.
 * 
 * Display Page Featured Image
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Page_Featured_Image extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __('Page Featured Image', 'themify-flow' ),
			'slug' => 'page_featured_image',
			'shortcode' => 'tf_page_featured_image',
			'description' => 'Page Featured Image module',
			'category' => 'page'
		) );
	}

	/**
	 * Module settings field
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function fields() {
		
		return apply_filters( 'tf_page_featured_image_fields', array(
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
			)
		) );
	}

	/**
	 * Module style selectors.
	 * 
	 * Hold module style selectors to be used in Styling Panel.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function styles() {
		return apply_filters( 'tf_module_page_featured_image_styles', array(
			'tf_module_page_featured_image_container' => array(
				'label' => __( 'Image Container', 'themify-flow' ),
				'selector' => '.tf_page_featured_image',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_page_featured_image_image' => array(
				'label' => __( 'Image', 'themify-flow' ),
				'selector' => '.tf_page_featured_image img',
			)
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
		$atts = shortcode_atts( array(
			'image_size' => 'blank',
			'image_width' => '',
			'image_height' => ''
		), $atts, $this->shortcode ); // must add the third params $this->shortcode, for builder shortcode rendering
		if ( ! TF_Model::is_template_page() ) {
                    $output = '';
                    $image_size = 'blank' != $atts['image_size'] ? $atts['image_size'] : 'large';

                    if (has_post_thumbnail( get_the_ID() ) ) {
                            $post_thumbnail = get_post_thumbnail_id( get_the_ID() );
                            $post_thumbnail_object = get_post( $post_thumbnail );
                            $thumbnail_title = is_object( $post_thumbnail_object ) ? $post_thumbnail_object->post_title : '';
                            $image_attribute = wp_get_attachment_image_src( $post_thumbnail, $image_size );
                            $post_image = sprintf( '<img src="%s" alt="%s" width="%s" height="%s" />',
                                    esc_url( $image_attribute[0] ),
                                    esc_attr( $thumbnail_title ),
                                    esc_attr( $atts['image_width'] ),
                                    esc_attr( $atts['image_height'] )
                            );
                            $output = '<figure class="tf_page_featured_image">'.$post_image.'</figure>';

                    }
                }
                else{
                    if(!$atts['image_width']){
                        $atts['image_width'] = '350';
                    }
                     if(!$atts['image_height']){
                        $atts['image_height'] = '150';
                    }
                    $output = '<figure class="tf_page_featured_image"><img width="'.$atts['image_width'].'" height="'.$atts['image_height'].'" src="http://placehold.it/'.$atts['image_width'].'x'.$atts['image_height'].'" /></figure>';
                }
		return apply_filters( 'tf_shortcode_element_render', $output, $this->slug, $atts, $content );
	}
}

new TF_Module_Page_Featured_Image();