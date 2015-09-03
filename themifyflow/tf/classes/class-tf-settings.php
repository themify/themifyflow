<?php
/**
 * Class to create settings page, save settings and return settings.
 * 
 * @package ThemifyFlow
 * @subpackage Settings
 * @since 1.0.0
 */
class TF_Settings {

	/**
	 * Admin page slug.
	 * 
	 * @since 1.0.0
	 * @access protected
	 * @var string $slug
	 */
	static protected $slug = 'themify-flow';

	/**
	 * Returns data structure composed of tabs and its title and the fields in each tab body.
	 *
	 * @return mixed
	 */
	public static function get_sections_and_settings() {
		$sections['general'] = array(
			'title' => __( 'General', 'themify-flow' ),
			'callback' => array( 'TF_Settings', 'general_description' ),
			'fields' => array(
				array(
					'id' => 'favicon',
					'label' => __( 'Favicon', 'themify-flow'),
					'type' => 'text',
					'class' => 'large-text',
					'default' => '',
					'description' =>'',
				),
				array(
					'id' => 'header_text',
					'label' => __( 'Header Code', 'themify-flow' ),
					'type' => 'textarea',
					'class' => 'large-text',
					'options' => '15',
					'description' => __('The code will be added to the <head> tag (use it to add additional scripts such as CSS or JS).', 'themify-flow'),
					'default' => ''
				),
				array(
					'id' => 'footer_text',
					'label' => __( 'Footer Code', 'themify-flow' ),
					'type' => 'textarea',
					'class' => 'large-text',
					'options' => '15',
					'description' => __('The code will be added to the footer before the closing </body> tag (use it to add JavaScript, Google Analytics code, etc).', 'themify-flow'),
					'default' => ''
				),
				array(
					'id' => 'disable_responsive',
					'label' => __( 'Responsive Design', 'themify-flow' ),
					'type' => 'checkbox',
					'default' => '',
					'description' => __( 'Check to disable responsive design', 'themify-flow' )
				),
				array(
					'id'   => 'webfonts',
					'label'=> __( 'Google Fonts', 'themify-flow' ),
					'type' => 'google_fonts',
					'description' => __('Enter the additional character subsets you need to use from Google Fonts separated by commas. Example: latin-ext,cyrillic.', 'themify-flow'),
					'default' => ''
				)
			),
		);

		return $sections;
	}

	/**
	 * Creates a text input field
	 *
	 * @since 1.0.0
	 *
	 * @param array
	 */
	public static function text( $args ) {
		$field_id = $args['id'];
		$value = self::get( $field_id, $args['default'] );
		?>
		<input id="<?php echo esc_attr( self::$slug . $field_id ); ?>" <?php if ( isset( $args['class'] ) ) : echo 'class="' . esc_attr( $args['class'] ) . '"'; endif; ?> name="<?php echo esc_attr( self::$slug . '[' . $field_id . ']' ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" />

		<?php if ( isset( $args['description'] ) ) : ?>
			<span class='howto'><?php echo wp_kses_post( $args['description'] ); ?></span>
		<?php endif;
	}

    /**
	 * Creates a checkbox input field
	 *
	 * @since 1.0.0
	 *
	 * @param array
	 */
    public static function checkbox( $args ) {
    	$field_id = $args['id'];
    	$value = self::get( $field_id, $args['default'] );
    	?>
    	<input type="checkbox" value="on" name="<?php echo esc_attr( self::$slug.'['.$field_id.']' ); ?>" <?php  checked( isset($value) ? $value : '', 'on', true )?>/>
    	<?php if ( isset( $args['description'] ) ) : ?>
    		<?php echo wp_kses_post( $args['description'] ); ?>
    	<?php endif;
    }
        
    /**
	 * Creates a wp_editor
	 *
	 * @since 1.0.0
	 *
	 * @param array
	 */
       public static function wp_editor( $args ) {
       	$field_id = $args['id'];
       	$value = self::get( $field_id, $args['default'] );
       	wp_editor( $value, $field_id, array(
       		'textarea_name' => $field_id
       		) );
       		?>
       		<?php if ( isset( $args['description'] ) ) : ?>
       			<span class='howto'><?php echo wp_kses_post( $args['description'] ); ?></span>
       		<?php endif;
       	}

	/**
	 * Creates a textarea
	 *
	 * @since 1.0.0
	 *
	 * @param array
	 */
	public static function textarea( $args ) {
		$field_id = $args['id'];
		$value = self::get( $field_id, $args['default'] );
		?>
		<textarea id="<?php echo self::$slug . $field_id; ?>" <?php if ( isset( $args['class'] ) ) : echo 'class="' . esc_attr( $args['class'] ) . '"'; endif; ?> <?php if ( isset( $args['options'] ) ) : echo 'rows="' . esc_attr( $args['options'] ) . '"'; endif; ?> name="<?php echo esc_attr( self::$slug . '[' . $field_id . ']' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>

		<?php if ( isset( $args['description'] ) ) : ?>
			<span class='howto'><?php echo wp_kses_post( $args['description'] ); ?></span>
		<?php endif;
	}

	/**
	 * Creates a select box
	 *
	 * @since 1.0.0
	 *
	 * @param array
	 */
	public static function select( $args ) {
		$field_id = $args['id'];
		$value = self::get( $field_id, $args['default'] );
		?>
		<select id="<?php echo self::$slug . $field_id; ?>" <?php if ( isset( $args['class'] ) ) : echo 'class="' . esc_attr( $args['class'] ) . '"'; endif; ?> name="<?php echo esc_attr( self::$slug . '[' . $field_id . ']' ); ?>">
			<?php foreach( $args['options'] as $key => $_value ) : ?>
				<option value="<?php echo $key; ?>" <?php selected( $value, $key ); ?>><?php echo $_value; ?></option>
			<?php endforeach; ?>
		</select>

		<?php if ( isset( $args['description'] ) ) : ?>
			<span class='howto'><?php echo wp_kses_post( $args['description'] ); ?></span>
		<?php endif;
	}




    /**
	 *  Google Fonts
	 *
	 * @since 1.0.0
	 *
	 * @param array
	 */
    public static function google_fonts( $args ) {
    	$field_id = $args['id'];
    	$value = self::get( $field_id);
    	?>
    	<p>
    		<input id="<?php echo esc_attr( self::$slug . '-'.$field_id.'-list-recommended' ); ?>" <?php  checked( isset( $value[ 'list'] ) ? $value[ 'list'] : !isset($value) || empty($value)?'recommended':'', 'recommended', true )?> type="radio" name="<?php echo esc_attr( self::$slug . '['.$field_id.'][list]' ); ?>" value="recommended" />
    		<label for="<?php echo esc_attr( self::$slug . '-'.$field_id.'-list-recommended' ); ?>"><?php _e('Show recommended Google Fonts only', 'themify-flow')?></label>
    	</p>
    	<p>
    		<input id="<?php echo esc_attr( self::$slug . '-'.$field_id.'-list-full' ); ?>" <?php  checked( isset( $value[ 'list'] ) ? $value[ 'list'] : '', 'full', true )?> type="radio" name="<?php echo esc_attr( self::$slug . '['.$field_id.'][list]' ); ?>" value="full" />
    		<label for="<?php echo esc_attr( self::$slug . '-'.$field_id.'-list-full' ); ?>"><?php _e('Show all Google Fonts (showing all fonts will take longer to load)', 'themify-flow')?></label>
    	</p>
    	<br/>
    	<p>
    		<input id="<?php echo esc_attr( self::$slug . '-'.$field_id.'-subsets' ); ?>" <?php if(isset($value['subsets'])):?>value="<?php echo $value['subsets']?>"<?php endif;?> type="text" name="<?php echo esc_attr( self::$slug . '['.$field_id.'][subsets]' ); ?>" />
    		<label for="<?php echo esc_attr( self::$slug . '-'.$field_id.'-subsets' ); ?>"><?php _e('Character Subsets', 'themify-flow')?></label>
    	</p>
    	<?php if ( isset( $args['description'] ) ) : ?>
    		<span class='howto'><?php echo wp_kses_post( $args['description'] ); ?></span>
    	<?php endif;?>
    	<?php

    }
        
    /**
	 * Creates a multiselect box
	 *
	 * @since 1.0.0
	 *
	 * @param array
	 */
    public static function multiselect( $args ) {
    	$field_id = $args['id'];
    	$value = self::get( $field_id, $args['default'] );
    	?>
    	<select multiple="multiple" id="<?php echo self::$slug . $field_id; ?>" <?php if ( isset( $args['class'] ) ) : echo 'class="' . esc_attr( $args['class'] ) . '"'; endif; ?> name="<?php echo esc_attr( self::$slug . '[' . $field_id . ']' ); ?>">
    		<?php foreach( $args['options'] as $key => $_value ) : ?>
    			<option value="<?php echo $key; ?>" <?php selected( $value, $key ); ?>><?php echo $_value; ?></option>
    		<?php endforeach; ?>
    	</select>

    	<?php if ( isset( $args['description'] ) ) : ?>
    		<span class='howto'><?php echo wp_kses_post( $args['description'] ); ?></span>
    	<?php endif;
    }

	/**
	 * Callback for settings section description
	 *
	 * @since 1.0.0
	 */
	public static function general_description() {

	}

	/**
	 * Return setting from option saved in database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Setting to return.
	 * @param mixed $default Fallback value if setting doesn't exist.
	 *
	 * @return mixed|bool
	 */
	public static function get( $key, $default = null ) {
		static $settings;
		if ( ! isset( $settings ) ) {
			$settings = get_option( self::$slug );
		}
		if ( isset( $settings[$key] ) ) {
			return $settings[$key];
		}
		if ( isset( $default ) ) {
			return $default;
		}
		return false;
	}

}