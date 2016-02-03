<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Builder Main Meta Box HTML
 */
global $TF_Layout, $tf_modules, $pagenow;
$module_groups = TF_Model::get_module_group_by_category();
$current_template_type = in_array( $post->post_type, array( 'tf_template', 'tf_template_part' ) ) ? get_post_meta( $post->ID, 'tf_template_type', true ) : 'content';
$current_template_type = '' == $current_template_type ? 'global' : $current_template_type;
$tabs_exclude = array(
	'archive' => array( 'single', 'page' ),
	'single'  => array( 'archive', 'page' ),
	'page'    => array( 'archive', 'single' ),
	'global'  => array( 'archive', 'single', 'page'),
	'content' => array( 'archive', 'single', 'page', 'global')
);
?>

<div class="tf_interface tf_admin clearfix">

	<div class="tf_module_panel clearfix">
		
		<div class="tf_module_tabs">
			<ul>
				<?php foreach( TF_Model::module_types() as $type ):
				if ($post->post_type!='tf_template_part' && in_array( $type['value'], $tabs_exclude[ $current_template_type ] ) ) continue; ?>
				<li><a href="#tf_module_tabs_<?php echo $type['value']; ?>"><?php echo $type['name']; ?></a></li>
				<?php endforeach; ?>
			</ul>
			
			<?php foreach( TF_Model::module_types() as $type ): 
			if ($post->post_type!='tf_template_part' && in_array( $type['value'], $tabs_exclude[ $current_template_type ] ) ) continue;
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
	<!-- /themify_builder_module_panel -->

	<div class="tf_row_panel clearfix">

		<div id="tf_row_wrapper" class="tf_content_builder">
			<?php   
                                global $wpdb;
                                $value = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $post->ID, 'tf_builder_content') );
				$value = $value?current($value):false;
                                $builder_content = in_array( $post->post_type, array( 'tf_template', 'tf_template_part' ) ) ? $post->post_content : ($value?$value->meta_value:false);
                                echo do_shortcode( $builder_content );
			?>
		</div> <!-- /#themify_builder_row_wrapper -->

		<p class="tf_save">
			<?php if ( $pagenow !== 'post-new.php' ): ?>
			<a href="<?php echo esc_url( add_query_arg( 'tf', 1, get_permalink( $post->ID ) ) ); ?>" class="tf_switch_frontend"><?php _e('Switch to frontend', 'themify-flow') ?></a>
			<?php endif; ?>
			<a href="#" id="tf_main_save" class="builder_button tf_btn"><?php _e('Save', 'themify-flow') ?></a>
		</p>

	</div>
	<!-- /themify_builder_row_panel -->

	<div style="display: none;">
		<?php
			wp_editor( ' ', 'tf_hidden_editor' );
		?>
	</div>

</div>
<!-- /themify_builder -->

<script type="text/javascript">
        <?php if($value):?>
            jQuery(document).ready(function(){
                jQuery('#meta-'+'<?php echo $value->meta_id?>').remove();
            });
        <?php endif;?>
	var _tdBootstrapTemplate = <?php echo json_encode( TF_Model::read_template_data( $post->ID ) ); ?>;
	var _tdBootstrapUtility = <?php echo json_encode( TF_Model::read_utility_data() ); ?>;
	var _tdBootstrapStyles = <?php echo json_encode( $this->get_bootstrap_styles( $post->ID ) ); ?>;
</script>