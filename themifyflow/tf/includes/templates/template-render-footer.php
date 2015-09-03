<?php global $TF_Layout; ?>

			<?php tf_hook_layout_after(); // hook ?>

			</div>
			<!-- /middle -->
			
		</div>
		<!-- /middlewrap -->
				
		<?php if( 'default' == $TF_Layout->footer ): ?>
		<div id="footerwrap">

			<?php tf_hook_footer_before(); // hook ?>
			<footer id="footer" class="tf_footer">
				<?php tf_hook_footer_start(); // hook ?>

				<?php
				if ( empty( $TF_Layout->region_footer ) ) {
					do_action( 'tf_template_empty_region_footer' );
				} else {
					echo $TF_Layout->render( $TF_Layout->region_footer );
				}
				?>
				
				<?php tf_hook_footer_end(); // hook ?>
			</footer>
			<!-- /#footer -->
			<?php tf_hook_footer_after(); // hook ?>
		</div>
		<!-- /#footerwrap -->
		<?php endif; ?>

	</div>
	<!-- /#pagewrap -->

	<!-- wp_footer -->
	<?php wp_footer(); ?>
	<?php tf_hook_body_end(); // hook ?>
</body>
</html>