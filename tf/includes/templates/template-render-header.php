<?php
/**
 * Template for template view.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
global $TF_Layout;
?>
<!doctype html>
<html <?php echo tf_get_html_schema(); ?> <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">

	<!-- wp_header -->
	<?php
	/**
	 * @hooked Themify_Flow::title_tag_fallback (if WP version < 4.1) - 10
	 */
	wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	
	<?php tf_hook_body_start(); // hook ?>
	
	<div id="pagewrap" class="hfeed site">
		
		<?php if ( 'default' == $TF_Layout->header ):  ?>
			<div id="headerwrap">
		    
				<?php tf_hook_header_before(); // hook ?>
				
				<header id="header" class="tf_header">
		        
		        <?php tf_hook_header_start(); // hook ?>
					
				<?php
				if ( empty( $TF_Layout->region_header ) ) {
					do_action( 'tf_template_empty_region_header' );
				} else {
					echo $TF_Layout->render( $TF_Layout->region_header );
				}
				?>

				<?php tf_hook_header_end(); // hook ?>
				
				</header>
				<!-- /#header -->
		        
		        <?php tf_hook_header_after(); // hook ?>
						
			</div>
			<!-- /#headerwrap -->
		<?php endif; ?>
		
		<div id="middlewrap"> 
			
			<div id="middle" class="pagewidth">
	
			<?php tf_hook_layout_before(); //hook ?>