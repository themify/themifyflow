(function($){

	'use strict';

	TF.Views.TemplatePart = Backbone.View.extend({
		initialize: function() {
			this.render();
		},

		render: function() {
			this.$el.each(function(){
				if ( $.trim( $(this).find('.tf_template_part_region_render_content').html() ).length ) {
					$(this).find('.tf_template_region_button').hide();
				}
			});
		},

		events: {
			'click .tf_template_region_button-add' : 'regionAdd',
			'click .tf_lightbox_link_region-swap' : 'regionSwap',
			'click .tf_lightbox_link_region-edit' : 'regionEdit',
			'click .tf_lightbox_link_region-delete' : 'regionDelete',
			'dblclick .tf_active_block_overlay' : 'regionEditDouble'
		},

		regionAdd: function( event ) {
			event.preventDefault();

			// Run an AJAX request.
			var region = $(event.currentTarget).data('region'),
				jqxhr = wp.ajax.post( 'tf_lightbox', {
					method: 'add',
					type: 'region_template_part',
					template_id: _tf_app.post_id,
					region: region,
					nonce: _tf_app.nonce
				}),
				lightbox = new TF.Views.Lightbox();
			jqxhr.done(function(data){
				lightbox.load( data );

				// Trigger event
				$('body').trigger( 'tf_on_lightbox_opened', [ lightbox.$el ] );
			});
		},

		regionSwap: function( event ) {
			event.preventDefault();
			var region = $(event.currentTarget).closest('[data-region-area]').data('region-area');

			// Run an AJAX request.
			var jqxhr = wp.ajax.post( 'tf_lightbox', {
					method: 'edit',
					type: 'region_template_part',
					template_id: _tf_app.post_id,
					region: region,
					slug: TF.Instance.templateModel.get( region ),
					nonce: _tf_app.nonce
				}),
				lightbox = new TF.Views.Lightbox();
			jqxhr.done(function(data){
				lightbox.load( data );

				// Trigger event
				$('body').trigger( 'tf_on_lightbox_opened', [ lightbox.$el ] );
			});
		},

		regionDelete: function( event ) {
			event.preventDefault();

			if ( confirm( _tf_app.template_part_delete ) ) {
				var $parent = $(event.currentTarget).closest('[data-region-area]'),
					region = $parent.data('region-area');
				
				$parent.find('.tf_template_part_region_render_content').html('')
				.end().find('.tf_template_region_button').show();

				// Update Model
				TF.Instance.templateModel.set( region, '' );
			}
		},

		regionEdit: function( event ) {
			event.preventDefault();
			
			var lightbox = new TF.Views.Lightbox({lightboxClass: 'template-part-lightbox builder-lightbox', closeBtn: 'yes'}),
				url = $(event.currentTarget).data('edit-url');

			var iframe = $('<iframe/>', {id: 'template-part-iframe', src: url, frameborder: 0, scrolling: 'yes'})
			.load(function( response ){
				lightbox.$el.find('.tf_loader').remove();
				$(this).show();
				
				TF.Instance.builder._makeEqual( $(this).contents().find('.tf_sub_row'), '.tf_sub_row_content');
				TF.Instance.builder._makeEqual( $(this).contents().find('.tf_row'), '.tf_row_content');

				lightbox.on('remove cancel', function(){
					var $self = this;
					if ( ! iframe.get(0).contentWindow.TF.Instance.builder.saved ) {
						if ( ! confirm( _tf_app.leaving_template_lightbox ) ) {
							$self.ensureClose = false;
						} else {
							$self.ensureClose = true;
						}
					} else {
						$self.ensureClose = true;
					}
				});

			}).hide();
			lightbox.append( iframe );
		},

		regionEditDouble: function( event ) {
			event.preventDefault();
			$(event.currentTarget).parent().find('.tf_lightbox_link_region-edit').trigger('click');
		}
	});
	
})(jQuery);