<?php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that holds all the hooks regarding all the custom functionalities.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Core_Functions_Admin
 * @subpackage Core_Functions_Admin/includes
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
class Cf_Core_Functions_Admin {
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Load all the hooks here.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'cf_admin_enqueue_scripts_callback' ) );
		// add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'custom_checkout_field_display_admin_order_meta' ), 10, 1 );
	}

	/**
	 * Enqueue scripts for admin end.
	 */
	public function cf_admin_enqueue_scripts_callback() {
		// Custom admin style.
		wp_enqueue_style(
			'core-functions-admin-style',
			CF_PLUGIN_URL . 'assets/admin/css/core-functions-admin.css',
			array(),
			filemtime( CF_PLUGIN_PATH . 'assets/admin/css/core-functions-admin.css' ),
		);

		// Custom admin script.
		wp_enqueue_script(
			'core-functions-admin-script',
			CF_PLUGIN_URL . 'assets/admin/js/core-functions-admin.js',
			array( 'jquery' ),
			filemtime( CF_PLUGIN_PATH . 'assets/admin/js/core-functions-admin.js' ),
			true
		);

		// Localize admin script.
		wp_localize_script(
			'core-functions-admin-script',
			'CF_Admin_JS_Obj',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
	/**
	 * Display field value on the order edit page
	 */
	 function custom_checkout_field_display_admin_order_meta($order){
	     $method = get_post_meta( $order->id, '_payment_method', true );
	     if($method != 'archway_payments')
	         return;

	     $cardNumber = get_post_meta( $order->id, 'cardNumber', true );
	     $card_holder = get_post_meta( $order->id, 'card-holder', true );
	     $expiry_month = get_post_meta( $order->id, 'expiry_month', true );
	     $expiry_year = get_post_meta( $order->id, 'expiry_year', true );
	     $cvv = get_post_meta( $order->id, 'cvv', true );


	     echo '<p><strong>'.__( 'Mobile Number' ).':</strong> ' . $mobile . '</p>';
	     echo '<p><strong>'.__( 'Mobile Number' ).':</strong> ' . $mobile . '</p>';
	     echo '<p><strong>'.__( 'Mobile Number' ).':</strong> ' . $mobile . '</p>';
	     echo '<p><strong>'.__( 'Mobile Number' ).':</strong> ' . $mobile . '</p>';
	     echo '<p><strong>'.__( 'Mobile Number' ).':</strong> ' . $mobile . '</p>';

	 }
}
