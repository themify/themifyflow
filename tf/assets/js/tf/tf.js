var TF_Form;
(function( window, undefined ) {

	'use strict';

	function TF_API() {
		this.Mixins = {};
		this.Models = {};
		this.Collections = {};
		this.Views = {};
		this.Instance = {};
		this.Utils = {};
	}

	window.TF = window.TF || new TF_API();

})( window );

(function($){

	'use strict';

	TF_Form = {

		domready : function(){
			
		},

		/*
		 * Initialize fields on a given context
		 */
		init_fields : function( $context, $holder ){

			/**
			 * Create a new row in repeater field type
			 * Called when .tf_add_row button is clicked
			 */
			$( '.tf_field_repeater .tf_add_row', $context ).on( 'click', function(){
				TF_Form.repeater_make_row( $( this ).closest( '.tf_field_repeater' ) );

				// re-init the field controls on new row
				TF_Form.init_fields( $( this ).closest( '.tf_field_repeater' ).find( '.tf_repeater_items li:last-child' ), $holder );
			} );

			$( '.tf_repeater_items', $context ).sortable( {
				'placeholder' : 'tf-state-highlight',
				'handle' : '.tf_item_header'
			} );

			$('.visibility-tabs', $context).tabs(); // tabs

			// Checkbox Group field type
			$( '.tf_checkbox_group', $context ).each( function() {
				var group = $( this ),
					data = $holder.data( 'tf-atts' ),
					field_name = group.data( 'field-name' );
				if( typeof data[field_name] != 'undefined' ) {
					var val = data[field_name].split( ',' );
					$.each(val, function( i, v ){
						group.find( ':checkbox[data-key="' + v + '"]' ).click();
					});
				}
			} );

			// Selected value
			$('[data-toggleable]:checked', $context).each(function(){
				var target = $(this).data('toggleable');
				if ( $('.' + target).length ) {
					var split_class = target.slice(0, target.lastIndexOf('-'));
					$('.' + split_class).hide();
					$('.' + target).show();
				}
			});
			// disable checkbox
			$('.tf_toggle_prop', $context).each(function(){
				if ($(this).is(':checked')) {
					$(this).parent().nextAll('label').each(function(){
						$(this).find('input[type="checkbox"]').prop({ disabled: true, checked: false });
					});
				} else {
					$(this).parent().nextAll('label').each(function(){
						$(this).find('input[type="checkbox"]').prop({ disabled: false });
					});
				}
			});

			// Pick Color
			$('.tf_color_picker', $context).each(function(){
				var $color = $(this),
					$container = $color.closest('.tf_custom_color_wrap'),
					$field = $('.tf_color_pick_value', $container),
					$removeColor = $('.remove-color', $container ),
					setColor = '', setOpacity = 1.0;

				if ( '' != $field.val() ) {
					// Get saved value from hidden field
					var colorOpacity = $field.val();
					if ( -1 != colorOpacity.indexOf('_') ) {
						// If it's a color + opacity, split and assign the elements
						colorOpacity = colorOpacity.split('_');
						setColor = colorOpacity[0];
						setOpacity = colorOpacity[1] ? colorOpacity[1] : 1;
					} else {
						// If it's a simple color, assign solid to opacity
						setColor = colorOpacity;
						setOpacity = 1.0;
					}
				}
				
				$color.minicolors( {
					theme: 'tf_field_form',
					defaultValue: '',
					letterCase: 'lowercase',
					opacity: true,
					change: function(hex, opacity) {
						if ( '' != hex ) {
							if ( opacity && '0.99' == opacity ) {
								opacity = '1';
							}
							var value = hex.replace('#', '') + '_' + opacity;
							$field.val(value);
							$color.val( hex.replace('#', '') );
						}

						$removeColor.show();
					},
					hide: function() {
						if ( '' != $(this).val() ) {
							$(this).val().replace( '#', '' );
							$removeColor.show();
						}
					}
				});
				$color.minicolors('opacity', setOpacity);

				// Trigger update when user interacts with input field
				$color.on( 'keyup.themifyflow blur.themifyflow input.themifyflow paste.themifyflow', function(e){
					var $color = $(e.target),
						$container = $color.closest('.tf_custom_color_wrap'),
						$field = $('.tf_color_pick_value', $container),
						opacity = $color.data( 'opacity' ) ? $color.data( 'opacity' ) : '1.00',
						value = $color.val().replace('#', '') + '_' + opacity;
					$field.val(value);
				});

				// Clear color field
				$removeColor.on( 'click', function(e){
					e.preventDefault();
					$color.minicolors( 'value', '' );
					$color.minicolors( 'opacity', '' );
					$field.val('');
					$removeColor.hide();
				});
			});

			$('.tf_upload_media_library', $context).on('click', function( event ){
				event.preventDefault();

				var $el = $(this),
					libraryType = $el.data('library-type')? $el.data('library-type') : 'image',
					file_frame = wp.media.frames.file_frame = wp.media({
						title: $(this).data('uploader-title'),
						library: {
							type: libraryType
						},
						button: {
							text: $(this).data('uploader-button-text')
						},
						multiple: false  // Set to true to allow multiple files to be selected
					});
		 
				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					var attachment = file_frame.state().get('selection').first().toJSON();

					// store the attachment post name
					$el.closest('.tf_lightbox_input').find('.tf_upload_value').val(attachment.name).trigger( 'change', [ attachment ] );

					// Attached id to input
					var attach_name = $el.closest('.tf_lightbox_input').find('.tf_upload_value').prop('name') + '_attach_id';
					$el.closest('form').find('input[name="'+ attach_name+'"]').val(attachment.id);
					//$el.closest('.tf_lightbox_input').find('.tf_upload_value_attach_id').val(attachment.id);
				});
		 
				// Finally, open the modal
				file_frame.open();
			});

			// Image preview
			$( 'body' ).on( 'change', '.tf_upload_value.tf_type-image', function( event, attachment ){
				var $preview = $( this ).closest( '.tf_lightbox_input' ).find( '.tf_thumb_preview' );
				if ( ! _.isUndefined( attachment ) ) {
					$preview.show().find( '.tf_thumb_preview_placeholder' ).html( $( '<img/>', {
						src: attachment.url,
						width: 50,
						height: 50
					} ) );
				} else if ( ! $preview.find('img').length ) {
					$preview.hide();
				}

			} );
			$( '.tf_upload_value.tf_type-image', $context ).change(); // initial preview
			$('.tf_thumb_preview_delete', $context).on('click', function( event ){
				event.preventDefault();
				$(this).prev().empty().parent().hide();
				$(this).parents('.tf_lightbox_input').find('.tf_upload_value').val('');
			});

			$('.tf_browse_gallery_btn', $context).on('click', function( event ){
				event.preventDefault();
				var file_frame,
					$field = $(this).closest('.tf_lightbox_input').find('.tf_gallery_value');
				
				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					frame:     'post',
					state:     'gallery-edit',
					title:     wp.media.view.l10n.editGalleryTitle,
					editing:   true,
					multiple:  true,
					selection: false
				});

				// Custom function
				var updateGallery = function( selection ) {
					var shortcode = wp.media.gallery.shortcode( selection ).string().slice( 1, -1 );
					$field.val( shortcode );

					var images = selection.toJSON(), 
						sizes = _.pluck( images, 'sizes' ),
						tpl = wp.template('tf_gallery_preview'),
						$el = $( tpl( { sizes: sizes } ) );
					$field.parent().find('.tf_gallery_preview').html( $el );	
				};
				file_frame.on( 'update', updateGallery);
			
				if($.trim($field.val()).length > 0) {
					file_frame = wp.media.gallery.edit('['+ $.trim($field.val()) + ']');
					file_frame.state('gallery-edit').on( 'update', updateGallery );
				} else {
					file_frame.open();
					$('.media-menu').find('.media-menu-item').last().trigger('click');
				}
			});

			// Widget picker
			$( '.tf-field-widget-select', $context ).on( 'change', function(){
				var $this = $( this ),
					widget_data = $holder.data( 'tf-atts' );
				if ( ! _.isUndefined( widget_data.widget_data ) ) {
					widget_data = widget_data.widget_data;
				}
				if( $this.val() == '' ) {
					$this.next().empty();
					return;
				}
				$.ajax( {
					type : 'POST',
					url : ajaxurl,
					data : {
						action : 'tf_builder_get_widget_form',
						widget_class : $this.val(),
						widget_data: widget_data,
						nonce : _tf_app.nonce
					},
					success : function( data ){
						$this.parent().next().html( data );
					}
				} );
			} ).trigger( 'change' );

			// Wrap all select input
			$context.find('select').wrap('<div class="tf_custom_select"/>');
			$context.find('select[multiple]').parent("div.tf_custom_select").addClass("select_multiple");
		},

		/**
		 * Create a new row in a "repeater" field
		 * If no ID for the new row is passed, creates a unique ID for the row
		 */
		repeater_make_row : function( $field, new_ID ) {
			var template = $field.find( '.tf_repeater_template' ),
				$items = $field.find( '.tf_repeater_items' );
			new_ID = new_ID || ( TF_Form._get_next_highest_row_id( $items ) ); // if no ID is set for this new row, make a new one
			$items.append( '<li data-id="'+ new_ID +'"><div class="tf_item_header">&nbsp;<a class="tf_toggle_row"></a><a class="tf_delete_row"></a></div><div class="tf_item_body">' + template.html() + '</div></li>' );
			var $row = $items.find( 'li:last-child' );
			var name_pattern = template.attr( 'data-name-pattern' );

			// update field names
			$row.find( 'input:not([type="button"]), select, textarea, hidden' ).each( function(){
				if( typeof $( this ).attr( 'name' ) != 'undefined' ) {
					$( this ).attr( 'name', name_pattern + '[' + new_ID + '][' + $( this ).attr( 'name' ) + ']' );
				}
			} );
		},

		/**
		 * Returns unique ID for a new in a repeater set
		 */
		_get_next_highest_row_id : function( $items ){
			var highest_id = 0;
			$items.find( '> li' ).each( function(){
				if( parseInt( $( this ).attr( 'data-id' ).substr(1) ) > highest_id ) {
					highest_id = parseInt( $( this ).attr( 'data-id' ).substr(1) );
				}
			} );
			return '_' + ( highest_id + 1 ); // NOTE: the underscore is necessary to work around JSON ordering bug in Chrome
		},

		/**
		 * Collapse / un-collapse a row in repeater field
		 */
		repeater_toggle_row : function(){
			$( this ).toggleClass( 'closed' ).closest( 'li' ).find( '.tf_item_body' ).slideToggle();
		},

		/**
		 * Remove a row in repeater field
		 */
		repeater_delete_row : function(){
			$( this ).closest( 'li' ).remove();
		}
	};

	$(function(){

		$( 'body' ).on( 'click', '.tf_field_repeater .tf_toggle_row', TF_Form.repeater_toggle_row );
		$( 'body' ).on( 'click', '.tf_field_repeater .tf_delete_row', TF_Form.repeater_delete_row );
		
		// layout icon selected
		$('body').on('click', '.tf-layout-option', function(e){
			e.preventDefault();
			var value = $(this).data('value');
			$(this).addClass('selected').siblings().removeClass('selected');
			$(this).parent().find('.val').val(value);
		})
		.on('click', '[data-toggleable]', function(e){
			var target = $(this).data('toggleable');
			var split_class = target.slice(0, target.lastIndexOf('-'));
			$('.' + split_class).hide();
			$('.' + target).show();
		})
		.on('tf_on_lightbox_opened', function( event, $context, $holder ) {
			TF_Form.init_fields( $context, $holder );

			// populate repeater fields
			$( '.tf_repeater_items' ).each( function(){
				var data = $holder.data( 'tf-atts' );
				if( data == '' || $.isEmptyObject( data ) ) {
					$( this ).next().click(); // pre-populate one row if the list is empty
					return;
				}

				var $items = $( this ),
					$add_new = $items.next(),
					key = $items.prev().attr( 'data-key' );
				if( typeof data == "object" && key in data ) {
					// loop through each item in the set
					var ids = data[ key + '_order' ];
					$.each( ids.split( ',' ), function( t, i ){
						TF_Form.repeater_make_row( $items.closest( '.tf_field_repeater' ), i );

						// set the values for each fields of the current item
						$.each( $items.data( 'fields' ).split( ',' ), function( n, field ) {
							if( typeof data[ key + "_" + i + "_" + field ] == 'undefined' ) {
								return;
							}
							var value = data[ key + "_" + i + "_" + field ];
							var $field = $items.find( '[name="' + key + '[' + i + '][' + field + ']"]' );
							$field.val( value );

							// color picker fix
							if( $field.hasClass( 'tf_color_pick_value' ) ) {
								$field.parent().find( '.tf_color_picker' ).val( '#' + value.substr( 0, 6 ) );
							}

						} );

						TF_Form.init_fields( $items.find( 'li:last-child' ), $holder );
					} );
				}
			} );
		})
		.on('click', '.tf_toggle_prop', function(){
			if ($(this).is(':checked')) {
				$(this).parent().nextAll('label').each(function(){
					$(this).find('input[type="checkbox"]').prop({ disabled: true, checked: false });
				});
			} else {
				$(this).parent().nextAll('label').each(function(){
					$(this).find('input[type="checkbox"]').prop({ disabled: false });
				});
			}
		});

	});

	// Icon Picker
	$( 'body' ).on( 'click', '.tf_fa_toggle', function(){
		var $input = $( this ).prev();
		var lightbox = new TF.Views.Lightbox( {title: 'Icon Picker' });
		$.ajax({
			url : _tf_app.base_uri + '/assets/css/fontawesome/list.html',
			type : 'GET',
			success : function( data ){
				var container = lightbox.$el.find( '.tf_lightbox_container' );
				container.html( '<div class="tf-icons-list">' + data + '</div>' );
				container.find( '.fontawesome-icon-list a' ).on( 'click', function(e){
					e.preventDefault();
					var selected_icon = $( this ).text().replace( '(alias)', '' ).trim();
					$input.val( 'fa-' + selected_icon );
					lightbox.close( e );
				} );
			}
		});
	} );

	// Run on WINDOW load
	$(window).load(function(){
	});
})(jQuery);