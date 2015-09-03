<?php

// uncomment this line for testing
//set_site_transient( 'update_plugins', null );
//set_site_transient( 'update_themes', null );
/**
 * Allows plugins to use their own update API.
 *
 * @author Themify
 * @version 1.0
 */
include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
class TF_Updater {
    
	private $api_data  = array();
	private $name      = '';
        private static $instance = null;
	private $slug      = '';
        private $theme      = false;
        private $response_key;
	const THEMIFY_STORE_URL = 'http://themifyflow.com/dev/';
        const PREFFIX = 'tf_updater';
        const VERSION = '1.0';
        
        
	/**
	 * Class constructor.
	 *
	 * @uses plugin_basename()
	 * @uses hook()
	 *
	 * @param string  $_plugin_file Path to the plugin file.
	 * @return void
	 */
        
	public function __construct($_file) {
            if(is_admin()){
                
                $this->theme = strpos($_file,'plugins')===FALSE;
                $this->slug  = $key = !$this->theme?basename($_file, '.php'):basename(dirname($_file));
                $this->name     = plugin_basename($_file);
                $this->api_data = get_option(self::PREFFIX.'_license_keys');
                $this->response_key   = $this->slug . '-update-response';
                
                /* for syncronize with previous version will be removed in the next update*/
                $tmp = get_option(self::PREFFIX.'_license_tmp');
                if(!$tmp){
                    $this->api_data = array();
                    update_option(self::PREFFIX.'_license_tmp', 1);
                }
                /*---*/
                $data = array();
                if(!isset($this->api_data[$key]) || !get_transient(self::PREFFIX.'-'.$this->slug)){
                   if(!$this->theme){
                        $data = get_file_data($_file,array('Name'=>'Plugin Name','Version'=>'Version'));
                   }
                   else{

                       $theme = wp_get_theme($this->slug);
                       if(!$theme->exists()){
                           return;
                       }
                       $data['Name'] = $theme->get( 'Name' );
                       $data['Version'] = $theme->get('Version');

                   } 
                    if(!isset($this->api_data[$key])){
                        $this->api_data[$key] = array();
                        $this->api_data[$key]['status'] = 'invalid';
                        $this->api_data[$key]['license']  = false;
                        $this->api_data[$key]['dir'] = $_file;
                    }
                    else{
                          set_transient(self::PREFFIX.'-'.$this->slug, 1,MINUTE_IN_SECONDS);//for test
                    }
                    $this->api_data[$key]['item_name'] = $data['Name'];
                    $this->api_data[$key]['version'] = $data['Version'];
                    update_option(self::PREFFIX.'_license_keys', $this->api_data);
                } 
                $this->version  = $this->api_data[$key]['version'];
                // Set up hooks.
                $this->init();
                if(self::$instance==NULL){
                    self::$instance = 1;
                }
            }

	}    
        
        /**
	 * Set up WordPress filters to hook into WP's update process.
	 *
	 * @uses add_filter()
	 *
	 * @return void
	 */
	public function init() { 
                
                if(null == self::$instance){
                    add_filter('tf_settings_and_sections',array($this, 'add_settings'));
                    add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue_scripts'));
                    add_action('wp_ajax_'.self::PREFFIX.'_activate_license', array($this, 'activate_license'));
                    add_action('wp_ajax_'.self::PREFFIX.'_activate_auto', array($this, 'activate_auto'));

                } 
                if(!$this->theme){
                    add_action('admin_init', array($this, 'show_changelog' ) );
                    add_filter('pre_set_site_transient_update_plugins', array( $this, 'check_plugin_update'));
                    add_filter('plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
                    add_action('after_plugin_row_' . $this->name, array( $this, 'show_update_notification' ), 10, 2 );
                }
                else{
                    add_filter( 'site_transient_update_themes', array( &$this, 'theme_update_transient' ) );
                    add_filter( 'delete_site_transient_update_themes', array( &$this, 'delete_theme_update_transient' ) );
                    add_action( 'load-update-core.php', array( &$this, 'delete_theme_update_transient' ) );
                    add_action( 'load-themes.php', array( &$this, 'delete_theme_update_transient' ) );
                    add_action( 'admin_notices', array( &$this, 'update_nag' ) );
                }
               
              
	}

        public function add_settings($sections){
            $sections['licenses'] = array('title' => __( 'Licenses', 'themify-flow' ),
                                        'callback' => array($this,'license_page'),
                                        'fields'=>array()
                                        );
            return $sections;
            
        }
           
        
        
        
       function theme_update_transient( $value ) {
		$update_data = $this->check_template_update();
                            
		if ( $update_data ) {
			$value->response[ $this->slug ] = $update_data;
		}
		return $value;
	}
        
        function delete_theme_update_transient() {
		delete_transient( $this->response_key );
	}
        
        function update_nag() {
		$theme = wp_get_theme( $this->slug );
		$api_response = get_transient( $this->response_key );
                
		if( false === $api_response )
			return;
		$update_url = wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( $this->slug ), 'upgrade-theme_' . $this->slug );
		$update_onclick = ' onclick="if ( confirm(\'' . esc_js( __( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update." ) ) . '\') ) {return true;}return false;"';
		if ( version_compare( $this->version, $api_response->new_version, '<' ) ) {
			add_thickbox();
                        echo '<div id="update-nag">';
				printf( '<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.',
					$theme->get( 'Name' ),
					$api_response->new_version,
					'#TB_inline?width=640&amp;inlineId=' . $this->slug . '_changelog',
					$theme->get( 'Name' ),
					$update_url,
					$update_onclick
				);
			echo '</div>';
			echo '<div id="' . $this->slug . '_' . 'changelog" style="display:none;">';
				echo wpautop( $api_response->sections['changelog'] );
			echo '</div>';
		}
	}

        function check_template_update() {
		
		$update_data = get_transient( $this->response_key );
                $failed = false; 
		if ( false === $update_data ) {
			
			if( empty( $this->api_data[$this->slug]['license'] ) )
				return false;
			$api_params = array(
				'edd_action' 	=> 'get_version',
				'license' 	=> $this->api_data[$this->slug]['license'],
				'name' 		=> $this->api_data[$this->slug]['item_name'],
				'slug' 		=> $this->slug,
				'url'           => home_url()
			);
			$response = wp_remote_post(self::THEMIFY_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
			 // make sure the response was successful
			if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
				$failed = true;
			}
			$update_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! is_object( $update_data ) ) {
				$failed = true;
			}
			// if the response failed, try again in 30 minutes
			if ( $failed ) {
				$data = new stdClass;
				$data->new_version = $this->version;
				set_transient( $this->response_key, $data, strtotime( '+30 minutes' ) );
				return false;
			}
			// if the status is 'ok', return the update arguments
			if ( ! $failed ) {
				$update_data->sections = maybe_unserialize( $update_data->sections );
				set_transient( $this->response_key, $update_data, strtotime( '+1 minutes' ) );//for test
			}
                       
		}
                if(!$failed){
                    if ( version_compare( $this->version, $update_data->new_version, '>=' ) ) {
                            return false;
                    }
                    elseif(isset($this->api_data[$this->slug]['auto']) && $this->api_data[$this->slug]['auto']){
                       
                        $this->version = $update_data->new_version;  
                        $update_data->theme = $this->slug;
                        $update_data->autoupdate = TRUE;
                        $data = $update_data;
                        $data->response[$this->slug] = (array)$update_data;
                        $data->checked[ $this->slug ] = $this->version;
                        set_site_transient( 'update_themes', $update_data );
                        $upgrader = new WP_Automatic_Updater();
                        $result = $upgrader->update('theme', $update_data);
                       
                        if(!is_wp_error(!$result)){
                            delete_transient(self::PREFFIX.'-'.$this->slug);
                        }
                        else{
                            exit;
                        }
                    }
                }
		return (array) $update_data;
	}
        
        
        /**
	 * License activation ajax handler
         * 
	 * @return void
	 */
        
        public function activate_license(){
            // listen for our activate button to be clicked
          
            if( isset( $_POST['key'] ) && isset($_POST[$_POST['key']])) {
                    // run a quick security check
       
                    if( ! check_admin_referer( self::PREFFIX.'_nonce', self::PREFFIX.'_nonce' ) ){
                            return; 
                    }
                    $licenses_array = get_option(self::PREFFIX.'_license_keys' );
                    // retrieve the license from the database
                    $key = esc_attr($_POST['key']);
                    if(!isset($licenses_array[$key])){
                        return;
                    }
                    $license = esc_attr($_POST[$key]);
                    // data to send in our API request
                    $api_params = array(
                            'edd_action'=> $licenses_array[$key]['status']=='invalid'?'activate_license':'deactivate_license',
                            'license' 	=> $license,
                            'item_name' => $licenses_array[$key]['item_name'],// the name  of our product in Flow
                            'url'       => home_url()
                    );     
                  
                    // Call the custom API.
                    $response = wp_remote_post( self::THEMIFY_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

                    // make sure the response came back okay
                    if ( is_wp_error( $response ) )
                            return false;

                    // decode the license data
                    $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                    // $license_data->license will be either "valid" or "invalid"

                    $licenses_array[$key]['status'] = $license_data->license=='valid'?'valid':'invalid';
                    $licenses_array[$key]['license'] = $license;
                    if(isset($license_data->error) || $license_data->license=='failed'){
                        $licenses_array[$key]['error'] = $license_data->license=='failed'?'failed':$license_data->error;
                    }
                    else{
                        
                        $licenses_array[$key]['expires'] = $license_data->expires;
                        $licenses_array[$key]['error'] = false;
                        $licenses_array[$key]['item_name'] = $license_data->item_name;
                    }  
                    if($licenses_array[$key]['status']!='valid' && isset($licenses_array[$key]['auto'])){
                        unset($licenses_array[$key]['auto']);
                    }
                    update_option(self::PREFFIX.'_license_keys', $licenses_array);
                    $resp = $licenses_array[$key];
                    if(!isset($resp['error']) || !$resp['error']){
                        $resp['expires'] = __('Date Expires','tmeify-flow').' '.$resp['expires'];                        
                    }
                    else{
                       $resp['error'] = $this->get_error_notifaction($resp['error']);
                    }
                    $resp['auto'] = $licenses_array[$key]['status']=='valid';
                    $resp['btn'] = $resp['status']=='valid'?__('Deactivate License','themify-flow'):__('Activate License','themify-flow');
                    die(json_encode($resp));
            }
        }
        
        /**
	 * Get error message
         * 
	 * @params string $error_type
	 * @return string
	 */
        
        private function get_error_notifaction($error_type){
            switch($error_type){
                case 'expired': 
                    return __('License key is expired','themify-flow');
                case 'invalid' :
                case 'missing':
                case 'failed':
                    return __('License key is invalid','themify-flow');
                case 'item_name_mismatch':
                    return __('Incorrect Item name','themify-flow');
                case 'revoked':
                    return __('License key is disabled','themify-flow');
                case 'invalid_item_id':
                    return __('Incorrect Item id','themify-flow');
                case 'no_activations_left':
                    return __('This license key is already used','themify-flow');
                default:
                    return '';
            }
            
        }
        
        /**
	 * Render Licenses page
	 *
	 * @return void
	 */
        public function license_page(){
        $licenses = get_option(self::PREFFIX.'_license_keys');
	?>
	<div class="wrap"  id="<?php echo self::PREFFIX?>_license_form">
            <?php 
                 wp_nonce_field( self::PREFFIX.'_nonce', self::PREFFIX.'_nonce' ); 
             ?>
             <table class="form-table">
                     <tbody>
                         <?php foreach($licenses as $key=>$license):?>
                             <tr>
                                 <?php 
                                 if(!file_exists($license['dir'])){
                                     continue;;
                                 }
                                
                                 ?>
                                 <td>
                                     <label for="<?php echo $key?>"><?php echo $license['item_name']?></label>
                                 </td>
                                 <td>
                                     <input type="text" name="<?php echo $key?>" id="<?php echo $key?>" class="regular-text" placeholder="<?php _e('Enter your license key','themify-flow'); ?>" value="<?php esc_attr_e($license['license'] ); ?>" />                                                
                                     <div class="<?php if(isset($license['error']) && $license['error']):?>tf_updater_error<?php endif;?> tf_updater_notifaction">
                                         <p> 
                                             <?php if(isset($license['error']) && $license['error']):?>
                                                 <?php echo $this->get_error_notifaction($license['error']);?>
                                             <?php endif;?>
                                         </p>
                                     </div>
                                     <div class="tf_date_expires">
                                         <?php if((!isset($license['error']) || !$license['error']) && isset($license['expires'])):?>
                                             <?php _e('Date expires','themify-flow')?>: <?php echo $license['expires']?>
                                         <?php endif;?>
                                     </div>
                                 </td>
                                 <td class="tf_auto_update_input">
                                     <input class="<?php echo self::PREFFIX?>_auto_update" <?php if($license['status']!= 'valid'):?>disabled="disabled"<?php endif;?> type="checkbox" id="<?php echo $key?>_auto" name="<?php echo $key?>_<?php echo self::PREFFIX?>_auto" value="1" <?php echo isset($license['auto']) && $license['auto'] && isset($license['expires'])?'checked="checked"':''?> />
                                     <label for="<?php echo $key?>_auto"><?php _e('AutoUpdate','themify-flow')?></label>
                                 </td>
                                 <td>
                                     <input type="button" data-key="<?php echo $key?>" class="<?php echo self::PREFFIX?>_activation_btn button-secondary" value="<?php $license['status'] == 'valid'?_e('Deactivate License','themify-flow'):_e('Activate License','themify-flow'); ?>"/>
                                 </td>
                             </tr>
                         <?php endforeach;?>
                     </tbody>
             </table>

        </div>
	<?php
        }
	
        
        /**
	 * Autoactivation ajax handler
	 *
	 * @return void
	 */
        public function activate_auto(){
            if(isset($_POST['akey']) && $_POST['akey'] && isset($_POST['value'])){
                
                if( ! check_admin_referer( self::PREFFIX.'_nonce', self::PREFFIX.'_nonce' ) ){
                      return;
                }     
                $key = esc_attr($_POST['akey']);
                $key = str_replace('_'.self::PREFFIX.'_auto','',$key);
                $licenses_array = get_option(self::PREFFIX.'_license_keys');
                // retrieve the license from the database
                if(!isset($licenses_array[$key])){
                    die(json_encode(array('msg'=>__("Cann't find the addon",'themify-flow'))));
                }
                if($licenses_array[$key]['status']!='valid'){
                     die(json_encode(array('msg'=>__("You need to activate license",'themify-flow'))));
                }
              
                if(intval($_POST['value'])>0){
                    $licenses_array[$key]['auto'] = 1;
                }
                elseif(isset($licenses_array[$key]['auto'])){
                    unset($licenses_array[$key]['auto']);
                }
                update_option(self::PREFFIX.'_license_keys', $licenses_array);
                $msg = $licenses_array[$key]['item_name'];
                $msg.= isset($licenses_array[$key]['auto'])?__(' autoupdate is activated','themify-flow'):__(' autoupdate is deactivated','themify-flow');
                die(json_encode(array('msg'=>$msg,'success'=>1)));
            }
            wp_die();
        }
        
       
        
        

        

        
	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * This function dives into the update API just when WordPress creates its update array,
	 * then adds a custom API call and injects the custom plugin data retrieved from the API.
	 * It is reassembled from parts of the native WordPress plugin update code.
	 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
	 *
	 * @uses api_request()
	 *
	 * @param array   $_transient_data Update array build by WordPress.
	 * @return array Modified update array with custom plugin data.
	 */
	function check_plugin_update( $_transient_data ) {
                
		global $pagenow;

		if(!is_object( $_transient_data ) ) {
			$_transient_data = new stdClass;
		}
            
		if((!$this->slug || !isset($this->api_data[$this->slug]))  || ( 'plugins.php' == $pagenow && is_multisite() )) {
			return $_transient_data;
		}

		if ( empty( $_transient_data->response ) || empty( $_transient_data->response[ $this->name ] ) ) {
                        
			$version_info = $this->api_request( 'plugin_latest_version', array( 'slug' => $this->slug ) );
                       if ( false !== $version_info && is_object( $version_info ) && isset( $version_info->new_version ) ) {
				$this->did_check = true;

				if( version_compare( $this->version, $version_info->new_version, '<' ) ) {
                                  
					$_transient_data->response[ $this->name ] = $version_info;
                                        $_transient_data->response[ $this->name ]->plugin = $this->name;
                                        if(isset($this->api_data[$this->slug]['auto']) && $this->api_data[$this->slug]['auto']){
                                            $this->version = $version_info->new_version;
                                            $_transient_data->response[ $this->name ]->autoupdate = TRUE;
                                            $_transient_data->last_checked = time();
                                            $_transient_data->checked[ $this->name ] = $this->version;
                                            set_site_transient( 'update_plugins', $_transient_data );
                                            $upgrader = new WP_Automatic_Updater();
                                            $result = $upgrader->update('plugin', $_transient_data->response[ $this->name ]);
                                            if(!is_wp_error(!$result)){
                                                delete_transient(self::PREFFIX.'-'.$this->slug);
                                                activate_plugin($this->api_data[$this->slug]['dir']);
                                            }
                                            else{
                                                exit;
                                            }
                                        }
				}
                              
                                
				$_transient_data->last_checked = time();
				$_transient_data->checked[ $this->name ] = $this->version;

			}
		}
               
		return $_transient_data;
	}

	/**
	 * show update nofication row -- needed for multisite subsites, because WP won't tell you otherwise!
	 *
	 * @param string  $file
	 * @param array   $plugin
	 */
	public function show_update_notification( $file, $plugin ) {

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		if ( ! is_multisite() ) {
			return;
		}
              
		if ( $this->name != $file ) {
			return;
		}
                
		// Remove our filter on the site transient
		remove_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_plugin_update' ), 10 );

		$update_cache = get_site_transient( 'update_plugins' );

		if ( ! is_object( $update_cache ) || empty( $update_cache->response ) || empty( $update_cache->response[ $this->name ] ) ) {

			$cache_key    = md5( 'td_plugin_' .sanitize_key( $this->name ) . '_version_info' );
			$version_info = get_transient( $cache_key );

			if( false === $version_info ) {

				$version_info = $this->api_request( 'plugin_latest_version', array( 'slug' => $this->slug ) );

				set_transient( $cache_key, $version_info, 3600 );
			}


			if ( ! is_object( $version_info ) ) {
				return;
			}

			if ( version_compare( $this->version, $version_info->new_version, '<' ) ) {

				$update_cache->response[ $this->name ] = $version_info;
                                $update_cache->response[ $this->name ]->plugin = $this->name;

			}

			$update_cache->last_checked = time();
			$update_cache->checked[ $this->name ] = $this->version;

			set_site_transient( 'update_plugins', $update_cache );

		} else {

			$version_info = $update_cache->response[ $this->name ];

		}

		// Restore our filter
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_plugin_update' ) );

		if ( ! empty( $update_cache->response[ $this->name ] ) && version_compare( $this->version, $version_info->new_version, '<' ) ) {

			// build a plugin list row, with update notification
			$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
			echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';

			$changelog_link = self_admin_url( 'index.php?edd_sl_action=view_plugin_changelog&plugin=' . $this->name . '&slug=' . $this->slug . '&TB_iframe=true&width=772&height=911' );
                        
			if ( empty( $version_info->download_link ) ) {
				printf(
					__( 'There is a new version of %1$s available. <a target="_blank" class="thickbox" href="%2$s">View version %3$s details</a>.', 'theimfy-flow' ),
					esc_html( $version_info->name ),
					esc_url( $changelog_link ),
					esc_html( $version_info->new_version )
				);
			} else {
				printf(
					__( 'There is a new version of %1$s available. <a target="_blank" class="thickbox" href="%2$s">View version %3$s details</a> or <a href="%4$s">update now</a>.', 'theimfy-flow' ),
					esc_html( $version_info->name ),
					esc_url( $changelog_link ),
					esc_html( $version_info->new_version ),
					esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $this->name, 'upgrade-plugin_' . $this->name ) )
				);
			}

			echo '</div></td></tr>';
		}
	}


	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 * @uses api_request()
	 *
	 * @param mixed   $_data
	 * @param string  $_action
	 * @param object  $_args
	 * @return object $_data
	 */
	function plugins_api_filter( $_data, $_action = '', $_args = null ) {

		if ( $_action != 'plugin_information' ) {
                    return $_data;
		}    
            
		if (!isset($this->api_data[$_args->slug])) {
                    return $_data;
		}
                
		$to_send = array(
			'slug'   => $_args->slug,
			'is_ssl' => is_ssl(),
			'fields' => array(
				'banners' => false, // These will be supported soon hopefully
				'reviews' => false,
			)
		);
       
                  
             
		$api_response = $this->api_request( 'plugin_information', $to_send );
		if ($api_response ) {
                    if(!isset($api_response->last_updated)){
                        $api_response->last_updated = false;
                    }
                    $_data = $api_response;
		}
		return $_data;
	}


	/**
	 * Disable SSL verification in order to prevent download update failures
	 *
	 * @param array   $args
	 * @param string  $url
	 * @return object $array
	 */
	function http_request_args( $args, $url ) {
		// If it is an https request and we are performing a package download, disable ssl verification
		if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'edd_action=package_download' ) ) {
			$args['sslverify'] = false;
		}
		return $args;
	}

	/**
	 * Calls the API and, if successfull, returns the object delivered by the API.
	 *
	 * @uses get_bloginfo()
	 * @uses wp_remote_post()
	 * @uses is_wp_error()
	 *
	 * @param string  $_action The requested action.
	 * @param array   $_data   Parameters for the API action.
	 * @return false||object
	 */
	private function api_request( $_action, $_data ) {

		global $wp_version;
                if(!isset($this->api_data[$this->slug])){
                    return;
                }
		$data = array_merge( $this->api_data[$this->slug], $_data );
                
		if ( $data['slug'] != $this->slug ) {
			return;
		}
      
		if ( empty( $data['license'] ) ) {
			return;
		}
		if( self::THEMIFY_STORE_URL == home_url() ) {
			return false; // Don't allow a plugin to ping itself
		}

		$api_params = array(
			'edd_action'  => 'get_version',
			'license'  => $data['license'],
			'item_name'  =>$data['item_name'],
			'item_id'  => isset( $data['item_id'] ) ? $data['item_id'] : false,
			'slug'  => $data['slug'],
			'url'	  => home_url()
		);
		$request = wp_remote_post( self::THEMIFY_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
      
              
		if ( ! is_wp_error( $request ) ) {
			$request = json_decode( wp_remote_retrieve_body( $request ) );
		}

		if ( $request && isset( $request->sections ) ) {
			$request->sections = maybe_unserialize( $request->sections );
		} else {
			$request = false;
		}

		return $request;
	}

	public function show_changelog() {
             
		if ( empty( $_REQUEST['edd_sl_action'] ) || 'view_plugin_changelog' != $_REQUEST['edd_sl_action'] ) {
			return;
		}

		if ( empty( $_REQUEST['plugin'] ) ) {
			return;
		}

		if ( empty( $_REQUEST['slug'] ) ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_die( __( 'You do not have permission to install plugin updates', 'theimfy-flow' ), __( 'Error', 'theimfy-flow' ), array( 'response' => 403 ) );
		}

		$response = $this->api_request( 'plugin_latest_version', array( 'slug' => $_REQUEST['plugin'] ) );
               
		if ( $response && isset( $response->sections['changelog'] ) ) {
			echo '<div style="background:#fff;padding:10px;">' . $response->sections['changelog'] . '</div>';
		}

		exit;
	}
        
        
        public static function admin_enqueue_scripts(){
             global $TF;
            // wp_enqueue_style(self::PREFFIX, $TF->framework_uri() . '/assets/css/tf-updater.css', array(), self::VERSION, false );
             wp_enqueue_script(self::PREFFIX, $TF->framework_uri() . '/assets/js/tf-updater.js', array('jquery'), self::VERSION, false );
        }
}