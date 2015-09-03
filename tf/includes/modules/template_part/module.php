<?php
/**
 * Module Template_Part.
 * 
 * Show Template_Part.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Template_Part extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Template Part', 'themify-flow' ),
			'slug' => 'template-part',
			'shortcode' => 'tf_module_template_part',
			'description' => __( 'Show content of selected Template Part', 'themify-flow' ),
			'category' => 'global'
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
                $template_parts = TF_Model::get_posts( 'tf_template_part' );
                if(!empty($template_parts)){
                    $options = array();
                    foreach ($template_parts as $t){
                        if(!isset($_REQUEST['template_id']) || $_REQUEST['template_id']!=$t->ID){
                            $options[] =array('name'=>$t->post_title,'value'=>$t->ID);
                        }
                    }   
                    if(!empty($options)){
                        return apply_filters( 'tf_module_template_part_fields', array(
                                'part'  => array(
                                        'type' => 'select',
                                        'label' => __( 'Template_Part', 'themify-flow' ),
                                        'options' => $options,
                                )

                        ) );
                    }
                }
                return apply_filters( 'tf_module_template_part_fields', array() );
		
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
		return apply_filters( 'tf_module_template_part_styles', array(
			'tf_module_template_part_container' => array(
				'label' => __( 'Template Part Container', 'themify-flow' ),
				'selector' => '.tf_module_template-part',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
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
		extract( $atts = shortcode_atts( array(
			'part' => ''
		), $atts, $this->shortcode ) );
                if(!isset($atts['part']) || !$atts['part'] || is_admin()){
                    return '';
                }
                global $TF_Template_Part;
                return $TF_Template_Part->template_part_shortcode(array('id'=>$atts['part']));
               
	}
}

/** Initialize module */
new TF_Module_Template_Part();