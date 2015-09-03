<?php
// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( ! class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) )
		require $class_wp_importer;
}

// include WXR file parsers
require get_template_directory() . '/tf/includes/utilities/parsers.php';

/**
 * WordPress Importer class for managing the import process of a WXR file
 *
 * @package WordPress
 * @subpackage Importer
 */
if ( class_exists( 'WP_Importer' ) ) {
    
class TF_Import extends WP_Importer {
	public $max_wxr_version = 1.2; // max. supported WXR version

	public $id; // WXR attachment ID

	// information to import from WXR file
	public $version;
	public $authors = array();
	public $posts = array();
	public $base_url = '';

	// mappings from old information to new
	public $processed_authors = array();
	public $author_mapping = array();
	public $processed_posts = array();
	public $post_orphans = array();

	public $fetch_attachments = false;
	public $url_remap = array();
	public $featured_images = array();

	protected $errors = array();

	public $method = 'add'; // add|edit

	public $edit_import_id;

	public $source = 'theme';

	public $base_theme = false;

	public $exclude_theme_post = false;

	public $set_associated_theme = '';
        
        public $set_associated_theme_by_value = false;

	public $return_ID = 0;

	function WP_Import() { /* nothing */ }

	/**
	 * The main controller for the actual import stage.
	 *
	 * @param string $file Path to the WXR file for importing
	 */
	function import( $file ) {
		add_filter( 'import_post_meta_key', array( $this, 'is_valid_meta_key' ) );
		add_filter( 'http_request_timeout', array( &$this, 'bump_request_timeout' ) );

		$this->import_start( $file );

		$this->get_author_mapping();

		wp_suspend_cache_invalidation( true );
		if ( 'add' == $this->method ) {
			$this->process_posts();
		} else {
			$this->process_replace_posts();
		}
		wp_suspend_cache_invalidation( false );

		// update incorrect/missing information in the DB
		$this->backfill_parents();
		$this->backfill_attachment_urls();
		$this->remap_featured_images();

		$this->import_end();
	}

	/**
	 * Parses the WXR file and prepares us for the task of processing parsed data
	 *
	 * @param string $file Path to the WXR file for importing
	 */
	function import_start( $file ) {
		if ( ! is_file($file) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'themify-flow' ) . '</strong><br />';
			echo __( 'The file does not exist, please try again.', 'themify-flow' ) . '</p>';
			die();
		}

		$import_data = $this->parse( $file );

		if ( is_wp_error( $import_data ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'themify-flow' ) . '</strong><br />';
			echo esc_html( $import_data->get_error_message() ) . '</p>';
			die();
		}

		$this->version = $import_data['version'];
		$this->get_authors_from_import( $import_data );
		$this->posts = $import_data['posts'];
		$this->base_url = esc_url( $import_data['base_url'] );

		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );

		do_action( 'tf_import_start' );
	}

	/**
	 * Performs post-import cleanup of files and the cache
	 */
	function import_end() {
		wp_import_cleanup( $this->id );

		wp_cache_flush();
		foreach ( get_taxonomies() as $tax ) {
			delete_option( "{$tax}_children" );
			_get_term_hierarchy( $tax );
		}

		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );

		do_action( 'tf_import_end', $this->base_theme );
	}

	/**
	 * Handles the WXR upload and initial parsing of the file to prepare for
	 * displaying author import options
	 *
	 * @return bool False if error uploading or invalid file, true otherwise
	 */
	function handle_upload() {
		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'themify-flow' ) . '</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';
			return false;
		} else if ( ! file_exists( $file['file'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'themify-flow' ) . '</strong><br />';
			printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'themify-flow' ), esc_html( $file['file'] ) );
			echo '</p>';
			return false;
		}

		$this->id = (int) $file['id'];
		$import_data = $this->parse( $file['file'] );
		if ( is_wp_error( $import_data ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'themify-flow' ) . '</strong><br />';
			echo esc_html( $import_data->get_error_message() ) . '</p>';
			return false;
		}

		$this->version = $import_data['version'];
		if ( $this->version > $this->max_wxr_version ) {
			echo '<div class="error"><p><strong>';
			printf( __( 'This WXR file (version %s) may not be supported by this version of the importer. Please consider updating.', 'themify-flow' ), esc_html($import_data['version']) );
			echo '</strong></p></div>';
		}

		$this->get_authors_from_import( $import_data );

		return true;
	}

	/**
	 * Retrieve authors from parsed WXR data
	 *
	 * Uses the provided author information from WXR 1.1 files
	 * or extracts info from each post for WXR 1.0 files
	 *
	 * @param array $import_data Data returned by a WXR parser
	 */
	function get_authors_from_import( $import_data ) {
		if ( ! empty( $import_data['authors'] ) ) {
			$this->authors = $import_data['authors'];
		// no author information, grab it from the posts
		} else {
			foreach ( $import_data['posts'] as $post ) {
				$login = sanitize_user( $post['post_author'], true );
				if ( empty( $login ) ) {
					printf( __( 'Failed to import author %s. Their posts will be attributed to the current user.', 'themify-flow' ), esc_html( $post['post_author'] ) );
					echo '<br />';
					continue;
				}

				if ( ! isset($this->authors[$login]) )
					$this->authors[$login] = array(
						'author_login' => $login,
						'author_display_name' => $post['post_author']
					);
			}
		}
	}

	/**
	 * Map old author logins to local user IDs based on decisions made
	 * in import options form. Can map to an existing user, create a new user
	 * or falls back to the current user in case of error with either of the previous
	 */
	function get_author_mapping() {
		if ( ! isset( $_POST['imported_authors'] ) )
			return;

		$create_users = $this->allow_create_users();

		foreach ( (array) $_POST['imported_authors'] as $i => $old_login ) {
			// Multisite adds strtolower to sanitize_user. Need to sanitize here to stop breakage in process_posts.
			$santized_old_login = sanitize_user( $old_login, true );
			$old_id = isset( $this->authors[$old_login]['author_id'] ) ? intval($this->authors[$old_login]['author_id']) : false;

			if ( ! empty( $_POST['user_map'][$i] ) ) {
				$user = get_userdata( intval($_POST['user_map'][$i]) );
				if ( isset( $user->ID ) ) {
					if ( $old_id )
						$this->processed_authors[$old_id] = $user->ID;
					$this->author_mapping[$santized_old_login] = $user->ID;
				}
			} else if ( $create_users ) {
				if ( ! empty($_POST['user_new'][$i]) ) {
					$user_id = wp_create_user( $_POST['user_new'][$i], wp_generate_password() );
				} else if ( $this->version != '1.0' ) {
					$user_data = array(
						'user_login' => $old_login,
						'user_pass' => wp_generate_password(),
						'user_email' => isset( $this->authors[$old_login]['author_email'] ) ? $this->authors[$old_login]['author_email'] : '',
						'display_name' => $this->authors[$old_login]['author_display_name'],
						'first_name' => isset( $this->authors[$old_login]['author_first_name'] ) ? $this->authors[$old_login]['author_first_name'] : '',
						'last_name' => isset( $this->authors[$old_login]['author_last_name'] ) ? $this->authors[$old_login]['author_last_name'] : '',
					);
					$user_id = wp_insert_user( $user_data );
				}

				if ( ! is_wp_error( $user_id ) ) {
					if ( $old_id )
						$this->processed_authors[$old_id] = $user_id;
					$this->author_mapping[$santized_old_login] = $user_id;
				} else {
					printf( __( 'Failed to create new user for %s. Their posts will be attributed to the current user.', 'themify-flow' ), esc_html($this->authors[$old_login]['author_display_name']) );
					if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
						echo ' ' . $user_id->get_error_message();
					echo '<br />';
				}
			}

			// failsafe: if the user_id was invalid, default to the current user
			if ( ! isset( $this->author_mapping[$santized_old_login] ) ) {
				if ( $old_id )
					$this->processed_authors[$old_id] = (int) get_current_user_id();
				$this->author_mapping[$santized_old_login] = (int) get_current_user_id();
			}
		}
	}

	/**
	 * Create new posts based on import information
	 *
	 * Posts marked as having a parent which doesn't exist will become top level items.
	 * Doesn't create a new post if: the post type doesn't exist, the given post ID
	 * is already noted as imported or a post with the same title and date already exists.
	 * Note that new/updated terms, comments and meta are imported for the last of the above.
	 */
	function process_posts() {
		$this->posts = apply_filters( 'tf_import_posts', $this->posts );
             
		foreach ( $this->posts as $post ) {
			$post = apply_filters( 'tf_import_post_data_raw', $post );

			if ( ! post_type_exists( $post['post_type'] ) ) {
				$this->errors[] = sprintf( __( 'Failed to import "%s": Invalid post type %s', 'themify-flow' ),
				esc_html($post['post_title']), esc_html($post['post_type']) );
				do_action( 'tf_import_post_exists', $post );
				continue;
			}

			if ( isset( $this->processed_posts[$post['post_id']] ) && ! empty( $post['post_id'] ) )
				continue;

			if ( $post['status'] == 'auto-draft' )
				continue;

			if ( 'nav_menu_item' == $post['post_type'] ) {
				continue;
			}

			if ( $this->exclude_theme_post && 'tf_theme' == $post['post_type'] ) {
				continue;
			} else if ( $this->exclude_theme_post && 'tf_theme' != $post['post_type'] ) {
				$post['post_name'] = TF_Model::replace_theme_prefix_slug( $post['post_name'], $this->set_associated_theme );
			}
                     
			$post_type_object = get_post_type_object( $post['post_type'] );
                      
			if ( 'theme' != $this->source && 'tf_theme' != $post['post_type'] ) {
				$post['post_name'] = TF_Model::replace_theme_prefix_slug( $post['post_name'] );
			}

			$post_exists = TF_Model::post_exists( $post['post_name'] );
			if ( $post_exists && get_post_type( $post_exists ) == $post['post_type'] ) {
				// don't show error for attachment
				if ( 'attachment' != $post['post_type'] ) {
					$this->errors[] = sprintf( __( '%1$s "%2$s" already exists. To replace the %1$s, use "Replace" instead.', 'themify-flow' ), $post_type_object->labels->singular_name, esc_html($post['post_title']) );
				}
				$post_id = $post_exists;

				if ( 'theme' == $this->source && 'tf_theme' == $post['post_type'] ) {
					break;
				}

			} else {
				$post_parent = (int) $post['post_parent'];
				if ( $post_parent ) {
					// if we already know the parent, map it to the new local ID
					if ( isset( $this->processed_posts[$post_parent] ) ) {
						$post_parent = $this->processed_posts[$post_parent];
					// otherwise record the parent for later
					} else {
						$this->post_orphans[intval($post['post_id'])] = $post_parent;
						$post_parent = 0;
					}
				}

				// map the post author
				$author = sanitize_user( $post['post_author'], true );
				if ( isset( $this->author_mapping[$author] ) )
					$author = $this->author_mapping[$author];
				else
					$author = (int) get_current_user_id();

				$postdata = array(
					'import_id' => $post['post_id'], 'post_author' => $author, 'post_date' => $post['post_date'],
					'post_date_gmt' => $post['post_date_gmt'], 'post_content' => $post['post_content'],
					'post_excerpt' => $post['post_excerpt'], 'post_title' => $post['post_title'],
					'post_status' => $post['status'], 'post_name' => $post['post_name'],
					'comment_status' => $post['comment_status'], 'ping_status' => $post['ping_status'],
					'guid' => $post['guid'], 'post_parent' => $post_parent, 'menu_order' => $post['menu_order'],
					'post_type' => $post['post_type'], 'post_password' => $post['post_password']
				);

				$original_post_ID = $post['post_id'];
				$postdata = apply_filters( 'tf_import_post_data_processed', $postdata, $post );

				if ( 'attachment' == $postdata['post_type'] ) {
					$remote_url = ! empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];

					// try to use _wp_attached file for upload folder placement to ensure the same location as the export site
					// e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
					$postdata['upload_date'] = $post['post_date'];
					if ( isset( $post['postmeta'] ) ) {
						foreach( $post['postmeta'] as $meta ) {
							if ( $meta['key'] == '_wp_attached_file' ) {
								if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta['value'], $matches ) )
									$postdata['upload_date'] = $matches[0];
								break;
							}
						}
					}

					$post_id = $this->process_attachment( $postdata, $remote_url );
				} else {

					do_action( 'tf_import_before_insert_post', $postdata, $this->source );

					$post_id = wp_insert_post( $postdata, true );

					// Assign associated theme with current theme active
					if ( in_array( $this->source, array( 'template', 'template_part' ) ) && in_array( $post['post_type'], array( 'tf_template', 'tf_template_part' ) ) ) {
						global $TF;
                                                
						update_post_meta( $post_id, 'associated_theme', $TF->active_theme->slug );
					}

					// return the ID
					if ( 'theme' == $this->source && 'tf_theme' == $post['post_type'] ) 
						$this->return_ID = $post_id;

					do_action( 'tf_import_insert_post', $post_id, $original_post_ID, $postdata, $post );
				}

				if ( is_wp_error( $post_id ) ) {
					$message = sprintf( __( 'Failed to import %s &#8220;%s&#8221;', 'themify-flow' ),
						$post_type_object->labels->singular_name, esc_html($post['post_title'])
					);
					
					if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
						$message .= ': ' . $post_id->get_error_message();
					$this->errors[] = $message;
					continue;
				}
			}

			// map pre-import ID to local ID
			$this->processed_posts[intval($post['post_id'])] = (int) $post_id;

			if ( ! isset( $post['postmeta'] ) )
				$post['postmeta'] = array();

			$post['postmeta'] = apply_filters( 'tf_import_post_meta', $post['postmeta'], $post_id, $post );

			// add/update post meta
			if ( ! empty( $post['postmeta'] ) ) {
				foreach ( $post['postmeta'] as $meta ) {
					$key = apply_filters( 'tf_import_post_meta_key', $meta['key'], $post_id, $post );
					$value = false;

					if ( '_edit_last' == $key ) {
						if ( isset( $this->processed_authors[intval($meta['value'])] ) )
							$value = $this->processed_authors[intval($meta['value'])];
						else
							$key = false;
					}

					if ( $key ) {
						// export gets meta straight from the DB so could have a serialized string
						if ( ! $value )
							$value = maybe_unserialize( $meta['value'] );

						update_post_meta( $post_id, $key, $value );
                                                if($this->set_associated_theme_by_value){
                                                    $this->set_associated_theme = $value;
                                                }
						if ( $this->exclude_theme_post && 'tf_theme' != $post['post_type'] && 'associated_theme' == $key ) {
                                                   update_post_meta( $post_id, $key, $this->set_associated_theme );
						}

						// Set the correct regions for Add default Templates and Parts
						if ( $this->exclude_theme_post && 'tf_theme' != $post['post_type'] && in_array( $key, array( 'tf_template_region_header', 'tf_template_region_sidebar', 'tf_template_region_footer' ) ) && ! empty( $value ) ) {
							$new_meta_value = TF_Model::replace_theme_prefix_slug( $value, $this->set_associated_theme );
							update_post_meta( $post_id, $key, $new_meta_value );
						}

						do_action( 'tf_import_post_meta', $post_id, $key, $value );

						// if the post has a featured image, take note of this in case of remap
						if ( '_thumbnail_id' == $key )
							$this->featured_images[$post_id] = (int) $value;
					}
				}
			}
		}

		unset( $this->posts );
	}

	/**
	 * Create new posts based on import information
	 *
	 * Posts marked as having a parent which doesn't exist will become top level items.
	 * Doesn't create a new post if: the post type doesn't exist, the given post ID
	 * is already noted as imported or a post with the same title and date already exists.
	 * Note that new/updated terms, comments and meta are imported for the last of the above.
	 */
	function process_replace_posts() {
		$this->posts = apply_filters( 'tf_import_posts', $this->posts );
     
		foreach ( $this->posts as $post ) {
			$post = apply_filters( 'tf_import_post_data_raw', $post );
                        
			if ( ! post_type_exists( $post['post_type'] ) ) {
				$this->errors[] = sprintf( __( 'Failed to import "%s": Invalid post type %s', 'themify-flow' ),
				esc_html($post['post_title']), esc_html($post['post_type']) );
				do_action( 'tf_import_post_exists', $post );
				continue;
			}

			if ( isset( $this->processed_posts[$post['post_id']] ) && ! empty( $post['post_id'] ) )
				continue;

			if ( $post['status'] == 'auto-draft' )
				continue;

			if ( 'nav_menu_item' == $post['post_type'] ) {
				continue;
			}

			$post_type_object = get_post_type_object( $post['post_type'] );

			if ( 'add' == $this->method && 'theme' != $this->source && 'tf_theme' != $post['post_type'] ) {
				$post['post_name'] = TF_Model::replace_theme_prefix_slug( $post['post_name'] );
			}

			// Replace Theme > Templates and Parts
			if ( 'theme' == $this->source && 'tf_theme' != $post['post_type'] ) {
				$the_original_theme = get_post( $this->edit_import_id );
				$post['post_name'] = TF_Model::replace_theme_prefix_slug( $post['post_name'], $the_original_theme->post_name );
			}
   
			if ( 'theme' == $this->source && 'tf_theme' == $post['post_type'] ) {
				$post_exists = $this->edit_import_id;
			} else if ( 'theme' != $this->source && 'tf_theme' != $post['post_type'] ) {
				$post_exists = $this->edit_import_id;
			} else {
				$post_exists = TF_Model::post_exists( $post['post_name'] );
			}

			$post_parent = (int) $post['post_parent'];
			if ( $post_parent ) {
				// if we already know the parent, map it to the new local ID
				if ( isset( $this->processed_posts[$post_parent] ) ) {
					$post_parent = $this->processed_posts[$post_parent];
				// otherwise record the parent for later
				} else {
					$this->post_orphans[intval($post['post_id'])] = $post_parent;
					$post_parent = 0;
				}
			}

			// map the post author
			$author = sanitize_user( $post['post_author'], true );
			if ( isset( $this->author_mapping[$author] ) )
				$author = $this->author_mapping[$author];
			else
				$author = (int) get_current_user_id();

			$postdata = array(
				'import_id' => $post['post_id'], 'post_author' => $author, 'post_date' => $post['post_date'],
				'post_date_gmt' => $post['post_date_gmt'], 'post_content' => $post['post_content'],
				'post_excerpt' => $post['post_excerpt'], 'post_title' => $post['post_title'],
				'post_status' => $post['status'], 'post_name' => $post['post_name'],
				'comment_status' => $post['comment_status'], 'ping_status' => $post['ping_status'],
				'guid' => $post['guid'], 'post_parent' => $post_parent, 'menu_order' => $post['menu_order'],
				'post_type' => $post['post_type'], 'post_password' => $post['post_password']
			);
        
			$original_post_ID = $post['post_id'];
			$postdata = apply_filters( 'tf_import_post_data_processed', $postdata, $post );

			if ( 'attachment' == $postdata['post_type'] ) {
				$remote_url = ! empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];

				// try to use _wp_attached file for upload folder placement to ensure the same location as the export site
				// e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
				$postdata['upload_date'] = $post['post_date'];
				if ( isset( $post['postmeta'] ) ) {
					foreach( $post['postmeta'] as $meta ) {
						if ( $meta['key'] == '_wp_attached_file' ) {
							if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta['value'], $matches ) )
								$postdata['upload_date'] = $matches[0];
							break;
						}
					}
				}

				if ( $post_exists ) {
					$post_id = $post_exists;	
				} else {
					$post_id = $this->process_attachment( $postdata, $remote_url );
				}
			} else {

				do_action( 'tf_import_before_insert_post', $postdata, $this->source );
 
				if ( $post_exists ) {
					$postdata['ID']  = $post_exists;
 					$post_id = $post_exists;
                                        if(!$this->set_associated_theme_by_value){
                                            unset( $postdata['post_name'] );
                                            unset( $postdata['post_title'] );
                                        }
 					wp_update_post( $postdata );
				} else {
					$post_id = wp_insert_post( $postdata, true );
				}

				// Assign associated theme with current theme active
				if ( in_array( $this->source, array( 'template', 'template_part' ) ) && in_array( $post['post_type'], array( 'tf_template', 'tf_template_part' ) ) ) {
					global $TF;
					update_post_meta( $post_id, 'associated_theme', $TF->active_theme->slug );
				}

				do_action( 'tf_import_insert_post', $post_id, $original_post_ID, $postdata, $post );
			}

			if ( is_wp_error( $post_id ) ) {
				$message = sprintf( __( 'Failed to import %s &#8220;%s&#8221;', 'themify-flow' ),
					$post_type_object->labels->singular_name, esc_html($post['post_title'])
				);
				
				if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
					$message .= ': ' . $post_id->get_error_message();
				$this->errors[] = $message;
				continue;
			}

			if ( $post['is_sticky'] == 1 )
				stick_post( $post_id );

			// map pre-import ID to local ID
			$this->processed_posts[intval($post['post_id'])] = (int) $post_id;

			if ( ! isset( $post['postmeta'] ) )
				$post['postmeta'] = array();

			$post['postmeta'] = apply_filters( 'tf_import_post_meta', $post['postmeta'], $post_id, $post );

			// add/update post meta
			if ( ! empty( $post['postmeta'] ) ) {
				foreach ( $post['postmeta'] as $meta ) {
					$key = apply_filters( 'tf_import_post_meta_key', $meta['key'], $post_id, $post );
					$value = false;

					if ( '_edit_last' == $key ) {
						if ( isset( $this->processed_authors[intval($meta['value'])] ) )
							$value = $this->processed_authors[intval($meta['value'])];
						else
							$key = false;
					}

					if ( $key ) {
						// export gets meta straight from the DB so could have a serialized string
						if ( ! $value )
							$value = maybe_unserialize( $meta['value'] );
                                                
						// Replace Theme > Templates and Parts
						if ( 'associated_theme' == $key && 'theme' == $this->source && 'tf_theme' != $post['post_type']) {
							if($this->set_associated_theme_by_value){
                                                            update_post_meta( $post_id, 'associated_theme',$value);
                                                        }
                                                        elseif(isset( $the_original_theme ) && is_object( $the_original_theme )){
                                                            update_post_meta( $post_id, 'associated_theme',$the_original_theme->post_name);
                                                        }
						}    

						// Set the correct regions for Add default Templates and Parts
						else if ( 'theme' == $this->source && 'tf_theme' != $post['post_type'] && in_array( $key, array( 'tf_template_region_header', 'tf_template_region_sidebar', 'tf_template_region_footer' ) ) && ! empty( $value )  ) {
							$new_meta_value = TF_Model::replace_theme_prefix_slug( $value, $the_original_theme->post_name );
							if($this->set_associated_theme_by_value){
                                                            update_post_meta( $post_id, $key,$value);
                                                        }
                                                        elseif(isset( $the_original_theme ) && is_object( $the_original_theme )){
                                                          update_post_meta( $post_id, $key, $new_meta_value );
                                                        } 
						}

						else {
							update_post_meta( $post_id, $key, $value );
						}

						do_action( 'tf_import_post_meta', $post_id, $key, $value );

						// if the post has a featured image, take note of this in case of remap
						if ( '_thumbnail_id' == $key )
							$this->featured_images[$post_id] = (int) $value;
					}
				}
			}
		}

		unset( $this->posts );
	}

	/**
	 * If fetching attachments is enabled then attempt to create a new attachment
	 *
	 * @param array $post Attachment post details from WXR
	 * @param string $url URL to fetch attachment from
	 * @return int|WP_Error Post ID on success, WP_Error otherwise
	 */
	function process_attachment( $post, $url ) {
		if ( ! $this->fetch_attachments )
			return new WP_Error( 'attachment_processing_error',
				__( 'Fetching attachments is not enabled', 'themify-flow' ) );

		// if the URL is absolute, but does not contain address, then upload it assuming base_site_url
		if ( preg_match( '|^/[\w\W]+$|', $url ) )
			$url = rtrim( $this->base_url, '/' ) . $url;

		$upload = $this->fetch_remote_file( $url, $post );
		if ( is_wp_error( $upload ) )
			return $upload;

		if ( $info = wp_check_filetype( $upload['file'] ) )
			$post['post_mime_type'] = $info['type'];
		else
			return new WP_Error( 'attachment_processing_error', __('Invalid file type', 'themify-flow') );

		$post['guid'] = $upload['url'];

		// as per wp-admin/includes/upload.php
		$post_id = wp_insert_attachment( $post, $upload['file'] );
		wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

		// remap resized image URLs, works by stripping the extension and remapping the URL stub.
		if ( preg_match( '!^image/!', $info['type'] ) ) {
			$parts = pathinfo( $url );
			$name = basename( $parts['basename'], ".{$parts['extension']}" ); // PATHINFO_FILENAME in PHP 5.2

			$parts_new = pathinfo( $upload['url'] );
			$name_new = basename( $parts_new['basename'], ".{$parts_new['extension']}" );

			$this->url_remap[$parts['dirname'] . '/' . $name] = $parts_new['dirname'] . '/' . $name_new;
		}

		return $post_id;
	}

	/**
	 * Attempt to download a remote file attachment
	 *
	 * @param string $url URL of item to fetch
	 * @param array $post Attachment details
	 * @return array|WP_Error Local file location details on success, WP_Error otherwise
	 */
	function fetch_remote_file( $url, $post ) {
		// extract the file name and extension from the url
		$file_name = basename( $url );

		// get placeholder file in the upload dir with a unique, sanitized filename
		$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] );
		if ( $upload['error'] )
			return new WP_Error( 'upload_dir_error', $upload['error'] );

		// fetch the remote url and write it to the placeholder file
		$headers = wp_get_http( $url, $upload['file'] );

		// request failed
		if ( ! $headers ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Remote server did not respond', 'themify-flow') );
		}

		// make sure the fetch was successful
		if ( $headers['response'] != '200' ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', sprintf( __('Remote server returned error response %1$d %2$s', 'themify-flow'), esc_html($headers['response']), get_status_header_desc($headers['response']) ) );
		}

		$filesize = filesize( $upload['file'] );

		if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Remote file is incorrect size', 'themify-flow') );
		}

		if ( 0 == $filesize ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Zero size file downloaded', 'themify-flow') );
		}

		$max_size = (int) $this->max_attachment_size();
		if ( ! empty( $max_size ) && $filesize > $max_size ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', sprintf(__('Remote file is too large, limit is %s', 'themify-flow'), size_format($max_size) ) );
		}

		// keep track of the old and new urls so we can substitute them later
		$this->url_remap[$url] = $upload['url'];
		$this->url_remap[$post['guid']] = $upload['url']; // r13735, really needed?
		// keep track of the destination if the remote url is redirected somewhere else
		if ( isset($headers['x-final-location']) && $headers['x-final-location'] != $url )
			$this->url_remap[$headers['x-final-location']] = $upload['url'];

		return $upload;
	}

	/**
	 * Attempt to associate posts and menu items with previously missing parents
	 *
	 * An imported post's parent may not have been imported when it was first created
	 * so try again. Similarly for child menu items and menu items which were missing
	 * the object (e.g. post) they represent in the menu
	 */
	function backfill_parents() {
		global $wpdb;

		// find parents for post orphans
		foreach ( $this->post_orphans as $child_id => $parent_id ) {
			$local_child_id = $local_parent_id = false;
			if ( isset( $this->processed_posts[$child_id] ) )
				$local_child_id = $this->processed_posts[$child_id];
			if ( isset( $this->processed_posts[$parent_id] ) )
				$local_parent_id = $this->processed_posts[$parent_id];

			if ( $local_child_id && $local_parent_id )
				$wpdb->update( $wpdb->posts, array( 'post_parent' => $local_parent_id ), array( 'ID' => $local_child_id ), '%d', '%d' );
		}
	}

	/**
	 * Use stored mapping information to update old attachment URLs
	 */
	function backfill_attachment_urls() {
		global $wpdb;
		// make sure we do the longest urls first, in case one is a substring of another
		uksort( $this->url_remap, array(&$this, 'cmpr_strlen') );

		foreach ( $this->url_remap as $from_url => $to_url ) {
			// remap urls in post_content
			$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $from_url, $to_url) );
			// remap enclosure urls
			$result = $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'", $from_url, $to_url) );
		}
	}

	/**
	 * Update _thumbnail_id meta to new, imported attachment IDs
	 */
	function remap_featured_images() {
		// cycle through posts that have a featured image
		foreach ( $this->featured_images as $post_id => $value ) {
			if ( isset( $this->processed_posts[$value] ) ) {
				$new_id = $this->processed_posts[$value];
				// only update if there's a difference
				if ( $new_id != $value )
					update_post_meta( $post_id, '_thumbnail_id', $new_id );
			}
		}
	}

	/**
	 * Parse a WXR file
	 *
	 * @param string $file Path to WXR file for parsing
	 * @return array Information gathered from the WXR file
	 */
	function parse( $file ) {
		$parser = new WXR_Parser();
		return $parser->parse( $file );
	}

	/**
	 * Decide if the given meta key maps to information we will want to import
	 *
	 * @param string $key The meta key to check
	 * @return string|bool The key if we do want to import, false if not
	 */
	function is_valid_meta_key( $key ) {
		// skip attachment metadata since we'll regenerate it from scratch
		// skip _edit_lock as not relevant for import
		if ( in_array( $key, array( '_wp_attached_file', '_wp_attachment_metadata', '_edit_lock' ) ) )
			return false;
		return $key;
	}

	/**
	 * Decide whether or not the importer is allowed to create users.
	 * Default is true, can be filtered via import_allow_create_users
	 *
	 * @return bool True if creating users is allowed
	 */
	function allow_create_users() {
		return apply_filters( 'import_allow_create_users', true );
	}

	/**
	 * Decide whether or not the importer should attempt to download attachment files.
	 * Default is true, can be filtered via import_allow_fetch_attachments. The choice
	 * made at the import options screen must also be true, false here hides that checkbox.
	 *
	 * @return bool True if downloading attachments is allowed
	 */
	function allow_fetch_attachments() {
		return apply_filters( 'import_allow_fetch_attachments', true );
	}

	/**
	 * Decide what the maximum file size for downloaded attachments is.
	 * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
	 *
	 * @return int Maximum attachment file size to import
	 */
	function max_attachment_size() {
		return apply_filters( 'import_attachment_size_limit', 0 );
	}

	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 * @return int 60
	 */
	function bump_request_timeout( $val ) {
		return 60;
	}

	// return the difference in length between two strings
	function cmpr_strlen( $a, $b ) {
		return strlen($b) - strlen($a);
	}

	/**
	 * Check if validator failed.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return boolean
	 */
	public function fails() {
		if ( count( $this->errors ) > 0 ) 
			return true;
		return false;
	}

	/**
	 * Get error messages.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function get_error_messages() {
		return $this->errors;
	}

	public function get_error_message( $type ) {
		if ( isset( $this->errors[ $type ] ) ) {
			return $this->errors[ $type ];
		} else {
			return '';
		}
	}
}

/**
 * Content Builder Import Class.
 * 
 * Import Builder Content
 * 
 * @package ThemifyFlow
 */
class TF_Import_Content_Builder extends TF_Import {

	public $content_builder_id = 0;

	/**
	 * The main controller for the actual import stage.
	 *
	 * @param string $file Path to the WXR file for importing
	 */
	function import( $file ) {
		add_filter( 'import_post_meta_key', array( $this, 'is_valid_meta_key' ) );
		add_filter( 'http_request_timeout', array( &$this, 'bump_request_timeout' ) );

		$this->import_start( $file );

		$this->get_author_mapping();

		wp_suspend_cache_invalidation( true );
		
		$this->process_builder_contents();
		
		wp_suspend_cache_invalidation( false );

		// update incorrect/missing information in the DB
		$this->backfill_parents();
		$this->backfill_attachment_urls();
		$this->remap_featured_images();

		$this->import_end();
	}

	function process_builder_contents() {
		$this->posts = apply_filters( 'tf_import_posts', $this->posts );

		foreach ( $this->posts as $post ) {
			$post = apply_filters( 'tf_import_post_data_raw', $post );

			if ( ! post_type_exists( $post['post_type'] ) ) {
				$this->errors[] = sprintf( __( 'Failed to import "%s": Invalid post type %s', 'themify-flow' ),
				esc_html($post['post_title']), esc_html($post['post_type']) );
				do_action( 'tf_import_post_exists', $post );
				continue;
			}

			if ( isset( $this->processed_posts[$post['post_id']] ) && ! empty( $post['post_id'] ) )
				continue;

			if ( $post['status'] == 'auto-draft' )
				continue;

			if ( 'nav_menu_item' == $post['post_type'] ) {
				continue;
			}

			$post_type_object = get_post_type_object( $post['post_type'] );

			$post_exists = post_exists( $post['post_title'], '', $post['post_date'] );
			
			$post_parent = (int) $post['post_parent'];
			if ( $post_parent ) {
				// if we already know the parent, map it to the new local ID
				if ( isset( $this->processed_posts[$post_parent] ) ) {
					$post_parent = $this->processed_posts[$post_parent];
				// otherwise record the parent for later
				} else {
					$this->post_orphans[intval($post['post_id'])] = $post_parent;
					$post_parent = 0;
				}
			}

			// map the post author
			$author = sanitize_user( $post['post_author'], true );
			if ( isset( $this->author_mapping[$author] ) )
				$author = $this->author_mapping[$author];
			else
				$author = (int) get_current_user_id();

			$postdata = array(
				'import_id' => $post['post_id'], 'post_author' => $author, 'post_date' => $post['post_date'],
				'post_date_gmt' => $post['post_date_gmt'], 'post_content' => $post['post_content'],
				'post_excerpt' => $post['post_excerpt'], 'post_title' => $post['post_title'],
				'post_status' => $post['status'], 'post_name' => $post['post_name'],
				'comment_status' => $post['comment_status'], 'ping_status' => $post['ping_status'],
				'guid' => $post['guid'], 'post_parent' => $post_parent, 'menu_order' => $post['menu_order'],
				'post_type' => $post['post_type'], 'post_password' => $post['post_password']
			);

			$original_post_ID = $post['post_id'];
			$postdata = apply_filters( 'tf_import_post_data_processed', $postdata, $post );

			if ( 'attachment' == $postdata['post_type'] ) {
				$remote_url = ! empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];

				// try to use _wp_attached file for upload folder placement to ensure the same location as the export site
				// e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
				$postdata['upload_date'] = $post['post_date'];
				if ( isset( $post['postmeta'] ) ) {
					foreach( $post['postmeta'] as $meta ) {
						if ( $meta['key'] == '_wp_attached_file' ) {
							if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta['value'], $matches ) )
								$postdata['upload_date'] = $matches[0];
							break;
						}
					}
				}

				if ( $post_exists ) {
					$post_id = $post_exists;	
				} else {
					$post_id = $this->process_attachment( $postdata, $remote_url );
				}
			} else {
				$post_id = $this->content_builder_id;
			}

			if ( is_wp_error( $post_id ) ) {
				$message = sprintf( __( 'Failed to import %s &#8220;%s&#8221;', 'themify-flow' ),
					$post_type_object->labels->singular_name, esc_html($post['post_title'])
				);
				
				if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
					$message .= ': ' . $post_id->get_error_message();
				$this->errors[] = $message;
				continue;
			}

			// map pre-import ID to local ID
			$this->processed_posts[intval($post['post_id'])] = (int) $post_id;

			if ( ! isset( $post['postmeta'] ) )
				$post['postmeta'] = array();

			$post['postmeta'] = apply_filters( 'tf_import_post_meta_content_builder', $post['postmeta'], $post_id, $post );

			// add/update post meta
			if ( ! empty( $post['postmeta'] ) ) {
				foreach ( $post['postmeta'] as $meta ) {
					$key = apply_filters( 'tf_import_post_meta_key', $meta['key'], $post_id, $post );
					$value = false;

					if ( '_edit_last' == $key ) {
						if ( isset( $this->processed_authors[intval($meta['value'])] ) )
							$value = $this->processed_authors[intval($meta['value'])];
						else
							$key = false;
					}

					if ( $key ) {
						// export gets meta straight from the DB so could have a serialized string
						if ( ! $value )
							$value = maybe_unserialize( $meta['value'] );

						// Only update builder content meta
						if ( in_array( $key, array('tf_builder_content', 'tf_template_style_modules') ) ) {
							update_post_meta( $post_id, $key, $value );	
						}
						do_action( 'tf_import_post_meta_content_builder', $post_id, $key, $value );
					}
				}
			}
		}

		unset( $this->posts );
	}
}

} // class_exists( 'WP_Importer' )