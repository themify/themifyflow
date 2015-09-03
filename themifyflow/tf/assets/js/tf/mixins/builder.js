(function($){

	'use strict';

	var isInIframe = (window.location != window.parent.location) ? true : false;

	// mixins
	TF.Mixins.Builder = {
		getRowData: function( $base, index ) {
			var row = { shortcode: $base.data('tf-shortcode'), params: $base.data('tf-atts'), content: '' },
				row_arr = [];

			$base.find('.tf_col').first().parent().children('.tf_col').each(function( col_key ){
				var $column = $(this),
					column = { shortcode: $column.data('tf-shortcode'), params: $column.data('tf-atts'), content: ''},
					column_arr = [];

				$column.find('.tf_module_holder').first().children().not('.tf_empty_holder_text').each(function( misc_key ){
					var $misc = $(this),
						misc = { shortcode: '', params: $misc.data('tf-atts'), content: ''},
						misc_arr = [];

					if ( $misc.hasClass('active_module') ) {
						misc.shortcode = 'module';
						misc.content = $misc.data('tf-content');
						misc.module_name = $misc.data('tf-module');
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
			return row;
		},

		getStylingData: function( $base, index ) {
			var return_data = [],
				$atts = $base.data('tf-atts'),
				r_sc_id = !_.isUndefined( $atts.sc_id ) ? $atts.sc_id : 0;

			return_data.push( r_sc_id );
			
			$base.find('.tf_col').first().parent().children('.tf_col').each(function( col_key ){
				var $column = $(this),
					$column_atts = $column.data('tf-atts'),
					column_sc_id = ! _.isUndefined( $column_atts.sc_id ) ? $column_atts.sc_id : 0;

				return_data.push( column_sc_id );

				$column.find('.tf_module_holder').first().children().not('.tf_empty_holder_text').each(function( misc_key ){
					var $misc = $(this),
						$misc_atts = $(this).data('tf-atts'),
						misc_sc_id = ! _.isUndefined( $misc_atts.sc_id ) ? $misc_atts.sc_id : 0;

					return_data.push( misc_sc_id );

					if ( $misc.hasClass('tf_sub_row') ) {
						// Deep checking module in sub_row
						$misc.find('.tf_col').first().parent().children('.tf_col').each( function( sub_col_key ){
							var $sub_col = $(this),
								$sub_col_atts = $sub_col.data('tf-atts'),
								sub_col_sc_id = ! _.isUndefined( $sub_col_atts.sc_id ) ? $sub_col_atts.sc_id : 0;

							return_data.push( sub_col_sc_id );

							$sub_col.find('.tf_module_holder').first().children().not('.tf_empty_holder_text').each( function(sub_module_key) {
								var $sub_module = $(this),
									$sub_module_atts = $sub_module.data('tf-atts'),
									sub_module_sc_id = ! _.isUndefined( $sub_module_atts.sc_id ) ? $sub_module_atts.sc_id : 0;
								return_data.push( sub_module_sc_id );
							});							
						});
					}

				});
			});
			
			return return_data;
		},

		getContentData: function() {
			var $self = this,
				return_data = [];

			this.$el.children('.tf_row').each(function( row_key ){
				return_data[ row_key ] = $self.getRowData( $(this), row_key );
			});
			
			return return_data;
		},

		saveBuilderData: function() {
			// set content model
			var $self = this,
				content = this.getContentData(),
				dataStyling = JSON.stringify( TF.Instance.elementsCollection.toJSON() );
			TF.Instance.templateModel.set('content', content);

			TF.Instance.loader.show();
			var jqxhr = wp.ajax.post( 'tf_builder_panel_save', {
					data: TF.Instance.templateModel.toJSON(),
					dataStyling: dataStyling,
					template_id: _tf_app.post_id,
					nonce: _tf_app.nonce
				});
			jqxhr.done(function(data){
				if ( isInIframe ) {
					// save the changes of parent Template Builder
					window.parent.TF.Instance.builder.saveBuilderData().done(function(){
						$self._updateTemplatePart();
						$self.saved = true;
					});
				} else {
					TF.Instance.loader.hide();
					$self.saved = true;
				}
			});
			return jqxhr;
		},

		_updateTemplatePart: function() {
			wp.ajax.post( 'tf_builder_update_template_part', {
				template_part_id: _tf_app.post_id,
				parent_template_id: _tf_app.parent_template_id,
				region: _tf_app.region,
				nonce: _tf_app.nonce
			}).done(function( response ){
				var tpl = wp.template('tf_template_part'),
					markup = tpl( {caption: response.caption, edit_url: response.edit_url, element: response.html } ),
					$newElemns = $(markup), $parentWindow = window.parent.jQuery,
					oldCss = $parentWindow('#tf-style-preview');

				$parentWindow('[data-region-area="'+ response.region +'"]').find('.tf_template_part_region_render_content')
				.html( $newElemns );

				if ( oldCss.length ) {
					oldCss.after( response.styles );
					oldCss.remove();
				}

				TF.Instance.loader.hide();
				$parentWindow('body').trigger('tf_live_preview', [ $newElemns ] );
				$parentWindow('.tf_close_lightbox').trigger('click');

			});
		}
	};

})(jQuery);