jQuery( function( $ ) {
	'use strict';

	/**
	 * Object to handle Paystack admin functions.
	 */
	var wc_paylot_admin = {
		/**
		 * Initialize.
		 */
		init: function() {

			// Toggle api key settings.
			$( document.body ).on( 'change', '#woocommerce_paylot_testmode', function() {
				var 
					live_secret_key = $( '#woocommerce_paylot_live_secret_key' ).parents( 'tr' ).eq( 0 ),
					live_public_key = $( '#woocommerce_paylot_live_public_key' ).parents( 'tr' ).eq( 0 );

				if ( $( this ).is( ':checked' ) ) {
					
					live_secret_key.hide();
					live_public_key.hide();
				} else {
					live_secret_key.show();
					live_public_key.show();
				}
			} );

			$( '#woocommerce_paylot_testmode' ).change();

			// Toggle Custom Metadata settings.
			$( '.wc-paylot-metadata' ).change( function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '.wc-paylot-meta-order-id, .wc-paylot-meta-name, .wc-paylot-meta-email, .wc-paylot-meta-phone, .wc-paylot-meta-billing-address, .wc-paylot-meta-shipping-address, .wc-paylot-meta-products' ).closest( 'tr' ).show();
				} else {
					$( '.wc-paylot-meta-order-id, .wc-paylot-meta-name, .wc-paylot-meta-email, .wc-paylot-meta-phone, .wc-paylot-meta-billing-address, .wc-paylot-meta-shipping-address, .wc-paylot-meta-products' ).closest( 'tr' ).hide();
				}
			}).change();

		

		}
	};

	function formatPaystackPaymentIcons (payment_method) {
		if (!payment_method.id) { return payment_method.text; }
		var $payment_method = $(
			'<span><img src=" ' + wc_paylot_admin_params.plugin_url + '/assets/images/' + payment_method.element.value.toLowerCase() + '.png" class="img-flag" style="height: 15px; weight:18px;" /> ' + payment_method.text + '</span>'
		);
		return $payment_method;
	};

	wc_paylot_admin.init();

});
