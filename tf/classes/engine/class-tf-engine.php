<?php
/**
 * Engine class
 * 
 * Set template selector based on right view.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Engine {

	/**
	 * Whether found template or not.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var boolean $render_template
	 */
	public $render_template = false;

	public $taxonomies = array();

	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'set_template_view' ) );
		add_action( 'tf_template_render_header', array( $this, 'render_header' ) );
		add_action( 'tf_template_render', array( $this, 'render' ) );
		add_action( 'tf_template_render_footer', array( $this, 'render_footer' ) );

		// Empty Section Text
		add_action( 'tf_template_empty_region_header', array( $this, 'empty_header_region' ) );
		add_action( 'tf_template_empty_region_sidebar', array( $this, 'empty_sidebar_region' ) );
		add_action( 'tf_template_empty_region_footer', array( $this, 'empty_footer_region' ) );
	}

	/**
	 * Render Template.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function render() {
		get_header();
		locate_template( 'tf/includes/templates/template-render-body.php', true, true );
		get_footer();
	}

	/**
	 * Render Header.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function render_header() {
		locate_template( 'tf/includes/templates/template-render-header.php', true, true );
	}

	/**
	 * Render Footer.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function render_footer() {
		locate_template( 'tf/includes/templates/template-render-footer.php', true, true );
	}

	/**
	 * Choose the right template for any page condition.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function set_template_view() {

		// bail it if in singular template and template part
		if ( is_singular( array( 'tf_template', 'tf_template_part' ) ) ) 
			return;

		global $TF_Layout, $TF;

		$found_template = false;
		$meta_value = 'archive';
		if ( is_archive() ) {
			$meta_value = 'archive';
		} else if ( is_page() || is_404() || ( ! is_home() && is_front_page() )  ) {
			$meta_value = 'page';
		} else if ( is_single() ) {
			$meta_value = 'single';
		}

		$templates = get_transient( 'tf_cached_template_assignment_' . $meta_value );

		if ( false === $templates ) {
			$args = array(
				'post_type' => 'tf_template',
				'posts_per_page' => -1,
				'order' => 'DESC',
				'orderby' => 'menu_order date',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'tf_template_type',
						'value' => $meta_value,
					),
					array(
						'key'     => 'associated_theme',
						'value' => $TF->active_theme->slug,
					)
				)
			);
			$query = new WP_Query( $args );
			$templates = $query->get_posts();

			// Cached meta data info as well
			if ( $templates ) {
				$metadatas = array(
					'tf_template_assign', 'tf_template_header_option', 'tf_template_sidebar_option',
					'tf_template_footer_option', 'tf_template_type'
				);
				foreach( $templates as $key => $template ) {
					foreach ( $metadatas as $meta ) {
						$templates[ $key ]->{$meta} = get_post_meta( $template->ID, $meta, true );	
					}
					$templates[ $key ]->tf_template_region_header  = get_post_meta( $template->ID, 'tf_template_region_header', true );
					$templates[ $key ]->tf_template_region_sidebar = get_post_meta( $template->ID, 'tf_template_region_sidebar', true );
					$templates[ $key ]->tf_template_region_footer  = get_post_meta( $template->ID, 'tf_template_region_footer', true );
				}
			}
			set_transient( 'tf_cached_template_assignment_' . $meta_value, $templates, 0 ); // no expired
		}

		if ( $templates ) {
			// Cached the taxonomy lists
			$taxonomies = get_taxonomies( array( 'public' => true ) );
			$exclude_tax = array( 'post_format', 'product_shipping_class' );

			// Exclude unnecessary taxonomies
			foreach( $exclude_tax as $tax ) {
				if ( isset( $taxonomies[ $tax ] ) ) 
					unset( $taxonomies[ $tax ] );	
			}
			$this->taxonomies = $taxonomies;

			// First check has template assignment as top priority
			foreach( $templates as $key => $template ) {
				$views = $template->tf_template_assign;
				if ( $views && isset( $views[ $meta_value ] ) ) {
					if ( $this->is_current_view( $meta_value, $views ) ) {
						$TF_Layout->setup_layout( $template );
						$this->render_template = true;
						$found_template = true;
						unset( $templates[ $key ] );
						break;
					}
					unset( $templates[ $key ] );
				}
			}

			if ( ! $found_template && count( $templates ) > 0 ) {
				$templates = array_values( $templates );
				$TF_Layout->setup_layout( $templates[0] );
				$this->render_template = true;
			}
		}
	}

	/**
	 * Logic template view.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $template_type 
	 * @param array $view 
	 * @return boolean
	 */
	public function is_current_view( $template_type = 'archive', $view ) {
		$visible = false;
		$query_object = get_queried_object();

		if ( ! empty( $view ) ) {

			switch ( $template_type ) {
				case 'archive':
					$archives_condition = ( is_home() || is_search() || is_date() || is_author() || is_year() || is_day() || is_month() || is_post_type_archive() );
					if ( is_category() && isset( $view[ $template_type ]['category']['all'] ) 
						|| is_tag() && isset( $view[ $template_type]['post_tag']['all'] ) 
						|| $archives_condition && isset( $view[ $template_type ]['archive']['all'] ) 
						|| is_tax() && isset( $view[ $template_type]['tax']['all'] )
					) {
						$visible = true;
					} else {

						// Check Term Taxonomy pages
						foreach( $this->taxonomies as $key => $tax ) {
							if ( isset( $view[ $template_type ][ $key ] ) && ! empty( $view[ $template_type ][ $key ] ) ) {
								if ( 'category' == $key && is_category( array_keys( $view[ $template_type ][ $key ] ) ) 
									|| 'post_tag' == $key && is_tag( array_keys( $view[ $template_type ][ $key ] ) ) 
									|| is_tax( $key, array_keys( $view[ $template_type ][ $key ] ) ) ) {
									$visible = true;
								}
							}
						}

						// Misc archive
						if ( ! empty( $view[ $template_type ]['archive'] ) ) {
							foreach( $view[ $template_type ]['archive'] as $function => $val ) {
								if ( function_exists( $function) ) {
									if ( call_user_func( $function ) ) {
										$visible = true;
									}
								}
							}
						}

						// Specific Taxonomy
						if ( ! empty( $view[ $template_type ]['tax'] ) ) {
							if ( is_tax( array_keys( $view[ $template_type ]['tax'] ) ) ) 
								$visible = true;
						}

						// Post Type archives
						if ( ! empty( $view[ $template_type ]['archive']['post_type'] ) ) {
							if ( is_post_type_archive( array_keys( $view[ $template_type ]['archive']['post_type'] ) ) ) {
								$visible = true;
							}
						}
					}
				break;

				case 'single':

					if ( has_category() && isset( $view[ $template_type ]['category']['all'] ) 
						|| is_singular() && isset( $view[ $template_type ]['post_type']['all'] ) 
						|| is_singular() && isset( $view[ $template_type ]['singular']['all'] ) 
					) {
						$visible = true;
					} else {

						// specific category
						if ( ! empty( $view[ $template_type ]['category'] ) ) {
							if ( in_category( array_keys( $view[ $template_type ]['category'] ) ) ) 
								$visible = true;
						}

						// specific post type
						if ( ! empty( $view[ $template_type ]['post_type'] ) ) {
							if ( is_singular( array_keys( $view[ $template_type ]['post_type'] ) ) ) 
								$visible = true;
						}

						// Specific post view
						if ( ! empty( $view[ $template_type ]['singular'] ) ) {
							foreach( $view[ $template_type ]['singular'] as $post_type => $post_data ) {
								if ( is_singular( $post_type ) && in_array( $query_object->post_name, array_keys( $post_data ) ) ) 
									$visible = true;
							}
						}

					}

				break;

				case 'page':

					if ( isset( $view[ $template_type ]['all'] ) ) {
						$visible = true;
					} else if ( is_404() && isset( $view[ $template_type ]['404'] ) ) {
						$visible = true;
					} else if ( ! is_home() && is_front_page() && isset( $view[ $template_type ]['is_front_page'] ) ) {
						$visible = true;
					} else if ( ! empty( $view[ $template_type ] ) ) {
						if ( is_page( array_keys( $view[ $template_type ] ) ) ) 
							$visible = true;
					}

				break;
			}
		}
		return $visible;
	}

	/**
	 * Empty Header section content.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function empty_header_region() {
		if ( TF_Model::is_template_editable() ) {
			echo sprintf( '<p>%s</p>', __('To auto display a header here, create a new Template Part and name it "Header".', 'themify-flow') );
		}
	}

	/**
	 * Empty Sidebar section content.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function empty_sidebar_region() {
		if ( TF_Model::is_template_editable() ) {
			echo sprintf( '<p>%s</p>', __('To auto display a sidebar here, create a new Template Part and name it "Sidebar".', 'themify-flow') );
		}
	}

	/**
	 * Empty Footer section content.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function empty_footer_region() {
		if ( TF_Model::is_template_editable() ) {
			echo sprintf( '<p>%s</p>', __('To auto display a footer here, create a new Template Part and name it "Footer".', 'themify-flow') );
		}
	}
}

/** Initialize class */
new TF_Engine();