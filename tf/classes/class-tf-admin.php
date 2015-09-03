<?php
/**
 * Framework admin class.
 * 
 * Framework administration pages and menus
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Admin {

	/**
	 * Admin page slug.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @var string $slug
	 */
	protected $slug = 'tf-settings';

	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'render_javascript_tmpl' ) );

		add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_head', array( $this, 'remove_about_menu' ) );
		add_action( 'after_setup_theme', array( $this, 'activating_theme' ) );
                add_action( 'tf_modules_loaded',array('TF_Template_Options','get_instance')); 
		add_filter( 'pre_get_posts', array( $this, 'filter_theme_query' ) );
		add_filter( 'views_edit-tf_template', array( $this, 'filter_views_edit' ) );
		add_filter( 'views_edit-tf_template_part', array( $this, 'filter_views_edit' ) );

		add_action( 'after_setup_theme', array( $this, 'redirect_to_front_end_edit' ) );

		add_filter( 'admin_body_class', array( $this, 'body_class'), 10 );

		add_filter( 'tf_admin_enqueue_scripts_condition', array( $this, 'app_scripts_logic_view' ), 10, 2 );

		add_action( 'load-customize.php', array( $this, 'disable_builder_editor' ) );
	}

	/**
	 * Enqueue admin scripts and styles.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_scripts( $hook_suffix ) {

		$screen = get_current_screen();

		// Do nothing if it's a Flow admin screen or an entry edit screen.
		if ( ! ( in_array( $screen->id, array( 'toplevel_page_tf-settings', 'themify-flow_page_tf-themes', 'edit-tf_template', 'edit-tf_template_part', 'dashboard_page_tf-about', 'nav-menus' ) ) 
				||
				in_array( $screen->base, array( 'post' ) ) ) ) {
			return;
		}

		global $TF, $pagenow;

		// FontAwesome uses the same handle than Themify Builder to prevent double loading the fonts
		wp_enqueue_style( 'tf-icon-font', $TF->framework_uri() . '/assets/css/fontawesome/css/font-awesome.min.css', array(), '4.3' );
		wp_enqueue_style( 'tf-icons', $TF->framework_uri() . '/assets/css/themify-icons/themify-icons.css', array(), $TF->get_version() );
		wp_enqueue_style( 'tf-admin-ui', $TF->framework_uri() . '/assets/css/tf-admin.css', array(), $TF->get_version() );
		wp_enqueue_style( 'tf-minicolors-css', $TF->framework_uri() . '/assets/css/jquery.minicolors.css', array(), $TF->get_version() );

		$load_depend_scripts = array(
			'underscore',
			'backbone',
			'wp-util',
			'jquery-ui-core',
			'jquery-ui-tabs',
			'jquery-ui-droppable', 
			'jquery-ui-sortable',
			'plupload-all',
			'media-upload',
			'jquery-ui-dialog',
			'wpdialogs',
			'wpdialogs-popup',
			'wplink',
			'editor',
			'quicktags',
		);

		$load_vendor_scripts = array(
			'tf-minicolors-js' => '/jquery.minicolors.js'
		);

		$load_app_scripts = array(
			'tf-app-js' => '/tf.js',
			'tf-util-js' => '/utils.js',
			'tf-view-loader-js' => '/views/loader.js',
			'tf-view-lightbox-js' => '/views/lightbox.js'
		);
		
		if ( apply_filters( 'tf_admin_enqueue_scripts_condition', false, $hook_suffix ) ) {
			$load_app_scripts = array_merge( $load_app_scripts, array(
				'tf-utility-js' => '/models/utility.js',
				'tf-model-element-style-js' => '/models/elementstyle.js',
				'tf-model-template-js' => '/models/template.js',
				'tf-collection-element-styles-js' => '/collections/elementstyles.js',
				'tf-mixins-builder-js' => '/mixins/builder.js',
				'tf-view-builder-js' => '/views/builder.js',
				'tf-view-builder-element-js' => '/views/builderelement.js',
				'tf-setup-js' => '/admin-setup.js'	
			));	
		}

		if( function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		foreach( $load_depend_scripts as $script ) {
			wp_enqueue_script( $script );
		}

		foreach( $load_vendor_scripts as $handle => $script ) {
			wp_enqueue_script( $handle, $TF->framework_uri() . '/assets/js/vendor' . $script , array( 'jquery' ), $TF->get_version(), true );
		}

		foreach( $load_app_scripts as $handle => $script ) {
			wp_enqueue_script( $handle, $TF->framework_uri() . '/assets/js/tf' . $script , array( 'jquery' ), $TF->get_version(), true );
		}

		wp_enqueue_script( 'tf-admin-js', $TF->framework_uri() . '/assets/js/tf-admin.js', array( 'jquery' ), $TF->get_version(), true );

		$operations = array( 'add', 'duplicate', 'import', 'replace', 'edit' );
		$captions = array();
		foreach ($operations as $op ) {
			$captions['title_' . $op . '_theme' ] = sprintf( __('%s Theme'), ucfirst( $op ) );
			$captions['title_' . $op . '_template' ] = sprintf( __('%s Template'), ucfirst( $op ) );
			$captions['title_' . $op . '_template_part' ] = sprintf( __('%s Template Part'), ucfirst( $op ) );
		}
		$localize_app = array(
			'nonce' => wp_create_nonce( 'tf_nonce' ),
			'post_id' => get_the_ID(),
			'post_type' => get_post_type( get_the_ID() ),
			'module_delete' => __('Are you sure to delete this module?', 'themify-flow' ),
			'sub_row_delete' => __('Are you sure to delete this sub row?', 'themify-flow' ),
			'row_delete' => __('Are you sure to delete this row?', 'themify-flow' ),
			'drop_module_text' => __('drop module here', 'themify-flow' ),
			'row_option_title' => __('Row Options', 'themify-flow'),
			'base_path' => $TF->framework_path(),
			'base_uri' => $TF->framework_uri(),
			'template_type' => get_post_meta( get_the_ID(), 'tf_template_type', true ),
			'replace_confirm' => __('This import will override all current data. Press OK to continue.', 'themify-flow'),
			'isTouch' => wp_is_mobile()
		);

		wp_localize_script( 'tf-app-js', '_tf_app', array_merge( $localize_app, $captions ) );

		wp_localize_script( 'tf-app-js', '_tf_app_plupload', $TF->get_plupload_settings() );

		do_action( 'tf_enqueue_scripts' );
	}

	/**
	 * Render _.underscore javascript templates.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function render_javascript_tmpl() {
		global $TF;
		include_once( sprintf( '%s/includes/tmpl/tmpl-backend.php', $TF->framework_path() ) );
	}

	/**
	 * Register administration menu.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function register_menu_page() {
		global $TF, $submenu;

		add_menu_page( __('Themify Flow', 'themify-flow'), __('Themify Flow', 'themify-flow'), 'manage_options', $this->slug, array( $this, 'render_settings' ), $TF->framework_uri() . '/assets/img/favicon.png', '61.3' );
		add_submenu_page( $this->slug, __( 'Flow Themes'), __('Flow Themes'), 'manage_options', 'tf-themes', array( $this, 'render_theme_page' ) );
		add_submenu_page( $this->slug, __( 'Flow Templates', 'themify-flow' ), __( 'Flow Templates', 'themify-flow' ), 'manage_options', 'edit.php?post_type=tf_template' );
		add_submenu_page( $this->slug, __( 'Flow Template Parts', 'themify-flow' ), __( 'Flow Template Parts', 'themify-flow' ), 'manage_options', 'edit.php?post_type=tf_template_part' );
		add_submenu_page( $this->slug, __( 'Global Styling', 'themify-flow' ), __( 'Global Styling', 'themify-flow' ), 'manage_options', 'admin.php?page=styling-panel-frontend' );
		add_submenu_page( $this->slug, __( 'Custom CSS', 'themify-flow' ), __( 'Custom CSS', 'themify-flow' ), 'manage_options', 'admin.php?page=custom-css-frontend' );

		if ( isset( $submenu['tf-settings'][0][0] ) ) 
			$submenu['tf-settings'][0][0] = __('Settings', 'themify-flow');

		// About page
		add_dashboard_page(
			__('Welcome to Themify Flow', 'themify-flow'),
			__('Welcome to Themify Flow', 'themify-flow'),
			'read',
			'tf-about',
			array( $this, 'about_screen' )
		);
	}

	private $sections_and_settings;

	/**
	 * Register settings to display in settings page.
	 * 
	 * @since 1.0.0
	 */
	public function register_settings() {
		global $TF;
		include_once $TF->framework_path() . '/classes/class-tf-settings.php';

		$this->sections_and_settings = apply_filters( 'tf_settings_and_sections', TF_Settings::get_sections_and_settings(), $this );

		register_setting( 'tf-settings', 'themify-flow' );

		foreach ( $this->sections_and_settings as $key => $block ) {

			$section = $this->slug . $key;

			add_settings_section( $section, $block['title'], $block['callback'], $section );

			foreach ( $block['fields'] as $field ) {
				add_settings_field(
					$this->slug . $field['id'],
					$field['label'],
					array( 'TF_Settings', $field['type'] ),
					$section,
					$section,
					array(
						'id' 		  => $field['id'],
						'description' => isset( $field['description'] ) ? $field['description'] : null,
						'default' 	  => isset( $field['default'] ) ? $field['default'] : null,
						'class' 	  => isset( $field['class'] ) ? $field['class'] : null,
						'options' 	  => isset( $field['options'] ) ? $field['options'] : null,
					)
				);
			}

		}
	}

	/**
	 * Output markup for settings page. Displays settings registered in TF_Admin::register_settings.
	 *
	 * @since 1.0.0
	 */
	public function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'themify-flow' ) );
		}
		?>
		<div class="wrap themify-flow-settings">
			<h2>
				<?php _e( 'Themify Flow Settings', 'themify-flow' ); ?> 
			</h2>
			<h2 class="nav-tab-wrapper">
				<?php foreach( $this->sections_and_settings as $key => $section ) : ?>
					<a href="#section-<?php echo $key; ?>" class="nav-tab <?php if( $key == 'general' ) echo 'nav-tab-active';?>"><?php echo $section['title']; ?></a>
				<?php endforeach; ?>
			</h2>
			<form action="options.php" method="post">
				<?php settings_fields( $this->slug ); ?>

				<?php foreach( $this->sections_and_settings as $key => $section ) : ?>

					<div id="section-<?php echo esc_attr( $key );?>" class="setting-<?php echo esc_attr( $key ); ?>-wrap setting-tab-wrap">
						<?php do_settings_sections( $this->slug . $key ); ?>
					</div>

				<?php endforeach; ?>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php

		do_action( 'tf_after_settings' );
	}

	/**
	 * Fires when theme activated.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function activating_theme() {
		global $pagenow;
		
		if ( isset( $_GET['activated'] ) && 'themes.php' == $pagenow ) {
			add_action( 'admin_init', array( $this, 'theme_first_run' ), 20 );
		}
	}

	/**
	 * Fires when theme first run.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function theme_first_run() {
		// Flush permalink
		flush_rewrite_rules();

		// Generate default Theme, Template, and Template Part
		if ( ! TF_Model::theme_exists() ) {
			$this->import_base_theme();
		}

		// Redirect to about screen
		wp_safe_redirect( esc_url_raw( add_query_arg( array( 'page' => 'tf-about' ), admin_url( 'index.php' ) ) ) );
	}

	/**
	 * About page.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function about_screen() {
		global $TF;
		include_once( sprintf( '%s/includes/templates/template-admin-about.php', $TF->framework_path() ) );
	}

	/**
	 * Remove about page menu from dashboard.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function remove_about_menu() {
		remove_submenu_page( 'index.php', 'tf-about' );
	}

	/**
	 * Filter theme queries.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param object $query 
	 * @return object
	 */
	function filter_theme_query( $query ) {
		if ( $query->is_admin && $query->is_main_query() && ( 'tf_template' == $query->get('post_type') || 'tf_template_part' == $query->get('post_type') ) ) {
			global $TF;
			$query->set( 'meta_query', array(
				array(
					'key' => 'associated_theme',
					'value' => $TF->active_theme->slug
				)
			) );
		}
		return $query;
	}

	public function filter_views_edit( $views ) {
		global $current_screen;
		switch ( $current_screen->id ) {
			case 'edit-tf_template':
				$views = TF_Model::manipulate_views_count( 'tf_template', $views );
			break;
			
			case 'edit-tf_template_part':
				$views = TF_Model::manipulate_views_count( 'tf_template_part', $views );
			break;
		}
		return $views;
	}

	public function redirect_to_front_end_edit() {
		if ( isset( $_GET['page'] ) ) {
			if ( 'styling-panel-frontend' === $_GET['page'] ) {
				wp_safe_redirect( add_query_arg( array( 'tf' => 1, 'tf_global_styling' => 1 ), home_url('/') ) );
				die();
			} elseif ( 'custom-css-frontend' === $_GET['page'] ) {
				wp_safe_redirect( add_query_arg( array( 'tf' => 1, 'tf_custom_css' => 1 ), home_url('/') ) );
				die();
			}
		}
	}

	public function render_theme_page() {
		global $TF;
		include_once( sprintf( '%s/includes/templates/template-admin-themes.php', $TF->framework_path() ) );	
	}

	/**
	 * Import base theme data.
	 * 
	 * @since 1.0.0
	 * @access private
	 */
	private function import_base_theme() {
		global $TF;

		$zip_file = $TF->framework_path() . '/includes/data/theme-base.zip';
		$filename = $TF->framework_path() . '/theme_export.xml';
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		WP_Filesystem();
		global $wp_filesystem;

		if ( $wp_filesystem->exists( $zip_file ) ) {
			
			unzip_file( $zip_file, $TF->framework_path() );

			if( $wp_filesystem->exists( $filename ) ) {
				// Remove function hooked in class-tf-engine-style-loader.php
				remove_action( 'tf_import_end', array( 'TF_Model', 'create_stylesheets' ) );
				add_action( 'tf_import_end', array( $this, 'set_initial_active_theme' ) );

				$import = new TF_Import();
				$import->fetch_attachments = true;
				$import->base_theme = true;
				$import->import( $filename );
				$wp_filesystem->delete( $filename );
			}
		}
	}

	/**
	 * Set active theme.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function set_initial_active_theme() {
		global $wpdb;
		$theme = $wpdb->get_row( "SELECT ID FROM $wpdb->posts WHERE post_type='tf_theme' AND post_status='publish'" );
		if ( ! is_null( $theme ) ) {
			TF_Model::set_active_theme( $theme->ID );
			TF_Model::create_stylesheets( $theme->ID );
		}
	}

	/**
	 * Add body classes.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $classes 
	 * @return string
	 */
	public function body_class( $classes ) {
		global $pagenow, $typenow;
		
		$pages = array( 'tf-settings', 'tf-themes' );
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $pages ) || in_array( $pagenow, array( 'post.php', 'edit.php' ) ) ) {
			$classes .= 'tf_admin';
		}
		return $classes;
	}

	/**
	 * App scripts view logic.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param boolean $return 
	 * @param string $hook_suffix 
	 * @return boolean
	 */
	public function app_scripts_logic_view( $return, $hook_suffix ) {
		$screen = get_current_screen();

		if ( in_array( $screen->id, array( 'tf_template', 'tf_template_part' ) ) ) 
			return true;
		return $return;
	}

	/**
	 * Disable builder frontend editor in Customizer Preview.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function disable_builder_editor() {
		parse_str( $_SERVER['QUERY_STRING'], $query_string);
		if ( isset( $query_string['url'] ) ) {
			parse_str( parse_url( $query_string['url'], PHP_URL_QUERY), $query_page );
			if ( isset( $query_page['tf'] ) ) {
				$replace_url = remove_query_arg( 'tf', $query_string['url'] );
				$replace_url = remove_query_arg( 'tf_source_uri', $replace_url );
				$query_string['return'] = urlencode( $query_string['url'] );
				$query_string['url'] = $replace_url;

				$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				$current_url = remove_query_arg( 'url', $current_url );
				$new_url = add_query_arg( $query_string, $current_url );
				wp_safe_redirect( esc_url_raw( $new_url ) );
			}
		}
	}
}

/** Initialize class */
new TF_Admin();