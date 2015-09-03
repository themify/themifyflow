<?php global $tf_styling_control, $TF_Layout; ?>
<script type="text/html" id="tmpl-tf_styling_panel">

	<!--***********************************************-->
	<!-- STYLING PANEL -->
	<!--***********************************************-->

	<div class="tf_target_elements">
		<?php echo tf_loader_span('small'); ?>
	</div><!-- /tf_target_elements -->
	
	<div class="tf_styling_tabs">
		<ul>
			<li class="tf_styling_basic tf_active"><?php _e('Basic', 'themify-flow') ?></li>
			<li class="tf_styling_all"><?php _e('Advanced', 'themify-flow') ?></li>
		</ul>
	</div>
	<!-- /tf_styling_tabs -->

	<div class="tf_css_properties_wrap">	
		<div class="tf_css_properties">
			<?php echo $tf_styling_control->render_sections(); ?>
		</div>
		<!-- /tf_css_properties -->
	</div>	

	<div class="tf_styling_panel_bottom">
		<a href="#" class="tf_btn tf_btn_clear"><?php _e('Clear all', 'themify-flow') ?></a>
		<a href="#" class="tf_btn tf_btn_cancel"><?php _e('Cancel', 'themify-flow') ?></a>
		<a href="#" class="tf_btn tf_btn_save"><?php _e('Done', 'themify-flow') ?></a>
	</div>
	<!-- /tf_styling_panel_bottom -->
</script>

<script type="text/html" id="tmpl-tf_styling_font_control">

	<!--**************-->
	<!-- FONT CONTROL -->
	<!--**************-->
	
	<div class="tf_property_row">
		<?php
		$font_family = '';
		$font_variant = '';
		?>
		<select name="family" class="tf_font_family">
			<option value=""></option>
			<optgroup label="<?php _e( 'Web Safe Fonts', 'themify-flow' ); ?>">
				<?php
				$fonts = TF_Model::get_web_safe_font_list();
				unset( $fonts[0] );
				unset( $fonts[1] );
				foreach ( $fonts as $font ) {
					$value = json_encode( array(
						'name' 		=> esc_attr( $font['value'] ),
						'variant' 	=> 'regular,bold,italic,bold italic',
						'fonttype'	=> 'websafe',
					));

					?>
					<option value='<?php echo $value; ?>' <?php selected( $font_family, $font['value'] ); ?> data-name="<?php echo esc_attr( $font['value'] ); ?>"><?php echo esc_html( $font['name'] ) ?></option>
					<?php
				}
				?>
			</optgroup>
			<optgroup label="<?php _e( 'Google Fonts', 'themify-flow' ); ?>">
				<?php
				$fonts = TF_Model::get_google_font_lists();
				$option = '';
				foreach ( $fonts as $font ) {
					$family_name = isset( $font['family'] ) ? esc_attr($font['family']) : '';
					$value = json_encode( array(
						'name' 		=> isset( $font['family'] ) ? esc_attr($font['family']) : '',
						'variant' 	=> isset( $font['variant'] ) ? $font['variant'] : '',
						'subsets' 	=> isset( $font['subsets'] ) ? $font['subsets'] : '',
						'fonttype' 	=> 'google',
					));
					?>
					<option class="google_font" value='<?php echo $value; ?>' <?php selected( $font_family, $font['family'] ); ?> data-name="<?php echo esc_attr( $family_name ); ?>"><?php echo esc_html( $font['family'] ) ?></option>
					<?php
				}
				echo $option;
				?>
			</optgroup>
		</select>

		<!-- FONT VARIANT -->
		<select class="tf_font_variant"></select>
	</div>
	<!-- /tf_property_row -->

	<div class="tf_property_row">
		<input type="text" class="tf_unit_size font_size_num"> 
		<select class="font_size_unit">
			<option>px</option>
			<option>%</option>
			<option>em</option>
		</select>
		<label><?php _e('Font Size', 'themify-flow') ?></label>
	</div>
	<!-- /tf_property_row -->

	<div class="tf_property_row">
		<input type="text" class="tf_unit_size font_line_num">
			<select class="font_line_unit">
				<option>px</option>
				<option>%</option>
				<option>em</option>
			</select>
		<label><?php _e('Line Height', 'themify-flow') ?></label>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_font_style tf_property_row">
		<span class="tf_property_btn tf_italic" data-style="italic">i</span>
		<span class="tf_property_btn tf_bold" data-style="bold">B</span>
		<span class="tf_property_btn tf_underline" data-style="underline">U</span>
		<span class="tf_property_btn tf_linethrough" data-style="linethrough">S</span>
		<span class="tf_property_btn tf_nostyle" data-style="nostyle">&times;</span>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_text_transform tf_property_row">
		<span class="tf_property_btn tf_uppercase" data-texttransform="uppercase">AA</span>
		<span class="tf_property_btn tf_lowercase" data-texttransform="lowercase">ab</span>
		<span class="tf_property_btn tf_capitalize" data-texttransform="capitalize">Ab</span>
		<span class="tf_property_btn tf_nostyle" data-texttransform="notexttransform">&times;</span>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_font_align tf_property_row">
		<span class="tf_property_btn tf_align tf_alignleft" data-align="left"></span>
		<span class="tf_property_btn tf_align tf_aligncenter" data-align="center"></span>
		<span class="tf_property_btn tf_align tf_alignright" data-align="right"></span>
		<span class="tf_property_btn tf_align tf_alignjustify" data-align="justify"></span>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_property_row">
		<div class="tf_custom_color_wrap">
			<input type="text" class="tf_color_pick_value color-select" data-opacity="">
			<a class="remove-color ti-close" href="#"></a>
			<label class="tf_color_picker_label"><?php _e( 'Color', 'themify-flow' ); ?></label>
		</div>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_border_control">
	
	<!--************************************************-->
	<!-- BORDER CONTROL -->
	<!--************************************************-->

	<?php
	// Same for all
	$same = isset( $values->same ) ? $values->same : 'same';

	// Sides
	$sides = array(
		'top'    => __( 'Border Top', 'themify-flow' ),
		'right'  => __( 'Border Right', 'themify-flow' ),
		'bottom' => __( 'Border Bottom', 'themify-flow' ),
		'left'   => __( 'Border Left', 'themify-flow' ),
	);

	// Style
	$styles = array(
		'' => '',
		'solid'  => __( 'Solid', 'themify-flow' ),
		'dotted' => __( 'Dotted', 'themify-flow' ),
		'dashed' => __( 'Dashed', 'themify-flow' ),
		'double' => __( 'Double', 'themify-flow' ),
		'none' => __( 'None', 'themify-flow' ),
	);
	?>

	<?php
	$first = true;

	foreach ( $sides as $side => $side_label ) : ?>
		<div class="tf_property_row <?php echo $first ? 'useforall' : 'component'; ?>">
			<div class="tf_border_property_row">
				<div class="wide-label tf_border_position_name <?php echo $first ? 'same-label' : ''; ?>" <?php echo $first ? 'data-same="' . __( 'Border', 'themify-flow' ) . '" data-notsame="' . $side_label . '"' : ''; ?>><?php echo
					$side_label;
					?></div>

				<!-- Border Color -->
				<div class="tf_custom_color_wrap color-picker">
					<?php
					// Check color
					if ( 'same' == $same ) {
						$color = isset( $values->color ) ? $values->color : '';
						$opacity = isset( $values->opacity ) ? $values->opacity : '';
					} else {
						$color = isset( $values->{$side} ) && isset( $values->{$side}->color ) ? $values->{$side}->color : '';
						$opacity = isset( $values->{$side} ) && isset( $values->{$side}->opacity ) ? $values->{$side}->opacity : '';
					}
					?>
					<input type="text" class="tf_color_pick_value border-color-select" data-side="<?php echo $side; ?>" value="<?php echo $color; ?>" data-opacity="<?php echo $opacity; ?>"/>
					<a class="remove-color ti-close" href="#" <?php echo ( '' != $color || '' != $opacity ) ? 'style="display:inline"' : ''; ?> data-side="<?php echo $side; ?>"></a>
				</div>

				<!-- Border Style -->
				<select class="border-style" data-side="<?php echo $side; ?>">
					<?php foreach ( $styles as $style => $label ) : ?>
						<option value="<?php echo $style; ?>"><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>

				<!-- Border Width -->
				<?php
				// Check width
				if ( 'same' == $same ) {
					$width = isset( $values->style ) ? $values->width : '';
				} else {
					$width = isset( $values->{$side} ) && isset( $values->{$side}->width ) ? $values->{$side}->width : '';
				}
				?>
				<input type="text" class="dimension-width border-width tf_border_size" data-side="<?php echo $side; ?>" value="<?php echo $width; ?>" />
				<label class="dimension-unit-label"><?php _e( 'px', 'themify-flow' ); ?></label>
			</div><!-- /tf_border_property_row -->
		</div>
	<?php
	$first = false;
	endforeach; ?>

	<div class="tf_property_row collapse-same">
		<div class="tf_border_property_row">
			<div class="tf_checkbox_wrap">
				<!-- Apply the same settings to all sides -->
				<?php $same_id = 'tf_border_properties_same'; ?>
				<input id="<?php echo $same_id; ?>" type="checkbox" class="same" value="same"/>
				<label for="<?php echo $same_id; ?>" class="tf_apply_all">
					<?php _e( 'Apply to all borders.', 'themify-flow' ); ?>
				</label>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_background_control">

	<!--****************************************************-->
	<!-- BACKGROUND CONTROL -->
	<!--****************************************************-->

	<div class="tf_property_row">
		<div class="tf_background_wrap">
			<div class="tf_background_img_wrap">
				<a href="#" class="tf_background_img_plus open-media"></a>
				<# if ( data.src ) { #>
					<a href="#" class="remove-image ti-close"></a>
					<img src="{{data.src}}" />					
				<# } #>
			</div>
			<label class="tf_img_label">{{data.labels.image}}</label>
			<div class="tf_background_style">
				<select class="image-style">
					<option value="fullcover" selected="selected">{{data.labels.fullcover}}</option>
					<option value="repeat">{{data.labels.repeatAll}}</option>
					<option value="repeat-x">{{data.labels.repeatHorizontal}}</option>
					<option value="repeat-y">{{data.labels.repeatVertical}}</option>
					<option value="no-repeat">{{data.labels.noRepeat}}</option>
				</select>
			</div>
			<div class="tf_background_style">
				<select class="image-position-style">
					<option value="" selected="selected"></option>
					<option value="left top">{{data.labels.leftTop}}</option>
					<option value="left center">{{data.labels.leftCenter}}</option>
					<option value="left bottom">{{data.labels.leftBottom}}</option>
					<option value="right top">{{data.labels.rightTop}}</option>
					<option value="right center">{{data.labels.rightCenter}}</option>
					<option value="right bottom">{{data.labels.rightBottom}}</option>
					<option value="center top">{{data.labels.centerTop}}</option>
					<option value="center center">{{data.labels.centerCenter}}</option>
					<option value="center bottom">{{data.labels.centerBottom}}</option>
				</select>
			</div>
		</div>
	</div>
	
	<div class="tf_property_row">
		<div class="tf_background_property_row">
			<div class="tf_checkbox_wrap no-image">
				<input type="checkbox" class="tf_checkbox disable-control">
				<label class="tf_apply_all">{{data.labels.noBackgroundImage}}</label>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->

	<div class="tf_property_row">
		<div class="tf_custom_color_wrap">
			<input type="text" class="tf_color_pick_value color-select">
			<label for="body_font_color_ctrl_color_picker" class="tf_color_picker_label"> {{data.labels.backgroundColor}}</label>
		</div>
	</div>
	<!-- /tf_property_row -->

	<div class="tf_property_row">
		<div class="tf_background_property_row">
			<div class="tf_checkbox_wrap">
				<input type="checkbox" class="tf_checkbox color-transparent" value="transparent">
				<label class="tf_apply_all">{{data.labels.transparent}}</label>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_padding_control">
	
	<!--*************************************************-->
	<!-- PADDING CONTROL -->
	<!--*************************************************-->

	<div class="tf_property_row useforall">
		<div class="tf_padding_property_row">
			<input type="text" class="tf_unit_size dimension-width" data-side="top">
			<select class="dimension-unit padding-unit" data-side="top">
				<option>px</option>
				<option>%</option>
				<option>em</option>
			</select>
			<label class="dimension-row-label same-label" data-same="{{data.labels.padding}}" data-notsame="{{data.labels.paddingTop}}">{{data.labels.paddingTop}}</label>
		</div>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_property_row component">
		<div class="tf_padding_property_row">
			<input type="text" class="tf_unit_size dimension-width" data-side="right">
				<select class="dimension-unit padding-unit" data-side="right">
					<option>px</option>
					<option>%</option>
					<option>em</option>
				</select>
			<label class="dimension-row-label">{{data.labels.paddingRight}}</label>
		</div>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_property_row component">
		<div class="tf_padding_property_row">
			<input type="text" class="tf_unit_size dimension-width" data-side="bottom">
			<select class="dimension-unit padding-unit" data-side="bottom">
				<option>px</option>
				<option>%</option>
				<option>em</option>
			</select>
			<label class="dimension-row-label">{{data.labels.paddingBottom}}</label>
		</div>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_property_row component">
		<div class="tf_padding_property_row">
			<input type="text" class="tf_unit_size dimension-width" data-side="left">
			<select class="dimension-unit padding-unit" data-side="left">
				<option>px</option>
				<option>%</option>
				<option>em</option>
			</select>
			<label class="dimension-row-label">{{data.labels.paddingLeft}}</label>
		</div>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_property_row">
		<div class="tf_padding_property_row">
			<div class="tf_checkbox_wrap">
				<input type="checkbox" class="tf_checkbox same">
				<label class="tf_apply_all">{{data.labels.applyToAll}}</label>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_margin_control">
	
	<!--************************************************-->
	<!-- MARGIN CONTROL -->
	<!--************************************************-->

	<div class="tf_property_row useforall">
		<div class="tf_property_col_left hide-x">
			<div class="tf_margin_property_row">
				<input type="text" class="tf_unit_size dimension-width" data-side="top">
				<select class="dimension-unit margin-unit" data-side="top">
					<option>px</option>
					<option>%</option>
					<option>em</option>
				</select>
				<label class="dimension-row-label same-label" data-same="{{data.labels.margin}}" data-notsame="{{data.labels.marginTop}}">{{data.labels.marginTop}}</label>
			</div>
		</div>
		<div class="tf_property_col_right">
			<div class="tf_checkbox_wrap">
				<input type="checkbox" class="tf_checkbox auto-prop-multi" data-side="top">
				<label class="tf_auto">{{data.labels.auto}}</label>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_property_row component">
		<div class="tf_property_col_left hide-x">
			<div class="tf_margin_property_row">
				<input type="text" class="tf_unit_size dimension-width" data-side="right">
				<select class="dimension-unit margin-unit" data-side="right">
					<option>px</option>
					<option>%</option>
					<option>em</option>
				</select>
				<label class="dimension-row-label">{{data.labels.marginRight}}</label>
			</div>
		</div>
		<div class="tf_property_col_right">
			<div class="tf_checkbox_wrap">
				<input type="checkbox" class="tf_checkbox auto-prop-multi" data-side="right">
				<label class="tf_auto">{{data.labels.auto}}</label>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_property_row component">
		<div class="tf_property_col_left hide-x">
			<div class="tf_margin_property_row">
				<input type="text" class="tf_unit_size dimension-width" data-side="bottom">
				<select class="dimension-unit margin-unit" data-side="bottom">
					<option>px</option>
					<option>%</option>
					<option>em</option>
				</select>
				<label class="dimension-row-label">{{data.labels.marginBottom}}</label>
			</div>
		</div>
		<div class="tf_property_col_right">
			<div class="tf_checkbox_wrap">
				<input type="checkbox" class="tf_checkbox auto-prop-multi" data-side="bottom">
				<label class="tf_auto">{{data.labels.auto}}</label>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_property_row component">
		<div class="tf_property_col_left hide-x">
			<div class="tf_margin_property_row">
				<input type="text" class="tf_unit_size dimension-width" data-side="left">
				<select class="dimension-unit margin-unit" data-side="left">
					<option>px</option>
					<option>%</option>
					<option>em</option>
				</select>
				<label class="dimension-row-label">{{data.labels.marginLeft}}</label>
			</div>
		</div>
		<div class="tf_property_col_right">
			<div class="tf_checkbox_wrap">
				<input type="checkbox" class="tf_checkbox auto-prop-multi" data-side="left">
				<label class="tf_auto">{{data.labels.auto}}</label>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_property_row">
		<div class="tf_margin_property_row">
			<div class="tf_checkbox_wrap">
				<input type="checkbox" class="tf_checkbox same">
				<label class="tf_apply_all">{{data.labels.applyToAll}}</label>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_width_control">
	
	<!--*************************************************-->
	<!-- WIDTH CONTROL -->
	<!--*************************************************-->

	<div class="tf_property_row">
		<div class="tf_margin_property_row">
			<div class="tf_property_col_left hide-x">
				<input type="text" class="tf_unit_size dimension-width-single">
				<select class="dimension-unit-single">
					<option>px</option>
					<option>%</option>
					<option>em</option>
				</select>
				<label>{{data.labels.width}}</label>
			</div>
			<div class="tf_property_col_right">
				<div class="tf_checkbox_wrap">
					<input type="checkbox" class="tf_checkbox auto-prop">
					<label class="tf_auto">{{data.labels.auto}}</label>
				</div>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_height_control">
	
	<!--************************************************-->
	<!-- HEIGHT CONTROL -->
	<!--************************************************-->

	<div class="tf_property_row">
		<div class="tf_margin_property_row">
			<div class="tf_property_col_left hide-x">
				<input type="text" class="tf_unit_size dimension-width-single">
				<select class="dimension-unit-single">
					<option>px</option>
					<option>%</option>
					<option>em</option>
				</select>
				<label>{{data.labels.height}}</label>
			</div>
			<div class="tf_property_col_right">
				<div class="tf_checkbox_wrap">
					<input type="checkbox" class="tf_checkbox auto-prop">
					<label class="tf_auto">{{data.labels.auto}}</label>
				</div>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_min-width_control">
	
	<!--************************************************-->
	<!-- MINIMUM WIDTH CONTROL -->
	<!--************************************************-->

	<div class="tf_property_row">
		<input type="text" class="tf_unit_size dimension-width-single">
		<select class="dimension-unit-single">
			<option>px</option>
			<option>%</option>
			<option>em</option>
		</select>
		<label>{{data.labels.minWidth}}</label>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_max-width_control">
	
	<!--************************************************-->
	<!-- MAXIMUM WIDTH CONTROL -->
	<!--************************************************-->

	<div class="tf_property_row">
		<input type="text" class="tf_unit_size dimension-width-single">
		<select class="dimension-unit-single">
			<option>px</option>
			<option>%</option>
			<option>em</option>
		</select>
		<label>{{data.labels.maxWidth}}</label>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_min-height_control">
	
	<!--************************************************-->
	<!-- MINIMUM HEIGHT CONTROL -->
	<!--************************************************-->

	<div class="tf_property_row">
		<input type="text" class="tf_unit_size dimension-width-single">
		<select class="dimension-unit-single">
			<option>px</option>
			<option>%</option>
			<option>em</option>
		</select>
		<label>{{data.labels.minHeight}}</label>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_position_control">
	
	<!--**************************************************-->
	<!-- POSITION CONTROL -->
	<!--***************************************************-->

	<div class="tf_property_row">
		<div class="tf_margin_property_row">
			<select class="position">
				<option value=""></option>
				<option value="absolute">{{data.labels.absolute}}</option>
				<option value="relative">{{data.labels.relative}}</option>
				<option value="fixed">{{data.labels.fixed}}</option>
				<option value="static">{{data.labels.static}}</option>
			</select>
			<label>{{data.labels.position}}</label>
		</div>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_property_row component">
		<div class="tf_margin_property_row">
			<div class="tf_property_col_left hide-x">
				<input type="text" class="tf_unit_size dimension-width" data-side="top">
				<select class="dimension-unit" data-side="top">
					<option>px</option>
					<option>%</option>
					<option>em</option>
				</select>
				<label>{{data.labels.top}}</label>
			</div>
			<div class="tf_property_col_right">
				<div class="tf_checkbox_wrap">
					<input type="checkbox" class="tf_checkbox auto-prop-multi" data-side="top">
					<label class="tf_auto">{{data.labels.auto}}</label>
				</div>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->
	
	<div class="tf_property_row component">
		<div class="tf_margin_property_row">
			<div class="tf_property_col_left hide-x">
				<input type="text" class="tf_unit_size dimension-width" data-side="right">
				<select class="dimension-unit" data-side="right">
					<option>px</option>
					<option>%</option>
					<option>em</option>
				</select>
				<label>{{data.labels.right}}</label>
			</div>
			<div class="tf_property_col_right">
				<div class="tf_checkbox_wrap">
					<input type="checkbox" class="tf_checkbox auto-prop-multi" data-side="right">
					<label class="tf_auto">{{data.labels.auto}}</label>
				</div>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->

	<div class="tf_property_row component">
		<div class="tf_margin_property_row">
			<div class="tf_property_col_left hide-x">
				<input type="text" class="tf_unit_size dimension-width" data-side="bottom">
				<select class="dimension-unit" data-side="bottom">
					<option>px</option>
					<option>%</option>
					<option>em</option>
				</select>
				<label>{{data.labels.bottom}}</label>
			</div>
			<div class="tf_property_col_right">
				<div class="tf_checkbox_wrap">
					<input type="checkbox" class="tf_checkbox auto-prop-multi" data-side="bottom">
					<label class="tf_auto">{{data.labels.auto}}</label>
				</div>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->

	<div class="tf_property_row component">
		<div class="tf_margin_property_row">
			<div class="tf_property_col_left hide-x">
				<input type="text" class="tf_unit_size dimension-width" data-side="left">
				<select class="dimension-unit" data-side="left">
					<option>px</option>
					<option>%</option>
					<option>em</option>
				</select>
				<label>{{data.labels.left}}</label>
			</div>
			<div class="tf_property_col_right">
				<div class="tf_checkbox_wrap">
					<input type="checkbox" class="tf_checkbox auto-prop-multi" data-side="left">
					<label class="tf_auto">{{data.labels.auto}}</label>
				</div>
			</div>
		</div>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_float_control">
	
	<!--**************************************************-->
	<!-- FLOAT CONTROL -->
	<!--***************************************************-->

	<div class="tf_property_row">
		<select class="float">
			<option value=""></option>
			<option value="left">{{data.labels.left}}</option>
			<option value="right">{{data.labels.right}}</option>
			<option value="none">{{data.labels.none}}</option>
		</select>
		<label>{{data.labels.float}}</label>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_opacity_control">
	
	<!--************************************************-->
	<!-- OPACITY CONTROL -->
	<!--************************************************-->

	<div class="tf_property_row">
		<input type="range" class="tf_unit_size opacity" min="0" max="100" />
		<label class="dimension-unit-label"></label>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_z-index_control">
	
	<!--************************************************-->
	<!-- Z-INDEX CONTROL -->
	<!--************************************************-->

	<div class="tf_property_row">
		<input type="number" class="tf_unit_size z-index">
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<script type="text/html" id="tmpl-tf_styling_customcss_control">
	
	<!--**************************************************-->
	<!-- CUSTOM CSS CONTROL -->
	<!--***************************************************-->

	<div class="tf_property_row">
		<textarea class="customcss"></textarea>
	</div>
	<!-- /tf_property_row -->

	<input type="hidden" class="tf_styling_panel_value_field" data-styling-setting-link="{{data.name}}" value="">

</script>

<?php
/**
 * Load bootstrapping data styling
 */
?>
<script type="text/javascript">
	var _tdBootstrapStyles = <?php echo json_encode( $TF_Layout->get_styles_model() ); ?>;
	var _tdBootstrapGlobalStyles = <?php echo json_encode( $TF_Layout->get_styles_model( array(
		'include_global_style' => true,
		'include_module_style' => false) ) ); ?>;
</script>