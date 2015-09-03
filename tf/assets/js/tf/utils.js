(function($){

	'use strict';

	TF.Utils.plupload = function( params, callback ) {
		params = params || {};
		callback = callback || function(){
			$('.tf_close_lightbox').trigger('click');
			window.location.reload();
		};

		var $builderPluploadUpload = $(".tf-plupload-upload-uic");

		if($builderPluploadUpload.length > 0) {
			var pconfig = false;
			$builderPluploadUpload.each(function() {
				var $this = $(this),
					id1 = $this.attr("id"),
					imgId = id1.replace("tf-plupload-upload-ui", "");

				pconfig = JSON.parse( JSON.stringify( _tf_app_plupload ) );
				
				pconfig["browse_button"] = imgId + pconfig["browse_button"];
				pconfig["container"] = imgId + pconfig["container"];
				pconfig["drop_element"] = imgId + pconfig["drop_element"];
				pconfig["file_data_name"] = imgId + pconfig["file_data_name"];
				pconfig["multipart_params"]["imgid"] = imgId;
				pconfig["multipart_params"]["nonce"] = _tf_app.nonce;
				pconfig["multipart_params"] = _.extend( params, pconfig["multipart_params"] );

				var uploader = new plupload.Uploader( pconfig );

				uploader.bind('Init', function(up){});

				uploader.init();

				// a file was added in the queue
				uploader.bind('FilesAdded', function(up, files){
					up.refresh();
					up.start();
					// show loader
					if ( ! _.isUndefined( TF.Instance.loader ) ) {
						TF.Instance.loader.show();
					}
				});

				uploader.bind('Error', function(up, error){
					var $promptError = $('.prompt-box .show-error');
					$('.prompt-box .show-login').hide();
					$promptError.show();
					
					if($promptError.length > 0){
						$promptError.html('<p class="prompt-error">' + error.message + '</p>');
					}
					$(".overlay, .prompt-box").fadeIn(500);
					if ( ! _.isUndefined( TF.Instance.loader ) ) {
						TF.Instance.loader.hide();
					}
				});

				// a file was uploaded
				uploader.bind('FileUploaded', function(up, file, response) {
					var json = JSON.parse(response['response']), status;

					if('200' == response['status'] && !json.error) {
						status = 'done';
					} else {
						status = 'error';
					}
					
					$("#themify_builder_alert").removeClass("busy").addClass(status).delay(800).fadeOut(800, function() {
						$(this).removeClass(status);
					});
					
					if(json.error){
						alert(json.error.replace(/\\n/g,"\n"));
						if ( ! _.isUndefined( TF.Instance.loader ) ) {
							TF.Instance.loader.hide();
						}
						return;
					}

					if ( _.isFunction( callback ) ) {
						callback.call(this, json);
					}
					
				});

			});
		}
	};
})(jQuery);