<?php

class TF_Styling_Panel {
	
	public function basic_fields() {
		return apply_filters( 'tf_styling_panel_basic_fields', array(
			'font' => array(
				'font_family' => array(
					'type' => ''
				)
			)
		) );
	}

	public static function control_selector_render( $module ) {
		ob_start();
		self::build_selectors( $module );
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public static function build_selectors( $module ) {
		global $tf_styles;
		$tf_styling_selectors = $tf_styles->get_style( $module ); // param: module slug 
		if ( count( $tf_styling_selectors ) > 0 ): ?>
			<ul class="tf_elements_list">
				<?php foreach( $tf_styling_selectors as $key => $param ):
					$li_state_class = count( $param['children'] ) > 0 ? ' tf_list_has_child': '';
					$basic_styling = isset( $param['basic_styling'] ) ? ' data-tf-basic-styling="' . implode( ',', $param['basic_styling'] ) . '"' : '';
					$parent_attr = isset( $param['selector'] ) && ! empty( $param['selector']) 
						? ' data-tf-style-selector="'. esc_attr( $param['selector'] ).'" data-tf-style-selector-key="'.$key.'"' 
						: '';
					$chain = isset( $param['chain_with_context'] ) && $param['chain_with_context'] ? ' data-tf-chain-with-context="chain"' : '';
				?>
					<li class="<?php echo esc_attr( $li_state_class ); ?>">
						<span class="tf_element_list_title"<?php echo $parent_attr . $chain; ?><?php echo $basic_styling; ?>><?php echo $param['label'] ?></span>
						
						<?php if( count( $param['children'] ) > 0 ): ?>
						<ul>
							<?php foreach( $param['children'] as $child_key => $child_param ): ?>
								<li>
									<?php
									$parent_child_attr = isset( $child_param['selector'] ) && ! empty( $child_param['selector']) 
									? ' data-tf-style-selector="'. esc_attr( $child_param['selector'] ).'" data-tf-style-selector-key="'.$child_key.'"' 
									: '';
									$basic_styling = isset( $child_param['basic_styling'] ) ? ' data-tf-basic-styling="' . implode( ',', $child_param['basic_styling'] ) . '"' : '';
									$chain = isset( $child_param['chain_with_context'] ) && $child_param['chain_with_context'] ? ' data-tf-chain-with-context="chain"' : '';
									?>
									<span class="tf_element_list_title"<?php echo $parent_child_attr . $chain; ?><?php echo $basic_styling; ?>><?php echo $child_param['label']; ?></span>
								</li>
							<?php endforeach;?>
						</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; // count $tf_styling_selectors
	}
}