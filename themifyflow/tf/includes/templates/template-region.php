<?php
/**
 * This template is for single TF Template View.
 * 
 * @package TF
 * @since 1.0.0
 */
add_action( 'tf_template_render_content', 'tf_template_region_content_empty' );
function tf_template_region_content_empty() {
	if ( TF_Model::is_template_editable() ) {
		echo sprintf( '<h4>%s</h4>', __('This is Content Section', 'themify-flow') );
		echo sprintf( '<p>%s</p>', __('If you would like to add content to this section, you may do so by editing active TF Template > Add Modules', 'themify-flow') );
	}
}
include_once( 'template-render-header.php' );
include_once( 'template-render-body.php' );
include_once( 'template-render-footer.php' );