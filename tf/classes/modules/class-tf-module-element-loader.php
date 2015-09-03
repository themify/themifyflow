<?php
/**
 * Module Element Loader class.
 * 
 * Register elements, get elements object.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Element_Loader {
	
	/**
	 * Lists elements.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @var array $elements
	 */
	protected $elements = array();

	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		do_action( 'tf_module_elements_loaded' );

		add_action( 'wp_footer', array( $this, 'load_tmpl' ) );
		add_action( 'admin_footer-post.php', array( $this, 'load_tmpl' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'load_tmpl' ) );
	}

	/**
	 * Register module.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param object &$module 
	 */
	public function register_element( &$module ) {
		$this->elements[ $module->slug ] = &$module;
	}

	/**
	 * Get all modules.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function get_elements() {
		return $this->elements;
	}

	/**
	 * Get a module object.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $slug 
	 * @return object
	 */
	public function get_element( $slug ) {
		if ( isset( $this->elements[ $slug ] ) ) {
			return $this->elements[ $slug ];
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
	public function get_element_shortcodes() {
		$shortcodes = array();
		foreach( $this->elements as $element ) {
			$shortcodes[] = $element->shortcode;
		}
		return $shortcodes;
	}

	public function get_elements_by_category( $category ) {
		$categories = $this->group_by_category();
		if ( isset( $categories[ $category ] ) ) {
			return $categories[ $category ];
		} else {
			return array();
		}
	}

	public function group_by_category() {
		$categories = array();
		foreach( $this->get_elements() as $element ) {
			$categories[ $element->category ][] = $element;
		}
		return $categories;
	}

	public function load_tmpl() {
		if ( TF_Model::is_tf_editor_active() || is_admin() ) {
			foreach( $this->elements as $element ) { ?>
			<script type="text/html" id="tmpl-tf_active_element_<?php echo $element->slug; ?>">
				<div class="tf_active_module" data-tf-module="<?php echo esc_attr( $element->slug ); ?>" data-tf-content="<?php echo esc_attr( $element->get_content_field() ); ?>">
					<div class="tf_back_module_top">
						<div class="tf_left">
							<span class="tf_back_active_module_title"><?php echo esc_attr( $element->name ); ?></span>
						</div>
						<!-- /tf_left -->
						<div class="tf_right">
							<a href="#" class="tf_module_btn tf_toggle_module"></a>
							<a href="#" class="tf_module_btn tf_delete_module"></a>
						</div>
						<!-- /tf_right -->
					</div>
					<!-- /tf_back_module_top -->
					<div class="tf_back_active_module_content">
						<?php echo TF_Form::render_element( $element->fields() ); ?>
					</div>
					<!-- /tf_back_module_content -->
				</div>
			</script>
			<?php
			}	
		}
	}
}

/** Initialize class */
$GLOBALS['tf_module_elements'] = new TF_Module_Element_Loader();

/**
 * Register module hook.
 * 
 * Hook the module object when initialize so it can be accessible by module loader.
 * 
 * @since 1.0.0
 */
do_action( 'tf_register_module_elements' );