(function($){

	'use strict';

	TF.Models.ElementStyle = Backbone.Model.extend({
		
		sync: function( method, model, options ) {
			var args, fallback;

			// Overload the delete method so model.fetch() functions correctly.
			if ( 'delete' === method ) {
				return '';
			// Otherwise, fall back to Backbone.sync()
			} else {
				return Backbone.sync.apply( this, arguments );
			}
		},

		defaults: {
			ID : ''
			//module: '',
			//settings: ''
		},

		idAttribute: 'ID'
	});
	
})(jQuery);