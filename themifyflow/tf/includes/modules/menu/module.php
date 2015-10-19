<?php

class TF_Module_Menu extends TF_module {

	/**
	 * Inline scripts used by menu module added to footer
	 */
	var $footer_scripts = '';

	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Menu', 'themify-flow' ),
			'slug' => 'menu',
			'description' => __( 'Menu', 'themify-flow' ),
			'shortcode' => 'tf_menu',
			'category' => array('content','global')
		) );

		include_once( dirname( __FILE__ ) . '/class-menu-dropdown.php' );
		add_action( 'after_setup_theme', array( $this, 'register_default_menu_location' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'footer_scripts' ), 99 );
	}

	public function fields() {
		$menu_option = array(
			array( 'value' => '__default', 'name' => __( 'Default Menu', 'themify-flow' ) )
		);
		$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
		if( ! empty( $menus ) ) { foreach( $menus as $menu ) {
			$menu_option[] = array( 'name' => $menu->name, 'value' => $menu->slug );
		} }

		return apply_filters( 'tf_module_menu_fields', array(
			'nav_menu' => array(
				'type' => 'select',
				'label' => __( 'Select Menu', 'themify-flow' ),
				'options' => $menu_option,
				'description' => '<br/>' . sprintf( __( 'Add menus in <a href="%s">WP > Appearance > Menus</a>', 'themify-flow' ), admin_url( 'nav-menus.php' ) )
			),
			'mobile_menu' => array(
				'label' => __( 'Mobile Menu', 'themify-flow' ),
				'type' => 'radio',
				'options' => array(
					array( 'name' => __( 'Yes', 'themify-flow' ), 'value' => 'yes', 'selected' => true ),
					array( 'name' => __( 'No', 'themify-flow' ), 'value' => 'no' ),
				),
				'toggleable' => array( 'target_class' => 'mobile_menu' )
			),
			'mobile_menu_breakpoint' => array(
				'type' => 'number',
				'label' => __( 'Trigger Point', 'themify-flow' ),
				'class' => 'tf_input_width_20',
				'default' => 600,
				'description' => 'px<br/>' . __( 'Trigger mobile menu at certain breakpoint (viewport width)', 'themify-flow' ),
				'row_class' => 'mobile_menu mobile_menu-yes',
			),
			'mobile_menu_label' => array(
				'type' => 'text',
				'label' => __( 'Mobile Menu label', 'themify-flow' ),
				'class' => 'tf_input_width_30',
				'default' => __( 'Menu', 'themify-flow' ),
				'row_class' => 'mobile_menu mobile_menu-yes',
			),
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
		return apply_filters( 'tf_module_menu_styles', array(
			'tf_module_menu_container' => array(
				'label' => __( 'Menu Container', 'themify-flow' ),
				'selector' => '.tf_menu',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_menu_link' => array(
				'label' => __( 'Menu Link', 'themify-flow' ),
				'selector' => '.tf_menu a',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_menu_link_hover' => array(
				'label' => __( 'Menu Link Hover', 'themify-flow' ),
				'selector' => '.tf_menu a:hover',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_menu_current_link' => array(
				'label' => __( 'Current Link', 'themify-flow' ),
				'selector' => '.tf_menu .current_page_item a, .tf_menu .current-menu-item a',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_menu_active_hover' => array(
				'label' => __( 'Active Link Hover', 'themify-flow' ),
				'selector' => '.tf_menu .current_page_item a:hover, .tf_menu .current-menu-item a:hover',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_menu_dropdown_container' => array(
				'label' => __( 'Dropdown Container', 'themify-flow' ),
				'selector' => '.tf_menu ul',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_menu_dropdown_link' => array(
				'label' => __( 'Dropdown Link', 'themify-flow' ),
				'selector' => '.tf_menu ul a, .tf_menu .current_page_item ul a, .tf_menu ul .current_page_item a, .tf_menu .current-menu-item ul a, .tf_menu ul .current-menu-item a',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_menu_dropdown_link_hover' => array(
				'label' => __( 'Dropdown Link Hover', 'themify-flow' ),
				'selector' => '.tf_menu ul a:hover, .tf_menu .current_page_item ul a:hover, .tf_menu ul .current_page_item a:hover, .tf_menu .current-menu-item ul a:hover, .tf_menu ul .current-menu-item a:hover',
				'basic_styling' => array( 'border', 'font', 'margin' ),
			),
			'tf_module_menu_mobile_menu_container' => array(
				'label' => __( 'Mobile Menu Container', 'themify-flow' ),
				'selector' => '.tf_mobile_menu_wrap',
				'basic_styling' => array( 'background', 'margin', 'border' ),
			),
			'tf_module_menu_mobile_menu_label' => array(
				'label' => __( 'Mobile Menu Label', 'themify-flow' ),
				'selector' => '.tf_mobile_menu_wrap select',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_menu_mobile_menu_icon' => array(
				'label' => __( 'Mobile Menu Icon', 'themify-flow' ),
				'selector' => '.tf_mobile_menu_wrap:after',
				'basic_styling' => array( 'font' ),
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
		extract( shortcode_atts( array(
			'nav_menu'               => '__default',
			'mobile_menu'            => 'yes',
			'mobile_menu_breakpoint' => 600,
			'mobile_menu_label' => __( 'Menu', 'themify-flow' ),
		), $atts, $this->shortcode ) );

		ob_start(); ?>

		<?php
		if( '__default' == $nav_menu ) {
			if( has_nav_menu( 'default_menu' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'default_menu',
					'container' => false,
					'menu_class' => 'tf_menu',
				) );
			} else {
				echo '<ul class="tf_menu tf_pages_menu">';
				wp_list_pages( 'title_li=' );
				echo '</ul>';
			}
		} else {
			wp_nav_menu( array(
				'menu' => $nav_menu,
				'container' => false,
				'menu_class' => 'tf_menu',
			) );
		}

		// mobile menu
		if( 'yes' == $mobile_menu ) {
			if( '__default' == $nav_menu ) {
				if( has_nav_menu( 'default_menu' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'default_menu',
						'container' => false,
						'walker'         => new Walker_Nav_Menu_TF_Dropdown(),
						'items_wrap'     => '<div class="mobile-menu"><form><div class="tf_mobile_menu_wrap"><select onchange="if (this.value) window.location.href=this.value"><option value="">' . $mobile_menu_label . '</option>%3$s</select></div></form></div>',
					) );
				} else {
					echo '<div class="mobile-menu">';
					$this->wp_dropdown_pages( array(), $mobile_menu_label );
					echo '</div>';
				}
			} else {
				wp_nav_menu( array(
					'menu' => $nav_menu,
					'container' => false,
					'walker'         => new Walker_Nav_Menu_TF_Dropdown(),
					'items_wrap'     => '<div class="mobile-menu"><form><div class="tf_mobile_menu_wrap"><select onchange="if (this.value) window.location.href=this.value"><option value="">' . $mobile_menu_label . '</option>%3$s</select></div></form></div>',
				) );
			}
			?>
			<style>
			@media (max-width: <?php echo $mobile_menu_breakpoint; ?>px) {
				.tf_module_block_<?php echo $atts['sc_id']; ?>.tf_module_menu .mobile-menu {
					display: block;
				}
				.tf_module_block_<?php echo $atts['sc_id']; ?>.tf_module_menu .tf_menu {
					display: none;
				}
			}
			</style>
		<?php } ?>

		<?php

		// on touch devices add dropdown script
		if( wp_is_mobile() ) {
			wp_enqueue_script( 'themify-dropdown' );
			$this->footer_scripts .= sprintf( 'jQuery(function(){ jQuery( ".tf_module_block_%s.tf_module_menu .tf_menu" ).themifyDropdown() });', $atts['sc_id'] );
		}

		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Modified version of wp_dropdown_pages where the select field redirects to the actual page.
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $args Optional. Override default arguments.
	 * @return string HTML content, if not displaying.
	 */
	function wp_dropdown_pages( $args = '', $mobile_menu_label ) {
		$defaults = array(
			'depth' => 0, 'child_of' => 0,
			'selected' => 0, 'echo' => 1,
			'name' => 'page_id', 'id' => '',
			'show_option_none' => '', 'show_option_no_change' => '',
			'option_none_value' => ''
		);

		$r = wp_parse_args( $args, $defaults );

		$pages = get_pages( $r );
		$output = '';
		// Back-compat with old system where both id and name were based on $name argument
		if ( empty( $r['id'] ) ) {
			$r['id'] = $r['name'];
		}

		if ( ! empty( $pages ) ) {
			$output = "<div class=\"tf_mobile_menu_wrap\"><select onchange=\"if (this.value) window.location.href=this.value\" name='" . esc_attr( $r['name'] ) . "' id='" . esc_attr( $r['id'] ) . "'>\n";
			$output .= '<option value="">' . $mobile_menu_label . '</option>';
			if ( $r['show_option_no_change'] ) {
				$output .= "\t<option value=\"-1\">" . $r['show_option_no_change'] . "</option>\n";
			}
			if ( $r['show_option_none'] ) {
				$output .= "\t<option value=\"" . esc_attr( $r['option_none_value'] ) . '">' . $r['show_option_none'] . "</option>\n";
			}
			$output .= $this->walk_page_dropdown_tree( $pages, $r['depth'], $r );
			$output .= "</select></div>\n";
		}

		/**
		 * Filter the HTML output of a list of pages as a drop down.
		 *
		 * @since 2.1.0
		 *
		 * @param string $output HTML output for drop down list of pages.
		 */
		$html = apply_filters( 'wp_dropdown_pages', $output );

		if ( $r['echo'] ) {
			echo $html;
		}
		return $html;
	}

	/**
	 * Retrieve HTML dropdown (select) content for page list.
	 *
	 * @uses Walker_PageDropdown to create HTML dropdown content.
	 * @since 2.1.0
	 * @see Walker_PageDropdown::walk() for parameters and return description.
	 */
	function walk_page_dropdown_tree() {
		$args = func_get_args();
		if ( empty($args[2]['walker']) ) // the user's options are the third parameter
			$walker = new Walker_TF_PageDropdown;
		else
			$walker = $args[2]['walker'];

		return call_user_func_array( array( $walker, 'walk'), $args );
	}

	public function register_default_menu_location() {
		register_nav_menus( array(
			'default_menu' => __( 'Default Menu', 'themify-flow' ),
		) );
	}

	public function wp_enqueue_scripts() {
		global $TF;
		wp_register_script( 'themify-dropdown', $TF->framework_uri() . '/assets/js/themify.dropdown.js', array( 'jquery' ), $TF->get_version(), true );
	}

	public function footer_scripts() {
		if( ! empty( $this->footer_scripts ) ) {
			echo sprintf( '<script>%s</script>', $this->footer_scripts );
		}
	}
}

new TF_Module_Menu();