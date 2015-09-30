<?php
/**
 * Module Text.
 * 
 * Show text blocks with rich editor.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Single_Loop extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(array(
			'name' => __('Single Post', 'themify-flow'),
			'slug' => 'single-loop',
			'shortcode' => 'tf_single_loop',
			'description' => 'Single Post module',
			'category' => 'single'
		));
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

		return apply_filters( 'tf_module_single_loop_fields', array(
			'content' => array(
				'type' => 'builder',
				'wrapper' => 'no',
				'options' => array(
					'category' => 'loop'
				),
				'default' => '[tf_back_row][tf_back_column grid="fullwidth"][tf_post_title][tf_post_excerpt_content][/tf_back_column][/tf_back_row]'
			)
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
		return apply_filters( 'tf_module_single_loop_styles', array(
			'tf_module_single_loop_container' => array(
				'label' => __('Post Container', 'themify-flow' ),
				'selector' => '.tf_post',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_single_loop_post_link' => array(
				'label' => __( 'Post Link', 'themify-flow' ),
				'selector' => '.tf_post a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_link_hover' => array(
				'label' => __( 'Post Link Hover', 'themify-flow' ),
				'selector' => '.tf_post a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_link_hover' => array(
				'label' => __( 'Post Link Hover', 'themify-flow' ),
				'selector' => '.tf_post a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_title' => array(
				'label' => __( 'Post Title', 'themify-flow' ),
				'selector' => '.tf_post_title',
				'basic_styling' => array(  'font' ),
			),
			'tf_module_single_loop_post_title_link' => array(
				'label' => __( 'Post Title Link', 'themify-flow' ),
				'selector' => '.tf_post_title a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_title_link_hover' => array(
				'label' => __( 'Post Title Link Hover', 'themify-flow' ),
				'selector' => '.tf_post_title a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_content' => array(
				'label' => __( 'Post Content', 'themify-flow' ),
				'selector' => '.tf_post_content',
				'basic_styling' => array( 'border', 'font', 'margin', 'padding', 'background' ),
			),
			'tf_module_single_loop_post_meta' => array(
				'label' => __( 'Post Meta Container', 'themify-flow' ),
				'selector' => '.tf_post_meta',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_single_loop_post_meta_link' => array(
				'label' => __( 'Post Meta Link', 'themify-flow' ),
				'selector' => '.tf_post_meta a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_meta_link_hover' => array(
				'label' => __( 'Post Meta Link Hover', 'themify-flow' ),
				'selector' => '.tf_post_meta a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_date' => array(
				'label' => __( 'Post Meta - Date', 'themify-flow' ),
				'selector' => '.tf_post_date',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_category' => array(
				'label' => __( 'Post Meta - Category Link', 'themify-flow' ),
				'selector' => '.tf_post_category a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_category_hover' => array(
				'label' => __( 'Post Meta - Category Link Hover', 'themify-flow' ),
				'selector' => '.tf_post_category a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_tag' => array(
				'label' => __( 'Post Meta - Tag Link', 'themify-flow' ),
				'selector' => '.tf_post_tag a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_tag_hover' => array(
				'label' => __( 'Post Meta - Tag Link Hover', 'themify-flow' ),
				'selector' => '.tf_post_tag a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_author' => array(
				'label' => __( 'Post Meta - Author', 'themify-flow' ),
				'selector' => '.tf_post_author a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_author_hover' => array(
				'label' => __( 'Post Meta - Author Hover', 'themify-flow' ),
				'selector' => '.tf_post_author a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_comment' => array(
				'label' => __( 'Post Meta - Comment Count', 'themify-flow' ),
				'selector' => '.tf_post_comment a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_single_loop_post_comment_hover' => array(
				'label' => __( 'Post Meta - Comment Count Hover', 'themify-flow' ),
				'selector' => '.tf_post_comment a:hover',
				'basic_styling' => array( 'font' ),
			)
		) );
	}

	/**
	 * Render main shortcode.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $original_atts 
	 * @param string $content 
	 * @return string
	 */
	public function render_shortcode( $original_atts, $content = null ) {
		$atts = shortcode_atts( array(
		), $original_atts, $this->shortcode ); // must add the third params $this->shortcode, for builder shortcode rendering

		global $query_string, $TF;
		$output = '';
		$build_query = array(
			'post_type' => 'post'
		);
		if ( TF_Model::is_template_page() ) {
			query_posts( build_query( $build_query ) );
		} else {
			query_posts( $query_string );
		}

		if (have_posts()) {
			$TF->in_archive_loop = true;
			the_post(); ob_start(); ?>
			<?php do_action( 'tf_single_loop_before_post' ); ?>

			<article <?php echo tf_get_attr( 'post', $original_atts ); ?>>

				<?php do_action( 'tf_single_loop_start_post' ); ?>

				<?php echo do_shortcode( $content ); ?>

				<?php do_action( 'tf_single_loop_end_post' ); ?>
                                <meta itemprop="datePublished" content="<?php the_modified_date('c')?>"/>    
			</article>

			<?php do_action( 'tf_single_loop_after_post' ); ?>

			<?php
			$output .= ob_get_contents();
			ob_get_clean();
			$TF->in_archive_loop = false;
		}
		wp_reset_query();

		return $output;
	}
}

new TF_Module_Single_Loop();