<?php
/**
 * Class TF Export.
 * 
 * Export Template, Template Part, Theme data.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Export {

	/**
	 * Export file names.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public $file_names = array();

	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		
		$this->file_names = apply_filters( 'tf_export_file_names', array(
			'theme'         => array( 'name' => 'theme', 'file' => 'theme_export.xml' ),
			'template'      => array( 'name' => 'template', 'file' => 'template_export.xml' ),
			'template_part' => array( 'name' => 'template-part', 'file' => 'template_part_export.xml' ),
			'content'       => array( 'name' => 'content', 'file' => 'content_export.xml' )
		) );

		add_action( 'admin_init', array( $this, 'do_export' ) );
		add_action( 'export_td', array( $this, 'export_td' ) );
	}

	/**
	 * Export template and template part data.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function do_export() {
		$actions = array( 'export_tf_theme', 'export_tf_template', 'export_tf_template_part', 'export_tf_content_builder' );
		if ( isset( $_GET['action'] ) && in_array( $_GET['action'], $actions ) && wp_verify_nonce($_GET['_wpnonce'], 'export_tf_nonce') ) {
			global $TF;
			include_once( sprintf( "%s/includes/utilities/export.php", $TF->framework_path() ) );
			$template = get_post( $_GET['post'] );
			
			$meta_file = $this->get_filename_data( $template->post_type );
			$name_prefix = $meta_file['name'];
			
			$basename = sanitize_file_name( $meta_file['file'] );
			$filename = sanitize_file_name( $name_prefix . '-' . $template->post_name . '.'. date( 'Y-m-d' ) . '.xml' );
			$ids = 'tf_theme' == $template->post_type ? TF_Model::get_theme_data_post_ids( $template->ID, $template->post_name ) : array( $template->ID );
			$ids = TF_Model::find_attachment_ids_from_posts( $ids ); // Include all attachments ID from each post content shortcode builder.

			ob_start();
			export_td(array(
				'content' => $template->post_type,
				'ids' => $ids,
				'filename' => $filename
			));
			$output = ob_get_contents();
			ob_end_clean();

			// Load WP Filesystem
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			WP_Filesystem();
			global $wp_filesystem;

			if ( class_exists('ZipArchive') ) {
				$datafile = $basename;
				$wp_filesystem->put_contents( $datafile, $output, FS_CHMOD_FILE );

				$files_to_zip = array( $datafile );
				$ext = pathinfo( $filename, PATHINFO_EXTENSION );
				$file = str_replace( '.' . $ext, '.zip', $filename );
				$result = tf_create_zip( $files_to_zip, $file, true );
			}

			if ( isset( $result ) && $result ) {
				if ( ( isset( $file ) ) && ( file_exists( $file ) ) ) {
					ob_start();
					header('Pragma: public');
					header('Expires: 0');
					header("Content-type: application/force-download");
					header('Content-Disposition: attachment; filename="' . $file . '"');
					header("Content-Transfer-Encoding: Binary"); 
					header("Content-length: ".filesize( $file ) );
					header('Connection: close');
					ob_clean();
					flush(); 
					echo $wp_filesystem->get_contents( $file );
					unlink( $datafile );
					unlink( $file );
					exit();
				} else {
					return false;
				}
			} else {
				if ( ini_get('zlib.output_compression') ) {
					/**
					 * Turn off output buffer compression for proper zip download.
					 * @since 2.0.2
					 */
					$srv_stg = 'ini' . '_' . 'set';
					call_user_func( $srv_stg, 'zlib.output_compression', 'Off');
				}
				ob_start();
				header('Content-Type: application/force-download');
				header('Pragma: public');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Cache-Control: private',false);
				header('Content-Disposition: attachment; filename="'. $filename .'"');
				header('Content-Transfer-Encoding: binary');
				ob_clean();
				flush();
				echo $output;
				exit();
			}

			die();
		}
	}

	/**
	 * Get filename data.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param string $post_type 
	 * @return array
	 */
	public function get_filename_data( $post_type ) {
		$type = str_replace( 'tf_', '', $post_type );
		if ( isset( $this->file_names[ $type ] ) ) {
			return $this->file_names[ $type ];
		} else {
			return $this->file_names['content'];
		}
	}

	/**
	 * Filter to skip specific post meta.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param boolean $boolean 
	 * @param string $meta_key 
	 * @param array $meta 
	 * @return boolean
	 */
	public function skip_post_meta( $boolean, $meta_key, $meta ) {
		global $post;
		$post_type = get_post_type( $post->ID );
		$post_types = array( 'tf_template', 'tf_template_part');
		if ( in_array( $post_type, $post_types ) && 'associated_theme' == $meta_key ) {
			return true;
		}
		return $boolean;
	}

	/**
	 * Filter skip posts meta.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param boolean $boolean 
	 * @param string $meta_key 
	 * @param array $meta 
	 * @return boolean
	 */
	public function skip_post_meta_global( $boolean, $meta_key, $meta ) {
		// Don't export active_theme meta key.
		if ( 'tf_active_theme' == $meta_key ) 
			return true;
		return $boolean;
	}

	/**
	 * Export td hook.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $args 
	 */
	public function export_td( $args ) {
		if ( 'tf_theme' != $args['content'] ) {
			add_filter( 'tf_wxr_export_skip_postmeta', array( $this, 'skip_post_meta' ), 10, 3 );
		}
		add_filter( 'tf_wxr_export_skip_postmeta', array( $this, 'skip_post_meta_global' ), 10, 3 );
	}
}

/** Initialize class. */
$GLOBALS['tf_export'] = new TF_Export();