<?php
$themes = TF_Model::prepare_themes_for_js();
wp_enqueue_script( 'tf-theme-js', $TF->framework_uri() . '/assets/js/tf/theme.js', array( 'jquery' ), $TF->get_version(), true );
wp_localize_script( 'tf-theme-js', '_tfThemeSettings', array(
	'themes'   => $themes,
	'settings' => array(
		'canInstall'    => ( ! is_multisite() && current_user_can( 'install_themes' ) ),
		'confirmDelete' => __( "Are you sure you want to delete this theme?\n\nClick 'Cancel' to go back, 'OK' to confirm the delete." ),
		'adminUrl'      => parse_url( admin_url(), PHP_URL_PATH ),
	),
 	'l10n' => array(
 		'addNew'            => __( 'Add New Theme', 'themify-flow' ),
 		'search'            => __( 'Search Installed Themes', 'themify-flow' ),
 		'searchPlaceholder' => __( 'Search installed themes...', 'themify-flow' ), // placeholder (no ellipsis)
		'themesFound'       => __( 'Number of Themes found: %d', 'themify-flow' ),
		'noThemesFound'     => __( 'No themes found. Try a different search.', 'themify-flow' ),
  	),
) );
?>

<div class="wrap">
	
	<h2><?php _e('Flow Themes', 'themify-flow') ?>
		<span class="title-count theme-count"><?php echo count( $themes ); ?></span>
		<a data-type="theme" class="add-new-h2 tf_lightbox_new" href="#"><?php _e('Add New', 'themify-flow') ?></a>
		<a data-type="theme" class="add-new-h2 tf_lightbox_import" href="#"><?php _e('Add via Import', 'themify-flow') ?></a>
	</h2>

	<div class="theme-browser tf-theme-browser">
		<div class="themes tf-themes">
			
			<?php
			/*
			 * This PHP is synchronized with the tmpl-theme template below!
			 */

			foreach ( $themes as $theme ) :
				$aria_action = esc_attr( $theme['id'] . '-action' );
				$aria_name   = esc_attr( $theme['id'] . '-name' );
				?>
			<div class="theme tf-theme<?php if ( $theme['active'] ) echo ' active'; ?>" tabindex="0" aria-describedby="<?php echo $aria_action . ' ' . $aria_name; ?>">
				<?php if ( ! empty( $theme['screenshot'][0] ) ) { ?>
					<div class="theme-screenshot">
						<img src="<?php echo $theme['screenshot'][0]; ?>" alt="" />
					</div>
				<?php } else { ?>
					<div class="theme-screenshot blank"></div>
				<?php } ?>
				<span class="more-details" id="<?php echo $aria_action; ?>"><?php _e( 'Theme Details' ); ?></span>
				<div class="theme-author"><?php printf( __( 'By %s' ), $theme['author'] ); ?></div>

				<?php if ( $theme['active'] ) { ?>
					<h3 class="theme-name" id="<?php echo $aria_name; ?>">
						<?php
						/* translators: %s: theme name */
						printf( __( '<span>Active:</span> %s' ), $theme['name'] );
						?>
					</h3>
				<?php } else { ?>
					<h3 class="theme-name" id="<?php echo $aria_name; ?>"><?php echo $theme['name']; ?></h3>
				<?php } ?>

				<div class="theme-actions">

				<?php if ( $theme['active'] ) { ?>
					<a class="button button-primary tf_lightbox_edit" href="#" data-type="theme" data-post-id="<?php echo $theme['theme_id']; ?>"><?php _e( 'Edit', 'themify-flow' ); ?></a>
				<?php } else { ?>
					<a class="button button-secondary" href="<?php echo $theme['actions']['activate']; ?>"><?php _e( 'Activate', 'themify-flow' ); ?></a>
					<a class="button button-primary tf_lightbox_edit" href="#" data-type="theme" data-post-id="<?php echo $theme['theme_id']; ?>"><?php _e( 'Edit', 'themify-flow' ); ?></a>
				<?php } ?>

				</div>

				<?php if ( $theme['hasUpdate'] ) { ?>
					<div class="theme-update"><?php _e( 'Update Available' ); ?></div>
				<?php } ?>
			</div>
			<?php endforeach; ?>
				<br class="clear" />		

		</div>
		<!-- /themes -->
	</div>
	<!-- /theme-browser -->
	<div class="theme-overlay"></div>

	<p class="no-themes"><?php _e( 'No themes found. Try a different search.' ); ?></p>
</div>

<script id="tmpl-tf-theme" type="text/template">
	<# if ( data.screenshot[0] ) { #>
		<div class="theme-screenshot">
			<img src="{{ data.screenshot[0] }}" alt="" />
		</div>
	<# } else { #>
		<div class="theme-screenshot blank"></div>
	<# } #>
	<span class="more-details" id="{{ data.id }}-action"><?php _e( 'Theme Details' ); ?></span>
	<div class="theme-author"><?php printf( __( 'By %s' ), '{{{ data.author }}}' ); ?></div>

	<# if ( data.active ) { #>
		<h3 class="theme-name" id="{{ data.id }}-name">
			<?php
			/* translators: %s: theme name */
			printf( __( '<span>Active:</span> %s' ), '{{ data.name }}' );
			?>
		</h3>
	<# } else { #>
		<h3 class="theme-name" id="{{ data.id }}-name">{{{ data.name }}}</h3>
	<# } #>

	<div class="theme-actions">

	<# if ( data.active ) { #>
		<a class="button button-primary tf_lightbox_edit" href="#" data-type="theme" data-post-id="{{{ data.theme_id }}}"><?php _e( 'Edit', 'themify-flow' ); ?></a>
	<# } else { #>
		<a class="button button-secondary" href="{{{ data.actions.activate }}}"><?php _e( 'Activate' ); ?></a>
		<a class="button button-primary tf_lightbox_edit" href="#" data-type="theme" data-post-id="{{{ data.theme_id }}}"><?php _e( 'Edit', 'themify-flow' ); ?></a>
	<# } #>

	</div>

	<# if ( data.hasUpdate ) { #>
		<div class="theme-update"><?php _e( 'Update Available' ); ?></div>
	<# } #>
</script>

<script id="tmpl-tf-theme-single" type="text/template">
	<div class="theme-backdrop"></div>
	<div class="theme-wrap">
		<div class="theme-header">
			<button class="left dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show previous theme' ); ?></span></button>
			<button class="right dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show next theme' ); ?></span></button>
			<button class="close dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Close overlay' ); ?></span></button>
		</div>
		<div class="theme-about">
			<div class="theme-screenshots">
			<# if ( data.screenshot[0] ) { #>
				<div class="screenshot"><img src="{{ data.screenshot[0] }}" alt="" /></div>
			<# } else { #>
				<div class="screenshot blank"></div>
			<# } #>
			</div>

			<div class="theme-info">
				<# if ( data.active ) { #>
					<span class="current-label"><?php _e( 'Current Theme' ); ?></span>
				<# } #>
				<h3 class="theme-name">{{{ data.name }}}<span class="theme-version"><?php printf( __( 'Version: %s' ), '{{ data.version }}' ); ?></span></h3>
				<h4 class="theme-author"><?php printf( __( 'By %s' ), '{{{ data.authorAndUri }}}' ); ?></h4>

				<# if ( data.hasUpdate ) { #>
				<div class="theme-update-message">
					<h4 class="theme-update"><?php _e( 'Update Available' ); ?></h4>
					{{{ data.update }}}
				</div>
				<# } #>
				<p class="theme-description">{{{ data.description }}}</p>

				<# if ( data.parent ) { #>
					<p class="parent-theme"><?php printf( __( 'This is a child theme of %s.' ), '<strong>{{{ data.parent }}}</strong>' ); ?></p>
				<# } #>

				<# if ( data.tags ) { #>
					<p class="theme-tags"><span><?php _e( 'Tags:' ); ?></span> {{{ data.tags }}}</p>
				<# } #>
			</div>
		</div>

		<div class="theme-actions">
			<div class="active-theme">
				<a class="button button-primary tf_lightbox_edit" href="#" data-type="theme" data-post-id="{{{ data.theme_id }}}"><?php _e( 'Edit', 'themify-flow' ); ?></a>
				<# if ( data.actions.export ) { #>
					<a href="{{{ data.actions.export }}}" class="button button-secondary"><?php _e( 'Export', 'themify-flow' ); ?></a>
				<# } #>
				<a class="button button-secondary tf_lightbox_duplicate" href="#" data-type="theme" data-post-id="{{{ data.theme_id }}}"><?php _e( 'Duplicate', 'themify-flow' ); ?></a>
				<a class="button button-secondary tf_lightbox_replace" href="#" data-type="theme" data-post-id="{{{ data.theme_id }}}"><?php _e( 'Replace', 'themify-flow' ); ?></a>
			</div>
			<div class="inactive-theme">
				<# if ( data.actions.activate ) { #>
					<a href="{{{ data.actions.activate }}}" class="button button-secondary"><?php _e( 'Activate' ); ?></a>
				<# } #>
				<a class="button button-primary tf_lightbox_edit" href="#" data-type="theme" data-post-id="{{{ data.theme_id }}}"><?php _e( 'Edit', 'themify-flow' ); ?></a>
				<# if ( data.actions.export ) { #>
					<a href="{{{ data.actions.export }}}" class="button button-secondary"><?php _e( 'Export', 'themify-flow' ); ?></a>
				<# } #>
				<a class="button button-secondary tf_lightbox_duplicate" href="#" data-type="theme" data-post-id="{{{ data.theme_id }}}"><?php _e( 'Duplicate', 'themify-flow' ); ?></a>
				<a class="button button-secondary tf_lightbox_replace" href="#" data-type="theme" data-post-id="{{{ data.theme_id }}}"><?php _e( 'Replace', 'themify-flow' ); ?></a>
			</div>

			<# if ( ! data.active && data.actions['delete'] ) { #>
				<a href="{{{ data.actions['delete'] }}}" class="button button-secondary delete-theme"><?php _e( 'Delete' ); ?></a>
			<# } #>
		</div>
	</div>
</script>