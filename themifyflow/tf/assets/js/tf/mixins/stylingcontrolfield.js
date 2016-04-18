var TF, wp, _;

(function($){

	'use strict';

	// mixins
	TF.Mixins.StylingControlField = {
		input: function($obj, key){
			var control = this,
				$container = $obj.closest('.tf_property_wrap'),
				$field = $('.tf_styling_panel_value_field', $container),
				setting_key = $field.data('styling-setting-link');

			$obj.on('keyup', function(){
				var setup_data = {};
				setup_data[ key ] = $(this).val();
				control._updateField( setup_data, setting_key, $field );
			});
		},

		customcss: function($obj, key) {
			var control = this,
				$container = $obj.closest('.tf_property_wrap'),
				$field = $('.tf_styling_panel_value_field', $container),
				setting_key = $field.data('styling-setting-link');

			$obj.on( 'keyup paste', function (e) {
				var $self = $(this),
					setup_data = {};

				if ( 'paste' === e.type ) {
					setTimeout( function() {
						setup_data[ key ] = $self.val();
						control._updateField( setup_data, setting_key, $field );
					}, 1 );
				} else {
					setup_data[ key ] = $self.val();
					control._updateField( setup_data, setting_key, $field );
				}
			});
		},

		inputInt: function($obj, key){
			var control = this,
				$container = $obj.closest('.tf_property_wrap'),
				$field = $('.tf_styling_panel_value_field', $container),
				setting_key = $field.data('styling-setting-link');

			$obj.on('keyup change input', function(){
				var $self = $(this),
					setup_data = {};
				setup_data[ key ] = $self.val();
				control._updateField( setup_data, setting_key, $field );
				$self.next().text( $self.val() + '%' );
			});
		},

		fontFamily: function($obj) {
			var control = this,	
				$container = $obj.closest('.tf_property_wrap'),
				$variant = $container.find('.tf_font_variant'),
				$field = $('.tf_styling_panel_value_field', $container),
				setting_key = $field.data('styling-setting-link');

			$obj.on('change', function(){
				var fontData = $(this).val(),
					fontDataObj = $.parseJSON( fontData ) || {},
					setup_data = {},
					selectedVariant = '';

				if ( ! _.isUndefined( fontDataObj.fonttype ) ) {
					if ( 'google' !== fontDataObj.fonttype ) {
						$variant.hide();
					} else {
						$variant.show();
					}
				}  else {
					$variant.show();
				}
				$variant.empty();
				if ( ! _.isUndefined( fontDataObj.variant ) ) {
					var variants = 'string' === typeof fontDataObj.variant ? fontDataObj.variant.split(',') : fontDataObj.variant,
						selected;
					_.each( variants, function( item ){
						selected = '';
						if ( 'regular' === item || '400' === item ) {
							selected = 'selected="selected"';
							selectedVariant = item;
						}
						var string = String( item ); // make sure it's a string
						string = string.charAt(0).toUpperCase() + string.slice(1);
						$variant.append( '<option value="' + item + '" ' + selected + '>' + string + '</option>' );
					});
					if ( '' === selectedVariant ) {
						selectedVariant = variants[0];
						$variant.prepend( '<option value=""></option>' );
					}
				}
				
				setup_data.family = $.parseJSON( fontData ) || {};
				setup_data.family.variant = selectedVariant;
				control._updateField( setup_data, setting_key, $field );
			});

			$variant.on( 'change', function(){
				var fontData = $container.find('.tf_font_family').val(),
					setup_data = {};

				setup_data.family = $.parseJSON( fontData );
				setup_data.family.variant = $(this).val();
				control._updateField( setup_data, setting_key, $field );
			});
		},

		dropdown: function($objects, key) {
			var control = this;
			$($objects).each(function(){
				var $obj = $(this),
					$container = $obj.closest('.tf_property_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					setting_key = $field.data('styling-setting-link');

				$obj.on('change', function(){
					var setup_data = {};
					setup_data[ key ] = $(this).val();
					control._updateField( setup_data, setting_key, $field );
				});
			});
				
		},

		dropdownPosition: function($obj, key) {
			var control = this,	
				$container = $obj.closest('.tf_property_wrap'),
				$field = $('.tf_styling_panel_value_field', $container),
				setting_key = $field.data('styling-setting-link');

			$obj.on('change', function(){
				var setup_data = {};
				setup_data[ key ] = $(this).val();
				control._updateField( setup_data, setting_key, $field );

				if ( '' !== setup_data[key] && 'static' !== setup_data[key] ) {
					$container.find('.component').show();
				} else {
					$container.find('.component').hide();
				}
				control._resizeScroll();
			});
		},

		fontStyle: function($style) {
			var control = this,	
				$container = $style.closest('.tf_property_wrap'),
				$field = $('.tf_styling_panel_value_field', $container),
				setting_key = $field.data('styling-setting-link'),
				setup_data = {};

			// Font style
			$('span', $style).on('click', function(){
				var clickedStyle = $(this).data('style');
				if ( 'underline' === clickedStyle ) {
					$('span.tf_active[data-style="linethrough"]', $style).removeClass('tf_active');
				}
				if ( 'linethrough' === clickedStyle ) {
					$('span.tf_active[data-style="underline"]', $style).removeClass('tf_active');
				}
				if ( 'nostyle' === clickedStyle ) {
					$('span:not([data-style="nostyle"])', $style).removeClass('tf_active');
				}
				if ( 'nostyle' !== clickedStyle ) {
					$('span[data-style="nostyle"]', $style).removeClass('tf_active');
				}
				// Mark this as selected
				$(this).toggleClass('tf_active');

				// Check which buttons are set
				$('span', $style).each(function(){
					if ( $(this).hasClass('tf_active') ) {
						setup_data[$(this).data('style')] = $(this).data('style');
					} else {
						setup_data[$(this).data('style')] = '';
					}
				});
				control._updateField( setup_data, setting_key, $field );

			});
		},

		textTransform: function($texttransform){
			var control = this,	
				$container = $texttransform.closest('.tf_property_wrap'),
				$field = $('.tf_styling_panel_value_field', $container),
				setting_key = $field.data('styling-setting-link'),
				setup_data = {};

			$('span', $texttransform).on('click', function(){
				var clickedTextTrans = $(this).data('texttransform');
				// Mark this as selected
				$('span', $texttransform).not(this).removeClass('tf_active');
				$(this).toggleClass('tf_active');

				if ( 'notexttransform' === clickedTextTrans ) {
					$('span:not([data-texttransform="notexttransform"])', $texttransform).removeClass('tf_active');
				}
				if ( 'notexttransform' !== clickedTextTrans ) {
					$('span[data-texttransform="notexttransform"]', $texttransform).removeClass('tf_active');
				}

				setup_data.texttransform = control._getSelectedData( $texttransform, 'texttransform' );
				control._updateField( setup_data, setting_key, $field );
			});
		},

		textAlign: function( $obj ) {
			var control = this,	
				$container = $obj.closest('.tf_property_wrap'),
				$field = $('.tf_styling_panel_value_field', $container),
				setting_key = $field.data('styling-setting-link'),
				setup_data = {};

			$('span', $obj).on('click', function(){
				var clickedAlign = $(this).data('align');
				// Mark this as selected
				$('span', $obj).not(this).removeClass('tf_active');
				$(this).toggleClass('tf_active');

				if ( 'noalign' === clickedAlign ) {
					$('span:not([data-align="noalign"])', $obj).removeClass('tf_active');
				}
				if ( 'noalign' !== clickedAlign ) {
					$('span[data-align="noalign"]', $obj).removeClass('tf_active');
				}
				setup_data.align = control._getSelectedData( $obj, 'align' );
				control._updateField( setup_data, setting_key, $field );
			});
		},

		dropdownBorderSame: function($obj){
			var control = this,	
				$container = $obj.closest('.tf_property_wrap'),
				$field = $('.tf_styling_panel_value_field', $container),
				setting_key = $field.data('styling-setting-link'),
				setup_data = {},
				$same = $('.same', $container);

			$obj.on('change', function(){
				var $self = $(this),
					side = $self.data('side' ),
				    style = $('option:selected', $self).val(),
				    exist_data = $.parseJSON( $field.val() ) || {};

				if ( $same.prop('checked') ) {
					setup_data.style = style;
					setup_data.same = 'same';
				} else {
					setup_data[side] = _.extend({}, exist_data[side], {
						'style' : style
					});
					_.each( _.without( ['top', 'right', 'bottom', 'left'],side ), function( key ){
						setup_data[ key ] = exist_data[ key ];
					});

					if ( 'top' === side ) {
						setup_data.style = style;
					}
					setup_data.same = '';
				}
				control._updateField( setup_data, setting_key, $field );
				if ( 'none' === style ) {
					$self.parent().siblings('.color-picker, .border-width, .dimension-unit-label').hide();
				} else {
					$self.parent().siblings('.color-picker, .border-width, .dimension-unit-label').show();
				}
			});

			$obj.each(function(){
				var $self = $(this),
				    style = $('option:selected', $self).val();
				if ( 'none' === style ) {
					$self.parent().siblings('.color-picker, .border-width, .dimension-unit-label').hide();
				} else {
					$self.parent().siblings('.color-picker, .border-width, .dimension-unit-label').show();
				}
			});
		},

		dimension: function( $objects ) {
			var control = this;
			$objects.each(function(){
				var $obj = $(this),	
					$container = $obj.closest('.tf_property_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					setting_key = $field.data('styling-setting-link'),
					setup_data = {};

				$obj.on('keyup', function(){
					setup_data.width = $(this).val();
					control._updateField( setup_data, setting_key, $field );
				});
			});
		},

		dimensionSame: function( $objects ) {
			var control = this;
			$objects.each(function(){
				var $obj = $(this),	
					$container = $obj.closest('.tf_property_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					setting_key = $field.data('styling-setting-link'),
					setup_data = {},
					$same = $('.same', $container);

				$obj.on('keyup', function(){
					var side = $(this).data('side'),
					    width = $(this).val(),
					    exist_data = $.parseJSON( $field.val() ) || {};

					if ( $same.prop('checked') ) {
						setup_data.width = width;
						setup_data.same = 'same';
					} else {
						setup_data[side] = _.extend({}, exist_data[side], {
							'width' : width
						});
						if ( 'top' === side ) {
							setup_data.width = width;
						}
						setup_data.same = '';
					}
					control._updateField( setup_data, setting_key, $field );
				});
			});
		},

		auto: function( $objects ) {
			var control = this;
			$objects.each(function(){
				var $obj = $(this),	
					$container = $obj.closest('.tf_property_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					setting_key = $field.data('styling-setting-link'),
					setup_data = {};

				$obj.on('click', function(){
					var $self = $(this);
					if ( $self.prop('checked') ) {
						setup_data.auto = 'auto';
					} else {
						setup_data.auto = '';
					}
					$obj.closest('.tf_margin_property_row').find('.tf_property_col_left').toggleClass('hide-x-on');
					control._updateField( setup_data, setting_key, $field );
				});
				if ( $obj.prop('checked') ) {
					$obj.closest('.tf_margin_property_row').find('.tf_property_col_left').addClass('hide-x-on');
				}
			});
		},

		autoSame: function( $objects ) {
			var control = this;
			$objects.each(function(){
				var $obj = $(this),	
					$container = $obj.closest('.tf_property_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					setting_key = $field.data('styling-setting-link'),
					setup_data = {},
					$same = $('.same', $container),
					obj = {};

				$obj.on('click', function(){
					var $self = $(this),
						side = $(this).data('side' );

					if ( $same.prop('checked') ) {
						if ( $self.prop('checked') ) {
							obj.auto = 'auto';
							setup_data.auto = 'auto';
							setup_data.top = _.extend({}, setup_data.top, obj);
						} else {
							obj.auto = '';
							setup_data.auto = '';
							setup_data.top = _.extend({}, setup_data.top, obj);
						}
						setup_data.same = 'same';
					} else {
						if ( $self.prop('checked') ) {
							obj.auto = 'auto';
							setup_data[side] = _.extend({}, setup_data[side], obj);
							if ( 'top' === side ) {
								setup_data.auto = 'auto';
							}
						} else {
							obj.auto = '';
							setup_data[side] = _.extend({}, setup_data[side], obj);
							if ( 'top' === side ) {
								setup_data.auto = '';
							}
						}
						setup_data.same = '';
					}
					$obj.closest('.tf_property_row').find('.tf_property_col_left').toggleClass('hide-x-on');
					control._updateField( setup_data, setting_key, $field );
				});
			});
		},

		dropdownSame: function($objects, key){
			var control = this;
			$objects.each(function(){
				var $obj = $(this),
					$container = $obj.closest('.tf_property_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					setting_key = $field.data('styling-setting-link'),
					setup_data = {},
					$same = $('.same', $container);

				$obj.on('change', function(){
					var side = $(this).data('side'),
					    val = $('option:selected', $(this)).val(),
					    exist_data = $.parseJSON( $field.val() ) || {};

					if ( $same.prop('checked') ) {
						setup_data[ key ] = val;
						setup_data.same = 'same';
					} else {
						var obj = {}; obj[key] = val;
						setup_data[side] = _.extend({}, exist_data[side], obj);
						if ( 'top' === side ) {
							setup_data[ key ] = val;
						}
						setup_data.same = '';
					}
					control._updateField( setup_data, setting_key, $field );
				});
			});
		},

		hideComponentsSame: function($objects) {
			var control = this;
			$objects.each(function(){
				var $obj = $(this),
					$container = $obj.closest('.tf_property_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					$components = $container.find('.component'),
					$rowLabel = $('.dimension-row-label:not(.same-label)', $container),
					$sameLabel = $container.find('.same-label'),
					setting_key = $field.data('styling-setting-link'),
					setup_data = {};

				$obj.on('click', function(){
					var $self = $(this);
					if ( $self.prop('checked') ) {
						setup_data.same = 'same';
						$components.stop().slideUp();
						$rowLabel.hide();
						$sameLabel.text( $sameLabel.data('same') );
					} else {
						setup_data.same = '';
						$components.stop().slideDown();
						$rowLabel.show();
						$sameLabel.text( $sameLabel.data('notsame') );
					}
					control._updateField( setup_data, setting_key, $field );
				});
			});
		},

		openMedia: function() {
			var control = this;
			$( '.tf_background_wrap', control.$el ).each(function() {
				var $obj = $(this),
					$container = $obj.closest('.tf_property_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					setting_key = $field.data('styling-setting-link'),
					setup_data = {},
					file_frame = '';

				$('.open-media', $container).on('click', function(e){
					e.preventDefault();

					file_frame = wp.media.frames.file_frame = wp.media({
						title: $(this).data('uploader-title'),
						library: {
							type: ['image']
						},
						button: {
							text: $(this).data('uploader-button-text')
						},
						multiple: false
					});

					file_frame.on( 'select', function() {
						var attachment = file_frame.state().get('selection').first().toJSON(),
							$preview = $('.tf_background_img_wrap', $container),
							$close = $('.remove-image', $preview ),
							$imgPre = $('img', $preview),
							$imgPreview;

						setup_data.id = attachment.id;
						setup_data.src = attachment.url;

						if ( attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url ) {
							setup_data.thumb = attachment.sizes.thumbnail.url;
						} else {
							setup_data.thumb = attachment.url;
						}

						control._updateField( setup_data, setting_key, $field );
						
						$imgPreview = $('<a href="#" class="remove-image ti-close"></a><img src="' + setup_data.thumb + '" />').css('display', 'inline-block');

						if( $close.length > 0 ) {
							$close.remove();
						}
						if( $imgPre.length > 0 ) {
							$imgPre.remove();
						}

						$preview.append( $imgPreview ).fadeIn();
					});

					file_frame.open();
				});

				// Remove image
				$('.tf_background_img_wrap', $container).on('click', '.remove-image', function(e){
					e.preventDefault();
					setup_data.id = '';
					setup_data.src = '';
					control._updateField( setup_data, setting_key, $field );
					$(this).next().remove();
					$(this).remove();
				});
			});
		},

		noImage: function($objects, hideSelector) {
			var control = this;

			$objects.each(function(){
				var $obj = $(this),
					$container = $obj.closest('.tf_property_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					setting_key = $field.data('styling-setting-link'),
					setup_data = {},
					$hideElements = $(hideSelector, $container);

				$obj.on('click', function() {
					var $self = $(this);
					if ( $self.prop('checked') ) {
						setup_data.noimage = 'noimage';
						$hideElements.stop().slideUp();
					} else {
						setup_data.noimage = '';
						$hideElements.stop().slideDown();
					}
					control._updateField( setup_data, setting_key, $field );
				});
				if ( $obj.prop('checked') ) {
					$hideElements.hide();
				}
			});
		},

		transparent: function($objects) {
			var control = this;

			$objects.each(function(){
				var $obj = $(this),
					$container = $obj.closest('.tf_property_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					setting_key = $field.data('styling-setting-link'),
					setup_data = {};

				$obj.on('click', function(){
					var $self = $(this);
					if ( $self.prop('checked') ) {
						setup_data.transparent = 'transparent';
						$container.find('.tf_custom_color_wrap').addClass('transparent');
					} else {
						setup_data.transparent = '';
						$container.find('.tf_custom_color_wrap').removeClass('transparent');
					}
					control._updateField( setup_data, setting_key, $field );
				});
				if ( $obj.prop('checked') ) {
					$container.find('.tf_custom_color_wrap').addClass('transparent');
				}
			});
		},

		pickColor: function() {
			var control = this;

			$( '.color-select', control.$el ).each(function(){
				var $color = $(this),
					$container = $color.closest('.tf_property_wrap'),
					$color_wrapper = $color.closest('.tf_custom_color_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					$removeColor = $('.remove-color', $color_wrapper),
					setting_key = $field.data('styling-setting-link'),
					setup_data = {};

				// color picker
				$color.minicolors( {
					theme: 'tf_styling_panel',
					defaultValue: '',
					letterCase: 'lowercase',
					opacity: true,
					change: function(hex, opacity) {
						setup_data.color = hex.replace( '#', '' );
						setup_data.opacity = opacity;
						$removeColor.show();
						control._updateField( setup_data, setting_key, $field );
					},
					hide: function() {
						if ( '' !== $(this).val() ) {
							setup_data.color = $(this).val().replace( '#', '' );
							$removeColor.show();
						} else {
							setup_data.color = '';
							$removeColor.hide();
						}
						control._updateField( setup_data, setting_key, $field );
					}
				});
				// Don't display color picker when user clicks the input field
				$(document).off( 'focus.minicolors', '.minicolors-input' );
				// Don't (show and) hide the color picker if input had focus and color swatch was clicked
				$(document).off( 'blur.minicolors', '.minicolors-input' );
				// Trigger update when user interacts with input field
				$color.on( 'keyup.themifyflow blur.themifyflow input.themifyflow paste.themifyflow', function(e){
					var $color = $(e.target),
						$container = $color.closest('.tf_property_wrap'),
						$field = $('.tf_styling_panel_value_field', $container);
					if ( '' !== $(e.target).val() ) {
						setup_data.color = $color.val().replace( '#', '' );
					} else {
						setup_data.color = '';
					}
					control._updateField( setup_data, setting_key, $field );
				} );

				// clear color picker
				$removeColor.on( 'click', function(e){
					e.preventDefault();
					setup_data.color = '';
					setup_data.opacity = '';
					control._updateField( setup_data, setting_key, $field );
					$color.minicolors( 'value', '' );
					$color.minicolors( 'opacity', '' );
					$removeColor.hide();
				});

			});
			
		},

		pickBorderColor: function() {
			var control = this;

			$('.border-color-select', control.$el).each(function(){
				var $color = $(this),
					$container = $color.closest('.tf_property_wrap'),
					$color_wrapper = $color.closest('.tf_custom_color_wrap'),
					$field = $('.tf_styling_panel_value_field', $container),
					$removeColor = $('.remove-color', $color_wrapper),
					$same = $('.same', $container),
					setting_key = $field.data('styling-setting-link'),
					setup_data = {};

				// Color Picker
				$color.minicolors( {
					theme: 'tf_styling_panel',
					defaultValue: '',
					letterCase: 'lowercase',
					opacity: true,
					change: function(hex, opacity) {
						var side = $(this).data('side'),
							color = hex.replace( '#', '' ),
							exist_data = $.parseJSON( $field.val() ) || {};

						if ( $same.prop('checked') ) {
							setup_data.color = color;
							setup_data.opacity = opacity;
							setup_data.same = 'same';
						} else {
							setup_data[side] = _.extend({}, exist_data[side], {
								'color' : color,
								'opacity' : opacity
							});
							if ( 'top' === side ) {
								setup_data.color = color;
								setup_data.opacity = opacity;
							}
							setup_data.same = '';
						}
						control._updateField( setup_data, setting_key, $field );
						$(this).parent().next().show();
					},
					hide: function() {
						if ( '' !== $(this).val() ) {
							var hex = $(this).val(),
								side = $(this).data('side'),
								color = hex.replace( '#', '' ),
								exist_data = $.parseJSON( $field.val() ) || {};

							if ( $same.prop('checked') ) {
								if ( '' !== color ) {
									setup_data.color = color;
								} else {
									setup_data.color = '';
								}
								setup_data.same = 'same';
							} else {
								setup_data[side] = _.extend({}, exist_data[side], {
									'color' : color
								});
								if ( 'top' === side ) {
									if ( '' !== color ) {
										setup_data.color = color;
									} else {
										setup_data.color = '';
									}
								}
								setup_data.same = '';
							}
							control._updateField( setup_data, setting_key, $field );
							$(this).parent().next().show();
						}
					}
				});

				// Don't display color picker when user clicks the input field
				$(document).off( 'focus.minicolors', '.minicolors-input' );
				// Don't (show and) hide the color picker if input had focus and color swatch was clicked
				$(document).off( 'blur.minicolors', '.minicolors-input' );
				// Trigger update when user interacts with input field
				$color.on( 'keyup.themifyflow blur.themifyflow input.themifyflow paste.themifyflow', function(e){
					var $color = $(e.target),
						$container = $color.closest('.tf_property_wrap'),
						$field = $('.tf_styling_panel_value_field', $container);
					
					var hex = $(this).val(),
						side = $(this).data('side'),
						color = hex.replace( '#', '' ),
						exist_data = $.parseJSON( $field.val() ) || {};

					if ( $same.prop('checked') ) {
						if ( '' !== color ) {
							setup_data.color = color;
							$(this).parent().next().show();
						} else {
							setup_data.color = '';
						}
						setup_data.same = 'same';
					} else {
						setup_data[side] = _.extend({}, exist_data[side], {
							'color' : color
						});
						if ( 'top' === side ) {
							if ( '' !== color ) {
								setup_data.color = color;
								$(this).parent().next().show();
							} else {
								setup_data.color = '';
							}
						}
						setup_data.same = '';
					}

					control._updateField( setup_data, setting_key, $field );
				} );

				// Clear color field
				$removeColor.on( 'click', function(e){
					e.preventDefault();
					var side = $(this).data('side'),
						exist_data = $.parseJSON( $field.val() ) || {};

					if ( $same.prop('checked') ) {
						setup_data.color = '';
						setup_data.opacity = '';
						setup_data.same = 'same';
					} else {
						setup_data = _.extend({}, exist_data[side], {
							'color' : '',
							'opacity' : ''
						});
						if ( 'top' === side ) {
							setup_data.color = '';
							setup_data.opacity = '';
						}
						setup_data.same = '';
					}
					control._updateField( setup_data, setting_key, $field );
					var $color = $(this).siblings('.minicolors').children('.border-color-select');
					$color.minicolors( 'value', '' );
					$color.minicolors( 'opacity', '' );
					$(this).hide();
				});

			});
		}

	};

})(jQuery);