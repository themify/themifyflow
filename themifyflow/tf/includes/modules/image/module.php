<?php
/**
 * Module Image.
 * 
 * Display Image
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Image extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Image', 'themify-flow' ),
			'slug' => 'image',
			'shortcode' => 'tf_image',
			'description' => 'Image module',
			'category' => 'content'
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
		global $TF;

		$image_base = $TF->framework_uri() . '/assets/img/builder';

		return apply_filters( 'tf_module_image_fields', array(
			'layout'  => array(
				'type'       => 'layout',
				'label'      => __( 'Image Style', 'themify-flow' ),
				'options'    => array(
					array( 'img' => $image_base . '/image-top.png', 'value' => 'image-top', 'label' => __( 'Image Top', 'themify-flow' ), 'selected' => true ),
					array( 'img' => $image_base . '/image-left.png', 'value' => 'image-left', 'label' => __( 'Image Left', 'themify-flow' ) ),
					array( 'img' => $image_base . '/image-right.png', 'value' => 'image-right', 'label' => __( 'Image Right', 'themify-flow' ) ),
					array( 'img' => $image_base . '/image-overlay.png', 'value' => 'image-overlay', 'label' => __( 'Image Overlay', 'themify-flow' ) ),
					array( 'img' => $image_base . '/image-center.png', 'value' => 'image-center', 'label' => __( 'Image Center', 'themify-flow' ) ),
				)
			),
			'image_url' => array(
				'type' => 'image',
				'label' => __( 'Image URL', 'themify-flow' ),
				'class' => 'tf_input_width_70',
			),
			'image_appearance' => array(
				'type' => 'checkbox_group',
				'label' => __( 'Image Appearance', 'themify-flow' ),
				'options' => array(
					'rounded' => __( 'Rounded', 'themify-flow' ),
					'drop-shadow' => __( 'Drop Shadow', 'themify-flow' ),
					'bordered' => __( 'Bordered', 'themify-flow' ),
					'circle' => __( 'Circled', 'themify-flow' ),
				),
				'is_array' => true,
			),
                        'image_size' => array(
				'type' => 'select',
				'label' => __('Image Size', 'themify-flow'),
				'options' => tf_get_image_sizes_list()
			),
			'image_dimensions' => array(
				'type' => 'multi',
				'label' => __( 'Image Dimensions', 'themify-flow' ),
				'fields' => array(
					'width' => array(
						'type' => 'number',
						'label' => __( '', 'themify-flow' ),
						'class' => 'tf_input_width_10',
						'description' => ' X ',
					),
					'height' => array(
						'type' => 'number',
						'label' => __( '', 'themify-flow' ),
						'class' => 'tf_input_width_10',
						'description' => 'px',
					),
				),
				'row_class' => 'logo_type logo_type-image',
			),
			'title' => array(
				'type' => 'text',
				'label' => __( 'Image Title', 'themify-flow' ),
				'class' => 'tf_input_width_70',
			),
			'link' => array(
				'type' => 'text',
				'label' => __( 'Image Link', 'themify-flow' ),
				'class' => 'tf_input_width_70',
			),
			'alt' => array(
				'type' => 'text',
				'label' => __( 'Alt Text', 'themify-flow' ),
				'class' => 'tf_input_width_70',
			),
			'caption' => array(
				'type' => 'textarea',
				'label' => __( 'Image Caption', 'themify-flow' ),
				'class' => 'tf_input_width_70',
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
		return apply_filters( 'tf_module_image_styles', array(
			'tf_module_image_container' => array(
				'label' => __( 'Image Container', 'themify-flow' ),
				'selector' => '.tf_image_wrapper',
				'chain_with_context' => true,
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_image_image' => array(
				'label' => __( 'Image', 'themify-flow' ),
				'selector' => '.tf_image_wrap img',
			),
			'tf_module_image_title' => array(
				'label' => __( 'Image Title', 'themify-flow' ),
				'selector' => '.tf_image_title',
				'basic_styling' => array( 'font', 'margin' ),
			),
			'tf_module_image_title_link' => array(
				'label' => __( 'Image Title Link', 'themify-flow' ),
				'selector' => '.tf_image_title_link a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_image_title_link_hover' => array(
				'label' => __( 'Image Title Link Hover', 'themify-flow' ),
				'selector' => '.tf_image_title_link a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_image_caption' => array(
				'label' => __( 'Image Caption', 'themify-flow' ),
				'selector' => '.tf_image_caption',
				'basic_styling' => array( 'font' ),
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
		extract( shortcode_atts( array(
			'layout' => 'image-top',
			'image_url' => '',
			'image_appearance' => '',
			'title' => '',
			'link' => '',
			'alt' => '',
			'caption' => '',
			'width' => '',
			'height' => '',
			'image_color' => '',
                        'image_size' => 'blank'
		), $atts, $this->shortcode ) );
                
                if(!$atts['image_url']){
                    return;
                }
             
                $image_size = 'blank' != $atts['image_size'] ? $atts['image_size'] : 'large';
                $image_url =  self::getImage($atts['image_url'],$image_size);
                $image = sprintf( '<img itemprop="image" src="%s" width="%s" height="%s" alt="%s" title="%s" />',
			esc_attr($image_url ),
			esc_attr( $atts['width'] ),
			esc_attr( $atts['height'] ),
			esc_attr( $atts['alt'] ),
                        esc_attr( $atts['title'] )
		);

		ob_start(); ?>

		<div class="tf_image_wrapper <?php echo $layout; ?> <?php echo str_replace( ',', ' ', $image_appearance ); ?>">

		<div class="tf_image_wrap">
			<?php if ( ! empty( $link ) ): ?>
			<a href="<?php echo esc_url( $link ); ?>">
				<?php echo wp_kses_post( $image ); ?>
			</a>
			<?php else: ?>
				<?php echo wp_kses_post( $image ); ?>
			<?php endif; ?>
		
		<?php if( 'image-overlay' != $layout ): ?>
		</div><!-- .tf_image_wrap -->
		<?php endif; ?>
		
		<?php if ( ! empty( $title ) || ! empty( $caption ) ): ?>
		<div class="tf_image_content">
			<?php if ( ! empty( $title ) ): ?>
			<h3 class="tf_image_title">
				<?php if ( ! empty( $link ) ): ?>
				<a href="<?php echo esc_url( $link ); ?>">
					<?php echo wp_kses_post( $title ); ?>
				</a>
				<?php else: ?>
					<?php echo wp_kses_post( $title ); ?>
				<?php endif; ?>
			</h3>
			<?php endif; ?>
			
			<?php if ( ! empty( $caption ) ): ?>
			<div class="tf_image_caption">
				<?php echo apply_filters( 'tf_module_content', $caption ); ?>
			</div><!-- .tf_image_caption -->
			<?php endif; ?>
		</div><!-- .tf_image_content -->
		<?php endif; ?>

		<?php if( 'image-overlay' == $layout ): ?>
		</div><!-- .tf_image_wrap -->
		<?php endif; ?>

		</div>

		<?php
		$output = ob_get_clean();
		return $output;
	}
        
        public static function getImage($image_slug, $image_size='full'){
            $attachment_id = TF_Model::get_post_type_query( 'ID', 'attachment', $image_slug, null );
            if(!$attachment_id){
                return FALSE;
            }
            $image_attribute = wp_get_attachment_image_src( $attachment_id, $image_size );
            return $image_attribute[0];
        }
}

new TF_Module_Image();