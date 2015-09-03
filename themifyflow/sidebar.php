<?php
/**
 * Template for Main Sidebar.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
?>

<?php tf_hook_sidebar_before(); // hook ?>

<aside id="sidebar">

	<?php tf_hook_sidebar_start(); // hook ?>
    
	<?php dynamic_sidebar('sidebar-main'); ?>
    
	<?php tf_hook_sidebar_end(); // hook ?>

</aside>
<!-- /#sidebar -->

<?php tf_hook_sidebar_after(); // hook ?>