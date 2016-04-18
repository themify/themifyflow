(function($){

	/** This Builder Views is also used for Backend Builder */

	'use strict';

	var leftButtonDown;

	$(document).mousedown(function(e){
		if(e.which === 1) leftButtonDown = true;
	}).mouseup(function(e){
		if(e.which === 1) leftButtonDown = false;
	});

	TF.Views.Builder = Backbone.View.extend({

		clearClass: 'tf_col6-1 tf_col5-1 tf_col4-1 tf_col4-2 tf_col4-3 tf_col3-1 tf_col3-2 tf_col2-1 tf_colfullwidth',

		gridClass: ['tf_colfullwidth', 'tf_col4-1', 'tf_col4-2', 'tf_col4-3', 'tf_col3-1', 'tf_col3-2', 'tf_col6-1', 'tf_col5-1'],

		slidePanelOpen : true,

		uuid: 0, // Create a universally unique identifier.

		mode: 'frontend', // frontend|backend

		saved: true,

		initialize: function( options ) {
			var $self = this;
			_.extend(this, _.pick(options, 'mode' ));
			this.mode = options.mode || this.mode;

			this.render();
			this.on('refresh', this.refresh);
			this.on('hideDropdown', function( $activeObject ){
				this.$el.find('.tf_dropdown').not( $activeObject ).hide()
				this.$el.find('.tf_grid_list_wrapper').not( $activeObject ).hide();
			});

			if ( 'frontend' == this.mode ) {
				window.onbeforeunload = function() {
					if ( ! $self.saved ) {
						return _tf_app.beforeunload;
					}
				};
			}
		},

		render: function() {
			this.setupGridMenu();
			this._selectedGridMenu();
			this._equalHeight();
			this.draggable();
			this.sortable();
			this._moduleDroppableText();
			this._droppableRowElemns();
			this._bindEvents();
			this._tipsy();
		},

		refresh: function() {
			this.setupGridMenu();
			this._equalHeight();
			this._moduleDroppableText();
			this.sortable();
			this.saved = false;
		},

		events: function() {
			var regEvents = {
				'click .tf_lightbox_link_module-edit' : 'moduleEdit',
				'click .tf_lightbox_link_module-style' : 'moduleStyle',
				'click .tf_lightbox_link_module-duplicate' : 'moduleDuplicate',
				'click .tf_lightbox_link_module-delete' : 'moduleDelete',
				'click .sub_row_delete' : 'subRowDelete',
				'click .sub_row_duplicate' : 'subRowDuplicate',
				'click .tf_grid_list li a' : 'gridMenuClicked',
				'dblclick .active_module' : 'moduleEditDouble',
				'change .tf_row .gutter_select' : 'gutterSelected',
				'click .toggle_row' : 'toggleRow',
				'click .tf_duplicate_row' : 'rowDuplicate',
				'click .tf_delete_row' : 'rowDelete',
				'click .tf_option_row' : 'rowOption',
				'click .tf_styling_row' : 'rowStyling'
			};
			if ( _tf_app.isTouch ) {
				var touchEvents = {
					'touchend .tf_row .tf_row_menu .menu_icon' : 'rowMenuHover',
					'touchend .tf_row .grid_menu .grid_icon' : 'gridMenuHover',
					// Backend builder
					'touchend .tf_module .tf_active_module_menu .menu_icon' : 'moduleMenuHover',
					'touchend .tf_row_top' : 'hideAllDropdown',
					'touchend .active_module' : 'hideAllDropdown'
				};
				regEvents = _.extend( regEvents, touchEvents );
			} else {
				var hoverEvents = {
					'mouseenter .tf_row .tf_row_menu' : 'rowMenuHover',
					'mouseleave .tf_row .tf_row_menu' : 'rowMenuHover',
					'mouseenter .tf_row .grid_menu' : 'gridMenuHover',
					'mouseleave .tf_row .grid_menu' : 'gridMenuHover',
					// Backend builder
					'mouseenter .tf_module .tf_active_module_menu' : 'moduleMenuHover',
					'mouseleave .tf_module .tf_active_module_menu' : 'moduleMenuHover'
				};
				regEvents = _.extend( regEvents, hoverEvents );
			}

			return regEvents;
		},

		draggable: function() {
			$( ".tf_module_panel .tf_module" ).draggable({
				helper: function(event, ui) {
					var name = $(event.currentTarget).data('module-name'),
						title = $(event.currentTarget).data('module-title'),
						tpl = wp.template('tf_module_helper');
					return $( tpl({ name: name, title: title }) );
				},
				revert: 'invalid',
				zIndex: 20000,
				connectToSortable: ".tf_module_holder"
			});
		},

		sortable: function() {
			var $self = this;

			$('.tf_content_builder').sortable({
				items: '.tf_row',
				handle: '.tf_row_top',
				axis: 'y',
				placeholder: 'tf_ui_state_highlight',
				sort: function( event, ui ){
					$('.tf_ui_state_highlight').height(35);
				}
			});

			var $m_holder_args = {
				placeholder: 'tf_ui_state_highlight',
				items: '.active_module, .tf_sub_row',
				connectWith: '.tf_module_holder',
				cursor: 'move',
				revert: 100,
				cursorAt: { top: 20, left: 110 },
				tolerance: 'pointer',
				sort: function( event, ui ){
					$('.tf_module_holder .tf_ui_state_highlight').height(35);
					$('.tf_module_holder .tf_sortable_helper').height(40).width(220);

					if ( !$('#tf_module_panel').hasClass('tf_slide_builder_module_state_down') ) {
						$('#tf_module_panel').addClass('tf_slide_builder_module_state_down');
						$('#tf_module_panel').find('.tf_slide_builder_module_wrapper').slideUp();
					}
				},
				receive: function( event, ui ){
					$self._moduleDroppableText();
					$( this ).parent().find( '.tf_empty_holder_text' ).hide();
				},
				start: function(event, ui) {
					//ThemifyPageBuilder.draggedNotTapped = true;
				},
				stop: function(event, ui) {

					if(leftButtonDown) return false;

					//ThemifyPageBuilder.draggedNotTapped = false;
					if(!ui.item.hasClass('active_module') && !ui.item.hasClass('tf_sub_row') ){
						var module_name = ui.item.find('.tf_module').data('module-name');

						var tpl = wp.template('tf_active_module'),
							mod_title = ui.item.find('.tf_module').data('module-title'),
							markup = tpl( { tf_module_title: mod_title, tf_module: module_name, content: '', atts: JSON.stringify({}), element: '' } ),
							$newElems = $(markup);

						$( this ).parent().find( ".tf_empty_holder_text" ).hide();
						ui.item.replaceWith( $newElems );
						$newElems.data('new', 'yes').find('.tf_lightbox_link_module-edit').trigger('click');

					} else{
						// Make sub_row only can nested one level
						if ( ui.item.hasClass('tf_sub_row') && ui.item.parents('.tf_sub_row').length ) {
							var $clone_for_move = ui.item.find('.active_module').clone();
							$clone_for_move.insertAfter(ui.item);
							ui.item.remove();
						}
						$('.tf_sortable_helper').remove();
					}
					$self.trigger('refresh');

					if ( $self.slidePanelOpen && $('#tf_module_panel').hasClass('tf_slide_builder_module_state_down') ) {
						$('#tf_module_panel').removeClass('tf_slide_builder_module_state_down');
						$('#tf_module_panel').find('.tf_slide_builder_module_wrapper').slideDown();
					}
				}
			};
			if ( 'frontend' == this.mode ) {
				$m_holder_args.handle = '.tf_active_block_overlay, .tf_sub_row_top';
				$m_holder_args.helper = function() {
					return $('<div class="tf_sortable_helper"/>');
				};
			}
			$( ".tf_module_holder" ).sortable( $m_holder_args );
		},

		moduleEdit: function( event ) {
			event.preventDefault();
			var $self = this,
				$holder = $(event.currentTarget).closest('[data-tf-module]'),
				mod_name = $holder.data('tf-module'),
				mod_title = $holder.data('tf-module-title'),
				$atts = $holder.data('tf-atts'),
				content = $holder.data('tf-content');

			// set current active module
			TF.Instance.utilityModel.set( 'current_edit_module', $holder );

			// Run an AJAX request.
			var jqxhr = wp.ajax.post( 'tf_lightbox', {
					method: 'edit',
					type: 'module',
					module: mod_name,
					shortcode_params: $atts,
					shortcode_content: content,
					template_id: _tf_app.post_id,
					nonce: _tf_app.nonce
				}),
				lightbox = new TF.Views.Lightbox( {title: mod_title });
			jqxhr.done(function(data){
				lightbox.load( data );
				if ( lightbox.$el.find('.tf_lightbox_builder_field-init').length > 0 ) {
					var builder_element = new TF.Views.BuilderElement({ el: '.tf_lightbox_builder_field-init'} );
					lightbox.on('remove cancel', function(){
						builder_element.destroy();
					});
				}
				$self._initializeEditor( lightbox.$el );

				// Trigger event
				$('body').trigger( 'tf_on_lightbox_opened', [ lightbox.$el, $holder ] );

				lightbox.on('remove cancel', function(){
					if ( $holder.data('new') ) {
						$holder.remove();
						$self.trigger('refresh');
					}
				});
			});
		},

		moduleEditDouble: function( event ) {
			event.preventDefault();
			$(event.currentTarget).find('.tf_lightbox_link_module-edit').trigger('click');
		},

		// Store the ID of the last styling panel open. Used to save last open panel when a new one opens.
		previous: 0,

		moduleStyle: function( event ) {
			event.preventDefault();
			var $this = $(event.currentTarget),
				$holder = $this.closest('[data-tf-module]'),
				module = $holder.data('tf-module'),
				atts = $holder.data('tf-atts'),
				shortcode_id = !_.isUndefined( atts.sc_id ) ? atts.sc_id : 0;

			// set current active module
			TF.Instance.utilityModel.set( 'current_edit_module', $holder );

			$('.tf_styling_panel').hide();

			if ( _.isUndefined( TF.Instance.stylingPanel[ shortcode_id ] ) ) {
				
				// Check if there was a styling panel previously open
				if ( ! _.isUndefined( TF.Instance.stylingPanel[ this.previous ] ) ) {
					// Save previously open styling panel
					TF.Instance.stylingPanel[ this.previous ].save();
				}
				// Save the id of this styling panel
				this.previous = shortcode_id;

				// Fetch data model
				TF.Instance.styleModel.fetch({
					data: {
						type: 'module',
						module: module,
						shortcode_id: shortcode_id
					},
					success: function( resp ) {
						TF.Instance.stylingPanel[ shortcode_id ].renderSelectorPanel();
					}
				});

				if ( TF.Instance.elementsCollection.get( shortcode_id ) ) {
					var modelCollection = TF.Instance.elementsCollection.get( shortcode_id ),
						model = new TF.Models.ElementStyle( modelCollection.toJSON() );
				} else {
					var model = new TF.Models.ElementStyle();	
				}
				var collection = new TF.Collections.Controls();

				TF.Instance.stylingPanel[ shortcode_id ] = new TF.Views.StylingControl( {model: model, collection: collection} );
			} else {
				TF.Instance.stylingPanel[ shortcode_id ].$el.show();
				// Add class to body indicating that styling panel is now visible
				TF.Views.StylingControl.prototype.toggleStylingPanel( 'visible' );
			}

			if ( this.slidePanelOpen ) {
				$('#tf_module_panel .tf_slide_builder_module_panel').trigger('click');
				// Add class to body indicating that styling panel is now visible
				TF.Views.StylingControl.prototype.toggleStylingPanel( 'visible' );
			}
		},

		moduleDuplicate: function( event ) {
			event.preventDefault();
			var $self = this,
				$target_clone = $(event.currentTarget).closest('[data-tf-module]'),
				mod_name = $target_clone.data('tf-module'),
				$atts = $target_clone.data('tf-atts'),
				content = $target_clone.data('tf-content'),
				mod_title = $target_clone.data('tf-module-title'),
				data_styling = {};

			if ( TF.Instance.elementsCollection.get( $atts.sc_id ) ) {
				data_styling = TF.Instance.elementsCollection.get( $atts.sc_id );
			}
			
			TF.Instance.loader.show();
			// Run an AJAX request.
			var jqxhr = wp.ajax.post( 'tf_shortcode_render', {
				method: 'duplicate',
				type: 'module',
				module: mod_name,
				module_title: mod_title,
				shortcode_params: $atts,
				shortcode_content: content,
				template_id: _tf_app.post_id,
				data_styling: JSON.stringify( data_styling ),
				nonce: _tf_app.nonce
			});
			jqxhr.done(function(data){
				var tpl = wp.template('tf_active_module'),
					newElemns = tpl( {tf_module: data.module, content: data.content, atts: JSON.stringify( data.atts ), tf_module_title: data.caption, element: data.element } );
				
				$(newElemns).insertAfter( $target_clone );
				if ( ! _.isEmpty( data.model ) ) {
					$('head').append(data.styles); // append temp duplicate style
					TF.Instance.elementsCollection.add( data.model, {merge: true} );
				}
				$self.trigger('refresh');
				TF.Instance.loader.hide();
			});			
		},

		moduleDelete: function( event ) {
			event.preventDefault();
			var $this = $(event.currentTarget),
				$holder = $this.closest('[data-tf-module]'),
				atts = $holder.data('tf-atts');

			if ( confirm( _tf_app.module_delete ) ) {
				$(event.currentTarget).closest('.active_module').remove();
				if ( ! _.isUndefined( atts.sc_id ) ) {
					var model = TF.Instance.elementsCollection.get( atts.sc_id );
					if ( _.isObject( model ) ) {
						TF.Instance.elementsCollection.remove(model);
						if ( _.isObject( TF.Instance.stylingPanel[ atts.sc_id ] ) ) {
							TF.Instance.stylingPanel[ atts.sc_id ].destroyThis();
						}
					}
				}

				this.trigger('refresh');
			}
		},

		subRowDelete: function( event ) {
			event.preventDefault();
			if ( confirm( _tf_app.sub_row_delete ) ) {
				$(event.currentTarget).closest('.tf_sub_row').remove();
				this.trigger('refresh');
			}
		},
                GenerateUnique: function () {
                    return Math.random().toString(36).substr(2,7)+Math.random().toString(36).substr(2,8);
                },
		subRowDuplicate: function( event ) {
			event.preventDefault();
                        var $self = this;
			var $sub_row = $(event.currentTarget).closest('.tf_sub_row').clone();
                             var temp_sub =  $sub_row.data('tf-atts');
                                 temp_sub.sc_id = $self.GenerateUnique();
                                 $sub_row.data('tf-atts', temp_sub);
                            $sub_row.find('.tf_col').each(function(){
                                var temp_atts =  $(this).data('tf-atts');
                                    temp_atts.sc_id = $self.GenerateUnique();
                                    $(this).data('tf-atts', temp_atts);
                                        $(this).find('.tf_active_block').each(function(){
                                            var temp_matts =  $(this).data('tf-atts');
                                                var $block = $(this).find('.tf_module_block_'+temp_matts.sc_id);
                                                    $block.removeClass('tf_module_block_'+temp_matts.sc_id);
                                                temp_matts.sc_id = $self.GenerateUnique();
                                                $block.addClass('tf_module_block_'+temp_matts.sc_id);
                                                $(this).data('tf-atts', temp_matts);
                                              
                                        });
                                      
                            });
                    
                        $sub_row.insertAfter($(event.currentTarget).closest('.tf_sub_row'));
			this.trigger('refresh');
		},

		gridMenuClicked: function( event ) {
			event.preventDefault();

			var $self = this,
				$this = $(event.currentTarget),
				set = $this.data('grid'),
				sc_column = 'tf_column',
				handle = $this.data('handle'), $base, is_sub_row = false;

			$this.closest('.tf_grid_list').find('.selected').removeClass('selected');
			$this.closest('li').addClass('selected');

			switch( handle ) {
				case 'module':
					if ( $this.hasClass('grid-layout-fullwidth') ) return;
					
					var sub_row_func = wp.template( 'tf_sub_row'),
						tmpl_sub_row = sub_row_func(),
						$mod_clone = $this.closest('.active_module').clone();
					$mod_clone.find('.grid_menu').remove();
					
					$base = $(tmpl_sub_row).find('.tf_module_holder')
					.html('<div class="tf_empty_holder_text">'+ _tf_app.drop_module_text +'</div>').append($mod_clone).end()
					.insertAfter( $this.closest('.active_module')).find('.tf_sub_row_content');

					$this.closest('.active_module').remove();
					sc_column = 'tf_sub_column';
				break;

				case 'sub_row':
					is_sub_row = true;
					sc_column = 'tf_sub_column';
					$base = $this.closest('.tf_sub_row').find('.tf_sub_row_content');
				break;

				default:
					$base = $this.closest('.tf_row').find('.tf_row_content');
			}

			// Hide the dropdown
			$this.closest('.tf_grid_list_wrapper').hide();

			$.each(set, function(i, v){
				if ( $base.children('.tf_col').eq(i).length > 0 ) {
					var $col = $base.children('.tf_col').eq(i),
                                            temp_atts = $col.data('tf-atts') || {};
					temp_atts.grid = v;
					$col.data('tf-atts', temp_atts );
					$col.removeClass($self.clearClass).addClass( 'tf_col' + v );
				} else {
					// Add column
					$self._addNewColumn( { placeholder: _tf_app.drop_module_text, newclass: 'tf_col' + v, atts: JSON.stringify({grid: v}), shortcode: sc_column }, $base);
				}
			});

			// remove unused column
			if ( set.length < $base.children().length ) {
				$base.children('.tf_col').eq( set.length - 1 ).nextAll().each( function(){
					// relocate active_module
					var modules = $(this).find('.tf_module_holder').first().clone();
					modules.find('.tf_empty_holder_text').remove();
					modules.children().appendTo($(this).prev().find('.tf_module_holder').first());
					$(this).remove(); // finally remove it
				});
			}

			$base.children().removeClass('first last');
			$base.children().first().addClass('first');
			$base.children().last().addClass('last');

			// Update data attributes
			$base.children().each(function( iterate ){
				var temp_atts = $(this).data('tf-atts');
				if ( iterate === 0 ) {
					temp_atts.grid = temp_atts.grid + ' ' + 'first';
				}
				$this.data( 'tf-atts', temp_atts );
			});
                        
                        // set selected grid
                        var $grid = $this.find('img').attr('src').split('/').pop();
                            $grid = $grid.replace('.png','');
                            $grid = $grid.replace(/\.+?/ig,'-');
                        switch( handle ) {
                                case 'module':
                                      var temp = $base.closest('.tf_sub_row').data('tf-atts');
                                      temp.grid = $grid;
                                      $base.closest('.tf_sub_row').data('tf-atts', temp);
                                break;
				case 'sub_row':
					var temp = $this.closest('.tf_sub_row').data('tf-atts');
                                        temp.grid = $grid;
                                        $this.closest('.tf_sub_row').data('tf-atts', temp);
				break;
                                case 'row':
					var temp = $this.closest('.tf_row').data('tf-atts');
					temp.grid = $grid;
                                        $this.closest('.tf_row').data('tf-atts', temp);
				break;
			};
                        
			// remove sub_row when fullwidth column
			if ( is_sub_row && set[0] == 'fullwidth' ) {
				var $move_modules = $base.find('.active_module').clone();
				$move_modules.insertAfter( $this.closest('.tf_sub_row') );
				$this.closest('.tf_sub_row').remove();
			}
                       
			this.trigger('refresh');
		},

		gutterSelected: function( event ) {
			event.preventDefault();

			var $this = $(event.currentTarget),
				handle = $this.data('handle');
			if ( handle == 'module' ) return;

			switch( handle ) {
				case 'sub_row':
					$this.closest('.tf_sub_row').data('gutter', $this.val()).removeClass( TF.Instance.utilityModel.get('gutterClass') ).addClass( $this.val() );
					var temp = $this.closest('.tf_sub_row').data('tf-atts');
					temp.gutter = $this.val();
					$this.closest('.tf_sub_row').data('tf-atts', temp);
				break;

				default:
					$this.closest('.tf_row').data('gutter', $this.val()).removeClass( TF.Instance.utilityModel.get('gutterClass') ).addClass( $this.val() );
					var temp = $this.closest('.tf_row').data('tf-atts');
					temp.gutter = $this.val();
					$this.closest('.tf_row').data('tf-atts', temp);
				break;
			}

			// Hide the dropdown
			$this.closest('.tf_grid_list_wrapper').hide();
		},

		gridMenuHover: function( event ) {
			if ( event.type == 'touchend' ) {
				if ( $(event.currentTarget).next('.tf_grid_list_wrapper').is(':hidden') ) {
					$(event.currentTarget).next('.tf_grid_list_wrapper').show();
				} else {
					$(event.currentTarget).next('.tf_grid_list_wrapper').hide();
				}
				this.trigger('hideDropdown', $(event.currentTarget).next('.tf_grid_list_wrapper') );
			}
			else if(event.type=='mouseenter') {
				$(event.currentTarget).find('.tf_grid_list_wrapper').stop(false,true).show();
			} else if(event.type=='mouseleave' && ( event.toElement || event.relatedTarget ) ) {
				$(event.currentTarget).find('.tf_grid_list_wrapper').stop(false,true).hide();
			}
		},

		_addNewColumn: function( params, $context ) {
			var tmpl_func = wp.template( 'tf_column'),
				template = tmpl_func( params );
			$context.append($(template));
		},

		setupGridMenu: function() {
			var grid_menu_func = wp.template( 'tf_grid_menu' ), 
				tmpl_grid_menu = grid_menu_func({});
			$('.tf_row_content').each(function(){
				$(this).children().each(function(){
					var $holder = $(this).find('.tf_module_holder').first();
					$holder.children('.active_module').each(function(){
						if ( $(this).find('.grid_menu').length == 0 ) {
							$(this).append( $( $.parseHTML( tmpl_grid_menu ) ) );
						}
					});
				});
			});
		},

		toggleRow: function( event ) {
			event.preventDefault();
			$(event.currentTarget).parents('.tf_row').toggleClass('collapsed').find('.tf_row_content').slideToggle();
		},

		_makeEqual: function( $obj, target ) {
			$obj.each(function(){
				var t = 0;
				$(this).find(target).children().each(function(){
					var $holder = $(this).find('.tf_module_holder').first();
					$holder.css('min-height', '');
					if ( $holder.height() > t ) {
						t=$holder.height();
					}
				});
				$(this).find(target).children().each(function(){
					$(this).find('.tf_module_holder').first().css('min-height', t + 'px');
				});
			});
		},

		_equalHeight: function(){
			this._makeEqual( $('.tf_sub_row'), '.tf_sub_row_content');
			this._makeEqual( $('.tf_row'), '.tf_row_content');
		},

		_moduleDroppableText: function() {
			$('.tf_module_holder').each(function(){
				if($(this).find('.active_module').length == 0 && $(this).find('.tf_sub_row').length == 0 ){
					$(this).find('.tf_empty_holder_text').show();
				} else {
					$(this).find('.tf_empty_holder_text').hide();
				}
			});
		},

		_droppableRowElemns: function() {
			var $self = this,
				tpl_row_droppable = wp.template('tf_row_droppable');

			this.$el.append( $( $.parseHTML( tpl_row_droppable({ placeholder: _tf_app.drop_module_text }) ) ) );

			$('.tf_row_droppable', this.$el).droppable({
				hoverClass: 'tf_ui_state_hover',
				accept: '.tf_module_interface, .tf_module, .active_module',
				greedy: true,
				drop: function( event, ui ) {
					
					if(leftButtonDown) return false;

					var tpl_row = wp.template('tf_row');
                           
					if ( ui.draggable.hasClass('active_module') ) {
						var $newElems = ui.draggable.clone(true).removeAttr('style'),
							$data = $(tpl_row({})).find('.tf_module_holder')
							.html('<div class="tf_empty_holder_text">'+ _tf_app.drop_module_text +'</div>').append( $newElems.show() ).end();
					} else {
						var $draggable = ui.draggable.hasClass('tf_module') ? ui.draggable : ui.draggable.find('.tf_module'),
							module_name = $draggable.data('module-name'),
							mod_title = $draggable.data('module-title'),
							tpl = wp.template('tf_active_module'),
							markup = tpl( { tf_module_title: mod_title, tf_module: module_name, content: '', atts: JSON.stringify({}), element: '' } ),
							$newElems = $(markup).data('new', 'yes'),
							$data = $(tpl_row({})).find('.tf_module_holder')
							.html('<div class="tf_empty_holder_text">'+ _tf_app.drop_module_text +'</div>').append( $newElems ).end();
                                                    var $atts = $data.data('tf-atts');
                                                    $data.removeClass('tf_row_block_'+$atts.sc_id);
                                                    $atts.sc_id = $self.GenerateUnique();
                                                    $data.attr('data-tf-atts',JSON.stringify($atts)); 
                                                    $data.addClass('tf_row_block_'+$atts.sc_id);
                                            }
                                            
					$data.insertBefore( $(this) );
					
					if ( ui.draggable.hasClass('active_module') ) {
						ui.draggable.remove();
					} else {
						$newElems.find('.tf_lightbox_link_module-edit').trigger('click');
					}
					
					$self.trigger('refresh');
				}
			});
		},

		rowMenuHover: function( event ) {
			if ( event.type == 'touchend' ) {
				if ( $(event.currentTarget).next('.tf_dropdown').is(':hidden') ) {
					$(event.currentTarget).next('.tf_dropdown').stop(false,true).show();	
				} else {
					$(event.currentTarget).next('.tf_dropdown').stop(false,true).hide();
				}
				this.trigger('hideDropdown', $(event.currentTarget).next('.tf_dropdown') );
			} else if( event.type == 'mouseenter' ) {
				$(event.currentTarget).find('.tf_dropdown').stop(false,true).show();
			} else if(event.type == 'mouseleave' ) {
				$(event.currentTarget).find('.tf_dropdown').stop(false,true).hide();
			}
		},

		rowDuplicate: function( event ) {
			event.preventDefault();
			var $self = this,
				$this = $(event.currentTarget).closest('.tf_row'),
				data = this.getRowData( $this, 0),
				jqxhr = wp.ajax.post( 'tf_shortcode_render', {
					method: 'duplicate',
					type: 'row',
					mode: $self.mode,
					row_data: data,
					template_id: _tf_app.post_id,
					tf_builder_mode: $self.mode,
					nonce: _tf_app.nonce
				});

			TF.Instance.loader.show();
			jqxhr.done(function(data){
				var $newElems = $(data);
				$newElems.insertAfter( $this );

				// Duplicate row styling
				var old_styles = $self.getStylingData( $this, 0),
					new_styles = $self.getStylingData( $newElems, 0),
					map_styles = [];

				if ( ! _.isEmpty( old_styles ) && ! _.isEmpty( new_styles ) ) {
					_.each( old_styles, function( value, key ){
						if ( TF.Instance.elementsCollection.get( value ) ) {
							var old_model = TF.Instance.elementsCollection.get( value ),
								new_model = old_model.clone();
							new_model.set('ID', new_styles[ key ], {silent: true} );
							if ( new_model.get('settings') instanceof Backbone.Collection ) {
								new_model.set('settings', new_model.get('settings').toJSON(), {silent: true} );
							}

							// Add to collection
							TF.Instance.elementsCollection.add( new_model, {merge: true} );
							map_styles.push( new_model.toJSON() );
						}
					});
					
					// Only update live stylesheet on frontend
					if ( 'frontend' == $self.mode ) {
						// Finally get temp stylesheet
						wp.ajax.post( 'tf_generate_temp_stylesheet', {
							data_styling: JSON.stringify( map_styles ),
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
						});
					} else {
						TF.Instance.loader.hide();
					}
				} else {
					TF.Instance.loader.hide();
				}
				$self.trigger('refresh');
			});
		},

		rowDelete: function( event ) {
			event.preventDefault();
			if ( confirm( _tf_app.row_delete ) ) {
				$(event.currentTarget).closest('.tf_row').remove();
				this.trigger('refresh');
			}
		},

		rowOption: function( event ) {
			event.preventDefault();
			var $holder = $(event.currentTarget).closest('[data-tf-shortcode="tf_row"]'),
				shortcode = $holder.data('tf-shortcode'),
				$atts = $holder.data('tf-atts');

			// set current active module
			TF.Instance.utilityModel.set( 'current_edit_module', $holder );

			// Run an AJAX request.
			var jqxhr = wp.ajax.post( 'tf_lightbox', {
					method: 'edit',
					type: 'row_option',
					shortcode: shortcode,
					shortcode_params: $atts,
					template_id: _tf_app.post_id,
					nonce: _tf_app.nonce,
					mode: this.mode,
				}),
				lightbox = new TF.Views.Lightbox( {title: _tf_app.row_option_title });
			jqxhr.done(function(data){
				lightbox.load( data );

				// Trigger event
				$('body').trigger( 'tf_on_lightbox_opened', [ lightbox.$el ] );
			});
		},

		rowStyling: function( event ) {
			event.preventDefault();
			var $holder = $(event.currentTarget).closest('[data-tf-shortcode="tf_row"]'),
				atts = $holder.data('tf-atts'),
				shortcode_id = !_.isUndefined( atts.sc_id ) ? atts.sc_id : 0;

			// set current active module
			TF.Instance.utilityModel.set( 'current_edit_module', $holder );

			$('.tf_styling_panel').hide();

			if ( _.isUndefined( TF.Instance.stylingPanel[ shortcode_id ] ) ) {

				// Check if there was a styling panel previously open
				if ( ! _.isUndefined( TF.Instance.stylingPanel[ this.previous ] ) ) {
					// Save previously open styling panel
					TF.Instance.stylingPanel[ this.previous ].save();
				}
				// Save the id of this styling panel
				this.previous = shortcode_id;
				
				// Fetch data model
				TF.Instance.styleModel.fetch({
					data: {
						type: 'row',
						shortcode_id: shortcode_id
					},
					success: function( resp ) {
						TF.Instance.stylingPanel[ shortcode_id ].renderSelectorPanel();
					}
				});

				if ( TF.Instance.elementsCollection.get( shortcode_id ) ) {
					var modelCollection = TF.Instance.elementsCollection.get( shortcode_id ),
						model = new TF.Models.ElementStyle( modelCollection.toJSON() );
				} else {
					var model = new TF.Models.ElementStyle();	
				}
				var collection = new TF.Collections.Controls();

				TF.Instance.stylingPanel[ shortcode_id ] = new TF.Views.StylingControl( {model: model, collection: collection} );				
			} else {
				TF.Instance.stylingPanel[ shortcode_id ].$el.show();
				// Add class to body indicating that styling panel is now visible
				TF.Views.StylingControl.prototype.toggleStylingPanel( 'visible' );
			}

			if ( this.slidePanelOpen ) {
				$('#tf_module_panel .tf_slide_builder_module_panel').trigger('click');
				// Add class to body indicating that styling panel is now visible
				TF.Views.StylingControl.prototype.toggleStylingPanel( 'visible' );
			}

		},

		_selectedGridMenu: function() {
			var $self = this;
			$('.grid_menu').each(function(){
				var handle = $(this).data('handle'),
					grid_base = [], $base;
				if ( handle == 'module' ) return;
				switch( handle ) {
					case 'sub_row':
						$base = $(this).closest('.tf_sub_row').find('.tf_sub_row_content');
					break;

					default:
						$base = $(this).closest('.tf_row').find('.tf_row_content');
				}

				$base.children().each(function(){
					grid_base.push( $self._getColClass( $(this).prop('class').split(' ') ) );
				});
                               
				$(this).find('.grid-layout-' + grid_base.join('-')).closest('li').addClass('selected');

			});
		},

		_getColClass: function(classes) {
			var matches = this.clearClass.split(' '),
				spanClass = null;
			
			for(var i = 0; i < classes.length; i++) {
				if($.inArray(classes[i], matches) > -1){
					spanClass = classes[i].replace('tf_col', '');
				}
			}
			return spanClass;
		},

		_initializeEditor: function( $context ) {
			var $self = this;
			$('.tf_wp_editor', $context).each(function(){
				var $parent = $(this).parents('.wp-editor-wrap').parent(),
					ori_id = $(this).prop('id'),
					name = $(this).prop('name'),
					val = $(this).val(),
					new_id = ori_id + '_' + $self._randNumber(),
					dom_changes = $parent.html().replace( new RegExp(ori_id, 'g'), new_id );
				
				$parent.html(dom_changes).find('.tf_wp_editor').prop('name', name);
				$self.initNewEditor( new_id );
			});
		},

		initNewEditor: function(editor_id) {
			this.initQuickTags( editor_id );
			if ( typeof tinyMCEPreInit.mceInit[editor_id] !== "undefined" ) {
				this.initMCEv4( editor_id, tinyMCEPreInit.mceInit[editor_id] );
				return;
			}
			var tfb_new_editor_object = tinyMCEPreInit.mceInit['tf_hidden_editor'];
			
			tfb_new_editor_object['elements'] = editor_id;
			tinyMCEPreInit.mceInit[editor_id] = tfb_new_editor_object;

			// v4 compatibility
			this.initMCEv4( editor_id, tinyMCEPreInit.mceInit[editor_id] );
		},

		initMCEv4: function( editor_id, $settings ){
			// v4 compatibility
			if( parseInt( tinyMCE.majorVersion) > 3 ) {
				// Creates a new editor instance
				var ed = new tinyMCE.Editor(editor_id, $settings, tinyMCE.EditorManager);	
				ed.render();
			}
		},

		initQuickTags: function(editor_id) {
			// add quicktags
			if ( typeof(QTags) == 'function' ) {
				quicktags( {id: editor_id} );
				QTags._buttonsInit();
			}
		},

		_randNumber: function() {
			return this.uuid++;
		},

		moduleMenuHover: function( event ) {
			if ( event.type == 'touchend' ) {
				if ( $(event.currentTarget).next('.tf_dropdown').is(':hidden') ) {
					$(event.currentTarget).next('.tf_dropdown').show();
				} else {
					$(event.currentTarget).next('.tf_dropdown').hide();
				}
				this.trigger('hideDropdown', $(event.currentTarget).next('.tf_dropdown') );
			} else if(event.type=='mouseenter') {
				$(event.currentTarget).find('.tf_dropdown').stop(false,true).show();
			} else if(event.type=='mouseleave' && ( event.toElement || event.relatedTarget ) ) {
				$(event.currentTarget).find('.tf_dropdown').stop(false,true).hide();
			}
		},

		_bindEvents: function() {
			$('body').on('click', '.tf_module .add_module', this._addModule );
		},

		_addModule: function( event ) {
			event.preventDefault();
			var module_name = $(event.currentTarget).parent().data('module-name'),
				tpl = wp.template('tf_active_module'),
				mod_title = $(event.currentTarget).parent().data('module-title'),
				markup = tpl( { tf_module_title: mod_title, tf_module: module_name, content: '', atts: JSON.stringify({}), element: '' } ),
				$newElems = $(markup).data('new', 'yes');

			if ( $('.tf_content_builder > .tf_row').length > 0 ) {
				$newElems.appendTo( $('.tf_content_builder > .tf_row').first().find('.tf_module_holder').first() );
			} else {
				var tpl_row = wp.template('tf_row'),
					$data = $(tpl_row({})).find('.tf_module_holder')
					.html('<div class="tf_empty_holder_text">'+ _tf_app.drop_module_text +'</div>').append( $newElems ).end();

				$data.insertBefore( $('.tf_content_builder > .tf_row_droppable') );
			}
			$newElems.find('.tf_lightbox_link_module-edit').trigger('click');
		},

		hideAllDropdown: function( event ) {
			if ( $(event.target).hasClass('tf_row_top') || $(event.target).hasClass('tf_active_block_overlay') ) {
				var $parent = $(event.currentTarget).closest('.tf_row');
				$parent.find('.tf_dropdown').hide()
				$parent.find('.tf_grid_list_wrapper').hide();
			}
		},

		_tipsy: function() {
			if ( ! _.isUndefined( $.fn.tipsy ) ) {
				$('.tf-tooltips', this.$el).tipsy({
					gravity: 's',
					live: true
				});
			}
		}

	});

	_.extend( TF.Views.Builder.prototype, TF.Mixins.Builder );
	
})(jQuery);