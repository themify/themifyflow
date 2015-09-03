<?php
/**
 * Archive Post Module
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Archive_Loop {
	public function __construct() {
		$this->load_modules();
		add_action( 'tf_module_elements_loaded', array( $this, 'load_elements' ) );
	}

	public function load_modules() {
		include_once( dirname( __FILE__ ) . '/class-tf-module-archive-loop.php' );
		include_once( dirname( __FILE__ ) . '/class-tf-module-single-loop.php' );
	}

	public function load_elements() {
		$dir = dirname( __FILE__ ) . '/elements';

		// Any core modules please list here
		$lists = array( 'post-title', 'post-excerpt-content', 'post-meta', 'featured-image', 'post-text' );

		foreach( $lists as $list ) {
			include_once( sprintf( '%s/%s.php', $dir, $list ) );
		}
	}
}

/** Initialize class */
new TF_Archive_Loop();