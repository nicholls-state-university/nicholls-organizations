jQuery(function () {
	jQuery(document).on('click', '.nicholls-org-popup-close', function (e) {
		jQuery.magnificPopup.close();
	});
});

jQuery( document ).ready( function() {

	jQuery('.nicholls-org-modal-email').magnificPopup({

          items: {
			  type: 'inline',
			  preloader: false,
			  midClick: true,
			  focus: '#name',
			  src: '#nicholls-org-form-email'
          },

		// When elemened is focused, some mobile browsers in some cases zoom in
		// It looks not nice, so we disable it:
		callbacks: {
			beforeOpen: function() {
				this.st.focus = '#nicholls-org-form-email-name';
				jQuery( '.nicholls-org-form-email-message' ).html( '<p></p>' );
				
				var the_link = jQuery( this.st.el ).attr('href'); 
				
				var the_address = the_link.replace( 'mailto:', '' );
				the_address = the_address.replace( '%40', '@' );
				
				var the_heading = '<strong>Send email to ' + '<a class="nicholls-org-popup-close" href="mailto:' + the_address + '">' + the_address + '</a>' + '</strong>';
							
				jQuery( 'input[name="nicholls-org-form-email-addr"]' ).val( the_address );
				jQuery( '#nicholls-org-form-message-top' ).html( the_heading );	
				
			},
			beforeClose: function() {
				jQuery( '.nicholls-org-form-email-message p' ).html( '' );
			}
		
		}
	});
	
	jQuery( ".nicholls-org-form-email-ajax-image").hide();
	
	jQuery( "#nicholls-org-form-email" ).submit( function(event) {
	
		event.preventDefault();	
		jQuery( ".nicholls-org-form-email-ajax-image").show();		
		
		var posting = jQuery.ajax({
			type: 'POST',
			url: nicholls_org_js_obj.ajaxurl,
			data: jQuery("#nicholls-org-form-email :input").serialize(),
			beforeSend: function( xhr, options ) {
				if ( !jQuery( 'input[name="nicholls-org-form-email-name"]' ).val() ) {
					jQuery(".nicholls-org-form-email-message p").html('Please input your name.');
					jQuery( ".nicholls-org-form-email-ajax-image").hide();
					xhr.abort();
				}
				if ( !jQuery( 'input[name="nicholls-org-form-email-email"]' ).val() ) {
					jQuery(".nicholls-org-form-email-message p").html('Please input your working email address.');
					jQuery( ".nicholls-org-form-email-ajax-image").hide();
					xhr.abort();
				}
				if ( !jQuery( '#nicholls-org-form-email-message' ).val() ) {
					jQuery(".nicholls-org-form-email-message p").html('Please input your message.');
					jQuery( ".nicholls-org-form-email-ajax-image").hide();
					xhr.abort();
				}							 
			},
			success: function( response ) {
				jQuery( ".nicholls-org-form-email-ajax-image").hide();
				jQuery(".nicholls-org-form-email-message p").html('Thanks. Your Message Sent Successfully. <a class="nicholls-org-popup-close">[Close]</a>');
				console.log( response );
			},
			error: function( response ) {
				jQuery( ".nicholls-org-form-email-ajax-image").hide();
				jQuery(".nicholls-org-form-email-message p").html('Sorry, something went wrong. Please <a href="//www.nicholls.edu/contact">contact us</a>.' );
				console.log( response );
			},
			dataType: 'json'
		});
				
	} );
	
});