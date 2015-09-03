<?php
/**
 * Module Category Image.
 * 
 * Display Page Featured Image
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Category_Image extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __('Category Image', 'themify-flow' ),
			'slug' => 'category-image',
			'shortcode' => 'tf_category_image',
			'description' => 'Category Image module',
			'category' => 'archive'
		) );
                 if(is_admin()){
                   add_action('edit_category_form_fields', array($this,'category_edit_fields'));
                   add_action('category_add_form_fields', array($this,'category_add_fields'));
                   add_action('edited_category', array($this,'save_category_fileds'));
                   add_action('created_category', array($this,'save_category_fileds'));
                }
	}
        
        /**
	 * Category add handler 
         * 
	 * Show extra fields in category add form
         * 
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
        public function category_add_fields(){
            ?>  
                <div class="form-field term-image-wrap">
                    <label for="tf-img"><?php _e('Category Image','themify-flow')?></label>
                    <input type="text" name="tf-img" id="tf-img" size="40"/>
                    <p><?php _e('Enter full image URL (eg. http://yoursite.com/image.jpg)','themify-flow'); ?></p>
                </div>
            <?php
        }
        /**
	 * Category edit handler 
         * 
	 * Show extra fields in category edit form
         * 
	 * @since 1.0.0
	 * @access public
         * @param stdClass $term
	 * @return void
	 */
        public function category_edit_fields($term){
            $categories = get_option('tf-categories-image');
            $term_id = $term->term_id;
            ?>
                <tr class="form-field term-image-wrap"> 
                    <th scope="row"><label for="tf-img"><?php _e('Category Image','themify-flow')?></label></th>
                    <td>
                        <input type="text" name="tf-img" id="tf-img" size="40" value="<?php echo $categories && isset($categories[$term_id])?esc_url($categories[$term_id]):''?>"/>
                        <p class="description"><?php _e('Enter full image URL (eg. http://yoursite.com/image.jpg)','themify-flow'); ?></p>
                    </td>
                </tr>
            <?php
        }

      
         /**
	 * Category save handler 
	 * 
	 * @since 1.0.0
	 * @access public
         * @param int $term_id 
	 * @return void
	 */
        public function save_category_fileds($term_id){
             if ( isset( $_POST['tf-img'] ) ) {
                $url = esc_url($_POST['tf-img']);
                $categories = get_option('tf-categories-image');
                if(!$categories){
                    $categories = array();
                }
                $categories[$term_id] = $url;
                //save the option array
                update_option('tf-categories-image', $categories );
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
		
		return apply_filters( 'tf_category_image_fields', array(
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
		return apply_filters( 'tf_module_category_image_styles', array(
			'tf_module_category_image_container' => array(
				'label' => __( 'Image Container', 'themify-flow' ),
				'selector' => '.tf_category_image',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_category_image_image' => array(
				'label' => __( 'Image', 'themify-flow' ),
				'selector' => '.tf_category_image img',
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

                    $cat = get_query_var('cat');
                    if($cat){
                        $categories = get_option('tf-categories-image');
                        if (isset($categories[$cat]) && $categories[$cat]) {
                                $category = get_category($cat);
                                $thumbnail_title = $category->name;
                                $post_image = sprintf( '<img src="%s" alt="%s" width="%s" height="%s" />',
                                        esc_url( $categories[$cat]),
                                        esc_attr( $thumbnail_title ),
                                        esc_attr( $atts['image_width'] ),
                                        esc_attr( $atts['image_height'] )
                                );
                                $output = '<figure class="tf_category_image">'.$post_image.'</figure>';

                        }
                    }
                }
                 else{
                    if(!$atts['image_width']){
                        $atts['image_width'] = '350';
                    }
                     if(!$atts['image_height']){
                        $atts['image_height'] = '150';
                    }
                    $output = '<figure class="tf_category_image"><img width="'.$atts['image_width'].'" height="'.$atts['image_height'].'" src="http://placehold.it/'.$atts['image_width'].'x'.$atts['image_height'].'" /></figure>';
                }
		return apply_filters( 'tf_shortcode_element_render', $output, $this->slug, $atts, $content );
	}
}

new TF_Module_Category_Image();