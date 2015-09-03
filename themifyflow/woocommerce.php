<?php
/**
 * Template for WooCommerce pages
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */

add_action( 'tf_template_render_content', 'woocommerce_content' );

do_action( 'tf_template_render', basename( __FILE__ ) );