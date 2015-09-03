<?php
/**
 * Builder Backend Loop
 * 
 * Builder backend loop in loop archive / single lightbox.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Interface {

	/**
	 * Module Panel
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public static function builder_element_panel( $category ) {
		global $tf_module_elements; ?>
		<div class="tf_back_module_panel">
			<?php if ( count( $tf_module_elements->get_elements_by_category( $category ) ) > 0 ): ?>
			<?php foreach( $tf_module_elements->get_elements_by_category( $category ) as $element ): ?>
			<div class="tf_back_module tf_module_name_<?php echo $element->slug; ?>" data-element-slug="<?php echo $element->slug; ?>" data-element-title="<?php echo esc_attr( $element->name );?>"> 
				<strong class="tf_module_name"><?php echo esc_attr( $element->name ); ?></strong> 
				<a href="#" class="tf_add_module"><?php _e('Add', 'themify-flow') ?></a> 
			</div>
			<!-- /tf_back_module post_title -->
			<?php endforeach; ?>

			<?php endif; ?>
		</div>
		<!-- /tf_back_module_panel -->
		<?php
	}

	/**
	 * Builder editor form
	 * 
	 * @since 1.0.0
	 * @access public 
	 */
	public static function builder_row_panel( $content ) { ?>
		<div class="tf_back_row_panel">
			<?php 
			global $tf_editor_ui;
			$editable_shortcodes = array( 'tf_back_row', 'tf_back_column' );
			foreach( $editable_shortcodes as $sc ) {
				add_filter( 'shortcode_atts_' . $sc, array( $tf_editor_ui, 'shortcode_atts_render_backend' ), 10, 3 );
			}
			$tf_editor_ui->force_editable_shortcode();
			echo do_shortcode( $content ); ?>
		</div>
		<!-- /tf_back_row_panel -->
	<?php
	}
}