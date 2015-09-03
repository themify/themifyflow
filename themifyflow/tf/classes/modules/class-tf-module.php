<?php
/**
 * Abstract module class.
 * 
 * Any module class should be extend from this class.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
abstract class TF_Module {
	
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
			'category'    => 'global'
		) );
		
		$this->name        = $params['name'];
		$this->slug        = $params['slug'];
		$this->shortcode   = $params['shortcode'];
		$this->description = $params['description'];
		$this->category    = $params['category'];

		add_action( 'tf_register_modules', array( &$this, 'register' ) );
		add_shortcode( $this->shortcode, array( $this, 'shortcode_handler' ) );
	}

	/**
	 * Register Module.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function register() {
		global $tf_modules;
			
		$tf_modules->register_module( $this );
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
	 * Module fields getter.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function get_fields() {
		return apply_filters( 'tf_module_fields', $this->fields(), $this );
	}

	public function styles() {
		return array();
	}

	public function shortcode_handler( $atts, $content, $code ) {
               
                global $TF;
                if($TF->in_template_part &&  !TF_Model::is_template_page()){
                    if(is_array($this->category)){
                        $cat = array_intersect($this->category, array('archive','page','single'));
                        if(!empty($cat)){
                            $cat = current($cat);
                        }
                    }
                    else{
                        $cat = $this->category;
                    }
                    if(in_array($cat,array('archive','page','single')) && !is_admin()){

                        if($cat=='page' && !is_page()){
                           return FALSE;
                        }
                        elseif($cat=='single' && !is_single()){ 
                           return FALSE;
                        }
                        elseif($cat=='archive' && !is_search() && !is_tax() && !is_archive() &&  !is_post_type_archive())
                        { 
                            return FALSE;
                        }
                    }
                }
		$atts = apply_filters( 'tf_shortcode_atts', $atts, $code, $this );

		/**
		 * Put module wrapper markup here directly instead using apply_filters()
		 * since this wrapper markup is required for each modules.
		 */
		if ( ! isset( $atts['sc_id'] ) )
			$atts['sc_id'] = TF_Model::generate_block_id();

		$classes = apply_filters( 'tf_module_classes', array(
			'tf_module_wrapper', 'tf_module_block', 'tf_module_' . $this->slug,
			'tf_module_block_' . $atts['sc_id']
		), $atts );
		$wrapper_atts = apply_filters( 'tf_module_wrapper_atts', array(
			'class' => $classes
		), $atts, $content, $this );
		$wrapper_atts['class'] = implode( ' ', $wrapper_atts['class'] );
		$atts_output = '';
		foreach( $wrapper_atts as $key => $value ) {
			$atts_output .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
		}

		$pre = sprintf( '<div %s>', $atts_output );
		$after = '</div>';

		$output = $pre . $this->render_shortcode( $atts, $content ) . $after;
		return apply_filters( 'tf_shortcode_module_render', $output, $this->slug, $atts, $content );
	}

	/**
	 * Returns a list of all fields in a module with their type
	 *
	 * @return array
	 */
	public function get_fields_map( $fields ) {
		$list = array();
		foreach( $fields as $key => $value ) {
			if( isset( $value['fields'] ) ) {
				if( $value['type'] != 'multi' ) {
					$list[$key] = $value['type'];
				}
				$list = array_merge( $list, $this->get_fields_map( $value['fields'] ) );
			} else {
				$list[$key] = $value['type'];
			}
		}

		return $list;
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
		$shortcode .= $this->has_shortcode_content() ? $content . sprintf( '[/%s]', $this->shortcode ) : '';
		return $shortcode;
	}

	/**
	 * Check if the shortcode has shortcode content.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return boolean
	 */
	public function has_shortcode_content() {
		return in_array( 'content', array_keys( $this->get_fields() ) );
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
				$attr .= ' ' . $key . '="' . esc_attr( $val ) . '"';
			}

			return $attr;
		}
	}
}