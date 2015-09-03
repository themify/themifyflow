(function($){

	'use strict';

	// Lightbox View
	TF.Views.Lightbox = Backbone.View.extend({

		tagName: 'div',

		className: 'tf_overlay',
		
		template: wp.template('tf_lightbox'),

		ensureClose: true,

		initialize: function( options ) {
			this.options = options || {};
			this.render();
		},

		events: {
			'click .tf_close_lightbox' : 'close',
			'click .tf_cancel_lightbox' : 'cancel'
		},

		render: function() {
			var lightboxParams = { title: this.options.title, lightboxClass: this.options.lightboxClass || 'builder-lightbox', closeBtn: this.options.closeBtn || 'yes' };
			this.$el.html( this.template(lightboxParams) ).appendTo('body');
		},

		close: function( event ) {
			event.preventDefault();
			this.trigger('remove');

			if ( this.ensureClose ) {
				this.remove();
			}
		},

		cancel: function( event ) {
			event.preventDefault();
			this.trigger('cancel');
			
			if ( this.ensureClose ) {
				this.remove();
			}
		},

		load: function( $html ) {
			this.$el.find('.tf_lightbox_container').html( $html );
		},

		append: function( $html ) {
			this.$el.find('.tf_lightbox_container').append( $html );
		}
	});
})(jQuery);