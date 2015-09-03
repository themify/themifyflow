<?php
/**
 * Archive List Post Module
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_List_Posts {
	public function __construct() {
		$this->load_modules();
		add_action( 'tf_module_elements_loaded', array( $this, 'load_elements' ) );
	}

	public function load_modules() {
		include_once( dirname( __FILE__ ) . '/class-tf-module-list-posts.php' );
	}

	public function load_elements() {
		$dir = dirname(dirname( __FILE__ )).'/archive-loop/elements';

		// Any core modules please list here
		$lists = array( 'post-title', 'post-excerpt-content', 'post-meta', 'featured-image', 'post-text' );

		foreach( $lists as $list ) {
			include_once( sprintf( '%s/%s.php', $dir, $list ) );
		}
	}
}

/** Initialize class */
new TF_List_Posts();