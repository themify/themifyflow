<?php global $TF_Layout; ?>

				<?php tf_hook_content_before(); // hook ?>
				<!-- content -->
				<div id="content" class="tf_content">
			    	<?php tf_hook_content_start(); // hook ?>
									
					<?php
					if ( empty( $TF_Layout->layout_content ) ) {
						do_action( 'tf_template_render_content' );
					} else {
						do_action( 'tf_template_before_layout_content_render' );
						echo $TF_Layout->render( $TF_Layout->layout_content );	
						do_action( 'tf_template_after_layout_content_render' );
					}
					?>
			        
					<?php tf_hook_content_end(); // hook ?>
				</div>
				<!-- /content -->
			    
			    <?php tf_hook_content_after(); // hook ?>

				<?php if ( 'sidebar_none' != $TF_Layout->sidebar ): ?>
					<?php tf_hook_sidebar_before(); // hook ?>

					<aside id="sidebar" class="tf_sidebar">

						<?php tf_hook_sidebar_start(); // hook ?>
					    
					    <?php
					    if ( empty( $TF_Layout->region_sidebar ) ) {
					    	do_action( 'tf_template_empty_region_sidebar' );
					    } else {
					    	echo $TF_Layout->render( $TF_Layout->region_sidebar );
					    }
						?>
					    
						<?php tf_hook_sidebar_end(); // hook ?>

					</aside>
					<!-- /#sidebar -->

					<?php tf_hook_sidebar_after(); // hook ?>
				<?php endif; // sidebar if ?>