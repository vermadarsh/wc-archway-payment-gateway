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
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_custom_gateway_class' ) );
		add_action('woocommerce_checkout_process', array( $this, 'process_custom_payment' ));
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'custom_payment_update_order_meta' ) );
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

	function add_custom_gateway_class( $methods ) {
	    $methods[] = 'WooCommerce_Archway_Payment_Gateway';
	    return $methods;
	}
	function process_custom_payment(){

	    if($_POST['payment_method'] != 'archway_payments'){
	      return;
	    }
	    $cardNumber = filter_input( INPUT_POST, 'cardNumber', FILTER_SANITIZE_STRING );
			$cardNumber = str_replace('-','',$cardNumber);
			$owner = filter_input( INPUT_POST, 'owner', FILTER_SANITIZE_STRING );
	    $expiry_month = filter_input( INPUT_POST, 'expiry_month', FILTER_SANITIZE_STRING );
	    $expiry_year = filter_input( INPUT_POST, 'expiry_year', FILTER_SANITIZE_STRING );
	    $cvv = filter_input( INPUT_POST, 'cvv', FILTER_SANITIZE_STRING );
	    if(! isset($cardNumber) || empty($cardNumber)){
	      wc_add_notice( __( 'Please add your card number' ), 'error' );

	    } elseif(! isset($owner) || empty($owner)) {
	      wc_add_notice( __( 'Please add your name' ), 'error' );

	    } elseif (! isset($cvv) || empty($cvv)) {
	      wc_add_notice( __( 'Please add your card cvv number' ), 'error' );

	    }

			$array_with_parameters = array(
				"mid"          => 12345,
				"amount"       => 10.00,
				"currency"     => 1,
				"card_number"  => "4111111111111111",
				"expiry_month" => "12",
				"expiry_year"  => "2019",
				"csv"          => "123",
				"order_number" => "ABCD1234",
				"first_name"   => "John",
				"last_name"    => "Smith",
				"email"        => "johnsmith@mail.com",
				"phone"        => "1-416-555-1212",
				"country"      => "US",
				"address"      => "123 Main St.",
				"city"         => "Somewhere",
				"state"        => "NY",
				"postal"       => "12345",
				"ip"           => "192.168.0.1"
			);

			// debug(json_encode($array_with_parameters));
			// die;
			$url       = 'https://api.archwaypayments.com/v1/test/transaction/ProcessTransaction';
			$body_data = json_encode($array_with_parameters);
			$data = wp_remote_post($url, array(
			    'headers'   => array( 'Content-Type' => 'application/json','X-Api-Key' => '2Xz7Gr5TnYj9esgL48MpZ26KixGE3R2c','Accept' => 'application/json' ),
			    'body'      => $body_data,
			    'method'    => 'POST'
			));
			debug($data);
			die;




	}
	/**
	 * Update the order meta with field value
	 */
	function custom_payment_update_order_meta( $order_id ) {

	    if($_POST['payment_method'] != 'archway_payments')
	        return;
	        $cardNumber = filter_input( INPUT_POST, 'cardNumber', FILTER_SANITIZE_STRING );
	        $owner = filter_input( INPUT_POST, 'owner', FILTER_SANITIZE_STRING );
	        $expiry_month = filter_input( INPUT_POST, 'expiry_month', FILTER_SANITIZE_STRING );
	        $expiry_year = filter_input( INPUT_POST, 'expiry_year', FILTER_SANITIZE_STRING );
	        $cvv = filter_input( INPUT_POST, 'cvv', FILTER_SANITIZE_STRING );
	    // echo "<pre>";
	    // print_r($_POST);
	    // echo "</pre>";
	    // exit();

	        update_post_meta( $order_id, 'cardNumber', $cardNumber );
	        update_post_meta( $order_id, 'owner', $owner );
	        update_post_meta( $order_id, 'expiry_month', $expiry_month );
	        update_post_meta( $order_id, 'expiry_year', $expiry_year );
	        update_post_meta( $order_id, 'cvv', $cvv );
	}
}
