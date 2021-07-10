<?php
/**
 * The file that defines the archway payment gateway class.
 *
 * A class definition that holds the API calls to handle transactions with ArchWay.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Core_Functions
 * @subpackage Core_Functions/includes
 */

/**
 * The file that defines the archway payment gateway class.
 *
 * A class definition that holds the API calls to handle transactions with ArchWay.
 *
 * @since      1.0.0
 * @package    Core_Functions
 * @author     Adarsh Verma <adarsh@cmsminds.com>
 */
class WooCommerce_Archway_Payment_Gateway extends WC_Payment_Gateway {
	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'archway_payments';
		$this->icon               = apply_filters( 'woocommerce_custom_gateway_icon', '' );
		$this->has_fields         = false;
		$this->method_title       = __( 'Archway', 'wc-archway-payment-gateway' );
		$this->method_description = __( 'Archway works by adding payment fields on checkout and then sending details to Archeay.', 'wc-archway-payment-gateway' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions', $this->description );
		$this->order_status = $this->get_option( 'order_status', 'completed' );
		$this->api_url      = $this->get_option( 'api_url' );
		$this->api_key      = $this->get_option( 'api_key' );

		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

		// Customer Emails
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'                     => array(
				'title'   => __( 'Enable/Disable', 'wc-archway-payment-gateway' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Custom Payment', 'wc-archway-payment-gateway' ),
				'default' => 'yes'
			),
			'title'                       => array(
				'title'       => __( 'Title', 'wc-archway-payment-gateway' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wc-archway-payment-gateway' ),
				'default'     => __( 'Archway Payments', 'wc-archway-payment-gateway' ),
				'desc_tip'    => true,
			),
			'order_status'                => array(
				'title'       => __( 'Order Status', 'wc-archway-payment-gateway' ),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'description' => __( 'Choose whether status you wish after checkout.', 'wc-archway-payment-gateway' ),
				'default'     => 'wc-completed',
				'desc_tip'    => true,
				'options'     => wc_get_order_statuses()
			),
			'description'                 => array(
				'title'       => __( 'Description', 'wc-archway-payment-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'wc-archway-payment-gateway' ),
				'default'     => __('Payment Information', 'wc-archway-payment-gateway'),
				'desc_tip'    => true,
			),
			'instructions'                => array(
				'title'       => __( 'Instructions', 'wc-archway-payment-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page and emails.', 'wc-archway-payment-gateway' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'is_sandbox'                  => array(
				'title' => __( 'Is Sandbox?', 'wc-archway-payment-gateway' ),
				'desc'  => __( 'If you\'re testing your payments, keep this checked.', 'wc-archway-payment-gateway' ),
				'id'    => 'archway_is_sandbox',
				'type'  => 'checkbox',
			),
			'process_transaction_api_url' => array(
				'title'       => __( 'Process Transaction API URL', 'wc-archway-payment-gateway' ),
				'type'        => 'text',
				'description' => __( 'This API URL helps in processing a transaction. Put the production mode URL here.', 'wc-archway-payment-gateway' ),
				'placeholder' => 'https://api.archwaypayments.com/v1/...',
				'desc_tip'    => true,
			),
			'get_transaction_api_url'     => array(
				'title'       => __( 'Get Transaction API URL', 'wc-archway-payment-gateway' ),
				'type'        => 'text',
				'description' => __( 'This API URL helps in fetching transaction details. Put the production mode URL here.', 'wc-archway-payment-gateway' ),
				'placeholder' => 'https://api.archwaypayments.com/v1/...',
				'desc_tip'    => true,
			),
			'sandbox_api_key'             => array(
				'title'       => __( 'Sandbox API KEY', 'wc-archway-payment-gateway' ),
				'type'        => 'password',
				'description' => __( 'Archway payment gateway sandbox API key.', 'wc-archway-payment-gateway' ),
				'placeholder' => '****',
				'desc_tip'    => true,
			),
			'production_api_key'          => array(
				'title'       => __( 'Production API KEY', 'wc-archway-payment-gateway' ),
				'type'        => 'password',
				'description' => __( 'Archway payment gateway production API key.', 'wc-archway-payment-gateway' ),
				'placeholder' => '****',
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page() {
		if ( $this->instructions )
			echo wpautop( wptexturize( $this->instructions ) );
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && 'archway_payments' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
			echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
		}
	}
	/*
	* Function to display Front end Credit card form.
	*/
	public function payment_fields(){
		// Print the payment gateway description, if we have.
		echo ( ! empty( $this->get_description() ) ) ? wpautop( wptexturize( $this->get_description() ) ) : '';

		// Get the months list.
		$months = cf_get_months_array();

		ob_start();
		?>
		<div id="archway_payment_gateway_method">
			<div class="form-group card-number-field">
				<label for="archway-card-number"><?php esc_html_e( 'Card Number', 'wc-archway-payment-gateway' ); ?></label>
				<input type="text" name="archway-card-number" class="form-control" id="archway-card-number" maxlength="19" placeholder="1234-1234-1234-1234">
			</div>
			<div class="form-group cvv-field">
				<label for="archway-card-cvv"><?php esc_html_e( 'CVV', 'wc-archway-payment-gateway' ); ?></label>
				<input type="password" name="archway-card-cvv" class="form-control" id="archway-card-cvv" maxlength="3"  placeholder="CVV">
			</div>
			<div class="form-group expiration-date-field">
				<label><?php esc_html_e( 'Expiration', 'wc-archway-payment-gateway' ); ?></label>
				<select name="archway-card-expiry-month" id="archway-card-expiry-month">
					<?php
					if ( ! empty( $months ) && is_array( $months ) ) {
						foreach( $months as $month_num => $month_name ) {
							echo wp_kses(
								'<option value="'. $month_num .'">'. $month_name .'</option>',
								array(
									'option' => array(
										'value' => array(),
									),
								)
							);
						}
					}
					?>
				</select>
				<select name="archway-card-expiry-year" id="archway-card-expiry-year">
					<?php
					$current_year = gmdate( 'Y' );
					for ( $year = $current_year; $year <= ( $current_year + 20 ); $year++ ) {
						echo wp_kses(
							'<option value="'. $year .'">' . $year . '</option>',
							array(
								'option' => array(
									'value' => array(),
								),
							)
						);
					}
					?>
				</select>
			</div>
			<div class="form-group card-holder-field">
				<label for="archway-card-holder"><?php esc_html_e( 'Card Holder', 'wc-archway-payment-gateway' ); ?></label>
				<input type="text" name="archway-card-holder" class="form-control" id="archway-card-holder" placeholder="<?php esc_html_e( 'Please input your name', 'wc-archway-payment-gateway' ); ?>">
			</div>
		</div>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		$status = 'wc-' === substr( $this->order_status, 0, 3 ) ? substr( $this->order_status, 3 ) : $this->order_status;

		// Set order status
		$order->update_status( $status, __( 'Checkout with custom payment. ', 'wc-archway-payment-gateway' ) );

		// or call the Payment complete
		// $order->payment_complete();

		// Reduce stock levels
		$order->reduce_order_stock();

		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result'    => 'success',
			'redirect'  => $this->get_return_url( $order )
		);
	}
}
