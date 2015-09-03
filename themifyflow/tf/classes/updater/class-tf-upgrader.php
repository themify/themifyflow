<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TF_Upgrader extends WP_Upgrader {

	var $cookies;
	var $show_before = '';

	function upgrade_strings() {
		$this->strings['up_to_date'] = __('The theme is at the latest version.', 'themify-flow');
		$this->strings['no_package'] = __('Update package not available.', 'themify-flow');
		$this->strings['downloading_package'] = __('Downloading update from <span class="code">%s</span>&#8230;', 'themify-flow');
		$this->strings['unpack_package'] = __('Unpacking the update&#8230;', 'themify-flow');
		$this->strings['remove_old'] = __('Removing the old version of the theme&#8230;', 'themify-flow');
		$this->strings['remove_old_failed'] = __('Could not remove the old theme.', 'themify-flow');
		$this->strings['process_failed'] = __('Theme update failed.', 'themify-flow');
		$this->strings['process_success'] = __('Theme updated successfully.', 'themify-flow');
	}

	function show_message() { }

	function upgrade( $theme, $url, $cookies ) {
		$this->cookies = $cookies;

		$this->init();
		$this->upgrade_strings();

		add_filter( 'upgrader_pre_install', array( $this, 'tf_upgrader_pre_install' ), 10, 2 );
		add_filter( 'upgrader_clear_destination', array( $this, 'tf_upgrader_clear_destination' ), 10, 4 );

		$this->run( array(
					'package' => $url,
					'destination' => WP_CONTENT_DIR . "/themes/$theme/",
					'clear_destination' => true,
					'clear_working' => true,
					'hook_extra' => array( 'theme' => $theme )
				) );

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_filter( 'upgrader_pre_install', array( $this, 'tf_upgrader_pre_install' ) );
		remove_filter( 'upgrader_clear_destination', array( $this, 'tf_upgrader_clear_destination' ) );

		if ( ! $this->result || is_wp_error($this->result) )
			return $this->result;
	}

	function download_package($package) {

		if ( ! preg_match('!^(http|https|ftp)://!i', $package) && file_exists($package) ) //Local file or remote?
			return $package; //must be a local file..

		if ( empty($package) )
			return new WP_Error('no_package', $this->strings['no_package']);

		$this->skin->feedback('downloading_package', $package);

		$download_file = $this->download_url($package);

		if ( is_wp_error($download_file) )
			return new WP_Error('download_failed', $this->strings['download_failed'], $download_file->get_error_message());

		return $download_file;
	}

	function download_url( $url, $timeout = 300 ) {
		//WARNING: The file is not automatically deleted, The script must unlink() the file.
		if ( ! $url )
			return new WP_Error( 'http_no_url', __( 'Invalid URL Provided.', 'themify-flow' ) );

		$tmpfname = wp_tempnam($url);
		if ( ! $tmpfname )
			return new WP_Error( 'http_no_file', __( 'Could not create Temporary file.', 'themify-flow' ) );

		$response = wp_safe_remote_get( $url, array( 'cookies' => $this->cookies, 'timeout' => $timeout, 'stream' => true, 'filename' => $tmpfname ) );

		if ( is_wp_error( $response ) ) {
			unlink( $tmpfname );
			return $response;
		}

		if ( 200 != wp_remote_retrieve_response_code( $response ) ){
			unlink( $tmpfname );
			return new WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) );
		}

		return $tmpfname;
	}

	//Hooked to pre_install
	function tf_upgrader_pre_install( $return, $theme ) {

		if ( is_wp_error( $return ) ) { //Bypass.
			return $return;
		}

		$theme = isset( $theme['theme'])  ? $theme['theme'] : '';
		if ( empty( $theme ) ) {
			return new WP_Error('bad_request', $this->strings['bad_request']);
		}
		
	}

	//Hooked to upgrade_clear_destination
	function tf_upgrader_clear_destination($removed, $local_destination, $remote_destination, $theme) {
		global $wp_filesystem;

		if ( is_wp_error( $removed ) ) {
			return $removed; //Pass errors through.
		}

		$theme = isset($theme['theme']) ? $theme['theme'] : '';
		if ( empty( $theme ) ) {
			return new WP_Error( 'bad_request', $this->strings['bad_request'] );
		}

		$themes_dir = $wp_filesystem->wp_themes_dir();
		$this_theme_dir = trailingslashit( dirname( $themes_dir . $theme ) );

		if ( ! $wp_filesystem->exists($this_theme_dir) ) {//If it's already vanished.
			return $removed;
		}

		// If theme is in its own directory, recursively delete the directory.
		if ( strpos($theme, '/') && $this_theme_dir != $themes_dir ) { //base check on if theme includes directory separator AND that it's not the root theme folder
			$deleted = $wp_filesystem->delete($this_theme_dir, true);
		} else {
			$deleted = $wp_filesystem->delete($themes_dir . $theme);
		}

		if ( ! $deleted ) {
			return new WP_Error('remove_old_failed', $this->strings['remove_old_failed']);
		}

		return true;
	}

}
?>