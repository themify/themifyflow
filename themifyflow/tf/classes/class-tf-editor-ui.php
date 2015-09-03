<?php
/**
 * Builder UI
 * 
 * Builder UI Markup
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Editor_UI {
	public function __construct() {
		// Editable content elements
		if ( is_admin() ) {
			add_action( 'load-post.php', array( $this, 'editable_content_element_backend' ) );
			add_action( 'load-post-new.php', array( $this, 'editable_content_element_backend') );
		} else {
			add_action( 'init', array( $this, 'editable_content_element_frontend' ) );
		}
	}

	/**
	 * Add Filter: Wrap shortcodes with builder markups.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function editable_content_element_frontend() {
		if ( ! TF_Model::is_tf_styling_active() && TF_Model::is_tf_editor_active() ) {
			add_filter( 'tf_shortcode_module_render', array( $this, 'shortcode_module_render_frontend' ), 10, 4 );
		}
		add_filter( 'tf_shortcode_element_render', array( $this, 'shortcode_element_render' ), 10, 4 ); // put this outside condition TF_Model::is_tf_editor_active()
		add_filter( 'tf_shortcode_atts', array( $this, 'shortcode_atts_render_frontend' ), 10 );
	}

	/**
	 * Add Filter: Wrap shortcodes with builder markups.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function editable_content_element_backend() {
		add_filter( 'tf_shortcode_module_render', array( $this, 'shortcode_module_render_backend' ), 10, 4 );
		add_filter( 'tf_shortcode_atts', array( $this, 'shortcode_atts_render_backend' ), 10 );
		add_filter( 'tf_shortcode_element_render', array( $this, 'shortcode_element_render' ), 10, 4 );
	}

	/**
	 * Filter shortcode to added editable markup for module edit.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $output 
	 * @param string $module_slug 
	 * @param array $atts 
	 * @return string
	 */
	public function shortcode_module_render_frontend( $output, $module_slug, $atts, $content ) {
		global $TF, $tf_modules;

		$module = $tf_modules->get_module( $module_slug );
		if ( isset( $atts['editable_markup'] ) && 'true' == $atts['editable_markup'] && ! $TF->in_template_part && ! $TF->in_archive_loop ) {
                            $inline = isset($atts['tf_module_display_inline_block']) && $atts['tf_module_display_inline_block']?' tf_module_inline_block':'';
                            $output = sprintf( '<div class="tf_active_block active_module clearfix%s" data-tf-module-title="%s" data-tf-module="%s" data-tf-content="%s" data-tf-atts="%s">
				<div class="tf_active_block_overlay"></div>
				<div class="tf_active_block_menu tf_interface">
					<ul class="tf_active_block_menu_ul">
						<li class="tf_active_block_menu_li"><span class="ti-menu"></span>
							<ul>
								<li>
									<a class="tf_lightbox_link_module-edit" title="%s" href="#">
										<span class="ti-pencil"></span>
									</a>
								</li>
								<li>
									<a class="tf_lightbox_link_module-style" title="%s" href="#">
										<span class="ti-brush"></span>
									</a>
								</li>
								<li>
									<a class="tf_lightbox_link_module-duplicate" title="%s" href="#">
										<span class="ti-layers"></span>
									</a>
								</li>
								<li>
									<a class="tf_lightbox_link_module-delete" title="%s" href="#">
										<span class="ti-close"></span>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</div>
				<div class="tf_active_block_caption tf_interface">%s</div>
				<div class="tf_active_block_element">%s</div>
			</div>',
                        $inline,
			esc_attr( $module->name ),
			$module_slug,
			esc_attr( $content ),
			esc_attr( json_encode( tf_escape_atts( $atts ) ) ),
			__( 'Edit', 'themify-flow'),
			__( 'Style', 'themify-flow'),
			__( 'Duplicate', 'themify-flow'),
			__( 'Delete', 'themify-flow'),
			esc_attr( $module->name ),
			$output
			);
		}
		return $output;
	}

	/**
	 * Filter shortcode to added editable markup for module edit.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $output 
	 * @param string $module_slug 
	 * @param array $atts 
	 * @return string
	 */
	public function shortcode_module_render_backend( $output, $module_slug, $atts, $content ) {
		global $TF, $tf_modules;

		$module = $tf_modules->get_module( $module_slug );
		if ( isset( $atts['editable_markup'] ) && 'true' == $atts['editable_markup'] && ! $TF->in_template_part && ! $TF->in_archive_loop ) {
			$output = sprintf( '<div class="tf_module active_module" data-tf-module-title="%1$s" data-tf-module="%2$s" data-tf-content="%3$s" data-tf-atts="%4$s">
				<div class="tf_active_module_menu">
					<div class="menu_icon">
					</div>
					<ul class="tf_dropdown">
						<li>
							<a class="tf_lightbox_link_module-edit" title="%5$s" href="#">
								<span class="ti-pencil"></span> %5$s
							</a>
						</li>
						<li>
							<a class="tf_lightbox_link_module-duplicate" title="%6$s" href="#">
								<span class="ti-layers"></span> %6$s
							</a>
						</li>
						<li>
							<a class="tf_lightbox_link_module-delete" title="%7$s" href="#">
								<span class="ti-close"></span> %7$s
							</a>
						</li>
					</ul>
				</div>
				<div class="module_label">
					<strong class="module_name">%8$s</strong>
					<em class="module_excerpt"></em>
				</div>
			</div>',
			esc_attr( $module->name ),
			$module_slug,
			esc_attr( $content ),
			esc_attr( json_encode( tf_escape_atts( $atts ) ) ),
			__( 'Edit', 'themify-flow'),
			__( 'Duplicate', 'themify-flow'),
			__( 'Delete', 'themify-flow'),
			esc_attr( $module->name )
			);
		}
		return $output;
	}

	/**
	 * Filter shortcode to added editable markup for module edit.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $output 
	 * @param string $module_slug 
	 * @param array $atts 
	 * @return string
	 */
	public function shortcode_element_render( $output, $element_slug, $atts, $content ) {
		global $TF, $tf_module_elements;

		if ( $TF->in_builder_lightbox ) {
			$element = $tf_module_elements->get_element( $element_slug );
			
			if ( ! empty( $content ) ) 
				$atts = array_merge( $atts, array( $element->get_content_field() => $content ) );
			
			$form = TF_Form::render_element( $element->fields(), $atts );
			$output = sprintf( '<div class="tf_active_module" data-tf-module="%s" data-tf-content="%s">
					<div class="tf_back_module_top">
						<div class="tf_left">
							<span class="tf_back_active_module_title">%s</span>
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
						%s
					</div>
					<!-- /tf_back_module_content -->
				</div>',
			$element_slug,
			esc_attr( $element->get_content_field() ),
			esc_attr( $element->name ),
			$form
			);
		}
		return $output;
	}

	/**
	 * Filter shortcode atts params
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $out 
	 * @param array $pairs 
	 * @param array $atts 
	 * @return array
	 */
	public function shortcode_atts_render_frontend( $out ) {
		global $TF;

		if ( ! isset( $out['sc_id'] ) )
			$out['sc_id'] = TF_Model::generate_block_id();

		if ( ! TF_Model::is_tf_styling_active() && TF_Model::is_tf_editor_active() && ! $TF->in_template_part && ! $TF->in_archive_loop )	
			$out['editable_markup'] = 'true';
		return $out;
	}

	/**
	 * Filter shortcode atts params
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $out 
	 * @param array $pairs 
	 * @param array $atts 
	 * @return array
	 */
	public function shortcode_atts_render_backend( $out ) {
		global $TF;

		if ( ! isset( $out['sc_id'] ) )
			$out['sc_id'] = TF_Model::generate_block_id();

		$out['editable_markup'] = 'true';
		return $out;
	}

	/**
	 * Force editable markup to be true.
	 * 
	 * @since 1.0.0
	 */
	public function force_editable_shortcode( $mode = 'frontend' ) {
		if ( 'frontend' == $mode ) {
			add_filter( 'tf_shortcode_module_render', array( $this, 'shortcode_module_render_frontend' ), 10, 4 );
		} else {
			add_filter( 'tf_shortcode_module_render', array( $this, 'shortcode_module_render_backend' ), 10, 4 );
		}
		add_filter( 'tf_shortcode_element_render', array( $this, 'shortcode_element_render' ), 10, 4 );
		add_filter( 'tf_shortcode_atts', array( $this, 'shortcode_atts_render_backend' ), 10 );
	}

	public function shortcode_atts_generate_uid( $out ) {
		if ( ! isset( $out['sc_id'] ) )
			$out['sc_id'] = TF_Model::generate_block_id();
		return $out;
	}

	/**
	 * Force read shortcodes.
	 * 
	 * @since 1.0.0
	 */
	public function force_read_shortcode() {
		add_filter( 'tf_shortcode_atts', array( $this, 'shortcode_atts_generate_uid' ), 10 );
	}
}

$GLOBALS['tf_editor_ui'] = new TF_Editor_UI();