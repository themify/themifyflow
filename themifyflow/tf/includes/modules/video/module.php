<?php
/**
 * Module Video.
 * 
 * Embed video from popular video sharing services.
 * @package ThemifyFlow
 * @since 1.0.0
 */
class TF_Module_Video extends TF_Module {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'name' => __( 'Video', 'themify-flow' ),
			'slug' => 'video',
			'shortcode' => 'tf_video',
			'description' => 'Video module',
			'category' => 'content'
		) );
	}

	/**
	 * Module settings field
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function fields() {
		global $TF;

		$image_base = $TF->framework_uri() . '/assets/img/builder';

		return apply_filters( 'tf_module_video_fields', array(
			'layout'  => array(
				'type'       => 'layout',
				'label'      => __( 'Video Layout', 'themify-flow' ),
				'options'    => array(
					array( 'img' => $image_base . '/video-top.png', 'value' => 'video-top', 'label' => __( 'Video Top', 'themify-flow' ), 'selected' => true ),
					array( 'img' => $image_base . '/video-left.png', 'value' => 'video-left', 'label' => __( 'Video Left', 'themify-flow' ) ),
					array( 'img' => $image_base . '/video-right.png', 'value' => 'video-right', 'label' => __( 'Video Right', 'themify-flow' ) ),
					array( 'img' => $image_base . '/video-overlay.png', 'value' => 'video-overlay', 'label' => __( 'Video Overlay', 'themify-flow' ) ),
				)
			),
			'video_url' => array(
				'type' => 'text',
				'label' => __( 'Video URL', 'themify-flow' ),
				'class' => 'tf_input_width_70',
			),
			'video_width_multi' => array(
				'type' => 'multi',
				'label' => __( 'Video Width', 'themify-flow' ),
				'fields' => array(
					'video_width' => array(
						'type' => 'number',
						'class' => 'tf_input_width_10',
					),
					'video_width_unit' => array(
						'type' => 'select',
						'label' => '',
						'options' => array(
							array( 'name' => __( 'px', 'themify-flow' ), 'value' => 'px', 'selected' => true ),
							array( 'name' => __( '%', 'themify-flow' ), 'value' => '%' ),
						),
						'description' => ''
					),
				),
			),
			'video_title' => array(
				'type' => 'text',
				'label' => __( 'Video Title', 'themify-flow' ),
				'class' => 'tf_input_width_70',
			),
			'video_title_link' => array(
				'type' => 'text',
				'label' => __( 'Video Title Link', 'themify-flow' ),
				'class' => 'tf_input_width_70',
			),
			'video_caption' => array(
				'type' => 'textarea',
				'label' => __( 'Video Caption', 'themify-flow' ),
				'class' => 'tf_input_width_70',
			)
		) );
	}

	/**
	 * Module style selectors.
	 * 
	 * Hold module style selectors to be used in Styling Panel.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return array
	 */
	public function styles() {
		return apply_filters( 'tf_module_video_styles', array(
			'tf_module_video_container' => array(
				'label' => __( 'Video Container', 'themify-flow' ),
				'selector' => '.tf_video_wrapper',
				'basic_styling' => array( 'background', 'font', 'padding', 'margin', 'border' ),
			),
			'tf_module_video_video' => array(
				'label' => __( 'Video Embed Container', 'themify-flow' ),
				'selector' => '.tf_video_wrap',
			),
			'tf_module_video_title' => array(
				'label' => __( 'Video Title', 'themify-flow' ),
				'selector' => '.tf_video_title',
				'basic_styling' => array( 'font', 'margin' ),
			),
			'tf_module_video_title_link' => array(
				'label' => __( 'Video Title Link', 'themify-flow' ),
				'selector' => '.tf_video_title_link a',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_video_title_link_hover' => array(
				'label' => __( 'Video Title Link Hover', 'themify-flow' ),
				'selector' => '.tf_video_title_link a:hover',
				'basic_styling' => array( 'font' ),
			),
			'tf_module_video_caption' => array(
				'label' => __( 'Video Caption', 'themify-flow' ),
				'selector' => '.tf_video_caption',
				'basic_styling' => array( 'font' ),
			)
		) );
	}

	/**
	 * Render main shortcode.
	 * 
	 * @since 1.0.0
	 * @access public
	 * @param array $atts 
	 * @param string $content 
	 * @return string
	 */
	public function render_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'layout' => 'video-top',
			'video_url' => '',
			'video_width' => '',
			'video_width_unit' => 'px',
			'video_title' => '',
			'video_title_link' => '',
			'video_caption' => '',
		), $atts, $this->shortcode ) );

		ob_start(); ?>

		<div class="tf_video_wrapper <?php echo $layout; ?>">
		<div class="tf_video_wrap" <?php echo '' != $video_width ? 'style="max-width: ' . esc_attr( $video_width ) . $video_width_unit . ';"' : ''; ?>>
			<?php echo $this->parse_video_embed_vars( wp_oembed_get( esc_url( $video_url ) ), esc_url( $video_url ) ); ?>
		</div><!-- .tf_video_wrap -->
		
		<?php if ( '' != $video_title || '' != $video_caption ): ?>
		<div class="tf_video_content">
	
			<?php if( '' != $video_title ): ?>
			<h3 class="tf_video_title">
				<?php if ( $video_title_link ) : ?>
				<a href="<?php echo esc_url( $video_title_link ); ?>"><?php echo wp_kses_post( $video_title ); ?></a>
				<?php else: ?>
				<?php echo wp_kses_post( $video_title ); ?>
				<?php endif; ?>
			</h3>
			<?php endif; ?>
			
			<?php if( '' != $video_caption ) : ?>
			<div class="tf_video_caption">
				<?php echo apply_filters( 'themify_builder_module_content', $video_caption ); ?>
			</div><!-- .tf_video_caption -->
			<?php endif; ?>

		</div><!-- .tf_video_content -->
		<?php endif; ?>
		</div>

		<?php
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Add wmode transparent and post-video container for responsive purpose
	 * @param string $html The embed markup.
	 * @param string $url The URL embedded.
	 * @return string The modified embed markup.
	 */
	function parse_video_embed_vars( $html, $url ) {
		$services = array(
			'youtube.com',
			'youtu.be',
			'blip.tv',
			'vimeo.com',
			'dailymotion.com',
			'hulu.com',
			'viddler.com',
			'qik.com',
			'revision3.com',
			'wordpress.tv',
			'wordpress.com',
			'funnyordie.com'
		);
		$video_embed = false;
		foreach( $services as $service ) {
			if( stripos($html, $service) ) {
				$video_embed = true;
				break;
			}
		}
		if( $video_embed ) {
			$html = '<div class="tf_video_embed">' . $html . '</div>';
			if( strpos( $html, "<embed src=" ) !== false ) {
				$html = str_replace('</param><embed', '</param><param name="wmode" value="transparent"></param><embed wmode="transparent" ', $html);
				return $html;
			} else {
				if( strpos( $html, 'wmode=transparent' ) == false ) {
					if( stripos($url, 'youtube') || stripos($url, 'youtu.be') ) {

						if( stripos($url, 'youtu.be') ) {
							$parsed = parse_url($url);
							$ytq = isset( $parsed['query'] )? $parsed['query']: '';
							$url = 'http://www.youtube.com/embed' . $parsed['path'] . '?wmode=transparent&fs=1' . $ytq;
						} else {
							$parsed = parse_url($url);
							parse_str($parsed['query'], $query);

							$parsed['scheme'] .= '://';

							if ( isset( $query['v'] ) && '' != $query['v'] ) {
								$parsed['path'] = '/embed/' . $query['v'];
								unset( $query['v'] );
							} else {
								$parsed['path'] = '/embed/';
							}

							$query['wmode'] = 'transparent';
							$query['fs'] = '1';

							$parsed['query'] = '?';
							foreach ( $query as $param => $value ) {
								$parsed['query'] .= $param . '=' . $value . '&';
							}
							$parsed['query'] = substr($parsed['query'], 0, -1);

							$url = implode('', $parsed);
						}

						return preg_replace('/src="(.*)" (frameborder)/i', 'src="'.esc_url( $url ).'" $2', $html);
					} else {
						$search = array('?fs=1', '?fs=0');
						$replace = array('?fs=1&wmode=transparent', '?fs=0&wmode=transparent');
						$html = str_replace($search, $replace, $html);
						return $html;
					}
				} else {
					return $html;
				}
			}
		} else {
			return '<div class="post-embed">' . $html . '</div>';
		}
	}
}

new TF_Module_Video();