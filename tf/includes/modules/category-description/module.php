<?php
/**
 * Module Category Description.
 * 
 * On category archive pages the module shows the description field of the category.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Category_Description extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Category Description', 'themify-flow' ),
			'slug' => 'category-description',
			'shortcode' => 'tf_category_description',
			'description' => __( 'Category Description', 'themify-flow' ),
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
		return apply_filters( 'tf_module_category_description_fields', array(
			'first_page_only' => array(
				'label' => __( 'First Page Only', 'themify-flow' ),
				'type' => 'radio',
				'options' => array(
					array( 'name' => __( 'Yes', 'themify-flow' ), 'value' => 'yes', 'selected' => true ),
					array( 'name' => __( 'No', 'themify-flow' ), 'value' => 'no' ),
				),
				'description' => '<br>' . __( 'Display category description on the first category page only', 'themify-flow' )
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
		return apply_filters( 'tf_module_category_description_styles', array(
			'tf_module_category_description' => array(
				'label' => __( 'Category Description', 'themify-flow' ),
				'selector' => '.tf_category_description',
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
		extract( shortcode_atts( array(
			'first_page_only' => 'yes'
		), $atts, $this->shortcode ) );
                if ( ! TF_Model::is_template_page() ) {
                    if( 'yes' == $first_page_only && is_paged() ) {
                            return '';
                    }

                    ob_start(); ?>

                    <?php the_archive_description( '<div class="tf_category_description">', '</div>' ); ?>

                    <?php
                    $output = ob_get_clean();
                }
                else{
                     $output = sprintf('<p>%s</p>', __('<strong>This is only preview text.</strong> The text content here will be replaced with actual category description when viewing the real page.', 'themify-flow') );
                }
		return $output;
	}
}

/** Initialize module */
new TF_Module_Category_Description();