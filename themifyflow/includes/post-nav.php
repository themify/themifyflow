<?php 
/**
 * Post Navigation Template
 * @package themify
 * @since 1.0.0
 */

$arrow = ( isset( $show_arrow ) && 'yes' == $show_arrow ) ? '<span class="arrow"></span>' : '';
$in_same_cat = ( isset( $in_same_cat ) && '1' == $in_same_cat ) ? true : false;
$next_post_label = ( ! empty( $next_post_label ) ) ? '<span class="next_prev_post_label">' . $next_post_label . '</span>' : '';
$prev_post_label = ( ! empty( $prev_post_label ) ) ? '<span class="next_prev_post_label">' . $prev_post_label . '</span>' : '';
?>

<div class="tf_post_nav clearfix">

	<?php previous_post_link( '<span class="prev">%link</span>', $arrow . $prev_post_label . ' %title', $in_same_cat ) ?>

	<?php next_post_link( '<span class="next">%link</span>', $arrow . $next_post_label . ' %title', $in_same_cat ) ?>

</div>
<!-- /tf_post_nav -->