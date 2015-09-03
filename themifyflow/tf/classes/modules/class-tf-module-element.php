<?php
/**
 * Abstract module element class.
 * 
 * Any module element class should be extend from this class.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
abstract class TF_Module_Element {
	
	/**
	 * Human friendly module name.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $name
	 */
	public $name;

	/**
	 * Module slug to use to identify module.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $slug
	 */
	public $slug;

	/**
	 * Shortcode name to use in Builder content.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $shortcode
	 */
	public $shortcode;

	/**
	 * Module description text.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $description
	 */
	public $description;

	/**
	 * Module Category.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $category
	 */
	public $category;

	/**
	 * Close type shortcode.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $close_type
	 */
	protected $close_type;

	protected $content_field;

	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @param array $params Module parameters.
	 */
	protected function __construct( $params ) {
		$params = wp_parse_args( $params, array(
			'name'        => '',
			'slug'        => '',
			'description' => '',
			'shortcode'   => '',
			'category'    => 'loop',
			'close_type'  => TF_Shortcodes::SELF_CLOSED
		) );
		
		$this->name        = $params['name'];
		$this->slug        = $params['slug'];
		$this->shortcode   = $params['shortcode'];
		$this->close_type  = $params['close_type'];
		$this->description = $params['description'];
		$this->category    = $params['category'];

		add_action( 'tf_register_module_elements', array( &$this, 'register' ) );

		if ( false !== $content_field = TF_Model::get_shortcode_content_field( $this->fields() ) ) {
			$this->content_field = $content_field;
		}
	}

	/**
	 * Register Module.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function register() {
		global $tf_module_elements;
			
		$tf_module_elements->register_element( $this );
	}

	/**
	 * Module fields parameters.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function fields() {
		return array();
	}

	/**
	 * Return attributes to shortcode string.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public function to_shortcode( $atts, $content = null ) {
		$shortcode = sprintf( '[%s', $this->shortcode );
		$shortcode .= count( $atts ) > 0 ? $this->parse_attr( $atts ) : '';
		$shortcode .= ']';
		$shortcode .= $this->close_type == TF_Shortcodes::ENCLOSED ? $content . sprintf( '[/%s]', $this->shortcode ) : '';
		return $shortcode;
	}

	/**
	 * Get close type shortcode.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_close_type() {
		return $this->close_type;
	}

	/**
	 * Return attributes array to shortcode params string.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @param array $attributes 
	 * @return string
	 */
	protected function parse_attr( $attributes ) {
		if ( is_string( $attributes ) ) {
			return ( ! empty( $attributes ) ) ? ' ' . trim( $attributes ) : '';
		}

		if ( is_array( $attributes ) ) {
			$attr = '';

			foreach ( $attributes as $key => $val ) {
				$attr .= ' ' . $key . '="' . $val . '"';
			}

			return $attr;
		}
	}

	public function get_content_field() {
		return $this->content_field;
	}
}