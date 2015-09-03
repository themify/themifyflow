(function($){

	'use strict';

	if ( _.isUndefined( TF.Instance.loader ) ) {
		TF.Instance.loader = new TF.Views.Loader();
	}
	
	$(function(){
		// Run on DOM ready

		if ( $('.visibility-tabs').length ) {
			$('.visibility-tabs').tabs();
			// Selected value
			$('[data-toggleable]:checked').each(function(){
				var target = $(this).data('toggleable');
				if ( $('.' + target).length ) {
					var split_class = target.slice(0, target.lastIndexOf('-'));
					$('.' + split_class).hide();
					$('.' + target).show();
				}
			});
		}

		$('.tf_interface select').each( function(){
			// Wrap all select input
			if ( ! $(this).parent().hasClass('tf_custom_select') ) {
				$(this).wrap('<div class="tf_custom_select"/>');
			}
		});

		$('body').on('click', '.tf_lightbox_new', function( event ){
			event.preventDefault();

			// Run an AJAX request.
			var type = $(this).data('type'),
				jqxhr = wp.ajax.post( 'tf_lightbox', {
					method: 'add',
					type: type,
					nonce: _tf_app.nonce
				}),
				lightbox = new TF.Views.Lightbox({ title: _tf_app[ 'title_add_' + type] });
			jqxhr.done(function(data){
				lightbox.load( data );

				// Trigger event
				$('body').trigger( 'tf_on_lightbox_opened', [ lightbox.$el, lightbox.$el ] );
			});
		})

		.on('click', '.tf_lightbox_edit', function( event ){
			event.preventDefault();

			// Run an AJAX request.
			var type = $(this).data('type'),
				theme_id = $(this).data('post-id'),
				jqxhr = wp.ajax.post( 'tf_lightbox', {
					method: 'edit',
					type: type,
					theme_id: theme_id,
					nonce: _tf_app.nonce
				}),
				lightbox = new TF.Views.Lightbox({ title: _tf_app[ 'title_edit_' + type] });
			jqxhr.done(function(data){
				lightbox.load( data );

				// Trigger event
				$('body').trigger( 'tf_on_lightbox_opened', [ lightbox.$el, lightbox.$el ] );
			});
		})

		.on('click', '.tf_lightbox_import', function( event ){
			event.preventDefault();
			var type = $(this).data('type'),
				lightbox = new TF.Views.Lightbox({ title: _tf_app['title_import_' + type] }),
				tpl = wp.template('tf_import_form'),
				callback = null;

			if ( 'theme' == type ) {
				callback = function( json ){
					if ( confirm( 'Theme has imported. Would you like to activate it?' ) ) {
						window.location.href = _.unescape( json.activate_theme_uri );
					} else {
						$('.tf_close_lightbox').trigger('click');
						window.location.reload();	
					}
				};
			}
			
			lightbox.load( $( tpl() ) );
			TF.Utils.plupload( { import_source: type }, callback );
		})

		.on('click', '.tf_lightbox_duplicate', function( event ){
			event.preventDefault();

			// Run an AJAX request.
			var type = $(this).data('type'),
				jqxhr = wp.ajax.post( 'tf_lightbox', {
					method: 'duplicate',
					type: type + '_duplicate',
					postid: $(this).data('post-id'),
					nonce: _tf_app.nonce
				}),
				lightbox = new TF.Views.Lightbox({ title: _tf_app['title_duplicate_' + type] });
			jqxhr.done(function(data){
				lightbox.load( data );

				// Trigger event
				$('body').trigger( 'tf_on_lightbox_opened', [ lightbox.$el ] );
			});
		})

		.on('submit', '#tf_theme_form', function( event ){
			event.preventDefault();

			TF.Instance.loader.show();
			var $this = $(this),
				data = $this.serialize(),
				jqxhr = wp.ajax.post( 'tf_form_save', {
					type: 'theme',
					data: data,
					nonce: _tf_app.nonce
				});
			jqxhr.done(function(data){

				if ( data.newTheme ) {
					if ( confirm( 'The theme has created. Would you like to activate it?' ) ) {
						window.location.href = _.unescape( data.url );
					} else {
						$('.tf_close_lightbox').trigger('click');
						window.location.reload();
					}
				} else {
					$('.tf_close_lightbox').trigger('click');
					window.location.reload();
				}

			}).fail(function( data ){
				TF.Instance.loader.hide();
				$this.find('.tf_input .error').html('');
				$.each(data, function(k,v){
					$('#tf_field_' + k).parent().find('.error').html(v);
				});
			});
		})

		.on('submit', '#tf_template_form', function( event ){
			event.preventDefault();
			TF.Instance.loader.show();
			var $this = $(this),
				data = $this.serialize(),
				jqxhr = wp.ajax.post( 'tf_form_save', {
					type: 'template',
					data: data,
					nonce: _tf_app.nonce
				});
			jqxhr.done(function(url){
				$('.tf_close_lightbox').trigger('click');
				window.location.href = url;
			}).fail(function( data ){
				TF.Instance.loader.hide();
				$this.find('.tf_input .error').html('');
				$.each(data, function(k,v){
					$('#tf_field_' + k).parent().find('.error').html(v);
				});
			});
		})

		.on('submit', '#tf_template_part_form', function( event ){
			event.preventDefault();
			TF.Instance.loader.show();
			var $this = $(this),
				data = $this.serialize(),
				jqxhr = wp.ajax.post( 'tf_form_save', {
					type: 'template_part',
					data: data,
					nonce: _tf_app.nonce
				});
			jqxhr.done(function(url){
				$('.tf_close_lightbox').trigger('click');
				window.location.href = url;
			}).fail(function( data ){
				TF.Instance.loader.hide();
				$this.find('.tf_input .error').html('');
				$.each(data, function(k,v){
					$('#tf_field_' + k).parent().find('.error').html(v);
				});
			});
		})

		.on('submit', '#tf_theme_duplicate_form', function( event ) {
			event.preventDefault();
			TF.Instance.loader.show();
			var $this = $(this),
				data = $this.serialize(),
				jqxhr = wp.ajax.post( 'tf_form_save', {
					type: 'theme_duplicate',
					data: data,
					nonce: _tf_app.nonce
				});
			jqxhr.done(function(url){
				if ( confirm( 'Theme has duplicated. Would you like to activate it?' ) ) {
					window.location.href = _.unescape( url );
				} else {
					$('.tf_close_lightbox').trigger('click');
					window.location.reload();
				}
			}).fail(function( data ){
				TF.Instance.loader.hide();
				$this.find('.tf_input .error').html('');
				$.each(data, function(k,v){
					$('#tf_field_' + k).parent().find('.error').html(v);
				});
			});
		})

		.on('submit', '#tf_template_duplicate_form', function( event ) {
			event.preventDefault();
			TF.Instance.loader.show();
			var $this = $(this),
				data = $this.serialize(),
				jqxhr = wp.ajax.post( 'tf_form_save', {
					type: 'template_duplicate',
					data: data,
					nonce: _tf_app.nonce
				});
			jqxhr.done(function(url){
				$('.tf_close_lightbox').trigger('click');
				window.location.reload();
			}).fail(function( data ){
				TF.Instance.loader.hide();
				$this.find('.tf_input .error').html('');
				$.each(data, function(k,v){
					$('#tf_field_' + k).parent().find('.error').html(v);
				});
			});
		})

		.on('submit', '#tf_template_part_duplicate_form', function( event ) {
			event.preventDefault();
			TF.Instance.loader.show();
			var $this = $(this),
				data = $this.serialize(),
				jqxhr = wp.ajax.post( 'tf_form_save', {
					type: 'template_part_duplicate',
					data: data,
					nonce: _tf_app.nonce
				});
			jqxhr.done(function(url){
				$('.tf_close_lightbox').trigger('click');
				window.location.reload();
			}).fail(function( data ){
				TF.Instance.loader.hide();
				$this.find('.tf_input .error').html('');
				$.each(data, function(k,v){
					$('#tf_field_' + k).parent().find('.error').html(v);
				});
			});
		})

		.on('click', '.tf_lightbox_replace', function( event ){
			event.preventDefault();
			if ( confirm( _tf_app.replace_confirm ) ) {
				var type = $(this).data('type'),
					lightbox = new TF.Views.Lightbox({title: _tf_app['title_replace_' + type] }),
					tpl = wp.template('tf_import_form');
				
				lightbox.load( $( tpl() ) );
				TF.Utils.plupload( { topost: $(this).data('post-id'), import_method: 'edit', import_source: type } );
			}
		});

		// Hack: add button after add new post button
		var import_links = ['theme', 'template', 'template_part'],
			import_btn = wp.template('tf_import_link');

		_.each( import_links, function( value ){
			var $addNew = $('.post-type-tf_'+ value +' a.page-title-action');
                        if($addNew.length==0){
                            $addNew = $('.post-type-tf_'+ value +' a.add-new-h2'); //old version
                        }
			$addNew.hide();
			$(import_btn({ type: value }))
			.insertAfter($addNew);
		});
                
                //Modul TF Options 
                 $('#tf_to_metaboxes input[type="radio"]').change(function(){
                   var $_select = $(this).closest('.tf_lightbox_input').find('.tf_custom_select');
                   if($(this).val()=='custom'){
                       $_select.fadeIn().css('display', 'inline-block');
                   }
                   else{
                       $_select.fadeOut();
                   }
                });  
                $('#tf_to_metaboxes input[type="radio"]:checked').trigger('change');
             
                $('#tf_to_template').change(function(){
                        var $_template = $('#tf_lightbox_radios');

                       if(!$(this).val()){
                           $_template.slideUp();
                       }
                       else if(!$_template.is(':visible')){
                          $_template.slideDown();
                       }
                });  
                $('#tf_to_template').trigger('change');

	});

	// Run on WINDOW load
	$(window).load(function(){

		// popup add new form
		if( window.location.hash === "#tf_add_new" ) {
			$('.tf_lightbox_new').trigger('click');

			// remove hash
			if ( window.history && window.history.replaceState ) { 
				window.history.replaceState('', '', window.location.pathname + window.location.search); 
			} else { 
				window.location.href = window.location.href.replace(/#.*$/, '#'); 
			}
		}

		// tabs in Flow settings page
		$( 'body' ).on( 'click', '.themify-flow-settings .nav-tab-wrapper a', function( e ){
			e.preventDefault();
			var $this = $( this );
			$this.addClass( 'nav-tab-active' ).siblings().removeClass( 'nav-tab-active' );
			$( 'div.themify-flow-settings' ).find( '.setting-tab-wrap' ).fadeOut().filter( $this.attr( 'href' ) ).fadeIn();
		} );
	});
})(jQuery);