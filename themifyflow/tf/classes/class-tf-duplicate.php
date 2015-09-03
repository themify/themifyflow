<?php
/**
 * Duplicate Post Types Class.
 *
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Duplicate {
	
	/**
	 * Define new url
	 * @var string
	 */
	public $new_url = '';

	/**
	 * Whether return edit link or permalink
	 * default = false
	 */
	public $edit_link = 0;

	private $old_post_content = '';

	private $new_post_content = '';

	/**
	 * Constructor
	 */
	function __construct() {
		// Actions
		//add_action( 'init', array( &$this, 'init' ), 10 );
		add_action( 'admin_init', array( &$this, 'init' ), 10 );
	}

	/**
	 * Init function
	 */
	function init() {

		$duplicate_actions = array(
			'postmeta',
			'taxonomies',
			'attachment_entries',
			'template_style'
		);

		foreach ( $duplicate_actions as $action ) {
			add_action( 'tf_duplicate_post', array( &$this, 'duplicate_'.$action ), 10, 2 );
			add_action( 'tf_duplicate_page', array( &$this, 'duplicate_'.$action ), 10, 2 );
		}

	}

	/**
	 * Perfomr duplicating post/page
	 * @param object $post
	 * @param string $status
	 * @param string $parent_id
	 * @return int
	 */
	function duplicate( $post, $status = '', $parent_id = '' ) {
		$prefix = '';
		$suffix = '';
		$this->old_post_content = $post->post_content;
		$this->new_post_content = tf_generate_new_shortcode_ids( $post->post_content );

		// We don't want to clone revisions
		if ( $post->post_type == 'revision' ) return;

		if ( $post->post_type != 'attachment' ) {
			$prefix = '';
			//$suffix = ' Copy';
			$suffix = '';
		}
		$new_post_author = $this->duplicate_get_current_user();

		$new_post = array(
			'menu_order' => $post->menu_order,
			'comment_status' => $post->comment_status,
			'ping_status' => $post->ping_status,
			'post_author' => $new_post_author->ID,
			'post_content' => $this->new_post_content,
			'post_excerpt' => $post->post_excerpt,
			'post_mime_type' => $post->post_mime_type,
			'post_parent' => $new_post_parent = empty($parent_id)? $post->post_parent : $parent_id,
			'post_password' => $post->post_password,
			'post_status' => $new_post_status = (empty($status))? $post->post_status: $status,
			'post_title' => $prefix.$post->post_title.$suffix,
			'post_type' => $post->post_type
		);

		$new_post_id = wp_insert_post( $new_post );

		// apply hook to duplicate action
		if ( $post->post_type == 'page' || ( function_exists( 'is_post_type_hierarchical' ) && is_post_type_hierarchical( $post->post_type ) ) )
			do_action( 'tf_duplicate_page', $new_post_id, $post );
		else
			do_action( 'tf_duplicate_post', $new_post_id, $post );

		// If the copy is published or scheduled, we have to set a proper slug.
		if ( $new_post_status == 'publish' || $new_post_status == 'future' ) {
			$post_name = wp_unique_post_slug( $post->post_name, $new_post_id, $new_post_status, $post->post_type, $new_post_parent );

			$new_post = array();
			$new_post['ID'] = $new_post_id;
			$new_post['post_name'] = $post_name;

			// Update the post into the database
			wp_update_post( $new_post );
		}

		// set new url
		if( $post->post_type == 'page' ) {
			$this->new_url = get_page_link( $new_post_id );
		} else{
			$this->new_url = get_permalink( $new_post_id );
		}

		// check if admin
		if ( $this->edit_link ) {
			$this->new_url = get_edit_post_link( $new_post_id );
		}

		return $new_post_id;
	}

	/**
	 * Duplicate custom fields / post meta
	 * @param int $new_id
	 * @param object $post
	 */
	function duplicate_postmeta( $new_id, $post ) {
		$post_meta_keys = get_post_custom_keys( $post->ID );
		if ( empty( $post_meta_keys ) ) return;
		$meta_keys = $post_meta_keys;

		foreach ( $meta_keys as $meta_key ) {
			$meta_values = get_post_custom_values( $meta_key, $post->ID );
			foreach ( $meta_values as $meta_value ) {
				$meta_value = maybe_unserialize( $meta_value );
				update_post_meta( $new_id, $meta_key, $meta_value );
			}
		}
	}

	/**
	 * Duplicate categories and custom taxonomies
	 * @param int $new_id
	 * @param object $post
	 */
	function duplicate_taxonomies( $new_id, $post ) {
		global $wpdb;
		if ( isset( $wpdb->terms ) ) {
			// Clear default category (added by wp_insert_post)
			wp_set_object_terms( $new_id, NULL, 'category' );

			$post_taxonomies = get_object_taxonomies( $post->post_type );
			$taxonomies = $post_taxonomies;
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post->ID, $taxonomy, array( 'orderby' => 'term_order' ) );
				$terms = array();
				for ( $i=0; $i < count( $post_terms ); $i++ ) {
					$terms[] = $post_terms[ $i ]->slug;
				}
				wp_set_object_terms( $new_id, $terms, $taxonomy );
			}
		}
	}

	/**
	 * Duplicate attachment data entries
	 * Actual files does not copied
	 * @param int $new_id
	 * @param object $post
	 */
	function duplicate_attachment_entries( $new_id, $post ) {
		// get children
		$children = get_posts( array( 'post_type' => 'any', 'numberposts' => -1, 'post_status' => 'any', 'post_parent' => $post->ID ) );
		// clone old attachments
		foreach ( $children as $child ) {
			if ( $child->post_type == 'attachment' ) continue;
			$this->duplicate( $child, '', $new_id );
		}
	}

	/**
	 * Return current user
	 * @return bool|object|WP_User
	 */
	function duplicate_get_current_user() {
		if ( function_exists( 'wp_get_current_user' ) ) {
			return wp_get_current_user();
		} else if ( function_exists( 'get_currentuserinfo' ) ) {
			global $userdata;
			get_currentuserinfo();
			return $userdata;
		} else {
			global $wpdb;
			$user_login = $_COOKIE[USER_COOKIE];
			$sql = $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_login = '%s'", $user_login );
			$current_user = $wpdb->get_results($sql);
			return $current_user;
		}
	}

	/**
	 * Duplicate template styles.
	 * 
	 * @param int $new_id 
	 * @param object $post
	 */
	function duplicate_template_style( $new_id, $post ) {
		$current_style = get_post_meta( $new_id, 'tf_template_style_modules', true );
		if ( $current_style ) {
			$old_sc_ids = tf_get_shortcode_ids( $this->old_post_content );
			$new_sc_ids = tf_get_shortcode_ids( $this->new_post_content );
			$new_style = array();
			if ( count( $old_sc_ids ) > 0 ) {
				foreach( $old_sc_ids as $key => $id ) {
					if ( isset( $current_style[ $old_sc_ids[ $key ] ] ) ) {
						$new_style[ $new_sc_ids[ $key ] ] = $current_style[ $old_sc_ids[ $key ] ];
					}
				}
			}
			update_post_meta( $new_id, 'tf_template_style_modules', $new_style );
		}
	}
}

$GLOBALS['tf_duplicate'] = new TF_Duplicate();