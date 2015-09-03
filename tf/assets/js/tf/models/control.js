(function($){

	'use strict';

	TF.Models.Control = Backbone.Model.extend({
		defaults: {
			SettingKey : ''
			/**
			 * all properties name fields (tf_font_properties, tf_font_border_properties, etc)
			 */
		},

		idAttribute: 'SettingKey'
	});
	
})(jQuery);