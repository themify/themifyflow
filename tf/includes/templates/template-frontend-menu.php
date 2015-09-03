<div id="tf_front_admin_menu" class="tf_front_admin_menu_wrap tf_interface">
	<div class="tf_front_admin_menu_title">
		<?php _e('Themify Flow', 'themify-flow') ?>
	</div>
	<ul>
		<?php foreach( $frontend_menus as $menu_key => $menu ):
		$meta = wp_parse_args( $menu['meta'], array(
			'class' => ''
		) );
		?>
		<li class="<?php echo 'tf_front_admin_menu_list tf_front_admin_menu_list_' . esc_attr( $menu_key ); ?>">
			<a href="<?php echo esc_url( $menu['href'] ); ?>" class="tf_front_admin_menu_item <?php echo esc_attr( $meta['class'] ); ?>">
				<?php echo esc_html( $menu['label'] ); ?>
			</a>
			<?php if ( isset( $menu['children'] ) && count( $menu['children'] ) > 0 ): ?>
			<ul class="tf_front_admin_menu_child">
				<?php foreach( $menu['children'] as $child_key => $children ): 
				$meta_children = wp_parse_args( $children['meta'], array(
					'class' => ''
				) );
				?>
				<li class="<?php echo 'tf_front_admin_menu_child_list tf_front_admin_menu_child_list_' . esc_attr( $child_key ); ?>">
					<a href="<?php echo esc_url( $children['href'] ); ?>" class="tf_front_admin_menu_item <?php echo esc_attr( $meta_children['class'] ); ?>">
						<?php echo esc_html( $children['label'] ); ?>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<!-- /tf_front_admin_menu_wrap -->