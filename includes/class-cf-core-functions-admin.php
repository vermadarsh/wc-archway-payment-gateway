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
		add_action( 'add_meta_boxes', array( $this, 'cf_add_meta_boxes_callback' ) );
		add_action( 'wp_ajax_get_transaction', array( $this, 'cf_get_transaction_callback' ) );
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
	 * Add custom metabox.
	 *
	 * @since 1.0.0
	 */
	public function cf_add_meta_boxes_callback() {
		// Add metabox to woocommerce order for archway payment gateway.
		add_meta_box(
			'cf-archway-payment-method-data',
			__( 'Archway: Transaction Details', 'wc-archway-payment-gateway' ),
			array( $this, 'cf_archway_transaction_data_callback' ),
			'shop_order',
			'normal'
		);
	}

	/**
	 * Archway transaction metabox callback.
	 */
	public function cf_archway_transaction_data_callback() {
		$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		// Get the transaction ID.
		$transaction_id = get_post_meta( $post_id, 'archway-payment-transaction-id', true );

		if ( ! empty( $transaction_id ) ) {
			echo '<p>';
			echo sprintf( __( '%2$sTransaction ID: %1$s%3$s', 'wc-archway-payment-gateway' ), $transaction_id, '<span>', '</span>' );
			echo '<a class="cf-view-transaction-details" href="javascript:void(0);">' . __( 'View Details', 'wc-archway-payment-gateway' ) . '</a>';
			echo '</p>';
			echo '<div class="cf-transaction-details"></div>';
		}
	}

	/**
	 * AJAX for fetching transaction.
	 *
	 * @since 1.0.0
	 */
	public function cf_get_transaction_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		// Exit, if the action doesn't match.
		if ( empty( $action ) || 'get_transaction' !== $action ) {
			echo 0;
			wp_die();
		}

		// Posted items.
		$order_id = (int) filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );

		// Get the transaction ID from the database.
		$transaction_id = get_post_meta( $order_id, 'archway-payment-transaction-id', true );

		// Get the transaction data.
		$transaction = cf_get_transaction( $transaction_id );

		// Prepare the HTML if the transaction data is received.
		ob_start();
		if ( false !== $transaction && is_array( $transaction ) ) {
			// Write the log.
			scf_write_payment_log( "SUCCESS: Transaction details fetched for order id #{$order_id}. Transaction ID: #{$transaction_id}" );
			
			$transaction_arr = array(
				'Card Number'  => ( ! empty( $transaction['card_number'] ) ) ? $transaction['card_number'] : '',
				'IP'           => ( ! empty( $transaction['ip'] ) ) ? $transaction['ip'] : '',
				'Descriptor'   => ( ! empty( $transaction['descriptor'] ) ) ? $transaction['descriptor'] : '',
				'Order Number' => ( ! empty( $transaction['order_number'] ) ) ? $transaction['order_number'] : '',
				'Status'       => ( ! empty( $transaction['status'] ) ) ? $transaction['status'] : '',
				'Message'      => ( ! empty( $transaction['message'] ) ) ? $transaction['message'] : '',
				'Created At'   => ( ! empty( $transaction['created_at'] ) ) ? $transaction['created_at'] : '',
				'Updated At'   => ( ! empty( $transaction['updated_at'] ) ) ? $transaction['updated_at'] : '',
			);
			?>
			<table class="cf-transaction-data">
				<tbody>
					<?php foreach( $transaction_arr as $data_index => $transaction_data ) { ?>
						<tr>
							<td scope="row"><label for=""><?php echo esc_html( $data_index ); ?></label></td>
							<td><?php echo esc_html( $transaction_data ); ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php
		} else {
			?>
			<p><?php esc_html_e( 'Unable to fetch transaction data.', 'wc-archway-payment-gateway' ); ?></p>
			<?php
		}

		$html = ob_get_clean();

		// Send back the final response.
		$response = array(
			'code' => 'cf-transaction-details-fetched',
			'html' => $html,
		);
		wp_send_json_success( $response );
		wp_die();
	}
}
