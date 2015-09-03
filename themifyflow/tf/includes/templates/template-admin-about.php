<div class="wrap about-wrap">
			
	<h1><?php echo sprintf( __('Welcome to Themify Flow %s', 'themify-flow'), $TF->get_version() ); ?></h1>

	<div class="about-text">
		<?php _e('Flow is a WordPress framework which allows you to customize and build your own themes.') ?>
		<iframe style="max-width: 100%; margin: 40px 0 30px; border: solid 1px #000; display: block; clear: both;" width="760" height="420" src="//www.youtube.com/embed/2JJjGvgRJuA?rel=0&amp;start=41" frameborder="0" allowfullscreen></iframe>
	</div>

	<hr>
	<h3><?php _e('Getting Started') ?></h3>

	<div class="changelog">
		<div class="feature-section col three-col">
			<div>
				<h4><span class="dashicons dashicons-category"></span> Flow Themes</h4>
				<p>Themify Flow allows you to switch, import, export, and create your own <a href="http://themifyflow.com/doc/themes" target="_blank">Flow themes</a> with drag &amp; drop.</p>
				<p><span class="dashicons dashicons-arrow-right"></span> <a href="<?php echo esc_url( add_query_arg( 'page', 'tf-themes', admin_url( 'admin.php' ) ) ); ?>">Go to Themes</a></p>
			</div>
			<div>
				<h4><span class="dashicons dashicons-media-default"></span> Flow Templates</h4>
				<p><a href="http://themifyflow.com/doc/templates" target="_blank">Templates</a> are used to render the page layout on the frontend. You can edit and design your own templates.</p>
				<p><span class="dashicons dashicons-arrow-right"></span> <a href="<?php echo esc_url( add_query_arg( 'post_type', 'tf_template', admin_url( 'edit.php' ) ) ); ?>">Go to Templates</a></p>
			</div>
			<div class="last-feature">
				<h4><span class="dashicons dashicons-admin-page"></span> Flow Template Parts</h4>
				<p><a href="http://themifyflow.com/doc/template-parts" target="_blank">Template Parts</a> are the layout pieces used in templates such as: Header, Sidebar, and Footer.</p>
				<p><span class="dashicons dashicons-arrow-right"></span> <a href="<?php echo esc_url( add_query_arg( 'post_type', 'tf_template_part', admin_url( 'edit.php' ) ) ); ?>">Go to Template Parts</a></p>
			</div>
			<div>
				<h4><span class="dashicons dashicons-admin-appearance"></span> Global Styling Panel</h4>
				<p><a href="http://themifyflow.com/doc/global-styling" target="_blank">Global Styling</a> panel allows you to customize the general styling of the theme elements (body, font, background, headings, etc.).</p>
				<p><span class="dashicons dashicons-arrow-right"></span> <a href="<?php echo esc_url( add_query_arg( 'page', 'styling-panel-frontend', admin_url( 'admin.php' ) ) ); ?>">Go to Styling</a></p>
			</div>

			<div>
				<h4><span class="dashicons dashicons-admin-generic"></span> Settings</h4>
				<p>In <a href="http://themifyflow.com/doc/settings" target="_blank">Settings</a> page, you can specify the favicon, header code, footer code, and other framework options.</p>
				<p><span class="dashicons dashicons-arrow-right"></span> <a href="<?php echo esc_url( add_query_arg( 'page', 'tf-settings', admin_url( 'admin.php' ) ) ); ?>">Go to Settings</a></p>
			</div>
			<div class="last-feature">
				<h4><span class="dashicons dashicons-forms"></span> Using Builder</h4>
				<p>If you need help with the drag &amp; drop Builder, refer to the <a href="http://themifyflow.com/doc/builder" target="_blank">Builder Documentation</a>.</p>
			</div>
		</div>
	</div>

	<h3><?php _e('Need Help?') ?></h3>

	<div class="changelog">
		<div class="feature-section">
			<ul>
				<li><span class="dashicons dashicons-arrow-right"></span> <a href="http://themifyflow.com/docs">Documentation</a></li>
				<li><span class="dashicons dashicons-arrow-right"></span> <a href="http://themifyflow.com/forums">Support Forums</a></li>
				<li><span class="dashicons dashicons-arrow-right"></span> <a href="http://themifyflow.com/contact">Contact Us</a></li>					</ul>
		</div>
	</div>

</div>