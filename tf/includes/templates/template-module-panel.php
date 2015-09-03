<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Builder Frontend Panel HTML
 */
global $TF_Layout, $post;

if ( ! TF_Model::is_tf_styling_active() && ( TF_Model::is_template_page() || TF_Model::is_content_builder_page() ) ):

$module_groups = TF_Model::get_module_group_by_category();
if ( is_object( $post ) && ! is_singular( array( 'tf_template', 'tf_template_part' ) ) ) {
	$current_template_type = 'content';
} else {
	$current_template_type = empty( $TF_Layout->type ) ? 'global' : $TF_Layout->type;
}
$tabs_exclude = array(
	'archive' => array( 'single', 'page' ),
	'single'  => array( 'archive', 'page' ),
	'page'    => array( 'archive', 'single' ),
	'global'  => array( 'archive', 'single', 'page'),
	'content' => array( 'archive', 'single', 'page', 'global')
);
?>

<div class="tf_front_panel tf_interface">	
	<div id="tf_module_panel" class="tf_module_panel clearfix">
		
		<a class="tf_slide_builder_module_panel" href="#"><?php _e('Slide', 'themify-flow') ?></a>

		<div class="tf_slide_builder_module_wrapper">
			
			<div class="tf_module_tabs">
				<ul>
					<?php foreach(TF_Model::module_types() as $type ):
                                            if ($post->post_type!='tf_template_part' &&  in_array( $type['value'], $tabs_exclude[ $current_template_type ] ) ) continue;
						 ?>
					<li><a href="#tf_module_tabs_<?php echo $type['value']; ?>"><?php echo $type['name']; ?></a></li>
					<?php endforeach; ?>
				</ul>
				
				<?php foreach( TF_Model::module_types() as $type ): 
				if ($post->post_type!='tf_template_part' &&  in_array( $type['value'], $tabs_exclude[ $current_template_type ] ) ) continue;
				?>
				<div id="tf_module_tabs_<?php echo $type['value']; ?>">
					<?php 
					if ( isset( $module_groups[ $type['value'] ] ) ) {
						foreach( $module_groups[ $type['value'] ] as $module ): ?>
						<?php $class = "tf_module module-type-{$module->slug}"; ?>

						<div class="<?php echo esc_attr($class); ?>" data-module-title="<?php echo esc_attr( $module->name ); ?>" data-module-name="<?php echo esc_attr( $module->slug ); ?>">
							<strong class="module_name"><?php echo esc_html( $module->name ); ?></strong>
							<a href="#" title="<?php _e('Add module', 'themify-flow') ?>" class="add_module" data-module-name="<?php echo esc_attr( $module->slug ); ?>"><?php _e('Add', 'themify-flow') ?></a>
						</div>
						<!-- /module -->
						<?php endforeach; ?>
					<?php } //endif ?>
				</div>
				<?php endforeach; ?>
				
			</div>
			<!-- /tf_module_tabs -->
		
		</div>
		<!-- /slide_builder_module_wrapper -->

		<div class="builder_save_front_panel">
			<a href="#" class="tf-front-save"><?php _e('Save', 'themify-flow') ?></a>
			<a href="<?php echo esc_url( TF_Model::get_builder_close_url() ); ?>" class="tf-front-close"><?php _e('Close', 'themify-flow') ?></a>
		</div>

	</div>
	<!-- /themify_builder_module_panel -->
</div>

<div style="display: none;">
	<?php
		wp_editor( ' ', 'tf_hidden_editor' );
	?>
</div>

<?php endif; // ! TF_Model::is_tf_styling_active ?>

<div id="tf_main_loader" class="tf_interface">
	<?php echo tf_loader_span(); ?>
</div>

<script type="text/javascript">
	var _tdBootstrapTemplate = <?php echo json_encode( TF_Model::read_template_data( $TF_Layout->layout_id ) ); ?>;
	var _tdBootstrapUtility = <?php echo json_encode( TF_Model::read_utility_data() ); ?>;
</script>