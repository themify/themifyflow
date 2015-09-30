<?php
/**
 * ADD TF Functions below
 */

/**
 * Get grid menu list
 * @param string $handle 
 * @param string $set_gutter 
 * @return string
 */
function tf_grid_lists( $handle = 'row', $set_gutter = null, $args = array() ) {
	$grid_lists = TF_Model::get_grid_settings();
	$gutters = TF_Model::get_grid_settings( 'gutter' );
	$selected_gutter = is_null( $set_gutter ) ? '' : $set_gutter;
	$args = wp_parse_args( $args, array(
		'grid_menu_class' => 'grid_menu tf_interface',
		'grid_icon_class' => 'grid_icon'
	) );
	ob_start();
	?>
	<div class="<?php echo esc_attr( $args['grid_menu_class'] );?>" data-handle="<?php echo $handle; ?>">
		<div class="ti-layout-column3 <?php echo esc_attr( $args['grid_icon_class'] ); ?>"></div>
		<div class="tf_grid_list_wrapper">
			<ul class="tf_grid_list clearfix">
				<?php foreach( $grid_lists as $row ): ?>
				<li>
					<ul>
						<?php foreach( $row as $li ): ?>
							<li><a href="#" class="tf_column_select <?php echo esc_attr( 'grid-layout-' . implode( '-', $li['data'] ) ); ?>" data-handle="<?php echo $handle; ?>" data-grid="<?php echo esc_attr( json_encode( $li['data'] ) ); ?>"><img src="<?php echo esc_url( $li['img'] ); ?>"></a></li>
						<?php endforeach; ?>
					</ul>
				</li>
				<?php endforeach; ?>
			</ul>

			<select class="gutter_select" data-handle="<?php echo $handle; ?>">
				<?php foreach( $gutters as $gutter ): ?>
				<option value="<?php echo esc_attr( $gutter['value'] ); ?>"<?php selected( $selected_gutter, $gutter['value'] ); ?>><?php echo $gutter['name']; ?></option>
				<?php endforeach; ?>
			</select>
			<small><?php _e('Gutter Spacing', 'themify-flow') ?></small>

		</div>
		<!-- /themify_builder_grid_list_wrapper -->
	</div>
	<!-- /grid_menu -->
	<?php

	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

/**
 * Create a zip files
 * @param array $files 
 * @param string $destination 
 * @param boolean $overwrite 
 */
function tf_create_zip( $files = array(), $destination = "", $overwrite = false ) {
	if( file_exists( $destination ) && ! $overwrite ) { return false; }
	if ( ! class_exists( 'ZipArchive' ) ) return false;
	
	$valid_files = array();
	if( is_array( $files ) ) {
		foreach( $files as $file ) {
			if( file_exists( $file ) ) {
				$valid_files[] = $file;
			}
		}
	}
	if( count( $valid_files ) ) {
		$zip = new ZipArchive();
		if( $zip->open( $destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE ) !== true ) {
			return false;
		}
		foreach($valid_files as $file) {
			$zip->addFile($file,pathinfo($file,PATHINFO_BASENAME));
		}
		$zip->close();
		return file_exists($destination);
	} else {
		return false;
	}
}

/**
 * Return list of image sizes with labels for translation.
 * @param bool $nested
 * @return mixed|void
 * @since 1.0.0
 */
function tf_get_image_sizes_list( $nested = true ) {
	$size_names = apply_filters( 'image_size_names_choose',
		array(
			'thumbnail' => __( 'Thumbnail', 'themify-flow' ),
			'medium' 	=> __( 'Medium', 'themify-flow' ),
			'large' 	=> __( 'Large', 'themify-flow' ),
			'full' 		=> __( 'Original Image', 'themify-flow' )
		)
	);
	$out = array(
		array( 'value' => 'blank', 'name' => '' ),
	);
	foreach( $size_names as $size => $label ) {
		$out[] = array( 'value' => $size, 'name' => $label );
	}
	return apply_filters( 'tf_get_image_sizes_list', $nested ? $out : $size_names, $nested );
}

/**
 * Get RGBA color format from hex color
 *
 * @return string
 */
function tf_get_rgba_color( $color ) {
	$color = explode( '_', $color );
	$opacity = isset( $color[1] ) ? $color[1] : '1';
	return 'rgba(' . tf_hex2rgb( $color[0] ) . ', ' . $opacity . ')';
}

/**
 * Converts color in hexadecimal format to RGB format.
 *
 * @since 1.0.0
 *
 * @param string $hex Color in hexadecimal format.
 * @return string Color in RGB components separated by comma.
 */
function tf_hex2rgb( $hex ) {
	$hex = str_replace( "#", "", $hex );

	if ( strlen( $hex ) == 3 ) {
		$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
		$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
		$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
	} else {
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
	}
	return implode( ',', array( $r, $g, $b ) );
}

/**
 * Get images from gallery shortcode.
 * 
 * @since 1.0.0
 * @return boolean|object
 */
function tf_get_images_from_gallery_shortcode( $shortcode ) {
	if ( empty( $shortcode ) ) return false;

	preg_match( '/\[?gallery.*ids=.(.*).\]?/', $shortcode, $ids );
	$ids_string = str_replace('quot;', '', $ids[1]);
	$image_ids = explode( ",", $ids_string );
	$orderby = tf_get_gallery_param_option( $shortcode, 'orderby' );
	$orderby = $orderby != '' ? $orderby : 'post__in';
	$order = tf_get_gallery_param_option( $shortcode, 'order' );
	$order = $order != '' ? $order : 'ASC';

	// Check if post has more than one image in gallery
	return get_posts( array(
		'post__in' => $image_ids,
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'numberposts' => -1,
		'orderby' => $orderby,
		'order' => $order
	) );
}

/**
 * Get gallery shortcode options.
 * 
 * @since 1.0.0
 * @param $shortcode
 * @param $param
 */
function tf_get_gallery_param_option( $shortcode, $param = 'link' ) {
	if ( $param == 'link' ) {
		preg_match( '/\[?gallery .*?(?=link)link=.([^\']?+)./si', $shortcode, $out );
	} elseif ( $param == 'order' ) {
		preg_match( '/\[?gallery .*?(?=order)order=.([^\']?+)./si', $shortcode, $out );	
	} elseif ( $param == 'orderby' ) {
		preg_match( '/\[?gallery .*?(?=orderby)orderby=.([^\']?+)./si', $shortcode, $out );	
	} elseif ( $param == 'columns' ) {
		preg_match( '/\[?gallery .*?(?=columns)columns=.([^\']?+)./si', $shortcode, $out );	
	}
	
	$out = isset($out[1]) ? explode( '"', $out[1] ) : array('');
	return $out[0];
}

/**
 * Escape shortcode attributes to be used in html5 data attribute.
 * 
 * @since 1.0.0
 * @param string $value
 */
function tf_escape_atts($value) {
	if ( is_array($value) ) {
		$value = array_map('tf_escape_atts', $value);
	} elseif ( is_object($value) ) {
		$vars = get_object_vars( $value );
		foreach ($vars as $key=>$data) {
			$value->{$key} = tf_escape_atts( $data );
		}
	} elseif ( is_string( $value ) ) {
		$value = html_entity_decode($value);
	}

	return $value;
}

/**
 * Unset unwanted key.
 * 
 * @since 1.0.0
 * @param array &$array 
 * @param string $unwanted_key 
 * @return array
 */
function tf_recursive_unset( &$array, $unwanted_key ) {
	if ( isset( $array[ $unwanted_key ] ) ) 
		unset($array[$unwanted_key]);
	
	foreach ( $array as &$value ) {
		if ( is_array( $value ) ) {
			tf_recursive_unset( $value, $unwanted_key );
		}
	}
}

function tf_parse_menu( $menus ) {
	$output = array();
	if ( count( $menus ) > 0 ) {
		foreach( $menus as $key => $args ) {
			
			$defaults = array(
				'parent' => false,
				'children' => array(),
				'meta'   => array(),
				'href' => ''
			);

			// Do the same for 'meta' items.
			if ( ! empty( $defaults['meta'] ) && ! empty( $args['meta'] ) )
				$args['meta'] = wp_parse_args( $args['meta'], $defaults['meta'] );

			$args = wp_parse_args( $args, $defaults );

			if ( $args['parent'] ) {
				$output[ $args['parent'] ]['children'][ $key ] = $args;
			} else {
				unset( $args['parent'] );
				$output[ $key ] = $args;
			}

		}
	}
	return $output;
}

/**
 * Print loader html markup.
 * 
 * @since 1.0.0
 * @param string $size 
 * @return string
 */
function tf_loader_span( $size = 'large' ) {
	$class = 'large' != $size ? 'small' : 'large';
	$out = sprintf( '<div class="tf_loader %s">', $class );
	$i = 1;
	while ( $i <= 8 ) {
		$out .= '<div class="tf_loader_dot"></div>';
		$i++;
	}
	$out .= '</div>';
	return $out;
}

/**
 * Get all sc_id values from content.
 * 
 * @since 1.0.0
 * @param string $content 
 * @return array
 */
function tf_get_shortcode_ids( $content ) {
	$return = array();
	if ( false === strpos( $content, '[' ) ) {
		return $return;
	}

	preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER );
	if ( empty( $matches ) )
		return $return;

	foreach ( $matches as $shortcode ) {
		$atts = shortcode_parse_atts( $shortcode[3] );
		if ( isset( $atts['sc_id'] ) ) 
			array_push( $return, $atts['sc_id'] );

		if ( ! empty( $shortcode[5] ) ) {
			$merge = tf_get_shortcode_ids( $shortcode[5], $shortcode[2] );
			if ( count( $merge ) > 0 ) {
				$return = array_merge( $return, $merge );
			}
		}
	}
	return $return;
}

/**
 * Replace builder content sc_id with new unique shortcode id.
 * 
 * @since 1.0.0
 * @param string $content 
 * @return string
 */
function tf_generate_new_shortcode_ids( $content ) {
	$unique_ids = tf_get_shortcode_ids( $content );
	if ( count( $unique_ids ) > 0 ) {
		foreach( $unique_ids as $id ) {
			$new_id = TF_Model::generate_block_id();
			$content = str_replace( $id, $new_id, $content );
                        usleep(300);//because php will not have time to generate new id and will return the same id
		}
	}
	return $content;
}

/**
 * Determine whether ajax request from frontend.
 * 
 * @since 1.0.0
 * @return boolean
 */
function tf_request_is_frontend_ajax() {

	$script_filename = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
 
	//Try to figure out if frontend AJAX request... If we are DOING_AJAX; let's look closer
	if((defined('DOING_AJAX') && DOING_AJAX)) {
		//From wp-includes/functions.php, wp_get_referer() function.
		//Required to fix: https://core.trac.wordpress.org/ticket/25294
		$ref = '';
		if ( ! empty( $_REQUEST['_wp_http_referer'] ) )
			$ref = wp_unslash( $_REQUEST['_wp_http_referer'] );
		elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) )
			$ref = wp_unslash( $_SERVER['HTTP_REFERER'] );

		//If referer does not contain admin URL and we are using the admin-ajax.php endpoint, this is likely a frontend AJAX request
		if(((strpos($ref, admin_url()) === false) && (basename($script_filename) === 'admin-ajax.php'))) 
			return true;
	}
 
	//If no checks triggered, we end up here - not an AJAX request.
	return false;
}

/**
 * Gets an HTML element's attributes. First argument must be the slug/ID of the element,
 * the rest of the argument are passed to the filter applied.
 *
 * @return string
 */
function tf_get_attr() {

	$args   = func_get_args();
	$slug   = array_shift( $args );
	$out    = '';
	$attr   = apply_filters( "tf_attr_{$slug}", array(), $args );

	if( ! empty( $attr ) )
		foreach ( $attr as $name => $value )
			$out .= !empty( $value ) ? sprintf( ' %s="%s"', esc_html( $name ), esc_attr( $value ) ) : esc_html( " {$name}" );

	return trim( $out );
}

/**
 * Post <article> element attributes.
 *
 * @param  array   $attr
 * @return array
 */
function tf_attr_post( $attr, $test ) {

	$post = get_post();

	// Make sure we have a real post first.
	if ( !empty( $post ) ) {

		$attr['id']        = 'post-' . get_the_ID();
		$attr['class']     = join( ' ', get_post_class( 'tf_post clearfix' ) );
		$attr['itemscope'] = 'itemscope';
		$attr['itemtype'] = 'http://schema.org/Article';

	} else {

		$attr['id']    = 'post-0';
		$attr['class'] = join( ' ', get_post_class() );
	}

	return $attr;
}
add_filter( 'tf_attr_post', 'tf_attr_post', 10, 2 );