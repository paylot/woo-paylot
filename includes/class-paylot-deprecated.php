<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tbz_WC_Paylot_Gateway extends WC_Payment_Gateway {


	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id		   			= 'Paylot';
		$this->method_title 	    = 'Paylot';
		$this->has_fields 	    	= true;

		// Load the form fields
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values
		$this->title 				= $this->get_option( 'title' );
		$this->description 			= $this->get_option( 'description' );
		$this->enabled            	= $this->get_option( 'enabled' );

		$this->payment_page         = $this->get_option( 'payment_page' );

		

		$this->public_key      		= $this->get_option( 'live_public_key' );
		$this->secret_key      		= $this->get_option( 'test_secret_key' );

	

		// Hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

		// Payment listener/API hook
		add_action( 'woocommerce_api_tbz_wc_Paylot_gateway', array( $this, 'verify_Paylot_transaction' ) );

		add_action( 'woocommerce_api_tbz_wc_Paylot_webhook', array( $this, 'process_webhooks' ) );

		// Check if the gateway can be used
		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = false;
		}

	}


	/**
	 * Check if this gateway is enabled and available in the user's country.
	 */
	public function is_valid_for_use() {

		if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_Paylot_supported_currencies', array( 'NGN', 'USD', 'GBP' ) ) ) ) {

			$this->msg = 'Paylot does not support your store currency. Kindly set it to either NGN (&#8358), USD (&#36;) or GBP (&#163;) <a href="' . admin_url( 'admin.php?page=wc-settings&tab=general' ) . '">here</a>';

			return false;

		}

		return true;

	}

	/**
	 * Display Paylot payment icon
	 */
	public function get_icon() {

		$icon  = '<img src="' . WC_HTTPS::force_https_url( plugins_url( '../assets/images/paylot-wc.png' , __FILE__ ) ) . '" alt="cards" />';

		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );

	}


	/**
	 * Check if Paylot merchant details is filled
	 */
	public function admin_notices() {

		if ( $this->enabled == 'no' ) {
			return;
		}

		// Check required fields
		if ( ! ( $this->public_key && $this->secret_key ) ) {
			echo '<div class="error"><p>' . sprintf( 'Please enter your Paylot merchant details <a href="%s">here</a> to be able to use the Paylot WooCommerce plugin.', admin_url( 'admin.php?page=wc-settings&tab=checkout&section=Paylot' ) ) . '</p></div>';
			return;
		}

	}


	/**
	 * Check if this gateway is enabled
	 */
	public function is_available() {

		if ( $this->enabled == "yes" ) {

			if ( ! ( $this->public_key && $this->secret_key ) ) {

				return false;

			}

			return true;
		}

		return false;

	}


    /**
     * Admin Panel Options
    */
    public function admin_options() {

    	

		if ( $this->is_valid_for_use() ){

            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';

        }
		else {	 ?>
			<div class="inline error"><p><strong>Paylot Payment Gateway Disabled</strong>: <?php echo $this->msg ?></p></div>

		<?php }

    }


	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled' => array(
				'title'       => 'Enable/Disable',
				'label'       => 'Enable Paylot',
				'type'        => 'checkbox',
				'description' => 'Enable Paylot as a payment option on the checkout page.',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'title' => array(
				'title' 		=> 'Title',
				'type' 			=> 'text',
				'description' 	=> 'This controls the payment method title which the user sees during checkout.',
    			'desc_tip'      => true,
				'default' 		=> 'Debit/Credit Cards'
			),
			'description' => array(
				'title' 		=> 'Description',
				'type' 			=> 'textarea',
				'description' 	=> 'This controls the payment method description which the user sees during checkout.',
    			'desc_tip'      => true,
				'default' 		=> 'Make payment using your debit and credit cards'
			
			),
			'payment_page' => array(
				'title'       => 'Payment Page',
				'type'        => 'select',
				'description' => 'Inline shows the payment popup on the page while Inline Embed shows the payment page directly on the page',
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''   		=> 'Select One',
					'inline'   	=> 'Inline',
					'embed' 	=> 'Inline Embed'
				)

			),
			'live_secret_key' => array(
				'title'       => 'Live Secret Key',
				'type'        => 'text',
				'description' => 'Enter your Live Secret Key here.',
				'default'     => ''
			),
			'live_public_key' => array(
				'title'       => 'Live Public Key',
				'type'        => 'text',
				'description' => 'Enter your Live Public Key here.',
				'default'     => ''
			),
			'meta_name'  => array(
				'title'       => 'Customer Name',
				'label'       => 'Send Customer Name',
				'type'        => 'checkbox',
				'class'       => 'wc-paylot-meta-name',
				'description' => 'If checked, the customer full name will be sent to Paylot',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'meta_email'  => array(
				'title'       => 'Customer Email',
				'label'       => 'Send Customer Email',
				'type'        => 'checkbox',
				'class'       => 'wc-paylot-meta-email',
				'description' => 'If checked, the customer email address will be sent to Paylot',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'meta_phone'  => array(
				'title'       => 'Customer Phone',
				'label'       => 'Send Customer Phone',
				'type'        => 'checkbox',
				'class'       => 'wc-paylot-meta-phone',
				'description' => 'If checked, the customer phone will be sent to Paylot',
				'default'     => 'no',
				'desc_tip'    => true
			),
			
		);

	}


	/**
	 * Outputs scripts used for Paylot payment
	 */
	public function payment_scripts() {

		if ( ! is_checkout_pay_page() ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'Paylot', 'https://js.Paylot.co/v1/inline.js', array( 'jquery' ), false );

		wp_enqueue_script( 'wc_Paylot', plugins_url( 'assets/js/Paylot.js', WC_Paylot_MAIN_FILE ), array( 'jquery', 'Paylot' ),  false );

		$Paylot_params = array(
			'key'	=> $this->public_key
		);

		if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

			$order_key 		= urldecode( $_GET['key'] );
			$order_id  		= absint( get_query_var( 'order-pay' ) );

			$order    		= wc_get_order( $order_id );

			$email  		= method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;

			$amount 		= $order->get_total() * 100;

			$txnref		 	= $order_id . '_' .time();

			$the_order_id 	= method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
	        $the_order_key 	= method_exists( $order, 'get_order_key' ) ? $order->get_order_key() : $order->order_key;

			if ( $the_order_id == $order_id && $the_order_key == $order_key ) {

				$Paylot_params['email'] 				= $email;
				$Paylot_params['amount']  			= $amount;
				$Paylot_params['txnref']  			= $txnref;
				$Paylot_params['pay_page']  			= $this->payment_page;
				$Paylot_params['currency']  			= get_woocommerce_currency();

			}

			if( $this->custom_metadata ) {

				if( $this->meta_order_id ) {

					$Paylot_params['meta_order_id'] = $order_id;

				}

				if( $this->meta_name ) {

					$first_name  	= method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name;
					$last_name  	= method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name;

					$Paylot_params['meta_name'] = $first_name . ' ' . $last_name;

				}

				if( $this->meta_email ) {

					$Paylot_params['meta_email'] = $email;

				}

				if( $this->meta_phone ) {

					$billing_phone  	= method_exists( $order, 'get_billing_phone' ) ? $order->get_billing_phone() : $order->billing_phone;

					$Paylot_params['meta_phone'] = $billing_phone;

				}

				

				


			}

			update_post_meta( $order_id, '_Paylot_txn_ref', $txnref );

		}

		wp_localize_script( 'wc_Paylot', 'wc_Paylot_params', $Paylot_params );

	}


	/**
	 * Load admin scripts
	 */
	public function admin_scripts() {

		if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
			return;
		}

		

		wp_enqueue_script( 'wc_Paylot_admin', plugins_url( 'assets/js/Paylot-admin.js', WC_Paylot_MAIN_FILE ), array(),  true );

	}


	/**
	 * Process the payment
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true )
		);

	}


	/**
	 * Displays the payment page
	 */
	public function receipt_page( $order_id ) {

		$order = wc_get_order( $order_id );

		if( 'embed' == $this->payment_page ) {

			echo '<p style="text-align: center; font-weight: bold;">Thank you for your order, please make payment below using Paylot.</p>';

			echo '<div id="PaylotWooCommerceEmbedContainer"></div>';

			echo '<div id="Paylot_form"><form id="order_review" method="post" action="'. WC()->api_request_url( 'Tbz_WC_Paylot_Gateway' ) .'"></form>
				<a href="' . esc_url( $order->get_cancel_order_url() ) . '" style="text-align:center; color: #EF3315; display: block; outline: none;">Cancel order &amp; restore cart</a></div>
				';

		} else {

			echo '<p>Thank you for your order, please click the button below to pay with Paylot.</p>';

			echo '<div id="Paylot_form"><form id="order_review" method="post" action="'. WC()->api_request_url( 'Tbz_WC_Paylot_Gateway' ) .'"></form><button class="button alt" id="Paylot-payment-button">Pay Now</button> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">Cancel order &amp; restore cart</a></div>
				';

		}

	}


	/**
	 * Verify Paylot payment
	 */
	public function verify_paylot_transaction() {

		

		@ob_clean();
		
			

		if ( isset( $_REQUEST['paylot_txnref'] ) ){
			

			$paylot_url = 'https://api.paylot.co/transactions/verify/' . $_REQUEST['paylot_txnref'];

			$headers = array(
				'Authorization' => 'Bearer ' . $this->secret_key
			);

			$args = array(
				'headers'	=> $headers,
				'timeout'	=> 60
			);

			$request = wp_remote_get( $paylot_url, $args );

	        if ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {

            	$paylot_response = json_decode( wp_remote_retrieve_body( $request ) );

				if ( true == $paylot_response->sent ) {

					$order_details 	= explode( '_', $paylot_response->reference );

					$order_id 		= (int) $order_details[0];

			        $order 			= wc_get_order( $order_id );

			        if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

			        	wp_redirect( $this->get_return_url( $order ) );

						exit;

			        }

	        		$order_total        = $order->get_total();

					$order_currency     = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();

					$currency_symbol	= get_woocommerce_currency_symbol( $order_currency );

	        		$amount_paid        = $paylot_response->realValue->amount;

	        		$paylot_ref       = $paylot_response->reference;

					$payment_currency   = $paylot_response->realValue->currency;

        			$gateway_symbol     = get_woocommerce_currency_symbol( $payment_currency );

					// check if the amount paid is equal to the order amount.
					if ( $amount_paid < $order_total ) {

						$order->update_status( 'on-hold', '' );

						add_post_meta( $order_id, '_transaction_id', $paylot_ref, true );

						$notice = 'Thank you for shopping with us.<br />Your payment transaction was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
						$notice_type = 'notice';

						// Add Customer Order Note
	                    $order->add_order_note( $notice, 1 );

	                    // Add Admin Order Note
	                    $order->add_order_note( '<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was <strong>&#8358;'.$amount_paid.'</strong> while the total order amount is <strong>&#8358;'.$order_total.'</strong><br />paylot Transaction Reference: '.$paylot_ref );

						function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

						wc_add_notice( $notice, $notice_type );

					} else {

						if( $payment_currency !== $order_currency ) {

							$order->update_status( 'on-hold', '' );

							update_post_meta( $order_id, '_transaction_id', $paylot_ref );

							$notice = 'Thank you for shopping with us.<br />Your payment was successful, but the payment currency is different from the order currency.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
							$notice_type = 'notice';

							// Add Customer Order Note
		                    $order->add_order_note( $notice, 1 );

			                // Add Admin Order Note
		                	$order->add_order_note( '<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Order currency is different from the payment currency.<br /> Order Currency is <strong>'. $order_currency . ' ('. $currency_symbol . ')</strong> while the payment currency is <strong>'. $payment_currency . ' ('. $gateway_symbol . ')</strong><br /><strong>paylot Transaction Reference:</strong> ' . $paylot_ref );

							function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

							wc_add_notice( $notice, $notice_type );

						} else {

							$order->payment_complete( $paylot_ref );

							$order->add_order_note( sprintf( 'Payment via paylot successful (Transaction Reference: %s)', $paylot_ref ) );

						}

					}

					$this->save_card_details( $paylot_response, $order->get_user_id(), $order_id );

					wc_empty_cart();

				} else {

					$order_details 	= explode( '_', $_REQUEST['paylot_txnref'] );

					$order_id 		= (int) $order_details[0];

			        $order 			= wc_get_order( $order_id );

					$order->update_status( 'failed', 'Payment was declined by paylot.' );

				}

	        }

			wp_redirect( $this->get_return_url( $order ) );

			exit;
		}

		wp_redirect( wc_get_page_permalink( 'cart' ) );

		exit;

	}


	/**
	 * Process Webhook
	 */
	public function process_webhooks() {

		if ( ( strtoupper( $_SERVER['REQUEST_METHOD'] ) != 'POST' ) || ! array_key_exists('HTTP_X_Paylot_SIGNATURE', $_SERVER) ) {
			exit;
		}

	    $json = file_get_contents( "php://input" );

		// validate event do all at once to avoid timing attack
		if ( $_SERVER['HTTP_X_Paylot_SIGNATURE'] !== hash_hmac( 'sha512', $json, $this->secret_key ) ) {
			exit;
		}

	    $event = json_decode( $json );

	    if ( 'charge.success' == $event->event ) {

			http_response_code(200);

			$order_details 		= explode( '_', $event->reference );

			$order_id 			= (int) $order_details[0];

	        $order 				= wc_get_order( $order_id );

	        $Paylot_txn_ref 	= get_post_meta( $order_id, '_Paylot_txn_ref', true );

	        if ( $event->data->reference != $Paylot_txn_ref ) {
	        	exit;
	        }

	        if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {
				exit;
	        }

			$order_currency 	= method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();

			$currency_symbol	= get_woocommerce_currency_symbol( $order_currency );

			$order_total		= $order->get_total();
			
			$amount_paid        = $paylot_response->realValue->amount;

			$paylot_ref       = $paylot_response->reference;

			

			// check if the amount paid is equal to the order amount.
			if ( $amount_paid < $order_total ) {

				$order->update_status( 'on-hold', '' );

				add_post_meta( $order_id, '_transaction_id', $Paylot_ref, true );

				$notice = 'Thank you for shopping with us.<br />Your payment transaction was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
				$notice_type = 'notice';

				// Add Customer Order Note
                $order->add_order_note( $notice, 1 );

                // Add Admin Order Note
                $order->add_order_note( '<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was <strong>'. $currency_symbol . $amount_paid . '</strong> while the total order amount is <strong>'. $currency_symbol . $order_total . '</strong><br />Paylot Transaction Reference: '.$Paylot_ref );

				$order->reduce_order_stock();

				wc_add_notice( $notice, $notice_type );

				wc_empty_cart();

			} else {

				$order->payment_complete( $Paylot_ref );

				$order->add_order_note( sprintf( 'Paylot Transaction Ref: %s', $Paylot_ref ) );

				wc_empty_cart();
			}

			exit;
	    }

	    exit;
	}

}