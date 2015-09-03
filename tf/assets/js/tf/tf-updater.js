// Flow - Updater Script
;(function($, window, document, undefined) {

	'use strict';

	var _updater_el;

	function showLogin(status){
		$('.prompt-box .show-login').show();
		$('.prompt-box .show-error').hide();
		if(status == 'error'){
			if($('.prompt-box .prompt-error').length == 0){
				$('.prompt-box .prompt-msg').after('<p class="prompt-error">' + tfUpdater.invalid_login + '</p>');
			}
		} else {
			$('.prompt-box .prompt-error').remove();
		}
		$('.prompt-box').addClass( 'update-theme' );
		$('.overlay, .prompt-box').fadeIn(500);	
	}	
	function hideLogin(){
		$('.overlay, .prompt-box').fadeOut(500);
	}
	function showAlert(){
		$('.alert').addClass('busy').fadeIn(800);
	}
	function hideAlert(status){
		if(status == 'error'){
			status = 'error';
			showErrors();
		} else {
			status = 'done';	
		}
		$('.alert').removeClass('busy').addClass(status).delay(800).fadeOut(800, function(){
			$(this).removeClass(status);											   
		});
	}
	function showErrors(verbose){
		$('.overlay, .prompt-box').delay(900).fadeIn(500);	
		$('.prompt-box .show-error').show();
		$('.prompt-box .show-error p').remove();
		$('.prompt-box .error-msg').after('<p class="prompt-error">' + verbose + '</p>');
		$('.prompt-box .show-login').hide();
	}
	
	//
	// Upgrade Theme / Framework
	//
	$('.tf-upgrade-theme').on('click', function(e){
		e.preventDefault();
		_updater_el = $(this);
		if ( confirm( tfUpdater.confirm_update ) ) {
			if ( $(this).parent().hasClass( 'login' ) ) {
				showLogin();
			} else {
				$('#themify_update_form').append( '<input type="hidden" name="theme" value="'+ _updater_el.data( 'theme' ) +'" /><input type="hidden" name="package_url" value="'+ _updater_el.data( 'package_url' ) +'" />' ).submit();
				
			}
		}
	});
	
	//
	// Login Validation
	//
	$('.tf-upgrade-login').on('click', function(e){
		e.preventDefault();
		if ( $('.prompt-box').hasClass( 'update-theme' ) ) {
			var el = $(this), 
				username = el.parent().parent().find('.username').val(),
				password = el.parent().parent().find('.password').val(),
				login = el.closest( '.notifications' ).find( '.update' ).hasClass('login');
			if(username != "" && password != ""){
				hideLogin();
				showAlert();
				$.post(
					ajaxurl,
					{
						'action':'tf_validate_login',
						'type':'theme',
						'login':login,
						'username':username,
						'password':password,
						'nicename_short': _updater_el.data( 'nicename_short' ),
						'update_type': _updater_el.data( 'update_type' )
					},
					function(data){
						data = $.trim(data);
						if(data == 'true'){
							hideAlert();
							$('#themify_update_form').append( '<input type="hidden" name="theme" value="'+ _updater_el.data( 'theme' ) +'" /><input type="hidden" name="package_url" value="'+ _updater_el.data( 'package_url' ) +'" />' ).submit();
						} else if(data == 'false') {
							hideAlert('error');	
							showLogin('error');
						}
					}
				);																					
			} else {
				hideAlert('error');	
				showLogin('error');							   
			}
		}
	});
	//
	// Hide Overlay
	//
	$('.overlay').on('click', function(){
		hideLogin();
	});

	$('.themify_changelogs').on('click', function(e){
		e.preventDefault();
		var $self = $(this),
			url = $self.data('changelog');
		$('.show-login, .show-error').hide();
		$('.alert').addClass('busy').fadeIn(300);
		$('.overlay, .prompt-box').fadeIn(300);
		var $iframe = $('<iframe src="'+url+'" />');
		$iframe.on('load', function(){
			$('.alert').removeClass('busy').fadeOut(300);
		}).prependTo( '.prompt-box' );
		$('.prompt-box').addClass('show-changelog');

	});

}(jQuery, window, document));