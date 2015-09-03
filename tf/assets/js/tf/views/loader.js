(function($){

	'use strict';

	TF.Views.Loader = Backbone.View.extend({
		id: 'tf_routine_loader',
		
		className: 'tf_interface',

		template: wp.template('tf_routine_loader'),
		
		initialize: function() {
			if ( ! $('#tf_routine_loader').length ) {
				this.render();
			}
		},
		
		render: function() {
			this.$el.html( this.template() ).appendTo('body');
		},
		
		show: function(){
			this.$el.show();
		},
		
		hide: function(){
			this.$el.hide();
		}
	});

	// code
})(jQuery);