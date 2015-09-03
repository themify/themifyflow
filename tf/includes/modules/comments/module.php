<?php
/**
 * Comments module
 * 
 * Show threaded comments for posts and pages.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Comments extends TF_Module {

	/* array that holds the module parameters for filtering wp_list_comments_args later */
	var $comments_args;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Comments', 'themify-flow' ),
			'slug' => 'comments',
			'shortcode' => 'tf_comments',
			'description' => __( 'Display the comments.', 'themify-flow' ),
			'category' => array( 'single', 'page' )
		) );
	}

	/**
	 * Module settings field.
	 * 
	 * Display module options.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function fields() {
		return apply_filters( 'tf_module_comments_fields', array(
			'comments_per_page' => array(
				'type' => 'text',
				'label' => __( 'Comments Per Page', 'themify-flow' ),
				'class' => 'tf_input_width_10',
				'description' => '<br>' . sprintf( __( 'Defaults to the number set in <a href="%s">Settings > Discussion</a>.', 'themify-flow' ), admin_url( 'options-discussion.php' ) )
			),
			'avatar_size' => array(
				'type' => 'text',
				'label' => __( 'Avatar Size', 'themify-flow' ),
				'class' => 'tf_input_width_10',
				'description' => 'px'
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
		return apply_filters( 'tf_module_comments_styles', array(
			'tf_module_comments_container' => array(
				'label' => __( 'Comments Container', 'themify-flow' ),
				'selector' => '.tf_comments_container',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_comments_link' => array(
				'label' => __( 'Comments Link', 'themify-flow' ),
				'selector' => '.tf_module_comments a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_comments_link_hover' => array(
				'label' => __( 'Comments Link Hover', 'themify-flow' ),
				'selector' => '.tf_module_comments a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_comments_title' => array(
				'label' => __( 'Comment Title', 'themify-flow' ),
				'selector' => '.comments-title, .comment-reply-title',
				'basic_styling' => array( 'font', 'margin', 'border' ),
			),
			'tf_module_comments_time' => array(
				'label' => __( 'Comment Time', 'themify-flow' ),
				'selector' => '.comment-time',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_comments_author' => array(
				'label' => __( 'Comment Author', 'themify-flow' ),
				'selector' => '.comment-author',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_comments_entry' => array(
				'label' => __( 'Comment Content', 'themify-flow' ),
				'selector' => '.comment-content',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_comments_reply_link' => array(
				'label' => __( 'Comment Reply Link', 'themify-flow' ),
				'selector' => '.comment-list .comment-reply-link',
				'basic_styling' => array( 'font', 'margin' ),
			),
			'tf_module_comments_reply_form' => array(
				'label' => __( 'Reply Form', 'themify-flow' ),
				'selector' => '#commentform',
				'basic_styling' => array( 'border', 'font', 'margin', 'background' ),
			),
			'tf_module_comments_input' => array(
				'label' => __( 'Reply Form Input', 'themify-flow' ),
				'selector' => '#commentform input[type=text]',
				'basic_styling' => array( 'border', 'font', 'margin', 'background' ),
			),
			'tf_module_comments_textarea' => array(
				'label' => __( 'Reply Form Textarea', 'themify-flow' ),
				'selector' => '#commentform textarea',
				'basic_styling' => array( 'border', 'font', 'margin', 'background' ),
			),
			'tf_module_comments_reply_button' => array(
				'label' => __( 'Reply Form Button', 'themify-flow' ),
				'selector' => '#commentform input#submit',
				'basic_styling' => array( 'border', 'font', 'margin', 'background' ),
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
		$args = shortcode_atts( array(
			'comments_per_page' => get_option( 'comments_per_page' ),
			'avatar_size' => 64,
		), array_filter( $atts ), $this->shortcode );
                if (TF_Model::is_template_page() ) {
                    global $post;
                    $old_post = $post;
                    $post_with_comment = get_posts(array('posts_per_page'=>1,'orderby'=>'comment_count','order'=>'DESC'));
                    if(!empty($post_with_comment)){
                        $post =  current($post_with_comment);
                    }
                }
                $this->comments_args = $args;
                add_filter( 'wp_list_comments_args', array( $this, 'wp_list_comments_args' ) );
                add_filter( 'pre_option_comments_per_page', array( $this, 'pre_option_comments_per_page' ) );
                add_filter( 'pre_option_page_comments', array( $this, 'pre_option_page_comments' ) );

                ob_start(); ?>

                <?php comments_template(); ?>

                <?php
                $output = ob_get_clean();
                remove_filter( 'wp_list_comments_args', array( $this, 'wp_list_comments_args' ) );
                remove_filter( 'pre_option_comments_per_page', array( $this, 'pre_option_comments_per_page' ) );
                remove_filter( 'pre_option_page_comments', array( $this, 'pre_option_page_comments' ) );
                if(isset($old_post)){
                    wp_reset_postdata();
                    $post = $old_post;
                }
		return $output;
	}

	public function wp_list_comments_args( $r ) {
		$r['per_page'] = $this->comments_args['comments_per_page'];
		$r['avatar_size'] = $this->comments_args['avatar_size'];

		return $r;
	}

	function pre_option_comments_per_page( $v ) {
		return $this->comments_args['comments_per_page'];
	}

	function pre_option_page_comments( $v ) {
		return true;
	}
}

/** Initialize module */
new TF_Module_Comments();