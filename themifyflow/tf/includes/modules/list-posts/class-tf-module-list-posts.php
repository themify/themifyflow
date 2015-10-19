<?php
/**
 * Module List Post.
 * 
 * Show text blocks with rich editor.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_List_Posts extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(array(
			'name' => __('List Posts', 'themify-flow'),
			'slug' => 'lists-posts',
			'shortcode' => 'tf_lists_posts',
			'description' => 'Module to show posts from selected categories',
			'category' => array('content','global')
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

		$image_base = $TF->framework_uri() . '/assets/img/builder';
                $categories = get_terms( 'category', array( 'hide_empty' => true ) );
                $cats = array();
                if(!empty($categories)){
                    $cats[] = array('name'=>__('All categories','themif-flow'),'value'=>'0');
                    foreach ($categories as $c){
                        $cats[] = array('name'=>$c->name,'value'=>$c->term_id);
                    }
                }
		return apply_filters( 'tf_module_lists_posts_fields', array(
			'layout'  => array(
				'type'       => 'layout',
				'label'      => __('Post Layout', 'themify-flow'),
				'options'    => array(
					array( 'img' => $image_base . '/list-post.png', 'value' => 'list_post', 'label' => __('List Post', 'themify-flow'), 'selected' => true ),
					array( 'img' => $image_base . '/grid3.png', 'value' => 'grid3', 'label' => __('Grid 3', 'themify-flow')),
					array( 'img' => $image_base . '/grid2.png', 'value' => 'grid2', 'label' => __('Grid 2', 'themify-flow')),
					array( 'img' => $image_base . '/grid4.png', 'value' => 'grid4', 'label' => __('Grid 4', 'themify-flow'))
				)
			),
			'order' => array(
				'type' => 'select',
				'label' => __('Order', 'themify-flow'),
				'options' => array(
					array( 'name' => __('Descending','themify-flow'), 'value' => 'desc' ),
					array( 'name' => __('Ascending','themify-flow'), 'value' => 'asc' )
				)
			),
			'orderby' => array(
				'type' => 'select',
				'label' => __('Order By', 'themify-flow'),
				'options' => array(
					array( 'name' => __('Date','themify-flow'), 'value' => 'date' ),
					array( 'name' => __('Id','themify-flow'), 'value' => 'id' ),
					array( 'name' => __('Author','themify-flow'), 'value' => 'author' ),
					array( 'name' => __('Title','themify-flow'), 'value' => 'title' ),
					array( 'name' => __('Name','themify-flow'), 'value' => 'name' ),
					array( 'name' => __('Modified','themify-flow'), 'value' => 'modified' ),
					array( 'name' => __('Rand','themify-flow'), 'value' => 'rand' ),
					array( 'name' => __('Comment Count','themify-flow'), 'value' => 'comment_count' ),
				)
			),
                        'category'=>array(
                                'type' => 'select',
                                'multiple'=>TRUE,
                                "size"    =>10,
				'label' => __('Category', 'themify-flow'),
				'options' => $cats,
				'default' => array( '0' )
                        ),
                        'offset' => array(
				'type' => 'text',
				'label' => __( 'Offset', 'themify-flow'),
				'class' => 'tf_input_width_10',
                                'description'=>__('number of post to skip'),
				'default' => 0
			),
                        'post_per_page' => array(
				'type' => 'text',
				'label' => __( 'Show Number of Posts', 'themify-flow'),
				'class' => 'tf_input_width_10',
				'default' => get_option('posts_per_page')
			),
			'pagination' => array(
				'type' => 'radio',
				'label' => __('Pagination', 'themify-flow'),
				'options' => array(
					array( 'name' => __('No','themify-flow'), 'value' => 'no', 'selected' => true ),
					array( 'name' => __('Yes','themify-flow'), 'value' => 'yes' ),
				)
			),
			'content' => array(
				'type' => 'builder',
				'wrapper' => 'no',
				'options' => array(
					'category' => 'loop'
				),
				'default' => '[tf_back_row][tf_back_column grid="fullwidth"][tf_post_title][tf_post_excerpt_content][/tf_back_column][/tf_back_row]'
			),
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
		return apply_filters( 'tf_module_lists_posts_styles', array(
			'tf_module_lists_posts_wrapper' => array(
				'label' => __('Posts Wrapper', 'themify-flow' ),
				'selector' => '.tf_loops_wrapper',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_lists_posts_container' => array(
				'label' => __('Post Container', 'themify-flow' ),
				'selector' => '.tf_post',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_lists_posts_post_link' => array(
				'label' => __( 'Post Link', 'themify-flow' ),
				'selector' => '.tf_post a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_link_hover' => array(
				'label' => __( 'Post Link Hover', 'themify-flow' ),
				'selector' => '.tf_post a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_title' => array(
				'label' => __( 'Post Title', 'themify-flow' ),
				'selector' => '.tf_post_title',
				'basic_styling' => array(  'font' ),
			),
			'tf_module_lists_posts_post_title_link' => array(
				'label' => __( 'Post Title Link', 'themify-flow' ),
				'selector' => '.tf_post_title a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_title_link_hover' => array(
				'label' => __( 'Post Title Link Hover', 'themify-flow' ),
				'selector' => '.tf_post_title a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_content' => array(
				'label' => __( 'Post Content', 'themify-flow' ),
				'selector' => '.tf_post_content',
				'basic_styling' => array( 'border', 'font', 'margin', 'padding', 'background' ),
			),
			'tf_module_lists_posts_post_meta' => array(
				'label' => __( 'Post Meta Container', 'themify-flow' ),
				'selector' => '.tf_post_meta',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_lists_posts_post_meta_link' => array(
				'label' => __( 'Post Meta Link', 'themify-flow' ),
				'selector' => '.tf_post_meta a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_meta_link_hover' => array(
				'label' => __( 'Post Meta Link Hover', 'themify-flow' ),
				'selector' => '.tf_post_meta a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_date' => array(
				'label' => __( 'Post Meta - Date', 'themify-flow' ),
				'selector' => '.tf_post_date',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_category' => array(
				'label' => __( 'Post Meta - Category Link', 'themify-flow' ),
				'selector' => '.tf_post_category a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_category_hover' => array(
				'label' => __( 'Post Meta - Category Link Hover', 'themify-flow' ),
				'selector' => '.tf_post_category a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_tag' => array(
				'label' => __( 'Post Meta - Tag Link', 'themify-flow' ),
				'selector' => '.tf_post_tag a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_tag_hover' => array(
				'label' => __( 'Post Meta - Tag Link Hover', 'themify-flow' ),
				'selector' => '.tf_post_tag a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_author' => array(
				'label' => __( 'Post Meta - Author', 'themify-flow' ),
				'selector' => '.tf_post_author a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_author_hover' => array(
				'label' => __( 'Post Meta - Author Hover', 'themify-flow' ),
				'selector' => '.tf_post_author a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_comment' => array(
				'label' => __( 'Post Meta - Comment Count', 'themify-flow' ),
				'selector' => '.tf_post_comment a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_lists_posts_post_comment_hover' => array(
				'label' => __( 'Post Meta - Comment Count Hover', 'themify-flow' ),
				'selector' => '.tf_post_comment a:hover',
				'basic_styling' => array( 'font' ),
			)
		) );
	}

	/**
	 * Get query page
	 */
	function get_paged_query() {
		global $wp;
		$page = 1;
		$qpaged = get_query_var( 'paged' );
		if ( ! empty( $qpaged ) ) {
			$page = $qpaged;
		} else {
			$qpaged = wp_parse_args( $wp->matched_query );
			if ( isset( $qpaged['paged'] ) && $qpaged['paged'] > 0 ) {
				$page = $qpaged['paged'];
			}
		}
		return $page;
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
	public function render_shortcode( $original_atts, $content = null ) {
		global $wp_query, $query_string, $TF;

		$atts = shortcode_atts( array(
			'layout' => 'post-list',
			'offset' => 0,
			'post_per_page' => -1,
			'order' => 'DESC',
			'orderby' => 'date',
			'pagination' => 'no',
			'category' => array(0)
		), $original_atts, $this->shortcode );

		$output = '';
		$paged = $this->get_paged_query();
                $atts['category'] = html_entity_decode ($atts['category'],ENT_COMPAT);
                $atts['category'] = maybe_unserialize($atts['category']);
                if(!$atts['category'] || empty( $atts['category'])){
                    return false;
                }
                $build_query = array(
                        'order' => $atts['order'],
                        'orderby' => $atts['orderby']
                );
                $non_category = array_search(0, $atts['category']);
                $build_query['category__in'] = array();
                if($non_category!==false){
                    $categories = get_terms( 'category', array( 'hide_empty' => true ) );
                    foreach ($categories as $c){
                        $build_query['category__in'][] = $c->term_id;
                    }
                }
                else{
                    $build_query['category__in'] = $atts['category'];
                }
                $build_query['posts_per_page'] = $atts['post_per_page'] &&  $atts['post_per_page']>0? $atts['post_per_page']:-1;
		$build_query['offset'] = ( ( $paged - 1 ) * $atts['post_per_page'] ) + $atts['offset'];
                $build_query['nopaging'] = $build_query['posts_per_page']==-1;
            
		$query = new WP_Query( build_query( $build_query ) );
		if ( $query->have_posts() ) {
			ob_start();
			?>
			<!-- loopswrapper -->
                        <div class="tf_loops_wrapper clearfix <?php echo esc_attr( $atts['layout'] ); ?>">
				<?php 
				$TF->in_archive_loop = true;
				while ( $query->have_posts() ) {
					$query->the_post(); ?>

					<?php do_action( 'tf_list_post_before_post' ); ?>

					<article <?php echo tf_get_attr( 'post', $original_atts ); ?>>

						<?php do_action( 'tf_list_post_start_post' ); ?>

						<?php echo do_shortcode( $content ); ?>

						<?php do_action( 'tf_list_post_end_post' ); ?>
                                                <meta itemprop="datePublished" content="<?php the_modified_date('c')?>"/>
					</article>

					<?php do_action( 'tf_list_post_after_post' ); ?>

					<?php
				}
				$TF->in_archive_loop = false; ?>
			</div><!-- /tf_loops_wrapper -->
			<?php 
		
			// Pagination links
			if( 'yes' == $atts['pagination']) {
				echo tf_get_pagenav( '', '', $query );
			}

			$output = ob_get_contents();
			ob_get_clean();
		}

		wp_reset_query();

		wp_reset_postdata();

		return $output;
	}
}

new TF_Module_List_Posts();