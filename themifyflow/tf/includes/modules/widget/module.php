<?php
/**
 * Module Widget.
 * 
 * Display WordPress widgets.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Widget extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Widget', 'themify-flow' ),
			'slug' => 'widget',
			'shortcode' => 'tf_widget',
			'description' => __( 'Display widget.', 'themify-flow' ),
			'category' => array('content','global')
		) );

		add_action( 'wp_ajax_tf_builder_get_widget_form', array( $this, 'ajax_get_form' ) );
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
		return apply_filters( 'tf_module_widget_fields', array(
			'widget' => array(
				'type' => 'widget',
				'label' => __( 'Widget', 'themify-flow' ),
			),
			'widget_data' => array( // dummy field, just to store the data from widgets
				'type' => 'html',
				'html' => '',
				'is_array' => true // signal to Flow that the data should be saved as an array (serialized string)
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
		return apply_filters( 'tf_module_widget_styles', array(
			'tf_module_widget_container' => array(
				'label' => __( 'Widget Container', 'themify-flow' ),
				'selector' => '.tf_widget',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_widget_widget_title' => array(
				'label' => __( 'Widget Title', 'themify-flow' ),
				'selector' => '.tf_widget_title',
				'basic_styling' => array( 'font', 'margin', 'padding', 'border' ),
			),
			'tf_module_widget_link' => array(
				'label' => __( 'Widget Link', 'themify-flow' ),
				'selector' => '.tf_widget a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_widget_link_hover' => array(
				'label' => __( 'Widget Link Hover', 'themify-flow' ),
				'selector' => '.tf_widget a:hover',
				'basic_styling' => array( 'font' ),
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
		global $wp_widget_factory;

		extract( wp_parse_args( $atts, array(
			'widget' => '',
			'widget_data' => '',
		) ) );
		if( $widget == '' || ! isset( $wp_widget_factory->widgets[$widget] ) ) {
			return;
		}
		ob_start();

		the_widget( $widget, unserialize( html_entity_decode( $widget_data ) ), array(
			'before_widget' => sprintf( '<div class="tf_widget %s">', $wp_widget_factory->widgets[$widget]->widget_options['classname'] ),
			'after_widget' => '</div>',
			'before_title' => '<h4 class="tf_widget_title">',
			'after_title' => '</h4>',
		) );

		$output = ob_get_clean();
		return $output;
	}

	public function ajax_get_form() {
		global $wp_widget_factory;
		require_once ABSPATH . 'wp-admin/includes/widgets.php';

		if( ! isset( $_POST['widget_class'] ) || empty( $_POST['widget_class'] ) ) {
			return '';
		}
		$options = ( isset( $_POST['widget_data'] ) && ! empty( $_POST['widget_data'] ) ) ? unserialize( stripslashes( $_POST['widget_data'] ) ) : array();

		echo $this->get_widget_form(
			$_POST['widget_class'],
			'widget_data', // field name to send widget fields to server
			$options
		);

		echo '<br/>';
		die();
	}

	/**
	 * Generates the widget form
	 *
	 * @since 1.0
	 * @return string
	 */
	function get_widget_form( $widget, $name, $options = array() ) {
		global $wp_widget_factory;

		require_once ABSPATH . 'wp-admin/includes/widgets.php';
                if(!is_array($options)){
                    $options = array();
                }
		$options = array_merge( $options, array(
			'number' => next_widget_id_number( 0 ),
		) );
		ob_start();
		$wp_widget_factory->widgets[$widget]->form( $options );
		do_action_ref_array( 'in_widget_form', array( $wp_widget_factory->widgets[$widget], null, $options ) );
		$form = ob_get_clean();
		$base_name = 'widget-' . $wp_widget_factory->widgets[$widget]->id_base . '\[' . $wp_widget_factory->widgets[$widget]->number . '\]';
		$form = preg_replace( "/{$base_name}/", $name, $form );

		return $form;
	}

	public function has_shortcode_content() {
		return true;
	}
}

/** Initialize module */
new TF_Module_Widget();