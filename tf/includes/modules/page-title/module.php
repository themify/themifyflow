<?php
/**
 * Module Page Title.
 * 
 * Show page title content.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Page_Title extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(array(
			'name' => __('Page Title', 'themify-flow'),
			'slug' => 'page-title',
			'shortcode' => 'tf_page_title',
			'description' => __('Display page title on the page', 'themify-flow'),
			'category' => 'page'
		));
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
		return apply_filters( 'tf_module_page_title_fields', array(
			'title_tag' => array(
				'type' => 'select',
				'label' => __('HTML Tag', 'themify-flow'),
				'options' => array(
					array( 'name' => 'H1', 'value' => 'h1' ),
					array( 'name' => 'H2', 'value' => 'h2' ),
					array( 'name' => 'H3', 'value' => 'h3' ),
					array( 'name' => 'H4', 'value' => 'h4' ),
					array( 'name' => 'H5', 'value' => 'h5' ),
					array( 'name' => 'H6', 'value' => 'h6' ),
				)
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
		return apply_filters( 'tf_module_text_styles', array(
			'tf_module_page_title' => array(
				'label' => __( 'Page Title', 'themify-flow'),
				'selector' => '.tf_page_title',
				'basic_styling' => array( 'font', 'margin' ),
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
			'title_tag' => ''
		), $atts, $this->shortcode ); // must add the third params $this->shortcode, for builder shortcode rendering
                if ( ! TF_Model::is_template_page() ) {
                    /** We can separate the module markup in separated template file later */
                    $output = sprintf( '<%1$s class="tf_page_title">%2$s</%1$s>', 
                            $atts['title_tag'],
                            get_the_title()
                    );
                }
                else{
                    $output = sprintf('<p>%s</p>', __('<strong>Page Title</strong>', 'themify-flow') );
                }
		return $output;
	}
}

/** Initialize module */
new TF_Module_Page_Title();