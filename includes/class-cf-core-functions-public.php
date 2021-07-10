<?php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that holds all the hooks regarding all the custom functionalities.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Core_Functions_Public
 * @subpackage Core_Functions_Public/includes
 */

/**
 * The core plugin class.
 *
 * A class definition that holds all the hooks regarding all the custom functionalities.
 *
 * @since      1.0.0
 * @package    Core_Functions
 * @author     Adarsh Verma <adarsh@cmsminds.com>
 */
class Cf_Core_Functions_Public {
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Load all the hooks here.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'cf_wp_enqueue_scripts_callback' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'cf_woocommerce_payment_gateways_callback' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'cf_woocommerce_checkout_process_callback' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'cf_woocommerce_checkout_update_order_meta_callback' ) );
		add_filter( 'cf_archway_payment_args', array( $this, 'cf_cf_archway_payment_args_callback' ) );
		add_filter( 'woocommerce_checkout_posted_data', array( $this, 'cf_woocommerce_checkout_posted_data_callback' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'cf_woocommerce_checkout_order_processed_callback' ), 20, 2 );
	}

	/**
	 * Enqueue scripts for public end.
	 */
	public function cf_wp_enqueue_scripts_callback() {
		// Custom public style.
		wp_enqueue_style(
			'core-functions-public-style',
			CF_PLUGIN_URL . 'assets/public/css/core-functions-public.css',
			array(),
			filemtime( CF_PLUGIN_PATH . 'assets/public/css/core-functions-public.css' ),
		);

		// Custom public script.
		wp_enqueue_script(
			'core-functions-public-script',
			CF_PLUGIN_URL . 'assets/public/js/core-functions-public.js',
			array( 'jquery' ),
			filemtime( CF_PLUGIN_PATH . 'assets/public/js/core-functions-public.js' ),
			true
		);

		// Localize public script.
		wp_localize_script(
			'core-functions-public-script',
			'CF_Public_JS_Obj',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Register Archway payment gateway with WooCommerce.
	 *
	 * @param array $methods WC registered payemnt methods.
	 * @return array
	 * @since 1.0.0 
	 */
	public function cf_woocommerce_payment_gateways_callback( $methods ) {
	    $methods[] = 'WooCommerce_Archway_Payment_Gateway';

		return $methods;
	}

	/**
	 * Fire the payment API now.
	 *
	 * @since 1.0.0
	 */
	public function cf_woocommerce_checkout_process_callback() {
		// Get the selected payment method.
		$payment_method = filter_input( INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING );

		// If it's not the archway payment gateway, return.
		if ( ! empty( $payment_method ) && 'archway_payments' !== $payment_method ) {
			return;
		}

		// Get the card payment details now.
		$card_number  = filter_input( INPUT_POST, 'archway-card-number', FILTER_SANITIZE_STRING );
		$card_number  = ( ! empty( $card_number ) ) ? str_replace( '-', '', $card_number ) : '';
		$card_holder  = filter_input( INPUT_POST, 'archway-card-holder', FILTER_SANITIZE_STRING );
		$expiry_month = filter_input( INPUT_POST, 'archway-card-expiry-month', FILTER_SANITIZE_STRING );
		$expiry_year  = filter_input( INPUT_POST, 'archway-card-expiry-year', FILTER_SANITIZE_STRING );
		$card_cvv     = filter_input( INPUT_POST, 'archway-card-cvv', FILTER_SANITIZE_STRING );

		// Is error.
		$is_checkout_error = false;

		// Check if card number is provided.
		if ( empty( $card_number ) ) {
			$is_checkout_error                 = true;
			$card_number_missing_error_message = __( 'Please add your card number.', 'wc-archway-payment-gateway' );
			/**
			 * Archway payment card number missing error.
			 *
			 * This filter helps in modifying the error message when card number is not provided.
			 *
			 * @param string $card_number_missing_error_message Error message.
			 * @return string
			 * @since 1.0.0
			 */
			$card_number_missing_error_message = apply_filters( 'cf_archway_payment_gateway_card_number_error_message', $card_number_missing_error_message );

			// Add the error message now.
			if ( ! empty( $card_number_missing_error_message ) ) {
				wc_add_notice( $card_number_missing_error_message, 'error' );
			}
		}

		// Check if card holder name is provided.
		if ( empty( $card_holder ) ) {
			$is_checkout_error                 = true;
			$card_holder_missing_error_message = __( 'Please provide the name on card.', 'wc-archway-payment-gateway' );
			/**
			 * Archway payment card holder name missing error.
			 *
			 * This filter helps in modifying the error message when card holder name is not provided.
			 *
			 * @param string $card_holder_missing_error_message Error message.
			 * @return string
			 * @since 1.0.0
			 */
			$card_holder_missing_error_message = apply_filters( 'cf_archway_payment_gateway_card_holder_error_message', $card_holder_missing_error_message );

			// Add the error message now.
			if ( ! empty( $card_holder_missing_error_message ) ) {
				wc_add_notice( $card_holder_missing_error_message, 'error' );
			}
		}

		// Check if card cvv is provided.
		if ( empty( $card_cvv ) ) {
			$is_checkout_error              = true;
			$card_cvv_missing_error_message = __( 'Please provide the CVV from the card.', 'wc-archway-payment-gateway' );
			/**
			 * Archway payment card CVV missing error.
			 *
			 * This filter helps in modifying the error message when card CVV is not provided.
			 *
			 * @param string $card_cvv_missing_error_message Error message.
			 * @return string
			 * @since 1.0.0
			 */
			$card_cvv_missing_error_message = apply_filters( 'cf_archway_payment_gateway_card_cvv_error_message', $card_cvv_missing_error_message );

			// Add the error message now.
			if ( ! empty( $card_cvv_missing_error_message ) ) {
				wc_add_notice( $card_cvv_missing_error_message, 'error' );
			}
		}

		// Get the gateway settings.
		$gateway_settings = woocommerce_archway_payments_settings();
		$api_url          = ( ! empty( $gateway_settings['process_transaction_api_url'] ) ) ? $gateway_settings['process_transaction_api_url'] : '';
		$api_key          = ( ! empty( $gateway_settings['api_key'] ) ) ? $gateway_settings['api_key'] : '';

		// Return false, if the transaction API URL is not available.
		if ( empty( $api_url ) || empty( $api_key ) ) {
			$is_checkout_error = true;
			wc_add_notice( __( 'Cannot process payment due to gateway settings error. Please contact site administrator.', 'wc-archway-payment-gateway' ), 'error' );
		}

		// Return, if there is checkout error.
		if ( $is_checkout_error ) {
			return;
		}

		// Billing address.
		$billing_address_1 = filter_input( INPUT_POST, 'billing_address_1', FILTER_SANITIZE_STRING );
		$billing_address_2 = filter_input( INPUT_POST, 'billing_address_2', FILTER_SANITIZE_STRING );
		$billing_address   = '';

		// Prepare the billing address.
		if ( ! empty( $billing_address_1 ) && ! empty( $billing_address_2 ) ) {
			$billing_address = trim( "{$billing_address_1}, {$billing_address_2}" );
		} elseif ( ! empty( $billing_address_1 ) ) {
			$billing_address = trim( $billing_address_1 );
		} elseif ( ! empty( $billing_address_2 ) ) {
			$billing_address = trim( $billing_address_2 );
		}

		// Get the cart totals.
		$cart_totals = WC()->cart->get_totals();
		$cart_totals = ( ! empty( $cart_totals['total'] ) ) ? (float) $cart_totals['total'] : 0.00;

		/**
		 * Fire the payment API now.
		 * Prepare the payment parameters.
		 */
		$payment_parameters = array(
			'mid'          => 987654321,
			'amount'       => $cart_totals,
			'currency'     => 1, // 1:GBP, 2:CAD, 3:EUR, 4:USD, 5:CNY, 6:AUD
			'card_number'  => $card_number,
			'expiry_month' => $expiry_month,
			'expiry_year'  => $expiry_year,
			'csv'          => $card_cvv,
			'order_number' => filter_input( INPUT_POST, 'woocommerce-process-checkout-nonce', FILTER_SANITIZE_STRING ),
			'first_name'   => filter_input( INPUT_POST, 'billing_first_name', FILTER_SANITIZE_STRING ),
			'last_name'    => filter_input( INPUT_POST, 'billing_last_name', FILTER_SANITIZE_STRING ),
			'email'        => filter_input( INPUT_POST, 'billing_email', FILTER_SANITIZE_STRING ),
			'phone'        => filter_input( INPUT_POST, 'billing_phone', FILTER_SANITIZE_STRING ),
			'country'      => filter_input( INPUT_POST, 'billing_country', FILTER_SANITIZE_STRING ),
			'address'      => $billing_address,
			'city'         => filter_input( INPUT_POST, 'billing_email', FILTER_SANITIZE_STRING ),
			'state'        => filter_input( INPUT_POST, 'billing_state', FILTER_SANITIZE_STRING ),
			'postal'       => filter_input( INPUT_POST, 'billing_postcode', FILTER_SANITIZE_STRING ),
		);

		/**
		 * Archway payment arguments.
		 *
		 * This filter helps to modify the archway payment arguments.
		 *
		 * @param array $payment_parameters Archway payment arguments.
		 * @return array
		 * @since 1.0.0
		 */
		$payment_parameters = apply_filters( 'cf_archway_payment_args', $payment_parameters );

		// Process the API now.
		$response = wp_remote_post(
			$api_url,
			array(
				'method'  => 'POST',
				'body'    => wp_json_encode( $payment_parameters ),
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-Api-Key'    => $api_key,
					'Accept'       => 'application/json',
				),
			)
		);

		// Get the response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		// Is it's a success.
		if ( 200 === $response_code ) {
			// Get the response body.
			$response_body = wp_remote_retrieve_body( $response );
			$response_body = json_decode( $response_body );

			// Get the transaction ID.
			$transaction_id = ( ! empty( $response_body->transaction_id ) ) ? $response_body->transaction_id : '';

			if ( ! empty( $transaction_id ) ) {
				// Set this ID in the session to be accessible during saving checkout data.
				WC()->session->set( 'cf_archway_transaction_id', $transaction_id );
			}
		} elseif ( 400 === $response_code ) {
			// Get the response body.
			$response_body = wp_remote_retrieve_body( $response );
			$response_body = json_decode( $response_body );
			$error_message = ( ! empty( $response_body->errors[0]->description ) ) ? $response_body->errors[0]->description : '';
			if ( ! empty( $error_message ) ) {
				wc_add_notice( $error_message, 'error' );
			}
		} else {
			// Set the reponse ID to the session to store log.
			WC()->session->set( 'cf_archway_response_code', $response_code );
		}
	}

	/**
	 * Save the card details in the database.
	 *
	 * @param int $order_id WooCommerce order ID.
	 * @since 1.0.0
	 */
	public function cf_woocommerce_checkout_update_order_meta_callback( $order_id ) {
		// Get the selected payment method.
		$payment_method = filter_input( INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING );

		// If it's not the archway payment gateway, return.
		if ( ! empty( $payment_method ) && 'archway_payments' !== $payment_method ) {
			return;
		}

		// Get the card payment details now.
		$card_number  = filter_input( INPUT_POST, 'archway-card-number', FILTER_SANITIZE_STRING );
		$card_number  = ( ! empty( $card_number ) ) ? str_replace( '-', '', $card_number ) : '';
		$card_holder  = filter_input( INPUT_POST, 'archway-card-holder', FILTER_SANITIZE_STRING );
		$expiry_month = filter_input( INPUT_POST, 'archway-card-expiry-month', FILTER_SANITIZE_STRING );
		$expiry_year  = filter_input( INPUT_POST, 'archway-card-expiry-year', FILTER_SANITIZE_STRING );
		$card_cvv     = filter_input( INPUT_POST, 'archway-card-cvv', FILTER_SANITIZE_STRING );

		// Save the details in the database.
		$card_details = array(
			'card_number'  => $card_number,
			'card_holder'  => $card_holder,
			'expiry_month' => $expiry_month,
			'expiry_year'  => $expiry_year,
			'card_cvv'     => $card_cvv,
		);
		update_post_meta( $order_id, 'archway-payment-card-details', $card_details );
	}

	/**
	 * Modify the payment arguments.
	 *
	 * @param array $args Payment arguments.
	 * @return array
	 * @since 1.0.0
	 */
	public function cf_cf_archway_payment_args_callback( $args ) {
		// Add the IP address to the payment arguments, if not localhost.
		$is_localhost = cf_is_localhost();

		if ( ! $is_localhost ) {
			$args['ip'] = $_SERVER['REMOTE_ADDR'];
		}

		return $args;
	}

	/**
	 * Add the transaction ID to the posted session data.
	 *
	 * @param array $posted_data Checkout posted data.
	 * @return array
	 * @since 1.0.0
	 */
	public function cf_woocommerce_checkout_posted_data_callback( $posted_data ) {
		// Get the selected payment method.
		$payment_method = ( ! empty( $posted_data['payment_method'] ) ) ? $posted_data['payment_method'] : '';

		// If it's not the archway payment gateway, return.
		if ( ! empty( $payment_method ) && 'archway_payments' !== $payment_method ) {
			return $posted_data;
		}

		// Get the transaction ID from the session.
		$transaction_id = WC()->session->get( 'cf_archway_transaction_id' );

		if ( ! empty( $transaction_id ) ) {
			$posted_data['archway_transaction_id'] = $transaction_id;
		}

		// Get the response code.
		$response_code = WC()->session->get( 'cf_archway_response_code' );

		if ( ! empty( $response_code ) ) {
			$posted_data['archway_response_code'] = $response_code;
		}
		
		return $posted_data; 
	}

	/**
	 * Update the transaction ID to the database.
	 *
	 * @param int   $order_id WooCommerce order ID.
	 * @param array $posted_data Checkout posted data.
	 * @since 1.0.0
	 */
	public function cf_woocommerce_checkout_order_processed_callback( $order_id, $posted_data ) {
		// Get the transaction ID from the posted data.
		$transaction_id = ( ! empty( $posted_data['archway_transaction_id'] ) ) ? $posted_data['archway_transaction_id'] : '';
		$response_code  = ( ! empty( $posted_data['archway_response_code'] ) ) ? $posted_data['archway_response_code'] : '';

		// If the transaction ID is available.
		if ( ! empty( $transaction_id ) ) {
			update_post_meta( $order_id, 'archway-payment-transaction-id', $transaction_id );

			// Unset the session now.
			WC()->session->__unset( 'cf_archway_transaction_id' );

			// Write the log.
			scf_write_payment_log( "SUCCESS: Transaction completed for order id #{$order_id}. Transaction ID: #{$transaction_id}" );
		}

		// If the response code is available.
		if ( ! empty( $response_code ) ) {
			// Unset the session now.
			WC()->session->__unset( 'cf_archway_response_code' );

			// Write the log.
			scf_write_payment_log( "ERROR: Transaction could not be completed for order id #{$order_id}. Response code: #{$response_code}" );
		}
	}
}
