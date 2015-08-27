(function( $ ) {
	'use strict';

	function testAPIKey(){
						//just request one content item (a fast call to test if everything works)
						var params = { page_size: "1" };

						//get content to update title and excerpt
						jQuery.ajax({
								contentType: 'application/json',
								dataType: 'json',
								headers: {"Authorization":jQuery('#xamoom_api_key').val()},
								success: function(data){
																				jQuery('#xamoom_api_key').css('border-style','solid');
																				jQuery('#xamoom_api_key').css('border-color','#00ff00');
																			},
								error: function(){
																	jQuery('#xamoom_api_key').css('border-style','solid');
																	jQuery('#xamoom_api_key').css('border-color','#ff0000');
																},
								type: 'GET',
								url: xamoom_api_endpoint  + 'content?' + jQuery.param(params)
						});
				}

				jQuery( document ).ready(function() {
					testAPIKey(); //test API key on page load
					jQuery('#xamoom_api_key').focusout(function() {
				    testAPIKey();
				  });
				});

})( jQuery );
