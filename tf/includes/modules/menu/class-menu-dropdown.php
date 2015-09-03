<?php

/**
 * Nav Menu Dropdown
 *
 * Creates the select navigation menu used for mobile devices
 */
class Walker_Nav_Menu_TF_Dropdown extends Walker_Nav_Menu {

	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth); // don't output children opening tag (`<ul>`)
	}

	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth); // don't output children closing tag
	}

	/**
	* Start the element output.
	*
	* @param  string $output Passed by reference. Used to append additional content.
	* @param  object $item   Menu item data object.
	* @param  int $depth     Depth of menu item. May be used for padding.
	* @param  array $args    Additional strings.
	* @return void
	*/
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
 		$url = '#' !== $item->url ? $item->url : '';
 		$output .= '<option value="' . $url . '">' . str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;', $depth ) . $item->title;
	}	

	function end_el( &$output, $item, $depth = 0, $args = array() ) {
		$output .= "</option>\n"; // replace closing </li> with the option tag
	}
}

/**
 * Create HTML dropdown list of pages.
 *
 * @since 1.0.0
 * @uses Walker
 */
class Walker_TF_PageDropdown extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	public $tree_type = 'page';

	/**
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	public $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object.
	 * @param int $depth Depth of page in reference to parent pages. Used for padding.
	 * @param array $args Uses 'selected' argument for selected page to set selected HTML attribute for option element.
	 * @param int $id
	 */
	public function start_el( &$output, $page, $depth = 0, $args = array(), $id = 0 ) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		$output .= "\t<option class=\"level-$depth\" value=\"" . get_permalink( $page->ID ) . '\"';
		if ( $page->ID == $args['selected'] )
			$output .= ' selected="selected"';
		$output .= '>';

		$title = $page->post_title;
		if ( '' === $title ) {
			$title = sprintf( __( '#%d (no title)' ), $page->ID );
		}

		/**
		 * Filter the page title when creating an HTML drop-down list of pages.
		 *
		 * @since 3.1.0
		 *
		 * @param string $title Page title.
		 * @param object $page  Page data object.
		 */
		$title = apply_filters( 'list_pages', $title, $page );
		$output .= $pad . esc_html( $title );
		$output .= "</option>\n";
	}
}
