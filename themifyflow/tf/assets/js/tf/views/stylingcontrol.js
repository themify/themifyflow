var TF, wp, _, Backbone, _tf_app, _tf_styling, WebFont;

(function($){

	'use strict';

	function updateQueryString(a,b,c){
		c||(c=window.location.href);var d=RegExp("([?|&])"+a+"=.*?(&|#|$)(.*)","gi");if(d.test(c))return b!==void 0&&null!==b?c.replace(d,"$1"+a+"="+b+"$2$3"):c.replace(d,"$1$3").replace(/(&|\?)$/,"");if(b!==void 0&&null!==b){var e=-1!==c.indexOf("?")?"&":"?",f=c.split("#");return c=f[0]+e+a+"="+b,f[1]&&(c+="#"+f[1]),c}return c;
	}

	// Checks if a parameter exists in the current URL and return its value. Returns false otherwise.
	function getParameterByName( name ) {
		name = name.replace( /[\[]/, "\\[" ).replace(/[\]]/, "\\]" );
		var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
		    results = regex.exec( window.location.search );
		return results === null ? false : decodeURIComponent( results[1].replace( /\+/g, ' ' ) );
	}

	function removeStylingQueryString() {
		var url = updateQueryString( 'tf', null, window.location.href );
		url = updateQueryString( 'tf_global_styling', null, url );
		url = updateQueryString( 'tf_custom_css', null, url );
		return url;
	}

	// Make sure the styles in preview are not applied to Flow UI elements or Admin Bar
	function excludeTF( $selector ) {
		return $selector.filter(function(){
			// works if the class is in the current element or an ancestor
			return 0 === $(this).closest('.tf_interface, #wpadminbar').length;
		});
	}

	// Capitalize first character
	function capitalize1st( text ) {
		var string = String( text ); // make sure it's a string
		return string.charAt(0).toUpperCase() + string.slice(1);
	}

	// Checks if the variant is a font weight. Returns the weight or empty.
	function getFontWeight( variant ) {
		variant = variant.replace( 'italic', '' );
		if ( ['100', '200', '300', '400', '500', '600', '700', '800', '900'].indexOf(variant) !== -1 ) {
			return variant;
		}
		return '';
	}

	// Google Font Loader
	var wf = document.createElement( 'script' );
		wf.src = ('https:' === document.location.protocol ? 'https' : 'http') + '://ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js';
		wf.type = 'text/javascript';
		wf.async = 'true';
		var s = document.getElementsByTagName( 'script' )[0];
		s.parentNode.insertBefore( wf, s );

	// Setup font family, styles, size and eveything
	function setFont( $selector, font ) {
		var initialSelector = $selector.selector;
		$selector = excludeTF( $selector );
		$selector.css( {
			'font-size' : '',
			'font-family' : '',
			'font-weight' : '',
			'font-style' : '',
			'text-transform' : '',
			'text-decoration' : '',
			'text-align' : '',
			'line-height' : ''
		} );

		if ( font ) {
			if ( ! font.nostyle || '' == font.nostyle ) {
				if ( font.bold && '' != font.bold ) {
					$selector.css( 'font-weight', 'bold' );
				} else {
					stripStyle( initialSelector, 'font-weight' );
				}

				if ( font.italic && '' != font.italic ) {
					$selector.css( 'font-style', 'italic' );
				} else {
					stripStyle( initialSelector, 'font-style' );
				}

				if ( ( font.linethrough && '' != font.linethrough ) || ( font.underline && '' != font.underline ) ) {
					if ( font.linethrough && '' != font.linethrough ) {
						$selector.css( 'text-decoration', 'line-through' );
					}
					if ( font.underline && '' != font.underline ) {
						$selector.css( 'text-decoration', 'underline' );
					}
				} else {
					stripStyle( initialSelector, 'text-decoration' );
				}
				
			} else {
				$selector.css( {
					'font-weight' : 'normal',
					'font-style' : 'normal',
					'text-decoration' : 'none'
				} );
			}

			if ( font.family && '' != font.family ) {
				var family = ( 'string' === typeof font.family ) ? JSON.parse( font.family ) : font.family;
				if ( family.fonttype && 'google' === family.fonttype ) {
					var googleFont = family.name;
					if ( family.variant ) {
						googleFont = family.name + ':' + family.variant;
						if ( 'regular' !== family.variant && '400' !== family.variant ) {
							if ( family.variant.indexOf( 'italic' ) !== -1 ) {
								$selector.css( 'font-style', 'italic' );
							}
							var variantWeight = getFontWeight( family.variant );
							if ( '' !== variantWeight ) {
								$selector.css( 'font-weight', variantWeight );
							}
						}
					}
					WebFont.load({
						google: {
							families: [googleFont]
						}
					});
				}
				$selector.css( 'font-family', family.name );
			} else {
				stripStyle( initialSelector, 'font-family' );
			}

			if ( font.texttransform && '' != font.texttransform ) {
				if ( 'notexttransform' != font.texttransform ) {
					$selector.css( 'text-transform', font.texttransform );
				} else {
					$selector.css( 'text-transform', 'none' );
				}
			} else {
				stripStyle( initialSelector, 'text-transform' );
			}

			if ( font.align && '' != font.align ) {
				if ( 'noalign' != font.align ) {
					$selector.css( 'text-align', font.align );
				} else {
					if ( '' == themifyCustomizer.isRTL ) {
						$selector.css( 'text-align', 'left' );
					} else {
						$selector.css( 'text-align', 'right' );
					}
				}
			} else {
				stripStyle( initialSelector, 'text-align' );
			}

			var unit = 'px';

			if ( font.sizenum && '' != font.sizenum ) {
				unit = ( font.sizeunit && '' != font.sizeunit ) ? font.sizeunit : 'px';
				$selector.css( 'font-size', font.sizenum + unit );
			} else {
				stripStyle( initialSelector, 'font-size' );
			}

			if ( font.linenum && '' != font.linenum ) {
				unit = ( font.lineunit && '' != font.lineunit ) ? font.lineunit : 'px';
				$selector.css( 'line-height', font.linenum + unit );
			} else {
				stripStyle( initialSelector, 'line-height' );
			}
		}
	}

	// Set border properties. @uses setColor().
	function setBorder( $selector, property, borderSide ) {
		var initialSelector = $selector.selector;
		$selector = excludeTF( $selector );

		// Border Style
		if ( borderSide.style && 'none' != borderSide.style ) {
			$selector.css( property + '-style', borderSide.style );
		} else {
			$selector.css( property + '-style', 'none' );
			stripStyle( initialSelector, property + '-style' );
		}

		// Border Width
		if ( borderSide.width && '' != borderSide.width ) {
			$selector.css( property + '-width', borderSide.width + 'px' );
		} else {
			stripStyle( initialSelector, property + '-width' );
		}
		
		// Border Color
		setColor( $selector, property + '-color', borderSide );
	}

	// Convert hexadecimal color to RGB. Receives string and returns object
	function hexToRgb(hex) {
	    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
	    return result ? {
	        r: parseInt(result[1], 16),
	        g: parseInt(result[2], 16),
	        b: parseInt(result[3], 16)
	    } : null;
	}

	// Set color in hexadecimal format and also rgba if opacity is set.
	function setColor( $selector, property, values ) {
		var initialSelector = $selector.selector;
		$selector = excludeTF( $selector );
		if ( ! _.isUndefined( values.transparent ) && 'transparent' == values.transparent) {
			$selector.css(property, 'transparent' );
		} else {
			if ( ! _.isUndefined( values.color ) ) {
				if ( values.color && '' !== values.color  ) {
					var rgb = hexToRgb( values.color ),
						alpha = values.opacity ? values.opacity : '1';
					$selector.css(property, '#' + ( values.color ) );
					if ( null !== rgb && rgb.r && rgb.g && rgb.a && alpha ) {
						$selector.css(property, 'rgba( ' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + alpha + ' )' );
					}
				} else {
					$selector.css(property, '' );
					stripStyle( initialSelector, property );
				}
			}
		}
	}

	// Set dimension by side, like padding or margin.
	function setDimension( $selector, property, side ) {
		var initialSelector = $selector.selector;
		$selector = excludeTF( $selector );
		// Check if auto was set
		if ( side.auto && 'auto' == side.auto ) {
			$selector.css( property, side.auto );
		} else {
			// Prepare unit
			var unit = 'px';
			if ( side.unit && 'px' != side.unit ) {
				unit = side.unit;
			}
			// Dimension Width
			if ( side.width && '' != side.width ) {
				$selector.css( property, side.width + unit );
			} else {
				stripStyle( initialSelector, property );
			}
		}
	}

	function stripStyle( selector, property ) {
		var stylesheets = document.styleSheets, previewStyle;
		
		if ( _.isUndefined( window.tfStylePreview ) ) {
			for ( var i = 0; i < stylesheets.length; i++ ) {
				if ( 'tf-style-preview' === stylesheets[i].ownerNode.id ) {
					window.tfStylePreview = stylesheets[i];
					break;
				}
			}
		}
		previewStyle = window.tfStylePreview;

		var rules = previewStyle.cssRules? previewStyle.cssRules: previewStyle.rules
		for ( var i = 0; i < rules.length; i++ ) {
			if ( rules[i].selectorText.toLowerCase() == selector ) {
				rules[i].style[property] = '';
				break;
			}
		}
	}

	TF.Views.StylingControl = Backbone.View.extend({

		tagName: 'div',

		className: 'tf_styling_panel tf_interface',

		template: wp.template( 'tf_styling_panel' ),

		currentControlModel: {},

		$currentSelector: '',

		bindPropertyChange: function( model ) {
			this.listenTo( model, 'change:tf_font_properties', this.changeFontProperty, this );
			this.listenTo( model, 'change:tf_border_properties', this.changeBorderProperty, this );
			this.listenTo( model, 'change:tf_background_properties', this.changeBackgroundProperty, this );
			this.listenTo( model, 'change:tf_padding_properties', this.changePaddingProperty, this );
			this.listenTo( model, 'change:tf_margin_properties', this.changeMarginProperty, this );
			this.listenTo( model, 'change:tf_width_properties', this.changeWidthProperty, this );
			this.listenTo( model, 'change:tf_height_properties', this.changeHeightProperty, this );
			this.listenTo( model, 'change:tf_min-width_properties', this.changeMinWidthProperty, this );
			this.listenTo( model, 'change:tf_max-width_properties', this.changeMaxWidthProperty, this );
			this.listenTo( model, 'change:tf_min-height_properties', this.changeMinHeightProperty, this );
			this.listenTo( model, 'change:tf_position_properties', this.changePositionProperty, this );
			this.listenTo( model, 'change:tf_float_properties', this.changeFloatProperty, this );
			this.listenTo( model, 'change:tf_opacity_properties', this.changeOpacityProperty, this );
			this.listenTo( model, 'change:tf_z-index_properties', this.changeZIndexProperty, this );
			this.listenTo( model, 'change:tf_customcss_properties', this.changeCustomCSSProperty, this );
		},

		events: {
			'click [data-tf-style-selector]': 'setActiveSelector',
			'click .tf_btn_save' : 'save',
			'click .tf_btn_cancel' : 'close',
			'change .tf_styling_panel_value_field' : 'updateControlModel',
			'update_attribute .tf_styling_panel_value_field' : 'updateAttribute',
			'click .tf_btn_clear' : 'clear',
			'click .tf_toggle_property_section' : 'togglePanel',
			'click .tf_expand_section' : 'expandSection',
			'click .tf_styling_basic' : 'stylingBasic',
			'click .tf_styling_all' : 'stylingAll',
			'click .tf_list_has_child > .tf_element_list_title' : 'expandTargetElements',
		},

		initialize: function(){
			var $self = this;

			// Add controls to panel
			this.render();

			// Construct existing control model
			_.each( this.model.get('settings'), function( val ){
				var temp = new TF.Models.Control( { SettingKey: val.SettingKey });
				$self.bindPropertyChange( temp );
				_.each( val, function( value, prop ){
					if ( prop !== 'SettingKey') {
						temp.set( prop, value, {silent: true});
					}
				});
				$self.collection.add( temp, {merge: true});
				$self.model.set( 'settings', $self.collection );

			});
			
			// Bind events to controls
			this.ready();

			// Set initial active selector
			TF.Instance.builder.on('tf_live_preview', function(){
				$self.$currentSelector = $self._getCurrentSelector();
			});

		},

		// Uses templates defined in tmpl-styling-panel.php
		render: function() {
			var $html = this.$el.html(this.template());
			
			$html.appendTo('body');

			_.each( _tf_styling.controls, function( data ) {
				var templateName = 'tf_styling_' + data.type,
					cTemplate = wp.template( templateName );

				if ( ! _.isUndefined( _tf_styling.core[data.type] ) ) {
					_.extend( data, _tf_styling.core[data.type] );
				}

				try {
					$html.find( '#' + data.name + '_control' ).find( '.tf_property_wrap' ).append( cTemplate( data ) ).find( "select" ).wrap ( "<div class='tf_custom_select'></div>" );
				} catch( e ) {
					console.log( 'Error: ' + templateName, e );
				}
			} );

			/* Styling Panel Script **/
			if ( ! $('body').hasClass( 'touch' ) ) {
				$('.tf_target_elements').niceScroll();
				$('.tf_css_properties').niceScroll();
			}
		},

		renderSelectorPanel: function() {
			this.model.set( { 
				ID: TF.Instance.styleModel.get('uniqid'), 
				module: TF.Instance.styleModel.get('module'), 
				styling_context: TF.Instance.styleModel.get('styling_context'),
				mode: TF.Instance.styleModel.get('mode')
			} );

			this.$el.find('.tf_target_elements').html( TF.Instance.styleModel.get('selectors_html_section') );

			// Slide down first selector group
			var $nested = $('.tf_list_has_child').eq(0);
			if ( $nested.length > 0 ) {
				$nested.children('.tf_element_list_title').trigger('click');
			} else {
				this.$el.find('.tf_target_elements [data-tf-style-selector]').first().trigger('click'); // set default selector
			}
		},

		setActiveSelector: function( e ) {
			var $self = this,
				$this = $(e.currentTarget),
				selector = $this.data('tf-style-selector'),
				key = $this.data('tf-style-selector-key'),
				chain = $this.data('tf-chain-with-context');

			this.model.set( 'tf_active_selector', selector );
			this.model.set( 'tf_active_selector_key', key );
			this.model.set( 'tf_chain_with_context', chain );
			this.$currentSelector = this._getCurrentSelector();

			this.firstTime = true;

			console.log( this.model.has('settings'), 'model has settings');

			if ( ! this.model.has('settings') || ! _.isObject( this.collection.get( key ) ) ) {
				console.log('new setings created');
				
				$('.tf_styling_panel_value_field', this.$el).val('');

				var model = new TF.Models.Control( { SettingKey: key });
				this.bindPropertyChange( model );

				this.collection.add( model, {merge: true} );
				this.model.set( 'settings', this.collection );
			}
			
			$('.tf_elements_list_selected', this.$el).removeClass('tf_elements_list_selected');
			$this.parent().addClass('tf_elements_list_selected');

			this.currentControlModel = this._getCurrentControlModel();

			$('.tf_styling_panel_value_field', this.$el).val('').trigger('update_attribute');
			_.each( this.currentControlModel.toJSON(), function( value, key ){
				if ( 'SettingKey' != key ) {
					$('[data-styling-setting-link="'+ key +'"]', $self.$el).val( value );
				}
			});

			// Manage Basic and All tabs
			if ( _.isUndefined( this.currentControlModel.activeTab ) ) {
				var itemToStore = this.sanitizeHostName( 'activeTab_' + window.location.host );
				if ( this.firstTime && null !== localStorage.getItem( itemToStore ) ) {
					$( localStorage.getItem( itemToStore ) ).trigger( 'click' );
					this.firstTime = false;
				} else {
					this.currentControlModel.activeTab = '.tf_styling_basic';
					$('.tf_styling_basic').show();
				}
			}
			$( this.currentControlModel.activeTab ).trigger( 'click' );
		},

		togglePanel: function(e){
			var $self = $(e.target);
			$self.next('.tf_property_wrap').stop().slideToggle().promise().done(function(){
				$self.parent().toggleClass('tf_properties_list_expanded');
				if ( 'object' === typeof $('.tf_css_properties').getNiceScroll() ) {
					$('.tf_css_properties').getNiceScroll().resize();
				}
			});
		},

		expandSection: function(e){
			$('body').toggleClass( 'expand-' + $( e.target ).data('expand') );
		},

		expandTargetElements: function(e) {
			// Add tf_elements_list_expanded
			var $self = $(e.target);
			$self.parent().toggleClass( 'tf_elements_list_expanded' ).siblings().each(function(){
				$(this).removeClass( 'tf_elements_list_expanded' ).children('.tf_element_list_title').next().slideUp();
			});
			$self.next().slideToggle(function(){
				if ( 'object' === typeof $('.tf_target_elements').getNiceScroll() ) {
					$('.tf_target_elements').getNiceScroll().resize();
				}
				$(this).find('li').eq(0).find('span').trigger( 'click' );
			});
		},

		stylingBasic: function(e){
			this._switchStylingTab( 'basic', $(e.target) );
		},

		stylingAll: function(e){
			this._switchStylingTab( 'all', $(e.target) );
		},

		_switchStylingTab: function( mode, $tab ){
			this.currentControlModel.activeTab = '.tf_styling_' + mode;
			var itemToStore = this.sanitizeHostName( 'activeTab_' + window.location.host );
			localStorage.setItem( itemToStore, '.tf_styling_' + mode );
			$tab.addClass( 'tf_active' ).siblings().removeClass( 'tf_active' );
			var basicList = $('[data-tf-style-selector="' + this.model.get( 'tf_active_selector' ) + '"]').data( 'tf-basic-styling' );
			if ( 'basic' === mode && ! _.isUndefined( basicList ) ) {
				basicList = basicList.split(',');
				$('.tf_styling_basic').show();
				if ( basicList.length > 0 ) {
					var excludeList = [];
					_.each( basicList, function( value ){
						excludeList.push( '#tf_' + value + '_properties_control' );
					} );
					excludeList = excludeList.join(',');
					$('.tf_properties_list > li').removeClass( 'hide-control' ).not( excludeList ).addClass( 'hide-control' );
				}
			} else {
				// There is no Basic whitelist or user clicked All tab so we show the full list
				if ( _.isUndefined( basicList ) ) {
					$('.tf_styling_basic').hide();
					$('.tf_styling_all').addClass( 'tf_active' );
					this.currentControlModel.activeTab = '.tf_styling_all';
				} else {
					$('.tf_styling_basic').show();
					$('.tf_properties_list > li').removeClass( 'hide-control' );
				}
			}
			this._resizeScroll();
		},

		save: function( e ) {
			// This function can be called through a click or from TF.Views.Builder.moduleStyle
			if ( ! _.isUndefined( e ) ) {
				e.preventDefault();
			}
			if ( 'module' == this.model.get('mode') ) {
				var $self = this;
				TF.Instance.elementsCollection.add( this.model, {merge: true} );
				console.log(TF.Instance.elementsCollection.toJSON(), 'json');
				TF.Instance.loader.show();
				wp.ajax.post( 'tf_generate_temp_stylesheet', {
					data_styling: JSON.stringify( [ this.model.toJSON() ] ),
					nonce: _tf_app.nonce
				}).done(function( response ){
					if ( ! _.isEmpty( response ) ) {
						_.each( response, function( value, key ){
							var oldCss = $('#tf-template-temp-'+ key +'-css');
							if ( oldCss.length ) {
								oldCss.after( value );
								oldCss.remove();
							} else {
								$('head').append( value );
							}
						});
					}
					TF.Instance.loader.hide();
					$self.$el.hide();
					if ( ! TF.Instance.builder.slidePanelOpen ) {
						$('#tf_module_panel .tf_slide_builder_module_panel').trigger('click');
					}
				});
			} else {
				this._saveGlobalStyling();
			}
			// Remove class in body since styling panel is now hidden
			this.toggleStylingPanel( 'hidden' );
		},

		_saveGlobalStyling: function() {
			TF.Instance.loader.show();
			var $self = this, globalStyling = JSON.stringify( $self.model.toJSON() );
			globalStyling = globalStyling.replace( /\\\\n/ig, '\\n' ).replace( /\\\\t/ig, '\\t' ); 
			 
			//globalStyling = globalStyling.replace( /content(\s*?)\:(\s*?)(\"|\')(\\+)(.*?)(\"|\')/g, ': $3\\\\\\$5$6' );

			console.log('Styling Data', globalStyling );
			
			wp.ajax.post( 'tf_save_global_styling', {
				data_styling: globalStyling,
				layout_id: _tf_app.layout_id,
				nonce: _tf_app.nonce
			}).done(function( response ){
				console.log( response );
				TF.Instance.loader.hide();
				$self.$el.hide();
				if ( ! TF.Instance.builder.slidePanelOpen ) {
					$('#tf_module_panel .tf_slide_builder_module_panel').trigger('click');
				}
				window.location.href = removeStylingQueryString();
			});
		},

		updateControlModel: function( event ) {
			var $this = $(event.currentTarget),
				fieldName = $this.data('styling-setting-link'),
				values = $this.val();
			
			this.currentControlModel.set( fieldName, values );
		},

		_updateField: function( setup_data, field_key, $field ) {
			if ( 'tf_customcss_properties' !== field_key ) {
				var prev_data = $.parseJSON( this.currentControlModel.get( field_key ) || "null" ) || {};
			} else {
				var prev_data = this.currentControlModel.get( field_key ) || "null";
				prev_data = prev_data.replace('{"css":"', '').replace('"}', '');
				prev_data = JSON.stringify( prev_data );
				prev_data = '{"css":' + this.cleanCustomCSS( prev_data ) + '}';
				prev_data = $.parseJSON( prev_data ) || {};
			}	
			$field.val( JSON.stringify( _.extend( prev_data, setup_data ) ) ).trigger('change');
			console.log( JSON.stringify( _.extend( prev_data, setup_data ) ), '_updateField stringify' );
		},

		updateAttribute: function( event ){
			var $self = this,
				$this = $(event.currentTarget),
				$container = $this.closest('.tf_property_wrap'),
				prop = $this.data('styling-setting-link'),
				jsonData = this.currentControlModel.get( prop );

			if ( 'tf_customcss_properties' !== prop ) {
				var value = $.parseJSON( jsonData || "null" ) || {};
			}

			switch( prop ) {
				case 'tf_font_properties':
					$('.font_size_num', $container).val( '' );
					$('.tf_font_family', $container).val( '' );
					$('.tf_font_variant', $container).val( '' );
					$('.font_size_unit', $container).val( 'px' );
					$('.font_line_num', $container).val( '' );
					$('.font_line_unit', $container).val( 'px' );
					$('.color-select', $container).minicolors( 'value', '' );
					$('.color-select', $container).minicolors( 'opacity', '' );
					
					if ( ! _.isUndefined(value.sizenum) ) {
						$('.font_size_num', $container).val( value.sizenum );
					}
					if ( ! _.isUndefined(value.family) ) {
						var fontData = $('.tf_font_family option[data-name="' + value.family.name + '"]', $container).val(),
							fontDataObj = $.parseJSON( fontData ) || {};
						$('.tf_font_family', $container).val( fontData );
						var $variant = $('.tf_font_variant', $container);

						if ( 'google' !== value.family.fonttype ) {
							$variant.hide();
						} else {
							$variant.show();
						}
						$variant.empty();
						if ( ! _.isUndefined( fontDataObj.variant ) ) {
							var variants = 'string' === typeof fontDataObj.variant ? fontDataObj.variant.split(',') : fontDataObj.variant,
								selected;
							_.each( variants, function( item ){
								$variant.append( '<option value="' + item + '">' + capitalize1st( item ) + '</option>' );
							});
						}
						$variant.val( value.family.variant );
					}
					if ( ! _.isUndefined(value.sizeunit) ) {
						$('.font_size_unit', $container).val( value.sizeunit );
					}
					if ( ! _.isUndefined(value.linenum) ) {
						$('.font_line_num', $container).val( value.linenum );
					}
					if ( ! _.isUndefined(value.lineunit) ) {
						$('.font_line_unit', $container).val( value.lineunit );
					}
					if ( ! _.isUndefined(value.color) ) {
						$('.color-select', $container).minicolors( 'value', value.color );
						$('.color-select', $container).minicolors( 'opacity', value.opacity );
					}

					var font_styles = ['italic', 'bold', 'underline', 'linethrough', 'nostyle'];

					_.each( font_styles, function( style ){
						if ( ! _.isUndefined( value[style] ) && '' !== value[style] ) {
							$('[data-style="'+ style +'"]', $container).addClass('tf_active');
						} else {
							$('[data-style="'+ style +'"]', $container).removeClass('tf_active');
						}
					});

					if ( ! _.isUndefined(value.texttransform) ) {
						$('[data-texttransform="'+ value.texttransform +'"]', $container).addClass('tf_active').siblings().removeClass('tf_active');
					} else {
						$('[data-texttransform]', $container).removeClass('tf_active');
					}

					if ( ! _.isUndefined(value.align) ) {
						$('[data-align="'+ value.align +'"]', $container).addClass('tf_active').siblings().removeClass('tf_active');
					} else {
						$('[data-align]', $container).removeClass('tf_active');
					}

				break;

				case 'tf_background_properties':
					$('.image-position-style', $container).val( '' );
					$('.disable-control', $container).val( '' );
					$('.color-select', $container).minicolors( 'value', '' );
					$('.color-select', $container).minicolors( 'opacity', '' );
					
					$('.color-transparent', $container).val( '' );

					if ( ! _.isUndefined(value.style) ) {
						$('.image-style', $container).val( value.style );
					} else {
						$('.image-style', $container).val( 'fullcover' ).trigger('change');
					}
					if ( ! _.isUndefined(value.position) ) {
						$('.image-position-style', $container).val( value.position );
					}
					if ( ! _.isUndefined(value.noimage) ) {
						$('.disable-control', $container).val( value.noimage );
					}
					if ( ! _.isUndefined(value.transparent) ) {
						$('.color-transparent', $container).val( value.transparent );
					}
					if ( ! _.isUndefined(value.color) ) {
						$('.color-select', $container).minicolors( 'value', value.color );
						$('.color-select', $container).minicolors( 'opacity', value.opacity );
					}
					if ( ! _.isUndefined(value.thumb) || ! _.isUndefined(value.src) ) {
						var $preview = $('.tf_background_img_wrap', $container),
							$close = $('.remove-image', $preview ),
							$imgPre = $('img', $preview),
							$imgPreview;

						if ( _.isUndefined(value.thumb) ) {
							value.thumb = value.src;
						}
						
						$imgPreview = $('<a href="#" class="remove-image ti-close"></a><img src="' + value.thumb + '" />').css('display', 'inline-block');
						
						if( $close.length > 0 ) {
							$close.remove();
						}
						if( $imgPre.length > 0 ) {
							$imgPre.remove();
						}
						
						$preview.append( $imgPreview ).fadeIn();
					}
				break;

				case 'tf_border_properties':
					var sides = ['top', 'right', 'bottom', 'left'];
					_.each(sides, function(side){
						if ( ! _.isUndefined( value[side] ) ) {
							$('.border-style[data-side="'+side+'"]', $container).val( value[side].style );
							if ( ! _.isUndefined( value[side].color ) ) {
								if ( $('.border-color-select[data-side="'+ side +'"]', $container).data( 'minicolors-initialized' ) ) {
									$('.border-color-select[data-side="'+ side +'"]', $container).minicolors( 'value', value[side].color );
									$('.border-color-select[data-side="'+ side +'"]', $container).minicolors( 'opacity', value[side].opacity );
								}
							}
							if ( ! _.isUndefined( value[side].width ) ) {
								$('.dimension-width[data-side="'+ side +'"]', $container).val( value[side].width );
							}
						} else {
							$('.border-style[data-side="'+side+'"]', $container).val('');
							$('.border-color-select[data-side="'+ side +'"]', $container).val('').data('opacity', '1');
							if ( $('.border-color-select[data-side="'+ side +'"]', $container).data( 'minicolors-initialized' ) ) {
								$('.border-color-select[data-side="'+ side +'"]', $container).minicolors( 'value', '' );
								$('.border-color-select[data-side="'+ side +'"]', $container).minicolors( 'opacity', '' );
							}
							$('.dimension-width[data-side="'+ side +'"]', $container).val( '' );
						}
					});

					var $sameLabel = $container.find('.same-label');
					if ( ! _.isUndefined( value.same ) && 'same' === value.same ) {
						$('.border-style[data-side="top"]', $container).val( value.style );
						if ( ! _.isUndefined( value.color ) ) {
							if ( $('.border-color-select[data-side="top"]', $container).data( 'minicolors-initialized' ) ) {
								$('.border-color-select[data-side="top"]', $container).minicolors( 'value', value.color );
								$('.border-color-select[data-side="top"]', $container).minicolors( 'opacity', value.opacity );
							}
						}
						if ( ! _.isUndefined( value.width ) ) {
							$('.dimension-width[data-side="top"]', $container).val( value.width );
						}
						$container.find('.component').hide();
						$container.find('.dimension-row-label:not(.same-label)').hide();
						$sameLabel.text( $sameLabel.data('same') );
						$container.find('.same').prop('checked', true);
					} else {
						$container.find('.component').show();
						$container.find('.dimension-row-label:not(.same-label)').show();
						$sameLabel.text( $sameLabel.data('notsame') );
						$container.find('.same').prop('checked', false);
					}
				break;

				case 'tf_padding_properties':
					var sides = ['top', 'right', 'bottom', 'left'];
					_.each(sides, function(side){
						if ( ! _.isUndefined( value[side] ) ) {
							if ( ! _.isUndefined( value[side].width ) ) {
								$('.dimension-width[data-side="'+ side +'"]', $container).val( value[side].width );
								if ( _.isUndefined( value[side].unit ) ) {
									value[side].unit = 'px';
								}
								$('.dimension-unit[data-side="'+ side +'"]', $container).val( value[side].unit );
							}
						} else {
							$('.dimension-width[data-side="'+ side +'"]', $container).val( '' );
							$('.dimension-unit[data-side="'+ side +'"]', $container).val( 'px' );
						}
					});
					var $sameLabel = $container.find('.same-label');
					if ( ! _.isUndefined( value.same ) && 'same' === value.same ) {
						$container.find('.component').hide();
						$container.find('.dimension-row-label:not(.same-label)').hide();
						$sameLabel.text( $sameLabel.data('same') );
						$container.find('.same').prop('checked', true);
					} else {
						$container.find('.component').show();
						$container.find('.dimension-row-label:not(.same-label)').show();
						$sameLabel.text( $sameLabel.data('notsame') );
						$container.find('.same').prop('checked', false);
					}
					if ( ! _.isUndefined( value.width ) ) {
						if ( _.isUndefined( value.unit ) ) {
							$('.useforall', $container).find( '.dimension-unit' ).val( 'px' );
						} else {
							$('.useforall', $container).find( '.dimension-unit' ).val( value.unit );
						}
						$('.useforall', $container).find('.dimension-width').val( value.width );
					}
				break;

				case 'tf_margin_properties':
					var sides = ['top', 'right', 'bottom', 'left'];
					
					_.each(sides, function(side){
						if ( ! _.isUndefined( value[side] ) ) {
							if ( ! _.isUndefined( value[side].width ) ) {
								$('.dimension-width[data-side="'+ side +'"]', $container).val( value[side].width );
								if ( _.isUndefined( value[side].unit ) ) {
									value[side].unit = 'px';
								}
								$('.dimension-unit[data-side="'+ side +'"]', $container).val( value[side].unit );
							}
							if ( ! _.isUndefined( value[side].auto ) && 'auto' === value[side].auto ) {
								$('.auto-prop-multi[data-side="'+ side +'"]', $container).prop('checked', true);
								$container.find('.dimension-width[data-side="' + side + '"]').closest('.tf_property_col_left').addClass('hide-x-on');
							} else {
								$('.auto-prop-multi[data-side="'+ side +'"]', $container).prop('checked', false);
								$container.find('.dimension-width[data-side="' + side + '"]').closest('.tf_property_col_left').removeClass('hide-x-on');
							}
						} else {
							$('.dimension-width[data-side="'+ side +'"]', $container).val( '' );
							$('.dimension-unit[data-side="'+ side +'"]', $container).val( 'px' );
							$('.auto-prop-multi[data-side="'+ side +'"]', $container).prop('checked', false);
							$container.find('.dimension-width[data-side="' + side + '"]').closest('.tf_property_col_left').removeClass('hide-x-on');
						}
						
					});
					var $sameLabel = $container.find('.same-label');
					if ( ! _.isUndefined( value.same ) && 'same' === value.same ) {
						$container.find('.component').hide();
						$container.find('.dimension-row-label:not(.same-label)').hide();
						$sameLabel.text( $sameLabel.data('same') );
						$container.find('.same').prop('checked', true);
					} else {
						$container.find('.component').show();
						$container.find('.dimension-row-label:not(.same-label)').show();
						$sameLabel.text( $sameLabel.data('notsame') );
						$container.find('.same').prop('checked', false);
					}
					if ( ! _.isUndefined( value.width ) ) {
						if ( _.isUndefined( value.unit ) ) {
							$('.useforall', $container).find( '.dimension-unit' ).val( 'px' );
						} else {
							$('.useforall', $container).find( '.dimension-unit' ).val( value.unit );
						}
						$('.useforall', $container).find('.dimension-width').val( value.width );
					}
				break;

				case 'tf_width_properties':
				case 'tf_height_properties':
					if ( ! _.isUndefined( value ) ) {
						if ( ! _.isUndefined( value.width ) ) {
							$('.dimension-width-single', $container).val( value.width );
							if ( _.isUndefined( value.unit ) ) {
								value.unit = 'px';
							}
							$('.dimension-unit-single', $container).val( value.unit );
						} else {
							$('.dimension-width-single', $container).val( '' );
							$('.dimension-unit-single', $container).val( 'px' );
						}
						if ( ! _.isUndefined( value.auto ) && 'auto' === value.auto ) {
							$('.auto-prop', $container).prop('checked', true);
							$container.find('.dimension-width-single').closest('.tf_property_col_left').addClass('hide-x-on');
						} else {
							$('.auto-prop', $container).prop('checked', false);
							$container.find('.dimension-width-single').closest('.tf_property_col_left').removeClass('hide-x-on');
						}
					} else {
						$('.dimension-width-single', $container).val( '' );
						$('.dimension-unit-single', $container).val( 'px' );
						$('.auto-prop', $container).prop('checked', false);
						$container.find('.dimension-width-single').closest('.tf_property_col_left').removeClass('hide-x-on');
					}
				break;

				case 'tf_min-width_properties':
				case 'tf_max-width_properties':
				case 'tf_min-height_properties':
					if ( ! _.isUndefined( value ) ) {
						if ( ! _.isUndefined( value.width ) ) {
							$('.dimension-width-single', $container).val( value.width );
							if ( _.isUndefined( value.unit ) ) {
								value.unit = 'px';
							}
							$('.dimension-unit-single', $container).val( value.unit );
						} else {
							$('.dimension-width-single', $container).val( '' );
							$('.dimension-unit-single', $container).val( 'px' );
						}
					} else {
						$('.dimension-width-single', $container).val( '' );
						$('.dimension-unit-single', $container).val( 'px' );
					}
				break;

				case 'tf_position_properties':
					var sides = ['top', 'right', 'bottom', 'left'];
					_.each(sides, function(side){
						if ( ! _.isUndefined( value[side] ) ) {
							if ( ! _.isUndefined( value[side].width ) ) {
								$('.dimension-width[data-side="'+ side +'"]', $container).val( value[side].width );
								if ( _.isUndefined( value[side].unit ) ) {
									value[side].unit = 'px';
								}
								$('.dimension-unit[data-side="'+ side +'"]', $container).val( value[side].unit );
							}
							if ( ! _.isUndefined( value[side].auto ) && 'auto' === value[side].auto ) {
								$('.auto-prop-multi[data-side="'+ side +'"]', $container).prop('checked', true);
								$container.find('.dimension-width[data-side="' + side + '"]').closest('.tf_property_col_left').addClass('hide-x-on');
							} else {
								$('.auto-prop-multi[data-side="'+ side +'"]', $container).prop('checked', false);
								$container.find('.dimension-width[data-side="' + side + '"]').closest('.tf_property_col_left').removeClass('hide-x-on');
							}
						} else {
							$('.dimension-width[data-side="'+ side +'"]', $container).val( '' );
							$('.dimension-unit[data-side="'+ side +'"]', $container).val( 'px' );
						}
					});
					if ( ! _.isUndefined( value.position ) && '' !== value.position ) {
						$('.position', $container).val( value.position );
						if ( 'static' !== value.position ) {
							$container.find('.component').show();
						} else {
							$container.find('.component').hide();
						}
					} else {
						$('.position', $container).val( '' );
						$container.find('.component').hide();
					}
				break;

				case 'tf_float_properties':
					if ( ! _.isUndefined( value.float ) && '' !== value.float ) {
						$('.float', $container).val( value.float );
					} else {
						$('.float', $container).val( '' );
					}
				break;

				case 'tf_opacity_properties':
					if ( ! _.isUndefined( value.opacity ) && '' !== value.opacity ) {
						// Opacity values are saved like 0, 27, 50, 100
						$('.opacity', $container).val( value.opacity ).next().text( value.opacity + '%' );
					} else {
						$('.opacity', $container).val( '100' ).next().text( '100%' );
					}
				break;

				case 'tf_z-index_properties':
					if ( ! _.isUndefined( value ) ) {
						if ( ! _.isUndefined( value.zindex ) ) {
							$('.z-index', $container).val( value.zindex );
						} else {
							$('.z-index', $container).val( '' );
						}
					} else {
						$('.z-index', $container).val( '' );
					}
				break;

				case 'tf_customcss_properties':
					if ( ! _.isUndefined( jsonData ) && '' !== jsonData ) {
						console.log(jsonData);
						var customcss = this.cleanCustomCSS( jsonData );
						// Trigger a keyup event so the backslashes are properly written
						$container.find('.customcss').val( customcss ).trigger('keyup');
					}
				break;
			}
		},

		cleanCustomCSS: function( jsonData ) {
			return jsonData.replace(/\\n/g, "\\n")
							.replace(/\\r/g, "\\r")
							.replace(/\\t/g, "\\t")
							.replace('{"css":"', '').replace('"}', '')
							.replace(/"/g, '\"')
							;
		},

		clean: function ( value ) {
			if ( typeof value === 'string' ) {
				return value
					.replace(/[\n]/g, '')
					.replace(/[\r]/g, '')
					.replace(/[\t]/g, '')
					;
			}
			return value;
		},

		_getCurrentControlModel: function() {
			return this.collection.get( this.model.get('tf_active_selector_key') );
		},

		_getCurrentSelector: function() {
			if ( 'chain' === this.model.get( 'tf_chain_with_context' ) ) {
				// .context123456.element
				return $( this.model.get( 'styling_context' ) + this.model.get( 'tf_active_selector' ) );
			} else {
				// .context123456 .element
				return $( this.model.get( 'tf_active_selector' ), this.model.get( 'styling_context' ) );
			}
		},

		close: function( event ) {
			event.preventDefault();
			// Remove class in body since styling panel is now hidden
			this.toggleStylingPanel( 'hidden' );
			this.$el.hide();
			var url = removeStylingQueryString();
			// Preserve ?tf=1 if styling panel on template/part
			if ( 'module' == this.model.get('mode') ) {
				window.location.href = updateQueryString( 'tf', 1, url );
			} else {
				window.location.href = url;
			}
		},

		destroyThis: function() {
			if ( 'object' === typeof $('.tf_target_elements', this.$el).getNiceScroll() ) {
				$('.tf_target_elements', this.$el).getNiceScroll().remove();
			}
			if ( 'object' === typeof $('.tf_css_properties', this.$el).getNiceScroll() ) {
				$('.tf_css_properties', this.$el).getNiceScroll().remove();
			}

			this.collection.reset();
			this.model.destroy();
			this.unbind();
			this.remove();

			delete this.$el; // Delete the jQuery wrapped object variable
			delete this.el; // Delete the variable reference to this node
		},

		ready: function() {
			// Font size numeric
			var control = this,
				container = this.$el;

			// Font family
			control.fontFamily( $('.tf_font_family', container) );

			// Font size numeric
			control.input( $('.font_size_num', container), 'sizenum' );

			// Font size unit
			control.dropdown( $('.font_size_unit', container), 'sizeunit' );

			// Line height numeric
			control.input( $('.font_line_num', container), 'linenum' );

			// Line height unit
			control.dropdown( $('.font_line_unit', container), 'lineunit' );

			// Font style
			control.fontStyle( $('.tf_font_style', container ) );

			// Text transform
			control.textTransform( $('.tf_text_transform', container ) );

			// Text align
			control.textAlign( $('.tf_font_align', container ) );

			// Border style
			control.dropdownBorderSame( $('.border-style', container) );

			// Border width
			control.dimensionSame( $('.dimension-width', container) );

			// Units
			control.dropdownSame( $('.dimension-unit', container), 'unit' );

			// Hide components leaving only one
			control.hideComponentsSame( $('.same', container) );

			// Open Media Library
			control.openMedia();

			// Checkbox to hide controls
			control.noImage( $('.disable-control', control.container), '.tf_background_wrap' );

			// Image style
			control.dropdown( $('.image-style', container), 'style' );

			// Image position
			control.dropdown( $('.image-position-style', container), 'position' );

			// Color Transparent
			control.transparent( $('.color-transparent', control.container) );

			control.pickColor();

			control.pickBorderColor();

			// Auto for multi side controls
			control.autoSame( $('.auto-prop-multi', control.container) );

			// Single Auto
			control.auto( $('.auto-prop', control.container) );

			// Single dimension for fields like width or height
			control.dimension( $('.dimension-width-single', container) );

			// Single unit for fields likw width or height
			control.dropdown( $('.dimension-unit-single', container), 'unit' );

			// Position hides values if it's set to static
			control.dropdownPosition( $('.position', container), 'position' );

			// Float
			control.dropdown( $('.float', container), 'float' );

			// Opacity
			control.inputInt( $('.opacity', container), 'opacity' );

			// Z Index
			control.inputInt( $('.z-index', container), 'zindex' );

			// Custom CSS textarea
			control.customcss( $('.tf_properties_list').find('.customcss'), 'css' );
		},

		changeFontProperty: function( model, values ) {
			var values = $.parseJSON( values || "null");
			setFont( this.$currentSelector, values );
			setColor( this.$currentSelector, 'color', values );
		},

		changeBorderProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;
			values.disabled = '';
			
			$selector.css( {
				'border-width' : '',
				'border-color' : 'transparent',
				'border-style' : ''
			} );

			if ( values && 'disabled' == values.disabled ) {
				$selector.css( {
					'border'  : 'none'
				} );
			} else if ( values && 'disabled' != values.disabled ) {
				if ( 'same' != values.same ) {
					_.each(['top', 'left', 'bottom', 'right'], function(side){
						if ( values[side] ) {
							setBorder( $selector, 'border-' + side, values[side] );
						}
					});
				} else if ( 'same' == values.same ) {
					setBorder( $selector, 'border', values );
				}
			}
		},

		changeBackgroundProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector,
				$stylePreview = $('#tf-style-preview');

			values.disabled = '';
			
			$selector.css( {
				'background-image'  : '',
				'background-color'  : '',
				'background-repeat' : '',
				'background-size'   : '',
				'background-position' : ''
			} );

			if ( values && 'noimage' == values.noimage ) {
				$selector.css( {
					'background-image' : 'none'
				} );
			} else if ( ! _.isUndefined( values.src ) ) {
				$selector.css('background-image', 'url(' + values.src + ')' );
			}
			if ( ! _.isUndefined( values.style ) && '' != values.style ) {
				if ( 'fullcover' == values.style ) {
					$selector.css( {
						'background-size': 'cover',
						'background-repeat': 'no-repeat'
					} );
				} else {
					$selector.css( {
						'background-size': 'auto',
						'background-repeat': values.style
					} );
				}
			}
			if ( ! _.isUndefined( values.position ) && '' != values.position ) {
				$selector.css( {
					'background-position': values.position
				} );
			}

			setColor( $selector, 'background-color', values );

		},

		changePaddingProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;
			values.disabled = '';
			
			$selector.css( {
				'padding' : '',
				'padding-top' : '',
				'padding-right' : '',
				'padding-bottom' : '',
				'padding-left' : ''
			} );

			if ( values && 'disabled' == values.disabled ) {
				$selector.css( {
					'padding' : 0,
					'padding-top' : 0,
					'padding-right' : 0,
					'padding-bottom' : 0,
					'padding-left' : 0
				} );
			} else if ( values && 'disabled' != values.disabled ) {
				if ( 'same' != values.same ) {
					_.each(['top', 'left', 'bottom', 'right'], function(side){
						if ( values[side] ) {
							setDimension( $selector, 'padding-' + side, values[side] );
						}
					});
				} else if ( 'same' == values.same ) {
					setDimension( $selector, 'padding', values );
				}
			}
		},

		changeMarginProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;
			values.disabled = '';

			$selector.css( {
				'margin' : '',
				'margin-top' : '',
				'margin-right' : '',
				'margin-bottom' : '',
				'margin-left' : ''
			} );

			if ( values && 'disabled' == values.disabled ) {
				$selector.css( {
					'margin' : 0,
					'margin-top' : 0,
					'margin-right' : 0,
					'margin-bottom' : 0,
					'margin-left' : 0
				} );
			} else if ( values && 'disabled' != values.disabled ) {
				if ( 'same' != values.same ) {
					_.each(['top', 'left', 'bottom', 'right'], function(side){
						if ( values[side] ) {
							setDimension( $selector, 'margin-' + side, values[side] );
						}
					});
				} else if ( 'same' == values.same ) {
					setDimension( $selector, 'margin', values );
				}
			}
		},

		changeWidthProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;

			$selector.css( 'width', '' );

			if ( values ) {
				setDimension( $selector, 'width', values );
			}
		},

		changeHeightProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;
			values.disabled = '';

			$selector.css( 'height', '' );

			if ( values && 'disabled' == values.disabled ) {
				$selector.css( 'height', '' );
			} else if ( values && 'disabled' != values.disabled ) {
				setDimension( $selector, 'height', values );
			}
		},

		changeMinWidthProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;

			$selector.css( 'min-width', '' );

			if ( values ) {
				setDimension( $selector, 'min-width', values );
			}
		},

		changeMaxWidthProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;

			$selector.css( 'max-width', '' );

			if ( values ) {
				setDimension( $selector, 'max-width', values );
			}
		},

		changeMinHeightProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;

			$selector.css( 'min-height', '' );

			if ( values ) {
				setDimension( $selector, 'min-height', values );
			}
		},

		changePositionProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;

			$selector.css( {
				'position' : '',
				'top' : '',
				'right' : '',
				'bottom' : '',
				'left' : ''
			} );

			if ( values ) {
				_.each(['top', 'left', 'bottom', 'right'], function(side){
					if ( values[side] ) {
						setDimension( $selector, side, values[side] );
					}
				});
				$selector.css( 'position', values.position );
			}
		},

		changeFloatProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;

			$selector.css( {
				'float' : ''
			} );

			if ( values && values.float ) {
				$selector.css( 'float', values.float );
			}
		},

		changeOpacityProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;

			$selector.css( {
				'opacity' : ''
			} );

			if ( values && values.opacity ) {
				// Opacity values are saved like 0, 27, 50, 100
				$selector.css( 'opacity', Math.abs( Math.floor( values.opacity ) ) / 100 );
			}
		},

		changeZIndexProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {},
				$selector = this.$currentSelector;

			$selector.css( {
				'z-index' : ''
			} );

			if ( values && values.zindex ) {
				$selector.css( 'zIndex', values.zindex );
			}
		},

		changeCustomCSSProperty: function( model, values ) {
			var values = $.parseJSON( values || "null") || {};

			try {
				var stylesheet = 'themify-custom-css',
					$stylesheet = $('#'+stylesheet);

				if ( values.css ) {
					var css = values.css;
					css = css.replace(/\\"/g, '"').replace(/:(\s*?)(\"|\')(\\+)(.*?)(\"|\')/g, ': $2\\$4$5');
					if ( $stylesheet.length > 0 ) {
						$stylesheet.remove();
					}
					$('head').append( '<style id="' + stylesheet + '">' + css + '</style>' );
				} else {
					if ( $stylesheet.length > 0 ) {
						$stylesheet.remove();
					}
				}
			} catch(e) {
				window.console && console.log && console.log(e);
			}
		},

		_getSelectedData: function( $obj, data, std ) {
			var $op = $('.tf_active', $obj ),
				dataget = $op.data( data );
			return dataget ? dataget : std;
		},

		_rebuildThis: function( model_id, module_name ) {
			var model = new TF.Models.ElementStyle(),
				collection = new TF.Collections.Controls(),
				type = 'row' == module_name ? 'row' : 'module';

			// Fetch data model
			TF.Instance.styleModel.fetch({
				data: {
					type: type,
					module: module_name,
					shortcode_id: model_id
				},
				success: function( resp ) {
					TF.Instance.stylingPanel[ model_id ].renderSelectorPanel();
				}
			});
			TF.Instance.stylingPanel[ model_id ] = new TF.Views.StylingControl( {model: model, collection: collection} );
		},

		_resizeScroll: function() {
			if ( 'object' === typeof this.$el.find('.tf_css_properties').getNiceScroll() ) {
				this.$el.find('.tf_css_properties').getNiceScroll().resize();
			}
		},

		clear: function( event ) {
			event.preventDefault();
			if ( confirm( _tf_app.clear_style_text ) ) {
				var $self = this,
					mode = this.model.get('mode'),
					model_id = this.model.get('ID'),
					mod_name = this.model.get('module');
				
				if ( 'module' == mode ) {
					// Module and row styling mode
					// reset from global collection
					if ( TF.Instance.elementsCollection.get( this.model ) ) {
						TF.Instance.elementsCollection.remove( this.model );
						console.log(TF.Instance.elementsCollection.toJSON(), 'after remove');
					}

					TF.Instance.loader.show();
					wp.ajax.post( 'tf_clear_template_style', {
						template_id: _tf_app.post_id,
						nonce: _tf_app.nonce,
						dataStyling: JSON.stringify( TF.Instance.elementsCollection.toJSON() )
					}).done(function(data){
						var oldCss = $('#tf-template-layout-css'),
							temp_oldCss = $('#tf-template-temp-'+ model_id +'-css');
						oldCss.after( data );
						oldCss.remove();
						if ( temp_oldCss.length ) {
							temp_oldCss.remove();
						}
						// update module preview
						var $active_module = TF.Instance.utilityModel.get('current_edit_module'),
							element = 'row' == mod_name ? 'row' : 'module',
							this_page = window.location.toString(),
							$targetLoad = 'row' == element ? $active_module : $active_module.find('.tf_'+ element +'_block_' + model_id);

						$targetLoad.load(this_page + ' .tf_'+ element +'_block_' + model_id + ' > *', function(){
							TF.Instance.builder.trigger('refresh');
							$self.destroyThis();
							$self._rebuildThis( model_id, mod_name );
							TF.Instance.loader.hide();
						} );
					});
				} else {
					// Global styling reset
					TF.Instance.loader.show();
					wp.ajax.post( 'tf_clear_global_styling', {
						nonce: _tf_app.nonce
					}).done(function( response ){
						TF.Instance.loader.hide();
						window.location.reload();
					});
				}
			}
		},

		toggleStylingPanel: function( mode ) {
			// Add class to body indicating that styling panel is now visible
			var $body = $('body');
			switch ( mode ) {
				case 'visible':
					$body.addClass( 'tf_styling_panel_visible' );
					break;
				case 'nothing':
					break;
				case 'hidden':
					$body.removeClass( 'tf_styling_panel_visible' );
					break;
			}
		},

		sanitizeHostName: function( host ) {
			return host.replace(/[.-]/g, '_');
		}

	});

	_.extend( TF.Views.StylingControl.prototype, TF.Mixins.StylingControlField );

	// code
})(jQuery);