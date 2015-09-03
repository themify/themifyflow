<?php

/**
 * Menu Icons feature
 * 
 * Allows adding custom icons to WordPress menu items.
 * 
 * @package ThemifyFlow
 * @since 1.0.5
 */
class TF_Menu_Icons {

	private static $instance = null;

	public static function get_instance() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}

	private function __construct() {
		if( is_admin() ) {
			add_filter( 'wp_edit_nav_menu_walker', array( $this, 'wp_edit_nav_menu_walker' ) );
			add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'wp_nav_menu_item_custom_fields' ), 12, 4 );
			add_action( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item' ), 10, 3 );
			add_action( 'delete_post', array( $this, 'delete_post' ), 1, 3 );
		} else {
			add_filter( 'wp_nav_menu_args', array( $this, 'add_menu_item_title_filter' ) );
			add_filter( 'wp_nav_menu', array( $this, 'remove_menu_item_title_filter' ) );
		}
	}

	/**
	 * Setup custom walker for Nav_Menu_Edit
	 *
	 * @return string
	 */
	function wp_edit_nav_menu_walker( $walker ) {
		include_once dirname( __FILE__ ) . '/class-tf-walker-nav-menu-edit.php';
		return 'Themify_Flow_Walker_Nav_Menu_Edit';
	}

	/**
	 * Display the icon picker for menu items in the backend
	 */
	function wp_nav_menu_item_custom_fields( $item_id, $item, $depth, $args ) {
		$saved_meta = themifyflow_get_menu_icon( $item_id );
	?>
		<p class="field-icon description description-thin">
			<label for="edit-menu-item-icon-<?php echo esc_attr( $item_id ); ?>">
				<?php _e( 'Icon', 'themify' ) ?><br/>
				<input type="text" name="menu-item-icon[<?php echo esc_attr( $item_id ); ?>]" id="edit-menu-item-icon-<?php echo esc_attr( $item_id ) ?>" size="8" class="edit-menu-item-icon themify_field_icon" value="<?php echo esc_attr( $saved_meta ); ?>">
				<a class="button button-secondary hide-if-no-js tf_fa_toggle" href="#"><?php _e( 'Insert Icon', 'themify-flow' ) ?> </a>
			</label>
		</p>
	<?php }

	/**
	 * Save the icon meta for a menu item. Also removes the meta entirely if the field is cleared.
	 */
	function wp_update_nav_menu_item( $menu_id, $menu_item_db_id, $args ) {
		$meta_key = '_menu_item_icon';
		$meta_value = themifyflow_get_menu_icon( $menu_item_db_id );
		$menu_item_icon = isset( $_POST['menu-item-icon'] ) && isset( $_POST['menu-item-icon'][$menu_item_db_id] ) ? $_POST['menu-item-icon'][$menu_item_db_id] : '';
		$new_meta_value = stripcslashes( $menu_item_icon );

		if ( $new_meta_value && '' == $meta_value )
			add_post_meta( $menu_item_db_id, $meta_key, $new_meta_value, true );
		elseif ( $new_meta_value && $new_meta_value != $meta_value )
			update_post_meta( $menu_item_db_id, $meta_key, $new_meta_value );
		elseif ( '' == $new_meta_value && $meta_value )
			delete_post_meta( $menu_item_db_id, $meta_key, $meta_value );
	}

	/**
	 * Clean up the icon meta field when a menu item is deleted
	 *
	 * @param int $post_id
	 */
	function delete_post( $post_id ) {
		if( is_nav_menu_item( $post_id ) ) {
			delete_post_meta( $post_id, '_menu_item_icon' );
		}
	}

	/**
	 * Start looking for menu icons
	 */
	function add_menu_item_title_filter( $args ) {
		add_filter( 'the_title', array( $this, 'menu_icon' ), 10, 2 );
		return $args;
	}

	/**
	 * The menu is rendered, we longer need to look for menu icons
	 */
	function remove_menu_item_title_filter( $nav_menu ) {
		remove_filter( 'the_title', 'menu_icon', 10, 2 );
		return $nav_menu;
	}

	/**
	 * Append icon to a menu item
	 *
	 * @param string $title
	 * @param string $id
	 *
	 * @return string
	 */
	function menu_icon( $title, $id = '' ) {
		if ( '' != $id ) {
			if ( $icon = themifyflow_get_menu_icon( $id ) ) {
				$title = '<i class="fa ' . esc_attr( $icon ) . '"></i> ' . $title;
			}
		}

		return $title;
	}
}
TF_Menu_Icons::get_instance();


/**
 * Returns the icon name chosen for a given menu item
 *
 * @return string|null
 * @since 1.0.5
 */
function themifyflow_get_menu_icon( $item_id ) {
	return get_post_meta( $item_id, '_menu_item_icon', true );
}
