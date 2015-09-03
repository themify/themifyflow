(function($){

	'use strict';

	// Serialize Object Function
	if ( 'undefined' === typeof $.fn.serializeObject ) {
		$.fn.serializeObject = function() {
			var o = {};
			var a = this.serializeArray();
			$.each(a, function() {
				if (o[this.name] !== undefined) {
					if (!o[this.name].push) {
						o[this.name] = [o[this.name]];
					}
					o[this.name].push(this.value || '');
				} else {
					o[this.name] = this.value || '';
				}
			});
			return o;
		};
	}

	TF.Instance.templateModel = new TF.Models.Template( _tdBootstrapTemplate );
	TF.Instance.utilityModel = new TF.Models.Utility( _tdBootstrapUtility );
	TF.Instance.loader = new TF.Views.Loader();

	var isInIframe = (window.location != window.parent.location) ? true : false;

	function tf_update_builder_field( $field ) {
		var return_data = [],
			id = $field.prop('id'),
			$builder = $('[data-builder-field="'+ id +'"]');

		$builder.find('.tf_back_row_panel').children('.tf_back_row:not(.tf_row_droppable)').each( function( row_key ){
			var $row = $(this),
				row = { shortcode: $row.data('tf-shortcode'), params: $row.data('tf-atts'), content: '' },
				row_arr = [];

			$row.find('.tf_back_col').first().parent().children('.tf_back_col').each(function( col_key ){
				var $column = $(this),
					column = { shortcode: $column.data('tf-shortcode'), params: $column.data('tf-atts'), content: ''},
					column_arr = [];

				$column.find('.tf_module_holder').first().children().not('.tf_empty_holder_text').each(function( misc_key ){
					var $misc = $(this),
						misc = { shortcode: '', content: ''},
						misc_arr = [];

					if ( $misc.hasClass('tf_active_module') ) {
						misc.shortcode = 'element';
						misc.module_name = $misc.data('tf-module');
						misc.params = $misc.find(':input').serializeObject();
						var content_field = $misc.data('tf-content');
						if ( ! _.isEmpty( content_field ) && ! _.isUndefined( misc.params[ content_field ] ) ) {
							misc.content = misc.params[ content_field ];
							delete misc.params[ content_field ];
						}
					} else if ( $misc.hasClass('tf_sub_row') ) {
						misc.shortcode = $misc.data('tf-shortcode');

						// Deep checking module in sub_row
						$misc.find('.tf_col').first().parent().children('.tf_col').each( function( sub_col_key ){
							var $sub_col = $(this),
								sub_col = { shortcode: $sub_col.data('tf-shortcode'), params: $sub_col.data('tf-atts'), content: '' },
								sub_col_arr = [];

							$sub_col.find('.tf_module_holder').first().children().not('.tf_empty_holder_text').each( function(sub_module_key) {
								var $sub_module = $(this),
								sub_module = { shortcode: 'module', params: $sub_module.data('tf-atts'), content: $sub_module.data('tf-content'), module_name: $sub_module.data('tf-module') };

								sub_col_arr[ sub_module_key ] = sub_module;
							});

							sub_col.content = sub_col_arr;
							misc_arr[ sub_col_key ] = sub_col;							
						});

						misc.content = misc_arr;
					}

					column_arr[ misc_key ] = misc;

				});

				column.content = column_arr;
				row_arr[ col_key ] = column;
			});

			row.content = row_arr;
			return_data[ row_key ] = row;
		});

		$field.val( JSON.stringify( return_data ) );
	}

	$(function(){
		// Instantiate views and models
		TF.Instance.builder = new TF.Views.Builder({ el: '.tf_content_builder', mode: 'backend' });
		TF.Instance.elementsCollection = new TF.Collections.ElementStyles( _tdBootstrapStyles );

		var active_tabs = _.isEmpty( _tf_app.template_type ) ? '#tf_module_tabs_global' : '#tf_module_tabs_' + _tf_app.template_type;
		$('.tf_module_tabs').tabs({active: $(active_tabs).index() - 1 });

		$('body').on('click', '#tf_main_save', function( event ){
			event.preventDefault();

			TF.Instance.builder.saveBuilderData().done(function(){
				if ( 'tf_template' == _tf_app.post_type || 'tf_template_part' == _tf_app.post_type ) {
					$(window).off( 'beforeunload.edit-post' );
					$('#post').submit();
				}
			});
		})

		.on('click', 'input#publish', function( event ) {
			var $this = $(this);
			if( $.inArray( _tf_app.post_type, ['tf_template', 'tf_template_part'] ) == -1 && ! TF.Instance.builder.saved ) {
				TF.Instance.builder.saveBuilderData().done(function(){
					$this.trigger('click');
				});
				event.preventDefault();
			}
		})

		.on('submit', '#tf_module_form', function( event ){
			event.preventDefault();
			TF.Instance.loader.show();

			$('.tf_field_builder').each(function(){
				tf_update_builder_field( $(this) );
			});

			var $this = $(this),
				data = $this.serialize(),
				jqxhr = wp.ajax.post( 'tf_builder_form_save', {
					type: 'module',
					data: data,
					nonce: _tf_app.nonce
				});
			jqxhr.done(function(data){
				var tpl = wp.template('tf_active_module'),
					$active_module = TF.Instance.utilityModel.get('current_edit_module'),
					$newElemns = tpl( {tf_module: data.module, content: data.content, atts: JSON.stringify( data.atts ), tf_module_title: data.caption, element: data.element } );
				$active_module.removeData('new');
				$('.tf_close_lightbox').trigger('click');
				$active_module.replaceWith( $newElemns );
				TF.Instance.loader.hide();
				TF.Instance.builder.trigger('refresh');
				TF.Instance.builder.trigger('tf_live_preview', $newElemns);
				$('body').trigger('tf_live_preview', [ $newElemns ] );
			}).fail(function( data ){
				$this.find('.tf_input .error').html('');
				$.each(data, function(k,v){
					$('#tf_field_' + k).parent().find('.error').html(v);
				});
				TF.Instance.loader.hide();
			});

		})

		.on('submit', '#tf_row_option_form', function( event ){
			event.preventDefault();
			TF.Instance.loader.show();

			var $this = $(this),
				$active_row = TF.Instance.utilityModel.get('current_edit_module'),
				data = $this.serialize(),
				jqxhr = wp.ajax.post( 'tf_builder_form_save', {
					type: 'row',
					data: data,
					nonce: _tf_app.nonce
				});
			jqxhr.done(function(data){
				$('.tf_close_lightbox').trigger('click');

				var $newElemns = $(data.element),
					$row_content = $active_row.find('.tf_row_content').html();
				
				$newElemns.find('.tf_row_content').html($row_content)
				$active_row.replaceWith( $newElemns );

				TF.Instance.loader.hide();
				TF.Instance.builder.trigger('refresh');
				TF.Instance.builder.trigger('tf_live_preview', $newElemns);
				$('body').trigger('tf_live_preview', [ $newElemns ] );
			}).fail(function( data ){
				$this.find('.tf_input .error').html('');
				$.each(data, function(k,v){
					$('#tf_field_' + k).parent().find('.error').html(v);
				});
				TF.Instance.loader.hide();
			});

		});

		// key event save
		$(document).on('keydown', function(event){
			if (83 == event.which && (true == event.ctrlKey || true == event.metaKey)) {
				var currentElement = document.activeElement.tagName.toLowerCase();

				if ( currentElement != 'input' && currentElement != 'textarea' ) {
					event.preventDefault();
					var $moduleForm = $('#tf_module_form'),
						$templateForm = $('#tf_template_form'),
						$templatePartForm = $('#tf_template_part_region_form'),
						$panelSave = $('#tf_module_panel').find('.tf-front-save');
					
					if($moduleForm.length > 0){
						$moduleForm.trigger('submit');
					} else if($templateForm.length > 0){
						$templateForm.trigger('submit');
					} else if($templatePartForm.length > 0){
						$templatePartForm.trigger('submit');
					} else if($panelSave.length > 0){
						$panelSave.trigger('click');
					}
				}
			}
		});

	});

	// Run on WINDOW load
	$(window).load(function(){
		
	});	
})(jQuery);