<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TF_Update_Check {

	var $name;
	/**
	 * @var string $nicename Human-readable name of the plugin.
	 */
	var $nicename = '';
	/**
	 * @var string $nicename_short Human-readable name of the plugin where 'Flow' or other prefixes have been removed.
	 */
	var $nicename_short = '';
	/**
	 * @var string $update_type Whether this is a 'plugin' update or an 'addon' update.
	 */
	var $update_type = '';
	var $version;
	var $versions_url;
	var $package_url;
	/**
	 * Stores the parsed XML for versions.
	 */
	var $versions_xml = null;

	/**
	 * @var string $page The ID of this page
	 */
	var $page = 'tf-settings';

	/**
	 * Class constructor.
	 * 
	 * @since 1.0.0
	 * 
	 * @param array $name List including theme name as slug, human-readable theme name and type of update.
	 * @param string $version Local version of theme installed.
	 */
	public function __construct( $name, $version ) {
		// New name parameter
		$this->name = $name['name'];
		$this->nicename = $name['nicename'];
		$this->update_type = $name['update_type'];
		
		$this->nicename_short = str_replace( 'Flow ', '', $this->nicename );
		$this->version = $version;
		$this->versions_url = 'https://themifyflow.com/versions/versions.xml';
		$this->package_url = "https://themifyflow.com/files/{$this->name}/{$this->name}.zip";

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			delete_transient( "{$this->name}_new_update" );
			delete_transient( "{$this->name}_check_update" );
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] == $this->page ) {
			if ( isset( $_GET['action'] ) && 'upgrade' == $_GET['action'] ) {
				add_action( 'admin_notices', array( $this, 'tf_updater' ) );
				add_filter( 'update_theme_complete_actions', array( $this, 'tf_upgrade_complete' ), 10, 2 );
			} else {
				add_action( 'admin_notices', array( $this, 'check_version' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
				add_action( 'tf_after_settings', array( $this, 'dialog' ) );
			}
		} else {
			add_action( 'admin_notices', array( $this, 'tf_notify_elsewhere' ) );
		}

		// Executes themify_updater function using wp_ajax_ action hook
		add_action( 'wp_ajax_tf_validate_login', array( $this, 'tf_validate_login' ) );
	}

	/** 
	 * Replace link to themes page with link to Flow settings page.
	 * 
	 * @since 1.0.0
	 * 
	 * @param array $update_actions Links to add after update process ends.
	 * @param string $theme Slug of the theme that just got updated.
	 */
	function tf_upgrade_complete( $update_actions, $theme ) {
		if ( $theme == $this->name ) {
			$update_actions['themes_page'] = '<a href="' . esc_url( add_query_arg( array( 'page' => $this->page, 'tf_updated' => 'true' ), self_admin_url( 'admin.php' ) ) ) . '" title="' . __( 'Return to Flow Settings', 'themify-flow' ) . '" target="_parent">' . __( 'Return to Flow Settings', 'themify-flow' ) . '</a>';
		}
		return $update_actions;
	}

	/**
	 * Launch dialog. For the future.
	 * 
	 * @since 1.0.0
	 */
	function dialog() {
		$login = 'false';
		$action_url = add_query_arg( array( 'page' => $this->page, 'action' => 'upgrade', 'login' => $login ), admin_url( 'admin.php' ) );
		?>
		<div class="prompt-box">
			<div class="show-login">
				<form id="themify_update_form" method="post" action="<?php echo esc_url( $action_url ); ?>">
					<p class="prompt-msg"><?php _e( 'Enter your Themify login info to upgrade', 'themify-flow' ); ?></p>
					<p><label><?php _e( 'Username', 'themify-flow' ); ?></label> <input type="text" name="username" class="username" value=""/></p>
					<p><label><?php _e( 'Password', 'themify-flow' ); ?></label> <input type="password" name="password" class="password" value=""/></p>
					<input type="hidden" value="<?php echo esc_attr( $login ); ?>" name="login" />
					<p class="pushlabel"><input name="login" type="submit" value="<?php _e( 'Login', 'themify-flow' ); ?>" class="button tf-upgrade-login" /></p>
				</form>
			</div>
			<div class="show-error">
				<p class="error-msg"><?php _e( 'There were some errors updating the theme', 'themify-flow' ); ?></p>
			</div>
		</div>
		<div class="overlay"></div>
		<?php
	}

	/**
	 * Display notice saying that an update is available.
	 * 
	 * @since 1.0.0
	 * 
	 * @uses TF_Update_Check::check_version()
	 * 
	 */
	function tf_notify_elsewhere() {
		$notice = $this->check_version( 'return' );
		if ( ! empty( $notice ) && is_array( $notice ) && ! empty( $notice['version'] ) ) {
			printf( '<p class="update-nag">' . __( '%s version %s is now available.', 'themify-flow' ), $notice['theme'], $notice['version'] );
			if ( current_user_can( 'update_themes' ) ) {
				printf( ' ' . __( 'Go to <a href="%s">Flow Settings</a> to update.', 'themify-flow' ), add_query_arg( 'page', 'tf-settings', admin_url( 'admin.php' ) ) );
			} else {
				echo ' ' . __( 'Notify your site administrator that the update is ready.', 'themify-flow' );
			}
			echo '</p>';
		}
	}

	/**
	 * Verify if there's an update available.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $return If it's empty, nothing will be returned. Otherwise an array with the theme name and version is returned.
	 * 
	 * @return array|void
	 */
	public function check_version( $return = '' ) {
		$notice = '';

		// Check update transient
		$current = get_transient( "{$this->name}_check_update" ); // get last check transient
		$newUpdate = get_transient( "{$this->name}_new_update" ); // get new update transient
		$timeout = 60;
		$time_not_changed = isset( $current->lastChecked ) && $timeout > ( time() - $current->lastChecked );
		$new_version = '';

		if ( is_object( $newUpdate ) && $time_not_changed ) {
			if ( version_compare( $this->version, $newUpdate->version, '<') ) {
				$notice .= sprintf( '<div class="update-nag update %s">' . __( '%s version %s is now available. <a href="%s" title="" class="%s" target="%s" data-theme="%s" data-package_url="%s" data-nicename_short="%s" data-update_type="%s">Update now</a> or view the <a href="%s" title="" class="themify_changelogs" target="_blank" data-changelog="%s">changelog</a> for details.', 'themify-flow') . '</div>',
					esc_attr( $newUpdate->login ),
					$this->nicename,
					$newUpdate->version,
					esc_url( $newUpdate->url ),
					esc_attr( $newUpdate->class ),
					esc_attr( $newUpdate->target ),
					esc_attr( $this->name ),
					esc_attr( $this->package_url ),
					esc_attr( $this->nicename_short ),
					esc_attr( $this->update_type ),
					esc_url( 'https://themifyflow.com/changelogs/' . $this->name . '.txt' ),
					esc_url( 'https://themifyflow.com/changelogs/' . $this->name . '.txt' )
				);
				$new_version = $newUpdate->version;
			}
		} else {
			// get remote version
			$remote_version = $this->get_remote_version();

			// delete update checker transient
			delete_transient( "{$this->name}_check_update" );

			$class = '';
			$target = '';
			$url = '#';
			
			$new = new stdClass();
			$new->login = '';
			$new->version = $remote_version;
			$new->url = $url;
			$new->class = 'tf-upgrade-theme';
			$new->target = $target;

			if ( version_compare( $this->version, $remote_version, '<' ) ) {
				set_transient( "{$this->name}_new_update", $new );
				$notice .= sprintf( __('<div class="update update-nag %s">%s version %s is now available. <a href="%s" title="" class="%s" target="%s" data-theme="%s" data-package_url="%s" data-nicename_short="%s" data-update_type="%s">Update now</a> or view the <a href="%s" title="" class="themify_changelogs" target="_blank" data-changelog="%s">changelog</a> for details.</div>', 'themify-flow'),
					esc_attr( $new->login ),
					$this->nicename,
					$new->version,
					esc_url( $new->url ),
					esc_attr( $new->class ),
					esc_attr( $new->target ),
					esc_attr( $this->name ),
					esc_attr( $this->package_url ),
					esc_attr( $this->nicename_short ),
					esc_attr( $this->update_type ),
					esc_url( 'https://themifyflow.com/changelogs/' . $this->name . '.txt' ),
					esc_url( 'https://themifyflow.com/changelogs/' . $this->name . '.txt' )
				);
				$new_version = $new->version;
			}

			// update transient
			$this->set_update();
		}

		if ( ! empty( $return ) ) {
			return array(
				'theme'   => $this->nicename,
				'version' => $new_version,
			);
		}

		echo $notice;
	}

	/**
	 * Fetch remove XML file and look for the version number.
	 * 
	 * @since 1.0.0
	 * 
	 * @return string $version Remote (and latest) version of the theme.
	 */
	public function get_remote_version() {
		$version = '';

		$response = wp_remote_get( $this->versions_url );
		if( is_wp_error( $response ) ) {
			return $version;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( is_wp_error( $body ) || empty( $body ) ) {
			return $version;
		}

		if ( is_null( $this->versions_xml ) ) {
			$this->build_versions_xml_info( $body );
		}
		$query = "//version[@name='".$this->name."']";
		$elements = $this->versions_xml->query( $query );
		if ( $elements->length ) {
			foreach ( $elements as $field ) {
				$version = $field->nodeValue;
			}
		}	

		return $version;
	}

	/**
	 * Builder an XML object to fetch info.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $body XML in string format.
	 */
	public function build_versions_xml_info( $body ) {
		$version = '';
		$xml = new DOMDocument;
		$xml->loadXML( trim( $body ) );
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;
		$this->versions_xml = new DOMXPath( $xml );
	}

	/**
	 * Save transient to cache the update check.
	 * 
	 * @since 1.0.0
	 */
	public function set_update() {
		$current = new stdClass();
		$current->lastChecked = time();
		set_transient( "{$this->name}_check_update", $current );
	}

	/**
	 * Fetch transient to check the update version.
	 * 
	 * @since 1.0.0
	 * 
	 * @return bool
	 */
	public function is_update_available() {
		$newUpdate = get_transient( "{$this->name}_new_update" ); // get new update transient

		if ( false === $newUpdate ) {
			$new_version = $this->get_remote_version();
		} else {
			$new_version = $newUpdate->version;
		}

		if ( version_compare( $this->version, $new_version, '<') ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Enqueue styles and scripts and pass JS variables.
	 * 
	 * @since 1.0.0
	 */
	public function enqueue() {
		global $TF;
		wp_enqueue_style( 'tf-updater', $TF->framework_uri() . '/assets/css/tf-updater.css' );
		wp_enqueue_script( 'tf-updater', $TF->framework_uri() . '/assets/js/tf/tf-updater.js', array('jquery'), false, true );
		wp_localize_script( 'tf-updater', 'tfUpdater', array(
			'confirm_reset_styling'	=> __( 'Are you sure you want to reset your theme style?', 'themify-flow' ),
			'confirm_reset_settings' => __( 'Are you sure you want to reset your theme settings?', 'themify-flow' ),
			'confirm_refresh_webfonts'	=> __( 'Are you sure you want to refresh the Google Fonts list? This will also save the current settings.', 'themify-flow' ),
			'confirm_update' => __( 'Make sure to backup before upgrading. Files and settings may get lost or changed.', 'themify-flow' ),
			'confirm_delete_image' => __( 'Do you want to delete this image permanently?', 'themify-flow' ),
			'invalid_login' => __( 'Invalid username or password.<br/>Contact <a href="https://themifyflow.com/contact">Themify</a> for login issues.', 'themify-flow' ),
			'unsuscribed' => __( 'Your membership might be expired. Login to <a href="https://themifyflow.com/member">Themify</a> to check.', 'themify-flow' ),
			'enable_zip_upload' => sprintf(
				__( 'Go to your <a href="%s">Network Settings</a> to enable <strong>zip</strong>, <strong>txt</strong> and <strong>svg</strong> extensions in <strong>Upload file types</strong>  field.', 'themify-flow' ),
				esc_url( network_admin_url('settings.php').'#upload_filetypes' )
			),
			'filesize_error' => __( 'The file you are trying to upload exceeds the maximum file size allowed.', 'themify-flow' ),
			'filesize_error_fix' => sprintf(
				__( 'Go to your <a href="%s">Network Settings</a> and increase the value of the <strong>Max upload file size</strong>.', 'themify-flow' ),
				esc_url( network_admin_url('settings.php').'#fileupload_maxk' )
			),
			'updateURL' => add_query_arg( array(
				'page' => 'tf-settings',
				'action' => 'upgrade',
				'type' => 'theme',
				'login' => 'false',
			), admin_url( 'admin.php' ) ),
		)
	);
	}

	/**
	 * Validate login credentials against Themify's membership system
	 * 
	 * @since 1.0.0
	 */
	function tf_validate_login( $die = true ) {
		$response = wp_remote_post(
			'https://themifyflow.com/files/themify-login.php',
			array(
				'timeout' => 300,
				'headers' => array(),
				'body' => array(
					'amember_login' => $_POST['username'],
					'amember_pass'  => $_POST['password']
				)
		    )
		);

		//Was there some error connecting to the server?
		if ( is_wp_error( $response ) ) {
			$out = 'Error ' . $response->get_error_code() . ': ' . $response->get_error_message( $response->get_error_code() );
			if ( $die ) {
				echo $out;
				die();
			}
		}

		//Connection to server was successful. Test login cookie
		$amember_nr = false;
		foreach ( $response['cookies'] as $cookie ) {
			if($cookie->name == 'amember_nr'){
				$amember_nr = true;
			}
		}
		if ( ! $amember_nr ) {
			$out = 'invalid';
			if ( $die ) {
				echo $out;
				die();
			}
		}

		$subs = json_decode($response['body'], true);
		$sub_match = 'false';

		if ( is_array( $subs ) ) {
			foreach ( $subs as $key => $value ) {
				if ( isset( $_POST['update_type'] ) && 'addon' === $_POST['update_type'] ) {
					if(stripos($value['title'], 'Addon Bundle') !== false){
						$sub_match = 'true';
						break;
					}
				}
				if ( isset( $_POST['nicename_short'] ) && stripos($value['title'], isset( $_POST['nicename_short'] ) ) !== false ) {
					$sub_match = 'true';
					break;
				}
				if(stripos($value['title'], 'Master Club') !== false){
					$sub_match = 'true';
					break;
				}
			}
		}
		$out = $sub_match;
		if ( $die ) {
			echo $out;
			die();
		}
	}

	/**
	 * Updater called through wp_ajax_ action
	 * 
	 * @since 1.0.0
	 */
	function tf_updater() {
		$url = isset( $_POST['package_url'] ) ? $_POST['package_url'] : null;
		$slug = isset( $_POST['theme'] ) ? $_POST['theme'] : null;

		if( ! $url || ! $slug ) return;

		// If login is required
		if ( 'true' == $_GET['login'] ) {
                    $this->tf_validate_login( false );
                    return;
		}

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		global $TF;
		require_once $TF->framework_path() . '/classes/updater/class-tf-upgrader.php';

		$upgrader = new TF_Upgrader( new Theme_Upgrader_Skin(
			array(
				'theme' => $slug,
				'title' => __( 'Update Flow', 'themify-flow' )
			)
		));

		$response_cookies = ( isset( $response ) && isset( $response['cookies'] ) ) ? $response['cookies'] : '';
		$upgrader->upgrade( $slug, $url, $response_cookies );

		//if we got this far, everything went ok!	
		die();
	}
}