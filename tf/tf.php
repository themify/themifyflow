<?php
/**
 * Main framework class.
 *
 * Setup framework environtment, load main framework classes and functions.
 *
 * @package ThemifyFlow
 * @since 1.0.0
 */
final class Themify_Flow {
	/**
	 * Framework current version number.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @var string $version
	 */
	protected $version = '1.0.0';

	/**
	 * Get active theme object.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var string $active_theme
	 */
	public $active_theme;

	/**
	 * Check current functions calling in template_part scope.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var boolean $in_template_part
	 */
	public $in_template_part = false;

	/**
	 * Check current state if on builder lightbox.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var boolean $in_builder_lightbox
	 */
	public $in_builder_lightbox = false;

	/**
	 * Check current state if inside archive / single loop
	 * 
	 * @since 1.0.0
	 * @access public
	 * @var boolean $in_archive_loop
	 */ 
	public $in_archive_loop = false;

	/**
	 * Initial loader.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct(){
		// Auto loader
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}
		spl_autoload_register( array( $this, 'autoload' ) );

		// Define constants
		//$this->define_constants();

		// Include required files
		$this->includes();

		// Hooks
		add_action( 'after_setup_theme', array( $this, 'setup_framework' ) );
		//add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( 'TF_Shortcodes', 'init' ) );
		//add_action( 'widgets_init', array( $this, 'include_widgets' ) );
            
	}

	/**
	 * Auto Load php classes.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $classname PHP Classname.
	 */
	public function autoload( $classname ) {
		$path  = null;
		$class = strtolower( $classname );
		$filename = 'class-' . strtolower( str_replace( '_', '-', $classname ) ) . '.php';

		if ( strpos( $class, 'tf_module' ) === 0 ) {
			$path = $this->framework_path() . '/classes/modules/';
		} elseif ( strpos( $class, 'tf_engine' ) === 0 ) {
			$path = $this->framework_path() . '/classes/engine/';
		} elseif ( strpos( $class, 'tf_import_content_builder' ) === 0 ) {
			$path = $this->framework_path() . '/classes/import-export/';
			$filename = 'class-tf-import.php';
		} elseif ( strpos( $class, 'tf_import' ) === 0 ) {
			$path = $this->framework_path() . '/classes/import-export/';
		} elseif ( strpos( $class, 'tf_interface' ) === 0 ) {
			$path = $this->framework_path() . '/classes/theme-elements/';
		} else {
			$path = $this->framework_path() . '/classes/';
		}

		$class_path = $path . $filename;
		
		if ( file_exists( $class_path ) )
			include_once $class_path;
		return;
	}

	/**
	 * Fires on init action.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {
		// Before init action
		do_action( 'before_tf_init' );

		// Add any action on init here

		// Init action
		do_action( 'tf_init' );
	}

	/**
	 * Define framework constansts.
	 * 
	 * @since 1.0.0
	 * @access private
	 */
	private function define_constants() {
		if ( ! defined( 'TF_FRAMEWORK_URI' ) ) {
			define( 'TF_FRAMEWORK_URI', $this->framework_uri() );
		}
	}

	/**
	 * Setup framework environment.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function setup_framework() {
		$theme = wp_get_theme();
		$this->version = is_child_theme() ? $theme->parent()->Version : $theme->display( 'Version' );

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Load theme domain
		load_theme_textdomain( 'themify-flow', $this->framework_path() . '/languages' );

		// Get active theme
		$this->active_theme = new TF_Engine_Theme_Loader();

		add_theme_support( 'menus' );
		add_theme_support( 'widgets' );

		if ( function_exists( '_wp_render_title_tag' ) ) {
			// Add Title Tag support
			add_theme_support( 'title-tag' );
		} else {
			// Fallback WP Title
			add_action( 'wp_head', array( $this, 'title_tag_fallback' ) );
		}

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_theme_support( 'woocommerce' );
		}

		// Check for updates
		$this->check_update();

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			// Load commands for wp-cli
			include $this->framework_path() . '/includes/utilities/class-wp-cli.php';
		}

		// Allow uploading .xml file
		add_filter( 'upload_mimes', array( $this, 'register_mime_types' ) );
                do_action('tf_theme_setup');
	}

	/**
	 * Remove last part of title to keep the traditional Themify theme title.
	 *
	 * @since 1.0.9
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	function title_tag( $title ) {
		if ( ! is_front_page() ) {
			$title = str_replace( get_bloginfo( 'name' ), '', $title );
		}
		return $title;
	}

	/**
	 * Fallback to render title before WP 4.1
	 *
	 * @since 1.0.9
	 */
	function title_tag_fallback() { ?>
		<title><?php wp_title(); ?></title>
	<?php
	}

	/**
	 * Initiate updater check.
	 * 
	 * @since 1.0.0
	 * @access private
	 */
	private function check_update() {
		if ( is_admin() && current_user_can( 'manage_options' ) ) {
			require_once $this->framework_path() . '/classes/updater/class-tf-update-check.php';
			$themify_builder_updater = new TF_Update_Check( array(
				'name' 		  => 'themifyflow',
				'nicename' 	  => 'Themify Flow',
				'update_type' => 'theme',
			), $this->version );
		}
	}

	/**
	 * Include any required theme classes.
	 * 
	 * @since 1.0.0
	 * @access private
	 */
	private function includes() {

		// Model
		include_once( 'classes/class-tf-model.php' );

		// Core functions
		include_once( 'includes/functions/tf-core-functions.php' );
		
		// Theme Elements
		include_once( 'classes/theme-elements/class-tf-theme.php' );
		include_once( 'classes/theme-elements/class-tf-template.php' );
		include_once( 'classes/theme-elements/class-tf-template-part.php' );
		include_once( 'classes/theme-elements/class-tf-styling-panel.php' );

		if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
			$this->frontend_includes();
		}

		if ( is_admin() ) {
			include_once( 'classes/class-tf-admin.php' );
			include_once( 'classes/import-export/class-tf-export.php' );
			include_once( 'classes/class-tf-backend.php' );
		}

		if ( defined( 'DOING_AJAX' ) ) {
			$this->ajax_includes();
		}

		// Load modules
		include_once( 'classes/modules/class-tf-module-loader.php' );

		// Load elements
		include_once( 'classes/modules/class-tf-module-element-loader.php' );

		// Load styles
		include_once( 'classes/engine/class-tf-engine-style-loader.php' );

		// Builder Edit markup UI
		include_once ( 'classes/class-tf-editor-ui.php' );

		// Load duplicate
		include_once( 'classes/class-tf-duplicate.php' );

		// Menu icons
		include_once( 'classes/class-tf-menu-icons.php' );
                
                // Content builder
		include_once( 'classes/class-tf-content-builder.php' );
	}

	/**
	 * Include any frontend classes.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function frontend_includes() {
		include_once( 'classes/class-tf-frontend.php' );
		include_once( 'classes/class-tf-layout.php' );
		include_once( 'classes/theme-elements/class-tf-styling-control.php' );

		// Theme Engine
		include_once( 'classes/engine/class-tf-engine.php' );
	}

	/**
	 * Include any ajax classes.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function ajax_includes() {
		include_once( 'classes/class-tf-ajax.php' );
	}

	/**
	 * Get framework version number.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get framework path.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function framework_path() {
		return get_template_directory() . '/tf';
	}

	/**
	 * Get framework URI.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function framework_uri() {
		return get_template_directory_uri() . '/tf';
	}

	/**
	 * Return plupload settings.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function get_plupload_settings() {
		return apply_filters('tf_plupload_init_vars', array(
			'runtimes'				=> 'html5,flash,silverlight,html4',
			'browse_button'			=> 'tf-plupload-browse-button', // adjusted by uploader
			'container' 			=> 'tf-plupload-upload-ui', // adjusted by uploader
			'drop_element' 			=> 'drag-drop-area', // adjusted by uploader
			'file_data_name' 		=> 'async-upload', // adjusted by uploader
			'multiple_queues' 		=> true,
			'max_file_size' 		=> wp_max_upload_size() . 'b',
			'url' 					=> admin_url('admin-ajax.php'),
			'flash_swf_url' 		=> includes_url('js/plupload/plupload.flash.swf'),
			'silverlight_xap_url' 	=> includes_url('js/plupload/plupload.silverlight.xap'),
			'filters' 				=> array( array(
				'title' => __('Allowed Files', 'themify-flow'),
				'extensions' => 'jpg,jpeg,gif,png,zip,txt,xml'
			)),
			'multipart' 			=> true,
			'urlstream_upload' 		=> true,
			'multi_selection' 		=> false, // added by uploader
			 // additional post data to send to our ajax hook
			'multipart_params' 		=> array(
				'nonce' 			=> '', // added by uploader
				'action' 			=> 'tf_plupload', // the ajax action name
				'imgid' 			=> 0 // added by uploader
			)
		));
	}

	/**
	 * Register mime types.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $existing_mimes 
	 * @return array
	 */
	public function register_mime_types( $existing_mimes = array() ) {
		$existing_mimes['xml'] = 'text/xml';
		return $existing_mimes;
	}
        
        /**
	 * Import theme files 
	 * Using Example flow_import(array('source1'=>array('method'=>'import','type'=>'theme','activate'=>false),
         *                                 'source2'=>array('method'=>'edit','type'=>'template_part'))
         *                          );
	 * @since  1.0.0
	 * @access public
         * @param  array $sources 
	 * @return mixed
	 */
        public static function flow_import(array $sources){
                                           
            if(empty($sources)){
                return false;
            }
            include_once( 'classes/import-export/class-tf-import.php');
            include_once(ABSPATH . 'wp-admin/includes/file.php');
            include_once( ABSPATH . 'wp-admin/includes/image.php' ); 
            WP_Filesystem();
            global $wp_filesystem,$TF;
            $errors = array();
            foreach ($sources as $path=>$data){
                if( !isset($data['method']) || 
                    !isset($data['type'])  ||
                    !in_array($data['type'], array('theme','template','template_part')) 
                    || !in_array($data['method'],array('import','replace')) 
                    || !$wp_filesystem->exists($path))
                {
                    continue;
                }
                $file = wp_check_filetype($path);
                if(!isset($file['ext']) || !in_array($file['ext'],array('plain','rar','zip','xml'))){
                    continue;
                }
                $is_zip = FALSE;
                if($file['ext']=='zip' || $file['ext']=='rar'){
                   
                   $tmp_dir = sys_get_temp_dir().'/'.  uniqid('flow').'/';
                   $unzipfile = unzip_file($path,$tmp_dir);
                   if(!$unzipfile){
                       $errors[$path] = 'There was an error unzipping the file '.basename($path);
                       continue;
                       
                   }
                   $tmp_file = scandir($tmp_dir);
                   if(empty($tmp_file)){
                        $errors[$path] = 'There was an error unzipping the file '.basename($path);
                        continue;
                        
                   } 
                   $xml_file = false;
                   foreach ($tmp_file as $tmp){
                       if($tmp!='.' && $tmp!='..' && $wp_filesystem->exists($tmp_dir.$tmp)){
                           $xml_file = $tmp;
                           break;
                       }
                   }
                   if(!$xml_file){
                        $errors[$path] = 'There was an error unzipping the file '.basename($path);
                        continue;
                        
                   }
                    $xml = wp_check_filetype($tmp_dir.$xml_file,array('xml'=>'application/xml'));
                    if($xml['ext']!='xml'){
                        $errors[$path] = 'The '.$file['ext'].' file '.basename($path).' doesn`t contain xml file';
                        continue;
                        
                    }
                    $is_zip = true;
                    $path = $tmp_dir.$xml_file;
                }
                $import = new TF_Import();
                $posts =  $import->parse($path);
                remove_filter( 'wp_unique_post_slug', array( $TF->active_theme, 'add_prefix_post_slug' ), 10, 6 );
                if(isset($posts['posts']) && !empty($posts['posts'])){
                    $import->method = $data['method']=='import'?'add':'edit';
                    $import->source = $data['type'];
                    $import->fetch_attachments = true;
                    $import->edit_import_id = FALSE;
                    $import->set_associated_theme_by_value = true;
                    $errors[$path] = array();
                    if($data['type']=='theme'){
                       $import->exclude_theme_post = FALSE;
                       if($import->method=='add'){
                            $import->import($path);
                       }
                       else{
                            foreach($posts['posts'] as $post){
                                if($post['post_type']=='tf_'.$data['type']){
                                    $post_exists = TF_Model::post_exists( $post['post_name'] );
                                    if($post_exists && get_post_type( $post_exists ) == $post['post_type']){
                                         $import->edit_import_id = $post_exists;
                                                                                 
                                         $import->import($path);
                                    }
                                    else{
                                       $errors[$path][] = $post['post_title'].' '.$data['type'].' doesn`t exist.'; 
                                    }
                                    break;
                                }
                            }
                       }
                    
                       if(isset($data['activate']) && $data['activate'] && !$import->fails()){
                          $activate = $import->return_ID>0?$import->return_ID:$import->edit_import_id;                
                       }
                    }
                    else{
                        $import->exclude_theme_post = TRUE;
                        $import->set_associated_theme_by_value = true;
                        $import->get_authors_from_import($posts);
                        $import->get_author_mapping();
                        wp_defer_term_counting( true );
                        wp_defer_comment_counting( true );
                        do_action('tf_import_start' );
                        foreach($posts['posts'] as $post){
                            if('tf_'.$data['type']==$post['post_type']){
                                $post_exists = TF_Model::post_exists( $post['post_name'] );
                            
                                $post_type =  $post_exists?get_post_type( $post_exists ) == $post['post_type']:FALSE;
                                $import->posts = array($post);
                                if($import->method=='add'){
                                    if($post_exists && $post_type){
                                        $errors[$path][] = $post['post_title'].' '.$data['type'].' already exist.'; 
                                    }
                                    else{
                                         $import->processed_posts();
                                    }
                                }
                                elseif($post_exists && $post_type){
                                    $import->edit_import_id = $post_exists;
                                    
                                    $import->process_replace_posts();
                                }
                                else{
                                    $errors[$path][] = $post['post_title'].' '.$data['type'].' doesn`t exists.';
                                }
                            }
                        }
                    }
                    if($is_zip){
                        $wp_filesystem->delete( $path );
                    }
                    $messages = $import->get_error_messages();
                    if(!empty($messages)){
                        foreach ($messages as $m){
                            $errors[$path][] = $m;
                        }
                    }
                    elseif(empty($errors[$path])){
                        unset($errors[$path]);
                    }
                }
            }
             if(isset($activate) && $activate){
                global $TF_Theme,$TF;
                $TF_Theme->set_active_theme($activate);
                $TF->active_theme = new TF_Engine_Theme_Loader();
                do_action('tf_import_end');
            }
            return empty($errors)?TRUE:$errors;
        }
}

/** Initialize class */
$GLOBALS['TF'] = new Themify_Flow();
// Loaded action
do_action( 'tf_loaded' );
