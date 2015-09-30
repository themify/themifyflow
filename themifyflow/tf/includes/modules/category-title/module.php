<?php
/**
 * Module Category Title.
 * 
 * On category archive pages the module shows the title of the category.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Category_Title extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Category Title', 'themify-flow' ),
			'slug' => 'category-title',
			'shortcode' => 'tf_category_title',
			'description' => __( 'Category Title', 'themify-flow' ),
			'category' => 'archive'
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
		return apply_filters( 'tf_module_category_title_fields', array(
			'title_tag' => array(
				'type' => 'select',
				'label' => __( 'HTML Tag', 'themify-flow' ),
				'options' => array(
					array( 'name' => 'H1', 'value' => 'h1' ),
					array( 'name' => 'H2', 'value' => 'h2' ),
					array( 'name' => 'H3', 'value' => 'h3' ),
					array( 'name' => 'H4', 'value' => 'h4' ),
					array( 'name' => 'H5', 'value' => 'h5' ),
					array( 'name' => 'H6', 'value' => 'h6' ),
					array( 'name' => __( 'Paragraph', 'themify-flow' ), 'value' => 'p' ),
				),
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
		return apply_filters( 'tf_module_category_title_styles', array(
			'tf_module_category_title' => array(
				'label' => __( 'Category Title', 'themify-flow' ),
				'selector' => '.tf_category_title',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
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
		extract( $atts = shortcode_atts( array(
			'title_tag' => 'h1'
		), $atts, $this->shortcode ) );
		
		$output = $this->get_the_archive_title( "<div class=\"module-category-title\"><{$title_tag} class=\"tf_category_title\">", "</{$title_tag}></div>" );

		return $output;
	}

	/**
	 * Retrieve the archive title based on the queried object.
	 *
	 * @return string Archive title.
	 */
	function get_the_archive_title( $before = '', $after = '' ) {
                if ( ! TF_Model::is_template_page() ) {
                    if ( is_category() ) {
                            $title = single_cat_title( '', false );
                    } elseif ( is_tag() ) {
                            $title = single_tag_title( '', false );
                    } elseif ( is_author() ) {
                            $title = sprintf( __( 'Author: %s', 'themify-flow' ), '<span class="vcard">' . get_the_author() . '</span>' );
                    } elseif ( is_year() ) {
                            $title = sprintf( __( 'Year: %s', 'themify-flow' ), get_the_date( _x( 'Y', 'yearly archives date format', 'themify-flow' ) ) );
                    } elseif ( is_month() ) {
                            $title = sprintf( __( 'Month: %s', 'themify-flow' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'themify-flow' ) ) );
                    } elseif ( is_day() ) {
                            $title = sprintf( __( 'Day: %s', 'themify-flow' ), get_the_date( _x( 'F j, Y', 'daily archives date format', 'themify-flow' ) ) );
                    } elseif ( is_tax( 'post_format' ) ) {
                            if ( is_tax( 'post_format', 'post-format-aside' ) ) {
                                    $title = _x( 'Asides', 'post format archive title', 'themify-flow' );
                            } elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
                                    $title = _x( 'Galleries', 'post format archive title', 'themify-flow' );
                            } elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
                                    $title = _x( 'Images', 'post format archive title', 'themify-flow' );
                            } elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
                                    $title = _x( 'Videos', 'post format archive title', 'themify-flow' );
                            } elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
                                    $title = _x( 'Quotes', 'post format archive title', 'themify-flow' );
                            } elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
                                    $title = _x( 'Links', 'post format archive title', 'themify-flow' );
                            } elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
                                    $title = _x( 'Statuses', 'post format archive title', 'themify-flow' );
                            } elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
                                    $title = _x( 'Audio', 'post format archive title', 'themify-flow' );
                            } elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
                                    $title = _x( 'Chats', 'post format archive title', 'themify-flow' );
                            }
                    } elseif ( is_post_type_archive() ) {
                            $title = sprintf( __( '%s', 'themify-flow' ), post_type_archive_title( '', false ) );
                    } elseif ( is_tax() ) {
                            $tax = get_taxonomy( get_queried_object()->taxonomy );
                            /* translators: 1: Taxonomy singular name, 2: Current taxonomy term */
                            $title = sprintf( __( '%1$s: %2$s', 'themify-flow' ), $tax->labels->singular_name, single_term_title( '', false ) );
                    } else {
                            $title = '';
                    }

                    /**
                     * Filter the archive title.
                     *
                     * @param string $title Archive title to be displayed.
                     */
                    $title = apply_filters( 'get_the_archive_title', $title );

                    if( ! empty( $title ) ) {
                            $title = $before . $title . $after;
                    }
                }
                else{
                    $title = $before.(__('Category Title', 'themify-flow')).$after;
                }
		return $title;
	}
}

/** Initialize module */
new TF_Module_Category_Title();