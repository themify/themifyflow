(function($){

	'use strict';

	var leftButtonDown;

	$(document).mousedown(function(e){
		if(e.which === 1) leftButtonDown = true;
	}).mouseup(function(e){
		if(e.which === 1) leftButtonDown = false;
	});

	// Builder Element View
	TF.Views.BuilderElement = Backbone.View.extend({

		clearClass: 'tf_col6-1 tf_col5-1 tf_col4-1 tf_col4-2 tf_col4-3 tf_col3-1 tf_col3-2 tf_col2-1 tf_colfullwidth',

		gridClass: ['tf_colfullwidth', 'tf_col4-1', 'tf_col4-2', 'tf_col4-3', 'tf_col3-1', 'tf_col3-2', 'tf_col6-1', 'tf_col5-1'],

		gutterClass: 'tf_gutter_default tf_gutter_narrow tf_gutter_none',

		uuid: 0, // Create a universally unique identifier.

		initialize: function( options ) {
			this.options = options || {};
			this.render();
			this.on('refresh', this.refresh);
			this.on('hideDropdown', function( $activeObject ){
				this.$el.find('.tf_dropdown').not( $activeObject ).hide()
				this.$el.find('.tf_grid_list_wrapper').not( $activeObject ).hide();
			});
		},

		events: function() {
			var regEvents = {
				'click .tf_delete_module' : 'deleteModule',
				'click .tf_toggle_module' : 'toggleModule',
				'click .tf_grid_list li a' : 'gridMenuClicked',
				'change .gutter_select' : 'gutterSelected',
				'click .tf_toggle_row' : 'toggleRow',
				'click .tf_back_delete_row' : 'rowDelete'
			};

			if ( _tf_app.isTouch ) {
				var touchEvents = {
					'touchend .tf_grid_menu .tf_row_btn' : 'gridMenuHover',
					'touchend .tf_back_row_menu .tf_menu_icon' : 'rowMenuHover',
				};
				regEvents = _.extend( regEvents, touchEvents );
			} else {
				var hoverEvents = {
					'mouseenter .tf_grid_menu' : 'gridMenuHover',
					'mouseleave .tf_grid_menu' : 'gridMenuHover',
					'mouseenter .tf_back_row_menu' : 'rowMenuHover',
					'mouseleave .tf_back_row_menu' : 'rowMenuHover'
				};
				regEvents = _.extend( regEvents, hoverEvents );
			}
			return regEvents;
		},

		render: function() {
			this.draggable();
			this.sortable();
			this._moduleDroppableText();
			this._droppableRowElemns();
			this.Unique($('#tf_module_form .tf_back_row_panel .tf_active_module'));
			this.ClearNamesOnSubmit();
		},

		refresh: function() {
			this.sortable();
			this._moduleDroppableText();
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

		draggable: function(){
			$( '.tf_back_module_panel .tf_back_module', this.$el ).draggable({
				helper: function(event, ui) {
					var slug = $(event.currentTarget).data('element-slug'),
						title = $(event.currentTarget).data('element-title'),
						tpl = wp.template('tf_back_module_helper');
					return $( tpl({ slug: slug, title: title }) );
				},
				revert: 'invalid',
				zIndex: 20000,
				connectToSortable: ".tf_back_row_panel .tf_module_holder"
			});
		},

		sortable: function() {
			var $self = this;
			$('.tf_back_row_panel', this.$el).sortable({
				items: '.tf_back_row:not(.tf_row_droppable)',
				handle: '.tf_back_row_top',
				axis: 'y',
				placeholder: 'tf_ui_state_highlight',
				sort: function( event, ui ){
					$('.tf_ui_state_highlight').height(35);
				},
				start: function( event, ui ) {
					$self._mceRefresh( ui.item, 'remove');
				},
				stop: function( event, ui ){
					$self._mceRefresh( ui.item, 'add');
				}
			});

			$( '.tf_module_holder', this.$el ).sortable({
				placeholder: 'tf_ui_state_highlight',
				items: '.tf_active_module',
				connectWith: this.$el.find('.tf_module_holder'),
				cursor: 'move',
				revert: 100,
				handle: '.tf_back_module_top',
				cursorAt: { top: 20, left: 110 },
				tolerance: 'pointer',
				helper: function() {
					return $('<div class="tf_sortable_helper"/>');
				},
				sort: function( event, ui ){
					$('.tf_module_holder .tf_ui_state_highlight').height(35);
					$('.tf_module_holder .tf_sortable_helper').height(40).width(220);
				},
				receive: function( event, ui ){
					$self._moduleDroppableText();
					$( this ).parent().find( '.tf_empty_holder_text' ).hide();
				},
				start: function(event, ui) {
					$self._mceRefresh( ui.item, 'remove');
				},
				stop: function(event, ui) {

					if(leftButtonDown) return false;

					var parent = ui.item.parent();
					if(!ui.item.hasClass('tf_active_module') && !ui.item.hasClass('tf_sub_row') ){
						var element_name = ui.item.find('.tf_back_module').data('element-slug');

						var tpl = wp.template('tf_active_element_' + element_name),
							markup = tpl(),
							$newElems = $(markup);
                                                $newElems.find('select').wrap('<div class="tf_custom_select"/>');
						$( this ).parent().find( ".tf_empty_holder_text" ).hide();
                                                $self.Unique($newElems);
                                                ui.item.replaceWith( $newElems );
						$self._initializeEditor( $newElems );
					} else{
						$self._mceRefresh( ui.item, 'add');
						// Make sub_row only can nested one level
						if ( ui.item.hasClass('tf_sub_row') && ui.item.parents('.tf_sub_row').length ) {
							var $clone_for_move = ui.item.find('.tf_active_module').clone();
							$clone_for_move.insertAfter(ui.item);
							ui.item.remove();
						}

						$('.tf_sortable_helper').remove();
					}

					$self.trigger('refresh');
				}
			});
		},

		deleteModule: function( event ) {
			event.preventDefault();
			$( event.currentTarget ).closest('.tf_active_module').remove();
		},

		toggleModule: function( event ) {
			event.preventDefault();
			$(event.currentTarget).parents('.tf_active_module').toggleClass('collapsed').find('.tf_back_active_module_content').slideToggle();
		},

		gridMenuClicked: function( event ) {
			event.preventDefault();

			var $self = this,
				$this = $(event.currentTarget),
				set = $this.data('grid'),
				sc_column = 'tf_back_column',
				handle = $this.data('handle'), $base, is_sub_row = false;

			$this.closest('.tf_grid_list').find('.selected').removeClass('selected');
			$this.closest('li').addClass('selected');

			switch( handle ) {
				case 'module':
					var sub_row_func = wp.template( 'tf_sub_row'),
						tmpl_sub_row = sub_row_func( {placeholder: _tf_app.drop_module_text, newclass: 'tf_colfullwidth' } ),
						$mod_clone = $this.closest('.active_module').clone();
					$mod_clone.find('.grid_menu').remove();
					
					$base = $(tmpl_sub_row).find('.tf_module_holder').append($mod_clone).end()
					.insertAfter( $this.closest('.active_module')).find('.tf_sub_row_content');

					$this.closest('.active_module').remove();
					sc_column = 'tf_sub_column';
				break;

				case 'sub_row':
					is_sub_row = true;
					sc_column = 'tf_back_sub_column';
					$base = $this.closest('.tf_sub_row').find('.tf_sub_row_content');
				break;

				default:
					$base = $this.closest('.tf_back_row').find('.tf_back_row_content');
			}

			// Hide the dropdown
			$this.closest('.tf_grid_list_wrapper').hide();

			$.each(set, function(i, v){
				if ( $base.children('.tf_back_col').eq(i).length > 0 ) {
					var $col = $base.children('.tf_back_col').eq(i),
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
				$base.children('.tf_back_col').eq( set.length - 1 ).nextAll().each( function(){
					$self._mceRefresh( $(this), 'remove');

					// relocate active_module
					var modules = $(this).find('.tf_module_holder').first().clone(),
						$targetEl = $(this).prev().find('.tf_module_holder').first();
					modules.find('.tf_empty_holder_text').remove();
					modules.children().appendTo($targetEl);
					$(this).remove(); // finally remove it
					
					$self._mceRefresh( $targetEl, 'add');
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
                        var temp = $this.closest('.tf_back_row').data('tf-atts') || {};
                            temp.grid = $grid;
                            $this.closest('.tf_back_row').data('tf-atts', temp);
			// remove sub_row when fullwidth column
			if ( is_sub_row && set[0] == 'fullwidth' ) {
				var $move_modules = $base.find('.tf_active_module').clone();
				$move_modules.insertAfter( $this.closest('.tf_sub_row') );
				$this.closest('.tf_sub_row').remove();
			}

			this.trigger('refresh');
		},

		gridMenuHover: function( event ) {
			if ( event.type == 'touchend' ) {
				if ( $(event.currentTarget).next('.tf_grid_list_wrapper').is(':hidden') ) {
					$(event.currentTarget).next('.tf_grid_list_wrapper').show();
				} else {
					$(event.currentTarget).next('.tf_grid_list_wrapper').hide();
				}
				this.trigger('hideDropdown', $(event.currentTarget).next('.tf_grid_list_wrapper') );
			} else if(event.type=='mouseenter') {
				$(event.currentTarget).find('.tf_grid_list_wrapper').stop(false,true).show();
			} else if(event.type=='mouseleave' && ( event.toElement || event.relatedTarget ) ) {
				$(event.currentTarget).find('.tf_grid_list_wrapper').stop(false,true).hide();
			}
		},

		gutterSelected: function( event ) {
			event.preventDefault();

			var $this = $(event.currentTarget),
				handle = $this.data('handle'),
				val = $this.val();
			if ( handle == 'module' ) return;

			switch( handle ) {
				case 'sub_row':
					$this.closest('.tf_sub_row').data('gutter', val).removeClass( this.gutterClass ).addClass( val );
					var temp = $this.closest('.tf_sub_row').data('tf-atts');
					temp.gutter = val;
					$this.closest('.tf_sub_row').data('tf-atts', temp);
				break;

				default:
					$this.closest('.tf_back_row').data('gutter', val).removeClass( this.gutterClass ).addClass( val );
					var temp = $this.closest('.tf_back_row').data('tf-atts') || {};
					temp.gutter = val;
					$this.closest('.tf_back_row').data('tf-atts', temp);
				break;
			}

			// Hide the dropdown
			$this.closest('.tf_grid_list_wrapper').hide();
		},

		_moduleDroppableText: function() {
			$('.tf_module_holder', this.$el).each(function(){
				if($(this).find('.tf_active_module').length === 0 && $(this).find('.tf_sub_row').length === 0 ){
					$(this).find('.tf_empty_holder_text').show();
				} else {
					$(this).find('.tf_empty_holder_text').hide();
				}
			});
		},

		_droppableRowElemns: function() {
			var $self = this,
				tpl_row_droppable = wp.template('tf_row_droppable');

			this.$el.find('.tf_back_row_panel').append( $( tpl_row_droppable({ class: 'tf_back_row', placeholder: _tf_app.drop_module_text }) ) );

			$('.tf_row_droppable', this.$el).droppable({
				hoverClass: 'tf_ui_state_hover',
				accept: '.tf_back_module_interface, .tf_back_module, .tf_active_module',
				greedy: true,
				drop: function( event, ui ) {
					
					if(leftButtonDown) return false;

					var tpl_row = wp.template('tf_element_row');

					if ( ui.draggable.hasClass('tf_active_module') ) {
						var $newElems = ui.draggable.clone(true),
							$data = $(tpl_row({})).find('.tf_module_holder').append( $newElems.show() ).end();
					} else {
						var $draggable = ui.draggable.hasClass('tf_back_module') ? ui.draggable : ui.draggable.find('.tf_back_module'),
							module_name = $draggable.data('element-slug'),
							tpl = wp.template('tf_active_element_' + module_name),
							markup = tpl(),
							$newElems = $(markup),
							$data = $(tpl_row({})).find('.tf_module_holder').append( $newElems ).end();
					}

					$data.insertBefore( $(this) );
					$self._initializeEditor( $data );
					
					if ( ui.draggable.hasClass('tf_active_module') ) {
						ui.draggable.remove();
					}
					
					$self.trigger('refresh');
				}
			});
		},

		_addNewColumn: function( params, $context ) {
			var tmpl_func = wp.template( 'tf_back_column'),
				template = tmpl_func( params );
			$context.append($(template));
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

		toggleRow: function( event ) {
			event.preventDefault();
			$(event.currentTarget).parents('.tf_back_row').toggleClass('collapsed').find('.tf_back_row_content').slideToggle();
		},

		_randNumber: function() {
			return this.uuid++;
		},

		rowMenuHover: function( event ) {
			if ( event.type == 'touchend' ) {
				if ( $(event.currentTarget).next('.tf_dropdown').is(':hidden') ) {
					$(event.currentTarget).next('.tf_dropdown').stop(false,true).show();	
				} else {
					$(event.currentTarget).next('.tf_dropdown').stop(false,true).hide();
				}
				this.trigger('hideDropdown', $(event.currentTarget).next('.tf_dropdown') );
			} else if( event.type=='mouseenter' ) {
				$(event.currentTarget).find('.tf_dropdown').stop(false,true).show();
			} else if(event.type=='mouseleave') {
				$(event.currentTarget).find('.tf_dropdown').stop(false,true).hide();
			}
		},

		rowDelete: function( event ) {
			event.preventDefault();
			if ( confirm( _tf_app.row_delete ) ) {
				$(event.currentTarget).closest('.tf_back_row').remove();
				this.trigger('refresh');
			}
		},

		_mceRefresh: function( $context, mode ) {
			$('.tf_wp_editor', $context).each(function(){
				var id = $(this).attr('id');
				if ( 'remove' == mode ) {
					tinyMCE.execCommand('mceRemoveEditor', false, id);
				} else if ( 'add' == mode ) {
					tinyMCE.execCommand('mceAddEditor', false, id);
				}
			});
		},

		destroy: function() {
			this.remove();
		},
                
                Escape: function ($selector) {
                    return $selector.replace(/(:|\.|\[|\]|,)/g, "\\$1");
                },
                
                GenerateUnique: function () {
                    return Math.random().toString(36).substr(2, 9);
                },
                
                Unique: function ($module) {
                    var $self = this;
                    $module.each(function () {
                        var $m = $(this);
                        var $labels = $m.find('label');
                        $labels.each(function () {
                            var $id = $(this).attr('for');
                            if ($id) {
                                $id = $self.Escape($id);
                                if ($('#' + $id).length > 0) {
                                    var $uniqud = $self.GenerateUnique();
                                    $m.find('#' + $id).attr('id', $uniqud);
                                    $(this).attr('for', $uniqud);
                                }
                            }
                        });
                       
                        var $input = $m.find('input[type="radio"]');
                        
                        $input.each(function () {
                            var $name = $(this).attr('name');
                            if ($name) {
                                
                                var $uniqeuname = $self.GenerateUnique();
                                var $radio = $m.find('input:radio[name="' + $name + '"]');//if there are several groups radio input
                                var $new_name = $uniqeuname +'['+ $name+']';
                                $radio.attr('name', $new_name);
                                if ($m.find('input:radio[name!="' + $name + '"]')) {//if empty
                                    $m.find('input:radio[name="' + $new_name + '"][checked]').prop('checked', true);//to display checked;
                                    return false;
                                }
                               
                            }
                        });
                    });
                },
                ClearNamesOnSubmit:function(){      //change radio names back
                    $('#tf_module_form').submit(function(e){
                        e.preventDefault();
                         var $self = $(this);
                         var $reg = /.*?\[(.+?)\]/i;
                         var $inputs = $self.find('.tf_back_row_panel .tf_active_module input[type="radio"]:checked');
                       $inputs.each(function(){
                            var $name = $(this).attr('name');
                            if($name){
                                var $match = $name.match($reg);
                                if ($match) {
                                    $(this).after('<input type="hidden" name="'+$match[1]+'" value="'+$(this).val()+'" />');
                                    $self.find('input:radio[name="' + $name + '"]').removeAttr('name');
                                }
                            }
                         });
                    });
                }
	});
})(jQuery);