<?php
/**
 * Module Loader class.
 * 
 * Register modules, get modules object.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Loader {
	
	/**
	 * Lists modules.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @var array $modules
	 */
	protected $modules = array();

	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$this->load_modules();

		/** Adding custom css class to all modules */
                add_filter( 'tf_module_fields', array( $this, 'display_inline_field' ), 10, 2 );
		add_filter( 'tf_module_fields', array( $this, 'custom_css_field' ), 10, 2 );
		add_filter( 'tf_module_classes', array( $this, 'output_css_class' ), 10, 2 );
                add_filter( 'tf_module_classes', array( $this, 'display_inline_field_class' ), 10, 2 );
	}

	/**
	 * Register module.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param object &$module 
	 */
	public function register_module( &$module ) {
		$this->modules[ $module->slug ] = &$module;
	}

	/**
	 * Get all modules.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function get_modules() {
		return $this->modules;
	}

	/**
	 * Get a module object.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $slug 
	 * @return object
	 */
	public function get_module( $slug ) {
		if ( isset( $this->modules[ $slug ] ) ) {
			return $this->modules[ $slug ];
		} else {
			return false;
		}
	}

	/**
	 * Get all modules shortcode name.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function get_module_shortcodes() {
		$shortcodes = array();
		foreach( $this->modules as $module ) {
			$shortcodes[] = $module->shortcode;
		}
		return $shortcodes;
	}

	/**
	 * Load modules.
	 * 
	 * @since 1.0.0
	 * @access protected
	 */
	protected function load_modules() {
		
		$dir = get_template_directory() . '/tf/includes/modules';

		// Any core modules please list here
		$lists = array( 'site-logo', 'site-tagline', 'menu', 'searchform', 'author-box', 'category-title', 'category-description', 'category-image', 'archive-loop','list-posts', 'page-title', 'page-content','page-featured-image', 'next-prev-post', 'comments', 'icon', 'text', 'widget-area', 'widget', 'video', 'divider', 'image', 'template_part');

		foreach( $lists as $list ) {
			require_once( sprintf( '%s/%s/module.php', $dir, $list ) );
		}

		do_action( 'tf_modules_loaded' );
	}

	/**
	 * Custom CSS Fields.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $fields 
	 * @param object $instance 
	 * @return array
	 */
	public function custom_css_field( $fields, $instance ) {
		$fields['tf_module_custom_css_separator'] = array(
			'type' => 'separator'
		);
		$fields['tf_module_custom_css_class'] = array(
			'type'  => 'text',
			'class' => 'tf_input_width_80',
			'label' => __('Custom CSS Class', 'themify-flow')
		);
		return $fields;
	}
        
        /**
	 * Display inline block field
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $fields 
	 * @param object $instance 
	 * @return array
	 */
	public function display_inline_field( $fields, $instance ) {
                $fields['tf_module_display_inline_block_separator'] = array(
			'type' => 'separator'
		);
                $fields['tf_module_display_inline_block'] = array(
                        'type' => 'checkbox',
                        'label' => __( 'Display Inline', 'themify-flow' ),
                        'text' => __( 'Display this module inline (float left)', 'themify-flow' ),
		);
		return $fields;
	}
       

        /**
	 * Output the custom css in the module wrapper.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $classes 
	 * @param array $atts 
	 * @return array
	 */
	public function output_css_class( $classes, $atts ) {
		if ( isset( $atts['tf_module_custom_css_class'] ) && ! empty( $atts['tf_module_custom_css_class'] ) ) {
			$custom = explode( ' ', $atts['tf_module_custom_css_class'] );
			foreach( $custom as $class ) {
				$classes[] = sanitize_html_class( $class );
			}
		}
		return $classes;
	}
        
         /**
	 * Output the display inline block css class in the module wrapper.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $classes 
	 * @param array $atts 
	 * @return array
	 */
	public function display_inline_field_class( $classes, $atts ) {
		if ( isset( $atts['tf_module_display_inline_block'] ) && $atts['tf_module_display_inline_block'] ) {
			$classes[] = 'tf_module_inline_block';
		}
		return $classes;
	}
}

/** Initialize class */
$GLOBALS['tf_modules'] = new TF_Module_Loader();

/**
 * Register module hook.
 * 
 * Hook the module object when initialize so it can be accessible by module loader.
 * 
 * @since 1.0.0
 */
do_action( 'tf_register_modules' );