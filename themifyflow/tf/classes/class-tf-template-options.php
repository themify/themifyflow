<?php
/**
 * Class to allow users to customize per post/page basis templates
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */


class TF_Template_Options {
	
        const PREFIX = 'tf_to';
        private static $instance = null;
        protected static $post_types = array('post','page','product');//limit meta box to certain post types
        
        /**
	 * Constructor.
	 */
	public function __construct() {         
           if(is_admin()){
                add_action( 'add_meta_boxes', array($this, 'tf_action_metaboxes' ));
                add_action( 'save_post', array($this,'tf_action_metaboxes_save'));
            }
            else{
                add_action('tf_template_render_header',array($this,'tf_template_render'),9);
            }
	}
        
        /**
	 * Creates or returns an instance of this class.
	 *
	 * @return	A single instance of this class.
	 */
	public static function get_instance() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}
        
       /**
        * Layout handler
        */
        public function tf_template_render(){ 
         
          if(is_singular(self::$post_types)){
            global $TF_Layout,$TF,$post;
            $template_id = get_post_meta( $post->ID, self::PREFIX.'_template', true );
            if($template_id){
                $active_theme = get_post_meta($template_id,'associated_theme',TRUE);
                if($active_theme!=$TF->active_theme->slug){
                    return ;
                }
                $template = get_post($template_id);
                if(!$template){
                    return;
                }
                
                $TF_Layout->setup_layout($template);
                $header  = get_post_meta( $post->ID, self::PREFIX.'_header', true );
                if(!$header){
                    $header = 'default';
                }
                $sidebar = get_post_meta( $post->ID, self::PREFIX.'_sidebar', true );
                if(!$sidebar){
                    $sidebar = 'default';
                }
                $footer  = get_post_meta( $post->ID, self::PREFIX.'_footer', true );
                if(!$footer){
                    $footer = 'default';
                }
               
                if($header!='default'){
                    if($header=='no'){
                        $TF_Layout->header = false;
                    }
                    else{
                        $header_template  = get_post_meta( $post->ID, self::PREFIX.'_header_template', true );
                        if($header_template){
                            $TF_Layout->region_header = '[tf_template_part slug='.$header_template.']';
                        }
                    }
                }
                if($sidebar!='default'){
                    if($sidebar=='sidebar_none'){
                        $TF_Layout->sidebar = 'sidebar_none';
                    }
                    else{
                        $sidebar_template  = get_post_meta( $post->ID, self::PREFIX.'_sidebar_template', true );
                       if($sidebar_template){
                            $TF_Layout->region_sidebar = '[tf_template_part slug='.$sidebar_template.']';
                        }
                    }
                }
                if($footer!='default'){
                    if($footer=='no'){
                         $TF_Layout->footer = false;
                    }
                    else{
                        $footer_template  = get_post_meta( $post->ID, self::PREFIX.'_footer_template', true );
                        if($footer_template){
                            $TF_Layout->region_footer = '[tf_template_part slug='.$footer_template.']';
                        }
                    }
                }
            }
          }
        }
       
      
      
        
        /**
        * Adds a box to the main column on the Post and Page edit screens.
        *
        * @param string post type.
        */
        public function  tf_action_metaboxes($post_type){
            
            if ( in_array( $post_type, self::$post_types)) {
                 add_meta_box(
                            self::PREFIX.'_metaboxes',
                             __('Flow Template Options', 'themify-flow'),
                             array($this,'tf_render_metabox'), 
                            $post_type
                );
            }
        }

        /**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function tf_render_metabox($post) {
            global $TF;
            $templates = get_posts(array(
                                        'post_type'      =>'tf_template',
                                        'post_status'    =>'publish',
                                        'meta_query'=>array(
                                                            'relation' => 'AND',
                                                            array(
                                                                    'key' => 'tf_template_type',
                                                                    'value' => $post->post_type=='post'?'single':'page',
                                                                ),
                                                            array(
                                                                    'key' => 'associated_theme',
                                                                    'value' =>  $TF->active_theme->slug
                                                                )
                                                        ),
                                            'order'          =>'ASC',
                                            'orderby'        =>'title',
                                            'posts_per_page' => -1
                                            ));
 
                $template_parts = TF_Model::get_posts( 'tf_template_part' );
                wp_nonce_field( self::PREFIX.'_metabox_nonce', self::PREFIX.'_metabox_nonce' );
                $template_id = get_post_meta( $post->ID, self::PREFIX.'_template', true );
                $find = false;
                foreach ( $templates as $t ){
                    if($template_id == $t->ID){
                        $find = true;
                        break;
                    }
                }
                if($find){
                    $header  = get_post_meta( $post->ID, self::PREFIX.'_header', true );
                    $header  =  $header?esc_attr($header):'default';
                    $header_template = $header=='custom'?get_post_meta( $post->ID, self::PREFIX.'_header_template', true ):false;
                    $sidebar = get_post_meta( $post->ID, self::PREFIX.'_sidebar', true );
                    $sidebar =  $sidebar?esc_attr($sidebar):'default';
                    $sidebar_template = $sidebar=='custom'?get_post_meta( $post->ID, self::PREFIX.'_sidebar_template', true ):false;
                    $footer  = get_post_meta( $post->ID, self::PREFIX.'_footer', true );
                    $footer  =  $footer?esc_attr($footer):'default';
                    $footer_template = $footer=='custom'?get_post_meta( $post->ID, self::PREFIX.'_footer_template', true ):false;
                }
                else{
                    $header_template = $sidebar_template = $footer_template = false;
                    $header = $sidebar = $footer = 'default';
                }
            ?>
            <div class="tf_interface">
                <div class="tf_lightbox_row">
                    <div class="tf_lightbox_label">
                        <label for="<?php echo self::PREFIX?>_template"><?php _e('Template','themify-flow')?></label>
                    </div>
                    <div class="tf_lightbox_input">
                        <div class="tf_custom_select">
                            <select name="<?php echo self::PREFIX?>[template]" id="<?php echo self::PREFIX?>_template">
                                <option value=""><?php _e('Select Template', 'themify-flow')?></option>
                                <?php foreach ( $templates as $t ) :?>
                                    <option <?php if($template_id == $t->ID):?>selected="selected"<?php endif;?> value="<?php echo $t->ID;?>"><?php echo $t->post_title;?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div id="tf_lightbox_radios">
                    <div class="tf_lightbox_row">
                        <div class="tf_lightbox_label">
                            <label for="<?php echo self::PREFIX?>_header_default"><?php _e('Header','themify-flow')?></label>
                        </div>
                        <div class="tf_lightbox_input">
                            <input <?php  checked($header, 'default', true )?> type="radio" name="<?php echo self::PREFIX?>[header]" id="<?php echo self::PREFIX?>_header_default" value="default" />
                            <label for="<?php echo self::PREFIX?>_header_default"><?php _e('Default', 'themify-flow')?></label>
                            <input <?php  checked($header, 'no', true )?> type="radio" name="<?php echo self::PREFIX?>[header]" id="<?php echo self::PREFIX?>_header_no" value="no" />
                            <label for="<?php echo self::PREFIX?>_header_no"><?php _e('No', 'themify-flow')?></label>
                            <?php if(!empty($template_parts)):?>
                                <input <?php  checked($header, 'custom', true )?> type="radio" name="<?php echo self::PREFIX?>[header]" id="<?php echo self::PREFIX?>_header_custom" value="custom" />
                                <label for="<?php echo self::PREFIX?>_header_custom"><?php _e('Custom', 'themify-flow')?></label>
                                <div class="tf_custom_select">
                                    <select name="<?php echo self::PREFIX?>[header_template]">
                                        <?php foreach($template_parts as $part):?>
                                            <option <?php selected( $header_template, $part->post_name, true )?> value="<?php echo esc_attr( $part->post_name )?>"><?php echo esc_html( $part->post_title );?></option>
                                        <?php endforeach;?>
                                    </select>
                                </div>
                            <?php endif;?>
                        </div>
                    </div>
                    <div class="tf_lightbox_row">
                        <div class="tf_lightbox_label">
                            <label for="<?php echo self::PREFIX?>_sidebar_default"><?php _e('Sidebar','themify-flow')?></label>
                        </div>
                        <div class="tf_lightbox_input">
                            <input <?php  checked($sidebar, 'default', true )?> type="radio" name="<?php echo self::PREFIX?>[sidebar]" id="<?php echo self::PREFIX?>_sidebar_default" value="default" />
                            <label for="<?php echo self::PREFIX?>_sidebar_default"><?php _e('Default', 'themify-flow')?></label>
                            <input <?php  checked($sidebar, 'sidebar_none', true )?> type="radio" name="<?php echo self::PREFIX?>[sidebar]" id="<?php echo self::PREFIX?>_sidebar_no" value="sidebar_none" />
                            <label for="<?php echo self::PREFIX?>_sidebar_no"><?php _e('No', 'themify-flow')?></label>
                            <?php if(!empty($template_parts)):?>
                                <input <?php  checked($sidebar, 'custom', true )?> type="radio" name="<?php echo self::PREFIX?>[sidebar]" id="<?php echo self::PREFIX?>_sidebar_custom" value="custom" />
                                <label for="<?php echo self::PREFIX?>_sidebar_custom"><?php _e('Custom', 'themify-flow')?></label>
                                <div class="tf_custom_select">
                                    <select name="<?php echo self::PREFIX?>[sidebar_template]">
                                        <?php foreach($template_parts as $part):?>
                                            <option <?php selected( $sidebar_template, $part->post_name, true )?> value="<?php echo esc_attr( $part->post_name )?>"><?php echo esc_html( $part->post_title );?></option>
                                        <?php endforeach;?>
                                    </select>
                                </div>      
                            <?php endif;?>
                        </div>
                    </div>
                    <div class="tf_lightbox_row">
                        <div class="tf_lightbox_label">
                            <label for="<?php echo self::PREFIX?>_footer_default"><?php _e('Footer','themify-flow')?></label>
                        </div>
                        <div class="tf_lightbox_input">
                            <input <?php  checked($footer, 'default', true )?> type="radio" name="<?php echo self::PREFIX?>[footer]" id="<?php echo self::PREFIX?>_footer_default" value="default" />
                            <label for="<?php echo self::PREFIX?>_footer_default"><?php _e('Default', 'themify-flow')?></label>
                            <input <?php  checked($footer, 'no', true )?> type="radio" name="<?php echo self::PREFIX?>[footer]" id="<?php echo self::PREFIX?>_footer_no" value="no" />
                            <label for="<?php echo self::PREFIX?>_footer_no"><?php _e('No', 'themify-flow')?></label>
                            <?php if(!empty($template_parts)):?>
                                <input <?php  checked($footer, 'custom', true )?> type="radio" name="<?php echo self::PREFIX?>[footer]" id="<?php echo self::PREFIX?>_footer_custom" value="custom" />
                                <label for="<?php echo self::PREFIX?>_footer_custom"><?php _e('Custom', 'themify-flow')?></label>
                                <div class="tf_custom_select">
                                    <select name="<?php echo self::PREFIX?>[footer_template]">
                                        <?php foreach($template_parts as $part):?>
                                            <option <?php selected( $footer_template, $part->post_name, true )?> value="<?php echo esc_attr( $part->post_name )?>"><?php echo esc_html( $part->post_title );?></option>
                                        <?php endforeach;?>
                                    </select>
                                </div>
                            <?php endif;?>
                       </div>
                    </div>
                </div>
            </div>
            <?php wp_reset_postdata();?>
        <?php
	}
        
       /**
        * When the post is saved, saves our metaboxs.
        *
        * @param int $post_id The ID of the post being saved.
        */
       function tf_action_metaboxes_save( $post_id ) {

               // Verify that the nonce is valid.et and If this is an autosave, our form has not been submitted, so we don't want to do anything.
               if ( ! isset( $_POST[self::PREFIX.'_metabox_nonce'] )  
                       || ! wp_verify_nonce($_POST[self::PREFIX.'_metabox_nonce'], self::PREFIX.'_metabox_nonce' )  
                       ||(defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE)) {
                       return;
               }
               
               // Check the user's permissions.
               if ( isset( $_POST['post_type'] ) && ($key = array_search($_POST['post_type'], self::$post_types)!==false)) {
                    if ( ! current_user_can( 'edit_'.self::$post_types[$key], $post_id ) ) {
                            return;
                    }
               }

               $post_keys = array('header','sidebar','footer','template','header_template','sidebar_template','footer_template');
               foreach ($post_keys as $key){
                   if($key=='template'||(isset($_POST[self::PREFIX][$key]) && $_POST[self::PREFIX][$key])){
                       $value = sanitize_text_field( $_POST[self::PREFIX][$key] );// Sanitize user input. 
                       update_post_meta($post_id, self::PREFIX.'_'.$key, $value ); // Update the meta field in the database.
                   }
               }
       }
}